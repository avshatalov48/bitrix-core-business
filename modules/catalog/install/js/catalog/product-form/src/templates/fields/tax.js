import {Menu} from 'main.popup';
import {Text, Type} from 'main.core';
import {Vue} from "ui.vue";
import {config} from "../../config";

Vue.component(config.templateFieldTax,
{
	/**
	 * @emits 'changeTax' {taxValue: number}
	 */

	props: {
		taxId: Number,
		editable: Boolean,
		options: Object,
	},
	data()
	{
		return {
			taxValue: this.getTaxList()[this.taxId] || 0
		};
	},
	methods:
	{
		onChangeValue(event, params)
		{
			const taxValue = Text.toNumber(params?.options?.item);
			if (taxValue === Text.toNumber(this.taxValue) || !this.editable)
			{
				return;
			}

			this.$emit('changeTax', {
				taxValue,
				taxId: params?.options?.id
			});

			if (this.popupMenu)
			{
				this.popupMenu.close();
			}
		},
		getTaxList()
		{
			return Type.isArray(this.options.taxList) ? this.options.taxList : [];
		},
		showPopupMenu(target)
		{
			if (!this.editable || !Type.isArray(this.options.taxList))
			{
				return;
			}
			const menuItems = [];
			this.options.taxList.forEach((item, id) => {
				menuItems.push({
					id,
					text: item + '%',
					item: item,
					onclick: this.onChangeValue,
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
	},
	// language=Vue
	template: `
		<div class="catalog-pf-product-input-wrapper catalog-pf-product-input-wrapper--right" @click="showPopupMenu">
			<div class="catalog-pf-product-input">{{this.taxValue}}%</div>
			<div class="catalog-pf-product-input-info catalog-pf-product-input-info--dropdown"></div>
		</div>
	`
});