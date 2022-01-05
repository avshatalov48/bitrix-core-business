import {Type} from 'main.core';
import {EventEmitter} from 'main.core.events';
import {Basket} from './basket';
import Form from '../form/form';
import {Total} from './total';
import {Loader} from './loader';
import {Payments} from './payments';
import {PlaceOrder} from './place-order';
import {Properties} from './properties';
import {Success} from './success';

export class BlockType
{
	static BASKET = 'basket';
	static LOADER = 'loader';
	static PAYMENTS = 'payments';
	static PLACE_ORDER = 'place-order';
	static PROPERTIES = 'properties';
	static SUCCESS = 'success';
	static TOTAL = 'total';
}

const blocks = [
	{
		type: BlockType.BASKET,
		entity: Basket
	},
	{
		type: BlockType.LOADER,
		entity: Loader
	},
	{
		type: BlockType.PAYMENTS,
		entity: Payments
	},
	{
		type: BlockType.PLACE_ORDER,
		entity: PlaceOrder
	},
	{
		type: BlockType.PROPERTIES,
		entity: Properties
	},
	{
		type: BlockType.SUCCESS,
		entity: Success
	},
	{
		type: BlockType.TOTAL,
		entity: Total
	}
];

export default class Factory extends EventEmitter
{
	static create(type: string, form: Form, options = {})
	{
		this.emit('BX.Sale.Checkout.Factory:onBeforeCreateBlock', blocks);

		let entity = blocks.find((item) => {
			return item.type === type;
		})['entity'];

		if (!entity)
		{
			const eventData = {};
			this.emit('BX.Sale.Checkout.Factory:onCreate', eventData);
			if (eventData[type])
			{
				entity = eventData[type];
			}
		}

		if (Type.isFunction(entity))
		{
			return new entity(form, options);
		}

		return null;
	}
}