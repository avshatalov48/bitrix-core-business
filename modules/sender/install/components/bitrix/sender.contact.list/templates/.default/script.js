;(function ()
{

	BX.namespace('BX.Sender');
	if (BX.Sender.ContactList)
	{
		return;
	}

	var Page = BX.Sender.Page;

	/**
	 * ContactList.
	 *
	 */
	function ContactList()
	{
	}
	ContactList.prototype.init = function (params)
	{
		this.gridId = params.gridId;
		this.actionUri = params.actionUri;
		this.mess = params.mess;

		Page.initButtons();
		this.ajaxAction = new BX.AjaxAction(this.actionUri);
		top.BX.addCustomEvent(top,'BX.Sender.ContactImport::loaded', Page.reloadGrid.bind(Page, null, null));
	};
	ContactList.prototype.remove = function (contactId)
	{
		this.sendChangeStateAction('remove', contactId);
	};
	ContactList.prototype.addToBlacklist = function (contactId)
	{
		this.sendChangeStateAction('addToBlacklist', contactId);
	};
	ContactList.prototype.removeFromBlacklist = function (contactId)
	{
		this.sendChangeStateAction('removeFromBlacklist', contactId);
	};
	ContactList.prototype.removeFromList = function (contactId, listId)
	{
		this.sendChangeStateAction('removeFromList', contactId, listId);
	};
	ContactList.prototype.sendChangeStateAction = function (actionName, contactId, listId)
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
				'id': contactId,
				'listId': listId
			}
		});
	};

	BX.Sender.ContactList = new ContactList();

})(window);