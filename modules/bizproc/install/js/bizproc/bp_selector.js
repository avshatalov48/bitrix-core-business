(function() {
	var BX = window.BX;

	BX.namespace('BX.Bizproc');
	if(BX.Bizproc.Selector)
		return;

	BX.Bizproc.Selector = {
		delimiter: '=',
		listenKeyCode: 187,
		listenKey: '=',
		currentData: {},
		inputElement: null,
		popup: null,
		selectedTab: null,

		activitiesItemsCache: null
	};

	const functions = BX.Extension.getSettings('bp_selector').get('functions');
	const functionItems = !functions ? [] : functions.map(func => {
		return {
			text: func.name,
			description: func.description,
			value: '{{=' + func.name + '()}}',
		};
	});
	Object.freeze(functionItems);

	BX.Bizproc.Selector.initSelectors = function(globalScope)
	{
		if (!globalScope)
			globalScope = document;

		var scope, props, i, items = globalScope.querySelectorAll('[data-role="bp-selector-button"]');

		if (items)
		{
			for (i = 0; i < items.length; ++i)
			{
				props = JSON.parse(items[i].getAttribute('data-bp-selector-props'));
				if (typeof props !== 'object')
					continue;

				scope = document.getElementById(props.controlId);

				if (!scope)
					continue;

				BX.bind(scope, 'keydown', function(e) {
					BX.Bizproc.Selector.onSearch(this, e);
				});
				scope.setAttribute('autocomplete', 'off');
			}
		}
	};

	BX.Bizproc.Selector.getTabItems = function(tabId)
	{
		var i;

		for (i = 0; i < this.currentData.length; ++i)
		{
			if (this.currentData[i]['tabId'] === tabId)
				return this.currentData[i]['items'];
		}

		return [];
	};

	BX.Bizproc.Selector.getActivitiesItems = function (nocache)
	{
		if (this.activitiesItemsCache === null || nocache)
		{
			this.activitiesItemsCache = this.getTemplateActivitiesItems([rootActivity.Serialize()], arAllActivities);
		}

		return this.activitiesItemsCache;
	};

	BX.Bizproc.Selector.getTemplateActivitiesItems = function(template, activities)
	{
		var result = [], i, s, activityType, activityData, key;
		for (i = 0, s = template.length; i < s; ++i)
		{
			activityType = template[i].Type.toLowerCase();
			if (activities[activityType])
				activityData =  activities[activityType];

			if (activityData && activityData['RETURN'])
			{
				for (key in activityData['RETURN'])
				{
					if (!activityData['RETURN'].hasOwnProperty(key))
						continue;

					result.push({
						text: activityData['RETURN'][key].NAME,
						description: template[i].Properties.Title || activityData.NAME,
						value: '{='+template[i].Name+':'+key+'}',
						propertyObject: template[i].Name,
						propertyField: key,
						property: {
							Name: activityData['RETURN'][key].NAME,
							Type: activityData['RETURN'][key].TYPE,
							Options: activityData['RETURN'][key].OPTIONS || null,
						}
					});
				}
			}
			else if (activityData && BX.type.isArray(activityData['ADDITIONAL_RESULT']))
			{
				var props = template[i]['Properties'];
				activityData['ADDITIONAL_RESULT'].forEach(function(addProperty)
				{
					if (props[addProperty])
					{
						for (var fieldId in props[addProperty])
						{
							if (props[addProperty].hasOwnProperty(fieldId))
							{
								var field = props[addProperty][fieldId];
								result.push({
									text: field['Name'],
									description: template[i].Properties.Title || activityData.NAME,
									value: '{='+template[i].Name+':'+fieldId+'}',
									propertyObject: template[i].Name,
									propertyField: fieldId,
									property: field
								});
							}
						}
					}
				}, this);
			}

			if (template[i].Children && template[i].Children.length > 0)
			{
				var subResult = this.getTemplateActivitiesItems(template[i].Children, activities);
				for (var j = 0; j < subResult.length; ++j)
				{
					result.push(subResult[j]);
				}
			}
		}
		return result;
	};

	BX.Bizproc.Selector.getTabsCounters = function()
	{
		var i, result = {};

		for (i = 0; i < this.currentData.length; ++i)
		{
			result[this.currentData[i]['tabId']] = this.currentData[i]['items'].length;
		}

		return result;
	};

	BX.Bizproc.Selector.getListElement = function()
	{
		return BX.findChild(this.popup.contentContainer, {className: 'bp-selector-list'}, true);
	};

	BX.Bizproc.Selector.getSelectedItem = function()
	{
		return BX.findChild(this.popup.contentContainer, {className: 'bp-selector-item-selected'}, true);
	};

	BX.Bizproc.Selector.getTabsElements = function()
	{
		return BX.findChildren(this.popup.contentContainer, {className: 'bp-selector-tab'}, true);
	};

	BX.Bizproc.Selector.closePopup = function()
	{
		if (this.popup)
			this.popup.close();
	};

	BX.Bizproc.Selector.insertItemValue = function(item, multiInsert)
	{
		var replaceText = this.inputElement.value.substr(0, this.inputElement.selectionEnd),
			beforePart = this.inputElement.value.substr(0, replaceText.lastIndexOf(this.delimiter)),
			middlePart = item.getAttribute('data-value') + (multiInsert? this.delimiter : ''),
			afterPart = this.inputElement.value.substr(this.inputElement.selectionEnd),
			cursorPosition = parseInt(item.getAttribute('data-cursor-position'));

		if (isNaN(cursorPosition))
			cursorPosition = middlePart.length;

		if (beforePart.substr(-1) === '{')
			beforePart = beforePart.substr(0, beforePart.length - 1);

		this.inputElement.value = beforePart + middlePart + afterPart;
		this.inputElement.selectionEnd = beforePart.length + Math.max(0, cursorPosition) + 1;
	};

	BX.Bizproc.Selector.onSearch = function(scope, e)
	{
		var me = this, result = true;

		if (scope.mentionListen)
		{
			if (e.keyCode == 27)
			{
				scope.mentionListen = false;
				this.closePopup();
				return BX.PreventDefault(e);
			}
			else if (e.keyCode == 13 && this.popup) //ENTER
			{
				var item = this.getSelectedItem();
				if (item)
				{
					this.insertItemValue(item, e.shiftKey === true);
					if (e.shiftKey !== true)
						this.closePopup();
				}

				return BX.PreventDefault(e);
			}
			else if ((e.keyCode == 37 || e.keyCode == 39 || e.keyCode == 9) && this.popup)
			{
				this.SelectNextTab(e.keyCode == 37 ? -1 : 1);
				return BX.PreventDefault(e);
			}
			else if ((e.keyCode == 38 || e.keyCode == 40) && this.popup)
			{
				this.SelectNextItem(e.keyCode == 38 ? -1 : 1);
				return BX.PreventDefault(e);
			}
			else if (e.altKey === true || e.ctrlKey === true || (e.shiftKey === true && e.keyCode == 16))
			{

			}
			else
			{
				setTimeout(BX.delegate(function(){
					var replaceText = this.value.substr(0, this.selectionEnd);
					if (replaceText.lastIndexOf(me.delimiter) < 0)
					{
						me.closePopup();
						return false;
					}
					replaceText = replaceText.substr(replaceText.lastIndexOf(me.delimiter), this.selectionEnd-replaceText.lastIndexOf(me.delimiter));
					if (replaceText.length <= 0)
					{
						me.closePopup();
						return false;
					}
					replaceText = replaceText.substr(1);
					if (replaceText.substr(0, 1) == ' ')
					{
						me.closePopup();
						return false;
					}

					if (me.popup)
						me.updatePopupContent(replaceText);
				},scope), 10)
			}
		}
		else if (e.shiftKey === false && (e.keyCode == me.listenKeyCode || e.key === me.listenKey))
		{
			if (!scope.mentionListen)
			{
				setTimeout(BX.delegate(function(){
					var delimiter = this.value.substr(this.selectionEnd-1, 1);
					if (delimiter != me.delimiter)
						return false;

					this.mentionListen = true;
					me.closePopup();
					me.popup = new BX.PopupWindow('bx-bizproc-selector', this, {
						lightShadow : true,
						offsetTop: 0,
						closeIcon : true,
						offsetLeft: 0,
						autoHide: true,
						bindOptions: {position: "bottom"},
						closeByEsc: true,
						zIndex: 200,
						events : {
							onPopupShow: function()
							{
								//For core_window and core_popup compatibility
								BX.WindowManager.currently_loaded = this;
								this.CloseDialog = this.Close = function()
								{
									BX.WindowManager.currently_loaded = null;
									if (me.popup !== null)
									{
										var wnd = BX.WindowManager.Get();
										if (wnd && !wnd.unclosable) wnd.Close();
									}
								};
							},
							onPopupClose : function() {
								this.destroy();
								setTimeout(function(){BX.WindowManager.currently_loaded = null;}, 50);
							},
							onPopupDestroy : BX.delegate(function() {
								me.popup = null;
								me.activitiesItemsCache = null;
								this.mentionListen = false;
							}, this)
						},
						content : BX.create("DIV", {children: [me.generatePopupContent()]})
					});
					me.popup.show();
					me.inputElement = this;
				},scope), 100)
			}
		}

		if (!result)
			return BX.PreventDefault(e);
	};

	BX.Bizproc.Selector.extractMenuItem = function(text, items, type)
	{
		var result = [];
		var key, value, visibility;
		for (key in items)
		{
			if (!items.hasOwnProperty(key))
				continue;

			value = '{=' + type + ':' + key + '}';
			if (type === 'Document')
			{
				value = '{{' + items[key].Name + '}}';
			}
			else if (
				type === 'GlobalVar'
				&& window.wfGVarVisibilityNames
				&& BX.util.object_keys(window.wfGVarVisibilityNames).length > 0
			)
			{
				visibility = items[key].Visibility;
				value = '{{' + window.wfGVarVisibilityNames[visibility] + ': ' + items[key].Name + '}}';
			}
			else if (
				type === 'GlobalConst'
				&& window.wfGConstVisibilityNames
				&& BX.util.object_keys(window.wfGConstVisibilityNames).length > 0
			)
			{
				visibility = items[key].Visibility;
				value = '{{' + window.wfGConstVisibilityNames[visibility] + ': ' + items[key].Name + '}}';
			}

			result.push({
				text: items[key].Name,
				value: value
			});
		}
		return this.filterItems(result, text);
	};

	BX.Bizproc.Selector.filterItems = function(items, query)
	{
		var result = [], i;
		query = query? query.toLowerCase() : '';
		var altQuery = query && BX.correctText ? BX.correctText(query, {replace_way: 'AUTO', mixed:true}).toLowerCase() : '';
		for (i = 0; i < items.length; ++i)
		{
			if (!query
				|| items[i].text.toLowerCase().indexOf(query) >= 0
				|| items[i].value.toLowerCase().indexOf(query) >= 0
				|| items[i].text.toLowerCase().indexOf(altQuery) >= 0
				|| items[i].value.toLowerCase().indexOf(altQuery) >= 0
			)
			{
				result.push(items[i]);
			}
		}
		return result;
	};

	BX.Bizproc.Selector.updateCurrentData = function(query)
	{
		var result = [];

		if (BX.util.object_keys(arWorkflowParameters).length > 0)
			result.push({
				tabName: BX.message('BIZPROC_JS_BP_SELECTOR_PARAMETERS'),
				tabId: 'parameters',
				items: this.extractMenuItem(query, arWorkflowParameters, 'Template')
			});

		if (BX.util.object_keys(arWorkflowVariables).length > 0)
			result.push({
				tabName: BX.message('BIZPROC_JS_BP_SELECTOR_VARIABLES'),
				tabId: 'variables',
				items: this.extractMenuItem(query, arWorkflowVariables, 'Variable')
			});

		if (BX.util.object_keys(arWorkflowConstants).length > 0)
			result.push({
				tabName: BX.message('BIZPROC_JS_BP_SELECTOR_CONSTANTS'),
				tabId: 'constants',
				items: this.extractMenuItem(query, arWorkflowConstants, 'Constant')
			});

		if (typeof arDocumentFields !== 'undefined')
		{
			result.push({
				tabName: BX.message('BIZPROC_JS_BP_SELECTOR_DOCUMENT'),
				tabId: 'document',
				items: this.extractMenuItem(query, arDocumentFields, 'Document')
			});
		}

		var activitiesItems = this.getActivitiesItems();

		if (activitiesItems.length > 0)
			result.push({
				tabName: BX.message('BIZPROC_JS_BP_SELECTOR_ACTIVITIES'),
				tabId: 'activities',
				items: this.filterItems(activitiesItems, query)
			});

		if (window.arWorkflowGlobalConstants && BX.util.object_keys(window.arWorkflowGlobalConstants).length > 0)
		{
			result.push({
				tabName: '@' + BX.message('BIZPROC_JS_BP_SELECTOR_CONSTANTS'),
				tabId: 'gconstants',
				items: this.extractMenuItem(query, window.arWorkflowGlobalConstants, 'GlobalConst')
			});
		}

		if (window.arWorkflowGlobalVariables && BX.util.object_keys(window.arWorkflowGlobalVariables).length > 0)
		{
			result.push({
				tabName: '@' + BX.message('BIZPROC_JS_BP_SELECTOR_VARIABLES'),
				tabId: 'gvariables',
				items: this.extractMenuItem(query, window.arWorkflowGlobalVariables, 'GlobalVar')
			});
		}

		result.push({
			tabName: BX.message('BIZPROC_JS_BP_SELECTOR_SYSTEM'),
			tabId: 'system',
			items: this.filterItems(
				[
					{
						text: BX.message('BIZPROC_JS_BP_SELECTOR_WORKFLOW_ID'),
						value: '{=Workflow:ID}'
					},
					{
						text: BX.message('BIZPROC_JS_BP_SELECTOR_TARGET_USER'),
						value: '{=Template:TargetUser}'
					},
					{
						text: BX.message('BIZPROC_JS_BP_SELECTOR_USER_ID'),
						value: '{=User:ID}'
					},
					{
						text: BX.message('BIZPROC_JS_BP_SELECTOR_NOW'),
						value: '{=System:Now}'
					},
					{
						text: BX.message('BIZPROC_JS_BP_SELECTOR_NOW_LOCAL'),
						value: '{=System:NowLocal}'
					},
					{
						text: BX.message('BIZPROC_JS_BP_SELECTOR_DATE'),
						value: '{=System:Date}'
					},
					{
						text: BX.message('BIZPROC_JS_BP_SELECTOR_EOL'),
						value: '{=System:Eol}'
					},
					{
						text: BX.message('BIZPROC_JS_BP_SELECTOR_HOST_URL'),
						value: '{=System:HostUrl}'
					},
				],
				query
			)
		});

		result.push({
			tabName: BX.message('BIZPROC_JS_BP_SELECTOR_FUNCTIONS'),
			tabId: 'functions',
			items: this.filterItems(functionItems, query)
		});

		this.currentData = result;
		return result;
	};

	BX.Bizproc.Selector.generatePopupContent = function(text)
	{
		var popup = BX.create('DIV', {attrs: {className: 'bp-selector-popup'}});
		var tabs = BX.create('UL', {attrs: {className: 'bp-selector-tabs'}});
		var list = BX.create('UL', {attrs: {className: 'bp-selector-list'}});

		popup.appendChild(tabs);
		popup.appendChild(list);

		var data = this.updateCurrentData(text);

		var i, tab, me = this;

		for (i = 0; i < data.length; ++i)
		{
			tab = BX.create('LI', {
				attrs: {className: 'bp-selector-tab'},
				html: data[i].tabName+' (<span class="counter-value">'+data[i].items.length+'</span>)'
			});
			tab.setAttribute('data-tab-id', data[i].tabId);
			tab.setAttribute('hidefocus', 'true');

			if (!this.selectedTab && i === 0 || this.selectedTab == data[i].tabId)
			{
				BX.addClass(tab, 'selected');
				this.updateList(list, data[i].items);
			}

			BX.bind(tab, 'click', function(e) {
				me.selectTab(this);
				BX.focus(me.inputElement);
				return BX.PreventDefault(e);
			});

			tabs.appendChild(tab);
		}

		return popup;
	};

	BX.Bizproc.Selector.updatePopupContent = function(text)
	{
		var list = this.getListElement();
		var i, tabId, counterElement, data = this.updateCurrentData(text);

		var counters = this.getTabsCounters();
		var tabs = this.getTabsElements();
		for (i = 0; i < tabs.length; ++i)
		{
			tabId = tabs[i].getAttribute('data-tab-id');

			counterElement = BX.findChild(tabs[i], {className: 'counter-value'});
			counterElement.innerHTML = counters[tabId] || 0;

			if (BX.hasClass(tabs[i], 'selected'))
			{
				this.updateList(list, this.getTabItems(tabId));
			}
		}
	};

	BX.Bizproc.Selector.SelectNextTab = function(direction)
	{
		var selected, selectedId, i, s, targetKey, tabs = this.getTabsElements();
		for (i=0, s = tabs.length; i < s; ++i)
		{
			selected = BX.hasClass(tabs[i], 'selected');

			if (selected && direction < 0)
			{
				targetKey = i-1 >= 0 ? i-1 : s-1;
				BX.removeClass(tabs[i], 'selected');
				BX.addClass(tabs[targetKey], 'selected');
				selectedId = tabs[targetKey].getAttribute('data-tab-id');
				break;
			}
			if (selected && direction > 0)
			{
				targetKey = i+1 <= s-1 ? i+1 : 0;
				BX.removeClass(tabs[i], 'selected');
				BX.addClass(tabs[targetKey], 'selected');
				selectedId = tabs[targetKey].getAttribute('data-tab-id');
				break;
			}
		}

		var list = this.getListElement();
		this.updateList(list, this.getTabItems(selectedId));
		this.selectedTab = selectedId;
	};

	BX.Bizproc.Selector.selectTab = function(tab)
	{
		var list = this.getListElement();
		var tabs = this.getTabsElements();

		for (var i=0; i < tabs.length; ++i)
		{
			if (tabs[i] == tab)
			{
				BX.addClass(tab, 'selected');
				this.updateList(list, this.getTabItems(tab.getAttribute('data-tab-id')));
			}
			else
			{
				BX.removeClass(tabs[i], 'selected');
			}
		}
		this.selectedTab = tab.getAttribute('data-tab-id');
	};

	BX.Bizproc.Selector.SelectNextItem = function(direction)
	{
		var list = this.getListElement();
		var selected, i, s, targetKey = 0, items = BX.findChildren(list, {className: 'bp-selector-item'}, true);
		for (i = 0, s = items.length; i < s; ++i)
		{
			selected = BX.hasClass(items[i], 'bp-selector-item-selected');
			if (selected && direction < 0)
			{
				targetKey = i-1 >= 0 ? i-1 : s-1;
				BX.removeClass(items[i], 'bp-selector-item-selected');
				BX.addClass(items[targetKey], 'bp-selector-item-selected');
				break;
			}
			if (selected && direction > 0)
			{
				targetKey = i+1 <= s-1 ? i+1 : 0;
				BX.removeClass(items[i], 'bp-selector-item-selected');
				BX.addClass(items[targetKey], 'bp-selector-item-selected');
				break;
			}
		}

		this.fixListScroll(list, items[targetKey]);
	};

	BX.Bizproc.Selector.updateList = function(list, items)
	{
		BX.cleanNode(list);

		var i, item, listElement, me = this;
		if (items.length === 0)
		{
			listElement = BX.create('LI', {html: BX.message('BIZPROC_JS_BP_SELECTOR_EMPTY_LIST')});
			BX.addClass(listElement, 'bp-selector-item-empty');
			list.appendChild(listElement);
		}

		for (i = 0; i < items.length; ++i)
		{
			item = items[i];
			listElement = BX.create('LI', {html: BX.util.htmlspecialchars(item.text)
				+(item.description ? '<span class="bp-selector-item-description"> ' + BX.util.htmlspecialchars(item.description) + '</span>' : '')
			});
			listElement.setAttribute('data-value', item.value);
			listElement.setAttribute('data-cursor-position', item.value.indexOf('('));
			listElement.setAttribute('hidefocus', 'true');

			BX.addClass(listElement, 'bp-selector-item');
			if (i === 0)
				BX.addClass(listElement, 'bp-selector-item-selected');

			BX.bind(listElement, 'click', function(e) {
				me.insertItemValue(this, e.shiftKey === true);
				if (e.shiftKey !== true)
					me.closePopup();
				BX.focus(me.inputElement);
				return BX.PreventDefault(e);
			});

			list.appendChild(listElement);
		}

		list.scrollTop = 0;
	};

	BX.Bizproc.Selector.fixListScroll = function(list, item)
	{
		var selectedListPos = BX.pos(list);
		var selectedItemPos = BX.pos(item);

		if (selectedItemPos.bottom > selectedListPos.bottom || selectedItemPos.top < selectedListPos.top)
		{
			list.scrollTop += (
				selectedItemPos.bottom > selectedListPos.bottom
					? (selectedItemPos.bottom - selectedListPos.bottom)
					: (selectedItemPos.top - selectedListPos.top)
			);
		}
	}
})();