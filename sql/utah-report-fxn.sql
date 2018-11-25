-- Reports Views

CREATE VIEW project_fuels
AS SELECT a.burn_project_id, SUM(f.total_tons) as tons_consumed, SUM(f.black_acres) as black_acres, SUM(f.tons_emitted) as tons_emitted,  fu.emission_type_id
FROM accomplishments a
JOIN accomplishment_fuels f ON (f.accomplishment_id = a.accomplishment_id)
JOIN fuels fu ON (f.fuel_id = fu.fuel_id)
GROUP BY a.burn_project_id, emission_type_id;

CREATE VIEW project_fuels_wide
AS SELECT p.burn_project_id,
    MAX(CASE WHEN p.emission_type_id=1 THEN p.tons_emitted END) as tons_pm10,
    MAX(CASE WHEN p.emission_type_id=2 THEN p.tons_emitted END) as tons_pm25,
    MAX(CASE WHEN p.emission_type_id=3 THEN p.tons_emitted END) as tons_nmoc,
    MAX(CASE WHEN p.emission_type_id=4 THEN p.tons_emitted END) as tons_co,
    MAX(CASE WHEN p.emission_type_id=5 THEN p.tons_emitted END) as tons_co2,
    MAX(CASE WHEN p.emission_type_id=6 THEN p.tons_emitted END) as tons_ch4,
    MAX(CASE WHEN p.emission_type_id=7 THEN p.tons_emitted END) as tons_nox,
    MAX(CASE WHEN p.emission_type_id=8 THEN p.tons_emitted END) as tons_so2,
    MAX(CASE WHEN p.emission_type_id=9 THEN p.tons_emitted END) as tons_nh3,
    MAX(CASE WHEN p.emission_type_id=10 THEN p.tons_emitted END) as tons_bc,
    MAX(CASE WHEN p.emission_type_id=11 THEN p.tons_emitted END) as tons_oc,
    MAX(CASE WHEN p.emission_type_id=12 THEN p.tons_emitted END) as tons_13_butadiene,
    MAX(CASE WHEN p.emission_type_id=13 THEN p.tons_emitted END) as tons_2378_tcdd_teq,
    MAX(CASE WHEN p.emission_type_id=14 THEN p.tons_emitted END) as tons_acetaldehyde,
    MAX(CASE WHEN p.emission_type_id=15 THEN p.tons_emitted END) as tons_acrolein,
    MAX(CASE WHEN p.emission_type_id=16 THEN p.tons_emitted END) as tons_anthracene,
    MAX(CASE WHEN p.emission_type_id=17 THEN p.tons_emitted END) as tons_benzaanthracene,
    MAX(CASE WHEN p.emission_type_id=18 THEN p.tons_emitted END) as tons_benzene,
    MAX(CASE WHEN p.emission_type_id=19 THEN p.tons_emitted END) as tons_benzoaflouranthene,
    MAX(CASE WHEN p.emission_type_id=20 THEN p.tons_emitted END) as tons_benzoapyrene,
    MAX(CASE WHEN p.emission_type_id=21 THEN p.tons_emitted END) as tons_benzocphenanthrene,
    MAX(CASE WHEN p.emission_type_id=22 THEN p.tons_emitted END) as tons_benzoepyrene,
    MAX(CASE WHEN p.emission_type_id=23 THEN p.tons_emitted END) as tons_benzghiperylene,
    MAX(CASE WHEN p.emission_type_id=24 THEN p.tons_emitted END) as tons_benzokfluoranthene,
    MAX(CASE WHEN p.emission_type_id=25 THEN p.tons_emitted END) as tons_benzofluoranthenes,
    MAX(CASE WHEN p.emission_type_id=26 THEN p.tons_emitted END) as tons_carbonyl_sulfide,
    MAX(CASE WHEN p.emission_type_id=27 THEN p.tons_emitted END) as tons_chrysene,
    MAX(CASE WHEN p.emission_type_id=28 THEN p.tons_emitted END) as tons_fluoranthene,
    MAX(CASE WHEN p.emission_type_id=29 THEN p.tons_emitted END) as tons_formaldehyde,
    MAX(CASE WHEN p.emission_type_id=30 THEN p.tons_emitted END) as tons_indeno123_cdpyrene,
    MAX(CASE WHEN p.emission_type_id=31 THEN p.tons_emitted END) as tons_methyl_chloride,
    MAX(CASE WHEN p.emission_type_id=32 THEN p.tons_emitted END) as tons_methylanthracene,
    MAX(CASE WHEN p.emission_type_id=33 THEN p.tons_emitted END) as tons_methylbenzopyrenes,
    MAX(CASE WHEN p.emission_type_id=34 THEN p.tons_emitted END) as tons_methylchrysene,
    MAX(CASE WHEN p.emission_type_id=35 THEN p.tons_emitted END) as tons_methylpyrene_fluoranthene,
    MAX(CASE WHEN p.emission_type_id=36 THEN p.tons_emitted END) as tons_n_hexane,
    MAX(CASE WHEN p.emission_type_id=37 THEN p.tons_emitted END) as tons_omp_xylene,
    MAX(CASE WHEN p.emission_type_id=38 THEN p.tons_emitted END) as tons_perylene,
    MAX(CASE WHEN p.emission_type_id=39 THEN p.tons_emitted END) as tons_phenanthrene,
    MAX(CASE WHEN p.emission_type_id=40 THEN p.tons_emitted END) as tons_pyrene,
    MAX(CASE WHEN p.emission_type_id=41 THEN p.tons_emitted END) as tons_toluene
FROM project_fuels p
GROUP BY p.burn_project_id;




-- Annual Reports Views

CREATE VIEW yearly_project_fuels
AS SELECT a.burn_project_id, YEAR(submitted_on) as year, SUM(f.total_tons) as tons_consumed, SUM(f.black_acres) as black_acres, SUM(f.tons_emitted) as tons_emitted,  fu.emission_type_id
FROM accomplishments a
JOIN accomplishment_fuels f ON (f.accomplishment_id = a.accomplishment_id)
JOIN fuels fu ON (f.fuel_id = fu.fuel_id)
WHERE submitted_on IS NOT NULL
GROUP BY a.burn_project_id, YEAR(submitted_on), emission_type_id;

CREATE VIEW yearly_project_fuels_wide
AS SELECT p.burn_project_id, p.year,
    MAX(CASE WHEN p.emission_type_id=1 THEN p.tons_emitted END) as tons_pm10,
    MAX(CASE WHEN p.emission_type_id=2 THEN p.tons_emitted END) as tons_pm25,
    MAX(CASE WHEN p.emission_type_id=3 THEN p.tons_emitted END) as tons_nmoc,
    MAX(CASE WHEN p.emission_type_id=4 THEN p.tons_emitted END) as tons_co,
    MAX(CASE WHEN p.emission_type_id=5 THEN p.tons_emitted END) as tons_co2,
    MAX(CASE WHEN p.emission_type_id=6 THEN p.tons_emitted END) as tons_ch4,
    MAX(CASE WHEN p.emission_type_id=7 THEN p.tons_emitted END) as tons_nox,
    MAX(CASE WHEN p.emission_type_id=8 THEN p.tons_emitted END) as tons_so2,
    MAX(CASE WHEN p.emission_type_id=9 THEN p.tons_emitted END) as tons_nh3,
    MAX(CASE WHEN p.emission_type_id=10 THEN p.tons_emitted END) as tons_bc,
    MAX(CASE WHEN p.emission_type_id=11 THEN p.tons_emitted END) as tons_oc,
    MAX(CASE WHEN p.emission_type_id=12 THEN p.tons_emitted END) as tons_13_butadiene,
    MAX(CASE WHEN p.emission_type_id=13 THEN p.tons_emitted END) as tons_2378_tcdd_teq,
    MAX(CASE WHEN p.emission_type_id=14 THEN p.tons_emitted END) as tons_acetaldehyde,
    MAX(CASE WHEN p.emission_type_id=15 THEN p.tons_emitted END) as tons_acrolein,
    MAX(CASE WHEN p.emission_type_id=16 THEN p.tons_emitted END) as tons_anthracene,
    MAX(CASE WHEN p.emission_type_id=17 THEN p.tons_emitted END) as tons_benzaanthracene,
    MAX(CASE WHEN p.emission_type_id=18 THEN p.tons_emitted END) as tons_benzene,
    MAX(CASE WHEN p.emission_type_id=19 THEN p.tons_emitted END) as tons_benzoaflouranthene,
    MAX(CASE WHEN p.emission_type_id=20 THEN p.tons_emitted END) as tons_benzoapyrene,
    MAX(CASE WHEN p.emission_type_id=21 THEN p.tons_emitted END) as tons_benzocphenanthrene,
    MAX(CASE WHEN p.emission_type_id=22 THEN p.tons_emitted END) as tons_benzoepyrene,
    MAX(CASE WHEN p.emission_type_id=23 THEN p.tons_emitted END) as tons_benzghiperylene,
    MAX(CASE WHEN p.emission_type_id=24 THEN p.tons_emitted END) as tons_benzokfluoranthene,
    MAX(CASE WHEN p.emission_type_id=25 THEN p.tons_emitted END) as tons_benzofluoranthenes,
    MAX(CASE WHEN p.emission_type_id=26 THEN p.tons_emitted END) as tons_carbonyl_sulfide,
    MAX(CASE WHEN p.emission_type_id=27 THEN p.tons_emitted END) as tons_chrysene,
    MAX(CASE WHEN p.emission_type_id=28 THEN p.tons_emitted END) as tons_fluoranthene,
    MAX(CASE WHEN p.emission_type_id=29 THEN p.tons_emitted END) as tons_formaldehyde,
    MAX(CASE WHEN p.emission_type_id=30 THEN p.tons_emitted END) as tons_indeno123_cdpyrene,
    MAX(CASE WHEN p.emission_type_id=31 THEN p.tons_emitted END) as tons_methyl_chloride,
    MAX(CASE WHEN p.emission_type_id=32 THEN p.tons_emitted END) as tons_methylanthracene,
    MAX(CASE WHEN p.emission_type_id=33 THEN p.tons_emitted END) as tons_methylbenzopyrenes,
    MAX(CASE WHEN p.emission_type_id=34 THEN p.tons_emitted END) as tons_methylchrysene,
    MAX(CASE WHEN p.emission_type_id=35 THEN p.tons_emitted END) as tons_methylpyrene_fluoranthene,
    MAX(CASE WHEN p.emission_type_id=36 THEN p.tons_emitted END) as tons_n_hexane,
    MAX(CASE WHEN p.emission_type_id=37 THEN p.tons_emitted END) as tons_omp_xylene,
    MAX(CASE WHEN p.emission_type_id=38 THEN p.tons_emitted END) as tons_perylene,
    MAX(CASE WHEN p.emission_type_id=39 THEN p.tons_emitted END) as tons_phenanthrene,
    MAX(CASE WHEN p.emission_type_id=40 THEN p.tons_emitted END) as tons_pyrene,
    MAX(CASE WHEN p.emission_type_id=41 THEN p.tons_emitted END) as tons_toluene
FROM yearly_project_fuels p
GROUP BY p.burn_project_id, p.year;
