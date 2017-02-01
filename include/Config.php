<?php
/**
 * Database configuration
 */
define("ENV","DEV"); //change to PROD when live.

//LOCAL database credentials
//define('DB_USERNAME', 'root');
//define('DB_PASSWORD', '');
//define('DB_HOST', '127.0.0.1');
//define('DB_NAME', 'sikkimdemodb');

//LIVE DATABASE CREDENTIALS
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'sikkimdemodb');


//define ('BASE_URL','http://api-sikkimtourismdemo.kminfosystems.com/');
define ('WEB_URL','http://www.se-event.kminfosystems.com/');
define ('DIRECTORY','../../api-sikkimtourismdemo.kminfosystems.com/uploads/');
define ('MULTI_IMAGES_PATH','http://api-sikkimtourismdemo.kminfosystems.com/uploads/');

define ('IMAGE_DIRECTORY','uploads/');


define('SMS_USERNAME', 'kapbulk');
define('SMS_PASSWORD', 'kap@user!23');
define('SMS_ID', 'KAPMSG');

define('SMS_STATUS', 'ON');

define('EVENT_BOOKING_DURATION_TIME', '00:10:00');
define('EVENT_BOOKING_DURATION_TIME_IOS', '00:08:00');


define('EVENT_ACCESS_CODE', '4YRUXLSRO20O8NIH');
define('EVENT_MERCHANT_ID', '2');

?>
