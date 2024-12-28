import { hint } from 'ui.vue3.directives.hint';

import { Utils } from 'im.v2.lib.utils';
import { ActionByRole } from 'im.v2.const';
import { PermissionManager } from 'im.v2.lib.permission';
import { Feature, FeatureManager } from 'im.v2.lib.feature';
import { Button as MessengerButton, ButtonColor, ButtonSize, ChatHint } from 'im.v2.component.elements';

import { CollabInvitationService } from '../classes/collab-invitation-service';

import type { JsonObject } from 'main.core';
import type { PopupOptions } from 'main.popup';

// @vue/component
export const CopyInviteLink = {
	name: 'CopyInviteLink',
	components: { MessengerButton, ChatHint },
	directives: { hint },
	props:
	{
		dialogId: {
			type: String,
			required: true,
		},
		collabId: {
			type: Number,
			required: true,
		},
	},
	data(): JsonObject
	{
		return {
			isCopyingInviteLink: false,
			isUpdatingLink: false,
		};
	},
	computed:
	{
		ButtonSize: () => ButtonSize,
		ButtonColor: () => ButtonColor,
		isInviteLinkAvailable(): boolean
		{
			return FeatureManager.isFeatureAvailable(Feature.inviteByLinkAvailable);
		},
		updateLinkHint(): { text: string, popupOptions: PopupOptions }
		{
			return {
				text: this.loc('IM_ENTITY_SELECTOR_ADD_TO_COLLAB_LINK_UPDATE_HINT'),
				popupOptions: {
					width: 278,
					bindOptions: {
						position: 'top',
					},
					angle: {
						offset: 36,
						position: 'top',
					},
					targetContainer: document.body,
					offsetTop: -8,
				},
			};
		},
		canUpdateLink(): boolean
		{
			return PermissionManager.getInstance().canPerformActionByRole(ActionByRole.updateInviteLink, this.dialogId);
		},
	},
	methods:
	{
		async copyInviteLink()
		{
			try
			{
				this.isCopyingInviteLink = true;
				const link = await (new CollabInvitationService()).copyLink(this.collabId);
				await Utils.text.copyToClipboard(link);
				this.showNotification(this.loc('IM_ENTITY_SELECTOR_ADD_TO_COLLAB_LINK_COPIED'));
			}
			catch
			{
				this.showNotification(this.loc('IM_ENTITY_SELECTOR_ADD_TO_COLLAB_LINK_NOT_COPIED'));
			}
			finally
			{
				this.isCopyingInviteLink = false;
			}
		},
		async updateLink()
		{
			try
			{
				this.isUpdatingLink = true;
				await (new CollabInvitationService()).updateLink(this.collabId);
				this.showNotification(this.loc('IM_ENTITY_SELECTOR_ADD_TO_COLLAB_LINK_UPDATED'));
			}
			catch
			{
				this.showNotification(this.loc('IM_ENTITY_SELECTOR_ADD_TO_COLLAB_LINK_UPDATED_ERROR'));
			}
			finally
			{
				this.isUpdatingLink = false;
			}
		},
		showNotification(content: string)
		{
			BX.UI.Notification.Center.notify({ content });
		},
		loc(phraseCode: string, replacements: {[string]: string} = {}): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
		},
	},
	template: `
		<div v-if="isInviteLinkAvailable" class="bx-im-add-to-collab__invite-block --link">
			<span class="bx-im-add-to-collab__invite-block-title --ellipsis">
				{{ loc('IM_ENTITY_SELECTOR_ADD_TO_COLLAB_INVITE_BY_LINK') }}
			</span>
			<MessengerButton
				:size="ButtonSize.M"
				:color="ButtonColor.Primary"
				:isRounded="true"
				:isUppercase="false"
				:isLoading="isCopyingInviteLink"
				:isDisabled="isUpdatingLink"
				:text="loc('IM_ENTITY_SELECTOR_ADD_TO_COLLAB_COPY_LINK')"
				@click="copyInviteLink"
			/>
			<button
				v-if="canUpdateLink"
				v-hint="updateLinkHint"
				:class="{'--loading': isUpdatingLink}"
				class="bx-im-add-to-collab__update-link_button"
				@click="updateLink"
			>
				<span class="bx-im-add-to-collab__update-link_icon"></span>
			</button>
		</div>
	`,
};
