export default {
	methods:
	{
		onClick()
		{
			this.$emit('start-payment', this.paySystem.ID);
		},
		getCheckTitle(check)
		{
			let title = this.localize.PAYMENT_PAY_PAYMENT_SYSTEM_COMPONENTS_11;
			return title
			.replace('#CHECK_ID#', check.id)
			.replace('#DATE_CREATE#', check.dateFormatted);
		}
	}
};