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

use OnePlace\User\Model\UserTable;
use Application\Controller\CoreController;
use Laminas\View\Model\ViewModel;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\Session\Container;
use Laminas\Math\Rand;

class AuthController extends CoreController
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
     * User Login
     *
     * @return ViewModel|Request - View Object with Data from Controller|Redirect to Login
     * @since 1.0.0
     */
    public function loginAction()
    {
        $this->layout('layout/login');

        # Check if user is already logged in
        if (isset(CoreController::$oSession->oUser)) {
            // already logged in
            return $this->redirect()->toRoute('app-home');
        }

        # Get current Request - if post - perform login - otherwise show for,m
        $oRequest = $this->getRequest();
        if ($oRequest->isPost()) {
            # Get User from Login Form
            $sUser = $oRequest->getPost('plc_login_user');

            try {
                # Try Login by E-Mail
                $oUser = $this->oTableGateway->getSingle($sUser, 'email');
            } catch (\Exception $e) {
                try {
                    # Try Login by Username
                    $oUser = $this->oTableGateway->getSingle($sUser, 'username');
                } catch (\Exception $e) {
                    # Show Login Form
                    return new ViewModel([
                        'sErrorMessage' => $e->getMessage(),
                    ]);
                }
            }

            # Check Password
            $sPasswordForm = $oRequest->getPost('plc_login_pass');
            if (! password_verify($sPasswordForm, $oUser->password)) {
                # Show Login Form
                return new ViewModel([
                    'sErrorMessage' => 'Wrong password',
                ]);
            }

            # Login Successful - redirect to Dashboard
            CoreController::$oSession->oUser = $oUser;

            # Check if it is first login
            $oPermTbl = new TableGateway('user_permission', CoreController::$oDbAdapter);
            $aPerms = $oPermTbl->select();
            if(count($aPerms) == 0) {
                $aBasePerms = CoreController::$aCoreTables['permission']->select([
                    'needs_globaladmin' => 0
                ]);
                if(count($aBasePerms) > 0) {
                    foreach($aBasePerms as $oPerm) {
                        $oPermTbl->insert([
                            'user_idfs' => $oUser->getID(),
                            'permission' => $oPerm->permission_key,
                            'module' => $oPerm->module,
                        ]);
                    }
                }
            }

            # Check if it is first login
            $aTabs = CoreController::$aCoreTables['form-tab']->select();
            if(count($aTabs) == 0) {
                $aBaseTabs = CoreController::$aCoreTables['core-form-tab']->select([]);
                if(count($aBaseTabs) > 0) {
                    $iSortID = 0;
                    foreach($aBaseTabs as $oTab) {
                        CoreController::$aCoreTables['form-tab']->insert([
                            'user_idfs' => $oUser->getID(),
                            'tab_idfs' => $oTab->Tab_ID,
                            'sort_id' => $iSortID,
                        ]);
                        $iSortID++;
                    }
                }
            }

            # Check if it is first login
            $aFields = CoreController::$aCoreTables['form-field']->select();
            if(count($aFields) == 0) {
                $aBaseFields = CoreController::$aCoreTables['core-form-field']->select([]);
                if(count($aBaseFields) > 0) {
                    $iSortID = 0;
                    foreach($aBaseFields as $oField) {
                        CoreController::$aCoreTables['form-field']->insert([
                            'user_idfs' => $oUser->getID(),
                            'field_idfs' => $oField->Field_ID,
                            'sort_id' => $iSortID,
                        ]);
                        $iSortID++;
                    }
                }
            }

            # Check if it is first login
            $aList = CoreController::$aCoreTables['table-col']->select();
            if(count($aFields) == 0) {
                $aBaseLists = CoreController::$aCoreTables['table-index']->select();
                if(count($aBaseLists) > 0) {
                    foreach($aBaseLists as $oList) {
                        $aListFields = CoreController::$aCoreTables['core-form-field']->select(['form' => $oList->form]);
                        if(count($aListFields) > 0) {
                            $iSortID = 0;
                            foreach($aListFields as $oListField) {
                                if($iSortID == 5) {
                                    break;
                                }
                                CoreController::$aCoreTables['table-col']->insert([
                                    'user_idfs' => $oUser->getID(),
                                    'tbl_name' => $oList->table_name,
                                    'field_idfs' => $oListField->Field_ID,
                                    'sortID' => $iSortID,
                                    'width' => 'col-m-2',
                                ]);
                                $iSortID++;
                            }
                        }
                    }
                }
            }

            # Add XP for successful login
            $oUser->addXP('login');

            return $this->redirect()->toRoute('app-home');
        } else {
            # Show Login Form
            return new ViewModel();
        }
    }

    /**
     * User Logout
     *
     * @return Request - Redirect to Login
     * @since 1.0.0
     */
    public function logoutAction()
    {
        # Remove User from Session
        unset(CoreController::$oSession->oUser);
        unset(CoreController::$oSession->aLicences);
        unset(CoreController::$oSession->aSeats);

        # Back to Login
        return $this->redirect()->toRoute('app-home');
    }

    /**
     * Denied - No Permission Page
     *
     * @return ViewModel - View Object with Data from Controller
     * @since 1.0.0
     */
    public function deniedAction()
    {
        # Set Layout based on users theme
        $this->setThemeBasedLayout('user');

        $sPermission = $this->params()->fromRoute('permission', 'Def');

        return new ViewModel([
            'sPermission' => $sPermission,
        ]);
    }

    /**
     * Token based login for Google and stuff
     *
     * @return bool no View File
     * @since 1.0.1
     */
    public function tokenloginAction()
    {
        $this->layout('layout/json');

        $sToken = $_REQUEST['idtoken'];

        $client = new \Google_Client(['client_id' => '865195135178-9riktlnk2jdknebbdj030j9nq7gdimvt.apps.googleusercontent.com']);  // Specify the CLIENT_ID of the app that accesses the backend
        $payload = $client->verifyIdToken($sToken);
        if ($payload) {
            $sUserEmail = $payload['email'];
            # Try Login by E-Mail
            $oUser = $this->oTableGateway->getSingle($sUserEmail, 'email');
            # Login Successful - redirect to Dashboard
            CoreController::$oSession->oUser = $oUser;
            //var_dump($payload);
            $userid = $payload['sub'];
            // If request specified a G Suite domain:
            //$domain = $payload['hd'];
            echo 'is good for '.$userid;
            return $this->redirect()->toRoute('app-home');
        } else {
            // Invalid ID token
            echo 'not good';
        }

        return false;
    }

    /**
     * Password forgot form
     *
     * @return ViewModel ViewModel with data
     * @since 1.0.2
     */
    public function forgotAction()
    {
        $this->layout('layout/login');

        # Get Request
        $oRequest = $this->getRequest();

        # Show forgot form if GET, parse form if POST
        if (! $oRequest->isPost()) {
            # Show forgot form
            return new ViewModel([]);
        } else {
            # Get User / E-Mail from Form
            $sUser = $oRequest->getPost('plc_login_user');

            $bIsEmailAddress = stripos($sUser, '@');
            $oUser = false;
            if ($bIsEmailAddress === false) {
                # its a username
                # Check if we find user by username
                try {
                    $oUser = $this->oTableGateway->getSingle($sUser, 'username');
                } catch (\RuntimeException $e) {
                    # user not found
                    # Show forgot form
                    return new ViewModel([
                        'sErrorMessage' => 'User not found',
                    ]);
                }
            } else {
                # Check if we find user by email
                try {
                    $oUser = $this->oTableGateway->getSingle($sUser, 'email');
                } catch (\RuntimeException $e) {
                    # user not found
                    # Show forgot form
                    return new ViewModel([
                        'sErrorMessage' => 'User not found',
                    ]);
                }
            }

            # Generate Token
            $sToken = Rand::getString(32, '0123456789abcdefghijklmnopqrstuvwxyz', true);

            # Encrypt Token
            $sTokenHash = password_hash($sToken, PASSWORD_BCRYPT);

            # Save Token to Database
            $oUser->setPasswordResetToken($sTokenHash);

            # Send E-Mail
            $this->sendEmail('one-place/user/email/reset-password', [
                'sResetUrl' => $this->getSetting('app-url').'/reset-password/'.$oUser->username.'/'.$sToken,
                'sUserName' => $oUser->getLabel(),
                'sInstallInfo' => 'onePlace'
            ], $oUser->getTextField('email'), $oUser->getTextField('full_name'), 'Password Reset');

            # Display Success Messages
            return new ViewModel([
                'sSuccessMessage' => 'E-Mail sent. Please check your inbox',
            ]);
        }
    }

    /**
     * Set new password form
     *
     * @return ViewModel
     * @since 1.0.3
     */
    public function resetAction()
    {
        $this->layout('layout/login');

        $oRequest = $this->getRequest();

        if (! $oRequest->isPost()) {
            $sToken = $this->params()->fromRoute('token', 'none');
            $sUser = $this->params()->fromRoute('username', 'none');

            # Try to get user with token
            try {
                $oUser = $this->oTableGateway->getSingle($sUser, 'username');
            } catch (\RuntimeException $e) {
                # Display Success Messages
                return new ViewModel([
                    'sErrorMessage' => 'user '.$sUser.' not found',
                ]);
            }

            # Verify token
            if (password_verify($sToken, $oUser->password_reset_token)) {
                # Token shall be valid for only 48 hours
                if (strtotime($oUser->password_reset_date) + (3600 * 48) >= time()) {
                    # Display password reset form
                    return new ViewModel([
                        'iUserID' => $oUser->getID(),
                        'sToken' => $sToken,
                    ]);
                } else {
                    # Display Error Message
                    return new ViewModel([
                        'sErrorMessage' => 'link is only valid for 48h. please generate a new one.',
                    ]);
                }
            } else {
                # Display Error Message
                return new ViewModel([
                    'sErrorMessage' => 'invalid token',
                ]);
            }
        } else {
            # Get data from reset form
            $sPass = $oRequest->getPost('plc_login_pass');
            $sPassCheck = $oRequest->getPost('plc_login_pass_repeat');
            $sToken = $oRequest->getPost('plc_login_token');
            $iUserID = $oRequest->getPost('plc_login_user');

            # Compare passwords
            if ($sPass != $sPassCheck) {
                # Display Error Message
                return new ViewModel([
                    'sErrorMessage' => 'password do not match',
                ]);
            }

            # Try to get user with token
            try {
                $oUser = $this->oTableGateway->getSingle($iUserID, 'User_ID');
            } catch (\RuntimeException $e) {
                # Display Error Message
                return new ViewModel([
                    'sErrorMessage' => 'user not found',
                ]);
            }

            # Verify token
            if (password_verify($sToken, $oUser->password_reset_token)) {
                # Set new password
                $oUser->setTextField('password', password_hash($sPass, PASSWORD_BCRYPT));
                $this->oTableGateway->saveSingle($oUser);

                $sSuccess = 'New password set. You can now <a href="/login">login</a> with your new password.';
                # Display Success Messages
                return new ViewModel([
                    'sSuccessMessage' => $sSuccess,
                ]);
            } else {
                # Display Success Messages
                return new ViewModel([
                    'sErrorMessage' => 'invalid token',
                ]);
            }
        }
    }

    public function signupAction() {
        $this->layout('layout/signup');

        $oRequest = $this->getRequest();

        if (! $oRequest->isPost()) {
            $oFound = false;
            $sToken = $this->params()->fromRoute('token', '');
            $bShowForm = false;
            $aExtraData = [];
            if($sToken != '') {
                $oRegisterTbl = new TableGateway('user_registration', CoreController::$oDbAdapter);
                $oFound = $oRegisterTbl->select(['user_token' => $sToken]);
                if(count($oFound) > 0) {
                    $oFound = $oFound->current();
                    $oSalutTag = CoreController::$aCoreTables['core-tag']->select(['tag_key' => 'salutation']);
                    if(count($oSalutTag) > 0) {
                        $oSalutTag = $oSalutTag->current();
                        $aSalutsDB = CoreController::$aCoreTables['core-entity-tag']->select([
                            'tag_idfs' => $oSalutTag->Tag_ID,
                            'entity_form_idfs' => 'contact-single',
                        ]);
                        $aSaluts = [];
                        if(count($aSalutsDB) > 0) {
                            foreach($aSalutsDB as $oSal) {
                                $aSaluts[] = (object)['id' => $oSal->Entitytag_ID, 'text' => $oSal->tag_value];
                            }
                        }
                        $oFound->aSalutations = $aSaluts;
                    }
                    $bShowForm = true;
                } else {
                    $aExtraData['sErrorMessage'] = 'Invalid token.';
                }
            }
            $aViewData = array_merge([
                'sToken' => $sToken,
                'bShowForm' => $bShowForm,
                'oContact' => $oFound
            ],$aExtraData);
            # Display Success Messages
            return new ViewModel($aViewData);
        } else {
            /**
             * Registration Step 2
             */
            if(isset($_REQUEST['plc_account_pass'])) {
                $sEmail = $oRequest->getPost('plc_account_email');
                $sPass = $oRequest->getPost('plc_account_pass');
                $sPassRep = $oRequest->getPost('plc_account_pass_rep');
                //$iSalutationID = $oRequest->getPost('plc_account_salutation');
                //$sPhone = $oRequest->getPost('plc_account_phone');
                //$sCity = $oRequest->getPost('plc_account_city');
                //$sZIP = $oRequest->getPost('plc_account_zip');
                //$sCompany = $oRequest->getPost('plc_account_company');
                $sLastname = $oRequest->getPost('plc_account_lastname');
                //$sFirstname = $oRequest->getPost('plc_account_firstname');
                //$sStreet = $oRequest->getPost('plc_account_street');
                //$sStreetNr = $oRequest->getPost('plc_account_street_nr');

                /**
                 * Create User
                 */
                $oNewUser = $this->oTableGateway->generateNew();
                $aDefSettings = [
                    'lang' => 'de_DE',
                    'theme' => 'vuze',
                ];
                $aUserData = [
                    'username' => str_replace([' '],['.'],strtolower($sLastname)),
                    'full_name' => $sLastname,
                    'email' => $sEmail,
                    'password' => password_hash($sPass, PASSWORD_DEFAULT),
                ];
                $aUserData = array_merge($aUserData,$aDefSettings);
                $oNewUser->exchangeArray($aUserData);
                $iNewUserID = $this->oTableGateway->saveSingle($oNewUser);

                if(isset($_FILES['plc_account_profile'])) {
                    if(!is_dir($_SERVER['DOCUMENT_ROOT'].'/data/profile/'.$iNewUserID)) {
                        mkdir($_SERVER['DOCUMENT_ROOT'].'/data/profile/'.$iNewUserID);
                    }
                    move_uploaded_file($_FILES['plc_account_profile']['tmp_name'],$_SERVER['DOCUMENT_ROOT'].'/data/profile/'.$iNewUserID.'/avatar.png');
                }

                $oLoginUser = $this->oTableGateway->getSingle($iNewUserID);

                /**
                 * Add Permissions
                 */
                $aUserPermissions = [
                    (object)['permission' => 'index', 'module' => 'Application\Controller\IndexController'],
                    (object)['permission' => 'profile', 'module' => 'OnePlace\User\Controller\UserController'],
                    (object)['permission' => 'upgrade', 'module' => 'OnePlace\Stockchart\Controller\StockchartController'],
                ];
                $oUserPermTbl = new TableGateway('user_permission', CoreController::$oDbAdapter);
                $oRegisterTbl = new TableGateway('user_registration', CoreController::$oDbAdapter);
                foreach($aUserPermissions as $oPerm) {
                    $oUserPermTbl->insert([
                        'user_idfs' => $iNewUserID,
                        'permission' => $oPerm->permission,
                        'module' => $oPerm->module,
                    ]);
                }

                /**
                 * Add Widgets
                 */
                $aUserWidgets = [
                    (object)['name' => 'echoapp_start'],
                ];
                $oUserWidgetTbl = new TableGateway('core_widget_user', CoreController::$oDbAdapter);
                $oWidgetTbl = new TableGateway('core_widget', CoreController::$oDbAdapter);
                $iSortID = 0;
                foreach($aUserWidgets as $oUserWidget) {
                    $oWidget = $oWidgetTbl->select(['widget_name' => $oUserWidget->name]);
                    if(count($oWidget) > 0) {
                        $oWidget = $oWidget->current();
                        $oUserWidgetTbl->insert(['user_idfs' => $iNewUserID, 'widget_idfs' => $oWidget->Widget_ID, 'sort_id' => $iSortID]);
                        $iSortID++;
                    }
                }

                $oRegisterTbl->delete(['user_email' => $sEmail]);

                # Login Successful - redirect to Dashboard
                CoreController::$oSession->oUser = $oLoginUser;

                # Success Message and back to settings
                return $this->redirect()->toRoute('home');
            } else {
                /**
                 * Registration Step 1
                 */
                $sEmail = $oRequest->getPost('plc_login_email');
                $sRegisterToken = str_replace(['$','/','.'],[],password_hash($sEmail, PASSWORD_DEFAULT));

                $oRegisterTbl = new TableGateway('user_registration', CoreController::$oDbAdapter);
                $oAlreadyReg = $oRegisterTbl->select(['user_email' => $sEmail]);
                if(count($oAlreadyReg) > 0) {
                    # Display Success Messages
                    return new ViewModel([
                        'sErrorMessage' => 'You already have a pending request.',
                    ]);
                } else {
                    $oRegisterTbl->insert([
                        'user_token' => $sRegisterToken,
                        'user_email' => $sEmail,
                        'created_date' => date('Y-m-d H:i:s', time()),
                    ]);

                    # Send E-Mail
                    $this->sendEmail('email/user/register-init', [
                        'sResetUrl' => $this->getSetting('app-url').'/signup/'.$sRegisterToken,
                        'sInstallInfo' => 'onePlace',
                        'sWelcomeText' => CoreController::$aGlobalSettings['register-welcome'],
                        'sPrivacyLink' => $this->getSetting('app-url').'/datenschutz',
                        'sEmailTitle' => CoreController::$aGlobalSettings['signup-email-subject'],
                    ], $sEmail, $sEmail, CoreController::$aGlobalSettings['signup-email-subject']);

                    # Display Success Messages
                    return new ViewModel([
                        'successMessage' => 'Vielen Dank für dein Interesse. Wir haben dir eine E-Mail geschickt, mit der du die Registrierung abschliessen kannst.',
                    ]);
                }
            }
        }
    }
}
