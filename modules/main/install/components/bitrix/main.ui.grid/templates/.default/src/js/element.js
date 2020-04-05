;(function() {
	'use strict';

	BX.namespace('BX.Grid');

	/**
	 * @param {HtmlElement} node
	 * @param {BX.Main.grid} [parent]
	 * @constructor
	 */
	BX.Grid.Element = function(node, parent)
	{
		this.node = null;
		this.href = null;
		this.parent = null;
		this.init(node, parent);
	};

	BX.Grid.Element.prototype = {
		init: function(node, parent)
		{
			this.node = node;
			this.parent = parent;
			this.resetOnclickAttr();
		},

		getParent: function()
		{
			return this.parent;
		},

		load: function()
		{
			BX.addClass(this.getNode(), this.getParent().settings.get('classLoad'));
		},

		unload: function()
		{
			BX.removeClass(this.getNode(), this.getParent().settings.get('classLoad'));
		},

		isLoad: function()
		{
			return BX.hasClass(this.getNode(), this.getParent().settings.get('classLoad'));
		},

		resetOnclickAttr: function()
		{
			if (BX.type.isDomNode(this.getNode()))
			{
				this.getNode().onclick = null;
			}
		},

		getObserver: function()
		{
			return BX.Grid.observer;
		},

		getNode: function()
		{
			return this.node;
		},

		getLink: function()
		{
			var result;

			try {
				result = this.getNode().href;
			} catch (err) {
				result = null;
			}

			return result;
		}
	};
})();