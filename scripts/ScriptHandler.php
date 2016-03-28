<?php

/**
 * @file
 * Contains \Acquia\Lightning\ScriptHandler.
 */

namespace Acquia\Lightning;

use Composer\Script\Event;

class ScriptHandler {

  /**
   * Moves front-end libraries to Lightning's installed directory.
   *
   * @param \Composer\Script\Event $event
   *   The script event.
   */
  public static function deployLibraries(Event $event) {
    $extra = $event->getComposer()->getPackage()->getExtra();

    if (isset($extra['installer-paths'])) {
      foreach ($extra['installer-paths'] as $path => $criteria) {
        if (in_array('drupal/lightning', $criteria) || in_array('type:drupal-profile', $criteria)) {
          $path = str_replace('{$name}', 'lightning', $path);
          rename('vendor/bower_components', $path . '/libraries');
        }
      }
    }
  }

}
