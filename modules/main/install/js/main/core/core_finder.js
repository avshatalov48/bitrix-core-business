(function(window) {

if (BX.Finder)
{
	return;
}

BX.FinderManager = {
	checkInitHandlerAdded: false,
	initHandlerAdded: false,
	initHandler2Added: false
};

BX.Finder = function(container, context, panels, lang, oContext)
{
	if (typeof oContext == 'undefined')
	{
		oContext = window;
	}

	BX.Finder.container = container;
	BX.Finder.context = context.toLowerCase();
	BX.Finder.panels = panels;
	BX.Finder.lang = lang;
	BX.Finder.elements = [];
	BX.Finder.mapElements = [];
	BX.Finder.searchBox = [];
	BX.Finder.searchTab = [];
	BX.Finder.searchPanel = [];
	BX.Finder.selectedProvider = {};
	BX.Finder.selectedElement = {};
	BX.Finder.selectedElements = [];
	BX.Finder.selectedType = {};
	BX.Finder.disabledId = [];
	BX.Finder.disabledElement = [];
	BX.Finder.searchTimeout = null;
	BX.Finder.loadPlace = {};

	if (BX.Finder.context == 'access')
	{
		BX.Finder.elements = BX.findChildren(container, { className : "bx-finder-element" }, true);
		for (var i = 0; i < BX.Finder.elements.length; i++)
		{
			BX.Finder.mapElements[i] = BX.Finder.elements[i].getAttribute('rel');
			BX.Finder.onDisableItem(i);
		}

		BX.addCustomEvent(BX.Access, "onSelectProvider", BX.Finder.onSelectProvider);
		BX.addCustomEvent(BX.Access, "onDeleteItem", BX.Finder.onDeleteItem);
		BX.addCustomEvent(BX.Access, "onAfterPopupShow", BX.Finder.onAfterPopupShow);
	}

	BX.Finder.dBScheme = {
		stores: [
			{
				name: 'users',
				keyPath: 'id',
				autoIncrement: true,
				indexes: [
					{
						name: 'id',
						keyPath: 'id',
						unique: true
					},
					{
						name: 'checksum',
						keyPath: 'checksum',
						unique: true
					}
				]
			},
			{
				name: 'sonetgroups',
				keyPath: 'id',
				autoIncrement: true,
				indexes: [
					{
						name: 'id',
						keyPath: 'id',
						unique: true
					},
					{
						name: 'checksum',
						keyPath: 'checksum',
						unique: true
					}
				]
			},
			{
				name: 'menuitems',
				keyPath: 'id',
				autoIncrement: true,
				indexes: [
					{
						name: 'id',
						keyPath: 'id',
						unique: false
					},
					{
						name: 'checksum',
						keyPath: 'checksum',
						unique: true
					}
				]
			}
		],
		version: "2"
	};

	BX.Finder.dBVersion = 8;

	if (BX.util.in_array(BX.Finder.context, ['destination', 'searchtitle']))
	{
		BX.addCustomEvent(oContext, "initFinderDb", function(obDestination, name, version, entities, oContext) {
			setTimeout(function() {
				BX.Finder.checkInitFinderDb(obDestination, name, BX.Finder.dBVersion, entities, oContext)
			}, 600)
		});
	}
};

BX.Finder.onAddItem = function(provider, type, element)
{
	elementId = BX(element).getAttribute('rel');

	if (BX.Finder.selectedElement[elementId])
	{
		if (BX.Finder.context == 'access')
		{
			for (var i = 0; i < BX.Finder.selectedElement[elementId].length; i++)
			{
				BX.removeClass(BX.Finder.selectedElement[elementId][i], 'bx-finder-box-item-selected');
			}
			BX.Access.RemoveSelection(provider, elementId);
		}
		else
			BX.Finder.onDeleteItem({'provider': provider, 'id': elementId});

		return false;
	}

	if (!BX.Finder.selectedElement[elementId])
		BX.Finder.selectedElement[elementId] = [];

	BX.Finder.selectedElement[elementId].push(element);

	BX.addClass(element, 'bx-finder-box-item-selected');

	if (type == 1)
	{
		elementTextBox = BX.findChild(element, { className : "bx-finder-box-item-text" }, true);
	}
	else if (type == 2)
	{
		elementTextBox = BX.findChild(element, { className : "bx-finder-box-item-t2-text" }, true);
	}
	else if (type == 3)
	{
		elementTextBox = BX.findChild(element, { className : "bx-finder-box-item-t3-name" }, true);
	}
	else if (type == 4)
	{
		elementTextBox = BX.findChild(element, { className : "bx-finder-box-item-t3-name" }, true);
	}
	else if (type == 5)
	{
		elementTextBox = BX.findChild(element, { className : "bx-finder-box-item-t5-name" }, true);
	}
	else if (type == 'structure')
	{
		elementTextBox = BX.findChild(element, { className : "bx-finder-company-department-employee-name" }, true);
	}
	else if (type == 'structure-checkbox')
	{
		elementTextBox = BX.findChild(element, { className : "bx-finder-company-department-check-text" }, true);
	}

	if (type == 'structure-checkbox')
		elementText = elementTextBox.getAttribute('rel');
	else
		elementText = elementTextBox.innerHTML;

	if (BX.Finder.context == 'access')
		BX.Access.AddSelection({'provider': provider, 'id': elementId, 'name': elementText});

	return false;
};

BX.Finder.onDeleteItem = function(arParams)
{
	if (BX.Finder.selectedElement[arParams['id']])
	{
		for (var i = 0; i < BX.Finder.selectedElement[arParams['id']].length; i++)
		{
			BX.removeClass(BX.Finder.selectedElement[arParams['id']][i], 'bx-finder-box-item-selected');
		}
	}

	delete BX.Finder.selectedElement[arParams['id']];

	return false;
};

BX.Finder.onAfterPopupShow = function()
{
	if (BX.Finder.context == 'access')
	{
		for (var i = 0; i < BX.Finder.mapElements.length; i++)
			BX.Finder.onDisableItem(i);

		BX.Finder.onUnDisableItem();

		BX.addCustomEvent(BX.Access, "onDeleteItem", BX.Finder.onDeleteItem);
	}
};

BX.Finder.onSelectProvider = function(arParams)
{
	if (!BX.Finder.searchBox[arParams['provider']])
		BX.Finder.searchBox[arParams['provider']] = BX.findChild(BX('access_provider_'+arParams['provider']), { tagName : "input", className : "bx-finder-box-search-textbox" }, true);

	BX.focus(BX.Finder.searchBox[arParams['provider']]);
};

BX.Finder.onDisableItem = function(mapId)
{
	element = BX.Finder.elements[mapId];
	elementId = BX.Finder.mapElements[mapId];
	if (BX.Finder.context == 'access' && BX.Access.obAlreadySelected[elementId])
	{
		if (BX.Access.showSelected)
		{
			BX.addClass(element, 'bx-finder-box-item-selected');
			if (!BX.Finder.selectedElement[elementId])
				BX.Finder.selectedElement[elementId] = [];

			BX.Finder.selectedElement[elementId].push(element);
		}
		else if (BX.util.array_search(element, BX.Finder.disabledElement) == -1)
		{
			BX.addClass(element, 'bx-finder-element-disabled');
			if (element.getAttribute('onclick') != '')
			{
				element.setAttribute('proxy_onclick', element.getAttribute('onclick'));
				element.setAttribute('onclick', '');
			}
			BX.Finder.disabledId.push(elementId);
			BX.Finder.disabledElement.push(element);
		}
	}
};

BX.Finder.onUnDisableItem = function()
{
	for (var i = 0; i < BX.Finder.disabledId.length; i++)
	{
		if (typeof(BX.Finder.disabledId[i]) == 'undefined')
			continue;

		if (BX.Finder.context == 'access' && !BX.Access.showSelected && BX.Access.obAlreadySelected[BX.Finder.disabledId[i]])
			continue;

		BX.removeClass(BX.Finder.disabledElement[i], 'bx-finder-element-disabled');
		BX.Finder.disabledElement[i].setAttribute('onclick', BX.Finder.disabledElement[i].getAttribute('proxy_onclick'));
		BX.Finder.disabledElement[i].setAttribute('proxy_onclick', '');
		delete BX.Finder.disabledId[i];
		delete BX.Finder.disabledElement[i];
	}
};

BX.Finder.SwitchTab = function(currentTab, bSearchFocus)
{
	var tabsContent = BX.findChildren(
		BX.findChild(currentTab.parentNode.parentNode, { tagName : "td", className : "bx-finder-box-tabs-content-cell"}, true),
		{ tagName : "div" }
	);

	if (!tabsContent)
	{
		return false;
	}

	if (bSearchFocus !== false)
		bSearchFocus = true;

	var tabIndex = 0;
	var tabs = BX.findChildren(currentTab.parentNode, { tagName : "a" });
	for (var i = 0; i < tabs.length; i++)
	{
		if (tabs[i] === currentTab)
		{
			BX.addClass(tabs[i], "bx-finder-box-tab-selected");
			tabIndex = i;
			if (bSearchFocus && BX.hasClass(tabs[i], 'bx-finder-box-tab-search'))
				BX.focus(BX.findChild(tabs[i].parentNode.parentNode, { tagName : "input", className : "bx-finder-box-search-textbox" }, true));
		}
		else
			BX.removeClass(tabs[i], "bx-finder-box-tab-selected");
	}

	for (i = 0; i < tabsContent.length; i++)
	{
		if (tabIndex === i)
			BX.addClass(tabsContent[i], "bx-finder-box-tab-content-selected");
		else
			BX.removeClass(tabsContent[i], "bx-finder-box-tab-content-selected");
	}
	return false;
};

BX.Finder.OpenCompanyDepartment = function(provider, id, department)
{
	BX.toggleClass(department, "bx-finder-company-department-opened");

	var nextDiv = BX.findNextSibling(department, { tagName : "div"} );
	if (BX.hasClass(nextDiv, "bx-finder-company-department-children"))
		BX.toggleClass(nextDiv, "bx-finder-company-department-children-opened");

	if (!BX.Finder.loadPlace[id])
	{
		BX.Finder.loadPlace[id] = BX.findChild(nextDiv, { className : "bx-finder-company-department-employees" });

		var ajaxSendUrl = null;
		if (BX.Finder.context == 'access')
		{
			ajaxSendUrl = '/bitrix/tools/access_dialog.php';
		}
		else
		{
			ajaxSendUrl = location.href.split('#');
			ajaxSendUrl = ajaxSendUrl[0];
		}
		BX.ajax({
			url: ajaxSendUrl,
			method: 'POST',
			dataType: 'html',
			processData: true,
			data: {'mode': 'ajax', 'action' : 'structure-item', 'provider' : provider, 'item' : id, 'sessid': BX.bitrix_sessid(), 'site_id': BX.message('SITE_ID')||''},
			onsuccess: function(data)	{
				BX.Finder.loadPlace[id].innerHTML = data;

				newElements = BX.findChildren(BX.Finder.loadPlace[id], { className : "bx-finder-element" }, true);
				for (var i = 0; i < newElements.length; i++)
				{
					BX.Finder.elements.push(newElements[i]);
					BX.Finder.mapElements.push(newElements[i].getAttribute('rel'));
					BX.Finder.onDisableItem(BX.Finder.mapElements.length-1);
				}

			},
			onfailure: function(data)	{}
		});
	}

	return false;
};

BX.Finder.OpenItemFolder = function(department)
{
	BX.toggleClass(department, "bx-finder-company-department-opened");

	var nextDiv = BX.findNextSibling(department, { tagName : "div"} );
	if (BX.hasClass(nextDiv, "bx-finder-company-department-children"))
		BX.toggleClass(nextDiv, "bx-finder-company-department-children-opened");

	return false;
};

BX.Finder.Search = function(element, provider)
{

	if (!BX.Finder.searchTab[provider])
		BX.Finder.searchTab[provider] = BX.findChild(element.parentNode.parentNode, { className : "bx-finder-box-tab-search" }, true);

	BX.Finder.SwitchTab(BX.Finder.searchTab[provider], false);


	if (!BX.Finder.searchPanel[provider])
		BX.Finder.searchPanel[provider] = BX.findChild(element.parentNode.parentNode, { className : "bx-finder-box-tab-content-selected" }, true);

	var ajaxSendUrl = null;
	if (BX.Finder.context == 'access')
	{
		ajaxSendUrl = '/bitrix/tools/access_dialog.php';
	}
	else
	{
		ajaxSendUrl = location.href.split('#');
		ajaxSendUrl = ajaxSendUrl[0];
	}

	clearTimeout(BX.Finder.searchTimeout);
	if (element.value != '')
	{
		BX.Finder.searchTimeout = setTimeout(function() {
			BX.Finder.searchTimeout = setTimeout(function() {
				if (BX.Finder.searchPanel[provider].innerHTML == '')
				{
					BX.Finder.searchPanel[provider].appendChild(
						BX.create('div', {	'props': {'className': 'bx-finder-search-wait', 'innerHTML': BX.Finder.lang['text-search-wait']}	})
					);
				}
			}, 3000);
			BX.ajax({
				url: ajaxSendUrl,
				method: 'POST',
				dataType: 'html',
				processData: true,
				data: {'mode': 'ajax', 'action' : 'search', 'provider' : provider, 'search' : element.value, 'sessid': BX.bitrix_sessid(), 'site_id': BX.message('SITE_ID')||''},
				onsuccess: function(data)	{
					if (data == '')
					{
						BX.Finder.searchPanel[provider].innerHTML = '';
						BX.Finder.searchPanel[provider].appendChild(
							BX.create('div', {	'props': {'className': 'bx-finder-item-text', 'innerHTML': BX.Finder.lang['text-search-no-result']}	})
						);
					}
					else
					{
						BX.Finder.searchPanel[provider].innerHTML = data;

						newElements = BX.findChildren(BX.Finder.searchPanel[provider], { className : "bx-finder-element" }, true);
						for (var i = 0; i < newElements.length; i++)
						{
							BX.Finder.elements.push(newElements[i]);
							BX.Finder.mapElements.push(newElements[i].getAttribute('rel'));
							BX.Finder.onDisableItem(BX.Finder.mapElements.length-1);
						}
					}
					clearTimeout(BX.Finder.searchTimeout);
				},
				onfailure: function(data)	{}
			});
		}, 500);
	}
};

BX.Finder.checkInitFinderDb = function(obDestination, name, version, entities, oContext)
{
	if (
		typeof version == 'undefined'
		|| parseInt(version) <= 0
	)
	{
		version = 6;
	}

	if (typeof entities == 'undefined')
	{
		entities = ['users'];
	}

	if (typeof oContext == 'undefined')
	{
		oContext = window;
	}

	BX.indexedDB({
		name: 'BX.Finder' + version + '.' + BX.message('USER_ID'),
		scheme: BX.Finder.dBScheme.stores,
		version: BX.Finder.dBScheme.version
	}).then(BX.delegate(function (dbObject) {
	
		obDestination.obClientDb = dbObject;

		if (!BX.FinderManager.checkInitHandlerAdded)
		{
			BX.addCustomEvent("onFinderAjaxLoadAll", BX.Finder.onFinderAjaxLoadAll);
			BX.FinderManager.checkInitHandlerAdded = true;
		}

		var entity = null;
		var entitiesToInit = [];

		for(var i=0;i<this.entities.length;i++)
		{
			entity = this.entities[i];

			BX.indexedDB.count(dbObject, entity).then(BX.delegate(function(count) {
				if (parseInt(count) > 0) // already not empty
				{
					entitiesToInit.push(this.entity);
				}
				else
				{
					BX.Finder.loadAll({
						ob: obDestination,
						name: name,
						entity: this.entity,
						callback: BX.delegate(function()
						{
							BX.Finder.initFinderDb(obDestination, [ this.entity ], oContext, version);

							if (version > 1)
							{
								for (var i = 1; i < version; i++)
								{
									BX.indexedDB.deleteDatabase('BX.Finder' + i + '.' + BX.message('USER_ID'), null);
								}
							}
						}, { entity: this.entity })
					});
				}
			}, { entity: entity }));
		}

		setTimeout(function() {
			BX.Finder.initFinderDb(obDestination, entitiesToInit, oContext, version);
		}, 1000);
		
	}, { entities: entities }));
};

BX.Finder.initFinderDb = function(obDestination, entities, oContext, version)
{
	if (typeof entities == 'undefined')
	{
		entities = [ 'users' ];
	}

	if (typeof oContext == 'undefined')
	{
		oContext = window;
	}

	BX.indexedDB({
		name: 'BX.Finder' + version + '.' + BX.message('USER_ID'),
		scheme: BX.Finder.dBScheme.stores,
		version: BX.Finder.dBScheme.version
	}).then(BX.delegate(function(dbObject) {
		for (var i=0;i<entities.length;i++)
		{
			BX.indexedDB.openCursor(dbObject, entities[i]).then(BX.proxy(function(values) {
				var cursorValue = '';
				for (var j = 0; j < values.length; j++)
				{
					cursorValue = values[j].value;

					if (typeof obDestination.obClientDbData[this.entity] == 'undefined')
					{
						obDestination.obClientDbData[this.entity] = {};
						if (!BX.FinderManager.initHandlerAdded)
						{
							BX.addCustomEvent("findEntityByName", BX.Finder.findEntityByName);
							BX.addCustomEvent("syncClientDb", BX.Finder.syncClientDb);
							if (BX.type.isNotEmptyObject(BX.UI.SelectorManager))
							{
								BX.addCustomEvent(BX.UI.SelectorManager, "syncClientDb", BX.Finder.syncClientDbNew);
							}
							BX.addCustomEvent("removeClientDbObject", BX.Finder.removeClientDbObject);
							BX.FinderManager.initHandlerAdded = true;
						}
					}

					obDestination.obClientDbData[this.entity][cursorValue.id] = cursorValue;
					BX.Finder.addSearchIndex(obDestination, cursorValue);
				}
			}, { entity: entities[i] }));
		}

		if (!BX.FinderManager.initHandler2Added)
		{
			BX.removeAllCustomEvents(oContext, "onFinderAjaxSuccess");
			BX.addCustomEvent(oContext, "onFinderAjaxSuccess", BX.Finder.onFinderAjaxSuccess);
			if (typeof oContext.finderInitialized != 'undefined')
			{
				BX.FinderManager.initHandler2Added[oContext] = true;
			}
		}

	}, { entities: entities }));
};

BX.Finder.addSearchIndex = function(obDestination, ob)
{
	if (
		BX.type.isNotEmptyObject(ob)
		&& BX.type.isNotEmptyString(ob.name)
	)
	{
		var partsSearchText = ob.name.toLowerCase().split(" ");
		for (var i in partsSearchText)
		{
			if (typeof obDestination.obClientDbDataSearchIndex[partsSearchText[i]] == 'undefined')
			{
				obDestination.obClientDbDataSearchIndex[partsSearchText[i]] = [];
			}

			if (!BX.util.in_array(ob.id, obDestination.obClientDbDataSearchIndex[partsSearchText[i]]))
			{
				obDestination.obClientDbDataSearchIndex[partsSearchText[i]].push(ob.id);
			}
		}
	}
};

BX.Finder.findEntityByName = function(obDestination, obSearch, oParams, oResult)
{
	var keysFiltered = Object.keys(obDestination.obClientDbDataSearchIndex).filter(function(key) {
		return (key.indexOf(obSearch.searchString.toLowerCase()) === 0);
	});
	if (
		keysFiltered.length <= 0
		&& BX.message('LANGUAGE_ID') == 'ru'
		&& BX.correctText
	)
	{
		obSearch.searchString = BX.correctText(obSearch.searchString);
		keysFiltered = Object.keys(obDestination.obClientDbDataSearchIndex).filter(function(key) {
			return (key.indexOf(obSearch.searchString.toLowerCase()) === 0);
		});
	}

	var arResult = [];
	for (var key in keysFiltered)
	{
		if (keysFiltered.hasOwnProperty(key))
		{
			BX.util.array_merge(arResult, obDestination.obClientDbDataSearchIndex[keysFiltered[key]]);
		}
	}

	oResult[obSearch.searchString.toLowerCase()] = BX.util.array_unique(arResult);
};

BX.Finder.onFinderAjaxSuccess = function(data, obDestination, entity)
{
	if (typeof entity == 'undefined')
	{
		entity = 'users'
	}

	if (typeof data != 'undefined')
	{
		for (var key in data)
		{
			if (data.hasOwnProperty(key))
			{
				oEntity = data[key];
				if (
					typeof obDestination.obClientDbData[entity] == 'undefined'
					|| typeof obDestination.obClientDbData[entity][oEntity.id] == 'undefined'
					|| obDestination.obClientDbData[entity][oEntity.id].checksum != oEntity.checksum
				)
				{
					if (typeof obDestination.obClientDbData[entity] == 'undefined')
					{
						obDestination.obClientDbData[entity] = [];
					}

					BX.indexedDB.updateValue(obDestination.obClientDb, entity, oEntity).catch(function(error) {
						BX.indexedDB.deleteValueByIndex(obDestination.obClientDb, entity, 'id', error.params.id);
					});

					obDestination.obClientDbData[entity][oEntity.id] = oEntity;
					BX.Finder.addSearchIndex(obDestination, oEntity);
				}
			}
		}
	}
};

BX.Finder.onFinderAjaxLoadAll = function(data, obDestination, entity)
{
	if (typeof BX.Finder.onFinderAjaxLoadAll.loadedEntities == 'undefined')
	{
		BX.Finder.onFinderAjaxLoadAll.loadedEntities = [];
	}

	if (BX.util.in_array(entity, BX.Finder.onFinderAjaxLoadAll.loadedEntities))
	{
		return;
	}

	BX.Finder.onFinderAjaxLoadAll.loadedEntities.push(entity);

	if (typeof entity == 'undefined')
	{
		entity = 'users';
	}
	if (typeof data != 'undefined')
	{
		for (var key in data)
		{
			if (data.hasOwnProperty(key))
			{
				oEntity = data[key];
				BX.indexedDB.updateValue(obDestination.obClientDb, entity, oEntity);
			}
		}
	}
};

BX.Finder.syncClientDb = function(obDestination, name, oDbData, oAjaxData, store)
{
	store = (BX.type.isNotEmptyString(store) ? store : 'users');

	if (
		typeof oDbData != 'undefined'
		&& typeof oAjaxData != 'undefined'
	)
	{
		for (var key in oDbData)
		{
			if (
				oDbData.hasOwnProperty(key)
				&& !BX.util.in_array(oDbData[key], oAjaxData)
			)
			{
				BX.indexedDB.deleteValueByIndex(obDestination.obClientDb, store, 'id', oDbData[key]);

				if (BX.type.isNotEmptyString(name))
				{
					delete obDestination.obItems[name].users[oDbData[key]];
					obDestination.deleteItem(oDbData[key], store, name);
				}
			}
		}
	}
};

BX.Finder.syncClientDbNew = function(params)
{
	var
		selectorInstance = (BX.type.isNotEmptyObject(params.selectorInstance) ? params.selectorInstance : false),
		store = (BX.type.isNotEmptyString(params.store) ? params.store : 'users'),
		clientDBData = (typeof params.clientDBData != 'undefined' ? params.clientDBData : []),
		ajaxData = (typeof params.ajaxData != 'undefined' ? params.ajaxData : []);

	if (!selectorInstance)
	{
		return;
	}

	for (var key = 0; key < clientDBData.length; key++)
	{
		if (!BX.util.in_array(clientDBData[key], ajaxData))
		{
			BX.indexedDB.deleteValueByIndex(selectorInstance.manager.obClientDb, store, 'id', clientDBData[key]);

			delete selectorInstance.entities.USERS.items[clientDBData[key]];
			selectorInstance.getRenderInstance().deleteItem({
				itemId: clientDBData[key],
				entityType: store
			});
		}
	}
};

BX.Finder.removeClientDbObject = function(obDestination, id, type)
{
	if (
		typeof type != 'undefined'
		&& type == 'users'
	)
	{
		BX.indexedDB.deleteValueByIndex(obDestination.obClientDb, 'users', 'id', id);
	}
};

BX.Finder.clearEntityDb = function(obClientDb, type)
{
	BX.indexedDB.clearObjectStore(obClientDb, type);
};

BX.Finder.loadAll = function(params)
{
	BX.onCustomEvent('loadAllFinderDb', [ params ]);
};

})(window);
