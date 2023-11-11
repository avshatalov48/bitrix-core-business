import { BaseMessage } from 'im.v2.component.message.base';
import { MessageStatus, ReactionList, ReactionSelector, ContextMenu } from 'im.v2.component.message.elements';
import { UserRole } from 'im.v2.const';
import { Parser } from 'im.v2.lib.parser';

import './css/smile.css';

import type { ImModelDialog, ImModelMessage } from 'im.v2.model';

// @vue/component
export const SmileMessage = {
	name: 'SmileMessage',
	components: {
		BaseMessage,
		MessageStatus,
		ReactionList,
		ReactionSelector,
		ContextMenu,
	},
	props: {
		item: {
			type: Object,
			required: true,
		},
		dialogId: {
			type: String,
			required: true,
		},
		menuIsActiveForId: {
			type: [String, Number],
			default: 0,
		},
	},
	computed:
	{
		message(): ImModelMessage
		{
			return this.item;
		},
		text(): string
		{
			return Parser.decodeSmile(this.message.text, {
				ratioConfig: {
					Default: 1,
					Big: 3,
				},
				enableBigSmile: true,
			});
		},
	},
	template: `
		<BaseMessage
			:dialogId="dialogId"
			:item="item"
			:withBackground="false"
			:withDefaultContextMenu="false"
		>
			<div class="bx-im-message-smile__container">
				<div class="bx-im-message-smile__content-container">
					<span class="bx-im-message-smile__text" v-html="text"></span>
					<div class="bx-im-message-smile__message-status-container">
						<MessageStatus :item="message" :isOverlay="true" />
					</div>
					<ReactionSelector :messageId="message.id" />
				</div>
				<ContextMenu :message="message" :menuIsActiveForId="menuIsActiveForId" />
			</div>
			<div class="bx-im-message-smile__reactions-container">
				<ReactionList :messageId="message.id" class="bx-im-message-smile__reactions" />
			</div>
		</BaseMessage>
	`,
};
