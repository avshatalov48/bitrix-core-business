import {Dom, Type} from 'main.core';

import {Utils} from 'im.old-chat-embedding.lib.utils';

import {SearchResultItem} from './search-result-item';
import {SearchService} from '../search-service';
import {SearchCache} from '../search-cache';
import {SearchRecentList} from '../search-recent-list';

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
		departmentAvatarStyle()
		{
			if (this.item.avatarOptions?.color)
			{
				return {backgroundColor: this.item.avatarOptions.color};
			}

			return {backgroundColor: '#df532d'};
		},
		title()
		{
			return Utils.text.htmlspecialcharsback(this.item.title);
		}
	},
	created()
	{
		const cache = new SearchCache(this.getCurrentUserId());
		const recentList = new SearchRecentList(this.$Bitrix);
		this.searchService = SearchService.getInstance(this.$Bitrix, cache, recentList);
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

			this.searchService.loadDepartmentUsers(this.item).then(usersAndDepartments => {
				this.usersInDepartment = [...usersAndDepartments.values()].filter(user => user.isUser());
				this.isLoading = false;
				this.expanded = true;
			});
		},
		closeDepartment()
		{
			this.expanded = false;
		},
		getCurrentUserId(): number
		{
			return this.$store.state.application.common.userId;
		},
		enterTransition(element)
		{
			Dom.style(element, 'height', 0);
			Dom.style(element, 'opacity', 0);

			requestAnimationFrame(() => {
				requestAnimationFrame(() => {
					Dom.style(element, 'opacity', 1);
					Dom.style(element, 'height', `${element.scrollHeight}px`);
				});
			});
		},
		afterEnterTransition(element)
		{
			Dom.style(element, 'height', 'auto');
		},
		leaveTransition(element)
		{
			Dom.style(element, 'height', `${element.scrollHeight}px`);

			requestAnimationFrame(() => {
				Dom.style(element, 'height', 0);
				Dom.style(element, 'opacity', 0);
			});
		},
	},
	// language=Vue
	template: `
		<div @click="onClick" class="bx-im-search-item">
			<div class="bx-im-search-avatar-wrap">
				<div :title="item.title" class="bx-im-component-avatar-wrap bx-im-component-avatar-size-l">
					<div 
						class="bx-im-component-avatar-content bx-im-component-avatar-image bx-search-item-department-icon"
						:style="departmentAvatarStyle"
					></div>
				</div>
			</div>
			<div class="bx-im-search-result-item-content bx-im-search-result-item-department-content">
				<div class="bx-im-component-chat-title-wrap">
					<div class="bx-im-component-chat-name-text">
						{{title}}
					</div>
				</div>
				<div class="bx-search-item-department-expand-button">
					<div v-if="isLoading" class="bx-search-loader bx-search-loader-large-size bx-search-item-department-expand-loader"></div>
					<div v-else-if="expanded" class="bx-search-item-department-down-arrow"></div>
					<div v-else class="bx-search-item-department-up-arrow"></div>
				</div>
			</div>
		</div>
		<transition
			name="bx-im-search-department-expand"
			@enter="enterTransition"
			@after-enter="afterEnterTransition"
			@leave="leaveTransition"
		>
			<div v-if="expanded" class="bx-search-department-users-wrapper">
				<div class="bx-search-department-users">
					<SearchResultItem v-for="user in usersInDepartment" :key="user.getEntityFullId()" :item="user" :child="true"/>
				</div>
			</div>
		</transition>
	`
};