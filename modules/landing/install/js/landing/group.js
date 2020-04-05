;(function() {
	"use strict";

	BX.namespace("BX.Landing");


	/**
	 * Implements interface for works with nodes group
	 * @param {{
	 * 		id: string,
	 * 		nodes: BX.Landing.Collection.NodeCollection,
	 * 		onClick: function,
	 * 		name: ?string
	 * 	}} options
	 * @constructor
	 */
	BX.Landing.Group = function(options)
	{
		this.id = options.id;
		this.name = typeof options.name === "string" ? options.name : null;
		this.nodes = options.nodes;
		this.callback = typeof options.onClick === "function" ? options.onClick : (function() {});

		var onClickHandler = this.onClick.bind(this);

		this.nodes.forEach(function(node) {
			node.node.addEventListener("click", onClickHandler);
		});
	};


	BX.Landing.Group.prototype = {
		onClick: function(event)
		{
			event.preventDefault();
			event.stopPropagation();
			event.stopImmediatePropagation();

			this.callback(this);

			return false;
		}
	};

})();