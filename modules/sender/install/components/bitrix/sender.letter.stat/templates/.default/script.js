;(function (window)
{

	BX.namespace('BX.Sender.Letter');
	if (BX.Sender.Letter.Stat)
	{
		return;
	}

	var Page = BX.Sender.Page;

	/**
	 * Letter.
	 *
	 */
	function Stat()
	{
	}
	Stat.prototype.init = function ()
	{
		Page.initButtons();
	};

	BX.Sender.Letter.Stat = new Stat();

})(window);