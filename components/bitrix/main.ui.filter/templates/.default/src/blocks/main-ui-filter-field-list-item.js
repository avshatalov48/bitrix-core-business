;(function() {
	'use strict';

	BX.namespace('BX.Main.ui.block');

	BX.Main.ui.block['main-ui-filter-field-list-item'] = function(data)
	{
		var label = {
			block: 'main-ui-select-inner-label',
			content: 'label' in data ? data.label : ''
		};

		var item = {
			block: 'main-ui-filter-field-list-item',
			mix: 'main-ui-select-inner-item',
			attrs: {
				'data-id': data.id,
				'data-name': data.name,
				'data-item': 'item' in data ? JSON.stringify(data.item) : {}
			},
			events: {
				click: 'onClick' in data ? data.onClick : ''
			},
			content: label
		};

		return item;
	}
})();