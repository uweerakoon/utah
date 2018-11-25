SELECT br.year as \"Year\", pb.pre_burn_id, a.abbreviation as \"Agency\",
  d.identifier as \"Unit\", b.project_number as \"PIN\", b.project_name as \"Project Name\", ai.name as \"Airshed\",
  ct.name as \"County\", IF(b.de_minimis = 0, 'False', 'True') as \"De Minimis\",
  ROUND(REPLACE(SPLIT_STR(b.location, ',', 1), '(', ''), 4) as \"Latitude\",
  ROUND(REPLACE(SPLIT_STR(b.location, ',', 2), ')', ''), 4) as \"Longitude\",
  b.elevation_low as \"Lowest\", b.elevation_high as \"Highest\",
  b.project_acres as \"Planned Acres\", b.completion_year as \"Planned Completion Year\",
  b.black_acres_current as \"Current Yearly Acres\", bt.name as \"Burn Type\", b.number_of_piles as \"Number of Piles\",
  im.name as \"Ignition Method\", b.major_fbps_fuel as \"Fuel Model\",
  f5.first as \"Earliest Date (Form 5)\", b.duration as \"Days Planned\",
  pt.name as \"Primary ERT Method\", pb.primary_ert_pct as \"Primary ERT Percent\",
  st.name as \"Secondary ERT Method\", pb.alternate_primary_ert as \"Primary ERT Other Description\",
  pb.alternate_secondary_ert as \"Secondary ERT Other Description\", b.submitted_on as \"Last Form 2\",
  f3.forms as \"Form 3\", f3.last as \"Last Form 3\", f4.forms as \"Form 4\", f4.last as \"Last Form 4\",
  f5.forms as \"Form 5\", f5.last as \"Last Form 5\",  f9.forms as \"Form 9\", f9.last as \"Last Form 9\",
  af.black_acres as \"Black Acres\", ROUND(af.tons_consumed, 4) as \"Tons Consumed\", ROUND(pf.tons_pm10, 4) as \"Total Tons PM 10\"
  {$pf_select} {$pte_select}
  FROM (
    SELECT burn_id, burn_project_id, YEAR(start_date) as year
    FROM burns
    WHERE status_id = 5
    AND (
      start_date BETWEEN '{$start_date}' AND '{$end_date}'
      OR end_date BETWEEN '{$start_date}' AND '{$end_date}'
    )
  ) br
  JOIN burn_projects b ON (br.burn_project_id = b.burn_project_id)
  JOIN districts d ON (b.district_id = d.district_id)
  JOIN agencies a ON (d.agency_id = a.agency_id)
  JOIN users u ON (b.submitted_by = u.user_id)
  JOIN (
    SELECT burn_project_id,
    pre_burn_id,
    year as current_year,
    primary_ert_id,
    primary_ert_pct,
    alternate_primary_ert,
    secondary_ert_id,
    alternate_secondary_ert
    FROM pre_burns
  ) pb ON(br.burn_project_id = pb.burn_project_id)
  LEFT JOIN airsheds ai ON(ai.airshed_id = b.airshed_id)
  LEFT JOIN counties ct ON(ct.county_id = b.county)
  LEFT JOIN burn_pile_types bt ON(b.burn_type = bt.burn_pile_type_id)
  LEFT JOIN ignition_methods im ON(b.ignition_method = im.ignition_method_id)
  LEFT JOIN emission_reduction_techniques pt ON(pb.primary_ert_id = pt.emission_reduction_technique_id)
  LEFT JOIN emission_reduction_techniques st ON(pb.secondary_ert_id = st.emission_reduction_technique_id)
  LEFT JOIN yearly_project_fuels_wide pf ON(pf.burn_project_id = b.burn_project_id AND pf.year = pb.year)
  LEFT JOIN (
    SELECT burn_project_id,
    YEAR,
    AVG(black_acres) AS black_acres,
    AVG(tons_consumed) AS tons_consumed
    FROM yearly_project_fuels
    WHERE YEAR BETWEEN YEAR('{$start_date}') AND YEAR('{$end_date}')
    GROUP BY burn_project_id, YEAR
  ) af ON(af.burn_project_id = pb.burn_project_id AND af.year = pb.year)
  LEFT JOIN (
    SELECT burn_project_id,
    YEAR(submitted_on) AS YEAR,
    COUNT(pre_burn_id) AS forms,
    MAX(submitted_on) AS LAST
    FROM pre_burns
    WHERE submitted_on BETWEEN '{$start_date}' AND '{$end_date}'
    GROUP BY burn_project_id, YEAR(submitted_on)
  ) f3 ON(f3.burn_project_id = pb.burn_project_id AND f3.year = pb.year)
  LEFT JOIN (
    SELECT burn_project_id,
    YEAR(submitted_on) AS YEAR,
    COUNT(burn_id) AS forms,
    MAX(submitted_on) AS LAST
    FROM burns
    WHERE submitted_on BETWEEN '{$start_date}' AND '{$end_date}'
    GROUP BY burn_project_id, YEAR(submitted_on)
  ) f4 ON(f4.burn_project_id = pb.burn_project_id AND f4.year = pb.year)
  LEFT JOIN (
    SELECT burn_project_id,
    YEAR(submitted_on) AS YEAR,
    COUNT(accomplishment_id) AS forms,
    MAX(submitted_on) AS LAST,
    MIN(submitted_on) AS FIRST
    FROM accomplishments
    WHERE submitted_on BETWEEN '{$start_date}' AND '{$end_date}'
    GROUP BY burn_project_id, YEAR(submitted_on)
  ) f5 ON(f5.burn_project_id = pb.burn_project_id AND f5.year = pb.year)
  LEFT JOIN (
    SELECT burn_project_id,
    YEAR(submitted_on) AS YEAR,
    COUNT(documentation_id) AS forms,
    MAX(submitted_on) AS LAST
    FROM documentation
    WHERE submitted_on BETWEEN '{$start_date}' AND '{$end_date}'
    GROUP BY burn_project_id, YEAR(submitted_on)
  ) f9 ON(f9.burn_project_id = pb.burn_project_id AND f9.year = pb.year)
  {$pte_join}
  WHERE pb.year IS NOT NULL
  $agency_cond
  $county_cond
  ORDER BY a.agency, af.year, d.district, b.project_number;
