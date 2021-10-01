import { BitrixVue } from 'ui.vue';
import { Type } from 'main.core';
import { CurrencyCore } from 'currency.currency-core';
import 'sale.checkout.view.element.animate-price'

BitrixVue.component('sale-checkout-view-element-animate_price', {
	props: ['sum', 'currency', 'prefix'],
	data()
	{
		return {
			displaySum: this.sum,
			interval: false,
		}
	},
	computed:
	{
		getPrefix()
		{
			return Type.isString(this.prefix) ? this.prefix:''
		},
		sumFormatted()
		{
			return CurrencyCore.currencyFormat(this.displaySum, this.currency, true);
		},
		getSum()
		{
			return this.sum
		}
	},
	methods:
	{
		animated()
		{
			clearInterval(this.interval);

			if(this.sum !== this.displaySum)
			{
				this.interval = window.setInterval(()=>
				{
					if(this.displaySum !== this.sum)
					{
						let diff = (this.sum - this.displaySum) / 5;

						diff = diff >= 0 ? Math.ceil(diff) : Math.floor(diff);

						if(diff > 0 && this.displaySum + diff > this.sum)
						{
							this.displaySum = this.sum
						}
						else
						{
							this.displaySum = this.displaySum + diff
						}
					}
					else
					{
						clearInterval(this.interval);
					}
				}, 10);
			}
		}
	},
	watch:
	{
		getSum()
		{
			this.animated()
		}
	},
	// language=Vue
	template: `
		<div v-html="getPrefix + sumFormatted"/>
	`
});