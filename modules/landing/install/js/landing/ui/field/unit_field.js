;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Field");


	/**
	 * Implements unit field interface
	 *
	 * @extends {BX.Landing.UI.Field.Text}
	 *
	 * @param data
	 * @constructor
	 */
	BX.Landing.UI.Field.Unit = function(data)
	{
		BX.Landing.UI.Field.Text.apply(this, arguments);
		this.layout.classList.add("landing-ui-field-unit");
		this.unit = BX.Landing.UI.Field.Unit.createUnit();

		this.onInputHandler = typeof data.onInput === "function" ? data.onInput : (function() {});
		this.input.addEventListener("keydown", this.onInputKeydown.bind(this));
		this.input.addEventListener("input", BX.debounce(this.onInputInput, 200, this));
		this.input.addEventListener("input", this.onInputInputWithoutDebounce.bind(this));
		this.input.value = data.content;
		this.input.placeholder = this.placeholder;
		this.items = data.items;
		this.unit.innerText = typeof data.unit === "string" ? data.unit : "";
		this.input.min = typeof data.min === "number" ? data.min : 0;
		this.input.max = typeof data.max === "number" ? data.max : Infinity;
		this.input.step = typeof data.step === "number" ? data.step : 1;
		this.frame = typeof data.frame === "object" ? data.frame : null;
		this.selector = typeof data.selector === "string" ? data.selector : null;
		this.property = typeof data.property === "string" ? data.property : null;
		this.postfix = typeof data.postfix === "string" ? data.postfix : "";
		this.format = typeof data.format === "function" ? data.format : (function() {});
		this.elements = null;

		if (this.frame)
		{
			this.onFrameLoad();
		}

		this.layout.appendChild(this.unit);
		this.enableTextOnly();
	};


	BX.Landing.UI.Field.Unit.createUnit = function()
	{
		return BX.create("div", {props: {className: "landing-ui-field-unit-unit"}});
	};


	BX.Landing.UI.Field.Unit.prototype = {
		constructor: BX.Landing.UI.Field.Unit,
		__proto__: BX.Landing.UI.Field.Text.prototype,

		onFrameLoad: function()
		{
			this.elements = [].slice.call(this.frame.document.querySelectorAll(this.selector));

			if (this.elements.length)
			{
				var value = parseFloat(BX.style(this.elements[0], this.property));
				value = value === value ? value : 0;
				this.setValue(value);
			}
		},


		/**
		 * Handles input event on field input
		 */
		onInputInput: function()
		{
			this.onInputHandler(this);
		},


		onInputInputWithoutDebounce: function()
		{
			if (!!this.elements)
			{
				this.elements.forEach(function(element) {
					this.format(element, this.getValue(), this.items);
				}, this);
			}
		},


		/**
		 * Handles keydown event on field input
		 * @param {Object} event
		 */
		onInputKeydown: function(event)
		{
			if (event.keyCode === 13)
			{
				event.preventDefault();
			}
		},


		/**
		 * Creates input element
		 * @return {HTMLElement}
		 */
		createInput: function()
		{
			return BX.create("input", {props: {
				className: "landing-ui-field-input",
				type: "number"
			}});
		},


		/**
		 * Sets field value
		 * @param value
		 */
		setValue: function(value)
		{
			this.input.value = value;
			BX.fireEvent(this.input, "input");
		},


		/**
		 * Gets field value
		 * @return {number}
		 */
		getValue: function()
		{
			return this.input.value;
		},


		/**
		 * Enables edit
		 */
		enableEdit: function()
		{
			if (this !== BX.Landing.UI.Field.BaseField.currentField && BX.Landing.UI.Field.BaseField.currentField !== null)
			{
				/** Disable preview active field */
				BX.Landing.UI.Field.BaseField.currentField.disableEdit();
			}

			/** Set this field as active */
			BX.Landing.UI.Field.BaseField.currentField = this;

			/** Adjust input focus */
			this.input.focus();
		},


		/**
		 * Disables edit
		 */
		disableEdit: function()
		{

		}
	};
})();