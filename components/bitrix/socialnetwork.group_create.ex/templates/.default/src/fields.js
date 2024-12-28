import { Type, Loc, ajax } from 'main.core';
import { WorkgroupForm } from './index';

export class FieldsManager
{
	static mandatoryFieldsByStep = {
		2: [
			{
				id: 'GROUP_NAME_input',
				type: 'string',
				bindNodeId: 'GROUP_NAME_input',
			},
		],
		4: [
			{
				id: 'SCRUM_MASTER_CODE_container',
				type: 'input_hidden_container',
				bindNodeId: 'SCRUM_MASTER_selector',
				condition: () => {
					return !!WorkgroupForm.getInstance().scrumManager.isScrumProject;
				}
			}
		],
	};

	static async check()
	{
		if (WorkgroupForm.getInstance().wizardManager.stepsCount === 1)
		{
			return await this.checkAll();
		}

		return await this.checkStep(WorkgroupForm.getInstance().wizardManager.currentStep);
	}

	static async checkStep(step)
	{
		step = parseInt(step);

		const errorDataList = [];

		if (Type.isArray(this.mandatoryFieldsByStep[step]))
		{
			for (const fieldData of this.mandatoryFieldsByStep[step])
			{

				let fieldNode = document.getElementById(fieldData.id);
				if (!Type.isDomNode(fieldNode))
				{
					continue;
				}

				if (fieldNode.tagName.toLowerCase() !== 'input')
				{
					if (fieldData.type === 'string')
					{
						fieldNode = fieldNode.querySelector('input[type="text"]');
						if (!Type.isDomNode(fieldNode))
						{
							continue;
						}
					}
				}

				fieldData.fieldNode = fieldNode;
				// eslint-disable-next-line no-await-in-loop
				const errorText = await this.checkField(fieldData);
				if (Type.isStringFilled(errorText))
				{
					const bindNode = document.getElementById(fieldData.bindNodeId)
					errorDataList.push({
						bindNode: (Type.isDomNode(bindNode) ? bindNode : fieldNode),
						message: errorText,
					});
				}
			}
		}

		return errorDataList;
	}

	static async checkAll()
	{
		let errorDataList = [];
		for (const stepData of Object.entries(this.mandatoryFieldsByStep))
		{
			errorDataList = errorDataList.concat(await this.checkStep(parseInt(stepData[0])));
		}

		return errorDataList;
	}

	static async checkField(fieldData)
	{
		let errorText = '';

		if (
			!Type.isPlainObject(fieldData)
			&& !Type.isDomNode(fieldData.fieldNode)
		)
		{
			return errorText;
		}

		if (Type.isFunction(fieldData.condition))
		{
			if (!fieldData.condition())
			{
				return errorText;
			}
		}

		const fieldNode = fieldData.fieldNode;
		const fieldType = (Type.isStringFilled(fieldData.type) ? fieldData.type : 'string');
		const fieldId = fieldData.id;
		const groupId = WorkgroupForm.getInstance()?.groupId;
		const type = WorkgroupForm.getInstance()?.selectedProjectType;

		switch (fieldType)
		{
			case 'string':
				if (fieldNode.value.trim() === '')
				{
					errorText = Loc.getMessage('SONET_GCE_T_STRING_FIELD_ERROR');
					break;
				}

				if (groupId <= 0 && fieldId === 'GROUP_NAME_input')
				{
					const exists = await FieldsManager.checkSameGroupExists(fieldNode.value);
					if (exists)
					{
						errorText = type === 'project'
							? Loc.getMessage('SONET_GCE_T_GROUP_NAME_EXISTS_PROJECT')
							: Loc.getMessage('SONET_GCE_T_GROUP_NAME_EXISTS');
					}

					break;
				}

				errorText = '';
				break;

			case 'input_hidden_container':
				let empty = true;
				fieldNode.querySelectorAll('input[type="hidden"]').forEach((hiddenNode) => {
					if (!empty)
					{
						return;
					}

					if (Type.isStringFilled(hiddenNode.value))
					{
						empty = false;
					}
				});
				errorText = (empty ? Loc.getMessage('SONET_GCE_T_STRING_FIELD_ERROR') : '');
				break
			default:
				errorText = '';
		}

		return errorText;
	}

	static showError(errorData)
	{
		if (
			!Type.isPlainObject(errorData)
			|| !Type.isStringFilled(errorData.message)
			|| !Type.isDomNode(errorData.bindNode)
		)
		{
			return;
		}

		WorkgroupForm.getInstance().alertManager.showAlert(errorData.message, errorData.bindNode.parentNode);
	}

	static async checkSameGroupExists(groupName: string)
	{
		const response = await ajax.runAction(
			'socialnetwork.api.workgroup.isExistingGroup',
			{
				data: {
					name: groupName,
				},
			},
		);

		return response?.data?.exists;
	}
}
