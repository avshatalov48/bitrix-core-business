import type { BitrixVueComponentProps } from 'ui.vue3';

export const ActionButton: BitrixVueComponentProps = {
	name: 'ActionButton',
	props: {
		icon: {
			type: String,
		},
		title: {
			type: String,
		},
		counter: {
			type: Number,
			default: 0,
		},
		selected: {
			type: Boolean,
			default: false,
		},
		buttonRef: {
			default: null,
		},
	},
	// language=Vue
	template: `
		<button class="ui-rich-text-area-action" :class="{ '--selected': selected }" :ref="buttonRef">
			<span class="ui-rich-text-area-action-icon"><span
				:class="icon"
				class="ui-icon-set"
				style="--ui-icon-set__icon-color: var(--ui-color-base-90)"
			></span></span>
			<span class="ui-rich-text-area-action-title">{{ title }}</span>
			<span class="ui-rich-text-area-action-counter" v-show="counter > 0">
				<span class="ui-counter ui-counter-sm ui-counter-gray"><span class="ui-counter-inner">{{ counter }}</span></span>
			</span>
		</button>
	`,
};
