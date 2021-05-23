;if (!BX.getClass('BX.Bizproc.UserSelector')) (function(BX)
{
	'use strict';
	BX.namespace('BX.Bizproc');

	var selectors = new WeakMap();

	var UserSelector = function(container, config)
	{
		var me = this;

		if (!config)
		{
			var configString = container.getAttribute('data-config');
			config = configString ? BX.parseJSON(configString) : null;
			container.removeAttribute('data-config');
		}

		if (!BX.type.isPlainObject(config))
		{
			config = {};
		}

		this.config = config;
		this.container = container || BX.create('div');
		this.isOnlyDialogMode = config.isOnlyDialogMode || false;
		this.data = null;
		this.dialogId = 'bp-user-selector-' + BX.util.getRandomString(7);
		this.selected = config.selected ? BX.clone(config.selected) : [];
		this.selectOne = !config.multiple;
		this.required = config.required || false;
		this.additionalFields = BX.type.isArray(config.additionalFields) ? config.additionalFields : [];

		this.prepareRoles();

		BX.bind(this.container, 'click', function(e) {
			e.preventDefault();
			me.openDialog();
		});

		if (!this.isOnlyDialogMode)
		{
			this.prepareNodes();
		}

		if (config.value)
		{
			this.selected = this.parseValue(config.value);
		}

		this.addItems(this.selected);
	};

	UserSelector.canUse = function()
	{
		return !!BX.SocNetLogDestination;
	};

	UserSelector.decorateNode = function(container, config)
	{
		var selector = selectors.get(container);
		if (!selector)
		{
			selector = new UserSelector(container, config);
			selectors.set(container, selector);
		}

		return selector;
	};

	/**
	 * @param container
	 * @returns {UserSelector|null}
	 */
	UserSelector.getByNode = function(container)
	{
		return selectors.get(container);
	};

	UserSelector.prototype = {
		prepareNodes: function()
		{
			this.itemsNode = BX.create('span');
			this.inputBoxNode = BX.create('span', {
				attrs: {
					className: 'bizproc-type-control-user-input-box'
				}
			});

			this.inputNode = BX.create('input', {
				props: {
					type: 'text'
				},
				attrs: {
					className: 'bizproc-type-control-user-input',
				}
			});

			this.inputBoxNode.appendChild(this.inputNode);

			this.tagNode = BX.create('a', {
				attrs: {
					className: 'bizproc-type-control-user-link'
				}
			});

			this.container.appendChild(this.itemsNode);
			this.container.appendChild(this.inputBoxNode);
			this.container.appendChild(this.tagNode);

			this.createValueNode(this.config.valueInputName || '');

			BX.bind(this.tagNode, 'focus', function(e) {
				e.preventDefault();
				me.openDialog({bByFocusEvent: true});
			});

			this.tagNode.innerHTML = (
				this.selected.length <= 0
					? BX.message('BIZPROC_JS_USER_SELECTOR_CHOOSE')
					: BX.message('BIZPROC_JS_USER_SELECTOR_EDIT')
			);
		},

		getData: function(next)
		{
			if (UserSelector.ajaxSent)
			{
				return;
			}

			UserSelector.ajaxSent = true;
			BX.ajax({
				method: 'POST',
				dataType: 'json',
				url: '/bitrix/tools/bizproc/user_selector.php',
				data: {
					ajax_action: 'get_destination_data',
					sessid: BX.bitrix_sessid(),
					site: BX.message('SITE_ID')
				},
				onsuccess: function (response)
				{
					UserSelector.data = response.data || {};
					UserSelector.ajaxSent = false;
					this.initDialog(next);
				}.bind(this)
			});
		},
		initDialog: function(next)
		{
			var i, me = this, data = UserSelector.data;

			if (!data)
			{
				me.getData(next);
				return;
			}

			var itemsSelected = {};
			for (i = 0; i < me.selected.length; ++i)
			{
				itemsSelected[me.selected[i].id] = me.selected[i].entityType
			}

			var items = {
				users : data.users || {},
				department : data.department || {},
				departmentRelation : data.departmentRelation || {},
				bpuserroles : this.roles || {}
			};
			var itemsLast =  {
				users: data.last.USERS || {}
			};

			if (!items["departmentRelation"])
			{
				items["departmentRelation"] = BX.SocNetLogDestination.buildDepartmentRelation(items["department"]);
			}

			if (!me.inited)
			{
				me.inited = true;
				if (this.isOnlyDialogMode)
				{
					this.initOnlyDialog(items, itemsLast, itemsSelected, data);
				}
				else
				{
					this.initDialogWithInputs(items, itemsLast, itemsSelected, data);
				}
			}
			next();
		},

		initDialogWithInputs: function(items, itemsLast, itemsSelected, data)
		{
			var me = this;

			var destinationInput = me.inputNode;
			destinationInput.id = me.dialogId + 'input';

			var destinationInputBox = me.inputBoxNode;
			destinationInputBox.id = me.dialogId + 'input-box';

			var tagNode = this.tagNode;
			tagNode.id = this.dialogId + 'tag';

			var itemsNode = me.itemsNode;

			BX.SocNetLogDestination.init({
				name : me.dialogId,
				searchInput : destinationInput,
				extranetUser :  false,
				bindMainPopup : {node: me.container, offsetTop: '5px', offsetLeft: '15px'},
				bindSearchPopup : {node: me.container, offsetTop : '5px', offsetLeft: '15px'},
				departmentSelectDisable: false,
				sendAjaxSearch: true,
				callback : {
					select : function(item, type)
					{
						me.addItem(item, type);
						if (me.selectOne)
						{
							BX.SocNetLogDestination.closeDialog();
						}
					},
					unSelect : function (item, type)
					{
						if (me.selectOne)
						{
							return;
						}
						me.unsetValue(item, type);
						BX.SocNetLogDestination.BXfpUnSelectCallback.call({
							formName: me.dialogId,
							inputContainerName: itemsNode,
							inputName: destinationInput.id,
							tagInputName: tagNode.id,
							tagLink1: BX.message('BIZPROC_JS_USER_SELECTOR_CHOOSE'),
							tagLink2: BX.message('BIZPROC_JS_USER_SELECTOR_EDIT')
						}, item)
					},
					openDialog : BX.delegate(BX.SocNetLogDestination.BXfpOpenDialogCallback, {
						inputBoxName: destinationInputBox.id,
						inputName: destinationInput.id,
						tagInputName: tagNode.id
					}),
					closeDialog : BX.delegate(BX.SocNetLogDestination.BXfpCloseDialogCallback, {
						inputBoxName: destinationInputBox.id,
						inputName: destinationInput.id,
						tagInputName: tagNode.id
					}),
					openSearch : BX.delegate(BX.SocNetLogDestination.BXfpOpenDialogCallback, {
						inputBoxName: destinationInputBox.id,
						inputName: destinationInput.id,
						tagInputName: tagNode.id
					}),
					closeSearch : BX.delegate(BX.SocNetLogDestination.BXfpCloseSearchCallback, {
						inputBoxName: destinationInputBox.id,
						inputName: destinationInput.id,
						tagInputName: tagNode.id
					})
				},
				items : items,
				itemsLast : itemsLast,
				itemsSelected : itemsSelected,
				useClientDatabase: false,
				destSort: data.DEST_SORT || {},
				allowAddUser: false
			});

			if (Object.keys(this.roles).length > 0)
			{
				BX.onCustomEvent(BX.SocNetLogDestination, "onTabsAdd", [me.dialogId, {
					id: 'bpuserrole',
					name: BX.message('BIZPROC_JS_USER_SELECTOR_ROLE_TAB'),
					itemType: 'bpuserroles',
					dialogGroup: {
						groupCode: 'bpuserroles',
						title: BX.message('BIZPROC_JS_USER_SELECTOR_ROLE_TAB')
					}
				}]);
			}

			BX.bind(destinationInput, 'keyup', BX.delegate(BX.SocNetLogDestination.BXfpSearch, {
				formName: me.dialogId,
				inputName: destinationInput.id,
				tagInputName: tagNode.id
			}));
			BX.bind(destinationInput, 'keydown', BX.delegate(BX.SocNetLogDestination.BXfpSearchBefore, {
				formName: me.dialogId,
				inputName: destinationInput.id
			}));

			BX.SocNetLogDestination.BXfpSetLinkName({
				formName: me.dialogId,
				tagInputName: tagNode.id,
				tagLink1: BX.message('BIZPROC_JS_USER_SELECTOR_CHOOSE'),
				tagLink2: BX.message('BIZPROC_JS_USER_SELECTOR_EDIT')
			});
		},

		initOnlyDialog: function(items, itemsLast, itemsSelected, data)
		{
			var me = this;
			BX.SocNetLogDestination.init({
				name : me.dialogId,
				showSearchInput: true,
				extranetUser :  false,
				bindMainPopup : {node: me.container, offsetTop: '5px', offsetLeft: '15px'},
				bindSearchPopup : {node: me.container, offsetTop : '5px', offsetLeft: '15px'},
				departmentSelectDisable: false,
				sendAjaxSearch: true,
				callback : {
					select : function(item, type)
					{
						me.selectInDialog(item, type);
						BX.SocNetLogDestination.closeDialog();
					}
				},
				items : items,
				itemsLast : itemsLast,
				itemsSelected : itemsSelected,
				useClientDatabase: false,
				destSort: data.DEST_SORT || {},
				allowAddUser: false
			});

			if (Object.keys(this.roles).length > 0)
			{
				BX.onCustomEvent(BX.SocNetLogDestination, "onTabsAdd", [me.dialogId, {
					id: 'bpuserrole',
					name: BX.message('BIZPROC_JS_USER_SELECTOR_ROLE_TAB'),
					itemType: 'bpuserroles',
					dialogGroup: {
						groupCode: 'bpuserroles',
						title: BX.message('BIZPROC_JS_USER_SELECTOR_ROLE_TAB')
					}
				}]);
			}
		},
		selectInDialog: function(item, type)
		{
			var value = this.convertItemToValue(item, type);
			if (this.config.callbacks && BX.type.isFunction(this.config.callbacks.select))
			{
				this.config.callbacks.select(value, this);
			}
		},
		addItem: function(item, type)
		{
			var me = this;
			var destinationInput = this.inputNode;
			var tagNode = this.tagNode;
			var items = this.itemsNode;

			if (!type && item.entityType)
			{
				type = item.entityType;
			}

			var addedResult = false;

			if (!BX.findChild(items, { attr : { 'data-id' : item.id }}, false, false))
			{
				if (me.selectOne)
				{
					var toRemove = [];
					for (var i = 0; i < items.childNodes.length; ++i)
					{
						toRemove.push({
							itemId: items.childNodes[i].getAttribute('data-id'),
							itemType: items.childNodes[i].getAttribute('data-type')
						})
					}

					me.initDialog(function() {
						for (var i = 0; i < toRemove.length; ++i)
						{
							BX.SocNetLogDestination.deleteItem(toRemove[i].itemId, toRemove[i].itemType, me.dialogId);
						}
					});

					BX.cleanNode(items);
					me.cleanValue();
				}

				var container = this.createItemNode({
					text: item.name,
					className: type === 'bpuserroles' ? 'bizproc-type-control-user-item-head' : '',
					deleteEvents: {
						click: function(e) {
							if (me.selectOne && me.required)
							{
								me.openDialog();
							}
							else
							{
								me.initDialog(function() {
									BX.SocNetLogDestination.deleteItem(item.id, type, me.dialogId);
									BX.remove(container);
									me.unsetValue(item, type);
								});
							}
							e.preventDefault();
						}
					}
				});

				this.setValue(item, type);

				container.setAttribute('data-id', item.id);
				container.setAttribute('data-type', type);

				items.appendChild(container);

				if (!item.entityType)
				{
					item.entityType = type;
				}

				addedResult = true;
			}
			destinationInput.value = '';
			tagNode.innerHTML = BX.message('BIZPROC_JS_USER_SELECTOR_EDIT');

			return addedResult;
		},
		toggleItem: function(item, type)
		{
			if (!this.addItem(item, type))
			{
				this.unsetValue(item, type);
				var element = BX.findChild(this.itemsNode, { attr : { 'data-id' : item.id }}, false, false);
				if (element)
				{
					BX.remove(element);
				}
			}
		},
		addItems: function(items)
		{
			for(var i = 0; i < items.length; ++i)
			{
				this.addItem(items[i], items[i].entityType)
			}
		},
		openDialog: function(params)
		{
			var me = this;
			if (me.handleOpenDialog && BX.Type.isFunction(me.handleOpenDialog) && me.handleOpenDialog(me) === false)
			{
				return;
			}
			this.initDialog(function()
			{
				BX.SocNetLogDestination.openDialog(me.dialogId, params);
			})
		},
		destroy: function()
		{
			if (this.inited)
			{
				if (BX.SocNetLogDestination.isOpenDialog())
				{
					BX.SocNetLogDestination.closeDialog();
				}
				BX.SocNetLogDestination.closeSearch();
			}
		},
		createItemNode: function(options)
		{
			return BX.create('span', {
				attrs: {
					className: 'bizproc-type-control-user-item ' + options.className
				},
				children: [
					BX.create('span', {
						attrs: {
							className: 'bizproc-type-control-user-name'
						},
						text: BX.util.htmlspecialcharsback(options.text || '')
					}),
					BX.create('span', {
						attrs: {
							className: 'bizproc-type-control-user-delete'
						},
						events: options.deleteEvents
					})
				]
			});
		},
		createValueNode: function(valueInputName)
		{
			this.valueNode = BX.create('input', {
				props: {
					type: 'hidden',
					name: valueInputName
				}
			});

			this.container.appendChild(this.valueNode);
		},
		setValue: function(item, type)
		{
			var id = this.getValueId(item, type);
			var value = this.convertItemToValue(item, type);

			if (this.selectOne)
			{
				this.valueNode.value = value;
			}
			else
			{
				var i, newVal = [], pairs = this.valueNode.value.split(',');
				for (i = 0; i < pairs.length; ++i)
				{
					if (!pairs[i] || pairs[i].indexOf(id) >= 0)
					{
						continue;
					}
					newVal.push(pairs[i]);
				}
				newVal.push(value);
				this.valueNode.value = newVal.join(',');
			}
		},
		convertItemToValue: function(item, type)
		{
			var id = this.getValueId(item, type);
			var value = id;
			var name = BX.util.htmlspecialcharsback(item['name']);

			name = name.replace(/[,\.\-\_\>\<\"\']/g, '');

			if (type === 'users')
			{
				value = [name, id].join(' ');
			}
			else if (type === 'department')
			{
				value = [name, id].join(' ');
			}
			return value;
		},

		unsetValue: function(item, type)
		{
			var id = this.getValueId(item, type);

			if (this.selectOne)
			{
				this.valueNode.value = '';
			}
			else
			{
				var i, newVal = [], pairs = this.valueNode.value.split(',');
				for (i = 0; i < pairs.length; ++i)
				{
					if (!pairs[i] || pairs[i].indexOf(id) >= 0)
					{
						continue;
					}
					newVal.push(pairs[i]);
				}
				this.valueNode.value = newVal.join(',');
			}

			if (this.selected && this.selected.length)
			{
				this.selected = this.selected.filter(function(item)
				{
					return (item.id !== id);
				});
			}
		},
		getValueId: function(item, type)
		{
			var id = item['id'].toString();
			if (type === 'users')
			{
				id = '['+ item.entityId +']';
			}
			else if (type === 'department' || type === 'bpuserroles' && id.indexOf('G') === 0)
			{
				id = '['+ id +']';
			}

			return id;
		},

		cleanValue: function()
		{
			this.valueNode.value = '';
		},
		getValue: function()
		{
			return this.valueNode.value;
		},
		parseValue: function(value)
		{
			value = this.prepareValueString(value);

			var i, name, id, entityId, entityType,
				items = [],
				pair, pairs = value.split(','),
				matches, found;

			for (i = 0; i < pairs.length; ++i)
			{
				pair = BX.util.trim(pairs[i]);

				if (matches = pair.match(/(.*)\[([A-Z]{0,2})(\d+)\]/))
				{
					name =  BX.util.trim(matches[1]);
					entityId = matches[3];
					id = matches[2] + entityId;
					entityType = (matches[2] === '') ? 'users' : 'bpuserroles';

					if (entityType === 'users' && id[0] !== 'U')
					{
						id = 'U' + id;
					}

					if (matches[2] === 'DR')
					{
						entityType = 'department';
					}

					items.push({
						id: id,
						entityId: parseInt(entityId),
						name: name,
						entityType: entityType
					});
				}
				else
				{
					found = false;

					if (this.roles[pair])
					{
						found = true;
						items.push(this.roles[pair]);
					}

					if (!found && this.getGroups().length)
					{
						this.getGroups().forEach(function(group)
						{
							if (pair === group['name'])
							{
								found = true;
								items.push({
									id: group['id'],
									entityId: group['id'],
									name: group['name'],
									entityType: 'bpuserroles'
								});
							}
						});
					}

					if (!found)
					{
						items.push({
							id: pair,
							entityId: pair,
							name: pair,
							entityType: 'bpuserroles'
						});
					}
				}
			}
			return items;
		},
		prepareValueString: function(value)
		{
			value = value.toString();

			if (value.indexOf('{{') >= 0) //if contains simple expressions
			{
				var fields = BX.Bizproc.FieldType.getDocumentFields();
				fields.forEach(function(field)
				{
					if (field['Type'] === 'user')
					{
						value = value.replace(field['Expression'], field['SystemExpression']);
					}
				});
			}

			return value;
		},
		prepareRoles: function()
		{
			var fields = BX.Bizproc.FieldType.getDocumentFields();
			var roles = {};

			if (this.getGroups().length)
			{
				this.getGroups().forEach(function(group)
				{
					roles[group['id']] = {
						id: group['id'],
						entityId: group['id'],
						name: group['name'],
						entityType: 'bpuserroles'
					};
				});
			}

			fields.forEach(function(field)
			{
				if (field['Type'] === 'user')
				{
					roles[field['SystemExpression']] = {
						id: field['SystemExpression'],
						entityId: field['SystemExpression'],
						name: BX.Text.encode(field['Name']),
						entityType: 'bpuserroles'
					};
				}
			});

			this.additionalFields.forEach(function(field)
			{
				field.entityType = 'bpuserroles';
				roles[field['id']] = field;
			});

			this.roles = roles;
		},
		/**
		 * @returns {[]}
		 */
		getGroups: function()
		{
			return this.config.groups ||  BX.Bizproc.FieldType.getDocumentUserGroups();
		}
	};

	BX.Bizproc.UserSelector = UserSelector;
})(window.BX || window.top.BX);