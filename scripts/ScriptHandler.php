<?php

/**
 * @file
 * Contains \Acquia\Lightning\ScriptHandler.
 */

namespace Acquia\Lightning;

use Composer\Script\Event;
use Composer\Util\Filesystem;

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
        if (array_intersect(['drupal/lightning', 'type:drupal-profile'], $criteria)) {
          $lightning = $path;
        }
      }
      if (isset($lightning)) {
        $libraries = str_replace('{$name}', 'lightning', $lightning) . '/libraries';
        $fs = new Filesystem();
        $fs->removeDirectory($libraries);
        $fs->rename('vendor/bower_components', $libraries);
      }
    }
  }

}
