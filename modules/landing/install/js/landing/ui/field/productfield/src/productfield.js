import {BaseField} from 'landing.ui.field.basefield';
import {ProductForm} from 'catalog.product-form';
import {DiscountType} from "catalog.product-calculator";
import {PageObject} from 'landing.pageobject';
import {Dom, Runtime, Type} from 'main.core';
import {BaseEvent} from 'main.core.events';

import './css/style.css';

export class ProductField extends BaseField
{
	constructor(options)
	{
		super(options);
		this.setEventNamespace('BX.Landing.UI.Field.ProductField');
		this.setLayoutClass('landing-ui-field-product');

		this.onBasketChange = this.onBasketChange.bind(this);

		Dom.append(this.getProductSelector().wrapper, this.input);

		this.setProducts(this.options.items);
		const root = PageObject.getRootWindow();
		root.BX.Event.EventEmitter
			.subscribe(this.getProductSelector(), 'ProductForm:onBasketChange', this.onBasketChange);
	}

	setProducts(products)
	{
		this.cache.set('products', Runtime.clone(products));
	}

	getProducts(): Array<any>
	{
		return this.cache.get('products') || [];
	}

	onBasketChange(event: BaseEvent)
	{
		const data = event.getData();
		this.setProducts(data.basket);
		this.emit('onChange', {skipPrepare: true});
	}

	getValue()
	{
		return this.getProducts().reduce((acc, item) => {
			if (!Type.isNil(item.fields.productId))
			{
				acc.push({
					label: item.fields.name,
					changeablePrice: false,
					discount: item.fields.discount,
					pics: [],
					price: item.fields.price,
					quantity: [],
					selected: false,
					value: item.fields.productId,
				});
			}

			return acc;
		}, []);
	}

	getProductSelector(): ProductForm
	{
		return this.cache.remember('productSelector', () => {
			const root = PageObject.getRootWindow();
			return new root.BX.Catalog.ProductForm({
				iblockId: this.options.iblockId,
				showResults: false,
				allowedDiscountTypes: [DiscountType.MONETARY],
				buttonsPosition: 'BOTTOM',
				newItemPosition: 'BOTTOM',
				basket: this.options.items,
			});
		});
	}
}