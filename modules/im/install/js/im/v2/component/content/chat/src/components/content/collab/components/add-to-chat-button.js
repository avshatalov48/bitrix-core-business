import { AddToCollab } from 'im.v2.component.entity-selector';
import { Analytics } from 'im.v2.lib.analytics';

import type { JsonObject } from 'main.core';

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
