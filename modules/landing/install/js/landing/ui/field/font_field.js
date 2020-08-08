;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Field");

	var isFunction = BX.Landing.Utils.isFunction;
	var isPlainObject = BX.Landing.Utils.isPlainObject;
	var bind = BX.Landing.Utils.bind;
	var proxy = BX.Landing.Utils.proxy;
	var escapeHtml = BX.Landing.Utils.escapeHtml;
	var addClass = BX.Landing.Utils.addClass;
	var clone = BX.Landing.Utils.clone;

	/**
	 * Implements interface for works with text field
	 *
	 * @extends {BX.Landing.UI.Field.BaseField}
	 *
	 * @inheritDoc
	 * @constructor
	 */
	BX.Landing.UI.Field.Font = function(data)
	{
		BX.Landing.UI.Field.BaseField.apply(this, arguments);
		addClass(this.layout, "landing-ui-field-font");

		this.frame = data.frame;
		this.items = [];

		if (isPlainObject(data.items))
		{
			var keys = Object.keys(this.items);
			this.items = keys.map(function(key) {
				return {name: this.items[key], value: key};
			}, this);
		}

		// Make event external event handler
		this.onChangeHandler = isFunction(data.onChange) ? data.onChange : (function() {});
		this.onValueChangeHandler = isFunction(data.onValueChange) ? data.onValueChange : (function() {});

		// Set display field value
		this.content = escapeHtml(this.content);
		this.input.innerHTML = this.content;

		if (this.frame)
		{
			var element = this.frame.document.querySelectorAll(this.selector)[0];

			if (element)
			{
				var family = BX.style(element, "font-family");

				if (family)
				{
					family = family.replace(/['|"]/g, "");
					this.content = family.split(",")[0];
					this.input.innerHTML = this.content;
				}
			}
		}

		bind(this.input, "click", proxy(this.onInputClick, this));
	};


	function makeFontClassName(family)
	{
		return "g-font-" + family.toLowerCase().replace(/ /g, "-");
	}


	BX.Landing.UI.Field.Font.prototype = {
		constructor: BX.Landing.UI.Field.Font,
		__proto__: BX.Landing.UI.Field.BaseField.prototype,

		/**
		 * Handles input click event
		 * @param {MouseEvent} event
		 */
		onInputClick: function(event)
		{
			event.preventDefault();
			event.stopPropagation();

			BX.Landing.UI.Panel.GoogleFonts.getInstance().show().then(function(font) {
				if (!this.response)
				{
					this.response = clone(BX.Landing.UI.Panel.GoogleFonts.getInstance().response);
					this.response.forEach(function(fontItem) {
						this.items.push({name: fontItem.family, value: makeFontClassName(fontItem.family)});
					}, this);
				}

				this.setValue(font);
			}.bind(this));
		},

		setValue: function(value)
		{
			if (isPlainObject(value))
			{
				var className = makeFontClassName(value.family);
				var href = BX.Landing.UI.Panel.GoogleFonts.getInstance().client.makeUrl({
					family: value.family.replace(/ /g, "+")
				});
				var FontManager = BX.Landing.UI.Tool.FontManager.getInstance();

				// Add font to current document
				FontManager.addFont({
					className: className,
					family: value.family,
					href: href,
					category: value.category
				}, window);

				// Update display field value
				this.input.innerHTML = escapeHtml(value.family);

				// Call handlers
				this.onChangeHandler(className, this.items, this.postfix, this.property);
				this.onValueChangeHandler(this);

				// Remove unused handlers
				FontManager.removeUnusedFonts();

				var headString = "";
				FontManager.getUsedLoadedFonts().forEach(function(item) {
					if (item.element)
					{
						item.element.setAttribute("rel", "stylesheet");
						item.element.removeAttribute("media");
						headString += "<noscript>"+item.element.outerHTML+"</noscript>\n";

						item.element.setAttribute("rel", "preload");
						item.element.setAttribute("onload", "this.removeAttribute('onload');this.rel='stylesheet'");
						item.element.setAttribute("as", "style");
						headString += item.element.outerHTML + "\n";
					}

					if (item.CSSDeclaration)
					{
						headString += item.CSSDeclaration.outerHTML;
					}
				});

				headString = headString
					.replace("async@load", "all")
					.replace(/data-loadcss="true"/g, "");

				BX.Landing.Backend.getInstance()
					.action("Landing::updateHead", {content: headString});
			}
		}
	}
})();