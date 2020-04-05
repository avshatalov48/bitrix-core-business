BX.namespace("BX.Lists");
BX.Lists.CatalogProcessesClass = (function ()
{
	var CatalogProcessesClass = function (parameters)
	{
		this.ajaxUrl = '/bitrix/components/bitrix/lists.catalog.processes/ajax.php';
		this.randomString = parameters.randomString;
	};

	CatalogProcessesClass.prototype.installProcesses = function (selector)
	{
		BX.addClass(selector, 'ui-btn-clock');
		selector.setAttribute('onclick','');
		var selectedProcesses = BX.findChildrenByClassName(
			BX('bx-lists-lcp-total-div'), 'bx-lists-lcp-table-tr-mousedown');
		var processes = [];
		for(var k in selectedProcesses)
			processes.push(selectedProcesses[k].getAttribute('data-file'));

		var siteId = null;
		if(BX('bx-lists-lcp-site-id'))
			siteId = BX('bx-lists-lcp-site-id').value;

		BX.Lists.ajax({
			method: 'POST',
			dataType: 'json',
			url: BX.Lists.addToLinkParam(this.ajaxUrl, 'action', 'installProcesses'),
			data: {
				siteId: siteId,
				processes: processes
			},
			onsuccess: BX.delegate(function (result)
			{
				if(result.status == 'success')
				{
					for(var k in selectedProcesses)
					{
						selectedProcesses[k].setAttribute('id', 'not allocated');
						selectedProcesses[k].setAttribute('data-pick-out', 'not allocated');
						selectedProcesses[k].setAttribute('class', 'bx-lists-lcp-table-tr-mouseout');
						var selectedTitle = BX.findChildrenByClassName(
							selectedProcesses[k], 'bx-lists-lcp-table-td-name');
						for(var i = 0; i < selectedTitle.length; i++)
						{
							selectedTitle[i].innerHTML = selectedTitle[i].innerHTML +
								BX.message('LISTS_LCP_TEMPLATE_PROCESS_INSTALLED');
						}
					}
					BX.Lists.showModalWithStatusAction({
						status: 'success',
						message: result.message
					});
				}
				else
				{
					result.errors = result.errors || [{}];
					BX.Lists.showModalWithStatusAction({
						status: 'error',
						message: result.errors.pop().message
					});
				}
				BX.removeClass(selector, 'ui-btn-clock');
				selector.setAttribute('onclick',
					'BX.Lists["CatalogProcessesClass_'+this.randomString+'"].installProcesses(this);');
			}, this)
		});
	};

	CatalogProcessesClass.prototype.mousedown = function (event)
	{
		var pickOut = event.getAttribute('data-pick-out');
		if(pickOut == 'allocate')
		{
			if(event.className == 'bx-lists-lcp-table-tr-mousedown')
			{
				event.className = 'bx-lists-lcp-table-tr-mouseout';
			}
			else
			{
				event.className = 'bx-lists-lcp-table-tr-mousedown';
			}
		}
	};

	CatalogProcessesClass.prototype.mouseover = function (event)
	{
		var pickOut = event.getAttribute('data-pick-out');
		if(pickOut == 'allocate')
		{
			if(event.className != 'bx-lists-lcp-table-tr-mousedown')
			{
				event.className = 'bx-lists-lcp-table-tr-mouseover';
			}
		}
	};

	CatalogProcessesClass.prototype.mouseout = function (event)
	{
		var pickOut = event.getAttribute('data-pick-out');
		if(pickOut == 'allocate')
		{
			if(event.className != 'bx-lists-lcp-table-tr-mousedown')
			{
				event.className = 'bx-lists-lcp-table-tr-mouseout';
			}
		}
	};

	return CatalogProcessesClass;

})();
