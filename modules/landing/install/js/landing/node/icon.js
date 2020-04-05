;(function() {
	"use strict";

	BX.namespace("BX.Landing.Block.Node");

	var encodeDataValue = BX.Landing.Utils.encodeDataValue;
	var decodeDataValue = BX.Landing.Utils.decodeDataValue;
	var data = BX.Landing.Utils.data;
	var attr = BX.Landing.Utils.attr;

	/**
	 * @extends {BX.Landing.Block.Node.Img}
	 * @param options
	 * @constructor
	 */
	BX.Landing.Block.Node.Icon = function(options)
	{
		BX.Landing.Block.Node.Img.apply(this, arguments);
		this.type = "icon";
	};

	function getPseudoUrl(node)
	{
		var url = data(node.node, "data-pseudo-url");
		return !!url ? url : "";
	}

	/**
	 * Gets icon class list
	 * @param {BX.Landing.Block.Node.Icon} node
	 * @return {string[]}
	 */
	function getIconClassList(node)
	{
		return node.node.className.split(" ");
	}


	/**
	 * Sets icon value or converts to span and sets value
	 * @param {BX.Landing.Block.Node.Icon} node
	 * @param {object} value
	 */
	function setIconValue(node, value)
	{
		BX.Landing.UI.Panel.Icon.getInstance().libraries.forEach(function(library) {
			library.categories.forEach(function(category) {
				category.items.forEach(function(item) {
					var classList = item.split(" ");
					classList.forEach(function(className) {
						if (className)
						{
							node.node.classList.remove(className);
						}
					});
				});
			});
		});

		value.classList.forEach(function(className) {
			node.node.classList.add(className);
		});
	}


	BX.Landing.Block.Node.Icon.prototype = {
		constructor: BX.Landing.Block.Node.Icon,
		__proto__: BX.Landing.Block.Node.Img.prototype,

		/**
		 * Gets form field
		 * @return {BX.Landing.UI.Field.BaseField}
		 */
		getField: function()
		{
			if (!this.field)
			{
				var value = this.getValue();
				value.url = decodeDataValue(value.url);

				var disableLink = !!this.node.closest("a");

				this.field = new BX.Landing.UI.Field.Icon({
					selector: this.selector,
					title: this.manifest.name,
					disableLink: disableLink,
					content: value,
					dimensions: !!this.manifest.dimensions ? this.manifest.dimensions : {}
				});
			}
			else
			{
				this.field.content = this.getValue();
			}

			return this.field;
		},


		/**
		 * Sets node value
		 * @param value - Path to image
		 * @param {?boolean} [preventSave = false]
		 * @param {?boolean} [preventHistory = false]
		 */
		setValue: function(value, preventSave, preventHistory)
		{
			this.lastValue = this.lastValue || this.getValue();
			this.preventSave(preventSave);
			setIconValue(this, value);
			if (value.url)
			{
				attr(this.node, "data-pseudo-url", value.url);
			}
			this.onChange();

			if (!preventHistory)
			{
				BX.Landing.History.getInstance().push(
					new BX.Landing.History.Entry({
						block: this.getBlock().id,
						selector: this.selector,
						command: "editIcon",
						undo: this.lastValue,
						redo: this.getValue()
					})
				);
			}

			this.lastValue = this.getValue();
		},


		/**
		 * Gets node value
		 * @return {{src: string}}
		 */
		getValue: function()
		{
			return {
				type: "icon",
				src: "",
				id: -1,
				alt: "",
				classList: getIconClassList(this),
				url: encodeDataValue(getPseudoUrl(this))
			};
		}
	};
})();