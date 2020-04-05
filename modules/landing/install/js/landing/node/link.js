;(function() {
	"use strict";

	BX.namespace("BX.Landing");

	var trim = BX.Landing.Utils.trim;
	var isPlainObject = BX.Landing.Utils.isPlainObject;
	var isString = BX.Landing.Utils.isString;
	var textToPlaceholders = BX.Landing.Utils.textToPlaceholders;
	var create = BX.Landing.Utils.create;
	var escapeText = BX.Landing.Utils.escapeText;
	var decodeDataValue = BX.Landing.Utils.decodeDataValue;

	/**
	 * Implements interface for works with link or button
	 *
	 * @extends {BX.Landing.Block.Node}
	 * @param {nodeOptions} options
	 * @constructor
	 */
	BX.Landing.Block.Node.Link = function(options)
	{
		BX.Landing.Block.Node.apply(this, arguments);

		if (!this.isGrouped())
		{
			this.node.addEventListener("click", this.onClick.bind(this));
		}

		if (this.isAllowInlineEdit())
		{
			this.node.setAttribute("title", BX.message("LANDING_TITLE_OF_LINK_NODE"));
		}
	};


	BX.Landing.Block.Node.Link.prototype = {
		__proto__: BX.Landing.Block.Node.prototype,
		constructor: BX.Landing.Block.Node.Link,

		onContentUpdate: function()
		{
			clearTimeout(this.contentEditTimeout);
			this.contentEditTimeout = setTimeout(function() {
				BX.Landing.History.getInstance().push(
					new BX.Landing.History.Entry({
						block: this.getBlock().id,
						selector: this.selector,
						command: "editLink",
						undo: this.startValue,
						redo: this.getValue()
					})
				);

				this.startValue = null;
			}.bind(this), 400);

			this.getField().setValue(this.getValue());
		},

		/**
		 * Handles click event
		 * @param {MouseEvent} event
		 */
		onClick: function(event)
		{
			event.preventDefault();
			event.stopPropagation();

			BX.Landing.UI.Button.FontAction.hideAll();
			BX.Landing.UI.Button.ColorAction.hideAll();

			if (!BX.Landing.UI.Panel.StylePanel.getInstance().isShown())
			{
				BX.Landing.UI.Panel.Link.getInstance().show(this);
			}
		},


		/**
		 * Checks that button is prevented
		 * @return {boolean}
		 */
		isPrevented: function()
		{
			return this.getValue().target === "_popup";
		},


		/**
		 * Sets node value
		 * @param data
		 * @param {?boolean} [preventSave = false]
		 * @param {?boolean} [preventHistory = false]
		 */
		setValue: function(data, preventSave, preventHistory)
		{
			this.startValue = this.startValue || this.getValue();

			this.preventSave(preventSave);

			if (!this.containsImage())
			{
				var field = this.getField(true).hrefInput;

				if (isString(data.text) && data.text.includes("{{name}}"))
				{
					field.getPlaceholderData(data.href)
						.then(function(placeholdersData) {
							this.node.innerHTML = data.text.replace(
								new RegExp("{{name}}"),
								"<span data-placeholder=\"name\">"+placeholdersData.name+"</span>"
							);
						}.bind(this));
				}
				else
				{
					if (!this.getField().containsHtml() && !this.manifest.skipContent)
					{
						this.node.innerHTML = escapeText(data.text);
					}
				}
			}

			this.node.setAttribute("href", decodeDataValue(data.href));
			this.node.setAttribute("target", escapeText(data.target));

			if ("attrs" in data)
			{
				for (var attr in data.attrs)
				{
					if (data.attrs.hasOwnProperty(attr))
					{
						this.node.setAttribute(attr, data.attrs[attr]);
					}
				}
			}
			else
			{
				this.node.removeAttribute("data-url");
				this.node.removeAttribute("data-embed");
			}

			this.onChange();

			if (!preventHistory)
			{
				this.onContentUpdate();
			}
		},


		/**
		 * Checks that this node contains image node
		 * @return {boolean}
		 */
		containsImage: function()
		{
			return !!this.node.firstElementChild && this.node.firstElementChild.tagName === "IMG";
		},


		/**
		 * Gets node value
		 * @return {{text: string, href: string|*, target: string|*}}
		 */
		getValue: function()
		{
			var value = {
				text: textToPlaceholders(trim(this.node.innerHTML)),
				href: trim(this.node.getAttribute("href")),
				target: trim(this.node.getAttribute("target") || "_self")
			};

			if (this.node.getAttribute("data-url"))
			{
				value.attrs = {
					"data-url": trim(this.node.getAttribute("data-url"))
				};
			}

			if (this.node.getAttribute("data-dynamic"))
			{
				if (!isPlainObject(value.attrs))
				{
					value.attrs = {};
				}

				value.attrs["data-dynamic"] = this.node.getAttribute("data-dynamic");
			}

			if (this.manifest.skipContent)
			{
				value['skipContent'] = true;
				delete value.text;
			}

			return value;
		},


		/**
		 * Gets field
		 * @param {boolean} [preventAdjustValue = false]
		 * @return {BX.Landing.UI.Field.BaseField}
		 */
		getField: function(preventAdjustValue)
		{
			var value = this.getValue();
			value.text = textToPlaceholders(create("div", {html: value.text}).innerHTML);

			if (!this.field)
			{
				var allowedTypes = [
					BX.Landing.UI.Field.LinkURL.TYPE_BLOCK,
					BX.Landing.UI.Field.LinkURL.TYPE_PAGE
				];

				if (BX.Landing.Main.getInstance().options.params.type === BX.Landing.Main.TYPE_STORE)
				{
					allowedTypes.push(BX.Landing.UI.Field.LinkURL.TYPE_CATALOG);
				}

				this.field = new BX.Landing.UI.Field.Link({
					title: this.manifest.name,
					selector: this.selector,
					skipContent: this.manifest.skipContent,
					content: value,
					options: {
						siteId: BX.Landing.Main.getInstance().options.site_id,
						landingId: BX.Landing.Main.getInstance().id,
						filter: {
							'=TYPE': BX.Landing.Main.getInstance().options.params.type
						}
					},
					allowedTypes: allowedTypes
				});
			}
			else
			{
				if (!preventAdjustValue)
				{
					this.field.setValue(value);
					this.field.content = value;
					this.field.hrefInput.content = value.href;
					this.field.hrefInput.makeDisplayedHrefValue();
					this.field.hrefInput.removeHrefTypeFromHrefString();
				}
			}

			return this.field;
		}
	};

})();