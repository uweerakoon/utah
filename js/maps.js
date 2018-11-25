/*!
 *      Utah SMS Google Maps v3 Support
 *      Tools for maps api.
 *      Requires elevation service enabled API key.
 */

var elevator = new google.maps.ElevationService();

function getPointElevation(latLonObj) 
{
    /**
     *  Gets the elevation of a location object.
     *  - Support function for area elevation.
     */

    var locations = []
      , elevation = null;

    locations.push(locationObj);

    var position = {
        'locations': locations
    }

    elevator.getElevationForLocations(position, function(results, status) {
        if (status == google.maps.ElevationStatus.OK) {
            if (results[0]) {
                elevation = results[0].elevation;
                console.log("Elevation successfully retrieved at: " + elevation + " meters");
            } else {
                console.log("pointElevation(locationObj) - no results");
            }
        } else {
            console.log("pointElevation(locationObj) failed: " + status);
        }
    });

    return elevation;
}

function getRectangleElevation(rectangleObj) 
{
    /**
     *  Breaks the rectangle down, and averages several elevations.
     *  - Gets elevation for form_functions.php.boundaryHTML()
     */

    // Break the area into a grid.
    var gridBreaks = 5
      , grid = [];

    return elevation;
}

function getRectangleArea(rectangleObj) 
{
    /**
     *  Calculates approximate area of a rectangle in square meters
     *  * Geodesic lengths,
     */

    // Get the rectangle boundaries. 
    var ne = rectangleObj.getBounds().getNorthEast()
      , sw = rectangleObj.getBounds().getSouthWest()
      , nw = new google.maps.LatLng(ne.lat(),sw.lng())
      , se = new google.maps.LatLng(sw.lat(),ne.lng());

    var path = new google.maps.Polygon({
        paths: [
            nw,
            ne,
            se,
            sw
        ]
    });

    // Calculate the area in square meters
    var area = google.maps.geometry.spherical.computeArea(path.getPath());

    console.log("The polygons area is: " + area + " meters");

    return area;
}

function convertAreaAcres(sqMeters) 
{
    /**
     *  Converts square meters to acres.
     */

    var sqMetersToAcres = 0.000247105;

    return sqMeters * sqMetersToAcres;
}

function isosceles(map, marker, initDeg, finalDeg, amplitude, color) 
{
    /** 
     *  Generates Google maps based isosceles.
     */

    this.marker = marker;
    this.initDeg = initDeg;
    this.finalDeg = finalDeg;
    this.amplitude = amplitude;
    this.color = color;
    this.path;
    this.polygon;

    if (typeof marker == 'object') {
        this.iso = this.generatePolygon(marker, initDeg, finalDeg, amplitude, color);

        this.iso.setMap(map);

        return this.iso;
    } else {
        console.log("Google Maps Isosceles, Toolset only.");
    }
}

isosceles.prototype.generatePolygon = function(marker, initDeg, finalDeg, amplitude, color) 
{
    /**
     *  Generates Google Maps based isosceles polygon.
     */
    
    var fill = (typeof color == 'undefined') ? '#GGGGGG' : color;

    this.isoCoords = this.generatePath(marker, initDeg, finalDeg, amplitude);

    var isoCoords = this.isoCoords;

    this.iso = new google.maps.Polygon({
        paths: isoCoords,
        strokeColor: fill,
        strokeOpacity: 0.8,
        strokeWeight: 2,
        fillColor: fill,
        fillOpacity: 0.35
    });

    return this.iso;
}

isosceles.prototype.generatePath = function(marker, initDeg, finalDeg, amplitude) 
{
    /**
     *  Generates Google Maps isosceles path.
     */

    var position = marker.getPosition();
    var radius = (amplitude * 3000);
    var pi = Math.PI;
    var initRad = initDeg * (pi/180);
    var finalRad = finalDeg * (pi/180);

    var p1 = new google.maps.LatLng(position.lat(), position.lng());
    var p2 = google.maps.geometry.spherical.computeOffset(p1, radius, initDeg);
    var p3 = google.maps.geometry.spherical.computeOffset(p1, radius, finalDeg);
    var p4 = new google.maps.LatLng(position.lat(), position.lng());

    this.isoPath = [p1, p2, p3, p4];

    return this.isoPath;
}

isosceles.prototype.isoFromJSON = function(json)
{
  
}

//isosceles.prototype = new google.maps.MVCObject();


