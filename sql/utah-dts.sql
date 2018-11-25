--
-- Utah Smoke Management System
--

--
-- Table structure for table `accomplishment_completeness`
--

DROP TABLE IF EXISTS `accomplishment_completeness`;

CREATE TABLE `accomplishment_completeness` (
  `completeness_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text,
  `class` text,
  PRIMARY KEY (`completeness_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `accomplishment_completeness`
--

LOCK TABLES `accomplishment_completeness` WRITE;

INSERT INTO `accomplishment_completeness` 
VALUES (1,'Incomplete','Daily Accomplishment is missing required fields.','label label-danger'),
(2,'Complete','Daily Accomplishment has all required fields.','label label-success');

UNLOCK TABLES;

--
-- Table structure for table `accomplishment_files`
--

DROP TABLE IF EXISTS `accomplishment_files`;

CREATE TABLE `accomplishment_files` (
  `accomplishment_file_id` int(11) NOT NULL AUTO_INCREMENT,
  `accomplishment_id` int(11) NOT NULL,
  `file_id` int(11) NOT NULL,
  PRIMARY KEY (`accomplishment_file_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `accomplishment_statuses`
--

DROP TABLE IF EXISTS `accomplishment_statuses`;

CREATE TABLE `accomplishment_statuses` (
  `status_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `class` text,
  PRIMARY KEY (`status_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `accomplishment_statuses`
--

LOCK TABLES `accomplishment_statuses` WRITE;

INSERT INTO `accomplishment_statuses` 
VALUES (4,'Approved','Burn accomplishment has received final approval','label label-success btn btn-success'),
(3,'Revision Requested','Burn accomplishment needs to be revised and submitted again.','label label-danger btn btn-danger'),
(2,'Under Review','Burn accomplishment is submitted but pending approval.','label label-warning btn btn-warning'),
(1,'Draft','Burn accomplishment is saved but not submitted','label label-warning btn btn-warning');

UNLOCK TABLES;

--
-- Table structure for table `accomplishment_fuels`
--

DROP TABLE IF EXISTS `accomplishment_fuels`;

CREATE TABLE `accomplishment_fuels` (
  `accomplishment_fuel_id` int(11) NOT NULL AUTO_INCREMENT,
  `accomplishment_id` int(11) NOT NULL,
  `fuel_id` int(11) NOT NULL,
  `black_acres` float DEFAULT NULL,
  `ton_per_acre` float DEFAULT NULL,
  `total_tons` float DEFAULT NULL,
  `tons_emitted` float DEFAULT NULL,
  PRIMARY KEY (`accomplishment_fuel_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `fuels`
--

DROP TABLE IF EXISTS `fuels`;

CREATE TABLE `fuels` (
  `fuel_id` int(11) NOT NULL AUTO_INCREMENT,
  `emission_type_id` int(11) DEFAULT NULL,
  `fuel_type_id` int(11) DEFAULT NULL,
  `ef` float DEFAULT NULL,
  `show_on_form` tinyint(1) DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `reference` text DEFAULT NULL,
  PRIMARY KEY (`fuel_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `consumed_types`
--

LOCK TABLES `fuels` WRITE;

INSERT INTO `fuels`
VALUES (1, 1, 1,  0.005, '1', '1'),
(2, 1, 2, 0.01255, '1', '1'),
(3, 1, 3, 0.005, '1', '1'),
(4, 1, 4, 0.01005, '1', '1'),
(5, 1, 5, 0.01495, '1', '1'),
(6, 1, 6, 0.0125, '1', '1'),
(7, 1, 7, 0.0125, '1', '1'),
(8, 1, 8, 0.0125, '1', '1'),
(9, 1, 9, 0.0125, '1', '1'),
(10, 1, 10, 0.0125, '1', '1'),
(11, 1, 11, 0.01155, '1', '1'),
(12, 1, 12, 0.01155, '1', '1'),
(13, 1, 13, 0.01155, '1', '1');

UNLOCK TABLES;

--
--  Table structure for table `fuel_types`
--

DROP TABLE IF EXISTS `fuel_types`;

CREATE TABLE `fuel_types` (
  `fuel_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `fuel_type` varchar(255) NOT NULL,
  `nffl_model` int(11) DEFAULT NULL,
  `ton_per_acre` float DEFAULT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`fuel_type_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

LOCK TABLES `fuel_types` WRITE;

INSERT INTO `fuel_types` (`fuel_type_id`, `nffl_model`, `fuel_type`, `ton_per_acre`)
VALUES (1, 1, 'Short Grass', 2.5),
(2, 2, 'Timber', 9.4),
(3, 3, 'Tall Grass', 2.8),
(4, 4, 'Chaparral', 7.8),
(5, 5, 'Brush', 15),
(6, 6, 'Dormant Brush/Slash', 14.5),
(7, 7, 'Southern Rough', 20.8),
(8, 8, 'Closed Timber Litter', 21.3),
(9, 9, 'Hardwood Litter', 11.5),
(10, 10, 'Timber', 36.8),
(11, 11, 'Light Slash', 32.0),
(12, 12, 'Medium Slash', 45),
(13, 13, 'Heavy Slash', 49);

UNLOCK TABLES;

--
--  Table structure for table `emission_types`
--

DROP TABLE IF EXISTS `emission_types`;

CREATE TABLE `emission_types` (
  `emission_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `emission_type` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`emission_type_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

LOCK TABLES `emission_types` WRITE;

INSERT INTO `emission_types`
VALUES (1, 'PM 10', 'Particulate Matter 10 Microns'),
(2, 'PM 2.5', 'Particulate Matter 2.5 Microns'),
(3, 'NMOC', 'Nonmethane Organic Compounds (identified)'),
(4, 'CO', 'Carbon Monoxide'),
(5, 'CO2', 'Carbon Dioxide'),
(6, 'CH4', 'Methane'),
(7, 'NOX', 'Nitrogen Oxides'),
(8, 'SO2', 'Sulfur Dioxide'),
(9, 'NH3', 'Ammonia'),
(10, 'BC', 'Black Carbon'),
(11, 'OC', 'Organic Carbon');
(12, '1,3-Butadiene', NULL),
(13, '2,3,7,8-TCDD TEQ', NULL),
(14, 'Acetaldehyde', NULL),
(15, 'Acrolein', NULL),
(16, 'Anthracene', NULL),
(17, 'Benz(a)anthracene', NULL),
(18, 'Benzene', NULL),
(19, 'Benzo(a)fluoranthene', NULL),
(20, 'Benzo(a)pyrene', NULL),
(21, 'Benzo(c)phenanthrene', NULL),
(22, 'Benzo(e)Pyrene', NULL),
(23, 'Benzo(g,h,i)perylene', NULL),
(24, 'Benzo(k)fluoranthene', NULL),
(25, 'Benzofluoranthenes', NULL),
(26, 'Carbonyl sulfide', NULL),
(27, 'Chrysene', NULL),
(28, 'Fluoranthene', NULL),
(29, 'Formaldehyde', NULL),
(30, 'Indeno(1,2,3-cd)pyrene', NULL),
(31, 'Methyl chloride', NULL),
(32, 'Methylanthracene', NULL),
(33, 'Methylbenzopyrenes', NULL),
(34, 'Methylchrysene', NULL),
(35, 'Methylpyrene,-Fluoranthene', NULL),
(36, 'N-hexane', NULL),
(37, 'O.m.p-xylene', NULL),
(38, 'Perylene', NULL),
(39, 'Phenanthrene', NULL),
(40, 'Pyrene', NULL),
(41, 'Toluen', NULL);



UNLOCK TABLES;

--
-- Table structure for table `accomplishments`
--

DROP TABLE IF EXISTS `accomplishments`;

CREATE TABLE `accomplishments` (
  `accomplishment_id` int(11) NOT NULL AUTO_INCREMENT,
  `agency_id` int(11) NOT NULL,
  `district_id` int(11) NOT NULL,
  `burn_project_id` int(11) NOT NULL,
  `pre_burn_id` int(11) NOT NULL,
  `burn_id` int(11) NOT NULL,
  `location` text DEFAULT NULL,
  `added_by` int(11) NOT NULL,
  `added_on` timestamp NULL DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `submitted_by` int(11) DEFAULT NULL,
  `submitted_on` timestamp NULL DEFAULT NULL,
  `clearing_index` int(11) DEFAULT NULL,
  `state_id` int(11) DEFAULT NULL,
  `state_comment` text DEFAULT NULL,
  `resume_date` date DEFAULT NULL ALLOW NULL,
  `wfu_updates` tinyint(1) DEFAULT '0',
  `wfu_remarks` text DEFAULT NULL,
  `black_acres_change` float DEFAULT NULL,
  `total_year_acres` float DEFAULT NULL,
  `total_project_acres` float DEFAULT NULL,
  `start_datetime` timestamp NULL DEFAULT NULL,
  `end_datetime` timestamp NULL DEFAULT NULL,
  `public_interest_id` int(11) DEFAULT NULL,
  `day_vent_id` int(11) DEFAULT NULL,
  `night_smoke_id` int(11) DEFAULT NULL,
  `swr_plan_met` tinyint(1) DEFAULT '0',
  `primary_ert_id` int(11) DEFAULT NULL,
  `primary_ert_pct` int(11) DEFAULT NULL,
  `alternate_primary_ert` text DEFAULT NULL,
  `secondary_ert_id` int(11) DEFAULT NULL,
  `alternate_secondary_ert` text DEFAULT NULL,
  `manager_name` varchar(255) DEFAULT NULL,
  `manager_number` varchar(255) DEFAULT NULL,
  `manager_cell` varchar(255) DEFAULT NULL,
  `manager_fax` varchar(255) DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  `completeness_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`accomplishment_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

--
-- Table structure for table `accomplishment_states`
--

DROP TABLE IF EXISTS `accomplishment_states`;

CREATE TABLE `accomplishment_states` (
  `accomplishment_state_id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`accomplishment_state_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

LOCK TABLES `accomplishment_states` WRITE;

INSERT INTO `accomplishment_states` (`type`,`description`)
VALUES ('Completed','Entire project is completed'),
('Completed','Today\'s acres completed'),
('Not-Completed/Postponed','Clearing index was too low'),
('Not-Completed/Postponed','Not in prescription'),
('Not-Completed/Postponed','Other reason (explain in comment)');

UNLOCK TABLES;

--
-- Table structure for table `interest_levels`
--

DROP TABLE IF EXISTS `interest_levels`;

CREATE TABLE `interest_levels` (
  `interest_level_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`interest_level_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

LOCK TABLES `interest_levels` WRITE;

INSERT INTO `interest_levels`
VALUES (1,'None'),
(2,'Low'),
(3,'Moderate'),
(4,'High'),
(5,'Extreme');

UNLOCK TABLES;

--
-- Table structure for table `daytime_ventilations`
--

DROP TABLE IF EXISTS `daytime_ventilations`;

CREATE TABLE `daytime_ventilations` (
  `daytime_ventilation_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`daytime_ventilation_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

LOCK TABLES `daytime_ventilations` WRITE;

INSERT INTO `daytime_ventilations`
VALUES (1,'Poor'),
(2,'Low'),
(3,'Moderate'),
(4,'Good');

UNLOCK TABLES;

--
-- Table structure for table `nighttime_smoke`
--

DROP TABLE IF EXISTS `nighttime_smoke`;

CREATE TABLE `nighttime_smoke` (
  `nighttime_smoke_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`nighttime_smoke_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

LOCK TABLES `nighttime_smoke` WRITE;

INSERT INTO `nighttime_smoke`
VALUES (1,'Poor'),
(2,'Low'),
(3,'Moderate'),
(4,'Good');

UNLOCK TABLES;

--
-- Table structure for table `documentation`
--

DROP TABLE IF EXISTS `documentation`;

CREATE TABLE `documentation` (
  `documentation_id` int(11) NOT NULL AUTO_INCREMENT,
  `agency_id` int(11) NOT NULL,
  `district_id` int(11) NOT NULL,
  `burn_project_id` int(11) NOT NULL,
  `pre_burn_id` int(11) NOT NULL,
  `burn_id` int(11) NOT NULL,
  `accomplishment_id` int(11) NOT NULL,
  `location` text DEFAULT NULL,
  `added_by` int(11) NOT NULL,
  `added_on` timestamp NULL DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `submitted_by` int(11) DEFAULT NULL,
  `submitted_on` timestamp NULL DEFAULT NULL,
  `observation_date` date DEFAULT NULL, 
  `observer` varchar(255) DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `clearing_index_pred` float DEFAULT NULL,
  `clearing_index_act` float DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  `completeness_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`documentation_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

--
-- Table structure for table `observations`
--

DROP TABLE IF EXISTS `observations`;

CREATE TABLE `observations` (
  `observation_id` int(11) NOT NULL AUTO_INCREMENT,
  `documentation_id` int(11) NOT NULL,
  `added_by` int(11) NOT NULL,
  `added_on` timestamp NULL DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `submitted_by` int(11) DEFAULT NULL,
  `submitted_on` timestamp NULL DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `time` time DEFAULT NULL, 
  `column_height` float DEFAULT NULL,
  `directional_flow_id` int(11) DEFAULT NULL,
  `comments` text DEFAULT NULL,
  PRIMARY KEY (`observation_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

--
-- Table structure for table `documentation_files`
--

DROP TABLE IF EXISTS `documentation_files`;

CREATE TABLE `documentation_files` (
  `documentation_file_id` int(11) NOT NULL AUTO_INCREMENT,
  `documentation_id` int(11) NOT NULL,
  `file_id` int(11) NOT NULL,
  PRIMARY KEY (`documentation_file_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

--
-- Table structure for table `aspect_types`
--

CREATE TABLE IF NOT EXISTS `directional_flows` (
  `directional_flow_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`directional_flow_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=33 ;

--
-- Dumping data for table `aspect_types`
--

INSERT INTO `directional_flows` (`directional_flow_id`, `name`) VALUES
(1, 'N'),
(2, 'NNE'),
(3, 'NE'),
(4, 'ENE'),
(5, 'E'),
(6, 'ESE'),
(7, 'SE'),
(8, 'SSE'),
(9, 'S'),
(10, 'SSW'),
(11, 'SW'),
(12, 'WSW'),
(13, 'W'),
(14, 'WNW'),
(15, 'NW'),
(16, 'NNW');

--
-- Table structure for table `documentation_statuses`
--

DROP TABLE IF EXISTS `documentation_statuses`;

CREATE TABLE `documentation_statuses` (
  `status_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text,
  `class` text,
  PRIMARY KEY (`status_id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;

LOCK TABLES `documentation_statuses` WRITE;

INSERT INTO `documentation_statuses` 
VALUES (5,'Disapproved','Documentation form was not approved.','label label-danger btn btn-danger'),
(4,'Approved','Documentation form has received final approval','label label-success btn btn-success'),
(3,'Revision Requested','Documentation form needs to be revised and submitted again.','label label-danger btn btn-danger'),
(2,'Under Review','Documentation form is submitted but pending approval.','label label-warning btn btn-warning'),
(1,'Draft','Documentation form is saved but not submitted','label label-warning btn btn-warning');

UNLOCK TABLES;

--
-- Table structure for table `documentation_completeness`
--

DROP TABLE IF EXISTS `documentation_completeness`;

CREATE TABLE `documentation_completeness` (
  `completeness_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text,
  `class` text,
  PRIMARY KEY (`completeness_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `documentation_completeness`
--

LOCK TABLES `documentation_completeness` WRITE;

INSERT INTO `documentation_completeness` 
VALUES (1,'Incomplete','Documentation is missing required fields.','label label-danger'),
(2,'Complete','Documentation has all required fields.','label label-success');

UNLOCK TABLES;

--
-- Table structure for table `agencies`
--

DROP TABLE IF EXISTS `agencies`;

CREATE TABLE `agencies` (
  `agency_id` int(11) NOT NULL AUTO_INCREMENT,
  `owner_id` int(11) DEFAULT NULL,
  `abbreviation` varchar(32) CHARACTER SET utf8 DEFAULT NULL,
  `agency` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `address` text CHARACTER SET utf8,
  `phone` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  PRIMARY KEY (`agency_id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;

LOCK TABLES `agencies` WRITE;

INSERT INTO `agencies` (`agency_id`, `abbreviation`, `agency`)
(1, 'DAQ', 'Division of Air Quality'),
(2, 'BLM', 'Bureau of Land Management'),
(3, 'FS', 'Forest Service'),
(4, 'FWS', 'Fish & Wildlife Service'),
(5, 'NPS', 'National Park Service'),
(6, 'STATE', ''),
(7, 'BIA', 'Bureau of Indian Affairs');

UNLOCK TABLES;

--
-- Table structure for table `districts`
--

DROP TABLE IF EXISTS `districts`;

CREATE TABLE `districts` (
  `district_id` int(11) NOT NULL AUTO_INCREMENT,
  `agency_id` int(11) NOT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `district` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `identifier` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `old_identifier` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `address` text CHARACTER SET utf8,
  `phone` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  PRIMARY KEY (`district_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

LOCK TABLES `districts` WRITE;

INSERT INTO `districts` (`agency_id`, `identifier`, `district`, `old_identifier`)
VALUES (1, 'DAQ', 'Division of Air Quality', NULL),
(2, 'CCD', 'Color County District', NULL),
(2, 'MOD', 'Canyon County District', 'CYD'),
(2, 'RID', 'Central Utah Fire Zone-East', 'CUEZ'),
(2, 'RID', 'Central Utah Fire Zone-West', 'CUWZ'),
(2, 'SLD', 'West Desert District', 'WDD'),
(2, 'VLD', 'Green River District', 'GRD'),
(2, 'USO', 'Utah State Office', NULL),
(3, 'ASF', 'Ashley National Forest', 'EZ'),
(3, 'ASF', 'Ashley National Forest', 'WZ'),
(3, 'ASF', 'Ashley National Forest', 'SO'),
(3, 'DIF', 'Dixie National Forest', 'ERD'),
(3, 'DIF', 'Dixie National Forest', 'PRD'),
(3, 'DIF', 'Dixie National Forest', 'CCRD'),
(3, 'DIF', 'Dixie National Forest', 'PVRD'),
(3, 'DIF', 'Dixie National Forest', 'SO'),
(3, 'FIF', 'Fishlake National Forest', 'RRD'),
(3, 'FIF', 'Fishlake National Forest', 'BRD'),
(3, 'FIF', 'Fishlake National Forest', 'FRRD'),
(3, 'FIF', 'Fishlake National Forest', 'FRD'),
(3, 'FIF', 'Fishlake National Forest', 'SO'),
(3, 'MLF', 'Manti-LaSal National Forest', 'NZ'),
(3, 'MLF', 'Manti-LaSal National Forest', 'SZ'),
(3, 'STF', 'Sawtooth National Forest', NULL),
(3, 'UWF', 'Uinta Wasatch Cache National Forest', 'NZ'),
(3, 'UWF', 'Uinta Wasatch Cache National Forest', 'MVRD'),
(3, 'UWF', 'Uinta Wasatch Cache National Forest', 'LRD'),
(3, 'UWF', 'Uinta Wasatch Cache National Forest', 'SZ'),
(3, 'UWF', 'Uinta Wasatch Cache National Forest', 'SO'),
(3, 'RO4', 'Region 4 Office', NULL),
(4, 'BBR', 'Bear River Migratory Bird Refuge', NULL),
(5, 'ZIP', 'Zion National Park', NULL),
(5, 'BRP', 'Bryce Canyon National Park', 'CRP'),
(5, 'ARP', 'Southeast Utah Parks Group', 'CAP'),
(5, 'HOP', 'Southeast Utah Parks Group', 'NBP'),
(5, 'DNP', 'Dinosaur National Monument', NULL),
(5, 'RO8', 'Region 8 Office', NULL),
(6, 'BRS', 'Bear River Area Office', NULL),
(6, 'NES', 'Northeast Area Office', NULL),
(6, 'NWS', 'Wasatch Front Area Office', NULL),
(6, 'SCS', 'Central Area Office', NULL),
(6, 'SES', 'Southeast Area Office', NULL),
(6, 'SWS', 'Southwest Area Office', NULL),
(6, 'FF&SL', 'Main Office', NULL),
(7, 'UOA', 'Uintah Ouray Agency', NULL);

UNLOCK TABLES;

--
-- Table structure for table `offices`
--

DROP TABLE IF EXISTS `offices`;

CREATE TABLE `offices` (
  `office_id` int(11) NOT NULL AUTO_INCREMENT,
  `agency_id` int(11) NOT NULL,
  `district_id` int(11) NOT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `office` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `identifier` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `address` text CHARACTER SET utf8,
  `phone` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  PRIMARY KEY (`office_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;


LOCK TABLES `offices` WRITE;

INSERT INTO `offices` (`agency_id`, `district_id`, `office`)
(1, 2, 'Canyon County District'),
(1, 2, 'Moab Interagency Fire Center');

UNLOCK TABLES;

--
-- Table structure for table `annual_registration`
--

DROP TABLE IF EXISTS `annual_registration`;

CREATE TABLE `annual_registration` (
  `annual_registration_id` int(11) NOT NULL AUTO_INCREMENT,
  `burn_project_id` int(11) NOT NULL,
  `year` year(4) NOT NULL,
  PRIMARY KEY (`annual_registration_id`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=latin1;

--
-- Table structure for table `burn_project_statuses`
--

DROP TABLE IF EXISTS `burn_project_statuses`;

CREATE TABLE `burn_project_statuses` (
  `status_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text,
  `class` text,
  PRIMARY KEY (`status_id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

LOCK TABLES `burn_project_statuses` WRITE;

INSERT INTO `burn_project_statuses` 
VALUES (5,'Disapproved','Burn project was not approved.','label label-danger btn btn-danger'),
(4,'Approved','Burn project has been approved.','label label-success btn btn-success'),
(3,'Revision Requested','Burn project needs to be revised and submitted again.','label label-danger btn btn-danger'),
(2,'Under Review','Burn project is submitted but pending approval.','label label-warning btn btn-warning'),
(1,'Draft','Burn project is saved but not submitted','label label-warning btn btn-warning');

UNLOCK TABLES;

--
-- Table structure for table `burn_project_completeness`
--

DROP TABLE IF EXISTS `burn_project_completeness`;

CREATE TABLE `burn_project_completeness` (
  `completeness_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text,
  `class` text,
  PRIMARY KEY (`completeness_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `burn_project_completeness`
--

LOCK TABLES `burn_project_completeness` WRITE;

INSERT INTO `burn_project_completeness` 
VALUES (1,'Incomplete','Burn Plan is missing required fields.','label label-danger btn btn-danger'),
(2,'Validated','Burn Plan has all required fields.','label label-success btn btn-success');

UNLOCK TABLES;

--
-- Table structure for table `burn_project_conditions`
--

DROP TABLE IF EXISTS `burn_project_conditions`;

CREATE TABLE `burn_project_conditions` (
  `burn_project_condition_id` int(11) NOT NULL AUTO_INCREMENT,
  `burn_project_id` int(11) NOT NULL,
  `added_by` int(11) NOT NULL,
  `added_on` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_burn_update` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `comment` text,
  PRIMARY KEY (`burn_project_condition_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `burn_project_erts`
--

DROP TABLE IF EXISTS `burn_project_erts`;

CREATE TABLE `burn_project_erts` (
  `burn_project_ert_id` int(11) NOT NULL AUTO_INCREMENT,
  `burn_project_id` int(11) NOT NULL,
  `emission_reduction_technique_id` int(11) NOT NULL,
  PRIMARY KEY (`burn_project_ert_id`)
) ENGINE=MyISAM AUTO_INCREMENT=71 DEFAULT CHARSET=latin1;

--
-- Table structure for table `burn_project_files`
--

DROP TABLE IF EXISTS `burn_project_files`;

CREATE TABLE `burn_project_files` (
  `burn_project_file_id` int(11) NOT NULL AUTO_INCREMENT,
  `burn_project_id` int(11) NOT NULL,
  `file_id` int(11) NOT NULL,
  PRIMARY KEY (`burn_project_file_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

--
-- Table structure for table `burn_project_reviews`
--

DROP TABLE IF EXISTS `burn_project_reviews`;

CREATE TABLE `burn_project_reviews` (
  `burn_project_review_id` int(11) NOT NULL AUTO_INCREMENT,
  `burn_project_id` int(11) NOT NULL,
  `added_by` int(11) NOT NULL,
  `added_on` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_by` int(11) DEFAULT NULL,
  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_burn_update` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `comment` text,
  PRIMARY KEY (`burn_project_review_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

--
-- Table structure for table `burn_projects`
--

DROP TABLE IF EXISTS `burn_projects`;

CREATE TABLE `burn_projects` (
  `burn_project_id` int(11) NOT NULL AUTO_INCREMENT,
  `agency_id` int(11) NOT NULL,
  `district_id` int(11) NOT NULL,
  `added_by` int(11) NOT NULL,
  `added_on` timestamp NULL DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `submitted_by` int(11) DEFAULT NULL,
  `submitted_on` timestamp NULL DEFAULT NULL,
  `project_number` varchar(32) DEFAULT NULL,
  `project_name` text DEFAULT NULL,
  `airshed_id` int(11) DEFAULT NULL,
  `class_1` tinyint(1) DEFAULT '0',
  `non_attainment` tinyint(1) DEFAULT '0',
  `de_minimis` tinyint(1) DEFAULT '0',
  `location` text DEFAULT NULL,
  `project_acres` float DEFAULT NULL,
  `completion_year` year(4) DEFAULT NULL,
  `black_acres_current` float DEFAULT NULL,
  `elevation_low` float DEFAULT NULL,
  `elevation_high` float DEFAULT NULL,
  `major_fbps_fuel` int(11) DEFAULT NULL,
  `burn_type` int(11) DEFAULT NULL,
  `number_of_piles` int(11) DEFAULT NULL,
  `first_burn` date DEFAULT NULL,
  `duration` int(11) DEFAULT NULL,
  `ignition_method` int(11) DEFAULT NULL,
  `county` int(11) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  `completeness_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`burn_project_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

--
-- Table structure for table `airsheds`
--

DROP TABLE IF EXISTS `airsheds`;

CREATE TABLE `airsheds` (
  `airshed_id` int(11) NOT NULL AUTO_INCREMENT,
  `number` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`airshed_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `burn_purposes`
--

LOCK TABLES `airsheds` WRITE;

INSERT INTO `airsheds` (`airshed_id`, `number`, `name`, `description`) VALUES 
(1, 1, 'Airshed 1', NULL),
(2, 2, 'Airshed 2', NULL),
(3, 3, 'Airshed 3', NULL),
(4, 4, 'Airshed 4', NULL),
(5, 5, 'Airshed 5', NULL),
(6, 6, 'Airshed 6', NULL),
(7, 7, 'Airshed 7', NULL),
(8, 8, 'Airshed 8', NULL),
(9, 9, 'Airshed 9', NULL),
(10, 10, 'Airshed 10', NULL),
(11, 11, 'Airshed 11', NULL),
(12, 12, 'Airshed 12', NULL),
(13, 13, 'Airshed 13', NULL),
(14, 14, 'Airshed 14', NULL),
(15, 15, 'Airshed 15', NULL),
(16, 16, 'Airshed 16', 'Above 6500 feet');

UNLOCK TABLES;

--
-- Table structure for table `burn_types`
--

DROP TABLE IF EXISTS `burn_pile_types`;

CREATE TABLE `burn_pile_types` (
  `burn_pile_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`burn_pile_type_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `burn_types`
--

LOCK TABLES `burn_pile_types` WRITE;

INSERT INTO `burn_pile_types` 
VALUES (1,'Broadcast'),
(2,'Understory'),
(3,'Canopy'),
(4,'Stand Replace'),
(5,'Piles'),
(6,'Partial Burn'),
(7,'Other');

UNLOCK TABLES;

--
-- Table structure for table `burn_conditions`
--

DROP TABLE IF EXISTS `burn_conditions`;
CREATE TABLE `burn_conditions` (
  `burn_condition_id` int(11) NOT NULL AUTO_INCREMENT,
  `burn_id` int(11) NOT NULL,
  `added_by` int(11) NOT NULL,
  `added_on` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_by` int(11) DEFAULT NULL,
  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_burn_update` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `comment` text,
  `acres` int(11) DEFAULT NULL,
  PRIMARY KEY (`burn_condition_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

--
-- Table structure for table `accomplishment_files`
--

DROP TABLE IF EXISTS `accomplishment_files`;
CREATE TABLE `accomplishment_files` (
  `accomplishment_file_id` int(11) NOT NULL AUTO_INCREMENT,
  `accomplishment_id` int(11) NOT NULL,
  `file_id` int(11) NOT NULL,
  PRIMARY KEY (`accomplishment_file_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

--
-- Table structure for table `accomplishment_reviews`
--

DROP TABLE IF EXISTS `accomplishment_reviews`;

CREATE TABLE `accomplishment_reviews` (
  `accomplishment_review_id` int(11) NOT NULL AUTO_INCREMENT,
  `accomplishment_id` int(11) NOT NULL,
  `added_by` int(11) NOT NULL,
  `added_on` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_by` int(11) DEFAULT NULL,
  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_burn_update` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `comment` text,
  PRIMARY KEY (`accomplishment_review_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;


--
-- Table structure for table `pre_burns`
--

DROP TABLE IF EXISTS `pre_burns`;

CREATE TABLE `pre_burns` (
  `pre_burn_id` int(11) NOT NULL AUTO_INCREMENT,
  `burn_project_id` int(11) NOT NULL,
  `agency_id` int(11) NOT NULL,
  `district_id` int(11) NOT NULL,
  `year` year DEFAULT NULL,
  `acres` float DEFAULT NULL,
  `added_by` int(11) NOT NULL,
  `added_on` timestamp NULL DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `submitted_by` int(11) DEFAULT NULL,
  `submitted_on` timestamp NULL DEFAULT NULL,
  `location` text DEFAULT NULL,
  `avoidance` tinyint(1) DEFAULT '0',
  `dilution` tinyint(1) DEFAULT '0',
  `primary_ert_id` int(11) DEFAULT NULL,
  `primary_ert_pct` int(11) DEFAULT NULL,
  `alternate_primary_ert` text DEFAULT NULL,
  `secondary_ert_id` int(11) DEFAULT NULL,
  `alternate_secondary_ert` text DEFAULT NULL,
  `dispersion_model_id` int(11) DEFAULT NULL,
  `alternate_dispersion_model` text DEFAULT NULL,
  `pm_min` float DEFAULT NULL,
  `pm_max` float DEFAULT NULL,
  `day_iso` text DEFAULT NULL,
  `night_iso` text DEFAULT NULL,
  `manager_name` varchar(255) DEFAULT NULL,
  `manager_number` varchar(255) DEFAULT NULL,
  `manager_cell` varchar(255) DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  `revision_id` int(11) DEFAULT NULL,
  `active` tinyint(1) DEFAULT '0',
  `completeness_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`pre_burn_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

--
-- Table structure for table `pre_burn_objective_presets`
--

DROP TABLE IF EXISTS `pre_burn_objective_presets`;

CREATE TABLE `pre_burn_objective_presets` (
  `pre_burn_objective_preset_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`pre_burn_objective_preset_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

LOCK TABLES `pre_burn_objective_presets`; 

INSERT INTO `pre_burn_objective_presets` (`name`, `description`)
VALUES ('Hazard Reduction', NULL),
('Wildlife Habitat Improvement', NULL),
('Site Preparation', NULL),
('Historical Scene Maintenance', NULL),
('Other Cultural Site Maintenance', NULL),
('Exotic or Undesireable Species Control', NULL),
('Habitat Maintenance', NULL),
('Research', NULL),
('Fire Dependent Ecosystem Maintenance', NULL),
('Fire Reduction (Activitiy Fuels)', NULL),
('Fire Reduction (Natural Fuels)', NULL),
('Debris Removal', NULL),
('Health (Insect Control)', NULL),
('Seed Bed Preparation', NULL),
('Vegetation Type Manipulation/Stand Improvement', NULL),
('Property Protection', NULL),
('Project Maintenance', NULL),
('Other', NULL);

UNLOCK TABLES;

--
-- Table structure for table `pre_burn_objectives`
--

DROP TABLE IF EXISTS `pre_burn_objectives`;

CREATE TABLE `pre_burn_objectives` (
  `pre_burn_objective_id` int(11) NOT NULL AUTO_INCREMENT,
  `pre_burn_id` int(11) NOT NULL,
  `pre_burn_objective_preset_id` int(11) DEFAULT NULL,
  `other` text DEFAULT NULL,
  PRIMARY KEY (`pre_burn_objective_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

--
-- Table structure for table `pre_burn_receptors`
--

DROP TABLE IF EXISTS `pre_burn_receptors`;

CREATE TABLE `pre_burn_receptors` (
  `pre_burn_receptor_id` int(11) NOT NULL AUTO_INCREMENT,
  `pre_burn_id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `location` text DEFAULT NULL,
  `miles` float DEFAULT NULL,
  `degrees` float DEFAULT NULL,
  PRIMARY KEY (`pre_burn_receptor_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

--
-- Table structure for table `dispersion_models`
--

DROP TABLE IF EXISTS `dispersion_models`;

CREATE TABLE `dispersion_models` (
  `dispersion_model_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`dispersion_model_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

LOCK TABLES `dispersion_models`;

INSERT INTO `dispersion_models` (`name`)
VALUES ('SASEM'),
('SASEM2'),
('NFSPUFF'),
('NFSPUFF4'),
('FOFEM5'),
('Other');

UNLOCK TABLES;

--
-- Table structure for table `pre_burn_statuses`
--

DROP TABLE IF EXISTS `pre_burn_statuses`;

CREATE TABLE `pre_burn_statuses` (
  `status_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text,
  `class` text,
  PRIMARY KEY (`status_id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

LOCK TABLES `pre_burn_statuses` WRITE;

INSERT INTO `pre_burn_statuses` 
VALUES (5,'Disapproved','Pre-Burn plan was not approved.','label label-danger btn btn-danger'),
(4,'Approved','Pre-Burn plan has received final approval','label label-success btn btn-success'),
(3,'Revision Requested','Pre-Burn plan needs to be revised and submitted again.','label label-danger btn btn-danger'),
(2,'Under Review','Pre-Burn plan is submitted but pending approval.','label label-warning btn btn-warning'),
(1,'Draft','Pre-Burn plan is saved but not submitted','label label-warning btn btn-warning');

UNLOCK TABLES;

--
-- Table structure for table `pre_burn_revisions`
--

DROP TABLE IF EXISTS `pre_burn_revisions`;

CREATE TABLE `pre_burn_revisions` (
  `revision_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text,
  `class` text,
  PRIMARY KEY (`revision_id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

LOCK TABLES `pre_burn_revisions` WRITE;

INSERT INTO `pre_burn_revisions` 
VALUES (5,'General & Smoke Modification','The Pre-Burn plan\'s smoke and general information has been updated.','label label-success btn btn-success'),
(4,'Smoke Modification','The Pre-Burn plan\'s smoke information has been updated.','label label-success btn btn-success'),
(3,'General Modification','The Pre-Burn plan\'s general information has been updated.','label label-success btn btn-success'),
(2,'Renewed','Pre-Burn plan has been renewed from a previous year.','label label-success btn btn-success'),
(1,'Original','Pre-Burn plan has not been revised.','label label-success btn btn-success');

UNLOCK TABLES;

--
-- Table structure for table `pre_burn_completeness`
--

DROP TABLE IF EXISTS `pre_burn_completeness`;

CREATE TABLE `pre_burn_completeness` (
  `completeness_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text,
  `class` text,
  PRIMARY KEY (`completeness_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `pre_burn_completeness`
--

LOCK TABLES `pre_burn_completeness` WRITE;

INSERT INTO `pre_burn_completeness` 
VALUES (1,'Incomplete','Pre-Burn form is missing required fields.','label label-danger'),
(2,'Complete','Pre-Burn form  has all required fields.','label label-success');

UNLOCK TABLES;

--
-- Table structure for table `burn_project_conditions`
--

DROP TABLE IF EXISTS `pre_burn_conditions`;

CREATE TABLE `pre_burn_conditions` (
  `pre_burn_condition_id` int(11) NOT NULL AUTO_INCREMENT,
  `pre_burn_id` int(11) NOT NULL,
  `added_by` int(11) NOT NULL,
  `added_on` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_burn_update` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `comment` text,
  PRIMARY KEY (`pre_burn_condition_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

--
-- Table structure for table `burn_project_reviews`
--

DROP TABLE IF EXISTS `pre_burn_reviews`;

CREATE TABLE `pre_burn_reviews` (
  `pre_burn_review_id` int(11) NOT NULL AUTO_INCREMENT,
  `pre_burn_id` int(11) NOT NULL,
  `added_by` int(11) NOT NULL,
  `added_on` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_by` int(11) DEFAULT NULL,
  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_burn_update` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `comment` text,
  PRIMARY KEY (`pre_burn_review_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

--
-- Table structure for table `burn_project_files`
--

DROP TABLE IF EXISTS `pre_burn_files`;

CREATE TABLE `pre_burn_files` (
  `pre_burn_file_id` int(11) NOT NULL AUTO_INCREMENT,
  `pre_burn_id` int(11) NOT NULL,
  `file_id` int(11) NOT NULL,
  PRIMARY KEY (`pre_burn_file_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;


--
-- Table structure for table `pre_burns`
--

DROP TABLE IF EXISTS `burns`;

CREATE TABLE `burns` (
  `burn_id` int(11) NOT NULL AUTO_INCREMENT,
  `burn_project_id` int(11) NOT NULL,
  `pre_burn_id` int(11) NOT NULL,
  `agency_id` int(11) NOT NULL,
  `district_id` int(11) NOT NULL,
  `added_by` int(11) NOT NULL,
  `added_on` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `submitted_by` int(11) DEFAULT NULL,
  `submitted_on` timestamp NULL DEFAULT NULL,
  `location` text DEFAULT NULL,
  `airshed_id` int(11) DEFAULT NULL,
  `modify_id` int(11) DEFAULT NULL,
  `request_acres` float DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `daily_acres` float DEFAULT NULL,
  `comments` text DEFAULT NULL,
  `pm_sampler_model` text DEFAULT NULL,
  `pm_sampler_id` text DEFAULT NULL,
  `manager_name` varchar(255) DEFAULT NULL,
  `manager_number` varchar(255) DEFAULT NULL,
  `manager_cell` varchar(255) DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  `expired`  tinyint(1) DEFAULT '0',
  `completeness_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`burn_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

--
-- Table structure for table `burn_statuses`
--

DROP TABLE IF EXISTS `burn_statuses`;

CREATE TABLE `burn_statuses` (
  `status_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text,
  `class` text,
  PRIMARY KEY (`status_id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `burn_statuses`
--

LOCK TABLES `burn_statuses` WRITE;

INSERT INTO `burn_statuses` 
VALUES (6,'Disapproved','The burn request was not approved.','label label-danger btn btn-danger'),
(5,'Approved','The burn request has received final approval','label label-success btn btn-success'),
(4,'Pending Approval','The burn request is now awaiting final approval.','label label-warning btn btn-warning'),
(3,'Revision Requested','The burn request needs to be revised and submitted again.','label label-danger btn btn-danger'),
(2,'Under Review','The burn request is submitted and under review.','label label-warning btn btn-warning'),
(1,'Draft','The burn request is saved but not submitted','label label-warning btn btn-warning');

UNLOCK TABLES;

--
-- Table structure for table `burn_modifications`
--

DROP TABLE IF EXISTS `burn_modifications`;

CREATE TABLE `burn_modifications` (
  `modification_id` int(11) NOT NULL AUTO_INCREMENT,
  `description` text,
  PRIMARY KEY (`modification_id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `burn_modifications`
--

LOCK TABLES `burn_modifications` WRITE;

INSERT INTO `burn_modifications` 
VALUES (1,'Burn information has not changed for the active pre-burn. No revision necessary.'),
(2,'Non-smoke elements have been revised for the active Pre-Burn.'),
(3,'Smoke elements have been revised for the active Pre-Burn.');

UNLOCK TABLES;

--
-- Table structure for table `burn_completeness`
--

DROP TABLE IF EXISTS `burn_completeness`;

CREATE TABLE `burn_completeness` (
  `completeness_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text,
  `class` text,
  PRIMARY KEY (`completeness_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `burn_completeness`
--

LOCK TABLES `burn_completeness` WRITE;

INSERT INTO `burn_completeness` 
VALUES (1,'Incomplete','Burn Request is missing required fields.','label label-danger'),
(2,'Complete','Burn Request has all required fields.','label label-success');

UNLOCK TABLES;

--
-- Table structure for table `burn_conditions`
--

DROP TABLE IF EXISTS `burn_conditions`;

CREATE TABLE `burn_conditions` (
  `burn_condition_id` int(11) NOT NULL AUTO_INCREMENT,
  `burn_id` int(11) NOT NULL,
  `added_by` int(11) NOT NULL,
  `added_on` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_burn_update` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `comment` text,
  PRIMARY KEY (`burn_condition_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `burn_reviews`
--

DROP TABLE IF EXISTS `burn_reviews`;

CREATE TABLE `burn_reviews` (
  `burn_review_id` int(11) NOT NULL AUTO_INCREMENT,
  `burn_id` int(11) NOT NULL,
  `added_by` int(11) NOT NULL,
  `added_on` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_by` int(11) DEFAULT NULL,
  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_burn_update` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `comment` text,
  PRIMARY KEY (`burn_review_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

--
-- Table structure for table `burn_project_files`
--

DROP TABLE IF EXISTS `burn_files`;

CREATE TABLE `burn_files` (
  `burn_file_id` int(11) NOT NULL AUTO_INCREMENT,
  `burn_id` int(11) NOT NULL,
  `file_id` int(11) NOT NULL,
  PRIMARY KEY (`burn_file_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

--
-- Table structure for table `emission_reduction_techniques`
--

DROP TABLE IF EXISTS `emission_reduction_techniques`;

CREATE TABLE `emission_reduction_techniques` (
  `emission_reduction_technique_id` int(11) NOT NULL AUTO_INCREMENT,
  `number` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`emission_reduction_technique_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

LOCK TABLES `emission_reduction_techniques` WRITE;

INSERT INTO `emission_reduction_techniques` (`number`, `name`)
VALUES (1, 'Reduce Area Burned'),
(2, 'Reduce Fuel Production'),
(3, 'Reduce Fuel Load'),
(4, 'Reduce Fuel Consumed'),
(5, 'Schedule Burning Before New Fuels Appear'),
(6, 'Increase Combustion Efficiency'),
(7, 'Other, provide below');

UNLOCK TABLES;

--
-- Table structure for table `datatypes`
--

DROP TABLE IF EXISTS `datatypes`;

CREATE TABLE `datatypes` (
  `datatype_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `input` text,
  PRIMARY KEY (`datatype_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

LOCK TABLES `datatypes` WRITE;

INSERT INTO `datatypes` 
VALUES (1,'textbox','textbox'),
(2,'password','password'),
(3,'text','memo'),
(4,'date','date'),
(5,'datetime','datetime'),
(6,'month','month'),
(7,'integer','textbox'),
(8,'foreign','combobox'),
(9,'map','boundary'),
(10,'marker','location'),
(11,'rich','rich_memo'),
(12, 'boolean', 'boolean');

UNLOCK TABLES;

--
-- Table structure for table `db_tables`
--

DROP TABLE IF EXISTS `db_tables`;

CREATE TABLE `db_tables` (
  `db_table_id` int(11) NOT NULL AUTO_INCREMENT,
  `db_table` varchar(255) DEFAULT NULL,
  `pcol` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`db_table_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

INSERT INTO `db_tables` (`db_table`, `pcol`)
VALUES ('db_tables', 'db_table_id'),
('fields', 'field_id'),
('users', 'user_id'),
('agencies', 'agency_id');

--
-- Table structure for table `fields`
--

DROP TABLE IF EXISTS `fields`;

CREATE TABLE `fields` (
  `field_id` int(11) NOT NULL AUTO_INCREMENT,
  `table_id` int(11) DEFAULT NULL,
  `column` varchar(255) DEFAULT NULL,
  `datatype` int(11) DEFAULT NULL,
  `sql` text,
  `primary_key` tinyint(1) DEFAULT '0',
  `allow_null` tinyint(1) DEFAULT '1',
  `multiselect` tinyint(1) DEFAULT '0',
  `ptable` varchar(255) DEFAULT NULL,
  `stable` varchar(255) DEFAULT NULL,
  `mmtable` varchar(255) DEFAULT NULL,
  `pcol` varchar(255) DEFAULT NULL,
  `scol` varchar(255) DEFAULT NULL,
  `sdisplay` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`field_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

--
-- Table structure for table `fields_help`
--

DROP TABLE IF EXISTS `fields_help`;

CREATE TABLE `fields_help` (
  `fields_help_id` int(11) NOT NULL AUTO_INCREMENT,
  `help_id` int(11) NOT NULL,
  `field_id` int(11) NOT NULL,
  `title` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `body` text CHARACTER SET utf8,
  `reference_field_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`fields_help_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `fields_info`;

CREATE TABLE `fields_info` (
  `fields_info_id` int(11) NOT NULL AUTO_INCREMENT,
  `value` text DEFAULT NULL,
  `title` text DEFAULT NULL,
  `display` tinyint(1) DEFAULT '0',
  
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

--
-- Table structure for table `files`
--

DROP TABLE IF EXISTS `files`;

CREATE TABLE `files` (
  `file_id` int(11) NOT NULL AUTO_INCREMENT,
  `path` text,
  `size_kb` float DEFAULT NULL,
  `comment` text,
  `added_on` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `added_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`file_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

--
-- Table structure for table `help`
--

DROP TABLE IF EXISTS `help`;

CREATE TABLE `help` (
  `help_id` int(11) NOT NULL AUTO_INCREMENT,
  `page_id` int(11) DEFAULT NULL,
  `title` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `body` text CHARACTER SET utf8,
  PRIMARY KEY (`help_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

--
-- Table structure for table `ignition_methods`
--

DROP TABLE IF EXISTS `ignition_methods`;

CREATE TABLE `ignition_methods` (
  `ignition_method_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ignition_method_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `ignition_methods`
--

LOCK TABLES `ignition_methods` WRITE;

INSERT INTO `ignition_methods` 
VALUES (1,'Helitorch'),
(2,'Hand Drip'),
(3,'Ping Pong'),
(4,'Hand Fusee'),
(5,'Aerial Fusee'),
(6,'Tera Torch'),
(7,'Propane Torch'),
(8,'Other');

UNLOCK TABLES;

--
-- Table structure for table `counties`
--

DROP TABLE IF EXISTS `counties`;

CREATE TABLE `counties` (
  `county_id` int(11) NOT NULL AUTO_INCREMENT,
  `number` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`county_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `counties`
--

LOCK TABLES `counties` WRITE;

INSERT INTO `counties` 
VALUES 
(1, 1, 'Beaver'),
(2, 3, 'Box Elder'),
(3, 5, 'Cache'),
(4, 7, 'Carbon'),
(5, 9, 'Daggett'),
(6, 11, 'Davis'),
(7, 13, 'Duchesn'),
(8, 15, 'Emery'),
(9, 17, 'Garfield'),
(10, 19, 'Grand'),
(11, 21, 'Iron'),
(12, 23, 'Juab'),
(13, 25, 'Kane'),
(14, 27, 'Millard'),
(15, 29, 'Morgan'),
(16, 31, 'Piute'),
(17, 33, 'Rich'),
(18, 35, 'Salt Lake'),
(19, 37, 'San Juan'),
(20, 39, 'Sanpete'),
(21, 41, 'Sevier'),
(22, 43, 'Summit'),
(23, 45, 'Tooele'),
(24, 47, 'Uinta'),
(25, 49, 'Utah'),
(26, 51, 'Wasatch'),
(27, 53, 'Washingtion'),
(28, 55, 'Wayne'),
(29, 57, 'Weber');

UNLOCK TABLES;

--
-- Table structure for table `index`
--

DROP TABLE IF EXISTS `index`;

CREATE TABLE `index` (
  `index_id` int(11) NOT NULL AUTO_INCREMENT,
  `added_by` int(11) NOT NULL,
  `added_on` timestamp NULL DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `date` date NULL DEFAULT NULL,
  `html` text,
  PRIMARY KEY (`index_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

--
-- Table structure for table `index_levels`
--

DROP TABLE IF EXISTS `index_levels`;

CREATE TABLE `index_levels` (
  `index_level_id` int(11) NOT NULL AUTO_INCREMENT,
  `added_by` int(11) NOT NULL,
  `added_on` timestamp NULL DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `date` date NULL DEFAULT NULL,
  `egbc_level` int(11) DEFAULT NULL,
  `national_level` int(11) DEFAULT NULL,
  PRIMARY KEY (`index_level_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

--
-- Table structure for table `index_egbc_team_fires`
--

DROP TABLE IF EXISTS `index_egbc_team_fires`;

CREATE TABLE `index_egbc_team_fires` (
  `index_egbc_team_fire_id` int(11) NOT NULL AUTO_INCREMENT,
  `added_by` int(11) NOT NULL,
  `added_on` timestamp NULL DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `date` date NULL DEFAULT NULL,
  `html` text,
  PRIMARY KEY (`index_egbc_team_fire_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

--
-- Table structure for table `index_egbc_large_fires`
--

DROP TABLE IF EXISTS `index_egbc_large_fires`;

CREATE TABLE `index_egbc_large_fires` (
  `index_egbc_large_fire_id` int(11) NOT NULL AUTO_INCREMENT,
  `added_by` int(11) NOT NULL,
  `added_on` timestamp NULL DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `date` date NULL DEFAULT NULL,
  `html` text,
  PRIMARY KEY (`index_egbc_large_fire_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

--
-- Table structure for table `notification_log`
--

DROP TABLE IF EXISTS `notification_log`;

CREATE TABLE `notification_log` (
  `notification_log_id` int(11) NOT NULL AUTO_INCREMENT,
  `notification_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `message` text,
  `sent` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `read` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`notification_log_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL AUTO_INCREMENT,
  `function_name` varchar(255) DEFAULT NULL,
  `email` tinyint(1) DEFAULT '0',
  `local` tinyint(1) DEFAULT '0',
  `min_user_level` int(11) DEFAULT NULL,
  PRIMARY KEY (`notification_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

--
-- Table structure for table `pages`
--

DROP TABLE IF EXISTS `pages`;

CREATE TABLE `pages` (
  `page_id` int(11) NOT NULL AUTO_INCREMENT,
  `page_href_name` varchar(255) NOT NULL,
  `page_title` varchar(255) NOT NULL,
  `page_url` text NOT NULL,
  `min_page_user_level` int(11) NOT NULL,
  `min_read_permission` varchar(255) NOT NULL,
  `menu_order` int(11) NOT NULL,
  PRIMARY KEY (`page_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `pages`
--

LOCK TABLES `pages` WRITE;

INSERT INTO `pages` 
VALUES (1,'Home','Smoke System / Utah.gov','index.php',0,'public',0),
(2,'Map','Burn Map / Utah.gov','map.php',0,'public',1),
(3,'Burn Projects','Burn Project Manager / Utah.gov','manager/project.php',1,'user',1),
(4,'Pre-Burns','Pre-Burn Manager / Utah.gov','manager/pre_burn.php',1,'user',2),
(5,'Burn Requests','Burn Manager / Utah.gov','manager/burn.php',1,'user',3),
(6,'Accomplishments','Burn Accomplishment Manager / Utah.gov','manager/accomplishment.php',1,'user',4),
(7,'Burn Documentation Manager','Documentation Manager / Utah.gov','manager/documentation.php',1,'user',5),
(8,'Burn Projects','Burn Project Reviewer / Utah.gov','review/project.php',6,'admin',1),
(9,'Pre-Burns','Pre-Burn Reviewer / Utah.gov','review/pre_burn.php',6,'admin',2),
(10,'Burn Requests','Burn Reviewer / Utah.gov','review/burn.php',6,'admin',3),
(11,'Accomplishments','Burn Accomplishment Reviewer / Utah.gov','review/accomplishment.php',6,'admin',4),
(12,'Burn Documentation','Documentation Reviewer / Utah.gov','review/documentation.php',6,'admin',5),
(13,'User Manager','User Manager / Utah.gov','admin/users.php',6,'admin',6),
(14,'Group Manager','Group Manager / Utah.gov','admin/groups.php',6,'admin',7),
(15,'Help Manager','Help Manager / Utah.gov','admin/help.php',6,'admin',8),
(16,'Homepage Manager','Homepage Manager / Utah.gov','admin/page.php',6,'admin',9),
(17,'Reports','Reporting / Utah.gov','admin/report.php',6,'admin',10),
(18,'Profile','User Profile / Utah.gov','profile.php',1,'user',NULL);

UNLOCK TABLES;

--
-- Table structure for table `reset`
--

DROP TABLE IF EXISTS `reset`;

CREATE TABLE `reset` (
  `reset_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `submitted_on` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `expires` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `token` text,
  `successful` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`reset_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

--
-- Table structure for table `user_agencies`
--

DROP TABLE IF EXISTS `user_agencies`;

CREATE TABLE `user_agencies` (
  `user_agency_id` int(11) NOT NULL AUTO_INCREMENT,
  `agency_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`user_agency_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

--
-- Table structure for table `user_levels`
--

DROP TABLE IF EXISTS `user_levels`;

CREATE TABLE `user_levels` (
  `user_level_id` int(11) NOT NULL,
  `user_level_name` varchar(255) CHARACTER SET utf8 NOT NULL,
  `user_level_description` varchar(255) CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`user_level_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `user_levels`
--

LOCK TABLES `user_levels` WRITE;

INSERT INTO `user_levels` 
VALUES (0,'Public','Public pages are accessible to anyone.'),
(1,'User','Can access and submit data on user pages for their user only. Can read district(s) info.'),
(2,'District Reviewer','Can view (read-only) for all of that users district(s).'),
(3,'District Administrator','Can access and submit data for all of that users district(s).'),
(4,'Agency Reviewer','Can view (read-only) burn pages for entire agency.'),
(5,'Agency Administrator','Can view and modify burn pages for entire agency.'),
(6,'DAQ Reviewer','Can view (read-only) burn and system admin pages.'),
(7,'DAQ Director','Can view (read-only) burn and system admin pages and perform final approval on burn requests.'),
(8,'DAQ Administrator','Can view and modify burn and system admin pages.'),
(9,'System Administrator','Can view and modify user, admin, and advanced system info.');

UNLOCK TABLES;

--
-- Table structure for table `user_notifications`
--

DROP TABLE IF EXISTS `user_notifications`;

CREATE TABLE `user_notifications` (
  `user_notification_id` int(11) NOT NULL AUTO_INCREMENT,
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`user_notification_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `agency_id` int(11) DEFAULT NULL,
  `level_id` int(11) NOT NULL,
  `active` tinyint(1) DEFAULT '1',
  `password` varchar(255) NOT NULL,
  `salt` varchar(255) NOT NULL,
  `password_update` timestamp NULL DEFAULT NULL,
  `full_name` varchar(255) CHARACTER SET utf8 NOT NULL,
  `email` varchar(255) CHARACTER SET utf8 NOT NULL,
  `address` text CHARACTER SET utf8 DEFAULT NULL,
  `address_b` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `state` varchar(255) DEFAULT NULL,
  `zip` varchar(32) DEFAULT NULL,
  `phone` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

--
-- Table structure for table `user_districts`
--

DROP TABLE IF EXISTS `user_districts`;

CREATE TABLE `user_districts` (
  `user_district_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `district_id` int(11) NOT NULL,
  PRIMARY KEY (`user_district_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

--
-- Table structure for table `user_offices`
--

DROP TABLE IF EXISTS `user_offices`;

CREATE TABLE `user_offices` (
  `user_office_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `office_id` int(11) NOT NULL,
  PRIMARY KEY (`user_office_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

--
-- Table structure for table `weather`
--

DROP TABLE IF EXISTS `weather`;

CREATE TABLE `weather` (
  `weather_id` int(11) NOT NULL AUTO_INCREMENT,
  `added_by` int(11) NOT NULL,
  `added_on` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  `updated_by` int(11) DEFAULT NULL,
  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `date` date DEFAULT NULL,
  `html` text,
  PRIMARY KEY (`weather_id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;
