import { BitrixVue } from 'ui.vue';
import { EventEmitter } from 'main.core.events';
import { Type } from 'main.core';
import { EventType, Property as Const } from 'sale.checkout.const';

BitrixVue.component('sale-checkout-view-element-input-property-date', {
	props: ['item', 'index', 'autocomplete', 'isDateTime'],
	methods: {
		onClick()
		{
			this.showCalendar();
			this.focusOnInput();
		},
		focusOnInput()
		{
			const element = this.$el.children[0];
			element.focus();
		},
		showCalendar()
		{
			BX.calendar({
				node: this.item.name,
				field: this.item.name,
				bTime: this.isDateTime,
				bUseSecond: false,
				callback_after: (data) => this.handleDate(data),
			});
		},
		handleDate(date)
		{
			const dateString = this.prepareDate(date);
			this.changeValue(dateString);
		},
		prepareDate(date)
		{
			if (this.isDateTime === true)
			{
				return date.toLocaleString([], {
					day: '2-digit',
					month: '2-digit',
					year: 'numeric',
					hour: 'numeric',
					minute: 'numeric',
				}).replace(',', '');
			}
			else
			{
				return date.toLocaleDateString().replace(',', '');
			}
		},
		blur()
		{
			if (Type.isStringFilled(this.item.value))
			{
				this.changeValue(this.item.value);
			}
		},
		changeValue(value)
		{
			let changeValue = '';
			if (Type.isStringFilled(value))
			{
				changeValue = this.validateDate(value)
					? this.prepareDate(BX.parseDate(value))
					: this.previousValue;
			}
			this.setDate(changeValue);
			this.validate();
		},
		validateDate(value): boolean
		{
			const date = BX.parseDate(value);
			return date && date.toLocaleDateString() !== 'Invalid Date';
		},
		validate()
		{
			EventEmitter.emit(EventType.property.validate, {index: this.index});
		},
		setDate(date)
		{
			this.item.value = date;
			this.previousValue = date;
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
		<div class="form-wrap form-asterisk">
			<input
				class="form-control form-control-lg"
				:class="checkedClassObject"
				@blur="blur"
				type="text"
				inputmode="numeric"
				:name="item.name"
				@click="onClick"
				@drop="(e) => e.preventDefault()"
				@dragstart="(e) => e.preventDefault()"
				@paste="(e) => e.preventDefault()"
				:autocomplete="autocomplete"
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
