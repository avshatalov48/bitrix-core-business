import {Avatar, AvatarSize, ChatTitle} from 'im.v2.component.elements';
import type {ImModelUser} from 'im.v2.model';

// @vue/component
export const DetailUser = {
	name: 'DetailUser',
	components: {Avatar, ChatTitle},
	props: {
		dialogId: {
			type: String,
			required: true
		},
		isModerator: {
			type: Boolean,
			default: false
		}
	},
	data() {
		return {
			showContextButton: false,
		};
	},
	computed:
	{
		AvatarSize: () => AvatarSize,
		position(): string
		{
			return this.$store.getters['users/getPosition'](this.dialogId);
		},
		user(): ImModelUser
		{
			return this.$store.getters['users/get'](this.dialogId, true);
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
		}
	},
	template: `
		<div
			class="bx-im-sidebar-main-detail__user"
			@mouseover="showContextButton = true"
			@mouseleave="showContextButton = false"
		>
			<div class="bx-im-sidebar-main-detail__avatar-container">
				<Avatar :size="AvatarSize.L" :dialogId="dialogId" />
				<span v-if="isModerator" class="bx-im-sidebar-main-detail__avatar-moderator-icon"></span>
			</div>
			<div class="bx-im-sidebar-main-detail__user-info-container">
				<div class="bx-im-sidebar-main-detail__user-title-container">
					<ChatTitle :dialogId="dialogId" class="bx-im-sidebar-main-detail__user-title-text" />
					<div
						v-if="showContextButton"
						class="bx-im-sidebar-main-detail__context-menu-icon bx-im-messenger__context-menu-icon"
						@click="onClickContextMenu"
					></div>
				</div>
				<div class="bx-im-sidebar-main-detail__position-text">
					{{ position }}
				</div>
			</div>
		</div>	
	`
};