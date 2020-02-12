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