import {Menu, MenuItem} from 'main.popup';
import {Runtime, Type, Text} from 'main.core';
import {Vue} from "ui.vue";
import {config} from "../../config";
import type {BaseEvent} from "main.core.events";

Vue.component(config.templateFieldQuantity,
{
	/**
	 * @emits 'onChangeQuantity' {quantity: number}
	 * @emits 'onSelectMeasure' {quantity: number, }
	 */

	props: {
		measureCode: Number,
		measureRatio: Number,
		measureName: String,
		quantity: Number,
		editable: Boolean,
		saveableMeasure: Boolean,
		hasError: Boolean,
		options: Object,
	},
	created()
	{
		this.onInputQuantityHandler = Runtime.debounce(this.onInputQuantity, 500, this);
	},
	methods:
	{
		onInputQuantity(event: BaseEvent): void
		{
			if (!this.editable)
			{
				return;
			}

			event.target.value = event.target.value.replace(/[^.\d]/g,'.');
			const newQuantity = Text.toNumber(event.target.value);
			const lastSymbol = event.target.value.substr(-1);

			if (lastSymbol === '.')
			{
				return;
			}

			this.changeQuantity(newQuantity);
		},
		calculateCorrectionFactor(quantity, measureRatio)
		{
			let factoredQuantity = quantity;
			let factoredRatio = measureRatio;
			let correctionFactor = 1;

			while (!(Number.isInteger(factoredQuantity) && Number.isInteger(factoredRatio)))
			{
				correctionFactor *= 10;
				factoredQuantity = quantity * correctionFactor;
				factoredRatio = measureRatio * correctionFactor;
			}

			return correctionFactor;
		},
		incrementValue()
		{
			if (!this.editable)
			{
				return;
			}

			const correctionFactor = this.calculateCorrectionFactor(this.quantity, this.measureRatio);
			const quantity = (this.quantity * correctionFactor + this.measureRatio * correctionFactor) / correctionFactor;
			this.changeQuantity(quantity);
		},
		decrementValue()
		{
			if (this.quantity > this.measureRatio && this.editable)
			{
				const correctionFactor = this.calculateCorrectionFactor(this.quantity, this.measureRatio);
				const quantity = (this.quantity * correctionFactor - this.measureRatio * correctionFactor) / correctionFactor;
				this.changeQuantity(quantity);
			}
		},
		changeQuantity(value: number)
		{
			this.$emit('onChangeQuantity', value);
		},
		showPopupMenu(target: HTMLElement)
		{
			if (!this.editable || !Type.isArray(this.options.measures))
			{
				return;
			}

			const menuItems = [];
			this.options.measures.forEach((item) => {
				menuItems.push({
					text: item.SYMBOL,
					item: item,
					onclick: this.selectMeasure,
				})
			});


			if (menuItems.length > 0)
			{
				this.popupMenu = new Menu({
					bindElement: target,
					items: menuItems
				});

				this.popupMenu.show();
			}
		},
		selectMeasure(event: BaseEvent, params: MenuItem)
		{
			this.$emit('onSelectMeasure', {
				code: params.options?.item.CODE,
				name: params.options?.item.SYMBOL,
			});

			if (this.popupMenu)
			{
				this.popupMenu.close();
			}
		},
	},
	// language=Vue
	template: `
		<div class="catalog-pf-product-input-wrapper" v-bind:class="{ 'ui-ctl-danger': hasError }">
			<input 	
				type="text" class="catalog-pf-product-input"
				v-bind:class="{ 'catalog-pf-product-input--disabled': !editable }"
				:value="quantity"
				@input="onInputQuantityHandler"
				:disabled="!editable"
				data-name="quantity"
				:data-value="quantity"
			>
			<div 
				class="catalog-pf-product-input-info catalog-pf-product-input-info--action" 
				@click="showPopupMenu($event.target)"
			>
				<span :title="measureName">{{ measureName }}</span>
			</div>
		</div>
	`
});