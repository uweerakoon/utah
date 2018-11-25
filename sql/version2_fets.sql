--
-- Requirements:
--   project id, burn id,date, location, acres, fuel loading,
--   tons consumed, fuel type, burn type (e.g., broadcast, piled)
--

SELECT a.burn_project_id, a.burn_id, a.accomplishment_id,
a.location, b.request_acres, SUM(f.black_acres) as acres,
SUM(f.total_tons) as tons_consumed,
GROUP_CONCAT(DISTINCT ft.nffl_model SEPARATOR ' ') as nffl_models
FROM accomplishments a
JOIN burns b ON(a.burn_id = b.burn_id)
JOIN accomplishment_fuels f ON(f.accomplishment_id = a.accomplishment_id)
JOIN fuels fu ON(fu.fuel_id = f.fuel_id)
JOIN fuel_types ft ON(fu.fuel_type_id = ft.fuel_type_id)
WHERE start_datetime BETWEEN '2016-01-01 00:00:00' AND '2017-02-01 00:00:00'
AND a.status_id = 4
AND f.total_tons > 0
AND fu.emission_type_id = 1
GROUP BY burn_project_id, burn_id, accomplishment_id, location;


SELECT *
FROM accomplishments a
JOIN burns b ON(a.burn_id = b.burn_id)
JOIN accomplishment_fuels f ON(f.accomplishment_id = a.accomplishment_id)
JOIN fuels fu ON(fu.fuel_id = f.fuel_id)
JOIN fuel_types ft ON(fu.fuel_type_id = ft.fuel_type_id)
WHERE start_datetime BETWEEN '2016-01-01 00:00:00' AND '2017-02-01 00:00:00'
AND a.status_id = 4
AND f.total_tons > 0
LIMIT 3;
