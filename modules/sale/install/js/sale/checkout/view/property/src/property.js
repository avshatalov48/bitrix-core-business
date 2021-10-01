import { BitrixVue } from 'ui.vue';
import { Application } from 'sale.checkout.const';

import 'sale.checkout.view.property';

BitrixVue.component('sale-checkout-view-property', {
    props: ['items', 'mode', 'order', 'errors'],
    computed:
    {
        getConstMode()
        {
            return Application.mode
        }
    },
    template: `
		<div>
		    <template v-if="mode === getConstMode.edit">
		         <sale-checkout-view-property-list_edit :items="items" :errors="errors"/>
		    </template>
		    <template v-else>
		        <sale-checkout-view-property-list_view :items="items" :number="order.accountNumber"/>
            </template>
        </div>
	`
});