;(function ()
{

	BX.namespace('BX.Sender');
	if (BX.Sender.CampaignList)
	{
		return;
	}

	var Page = BX.Sender.Page;
	var Helper = BX.Sender.Helper;

	/**
	 * ListManager.
	 *
	 */
	function ListManager()
	{
	}
	ListManager.prototype.init = function (params)
	{
		this.presets = params.presets || [];
		this.gridId = params.gridId;
		this.actionUri = params.actionUri;
		this.pathToEdit = params.pathToEdit;
		this.pathToAdd = params.pathToAdd;
		this.mess = params.mess;

		this.buttonAdd = BX('SENDER_BUTTON_ADD');
		if (this.buttonAdd)
		{
			var menuItems = this.presets.map(function (item) {
				var hint = BX.util.htmlspecialchars(item.DESC);
				return {
					'id': item.CODE,
					'html': item.NAME + '<span data-hint="' + hint + '"></span>',
					'onclick': this.onMenuItemClick.bind(this, item)
					//'className': message.IS_AVAILABLE ? '' : 'b24-tariff-lock'
				};
			}, this);
			this.initMenuAdd(
				[
					{
						'id': 'manually',
						'text': this.mess.manually,
						'onclick': Page.open.bind(Page, this.pathToAdd)
					},
					{'delimiter': true}
				].concat(menuItems)
			);
		}

		this.ajaxAction = new BX.AjaxAction(this.actionUri);
		Page.initButtons();
	};
	ListManager.prototype.activate = function (id)
	{
		this.doAction('activate', id);
	};
	ListManager.prototype.deactivate = function (id)
	{
		this.doAction('deactivate', id);
	};
	ListManager.prototype.remove = function (id)
	{
		this.doAction('remove', id);
	};
	ListManager.prototype.createUsingPreset = function (code)
	{
		this.doAction('createUsingPreset', null, null, {'code': code});
	};
	ListManager.prototype.removeSelected = function ()
	{
		var grid = BX.Main.gridManager.getById(this.gridId);
		if (!grid || !grid.instance)
		{
			return;
		}

		this.doAction('removeList', grid.instance.getRows().getSelectedIds());
	};
	ListManager.prototype.doAction = function (actionName, id, callback, dataParameters)
	{
		var gridId = this.gridId;

		dataParameters = dataParameters || {};
		dataParameters.id = id;
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
			onfailure: function () {
				Page.reloadGrid(gridId);
			},
			data: dataParameters
		});
	};
	ListManager.prototype.onMenuItemClick = function (item)
	{
		this.createUsingPreset(item.CODE);
		this.popupMenu.close();
	};
	ListManager.prototype.initMenuAdd = function (items)
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

		var container = this.popupMenu.getPopupWindow().getPopupContainer();
		Helper.hint.init(container);
		BX.bind(this.buttonAdd, 'click', this.popupMenu.show.bind(this.popupMenu));
	};


	BX.Sender.CampaignList = new ListManager();

})(window);