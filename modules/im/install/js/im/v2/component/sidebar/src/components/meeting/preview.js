import {ImModelSidebarMeetingItem, ImModelDialog} from 'im.v2.model';
import {Button, ButtonColor, ButtonSize} from 'im.v2.component.elements';
import {SidebarBlock, SidebarDetailBlock} from 'im.v2.const';
import {EntityCreator} from 'im.v2.lib.entity-creator';
import {MeetingMenu} from '../../classes/context-menu/meeting/meeting-menu';
import {DetailEmptyState} from '../detail-empty-state';
import {MeetingItem} from './meeting-item';
import '../../css/meeting/preview.css';

// @vue/component
export const MeetingPreview = {
	name: 'MeetingPreview',
	components: {MeetingItem, DetailEmptyState, Button},
	props: {
		isLoading: {
			type: Boolean,
			default: false
		},
		dialogId: {
			type: String,
			required: true
		}
	},
	data() {
		return {
			showAddButton: false,
		};
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
		dialog(): ImModelDialog
		{
			return this.$store.getters['dialogues/get'](this.dialogId, true);
		},
		chatId(): number
		{
			return this.dialog.chatId;
		},
		dialogInited()
		{
			return this.dialog.inited;
		},
		isLoadingState(): boolean
		{
			return !this.dialogInited || this.isLoading;
		}
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

			this.$emit('openDetail', {block: SidebarBlock.meeting, detailBlock: SidebarDetailBlock.meeting});
		},
		onContextMenuClick(event, target)
		{
			const item = {
				...event,
				dialogId: this.dialogId
			};

			this.contextMenu.openMenu(item, target);
		}
	},
	template: `
		<div class="bx-im-sidebar-meeting-preview__scope">
			<div v-if="isLoadingState" class="bx-im-sidebar-meeting-preview__skeleton"></div>
			<div v-else class="bx-im-sidebar-meeting-preview__container">
				<div
					class="bx-im-sidebar-meeting-preview__header_container"
					@mouseover="showAddButton = true"
					@mouseleave="showAddButton = false"
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
						<Button
							v-if="showAddButton"
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
	`
};