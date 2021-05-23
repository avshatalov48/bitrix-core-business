BX.namespace("BX.Lists");
BX.Lists.ListsIblockClass = (function ()
{
	var ListsIblockClass = function (parameters)
	{
		this.ajaxUrl = '/bitrix/components/bitrix/lists.lists/ajax.php';
		this.randomString = parameters.randomString;
		this.jsClass = 'ListsIblockClass_'+parameters.randomString;
	};

	ListsIblockClass.prototype.showLiveFeed = function (iblockId)
	{
		BX.Lists.ajax({
			method: 'POST',
			dataType: 'json',
			url: BX.Lists.addToLinkParam(this.ajaxUrl, 'action', 'setLiveFeed'),
			data: {
				iblockId: iblockId,
				checked: BX('bx-lists-show-live-feed-'+iblockId).checked
			},
			onsuccess: BX.delegate(function (result)
			{
				if(result.status == 'error')
				{
					result.errors = result.errors || [{}];
					BX.Lists.showModalWithStatusAction({
						status: 'error',
						message: result.errors.pop().message
					})
				}
			}, this)
		});
	};

	ListsIblockClass.prototype.createDefaultProcesses = function ()
	{
		BX.addClass(BX('bx-lists-default-processes'), 'ui-btn-clock');
		BX('bx-lists-default-processes').setAttribute('onclick','');
		BX.Lists.ajax({
			method: 'POST',
			dataType: 'json',
			url: BX.Lists.addToLinkParam(this.ajaxUrl, 'action', 'createDefaultProcesses'),
			data: {
				siteId: BX('bx-lists-select-site').value
			},
			onsuccess: BX.delegate(function (result)
			{
				if(result.status == 'success')
				{
					location.reload();
				}
				else
				{
					BX('bx-lists-default-processes').setAttribute('onclick','BX.Lists["'+this.jsClass+'"].createDefaultProcesses();');
					result.errors = result.errors || [{}];
					BX.Lists.showModalWithStatusAction({
						status: 'error',
						message: result.errors.pop().message
					});
					BX.removeClass(BX('bx-lists-default-processes'), 'ui-btn-clock');
				}
			}, this)
		});
	};

	return ListsIblockClass;

})();

