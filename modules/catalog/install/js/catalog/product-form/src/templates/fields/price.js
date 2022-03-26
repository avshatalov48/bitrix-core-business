import {Runtime, Text} from 'main.core';
import {Vue} from "ui.vue";
import {config} from "../../config";
import type {BaseEvent} from "main.core.events";

Vue.component(config.templateFieldPrice,
{
	/**
	 * @emits 'onChangePrice' {price: number}
	 * @emits 'saveCatalogField' {}
	 */

	props: {
		selectorId: String,
		price: Number,
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
			const lastSymbol = event.target.value.substr(-1);
			if (lastSymbol === ',')
			{
				event.target.value = event.target.value.replace(',', ".");
			}

			let newPrice = Text.toNumber(event.target.value);
			if (lastSymbol === '.' || lastSymbol === ',')
			{
				return;
			}

			if (newPrice < 0)
			{
				newPrice *= -1;
			}

			this.$emit('onChangePrice', newPrice);
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
					v-model.lazy="price"
					@input="onInputPriceHandler"
					:disabled="!editable"
			>
			<div class="catalog-pf-product-input-info" v-html="currencySymbol"></div>
		</div>
	`
});