import { BaseRestHandler } from "./base";
import { Type } from 'main.core';
import { EventEmitter } from 'main.core.events'
import { EventType } from 'sale.checkout.const';
import {
    Application as ApplicationConst,
    Property as PropertyConst,
    Loader as LoaderConst,
    Pool as PoolConst
} from 'sale.checkout.const';

export class BasketRestHandler extends BaseRestHandler
{
    handleRecalculateBasket(response, pool)
    {
        return new Promise((resolve, reject) =>
        {
            if(response.data.needFullRecalculation === 'Y')
            {
                EventEmitter.emit(EventType.basket.needRefresh, {})
            }

            let needRefresh = this.store.getters['basket/getNeedRefresh'];

            this.#setModelBasketForAction(response.data, pool)
                .then(() => resolve());

            this.#setModelBasketForActionError(response.data)
                .then(() => resolve());

            if(needRefresh === 'Y')
            {
                if(pool.isEmpty())
                {
                    this.#setModelBasketByItem(response.data, pool)
                    EventEmitter.emit(EventType.basket.refreshAfter, {})
                }
            }
        });
    }

    #setModelBasketByItem(data, pool)
    {
        return new Promise((resolve, reject) =>{
            if(Type.isObject(data) && Type.isArray(data.basketItems))
            {
                const items = data.basketItems;
                const collection = this.store.getters['basket/getBasket'];

                //refresh
                collection.forEach((fields, index) =>
                {
                    let item = this.#findItemById(fields.id, items);

                    if(Type.isObject(item))
                    {
                        let fields = this.#prepareBasketItemFields(item);

                        this.#changeBasketItem(fields, index);
                    }
                })

                if(Type.isObject(data) && Type.isObject(data.orderPriceTotal))
                {
                    this.#refreshModelBasketTotal(data);
                    this.#refreshModelBasketDiscount(data);
                }
            }

            resolve();
        });
    }

    #setModelBasketForActionError(data)
    {
        return new Promise((resolve, reject) =>
        {
            if(Type.isObject(data) && Type.isObject(data.actions))
            {
                const actions = data.actions;
                const collection = this.store.getters['basket/getBasket'];

                let list = this.#prepareBasketErrors(collection, actions)

                if(list.length > 0)
                {
                    this.store.commit('basket/setErrors', list);
                }
                else
                {
                    this.store.commit('basket/clearErrors');
                }
            }
            resolve();
        });
    }

    #setModelBasketForAction(data, pool)
    {
        return new Promise((resolve, reject) =>
        {
            if(Type.isObject(data) && Type.isArray(data.basketItems))
            {
                const items = data.basketItems;
                const actions = data.actions;
                const collection = this.store.getters['basket/getBasket'];
                const poolList = pool.get();

                collection.forEach((fields, index) =>
                {
                    let item;
                    let typeAction = this.#getTypeAction(actions, index);

                    if(Type.isString(typeAction))
                    {
                        if(typeAction === PoolConst.action.quantity)
                        {
                            item = null; //not refresh

                            let exists = this.#hasActionInPool(index, PoolConst.action.quantity, poolList);
                            if(exists === false)
                            {
                                item = this.#findItemById(fields.id, items)
                            }
                        }
                        else if(typeAction === PoolConst.action.restore)
                        {
                            item = this.#findItemById(actions[index].fields.id, items)
                        }
                        else if(typeAction === PoolConst.action.delete)
                        {
                            fields.status = LoaderConst.status.none;
                            this.#changeBasketItem(fields, index)
                                .then(()=> EventEmitter.emit(EventType.basket.removeProduct, {index}));
                        }
                        else if(typeAction === PoolConst.action.offer)
                        {
                            item = null; //not refresh

                            let exists = this.#hasActionInPool(index, PoolConst.action.offer, poolList);
                            if(exists === false)
                            {
                                item = this.#findItemById(fields.id, items)
                            }
                        }

                        if(Type.isObject(item))
                        {
                            let fields = this.#prepareBasketItemFields(item);

                            fields.status = LoaderConst.status.none;
                            this.#changeBasketItem(fields, index)
                                .then(() =>
                                    {
                                        if(typeAction === PoolConst.action.restore)
                                        {
                                            EventEmitter.emit(EventType.basket.restoreProduct, {index})
                                        }
                                    }
                                );
                        }
                    }
                });

                if(Type.isObject(data) && Type.isObject(data.orderPriceTotal))
                {
                    this.#refreshModelBasketTotal(data);
                    this.#refreshModelBasketDiscount(data);
                }
            }

            resolve();
        });
    }

    #hasErrorAction(action)
    {
        return action.hasOwnProperty('errors')
    }

    #getAction(actions, index)
    {
        return actions.hasOwnProperty(index) ? actions[index] : null
    }

    #getErrorsAction(actions, index)
    {
        let action = this.#getAction(actions, index)

        if(action !== null)
        {
            return action.hasOwnProperty('errors') ? action.errors : null
        }
        else
        {
            return null
        }
    }

    #getTypeAction(actions, index)
    {
        const types = Object.values(PoolConst.action);
        let action = this.#getAction(actions, index)

        if(action !== null)
        {
            let type = action.type.toString();
            return types.includes(type) ? type : null;
        }
        return null;
    }

    #hasActionInPool(index, type, poolList)
    {
        let item = poolList.hasOwnProperty(index) ? poolList[index]:null;
        if(Type.isArray(item))
        {
            return this.#hasActionInPoolItem(item, type);
        }
        return false;
    }

    #hasActionInPoolItem(item, type)
    {
        return item.some((item)=>item.hasOwnProperty(type))
    }

    #findItemById(id, items)
    {
        id = parseInt(id);

        for (let index in items)
        {
            if (!items.hasOwnProperty(index))
            {
                continue;
            }

            items[index].id = parseInt(items[index].id);

            if(items[index].id === id)
            {
                return items[index];
            }
        }

        return null
    }

    #changeBasketItem(fields, index)
    {
        return this.store.dispatch('basket/changeItem', {index, fields});
    }

    #prepareBasketItemFields(item)
    {
        return {
            id: item.id,
            name: item.name,
            quantity: item.quantity,
            measureText: item.measureText,
            sum: item.sum,
            price: item.price,
            module: item.module,
            productProviderClass: item.productProviderClass,
            baseSum: item.sumBase,
            basePrice: item.basePrice,
            currency: item.currency,
            discount: {
                sum: item.sumDiscountDiff,
                price: item.discountPrice
            },
            props: item.props,
            sku: item.sku,
            product: {
                id: item.catalogProduct.id,
                detailPageUrl: item.detailPageUrl,
                picture: Type.isObject(item.catalogProduct.frontImage) ? item.catalogProduct.frontImage.src:null,
                ratio: item.catalogProduct.ratio,
                availableQuantity: item.catalogProduct.availableQuantity,
                type: item.catalogProduct.type,
				checkMaxQuantity: item.catalogProduct.checkMaxQuantity,
            }
        };
    }

    #refreshModelBasketTotal(data)
    {
        let total = data.orderPriceTotal;

        this.store.dispatch('basket/setTotal', {
            price : total.orderPrice,
            basePrice : total.priceWithoutDiscountValue,
        });
    }

    #refreshModelBasketDiscount(data)
    {
        let total = data.orderPriceTotal;

        this.store.dispatch('basket/setDiscount', {
            sum: total.basketPriceDiscountDiffValue
        });
    }

    handleSaveOrderSuccess(data)
    {
        EventEmitter.emit(EventType.order.success);

        this.store.dispatch('application/setStage', {stage: ApplicationConst.stage.success});
        this.store.dispatch('order/set', {
            id: data.order.id,
            hash: data.hash,
            accountNumber: data.order.accountNumber
        });

        return this.#refreshModelBasket(data)
            .then(()=>this.#refreshModelProperty(data))
    }

    #refreshModelProperty(data)
    {
        this.store.commit('property/clearProperty');

        if(Type.isObject(data) && Type.isArray(data.properties))
        {
            data.properties.forEach((item, index) => {
                let fields = {
                    id : item.id,
                    name : item.name,
                    type : item.type,
                    value : item.value[0],//TODO
                };

                this.store.dispatch('property/changeItem', {index, fields});
            });
        }
    }

    #refreshModelBasket(data)
    {
        return new Promise((resolve, reject) => {

            this.store.commit('basket/clearBasket');

            if(Type.isObject(data) && Type.isArray(data.basketItems))
            {
                const items = data.basketItems;
                items.forEach((item, index) => {
                    let fields = this.#prepareBasketItemFields(item);
                    this.#changeBasketItem(fields, index)});
            }

            if(Type.isObject(data) && Type.isObject(data.orderPriceTotal))
            {
                this.#refreshModelBasketTotal(data)
                this.#refreshModelBasketDiscount(data)
            }

            resolve();
        });
    }

	setModelPropertyError(properties)
	{
		if (Type.isArrayFilled(properties))
		{
			this.store.commit('property/setErrors', properties);

			this.store.getters['property/getProperty']
			.forEach((fields, index)=>
			{
				if (typeof properties.find(item => item.propertyId === fields.id) !== 'undefined')
				{
					fields.validated = PropertyConst.validate.failure;
				}
				else
				{
					fields.validated = PropertyConst.validate.unvalidated;
				}
				this.store.dispatch('property/changeItem', {index, fields});
			})
		}
		else
		{
			this.store.commit('property/clearErrors');

			this.store.getters['property/getProperty']
			.forEach((fields, index)=>
			{
				fields.validated = PropertyConst.validate.unvalidated;
				this.store.dispatch('property/changeItem', {index, fields});
			})
		}
	}

    handleSaveOrderError(errors)
    {
        return new Promise((resolve, reject) => {
            if (Type.isArrayFilled(errors))
            {
                let general = this.#prepareGeneralErrors(errors)
                let properties = this.#preparePropertyErrors(errors);

                if(general.length > 0)
                {
                    this.store.commit('application/setErrors', general);
                }
                else
                {
                    this.store.commit('application/clearErrors');
                }

				this.setModelPropertyError(properties);
            }
        });
    }

    #prepareBasketErrors(collection, actions)
    {
        const result = [];
        collection.forEach((fields, index) =>
        {
            let list = this.#getErrorsAction(actions, index);
            if(list !== null)
            {
                result.push({list, index});
            }
        })

        return result
    }

    #preparePropertyErrors(errors)
    {
        const result = [];

        errors.forEach((fields)=>{
            if(fields.code === 'PROPERTIES')
            {
                if (fields.hasOwnProperty('customData'))
                {
                    let id = parseInt(fields.customData.id)
                    result.push({message: fields.message, propertyId: id});
                }
            }
        })
        return result;
    }

    #prepareGeneralErrors(errors)
    {
        const result = [];

        errors.forEach((fields)=>{
            if(parseInt(fields.code) === 0 || fields.code === 'ORDER')
            {
                result.push({message: fields.message})
            }
        })
        return result;
    }
}