import { SendingService } from 'im.v2.provider.service';
import { BaseMessage } from 'im.v2.component.message.base';
import { AvatarSize, MessageAvatar } from 'im.v2.component.elements';

import './css/copilot-creation-message.css';

import type { ImModelMessage, ImModelCopilotPrompt, ImModelCopilotRole } from 'im.v2.model';

// @vue/component
export const ChatCopilotCreationMessage = {
	name: 'ChatCopilotCreationMessage',
	components: { BaseMessage, MessageAvatar },
	props: {
		item: {
			type: Object,
			required: true,
		},
		dialogId: {
			type: String,
			required: true,
		},
	},
	computed:
	{
		AvatarSize: () => AvatarSize,
		message(): ImModelMessage
		{
			return this.item;
		},
		preparedTitle(): string
		{
			const phrase = this.message.componentParams?.copilotRoleUpdated
				? 'IM_MESSAGE_COPILOT_CREATION_HEADER_TITLE_AFTER_CHANGE'
				: 'IM_MESSAGE_COPILOT_CREATION_HEADER_TITLE'
			;

			return this.loc(phrase, {
				'#COPILOT_ROLE_NAME#': this.roleName,
			});
		},
		promptList(): ImModelCopilotPrompt[]
		{
			return this.$store.getters['copilot/messages/getPrompts'](this.message.id);
		},
		role(): ImModelCopilotRole
		{
			return this.$store.getters['copilot/messages/getRole'](this.message.id);
		},
		roleName(): string
		{
			return this.role.name;
		},
	},
	methods:
	{
		onMessageClick(prompt: ImModelCopilotPrompt)
		{
			void this.getSendingService().sendCopilotPrompt({
				text: prompt.text,
				copilot: {
					promptCode: prompt.code,
				},
				dialogId: this.dialogId,
			});
		},
		getSendingService(): SendingService
		{
			if (!this.sendingService)
			{
				this.sendingService = SendingService.getInstance();
			}

			return this.sendingService;
		},
		loc(phraseCode: string, replacements: {[p: string]: string} = {}): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
		},
	},
	template: `
		<BaseMessage
			:dialogId="dialogId"
			:item="item"
			:withContextMenu="false"
			:withReactions="false"
			:withBackground="false"
		>
			<div class="bx-im-message-copilot-creation__container">
				<div class="bx-im-message-copilot-creation__header">
					<MessageAvatar 
						:messageId="message.id"
						:authorId="message.authorId"
						:size="AvatarSize.XXL"
					/>
					<div class="bx-im-message-copilot-creation__info">
						<div class="bx-im-message-copilot-creation__title" :title="preparedTitle">
							{{ preparedTitle }}
						</div>
						<div 
							class="bx-im-message-copilot-creation__text" 
							:title="loc('IM_MESSAGE_COPILOT_CREATION_HEADER_DESC')"
						>
							{{ loc('IM_MESSAGE_COPILOT_CREATION_HEADER_DESC') }}
						</div>
					</div>
				</div>
				<div class="bx-im-message-copilot-creation__separator"><div></div></div>
				<div class="bx-im-message-copilot-creation__actions">
					<div
						v-for="prompt in promptList"
						:key="prompt.code"
						@click="onMessageClick(prompt)"
						class="bx-im-message-copilot-creation__action"
					>
						<span class="bx-im-message-copilot-creation__action-text">
							{{ prompt.title }}
						</span>
					</div>
				</div>
			</div>
		</BaseMessage>
	`,
};
