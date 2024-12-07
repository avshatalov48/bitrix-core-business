import { ChatAvatar, AvatarSize, ChatTitle } from 'im.v2.component.elements';
import { Utils } from 'im.v2.lib.utils';

import type { ImModelBot, ImModelUser } from 'im.v2.model';

// @vue/component
export const DetailUser = {
	name: 'DetailUser',
	components: { ChatAvatar, ChatTitle },
	props: {
		dialogId: {
			type: String,
			required: true,
		},
		contextDialogId: {
			type: String,
			required: true,
		},
		isOwner: {
			type: Boolean,
			default: false,
		},
		isManager: {
			type: Boolean,
			default: false,
		},
	},
	data(): {showContextButton: boolean}
	{
		return {
			showContextButton: false,
		};
	},
	computed:
	{
		AvatarSize: () => AvatarSize,
		position(): string
		{
			if (this.isCopilot)
			{
				return this.$store.getters['copilot/getProvider'];
			}

			return this.$store.getters['users/getPosition'](this.dialogId);
		},
		user(): ImModelUser
		{
			return this.$store.getters['users/get'](this.dialogId, true);
		},
		userLink(): string
		{
			return Utils.user.getProfileLink(this.dialogId);
		},
		needContextMenu(): boolean
		{
			const bot: ImModelBot = this.$store.getters['users/bots/getByUserId'](this.dialogId);
			if (!bot)
			{
				return true;
			}

			return bot.code !== 'copilot';
		},
		isCopilot(): boolean
		{
			const userId = Number.parseInt(this.dialogId, 10);

			return this.$store.getters['users/bots/isCopilot'](userId);
		},
		hasLink(): boolean
		{
			return !this.isCopilot;
		},
	},
	methods:
	{
		onClickContextMenu(event)
		{
			this.$emit('contextMenuClick', {
				userDialogId: this.dialogId,
				target: event.currentTarget,
			});
		},
	},
	template: `
		<div
			class="bx-im-sidebar-main-detail__user"
			@mouseover="showContextButton = true"
			@mouseleave="showContextButton = false"
		>
			<div class="bx-im-sidebar-main-detail__avatar-container">
				<ChatAvatar 
					:size="AvatarSize.L"
					:avatarDialogId="dialogId"
					:contextDialogId="contextDialogId"
				/>
				<span v-if="isOwner" class="bx-im-sidebar-main-detail__avatar-owner-icon"></span>
				<span v-else-if="isManager" class="bx-im-sidebar-main-detail__avatar-manager-icon"></span>
			</div>
			<div class="bx-im-sidebar-main-detail__user-info-container">
				<div class="bx-im-sidebar-main-detail__user-title-container">
					<a v-if="hasLink" :href="userLink" target="_blank" class="bx-im-sidebar-main-detail__user-title-link">
						<ChatTitle :dialogId="dialogId" :withLeftIcon="!isCopilot" />
					</a>
					<div v-else class="bx-im-sidebar-main-detail__user-title-link">
						<ChatTitle :dialogId="dialogId" :withLeftIcon="!isCopilot" />
					</div>
					<div
						v-if="needContextMenu && showContextButton"
						class="bx-im-sidebar-main-detail__context-menu-icon bx-im-messenger__context-menu-icon"
						@click="onClickContextMenu"
					></div>
				</div>
				<div class="bx-im-sidebar-main-detail__position-text" :title="position">
					{{ position }}
				</div>
			</div>
		</div>	
	`,
};
