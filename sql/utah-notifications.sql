--
--	Utah-SMS Notifications
--

INSERT INTO `notifications` (`function_name`, `email`, `local`, `min_user_level`)
VALUES ('Burn Director Final Approval', 1, 1, 7);

INSERT INTO `user_notifications` (`user_id`, `notification_id`)
VALUES (1,1);

INSERT INTO `user_notifications` (`user_id`, `notification_id`)
VALUES (19,1);


--
--	Pre-Burn Renewal
--

INSERT INTO `notifications` (`function_name`, `email`, `local`, `min_user_level`)
VALUES ('Pre-Burn Renewal', 0, 1, 7);

INSERT INTO `user_notifications` (`user_id`, `notification_id`)
VALUES (1,2);

INSERT INTO `user_notifications` (`user_id`, `notification_id`)
VALUES (4,2);

--
--	Pre-Burn Revision
--

INSERT INTO `notifications` (`function_name`, `email`, `local`, `min_user_level`)
VALUES ('Pre-Burn Revision', 0, 1, 7);

INSERT INTO `user_notifications` (`user_id`, `notification_id`)
VALUES (1,3);

INSERT INTO `user_notifications` (`user_id`, `notification_id`)
VALUES (4,3);

--
--	Burn Project Submittal
--

INSERT INTO `notifications` (`function_name`, `email`, `local`, `min_user_level`)
VALUES ('Burn Project Submittal', 1, 1, 6);

INSERT INTO `user_notifications` (`user_id`, `notification_id`)
VALUES (1,4);

INSERT INTO `user_notifications` (`user_id`, `notification_id`)
VALUES (4,4);

--
--	Pre-Burn Submittal
--

INSERT INTO `notifications` (`function_name`, `email`, `local`, `min_user_level`)
VALUES ('Pre-Burn Submittal', 1, 1, 6);

INSERT INTO `user_notifications` (`user_id`, `notification_id`)
VALUES (1,5);

INSERT INTO `user_notifications` (`user_id`, `notification_id`)
VALUES (4,5);

--
--	Burn Submittal
--

INSERT INTO `notifications` (`function_name`, `email`, `local`, `min_user_level`)
VALUES ('Burn Submittal', 1, 1, 6);

INSERT INTO `user_notifications` (`user_id`, `notification_id`)
VALUES (1,6);

INSERT INTO `user_notifications` (`user_id`, `notification_id`)
VALUES (4,6);

--
--	Accomplishment Submittal
--

INSERT INTO `notifications` (`function_name`, `email`, `local`, `min_user_level`)
VALUES ('Accomplishment Submittal', 1, 1, 6);

INSERT INTO `user_notifications` (`user_id`, `notification_id`)
VALUES (1,7);

INSERT INTO `user_notifications` (`user_id`, `notification_id`)
VALUES (4,7);

--
--	Documentation Submittal
--

INSERT INTO `notifications` (`function_name`, `email`, `local`, `min_user_level`)
VALUES ('Documentation Submittal', 1, 1, 6);

INSERT INTO `user_notifications` (`user_id`, `notification_id`)
VALUES (1,8);

INSERT INTO `user_notifications` (`user_id`, `notification_id`)
VALUES (4,8);

--
--	Burn Final Approval Submittal
--

INSERT INTO `notifications` (`function_name`, `email`, `local`, `min_user_level`)
VALUES ('Burn Recieved Final Approval', 1, 1, 1);

INSERT INTO `user_notifications` (`user_id`, `notification_id`)
SELECT user_id, 9 as notification_id 
FROM users
WHERE level_id > 0 
AND level_id <= 5; 

