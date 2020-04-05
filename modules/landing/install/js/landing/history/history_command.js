;(function() {
	"use strict";

	BX.namespace("BX.Landing.History");


	/**
	 * Implements interface for works with command of history
	 * @param {{id: string, undo: function, redo: function}} options
	 * @constructor
	 */
	BX.Landing.History.Command = function(options)
	{
		this.id = typeof options.id === "string" ? options.id : "#invalidCommand";
		this.undo = typeof options.undo ? options.undo : (function() {});
		this.redo = typeof options.redo ? options.redo : (function() {});
	};
})();