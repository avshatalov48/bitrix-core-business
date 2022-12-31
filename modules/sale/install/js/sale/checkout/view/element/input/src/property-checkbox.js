import { BitrixVue } from 'ui.vue';
import { EventEmitter } from 'main.core.events';
import { Property as Const, EventType } from 'sale.checkout.const';

BitrixVue.component('sale-checkout-view-element-input-property-checkbox', {
	props: ['item', 'index'],
	data()
	{
		return {'showValue': this.item.value === 'Y'}
	},
	methods: {
		validate()
		{
			EventEmitter.emit(EventType.property.validate, { index: this.index });
		},
	},
	computed: {
		checkedClassObject()
		{
			return {
				'is-invalid': this.item.validated === Const.validate.failure,
				'is-valid': this.item.validated === Const.validate.successful,
			};
		},
		switchValue: {
			get()
			{
				this.showValue = this.item.value === 'Y';
				return this.item.value === 'Y';
			},
			set(value)
			{
				if (value)
				{
					this.item.value = 'Y';
					this.showValue = true;
				}
				else
				{
					this.item.value = 'N';
					this.showValue = false;
				}
				this.validate();
			}
		},
		isAsteriskShown()
		{
			return this.item.required === 'Y';
		},
	},
	// language=Vue
	template: `
		<div class="form-wrap form-control form-control-lg border-0 pl-0 form-asterisk" :class="checkedClassObject">
			<input
				@blur="validate"
				type="checkbox"
				:id="item.name"
				:value="showValue"
				v-model="switchValue"
			/>
			<label :for="item.name" class="ml-2">{{item.name}}</label>
			<div 
				class="asterisk-item"
				v-if="isAsteriskShown"
			>
			</div>
		</div>
	`,
});
