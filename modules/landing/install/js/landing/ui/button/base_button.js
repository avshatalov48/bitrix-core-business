;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Button");


	var isPlainObject = BX.Landing.Utils.isPlainObject;
	var isString = BX.Landing.Utils.isString;
	var isFunction = BX.Landing.Utils.isFunction;
	var isArray = BX.Landing.Utils.isArray;
	var assign = BX.Landing.Utils.assign;
	var attr = BX.Landing.Utils.attr;
	var setTextContent = BX.Landing.Utils.setTextContent;
	var escapeAttributeValue = BX.Landing.Utils.escapeAttributeValue;
	var append = BX.Landing.Utils.append;
	var addClass = BX.Landing.Utils.addClass;
	var removeClass = BX.Landing.Utils.removeClass;
	var hasClass = BX.Landing.Utils.hasClass;
	var show = BX.Landing.Utils.Show;
	var hide = BX.Landing.Utils.Hide;
	var bind = BX.Landing.Utils.bind;


	/**
	 * Implements base interface for works with button
	 *
	 * @param {string} [id] - Button id
	 * @param {object} [options]
	 * @param {?string} [options.text]
	 * @param {?string} [options.html]
	 * @param {function} [options.onClick]
	 * @param {object} [options.attrs]
	 * @param {?boolean} [options.disabled]
	 * @param {?string|?string[]} [options.className]
	 * @param {?boolean} [options.active = false]
	 * @constructor
	 */
	BX.Landing.UI.Button.BaseButton = function(id, options)
	{
		// Prepare params
		options = isPlainObject(options) ? options : isPlainObject(id) ? id : {};
		options = assign({text: "", html: "", onClick: (function() {}), attrs: {}, disabled: false, className: null}, options);
		id = !!id && isString(id) ? id : BX.Landing.UI.Button.BaseButton.makeId();

		this.id = id;
		this.options = options;
		this.loader = null;

		// Make layout
		this.layout = BX.create("button", {props: {className: "landing-ui-button"}, attrs: {type: "button"}});
		this.text = BX.create("span", {props: {className: "landing-ui-button-text"}});
		append(this.text, this.layout);

		// Set id
		attr(this.layout, "data-id", escapeAttributeValue(this.id));

		// Set content
		if (isString(this.options.html) && !!this.options.html)
		{
			this.text.innerHTML = this.options.html;
		}
		else
		{
			setTextContent(this.text, this.options.text);
		}

		// Set click handler function
		if (isFunction(this.options.onClick))
		{
			this.on("click", this.options.onClick);
		}

		// Set attrs
		if (isPlainObject(this.options.attrs))
		{
			this.setAttributes(this.options.attrs);
		}

		// Set class name
		if (isArray(this.options.className))
		{
			this.options.className.forEach(this.layout.classList.add, this.layout.classList);
		}

		if (isString(this.options.className) && !!this.options.className)
		{
			this.layout.classList.add(this.options.className);
		}

		if (this.options.active)
		{
			this.activate();
		}

		if (this.options.disabled)
		{
			this.disable();
		}
	};


	/**
	 * Makes button id
	 * @return {string}
	 */
	BX.Landing.UI.Button.BaseButton.makeId = function()
	{
		return "landing_ui_button_" + (+new Date());
	};


	BX.Landing.UI.Button.BaseButton.prototype = {
		/**
		 * Sets html
		 * @param {string} html
		 */
		setHtml: function(html)
		{
			if (isString(html))
			{
				this.text.innerHTML = html.trim();
			}
		},


		/**
		 * Setts text
		 * @param {string} text
		 */
		setText: function(text)
		{
			if (isString(text))
			{
				setTextContent(this.text, text);
			}
		},


		/**
		 * Adds event handler
		 * @param {string} event - Event name
		 * @param {function} handler
		 * @param {object} [context]
		 */
		on: function(event, handler, context)
		{
			if (isString(event) && isFunction(handler))
			{
				bind(this.layout, event, BX.proxy(handler, context));
			}
		},


		/**
		 * Sets attributes
		 * @param {object} attrs
		 */
		setAttributes: function(attrs)
		{
			attr(this.layout, attrs);
		},


		/**
		 * Sets attribute
		 * @param {string} key
		 * @param {string|boolean|number|null} value
		 */
		setAttribute: function(key, value)
		{
			attr(this.layout, key, value);
		},


		/**
		 * Disables button
		 */
		disable: function()
		{
			addClass(this.layout, "landing-ui-disabled");
		},


		/**
		 * Enables button
		 */
		enable: function()
		{
			removeClass(this.layout, "landing-ui-disabled");
			this.layout.removeAttribute("disabled");
		},


		/**
		 * Checks that this button is enabled
		 * @return {boolean}
		 */
		isEnabled: function()
		{
			return !hasClass(this.layout, "landing-ui-disable");
		},


		/**
		 * Shows button
		 * @return {Promise}
		 */
		show: function()
		{
			return show(this.layout);
		},


		/**
		 * Hides button
		 * @return {Promise}
		 */
		hide: function()
		{
			return hide(this.layout);
		},


		/**
		 * Sets active state
		 */
		activate: function()
		{
			addClass(this.layout, "landing-ui-active");
		},


		/**
		 * Removes active state
		 */
		deactivate: function()
		{
			removeClass(this.layout, "landing-ui-active");
		},


		/**
		 * Checks that this button is active
		 * @return {boolean}
		 */
		isActive: function()
		{
			return hasClass(this.layout, "landing-ui-active");
		}
	}
})();