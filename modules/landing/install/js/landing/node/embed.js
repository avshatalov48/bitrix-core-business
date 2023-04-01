;(function() {
	"use strict";

	BX.namespace("BX.Landing");

	var data = BX.Landing.Utils.data;

	/**
	 * @extends {BX.Landing.Block.Node}
	 * @param options
	 * @constructor
	 */
	BX.Landing.Block.Node.Embed = function(options)
	{
		BX.Landing.Block.Node.apply(this, arguments);
		this.type = 'embed';
		this.attribute = ['data-src', 'data-source', 'data-preview'];
		this.onAttributeChangeHandler = options.onAttributeChange || (function() {});
		this.lastValue = this.getValue();
		this.nodeContainer = this.node.closest(BX.Landing.Block.Node.Embed.CONTAINER_SELECTOR);
	};

	BX.Landing.Block.Node.Embed.CONTAINER_SELECTOR = '.embed-responsive';
	BX.Landing.Block.Node.Embed.RATIO_CLASSES = [
		'embed-responsive-16by9',
		'embed-responsive-9by16',
		'embed-responsive-4by3',
		'embed-responsive-3by4',
		'embed-responsive-21by9',
		'embed-responsive-9by21',
		'embed-responsive-1by1',
	];
	BX.Landing.Block.Node.Embed.DEFAULT_RATIO_V = 'embed-responsive-9by16';
	BX.Landing.Block.Node.Embed.DEFAULT_RATIO_H = 'embed-responsive-16by9';

	BX.Landing.Block.Node.Embed.prototype = {
		constructor: BX.Landing.Block.Node.Embed,
		__proto__: BX.Landing.Block.Node.prototype,

		onChange: function(preventHistory)
		{
			this.lastValue = this.getValue();
			this.onAttributeChangeHandler(this);
			this.onChangeHandler(this, preventHistory);
		},

		isChanged: function()
		{
			return JSON.stringify(this.getValue()) !== JSON.stringify(this.lastValue);
		},

		getValue: function()
		{
			const ratio = this.nodeContainer
				? BX.Landing.Block.Node.Embed.RATIO_CLASSES.find(item => this.nodeContainer.classList.contains(item))
				: ''
			;

			return {
				src: this.node.src ? this.node.src : data(this.node, "data-src"),
				source: data(this.node, "data-source"),
				preview: data(this.node, "data-preview"),
				ratio: ratio || '',
			};
		},

		/**
		 * Sets node value
		 * @abstract
		 * @param {*} value
		 * @param {?boolean} [preventSave = false]
		 * @param {?boolean} [preventHistory = false]
		 * @return void
		 */
		setValue: function(value, preventSave, preventHistory)
		{
			// if iframe or preview-div
			if (this.node.src)
			{
				this.node.src = value.src;
			}
			else
			{
				data(this.node, "data-src", value.src)
			}

			data(this.node, "data-source", value.source);
			if (value.preview)
			{
				data(this.node, "data-preview", value.preview);
				this.node.style.backgroundImage = "url(\""+value.preview+"\")";
			}
			else
			{
				data(this.node, "data-preview", null);
				this.node.style.backgroundImage = "";
			}

			if (
				value.src && value.ratio
				&& this.lastValue.src !== value.src
				&& BX.Landing.Block.Node.Embed.RATIO_CLASSES.indexOf(value.ratio) !== -1
				&& this.nodeContainer
			)
			{
				BX.Landing.Block.Node.Embed.RATIO_CLASSES.forEach(ratioClass =>
				{
					(value.ratio === ratioClass)
						? BX.Dom.addClass(this.nodeContainer, ratioClass)
						: BX.Dom.removeClass(this.nodeContainer, ratioClass)
					;
				});
			}

			if (this.isChanged())
			{
				if (!preventHistory)
				{
					BX.Landing.History.getInstance().push();
				}

				this.onChange(preventHistory);
			}
		},

		getField: function()
		{
			const fieldData = {
				title: this.manifest.name,
				selector: this.selector,
				content: this.getValue()
			};
			if (BX.Dom.hasClass(this.node.parentNode, 'bg-video__inner'))
			{
				return new BX.Landing.UI.Field.EmbedBg(fieldData);
			}

			return new BX.Landing.UI.Field.Embed(fieldData);
		}
	};

})();