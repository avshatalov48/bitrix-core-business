/* eslint-disable */
this.BX = this.BX || {};
this.BX.Vote = this.BX.Vote || {};
(function (exports,im_v2_component_message_base,im_v2_component_message_elements,vote_component_vote,vote_analytics) {
	'use strict';

	// @vue/component
	const VoteChatDisplay = {
	  name: 'VoteChatDisplay',
	  components: {
	    BaseMessage: im_v2_component_message_base.BaseMessage,
	    VoteDisplay: vote_component_vote.VoteDisplay,
	    AuthorTitle: im_v2_component_message_elements.AuthorTitle,
	    MessageFooter: im_v2_component_message_elements.MessageFooter,
	    DefaultMessageContent: im_v2_component_message_elements.DefaultMessageContent
	  },
	  props: {
	    /** @type {ImModelMessage} */
	    item: {
	      type: Object,
	      required: true
	    },
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  computed: {
	    savedMessageId() {
	      return Number.parseInt(this.item.id, 10);
	    },
	    voteItem() {
	      return this.item.componentParams;
	    }
	  },
	  methods: {
	    onVote() {
	      vote_analytics.VoteAnalytics.vote(this.dialogId, this.savedMessageId);
	    },
	    onRevokeVote() {
	      vote_analytics.VoteAnalytics.revokeVote(this.dialogId, this.savedMessageId);
	    },
	    onCopyLink() {
	      vote_analytics.VoteAnalytics.copyLink(this.dialogId, this.savedMessageId, 'poll_results');
	    },
	    onCompleteVote() {
	      vote_analytics.VoteAnalytics.completeVote(this.dialogId, this.savedMessageId);
	    }
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
	`
	};

	exports.VoteChatDisplay = VoteChatDisplay;

}((this.BX.Vote.Component = this.BX.Vote.Component || {}),BX.Messenger.v2.Component.Message,BX.Messenger.v2.Component.Message,BX.Vote.Component,BX.Vote));
//# sourceMappingURL=index.bundle.js.map
