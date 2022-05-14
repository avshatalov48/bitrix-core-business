import {BitrixVue} from 'ui.vue';

export default {
	props:
	{
		paySystems:
		{
			type: Array,
			default: [],
			required: false,
		},
	},
	data()
	{
		return {
			selectedPaySystem: null,
		};
	},
	computed:
	{
		selectedName()
		{
			return this.selectedPaySystem ? this.selectedPaySystem.NAME : '';
		},
		selectedDescription()
		{
			return this.selectedPaySystem ? BX.util.htmlspecialchars(this.selectedPaySystem.DESCRIPTION) : '';
		}
	},
	methods:
	{
		showInfo(paySystem)
		{
			this.selectedPaySystem = paySystem;
		},
		logoStyle(paySystem)
		{
			const defaultLogo = '/bitrix/js/salescenter/payment-pay/payment-method/images/default_logo.png';
			const src = paySystem.LOGOTIP || defaultLogo;

			return `background-image: url("${BX.util.htmlspecialchars(src)}")`;
		},
	}
};