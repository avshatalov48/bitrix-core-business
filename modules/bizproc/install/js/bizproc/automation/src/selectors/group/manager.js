import { Loc, Type, Runtime } from 'main.core';
import { GlobalsProperty } from '../../automation-globals';
import { Field } from '../types';
import { ConstantGroup } from './constant-group';
import { DocumentGroup } from './document-group';
import { FileGroup } from './file-group';
import { ActivityResultGroup } from './activity-result-group';
import { TriggerResultGroup } from './trigger-result-group';
import { VariableGroup } from './variable-group';

export class SelectorItemsManager
{
	#documentFields: Array<Field> = [];
	#documentTitle: string = Loc.getMessage('BIZPROC_JS_AUTOMATION_SELECTOR_GROUP_MANAGER_DOCUMENT_GROUP_TITLE');
	#linkFiles: Array<Field> = [];
	#variables: Array<Field> = [];
	#constants: Array<Field> = [];
	#activityResultFields: Array<{id: string, title: string, fields: Array<Field>}> = [];
	#activityResultFieldsTitle: string = Loc.getMessage('BIZPROC_AUTOMATION_CMP_ROBOT_LIST');
	#triggerResultFields: Array<{id: string, title: string, fields: Array<Field>}> = [];

	constructor(data: {
		documentFields?: Array<Field>,
		documentTitle?: string,
		globalVariables?: Array<GlobalsProperty>,
		variables?: Array<Field>,
		globalConstants?: Array<GlobalsProperty>,
		constants?: Array<Field>,
		activityResultFields?: Array<{id: string, title: string, fields: Array<Field>}>,
		activityResultFieldsTitle: ?string,
		triggerResultFields?: Array<Field>,
	})
	{
		if (Type.isArray(data.documentFields))
		{
			this.#setDocumentFields(data.documentFields);
		}

		if (Type.isStringFilled(data.documentTitle))
		{
			this.#documentTitle = data.documentTitle;
		}

		if (Type.isArray(data.variables))
		{
			this.#setVariables(data.variables);
		}

		if (Type.isArray(data.globalVariables))
		{
			this.#setVariables(data.globalVariables);
		}

		if (Type.isArray(data.constants))
		{
			this.#setConstants(data.constants);
		}

		if (Type.isArray(data.globalConstants))
		{
			this.#setConstants(data.globalConstants);
		}

		// todo: activity
		if (Type.isArray(data.activityResultFields))
		{
			this.#setActivityResultFields(data.activityResultFields);
		}

		if (Type.isStringFilled(data.activityResultFieldsTitle))
		{
			this.#activityResultFieldsTitle = data.activityResultFieldsTitle;
		}

		if (Type.isArray(data.triggerResultFields))
		{
			this.#setTriggerResultFields(data.triggerResultFields);
		}
	}

	#setDocumentFields(documentFields: Array<Field>)
	{
		documentFields.forEach((field) => {
			if (this.#isFileShortLinkField(field))
			{
				this.#linkFiles.push(Runtime.clone(field));

				return;
			}

			this.#documentFields.push(Runtime.clone(field));
		});
	}

	#setVariables(variables: Array<Field>)
	{
		variables.forEach((variable) => {
			this.#variables.push({
				...Runtime.clone(variable),
			});
		});
	}

	#setConstants(constants: Array<Field>)
	{
		constants.forEach((constant) => {
			this.#constants.push({
				...Runtime.clone(constant),
			});
		});
	}

	#setActivityResultFields(activities: Array<{id: string, title: string, fields: Array<Field>}>)
	{
		activities.forEach((activity) => {
			const fields = [];

			activity.fields.forEach((field) => {
				if (this.#isFileShortLinkField(field))
				{
					this.#linkFiles.push(Runtime.clone(field));

					return;
				}

				fields.push(Runtime.clone(field));
			});

			this.#activityResultFields.push({
				id: activity.id,
				title: activity.title,
				fields,
			});
		});
	}

	#setTriggerResultFields(fields: Array<Field>)
	{
		const groups = {};

		fields.forEach((field) => {
			const groupId = field.ObjectRealId;
			if (!groupId)
			{
				return;
			}

			if (!Object.hasOwn(groups, groupId))
			{
				groups[groupId] = {
					id: groupId,
					title: field.ObjectName,
					fields: [],
				};
			}

			groups[groupId].fields.push({
				...Runtime.clone(field),
			});
		});

		this.#triggerResultFields.push(...Object.values(groups));
	}

	#isFileShortLinkField(field: Field): boolean
	{
		return field.Id.endsWith('_shortlink') && field.Type === 'string';
	}

	get groupsWithChildren(): []
	{
		const documentGroup = new DocumentGroup({
			fields: this.#documentFields,
			title: this.#documentTitle,
		});

		const fileGroup = new FileGroup({
			fields: this.#linkFiles,
		});

		const variablesGroup = new VariableGroup({
			fields: this.#variables,
		});

		const constantsGroup = new ConstantGroup({
			fields: this.#constants,
		});

		const robotResultGroup = new ActivityResultGroup({
			fields: this.#activityResultFields,
			title: this.#activityResultFieldsTitle,
		});

		const triggerResultGroup = new TriggerResultGroup({
			fields: this.#triggerResultFields,
		});

		return [
			...documentGroup.groupsWithChildren,
			...fileGroup.groupsWithChildren,
			...robotResultGroup.groupsWithChildren,
			...constantsGroup.groupsWithChildren,
			...variablesGroup.groupsWithChildren,
			...triggerResultGroup.groupsWithChildren,
		];
	}

	get items(): []
	{
		const documentGroup = new DocumentGroup({
			fields: this.#documentFields,
			title: this.#documentTitle,
		});

		const fileGroup = new FileGroup({
			fields: this.#linkFiles,
		});

		const variablesGroup = new VariableGroup({
			fields: this.#variables,
		});

		const constantsGroup = new ConstantGroup({
			fields: this.#constants,
		});

		const robotResultGroup = new ActivityResultGroup({
			fields: this.#activityResultFields,
			title: this.#activityResultFieldsTitle,
		});

		const triggerResultGroup = new TriggerResultGroup({
			fields: this.#triggerResultFields,
		});

		return [
			...documentGroup.items,
			...fileGroup.items,
			...variablesGroup.items,
			...constantsGroup.items,
			...robotResultGroup.items,
			...triggerResultGroup.items,
		];
	}
}
