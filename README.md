This is a Composer-based installer for the [Lightning](https://www.drupal.org/project/lightning) Drupal distribution. Welcome to the future!

## Get Started
```
$ composer create-project acquia/lightning-project MY_PROJECT
$ cd MY_PROJECT && composer quick-start
```

This will create a functioning Lightning site, open a web browser, and log you into the site using Drupal's built-in Quick Start command. If you'd rather use your own database and web server, you can skip the second step above and install Lightning like you would any other Drupal site.

Normally, Composer will install all dependencies into a `vendor` directory that is *next* to `docroot`, not inside it. This may create problems in certain hosting environments, so if you need to, you can tell Composer to install dependencies into `docroot/vendor` instead:

```
$ composer create-project acquia/lightning-project MY_PROJECT --no-install
$ composer config vendor-dir docroot/vendor
$ cd MY_PROJECT
$ composer install
```

Either way, remember to keep the `composer.json` and `composer.lock` files that exist above `docroot` -- they are controlling your dependencies.

## Maintenance
`drush make`, `drush pm-download`, `drush pm-update` and their ilk are the old-school way of maintaining your code base. Forget them. You're in Composer land now!

Let this handy table be your guide:

| Task                                            | Drush                                         | Composer                                          |
|-------------------------------------------------|-----------------------------------------------|---------------------------------------------------|
| Installing a contrib project (latest version)   | ```drush pm-download PROJECT```               | ```composer require drupal/PROJECT```             |
| Installing a contrib project (specific version) | ```drush pm-download PROJECT-8.x-1.0-beta3``` | ```composer require drupal/PROJECT:1.0.0-beta3``` |
| Installing a javascript library (e.g. dropzone) | ```drush pm-download dropzone```              | ```composer require bower-asset/dropzone```       |
| Updating all contrib projects and Drupal core   | ```drush pm-update```                         | ```composer update```                             |
| Updating a single contrib project               | ```drush pm-update PROJECT```                 | ```composer update drupal/PROJECT```              |
| Updating Drupal core                            | ```drush pm-update drupal```                  | ```composer update drupal/core```                 |

The magic is that Composer, unlike Drush, is a *dependency manager*. If module ```foo version: 1.0.0``` depends on ```baz version: 3.2.0```, Composer will not let you update baz to ```3.3.0``` (or downgrade it to ```3.1.0```, for that matter). Drush has no concept of dependency management. If you've ever accidentally hosed a site because of dependency issues like this, you've probably already realized how valuable Composer can be.

But to be clear: it is still very helpful to use a site management tool like Drush or Drupal Console. Tasks such as database updates (```drush updatedb```) are still firmly in the province of such utilities. This installer will install a copy of Drush (local to the project) in the ```bin``` directory.

### Specifying a version
you can specify a version from the command line with:

    $ composer require drupal/<modulename>:<version> 

For example:

    $ composer require drupal/ctools:3.0.0-alpha26
    $ composer require drupal/token:1.x-dev 

In these examples, the composer version 3.0.0-alpha26 maps to the drupal.org version 8.x-3.0-alpha26 and 1.x-dev maps to 8.x-1.x branch on drupal.org.

If you specify a branch, such as 1.x you must add -dev to the end of the version.

**Composer is only responsible for maintaining the code base**.

## Source Control
If you peek at the ```.gitignore``` we provide, you'll see that certain directories, including all directories containing contributed projects, are excluded from source control. This might be a bit disconcerting if you're newly arrived from Planet Drush, but in a Composer-based project like this one, **you SHOULD NOT commit your installed dependencies to source control**.

When you set up the project, Composer will create a file called ```composer.lock```, which is a list of which dependencies were installed, and in which versions. **Commit ```composer.lock``` to source control!** Then, when your colleagues want to spin up their own copies of the project, all they'll have to do is run ```composer install```, which will install the correct versions of everything in ```composer.lock```.

## How do I update Drupal core?
It's counterintuitive, but **don't add `drupal/core` to your project's composer.json!** Lightning manages Drupal core for you, so adding a direct dependency on Drupal core is likely to cause problems for you in the future.

Lightning's minor versions correspond to Drupal core's. So, for example, `acquia/lightning:~3.3.0` will require Drupal core 8.7.x. `acquia/lightning:~3.2.0` requires Drupal core 8.6.x, and `~3.1.0` requires Drupal core 8.5.x. If you wanted to update Drupal core from (for instance) 8.6.x to 8.7.x, you would do this by changing your requirement for `acquia/lightning`, like so:

```
composer require --no-update acquia/lightning:~3.3.0
composer update
```

## Compatibility table
| `acquia/lightning` version | Drupal core version | Drush version |
|----------------------------|---------------------|---------------|
| `~4.0.0` | 8.7.x | `>=9.4` |
| `~3.3.0` | 8.7.x | `>=9.4` |
| `~3.2.0` | 8.6.x | `>=9.4` |
| `~3.1.0` | 8.5.x | `>=9.4` |
