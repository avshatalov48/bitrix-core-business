/**
 * Bitrix im dialog mobile
 * Dialog vue component
 *
 * @package bitrix
 * @subpackage mobile
 * @copyright 2001-2019 Bitrix
 */

import {Vue} from "ui.vue";
import {Vuex} from "ui.vue.vuex";
import {Logger} from "im.lib.logger";
import {EventType, RestMethodHandler, RestMethod} from "im.const";
import {Utils} from "im.lib.utils";
import {Clipboard} from "im.lib.clipboard";
import "im.view.dialog";
import "im.view.quotepanel";

import "./component.css";

/**
 * @notice Do not mutate or clone this component! It is under development.
 */
Vue.component('bx-im-component-dialog',
{
	props:
	{
		chatId: { default: 0 },
		userId: { default: 0 },
		dialogId: { default: 0 },
		enableGestureQuote: { default: true },
		enableGestureQuoteFromRight: { default: true },
		enableGestureMenu: { default: false },
		showMessageUserName: { default: true },
		showMessageAvatar: { default: true },
		listenEventScrollToBottom: { default: '' },
		listenEventRequestHistory: { default: '' },
		listenEventRequestUnread: { default: '' },
		listenEventSendReadMessages: { default: '' }
	},
	data: function()
	{
		return {
			dialogState: 'loading',
			dialogDiskFolderId: 0,
			dialogChatId: 0,
			scrollToBottomEvent: this.listenEventScrollToBottom,
			requestHistoryEvent: this.listenEventRequestHistory,
			requestUnreadEvent: this.listenEventRequestUnread,
			sendReadMessagesEvent: this.listenEventSendReadMessages
		};
	},
	created: function()
	{
		if (!this.listenEventScrollToBottom)
		{
			this.scrollToBottomEvent = EventType.dialog.scrollToBottom;
		}
		if (!this.listenEventRequestHistory)
		{
			this.requestHistoryEvent = EventType.dialog.requestHistoryResult
		}
		if (!this.listenEventRequestUnread)
		{
			this.requestUnreadEvent = EventType.dialog.requestUnreadResult;
		}
		if (!this.listenEventSendReadMessages)
		{
			this.sendReadMessagesEvent = EventType.dialog.sendReadMessages;
		}

		this.requestData();
	},
	watch:
	{
		dialogId()
		{
			this.requestData();
		}
	},
	computed:
	{
		EventType: () => EventType,
		localize()
		{
			return Object.assign({},
				Vue.getFilteredPhrases('IM_DIALOG_', this.$root.$bitrixMessages),
				Vue.getFilteredPhrases('IM_UTILS_', this.$root.$bitrixMessages),
			);
		},
		widgetClassName(state)
		{
			let className = ['bx-mobilechat-wrapper'];

			if (this.showMessageDialog)
			{
				className.push('bx-mobilechat-chat-start');
			}

			return className.join(' ');
		},
		quotePanelData()
		{
			let result = {
				id: 0,
				title: '',
				description: '',
				color: ''
			};

			if (!this.showMessageDialog || !this.dialog.quoteId)
			{
				return result;
			}

			let message = this.$store.getters['messages/getMessage'](this.dialog.chatId, this.dialog.quoteId);
			if (!message)
			{
				return result;
			}

			let user = this.$store.getters['users/get'](message.authorId);
			let files = this.$store.getters['files/getList'](this.dialog.chatId);

			return {
				id: this.dialog.quoteId,
				title: message.params.NAME ? message.params.NAME : (user ? user.name: ''),
				color: user? user.color: '',
				description: Utils.text.purify(message.text, message.params, files, this.localize)
			};
		},

		isDialog()
		{
			return Utils.dialog.isChatId(this.dialog.dialogId);
		},

		isGestureQuoteSupported()
		{
			return false;
		},
		isDarkBackground()
		{
			return this.application.options.darkBackground;
		},
		showMessageDialog()
		{
			let result = this.messageCollection && this.messageCollection.length > 0;
			if (result)
			{
				this.dialogState = 'show';
			}
			else if (this.dialog && this.dialog.init)
			{
				this.dialogState = 'empty';
			}
			else
			{
				this.dialogState = 'loading';
			}

			return result;
		},
		dialog()
		{
			let dialog = this.$store.getters['dialogues/get'](this.application.dialog.dialogId);
			return dialog? dialog: this.$store.getters['dialogues/getBlank']();
		},
		messageCollection()
		{
			return this.$store.getters['messages/get'](this.application.dialog.chatId);
		},
		...Vuex.mapState({
			application: state => state.application,
		})
	},
	methods:
	{
		requestData()
		{
			console.log('4. requestData');

			//this.requestDataSend = true;

			let query = {
				[RestMethodHandler.mobileBrowserConstGet]: [RestMethod.mobileBrowserConstGet, {}],
				[RestMethodHandler.imChatGet]: [RestMethod.imChatGet, {dialog_id: this.dialogId}],
				[RestMethodHandler.imDialogMessagesGetInit]: [RestMethod.imDialogMessagesGet, {
					dialog_id: this.dialogId,
					limit: this.$root.$bitrixController.application.getRequestMessageLimit(),
					convert_text: 'Y'
				}],
			};
			if (Utils.dialog.isChatId(this.dialogId))
			{
				query[RestMethodHandler.imUserGet] = [RestMethod.imUserGet, {}];
			}
			else
			{
				query[RestMethodHandler.imUserListGet] = [RestMethod.imUserListGet, {id: [this.userId, this.dialogId]}];
			}

			this.$root.$bitrixController.restClient.callBatch(query, (response) =>
			{
				if (!response)
				{
					//this.requestDataSend = false;
					//this.setError('EMPTY_RESPONSE', 'Server returned an empty response.');
					return false;
				}

				let constGet = response[RestMethodHandler.mobileBrowserConstGet];
				if (constGet.error())
				{
					// this.setError(constGet.error().ex.error, constGet.error().ex.error_description);
				}
				else
				{
					this.$root.$bitrixController.executeRestAnswer(RestMethodHandler.mobileBrowserConstGet, constGet);
				}

				let userGet = response[RestMethodHandler.imUserGet];
				if (userGet && !userGet.error())
				{
					this.$root.$bitrixController.executeRestAnswer(RestMethodHandler.imUserGet, userGet);
				}

				let userListGet = response[RestMethodHandler.imUserListGet];
				if (userListGet && !userListGet.error())
				{
					this.$root.$bitrixController.executeRestAnswer(RestMethodHandler.imUserListGet, userListGet);
				}

				let chatGetResult = response[RestMethodHandler.imChatGet];
				if (!chatGetResult.error())
				{
					this.dialogChatId = chatGetResult.data().id;
					this.dialogDiskFolderId = chatGetResult.data().disk_folder_id;
				}

				// TODO imChatGet
				this.$root.$bitrixController.executeRestAnswer(RestMethodHandler.imChatGet, chatGetResult);

				let dialogMessagesGetResult = response[RestMethodHandler.imDialogMessagesGetInit];
				if (dialogMessagesGetResult.error())
				{
					//this.setError(dialogMessagesGetResult.error().ex.error, dialogMessagesGetResult.error().ex.error_description);
				}
				else
				{
					//this.timer.stop('data', 'load', true);

					// this.$root.$bitrixController.getStore().dispatch('dialogues/saveDialog', {
					// 	dialogId: this.$root.$bitrixController.application.getDialogId(),
					// 	chatId: this.$root.$bitrixController.application.getChatId(),
					// });

					if (this.$root.$bitrixController.pullCommandHandler)
					{
						//this.$root.$bitrixController.pullCommandHandler.option.skip = false;
					}

					this.$root.$bitrixController.getStore().dispatch('application/set', {dialog: {
						enableReadMessages: true
					}}).then(() => {
						this.$root.$bitrixController.executeRestAnswer(RestMethodHandler.imDialogMessagesGetInit, dialogMessagesGetResult);
					});

					//this.processSendMessages();
				}

				//this.requestDataSend = false;
			}, false, false, Utils.getLogTrackingParams({name: 'im.dialog', dialog: this.$root.$bitrixController.application.getDialogData()}));

			return new Promise((resolve, reject) => resolve());
		},

		getDialogHistory(lastId, limit = this.$root.$bitrixController.application.getRequestMessageLimit())
		{
			this.$root.$bitrixController.restClient.callMethod(RestMethod.imDialogMessagesGet, {
				'CHAT_ID': this.dialogChatId,
				'LAST_ID': lastId,
				'LIMIT': limit,
				'CONVERT_TEXT': 'Y'
			}).then(result => {
				this.$root.$bitrixController.executeRestAnswer(RestMethodHandler.imDialogMessagesGet, result);
				this.$root.$emit(EventType.dialog.requestHistoryResult, {count: result.data().messages.length});
			}).catch(result => {
				this.$root.$emit(EventType.dialog.requestHistoryResult, {error: result.error().ex});
			});
		},

		getDialogUnread(lastId, limit = this.$root.$bitrixController.application.getRequestMessageLimit())
		{
			if (this.promiseGetDialogUnreadWait)
			{
				return this.promiseGetDialogUnread;
			}

			this.promiseGetDialogUnread = new BX.Promise();
			this.promiseGetDialogUnreadWait = true;

			if (!lastId)
			{
				lastId = this.$root.$bitrixController.getStore().getters['messages/getLastId'](this.dialogChatId);
			}

			if (!lastId)
			{
				this.$root.$emit(EventType.dialog.requestUnreadResult, {error: {error: 'LAST_ID_EMPTY', error_description: 'LastId is empty.'}});

				this.promiseGetDialogUnread.reject();
				this.promiseGetDialogUnreadWait = false;

				return this.promiseGetDialogUnread;
			}

			this.$root.$bitrixController.application.readMessage(lastId, true, true).then(() =>
			{
				// this.timer.start('data', 'load', .5, () => {
				// 	console.warn("ChatDialog.requestData: slow connection show progress icon");
				// 	app.titleAction("setParams", {useProgress: true, useLetterImage: false});
				// });

				let query = {
					[RestMethodHandler.imDialogRead]: [RestMethod.imDialogRead, {
						dialog_id: this.dialogId,
						message_id: lastId
					}],
					[RestMethodHandler.imChatGet]: [RestMethod.imChatGet, {
						dialog_id: this.dialogId
					}],
					[RestMethodHandler.imDialogMessagesGetUnread]: [RestMethod.imDialogMessagesGet, {
						chat_id: this.dialogChatId,
						first_id: lastId,
						limit: limit,
						convert_text: 'Y'
					}]
				};

				this.$root.$bitrixController.restClient.callBatch(query, (response) =>
				{
					if (!response)
					{
						this.$root.$emit(EventType.dialog.requestUnreadResult, {error: {error: 'EMPTY_RESPONSE', error_description: 'Server returned an empty response.'}});

						this.promiseGetDialogUnread.reject();
						this.promiseGetDialogUnreadWait = false;

						return false;
					}

					let chatGetResult = response[RestMethodHandler.imChatGet];
					if (!chatGetResult.error())
					{
						this.$root.$bitrixController.executeRestAnswer(RestMethodHandler.imChatGet, chatGetResult);
					}

					let dialogMessageUnread = response[RestMethodHandler.imDialogMessagesGetUnread];
					if (dialogMessageUnread.error())
					{
						this.$root.$emit(EventType.dialog.requestUnreadResult, {error: dialogMessageUnread.error().ex});
					}
					else
					{
						this.$root.$bitrixController.executeRestAnswer(RestMethodHandler.imDialogMessagesGetUnread, dialogMessageUnread);

						this.$root.$emit(EventType.dialog.requestUnreadResult, {
							firstMessageId: dialogMessageUnread.data().messages.length > 0? dialogMessageUnread.data().messages[0].id: 0,
							count: dialogMessageUnread.data().messages.length
						});

						//app.titleAction("setParams", {useProgress: false, useLetterImage: true});
						//this.timer.stop('data', 'load', true);
					}

					this.promiseGetDialogUnread.fulfill(response);
					this.promiseGetDialogUnreadWait = false;

				}, false, false, Utils.getLogTrackingParams({name: RestMethodHandler.imDialogMessagesGetUnread, dialog: this.$root.$bitrixController.application.getDialogData()}));
			});

			return this.promiseGetDialogUnread;
		},




		logEvent(name, ...params)
		{
			Logger.info(name, ...params);
		},
		onDialogRequestHistory(event)
		{
			this.getDialogHistory(event.lastId);
		},

		onDialogRequestUnread(event)
		{
			this.getDialogUnread(event.lastId);
		},
		onDialogMessageClickByUserName(event)
		{
			this.$root.$bitrixController.application.replyToUser(event.user.id, event.user);
		},
		onDialogMessageClickByUploadCancel(event)
		{
			this.$root.$bitrixController.application.cancelUploadFile(event.file.id);
		},
		onDialogMessageClickByCommand(event)
		{
			if (event.type === 'put')
			{
				this.$root.$bitrixApplication.insertText({text: event.value+' '});
			}
			else if (event.type === 'send')
			{
				this.$root.$bitrixApplication.addMessage(event.value);
			}
			else
			{
				Logger.warn('Unprocessed command', event);
			}
		},
		onDialogMessageClickByMention(event)
		{
			if (event.type === 'USER')
			{
				this.$root.$bitrixController.application.openProfile(event.value);
			}
			else if (event.type === 'CHAT')
			{
				this.$root.$bitrixController.application.openDialog(event.value);
			}
			else if (event.type === 'CALL')
			{
				this.$root.$bitrixController.application.openPhoneMenu(event.value);
			}
		},
		onDialogMessageMenuClick(event)
		{
			Logger.warn('Message menu:', event);
			this.$root.$bitrixController.application.openMessageMenu(event.message);
		},
		onDialogMessageRetryClick(event)
		{
			Logger.warn('Message retry:', event);
			this.$root.$bitrixController.application.retrySendMessage(event.message);
		},
		onDialogReadMessage(event)
		{
			this.$root.$bitrixController.application.readMessage(event.id);
		},
		onDialogReadedListClick(event)
		{
			this.$root.$bitrixController.application.openReadedList(event.list);
		},
		onDialogQuoteMessage(event)
		{
			this.$root.$bitrixController.application.quoteMessage(event.message.id);
		},
		onDialogMessageReactionSet(event)
		{
			this.$root.$bitrixController.application.reactMessage(event.message.id, event.reaction);
		},
		onDialogMessageReactionListOpen(event)
		{
			this.$root.$bitrixController.application.openMessageReactionList(event.message.id, event.values);
		},
		onDialogMessageClickByChatTeaser(event)
		{
			this.$root.$bitrixController.application.joinParentChat(data.message.id, 'chat'+data.message.params.CHAT_ID).then((dialogId) => {
				this.openDialog(dialogId);
			}).catch(() => {});

			return true;
		},
		onDialogMessageClickByKeyboardButton(data)
		{
			if (data.action === 'ACTION')
			{
				let {dialogId, messageId, botId, action, value} = data.params;

				if (action === 'SEND')
				{
					this.$root.$bitrixApplication.addMessage(value);
					setTimeout(() => this.$root.$bitrixController.application.emit(EventType.dialog.scrollToBottom, {duration: 300, cancelIfScrollChange: false}), 300);
				}
				else if (action === 'PUT')
				{
					this.$root.$bitrixApplication.insertText({text: value+' '});
				}
				else if (action === 'CALL')
				{
					//this.openPhoneMenu(value);
				}
				else if (action === 'COPY')
				{
					Clipboard.copy(value);

					BX.UI.Notification.Center.notify({
						content: this.localize.IM_DIALOG_CLIPBOARD_COPY_SUCCESS,
						autoHideDelay: 4000
					});
				}
				else if (action === 'DIALOG')
				{
					//this.openDialog(value);
				}

				return true;
			}

			if (data.action === 'COMMAND')
			{
				let {dialogId, messageId, botId, command, params} = data.params;

				this.$root.$bitrixController.restClient.callMethod(RestMethod.imMessageCommand, {
					'MESSAGE_ID': messageId,
					'DIALOG_ID': dialogId,
					'BOT_ID': botId,
					'COMMAND': command,
					'COMMAND_PARAMS': params,
				});

				return true;
			}

			return false;
		},
		onDialogClick(event)
		{
		},
		onQuotePanelClose()
		{
			this.$root.$bitrixController.quoteMessageClear();
		},

	},
	template: `
		<div :class="widgetClassName">
			<div :class="['bx-mobilechat-box', {'bx-mobilechat-box-dark-background': isDarkBackground}]">
				<template v-if="application.error.active">
					<div class="bx-mobilechat-body">
						<div class="bx-mobilechat-warning-window">
							<div class="bx-mobilechat-warning-icon"></div>
							<template v-if="application.error.description"> 
								<div class="bx-mobilechat-help-title bx-mobilechat-help-title-sm bx-mobilechat-warning-msg" v-html="application.error.description"></div>
							</template> 
							<template v-else>
								<div class="bx-mobilechat-help-title bx-mobilechat-help-title-md bx-mobilechat-warning-msg">{{localize.IM_DIALOG_ERROR_TITLE}}</div>
								<div class="bx-mobilechat-help-title bx-mobilechat-help-title-sm bx-mobilechat-warning-msg">{{localize.IM_DIALOG_ERROR_DESC}}</div>
							</template> 
						</div>
					</div>
				</template>			
				<template v-else>
					<div :class="['bx-mobilechat-body', {'bx-mobilechat-body-with-message': dialogState == 'show'}]" key="with-message">
						<template v-if="dialogState == 'loading'">
							<div class="bx-mobilechat-loading-window">
								<svg class="bx-mobilechat-loading-circular" viewBox="25 25 50 50">
									<circle class="bx-mobilechat-loading-path" cx="50" cy="50" r="20" fill="none" stroke-miterlimit="10"/>
									<circle class="bx-mobilechat-loading-inner-path" cx="50" cy="50" r="20" fill="none" stroke-miterlimit="10"/>
								</svg>
								<h3 class="bx-mobilechat-help-title bx-mobilechat-help-title-md bx-mobilechat-loading-msg">{{localize.IM_DIALOG_LOADING}}</h3>
							</div>
						</template>
						<template v-else-if="dialogState == 'empty'">
							<div class="bx-mobilechat-loading-window">
								<h3 class="bx-mobilechat-help-title bx-mobilechat-help-title-md bx-mobilechat-loading-msg">{{localize.IM_DIALOG_EMPTY}}</h3>
							</div>
						</template>
						<template v-else>
							<div class="bx-mobilechat-dialog">
								<bx-im-view-dialog
									:userId="userId" 
									:dialogId="dialogId"
									:chatId="dialogChatId"
									:messageLimit="application.dialog.messageLimit"
									:messageExtraCount="application.dialog.messageExtraCount"
									:enableReadMessages="application.dialog.enableReadMessages"
									:enableReactions="true"
									:enableDateActions="false"
									:enableCreateContent="false"
									:enableGestureQuote="enableGestureQuote"
									:enableGestureQuoteFromRight="enableGestureQuoteFromRight"
									:enableGestureMenu="enableGestureMenu"
									:showMessageUserName="showMessageUserName"
									:showMessageAvatar="showMessageAvatar"
									:showMessageMenu="false"
									:listenEventScrollToBottom="scrollToBottomEvent"
									:listenEventRequestHistory="listenEventRequestHistory"
									:listenEventRequestUnread="listenEventRequestUnread"
									:listenEventSendReadMessages="listenEventSendReadMessages"
									@readMessage="onDialogReadMessage"
									@quoteMessage="onDialogQuoteMessage"
									@requestHistory="onDialogRequestHistory"
									@requestUnread="onDialogRequestUnread"
									@clickByCommand="onDialogMessageClickByCommand"
									@clickByMention="onDialogMessageClickByMention"
									@clickByUserName="onDialogMessageClickByUserName"
									@clickByMessageMenu="onDialogMessageMenuClick"
									@clickByMessageRetry="onDialogMessageRetryClick"
									@clickByUploadCancel="onDialogMessageClickByUploadCancel"
									@clickByReadedList="onDialogReadedListClick"
									@setMessageReaction="onDialogMessageReactionSet"
									@openMessageReactionList="onDialogMessageReactionListOpen"
									@clickByKeyboardButton="onDialogMessageClickByKeyboardButton"
									@clickByChatTeaser="onDialogMessageClickByChatTeaser"
									@click="onDialogClick"
								 />
							</div>
							<bx-im-view-quote-panel :id="quotePanelData.id" :title="quotePanelData.title" :description="quotePanelData.description" :color="quotePanelData.color" @close="onQuotePanelClose"/>
						</template>
					</div>
				</template>
			</div>
		</div>
	`
});