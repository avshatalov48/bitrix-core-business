import {Vue} from 'ui.vue';

Vue.component('sale-checkout-view-property-note_error', {
    props: ['message'],
    template: `
        <div class="invalid-feedback">
            {{message}}
        </div>
	`
});