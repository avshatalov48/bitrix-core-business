import { BitrixVue } from 'ui.vue';
import { Event } from 'main.core'
import { Property as Const, EventType} from 'sale.checkout.const';

BitrixVue.component('sale-checkout-view-property-input-text', {
    props: ['item', 'index', 'autocomplete'],
    methods:
        {
            validate()
            {
                Event.EventEmitter.emit(EventType.property.validate, {index: this.index});
            }
        },
    computed:
        {
            checkedClassObject()
            {
                return this.item.validated === Const.validate.unvalidated ?
                    {}
                    :
                    {
                        'is-invalid': this.item.validated === Const.validate.failure,
                        'is-valid': this.item.validated === Const.validate.successful
                    }
            }
        },
    // language=Vue
    template: `
        <input class="form-control form-control-lg" :class="checkedClassObject"
            @blur="validate"
            type="text" 
            :placeholder="item.name"
            :autocomplete="autocomplete"
            v-model="item.value"
        />
	`
});