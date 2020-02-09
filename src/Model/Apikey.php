<?php
/**
 * Apikey.php - Apikey Entity
 *
 * Entity Model for Apikey
 *
 * @category Model
 * @package User
 * @author Verein onePlace
 * @copyright (C) 2020 Verein onePlace <admin@1plc.ch>
 * @license https://opensource.org/licenses/BSD-3-Clause
 * @version 1.0.0
 * @since 1.0.0
 */

namespace OnePlace\User\Model;

use Application\Controller\CoreController;
use Application\Model\CoreEntityModel;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\Db\Sql\Select;

class Apikey extends CoreEntityModel {
    /**
     * User E-Mail Address
     *
     * @var string
     * @since 1.0.0
     */
    public $api_key;

    /**
     * User Full Name
     *
     * @var string
     * @since 1.0.0
     */
    public $api_token;

    /**
     * User constructor.
     *
     * @param AdapterInterface $oDbAdapter Database Connection
     * @since 1.0.0
     */
    public function __construct($oDbAdapter) {
        parent::__construct($oDbAdapter);
    }

    /**
     * Get Object Data from Array
     *
     * @param array $data
     * @since 1.0.0
     */
    public function exchangeArray(array $data) {
        $this->id = !empty($data['Apikey_ID']) ? $data['Apikey_ID'] : 0;
        $this->api_key = !empty($data['api_key']) ? $data['api_key'] : '';
        $this->api_token = !empty($data['api_token']) ? $data['api_token'] : '';
    }

    /**
     * Return Name
     *
     * @return string
     * @since 1.0.0
     */
    public function getLabel() {
        return $this->api_key;
    }
}