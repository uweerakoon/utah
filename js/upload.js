

function Upload()
{
	this.target = '/ajax/upload.php';
    this.formId = '#upload_file_output';
    this.errorPrepend = 'Upload.';
    this.args = {};
}

Upload.prototype.form = function(refTable, id)
{
    /**
     *	Upload files form	
     */

    var errorPrepend = this.errorPrepend + "form():";
    var title = "Upload File";
    this.args = {refTable: refTable, id: id};
    
    $.post(this.target, this.args)
	.done(function(data) {
	    show_modal(data, title);
	})
	.fail(function() {
		console.log(" $.post failed.");
	});
};

Upload.prototype.upload = function()
{
    /**
     *	Process the file upload submit.
     */

    var errorPrepend = this.errorPrepend + "upload():";

    var options = {
	    target:  this.formId,
	    beforeSubmit:  Upload.browserCheck,
	    success:       Upload.success,
	    uploadProgress: Upload.progress,
	    resetForm: true
	};

	// store the context 
	var diag = this;
	// get the form information
	var jsn = JSON.stringify($('.modal-body').find('form').serializeArray())
	// and the file data
	$('.modal-body').find('form').ajaxSubmit(options)
	
	return false;
}

Upload.prototype.success = function() 
{
	/**
	 *	Upload successful event.
	 */

	console.log("Upload successful");

	$('.modal-body').html("<div class=\"alert alert-success\"> \
			<p>The file has been uploaded.</p> \
		</div> \
		<button class=\"btn btn-default\" onclick=\"cancel_modal(); location.reload();\">Close</button>");
	
}

Upload.prototype.browserCheck = function()
{
   	/**
   	 *	Check browser support.
   	 */   	

   	if (window.File && window.FileReader && window.FileList && window.Blob) {
		//Browser is supported.
   	} else {
       alert("Please upgrade your browser, it does not support the file upload feature.");
    }
}

Upload.prototype.check = function()
{
	/**
	 *	Check/validate the file before upload.
	 */

	if (window.File && window.FileReader && window.FileList && window.Blob) {
		var fsize = $('#upload_file')[0].files[0].size; //get file size
        var ftype = $('#upload_file')[0].files[0].type; // get file type
        //allow file types
		switch(ftype) {
			case 'image/png':
            case 'image/gif':
            case 'image/jpeg':
            case 'image/pjpeg':
            case 'text/plain':
            case 'text/html':
            case 'application/x-zip-compressed':
            case 'application/pdf':
            case 'application/msword':
            case 'application/vnd.ms-excel':
            case 'video/mp4':
            break;
            default:
            	$(".modal-body").prepend("<b>"+ftype+"</b> is not supported.");
			return false
		}
   
		//Allowed file size is less than 30 MB (1048576 = 1 mb)
		if(fsize > 31457280) {
	        alert("<b>"+fsize +"</b> is too large.<br/>File should be less than 30 MB.");
	        return false
		}
	} else {
    	//Error for older unsupported browsers that doesn't support HTML5 File API
		alert("Please upgrade your browser, it does not support the file upload feature.");
		return false
	}
}

Upload.prototype.progress = function(event, position, total, percentComplete) 
{
	/**
	 *	Run the the upload progress bar.
	 */

	var parent = '.modal-body';

	$(parent).find('.progress').show();
	$(parent).find('.progress-bar').attr('aria-valuenow', percentComplete);
	$(parent).find('.progress-bar').width(percentComplete + '%');
	$(parent).find('.progress-bar').html(percentComplete + '%');
}

/**
 *
 *	File uploads.
 *
 */

function FileManager()
{
	this.target = '/ajax/file.php';
    this.errorPrepend = 'File.';
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

FileManager.prototype.deleteConfirmation = function(id)
{
	/**
	 *	Delete confirmation modal.
	 */

	var errorPrepend = this.errorPrepend + 'deleteConfirmation():';
	var title = "Delete File";
	this.args = {action: "delete-confirmation", id: id};

	$.post(this.target, this.args)
	.done( function(data) {
		show_modal_small(data, title);
	})
	.fail( function() {
		console.log(errorPrepend + ' $.post failed.');
	})
}

FileManager.prototype.deleteRecord = function(id)
{
	/**
	 *	Delete the file.
	 */

	var errorPrepend = this.errorPrepend + 'delete():';
	var title = "Delete";
	this.args = {action: "delete", id: id};
	var anchor = this;

	$.post(this.target, this.args)
	.done( function(data) {
		show_modal_small(data, title);
		anchor.refresh();
	})
	.fail( function() {
		console.log(errorPrepend + ' $.post failed.');
	})
}

FileManager.prototype.info = function(id)
{
	/**
	 *	Delete the file.
	 */

	var errorPrepend = this.errorPrepend + 'info():';
	var title = "File Information";
	this.args = {action: "info", id: id};
	var anchor = this;

	$.post(this.target, this.args)
	.done( function(data) {
		show_modal_small(data, title);
	})
	.fail( function() {
		console.log(errorPrepend + ' $.post failed.');
	})	
}



