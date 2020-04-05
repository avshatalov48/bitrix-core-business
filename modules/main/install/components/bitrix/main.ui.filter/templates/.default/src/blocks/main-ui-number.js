;(function() {
	'use strict';

	BX.namespace('BX.Main.ui.block');

	BX.Main.ui.block['main-ui-number'] = function(data)
	{
		var control, input, valueDelete;

		control = {
			block: 'main-ui-number',
			mix: ['main-ui-control'],
			content: []
		};

		if ('mix' in data && BX.type.isArray(data.mix))
		{
			data.mix.forEach(function(current) {
				control.mix.push(current);
			});
		}

		input = {
			block: 'main-ui-number-input',
			mix: ['main-ui-control-input'],
			tag: 'input',
			attrs: {
				type: 'number',
				name: 'name' in data ? data.name : '',
				tabindex: 'tabindex' in data ? data.tabindex : '',
				value: 'value' in data ? data.value : '',
				placeholder: 'placeholder' in data ? data.placeholder : '',
				autocomplete: 'off'
			}
		};

		control.content.push(input);

		if ('valueDelete' in data && data.valueDelete === true)
		{
			valueDelete = {
				block: 'main-ui-control-value-delete',
				content: {
					block: 'main-ui-control-value-delete-item',
					tag: 'span'
				}
			};

			control.content.push(valueDelete);
		}

		return control;
	};
})();