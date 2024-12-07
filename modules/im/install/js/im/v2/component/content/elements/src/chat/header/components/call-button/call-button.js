import { BaseEvent } from 'main.core.events';

import { Messenger } from 'im.public';
import { LocalStorageKey, ChatType } from 'im.v2.const';
import { CallManager } from 'im.v2.lib.call';
import { LocalStorageManager } from 'im.v2.lib.local-storage';
import { Analytics } from 'im.v2.lib.analytics';

import { CallMenu } from './classes/call-menu';
import { CallTypes } from './types/call-types';

import { hint } from 'ui.vue3.directives.hint';

import './css/call-button.css';

import type { ImModelChat } from 'im.v2.model';

// @vue/component
export const CallButton = {
	directives: { hint },
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
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.dialogId, true);
		},
		chatId(): number
		{
			return this.dialog.chatId;
		},
		isConference(): boolean
		{
			return this.dialog.type === ChatType.videoconf;
		},
		callButtonText(): string
		{
			const locCode = CallTypes[this.lastCallType].locCode;

			return this.loc(locCode);
		},
		hasActiveCurrentCall(): boolean
		{
			return CallManager
				.getInstance()
				.hasActiveCurrentCall(this.dialogId);
		},
		hasActiveAnotherCall(): boolean
		{
			return CallManager
				.getInstance()
				.hasActiveAnotherCall(this.dialogId);
		},
		isActive(): boolean
		{
			if (
				this.hasActiveCurrentCall
			)
			{
				return true;
			}

			if (this.hasActiveAnotherCall)
			{
				return false;
			}

			return CallManager
				.getInstance()
				.chatCanBeCalled(this.dialogId);
		},
		userLimit(): number
		{
			return CallManager
				.getInstance()
				.getCallUserLimit();
		},
		isChatUserLimitExceeded(): boolean
		{
			return CallManager
				.getInstance()
				.isChatUserLimitExceeded(this.dialogId);
		},
		hintContent(): Object | null
		{
			if (this.isChatUserLimitExceeded)
			{
				return {
					text: this.loc('IM_LIB_CALL_USER_LIMIT_EXCEEDED_TOOLTIP', { '#USER_LIMIT#': this.userLimit }),
					popupOptions: {
						bindOptions: {
							position: 'bottom',
						},
						angle: { position: 'top' },
						targetContainer: document.body,
						offsetLeft: 63,
						offsetTop: 0,
					},
				};
			}

			return null;
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

			Analytics.getInstance().onStartCallClick({
				type: this.dialog.type === ChatType.user
					? Analytics.AnalyticsType.privateCall
					: Analytics.AnalyticsType.groupCall,
				section: Analytics.AnalyticsSection.chatWindow,
				subSection: Analytics.AnalyticsSubSection.window,
				element: this.lastCallType === CallTypes.video.id
					? Analytics.AnalyticsElement.videocall
					: Analytics.AnalyticsElement.audiocall,
				chatId: this.chatId,
			});

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
			if (!this.isActive)
			{
				return;
			}

			Analytics.getInstance().onStartConferenceClick({
				element: Analytics.AnalyticsElement.startButton,
				chatId: this.chatId,
			});

			Messenger.openConference({ code: this.dialog.public.code });
		},
		getLastCallChoice(): string
		{
			const result = LocalStorageManager.getInstance().get(LocalStorageKey.lastCallType, CallTypes.video.id);
			if (result === CallTypes.beta.id)
			{
				return CallTypes.video.id;
			}

			return result;
		},
		saveLastCallChoice(callTypeId: string)
		{
			this.lastCallType = callTypeId;
			LocalStorageManager.getInstance().set(LocalStorageKey.lastCallType, callTypeId);
		},
		shouldShowMenu(): boolean
		{
			return this.isActive;
		},
		loc(phraseCode: string, replacements: {[p: string]: string} = {}): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
		},
	},
	template: `
		<div
			v-if="isConference"
			class="bx-im-chat-header-call-button__scope bx-im-chat-header-call-button__container --conference"
			:class="{'--disabled': !isActive}"
			@click="onStartConferenceClick"
		>
			<div class="bx-im-chat-header-call-button__text">
				{{ loc('IM_CONTENT_CHAT_HEADER_START_CONFERENCE') }}
			</div>
		</div>
		<div
			v-else
			class="bx-im-chat-header-call-button__scope bx-im-chat-header-call-button__container"
			:class="{'--disabled': !isActive}"
			v-hint="hintContent"
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
