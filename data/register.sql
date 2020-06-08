SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE `user_registration` (
  `Registration_ID` int(11) NOT NULL,
  `user_token` varchar(255) NOT NULL,
  `user_email` varchar(255) NOT NULL,
  `created_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


ALTER TABLE `user_registration`
  ADD PRIMARY KEY (`Registration_ID`);


ALTER TABLE `user_registration`
  MODIFY `Registration_ID` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;
