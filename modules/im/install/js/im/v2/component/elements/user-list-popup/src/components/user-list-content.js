import { ImModelUser } from 'im.v2.model';
import { Loader } from 'im.v2.component.elements.loader';

import { UserListService } from '../classes/user-list-service';

import { UserItem } from './user-item';

import '../css/user-list-content.css';

import type { JsonObject } from 'main.core';

// @vue/component
export const UserListContent = {
	components: { UserItem, Loader },
	props: {
		userIds: {
			type: Array,
			required: true,
		},
		adjustPopupFunction: {
			type: Function,
			required: true,
		},
		loading: {
			type: Boolean,
			required: false,
			default: false,
		},
		contextDialogId: {
			type: String,
			required: true,
		},
	},
	data(): JsonObject
	{
		return {
			hasError: false,
			isLoadingUsers: false,
		};
	},
	computed:
	{
		isLoading(): boolean
		{
			return this.loading || this.isLoadingUsers;
		},
	},
	watch:
	{
		userIds()
		{
			this.$nextTick(() => {
				this.adjustPopupFunction();
			});
		},
	},
	created()
	{
		if (this.needUserRequest())
		{
			this.requestUserData();
		}
	},
	methods:
	{
		getUserListService(): UserListService
		{
			if (!this.userListService)
			{
				this.userListService = new UserListService();
			}

			return this.userListService;
		},
		getUser(userId): ?ImModelUser
		{
			return this.$store.getters['users/get'](userId);
		},
		needUserRequest(): boolean
		{
			return this.userIds.some((userId) => !this.getUser(userId));
		},
		async requestUserData()
		{
			this.isLoadingUsers = true;
			await this.getUserListService().loadUsers(this.userIds)
				.catch(() => {
					this.hasError = true;
				});

			this.isLoadingUsers = false;
		},
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<div class="bx-im-user-list-content__container bx-im-user-list-content__scope">
			<template v-if="!isLoading && !hasError">
				<UserItem v-for="userId in userIds" :userId="userId" :contextDialogId="contextDialogId" />
			</template>
			<div v-else-if="isLoading" class="bx-im-user-list-content__loader-container">
				<Loader />
			</div>
			<div v-else-if="hasError">
				{{ loc('IM_ELEMENTS_CHAT_INFO_POPUP_NO_ACCESS') }}
			</div>
		</div>
	`,
};
