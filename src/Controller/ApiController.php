<?php
/**
 * ApiController.php - User Api Controller
 *
 * Main Controller for User Api
 *
 * @category Controller
 * @package User
 * @author Verein onePlace
 * @copyright (C) 2020  Verein onePlace <admin@1plc.ch>
 * @license https://opensource.org/licenses/BSD-3-Clause
 * @version 1.0.0
 * @since 1.0.0
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

class ApiController extends CoreController {
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
    public function __construct(AdapterInterface $oDbAdapter,UserTable $oTableGateway,$oServiceManager) {
        parent::__construct($oDbAdapter,$oTableGateway,$oServiceManager);
        $this->oTableGateway = $oTableGateway;
        $this->sSingleForm = 'apikey-single';
    }

    /**
     * API Home - Main Index
     *
     * @return bool - no View File
     * @since 1.0.0
     */
    public function indexAction() {
        $this->layout('layout/json');

        $aReturn = ['state'=>'success','message'=>'Welcome to onePlace User API'];
        echo json_encode($aReturn);

        return false;
    }

    public function addAction() {
        # Set Layout based on users theme
        $this->setThemeBasedLayout('user');

        # Set Links for Breadcrumb
        $this->layout()->aNavLinks = [
            (object)['label'=>'Users','href'=>'/user'],
            (object)['label'=>'Add Api Key'],
        ];

        # Get Request to decide wether to save or display form
        $oRequest = $this->getRequest();

        # Display Add Form
        if(!$oRequest->isPost()) {
            # Add Buttons for breadcrumb
            $this->setViewButtons('apikey-single');

            # Load Tabs for Add Form
            $this->setViewTabs($this->sSingleForm);

            # Load Fields for Add Form
            $this->setFormFields($this->sSingleForm);

            # Log Performance in DB
            $aMeasureEnd = getrusage();
            $this->logPerfomance('apikey-add',$this->rutime($aMeasureEnd,CoreController::$aPerfomanceLogStart,"utime"),$this->rutime($aMeasureEnd,CoreController::$aPerfomanceLogStart,"stime"));

            # Pass Data to View
            return new ViewModel([
                'sFormName'=>$this->sSingleForm,
            ]);
        }

        # Get and validate Form Data
        $aFormData = [];
        foreach(array_keys($_REQUEST) as $sKey) {
            $sFieldName = substr($sKey,strlen($this->sSingleForm.'_'));
            switch($sFieldName) {
                case 'api_token':
                    $aFormData[$sFieldName] = password_hash($_REQUEST[$sKey],PASSWORD_DEFAULT);
                    break;
                case 'api_key':
                    $aFormData[$sFieldName] = $this->generateKey();
                    break;
                default:
                    $aFormData[$sFieldName] = $_REQUEST[$sKey];
                    break;
            }
        }

        # Save Add Form
        $oApiTbl = CoreController::$oServiceManager->get(ApikeyTable::class);
        $oNewKey = new Apikey($this->oDbAdapter);
        $oNewKey->exchangeArray($aFormData);
        $iNewKeyID = $oApiTbl->saveSingle($oNewKey);
        $oKey = $oApiTbl->getSingle($iNewKeyID);

        # Add XP for creating a new api key
        CoreController::$oSession->oUser->addXP('user-add');

        # Log Performance in DB
        $aMeasureEnd = getrusage();
        $this->logPerfomance('apikey-save',$this->rutime($aMeasureEnd,CoreController::$aPerfomanceLogStart,"utime"),$this->rutime($aMeasureEnd,CoreController::$aPerfomanceLogStart,"stime"));

        # Display Success Message and View New User
        $this->flashMessenger()->addSuccessMessage('Api Key successfully created');
        return $this->redirect()->toRoute('user-api',['action'=>'manage']);
    }

    public function manageAction() {
        # Set Layout based on users theme
        $this->setThemeBasedLayout('user');

        $oApiTbl = CoreController::$oServiceManager->get(ApikeyTable::class);

        # Add Buttons for breadcrumb
        $this->setViewButtons('apikey-index');

        # Set Table Rows for Index
        $this->setIndexColumns('apikey-index');

        return new ViewModel([
            'sTableName'=>'apikey-index',
            'aItems'=>$oApiTbl->fetchAll(true),
        ]);
    }

    /**
     * List all Entities of Skeletons
     *
     * @return bool - no View File
     * @since 1.0.0
     */
    public function listAction() {
        $this->layout('layout/json');

        # Set default values
        $bSelect2 = true;
        $sListLabel = 'username';

        # Get list mode from query
        if(isset($_REQUEST['listmode'])) {
            if($_REQUEST['listmode'] == 'entity') {
                $bSelect2 = false;
            }
        }

        # get list label from query
        if(isset($_REQUEST['listlabel'])) {
            $sListLabel = $_REQUEST['listlabel'];
        }

        /**
         * todo: enforce to use /api/contact instead of /contact/api so we can do security checks in main api controller
        if(!\Application\Controller\ApiController::$bSecurityCheckPassed) {
        # Print List with all Entities
        $aReturn = ['state'=>'error','message'=>'no direct access allowed','aItems'=>[]];
        echo json_encode($aReturn);
        return false;
        }
         **/

        # Init Item List for Response
        $aItems = [];

        $aFields = $this->getFormFields('user-single');
        $aFieldsByKey = [];
        # fields are sorted by tab , we need an index with all fields
        foreach($aFields as $oField) {
            $aFieldsByKey[$oField->fieldkey] = $oField;
        }

        # only allow form fields as list labels
        if(!array_key_exists($sListLabel,$aFieldsByKey)) {
            $aReturn = [
                'state'=>'error',
                'results' => [],
                'message' => 'invalid list label',
            ];

            # Print List with all Entities
            echo json_encode($aReturn);
            return false;
        }

        # Get All Skeleton Entities from Database
        $oItemsDB = $this->oTableGateway->fetchAll(false);
        if(count($oItemsDB) > 0) {
            # Loop all items
            foreach($oItemsDB as $oItem) {

                # Output depending on list mode
                if($bSelect2) {
                    $sVal = null;
                    # get value for list label field
                    switch($aFieldsByKey[$sListLabel]->type) {
                        case 'select':
                            $oTag = $oItem->getSelectField($aFieldsByKey[$sListLabel]->fieldkey);
                            if($oTag) {
                                $sVal = $oTag->getLabel();
                            }
                            break;
                        case 'text':
                        case 'date':
                        case 'textarea':
                        case 'email':
                            $sVal = $oItem->getTextField($aFieldsByKey[$sListLabel]->fieldkey);
                            break;
                        default:
                            break;
                    }
                    $aItems[] = ['id'=>$oItem->getID(),'text'=>$sVal];
                } else {
                    # Init public item
                    $aPublicItem = [];

                    # add all fields to item
                    foreach($aFields as $oField) {
                        switch($oField->type) {
                            case 'multiselect':
                                # get selected
                                $oTags = $oItem->getMultiSelectField($oField->fieldkey);
                                $aTags = [];
                                foreach($oTags as $oTag) {
                                    $aTags[] = ['id'=>$oTag->id,'label'=>$oTag->text];
                                }
                                $aPublicItem[$oField->fieldkey] = $aTags;
                                break;
                            case 'select':
                                # get selected
                                $oTag = $oItem->getSelectField($oField->fieldkey);
                                $aPublicItem[$oField->fieldkey] = ['id'=>$oTag->id,'label'=>$oTag->tag_value];
                                break;
                            case 'text':
                            case 'date':
                            case 'textarea':
                                $aPublicItem[$oField->fieldkey] = $oItem->getTextField($oField->fieldkey);
                                break;
                            default:
                                break;
                        }
                    }

                    # add item to list
                    $aItems[] = $aPublicItem;
                }

            }
        }

        /**
         * Build Select2 JSON Response
         */
        $aReturn = [
            'state'=>'success',
            'results' => $aItems,
            'pagination' => (object)['more'=>false],
        ];

        # Print List with all Entities
        echo json_encode($aReturn);

        return false;
    }

    /**
     * Get a single Entity of Skeleton
     *
     * @return bool - no View File
     * @since 1.0.0
     */
    public function getAction() {
        $this->layout('layout/json');

        # Get Skeleton ID from route
        $iItemID = $this->params()->fromRoute('id', 0);

        # Try to get Skeleton
        try {
            $oItem = $this->oTableGateway->getSingle($iItemID);
        } catch (\RuntimeException $e) {
            # Display error message
            $aReturn = ['state'=>'error','message'=>'User not found','oItem'=>[]];
            echo json_encode($aReturn);
            return false;
        }

        # Print Entity
        $aReturn = ['state'=>'success','message'=>'User found','oItem'=>$oItem];
        echo json_encode($aReturn);

        return false;
    }

    private function generateKey() {
        $string = Rand::getString(32, 'abcdefghijklmnopqrstuvwxyz1234567890');

        $sKey = strtoupper(substr($string, 0, 4)) . '-' . strtoupper(substr($string, 4, 4));
        $sKey .= '-' . strtoupper(substr($string, 8, 4)) . '-' . strtoupper(substr($string, 12, 4));
        $sKey .= '-' . strtoupper(substr($string, 16, 4)) . '-' . strtoupper(substr($string, 20, 4));
        $sKey .= '-' . strtoupper(substr($string, 24, 4)) . '-' . strtoupper(substr($string, 28, 4));

        return $sKey;
    }
}
