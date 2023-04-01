import {Type, Loc} from 'main.core';
import {BuilderModel} from 'ui.vue3.vuex';
import {ChatTypes, MessageStatus, RecentCallStatus, RecentSettings} from 'im.v2.const';
import {Utils} from 'im.v2.lib.utils';

export class RecentModel extends BuilderModel
{
	getName()
	{
		return 'recent';
	}

	getState()
	{
		return {
			collection: {},
			activeCalls: [],
			options: {
				showBirthday: true,
				showInvited: true,
				showLastMessage: true
			}
		};
	}

	getElementState()
	{
		return {
			dialogId: '0',
			message: {
				id: 0,
				text: '',
				date: new Date(),
				senderId: 0,
				status: MessageStatus.received
			},
			draft: {
				text: '',
				date: null
			},
			unread: false,
			pinned: false,
			liked: false,
			invitation: {
				isActive: false,
				originator: 0,
				canResend: false
			},
			options: {}
		};
	}

	getActiveCallDefaultState()
	{
		return {
			dialogId: 0,
			name: '',
			call: {},
			state: RecentCallStatus.waiting
		};
	}

	getGetters()
	{
		return {
			getCollection: (state): Object[] =>
			{
				return Object.values(state.collection);
			},
			getSortedCollection: (state): Object[] =>
			{
				const collectionAsArray = Object.values(state.collection).filter(item => {
					const isBirthdayPlaceholder = item.options.birthdayPlaceholder;
					const isInvitedUser = item.options.defaultUserRecord;

					return !isBirthdayPlaceholder && !isInvitedUser && item.message.id;
				});

				return [...collectionAsArray].sort((a, b) => {
					return b.message.date - a.message.date;
				});
			},
			get: (state) => (dialogId: string): Object | null =>
			{
				if (Type.isNumber(dialogId))
				{
					dialogId = dialogId.toString();
				}

				if (state.collection[dialogId])
				{
					return state.collection[dialogId];
				}

				return null;
			},

			getItemText: (state) => (dialogId): string =>
			{
				const currentItem = state.collection[dialogId];
				if (!currentItem)
				{
					return '';
				}

				let result = currentItem.message.text;
				// system mention (get current name from model, otherwise - from code)
				result = result.replace(/\[user=(\d+) replace](.*?)\[\/user]/gi, (match, userId, userName) => {
					const user = this.store.getters['users/get'](userId);
					return user ? user.name : userName;
				});

				result = result.replace(/\[user=(\d+)]\[\/user]/gi, (match, userId) => {
					const user = this.store.getters['users/get'](userId);
					return user ? user.name : match;
				});

				// custom mention (keep name as it is)
				return result.replace(/\[user=(\d+)](.+?)\[\/user]/gi, '$2');
			},

			needsBirthdayPlaceholder: (state) => (dialogId): boolean =>
			{
				const currentItem = state.collection[dialogId];
				if (!currentItem)
				{
					return false;
				}

				const dialog = this.store.getters['dialogues/get'](dialogId);
				if (!dialog || dialog.type !== ChatTypes.user)
				{
					return false;
				}
				const hasBirthday = this.store.getters['users/hasBirthday'](dialogId);
				if (!hasBirthday)
				{
					return false;
				}

				const hasTodayMessage = currentItem.message.id > 0 && Utils.date.isToday(currentItem.message.date);

				return state.options.showBirthday && !hasTodayMessage && dialog.counter === 0;
			},

			getMessageDate: (state) => (dialogId): Date | null =>
			{
				const currentItem = state.collection[dialogId];
				if (!currentItem)
				{
					return null;
				}

				if (Type.isDate(currentItem.draft.date) && currentItem.draft.date > currentItem.message.date)
				{
					return currentItem.draft.date;
				}

				const needsBirthdayPlaceholder = this.store.getters['recent/needsBirthdayPlaceholder'](currentItem.dialogId);
				if (needsBirthdayPlaceholder)
				{
					return Utils.date.getStartOfTheDay();
				}

				return currentItem.message.date;
			},

			hasActiveCall: (state): boolean =>
			{
				return state.activeCalls.some(item => item.state === RecentCallStatus.joined);
			},

			getOption: (state) => (optionName: string): boolean =>
			{
				if (!RecentSettings[optionName])
				{
					return false;
				}

				return state.options[optionName];
			}
		};
	}

	getActions()
	{
		return {
			set: (store, payload: Array | Object) =>
			{
				if (!Array.isArray(payload) && Type.isPlainObject(payload))
				{
					payload = [payload];
				}

				const itemsToUpdate = [];
				const itemsToAdd = [];
				payload.map(element => {
					return this.validate(element);
				}).forEach(element => {
					const existingItem = store.state.collection[element.dialogId];
					if (existingItem)
					{
						itemsToUpdate.push({id: existingItem.dialogId, fields: {...element}});
					}
					else
					{
						itemsToAdd.push({...this.getElementState(), ...element});
					}
				});

				if (itemsToAdd.length > 0)
				{
					store.commit('add', itemsToAdd);
				}
				if (itemsToUpdate.length > 0)
				{
					store.commit('update', itemsToUpdate);
				}
			},

			update: (store, payload: {id: string | number, fields: Object}) =>
			{
				const existingItem = store.state.collection[payload.id];
				if (!existingItem)
				{
					return false;
				}

				store.commit('update', {
					id: existingItem.dialogId,
					fields: this.validate(payload.fields)
				});
			},

			unread: (store, payload: {id: string | number, action: boolean}) =>
			{
				const existingItem = store.state.collection[payload.id];
				if (!existingItem)
				{
					return false;
				}

				store.commit('update', {
					id: existingItem.dialogId,
					fields: {unread: payload.action}
				});
			},

			pin: (store, payload: {id: string | number, action: boolean}) =>
			{
				const existingItem = store.state.collection[payload.id];
				if (!existingItem)
				{
					return false;
				}

				store.commit('update', {
					id: existingItem.dialogId,
					fields: {pinned: payload.action}
				});
			},

			like: (store, payload: {id: string | number, messageId: number, liked: boolean}) =>
			{
				const existingItem = store.state.collection[payload.id];
				if (!existingItem)
				{
					return false;
				}

				const isLastMessage = existingItem.message.id === Number.parseInt(payload.messageId, 10);
				const isExactMessageLiked = !Type.isUndefined(payload.messageId) && payload.liked === true;
				if (isExactMessageLiked && !isLastMessage)
				{
					return false;
				}

				store.commit('update', {
					id: existingItem.dialogId,
					fields: {liked: payload.liked === true}
				});
			},

			draft: (store, payload: {id: string | number, text: string}) =>
			{
				const dialog = this.store.getters['dialogues/get'](payload.id);
				if (!dialog)
				{
					return false;
				}

				let existingItem = store.state.collection[payload.id];
				if (!existingItem)
				{
					if (payload.text === '')
					{
						return false;
					}
					const newItem = {
						dialogId: payload.id.toString(),
					};
					store.commit('add', {...this.getElementState(), ...newItem});
					existingItem = store.state.collection[payload.id];
				}

				const fields = this.validate({draft: {text: payload.text.toString()}});
				if (fields.draft.text === existingItem.draft.text)
				{
					return false;
				}

				store.commit('update', {
					id: existingItem.dialogId,
					fields
				});
			},

			delete: (store, payload: {id: string | number}) =>
			{
				const existingItem = store.state.collection[payload.id];
				if (!existingItem)
				{
					return false;
				}

				store.commit('delete', {
					id: existingItem.dialogId
				});
			},

			addActiveCall: (store, payload) =>
			{
				const existingIndex = store.state.activeCalls.findIndex(item => {
					return item.dialogId === payload.dialogId || item.call.id === payload.call.id;
				});

				if (existingIndex > -1)
				{
					store.commit('updateActiveCall', {
						index: existingIndex,
						fields: this.validateActiveCall(payload)
					});

					return true;
				}

				store.commit('addActiveCall', this.prepareActiveCall(payload));
			},

			updateActiveCall: (store, payload) =>
			{
				const existingIndex = store.state.activeCalls.findIndex(item => {
					return item.dialogId === payload.dialogId;
				});

				store.commit('updateActiveCall', {
					index: existingIndex,
					fields: this.validateActiveCall(payload.fields)
				});
			},

			deleteActiveCall: (store, payload) =>
			{
				const existingIndex = store.state.activeCalls.findIndex(item => {
					return item.dialogId === payload.dialogId;
				});

				if (existingIndex === -1)
				{
					return false;
				}

				store.commit('deleteActiveCall', {
					index: existingIndex
				});
			},

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
			add: (state, payload: Object[] | Object) => {
				if (!Array.isArray(payload) && Type.isPlainObject(payload))
				{
					payload = [payload];
				}
				payload.forEach(item => {
					state.collection[item.dialogId] = item;
				});
			},

			update: (state, payload: Object[] | Object) => {
				if (!Array.isArray(payload) && Type.isPlainObject(payload))
				{
					payload = [payload];
				}
				payload.forEach(({id, fields}) => {
					// if we already got chat - we should not update it with default user chat (unless it's an accepted invitation)
					const defaultUserElement = fields.options && fields.options.defaultUserRecord && !fields.invitation;
					if (defaultUserElement)
					{
						return false;
					}

					const currentElement = state.collection[id];
					fields.message = {...currentElement.message, ...fields.message};
					fields.options = {...currentElement.options, ...fields.options};
					state.collection[id] = {
						...currentElement,
						...fields
					};
				});
			},

			delete: (state, payload: {id: string}) => {
				delete state.collection[payload.id];
			},

			addActiveCall: (state, payload) => {
				state.activeCalls.push(payload);
			},

			updateActiveCall: (state, payload) => {
				state.activeCalls[payload.index] = {
					...state.activeCalls[payload.index],
					...payload.fields
				};
			},

			deleteActiveCall: (state, payload) => {
				state.activeCalls.splice(payload.index, 1);
			},

			setOptions: (state, payload) => {
				state.options[payload.option] = payload.value;
			}
		};
	}

	validate(fields: Object)
	{
		const result = {
			options: {}
		};

		if (Type.isNumber(fields.id))
		{
			result.dialogId = fields.id.toString();
		}
		if (Type.isStringFilled(fields.id))
		{
			result.dialogId = fields.id;
		}

		if (Type.isNumber(fields.dialogId))
		{
			result.dialogId = fields.dialogId.toString();
		}
		if (Type.isStringFilled(fields.dialogId))
		{
			result.dialogId = fields.dialogId;
		}

		if (Type.isPlainObject(fields.message))
		{
			result.message = this.prepareMessage(fields);
		}

		if (Type.isPlainObject(fields.draft))
		{
			result.draft = this.prepareDraft(fields);
		}

		if (Type.isBoolean(fields.unread))
		{
			result.unread = fields.unread;
		}

		if (Type.isBoolean(fields.pinned))
		{
			result.pinned = fields.pinned;
		}

		if (Type.isBoolean(fields.liked))
		{
			result.liked = fields.liked;
		}

		if (Type.isPlainObject(fields.invited))
		{
			result.invitation = {
				isActive: true,
				originator: fields.invited.originator_id,
				canResend: fields.invited.can_resend
			};
			result.options.defaultUserRecord = true;
		}
		else if (fields.invited === false)
		{
			result.invitation = {
				isActive: false,
				originator: 0,
				canResend: false
			};
			result.options.defaultUserRecord = true;
		}

		if (Type.isPlainObject(fields.options))
		{
			if (!result.options)
			{
				result.options = {};
			}

			if (Type.isBoolean(fields.options.default_user_record))
			{
				fields.options.defaultUserRecord = fields.options.default_user_record;
			}

			if (Type.isBoolean(fields.options.defaultUserRecord))
			{
				result.options.defaultUserRecord = fields.options.defaultUserRecord;
			}

			if (Type.isBoolean(fields.options.birthdayPlaceholder))
			{
				result.options.birthdayPlaceholder = fields.options.birthdayPlaceholder;
			}
		}

		return result;
	}

	prepareChatType(fields: Object): string
	{
		if (fields.type === ChatTypes.user)
		{
			return ChatTypes.user;
		}

		if (fields.chat)
		{
			return fields.chat.type;
		}

		return fields.type;
	}

	prepareMessage(fields: Object): Object
	{
		const {message} = this.getElementState();
		if (Type.isNumber(fields.message.id))
		{
			message.id = fields.message.id;
		}
		if (Type.isString(fields.message.text))
		{
			const textOptions = {};
			if (fields.message.withAttach || fields.message.attach)
			{
				textOptions.WITH_ATTACH = true;
			}
			else if (fields.message.withFile || fields.message.file)
			{
				textOptions.WITH_FILE = true;
			}
			message.text = this.prepareText(fields.message.text, textOptions);
		}

		if (Type.isDate(fields.message.date) || Type.isString(fields.message.date))
		{
			message.date = Utils.date.cast(fields.message.date);
		}

		if (Type.isNumber(fields.message.author_id))
		{
			message.senderId = fields.message.author_id;
		}
		if (Type.isNumber(fields.message.senderId))
		{
			message.senderId = fields.message.senderId;
		}
		if (Type.isStringFilled(fields.message.status))
		{
			message.status = fields.message.status;
		}

		return message;
	}

	prepareDraft(fields: Object): Object
	{
		const {draft} = this.getElementState();

		if (Type.isString(fields.draft.text))
		{
			draft.text = this.prepareText(fields.draft.text, {});
		}

		if (Type.isStringFilled(draft.text))
		{
			draft.date = new Date();
		}
		else
		{
			draft.date = null;
		}

		return draft;
	}

	prepareText(text: string, options: Object): string
	{
		let result = text.trim();

		if (result.startsWith('/me'))
		{
			result = result.slice(4);
		}
		else if (result.startsWith('/loud'))
		{
			result = result.slice(6);
		}

		result = result.replace(/<br><br \/>/gi, '<br />');
		result = result.replace(/<br \/><br>/gi, '<br />');

		const codeReplacement = [];
		result = result.replace(/\[code]\n?([\0-\uFFFF]*?)\[\/code]/gi, (whole, group) => {
			const id = codeReplacement.length;
			codeReplacement.push(group);
			return `####REPLACEMENT_CODE_${id}####`;
		});

		result = result.replace(/\[put(?:=.+?)?](?:.+?)?\[\/put]/gi, (match) => {
			return match.replace(/\[put(?:=(.+))?](.+?)?\[\/put]/gi, (whole, command, textToPut) => {
				return textToPut || command;
			});
		});

		result = result.replace(/\[send(?:=.+?)?](?:.+?)?\[\/send]/gi, (match) => {
			return match.replace(/\[send(?:=(.+))?](.+?)?\[\/send]/gi, (whole, command, textToSend) => {
				return textToSend || command;
			});
		});

		result = result.replace(/\[[bisu]](.*?)\[\/[bisu]]/gi, '$1');
		result = result.replace(/\[url](.*?)\[\/url]/gi, '$1');
		result = result.replace(/\[url=(.*?)](.*?)\[\/url]/gi, '$2');
		result = result.replace(/\[rating=([1-5])]/gi, () => `[${Loc.getMessage('IM_UTILS_TEXT_RATING')}] `);
		result = result.replace(/\[attach=(\d+)]/gi, () => `[${Loc.getMessage('IM_UTILS_TEXT_ATTACH')}] `);
		result = result.replace(/\[dialog=(chat\d+|\d+)(?: message=(\d+))?](.*?)\[\/dialog]/gi, (whole, dialogId, messageId, message) => message);
		result = result.replace(/\[chat=(\d+)](.*?)\[\/chat]/gi, '$2');
		result = result.replace(/\[send(?:=.+?)?](.+?)?\[\/send]/gi, '$1');
		result = result.replace(/\[put(?:=.+?)?](.+?)?\[\/put]/gi, '$1');
		result = result.replace(/\[call(?:=.+?)?](.*?)\[\/call]/gi, '$1');
		result = result.replace(/\[pch=(\d+)](.*?)\[\/pch]/gi, '$2');
		result = result.replace(/<img.*?data-code="([^"]*)".*?>/gi, '$1');
		result = result.replace(/<span.*?title="([^"]*)".*?>.*?<\/span>/gi, '($1)');
		result = result.replace(/<img.*?title="([^"]*)".*?>/gi, '($1)');
		result = result.replace(/<s>([^"]*)<\/s>/gi, ' ');
		result = result.replace(/\[s]([^"]*)\[\/s]/gi, ' ');
		result = result.replace(/\[icon=([^\]]*)]/gi, this.prepareIconCode);

		codeReplacement.forEach((element, index) => {
			result = result.replace(`####REPLACEMENT_CODE_${index}####`, element);
		});

		result = result.replace(/-{54}(.*?)-{54}/gims, `[${Loc.getMessage('IM_UTILS_TEXT_QUOTE')}] `);
		result = result.replace(/^(>>(.*)(\n)?)/gim, `[${Loc.getMessage('IM_UTILS_TEXT_QUOTE')}] `);

		if (options.WITH_ATTACH && result.length === 0)
		{
			result = `[${Loc.getMessage('IM_UTILS_TEXT_ATTACH')}] ${result}`;
		}
		else if (options.WITH_FILE && result.length === 0)
		{
			result = `[${Loc.getMessage('IM_UTILS_TEXT_FILE')}] ${result}`;
		}

		result = result.replace(/\n/gi, ' ').trim();

		const SPLIT_INDEX = 24;
		const UNSEEN_SPACE = '\u200B';

		if (result.length > SPLIT_INDEX)
		{
			let firstPart = result.slice(0, SPLIT_INDEX + 1);
			const secondPart = result.slice(SPLIT_INDEX + 1);
			const hasWhitespace = /\s/.test(firstPart);
			const hasUserCode = /\[user=(\d+)](.*?)\[\/user]/i.test(result);
			if (firstPart.length === SPLIT_INDEX + 1 && !hasWhitespace && !hasUserCode)
			{
				firstPart += UNSEEN_SPACE;
			}
			result = firstPart + secondPart;
		}

		return result;
	}

	prepareIconCode(wholeMatch: string): string
	{
		let title = wholeMatch.match(/title=(.*[^\s\]])/i);
		if (title && title[1])
		{
			// eslint-disable-next-line prefer-destructuring
			title = title[1];
			if (title.includes('width='))
			{
				title = title.slice(0, Math.max(0, title.indexOf('width=')));
			}
			if (title.includes('height='))
			{
				title = title.slice(0, Math.max(0, title.indexOf('height=')));
			}
			if (title.includes('size='))
			{
				title = title.slice(0, Math.max(0, title.indexOf('size=')));
			}
			if (title)
			{
				title = `(${title.trim()})`;
			}
		}
		else
		{
			title = `(${Loc.getMessage('IM_UTILS_TEXT_ICON')})`;
		}
		return title;
	}

	prepareActiveCall(call)
	{
		return {...this.getActiveCallDefaultState(), ...this.validateActiveCall(call)};
	}

	validateActiveCall(fields)
	{
		const result = {};

		if (Type.isStringFilled(fields.dialogId) || Type.isNumber(fields.dialogId))
		{
			result.dialogId = fields.dialogId;
		}

		if (Type.isStringFilled(fields.name))
		{
			result.name = fields.name;
		}

		if (Type.isObjectLike(fields.call))
		{
			result.call = fields.call;

			if (fields.call?.associatedEntity?.avatar === '/bitrix/js/im/images/blank.gif')
			{
				result.call.associatedEntity.avatar = '';
			}
		}

		if (RecentCallStatus[fields.state])
		{
			result.state = fields.state;
		}

		return result;
	}

	validateOptions(fields)
	{
		const result = {};

		if (Type.isBoolean(fields.showBirthday))
		{
			result.showBirthday = fields.showBirthday;
		}

		if (Type.isBoolean(fields.showInvited))
		{
			result.showInvited = fields.showInvited;
		}

		if (Type.isBoolean(fields.showLastMessage))
		{
			result.showLastMessage = fields.showLastMessage;
		}

		return result;
	}
}