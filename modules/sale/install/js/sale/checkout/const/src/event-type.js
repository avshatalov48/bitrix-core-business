export const EventType = Object.freeze({
    order:
        {
            success: 'EventType.order.success',
        },
    basket:
        {
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
    consent:
        {
            refused: 'EventType.consent.refused',
            accepted: 'EventType.consent.accepted'
        },
    element:
        {
            buttonCheckout: 'EventType.element.buttonCheckout',
            buttonShipping: 'EventType.element.buttonShipping',
        },
    property:
        {
            validate: 'EventType.property.validate'
        },
    application:
        {
            none: 'EventType.application.status.none',
            wait: 'EventType.application.status..wait',
        },
    paysystem:
        {
            beforeInitList: 'EventType.paysystem.beforeInitList',
            afterInitList: 'EventType.paysystem.afterInitList'
        }
});