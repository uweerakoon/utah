/*!
 *	Datatables filtering package.
 *	Defines some default methods for using datatables.
 *
 *	table = a datatables object,
 *	active = a javascript array of active id's e.g. active = [1,2,3];
 *	info = a javascript array containing objects for each filter e.g info = [{id: 1, title: "Title", class: "class"},...]; 
 *	column = the datatables column this regex should be applied to, starts with 
 */

function dtFilter(active, activeDef, info, column, filterWrapper, ref, key, table)
{
	if (typeof table != 'undefined') {
		this.table = table;
	}
	this.filterWrapper = filterWrapper;
	this.ref = ref || 'span[ref=\"';
	this.triggerDefault = 'label-disabled';
	this.active = active;
	this.info = info;
	this.regex = "";
	this.column = column;

	// Private
	if (typeof activeDefault == 'undefined') {
		var activeDefault = activeDef;
	}

	this.getDefaults = function()
	{
		return activeDefault;
	};

	if (typeof refKey == 'undefined') {
		var refKey = key;
	}

	this.getKey = function()
	{
		return refKey;
	};
}

dtFilter.prototype.pushState = function()
{
	var page = window.location.pathname;
	var active = JSON.stringify(this.active);
	var args = {action: 'push', page: page, active: active, key: this.getKey()};

	$.post('/ajax/filterstate.php', args)
	.done(function(data) {
		//console.log("dtFitler.pushState(): $_SESSION pushed.");
	})
	.fail(function() {
		console.log("dtFitler.pushState(): failed.");
	});
}

dtFilter.prototype.getState = function()
{
	var page = window.location.pathname;
	var args = {action: 'get', page: page, key: this.getKey()};
	var anchor = this;

	$.post('/ajax/filterstate.php', args)
	.done(function(data) {
		console.log("dtFitler.getState(): $_SESSION retrieved.");
		active = JSON.parse(data);
		if (active) {
			anchor.hideAll();
			anchor.active = active;
			anchor.buildRegex();
			anchor.filter();
			for (var i = 0; i < active.length; i++) {
				var id = active[i];
				anchor.displaySpan(id);
			}
		}
	})
	.fail(function() {
		console.log("dtFitler.getState(): failed.");
	});
}

dtFilter.prototype.defineTable = function(table)
{
	/**
	 *	Allows table to be defined after intializing this class
	 */

	this.table = table;
	this.regex = this.buildRegex();
	this.filter();
}

dtFilter.prototype.filter = function()
{
	/**
	 *	 Fires the currently active filter/regex.
	 */

	this.table.fnFilter(this.regex, this.column, true, false, false, false);

	if (typeof this.filterMap != 'undefined') {
		this.showByRegexMap()
		this.filterMap();
	}
}

dtFilter.prototype.all = function()
{
	/**
	 *	Activate all filter items.
	 */

	if (this.active.length == this.info.length) {
		// Already selected all. reset instead.
		this.hideAll();
	} else {
		this.hideAll();

		for (var i = 0; i < this.info.length; i++) {
			var id = this.info[i].id;
			this.active.push(id);
			this.displaySpan(id);
		}
		
		this.buildRegex();
	}
	
	this.filter();	
}

dtFilter.prototype.reset = function()
{
	this.hideAll();

	this.active = this.getDefaults();
	
	this.buildRegex();
	this.displayActive();
	this.filter();
}

dtFilter.prototype.toggle = function(id)
{
	/**
	 *	Toggles a filter item by id.
	 */
	
	if ($.inArray(id, this.active) >= 0) {
		// The value is already showing, therefore remove it.
		this.active.remove(id);
		this.hideSpan(id);
		this.buildRegex();
	} else {
		// The value is not showing, add it.
		this.active.push(id);
		this.displaySpan(id);
		this.buildRegex();
	}

	this.pushState();
	this.filter();
}

dtFilter.prototype.buildRegex = function()
{
	/**
	 *	Construct the regex based on the active id array.
	 */

	if (this.active.length < 1) {
		this.regex = "^$";
	} else {
		for (var i = 0; i < this.active.length; i++) {
			var id = this.active[i];
			var row = $.grep(this.info, function(e){ return e.id == id; });
			var title = '' + row[0].title.replace(" ", ".") + '';
	
			if (i == 0) {
				this.regex = title;
			} else {
				this.regex = this.regex + '|' + title;
			}
		}
	}

	return this.regex;
}

dtFilter.prototype.displaySpan = function(id)
{
	var oldClass = this.triggerDefault;
	var row = $.grep(this.info, function(e){ return e.id == id; });
	var newClass = row[0].spanClass;

	$(this.filterWrapper).find(this.ref + id + '\"]')
		.removeClass(oldClass)
		.addClass(newClass);

	this.filter();
}

dtFilter.prototype.displayActive = function()
{
	for (var i = 0; i < this.active.length; i++){
		var id = this.active[i];
		var oldClass = this.triggerDefault;
		var row = $.grep(this.info, function(e){ return e.id == id; });
		var newClass = row[0].spanClass;
	
		$(this.filterWrapper).find(this.ref + id + '\"]')
			.removeClass(oldClass)
			.addClass(newClass);
	}

	this.filter();
}

dtFilter.prototype.hideAll = function()
{
	for (var i = 0; i < this.info.length; i++) {
		var id = this.info[i].id;
		this.active.remove(id);
		this.hideSpan(id);
		this.buildRegex();	
	}
}

dtFilter.prototype.hideSpan = function(id)
{
	var row = $.grep(this.info, function(e){ return e.id == id; });
	var oldClass = row[0].spanClass;
	var newClass = this.triggerDefault;
	
	$(this.filterWrapper).find(this.ref + id + '\"]')
		.removeClass(oldClass)
		.addClass(newClass);

	this.filter();
}