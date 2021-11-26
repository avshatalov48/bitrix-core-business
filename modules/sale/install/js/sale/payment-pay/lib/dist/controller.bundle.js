this.BX = this.BX || {};
this.BX.Sale = this.BX.Sale || {};
(function (exports,main_core_events,sale_paymentPay_const) {
	'use strict';

	var FormHelper = /*#__PURE__*/function () {
	  /**
	   * @private
	   * @param {HTMLFormElement|null} form
	   */
	  function FormHelper(form) {
	    babelHelpers.classCallCheck(this, FormHelper);
	    this.form = form || null;
	  }
	  /**
	   * @public
	   * @param {string} html
	   * @returns {FormHelper}
	   */


	  babelHelpers.createClass(FormHelper, [{
	    key: "submit",

	    /**
	     * @public
	     * @returns {void}
	     */
	    value: function submit() {
	      if (!this.canSubmit()) {
	        return;
	      }

	      if (this.isVirtual()) {
	        document.body.appendChild(this.form.parentNode);
	      }

	      HTMLFormElement.prototype.submit.call(this.form);
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
	        if (!FormHelper.elementAllowed(this.form.elements[i])) {
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
	      tempNode.style.display = 'none';
	      var form = tempNode.querySelector('form');
	      return new FormHelper(form);
	    }
	    /**
	     * @public
	     * @param {HTMLElement} node
	     * @returns {FormHelper}
	     */

	  }, {
	    key: "createFromNode",
	    value: function createFromNode(node) {
	      if (node instanceof HTMLFormElement) {
	        return new FormHelper(node);
	      }

	      var form = node.querySelector('form');
	      return new FormHelper(form);
	    }
	  }, {
	    key: "elementAllowed",
	    value: function elementAllowed(element) {
	      var allowedTypes = FormHelper.getAllowedInputTypes();

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
	  return FormHelper;
	}();

	var Controller = /*#__PURE__*/function () {
	  function Controller(options) {
	    babelHelpers.classCallCheck(this, Controller);
	    this.options = options || {};
	    this.url = this.option('url', this.getDefaultAjaxController());
	    this.allowPaymentRedirect = this.option('allowPaymentRedirect', true);
	    this.returnUrl = this.option('returnUrl', this.getCurrentUrl());
	    this.orderId = this.option('orderId', null);
	    this.paymentId = this.option('paymentId', null);
	    this.accessCode = this.option('accessCode', null);
	    this.response = null;
	  }
	  /**
	   * @public
	   * @param {object} params
	   * @returns {Error|void}
	   */


	  babelHelpers.createClass(Controller, [{
	    key: "initPayment",
	    value: function initPayment(params) {
	      var _this = this;

	      if (!params.paySystemId) {
	        throw new Error('Payment system undefined');
	      }

	      BX.ajax({
	        method: 'POST',
	        dataType: 'json',
	        url: this.url,
	        data: {
	          sessid: BX.bitrix_sessid(),
	          paysystemId: params.paySystemId,
	          returnUrl: this.returnUrl,
	          orderId: this.orderId,
	          paymentId: this.paymentId,
	          access: this.accessCode
	        },
	        onsuccess: function onsuccess(response) {
	          _this.response = response;

	          _this.handleResponse();
	        }
	      });
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "handleResponse",
	    value: function handleResponse() {
	      if (this.isResponseSucceed()) {
	        this.tryToRedirectUserOnPaymentGate();
	        main_core_events.EventEmitter.emit(sale_paymentPay_const.EventType.payment.success, this.response);
	      } else {
	        main_core_events.EventEmitter.emit(sale_paymentPay_const.EventType.payment.error, this.response);
	      }
	    }
	    /**
	     * @private
	     * @returns {boolean}
	     */

	  }, {
	    key: "isResponseSucceed",
	    value: function isResponseSucceed() {
	      return BX.type.isObject(this.response) && this.response.status === 'success';
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "tryToRedirectUserOnPaymentGate",
	    value: function tryToRedirectUserOnPaymentGate() {
	      var url = BX.type.isString(this.response.url) ? this.response.url : '';
	      var html = BX.type.isString(this.response.html) ? this.response.html : '';

	      if (this.allowPaymentRedirect) {
	        if (url.length > 0) {
	          window.location.href = url;
	        } else if (html.length > 0) {
	          this.tryToAutoSubmitHtmlChunk(html);
	        }
	      }
	    }
	    /**
	     * @public
	     * @param {string} html
	     * @returns {void}
	     */

	  }, {
	    key: "tryToAutoSubmitHtmlChunk",
	    value: function tryToAutoSubmitHtmlChunk(html) {
	      FormHelper.createFromHtml(html).submit();
	    }
	    /**
	     * @public
	     * @param {HTMLElement} node
	     * @returns {void}
	     */

	  }, {
	    key: "tryToAutoSubmitDomNode",
	    value: function tryToAutoSubmitDomNode(node) {
	      FormHelper.createFromNode(node).submit();
	    }
	    /**
	     * @private
	     * @returns {string}
	     */

	  }, {
	    key: "getDefaultAjaxController",
	    value: function getDefaultAjaxController() {
	      return sale_paymentPay_const.Api.controller.initPayment;
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
	  return Controller;
	}();

	exports.Controller = Controller;

}((this.BX.Sale.PaymentPay = this.BX.Sale.PaymentPay || {}),BX.Event,BX.Sale.PaymentPay.Const));
//# sourceMappingURL=controller.bundle.js.map
