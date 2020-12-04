;(function ()
{

	BX.namespace('BX.Sender');
	if (BX.Main.Blacklist)
	{
		return;
	}
	/**
	 * BlackList.
	 *
	 */
	function BlackList()
	{
	}
	BlackList.prototype.init = function (params)
	{
		this.gridId = params.gridId;
		this.componentName = params.componentName;
		this.signedParameters = params.signedParameters;
	};
	BlackList.prototype.showDeleteConfirm = function(id)
	{
		BX.UI.Dialogs.MessageBox.show({
		message: BX.message('MAIN_MAIL_BLACKLIST_DELETE_CONFIRM'),
		modal: true,
		title:BX.message('MAIN_MAIL_BLACKLIST_DELETE_CONFIRM_TITLE'),
		buttons: BX.UI.Dialogs.MessageBoxButtons.OK_CANCEL,
		onOk: function()
		{
			return new Promise(function(resolve, reject) {
				this.remove(id).then(
					function(response)
					{
						resolve(response);
						response.data? this.reloadGrid() : this.showNotification( BX.message('MAIN_MAIL_BLACKLIST_DELETE_ERROR'));
					}.bind(this),
					function(response)
					{
						reject(response);
						this.showNotification( BX.message('MAIN_MAIL_BLACKLIST_DELETE_ERROR'));
					}.bind(this));
				}.bind(this));
		}.bind(this),
		onCancel: function(messageBox)
		{
			messageBox.close();
		}
	});
	};
	BlackList.prototype.reloadGrid = function()
	{
		var grid = BX.Main.gridManager.getById(this.gridId);
		if (grid)
		{
			grid.instance.reload();
		}
	};
	BlackList.prototype.showNotification = function(string)
	{
		BX.UI.Notification.Center.notify({
			content:string
		});
	};
	BlackList.prototype.remove = function (id)
	{
		return BX.ajax.runComponentAction(this.componentName, 'remove', {
			mode: 'class',
			data: {
				id: id
			},
			signedParameters: this.signedParameters
		});
	};

	BX.Main.BlackList = new BlackList();

})(window);