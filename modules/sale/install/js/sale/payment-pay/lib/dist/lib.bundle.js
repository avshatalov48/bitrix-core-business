this.BX = this.BX || {};
this.BX.Sale = this.BX.Sale || {};
this.BX.Sale.PaymentPay = this.BX.Sale.PaymentPay || {};
(function (exports,main_core_events,sale_paymentPay_const) {
	'use strict';

	var AbstractBackendProvider = /*#__PURE__*/function () {
	  function AbstractBackendProvider(options) {
	    babelHelpers.classCallCheck(this, AbstractBackendProvider);
	    this.options = options || {};
	  }
	  /**
	   * @public
	   * @returns {Promise} Resolve when backend responds, reject if there was 4** or 5** HTTP errors
	   */


	  babelHelpers.createClass(AbstractBackendProvider, [{
	    key: "initiatePayment",
	    value: function initiatePayment() {}
	    /**
	     * @public
	     * @returns {object|string|*} Plain response from backend
	     */

	  }, {
	    key: "getResponse",
	    value: function getResponse() {}
	    /**
	     * Returns true if payment inited and user can be redirected to payment gate.
	     * @public
	     * @returns {boolean}
	     */

	  }, {
	    key: "isResponseSucceed",
	    value: function isResponseSucceed() {}
	    /**
	     * Returns url of payment gate which user can be redirected to, or null.
	     * @public
	     * @returns {string|null}
	     */

	  }, {
	    key: "getPaymentGateUrl",
	    value: function getPaymentGateUrl() {}
	    /**
	     * Returns HTML-chunk with payment form which can be displayed to user, or null.
	     * @public
	     * @returns {string|null}
	     */

	  }, {
	    key: "getPaymentFormHtml",
	    value: function getPaymentFormHtml() {}
	    /**
	     * @protected
	     * @param {string} name
	     * @param {*} defaultValue
	     * @returns {*}
	     */

	  }, {
	    key: "option",
	    value: function option(name, defaultValue) {
	      return this.options.hasOwnProperty(name) ? this.options[name] : defaultValue;
	    }
	  }]);
	  return AbstractBackendProvider;
	}();

	var VirtualForm = /*#__PURE__*/function () {
	  /**
	   * @private
	   * @param {HTMLFormElement|null} form
	   */
	  function VirtualForm(form) {
	    babelHelpers.classCallCheck(this, VirtualForm);
	    this.form = form || null;
	  }
	  /**
	   * @public
	   * @param {string} html
	   * @returns {VirtualForm}
	   */


	  babelHelpers.createClass(VirtualForm, [{
	    key: "submit",

	    /**
	     * @public
	     * @returns {boolean}
	     */
	    value: function submit() {
	      if (!this.canSubmit()) {
	        return false;
	      }

	      if (this.isVirtual()) {
	        var tempNode = document.createElement('div');
	        tempNode.style.display = 'none';
	        tempNode.append(this.form);
	        document.body.appendChild(tempNode);
	      }

	      HTMLFormElement.prototype.submit.call(this.form);
	      return true;
	    }
	    /**
	     * @public
	     * @returns {boolean}
	     */

	  }, {
	    key: "canSubmit",
	    value: function canSubmit() {
	      return this.isValidFormObject() && this.containsAllowedInputTypesOnly();
	    }
	    /**
	     * @private
	     * @returns {boolean}
	     */

	  }, {
	    key: "isValidFormObject",
	    value: function isValidFormObject() {
	      return this.form instanceof HTMLFormElement;
	    }
	    /**
	     * @private
	     * @returns {boolean}
	     */

	  }, {
	    key: "containsAllowedInputTypesOnly",
	    value: function containsAllowedInputTypesOnly() {
	      if (!this.form || !this.form.elements) {
	        return false;
	      } // eslint-disable-next-line no-plusplus


	      for (var i = 0; i < this.form.elements.length; i++) {
	        if (!VirtualForm.elementAllowed(this.form.elements[i])) {
	          return false;
	        }
	      }

	      return true;
	    }
	    /**
	     * @private
	     * @param element
	     * @returns {boolean}
	     */

	  }, {
	    key: "isVirtual",

	    /**
	     * @public
	     * @returns {boolean}
	     */
	    value: function isVirtual() {
	      if (this.form) {
	        return !document.body.contains(this.form);
	      }

	      return true;
	    }
	  }], [{
	    key: "createFromHtml",
	    value: function createFromHtml(html) {
	      var tempNode = document.createElement('div');
	      tempNode.innerHTML = html;
	      var form = tempNode.querySelector('form');
	      return new VirtualForm(form);
	    }
	    /**
	     * @public
	     * @param {HTMLElement} node
	     * @returns {VirtualForm}
	     */

	  }, {
	    key: "createFromNode",
	    value: function createFromNode(node) {
	      if (node instanceof HTMLFormElement) {
	        return new VirtualForm(node);
	      }

	      var form = node.querySelector('form');
	      return new VirtualForm(form);
	    }
	  }, {
	    key: "elementAllowed",
	    value: function elementAllowed(element) {
	      var allowedTypes = VirtualForm.getAllowedInputTypes();

	      if (element instanceof HTMLInputElement) {
	        return allowedTypes.indexOf(element.type) !== -1;
	      }

	      return true;
	    }
	    /**
	     * @private
	     * @returns {string[]}
	     */

	  }, {
	    key: "getAllowedInputTypes",
	    value: function getAllowedInputTypes() {
	      return ['hidden', 'submit'];
	    }
	  }]);
	  return VirtualForm;
	}();

	var PaymentProcess = /*#__PURE__*/function () {
	  function PaymentProcess(options) {
	    babelHelpers.classCallCheck(this, PaymentProcess);
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


	  babelHelpers.createClass(PaymentProcess, [{
	    key: "start",
	    value: function start() {
	      var _this = this;

	      this.backendProvider.initiatePayment().then(function () {
	        _this.handleResponse();
	      });
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "handleResponse",
	    value: function handleResponse() {
	      if (this.backendProvider.isResponseSucceed()) {
	        var redirected = this.tryToRedirectUserOnPaymentGate();

	        if (!redirected) {
	          main_core_events.EventEmitter.emit(sale_paymentPay_const.EventType.payment.success, this.backendProvider.getResponse());
	        }
	      } else {
	        main_core_events.EventEmitter.emit(sale_paymentPay_const.EventType.payment.error, this.backendProvider.getResponse());
	      }
	    }
	    /**
	     * @private
	     * @returns {boolean}
	     */

	  }, {
	    key: "tryToRedirectUserOnPaymentGate",
	    value: function tryToRedirectUserOnPaymentGate() {
	      var url = this.backendProvider.getPaymentGateUrl();
	      var html = this.backendProvider.getPaymentFormHtml();

	      if (this.allowPaymentRedirect) {
	        if (url) {
	          window.location.href = url;
	          return true;
	        } else if (html) {
	          return this.tryToAutoSubmitHtmlChunk(html);
	        }
	      }

	      return false;
	    }
	    /**
	     * @private
	     * @param {string} html
	     * @returns {boolean}
	     */

	  }, {
	    key: "tryToAutoSubmitHtmlChunk",
	    value: function tryToAutoSubmitHtmlChunk(html) {
	      return VirtualForm.createFromHtml(html).submit();
	    }
	    /**
	     * @private
	     * @param {string} name
	     * @param {*} defaultValue
	     * @returns {*}
	     */

	  }, {
	    key: "option",
	    value: function option(name, defaultValue) {
	      return this.options.hasOwnProperty(name) ? this.options[name] : defaultValue;
	    }
	  }]);
	  return PaymentProcess;
	}();

	var Settings = /*#__PURE__*/function () {
	  function Settings(settings) {
	    babelHelpers.classCallCheck(this, Settings);
	    this.settings = settings;
	  }

	  babelHelpers.createClass(Settings, [{
	    key: "get",
	    value: function get(name, defaultValue) {
	      var parts = name.split('.');
	      var currentOption = this.settings;
	      var found = false;
	      parts.map(function (part) {
	        if (currentOption && currentOption.hasOwnProperty(part)) {
	          currentOption = currentOption[part];
	          found = true;
	        } else {
	          currentOption = null;
	          found = false;
	        }
	      });
	      return found ? currentOption : defaultValue;
	    }
	  }]);
	  return Settings;
	}();

	exports.AbstractBackendProvider = AbstractBackendProvider;
	exports.PaymentProcess = PaymentProcess;
	exports.VirtualForm = VirtualForm;
	exports.Settings = Settings;

}((this.BX.Sale.PaymentPay.Lib = this.BX.Sale.PaymentPay.Lib || {}),BX.Event,BX.Sale.PaymentPay.Const));
//# sourceMappingURL=lib.bundle.js.map
