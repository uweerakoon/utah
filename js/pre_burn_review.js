/*!
 *      Utah.gov Pre-Burn Review Ajax Class
 *      Tools & Actions for Pre-Burn Review
 *      Extends BurnProject
 */

function PreBurnReview()
{
    
    this.target = '/ajax/review_pre_burn.php';
    this.formId = '#review_form';
    this.errorPrepend = 'PreBurnReview.';
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

PreBurnReview.prototype.reviewForm = function(pre_burn_id) 
{
    /**
     *  Initializes the pre-burn review form.
     */

    var errorPrepend = this.errorPrepend + 'reviewForm():';
    this.args = {action: "form-review", pre_burn_id: pre_burn_id};
    var title = "Add Review";

    $.post(this.target, this.args)
    .done( function(data) {
        show_modal_small(data, title);
    })
    .fail( function() {
        console.log(errorPrepend + " $.post failed.");
    });
}

PreBurnReview.prototype.editReviewForm = function(pre_burn_review_id) 
{
    /**
     *  Initializes the pre-burn edit review form.
     */

    var errorPrepend = this.errorPrepend + 'newForm():';
    this.args = {action: "form-edit-review", pre_burn_review_id: pre_burn_review_id};
    var title = "Edit Review";

    $.post(this.target, this.args)
    .done( function(data) {
        show_modal_small(data, title)
    })
    .fail( function() {
        console.log(errorPrepend + " $.post failed.");
    });
}

PreBurnReview.prototype.conditionForm = function(pre_burn_id) 
{
    /**
     *  Initializes the burn review form.
     */

    var errorPrepend = this.errorPrepend + 'conditionForm():';
    this.args = {action: "form-condition", pre_burn_id: pre_burn_id};
    var title = "Conditional Approval";

    $.post(this.target, this.args)
    .done( function(data) {
        show_modal_small(data, title);
    })
    .fail( function() {
        console.log(errorPrepend + " $.post failed.");
    });
}

PreBurnReview.prototype.conditionEdit = function(pre_burn_condition_id) 
{
    /**
     *  Initializes the burn review form.
     */

    var errorPrepend = this.errorPrepend + 'conditionEdit():';
    this.args = {action: "edit-condition", pre_burn_condition_id: pre_burn_condition_id};
    var title = "Edit Conditional Approval";

    $.post(this.target, this.args)
    .done( function(data) {
        show_modal_small(data, title);
    })
    .fail( function() {
        console.log(errorPrepend + " $.post failed.");
    });
}


PreBurnReview.prototype.save = function(pre_burn_id)
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

PreBurnReview.prototype.update = function(pre_burn_review_id)
{
    /**
     *  Saves the burn project review comment.
     */

    var errorPrepend = this.errorPrepend + 'update():';
    var jsn = JSON.stringify($(this.formId).serializeArray());
    this.args = {action: "update-review", args: jsn, pre_burn_review_id: pre_burn_review_id};
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

PreBurnReview.prototype.saveCondition = function(pre_burn_id)
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

PreBurnReview.prototype.approveForm = function(pre_burn_id) 
{
    /**
     *  Initializes the burn plan approval form.
     */

    var errorPrepend = this.errorPrepend + 'approveForm():';
    this.args = {action: 'form-approve', pre_burn_id: pre_burn_id};
    var title = "Approve Pre-Burn Plan";

    $.post(this.target, this.args)
    .done( function(data) {
        show_modal_small(data, title)
    })
    .fail( function() {
        console.log(errorPrepend + " $.post failed.");
    });
}

PreBurnReview.prototype.approve = function(pre_burn_id)
{
    /**
     *  Approves the burn plan.
     */

    var errorPrepend = this.errorPrepend + 'approve():';
    this.args = {action: 'approve', pre_burn_id: pre_burn_id};
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

PreBurnReview.prototype.disapproveForm = function(pre_burn_id) 
{
    /**
     *  Initializes the burn plan pre-approval form.
     */

    var errorPrepend = this.errorPrepend + 'disapproveForm():';
    this.args = {action: 'form-disapprove', pre_burn_id: pre_burn_id};
    var title = "Disapprove Pre-Burn Plan";

    $.post(this.target, this.args)
    .done( function(data) {
        show_modal_small(data, title)
    })
    .fail( function() {
        console.log(errorPrepend + " $.post failed.");
    });
}

PreBurnReview.prototype.disapprove = function(pre_burn_id)
{
    /**
     *  Pre-approves the burn plan.
     */

    var errorPrepend = this.errorPrepend + 'disapprove():';
    this.args = {action: 'disapprove', pre_burn_id: pre_burn_id};
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

PreBurnReview.prototype.approveAll = function(selected)
{
    for (var i = 0; i < selected.length; i++) {
        this.approve(selected[i]);
        console.log("Approved: " + selected[i]);
    }

    this.refresh();
}

PreBurnReview.prototype.preApproveAll = function(selected)
{
    for (var i = 0; i < selected.length; i++) {
        this.preApprove(selected[i]);
        console.log("Pre-approved: " + selected[i]);
    }

    this.refresh();
}

PreBurnReview.prototype.disapproveAll = function(selected)
{
    for (var i = 0; i < selected.length; i++) {
        this.disapprove(selected[i]);
        console.log("Disapproved: " + selected[i]);
    }

    this.refresh();
}