import {Loc} from 'main.core';

export const MAX_FIELD_LENGTH = 20;

/**
 * @memberof BX.UI.UserFieldFactory
 */
export const FieldTypes = Object.freeze({
	string: 'string',
	enumeration: 'enumeration',
	date: 'date',
	datetime: 'datetime',
	address: 'address',
	url: 'url',
	file: 'file',
	money: 'money',
	boolean: 'boolean',
	double: 'double',
	employee: 'employee',
	crm: 'crm',
	crmStatus: 'crm_status',
});

export const FieldDescriptions = Object.freeze({
	string: {
		title: Loc.getMessage("UI_USERFIELD_FACTORY_UF_STRING_TITLE"),
		description: Loc.getMessage("UI_USERFIELD_FACTORY_UF_STRING_LEGEND"),
		defaultTitle: Loc.getMessage('UI_USERFIELD_FACTORY_UF_STRING_LABEL'),
	},
	enumeration: {
		title: Loc.getMessage("UI_USERFIELD_FACTORY_UF_ENUM_TITLE"),
		description: Loc.getMessage("UI_USERFIELD_FACTORY_UF_ENUM_LEGEND"),
		defaultTitle: Loc.getMessage('UI_USERFIELD_FACTORY_UF_ENUMERATION_LABEL'),
	},
	datetime: {
		title: Loc.getMessage("UI_USERFIELD_FACTORY_UF_DATETIME_TITLE"),
		description: Loc.getMessage("UI_USERFIELD_FACTORY_UF_DATETIME_LEGEND"),
		defaultTitle: Loc.getMessage('UI_USERFIELD_FACTORY_UF_DATETIME_LABEL'),
	},
	address: {
		title: Loc.getMessage("UI_USERFIELD_FACTORY_UF_ADDRESS_TITLE"),
		description: Loc.getMessage("UI_USERFIELD_FACTORY_UF_ADDRESS_LEGEND"),
	},
	url: {
		title: Loc.getMessage("UI_USERFIELD_FACTORY_UF_URL_TITLE"),
		description: Loc.getMessage("UI_USERFIELD_FACTORY_UF_URL_LEGEND"),
	},
	file: {
		title: Loc.getMessage("UI_USERFIELD_FACTORY_UF_FILE_TITLE"),
		description: Loc.getMessage("UI_USERFIELD_FACTORY_UF_FILE_LEGEND"),
		defaultTitle: Loc.getMessage('UI_USERFIELD_FACTORY_UF_FILE_LABEL'),
	},
	money: {
		title: Loc.getMessage("UI_USERFIELD_FACTORY_UF_MONEY_TITLE"),
		description: Loc.getMessage("UI_USERFIELD_FACTORY_UF_MONEY_LEGEND"),
		defaultTitle: Loc.getMessage('UI_USERFIELD_FACTORY_UF_MONEY_LABEL'),
	},
	boolean: {
		title: Loc.getMessage("UI_USERFIELD_FACTORY_BOOLEAN_TITLE"),
		description: Loc.getMessage("UI_USERFIELD_FACTORY_UF_BOOLEAN_LEGEND"),
	},
	double: {
		title: Loc.getMessage("UI_USERFIELD_FACTORY_UF_DOUBLE_TITLE"),
		description: Loc.getMessage("UI_USERFIELD_FACTORY_UF_DOUBLE_LEGEND"),
		defaultTitle: Loc.getMessage('UI_USERFIELD_FACTORY_UF_DOUBLE_LABEL'),
	},
	employee: {
		title: Loc.getMessage("UI_USERFIELD_FACTORY_UF_EMPLOYEE_TITLE"),
		description: Loc.getMessage("UI_USERFIELD_FACTORY_UF_EMPLOYEE_LEGEND"),
	},
});

export const DefaultData = Object.freeze({
	MULTIPLE: 'N',
	MANDATORY: 'N',
	USER_TYPE_ID: FieldTypes.string,
	SHOW_FILTER: 'E',
	SHOW_IN_LIST: 'Y',
	SETTINGS: {},
	IS_SEARCHABLE: 'N',
});

export const DefaultFieldData = Object.freeze({
	file: {
		SHOW_FILTER: 'N',
		SHOW_IN_LIST: 'N',
	},
	employee: {
		SHOW_FILTER: 'I',
	},
	crm: {
		SHOW_FILTER: 'I',
	},
	crm_status: {
		SHOW_FILTER: 'I',
	},
	enumeration: {
		SETTINGS: {
			DISPLAY: 'UI',
		},
	},
	double: {
		SETTINGS: {
			PRECISION: 2,
		},
	},
});