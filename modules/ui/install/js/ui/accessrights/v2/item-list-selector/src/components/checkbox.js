import { BIcon, Outline } from 'ui.icon-set.api.vue';
import 'ui.icon-set.actions';
import 'ui.icon-set.outline';
import 'ui.forms';

export const Checkbox = {
	name: 'Checkbox',
	components: {
		BIcon,
	},
	props: {
		status: {
			type: String,
			required: true,
			validator: (value) => ['checked', 'indeterminate', 'unchecked'].includes(value),
		},
	},
	computed: {
		set(): Outline {
			return Outline;
		},
		iconName(): string {
			return this.status === 'checked' ? this.set.CHECK_S : this.set.MINUS_S;
		},
		iconColor(): string {
			return this.status === 'checked' ? 'var(--ui-color-bg-content-light)' : 'var(--ui-color-accent-main-primary)';
		},
	},
	methods: {
		handleClick() {
			this.$emit('change');
		},
	},
	template: `
		<label
		  class="ui-item-selector__checkbox"
		  :class="{ 
		    'ui-item-selector__checkbox--active': status === 'checked',
		    'ui-item-selector__checkbox--indeterminate': status === 'indeterminate'
		  }"
		  @click="handleClick"
		>
		  <BIcon
		    v-if="status !== 'unchecked'"
		    class="ui-item-selector-item__checked-icon"
		    :name="iconName"
		    :color="iconColor"
		    :size="22"
		  ></BIcon>
		</label>
	`,
};
