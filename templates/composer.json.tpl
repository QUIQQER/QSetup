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
    "hirak\/prestissimo": "^0.3",
    "fxp/composer-asset-plugin": "*"
  },
  "minimum-stability": "dev",
  "preferred-install": "dist",
  "config": {
    "vendor-dir": "",
    "cache-dir": "",
    "component-dir": "",
    "quiqqer-dir": "",
    "secure-http": false,
    "symlink": false
  },
  "options": {
    "symlink": false
  },
  "extra": {
    "asset-installer-paths": {
      "npm-asset-library": "",
      "bower-asset-library": ""
    }
  }
}
