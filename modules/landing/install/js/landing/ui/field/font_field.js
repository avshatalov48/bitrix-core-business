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

		this.value = data.value;
		this.headlessMode = data.headlessMode === true;
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
		if (this.value)
		{
			this.content = escapeHtml(this.value.family);
			this.input.innerHTML = this.value.family;
		}
		else
		{
			this.content = escapeHtml(this.content);
			this.input.innerHTML = this.content;
		}

		//todo: need refactoring, need use ui.component.link
		this.link = BX.create(
			"a",
			{
				props: {
					className: "landing-ui-field-font-link",
					text: BX.Landing.Loc.getMessage("LANDING_EDIT_BLOCK_DEFAULT_FONT")
				}
			}
		);

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

			var element = this.frame.document.querySelectorAll(this.selector)[0];
			var classList = element.classList.value;
			var regClassFont = /g-font-(?:(?!size|weight)\w)[a-z-]*/i;
			var regCurrentFont = /[a-z\s]*[a-z]/i;
			var foundedClass = classList.match(regClassFont);
			var classFontName;
			if (foundedClass)
			{
				classFontName = foundedClass[0];
			}
			if (element)
			{
				var currentFont = BX.style(element, "font-family");
				currentFont = currentFont.match(regCurrentFont);
				if (classFontName)
				{
					element.classList.remove(classFontName);
				}
				var fontName = BX.style(element, "font-family");
				var defaultFont = fontName.match(regCurrentFont);
				if (classFontName)
				{
					element.classList.add(classFontName);
				}
			}
			if (defaultFont[0] !== currentFont[0])
			{
				this.layout.append(this.link);
				this.defaultFont = fontName;
			}
		}

		bind(this.input, "click", proxy(this.onInputClick, this));
		bind(this.link, "click", proxy(this.onLinkClick, this));
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

		/**
		 * Handles link click event
		 * @param {MouseEvent} event
		 */
		onLinkClick: function(event)
		{
			event.preventDefault();
			event.stopPropagation();

			var element = this.frame.document.querySelectorAll(this.selector)[0];
			var classList = element.classList.value;
			var regClassFont = /g-font-(?:(?!size|weight)\w)[a-z-]*/i;
			var foundedClass = classList.match(regClassFont);

			if (element)
			{
				if (foundedClass[0])
				{
					element.classList.remove(foundedClass[0]);
				}
				var family = this.defaultFont;
				if (family)
				{
					family = family.replace(/['|"]/g, "");
					this.content = family.split(",")[0];
				}
			}
			var font = {
				family: this.content
			};
			this.setValue(font);
		},

		setValue: function(value)
		{
			if (isPlainObject(value))
			{
				var className = makeFontClassName(value.family);
				var weightList = [300, 400, 500, 600, 700, 900];
				var weightPrefix = ':wght@';
				var weightParams = weightPrefix + weightList.join(';');
				var family = value.family.replace(/ /g, "+");
				var familyParams = family + weightParams;
				var href = BX.Landing.UI.Panel.GoogleFonts.getInstance().client.makeUrl({
					family: familyParams
				});

				if (this.headlessMode)
				{
					this.value = {
						family: value.family,
						public: 'https://fonts.google.com/specimen/' + family,
						uri: href,
					};
					this.input.innerHTML = escapeHtml(value.family);
					this.emit('onChange');
				}
				else
				{
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
		},

		getValue: function()
		{
			return this.value;
		}
	}
})();