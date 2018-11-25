SELECT
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
  ROUND(pf.tons_pm10, 4) AS "Total Tons PM 10",
  ROUND(pf.tons_pm25, 4) AS "Total Tons PM 2.5",
  ROUND(pf.tons_nmoc, 4) AS "Total Tons NMOC",
  ROUND(pf.tons_co, 4) AS "Total Tons CO",
  ROUND(pf.tons_co2, 4) AS "Total Tons CO2",
  ROUND(pf.tons_ch4, 4) AS "Total Tons CH4",
  ROUND(pf.tons_nox, 4) AS "Total Tons NOX",
  ROUND(pf.tons_so2, 4) AS "Total Tons SO2",
  ROUND(pf.tons_nh3, 4) AS "Total Tons NH3",
  ROUND(pf.tons_bc, 4) AS "Total Tons Black Carbon",
  ROUND(pf.tons_oc, 4) AS "Total Tons Organic Carbon",
  (
    ifnull(pf.tons_13_butadiene, 0) + ifnull(pf.tons_2378_tcdd_teq, 0) +
    ifnull(pf.tons_acetaldehyde, 0) + ifnull(pf.tons_acrolein, 0) +
    ifnull(pf.tons_anthracene, 0) + ifnull(pf.tons_benzaanthracene, 0) +
    ifnull(pf.tons_benzene, 0) + ifnull(pf.tons_benzoaflouranthene, 0) +
    ifnull(pf.tons_benzoapyrene, 0) + ifnull(pf.tons_benzocphenanthrene, 0) +
    ifnull(pf.tons_benzoepyrene, 0) + ifnull(pf.tons_benzghiperylene, 0) +
    ifnull(pf.tons_benzokfluoranthene, 0) +
    ifnull(pf.tons_benzofluoranthenes, 0) +
    ifnull(pf.tons_carbonyl_sulfide, 0) + ifnull(pf.tons_chrysene, 0) +
    ifnull(pf.tons_fluoranthene, 0) + ifnull(pf.tons_formaldehyde, 0) +
    ifnull(pf.tons_indeno123_cdpyrene, 0) +
    ifnull(pf.tons_methyl_chloride, 0) + ifnull(pf.tons_methylanthracene, 0) +
    ifnull(pf.tons_methylbenzopyrenes, 0) + ifnull(pf.tons_methylchrysene, 0) +
    ifnull(pf.tons_methylpyrene_fluoranthene, 0) +
    ifnull(pf.tons_n_hexane, 0) + ifnull(pf.tons_omp_xylene, 0) +
    ifnull(pf.tons_perylene, 0) + ifnull(pf.tons_phenanthrene, 0) +
    ifnull(pf.tons_pyrene, 0) + ifnull(pf.tons_toluene, 0)
  ) AS "Total Tons VOC"
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
  primary_ert_id,
  primary_ert_pct,
  alternate_primary_ert,
  secondary_ert_id,
  alternate_secondary_ert
  FROM pre_burns
  WHERE submitted_on IN(
    SELECT MAX(submitted_on)
    FROM pre_burns p
    WHERE p.active = TRUE
    AND YEAR = YEAR(CURDATE())
    GROUP BY p.burn_project_id
  )
) pb ON(pb.burn_project_id = b.burn_project_id)
LEFT JOIN emission_reduction_techniques pt ON(pb.primary_ert_id = pt.emission_reduction_technique_id)
LEFT JOIN emission_reduction_techniques st ON(pb.secondary_ert_id = st.emission_reduction_technique_id)
LEFT JOIN project_fuels_wide pf ON(pf.burn_project_id = b.burn_project_id)
LEFT JOIN (
  SELECT burn_project_id,
  AVG(black_acres) AS black_acres,
  AVG(tons_consumed) AS tons_consumed
  FROM project_fuels
  GROUP BY burn_project_id
) af ON(af.burn_project_id = b.burn_project_id)
LEFT JOIN (
  SELECT burn_project_id,
  COUNT(pre_burn_id) AS forms,
  MAX(submitted_on) AS LAST
  FROM pre_burns
  WHERE submitted_on BETWEEN '2015-12-31' AND '2016-12-31'
  GROUP BY burn_project_id
) f3 ON(f3.burn_project_id = b.burn_project_id)
LEFT JOIN (
  SELECT burn_project_id,
  COUNT(burn_id) AS forms,
  MAX(submitted_on) AS LAST
  FROM burns
  WHERE submitted_on BETWEEN '2015-12-31' AND '2016-12-31'
  GROUP BY burn_project_id
) f4 ON(f4.burn_project_id = b.burn_project_id)
LEFT JOIN (
  SELECT burn_project_id,
  COUNT(accomplishment_id) AS forms,
  MAX(submitted_on) AS LAST,
  MIN(submitted_on) AS FIRST
  FROM accomplishments
  WHERE submitted_on BETWEEN '2015-12-31' AND '2016-12-31'
  GROUP BY burn_project_id
) f5 ON(f5.burn_project_id = b.burn_project_id)
LEFT JOIN (
  SELECT burn_project_id,
  COUNT(documentation_id) AS forms,
  MAX(submitted_on) AS LAST
  FROM documentation
  WHERE submitted_on BETWEEN '2015-12-31' AND '2016-12-31'
  GROUP BY burn_project_id
) f9 ON(f9.burn_project_id = b.burn_project_id)
WHERE b.submitted_on BETWEEN '2015-12-31' AND '2016-12-31'
AND a.agency_id IN(33, 7, 2, 1, 4, 3, 5, 6)
AND (b.county IN(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16,
  17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29)
  OR b.county IS NULL
)
ORDER BY a.agency, d.district, b.project_number;
