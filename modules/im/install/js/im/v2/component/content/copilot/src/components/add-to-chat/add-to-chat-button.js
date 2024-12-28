import { AddToChat } from 'im.v2.component.entity-selector';
import { PromoId } from 'im.v2.const';
import { PromoManager } from 'im.v2.lib.promo';
import { Analytics } from 'im.v2.lib.analytics';

import { AddToChatHint } from './add-to-chat-hint';

import type { JsonObject } from 'main.core';

// @vue/component
export const AddToChatButton = {
	name: 'AddToChatButton',
	components: { AddToChat, AddToChatHint },
	props:
	{
		dialogId: {
			type: String,
			default: '',
		},
	},
	data(): JsonObject
	{
		return {
			showInviteButton: false,
			showAddToChatPopup: false,
			showAddToChatHint: false,
		};
	},
	mounted()
	{
		this.showAddToChatHint = PromoManager.getInstance().needToShow(PromoId.addUsersToCopilotChat);
	},
	methods:
	{
		openAddToChatPopup(): void
		{
			Analytics.getInstance().userAdd.onChatHeaderClick(this.dialogId);
			this.showAddToChatPopup = true;
		},
		closeAddToChatPopup(): void
		{
			this.showAddToChatPopup = false;
		},
		showHint()
		{
			this.showAddToChatHint = true;
		},
		closeHint()
		{
			this.showAddToChatHint = false;
		},
		onHintHide()
		{
			void PromoManager.getInstance().markAsWatched(PromoId.addUsersToCopilotChat);
			this.closeHint();
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
			class="bx-im-chat-header__icon --add-people"
			@click="openAddToChatPopup"
			ref="add-members"
		>
			<slot name="invite-hint" :inviteButtonRef="$refs['add-members']"></slot>
		</div>
		<AddToChat
			v-if="showAddToChatPopup"
			:bindElement="$refs['add-members'] ?? {}"
			:dialogId="dialogId"
			:popupConfig="{ offsetTop: 15, offsetLeft: -300 }"
			@close="closeAddToChatPopup"
		/>
		<AddToChatHint
			v-if="showAddToChatHint"
			:bindElement="$refs['add-members'] ?? {}"
			@close="closeHint"
			@hide="onHintHide"
		/>
	`,
};
