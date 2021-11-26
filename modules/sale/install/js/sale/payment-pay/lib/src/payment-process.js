import {EventEmitter} from 'main.core.events';
import {EventType} from 'sale.payment-pay.const';
import {VirtualForm} from './virtual-form';
import {AbstractBackendProvider} from './abstract-backend-provider';

export class PaymentProcess
{
	constructor(options)
	{
		this.options = options || {};

		this.backendProvider = this.option('backendProvider', null);

		if (!this.backendProvider || !this.backendProvider instanceof AbstractBackendProvider) {
			throw new Error('Invalid backend provider');
		}

		this.allowPaymentRedirect = this.option('allowPaymentRedirect', true);
	}

	/**
	 * @public
	 * @returns {void}
	 */
	start()
	{
		this.backendProvider.initiatePayment().then(() => {this.handleResponse()});
	}

	/**
	 * @private
	 */
	handleResponse()
	{
		if (this.backendProvider.isResponseSucceed()) {
			this.tryToRedirectUserOnPaymentGate();
			EventEmitter.emit(EventType.payment.success, this.backendProvider.getResponse());
		} else {
			EventEmitter.emit(EventType.payment.error, this.backendProvider.getResponse());
		}
	}

	/**
	 * @private
	 */
	tryToRedirectUserOnPaymentGate()
	{
		const url = this.backendProvider.getPaymentGateUrl();
		const html = this.backendProvider.getPaymentFormHtml();

		if (this.allowPaymentRedirect) {
			if (url) {
				window.location.href = url;
			} else if (html) {
				this.tryToAutoSubmitHtmlChunk(html);
			}
		}
	}

	/**
	 * @private
	 * @param {string} html
	 * @returns {void}
	 */
	tryToAutoSubmitHtmlChunk(html)
	{
		VirtualForm.createFromHtml(html).submit();
	}

	/**
	 * @private
	 * @param {string} name
	 * @param {*} defaultValue
	 * @returns {*}
	 */
	option(name, defaultValue)
	{
		return this.options.hasOwnProperty(name) ? this.options[name] : defaultValue;
	}
}