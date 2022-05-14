export default {
	computed:
	{
		classes()
		{
			const classes = [
				'landing-block-node-button',
				'text-uppercase',
				'btn',
				'btn-xl',
				'pr-7',
				'pl-7',
				'u-btn-primary',
				'g-font-weight-700',
				'g-font-size-12',
				'g-rounded-50',
			];

			if (this.loading)
			{
				classes.push('loading');
			}

			return classes;
		},
	},
	methods:
	{
		onClick(event)
		{
			this.$emit('click', event);
		},
	}
};
