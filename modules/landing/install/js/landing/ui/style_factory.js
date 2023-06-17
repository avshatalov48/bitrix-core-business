;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Factory");


	/**
	 * Implements style factory
	 * @param {{
	 * 		frame: ?HTMLDocument|Window,
	 * 		[postfix]: ?string,
	 * 	}} options
	 * @constructor
	 */
	BX.Landing.UI.Factory.StyleFactory = function(options)
	{
		this.frame = !!options.frame ? options.frame : null;
		this.postfix = typeof options.postfix === "string" ? options.postfix : "";
	};


	function formatClassName(element, value, items, postfix, inlineReset) {
		postfix = !!postfix ? postfix : "";

		if (!!value && typeof value === "object")
		{
			var valueKeys = Object.keys(value);
			value = valueKeys.map(function(key) {
				return value[key];
			});
		}
		else if (typeof value === "string")
		{
			value = [value];
		}

		value.forEach(function(valueItem) {
			items.forEach(function(item) {
				if (valueItem+postfix !== item.value+postfix)
				{
					element.classList.remove(item.value+postfix);
				}

				if (inlineReset)
				{
					element.style[inlineReset] = null;
					[].slice.call(element.querySelectorAll("*")).forEach(function(child) {
						child.style[inlineReset] = null;
					});
				}
			});

			element.classList.add(valueItem+postfix);
		});
	}


	BX.Landing.UI.Factory.StyleFactory.prototype = {
		/**
		 * Creates field
		 * @param {Object} options
		 * @returns {{
		 * 	[title]: string,
		 * 	[selector]: string,
		 * 	[format]: function,
		 * 	[frame]: HTMLIFrameElement,
		 * 	[property]: string,
		 * 	[items]: string[]
		 * }}
		 */
		createField: function(options)
		{
			var field = null;
			var defaultOptions = {
				title: options.title,
				selector: options.selector,
				contentRoot: BX.Landing.PageObject.getStylePanelContent(),
				style: options.style,
				format: formatClassName,
				frame: this.frame,
				property: options.property,
				pseudoElement: options.pseudoElement,
				pseudoClass: options.pseudoClass,
				items: options.items,
				postfix: this.postfix,
				onChange: options.onChange,
				onReset: options.onReset,
				help: options.help,
				attrKey: options.attrKey
			}

			if (options.type === "slider" || options.type === "range-slider")
			{
				field = new BX.Landing.UI.Field.Range(Object.assign(
					defaultOptions,
					{
						type: options.type === "range-slider" ? "multiple" : null
					}
				));
			}

			if (options.type === "buttons")
			{
				field = new BX.Landing.UI.Field.ButtonGroup(Object.assign(
					defaultOptions,
					{
						multiple: options.multiple === true
					}
				));
			}

			if (options.type === "display")
			{
				field = new BX.Landing.UI.Field.ButtonGroup(Object.assign(
					defaultOptions,
					{
						multiple: true,
						className: "landing-ui-display-button-group"
					}
				));
			}

			if (options.type === "palette")
			{
				field = new BX.Landing.UI.Field.ColorPalette(defaultOptions);
			}

			// todo: need save Backward compatibility for "pallette"?
			if (options.type === "color")
			{
				field = new BX.Landing.UI.Field.ColorField(Object.assign(
					defaultOptions,
					{
						block: options.block,
						styleNode: options.styleNode,
						subtype: options.subtype
					}
				));
			}

			if (options.type === "list" && options.style !== "font-family")
			{
				field = new BX.Landing.UI.Field.Dropdown(defaultOptions);
			}

			if (options.style === "font-family")
			{
				field = new BX.Landing.UI.Field.Font(Object.assign(
					defaultOptions,
					{
						styleNode: options.styleNode
					}
				));
			}

			return field;

		}
	};
})();