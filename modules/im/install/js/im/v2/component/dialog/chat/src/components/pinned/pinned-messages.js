import {PinnedMessage} from './pinned-message';

import '../../css/pinned-messages.css';

import type {ImModelMessage} from 'im.v2.model';

// @vue/component
export const PinnedMessages = {
	components: {PinnedMessage},
	props:
	{
		messages: {
			type: Array,
			required: true
		}
	},
	emits: ['messageClick', 'messageUnpin'],
	data()
	{
		return {};
	},
	computed:
	{
		firstMessage(): ImModelMessage
		{
			return this.messagesToShow[0];
		},
		messagesToShow(): ImModelMessage[]
		{
			return this.messages.slice(-1);
		}
	},
	methods:
	{
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		}
	},
	template: `
		<div @click="$emit('messageClick', firstMessage.id)" class="bx-im-dialog-chat__pinned_container">
			<div class="bx-im-dialog-chat__pinned_title">{{ loc('IM_DIALOG_CHAT_PINNED_TITLE') }}</div>
			<PinnedMessage
				v-for="message in messagesToShow"
				:message="message"
				:key="message.id"
				@click="$emit('messageClick', message.id)"
			/>
			<div @click.stop="$emit('messageUnpin', firstMessage.id)" class="bx-im-dialog-chat__pinned_unpin"></div>
		</div>
	`
};