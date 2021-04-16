<?php

namespace Drupal\newsletterapi;

/**
 * Interface JsonFixerServiceInterface.
 */
interface JsonFixerServiceInterface {

  /**
   * Cleaning broken json from string.
   *
   * @param string $json
   * @return mixed
   */
  public function fix(string $json);
}
