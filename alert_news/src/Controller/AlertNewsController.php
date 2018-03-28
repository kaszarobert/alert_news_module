<?php

namespace Drupal\alert_news\Controller;

use Drupal\Core\Controller\ControllerBase;

class AlertNewsController extends ControllerBase {

  /**
   * Display the markup.
   *
   * @return array
   */
  public function content() {

    //SELECT name FROM `taxonomy_term_field_data` WHERE vid = 'hirtipusok'
      $result = \Drupal::database()->select('taxonomy_term_field_data', 't')
          ->fields('t', ['name'])->condition('vid', 'hirtipusok', '=')
          ->execute()
          ->fetchAll();

      $rows = array();
      foreach ($result as $row) {
          $rows[] = array(
              'data' => array($row->name));
      }

      $output = array(
          '#theme' => 'table',
          '#rows' => $rows
      );
      return $output;
  }

}