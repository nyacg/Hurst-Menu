--
-- MySQL 5.5.24
-- Sun, 20 Apr 2014 16:24:53 +0000
--

CREATE TABLE `attendance` (
   `date` date not null,
   `shell` int(11) default '0',
   `remove` int(11) default '0',
   `fifth` int(11) default '0',
   `LVI` int(11) default '0',
   `UVI` int(11) default '0',
   `actual_breakfast` int(11) default '0',
   `actual_lunch` int(11) default '0',
   `actual_supper` int(11) default '0',
   PRIMARY KEY (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `item` (
   `item_id` int(11) not null auto_increment,
   `item_name` varchar(100) not null,
   PRIMARY KEY (`item_id`),
   UNIQUE KEY (`item_name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=11128;


CREATE TABLE `lunch` (
   `date` date not null,
   `soup_id` int(11),
   `main_meat_id` int(11),
   `main_fish_id` int(11),
   `main_vegetarian_id` int(11),
   `potato_id` int(11),
   `veg_1_id` int(11),
   `veg_2_id` int(11),
   `veg_3_id` int(11),
   `alternative_id` int(11),
   `sauce_1_id` int(11),
   `sauce_2_id` int(11),
   `dessert_id` int(11),
   PRIMARY KEY (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `message` (
   `message_id` int(11) not null auto_increment,
   `date` date,
   `time` time,
   `name` varchar(30),
   `email` varchar(45),
   `message` text,
   `ip_address` varchar(45),
   PRIMARY KEY (`message_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=45;


CREATE TABLE `supper` (
   `date` date not null,
   `soup_id` int(11),
   `main_meat_id` int(11),
   `main_fish_id` int(11),
   `main_vegetarian_id` int(11),
   `staple_id` int(11),
   `veg_1_id` int(11),
   `veg_2_id` int(11),
   `sauce_1_id` int(11),
   `sauce_2_id` int(11),
   `dessert_id` int(11),
   PRIMARY KEY (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `user` (
   `user_id` int(11) not null auto_increment,
   `email_address` varchar(60) not null,
   `password` varchar(45) not null,
   `receive_suggestions` binary(1) default '0',
   PRIMARY KEY (`user_id`),
   UNIQUE KEY (`email_address`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=4;


CREATE TABLE `vote` (
   `date` date not null,
   `item_id` int(11) not null,
   `likes` int(11) default '0',
   `dislikes` int(11) default '0',
   `item_type` varchar(25) not null,
   PRIMARY KEY (`date`,`item_id`),
   KEY `fk_votes_item_idx` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;