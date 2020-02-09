CREATE DATABASE IF NOT EXISTS `travis` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
CREATE USER 'travis'@'localhost' IDENTIFIED BY 'travis';
GRANT ALL ON travis.* TO 'travis'@'localhost';
USE `travis`;