import { BaseEvent } from 'main.core.events';

export type Field = {
	Id: string,
	Type: string,
	Name: string,
	ObjectName: string,
	ObjectId?: string,
	SystemExpression: string,
	Expression: string,
}

export type MenuGroupItem = {
	id: string,
	title: string,
	supertitle: ?string,
	entityId: ?string,
	tabs: ?string,
	searchable: ?boolean,
	customData: ?{
		field: Field,
	},
	children: ?Array<MenuGroupItem>,
}

export type ConditionSelectorOptions = {
	fields: Array<object>,
	joiner: string,
	fieldPrefix: string,
	rootGroupTitle: ?string,
	onOpenFieldMenu?: (BaseEvent) => void,
	onOpenMenu?: (BaseEvent) => void,
	showValuesSelector: ?boolean,
}
