import { Type } from 'main.core';

import { ActionByRole, ChatType } from 'im.v2.const';
import { PermissionManager } from 'im.v2.lib.permission';

import { PinnedMessage } from './pinned-message';
import { PinnedHeader } from './header/header';

import './css/pinned-messages.css';
import './css/list.css';

import type { ImModelChat, ImModelMessage } from 'im.v2.model';
import type { JsonObject } from 'main.core';

// @vue/component
export const PinnedMessages = {
	name: 'PinnedMessages',
	components:
	{
		PinnedMessage,
		PinnedHeader,
	},
	props:
	{
		dialogId: {
			type: String,
			default: '',
		},
		messages: {
			type: Array,
			required: true,
		},
	},
	emits: ['messageClick', 'messageUnpin'],
	data(): JsonObject
	{
		return {
			isListOpened: false,
			upcomingMessageIndex: 0,
		};
	},
	computed:
	{
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.dialogId, true);
		},
		sortedPinnedMessages(): ImModelMessage[]
		{
			return [...this.messages].sort((a, b) => b.id - a.id);
		},
		totalPinCounter(): number
		{
			return this.messages.length;
		},
		canUnpin(): boolean
		{
			return PermissionManager.getInstance().canPerformActionByRole(ActionByRole.pinMessage, this.dialogId);
		},
		showUnpinIcon(): boolean
		{
			return !this.isCommentChat && this.canUnpin;
		},
		isCommentChat(): boolean
		{
			return this.dialog.type === ChatType.comment;
		},
		upcomingMessage(): ImModelMessage
		{
			return this.sortedPinnedMessages[this.upcomingMessageIndex];
		},
		upcomingMessageDisplayPosition(): number
		{
			return this.upcomingMessageIndex + 1;
		},
	},
	watch:
	{
		messages(newValue: Array<ImModelMessage>): void
		{
			if (newValue.length === 1)
			{
				this.toggleList(false);
			}

			if (this.shouldResetIndex(this.upcomingMessageIndex))
			{
				this.resetHeaderIndex();
			}
		},
	},
	methods:
	{
		toggleList(flag: boolean | undefined): void
		{
			this.isListOpened = Type.isUndefined(flag) ? !this.isListOpened : flag;
		},

		incrementHeaderIndex(): void
		{
			const nextIndex = this.upcomingMessageIndex + 1;

			if (this.shouldResetIndex(nextIndex))
			{
				this.resetHeaderIndex();
			}
			else
			{
				this.upcomingMessageIndex = nextIndex;
			}
		},

		resetHeaderIndex(): void
		{
			this.upcomingMessageIndex = 0;
		},

		shouldResetIndex(index: number): boolean
		{
			return index >= this.totalPinCounter;
		},

		clickOnHeaderMessage(): void
		{
			this.emitMessageClick(this.sortedPinnedMessages[this.upcomingMessageIndex].id);
			this.incrementHeaderIndex();
		},

		clickOnHeaderMessageFromList(index: number): void
		{
			// manually setting the next index because the item is selected directly from the list
			const nextIndex = index + 1;
			this.upcomingMessageIndex = this.shouldResetIndex(nextIndex) ? 0 : nextIndex;
			this.emitMessageClick(this.sortedPinnedMessages[index].id);
		},

		emitMessageClick(messageId: number): void
		{
			this.$emit('messageClick', messageId);
		},
	},
	template: `
		<div class="bx-im-dialog-chat__pinned_container">
			<PinnedHeader
				:message="upcomingMessage"
				:messagePosition="upcomingMessageDisplayPosition"
				:showUnpinIcon="showUnpinIcon"
				:totalPinCounter="totalPinCounter"
				:isListOpened="isListOpened"
				@toggleList="toggleList"
				@messageUnpin="$emit('messageUnpin', upcomingMessage.id)"
				@messageClick="clickOnHeaderMessage"
			/>
			<transition name="pinned-list">
				<div v-if="isListOpened" class="bx-im-dialog-chat__pinned_list">
					<PinnedMessage
						v-for="(message, index) in sortedPinnedMessages"
						:key="message.id"
						:message="message"
						:showUnpinIcon="showUnpinIcon"
						@messageUnpin="$emit('messageUnpin', message.id)"
						@click="clickOnHeaderMessageFromList(index)"
					/>
				</div>
			</transition>
		</div>
	`,
};
