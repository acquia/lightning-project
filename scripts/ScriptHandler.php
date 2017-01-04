<?php

/**
 * @file
 * Contains \Acquia\Lightning\ScriptHandler.
 */

namespace Acquia\Lightning;

use Composer\Script\Event;
use Composer\Util\ProcessExecutor;
use Drupal\Component\Serialization\Yaml;

class ScriptHandler {

  /**
   * The path where the Lightning profile was installed.
   *
   * @var string
   */
  protected static $installPath;

  /**
   * Derives local Behat configuration from Lightning.
   *
   * @param \Composer\Script\Event $event
   *   The script event.
   */
  public static function prepareBehat(Event $event) {
    $composer = $event->getComposer();

    // Use Composer's local repository to find the path to Lightning. If we
    // can't find Lightning at all, sound the alarm and bail out.
    $packages = $composer
      ->getRepositoryManager()
      ->getLocalRepository()
      ->findPackages('acquia/lightning');

    if ($packages) {
      static::$installPath = $composer
        ->getInstallationManager()
        ->getInstallPath($packages[0]);
    }
    else {
      throw new \RuntimeException('acquia/lightning package(s) not found in local repository.');
    }

    // If behat.yml doesn't exist in the current directory, copy it from the
    // Lightning profile and make the appropriate alterations.
    if (!file_exists('behat.yml')) {
      $config = static::readYAML('behat.yml');

      // Alias parts of the configuration for readability.
      $suite = &$config['default']['suites']['default'];
      $extensions = &$config['default']['extensions'];

      // The Selenium2 driver should drive Chrome.
      $extensions['Behat\MinkExtension']['selenium2'] = ['browser' => 'chrome'];

      // Process relevant paths.
      $suite['paths'] = array_map([static::class, 'processPath'], $suite['paths']);

      $extensions['Drupal\DrupalExtension']['subcontexts']['paths'] = array_map(
        [static::class, 'processPath'],
        $extensions['Drupal\DrupalExtension']['subcontexts']['paths']
      );

      file_put_contents('behat.yml', Yaml::encode($config));
    }

    // If behat.local.yml doesn't exist in the current directory, copy it from
    // the Lightning profile and make the appropriate alterations.
    if (!file_exists('behat.local.yml')) {
      $config = static::readYAML('behat.local.yml');

      // The Drupal root is the installed docroot.
      // TODO: We probably shouldn't naively assume that docroot is our docroot.
      $config['default']['extensions']['Drupal\DrupalExtension']['drupal']['drupal_root'] = 'docroot';

      file_put_contents('behat.local.yml', Yaml::encode($config));
    }
  }

  /**
   * Reads a YAML file from the Lightning profile directory.
   *
   * @param string $file
   *   The file to read.
   *
   * @return mixed
   *   The decoded file.
   */
  protected static function readYAML($file) {
    $config = file_get_contents(static::$installPath . '/' . $file);
    return Yaml::decode($config);
  }

  /**
   * Process a path in Behat configuration.
   *
   * @param string $path
   *   The path to process.
   *
   * @return string
   *   The processed path. If %paths.base% is present in the original path, it
   *   will be replaced with the path to the Lightning profile. Otherwise, the
   *   path to Lightning will be prepended.
   */
  protected static function processPath($path) {
    $replaced = 0;
    $path = str_replace('%paths.base%', static::$installPath, $path, $replaced);

    return $replaced ? $path : static::$installPath . '/' . $path;
  }

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
