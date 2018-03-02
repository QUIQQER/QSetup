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
      "packagist": false
    },
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
    "php": ">=5.5",
    "hirak\/prestissimo": "^0.3",
    "pcsg\/composer-assets": "dev-master"
  },
  "minimum-stability": "dev",
  "config": {
    "preferred-install": "dist",
    "vendor-dir": "",
    "cache-dir": "",
    "component-dir": "",
    "quiqqer-dir": "",
    "symlink": false
  },
  "options": {
    "symlink": false
  },
  "extra": {
    "asset-installer-paths": {
      "npm-asset-library": "",
      "bower-asset-library": ""
    },
    "asset-registry-options": {
      "npm": false,
      "bower": false,
      "npm-searchable": false,
      "bower-searchable": false
    },
    "asset-custom-npm-registries": {
      "npm.quiqqer.com": "https:\/\/npm.quiqqer.com\/"
    }
  }
}
