var eventTarget = ".event-block";
var formTarget = ".form-block";

function refresh_table(id, args) {
    $.post('/ajax/get_table.php', { args: args })
	.done(function(data) {
	    $('#'+id).replaceWith(data);
	})
	.fail(function() {
	    console.log('Refreshing #'+id+' failed.');
	});
}

function delete_record(table, key, id, refresh_function) {
	$.post('/ajax/delete.php', {table: table, key: key, id: id})
	.done(function(data) {
		$(formTarget).html(data);
		refresh_function();
	})
	.fail(function() {
		console.log('file: modify_records.js, function: delete_record(), error: Ajax post unsuccessful.')
		$('.event-block').append("Delete AJAX call unsuccessful.");
	})
}

function insert_form(table, key, refresh_function) {
	$.post('/ajax/insert.php', {form: true, table:table, refresh_function:refresh_function})
	.done(function(data) {
		Interface.toggle();
		$(formTarget).html(data)
	})
	.fail(function() {
		console.log("Modify_Records: insert_form() failed.");
	})
}

function insert_record(table, key, refresh_function) {
	sync_ckeditors()
	var jsn = JSON.stringify($('#insert_form').serializeArray())
	$.post('/ajax/insert.php', {submit:true, table: table, key: key, args:jsn})
	.done(function(data) {
		Interface.toggle();
		$(formTarget).html(data)
		refresh_function()
		clear_status_messages()
	})
	.fail(function() {
		console.log('file: modify_records.js, function: insert_record(), error: Ajax post unsuccessful.')
		$(eventTarget).append("Insert AJAX call unsuccessful.");
	})
}

function edit_form(table, key, id, refresh_function) {
	$.post('/ajax/update.php', {form:true, table:table, key:key, id:id, refresh_function:refresh_function})
	.done(function(data) {
		Interface.toggle();
		$(formTarget).html(data)
	})
	.fail(function() {
		console.log("Modify_Records: edit_form() failed.");
	})
}

function edit_record(table, key, id, refresh_function) {
	sync_ckeditors();
	var jsn = JSON.stringify($('#edit_form').serializeArray())
	$.post('/ajax/update.php', {submit:true, table:table, key:key, id:id, args: jsn})
	.done(function(data) {
		Interface.toggle();
		$(formTarget).html(data)
		refresh_function()
		clear_status_messages()
	})
	.fail(function() {
		console.log('file: modify_records.js, function: edit_record(), error: Ajax post unsuccessful.')
		$(eventTarget).append("Edit AJAX call unsuccessful.");
	})
}

/**
 *	General HTML Functions.
 */

function cancel_form(toggle) 
{
	$(formTarget).html("");
	if (toggle == true) {
		Interface.toggle();
	}

	return false;
}

function clear_status_messages() 
{
	var timeout = 2500
	  , animationTime = 500
	  , target = 'div.alert';
	
	setTimeout( function () {
		$(target).hide("slow")
		$(target).remove()
	}, timeout);

	return false;
}

/**
 *	Redirect Functions.
 */

function go_to(url) 
{
	window.location(url);
	return false;
}

function go_to_district(district_id) 
{
	window.location.href = '?district_id='+district_id;
	return false;
}

/**
 *	Modal Functions.
 */

function append_modal_small(target)
{
	/**
	 *	Append the modal html structure to a DOM element.
	 */

	if (typeof target == 'undefined') {
		target = 'body';
	}

	html = '<div class=\"modal fade\" tabindex=\"-1\" role=\"dialog\" aria-labelledby=\"mySmallModalLabel\" aria-hidden=\"true\"> \
	  <div class=\"modal-dialog modal-sm\"> \
	    <div class=\"modal-content\"> \
	      <div class=\"modal-header\"> \
	      	<button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-hidden=\"true\">&times;</button> \
	      	<div class=\"modal-text-header\"></div> \
	      </div> \
	      <div class=\"modal-body\"> \
	      </div> \
	      <div class=\"modal-footer\"> \
	      </div> \
	    </div> \
	  </div> \
	</div>';

	if ($('.modal').length > 0) {
		// The modal exists already, simply return false.
		return true;
	} else {
		$(target).append(html)
		return true;
	}	
}

function show_modal_small(content, title, footer)
{
	
	// Add a small modal.
	var modal = append_modal_small()

	// If there is a modal, add contend then show it.
	if (modal) {
		// Add the default content.
		$('.modal-body').html(content)
	
		if (!$('.modal-dialog').hasClass('modal-sm')) {
			$('.modal-dialog').addClass('modal-sm');
		}

		// Add a title if one was specified.
		if (typeof title !== 'undefined') {
			$('.modal-text-header').html(title);
		} else {
			$('.modal-text-header').hide();
		}
	
		// Add a footer if one was specified.
		if (typeof footer !== 'undefined') {
			$('.modal-footer').html(footer)
		} else {
			$('.modal-footer').hide();
		}

		$('.modal').modal('show');

		return true;
	} else {
		return false;
	}
}

function append_modal(target)
{
	/**
	 *	Append the modal html structure to a DOM element.
	 */

	if (typeof target == 'undefined') {
		target = 'body';
	}

	html = '<div class=\"modal fade\" tabindex=\"-1\" role=\"dialog\" aria-labelledby=\"mySmallModalLabel\" aria-hidden=\"true\"> \
	  <div class=\"modal-dialog\"> \
	    <div class=\"modal-content\"> \
	      <div class=\"modal-header\"> \
	      	<button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-hidden=\"true\">&times;</button> \
	      	<div class=\"modal-text-header\"></div> \
	      </div> \
	      <div class=\"modal-body\"> \
	      </div> \
	      <div class=\"modal-footer\"> \
	      </div> \
	    </div> \
	  </div> \
	</div>';

	if ($('.modal').length > 0) {
		// The modal exists already, simply return false.
		return true;
	} else {
		$(target).append(html)
		return true;
	}	
}

function show_modal(content, title, footer)
{
	
	// Add a small modal.
	var modal = append_modal()

	// If there is a modal, add contend then show it.
	if (modal) {
		// Add the default content.
		$('.modal-body').html(content)
	
		if ($('.modal-dialog').hasClass('modal-sm')) {
			$('.modal-dialog').removeClass('modal-sm');
		}

		// Add a title if one was specified.
		if (typeof title !== 'undefined') {
			$('.modal-text-header').html(title);
		} else {
			$('.modal-text-header').hide();
		}
	
		// Add a footer if one was specified.
		if (typeof footer !== 'undefined') {
			$('.modal-footer').html(footer)
		} else {
			$('.modal-footer').hide();
		}

		$('.modal').modal('show');

		return true;
	} else {
		return false;
	}
}

function hide_modal(reload)
{
	if ($('.modal').length > 0) {
		$('.modal').modal('hide');
	}

	if(reload == true) {
		location.reload(false);
	}

	if (typeof OverlayTwo != 'function') {
		delete OverlayTwo;
	}

	return false;
}

function cancel_modal(reload)
{
	return hide_modal(reload);
}

function sync_ckeditors()
{
	$('textarea.editordiv').each(function () {
		var textarea = $(this);
        var value = CKEDITOR.instances[textarea.attr('id')].getData();

        textarea.html(value);
    });
}

var BrowserDetect = 
{
    init: function () 
    {
        this.browser = this.searchString(this.dataBrowser) || "Other";
        this.version = this.searchVersion(navigator.userAgent) ||       this.searchVersion(navigator.appVersion) || "Unknown";
    },

    searchString: function (data) 
    {
        for (var i=0 ; i < data.length ; i++)   
        {
            var dataString = data[i].string;
            this.versionSearchString = data[i].subString;

            if (dataString.indexOf(data[i].subString) != -1)
            {
                return data[i].identity;
            }
        }
    },

    searchVersion: function (dataString) 
    {
        var index = dataString.indexOf(this.versionSearchString);
        if (index == -1) return;
        return parseFloat(dataString.substring(index+this.versionSearchString.length+1));
    },

    dataBrowser: 
    [
        { string: navigator.userAgent, subString: "Chrome",  identity: "Chrome" },
        { string: navigator.userAgent, subString: "MSIE",    identity: "Explorer" },
        { string: navigator.userAgent, subString: "Firefox", identity: "Firefox" },
        { string: navigator.userAgent, subString: "Safari",  identity: "Safari" },
        { string: navigator.userAgent, subString: "Opera",   identity: "Opera" }
    ]

};

BrowserDetect.init();

function checkLegacy()
{
    /**
     *  Detects if 'legacy' browser is being used.
     *  If returns true, Overlay should default to basic features.
     */

    if (typeof BrowserDetect == 'undefined') {
        console.log("BrowserDetect not initialized.");
    } else {
        if (BrowserDetect.version < 9 && BrowserDetect.browser == 'Explorer') {
            return true;
        }
    }

    return false;
}

// Support functions:
if (!Array.prototype.indexOf)
{
  Array.prototype.indexOf = function(elt /*, from*/)
  {
    var len = this.length >>> 0;

    var from = Number(arguments[1]) || 0;
    from = (from < 0)
         ? Math.ceil(from)
         : Math.floor(from);
    if (from < 0)
      from += len;

    for (; from < len; from++)
    {
      if (from in this &&
          this[from] === elt)
        return from;
    }
    return -1;
  };
}

Array.prototype.remove = function() 
{
    var what, a = arguments, L = a.length, ax;
    while (L && this.length) {
        what = a[--L];
        while ((ax = this.indexOf(what)) !== -1) {
            this.splice(ax, 1);
        }
    }

    return this;
};

/** 
 *	Ajax Loading
 */

$(document).on({
    ajaxStart: function() {
    	console.log("Ajax: Loading...");
    	if ($('#loadingIcon').length > 0) {
    		$('#loadingIcon').show(); 
    	} else {
    		$('#interfaceForm').append("<span id=\"loadingIcon\" style=\"width: 100%; text-align: center; font-size: 24px\" class=\"clean-animation glyphicon glyphicon-refresh spin\"></span>"); 	
    	}
    },
    ajaxStop: function() {
    	console.log("Ajax: Loading complete.");
    	$('#loadingIcon').hide();
   	}
});

/**
 *	Controller for hiding/showing content areas on pages.
 */

function Interface() 
{
	this.formDiv = '#interfaceForm';
	this.mainDiv = '#interfaceMain';

	this.formVisible = false;
	this.mainVisible = false;
}

Interface.prototype.toggle = function() {
	/** 
	 *	Toggle the view
	 */

	if (this.formVisible) {
		this.formHide();
		this.mainShow();
	} else if (this.mainVisible) {
		this.formShow();
		this.mainHide();
	}
}

Interface.prototype.formStatus = function() {
	if($(this.formDiv).is(':visible')) {
		this.formVisible = true;
	} else {
		this.formVisible = false;
	}

	return this.formVisible;
}

Interface.prototype.formShow = function() {
	$(this.formDiv).show();
	this.formStatus();
}

Interface.prototype.formHide = function() {
	$(this.formDiv).hide();
	this.formStatus();
}

Interface.prototype.mainStatus = function() {
	if($(this.mainDiv).is(':visible')) {
		this.mainVisible = true;
	} else {
		this.mainVisible = false;
	}

	return this.mainVisible;
}

Interface.prototype.mainShow = function() {
	$(this.mainDiv).show();
	this.mainStatus();
}

Interface.prototype.mainHide = function() {
	$(this.mainDiv).hide();
	this.mainStatus();
}

Interface.prototype.status = function() {
	/** 
	 *	Get statuses
	 */

	this.formStatus();
	this.mainStatus();
}

/**
 *	Math.Round() - Rounding Functions
 */

/**
 * Decimal adjustment of a number.
 * Reference: https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Math/round
 *
 * @param	{String}	type	The type of adjustment.
 * @param	{Number}	value	The number.
 * @param	{Integer}	exp		The exponent (the 10 logarithm of the adjustment base).
 * @returns	{Number}			The adjusted value.
 */
	
function decimalAdjust(type, value, exp) {
	// If the exp is undefined or zero...
	if (typeof exp === 'undefined' || +exp === 0) {
		return Math[type](value);
	}
	value = +value;
	exp = +exp;
	// If the value is not a number or the exp is not an integer...
	if (isNaN(value) || !(typeof exp === 'number' && exp % 1 === 0)) {
		return NaN;
	}
	// Shift
	value = value.toString().split('e');
	value = Math[type](+(value[0] + 'e' + (value[1] ? (+value[1] - exp) : -exp)));
	// Shift back
	value = value.toString().split('e');
	return +(value[0] + 'e' + (value[1] ? (+value[1] + exp) : exp));
}

// Decimal round
if (!Math.round10) {
	Math.round10 = function(value, exp) {
		return decimalAdjust('round', value, exp);
	};
}
// Decimal floor
if (!Math.floor10) {
	Math.floor10 = function(value, exp) {
		return decimalAdjust('floor', value, exp);
	};
}
// Decimal ceil
if (!Math.ceil10) {
	Math.ceil10 = function(value, exp) {
		return decimalAdjust('ceil', value, exp);
	};
}


var alertFallback = true;
if (typeof console === "undefined" || typeof console.log === "undefined") {
  console = {};
  if (alertFallback) {
      console.log = function(msg) {
           //alert(msg);
      };
  } else {
      console.log = function() {};
  }
}


