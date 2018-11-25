/*!
 *      Utah.gov Accomplishment Review Ajax Class
 *      Tools & Actions for Accomplishment Review
 *      Extends Accomplishment
 */

function BurnDocumentationReview()
{
    
    this.target = '/ajax/review_documentation.php';
    this.formId = '#review_form';
    this.errorPrepend = 'BurnDocumentationReview.';
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
    }
}

BurnDocumentationReview.prototype.reviewForm = function(documentation_id) 
{
    /**
     *  Initializes the burn review form.
     */

    var errorPrepend = this.errorPrepend + 'reviewForm():';
    this.args = {action: "form-review", documentation_id: documentation_id};
    var title = "Add Review";

    $.post(this.target, this.args)
    .done( function(data) {
        show_modal_small(data, title);
    })
    .fail( function() {
        console.log(errorPrepend + " $.post failed.");
    });
}

BurnDocumentationReview.prototype.editReviewForm = function(documentation_review_id) 
{
    /**
     *  Initializes the burn edit review form.
     */

    var errorPrepend = this.errorPrepend + 'newForm():';
    this.args = {action: "form-edit-review", documentation_review_id: documentation_review_id};
    var title = "Edit Review";

    $.post(this.target, this.args)
    .done( function(data) {
        show_modal_small(data, title);
    })
    .fail( function() {
        console.log(errorPrepend + " $.post failed.");
    });
}

BurnDocumentationReview.prototype.conditionForm = function(documentation_id) 
{
    /**
     *  Initializes the burn review form.
     */

    var errorPrepend = this.errorPrepend + 'conditionForm():';
    this.args = {action: "form-condition", documentation_id: documentation_id};
    var title = "Conditional Approval";

    $.post(this.target, this.args)
    .done( function(data) {
        show_modal_small(data, title);
    })
    .fail( function() {
        console.log(errorPrepend + " $.post failed.");
    });
}

BurnDocumentationReview.prototype.conditionEdit = function(burn_condition_id) 
{
    /**
     *  Initializes the burn review form.
     */

    var errorPrepend = this.errorPrepend + 'conditionEdit():';
    this.args = {action: "edit-condition", burn_condition_id: burn_condition_id};
    var title = "Edit Conditional Approval";

    $.post(this.target, this.args)
    .done( function(data) {
        show_modal_small(data, title);
    })
    .fail( function() {
        console.log(errorPrepend + " $.post failed.");
    });
}


BurnDocumentationReview.prototype.save = function(documentation_id)
{
    /**
     *  Saves the burn plan review comment.
     */

    var errorPrepend = this.errorPrepend + 'save():';
    var jsn = JSON.stringify($(this.formId).serializeArray());
    this.args = {action: "save-review", args: jsn};
    var anchor = this;

    $.post(this.target, this.args)
    .done( function(data) {
        $(formTarget).html(data);
        anchor.refresh();
    })
    .fail( function(data) {
        console.log(errorPrepend + " $.post failed.");
    });
}

BurnDocumentationReview.prototype.update = function(documentation_review_id)
{
    /**
     *  Saves the burn project review comment.
     */

    var errorPrepend = this.errorPrepend + 'update():';
    var jsn = JSON.stringify($(this.formId).serializeArray());
    this.args = {action: "update-review", args: jsn, documentation_review_id: documentation_review_id};
    var anchor = this;

    $.post(this.target, this.args)
    .done( function(data) {
        $(formTarget).html(data);
        anchor.refresh();
    })
    .fail( function(data) {
        console.log(errorPrepend + " $.post failed.");
    });
}

BurnDocumentationReview.prototype.saveCondition = function(documentation_id)
{
    /**
     *  Saves the burn plan review comment.
     */

    var errorPrepend = this.errorPrepend + 'saveCondition():';
    var jsn = JSON.stringify($(this.formId).serializeArray());
    this.args = {action: "save-condition", args: jsn};
    var anchor = this;

    $.post(this.target, this.args)
    .done( function(data) {
        $(formTarget).html(data);
        anchor.refresh();
    })
    .fail( function(data) {
        console.log(errorPrepend + " $.post failed.");
    });
}

BurnDocumentationReview.prototype.approveForm = function(documentation_id) 
{
    /**
     *  Initializes the burn plan approval form.
     */

    var errorPrepend = this.errorPrepend + 'approveForm():';
    this.args = {action: 'form-approve', documentation_id: documentation_id};
    var title = "Approve Burn Documentation";

    $.post(this.target, this.args)
    .done( function(data) {
        show_modal_small(data, title)
    })
    .fail( function() {
        console.log(errorPrepend + " $.post failed.");
    });
}

BurnDocumentationReview.prototype.approve = function(documentation_id)
{
    /**
     *  Approves the burn plan.
     */

    var errorPrepend = this.errorPrepend + 'approve():';
    this.args = {action: 'approve', documentation_id: documentation_id};
    var anchor = this;

    hide_modal();

    $.post(this.target, this.args)
    .done( function(data) {
        $(formTarget).html(data);
    })
    .fail( function() {
        console.log(errorPrepend + " $.post failed.");
    })
}

BurnDocumentationReview.prototype.preApproveForm = function(documentation_id) 
{
    /**
     *  Initializes the burn plan pre-approval form.
     */

    var errorPrepend = this.errorPrepend + 'preApproveForm():';
    this.args = {action: 'form-pre-approve', documentation_id: documentation_id};
    var title = "Pre-approve Burn Request";

    $.post(this.target, this.args)
    .done( function(data) {
        show_modal_small(data, title)
    })
    .fail( function() {
        console.log(errorPrepend + " $.post failed.");
    });
}

BurnDocumentationReview.prototype.preApprove = function(documentation_id)
{
    /**
     *  Pre-approves the burn plan.
     */

    var errorPrepend = this.errorPrepend + 'preApprove():';
    this.args = {action: 'pre-approve', documentation_id: documentation_id};
    var anchor = this;

    hide_modal();

    $.post(this.target, this.args)
    .done( function(data) {
        $(formTarget).html(data)
    })
    .fail( function() {
        console.log(errorPrepend + " $.post failed.");
    })
}

BurnDocumentationReview.prototype.disapproveForm = function(documentation_id) 
{
    /**
     *  Initializes the burn plan pre-approval form.
     */

    var errorPrepend = this.errorPrepend + 'disapproveForm():';
    this.args = {action: 'form-disapprove', documentation_id: documentation_id};
    var title = "Disapprove Pre-Burn Plan";

    $.post(this.target, this.args)
    .done( function(data) {
        show_modal_small(data, title)
    })
    .fail( function() {
        console.log(errorPrepend + " $.post failed.");
    });
}

BurnDocumentationReview.prototype.disapprove = function(documentation_id)
{
    /**
     *  Pre-approves the burn plan.
     */

    var errorPrepend = this.errorPrepend + 'disapprove():';
    this.args = {action: 'disapprove', documentation_id: documentation_id};
    var anchor = this;

    hide_modal();

    $.post(this.target, this.args)
    .done( function(data) {
        $(formTarget).html(data)
    })
    .fail( function() {
        console.log(errorPrepend + " $.post failed.");
    })
}

BurnDocumentationReview.prototype.approveAll = function(selected)
{
    for (var i = 0; i < selected.length; i++) {
        this.approve(selected[i]);
        console.log("Approved: " + selected[i]);
    }

    this.refresh();
}

BurnDocumentationReview.prototype.preApproveAll = function(selected)
{
    for (var i = 0; i < selected.length; i++) {
        this.preApprove(selected[i]);
        console.log("Pre-approved: " + selected[i]);
    }

    this.refresh();
}

BurnDocumentationReview.prototype.disapproveAll = function(selected)
{
    for (var i = 0; i < selected.length; i++) {
        this.disapprove(selected[i]);
        console.log("Disapproved: " + selected[i]);
    }

    this.refresh();
}