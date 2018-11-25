/*!
 *      Utah.gov Burn Documentation Ajax
 *      Tools & Actions for Burn Requests
 */

function BurnDocumentation()
{
    /**
     *    Burn Request Class
     */

    this.target = '/ajax/documentation.php';
    //this.refreshTrigger = '.accomplishment_refresh';
    this.formId = '#documentation_form';
    this.fieldsetPrepend = 'documentation_form_fs';
    this.toolbarSelector = '.documentation_form_tb';
    this.errorPrepend = 'BurnDocumentation.';
    this.args = {};

    this.refresh = function()
    {
        location.reload();
    };

    this.clear = function()
    {
        setTimeout( function(){
            clear_status_messages();
        }, 4000);
    };
}

BurnDocumentation.prototype.showForm = function (page, documentation_id)
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
        this.appendForm(page, documentation_id);
    }

    if ($(this.toolbarSelector)) {
        this.updateToolbar(page, documentation_id);
    } else {
        this.toolbar(page, documentation_id);
    }
}

BurnDocumentation.prototype.newForm = function(burn_project_id, accomplishment_id) 
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
        if (typeof accomplishment_id == 'undefined') {
            title = "Select an Accomplishment";
            this.args = {action: "accomplishment-selector", burn_project_id: burn_project_id};
        
            $.post(this.target, this.args)
            .done( function(data) {
               show_modal_small(data, title);
            })
            .fail( function() {
                console.log(errorPrepend + " Pre-Burn selector $.post failed.");
            });    
        } else {
            this.args = {action: "form-basic", page: page, accomplishment_id: accomplishment_id};

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

BurnDocumentation.prototype.editConfirmation = function(documentation_id)
{
    /**
     *  Initializes delete confirmation in small modal.
     *  Prevents accidental and disallowed burn plan deletes.
     */

    var errorPrepend = this.errorPrepend + 'editConfirmation():';
    var title = "Edit Confirmation";
    this.args = {action: "get-status", documentation_id: documentation_id};
    var anchor = this;

    $.post(this.target, this.args)
    .done( function(data) {
        var data = JSON.parse(data);
        data.status_id = parseInt(data.status_id);

        console.log(data);

        if (data.allow_edit) {
            anchor.editForm(documentation_id);    
        } else {
            title = "Burn Request Warning";
            anchor.args = {action: "edit-warning"};

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

BurnDocumentation.prototype.editForm = function(documentation_id) 
{
    /**
     *  Initializes a new update pre-burn form.
     */

    var errorPrepend = this.errorPrepend + 'editForm():';
    var page = 1;
    this.args = {action: "form-basic", page: page, documentation_id: documentation_id};
    var anchor = this;

    $.post(this.target, this.args)
    .done( function(data) {
        Interface.toggle();
        anchor.toolbar(page, documentation_id);
        $(formTarget).html(data);
    })
    .fail( function() {
        console.log(errorPrepend + " $.post failed.");
    });
}

BurnDocumentation.prototype.appendForm = function(page, documentation_id) 
{
    /**
     *  Appends fieldsets to the pre-burn form.
     */

    var errorPrepend = this.errorPrepend + 'appendForm():';
    this.args = {action: "form-basic", page: page, documentation_id: documentation_id}
    var anchor = this;

    $.post(this.target, this.args)
    .done( function(data) {
        $(anchor.formId).append(data);
    })
    .fail( function(data) {
        console.log(errorPrepend + " $.post failed.");
    });
}

BurnDocumentation.prototype.toolbar = function(page, documentation_id) 
{
    /**
     *  Initializes a new toolbar.
     */
    
    var errorPrepend = this.errorPrepend + 'toolbar():';
    this.args = {action: "form-toolbar", page: page, documentation_id: documentation_id}

    $.post(this.target, this.args)
    .done( function(data) {
        $(formTarget).prepend(data).append(data);
    })
    .fail( function() {
        console.log(errorPrepend + " $.post failed.");
    });
}

BurnDocumentation.prototype.updateToolbar = function(page, documentation_id) 
{
    /**
     *  Updates/appends an existing toolbar.
     */
    
    var errorPrepend = this.errorPrepend + 'updateToolbar():';
    this.args = {action: "form-toolbar", page: page, documentation_id: documentation_id};
    var anchor = this;

    $.post(this.target, this.args)
    .done( function(data) {
        $(anchor.toolbarSelector).html(data);
    })
    .fail( function() {
        console.log(errorPrepend + " $.post failed.");
    });
}

BurnDocumentation.prototype.save = function() 
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

BurnDocumentation.prototype.update = function(documentation_id)
{
    /**
     *  Update an existing pre-burn form.
     */

    var errorPrepend = this.errorPrepend + 'update():';
    var jsn = JSON.stringify($(this.formId).serializeArray());
    this.args = {action: "update", args: jsn, documentation_id: documentation_id};
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

BurnDocumentation.prototype.submitForm = function(documentation_id)
{
    /**
     *  Initializes the submit to Utah.gov confirmation form.
     */

    var errorPrepend = this.errorPrepend + 'submitForm():';
    this.args = {action: 'form-submit', documentation_id: documentation_id};
    var title = "Submit to Utah.gov";

    $.post(this.target, this.args)
    .done( function(data) {
        show_modal_small(data, title);
    })
    .fail( function() {
        console.log(errorPrepend + " $.post failed.");
    });
}

BurnDocumentation.prototype.submitToUtah = function(documentation_id)
{
    /**
     *  Submits the burn plan to Utah.gov.
     */

    var errorPrepend = this.errorPrepend + 'submitToUtah():';
    this.args = {action: 'submit', documentation_id: documentation_id};
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

BurnDocumentation.prototype.reviewDetail = function(review_id)
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

BurnDocumentation.prototype.conditionDetail = function(condition_id)
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

BurnDocumentation.prototype.ownerChange = function(documentation_id)
{
    /**
     *  Change BurnDocumentation Owner
     */

    var errorPrepend = this.errorPrepend + 'ownerChange():';
    var anchor = this;
    var formArray = $('#owner-change-form').serializeArray();
    var user_id = formArray[0].value; 
    var district_id = formArray[1].value; 
    this.args = {action: 'owner-change', documentation_id: documentation_id, user_id: user_id, district_id: district_id};

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

BurnDocumentation.prototype.ownerChangeForm = function(documentation_id)
{
    /**
     *  Change BurnDocumentation Owner Form
     */

    var errorPrepend = this.errorPrepend + 'ownerChangeForm():';
    var title = "Change Pre-Burn Owner";
    this.args = {action: "owner-change-form", documentation_id: documentation_id};

    $.post(this.target, this.args)
    .done( function(data) {
        show_modal_small(data, title);
    })
    .fail( function(data) {
        console.log(errorPrepend + " $.post failed.");
    });
}

BurnDocumentation.prototype.deleteRecord = function(documentation_id)
{
    /**
     *  Delete a pre-burn plan. Return success message.
     */

    var errorPrepend = this.errorPrepend + 'delete():';
    this.args = {action: 'delete', documentation_id: documentation_id};
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

BurnDocumentation.prototype.deleteConfirmation = function(documentation_id)
{
    /**
     *  Initializes delete confirmation in small modal.
     *  Prevents accidental and disallowed burn plan deletes.
     */

    var errorPrepend = this.errorPrepend + 'deleteConfirmation():';
    var title = "Delete Confirmation";
    this.args = {action: "delete-confirmation", documentation_id: documentation_id};

    $.post(this.target, this.args)
    .done( function(data) {
        show_modal_small(data, title);
    })
    .fail( function(data) {
        console.log(errorPrepend + " $.post failed.");
    });
}

BurnDocumentation.prototype.validate = function(documentation_id, refresh_function)
{
    /**
     *  Validate the burn request.
     */
    
    var errorPrepend = this.errorPrepend + 'validate():';
    var title = "Burn Request Completeness";
    this.args = {action: 'check-complete', documentation_id: documentation_id};
    var anchor = this;

    $.post(this.target, this.args)
    .done( function(data) {
        show_modal_small(data, title);
    })
    .fail( function() {
        console.log(errorPrepend + " $.post failed.");
    });
}

BurnDocumentation.prototype.observationForm = function(documentation_id)
{
    /**
     *  Initializes the Burn Documentation - observationForm form.
     */

    var errorPrepend = this.errorPrepend + 'observationForm():';
    var anchor = this;
    var title = "";
    var origin = $('#location').val();

    if (typeof burn_project_id == 'undefined') {
        burn_project_id = $("[name='my[documentation_id]']").val();
    }

    title = "Add an Observation";
    this.args = {action: "form-observation", documentation_id: documentation_id};
    
    $.post(this.target, this.args)
    .done( function(data) {
       show_modal(data, title);
    })
    .fail( function() {
        console.log(errorPrepend + " Observation Form selector $.post failed.");
    });    
}

BurnDocumentation.prototype.addObservation = function(targetId, observation)
{
    /** 
     *  Appends a observation item to the form.
     *  observationJSON - {name: name, location: , miles: , degrees: }
     */

    var errorPrepend = this.errorPrepend + 'addObservation():';
    var anchor = this;
    var observationForm = '#form-observation'
    var formArray = $(observationForm).serializeArray();
    if (typeof observation == 'undefined') {
        var observation = {photo: formArray[0].value, time: formArray[1].value, column_height: formArray[2].value, directional_flow_id: formArray[3].value, comments: formArray[4].value}; 
    }
    var observationJSON = JSON.stringify(observation);
    var target = '#' + targetId;
    var count = 0;

    hide_modal();

    $(target).find("[name='observation']").each(
        function() {
            count++;
        }
    )

    var html = "    <div class=\"col-sm-3 alert alert-dismissible\" style=\"border-color: rgb(204, 204, 204); padding: 8px; margin-right: 8px; font-size: 12px;\"> \
                      <button type=\"button\" class=\"close\" data-dismiss=\"alert\" style=\"right: 0px\"><span aria-hidden=\"true\">&times;</span><span class=\"sr-only\">Close</span></button> \
                      <strong>Time: " + observation.time + "</strong><br> Column Height (ft): " + observation.column_height + ". \
                      <input name=\"my[observations][" + count + "]\" type=\"hidden\" value='" + observationJSON + "'> \
                    </div> \
                ";

    $(target).append(html);

    return true;
}
