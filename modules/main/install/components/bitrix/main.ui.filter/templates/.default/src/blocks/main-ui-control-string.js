;(function() {
	'use strict';

	BX.namespace('BX.Main.ui.block');

	BX.Main.ui.block['main-ui-control-string'] = function(data)
	{
		return {
			block: 'main-ui-control-string',
			mix: ['main-ui-control'],
			tag: 'input',
			attrs: {
				type: 'type' in data ? data.type : 'text',
				name: 'name' in data ? data.name : '',
				placeholder: 'placeholder' in data ? data.placeholder : '',
				tabindex: 'tabindex' in data ? data.tabindex : '',
				value: 'value' in data ? data.value : ''
			}
		};
	};
})();