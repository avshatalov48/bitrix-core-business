import { PermissionManager } from 'im.v2.lib.permission';
import { EventEmitter } from 'main.core.events';

import { Core } from 'im.v2.application.core';
import { ChatActionType, EventType, SidebarDetailBlock } from 'im.v2.const';
import { AddToChat } from 'im.v2.component.entity-selector';
import { Button as ChatButton, ButtonColor, ButtonSize, Loader } from 'im.v2.component.elements';

import { DetailUser } from './detail-user';
import { DetailHeader } from '../../elements/detail-header';
import { MembersService } from '../../../classes/panels/members';
import { MembersMenu } from '../../../classes/context-menu/main/members-menu';

import './css/members-panel.css';

import type { JsonObject } from 'main.core';
import type { ImModelChat } from 'im.v2.model';

// @vue/component
export const MembersPanel = {
	name: 'MembersPanel',
	components: { DetailUser, ChatButton, DetailHeader, Loader, AddToChat },
	props: {
		dialogId: {
			type: String,
			required: true,
		},
		secondLevel: {
			type: Boolean,
			default: false,
		},
	},
	data(): JsonObject
	{
		return {
			isLoading: false,
			showAddToChatPopup: false,
			showAddToChatTarget: null,
		};
	},
	computed:
	{
		SidebarDetailBlock: () => SidebarDetailBlock,
		ButtonSize: () => ButtonSize,
		ButtonColor: () => ButtonColor,
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.dialogId, true);
		},
		dialogManagers(): number[]
		{
			return this.dialog.managerList;
		},
		dialogIds(): string[]
		{
			const users = this.$store.getters['sidebar/members/get'](this.chatId);

			return users.map((userId) => userId.toString());
		},
		chatLink(): string
		{
			return `${Core.getHost()}/online/?IM_DIALOG=${this.dialogId}`;
		},
		hasNextPage(): boolean
		{
			return this.$store.getters['sidebar/members/hasNextPage'](this.chatId);
		},
		panelInited(): boolean
		{
			return this.$store.getters['sidebar/members/getInited'](this.chatId);
		},
		chatId(): number
		{
			return this.dialog.chatId;
		},
		title(): string
		{
			let usersInChatCount = this.dialog.userCounter;
			if (usersInChatCount >= 1000)
			{
				usersInChatCount = `${Math.floor(usersInChatCount / 1000)}k`;
			}

			return this.$Bitrix.Loc.getMessage('IM_SIDEBAR_MEMBERS_DETAIL_TITLE').replace('#NUMBER#', usersInChatCount);
		},
		needAddButton(): boolean
		{
			return PermissionManager.getInstance().canPerformAction(ChatActionType.extend, this.dialogId);
		},
	},
	watch:
	{
		dialogId(dialogId: string)
		{
			this.service = new MembersService({ dialogId });
			this.loadFirstPage();
		},
	},
	created()
	{
		this.contextMenu = new MembersMenu();
		this.service = new MembersService({ dialogId: this.dialogId });
		this.loadFirstPage();
	},
	beforeUnmount()
	{
		this.contextMenu.destroy();
	},
	methods:
	{
		async loadFirstPage()
		{
			if (this.panelInited || this.isLoading)
			{
				return;
			}

			this.isLoading = true;
			this.chats = await this.service.loadFirstPage();
			this.isLoading = false;
		},
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
			};

			this.contextMenu.openMenu(item, event.target);
		},
		onCopyInviteClick()
		{
			if (BX.clipboard.copy(this.chatLink))
			{
				BX.UI.Notification.Center.notify({
					content: this.$Bitrix.Loc.getMessage('IM_SIDEBAR_COPIED_SUCCESS'),
				});
			}
		},
		onBackClick()
		{
			EventEmitter.emit(EventType.sidebar.close, { panel: SidebarDetailBlock.members });
		},
		needToLoadNextPage(event: Event): boolean
		{
			const target = event.target;
			const isAtThreshold = target.scrollTop + target.clientHeight >= target.scrollHeight - target.clientHeight;

			return isAtThreshold && this.hasNextPage;
		},
		async onScroll(event: Event)
		{
			this.contextMenu.destroy();

			if (this.isLoading || !this.needToLoadNextPage(event))
			{
				return;
			}

			this.isLoading = true;
			await this.service.loadNextPage();
			this.isLoading = false;
		},
		onAddClick(event)
		{
			this.showAddToChatPopup = true;
			this.showAddToChatTarget = event.target;
		},
	},
	template: `
		<div class="bx-im-sidebar-main-detail__scope">
			<DetailHeader 
				:dialogId="dialogId"
				:title="title"
				:secondLevel="secondLevel"
				:withAddButton="needAddButton"
				@addClick="onAddClick"
				@back="onBackClick" 
			/>
			<div class="bx-im-sidebar-detail__container" @scroll="onScroll">
				<div class="bx-im-sidebar-main-detail__invitation-button-container">
					<ChatButton
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
				<Loader v-if="isLoading" class="bx-im-sidebar-detail__loader-container" />
			</div>
			<AddToChat
				:bindElement="showAddToChatTarget || {}"
				:dialogId="dialogId"
				:showPopup="showAddToChatPopup"
				:popupConfig="{offsetTop: 0, offsetLeft: 0}"
				@close="showAddToChatPopup = false"
			/>
		</div>
	`,
};
