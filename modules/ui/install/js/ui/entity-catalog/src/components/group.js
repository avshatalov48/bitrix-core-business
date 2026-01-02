import { Type } from 'main.core';
import { GroupData } from '@/types/group';
import { CounterStyle } from 'ui.cnt';
import { Counter } from 'ui.vue3.components.counter';

import '../css/group.css';

export const Group = {
	emits: ['selected', 'unselected'],

	name: 'ui-entity-catalog-group',
	components: {
		Counter,
	},
	props: {
		/** @type GroupData */
		groupData: {
			type: GroupData,
			required: true,
		},
	},
	computed: {
		hasIcon(): boolean
		{
			return Type.isStringFilled(this.groupData.icon);
		},
		getCounterStyle(): string
		{
			return CounterStyle.FILLED_SUCCESS;
		},
		getCounterValue(): ?number
		{
			const custom = this.groupData?.customData ?? {};
			const value = custom.counterValue;
			const isValueInteger = Number.isInteger(value);

			return isValueInteger && value > 0 ? value : null;
		}
	},
	methods: {
		handleClick()
		{
			if (this.groupData.deselectable)
			{
				this.$emit(!this.groupData.selected ? 'selected' : 'unselected', this.groupData);
			}
			else if (!this.groupData.selected)
			{
				this.$emit('selected', this.groupData);
			}
		},
	},
	template: `
		<slot name="group" v-bind:groupData="groupData" v-bind:handleClick="handleClick">
			<li
				:class="{
					'ui-entity-catalog__menu_item': true,
					'--active': groupData.selected,
					'--disabled': groupData.disabled,
				}"
				@click="handleClick"
			>
				<span class="ui-entity-catalog__menu_item-icon" v-if="hasIcon" v-html="groupData.icon"/>
				<span class="ui-entity-catalog__menu_item-text">{{ groupData.name }}</span>
				<span
					v-if="getCounterValue !== null"
					class="ui-entity-catalog__menu_item-entity-count"
				>
					<Counter
						:value="getCounterValue"
						:style="getCounterStyle"
					>
					</Counter>
				</span>
			</li>
		</slot>
	`,
};