export const EventType = Object.freeze({
    order:
        {
            success: 'BX:Sale:Checkout:EventType:order:success',
        },
    basket:
        {
            inputChangeQuantityProduct: 'BX:Sale:Checkout:EventType:basket:inputChangeQuantityProduct',
            buttonRemoveProduct: 'BX:Sale:Checkout:EventType:basket:buttonRemoveProduct',
            buttonPlusProduct: 'BX:Sale:Checkout:EventType:basket:buttonPlusProduct',
            buttonMinusProduct: 'BX:Sale:Checkout:EventType:basket:buttonMinusProduct',
            buttonRestoreProduct: 'BX:Sale:Checkout:EventType:basket:buttonRestoreProduct',
            removeProduct: 'BX:Sale:Checkout:EventType:basket:removeProduct',
            restoreProduct: 'BX:Sale:Checkout:EventType:basket:restoreProduct',
            backdropClose: 'BX:Sale:Checkout:EventType:basket:backdropClose',
            backdropOpenMobileMenu: 'BX:Sale:Checkout:EventType:basket:backdropOpenMobileMenu',
            backdropOpenChangeSku: 'BX:Sale:Checkout:EventType:basket:backdropOpenChangeSku',
            backdropTotalClose: 'BX:Sale:Checkout:EventType:basket:backdropTotalClose',
            backdropTotalOpen: 'BX:Sale:Checkout:EventType:basket:backdropTotalOpen',
            needRefresh: 'BX:Sale:Checkout:EventType:basket:needRefresh',
            refreshAfter: 'BX:Sale:Checkout:EventType:basket:refreshAfter',
            changeSku: 'BX:Sale:Checkout:EventType:basket:changeSku',
            changeSkuOriginName: 'SkuProperty::onChange'
        },
    consent:
        {
            refused: 'BX:Sale:Checkout:EventType:consent:refused',
            accepted: 'BX:Sale:Checkout:EventType:consent:accepted'
        },
    element:
        {
            buttonCheckout: 'BX:Sale:Checkout:EventType:element:buttonCheckout',
            buttonShipping: 'BX:Sale:Checkout:EventType:element:buttonShipping',
        },
    property:
        {
            validate: 'BX:Sale:Checkout:EventType:property:validate'
        },
    application:
        {
            none: 'BX:Sale:Checkout:EventType:application:status:none',
            wait: 'BX:Sale:Checkout:EventType:application:status:wait',
        },
    paysystem:
        {
            beforeInitList: 'BX:Sale:Checkout:EventType:paysystem:beforeInitList',
            afterInitList: 'BX:Sale:Checkout:EventType:paysystem:afterInitList'
        }
});