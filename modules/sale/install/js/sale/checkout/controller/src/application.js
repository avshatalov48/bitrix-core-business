import {Runtime, Event, ajax} from 'main.core';
import {BasketRestHandler} from 'sale.checkout.provider.rest'
import {
    Application as ApplicationConst,
    RestMethod as RestMethodConst,
    Component as ComponentConst,
    Consent as ConsentConst,
    Loader as LoaderConst,
    EventType
} from 'sale.checkout.const';

import {Timer} from 'sale.checkout.lib';
import {History} from 'sale.checkout.lib';

import {Basket} from "./basket";

export class Application
{
    constructor(option)
    {
        this.init(option)
            .then(() => this.initProvider())
            .then(() => this.iniController())
            .then(() => this.subscribeToEvents())
            .then(() => this.subscribeToStoreChanges())
    }

    init(option)
    {
        this.store = option.store;
        this.timer = new Timer();
        return new Promise((resolve, reject) => resolve());
    }

    initProvider()
    {
        this.provider = BasketRestHandler.create({store: this.store})
        return new Promise((resolve, reject) => resolve());
    }

    iniController()
    {
        this.basket = new Basket().setStore(this.store).setProvider(this.provider);
        return new Promise((resolve, reject) => resolve());
    }

    executeRestAnswer(command, result, extra)
    {
        return this.provider.execute(command, result, extra);
    }

    subscribeToEvents()
    {
        Event.EventEmitter.subscribe(EventType.order.success, (e)=>this.basket.handlerOrderSuccess(e));
        
        // Event.EventEmitter.subscribe(EventType.basket.removeProduct, Runtime.debounce((e)=>this.basket.handlerSuccessRemove(e), 500, this));
        Event.EventEmitter.subscribe(EventType.basket.buttonRemoveProduct, Runtime.debounce((e)=>this.basket.handlerRemove(e), 500, this));

        Event.EventEmitter.subscribe(EventType.basket.buttonPlusProduct, (e) => this.basket.handlerQuantityPlus(e));
        Event.EventEmitter.subscribe(EventType.basket.buttonMinusProduct, (e) => this.basket.handlerQuantityMinus(e));
        Event.EventEmitter.subscribe(EventType.basket.buttonRestoreProduct, Runtime.debounce((e) => this.basket.handlerRestore(e), 500, this));
        Event.EventEmitter.subscribe(EventType.basket.needRefresh, (e) => this.basket.handlerNeedRefreshY(e));
        Event.EventEmitter.subscribe(EventType.basket.refreshAfter, (e) => this.basket.handlerNeedRefreshN(e));
        
        // Event.EventEmitter.subscribe(EventType.property.validate,           (e) => this.handlerValidateProperty(e));

        Event.EventEmitter.subscribe(EventType.consent.refused, () => this.handlerConsentRefused());
        Event.EventEmitter.subscribe(EventType.consent.accepted, () => this.handlerConsentAccepted());

        // Event.EventEmitter.subscribe(EventType.application.none, () => this.handlerApplicationStatusNone());
        // Event.EventEmitter.subscribe(EventType.application.wait, () => this.handlerApplicationStatusWait());

        Event.EventEmitter.subscribe(EventType.element.buttonCheckout, Runtime.debounce(() => this.handlerCheckout(), 1000, this));
        Event.EventEmitter.subscribe(EventType.element.buttonShipping, Runtime.debounce(() => this.handlerShipping(), 1000, this));

        Event.EventEmitter.subscribe(EventType.paysystem.beforeInitList, ()=>this.paySystemSetStatusWait());
        Event.EventEmitter.subscribe(EventType.paysystem.afterInitList, ()=>this.paySystemSetStatusNone());

        return new Promise((resolve, reject) => resolve());
    }

    subscribeToStoreChanges()
    {
        // this.store.subscribe((mutation, state) => {
        //     const { payload, type } = mutation;
        //     if (type === 'basket/setNeedRefresh')
        //     {
        //     	alert('@@');
        //     	this.getData();
        //     }
        // });

        return new Promise((resolve, reject) => resolve());
    }
    
    paySystemSetStatusWait()
    {
        let paySystem = { status: LoaderConst.status.wait};
        return this.store.dispatch('pay-system/setStatus', paySystem);
    }

    paySystemSetStatusNone()
    {
        let paySystem = { status: LoaderConst.status.none};
        return this.store.dispatch('pay-system/setStatus', paySystem);
    }

    appSetStatusWait()
    {
        let app = { status: LoaderConst.status.wait};
        return this.store.dispatch('application/setStatus', app);
    }

    appSetStatusNone()
    {
        let app = { status: LoaderConst.status.none};
        return this.store.dispatch('application/setStatus', app);
    }

    handlerConsentAccepted()
    {
        this.store.dispatch('consent/setStatus', ConsentConst.status.accepted);
    }

    handlerConsentRefused()
    {
        this.store.dispatch('consent/setStatus', ConsentConst.status.refused);
    }

    handlerCheckout()
    {
        BX.onCustomEvent(ConsentConst.validate.submit, []);

        const consent = this.store.getters['consent/get'];
        const consentStatus = this.store.getters['consent/getStatus'];
        const allowed = consent.id > 0 ?  consentStatus === ConsentConst.status.accepted:true;

        if(allowed)
        {
            // this.propertiesValidate();
            // this.propertiesIsValid() ? alert('propsSuccess'):alert('propsError')

            this.appSetStatusWait();

            this.saveOrder()
                .then(() => {
                        this.appSetStatusNone()
                            .then(()=>
                            {
                                let order = this.store.getters['order/getOrder'];

                                if(order.id>0)
                                {
                                    const url = History.pushState(
                                        this.store.getters['application/getPathLocation'],
                                        {
                                            accountNumber: order.accountNumber,
                                            access: order.hash
                                        })

                                    this.store.dispatch('application/setPathLocation', url);
                                }
                            })
                    }
                )
                .catch(() => this.appSetStatusNone())
        }
    }

    handlerShipping()
    {
        this.store.dispatch('application/setStage', {stage: ApplicationConst.stage.view});
        // todo
        delete BX.UserConsent;
    }

    saveOrder()
    {
        const component = ComponentConst.bitrixSaleOrderCheckout;
        const cmd = RestMethodConst.saleEntitySaveOrder;
        return ajax.runComponentAction(
            component,
            cmd,
            {
                data: {
                    fields: {
                        siteId: this.store.getters['application/getSiteId'],
                        personTypeId: this.store.getters['application/getPersonTypeId'],
                        tradingPlatformId: this.store.getters['application/getTradingPlatformId'],
                        properties: this.preparePropertyFields(
                            this.getPropertyList()
                        ),
                    }
                },
                signedParameters: this.store.getters['application/getSignedParameters']
            }
        )
            .then((result) => this.executeRestAnswer(cmd, result))
            .catch((result) => this.executeRestAnswer(cmd, {error: result.errors}));
    }

    getPropertyList()
    {
        const result = [];
        let list = this.store.getters['property/getProperty'];
        try
        {
            for (let key in list)
            {
                if (!list.hasOwnProperty(key))
                {
                    continue;
                }

                result[list[key].id] = list[key];
            }
        }
        catch (e) {}

        return result;
    }

    preparePropertyFields(list)
    {
        let fields = {};
        list.forEach((property, inx)=>
        {
            fields[inx] = property.value
        })
        return fields;
    }
}