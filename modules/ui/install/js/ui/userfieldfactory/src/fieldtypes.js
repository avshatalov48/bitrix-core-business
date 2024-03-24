import {Loc} from 'main.core';

export const MAX_FIELD_LENGTH = 50;

/**
 * @memberof BX.UI.UserFieldFactory
 */
export class FieldTypes
{
	static getTypes(): {}
	{
		return Object.freeze({
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
	}

	static getDescriptions(): {}
	{
		return Object.freeze({
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
			date: {
				title: Loc.getMessage("UI_USERFIELD_FACTORY_UF_DATE_TITLE"),
				description: Loc.getMessage("UI_USERFIELD_FACTORY_UF_DATE_LEGEND"),
				defaultTitle: Loc.getMessage('UI_USERFIELD_FACTORY_UF_DATE_LABEL'),
			},
			datetime: {
				title: Loc.getMessage("UI_USERFIELD_FACTORY_UF_DATETIME_TITLE"),
				description: Loc.getMessage("UI_USERFIELD_FACTORY_UF_DATETIME_LEGEND"),
				defaultTitle: Loc.getMessage('UI_USERFIELD_FACTORY_UF_DATETIME_LABEL'),
			},
			address: {
				title: Loc.getMessage("UI_USERFIELD_FACTORY_UF_ADDRESS_TITLE_2"),
				description: Loc.getMessage("UI_USERFIELD_FACTORY_UF_ADDRESS_LEGEND_2"),
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
	}

	static getCustomTypeDescription(): {}
	{
		return Object.freeze({
			name: 'custom',
			title: Loc.getMessage('UI_USERFIELD_FACTORY_UF_CUSTOM_TITLE'),
			description: Loc.getMessage('UI_USERFIELD_FACTORY_UF_CUSTOM_LEGEND'),
		});
	}
}

export const DefaultData = Object.freeze({
	multiple: 'N',
	mandatory: 'N',
	userTypeId: FieldTypes.string,
	showFilter: 'E',
	showInList: 'Y',
	settings: {},
	isSearchable: 'N',
});

export const DefaultFieldData = Object.freeze({
	file: {
		showFilter: 'N',
		showInList: 'N',
	},
	employee: {
		showFilter: 'I',
	},
	crm: {
		showFilter: 'I',
	},
	crm_status: {
		showFilter: 'I',
	},
	enumeration: {
		settings: {
			DISPLAY: 'UI',
		},
	},
	double: {
		settings: {
			PRECISION: 2,
		},
	},
});
