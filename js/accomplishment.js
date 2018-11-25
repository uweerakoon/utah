/*!
 *      Utah.gov Accomplishment Ajax Class
 *      Tools & Actions for Daily Burn Accomplishments
 */

function Accomplishment()
{
    /**
     *    Accomplishment Class.
     */

    this.target = '/ajax/accomplishment.php';
    //this.refreshTrigger = '.accomplishment_refresh';
    this.formId = '#accomplishment_form';
    this.fieldsetPrepend = 'accomplishment_form_fs';
    this.toolbarId = '.accomplishment_form_tb';
    this.errorPrepend = 'Accomplishment.';
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

/**
 *  Major Form Methods.
 */

Accomplishment.prototype.showForm = function(page, burn_id, accomplishment_id)
{
    /**
     *  Changes the accomplishment form page and toolbar.
     *  Decides whether or not to load more from the server, or just display it.
     */

    var errorPrepend = this.errorPrepend + 'showForm():';
    var fieldset = this.fieldsetPrepend + page;

    if (burn_id == null) {
        burn_id = 'null';
    }

    // Hide the currently displayed fieldset/form page.
    $(this.formId).find('fieldset').hide();

    // Check for the form page, load (if necessary), and display.
    if (document.getElementById(fieldset)) {
        // The page is already loaded on the client side, but hidden.
        $('#' + fieldset).show();
    } else {
        // The page is not client side, load from server.
        this.appendForm(page, burn_id, accomplishment_id);
    }

    // Check for and update the toolbar according to the new page.
    if ($(this.toolbarId)) {
        this.updateToolbar(page, burn_id, accomplishment_id);
    } else {
        this.toolbar(page, burn_id, accomplishment_id);
    }
};

Accomplishment.prototype.newForm = function(burn_project_id, burn_id)
{
    /**
     *    Initialize a new Accomplishment form (blank/empty).
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
        .fail(function() {
            // Failure error message.
            console.log(errorPrepend + " Burn project selector $.post failed.");
        });
    } else {
        if (typeof burn_id == 'undefined') {
            title = "Select a Burn Request";
            this.args = {action: "burn-selector", burn_project_id: burn_project_id};

            $.post(this.target, this.args)
            .done( function(data) {
                show_modal_small(data, title);
            })
            .fail(function() {
                console.log(errorPrepend + " Burn request selector $.post failed.");
            });
        } else {
            this.args = {action: "form-basic", page: page, burn_id: burn_id};
        
            hide_modal();
        
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
    }
};

Accomplishment.prototype.editForm = function(accomplishment_id)
{
    /**
     *  Initialize an edit accomplishment form (filled with the specified accomplishment's values).
     */ 

    var errorPrepend = this.errorPrepend + 'editForm():';
    var page = 1;
    this.args = {action: "form-basic", page: page, accomplishment_id: accomplishment_id};
    var anchor = this;

    $.post(this.target, this.args)
    .done( function(data) {
        Interface.toggle();
        anchor.toolbar(page, 'null', accomplishment_id);
        $(formTarget).html(data);
    })
    .fail( function() {
        console.log(errorPrepend + " $.post failed.");
    });
};

Accomplishment.prototype.editConfirmation = function(accomplishment_id)
{
    /**
     *  Initializes delete confirmation in small modal.
     *  Prevents accidental and disallowed burn plan deletes.
     */

    var errorPrepend = this.errorPrepend + 'deleteConfirmation():';
    var title = "Edit Confirmation";
    this.args = {action: "get-status", accomplishment_id: accomplishment_id};
    var anchor = this;

    $.post(this.target, this.args)
    .done( function(data) {
        var data = JSON.parse(data);
        data.status_id = parseInt(data.status_id);

        if (data.allow_edit) {
            anchor.editForm(accomplishment_id);
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
};

Accomplishment.prototype.completeForm = function(accomplishment_id)
{
    /**
     *  Returns the completion form.
     */

    var errorPrepend = this.errorPrepend + 'completeForm():';
    var title = "Mark as Completed";
    this.args = {action: "form-complete", accomplishment_id: accomplishment_id};

    $.post(this.target, this.args)
    .done( function(data) {
        show_modal_small(data, title);
    })
    .fail( function() {
        console.log(errorPrepend + " $.post failed.")
    });
};

Accomplishment.prototype.appendForm = function(page, burn_id, accomplishment_id)
{
    /** 
     *  Append an initialized Accomplishment form with a new page.
     *  Only used when showForm(page) page is not already loaded client side.
     */

    var errorPrepend = this.errorPrepend + 'appendForm():';
    this.args = {action: "form-basic", page: page, burn_id: burn_id, accomplishment_id: accomplishment_id};
    var anchor = this;

    $.post(this.target, this.args)
    .done( function(data) {
        $(anchor.formId).append(data)
    })
    .fail( function() {
        console.log(errorPrepend + " $.post failed.")
    });
};

/**
 *  Form Toolbar Methods.
 */

Accomplishment.prototype.toolbar = function(page, burn_id, accomplishment_id)
{
    /** 
     *  Construct the accomplishment Toolbar.
     */

    var errorPrepend = this.errorPrepend + 'toolbar():';
    this.args = {action: "toolbar", page: page, burn_id: burn_id, accomplishment_id: accomplishment_id};
    var anchor = this;

    $.post(this.target, this.args)
    .done( function(data) {
        $(formTarget).prepend(data).append(data);
    })
    .fail( function() {
        console.log(errorPrepend + " $.post failed.");
    });
};

Accomplishment.prototype.updateToolbar = function(page, burn_id, accomplishment_id)
{
    /**
     *  Append an initialized toolbar for a new form page.
     */

    var errorPrepend = this.errorPrepend + 'appendToolbar():';
    this.args = {action: "toolbar", page: page, burn_id: burn_id, accomplishment_id: accomplishment_id};
    var anchor = this;

    $.post(this.target, this.args)
    .done( function(data) {
        $(anchor.toolbarId).html(data);
    })
    .fail( function() {
        console.log(errorPrepend + " $.post failed.")
    });
};

/**
 *  Miscellaneous Validations
 */

Accomplishment.prototype.deleteConfirmation = function(accomplishment_id)
{
    /**
     *  Initializes delete confirmation in small modal.
     *  Prevents accidental and disallowed burn plan deletes.
     */

    var errorPrepend = this.errorPrepend + 'deleteConfirmation():';
    var title = "Delete Confirmation";
    this.args = {action: "delete-confirmation", accomplishment_id: accomplishment_id};

    $.post(this.target, this.args)
    .done( function(data) {
        show_modal_small(data, title);
    })
    .fail( function(data) {
        console.log(errorPrepend + " $.post failed.");
    });
};

/**
 *  Accomplishment Database Methods.
 */

Accomplishment.prototype.save = function()
{
    /**
     *  Save a daily accomplishment form to the database.
     */ 

    var errorPrepend = this.errorPrepend + 'save():';
    var anchor = this;
    var form = JSON.stringify($(this.formId).serializeArray());
    this.args = {action: 'save', form: form}

    $.post(this.target, this.args)
    .done( function(data) {
        $(anchor.formId).html(data);
        anchor.refresh();
    })
    .fail( function() {
        console.log(errorPrepend + ' $.post failed.');
    });
};

Accomplishment.prototype.update = function(accomplishment_id)
{
    /**
     *  Update an existing accomplishment request.
     */

    var errorPrepend = this.errorPrepend + 'update():';
    var jsn = JSON.stringify($(this.formId).serializeArray());
    this.args = {action: "update", args: jsn, accomplishment_id: accomplishment_id};
    var anchor = this;

    $.post(this.target, this.args)
    .done( function(data) {
        $(anchor.formId).html(data);
        anchor.refresh();
    })
    .fail( function(data) {
        console.log(errorPrepend + " $.post failed.");
    });
};

Accomplishment.prototype.submitForm = function(accomplishment_id)
{
    /**
     *  Initializes the submit to Utah.gov confirmation form.
     */

    var errorPrepend = this.errorPrepend + 'submitForm():';
    this.args = {action: 'form-submit', accomplishment_id: accomplishment_id};
    var title = "Submit to Utah.gov";

    $.post(this.target, this.args)
    .done( function(data) {
        show_modal_small(data, title);
    })
    .fail( function() {
        console.log(errorPrepend + " $.post failed.");
    });
}

Accomplishment.prototype.submitToUtah = function(accomplishment_id)
{
    /**
     *  Submits the burn plan to Utah.gov.
     */

    var errorPrepend = this.errorPrepend + 'submitToUtah():';
    this.args = {action: 'submit', accomplishment_id: accomplishment_id};
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

Accomplishment.prototype.reviewDetail = function(review_id)
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

Accomplishment.prototype.validate = function(accomplishment_id, refresh_function)
{
    /**
     *  Validate the burn plan.
     */
    
    var errorPrepend = this.errorPrepend + 'validate():';
    var title = "Accomplishment Validation";
    this.args = {action: 'check-complete', accomplishment_id: accomplishment_id};
    var anchor = this;

    $.post(this.target, this.args)
    .done( function(data) {
        show_modal_small(data, title);
    })
    .fail( function() {
        console.log(errorPrepend + " $.post failed.");
    });
}

Accomplishment.prototype.ownerChange = function(accomplishment_id)
{
    /**
     *  Change Accomplishment Owner
     */

    var errorPrepend = this.errorPrepend + 'ownerChange():';
    var anchor = this;
    var formArray = $('#owner-change-form').serializeArray();
    var user_id = formArray[0].value; 
    var district_id = formArray[1].value; 
    this.args = {action: 'owner-change', accomplishment_id: accomplishment_id, user_id: user_id, district_id: district_id};

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

Accomplishment.prototype.ownerChangeForm = function(accomplishment_id)
{
    /**
     *  Change Accomplishment Owner Form
     */

    var errorPrepend = this.errorPrepend + 'ownerChangeForm():';
    var title = "Change Pre-Burn Owner";
    this.args = {action: "owner-change-form", accomplishment_id: accomplishment_id};

    $.post(this.target, this.args)
    .done( function(data) {
        show_modal_small(data, title);
    })
    .fail( function(data) {
        console.log(errorPrepend + " $.post failed.");
    });
}

Accomplishment.prototype.deleteRecord = function(accomplishment_id)
{
    /**
     *  Delete a burn plan. Return success message.
     */

    var errorPrepend = this.errorPrepend + 'delete():';
    this.args = {action: 'delete', accomplishment_id: accomplishment_id};
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
};

Accomplishment.prototype.complete = function(accomplishment_id)
{
    /**
     *  Delete a burn plan. Return success message.
     */

    var errorPrepend = this.errorPrepend + 'complete():';
    this.args = {action: 'complete', accomplishment_id: accomplishment_id};
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
};