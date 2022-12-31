import { BitrixVue } from 'ui.vue';
import { EventEmitter } from 'main.core.events'
import { Property as Const, EventType} from 'sale.checkout.const';

BitrixVue.component('sale-checkout-view-element-input-property-number', {
	props: ['item', 'index'],
	methods: {
		validate()
		{
			EventEmitter.emit(EventType.property.validate, {index: this.index});
		},
		onKeyDown(e)
		{
			if (
				!isNaN(Number(e.key))
				&& e.key !== ' '
			)
			{
				return;
			}
			if (e.ctrlKey
				|| e.metaKey
				|| ['Esc', 'Tab', 'Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', '.'].indexOf(e.key) >= 0
			)
			{
				return;
			}
			e.preventDefault();
		},
		onPaste(e)
		{
			e.preventDefault();
			const pastedText = e.clipboardData.getData('Text');
			if (!isNaN(Number(pastedText)))
			{
				this.item.value = pastedText.trim();
			}
			this.validate();
		},
	},
	computed: {
		checkedClassObject()
		{
			return {
				'is-invalid': this.item.validated === Const.validate.failure,
				'is-valid': this.item.validated === Const.validate.successful
			}
		},
		isEmpty()
		{
			return this.item.value === '';
		},
		isRequired()
		{
			return this.item.required === 'Y';
		},
		isAsteriskShown()
		{
			return this.isEmpty && this.isRequired;
		},
	},
	// language=Vue
	template: `
		<div class="form-wrap form-asterisk" :class="checkedClassObject">
			<input
				class="form-control form-control-lg"
				:class="checkedClassObject"
				@blur="validate"
				@keydown="onKeyDown"
				@keyup="validate"
				@paste="onPaste"
				type="text"
				inputmode="numeric"
				:placeholder="item.name"
				v-model="item.value"
			/>
			<span
				class="asterisk-item"
				v-if="isAsteriskShown"
			>
				{{item.name}}
			</span>
		</div>
	`
});
