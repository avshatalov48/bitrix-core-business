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

		let title = ('title' in data ? data.title : '');
		let name = ('name' in data ? BX.util.htmlspecialcharsback(data.name) : '');
		if ('icon' in data && BX.Type.isPlainObject(data.icon))
		{
			let iconTitle = data.icon.title;
			title = title.length ? (iconTitle + ': ' + title) : '';
			name = name.length ? (iconTitle + ': ' + name)  : '';
		}

		return {
			block: 'main-ui-square',
			mix: mix,
			attrs: {
				'data-item': 'item' in data ? JSON.stringify(data.item) : '',
				'title': title
			},
			content: [
				{
					block: 'main-ui-square-item',
					content: name
				},
				{
					block: 'main-ui-square-delete',
					mix: ['main-ui-item-icon']
				}
			]
		}
	};
})();