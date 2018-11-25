/*!
 *	Utah.gov Notification Package
 */

function Notify()
{
	/**
	 *	The Notify class.
	 */

	this.ajaxTarget = "/ajax/notify.php";
	this.errorPrepend = "Notify.";
	this.args = {};
	this.trigger = '#notify-btn';
	this.cTarget = '#notify-upper-count';
	this.target = '#notify-div';
	this.display = false;
	this.notifications = [];
	this.count = 0;

	// Attach post DOM.
	var _this = this;

	this.attachEvents = function() {
		$(this.target + ' > .list-group-item').each(function() {
			if ($(this).attr("readable") == "true") {
				$(this).mouseenter( function() {
					var intermediate = this;
					setTimeout(function() {
						console.log("Mark as read now");
						console.log($(intermediate).attr("notify"));
						_this.markAsRead($(intermediate).attr("notify"));
					}, 3000);
				});
			}
		});
	}

	$(document).ready( function() {
		_this.getCount();
		//anchor.attachEvents();
	});
}

Notify.prototype.getCount = function()
{
	/**
	 *	Retrieve the current count from html.
	 */

	var count = $(this.cTarget).html();
	this.count = parseInt(count);

	return this.count;
}

Notify.prototype.syncList = function()
{
	/**
	 *	Get the database updated notify list.
	 */

	var errorPrepend = this.errorPrepend + 'syncList():';
	this.args = {action: "get-list"};
	var anchor = this;

	$.post(this.ajaxTarget, this.args)
    .done( function(data) {
       	// Get rid of the old one
       	$(anchor.target).remove();

       	// Append the new one
       	$('body').append(data);
    })
    .fail( function() {
        console.log(errorPrepend + " $.post failed.");
    });
}

Notify.prototype.syncCounter = function()
{
	/**
	 *	Get the database updated counter.
	 */

	var errorPrepend = this.errorPrepend + 'syncCounter():';
	this.args = {action: "get-count"};
	var anchor = this;

	$.post(this.ajaxTarget, this.args)
    .done( function(data) {
       	$(anchor.cTarget).html(data);
    })
    .fail( function() {
        console.log(errorPrepend + " $.post failed.");
    });
}

Notify.prototype.syncAll = function()
{
	/**
	 *
	 */

	this.syncCounter();
	this.syncList();
}

Notify.prototype.pushCount = function()
{
	/**
	 *	Retrieve the current count from html.
	 */

	$(this.cTarget).html(this.count);

	return this.count;
}

Notify.prototype.toggle = function()
{
	/**
	 *	Toggle the target div.
	 */

	if (this.count > 0) {
		$(this.target).toggle();
	} else {
		this.syncAll()
		this.getCount();
	}
}

Notify.prototype.show = function()
{
	/**
	 *	Show the target div.
	 */

	if (this.count > 0) {
		$(this.target).show();
	} else {
		this.syncAll()
		this.getCount();
	}
}

Notify.prototype.hide = function()
{
	/**
	 *	Hide the target div & check for more.
	 */

	$(this.target).hide();
}

Notify.prototype.detail = function(notify_id)
{

	var errorPrepend = this.errorPrepend + 'detail(notify_id):';
	this.args = {action: "detail", notify_id: notify_id};
	var title = "Notification Details";

	this.hide();

	$.post(this.ajaxTarget, this.args)
    .done( function(data) {
       	obj = JSON.parse(data);

        show_modal(obj.html, obj.title);
    })
    .fail( function() {
        console.log(errorPrepend + " $.post failed.");
    });
}

Notify.prototype.markAsRead = function(notify_id)
{
	/**
	 *	Mark an item as read.
	 */

	// Update the count.
	this.count = this.count - 1;
	this.count = ((this.count <= 0) ? 0: this.count);
	this.pushCount();

	var intermediate = '#notify_' + notify_id;

	// Update the message item.
	$(intermediate).attr("readable", "false");
	$(intermediate).hide(500);

	// Mark the notification read
	this.read(notify_id);

	// If there are no notifications
	if (this.count == 0) {
		this.hide();
	}
}

Notify.prototype.read = function(notify_id)
{
	/**
	 *	Sync the database to read
	 */

	var errorPrepend = this.errorPrepend + 'read(notify_id):';
	this.args = {action: "read", notify_id: notify_id};

	$.post(this.ajaxTarget, this.args)
    .done( function(data) {
        console.log("Marked read db");
    })
    .fail( function() {
        console.log(errorPrepend + " $.post failed.");
    });
}
