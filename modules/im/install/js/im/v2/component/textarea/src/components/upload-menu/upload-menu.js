import { Type } from 'main.core';
import { GroupSharingController } from 'calendar.sharing.interface';
import { BIcon, Outline as OutlineIcons } from 'ui.icon-set.api.vue';
import { VoteApplication } from 'vote.application';

import { Core } from 'im.v2.application.core';
import { MessengerMenu, MenuItem, MenuItemIcon } from 'im.v2.component.elements.menu';
import { ActionByRole, ChatType, Color } from 'im.v2.const';
import { EntityCreator } from 'im.v2.lib.entity-creator';
import { Analytics } from 'im.v2.lib.analytics';
import { Feature, FeatureManager } from 'im.v2.lib.feature';
import { PermissionManager } from 'im.v2.lib.permission';
import { Notifier } from 'im.v2.lib.notifier';

import { DiskPopup } from './disk-popup';

import '../../css/upload-menu.css';

import type { PopupOptions } from 'main.popup';
import type { JsonObject } from 'main.core';
import type { ImModelChat, ImModelCollabInfo } from 'im.v2.model';

type UploadMenuItem = {
	icon: $Values<typeof MenuItemIcon>,
	title: string,
	clickHandler: Function,
	showCondition?: boolean,
}

const ICON_SIZE = 24;
const DOCUMENT_SIGN_SLIDER_URL = '/sign/doc/0/?chat_id=';

// @vue/component
export const UploadMenu = {
	components: { BIcon, MessengerMenu, MenuItem, DiskPopup },
	props:
	{
		dialogId: {
			type: String,
			required: true,
		},
	},
	emits: ['fileSelect', 'diskFileSelect'],
	data(): JsonObject
	{
		return {
			showMenu: false,
			showDiskPopup: false,
		};
	},
	computed:
	{
		OutlineIcons: () => OutlineIcons,
		ICON_SIZE: () => ICON_SIZE,
		menuItems(): UploadMenuItem[]
		{
			return [
				{
					icon: MenuItemIcon.file,
					title: this.loc('IM_TEXTAREA_SELECT_LOCAL_FILE'),
					clickHandler: this.onSelectLocalFile,
				},
				{
					icon: MenuItemIcon.b24,
					title: this.loc('IM_TEXTAREA_SELECT_FILE_FROM_B24'),
					clickHandler: this.onSelectFromB24,
				},
				{
					icon: MenuItemIcon.task,
					title: this.loc('IM_TEXTAREA_SELECT_TASK'),
					clickHandler: this.onCreateTaskClick,
				},
				{
					icon: MenuItemIcon.meeting,
					title: this.loc('IM_TEXTAREA_SELECT_MEETING'),
					clickHandler: this.onCreateMeetingClick,
				},
				{
					icon: MenuItemIcon.calendarSlot,
					title: this.loc('IM_TEXTAREA_SELECT_CALENDAR_SLOT'),
					clickHandler: this.onCreateCalendarSlotClick,
					showCondition: () => this.isCalendarSlotAvailable,
				},
				{
					icon: MenuItemIcon.documentSign,
					title: this.loc('IM_TEXTAREA_SELECT_DOCUMENT_SIGN'),
					clickHandler: this.onCreateDocumentSignClick,
					showCondition: () => this.isDocumentSignAvailable,
				},
				{
					icon: MenuItemIcon.vote,
					title: this.loc('IM_TEXTAREA_SELECT_VOTE'),
					clickHandler: this.onCreateVoteClick,
					showCondition: () => this.isVoteCreationAvailable,
				},
			];
		},
		availableMenuItems(): UploadMenuItem[]
		{
			return this.menuItems.filter((item) => {
				if (!Type.isFunction(item.showCondition))
				{
					return true;
				}

				return item.showCondition();
			});
		},
		menuConfig(): PopupOptions
		{
			return {
				width: 278,
				bindElement: this.$refs.upload || {},
				bindOptions: {
					position: 'top',
				},
				offsetTop: 30,
				offsetLeft: -10,
				padding: 0,
			};
		},
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.dialogId, true);
		},
		chatType(): $Values<typeof ChatType>
		{
			return this.dialog.type;
		},
		chatId(): number
		{
			return this.dialog.chatId;
		},
		isDocumentSignAvailable(): boolean
		{
			const isActiveFeature = FeatureManager.isFeatureAvailable(Feature.documentSignAvailable);
			if (!isActiveFeature)
			{
				return false;
			}

			return PermissionManager.getInstance().canPerformActionByRole(ActionByRole.createDocumentSign, this.dialogId);
		},
		isCalendarSlotAvailable(): boolean
		{
			return PermissionManager.getInstance().canPerformActionByRole(ActionByRole.createCalendarSlots, this.dialogId);
		},
		isVoteCreationAvailable(): boolean
		{
			if (!VoteApplication?.canCreateVoteInChat(this.chatType))
			{
				return false;
			}

			return FeatureManager.isFeatureAvailable(Feature.voteCreationAvailable);
		},
		iconColor(): string
		{
			if (this.showMenu)
			{
				return Color.accentBlue;
			}

			return Color.gray40;
		},
	},
	methods:
	{
		onSelectLocalFile()
		{
			this.$refs.fileInput.click();
			this.showMenu = false;
		},
		onSelectFromB24()
		{
			this.showDiskPopup = true;
			this.showMenu = false;
		},
		onFileSelect(event)
		{
			this.$emit('fileSelect', {
				event,
			});
			this.showMenu = false;
		},
		onDiskFileSelect(event)
		{
			this.$emit('diskFileSelect', event);
		},
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
		getEntityCreator(): EntityCreator
		{
			if (!this.entityCreator)
			{
				this.entityCreator = new EntityCreator(this.chatId);
			}

			return this.entityCreator;
		},
		onCreateTaskClick()
		{
			void this.getEntityCreator().createTaskForChat();
			Analytics.getInstance().chatEntities.onCreateTaskFromTextareaClick(this.dialogId);

			this.showMenu = false;
		},
		onCreateMeetingClick()
		{
			void this.getEntityCreator().createMeetingForChat();
			Analytics.getInstance().chatEntities.onCreateEventFromTextareaClick(this.dialogId);

			this.showMenu = false;
		},
		onUploadButtonClick()
		{
			if (this.showMenu !== true)
			{
				Analytics.getInstance().attachMenu.onOpenUploadMenu(this.dialogId);
			}

			this.showMenu = true;
		},
		async onCreateCalendarSlotClick(event: PointerEvent)
		{
			if (!GroupSharingController)
			{
				return;
			}

			const collabInfo: ImModelCollabInfo = Core.getStore().getters['chats/collabs/getByChatId'](this.chatId);
			if (!collabInfo || !collabInfo.collabId)
			{
				return;
			}

			try
			{
				const groupSharing = await GroupSharingController.getGroupSharing(collabInfo.collabId, event.target);
				groupSharing.openDialog();
				this.showMenu = false;
			}
			catch (errors)
			{
				Notifier.onDefaultError();
				console.error('ChatTextarea: UploadMenu: select slots error', errors);
			}
		},
		onCreateDocumentSignClick()
		{
			const preparedUrl = DOCUMENT_SIGN_SLIDER_URL + this.chatId;
			BX.SidePanel.Instance.open(preparedUrl, { cacheable: false });
		},
		onCreateVoteClick(): void
		{
			const analyticsInstance = Analytics.getInstance();
			const analyticsParams = analyticsInstance.vote.getSerializedParams(this.dialogId);
			const preparedUrl = `/bitrix/components/bitrix/voting.im.edit/slider.php?chatId=${this.chatId}&${analyticsParams}`;
			BX.SidePanel.Instance.open(preparedUrl, {
				cacheable: false,
				width: 600,
				allowChangeHistory: false,
			});
			Analytics.getInstance().chatEntities.onCreateVoteFromTextareaClick(this.dialogId);
			this.showMenu = false;
		},
	},
	template: `
		<div ref="upload" class="bx-im-textarea__icon-container">
			<BIcon
				:name="OutlineIcons.ATTACH"
				:title="loc('IM_TEXTAREA_ICON_UPLOAD_TITLE')"
				:color="iconColor"
				:size="ICON_SIZE"
				class="bx-im-textarea__icon"
				@click="onUploadButtonClick"
			/>
		</div>
		<MessengerMenu v-if="showMenu" :config="menuConfig" @close="showMenu = false" className="bx-im-file-menu__scope">
			<MenuItem
				v-for="item in availableMenuItems"
				:icon="item.icon"
				:title="item.title"
				@click="item.clickHandler"
			/>
			<input type="file" @change="onFileSelect" multiple class="bx-im-file-menu__file-input" ref="fileInput">
		</MessengerMenu>
		<DiskPopup v-if="showDiskPopup" @diskFileSelect="onDiskFileSelect" @close="showDiskPopup = false"/>
	`,
};
