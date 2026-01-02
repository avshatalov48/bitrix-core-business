import { AnchorType } from 'im.v2.const';
import type { ImModelChat } from 'im.v2.model';

import { ScrollButton } from './scroll-button';
import { ReactionsButton } from './reactions-button';
import { MentionsButton } from './mentions-button';

// @vue/component
export const FloatButtons = {
	components:
	{
		ScrollButton,
		ReactionsButton,
		MentionsButton,
	},
	props: {
		dialogId: {
			type: String,
			default: '',
		},
		isScrolledUp: {
			type: Boolean,
			required: false,
			default: false,
		},
	},
	emits: ['scrollButtonClick', 'reactionsButtonClick', 'mentionsButtonClick'],
	computed:
	{
		dialogInited(): boolean
		{
			return this.dialog.inited;
		},
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.dialogId, true);
		},
		showScrollButton(): boolean
		{
			return this.isScrolledUp || this.dialog.hasNextPage;
		},
		showMentionsButton(): boolean
		{
			return this.dialogInited && this.$store.getters['messages/anchors/getCounterInChatByType'](this.dialog.chatId, AnchorType.mention);
		},
		showReactionsButton(): boolean
		{
			return this.dialogInited && this.$store.getters['messages/anchors/getCounterInChatByType'](this.dialog.chatId, AnchorType.reaction);
		},
		hasAdditionalButtonSlot(): boolean
		{
			const slot = this.$slots['additional-float-button'];

			if (!slot)
			{
				return false;
			}

			const slotChildren = slot();

			/**
			 * Check that slot has empty content.
			 * Used for case when parent component passes a slot to this slot
			 * This is important for the animation to work correctly
			 */
			return slotChildren[0]?.children.length > 0;
		},
	},
	methods:
	{
		onReactionsButtonClick(): void
		{
			this.$emit('reactionsButtonClick');
		},
		onMentionsButtonClick(): void
		{
			this.$emit('mentionsButtonClick');
		},
		onScrollButtonClick(): void
		{
			this.$emit('scrollButtonClick');
		},
	},
	template: `
		<TransitionGroup
			name="float-button-transition"
			tag="div"
			class="bx-im-dialog-chat__float-buttons"
		>
			<div
				v-if="showReactionsButton"
				key="reaction"
				class="bx-im-dialog-chat__float-buttons_button"
			>
				<ReactionsButton :dialogId="dialogId" @click="onReactionsButtonClick" />
			</div>
			<div
				v-if="showMentionsButton"
				key="mention"
				class="bx-im-dialog-chat__float-buttons_button"
			>
				<MentionsButton :dialogId="dialogId" @click="onMentionsButtonClick" />
			</div>
			<div
				v-if="hasAdditionalButtonSlot"
				key="additionalButton"
				class="bx-im-dialog-chat__float-buttons_button"
			>
				<slot name="additional-float-button"></slot>
			</div>
			<div
				v-if="showScrollButton"
				key="scroll"
				class="bx-im-dialog-chat__float-buttons_button"
			>
				<ScrollButton :dialogId="dialogId" @click="onScrollButtonClick" />
			</div>
		</TransitionGroup>
	`,
};
