SELECT af.year AS "Year",
       a.abbreviation AS "Agency",
       d.identifier AS "Unit",
       b.project_number AS "PIN",
       b.project_name AS "Project Name",
       ai.name AS "Airshed",
       ct.name AS "County",
       IF(b.de_minimis = 0, 'False', 'True') AS "De Minimis",
       ROUND(REPLACE(SPLIT_STR(b.location, ',', 1), '(', ''), 4) AS "Latitude",
       ROUND(REPLACE(SPLIT_STR(b.location, ',', 2), ')', ''), 4) AS "Longitude",
       b.elevation_low AS "Lowest",
       b.elevation_high AS "Highest",
       b.project_acres AS "Planned Acres",
       b.completion_year AS "Planned Completion Year",
       b.black_acres_current AS "Current Yearly Acres",
       bt.name AS "Burn Type",
       b.number_of_piles AS "Number of Piles",
       im.name AS "Ignition Method",
       b.major_fbps_fuel AS "Fuel Model",
       f5.first AS "Earliest Date (Form 5)",
       b.duration AS "Days Planned",
       pt.name AS "Primary ERT Method",
       pb.primary_ert_pct AS "Primary ERT Percent",
       st.name AS "Secondary ERT Method",
       pb.alternate_primary_ert AS "Primary ERT Other Description",
       pb.alternate_secondary_ert AS "Secondary ERT Other Description",
       b.submitted_on AS "Last Form 2",
       f3.forms AS "Form 3",
       f3.last AS "Last Form 3",
       f4.forms AS "Form 4",
       f4.last AS "Last Form 4",
       f5.forms AS "Form 5",
       f5.last AS "Last Form 5",
       f9.forms AS "Form 9",
       f9.last AS "Last Form 9",
       af.black_acres AS "Black Acres",
       ROUND(af.tons_consumed, 4) AS "Tons Consumed",
       ROUND(pf.tons_pm10, 4) AS "Total Tons PM 10"
FROM burn_projects b
JOIN districts d ON (b.district_id = d.district_id)
JOIN agencies a ON (d.agency_id = a.agency_id)
JOIN users u ON (b.submitted_by = u.user_id)
LEFT JOIN airsheds ai ON(ai.airshed_id = b.airshed_id)
LEFT JOIN counties ct ON(ct.county_id = b.county)
LEFT JOIN burn_pile_types bt ON(b.burn_type = bt.burn_pile_type_id)
LEFT JOIN ignition_methods im ON(b.ignition_method = im.ignition_method_id)
LEFT JOIN (
  SELECT burn_project_id,
  YEAR,
  primary_ert_id,
  primary_ert_pct,
  alternate_primary_ert,
  secondary_ert_id,
  alternate_secondary_ert
  FROM pre_burns
  WHERE status_id = 4
) pb ON (pb.burn_project_id = b.burn_project_id)
LEFT JOIN emission_reduction_techniques pt ON(pb.primary_ert_id = pt.emission_reduction_technique_id)
LEFT JOIN emission_reduction_techniques st ON(pb.secondary_ert_id = st.emission_reduction_technique_id)
LEFT JOIN yearly_project_fuels_wide pf ON(pf.burn_project_id = b.burn_project_id AND pf.year = pb.year)
LEFT JOIN (
  SELECT burn_project_id,
  YEAR,
  AVG(black_acres) AS black_acres,
  AVG(tons_consumed) AS tons_consumed
  FROM yearly_project_fuels
  WHERE YEAR BETWEEN YEAR('2017-01-01') AND YEAR('2018-01-01')
  GROUP BY burn_project_id, YEAR
) af ON(af.burn_project_id = pb.burn_project_id AND af.year = pb.year)
LEFT JOIN (
  SELECT burn_project_id,
  YEAR(submitted_on) AS YEAR,
  COUNT(pre_burn_id) AS forms,
  MAX(submitted_on) AS LAST
  FROM pre_burns
  WHERE submitted_on BETWEEN '2017-01-01' AND '2018-01-01'
  GROUP BY burn_project_id, YEAR(submitted_on)
) f3 ON(f3.burn_project_id = pb.burn_project_id AND f3.year = pb.year)
LEFT JOIN (
  SELECT burn_project_id,
  YEAR(submitted_on) AS YEAR,
  COUNT(burn_id) AS forms,
  MAX(submitted_on) AS LAST
  FROM burns
  WHERE submitted_on BETWEEN '2017-01-01' AND '2018-01-01'
  GROUP BY burn_project_id, YEAR(submitted_on)
) f4 ON(f4.burn_project_id = pb.burn_project_id AND f4.year = pb.year)
LEFT JOIN (
  SELECT burn_project_id,
  YEAR(submitted_on) AS YEAR,
  COUNT(accomplishment_id) AS forms,
  MAX(submitted_on) AS LAST,
  MIN(submitted_on) AS FIRST
  FROM accomplishments
  WHERE submitted_on BETWEEN '2017-01-01' AND '2018-01-01'
  GROUP BY burn_project_id, YEAR(submitted_on)
) f5 ON(f5.burn_project_id = pb.burn_project_id AND f5.year = pb.year)
LEFT JOIN (
  SELECT burn_project_id,
  YEAR(submitted_on) AS YEAR,
  COUNT(documentation_id) AS forms,
  MAX(submitted_on) AS LAST
  FROM documentation
  WHERE submitted_on BETWEEN '2017-01-01' AND '2018-01-01'
  GROUP BY burn_project_id, YEAR(submitted_on)
) f9 ON(f9.burn_project_id = pb.burn_project_id AND f9.year = pb.year)
WHERE true
AND a.agency_id IN(33,7,2,1,4,3,5,6)
AND (b.county IN(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29) OR b.county IS NULL)
ORDER BY a.agency, af.year, d.district, b.project_number;
