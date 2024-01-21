import { Type } from 'main.core';

import { Settings } from 'im.v2.const';

import { convertToNumber, isNumberOrString } from '../../../utils/format';
import { prepareNotificationSettings } from './format-functions';

import type { FieldsConfig } from '../../../utils/validate';

export const settingsFieldsConfig: FieldsConfig = [
	{
		fieldName: Settings.notification.enableSound,
		targetFieldName: Settings.notification.enableSound,
		checkFunction: Type.isBoolean,
	},
	{
		fieldName: Settings.notification.enableAutoRead,
		targetFieldName: Settings.notification.enableAutoRead,
		checkFunction: Type.isBoolean,
	},
	{
		fieldName: Settings.notification.mode,
		targetFieldName: Settings.notification.mode,
		checkFunction: Type.isString,
	},
	{
		fieldName: Settings.notification.enableWeb,
		targetFieldName: Settings.notification.enableWeb,
		checkFunction: Type.isBoolean,
	},
	{
		fieldName: Settings.notification.enableMail,
		targetFieldName: Settings.notification.enableMail,
		checkFunction: Type.isBoolean,
	},
	{
		fieldName: Settings.notification.enablePush,
		targetFieldName: Settings.notification.enablePush,
		checkFunction: Type.isBoolean,
	},
	{
		fieldName: 'notifications',
		targetFieldName: 'notifications',
		checkFunction: Type.isArray,
		formatFunction: prepareNotificationSettings,
	},
	{
		fieldName: Settings.message.bigSmiles,
		targetFieldName: Settings.message.bigSmiles,
		checkFunction: Type.isBoolean,
	},
	{
		fieldName: Settings.appearance.background,
		targetFieldName: Settings.appearance.background,
		checkFunction: isNumberOrString,
		formatFunction: convertToNumber,
	},
	{
		fieldName: Settings.appearance.alignment,
		targetFieldName: Settings.appearance.alignment,
		checkFunction: Type.isString,
	},
	{
		fieldName: Settings.recent.showBirthday,
		targetFieldName: Settings.recent.showBirthday,
		checkFunction: Type.isBoolean,
	},
	{
		fieldName: Settings.recent.showInvited,
		targetFieldName: Settings.recent.showInvited,
		checkFunction: Type.isBoolean,
	},
	{
		fieldName: Settings.recent.showLastMessage,
		targetFieldName: Settings.recent.showLastMessage,
		checkFunction: Type.isBoolean,
	},
	{
		fieldName: Settings.hotkey.sendByEnter,
		targetFieldName: Settings.hotkey.sendByEnter,
		checkFunction: Type.isString,
		formatFunction: (target) => {
			return target === '1';
		},
	},
	{
		fieldName: Settings.hotkey.sendByEnter,
		targetFieldName: Settings.hotkey.sendByEnter,
		checkFunction: Type.isBoolean,
	},
	{
		fieldName: Settings.desktop.enableRedirect,
		targetFieldName: Settings.desktop.enableRedirect,
		checkFunction: Type.isBoolean,
	},
	{
		fieldName: Settings.user.status,
		targetFieldName: Settings.user.status,
		checkFunction: Type.isString,
	},
];
