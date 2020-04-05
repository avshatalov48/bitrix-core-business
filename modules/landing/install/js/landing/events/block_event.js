;(function() {
	"use strict";

	BX.namespace("BX.Landing.Event");


	BX.Landing.Event.Block = function(options)
	{
		this.block = typeof options.block === "object" ? options.block : null;
		this.card = typeof options.card === "object" ? options.card : null;
		this.node = typeof options.node === "object" ? options.node : null;
		this.data = typeof options.data !== "undefined" ? options.data : null;
		this.forceInitHandler = typeof options.onForceInit === "function" ? options.onForceInit : (function() {});
	};

	BX.Landing.Event.Block.prototype = {
		/**
		 * Forces block initialization
		 */
		forceInit: function()
		{
			this.forceInitHandler();
		},

		/**
		 * Makes selector relative block
		 * @param {string} selector
		 * @returns {string}
		 */
		makeRelativeSelector: function(selector)
		{
			return "#" + this.block.id + " " + selector;
		}
	};
})();