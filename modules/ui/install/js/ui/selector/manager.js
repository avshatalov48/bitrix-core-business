(function() {

var BX = window.BX;

BX.namespace('BX.UI');

if (!!BX.UI.SelectorManager)
{
	return;
}

BX.UI.SelectorManager = {

	statuses: {
		searchStarted: false,
		allowSendEvent: true
	},

	extranetUser: false,

	instances: {},

	/* don't rename */
	finderInitialized: false,
	loadAllInitialized: false,
	obClientDb: null,
	obClientDbData: {},
	obClientDbDataSearchIndex: {},
	/* don't rename */

	getSelector: function(params)
	{
		return (
			BX.type.isNotEmptyObject(params)
			&& BX.type.isNotEmptyString(params.id)
			&& BX.type.isNotEmptyObject(this.instances[params.id])
				? this.instances[params.id]
				: false
		);
	},

	buildRelations: function(entities, prefix)
	{
		var relation = {}, p, iid;
		for(iid in entities)
		{
			if (entities.hasOwnProperty(iid))
			{
				p = entities[iid]['parent'];
				if (!relation[p])
				{
					relation[p] = [];
				}
				relation[p].push(iid);
			}
		}
		return this.makeTree(prefix + '0', relation);
	},
	makeTree: function(id, relation)
	{
		var relationsList = {}, relId, itemsList;
		if (relation[id])
		{
			for (var x in relation[id])
			{
				if (relation[id].hasOwnProperty(x))
				{
					relId = relation[id][x];
					itemsList = [];
					if (
						relation[relId]
						&& relation[relId].length > 0
					)
					{
						itemsList = this.makeTree(relId, relation);
					}

					relationsList[relId] = {
						id: relId,
						idFlat: '',
						type: 'category',
						items: itemsList
					};
				}
			}
		}

		return relationsList;
	},

	onAddData: function(id, data)
	{
		var instance = BX.UI.SelectorManager.instances[id];
		if (!BX.type.isNotEmptyObject(instance))
		{
			return;
		}

		var
			entityTypeData = null,
			i = null;

		for (var entityType in data.ENTITIES)
		{
			if (data.ENTITIES.hasOwnProperty(entityType))
			{
				entityTypeData = data.ENTITIES[entityType];

				if (!BX.type.isNotEmptyObject(instance.entities[entityType]))
				{
					instance.entities[entityType] = {
						items: {},
						itemsLast: [],
						itemsHidden: [],
						additionalData: {}
					};
				}

				if (!BX.type.isNotEmptyObject(instance.entities[entityType].items))
				{
					instance.entities[entityType].items = {};
				}

				for (key in entityTypeData.ITEMS)
				{
					if (entityTypeData.ITEMS.hasOwnProperty(key))
					{
						instance.entities[entityType].items[key] = entityTypeData.ITEMS[key];
					}
				}

				if (
					BX.type.isNotEmptyObject(entityTypeData.ADDITIONAL_INFO)
					&& BX.type.isNotEmptyObject(entityTypeData.ADDITIONAL_INFO)
				)
				{
					instance.entities[entityType].additionalData = entityTypeData.ADDITIONAL_INFO;

					if (
						BX.type.isNotEmptyString(entityTypeData.ADDITIONAL_INFO.TYPE)
						&& entityTypeData.ADDITIONAL_INFO.TYPE == 'tree'
						&& BX.type.isNotEmptyString(entityTypeData.ADDITIONAL_INFO.PREFIX)
					)
					{
						instance.entities[entityType + '_RELATION'] = {
							items: BX.UI.SelectorManager.buildRelations(instance.entities[entityType].items, entityTypeData.ADDITIONAL_INFO.PREFIX)
						};
					}

					if (BX.type.isNotEmptyObject(entityTypeData.ADDITIONAL_INFO.GROUPS_LIST))
					{
						for (key in entityTypeData.ADDITIONAL_INFO.GROUPS_LIST)
						{
							if (entityTypeData.ADDITIONAL_INFO.GROUPS_LIST.hasOwnProperty(key))
							{
								instance.dialogGroups[key] = entityTypeData.ADDITIONAL_INFO.GROUPS_LIST[key];
							}
						}
					}
				}

				if (
					BX.type.isArray(entityTypeData.ITEMS_LAST)
					&& entityTypeData.ITEMS_LAST.length > 0
				)
				{
					if (!BX.type.isArray(instance.entities[entityType].itemsLast))
					{
						instance.entities[entityType].itemsLast = [];
					}

					for (i=0; i < entityTypeData.ITEMS_LAST.length; i++)
					{
						instance.entities[entityType].itemsLast.push(entityTypeData.ITEMS_LAST[i]);
					}
				}

				if (
					BX.type.isArray(entityTypeData.ITEMS_HIDDEN)
					&& entityTypeData.ITEMS_HIDDEN.length > 0
				)
				{
					if (!BX.type.isArray(instance.entities[entityType].itemsHidden))
					{
						instance.entities[entityType].itemsHidden = [];
					}

					for (i=0; i < entityTypeData.ITEMS_HIDDEN.length; i++)
					{
						instance.entities[entityType].itemsHidden.push(entityTypeData.ITEMS_HIDDEN[i]);
					}
				}
			}
		}

		if (BX.type.isNotEmptyObject(data.TABS))
		{
			instance.tabs.list = data.TABS;
		}

		if (BX.type.isNotEmptyObject(data.SORT))
		{
			instance.sortData = data.SORT;
		}

		if (instance.callback.select)
		{
			var
				itemsSelectedSorted = instance.getItemsSelectedSorted(),
				itemId = null,
				fullList = null,
				k = 0;

			// select visible
			for (i = 0; i < itemsSelectedSorted.length; i++)
			{
				itemId = itemsSelectedSorted[i].itemId;
				entityType = BX.UI.SelectorManager.convertEntityType(itemsSelectedSorted[i].entityType);
				fullList = BX.UI.SelectorManager.getEntityTypeFullList(entityType);

				for (k=0; k < fullList.length; k++)
				{
					if (
						BX.type.isNotEmptyObject(instance.entities[fullList[k]])
						&& BX.type.isNotEmptyObject(instance.entities[fullList[k]].items)
						&& BX.type.isNotEmptyObject(instance.entities[fullList[k]].items[itemId])
					)
					{
						instance.callback.select({
							item: instance.entities[fullList[k]].items[itemId],
							entityType: fullList[k],
							selectorId: instance.id,
							undeletable: (BX.util.in_array(itemId, instance.itemsUndeletable)),
							state: 'init'
						});
						break;
					}
				}
			}

			// select hidden
			for (entityType in instance.entities)
			{
				if (
					instance.entities.hasOwnProperty(entityType)
					&& BX.type.isArray(instance.entities[entityType].itemsHidden)
					&& instance.entities[entityType].itemsHidden.length > 0
				)
				{
					for (i = 0; i < instance.entities[entityType].itemsHidden.length; i++)
					{
						instance.callback.select({
							item: {
								id: instance.entities[entityType].itemsHidden[i],
								name: BX.message('MAIN_UI_SELECTOR_HIDDEN_TITLE')
							},
							entityType: entityType,
							selectorId: instance.id,
							undeletable: true,
							state: 'init'
						});
					}
				}
			}
		}

		BX.onCustomEvent('BX.Main.SelectorV2:onAfterAddData', [ {
			selectorId: instance.id
		} ]);
	},

	convertEntityType: function(entityType)
	{
		entityType = entityType.toUpperCase();

		switch(entityType)
		{
			case 'DEPARTMENT':
				entityType = 'DEPARTMENTS';
				break;
			default:
		}

		return entityType;
	},

	getEntityTypeFullList: function(entityType)
	{
		entityType = entityType.toUpperCase();

		var result = [ entityType ];

		switch(entityType)
		{
			case 'USERS':
				result.push('EMAILUSERS');
				break;
			default:
		}

		return result;
	},

	checkEmail: function(searchString)
	{
		searchString = searchString.trim();

		var re = /^([^<]+)\s<([^>]+)>$/igm;
		var matches = re.exec(searchString);
		var userName = '';
		var userLastName = '';

		if (
			matches != null
			&& matches.length == 3
		)
		{
			userName = matches[1];
			var parts = userName.split(/[\s]+/);
			userLastName = parts.pop();
			userName = parts.join(' ');

			searchString = matches[2].trim();
		}

		re = /^[=_0-9a-z+~'!\$&*^`|\#%/?{}-]+(\.[=_0-9a-z+~'!\$&*^`|\#%/?{}-]+)*@(([-0-9a-z_]+\.)+)([a-z0-9-]{2,20})$/igm;

		if (
			searchString.length >= 6
			&& re.test(searchString)
		)
		{
			return {
				name: userName,
				lastName: userLastName,
				email: searchString.toLowerCase()
			};
		}
		else
		{
			return false;
		}
	}
};

BX.ready(function () {

	BX.addCustomEvent('BX.Main.SelectorV2:onAddData', BX.UI.SelectorManager.onAddData);

	BX.addCustomEvent('BX.UI.SelectorManager:initClientDatabase', function() {
		if (!BX.UI.SelectorManager.finderInitialized)
		{
			BX.Finder(false, 'destination', [], {}, BX.UI.SelectorManager);
			BX.onCustomEvent(BX.UI.SelectorManager, 'initFinderDb', [ BX.UI.SelectorManager, null, null, [ 'users' ], BX.UI.SelectorManager]);
			BX.UI.SelectorManager.finderInitialized = true;
		}
	});

	BX.addCustomEvent('loadAllFinderDb', function(params) {
		if (!BX.UI.SelectorManager.loadAllInitialized)
		{
			BX.onCustomEvent('BX.UI.SelectorManager:loadAll', [ params ]);
		}
		BX.UI.SelectorManager.loadAllInitialized = true;
	});

	BX.addCustomEvent('BX.Main.SelectorV2:onGetDataStart', function(selectorId) {

		var selectorInstance = BX.UI.SelectorManager.instances[selectorId];
		if (!BX.type.isNotEmptyObject(selectorInstance))
		{
			return;
		}

		selectorInstance.postponeSearch = true;
	});

	BX.addCustomEvent('BX.Main.SelectorV2:onGetDataFinish', function(selectorId) {

		var selectorInstance = BX.UI.SelectorManager.instances[selectorId];
		if (!BX.type.isNotEmptyObject(selectorInstance))
		{
			return;
		}



		selectorInstance.postponeSearch = false;
	});
});

})();
