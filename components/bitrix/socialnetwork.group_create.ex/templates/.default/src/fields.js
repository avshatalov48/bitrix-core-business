import { Type, Loc } from 'main.core';
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

	static check()
	{
		if (WorkgroupForm.getInstance().wizardManager.stepsCount === 1)
		{
			return this.checkAll();
		}
		else
		{
			return this.checkStep(WorkgroupForm.getInstance().wizardManager.currentStep);
		}
	}

	static checkStep(step)
	{
		step = parseInt(step);

		const errorDataList = [];

		if (Type.isArray(this.mandatoryFieldsByStep[step]))
		{
			this.mandatoryFieldsByStep[step].forEach((fieldData) => {

				let fieldNode = document.getElementById(fieldData.id);
				if (!Type.isDomNode(fieldNode))
				{
					return;
				}

				if (fieldNode.tagName.toLowerCase() !== 'input')
				{
					if (fieldData.type === 'string')
					{
						fieldNode = fieldNode.querySelector('input[type="text"]');
						if (!Type.isDomNode(fieldNode))
						{
							return;
						}
					}
				}

				fieldData.fieldNode = fieldNode;
				const errorText = this.checkField(fieldData);
				if (Type.isStringFilled(errorText))
				{
					const bindNode = document.getElementById(fieldData.bindNodeId)
					errorDataList.push({
						bindNode: (Type.isDomNode(bindNode) ? bindNode : fieldNode),
						message: errorText,
					});
				}
			});
		}

		return errorDataList;
	}

	static checkAll()
	{
		let errorDataList = [];
		Object.entries(this.mandatoryFieldsByStep).forEach((stepData) => {
			errorDataList = errorDataList.concat(this.checkStep(parseInt(stepData[0])));
		});

		return errorDataList;
	}

	static checkField(fieldData)
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
		switch (fieldType)
		{
			case 'string':
				errorText = (fieldNode.value.trim() === '' ? Loc.getMessage('SONET_GCE_T_STRING_FIELD_ERROR') : '');
				break
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
}
