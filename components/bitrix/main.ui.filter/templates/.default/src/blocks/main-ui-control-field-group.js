;(function() {
	'use strict';

	BX.namespace('BX.Main.ui.block');

	BX.Main.ui.block['main-ui-control-field-group'] = function(data)
	{
		var field, deleteButton, label, dragButton;

		field = {
			block: 'main-ui-control-field-group',
			mix: 'mix' in data ? data.mix : null,
			attrs: {
				'data-type': 'type' in data ? data.type : '',
				'data-name': 'name' in data ? data.name : ''
			},
			content: []
		};

		if ('label' in data && BX.type.isNotEmptyString(data.label))
		{
			let labelContent = data.label;

			if ('icon' in data && BX.Type.isPlainObject(data.icon))
			{
				labelContent = [
					{
						block: 'main-ui-control-field-label-icon',
						tag: 'img',
						attrs: {
							title: data.icon.title ? data.icon.title : '',
							src: data.icon.url
						}
					},
					{
						block: 'main-ui-control-field-label-text',
						tag: 'span',
						content: labelContent
					}
				];
			}
			label = {
				block: 'main-ui-control-field-label',
				tag: 'span',
				attrs: {title: data.label},
				content: labelContent
			};

			field.content.push(label);
		}

		if (BX.type.isArray(data.content))
		{
			data.content.forEach(function(current) {
				field.content.push(current);
			});
		}
		else if (BX.type.isPlainObject(data.content) ||
			BX.type.isNotEmptyString(data.content))
		{
			field.content.push(data.content);
		}

		if ('deleteButton' in data && data.deleteButton === true)
		{
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

			field.content.push(deleteButton);
		}

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

			field.content.push(dragButton);
		}

		return field;
	};

})();