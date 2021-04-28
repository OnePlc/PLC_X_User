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
use Laminas\Http\ClientStatic;

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
        $oResolver = $this->getEvent()
            ->getApplication()
            ->getServiceManager()
            ->get('Laminas\View\Resolver\TemplatePathStack');

        if (false === $oResolver->resolve('layout/login_custom')) {
            $this->layout('layout/login');
        } else {
            $this->layout('layout/login_custom');
        }

        if(isset($_REQUEST['g-recaptcha-response'])) {
            $response = ClientStatic::post(
                'https://www.google.com/recaptcha/api/siteverify', [
                'secret' => CoreController::$aGlobalSettings['recaptcha-secret-login'],
                'response' => $_REQUEST['g-recaptcha-response']
            ]);

            $iStatus = $response->getStatusCode();
            $sRespnse = $response->getBody();

            $oJson = json_decode($sRespnse);

            if(!$oJson->success) {
                $this->layout()->sErrorMessage = 'Please solve Captcha';
                # Show Login Form
                return new ViewModel([
                    'sErrorMessage' => 'Please solve Captcha',
                ]);
            }
        }

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

            $oMetricTbl = new TableGateway('core_metric', CoreController::$oDbAdapter);

            try {
                # Try Login by E-Mail
                $oUser = $this->oTableGateway->getSingle($sUser, 'email');
            } catch (\Exception $e) {
                try {
                    # Try Login by Username
                    $oUser = $this->oTableGateway->getSingle($sUser, 'username');
                } catch (\Exception $e) {
                    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                        $sIpAddr = $_SERVER['HTTP_CLIENT_IP'];
                    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                        $sIpAddr = $_SERVER['HTTP_X_FORWARDED_FOR'];
                    } else {
                        $sIpAddr = $_SERVER['REMOTE_ADDR'];
                    }
                    $oMetricTbl->insert([
                        'user_idfs' => 0,
                        'action' => 'login',
                        'type' => 'error',
                        'date' => date('Y-m-d H:i:s', time()),
                        'comment' => 'user not found ('.$sUser.' - '.$sIpAddr.' - '.json_encode($_REQUEST).')',
                    ]);
                    $this->layout()->sErrorMessage = $e->getMessage();
                    # Show Login Form
                    return new ViewModel([
                        'sErrorMessage' => $e->getMessage(),
                    ]);
                }
            }

            # Check Password
            $sPasswordForm = $oRequest->getPost('plc_login_pass');
            if (! password_verify($sPasswordForm, $oUser->password)) {
                $oMetricTbl->insert([
                    'user_idfs' => $oUser->getID(),
                    'action' => 'login',
                    'type' => 'error',
                    'date' => date('Y-m-d H:i:s', time()),
                    'comment' => 'wrong password',
                ]);
                $this->layout()->sErrorMessage = 'Wrong password';
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

            $oMetricTbl->insert([
                'user_idfs' => $oUser->getID(),
                'action' => 'login',
                'type' => 'success',
                'date' => date('Y-m-d H:i:s', time()),
                'comment' => '',
            ]);

            if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                $sIpAddr = $_SERVER['HTTP_CLIENT_IP'];
            } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $sIpAddr = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } else {
                $sIpAddr = $_SERVER['REMOTE_ADDR'];
            }
            $oSessTbl = new TableGateway('user_session', CoreController::$oDbAdapter);
            $oCheckSess = $oSessTbl->select([
                'user_idfs' => $oUser->getID(),
                'ipaddress' => strip_tags($sIpAddr),
            ]);
            if(count($oCheckSess) == 0) {
                # todo: add security email check
                $oSessTbl->insert([
                    'user_idfs' => $oUser->getID(),
                    'ipaddress' => strip_tags($sIpAddr),
                    'browser' => json_encode(getallheaders()),
                    'date_created' => date('Y-m-d H:i:s', time()),
                    'date_last_login' => date('Y-m-d H:i:s', time()),
                ]);
            } else {
                $oSessTbl->update([
                    'date_last_login' => date('Y-m-d H:i:s', time()),
                ]);
            }

            $sLoginRoute = (isset(CoreController::$aGlobalSettings['login-route']))
                ? CoreController::$aGlobalSettings['login-route'] : 'app-home';

            return $this->redirect()->toRoute($sLoginRoute);
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

    private function xssCheck($aValsToCheck = [])
    {
        foreach($aValsToCheck as $sVal) {
            $bHasScript = stripos(strtolower($sVal),'script>');
            if($bHasScript === false) {
                $bHasScript = stripos(strtolower($sVal),'src=');
                if($bHasScript === false) {

                } else {
                    # found xss attack
                    return true;
                }
            } else {
                # found xss attack
                return true;
            }
        }

        return false;
    }

    private function snifferCheck($aValsToCheck = [])
    {
        $aBlacklist = ['http:','127.0.0.1','__import__',
            '.popen(','gethostbyname','localtime()','form-data',
            'java.lang','/bin/bash','cmd.exe','org.apache.commons','nginx','?xml','version=',
            'ping -n','WAITFOR DELAY','../','varchar(','exec(','%2F..','..%2F','multipart/'];
        foreach($aValsToCheck as $sVal) {
            foreach($aBlacklist as $sBlack) {
                $bHasBlack = stripos(strtolower($sVal),strtolower($sBlack));
                if($bHasBlack === false) {
                    # all good
                } else {
                    # found blacklisted needle in string
                    return true;
                }
            }
        }

        return false;
    }

    private function sqlinjectCheck($aValsToCheck = [])
    {
        $aBlacklist = ['dblink_connect','user=','(SELECT','SELECT (','select *','union all','and 1',
            'or 1','1=1','2=2',' where ',' all ',' or ',' and '];
        foreach($aValsToCheck as $sVal) {
            foreach($aBlacklist as $sBlack) {
                $bHasBlack = stripos(strtolower($sVal),strtolower($sBlack));
                if($bHasBlack === false) {
                    # all good
                } else {
                    # found blacklisted needle in string
                    return true;
                }
            }
        }

        return false;
    }

    public function signupAction() {
        $oResolver = $this->getEvent()
            ->getApplication()
            ->getServiceManager()
            ->get('Laminas\View\Resolver\TemplatePathStack');

        if (false === $oResolver->resolve('layout/signup_custom')) {
            $this->layout('layout/signup');
        } else {
            $this->layout('layout/signup_custom');
        }

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
                if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                    $sIpAddr = filter_var ($_SERVER['HTTP_CLIENT_IP'], FILTER_SANITIZE_STRING);
                } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                    $sIpAddr = filter_var ($_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_SANITIZE_STRING);
                } else {
                    $sIpAddr = filter_var ($_SERVER['REMOTE_ADDR'], FILTER_SANITIZE_STRING);
                }

                $sEmail = filter_var ( $oRequest->getPost('plc_account_email'), FILTER_SANITIZE_STRING);
                $sPass = filter_var ($oRequest->getPost('plc_account_pass'), FILTER_SANITIZE_STRING);
                $sPassRep = filter_var ($oRequest->getPost('plc_account_pass_rep'), FILTER_SANITIZE_STRING);
                //$iSalutationID = $oRequest->getPost('plc_account_salutation');
                //$sPhone = $oRequest->getPost('plc_account_phone');
                //$sCity = $oRequest->getPost('plc_account_city');
                //$sZIP = $oRequest->getPost('plc_account_zip');
                //$sCompany = $oRequest->getPost('plc_account_company');
                $sLastname = filter_var ($oRequest->getPost('plc_account_lastname'), FILTER_SANITIZE_STRING);

                /**
                 * We don't want attackers to make us any pain
                 */
                $bXSSCheck = $this->xssCheck([$sEmail,$sPass,$sPassRep,$sLastname,$sIpAddr]);
                if($bXSSCheck) {
                    # script tag found !! maybe wants to inject script
                    $oMetricTbl = $this->getCustomTable('core_metric');
                    $oMetricTbl->insert([
                        'user_idfs' => 0,
                        'action' => 'signup-xss-hack',
                        'type' => 'error',
                        'date' => date('Y-m-d H:i:s', time()),
                        'comment' => 'Someone tried to inject a script in username! Input Value: {'.$sEmail.'}, IP:{'.$sIpAddr.'}, HEADER: {'.json_encode(getallheaders()).'}',
                    ]);
                    $this->layout()->sErrorMessage =  'Nice try. Seems like you tried an XSS Attack. Your data is logged and Admin noticed. Try again and you will see what happens. If you think you see this message by error, contact admin@swissfaucet.io';
                    return new ViewModel();
                }

                $bSnifferCheck = $this->snifferCheck([$sEmail,$sPass,$sPassRep,$sLastname,$sIpAddr]);
                if($bSnifferCheck) {
                    # script tag found !! maybe wants to inject script
                    $oMetricTbl = $this->getCustomTable('core_metric');
                    $oMetricTbl->insert([
                        'user_idfs' => 0,
                        'action' => 'signup-sniffer-attack',
                        'type' => 'error',
                        'date' => date('Y-m-d H:i:s', time()),
                        'comment' => 'Someone tried to hack / sniff the server Input Value: {'.$sEmail.'}, IP:{'.$sIpAddr.'}, HEADER: {'.json_encode(getallheaders()).'}',
                    ]);
                    $this->layout()->sErrorMessage =  'Nice try. Seems like you tried to attack us or find out things you should not know. Your data is logged and Admin noticed. Try again and you will see what happens. If you think you see this message by error, contact admin@swissfaucet.io';
                    return new ViewModel();
                }

                $bSqlinjectCheck = $this->sqlinjectCheck([$sEmail,$sPass,$sPassRep,$sLastname,$sIpAddr]);
                if($bSqlinjectCheck) {
                    # script tag found !! maybe wants to inject script
                    $oMetricTbl = $this->getCustomTable('core_metric');
                    $oMetricTbl->insert([
                        'user_idfs' => 0,
                        'action' => 'signup-sql-attack',
                        'type' => 'error',
                        'date' => date('Y-m-d H:i:s', time()),
                        'comment' => 'Someone tried to inject sql Input Value: {'.json_encode([$sEmail,$sPass,$sPassRep,$sLastname,$sIpAddr]).'}, IP:{'.$sIpAddr.'}, HEADER: {'.json_encode(getallheaders()).'}',
                    ]);
                    $this->layout()->sErrorMessage =  'Nice try. Seems like you tried an SQL Injection Attack. Your data is logged and Admin noticed. Try again and you will see what happens. If you think you see this message by error, contact admin@swissfaucet.io';
                    return new ViewModel();
                }

                if(strlen($sLastname) > 50) {
                    $this->layout()->sErrorMessage =  'Username too long';
                    return new ViewModel();
                }

                $bIsEmail = stripos($sEmail,'@');
                if($sEmail == '' || $bIsEmail === false) {
                    $this->layout()->sErrorMessage =  'Please provide a valid email address';
                    return new ViewModel();
                } else {
                    $bCheck = true;
                    # blacklist check
                    if(isset(CoreController::$aGlobalSettings['username-blacklist'])) {
                        $aBlacklist = json_decode(CoreController::$aGlobalSettings['username-blacklist']);
                        foreach($aBlacklist as $sBlackText) {
                            if(stripos(strtolower($sEmail),$sBlackText) === false) {

                            } else {
                                $bCheck = false;
                            }
                        }
                        if(!$bCheck) {
                            $this->layout()->sErrorMessage =  'Please provide a valid email address';
                            return new ViewModel();
                        }
                    }
                    # check if user already exists
                    try {
                        $oUser = $this->oTableGateway->getSingle($sEmail, 'email');
                        $this->layout()->sErrorMessage =  'There is already an account with that e-mail address';
                        return new ViewModel();
                    } catch(\RuntimeException $e) {
                    }
                }

                if($sLastname == '') {
                    $this->layout()->sErrorMessage =  'Please provide a valid username';
                    return new ViewModel();
                } else {
                    $bCheck = true;
                    # blacklist check
                    if(isset(CoreController::$aGlobalSettings['username-blacklist'])) {
                        $aBlacklist = json_decode(CoreController::$aGlobalSettings['username-blacklist']);
                        foreach($aBlacklist as $sBlackText) {
                            if(stripos(strtolower($sLastname),$sBlackText) === false) {

                            } else {
                                $bCheck = false;
                            }
                        }
                        if(!$bCheck) {
                            $this->layout()->sErrorMessage =  'Please provide a valid email username';
                            return new ViewModel();
                        }
                    }
                    # check if user already exists
                    try {
                        $oUser = $this->oTableGateway->getSingle($sLastname, 'email');
                        $this->layout()->sErrorMessage =  'There is already an account with that username';
                        return new ViewModel();
                    } catch(\RuntimeException $e) {
                    }
                }

                if($sPass == '' || $sPassRep == '') {
                    $this->layout()->sErrorMessage =  'Please provide a valid password';
                    return new ViewModel();
                }

                if($sPass !== $sPassRep) {
                    $this->layout()->sErrorMessage =  'Passwords do not match';
                    return new ViewModel();
                }

                if(isset($_REQUEST['g-recaptcha-response'])) {
                    $response = ClientStatic::post(
                        'https://www.google.com/recaptcha/api/siteverify', [
                        'secret' => CoreController::$aGlobalSettings['recaptcha-secret-login'],
                        'response' => $_REQUEST['g-recaptcha-response']
                    ]);

                    $iStatus = $response->getStatusCode();
                    $sRespnse = $response->getBody();

                    $oJson = json_decode($sRespnse);

                    if(!$oJson->success) {
                        $this->layout()->sErrorMessage = 'Please solve Captcha';
                        # Show Login Form
                        return new ViewModel();
                    }
                }

                $bAgree = false;
                if(isset($_REQUEST['plc_account_terms'])) {
                    if($_REQUEST['plc_account_terms'] == 'agree') {
                        $bAgree = true;
                    }
                }
                if(!$bAgree) {
                    $this->layout()->sErrorMessage = 'You must agree to our terms and conditions';
                    # Show Login Form
                    return new ViewModel();
                }

                /**
                 * Create User
                 */
                $sTheme = 'default';
                if(isset(CoreController::$aGlobalSettings['default-theme'])) {
                    $sTheme = CoreController::$aGlobalSettings['default-theme'];
                }
                $sLang = 'en_US';
                if(isset(CoreController::$aGlobalSettings['default-lang'])) {
                    $sLang = CoreController::$aGlobalSettings['default-lang'];
                }
                $oNewUser = $this->oTableGateway->generateNew();
                $aDefSettings = [
                    'lang' => $sLang,
                    'theme' => $sTheme,
                ];
                $aUserData = [
                    'username' => str_replace([' '],['.'],strtolower($sLastname)),
                    'full_name' => $sLastname,
                    'email' => $sEmail,
                    'password' => password_hash($sPass, PASSWORD_DEFAULT),
                ];
                if(isset(CoreController::$oSession->oRefUser)) {
                    $aUserData['ref_user_idfs'] = CoreController::$oSession->oRefUser->getID();
                }
                $aUserData = array_merge($aUserData,$aDefSettings);
                $oNewUser->exchangeArray($aUserData);
                $iNewUserID = $this->oTableGateway->saveSingle($oNewUser);

                $oLoginUser = $this->oTableGateway->getSingle($iNewUserID);

                /**
                 * Add Permissions
                 */
                $aUserPermissions = [
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
                    (object)['name' => ''],
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

                //$oRegisterTbl->delete(['user_email' => $sEmail]);

                # Login Successful - redirect to Dashboard
                CoreController::$oSession->oUser = $oLoginUser;

                $this->flashMessenger()->addSuccessMessage('Account created, please login');

                # Success Message and back to settings
                $sLoginRoute = 'app-home';
                if(isset(CoreController::$aGlobalSettings['login-route'])) {
                    $sLoginRoute = CoreController::$aGlobalSettings['login-route'];
                }

                $oSessTbl = new TableGateway('user_session', CoreController::$oDbAdapter);
                $oSessTbl->insert([
                    'user_idfs' => $oLoginUser->getID(),
                    'ipaddress' => strip_tags($sIpAddr),
                    'browser' => json_encode(getallheaders()),
                    'date_created' => date('Y-m-d H:i:s', time()),
                    'date_last_login' => date('Y-m-d H:i:s', time()),
                ]);
                return $this->redirect()->toRoute($sLoginRoute);
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
