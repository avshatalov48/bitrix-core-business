/**
 * Element.prototype.matches polyfill
 */
;(function(element) {
	'use strict';

	if (!element.matches && element.matchesSelector)
	{
		element.matches = element.matchesSelector;
	}

	if (!element.matches)
	{
		element.matches = function(selector) {
			var matches = document.querySelectorAll(selector);
			var self = this;

			return Array.prototype.some.call(matches, function(e) {
				return e === self;
			});
		};
	}

})(Element.prototype);