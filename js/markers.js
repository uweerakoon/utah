/*!
 *      Utah.gov Marker Control Class
 *      Marker Control and Toggle functions
 */

function Markers(markers)
{
	/**
	 *
	 */

	var controls = [
        {title: "Editable", edit: 1},
        {title: "District-Wide", edit: 0}
    ];

    // UPDATE TO DYNAMIC.
    this.prehref = '/manager/project.php';
    this.markers = markers;

    console.log(markers);
    
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
    	/**
    	 * 	Return a standard color for old browsers (non rgb-alpha).
    	 */

        if (this.legacy()) {
            return "#CCCCCC";
        } else {
            return 'rgba(255,255,255,0.35)';
        }
    }
    
    this.getControls = function()
    {
        return controls;
    }

    this.getControl = function(i)
    {
        return controls[i];
    }
}

Markers.prototype.setMarkers = function(map) 
{
    /** 
     *	Sets the Markers to the map.
     */

    for (var i = 0; i < markers.length; i++) {
        var marker = markers[i];
        var myLatLng = new google.maps.LatLng(marker[1], marker[2]);
                
        if (this.wcheckLegacy()) {
            var marker = new google.maps.Marker({
                position: myLatLng,
                map: map,
                title: marker[3],
                id: marker[0]
            });
        } else {
            var marker = new google.maps.Marker({
                position: myLatLng,
                map: map,
                icon: {
                    path: google.maps.SymbolPath.CIRCLE,
                    scale: 6,
                    strokeColor: '#333',
                    strokeOpacity: 1,
                    strokeWeight: 1,
                    fillColor: marker[4],
                    fillOpacity: 1
                },
                title: marker[3],
                id: marker[0],
                edit: marker[5]
            });
        }                   
    }
}

Markers.prototype.bindMarkers = function(map, marker)
{
    /** 
     *
     */

    google.maps.event.addListener(marker, 'click', function() {
        window.location=this.prehref+'?detail=true&id='+marker.id;return false;
    });	
}



Markers.prototype.toggleControl = function(controlDiv, map, control)
{
	/**
     *  Build a single control from the controls list (excludes HMS detect control).
     */

    // Control Div CSS
    controlDiv.style.padding = '5px';

    // Control Border CSS
    var controlUI = document.createElement('div');
    controlUI.style.backgroundColor = Markers.inactiveColor();
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
        result = Markers.toggle(control.layerId, map);

        if (result) {
            controlUI.style.backgroundColor = '#FFFFFF';
        } else {
            controlUI.style.backgroundColor = Overlay.inactiveColor();
        }
    });
}

Markers.prototype.toggle = function()
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

Markers.prototype.setControls = function(map)
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

    // Append the control box.
    toggleControlDiv.index = 1;
    map.controls[google.maps.ControlPosition.TOP_RIGHT].push(toggleControlDiv);
}

