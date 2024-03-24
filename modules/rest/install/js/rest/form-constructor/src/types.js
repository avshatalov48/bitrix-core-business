export type DropdownItemConfig = {
	value: string,
	name: string,
}

export type FieldConfig = {
	id: string,
	name: string,
	type: 'input' | 'dropdown-list',
	placeholder?: string,
	items?: Array<DropdownItemConfig>,
	label?: string,
	value?: string
}

export type StepEInvoiceSettingsConfig = {
	id: string,
	title?: string,
	description?: string,
	fields?: Array<FieldConfig>,
	link?: {
		name: string,
		url: string,
	},
}

export type SettingsEInvoiceOptions = {
	steps: Array<StepEInvoiceSettingsConfig>,
	wrapper: HTMLElement,
	handler: string,
	redirect?: string,
	saveAction: function,
}
