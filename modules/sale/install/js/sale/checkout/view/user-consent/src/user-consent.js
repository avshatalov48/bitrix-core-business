import { BitrixVue } from 'ui.vue';
import { ajax, Tag, Type } from 'main.core';
import { EventEmitter } from 'main.core.events'
import { EventType, Component, RestMethod } from 'sale.checkout.const';


BitrixVue.component('sale-checkout-view-user_consent', {
	props: ['item'],
	methods:
	{
		getBlockHtml()
		{
			let userConsent = {
				id: this.item.id,
				title: this.item.title,
				isLoaded: this.item.isLoaded,
				autoSave: this.item.autoSave,
				isChecked: this.item.isChecked,
				submitEventName: this.item.submitEventName,
				fields: this.item.params
			};

			ajax.runComponentAction(
				Component.bitrixSaleOrderCheckout,
				RestMethod.saleEntityUserConsentRequest,
				{
					data: {
						fields: userConsent
					}
				}
			)
				.then((response)=>{
						if(
							BX.type.isPlainObject(response.data)
							&& BX.type.isNotEmptyString(response.data.html)
						)
						{
							let consent = response.data.html;

							if (BX.UserConsent !== undefined)
							{
								let wrapper = this.$refs.consentDiv;

								wrapper.appendChild(Tag.render`<div>${consent}</div>`);

								let control = BX.UserConsent.load(wrapper);


								BX.addCustomEvent(
									control,
									BX.UserConsent.events.accepted,
									() => EventEmitter.emit(EventType.consent.accepted, {})
								);
								BX.addCustomEvent(
									control,
									BX.UserConsent.events.refused,
									() => EventEmitter.emit(EventType.consent.refused, {})
								);
							}
						}
				})
		}
	},
	mounted()
	{
		this.getBlockHtml();
	},
	// language=Vue
	template: `
	  <div class="checkout-basket-section checkout-basket-section-consent">
		<div ref="consentDiv"/>
      </div>
	`
});