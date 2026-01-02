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
		groups: {
			/** @type Array<Array<GroupData>> */
			type: Array,
			required: true,
		},
		searching: {
			type: Boolean,
			default: false,
		},
		selectedGroup: {
			/** @type GroupData */
			type: Object,
			default: null,
		},
	},
	computed: {
		processedGroups()
		{
			const selectedGroupId = this.searching ? null : (this.selectedGroup ? this.selectedGroup.id : null);

			const groupsClone = BX.Runtime.clone(this.groups);

			groupsClone.forEach(groupList => {
				groupList.forEach(group => {
					group.selected = (group.id === selectedGroupId);
				});
			});

			return groupsClone;
		},
		headerLists(): Array<Array<GroupData>>
		{
			return this.processedGroups
				.map(list => list.filter(g => g.isHeaderGroup))
				.filter(list => list.length > 0);
		},
		mainLists(): Array<Array<GroupData>>
		{
			return this.processedGroups
				.map(list => list.filter(g => !g.isHeaderGroup))
				.filter(list => list.length > 0);
		},
	},
	methods: {
		handleGroupSelected(group: GroupData)
		{
			this.$emit('groupSelected', group);
		},
		handleGroupUnselected()
		{
			this.$emit('groupSelected', null);
		},
	},
	template: `
		<div class="ui-entity-catalog__main-groups">
			<div class="ui-entity-catalog__main-groups-head">
				<slot name="group-list-header"/>
			</div>
			<div
				class="ui-entity-catalog__header-groups-content"
				v-if="headerLists && headerLists.length"
			>
				<GroupList
					:groups="headerLists"
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
			<div class="ui-entity-catalog__main-groups-content">
				<GroupList
					:groups="mainLists"
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
