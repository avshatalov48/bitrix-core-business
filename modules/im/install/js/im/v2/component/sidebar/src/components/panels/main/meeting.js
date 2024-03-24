import { ImModelSidebarMeetingItem, ImModelChat } from 'im.v2.model';
import { Button as MessengerButton, ButtonColor, ButtonSize } from 'im.v2.component.elements';
import { EventType, SidebarDetailBlock } from 'im.v2.const';
import { EntityCreator } from 'im.v2.lib.entity-creator';
import { EventEmitter } from 'main.core.events';

import { MeetingMenu } from '../../../classes/context-menu/meeting/meeting-menu';
import { DetailEmptyState } from '../../elements/detail-empty-state';
import { MeetingItem } from '../meeting/meeting-item';

import './css/meeting.css';

// @vue/component
export const MeetingPreview = {
	name: 'MeetingPreview',
	components: { MeetingItem, DetailEmptyState, MessengerButton },
	props:
	{
		isLoading: {
			type: Boolean,
			default: false,
		},
		dialogId: {
			type: String,
			required: true,
		},
	},
	computed:
	{
		SidebarDetailBlock: () => SidebarDetailBlock,
		ButtonSize: () => ButtonSize,
		ButtonColor: () => ButtonColor,
		firstMeeting(): ?ImModelSidebarMeetingItem
		{
			return this.$store.getters['sidebar/meetings/get'](this.chatId)[0];
		},
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.dialogId, true);
		},
		chatId(): number
		{
			return this.dialog.chatId;
		},
	},
	created()
	{
		this.contextMenu = new MeetingMenu();
	},
	beforeUnmount()
	{
		this.contextMenu.destroy();
	},
	methods:
	{
		getEntityCreator(): EntityCreator
		{
			if (!this.entityCreator)
			{
				this.entityCreator = new EntityCreator(this.chatId);
			}

			return this.entityCreator;
		},
		onAddClick()
		{
			this.getEntityCreator().createMeetingForChat();
		},
		onOpenDetail()
		{
			if (!this.firstMeeting)
			{
				return;
			}

			EventEmitter.emit(EventType.sidebar.open, {
				panel: SidebarDetailBlock.meeting,
				dialogId: this.dialogId,
			});
		},
		onContextMenuClick(event, target)
		{
			const item = {
				...event,
				dialogId: this.dialogId,
			};

			this.contextMenu.openMenu(item, target);
		},
	},
	template: `
		<div class="bx-im-sidebar-meeting-preview__scope">
			<div v-if="isLoading" class="bx-im-sidebar-meeting-preview__skeleton"></div>
			<div v-else class="bx-im-sidebar-meeting-preview__container">
				<div
					class="bx-im-sidebar-meeting-preview__header_container"
					:class="[firstMeeting ? '--active': '']"
					@click="onOpenDetail"
				>
					<div class="bx-im-sidebar-meeting-preview__title">
						<span class="bx-im-sidebar-meeting-preview__title-text">
							{{ $Bitrix.Loc.getMessage('IM_SIDEBAR_MEETING_DETAIL_TITLE') }}
						</span>
						<div v-if="firstMeeting" class="bx-im-sidebar__forward-icon"></div>
					</div>
					<transition name="add-button">
						<MessengerButton
							:text="$Bitrix.Loc.getMessage('IM_SIDEBAR_ADD_BUTTON_TEXT')"
							:size="ButtonSize.S"
							:color="ButtonColor.PrimaryLight"
							:isRounded="true"
							:isUppercase="false"
							icon="plus"
							@click="onAddClick"
							class="bx-im-sidebar-meeting-preview__title-button"
						/>
					</transition>
				</div>
				<MeetingItem v-if="firstMeeting" :meeting="firstMeeting" @contextMenuClick="onContextMenuClick"/>
				<DetailEmptyState
					v-else
					:title="$Bitrix.Loc.getMessage('IM_SIDEBAR_MEETINGS_EMPTY')"
					:iconType="SidebarDetailBlock.meeting"
				/>
			</div>
		</div>
	`,
};
