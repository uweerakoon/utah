
INSERT INTO `fields` (`table_id`, `column`, `datatype`, `sql`, `primary_key`, `allow_null`)
VALUES (1, 'db_table_id', 7, NULL, 1, 1),
(1, 'db_table', 1, NULL, 0, 1),
(1, 'pcol', 1, NULL, 0, 1);

INSERT INTO `fields` (`table_id`, `column`, `datatype`, `sql`, `primary_key`, `allow_null`)
VALUES (3, 'user_id', 7, NULL, 1, 1),
(3, 'agency_id', 8, 'SELECT agency_id as id, agency as label FROM agencies ORDER BY agency;', 0, 1),
(3, 'level_id', 8, 'SELECT user_level_id as id, user_level_name as label FROM user_levels ORDER BY user_level_name;', 0, 1),
(3, 'full_name', 1, NULL, 0, 1),
(3, 'email', 1, NULL, 0, 1),
(3, 'address', 3, NULL, 0, 1),
(3, 'address_b', 1, NULL, 0, 1),
(3, 'city', 1, NULL, 0, 1),
(3, 'state', 1, NULL, 0, 1),
(3, 'zip', 1, NULL, 0, 1),
(3, 'phone', 1, NULL, 0, 1),
(3, 'active', 12, NULL, 0, 1);

INSERT INTO `fields` (`table_id`, `column`, `datatype`, `sql`, `primary_key`, `allow_null`, `multiselect`, `ptable`, `stable`, `mmtable`, `pcol`, `scol`, `sdisplay`)
VALUES (3, 'district_id', 8, 'SELECT district_id as id, COALESCE(CONCAT(identifier, \' - \', old_identifier, \' - \', district),  CONCAT(identifier, \' - \', district), district) as label FROM districts ORDER BY district;', 0, 1, 1, 'users', 'districts', 'user_districts', 'user_id', 'district_id', 'district'),
(3, 'office_id', 8, 'SELECT office_id as id, office as label FROM offices ORDER BY office;', 0, 1, 1, 'users', 'offices', 'user_offices', 'user_id', 'office_id', 'office');

INSERT INTO `fields` (`table_id`, `column`, `datatype`, `sql`, `primary_key`, `allow_null`)
VALUES (4, 'agency_id', 7, NULL, 1, 1),
(4, 'owner_id', 8, 'SELECT user_id as id, full_name as label FROM users ORDER BY email;', 0, 1),
(4, 'abbreviation', 1, NULL, 0, 1),
(4, 'agency', 1, NULL, 0, 1),
(4, 'address', 3, NULL, 0, 1),
(4, 'phone', 1, NULL, 0, 1);

INSERT INTO `fields` (`table_id`, `column`, `datatype`, `sql`, `primary_key`, `allow_null`)
VALUES (14, 'district_id', 7, NULL, 1, 1),
(14, 'agency_id', 8, 'SELECT agency_id as id, agency as label FROM agencies ORDER BY agency;', 0, 1),
(14, 'owner_id', 8, 'SELECT user_id as id, full_name as label FROM users ORDER BY email;', 0, 1),
(14, 'identifier', 1, NULL, 0, 1),
(14, 'old_identifier', 1, NULL, 0, 1),
(14, 'district', 1, NULL, 0, 1),
(14, 'address', 3, NULL, 0, 1),
(14, 'phone', 1, NULL, 0, 1);

INSERT INTO `fields` (`table_id`, `column`, `datatype`, `sql`, `primary_key`, `allow_null`)
VALUES (2, 'field_id', 7, NULL, 1, 1),
(2, 'table_id', 8, 'SELECT db_table_id as id, db_table as label FROM db_tables ORDER BY db_table;', 0, 1),
(2, 'column', 1, NULL, 0, 1),
(2, 'datatype', 8, 'SELECT datatype_id as id, name as label FROM datatypes ORDER BY name;', 0, 1),
(2, 'sql', 3, NULL, 0, 1),
(2, 'primary_key', 1, NULL, 0, 1),
(2, 'allow_null', 1, NULL, 0, 1),
(2, 'multiselect', 1, NULL, 0, 1),
(2, 'ptable', 1, NULL, 0, 1),
(2, 'stable', 1, NULL, 0, 1),
(2, 'mmtable', 1, NULL, 0, 1),
(2, 'pcol', 1, NULL, 0, 1),
(2, 'scol', 1, NULL, 0, 1),
(2, 'sdisplay', 1, NULL, 0, 1);

INSERT INTO `fields` (`table_id`, `column`, `datatype`, `sql`, `primary_key`, `allow_null`)
VALUES (6, 'help_id', 7, NULL, 1, 1),
(6, 'page_id', 8, 'SELECT page_id as id, page_url as label FROM pages ORDER BY page_url;', 0, 1),
(6, 'title', 1, NULL, 0, 1),
(6, 'body', 3, NULL, 0, 1);

INSERT INTO `fields` (`table_id`, `column`, `datatype`, `sql`, `primary_key`, `allow_null`)
VALUES (7, 'fields_help_id', 7, NULL, 1, 1),
(7, 'help_id', 8, 'SELECT help_id as id, title as label FROM help ORDER BY title;', 0, 1),
(7, 'field_id', 8, 'SELECT field_id as id, CONCAT(db_table, \' - \', `column`) as label FROM fields f JOIN db_tables t ON(f.table_id = t.db_table_id) ORDER BY db_table, `column`;', 0, 1),
(7, 'title', 1, NULL, 0, 1),
(7, 'body', 3, NULL, 0, 1),
(7, 'reference_field_id', 8, 'SELECT field_id as id, CONCAT(db_table, \' - \', `column`) as label FROM fields f JOIN db_tables t ON(f.table_id = t.db_table_id) ORDER BY db_table, `column`;', 0, 1);

INSERT INTO `fields` (`table_id`, `column`, `datatype`, `sql`, `primary_key`, `allow_null`)
VALUES (5, 'burn_project_id', 7, NULL, 1, 1),
(5, 'agency_id', 8, 'SELECT agency_id as id, agency as label FROM agencies ORDER by agency;', 0, 1),
(5, 'project_number', 1, NULL, 0, 1),
(5, 'project_name', 1, NULL, 0, 1),
(5, 'airshed_id', 8, 'SELECT airshed_id as id, name as label FROM airsheds ORDER by name;', 0, 1),
(5, 'class_1', 12, NULL, 0, 1),
(5, 'non_attainment', 12, NULL, 0, 1),
(5, 'de_minimus', 12, NULL, 0, 1),
(5, 'location', 1, NULL, 0, 1),
(5, 'project_acres', 1, NULL, 0, 1),
(5, 'completion_year', 1, NULL, 0, 1),
(5, 'black_acres_current', 1, NULL, 0, 1),
(5, 'elevation_low', 1, NULL, 0, 1),
(5, 'elevation_high', 1, NULL, 0, 1),
(5, 'major_fbps_fuel', 1, NULL, 0, 1),
(5, 'piles_type', 8, 'SELECT burn_pile_type_id as id, name as label FROM burn_pile_types ORDER by name;', 0, 1),
(5, 'first_burn', 4, NULL, 0, 1),
(5, 'duration', 4, NULL, 0, 1),
(5, 'ignition_method', 8, 'SELECT ignition_method_id as id, name as label FROM ignition_methods ORDER by name;', 0, 1),
(5, 'county', 8, 'SELECT county_id as id, name as label FROM counties ORDER by name;', 0, 1);

INSERT INTO `fields` (`table_id`, `column`, `datatype`, `sql`, `primary_key`, `allow_null`)
VALUES (8, 'pre_burn_id', 7, NULL, 1, 1),
(8, 'agency_id', 8, 'SELECT agency_id as id, agency as label FROM agencies ORDER by agency;', 0, 1),
(8, 'year', 1, NULL, 0, 1),
(8, 'location', 1, NULL, 0, 1),
(8, 'avoidance', 12, NULL, 0, 1),
(8, 'dilution', 12, NULL, 0, 1),
(8, 'primary_ert_id', 8, 'SELECT emission_reduction_technique_id as id, name as label FROM emission_reduction_techniques ORDER by name;', 0, 1),
(8, 'primary_ert_pct', 1, NULL, 0, 1),
(8, 'alternate_primary_ert', 1, NULL, 0, 1),
(8, 'secondary_ert_id', 8, 'SELECT emission_reduction_technique_id as id, name as label FROM emission_reduction_techniques ORDER by name;', 0, 1),
(8, 'alternate_secondary_ert', 1, NULL, 0, 1),
(8, 'dispersion_model', 8, 'SELECT dispersion_model_id as id, name as label FROM dispersion_models ORDER by name;', 0, 1),
(8, 'alternate_disperpersion_model', 1, NULL, 0, 1),
(8, 'pm_min', 1, NULL, 0, 1),
(8, 'pm_max', 1, NULL, 0, 1),
(8, 'day_iso', 1, NULL, 0, 1),
(8, 'night_iso', 1, NULL, 0, 1),
(8, 'manager_name', 1, NULL, 0, 1),
(8, 'manager_number', 1, NULL, 0, 1),
(8, 'manager_cell', 1, NULL, 0, 1);

INSERT INTO `fields` (`table_id`, `column`, `datatype`, `sql`, `primary_key`, `allow_null`, `multiselect`, `ptable`, `stable`, `mmtable`, `pcol`, `scol`, `sdisplay`)
VALUES (8, 'burn_objective', 8, 'SELECT pre_burn_objective_preset_id as id, name as label FROM pre_burn_objective_presets ORDER BY name;', 0, 1, 1, 'pre_burns', 'pre_burn_objective_presets', 'pre_burn_objectives', 'pre_burn_id', 'pre_burn_objective_preset_id', 'name');

INSERT INTO `fields` (`table_id`, `column`, `datatype`, `sql`, `primary_key`, `allow_null`)
VALUES (10, 'burn_id', 7, NULL, 1, 1),
(10, 'location', 1, NULL, 0, 1),
(10, 'airshed_id', 8, 'SELECT airshed_id as id, name as label FROM airsheds ORDER by name;', 0, 1),
(10, 'modify_id', 8, 'SELECT modification_id as id, description as label FROM modifications ORDER by description;', 0, 1),
(10, 'request_acres', 1, NULL, 0, 1),
(10, 'start_date', 4, NULL, 0, 1),
(10, 'end_date', 4, NULL, 0, 1),
(10, 'daily_acres', 1, NULL, 0, 1),
(10, 'comments', 3, NULL, 0, 1),
(10, 'pm_sampler_model', 1, NULL, 0, 1),
(10, 'pm_sampler_id', 1, NULL, 0, 1),
(10, 'manager_name', 1, NULL, 0, 1),
(10, 'manager_number', 1, NULL, 0, 1),
(10, 'manager_cell', 1, NULL, 0, 1);

INSERT INTO `fields` (`table_id`, `column`, `datatype`, `sql`, `primary_key`, `allow_null`)
VALUES (11, 'accomplishment_id', 7, NULL, 1, 1),
(11, 'location', 1, NULL, 0, 1),
(11, 'clearing_index', 1, NULL, 0, 1),
(11, 'state_id', 8, 'SELECT accomplishment_state_id as id, description as label FROM accomplishment_states ORDER by description;', 0, 1),
(11, 'state_comment', 3, NULL, 0, 1),
(11, 'resume_date', 4, NULL, 0, 1),
(11, 'wfu_updates', 12, NULL, 0, 1),
(11, 'wfu_remarks', 3, NULL, 0, 1),
(11, 'black_acres_change', 1, NULL, 0, 1),
(11, 'total_year_acres', 1, NULL, 0, 1),
(11, 'total_project_acres', 1, NULL, 0, 1),
(11, 'start_datetime', 1, NULL, 0, 1),
(11, 'end_datetime', 1, NULL, 0, 1),
(11, 'public_interest_id', 8, 'SELECT interest_level_id as id, name as label FROM interest_levels ORDER by name;', 0, 1),
(11, 'day_vent_id', 8, 'SELECT daytime_ventilation_id as id, name as label FROM daytime_ventilations ORDER by name;', 0, 1),
(11, 'night_smoke_id', 8, 'SELECT nighttime_smoke_id as id, name as label FROM nighttime_smoke ORDER by name;', 0, 1),
(11, 'swr_plan_met', 12, NULL, 0, 1),
(11, 'primary_ert_id', 8, 'SELECT emission_reduction_technique_id as id, name as label FROM emission_reduction_techniques ORDER by name;', 0, 1),
(11, 'primary_ert_pct', 1, NULL, 0, 1),
(11, 'alternate_primary_ert', 1, NULL, 0, 1),
(11, 'secondary_ert_id', 8, 'SELECT emission_reduction_technique_id as id, name as label FROM emission_reduction_techniques ORDER by name;', 0, 1),
(11, 'alternate_secondary_ert', 1, NULL, 0, 1),
(11, 'manager_name', 1, NULL, 0, 1),
(11, 'manager_number', 1, NULL, 0, 1),
(11, 'manager_cell', 1, NULL, 0, 1),
(11, 'manager_fax', 1, NULL, 0, 1);

INSERT INTO `fields` (`table_id`, `column`, `datatype`, `sql`, `primary_key`, `allow_null`)
VALUES (12, 'documentation_id', 7, NULL, 1, 1),
(12, 'location', 1, NULL, 0, 1),
(12, 'observation_date', 4, NULL, 0, 1),
(12, 'observer', 1, NULL, 0, 1),
(12, 'start_time', 1, NULL, 0, 1),
(12, 'end_time', 1, NULL, 0, 1),
(12, 'clearing_index_pred', 1, NULL, 0, 1),
(12, 'clearing_index_act', 1, NULL, 0, 1),
(12, 'end_time', 1, NULL, 0, 1);

INSERT INTO `fields` (`table_id`, `column`, `datatype`, `sql`, `primary_key`, `allow_null`)
VALUES (13, 'observation_id', 7, NULL, 1, 1),
(13, 'time', 1, NULL, 0, 1),
(13, 'column_height', 1, NULL, 0, 1),
(13, 'directional_flow_id', 8, 'SELECT directional_flow_id as id, name as label FROM directional_flows ORDER by name;', 0, 1);


