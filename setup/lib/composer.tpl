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
        "type": "composer",
        "url": "http://update.quiqqer.com"
    }],

    "require": {
        "php" : ">=5.3.2",
        "quiqqer/quiqqer" : "dev-master"
    },

    "config": {
        "vendor-dir" : "{$packages_dir}",
        "cache-dir"  : "{$composer_cache_dir}"
    }
}
