import { CloseIconSize } from 'main.popup';
import { Tooltip } from 'ui.dialogs.tooltip';
import { AddToCollab } from 'im.v2.component.entity-selector';
import { PromoId } from 'im.v2.const';
import { Analytics } from 'im.v2.lib.analytics';
import { PromoManager } from 'im.v2.lib.promo';

import { CollabTooltipContent } from '../classes/tooltip-content/tooltip-content';
import { IconKey } from '../classes/tooltip-content/icon-key';

import type { ImModelChat } from 'im.v2.model';
import type { PromoParams } from 'im.v2.provider.pull';
import type { JsonObject } from 'main.core';

const PromoMessages = {
	[PromoId.membersNotInvitedOneDayToCollab]: {
		title: 'IM_CONTENT_COLLAB_ONBOARDING_INVITE_MEMBERS_ONE_DAY_TITLE',
		text: 'IM_CONTENT_COLLAB_ONBOARDING_INVITE_MEMBERS_ONE_DAY_TEXT',
	},
	[PromoId.membersNotInvitedFourDayToCollab]: {
		title: 'IM_CONTENT_COLLAB_ONBOARDING_INVITE_MEMBERS_FOUR_DAYS_TITLE',
		text: 'IM_CONTENT_COLLAB_ONBOARDING_INVITE_MEMBERS_FOUR_DAYS_TEXT',
	},
};

// @vue/component
export const AddToChatButton = {
	name: 'AddToChatButton',
	components: { AddToCollab },
	props:
	{
		dialogId: {
			type: String,
			default: '',
		},
		withAnimation: {
			type: Boolean,
			default: false,
		},
	},
	emits: ['close'],
	data(): JsonObject
	{
		return {
			showAddToChatPopup: false,
		};
	},
	computed:
	{
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.dialogId, true);
		},
	},
	mounted(): void
	{
		this.initPromo();
	},
	methods:
	{
		openAddToChatPopup()
		{
			Analytics.getInstance().userAdd.onChatHeaderClick(this.dialogId);
			this.showAddToChatPopup = true;
		},
		closeAddToChatPopup()
		{
			this.$emit('close');
			this.showAddToChatPopup = false;
		},
		initPromo(): void
		{
			const promoManager = PromoManager.getInstance();

			Object.keys(PromoMessages).forEach((promoId: $Values<typeof PromoId>): void => {
				const promoParams = { chatId: this.dialog.chatId };

				if (promoManager.needToShow(promoId, promoParams))
				{
					void this.showMembersNotInvitedPromo(promoId, promoParams);
				}
			});
		},
		showMembersNotInvitedPromo(promoId: $Values<typeof PromoId>, promoParams: PromoParams): Promise<void>
		{
			const tooltip = new Tooltip({
				bindElement: this.$refs['add-members'],
				content: this.renderTooltipContent(promoId),
				minWidth: 410,
				popupOptions: {
					offsetTop: 8,
					offsetLeft: 9,
					autoHide: true,
					closeIcon: true,
					closeIconSize: CloseIconSize.LARGE,
				},
			});

			tooltip.show();
			void PromoManager.getInstance().markAsWatched(promoId, promoParams);
		},
		renderTooltipContent(promoId: $Values<typeof PromoId>): HTMLElement
		{
			const tooltipContent = new CollabTooltipContent({
				title: this.loc(PromoMessages[promoId].title),
				text: this.loc(PromoMessages[promoId].text),
				iconKey: IconKey.addToChat,
			});

			return tooltipContent.render();
		},
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<div
			:title="loc('IM_CONTENT_CHAT_HEADER_OPEN_INVITE_POPUP_TITLE')"
			:class="{'--active': showAddToChatPopup}"
			class="bx-im-collab-header__add-people-icon"
			@click="openAddToChatPopup"
			ref="add-members"
		></div>
		<AddToCollab
			v-if="showAddToChatPopup"
			:bindElement="$refs['add-members'] ?? {}"
			:dialogId="dialogId"
			:popupConfig="{ offsetTop: 25, offsetLeft: -300 }"
			@close="closeAddToChatPopup"
		/>
	`,
};
