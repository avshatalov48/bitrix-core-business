import { BitrixVue } from 'ui.vue';
import { EventEmitter } from 'main.core.events'
import { PhoneFilter as Filter, PhoneFormatter as Formatter} from "ui.type";
import { Property as Const, EventType } from 'sale.checkout.const';

BitrixVue.component('sale-checkout-view-element-input-property-phone', {
	props: ['item', 'index'],
	methods:
	{
		validate()
		{
			EventEmitter.emit(EventType.property.validate, {index: this.index});
		},
		onKeyDown(e)
		{
			let value = e.key;
			
			if (Filter.replace(value) !== '')
			{
				return;
			}
			
			if (['Esc', 'Delete', 'Backspace', 'Tab'].indexOf(e.key) >= 0)
			{
				return;
			}
			if (e.ctrlKey || e.metaKey)
			{
				return;
			}
			
			e.preventDefault();
		},
		onInput()
		{
			let value = Formatter.formatValue(this.value);
			if (this.value !== value)
			{
				this.value = value;
			}
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
		},
		value: {
			get ()
			{
				return this.item.value;
			},
			set (newValue)
			{
				this.item.value = newValue;
			}
		},
	},
	// language=Vue
	template: `
      <input class="form-control form-control-lg" :class="checkedClassObject"
             @blur="validate"
             @input="onInput"
			 @keydown="onKeyDown"
             v-model="value"
             autocomplete="tel"
			 :placeholder="item.name"
      />
	`
});