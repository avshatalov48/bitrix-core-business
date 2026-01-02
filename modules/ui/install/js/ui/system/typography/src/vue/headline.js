// @vue/component
export const Headline = {
	name: 'BHeadline',
	inheritAttrs: false,
	props: {
		size: {
			type: String,
			required: true,
			validator: (value) => ['xl', 'lg', 'md', 'sm', 'xs'].includes(value),
		},
		accent: {
			type: Boolean,
			default: false,
		},
		tag: {
			type: String,
			default: 'div',
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
		classes(): Array
		{
			return [
				'ui-headline',
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

export const HeadlineXl = {
	extends: Headline,
	props: {
		size: {
			default: 'xl',
		},
	},
};

export const HeadlineLg = {
	extends: Headline,
	props: {
		size: {
			default: 'lg',
		},
	},
};

export const HeadlineMd = {
	extends: Headline,
	props: {
		size: {
			default: 'md',
		},
	},
};

export const HeadlineSm = {
	extends: Headline,
	props: {
		size: {
			default: 'sm',
		},
	},
};

export const HeadlineXs = {
	extends: Headline,
	props: {
		size: {
			default: 'xs',
		},
	},
};
