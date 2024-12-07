import { CopilotRolesDialog } from 'im.v2.component.elements';
import { PromoManager } from 'im.v2.lib.promo';
import { PromoId } from 'im.v2.const';

import { CopilotService } from './classes/copilot-serivce';
import { ChangeRolePromo } from './components/change-role-promo';

import './css/copilot-role.css';

import type { JsonObject } from 'main.core';
import type { ImModelCopilotRole } from 'im.v2.model';

// @vue/component
export const CopilotRole = {
	name: 'CopilotRole',
	components: { ChangeRolePromo, CopilotRolesDialog },
	props:
	{
		dialogId: {
			type: String,
			required: true,
		},
	},
	data(): JsonObject
	{
		return {
			shouldShowChangeRolePromo: false,
			showRolesDialog: false,
		};
	},
	computed:
	{
		chatRole(): ImModelCopilotRole
		{
			const chatRole = this.$store.getters['copilot/chats/getRole'](this.dialogId);
			if (!chatRole)
			{
				return this.$store.getters['copilot/roles/getDefault'];
			}

			return chatRole;
		},
		roleName(): string
		{
			return this.chatRole.name;
		},
		canShowChangeRolePromo(): boolean
		{
			// we don't want to show change role promo if we are still showing first promo (add users to copilot chat)
			const needToShowAddUsersToChatHint = PromoManager.getInstance().needToShow(PromoId.addUsersToCopilotChat);
			const needToShowChangeRolePromo = PromoManager.getInstance().needToShow(PromoId.changeRoleCopilot);

			return !needToShowAddUsersToChatHint && needToShowChangeRolePromo;
		},
	},
	mounted()
	{
		// Show promo after sidebar animation is over.
		setTimeout(() => {
			this.shouldShowChangeRolePromo = this.canShowChangeRolePromo;
		}, 300);
	},
	beforeUnmount()
	{
		this.showRolesDialog = false;
		this.shouldShowChangeRolePromo = false;
	},
	methods:
	{
		handleChangeRole()
		{
			this.showRolesDialog = true;
		},
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
		onChangeRolePromoAccept()
		{
			this.shouldShowChangeRolePromo = false;
			void PromoManager.getInstance().markAsWatched(PromoId.changeRoleCopilot);
		},
		onCopilotDialogSelectRole(role)
		{
			void (new CopilotService()).updateRole({
				dialogId: this.dialogId,
				role,
			});
		},
	},
	template: `
		<div class="bx-im-sidebar-copilot-role__container" @click="handleChangeRole" ref="change-role">
			<div class="bx-im-sidebar-copilot-role__title">
				<div class="bx-im-sidebar-copilot-role__title-icon"></div>
				<div class="bx-im-sidebar-copilot-role__title-text">
					{{ roleName }}
				</div>
			</div>
			<div class="bx-im-sidebar-copilot-role__arrow-icon"></div>
			<ChangeRolePromo 
				v-if="shouldShowChangeRolePromo"
				:bindElement="$refs['change-role']"
				@accept="onChangeRolePromoAccept"
				@hide="shouldShowChangeRolePromo = false"
			/>
			<CopilotRolesDialog
				v-if="showRolesDialog"
				:title="loc('IM_SIDEBAR_COPILOT_CHANGE_ROLE_DIALOG_TITLE')"
				@selectRole="onCopilotDialogSelectRole"
				@close="showRolesDialog = false"
			/>
		</div>
	`,
};
