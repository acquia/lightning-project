<?php

/**
 * @file
 * Contains \Acquia\Lightning\ScriptHandler.
 */

namespace Acquia\Lightning;

use Composer\Script\Event;
use Composer\Util\ProcessExecutor;

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
        $lightning = str_replace('{$name}', 'lightning', $lightning);

        $executor = new ProcessExecutor($event->getIO());
        $output = NULL;
        $executor->execute('npm run install-libraries', $output, $lightning);
      }
    }
  }

}
