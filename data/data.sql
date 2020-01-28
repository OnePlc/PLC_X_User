--
-- Core Form
--
INSERT INTO `core_form` (`form_key`, `label`) VALUES
('user-single', 'User');

--
-- Core Form Button
--
INSERT INTO `core_form_button` (`Button_ID`, `label`, `icon`, `title`, `href`, `class`, `append`, `form`, `mode`, `filter_check`, `filter_value`) VALUES
(NULL, 'Add User', 'fas fa-plus', 'Add User', '/user/add', 'primary', '', 'user-index', 'link', '', ''),
(NULL, 'Save User', 'fas fa-save', 'Save User', '#', 'primary saveForm', '', 'user-single', 'link', '', ''),
(NULL, 'Edit User', 'fas fa-edit', 'Edit User', '/user/edit/##ID##', 'primary', '', 'user-view', 'link', '', '');

--
-- Core Form Field
--
INSERT INTO `core_form_field` (`Field_ID`, `type`, `label`, `fieldkey`, `tab`, `form`, `class`, `url_view`, `url_ist`, `show_widget_left`, `allow_clear`, `readonly`, `tbl_cached_name`, `tbl_class`, `tbl_permission`) VALUES
(NULL, 'text', 'Username', 'username', 'user-base', 'user-single', 'col-md-3', '/user/view/##ID##', '', 0, 1, 0, '', '', ''),
(NULL, 'text', 'Full Name', 'full_name', 'user-base', 'user-single', 'col-md-3', '', '', 0, 1, 0, '', '', ''),
(NULL, 'email', 'E-Mail', 'email', 'user-base', 'user-single', 'col-md-3', '/user/view/##ID##', '', 0, 1, 0, '', '', ''),
(NULL, 'password', 'Password', 'password', 'user-base', 'user-single', 'col-md-3', '', '', 0, 1, 0, '', '', ''),
(NULL, 'partial', 'Permissions', 'permissions', 'user-permissions', 'user-single', 'col-md-12', '', '', 0, 1, 0, '', '', ''),
(NULL, 'partial', 'Columns', 'indexcolumns', 'user-columns', 'user-single', 'col-md-12', '', '', 0, 1, 0, '', '', ''),
(NULL, 'partial', 'Tabs', 'tabs', 'user-tabs', 'user-single', 'col-md-12', '', '', 0, 1, 0, '', '', ''),
(NULL, 'partial', 'Fields', 'formfields', 'user-fields', 'user-single', 'col-md-12', '', '', 0, 1, 0, '', '', ''),
(NULL, 'partial', 'Widgets', 'widgets', 'user-widgets', 'user-single', 'col-md-12', '', '', '0', '1', '0', '', '', '');

--
-- Core Form Tab
--
INSERT INTO `core_form_tab` (`Tab_ID`, `form`, `title`, `subtitle`, `icon`, `counter`, `sort_id`, `filter_check`, `filter_value`) VALUES
('user-base', 'user-single', 'User', 'Base Data', 'fas fa-user', '', 0, '', ''),
('user-columns', 'user-single', 'Columns', 'Index Info', 'fas fa-columns', '', 0, '', ''),
('user-fields', 'user-single', 'Fields', 'Form fields', 'fas fa-edit', '', 0, '', ''),
('user-permissions', 'user-single', 'Permissions', 'What is allowed', 'fas fa-key', '', 0, '', ''),
('user-tabs', 'user-single', 'Tabs', 'Form tabs', 'fas fa-bars', '', 0, '', ''),
('user-widgets', 'user-single', 'Widgets', 'Dashboard Widgets', 'fas fa-window-restore', '', 0, '', '');

--
-- Core Index Table
--
INSERT INTO `core_index_table` (`table_name`, `form`, `label`) VALUES
('user-index', 'user-single', 'User Index');

--
-- Basic Permissions
--
INSERT INTO `permission` (`permission_key`, `module`, `label`, `nav_label`, `nav_href`, `show_in_menu`) VALUES
('add', 'OnePlace\\User\\Controller\\UserController', 'Add', '', '', 0),
('edit', 'OnePlace\\User\\Controller\\UserController', 'Edit', '', '', 0),
('index', 'OnePlace\\User\\Controller\\UserController', 'Index', 'Users', '/user', 1),
('updateindexcolumnsort', 'OnePlace\\User\\Controller\\UserController', 'Update Column Index', '', '', 0),
('settheme', 'OnePlace\\User\\Controller\\UserController', 'Set own Theme', '', '', 0),
('view', 'OnePlace\\User\\Controller\\UserController', 'View', '', '', 0);

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
INSERT INTO `user_xp_activity` (`Activity_ID`, `xp_key`, `label`, `xp_base`) VALUES
(1, 'login', 'Login', 10),
(2, 'user-add', 'Add New User', 50),
(3, 'user-edit', 'Edit User', 5);
