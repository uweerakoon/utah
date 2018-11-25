<?php
if ($_SERVER['HTTPS'] === 'off') {
  echo json_encode([
    "error"=>true,
    "message"=>"invalid_protocol",
    "detail"=>"A secure connection (HTTPS) is required to access this resource."
  ]);
  exit;
}

/** Include Methods */
// $auth_token = fetch_one(
//   "SELECT "
//
// );

$headers = getallheaders();
$auth_token = "401c5f50b490e3ffc52f0211669eaceac753abbb2b479bc257dcf035bf099f2e";

if ($headers['Authorization'] !== $auth_token) {
  error_log("FETS API - invalid Authorization: " . json_encode($headers['Authorization']), 0);
  echo json_encode([
    "error"=>true,
    "message"=>"invalid_token",
    "detail"=>"You do not have the required permission to access this resource."
  ]);
  exit;
}

if (!array_key_exists('start_date', $_POST)
  || !array_key_exists('end_date', $_POST)) {
  echo json_encode([
    "error"=>true,
    "message"=>"missing_parameters",
    "detail"=>"You are missing required parameters."
  ]);
  exit;
}

$pattern = "/^\d{4}\-\d{2}\-\d{2}$/";
if (!(bool)preg_match($pattern, $_POST['start_date'])
  || !(bool)preg_match($pattern, $_POST['end_date'])) {
  echo json_encode([
    "error"=>true,
    "message"=>"invalid_parameters",
    "detail"=>"One or more parameter(s) has an invalid value."
  ]);
  exit;
}


/** Includes Library Files */
require_once "/var/www/utah/modules/database.php";
require_once "/home/.control.php";
require_once "/var/www/utah/library.php";


/** Get the Fetch Assoc Results */
$result = fetch_assoc(
  "SELECT b.burn_project_id, b.burn_id, a.accomplishment_id,
  b.location,
  IF(bp.number_of_piles > 0, 'piled', 'broadcast') as burn_type,
  b.request_acres,
  a.acres, a.tons_consumed, a.nffl_models,
  a.start_datetime as ignition_started,
  a.end_datetime as ignition_ended
  FROM burns b
  JOIN burn_projects bp ON(b.burn_project_id = bp.burn_project_id)
  LEFT JOIN (
    SELECT a1.accomplishment_id,
    a1.burn_id,
    a1.start_datetime,
    a1.end_datetime,
    SUM(f.black_acres) as acres,
    SUM(f.total_tons) as tons_consumed,
    GROUP_CONCAT(DISTINCT ft.nffl_model SEPARATOR ' ') as nffl_models
    FROM accomplishments a1
    JOIN accomplishment_fuels f ON(f.accomplishment_id = a1.accomplishment_id)
    JOIN fuels fu ON(fu.fuel_id = f.fuel_id)
    JOIN fuel_types ft ON(fu.fuel_type_id = ft.fuel_type_id)
    WHERE status_id = 4
    AND f.total_tons > 0
    AND fu.emission_type_id = 1
    GROUP BY accomplishment_id
  ) a ON(a.burn_id = b.burn_id)
  WHERE start_datetime BETWEEN ? AND ?
  AND b.status_id = 5
  GROUP BY burn_project_id, burn_id, accomplishment_id, location,
  bp.number_of_piles;",
  [$_POST['start_date'], $_POST['end_date']]
);
echo json_encode($result);
exit;
?>
