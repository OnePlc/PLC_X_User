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
            return $this->redirect()->toRoute('home');
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

            # Add XP for successful login
            $oUser->addXP('login');

            return $this->redirect()->toRoute('home');
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

        # Back to Login
        return $this->redirect()->toRoute('login');
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

        $sPermission = $this->params()->fromRoute('id', 'Def');

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
            return $this->redirect()->toRoute('home');
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
                'sResetUrl' => $this->getSetting('app-url').'/reset-password/'.$sToken,
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
}
