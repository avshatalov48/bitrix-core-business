;(function() {
	'use strict';

	BX.namespace('BX.Main.ui.block');

	BX.Main.ui.block['main-ui-filter-info'] = function(data)
	{
		return {
			block: 'main-ui-filter-info',
			tag: 'span',
			content: data.content,
			attrs: {
				title: data.title
			}
		}
	};
})();