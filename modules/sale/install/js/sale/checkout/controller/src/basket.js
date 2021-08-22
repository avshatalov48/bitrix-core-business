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
        this.pool = new Pool();
        this.timer = new Timer();
        this.running = 'N';
    }
    
    isRunning()
    {
        return this.running === 'Y';
    }
    
    setRunningY()
    {
        this.running = 'Y'
    }
    
    setRunningN()
    {
        this.running = 'N'
    }

    setStore(store)
    {
        this.store = store;
        return this;
    }

    setProvider(provider)
    {
        this.provider = provider;
        return this;
    }

    executeRestAnswer(command, result, extra)
    {
        return this.provider.execute(command, result, extra);
    }

    getItem(index)
    {
        return this.store.getters['basket/get'](index);
    }

    getBasket()
    {
        return this.store.getters['basket/getBasket'];
    }

    changeItem(product)
    {
        this.store.dispatch('basket/changeItem', {
            index: product.index,
            fields: product.fields
        });
    }

    setQuantity(index, quantity)
    {
        let fields = this.getItem(index);

        fields.quantity = quantity;
        fields.baseSum = this.round(fields.basePrice * fields.quantity);
        fields.sum = this.round(fields.price * fields.quantity);
        fields.discount.sum = this.round(fields.discount.price * fields.quantity);

        this.pool.add(PoolConst.action.quantity, index, {id: fields.id, value: fields.quantity});
        this.changeItem({index, fields});
        this.shelveCommit();
    }

    removeItem(product)
    {
        return this.store.dispatch('basket/removeItem', {
            index: product.index
        });
    }

    round(value, precision = 10)
    {
        const factor = Math.pow(10, precision);

        return Math.round(value * factor) / factor;
    }
    
    handlerOrderSuccess()
    {
        BX.onCustomEvent('OnBasketChange');
    }

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

        this.pool.add(PoolConst.action.restore, index, fields);
        this.changeItem({index, fields});
        this.shelveCommit();
    }
    
    handlerQuantityPlus(event)
    {
        let index = event.getData().index;
        let fields = this.getItem(index);
        let quantity = fields.quantity;
        let ratio = fields.product.ratio;
        let available = fields.product.availableQuantity;

        quantity = quantity + ratio;
    
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

    handlerQuantityMinus(event)
    {
        let index = event.getData().index;
        let fields = this.getItem(index);
        let quantity = fields.quantity;
        let ratio = fields.product.ratio;
        let available = fields.product.availableQuantity;
    
        quantity = quantity - ratio;
    
        if (ratio > 0 && quantity < ratio)
        {
            quantity = ratio;
        }
        
        if (available > 0 && quantity > available)
        {
            quantity = available;
        }
    
        quantity = Lib.toFixed(quantity, ratio, available)
    
        if(quantity >= ratio)
        {
            this.setQuantity(index, quantity)
        }
    }

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

    getStatus()
    {
        return this.store.getters['basket/getStatus'];
    }

    setStatusWait()
    {
        let app = {status: LoaderConst.status.wait};
        return this.store.dispatch('basket/setStatus', app);
    }

    setStatusNone()
    {
        let app = {status: LoaderConst.status.none};
        return this.store.dispatch('basket/setStatus', app);
    }
    
    handlerNeedRefreshY()
    {
        this.setNeedRefreshY();
        this.setStatusWait();
    }
    
    handlerNeedRefreshN()
    {
        this.setNeedRefreshN();
        this.setStatusNone();
    }
    
    setNeedRefreshY()
    {
        let app = {needRefresh: 'Y'};
        return this.store.dispatch('basket/setNeedRefresh', app);
    }
    
    setNeedRefreshN()
    {
        let app = {needRefresh: 'N'};
        return this.store.dispatch('basket/setNeedRefresh', app);
    }
}