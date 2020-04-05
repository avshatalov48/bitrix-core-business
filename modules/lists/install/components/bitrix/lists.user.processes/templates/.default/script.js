BX.namespace("BX.Lists");
BX.Lists.ListsProcessesClass = (function ()
{
	var ListsProcessesClass = function (parameters)
	{
		this.ajaxUrl = '/bitrix/components/bitrix/lists.user.processes/ajax.php';

		BX.bind(BX('lists-title-action-add'), 'click', BX.delegate(this.showProcesses, this));
	};

	ListsProcessesClass.prototype.showProcesses = function ()
	{
		var tabContainer = BX('bx-lists-store_items'),
			menuItemsLists = [],
			tabs = BX.findChildren(tabContainer,
				{'tag':'span', 'className': 'feed-add-post-form-link-lists'}, true);

		if(tabs.length)
		{
			menuItemsLists = this.getMenuItems(tabs);
			this.showMoreMenuLists(menuItemsLists);
		}
		else
		{
			var siteId = null, siteDir = null;
			if(BX('bx-lists-select-site-id'))
			{
				siteId = BX('bx-lists-select-site-id').value;
			}
			if(BX('bx-lists-select-site'))
			{
				siteDir = BX('bx-lists-select-site').value;
			}
			BX.Lists.ajax({
				method: 'POST',
				dataType: 'json',
				url: BX.Lists.addToLinkParam(this.ajaxUrl, 'action', 'showProcesses'),
				data: {
					siteDir: siteDir,
					siteId: siteId,
					sessid: BX.bitrix_sessid()
				},
				onsuccess: BX.delegate(function (result)
				{
					if(result.status == 'success')
					{
						for(var k in result.lists)
						{
							tabContainer.appendChild(BX.create('span', {
								attrs: {
									'data-name': BX.util.htmlspecialchars(result.lists[k].name),
									'data-picture': result.lists[k].picture,
									'data-url': 'document.location.href = "'+result.lists[k].url+'"'
								},
								props:{
									className: 'feed-add-post-form-link-lists',
									id: 'bx-lists-tab-create-processes'
								},
								style : {
									display: 'none'
								}
							}));
						}
						tabs = BX.findChildren(tabContainer, {'tag':'span',
							'className': 'feed-add-post-form-link-lists'}, true);
						menuItemsLists = this.getMenuItems(tabs);
						this.showMoreMenuLists(menuItemsLists);
					}
					else
					{
						result.errors = result.errors || [{}];
						BX.Lists.showModalWithStatusAction({
							status: 'error',
							message: result.errors.pop().message
						})
					}
				}, this)
			});
		}
	};

	ListsProcessesClass.prototype.getMenuItems = function(tabs)
	{
		var menuItemsLists = [];
		for (var i = 0; i < tabs.length; i++)
		{
			menuItemsLists.push({
				tabId : "lists",
				text : tabs[i].getAttribute("data-name"),
				className : "feed-add-post-form-lists",
				onclick : tabs[i].getAttribute("data-url")
			});
		}
		return menuItemsLists;
	};

	ListsProcessesClass.prototype.showMoreMenuLists = function(menuItemsLists)
	{
		var buttonRect = BX("lists-title-action-add").getBoundingClientRect();
		var menu = BX.PopupMenu.create(
			"lists",
			BX("lists-title-action-add"),
			menuItemsLists,
			{
				closeByEsc : true,
				offsetLeft: buttonRect.width/2,
				angle: true
			}
		);
		var spanIcon = BX.findChildren(BX('popup-window-content-menu-popup-lists'),
				{'tag':'span', 'className': 'menu-popup-item-icon'}, true),
			spanDataPicture = BX.findChildren(BX('bx-lists-store_items'),
				{'tag':'span', 'className': 'feed-add-post-form-link-lists'}, true);

		for(var i = 0; i < spanIcon.length; i++)
		{
			spanIcon[i].innerHTML = spanDataPicture[i].getAttribute('data-picture');
		}
		menu.popupWindow.show();
	};

	return ListsProcessesClass;

})();

