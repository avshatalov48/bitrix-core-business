import {Tag, Type} from 'main.core';
import SkuProperty from './sku-property';
import './sku-tree.css';
import {EventEmitter} from 'main.core.events';

export class SkuTree extends EventEmitter
{
	selectedValues = {};

	constructor(options)
	{
		super();
		this.setEventNamespace('BX.Catalog.SkuTree');

		this.skuTree = options.skuTree || {};
		this.selectable = (options.selectable !== false);
		this.hideUnselected = (options.hideUnselected === true);

		if (this.hasSku())
		{
			this.selectedValues = this.skuTree.SELECTED_VALUES || {...this.skuTree.OFFERS[0].TREE};
		}
	}

	isSelectable()
	{
		return this.selectable;
	}

	getSelectedValues()
	{
		return this.selectedValues;
	}

	setSelectedProperty(propertyId, propertyValue)
	{
		this.selectedValues[propertyId] = propertyValue;

		const remainingProperties = this.getRemainingProperties(propertyId);
		if (remainingProperties.length)
		{
			for (let remainingPropertyId of remainingProperties)
			{
				let filterProperties = this.getFilterProperties(remainingPropertyId);
				let skuItems = this.filterSku(filterProperties);

				if (skuItems.length)
				{
					let found = false;
					for (let sku of skuItems)
					{
						if (sku.TREE[remainingPropertyId] === this.selectedValues[remainingPropertyId])
						{
							found = true;
						}
					}

					if (!found)
					{
						this.selectedValues[remainingPropertyId] = skuItems[0].TREE[remainingPropertyId];
					}

				}
			}
		}
	}

	getRemainingProperties(propertyId)
	{
		const filter = [];
		let found = false;

		for (let prop of Object.values(this.skuTree.OFFERS_PROP))
		{
			if (prop.ID === propertyId)
			{
				found = true;
			}
			else if (found)
			{
				filter.push(prop.ID);
			}
		}

		return filter;
	}

	hasSku()
	{
		return Type.isArrayFilled(this.skuTree.OFFERS);
	}

	hasSkuProps()
	{
		return Type.isPlainObject(this.skuTree.OFFERS_PROP) && Object.keys(this.skuTree.OFFERS_PROP).length;
	}

	getSelectedSku()
	{
		if (!this.hasSku())
		{
			return null;
		}

		return this.skuTree.OFFERS.filter(item => {
			return JSON.stringify(item.TREE) === JSON.stringify(this.selectedValues);
		})[0];
	}

	getActiveSkuProperties(propertyId)
	{
		const activeSkuProperties = [];
		const filterProperties = this.getFilterProperties(propertyId);

		this.filterSku(filterProperties)
			.forEach(item => {
				if (!activeSkuProperties.includes(item.TREE[propertyId]))
				{
					activeSkuProperties.push(item.TREE[propertyId]);
				}
			})
		;

		return activeSkuProperties;
	}

	getFilterProperties(propertyId)
	{
		const filter = [];

		for (let prop of Object.values(this.skuTree.OFFERS_PROP))
		{
			if (prop.ID === propertyId)
			{
				break;
			}

			filter.push(prop.ID);
		}

		return filter;
	}

	filterSku(filter)
	{
		if (filter.length === 0)
		{
			return this.skuTree.OFFERS;
		}

		const selectedValues = this.getSelectedValues();

		return this.skuTree.OFFERS.filter(sku => {
			for (let prop of filter)
			{
				if (sku.TREE[prop] !== selectedValues[prop])
				{
					return false;
				}
			}

			return true;
		});
	}

	getSelectedSkuProperty(propertyId)
	{
		return this.getSelectedSku()['TREE'][propertyId];
	}

	layout()
	{
		const container = Tag.render`<div class="product-item-scu-wrapper"></div>`;

		this.skuProperties = [];

		if (this.hasSku() && this.hasSkuProps())
		{
			for (let i in this.skuTree.OFFERS_PROP)
			{
				if (this.skuTree.OFFERS_PROP.hasOwnProperty(i))
				{
					let skuProperty = new SkuProperty({
						parent: this,
						property: this.skuTree.OFFERS_PROP[i],
						existingValues: this.skuTree.EXISTING_VALUES[i],
						offers: this.skuTree.OFFERS,
						hideUnselected: this.hideUnselected,
					});
					container.appendChild(skuProperty.layout());
					this.skuProperties.push(skuProperty);
				}
			}
		}

		return container;
	}

	toggleSkuProperties()
	{
		this.skuProperties.forEach(property => property.toggleSkuPropertyValues());
	}
}