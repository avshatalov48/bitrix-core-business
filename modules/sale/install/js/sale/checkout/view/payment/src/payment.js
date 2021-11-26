import { BitrixVue } from 'ui.vue';
import { Component, RestMethod, EventType } from 'sale.checkout.const';
import { ajax } from 'main.core';
import { EventEmitter } from 'main.core.events'
import { MixinLoader } from "sale.checkout.view.mixins";

BitrixVue.component('sale-checkout-view-payment', {
	props: ['order', 'config'],
	mixins:[MixinLoader],
	methods:
		{
			getBlockHtml() {

				const fields = {
					accessCode: this.order.hash,
					orderId: this.order.id,
					returnUrl: this.config.returnUrl,
				}

				ajax.runComponentAction(
					Component.bitrixSaleOrderCheckout,
					RestMethod.saleEntityPaymentPay,
					{
						data: {
							fields: fields
						}
					})
					.then((response) => {
						let html = response.data.html;
						let wrapper = this.$refs.paymentSystemList;

						BX.html(wrapper, html);

						EventEmitter.emit(EventType.paysystem.afterInitList, {});

						BX.addCustomEvent('onChangePaySystems', () => {

							EventEmitter.emit(EventType.paysystem.beforeInitList, {});

							this.getBlockHtml()
						});
					})
			}
		},
	mounted()
	{
		this.getBlockHtml();
	},
	// language=Vue
	template: `
		<div style='position: relative;' ref="container">
			<div ref="paymentSystemList"/>
		</div>
	`
});