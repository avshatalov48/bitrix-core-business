import {Vue} from 'ui.vue';
import {Application, Loader as LoaderConst} from 'sale.checkout.const';

import 'sale.checkout.view.product.info'
import 'sale.checkout.view.product.price'
import 'sale.checkout.view.product.info-deleted'

import './row.css'

Vue.component('sale-checkout-view-product-row', {
	props: ['item', 'index', 'mode'],
	computed:
	{
		isDeleted()
		{
			return this.item.deleted === 'Y';
		},
		isLocked()
		{
			return this.item.status === LoaderConst.status.wait
		},
		buttonMinusDisabled()
		{
			return this.item.quantity - this.item.product.ratio < this.item.product.ratio
		},
		buttonPlusDisabled()
		{
			return this.item.quantity + this.item.product.ratio > this.item.product.availableQuantity
		},
		getConstMode()
		{
			return Application.mode
		},
		getObjectClass()
		{
			const classes = [
				'checkout-item'
			];
			
			if(this.isDeleted)
			{
				classes.push('checkout-item-deleted')
			}
			
			if(this.isLocked)
			{
				classes.push('checkout-item-locked');
			}
			
			return classes;
		}
	},
	// language=Vue
	template: `
      <tr :class="getObjectClass">
      <template v-if="isDeleted">
        <td colspan="2">
          <sale-checkout-view-product-info_deleted :item="item" :index="index"/>
        </td>
      </template>
      <template v-else>
        <td>
          <template v-if="mode === getConstMode.edit">
            <sale-checkout-view-product-info :item="item" :index="index">
              <template v-slot:button-minus><sale-checkout-view-element-button-minus :class="{'checkout-item-quantity-btn-disabled': buttonMinusDisabled}" :index="index"/></template>
              <template v-slot:button-plus><sale-checkout-view-element-button-plus :class="{'checkout-item-quantity-btn-disabled': buttonPlusDisabled}" :index="index"/></template>
            </sale-checkout-view-product-info>
          </template>
          <template v-else>
            <sale-checkout-view-product-info :item="item" :index="index"/>
          </template>
        </td>
        <td>
          <sale-checkout-view-product-price :item="item" :index="index" />
          <sale-checkout-view-element-button-remove :index="index" v-if="mode === getConstMode.edit"/>
        </td>
      </template>
      </tr>
	`
});