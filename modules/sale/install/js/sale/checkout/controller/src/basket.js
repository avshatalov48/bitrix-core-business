import { ajax } from 'main.core';
import { Pool, Timer, Basket as Lib } from 'sale.checkout.lib'
import {
    Application as ApplicationConst,
    RestMethod as RestMethodConst,
    Component as ComponentConst,
    Loader as LoaderConst,
    Pool as PoolConst,
} from 'sale.checkout.const';

export class Basket
{
    constructor()
    {
        this.pool = this.getPool();
        this.timer = this.getTimer();

        this.running = 'N';
    }

    /**
     * @private
     */
    getPool()
    {
        return new Pool();
    }

    /**
     * @private
     */
    getTimer()
    {
        return new Timer();
    }

    /**
     * @private
     */
    isRunning()
    {
        return this.running === 'Y';
    }

    /**
     * @private
     */
    setRunningY()
    {
        this.running = 'Y'
    }

    /**
     * @private
     */
    setRunningN()
    {
        this.running = 'N'
    }

    /**
     * @private
     */
    setStore(store)
    {
        this.store = store;
        return this;
    }

    /**
     * @private
     */
    setProvider(provider)
    {
        this.provider = provider;
        return this;
    }

    /**
     * @private
     */
    executeRestAnswer(command, result, extra)
    {
        return this.provider.execute(command, result, extra);
    }

    /**
     * @private
     */
    getItem(index)
    {
        return this.store.getters['basket/get'](index);
    }

    /**
     * @private
     */
    getBasket()
    {
        return this.store.getters['basket/getBasket'];
    }

	/**
	 * @private
	 */
	getBasketCollection()
	{
		return this.getBasket().filter(item => item.deleted === 'N');
	}

    /**
     * @private
     */
    changeItem(product)
    {
        this.store.dispatch('basket/changeItem', {
            index: product.index,
            fields: product.fields
        });
    }

    /**
     * @private
     */
    setQuantity(index, quantity)
    {
        let fields = this.getItem(index);

        fields.quantity = quantity;
        fields.baseSum = this.round(fields.basePrice * fields.quantity);
        fields.sum = this.round(fields.price * fields.quantity);
        fields.discount.sum = this.round(fields.discount.price * fields.quantity);

        this.refreshDiscount();
        this.refreshTotal();

        this.pool.add(PoolConst.action.quantity, index, {id: fields.id, value: fields.quantity});
        this.changeItem({index, fields});
        this.shelveCommit();
    }

    refreshDiscount()
    {
        let basket = this.getBasket();
        if(basket.length > 0)
        {
            this.store.dispatch('basket/setDiscount', {
                sum: basket.reduce((result, value) => result + value.discount.sum, 0),
            });
        }
    }

    refreshTotal()
    {
        let basket = this.getBasketCollection();
        if(basket.length > 0)
        {
            this.store.dispatch('basket/setTotal', {
                price: basket.reduce((result, value) => result + value.sum, 0),
                basePrice: basket.reduce((result, value) => result + value.baseSum, 0)
            });
        }
    }

    /**
     * @private
     */
    removeItem(product)
    {
        return this.store.dispatch('basket/removeItem', {
            index: product.index
        });
    }

    /**
     * @private
     */
    round(value, precision = 10)
    {
        const factor = Math.pow(10, precision);

        return Math.round(value * factor) / factor;
    }

    emitOnBasketChange()
    {
        BX.onCustomEvent('OnBasketChange');
    }

    /**
     * @private
     */
    handlerOrderSuccess()
    {
        this.emitOnBasketChange()
    }

    /**
     * @private
     */
    handlerRemoveProductSuccess()
    {
        this.emitOnBasketChange()
    }

    /**
     * @private
     */
    handlerRestoreProductSuccess()
    {
        this.emitOnBasketChange()
    }

    /**
     * @private
     */
    handlerRemove(event)
    {
        let index = event.getData().index;
        let fields = this.getItem(index);

        fields.deleted = 'Y';
        fields.status = LoaderConst.status.wait;

        this.pool.add(PoolConst.action.delete, index, {id: fields.id, fields: {value: 'Y'}});
        this.changeItem({index, fields});
        this.shelveCommit();
    }

    /**
     * @private
     */
    handlerSuccessRemove(event)
    {
        let index = event.getData().index;

        this.timer.create(5000, index + '_DELETE', () =>
            this.removeItem({index})
                .then(() =>
                {
                    if(this.getBasket().length === 0)
                    {
                        this.store.dispatch('application/setStage', {stage: ApplicationConst.stage.empty})
                    }
                })
        )
    }

    /**
     * @private
     */
    handlerRestore(event)
    {
        let index = event.getData().index;
        let fields = this.getItem(index);

        this.timer.clean({
            index: index + '_DELETE'
        });

        fields.deleted = 'N';
        fields.status = LoaderConst.status.wait;

        //todo: send all fields ?
        this.pool.add(PoolConst.action.restore, index, {
            basePrice:fields.basePrice,
            baseSum:fields.baseSum,
            currency:fields.currency,
            discount:fields.discount,
            id:fields.id,
            measureText:fields.measureText,
            module:fields.module,
            name:fields.name,
            price:fields.price,
            product:fields.product,
            productProviderClass:fields.productProviderClass,
            props:fields.props,
            quantity:fields.quantity,
            sum:fields.sum,
        });
        this.changeItem({index, fields});
        this.shelveCommit();
    }

    /**
     * @private
     */
    handlerChangeQuantity(event)
    {
        // let data = event.getData().data;
        let index = event.getData().index;
        let fields = this.getItem(index);

        let quantity = fields.quantity;
        let ratio = fields.product.ratio;
        let available = fields.product.availableQuantity;

        quantity = Lib.roundValue(quantity)
        ratio = Lib.roundValue(ratio)

        quantity = isNaN(quantity) ? 0:quantity

        if (ratio > 0 && quantity < ratio)
        {
            quantity = ratio;
        }

        if (available > 0 && quantity > available)
        {
            quantity = available;
        }

        quantity = Lib.toFixed(quantity, ratio, available)

        if(fields.quantity !== quantity)
        {
            this.setQuantity(index, quantity)
        }
    }

    /**
     * @private
     */
    handlerQuantityPlus(event)
    {
        let index = event.getData().index;
        let fields = this.getItem(index);
        let quantity = fields.quantity;
        let ratio = fields.product.ratio;
        let available = fields.product.availableQuantity;

        quantity = Lib.roundValue(quantity)
        ratio = Lib.roundValue(ratio)

        quantity = quantity + ratio;

        if(Lib.isValueFloat(quantity))
        {
            quantity = Lib.roundFloatValue(quantity)
        }

        if (available > 0 && quantity > available)
        {
            quantity = available;
        }

        quantity = Lib.toFixed(quantity, ratio, available)

        if(fields.quantity < quantity)
        {
            this.setQuantity(index, quantity)
        }
    }

    /**
     * @private
     */
    handlerQuantityMinus(event)
    {
        let index = event.getData().index;
        let fields = this.getItem(index);
        let quantity = fields.quantity;
        let ratio = fields.product.ratio;
        let available = fields.product.availableQuantity;

        quantity = Lib.roundValue(quantity)
        ratio = Lib.roundValue(ratio)

        let delta = quantity = quantity - ratio;

        if(Lib.isValueFloat(quantity))
        {
            quantity = Lib.roundFloatValue(quantity)
			delta = Lib.roundFloatValue(delta)
        }

        if (ratio > 0 && quantity < ratio)
        {
            quantity = ratio;
        }

        if (available > 0 && quantity > available)
        {
            quantity = available;
        }

        quantity = Lib.toFixed(quantity, ratio, available)

        if(delta >= ratio)
        {
            this.setQuantity(index, quantity)
        }
    }

    /**
     * @private
     */
    commit()
    {
        return new Promise((resolve, reject) =>
        {
            let fields = {};

            if(this.pool.isEmpty() === false)
            {
                fields = this.pool.get();
                this.pool.clean();

                const component = ComponentConst.bitrixSaleOrderCheckout;
                const cmd = RestMethodConst.saleEntityRecalculateBasket;

                ajax.runComponentAction(
                    component,
                    cmd,
                    {
                        data: {
                            actions: fields
                        },
                        signedParameters: this.store.getters['application/getSignedParameters']
                    }
                )
                .then((result) => this.executeRestAnswer(cmd, result, this.pool)
                    .then(() => this.commit()
                        .then(() => resolve())))
                .catch()
            }
            else
            {
                resolve();
            }
        });
    }

    /**
     * @private
     */
    shelveCommit(index = 'BASKET')
    {
        if(this.isRunning() === false)
        {
            this.timer.create(300, index,
                () => {
                    this.setRunningY();
                    this.commit()
                        .then(()=>this.setRunningN())
                }
            );
        }
    }

    /**
     * @private
     */
    getStatus()
    {
        return this.store.getters['basket/getStatus'];
    }

    /**
     * @private
     */
    setStatusWait()
    {
        let app = {status: LoaderConst.status.wait};
        return this.store.dispatch('basket/setStatus', app);
    }

    /**
     * @private
     */
    setStatusNone()
    {
        let app = {status: LoaderConst.status.none};
        return this.store.dispatch('basket/setStatus', app);
    }

    /**
     * @private
     */
    handlerNeedRefreshY()
    {
        this.setNeedRefreshY();
        this.setStatusWait();
    }

    /**
     * @private
     */
    handlerNeedRefreshN()
    {
        this.setNeedRefreshN();
        this.setStatusNone();
    }

    /**
     * @private
     */
    setNeedRefreshY()
    {
        let app = {needRefresh: 'Y'};
        return this.store.dispatch('basket/setNeedRefresh', app);
    }

    /**
     * @private
     */
    setNeedRefreshN()
    {
        let app = {needRefresh: 'N'};
        return this.store.dispatch('basket/setNeedRefresh', app);
    }

    /**
     * @private
     */
    handlerChangeSku(event)
    {
        let offerId = event.getData().data[0].ID;

        let index = event.getData().index;
        let fields = this.getItem(index);

        fields.status = LoaderConst.status.wait;

        this.pool.add(PoolConst.action.offer, index, {id: fields.id, fields: {offerId: offerId}});
        this.changeItem({index, fields});
        this.shelveCommit();
    }
}
