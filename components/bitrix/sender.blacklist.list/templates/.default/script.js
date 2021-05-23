;(function ()
{

	BX.namespace('BX.Sender');
	if (BX.Sender.BlacklistGrid)
	{
		return;
	}

	var Page = BX.Sender.Page;

	/**
	 * Blacklist.
	 *
	 */
	function Blacklist()
	{
	}
	Blacklist.prototype.init = function (params)
	{
		this.gridId = params.gridId;
		this.actionUri = params.actionUri;
		this.mess = params.mess;

		Page.initButtons();
		this.ajaxAction = new BX.AjaxAction(this.actionUri);
		top.BX.addCustomEvent(top,'BX.Sender.ContactImport::loaded', Page.reloadGrid.bind(Page, null, null));
	};
	Blacklist.prototype.removeFromBlacklist = function (contactId)
	{
		this.sendChangeStateAction('removeFromBlacklist', contactId);
	};
	Blacklist.prototype.sendChangeStateAction = function (actionName, contactId)
	{
		var gridId = this.gridId;

		Page.changeGridLoaderShowing(gridId, true);
		this.ajaxAction.request({
			action: actionName,
			onsuccess: function () {
				Page.reloadGrid(gridId);
			},
			onfailure: function () {
				Page.changeGridLoaderShowing(gridId, false);
			},
			data: {
				'id': contactId
			}
		});
	};

	BX.Sender.BlacklistGrid = new Blacklist();

})(window);