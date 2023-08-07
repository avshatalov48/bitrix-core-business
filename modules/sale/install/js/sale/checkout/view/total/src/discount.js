import { BitrixVue } from 'ui.vue';
import {CurrencyCore} from 'currency.currency-core';

BitrixVue.component('sale-checkout-view-total-discount', {
    props: ['total'],
    computed:
    {
        localize()
        {
            return Object.freeze(
                BitrixVue.getFilteredPhrases('CHECKOUT_VIEW_TOTAL_'))
        },
        discountSumFormatted()
        {
            return CurrencyCore.currencyFormat(this.total.discount.sum, this.total.currency, true);
        }
    },
    // language=Vue
    template: `
      <tr class="checkout-basket-total-item checkout-basket-total-item-discount">
        <td>
          <div class="checkout-basket-total-item-summary">
            <span>{{localize.CHECKOUT_VIEW_TOTAL_TOTAL_PROFIT}}</span>
          </div>
        </td>
        <td>
          <div class="checkout-basket-total-price-block">
            <span class="checkout-basket-total-item-price-discount" v-html="'-' + discountSumFormatted"/>
          </div>
        </td>
      </tr>
    `
});