function userDistrictForm() {
    var target = '/ajax/user_district.php'
      , args = {form:true};

    $.post(target, args)
    .done( function(data) {
        $(formTarget).html(data)
    })
    .fail( function(data) {
        console.log("User District Form ajax failed.")
    })
}

function addUserGroup(refresh_function) {
	var target = '/ajax/user_district.php'
	  , jsn = JSON.stringify($('#user_group_form').serializeArray())
	  , args = {submit:true, args:jsn};

	if (typeof refresh_function == 'undefined') {
		function refresh_function(){
			location.reload();
		}
	}

	$.post(target, args)
	.done( function(data) {
		$(formTarget).html(data)
		refresh_function()
		clear_status_messages()
	})
	.fail( function() {
		console.log("Add User(s) to District(s) submit failed.");
	})
}

function User()
{
    /**
     *    User Class.
     */

    this.target = '/ajax/user.php';
    //this.refreshTrigger = '.accomplishment_refresh';
    this.formId = '#project_form';
    this.fieldsetPrepend = 'user_form_fs';
    this.toolbarSelector = '.user_form_fs';
    this.errorPrepend = 'User.';
    this.args = {};

    this.refresh = function()
    {
        location.reload(false);
    };

    this.clear = function()
    {
        setTimeout( function(){
            clear_status_messages();
        }, 4000);
    };
}

User.prototype.adminForm = function(user_id)
{
    /**
     *  Profile Page Edit Form
     */

    var errorPrepend = this.errorPrepend + 'adminForm():';
    this.args = {action: 'form-admin', user_id: user_id};
    var anchor = this;

    $.post(this.target, this.args)
    .done( function(data) {
        Interface.toggle();
        $(formTarget).html(data);
    })
    .fail( function() {
        console.log(errorPrepend + " $.post failed.");
    });
}

User.prototype.adminUpdate = function(user_id)
{
    /**
     *  Profile Page Edit Form
     */

    var errorPrepend = this.errorPrepend + 'adminUpdate():';
    var jsn = JSON.stringify($('#admin_form').serializeArray());
    var args = {action: 'update-admin', user_id: user_id, args: jsn};
    var anchor = this;

    $.post(this.target, args)
    .done(function(data) {
      var result = JSON.parse(data);
      console.log("Admin Update Result", result);
      if (result.error) {
        $("#interface-error").show()
        $("#error-message-block").html(
          "<div class=\"alert alert-danger alert-dismissible\" role=\"alert\"> \
            <button type=\"button\" class=\"close\" data-dismiss=\"alert\" \
             aria-label=\"Close\" $action><span aria-hidden=\"true\">&times;</span></button> \
            <strong>Error:</strong> "+ result.detail+". \
          </div>")
        return
      }
      anchor.refresh();
    })
    .fail(function() {
        console.log(errorPrepend + " $.post failed.");
    });
}

User.prototype.saveNew = function()
{
    /**
     *  Profile Page Edit Form
     */

    var errorPrepend = this.errorPrepend + 'adminUpdate():';
    var jsn = JSON.stringify($('#admin_form').serializeArray());
    var args = {action: 'save-new', args: jsn};
    var anchor = this;

    $.post(this.target, args)
    .done(function(data) {
      var result = JSON.parse(data);
      console.log("Admin Save New Result", result);
      if (result.error) {
        $("#interface-error").show()
        $("#error-message-block").html(
          "<div class=\"alert alert-danger alert-dismissible\" role=\"alert\"> \
            <button type=\"button\" class=\"close\" data-dismiss=\"alert\" \
             aria-label=\"Close\" $action><span aria-hidden=\"true\">&times;</span></button> \
            <strong>Error:</strong> "+ result.detail+". \
          </div>")
        return
      }
      anchor.refresh();
    })
    .fail(function() {
        console.log(errorPrepend + " $.post failed.");
    });
}

User.prototype.profileForm = function(user_id)
{
	/**
	 *	Profile Page Edit Form
	 */

    var errorPrepend = this.errorPrepend + 'profileForm():';
    var args = {action: 'form-profile', user_id: user_id};
    var anchor = this;

    $.post(this.target, args)
    .done( function(data) {
        Interface.toggle();
        $(formTarget).html(data);
    })
    .fail( function() {
        console.log(errorPrepend + " $.post failed.");
    });
}

User.prototype.profileUpdate = function(user_id)
{
    /**
     *  Profile Page Edit Form
     */

    var errorPrepend = this.errorPrepend + 'profileUpdate():';
    var jsn = JSON.stringify($('#profile_form').serializeArray());
    var args = {action: 'update-profile', user_id: user_id, args: jsn};
    var anchor = this;

    $.post(this.target, args)
    .done( function(data) {
        anchor.refresh();
        $(formTarget).html(data);
        //anchor.clear();
    })
    .fail( function() {
        console.log(errorPrepend + " $.post failed.");
    });
}

User.prototype.delete = function(user_id, warning)
{
  warning = typeof warning === "undefined"? true: warning;
  var errorPrepend = this.errorPrepend + 'delete()';
  var args = {
    action: "delete-warning",
    user_id: user_id
  }

  if (warning == false) {
    args['action'] = "delete"
  }

  $.post(this.target, args)
    .done(function(data) {
      var result = JSON.parse(data);
      console.log("Delete user results", result);
      if (args.action == "delete-warning") {
        show_modal_small(result.content, result.title, result.footer)
        return
      }
      if (result.error) {
        $("#interface-error").show()
        $("#error-message-block").html(
          "<div class=\"alert alert-danger alert-dismissible\" role=\"alert\"> \
            <button type=\"button\" class=\"close\" data-dismiss=\"alert\" \
             aria-label=\"Close\" $action><span aria-hidden=\"true\">&times;</span></button> \
            <strong>Error:</strong> "+ result.detail+". \
          </div>")
        return
      }
      cancel_modal()
      $("#user_id"+user_id).remove()
      return
    })
    .fail(function() {
      console.log(errorPrepend + " $.post failed")
    })
}
