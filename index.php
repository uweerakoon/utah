<?php
include 'checklogin.php';
echo checklogin(array('title'=>'Smoke / Utah.gov','public'=>true));
//THIS IS THE PRODUCTION PAGE
//$weather = new \Page\Weather($db);
$content = new \Page\Index($db);

?>
<style type="text/css">
    body {
        padding-top: 64px;
    }
</style>

<div class="container">
    <div class="row" style="min-height: 256px;">
        <div class="col-sm-12 col-md-8">
            <div id="bigLogin">
                
                        <!-- <h3><strong><a href="/login.php">Login Here to Access Online Forms</a><strong></h3>  -->
                        <!-- <hr> -->
               
                
            </div>
            <div id="general">
                <h3>Links</h3>
                <hr>
                <p class="">
                	<h4>Weather</h4>
                	<a href="https://www.weather.gov/slc/ClearingIndex#tab-2"><span class="glyphicon glyphicon-cloud"></span> NWS Clearing Index</a><br>
                	<a href="https://gacc.nifc.gov/gbcc/outlooks.php"><span class="glyphicon glyphicon-cloud"></span> GACC Outlooks</a><br>
                	<a href="http://air.utah.gov/currentconditions.php"><span class="glyphicon glyphicon-cloud"></span> Utah DAQ Current Conditions</a><br>
                	<a href="http://www.wrh.noaa.gov/map/?obs=true&wfo=slc&basemap=OpenStreetMap&boundaries=true,false&obs_popup=true"><span class="glyphicon glyphicon-cloud"></span> RAWS and other weather stations</a><br>
                	<a href="http://mesowest.utah.edu/cgi-bin/droman/mesomap.cgi?state=UT&rawsflag=3"><span class="glyphicon glyphicon-cloud"></span> MESOWEST current/historical weather data</a><br>
                	<a href="https://www.wcc.nrcs.usda.gov/webmap/#version=80.1&elements=W,R&networks=!&states=!&counties=!&hucs=&minElevation=&maxElevation=&elementSelectType=all&activeOnly=true&activeForecastPointsOnly=false&hucLabels=false&hucParameterLabels=false&stationLabels=&overlays=&hucOverlays=&mode=data&openSections=dataElement,parameter,date,basin,elements,location,networks&controlsOpen=true&popup=&popupMulti=&base=esriNgwm&displayType=station&basinType=6&dataElement=SNWD&parameter=OBS&frequency=DAILY&duration=I&customDuration=&dayPart=E&year=2018&month=4&day=25&monthPart=E&forecastPubMonth=4&forecastPubDay=1&forecastExceedance=50&seqColor=1&divColor=3&scaleType=D&scaleMin=&scaleMax=&referencePeriodType=POR&referenceBegin=1981&referenceEnd=2010&minimumYears=20&hucAssociations=true&lat=40.170&lon=-109.072&zoom=6.0"><span class="glyphicon glyphicon-cloud"></span> SNOTEL Map</a><br>

                	<h4>Emissions</h4>
                    <a href="https://depts.washington.edu/nwfire/piles/"><span class="glyphicon glyphicon-leaf"></span> Piled Fuels Biomass and Emissions Calculator</a><br>
                    <a href="https://playground.airfire.org/login.php?next=/index.php"><span class="glyphicon glyphicon-leaf"></span> BlueSky Playground v2</a><br>
                	<a href="https://www.firelab.org/document/fofem-files"><span class="glyphicon glyphicon-leaf"></span> FOFEM Model Software</a><br>
                	<a href="https://www.fs.usda.gov/ccrc/tools/fuel-fire-tools-fft"><span class="glyphicon glyphicon-leaf"></span> Fuel and Fire Tools (FFT)</a><br>
                	<a href="https://tools.airfire.org/"><span class="glyphicon glyphicon-leaf"></span> Wildland Fire Air Quality Tools</a><br>
                	
                	<h4>Intel</h4>
                	<a href="https://www.nifc.gov/nicc/sitreprt.pdf"><span class="glyphicon glyphicon-fire"></span> Sit Report</a><br>
                	<a href="http://utahfireinfobox.com/active-wildfires/"><span class="glyphicon glyphicon-fire"></span> Utah Fire Info</a><br>
                	<a href="https://inciweb.nwcg.gov/"><span class="glyphicon glyphicon-fire"></span> Inciweb</a><br>
                	<a href="https://weather.cod.edu/satrad/exper/?parms=subregional-Cen_Rockies-truecolor-48-0-100-1&checked=map&colorbar=undefined"><span class="glyphicon glyphicon-fire"></span> GOES 16 Satellite Imagery</a><br>
                	<a href="http://udottraffic.utah.gov/"><span class="glyphicon glyphicon-fire"></span> UDOT Webcams</a><br>
                    

                    <h4>Policy</h4>
                    <a href="/static/pdf/SMP011606_Final.pdf"><span class="glyphicon glyphicon-file"></span> Utah Smoke Management Plan</a><br>
         
                    <a href="https://gacc.nifc.gov/gbcc/smoke.php"><span class="glyphicon glyphicon-file"></span> GACC Smoke Page</a> (Including Utah's paper forms)<br>
                	<a href="https://www.nwcg.gov/publications/420-2"><span class="glyphicon glyphicon-file"></span> NWCG Smoke Management Guide</a><br>

                    <h4>Maps</h4>
                    <a href="/static/Airsheds.pdf"><span class="glyphicon glyphicon-globe"></span> Utah Airshed Map</a><br>
                    <a href="/static/kmz/utah_airshed.kmz"><span class="glyphicon glyphicon-globe"></span> Airshed Map KMZ</a><br>
                    <a href="/static/kmz/utah_airshedLabel2.kmz"><span class="glyphicon glyphicon-globe"></span> Airshed Labels KMZ</a><br>
                    <a href="/static/pdf/NONATTAINMENT_MAP(2013).pdf"><span class="glyphicon glyphicon-globe"></span> Utah Nonattainment Map</a><br>
                    <a href="/static/npsmap_basemap_classi_11x17.jpg"><span class="glyphicon glyphicon-globe"></span> Class One Areas Map</a><br>
                   <!--  <a href="/static/pdf/American_Indian_Class_1_Lands.pdf"><span class="glyphicon glyphicon-file"></span> American Indian Class One Lands</a><br> -->
                </p>
            </div>
            <br>
            <!-- <div id="2015-forms">
                <h4>Paper Forms</h4>
                <hr>
                <p class="">
                    <a href="/static/forms/2015/2015_SM_Form_2.pdf"><span class="glyphicon glyphicon-file"></span> Form 2: UT Annual Burn Schedule</a><br>
                    <a href="/static/forms/2015/2015_SM_Form_3.pdf"><span class="glyphicon glyphicon-file"></span> Form 3: Pre-Burn Information</a><br>
                    <a href="/static/forms/2015/2015_SM_Form_4.pdf"><span class="glyphicon glyphicon-file"></span> Form 4: Burn Request</a><br>
                    <a href="/static/forms/2015/2015_SM_Form_5.pdf"><span class="glyphicon glyphicon-file"></span> Form 5: Daily Emissions Record</a><br>
                    <a href="/static/forms/2015/2015_SM_Form_6.pdf"><span class="glyphicon glyphicon-file"></span> Form 6: Hourly Plume Observation</a><br>
                    <a href="/static/forms/2015/2015_SM_Form_7.pdf"><span class="glyphicon glyphicon-file"></span> Form 7: Changes or Corrections</a><br>
                    <a href="/static/forms/2015/2015_SM_Form_8.pdf"><span class="glyphicon glyphicon-file"></span> Form 8: Carryover and Dropped Projects</a><br>
                    <a href="/static/forms/2015/2015_SM_Form_9.pdf"><span class="glyphicon glyphicon-file"></span> Form 9: Burn Documentation (CI &lt; 500)</a><br>
                </p>
            </div>      -->       
        </div>
        <div class="col-sm-12 col-md-4">
            <script type="text/javascript">
                $('#myTab a').click(function (e) {
                    e.preventDefault()
                    $(this).tab('show')
                })
            </script>

            <div id="News">
                <h3>News</h3>
                <hr>
                No news at this time

            <!-- <div id="messages" role="tabpanel">
                <ul class="nav nav-tabs" role="tablist">
                    <li role="presentation" class="active"><a href="#news" aria-controls="news" role="tab" data-toggle="tab">News</a></li>
                    <li role="presentation"><a href="#current" aria-controls="current" role="tab" data-toggle="tab">Current</a></li>
                </ul>
                <div class="tab-content">
                    <div role="tabpanel" class="tab-pane active" id="news">
                        <br>
                        Paul will be on leave from 8/3/18 until 8/13/18.  In the meantime please contact Phil Kacirek for your Utah smoke management needs at <a href="mailto:pkacirek@fs.fed.us">pkacirek@fs.fed.us</a>
                        No news at this time
                        <?php echo $content->display(); ?>
                    </div>
                    <div role="tabpanel" class="tab-pane" id="current">
                        <br>
                        <?php echo $content->displayDaily(); ?>
                    </div>
                </div>
            
            </div> -->

            <div id="contact"> 
                <h3>Contact</h3>
                <hr>
                <address>
                    <strong>Paul Corrigan</strong><br>
                    <strong>Division of Air Quality</strong><br>
                    <a style="color: #000" href="https://goo.gl/maps/KVW8z"><span class="glyphicon glyphicon-map-marker"></span> 195 North 1950 West<br>
                    Salt Lake City, UT 84114-4820<br></a>
                    Attn: Smoke Coordinator
                </address>
                <p>
                    <a href=mailto:pcorrigan@fs.fed.us?cc=paulcorrigan@utah.gov"><span class="glyphicon glyphicon-envelope"></span> Click to Email</a><br>
                    Or send mail<br>
                    To:<a href="mailto:pcorrigan@fs.fed.us">pcorrigan@fs.fed.us</a> and<br>
                    Cc:<a href="mailto:paulcorrigan@utah.gov">paulcorrigan@utah.gov</a>
                </p>
                <address>
                    Phone: 801.440.1350
                    
                </address>
            </div>
            <div id="bulletin">
            	<h3>Bulletins</h3>
            	<hr>
            	<a href="/static/SBSept2018.pdf"><span class="glyphicon glyphicon-paperclip"></span> Sep 2018 - ERT Overview</a><br> 
            	<a href="/static/SBFeb2018.pdf"><span class="glyphicon glyphicon-paperclip"></span> Feb 2018 - How to report pile emissions</a><br> 
            </div>
        <div>
            
        </div>    
        </div>
    </div>
</div>
