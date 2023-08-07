import { BitrixVue } from 'ui.vue';

import './alert'

BitrixVue.component('sale-checkout-view-alert-list', {
    props: ['errors'],
    // language=Vue
    template: `
		<div v-if="errors.length>0">
          <template v-for="(error) in errors" >
            <sale-checkout-view-alert :error="error"/>
          </template>
        </div>
	`
});