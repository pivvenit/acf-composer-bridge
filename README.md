# Advanced Custom Fields Pro Composer Bridge

[![CircleCI](https://circleci.com/gh/pivvenit/acf-composer-bridge.svg?style=svg)](https://circleci.com/gh/pivvenit/acf-composer-bridge)

This repository acts as a bridge to use the excellent [Advanced Custom Fields Pro](https://www.advancedcustomfields.com/pro/)
Wordpress plugin together with [Composer](https://getcomposer.org)/[Bedrock](https://roots.io/bedrock/).

## How to install
**1. Add the desired repository to the repositories field in composer.json**

Select one of the following repositories based on the desired plugin type:

***Wordpress Packagist plugin***

Use this version if you are unsure which version to use.
```json
{
  "type": "composer",
  "url": "https://pivvenit.github.io/acf-composer-bridge/composer/v2/wpackagist-plugin/"
}
```

***Wordpress Packagist Must-Use plugin***

Use this version if you want ACF installed as MU-plugin.
```json
{
  "type": "composer",
  "url": "https://pivvenit.github.io/acf-composer-bridge/composer/v2/wpackagist-muplugin/"
}
```

***Wordpress Legacy Packagist plugin***

Use this repository URL if you use the legacy `wordpress-plugin` plugin type.
```json
{
  "type": "composer",
  "url": "https://pivvenit.github.io/acf-composer-bridge/composer/v2/wordpress-plugin/"
}
```

***As regular composer dependency***

To install the plugin in the `vendor` directory.

```json
{
  "type": "composer",
  "url": "https://pivvenit.github.io/acf-composer-bridge/composer/v2/library/"
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
This repository uses [CircleCI](https://circleci.com/gh/pivvenit/acf-composer-bridge/) to periodically create a packages.json that references 
the files provided by ACF. Please note that these files require a valid license key that is **not provided** by this repository.
In order to append this license key to the files, [https://github.com/pivvenit/acf-pro-installer](https://github.com/pivvenit/acf-pro-installer) is used.
This installer detects that you want to install advanced custom fields, and then appends the provided private key (via environment variable) to the actual download URL on ACF's servers (so the key is never send to this composer repository).

## Available versions
See [https://pivvenit.github.io/acf-composer-bridge/composer/v2/wpackagist-plugin/packages.json](https://pivvenit.github.io/acf-composer-bridge/composer/v2/packages.json)
