import '../css/role-item.css';

import type { ImModelCopilotRole } from 'im.v2.model';
import type { JsonObject } from 'main.core';

// @vue/component
export const RoleItem = {
	name: 'RoleItem',
	props:
	{
		role: {
			type: Object,
			required: true,
		},
	},
	data(): JsonObject
	{
		return {
			imageLoadError: false,
		};
	},
	computed:
	{
		roleItem(): ImModelCopilotRole
		{
			return this.role;
		},
		roleAvatar(): string
		{
			return this.roleItem.avatar.medium;
		},
		roleName(): string
		{
			return this.roleItem.name;
		},
		roleSDescription(): string
		{
			return this.roleItem.desc;
		},
		defaultRole(): ImModelCopilotRole
		{
			return this.$store.getters['copilot/roles/getDefault'];
		},
		defaultRoleAvatarUrl(): string
		{
			return this.defaultRole.avatar.medium;
		},
	},
	methods:
	{
		onImageLoadError()
		{
			this.imageLoadError = true;
		},
	},
	template: `
		<div class="bx-im-role-item__container">
			<div class="bx-im-role-item__avatar">
				<img v-if="!imageLoadError" :src="roleAvatar" :alt="roleName" @error="onImageLoadError">
				<img v-else :src="defaultRoleAvatarUrl" :alt="roleName">
			</div>
			<div class="bx-im-role-item__info">
				<div class="bx-im-role-item__name" :title="roleName">{{ roleName }}</div>
				<div class="bx-im-role-item__description" :title="roleSDescription">{{ roleSDescription }}</div>
			</div>
		</div>
	`,
};
