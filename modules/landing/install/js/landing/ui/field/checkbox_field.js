;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Field");

	var addClass = BX.Landing.Utils.addClass;
	var isArray = BX.Landing.Utils.isArray;
	var isPlainObject = BX.Landing.Utils.isPlainObject;
	var isFunction = BX.Landing.Utils.isFunction;
	var create = BX.Landing.Utils.create;
	var random = BX.Landing.Utils.random;
	var escapeHtml = BX.Landing.Utils.escapeHtml;
	var append = BX.Landing.Utils.append;
	var slice = BX.Landing.Utils.slice;
	var encodeDataValue = BX.Landing.Utils.encodeDataValue;
	var decodeDataValue = BX.Landing.Utils.decodeDataValue;
	var isNumber = BX.Landing.Utils.isNumber;
	var isBoolean = BX.Landing.Utils.isBoolean;
	var data = BX.Landing.Utils.data;
	var clone = BX.Landing.Utils.clone;


	/**
	 * Implements interface for works with checkboxes list field
	 * @extends {BX.Landing.UI.Field.BaseField}
	 * @param options
	 * @constructor
	 */
	BX.Landing.UI.Field.Checkbox = function(options)
	{
		BX.Landing.UI.Field.BaseField.apply(this, arguments);
		addClass(this.layout, "landing-ui-field-checkbox");

		this.onChangeHandler = isFunction(options.onChange) ? options.onChange : (function() {});
		this.items = isArray(options.items) ? options.items : [];
		this.value = isArray(options.value) ? options.value : null;
		this.depth = isNumber(options.depth) ? options.depth : 0;
		this.compact = isBoolean(options.compact) ? options.compact : false;

		data(this.layout, "data-depth", this.depth);
		data(this.layout, "data-compact", this.compact);

		if (isArray(this.value))
		{
			this.value = this.value.map(function(value) {
				return decodeDataValue(value);
			})
		}

		if (!isArray(this.value))
		{
			this.value = this.items
				.filter(function(item) {
					return item.checked;
				})
				.map(function(item) {
					return decodeDataValue(item.value);
				});
		}

		this.content = this.value;

		this.items.forEach(this.addItem, this);
	};


	BX.Landing.UI.Field.Checkbox.prototype = {
		constructor: BX.Landing.UI.Field.Checkbox,
		__proto__: BX.Landing.UI.Field.BaseField.prototype,


		/**
		 * Adds item
		 * @param itemOptions
		 */
		addItem: function(itemOptions)
		{
			if (isPlainObject(itemOptions))
			{
				var itemId = ("checkbox_item_" + random());
				var item = create("div", {
					props: {className: "landing-ui-field-checkbox-item"},
					children: [
						create("input", {
							props: {className: "landing-ui-field-checkbox-item-checkbox"},
							attrs: {
								id: itemId,
								type: "checkbox",
								value: encodeDataValue(itemOptions.value),
								checked: this.value.find(function(itemVal) {
									// noinspection EqualityComparisonWithCoercionJS
									return itemVal == itemOptions.value;
								}) !== undefined
							},
							events: {change: this.onItemChange.bind(this)}
						}),
						create("label", {
							props: {className: "landing-ui-field-checkbox-item-label"},
							attrs: {"for": itemId},
							html: escapeHtml(itemOptions.name)
						})
					]
				});

				append(item, this.input);
			}

			return item;
		},


		/**
		 * Handles item change
		 */
		onItemChange: function()
		{
			this.onChangeHandler(this);
			this.onValueChangeHandler(this);
		},


		isChanged: function()
		{
			var content = clone(this.content).sort();
			var value = this.getValue().sort();

			return JSON.stringify(content) !== JSON.stringify(value);
		},


		setValue: function(value)
		{
			if (isArray(value))
			{
				slice(this.input.children).forEach(function(element) {
					element.querySelector("input").checked = false;
				});

				value.forEach(function(currentValue) {
					var element = slice(this.input.children).forEach(function(element) {
						// noinspection EqualityComparisonWithCoercionJS
						return element.querySelector("input").value == currentValue;
					}, this);

					if (element)
					{
						element.querySelector("input").checked = true;
					}
				}, this);
			}
		},


		/**
		 * Gets field value
		 * @return {Array}
		 */
		getValue: function()
		{
			return slice(this.input.children)
				.filter(function(element) {
					return element.querySelector("input").checked;
				})
				.map(function(element) {
					return decodeDataValue(element.querySelector("input").value);
				})
		}
	};
})();