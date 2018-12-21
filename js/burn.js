/*!
 *      Utah.gov Burn Request Ajax
 *      Tools & Actions for Burn Requests
 */

function Burn()
{
    /**
     *    Burn Request Class
     */

    this.target = '/ajax/burn.php';
    //this.refreshTrigger = '.accomplishment_refresh';
    this.formId = '#burn_form';
    this.fieldsetPrepend = 'burn_form_fs';
    this.toolbarSelector = '.burn_form_tb';
    this.errorPrepend = 'Burn.';
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

Burn.prototype.showForm = function (page, burn_id)
{
    /**
     *  Shows or appends the selected burn form page.
     */

    var errorPrepend = this.errorPrepend + 'showForm():';
    var fieldset = this.fieldsetPrepend + page;

    $(this.formId).find('fieldset').hide();

    if (document.getElementById(fieldset)) {
        $('#'+fieldset).show();
    } else {
        this.appendForm(page, burn_id);
    }

    if ($(this.toolbarSelector)) {
        this.updateToolbar(page, burn_id);
    } else {
        this.toolbar(page, burn_id);
    }
}

Burn.prototype.newForm = function(burn_project_id, pre_burn_id)
{
    /**
     *  Initializes the burn request form.
     */

    var errorPrepend = this.errorPrepend + 'newForm():';
    var anchor = this;
    var title = "";
    var page = 1;

    if (typeof burn_project_id == 'undefined') {
        title = "Select a Burn Project";
        this.args = {action: "burn-project-selector"};

        $.post(this.target, this.args)
        .done( function(data) {
           show_modal_small(data, title);
        })
        .fail( function() {
            console.log(errorPrepend + " Burn Plan selector $.post failed.");
        });
    } else {
        if (typeof pre_burn_id == 'undefined') {
            title = "Select a Pre-Burn";
            this.args = {action: "pre-burn-selector", burn_project_id: burn_project_id};

            $.post(this.target, this.args)
            .done( function(data) {
               show_modal_small(data, title);
            })
            .fail( function() {
                console.log(errorPrepend + " Pre-Burn selector $.post failed.");
            });
        } else {
            this.args = {action: "form-basic", page: page, pre_burn_id: pre_burn_id};

            hide_modal();

            $.post(this.target, this.args)
            .done( function(data) {
                Interface.toggle();
                anchor.toolbar(page);
                $(formTarget).html(data);
            })
            .fail( function() {
                console.log(errorPrepend + " $.post failed.");
            });
        }
    }
}

Burn.prototype.editConfirmation = function(burn_id)
{
    /**
     *  Initializes delete confirmation in small modal.
     *  Prevents accidental and disallowed burn plan deletes.
     */

    var errorPrepend = this.errorPrepend + 'editConfirmation():';
    var title = "Edit Confirmation";
    this.args = {action: "get-status", burn_id: burn_id};
    var anchor = this;

    $.post(this.target, this.args)
    .done( function(data) {
        var data = JSON.parse(data);
        data.status_id = parseInt(data.status_id);

        if (data.allow_edit) {
            anchor.editForm(burn_id);
        } else {
            title = "Burn Request Warning";
            anchor.args = {action: "edit-warning", burn_id: burn_id};

            $.post(anchor.target, anchor.args)
            .done( function(data) {
                show_modal_small(data, title);
            })
            .fail( function(data) {
                console.log(errorPrepend + " edit-warning $.post failed.");
            })
        }
    })
    .fail( function(data) {
        console.log(errorPrepend + " get-status $.post failed.");
    });
}

Burn.prototype.toDraft = function(burn_id)
{
    /**
     *  Changes an approved plan to draft plan.
     *  After the confirmation, allows users to edit an approved.
     */

    var errorPrepend = this.errorPrepend + 'toDraft():';
    this.args = {action: "to-draft", burn_id: burn_id};
    var anchor = this;

    hide_modal();

    $.post(this.target, this.args)
    .done( function(data) {
        $(anchor.formId).append(data);
        anchor.refresh();
    })
    .fail( function() {
        console.log(errorPrepend + " $.post failed.");
    })
}

Burn.prototype.editForm = function(burn_id)
{
    /**
     *  Initializes a new update pre-burn form.
     */

    var errorPrepend = this.errorPrepend + 'editForm():';
    var page = 1;
    this.args = {action: "form-basic", page: page, burn_id: burn_id};
    var anchor = this;

    $.post(this.target, this.args)
    .done( function(data) {
        Interface.toggle();
        anchor.toolbar(page, burn_id);
        $(formTarget).html(data);
    })
    .fail( function() {
        console.log(errorPrepend + " $.post failed.");
    });
}

Burn.prototype.appendForm = function(page, burn_id)
{
    /**
     *  Appends fieldsets to the pre-burn form.
     */

    var errorPrepend = this.errorPrepend + 'appendForm():';
    this.args = {action: "form-basic", page: page, burn_id: burn_id}
    var anchor = this;

    $.post(this.target, this.args)
    .done( function(data) {
        $(anchor.formId).append(data);
    })
    .fail( function(data) {
        console.log(errorPrepend + " $.post failed.");
    });
}

Burn.prototype.toolbar = function(page, burn_id)
{
    /**
     *  Initializes a new toolbar.
     */

    var errorPrepend = this.errorPrepend + 'toolbar():';
    this.args = {action: "form-toolbar", page: page, burn_id: burn_id}

    $.post(this.target, this.args)
    .done( function(data) {
        $(formTarget).prepend(data).append(data);
    })
    .fail( function() {
        console.log(errorPrepend + " $.post failed.");
    });
}

Burn.prototype.updateToolbar = function(page, burn_id)
{
    /**
     *  Updates/appends an existing toolbar.
     */

    var errorPrepend = this.errorPrepend + 'updateToolbar():';
    this.args = {action: "form-toolbar", page: page, burn_id: burn_id};
    var anchor = this;

    $.post(this.target, this.args)
    .done( function(data) {
        $(anchor.toolbarSelector).html(data);
    })
    .fail( function() {
        console.log(errorPrepend + " $.post failed.");
    });
}

Burn.prototype.save = function()
{
    /**
     *  Save a new pre-burn form.
     */

    var errorPrepend = this.errorPrepend + 'save():';
    var jsn = JSON.stringify($(this.formId).serializeArray());
    this.args = {action: "save", args: jsn};
    var anchor = this;

    $.post(this.target, this.args)
    .done( function(data) {
        $(anchor.formId).html(data);
        anchor.refresh();
    })
    .fail( function(data) {
        console.log(errorPrepend + " $.post failed.");
    })
}

Burn.prototype.saveUtah = function()
{
    /**
     *  Save a new pre-burn form.
     */

    var errorPrepend = this.errorPrepend + 'saveUtah():';
    var jsn = JSON.stringify($(this.formId).serializeArray());
    this.args = {action: "saveUtah", args: jsn};
    var title = "Submit to Utah.gov";

    $.post(this.target, this.args)
    .done( function(data) {
    	show_modal_small(data, title);
    })
    .fail( function(data) {
        console.log(errorPrepend + " $.post failed.");
    })
}

Burn.prototype.update = function(burn_id)
{
    /**
     *  Update an existing pre-burn form.
     */

    var errorPrepend = this.errorPrepend + 'update():';
    var jsn = JSON.stringify($(this.formId).serializeArray());
    this.args = {action: "update", args: jsn, burn_id: burn_id};
    var anchor = this;

    $.post(this.target, this.args)
    .done( function(data) {
        $(anchor.formId).html(data);
        anchor.refresh();
    })
    .fail( function(data) {
        console.log(errorPrepend + " $.post failed.");
    });
}

Burn.prototype.updateUtah = function(burn_id)
{
    /**
     *  Update an existing pre-burn form.
     */

    var errorPrepend = this.errorPrepend + 'updateUtah():';
    var jsn = JSON.stringify($(this.formId).serializeArray());
    this.args = {action: "updateUtah", args: jsn, burn_id: burn_id};
    var title = "Submit to Utah.gov";

    $.post(this.target, this.args)
    .done( function(data) {
    	show_modal_small(data, title);
    })
    .fail( function(data) {
        console.log(errorPrepend + " $.post failed.");
    });
}

Burn.prototype.submitForm = function(burn_id)
{
    /**
     *  Initializes the submit to Utah.gov confirmation form.
     */

    var errorPrepend = this.errorPrepend + 'submitForm():';
    this.args = {action: 'form-submit', burn_id: burn_id};
    var title = "Submit to Utah.gov";

    $.post(this.target, this.args)
    .done( function(data) {
        show_modal_small(data, title);
    })
    .fail( function() {
        console.log(errorPrepend + " $.post failed.");
    });
}

Burn.prototype.submitToUtah = function(burn_id)
{
    /**
     *  Submits the burn plan to Utah.gov.
     */

    var errorPrepend = this.errorPrepend + 'submitToUtah():';
    this.args = {action: 'submit', burn_id: burn_id};
    var anchor = this;

    hide_modal();

    $.post(this.target, this.args)
    .done( function(data) {
        $(formTarget).html(data);
        anchor.refresh();
    })
    .fail( function() {
        console.log(errorPrepend + " $.post failed.");
    });
}

Burn.prototype.reviewDetail = function(review_id)
{
    /**
     *  Initializes a small modal with the review information.
     */

    var errorPrepend = this.errorPrepend + 'reviewDetail():';
    var title = "Review Detail";
    this.args = {action: "review-detail", review_id: review_id};

    $.post(this.target, this.args)
    .done( function(data) {
        show_modal_small(data, title);
    })
    .fail( function(data) {
        console.log(errorPrepend + " $.post failed.");
    });
}

Burn.prototype.conditionDetail = function(condition_id)
{
    /**
     *  Initializes a small modal with the review information.
     */

    var errorPrepend = this.errorPrepend + 'conditionDetail():';
    var title = "Conditional Approval Detail";
    this.args = {action: "condition-detail", condition_id: condition_id};

    $.post(this.target, this.args)
    .done( function(data) {
        show_modal_small(data, title);
    })
    .fail( function(data) {
        console.log(errorPrepend + " $.post failed.");
    });
}

Burn.prototype.deleteRecord = function(burn_id)
{
    /**
     *  Delete a pre-burn plan. Return success message.
     */

    var errorPrepend = this.errorPrepend + 'delete():';
    this.args = {action: 'delete', burn_id: burn_id};
    var anchor = this;

    // Hide the deleteConfirmation modal.
    hide_modal();

    // Process the burn plan delete.
    $.post(this.target, this.args)
    .done( function(data) {
        $(formTarget).append(data);
        anchor.refresh();
    })
    .fail( function() {
        console.log(errorPrepend + " $.post failed.");
    });
}

Burn.prototype.deleteConfirmation = function(burn_id)
{
    /**
     *  Initializes delete confirmation in small modal.
     *  Prevents accidental and disallowed burn plan deletes.
     */

    var errorPrepend = this.errorPrepend + 'deleteConfirmation():';
    var title = "Delete Confirmation";
    this.args = {action: "delete-confirmation", burn_id: burn_id};

    $.post(this.target, this.args)
    .done( function(data) {
        show_modal_small(data, title);
    })
    .fail( function(data) {
        console.log(errorPrepend + " $.post failed.");
    });
}

Burn.prototype.ownerChange = function(burn_id)
{
    /**
     *  Delete a burn plan. Return success message.
     */

    var errorPrepend = this.errorPrepend + 'ownerChange():';
    var anchor = this;
    var formArray = $('#owner-change-form').serializeArray();
    var user_id = formArray[0].value;
    var district_id = formArray[1].value;
    this.args = {action: 'owner-change', burn_id: burn_id, user_id: user_id, district_id: district_id};

    // Hide the deleteConfirmation modal.
    hide_modal();

    // Process the burn plan delete.
    $.post(this.target, this.args)
    .done( function(data) {
        $(formTarget).append(data);
        anchor.refresh();
    })
    .fail( function() {
        console.log(errorPrepend + " $.post failed.");
    });
}

Burn.prototype.ownerChangeForm = function(burn_id)
{
    /**
     *  Initializes delete confirmation in small modal.
     *  Prevents accidental and disallowed burn plan deletes.
     */

    var errorPrepend = this.errorPrepend + 'ownerChangeForm():';
    var title = "Change Pre-Burn Owner";
    this.args = {action: "owner-change-form", burn_id: burn_id};

    $.post(this.target, this.args)
    .done( function(data) {
        show_modal_small(data, title);
    })
    .fail( function(data) {
        console.log(errorPrepend + " $.post failed.");
    });
}

Burn.prototype.validate = function(burn_id, refresh_function)
{
    /**
     *  Validate the burn request.
     */

    var errorPrepend = this.errorPrepend + 'validate():';
    var title = "Burn Request Completeness";
    this.args = {action: 'check-complete', burn_id: burn_id};
    var anchor = this;

    $.post(this.target, this.args)
    .done( function(data) {
        show_modal_small(data, title);
    })
    .fail( function() {
        console.log(errorPrepend + " $.post failed.");
    });
}

Burn.prototype.checkOverlap = function(pre_burn_id, start_date, end_date)
{
    /**
     *  Checks for burn date overlap for the current pre_burn.
     */

    var errorPrepend = this.errorPrepend + 'checkOverlap():';
    this.args = {action: 'check-overlap', pre_burn_id: pre_burn_id, start_date: start_date, end_date: end_date};
    var anchor = this;

    // Process the burn plan delete.
    $.post(this.target, this.args)
    .done(function(data) {
      console.log(data == 'true')
      if (data == 'true') {
        $('[name="my[start_date]"]').addClass('date-input-error');
        $('[name="my[end_date]"]').addClass('date-input-error');
        $('[name="my[start_date]"]').parent().append('<span id="sd_overlap_error" class="date-overlap-error-message date-error-icon"><span style="font-size: 11px;">Overlaps With Existing Burn <i style="margin-left: 2px;" class="glyphicon glyphicon-warning-sign"></i></span>');
        $('[name="my[end_date]"]').parent().append('<span id="ed_overlap_error" class="date-overlap-error-message date-error-icon"><span style="font-size: 11px;">Overlaps With Existing Burn <istyle="margin-left: 2px;" class="glyphicon glyphicon-warning-sign"></i></span>');
        return;
      }

      $('[name="my[start_date]"]').removeClass('date-input-error');
      $('[name="my[end_date]"]').removeClass('date-input-error');
      $('.date-overlap-error-message').remove();
      return;
    })
    .fail( function() {
        console.log(errorPrepend + " $.post failed.");
    });
}
