/*!
 *	Google Maps filtering package, extends dtFitler methods.
 *	Defines some default methods.
 *
 *	map = a Google Maps map object,
 *	active = a javascript array of active id's e.g. active = [1,2,3];
 *	info = a javascript array containing objects for each filter e.g info = [{id: 1, title: "Title", class: "class"},...]; 
 *	column = the datatables column this regex should be applied to, starts with 
 */

function gmFilter(map, markers)
{
	if (typeof map != 'undefined') {
		this.map = map;
	}
	if (typeof markers != 'undefined') {
		this.markers = markers;
	}

	this.filterFn = [];
	this.regexes = [];
};

gmFilter.prototype.defineMap = function(table)
{
	/**
	 *	Allows map to be defined after intializing this class
	 */

	this.map = map;
	if (typeof this.markers != 'undefined') {
		this.filterMap();	
	}
};

gmFilter.prototype.defineMarkers = function(markers)
{
	/**
	 *	Allows dtFilter to be defined after intializing this class
	 */

	this.markers = markers;
	this.filterMap();
};

gmFilter.prototype.registerFn = function(fnName, field)
{
	/**
	 * Creates a list of dtFilter functions, for automatic regex collection.
	 */

	var fnObj = {field: field, fnName: fnName};

	if (this.filterFn.length > 0) {
		for (var i = 0; i < this.filterFn.length; i++) {
			if (this.filterFn[i].field == field) {
				this.filterFn[i].fnName = fnName;
			} else {
				this.filterFn.push(fnObj);
			}
		}
	} else {
		this.filterFn.push(fnObj);
	}
};

gmFilter.prototype.appendRegex = function(field, regex)
{
	/**
	 * Manually append the regex array.
	 */

	var regObj = {field: field, regex: regex};

	if (this.regexes.length > 0) {
		for (var i = 0; i < this.regexes.length; i++) {
			if (this.regexes[i].field == field) {
				this.regexes[i].regex = regex;
			} else {
				this.regexes.push(regObj);
			}
		}
	} else {
		this.regexes.push(regObj);
	}
};

gmFilter.prototype.syncRegex = function()
{
	/**
	 *	Automatically collects all regex from the registered functions.
	 */

	// Assumes that we only want regexes from registered functions.
	this.regexes = [];

	for (var i = 0; i < this.filterFn.length; i++) {
		var field = this.filterFn[i].field;
		var fnName = this.filterFn[i].fnName;
		var regex = fnName.regex;
		var regObj = {field: field, regex: regex};

		this.regexes.push(regObj);
	}
};

gmFilter.prototype.filter = function()
{
	/**
	 *	Displays and hides map markers based on the show attribute.
	 */

	for (var i = 0; i < this.markers.length; i++) {
		var marker = this.markers[i];

		if (marker.show == true) {
			this.markers[i].setMap(this.map);
		} else {
			this.markers[i].setMap(null)
		}
	}
};

gmFilter.prototype.showByRegex = function()
{
	for (var j = 0; j < this.markers.length; j++) {
		var show = true;

		for (var i = 0; i < this.regexes.length; i++) {
			var regex = new RegExp(this.regexes[i].regex);
			var field = this.regexes[i].field;

			if (!regex.test(this.markers[j][field])) {
				show = false;
			}	
		}

		this.markers[j].show = show;		
	}	
};

gmFilter.prototype.sync = function()
{
	/**
	 *	Automatically syncs the map to the registered filter functions.
	 */

	this.syncRegex();
	this.showByRegex();
	this.filter();
};


