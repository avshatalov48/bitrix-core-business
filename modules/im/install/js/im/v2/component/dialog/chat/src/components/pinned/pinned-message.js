import {Parser} from 'im.v2.lib.parser';

import type {ImModelUser, ImModelMessage} from 'im.v2.model';

// @vue/component
export const PinnedMessage = {
	props:
	{
		message: {
			type: Object,
			required: true
		}
	},
	data()
	{
		return {};
	},
	computed:
	{
		internalMessage(): ImModelMessage
		{
			return this.message;
		},
		text(): string
		{
			return Parser.purifyMessage(this.internalMessage);
		},
		authorId(): number
		{
			return this.internalMessage.authorId;
		},
		author(): ImModelUser
		{
			return this.$store.getters['users/get'](this.authorId);
		}
	},
	template: `
		<div class="bx-im-dialog-chat__pinned_item">
			<span v-if="author" class="bx-im-dialog-chat__pinned_item_user">{{ author.name }}:</span> {{ text }}
		</div>
	`
};