;(function() {
	'use strict';

	BX.namespace('BX.Main.ui.block');

	BX.Main.ui.block['sidebar-item'] = function(data)
	{
		return {
			block: 'main-ui-filter-sidebar-item' + ('pinned' in data && data.pinned ? ' main-ui-item-pin' : ''),
			attrs: {
				'data-id': 'id' in data ? data.id : ''
			},
			content: [
				{
					block: 'main-ui-filter-icon-grab',
					tag: 'span',
					mix: ['main-ui-item-icon'],
					attrs: {
						title: 'dragTitle' in data && data.dragTitle ? data.dragTitle : ''
					}
				},
				{
					block: 'main-ui-filter-sidebar-item-text-container',
					tag: 'span',
					content: [
						{
							block: 'main-ui-filter-sidebar-item-input',
							tag: 'input',
							attrs: {
								type: 'text',
								placeholder: 'placeholder' in data ? data.placeholder : '',
								value: 'text' in data ? BX.util.htmlspecialchars(BX.util.htmlspecialcharsback(data.text)) : ''
							}
						},
						{
							block: 'main-ui-filter-sidebar-item-text',
							tag: 'span',
							content: 'text' in data ? data.text : ''
						},
						{
							block: 'main-ui-filter-icon-pin',
							tag: 'span',
							mix: ['main-ui-item-icon'],
							attrs: {
								title: 'noEditPinTitle' in data && data.noEditPinTitle ? data.noEditPinTitle : ''
							}
						}
					]
				},
				{
					block: 'main-ui-filter-icon-edit',
					tag: 'span',
					mix: ['main-ui-item-icon'],
					attrs: {
						title: 'editNameTitle' in data && data.editNameTitle ? data.editNameTitle : ''
					}
				},
				{
					block: 'main-ui-delete',
					tag: 'span',
					mix: ['main-ui-item-icon'],
					attrs: {
						title: 'removeTitle' in data && data.removeTitle ? data.removeTitle : ''
					}
				},
				{
					block: 'main-ui-filter-icon-pin',
					tag: 'span',
					mix: ['main-ui-item-icon'],
					attrs: {
						title: 'editPinTitle' in data && data.editPinTitle ? data.editPinTitle : ''
					}
				},
				{
					block: 'main-ui-filter-edit-mask'
				}
			]
		};
	};
})();