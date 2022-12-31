this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
(function (exports,main_core) {
	'use strict';

	var Helper = /*#__PURE__*/function () {
	  /**
	   * Constructor.
	   */
	  function Helper(params) {
	    babelHelpers.classCallCheck(this, Helper);
	    this.idDomainName = params.idDomainName;
	    this.idDomainMessage = params.idDomainMessage;
	    this.idDomainLoader = params.idDomainLoader;
	    this.idDomainLength = params.idDomainLength || null;
	    this.idDomainErrorAlert = params.idDomainErrorAlert;
	    this.classes = {
	      dangerBorder: 'ui-ctl-danger',
	      successBorder: 'ui-ctl-success',
	      dangerAlert: 'landing-domain-alert-danger',
	      successAlert: 'landing-domain-alert-success'
	    };

	    if (this.idDomainName) {
	      this.idDomainNameParent = this.idDomainName.parentNode;
	    }
	  }
	  /**
	   * Shows loader div near input.
	   */


	  babelHelpers.createClass(Helper, [{
	    key: "showLoader",
	    value: function showLoader() {
	      this.clearMessage();
	      this.hideLength();
	      main_core.Dom.show(this.idDomainLoader);
	    }
	    /**
	     * Hides loader div near input.
	     */

	  }, {
	    key: "hideLoader",
	    value: function hideLoader() {
	      this.showLength();
	      main_core.Dom.hide(this.idDomainLoader);
	    }
	    /**
	     * Returns true if loader showed.
	     * @return {boolean}
	     */

	  }, {
	    key: "isLoaderShowed",
	    value: function isLoaderShowed() {
	      return this.idDomainLoader && this.idDomainLoader.style.display !== 'none';
	    }
	  }, {
	    key: "setLength",
	    value: function setLength(length) {
	      var limit = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : Helper.DEFAULT_LENGTH_LIMIT;

	      if (this.idDomainLength) {
	        this.idDomainLength.innerHTML = main_core.Loc.getMessage('LANDING_TPL_DOMAIN_LENGTH_LIMIT', {
	          '#LENGTH#': length,
	          '#LIMIT#': limit
	        });
	      }

	      main_core.Dom.show(this.idDomainLength);
	    }
	  }, {
	    key: "hideLength",
	    value: function hideLength() {
	      if (this.idDomainLength) {
	        main_core.Dom.hide(this.idDomainLength);
	      }
	    }
	  }, {
	    key: "showLength",
	    value: function showLength() {
	      if (this.idDomainLength) {
	        main_core.Dom.show(this.idDomainLength);
	      }
	    }
	    /**
	     * Marks input with success class.
	     * @param {string} successMessage Success message.
	     */

	  }, {
	    key: "setSuccess",
	    value: function setSuccess(successMessage) {
	      if (this.idDomainErrorAlert) {
	        main_core.Dom.hide(this.idDomainErrorAlert);
	      }

	      this.setMessage(successMessage);
	    }
	    /**
	     * Sets error message on error occurred or hide message if errorMessage is empty.
	     * @param {string} errorMessage Error message.
	     */

	  }, {
	    key: "setError",
	    value: function setError(errorMessage) {
	      this.setMessage(errorMessage, true);
	    }
	    /**
	     * Returns true if error message showed.
	     * @return {boolean}
	     */

	  }, {
	    key: "isErrorShowed",
	    value: function isErrorShowed() {
	      return this.idDomainMessage && main_core.Dom.hasClass(this.idDomainMessage, this.classes.dangerAlert) && this.idDomainMessage.style.display !== 'none';
	    }
	    /**
	     * Sets success or fail message.
	     * @param {string} message Error message.
	     * @param {boolean} error Error message (false by default).
	     */

	  }, {
	    key: "setMessage",
	    value: function setMessage(message, error) {
	      if (!this.idDomainMessage) {
	        return;
	      }

	      error = !!error;
	      this.clearMessage();

	      if (message) {
	        if (this.idDomainNameParent) {
	          main_core.Dom.addClass(this.idDomainNameParent, error ? this.classes.dangerBorder : this.classes.successBorder);
	        }

	        main_core.Dom.addClass(this.idDomainMessage, error ? this.classes.dangerAlert : this.classes.successAlert);
	        main_core.Dom.show(this.idDomainMessage);
	        this.idDomainMessage.innerHTML = message;
	      }
	    }
	    /**
	     * Clears message alert.
	     */

	  }, {
	    key: "clearMessage",
	    value: function clearMessage() {
	      if (!this.idDomainMessage) {
	        return;
	      }

	      if (this.idDomainNameParent) {
	        main_core.Dom.removeClass(this.idDomainNameParent, this.classes.dangerBorder);
	        main_core.Dom.removeClass(this.idDomainNameParent, this.classes.successBorder);
	      }

	      main_core.Dom.removeClass(this.idDomainMessage, this.classes.dangerAlert);
	      main_core.Dom.removeClass(this.idDomainMessage, this.classes.successAlert);
	      this.idDomainMessage.innerHTML = '';
	    }
	  }]);
	  return Helper;
	}();
	babelHelpers.defineProperty(Helper, "DEFAULT_LENGTH_LIMIT", 63);

	var Input = /*#__PURE__*/function () {
	  /**
	   * Constructor.
	   */
	  function Input(params) {
	    var _this = this;

	    babelHelpers.classCallCheck(this, Input);
	    this.domainId = params.domainId;
	    this.domainName = params.domainName;
	    this.domainPostfix = params.domainPostfix || '';
	    this.idDomainName = params.idDomainName;
	    this.idDomainINA = params.idDomainINA;
	    this.idDomainDnsInfo = params.idDomainDnsInfo;
	    this.idDomainSubmit = params.idDomainSubmit;
	    this.previousDomainName = null;
	    this.helper = new Helper(params);
	    this.tld = params.tld ? params.tld.toLowerCase() : 'tld';
	    this.classes = {
	      submit: 'ui-btn-clock'
	    };
	    this.keyupCallback = this.keyupCallback.bind(this);

	    if (this.idDomainName) {
	      main_core.Event.bind(this.idDomainName, 'keyup', main_core.Runtime.debounce(this.keyupCallback, 900));
	      var initValue = main_core.Type.isString(this.idDomainName.value) ? this.idDomainName.value.trim() : '';

	      if (initValue.length === 0) {
	        this.helper.setLength(0);
	      } else {
	        this.keyupCallback();
	      }
	    }

	    if (this.idDomainSubmit) {
	      main_core.Event.bind(this.idDomainSubmit, 'click', function (event) {
	        _this.checkSubmit(event);
	      });
	    }

	    this.fillDnsInstruction(this.domainName);
	  }
	  /**
	   * Returns true if domain name is empty.
	   * return {bool}
	   */


	  babelHelpers.createClass(Input, [{
	    key: "domainNameIsEmpty",
	    value: function domainNameIsEmpty() {
	      this.idDomainName.value = main_core.Type.isString(this.idDomainName.value) ? this.idDomainName.value.trim() : this.idDomainName.value;
	      return this.idDomainName.value === '';
	    }
	    /**
	     * Makes some check before submit.
	     */

	  }, {
	    key: "checkSubmit",
	    value: function checkSubmit(event) {
	      if (main_core.Dom.hasClass(this.idDomainSubmit, this.classes.submit)) {
	        event.preventDefault();
	      } else if (this.domainNameIsEmpty()) {
	        this.helper.setError(main_core.Loc.getMessage('LANDING_TPL_ERROR_DOMAIN_EMPTY'));
	        event.preventDefault();
	      } else if (this.helper.isErrorShowed()) {
	        event.preventDefault();
	      } else {
	        main_core.Dom.addClass(this.idDomainSubmit, this.classes.submit);
	      }
	    }
	    /**
	     * Handler on keyup input.
	     */

	  }, {
	    key: "keyupCallback",
	    value: function keyupCallback() {
	      this.idDomainName.value = main_core.Type.isString(this.idDomainName.value) ? this.idDomainName.value.trim() : this.idDomainName.value;

	      if (this.idDomainName.value === '') {
	        this.helper.setError(main_core.Loc.getMessage('LANDING_TPL_ERROR_DOMAIN_EMPTY'));
	        this.helper.setLength(0);
	        return;
	      }

	      var domainName = this.idDomainName.value;

	      if (this.previousDomainName === domainName) {
	        return;
	      }

	      this.previousDomainName = domainName;
	      this.helper.showLoader();
	      BX.ajax({
	        url: '/bitrix/tools/landing/ajax.php?action=Domain::check',
	        method: 'POST',
	        data: {
	          data: {
	            domain: domainName + this.domainPostfix,
	            filter: this.domainId ? {
	              '!ID': this.domainId
	            } : {}
	          },
	          sessid: main_core.Loc.getMessage('bitrix_sessid')
	        },
	        dataType: 'json',
	        onsuccess: function (data) {
	          this.helper.hideLoader();

	          if (data.type === 'success') {
	            if (data.result.length && data.result.length.length && data.result.length.limit) {
	              this.helper.setLength(data.result.length.length, data.result.length.limit);
	            } else {
	              this.helper.hideLength();
	            }

	            if (!data.result.available) {
	              if (data.result.errors) {
	                if (data.result.errors.wrongSymbols) {
	                  this.helper.setError(main_core.Loc.getMessage('LANDING_TPL_ERROR_DOMAIN_WRONG_NAME'));
	                } else if (data.result.errors.wrongLength) {
	                  this.helper.setError(main_core.Loc.getMessage('LANDING_TPL_ERROR_DOMAIN_WRONG_LENGTH'));
	                } else if (data.result.errors.wrongSymbolCombination) {
	                  this.helper.setError(main_core.Loc.getMessage('LANDING_TPL_ERROR_DOMAIN_WRONG_SYMBOL_COMBINATIONS'));
	                } else if (data.result.errors.wrongDomainLevel) {
	                  this.helper.setError(main_core.Loc.getMessage('LANDING_TPL_ERROR_DOMAIN_WRONG_DOMAIN_LEVEL'));
	                }
	              } else {
	                this.helper.setError(!!data.result.deleted ? main_core.Loc.getMessage('LANDING_TPL_ERROR_DOMAIN_EXIST_DELETED') : main_core.Loc.getMessage('LANDING_TPL_ERROR_DOMAIN_EXIST'));
	              }
	            } else if (!data.result.domain) {
	              this.helper.setError(main_core.Loc.getMessage('LANDING_TPL_ERROR_DOMAIN_INCORRECT'));
	            } else {
	              this.fillDnsInstruction(data.result.domain);
	              this.helper.setSuccess(main_core.Loc.getMessage('LANDING_TPL_DOMAIN_AVAILABLE'));
	            }

	            if (data.result.dns && this.idDomainINA) {
	              this.idDomainINA.textContent = data.result.dns['INA'];
	            }
	          } else {
	            this.helper.setError('Error processing');
	          }
	        }.bind(this)
	      });
	    }
	    /**
	     * Sets new DNS instructions after domain name change.
	     * @param {string} domainName Domain name.
	     */

	  }, {
	    key: "fillDnsInstruction",
	    value: function fillDnsInstruction(domainName) {
	      if (!this.idDomainDnsInfo) {
	        return;
	      }

	      if (!domainName) {
	        return;
	      }

	      if (!this.idDomainDnsInfo.rows[1]) {
	        return;
	      }

	      if (!this.idDomainDnsInfo.rows[2]) {
	        return;
	      }

	      if (this.idDomainDnsInfo.rows[1].cells.length < 3 || this.idDomainDnsInfo.rows[2].cells.length < 3) {
	        return;
	      }

	      var cNameRecordRow = this.idDomainDnsInfo.rows[1];
	      var aRecordRow = this.idDomainDnsInfo.rows[2];
	      var domainParts = domainName.split('.');
	      var domainRe = /^(com|net|org|co|kiev|spb|kharkov|msk|in|app)\.[a-z]{2}$/;
	      aRecordRow.style.display = 'none';
	      cNameRecordRow.cells[0].textContent = domainName ? domainName : 'landing.mydomain';

	      if (domainParts.length === 2 || domainParts.length === 3 && domainParts[0] === 'www' || domainParts.length === 3 && (domainParts[1] + '.' + domainParts[2]).match(domainRe)) {
	        aRecordRow.style.display = 'table-row';

	        if (domainParts.length === 3 && domainParts[0] === 'www') {
	          aRecordRow.cells[0].textContent = domainParts[1] + '.' + domainParts[2] + '.';
	        } else {
	          cNameRecordRow.cells[0].textContent = 'www.' + domainName + '.';
	          aRecordRow.cells[0].textContent = domainName + '.';
	        }
	      }
	    }
	  }]);
	  return Input;
	}();

	var Private = /*#__PURE__*/function (_Input) {
	  babelHelpers.inherits(Private, _Input);

	  /**
	   * Constructor.
	   */
	  function Private(params) {
	    babelHelpers.classCallCheck(this, Private);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Private).call(this, params));
	  }

	  return Private;
	}(Input);

	var Bitrix24 = /*#__PURE__*/function (_Input) {
	  babelHelpers.inherits(Bitrix24, _Input);

	  /**
	   * Constructor.
	   */
	  function Bitrix24(params) {
	    babelHelpers.classCallCheck(this, Bitrix24);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Bitrix24).call(this, params));
	  }

	  return Bitrix24;
	}(Input);

	var Free = /*#__PURE__*/function () {
	  /**
	   * Constructor.
	   */
	  function Free(params) {
	    babelHelpers.classCallCheck(this, Free);
	    this.idDomainSubmit = params.idDomainSubmit;
	    this.idDomainCheck = params.idDomainCheck;
	    this.idDomainName = params.idDomainName;
	    this.idDomainAnother = params.idDomainAnother;
	    this.idDomainAnotherMore = params.idDomainAnotherMore;
	    this.idDomainErrorAlert = params.idDomainErrorAlert;
	    this.saveBlocker = params.saveBlocker;
	    this.saveBlockerCallback = params.saveBlockerCallback;
	    this.promoCloseIcon = params.promoCloseIcon;
	    this.promoCloseLink = params.promoCloseLink;
	    this.promoBlock = params.promoBlock;
	    this.maxVisibleSuggested = parseInt(params.maxVisibleSuggested || 10);
	    this.tld = params.tld ? params.tld.toLowerCase() : 'tld';
	    this.helper = new Helper(params);
	    this.classes = {
	      submit: 'ui-btn-clock'
	    };

	    if (this.promoCloseIcon && this.promoCloseLink) {
	      main_core.Event.bind(this.promoCloseIcon, 'click', this.closePromoBlock.bind(this));
	      main_core.Event.bind(this.promoCloseLink, 'click', this.closePromoBlock.bind(this));
	    }

	    if (this.idDomainAnotherMore) {
	      main_core.Event.bind(this.idDomainAnotherMore, 'click', this.showMoreDomains.bind(this));
	    }

	    if (this.idDomainSubmit) {
	      main_core.Event.bind(this.idDomainSubmit, 'click', function (event) {
	        this.checkSubmit(event);
	      }.bind(this));
	    }

	    if (this.idDomainCheck && this.idDomainName) {
	      main_core.Event.bind(this.idDomainCheck, 'click', function (event) {
	        this.checkDomain(event);
	      }.bind(this));
	    }

	    if (this.idDomainName) {
	      main_core.Event.bind(this.idDomainName, 'keyup', main_core.Runtime.debounce(function (event) {
	        this.keyupCallback(event);
	      }.bind(this), 500, this));
	    }
	  }
	  /**
	   * Handler on keyup input.
	   */


	  babelHelpers.createClass(Free, [{
	    key: "keyupCallback",
	    value: function keyupCallback() {
	      if (this.idDomainName.value === '') {
	        this.helper.setError(main_core.Loc.getMessage('LANDING_TPL_ERROR_DOMAIN_EMPTY'));
	        return;
	      }

	      this.helper.setSuccess('');
	    }
	    /**
	     * Closes promo banner.
	     */

	  }, {
	    key: "closePromoBlock",
	    value: function closePromoBlock() {
	      this.promoBlock.remove();
	    }
	    /**
	     * Shows full block of suggesiotn domains.
	     */

	  }, {
	    key: "showMoreDomains",
	    value: function showMoreDomains() {
	      this.idDomainAnother.style.height = this.idDomainAnother.children[0].offsetHeight + 'px';
	      this.idDomainAnotherMore.classList.add('landing-domain-block-available-btn-hide');
	    }
	    /**
	     * Makes some check before submit.
	     */

	  }, {
	    key: "checkSubmit",
	    value: function checkSubmit(event) {
	      if (main_core.Dom.hasClass(this.idDomainSubmit, this.classes.submit)) {
	        event.preventDefault();
	        return;
	      }

	      this.checkDomainName();

	      if (this.helper.isErrorShowed()) {
	        event.preventDefault();
	      } else if (this.saveBlocker && this.saveBlockerCallback) {
	        this.saveBlockerCallback();
	        event.preventDefault();
	      } else {
	        main_core.Dom.addClass(this.idDomainSubmit, this.classes.submit);
	      }
	    }
	    /**
	     * Sets suggested domain to the main input.
	     * @param {string} domainName Domain name.
	     */

	  }, {
	    key: "selectSuggested",
	    value: function selectSuggested(domainName) {
	      this.idDomainName.value = domainName;
	      this.helper.setSuccess(main_core.Loc.getMessage('LANDING_TPL_DOMAIN_AVAILABLE'));
	    }
	    /**
	     * Fill suggested domain area.
	     * @param {array} suggest Suggested domains.
	     */

	  }, {
	    key: "fillSuggest",
	    value: function fillSuggest(suggest) {
	      var _this = this;

	      if (!this.idDomainAnother) {
	        return;
	      }

	      if (this.idDomainAnotherMore) {
	        if (suggest.length > this.maxVisibleSuggested) {
	          main_core.Dom.show(this.idDomainAnotherMore);
	          this.idDomainAnotherMore.classList.remove('landing-domain-block-available-btn-hide');
	        } else {
	          main_core.Dom.hide(this.idDomainAnotherMore);
	        }
	      }

	      if (suggest.length) {
	        main_core.Dom.show(this.idDomainAnother.parentNode);
	      } else {
	        main_core.Dom.hide(this.idDomainAnother.parentNode);
	      }

	      var children = [];

	      for (var i = 0, c = suggest.length; i < c; i++) {
	        children.push(main_core.Dom.create('div', {
	          props: {
	            className: 'landing-domain-block-available-item'
	          },
	          children: [main_core.Dom.create('input', {
	            props: {
	              className: ''
	            },
	            attrs: {
	              name: 'domain-edit-suggest',
	              id: 'domain-edit-suggest-' + i,
	              type: 'radio'
	            },
	            events: {
	              click: function click(i) {
	                _this.selectSuggested(suggest[i]);
	              }
	            }
	          }), main_core.Dom.create('label', {
	            props: {
	              className: 'landing-domain-block-available-label'
	            },
	            attrs: {
	              "for": 'domain-edit-suggest-' + i
	            },
	            text: suggest[i]
	          })]
	        }));
	      }

	      this.idDomainAnother.innerHTML = '';
	      this.idDomainAnother.appendChild(main_core.Dom.create('div', {
	        props: {
	          className: 'landing-domain-block-available-list'
	        },
	        children: children
	      }));

	      if (this.idDomainAnotherMore.style.display === 'none') {
	        this.idDomainAnother.style.height = this.idDomainAnother.children[0].offsetHeight + 'px';
	      } else {
	        this.idDomainAnother.style.height = 80 + 'px';
	      }
	    }
	    /**
	     * Checks that domain name is correct.
	     */

	  }, {
	    key: "checkDomainName",
	    value: function checkDomainName() {
	      this.idDomainName.value = main_core.Type.isString(this.idDomainName.value) ? this.idDomainName.value.trim() : this.idDomainName.value;
	      var domainRe = RegExp('^[a-z0-9-]+\.' + this.tld + '$');

	      if (this.idDomainName.value === '') {
	        this.helper.setError(main_core.Loc.getMessage('LANDING_TPL_ERROR_DOMAIN_EMPTY'));
	      } else if (!domainRe.test(this.idDomainName.value.toLowerCase())) {
	        this.helper.setError(main_core.Loc.getMessage('LANDING_TPL_ERROR_DOMAIN_CHECK'));
	      } else if (this.idDomainName.value.indexOf('--') !== -1 || this.idDomainName.value.indexOf('-.') !== -1 || this.idDomainName.value.indexOf('-') === 0) {
	        this.helper.setError(main_core.Loc.getMessage('LANDING_TPL_ERROR_DOMAIN_CHECK_DASH'));
	      }
	    }
	    /**
	     * Makes whois query for user pointed domain.
	     */

	  }, {
	    key: "checkDomain",
	    value: function checkDomain(event) {
	      event.preventDefault();

	      if (this.helper.isLoaderShowed()) {
	        return;
	      }

	      this.checkDomainName();

	      if (this.helper.isErrorShowed()) {
	        return;
	      }

	      this.helper.showLoader();
	      this.fillSuggest([]);
	      BX.ajax({
	        url: '/bitrix/tools/landing/ajax.php?action=Domain::whois',
	        method: 'POST',
	        data: {
	          data: {
	            domainName: this.idDomainName.value,
	            tld: this.tld
	          },
	          sessid: main_core.Loc.getMessage('bitrix_sessid')
	        },
	        dataType: 'json',
	        onsuccess: function (data) {
	          this.helper.hideLoader();

	          if (data.type === 'success') {
	            var result = data.result;

	            if (!result.enable) {
	              if (result.suggest) {
	                this.fillSuggest(result.suggest);
	              }

	              this.helper.setError(main_core.Loc.getMessage('LANDING_TPL_ERROR_DOMAIN_EXIST'));
	            } else {
	              this.helper.setSuccess(main_core.Loc.getMessage('LANDING_TPL_DOMAIN_AVAILABLE'));
	            }
	          }
	        }.bind(this)
	      });
	    }
	  }]);
	  return Free;
	}();

	// export Helper from './js/landing.site_domain.helper';

	exports.Helper = Helper;
	exports.Input = Input;
	exports.Private = Private;
	exports.Bitrix24 = Bitrix24;
	exports.Free = Free;

}((this.BX.Landing.SiteDomain = this.BX.Landing.SiteDomain || {}),BX));
//# sourceMappingURL=script.js.map
