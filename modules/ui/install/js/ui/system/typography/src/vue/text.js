// @vue/component
export const Text = {
	name: 'BText',
	inheritAttrs: false,
	props: {
		size: {
			type: String,
			required: true,
			validator: (value) => ['2xl', 'xl', 'lg', 'md', 'sm', 'xs', '2xs', '3xs', '4xs'].includes(value),
		},
		accent: {
			type: Boolean,
			default: false,
		},
		tag: {
			type: String,
			default: 'span',
		},
		align: {
			type: String,
			default: null,
			validator: (value) => value === null || ['left', 'center', 'right', 'justify'].includes(value),
		},
		transform: {
			type: String,
			default: null,
			validator: (value) => value === null || ['uppercase', 'lowercase', 'capitalize'].includes(value),
		},
		wrap: {
			type: String,
			default: null,
			validator: (value) => value === null || ['truncate', 'break-words', 'break-all'].includes(value),
		},
		className: {
			type: [String, Array, Object],
			default: null,
		},
	},
	computed: {
		classes(): string[]
		{
			return [
				'ui-text',
				`--${this.size}`,
				{
					'--accent': this.accent,
					[`--align-${this.align}`]: this.align,
					[`--${this.transform}`]: this.transform,
					[`--${this.wrap}`]: this.wrap,
				},
				this.className,
			];
		},
	},
	template: `
		<component
			:is="tag"
			:class="classes"
			v-bind="$attrs"
		>
			<slot/>
		</component>
	`,
};

export const Text2Xl = {
	extends: Text,
	props: {
		size: {
			default: '2xl',
		},
	},
};

export const TextXl = {
	extends: Text,
	props: {
		size: {
			default: 'xl',
		},
	},
};

export const TextLg = {
	extends: Text,
	props: {
		size: {
			default: 'lg',
		},
	},
};

export const TextMd = {
	extends: Text,
	props: {
		size: {
			default: 'md',
		},
	},
};

export const TextSm = {
	extends: Text,
	props: {
		size: {
			default: 'sm',
		},
	},
};

export const TextXs = {
	extends: Text,
	props: {
		size: {
			default: 'xs',
		},
	},
};

export const Text2Xs = {
	extends: Text,
	props: {
		size: {
			default: '2xs',
		},
	},
};

export const Text3Xs = {
	extends: Text,
	props: {
		size: {
			default: '3xs',
		},
	},
};

export const Text4Xs = {
	extends: Text,
	props: {
		size: {
			default: '4xs',
		},
	},
};
