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
	 * @return {Promise<any>}
	 */
	function setIconValue(node, value)
	{
		return BX.Landing.UI.Panel.IconPanel
			.getLibraries()
			.then(function(libraries) {
				libraries.forEach(function(library) {
					library.categories.forEach(function(category) {
						category.items.forEach(function(item) {
							var className = '';
							if (BX.Type.isObject(item))
							{
								className = item.options.join(' ');
							}
							else
							{
								className = item;
							}

							var classList = className.split(" ");
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
		 * @return {Promise<any>}
		 */
		setValue: function(value, preventSave, preventHistory)
		{
			this.lastValue = this.lastValue || this.getValue();
			this.preventSave(preventSave);

			return setIconValue(this, value)
				.then(function() {
					if (value.url)
					{
						const url = this.preparePseudoUrl(value.url);
						if (url !== null)
						{
							attr(this.node, "data-pseudo-url", url);
						}
					}
					this.onChange(preventHistory);

					if (!preventHistory)
					{
						BX.Landing.History.getInstance().push();
					}

					this.lastValue = this.getValue();
				}.bind(this));
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