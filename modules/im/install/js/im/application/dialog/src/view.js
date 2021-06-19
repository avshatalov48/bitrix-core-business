/**
 * Bitrix Im
 * Application Dialog view
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

import {DeviceType} from 'im.const';
import {
	DialogCore, DialogReadMessages, DialogQuoteMessage, DialogClickOnCommand, DialogClickOnMention, DialogClickOnUserName,
	DialogClickOnMessageMenu, DialogClickOnMessageRetry, DialogClickOnUploadCancel, DialogClickOnReadList,
	DialogSetMessageReaction, DialogOpenMessageReactionList, DialogClickOnKeyboardButton, DialogClickOnChatTeaser,
	DialogClickOnDialog, TextareaCore, TextareaUploadFile
} from 'im.mixin';
import { EventEmitter } from "main.core.events";

BitrixVue.component('bx-im-application-dialog',
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
			dialogId: 0
		}
	},

	created()
	{
		this.dialogId = this.initialDialogId;

		EventEmitter.subscribe('openMessenger', this.onOpenMessenger)
	},

	beforeDestroy()
	{
		EventEmitter.unsubscribe('openMessenger', this.onOpenMessenger)
	},

	computed:
	{
		DeviceType: () => DeviceType,
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
	},
	methods:
	{
		onOpenMessenger({data: event})
		{
			this.dialogId = event.id;
		},
		logEvent(name, ...params)
		{
			Logger.info(name, ...params);
		},
	},
	// language=Vue
	template: `
	  	<div style="display: flex;">
			<div class="bx-mobilechat">
				<div class="bx-mobilechat-dialog-title">Dialog: {{dialogId}}</div>
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
	`
});