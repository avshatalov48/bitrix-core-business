import { ChannelManager } from 'im.v2.lib.channel';
import { Toggle, ToggleSize } from 'im.v2.component.elements.toggle';
import { AutoDeleteDropdown, AutoDeletePopup, AutoDeleteHint } from 'im.v2.component.elements.auto-delete';
import { Feature, FeatureManager } from 'im.v2.lib.feature';
import { ChatService } from 'im.v2.provider.service.chat';
import { ChatType, UserType, AutoDeleteDelay } from 'im.v2.const';
import { AutoDeleteManager } from 'im.v2.lib.auto-delete';

import './css/auto-delete.css';

import type { JsonObject } from 'main.core';
import type { ImModelChat, ImModelUser } from 'im.v2.model';

// @vue/component
export const AutoDelete = {
	name: 'AutoDelete',
	components: { Toggle, AutoDeleteHint, AutoDeleteDropdown, AutoDeletePopup },
	props:
	{
		dialogId: {
			type: String,
			required: true,
		},
	},
	data(): JsonObject
	{
		return {
			showHint: false,
			showPopup: false,
		};
	},
	computed:
	{
		ToggleSize: () => ToggleSize,
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.dialogId, true);
		},
		chatId(): number
		{
			return this.dialog.chatId;
		},
		autoDeleteDelayInHours(): number
		{
			return this.$store.getters['chats/autoDelete/getDelay'](this.chatId);
		},
		isAutoDeleteFeatureEnabled(): boolean
		{
			return FeatureManager.isFeatureAvailable(Feature.messagesAutoDeleteEnabled);
		},
		isAutoDeleteAllowed(): boolean
		{
			return AutoDeleteManager.isAutoDeleteAllowed(this.dialogId);
		},
		isBot(): boolean
		{
			const user: ImModelUser = this.$store.getters['users/get'](this.dialogId, true);

			return user.type === UserType.bot;
		},
		isAutoDeleteAvailableByChatType(): boolean
		{
			const NoAutoDeleteChatTypes = [
				ChatType.copilot,
				ChatType.lines,
				ChatType.videoconf,
				...ChannelManager.getChannelTypes(),
			];

			if (NoAutoDeleteChatTypes.includes(this.dialog.type))
			{
				return false;
			}

			return !this.isBot;
		},
		isAutoDeleteActive(): boolean
		{
			return this.autoDeleteDelayInHours > 0;
		},
	},
	methods:
	{
		getChatService(): ChatService
		{
			if (!this.chatService)
			{
				this.chatService = new ChatService();
			}

			return this.chatService;
		},
		changeAutoDeleteActionHandler(): void
		{
			if (!this.isAutoDeleteAllowed)
			{
				this.showHint = true;

				return;
			}

			if (this.isAutoDeleteActive)
			{
				this.updateAutoDeleteDelay(AutoDeleteDelay.Off);

				return;
			}

			if (!this.isAutoDeleteFeatureEnabled)
			{
				FeatureManager.messagesAutoDelete.openFeatureSlider();

				return;
			}

			this.showPopup = true;
		},
		updateAutoDeleteDelay(delay: number): void
		{
			this.getChatService().setMessagesAutoDeleteDelay(this.dialogId, delay);
		},
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
		onAutoDeleteDelayChange(delay: number): void
		{
			this.updateAutoDeleteDelay(delay);
			this.$emit('close');
		},
		onDropDownClick(event: PointerEvent)
		{
			if (this.isAutoDeleteAllowed)
			{
				return;
			}

			event.stopPropagation();
			this.showHint = true;
		},
	},
	template: `
		<div
			v-if="isAutoDeleteAvailableByChatType"
			class="bx-im-sidebar-auto-delete__container"
			ref="auto-delete"
		>
			<div class="bx-im-sidebar-auto-delete__title">
				<div class="bx-im-sidebar-auto-delete__title-text bx-im-sidebar-auto-delete__icon">
					{{ loc('IM_SIDEBAR_ENABLE_AUTODELETE_TITLE') }}
				</div>
				<Toggle
					:size="ToggleSize.M"
					:isEnabled="isAutoDeleteActive"
					:disabled="!isAutoDeleteAllowed"
					@click="changeAutoDeleteActionHandler"
				/>
			</div>
			<AutoDeleteDropdown
				:currentDelay="autoDeleteDelayInHours"
				@delayChange="updateAutoDeleteDelay"
				@click.capture="onDropDownClick"
			/>
			<AutoDeleteHint 
				v-if="showHint"
				:bindElement="$refs['auto-delete']" 
				@close="showHint = false"
			/>
			<AutoDeletePopup
				v-if="showPopup"
				:autoDeleteDelay="autoDeleteDelayInHours"
				@close="showPopup = false"
				@autoDeleteDelayChange="onAutoDeleteDelayChange"
			/>
		</div>
	`,
};
