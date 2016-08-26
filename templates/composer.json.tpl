{
  "name": "quiqqer/setup",
  "description": "Setup for the QUIQQER system",
  "version": "dev-master",
  "license": "GPL-3.0+",
  "authors": [
    {
      "name": "Henning Leutz",
      "email": "leutz@pcsg.de",
      "homepage": "http://www.pcsg.de",
      "role": "Developer"
    },
    {
      "name": "Moritz Scholz",
      "email": "scholz@pcsg.de",
      "homepage": "http://www.pcsg.de",
      "role": "Developer"
    }
  ],
  "support": {
    "email": "support@pcsg.de",
    "url": "http://www.quiqqer.com"
  },
  "repositories": [
    {
      "type": "composer",
      "url": "https:\/\/update.quiqqer.com\/"
    },
    {
      "type": "composer",
      "url": "https:\/\/composer.quiqqer.com\/"
    }
  ],
  "require": {
    "php": ">=5.3.2",
    "hirak/prestissimo":"^0.3",
    "composer/composer": "^1.1.3",
    "robloach/component-installer": "0.0.12",
    "quiqqer/utils": "dev-dev",
    "tedivm/stash": "0.11.6",
    "symfony/http-foundation": "2.6.4",
    "symfony/console": "2.5",
    "fxp/composer-asset-plugin": "^1.1"
  },
  "minimum-stability": "dev",
  "preferred-install": "dist",
  "config": {
    "vendor-dir": "",
    "cache-dir": "",
    "component-dir": "",
    "quiqqer-dir": "",
    "secure-http": false
  }
}
