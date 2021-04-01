INSERT INTO `settings` (`settings_key`, `settings_value`) VALUES ('firewall-user-whitelist', '[\"login\",\"reset-pw\",\"forgot-pw\",\"home\",\"app-home\",\"route\"]');

INSERT INTO `permission` (`permission_key`, `module`, `label`, `nav_label`, `nav_href`, `show_in_menu`, `needs_globaladmin`) VALUES
('index', 'OnePlace\\User\\Controller\\FirewallController', 'Firewall Index', '', '', 0, 0);