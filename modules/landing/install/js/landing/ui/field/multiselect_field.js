;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Field");

	var addClass = BX.Landing.Utils.addClass;
	var hasClass = BX.Landing.Utils.hasClass;
	var removeClass = BX.Landing.Utils.removeClass;
	var isArray = BX.Landing.Utils.isArray;
	var isFunction = BX.Landing.Utils.isFunction;
	var create = BX.Landing.Utils.create;
	var random = BX.Landing.Utils.random;
	var escapeHtml = BX.Landing.Utils.escapeHtml;
	var append = BX.Landing.Utils.append;
	var slice = BX.Landing.Utils.slice;
	var encodeDataValue = BX.Landing.Utils.encodeDataValue;
	var remove = BX.Landing.Utils.remove;
	var data = BX.Landing.Utils.data;
	var bind = BX.Landing.Utils.bind;
	var style = BX.Landing.Utils.style;
	var clone = BX.Landing.Utils.clone;
	var offsetLeft = BX.Landing.Utils.offsetLeft;
	var offsetTop = BX.Landing.Utils.offsetTop;
	var findParent = BX.Landing.Utils.findParent;
	var decodeDataValue = BX.Landing.Utils.decodeDataValue;
	var BaseCollection = BX.Landing.Collection.BaseCollection;
	var Popup = BX.Landing.UI.Tool.Popup;


	function addPlaceholders(items, field, depth)
	{
		items.forEach(function(item) {
			var checkbox = new BX.Landing.UI.Field.Checkbox({
				id: item.value,
				items: [{name: item.name, value: item.value, checked: item.selected}],
				depth: depth,
				compact: true,
				onChange: field.onCheckboxChange
			});

			field.fields.add(checkbox);

			if (checkbox.layout)
			{
				append(checkbox.layout, field.getPopup().contentContainer);
			}

			if (item.selected)
			{
				field.addPlaceholder(item);
			}

			if (isArray(item.items))
			{
				depth += 1;
				addPlaceholders(item.items, field, depth);
				depth -= 1;
			}
		});
	}


	/**
	 * Implements interface for works with multi select field
	 * @extends {BX.Landing.UI.Field.BaseField}
	 * @param options
	 * @constructor
	 */
	BX.Landing.UI.Field.MultiSelect = function(options)
	{
		BX.Landing.UI.Field.BaseField.apply(this, arguments);
		addClass(this.layout, "landing-ui-field-multiselect");

		this.onChangeHandler = isFunction(options.onChange) ? options.onChange : (function() {});
		this.items = isArray(options.items) ? options.items : [];
		this.value = isArray(options.value) ? options.value : null;
		this.content = this.value;
		this.fields = new BaseCollection();

		this.button = new BX.Landing.UI.Button.BaseButton("dropdown_button", {
			text: BX.message("LINK_URL_SUGGESTS_SELECT"),
			className: "landing-ui-button-select-link",
			onClick: this.onButtonClick.bind(this)
		});

		this.grid = create("div", {
			props: {className: "landing-ui-field-multiselect-grid"},
			children: [
				create("div", {
					props: {className: "landing-ui-field-multiselect-grid-left"},
					children: [
						this.input
					]
				}),
				create("div", {
					props: {className: "landing-ui-field-multiselect-grid-right"},
					children: [
						this.button.layout
					]
				})
			]
		});

		append(this.grid, this.layout);

		this.onInputClick = this.onInputClick.bind(this);
		this.onCheckboxChange = this.onCheckboxChange.bind(this);

		bind(this.input, "click", this.onInputClick);
		bind(top.document, "click", this.onDocumentClick.bind(this));

		requestAnimationFrame(function() {
			addPlaceholders(this.items, this, 0);

			if (isArray(this.value))
			{
				this.value = this.value.map(function(value) {
					return decodeDataValue(value);
				});

				this.setValue(this.value, true);
			}
			else
			{
				this.value = this.getValue();
				this.content = this.value;
			}
		}.bind(this));
	};


	BX.Landing.UI.Field.MultiSelect.prototype = {
		constructor: BX.Landing.UI.Field.MultiSelect,
		__proto__: BX.Landing.UI.Field.BaseField.prototype,


		/**
		 * Adds placeholder
		 * @param item
		 */
		addPlaceholder: function(item)
		{
			var placeholder = create("div", {
				props: {className: "landing-ui-field-multiselect-placeholder"},
				attrs: {'data-item': encodeDataValue(item), title: escapeHtml(item.name)},
				children: [
					create("span", {
						props: {className: "landing-ui-field-multiselect-placeholder-text"},
						html: escapeHtml(item.name)
					}),
					create("span", {
						props: {className: "landing-ui-field-multiselect-placeholder-remove"},
						events: {click: this.onPlaceholderRemoveClick.bind(this, item)}
					})
				]
			});

			append(placeholder, this.input);
		},


		/**
		 * Handles item remove event
		 * @param item
		 * @param event
		 * @param [preventEvent]
		 */
		onPlaceholderRemoveClick: function(item, event, preventEvent)
		{
			if (event)
			{
				event.preventDefault();
				event.stopPropagation();
			}

			var placeholder = this.getPlaceholderByItem(item);

			if (placeholder)
			{
				remove(placeholder);
				this.adjustPopupPosition();

				var field = this.fields.get(item.value);

				if (field)
				{
					field.layout.querySelector("input").checked = false;
				}
			}

			if (!preventEvent)
			{
				this.onValueChangeHandler(this);
			}
		},


		onCheckboxChange: function(checkbox)
		{
			var value = checkbox.getValue();

			if (value.length)
			{
				this.addPlaceholder(checkbox.items[0]);
				this.adjustPopupPosition();
			}
			else
			{
				this.onPlaceholderRemoveClick(checkbox.items[0], null, true);
			}

			this.onValueChangeHandler(this);
		},


		/**
		 * Gets placeholder element by item
		 * @param item
		 * @return {?HTMLElement}
		 */
		getPlaceholderByItem: function(item)
		{
			return slice(this.input.children).find(function(element) {
				return data(element, "data-item").value === item.value;
			});
		},


		getPopup: function()
		{
			if (this.popup)
			{
				return this.popup;
			}

			this.popup = new Popup({
				id: (this.selector + "_" + random()),
				bindElement: this.input,
				autoHide: true,
				events: {
					onPopupClose: function()
					{
						removeClass(this.input, "landing-ui-active");

						if (hasClass(this.layout.parentElement.parentElement, "landing-ui-form-style"))
						{
							void style(this.layout.parentElement.parentElement, {
								"z-index": null,
								"position": null
							});
						}
					}.bind(this)
				}
			});

			if (this.popup.popupContainer)
			{
				addClass(this.popup.popupContainer, "landing-ui-field-multiselect-popup");

				var parent = findParent(this.input, {className: "landing-ui-panel-content-body-content"}, document.body);

				if (parent)
				{
					append(this.popup.popupContainer, parent);
				}
			}

			return this.popup;
		},

		showPopup: function()
		{
			this.getPopup().show();
			this.adjustPopupPosition();
			this.setValue(this.getValue(), true);
		},

		adjustPopupPosition: function()
		{
			if (this.popup)
			{
				var offsetParent = findParent(this.input, {className: "landing-ui-panel-content-body-content"});

				var inputTop = offsetTop(this.input, offsetParent);
				var inputLeft = offsetLeft(this.input, offsetParent);
				var inputRect = this.input.getBoundingClientRect();

				var offsetY = 2;

				requestAnimationFrame(function() {
					this.popup.popupContainer.style.top = inputTop + inputRect.height + offsetY + "px";
					this.popup.popupContainer.style.left = inputLeft + "px";
					this.popup.popupContainer.style.width = inputRect.width + "px";
				}.bind(this));
			}
		},

		onInputClick: function(event)
		{
			if (event)
			{
				event.stopPropagation();
			}

			var popup = this.getPopup();

			if (popup.isShown())
			{
				removeClass(this.input, "landing-ui-active");
				return popup.close();
			}

			addClass(this.input, "landing-ui-active");
			return this.showPopup();
		},

		onButtonClick: function(event)
		{
			event.preventDefault();
			event.stopPropagation();

			this.onInputClick();
		},

		onDocumentClick: function()
		{
			this.getPopup().close();
		},

		getValue: function()
		{
			return slice(this.input.children).map(function(element) {
				return data(element, "data-item").value;
			});
		},

		isChanged: function()
		{
			var content = clone(this.content).sort();
			var value = this.getValue().sort();

			return JSON.stringify(content) !== JSON.stringify(value);
		},

		getItemByValue: function(items, value)
		{
			var result = null;

			function findItem(items, value)
			{
				return items.forEach(function(item) {
					// noinspection EqualityComparisonWithCoercionJS
					if (value == item.value)
					{
						result = item;
						return;
					}

					if (item.items)
					{
						findItem(item.items, value);
					}
				}, this)
			}

			findItem(items, value);

			return result;
		},

		setValue: function(value, preventEvent)
		{
			if (isArray(value))
			{
				this.input.innerHTML = "";
				value.forEach(function(itemValue) {
					var item = this.getItemByValue(this.items, itemValue);

					if (item)
					{
						this.addPlaceholder(item);
					}
				}, this);
			}

			if (!preventEvent)
			{
				this.onValueChangeHandler(this);
			}
		}
	};
})();