import { BitrixVue } from 'ui.vue';
import {CurrencyCore} from 'currency.currency-core';

BitrixVue.component('sale-checkout-view-total-summary', {
    props: ['total'],
    computed:
    {
        localize()
        {
            return Object.freeze(
                BitrixVue.getFilteredPhrases('CHECKOUT_VIEW_TOTAL_'))
        },
        priceFormatted()
        {
            return CurrencyCore.currencyFormat(this.total.price, this.total.currency, true);
        }
    },
    // language=Vue
    template: `
       <tr class="checkout-basket-total-item checkout-basket-total-item-total">
        <td>
          <div class="checkout-basket-total-item-summary">
            <span>{{localize.CHECKOUT_VIEW_TOTAL_TOTAL_SUMMARY}}</span>
          </div>
        </td>
        <td>
          <div class="checkout-basket-total-price-block">
            <span class="checkout-basket-total-item-price" v-html="priceFormatted"/>
          </div>
        </td>
      </tr>
    `
});