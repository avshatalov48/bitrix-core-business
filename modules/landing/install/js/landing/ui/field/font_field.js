;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Field");
	BX.Runtime.loadExtension('landing.ui.component.link');

	var isFunction = BX.Landing.Utils.isFunction;
	var isPlainObject = BX.Landing.Utils.isPlainObject;
	var bind = BX.Landing.Utils.bind;
	var proxy = BX.Landing.Utils.proxy;
	var escapeHtml = BX.Landing.Utils.escapeHtml;
	var addClass = BX.Landing.Utils.addClass;
	var clone = BX.Landing.Utils.clone;

	var REG_CLASS_FONT = /g-font-(?!size-|weight-)([a-z0-9-]+)/ig;
	var REG_NAME_FONT = /[a-z0-9 ]*[a-z0-9]/i;
	var REG_SYNTAX = /['|"]/g;
	var REG_SPACE = / /g;

	var HEADER_TAGS = ['H1', 'H2', 'H3', 'H4', 'H5', 'H6', 'H7'];

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
		this.defaultFontFamily = null;
		this.defaultFontLink = new BX.Landing.UI.Component.Link({
			text: BX.Landing.Loc.getMessage("LANDING_EDIT_BLOCK_DEFAULT_FONT"),
		});

		addClass(this.defaultFontLink.getLayout(), "landing-ui-field-font-link");

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

		if (this.frame)
		{
			if (data.styleNode.currentTarget)
			{
				this.element = data.styleNode.currentTarget;
			}
			else
			{
				this.element = this.frame.document.querySelectorAll(this.selector)[0];
			}

			if (this.element)
			{
				var family = BX.style(this.element, "font-family");

				if (family)
				{
					family = family.replace(REG_SYNTAX, "");
					this.content = family.split(",")[0];
					this.input.innerHTML = this.content;
				}

				this.createLinkContainer();
				this.setConditionLink();
			}
		}

		if (this.content.search(new RegExp("var\\(--[a-z-]*\\)")) !== -1)
		{
			var fontFamily = window.getComputedStyle(document.body).getPropertyValue("font-family");
			this.input.innerHTML = fontFamily.replace(REG_SYNTAX, "").split(",")[0];
		}

		bind(this.input, "click", proxy(this.onInputClick, this));
		this.defaultFontLink.subscribe('onClick', proxy(this.onDefaultFontLinkClick, this));
	};


	function makeFontClassName(family)
	{
		return "g-font-" + family.toLowerCase().replace(REG_SPACE, "-");
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
		 */
		onDefaultFontLinkClick: function()
		{
			if (this.defaultFontFamily)
			{
				const foundedClasses = this.element.classList.value.match(REG_CLASS_FONT);
				if (foundedClasses !== null)
				{
					foundedClasses.forEach(foundedClass => {
						this.element.classList.remove(foundedClass);
					});
				}
				this.content = this.defaultFontFamily.replace(REG_SYNTAX, "").split(",")[0];
				const font = {
					family: this.content
				};
				this.setValue(font);
			}
		},

		createLinkContainer: function()
		{
			this.linkContainer = document.createElement("div");
			this.linkContainer.classList.add('landing-ui-component-link-container');
			this.layout.append(this.linkContainer);
		},

		setConditionLink: function()
		{
			if (this.element)
			{
				if (HEADER_TAGS.includes(this.element.tagName))
				{
					const emptyHeader = document.createElement("H2");
					document.body.appendChild(emptyHeader);
					this.defaultFontFamily = getComputedStyle(emptyHeader).fontFamily;
					emptyHeader.remove();
				}
				else
				{
					this.defaultFontFamily = getComputedStyle(document.body).fontFamily;
				}

				this.defaultFont = this.defaultFontFamily.match(REG_NAME_FONT)[0];
				this.currentFont = getComputedStyle(this.element).fontFamily.match(REG_NAME_FONT)[0];
				if (this.defaultFont !== this.currentFont)
				{
					this.linkContainer.append(this.defaultFontLink.getLayout());
				}
				else if (this.linkContainer.hasChildNodes())
				{
					this.linkContainer.removeChild(this.linkContainer.firstChild);
				}
			}
		},

		setValue: function(value, preventEvent)
		{
			if (isPlainObject(value))
			{
				var className = makeFontClassName(value.family);
				var weightList = [100, 200, 300, 400, 500, 600, 700, 800, 900];
				var weightParams = ':wght@' + weightList.join(';');
				var family = value.family.replace(REG_SPACE, "+");
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
					const FontManager = BX.Landing.UI.Tool.FontManager.getInstance();

					// Add font to current document
					FontManager.addFont({
						className: className,
						family: family,
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

					if (!preventEvent)
					{
						let headString = "";
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

			this.setConditionLink();
		},

		getValue: function()
		{
			return this.value;
		},

		onFrameLoad: function ()
		{

			const classes = Array.from(this.element.classList.value.matchAll(REG_CLASS_FONT));
			if (classes)
			{
				const family = classes[classes.length - 1][1]
					.split('-')
					.map(part => {
						return part.charAt(0).toUpperCase() + part.slice(1);
					})
					.join(' ')
				;
				this.content = family;
				this.setValue({family: family}, true)
			}
		},
	}
})();