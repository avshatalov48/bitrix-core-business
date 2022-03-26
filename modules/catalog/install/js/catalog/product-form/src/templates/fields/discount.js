import {Menu} from 'main.popup';
import { Runtime, Text, Type} from 'main.core';
import {Vue} from "ui.vue";
import {config} from "../../config";
import {DiscountType} from "catalog.product-calculator";

Vue.component(config.templateFieldDiscount,
{
	/**
	 * @emits 'changeDiscountType' {type: Y|N}
	 * @emits 'changeDiscount' {discountValue: number}
	 */

	props: {
		editable: Boolean,
		options: Object,
		discount: Number,
		discountType: Number,
		discountRate: Number,
	},
	created()
	{
		this.onInputDiscount = Runtime.debounce(this.onChangeDiscount, 500, this);
		this.currencySymbol = this.options.currencySymbol;
	},
	methods:
	{
		onChangeType(event, params)
		{
			if (!this.editable)
			{
				return;
			}

			const type = (Text.toNumber(params?.options?.type) === DiscountType.MONETARY) ?  DiscountType.MONETARY : DiscountType.PERCENTAGE;
			this.$emit('changeDiscountType', type);

			if (this.popupMenu)
			{
				this.popupMenu.close();
			}
		},
		onChangeDiscount(event)
		{
			const discountValue = Text.toNumber(event.target.value) || 0;
			if (discountValue === Text.toNumber(this.discount) || !this.editable)
			{
				return;
			}

			this.$emit('changeDiscount', discountValue);
		},
		showPopupMenu(target)
		{
			if (!this.editable || !Type.isArray(this.options.allowedDiscountTypes))
			{
				return;
			}

			const menuItems = [];
			if (this.options.allowedDiscountTypes.includes(DiscountType.PERCENTAGE))
			{
				menuItems.push({
					text: '%',
					onclick: this.onChangeType,
					type: DiscountType.PERCENTAGE,
				})
			}

			if (this.options.allowedDiscountTypes.includes(DiscountType.MONETARY))
			{
				menuItems.push({
					text: this.currencySymbol,
					onclick: this.onChangeType,
					type: DiscountType.MONETARY,
				})
			}

			if (menuItems.length > 0)
			{
				this.popupMenu = new Menu({
					bindElement: target,
					items: menuItems
				});

				this.popupMenu.show();
			}
		},
	},
	computed: {
		getDiscountInputValue()
		{
			if (Text.toNumber(this.discountType) === DiscountType.PERCENTAGE)
			{
				return Text.toNumber(this.discountRate);
			}
			return Text.toNumber(this.discount);
		},
		getDiscountSymbol()
		{
			return Text.toNumber(this.discountType) === DiscountType.PERCENTAGE ? '%' : this.currencySymbol;
		},
	},
	// language=Vue
	template: `
		<div class="catalog-pf-product-input-wrapper catalog-pf-product-input-wrapper--left">
			<input class="catalog-pf-product-input catalog-pf-product-input--align-right catalog-pf-product-input--right"
					v-bind:class="{ 'catalog-pf-product-input--disabled': !editable }"
					ref="discountInput" 
					:value="getDiscountInputValue"
					:v-model="discountRate"
					@input="onInputDiscount"
					placeholder="0"
					:disabled="!editable">
			<div class="catalog-pf-product-input-info catalog-pf-product-input-info--action" 
				@click="showPopupMenu">
				<span v-html="getDiscountSymbol"></span>
			</div>
		</div>
	`
});