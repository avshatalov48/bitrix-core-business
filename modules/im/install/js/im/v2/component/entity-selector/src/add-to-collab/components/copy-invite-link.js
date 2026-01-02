import { hint } from 'ui.vue3.directives.hint';

import { FeaturePromoter } from 'ui.info-helper';
import { Utils } from 'im.v2.lib.utils';
import { ActionByRole, SliderCode } from 'im.v2.const';
import { PermissionManager } from 'im.v2.lib.permission';
import { Feature, FeatureManager } from 'im.v2.lib.feature';
import { Notifier } from 'im.v2.lib.notifier';
import { ChatButton, ButtonColor, ButtonSize } from 'im.v2.component.elements.button';

import { CollabInvitationService } from '../classes/collab-invitation-service';

import type { JsonObject } from 'main.core';
import type { PopupOptions } from 'main.popup';

// @vue/component
export const CopyInviteLink = {
	name: 'CopyInviteLink',
	components: { ChatButton },
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
		langCode: {
			type: String,
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
		isEnabledCollabersInvitation(): boolean
		{
			return FeatureManager.isFeatureAvailable(Feature.enabledCollabersInvitation);
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
			if (!this.isEnabledCollabersInvitation)
			{
				this.showHelper();

				return;
			}

			try
			{
				this.isCopyingInviteLink = true;
				const link = await (new CollabInvitationService()).copyLink(this.collabId, this.langCode);
				await Utils.text.copyToClipboard(link);
				Notifier.onCopyLinkComplete();
			}
			catch
			{
				Notifier.collab.onCopyLinkError();
			}
			finally
			{
				this.isCopyingInviteLink = false;
			}
		},
		async updateLink()
		{
			if (!this.isEnabledCollabersInvitation)
			{
				this.showHelper();

				return;
			}

			try
			{
				this.isUpdatingLink = true;
				await (new CollabInvitationService()).updateLink(this.collabId);
				Notifier.collab.onUpdateLinkComplete();
			}
			catch
			{
				Notifier.onDefaultError();
			}
			finally
			{
				this.isUpdatingLink = false;
			}
		},
		loc(phraseCode: string, replacements: {[string]: string} = {}): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
		},
		showHelper()
		{
			new FeaturePromoter({ code: SliderCode.collabInviteOff }).show();
		},
	},
	template: `
		<div v-if="isInviteLinkAvailable" class="bx-im-add-to-collab__invite-block --link">
			<span class="bx-im-add-to-collab__invite-block-title --ellipsis">
				{{ loc('IM_ENTITY_SELECTOR_ADD_TO_COLLAB_INVITE_BY_LINK') }}
			</span>
			<ChatButton
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
