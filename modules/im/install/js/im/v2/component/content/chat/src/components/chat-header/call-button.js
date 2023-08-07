import { Messenger } from 'im.public';
import { CallManager } from 'im.v2.lib.call';
import type { ImModelDialog } from 'im.v2.model';

import { CallMenu } from './classes/call-menu';

import '../../css/call-button.css';

// @vue/component
export const CallButton = {
	props:
	{
		dialogId: {
			type: String,
			required: true,
		},
	},
	emits: [],
	data()
	{
		return {};
	},
	computed:
	{
		dialog(): ImModelDialog
		{
			return this.$store.getters['dialogues/get'](this.dialogId, true);
		},
		isActive(): boolean
		{
			return CallManager.getInstance().chatCanBeCalled(this.dialogId);
		},
	},
	methods:
	{
		startVideoCall()
		{
			if (!this.isActive)
			{
				return;
			}

			Messenger.startVideoCall(this.dialogId);
		},
		getCallMenu(): CallMenu
		{
			if (!this.callMenu)
			{
				this.callMenu = new CallMenu();
			}

			return this.callMenu;
		},
		onMenuClick()
		{
			this.getCallMenu().openMenu(this.dialog, this.$refs.menu);
		},
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<div
			class="bx-im-chat-header-call-button__container"
			:class="{'--disabled': !isActive}"
			@click="startVideoCall"
		>
			<div class="bx-im-chat-header-call-button__text">
				{{ loc('IM_CONTENT_CHAT_HEADER_VIDEOCALL_HD') }}
			</div>
			<div class="bx-im-chat-header-call-button__separator"></div>
			<div class="bx-im-chat-header-call-button__chevron_container" @click.stop="onMenuClick">
				<div class="bx-im-chat-header-call-button__chevron" ref="menu"></div>
			</div>
		</div>
	`,
};
