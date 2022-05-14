export default {
	methods:
	{
		onClick()
		{
			this.$emit('click', this.id);
		},
	},
	computed: {
		logoStyle()
		{
			const defaultLogo = '/bitrix/js/sale/payment-pay/images/default_logo.png';
			const src = this.logo || defaultLogo;

			return `background-image: url("${BX.util.htmlspecialchars(src)}")`;
		}
	}
};
