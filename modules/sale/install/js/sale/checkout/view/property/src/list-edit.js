import { BitrixVue } from 'ui.vue';
import {Property as Const} from 'sale.checkout.const';

import 'sale.checkout.view.element.input';

import './note-error'

BitrixVue.component('sale-checkout-view-property-list_edit', {
	props: ['items', 'errors'],
	computed:
	{
		localize() {
			return Object.freeze(
				BitrixVue.getFilteredPhrases('CHECKOUT_VIEW_PROPERTY_LIST_'))
		}
	},
	methods:
	{
		getErrorMessage(item)
		{
			let error = this.errors.find(error => error.propertyId === item.id);
			return typeof error !== 'undefined' ? error.message:null
		},
		isPhone(item)
		{
			return item.type === Const.type.phone
		},
		isName(item)
		{
			return item.type === Const.type.name
		},
		isEmail(item)
		{
			return item.type === Const.type.email
		},
		isFailure(item)
		{
			return item.validated === Const.validate.failure
		}
	},
	// language=Vue
	template: `
		<div class="checkout-basket-section checkout-basket-section-personal-form">
			<h2 class="checkout-basket-title">{{localize.CHECKOUT_VIEW_PROPERTY_LIST_VIEW_SHIPPING_CONTACTS}}</h2>
				<template v-for="(item, index) in items">
				  <div class="form-group" v-if="isName(item)">
					<sale-checkout-view-element-input-property-text :item="item" :index="index" :autocomplete="'name'"/>
					<sale-checkout-view-property-note_error v-if="isFailure(item)"
															:message="getErrorMessage(item)"/>
				  </div>
				</template>
				<template v-for="(item, index) in items">
				  <div class="form-group" v-if="isPhone(item)">
					<sale-checkout-view-element-input-property-phone :item="item" :index="index"/>
					<sale-checkout-view-property-note_error v-if="isFailure(item)"
															:message="getErrorMessage(item)"/>
				  </div>
				</template>
				<template v-for="(item, index) in items">
				  <div class="form-group" v-if="isEmail(item)">
					<sale-checkout-view-element-input-property-text :item="item" :index="index" :autocomplete="'email'" />
					<sale-checkout-view-property-note_error v-if="isFailure(item)"
															:message="getErrorMessage(item)"/>
				  </div>
				</template>
	
				<template v-for="(item, index) in items">
				  <div class="form-group" v-if="isPhone(item) === false && isName(item) === false && isEmail(item) === false">
					<sale-checkout-view-element-input-property-text :item="item" :index="index" :autocomplete="'off'"/>
					<sale-checkout-view-property-note_error v-if="isFailure(item)"
															:message="getErrorMessage(item)"/>
				  </div>
				</template>
		</div>
	`
});