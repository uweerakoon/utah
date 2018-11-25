/*!
 *      Utah.gov Burn Plan Ajax Class
 *      Tools & Actions for Burn Plans
 */

function BurnProject()
{
    /**
     *    Burn Plan Class.
     */

    this.target = '/ajax/project.php';
    //this.refreshTrigger = '.accomplishment_refresh';
    this.formId = '#project_form';
    this.fieldsetPrepend = 'project_form_fs';
    this.toolbarSelector = '.project_form_tb';
    this.errorPrepend = 'BurnProject.';
    this.args = {};

    var approved_status = 4;
    var archived_status = 5;

    this.getApprovedStatus = function()
    {
        return approved_status;
    }

    this.getArchivedStatus = function()
    {
        return archived_status;
    }

    this.refresh = function()
    {
        location.reload(false);
    };

    this.clear = function()
    {
        setTimeout( function(){
            clear_status_messages();
        }, 2000);
    };
}

/**
 *  Major Form Methods.
 */

BurnProject.prototype.showForm = function(page, burn_project_id)
{
    /**
     *  Shows or appends the selected burn form page.
     */

    var errorPrepend = this.errorPrepend + 'showForm():';
    var fieldset = this.fieldsetPrepend + page;

    // Hide the currently displayed fieldset/form page.
    $(this.formId).find('fieldset').hide();

    // Check for the form page, load (if necessary), and display.
    if (document.getElementById(fieldset)) {
        $('#' + fieldset).show();
    } else {
        this.appendForm(page, burn_project_id);
    }

    // Check for and update the toolbar according to the new page.
    if ($('.project_form_tb')) {
        this.updateToolbar(page, burn_project_id);
    } else {
        this.toolbar(page, burn_project_id);
    }
}

BurnProject.prototype.newForm = function(district_id)
{
    /**
     *  Initializes a new burn plan form.
     */

    var errorPrepend = this.errorPrepend + 'newForm():';
    var anchor = this;
    var title = "";
    var page = 1;

    if (typeof district_id == 'undefined') {
        // No district_id is specified, this is required for Burn Plans.
        var title = "Select a District";
        this.args = {action: "district-selector"};

        $.post(this.target, this.args)
        .done( function(data) {
           show_modal_small(data, title);
        })
        .fail( function() {
            console.log(errorPrepend + "District Selector $.post failed.");
        });
    } else {
        // Get a new form.
        this.args = {action: "form-basic", page: page, district_id: district_id};

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

BurnProject.prototype.editForm = function(burn_project_id)
{
    /**
     *  Initializes a new update burn plan form.
     */

    var errorPrepend = this.errorPrepend + 'editForm():';
    var page = 1;
    this.args = {action: 'form-basic', page: page, burn_project_id: burn_project_id};
    var anchor = this;

    hide_modal();
    
    $.post(this.target, this.args)
    .done( function(data) {
        Interface.toggle();
        anchor.toolbar(page, burn_project_id);
        $(formTarget).html(data);
    })
    .fail( function() {
        console.log(errorPrepend + " $.post failed.");
    });
}

BurnProject.prototype.editConfirmation = function(burn_project_id)
{
    /**
     *  Initializes delete confirmation in small modal.
     *  Prevents accidental and disallowed burn plan deletes.
     */

    var errorPrepend = this.errorPrepend + 'editConfirmation():';
    var title = "Edit Confirmation";
    this.args = {action: "get-status", burn_project_id: burn_project_id};
    var anchor = this;

    $.post(this.target, this.args)
    .done( function(data) {
        var data = JSON.parse(data);
        data.status_id = parseInt(data.status_id);

        console.log(data);

        if (data.allow_edit) {
            anchor.editForm(burn_project_id);    
        } else if (data.status_id == anchor.getApprovedStatus() || data.status_id == anchor.getArchivedStatus()) {
            title = "Modify to Draft Confirmation";
            anchor.args = {action: "edit-approved", burn_project_id: burn_project_id};

            $.post(anchor.target, anchor.args)
            .done( function(data) {
                show_modal_small(data, title);
            })
            .fail( function(data) {
                console.log(errorPrepend + " edit-approved $.post failed.");
            })
        } else { 
            anchor.args = {action: "edit-confirmation"};

            $.post(anchor.target, anchor.args)
            .done( function(data) {
                show_modal_small(data, title);
            })
            .fail( function() {
                console.log(errorPrepend + " edit-confirmation $.post failed.");
            });
        }
    })
    .fail( function(data) {
        console.log(errorPrepend + " get-status $.post failed.");
    });
}

BurnProject.prototype.toDraft = function(burn_project_id)
{
    /**
     *  Changes an approved plan to draft plan.
     *  After the confirmation, allows users to edit an approved.
     */

    var errorPrepend = this.errorPrepend + 'toDraft():';
    this.args = {action: "to-draft", burn_project_id: burn_project_id};
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

BurnProject.prototype.toArchive = function(burn_project_id)
{
    /**
     *  Changes an approved plan to an archived approved plan.
     *  This is just a cleanliness status.
     */

    var errorPrepend = this.errorPrepend + 'toArchive():';
    this.args = {action: "to-archive", burn_project_id: burn_project_id};
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

BurnProject.prototype.toApproved = function(burn_project_id)
{
    /**
     *  Changes an approved plan to an archived approved plan.
     *  This is just a cleanliness status.
     */

    var errorPrepend = this.errorPrepend + 'toApproved():';
    this.args = {action: "to-approved", burn_project_id: burn_project_id};
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

BurnProject.prototype.appendForm = function(page, burn_project_id)
{
    /**
     *  Appends fieldsets to the burn form.
     */

    var errorPrepend = this.errorPrepend + 'appendForm():';
    this.args = {action: "form-basic", page: page, burn_project_id: burn_project_id};
    var anchor = this;

    $.post(this.target, this.args)
    .done( function(data) {
        $(anchor.formId).append(data);
    })
    .fail( function() {
        console.log(errorPrepend + " $.post failed.");
    });
}

BurnProject.prototype.toolbar = function(page, burn_project_id)
{
    /**
     *  Builds a new toolbar for the specified burn plan form page.
     */

    var errorPrepend = this.errorPrepend + 'toolbar:';
    this.args = {action: "form-toolbar", page: page, burn_project_id: burn_project_id};

    $.post(this.target, this.args)
    .done( function(data) {
        $(formTarget).prepend(data).append(data);
    })
    .fail( function() {
        console.log(errorPrepend + " $.post failed.");
    });
}

BurnProject.prototype.updateToolbar = function(page, burn_project_id)
{
    /**
     *  Updates an existing toolbar(s) for the specified burn plan form page.
     */

    var errorPrepend = this.errorPrepend + 'updateToolbar():';
    this.args = {action: "form-toolbar", page: page, burn_project_id: burn_project_id};
    var anchor = this;

    $.post(this.target, this.args)
    .done( function(data) {
        $(anchor.toolbarSelector).html(data);
    })
    .fail( function() {
        console.log(errorPrepend + " $.post failed.");
    });
}

BurnProject.prototype.save = function()
{
    /**
     *  Save a new burn plan.
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
    });
}

BurnProject.prototype.update = function(burn_project_id)
{
    /**
     *  Update the specified burn plan.
     */

    var errorPrepend = this.errorPrepend + 'update():';
    var jsn = JSON.stringify($(this.formId).serializeArray());
    this.args = {action: "update", args: jsn, burn_project_id: burn_project_id};
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

BurnProject.prototype.submitForm = function(burn_project_id)
{
    /**
     *  Initializes the submit to Utah.gov confirmation form.
     */

    var errorPrepend = this.errorPrepend + 'submitForm():';
    this.args = {action: 'form-submit', burn_project_id: burn_project_id};
    var title = "Submit to Utah.gov";

    $.post(this.target, this.args)
    .done( function(data) {
        show_modal_small(data, title);
    })
    .fail( function() {
        console.log(errorPrepend + " $.post failed.");
    });
}

BurnProject.prototype.submitToUtah = function(burn_project_id)
{
    /**
     *  Submits the burn plan to Utah.gov.
     */

    var errorPrepend = this.errorPrepend + 'submitToUtah():';
    this.args = {action: 'submit', burn_project_id: burn_project_id};
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

BurnProject.prototype.validate = function(burn_project_id, refresh_function)
{
    /**
     *  Validate the burn plan.
     */
    
    var errorPrepend = this.errorPrepend + 'validate():';
    var title = "Burn Project Completeness";
    this.args = {action: 'check-complete', burn_project_id: burn_project_id};
    var anchor = this;

    $.post(this.target, this.args)
    .done( function(data) {
        show_modal_small(data, title);
    })
    .fail( function() {
        console.log(errorPrepend + " $.post failed.");
    });
}

BurnProject.prototype.deleteRecord = function(burn_project_id)
{
    /**
     *  Delete a burn plan. Return success message.
     */

    var errorPrepend = this.errorPrepend + 'delete():';
    this.args = {action: 'delete', burn_project_id: burn_project_id};
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

BurnProject.prototype.deleteConfirmation = function(burn_project_id)
{
    /**
     *  Initializes delete confirmation in small modal.
     *  Prevents accidental and disallowed burn plan deletes.
     */

    var errorPrepend = this.errorPrepend + 'deleteConfirmation():';
    var title = "Delete Confirmation";
    this.args = {action: "delete-confirmation", burn_project_id: burn_project_id};

    $.post(this.target, this.args)
    .done( function(data) {
        show_modal_small(data, title);
    })
    .fail( function(data) {
        console.log(errorPrepend + " $.post failed.");
    });
}

BurnProject.prototype.ownerChange = function(burn_project_id)
{
    /**
     *  Delete a burn plan. Return success message.
     */

    var errorPrepend = this.errorPrepend + 'ownerChange():';
    var anchor = this;
    var formArray = $('#owner-change-form').serializeArray();
    var user_id = formArray[0].value; 
    var district_id = formArray[1].value;
    this.args = {action: 'owner-change', burn_project_id: burn_project_id, user_id: user_id, district_id: district_id};

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

BurnProject.prototype.ownerChangeForm = function(burn_project_id)
{
    /**
     *  Initializes delete confirmation in small modal.
     *  Prevents accidental and disallowed burn plan deletes.
     */

    var errorPrepend = this.errorPrepend + 'deleteConfirmation():';
    var title = "Change Burn Project Owner";
    this.args = {action: "owner-change-form", burn_project_id: burn_project_id};

    $.post(this.target, this.args)
    .done( function(data) {
        show_modal_small(data, title);
    })
    .fail( function(data) {
        console.log(errorPrepend + " $.post failed.");
    });
}

BurnProject.prototype.register = function(burn_project_id)
{
    /**
     *  Delete a burn plan. Return success message.
     */

    var errorPrepend = this.errorPrepend + 'register():';
    this.args = {action: 'register', burn_project_id: burn_project_id};
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

BurnProject.prototype.registerSelect = function()
{
    /**
     *  Initializes the register select in small modal.
     *  Prevents accidental and disallowed burn plan deletes.
     */

    var errorPrepend = this.errorPrepend + 'registerSelect():';
    var title = "Registration Select";
    this.args = {action: "register-select", district_id: district_id};

    $.post(this.target, this.args)
    .done( function(data) {
        show_modal_small(data, title);
    })
    .fail( function(data) {
        console.log(errorPrepend + " $.post failed.");
    });    
}

BurnProject.prototype.registerConfirmation = function(burn_project_id)
{
    /**
     *  Initializes the register confirmation in small modal.
     *  Prevents accidental and disallowed burn plan deletes.
     */

    var errorPrepend = this.errorPrepend + 'registerConfirmation():';
    var title = "Registration Confirmation";
    this.args = {action: "register-confirmation", burn_project_id: burn_project_id};

    $.post(this.target, this.args)
    .done( function(data) {
        show_modal_small(data, title);
    })
    .fail( function(data) {
        console.log(errorPrepend + " $.post failed.");
    });    
}

BurnProject.prototype.reviewDetail = function(review_id)
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

BurnProject.prototype.lastPost = function()
{
    /**
     *  Returns the last args to be $.posted.
     */

    return this.args;
}
