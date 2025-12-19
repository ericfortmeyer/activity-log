SELECT 'Creating app database...' AS '';
CREATE DATABASE IF NOT EXISTS `activity_log`;

USE `activity_log`;

SELECT 'Creating app user...' AS '';
CREATE USER `activity_log_app`@'localhost' IDENTIFIED BY RANDOM PASSWORD;

SELECT 'Granting privileges to app user...' AS '';
GRANT
  SELECT, INSERT, UPDATE, DELETE
ON `activity_log`.* TO 'activity_log_app'@'localhost';

FLUSH PRIVILEGES;
