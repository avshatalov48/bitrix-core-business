import { Group } from './group';
import type { GroupData } from '@/types/group';

import '../css/group-list.css';

export const GroupList = {
	emits: ['groupSelected', 'groupUnselected'],

	name: 'ui-entity-selector-group-list',
	components: {
		Group,
	},
	props: {
		/** @type Array<Array<GroupData>> */
		groups: {
			type: Array,
			required: true,
		},
	},
	computed: {
		groupLists(): Array<Array<GroupData>>
		{
			if (!this.groups || this.groups.length === 0)
			{
				return [];
			}

			if (Array.isArray(this.groups[0]))
			{
				return this.groups;
			}

			return [this.groups];
		},

		headerLists(): Array<Array<GroupData>>
		{
			return this.groupLists
				.map(list => list.filter(g => !!g.isHeaderGroup))
				.filter(list => list.length > 0);
		},

		mainLists(): Array<Array<GroupData>>
		{
			return this.groupLists
				.map(list => list.filter(g => !g.isHeaderGroup))
				.filter(list => list.length > 0);
		},
	},
	methods: {
		handleGroupSelected(group: GroupData)
		{
			this.$emit('groupSelected', group);
		},
		handleGroupUnselected(group: GroupData)
		{
			this.$emit('groupUnselected', group);
		},
	},
	template: `
		<div>
			<div
				class="ui-entity-catalog__header-groups-content"
				v-if="headerLists && headerLists.length"
			>
				<ul
					class="ui-entity-catalog__menu"
					v-for="(groupList, listIndex) in headerLists"
					:key="'header-'+listIndex"
				>
					<Group
						:group-data="group"
						:key="group.id"
						v-for="group in groupList"
						@selected="handleGroupSelected"
						@unselected="handleGroupUnselected"
					>
						<template #group="groupSlotProps">
							<slot
								name="group"
								v-bind:groupData="groupSlotProps.groupData"
								v-bind:handleClick="groupSlotProps.handleClick"
							/>
						</template>
					</Group>
				</ul>
			</div>

			<div>
				<ul
					class="ui-entity-catalog__menu"
					v-for="(groupList, listIndex) in mainLists"
					:key="'main-'+listIndex"
				>
					<Group
						:group-data="group"
						:key="group.id"
						v-for="group in groupList"
						@selected="handleGroupSelected"
						@unselected="handleGroupUnselected"
					>
						<template #group="groupSlotProps">
							<slot
								name="group"
								v-bind:groupData="groupSlotProps.groupData"
								v-bind:handleClick="groupSlotProps.handleClick"
							/>
						</template>
					</Group>
				</ul>
			</div>
		</div>
	`,
};
