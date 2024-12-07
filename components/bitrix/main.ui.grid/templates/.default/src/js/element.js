(function() {
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
		init(node, parent)
		{
			this.node = node;
			this.parent = parent;
			this.resetOnclickAttr();
		},

		getParent()
		{
			return this.parent;
		},

		load()
		{
			BX.addClass(this.getNode(), this.getParent().settings.get('classLoad'));
		},

		unload()
		{
			BX.removeClass(this.getNode(), this.getParent().settings.get('classLoad'));
		},

		isLoad()
		{
			return BX.hasClass(this.getNode(), this.getParent().settings.get('classLoad'));
		},

		resetOnclickAttr()
		{
			if (BX.type.isDomNode(this.getNode()))
			{
				this.getNode().onclick = null;
			}
		},

		getObserver()
		{
			return BX.Grid.observer;
		},

		getNode()
		{
			return this.node;
		},

		getLink()
		{
			let result;

			try
			{
				result = this.getNode().href;
			}
			catch
			{
				result = null;
			}

			return result;
		},
	};
})();
