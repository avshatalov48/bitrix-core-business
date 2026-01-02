import { Type } from 'main.core';
import { Lottie } from 'ui.lottie';

import { InputAction } from 'im.v2.lib.input-action';

import RecordingAnimation from '../animations/recording.json';
import UploadingAnimation from '../animations/uploading.json';
import WritingAnimation from '../animations/writing.json';

import type { ImModelInputActions } from 'im.v2.model';

type InputActionUserRecord = {
	userId: number,
	userName: string
};

const USERS_TO_SHOW = 3;
const AnimationByActionType = {
	[InputAction.writing]: WritingAnimation,
	[InputAction.sendingFile]: UploadingAnimation,
	[InputAction.recordingVoice]: RecordingAnimation,
};

const LocCodeByActionType = {
	[InputAction.writing]: 'IM_MESSAGE_LIST_STATUS_TYPING_MSGVER_1',
	[InputAction.sendingFile]: 'IM_MESSAGE_LIST_STATUS_SENDING_FILE_MSGVER_1',
	[InputAction.recordingVoice]: 'IM_MESSAGE_LIST_STATUS_RECORDING_VOICE_MSGVER_1',
};

// @vue/component
export const InputActions = {
	name: 'InputActions',
	props:
	{
		dialogId: {
			required: true,
			type: String,
		},
		enterAnimationFinished: {
			required: true,
			type: Boolean,
		},
	},
	computed:
	{
		chatInputActions(): ImModelInputActions
		{
			return this.$store.getters['chats/inputActions/getByDialogId'](this.dialogId);
		},
		uniqueUserRecords(): InputActionUserRecord[]
		{
			const uniqueUserRecords = {};
			this.chatInputActions.forEach((userRecord) => {
				uniqueUserRecords[userRecord.userId] = userRecord.userName;
			});

			return Object.entries(uniqueUserRecords).map(([userId, userName]) => {
				return { userId, userName };
			});
		},
		isSingleUserActive(): boolean
		{
			const userIds = this.chatInputActions.map((element) => element.userId);
			const uniqueUsers = new Set(userIds);

			return uniqueUsers.size === 1;
		},
		usersToShowText(): string
		{
			const usersToShow = this.uniqueUserRecords.slice(0, USERS_TO_SHOW);

			return usersToShow.map((element) => element.userName).join(', ');
		},
		inputActionText(): string
		{
			if (this.isSingleUserActive)
			{
				return this.getActionTextForSingleUser();
			}

			// Ivan, Alex, Rob and 7 more are writing
			const remainingUsersCount = this.uniqueUserRecords.length - USERS_TO_SHOW;
			if (remainingUsersCount > 0)
			{
				return this.loc('IM_MESSAGE_LIST_STATUS_TYPING_PLURAL_MORE_MSGVER_1', {
					'#USERS#': this.usersToShowText,
					'#COUNT#': remainingUsersCount,
				});
			}

			// Ivan, Alex, Rob are writing
			return this.loc('IM_MESSAGE_LIST_STATUS_TYPING_PLURAL_MSGVER_1', {
				'#USERS#': this.usersToShowText,
			});
		},
		animationName(): string
		{
			if (this.isSingleUserActive)
			{
				const [firstUserRecord] = this.chatInputActions;
				const { type } = firstUserRecord;

				return AnimationByActionType[type];
			}

			return WritingAnimation;
		},
	},
	watch:
	{
		enterAnimationFinished()
		{
			if (this.currentAnimation)
			{
				return;
			}

			this.playAnimation(this.animationName);
		},
		animationName(newAnimation: string)
		{
			this.stopAnimation();
			this.playAnimation(newAnimation);
		},
	},
	mounted()
	{
		if (!this.$refs.animationContainer)
		{
			return;
		}

		this.playAnimation(this.animationName);
	},
	beforeUnmount()
	{
		this.stopAnimation();
	},
	methods:
	{
		getActionTextForSingleUser(): string
		{
			const [firstUserRecord] = this.chatInputActions;
			const { type, userName, statusMessageCode } = firstUserRecord;
			const hasCustomMessageCode = this.hasCustomMessageCode(statusMessageCode);
			const code = hasCustomMessageCode ? statusMessageCode : LocCodeByActionType[type];

			return this.loc(code, { '#USER#': userName });
		},
		playAnimation(animationName: string): void
		{
			this.currentAnimation = Lottie.loadAnimation({
				animationData: animationName,
				container: this.$refs.animationContainer,
				renderer: 'svg',
				loop: true,
				autoplay: true,
			});
		},
		stopAnimation()
		{
			if (!this.currentAnimation)
			{
				return;
			}

			this.currentAnimation.destroy();
		},
		hasCustomMessageCode(phraseCode: string | null): boolean
		{
			return Type.isStringFilled(phraseCode) && Type.isStringFilled(this.loc(phraseCode));
		},
		loc(phraseCode: string, replacements: {[string]: string} = {}): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
		},
	},
	template: `
		<div class="bx-im-dialog-chat-status__content">
			<div class="bx-im-dialog-chat-status__animation" ref="animationContainer"></div>
			<div class="bx-im-dialog-chat-status__text">{{ inputActionText }}</div>
		</div>
	`,
};
