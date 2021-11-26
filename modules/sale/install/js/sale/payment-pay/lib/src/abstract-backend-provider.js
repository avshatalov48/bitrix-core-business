export class AbstractBackendProvider
{
	constructor(options) {
		this.options = options || {};
	}

	/**
	 * @public
	 * @returns {Promise} Resolve when backend responds, reject if there was 4** or 5** HTTP errors
	 */
	initiatePayment() {}

	/**
	 * @public
	 * @returns {object|string|*} Plain response from backend
	 */
	getResponse() {}

	/**
	 * Returns true if payment inited and user can be redirected to payment gate.
	 * @public
	 * @returns {boolean}
	 */
	isResponseSucceed() {}

	/**
	 * Returns url of payment gate which user can be redirected to, or null.
	 * @public
	 * @returns {string|null}
	 */
	getPaymentGateUrl() {}

	/**
	 * Returns HTML-chunk with payment form which can be displayed to user, or null.
	 * @public
	 * @returns {string|null}
	 */
	getPaymentFormHtml() {}

	/**
	 * @protected
	 * @param {string} name
	 * @param {*} defaultValue
	 * @returns {*}
	 */
	option(name, defaultValue) {
		return this.options.hasOwnProperty(name) ? this.options[name] : defaultValue;
	}
}