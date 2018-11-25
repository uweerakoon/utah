/*!
 *      Utah.gov KML Manager Class
 *      KML Control and Toggle functions
 */

function Overlay()
{
    /**
     *  Overlay class definition.
     */

    // Static layers.
    //These layers + the manageDetects function (HMS layer)
    var geoXMLpath = "http://smokemgt.utah.gov/maplayers/";
    var class1XML = new google.maps.KmlLayer(geoXMLpath +"class1_16Nov2008.kml", {preserveViewport:true}); 
    var pm10XML = new google.maps.KmlLayer(geoXMLpath + "ut_pm10.kml", {preserveViewport:true});
    var airshedXML = new google.maps.KmlLayer(geoXMLpath + "ut_airsheds.kml", {preserveViewport:true});
    var airshedlabelsXML = new google.maps.KmlLayer(geoXMLpath + "airshed_labels3.kml", {preserveViewport:true});
    var pm25XML = new google.maps.KmlLayer(geoXMLpath + "ut_pm25.kml", {preserveViewport:true});
    //var firewxXML = new google.maps.KmlLayer("http://activefiremaps.fs.fed.us/data/kml/conus_latest_fire_wx.kml", {preserveViewport:true});
    //var firepotentialXML = new google.maps.KmlLayer("http://psgeodata.fs.fed.us/data/kml/conus_7day_fire_potential_latest.kml", {preserveViewport:true});

    var layers = [
        class1XML,
        pm10XML,
        pm25XML,
        airshedXML,         //Update Overlay.prototype.toggle if position canges.
        airshedlabelsXML    //Always at the end?  Update Overlay.prototype.toggle if position changes.
        //firewxXML,
        //firepotentialXML
    ];
    
	var images = [
        goes = new google.maps.ImageMapType({
            getTileUrl: function(tile, zoom) {
                return "http://mesonet.agron.iastate.edu/cache/tile.py/1.0.0/goes-west-vis-1km-900913/" + zoom + "/" + tile.x + "/" + tile.y +".png?"+ (new Date()).getTime(); 
            },
            tileSize: new google.maps.Size(256, 256),
            opacity:0.60,
            name : 'GOES Visible',
            isPng: true
        }),
		tileNEX = new google.maps.ImageMapType({
            getTileUrl: function(tile, zoom) {
                return "http://mesonet.agron.iastate.edu/cache/tile.py/1.0.0/nexrad-n0q-900913/" + zoom + "/" + tile.x + "/" + tile.y +".png?"+ (new Date()).getTime(); 
            },
            tileSize: new google.maps.Size(256, 256),
            opacity:0.60,
            name : 'NEXRAD',
            isPng: true
        })
	];

    var controls = [
        {title: "Class 1", layerId: 0},
        {title: "PM10 NAA", layerId: 1},
        {title: "PM2.5 NAA", layerId: 2},
        {title: "Airshed", layerId: 3}
        //{title: "Fire Wx", layerId: 4},
        //{title: "Fire Potential", layerId: 5},
    ];

    this.wmsActive = [];
    
    var wmsControls = [
    	{title: "NEXRAD", imageId: 1},
    	{title: "GOES Visible", imageId: 0}
    ];

    this.legacy = function()
    {
        /**
         *  Detects if 'legacy' browser is being used.
         *  If returns true, Overlay should default to basic features.
         */

        if (typeof BrowserDetect == 'undefined') {
            //console.log("BrowserDetect not initialized.");
        } else {
            if (BrowserDetect.version < 9 && BrowserDetect.browser == 'Explorer') {
                return true;
            }
        }

        return false;
    }

    this.inactiveColor = function()
    {
        if (this.legacy()) {
            return "#CCCCCC";
        } else {
            return 'rgba(255,255,255,0.35)';
        }
    }

    // HMS Info & layers.
    this.hmsActive = false;
    this.startdate = '';
    this.enddate = '';
    this.hmsLayers = [];

    this.getControls = function()
    {
        return controls;
    }

    this.getControl = function(i)
    {
        return controls[i];
    }
    
    this.getWmsControls = function()
    {
        return wmsControls;
    }

    this.getWmsControl = function(i)
    {
        return wmsControls[i];
    }
    
    this.getImages = function()
    {
        return images;
    }
    
    this.getImage = function(i)
    {
        return images[i];
    }
    
    this.getLayers = function()
    {
        return layers;
    }

    this.getLayer = function(i)
    {
        return layers[i];
    }
}

Overlay.prototype.setMap = function(map)
{
    /**
     *  Setmap for all static layers.
     */

    var layers = this.getLayers();

    for (var i = 0; i < layers.length; i++)
    {
        layers[i].setMap(map);
    }
}

Overlay.prototype.toggle = function(layerId, map)
{
    /**
     *  Toggle static KML layers.
     */

    var layer = this.getLayer(layerId);

    if (layer.getMap() == map) {
        layer.setMap(null);
        if(layerId == 3){
            this.getLayer(4).setMap(null);
        }
        return false;
    } else {
        layer.setMap(map);
        if(layerId == 3){
            this.getLayer(4).setMap(map);
        }
        return true;
    }
}

Overlay.prototype.toggleWms = function(imageId, map)
{
    /**
     *  Toggle static WMS images.
     */
     	
    var image = this.getImage(imageId);

    if (this.wmsActive.indexOf(imageId) > -1) {
        console.log("Remove image "+imageId);
        map.overlayMapTypes.setAt(imageId, null);
        this.wmsActive.remove(imageId);
        return false;
    } else {
        console.log("Add image "+imageId);
        this.wmsActive.push(imageId);
        map.overlayMapTypes.setAt(imageId, image);
        return true;
    }
}


Overlay.prototype.toggleDetects = function(SDate, EDate, map)
{
    /**
     *  Toggle HMS detects KML layers.
     */

    // Statics.
    var hmsPath = "http://wrapfets.org/hms/";
    var layer;

    if (this.hmsActive == false) {
        // If the HMS layers are not active, generate them.
        this.startdate = new Date(SDate);   
        this.enddate = new Date(EDate);
    
        // Calculate the date differential. 
        var diff = (this.enddate.getTime() - this.startdate.getTime()) / 1000 / 60 / 60 / 24;
        if (diff > 7) {
            diff = 7;
        }
        
        // Reset HMS layers array.
        this.hmsLayers = [];

        for (var i = 0; i <= diff; i++) {
            this.startdate.setDate(this.startdate.getDate()+1);
            var dateString = this.startdate.getFullYear() + String("0" +(this.startdate.getMonth() + 1)).slice(-2) + String("0" +this.startdate.getDate()).slice(-2);
            layer = new google.maps.KmlLayer(hmsPath + "fire" + dateString + ".kml",{preserveViewport:true});
            this.hmsLayers.push(layer);
        }
    }
    
    for (var i = 0; i < this.hmsLayers.length; i++) {
        if (this.hmsActive == false) {
            this.hmsLayers[i].setMap(map);
        } else if (this.hmsActive == true) {
            this.hmsLayers[i].setMap(null);
        }
    }

    if (this.hmsActive == false) {
        this.hmsActive = true;
        $('#hms-detect-toggle').css('backgroundColor', '#FFFFFF');
     }else if (this.hmsActive == true) {
        this.hmsActive = false;
        $('#hms-detect-toggle').css('backgroundColor', Overlay.inactiveColor());
    }
}

Overlay.prototype.setControls = function(map)
{
    /**
     *  Build the controls block.
     */

    var controls = this.getControls();    
    var toggleControlDiv = document.createElement('div');
    
    // Add the controls array.
    for (var i = 0; i < controls.length; i++) {
        var toggleControl = new this.toggleControl(toggleControlDiv, map, controls[i]);
    }

    // Add the HMS control.
    var hmsControl = new this.hmsControl(toggleControlDiv, map);

	var wmsControls = this.getWmsControls();

    // Add the controls array.
    for (var i = 0; i < wmsControls.length; i++) {
        var toggleControl = new this.wmsControl(toggleControlDiv, map, wmsControls[i]);
    }

    // Append the control box.
    toggleControlDiv.index = 1;
    map.controls[google.maps.ControlPosition.RIGHT_TOP].push(toggleControlDiv);
}

Overlay.prototype.toggleControl = function(controlDiv, map, control) 
{
    /**
     *  Build a single control from the controls list (excludes HMS detect control).
     */

    // Control Div CSS
    controlDiv.style.padding = '5px';

    // Control Border CSS
    var controlUI = document.createElement('div');
    controlUI.style.backgroundColor = Overlay.inactiveColor();
    controlUI.style.borderStyle = 'solid';
    controlUI.style.borderWidth = '1px';
    controlUI.style.borderColor = '#999999';
    controlUI.style.borderOpacity = '0.7';
    controlUI.style.borderRadius = '1px';
    controlUI.style.cursor = 'pointer';
    controlUI.style.textAlign = 'center';
    controlUI.title = control.title || 'Control';
    controlDiv.appendChild(controlUI);

    // Set CSS for the control interior
    var controlText = document.createElement('div');
    controlText.style.fontFamily = '\"Helvetica Neue\",Helvetica,Arial,sans-serif';
    controlText.style.fontSize = '12px';
    controlText.style.paddingLeft = '6px';
    controlText.style.paddingRight = '6px';
    controlText.innerHTML = '<b>'+control.title+'</b>' || '<b>Control</b>';
    controlUI.appendChild(controlText);

    google.maps.event.addDomListener(controlUI, 'click', function() {
        result = Overlay.toggle(control.layerId, map);

        if (result) {
            controlUI.style.backgroundColor = '#FFFFFF';
        } else {
            controlUI.style.backgroundColor = Overlay.inactiveColor();
        }
    });
}

Overlay.prototype.hmsControl = function(controlDiv, map)
{
    /**
     *  Add the HMS toggle form map control.
     */

    // Control Div CSS
    controlDiv.style.padding = '5px';

    // Control Border CSS
    var controlUI = document.createElement('div');
    controlUI.style.backgroundColor = Overlay.inactiveColor();
    controlUI.style.borderStyle = 'solid';
    controlUI.style.borderWidth = '1px';
    controlUI.style.borderColor = '#999999';
    controlUI.style.borderOpacity = '0.7';
    controlUI.style.borderRadius = '1px';
    controlUI.style.cursor = 'pointer';
    controlUI.style.textAlign = 'center';
    controlUI.title = 'HMS Detect';
    controlUI.id = "hms-detect-toggle";
    controlDiv.appendChild(controlUI);

    // Set CSS for the control interior
    var controlText = document.createElement('div');
    controlText.style.fontFamily = '\"Helvetica Neue\",Helvetica,Arial,sans-serif';
    controlText.style.fontSize = '12px';
    controlText.style.paddingLeft = '6px';
    controlText.style.paddingRight = '6px';
    controlText.innerHTML = '<b>HMS Detect</b>';
    controlUI.appendChild(controlText);

    google.maps.event.addDomListener(controlUI, 'click', function() {
        Overlay.hmsForm();
    });
}

Overlay.prototype.wmsControl = function(controlDiv, map, control) 
{
    /**
     *  Build the WMS controls from the wmsControls list).
     */

    // Control Div CSS
    controlDiv.style.padding = '5px';

    // Control Border CSS
    var controlUI = document.createElement('div');
    controlUI.style.backgroundColor = Overlay.inactiveColor();
    controlUI.style.borderStyle = 'solid';
    controlUI.style.borderWidth = '1px';
    controlUI.style.borderColor = '#999999';
    controlUI.style.borderOpacity = '0.7';
    controlUI.style.borderRadius = '1px';
    controlUI.style.cursor = 'pointer';
    controlUI.style.textAlign = 'center';
    controlUI.title = control.title || 'Control';
    controlDiv.appendChild(controlUI);

    // Set CSS for the control interior
    var controlText = document.createElement('div');
    controlText.style.fontFamily = '\"Helvetica Neue\",Helvetica,Arial,sans-serif';
    controlText.style.fontSize = '12px';
    controlText.style.paddingLeft = '6px';
    controlText.style.paddingRight = '6px';
    controlText.innerHTML = '<b>'+control.title+'</b>' || '<b>Control</b>';
    controlUI.appendChild(controlText);

    google.maps.event.addDomListener(controlUI, 'click', function() {
        result = Overlay.toggleWms(control.imageId, map);

        if (result) {
            controlUI.style.backgroundColor = '#FFFFFF';
        } else {
            controlUI.style.backgroundColor = Overlay.inactiveColor();
        }
    });
}

Overlay.prototype.hmsForm = function()
{
    /**
     * HMS date selector form.
     */

    var title = "Show HMS Detect Overlay";
    
    var append = "";

    if (this.hmsActive) {
        append = "<button class=\"btn btn-default btn-block\" onclick=\"Overlay.hideHms()\">Hide HMS Detects</button>";
    }

    var html = " \
            <p class=\"text-center\">Select a start and end date range spanning a maximum of 7 days.</p> \
            <form role=\"form\" id=\"hmsDetectForm\"> \
                <div class=\"form-group\"> \
                    <label for=\"hmsStartDate\">HMS Start Date</label> \
                    <input type=\"text\" data-provide=\"datepicker\" data-date-format=\"yyyy-mm-dd\" class=\"form-control\" name=\"start_date\" id=\"hmsStartDate\" placeholder=\"HMS Start Date\"> \
                </div> \
                <div class=\"form-group\"> \
                    <label for=\"hmsEndDate\">HMS End Date</label> \
                    <input type=\"text\" data-provide=\"datepicker\" data-date-format=\"yyyy-mm-dd\" class=\"form-control\" name=\"end_date\" id=\"hmsEndDate\" placeholder=\"HMS End Date\"> \
                </div> \
            </form> \
            <button class=\"btn btn-default btn-block\" onclick=\"Overlay.addHms(map)\">Show HMS Detects</button> \
            "+ append +" \
            <button class=\"btn btn-default btn-block\" onclick=\"cancel_modal()\">Close</button> \
        ";

    show_modal_small(html, title);
}

Overlay.prototype.addHms = function(map)
{
    /**
     *  Process HMS form.
     */

    var jsn = $('#hmsDetectForm').serializeArray();
    var startDate = jsn[0].value;
    var endDate = jsn[1].value;

    hide_modal();

    if (this.hmsActive == true) {
        // Hide the current HMS for a new range.
        this.toggleDetects(this.startdate, this.enddate);
    }

    this.toggleDetects(startDate, endDate, map);
}

Overlay.prototype.hideHms = function()
{
    /**
     *  Hide the HMS detects using the toggle function. Resets hmsActive, to ensure.
     */

    hide_modal();

    this.toggleDetects(this.startdate, this.enddate);
}