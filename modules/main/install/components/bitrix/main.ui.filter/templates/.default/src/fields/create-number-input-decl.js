export default function createNumberInputDecl(options)
{
	return {
		block: 'main-ui-control-field',
		type: options.type,
		dragButton: false,
		content: {
			block: 'main-ui-number',
			mix: ['filter-type-single'],
			valueDelete: true,
			placeholder: options.placeholder,
			name: options.name,
			tabindex: options.tabindex,
			value: options.value,
		},
	};
}