import { FeaturePromoter } from 'ui.info-helper';

import { Messenger } from 'im.public';
import { MessengerMenu, MenuItem, MenuItemIcon } from 'im.v2.component.elements.menu';
import { CopilotRolesDialog } from 'im.v2.component.elements.copilot-roles-dialog';
import { CreateChatPromo } from 'im.v2.component.list.container.elements.create-chat-promo';
import { Layout, PromoId, ChatType, ActionByUserType, SliderCode } from 'im.v2.const';
import { Analytics } from 'im.v2.lib.analytics';
import { PermissionManager } from 'im.v2.lib.permission';
import { PromoManager } from 'im.v2.lib.promo';
import { CreateChatManager } from 'im.v2.lib.create-chat';
import { Feature, FeatureManager } from 'im.v2.lib.feature';
import { CopilotService } from 'im.v2.provider.service.copilot';

import { CreateChatHelp } from './components/create-chat-help';
import { NewBadge } from './components/collab/new-badge';
import { DescriptionBanner } from './components/collab/description-banner';
import { CopilotRoleSelectionButton } from './components/copilot-role-selection-button';
import { InvitePromo } from './components/invite-promo';

import type { JsonObject } from 'main.core';
import type { MenuOptions } from 'main.popup';

const PromoByChatType = {
	[ChatType.chat]: PromoId.createGroupChat,
	[ChatType.videoconf]: PromoId.createConference,
	[ChatType.channel]: PromoId.createChannel,
};

const COPILOT_UNIVERSAL_ROLE = 'copilot_assistant';

// @vue/component
export const CreateChatMenu = {
	components: {
		MessengerMenu,
		MenuItem,
		CreateChatHelp,
		CreateChatPromo,
		NewBadge,
		DescriptionBanner,
		CopilotRoleSelectionButton,
		CopilotRolesDialog,
		InvitePromo,
	},
	data(): JsonObject
	{
		return {
			showMenu: false,
			chatTypeToCreate: '',
			showCreateChatPromo: false,
			showCollabPromo: false,
			showInvitePromo: false,
			showCopilotRolesDialog: false,
			isLoading: false,
		};
	},
	computed:
	{
		ChatType: () => ChatType,
		MenuItemIcon: () => MenuItemIcon,
		menuConfig(): MenuOptions
		{
			return {
				id: 'im-create-chat-menu',
				width: 275,
				bindElement: this.$refs.icon || {},
				offsetTop: 4,
				padding: 0,
			};
		},
		collabAvailable(): boolean
		{
			const hasAccess = PermissionManager.getInstance().canPerformActionByUserType(
				ActionByUserType.createCollab,
			);
			const creationAvailable = FeatureManager.isFeatureAvailable(Feature.collabCreationAvailable);
			const featureAvailable = FeatureManager.isFeatureAvailable(Feature.collabAvailable);

			return hasAccess && featureAvailable && creationAvailable;
		},
		canCreateChat(): boolean
		{
			return PermissionManager.getInstance().canPerformActionByUserType(
				ActionByUserType.createChat,
			);
		},
		canCreateCopilot(): boolean
		{
			return PermissionManager.getInstance().canPerformActionByUserType(
				ActionByUserType.createCopilot,
			);
		},
		isCopilotAvailable(): boolean
		{
			return FeatureManager.isFeatureAvailable(Feature.copilotAvailable);
		},
		isCopilotActive(): boolean
		{
			return FeatureManager.isFeatureAvailable(Feature.copilotActive);
		},
		isCopilotAvailableAndCreatable(): boolean
		{
			return this.isCopilotAvailable && this.canCreateCopilot;
		},
		canCreateChannel(): boolean
		{
			return PermissionManager.getInstance().canPerformActionByUserType(
				ActionByUserType.createChannel,
			);
		},
		canCreateConference(): boolean
		{
			return PermissionManager.getInstance().canPerformActionByUserType(
				ActionByUserType.createConference,
			);
		},
		iconStatusClasses(): { [key: string]: boolean }
		{
			return {
				'--default': !this.isLoading,
				'--loading': this.isLoading,
			};
		},
	},
	created()
	{
		this.showCollabPromo = PromoManager.getInstance().needToShow(PromoId.createCollabDescription);
		this.showInvitePromo = PromoManager.getInstance().needToShow(PromoId.recentCreateChatInviteUsers);
	},
	methods:
	{
		onChatCreateClick(type: $Values<typeof ChatType>)
		{
			Analytics.getInstance().chatCreate.onStartClick(type);
			this.chatTypeToCreate = type;

			const promoBannerIsNeeded = PromoManager.getInstance().needToShow(this.getPromoType());
			if (promoBannerIsNeeded)
			{
				this.showCreateChatPromo = true;
				this.showMenu = false;

				return;
			}

			this.startChatCreation();
			this.showMenu = false;
		},
		showCopilotPromoter()
		{
			const promoter = new FeaturePromoter({ code: SliderCode.copilotDisabled });
			promoter.show();
		},
		checkCopilotActive(): boolean
		{
			if (!this.isCopilotActive)
			{
				this.showCopilotPromoter();

				return false;
			}

			return true;
		},
		async onDefaultCopilotCreateClick()
		{
			if (!this.checkCopilotActive())
			{
				return;
			}

			Analytics.getInstance().copilot.onCreateDefaultChatInRecent();
			await this.createCopilotChat(COPILOT_UNIVERSAL_ROLE);
		},
		onCopilotRoleSelectClick()
		{
			if (!this.checkCopilotActive())
			{
				return;
			}

			Analytics.getInstance().copilot.onSelectRoleInRecent();
			this.showCopilotRolesDialog = true;
		},
		async onCopilotDialogSelectRole(role)
		{
			await this.createCopilotChat(role.code);
		},
		async createCopilotChat(roleCode: string): Promise<string>
		{
			this.showMenu = false;
			this.isLoading = true;
			const newDialogId = await this.getCopilotService().createChat({ roleCode })
				.catch(() => {
					this.isLoading = false;
				});
			this.isLoading = false;
			void Messenger.openChat(newDialogId);
		},
		onPromoContinueClick()
		{
			PromoManager.getInstance().markAsWatched(this.getPromoType());
			this.startChatCreation();
			this.showCreateChatPromo = false;
			this.showMenu = false;
			this.chatTypeToCreate = '';
		},
		onCollabDescriptionClose(): void
		{
			void PromoManager.getInstance().markAsWatched(PromoId.createCollabDescription);
			this.showCollabPromo = false;
		},
		startChatCreation()
		{
			const { name: currentLayoutName, entityId: currentLayoutChatType } = this.$store.getters['application/getLayout'];
			if (currentLayoutName === Layout.createChat && currentLayoutChatType === this.chatTypeToCreate)
			{
				return;
			}
			CreateChatManager.getInstance().startChatCreation(this.chatTypeToCreate);
		},
		getPromoType(): string
		{
			return PromoByChatType[this.chatTypeToCreate] ?? '';
		},
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
		getCopilotService(): CopilotService
		{
			if (!this.copilotService)
			{
				this.copilotService = new CopilotService();
			}

			return this.copilotService;
		},
		handleShowPopup()
		{
			Analytics.getInstance().chatCreate.onMenuCreateClick();
			this.showMenu = true;
		},
	},
	template: `
		<div
			class="bx-im-list-container-recent__create-chat_icon"
			:class="{'--active': showMenu}"
			@click="handleShowPopup"
			ref="icon"
		>
			<div
				class="bx-im-list-container-recent__create-chat_icon_status"
				:class="iconStatusClasses"
			></div>
		</div>
		<MessengerMenu v-if="showMenu" :config="menuConfig" @close="showMenu = false">
			<MenuItem
				v-if="canCreateChat"
				:icon="MenuItemIcon.chat"
				:title="loc('IM_RECENT_CREATE_GROUP_CHAT_TITLE_V2')"
				:subtitle="loc('IM_RECENT_CREATE_GROUP_CHAT_SUBTITLE_MSGVER_1')"
				@click="onChatCreateClick(ChatType.chat)"
			/>
			<MenuItem
				v-if="isCopilotAvailableAndCreatable"
				:icon="MenuItemIcon.copilot"
				:title="loc('IM_RECENT_CREATE_COPILOT_TITLE')"
				:subtitle="loc('IM_RECENT_CREATE_COPILOT_SUBTITLE_MSGVER_1')"
				@click.stop="onDefaultCopilotCreateClick"
			>
				<template #after-content>
					<CopilotRoleSelectionButton @click.stop="onCopilotRoleSelectClick" />
				</template>
			</MenuItem>
			<MenuItem
				v-if="canCreateChannel"
				:icon="MenuItemIcon.channel"
				:title="loc('IM_RECENT_CREATE_CHANNEL_TITLE_V2')"
				:subtitle="loc('IM_RECENT_CREATE_CHANNEL_SUBTITLE_MSGVER_1')"
				@click="onChatCreateClick(ChatType.channel)"
			/>
			<MenuItem
				v-if="collabAvailable"
				:icon="MenuItemIcon.collab"
				:title="loc('IM_RECENT_CREATE_COLLAB_TITLE')"
				:subtitle="loc('IM_RECENT_CREATE_COLLAB_SUBTITLE_MSGVER_1')"
				@click="onChatCreateClick(ChatType.collab)"
			>
				<template #after-title>
					<NewBadge />
				</template>
				<template #below-content>
					<DescriptionBanner v-if="showCollabPromo" @close="onCollabDescriptionClose" />
				</template>
			</MenuItem>
			<MenuItem
				v-if="canCreateConference"
				:icon="MenuItemIcon.conference"
				:title="loc('IM_RECENT_CREATE_CONFERENCE_TITLE')"
				:subtitle="loc('IM_RECENT_CREATE_CONFERENCE_SUBTITLE_MSGVER_1')"
				:withBottomBorder="showInvitePromo"
				@click="onChatCreateClick(ChatType.videoconf)"
			/>
			<InvitePromo v-if="showInvitePromo" @close="showInvitePromo = false" />
			<template #footer>
				<CreateChatHelp @articleOpen="showMenu = false" />
			</template>
		</MessengerMenu>
		<CreateChatPromo
			v-if="showCreateChatPromo"
			:chatType="chatTypeToCreate"
			@continue="onPromoContinueClick"
			@close="showCreateChatPromo = false"
		/>
		<CopilotRolesDialog
			v-if="showCopilotRolesDialog"
			@selectRole="onCopilotDialogSelectRole"
			@close="showCopilotRolesDialog = false"
		/>
	`,
};
