/**
 * window.CustomEvent polyfill
 */
;(function() {
	'use strict';

	try
	{
		new window.CustomEvent('bx-test-custom-event', {
			bubbles: true,
  			cancelable: true
		});
	}
	catch (exception)
	{
		var CustomEventPolyfill = function(event, params)
		{
			params = params || {};
			params.bubbles = !!params.bubbles;
			params.cancelable = !!params.cancelable;

			var customEvent = document.createEvent('CustomEvent');

			customEvent.initCustomEvent(event, params.bubbles, params.cancelable, params.detail);

			var originalPreventFunction = customEvent.preventDefault;

			customEvent.preventDefault = function()
			{
				Object.defineProperty(this, 'defaultPrevented', {
					get: function() { return true; }
				});

				originalPreventFunction.call(this);
			};

			return customEvent;
		};

		CustomEventPolyfill.prototype = window.Event.prototype;

		window.CustomEvent = CustomEventPolyfill;
	}

})();