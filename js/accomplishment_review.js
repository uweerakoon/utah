/*!
 *      Utah.gov Accomplishment Review Ajax Class
 *      Tools & Actions for Accomplishment Review
 *      Extends Accomplishment
 */

function AccomplishmentReview()
{
    
    this.target = '/ajax/review_accomplishment.php';
    this.formId = '#review_form';
    this.errorPrepend = 'AccomplishmentReview.';
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
    }
}

AccomplishmentReview.prototype.reviewForm = function(accomplishment_id) 
{
    /**
     *  Initializes the burn review form.
     */

    var errorPrepend = this.errorPrepend + 'reviewForm():';
    this.args = {action: "form-review", accomplishment_id: accomplishment_id};
    var title = "Add Review";

    $.post(this.target, this.args)
    .done( function(data) {
        show_modal_small(data, title);
    })
    .fail( function() {
        console.log(errorPrepend + " $.post failed.");
    });
}

AccomplishmentReview.prototype.editReviewForm = function(accomplishment_review_id) 
{
    /**
     *  Initializes the burn edit review form.
     */

    var errorPrepend = this.errorPrepend + 'newForm():';
    this.args = {action: "form-edit-review", accomplishment_review_id: accomplishment_review_id};
    var title = "Edit Review";

    $.post(this.target, this.args)
    .done( function(data) {
        show_modal_small(data, title);
    })
    .fail( function() {
        console.log(errorPrepend + " $.post failed.");
    });
}

AccomplishmentReview.prototype.conditionForm = function(accomplishment_id) 
{
    /**
     *  Initializes the burn review form.
     */

    var errorPrepend = this.errorPrepend + 'conditionForm():';
    this.args = {action: "form-condition", accomplishment_id: accomplishment_id};
    var title = "Conditional Approval";

    $.post(this.target, this.args)
    .done( function(data) {
        show_modal_small(data, title);
    })
    .fail( function() {
        console.log(errorPrepend + " $.post failed.");
    });
}

AccomplishmentReview.prototype.conditionEdit = function(burn_condition_id) 
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


AccomplishmentReview.prototype.save = function(accomplishment_id)
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

AccomplishmentReview.prototype.update = function(accomplishment_review_id)
{
    /**
     *  Saves the burn project review comment.
     */

    var errorPrepend = this.errorPrepend + 'update():';
    var jsn = JSON.stringify($(this.formId).serializeArray());
    this.args = {action: "update-review", args: jsn, accomplishment_review_id: accomplishment_review_id};
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

AccomplishmentReview.prototype.saveCondition = function(accomplishment_id)
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

AccomplishmentReview.prototype.approveForm = function(accomplishment_id) 
{
    /**
     *  Initializes the burn plan approval form.
     */

    var errorPrepend = this.errorPrepend + 'approveForm():';
    this.args = {action: 'form-approve', accomplishment_id: accomplishment_id};
    var title = "Approve Pre-Burn Plan";

    $.post(this.target, this.args)
    .done( function(data) {
        show_modal_small(data, title)
    })
    .fail( function() {
        console.log(errorPrepend + " $.post failed.");
    });
}

AccomplishmentReview.prototype.approve = function(accomplishment_id)
{
    /**
     *  Approves the burn plan.
     */

    var errorPrepend = this.errorPrepend + 'approve():';
    this.args = {action: 'approve', accomplishment_id: accomplishment_id};
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

AccomplishmentReview.prototype.preApproveForm = function(accomplishment_id) 
{
    /**
     *  Initializes the burn plan pre-approval form.
     */

    var errorPrepend = this.errorPrepend + 'preApproveForm():';
    this.args = {action: 'form-pre-approve', accomplishment_id: accomplishment_id};
    var title = "Pre-approve Burn Request";

    $.post(this.target, this.args)
    .done( function(data) {
        show_modal_small(data, title)
    })
    .fail( function() {
        console.log(errorPrepend + " $.post failed.");
    });
}

AccomplishmentReview.prototype.preApprove = function(accomplishment_id)
{
    /**
     *  Pre-approves the burn plan.
     */

    var errorPrepend = this.errorPrepend + 'preApprove():';
    this.args = {action: 'pre-approve', accomplishment_id: accomplishment_id};
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

AccomplishmentReview.prototype.disapproveForm = function(accomplishment_id) 
{
    /**
     *  Initializes the burn plan pre-approval form.
     */

    var errorPrepend = this.errorPrepend + 'disapproveForm():';
    this.args = {action: 'form-disapprove', accomplishment_id: accomplishment_id};
    var title = "Disapprove Pre-Burn Plan";

    $.post(this.target, this.args)
    .done( function(data) {
        show_modal_small(data, title)
    })
    .fail( function() {
        console.log(errorPrepend + " $.post failed.");
    });
}

AccomplishmentReview.prototype.disapprove = function(accomplishment_id)
{
    /**
     *  Pre-approves the burn plan.
     */

    var errorPrepend = this.errorPrepend + 'disapprove():';
    this.args = {action: 'disapprove', accomplishment_id: accomplishment_id};
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

AccomplishmentReview.prototype.approveAll = function(selected)
{
    for (var i = 0; i < selected.length; i++) {
        this.approve(selected[i]);
        console.log("Approved: " + selected[i]);
    }

    this.refresh();
}

AccomplishmentReview.prototype.preApproveAll = function(selected)
{
    for (var i = 0; i < selected.length; i++) {
        this.preApprove(selected[i]);
        console.log("Pre-approved: " + selected[i]);
    }

    this.refresh();
}

AccomplishmentReview.prototype.disapproveAll = function(selected)
{
    for (var i = 0; i < selected.length; i++) {
        this.disapprove(selected[i]);
        console.log("Disapproved: " + selected[i]);
    }

    this.refresh();
}