this.BX = this.BX || {};
this.BX.Sale = this.BX.Sale || {};
this.BX.Sale.PaymentPay = this.BX.Sale.PaymentPay || {};
(function (exports,main_core,sale_paymentPay_lib) {
	'use strict';

	var BackendProvider = /*#__PURE__*/function (_AbstractBackendProvi) {
	  babelHelpers.inherits(BackendProvider, _AbstractBackendProvi);

	  function BackendProvider(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, BackendProvider);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(BackendProvider).call(this, options));
	    _this.returnUrl = _this.option('returnUrl', _this.getCurrentUrl());
	    _this.orderId = _this.option('orderId', null);
	    _this.paymentId = _this.option('paymentId', null);
	    _this.accessCode = _this.option('accessCode', null);
	    _this.paySystemId = null;
	    _this.response = null;
	    return _this;
	  }
	  /**
	   * @override
	   * @returns {Promise}
	   */


	  babelHelpers.createClass(BackendProvider, [{
	    key: "initiatePayment",
	    value: function initiatePayment() {
	      var _this2 = this;

	      if (!this.paySystemId) {
	        throw new Error('Payment system undefined');
	      }

	      return new Promise(function (resolve, reject) {
	        main_core.ajax.runComponentAction('bitrix:sale.order.checkout', 'initiatePay', {
	          mode: 'ajax',
	          data: {
	            fields: {
	              paySystemId: _this2.paySystemId,
	              returnUrl: _this2.returnUrl,
	              orderId: _this2.orderId,
	              paymentId: _this2.paymentId,
	              accessCode: _this2.accessCode
	            }
	          }
	        }).then(function (response) {
	          _this2.response = response;
	          resolve(_this2);
	        }).catch(function (error) {
	          _this2.response = error;
	          resolve(_this2);
	        });
	      });
	    }
	    /**
	     * @override
	     * @returns {object|string|*}
	     */

	  }, {
	    key: "getResponse",
	    value: function getResponse() {
	      return this.response;
	    }
	    /**
	     * @override
	     * @returns {boolean}
	     */

	  }, {
	    key: "isResponseSucceed",
	    value: function isResponseSucceed() {
	      return main_core.Type.isObject(this.response) && this.response.status === 'success';
	    }
	    /**
	     * @override
	     * @returns {string|null}
	     */

	  }, {
	    key: "getPaymentGateUrl",
	    value: function getPaymentGateUrl() {
	      if (main_core.Type.isObject(this.response.data) && main_core.Type.isStringFilled(this.response.data.url)) {
	        return this.response.data.url;
	      }

	      return null;
	    }
	    /**
	     * @override
	     * @returns {string|null}
	     */

	  }, {
	    key: "getPaymentFormHtml",
	    value: function getPaymentFormHtml() {
	      if (main_core.Type.isObject(this.response.data) && main_core.Type.isStringFilled(this.response.data.html)) {
	        return this.response.data.html;
	      }

	      return null;
	    }
	    /**
	     * @private
	     * @returns {string}
	     */

	  }, {
	    key: "getCurrentUrl",
	    value: function getCurrentUrl() {
	      return window.location.href;
	    }
	  }]);
	  return BackendProvider;
	}(sale_paymentPay_lib.AbstractBackendProvider);

	exports.BackendProvider = BackendProvider;

}((this.BX.Sale.PaymentPay.BackendProvider = this.BX.Sale.PaymentPay.BackendProvider || {}),BX,BX.Sale.PaymentPay.Lib));
//# sourceMappingURL=backend-provider.bundle.js.map
