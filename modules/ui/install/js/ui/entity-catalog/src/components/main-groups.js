import { Loc } from 'main.core';
import { GroupList } from './group-list';

import { GroupData } from '@/type/group';

import '../css/main-groups.css';

export const MainGroups = {
	emits: ['groupSelected'],

	name: 'ui-entity-catalog-main-groups',
	components: {
		GroupList,
	},
	props: {
		recentGroupData: {
			type: GroupData,
			required: false,
		},
		groups: {
			type: Array,
			required: true,
		},
		showRecentGroup: {
			type: Boolean,
			default: false,
		},
		searching: {
			type: Boolean,
			default: false,
		}
	},
	data(): Object
	{
		const recentGroup = this.getRecentGroup();
		recentGroup[0] = Object.assign(recentGroup[0], this.recentGroupData ?? {});

		let selectedGroup = this.groups.find(group => group.selected) ?? null;
		if (!selectedGroup)
		{
			selectedGroup = recentGroup.find(group => group.selected) ?? null;
		}

		return {
			shownGroups: this.groups,
			selectedGroup: null,
			recentGroup,
		};
	},
	watch: {
		selectedGroup(newGroup: ?GroupData)
		{
			const newGroupId = newGroup ? newGroup.id : null;

			this.shownGroups = this.shownGroups.map(groupList => groupList.map((group) => ({
				...group,
				selected: group.id === newGroupId,
			})));

			if (this.showRecentGroup && newGroupId !== this.recentGroup[0].id)
			{
				this.recentGroup = [Object.assign(this.recentGroup[0], {selected: false})];
			}

			this.$emit('groupSelected', newGroup);
		},
	},
	beforeUpdate()
	{
		if (this.searching)
		{
			this.shownGroups = this.shownGroups.map(groupList => groupList.map((group) => ({
				...group,
				selected: false,
			})));

			this.recentGroup = [Object.assign(this.recentGroup[0], {selected: false})];
		}
	},
	methods:{
		getRecentGroup(): Array<GroupData>
		{
			return [{
				id: 'recent',
				name: Loc.getMessage('UI_JS_ENTITY_CATALOG_GROUP_LIST_RECENT_GROUP_DEFAULT_NAME'),
				icon: `
					<svg width="18" height="14" viewBox="0 0 18 14" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path class="ui-entity-catalog__svg-icon-blue" fill-rule="evenodd" clip-rule="evenodd" d="M9.369 13.2593C13.0305 13.2593 15.9986 10.2911 15.9986 6.62965C15.9986 2.9682 13.0305 0 9.369 0C6.00693 0 3.22939 2.50263 2.79764 5.74663H0L3.69844 9.44506L7.39687 5.74663H4.48558C4.90213 3.4276 6.93006 1.66789 9.369 1.66789C12.1093 1.66789 14.3308 3.88935 14.3308 6.62965C14.3308 9.36995 12.1093 11.5914 9.369 11.5914C9.2435 11.5914 9.11909 11.5867 8.99593 11.5776V13.249C9.11941 13.2558 9.2438 13.2593 9.369 13.2593ZM10.0865 4.01429H8.41983V8.18096H9.65978H10.0865H12.1195V6.56367H10.0865V4.01429Z"></path>
					</svg>
				`,
			}];
		},
		handleGroupSelected(group: GroupData)
		{
			this.selectedGroup = group;
		},
		handleRecentGroupSelected(group: GroupData)
		{
			group.selected = true;
			this.selectedGroup = group;
		},
		handleGroupUnselected()
		{
			this.selectedGroup = null;
		},
	},
	template: `
		<div class="ui-entity-catalog__main-groups">
			<div class="ui-entity-catalog__main-groups-head">
				<slot name="group-list-header"/>
			</div>
			<div class="ui-entity-catalog__recently" v-if="showRecentGroup">
				<GroupList
					:groups="recentGroup"
					@groupSelected="handleRecentGroupSelected"
					@groupUnselected="handleGroupUnselected"
				>
					<template #group="groupSlotProps">
						<slot
							name="group"
							v-bind:groupData="groupSlotProps.groupData"
							v-bind:handleClick="groupSlotProps.handleClick"
						/>
					</template>
				</GroupList>
			</div>
			<div class="ui-entity-catalog__main-groups-content">
				<GroupList
					:groups="groupList"
					v-for="groupList in shownGroups"
					@groupSelected="handleGroupSelected"
					@groupUnselected="handleGroupUnselected"
				>
					<template #group="groupSlotProps">
						<slot
							name="group"
							v-bind:groupData="groupSlotProps.groupData"
							v-bind:handleClick="groupSlotProps.handleClick"
						/>
					</template>
				</GroupList>
			</div>
			<div class="ui-entity-catalog__main-groups-footer">
				<slot name="group-list-footer"/>
			</div>
		</div>
	`,
};