import { BitrixVue } from 'ui.vue';
import { EventEmitter } from 'main.core.events';
import { Property as Const, EventType } from 'sale.checkout.const';

BitrixVue.component('sale-checkout-view-element-input-property-text', {
	props: ['item', 'index', 'autocomplete'],
	data()
	{
		return {
			showAsterisk: this.showAsterisk,
		}
	},
	methods: {
		validate()
		{
			EventEmitter.emit(EventType.property.validate, { index: this.index });
		},
		onKeyUp(e)
		{
			if (['Esc', 'Tab'].indexOf(e.key) >= 0)
			{
				return;
			}
			if (e.ctrlKey || e.metaKey)
			{
				return;
			}
			if (this.isKeyAndroidChrome(e.key))
			{
				this.hideAsteriskAndroid();
				return;
			}
			this.validate();
		},
		isKeyAndroidChrome(key)
		{
			return key === 'Unidentified';
		},
		hideAsteriskAndroid()
		{
			const asterisk = this.$el.getElementsByTagName('span')[0];
			asterisk.style.display = 'none';
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
				type="text"
				:placeholder="item.name"
				:autocomplete="autocomplete"
				v-model="item.value"
				@keyup="onKeyUp"
			/>
			<span 
				class="asterisk-item"
				v-if="isAsteriskShown"
			>
				{{item.name}}
			</span>
		</div>
	`,
});
