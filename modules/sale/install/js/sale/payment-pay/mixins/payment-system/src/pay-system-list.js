export default {
	methods:
	{
		isItemLoading(paySystemId)
		{
			return (this.selectedPaySystem === paySystemId) && this.loading;
		},
		startPayment(paySystemId)
		{
			this.$emit('start-payment', paySystemId);
		},
	}
};