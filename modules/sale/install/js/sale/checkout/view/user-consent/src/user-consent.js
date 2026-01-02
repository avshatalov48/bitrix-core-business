import { BitrixVue } from 'ui.vue';
import { ajax, Tag, Type } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { EventType, Component, RestMethod } from 'sale.checkout.const';

BitrixVue.component('sale-checkout-view-user_consent', {
	props: ['item'],
	mounted()
	{
		this.getBlockHtml();
	},
	methods:
	{
		getBlockHtml()
		{
			const userConsent = {
				title: this.item.title,
				isLoaded: this.item.isLoaded,
				autoSave: this.item.autoSave,
				submitEventName: this.item.submitEventName,
				fields: this.item.params,
				items: this.item.items,
			};

			ajax.runComponentAction(
				Component.bitrixSaleOrderCheckout,
				RestMethod.saleEntityUserConsentRequest,
				{
					data: {
						fields: userConsent,
					},
				},
			)
				.then((response) => {
					if (
						BX.type.isPlainObject(response.data)
							&& BX.type.isNotEmptyString(response.data.html)
					)
					{
						const consent = response.data.html;

						if (BX.UserConsent !== undefined)
						{
							const wrapper = this.$refs.consentDiv;

							wrapper.appendChild(Tag.render`<div>${consent}</div>`);

							BX.UserConsent.loadAll(wrapper);
							const controls = BX.UserConsent && BX.UserConsent.getItems();
							controls.forEach((control) => {
								EventEmitter.subscribe(
									control,
									BX.UserConsent.events.afterAccepted,
									(event) => EventEmitter.emit(
										EventType.consent.accepted,
										event,
									),
								);
								EventEmitter.subscribe(
									control,
									BX.UserConsent.events.refused,
									(event) => EventEmitter.emit(
										EventType.consent.refused,
										event,
									),
								);
							});
						}
					}
				});
		},
	},
	// language=Vue
	template: `
		 <div class="checkout-basket-section checkout-basket-section-consent">
		<div ref="consentDiv"/>
		    </div>
	`,
});
