/**
 * Bitrix Im
 * Application Messenger view
 *
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2020 Bitrix
 */

import {BitrixVue} from "ui.vue";
import {Logger} from "im.lib.logger";
import {Utils} from "im.lib.utils";
import "im.component.recent";
import "im.component.dialog";
import "im.component.textarea";
import "pull.component.status";
import "./view.css";

import {DeviceType, EventType} from 'im.const';
import {
	DialogCore, DialogReadMessages, DialogQuoteMessage, DialogClickOnCommand, DialogClickOnMention, DialogClickOnUserName,
	DialogClickOnMessageMenu, DialogClickOnMessageRetry, DialogClickOnUploadCancel, DialogClickOnReadList,
	DialogSetMessageReaction, DialogOpenMessageReactionList, DialogClickOnKeyboardButton, DialogClickOnChatTeaser,
	DialogClickOnDialog, TextareaCore, TextareaUploadFile
} from 'im.mixin';
import { EventEmitter } from "main.core.events";
import {Loc} from "main.core";

BitrixVue.component('bx-im-application-messenger',
{
	props:
	{
		userId: { default: 0 },
		initialDialogId: { default: '0' }
	},

	mixins: [
		DialogCore, DialogReadMessages, DialogQuoteMessage, DialogClickOnCommand, DialogClickOnMention, DialogClickOnUserName,
		DialogClickOnMessageMenu, DialogClickOnMessageRetry, DialogClickOnUploadCancel, DialogClickOnReadList,
		DialogSetMessageReaction, DialogOpenMessageReactionList, DialogClickOnKeyboardButton, DialogClickOnChatTeaser,
		DialogClickOnDialog, TextareaCore, TextareaUploadFile
	],

	data()
	{
		return {
			dialogId: 0,
			notify: false,
			textareaDrag: false,
			textareaHeight: 120,
			textareaMinimumHeight: 120,
			textareaMaximumHeight: Utils.device.isMobile()? 200: 400,
		}
	},

	created()
	{
		EventEmitter.subscribe('openMessenger', this.onOpenMessenger);
	},

	beforeDestroy()
	{
		EventEmitter.unsubscribe('openMessenger', this.onOpenMessenger);

		this.onTextareaDragEventRemove();
	},

	computed:
	{
		DeviceType: () => DeviceType,

		textareaHeightStyle(state)
		{
			return {flex: '0 0 '+this.textareaHeight+'px'};
		},

		isDialog()
		{
			return Utils.dialog.isChatId(this.dialogId);
		},

		isEnableGesture()
		{
			return false;
		},

		isEnableGestureQuoteFromRight()
		{
			return this.isEnableGesture && true;
		},

		localizeEmptyChat()
		{
			return Loc.getMessage('IM_M_EMPTY');
		}
	},
	methods:
	{
		onOpenMessenger({data: event})
		{
			if (event.id === 'notify')
			{
				this.dialogId = 0;
				this.notify = true;
			}
			else
			{
				this.notify = false;
				this.dialogId = event.id;
			}
		},

		onTextareaStartDrag(event)
		{
			if (this.textareaDrag)
			{
				return;
			}

			Logger.log('Livechat: textarea drag started');

			this.textareaDrag = true;

			event = event.changedTouches ? event.changedTouches[0] : event;

			this.textareaDragCursorStartPoint = event.clientY;
			this.textareaDragHeightStartPoint = this.textareaHeight;

			this.onTextareaDragEventAdd();

			EventEmitter.emit(EventType.textarea.setBlur, true);
		},
		onTextareaContinueDrag(event)
		{
			if (!this.textareaDrag)
			{
				return;
			}

			event = event.changedTouches ? event.changedTouches[0] : event;

			this.textareaDragCursorControlPoint = event.clientY;

			let textareaHeight = Math.max(
				Math.min(this.textareaDragHeightStartPoint + this.textareaDragCursorStartPoint - this.textareaDragCursorControlPoint, this.textareaMaximumHeight)
			, this.textareaMinimumHeight);

			Logger.log('Livechat: textarea drag', 'new: '+textareaHeight, 'curr: '+this.textareaHeight);

			if (this.textareaHeight !== textareaHeight)
			{
				this.textareaHeight = textareaHeight;
			}
		},
		onTextareaStopDrag()
		{
			if (!this.textareaDrag)
			{
				return;
			}

			Logger.log('Livechat: textarea drag ended');

			this.textareaDrag = false;

			this.onTextareaDragEventRemove();

			this.$store.commit('widget/common', {textareaHeight: this.textareaHeight});
			EventEmitter.emit(EventType.dialog.scrollToBottom, {chatId: this.chatId, force: true});
		},
		onTextareaDragEventAdd()
		{
			document.addEventListener('mousemove', this.onTextareaContinueDrag);
			document.addEventListener('touchmove', this.onTextareaContinueDrag);
			document.addEventListener('touchend', this.onTextareaStopDrag);
			document.addEventListener('mouseup', this.onTextareaStopDrag);
			document.addEventListener('mouseleave', this.onTextareaStopDrag);
		},
		onTextareaDragEventRemove()
		{
			document.removeEventListener('mousemove', this.onTextareaContinueDrag);
			document.removeEventListener('touchmove', this.onTextareaContinueDrag);
			document.removeEventListener('touchend', this.onTextareaStopDrag);
			document.removeEventListener('mouseup', this.onTextareaStopDrag);
			document.removeEventListener('mouseleave', this.onTextareaStopDrag);
		},

		logEvent(name, ...params)
		{
			Logger.info(name, ...params);
		},
	},
	// language=Vue
	template: `
	  	<div class="bx-im-next-layout">
			<div class="bx-im-next-layout-recent">
				<bx-im-component-recent/>
			</div>
			<div class="bx-im-next-layout-dialog" v-if="dialogId">
				<div class="bx-im-next-layout-dialog-header">
					<div class="bx-im-header-title">Dialog: {{dialogId}}</div>
				</div>
				<div class="bx-im-next-layout-dialog-messages">
				  	<bx-pull-component-status/>
					<bx-im-component-dialog
						:userId="userId" 
						:dialogId="dialogId"
						:enableGestureMenu="isEnableGesture"
						:enableGestureQuote="isEnableGesture"
						:enableGestureQuoteFromRight="isEnableGestureQuoteFromRight"
						:showMessageUserName="isDialog"
						:showMessageAvatar="isDialog"
					 />
				</div>
				<div class="bx-im-next-layout-dialog-textarea" :style="textareaHeightStyle" ref="textarea">
				  	<div class="bx-im-next-layout-dialog-textarea-handle" @mousedown="onTextareaStartDrag" @touchstart="onTextareaStartDrag"></div>
					<bx-im-component-textarea
						:siteId="application.common.siteId"
						:userId="userId"
						:dialogId="dialogId"
						:writesEventLetter="3"
						:enableEdit="true"
						:enableCommand="false"
						:enableMention="false"
						:enableFile="true"
						:autoFocus="application.device.type !== DeviceType.mobile"
					/>
				</div>
			</div>
			<div class="bx-im-next-layout-notify" v-else-if="notify">
				<bx-im-component-notifications :darkTheme="false"/>
			</div>
			<div class="bx-im-next-layout-notify" v-else>
				<div class="bx-messenger-box-hello-wrap">
				  <div class="bx-messenger-box-hello">{{localizeEmptyChat}}</div>
				</div>
			</div>
		
		</div>
	`
});