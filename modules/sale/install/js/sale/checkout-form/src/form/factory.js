import {Type} from 'main.core';
import {EventEmitter} from 'main.core.events';
import Form from './form';
import Model from './model';
import Scheme from './scheme';

export class EntityType
{
	static FORM = 'form';
	static MODEL = 'model';
	static SCHEME = 'scheme';
}

const entities = [
	{
		type: EntityType.FORM,
		entity: Form
	},
	{
		type: EntityType.MODEL,
		entity: Model
	},
	{
		type: EntityType.SCHEME,
		entity: Scheme
	}
];

export default class Factory extends EventEmitter
{
	static createForm(model: Object, scheme: Object, parameters: Object)
	{
		const modelEntity = this.create(EntityType.MODEL, model);
		const schemeEntity = this.create(EntityType.SCHEME, scheme);

		return this.create(EntityType.FORM, modelEntity, schemeEntity, parameters);
	}

	static create(type: string, ...options)
	{
		this.emit('BX.Sale.Checkout.Factory:onBeforeCreate', entities);

		let entity = entities.find((item) => {
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
			return new entity(...options);
		}

		return null;
	}
}