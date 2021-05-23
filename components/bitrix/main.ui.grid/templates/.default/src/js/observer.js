;(function() {
	'use strict';

	BX.namespace('BX.Grid');

	BX.Grid.observer = {
		handlers: [],
		add: function(node, event, handler, context)
		{
			BX.bind(node, event, context ? BX.proxy(handler, context) : handler);
		}
	};
})();