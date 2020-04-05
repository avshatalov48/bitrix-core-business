;(function() {
	'use strict';

	if (!Element.prototype.closest)
	{
		/**
		 * Finds closest parent element by selector
		 * @param {string} selector
		 * @return {HTMLElement|Element|Node}
		 */
		Object.defineProperty(Element.prototype, 'closest', {
			enumerable: false,
			value: function(selector) {
				var node = this;

				while (node)
				{
					if (node.matches(selector))
					{
						return node;
					}

					node = node.parentElement;
				}

				return null;
			},
		});
	}
})();