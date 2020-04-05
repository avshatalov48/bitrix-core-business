;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI");


	/**
	 * Implements interface for works with highlights
	 * Implements singleton pattern
	 * @constructor
	 */
	BX.Landing.UI.Highlight = function()
	{
		this.layout = BX.create("div");
		this.layout.style.position = "absolute";
		this.layout.style.border = "2px #fe541e dashed";
		this.layout.style.top = "0";
		this.layout.style.right = "0";
		this.layout.style.bottom = "0";
		this.layout.style.left = "0";
		this.layout.style.zIndex = "9999";
		this.layout.style.opacity = ".4";
		this.layout.style.pointerEvents = "none";
		this.layout.style.transform = "translateZ(0)";
	};


	/**
	 * Stores active highlights
	 * @type {BX.Landing.Collection.BaseCollection.<{node: HTMLElement, highlight: HTMLElement}>}
	 */
	BX.Landing.UI.Highlight.highlights = new BX.Landing.Collection.BaseCollection();


	/**
	 * Stores current instance
	 * @type {?BX.Landing.UI.Highlight}
	 */
	BX.Landing.UI.Highlight.instance = null;


	/**
	 * Gets instance of BX.Landing.UI.Highlight
	 * @returns {BX.Landing.UI.Highlight}
	 */
	BX.Landing.UI.Highlight.getInstance = function() {
		if (!BX.Landing.UI.Highlight.instance)
		{
			BX.Landing.UI.Highlight.instance = new BX.Landing.UI.Highlight();
		}

		return BX.Landing.UI.Highlight.instance;
	};


	BX.Landing.UI.Highlight.prototype = {
		/**
		 * Shows highlight for node
		 * @param {HTMLElement|HTMLElement[]} node
		 * @param {object} [rect]
		 */
		show: function(node, rect)
		{
			this.hide();
			if (BX.type.isArray(node))
			{
				node.forEach(function(element) {
					this.highlightNode(element);
				}, this);
			}
			else if (BX.type.isDomNode(node))
			{
				this.highlightNode(node, rect);
			}
		},


		/**
		 * Hides highlight for all nodes
		 */
		hide: function()
		{
			BX.Landing.UI.Highlight.highlights.forEach(function(item) {
				BX.DOM.write(function() {
					BX.remove(item.highlight);
					item.node.style.position = "";
					item.node.style.userSelect = "";
					item.node.style.cursor = "";
				}.bind(this));
			});

			BX.Landing.UI.Highlight.highlights.clear();
		},


		/**
		 * @private
		 * @param node
		 * @param {object} rect
		 */
		highlightNode: function(node, rect)
		{
			var highlight = BX.clone(this.layout);

			if (rect)
			{
				BX.DOM.write(function() {
					highlight.style.position = "fixed";
					highlight.style.width = rect.width + "px";
					highlight.style.height = rect.height + "px";
					highlight.style.top = rect.top + "px";
					highlight.style.left = rect.left + "px";
					highlight.style.right = rect.right + "px";
					highlight.style.bottom = rect.bottom + "px";
				});

				BX.Landing.PageObject.getInstance().view().then(function(frame) {
					BX.DOM.write(function() {
						BX.append(highlight, frame.contentDocument.body);
					}.bind(this));
				});
			}
			else
			{
				BX.DOM.write(function() {
					BX.append(highlight, node);
				}.bind(this));
			}

			BX.DOM.write(function() {
				node.style.position = "relative";
				node.style.userSelect = "none";
				node.style.cursor = "pointer";
			}.bind(this));

			BX.Landing.UI.Highlight.highlights.add({node: node, highlight: highlight});
		}
	};

})();