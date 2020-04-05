;(function() {
	"use strict";

	BX.namespace("BX.Landing.EventTracker.Service");


	/**
	 * Implements base interface for works with analytic service
	 * @constructor
	 */
	BX.Landing.EventTracker.Service.BaseService = function()
	{

	};


	BX.Landing.EventTracker.Service.BaseService.prototype = {
		/**
		 * Pushes data to analytics service
		 * @param data
		 */
		push: function(data)
		{
			throw new Error("Must be implemented by subclass");
		}
	};
})();