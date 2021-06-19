import typeof {BaseField} from 'landing.ui.field.basefield';

type BaseFormOptions = {
	id?: string,
	selector?: string,
	title?: string,
	label?: string,
	type?: string,
	code?: string,
	description?: string,
	headerCheckbox?: {
		text: string,
		onChange: () => void,
		state: boolean,
		help?: string,
	},
	fields: Array<BaseField>,
	hidden?: boolean,
};

export default BaseFormOptions;