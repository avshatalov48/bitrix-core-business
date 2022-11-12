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
		groups: {
			type: Array,
			required: true,
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
		}
	},
	template: `
		<ul class="ui-entity-catalog__menu">
			<Group
				:group-data="group"
				:key="group.id"
				v-for="group in groups"
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
	`,
}