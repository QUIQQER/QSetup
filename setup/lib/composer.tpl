{
    "name" : "quiqqer/setup",
    "description" : "Setup for the QUIQQER system",
    "version" : "dev-master",
    "license" : "GPL-3.0+",

    "authors" : [{
        "name": "Henning Leutz",
        "email": "leutz@pcsg.de",
        "homepage": "http://www.pcsg.de",
        "role": "Developer"
    }, {
        "name": "Moritz Scholz",
        "email": "scholz@pcsg.de",
        "homepage": "http://www.pcsg.de",
        "role": "Developer"
    }],

    "support" : {
        "email": "support@pcsg.de",
        "url": "http://www.quiqqer.com"
    },

    "repositories": [{
        "packagist": false
    }, {
        "type": "composer",
        "url": "http://update.quiqqer.com"
    }, {
        "type": "composer",
        "url": "http://composer.quiqqer.com"
    }],

    "require": {
        "php" : ">=5.3.2",
        "composer/composer": "1.0.*@dev",
        "robloach/component-installer" : "0.0.12",
        "quiqqer/utils" : "dev-master",
        "tedivm/stash" : "0.11.6",
        "phpmailer/phpmailer" : "v5.2.9",
        "symfony/http-foundation" : "2.6.*@dev"
    },

    "minimum-stability": "dev",

    "config": {
        "vendor-dir"    : "{$packages_dir}",
        "cache-dir"     : "{$composer_cache_dir}",
        "component-dir" : "{$packages_dir}bin"
    }
}
