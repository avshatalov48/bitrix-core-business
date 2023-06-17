import {Type} from 'main.core';
import {BuilderModel} from 'ui.vue3.vuex';

import {DeviceType, DeviceOrientation, Settings} from 'im.old-chat-embedding.const';

export class ApplicationModel extends BuilderModel
{
	getName()
	{
		return 'application';
	}

	getState()
	{
		return {
			common:
			{
				host: this.getVariable('common.host', `${location.protocol}//${location.host}`),
				siteId: this.getVariable('common.siteId', 'default'),
				userId: this.getVariable('common.userId', 0),
				languageId: this.getVariable('common.languageId', 'en'),
			},
			dialog:
			{
				dialogId: this.getVariable('dialog.dialogId', '0'),
				chatId: this.getVariable('dialog.chatId', 0),
				diskFolderId: this.getVariable('dialog.diskFolderId', 0),
				messageLimit: this.getVariable('dialog.messageLimit', 20),
				enableReadMessages: this.getVariable('dialog.enableReadMessages', true),
			},
			disk:
			{
				enabled: false,
				maxFileSize: 5242880,
			},
			call:
			{
				serverEnabled: false,
				maxParticipants: 24,
			},
			mobile:
			{
				keyboardShow: false,
			},
			device:
			{
				type: this.getVariable('device.type', DeviceType.desktop),
				orientation: this.getVariable('device.orientation', DeviceOrientation.portrait),
			},
			options:
			{
				quoteEnable: this.getVariable('options.quoteEnable', true),
				quoteFromRight: this.getVariable('options.quoteFromRight', true),
				autoplayVideo: this.getVariable('options.autoplayVideo', true),
				darkTheme: this.getVariable('options.darkTheme', false),
				bigSmileEnable: this.getVariable('options.bigSmileEnable', true),
			},
			error:
			{
				active: false,
				code: '',
				description: '',
			},
		};
	}

	getGetters()
	{
		return {
			getOption: state => (optionName: string) =>
			{
				if (!Settings[optionName])
				{
					return false;
				}

				return state.options[optionName];
			},
		};
	}

	getActions()
	{
		return {
			setOptions: (store, payload) =>
			{
				if (!Type.isPlainObject(payload))
				{
					return false;
				}

				payload = this.validateOptions(payload);
				Object.entries(payload).forEach(([option, value]) => {
					store.commit('setOptions', {
						option,
						value
					});
				});
			}
		};
	}

	getMutations()
	{
		return {
			update: (state, payload) => {
				Object.keys(payload).forEach((group) => {
					Object.entries(payload[group]).forEach(([key, value]) => {
						state[group][key] = value;
					});
				});
			},
			setOptions: (state, payload) => {
				state.options[payload.option] = payload.value;
			}
		};
	}

	validate(fields)
	{
		const result = {};

		if (typeof fields.common === 'object' && fields.common)
		{
			result.common = {};

			if (typeof fields.common.userId === 'number')
			{
				result.common.userId = fields.common.userId;
			}

			if (typeof fields.common.languageId === 'string')
			{
				result.common.languageId = fields.common.languageId;
			}
		}

		if (typeof fields.dialog === 'object' && fields.dialog)
		{
			result.dialog = {};

			if (typeof fields.dialog.dialogId === 'number')
			{
				result.dialog.dialogId = fields.dialog.dialogId.toString();
				result.dialog.chatId = 0;
			}
			else if (typeof fields.dialog.dialogId === 'string')
			{
				result.dialog.dialogId = fields.dialog.dialogId;

				if (typeof fields.dialog.chatId !== 'number')
				{
					let chatId = fields.dialog.dialogId;
					if (chatId.startsWith('chat'))
					{
						chatId = fields.dialog.dialogId.substr(4);
					}

					chatId = parseInt(chatId);

					result.dialog.chatId = !isNaN(chatId)? chatId: 0;
					fields.dialog.chatId = result.dialog.chatId;
				}
			}

			if (typeof fields.dialog.chatId === 'number')
			{
				result.dialog.chatId = fields.dialog.chatId;
			}

			if (typeof fields.dialog.diskFolderId === 'number')
			{
				result.dialog.diskFolderId = fields.dialog.diskFolderId;
			}

			if (typeof fields.dialog.messageLimit === 'number')
			{
				result.dialog.messageLimit = fields.dialog.messageLimit;
			}

			if (typeof fields.dialog.enableReadMessages === 'boolean')
			{
				result.dialog.enableReadMessages = fields.dialog.enableReadMessages;
			}
		}

		if (typeof fields.disk === 'object' && fields.disk)
		{
			result.disk = {};

			if (typeof fields.disk.enabled === 'boolean')
			{
				result.disk.enabled = fields.disk.enabled;
			}

			if (typeof fields.disk.maxFileSize === 'number')
			{
				result.disk.maxFileSize = fields.disk.maxFileSize;
			}
		}

		if (typeof fields.call === 'object' && fields.call)
		{
			result.call = {};

			if (typeof fields.call.serverEnabled === 'boolean')
			{
				result.call.serverEnabled = fields.call.serverEnabled;
			}

			if (typeof fields.call.maxParticipants === 'number')
			{
				result.call.maxParticipants = fields.call.maxParticipants;
			}
		}

		if (typeof fields.mobile === 'object' && fields.mobile)
		{
			result.mobile = {};

			if (typeof fields.mobile.keyboardShow === 'boolean')
			{
				result.mobile.keyboardShow = fields.mobile.keyboardShow;
			}
		}

		if (typeof fields.device === 'object' && fields.device)
		{
			result.device = {};

			if (typeof fields.device.type === 'string' && typeof DeviceType[fields.device.type] !== 'undefined')
			{
				result.device.type = fields.device.type;
			}

			if (typeof fields.device.orientation === 'string' && typeof DeviceOrientation[fields.device.orientation] !== 'undefined')
			{
				result.device.orientation = fields.device.orientation;
			}
		}

		if (typeof fields.error === 'object' && fields.error)
		{
			if (typeof fields.error.active === 'boolean')
			{
				result.error = {
					active: fields.error.active,
					code: fields.error.code.toString() || '',
					description: fields.error.description.toString() || '',
				};
			}
		}

		return result;
	}

	validateOptions(fields)
	{
		const result = {};

		if (!Type.isUndefined(fields.darkTheme) && Type.isStringFilled(fields.darkTheme))
		{
			if (fields.darkTheme === 'auto' && BX.MessengerProxy)
			{
				result.darkTheme = BX.MessengerProxy.isDarkTheme();
			}
			else
			{
				result.darkTheme = fields.darkTheme === 'dark';
			}
		}

		return result;
	}
}