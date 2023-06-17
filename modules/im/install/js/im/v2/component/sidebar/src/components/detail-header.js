import {Button as ChatButton, ButtonSize, ButtonColor} from 'im.v2.component.elements';
import {SidebarDetailBlock, SidebarFileTabTypes, ChatOption} from 'im.v2.const';
import {EntityCreator} from 'im.v2.lib.entity-creator';
import '../css/detail-header.css';
import type {ImModelDialog} from 'im.v2.model';
import {AddToChat} from 'im.v2.component.entity-selector';

// @vue/component
export const DetailHeader = {
	name: 'DetailHeader',
	components: {ChatButton, AddToChat},
	props: {
		detailBlock: {
			type: String,
			required: true,
		},
		dialogId: {
			type: String,
			required: true
		},
		chatId: {
			type: Number,
			required: true
		},
	},
	emits: ['back'],
	data() {
		return {
			showAddToChatPopup: false
		};
	},
	computed:
	{
		ButtonSize: () => ButtonSize,
		ButtonColor: () => ButtonColor,
		needAddButton(): boolean
		{
			const detailsWithAddButton = [
				SidebarDetailBlock.main,
				SidebarDetailBlock.task,
				SidebarDetailBlock.meeting
			];

			if (this.detailBlock === SidebarDetailBlock.main)
			{
				return this.$store.getters['dialogues/getChatOption'](this.dialog.type, ChatOption.extend);
			}

			return detailsWithAddButton.includes(this.detailBlock);
		},
		dialog(): ImModelDialog
		{
			return this.$store.getters['dialogues/get'](this.dialogId, true);
		},
		title(): string
		{
			if (this.detailBlock === SidebarDetailBlock.main)
			{
				let usersInChatCount = this.dialog.userCounter;
				if (usersInChatCount >= 1000)
				{
					usersInChatCount = `${Math.floor(usersInChatCount / 1000)}k`;
				}

				return this.$Bitrix.Loc.getMessage('IM_SIDEBAR_MAIN_DETAIL_TITLE').replace('#NUMBER#', usersInChatCount);
			}

			if (Object.values(SidebarFileTabTypes).includes(this.detailBlock))
			{
				return this.$Bitrix.Loc.getMessage('IM_SIDEBAR_MEDIA_DETAIL_TITLE');
			}

			const phrase = `IM_SIDEBAR_${this.detailBlock.toUpperCase()}_DETAIL_TITLE`;

			return this.$Bitrix.Loc.getMessage(phrase);
		},
	},
	created()
	{
		this.entityCreator = new EntityCreator(this.chatId);
	},
	methods:
	{
		onSearchClick()
		{
			console.warn('onSearchClick');
		},
		onAddClick()
		{
			if (!this.needAddButton)
			{
				return;
			}

			switch (this.detailBlock)
			{
				case SidebarDetailBlock.meeting:
				{
					this.entityCreator.createMeetingForChat();
					break;
				}
				case SidebarDetailBlock.task:
				{
					this.entityCreator.createTaskForChat();
					break;
				}
				case SidebarDetailBlock.main:
				{
					this.showAddToChatPopup = true;
					break;
				}
				default:
					break;
			}
		}
	},
	template: `
		<div class="bx-im-sidebar-detail-header__container bx-im-sidebar-detail-header__scope">
			<div class="bx-im-sidebar-detail-header__title-container">
				<button class="bx-im-sidebar__back-icon" @click="$emit('back')"></button>
				<div class="bx-im-sidebar-detail-header__title-text">{{ title }}</div>
				<div class="bx-im-sidebar-detail-header__add-button" ref="add-members">
					<ChatButton
						v-if="needAddButton"
						:text="$Bitrix.Loc.getMessage('IM_SIDEBAR_ADD_BUTTON_TEXT')"
						:size="ButtonSize.S"
						:color="ButtonColor.PrimaryLight"
						:isRounded="true"
						:isUppercase="false"
						icon="plus"
						@click="onAddClick"
					/>
				</div>
				
			</div>
<!--			<button-->
<!--				class="bx-im-sidebar-detail-header__search-button bx-im-sidebar__search-icon"-->
<!--				@click="onSearchClick"-->
<!--			>-->
<!--			</button>-->
			<AddToChat
				:bindElement="$refs['add-members'] || {}"
				:chatId="chatId"
				:dialogId="dialogId"
				:showPopup="showAddToChatPopup"
				:popupConfig="{offsetTop: 15, offsetLeft: -300}"
				@close="showAddToChatPopup = false"
			/>
		</div>
	`
};