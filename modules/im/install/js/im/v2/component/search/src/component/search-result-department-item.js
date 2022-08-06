import {Type} from 'main.core';
import {SearchResultItem} from './search-result-item';
import {SearchService} from '../search-service';
import '../css/search.css';

// @vue/component
export const SearchResultDepartmentItem = {
	name: 'SearchResultDepartmentItem',
	components: {SearchResultItem},
	props: {
		item: {
			type: Object,
			required: true
		},
	},
	data: function() {
		return {
			expanded: false,
			isLoading: false,
			usersInDepartment: [],
		};
	},
	computed:
	{
		usersDialogIds()
		{
			return this.searchService.convertItemsToDialogIds(this.usersInDepartment);
		},
	},
	created()
	{
		this.searchService = new SearchService(this.$Bitrix);
	},
	methods:
	{
		onClick()
		{
			if (!this.expanded)
			{
				this.openDepartment();
			}
			else
			{
				this.closeDepartment();
			}
		},
		openDepartment()
		{
			this.isLoading = true;
			if (Type.isArrayFilled(this.usersInDepartment))
			{
				this.isLoading = false;
				this.expanded = true;

				return;
			}

			this.searchService.loadDepartmentUsers(this.item).then(departmentUsers => {
				this.usersInDepartment = departmentUsers;
				this.isLoading = false;
				this.expanded = true;
			});
		},
		closeDepartment()
		{
			this.expanded = false;
		},
	},
	// language=Vue
	template: `
		<div @click="onClick" class="bx-im-search-item">
			<div class="bx-im-search-avatar-wrap">
				<div :title="item.title" class="bx-im-component-avatar-wrap bx-im-component-avatar-size-l">
					<div class="bx-im-component-avatar-content bx-im-component-avatar-image bx-search-item-department-icon"></div>
				</div>
			</div>
			<div class="bx-im-search-result-item-title-content bx-im-component-chat-name-text">
				{{item.title}}
				<div class="bx-search-item-department-expand-button">
					<div v-if="isLoading" class="bx-search-item-department-expand-loader"></div>
					<div v-else-if="expanded" class="bx-search-item-department-down-arrow"></div>
					<div v-else class="bx-search-item-department-up-arrow"></div>
				</div>
			</div>
		</div>
		<template v-if="expanded">
			<SearchResultItem v-for="dialogId in usersDialogIds" :key="dialogId" :dialogId="dialogId" :child="true" />
		</template>
	`
};