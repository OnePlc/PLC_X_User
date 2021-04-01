<?php
/**
 * FirewallController.php - Firewall Controller
 *
 * Main Controller for Application Firewall Management
 *
 * @category Controller
 * @package User
 * @author Verein onePlace
 * @copyright (C) 2021  Verein onePlace <admin@1plc.ch>
 * @license https://opensource.org/licenses/BSD-3-Clause
 * @version 1.0.0
 * @since 1.0.24
 */

declare(strict_types=1);

namespace OnePlace\User\Controller;

use Application\Controller\CoreController;
use OnePlace\User\Model\Apikey;
use OnePlace\User\Model\ApikeyTable;
use OnePlace\User\Model\UserTable;
use Laminas\View\Model\ViewModel;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\Paginator\Paginator;
use Laminas\Paginator\Adapter\DbSelect;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Where;
use Laminas\Math\Rand;

class FirewallController extends CoreController
{
    /**
     * Skeleton Table Object
     *
     * @since 1.0.0
     */
    private $oTableGateway;

    /**
     * ApiController constructor.
     *
     * @param AdapterInterface $oDbAdapter
     * @param UserTable $oTableGateway
     * @since 1.0.0
     */
    public function __construct(AdapterInterface $oDbAdapter, UserTable $oTableGateway, $oServiceManager)
    {
        parent::__construct($oDbAdapter, $oTableGateway, $oServiceManager);
        $this->oTableGateway = $oTableGateway;
        $this->sSingleForm = 'firewall-single';
    }

    /**
     * Firewall Home - Main Index
     *
     * @return bool - no View File
     * @since 1.0.0
     */
    public function indexAction()
    {
        $this->setThemeBasedLayout('firewall');

        return new ViewModel([]);
    }
}
