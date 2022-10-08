/**
 * Bitrix im
 * Dialog vue component
 *
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2021 Bitrix
 */

import 'ui.design-tokens';
import 'ui.fonts.opensans';

import {BitrixVue} from "ui.vue";

import {Logger} from "im.lib.logger";
import {Utils} from "im.lib.utils";
import {EventType, RestMethodHandler, RestMethod, DialogState} from "im.const";

import "./component.css";
import {MessageList} from './components/message-list/message-list';
import {ErrorState} from './components/error-state';
import {LoadingState} from './components/loading-state';
import {EmptyState} from './components/empty-state';
import {QuotePanel} from './components/quote-panel';

import {Text} from 'main.core';
import { EventEmitter } from "main.core.events";
import { Vuex } from "ui.vue.vuex";

BitrixVue.component('bx-im-component-dialog',
{
	components: {MessageList, ErrorState, LoadingState, EmptyState, QuotePanel},
	props:
	{
		userId: { default: 0 },
		dialogId: { default: 0 },
		skipDataRequest: { default: false },
		showLoadingState: {default: true},
		showEmptyState: {default: true},
		enableGestureQuote: { default: true },
		enableGestureQuoteFromRight: { default: true },
		enableGestureMenu: { default: false },
		showMessageUserName: { default: true },
		showMessageAvatar: { default: true },
	},
	data()
	{
		return {
			messagesSet: false,
			dialogState: DialogState.loading
		};
	},
	created()
	{
		EventEmitter.subscribe(EventType.dialog.messagesSet, this.onMessagesSet);
		this.onDialogOpen();
	},
	beforeDestroy()
	{
		EventEmitter.unsubscribe(EventType.dialog.messagesSet, this.onMessagesSet);
	},
	watch:
	{
		dialogId(newValue, oldValue)
		{
			Logger.warn('Switching dialogId from ', oldValue, ' to ', newValue);
			this.messagesSet = false;
			this.onDialogOpen();
		}
	},
	computed:
	{
		EventType: () => EventType,
		DialogState: () => DialogState,

		dialogWrapClasses()
		{
			return ['bx-mobilechat-wrapper', {'bx-mobilechat-chat-start': this.isDialogShowingMessages}];
		},
		dialogBoxClasses()
		{
			return ['bx-mobilechat-box', {'bx-mobilechat-box-dark-background': this.isDarkBackground}];
		},
		dialogBodyClasses()
		{
			return ['bx-mobilechat-body', {'bx-mobilechat-body-with-message': this.dialogState === DialogState.show}];
		},
		quotePanelData()
		{
			const result = {
				id: 0,
				title: '',
				description: '',
				color: ''
			};

			if (!this.isDialogShowingMessages || !this.dialog.quoteId)
			{
				return result;
			}

			const message = this.$store.getters['messages/getMessage'](this.dialog.chatId, this.dialog.quoteId);
			if (!message)
			{
				return result;
			}

			const user = this.$store.getters['users/get'](message.authorId);
			const files = this.$store.getters['files/getList'](this.dialog.chatId);

			return {
				id: this.dialog.quoteId,
				title: message.params.NAME ? Text.decode(message.params.NAME) : (user ? user.name: ''),
				color: user? user.color: '',
				description: Utils.text.purify(message.text, message.params, files, this.localize)
			};
		},
		isLoading()
		{
			if (!this.showLoadingState)
			{
				return false;
			}
			// show placeholders if we don't have chatId for current dialogId
			// or we have chatId, but there is no messages collection for this chatId and messages are not set yet
			// (because if chat is empty - there will be no messages collection, but we should not show loading state)
			return !this.isChatIdInModel || (this.isChatIdInModel && !this.isMessagesModelInited && !this.messagesSet);
		},
		isEmpty()
		{
			return this.showEmptyState && this.messagesSet && this.messageCollection.length === 0;
		},
		isChatIdInModel()
		{
			const dialogues = this.$store.state.dialogues.collection;

			return dialogues[this.dialogId] && dialogues[this.dialogId].chatId > 0;
		},
		isMessagesModelInited()
		{
			const messages = this.$store.state.messages.collection;

			return messages[this.chatId];
		},
		isDialogShowingMessages()
		{
			const messagesNotEmpty = this.messageCollection && this.messageCollection.length > 0;
			if (messagesNotEmpty)
			{
				this.dialogState = DialogState.show;
			}
			else if (this.dialog && this.dialog.init)
			{
				this.dialogState = DialogState.empty;
			}
			else
			{
				this.dialogState = DialogState.loading;
			}

			return messagesNotEmpty;
		},
		dialog()
		{
			const dialog = this.$store.getters['dialogues/get'](this.application.dialog.dialogId);

			return dialog? dialog: this.$store.getters['dialogues/getBlank']();
		},
		chatId()
		{
			if (!this.application)
			{
				return 0;
			}

			return this.application.dialog.chatId;
		},
		messageCollection()
		{
			return this.$store.getters['messages/get'](this.application.dialog.chatId);
		},
		isDarkBackground()
		{
			return this.application.options.darkBackground;
		},
		...Vuex.mapState({
			application: state => state.application,
		}),
		localize()
		{
			return BitrixVue.getFilteredPhrases(['IM_DIALOG_', 'IM_UTILS_', 'IM_MESSENGER_DIALOG_', 'IM_QUOTE_'], this);
		},
	},
	methods:
	{
		prepareRequestDataQuery()
		{
			const query = {
				[RestMethodHandler.mobileBrowserConstGet]: [RestMethod.mobileBrowserConstGet, {}],
				[RestMethodHandler.imChatGet]: [RestMethod.imChatGet, {dialog_id: this.dialogId}],
				[RestMethodHandler.imDialogMessagesGetInit]: [RestMethod.imDialogMessagesGet, {
					dialog_id: this.dialogId,
					limit: this.getController().application.getRequestMessageLimit(),
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

			return query;
		},

		requestData()
		{
			Logger.log('requesting dialog data');

			const query = this.prepareRequestDataQuery();
			this.$Bitrix.RestClient.get().callBatch(query, (response) =>
			{
				if (!response)
				{
					return false;
				}

				//const.get
				let constGetResult = response[RestMethodHandler.mobileBrowserConstGet];
				if (!constGetResult.error())
				{
					this.executeRestAnswer(RestMethodHandler.mobileBrowserConstGet, constGetResult);
				}

				//user.get
				let userGetResult = response[RestMethodHandler.imUserGet];
				if (userGetResult && !userGetResult.error())
				{
					this.executeRestAnswer(RestMethodHandler.imUserGet, userGetResult);
				}

				//user.list.get
				let userListGetResult = response[RestMethodHandler.imUserListGet];
				if (userListGetResult && !userListGetResult.error())
				{
					this.executeRestAnswer(RestMethodHandler.imUserListGet, userListGetResult);
				}

				//chat.get
				let chatGetResult = response[RestMethodHandler.imChatGet];
				if (!chatGetResult.error())
				{
					this.executeRestAnswer(RestMethodHandler.imChatGet, chatGetResult);
				}

				//dialog.messages.get
				let dialogMessagesGetResult = response[RestMethodHandler.imDialogMessagesGetInit];
				if (!dialogMessagesGetResult.error())
				{
					this.$store.dispatch('application/set', {
						dialog: { enableReadMessages: true }
					}).then(() => {
						this.executeRestAnswer(RestMethodHandler.imDialogMessagesGetInit, dialogMessagesGetResult);
						// this.messagesSet = true;
					});
				}
			}, false, false, Utils.getLogTrackingParams({name: 'im.dialog', dialog: this.getController().application.getDialogData()}));

			return new Promise((resolve, reject) => resolve());
		},
		onDialogOpen()
		{
			if (this.isChatIdInModel)
			{
				const dialogues = this.$store.state.dialogues.collection;

				this.$store.commit('application/set', {dialog: {
					chatId: dialogues[this.dialogId].chatId,
					dialogId: this.dialogId,
				}});
			}
			if (!this.skipDataRequest)
			{
				this.requestData();
			}
		},
		onMessagesSet({data: event})
		{
			if (event.chatId !== this.chatId)
			{
				return false;
			}

			if (this.messagesSet === true)
			{
				return false;
			}

			this.messagesSet = true;
		},

		getController()
		{
			return this.$Bitrix.Data.get('controller');
		},

		executeRestAnswer(method, queryResult, extra)
		{
			this.getController().executeRestAnswer(method, queryResult, extra);
		}
	},
	// language=Vue
	template: `
		<div :class="dialogWrapClasses">
			<div :class="dialogBoxClasses" ref="chatBox">
				<!-- Error state -->
				<ErrorState v-if="application.error.active" />
				<template v-else>
					<div :class="dialogBodyClasses" key="with-message">
						<!-- Loading state -->
					  	<LoadingState v-if="isLoading" />
						<!-- Empty state -->
					  	<EmptyState v-else-if="isEmpty" />
						<!-- Message list state -->
						<template v-else>
							<div class="bx-mobilechat-dialog">
								<MessageList
									:userId="userId" 
									:dialogId="dialogId"
									:messageLimit="application.dialog.messageLimit"
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
								 />
							</div>
							<!-- Quote panel -->
							<QuotePanel :quotePanelData="quotePanelData" />
						</template>
					</div>
				</template>
			</div>
		</div>
	`
});