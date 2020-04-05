/**
 * Array.prototype.includes polyfill
 */
(function() {
	'use strict';

	if (typeof Array.prototype.includes !== 'function')
	{
		Object.defineProperty(Array.prototype, 'includes', {
			enumerable: false,
			value: function(element)
			{
				var result = this.find(function(currentElement) {
					return currentElement === element;
				});

				return result === element;
			},
		});
	}
})();