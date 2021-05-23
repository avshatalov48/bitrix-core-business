;(function() {
	'use strict';

	BX.namespace('BX.Main.ui.block');

	BX.Main.ui.block['main-ui-control-textarea'] = function(data)
	{
		return {
			block: 'main-ui-control-string',
			mix: ['main-ui-control main-ui-control-textarea'],
			tag: 'textarea',
			attrs: {
				name: 'name' in data ? data.name : '',
				placeholder: 'placeholder' in data ? data.placeholder : '',
				tabindex: 'tabindex' in data ? data.tabindex : '',
			},
			content: 'value' in data ? data.value : '',
		};
	};
})();