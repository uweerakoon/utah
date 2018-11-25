/*!
 *      Utah.gov Pre-Burn Ajax
 *      Tools & Actions for Pre-Burn Burns
 */

function PreBurn()
{
    /**
     *    Pre-Burn Request Class
     */

    this.target = '/ajax/pre_burn.php';
    //this.refreshTrigger = '.accomplishment_refresh';
    this.formId = '#pre_burn_form';
    this.fieldsetPrepend = 'pre_burn_form_fs';
    this.toolbarSelector = '.pre_burn_form_tb';
    this.errorPrepend = 'PreBurn.';
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

PreBurn.prototype.showForm = function (page, pre_burn_id)
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
        this.appendForm(page, pre_burn_id);
    }

    if ($(this.toolbarSelector)) {
        this.updateToolbar(page, pre_burn_id);
    } else {
        this.toolbar(page, pre_burn_id);
    }
}

PreBurn.prototype.newForm = function(burn_project_id) 
{
    /**
     *  Initializes the pre-burn form.
     */

    var errorPrepend = this.errorPrepend + 'newForm():';
    var anchor = this;
    var title = "";
    var page = 1;

    if (typeof burn_project_id == 'undefined') {
        title = "Select a Burn Plan";
        this.args = {action: "burn-plan-selector"};
    
        $.post(this.target, this.args)
        .done( function(data) {
           show_modal_small(data, title);
        })
        .fail( function() {
            console.log(errorPrepend + " Burn Plan selector $.post failed.");
        });
    } else {
        this.args = {action: "form-basic", page: page, burn_project_id: burn_project_id};

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

PreBurn.prototype.editConfirmation = function(pre_burn_id)
{
    /**
     *  Initializes delete confirmation in small modal.
     *  Prevents accidental and disallowed burn plan deletes.
     */

    var errorPrepend = this.errorPrepend + 'editConfirmation():';
    var title = "Edit Confirmation";
    this.args = {action: "get-status", pre_burn_id: pre_burn_id};
    var anchor = this;

    $.post(this.target, this.args)
    .done( function(data) {
        var data = JSON.parse(data);
        data.status_id = parseInt(data.status_id);

        console.log(data);

        if (data.allow_edit) {
            anchor.editForm(pre_burn_id);    
        } else {
            title = "Pre-Burn Warning";
            anchor.args = {action: "edit-warning", pre_burn_id: pre_burn_id};

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

PreBurn.prototype.editForm = function(pre_burn_id) 
{
    /**
     *  Initializes a new update pre-burn form.
     */

    var errorPrepend = this.errorPrepend + 'editForm():';
    var page = 1;
    this.args = {action: "form-basic", page: page, pre_burn_id: pre_burn_id};
    var anchor = this;

    $.post(this.target, this.args)
    .done( function(data) {
        Interface.toggle();
        anchor.toolbar(page, pre_burn_id);
        $(formTarget).html(data);
    })
    .fail( function() {
        console.log(errorPrepend + " $.post failed.");
    });
}

PreBurn.prototype.appendForm = function(page, pre_burn_id) 
{
    /**
     *  Appends fieldsets to the pre-burn form.
     */

    var errorPrepend = this.errorPrepend + 'appendForm():';
    this.args = {action: "form-basic", page: page, pre_burn_id: pre_burn_id}
    var anchor = this;

    $.post(this.target, this.args)
    .done( function(data) {
        console.log(errorPrepend + " data:");
        console.log(data);
        $(anchor.formId).append(data);
    })
    .fail( function(data) {
        console.log(errorPrepend + " $.post failed.");
    });
}

PreBurn.prototype.toolbar = function(page, pre_burn_id) 
{
    /**
     *  Initializes a new toolbar.
     */
    
    var errorPrepend = this.errorPrepend + 'toolbar():';
    this.args = {action: "form-toolbar", page: page, pre_burn_id: pre_burn_id}

    $.post(this.target, this.args)
    .done( function(data) {
        $(formTarget).prepend(data).append(data);
    })
    .fail( function() {
        console.log(errorPrepend + " $.post failed.");
    });
}

PreBurn.prototype.updateToolbar = function(page, pre_burn_id) 
{
    /**
     *  Updates/appends an existing toolbar.
     */
    
    var errorPrepend = this.errorPrepend + 'updateToolbar():';
    this.args = {action: "form-toolbar", page: page, pre_burn_id: pre_burn_id};
    var anchor = this;

    $.post(this.target, this.args)
    .done( function(data) {
        $(anchor.toolbarSelector).html(data);
    })
    .fail( function() {
        console.log(errorPrepend + " $.post failed.");
    });
}

PreBurn.prototype.save = function() 
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

PreBurn.prototype.update = function(pre_burn_id)
{
    /**
     *  Update an existing pre-burn form.
     */

    var errorPrepend = this.errorPrepend + 'update():';
    var jsn = JSON.stringify($(this.formId).serializeArray());
    this.args = {action: "update", args: jsn, pre_burn_id: pre_burn_id};
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

PreBurn.prototype.submitForm = function(pre_burn_id)
{
    /**
     *  Initializes the submit to Utah.gov confirmation form.
     */

    var errorPrepend = this.errorPrepend + 'submitForm():';
    this.args = {action: 'form-submit', pre_burn_id: pre_burn_id};
    var title = "Submit to Utah.gov";

    $.post(this.target, this.args)
    .done( function(data) {
        show_modal_small(data, title);
    })
    .fail( function() {
        console.log(errorPrepend + " $.post failed.");
    });
}

PreBurn.prototype.submitToUtah = function(pre_burn_id)
{
    /**
     *  Submits the burn plan to Utah.gov.
     */

    var errorPrepend = this.errorPrepend + 'submitToUtah():';
    this.args = {action: 'submit', pre_burn_id: pre_burn_id};
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

PreBurn.prototype.reviewDetail = function(review_id)
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

PreBurn.prototype.conditionDetail = function(condition_id)
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

PreBurn.prototype.deleteRecord = function(pre_burn_id)
{
    /**
     *  Delete a pre-burn plan. Return success message.
     */

    var errorPrepend = this.errorPrepend + 'delete():';
    this.args = {action: 'delete', pre_burn_id: pre_burn_id};
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

PreBurn.prototype.deleteConfirmation = function(pre_burn_id)
{
    /**
     *  Initializes delete confirmation in small modal.
     *  Prevents accidental and disallowed burn plan deletes.
     */

    var errorPrepend = this.errorPrepend + 'deleteConfirmation():';
    var title = "Delete Confirmation";
    this.args = {action: "delete-confirmation", pre_burn_id: pre_burn_id};

    $.post(this.target, this.args)
    .done( function(data) {
        show_modal_small(data, title);
    })
    .fail( function(data) {
        console.log(errorPrepend + " $.post failed.");
    });
}

PreBurn.prototype.revisionForm = function(pre_burn_id)
{
    /**
     *  Revision/Renewal Form
     */

    var errorPrepend = this.errorPrepend + 'revisionForm():';
    var title = "Pre-Burn Revision & Renewal";
    this.args = {action: "form-revision", pre_burn_id: pre_burn_id};

    $.post(this.target, this.args)
    .done( function(data) {
        show_modal_small(data, title);
    })
    .fail( function(data) {
        console.log(errorPrepend + " $.post failed.");
    });
}

PreBurn.prototype.renew = function(pre_burn_id)
{
    /**
     *  Renew Pre-Burn
     */

    var errorPrepend = this.errorPrepend + 'renew():';
    var anchor = this;
    var title = "Renew Pre-Burn";
    this.args = {action: "renew", pre_burn_id: pre_burn_id};

    hide_modal();

    $.post(this.target, this.args)
    .done( function(data) {
        $(formTarget).append(data);
        anchor.refresh();
    })
    .fail( function(data) {
        console.log(errorPrepend + " $.post failed.");
    });
}

PreBurn.prototype.revise = function(pre_burn_id, type)
{
    /**
     *  Revise Pre-Burn
     */

    var errorPrepend = this.errorPrepend + 'revise():';
    var anchor = this;
    var title = "Revise Pre-Burn";
    this.args = {action: "revise", pre_burn_id: pre_burn_id, type: type};

    hide_modal();

    $.post(this.target, this.args)
    .done( function(data) {
        //anchor.refresh();
        var result = $.parseJSON(data);
        anchor.editConfirmation(parseInt(result.pre_burn_id));
    })
    .fail( function(data) {
        console.log(errorPrepend + " $.post failed.");
    });
}

PreBurn.prototype.ownerChange = function(pre_burn_id)
{
    /**
     *  Delete a burn plan. Return success message.
     */

    var errorPrepend = this.errorPrepend + 'ownerChange():';
    var anchor = this;
    var formArray = $('#owner-change-form').serializeArray();
    var user_id = formArray[0].value; 
    var district_id = formArray[1].value; 
    this.args = {action: 'owner-change', pre_burn_id: pre_burn_id, user_id: user_id, district_id: district_id};

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

PreBurn.prototype.ownerChangeForm = function(pre_burn_id)
{
    /**
     *  Initializes delete confirmation in small modal.
     *  Prevents accidental and disallowed burn plan deletes.
     */

    var errorPrepend = this.errorPrepend + 'ownerChangeForm():';
    var title = "Change Pre-Burn Owner";
    this.args = {action: "owner-change-form", pre_burn_id: pre_burn_id};

    $.post(this.target, this.args)
    .done( function(data) {
        show_modal_small(data, title);
    })
    .fail( function(data) {
        console.log(errorPrepend + " $.post failed.");
    });
}

PreBurn.prototype.validate = function(pre_burn_id, refresh_function)
{
    /**
     *  Validate the pre-burn burn plan.
     */
    
    var errorPrepend = this.errorPrepend + 'validate():';
    var title = "Pre-Burn Completeness";
    this.args = {action: 'check-complete', pre_burn_id: pre_burn_id};
    var anchor = this;

    $.post(this.target, this.args)
    .done( function(data) {
        show_modal_small(data, title);
    })
    .fail( function() {
        console.log(errorPrepend + " $.post failed.");
    });
}

PreBurn.prototype.receptorForm = function(burn_project_id)
{
    /**
     *  Initializes the pre-burn form.
     */

    var errorPrepend = this.errorPrepend + 'receptorForm():';
    var anchor = this;
    var title = "";
    var origin = $('#location').val();

    if (typeof burn_project_id == 'undefined') {
        burn_project_id = $("[name='my[burn_project_id]']").val();
    }

    title = "Add a Sensitive Receptor";
    this.args = {action: "form-receptor", origin: origin, burn_project_id: burn_project_id};
    
    $.post(this.target, this.args)
    .done( function(data) {
       show_modal(data, title);
    })
    .fail( function() {
        console.log(errorPrepend + " Receptor Form selector $.post failed.");
    });    
}

PreBurn.prototype.addReceptor = function(targetId, receptor)
{
    /** 
     *  Appends a receptor item to the form.
     *  receptorJSON - {name: name, location: , miles: , degrees: }
     */

    var errorPrepend = this.errorPrepend + 'addReceptor():';
    var anchor = this;
    var formArray = $('#form-receptor').serializeArray();
    if (typeof receptor == 'undefined') {
        var receptor = {name: formArray[0].value, location: formArray[3].value, miles: formArray[1].value, degrees: formArray[2].value}; 
    }
    var receptorJSON = JSON.stringify(receptor);
    var target = '#' + targetId;
    var count = 0;

    hide_modal();

    $(target).find("[name='receptor']").each(
        function() {
            count++;
        }
    )

    var html = "    <div class=\"col-sm-3 alert alert-dismissible\" style=\"border-color: rgb(204, 204, 204); padding: 8px; margin-right: 8px; font-size: 12px;\"> \
                      <button type=\"button\" class=\"close\" data-dismiss=\"alert\" style=\"right: 0px\"><span aria-hidden=\"true\">&times;</span><span class=\"sr-only\">Close</span></button> \
                      <strong>" + receptor.name + "</strong><br> Dist: " + Math.round(receptor.miles*1)/1 + " mi., Deg: " + Math.round(receptor.degrees*1)/1 + "&deg;. \
                      <input name=\"my[receptors][" + count + "]\" type=\"hidden\" value='" + receptorJSON + "'> \
                    </div> \
                ";

    $(target).append(html);

    return true;
}