<?php

namespace Drupal\marketools\Form;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class MarketoolsConfigForm extends ConfigFormBase
{

  const CONFIG_NAME = 'marketools.config';

  protected function getEditableConfigNames(): array
  {
    return [static::CONFIG_NAME];
  }

  public function getFormId(): string
  {
    return 'marketools_config_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state): array
  {
    $config = $this->config(static::CONFIG_NAME);
    $form['mautic_base_url'] = [
      '#type' => 'textfield',
      '#title' => t('Mautic base url'),
      '#default_value' => $config->get('mautic_base_url'),
      '#description' => t('Enter a valid and absolute URL. No query parameters. No trailing slash.')
    ];
    $form['mautic_client_id'] = [
      '#type' => 'textfield',
      '#title' => t('Mautic client id (public key)'),
      '#default_value' => $config->get('mautic_client_id'),
      '#description' => t('Enter a valid mautic client id')
    ];
    $form['mautic_client_secret'] = [
      '#type' => 'textfield',
      '#title' => t('Mautic client secret (secret key)'),
      '#default_value' => $config->get('mautic_client_secret'),
      '#description' => t('Enter a valid mautic client secret')
    ];
    return parent::buildForm($form, $form_state);
  }

  public function validateForm(array &$form, FormStateInterface $form_state): void
  {
    $form_state->setValue('mautic_base_url', trim($form_state->getValue('mautic_base_url')));
    $mauticBaseUrl = $form_state->getValue('mautic_base_url');
    if (isset($mauticBaseUrl)) {
      if (!UrlHelper::isValid($mauticBaseUrl, true)) {
        $form_state->setErrorByName('mautic_base_url', 'Invalid URL. This url has to be valid and absolute.');
      } elseif (str_ends_with($mauticBaseUrl, '/')) {
        $form_state->setValue('mautic_base_url', substr_replace($mauticBaseUrl, '', -1));
      }
    }
    parent::validateForm($form, $form_state);
  }

  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $config = $this->config(static::CONFIG_NAME);
    $values = $form_state->getValues();
    $config->set('mautic_base_url', $values['mautic_base_url'])->save();
    $config->set('mautic_client_id', $values['mautic_client_id'])->save();
    $config->set('mautic_client_secret', $values['mautic_client_secret'])->save();
    parent::submitForm($form, $form_state);
  }

}
