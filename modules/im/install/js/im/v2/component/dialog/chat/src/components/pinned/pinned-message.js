import { Parser } from 'im.v2.lib.parser';
import { MessageAvatar, AvatarSize } from 'im.v2.component.elements.avatar';

import type { ImModelUser, ImModelMessage } from 'im.v2.model';

// @vue/component
export const PinnedMessage = {
	components: { MessageAvatar },
	props:
	{
		message: {
			type: Object,
			required: true,
		},
		showUnpinIcon: {
			type: Boolean,
			required: true,
		},
	},
	emits: ['messageUnpin'],
	computed:
	{
		AvatarSize: () => AvatarSize,
		typedMessage(): ImModelMessage
		{
			return this.message;
		},
		text(): string
		{
			return Parser.purifyMessage(this.typedMessage);
		},
		authorId(): number
		{
			return this.typedMessage.authorId;
		},
		author(): ImModelUser
		{
			return this.$store.getters['users/get'](this.authorId);
		},
	},
	template: `
		<div class="bx-im-dialog-chat__pinned_item">
			<MessageAvatar
				v-if="typedMessage.authorId"
				:messageId="typedMessage.id"
				:authorId="typedMessage.authorId"
				:size="AvatarSize.M"
			/>
			<div class="bx-im-dialog-chat__pinned_item_content">
				<div v-if="author" class="bx-im-dialog-chat__pinned_item_user">
					{{ author.name }}
				</div>
				<div class="bx-im-dialog-chat__pinned_item_text --ellipsis">
					{{ text }}
				</div>
			</div>
			<button
				v-if="showUnpinIcon"
				class="bx-im-dialog-chat__pinned_icon-item-unpin"
				@click.stop="$emit('messageUnpin', typedMessage.id)"
			></button>
		</div>
	`,
};
