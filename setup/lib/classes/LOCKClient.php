<?php

/**
 * This file contains \QUI\LOCKClient
 */

namespace QUI;

use QUI;

/**
 * Class LOCKClient
 *
 * @package quiqqer/quiqqer
 */
class LOCKClient extends QUI\QDOM
{
    /**
     * @param array $params
     */
    public function __construct($params = array())
    {
        $this->setAttributes(array(
            'composerJsonFile' => '',
            'lockServer'       => 'https://lock.quiqqer.com'
        ));

        $this->setAttributes($params);
    }

    /**
     * Update the composer.lock file via a composerLOCK server
     *
     * @param String|Bool $package - optional, name of the package
     *
     * @return String
     * @throws QUI\Exception
     */
    public function getComposerLock($package = false)
    {
        $lockServer = $this->getAttribute('lockServer');
        $file = $this->getAttribute('composerJsonFile');

        if (empty($lockServer)) {
            throw new QUI\Exception('Unknown LOCK Server', 400);
        }

        if (!file_exists($file)) {
            throw new QUI\Exception('Composer file not found', 400);
        }


        $postFields = array(
            'composerJson' => file_get_contents($file)
        );

        if ($package) {
            $postFields['package'] = $package;
        }


        $Curl = QUI\Utils\Request\Url::Curl($lockServer.'/v1/update', array(
            \CURLOPT_POST       => 1,
            \CURLOPT_POSTFIELDS => $postFields
        ));

        $result = QUI\Utils\Request\Url::exec($Curl);
        $info = curl_getinfo($Curl);


        if ($info['http_code'] == 200) {
            return $result;
        }

        throw new QUI\Exception($result, 400);
    }
}
