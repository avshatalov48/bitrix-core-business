/**
 * Bitrix Im
 * Application Dialog view
 *
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2020 Bitrix
 */

import {Vue} from "ui.vue";
import {Logger} from "im.lib.logger";
import {Utils} from "im.lib.utils";
import "im.component.dialog";
import "pull.component.status";
import "./view.css";

/**
 * @notice Do not mutate or clone this component! It is under development.
 */
Vue.component('bx-im-application-dialog',
{
	props:
	{
		userId: { default: 0 },
		dialogId: { default: '0' }
	},

	data()
	{
		return {
			realDialogId: 0
		}
	},


	created()
	{
		this.realDialogId = this.dialogId;

		Vue.event.$on('openMessenger', data =>
		{
			let metaPress = data.$event.ctrlKey || data.$event.metaKey;

			if (this.$root.$bitrixApplication.params.place === 2)
			{
				if (metaPress)
				{
					this.realDialogId = data.id;
				}
			}
			else
			{
				if (!metaPress)
				{
					this.realDialogId = data.id;
				}
			}
		});
	},

	computed:
	{
		isDialog()
		{
			return Utils.dialog.isChatId(this.realDialogId);
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
		logEvent(name, ...params)
		{
			Logger.info(name, ...params);
		},
	},
	template: `
		<div class="bx-mobilechat">
			<div class="bx-mobilechat-dialog-title">Dialog: {{realDialogId}}</div>
			<bx-pull-component-status/>
			<bx-im-component-dialog
				:userId="userId" 
				:dialogId="realDialogId"
				:enableGestureMenu="isEnableGesture"
				:enableGestureQuote="isEnableGesture"
				:enableGestureQuoteFromRight="isEnableGestureQuoteFromRight"
				:showMessageUserName="isDialog"
				:showMessageAvatar="isDialog"
				@clickByCommand="logEvent('clickByCommand', $event)"
				@clickByMention="logEvent('clickByMention', $event)"
				@clickByUserName="logEvent('clickByUserName', $event)"
				@clickByMessageMenu="logEvent('clickByMessageMenu', $event)"
				@clickByMessageRetry="logEvent('clickByMessageRetry', $event)"
				@clickByUploadCancel="logEvent('clickByUploadCancel', $event)"
				@clickByReadedList="logEvent('clickByReadedList', $event)"
				@setMessageReaction="logEvent('setMessageReaction', $event)"
				@openMessageReactionList="logEvent('openMessageReactionList', $event)"
				@clickByKeyboardButton="logEvent('clickByKeyboardButton', $event)"
				@clickByChatTeaser="logEvent('clickByChatTeaser', $event)"
				@click="logEvent('click', $event)"
			 />
		</div>
	`
});