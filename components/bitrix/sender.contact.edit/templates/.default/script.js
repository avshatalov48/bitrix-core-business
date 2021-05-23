;(function ()
{

	BX.namespace('BX.Sender');
	if (BX.Sender.ContactEditor)
	{
		return;
	}

	var Page = BX.Sender.Page;
	var Helper = BX.Sender.Helper;

	/**
	 * Editor.
	 *
	 */
	function Editor()
	{
		this.context = null;
		this.editor = null;
	}
	Editor.prototype.init = function (params)
	{
		this.isFrame = params.isFrame || false;
		this.isSaved = params.isSaved || false;
		this.prettyDateFormat = params.prettyDateFormat;
		this.mess = params.mess || {};
		this.campaignTile = params.campaignTile || {};

		this.context = BX(params.containerId);

		this.initUi();
		Page.initButtons();
	};
	Editor.prototype.initUi = function ()
	{
		if (this.isFrame && this.isSaved)
		{
			BX.Sender.Page.slider.close();
		}
	};


	BX.Sender.ContactEditor = new Editor();

})(window);