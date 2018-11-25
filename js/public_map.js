/*!
 *      Utah.gov Public Map Control Class
 */

function PublicMap()
{
	/**
	 *
	 */

    this.target = '/ajax/public_map.php';
    //this.refreshTrigger = '.public_refresh';
    this.formId = '#map_fitler';
    this.fieldsetPrepend = 'public_form_fs';
    this.toolbarSelector = '.public_form_tb';
    this.errorPrepend = 'PublicMap.';
    this.args = {};

    this.getControl = function(i)
    {
        return controls[i];
    }
}

PublicMap.prototype.filterForm = function()
{
    /**
     *  Initializes the Public Map filter form.
     */

    var errorPrepend = this.errorPrepend + 'filterForm():';
    var anchor = this;
    var title = "Filter the Public Map";
    this.args = {action: "filter-form"};
    
    $.post(this.target, this.args)
    .done( function(data) {
       show_modal(data, title);
    })
    .fail( function() {
        console.log(errorPrepend + " $.post failed.");
    });   
}

PublicMap.prototype.filter = function()
{
    /** 
     *  Filter the map.
     */

    var errorPrepend = this.errorPrepend + 'filter():';
    var anchor = this;
    var jsn = JSON.stringify($(this.formId).serializeArray());

    this.setBurns(jsn);
}

PublicMap.prototype.setBurns = function(args)
{
    /** 
     *  Get a filtered set of burns.
     */

    var errorPrepend = this.errorPrepend + 'getBurns():';
    var anchor = this;
    this.args = {action: "get-burns", args: args};
    
    $.post(this.target, this.args)
    .done( function(data) {
        /** Hide the old markers **/
        for (var i = markers.length - 1; i >= 0; i--) {
            markers[i].setMap(null);
        };
        markers = [];

        /** Parse the JSON return **/
        var arr = JSON.parse(data);

        /** Set the markers on the map **/
        markers = setMarkers(map, arr['data']);

        /** Hide the modal form, assumes success. **/
        hide_modal();

        return markers;
    })
    .fail( function() {
        console.log(errorPrepend + " $.post failed.");
    });
}