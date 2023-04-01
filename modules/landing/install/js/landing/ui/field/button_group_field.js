;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Field");

	var isArray = BX.Landing.Utils.isArray;

	/**
	 * Implements interface for works with button group field in editor
	 *
	 * @extends {BX.Landing.UI.Field.BaseField}
	 *
	 * @param {object} data
	 * @constructor
	 */
	BX.Landing.UI.Field.ButtonGroup = function(data)
	{
		this.items = "items" in data && data.items ? data.items : [];
		BX.Landing.UI.Field.BaseField.apply(this, arguments);
		this.layout.classList.add("landing-ui-field-button-group");
		this.frame = typeof data.frame === "object" ? data.frame : null;
		this.format = typeof data.format === "function" ? data.format : (function() {});
		this.postfix = typeof data.postfix === "string" ? data.postfix : "";
		this.property = typeof data.property === "string" ? data.property : "";
		this.multiple = typeof data.multiple === "boolean" ? data.multiple : false;
		this.changeHandler = typeof data.onChange === "function" ? data.onChange : (function() {});
		this.elements = [];
		this.buttons = new BX.Landing.UI.Collection.ButtonCollection();
		this.value = this.getValue();

		this.onButtonClick = this.onButtonClick.bind(this);

		this.input.innerHTML = "";

		this.items.forEach(function(item) {
			var button = this.createButtonByItem(item);
			this.buttons.add(button);
			this.input.appendChild(button.layout);
		}, this);

		if (this.content)
		{
			this.setValue(this.content, true);
		}

		if (this.frame)
		{
			this.onFrameLoad();
		}
	};


	BX.Landing.UI.Field.ButtonGroup.prototype = {
		constructor: BX.Landing.UI.Field.ButtonGroup,
		__proto__: BX.Landing.UI.Field.BaseField.prototype,
		superClass: BX.Landing.UI.Field.BaseField,

		onFrameLoad: function()
		{
			this.elements = [].slice.call(this.frame.document.querySelectorAll(this.selector));

			if (this.elements.length)
			{
				this.deactivateAll();
				this.items.some(item => {
					if (this.elements[0].classList.contains(item.value))
					{
						this.buttons.getByValue(item.value).activate();
						return !this.multiple;
					}
				});
			}
		},

		createButtonByItem: function (item) {
			return new BX.Landing.UI.Button.BaseButton(
				item.id || item.value,
				{
					html: item.name,
					active: item.active,
					attrs: {
						value: item.value,
						title: item.title ? BX.Landing.Utils.escapeText(item.title) : null,
					},
					onClick: this.onButtonClick,
				}
			);
		},

		onButtonClick: function(event)
		{
			var button = this.buttons.getByNode(event.currentTarget);
			var value = button.layout.value;

			if (this.multiple)
			{
				if (button.isActive())
				{
					button.deactivate();
				}
				else
				{
					button.activate();
				}
			}
			else
			{
				this.deactivateAll();
				button.activate();
			}

			this.onChange(value);
		},


		/**
		 * Handles change event
		 */
		onChange: function(value)
		{
			if (!this.multiple)
			{
				this.changeHandler(value, this.items, this.postfix, this.property);
			}
			else
			{
				this.elements.forEach(function(element) {
					element.classList.toggle(value);
				}, this);

				this.changeHandler();
			}

			this.onValueChangeHandler(this);
		},


		/**
		 * Checks that field is changed
		 * @return {boolean}
		 */
		isChanged: function()
		{
			return this.value !== this.getValue();
		},


		/**
		 * Sets field value value
		 * @param value
		 * @param {boolean} [preventEvent = false]
		 */
		setValue: function(value, preventEvent)
		{
			this.deactivateAll();

			if (this.multiple)
			{
				value = isArray(value) ? value : [value];

				value.forEach(function(val) {
					var button = this.buttons.getByValue(val);

					if (button)
					{
						button.activate();
					}
				}, this);
			}
			else
			{
				var button = this.buttons.getByValue(value);

				if (button)
				{
					button.activate();
				}
			}

			if (!preventEvent)
			{
				this.onChange(value);
			}
		},


		/**
		 * Deactivates all buttons
		 */
		deactivateAll: function()
		{
			this.buttons.forEach(function(button) {
				button.deactivate();
			});
		},


		/**
		 * Gets field value
		 * @return {?string}
		 */
		getValue: function()
		{
			var button = this.buttons.getActive();

			if (button)
			{
				return button.layout.value;
			}

			return null;
		}
	}
})();