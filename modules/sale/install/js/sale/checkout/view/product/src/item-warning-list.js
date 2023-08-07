import { BitrixVue } from 'ui.vue';

BitrixVue.component('sale-checkout-view-product-item_warning_list', {
	props: ['list'],

	// language=Vue
	template: `
	  <div class="checkout-item-warning-container">
	  		<div class="text-danger" v-for="(item, index) in list" :key="index" >{{item.message}}</div>
	  </div>
	`
});