<?php

namespace Drupal\marketools\Service;

use Drupal;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\marketools\Form\MarketoolsConfigForm;
use Drupal\marketools\model\NewsletterContact;
use Mautic\Api\Contacts;
use Mautic\Auth\ApiAuth;
use Mautic\Auth\OAuth;
use Mautic\Exception\ContextNotFoundException;
use Mautic\Exception\IncorrectParametersReturnedException;
use Mautic\MauticApi;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class MauticService
{

  protected LoggerChannelInterface $logger;
  protected array $oAuthSettings;

  private readonly string $AUTH_DATA_KEY;

  /**
   * @param LoggerChannelFactory $logger
   */
  public function __construct(LoggerChannelFactory $logger) {
    $this->AUTH_DATA_KEY = 'mautic_auth_data';
    $this->logger = $logger->get('mautic_service');
    $marketConfig = Drupal::config(MarketoolsConfigForm::CONFIG_NAME);
    $this->oAuthSettings = [
      'baseUrl' => $marketConfig->get('mautic_base_url'),
      'clientKey' => $marketConfig->get('mautic_client_id'),
      'clientSecret' => $marketConfig->get('mautic_client_secret'),
      'version' => 'OAuth2'
    ];
    $this->oAuthSettings += Drupal::state()->get($this->AUTH_DATA_KEY, []);
  }

  /**
   * @throws ContextNotFoundException
   * @throws IncorrectParametersReturnedException
   */
  public function createContact(NewsletterContact $newsletterContact): array
  {
    $mauticApi = new MauticApi();
    $contactsApi = $mauticApi
      ->newApi('contacts', $this->getAuthData($this->oAuthSettings), $this->oAuthSettings['base_url']);
    if ($contactsApi instanceof Contacts) {
      $data = [
        'firstname' => $newsletterContact->getFirstname(),
        'lastname' => $newsletterContact->getLastname(),
        'email' => $newsletterContact->getEmail(),
        'phone' => $newsletterContact->getPhone()
      ];
      $response = $contactsApi->create($data);
      if ($response !== FALSE and is_array($response)) {
        $this->logger->info('Contact created successfully.');
        return $response;
      } else {
        $this->logger->error('Creating contact failed');
      }
    }
    return [];
  }

  /**
   * @throws IncorrectParametersReturnedException
   * @throws AccessDeniedHttpException
   */
  private function getAuthData(array $settings): OAuth {
    $oAuthData = (new ApiAuth())->newAuth($settings);
    if ($oAuthData instanceof OAuth) {
      if ($oAuthData->validateAccessToken()) {
        if ($oAuthData->accessTokenUpdated()) {
          $this->logger->info('Mautic authentication successful.');
          Drupal::state()->set($this->AUTH_DATA_KEY, $oAuthData->getAccessTokenData());
          $this->oAuthSettings += $oAuthData->getAccessTokenData();
        }
      }
      if (!$oAuthData->isAuthorized()) {
        $this->logger->error('Access to Mautic denied.');
        throw new AccessDeniedHttpException('Authorisation to access Mautic is denied! Please contact the administrator');
      }
    } else {
      $this->logger->error('Connection to Mautic failed. <pre><code>' . print_r($oAuthData, TRUE) . '</code></pre>');
      throw new BadRequestHttpException('Connection to Mautic failed');
    }
    return $oAuthData;
  }
}
