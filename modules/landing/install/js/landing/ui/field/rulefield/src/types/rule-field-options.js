import {Dictionary} from 'crm.form';

export interface FormField
{
	id: string,
	label: string,
	items: Array<{value: any, label: string}>,
	type: string,
}

export interface ExpressionEntry {
	field: string,
	action: 'show' | 'hide',
}

export interface RuleEntryOptions
{
	condition: {
		field: string,
		value: any,
		operator: '=' | '!=',
	},
	expression: Array<ExpressionEntry>,
	dictionary: Dictionary,
}

export interface FieldRulesOptions extends RuleEntryOptions
{
	fields: Array<FormField>,
}

export type RuleType = 'type1' | 'type2' | 'type3';

export interface RuleFieldOptions
{
	fields: Array<FormField>,
	rules: Array<FieldRules>,
	dictionary: Dictionary,
	type: RuleType,
}