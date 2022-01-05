import {ajax, Dom, Event, Type} from 'main.core';
import type {BaseBlock, BlockSetting} from '../blocks/base-block';
import BlockFactory, {BlockType} from '../blocks/factory';
import {EventEmitter} from 'main.core.events';
import Scheme from './scheme';

type FormParameters = {
	blocks: { type: string, options: mixed }[],
};

export class Stage
{
	static INITIAL: number = 1;
	static VIEW: number = 2;
}

export default class Form extends EventEmitter
{
	model: Model = null;
	scheme: Scheme = null;
	parameters: FormParameters = null;

	stage: number = null;
	container: HTMLElement = null;
	blocks: BaseBlock[] = null;

	constructor(model: Model, scheme: Scheme, parameters: FormParameters = {})
	{
		super();
		this.setEventNamespace('BX.Sale.CheckoutForm');

		this.model = model;
		this.scheme = scheme;
		this.parameters = parameters;
	}

	setStage(stage: number)
	{
		this.stage = stage;
		return this;
	}

	setModel(fields: Object): void
	{
		this.model.initFields(fields);
	}

	// todo model and fields
	hasField(name: string): boolean
	{
		return this.model.hasField(name);
	}

	getField(name: string, defaultValue: mixed): mixed
	{
		return this.model.getField(name, defaultValue);
	}

	setField(name: string, value: mixed): boolean
	{
		const isChanged = this.setFieldNoDemand(name, value);

		if (isChanged)
		{
			this.requestRefresh();
		}

		return isChanged;
	}

	setFieldNoDemand(name: string, value: mixed): boolean
	{
		return this.model.setField(name, value);
	}

	setScheme(fields: Object): void
	{
		for (let name in fields)
		{
			if (fields.hasOwnProperty(name))
			{
				this.scheme.fields.set(name, fields[name]);
			}
		}
		// todo init when all fields come from api request
		// this.scheme.initFields(fields);
	}

	getSchemeField(name: string, defaultValue: mixed)
	{
		return this.scheme.getField(name, defaultValue);
	}

	getParameter(name: string, defaultValue = null)
	{
		return this.parameters[name] || defaultValue;
	}

	getStage(): number
	{
		return this.stage;
	}

	getContainer(): ?HTMLElement
	{
		return this.container;
	}

	setContainer(container: HTMLElement)
	{
		if (!Type.isDomNode(container))
		{
			throw new Error('Wrong target node to render');
		}

		const oldContainer = this.getContainer();
		if (Type.isDomNode(oldContainer))
		{
			this.clearContainer(oldContainer);
		}

		this.container = container;
	}

	clearContainer(container: HTMLElement)
	{
		Event.unbindAll(container);
		Dom.clean(container);
	}

	buildBlocks(): BaseBlock[]
	{
		let blocks = [];

		this.getParameter('blocks', []).forEach((setting: BlockSetting) => {
			blocks.push(BlockFactory.create(setting.type, this, setting));
		});

		return blocks;
	}

	getBlocks(): BaseBlock[]
	{
		if (this.blocks === null)
		{
			this.blocks = this.buildBlocks();
		}

		return this.blocks;
	}

	refreshLayout(): void
	{
		this.getBlocks().forEach((block) => {
			block.refreshLayout();
		});
	}

	renderTo(target: string | HTMLElement): void
	{
		if (Type.isString(target))
		{
			target = document.getElementById(target);
		}

		this.setContainer(target);
		this.refreshLayout();
	}

	layoutSuccessBlock(): void
	{
		const finalBlock = this.getBlocks().find((block) => {
			return block.isSuccess();
		});

		if (finalBlock)
		{
			finalBlock.refreshLayout(true);
		}
	}

	requestRefresh()
	{
		ajax.runAction(
			'sale.entity.refreshorder',
			{
				data: {
					fields: this.prepareFields()
				}
			}
		)
			.then(this.handleRefreshResponse.bind(this))
		;
	}

	handleRefreshResponse(response): void
	{
		if (response.status === 'success')
		{
			const modelFields = this.extractModelFields(response.data);
			this.setModel(modelFields);

			const schemeFields = this.extractSchemeFields(response.data);
			this.setModel(schemeFields);

			this.refreshLayout();
		}
	}

	getPropertyErrorCollection():Array
	{
		let collection = this.getField('ERROR_COLLECTION', {});
		return collection.hasOwnProperty('PROPERTIES') && Type.isArrayFilled(collection.PROPERTIES)
			? collection.PROPERTIES:[];
	}

	verify()
	{
		return this.verifyProperty();
	}

	verifyProperty()
	{
		let list = [];
		let properties = this.getField('properties');

		this.getSchemeField('properties', []).forEach((item) => {
			if (item.type === 'STRING' && item.required === 'Y')
			{
				//console.log('properties', properties[item.ID]);
				if(Type.isStringFilled(properties[item.id]) === false)
				{
					list.push({
						id: item.id,
						message: ''
					});
				}
			}
		});

		this.setFieldNoDemand('ERROR_COLLECTION', {PROPERTIES: list});

		return Type.isArrayFilled(list) === false;
	}

	requestSave()
	{
		if(this.verify())
		{
			ajax.runAction(
				'sale.entity.saveorder',
				{
					data: {
						fields: this.prepareFields()
					}
				}
			)
				.then(
					this.handleSaveResponse.bind(this),
					(response) => {
						if(response.status === 'error')
						{
							this.fillErrorCollection(response.errors);

							this.getPropertyErrorCollection().forEach((error)=>{

								BX.onCustomEvent("BX.Sale.Checkout.Property.Error:onSave_" + error.id);
							});
						}
					}
				)
		}
		else
		{
			this.getPropertyErrorCollection().forEach((error)=>{
				BX.onCustomEvent("BX.Sale.Checkout.Property.Error:onSave_" + error.id);
			});
		}
	}

	handleSaveResponse(response): void
	{
		if (response.status === 'success')
		{
			const modelFields = this.extractModelFields(response.data);
			this.setModel(modelFields);

			const schemeFields = this.extractSchemeFields(response.data);
			this.setScheme(schemeFields);

			this.layoutSuccessBlock();
			this.stage++;
		}
	}

	fillErrorCollection(errors)
	{
		let list = [];
		if(Type.isArrayFilled(errors))
		{
			errors.forEach((error) => {
				if(error.code === 'properties')
				{
					list.push({
						id: error.customData.id,
						message: error.customData.message
					});
				}
			});
			this.setFieldNoDemand('ERROR_COLLECTION', {PROPERTIES: list});
		}
	}

	prepareFields(): Object
	{
		const fields = {
			'siteId': this.getSchemeField('siteId'),
			'products': this.getField('basketItems'),
			'properties': this.getField('properties')
		};

		const userId = this.getSchemeField('userId');
		if (userId)
		{
			fields['userId'] = userId;
		}

		return fields;
	}

	extractModelFields(data: Object): Object
	{
		const basketItems = {};
		data.basketItems.forEach((item) => {
			basketItems[item.id] = {
				productId: item.productId,
				quantity: item.quantity,
				props: item.props
			};
		});

		const properties = {};
		data.properties.forEach((item) => {
			properties[item.orderPropsId] = item.value;
		});

		const payments = {};
		data.payments.forEach((item) => {
			payments[item.id] = {
				id: item.id,
				sum: item.sum
			};
		});

		return {
			basketItems: basketItems,
			properties: properties,
			payments: payments
		};
	}

	extractSchemeFields(data: Object): Object
	{
		return {
			siteId: data.lid,
			userId: data.userId,
			accountNumber: data.accountNumber,
			orderId: data.id,
			paySystems: data.paySystems,
			//SIGNED_PARAMETERS: data.SIGNED_PARAMETERS,
			hash: data.hash
			// ToDo TOTAL, BASKET_ITEMS, PROPERTIES
		};
	}
}