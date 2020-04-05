;(function() {
	"use strict";

	BX.namespace("BX.Landing.EventTracker.Service");


	/**
	 * Implements interface for works with Google Analytics
	 * @extends {BX.Landing.EventTracker.Service.BaseService}
	 * @constructor
	 */
	BX.Landing.EventTracker.Service.GoogleAnalytics = function()
	{
		BX.Landing.EventTracker.Service.BaseService.apply(this);
	};


	BX.Landing.EventTracker.Service.GoogleAnalytics.prototype = {
		constructor: BX.Landing.EventTracker.Service.GoogleAnalytics,
		__proto__: BX.Landing.EventTracker.Service.BaseService.prototype,


		/**
		 * @inheritDoc
		 */
		push: function(data)
		{
			if ("gtag" in window)
			{
				gtag('event', data.type, {
					'event_category': data.category,
					'event_label': data.label
				});
			}
		}
	}
})();