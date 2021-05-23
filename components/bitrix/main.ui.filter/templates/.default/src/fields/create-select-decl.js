export default function createSelectDecl(options)
{
	return {
		block: 'main-ui-control-field',
		dragButton: false,
		content: {
			block: 'main-ui-select',
			tabindex: options.tabindex,
			value: options.value,
			items: options.items,
			name: options.name,
			valueDelete: false,
		},
	};
}