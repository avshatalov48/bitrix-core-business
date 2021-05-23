(function (window) {
	var __windows = {};

	BX.GroupsPopup = {
		searchTimeout: null,
		oXHR: null,
		create : function(uniquePopupId, bindElement, params)
		{
			if (!__windows[uniquePopupId])
				__windows[uniquePopupId] = new GroupsPopup(uniquePopupId, bindElement, params);
			return __windows[uniquePopupId];
		},
		abortSearchRequest : function()
		{
			if (this.oXHR)
			{
				this.oXHR.abort();
			}
		}
	};

	var GroupsPopup = function(uniquePopupId, bindElement, params) {

		this.tabs = [];
		this.items2Objects = [];
		this.selected = [];
		this.lastGroups = [];
		this.myGroups = [];
		this.featuresPerms = null;
		
		var mainChildren = [];
		
		if (params)
		{
			if (params.lastGroups)
			{
				this.lastGroups = params.lastGroups;
			}
			if (params.myGroups)
			{
				this.myGroups = params.myGroups;
			}
			if (params.featuresPerms)
			{
				this.featuresPerms = params.featuresPerms;
			}
			if (params.events)
			{
				for (var eventName in params.events)
				{
					if (params.events.hasOwnProperty(eventName))
					{
						BX.addCustomEvent(this, eventName, params.events[eventName]);
					}
				}
			}
			if (params.selected && params.selected.length)
			{
				this.selected = params.selected;
				BX.onCustomEvent(this, "onGroupSelect", [this.selected, {onInit: true}]);
			}
			if (params.searchInput)
			{
				this.searchInput = params.searchInput
			}
			else
			{
				this.searchInput = BX.create("input", {props: {className: "bx-finder-box-search-textbox"}});
				mainChildren.push(BX.create("div", {
					props: {className: "bx-finder-box-search"},
					style: {},
					children: [this.searchInput]
				}));
			}
		}
		
		BX.adjust(this.searchInput, {
			events: {
				keyup: BX.proxy(
					function(e) {
						if (!e) e = window.event;
						this.search((e.target || e.srcElement).value);
					},
					this
				),
				focus: function() {
					this.value = "";
				},
				blur: BX.proxy(
					function() {
						setTimeout(
							BX.proxy(
								function() {
									if (this.selected[0]) {
										this.searchInput.value = this.selected[0].title;
									}
								},
								this
							),
							150
						);
					},
					this
				)
			}
		});
		
		this.ajaxURL = "/bitrix/components/bitrix/socialnetwork.group.selector/ajax.php";
		
		if (this.lastGroups.length > 0)
		{
			this.addTab("last", this.lastGroups);
		}
		
		if (this.myGroups.length > 0)
		{
			this.addTab("my", this.myGroups);
		}
		
		this.addTab("search");
		
		this.tabsOuter = BX.create("div", {props: {className: "bx-finder-box-tabs"}});
		
		this.tabsContentOuter = BX.create("td", {
			props: {
				className: "bx-finder-box-tabs-content-cell"
			}
		});
		
		mainChildren.splice(mainChildren.length, 0,
			this.tabsOuter,
			BX.create("div", {
				props: {className: "popup-window-hr popup-window-buttons-hr"},
				html: "<i></i>"
			}),
			BX.create("div", {
				props: {
					className: "bx-finder-box-tabs-content"
				},
				children: [
					BX.create("table", {
						props: {
							className: "bx-finder-box-tabs-content-table"
						},
						children: [
							BX.create("tr", {
								children: [
									this.tabsContentOuter
								]
							})
						]
					})
				]
			})
		);
		
		this.content = BX.create("div", {
			props: {className: "bx-finder-box bx-lm-box sonet-groups-finder-box"},
			style : { padding: "2px 6px 6px 6px", minWidth: "500px"},
			children: mainChildren
		});

		this.popupWindow = BX.PopupWindowManager.create(uniquePopupId, bindElement, {
			content: "",
			autoHide: true,
			events : {
				onPopupFirstShow : BX.proxy(
					function(popupWindow) {
						popupWindow.setContent(this.content);
					},
					this
				),
				onPopupShow : BX.proxy(
					function(popupWindow)
					{
						this.__render();
					},
					this
				)
			},
			buttons: [
				new BX.PopupWindowButton({
					text : BX.message("SONET_GROUP_BUTTON_CLOSE"),
					className : "popup-window-button-accept task-edit-popup-close-but",
					events : {click : function() {this.popupWindow.close();}}
				})
			]
		});
	};

	GroupsPopup.prototype.show = function() {
		this.popupWindow.show();
		this.searchInput.focus();
	};

	GroupsPopup.prototype.selectTab = function(tab) {
		for(var i in this.tabs)
		{
			if (this.tabs.hasOwnProperty(i))
			{
				BX.removeClass(this.tabs[i].tab, "bx-finder-box-tab-selected");
				BX.adjust(this.tabs[i].content, {style: {display: "none"}});
			}
		}
		
		BX.addClass(tab.tab, "bx-finder-box-tab-selected");
		BX.adjust(tab.content, {style: {display: "block"}});
	};
	
	GroupsPopup.prototype.addTab = function(code, items, selected) {
		var content = BX.create("div", {
			props: {className: "bx-finder-box-tab-content bx-lm-box-tab-content-sonetgroup"}
		});
		if (selected)
		{
			BX.adjust(content, {style: {display: "block"}});
		}
		
		var tab = BX.create("span", {
			props: {className: "bx-finder-box-tab" + (selected ? " bx-finder-box-tab-selected" : "")},
			text: BX.message("SONET_GROUP_TABS_" + code.toUpperCase())
		});
		
		this.tabs[code] = {tab: tab, content: content};
		
		BX.adjust(this.tabs[code].tab, {
			events: {
				click: BX.proxy(
					function() {
						this.selectTab(this.tabs[code])
					},
					this
				)
			}
		});
		
		if (items)
		{
			this.setItems(this.tabs[code], items);
		}
	};
	
	GroupsPopup.prototype.setItems = function(tab, items) {
		BX.cleanNode(tab.content);

		if (!!items)
		{
			for(var i = 0, count = items.length; i < count; i++)
			{
				tab.content.appendChild(this.__renderItem(items[i]));
			}
		}
	};
	
	GroupsPopup.prototype.select = function(item) {

		this.selected = [item];

		var i = 0;
		var count = 0;

		clearTimeout(BX.GroupsPopup.searchTimeout);

		if (this.items2Objects[item.id])
		{
			for(i = 0, count = this.items2Objects[item.id].length; i < count; i++)
			{
				BX.addClass(this.items2Objects[item.id][i], "bx-finder-box-item-t7-selected");
			}
		}

		BX.onCustomEvent(this, "onGroupSelect", [this.selected, {onInit: false}]);

		var lastSelected = [item.id];
		for(i = 0, count = this.lastGroups.length; i < count; i++)
		{
			if (!BX.util.in_array(this.lastGroups[i].id, lastSelected))
			{
				lastSelected.push(this.lastGroups[i].id);
			}
		}
		BX.userOptions.save("socialnetwork", "groups_popup", "last_selected", lastSelected.slice(0, 10));

		if (this.selected[0]) {
			this.searchInput.value = this.selected[0].title;
		}

		this.popupWindow.close();
	};
	
	GroupsPopup.prototype.deselect = function(itemId) {
		this.selected = [];
		if (itemId && this.items2Objects[itemId])
		{
			for(var i = 0, count = this.items2Objects[itemId].length; i < count; i++)
			{
				BX.removeClass(this.items2Objects[itemId][i], "bx-finder-box-item-t7-selected");
			}
		}
		this.searchInput.value = "";
	};
	
	GroupsPopup.prototype.search = function(query) {
		if (query.length > 0)
		{
			clearTimeout(BX.GroupsPopup.searchTimeout);
			BX.GroupsPopup.abortSearchRequest();

			this.selectTab(this.tabs["search"]);

			var url = this.ajaxURL + '?mode=search&SITE_ID=' + __bx_group_site_id + '&query=' + encodeURIComponent(query);
			if (this.featuresPerms)
			{
				url += "&features_perms[0]=" + encodeURIComponent(this.featuresPerms[0]);
				url += "&features_perms[1]=" + encodeURIComponent(this.featuresPerms[1]);
			}

			BX.GroupsPopup.searchTimeout = setTimeout(BX.delegate(function()
			{
				BX.GroupsPopup.oXHR = BX.ajax.loadJSON(url, BX.proxy(
					function(data) {
						this.setItems(this.tabs["search"], data);
					}, this
				));
			}, this), 1000);

		}
		else
		{
			clearTimeout(BX.GroupsPopup.searchTimeout);
		}
	};

	GroupsPopup.prototype.__render = function() {
		var selected = false;
		
		BX.cleanNode(this.tabsOuter);
		BX.cleanNode(this.tabsContentOuter);
		for(var i in this.tabs)
		{
			if (this.tabs.hasOwnProperty(i))
			{
				if (!selected)
				{
					selected = BX.hasClass(this.tabs[i].tab, "bx-finder-box-tab-selected");
				}
				this.tabsOuter.appendChild(this.tabs[i].tab);
				this.tabsContentOuter.appendChild(this.tabs[i].content);
			}
		}

		if (!selected)
		{
			this.selectTab(this.tabs["last"] || this.tabs["my"] || this.tabs["search"]);
		}
	};

	GroupsPopup.prototype.__renderItem = function(item) {
		var avatar = BX.create("div", { props: {className: "bx-finder-box-item-t7-avatar bx-finder-box-item-t7-group-avatar"}});
		if (item.image)
		{
			BX.adjust(avatar, { style: { background: "url('" + item.image + "') no-repeat center center", backgroundSize : "24px 24px" }});
		}
		
		var isSelected = false;
		for(var i=0; i<this.selected.length; i++)
		{
			if(this.selected[i].id == item.id)
			{
				isSelected = true;
				break;
			}
		}

		var itemNode = BX.create("div", {
			props: {
				className: "bx-finder-box-item-t7 bx-finder-element bx-lm-element-sonetgroup" + (typeof item.IS_EXTRANET != 'undefined' && item.IS_EXTRANET == 'Y' ? ' bx-lm-element-extranet' : '') + (isSelected ? " bx-finder-box-item-t7-selected" : "")
			},
			children: [
				avatar,
				BX.create("div", {
					props : {
						className : "bx-finder-box-item-t7-space"
					}
				}),
				BX.create("div", {
					props: {className: "bx-finder-box-item-t7-info"},
					children: [
						BX.create("div", {
							text: item.title, 
							props : { 
								className : "bx-finder-box-item-t7-name" 
							}
						})
					]
				})
			],
			events: {
				click: BX.proxy(
					function()
					{
						this.select(item);
					},
					this
				)
			}
		});
		
		if (!this.items2Objects[item.id])
		{
			this.items2Objects[item.id] = [itemNode];
		}
		else if (!BX.util.in_array(itemNode, this.items2Objects[item.id]))
		{
			this.items2Objects[item.id].push(itemNode);
		}
		
		return itemNode;
	};

})(window);