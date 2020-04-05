;(function ()
{

	BX.namespace('BX.Sender');
	if (BX.Sender.LetterList)
	{
		return;
	}

	//var Helper = BX.Sender.Helper;
	var Page = BX.Sender.Page;

	/**
	 * LetterList.
	 *
	 */
	function LetterList()
	{
	}
	LetterList.prototype.init = function (params)
	{
		this.gridId = params.gridId;
		this.actionUri = params.actionUri;
		this.pathToEdit = params.pathToEdit;
		this.mess = params.mess;

		this.buttonAdd = BX('SENDER_LETTER_BUTTON_ADD');
		if (this.buttonAdd)
		{
			var menuItems = (params.messages || []).map(function (message) {
				return {
					'id': message.CODE,
					'text': message.NAME,
					'onclick': this.onMenuItemClick.bind(this, message),
					'className': message.IS_AVAILABLE ? '' : 'b24-tariff-lock'
				};
			}, this);
			this.initMenuAdd(menuItems);
		}

		this.ajaxAction = new BX.AjaxAction(this.actionUri);
		this.userErrorHandler = new BX.Sender.ErrorHandler();

		BX.addCustomEvent('Grid::updated', this.onGridUpdated.bind(this));
	};
	LetterList.prototype.remove = function (letterId)
	{
		this.sendChangeStateAction('remove', letterId);
	};
	LetterList.prototype.copy = function (letterId)
	{
		var self = this;
		this.sendChangeStateAction('copy', letterId, function (data) {
			if (!data.copiedId)
			{
				return;
			}
			Page.open(
				self.pathToEdit.replace('#id#', data.copiedId),
				Page.reloadGrid.bind(Page, this.gridId)
			);
		});
	};
	LetterList.prototype.send = function (letterId)
	{
		this.sendChangeStateAction('send', letterId);
	};
	LetterList.prototype.pause = function (letterId)
	{
		this.sendChangeStateAction('pause', letterId);
	};
	LetterList.prototype.stop = function (letterId)
	{
		this.sendChangeStateAction('stop', letterId);
	};
	LetterList.prototype.resume = function (letterId)
	{
		this.sendChangeStateAction('resume', letterId);
	};
	LetterList.prototype.sendChangeStateAction = function (actionName, letterId, callback)
	{
		var gridId = this.gridId;

		var messageCode = null;
		if (BX.Main && BX.Main.gridManager)
		{
			var grid = BX.Main.gridManager.getById(gridId);
			if (grid)
			{
				messageCode = grid.instance.getRows().getById(letterId).getDataset().messageCode;
			}
		}

		Page.changeGridLoaderShowing(gridId, true);
		var self = this;
		this.ajaxAction.request({
			action: actionName,
			onsuccess: function (data) {
				Page.reloadGrid(gridId);
				if (callback)
				{
					callback.apply(self, [data]);
				}
			},
			onusererror: this.userErrorHandler.getHandlers(
				(function() {
					this.sendChangeStateAction(actionName, letterId, callback);
				}).bind(this),
				(function() {
					Page.changeGridLoaderShowing(gridId, false);
				}).bind(this),
				{
					editUrl: this.pathToEdit.replace('#id#', letterId)
				}
			),
			onfailure: function () {
				Page.changeGridLoaderShowing(gridId, false);
			},
			data: {
				'id': letterId
			},
			urlParams: {
				'messageCode': messageCode
			}
		});
	};
	LetterList.prototype.onMenuItemClick = function (message)
	{
		if (!message.IS_AVAILABLE && BX.Sender.B24License)
		{
			BX.Sender.B24License.showPopup('Ad');
			this.popupMenu.close();
			return;
		}

		Page.open(message.URL);
		this.popupMenu.close();
	};
	LetterList.prototype.initMenuAdd = function (items)
	{
		if (this.popupMenu)
		{
			this.popupMenu.show();
			return;
		}

		this.popupMenu = BX.PopupMenu.create(
			'sender-letter-list',
			this.buttonAdd,
			items,
			{
				autoHide: true,
				autoClose: true
			}
		);

		BX.bind(this.buttonAdd, 'click', this.popupMenu.show.bind(this.popupMenu));
	};
	LetterList.prototype.onGridUpdated = function (grid)
	{
		BX.UI.Hint.init(grid.getTable());
	};

	BX.Sender.LetterList = new LetterList();

})(window);