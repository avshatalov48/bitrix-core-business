import {Type, Text} from 'main.core';
import {ExpandAnimation} from 'im.v2.component.animation';
import {Loader} from 'im.v2.component.elements';
import {SearchResultItem} from './search-result-item';

import type {SearchItem} from '../classes/search-item';
import '../css/search-result-department-item.css';

// @vue/component
export const SearchResultDepartmentItem = {
	name: 'SearchResultDepartmentItem',
	components: {SearchResultItem, ExpandAnimation, Loader},
	inject: ['searchService'],
	props: {
		item: {
			type: Object,
			required: true
		},
		selectMode: {
			type: Boolean,
			default: false
		},
		isSelected: {
			type: Boolean,
			required: false,
		}
	},
	emits: ['clickItem'],
	data: function() {
		return {
			selected: this.isSelected,
			expanded: false,
			isLoading: false,
			usersInDepartment: [],
		};
	},
	computed:
	{
		searchItem(): SearchItem
		{
			return this.item;
		},
		departmentAvatarStyle(): {[string]: string}
		{
			if (this.searchItem.avatarOptions?.color)
			{
				return {backgroundColor: this.searchItem.avatarOptions.color};
			}

			return {};
		},
		title(): string
		{
			return Text.decode(this.searchItem.title);
		},
		selectedStyles()
		{
			return {
				'--selected': this.selectMode && this.selected
			};
		}
	},
	watch:
	{
		isSelected(newValue: boolean, oldValue: boolean)
		{
			if (newValue === true && oldValue === false)
			{
				this.selected = true;
			}
			else if (newValue === false && oldValue === true)
			{
				this.selected = false;
			}
		}
	},
	methods:
	{
		onClick(event)
		{
			if (!this.expanded)
			{
				this.openDepartment(event);
			}
			else
			{
				this.expanded = false;
			}
		},
		openDepartment(event)
		{
			if (this.selectMode)
			{
				this.selected = !this.selected;

				this.$emit('clickItem', {
					selectedItem: this.searchItem,
					selectedStatus: this.selected,
					nativeEvent: event
				});
			}
			else
			{
				this.isLoading = true;
				if (Type.isArrayFilled(this.usersInDepartment))
				{
					this.isLoading = false;
					this.expanded = true;

					return;
				}

				this.searchService.loadDepartmentUsers(this.searchItem).then(response => {
					this.usersInDepartment = [...response.values()].filter(user => user.isUser());
					this.isLoading = false;
					this.expanded = true;
				});
			}
		}
	},
	template: `
		<div 
			@click="onClick" 
			class="bx-im-search-result-department-item__container bx-im-search-result-department-item__scope"
			:class="selectedStyles"
		>
			<div class="bx-im-search-result-department-item__avatar_container">
				<div 
					:title="searchItem.title" 
					class="bx-im-search-result-department-item__avatar"
					:style="departmentAvatarStyle"
				></div>
			</div>
			<div class="bx-im-search-result-department-item__title_container">
				<div class="bx-im-search-result-department-item__title_text" :title="title">
					{{title}}
				</div>
				<div v-if="!selectMode" class="bx-im-search-result-department-item__expand-button">
					<div v-if="isLoading" class="bx-im-search-result-department-item__loader">
						<Loader />
					</div>
					<div v-else-if="expanded" class="bx-im-search-result-department-item__arrow --down"></div>
					<div v-else class="bx-im-search-result-department-item__arrow"></div>
				</div>
				<div v-if="selectMode && selected" class="bx-im-search-result-department-item__selected"></div>
			</div>
		</div>
		<ExpandAnimation>
			<div v-if="expanded" class="bx-im-search-result-department-item__users">
				<SearchResultItem 
					v-for="user in usersInDepartment" 
					:key="user.getEntityFullId()" 
					:item="user"
					@clickItem="$emit('clickItem', $event)"
				/>
			</div>
		</ExpandAnimation>
	`
};