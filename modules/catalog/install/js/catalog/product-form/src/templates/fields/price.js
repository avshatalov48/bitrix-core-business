import {Runtime} from 'main.core';
import {Vue} from "ui.vue";
import {config} from "../../config";
import type {BaseEvent} from "main.core.events";

Vue.component(config.templateFieldPrice,
{
	/**
	 * @emits 'changePrice' {price: number}
	 */

	props: {
		basePrice: Number,
		editable: Boolean,
		hasError: Boolean,
		options: Object,
	},
	created()
	{
		this.onInputPriceHandler = Runtime.debounce(this.onInputPrice, 500, this);
	},
	methods:
	{
		onInputPrice(event: BaseEvent): void
		{
			if (!this.editable)
			{
				return;
			}

			event.target.value = event.target.value.replace(/[^.,\d]/g,'');
			if (event.target.value === '')
			{
				event.target.value = 0;
			}
			let lastSymbol = event.target.value.substr(-1);
			if (lastSymbol === ',')
			{
				event.target.value = event.target.value.replace(',', ".");
			}
			let newPrice = parseFloat(event.target.value);
			if (newPrice < 0|| lastSymbol === '.' || lastSymbol === ',')
			{
				return;
			}

			this.$emit('changePrice', newPrice);
		},
	},
	computed:
	{
		localize()
		{
			return Vue.getFilteredPhrases('CATALOG_');
		},
		currencySymbol()
		{
			return this.options.currencySymbol || '';
		},
	},
	// language=Vue
	template: `
		<div class="catalog-pf-product-input-wrapper" v-bind:class="{ 'ui-ctl-danger': hasError }">
			<input 	type="text" class="catalog-pf-product-input catalog-pf-product-input--align-right"
					v-bind:class="{ 'catalog-pf-product-input--disabled': !editable }"
					:value="basePrice"
					@input="onInputPriceHandler"
					:disabled="!editable">
			<div class="catalog-pf-product-input-info" v-html="currencySymbol"></div>
		</div>
	`
});