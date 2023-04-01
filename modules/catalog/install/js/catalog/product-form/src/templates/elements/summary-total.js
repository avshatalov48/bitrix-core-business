import {Vue} from 'ui.vue';
import {config} from '../../config';
import {CurrencyCore} from 'currency.currency-core';
import {Tag} from 'main.core';

Vue.component(config.templateSummaryTotal,
{
	props:
	{
		currency:
		{
			type: String,
			required: true,
		},
		sum:
		{
			required: true,
		},
		sumAdditionalClass: String,
		currencyAdditionalClass: String,
	},
	computed:
	{
		formattedSum()
		{
			const element = Tag.render`<span class="catalog-pf-text ${this.sumAdditionalClass ?? ''}">${this.sum}</span>`;
			return CurrencyCore.getPriceControl(element, this.currency);
		},
	},
	// language=Vue
	template: `
	<span class="catalog-pf-symbol" :class="currencyAdditionalClass" v-html="formattedSum"></span>
	`
});
