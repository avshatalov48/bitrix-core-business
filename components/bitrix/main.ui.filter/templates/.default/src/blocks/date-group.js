;(function() {
	'use strict';

	BX.namespace('BX.Main.ui.block');

	BX.Main.ui.block['date-group'] = function(data)
	{
		var group, select, deleteButton, label, dragButton;

		group = {
			block: 'main-ui-control-field-group',
			name: 'name' ? (data.name + '_datesel') : '',
			mix: 'mix' in data ? data.mix : null,
			attrs: {
				'data-type': 'type' in data ? data.type : '',
				'data-name': 'name' in data ? data.name : '',
				'data-time': data.enableTime

			},
			content: []
		};

		if ('label' in data && BX.type.isNotEmptyString(data.label))
		{
			label = {
				block: 'main-ui-control-field-label',
				tag: 'span',
				attrs: {title: data.label},
				content: data.label
			};

			group.content.push(label);
		}

		select = {
			block: 'main-ui-control-field',
			dragButton: false,
			content: {
				block: 'main-ui-select',
				tabindex: 'tabindex' in data ? data.tabindex : '',
				value: 'value' in data ? data.value : '',
				items: 'items' in data ? data.items : '',
				name: 'name' in data ? (data.name + '_datesel') : '',
				params: 'params' in data ? data.params : '',
				valueDelete: false
			}
		};

		group.content.push(select);

		if ('content' in data && BX.type.isArray(data.content))
		{
			data.content.forEach(function(current) {
				group.content.push(current);
			});
		}

		if ('content' in data &&
			(BX.type.isPlainObject(data.content) || BX.type.isNotEmptyString(data.content)))
		{
			group.content.push(data.content);
		}

		deleteButton = {
			block: 'main-ui-item-icon-container',
			content: {
				block: 'main-ui-item-icon',
				mix: ['main-ui-delete', 'main-ui-filter-field-delete'],
				tag: 'span',
				attrs: {
					title: 'deleteTitle' in data && data.deleteTitle ? data.deleteTitle : ''
				}
			}
		};

		group.content.push(deleteButton);

		if (!('dragButton' in data) || data.dragButton !== false)
		{
			dragButton = {
				block: 'main-ui-filter-icon-grab',
				mix: ['main-ui-item-icon'],
				tag: 'span',
				attrs: {
					title: 'dragTitle' in data && data.dragTitle ? data.dragTitle : ''
				}
			};

			group.content.push(dragButton);
		}

		return group;
	};
})();