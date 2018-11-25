/*!
 *      Utah.gov Burn Project Review Ajax Class
 *      Tools & Actions for Burn Plan Review
 *      Extends BurnProject
 */

function BurnProjectReview()
{
    
    this.target = '/ajax/review_project.php';
    this.formId = '#review_form';
    this.errorPrepend = 'BurnProjectReview.';
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

BurnProjectReview.prototype.reviewForm = function(burn_project_id) 
{
    /**
     *  Initializes the burn review form.
     */

    var errorPrepend = this.errorPrepend + 'newForm():';
    this.args = {action: "form-review", burn_project_id: burn_project_id};
    var title = "Add Review";

    $.post(this.target, this.args)
    .done( function(data) {
        show_modal_small(data, title);
    })
    .fail( function() {
        console.log(errorPrepend + " $.post failed.");
    });
}

BurnProjectReview.prototype.editReviewForm = function(burn_project_review_id) 
{
    /**
     *  Initializes the burn review form.
     */

    var errorPrepend = this.errorPrepend + 'newForm():';
    this.args = {action: "form-edit-review", burn_project_review_id: burn_project_review_id};
    var title = "Edit Review";

    $.post(this.target, this.args)
    .done( function(data) {
        show_modal_small(data, title);
    })
    .fail( function() {
        console.log(errorPrepend + " $.post failed.");
    });
}

BurnProjectReview.prototype.save = function(burn_project_id)
{
    /**
     *  Saves the burn project review comment.
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

BurnProjectReview.prototype.update = function(burn_project_review_id)
{
    /**
     *  Saves the burn project review comment.
     */

    var errorPrepend = this.errorPrepend + 'update():';
    var jsn = JSON.stringify($(this.formId).serializeArray());
    this.args = {action: "update-review", args: jsn, burn_project_review_id: burn_project_review_id};
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

BurnProjectReview.prototype.approveForm = function(burn_project_id) 
{
    /**
     *  Initializes the burn plan approval form.
     */

    var errorPrepend = this.errorPrepend + 'approveForm():';
    this.args = {action: 'form-approve', burn_project_id: burn_project_id};
    var title = "Approve Burn Project";

    $.post(this.target, this.args)
    .done( function(data) {
        show_modal_small(data, title)
    })
    .fail( function() {
        console.log(errorPrepend + " $.post failed.");
    });
}

BurnProjectReview.prototype.approve = function(burn_project_id)
{
    /**
     *  Approves the burn project.
     */

    var errorPrepend = this.errorPrepend + 'approve():';
    this.args = {action: 'approve', burn_project_id: burn_project_id};
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