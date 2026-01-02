import { BaseMessage } from 'im.v2.component.message.base';
import { AuthorTitle, DefaultMessageContent, MessageFooter } from 'im.v2.component.message.elements';

import { VoteDisplay } from 'vote.component.vote';
import { VoteAnalytics } from 'vote.analytics';

import './style.css';

// @vue/component
export const VoteChatDisplay = {
	name: 'VoteChatDisplay',
	components: {
		BaseMessage,
		VoteDisplay,
		AuthorTitle,
		MessageFooter,
		DefaultMessageContent,
	},
	props:
	{
		/** @type {ImModelMessage} */
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
		savedMessageId(): number
		{
			return Number.parseInt(this.item.id, 10);
		},

		voteItem(): Object | null
		{
			return this.item.componentParams;
		},
	},
	methods:
	{
		onVote(): void
		{
			VoteAnalytics.vote(this.dialogId, this.savedMessageId);
		},
		onRevokeVote(): void
		{
			VoteAnalytics.revokeVote(this.dialogId, this.savedMessageId);
		},
		onCopyLink(): void
		{
			VoteAnalytics.copyLink(this.dialogId, this.savedMessageId, 'poll_results');
		},
		onCompleteVote(): void
		{
			VoteAnalytics.completeVote(this.dialogId, this.savedMessageId);
		},
	},
	template: `
		<BaseMessage
			:dialogId="dialogId"
			:item="item"
			:withBackground="true"
		>
			<div class="bx-im-chat__vote-container">
				<AuthorTitle :item="item" class="bx-im-chat-title__vote"/>
				<div class="bx-im-chat-title__vote-icon"></div>
				<VoteDisplay
					:voteItem="voteItem"
					:entityId="savedMessageId"
					:entityType="'ImMessage'"
					:contextId="dialogId"
					@vote="onVote"
					@revokeVote="onRevokeVote"
					@copyLink="onCopyLink"
					@completeVote="onCompleteVote"
				/>
				<DefaultMessageContent
					:item="item"
					:dialogId="dialogId"
					:withText="false"
					:withAttach="false"
					class="bx-im-message-default-content__vote"/>
				<MessageFooter :item="item" :dialogId="dialogId" />
			</div>
		</BaseMessage>
	`,
};
