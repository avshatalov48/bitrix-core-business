;(function ()
{
	BX.namespace('BX.Mail.UserSignature.List');

	BX.Mail.UserSignature.List = {
		gridId: 'mail-usersignature-grid'
	};

	BX.Mail.UserSignature.List.init = function()
	{
		BX.addCustomEvent('SidePanel.Slider:onMessage', function(message)
		{
			if(message.getEventId() === 'mail-add-signature')
			{
				var data = message.getData();
				var userSignatureId = data.userSignatureId;
				if(userSignatureId > 0)
				{
					var grid = BX.Main.gridManager.getById(BX.Mail.UserSignature.List.gridId);
					if(grid)
					{
						grid.instance.reloadTable('GET', {}, function()
						{
							BX.Mail.UserSignature.List.highlightRow(userSignatureId);
						});
					}
				}
			}
		});
	};

	BX.Mail.UserSignature.List.openUrl = function(url)
	{
		if(BX.SidePanel)
		{
			BX.SidePanel.Instance.open(url, {width: 760, cacheable: false});
		}
		else
		{
			location.href = viewUrl;
		}
	};

	BX.Mail.UserSignature.List.delete = function(signatureId)
	{
		if(confirm(BX.message('MAIL_SIGNATURE_DELETE_CONFIRM')))
		{
			BX.ajax.runAction('mail.api.usersignature.delete', {
				data: {
					userSignatureId: signatureId
				}
			}).then(function()
			{
				BX.UI.Notification.Center.notify({
					content: BX.message('MAIL_SIGNATURE_DELETED_SUCCESS')
				});
				var grid = BX.Main.gridManager.getById(BX.Mail.UserSignature.List.gridId);
				if(grid)
				{
					grid.instance.reloadTable('GET');
				}
			}, function(response)
			{
				var alert = new BX.UI.Alert({
					color: BX.UI.Alert.Color.DANGER,
					icon: BX.UI.Alert.Icon.DANGER,
					text: response.errors.join(', ')
				});
				BX.adjust(BX('signature-alert-container'), {
					html: ''
				});
				BX.append(alert.getContainer(), BX('signature-alert-container'));
			});
		}
	};

	BX.Mail.UserSignature.List.highlightRow = function(userSignatureId)
	{
		var grid = BX.Main.gridManager.getById(BX.Mail.UserSignature.List.gridId);
		if(grid)
		{
			var newRow = grid.instance.getRows().getById(userSignatureId);
			if(newRow)
			{
				newRow.select();
			}
		}
	};

})();