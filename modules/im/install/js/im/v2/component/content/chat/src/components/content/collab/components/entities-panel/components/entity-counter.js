// @vue/component
export const EntityCounter = {
	name: 'EntityCounter',
	props:
	{
		counter: {
			type: Number,
			required: true,
		},
	},
	computed:
	{
		preparedCounter(): string
		{
			return this.counter > 99 ? '99+' : this.counter.toString();
		},
	},
	template: `
		<span class="bx-im-collab-header__link-counter">
			{{ preparedCounter }}
		</span>
	`,
};
