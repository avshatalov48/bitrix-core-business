import './icon-button.css';
import { computed, toRefs, toValue } from 'ui.vue3';
import { BIcon } from 'ui.icon-set.api.vue';

export type IconButtonSetup = {
	iconName: string,
	color: string,
	size: number,
	buttonClassNames: { [string]: boolean },
	buttonStyle: { [string]: string },
	iconClassNames: { [string]: boolean },
};

// @vue/component
export const IconButton = {
	name: 'icon-button',
	components: {
		BIcon,
	},
	props: {
		iconName: {
			type: String,
			default: '',
		},
		size: {
			type: [Number, String],
			default: 16,
		},
		color: {
			type: String,
			default: '#959CA4',
		},
		active: {
			type: Boolean,
			default: false,
		},
		activeColor: {
			type: String,
			default: '#4A9DFF',
		},
		disabled: {
			type: Boolean,
			default: false,
		},
	},
	setup(props): IconButtonSetup
	{
		const { size, active, disabled } = toRefs(props);
		const buttonClassNames = computed(() => ({
			'ui-block-diagram-icon-button': true,
			'--disabled': toValue(disabled),
		}));
		const buttonStyle = computed(() => ({
			width: `${toValue(size)}px`,
			height: `${toValue(size)}px`,
		}));
		const iconClassNames = computed(() => ({
			'ui-block-diagram-icon-button__icon': true,
			'--active': toValue(active),
		}));

		return {
			buttonClassNames,
			buttonStyle,
			iconClassNames,
		};
	},
	template: `
		<button
			:class="buttonClassNames"
			:style="buttonStyle"
		>
			<slot>
				<BIcon
					:class="iconClassNames"
					:name="iconName"
					:color="color"
					:size="size"
				/>
			</slot>
		</button>
	`,
};
