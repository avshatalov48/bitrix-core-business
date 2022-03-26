import type {FormDictionary, FormOptions} from 'crm.form.type';

export type RuleCondition = {
	target: string,
	event: string,
	value: string,
	operation: ?string,
};

export type RuleAction = {
	target: string,
	type: string,
	value: string,
};

export type Rule = {
	condition: RuleCondition,
	action: RuleAction,
};

export type CrmField = {
	type: 'list' | 'string' | 'checkbox' | 'date' | 'text' | 'typed_string' | 'file',
	entity_field_name: string,
	entity_name: string,
	name: string,
	caption: string,
	multiple: boolean,
	required: boolean,
	hidden: boolean,
	items: Array<{ID: any, VALUE: any}>,
};

export type RuleGroupOptions = {
	dictionary: FormDictionary,
	fields: Array<CrmField>,
	data: {
		id: string,
		typeId: number,
		logic: string,
		list: Array<Rule>,
	}
};
