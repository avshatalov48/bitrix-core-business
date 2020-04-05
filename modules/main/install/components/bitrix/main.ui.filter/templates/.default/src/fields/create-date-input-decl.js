export default function createDateInputDecl(options)
{
	return {
		block: 'main-ui-control-field',
		type: options.type,
		dragButton: false,
		content: {
			block: 'main-ui-date',
			mix: ['filter-type-single'],
			calendarButton: true,
			valueDelete: true,
			placeholder: options.placeholder,
			name: options.name,
			tabindex: options.tabindex,
			value: options.value,
			enableTime: options.enableTime,
		},
	};
}