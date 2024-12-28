import { Type } from 'main.core';
import {
	headerTypeEnum,
	footerTypeEnum,
	imageTypeEnum,
	stackStatusEnum,
} from '../image-stack-steps-options';
import type {
	StepType,
	HeaderType,
	FooterType,
	ImageType,
	StackStatusType,
	StackType,
	FooterDurationType,
	IconType,
} from '../image-stack-steps-options';

export function validateStep(data: StepType): boolean
{
	if (!Type.isStringFilled(data.id))
	{
		// eslint-disable-next-line no-console
		console.warn('UI.Image-Stack-Steps: StepData.id must be filled string');

		return false;
	}

	if (!Type.isUndefined(data.progressBox))
	{
		if (!Type.isPlainObject(data.progressBox))
		{
			// eslint-disable-next-line no-console
			console.warn('UI.Image-Stack-Steps: StepData.progressBox must be plain object');
		}

		if (!Type.isString(data.progressBox.title))
		{
			// eslint-disable-next-line no-console
			console.warn('UI.Image-Stack-Steps: StepData.progressBox.title must be string');

			return false;
		}
	}

	return (validateHeader(data.header) && validateStack(data.stack) && validateFooter(data.footer));
}

export function validateHeader(data: HeaderType): boolean
{
	if (Type.isNil(data))
	{
		return true;
	}

	if (!Type.isPlainObject(data))
	{
		// eslint-disable-next-line no-console
		console.warn('UI.Image-Stack-Steps: StepData.header must be plain object');

		return false;
	}

	if (!Object.values(headerTypeEnum).includes(data.type))
	{
		// eslint-disable-next-line no-console
		console.warn('UI.Image-Stack-Steps: StepData.header.type must be one of headerTypeEnum values');

		return false;
	}

	if (data.type === headerTypeEnum.TEXT)
	{
		if (Type.isString(data.data?.text))
		{
			return true;
		}

		// eslint-disable-next-line no-console
		console.warn(
			'UI.Image-Stack-Steps: '
			+ 'StepData.header with type "text" must contain HeaderData.text; '
			+ 'HeaderData.text must be string',
		);

		return false;
	}

	return data.type === headerTypeEnum.STUB;
}

export function validateFooter(data: FooterType): boolean
{
	if (Type.isNil(data))
	{
		return true;
	}

	if (!Type.isPlainObject(data))
	{
		// eslint-disable-next-line no-console
		console.warn('UI.Image-Stack-Steps: StepData.footer must be plain object');

		return false;
	}

	if (data.type === footerTypeEnum.TEXT)
	{
		if (Type.isString(data.data?.text))
		{
			return true;
		}

		// eslint-disable-next-line no-console
		console.warn(
			'UI.Image-Stack-Steps: '
			+ 'StepData.footer with type "text" must contain FooterData.text; '
			+ 'FooterData.text must be string',
		);

		return false;
	}

	if (data.type === footerTypeEnum.DURATION)
	{
		return validateFooterDuration(data.data);
	}

	return data.type === footerTypeEnum.STUB;
}

export function validateStack(data: StackType): boolean
{
	if (!Type.isPlainObject(data))
	{
		// eslint-disable-next-line no-console
		console.warn('UI.Image-Stack-Steps: StepData.stack must be plain object');

		return false;
	}

	if (!Type.isUndefined(data.status))
	{
		if (!Type.isPlainObject(data.status))
		{
			// eslint-disable-next-line no-console
			console.warn('UI.Image-Stack-Steps: StackData.status must be plain object');

			return false;
		}

		if (!validateStatus(data.status))
		{
			return false;
		}
	}

	if (!Type.isArrayFilled(data.images))
	{
		// eslint-disable-next-line no-console
		console.warn('UI.Image-Stack-Steps: StackData.images must be filled array');

		return false;
	}

	for (const image of data.images)
	{
		if (!validateImage(image))
		{
			return false;
		}
	}

	return true;
}

export function validateImage(data: ImageType): boolean
{
	if (!Type.isPlainObject(data))
	{
		return false;
	}

	if (
		data.type === imageTypeEnum.IMAGE
		&& Type.isString(data.data?.src)
		&& (Type.isUndefined(data.data?.title) || Type.isStringFilled(data.data?.title))
	)
	{
		return true;
	}

	if (
		data.type === imageTypeEnum.USER
		&& Type.isString(data.data?.src)
		&& Type.isNumber(data.data?.userId)
		&& data.data.userId > 0
	)
	{
		return true;
	}

	if (data.type === imageTypeEnum.ICON && validateIcon(data.data))
	{
		return true;
	}

	if (data.type === imageTypeEnum.USER_STUB || data.type === imageTypeEnum.IMAGE_STUB)
	{
		return true;
	}

	if (
		data.type === imageTypeEnum.COUNTER
		&& Type.isStringFilled(data.data?.text)
	)
	{
		return true;
	}

	// eslint-disable-next-line no-console
	console.warn('UI.Image-Stack-Steps: StackData.data must be correct', data);

	return false;
}

export function validateStatus(data: StackStatusType): boolean
{
	if (data.type === stackStatusEnum.CUSTOM && (!validateIcon(data.data)))
	{
		// eslint-disable-next-line no-console
		console.warn('UI.Image-Stack-Steps: StackData.status with type "custom" must be correct', data);

		return false;
	}

	if (Object.values(stackStatusEnum).includes(data.type))
	{
		return true;
	}

	// eslint-disable-next-line no-console
	console.warn('UI.Image-Stack-Steps: StackData.status must be correct', data);

	return false;
}

export function validateFooterDuration(data: FooterDurationType): boolean
{
	if (!Type.isNumber(data.duration) || data.duration < 0)
	{
		// eslint-disable-next-line no-console
		console.warn('UI.Image-Stack-Steps: FooterDurationData.duration must be not negative number');

		return false;
	}

	if (!Type.isBoolean(data.realtime))
	{
		// eslint-disable-next-line no-console
		console.warn('UI.Image-Stack-Steps: FooterDurationData.realtime must be boolean');

		return false;
	}

	if (
		data.realtime === true
		&& !Type.isUndefined(data.realtimeBoundary)
		&& (
			!Type.isNumber(data.realtimeBoundary) || data.realtimeBoundary <= 0
		)
	)
	{
		// eslint-disable-next-line no-console
		console.warn('UI.Image-Stack-Steps: FooterDurationData.realtimeBoundary must be positive integer');

		return false;
	}

	if (!Type.isUndefined(data.format) && !(Type.isString(data.format) || Type.isArray(data.format)))
	{
		// eslint-disable-next-line no-console
		console.warn('UI.Image-Stack-Steps: FooterDurationData.format must be array or string');

		return false;
	}

	return true;
}

export function validateIcon(data: IconType): boolean
{
	return Type.isPlainObject(data) && Type.isStringFilled(data.icon) && Type.isStringFilled(data.color);
}
