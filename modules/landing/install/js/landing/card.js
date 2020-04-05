(function() {
	"use strict";

	BX.namespace("BX.Landing");

	var clone = BX.Landing.Utils.clone;
	var isPlainObject = BX.Landing.Utils.isPlainObject;
	var isEmpty = BX.Landing.Utils.isEmpty;
	var data = BX.Landing.Utils.data;
	var deepFreeze = BX.Landing.Utils.deepFreeze;


	/**
	 * Implements interface for works with cards of block
	 *
	 * @param {HTMLElement} node - Element of Card in Block.
	 * @param {cardManifest} manifest - Manifest of Card in Block.
	 * @param {string} selector - Selector of node
	 * @param {string} [label]
	 * @constructor
	 */
	BX.Landing.Block.Card = function(node, manifest, selector, label)
	{
		this.node = node;
		this.manifest = manifest || {};
		this.selector = selector;
		this.panels = new BX.Landing.UI.Collection.PanelCollection();
		this.form = null;
		this.node.classList.add("landing-card");
		this.label = label;
		this.preset = null;

		if (isPlainObject(this.manifest.presets) && !isEmpty(this.manifest.presets))
		{
			var presetId = data(this.node, "data-card-preset");

			if (presetId)
			{
				this.preset = clone(this.manifest.presets[presetId]);
				this.preset.id = presetId;
				deepFreeze(this.preset);
			}
		}

		if (!BX.type.isDomNode(this.node))
		{
			throw new Error("BX.Landing.Block.Card: 'node' is not a DOMNode.");
		}

		if (!BX.type.isPlainObject(this.manifest))
		{
			throw new Error("BX.Landing.Block.Card: 'manifest' is not an Object.");
		}
	};


	/**
	 * Creates object.
	 * @static
	 */
	BX.Landing.Block.Card.init = function(node, manifest, selector)
	{
		return new BX.Landing.Block.Card(node, manifest, selector);
	};



	BX.Landing.Block.Card.prototype = {
		/**
		 * Gets card index
		 * @return {int}
		 */
		getIndex: function()
		{
			var index = parseInt(this.selector.split("@")[1]);
			index = BX.type.isNumber(index) ? index : 0;
			return index;
		},


		/**
		 * Gets card name
		 * @return {string}
		 */
		getName: function()
		{
			var name = (this.manifest.name || "") + " " + (this.getIndex() + 1);

			if (this.manifest.label)
			{
				var labelNode = this.node.querySelector("[data-selector*=\""+this.manifest.label+"\"]");

				if (labelNode && labelNode.innerHTML)
				{
					name = labelNode.innerHTML;
				}
			}

			return name;
		},


		getLabel: function()
		{
			return this.label;
		},


		/**
		 * Adds panel into card
		 * @param {BX.Landing.UI.Panel.BasePanel} panel
		 */
		addPanel: function(panel)
		{
			if (!!panel && panel instanceof BX.Landing.UI.Panel.BasePanel && !this.panels.contains(panel))
			{
				this.panels.add(panel);
				this.node.appendChild(panel.layout);
			}
		},


		clone: function()
		{
			return new BX.Landing.Block.Card(
				clone(this.node),
				clone(this.manifest),
				this.selector,
				clone(this.label)
			);
		}
	};
})();