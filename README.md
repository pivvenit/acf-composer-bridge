# Advanced Custom Fields Pro Composer Bridge

[![Build Status](https://img.shields.io/endpoint.svg?url=https%3A%2F%2Factions-badge.atrox.dev%2Fpivvenit%2Facf-composer-bridge%2Fbadge&style=for-the-badge)](https://actions-badge.atrox.dev/pivvenit/acf-composer-bridge/goto)

This repository acts as a bridge to use the excellent [Advanced Custom Fields Pro](https://www.advancedcustomfields.com/pro/)
Wordpress plugin together with [Composer](https://getcomposer.org)/[Bedrock](https://roots.io/bedrock/).

## :warning: Native Dependabot warning :warning: ##
Dependabot Preview will be shut down on August 3rd, 2021 and will be replaced by the new Dependabot integration.
However, this new integration does not support environment variables that are used by [our acf-pro-installer](https://github.com/pivvenit/acf-pro-installer).

We have created a (temporary) workaround for this use case: https://github.com/pivvenit/acf-pro-installer/issues/222#issuecomment-890359373.
To summarise, we've published an authenticated Composer repository that uses the licensekey you provide via Basic authentication to download the file from ACF's server.
The status of this workaround will be updated in the issue mentioned above.

## Breaking change between v2 and V3 ##
As `composer/installers` is added to the dependencies of all provided packages, the default installation folder is changed.
It is recommended to switch to V3, but *ensure ACF Pro ends up in the folder you except when installing* to ensure your code does not break. [This composer manual page](https://getcomposer.org/doc/faqs/how-do-i-install-a-package-to-a-custom-path-for-my-framework.md) describes how to modify the install path. You can find a few examples at the end of this readme.

## How to install
**1. Add the desired repository to the repositories field in composer.json**

Select one of the following repositories based on the desired plugin type:

***Wordpress Packagist plugin***

Use this version if you are unsure which version to use.
```json
{
  "type": "composer",
  "url": "https://pivvenit.github.io/acf-composer-bridge/composer/v3/wordpress-plugin/"
}
```
***Wordpress Packagist Must-Use plugin***

Use this version if you want ACF installed as MU-plugin.
```json
{
  "type": "composer",
  "url": "https://pivvenit.github.io/acf-composer-bridge/composer/v3/wordpress-muplugin/"
}
```

***Wordpress wpackagist plugin type***

Use this repository URL if you use the (seemingly deprecated) `wpackagist-plugin` plugin type.
```json
{
  "type": "composer",
  "url": "https://pivvenit.github.io/acf-composer-bridge/composer/v3/wpackagist-plugin/"
}
```

***As regular composer dependency***

To install the plugin in the `vendor` directory.

```json
{
  "type": "composer",
  "url": "https://pivvenit.github.io/acf-composer-bridge/composer/v3/library/"
}
```

**2. Make your ACF PRO key available**

Set the environment variable **`ACF_PRO_KEY`** to your ACF PRO key.

Alternatively you can add an entry to your **`.env`** file:

```ini
# .env (same directory as composer.json)
ACF_PRO_KEY=Your-Key-Here
```

**3. Require ACF PRO**

You can now use composer as usual, to include any version of advanced-custom-fields-pro
```sh
composer require advanced-custom-fields/advanced-custom-fields-pro
```
## How does it work
This Github repository is a 'Composer repository'.
Actually a composer repository is simply a packages.json served from a webserver.
This repository uses Github Actions to periodically create a packages.json that references 
the files provided by ACF. Please note that these files require a valid license key that is **not provided** by this repository.
In order to append this license key to the files, [https://github.com/pivvenit/acf-pro-installer](https://github.com/pivvenit/acf-pro-installer) is used.
This installer detects that you want to install advanced custom fields, and then appends the provided private key (via environment variable) to the actual download URL on ACF's servers (so the key is never send to this composer repository).

## Available versions
See [https://pivvenit.github.io/acf-composer-bridge/composer/v3/wpackagist-plugin/packages.json](https://pivvenit.github.io/acf-composer-bridge/composer/v3/packages.json)

## Example(s)

1. Installs ACF Pro as mu-plugin in web/app/mu-plugins/advanced-custom-fields-pro
```json
{
  "name": "example/test",
  "repositories": [
    {
      "type": "composer",
      "url": "https://pivvenit.github.io/acf-composer-bridge/composer/v3/wordpress-muplugin/"
    },
    {
      "type": "composer",
      "url": "https://wpackagist.org"
    }
  ],
  "require": {
    "advanced-custom-fields/advanced-custom-fields-pro": "^5.8.8"
  },
  "extra": {
    "installer-paths": {
      "web/app/mu-plugins/{$name}/": ["type:wordpress-muplugin"]
    }
  }
}
```

2. Installs ACF Pro as plugin in wp-content/plugins/advanced-custom-fields-pro
```json
{
    "name": "example/test",
    "repositories": [
      {
        "type": "composer",
        "url": "https://pivvenit.github.io/acf-composer-bridge/composer/v3/wordpress-plugin/"
      },
      {
        "type": "composer",
        "url": "https://wpackagist.org"
      }
    ],
    "require": {
      "advanced-custom-fields/advanced-custom-fields-pro": "^5.8.8"
    },
    "extra": {
      "installer-paths": {
        "wp-content/plugins/{$name}/": ["type:wordpress-plugin"]
      }
    }
  }
```
