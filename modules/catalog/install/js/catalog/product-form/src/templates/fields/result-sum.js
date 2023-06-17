import {Runtime, Text} from 'main.core';
import {Vue} from "ui.vue";
import {config} from "../../config";
import type {BaseEvent} from "main.core.events";

Vue.component(config.templateFieldResultSum,
{
	/**
	 * @emits 'onChangeSum' {sum: number}
	 */

	props: {
		sum: Number,
		editable: Boolean,
		options: Object,
	},
	created()
	{
		this.onInputSumHandler = Runtime.debounce(this.onInputSum, 500, this);
	},
	methods:
	{
		onInputSum(event: BaseEvent): void
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
			let newSum = Text.toNumber(event.target.value);
			if (lastSymbol === '.' || lastSymbol === ',')
			{
				return;
			}

			if (newSum < 0)
			{
				newSum *= -1;
			}

			this.$emit('onChangeSum', newSum);
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
		<div class="catalog-pf-product-input-wrapper">
			<input 	type="text" 
					class="catalog-pf-product-input catalog-pf-product-input--align-right"
					:class="{ 'catalog-pf-product-input--disabled': !editable }"
					:value="sum"
					@input="onInputSumHandler"
					:disabled="!editable"
					data-name="sum"
					:data-value="sum"
			>
			<div class="catalog-pf-product-input-info"
				 :class="{ 'catalog-pf-product-input--disabled': !editable }"
				 v-html="currencySymbol"
			></div>
		</div>
	`
});