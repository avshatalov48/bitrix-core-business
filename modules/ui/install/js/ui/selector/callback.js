(function() {

var BX = window.BX;

BX.namespace('BX.UI');

if (!!BX.UI.Selector.Callback)
{
	return;
}

BX.UI.Selector.Callback = function()
{
};

BX.UI.Selector.Callback.select = function(params) // BXfpSelectCallback
{
	var
		fieldName = params.fieldName,
		item = params.item,
		selectorId = params.selectorId,
		entityType = params.entityType,
		undeletable = params.undeletable;

	if (
		!selectorId
		|| !BX.type.isNotEmptyObject(item)
		|| !BX.type.isNotEmptyString(entityType)
	)
	{
		return;
	}

	var selectorInstance = BX.UI.SelectorManager.instances[selectorId];
	if (!BX.type.isNotEmptyObject(selectorInstance))
	{
		return;
	}

	if (
		selectorInstance.nodes.inputItemsContainer
		&& !BX.findChild(selectorInstance.nodes.inputItemsContainer, { attr : { 'data-id' : item.id }}, false, false)
	)
	{
		if (selectorInstance.getOption('multiple') != 'Y')
		{
			BX.cleanNode(selectorInstance.nodes.inputItemsContainer);
			if (selectorInstance.nodes.input)
			{
				selectorInstance.nodes.input.style.display = 'none';
			}
		}
/*
			if (
				typeof window['arExtranetGroupID'] != 'undefined'
				&& BX.util.in_array(params.item.entityId, window['arExtranetGroupID'])
			)
			{
				type1 = 'extranet';
			}
*/
		var itemNode = BX.create("span", {
			attrs : {
				'data-id' : item.id,
				'data-type' : entityType
			},
			props: {
				className: selectorInstance.getRenderInstance().class.itemDestination + ' ' + selectorInstance.getRenderInstance().class.itemDestinationPrefix + entityType.toLowerCase() + ' ' + (undeletable ? ' ' + selectorInstance.getRenderInstance().class.itemDestinationUndeletable : '')
			},
			children: BX.util.array_merge(BX.UI.Selector.Callback.getHiddenInputCollection({
				entityType: entityType,
				itemId: item.id,
				fieldName: selectorInstance.fieldName,
				multiple: (selectorInstance.getOption('multiple') == 'Y')
			}), [
				BX.create('SPAN', {
					props: {
						className: selectorInstance.getRenderInstance().class.itemDestinationText
					},
					html: (item.name + (
						BX.type.isNotEmptyString(item.showEmail)
						&& item.showEmail == 'Y'
						&& BX.type.isNotEmptyString(params.item.email)
							? ' (' + params.item.email + ')'
							: ''
					))
				})
			])
		});

		if(!undeletable)
		{
			itemNode.appendChild(BX.create('SPAN', {
				props : {
					className: selectorInstance.getRenderInstance().class.itemDestinationDeleteButton
				},
				events : {
					click : function(e) {
						selectorInstance.getRenderInstance().deleteItem({
							entityType: entityType,
							itemId: item.id
						});
						e.preventDefault()
					},
					mouseover : function(e) {
						e.currentTarget.parentNode.classList.add(selectorInstance.getRenderInstance().class.itemDestinationHover);
					},
					mouseout : function(e) {
						e.currentTarget.parentNode.classList.remove(selectorInstance.getRenderInstance().class.itemDestinationHover);
					}
				}
			}));
		}

		selectorInstance.nodes.inputItemsContainer.appendChild(itemNode);
	}

	if (selectorInstance.nodes.input)
	{
		selectorInstance.nodes.input.value = '';
	}

	if (selectorInstance.getOption('multiple') == 'Y')
	{
		selectorInstance.setTagTitle();
	}
	else
	{
		if (selectorInstance.nodes.tag)
		{
			BX.style(selectorInstance.nodes.tag, 'display', 'none');
		}
		selectorInstance.closeDialog();
	}

	BX.onCustomEvent('BX.UI.Selector.Callback:select', [ {
		selectorId: selectorInstance.id
	} ]);
};

BX.UI.Selector.Callback.unSelect = function(params) // BXfpUnSelectCallback
{
	var
		item = params.item,
		selectorId = params.selectorId,
		entityType = params.entityType;

	if (
		!selectorId
		|| !BX.type.isNotEmptyObject(item)
		|| !BX.type.isNotEmptyString(entityType)
	)
	{
		return;
	}

	var selectorInstance = BX.UI.SelectorManager.instances[selectorId];
	if (!BX.type.isNotEmptyObject(selectorInstance))
	{
		return;
	}

	delete selectorInstance.itemsSelected[item.id];

	if (selectorInstance.nodes.inputItemsContainer)
	{
		var elements = BX.findChildren(selectorInstance.nodes.inputItemsContainer, { attribute: { 'data-id': '' + item.id + '' } }, true);
		if (elements !== null)
		{
			for (var i = 0; i < elements.length; i++)
			{
				if (!BX.hasClass(elements[i], selectorInstance.getRenderInstance().class.itemDestinationUndeletable))
				{
					BX.remove(elements[i]);
				}
			}
		}
	}

	if (selectorInstance.nodes.input)
	{
		selectorInstance.nodes.input.value = '';
	}

	selectorInstance.setTagTitle();

	if (
		selectorInstance.nodes.tag
		&& (
			selectorInstance.getOption('useContainer') == 'Y'
			|| (
				(
					!selectorInstance.popups.main
					|| !selectorInstance.popups.main.isShown()
				)
				&& (
					!selectorInstance.popups.search
					|| !selectorInstance.popups.search.isShown()
				)
			)
		)
	)
	{
		BX.style(selectorInstance.nodes.tag, 'display', 'inline-block');
	}

	BX.onCustomEvent('BX.UI.Selector.Callback:unSelect', [ {
		selectorId: selectorInstance.id
	} ]);
};

BX.UI.Selector.Callback.openDialog = function(params) // BXfpOpenDialogCallback
{
	var
		selectorId = params.selectorId;

	if (!selectorId)
	{
		return;
	}

	var selectorInstance = BX.UI.SelectorManager.instances[selectorId];
	if (!BX.type.isNotEmptyObject(selectorInstance))
	{
		return;
	}

	if (
		selectorInstance.getOption('multiple') != 'Y'
		&& selectorInstance.nodes.inputBox
	)
	{
		BX.style(selectorInstance.nodes.inputBox, 'display', 'inline-block');
	}

	if (selectorInstance.nodes.tag)
	{
		BX.style(selectorInstance.nodes.tag, 'display', 'none');
	}

	BX.defer(BX.focus)(selectorInstance.nodes.input);
};

BX.UI.Selector.Callback.closeDialog = function(params) // BXfpCloseDialogCallback
{
	var
		selectorId = params.selectorId;

	if (!selectorId)
	{
		return;
	}

	var selectorInstance = BX.UI.SelectorManager.instances[selectorId];
	if (!BX.type.isNotEmptyObject(selectorInstance))
	{
		return;
	}

	if (
		!selectorInstance.isSearchOpen()
		&& selectorInstance.nodes.input
		&& selectorInstance.nodes.input.value.length <= 0
	)
	{
		if (selectorInstance.nodes.inputBox)
		{
			BX.style(selectorInstance.nodes.inputBox, 'display', 'none');
		}

		if (
			selectorInstance.nodes.tag
			&& (
				selectorInstance.getOption('multiple') == 'Y'
				|| Object.keys(selectorInstance.itemsSelected).length <= 0
			)
		)
		{
			BX.style(selectorInstance.nodes.tag, 'display', 'inline-block');
		}

		BX.UI.Selector.Callback.disableBackspace();
	}
};

BX.UI.Selector.Callback.disableBackspace = function() // BXfpDisableBackspace
{
	if (BX.type.isFunction(BX.UI.Selector.Callback.disableBackspaceHandler))
	{
		BX.unbind(window, 'keydown', BX.UI.Selector.Callback.disableBackspaceHandler);
	}

	BX.bind(window, 'keydown', BX.UI.Selector.Callback.disableBackspaceHandler = function(event)
	{
		if (
			event.keyCode == 8
			&& !BX.util.in_array(event.target.tagName.toLowerCase(), ['input', 'textarea'])
		)
		{
			event.stopPropagation();
			event.preventDefault();
			return false;
		}
	});

	setTimeout(function()
	{
		BX.unbind(window, 'keydown', BX.UI.Selector.Callback.disableBackspaceHandler);
		BX.UI.Selector.Callback.disableBackspaceHandler = null;
	}, 5000);
};

BX.UI.Selector.Callback.getHiddenInputCollection = function(params) // getHidden
{
	var
		result = [],
		fieldName = (BX.type.isNotEmptyString(params.fieldName) ? params.fieldName : false),
		entityType = (BX.type.isNotEmptyString(params.entityType) ? params.entityType : false),
		itemId = (BX.type.isNotEmptyString(params.itemId) ? params.itemId : false);

	if (!fieldName)
	{
		return result;
	}

	result.push(BX.create('INPUT', {
		attrs : {
			type : 'hidden',
			name : fieldName + (!!params.multiple ? '[]' : ''),
			value : itemId
		}
	}));

/*
	var value = (
		typeof item.id != 'undefined'
		&& (
			item.id.indexOf("C_") === 0
			|| item.id.indexOf("CO_") === 0
			|| item.id.indexOf("L_") === 0
		)
			? item.desc
			: item.id
	);

	return [
		BX.create("input", {
			attrs : {
				type : 'hidden',
				name : varName + '[' + prefix + '][]',
				value : value
			}
		}),
		(
			prefix == 'UE'
			&& typeof item.params != 'undefined'
			&& typeof item.params.name != 'undefined'
				? BX.create("input", {
					attrs : {
						'type' : 'hidden',
						'name' : 'INVITED_USER_NAME[' + value + ']',
						'value' : item.params.name
					}
				})
				: null
		),
		(
			prefix == 'UE'
			&& typeof item.params != 'undefined'
			&& typeof item.params.lastName != 'undefined'
				? BX.create("input", {
					attrs : {
						'type' : 'hidden',
						'name' : 'INVITED_USER_LAST_NAME[' + value + ']',
						'value' : item.params.lastName
					}
				})
				: null
		),
		(
			prefix == 'UE'
			&& typeof item.id != 'undefined'
			&& (
				item.id.indexOf("C_") === 0
				|| item.id.indexOf("CO_") === 0
				|| item.id.indexOf("L_") === 0
			)
				? BX.create("input", {
					attrs : {
						'type' : 'hidden',
						'name' : 'INVITED_USER_CRM_ENTITY[' + value + ']',
						'value' : item.id
					}
				})
				: null
		),
		(
			prefix == 'UE'
			&& typeof item.params != 'undefined'
			&& typeof item.params.createCrmContact != 'undefined'
			&& !!item.params.createCrmContact
				? BX.create("input", {
					attrs : {
						'type' : 'hidden',
						'name' : 'INVITED_USER_CREATE_CRM_CONTACT[' + value + ']',
						'value' : 'Y'
					}
				})
				: null
		)
	];
*/
	return result;
};

})();
