;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Field");

	var escapeHtml = BX.Landing.Utils.escapeHtml;
	var isString = BX.Landing.Utils.isString;
	var random = BX.Landing.Utils.random;
	var clone = BX.Landing.Utils.clone;
	var fireCustomEvent = BX.Landing.Utils.fireCustomEvent;

	/**
	 * Implements base interface for works with fields
	 *
	 * @param {{
	 * 		selector: string,
	 * 		[content]: *,
	 * 		[title]: ?string,
	 * 		[placeholder]: string,
	 * 		[description]: string,
	 * 		[className]: string,
	 * 		[property]: string,
	 * 		[attribute]: string,
	 * 		[style]: string,
	 * 		[onValueChange]: function,
	 * 		[hidden]: boolean
	 * 	}} data
	 *
	 * @constructor
	 */
	BX.Landing.UI.Field.BaseField = function(data)
	{
		this.data = data;
		this.id = "id" in data ? data.id : random();
		this.selector = isString(data.selector) ? data.selector : random();
		this.content = "content" in data ? data.content : "";
		this.title = isString(data.title) ? data.title : "";
		this.placeholder = isString(data.placeholder) ? data.placeholder : "";
		this.className = isString(data.className) ? data.className : "";
		this.descriptionText = isString(data.description) ? data.description : "";
		this.attribute = isString(data.attribute) ? data.attribute : "";
		this.hidden = data.hidden ? data.hidden : false;
		this.description = null;
		this.property = isString(data.property) ? data.property : "";
		this.style = "style" in data ? data.style : "";
		this.layout = BX.Landing.UI.Field.BaseField.createLayout();
		this.header = BX.Landing.UI.Field.BaseField.createHeader();
		this.header.innerHTML = escapeHtml(this.title);
		this.layout.appendChild(this.header);
		this.input = this.createInput();
		this.layout.appendChild(this.input);
		this.layout.dataset.selector = this.selector;
		this.input.dataset.placeholder = this.placeholder;
		this.onValueChangeHandler = data.onValueChange ? data.onValueChange : (function() {});

		if (this.className)
		{
			this.layout.classList.add(this.className);
		}

		if (this.descriptionText)
		{
			this.description = BX.Landing.UI.Field.BaseField.createDescription(this.descriptionText);
			this.layout.appendChild(this.description);
		}

		this.input.addEventListener("paste", this.onPaste.bind(this));

		this.init();
	};


	/**
	 * Creates field layout
	 * @return {HTMLElement}
	 */
	BX.Landing.UI.Field.BaseField.createLayout = function()
	{
		return BX.create("div", {props: {className: "landing-ui-field"}});
	};


	/**
	 * Creates field title
	 * @return {HTMLElement}
	 */
	BX.Landing.UI.Field.BaseField.createHeader = function()
	{
		return BX.create("div", {props: {className: "landing-ui-field-header"}});
	};


	/**
	 * Creates field description
	 * @return {HTMLElement}
	 */
	BX.Landing.UI.Field.BaseField.createDescription = function(text)
	{
		return BX.create("div", {
			props: {className: "landing-ui-field-description"},
			html: "<span class=\"fa fa-info-circle\"></span>" + " " + text
		});
	};


	/**
	 * Stores current field instance
	 * @type {?BX.Landing.UI.Field.BaseField}
	 */
	BX.Landing.UI.Field.BaseField.currentField = null;


	BX.Landing.UI.Field.BaseField.prototype = {
		init: function()
		{

		},


		/**
		 * Handles paste event
		 * @param event
		 */
		onPaste: function(event)
		{
			event.preventDefault();

			var text;

			// Prevents XSS and prevents insert potential dangerously code
			if (event.clipboardData && event.clipboardData.getData)
			{
				text = event.clipboardData.getData("text/plain");

				if (!this.textOnly)
				{
					text = text.replace(new RegExp('\n', 'g'), '<br>');
				}

				document.execCommand("insertHTML", false, text);
			}
			else
			{
				// ie11
				text = window.clipboardData.getData("text");

				if (!this.textOnly)
				{
					text = text.replace(new RegExp('\n', 'g'), '<br>');
				}

				document.execCommand("paste", true, text);
			}
		},


		/**
		 * Creates field input
		 * @return {HTMLElement}
		 */
		createInput: function()
		{
			return BX.create("div", {props: {className: "landing-ui-field-input"}, html: this.content});
		},


		/**
		 * Gets field node
		 * @return {HTMLElement}
		 */
		getNode: function()
		{
			return this.layout;
		},


		/**
		 * Checks that this field is changed
		 * @return {boolean}
		 */
		isChanged: function()
		{
			return (this.content === 0 ? 0 : (!!this.content ? this.content.trim ? this.content.trim() : this.content : "")) !== this.getValue();
		},


		/**
		 * Gets field content
		 * @return {*}
		 */
		getValue: function()
		{
			return this.input.innerHTML.trim();
		},


		/**
		 * Sets value
		 * @param {*} value
		 */
		setValue: function(value)
		{
			value = value || "";
			value = this.textOnly ? escapeHtml(value) : value;
			this.input.innerHTML = value.toString().trim();
			this.onValueChangeHandler(this);
			fireCustomEvent(this, "BX.Landing.UI.Field:change", [this.getValue()]);
		},

		enable: function()
		{
			this.layout.disabled = false;
			this.layout.classList.remove("landing-ui-disable");
		},

		disable: function()
		{
			this.layout.disabled = true;
			this.layout.classList.add("landing-ui-disable");
		},

		reset: function()
		{

		},

		clone: function(data)
		{
			return new this.constructor(clone(data || this.data));
		}
	}
})();