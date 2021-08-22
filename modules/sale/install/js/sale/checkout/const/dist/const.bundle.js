this.BX = this.BX || {};
this.BX.Sale = this.BX.Sale || {};
this.BX.Sale.Checkout = this.BX.Sale.Checkout || {};
(function (exports) {
    'use strict';

    var EventType = Object.freeze({
      order: {
        success: 'EventType.order.success'
      },
      basket: {
        buttonRemoveProduct: 'EventType.basket.buttonRemoveProduct',
        buttonPlusProduct: 'EventType.basket.buttonPlusProduct',
        buttonMinusProduct: 'EventType.basket.buttonMinusProduct',
        buttonRestoreProduct: 'EventType.basket.buttonRestoreProduct',
        removeProduct: 'EventType.basket.removeProduct',
        backdropClose: 'EventType.basket.backdropClose',
        backdropOpen: 'EventType.basket.backdropOpen',
        backdropTotalClose: 'EventType.basket.backdropTotalClose',
        backdropTotalOpen: 'EventType.basket.backdropTotalOpen',
        needRefresh: 'EventType.basket.needRefresh',
        refreshAfter: 'EventType.basket.refreshAfter'
      },
      consent: {
        refused: 'EventType.consent.refused',
        accepted: 'EventType.consent.accepted'
      },
      element: {
        buttonCheckout: 'EventType.element.buttonCheckout',
        buttonShipping: 'EventType.element.buttonShipping'
      },
      property: {
        validate: 'EventType.property.validate'
      },
      application: {
        none: 'EventType.application.status.none',
        wait: 'EventType.application.status..wait'
      },
      paysystem: {
        beforeInitList: 'EventType.paysystem.beforeInitList',
        afterInitList: 'EventType.paysystem.afterInitList'
      }
    });

    var RestMethod = Object.freeze({
      saleEntityAddBasketItem: 'addBasketItem',
      saleEntityUpdateBasketItem: 'updateBasketItem',
      saleEntityDeleteBasketItem: 'deleteBasketItem',
      saleEntityRecalculateBasket: 'recalculateBasket',
      saleEntityGetBasket: 'getBasket',
      saleEntityUserConsentRequest: 'userconsentrequest',
      saleEntitySaveOrder: 'saveOrder',
      saleEntityPaymentPay: 'paymentpay'
    });

    var Application = Object.freeze({
      stage: {
        view: 'Application.stage.view',
        edit: 'Application.stage.edit',
        payed: 'Application.stage.payed',
        empty: 'Application.stage.empty',
        success: 'Application.stage.success',
        undefined: 'Application.stage.undefined'
      },
      mode: {
        view: 'Application.mode.view',
        edit: 'Application.mode.edit'
      }
    });

    var PaySystem = Object.freeze({
      type: {
        cash: 'CASH',
        cashLess: 'CASH_LESS',
        cardTransaction: 'CARD_TRANSACTION',
        undefined: 'UNDEFINED'
      }
    });

    var Component = Object.freeze({
      bitrixSaleOrderCheckout: 'bitrix:sale.order.checkout'
    });

    var Property = Object.freeze({
      validate: {
        failure: 'Property.Validate.failure',
        successful: 'Property.Validate.successful',
        unvalidated: 'Property.Validate.unvalidated'
      },
      type: {
        name: 'NAME',
        email: 'EMAIL',
        phone: 'PHONE',
        string: 'STRING',
        undefined: 'UNDEFINED'
      }
    });

    var Consent = Object.freeze({
      status: {
        init: 'Consent.status.init',
        refused: 'Consent.status.refused',
        accepted: 'Consent.status.accepted'
      },
      validate: {
        submit: 'Consent.validate.submit'
      }
    });

    var Loader = Object.freeze({
      status: {
        none: 'Loader.status.none',
        wait: 'Loader.status.wait'
      }
    });

    var Pool = Object.freeze({
      action: {
        offer: 'offer',
        delete: 'delete',
        restore: 'restore',
        quantity: 'quantity'
      }
    });

    exports.EventType = EventType;
    exports.RestMethod = RestMethod;
    exports.Application = Application;
    exports.PaySystem = PaySystem;
    exports.Component = Component;
    exports.Property = Property;
    exports.Consent = Consent;
    exports.Loader = Loader;
    exports.Pool = Pool;

}((this.BX.Sale.Checkout.Const = this.BX.Sale.Checkout.Const || {})));
//# sourceMappingURL=const.bundle.js.map
