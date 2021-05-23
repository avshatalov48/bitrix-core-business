(function (window) {

if (!!window.JCCatalogTabs)
{
	return;
}

window.JCCatalogTabs = function (params)
{
	this.errorCode = 0;
	this.activeTabId = params.activeTabId;
	this.currentTab = -1;
	this.tabsContId = params.tabsContId;
	this.tabList = params.tabList;
	this.obTabList = [];

	BX.ready(BX.delegate(this.Init, this));
};

window.JCCatalogTabs.prototype.Init = function()
{
	if (!this.tabList || !BX.type.isArray(this.tabList) || this.tabList.length === 0)
	{
		this.errorCode = -1;
		return;
	}
	var i,
		strFullId;

	for (i = 0; i < this.tabList.length; i++)
	{
		strFullId = this.tabsContId+this.tabList[i];
		this.obTabList[i] = {
			id: this.tabList[i],
			tabId: strFullId,
			contId: strFullId+'_cont',
			tab: BX(strFullId),
			cont: BX(strFullId+'_cont')
		};
		if (!this.obTabList[i].tab || !this.obTabList[i].cont)
		{
			this.errorCode = -2;
			break;
		}
		if (this.activeTabId === this.tabList[i])
		{
			this.currentTab = i;
		}
		BX.bind(this.obTabList[i].tab, 'click', BX.proxy(this.onClick, this));
	}
	if (this.errorCode === 0)
	{
		this.showActiveTab();
	}
};

window.JCCatalogTabs.prototype.onClick = function()
{
	var target = BX.proxy_context,
		index = -1,
		i;

	for (i = 0; i < this.obTabList.length; i++)
	{
		if (target.id === this.obTabList[i].tabId)
		{
			index = i;
			break;
		}
	}
	if (index > -1)
	{
		if (index !== this.currentTab)
		{
			this.hideActiveTab();
			this.currentTab = index;
			this.showActiveTab();
		}
	}
};

window.JCCatalogTabs.prototype.hideActiveTab = function()
{
	BX.removeClass(this.obTabList[this.currentTab].tab, 'active');
	BX.addClass(this.obTabList[this.currentTab].cont, 'tab-off');
	BX.addClass(this.obTabList[this.currentTab].cont, 'hidden');
};

window.JCCatalogTabs.prototype.showActiveTab = function()
{
	BX.onCustomEvent('onAfterBXCatTabsSetActive_'+this.tabsContId,[{activeTab: this.obTabList[this.currentTab].id}]);
	BX.addClass(this.obTabList[this.currentTab].tab, 'active');
	BX.removeClass(this.obTabList[this.currentTab].cont, 'tab-off');
	BX.removeClass(this.obTabList[this.currentTab].cont, 'hidden');
};
})(window);