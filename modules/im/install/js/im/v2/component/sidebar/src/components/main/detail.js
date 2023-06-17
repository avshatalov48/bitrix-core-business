import {Core} from 'im.v2.application.core';
import {ImModelDialog} from 'im.v2.model';
import {Button, ButtonColor, ButtonSize} from 'im.v2.component.elements';

import {DetailUser} from './detail-user';
import {SidebarDetail} from '../detail';
import {MembersMenu} from '../../classes/context-menu/main/members-menu';

import '../../css/main/detail.css';

// @vue/component
export const MainDetail = {
	name: 'MainDetail',
	components: {DetailUser, SidebarDetail, Button},
	props: {
		dialogId: {
			type: String,
			required: true
		},
		chatId: {
			type: Number,
			required: true
		},
		service: {
			type: Object,
			required: true
		}
	},
	computed:
	{
		ButtonSize: () => ButtonSize,
		ButtonColor: () => ButtonColor,
		dialog(): ImModelDialog
		{
			return this.$store.getters['dialogues/get'](this.dialogId, true);
		},
		dialogManagers(): number[]
		{
			return this.dialog.managerList;
		},
		dialogIds(): string[]
		{
			const users = this.$store.getters['sidebar/members/get'](this.chatId);

			return users.map(userId => userId.toString());
		},
		showCopyInviteButton(): boolean
		{
			// todo
			return true;
		},
		chatLink(): string
		{
			return `${Core.getHost()}/online/?IM_DIALOG=${this.dialogId}`;
		}
	},
	created()
	{
		this.contextMenu = new MembersMenu();
	},
	beforeUnmount()
	{
		this.contextMenu.destroy();
	},
	methods:
	{
		isModerator(userDialogId: string): boolean
		{
			const userId = Number.parseInt(userDialogId, 10);

			return this.dialogManagers.includes(userId);
		},
		onContextMenuClick(event)
		{
			const item = {
				dialogId: event.userDialogId,
				contextDialogId: this.dialogId,
				contextChatId: this.chatId,
			};

			this.contextMenu.openMenu(item, event.target);
		},
		onScroll()
		{
			this.contextMenu.destroy();
		},
		onCopyInviteClick()
		{
			if (BX.clipboard.copy(this.chatLink))
			{
				BX.UI.Notification.Center.notify({
					content: this.$Bitrix.Loc.getMessage('IM_SIDEBAR_COPIED_SUCCESS')
				});
			}
		}
	},
	template: `
		<SidebarDetail
			:dialogId="dialogId"
			:chatId="chatId"
			:service="service"
			@scroll="onScroll"
			v-slot="slotProps"
			class="bx-im-sidebar-main-detail__scope"
		>
			<div class="bx-im-sidebar-main-detail__invitation-button-container">
				<Button
					v-if="showCopyInviteButton"
					:text="$Bitrix.Loc.getMessage('IM_SIDEBAR_COPY_INVITE_LINK')"
					:size="ButtonSize.M"
					:color="ButtonColor.PrimaryBorder"
					:isRounded="true"
					:isUppercase="false"
					icon="link"
					@click="onCopyInviteClick"
				/>
			</div>
			<DetailUser 
				v-for="dialogId in dialogIds" 
				:dialogId="dialogId" 
				:isModerator="isModerator(dialogId)"
				@contextMenuClick="onContextMenuClick"
			/>
		</SidebarDetail>
	`
};