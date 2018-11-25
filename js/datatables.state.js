/*!
 *	Datatables filter state session package.
 *	Defines some default methods for saving sort column and direction.
 *
 *	table = a datatables object,
 */

function dtState(key, table)
{
	if (typeof table != 'undefined') {
		this.table = table;
	}

	if (typeof key != 'undefined') {
		var key = key;
	}

	this.getKey = function()
	{
		return key;
	};

	this.sort = false;
	this.column;
	this.direction;
}

dtState.prototype.defineTable = function(table)
{
	/**
	 *	Allows table to be defined after intializing this class
	 */

	this.table = table;
}

dtState.prototype.getTableParams = function()
{
	/**
	 *	Retreives the sort state.
	 */

	var aaSorting = this.table.fnSettings().aaSorting;
	this.column = aaSorting[0][0];
	this.direction = aaSorting[0][1];
	if (typeof this.column != 'undefined') {
		this.sort = true;
	}
}

dtState.prototype.pushState = function()
{
	var page = window.location.pathname;
	var args = {action: 'push-sort', page: page, column: this.column, direction: this.direction, key: this.getKey()};

	if(this.sort) {
		$.post('/ajax/filterstate.php', args)
		.done(function(data) {
			//console.log("dateFilter.pushState(): $_SESSION pushed.");
		})
		.fail(function() {
			console.log("dateFilter.pushState(): failed.");
		});
	}
}
