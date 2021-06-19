import { Vue } from 'ui.vue';

Vue.component('sale-checkout-view-element-button-shipping-link', {
	props: ['url'],
	methods:
	{
		clickAction()
		{
			document.location.href = this.url
		}
	},
	// language=Vue
	template: `
      <div class="btn btn-checkout-order-status-link" @click="clickAction">
	  		<slot name="link-title"/>
	  </div>
	`
});