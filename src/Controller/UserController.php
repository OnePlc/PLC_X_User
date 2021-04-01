<?php
/**
 * UserController.php - Main Controller
 *
 * Main Controller User Module
 *
 * @category Controller
 * @package User
 * @author Verein onePlace
 * @copyright (C) 2020 Verein onePlace <admin@1plc.ch>
 * @license https://opensource.org/licenses/BSD-3-Clause
 * @version 1.0.0
 * @since 1.0.0
 */

declare(strict_types=1);

namespace OnePlace\User\Controller;

use Application\Controller\CoreEntityController;
use OnePlace\User\Model\User;
use OnePlace\User\Model\UserTable;
use Application\Controller\CoreController;
use Laminas\View\Model\ViewModel;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\Session\Container;

class UserController extends CoreController
{
    /**
     * User Table Object
     *
     * @var UserTable Gateway to UserTable
     * @since 1.0.0
     */
    private $oTableGateway;

    /**
     * UserController constructor.
     *
     * @param AdapterInterface $oDbAdapter
     * @param UserTable $oTableGateway
     * @param $oServiceManager
     * @since 1.0.0
     */
    public function __construct(AdapterInterface $oDbAdapter, UserTable $oTableGateway, $oServiceManager)
    {
        $this->oTableGateway = $oTableGateway;
        $this->sSingleForm = 'user-single';
        parent::__construct($oDbAdapter, $oTableGateway, $oServiceManager);
    }

    /**
     * User Index
     *
     * @return ViewModel - View Object with Data from Controller
     * @since 1.0.0
     */
    public function indexAction()
    {
        # Check license
        if(!$this->checkLicense('user')) {
            $this->flashMessenger()->addErrorMessage('You have no active license for user');
            $this->redirect()->toRoute('home');
        }

        # Set Layout based on users theme
        $this->setThemeBasedLayout('user');

        # Set Links for Breadcrumb
        $this->layout()->aNavLinks = [
            (object)['label' => 'Users'],
        ];

        # Add Buttons for breadcrumb
        $this->setViewButtons('user-index');

        # Set Table Rows for Index
        $this->setIndexColumns('user-index');

        # Get Paginator
        $aWhere = [];
        if (! CoreEntityController::$oSession->oUser->hasPermission('globaladmin', 'OnePlace-Core')) {
            $aWhere['is_globaladmin'] = 0;
        }
        $oPaginator = $this->oTableGateway->fetchAll(true, $aWhere);
        $iPage = (int) $this->params()->fromQuery('page', 1);
        $iPage = ($iPage < 1) ? 1 : $iPage;
        $oPaginator->setCurrentPageNumber($iPage);

        $iItemsPerPage = (CoreEntityController::$oSession->oUser->getSetting('user-index-items-per-page'))
            ? CoreEntityController::$oSession->oUser->getSetting('user-index-items-per-page') : 10;
        $oPaginator->setItemCountPerPage($iItemsPerPage);

        # set to -1 to disable
        $iSeatsLeft = -1;
        if (isset(CoreController::$oSession->aSeats['user'])) {
            $iLimit = (int)CoreController::$oSession->aSeats['user'];
            $iSeatsUsed = count($this->oTableGateway->fetchAll(false, $aWhere));
            $iSeatsLeft = $iLimit - $iSeatsUsed;
        }

        $aMeasureEnd = getrusage();
        $sTimeOne = $this->rutime($aMeasureEnd, CoreController::$aPerfomanceLogStart, "utime");
        $sTimeTwo = $this->rutime($aMeasureEnd, CoreController::$aPerfomanceLogStart, "stime");
        $this->logPerfomance('user-index', $sTimeOne, $sTimeTwo);

        return new ViewModel([
            'sTableName' => 'user-index',
            'aItems' => $oPaginator,
            'iSeatsLeft' => $iSeatsLeft,
        ]);
    }

    /**
     * User Add Form
     *
     * @return ViewModel - View Object with Data from Controller
     * @since 1.0.0
     */
    public function addAction()
    {
        # Set Layout based on users theme
        $this->setThemeBasedLayout('user');

        # Set Links for Breadcrumb
        $this->layout()->aNavLinks = [
            (object)['label' => 'Users','href' => '/user'],
            (object)['label' => 'Add User'],
        ];

        # Get Request to decide wether to save or display form
        $oRequest = $this->getRequest();

        # check if a licence is set
        $iSeatsLeft = -1;
        if (isset(CoreController::$aGlobalSettings['user-limit'])) {
            $iLimit = CoreController::$aGlobalSettings['user-limit'];
            $iSeatsUsed = count($this->oTableGateway->fetchAll(false));
            # there must be at least 1 seat left
            $iSeatsLeft = $iLimit - $iSeatsUsed;
            if ($iSeatsLeft == 0) {
                # Display Success Message and View New User
                $this->flashMessenger()->addErrorMessage('no seats left');
                return $this->redirect()->toRoute('user');
            }
        }

        # Display Add Form
        if (! $oRequest->isPost()) {
            # Add Buttons for breadcrumb
            $this->setViewButtons('user-single');

            # Load Tabs for Add Form
            $this->setViewTabs($this->sSingleForm);

            # Load Fields for Add Form
            $this->setFormFields($this->sSingleForm);

            # Get User Permissions
            $aPartialData = [
                'aPermissions' => $this->getPermissions(),
            ];
            $this->setPartialData('permissions', $aPartialData);

            # Get User Index Columns
            $aPartialData = [
                'aColumns' => $this->getIndexTablesWithColumns(),
                'aUserColumns' => [],
            ];
            $this->setPartialData('indexcolumns', $aPartialData);

            # Get User Tabs
            $aPartialData = [
                'aTabs' => $this->getFormTabs(),
                'aUserTabs' => [],
            ];
            $this->setPartialData('tabs', $aPartialData);

            # Get User Fields
            $aPartialData = [
                'aFields' => $this->getFormFields(),
                'aUserFields' => [],
            ];
            $this->setPartialData('formfields', $aPartialData);

            # Log Performance in DB
            $aMeasureEnd = getrusage();
            $sTimeOne = $this->rutime($aMeasureEnd, CoreController::$aPerfomanceLogStart, "utime");
            $sTimeTwo = $this->rutime($aMeasureEnd, CoreController::$aPerfomanceLogStart, "stime");
            $this->logPerfomance('user-add', $sTimeOne, $sTimeTwo);

            # Pass Data to View
            return new ViewModel([
                'sFormName' => $this->sSingleForm,
            ]);
        }

        # Get and validate Form Data
        $aFormData = [];
        foreach (array_keys($_REQUEST) as $sKey) {
            $sFieldName = substr($sKey, strlen($this->sSingleForm.'_'));
            switch ($sFieldName) {
                case 'password':
                    $aFormData[$sFieldName] = password_hash($_REQUEST[$sKey], PASSWORD_DEFAULT);
                    break;
                default:
                    $aFormData[$sFieldName] = $_REQUEST[$sKey];
                    break;
            }
        }

        # Save Add Form
        $oUser = new User($this->oDbAdapter);
        $oUser->exchangeArray($aFormData);
        $iUserID = $this->oTableGateway->saveSingle($oUser);
        $oUser = $this->oTableGateway->getSingle($iUserID);

        # Update Permissions
        $aDataPermission = (is_array($_REQUEST[$this->sSingleForm.'-permissions']))
            ? $_REQUEST[$this->sSingleForm.'-permissions'] : [];
        $oUser->updatePermissions($aDataPermission);

        # Update Index Columns
        $aDataIndexColumn = (is_array($_REQUEST[$this->sSingleForm.'-indexcolumns']))
            ? $_REQUEST[$this->sSingleForm.'-indexcolumns'] : [];
        $oUser->updateIndexColumns($aDataIndexColumn);

        # Update Form Tabs
        $aDataTabs = (is_array($_REQUEST[$this->sSingleForm.'-tabs']))
            ? $_REQUEST[$this->sSingleForm.'-tabs'] : [];
        $oUser->updateFormTabs($aDataTabs);

        # Update Form Fields
        $aDataFields = (is_array($_REQUEST[$this->sSingleForm.'-formfields']))
            ? $_REQUEST[$this->sSingleForm.'-formfields'] : [];
        $oUser->updateFormFields($aDataFields);

        # Update Widgets
        $aDataFields = (is_array($_REQUEST[$this->sSingleForm.'-widgets']))
            ? $_REQUEST[$this->sSingleForm.'-widgets'] : [];
        $oUser->updateWidgets($aDataFields);

        # Add XP for creating a new user
        CoreController::$oSession->oUser->addXP('user-add');

        # Log Performance in DB
        $aMeasureEnd = getrusage();
        $sTimeOne = $this->rutime($aMeasureEnd, CoreController::$aPerfomanceLogStart, "utime");
        $sTimeTwo = $this->rutime($aMeasureEnd, CoreController::$aPerfomanceLogStart, "stime");
        $this->logPerfomance('user-save', $sTimeOne, $sTimeTwo);

        # Display Success Message and View New User
        $this->flashMessenger()->addSuccessMessage('User successfully created');
        return $this->redirect()->toRoute('user', ['action' => 'view','id' => $iUserID]);
    }

    /**
     * User View Form
     *
     * @return ViewModel - View Object with Data from Controller
     * @since 1.0.0
     */
    public function viewAction()
    {
        # Set Layout based on users theme
        $this->setThemeBasedLayout('user');

        # Get User ID from route
        $iUserID = $this->params()->fromRoute('id', 0);

        $oUser = $this->oTableGateway->getSingle($iUserID);

        # Attach User Entity to Layout
        $this->setViewEntity($oUser);

        # Add Buttons for breadcrumb
        $this->setViewButtons('user-view');

        # Load Tabs for Add Form
        $this->setViewTabs($this->sSingleForm);

        # Load Fields for Add Form
        $this->setFormFields($this->sSingleForm);

        # Get User Permissions
        $aPartialData = [
            'aPermissions' => $this->getPermissions(),
            'aUserPermissions' => $oUser->getMyPermissions(),
        ];
        $this->setPartialData('permissions', $aPartialData);

        # Get User Index Columns
        $aPartialData = [
            'aColumns' => $this->getIndexTablesWithColumns(),
            'aUserColumns' => $oUser->getMyIndexTablesWithColumns(),
        ];
        $this->setPartialData('indexcolumns', $aPartialData);

        # Get User Tabs
        $aPartialData = [
            'aTabs' => $this->getFormTabs(),
            'aUserTabs' => $oUser->getMyTabs(),
        ];
        $this->setPartialData('tabs', $aPartialData);

        # Get User Widgets
        $aPartialData = [
            'aWidgets' => $this->getWidgets(),
            'aUserWidgets' => $oUser->getMyWidgets(),
        ];
        $this->setPartialData('widgets', $aPartialData);

        # Get User Fields
        $aPartialData = [
            'aFields' => $this->getFormFields(),
            'aUserFields' => $oUser->getMyFormFields(),
        ];
        $this->setPartialData('formfields', $aPartialData);

        # Set Links for Breadcrumb
        $this->layout()->aNavLinks = [
            (object)['label' => 'Users','href' => '/user'],
            (object)['label' => $oUser->getLabel()],
        ];

        # Log Performance in DB
        $aMeasureEnd = getrusage();
        $sTimeOne = $this->rutime($aMeasureEnd, CoreController::$aPerfomanceLogStart, "utime");
        $sTimeTwo = $this->rutime($aMeasureEnd, CoreController::$aPerfomanceLogStart, "stime");
        $this->logPerfomance('user-view', $sTimeOne, $sTimeTwo);

        return new ViewModel([
            'sFormName' => $this->sSingleForm,
            'oUser' => $oUser,
        ]);
    }

    /**
     * User Edit Form
     *
     * @return ViewModel - View Object with Data from Controller
     * @since 1.0.0
     */
    public function editAction()
    {
        # Set Layout based on users theme
        $this->setThemeBasedLayout('user');

        # Get Request to decide wether to save or display form
        $oRequest = $this->getRequest();

        # Display Edit Form
        if (! $oRequest->isPost()) {
            # Get User ID from route
            $iUserID = $this->params()->fromRoute('id', 0);

            # Load User Entity
            $oUser = $this->oTableGateway->getSingle($iUserID);

            # Attach User Entity to Layout
            $this->setViewEntity($oUser);

            # Add Buttons for breadcrumb
            $this->setViewButtons('user-single');

            # Load Tabs for Edit Form
            $this->setViewTabs($this->sSingleForm);

            # Load Fields for Edit Form
            $this->setFormFields($this->sSingleForm);

            # Get User Permissions
            $aPartialData = [
                'aPermissions' => $this->getPermissions(),
                'aUserPermissions' => $oUser->getMyPermissions(),
            ];
            $this->setPartialData('permissions', $aPartialData);

            # Get User Index Columns
            $aPartialData = [
                'aColumns' => $this->getIndexTablesWithColumns(),
                'aUserColumns' => $oUser->getMyIndexTablesWithColumns(),
            ];
            $this->setPartialData('indexcolumns', $aPartialData);

            # Get User Tabs
            $aPartialData = [
                'aTabs' => $this->getFormTabs(),
                'aUserTabs' => $oUser->getMyTabs(),
            ];
            $this->setPartialData('tabs', $aPartialData);

            # Get User Widgets
            $aPartialData = [
                'aWidgets' => $this->getWidgets(),
                'aUserWidgets' => $oUser->getMyWidgets(),
            ];
            $this->setPartialData('widgets', $aPartialData);

            # Get User Fields
            $aPartialData = [
                'aFields' => $this->getFormFields(),
                'aUserFields' => $oUser->getMyFormFields(),
            ];
            $this->setPartialData('formfields', $aPartialData);

            # Set Links for Breadcrumb
            $this->layout()->aNavLinks = [
                (object)['label' => 'Users','href' => '/user'],
                (object)['label' => 'Edit User'],
            ];

            # Log Performance in DB
            $aMeasureEnd = getrusage();
            $sTimeOne = $this->rutime($aMeasureEnd, CoreController::$aPerfomanceLogStart, "utime");
            $sTimeTwo = $this->rutime($aMeasureEnd, CoreController::$aPerfomanceLogStart, "stime");
            $this->logPerfomance('user-edit', $sTimeOne, $sTimeTwo);

            # Pass Data to View
            return new ViewModel([
                'sFormName' => $this->sSingleForm,
                'oUser' => $oUser,
            ]);
        }

        $iUserID = $oRequest->getPost('Item_ID');
        $oUser = $this->oTableGateway->getSingle($iUserID);

        # Get and validate Form Data
        $aFormData = [];
        foreach (array_keys($_REQUEST) as $sKey) {
            $sFieldName = substr($sKey, strlen($this->sSingleForm.'_'));
            switch ($sFieldName) {
                case 'password':
                    //$aFormData[$sFieldName] = password_hash($_REQUEST[$sKey],PASSWORD_DEFAULT);
                    break;
                default:
                    if ($sFieldName != '') {
                        if (! $oUser->setTextField($sFieldName, $_REQUEST[$sKey])) {
                            echo 'could not save field '.$sFieldName;
                        }
                    }
                    break;
            }
        }

        # Save User
        $iUserID = $this->oTableGateway->saveSingle($oUser);

        # Update Permissions
        $aDataPermission = (is_array($_REQUEST[$this->sSingleForm.'-permissions']))
            ? $_REQUEST[$this->sSingleForm.'-permissions'] : [];
        $oUser->updatePermissions($aDataPermission);

        # Update Index Columns
        $aDataIndexColumn = (is_array($_REQUEST[$this->sSingleForm.'-indexcolumns']))
            ? $_REQUEST[$this->sSingleForm.'-indexcolumns'] : [];
        $oUser->updateIndexColumns($aDataIndexColumn);

        # Update Form Tabs
        $aDataTabs = (is_array($_REQUEST[$this->sSingleForm.'-tabs']))
            ? $_REQUEST[$this->sSingleForm.'-tabs'] : [];
        $oUser->updateFormTabs($aDataTabs);

        # Update Form Fields
        $aDataFields = (is_array($_REQUEST[$this->sSingleForm.'-formfields']))
            ? $_REQUEST[$this->sSingleForm.'-formfields'] : [];
        $oUser->updateFormFields($aDataFields);

        # Update Widgets
        $aDataFields = (is_array($_REQUEST[$this->sSingleForm.'-widgets']))
            ? $_REQUEST[$this->sSingleForm.'-widgets'] : [];
        $oUser->updateWidgets($aDataFields);

        # Add XP for managing a user
        CoreController::$oSession->oUser->addXP('user-edit');

        # Log Performance in DB
        $aMeasureEnd = getrusage();
        $sTimeOne = $this->rutime($aMeasureEnd, CoreController::$aPerfomanceLogStart, "utime");
        $sTimeTwo = $this->rutime($aMeasureEnd, CoreController::$aPerfomanceLogStart, "stime");
        $this->logPerfomance('user-save', $sTimeOne, $sTimeTwo);

        # Display Success Message and View New User
        $this->flashMessenger()->addSuccessMessage('User successfully saved');
        return $this->redirect()->toRoute('user', ['action' => 'view','id' => $iUserID]);
    }

    /**
     * Update Sorting for given Table Columns
     *
     * @return string JSON Response
     * @since 1.0.0
     */
    public function updateindexcolumnsortAction()
    {
        # Set JSON Raw Layout
        $this->layout('layout/json');

        # Get Data from Reust
        $oRequest = $this->getRequest();

        # Prepare JSON Answer
        $aReturn = ['state' => 'success','message' => 'nothing todo'];

        if ($oRequest->isPost()) {
            $sTable = $oRequest->getPost('table');
            $aColumns = $oRequest->getPost('columns');

            $iSortID = 0;
            # Loop over all columns provided
            foreach ($aColumns as $sColInfo) {
                # Parse info
                $aInfo = explode('_', $sColInfo);
                $sTable = $aInfo[0];
                $sColumn = substr($sColInfo, strlen($sTable.'_'));

                # Check if table exists
                $oTable = CoreController::$aCoreTables['table-index']->select(['table_name' => $sTable]);
                if (count($oTable) > 0) {
                    # check if field exists
                    $oTable = $oTable->current();
                    $oField = CoreController::$aCoreTables['core-form-field']->select([
                        'form' => $oTable->form,
                        'fieldkey' => $sColumn
                    ]);
                    if (count($oField) > 0) {
                        $oField = $oField->current();

                        # check if column exists for used
                        $oColFound = CoreController::$aCoreTables['table-col']->select([
                            'field_idfs' => $oField->Field_ID,
                            'user_idfs' => CoreController::$oSession->oUser->getID(),
                            'tbl_name' => $sTable
                        ]);

                        # update column sortid
                        if (count($oColFound) > 0) {
                            $oColFound = $oColFound->current();
                            CoreController::$aCoreTables['table-col']->update([
                                'sortID' => $iSortID,
                            ], [
                                'field_idfs' => $oField->Field_ID,
                                'user_idfs' => CoreController::$oSession->oUser->getID(),
                                'tbl_name' => $sTable
                            ]);

                            $aReturn = ['state' => 'success','message' => 'column sorting updated'];

                            $iSortID++;
                        }
                    }
                }
            }
        }

        echo json_encode($aReturn);

        # No View File
        return false;
    }

    public function setthemeAction()
    {
        $sTheme = $this->params()->fromRoute('id', 'default');

        $oThemeTbl = new TableGateway('user', CoreController::$oDbAdapter);
        $oThemeTbl->update(['theme' => $sTheme], ['User_ID' => CoreController::$oSession->oUser->getID()]);
        $this->flashMessenger()->addSuccessMessage('Please login again to see your new theme');

        return $this->redirect()->toRoute('logout');
    }

    public function profileAction()
    {
        # Set Layout based on users theme
        $this->setThemeBasedLayout('user');

        $oRequest = $this->getRequest();
        if($oRequest->isPost()) {
            $iUserID = $oRequest->getPost('user_id');
            $sUserDesc = $oRequest->getPost('user_description');
            $sUserName = $oRequest->getPost('user_fullname');

            if(!is_dir($_SERVER['DOCUMENT_ROOT'].'/data/profile/'.$iUserID)) {
                mkdir($_SERVER['DOCUMENT_ROOT'].'/data/profile/'.$iUserID);
            }

            if(count($_FILES) > 0) {
                move_uploaded_file($_FILES['profile_image']['tmp_name'],$_SERVER['DOCUMENT_ROOT'].'/data/profile/'.$iUserID.'/avatar.png');
            }

            if($sUserName != '') {
                $oUserTbl = new TableGateway('user', CoreController::$oDbAdapter);
                $oUserTbl->update(['full_name' => $sUserName],['User_ID' => $iUserID]);
                CoreController::$oSession->oUser->full_name = $sUserName;
            }

            if($sUserDesc != '') {
                $oUserTbl = new TableGateway('user', CoreController::$oDbAdapter);
                $oUserTbl->update(['description' => $sUserDesc],['User_ID' => $iUserID]);
                CoreController::$oSession->oUser->description = $sUserDesc;
            }

            $this->flashMessenger()->addSuccessMessage('Deine Profil Einstellungen wurden gespeichert');
            return $this->redirect()->toRoute('user', ['action' => 'profile']);
        } else {
            $oUser = CoreController::$oSession->oUser;
            return new ViewModel([
                'oUser' => $oUser,
            ]);
        }
    }

    public function settingsAction()
    {
        # Set Layout based on users theme
        $this->setThemeBasedLayout('user');

        return new ViewModel([]);
    }

    public function updatesettingAction()
    {
        $this->layout('layout/json');

        $oRequest = $this->getRequest();

        # only parse form if its sent by post
        if ($oRequest->isPost()) {
            $sSettingKey = $oRequest->getPost('setting_name');
            $sSettingVal = $oRequest->getPost('setting_value');
            $iUserID = CoreController::$oSession->oUser->getID();

            # get user settings tbl
            $oSettingsTbl = new TableGateway('user_setting', CoreController::$oDbAdapter);
            $oExists = $oSettingsTbl->select([
                'user_idfs' => $iUserID,
                'setting_name' => $sSettingKey,
            ]);
            if (count($oExists) > 0) {
                # Update Setting
                $oSettingsTbl->update([
                    'setting_value' => $sSettingVal,
                ], [
                    'user_idfs' => $iUserID,
                    'setting_name' => $sSettingKey,
                ]);
            } else {
                # Insert setting
                $oSettingsTbl->insert([
                    'setting_value' => $sSettingVal,
                    'user_idfs' => $iUserID,
                    'setting_name' => $sSettingKey,
                ]);
            }
        }

        return false;
    }

    /**
     * User Language Settings
     *
     * @return ViewModel
     * @since 1.0.13
     */
    public function languagesAction()
    {
        $oRequest = $this->getRequest();

        if (! $oRequest->isPost()) {
            # Set Layout based on users theme
            $this->setThemeBasedLayout('user');

            return new ViewModel([]);
        } else {
            # get selected language
            $sLang = $oRequest->getPost('user_language');

            # Update Users Language
            $iUserID = CoreController::$oSession->oUser->getID();
            $this->oTableGateway->updateAttribute('lang', $sLang, 'User_ID', $iUserID);

            # Success Message and back to settings
            $this->flashMessenger()->addSuccessMessage('Please logout to apply new language');
            return $this->redirect()->toRoute('user', ['action' => 'settings']);
        }
    }

    public function copyAction()
    {
        $oRequest = $this->getRequest();

        $iBaseUserID = $this->params()->fromRoute('id', 0);
        $sCustomForm = 'user-copy';

        if (! $oRequest->isPost()) {
            # Set Layout based on users theme
            $this->setThemeBasedLayout('user');

            # Add Buttons for breadcrumb
            $this->setViewButtons($sCustomForm);

            # Load Tabs for Add Form
            $this->setViewTabs($sCustomForm);

            # Load Fields for Add Form
            $this->setFormFields($sCustomForm);

            return new ViewModel([]);
        } else {
            $this->layout('layout/json');

            $oBaseUser = $this->oTableGateway->getSingle($iBaseUserID);

            $sUserName = $_REQUEST[$sCustomForm.'_username'];
            $sFullName = $_REQUEST[$sCustomForm.'_full_name'];
            $sEmail = $_REQUEST[$sCustomForm.'_email'];
            $sPassword = $_REQUEST[$sCustomForm.'_password'];

            $aUserData = [
                'username' => $sUserName,
                'full_name' => $sFullName,
                'email' => $sEmail,
                'password' => password_hash($sPassword, PASSWORD_DEFAULT),
                'lang' => $oBaseUser->getLang(),
                'theme' => $oBaseUser->getTheme(),
            ];

            # Copy User
            $oNewUser = $this->oTableGateway->generateNew();
            $oNewUser->exchangeArray($aUserData);
            $iNewUserID = $this->oTableGateway->saveSingle($oNewUser);

            # Copy Permissions
            $oUserPermTbl = new TableGateway('user_permission', CoreController::$oDbAdapter);
            $aPermissions = $oUserPermTbl->select(['user_idfs' => $iBaseUserID]);
            if(count($aPermissions) > 0) {
                foreach($aPermissions as $oPerm) {
                    $oUserPermTbl->insert([
                        'user_idfs' => $iNewUserID,
                        'permission' => $oPerm->permission,
                        'module' => $oPerm->module,
                    ]);
                }
            }

            # Copy Form Fields
            $aUserFields = CoreController::$aCoreTables['form-field']->select(['user_idfs' => $iBaseUserID]);
            if(count($aUserFields) > 0) {
                foreach($aUserFields as $oField) {
                    CoreController::$aCoreTables['form-field']->insert([
                       'user_idfs' => $iNewUserID,
                       'field_idfs' => $oField->field_idfs,
                       'sort_id' => $oField->sort_id
                    ]);
                }
            }

            # Copy Form Tabs
            $aUserTabs = CoreController::$aCoreTables['form-tab']->select(['user_idfs' => $iBaseUserID]);
            if(count($aUserTabs) > 0) {
                foreach($aUserTabs as $oTab) {
                    CoreController::$aCoreTables['form-tab']->insert([
                        'user_idfs' => $iNewUserID,
                        'tab_idfs' => $oTab->tab_idfs,
                        'sort_id' => $oTab->sort_id
                    ]);
                }
            }

            # Copy Widgets
            $aUserWidgets = CoreController::$aCoreTables['user-widget']->select(['user_idfs' => $iBaseUserID]);
            if(count($aUserWidgets) > 0) {
                foreach($aUserWidgets as $oWidget) {
                    CoreController::$aCoreTables['user-widget']->insert([
                        'user_idfs' => $iNewUserID,
                        'widget_idfs' => $oWidget->widget_idfs,
                        'sort_id' => $oWidget->sort_id
                    ]);
                }
            }

            # Copy Table Columns
            $aUserTblCols = CoreController::$aCoreTables['table-col']->select(['user_idfs' => $iBaseUserID]);
            if(count($aUserTblCols) > 0) {
                foreach($aUserTblCols as $oCol) {
                    CoreController::$aCoreTables['table-col']->insert([
                        'user_idfs' => $iNewUserID,
                        'field_idfs' => $oCol->field_idfs,
                        'width' => $oCol->width,
                        'tbl_name' => $oCol->tbl_name,
                        'sortID' => $oCol->sortID
                    ]);
                }
            }

            # Success Message and back to settings
            $this->flashMessenger()->addSuccessMessage('User copied succuessfully');
            return $this->redirect()->toRoute('user', ['action' => 'view','id' => $iNewUserID]);
        }
    }

    /**
     * Update Module to the latest version
     *
     * @return ViewModel
     * @since 1.0.25
     */
    public function updateAction()
    {
        # Set Layout based on users theme
        $this->setThemeBasedLayout('user');

        $oModTbl = new TableGateway('core_module', CoreController::$oDbAdapter);
        $oCurrentMod = $oModTbl->select([
            'module_key'=>'oneplace-user',
        ])->current();

        $oRequest = $this->getRequest();
        if(! $oRequest->isPost()) {
            return new ViewModel([
                'oCurrentModule' => $oCurrentMod,
            ]);
        }

        $sNewVer = \OnePlace\User\Module::VERSION;

        $sUpdateSQL = \OnePlace\User\Module::getModuleDir().'data/update_'.$oCurrentMod->version.'-'.$sNewVer.'.sql';
        if(file_exists($sUpdateSQL)) {
            $this->parseSQLInstallFile($sUpdateSQL,CoreController::$oDbAdapter);
        }
        $oModTbl->update([
            'version' => $sNewVer,
        ],[
            'module_key'=>'oneplace-user',
        ]);

        $this->flashMessenger()->addSuccessMessage('User Module successfully updated to version '.$sNewVer);

        return $this->redirect()->toRoute('application', ['action' => 'checkforupdates']);
    }

    /**
     * Parse SQL File from Installer and save to database
     *
     * @param string $sFile location of sql file
     * @param AdapterInterface $oAdapter database connection
     * @since 1.0.2.1
     */
    private function parseSQLInstallFile($sFile,$oAdapter) {
        $templine = '';
        $lines = file($sFile);
        // Loop through each line
        foreach ($lines as $line)  {
            if (substr($line, 0, 2) == '--' || $line == '')
                continue;
            // Add this line to the current segment
            $templine .= $line;
            // If it has a semicolon at the end, it's the end of the query
            if (substr(trim($line), -1, 1) == ';')
            {
                $results = $oAdapter->query($templine, $oAdapter::QUERY_MODE_EXECUTE);
                $templine = '';
            }
        }
    }
}
