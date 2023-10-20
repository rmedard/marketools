<?php

namespace Drupal\marketools\model;

class NewsletterContact
{
  private string $firstname;
  private string $lastname;

  private string $email;

  private string $phone;

  /**
   * @param string $firstname
   * @param string $lastname
   * @param string $email
   * @param string $phone
   */
  public function __construct(string $firstname, string $lastname, string $email, string $phone)
  {
    $this->firstname = $firstname;
    $this->lastname = $lastname;
    $this->email = $email;
    $this->phone = $phone;
  }

  public function getFirstname(): string
  {
    return $this->firstname;
  }

  public function setFirstname(string $firstname): void
  {
    $this->firstname = $firstname;
  }

  public function getLastname(): string
  {
    return $this->lastname;
  }

  public function setLastname(string $lastname): void
  {
    $this->lastname = $lastname;
  }

  public function getEmail(): string
  {
    return $this->email;
  }

  public function setEmail(string $email): void
  {
    $this->email = $email;
  }

  public function getPhone(): string
  {
    return $this->phone;
  }

  public function setPhone(string $phone): void
  {
    $this->phone = $phone;
  }

}
