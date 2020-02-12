--
-- Permissions
--
CREATE TABLE `permission` (
  `permission_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `module` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nav_label` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `nav_href` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `show_in_menu` tinyint(1) NOT NULL,
  `needs_globaladmin` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `permission`
  ADD PRIMARY KEY (`permission_key`,`module`);

--
-- User
--
CREATE TABLE `user` (
  `User_ID` int(11) NOT NULL,
  `username` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `full_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `authkey` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `xp_level` int(3) NOT NULL DEFAULT 1,
  `xp_total` int(11) NOT NULL DEFAULT 0,
  `xp_current` int(11) NOT NULL DEFAULT 0,
  `is_backend_user` tinyint(1) NOT NULL DEFAULT 1,
  `is_globaladmin` tinyint(1) NOT NULL DEFAULT 0,
  `lang` varchar(10) NOT NULL DEFAULT 'en_US',
  `mobile` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `password_reset_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password_reset_date` datetime DEFAULT NULL,
  `items_per_page` int(11) NOT NULL DEFAULT 25,
  `button_icon_position` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `form_label_spacing` int(11) NOT NULL DEFAULT 8,
  `theme` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'default',
  `featured_image` VARCHAR(255) NOT NULL DEFAULT '',
  `created_by` int(11) NOT NULL,
  `created_date` datetime NOT NULL,
  `modified_by` int(11) NOT NULL,
  `modified_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `user`
  ADD PRIMARY KEY (`User_ID`);

ALTER TABLE `user`
  MODIFY `User_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- User Form Field Mapping
--

CREATE TABLE `user_form_field` (
  `user_idfs` int(11) NOT NULL,
  `field_idfs` int(11) NOT NULL,
  `sort_id` int(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `user_form_field`
  ADD PRIMARY KEY (`user_idfs`,`field_idfs`);

--
-- User Widgets
--
CREATE TABLE `core_widget_user` (
  `user_idfs` int(11) NOT NULL,
  `widget_idfs` int(11) NOT NULL,
  `sort_id` int(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `core_widget_user`
  ADD PRIMARY KEY (`user_idfs`,`widget_idfs`);

--
-- User Form Tab Mapping
--
CREATE TABLE `user_form_tab` (
  `tab_idfs` varchar(50) NOT NULL,
  `user_idfs` int(11) NOT NULL,
  `sort_id` int(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `user_form_tab`
  ADD PRIMARY KEY (`tab_idfs`,`user_idfs`);

--
-- User Permissions
--
CREATE TABLE `user_permission` (
  `user_idfs` int(11) NOT NULL,
  `permission` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `module` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `user_permission`
  ADD PRIMARY KEY (`user_idfs`,`permission`,`module`);

--
-- User Settings
--
CREATE TABLE `user_setting` (
  `user_idfs` int(11) NOT NULL,
  `setting_name` varchar(100) NOT NULL,
  `setting_value` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `user_setting`
  ADD PRIMARY KEY (`user_idfs`,`setting_name`);

--
-- Uesr Index Columns
--
CREATE TABLE `user_table_column` (
  `tbl_name` varchar(100) NOT NULL,
  `user_idfs` int(11) NOT NULL,
  `field_idfs` int(11) NOT NULL,
  `sortID` int(5) NOT NULL,
  `width` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `user_table_column`
  ADD PRIMARY KEY (`tbl_name`,`user_idfs`,`field_idfs`);

--
-- User XP Levels
--
CREATE TABLE `user_xp_level` (
  `Level_ID` int(11) NOT NULL,
  `xp_total` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `user_xp_level`
  ADD PRIMARY KEY (`Level_ID`);

ALTER TABLE `user_xp_level`
  MODIFY `Level_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- User XP Activity
--
CREATE TABLE `user_xp_activity` (
  `Activity_ID` int(11) NOT NULL,
  `xp_key` varchar(100) NOT NULL,
  `label` varchar(255) NOT NULL,
  `xp_base` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `user_xp_activity`
  ADD PRIMARY KEY (`Activity_ID`),
  ADD UNIQUE KEY `xp_key` (`xp_key`);

ALTER TABLE `user_xp_activity`
  MODIFY `Activity_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- Api Key Table
--
CREATE TABLE `core_api_key` (
  `Apikey_ID` int(11) NOT NULL,
  `api_token` varchar(255) NOT NULL,
  `api_key` varchar(255) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_date` datetime NOT NULL,
  `modified_by` int(11) NOT NULL,
  `modified_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `core_api_key`
  ADD PRIMARY KEY (`Apikey_ID`);

ALTER TABLE `core_api_key`
  MODIFY `Apikey_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- Save
--
COMMIT;