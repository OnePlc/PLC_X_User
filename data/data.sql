--
-- Core Form
--
INSERT INTO `core_form` (`form_key`, `label`, `entity_class`, `entity_tbl_class`) VALUES
('user-single', 'User', 'OnePlace\\User\\Model\\User', 'OnePlace\\User\\Model\\UserTable'),
('user-copy', 'Copy User', 'OnePlace\\User\\Model\\User', 'OnePlace\\User\\Model\\UserTable');

--
-- Core Form Button
--
INSERT INTO `core_form_button` (`Button_ID`, `label`, `icon`, `title`, `href`, `class`, `append`, `form`, `mode`, `filter_check`, `filter_value`) VALUES
(NULL, 'Add User', 'fas fa-plus', 'Add User', '/user/add', 'primary', '', 'user-index', 'link', '', ''),
(NULL, 'Save User', 'fas fa-save', 'Save User', '#', 'primary saveForm', '', 'user-single', 'link', '', ''),
(NULL, 'Edit User', 'fas fa-edit', 'Edit User', '/user/edit/##ID##', 'primary', '', 'user-view', 'link', '', ''),
(NULL, 'Copy User', 'fas fa-copy', 'Copy User', '/user/copy/##ID##', 'primary', '', 'user-view', 'link', '', ''),
(NULL, 'Copy User', 'fas fa-copy', 'Copy User', '#', 'primary saveForm', '', 'user-copy', 'link', '', '');

--
-- Core Form Field
--
INSERT INTO `core_form_field` (`Field_ID`, `type`, `label`, `fieldkey`, `tab`, `form`, `class`, `url_view`, `url_list`, `show_widget_left`, `allow_clear`, `readonly`, `tbl_cached_name`, `tbl_class`, `tbl_permission`) VALUES
(NULL, 'text', 'Username', 'username', 'user-base', 'user-single', 'col-md-3', '/user/view/##ID##', '', 0, 1, 0, '', '', ''),
(NULL, 'text', 'Full Name', 'full_name', 'user-base', 'user-single', 'col-md-3', '', '', 0, 1, 0, '', '', ''),
(NULL, 'email', 'E-Mail', 'email', 'user-base', 'user-single', 'col-md-3', '/user/view/##ID##', '', 0, 1, 0, '', '', ''),
(NULL, 'password', 'Password', 'password', 'user-base', 'user-single', 'col-md-3', '', '', 0, 1, 0, '', '', ''),
(NULL, 'partial', 'Permissions', 'permissions', 'user-permissions', 'user-single', 'col-md-12', '', '', 0, 1, 0, '', '', ''),
(NULL, 'partial', 'Columns', 'indexcolumns', 'user-columns', 'user-single', 'col-md-12', '', '', 0, 1, 0, '', '', ''),
(NULL, 'partial', 'Tabs', 'tabs', 'user-tabs', 'user-single', 'col-md-12', '', '', 0, 1, 0, '', '', ''),
(NULL, 'partial', 'Fields', 'formfields', 'user-fields', 'user-single', 'col-md-12', '', '', 0, 1, 0, '', '', ''),
(NULL, 'partial', 'Widgets', 'widgets', 'user-widgets', 'user-single', 'col-md-12', '', '', '0', '1', '0', '', '', ''),
(NULL, 'featuredimage', 'Featured Image', 'featured_image', 'user-base', 'user-single', 'col-md-3', '', '', '0', '1', '0', '', '', ''),
(NULL, 'text', 'Username', 'username', 'usercopy-base', 'user-copy', 'col-md-3', '/user/view/##ID##', '', 0, 1, 0, '', '', ''),
(NULL, 'text', 'Full Name', 'full_name', 'usercopy-base', 'user-copy', 'col-md-3', '', '', 0, 1, 0, '', '', ''),
(NULL, 'email', 'E-Mail', 'email', 'usercopy-base', 'user-copy', 'col-md-3', '/user/view/##ID##', '', 0, 1, 0, '', '', ''),
(NULL, 'password', 'Password', 'password', 'usercopy-base', 'user-copy', 'col-md-3', '', '', 0, 1, 0, '', '', ''),
(NULL, 'text', 'Theme', 'theme', 'user-base', 'user-single', 'col-md-3', '', '', 0, 1, 0, '', '', ''),
(NULL, 'text', 'Language', 'lang', 'user-base', 'user-single', 'col-md-3', '', '', 0, 1, 0, '', '', '');

--
-- Core Form Tab
--
INSERT INTO `core_form_tab` (`Tab_ID`, `form`, `title`, `subtitle`, `icon`, `counter`, `sort_id`, `filter_check`, `filter_value`) VALUES
('user-base', 'user-single', 'User', 'Base Data', 'fas fa-user', '', 0, '', ''),
('user-columns', 'user-single', 'Columns', 'Index Info', 'fas fa-columns', '', 0, '', ''),
('user-fields', 'user-single', 'Fields', 'Form fields', 'fas fa-edit', '', 0, '', ''),
('user-permissions', 'user-single', 'Permissions', 'What is allowed', 'fas fa-key', '', 0, '', ''),
('user-tabs', 'user-single', 'Tabs', 'Form tabs', 'fas fa-bars', '', 0, '', ''),
('user-widgets', 'user-single', 'Widgets', 'Dashboard Widgets', 'fas fa-window-restore', '', 0, '', ''),
('usercopy-base', 'user-single', 'User', 'Base Data', 'fas fa-user', '', 0, '', '');

--
-- Core Index Table
--
INSERT INTO `core_index_table` (`table_name`, `form`, `label`) VALUES
('user-index', 'user-single', 'User Index');

--
-- Basic Permissions
--
INSERT INTO `permission` (`permission_key`, `module`, `label`, `nav_label`, `nav_href`, `show_in_menu`, `needs_globaladmin`) VALUES
('add', 'OnePlace\\User\\Controller\\UserController', 'Add', '', '', 0, 0),
('edit', 'OnePlace\\User\\Controller\\UserController', 'Edit', '', '', 0, 0),
('index', 'OnePlace\\User\\Controller\\UserController', 'Index', 'Users', '/user', 1, 0),
('updateindexcolumnsort', 'OnePlace\\User\\Controller\\UserController', 'Update Column Index', '', '', 0, 0),
('settheme', 'OnePlace\\User\\Controller\\UserController', 'Set own Theme', '', '', 0, 0),
('view', 'OnePlace\\User\\Controller\\UserController', 'View', '', '', 0, 0),
('copy', 'OnePlace\\User\\Controller\\UserController', 'Copy', '', '', 0, 0),
('list', 'OnePlace\\User\\Controller\\ApiController', 'List', '', '', 1, 0),
('profile', 'OnePlace\\User\\Controller\\UserController', 'Profile', '', '', 0, 0),
('settings', 'OnePlace\\User\\Controller\\UserController', 'Settings', '', '', 0, 0),
('manage', 'OnePlace\\User\\Controller\\ApiController', 'Manage API Keys', '', '', 0, 1),
('add', 'OnePlace\\User\\Controller\\ApiController', 'Create API Key', '', '', 0, 1),
('globaladmin', 'OnePlace\\Core', 'Super Admin', '', '', 0, 1),
('updatesetting', 'OnePlace\\User\\Controller\\UserController', 'Update Personal Setting', '', '', 0, 0),
('languages', 'OnePlace\\User\\Controller\\UserController', 'Language Selection', '', '', 0, 0),
('index', 'OnePlace\\User\\Controller\\FirewallController', 'Firewall Index', '', '', 0, 0);

--
-- Default Leveling Settings
--
INSERT INTO `user_xp_level` (`Level_ID`, `xp_total`) VALUES
(1, 0),
(2, 400),
(3, 900),
(4, 1400),
(5, 2100),
(6, 2800),
(7, 3800),
(8, 5000),
(9, 6400),
(10, 8100),
(11, 9240),
(12, 10780),
(13, 13230),
(14, 16800),
(15, 20380),
(16, 24440),
(17, 28080),
(18, 31500),
(19, 34800),
(20, 38550),
(21, 42320),
(22, 46560),
(23, 49440),
(24, 52000),
(25, 55040),
(26, 58400),
(27, 61120),
(28, 64160),
(29, 66880),
(30, 71680),
(31, 76160),
(32, 81440),
(33, 85600),
(34, 90240),
(35, 94560),
(36, 99200),
(37, 104160),
(38, 108480),
(39, 113280),
(40, 117920),
(41, 123280),
(42, 128750),
(43, 134160),
(44, 139870),
(45, 145490),
(46, 151440),
(47, 157500),
(48, 163470),
(49, 169760),
(50, 176180),
(51, 182480),
(52, 189130),
(53, 195900),
(54, 202540),
(55, 209540),
(56, 216660),
(57, 223640),
(58, 230990),
(59, 238460),
(60, 245790);

--
-- Default User XP Activities
--
INSERT INTO `user_xp_activity` (`Activity_ID`, `xp_key`, `label`, `xp_base`) VALUES
(1, 'login', 'Login', 10),
(2, 'user-add', 'Add New User', 50),
(3, 'user-edit', 'Edit User', 5);

--
-- Default Widgets
--
INSERT INTO `core_widget` (`Widget_ID`, `widget_name`, `label`, `permission`) VALUES
(NULL, 'user_dailystats', 'User- Daily Stats', 'index-User\\Controller\\UserController');

--
-- Index for API Keys
--
INSERT INTO `core_index_table` (`table_name`, `form`, `label`) VALUES
('apikey-index', 'apikey-single', 'API Key Index');

--
-- add button for index
--
INSERT INTO `core_form_button` (`Button_ID`, `label`, `icon`, `title`, `href`, `class`, `append`, `form`, `mode`, `filter_check`, `filter_value`) VALUES
(NULL, 'Create API Key', 'fas fa-key', 'Create API Key', '/user/api/add', 'primary', '', 'apikey-index', 'link', '', ''),
(NULL, 'Save Api Key', 'fas fa-save', 'Save Api Key', '#', 'primary saveForm', '', 'apikey-single', 'link', '', '');

--
-- form for api keys
--
INSERT INTO `core_form` (`form_key`, `label`, `entity_class`, `entity_tbl_class`) VALUES
('apikey-single', 'API Key', 'OnePlace\\User\\Model\\Apikey', 'OnePlace\\User\\Model\\ApikeyTable');

--
-- tab for apikey form
--
INSERT INTO `core_form_tab` (`Tab_ID`, `form`, `title`, `subtitle`, `icon`, `counter`, `sort_id`, `filter_check`, `filter_value`) VALUES
('apikey-base', 'apikey-single', 'Api Key', 'Base', 'fas fa-cogs', '', '0', '', '');

--
-- form fields for api keys
--
INSERT INTO `core_form_field` (`Field_ID`, `type`, `label`, `fieldkey`, `tab`, `form`, `class`, `url_view`, `url_list`, `show_widget_left`, `allow_clear`, `readonly`, `tbl_cached_name`, `tbl_class`, `tbl_permission`) VALUES
(NULL, 'text', 'API Key', 'api_key', 'apikey-base', 'apikey-single', 'col-md-6', '', '', '0', '1', '0', '', '', ''),
(NULL, 'text', 'API Token', 'api_token', 'apikey-base', 'apikey-single', 'col-md-6', '', '', '0', '1', '0', '', '', '');

--
-- module icon
--
INSERT INTO `settings` (`settings_key`, `settings_value`) VALUES ('user-icon', 'fas fa-users');

--
-- basic whitelist
--
INSERT INTO `settings` (`settings_key`, `settings_value`) VALUES ('firewall-whitelist', '[\"setup\",\"login\",\"reset-pw\",\"forgot-pw\",\"home\"]');
INSERT INTO `settings` (`settings_key`, `settings_value`) VALUES ('firewall-user-whitelist', '[\"login\",\"reset-pw\",\"forgot-pw\",\"home\",\"app-home\"]');