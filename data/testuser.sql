INSERT INTO `user` (`User_ID`, `username`, `full_name`, `email`, `password`, `authkey`, `lang`, `xp_level`, `xp_total`, `xp_current`, `is_backend_user`, `is_globaladmin`, `mobile`, `password_reset_token`, `password_reset_date`, `theme`, `featured_image`, `created_by`, `created_date`, `modified_by`, `modified_date`) VALUES
(1, 'travis', 'Travis CI', 'travis@1plc.ch', '$2y$10$zdbi3jiMz/Nud7y4Za.BuOZterld3eZ2whZ7xtU9z9lP15q66gu1W', '', 'en_US', 1, 245, 245, 1, 1, '', '', '0000-00-00 00:00:00', 'default', '', 1, '2020-02-11 20:14:26', 1, '2020-02-11 22:58:11');

--
-- testuser permissions
--
INSERT INTO `user_permission` (`user_idfs`, `permission`, `module`) VALUES
(1, 'add', 'OnePlace\\User\\Controller\\ApiController'),
(1, 'add', 'OnePlace\\User\\Controller\\UserController'),
(1, 'addtheme', 'Application\\Controller\\UploadController'),
(1, 'checkforupdates', 'Application\\Controller\\IndexController'),
(1, 'edit', 'OnePlace\\User\\Controller\\UserController'),
(1, 'filepond', 'Application\\Controller\\UploadController'),
(1, 'globaladmin', 'OnePlace\\Core'),
(1, 'index', 'Application\\Controller\\IndexController'),
(1, 'index', 'OnePlace\\User\\Controller\\UserController'),
(1, 'languages', 'OnePlace\\User\\Controller\\UserController'),
(1, 'list', 'OnePlace\\User\\Controller\\ApiController'),
(1, 'manage', 'OnePlace\\User\\Controller\\ApiController'),
(1, 'profile', 'OnePlace\\User\\Controller\\UserController'),
(1, 'quicksearch', 'Application\\Controller\\IndexController'),
(1, 'selectbool', 'Application\\Controller\\IndexController'),
(1, 'settheme', 'OnePlace\\User\\Controller\\UserController'),
(1, 'settings', 'OnePlace\\User\\Controller\\UserController'),
(1, 'themes', 'Application\\Controller\\IndexController'),
(1, 'togglemediapub', 'Application\\Controller\\UploadController'),
(1, 'updatefieldsort', 'Application\\Controller\\IndexController'),
(1, 'updateindexcolumnsort', 'OnePlace\\User\\Controller\\UserController'),
(1, 'updatesetting', 'OnePlace\\User\\Controller\\UserController'),
(1, 'updateuppysort', 'Application\\Controller\\UploadController'),
(1, 'uppy', 'Application\\Controller\\UploadController'),
(1, 'view', 'OnePlace\\User\\Controller\\UserController');