import {Tag, Type, Text, Dom, ajax} from 'main.core';
import 'ui.design-tokens';
import './sku-tree.css';
import SkuProperty from './sku-property';
import {EventEmitter} from 'main.core.events';
import 'ui.forms';
import 'ui.buttons';

const iblockSkuProperties = new Map();
const iblockSkuList = new Map();
const propertyPromises = new Map();

export class SkuTree extends EventEmitter
{
	selectedValues = {};

	static DEFAULT_IBLOCK_ID = 0;

	constructor(options)
	{
		super();
		this.setEventNamespace('BX.Catalog.SkuTree');

		this.id = Text.getRandom();
		this.skuTree = options.skuTree || {};

		this.productId = this.skuTree?.PRODUCT_ID;

		this.skuTreeOffers = this.skuTree.OFFERS || [];

		if (!Type.isNil(options.skuTree.OFFERS_JSON) && !Type.isArrayFilled(this.skuTreeOffers))
		{
			this.skuTreeOffers = JSON.parse(this.skuTree.OFFERS_JSON);
		}

		this.iblockId = this.skuTree.IBLOCK_ID || SkuTree.DEFAULT_IBLOCK_ID;
		if (!iblockSkuProperties.has(this.iblockId))
		{
			if (Type.isObject(this.skuTree.OFFERS_PROP))
			{
				iblockSkuProperties.set(this.iblockId, this.skuTree.OFFERS_PROP);
			}
			else
			{
				iblockSkuProperties.set(this.iblockId, {});
				const promise = new Promise((resolve) => {
					ajax
						.runAction(	'catalog.skuTree.getIblockProperties', {
							json:{
								iblockId: this.iblockId
							}
						})
						.then((result) => {
							iblockSkuProperties.set(this.iblockId, result.data);
							resolve();
							propertyPromises.delete(SkuTree.#getIblockPropertiesRequestName(this.iblockId));
						});
				});

				propertyPromises.set(SkuTree.#getIblockPropertiesRequestName(this.iblockId), promise);
			}
		}

		this.selectable = (options.selectable !== false);
		this.isShortView = (options.isShortView === true);
		this.hideUnselected = (options.hideUnselected === true);

		if (this.hasSku())
		{
			this.selectedValues = this.skuTree.SELECTED_VALUES || {...this.skuTreeOffers[0].TREE};
		}

		this.existingValues = this.skuTree.EXISTING_VALUES || {};
		if (!Type.isNil(options.skuTree.EXISTING_VALUES_JSON) && Type.isNil(options.skuTree.EXISTING_VALUES))
		{
			this.existingValues = JSON.parse(options.skuTree.EXISTING_VALUES_JSON);
		}

		for (const key in this.existingValues)
		{
			if (this.existingValues[key].length === 1 && this.existingValues[key][0] === 0)
			{
				delete this.existingValues[key];
			}
		}
	}

	static #getIblockPropertiesRequestName(iblockId: number): string
	{
		return 'IblockPropertiesRequest_' + iblockId;
	}

	getProperties(): {}
	{
		return iblockSkuProperties.get(this.iblockId);
	}

	isSelectable(): boolean
	{
		return this.selectable;
	}

	getSelectedValues(): {}
	{
		return this.selectedValues;
	}

	setSelectedProperty(propertyId, propertyValue)
	{
		this.selectedValues[propertyId] = Text.toNumber(propertyValue);

		const remainingProperties = this.getRemainingProperties(propertyId);
		if (remainingProperties.length)
		{
			for (const remainingPropertyId of remainingProperties)
			{
				const filterProperties = this.getFilterProperties(remainingPropertyId);
				const skuItems = this.filterSku(filterProperties);

				if (skuItems.length)
				{
					let found = false;
					for (const sku of skuItems)
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

	getRemainingProperties(propertyId): []
	{
		const filter = [];
		let found = false;

		for (const prop of Object.values(this.getProperties()))
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

	hasSku(): boolean
	{
		return Type.isArrayFilled(this.skuTreeOffers);
	}

	hasSkuProps(): boolean
	{
		return Object.values(this.getProperties()).length > 0;
	}

	getSelectedSkuId(): ?number
	{
		if (!this.hasSku())
		{
			return;
		}

		const item = this.skuTreeOffers.filter(item => {
			return JSON.stringify(item.TREE) === JSON.stringify(this.selectedValues);
		})[0]

		return item?.ID;
	}

	getSelectedSku(): Promise
	{
		return new Promise((resolve, reject) => {
			const skuId = this.getSelectedSkuId();

			if (skuId <= 0)
			{
				reject();
				return;
			}

			if (iblockSkuList.has(skuId))
			{
				const skuData = iblockSkuList.get(skuId);
				resolve(skuData);
			}
			else
			{
				if (propertyPromises.has(SkuTree.#getSkuRequestName(skuId)))
				{
					propertyPromises
						.get(SkuTree.#getSkuRequestName(skuId))
						.then((skuFields) => {
							resolve(skuFields);
						});
				}
				else
				{
					const skuRequest = ajax
						.runAction(	'catalog.skuTree.getSku', {
							json: {	skuId }
						})
						.then((result) => {
							const skuData = result.data;
							iblockSkuList.set(skuId, skuData);
							resolve(skuData);

							propertyPromises.delete(SkuTree.#getSkuRequestName(skuId), skuRequest);
						});

					propertyPromises.set(SkuTree.#getSkuRequestName(skuId), skuRequest)
				}
			}
		});
	}

	static #getSkuRequestName(skuId: number): string
	{
		return 'SkuFieldsRequest_' + skuId;
	}

	getActiveSkuProperties(propertyId): {}
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

	getFilterProperties(propertyId): []
	{
		const filter = [];

		for (const prop of Object.values(this.getProperties()))
		{
			if (prop.ID === propertyId)
			{
				break;
			}

			filter.push(prop.ID);
		}

		return filter;
	}

	filterSku(filter): []
	{
		if (filter.length === 0)
		{
			return this.skuTreeOffers;
		}

		const selectedValues = this.getSelectedValues();

		return this.skuTreeOffers.filter(sku => {
			for (const propertyId of filter)
			{
				if (sku.TREE[propertyId] !== selectedValues[propertyId])
				{
					return false;
				}
			}

			return true;
		});
	}

	getSelectedSkuProperty(propertyId)
	{
		return Text.toNumber(this.selectedValues[propertyId]);
	}

	layout(): HTMLElement
	{
		const container = Tag.render`<div class="product-item-scu-wrapper" id="${this.id}"></div>`;

		if (this.isShortView)
		{
			Dom.addClass(container, '--short-format');
		}

		this.skuProperties = [];
		if (this.hasSku())
		{
			new Promise(
				(resolve) => {
					if (propertyPromises.has(SkuTree.#getIblockPropertiesRequestName(this.iblockId)))
					{
						propertyPromises
							.get(SkuTree.#getIblockPropertiesRequestName(this.iblockId))
							.then(resolve);
					}
					else
					{
						resolve();
					}
				})
				.then(() => {
					if (!this.hasSkuProps())
					{
						return;
					}

					const skuProperties = this.getProperties();
					for (const i in skuProperties)
					{
						if (skuProperties.hasOwnProperty(i) && !Type.isNil(this.existingValues[i]))
						{
							const skuProperty = new SkuProperty({
								parent: this,
								property: skuProperties[i],
								existingValues:
									Type.isArray(this.existingValues[i])
										? this.existingValues[i]
										:	Object.values(this.existingValues[i])
								,
								offers: this.skuTreeOffers,
								hideUnselected: this.hideUnselected,
							});

							Dom.append(skuProperty.layout(), container);
							this.skuProperties.push(skuProperty);
						}
					}
					EventEmitter.emit('BX.Catalog.SkuTree::onSkuLoaded', { id: this.id });
				});
		}

		return container;
	}

	toggleSkuProperties()
	{
		this.skuProperties.forEach(property => property.toggleSkuPropertyValues());
	}
}
