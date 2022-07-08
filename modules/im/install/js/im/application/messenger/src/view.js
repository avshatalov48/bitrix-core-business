/**
 * Bitrix Im
 * Application Messenger view
 *
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2020 Bitrix
 */

import {EventEmitter} from "main.core.events";
import {BitrixVue} from "ui.vue";
import {Vuex} from "ui.vue.vuex";
import {Utils} from "im.lib.utils";
import {DeviceType, EventType} from 'im.const';

import "im.component.recent";
import "im.component.dialog";
import "im.component.textarea";
import "pull.component.status";

import "./view.css";
import {Search} from './search';
import {
	TextareaHandler, TextareaDragHandler, TextareaUploadHandler,
	ReadingHandler, ReactionHandler, QuoteHandler, SendMessageHandler, DialogActionHandler
} from "im.event-handler";

BitrixVue.component('bx-im-application-messenger',
{
	props:
	{
		userId: { type: Number, default: 0 }
	},

	data()
	{
		return {
			selectedDialogId: 0,
			notificationsSelected: false,
			textareaHeight: 120
		};
	},

	computed:
	{
		DeviceType: () => DeviceType,

		textareaHeightStyle(): string
		{
			return {flex: `0 0 ${this.textareaHeight}px`};
		},

		isDialog(): boolean
		{
			return Utils.dialog.isChatId(this.selectedDialogId);
		},

		chatId(): number
		{
			if (this.application)
			{
				return this.application.dialog.chatId;
			}

			return 0;
		},

		dialogId()
		{
			if (this.application)
			{
				return this.application.dialog.dialogId;
			}

			return 0;
		},

		localize()
		{
			return BitrixVue.getFilteredPhrases(['IM_DIALOG_', 'IM_UTILS_', 'IM_MESSENGER_DIALOG_', 'IM_QUOTE_'], this);
		},

		...Vuex.mapState({
			application: state => state.application,
		}),
	},

	created()
	{
		this.initEventHandlers();
		this.searchPopup = null;
		this.subscribeToEvents();
	},

	beforeDestroy()
	{
		this.unsubscribeEvents();
		this.destroyHandlers();
	},

	methods:
	{
		// region handlers
		initEventHandlers()
		{
			this.textareaDragHandler = this.getTextareaDragHandler();
			this.readingHandler = new ReadingHandler(this.$Bitrix);
			this.reactionHandler = new ReactionHandler(this.$Bitrix);
			this.quoteHandler = new QuoteHandler(this.$Bitrix);
			this.textareaHandler = new TextareaHandler(this.$Bitrix);
			this.sendMessageHandler = new SendMessageHandler(this.$Bitrix);
			this.textareaUploadHandler = new TextareaUploadHandler(this.$Bitrix);
			this.dialogActionHandler = new DialogActionHandler(this.$Bitrix);
		},

		destroyHandlers()
		{
			this.textareaDragHandler.destroy();
			this.readingHandler.destroy();
			this.reactionHandler.destroy();
			this.quoteHandler.destroy();
			this.textareaHandler.destroy();
			this.textareaUploadHandler.destroy();
			this.dialogActionHandler.destroy();
		},

		getTextareaDragHandler(): TextareaDragHandler
		{
			return new TextareaDragHandler({
				[TextareaDragHandler.events.onHeightChange]: ({data}) => {
					const {newHeight} = data;
					if (this.textareaHeight !== newHeight)
					{
						this.textareaHeight = newHeight;
					}
				},
				[TextareaDragHandler.events.onStopDrag]: () => {
					EventEmitter.emit(EventType.dialog.scrollToBottom, {chatId: this.chatId, force: true});
				}
			});
		},
		// endregion handlers

		openSearch()
		{
			if (!this.searchPopup)
			{
				this.searchPopup = new Search({
					targetNode: document.querySelector('#bx-im-next-layout-recent-search-input'),
					store: this.$store,
				});
			}

			this.searchPopup.open();
		},

		openMessenger(dialogId)
		{
			dialogId = dialogId.toString();

			if (dialogId === 'notify')
			{
				this.selectedDialogId = 0;
				this.notificationsSelected = true;
			}
			else
			{
				this.selectedDialogId = dialogId;
				this.notificationsSelected = false;
			}
		},

		// region events
		subscribeToEvents()
		{
			EventEmitter.subscribe(EventType.dialog.open, this.onOpenMessenger);
		},

		unsubscribeEvents()
		{
			EventEmitter.unsubscribe(EventType.dialog.open, this.onOpenMessenger);
		},

		onOpenMessenger({data})
		{
			this.openMessenger(data.id);
		},

		onTextareaStartDrag(event)
		{
			this.textareaDragHandler.onStartDrag(event, this.textareaHeight);
			EventEmitter.emit(EventType.textarea.setBlur, true);
		}
		// endregion events
	},
	// language=Vue
	template: `
	  	<div class="bx-im-next-layout">
			<div class="bx-im-next-layout-recent">
				<div class="bx-im-next-layout-recent-search">
					<div class="bx-im-next-layout-recent-search-input" id="bx-im-next-layout-recent-search-input" @click="openSearch">Search</div>  
				</div>
				<div class="bx-im-next-layout-recent-list">
					<bx-im-component-recent/>
				</div>
			</div>
			<div class="bx-im-next-layout-dialog" v-if="selectedDialogId">
				<div class="bx-im-next-layout-dialog-header">
					<div class="bx-im-header-title">Dialog: {{selectedDialogId}}</div>
				</div>
				<div class="bx-im-next-layout-dialog-messages">
				  	<bx-pull-component-status/>
					<bx-im-component-dialog
						:userId="userId" 
						:dialogId="selectedDialogId"
						:showMessageUserName="isDialog"
						:showMessageAvatar="isDialog"
					 />
				</div>
				<div class="bx-im-next-layout-dialog-textarea" :style="textareaHeightStyle" ref="textarea">
				  	<div class="bx-im-next-layout-dialog-textarea-handle" @mousedown="onTextareaStartDrag" @touchstart="onTextareaStartDrag"></div>
					<bx-im-component-textarea
						:siteId="application.common.siteId"
						:userId="userId"
						:dialogId="selectedDialogId"
						:writesEventLetter="3"
						:enableEdit="true"
						:enableCommand="false"
						:enableMention="false"
						:enableFile="true"
						:autoFocus="application.device.type !== DeviceType.mobile"
					/>
				</div>
			</div>
			<div class="bx-im-next-layout-notify" v-else-if="notificationsSelected">
				<bx-im-component-notifications :darkTheme="false"/>
			</div>
			<div class="bx-im-next-layout-notify" v-else>
				<div class="bx-messenger-box-hello-wrap">
				  <div class="bx-messenger-box-hello">{{ $Bitrix.Loc.getMessage('IM_M_EMPTY') }}</div>
				</div>
			</div>
		</div>
	`
});