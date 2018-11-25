/*!
 *      Utah.gov Form Ajax
 *      Tools & Actions for Ajax Form Functions Actions
 */

function AjaxForms()
{
    /**
     *    Ajax Forms Class
     */

    this.target = '/ajax/form.php';
    //this.refreshTrigger = '.accomplishment_refresh';
    this.formId = '#pre_burn_form';
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

AjaxForms.prototype.modalForm = function()
{
    /**
     *  Modal Form
     */

    $.post(this.target, this.args)
    .done( function(data) {
       show_modal_small(data, title);
       google.maps.event.trigger(map, "resize");
    })
    .fail( function() {
        console.log(errorPrepend + ".modalForm $.post failed.");
    });
}

AjaxForms.prototype.appendRelatedHTML = function (target, title, body)
{
    /** 
     *  Append Related Field HTML
     */

    var target = '#receptors_pad';

    var html = '<a href="#" class="list-group-item"> \
            <h5 class="list-group-item-heading">' + title + '</h5> \
            <p class="list-group-item-text">' + body + '</p> \
        </a>'

    //var html = '<div class=\"panel panel-primary\" style=\"width: 25%\"> \
    //                <div class=\"panel-heading\"> \
    //                    ' + title + ' \
    //                </div> \
    //                <div class=\"panel-body\"> \
    //                    ' + body + ' \
    //                </div> \
    //            </div>'

    $(target).append(html);

    return html;
}

