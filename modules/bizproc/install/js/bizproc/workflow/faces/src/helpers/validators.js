import { Type } from 'main.core';
import type { FacesData, WorkflowFacesData } from '../types/workflow-faces';

export function workflowFacesDataValidator(data: WorkflowFacesData): boolean
{
	if (!Type.isPlainObject(data))
	{
		return false;
	}

	if (!Type.isStringFilled(data.workflowId))
	{
		return false;
	}

	if (!Type.isDomNode(data.target))
	{
		return false;
	}

	if (!Type.isNil(data.targetUserId) && (!Type.isInteger(data.targetUserId) || data.targetUserId <= 0))
	{
		return false;
	}

	return validateFacesData(data.data);
}

export function validateFacesData(data: FacesData): boolean
{
	if (!Type.isPlainObject(data))
	{
		return false;
	}

	if (!Type.isArrayFilled(data.steps))
	{
		return false;
	}

	for (const step of data.steps)
	{
		if (!Type.isStringFilled(step.id) || !Type.isString(step.name) || !Type.isArray(step.avatars))
		{
			return false;
		}

		const duration = step.duration;
		if (
			!Type.isString(duration)
			&& (!Type.isNumber(duration) || duration < 0)
		)
		{
			return false;
		}
	}

	return true;
}
