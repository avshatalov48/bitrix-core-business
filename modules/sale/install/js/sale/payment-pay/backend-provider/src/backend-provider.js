import {ajax, Type} from 'main.core';
import {AbstractBackendProvider} from 'sale.payment-pay.lib';

export class BackendProvider extends AbstractBackendProvider
{
	constructor(options) {
		super(options);

		this.returnUrl = this.option('returnUrl', this.getCurrentUrl());
		this.orderId = this.option('orderId', null);
		this.paymentId = this.option('paymentId', null);
		this.accessCode = this.option('accessCode', null);
		this.paySystemId = null;
		this.response = null;
	}

	/**
	 * @override
	 * @returns {Promise}
	 */
	initiatePayment() {
		if (!this.paySystemId) {
			throw new Error('Payment system undefined');
		}

		return new Promise((resolve, reject) => {
			ajax.runComponentAction(
				'bitrix:sale.order.checkout',
				'initiatePay',
				{
					mode: 'ajax',
					data: {
						fields: {
							paySystemId: this.paySystemId,
							returnUrl: this.returnUrl,
							orderId: this.orderId,
							paymentId: this.paymentId,
							accessCode: this.accessCode,
						}
					},
				}
			).then((response) => {
				this.response = response;
				resolve(this);
			}).catch((error) => {
				this.response = error;
				resolve(this);
			});
		});
	}

	/**
	 * @override
	 * @returns {object|string|*}
	 */
	getResponse() {
		return this.response;
	}

	/**
	 * @override
	 * @returns {boolean}
	 */
	isResponseSucceed() {
		return Type.isObject(this.response) && this.response.status === 'success';
	}

	/**
	 * @override
	 * @returns {string|null}
	 */
	getPaymentGateUrl() {
		if (Type.isObject(this.response.data) && Type.isStringFilled(this.response.data.url)) {
			return this.response.data.url;
		}
		return null;
	}

	/**
	 * @override
	 * @returns {string|null}
	 */
	getPaymentFormHtml() {
		if (Type.isObject(this.response.data) && Type.isStringFilled(this.response.data.html)) {
			return this.response.data.html;
		}
		return null;
	}

	/**
	 * @private
	 * @returns {string}
	 */
	getCurrentUrl() {
		return window.location.href;
	}
}
