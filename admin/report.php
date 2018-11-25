<?php
$page_title = "Reports / Utah.gov";
include '../checklogin.php';
$extra_js = "";
$header = checklogin(array('title'=>$page_title,'extra_js'=>$extra_js,));
$permissions = checkFunctionPermissionsAll($_SESSION['user']['id'], array('user','user_district','user_agency','admin'));

if (isset($_POST['my'])) {
    extract($_POST['my']);

    if (is_array($agency)) {
        $agency_cond = "AND a.agency_id IN(";
        for ($i=0; $i<count($agency); $i++) {
            if ($i == count($agency) - 1) {
                $agency_cond .= $agency[$i];
            } else {
                $agency_cond .= $agency[$i]. ',';
            }
        }
        $agency_cond .= ")";
    } else {
        $agency_cond = "AND a.agency_id = $agency";
    }

    if (is_array($county)) {
        $county_cond = "AND (b.county IN(";
        for ($i=0; $i<count($county); $i++) {
            if ($i == count($county) - 1) {
                $county_cond .= $county[$i];
            } else {
                $county_cond .= $county[$i]. ',';
            }
        }
        $county_cond .= ") OR b.county IS NULL)";
    } else {
        $county_cond = "AND (b.county = $county
            OR c.county IS NULL)";
    }

    switch($type) {
        case 1:
          /**
           * Yearly Burn Project Totals
           */

          if ($all_emissions || $sum_voc) {
            if ($sum_voc) {
              $voc_block = "(ifnull(pf.tons_13_butadiene, 0) + ifnull(pf.tons_2378_tcdd_teq, 0) + ifnull(pf.tons_acetaldehyde, 0) + ifnull(pf.tons_acrolein, 0)
                + ifnull(pf.tons_anthracene, 0) + ifnull(pf.tons_benzaanthracene, 0) + ifnull(pf.tons_benzene, 0) + ifnull(pf.tons_benzoaflouranthene, 0)
                + ifnull(pf.tons_benzoapyrene, 0) + ifnull(pf.tons_benzocphenanthrene, 0) + ifnull(pf.tons_benzoepyrene, 0) + ifnull(pf.tons_benzghiperylene, 0)
                + ifnull(pf.tons_benzokfluoranthene, 0) + ifnull(pf.tons_benzofluoranthenes, 0) + ifnull(pf.tons_carbonyl_sulfide, 0) + ifnull(pf.tons_chrysene, 0)
                + ifnull(pf.tons_fluoranthene, 0) + ifnull(pf.tons_formaldehyde, 0) +ifnull(pf.tons_indeno123_cdpyrene, 0) + ifnull(pf.tons_methyl_chloride, 0)
                + ifnull(pf.tons_methylanthracene, 0) + ifnull(pf.tons_methylbenzopyrenes, 0) + ifnull(pf.tons_methylchrysene, 0) + ifnull(pf.tons_methylpyrene_fluoranthene, 0)
                + ifnull(pf.tons_n_hexane, 0) + ifnull(pf.tons_omp_xylene, 0) + ifnull(pf.tons_perylene, 0) + ifnull(pf.tons_phenanthrene, 0) + ifnull(pf.tons_pyrene, 0)
                + ifnull(pf.tons_toluene, 0)) as \"Total Tons VOC\"";
            } else {
              $voc_block = "ROUND(pf.tons_13_butadiene, 4) as \"Total Tons 1_3-Butadiene\",
                ROUND(pf.tons_2378_tcdd_teq, 4) as \"Total Tons 2_3_7_8-TCDD TEQ\",
                ROUND(pf.tons_acetaldehyde, 4) as \"Total Tons Acetaldehyde\",
                ROUND(pf.tons_acrolein, 4) as \"Total Tons Acrolein\",
                ROUND(pf.tons_anthracene, 4) as \"Total Tons Anthracene\",
                ROUND(pf.tons_benzaanthracene, 4) as \"Total Tons Benz(a)anthracene\",
                ROUND(pf.tons_benzene, 4) as \"Total Tons Benzene\",
                ROUND(pf.tons_benzoaflouranthene, 4) as \"Total Tons Benzo(a)fluoranthene\",
                ROUND(pf.tons_benzoapyrene, 4) as \"Total Tons Benzo(a)pyrene\",
                ROUND(pf.tons_benzocphenanthrene, 4) as \"Total Tons Benzo(c)phenanthrene\",
                ROUND(pf.tons_benzoepyrene, 4) as \"Total Tons Benzo(e)pyrene\",
                ROUND(pf.tons_benzghiperylene, 4) as \"Total Tons Benz(g_h_i)perylene\",
                ROUND(pf.tons_benzokfluoranthene, 4) as \"Total Tons Benzo(k)fluoranthene\",
                ROUND(pf.tons_benzofluoranthenes, 4) as \"Total Tons Benzofluoranthenes\",
                ROUND(pf.tons_carbonyl_sulfide, 4) as \"Total Tons Carbonyl Sulfide\",
                ROUND(pf.tons_chrysene, 4) as \"Total Tons Chrysene\",
                ROUND(pf.tons_fluoranthene, 4) as \"Total Tons Fluoranthene\",
                ROUND(pf.tons_formaldehyde, 4) as \"Total Tons Formaldehyde\",
                ROUND(pf.tons_indeno123_cdpyrene, 4) as \"Total Tons Indeno(1_2_3-cd)pyrene\",
                ROUND(pf.tons_methyl_chloride, 4) as \"Total Tons Chloride\",
                ROUND(pf.tons_methylanthracene, 4) as \"Total Tons Methylanthracene\",
                ROUND(pf.tons_methylbenzopyrenes, 4) as \"Total Tons Methylbenzopyrenes\",
                ROUND(pf.tons_methylchrysene, 4) as \"Total Tons Methylchrysene\",
                ROUND(pf.tons_methylpyrene_fluoranthene, 4) as \"Total Tons Methylpyrene_-Fluoranthene\",
                ROUND(pf.tons_n_hexane, 4) as \"Total Tons N-Hexane\",
                ROUND(pf.tons_omp_xylene, 4) as \"Total Tons O.m.p-xylene\",
                ROUND(pf.tons_perylene, 4) as \"Total Tons Perylene\",
                ROUND(pf.tons_phenanthrene, 4) as \"Total Tons Phenanthrene\",
                ROUND(pf.tons_pyrene, 4) as \"Total Tons Pyrene\",
                ROUND(pf.tons_toluene,  4) as \"Total Tons Toluene\"";
            }

            $pf_select = ", ROUND(pf.tons_pm25, 4) as \"Total Tons PM 2.5\",
              ROUND(pf.tons_nmoc, 4) as \"Total Tons NMOC\",
              ROUND(pf.tons_co, 4) as \"Total Tons CO\",
              ROUND(pf.tons_co2, 4) as \"Total Tons CO2\",
              ROUND(pf.tons_ch4, 4) as \"Total Tons CH4\",
              ROUND(pf.tons_nox, 4) as \"Total Tons NOX\",
              ROUND(pf.tons_so2, 4) as \"Total Tons SO2\",
              ROUND(pf.tons_nh3, 4) as \"Total Tons NH3\",
              ROUND(pf.tons_bc, 4) as \"Total Tons Black Carbon\",
              ROUND(pf.tons_oc, 4) as \"Total Tons Organic Carbon\",
              {$voc_block}
              ";
          }

          if ($potential_emissions) {
            $pte_select = ", (pte10.ef*pte10.tpa*pte10.acres) as \"Potential PM 10\", (pte25.ef*pte25.tpa*pte25.acres) as \"Potential PM 2.5\"";
            $pte_join = "LEFT JOIN (
                SELECT b.burn_project_id, f.ef as ef, t.ton_per_acre as tpa,
                COALESCE(b.black_acres_current, 0) as acres
                FROM burn_projects b
                JOIN fuel_types t ON(b.major_fbps_fuel = t.fuel_type_id)
                JOIN fuels f ON(t.fuel_type_id = f.fuel_type_id)
                WHERE f.emission_type_id = 1
                AND f.is_active
              ) pte10 ON(pte10.burn_project_id = b.burn_project_id)
              LEFT JOIN (
                SELECT b.burn_project_id, f.ef as ef, t.ton_per_acre as tpa,
                COALESCE(b.black_acres_current, 0) as acres
                FROM burn_projects b
                JOIN fuel_types t ON(b.major_fbps_fuel = t.fuel_type_id)
                JOIN fuels f ON(t.fuel_type_id = f.fuel_type_id)
                WHERE f.emission_type_id = 2
                AND f.is_active
              ) pte25 ON(pte25.burn_project_id = b.burn_project_id)";
          }

          $sql = "SELECT br.year as \"Year\", pb.current_year as \"Pre-Burns Latest Year\", a.abbreviation as \"Agency\",
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
            ROUND(af.black_acres, 4) as \"Black Acres\", ROUND(af.tons_consumed, 4) as \"Tons Consumed\",
            ROUND(pf.tons_pm10, 4) as \"Total Tons PM 10\"
            {$pf_select} {$pte_select}
            FROM (
              SELECT burn_project_id, pre_burn_id, YEAR(start_date) as year
              FROM burns
              WHERE status_id = 5
              AND (
                start_date BETWEEN '{$start_date}' AND '{$end_date}'
                OR end_date BETWEEN '{$start_date}' AND '{$end_date}'
              )
              GROUP BY burn_project_id, pre_burn_id, YEAR(start_date)
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
            ) pb ON(br.pre_burn_id = pb.pre_burn_id)
            LEFT JOIN airsheds ai ON(ai.airshed_id = b.airshed_id)
            LEFT JOIN counties ct ON(ct.county_id = b.county)
            LEFT JOIN burn_pile_types bt ON(b.burn_type = bt.burn_pile_type_id)
            LEFT JOIN ignition_methods im ON(b.ignition_method = im.ignition_method_id)
            LEFT JOIN emission_reduction_techniques pt ON(pb.primary_ert_id = pt.emission_reduction_technique_id)
            LEFT JOIN emission_reduction_techniques st ON(pb.secondary_ert_id = st.emission_reduction_technique_id)
            LEFT JOIN yearly_project_fuels_wide pf ON(pf.burn_project_id = b.burn_project_id AND pf.year = br.year)
            LEFT JOIN (
              SELECT burn_project_id, year,
              AVG(black_acres) AS black_acres,
              AVG(tons_consumed) AS tons_consumed
              FROM yearly_project_fuels
              GROUP BY burn_project_id, YEAR
            ) af ON(af.burn_project_id = br.burn_project_id AND af.year = br.year)
            LEFT JOIN (
              SELECT burn_project_id,
              YEAR(submitted_on) AS YEAR,
              COUNT(pre_burn_id) AS forms,
              MAX(submitted_on) AS LAST
              FROM pre_burns
              WHERE submitted_on BETWEEN '{$start_date}' AND '{$end_date}'
              GROUP BY burn_project_id, YEAR(submitted_on)
            ) f3 ON(f3.burn_project_id = pb.burn_project_id AND f3.year = br.year)
            LEFT JOIN (
              SELECT burn_project_id,
              YEAR(submitted_on) AS YEAR,
              COUNT(burn_id) AS forms,
              MAX(submitted_on) AS LAST
              FROM burns
              WHERE submitted_on BETWEEN '{$start_date}' AND '{$end_date}'
              GROUP BY burn_project_id, YEAR(submitted_on)
            ) f4 ON(f4.burn_project_id = pb.burn_project_id AND f4.year = br.year)
            LEFT JOIN (
              SELECT burn_project_id,
              YEAR(submitted_on) AS YEAR,
              COUNT(accomplishment_id) AS forms,
              MAX(submitted_on) AS LAST,
              MIN(submitted_on) AS FIRST
              FROM accomplishments
              WHERE submitted_on BETWEEN '{$start_date}' AND '{$end_date}'
              GROUP BY burn_project_id, YEAR(submitted_on)
            ) f5 ON(f5.burn_project_id = pb.burn_project_id AND f5.year = br.year)
            LEFT JOIN (
              SELECT burn_project_id,
              YEAR(submitted_on) AS YEAR,
              COUNT(documentation_id) AS forms,
              MAX(submitted_on) AS LAST
              FROM documentation
              WHERE submitted_on BETWEEN '{$start_date}' AND '{$end_date}'
              GROUP BY burn_project_id, YEAR(submitted_on)
            ) f9 ON(f9.burn_project_id = pb.burn_project_id AND f9.year = br.year)
            {$pte_join}
            WHERE br.year IS NOT NULL
            {$agency_cond}
            {$county_cond}
            ORDER BY a.agency, af.year, d.district, b.project_number;";

          $csv = get_csv($sql);
          break;
        case 2:
          /**
           *  Burn Project Totals Report
           */

          if ($all_emissions || $sum_voc) {
            if ($sum_voc) {
              $voc_block = "(ifnull(tons_13_butadiene, 0) + ifnull(tons_2378_tcdd_teq, 0) + ifnull(tons_acetaldehyde, 0) + ifnull(tons_acrolein, 0)
                + ifnull(tons_anthracene, 0) + ifnull(tons_benzaanthracene, 0) + ifnull(tons_benzene, 0) + ifnull(tons_benzoaflouranthene, 0)
                + ifnull(tons_benzoapyrene, 0) + ifnull(tons_benzocphenanthrene, 0) + ifnull(tons_benzoepyrene, 0) + ifnull(tons_benzghiperylene, 0)
                + ifnull(tons_benzokfluoranthene, 0) + ifnull(tons_benzofluoranthenes, 0) + ifnull(tons_carbonyl_sulfide, 0) + ifnull(tons_chrysene, 0)
                + ifnull(tons_fluoranthene, 0) + ifnull(tons_formaldehyde, 0) +ifnull(tons_indeno123_cdpyrene, 0) + ifnull(tons_methyl_chloride, 0)
                + ifnull(tons_methylanthracene, 0) + ifnull(tons_methylbenzopyrenes, 0) + ifnull(tons_methylchrysene, 0) + ifnull(tons_methylpyrene_fluoranthene, 0)
                + ifnull(tons_n_hexane, 0) + ifnull(tons_omp_xylene, 0) + ifnull(tons_perylene, 0) + ifnull(tons_phenanthrene, 0) + ifnull(tons_pyrene, 0)
                + ifnull(tons_toluene, 0)) as \"Total Tons VOC\"";
            } else {
              $voc_block = "ROUND(tons_13_butadiene, 4) as \"Total Tons 1_3-Butadiene\",
                ROUND(tons_2378_tcdd_teq, 4) as \"Total Tons 2_3_7_8-TCDD TEQ\",
                ROUND(tons_acetaldehyde, 4) as \"Total Tons Acetaldehyde\",
                ROUND(tons_acrolein, 4) as \"Total Tons Acrolein\",
                ROUND(tons_anthracene, 4) as \"Total Tons Anthracene\",
                ROUND(tons_benzaanthracene, 4) as \"Total Tons Benz(a)anthracene\",
                ROUND(tons_benzene, 4) as \"Total Tons Benzene\",
                ROUND(tons_benzoaflouranthene, 4) as \"Total Tons Benzo(a)fluoranthene\",
                ROUND(tons_benzoapyrene, 4) as \"Total Tons Benzo(a)pyrene\",
                ROUND(tons_benzocphenanthrene, 4) as \"Total Tons Benzo(c)phenanthrene\",
                ROUND(tons_benzoepyrene, 4) as \"Total Tons Benzo(e)pyrene\",
                ROUND(tons_benzghiperylene, 4) as \"Total Tons Benz(g_h_i)perylene\",
                ROUND(tons_benzokfluoranthene, 4) as \"Total Tons Benzo(k)fluoranthene\",
                ROUND(tons_benzofluoranthenes, 4) as \"Total Tons Benzofluoranthenes\",
                ROUND(tons_carbonyl_sulfide, 4) as \"Total Tons Carbonyl Sulfide\",
                ROUND(tons_chrysene, 4) as \"Total Tons Chrysene\",
                ROUND(tons_fluoranthene, 4) as \"Total Tons Fluoranthene\",
                ROUND(tons_formaldehyde, 4) as \"Total Tons Formaldehyde\",
                ROUND(tons_indeno123_cdpyrene, 4) as \"Total Tons Indeno(1_2_3-cd)pyrene\",
                ROUND(tons_methyl_chloride, 4) as \"Total Tons Chloride\",
                ROUND(tons_methylanthracene, 4) as \"Total Tons Methylanthracene\",
                ROUND(tons_methylbenzopyrenes, 4) as \"Total Tons Methylbenzopyrenes\",
                ROUND(tons_methylchrysene, 4) as \"Total Tons Methylchrysene\",
                ROUND(tons_methylpyrene_fluoranthene, 4) as \"Total Tons Methylpyrene_-Fluoranthene\",
                ROUND(tons_n_hexane, 4) as \"Total Tons N-Hexane\",
                ROUND(tons_omp_xylene, 4) as \"Total Tons O.m.p-xylene\",
                ROUND(tons_perylene, 4) as \"Total Tons Perylene\",
                ROUND(tons_phenanthrene, 4) as \"Total Tons Phenanthrene\",
                ROUND(tons_pyrene, 4) as \"Total Tons Pyrene\",
                ROUND(tons_toluene,  4) as \"Total Tons Toluene\"";
              }

              $pf_select = ", ROUND(tons_pm25, 4) as \"Total Tons PM 2.5\",
                ROUND(tons_nmoc, 4) as \"Total Tons NMOC\",
                ROUND(tons_co, 4) as \"Total Tons CO\",
                ROUND(tons_co2, 4) as \"Total Tons CO2\",
                ROUND(tons_ch4, 4) as \"Total Tons CH4\",
                ROUND(tons_nox, 4) as \"Total Tons NOX\",
                ROUND(tons_so2, 4) as \"Total Tons SO2\",
                ROUND(tons_nh3, 4) as \"Total Tons NH3\",
                ROUND(tons_bc, 4) as \"Total Tons Black Carbon\",
                ROUND(tons_oc, 4) as \"Total Tons Organic Carbon\",
                {$voc_block}
                ";
            }

            if ($potential_emissions) {
              $pte_select = ", (pte10.ef*pte10.tpa*pte10.acres) as \"Potential PM 10\", (pte25.ef*pte25.tpa*pte25.acres) as \"Potential PM 2.5\"";
              $pte_join = "LEFT JOIN (
                  SELECT b.burn_project_id, f.ef as ef, t.ton_per_acre as tpa,
                  COALESCE(b.black_acres_current, 0) as acres
                  FROM burn_projects b
                  JOIN fuel_types t ON(b.major_fbps_fuel = t.fuel_type_id)
                  JOIN fuels f ON(t.fuel_type_id = f.fuel_type_id)
                  WHERE f.emission_type_id = 1
                  AND f.is_active
                ) pte10 ON(pte10.burn_project_id = s1.burn_project_id)
                LEFT JOIN (
                  SELECT b.burn_project_id, f.ef as ef, t.ton_per_acre as tpa,
                  COALESCE(b.black_acres_current, 0) as acres
                  FROM burn_projects b
                  JOIN fuel_types t ON(b.major_fbps_fuel = t.fuel_type_id)
                  JOIN fuels f ON(t.fuel_type_id = f.fuel_type_id)
                  WHERE f.emission_type_id = 2
                  AND f.is_active
                ) pte25 ON(pte25.burn_project_id = s1.burn_project_id)";
            }

            $sql = "SELECT abbreviation as \"Agency\",
              identifier as \"Unit\", project_number as \"PIN\", project_name as \"Project Name\", airshed as \"Airshed\",
              county as \"County\", IF(de_minimis = 0, 'False', 'True') as \"De Minimis\",
              ROUND(REPLACE(SPLIT_STR(location, ',', 1), '(', ''), 4) as \"Latitude\",
              ROUND(REPLACE(SPLIT_STR(location, ',', 2), ')', ''), 4) as \"Longitude\",
              elevation_low as \"Lowest\", elevation_high as \"Highest\",
              project_acres as \"Planned Acres\", completion_year as \"Planned Completion Year\",
              black_acres_current as \"Current Yearly Acres\", burn_type as \"Burn Type\",
              number_of_piles as \"Number of Piles\",
              ignition_method as \"Ignition Method\", major_fbps_fuel as \"Fuel Model\",
              earliest_burn as \"Earliest Date (Form 5)\", duration as \"Days Planned\",
              pri_ert_name as \"Primary ERT Method\", primary_ert_pct as \"Primary ERT Percent\",
              sec_ert_name as \"Secondary ERT Method\", alternate_primary_ert as \"Primary ERT Other Description\",
              alternate_secondary_ert as \"Secondary ERT Other Description\", submitted_on as \"Last Form 2\",
              f3_forms as \"Form 3\", f3_last as \"Last Form 3\", f4_forms as \"Form 4\", f4_last as \"Last Form 4\",
              f5_forms as \"Form 5\", f5_last as \"Last Form 5\",  f9_forms as \"Form 9\", f9_last as \"Last Form 9\",
              ROUND(black_acres, 4) as \"Black Acres\", ROUND(tons_consumed, 4) as \"Tons Consumed\", ROUND(tons_pm10, 4) as \"Total Tons PM 10\"
              {$pf_select} {$pte_select}
              FROM (
                SELECT b.burn_project_id, a.agency, a.abbreviation, d.identifier, b.project_number,
                b.project_name, ai.name as airshed, ct.name as county, b.de_minimis, b.location,
                b.elevation_low, b.elevation_high,
                b.project_acres, b.completion_year, b.black_acres_current, bt.name as burn_type,
                b.number_of_piles, im.name as ignition_method, b.major_fbps_fuel as major_fbps_fuel,
                f5.first as earliest_burn, b.duration, pt.name as pri_ert_name, pb.primary_ert_pct,
                st.name as sec_ert_name, pb.alternate_primary_ert, pb.alternate_secondary_ert,
                b.submitted_on, f3.forms as f3_forms, f3.last as f3_last, f4.forms as f4_forms,
                f4.last as f4_last, f5.forms as f5_forms, f5.last as f5_last,
                f9.forms as f9_forms, f9.last as f9_last, SUM(af.black_acres) as black_acres,
                SUM(af.tons_consumed) as tons_consumed,
                SUM(pf.tons_pm10) as tons_pm10,
                SUM(pf.tons_13_butadiene) as tons_13_butadiene,
                SUM(pf.tons_2378_tcdd_teq) as tons_2378_tcdd_teq,
                SUM(pf.tons_acetaldehyde) as tons_acetaldehyde,
                SUM(pf.tons_acrolein) as tons_acrolein,
                SUM(pf.tons_anthracene) as tons_anthracene,
                SUM(pf.tons_benzaanthracene) as tons_benzaanthracene,
                SUM(pf.tons_benzene) as tons_benzene,
                SUM(pf.tons_benzoaflouranthene) as tons_benzoaflouranthene,
                SUM(pf.tons_benzoapyrene) as tons_benzoapyrene,
                SUM(pf.tons_benzocphenanthrene) as tons_benzocphenanthrene,
                SUM(pf.tons_benzoepyrene) as tons_benzoepyrene,
                SUM(pf.tons_benzghiperylene) as tons_benzghiperylene,
                SUM(pf.tons_benzokfluoranthene) as tons_benzokfluoranthene,
                SUM(pf.tons_benzofluoranthenes) as tons_benzofluoranthenes,
                SUM(pf.tons_carbonyl_sulfide) as tons_carbonyl_sulfide,
                SUM(pf.tons_chrysene) as tons_chrysene,
                SUM(pf.tons_fluoranthene) as tons_fluoranthene,
                SUM(pf.tons_formaldehyde) as tons_formaldehyde,
                SUM(pf.tons_indeno123_cdpyrene) as tons_indeno123_cdpyrene,
                SUM(pf.tons_methyl_chloride) as tons_methyl_chloride,
                SUM(pf.tons_methylanthracene) as tons_methylanthracene,
                SUM(pf.tons_methylbenzopyrenes) as tons_methylbenzopyrenes,
                SUM(pf.tons_methylchrysene) as tons_methylchrysene,
                SUM(pf.tons_methylpyrene_fluoranthene) as tons_methylpyrene_fluoranthene,
                SUM(pf.tons_n_hexane) as tons_n_hexane,
                SUM(pf.tons_omp_xylene) as tons_omp_xylene,
                SUM(pf.tons_perylene) as tons_perylene,
                SUM(pf.tons_phenanthrene) as tons_phenanthrene,
                SUM(pf.tons_pyrene) as tons_pyrene,
                SUM(pf.tons_toluene) as tons_toluene,
                SUM(pf.tons_pm25) as tons_pm25,
                SUM(pf.tons_nmoc) as tons_nmoc,
                SUM(pf.tons_co) as tons_co,
                SUM(pf.tons_co2) as tons_co2,
                SUM(pf.tons_ch4) as tons_ch4,
                SUM(pf.tons_nox) as tons_nox,
                SUM(pf.tons_so2) as tons_so2,
                SUM(pf.tons_nh3) as tons_nh3,
                SUM(pf.tons_bc) as tons_bc,
                SUM(pf.tons_oc) as tons_oc
                FROM (
                  SELECT burn_project_id, pre_burn_id, YEAR(start_date) as year
                  FROM burns
                  WHERE status_id = 5
                  AND (
                    start_date BETWEEN '{$start_date}' AND '{$end_date}'
                    OR end_date BETWEEN '{$start_date}' AND '{$end_date}'
                  )
                  GROUP BY burn_project_id, pre_burn_id, YEAR(start_date)
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
                ) pb ON(br.pre_burn_id = pb.pre_burn_id)
                LEFT JOIN airsheds ai ON(ai.airshed_id = b.airshed_id)
                LEFT JOIN counties ct ON(ct.county_id = b.county)
                LEFT JOIN burn_pile_types bt ON(b.burn_type = bt.burn_pile_type_id)
                LEFT JOIN ignition_methods im ON(b.ignition_method = im.ignition_method_id)
                LEFT JOIN emission_reduction_techniques pt ON(pb.primary_ert_id = pt.emission_reduction_technique_id)
                LEFT JOIN emission_reduction_techniques st ON(pb.secondary_ert_id = st.emission_reduction_technique_id)
                LEFT JOIN yearly_project_fuels_wide pf ON(pf.burn_project_id = b.burn_project_id AND pf.year = br.year)
                LEFT JOIN (
                  SELECT burn_project_id, year,
                  AVG(black_acres) AS black_acres,
                  AVG(tons_consumed) AS tons_consumed
                  FROM yearly_project_fuels
                  GROUP BY burn_project_id, YEAR
                ) af ON(af.burn_project_id = br.burn_project_id AND af.year = br.year)
                LEFT JOIN (
                  SELECT burn_project_id,
                  YEAR(submitted_on) AS YEAR,
                  COUNT(pre_burn_id) AS forms,
                  MAX(submitted_on) AS LAST
                  FROM pre_burns
                  WHERE submitted_on BETWEEN '{$start_date}' AND '{$end_date}'
                  GROUP BY burn_project_id, YEAR(submitted_on)
                ) f3 ON(f3.burn_project_id = pb.burn_project_id AND f3.year = br.year)
                LEFT JOIN (
                  SELECT burn_project_id,
                  YEAR(submitted_on) AS YEAR,
                  COUNT(burn_id) AS forms,
                  MAX(submitted_on) AS LAST
                  FROM burns
                  WHERE submitted_on BETWEEN '{$start_date}' AND '{$end_date}'
                  GROUP BY burn_project_id, YEAR(submitted_on)
                ) f4 ON(f4.burn_project_id = pb.burn_project_id AND f4.year = br.year)
                LEFT JOIN (
                  SELECT burn_project_id,
                  YEAR(submitted_on) AS YEAR,
                  COUNT(accomplishment_id) AS forms,
                  MAX(submitted_on) AS LAST,
                  MIN(submitted_on) AS FIRST
                  FROM accomplishments
                  WHERE submitted_on BETWEEN '{$start_date}' AND '{$end_date}'
                  GROUP BY burn_project_id, YEAR(submitted_on)
                ) f5 ON(f5.burn_project_id = pb.burn_project_id AND f5.year = br.year)
                LEFT JOIN (
                  SELECT burn_project_id,
                  YEAR(submitted_on) AS YEAR,
                  COUNT(documentation_id) AS forms,
                  MAX(submitted_on) AS LAST
                  FROM documentation
                  WHERE submitted_on BETWEEN '{$start_date}' AND '{$end_date}'
                  GROUP BY burn_project_id, YEAR(submitted_on)
                ) f9 ON(f9.burn_project_id = pb.burn_project_id AND f9.year = br.year)
                WHERE br.year IS NOT NULL
                {$agency_cond}
                {$county_cond}
                GROUP BY b.burn_project_id, a.agency, a.abbreviation, d.identifier, b.project_number, b.project_name, ai.name,
                ct.name, b.de_minimis, b.location, b.elevation_low, b.elevation_high,
                b.project_acres, b.completion_year, b.black_acres_current, bt.name,
                b.number_of_piles, im.name, b.major_fbps_fuel,
                f5.first, b.duration, pt.name, pb.primary_ert_pct,
                st.name, pb.alternate_primary_ert, pb.alternate_secondary_ert,
                b.submitted_on, f3.forms, f3.last, f4.forms,
                f4.last, f5.forms, f5.last, f9.forms, f9.last
              ) s1
              {$pte_join}
              ORDER BY agency, identifier, project_number;";

            error_log("Project Summary Report (SQL):
              " . $sql, 0);

            $csv = get_csv($sql);
            break;
        //case 2:
        //    /**
        //     *  Daily Burn Report
        //     */
//
        //    $sql = "SELECT db.ignition_date as \"Ignition Date\", a.agency as \"Agency\", d.district as \"District\", b.burn_number as \"Burn Number\",
        //        u.full_name as \"Submitted By\", db.submitted_on as \"Submitted On\", db.acres_treated as \"Acres Treated\",
        //        db.location as \"Location\", db.sm_unit_number as \"Smoke Management Unit Number\", IF(db.acres_lined = 1, 'True', 'False') as \"Acres Lined\",
        //        db.non_lined_max_area as \"Maximum Burn Acres\", IF(db.multi_day = 1, 'True', 'False') as \"Multi Day\",
        //        db.daytime_plume_behavior as \"Expected Daytime Plume\", db.diurnal_smoke_behavior as \"Expected Diurnal Smoke\",
        //        db.sensitive_impact as \"Sensitive Impact\", db.comments as \"Comments\", db.contact_name as \"Contact Name\", db.contact_number as \"Contact Number\",
        //        FROM daily_burns db
        //        JOIN burn_plans b ON(db.burn_plan_id = b.burn_plan_id)
        //        JOIN districts d ON (b.district_id = d.district_id)
        //        JOIN agencies a ON (d.agency_id = a.agency_id)
        //        JOIN users u ON (b.submitted_by = u.user_id)
        //        WHERE b.submitted_on BETWEEN '$start_date' AND '$end_date'
        //        AND a.agency_id $agency_cond;";
//
        //    $csv = get_csv($sql);
        //    break;
        //case 3:
        //    /**
        //     *  Accomplishment Report
        //     */
//
        //    $sql = "SELECT ac.ignition_date as \"Ignition Date\", a.agency as \"Agency\", d.district as \"District\", b.burn_number as \"Burn Number\",
        //        u.full_name as \"Completed By\", ac.completed_on as \"Completed On\", ac.contact_name \"Contact Name\",
        //        ac.contact_number as \"Contact Number\", db.acres_treated as \"Daily Burn Acres Requested\", ac.acres_treated as \"Acres Treated\", ac.acres_burned as \"Acres Burned\",
        //        ac.acres_ert_used as \"Acres ERT Used\", ac.location as \"Location\", ac.burn_duration as \"Burn Duration\",
        //        ac.ignition_duration as \"Ignition Duration\", ac.dead_fuel_mstr_10hr as \"Dead Fuel Moisture 10 Hour\",
        //        ac.dead_fuel_mstr_1000hr as \"Dead Fuel Moisture 1000 Hour\", ac.duff_fuel_mstr as \"Duff Fuel Moisture\",
        //        fm.name as \"Fuel Moisture Method\", ac.days_since_rain as \"Days Since Rain\", ac.snow_off as \"Snow Off\",
        //        (SELECT GROUP_CONCAT(a.name SEPARATOR '; ') FROM accomplishment_erts ba JOIN emission_reduction_techniques a ON(ba.emission_reduction_technique_id = a.emission_reduction_technique_id) WHERE ba.accomplishment_id = ac.accomplishment_id) as \"Emission Reduction Techniques\",
        //        ac.diurnal_plume_char as \"Diurnal Plume Characteristics\", ac.remarks as \"Remarks\",
        //        f.name as \"Primary Fuel Type\", m.name as \"NFDRS Fuel Model\", bs.harvest_date as \"Harvest Date\",
        //        df.name as \"Primary Duff Type\", bs.sound_rotten_0_025, bs.sound_rotten_026_1, bs.sound_rotten_1_3,
        //        bs.sound_3_9, bs.sound_9_20, bs.sound_20_greater, bs.rotten_3_greater, bs.stump_20_greater, bs.shrub_brush,
        //        bs.grass_herb,bs.avg_litter_depth,bs.avg_duff_depth,
        //        ps.piles_per_acre as \"Piles per Acre\", ps.tons_piles_per_acre as \"Tons Piles per Acre\", ps.soil_pct as \"Soil in Piles\",
        //        sp1.name as \"Primary Species\", ps.pri_species_pct as \"Primary Species (%)\",
        //        sp2.name as \"Secondary Species\", ps.sec_species_id as \"Secondary Species (%)\",
        //        q.name as \"Quality\", ps.dim_width as \"Width\", ps.dim_height as \"Height\", ps.dim_length as \"Length\",
        //        r.name as \"Packing Ratio\"
        //        FROM accomplishments ac
        //        LEFT JOIN accomplishment_piled_slash ps ON(ac.accomplishment_id = ps.accomplishment_id)
        //        LEFT JOIN accomplishment_broadcast bs ON(ac.accomplishment_id = bs.accomplishment_id)
        //        LEFT JOIN species_types sp1 ON (sp1.species_type_id = ps.pri_species_id)
        //        LEFT JOIN species_types sp2 ON (sp2.species_type_id = ps.sec_species_id)
        //        LEFT JOIN qualities q ON (q.quality_id = ps.quality_id)
        //        LEFT JOIN packing_ratios r ON(r.packing_ratio_id = ps.packing_ratio_id)
        //        LEFT JOIN fuel_types f ON(f.fuel_type_id = bs.fuel_type_id)
        //        LEFT JOIN nfdrs_fuel_models m ON(m.nfdrs_fuel_model_id = bs.nfdrs_fuel_type)
        //        LEFT JOIN duff_types df ON(df.duff_type_id = bs.duff_type_id)
        //        JOIN daily_burns db ON (ac.daily_burn_id = db.daily_burn_id)
        //        JOIN burn_plans b ON(db.burn_plan_id = b.burn_plan_id)
        //        JOIN districts d ON (b.district_id = d.district_id)
        //        JOIN agencies a ON (d.agency_id = a.agency_id)
        //        JOIN users u ON (ac.completed_by = u.user_id)
        //        LEFT JOIN fuel_moisture_methods fm ON (fm.fuel_moisture_method_id = ac.fuel_mstr_method_id)
        //        WHERE b.submitted_on BETWEEN '$start_date' AND '$end_date'
        //        AND a.agency_id $agency_cond;";
//
        //    $csv = get_csv($sql);
        //    break;
        //case 4:
        //    /**
        //     *  Summary Report
        //     */
//
        //    $sql = "SELECT s.agency as \"Agency\", s.daily_burns as \"Total Daily Requests\", s.daily_acres as \"Total Acres Requested\",
        //        sq.accomplishments as \"Total Accomplished Burns\", sq.accomp_acres as \"To Accomplished Acres\"
        //        FROM
        //        (SELECT ag.agency_id, ag.agency, COUNT(db.daily_burn_id) as daily_burns, SUM(db.acres_treated) as daily_acres
        //        FROM daily_burns db
        //        JOIN districts d ON(db.district_id = d.district_id)
        //        JOIN agencies ag ON(d.agency_id = ag.agency_id)
        //        WHERE db.status_id > 1
        //        AND db.submitted_on BETWEEN '$start_date' AND '$end_date'
        //        AND ag.agency_id $agency_cond
        //        GROUP BY ag.agency_id
        //        ) s
        //        JOIN (SELECT ag.agency_id, ag.agency, COUNT(a.accomplishment_id) as accomplishments, SUM(a.acres_treated) as accomp_acres
        //        FROM accomplishments a
        //        JOIN districts d ON(a.district_id = d.district_id)
        //        JOIN agencies ag ON(d.agency_id = ag.agency_id)
        //        WHERE a.status_id > 1
        //        AND a.completed_on BETWEEN '$start_date' AND '$end_date'
        //        AND ag.agency_id $agency_cond
        //        GROUP BY ag.agency_id) sq ON(s.agency_id = sq.agency_id);";
//
        //    $csv = get_csv($sql);
        //    break;
    }

    /**
     *  Construct the File Report & Download
     */

    $file_dir = "/var/reports/";
    $file_name = "utah_report_".uniqid();
    $file_path = $file_dir.$file_name.'.csv';
    file_put_contents($file_path, $csv);

    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename='.urlencode(basename($file_path)));
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file_path));

    ob_clean();
    flush();
    readfile($file_path);
    exit;
} else {
    echo $header;
}

?>
    <div class="container">
        <div class="row">
            <div class="col-sm-12">
                <h3>Reviewer Reports</h3>
                <hr>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="form-block">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6">
                <?php
                    //$types = array('Burn Projects', 'Pre-Burns', 'Accomplishments', 'Agency Summary');
                    $types = array('Annual Project Summary', 'Cumulative Project Summary');

                    $user_id = $_SESSION['user']['id'];

                    if ($permissions['read']['admin']) {
                        $sql = "SELECT agency_id as id, agency FROM agencies ORDER BY agency";
                    } else {
                        $sql = "SELECT agency_id as id, agency FROM agencies WHERE agency_id IN(SELECT agency_id FROM users WHERE user_id = {$user_id}) ORDER BY agency ";
                    }

                    $csql = "SELECT county_id as id, name FROM counties ORDER BY name;";

                    $ctls = array(
                        'type'=>array('type'=>'combobox','array'=>$types,'label'=>'Report Type'),
                        'start_date'=>array('type'=>'date','label'=>'Start Date'),
                        'end_date'=>array('type'=>'date','label'=>'End Date'),
                        'select_all'=>array('type'=>'checkbox','label'=>'Select All'),
                        'agency'=>array('type'=>'combobox','multiselect'=>true,'sql'=>$sql,'label'=>'Agency(s)','display'=>'agency','fcol'=>'id'),
                        'county'=>array('type'=>'combobox','multiselect'=>true,'sql'=>$csql,'label'=>'County(s)','display'=>'name','fcol'=>'id'),
                        'potential_emissions'=>array('type'=>'boolean','label'=>'Show Potential to Emit (Form 3 or Form 2 Total Black Acres)'),
                        'all_emissions'=>array('type'=>'boolean','label'=>'Show All Emission Types'),
                        'sum_voc'=>array('type'=>'boolean','label'=>'Sum VOC Emissions')
                    );

                    echo mkForm(array('controls'=>$ctls,'submit'=>'Download','id'=>'report_form','suppress_legend'=>true,'target'=>'_blank'));
                ?>
                <script type="text/javascript">
                    $('[name="my[select_all]"]').click(function() {
                        var showOptions = false;

                        if($(this).is(':checked')) {
                            var showOptions = true;
                        }

                        $('[name="my[agency][]"] option').prop('selected', showOptions);
                        $('[name="my[county][]"] option').prop('selected', showOptions);
                    });
                </script>
            </div>
            <div class="col-sm-6">
                <!--<a href="/pdf/daily.php?preapproved=true"><button class="btn btn-default">Final Approval PDF</button></a>
                <br><br>
                <p>View the pre-approved burns pending final approval.</p>-->
            </div>
        </div>
    </div>
</body>
