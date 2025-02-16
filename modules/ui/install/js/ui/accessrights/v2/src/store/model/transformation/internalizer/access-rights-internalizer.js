import { Text, Type } from 'main.core';
import { DEFAULT_ALIAS_SEPARATOR, normalizeAliasKey } from '../../../../utils';
import type {
	AccessRightItem,
	AccessRightsCollection,
	AccessRightSection,
	AccessRightSectionIcon,
	Variable,
} from '../../access-rights-model';
import type { Transformer } from '../transformer';

export type ExternalAccessRightSection = {
	sectionTitle: ?any,
	sectionSubTitle: ?any,
	sectionCode: ?any,
	sectionHint: ?any,
	sectionIcon: ?any,
	rights: ExternalAccessRightItem[],
};

export type ExternalAccessRightItem = {
	id: any,
	type: any,
	title: any,
	hint: ?any,
	group: ?any,
	groupHead: ?any,
	minValue?: any | any[],
	maxValue?: any | any[],
	defaultValue?: any | any[],
	nothingSelectedValue?: any | any[],
	setEmptyOnSetMinMaxValueInColumn: ?any,

	variables?: ExternalVariable[],

	allSelectedCode: ?any,
	selectedVariablesAliases: {[key: string]: string} & {separator?: string},
	enableSearch?: any,
	showAvatars?: any,
	compactView?: any,
	hintTitle: ?any,
};

export type ExternalVariable = {
	id: any,
	title: any,
	entityId: ?any,
	supertitle: ?any,
	avatar: ?any,
	avatarOptions: ?any,
	conflictsWith?: any[],
	requires?: any[],
	secondary: ?any,
}

export class AccessRightsInternalizer implements Transformer<ExternalAccessRightSection[], AccessRightsCollection>
{
	transform(externalSource: ExternalAccessRightSection[]): AccessRightsCollection
	{
		const result: AccessRightsCollection = new Map();

		for (const external of externalSource)
		{
			const internalized = this.#internalizeExternalSection(external);
			result.set(internalized.sectionCode, internalized);
		}

		return result;
	}

	#internalizeExternalSection(externalSection: ExternalAccessRightSection): AccessRightSection
	{
		const internalizedSection: AccessRightSection = {
			sectionCode: Type.isStringFilled(externalSection.sectionCode)
				? externalSection.sectionCode
				: Text.getRandom(),
			sectionTitle: String(externalSection.sectionTitle),
			sectionSubTitle: Type.isStringFilled(externalSection.sectionSubTitle)
				? externalSection.sectionSubTitle
				: null,
			sectionHint: Type.isStringFilled(externalSection.sectionHint)
				? externalSection.sectionHint
				: null,
			sectionIcon: this.#internalizeExternalIcon(externalSection.sectionIcon),
			rights: new Map(),
			isExpanded: true,
			isShown: true,
		};

		for (const externalItem of externalSection.rights)
		{
			const internalizedItem = this.#internalizeExternalItem(externalItem);

			internalizedSection.rights.set(internalizedItem.id, internalizedItem);
		}

		return internalizedSection;
	}

	#internalizeExternalIcon(externalIcon: ?any): ?AccessRightSectionIcon
	{
		if (Type.isStringFilled(externalIcon?.type) && Type.isStringFilled(externalIcon?.bgColor))
		{
			return {
				type: externalIcon.type,
				bgColor: externalIcon.bgColor,
			};
		}

		return null;
	}

	#internalizeExternalItem(externalItem: ExternalAccessRightItem): AccessRightItem
	{
		const [aliases, separator] = this.#internalizeSelectedVariablesAliases(externalItem.selectedVariablesAliases);

		const normalizedItem: AccessRightItem = {
			id: String(externalItem.id),
			type: String(externalItem.type),
			title: String(externalItem.title),
			hint: Type.isStringFilled(externalItem.hint) ? externalItem.hint : null,
			group: Type.isNil(externalItem.group) ? null : String(externalItem.group),
			groupHead: Type.isBoolean(externalItem.groupHead) ? externalItem.groupHead : false,
			isShown: true,
			minValue: this.#internalizeValueSet(externalItem.minValue),
			maxValue: this.#internalizeValueSet(externalItem.maxValue),
			defaultValue: this.#internalizeValueSet(externalItem.defaultValue),
			emptyValue: this.#internalizeValueSet(externalItem.emptyValue),
			nothingSelectedValue: this.#internalizeValueSet(externalItem.nothingSelectedValue),
			setEmptyOnSetMinMaxValueInColumn: this.#internalizeSetEmptyOnSetMinMaxValueInColumn(externalItem),
			variables: Type.isArray(externalItem.variables) ? new Map() : null,

			allSelectedCode: Type.isStringFilled(externalItem.allSelectedCode) ? externalItem.allSelectedCode : null,
			selectedVariablesAliases: aliases,
			selectedVariablesAliasesSeparator: separator,
			enableSearch: Type.isBoolean(externalItem.enableSearch) ? externalItem.enableSearch : null,
			showAvatars: Type.isBoolean(externalItem.showAvatars) ? externalItem.showAvatars : null,
			compactView: Type.isBoolean(externalItem.compactView) ? externalItem.compactView : null,
			hintTitle: Type.isStringFilled(externalItem.hintTitle) ? externalItem.hintTitle : null,
		};
		if (normalizedItem.groupHead || normalizedItem.group)
		{
			normalizedItem.isGroupExpanded = false;
		}

		if (Type.isArray(externalItem.variables))
		{
			for (const variable of externalItem.variables)
			{
				const normalizedVariable = this.#internalizeExternalVariable(variable);

				normalizedItem.variables.set(normalizedVariable.id, normalizedVariable);
			}
		}

		return normalizedItem;
	}

	#internalizeSelectedVariablesAliases(externalAliases: {[key: string]: string} & {separator?: string}): [Map, string]
	{
		if (!Type.isPlainObject(externalAliases))
		{
			return [new Map(), DEFAULT_ALIAS_SEPARATOR];
		}

		const separator = Type.isString(externalAliases.separator) ? externalAliases.separator : DEFAULT_ALIAS_SEPARATOR;

		const result = new Map();
		for (const [key, value] of Object.entries(externalAliases))
		{
			if (key === 'separator')
			{
				continue;
			}

			result.set(normalizeAliasKey(key, separator), String(value));
		}

		return [result, separator];
	}

	#internalizeValueSet(value: any | any[] | undefined): ?Set<string>
	{
		if (Type.isNil(value))
		{
			return null;
		}

		if (Type.isArray(value))
		{
			return new Set(value.map((item) => String(item)));
		}

		return new Set([String(value)]);
	}

	#internalizeSetEmptyOnSetMinMaxValueInColumn(externalItem: ExternalAccessRightItem): ?boolean
	{
		const boolOrNull = (x: any) => (Type.isBoolean(x) ? x : null);

		if (!Type.isUndefined(externalItem.setEmptyOnSetMinMaxValueInColumn))
		{
			return boolOrNull(externalItem.setEmptyOnSetMinMaxValueInColumn);
		}

		// todo compatibility, can be removed when crm update is out
		return boolOrNull(externalItem.setEmptyOnGroupActions);
	}

	#internalizeExternalVariable(externalVariable: ExternalVariable): Variable
	{
		return {
			id: String(externalVariable.id),
			title: String(externalVariable.title),
			entityId: Type.isStringFilled(externalVariable.entityId) ? externalVariable.entityId : null,
			supertitle: Type.isStringFilled(externalVariable.supertitle) ? externalVariable.supertitle : null,
			avatar: Type.isStringFilled(externalVariable.avatar) ? externalVariable.avatar : null,
			avatarOptions: Type.isPlainObject(externalVariable.avatarOptions) ? externalVariable.avatarOptions : null,
			conflictsWith: Type.isArray(externalVariable.conflictsWith)
				? new Set(externalVariable.conflictsWith.map((x) => String(x)))
				: null,
			requires: Type.isArray(externalVariable.requires)
				? new Set(externalVariable.requires.map((x) => String(x)))
				: null,
			secondary: Type.isBoolean(externalVariable.secondary) ? externalVariable.secondary : null,
		};
	}
}
