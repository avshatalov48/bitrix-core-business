import { EventEmitter, BaseEvent } from 'main.core.events';

import { EventType } from 'im.v2.const';

import { ChatOpener } from './components/openers/chat/chat';
import { CommentsOpener } from './components/openers/comments/comments';

import './css/chat-content.css';

import type { JsonObject } from 'main.core';
import type { ImModelLayout } from 'im.v2.model';

// @vue/component
export const ChatContent = {
	name: 'ChatContent',
	components: { ChatOpener, CommentsOpener },
	props:
	{
		entityId: {
			type: String,
			default: '',
		},
	},
	data(): JsonObject
	{
		return {
			commentsPostId: 0,
			commentsAnimationFlag: false,
		};
	},
	computed:
	{
		layout(): ImModelLayout
		{
			return this.$store.getters['application/getLayout'];
		},
		showComments(): boolean
		{
			return this.$store.getters['messages/comments/areOpened'];
		},
	},
	watch:
	{
		layout()
		{
			this.closeComments();
		},
	},
	created()
	{
		EventEmitter.subscribe(EventType.dialog.openComments, this.onOpenComments);
		EventEmitter.subscribe(EventType.dialog.closeComments, this.onCloseComments);
	},
	beforeUnmount()
	{
		EventEmitter.unsubscribe(EventType.dialog.openComments, this.onOpenComments);
		EventEmitter.unsubscribe(EventType.dialog.closeComments, this.onCloseComments);
	},
	methods:
	{
		onOpenComments(event: BaseEvent<{ messageId: number }>)
		{
			const { messageId } = event.getData();
			this.commentsPostId = messageId;
			this.commentsAnimationFlag = true;
			this.$store.dispatch('messages/comments/setOpened', {
				channelDialogId: this.entityId,
				commentsPostId: this.commentsPostId,
			});
		},
		onCloseComments()
		{
			this.closeComments();
		},
		closeComments()
		{
			this.commentsPostId = 0;
			this.$store.dispatch('messages/comments/setClosed');
		},
		onCommentsAnimationEnd()
		{
			this.commentsAnimationFlag = false;
		},
	},
	template: `
		<ChatOpener :dialogId="entityId" :class="{'--comments-show-animation': commentsAnimationFlag}" />
		<Transition name="comments-content" @after-enter="onCommentsAnimationEnd">
			<CommentsOpener
				v-if="showComments"
				:postId="commentsPostId"
				:channelId="entityId"
			/>
		</Transition>
	`,
};
