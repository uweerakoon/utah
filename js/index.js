

function HomeManager()
{
    /**
     *    Home Page Manager
     */

    this.target = '/ajax/home.php';
    //this.refreshTrigger = '.accomplishment_refresh';
    this.formId = '#home_form';
    this.errorPrepend = 'HomeManager.';
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

HomeManager.prototype.form = function()
{
    var errorPrepend = this.errorPrepend + 'form():';
    this.args = {action: "form"};
    var anchor = this;

    $.post(this.target, this.args)
    .done( function(data) {
        $(formTarget).html(data);
    })
    .fail( function(data) {
        console.log(errorPrepend + " $.post failed.");
    })
}

HomeManager.prototype.updateForm = function(index_id) 
{
    /**
     *  Update an existing home page.
     */

    var errorPrepend = this.errorPrepend + 'updateForm():';
    this.args = {action: "form", index_id: index_id};
    var anchor = this;

    $.post(this.target, this.args)
    .done( function(data) {
        $(formTarget).html(data);
    })
    .fail( function(data) {
        console.log(errorPrepend + " $.post failed.");
    })
}

HomeManager.prototype.submit = function() 
{
    /**
     *  Save a new home page.
     */

    var errorPrepend = this.errorPrepend + 'submit():';
    var data = $(this.formId).serializeArray();
    data[2] = {name: 'my[tinymce]', value: tinyMCE.activeEditor.getContent()};
    var jsn = JSON.stringify(data);
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

HomeManager.prototype.update = function(index_id) 
{
    /**
     *  Save a new daily burn request.
     */

    var errorPrepend = this.errorPrepend + 'update():';
    var data = $(this.formId).serializeArray();
    data[2] = {name: 'my[tinymce]', value: tinyMCE.activeEditor.getContent()};
    var jsn = JSON.stringify(data);
    this.args = {action: "update", index_id: index_id, args: jsn};
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

HomeManager.prototype.formLevels = function()
{
    var errorPrepend = this.errorPrepend + 'formLevels():';
    this.args = {action: "form-levels"};
    var anchor = this;

    $.post(this.target, this.args)
    .done( function(data) {
        $(formTarget).html(data);
    })
    .fail( function(data) {
        console.log(errorPrepend + " $.post failed.");
    })
}

HomeManager.prototype.updateFormLevels = function(index_levels_id) 
{
    /**
     *  Update an existing home page.
     */

    var errorPrepend = this.errorPrepend + 'updateFormLevels():';
    this.args = {action: "form-levels", index_levels_id: index_levels_id};
    var anchor = this;

    $.post(this.target, this.args)
    .done( function(data) {
        $(formTarget).html(data);
    })
    .fail( function(data) {
        console.log(errorPrepend + " $.post failed.");
    })
}

HomeManager.prototype.submitLevels = function() 
{
    /**
     *  Save a new home page.
     */

    var errorPrepend = this.errorPrepend + 'submitLevels():';
    var data = $(this.formId).serializeArray();
    var jsn = JSON.stringify(data);
    this.args = {action: "save-levels", args: jsn};
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

HomeManager.prototype.updateLevels = function(index_levels_id) 
{
    /**
     *  Save a new daily burn request.
     */

    var errorPrepend = this.errorPrepend + 'updateLevels():';
    var data = $(this.formId).serializeArray();
    var jsn = JSON.stringify(data);
    this.args = {action: "update-levels", index_levels_id: index_levels_id, args: jsn};
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

HomeManager.prototype.formTeamFires = function()
{
    var errorPrepend = this.errorPrepend + 'formTeamFires():';
    this.args = {action: "form-team-fires"};
    var anchor = this;

    $.post(this.target, this.args)
    .done( function(data) {
        $(formTarget).html(data);
    })
    .fail( function(data) {
        console.log(errorPrepend + " $.post failed.");
    })
}

HomeManager.prototype.updateFormTeamFires = function(gbcc_team_fire_id) 
{
    /**
     *  Update an existing home page.
     */

    var errorPrepend = this.errorPrepend + 'updateFormTeamFires():';
    this.args = {action: "form-team-fires", gbcc_team_fire_id: gbcc_team_fire_id};
    var anchor = this;

    $.post(this.target, this.args)
    .done( function(data) {
        $(formTarget).html(data);
    })
    .fail( function(data) {
        console.log(errorPrepend + " $.post failed.");
    })
}

HomeManager.prototype.submitTeamFires = function() 
{
    /**
     *  Save a new home page.
     */

    var errorPrepend = this.errorPrepend + 'submitTeamFires():';
    var data = $(this.formId).serializeArray();
    data[2] = {name: 'my[tinymce]', value: tinyMCE.activeEditor.getContent()};
    var jsn = JSON.stringify(data);
    this.args = {action: "save-team-fires", args: jsn};
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

HomeManager.prototype.updateTeamFires = function(gbcc_team_fire_id) 
{
    /**
     *  Save a new daily burn request.
     */

    var errorPrepend = this.errorPrepend + 'updateTeamFires():';
    var data = $(this.formId).serializeArray();
    data[2] = {name: 'my[tinymce]', value: tinyMCE.activeEditor.getContent()};   
    var jsn = JSON.stringify(data);
    this.args = {action: "update-team-fires", gbcc_team_fire_id: gbcc_team_fire_id, args: jsn};
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

HomeManager.prototype.formLargeFires = function()
{
    var errorPrepend = this.errorPrepend + 'formLargeFires():';
    this.args = {action: "form-large-fires"};
    var anchor = this;

    $.post(this.target, this.args)
    .done( function(data) {
        $(formTarget).html(data);
    })
    .fail( function(data) {
        console.log(errorPrepend + " $.post failed.");
    })
}

HomeManager.prototype.updateFormLargeFires = function(gbcc_large_fire_id) 
{
    /**
     *  Update an existing home page.
     */

    var errorPrepend = this.errorPrepend + 'updateFormLargeFires():';
    this.args = {action: "form-large-fires", gbcc_large_fire_id: gbcc_large_fire_id};
    var anchor = this;

    $.post(this.target, this.args)
    .done( function(data) {
        $(formTarget).html(data);
    })
    .fail( function(data) {
        console.log(errorPrepend + " $.post failed.");
    })
}

HomeManager.prototype.submitLargeFires = function() 
{
    /**
     *  Save a new home page.
     */

    var errorPrepend = this.errorPrepend + 'submitLargeFires():';
    var data = $(this.formId).serializeArray();
    data[2] = {name: 'my[tinymce]', value: tinyMCE.activeEditor.getContent()};
    var jsn = JSON.stringify(data);
    this.args = {action: "save-large-fires", args: jsn};
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

HomeManager.prototype.updateLargeFires = function(gbcc_large_fire_id) 
{
    /**
     *  Save a new daily burn request.
     */

    var errorPrepend = this.errorPrepend + 'updateLargeFires():';
    var data = $(this.formId).serializeArray();
    data[2] = {name: 'my[tinymce]', value: tinyMCE.activeEditor.getContent()};   
    var jsn = JSON.stringify(data);
    this.args = {action: "update-large-fires", gbcc_large_fire_id: gbcc_large_fire_id, args: jsn};
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