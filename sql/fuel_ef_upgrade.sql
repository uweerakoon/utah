--
--	Inactivate Utah Fuels (PM10)
--

UPDATE fuels
SET is_active = 0
WHERE fuel_id >= 1
AND fuel_id <= 13;

--
--	Activate PMDetail Fuels (PM10)
--

UPDATE fuels
SET is_active = 1
WHERE fuel_id >= 27
AND fuel_id <= 39;

--
--	Update the fuels.
--

UPDATE accomplishment_fuels
SET fuel_id = 27, tons_emitted = (total_tons * (SELECT ef FROM fuels WHERE fuel_id = 27))
WHERE fuel_id = 1;

UPDATE accomplishment_fuels
SET fuel_id = 28, tons_emitted = (total_tons * (SELECT ef FROM fuels WHERE fuel_id = 28))
WHERE fuel_id = 2;

UPDATE accomplishment_fuels
SET fuel_id = 29, tons_emitted = (total_tons * (SELECT ef FROM fuels WHERE fuel_id = 29))
WHERE fuel_id = 3;

UPDATE accomplishment_fuels
SET fuel_id = 30, tons_emitted = (total_tons * (SELECT ef FROM fuels WHERE fuel_id = 30))
WHERE fuel_id = 4;

UPDATE accomplishment_fuels
SET fuel_id = 31, tons_emitted = (total_tons * (SELECT ef FROM fuels WHERE fuel_id = 31))
WHERE fuel_id = 5;

UPDATE accomplishment_fuels
SET fuel_id = 32, tons_emitted = (total_tons * (SELECT ef FROM fuels WHERE fuel_id = 32))
WHERE fuel_id = 6;

UPDATE accomplishment_fuels
SET fuel_id = 33, tons_emitted = (total_tons * (SELECT ef FROM fuels WHERE fuel_id = 33))
WHERE fuel_id = 7;

UPDATE accomplishment_fuels
SET fuel_id = 34, tons_emitted = (total_tons * (SELECT ef FROM fuels WHERE fuel_id = 34))
WHERE fuel_id = 8;

UPDATE accomplishment_fuels
SET fuel_id = 35, tons_emitted = (total_tons * (SELECT ef FROM fuels WHERE fuel_id = 35))
WHERE fuel_id = 9;

UPDATE accomplishment_fuels
SET fuel_id = 36, tons_emitted = (total_tons * (SELECT ef FROM fuels WHERE fuel_id = 36))
WHERE fuel_id = 10;

UPDATE accomplishment_fuels
SET fuel_id = 37, tons_emitted = (total_tons * (SELECT ef FROM fuels WHERE fuel_id = 37))
WHERE fuel_id = 11;

UPDATE accomplishment_fuels
SET fuel_id = 38, tons_emitted = (total_tons * (SELECT ef FROM fuels WHERE fuel_id = 38))
WHERE fuel_id = 12;

UPDATE accomplishment_fuels
SET fuel_id = 39, tons_emitted = (total_tons * (SELECT ef FROM fuels WHERE fuel_id = 39))
WHERE fuel_id = 13;