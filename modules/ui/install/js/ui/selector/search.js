(function() {

var BX = window.BX;

BX.namespace('BX.UI');

if (!!BX.UI.Selector.Search)
{
	return;
}

BX.UI.Selector.Search = function(params)
{
	this.selectorInstance = params.selectorInstance;
	this.selectorManager = this.selectorInstance.manager;
};

BX.UI.Selector.Search.create = function(params)
{
	return new BX.UI.Selector.Search(params);
};

BX.UI.Selector.Search.prototype.beforeSearchHandler = function(params)
{
	var
		inputNode = this.selectorInstance.nodes.input,
		event = params.event;

	if (!inputNode)
	{
		return false;
	}

	if (
		event.keyCode == 8
		&& inputNode.value.length <= 0
	)
	{
		this.selectorManager.statuses.allowSendEvent = false;
		this.selectorInstance.deleteLastItem();
	}
	else if (event.keyCode == 13)
	{
		this.selectorManager.statuses.searchStarted = true;
		event.stopPropagation();
		return event.preventDefault();
	}
	else if
	(
		event.keyCode == 17 // ctrl
		|| event.keyCode == 224 // cmd
		|| event.keyCode == 91 // left cmd
		|| event.keyCode == 93 // right cmd
	)
	{
		event.stopPropagation();
		return event.preventDefault();
	}

	this.selectorManager.statuses.searchStarted = true;

	var navigationKeys = this.selectorInstance.getNavigationInstance().keys;

	if (
		this.selectorInstance.isSearchOpen() &&
		(
			event.keyCode == navigationKeys.up
			|| event.keyCode == navigationKeys.down
			|| event.keyCode == navigationKeys.left
			|| event.keyCode == navigationKeys.right
		)
	)
	{
		event.stopPropagation();
		event.preventDefault();
		return false;
	}
	return true;
};

BX.UI.Selector.Search.prototype.searchHandler = function(params)
{
	var
		inputNode = this.selectorInstance.nodes.input,
		tagNode = this.selectorInstance.nodes.tag,
		event = params.event;

	if (!inputNode)
	{
		return false;
	}

	if (
		!this.selectorManager.statuses.searchStarted
		&& event.type != 'paste'
	)
	{
		return false;
	}

	this.selectorManager.statuses.searchStarted = false;

	if (
		event.type != 'paste'
		&& (
			event.keyCode == 16
			|| event.keyCode == 17 // ctrl
			|| event.keyCode == 18
			|| event.keyCode == 20
			|| event.keyCode == 244
			|| event.keyCode == 224 // cmd
			|| event.keyCode == 91 // left cmd
			|| event.keyCode == 93 // right cmd
			|| event.keyCode == 9 // tab
		)
	)
	{
		return false;
	}

	BX.onCustomEvent('BX.UI.SelectorManager:beforeRunSearch', [ {
		selectorInstance: this.selectorInstance
	} ]);

	var type = null;
	if (this.selectorInstance.popups.search != null)
	{
		type = 'search';
	}
	else if (
		typeof event.keyCode != 'undefined'
		&& BX.util.in_array(event.keyCode, [37,38,39,40,13])
		&& BX.util.in_array(this.selectorInstance.tabs.selected, [ 'department' ])
	)
	{
		return true;
	}
	else
	{
		type = this.selectorInstance.tabs.selected;
	}

	if (
		typeof event.keyCode != 'undefined'
		&& type
	)
	{
		var keyboardNavigation = this.selectorInstance.getNavigationInstance().checkKeyboardNavigation({
			keyCode: event.keyCode,
			tab: type
		});

		if (keyboardNavigation == 'space')
		{
			event.stopPropagation();
			event.preventDefault();
			return true;
		}
		else if (
			keyboardNavigation == 'move'
			|| keyboardNavigation == 'enter'
		)
		{
			event.stopPropagation();
			event.preventDefault();
			return false;
		}
	}

	var searchText = '';
	if (event.keyCode == 27) // esc
	{
		if (
			!this.selectorInstance.popups.inviteEmailUser
			|| !this.selectorInstance.popups.inviteEmailUser.isShown()
		)
		{
			inputNode.value = '';
			if (tagNode)
			{
				BX.style(tagNode, 'display', 'inline');
			}

			if (this.selectorInstance.nodes.input) // non-container
			{
				event.preventDefault();
			}
		}
		else
		{
			this.selectorInstance.popups.inviteEmailUser.close();
			return false;
		}
	}
	else
	{
		searchText = this.selectorInstance.nodes.input.value;

		this.runSearch({
			text: searchText
//			sendAjax: option.search.useAjax
		});
	}

	if (
		!this.selectorInstance.isDialogOpen()
		&& !BX.type.isNotEmptyString(searchText)
	)
	{
		this.selectorInstance.openDialog();
	}
	else
	{
		if (
			this.selectorManager.statuses.allowSendEvent
			&& this.selectorInstance.isDialogOpen()
			&& !this.selectorInstance.isContainerOpen
		)
		{
			this.selectorInstance.closeDialog();
		}
	}

	if (event.keyCode == 8)
	{
		this.selectorManager.statuses.allowSendEvent = true;
	}

	return true;
};

BX.UI.Selector.Search.prototype.runSearch = function(params)
{
	if (!params)
	{
		params = {};
	}

	var
		searchOptions = this.getOption('search'),
		text = (BX.type.isNotEmptyString(params.text) ? BX.util.trim(params.text) : ''),
		contentCollection = null;

	if (!BX.type.isNotEmptyObject(searchOptions))
	{
		searchOptions = {};
	}

	var
		sendAjax = (
			BX.type.isNotEmptyString(searchOptions.useAjax)
			&& searchOptions.useAjax == 'Y'
			&& !this.selectorManager.extranetUser
		),
		nameTemplate = this.getOption('userNameTemplate');

	if (BX.type.isBoolean(params.sendAjax))
	{
		sendAjax = params.sendAjax;
	}

	this.selectorInstance.cursors.search = {
		firstItem: null, // obSearchFirstElement
		currentItem: null, // obCurrentElement.search
		position: { // obCursorPosition.search
			group: 0,
			row: 0,
			column: 0
		}
	};

	if (!BX.type.isNotEmptyString(text))
	{
		this.abortSearchRequest();
		if (this.selectorInstance.popups.search != null)
		{
			this.selectorInstance.popups.search.close();
		}
		return false;
	}
	else
	{
		var count = 0;

		var resultGroupIndex = 0;
		var resultRowIndex = 0;
		var resultColumnIndex = 0;
		var bNewGroup = null;
		var storedItem = false;
		var bSkip = false;

		var partsItem = [];
		var bFound = false;
		var bPartFound = false;
		var partsSearchText = null;
		var searchStringAlternativesList = [
			text
		];
		var searchString = null;

		var
			arTmp = [],
			tmpVal = false,
			tmpString = null;

		var key = null;
		var i = null;
		var k = null;

		if (sendAjax) // before AJAX request
		{
			this.abortSearchRequest();

			var obSearch = {
				searchString: text
			};

			if (
				BX.type.isNotEmptyString(searchOptions.useClientDatabase)
				&& searchOptions.useClientDatabase == 'Y'
			)
			{
				BX.onCustomEvent('findEntityByName', [
					this.selectorManager,
					obSearch,
					{ },
					this.selectorInstance.clientDBSearchResult.users
				]); // get result from the clientDb
			}

			if (obSearch.searchString != text) // if text was converted to another charset
			{
				searchStringAlternativesList.push(obSearch.searchString);
			}
			this.selectorInstance.resultChanged.search = false;
			this.selectorInstance.tmpSearchResult.ajax = [];
		}
		else // from AJAX results
		{
			if (
				BX.type.isNotEmptyString(params.textAjax)
				&& params.textAjax != text
			)
			{
				searchStringAlternativesList.push(params.textAjax);
			}

			// syncronize local DB
			if (
				BX.type.isNotEmptyObject(this.selectorInstance.entities.USERS)
				&& !BX.type.isNotEmptyString(this.selectorInstance.getOption('scope', 'USERS'))
				&& this.selectorInstance.getOption('allowSearchNetwork', 'USERS') != 'Y'
			)
			{
				for (key = 0; key < searchStringAlternativesList.length; key++)
				{
					searchString = searchStringAlternativesList[key].toLowerCase();
					if (
						searchString.length > 1
						&& BX.type.isArray(this.selectorInstance.clientDBSearchResult.users[searchString])
						&& this.selectorInstance.clientDBSearchResult.users[searchString].length > 0
					)
					{
						/* sync minus */
						BX.onCustomEvent(BX.UI.SelectorManager, 'syncClientDb', [ {
							selectorInstance: this.selectorInstance,
							clientDBData: this.selectorInstance.clientDBSearchResult.users[searchString], // oDbUserSearchResult
							ajaxData: ( // oAjaxUserSearchResult
								typeof this.selectorInstance.ajaxSearchResult.users[searchString] != 'undefined'
									? this.selectorInstance.ajaxSearchResult.users[searchString]
									: []
							)
						} ]);
					}
				}
			}
		}

		if (sendAjax) // before Ajax search
		{
			this.selectorInstance.tmpSearchResult.client = [];
		}

		var
			entityTypeData = null,
			itemCode = null,
			item = null,
			itemsList = {};

		for (var entityType in this.selectorInstance.entities) // group
		{
			if (!this.selectorInstance.entities.hasOwnProperty(entityType))
			{
				continue;
			}

			entityTypeData = this.selectorInstance.entities[entityType];

			bNewGroup = true;
			arTmp = [];

			itemsList[entityType] = {};

			if (this.selectorInstance.getOption('allowSelect', entityType) == 'N')
			{
				continue;
			}

			var scope = null;

			for (key = 0; key < searchStringAlternativesList.length; key++)
			{
				searchString = searchStringAlternativesList[key].toLowerCase();
				if (
					sendAjax
					&& BX.type.isNotEmptyObject(this.selectorInstance.clientDBSearchResult[entityType.toLowerCase()])
					&& BX.type.isArray(this.selectorInstance.clientDBSearchResult[entityType.toLowerCase()][searchString])
					&& this.selectorInstance.clientDBSearchResult[entityType.toLowerCase()][searchString].length > 0
				)
				{
					for (i = 0; i < this.selectorInstance.clientDBSearchResult[entityType.toLowerCase()][searchString].length; i++)
					{
						// skip current user
						if (
							entityType.toLowerCase() == 'users'
							&& this.selectorInstance.getOption('allowSearchSelf', entityType) == 'N'
							&& BX.type.isNotEmptyObject(entityTypeData.additionalData)
							&& BX.type.isNotEmptyString(entityTypeData.additionalData.PREFIX)
							&& this.selectorInstance.clientDBSearchResult[entityType.toLowerCase()][searchString][i] == entityTypeData.additionalData.PREFIX + BX.message('USER_ID')
						)
						{
							continue;
						}

						if (
							entityType.toLowerCase() == 'users'
							&& BX.type.isNotEmptyObject(this.selectorManager.obClientDbData[entityType.toLowerCase()])
						)
						{
							itemCode = this.selectorInstance.clientDBSearchResult[entityType.toLowerCase()][searchString][i];
							scope = this.selectorInstance.getOption('scope', entityType);

							if (
								BX.type.isNotEmptyObject(this.selectorManager.obClientDbData[entityType.toLowerCase()][itemCode])
								&& (
									!BX.type.isNotEmptyString(scope) // !BX.SocNetLogDestination.obUserSearchArea[name]
									|| (
										scope == 'E'
										&& BX.type.isNotEmptyString(this.selectorManager.obClientDbData[entityType.toLowerCase()][itemCode]['isExtranet'])
										&& this.selectorManager.obClientDbData[entityType.toLowerCase()][itemCode]['isExtranet'] == 'Y'
									)
									|| (
										scope == 'I'
										&& BX.type.isNotEmptyString(this.selectorManager.obClientDbData[entityType.toLowerCase()][itemCode]['isExtranet'])
										&& this.selectorManager.obClientDbData[entityType.toLowerCase()][itemCode]['isExtranet'] != 'Y'
									)
								)
							)
							{
								if (!BX.type.isNotEmptyObject(entityTypeData.items))
								{
									entityTypeData.items = {};
								}
								entityTypeData.items[itemCode] = this.selectorManager.obClientDbData[entityType.toLowerCase()][itemCode];
							}
						}
					}
				}
			}

			tmpString = '';

			for (itemCode in entityTypeData.items)
			{
				if (
					!entityTypeData.items.hasOwnProperty(itemCode)
					|| (
						BX.type.isNotEmptyString(entityTypeData.items[itemCode].searchable)
						&& entityTypeData.items[itemCode].searchable == 'N'
					)
				)
				{
					continue;
				}

				if (this.selectorInstance.itemsSelected[itemCode]) // if already in selected
				{
					continue;
				}

				bFound = (
					this.selectorInstance.getOption('searchById', entityType) == 'Y'
					&& parseInt(searchString) == searchString
					&& entityTypeData.items[itemCode].entityId == searchString
				);

				if (!bFound)
				{
					for (key = 0; key < searchStringAlternativesList.length; key++)
					{
						bFound = false;

						searchString = searchStringAlternativesList[key];
						partsSearchText = searchString.toLowerCase().split(/\s+/);

						if (BX.type.isNotEmptyString(entityTypeData.items[itemCode].index))
						{
							partsItem = entityTypeData.items[itemCode].index.toLowerCase().split(/\s+/);
						}
						else
						{
							partsItem = [];
						}

						if (BX.type.isNotEmptyString(entityTypeData.items[itemCode].name))
						{
							partsItem = partsItem.concat(entityTypeData.items[itemCode].name.toLowerCase().split(/\s+/));
						}

						if (
							entityType.toLowerCase() === "mailContacts"
							&& entityTypeData.items[itemCode].email
						)
						{
							partsItem = partsItem.concat(entityTypeData.items[itemCode].email.toLowerCase().split("@"));
						}

						for (k in partsItem)
						{
							if (partsItem.hasOwnProperty(k))
							{
								partsItem[k] = BX.util.htmlspecialcharsback(partsItem[k]);
								tmpString = partsItem[k].replace(/(["\(\)\xAB\xBB\u201C\u201D])/g, ''); // strip quotes and brackets

								if (tmpString.length != partsItem[k].length)
								{
									partsItem.push(tmpString);
								}
							}
						}

						if (
							typeof entityTypeData.items[itemCode].email != 'undefined'
							&& entityTypeData.items[itemCode].email
							&& entityTypeData.items[itemCode].email.length > 0
						)
						{
							partsItem.push(entityTypeData.items[itemCode].email.toLowerCase());
						}

						if (
							typeof entityTypeData.items[itemCode].login != 'undefined'
							&& entityTypeData.items[itemCode].login.length > 0
							&& partsSearchText.length <= 1
							&& searchString.length > 2
						)
						{
							partsItem.push(entityTypeData.items[itemCode].login.toLowerCase());
						}

						BX.onCustomEvent(window, 'SocNetLogDestinationSearchFillItemParts', [ entityType, entityTypeData.items[itemCode], partsItem ]);

						if (partsSearchText.length <= 1)
						{
							for (k in partsItem)
							{
								if (
									partsItem.hasOwnProperty(k)
									&& searchString.toLowerCase().localeCompare(partsItem[k].substring(0, searchString.length), 'en-US', { sensitivity: 'base' }) === 0
								)
								{
									bFound = true;
									break;
								}
							}
						}
						else
						{
							bFound = true;

							for (var j in partsSearchText)
							{
								if (!partsSearchText.hasOwnProperty(j))
								{
									continue;
								}

								bPartFound = false;
								for (k in partsItem)
								{
									if (
										partsItem.hasOwnProperty(k)
										&& partsSearchText[j].toLowerCase().localeCompare(partsItem[k].substring(0, partsSearchText[j].length), 'en-US', { sensitivity: 'base' }) === 0
									)
									{
										bPartFound = true;
										break;
									}
								}

								if (!bPartFound)
								{
									bFound = false;
									break;
								}
							}

							if (!bFound)
							{
								continue;
							}
						}
						if (bFound)
						{
							break;
						}
					}
				}

				if (!bFound)
				{
					continue;
				}

				if (bNewGroup)
				{
					if (typeof this.selectorInstance.result.search[resultGroupIndex] != 'undefined')
					{
						resultGroupIndex++;
					}
					bNewGroup = false;
				}

				tmpVal = {
					value: itemCode
				};

				if (BX.type.isNotEmptyObject(this.selectorInstance.sortData[itemCode]))
				{
					tmpVal.sort = this.selectorInstance.sortData[itemCode];
				}

				if (entityTypeData.items[itemCode].isNetwork == 'Y')
				{
					tmpVal.isNetwork = true;
				}

				if (sendAjax) // before Ajax search
				{
					this.selectorInstance.tmpSearchResult.client.push(i);
				}

				arTmp.push(tmpVal);
			}

			this.selectorInstance.tmpSearchResult.client.filter(function(el, index, arr) {
				return index == arr.indexOf(el);
			});

			arTmp.sort(function(a, b) {
				if (
					BX.util.in_array(a.value, this.selectorInstance.tmpSearchResult.client)
					&& !BX.util.in_array(b.value, this.selectorInstance.tmpSearchResult.client)
				)
				{
					return -1;
				}
				else if (
					typeof a.isNetwork == 'undefined'
					&& typeof b.isNetwork != 'undefined'
				)
				{
					return -1;
				}
				else if (
					typeof a.isNetwork != 'undefined'
					&& typeof b.isNetwork == 'undefined'
				)
				{
					return 1;
				}
				else if (
					typeof a.sort == 'undefined'
					&& typeof b.sort == 'undefined'
				)
				{
					return 0;
				}
				else if (
					typeof a.sort != 'undefined'
					&& typeof b.sort == 'undefined'
				)
				{
					return -1;
				}
				else if (
					typeof a.sort == 'undefined'
					&& typeof b.sort != 'undefined'
				)
				{
					return 1;
				}
				else
				{
					if (
						typeof a.sort.Y != 'undefined'
						&& typeof b.sort.Y == 'undefined'
					)
					{
						return -1;
					}
					else if (
						typeof a.sort.Y == 'undefined'
						&& typeof b.sort.Y != 'undefined'
					)
					{
						return 1;
					}
					else if (
						typeof a.sort.Y != 'undefined'
						&& typeof b.sort.Y != 'undefined'
					)
					{
						if (parseInt(a.sort.Y) > parseInt(b.sort.Y))
						{
							return -1;
						}
						else if (parseInt(a.sort.Y) < parseInt(b.sort.Y))
						{
							return 1;
						}
						else
						{
							return 0;
						}
					}
					else
					{
						if (parseInt(a.sort.N) > parseInt(b.sort.N))
						{
							return -1;
						}
						else if (parseInt(a.sort.N) < parseInt(b.sort.N))
						{
							return 1;
						}
						else
						{
							return 0;
						}
					}
				}
			}.bind(this));

			var sort = 0;
			for (key = 0; key < arTmp.length; key++)
			{
				itemCode = arTmp[key].value;
				itemsList[entityType][itemCode] = itemCode;
				sort++;

				bSkip = false;
				if (entityTypeData.items[itemCode].id == 'UA')
				{
					bSkip = true;
				}
				else // calculate position
				{
					if (!BX.type.isArray(this.selectorInstance.result.search[resultGroupIndex]))
					{
						this.selectorInstance.result.search[resultGroupIndex] = [];
						resultRowIndex = 0;
						resultColumnIndex = 0;
					}

					if (resultColumnIndex == 2)
					{
						resultRowIndex++;
						resultColumnIndex = 0;
					}

					if (!BX.type.isArray(this.selectorInstance.result.search[resultGroupIndex][resultRowIndex]))
					{
						this.selectorInstance.result.search[resultGroupIndex][resultRowIndex] = [];
						resultColumnIndex = 0;
					}
				}

				item = BX.clone(entityTypeData.items[itemCode]);

				if (bSkip)
				{
					storedItem = item;
				}

				item.type = entityType;

				if (!bSkip)
				{
					if (storedItem) // add stored item / UA
					{
						this.selectorInstance.result.search[resultGroupIndex][resultRowIndex][resultColumnIndex] = storedItem;
						storedItem = false;
						resultColumnIndex++;
					}

					this.selectorInstance.result.search[resultGroupIndex][resultRowIndex][resultColumnIndex] = item;
				}

				if (count <= 0)
				{
					this.selectorInstance.cursors.search.firstItem = item;
					this.selectorInstance.cursors.search.currentItem = item;
				}

				count++;
				resultColumnIndex++;
			}
		}

		if (sendAjax)
		{
			if (this.selectorInstance.popups.search != null)
			{
				if (BX(this.selectorInstance.nodes.searchContent))
				{
					BX.cleanNode(this.selectorInstance.nodes.searchContent);
					contentCollection = this.selectorInstance.buildContentCollection({
						type: 'search',
						items: itemsList
					});
					for (i = 0; i < contentCollection.length; i++)
					{
						this.selectorInstance.nodes.searchContent.appendChild(contentCollection[i]);
					}
				}
			}
			else
			{
				this.selectorInstance.openSearch({
					itemsList: itemsList
				});
			}
		}
		else
		{
			if (count <= 0)
			{
				if (this.selectorInstance.popups.search)
				{
					if (this.selectorInstance.getOption('allowSearchNetwork', 'USERS') != 'Y')
					{
						this.selectorInstance.closeByEmptySearchResult = true;
						this.selectorInstance.popups.search.destroy();
					}
				}
				else if (
					this.getOption('useContainer') == 'Y'
					&& this.selectorInstance.nodes.contentWaiter
				)
				{
					this.selectorInstance.nodes.contentWaiter.innerHTML = BX.message('MAIN_UI_SELECTOR_STUB_EMPTY_LIST');
				}
			}
			else
			{
				if (
					this.selectorInstance.popups.search != null
					&& this.selectorInstance.popups.search.isShown()
				)
				{
					if (BX(this.selectorInstance.nodes.searchContent))
					{
						BX.cleanNode(this.selectorInstance.nodes.searchContent);
						contentCollection = this.selectorInstance.buildContentCollection({
							type: 'search',
							items: itemsList
						});
						for (i = 0; i < contentCollection.length; i++)
						{
							this.selectorInstance.nodes.searchContent.appendChild(contentCollection[i]);
						}
					}
				}
				else
				{
					if (this.selectorInstance.popups.search != null)
					{
						this.selectorInstance.popups.search.destroy();
					}
					this.selectorInstance.openSearch({
						itemsList: itemsList
					});
				}

			}
		}

		if (this.selectorInstance.popups.container)
		{
			this.selectorInstance.popups.container.adjustPosition();
		}
		else if (this.selectorInstance.popups.search)
		{
			this.selectorInstance.popups.search.adjustPosition();
		}

		if (count > 0)
		{
			this.selectorInstance.getNavigationInstance().hoverFirstItem({
				tab: 'search'
			});
		}

		clearTimeout(this.selectorInstance.timeouts.search);

		if (sendAjax && text.toLowerCase() != '')
		{
			this.showSearchWaiter();
			this.searchRequest({
				text: text
			});
		}
	}
};

BX.UI.Selector.Search.prototype.searchRequest = function(params)
{
	var text = params.text;

	if (this.selectorInstance.postponeSearch)
	{
		this.selectorInstance.timeouts.postponeSearch = setTimeout(function() {
			this.searchRequest({
				text: text
			});
		}.bind(this), 100);
		return;
	}

	BX.onCustomEvent('BX.UI.SelectorManager:searchRequest', [ {
		selectorInstance: this.selectorInstance,
		additionalData: this.selectorInstance.getAdditionalEntitiesData(),
		callback: {
			success: this.searchRequestCallbackSuccess.bind(this),
			failure: this.searchRequestCallbackFailure.bind(this),
		},
		searchStringOriginal: text,
		searchString: text.toLowerCase()
	} ]);
};

BX.UI.Selector.Search.prototype.searchRequestCallbackSuccess = function(responseData, requestData)
{
	var
		itemCode = null,
		searchOptions = this.getOption('search'),
		searchString = (BX.type.isNotEmptyString(requestData.searchString) ? BX.util.trim(requestData.searchString) : ''),  // text
		found = false;

	if (!BX.type.isNotEmptyObject(searchOptions))
	{
		searchOptions = {};
	}

	this.hideSearchWaiter();

	if (responseData)
	{
		var searchStringAjax = (
			BX.type.isNotEmptyObject(responseData.ENTITIES)
			&& BX.type.isNotEmptyObject(responseData.ENTITIES.USERS)
			&& BX.type.isNotEmptyString(responseData.ENTITIES.USERS.SEARCH)
				? responseData.ENTITIES.USERS.SEARCH
				: requestData.searchString
		);

		var finderData = BX.clone(responseData);

		// prepare data for indexedDB

		if (
			BX.type.isNotEmptyObject(finderData.ENTITIES.USERS)
			&& BX.type.isNotEmptyObject(finderData.ENTITIES.USERS.ITEMS)
		)
		{
			for (itemCode in finderData.ENTITIES.USERS.ITEMS)
			{
				if (
					finderData.ENTITIES.USERS.ITEMS.hasOwnProperty(itemCode)
					&& (
						(
							BX.type.isNotEmptyString(finderData.ENTITIES.USERS.ITEMS[itemCode].active)
							&& finderData.ENTITIES.USERS.ITEMS[itemCode].active == 'N'
						)
						|| (
							BX.type.isNotEmptyString(finderData.ENTITIES.USERS.ITEMS[itemCode].isNetwork)
							&& finderData.ENTITIES.USERS.ITEMS[itemCode].isNetwork == 'Y'
						)
						|| (
							BX.type.isNotEmptyString(finderData.ENTITIES.USERS.ITEMS[itemCode].isEmail)
							&& finderData.ENTITIES.USERS.ITEMS[itemCode].isEmail == 'Y'
						)
					)
				)
				{
					delete finderData.ENTITIES.USERS.ITEMS[itemCode];
				}
			}

			if (
				BX.type.isNotEmptyString(searchOptions.useClientDatabase)
				&& searchOptions.useClientDatabase == 'Y'
			)
			{
				BX.onCustomEvent(BX.UI.SelectorManager, 'onFinderAjaxSuccess', [ finderData.ENTITIES.USERS.ITEMS, BX.UI.SelectorManager, 'users' ]);
			}
		}

		if (!this.selectorInstance.resultChanged.search)
		{
			if (
				!BX.type.isNotEmptyObject(this.selectorInstance.ajaxSearchResult.users)
				|| !this.selectorInstance.ajaxSearchResult.users[searchStringAjax.toLowerCase()]
			)
			{
				this.selectorInstance.ajaxSearchResult.users = {};
				this.selectorInstance.ajaxSearchResult.users[searchStringAjax.toLowerCase()] = [];
			}

			if (BX.type.isNotEmptyObject(responseData.ENTITIES))
			{
				for (var entityType in responseData.ENTITIES)
				{
					if (!responseData.ENTITIES.hasOwnProperty(entityType))
					{
						continue;
					}

					if (
						BX.type.isNotEmptyObject(responseData.ENTITIES[entityType])
						&& BX.type.isNotEmptyObject(responseData.ENTITIES[entityType].ITEMS)
					)
					{
						for (itemCode in responseData.ENTITIES[entityType].ITEMS)
						{
							if (!responseData.ENTITIES[entityType].ITEMS.hasOwnProperty(itemCode))
							{
								continue;
							}

							found = true;
							break;
						}
					}

					if (found)
					{
						break;
					}
				}

				if (
					BX.type.isNotEmptyObject(responseData.ENTITIES.USERS)
					&& BX.type.isNotEmptyObject(responseData.ENTITIES.USERS.ITEMS)
				)
				{
					for (itemCode in responseData.ENTITIES.USERS.ITEMS)
					{
						if (!responseData.ENTITIES.USERS.ITEMS.hasOwnProperty(itemCode))
						{
							continue;
						}

						this.selectorInstance.ajaxSearchResult.users[searchStringAjax.toLowerCase()].push(itemCode);
						if (
							typeof responseData.ENTITIES.USERS.ITEMS[itemCode].isNetwork != 'undefined'
							&& responseData.ENTITIES.USERS.ITEMS[itemCode].isNetwork == 'Y'
						)
						{
							this.selectorInstance.networkItems[itemCode] =  responseData.ENTITIES.USERS.ITEMS[itemCode];
							this.selectorInstance.tmpSearchResult.ajax.push(itemCode);
						}
						else
						{
							if (
								BX.type.isNotEmptyString(responseData.ENTITIES.USERS.ITEMS[itemCode].isCrmEmail)
								&& responseData.ENTITIES.USERS.ITEMS[itemCode].isCrmEmail == 'Y'
								&& this.selectorInstance.getOption('allowSearchCrmEmailUsers') == 'Y'
								&& BX.type.isNotEmptyObject(this.selectorInstance.entities.CRMEMAILUSERS)
							)
							{
								this.selectorInstance.entities.CRMEMAILUSERS.items[itemCode] = responseData.ENTITIES.USERS.ITEMS[itemCode];
							}
							else if (
								BX.type.isNotEmptyString(responseData.ENTITIES.USERS.ITEMS[itemCode].isEmail)
								&& responseData.ENTITIES.USERS.ITEMS[itemCode].isEmail == 'Y'
								&& BX.type.isNotEmptyObject(this.selectorInstance.entities.EMAILUSERS)
							)
							{
								this.selectorInstance.entities.EMAILUSERS.items[itemCode] = responseData.ENTITIES.USERS.ITEMS[itemCode];
							}
							else
							{
								this.selectorInstance.entities.USERS.items[itemCode] = responseData.ENTITIES.USERS.ITEMS[itemCode];
							}
							this.selectorInstance.tmpSearchResult.ajax.push(itemCode);
						}
					}
				}

				if (
					BX.type.isNotEmptyObject(responseData.ENTITIES.CRMEMAILUSERS)
					&& BX.type.isNotEmptyObject(responseData.ENTITIES.CRMEMAILUSERS.ITEMS)
				)
				{
					for (itemCode in responseData.ENTITIES.CRMEMAILUSERS.ITEMS)
					{
						if (!responseData.ENTITIES.CRMEMAILUSERS.ITEMS.hasOwnProperty(itemCode))
						{
							continue;
						}

						this.selectorInstance.entities.CRMEMAILUSERS.items[itemCode] = responseData.ENTITIES.CRMEMAILUSERS.ITEMS[itemCode];
						this.selectorInstance.tmpSearchResult.ajax.push(itemCode);
					}
				}

				if (
					BX.type.isNotEmptyObject(responseData.ENTITIES.SONETGROUPS)
					&& BX.type.isNotEmptyObject(responseData.ENTITIES.SONETGROUPS.ITEMS)
				)
				{
					for (itemCode in responseData.ENTITIES.SONETGROUPS.ITEMS)
					{
						if (!responseData.ENTITIES.SONETGROUPS.ITEMS.hasOwnProperty(itemCode))
						{
							continue;
						}

						if (!this.selectorInstance.entities.SONETGROUPS.items.hasOwnProperty(itemCode))
						{
							this.selectorInstance.entities.SONETGROUPS.items[itemCode] = responseData.ENTITIES.SONETGROUPS.ITEMS[itemCode];
						}
					}
				}
			}

			var eventResult = {
				found: found,
				itemCodeList: []
			};

			BX.onCustomEvent('BX.UI.Selector:onSearchRequestCallbackSussess', [ {
				selector: this.selectorInstance,
				responseData: responseData,
				eventResult: eventResult
			} ]);

			found = eventResult.found;

			for (var i = 0; i < eventResult.itemCodeList.length; i++)
			{
				this.selectorInstance.tmpSearchResult.ajax.push(eventResult.itemCodeList[i]);
			}

			if (!found)
			{
				BX.onCustomEvent('BX.UI.Selector:onEmptySearchResult', [ {
					selectorId: this.selectorInstance.id,
					searchString: searchString,
					searchStringOriginal: (BX.type.isNotEmptyString(requestData.searchStringOriginal) ? requestData.searchStringOriginal : searchString)
				} ]);
			}

			if (BX.type.isNotEmptyObject(responseData.ENTITIES))
			{
				if (
					BX.type.isNotEmptyObject(responseData.ENTITIES.SONETGROUPS)
					&& BX.type.isNotEmptyObject(responseData.ENTITIES.SONETGROUPS.ITEMS)
				)
				{

					for (itemCode in responseData.ENTITIES.SONETGROUPS.ITEMS)
					{
						if (!responseData.ENTITIES.SONETGROUPS.ITEMS.hasOwnProperty(itemCode))
						{
							continue;
						}

						found = true;
						this.selectorInstance.entities.SONETGROUPS.items[itemCode] = responseData.ENTITIES.SONETGROUPS.ITEMS[itemCode];
						this.selectorInstance.tmpSearchResult.ajax.push(itemCode);
					}
				}

				if (
					BX.type.isNotEmptyObject(responseData.ENTITIES.PROJECTS)
					&& BX.type.isNotEmptyObject(responseData.ENTITIES.PROJECTS.ITEMS)
				)
				{

					for (itemCode in responseData.ENTITIES.PROJECTS.ITEMS)
					{
						if (!responseData.ENTITIES.PROJECTS.ITEMS.hasOwnProperty(itemCode))
						{
							continue;
						}

						found = true;
						this.selectorInstance.entities.PROJECTS.items[itemCode] = responseData.ENTITIES.PROJECTS.ITEMS[itemCode];
						this.selectorInstance.tmpSearchResult.ajax.push(itemCode);
					}
				}

				if (
					BX.type.isNotEmptyObject(responseData.ENTITIES.MAILCONTACTS)
					&& BX.type.isNotEmptyObject(responseData.ENTITIES.MAILCONTACTS.ITEMS)
				)
				{
					for (itemCode in responseData.ENTITIES.MAILCONTACTS.ITEMS)
					{
						if (!responseData.ENTITIES.MAILCONTACTS.ITEMS.hasOwnProperty(itemCode))
						{
							continue;
						}

						found = true;
						this.selectorInstance.entities.MAILCONTACTS.items[itemCode] = responseData.ENTITIES.MAILCONTACTS.ITEMS[itemCode];
						this.selectorInstance.tmpSearchResult.ajax.push(itemCode);
					}
				}
			}

			this.selectorInstance.tmpSearchResult.ajax.filter(function(el, index, arr) {
				return index == arr.indexOf(el);
			});

			this.runSearch({
				text: searchString,
				sendAjax: false,
				textAjax: searchStringAjax
			});
		}

		/*
				if (BX.SocNetLogDestination.obAllowSearchNetworkUsers[name])
				{
					var contentArea = BX.findChildren(BX.SocNetLogDestination.popupSearchWindowContent,
						{
							'className': 'bx-finder-groupbox-content'
						},
						true
					);

					BX.SocNetLogDestination.searchButton = BX.create('span', {
						props : {
							'className' : "bx-finder-box-button"
						},
						text: BX.message('LM_POPUP_SEARCH_NETWORK')
					});

					var foundUsers = BX.findChildren(contentArea[0], {tagName: 'a'}, true);
					if (!foundUsers || foundUsers.length <= 0)
					{
						contentArea[0].innerHTML = '';
					}
					contentArea[0].appendChild(BX.SocNetLogDestination.searchButton);
					BX.bind(BX.SocNetLogDestination.searchButton, 'click', function()
					{
						this.showSearchWaiter();
						BX.SocNetLogDestination.searchNetwork(searchString, name, nameTemplate, finderData, searchStringAjax, ajaxData);
					});
				}
		*/
	}
};

BX.UI.Selector.Search.prototype.searchRequestCallbackFailure = function(data)
{
	this.hideSearchWaiter();
};


BX.UI.Selector.Search.prototype.abortSearchRequest = function()
{
	if (this.selectorInstance.searchXhr)
	{
		this.selectorInstance.searchXhr.abort();
	}

	if (this.selectorInstance.searchRequestId)
	{
		this.selectorInstance.searchRequestId = null;
	}

	if (this.selectorInstance.timeouts.search)
	{
		clearTimeout(this.selectorInstance.timeouts.search);
	}
	if (this.selectorInstance.timeouts.postponeSearch)
	{
		clearTimeout(this.selectorInstance.timeouts.postponeSearch);
	}
	this.hideSearchWaiter();
};

BX.UI.Selector.Search.prototype.buildSearchWaiter = function()
{
	this.selectorInstance.nodes.searchWaiter = BX.create('DIV', {
		props: {
			className: this.selectorInstance.getRenderInstance().class.searchWaiter
		},
		style: {
			height: '0px'
		},
		children: [
			BX.create('IMG', {
				props: {
					className: this.selectorInstance.getRenderInstance().class.searchWaiterBackground
				},
				attrs: {
					src: '/bitrix/js/main/core/images/waiter-white.gif'
				}
			}),
			BX.create('DIV', {
				props: {
					className: this.selectorInstance.getRenderInstance().class.searchWaiterText
				},
				text: BX.message('MAIN_UI_SELECTOR_WAITER_TEXT')
			})
		]
	});

	return this.selectorInstance.nodes.searchWaiter;
};

BX.UI.Selector.Search.prototype.showSearchWaiter = function()
{
	if (
		!this.selectorInstance.statuses.searchWaiterEnabled
		&& this.selectorInstance.nodes.searchContentsContainer
	)
	{
		if (BX.pos(this.selectorInstance.nodes.searchContentsContainer).height > 0)
		{
			this.selectorInstance.statuses.searchWaiterEnabled = true;
			var startHeight = 0;
			var finishHeight = 40;

			this.animateSearchWaiter(startHeight, finishHeight);
		}
	}
};

BX.UI.Selector.Search.prototype.hideSearchWaiter = function()
{
	if (this.selectorInstance.statuses.searchWaiterEnabled)
	{
		this.selectorInstance.statuses.searchWaiterEnabled = false;

		var startHeight = 40;
		var finishHeight = 0;
		this.animateSearchWaiter(startHeight, finishHeight);
	}
};

BX.UI.Selector.Search.prototype.animateSearchWaiter = function(startHeight, finishHeight)
{
	if (this.selectorInstance.nodes.searchWaiter)
	{
		(new BX.fx({
			time: 0.5,
			step: 0.05,
			type: 'linear',
			start: startHeight,
			finish: finishHeight,
			callback: BX.delegate(function(height)
				{
					if (this)
					{
						this.waiterBlock.style.height = height + 'px';
					}
				},
				{
					waiterBlock: this.selectorInstance.nodes.searchWaiter
				}),
			callback_complete: function()
			{
			}
		})).start();
	}
};

BX.UI.Selector.Search.prototype.getOption = function(optionId)
{
	return this.selectorInstance.getOption(optionId);
}

})();
