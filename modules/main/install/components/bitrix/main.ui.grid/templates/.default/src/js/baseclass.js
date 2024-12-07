(function() {
	'use strict';

	BX.namespace('BX.Grid');

	/**
	 * Base class
	 * @param {BX.Main.grid} parent
	 * @constructor
	 */
	BX.Grid.BaseClass = function(parent)
	{
		this.parent = parent;
	};

	BX.Grid.BaseClass.prototype = {
		getParent()
		{
			return this.parent;
		},
	};
})();
