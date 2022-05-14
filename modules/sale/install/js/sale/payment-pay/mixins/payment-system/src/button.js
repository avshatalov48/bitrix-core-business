export default {
	computed:
	{
		classes()
		{
			return {
				'order-payment-method-item-button': true,
				'btn': true,
				'btn-primary': true,
				'rounded-pill': true,
				'pay-mode': true,
				'btn-wait': this.loading
			};
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
