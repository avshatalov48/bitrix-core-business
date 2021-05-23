;(function() {
	'use strict';

	BX.namespace('BX.Main.ui.block');

	BX.Main.ui.block['main-ui-search-square'] = function(data)
	{
		var mix = ['main-ui-filter-search-square'];

		if ('isPreset' in data && data.isPreset)
		{
			mix.push('main-ui-filter-search-square-preset');
		}

		return {
			block: 'main-ui-square',
			mix: mix,
			attrs: {
				'data-item': 'item' in data ? JSON.stringify(data.item) : '',
				'title': 'title' in data ? data.title : ''
			},
			content: [
				{
					block: 'main-ui-square-item',
					content: 'name' in data ? BX.util.htmlspecialcharsback(data.name) : ''
				},
				{
					block: 'main-ui-square-delete',
					mix: ['main-ui-item-icon']
				}
			]
		}
	};
})();