import { RoleItem } from './role-item';

import '../css/role-selector-mini-content.css';

import type { ImModelCopilotRole } from 'im.v2.model';

// @vue/component
export const RoleSelectorMiniContent = {
	name: 'RoleSelectorMiniContent',
	components: { RoleItem },
	emits: ['selectedRole', 'openMainSelector'],
	computed:
	{
		rolesToShow(): ImModelCopilotRole[]
		{
			return this.$store.getters['copilot/getRecommendedRoles']();
		},
	},
	methods:
	{
		openMainSelector()
		{
			this.$emit('openMainSelector');
		},
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
		onRoleClick(role)
		{
			this.$emit('selectedRole', role.code);
		},
	},
	template: `
		<div class="bx-im-role-selector-mini-content__container">
			<span class="bx-im-role-selector-mini-content__title">
				{{ loc('IM_LIST_CONTAINER_COPILOT_ROLES_LIST') }}
			</span>
			<div class="bx-im-role-selector-mini-content__items">
				<RoleItem 
					v-for="role in rolesToShow" 
					:role="role"
					@click="onRoleClick(role)"
				/>
				<div class="bx-im-role-selector-mini-content__main-selector" @click="openMainSelector">
					<div class="bx-im-role-selector-mini-content__main-selector-info">
						<div class="bx-im-role-selector-mini-content__main-selector-avatar"></div>
						<div class="bx-im-role-selector-mini-content__main-selector-name">
							{{ loc('IM_LIST_CONTAINER_COPILOT_SELECT_ROLE_FROM_LIST') }}
						</div>
					</div>
					<div class="bx-im-role-selector-mini-content__main-selector-arrow"></div>
				</div>
			</div>
		</div>
	`,
};
