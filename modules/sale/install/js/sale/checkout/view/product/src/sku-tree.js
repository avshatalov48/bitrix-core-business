import { BitrixVue } from 'ui.vue';
import { SkuTree } from 'catalog.sku-tree';
import { EventType } from 'sale.checkout.const';
import { EventEmitter } from 'main.core.events';

BitrixVue.component('sale-checkout-view-product-sku_tree', {
	props: ['tree', 'index'],
	data()
	{
		return {
			skuTree: new SkuTree({
				skuTree: this.tree,
				selectable: true,
				hideUnselected: false
			})
		}
	},
	computed:
	{
		getHash()
		{
			return this.prepareValues(this.tree.SELECTED_VALUES)
		}
	},
	methods:
	{
		prepareValues(values)
		{
			return Object.keys(values)
			.concat(
				Object.values(values))
			.join()
		},
		appendBlockHtml()
		{
			let wrapper = this.$refs.container;
			wrapper.appendChild(this.skuTree.layout());
		}
	},
	watch:
	{
		getHash()
		{
			let selectedValues  = this.tree.SELECTED_VALUES;

			try
			{
				for (let propertyId in selectedValues)
				{
					if (!selectedValues.hasOwnProperty(propertyId))
					{
						continue;
					}

					this.skuTree.setSelectedProperty(propertyId, selectedValues[propertyId]);
				}
			}
			catch (e) {}

			this.skuTree.toggleSkuProperties();
		}
	},
	mounted()
	{
		this.appendBlockHtml();

		if (this.skuTree)
		{
			this.skuTree.subscribe(EventType.basket.changeSkuOriginName, (event) => {
				EventEmitter.emit(EventType.basket.changeSku, {index: this.index, data: event.getData()})
			});
		}
	},
	// language=Vue
	template: `<div>
	  	<div ref="container"/>
    </div>
	`
});