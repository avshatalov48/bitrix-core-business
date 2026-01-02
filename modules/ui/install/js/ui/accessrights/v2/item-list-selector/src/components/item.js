import { BIcon, Outline } from 'ui.icon-set.api.vue';
import { Checkbox } from './checkbox';
import 'ui.icon-set.actions';
import 'ui.icon-set.outline';
import 'ui.forms';
import { hint } from 'ui.vue3.directives.hint';

export const Item = {
	name: 'Item',
	components: {
		BIcon,
		Checkbox,
	},
	props: {
		id: {
			type: String,
			required: true,
		},
		title: {
			type: String,
			required: true,
		},
		isSelected: {
			type: Boolean,
		},
		isDraggable: {
			type: Boolean,
		},
		hint: {
			type: String,
			default: null,
		},
	},
	directives: {
		hint,
	},
	methods: {
		handleClick(event) {
			if (!event.target.closest('.ui-item-selector-item__drag-icon'))
			{
				this.$emit('update:selected', !this.isSelected);
			}
		},
	},
	computed: {
		Outline(): Outline {
			return Outline;
		},
		checkboxStatus(): string {
			return this.isSelected ? 'checked' : 'unchecked';
		},
		itemClasses(): Object {
			return {
				'ui-item-selector-item--active': this.isSelected,
			};
		},
	},
	template: `
		<div
			class="ui-item-selector-item"
			:class="itemClasses"
			@click="handleClick"
			:data-id="id"
			:data-selected="isSelected"
			v-hint="hint"
		>
			<BIcon
				class="ui-item-selector-item__drag-icon"
				:class="isDraggable ? 'ui-item-selector-item__drag_element' : ''"
				:name="Outline.DRAG_M"
				:color="isSelected ? 'var(--ui-color-accent-main-primary-alt)' : 'var(--ui-color-g-content-grey-2)'"
				:style="{ opacity: isDraggable ? '1' : '0' }"
			></BIcon>

			<span class="ui-item-selector-item__title" :title="title">
				{{ title }}
			</span>

			<Checkbox
				:status="checkboxStatus"
			/>
		</div>
	`,
};
