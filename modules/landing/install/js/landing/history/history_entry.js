;(function() {
	"use strict";

	BX.namespace("BX.Landing.History");

	BX.Landing.History.Entry = function(options)
	{
		this.block = options.block;
		this.selector = options.selector;
		this.command = typeof options.command === "string" ? options.command : "#invalidCommand";
		this.undo = options.undo;
		this.redo = options.redo;
	};
})();