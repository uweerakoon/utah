/*!
 *      Utah.gov Pre-Burn Review Ajax Class
 *      Tools & Actions for Pre-Burn Review
 *      Extends BurnProject
 */

function BurnReview()
{
    
    this.target = '/ajax/review_burn.php';
    this.formId = '#review_form';
    this.errorPrepend = 'BurnReview.';
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

BurnReview.prototype.reviewForm = function(burn_id) 
{
    /**
     *  Initializes the burn review form.
     */

    var errorPrepend = this.errorPrepend + 'reviewForm():';
    this.args = {action: "form-review", burn_id: burn_id};
    var title = "Add Review";

    $.post(this.target, this.args)
    .done( function(data) {
        show_modal_small(data, title);
    })
    .fail( function() {
        console.log(errorPrepend + " $.post failed.");
    });
}

BurnReview.prototype.editReviewForm = function(burn_review_id) 
{
    /**
     *  Initializes the burn edit review form.
     */

    var errorPrepend = this.errorPrepend + 'newForm():';
    this.args = {action: "form-edit-review", burn_review_id: burn_review_id};
    var title = "Edit Review";

    $.post(this.target, this.args)
    .done( function(data) {
        show_modal_small(data, title);
    })
    .fail( function() {
        console.log(errorPrepend + " $.post failed.");
    });
}

BurnReview.prototype.conditionForm = function(burn_id) 
{
    /**
     *  Initializes the burn review form.
     */

    var errorPrepend = this.errorPrepend + 'conditionForm():';
    this.args = {action: "form-condition", burn_id: burn_id};
    var title = "Conditional Approval";

    $.post(this.target, this.args)
    .done( function(data) {
        show_modal_small(data, title);
    })
    .fail( function() {
        console.log(errorPrepend + " $.post failed.");
    });
}

BurnReview.prototype.conditionEdit = function(burn_condition_id) 
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


BurnReview.prototype.save = function(burn_id)
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

BurnReview.prototype.update = function(burn_review_id)
{
    /**
     *  Saves the burn project review comment.
     */

    var errorPrepend = this.errorPrepend + 'update():';
    var jsn = JSON.stringify($(this.formId).serializeArray());
    this.args = {action: "update-review", args: jsn, burn_review_id: burn_review_id};
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

BurnReview.prototype.saveCondition = function(burn_id)
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

BurnReview.prototype.approveForm = function(burn_id) 
{
    /**
     *  Initializes the burn plan approval form.
     */

    var errorPrepend = this.errorPrepend + 'approveForm():';
    this.args = {action: 'form-approve', burn_id: burn_id};
    var title = "Approve Pre-Burn Plan";

    $.post(this.target, this.args)
    .done( function(data) {
        show_modal_small(data, title)
    })
    .fail( function() {
        console.log(errorPrepend + " $.post failed.");
    });
}

BurnReview.prototype.approve = function(burn_id)
{
    /**
     *  Approves the burn plan.
     */

    var errorPrepend = this.errorPrepend + 'approve():';
    this.args = {action: 'approve', burn_id: burn_id};
    var anchor = this;

    hide_modal();

    $.post(this.target, this.args)
    .done( function(data) {
        $(formTarget).html(data);
        anchor.refresh();
    })
    .fail( function() {
        console.log(errorPrepend + " $.post failed.");
    })
}

BurnReview.prototype.preApproveForm = function(burn_id) 
{
    /**
     *  Initializes the burn plan pre-approval form.
     */

    var errorPrepend = this.errorPrepend + 'preApproveForm():';
    this.args = {action: 'form-pre-approve', burn_id: burn_id};
    var title = "Pre-approve Burn Request";

    $.post(this.target, this.args)
    .done( function(data) {
        show_modal_small(data, title)
    })
    .fail( function() {
        console.log(errorPrepend + " $.post failed.");
    });
}

BurnReview.prototype.preApprove = function(burn_id)
{
    /**
     *  Pre-approves the burn plan.
     */

    var errorPrepend = this.errorPrepend + 'preApprove():';
    this.args = {action: 'pre-approve', burn_id: burn_id};
    var anchor = this;

    hide_modal();

    $.post(this.target, this.args)
    .done( function(data) {
        $(formTarget).html(data)
        anchor.refresh();
    })
    .fail( function() {
        console.log(errorPrepend + " $.post failed.");
    })
}

BurnReview.prototype.disapproveForm = function(burn_id) 
{
    /**
     *  Initializes the burn plan pre-approval form.
     */

    var errorPrepend = this.errorPrepend + 'disapproveForm():';
    this.args = {action: 'form-disapprove', burn_id: burn_id};
    var title = "Disapprove Pre-Burn Plan";

    $.post(this.target, this.args)
    .done( function(data) {
        show_modal_small(data, title)
    })
    .fail( function() {
        console.log(errorPrepend + " $.post failed.");
    });
}

BurnReview.prototype.disapprove = function(burn_id)
{
    /**
     *  Pre-approves the burn plan.
     */

    var errorPrepend = this.errorPrepend + 'disapprove():';
    this.args = {action: 'disapprove', burn_id: burn_id};
    var anchor = this;

    hide_modal();

    $.post(this.target, this.args)
    .done( function(data) {
        $(formTarget).html(data);
        anchor.refresh();
    })
    .fail( function() {
        console.log(errorPrepend + " $.post failed.");
    })
}

BurnReview.prototype.approveAll = function(selected)
{
    for (var i = 0; i < selected.length; i++) {
        this.approve(selected[i]);
        console.log("Approved: " + selected[i]);
    }

    this.refresh();
}

BurnReview.prototype.preApproveAll = function(selected)
{
    for (var i = 0; i < selected.length; i++) {
        this.preApprove(selected[i]);
        console.log("Pre-approved: " + selected[i]);
    }

    this.refresh();
}

BurnReview.prototype.disapproveAll = function(selected)
{
    for (var i = 0; i < selected.length; i++) {
        this.disapprove(selected[i]);
        console.log("Disapproved: " + selected[i]);
    }

    this.refresh();
}