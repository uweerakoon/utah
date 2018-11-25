/*!
 *	Datatables filtering package.
 *	Defines some default methods for using datatables.
 *
 *	table = a datatables object,
 *	active = a javascript array of active id's e.g. active = [1,2,3];
 *	info = a javascript array containing objects for each filter e.g info = [{id: 1, title: "Title", class: "class"},...]; 
 *	column = the datatables column this regex should be applied to, starts with 
 */

function dateFilter(column, table)
{
	if (typeof table != 'undefined') {
		this.table = table;
	}

	this.column = column;
	this.regex = "";
	this.selector = "#dtFilterDate";
}

dateFilter.prototype.pushState = function()
{
	var page = window.location.pathname;
	this.getDate();
	var date = this.regex;
	var args = {action: 'push-date', page: page, date: date, key: 'dateFilter'};

	$.post('/ajax/filterstate.php', args)
	.done(function(data) {
		//console.log("dateFilter.pushState(): $_SESSION pushed.");
	})
	.fail(function() {
		console.log("dateFilter.pushState(): failed.");
	});
}

dateFilter.prototype.defineTable = function(table)
{
	/**
	 *	Define the datatable.
	 */

	this.table = table;
}

dateFilter.prototype.getDate = function()
{
	/**
	 *	Retrieve the date for the filter.
	 */

	this.regex = $(this.selector).val();
}

dateFilter.prototype.filter = function()
{
	/**
	 *	Run the datatables filter.
	 */

	this.table.fnFilter(this.regex, this.column, true, false, false, false);
}

dateFilter.prototype.enable = function()
{
	/**
	 *	Enable the filter.
	 */
	
	this.getDate();
	this.pushState();
	this.filter();
}

dateFilter.prototype.disable = function()
{
	/**
	 *	Disable the filter.
	 */

	this.regex = "";
	this.filter();
}