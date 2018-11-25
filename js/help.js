/*!
 *	Help management class.
 *	Has basic functionality for the help fields.
 */

function UtahHelp()
{
	var targetClass = 'help';

	this.getClass = function()
	{
		return targetClass;
	}
}

UtahHelp.prototype.toggleAll = function()
{
	/**
	 *	Toggles help from the current state.
	 */

	$('.'+this.getClass())
		.toggle();
}

UtahHelp.prototype.hideAll = function()
{
	/**
	 *	Hides all help.
	 */

	$('.'+this.getClass())
		.hide();	
}

UtahHelp.prototype.showAll = function()
{
	/**
	 *	Shows all help.
	 */

	$('.'+this.getClass())
		.show();	
}