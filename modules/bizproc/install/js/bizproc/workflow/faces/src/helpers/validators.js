import { Text, Type } from 'main.core';
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

	if (!Type.isInteger(data.targetUserId) || data.targetUserId <= 0)
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

	// avatars
	const avatars = data.avatars;
	if (
		!Type.isPlainObject(avatars)
		|| !Type.isArrayFilled(avatars.author)
		|| !Type.isArray(avatars.running)
		|| !Type.isArray(avatars.completed)
		|| !Type.isArray(avatars.done)
	)
	{
		return false;
	}

	// statuses
	const statuses = data.statuses;
	if (!Type.isPlainObject(statuses))
	{
		return false;
	}

	// time
	const time = data.time;
	if (
		!Type.isPlainObject(time)
		|| !timeValidator(time.author)
		|| !timeValidator(time.running)
		|| !timeValidator(time.completed)
		|| !timeValidator(time.done)
		|| !timeValidator(time.total)
	)
	{
		return false;
	}

	return true;
}

const timeValidator = (time) => {
	return (Type.isNull(time) || time === 0 || Text.toInteger(time) > 0);
};
