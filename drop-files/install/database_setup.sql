/* CREATE DATABASE IF NOT EXISTS `cabmanng_data`; */

CREATE TABLE IF NOT EXISTS `cab_tbl_users` (
  `user_id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'auto incrementing user_id of each user, unique index',
  `pwd_raw` varchar(64) COLLATE utf8_unicode_ci NOT NULL COMMENT 'user''s raw password',
 	`password_hash` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'users password in salted and hashed format',
  `address` varchar(255) COLLATE utf8_unicode_ci NULL COMMENT 'users address',
  `email` varchar(64) COLLATE utf8_unicode_ci NULL COMMENT 'users email, unique',
  `firstname` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
	`lastname` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `sex` varchar(7),
  `phone` varchar(25) NOT NULL,
  `u_uuid` char(32) NULL COMMENT 'ID to uniquely identify this user on the service',
  `country` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `referal_code` varchar(10) COLLATE utf8_unicode_ci NULL COMMENT 'Referal code for this user for bringing other users',
  `reg_with_referal_code` varchar(20) NULL COMMENT 'Referal code this rider registered with if any',
	`account_type` tinyint(1) UNSIGNED DEFAULT '1' COLLATE utf8_unicode_ci COMMENT '1:passenger, 2:dispatcher , 3:Admin, 4:franchise, 5:biller',
  `route_id` INTEGER NULL COMMENT 'id of the route this this user selected on App', 
  `last_login_date` DATETIME NULL,
  `login_count` INTEGER NOT NULL DEFAULT 0 COMMENT 'how many times this user has logGed in',
  `referral_count` INTEGER NOT NULL DEFAULT 0 COMMENT 'number of referral registrations',
  `referral_discounts_count` INTEGER NOT NULL DEFAULT 0 COMMENT 'number of subsequent bookings referral discount should be applied. auto decremented after each successfl next booking',
  `account_create_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `push_notification_token` varchar(200) NULL,
  `is_activated` tinyint(1) UNSIGNED DEFAULT '0' COLLATE utf8_unicode_ci COMMENT 'sets if an account is activated or not', 
  `account_deleted` tinyint(1) UNSIGNED DEFAULT '0' COLLATE utf8_unicode_ci COMMENT 'Indicate that a user has deleted his account', 
  `user_rating` TINYINT(1) NOT NULL DEFAULT 5 COMMENT 'users overall rating',  
  `booking_cancel_freq` TINYINT(10) NOT NULL DEFAULT 0 COMMENT 'consecutive number of times user cancels bookings before getting banned',  
  `ban_date` DATETIME NULL COMMENT 'last date and time user was banned for cancelling trips',
  `account_active` boolean NOT NULL DEFAULT 0 COMMENT 'active or inactivate user account',
  `photo_file` varchar(255) COLLATE utf8_unicode_ci,
  `completed_rides` INTEGER DEFAULT 0 COMMENT 'keep track of all-time number of rides completed by this user',
  `cancelled_rides` INTEGER DEFAULT 0 COMMENT 'keep track of all-time number of rides cancelled by this user',
  `reward_points` DECIMAL(11,1) DEFAULT 0 COMMENT 'number of reward points earned',
  `reward_points_redeemed` DECIMAL(11,2) NOT NULL DEFAULT 0 COMMENT 'Last amount earned from redeeming rewward points',
  `disp_lang` varchar(10) DEFAULT 'en' COLLATE utf8_unicode_ci COMMENT 'current user ui language',
  `wallet_amount` DECIMAL(11,2) NOT NULL DEFAULT 0 COMMENT 'Amount left in users wallet',
  `country_code` varchar(3) NOT NULL DEFAULT 'ng' COLLATE utf8_unicode_ci,
  `country_dial_code` varchar(5) NOT NULL DEFAULT '+234' COLLATE utf8_unicode_ci,
     
  PRIMARY KEY (`user_id`),
  FULLTEXT (`firstname`,`lastname`),
  INDEX (`push_notification_token`),
  INDEX (`referal_code`),
  INDEX (`email`),
  INDEX (`account_deleted`),
  INDEX (`phone`),
  INDEX (`last_login_date`),
  INDEX (`u_uuid`),
  INDEX (`account_create_date`)
  
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='user data';


INSERT IGNORE INTO `cab_tbl_users` (`user_id`, `pwd_raw`, `password_hash`, `address`, `email`, `firstname`, `lastname`, `sex`, `phone`, `country`, `referal_code`, `account_type`, `route_id`, `last_login_date`, `login_count`, `referral_count`, `referral_discounts_count`, `account_create_date`, `push_notification_token`, `is_activated`, `user_rating`, `account_active`, `photo_file`, `wallet_amount`, `country_code`, `country_dial_code`) VALUES (1, 'Droptaxi', '$2y$10$IFPUQ4nErevZr8q2SdUiYOmQDuDVAwNVxlKOBsILJ5y3KmJOjCHci', 'Enugu', 'admin@droptaxi.com.ng', 'Michael', 'Chike', 'male', '08035631219', 'Nigeria', 'XXV235', '3', '1', '2023-07-30 14:05:02', '127', '4', '0', '2023-02-14 14:07:19', '', '1', '4', '0', '0', '5000', 'ng', '+234');

CREATE TABLE IF NOT EXISTS `cab_tbl_drivers` (
  `driver_id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'auto incrementing user_id of each user, unique index',
  `pwd_raw` varchar(64) COLLATE utf8_unicode_ci NOT NULL COMMENT 'raw password',
 	`password_hash` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'password in salted and hashed format',
  `drv_address` varchar(255) COLLATE utf8_unicode_ci NULL COMMENT 'driver address',
  `email` varchar(64) COLLATE utf8_unicode_ci NULL COMMENT 'email, unique',
  `firstname` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
	`lastname` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `phone` varchar(20) NOT NULL,
  `d_uuid` char(32) NULL COMMENT 'ID to uniquely identify this driver on the service',
  `state` varchar(20) COLLATE utf8_unicode_ci NULL,
  `drv_country` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `car_plate_num` varchar(20) COLLATE utf8_unicode_ci NULL,
  `car_reg_num` varchar(30) COLLATE utf8_unicode_ci NULL,
  `car_model` varchar(64) COLLATE utf8_unicode_ci NULL,
  `car_color` varchar(20) COLLATE utf8_unicode_ci NULL,
  `car_year` varchar(5) COLLATE utf8_unicode_ci NULL,
  `route_id` INTEGER NULL COMMENT 'id of the route this driver operates',
  `ride_id` INTEGER NOT NULL COMMENT 'id of the ride type for this driver',
  `referal_code` varchar(10) COLLATE utf8_unicode_ci NULL COMMENT 'Referal code for this driver for bringing other drivers',
  `reg_with_referal_code` varchar(20) NULL COMMENT 'Referal code this driver registered with if any',
  `referral_target_status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0 = pending, 1 = referal target met, 2 = did not meet referal target',   
  `referral_task_progress` INTEGER NULL COMMENT 'referral task progress of this driver. number of completed rides since registered',
  `reg_route_id` INTEGER NOT NULL DEFAULT 1 COMMENT 'id of the city route this driver originally registered in',
  `franchise_id` INTEGER UNSIGNED NOT NULL DEFAULT 1 COMMENT 'id of the franchise this driver belongs',
  `last_login_date` DATETIME NULL,
  `account_create_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `push_notification_token` varchar(200) NULL,
  `is_activated` tinyint(1) UNSIGNED DEFAULT '0' COLLATE utf8_unicode_ci COMMENT 'sets if an account is activated or not',
  `account_deleted` tinyint(1) UNSIGNED DEFAULT '0' COLLATE utf8_unicode_ci COMMENT 'Indicate that a driver has deleted his account', 
  `available` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'AVAILABLE = 1, NOT AVAILABLE = 0',   
  `operation_status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'BUSY = 1, NOT BUSY = 0',   
  `driver_rating` TINYINT(1) NOT NULL DEFAULT 5 COMMENT 'drivers overall rating',  
  `booking_cancel_freq` TINYINT(10) NOT NULL DEFAULT 0 COMMENT 'consecutive number of times driver cancels bookings before getting banned',  
  `ban_date` DATETIME NULL COMMENT 'last date and time driver was banned for cancelling trips', 
  `account_active` boolean NOT NULL DEFAULT 0 COMMENT 'active or inactivate driver account',
  `photo_file` varchar(255) COLLATE utf8_unicode_ci,
  `allow_photo_edit` TINYINT(1) NOT NULL DEFAULT 0 COLLATE utf8_unicode_ci COMMENT '0 - driver photo change disabled. 1 - Driver can change photo',
  `allow_vehicle_edit` TINYINT(1) NOT NULL DEFAULT 0 COLLATE utf8_unicode_ci COMMENT '0 - driver vehicle edit disabled. 1 - Driver can edit vehicle',
  `allow_city_edit` TINYINT(1) NOT NULL DEFAULT 0 COLLATE utf8_unicode_ci COMMENT '0 - driver city change disabled. 1 - Driver can change city',
  `disp_lang` varchar(10) DEFAULT 'en' COLLATE utf8_unicode_ci COMMENT 'current user ui language',
  `driving_license_file` varchar(255) COLLATE utf8_unicode_ci,
  `road_worthiness_file` varchar(255) COLLATE utf8_unicode_ci,
  `wallet_amount` DECIMAL(11,2) NOT NULL DEFAULT 0 COMMENT 'Amount left in drivers wallet',
  `reward_points` DECIMAL(11,1) DEFAULT 0 COMMENT 'number of reward points earned',
  `bank_name` varchar(100) COLLATE utf8_unicode_ci,
  `bank_acc_holder_name` varchar(100) COLLATE utf8_unicode_ci,
  `bank_acc_num` varchar(40) COLLATE utf8_unicode_ci,
  `completed_rides` INTEGER DEFAULT 0 COMMENT 'keep track of all-time number of rides completed by this driver',
  `cancelled_rides` INTEGER DEFAULT 0 COMMENT 'keep track of all-time number of rides cancelled by this driver',
  `rejected_rides` INTEGER DEFAULT 0 COMMENT 'keep track of all-time number of rides rejected by this driver',
  `bank_code` varchar(15) COLLATE utf8_unicode_ci,
  `bank_swift_code` varchar(15) COLLATE utf8_unicode_ci,
  `country_code` varchar(3) NOT NULL DEFAULT 'ng' COLLATE utf8_unicode_ci,
  `country_dial_code` varchar(5) NOT NULL DEFAULT '+234' COLLATE utf8_unicode_ci,
  `driver_commision` DECIMAL(4,1) NOT NULL DEFAULT 0 COMMENT 'percentage commision for this driver for every successful booking',
  `allow_alt_cars` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '0 = Driver will not receive trips from alternate vehicles. 1 = Driver has chosen to receive trips from alternate vehicles',
   
  PRIMARY KEY (`driver_id`),
  FULLTEXT (`firstname`,`lastname`),
  INDEX (`route_id`),
  INDEX (`push_notification_token`),
  INDEX (`referal_code`),
  INDEX (`email`),
  INDEX (`account_deleted`),
  INDEX (`phone`),
  INDEX (`account_create_date`),
  INDEX (`is_activated`),
  INDEX (`d_uuid`),
  INDEX (`account_active`)
  
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='driver data';


CREATE TABLE IF NOT EXISTS `cab_tbl_banners` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL COMMENT 'title of banner',
  `excerpt` VARCHAR(255) NOT NULL COMMENT 'summary of the content',
  `content` TEXT NOT NULL COMMENT 'content of the banner',
  `city` INTEGER NOT NULL DEFAULT 0 COMMENT 'id of city route this banner will be displayed',
  `feature_img` VARCHAR(30) COMMENT 'feature image url',
  `visibility` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0 = rider and driver apps, 1 = rider app, 2 = driver app',
  `status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0-inactive, 1 = active',
  `date_created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'time and date this banner was created',       

  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Table for banners displayed on home screens of apps';


CREATE TABLE IF NOT EXISTS `cab_tbl_documents` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL COMMENT 'title of doucment',
  `doc_desc` TEXT NOT NULL COMMENT 'Short description of the document',
  `doc_city` INTEGER NOT NULL DEFAULT 0 COMMENT 'id of city route this document should be required, 0 = all cities',
  `doc_type` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0 - personal document, 1 - Vehicle document',
  `doc_user` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '0 - rider, 1 - driver',
  `doc_expiry` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0 - does not expire, 1 - document expires',
  `doc_id_num` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0 - does not require the user to enter the document ID, 1 - requires the user to enter an ID number',
  `doc_id_num_title` VARCHAR(255) NOT NULL COMMENT 'title of doucment ID number field',
  `doc_id_num_desc` TEXT NOT NULL COMMENT 'Short description of the document ID number input field',
  `status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0-inactive, 1 = active',
  `date_created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'time and date this document was created',       

  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Documents required by drivers or customers on the service';


CREATE TABLE IF NOT EXISTS `cab_tbl_users_documents` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `doc_id` INTEGER NOT NULL DEFAULT 0 COMMENT 'id of document meta',
  `doc_uuid` char(32) NULL COMMENT 'ID to uniquely identify this documnet on the service',
  `u_vehicle_id` INTEGER NOT NULL DEFAULT 0 COMMENT 'id of the drivers vehicle which this document belongs to',
  `u_id` INTEGER NOT NULL DEFAULT 0 COMMENT 'id of the user that has this document',
  `u_type` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0-rider, 1 = driver',
  `u_doc_id_num_title` VARCHAR(255) NULL COMMENT 'title of doucment ID number field',
  `u_doc_id_num` VARCHAR(40) NULL COMMENT 'ID number of this document if applicable',
  `u_can_edit` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0-no, 1 = yes',
  `u_doc_expiry_date` DATE NULL COMMENT 'expiry date of this document',
  `u_doc_title` VARCHAR(255) NOT NULL COMMENT 'title of doucment',
  `u_doc_img` VARCHAR(30) NULL COMMENT 'image url the user uploaded documment',
  `u_doc_status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0-pending, 1 = failed, 2 = expired, 3 = approved',
  `date_created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'time and date this document was uploaded',
  `date_updated` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'time and date this document was updated',       

  PRIMARY KEY (`id`),
  INDEX (`doc_id`),
  INDEX (`doc_uuid`),
  INDEX (`u_id`,`u_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Documents uploaded by riders and drivers';


CREATE TABLE IF NOT EXISTS `cab_tbl_franchise` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `f_uuid` char(32) NULL COMMENT 'ID to uniquely identify this franchise on the service',
  `franchise_name` VARCHAR(50) NOT NULL COMMENT 'name of the franchise',
  `franchise_email` varchar(64) COLLATE utf8_unicode_ci NOT NULL COMMENT 'email, unique',
  `franchise_phone` varchar(20) NOT NULL,
  `pwd_raw` varchar(64) COLLATE utf8_unicode_ci NOT NULL COMMENT 'raw password',
 	`password_hash` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'password in salted and hashed format',
  `franchise_desc` VARCHAR(250) NOT NULL COMMENT 'description of this franchise',
  `bank_name` varchar(100) COLLATE utf8_unicode_ci,
  `bank_acc_holder_name` varchar(100) COLLATE utf8_unicode_ci,
  `bank_acc_num` varchar(20) COLLATE utf8_unicode_ci,
  `bank_code` varchar(15) COLLATE utf8_unicode_ci,
  `bank_swift_code` varchar(15) COLLATE utf8_unicode_ci,
  `franchise_commision` DECIMAL(4,1) NOT NULL DEFAULT 0 COMMENT 'percentage commision for this franchise for every successful booking executed by its driver',
  `fwallet_amount` DECIMAL(11,2) NOT NULL DEFAULT 0 COMMENT 'Amount in franchise wallet',
  `date_created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, 
  PRIMARY KEY (`id`),
  INDEX (`f_uuid`),
  INDEX (`franchise_email`,`pwd_raw`,`password_hash`)

) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Table for franchises';

INSERT IGNORE INTO `cab_tbl_franchise` (`id`,`franchise_name`,`franchise_desc`, `franchise_commision`) VALUES (1,'DropTaxi','Default franchise (Company)','100');



CREATE TABLE IF NOT EXISTS `cab_tbl_coupon_codes` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `coupon_code` VARCHAR(15) NOT NULL COMMENT 'coupon code',
  `coupon_title` VARCHAR(255) NULL COMMENT 'comma seperated ids of vehicles which coupon code is valid for',
  `city` INTEGER NOT NULL COMMENT 'id of city route this coupon code is active in',
  `vehicles` VARCHAR(255) NULL COMMENT 'comma seperated ids of vehicles which coupon code is valid for',
  `visibility` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0 = not visible. riders must enter code, 1 = visible. Riders select and activate code',
  `discount_type` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0 = percentage, 1 = nominal',
  `discount_value` DECIMAL(11,2) NOT NULL DEFAULT 0 COMMENT 'discount value for this coupon code',
  `min_fare` DECIMAL(11,2) NOT NULL DEFAULT 0 COMMENT 'Ride fare must be at least this value in order for coupon to be applied',
  `max_discount_amount` DECIMAL(11,2) NOT NULL DEFAULT 0 COMMENT 'For percentage discount type. this is the maximum percentage amount that can be discounted',
  `limit_count` INTEGER NOT NULL DEFAULT 0 COMMENT 'maximum number of times this coupon code can be used',
  `user_limit_count` INTEGER NOT NULL DEFAULT 1 COMMENT 'maximum number of times this coupon code can be used by a single user',
  `status` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'inactive = 0, active = 1',
  `active_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expiry_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, 
  PRIMARY KEY (`id`),
  UNIQUE KEY `city` (`coupon_code`,`city`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Table for coupon codes';


CREATE TABLE IF NOT EXISTS `cab_tbl_referral` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `beneficiary` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'customer = 0, invitee = 1, 2 = customer and invitee',
  `discount_value` DECIMAL(11,2) NOT NULL DEFAULT 0 COMMENT 'discount value percentage for referral code',
  `status` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'inactive = 0, active = 1',

  PRIMARY KEY (`id`)
  
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Table for referrals';

INSERT IGNORE INTO `cab_tbl_referral` (`id`,`beneficiary`,`discount_value`,`status`) VALUES (1,2,'10','1');


CREATE TABLE IF NOT EXISTS `cab_tbl_referral_drivers` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `beneficiary` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'driver = 0, invitee = 1, 2 = driver and invitee',
  `route_id` INTEGER NULL COMMENT 'id of the route this this referal is valid', 
  `number_of_rides` INTEGER NULL COMMENT 'Number of rides invitee must complete to earn incentive', 
  `number_of_days` INTEGER NULL COMMENT 'Number of days within which invitee must complete number of rides to earn incentive', 
  `invitee_incentive` DECIMAL(11,2) NOT NULL DEFAULT 0 COMMENT 'Target incentive / amount invitee is guaranteed to earn after completing number of rides within set number of days',
  `driver_incentive` DECIMAL(11,2) NOT NULL DEFAULT 0 COMMENT 'Amount earned by old driver after invitee completes number of rides within set number of days',
  `status` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'inactive = 0, active = 1',

  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_route` (`route_id`)
  
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Table for referrals for drivers';

INSERT IGNORE INTO `cab_tbl_referral_drivers` (`id`,`beneficiary`,`route_id`,`number_of_rides`,`number_of_days`,`invitee_incentive`,`driver_incentive`,`status`) VALUES (1,2,1,'20','5','100','50',1);


CREATE TABLE IF NOT EXISTS `cab_tbl_reward_points` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `cur_to_points_conv` VARCHAR(10) NOT NULL DEFAULT 0 COMMENT 'How much money paid by user in city currency equals 1 point',
  `points_to_cur_conv` VARCHAR(10) NOT NULL DEFAULT 0 COMMENT '1 point redeemed equals how much in city currency',
  `status` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'inactive = 0, active = 1',
  `min_points_redeemable` INTEGER NULL COMMENT 'Minimum number of points redeemable by the rider',   
  `date_created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'time and date this reward was created',

  PRIMARY KEY (`id`)
  
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Table for reward points';

INSERT IGNORE INTO `cab_tbl_reward_points` (`id`,`cur_to_points_conv`,`points_to_cur_conv`,`status`,`min_points_redeemable`,`date_created`) VALUES (1,'500','100','1','1','2021-03-15 16:06:12');


CREATE TABLE IF NOT EXISTS `cab_tbl_chats` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `booking_id` INTEGER NOT NULL COMMENT 'id of the booking this chat message is linked to',
  `user_id` INTEGER NOT NULL DEFAULT 0 COMMENT 'id of the rider',
  `driver_id` INTEGER NOT NULL DEFAULT 0 COMMENT 'id of the driver',
  `chat_msg` TEXT NULL COMMENT 'Chat message',
  `date_created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'time and date this chat message was sent',

  PRIMARY KEY (`id`),
  INDEX (`booking_id`)
  
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Table for chat messages';



CREATE TABLE IF NOT EXISTS `cab_tbl_chatsupport` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `session_status` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0 = session is closed, start new session ID on next message. 1 = session is open, continue using the previous session ID ',
  `user_id` INTEGER NOT NULL DEFAULT 0 COMMENT 'id of the rider',
  `driver_id` INTEGER NOT NULL DEFAULT 0 COMMENT 'id of the driver',
  `admin_id` INTEGER NOT NULL DEFAULT 0 COMMENT 'id to indicate message is from admin',
  `rider_recipient_id` INTEGER NOT NULL DEFAULT 0 COMMENT 'receiver id of the rider',
  `driver_recipient_id` INTEGER NOT NULL DEFAULT 0 COMMENT 'receiver id of the driver',
  `chat_msg` TEXT NULL COMMENT 'Chat message',
  `date_created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'time and date this chat message was sent',

  PRIMARY KEY (`id`),
  INDEX (`user_id`,`driver_id`)
  
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Table for chat support messages';


CREATE TABLE IF NOT EXISTS `cab_tbl_driver_location` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `driver_id` INTEGER NOT NULL COMMENT 'id of the driver with this location coord',
  `long` DECIMAL(11,8) NOT NULL COMMENT 'longitude of the driver coord',
  `lat` DECIMAL(11,8) NOT NULL COMMENT 'latitude of the driver coord',
  `b_angle` DECIMAL(4,1) NOT NULL DEFAULT 0 COMMENT 'bearing of the driver current coord from the previous coord',
  `loc_static_status` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT 'static at the location = 1, driver is in motion = 0',
  `loc_static_duration` INTEGER NULL COMMENT 'how many minutes driver has been static in the location',
  `location_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'time and date this coordinate was received',  
    
  PRIMARY KEY (`id`),
  UNIQUE KEY (`driver_id`),
  INDEX (`location_date`),
  INDEX (`long`,`lat`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Table for location';





CREATE TABLE IF NOT EXISTS `cab_tbl_users_location` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INTEGER NOT NULL COMMENT 'id of the user with this location coord',
  `long` DECIMAL(11,8) NOT NULL COMMENT 'longitude of the user coord',
  `lat` DECIMAL(11,8) NOT NULL COMMENT 'latitude of the user coord',
  `location_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'time and date this coordinate was received',  
    
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Table for location';




CREATE TABLE IF NOT EXISTS `cab_tbl_ratings_users` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `booking_id` INTEGER UNSIGNED NOT NULL COMMENT 'id of the booking on which the driver is rated by the customer',
  `user_id` INTEGER UNSIGNED NOT NULL COMMENT 'id of the customer',
  `user_comment` VARCHAR(500) NULL COMMENT 'comment by this user for the driver on this booking',
  `user_rating` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT 'rating given by user for the driver on this booking',    

  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Table for ratings';



CREATE TABLE IF NOT EXISTS `cab_tbl_ratings_drivers` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `booking_id` INTEGER UNSIGNED NOT NULL COMMENT 'id of the booking on which the customer is rated by the driver',
  `driver_id` INTEGER UNSIGNED NOT NULL COMMENT 'id of the driver',
  `driver_comment` VARCHAR(500) NULL COMMENT 'comment by this driver for the user on this booking',
  `driver_rating` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'rating given by driver for the user on this booking',
  
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Table for ratings';


CREATE TABLE IF NOT EXISTS `cab_tbl_sessions` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `token` VARCHAR(20) NOT NULL COMMENT 'code string',
  `user_id` INTEGER UNSIGNED NOT NULL COMMENT 'id of a driver or user this code belongs to',
  `user_type` TINYINT(1) UNSIGNED NOT NULL COMMENT '0 = user, 1 = driver',
  `date_created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'time and date this coordinate was received',       

  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Table for tracking account activations and password resets';


CREATE TABLE IF NOT EXISTS `cab_tbl_notifications` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `person_id` INTEGER UNSIGNED NOT NULL COMMENT 'user id, driver id',
  `user_type` TINYINT(1) UNSIGNED NOT NULL COMMENT '0 = user, 1 = driver',
  `content` VARCHAR(300) NULL COMMENT 'content of the notification',
  `route_id` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'city which users this notification is for. 0 means everyone',
  `n_type` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0=normal notification, 1 = admin notification , 2 = booking notification, 3 = transaction to wallet notification, 4 = ride completed notification, 5 = broadcast message',
  `date_created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'time and date this notification was sent',       

  PRIMARY KEY (`id`),
  INDEX (`person_id`),
  INDEX (`person_id`,`user_type`),
  INDEX (`date_created`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Table for tracking notifications';


CREATE TABLE IF NOT EXISTS `cab_tbl_account_codes` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(20) NOT NULL COMMENT 'code string',
  `user_id` INTEGER UNSIGNED NOT NULL COMMENT 'id of a driver or user this code belongs to',
  `user_type` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0 = user, 1 = driver',
  `context` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'account activation = 0;password reset=1;',
   
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Table for tracking account activations and password resets';


CREATE TABLE IF NOT EXISTS `cab_tbl_bookings` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INTEGER UNSIGNED NOT NULL COMMENT 'id of a driver or user this code belongs to',
  `user_firstname` varchar(64) COLLATE utf8_unicode_ci NULL,
	`user_lastname` varchar(64) COLLATE utf8_unicode_ci NULL,
  `user_phone` varchar(25) NULL,
  `driver_id` INTEGER UNSIGNED NOT NULL DEFAULT 0 COMMENT 'id of a driver assigned to pickup the passenger. 0 = unallocated',
  `driver_firstname` varchar(64) COLLATE utf8_unicode_ci NULL,
	`driver_lastname` varchar(64) COLLATE utf8_unicode_ci NULL,
  `franchise_id` INTEGER UNSIGNED NOT NULL DEFAULT 0 COMMENT 'id of a driver or user this code belongs to',
  `franchise_name` VARCHAR(50) NULL COMMENT 'name of the franchise',
  `driver_phone` varchar(25) NULL,
  `pickup_datetime` DATETIME NULL COMMENT 'time when the passenger should be picked up',
  `pickup_address` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'address passenger will be picked up',
  `pickup_long` varchar(30) NULL COMMENT 'longitude of location passenger should be picked up',
  `pickup_lat` varchar(30) NULL COMMENT 'latitude of location passenger should be picked up',
  `dropoff_datetime` DATETIME NULL COMMENT 'time when the driver dropped the passenger',
  `dropoff_address` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'address the passenger will be dropped off',
  `dropoff_long` varchar(30) NULL COMMENT 'longitude of location passenger should be dropped off',
  `dropoff_lat` varchar(30) NULL COMMENT 'latitude of location passenger should be dropped off',
  `waypoint1_address` varchar(255) COLLATE utf8_unicode_ci NULL COMMENT 'First passenger stop address name',
  `waypoint1_long` varchar(30) NULL COMMENT 'First passenger stop location longitude',
  `waypoint1_lat` varchar(30) NULL COMMENT 'First passenger stop location latitude',
  `waypoint2_address` varchar(255) COLLATE utf8_unicode_ci NULL COMMENT 'Second passenger stop address name',
  `waypoint2_long` varchar(30) NULL COMMENT 'Second passenger stop location longitude',
  `waypoint2_lat` varchar(30) NULL COMMENT 'Second passenger stop location latitude',
  `est_distance` DECIMAL(11,2) NULL COMMENT 'The google estimated distance of the trip in kilometers',
  `est_duration` DECIMAL(11,2) NULL COMMENT 'The google estimated duration of the trip in minutes',
  `distance_travelled` DECIMAL(11,2) NULL COMMENT 'The distance the driver travelled for this trip in meters',
  `estimated_cost` DECIMAL(11,2) NOT NULL DEFAULT 0 COMMENT 'estimated price using google map estimated trip distance and time',
  `actual_cost` DECIMAL(11,2) NOT NULL DEFAULT 0 COMMENT 'computed price using actual distance traveled by the driver and duration of the trip',
  `cur_symbol` VARCHAR(10) NOT NULL DEFAULT '₦' COMMENT 'symbol for the currency currency used for this booking',
  `cur_exchng_rate` DECIMAL(10,5) NOT NULL DEFAULT 1 COMMENT 'current currency exchange rate to the default currency',
  `cur_code` VARCHAR(4) NOT NULL DEFAULT 'NGN' COMMENT '3 character alphabetic code assigned to all currencies eg Naira = NGN',
  `route_id` INTEGER UNSIGNED NOT NULL COMMENT 'id of the route chosen by the passenger for this booking',
  `ride_id` INTEGER UNSIGNED NOT NULL COMMENT 'id of the ride chosen by the passenger for this booking',
  `bk_alt_cars` VARCHAR(50) NULL COMMENT 'Alternate car categories that can receive trip requests for this car type if it is unavailable for this booking',  
  `ride_share` TINYINT(1) UNSIGNED DEFAULT 0 COMMENT '0-do not ride share this booking, 1 = ride share this booking',
  `payment_type` TINYINT(1) UNSIGNED NULL COMMENT 'cash = 1;wallet = 2;card=3;4="POS"',
  `scheduled` TINYINT(1) UNSIGNED DEFAULT 0 COMMENT '0-not scheduled, 1=scheduled',
  `scheduled_notify` TINYINT(1) UNSIGNED DEFAULT 0 COMMENT '0-not notified for scheduled booking, 1= notified for scheduled booking',
  `scheduled_driver` INTEGER UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Driver who have accepted the scheduled booking',
  `retry_booking` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Number of times the rider retried booking after not finding a driver',
  `dispatch_mode` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0 = auto dispatch, 1 = manual dispatch',
  `luggage` TINYINT(1) UNSIGNED NULL COMMENT 'small = 1;medium = 2;large=3;',
  `haspaid` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'paid = 1; not paid = 0',
  `paid_amount` DECIMAL(11,2) NOT NULL DEFAULT 0 COMMENT 'Price paid by user for the ride',
  `hr_set_num_h` INTEGER UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Hourly rate number of hours set 0 = not hourly rate. 1+ = hourly rate set',
  `cancel_amount` DECIMAL(11,2) NOT NULL DEFAULT 0 COMMENT 'Price charged user for cancelling the ride',
  `driver_commision` DECIMAL(4,1) NOT NULL DEFAULT 0 COMMENT 'percentage commision on this booking for this driver',
  `driver_settled` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '1 = driver has been settled for this booking. 0 = driver not settled',
  `franchise_settled` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '1 = franchise has been settled for this booking. 0 = franchise not settled',
  `franchise_commision` DECIMAL(4,1) NOT NULL DEFAULT 0 COMMENT 'percentage commision on this booking for the franchise this driver belongs to',
  `cancel_comment` TEXT NULL COMMENT 'comment why ride was cancelled',
  `transaction_id` INTEGER UNSIGNED NOT NULL DEFAULT 0 COMMENT 'transaction ID of holding record of extra transaction details',
  `status` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'pending = 0, onride = 1, cancelled by user = 2, completed = 3, 4 = cancelled by driver, 5 = cancelled by admin,6 = arrived',
  `coupon_code` VARCHAR(15) NULL COMMENT 'coupon code used',
  `coupon_discount_type` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0 = percentage, 1 = nominal',
  `coupon_discount_value` DECIMAL(11,2) NOT NULL DEFAULT 0 COMMENT 'discount value for this coupon code',
  `coupon_min_fare` DECIMAL(11,2) NOT NULL DEFAULT 0 COMMENT 'Minimum fare for this coupon to be applied',
  `coupon_max_discount` DECIMAL(11,2) NOT NULL DEFAULT 0 COMMENT 'Maximum discount amount for percentage discount',
  `referral_discount_value` DECIMAL(11,2) NOT NULL DEFAULT 0 COMMENT 'discount value percentage for referral code',
  `referral_used` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0 = no referral used, 1 = referral used',
  `completion_code` varchar(6) NULL COMMENT 'completion code rider gives driver for ride completion',
  `b_uuid` char(32) NULL COMMENT 'ID to uniquely identify this booking on the service',
  `drv_acc_long` varchar(30) NULL COMMENT 'longitude of location of driver when he accepted the trip',
  `drv_acc_lat` varchar(30) NULL COMMENT 'latitude of location of driver when he accepted the trip',
  `drv_arv_long` varchar(30) NULL COMMENT 'longitude of location of driver when he arrived to pickup the rider',
  `drv_arv_lat` varchar(30) NULL COMMENT 'latitude of location of driver when he arrived to pickup the rider',
  `drv_start_long` varchar(30) NULL COMMENT 'longitude of location of driver when he started the trip',
  `drv_start_lat` varchar(30) NULL COMMENT 'latitude of location of driver when he started the trip',
  `drv_comp_long` varchar(30) NULL COMMENT 'longitude of location of driver when he started the trip',
  `drv_comp_lat` varchar(30) NULL COMMENT 'latitude of location of driver when he started the trip',
  `total_wait_time` INTEGER UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Total time in seconds the driver spent waiting for the rider. This does not include free wait time duration',
  `total_wait_time_cost` DECIMAL(11,2) NOT NULL DEFAULT 0 COMMENT 'The cost of the total wait time',
  `service_mode` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0 = regular taxi booking, 1 = Quick ride',
  `num_seats` INTEGER UNSIGNED NOT NULL DEFAULT 1 COMMENT 'number of seat requested by the customer. used for ride share',
  `date_created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_arrived` DATETIME NULL,
  `date_arrived_stop_1` DATETIME NULL,
  `date_arrived_stop_2` DATETIME NULL,
  `date_left_stop_1` DATETIME NULL,
  `date_left_stop_2` DATETIME NULL,
  `date_started` DATETIME NULL,
  `date_completed` DATETIME NULL,
  

  PRIMARY KEY (`id`),
  INDEX (`user_id`),
  INDEX (`driver_id`),
  INDEX (`pickup_datetime`),
  INDEX (`date_completed`),
  INDEX (`status`),
  INDEX (`b_uuid`),
  INDEX (`service_mode`),
  INDEX (`date_created`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Table for ride bookings';


CREATE TABLE IF NOT EXISTS `cab_tbl_rides` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `ride_type` VARCHAR(20) NOT NULL COMMENT 'types of rides suv, saloon, vip, premium etc',
  `ride_desc` VARCHAR(250) NOT NULL COMMENT 'decription for the ride type',
  `ride_img` VARCHAR(100) NOT NULL COMMENT 'image filename',
  `num_seats` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Number of passengers this vehicle can take',
  `icon_type` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'The icon type to use when showing this vehicle on the map',
  `avail` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'ride availability status. 1=available, 0=not available',

     
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Table for car types and rates';


INSERT IGNORE INTO `cab_tbl_rides` (`id`,`ride_type`,`ride_desc`,`ride_img`,`avail`,`icon_type`,`num_seats`) VALUES (1,'Sedan','Endless comfort and luxury ride','../img/ride_imgs/ride-sample.png',1,1,4);



CREATE TABLE IF NOT EXISTS `cab_tbl_appinfo_pages` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(100) NOT NULL COMMENT 'title of the page or help topic item',
  `excerpt` VARCHAR(255) NOT NULL COMMENT 'a small description / summary of the page',
  `content` TEXT NOT NULL COMMENT 'Content of the page',
  `cat_id` INTEGER NULL DEFAULT 0 COMMENT 'id of the category this item belongs. used in help topics',
  `type` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0 = a page, 1 = help page topic',
  `show_web` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'for help topics. 0 - do not show on web, 1 = show on web',
  `show_rider` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'for help topics. 0 - do not show on rider app, 1 = show on rider app',
  `show_driver` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'for help topics. 0 - do not show on driver app, 1 = show on driver app',
  `date_created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_modified` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

     
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Table for info pages and help topics';


INSERT IGNORE INTO `cab_tbl_appinfo_pages` (`id`,`title`,`excerpt`,`content`) VALUES (1,'About (Rider App)','Displays info about the Riders App on the Riders App','<h1>The About page content for the rider App goes here.</h1>');
INSERT IGNORE INTO `cab_tbl_appinfo_pages` (`id`,`title`,`excerpt`,`content`) VALUES (2,'About (Driver App)','Displays info about the Drivers App on the Drivers App','<h1>The About page content for the driver App goes here.</h1>');
INSERT IGNORE INTO `cab_tbl_appinfo_pages` (`id`,`title`,`excerpt`,`content`) VALUES (3,'Terms and Privacy Policy (Rider App)','Displays info on the terms and conditions of using the Riders App','<h1>The terms and conditions page content for rider App goes here.</h1>');
INSERT IGNORE INTO `cab_tbl_appinfo_pages` (`id`,`title`,`excerpt`,`content`) VALUES (4,'Terms and Privacy Policy (Driver App)','Displays info on the terms and conditions of using the Riders App','<h1>The terms and conditions page content for driver App goes here.</h1>');
INSERT IGNORE INTO `cab_tbl_appinfo_pages` (`id`,`title`,`excerpt`,`content`) VALUES (5,'Drive with App (Rider App)','Displays info on the Riders App on how to become a driver','<h1>The Drive-With-App content goes here.</h1>');


CREATE TABLE IF NOT EXISTS `cab_tbl_help_cat` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(100) NOT NULL COMMENT 'title of the help category',
  `desc` VARCHAR(255) NOT NULL COMMENT 'a small description / summary of the help category',
  `show_web` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0 - do not show on web, 1 = show on web',
  `show_rider` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0 - do not show on rider app, 1 = show on rider app',
  `show_driver` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0 - do not show on driver app, 1 = show on driver app',
  `date_created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_modified` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

     
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Table for info pages and help topics';


INSERT IGNORE INTO `cab_tbl_help_cat` (`id`,`title`,`desc`) VALUES (1,'Uncategorized','uncategorized help topics');



CREATE TABLE IF NOT EXISTS `cab_tbl_notify_task_queue` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(50) NOT NULL COMMENT 'title of the notification',
  `message` TEXT NOT NULL COMMENT 'notification message to send',
  `type` TINYINT(1) NOT NULL COMMENT '0 - push notification, 1 = email notification',
  `scope` TINYINT(1) NOT NULL COMMENT '0 - to all cities, 1 - to a specific city',
  `user_type` TINYINT(1) NOT NULL COMMENT '0 - customers, 1 - drivers, 2 - staff, 3 - specific customer, 4 - specific driver, 5 - specific staff',
  `route_id` INTEGER NULL DEFAULT 0 COMMENT 'id of city which notification is to be sent to its users',
  `status` TINYINT(1) NOT NULL COMMENT '0-queued/pending, 1 - active, 2 - completed',

  
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Table for car types and rates';



CREATE TABLE IF NOT EXISTS `cab_tbl_coupons_used` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `coupon_id` INTEGER UNSIGNED NOT NULL COMMENT 'id of the coupon',
  `user_id` INTEGER UNSIGNED NOT NULL COMMENT 'id of the user who used the coupon',
  `times_used` INTEGER UNSIGNED NOT NULL COMMENT 'no of times this token has been used',
      
  PRIMARY KEY (`id`),
  INDEX `couponid` (`coupon_id`),
  INDEX `userid` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Table for coupon code usage';


CREATE TABLE IF NOT EXISTS `cab_tbl_referral_regs` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `customer_id` INTEGER UNSIGNED NOT NULL COMMENT 'id of the customer whose referral was used',
  `invitee_id` INTEGER UNSIGNED NOT NULL COMMENT 'id of the invitee who regitered with the customer referral code',
  `user_type` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0 = customer, 1 = driver',
      
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Table for registrations through referrals';


CREATE TABLE IF NOT EXISTS `cab_tbl_zones` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL COMMENT 'title of the zone',
  `city_id` INTEGER NOT NULL COMMENT 'id of city route this zone belongs to',
  `zone_fare_type` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'fare increase type. 1 = multiplier, 2 = additional',
  `zone_fare_value` DECIMAL(11,2) NOT NULL DEFAULT 0 COMMENT 'value to multiply or increase the fare by',
  `zone_bound_coords` TEXT NULL COMMENT 'latitude and logitude coordinates of the zone boundary in json format', 
  `zone_create_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'date and time this zone was created',  
     
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='zone table for zones within cities';



CREATE TABLE IF NOT EXISTS `cab_tbl_routes` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `r_title` VARCHAR(255) NOT NULL COMMENT 'cities: lagos, abuja, portharcourt... Routes:lagos-abuja, lagos-ph',
  `c_name` VARCHAR(255) NULL COMMENT 'inter city name provided by google autocomplete',
  `pickup_city_id` INTEGER NOT NULL DEFAULT 0 COMMENT 'city where the pickup location of an interstate route is in',
  `pick_name` VARCHAR(255) NULL COMMENT 'inter state location pickup name provided by google map autocomplete',
  `drop_name` VARCHAR(255) NULL COMMENT 'inter state location drop-off name provided by google map autocomplete',
  `r_scope` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'inter-city = 0, inter-state = 1',
  `lng` VARCHAR(30) NULL COMMENT 'logitude coordinate for inter city',
  `lat` VARCHAR(30) NULL COMMENT 'latitude coordinate for inter city',
  `pick_lng` VARCHAR(30) NULL COMMENT 'logitude coordinate for inter state',
  `pick_lat` VARCHAR(30) NULL COMMENT 'latitude coordinate for inter state',
  `drop_lng` VARCHAR(30) NULL COMMENT 'logitude coordinate for inter state',
  `drop_lat` VARCHAR(30) NULL COMMENT 'latitude coordinate fo inter state',
  `city_bound_coords` TEXT NULL COMMENT 'latitude and logitude coordinates of the city boundary in json format',
  `dist_unit` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'KM = 0, miles = 1',
  `city_radius` DECIMAL(11,1) NOT NULL DEFAULT 0 COMMENT 'span raduis of the city in',
  `city_currency_id` INTEGER UNSIGNED NOT NULL DEFAULT 0 COMMENT 'ID of the currency to use for this city',
  
     
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique` (`r_title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='ROutes table for location routes';

INSERT IGNORE INTO `cab_tbl_routes` (`id`, `r_title`, `c_name`, `pick_name`, `drop_name`, `r_scope`, `lng`, `lat`, `pick_lng`, `pick_lat`, `drop_lng`, `drop_lat`, `city_bound_coords`, `dist_unit`, `city_radius`, `city_currency_id`) VALUES (NULL, 'World', 'New York, NY, USA', '', '', '0', '-74.0059728', '40.7127753', '', '', '', '', '{&quot;coords&quot;:[{&quot;lat&quot;:84.34951593279807,&quot;lng&quot;:-101.53417881441857},{&quot;lat&quot;:71.78622419442432,&quot;lng&quot;:-164.81542881441857},{&quot;lat&quot;:52.130627551196916,&quot;lng&quot;:-164.81542881441857},{&quot;lat&quot;:-59.825074258600786,&quot;lng&quot;:-84.65917881441857},{&quot;lat&quot;:-53.67344078508936,&quot;lng&quot;:-42.471678814418574},{&quot;lat&quot;:-1.9818037377556204,&quot;lng&quot;:-5.909178814418574},{&quot;lat&quot;:-46.47134248515665,&quot;lng&quot;:10.965821185581426},{&quot;lat&quot;:-45.4942123355953,&quot;lng&quot;:55.965821185581426},{&quot;lat&quot;:-7.584947007416016,&quot;lng&quot;:88.30957118558142},{&quot;lat&quot;:-47.43123970359443,&quot;lng&quot;:112.21582118558142},{&quot;lat&quot;:-47.43123970359443,&quot;lng&quot;:165.65332118558143},{&quot;lat&quot;:-8.97650021479622,&quot;lng&quot;:162.84082118558143},{&quot;lat&quot;:16.083564703044146,&quot;lng&quot;:131.90332118558143},{&quot;lat&quot;:54.645716193516854,&quot;lng&quot;:164.24707118558143},{&quot;lat&quot;:65.1256779112553,&quot;lng&quot;:178.30957118558143},{&quot;lat&quot;:73.46465731315861,&quot;lng&quot;:145.96582118558143},{&quot;lat&quot;:81.84186115792788,&quot;lng&quot;:89.71582118558142},{&quot;lat&quot;:78.79253687849555,&quot;lng&quot;:51.747071185581426},{&quot;lat&quot;:84.06572391921102,&quot;lng&quot;:-22.784178814418574}],&quot;center&quot;:{&quot;lat&quot;:12.262220837098642,&quot;lng&quot;:-25.59667881441851},&quot;radius&quot;:8027.3686721912}', '0', '6000000.0', '1');



CREATE TABLE IF NOT EXISTS `cab_tbl_rides_tariffs` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `ride_id` INTEGER NOT NULL COMMENT 'id of the ride with this tariff',
  `routes_id` INTEGER NOT NULL COMMENT 'routes for this tariff',
  `cost_per_km` DECIMAL(11,2) NOT NULL DEFAULT 0 COMMENT 'rate per kilometer / mile for this tariff and car type',
  `cost_per_minute` DECIMAL(11,2) NOT NULL DEFAULT 0 COMMENT 'rate per minute for this tariff and car type',
  `wait_time` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Time in minutes the driver can wait after he has arrived without the customer being charged',
  `wait_cost_per_minute` DECIMAL(11,2) NOT NULL DEFAULT 0 COMMENT 'wait rate per minute for this tariff and car type',
  `pickup_cost` DECIMAL(11,2) NOT NULL DEFAULT 0 COMMENT 'pickup cost for this tariff and car type',
  `drop_off_cost` DECIMAL(11,2) NOT NULL DEFAULT 0 COMMENT 'drop off cost for this tariff and car type',
  `cancel_cost` DECIMAL(11,2) NOT NULL DEFAULT 0 COMMENT 'cancellation cost for this tariff and car type',
  `init_distance` DECIMAL(11,1) NOT NULL DEFAULT 1 COMMENT 'Initial distance in KM below which only pickup cost will be charged',
  `init_distance_n` DECIMAL(11,1) NOT NULL DEFAULT 1 COMMENT 'Initial distance in KM below which only pickup cost will be charged at night',
  `ncost_per_km` DECIMAL(11,2) NOT NULL DEFAULT 0 COMMENT 'night rate per kilometer / mile for this tariff and car type',
  `ncost_per_minute` DECIMAL(11,2) NOT NULL DEFAULT 0 COMMENT 'night rate per minute for this tariff and car type',
  `nwait_time` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Night time in minutes the driver can wait after he has arrived without the customer being charged',
  `nwait_cost_per_minute` DECIMAL(11,2) NOT NULL DEFAULT 0 COMMENT 'Night wait rate per minute for this tariff and car type',
  `npickup_cost` DECIMAL(11,2) NOT NULL DEFAULT 0 COMMENT 'night pickup cost for this tariff and car type',
  `ndrop_off_cost` DECIMAL(11,2) NOT NULL DEFAULT 0 COMMENT 'night drop off cost for this tariff and car type',
  `ncancel_cost` DECIMAL(11,2) NOT NULL DEFAULT 0 COMMENT 'night cancellation cost for this tariff and car type',
  `cfare_enabled` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'computed fare disabled (riders charged an estimated fare) = 0.computed fare enabled. Riders charged an amount computed from the actual distance tavelled and time spent. = 1',
  `rshare_enabled` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Ride sharing not enabled(every rider get a whole car to himself) = 0.Ride sharing enabled. Riders from different pickup and dropoff locations can share a ride = 1',
  `hr_enabled` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Hourly rate not enabled = 0. Hourly rate enabled = 1',
  `hr_cph` DECIMAL(11,2) NOT NULL DEFAULT 0 COMMENT 'Hourly rate cost per hour',
  `hr_dist` DECIMAL(11,1) NOT NULL DEFAULT 1 COMMENT 'Hourly rate distance in KM',
  `nhr_cph` DECIMAL(11,2) NOT NULL DEFAULT 0 COMMENT 'Night time Hourly rate cost per hour',
  `nhr_dist` DECIMAL(11,1) NOT NULL DEFAULT 1 COMMENT 'Night time Hourly rate distance in KM',
  `pp_enabled` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'peak period disabled = 0.peak period enabled = 1',
  `pp_start` TINYINT(2) UNSIGNED NULL COMMENT 'peak period start hour. in 24hrs format',
  `pp_end` TINYINT(2) UNSIGNED NULL COMMENT 'peak period end hour. in 24hrs format',
  `pp_active_days` VARCHAR(50) NULL COMMENT 'json of the active days this peak period holds',
  `pp_charge_type` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'nominal = 0.multiplier = 1. nominal adds the charge value to the ride fare while multiplier multiplies the ride fare by charge value',
  `pp_charge_value` DECIMAL(11,2) NOT NULL DEFAULT 0 COMMENT 'charge value for the peak period',  
  `alt_cars` VARCHAR(50) NULL COMMENT 'Alternate car categories that can receive trip requests for this car type if it is unavailable',  

  PRIMARY KEY (`id`),
  UNIQUE KEY `unique` (`ride_id`,`routes_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Tariff table for rides on different routes';

INSERT IGNORE INTO `cab_tbl_rides_tariffs` (`id`, `ride_id`, `routes_id`, `cost_per_km`, `cost_per_minute`, `pickup_cost`, `drop_off_cost`, `cancel_cost`, `init_distance`, `init_distance_n`, `ncost_per_km`, `ncost_per_minute`, `npickup_cost`, `ndrop_off_cost`, `ncancel_cost`, `cfare_enabled`, `rshare_enabled`, `pp_enabled`, `pp_start`, `pp_end`, `pp_active_days`, `pp_charge_type`, `pp_charge_value`) VALUES (NULL, '1', '1', '10.00', '1.00', '10.00', '10.00', '10.00', '1.0', '1.0', '20.00', '2.00', '20.00', '20.00', '20.00', '0', '0', '0', '12', '17', ' ', '0', '0.00');


CREATE TABLE IF NOT EXISTS `cab_tbl_vogue_pay` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `merchant_id` VARCHAR(100) NULL COMMENT 'merchant ID as provided by Voguepay',
  `v_transaction_id` VARCHAR(100) NULL COMMENT 'Transaction ID as provided by Voguepay for their record to track this transaction. You can use this to query their DB for more details on the transaction',
  `email` VARCHAR(100) NULL COMMENT 'email address of buyer as provided by voguepay transaction notification response',
  `total` VARCHAR(10) NULL COMMENT 'total amount of course or program being paid for',
  `total_paid_by_buyer` VARCHAR(10) NULL COMMENT 'Actual amount paid by user',
  `total_credited_to_merchant` VARCHAR(10) NULL COMMENT 'Total amount creditable on your account wallet on voguepay',
  `extra_charges_by_merchant` VARCHAR(10) NULL COMMENT 'Extra charges placed on user such as taxes e.t.c',
  `user_type` TINYINT(1) UNSIGNED NOT NULL COMMENT '0 = user, 1 = driver',
  `user_id` INTEGER UNSIGNED NOT NULL COMMENT 'id of a driver or user this code belongs to',
  `transaction_ref` VARCHAR(20) NOT NULL COMMENT 'used as the persons ID',
  `memo` VARCHAR(255) NULL COMMENT 'A description of this transaction',
  `status` VARCHAR(10) NOT NULL COMMENT 'status of this online payment transaction identified by APPROVED, PENDING, FAILED or DISPUTED',
  `date` DATETIME COMMENT 'Date of transaction in the format yyyy-mm-dd hh:ii:ss e.g 2012-01-09 18:56:23',
  `referrer` VARCHAR(255) NULL COMMENT 'The SIS page from which the transaction form was sent to VoguePay',
  `method` VARCHAR(10) NOT NULL COMMENT 'Method/gateway used for payment e.g Interswitch, voguePay e.t.c',
  `fund_maturity` VARCHAR(100) NULL COMMENT 'The date that the will be able to withdraw or spend the amount credited to her voguepay account wallet as a result of this transaction',
  `cur` VARCHAR(10) NOT NULL COMMENT 'Currency in which transaction was executed',
  `franchise_name` VARCHAR(50) NULL COMMENT 'name of the franchise',
  `wallet_balance` DECIMAL(11,2) NOT NULL DEFAULT 0 COMMENT 'amount in wallet after funding',
 
  PRIMARY KEY (`id`)
  
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='This table holds information on all online payments done through the voguepay online payment gateway. please refer to https://voguepay.com/developers for more details on table schema';



  CREATE TABLE IF NOT EXISTS `cab_tbl_wallet_transactions` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `transaction_id` VARCHAR(13) NOT NULL COMMENT 'used as the persons ID',
  `amount` DECIMAL(11,2) NOT NULL DEFAULT 0 COMMENT 'Amount transacted',
  `cur_symbol` VARCHAR(10) NOT NULL DEFAULT '₦' COMMENT 'symbol for the currency currency used for this wallet transaction',
  `cur_exchng_rate` DECIMAL(10,5) NOT NULL DEFAULT 1 COMMENT 'current currency exchange rate to the default currency',
  `cur_code` VARCHAR(4) NOT NULL DEFAULT 'NGN' COMMENT '3 character alphabetic code assigned to all currencies eg Naira = NGN',
  `wallet_balance` DECIMAL(11,2) NOT NULL DEFAULT 0 COMMENT 'amount in wallet after transaction always in default currency value',
  `user_id` INTEGER UNSIGNED NOT NULL COMMENT 'id of the driver / customer / franchise',
  `voguepay_id` INTEGER UNSIGNED NULL COMMENT 'id of the voguepay record is transaction was from user app',
  `book_id` INTEGER UNSIGNED NOT NULL DEFAULT 0 COMMENT 'id of the booking for this wallet transaction funding',
  `user_type` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'type of user the user_id belongs: 0 rider, 1 = driver, 2 = franchise',
  `desc` VARCHAR(250) NULL COMMENT 'description of this transaction',
  `type` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'user wallet funding 0, wallet funding admin = 1, Earnings wallet credit = 2 Earning wallet debit = 3',
  `transaction_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'date and time this transaction took place',
  
  PRIMARY KEY (`id`),
  INDEX (`transaction_id`),
  INDEX (`user_id`),
  INDEX (`book_id`),
  INDEX (`type`),
  INDEX (`transaction_date`)
  

) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Table for transactions'; 



CREATE TABLE IF NOT EXISTS `cab_tbl_wallet_withdrawal` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `person_id` INTEGER NOT NULL COMMENT 'id of the driver or franchise the requested witdrawal',
  `user_type` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'type of user the user_id belongs: 0 driver, 1 = franchise',
  `wallet_amount` DECIMAL(11,2) NOT NULL DEFAULT 0 COMMENT 'amount in wallet when request was made',
  `withdrawal_amount` DECIMAL(11,2) NOT NULL DEFAULT 0 COMMENT 'amount requested to be withdrawn',
  `cur_symbol` VARCHAR(10) NOT NULL DEFAULT '₦' COMMENT 'symbol for the currency currency used for this wallet transaction',
  `cur_exchng_rate` DECIMAL(10,5) NOT NULL DEFAULT 1 COMMENT 'current currency exchange rate to the default currency',
  `cur_code` VARCHAR(4) NOT NULL DEFAULT 'NGN' COMMENT '3 character alphabetic code assigned to all currencies eg Naira = NGN',
  `wallet_balance` DECIMAL(11,2) NOT NULL DEFAULT 0 COMMENT 'amount in wallet after withdrawal was serviced',
  `request_status` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0 - pending request,1 - cancelled request,2-serviced request',
  `date_requested` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'date and time this withrawal was requested', 
  `date_settled` DATETIME NULL COMMENT 'date and time this withrawal was settled',  
    
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Table for wallet withdrawals';


CREATE TABLE IF NOT EXISTS `cab_tbl_payouts` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `staff_id` INTEGER UNSIGNED NOT NULL DEFAULT 0,
  `staff_firstname` varchar(64) COLLATE utf8_unicode_ci NULL,
	`staff_lastname` varchar(64) COLLATE utf8_unicode_ci NULL,
  `payout_type` TINYINT(1) NOT NULL COMMENT '1=driver payout, 2 = franchise payout, 3 = partner payout, 4 = other',
  `driver_id` INTEGER UNSIGNED NOT NULL DEFAULT 0,
  `driver_firstname` varchar(64) COLLATE utf8_unicode_ci NULL,
	`driver_lastname` varchar(64) COLLATE utf8_unicode_ci NULL,
  `driver_phone` varchar(25) NULL,
  `driver_franchise_name` varchar(25) NULL,
  `franchise_id` INTEGER UNSIGNED NOT NULL DEFAULT 0 COMMENT 'id of franchise for payout',
  `franchise_name` VARCHAR(50) NULL COMMENT 'name of the franchise',
  `partner_name` VARCHAR(50) NULL COMMENT 'name of the partner',
  `other_name` VARCHAR(50) NULL COMMENT 'name of the other thing paid out to',
  `payout_comment` VARCHAR(250) NULL COMMENT 'comment for payout',
  `date_payout` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `payout_amount` DECIMAL(11,2) NOT NULL DEFAULT 0 COMMENT 'Amount paid out',
  
     
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Table for pay-outs';


CREATE TABLE IF NOT EXISTS `cab_tbl_wallet_fund` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `staff_id` INTEGER UNSIGNED NOT NULL DEFAULT 0,
  `staff_firstname` varchar(64) COLLATE utf8_unicode_ci NULL,
	`staff_lastname` varchar(64) COLLATE utf8_unicode_ci NULL,
  `fund_type` TINYINT(1) NOT NULL COMMENT '1=driver wallet funding, 2 = customer wallet funding, 3 = staff wallet funding',
  `driver_id` INTEGER UNSIGNED NOT NULL DEFAULT 0,
  `driver_firstname` varchar(64) COLLATE utf8_unicode_ci NULL,
	`driver_lastname` varchar(64) COLLATE utf8_unicode_ci NULL,
  `driver_phone` varchar(25) NULL,
  `franchise_name` VARCHAR(50) NULL COMMENT 'name of the franchise',
  `customer_id` INTEGER UNSIGNED NOT NULL DEFAULT 0,
  `customer_firstname` varchar(64) COLLATE utf8_unicode_ci NULL,
	`customer_lastname` varchar(64) COLLATE utf8_unicode_ci NULL,
  `customer_phone` varchar(25) NULL,  
  `fund_comment` VARCHAR(250) NULL COMMENT 'comment for funding',
  `date_fund` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fund_amount` DECIMAL(11,2) NOT NULL DEFAULT 0 COMMENT 'Amount funded',
  `cur_symbol` VARCHAR(10) NOT NULL DEFAULT '₦' COMMENT 'symbol for the currency currency used for this wallet transaction',
  `cur_exchng_rate` DECIMAL(10,5) NOT NULL DEFAULT 1 COMMENT 'current currency exchange rate to the default currency',
  `cur_code` VARCHAR(4) NOT NULL DEFAULT 'NGN' COMMENT '3 character alphabetic code assigned to all currencies eg Naira = NGN',
  `wallet_balance` DECIMAL(11,2) NOT NULL DEFAULT 0 COMMENT 'amount in wallet after funding',
     
  PRIMARY KEY (`id`),
  INDEX (`date_fund`),
  INDEX (`customer_id`),
  INDEX (`driver_id`),
  INDEX (`staff_id`),
  INDEX (`fund_type`)


) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Table for wallet funding';


CREATE TABLE IF NOT EXISTS `cab_tbl_pgateway` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `p_transaction_ref` VARCHAR(64) NULL COMMENT 'Transaction reference as provided by gateway to track this transaction. You can use this to query their DB for more details on the transaction',  
  `transaction_ref` VARCHAR(15) NOT NULL COMMENT 'Transaction reference code generated by platform to track this transaction ',
  `access_code` VARCHAR(30) NULL COMMENT 'Used by some gateways to charge a card after initiating a transaction',
  `status` VARCHAR(15) NULL COMMENT 'status of this payment transaction identified by SUCCESS, FAILED, ABANDONED',
  `gateway_resp` VARCHAR(50) NULL COMMENT 'message response returned by gateway',
  `amount` DECIMAL(11,2) NULL COMMENT 'amount charged for this transaction',
  `date` DATETIME COMMENT 'Date of transaction in the format yyyy-mm-dd hh:ii:ss e.g 2012-01-09 18:56:23',
  `gateway` VARCHAR(10) NOT NULL COMMENT 'gateway used for payment e.g Interswitch, voguePay, paystack e.t.c',
  `cur` VARCHAR(5) NULL COMMENT 'Currency in which transaction was executed',
  `user_type` TINYINT(1) UNSIGNED NOT NULL COMMENT '0 = user, 1 = driver',
  `user_id` INTEGER UNSIGNED NOT NULL COMMENT 'id of a driver or user this code belongs to',
  `memo` TEXT NULL COMMENT 'A description of this transaction',
 
  PRIMARY KEY (`id`),
  INDEX (`transaction_ref`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='This table holds information on all online payments done through the online payment gateway.';



CREATE TABLE IF NOT EXISTS `cab_tbl_driver_allocate` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `booking_id` INTEGER UNSIGNED NOT NULL DEFAULT 0 COMMENT 'id of booking driver is allocated',
  `driver_id` INTEGER UNSIGNED NOT NULL DEFAULT 0 COMMENT 'id of driver allocated to booking',
  `status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0 = driver not responded, 1 = driver accepted, 2 = driver rejected, 3 = auto-rejected as driver didnt respond on time or before timeout, 4 = allocated task finalized or completed',
  `date_allocated` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,  
     
  PRIMARY KEY (`id`),
  INDEX (`driver_id`),
  INDEX (`status`),
  INDEX (`booking_id`),
  INDEX (`date_allocated`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Table for tracking driver allocations to bookings';



CREATE TABLE IF NOT EXISTS `cab_tbl_driver_travel_route` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `booking_id` INTEGER UNSIGNED NOT NULL DEFAULT 0 COMMENT 'id of booking driver is serviced',
  `driver_id` INTEGER UNSIGNED NOT NULL DEFAULT 0 COMMENT 'id of driver who serviced the booking',
  `route_data` MEDIUMTEXT NULL COMMENT 'serialized driver travel route data',
   
     
  PRIMARY KEY (`id`),
  INDEX (`driver_id`),
  INDEX (`booking_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Table for tracking driver travel route for booking he accepted or serviced';


CREATE TABLE IF NOT EXISTS `cab_tbl_currencies` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(50) NOT NULL COMMENT 'name of the currency e.g Naira',
  `iso_code` VARCHAR(4) NOT NULL COMMENT '3 character alphabetic code assigned to all currencies eg Naira = NGN',
  `symbol` VARCHAR(10) NOT NULL COMMENT 'symbol for the currency currencies eg Naira = NGN',
  `exchng_rate` DECIMAL(10,5) NOT NULL DEFAULT 0 COMMENT 'current currency exchange rate to the default currency',
  `default` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0 = not default, 1 = default currency',
  `date_created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, 
  PRIMARY KEY (`id`),
  UNIQUE KEY `iso_code` (`iso_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Table for currencies';

INSERT IGNORE INTO `cab_tbl_currencies` (`id`, `name`, `iso_code`, `symbol`, `exchng_rate`, `default`) VALUES (1, 'US Dollar', 'USD', '$', '1.00000', '1');


CREATE TABLE IF NOT EXISTS `cab_tbl_currency_list` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(50) NOT NULL COMMENT 'name of the currency e.g Naira',
  `code` VARCHAR(4) NOT NULL COMMENT '3 character alphabetic code assigned to all currencies eg Naira = NGN',
  `symbol` VARCHAR(10) NOT NULL COMMENT 'symbol for the currency currencies eg ₦',
  
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Table for currency list';


INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('UAE Dirham','AED','د.إ');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Afghani','AFN','Af');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Lek','ALL','L');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Armenian Dram','AMD','Դ');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Kwanza','AOA','Kz');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Argentine Peso','ARS','$');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Australian Dollar','AUD','$');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Aruban Guilder/Florin','AWG','ƒ');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Azerbaijanian Manat','AZN','ман');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Konvertibilna Marka','BAM','КМ');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Barbados Dollar','BBD','$');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Taka','BDT','৳');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Bulgarian Lev','BGN','лв');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Bahraini Dinar','BHD','ب.د');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Burundi Franc','BIF','₣');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Bermudian Dollar','BMD','$');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Brunei Dollar','BND','$');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Boliviano','BOB','Bs.');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Brazilian Real','BRL','R$');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Bahamian Dollar','BSD','$');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Ngultrum','BTN','');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Pula','BWP','P');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Belarussian Ruble','BYR','Br');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Belize Dollar','BZD','$');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Canadian Dollar','CAD','$');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Congolese Franc','CDF','₣');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Swiss Franc','CHF','₣');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Chilean Peso','CLP','$');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Yuan','CNY','¥');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Colombian Peso','COP','$');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Costa Rican Colon','CRC','₡');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Cuban Peso','CUP','$');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Cape Verde Escudo','CVE','$');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Czech Koruna','CZK','Kč');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Djibouti Franc','DJF','₣');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Danish Krone','DKK','kr');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Dominican Peso','DOP','$');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Algerian Dinar','DZD','د.ج');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Egyptian Pound','EGP','E£');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Nakfa','ERN','Nfk');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Ethiopian Birr','ETB','ብር');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Euro','EUR','€');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Fiji Dollar','FJD','$');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Falkland Islands Pound','FKP','£');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Pound Sterling','GBP','£');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Lari','GEL','ლ');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Cedi','GHS','₵');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Gibraltar Pound','GIP','£');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Dalasi','GMD','D');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Guinea Franc','GNF','₣');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Quetzal','GTQ','Q');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Guyana Dollar','GYD','$');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Hong Kong Dollar','HKD','$');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Lempira','HNL','L');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Croatian Kuna','HRK','Kn');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Gourde','HTG','G');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Forint','HUF','Ft');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Rupiah','IDR','Rp');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('New Israeli Shekel','ILS','₪');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Indian Rupee','INR','₹');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Iraqi Dinar','IQD','ع.د');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Iranian Rial','IRR','﷼');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Iceland Krona','ISK','Kr');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Jamaican Dollar','JMD','$');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Jordanian Dinar','JOD','د.ا');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Yen','JPY','¥');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Kenyan Shilling','KES','Sh');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Som','KGS','');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Riel','KHR','៛');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('North Korean Won','KPW','₩');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('South Korean Won','KRW','₩');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Kuwaiti Dinar','KWD','د.ك');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Cayman Islands Dollar','KYD','$');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Tenge','KZT','〒');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Kip','LAK','₭');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Lebanese Pound','LBP','ل.ل');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Sri Lanka Rupee','LKR','Rs');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Liberian Dollar','LRD','$');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Loti','LSL','L');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Libyan Dinar','LYD','ل.د');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Moroccan Dirham','MAD','د.م.');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Moldavian Leu','MDL','L');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Malagasy Ariary','MGA','');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Denar','MKD','ден');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Kyat','MMK','K');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Tugrik','MNT','₮');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Pataca','MOP','P');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Ouguiya','MRO','UM');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Mauritius Rupee','MUR','₨');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Rufiyaa','MVR','ރ.');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Kwacha','MWK','MK');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Mexican Peso','MXN','$');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Malaysian Ringgit','MYR','RM');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Metical','MZN','MTn');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Namibia Dollar','NAD','$');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Naira','NGN','₦');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Cordoba Oro','NIO','C$');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Norwegian Krone','NOK','kr');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Nepalese Rupee','NPR','₨');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('New Zealand Dollar','NZD','$');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Rial Omani','OMR','ر.ع.');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Balboa','PAB','B/.');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Nuevo Sol','PEN','S/.');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Kina','PGK','K');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Philippine Peso','PHP','₱');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Pakistan Rupee','PKR','₨');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('PZloty','PLN','zł');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Guarani','PYG','₲');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Qatari Rial','QAR','ر.ق');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Leu','RON','L');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Serbian Dinar','RSD','din');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Russian Ruble','RUB','р.');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Rwanda Franc','RWF','₣');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Saudi Riyal','SAR','ر.س');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Solomon Islands Dollar','SBD','$');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Seychelles Rupee','SCR','₨');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Sudanese Pound','SDG','£');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Swedish Krona','SEK','kr');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Singapore Dollar','SGD','$');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Saint Helena Pound','SHP','£');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Leone','SLL','Le');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Somali Shilling','SOS','Sh');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Suriname Dollar','SRD','$');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Dobra','STD','Db');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Syrian Pound','SYP','ل.س');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Lilangeni','SZL','L');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Baht','THB','฿');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Somoni','TJS','ЅМ');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Manat','TMT','m');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Tunisian Dinar','TND','د.ت');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Pa’anga','TOP','T$');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Turkish Lira','TRY','₺');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Trinidad and Tobago Dollar','TTD','$');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Taiwan Dollar','TWD','$');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Tanzanian Shilling','TZS','Sh');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Hryvnia','UAH','₴');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Uganda Shilling','UGX','Sh');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('US Dollar','USD','$');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Peso Uruguayo','UYU','$');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Uzbekistan Sum','UZS','');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Bolivar Fuerte','VEF','Bs F');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Dong','VND','₫');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Vatu','VUV','Vt');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Tala','WST','T');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('CFA Franc BCEAO','XAF','₣');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('East Caribbean Dollar','XCD','$');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('CFP Franc','XPF','₣');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Yemeni Rial','YER','﷼');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Rand','ZAR','R');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Zambian Kwacha','ZMW','ZK');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('Zimbabwe Dollar','ZWL','$');
INSERT IGNORE INTO `cab_tbl_currency_list` (`name`, `code`, `symbol`) VALUES ('West African CFA franc','XOF','Fr');

