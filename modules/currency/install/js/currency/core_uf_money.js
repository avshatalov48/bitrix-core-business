;(function(){
	'use strict';

	BX.namespace('BX.Main.UF');

	if(typeof BX.Main.UF.TypeMoney !== 'undefined')
	{
		return;
	}

	/**
	 * Money type handler class. Will be initialized in Factory.
	 *
	 * @constructor
	 */
	BX.Main.UF.TypeMoney = function()
	{
	};
	BX.extend(BX.Main.UF.TypeMoney, BX.Main.UF.BaseType);

	BX.Main.UF.TypeMoney.USER_TYPE_ID = 'money';

	BX.Main.UF.TypeMoney.prototype.addRow = function(fieldName, thisButton)
	{
		var node = thisButton.previousSibling;
		var newNode = this.getClone(node, fieldName);

		node.parentNode.insertBefore(newNode, thisButton);
	};

	BX.Main.UF.TypeMoney.prototype.getClone = function(node, fieldName)
	{
		var newNode = BX.Main.UF.TypeMoney.superclass.getClone.apply(this, arguments);

		var nodeList = BX.findChildrenByClassName(newNode, 'money-editor-currency-selector-wrap', true);
		var wrapNode = nodeList[0];
		BX.cleanNode(wrapNode);

		var inputList = BX.findChildren(newNode, {
			tagName: /INPUT|SELECT/i
		}, true);

		inputList[0].value = '';
		inputList[1].value = '';

		var currencyList = BX.message('CURRENCY');
		var currencyItems = [];
		var defaultValue = null;

		for(var currency in currencyList)
		{
			if(currencyList.hasOwnProperty(currency))
			{
				var item = {NAME: currencyList[currency].NAME, VALUE: currency};
				currencyItems.push(item);

				if(defaultValue === null)
				{
					defaultValue = item;
				}
			}
		}

		var inputHandler = new BX.Currency.MoneyInput({
			controlId: controlId,
			input: inputList[1],
			resultInput: inputList[0],
			currency: defaultValue.VALUE
		});

		var controlId = Math.random();

		wrapNode.appendChild(BX.decl({
			block: 'main-ui-select',
			name: controlId,
			items: currencyItems,
			value: defaultValue,
			params: {
				fieldName: controlId, isMulti: false
			},
			valueDelete: false
		}));

		BX.addCustomEvent(window, 'UI::Select::change', function(controlObject, value)
		{
			if(controlObject.params.fieldName === controlId)
			{
				var currentValue = JSON.parse(controlObject.node.getAttribute('data-value'));
				if(!!currentValue)
				{
					inputHandler.setCurrency(currentValue.VALUE);
				}
			}
		});

		return newNode;
	};

	BX.Main.UF.TypeMoney.prototype.focus = function(field)
	{
		var node = this.getNode(field);

		if(!BX.isNodeInDom(node))
		{
			console.error('Node for field ' + field + ' is already removed from DOM');
		}

		var input = BX.findChild(node, {
			tagName: 'INPUT',
			attribute: {
				type: 'text'
			}
		}, true);

		if(input)
		{
			BX.focus(input);
		}
	};

	BX.Main.UF.Factory.setTypeHandler(BX.Main.UF.TypeMoney.USER_TYPE_ID, BX.Main.UF.TypeMoney);
})();