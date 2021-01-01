/**
 * Bitrix Messenger
 * Application model (Vuex Builder model)
 *
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2020 Bitrix
 */

import {DeviceType, DeviceOrientation} from 'im.const';
import {VuexBuilderModel} from 'ui.vue.vuex';

export class ApplicationModel extends VuexBuilderModel
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
				host: this.getVariable('common.host', location.protocol+'//'+location.host),
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
				messageExtraCount: 0,
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
				darkBackground: this.getVariable('options.darkBackground', false),
				showSmiles: false
			},
			error:
			{
				active: false,
				code: '',
				description: '',
			},
		}
	}

	getStateSaveException()
	{
		return Object.assign({
			common: this.getVariable('saveException.common', null),
			dialog: this.getVariable('saveException.dialog', null),
			mobile: this.getVariable('saveException.mobile', null),
			device: this.getVariable('saveException.device', null),
			error: this.getVariable('saveException.error', null)
		});
	}

	getActions()
	{
		return {
			set: (store, payload) =>
			{
				store.commit('set', this.validate(payload));
			},
			showSmiles: (store, payload) =>
			{
				store.commit('showSmiles')
			},
			hideSmiles: (store, payload) =>
			{
				store.commit('hideSmiles');
			}
		}
	}

	getMutations()
	{
		return {
			set: (state, payload) =>
			{
				let hasChange = false;
				for (let group in payload)
				{
					if (!payload.hasOwnProperty(group))
					{
						continue;
					}

					for (let field in payload[group])
					{
						if (!payload[group].hasOwnProperty(field))
						{
							continue;
						}

						state[group][field] = payload[group][field];
						hasChange = true;
					}
				}

				if (hasChange && this.isSaveNeeded(payload))
				{
					this.saveState(state);
				}
			},
			increaseDialogExtraCount(state, payload = {})
			{
				let {count = 1} = payload;

				state.dialog.messageExtraCount += count;
			},
			decreaseDialogExtraCount(state, payload = {})
			{
				let {count = 1} = payload;

				let newCounter = state.dialog.messageExtraCount - count;
				if (newCounter <= 0)
				{
					newCounter = 0;
				}

				state.dialog.messageExtraCount = newCounter;
			},
			clearDialogExtraCount(state)
			{
				state.dialog.messageExtraCount = 0;
			},
			showSmiles(state)
			{
				state.options.showSmiles = true;
			},
			hideSmiles(state)
			{
				state.options.showSmiles = false;
			}
		}
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

			if (typeof fields.dialog.messageExtraCount === 'number')
			{
				result.dialog.messageExtraCount = fields.dialog.messageExtraCount;
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
}