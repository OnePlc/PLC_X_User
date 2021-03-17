<?php
/**
 * User.php - User Entity
 *
 * Entity Model for User
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

class User extends CoreEntityModel
{
    /**
     * User E-Mail Address
     *
     * @var string
     * @since 1.0.0
     */
    public $email;

    /**
     * User Full Name
     *
     * @var string
     * @since 1.0.0
     */
    public $full_name;

    /**
     * User Profile Image
     *
     * @var string $featured_image
     * @since 1.0.20
     */
    public $featured_image;

    /**
     * Username
     *
     * @var string
     * @since 1.0.0
     */
    public $username;

    /**
     * User Password
     *
     * @var string user password bcrypt hash
     * @since 1.0.0
     */
    public $password;

    /**
     * User Password reset token
     *
     * @var string hashed token
     * @since 1.0.3
     */
    public $password_reset_token;

    /**
     * Date when token was generated
     *
     * @var datetime timer for password reset
     * @since 1.0.3
     */
    public $password_reset_date;

    /**
     * User Gameification - XP Level
     *
     * @var int $xp_level current level
     * @since 1.0.4
     */
    public $xp_level;

    /**
     * User Gameification - Total XP
     *
     * @var int $xp_total total experience
     * @since 1.0.4
     */
    public $xp_total;

    /**
     * User Gameification - Current XP
     *
     * @var int $xp_current current level experience
     * @since 1.0.4
     */
    public $xp_current;

    /**
     * User Selected Theme
     *
     * @var string selected theme name
     * @since 1.0.0
     */
    public $theme;

    /**
     * User Selected Language
     *
     * @var string selected language
     * @since 1.0.13
     */
    public $lang;

    /**
     * User Permissions (Cache)
     *
     * @var array contains users permissions
     * @since 1.0.0
     */
    private $aMyPermissions;

    /**
     * User constructor.
     *
     * @param AdapterInterface $oDbAdapter Database Connection
     * @since 1.0.0
     */
    public function __construct($oDbAdapter)
    {
        parent::__construct($oDbAdapter);
        # User Permissions Table
        if (! isset(CoreEntityModel::$aEntityTables['user-permission'])) {
            CoreEntityModel::$aEntityTables['user-permission'] =
                new TableGateway('user_permission', CoreEntityModel::$oDbAdapter);
        }
        # User Index Table Columns
        if (! isset(CoreEntityModel::$aEntityTables['user-table-cols'])) {
            CoreEntityModel::$aEntityTables['user-table-cols'] =
                new TableGateway('user_table_column', CoreEntityModel::$oDbAdapter);
        }
        # User Form Tabs
        if (! isset(CoreEntityModel::$aEntityTables['user-form-tabs'])) {
            CoreEntityModel::$aEntityTables['user-form-tabs'] =
                new TableGateway('user_form_tab', CoreEntityModel::$oDbAdapter);
        }
        # User
        if (! isset(CoreEntityModel::$aEntityTables['user'])) {
            CoreEntityModel::$aEntityTables['user'] =
                new TableGateway('user', CoreEntityModel::$oDbAdapter);
        }
        # User Form Fields
        if (! isset(CoreEntityModel::$aEntityTables['user-form-fields'])) {
            CoreEntityModel::$aEntityTables['user-form-fields'] =
                new TableGateway('user_form_field', CoreEntityModel::$oDbAdapter);
        }
        $this->aMyPermissions = $this->getMyPermissions();
        $this->sSingleForm = 'user-single';
    }

    /**
     * Set Adapter (used for login)
     *
     * @param AdapterInterface $oDbAdapter Database Connection
     */
    public function setAdapter($oDbAdapter)
    {
        CoreEntityModel::$oDbAdapter = $oDbAdapter;
        # User Permissions Table
        if (! isset(CoreEntityModel::$aEntityTables['user-permission'])) {
            CoreEntityModel::$aEntityTables['user-permission'] =
                new TableGateway('user_permission', CoreEntityModel::$oDbAdapter);
        }
        # User Index Table Columns
        if (! isset(CoreEntityModel::$aEntityTables['user-table-cols'])) {
            CoreEntityModel::$aEntityTables['user-table-cols'] =
                new TableGateway('user_table_column', CoreEntityModel::$oDbAdapter);
        }
        # User Form Tabs
        if (! isset(CoreEntityModel::$aEntityTables['user-form-tabs'])) {
            CoreEntityModel::$aEntityTables['user-form-tabs'] =
                new TableGateway('user_form_tab', CoreEntityModel::$oDbAdapter);
        }
        # User Form Fields
        if (! isset(CoreEntityModel::$aEntityTables['user-form-fields'])) {
            CoreEntityModel::$aEntityTables['user-form-fields'] =
                new TableGateway('user_form_field', CoreEntityModel::$oDbAdapter);
        }
    }

    /**
     * Get Object Data from Array
     *
     * @param array $data
     * @since 1.0.0
     */
    public function exchangeArray(array $data)
    {
        $this->id = ! empty($data['User_ID']) ? $data['User_ID'] : 0;
        $this->email = ! empty($data['email']) ? $data['email'] : '';
        $this->username = ! empty($data['username']) ? $data['username'] : '';
        $this->lang = ! empty($data['lang']) ? $data['lang'] : '';
        $this->full_name = ! empty($data['full_name']) ? $data['full_name'] : '';
        $this->password = ! empty($data['password']) ? $data['password'] : '';
        $this->password_reset_token = ! empty($data['password_reset_token'])
            ? $data['password_reset_token'] : '';
        $this->password_reset_date = ! empty($data['password_reset_date'])
            ? $data['password_reset_date'] : '0000-00-00 00:00:00';
        $this->theme = ! empty($data['theme']) ? $data['theme'] : 'default';
        $this->featured_image = ! empty($data['featured_image']) ? $data['featured_image'] : '';

        # User XP Plugin
        $this->xp_level = ! empty($data['xp_level']) ? $data['xp_level'] : 1;
        $this->xp_current = ! empty($data['xp_current']) ? $data['xp_current'] : 0;
        $this->xp_total = ! empty($data['xp_total']) ? $data['xp_total'] : 0;

        if(array_key_exists('is_analyst',$data)) {
            $this->is_analyst = ! empty($data['is_analyst']) ? $data['is_analyst'] : 0;
        }
        if(array_key_exists('is_globaladmin',$data)) {
            $this->is_globaladmin = ! empty($data['is_globaladmin']) ? $data['is_globaladmin'] : 0;
        }
        if(array_key_exists('function',$data)) {
            $this->function = ! empty($data['function']) ? $data['function'] : '';
        }
        if(array_key_exists('description',$data)) {
            $this->description = ! empty($data['description']) ? $data['description'] : '';
        }
        if(array_key_exists('contact_idfs',$data)) {
            $this->contact_idfs = ! empty($data['contact_idfs']) ? $data['contact_idfs'] : 0;
        }
    }

    /**
     * Return Name
     *
     * @return string
     * @since 1.0.0
     */
    public function getLabel()
    {
        return $this->full_name;
    }

    /**
     * Check if user has permission
     *
     * @return boolean
     * @since 1.0.0
     */
    public function hasPermission($sPermission, $sModule)
    {
        if (! $this->aMyPermissions) {
            $this->aMyPermissions = $this->getMyPermissions();
        }
        $sModule = str_replace(['\\'], ['-'], $sModule);

        # Whitelisted Actions
        if ($sPermission == 'login' || $sPermission == 'logout' || $sPermission == 'home') {
            return true;
        }

        # Check if User has Permissions on Module
        if (array_key_exists($sModule, $this->aMyPermissions)) {
            # Check if User has permission
            if (array_key_exists($sPermission, $this->aMyPermissions[$sModule])) {
                # Has Permission
                return true;
            }
        }

        # No Permission
        return false;
    }

    /**
     * Gets User's permissions
     *
     * @param string $sPermissionFilter filter permissons return only selected
     * @param bool $bInfo load permission info
     * @return array list with permissions
     * @since 1.0.0
     */
    public function getMyPermissions($sPermissionFilter = '', $bInfo = false)
    {
        $aWhere = ['user_idfs' => $this->getID()];
        if ($sPermissionFilter != '') {
            $aWhere['permission'] = $sPermissionFilter;
        }
        $aMyPermsDB = CoreEntityModel::$aEntityTables['user-permission']->select($aWhere);
        $aMyPermsByModule = [];
        foreach ($aMyPermsDB as $oPerm) {
            $sModule = str_replace(['\\'], ['-'], $oPerm->module);
            # Sort Permissions By Module
            if (! array_key_exists($sModule, $aMyPermsByModule)) {
                $aMyPermsByModule[$sModule] = [];
            }
            if ($bInfo) {
                $oPermData = CoreController::$aCoreTables['permission']->select([
                    'module' => $oPerm->module,
                    'permission_key' => $oPerm->permission
                ]);
                if (count($oPermData) > 0) {
                    $oPermData = $oPermData->current();
                    $aMyPermsByModule[$sModule][$oPerm->permission] = $oPermData;
                }
            } else {
                //
                $aMyPermsByModule[$sModule][$oPerm->permission] = true;
            }
        }
        return $aMyPermsByModule;
    }

    /**
     * Get users theme
     *
     * @return string
     * @since 1.0.0
     */
    public function getTheme()
    {
        return $this->theme;
    }

    /**
     * Update permissions for used
     * based on array submitted
     *
     * @param array $aPermissions
     * @since 1.0.0
     */
    public function updatePermissions(array $aPermissions)
    {
        $aMyPermsDB = CoreEntityModel::$aEntityTables['user-permission']->delete(['user_idfs' => $this->getID()]);

        $aBasePermissions = [];
        $aPermissions = array_merge($aBasePermissions, $aPermissions);

        foreach ($aPermissions as $sPermWithModule) {
            $aInfo = explode('-', $sPermWithModule);
            $sPermission = $aInfo[0];
            $sModule = str_replace(['-'], ['\\'], substr($sPermWithModule, strlen($sPermission.'-')));

            CoreEntityModel::$aEntityTables['user-permission']->insert([
                'user_idfs' => $this->getID(),
                'permission' => $sPermission,
                'module' => $sModule
            ]);
            //echo 'save '.$sPermission.' mod '.$sModule;
        }
    }

    /**
     * Get all index tables and columns
     * for user
     *
     * @return array
     * @since 1.0.0
     */
    public function getMyIndexTablesWithColumns()
    {
        $aMyColumnsByTable = [];

        # Build Query to get User Based Columns
        $oColumnSel = new Select(CoreEntityModel::$aEntityTables['user-table-cols']->getTable());
        $oColumnSel->join(['core_field' => 'core_form_field'], 'core_field.Field_ID = user_table_column.field_idfs');
        $oColumnSel->where(['user_idfs' => $this->getID()]);

        # Get Users Index Table Columns from DB
        $aMyColumnsDB = CoreEntityModel::$aEntityTables['user-table-cols']->selectWith($oColumnSel);
        foreach ($aMyColumnsDB as $oCol) {
            if (! array_key_exists($oCol->tbl_name, $aMyColumnsByTable)) {
                $aMyColumnsByTable[$oCol->tbl_name] = [];
            }
            $aMyColumnsByTable[$oCol->tbl_name][$oCol->fieldkey] = $oCol;
        }

        return $aMyColumnsByTable;
    }

    /**
     * Update Users Index Table Columns
     *
     * @since 1.0.0
     */
    public function updateIndexColumns(array $aIndexColumns)
    {
        # Get Current Columns Settings for user - so we don't loose them
        $aCurrentColumnsDB = CoreEntityModel::$aEntityTables['user-table-cols']
            ->select(['user_idfs' => $this->getID()]);
        $aCurrentColumns = [];
        foreach ($aCurrentColumnsDB as $oColCur) {
            $aCurrentColumns[$oColCur->field_idfs] = $oColCur;
        }
        # Delete all settings
        $aMyColumnsDB = CoreEntityModel::$aEntityTables['user-table-cols']
            ->delete(['user_idfs' => $this->getID()]);

        # merge new settings with default settings
        $aBaseColumns = [];
        $aIndexColumns = array_merge($aBaseColumns, $aIndexColumns);
        $iSortID = 0;

        # Add new settings
        foreach ($aIndexColumns as $sColumnWithTable) {
            $aInfo = explode('-', $sColumnWithTable);
            $iFieldID = $aInfo[0];
            $sTable = str_replace([], [], substr($sColumnWithTable, strlen($iFieldID.'-')));

            # Get current sortID from User
            $iSortID = (array_key_exists($iFieldID, $aCurrentColumns))
                ? $aCurrentColumns[$iFieldID]->sortID : $iSortID;

            # insert new setting
            CoreEntityModel::$aEntityTables['user-table-cols']->insert([
                'tbl_name' => $sTable,
                'user_idfs' => $this->getID(),
                'field_idfs' => $iFieldID,
                'sortID' => $iSortID,
                'width' => '20%',
            ]);

            $iSortID++;
        }
    }

    /**
     * Get all form tabs for user
     *
     * @return array
     * @since 1.0.0
     */
    public function getMyTabs()
    {
        $aMyTabsByForm = [];

        # Build Query to get User Based Columns
        $oTabsSel = new Select(CoreEntityModel::$aEntityTables['user-form-tabs']->getTable());
        $oTabsSel->join(['core_tab' => 'core_form_tab'], 'core_tab.Tab_ID = user_form_tab.tab_idfs');
        $oTabsSel->where(['user_idfs' => $this->getID()]);

        # Get My Tabs from Database
        $oMyTabsDB = CoreEntityModel::$aEntityTables['user-form-tabs']->selectWith($oTabsSel);

        foreach ($oMyTabsDB as $oTab) {
            # Order By Form
            if (! array_key_exists($oTab->form, $aMyTabsByForm)) {
                $aMyTabsByForm[$oTab->form] = [];
            }
            $aMyTabsByForm[$oTab->form][$oTab->Tab_ID] = $oTab;
        }

        return $aMyTabsByForm;
    }

    /**
     * Update User Form Tabs
     *
     * @param array $aTabData
     * @since 1.0.0
     */
    public function updateFormTabs(array $aTabData)
    {
        # Get Current Tab Settings for user - so we don't loose them
        $aCurrentTabsDB = CoreEntityModel::$aEntityTables['user-form-tabs']->select(['user_idfs' => $this->getID()]);
        $aCurrentTabs = [];
        foreach ($aCurrentTabsDB as $oTabCur) {
            $aCurrentTabs[$oTabCur->tab_idfs] = $oTabCur;
        }
        # Delete all tabs
        $aMyTabsDB = CoreEntityModel::$aEntityTables['user-form-tabs']->delete(['user_idfs' => $this->getID()]);

        # merge new settings with default settings
        $aBaseTabs = [];
        $aIndexColumns = array_merge($aBaseTabs, $aTabData);
        $iSortID = 0;

        # Add new settings
        foreach ($aTabData as $sTabWithForm) {
            $aInfo = explode('_', $sTabWithForm);
            $sTabID = $aInfo[0];
            $sForm = str_replace([], [], substr($sTabWithForm, strlen($sTabID.'-')));

            # Get current sortID from User
            $iSortID = (array_key_exists($sTabID, $aCurrentTabs)) ? $aCurrentTabs[$sTabID]->sort_id : $iSortID;

            # insert new setting
            CoreEntityModel::$aEntityTables['user-form-tabs']->insert([
                'tab_idfs' => $sTabID,
                'user_idfs' => $this->getID(),
                'sort_id' => $iSortID,
            ]);

            $iSortID++;
        }
    }

    /**
     * Get User Form Fields by Forms
     *
     * @return array User Form Fields By Forms
     * @since 1.0.0
     */
    public function getMyFormFields()
    {
        $aMyFieldsByForm = [];

        # Build Query to get User Based Columns
        $oFieldsSel = new Select(CoreEntityModel::$aEntityTables['user-form-fields']->getTable());
        $oFieldsSel->join(['core_field' => 'core_form_field'], 'core_field.Field_ID = user_form_field.field_idfs');
        $oFieldsSel->where(['user_idfs' => $this->getID()]);
        $oFieldsSel->order('sort_id ASC');

        # Get My Fields from Database
        $oMyFieldsDB = CoreEntityModel::$aEntityTables['user-form-fields']->selectWith($oFieldsSel);

        foreach ($oMyFieldsDB as $oField) {
            # Order By Form
            if (! array_key_exists($oField->form, $aMyFieldsByForm)) {
                $aMyFieldsByForm[$oField->form] = [];
            }
            $aMyFieldsByForm[$oField->form][$oField->Field_ID] = $oField;
        }

        return $aMyFieldsByForm;
    }

    /**
     * Update User Form Tabs
     *
     * @param array $aFieldData
     * @since 1.0.0
     */
    public function updateFormFields(array $aFieldData)
    {
        # Get Current Tab Settings for user - so we don't loose them
        $aCurrentFieldsDB = CoreEntityModel::$aEntityTables['user-form-fields']
            ->select(['user_idfs' => $this->getID()]);
        $aCurrentFields = [];
        foreach ($aCurrentFieldsDB as $oFieldCur) {
            $aCurrentFields[$oFieldCur->field_idfs] = $oFieldCur;
        }
        # Delete all fields
        $aMyFieldsDB = CoreEntityModel::$aEntityTables['user-form-fields']
            ->delete(['user_idfs' => $this->getID()]);

        # merge new settings with default settings
        $aBaseFields = [];
        $aIndexColumns = array_merge($aBaseFields, $aFieldData);
        $iSortID = 0;

        # Add new settings
        foreach ($aFieldData as $sFieldWithForm) {
            $aInfo = explode('_', $sFieldWithForm);
            $iFieldID = (int) $aInfo[0];
            $sForm = str_replace([], [], substr($sFieldWithForm, strlen($iFieldID.'-')));

            # Get current sortID from User
            $iSortID = (array_key_exists($iFieldID, $aCurrentFields))
                ? $aCurrentFields[$iFieldID]->sort_id : $iSortID;

            # insert new setting
            CoreEntityModel::$aEntityTables['user-form-fields']->insert([
                'field_idfs' => $iFieldID,
                'user_idfs' => $this->getID(),
                'sort_id' => $iSortID,
            ]);

            $iSortID++;
        }
    }

    /**
     * Set Password Reset Token and Timer
     *
     * @param string $sTokenHash BCRYPT hashed token
     * @since 1.0.3
     */
    public function setPasswordResetToken($sTokenHash)
    {
        CoreEntityModel::$aEntityTables['user']->update([
            'password_reset_token' => $sTokenHash,
            'password_reset_date' => date('Y-m-d H:i:s', time()),
        ], ['User_ID' => $this->getID()]);
    }

    /**
     * Get User Experience info
     *
     * @return array Experience info
     * @since 1.0.4
     */
    public function getExperience()
    {
        $oNextLvl = CoreController::$aCoreTables['user-xp-level']
            ->select(['Level_ID' => ($this->xp_level + 1)])->current();
        $dPercent = 0;
        if ($this->xp_current != 0) {
            $dPercent = round((100 / ($oNextLvl->xp_total / $this->xp_current)), 2);
        }

        $aExp = [
            'level' => $this->xp_level,
            'total' => $this->xp_total,
            'current' => $this->xp_current,
            'percent' => $dPercent,
        ];

        return $aExp;
    }

    /**
     * Grant user experience based on activity
     *
     * @param string $sXPKey name of activity
     * @return bool true if successfull otherwise false
     * @since 1.0.4
     */
    public function addXP($sXPKey)
    {
        # Load XP Activity
        $oActivity = CoreController::$aCoreTables['user-xp-activity']
            ->select(['xp_key' => $sXPKey]);
        if (count($oActivity) > 0) {
            # get activity
            $oActivity = $oActivity->current();
            # get base xp
            $iXP = $oActivity->xp_base;
            # get next level
            $oNextLvl = CoreController::$aCoreTables['user-xp-level']
                ->select(['Level_ID' => ($this->xp_level + 1)])->current();

            # calculate new level and experience
            $iNewLvl = $this->xp_level;
            $iCurrentXP = $this->xp_current;
            if ($oNextLvl->xp_total <= ($iCurrentXP + $iXP)) {
                $iNewLvl++;
                $iCurrentXP = ($this->xp_current + $iXP) - $oNextLvl->xp_total;
            } else {
                $iCurrentXP = $iCurrentXP + $iXP;
            }
            # Set new information to entity
            $this->xp_level = $iNewLvl;
            $this->xp_current = $iCurrentXP;
            $this->xp_total = $this->xp_total + $iXP;

            # save to database
            CoreController::$aCoreTables['user']->update([
                'xp_level' => $this->xp_level,
                'xp_total' => $this->xp_total,
                'xp_current' => $this->xp_current,
            ], ['User_ID' => $this->getID()]);
        }

        return false;
    }

    public function getMyWidgets()
    {
        $aMyWidgets = [];

        # Get Current Widgets Settings for user - so we don't loose them
        $oMyWidgetsSel = new Select(CoreController::$aCoreTables['user-widget']->getTable());
        $oMyWidgetsSel->join([
            'core_widget' => 'core_widget'
        ], 'core_widget.Widget_ID = core_widget_user.widget_idfs');
        $oMyWidgetsSel->where(['core_widget_user.user_idfs' => $this->getID()]);
        $oMyWidgetsSel->order('core_widget_user.sort_id ASC');
        $aCurrentWidgetsDB = CoreController::$aCoreTables['user-widget']->selectWith($oMyWidgetsSel);
        foreach ($aCurrentWidgetsDB as $oWidCur) {
            $aMyWidgets[$oWidCur->widget_idfs] = $oWidCur;
        }

        return $aMyWidgets;
    }

    /**
     * Update Users Widgets
     *
     * @param array $aWidgetData
     * @since 1.0.5
     */
    public function updateWidgets(array $aWidgetData)
    {
        # Get Current Widgets Settings for user - so we don't loose them
        $aCurrentWidgetsDB = CoreController::$aCoreTables['user-widget']->select(['user_idfs' => $this->getID()]);
        $aCurrentWidgets = [];
        foreach ($aCurrentWidgetsDB as $oWidCur) {
            $aCurrentWidgets[$oWidCur->widget_idfs] = $oWidCur;
        }
        # Delete all widgets
        $aMyWidgetsDB = CoreController::$aCoreTables['user-widget']->delete(['user_idfs' => $this->getID()]);

        # merge new settings with default settings
        $aBaseWidgets = [];
        $aIndexColumns = array_merge($aBaseWidgets, $aWidgetData);
        $iSortID = 0;

        # Add new settings
        foreach ($aWidgetData as $iWidgetID) {
            if (is_numeric($iWidgetID) && ! empty($iWidgetID)) {
                # insert new setting
                CoreController::$aCoreTables['user-widget']->insert([
                    'widget_idfs' => $iWidgetID,
                    'user_idfs' => $this->getID(),
                    'sort_id' => $iSortID,
                ]);

                $iSortID++;
            }
        }
    }

    /**
     * Get user based setting
     *
     * @param $sSettingKey name of setting
     * @return bool|string false or setting value
     * @since 1.0.13
     */
    public function getSetting($sSettingKey)
    {
        $oSettingsTbl = new TableGateway('user_setting', CoreController::$oDbAdapter);
        $oExists = $oSettingsTbl->select([
            'user_idfs' => $this->getID(),
            'setting_name' => $sSettingKey,
        ]);
        if (count($oExists) > 0) {
            $oSetting = $oExists->current();
            return $oSetting->setting_value;
        }

        return false;
    }

    /**
     * Get users language
     *
     * @return mixed
     * @since 1.0.13
     */
    public function getLang()
    {
        return $this->lang;
    }
}
