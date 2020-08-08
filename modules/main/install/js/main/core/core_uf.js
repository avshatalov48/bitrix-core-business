;(function ()
{
	'use strict';

	BX.namespace('BX.Main.UF');

	if (typeof BX.Main.UF.Manager !== 'undefined')
	{
		return;
	}

	var fieldStack = {};

	/**
	 * Dynamic form manager. Initilized as singleton below
	 *
	 * @constructor
	 */

	BX.Main.UF.Manager = function ()
	{
		this.mode = this.mode || '';
		this.ajaxUrl = '/bitrix/tools/uf.php';
	};


	BX.Main.UF.Manager.getEdit = function (param, callback)
	{
		return BX.Main.UF.EditManager.get(param, callback);
	};

	BX.Main.UF.Manager.getView = function (param, callback)
	{
		return BX.Main.UF.ViewManager.get(param, callback);
	};

	BX.Main.UF.Manager.prototype.get = function (param, callback)
	{
		if (!this.mode)
		{
			this.displayError([
				'No mode set. Use BX.UF.EditManager or BX.UF.ViewManager'
			]);

			return;
		}

		return this.query(this.mode, {
			FIELDS: param.FIELDS,
			FORM: param.FORM || '',
			CONTEXT: param.CONTEXT || '',
			MEDIA_TYPE: param.MEDIA_TYPE || ''
		}, callback);
	};

	BX.Main.UF.Manager.prototype.add = function (param, callback)
	{
		if (!this.mode)
		{
			this.displayError([
				'No mode set. Use BX.UF.EditManager or BX.UF.ViewManager'
			]);

			return;
		}

		return this.query(this.mode, {
			action: 'add',
			FIELDS: param.FIELDS,
			FORM: param.FORM || ''
		}, callback);
	};

	BX.Main.UF.Manager.prototype.update = function (param, callback)
	{
		if (!this.mode)
		{
			this.displayError([
				'No mode set. Use BX.UF.EditManager or BX.UF.ViewManager'
			]);

			return;
		}

		return this.query(this.mode, {
			action: 'update',
			FIELDS: param.FIELDS,
			FORM: param.FORM || ''
		}, callback);
	};

	BX.Main.UF.Manager.prototype.delete = function (param, callback)
	{
		if (!this.mode)
		{
			this.displayError([
				'No mode set. Use BX.UF.EditManager or BX.UF.ViewManager'
			]);

			return;
		}

		return this.query(this.mode, {
			action: 'delete',
			FIELDS: param.FIELDS,
			FORM: param.FORM || ''
		}, callback);
	};

	BX.Main.UF.Manager.prototype.query = function (mode, param, callback)
	{
		BX.ajax({
			dataType: 'json',
			url: this.ajaxUrl,
			method: 'POST',
			data: this.prepareQuery(mode, param),
			onsuccess: this.queryCallback(callback)
		});
	};

	BX.Main.UF.Manager.prototype.prepareQuery = function (mode, param)
	{
		var p = param || {};

		p.mode = mode;
		p.lang = BX.message('LANGUAGE_ID') || '';
		p.tpl = BX.message('UF_SITE_TPL') || '';
		p.tpls = BX.message('UF_SITE_TPL_SIGN') || '';
		p.sessid = BX.bitrix_sessid();

		return p;
	};

	BX.Main.UF.Manager.prototype.queryCallback = function (callback)
	{
		var processResult = BX.proxy(this.processResult, this);
		return function (result)
		{
			processResult(result, callback);
		}
	};

	BX.Main.UF.Manager.prototype.processResult = function (result, callback)
	{
		var asset = '';
		if (BX.type.isArray(result.ASSET))
		{
			asset += result.ASSET.join('\n');
		}

		if (!!result.ERROR)
		{
			this.displayError(result.ERROR);
		}

		return BX.html(null, asset).then(function ()
		{
			if (!!callback)
			{
				callback(result.FIELD);
			}
		});
	};

	BX.Main.UF.Manager.prototype.displayError = function (errorList)
	{
		for (var i in errorList)
		{
			if (errorList.hasOwnProperty(i))
			{
				console.error(errorList[i]);
			}
		}
	};

	BX.Main.UF.Manager.prototype.registerField = function (field, fieldDescription, node)
	{
		fieldStack[field] = {
			FIELD: fieldDescription,
			NODE: node
		};
	};

	BX.Main.UF.Manager.prototype.unRegisterField = function (field)
	{
		if (!!fieldStack[field])
		{
			delete fieldStack[field];
		}
	};


	BX.Main.UF.ViewManager = function ()
	{
		BX.Main.UF.ViewManager.superclass.constructor.apply(this, arguments);

		this.mode = 'main.view';
	};
	BX.extend(BX.Main.UF.ViewManager, BX.Main.UF.Manager);

	BX.Main.UF.EditManager = function ()
	{
		BX.Main.UF.EditManager.superclass.constructor.apply(this, arguments);

		this.mode = 'main.edit';
	};
	BX.extend(BX.Main.UF.EditManager, BX.Main.UF.Manager);

	BX.Main.UF.EditManager.prototype.validate = function (fieldList, callback)
	{
		if (fieldList.length > 0)
		{
			var request = [];
			for (var i = 0; i < fieldList.length; i++)
			{
				var value = BX.Main.UF.Factory.getValue(fieldList[i]);
				if (value !== null)
				{
					request.push({
						'ENTITY_ID': fieldStack[fieldList[i]].FIELD.ENTITY_ID,
						'FIELD': fieldStack[fieldList[i]].FIELD.FIELD,
						'ENTITY_VALUE_ID': fieldStack[fieldList[i]].FIELD.ENTITY_VALUE_ID,
						'VALUE': value
					});
				}
			}

			return this.query(this.mode, {
				action: 'validate',
				FIELDS: request
			}, callback);
		}
		else
		{
			this.queryCallback(callback)({'FIELD': []});
		}
	};

	/**
	 * Base type handler class. Will be initialized in Factory.
	 *
	 * @constructor
	 */

	BX.Main.UF.BaseType = function ()
	{
	};

	BX.Main.UF.BaseType.prototype.addRow = function (fieldName, thisButton)
	{
		var element = thisButton.parentNode.getElementsByTagName('span');
		if (element && element.length > 0 && element[0])
		{
			var parentElement = element[0].parentNode; // parent
			var newNode = this.getClone(element[element.length - 1], fieldName);

			if (parentElement === thisButton.parentNode)
			{
				parentElement.insertBefore(newNode, thisButton);
			}
			else
			{
				parentElement.appendChild(newNode);
			}
		}
	};

	/**
	 * @deprecated
	 * @param fieldName
	 * @param thisButton
	 */
	BX.Main.UF.BaseType.prototype.addMobileRow = function (fieldName, thisButton)
	{
		var element = thisButton.parentNode.getElementsByTagName('span');
		if (element && element.length && element[0])
		{
			var parentElement = element[0].parentNode; // parent
			var newNode = this.getClone(element[element.length - 1], fieldName);
			var firstChildren = newNode.firstElementChild;
			var name = firstChildren.getAttribute('name');
			var re = /\[(\d)]/;
			var newName = name.replace(re, function (match, index)
			{
				index = parseInt(index) + 1;
				return '[' + index + ']';
			});
			var newItemId = false;
			var prevItemName = false;
			var userFieldTypeName = null;

			firstChildren.setAttribute('name', newName);

			if (firstChildren.hasChildNodes())
			{
				firstChildren.childNodes.forEach(
					function (item, index, array)
					{
						if (!prevItemName && item.attributes !== undefined && item.tagName === 'INPUT')
						{
							item.setAttribute('name', newName);
							prevItemName = item.getAttribute('id');
							newItemId = prevItemName + '_1';
							userFieldTypeName = item.getAttribute('data-user-field-type-name');
						}

						if (prevItemName && item.attributes !== undefined && item.id !== undefined)
						{
							var id = item.getAttribute('id');

							if (id !== prevItemName)
							{
								item.setAttribute('id', id.replace(prevItemName, newItemId));
							}
							else
							{
								item.setAttribute('id', newItemId);
							}
						}
					}
				);
			}

			if (parentElement === thisButton.parentNode)
			{
				parentElement.insertBefore(newNode, thisButton);
			}
			else
			{
				parentElement.appendChild(newNode);
			}

			if (newItemId)
			{
				BX.onCustomEvent(
					'onAddMobileUfField',
					[newItemId, userFieldTypeName]
				);
			}

		}
	};

	BX.Main.UF.BaseType.prototype.getClone = function (node, fieldName)
	{
		var newNode = node.cloneNode(true);

		var inputList = this.findInput(newNode, fieldName);
		for (var i = 0; i < inputList.length; i++)
		{
			inputList[i].value = '';
		}

		return newNode;
	};

	BX.Main.UF.BaseType.prototype.findInput = function (node, fieldName)
	{
		return BX.findChildren(node, {
			tagName: /INPUT|TEXTAREA|SELECT/i,
			attribute: {
				name: fieldName
			}
		}, true);
	};

	BX.Main.UF.BaseType.prototype.isEmpty = function (field)
	{
		var node = this.getNode(field),
			fieldName = field + (
				fieldStack[field].FIELD.MULTIPLE === 'Y'
					? '[]'
					: ''
			);

		if (!BX.isNodeInDom(node))
		{
			console.error('Node for field ' + field + ' is already removed from DOM');
		}

		var nodeList = this.findInput(node, fieldName);

		if (nodeList.length <= 0)
		{
			console.error('Unable to find field ' + field + ' in the registered node');
		}
		else
		{
			for (var i = 0; i < nodeList.length; i++)
			{
				if (nodeList[i].value !== '')
				{
					return false;
				}
			}
		}

		return true;
	};

	BX.Main.UF.BaseType.prototype.getValue = function (field)
	{
		var node = this.getNode(field),
			fieldName = field + (
				fieldStack[field].FIELD.MULTIPLE === 'Y'
					? '[]'
					: ''
			),
			value = fieldStack[field].FIELD.MULTIPLE === 'Y' ? [] : '';

		if (!BX.isNodeInDom(node))
		{
			console.error('Node for field ' + field + ' is already removed from DOM');
		}
		var nodeList = this.findInput(node, fieldName);

		if (nodeList.length <= 0)
		{
			var nodeChildren = (node.children.length ? node.children[0] : false);
			/**
			 * @todo remove !BX.util.in_array(fieldStack[field].FIELD.USER_TYPE_ID, ['crm', 'employee'])
			 * after deploy new Crm and Employee types
			 */
			if (
				!BX.util.in_array(fieldStack[field].FIELD.USER_TYPE_ID, ['crm', 'employee'])
				&&
				(
					!nodeChildren
					||
					nodeChildren.getAttribute('data-has-input') !== 'no'
				)
			)
			{
				console.error('Unable to find field ' + field + ' in the registered node');
			}
		}
		else
		{
			for (var i = 0; i < nodeList.length; i++)
			{
				if (
					nodeList[i].tagName === 'INPUT'
					&& (nodeList[i].type === 'radio' || nodeList[i].type === 'checkbox')
					&& !nodeList[i].checked
				)
				{
					continue;
				}

				if (fieldStack[field].FIELD.MULTIPLE === 'Y')
				{
					value.push(nodeList[i].value);
				}
				else
				{
					value = nodeList[i].value;
					break;
				}
			}
		}

		return value;
	};

	BX.Main.UF.BaseType.prototype.focus = function (field)
	{
		var node = this.getNode(field),
			fieldName = field + (
				fieldStack[field].FIELD.MULTIPLE === 'Y'
					? '[]'
					: ''
			);

		if (!BX.isNodeInDom(node))
		{
			console.error('Node for field ' + field + ' is already removed from DOM');
		}
		var nodeList = this.findInput(node, fieldName);

		if (nodeList.length > 0)
		{
			BX.focus(nodeList[0]);
		}
	};

	BX.Main.UF.BaseType.prototype.getNode = function (field)
	{
		return fieldStack[field].NODE;
	};

	/**
	 * Integer type handler class. Will be initialized in Factory.
	 *
	 * @constructor
	 */
	BX.Main.UF.TypeBoolean = function ()
	{
	};
	BX.extend(BX.Main.UF.TypeBoolean, BX.Main.UF.BaseType);

	BX.Main.UF.TypeBoolean.USER_TYPE_ID = 'boolean';

	BX.Main.UF.TypeBoolean.prototype.isEmpty = function (field)
	{
		return false;
	};

	/**
	 * Integer type handler class. Will be initialized in Factory.
	 *
	 * @constructor
	 */
	BX.Main.UF.TypeInteger = function ()
	{
	};
	BX.extend(BX.Main.UF.TypeInteger, BX.Main.UF.BaseType);

	BX.Main.UF.TypeInteger.USER_TYPE_ID = 'integer';

	/**
	 * Double type handler class. Will be initialized in Factory.
	 *
	 * @constructor
	 */
	BX.Main.UF.TypeDouble = function ()
	{
	};
	BX.extend(BX.Main.UF.TypeDouble, BX.Main.UF.BaseType);

	BX.Main.UF.TypeDouble.USER_TYPE_ID = 'double';

	/**
	 * String type handler class. Will be initialized in Factory.
	 *
	 * @constructor
	 */
	BX.Main.UF.TypeSting = function ()
	{
	};
	BX.extend(BX.Main.UF.TypeSting, BX.Main.UF.BaseType);

	BX.Main.UF.TypeSting.USER_TYPE_ID = 'string';

	/**
	 * URL type handler class. Will be initialized in Factory.
	 *
	 * @constructor
	 */
	BX.Main.UF.TypeUrl = function ()
	{
	};
	BX.extend(BX.Main.UF.TypeUrl, BX.Main.UF.BaseType);

	BX.Main.UF.TypeUrl.USER_TYPE_ID = 'url';

	/**
	 * Formatted string type handler class. Will be initialized in Factory.
	 *
	 * @constructor
	 */
	BX.Main.UF.TypeStingFormatted = function ()
	{
	};
	BX.extend(BX.Main.UF.TypeStingFormatted, BX.Main.UF.TypeSting);

	BX.Main.UF.TypeStingFormatted.USER_TYPE_ID = 'string_formatted';


	/**
	 * Enumeration type handler class. Will be initialized in Factory.
	 *
	 * @constructor
	 */
	BX.Main.UF.TypeEnumeration = function ()
	{
	};
	BX.extend(BX.Main.UF.TypeEnumeration, BX.Main.UF.BaseType);

	BX.Main.UF.TypeEnumeration.USER_TYPE_ID = 'enumeration';

	BX.Main.UF.TypeEnumeration.prototype.findInput = function (node, fieldName)
	{
		var inputList = BX.Main.UF.TypeEnumeration.superclass.findInput.apply(this, arguments);

		if (inputList.length > 0)
		{
			for (var i = 0; i < inputList.length; i++)
			{
				if (inputList[i].tagName === 'INPUT' && inputList[i].type === 'hidden' && inputList.length > 1)
				{
					delete inputList[i];
					break;
				}
			}
		}

		return BX.util.array_values(inputList);
	};

	BX.Main.UF.TypeEnumeration.prototype.focus = function (field)
	{
		if (fieldStack[field]
			&& fieldStack[field].FIELD.SETTINGS.DISPLAY === 'UI'
			&& BX.type.isElementNode(fieldStack[field].NODE)
		)
		{
			BX.fireEvent(fieldStack[field].NODE, 'focus');
		}
		else
		{
			BX.Main.UF.TypeEnumeration.superclass.focus.apply(this, arguments);
		}
	};

	/**
	 * Date type handler class. Will be initialized in Factory.
	 *
	 * @constructor
	 */
	BX.Main.UF.TypeDate = function ()
	{
	};
	BX.extend(BX.Main.UF.TypeDate, BX.Main.UF.BaseType);

	BX.Main.UF.TypeDate.USER_TYPE_ID = 'date';

	BX.Main.UF.TypeDate.prototype.focus = function (field)
	{
		var fieldName = field + (
			fieldStack[field].FIELD.MULTIPLE === 'Y'
				? '[]'
				: ''
		);
		var inputList = this.findInput(this.getNode(field), fieldName);

		if (inputList.length > 0)
		{
			BX.fireEvent(inputList[0], 'click');
		}

		BX.Main.UF.TypeDate.superclass.focus.apply(this, arguments);
	};

	/**
	 * Datetime type handler class. Will be initialized in Factory.
	 *
	 * @constructor
	 */
	BX.Main.UF.TypeDateTime = function ()
	{
	};
	BX.extend(BX.Main.UF.TypeDateTime, BX.Main.UF.TypeDate);

	BX.Main.UF.TypeDateTime.USER_TYPE_ID = 'datetime';

	/**
	 * Datetime type handler class. Will be initialized in Factory.
	 *
	 * @constructor
	 */
	BX.Main.UF.TypeFile = function ()
	{
	};
	BX.extend(BX.Main.UF.TypeFile, BX.Main.UF.BaseType);

	BX.Main.UF.TypeFile.USER_TYPE_ID = 'file';

	BX.Main.UF.TypeFile.prototype.findInput = function (node, fieldName)
	{
		var inputList = BX.Main.UF.TypeFile.superclass.findInput.apply(this, arguments);

		if (inputList.length <= 0)
		{
			inputList = BX.findChildren(node, {
				tagName: /INPUT/i,
				attribute: {
					type: 'file',
					name: /^bxu_files/
				}
			}, true);
		}

		return inputList;
	};

	BX.Main.UF.TypeFile.prototype.getValue = function (field)
	{
		var
			baseValue = BX.Main.UF.TypeFile.superclass.getValue.apply(this, arguments),
			node = fieldStack[field].NODE,
			deletedNodeList = [],
			i;

		if (fieldStack[field].FIELD.MULTIPLE === 'Y')
		{
			var deletedFieldName = field + '_del[]';

			if (BX.type.isArray(baseValue) && baseValue.length > 0)
			{
				deletedNodeList = BX.Main.UF.TypeFile.superclass.findInput.apply(this, [node, deletedFieldName]);

				for (i = 0; i < deletedNodeList.length; i++)
				{
					var pos = BX.util.array_search(deletedNodeList[i].value, baseValue);
					if (pos >= 0)
					{
						baseValue[pos] = {'old_id': deletedNodeList[i].value, 'del': 'Y', 'tmp_name': ''};
					}
				}
			}

			return BX.util.array_values(baseValue);
		}
		else if (baseValue > 0)
		{
			var deletedFieldName = field + '_del';

			deletedNodeList = BX.Main.UF.TypeFile.superclass.findInput.apply(this, [node, deletedFieldName]);

			for (i = 0; i < deletedNodeList.length; i++)
			{
				if (baseValue == deletedNodeList[i].value)
				{
					baseValue = {'old_id': baseValue, 'del': 'Y', 'tmp_name': ''};
					break;
				}
			}

			return baseValue;
		}
	};


	/**
	 * Type handler Factory singleton. Will be initialized below.
	 *
	 * @constructor
	 */
	BX.Main.UF.Factory = function ()
	{
		this.defaultTypeHandler = BX.Main.UF.BaseType;

		this.typeHandlerList = {};
		this.objectCollection = {};
	};

	BX.Main.UF.Factory.prototype.setTypeHandler = function (type, handlerClass)
	{
		this.typeHandlerList[type] = handlerClass;
		if (typeof this.objectCollection[type] !== 'undefined')
		{
			delete this.objectCollection[type];
		}
	};

	BX.Main.UF.Factory.prototype.get = function (type)
	{
		if (typeof this.objectCollection[type] === 'undefined')
		{
			this.objectCollection[type] = this.getObject(type);
		}

		return this.objectCollection[type];
	};

	BX.Main.UF.Factory.prototype.getObject = function (type)
	{
		return new (this.typeHandlerList[type] || this.defaultTypeHandler);
	};

	BX.Main.UF.Factory.prototype.getFieldObject = function (field)
	{
		if (typeof fieldStack[field] === 'undefined')
		{
			console.error('Field ' + field + 'is not registered. Use BX.Main.UF.Factory.registerField to register');

			return null;
		}

		return this.get(fieldStack[field]['FIELD']['USER_TYPE_ID']);
	};

	BX.Main.UF.Factory.prototype.isEmpty = function (field)
	{
		if (typeof fieldStack[field] === 'undefined')
		{
			console.error('Field ' + field + 'is not registered. Use BX.Main.UF.Factory.registerField to register');

			return true;
		}

		return this.get(fieldStack[field]['FIELD']['USER_TYPE_ID']).isEmpty(field);
	};

	BX.Main.UF.Factory.prototype.getValue = function (field)
	{
		if (typeof fieldStack[field] === 'undefined')
		{
			console.error('Field ' + field + 'is not registered. Use BX.Main.UF.Factory.registerField to register');

			return null;
		}

		return this.get(fieldStack[field]['FIELD']['USER_TYPE_ID']).getValue(field);
	};

	BX.Main.UF.Factory.prototype.focus = function (field)
	{
		if (typeof fieldStack[field] === 'undefined')
		{
			console.error('Field ' + field + 'is not registered. Use BX.Main.UF.Factory.registerField to register');
		}

		return this.get(fieldStack[field]['FIELD']['USER_TYPE_ID']).focus(field);
	};

	/**
	 * Singletons initialization
	 */

	BX.Main.UF.EditManager = new BX.Main.UF.EditManager();
	BX.Main.UF.ViewManager = new BX.Main.UF.ViewManager();
	BX.Main.UF.Factory = new BX.Main.UF.Factory();

	BX.Main.UF.Factory.setTypeHandler(BX.Main.UF.TypeBoolean.USER_TYPE_ID, BX.Main.UF.TypeBoolean);
	BX.Main.UF.Factory.setTypeHandler(BX.Main.UF.TypeInteger.USER_TYPE_ID, BX.Main.UF.TypeInteger);
	BX.Main.UF.Factory.setTypeHandler(BX.Main.UF.TypeDouble.USER_TYPE_ID, BX.Main.UF.TypeDouble);
	BX.Main.UF.Factory.setTypeHandler(BX.Main.UF.TypeSting.USER_TYPE_ID, BX.Main.UF.TypeSting);
	BX.Main.UF.Factory.setTypeHandler(BX.Main.UF.TypeStingFormatted.USER_TYPE_ID, BX.Main.UF.TypeStingFormatted);
	BX.Main.UF.Factory.setTypeHandler(BX.Main.UF.TypeEnumeration.USER_TYPE_ID, BX.Main.UF.TypeEnumeration);
	BX.Main.UF.Factory.setTypeHandler(BX.Main.UF.TypeFile.USER_TYPE_ID, BX.Main.UF.TypeFile);
	BX.Main.UF.Factory.setTypeHandler(BX.Main.UF.TypeDate.USER_TYPE_ID, BX.Main.UF.TypeDate);
	BX.Main.UF.Factory.setTypeHandler(BX.Main.UF.TypeDateTime.USER_TYPE_ID, BX.Main.UF.TypeDateTime);
})();