import { Core } from 'im.v2.application.core';
import { Type } from 'main.core';

export const prepareAvatar = (avatar: string): string => {
	let result = '';

	if (!avatar || avatar.endsWith('/js/im/images/blank.gif'))
	{
		result = '';
	}
	else if (avatar.startsWith('http'))
	{
		result = avatar;
	}
	else
	{
		result = Core.getHost() + avatar;
	}

	if (result)
	{
		result = encodeURI(result);
	}

	return result;
};

export const prepareDepartments = (departments: Array<number | string>): number[] => {
	const result = [];
	departments.forEach((rawDepartmentId) => {
		const departmentId = Number.parseInt(rawDepartmentId, 10);
		if (departmentId > 0)
		{
			result.push(departmentId);
		}
	});

	return result;
};

export const preparePhones = (phones): Object => {
	const result = {};

	if (Type.isStringFilled(phones.workPhone) || Type.isNumber(phones.workPhone))
	{
		result.workPhone = phones.workPhone.toString();
	}

	if (Type.isStringFilled(phones.personalMobile) || Type.isNumber(phones.personalMobile))
	{
		result.personalMobile = phones.personalMobile.toString();
	}

	if (Type.isStringFilled(phones.personalPhone) || Type.isNumber(phones.personalPhone))
	{
		result.personalPhone = phones.personalPhone.toString();
	}

	if (Type.isStringFilled(phones.innerPhone) || Type.isNumber(phones.innerPhone))
	{
		result.innerPhone = phones.innerPhone.toString();
	}

	return result;
};
