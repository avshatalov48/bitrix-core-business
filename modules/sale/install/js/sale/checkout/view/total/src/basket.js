import { BitrixVue } from 'ui.vue';
import {CurrencyCore} from 'currency.currency-core';

BitrixVue.component('sale-checkout-view-total-basket', {
    props: ['total'],
    computed:
    {
        localize()
        {
            return Object.freeze(
                BitrixVue.getFilteredPhrases('CHECKOUT_VIEW_TOTAL_'))
        },
        basePriceFormatted()
        {
            return CurrencyCore.currencyFormat(this.total.basePrice, this.total.currency, true);
        }
    },
    // language=Vue
    template: `
      <tr class="checkout-basket-total-item checkout-basket-total-item-subtotal">
        <td>
          <div class="checkout-basket-total-item-summary">
            <span>{{localize.CHECKOUT_VIEW_TOTAL_TOTAL_ITEMS}}</span>
          </div>
        </td>
        <td>
          <div class="checkout-basket-total-item-price-block">
            <span class="checkout-basket-total-item-price" v-html="basePriceFormatted"/>
          </div>
        </td>
      </tr>
    `
});