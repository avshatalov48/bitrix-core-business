import { EventEmitter } from 'main.core.events';
import { hint } from 'ui.vue3.directives.hint';
import { Core } from 'im.v2.application.core';

import { Analytics } from 'im.v2.lib.analytics';
import { ActionByRole, EventType } from 'im.v2.const';
import { PermissionManager } from 'im.v2.lib.permission';
import { ChatButton, ButtonSize, ButtonIcon, ButtonColor } from 'im.v2.component.elements.button';
import { ForwardPopup } from 'im.v2.component.entity-selector';
import { showDeleteMessagesConfirm } from 'im.v2.lib.confirm';
import { MessageService } from 'im.v2.provider.service.message';

import type { ImModelChat } from 'im.v2.model';

import '../css/bulk-actions-panel.css';

// @vue/component
export const BulkActionsPanel = {
	name: 'BulkActionsPanel',
	components: { ChatButton, ForwardPopup },
	directives: { hint },
	props:
	{
		dialogId: {
			type: String,
			default: '',
		},
	},
	data(): Object
	{
		return {
			showForwardPopup: false,
			messagesIds: [],
		};
	},
	computed:
	{
		ButtonSize: () => ButtonSize,
		ButtonIcon: () => ButtonIcon,
		ButtonColor: () => ButtonColor,
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.dialogId, true);
		},
		selectedMessages(): Set<number>
		{
			return this.$store.getters['messages/select/getCollection'](this.dialogId);
		},
		messagesAuthorId(): number[]
		{
			return [...this.selectedMessages].map((messageId) => {
				return this.$store.getters['messages/getById'](messageId).authorId;
			});
		},
		hasOthersMessages(): boolean
		{
			const userId = Core.getUserId();

			return this.messagesAuthorId.some((authorId) => authorId !== userId);
		},
		canDeleteMessage(): boolean
		{
			const permissionManager = PermissionManager.getInstance();

			return permissionManager.canPerformActionByRole(
				ActionByRole.deleteOthersMessage,
				this.dialogId,
			);
		},
		selectedMessagesSize(): number
		{
			return this.selectedMessages.size;
		},
		formattedMessagesCounter(): string
		{
			if (!this.selectedMessagesSize)
			{
				return '';
			}

			return `(${this.selectedMessagesSize})`;
		},
		isBlockedDeletion(): boolean
		{
			if (this.canDeleteMessage)
			{
				return false;
			}

			return this.hasOthersMessages;
		},
		messageCounterText(): string
		{
			if (!this.selectedMessagesSize)
			{
				return this.loc('IM_CONTENT_BULK_ACTIONS_SELECT_MESSAGES');
			}

			return this.loc('IM_CONTENT_BULK_ACTIONS_COUNT_MESSAGES');
		},
		confirmTitle(): string
		{
			return this.loc('IM_CONTENT_BULK_ACTIONS_CONFIRM_TITLE', { '#COUNT#': this.selectedMessagesSize });
		},
		tooltipSettings(): { text: string, popupOptions: Object<string, any> }
		{
			return {
				text: this.loc('IM_CONTENT_BULK_ACTIONS_DELETE_NOT_CAN_DELETE'),
				popupOptions: {
					angle: true,
					targetContainer: document.body,
					offsetTop: -13,
					offsetLeft: 65,
					bindOptions: {
						position: 'top',
					},
				},
			};
		},
	},
	methods:
	{
		onForwardButtonClick()
		{
			Analytics.getInstance().messageForward.onClickForward(this.dialogId);
			this.messagesIds = [...this.selectedMessages];
			this.showForwardPopup = true;
		},
		closeForwardPopup()
		{
			this.messagesIds = [];
			this.showForwardPopup = false;
		},
		async onDeleteButtonClick(): Promise<boolean>
		{
			const confirmResult = await showDeleteMessagesConfirm(this.confirmTitle);

			if (!confirmResult)
			{
				return false;
			}

			this.getMessageService().deleteMessages([...this.selectedMessages]);
			this.closeBulkActionsMode();

			return true;
		},
		closeBulkActionsMode()
		{
			EventEmitter.emit(EventType.dialog.closeBulkActionsMode, {
				dialogId: this.dialogId,
			});
		},
		getMessageService(): MessageService
		{
			if (!this.messageService)
			{
				this.messageService = new MessageService({ chatId: this.dialog.chatId });
			}

			return this.messageService;
		},
		loc(phraseCode: string, replacements: {[string]: string} = {}): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
		},
	},
	template: `
		<div class="bx-im-content-bulk-actions-panel">
			<div class="bx-im-content-bulk-actions-panel__container">
				<div class="bx-im-content-bulk-actions-panel__left-section">
					<div @click="closeBulkActionsMode" class="bx-im-content-bulk-actions-panel__cancel"></div>
					<div class="bx-im-content-bulk-actions-panel__counter-container">
						<span class="bx-im-content-bulk-actions-panel__counter-name">{{ messageCounterText }}</span>
						<span class="bx-im-content-bulk-actions-panel__counter-number">{{ formattedMessagesCounter }}</span>
					</div>
				</div>
				<div class="bx-im-content-bulk-actions-panel__right-section">
					<div class="bx-im-content-bulk-actions-panel__delete-button">
						<div
							v-if="isBlockedDeletion"
							v-hint="tooltipSettings"
							class="bx-im-content-bulk-actions-panel__tooltip"
						>
						</div>
						<ChatButton
							:size="ButtonSize.L"
							:icon="ButtonIcon.Delete"
							:color="ButtonColor.Delete"
							:isDisabled="!selectedMessagesSize || isBlockedDeletion"
							:isRounded="true"
							:isUppercase="false"
							:text="loc('IM_CONTENT_BULK_ACTIONS_PANEL_DELETE')"
							@click="onDeleteButtonClick"
						/>
					</div>
					<ChatButton
						:size="ButtonSize.L"
						:icon="ButtonIcon.Forward"
						:color="ButtonColor.Forward"
						:isRounded="true"
						:isUppercase="false"
						:isDisabled="!selectedMessagesSize"
						:text="loc('IM_CONTENT_BULK_ACTIONS_PANEL_FORWARD')"
						@click="onForwardButtonClick"
					/>
				</div>
				<ForwardPopup
					v-if="showForwardPopup"
					:messagesIds="messagesIds"
					:dialogId="dialogId"
					@close="closeForwardPopup"
				/>
			</div>
		</div>
	`,
};
