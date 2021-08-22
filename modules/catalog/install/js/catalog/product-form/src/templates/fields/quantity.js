import {Menu, MenuItem} from 'main.popup';
import {Runtime, Type} from 'main.core';
import {Vue} from "ui.vue";
import {config} from "../../config";
import type {BaseEvent} from "main.core.events";

Vue.component(config.templateFieldQuantity,
{
	/**
	 * @emits 'changeQuantity' {quantity: number}
	 * @emits 'changeMeasure' {quantity: number, }
	 */

	props: {
		measureCode: Number,
		measureRatio: Number,
		measureName: String,
		quantity: Number,
		editable: Boolean,
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
			let newQuantity = parseFloat(event.target.value);
			let lastSymbol = event.target.value.substr(-1);

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

			let correctionFactor = this.calculateCorrectionFactor(this.quantity, this.measureRatio);
			const quantity = (this.quantity * correctionFactor + this.measureRatio * correctionFactor) / correctionFactor;
			this.changeQuantity(quantity);
		},
		decrementValue()
		{
			if (this.quantity > this.measureRatio && this.editable)
			{
				let correctionFactor = this.calculateCorrectionFactor(this.quantity, this.measureRatio);
				const quantity = (this.quantity * correctionFactor - this.measureRatio * correctionFactor) / correctionFactor;
				this.changeQuantity(quantity);
			}
		},
		changeQuantity(value: number)
		{
			this.$emit('changeQuantity', value);
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
					onclick: this.changeMeasure,
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
		changeMeasure(event: BaseEvent, params: MenuItem)
		{
			this.$emit('changeMeasure', {
				code: param.options.item.CODE,
				name: param.options.item.SYMBOL,
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
				@input="onInputQuantity"
				:disabled="!editable"
			>
			<div 
				class="catalog-pf-product-input-info catalog-pf-product-input-info--action" 
				@click="showPopupMenu($event.target)"
			>
				<span>{{ measureName }}</span>
			</div>
		</div>
	`
});