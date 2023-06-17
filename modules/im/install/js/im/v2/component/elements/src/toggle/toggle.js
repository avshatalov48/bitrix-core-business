import './toggle.css';

export const ToggleSize = {
	S: 'S',
	M: 'M'
};

// @vue/component
export const Toggle = {
	name: 'ToggleControl',
	props: {
		size: {
			type: String,
			required: true
		},
		isEnabled: {
			type: Boolean,
			default: true
		}
	},
	emits: ['change'],
	data()
	{
		return {};
	},
	computed: {
		containerClasses(): string[]
		{
			const classes = [`--size-${this.size.toLowerCase()}`];
			if (!this.isEnabled)
			{
				classes.push('--off');
			}

			return classes;
		}
	},
	methods:
	{
		toggle()
		{
			this.$emit('change', !this.isEnabled);
		}
	},
	template:
	`
		<div @click="toggle" :class="containerClasses" class="bx-im-toggle__container bx-im-toggle__scope">
			<span class="bx-im-toggle__cursor"></span>
			<span class="bx-im-toggle__enabled"></span>
			<span class="bx-im-toggle__disabled"></span>
		</div>
	`
};