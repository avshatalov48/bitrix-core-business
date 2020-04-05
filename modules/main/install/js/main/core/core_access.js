(function() {
var BX = window.BX;
if(BX.Access)
	return;

BX.Access = 
{
	bInit: false,
	waitDiv: null, 
	waitPopup: null,
	bDialogLoaded: false,
	selectedProvider: '',
	obSelected: {},
	obCnt: {__providers_cnt: 0},
	obAlreadySelected: {},
	obSelectedBind: {},
	showSelected: false,
	popup: null,
	callback: null,
	obProviderNames: {},
	arParams: {}
};

BX.Access.Init = function(arParams)
{
	if(arParams)
		BX.Access.arParams = arParams;
	
	if(BX.Access.bInit)
		return;

	BX.Access.bInit = true;
		
	BX.ready(BX.delegate(function()
	{
		BX.Access.popup = BX.PopupWindowManager.create("BXUserRights", null, {
			autoHide: false,
			zIndex: 0,
			offsetLeft: 0,
			offsetTop: 0,
			draggable: {restrict:true},
			closeByEsc: true,
			titleBar: BX.message('js_access_title'),
			contentColor : 'white',
			contentNoPaddings : true,
			closeIcon: true,
			buttons: [
				new BX.PopupWindowButton({
					text : BX.message('js_access_select'),
					className : "popup-window-button-accept",
					events : { click : function() 
					{
						BX.Access.SaveLRU();

						BX.Access.SaveSelected();

						if(BX.Access.callback)
							BX.Access.callback(BX.Access.obSelected);

						this.popupWindow.close();
					}}
				}),
	
				new BX.PopupWindowButtonLink({
					text: BX.message('js_access_close'),
					className: "popup-window-button-link-cancel",
					events: { click : function()
					{
						this.popupWindow.close(); 
					}}
				})
			],
			content: '<div class="access-container"></div>',
			events: {
				onAfterPopupShow: function()
				{
					if(!BX.Access.bDialogLoaded)
					{
						BX.Access.showWait(this.contentContainer);
						BX.ajax.post(
							'/bitrix/tools/access_dialog.php', 
							{
								lang: BX.message('LANGUAGE_ID'),
								site_id: BX.message('SITE_ID') || '',
								arParams: BX.Access.arParams
							}, 
							BX.delegate(function(result)
							{
								this.setContent(result);
								BX.Access.closeWait();
								BX.Access.bDialogLoaded = true;

								if (BX.Access.showSelected)
								{
									for(var code in BX.Access.obAlreadySelected)
									{
										if (typeof(BX.Access.obAlreadySelected[code]) == 'object')
										{
											BX.Access.AddSelection(BX.Access.obAlreadySelected[code]);
										}
									}
								}
							}, 
							this)
						);
					}
					else
					{
						if (BX.Access.showSelected)
						{
							for(var code in BX.Access.obAlreadySelected)
							{
								if (typeof(BX.Access.obAlreadySelected[code]) == 'object')
								{
									BX.Access.AddSelection(BX.Access.obAlreadySelected[code]);
								}
							}
						}
					}
					BX.onCustomEvent(BX.Access, "onAfterPopupShow", []);
				},
				onPopupClose: function()
				{
					BX.Access.ClearSelection();
				}

			}
		});
	}, this));
};

BX.Access.ShowForm = function(arParams)
{
	if(!arParams.bind)
		arParams.bind = 'bind';
	BX.Access.bind = arParams.bind;
	BX.Access.showSelected = arParams.showSelected === true;

	if(BX.Access.obSelectedBind[arParams.bind])
		BX.Access.obAlreadySelected = BX.Access.obSelectedBind[arParams.bind];
	else
		BX.Access.obAlreadySelected = {};

	BX.Access.callback = arParams.callback;
	BX.Access.popup.params.zIndex = (BX.WindowManager? BX.WindowManager.GetZIndex() : 0);
	BX.Access.popup.show();
};

BX.Access.showWait = function(div)
{
	BX.Access.waitDiv = BX.Access.waitDiv || div;
	div = BX(div || BX.Access.waitDiv);

	if (!BX.Access.waitPopup)
	{
		BX.Access.waitPopup = new BX.PopupWindow('ur_wait', div, {
			autoHide: true,
			lightShadow: true,
			zIndex: (BX.WindowManager? BX.WindowManager.GetZIndex() : 2),
			content: BX.create('DIV', {props: {className: 'ur-wait'}})
		});
	}
	else
	{
		BX.Access.waitPopup.setBindElement(div);
	}

	var height = div.offsetHeight, width = div.offsetWidth;
	if (height > 0 && width > 0)
	{
		BX.Access.waitPopup.setOffset({
			offsetTop: -parseInt(height/2+15),
			offsetLeft: parseInt(width/2-15)
		});

		BX.Access.waitPopup.show();
	}

	return BX.Access.waitPopup;
};

BX.Access.closeWait = function()
{
	if(BX.Access.waitPopup)
		BX.Access.waitPopup.close();
};

BX.Access.SelectProvider = function(id)
{
	if(BX.Access.selectedProvider != '')
	{
		BX('access_btn_'+BX.Access.selectedProvider).className = 'access-provider-button';
		BX('access_provider_'+BX.Access.selectedProvider).style.display = 'none';
	}
	BX('access_btn_'+id).className = 'access-provider-button access-provider-button-selected';
	BX('access_provider_'+id).style.display = '';
	BX.Access.selectedProvider = id;
	
	BX.onCustomEvent(BX.Access, "onSelectProvider", [{'provider': id}]);
};

BX.Access.AddSelection = function(ob)
{
	if(!ob.provider)
	{
		return;
	}

	if(!BX.Access.obSelected[ob.provider])
	{
		BX.Access.obSelected[ob.provider] = {};
		BX.Access.obCnt[ob.provider] = 0;
		BX.Access.obCnt.__providers_cnt++;
	}

	if(!BX.Access.obSelected[ob.provider][ob.id])
	{
		BX.Access.obSelected[ob.provider][ob.id] = BX.clone(ob);
		BX.Access.obCnt[ob.provider]++;

		BX('access_selected_title').style.display = 'none';
		BX('access_selected_provider_'+ob.provider).style.display = '';
		BX('access_selected_items_'+ob.provider).appendChild(BX.create('div', {
			props: {
				'className':'bx-finder-box-selected-item',
				'id': 'access_selected_item_'+ob.id
			}, 
			html: '<a href="javascript:void(0);" onclick="BX.Access.RemoveSelection(\''+ob.provider+'\', \''+ob.id+'\')" class="bx-finder-box-selected-item-icon"></a><span class="bx-finder-box-selected-item-text">'+ob.name+'</span>'
		}));

		BX('access_sel_count_'+ob.provider).innerHTML = '('+BX.Access.obCnt[ob.provider]+')';
	}
};

BX.Access.RemoveSelection = function(provider, id)
{
	delete BX.Access.obSelected[provider][id];

	BX.Access.obCnt[provider]--;
	
	var item = BX('access_selected_item_'+id);
	item.parentNode.removeChild(item);

	if(BX.Access.obCnt[provider] == 0)
	{
		delete BX.Access.obSelected[provider];
		BX.Access.obCnt.__providers_cnt--;

		BX('access_selected_provider_'+provider).style.display = 'none';
		
		if(BX.Access.obCnt.__providers_cnt == 0)
			BX('access_selected_title').style.display = '';
	}
	else
	{
		BX('access_sel_count_'+provider).innerHTML = '('+BX.Access.obCnt[provider]+')';
	}

	BX.onCustomEvent(BX.Access, "onDeleteItem", [{'provider': provider, 'id': id}]);
};

BX.Access.ClearSelection = function()
{
	for(var provider in BX.Access.obSelected)
		for(var id in BX.Access.obSelected[provider])
			BX.Access.RemoveSelection(provider, id);
	BX.Access.obSelected = {};
};

BX.Access.SaveLRU = function()
{
	BX.ajax.post('/bitrix/tools/access_dialog.php', {
		LRU: BX.Access.obSelected,
		mode: 'save_lru',
		sessid: BX.bitrix_sessid()
	});
};

BX.Access.SaveSelected = function()
{
	if(BX.Access.showSelected || !BX.Access.obSelectedBind[BX.Access.bind])
		BX.Access.obSelectedBind[BX.Access.bind] = {};
	
	for(var pr in BX.Access.obSelected)
	{
		for(var id in BX.Access.obSelected[pr])
		{
			if (BX.Access.showSelected)
				BX.Access.obSelectedBind[BX.Access.bind][id] = {id: id, provider: pr, name: BX.Access.obSelected[pr][id].name};
			else
				BX.Access.obSelectedBind[BX.Access.bind][id] = true;
		}
	}
};

BX.Access.SetSelected = function(obSel, bind)
{
	if(!bind)
		bind = 'bind';

	BX.Access.obSelectedBind[bind] = obSel;
};

BX.Access.DeleteSelected = function(id, bind)
{
	if(!bind)
		bind = 'bind';

	if(BX.Access.obSelectedBind[bind] && BX.Access.obSelectedBind[bind][id])
	{
		delete BX.Access.obSelectedBind[bind][id];
	}
};

BX.Access.GetProviderName = function(provider)
{
	if(BX.Access.obProviderNames[provider])
		return BX.Access.obProviderNames[provider].name;
	return '';
};

BX.Access.GetProviderPrefix = function(provider, id)
{
	if(BX.Access.obProviderNames[provider])
	{
		var prefixes = BX.Access.obProviderNames[provider]['prefixes'];
		for(var i in prefixes)
		{
			var expr = new RegExp(prefixes[i]['pattern']);
			if(expr.test(id))
			{
				return prefixes[i]['prefix'];
			}
		}
		return BX.Access.obProviderNames[provider].name;
	}
	return '';
};

})();
