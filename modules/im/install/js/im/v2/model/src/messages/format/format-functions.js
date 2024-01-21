import { Type, type JsonObject } from 'main.core';
import { MessageComponent, type KeyboardButtonConfig } from 'im.v2.const';

import { convertToNumber } from '../../utils/format';

export const prepareComponentId = (componentId: string) => {
	const supportedComponents = Object.values(MessageComponent);
	if (!supportedComponents.includes(componentId))
	{
		return MessageComponent.unsupported;
	}

	return componentId;
};

export const prepareAuthorId = (target: number | string, currentResult: JsonObject, rawFields: JsonObject): number => {
	if (Type.isString(rawFields.system) && rawFields.system === 'Y')
	{
		return 0;
	}

	if (Type.isBoolean(rawFields.isSystem) && rawFields.isSystem === true)
	{
		return 0;
	}

	return convertToNumber(target);
};

export const prepareKeyboard = (rawKeyboardButtons: KeyboardButtonConfig[]): KeyboardButtonConfig[] => {
	return rawKeyboardButtons.map((rawButton) => {
		return {
			...rawButton,
			block: rawButton.block === 'Y',
			disabled: rawButton.disabled === 'Y',
			vote: rawButton.vote === 'Y',
			wait: rawButton.wait === 'Y',
		};
	});
};
