import { BaseRestHandler } from "./base";
import { Type, Event } from 'main.core';
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
                Event.EventEmitter.emit(EventType.basket.needRefresh, {})
            }
            
            let needRefresh = this.store.getters['basket/getNeedRefresh'];
            
            this.#setModelBasketForAction(response.data, pool)
                .then(() => resolve());
            
            if(needRefresh === 'Y')
            {
                if(pool.isEmpty())
                {
                    this.#setModelBasketByItem(response.data, pool)
                    Event.EventEmitter.emit(EventType.basket.refreshAfter, {})
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
                    let action = this.#getTypeAction(actions, index);

                    if(Type.isString(action))
                    {
                        if(action === PoolConst.action.quantity)
                        {
                            item = null; //not refresh

                            let exists = this.#hasActionInPool(index, PoolConst.action.quantity, poolList);
                            if(exists === false)
                            {
                                item = this.#findItemById(fields.id, items)
                            }
                        }
                        else if(action === PoolConst.action.restore)
                        {
                            item = this.#findItemById(actions[index].fields.id, items)
                        }
                        else if(action === PoolConst.action.delete)
                        {
                            fields.status = LoaderConst.status.none;
                            this.#changeBasketItem(fields, index)
                                .then(()=> Event.EventEmitter.emit(EventType.basket.removeProduct, {index}));
                        }
                        else // for example: offer
                        {
                            // item = this.#findItemById(fields.id, items)
                        }

                        if(Type.isObject(item))
                        {
                            let fields = this.#prepareBasketItemFields(item);

                            fields.status = LoaderConst.status.none;
                            this.#changeBasketItem(fields, index);
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

    #getTypeAction(actions, index)
    {
        const types = Object.values(PoolConst.action);

        if(actions.hasOwnProperty(index))
        {
            let type = actions[index].type.toString();
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
            product: {
                id: item.catalogProduct.id,
                detailPageUrl: item.detailPageUrl,
                picture: Type.isObject(item.catalogProduct.frontImage) ? item.catalogProduct.frontImage.src:null,
                ratio: item.catalogProduct.ratio,
                availableQuantity: item.catalogProduct.availableQuantity
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
        Event.EventEmitter.emit(EventType.order.success);

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

                if(properties.length > 0)
                {
                    this.store.commit('property/setErrors', properties);

                    this.store.getters['property/getProperty']
                        .forEach((fields, index)=>
                        {
                            if(typeof properties.find(item => item.propertyId === fields.id) !== 'undefined')
                            {
                                fields.validated = PropertyConst.validate.failure
                            }
                            else
                            {
                                if(fields.validated !== PropertyConst.validate.unvalidated)
                                {
                                    fields.validated = PropertyConst.validate.successful
                                }
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
                            if(fields.validated !== PropertyConst.validate.unvalidated)
                            {
                                fields.validated = PropertyConst.validate.successful
                            }
                            this.store.dispatch('property/changeItem', {index, fields});
                        })
                }
            }
        });
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