# Advanced Custom Fields Pro Composer Bridge

[![Build Status](https://dev.azure.com/sklock0564/ACF%20Composer%20Bridge/_apis/build/status/pivvenit.acf-composer-bridge?branchName=master)](https://dev.azure.com/sklock0564/ACF%20Composer%20Bridge/_build/latest?definitionId=1&branchName=master)

This repository acts as a bridge to use the excellent [Advanced Custom Fields Pro](https://www.advancedcustomfields.com/pro/)
Wordpress plugin together with [Composer](getcomposer.org)/[Bedrock](https://roots.io/bedrock/).

## How to install
**1. Add this repository to the repositories field in composer.json**
```json
{
  "type": "composer",
  "url": "https://pivvenit.github.io/acf-composer-bridge/composer/v1/"
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
This repository uses [Azure Pipelines](https://azure.microsoft.com/nl-nl/services/devops/pipelines/) to periodically create a packages.json that references 
the files provided by ACF. Please note that these files require a valid license key that is **not provided** by this repository.
In order to append this license key to the files, [https://github.com/PhilippBaschke/acf-pro-installer](https://github.com/PhilippBaschke/acf-pro-installer) is used.
This installer detects that you want to install advanced custom fields, and then appends the provided private key (via environment variable) to the actual download URL on ACF's servers (so the key is never send to this composer repository).

## Available versions
See [https://pivvenit.github.io/acf-composer-bridge/composer/v1/packages.json](https://pivvenit.github.io/acf-composer-bridge/composer/v1/packages.json)
