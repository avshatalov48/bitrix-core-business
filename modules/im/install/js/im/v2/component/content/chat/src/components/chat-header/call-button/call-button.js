import { Extension } from 'main.core';
import { BaseEvent } from 'main.core.events';

import { Messenger } from 'im.public';
import { ChatActionType, LocalStorageKey, DialogType } from 'im.v2.const';
import { CallManager } from 'im.v2.lib.call';
import { PermissionManager } from 'im.v2.lib.permission';
import { LocalStorageManager } from 'im.v2.lib.local-storage';

import { CallMenu } from '../classes/call-menu';
import { CallTypes } from './call-types';

import '../../../css/call-button.css';

import type { ImModelDialog } from 'im.v2.model';

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
		return {
			lastCallType: '',
		};
	},
	computed:
	{
		dialog(): ImModelDialog
		{
			return this.$store.getters['dialogues/get'](this.dialogId, true);
		},
		isActive(): boolean
		{
			const chatCanBeCalled = CallManager.getInstance().chatCanBeCalled(this.dialogId);
			const chatIsAllowedToCall = PermissionManager.getInstance().canPerformAction(ChatActionType.call, this.dialogId);

			return chatCanBeCalled && chatIsAllowedToCall;
		},
		isConference(): boolean
		{
			return this.dialog.type === DialogType.videoconf;
		},
		callButtonText(): string
		{
			const locCode = CallTypes[this.lastCallType].locCode;

			return this.loc(locCode);
		},
	},
	created()
	{
		this.lastCallType = this.getLastCallChoice();
		this.subscribeToMenuItemClick();
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
		subscribeToMenuItemClick()
		{
			this.getCallMenu().subscribe(
				CallMenu.events.onMenuItemClick,
				(event: BaseEvent<{id: string}>) => {
					const { id: callTypeId } = event.getData();
					this.saveLastCallChoice(callTypeId);
				},
			);
		},
		getCallMenu(): CallMenu
		{
			if (!this.callMenu)
			{
				this.callMenu = new CallMenu();
			}

			return this.callMenu;
		},
		onButtonClick()
		{
			if (!this.isActive)
			{
				return;
			}

			CallTypes[this.lastCallType].start(this.dialogId);
		},
		onMenuClick()
		{
			if (!this.shouldShowMenu())
			{
				return;
			}
			this.getCallMenu().openMenu(this.dialog, this.$refs.menu);
		},
		onStartConferenceClick()
		{
			Messenger.openConference({ code: this.dialog.public.code });
		},
		getLastCallChoice(): string
		{
			return LocalStorageManager.getInstance().get(LocalStorageKey.lastCallType, CallTypes.video.id);
		},
		saveLastCallChoice(callTypeId: string)
		{
			this.lastCallType = callTypeId;
			LocalStorageManager.getInstance().set(LocalStorageKey.lastCallType, callTypeId);
		},
		shouldShowMenu(): boolean
		{
			return this.isActive || this.isCallBetaAvailable();
		},
		isCallBetaAvailable(): boolean
		{
			const settings = Extension.getSettings('im.v2.component.content.chat');

			return settings.get('isCallBetaAvailable');
		},
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<div v-if="isConference" class="bx-im-chat-header-call-button__container --conference" @click="onStartConferenceClick">
			<div class="bx-im-chat-header-call-button__text">
				{{ loc('IM_CONTENT_CHAT_HEADER_START_CONFERENCE') }}
			</div>
		</div>
		<div
			v-else
			class="bx-im-chat-header-call-button__container"
			:class="{'--disabled': !isActive}"
			@click="onButtonClick"
		>
			<div class="bx-im-chat-header-call-button__text">
				{{ callButtonText }}
			</div>
			<div class="bx-im-chat-header-call-button__separator"></div>
			<div class="bx-im-chat-header-call-button__chevron_container" @click.stop="onMenuClick">
				<div class="bx-im-chat-header-call-button__chevron" ref="menu"></div>
			</div>
		</div>
	`,
};
