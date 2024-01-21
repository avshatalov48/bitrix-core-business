/* eslint-disable */
this.BX = this.BX || {};
this.BX.Seo = this.BX.Seo || {};
(function (exports,main_core,ui_vue) {
	'use strict';

	var Login = /*#__PURE__*/function () {
	  function Login() {
	    var _options$provider;
	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {
	      provider: {
	        TYPE: null,
	        AUTH_URL: null
	      }
	    };
	    babelHelpers.classCallCheck(this, Login);
	    this.provider = (_options$provider = options.provider) !== null && _options$provider !== void 0 ? _options$provider : null;
	  }
	  babelHelpers.createClass(Login, [{
	    key: "login",
	    value: function login() {
	      if (this.provider && main_core.Type.isString(this.provider['AUTH_URL'])) {
	        if (this.provider['TYPE'] && this.provider['TYPE'] === 'vkads') {
	          BX.util.popup(this.provider.AUTH_URL, 1200, 600);
	        } else {
	          BX.util.popup(this.provider.AUTH_URL, 800, 600);
	        }
	      }
	    }
	  }]);
	  return Login;
	}();

	var FacebookLoginComponent = ui_vue.Vue.extend({
	  props: {
	    defaultSetup: {
	      type: Object,
	      required: true
	    },
	    defaultConfig: {
	      type: Object,
	      required: true
	    }
	  },
	  data: function data() {
	    return {
	      config: {
	        business: {
	          name: null
	        },
	        ig_cta: {
	          cta_button_text: "",
	          cta_button_url: null
	        },
	        messenger_chat: {
	          domains: [window.location.protocol + '//' + (window.location.host || window.location.hostname)]
	        },
	        messenger_menu: {
	          cta_button_text: "",
	          cta_button_url: null
	        },
	        page_card: {
	          see_all_url: null
	        },
	        page_cta: {
	          cta_button_text: "",
	          cta_button_url: null
	        },
	        page_post: {
	          cta_button_text: "",
	          cta_button_url: null,
	          title: null
	        },
	        thread_intent: {
	          cta_button_url: null
	        }
	      },
	      setup: {
	        timezone: null,
	        currency: null,
	        business_vertical: null
	      },
	      values: {
	        timezone: [],
	        currency: []
	      },
	      available: {
	        business: true,
	        messenger_chat: true,
	        ig_cta: false,
	        messenger_menu: false,
	        page_cta: false,
	        page_post: false,
	        page_card: false,
	        thread_intent: false
	      },
	      checked: {
	        business: true,
	        messenger_chat: true,
	        ig_cta: false,
	        page_cta: false,
	        messenger_menu: false,
	        page_post: false,
	        page_card: false,
	        thread_intent: false
	      }
	    };
	  },
	  created: function created() {
	    for (var _i = 0, _Object$entries = Object.entries(this.setup); _i < _Object$entries.length; _i++) {
	      var _Object$entries$_i = babelHelpers.slicedToArray(_Object$entries[_i], 2),
	        field = _Object$entries$_i[0],
	        value = _Object$entries$_i[1];
	      if (this.defaultSetup[field] && this.defaultSetup[field].value) {
	        this.setup[field] = this.defaultSetup[field].value;
	      }
	      if (this.defaultSetup[field] && this.defaultSetup[field].set) {
	        this.values[field] = this.defaultSetup[field].set;
	      }
	    }
	    for (var _i2 = 0, _Object$entries2 = Object.entries(this.config); _i2 < _Object$entries2.length; _i2++) {
	      var _Object$entries2$_i = babelHelpers.slicedToArray(_Object$entries2[_i2], 2),
	        _field = _Object$entries2$_i[0],
	        _value = _Object$entries2$_i[1];
	      if (this.defaultConfig[_field] && this.defaultConfig[_field].value) {
	        this.checked[_field] = !!this.defaultConfig[_field].value;
	        this.config[_field] = this.defaultConfig[_field].value;
	      }
	      this.available[_field] = !!this.defaultConfig[_field];
	    }
	  },
	  methods: {
	    getSetup: function getSetup() {
	      return this.setup;
	    },
	    getConfig: function getConfig() {
	      var _this = this;
	      return Object.entries(this.checked).reduce(function (result, _ref) {
	        var _ref2 = babelHelpers.slicedToArray(_ref, 2),
	          field = _ref2[0],
	          value = _ref2[1];
	        if (value && _this.availableProps[field]) {
	          result[field] = _this.config[field];
	        }
	        return result;
	      }, {});
	    },
	    addDomain: function addDomain() {
	      this.config.messenger_chat.domains.push(null);
	    },
	    removeDomain: function removeDomain(index) {
	      this.config.messenger_chat.domains.splice(index, 1);
	    },
	    openInfoHelp: function openInfoHelp() {
	      top.BX.Helper.show('redirect=detail&code=13097346');
	    },
	    checkUrl: function checkUrl(url) {
	      if (main_core.Type.isString(url)) {
	        return url.search(/^((https:\/\/)|(www\.)|(http:\/\/))([a-z0-9-].?)+(:[0-9]+)?(\/.*)?$/i) === 0;
	      }
	      return false;
	    },
	    checkDomain: function checkDomain(domain) {
	      if (main_core.Type.isString(domain)) {
	        return domain.search(/^((https:\/\/)|(http:\/\/)){1}[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,6}$/i) === 0;
	      }
	      return false;
	    },
	    getSetupPropertiesStatus: function getSetupPropertiesStatus() {
	      var _this$getSetup,
	        _this2 = this;
	      return Object.entries((_this$getSetup = this.getSetup()) !== null && _this$getSetup !== void 0 ? _this$getSetup : {}).reduce(function (result, _ref3) {
	        var _ref4 = babelHelpers.slicedToArray(_ref3, 2),
	          key = _ref4[0],
	          value = _ref4[1];
	        if (main_core.Type.isString(value) && value.length > 0) {
	          switch (key) {
	            case 'timezone':
	              result[key] = _this2.values.timezone.includes(value);
	              break;
	            case 'currency':
	              result[key] = _this2.values.currency.includes(value);
	              break;
	            case 'business_vertical':
	              result[key] = ['ECOMMERCE', 'SERVICES'].includes(value);
	              break;
	          }
	        }
	        return result;
	      }, {});
	    },
	    getConfigPropertiesStatus: function getConfigPropertiesStatus() {
	      var _this$getConfig,
	        _this3 = this;
	      return Object.entries((_this$getConfig = this.getConfig()) !== null && _this$getConfig !== void 0 ? _this$getConfig : {}).reduce(function (result, _ref5) {
	        var _ref6 = babelHelpers.slicedToArray(_ref5, 2),
	          key = _ref6[0],
	          value = _ref6[1];
	        result[key] = Object.entries(value).reduce(function (propertyResult, _ref7) {
	          var _ref8 = babelHelpers.slicedToArray(_ref7, 2),
	            propertyKey = _ref8[0],
	            propertyValue = _ref8[1];
	          if (!['cta_button_text', 'see_all_url', 'cta_button_url', 'title', 'name', 'domains'].includes(propertyKey)) {
	            return propertyResult;
	          }
	          switch (propertyKey) {
	            case 'cta_button_text':
	              return propertyResult = propertyResult && main_core.Type.isString(propertyValue) && propertyValue.length > 0 && ['Reserve', 'Book Now', 'Buy Now', 'Book'].includes(propertyValue);
	            case 'see_all_url':
	            case 'cta_button_url':
	              return propertyResult = propertyResult && main_core.Type.isString(propertyValue) && propertyValue.length > 0 && _this3.checkUrl(propertyValue);
	            case 'title':
	            case 'name':
	              return propertyResult = propertyResult && main_core.Type.isString(propertyValue) && propertyValue.length > 0;
	            case 'domains':
	              return propertyResult = propertyResult && main_core.Type.isArray(propertyValue) && propertyValue.length > 0 && propertyValue.reduce(function (value, domain) {
	                return value && _this3.checkDomain(domain);
	              }, true);
	          }
	          return propertyResult;
	        }, true);
	        return result;
	      }, {});
	    },
	    getPropertiesStatus: function getPropertiesStatus() {
	      return Object.assign({}, this.getSetupPropertiesStatus(), this.getConfigPropertiesStatus());
	    },
	    alert: function alert(title, content, callback) {
	      BX.UI.Dialogs.MessageBox.alert(content, title, callback);
	      return this;
	    },
	    focusOnWrongProperty: function focusOnWrongProperty() {
	      for (var _i3 = 0, _Object$entries3 = Object.entries(this.getPropertiesStatus()); _i3 < _Object$entries3.length; _i3++) {
	        var _Object$entries3$_i = babelHelpers.slicedToArray(_Object$entries3[_i3], 2),
	          key = _Object$entries3$_i[0],
	          value = _Object$entries3$_i[1];
	        if (!value && this.$refs[key]) {
	          this.$refs[key].scrollIntoView();
	        }
	      }
	      return this;
	    },
	    validate: function validate() {
	      return Object.entries(this.getPropertiesStatus()).reduce(function (result, _ref9) {
	        var _ref10 = babelHelpers.slicedToArray(_ref9, 2),
	          key = _ref10[0],
	          value = _ref10[1];
	        return result && value;
	      }, true);
	    }
	  },
	  computed: {
	    localize: function localize() {
	      return ui_vue.Vue.getFilteredPhrases('SEO_ADS_FACEBOOK_BUSINESS_');
	    },
	    availableProps: function availableProps() {
	      return {
	        business: this.available.business,
	        messenger_chat: this.available.messenger_chat,
	        ig_cta: this.available.ig_cta,
	        page_cta: this.available.page_cta,
	        page_post: this.available.page_post,
	        messenger_menu: this.available.messenger_menu && this.checked.messenger_chat,
	        page_card: this.available.page_card && this.setup.business_vertical === 'SERVICES',
	        thread_intent: this.available.thread_intent && this.checked.messenger_chat
	      };
	    }
	  },
	  template: "\n\t\t<div class=\"seo-ads-login\">\n\t\t\t\t<div class=\"ui-slider-section\">\n\t\t\t\t\t<div class=\"ui-slider-content-box\">\n\t\t\t\t\t\t<div class=\"ui-slider-heading-4\">\n\t\t\t\t\t\t\t{{localize.SEO_ADS_FACEBOOK_BUSINESS_SETUP_FIELDS_TITLE}}\n\t\t\t\t\t\t\t<span class=\"seo-ads-login-hint\"\n\t\t\t\t\t\t\t\t@click=\"openInfoHelp()\"\n\t\t\t\t\t\t\t><span class=\"seo-ads-login-hint-icon\"></span></span>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<div class=\"ui-form\">\n\t\t\t\t\t\t\t<div ref=\"business\" class=\"ui-form-row\">\n\t\t\t\t\t\t\t\t<div class=\"ui-form-label\">\n\t\t\t\t\t\t\t\t\t<div class=\"ui-ctl-label-text\">{{localize.SEO_ADS_FACEBOOK_BUSINESS_LOGIN_NAME}}</div>\n\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t<div class=\"ui-form-content\">\n\t\t\t\t\t\t\t\t\t<div class=\"ui-ctl ui-ctl-textbox ui-ctl-w100\" :class=\"{'ui-ctl-danger': !config.business.name}\">\n\t\t\t\t\t\t\t\t\t\t<input \n\t\t\t\t\t\t\t\t\t\ttype =\"text\" \n\t\t\t\t\t\t\t\t\t\tclass=\"ui-ctl-element\" \n\t\t\t\t\t\t\t\t\t\tv-model=\"config.business.name\">\n\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t<div ref=\"business_vertical\" class=\"ui-form-row\">\n\t\t\t\t\t\t\t\t<div class=\"ui-form-label\">\n\t\t\t\t\t\t\t\t\t<div class=\"ui-ctl-label-text\">\n\t\t\t\t\t\t\t\t\t\t{{localize.SEO_ADS_FACEBOOK_BUSINESS_LOGIN_TYPE}}\n\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t<div class=\"ui-form-content\">\n\t\t\t\t\t\t\t\t\t<div class=\"ui-ctl ui-ctl-after-icon ui-ctl-dropdown ui-ctl-w100\" :class=\"{'ui-ctl-danger': !setup.business_vertical}\">\n\t\t\t\t\t\t\t\t\t\t<div class=\"ui-ctl-after ui-ctl-icon-angle\"></div>\n\t\t\t\t\t\t\t\t\t\t\t<select class=\"ui-ctl-element\" v-model=\"setup.business_vertical\">\n\t\t\t\t\t\t\t\t\t\t\t\t<option value=\"ECOMMERCE\">\n\t\t\t\t\t\t\t\t\t\t\t\t\t{{localize.SEO_ADS_FACEBOOK_BUSINESS_LOGIN_ECOMMERCE}}\n\t\t\t\t\t\t\t\t\t\t\t\t</option>\n\t\t\t\t\t\t\t\t\t\t\t\t<option value=\"SERVICES\">\n\t\t\t\t\t\t\t\t\t\t\t\t\t{{localize.SEO_ADS_FACEBOOK_BUSINESS_LOGIN_SERVICES}}\n\t\t\t\t\t\t\t\t\t\t\t\t</option>\n\t\t\t\t\t\t\t\t\t\t\t</select>\n\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t<div ref=\"timezone\" class=\"ui-form-row\">\n\t\t\t\t\t\t\t\t<div class=\"ui-form-label\">\n\t\t\t\t\t\t\t\t\t<div class=\"ui-ctl-label-text\">\n\t\t\t\t\t\t\t\t\t\t{{ localize.SEO_ADS_FACEBOOK_BUSINESS_LOGIN_TIMEZONE }}\n\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t<div class=\"ui-form-content\">\n\t\t\t\t\t\t\t\t\t<div class=\"ui-ctl ui-ctl-after-icon ui-ctl-dropdown ui-ctl-w100\" :class=\"{'ui-ctl-danger': !setup.timezone}\">\n\t\t\t\t\t\t\t\t\t\t<div class=\"ui-ctl-after ui-ctl-icon-angle\"></div>\n\t\t\t\t\t\t\t\t\t\t<select class=\"ui-ctl-element\" v-model=\"setup.timezone\">\n\t\t\t\t\t\t\t\t\t\t\t<option v-for=\"timezone in values.timezone\" :value=\"timezone\">{{ timezone }}</option>\n\t\t\t\t\t\t\t\t\t\t</select>\n\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t<div ref=\"currency\" class=\"ui-form-row\">\n\t\t\t\t\t\t\t\t<div class=\"ui-form-label\">\n\t\t\t\t\t\t\t\t\t<div class=\"ui-ctl-label-text\">\n\t\t\t\t\t\t\t\t\t\t{{localize.SEO_ADS_FACEBOOK_BUSINESS_LOGIN_CURRENCY}}\n\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t<div class=\"ui-form-content\">\n\t\t\t\t\t\t\t\t\t<div class=\"ui-ctl ui-ctl-after-icon ui-ctl-dropdown ui-ctl-w100\" :class=\"{'ui-ctl-danger': !setup.currency}\">\n\t\t\t\t\t\t\t\t\t\t<div class=\"ui-ctl-after ui-ctl-icon-angle\"></div>\n\t\t\t\t\t\t\t\t\t\t<select class=\"ui-ctl-element\" v-model=\"setup.currency\">\n\t\t\t\t\t\t\t\t\t\t\t<option v-for=\"currency in values.currency\" :value=\"currency\">{{ currency }}</option>\n\t\t\t\t\t\t\t\t\t\t</select>\n\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"ui-slider-section\">\n\t\t\t\t\t<div class=\"ui-slider-content-box\">\n\t\t\t\t\t\t<div class=\"ui-slider-heading-4\">\n\t\t\t\t\t\t\t{{localize.SEO_ADS_FACEBOOK_BUSINESS_FEATURE_TITLE}}\n\t\t\t\t\t\t\t<span class=\"seo-ads-login-hint\"\n\t\t\t\t\t\t\t\t@click=\"openInfoHelp()\"\n\t\t\t\t\t\t\t><span class=\"seo-ads-login-hint-icon\"></span></span>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<div class=\"ui-form\">\n\t\t\t\t\t\t<div ref=\"ig_cta\" v-if=\"availableProps.ig_cta\" class=\"ui-form-row\">\n\t\t\t\t\t\t\t<div class=\"ui-form-label\">\n\t\t\t\t\t\t\t\t<label class=\"ui-ctl ui-ctl-checkbox\">\n\t\t\t\t\t\t\t\t\t<input type=\"checkbox\" class=\"ui-ctl-element\" v-model=\"checked.ig_cta\">\n\t\t\t\t\t\t\t\t\t<div class=\"ui-ctl-label-text\">\n\t\t\t\t\t\t\t\t\t\t{{localize.SEO_ADS_FACEBOOK_BUSINESS_LOGIN_IG_CTA}}\n\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t</label>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t<transition v-if=\"checked.ig_cta\">\n\t\t\t\t\t\t\t\t<div class=\"ui-form-content\">\n\t\t\t\t\t\t\t\t\t<div class=\"ui-form-row-group ui-form-row-inline\">\n\t\t\t\t\t\t\t\t\t\t<div class=\"ui-form-row\">\n\t\t\t\t\t\t\t\t\t\t\t<div class=\"ui-ctl ui-ctl-textbox ui-ctl-w100 ui-ctl-sm\" :class=\"{'ui-ctl-danger': checked.ig_cta && !checkUrl(config.ig_cta.cta_button_url)}\">\n\t\t\t\t\t\t\t\t\t\t\t\t<input \n\t\t\t\t\t\t\t\t\t\t\t\t\ttype=\"text\"\n\t\t\t\t\t\t\t\t\t\t\t\t\tclass=\"ui-ctl-element\"\n\t\t\t\t\t\t\t\t\t\t\t\t\t:placeholder=\"localize.SEO_ADS_FACEBOOK_BUSINESS_LOGIN_URL_PLACEHOLDER\"\n\t\t\t\t\t\t\t\t\t\t\t\t\tv-model=\"config.ig_cta.cta_button_url\"\n\t\t\t\t\t\t\t\t\t\t\t\t\t>\n\t\t\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t\t<div class=\"ui-form-row\">\n\t\t\t\t\t\t\t\t\t\t\t<div class=\"ui-ctl ui-ctl-after-icon ui-ctl-dropdown ui-ctl-w100 ui-ctl-sm\" :class=\"{'ui-ctl-danger': checked.ig_cta && !config.ig_cta.cta_button_text}\">\n\t\t\t\t\t\t\t\t\t\t\t\t<div class=\"ui-ctl-after ui-ctl-icon-angle\"></div>\n\t\t\t\t\t\t\t\t\t\t\t\t<select class=\"ui-ctl-element\" v-model=\"config.ig_cta.cta_button_text\">\n\t\t\t\t\t\t\t\t\t\t\t\t\t<option value=\"\" disabled selected>{{localize.SEO_ADS_FACEBOOK_BUSINESS_LOGIN_BUTTON_TEXT}}</option>\n\t\t\t\t\t\t\t\t\t\t\t\t\t<option value=\"Reserve\">{{localize.SEO_ADS_FACEBOOK_BUSINESS_LOGIN_RESERVE}}</option>\n\t\t\t\t\t\t\t\t\t\t\t\t\t<option value=\"Book Now\">{{localize.SEO_ADS_FACEBOOK_BUSINESS_LOGIN_BOOK}}</option>\n\t\t\t\t\t\t\t\t\t\t\t\t\t<option value=\"Buy Now\">{{localize.SEO_ADS_FACEBOOK_BUSINESS_LOGIN_BUY}}</option>\n\t\t\t\t\t\t\t\t\t\t\t\t</select>\n\t\t\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t</transition>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<div ref=\"page_cta\" v-if=\"availableProps.page_cta\" class=\"ui-form-row\" >\n\t\t\t\t\t\t\t<div class=\"ui-form-label\">\n\t\t\t\t\t\t\t\t<label class=\"ui-ctl ui-ctl-checkbox\">\n\t\t\t\t\t\t\t\t\t<input type=\"checkbox\" class=\"ui-ctl-element\" v-model=\"checked.page_cta\">\n\t\t\t\t\t\t\t\t\t<div class=\"ui-ctl-label-text\">\n\t\t\t\t\t\t\t\t\t\t{{localize.SEO_ADS_FACEBOOK_BUSINESS_LOGIN_PAGE_CTA}}\n\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t</label>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t<transition v-if=\"checked.page_cta\">\n\t\t\t\t\t\t\t\t<div class=\"ui-form-content\">\n\t\t\t\t\t\t\t\t\t<div \n\t\t\t\t\t\t\t\t\t\tclass=\"ui-form-row-group ui-form-row-inline\"  \n\t\t\t\t\t\t\t\t\t\t:class=\"{'ui-ctl-danger': checked.page_cta && !checkUrl(config.page_cta.cta_button_url)}\"\n\t\t\t\t\t\t\t\t\t>\n\t\t\t\t\t\t\t\t\t\t<div class=\"ui-form-row\">\n\t\t\t\t\t\t\t\t\t\t\t<div class=\"ui-ctl ui-ctl-textbox ui-ctl-w100 ui-ctl-sm\">\n\t\t\t\t\t\t\t\t\t\t\t\t<input \n\t\t\t\t\t\t\t\t\t\t\t\ttype=\"text\" \n\t\t\t\t\t\t\t\t\t\t\t\tclass=\"ui-ctl-element\"\n\t\t\t\t\t\t\t\t\t\t\t\t:placeholder=\"localize.SEO_ADS_FACEBOOK_BUSINESS_LOGIN_URL_PLACEHOLDER\"\n\t\t\t\t\t\t\t\t\t\t\t\tv-model=\"config.page_cta.cta_button_url\"\n\t\t\t\t\t\t\t\t\t\t\t\t>\n\t\t\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t\t<div class=\"ui-form-row\">\n\t\t\t\t\t\t\t\t\t\t\t<div \n\t\t\t\t\t\t\t\t\t\t\t\tclass=\"ui-ctl ui-ctl-after-icon ui-ctl-dropdown ui-ctl-w100 ui-ctl-sm\" \n\t\t\t\t\t\t\t\t\t\t\t\t:class=\"{'ui-ctl-danger': checked.page_cta && !config.page_cta.cta_button_text}\"\n\t\t\t\t\t\t\t\t\t\t\t>\n\t\t\t\t\t\t\t\t\t\t\t\t<div class=\"ui-ctl-after ui-ctl-icon-angle\"></div>\n\t\t\t\t\t\t\t\t\t\t\t\t<select class=\"ui-ctl-element\" v-model=\"config.page_cta.cta_button_text\">\n\t\t\t\t\t\t\t\t\t\t\t\t\t<option value=\"\" disabled selected>{{localize.SEO_ADS_FACEBOOK_BUSINESS_LOGIN_BUTTON_TEXT}}</option>\n\t\t\t\t\t\t\t\t\t\t\t\t\t<option value=\"Reserve\">{{localize.SEO_ADS_FACEBOOK_BUSINESS_LOGIN_RESERVE}}</option>\n\t\t\t\t\t\t\t\t\t\t\t\t\t<option value=\"Book Now\">{{localize.SEO_ADS_FACEBOOK_BUSINESS_LOGIN_BOOK}}</option>\n\t\t\t\t\t\t\t\t\t\t\t\t\t<option value=\"Buy Now\">{{localize.SEO_ADS_FACEBOOK_BUSINESS_LOGIN_BUY}}</option>\n\t\t\t\t\t\t\t\t\t\t\t\t</select>\n\t\t\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t</transition>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<div ref=\"page_post\" v-if=\"availableProps.page_post\" class=\"ui-form-row\">\n\t\t\t\t\t\t\t<div class=\"ui-form-label\">\n\t\t\t\t\t\t\t\t<label class=\"ui-ctl ui-ctl-checkbox\">\n\t\t\t\t\t\t\t\t\t<input type=\"checkbox\" class=\"ui-ctl-element\" v-model=\"checked.page_post\">\n\t\t\t\t\t\t\t\t\t<div class=\"ui-ctl-label-text\">\n\t\t\t\t\t\t\t\t\t\t{{localize.SEO_ADS_FACEBOOK_BUSINESS_LOGIN_PAGE_POST}}\n\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t</label>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t<transition v-if=\"checked.page_post\">\n\t\t\t\t\t\t\t\t<div class=\"ui-form-content\">\n\t\t\t\t\t\t\t\t\t<div class=\"ui-form-row-group\">\n\t\t\t\t\t\t\t\t\t\t<div class=\"ui-form-row-inline\">\n\t\t\t\t\t\t\t\t\t\t\t<div class=\"ui-form-row\">\n\t\t\t\t\t\t\t\t\t\t\t\t<div class=\"ui-ctl ui-ctl-textbox ui-ctl-w100 ui-ctl-sm\"  :class=\"{'ui-ctl-danger': checked.page_post && !checkUrl(config.page_post.cta_button_url) }\">\n\t\t\t\t\t\t\t\t\t\t\t\t\t<input \n\t\t\t\t\t\t\t\t\t\t\t\t\t\ttype=\"text\" \n\t\t\t\t\t\t\t\t\t\t\t\t\t\tclass=\"ui-ctl-element\"\n\t\t\t\t\t\t\t\t\t\t\t\t\t\t:placeholder=\"localize.SEO_ADS_FACEBOOK_BUSINESS_LOGIN_URL_PLACEHOLDER\"\n\t\t\t\t\t\t\t\t\t\t\t\t\t\tv-model=\"config.page_post.cta_button_url\" \n\t\t\t\t\t\t\t\t\t\t\t\t\t>\n\t\t\t\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t\t\t<div class=\"ui-form-row\">\n\t\t\t\t\t\t\t\t\t\t\t\t<div class=\"ui-ctl ui-ctl-after-icon ui-ctl-dropdown ui-ctl-w100 ui-ctl-sm\" :class=\"{'ui-ctl-danger': checked.page_post && !config.page_post.cta_button_text}\">\n\t\t\t\t\t\t\t\t\t\t\t\t\t<div class=\"ui-ctl-after ui-ctl-icon-angle\"></div>\n\t\t\t\t\t\t\t\t\t\t\t\t\t<select class=\"ui-ctl-element\" v-model=\"config.page_post.cta_button_text\">\n\t\t\t\t\t\t\t\t\t\t\t\t\t\t<option value=\"\" disabled selected>{{localize.SEO_ADS_FACEBOOK_BUSINESS_LOGIN_BUTTON_TEXT}}</option>\n\t\t\t\t\t\t\t\t\t\t\t\t\t\t<option value=\"Reserve\">{{localize.SEO_ADS_FACEBOOK_BUSINESS_LOGIN_RESERVE}}</option>\n\t\t\t\t\t\t\t\t\t\t\t\t\t\t<option value=\"Book Now\">{{localize.SEO_ADS_FACEBOOK_BUSINESS_LOGIN_BOOK}}</option>\n\t\t\t\t\t\t\t\t\t\t\t\t\t\t<option value=\"Buy Now\">{{localize.SEO_ADS_FACEBOOK_BUSINESS_LOGIN_BUY}}</option>\n\t\t\t\t\t\t\t\t\t\t\t\t\t</select>\n\t\t\t\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t\t<div class=\"ui-form-row\">\n\t\t\t\t\t\t\t\t\t\t\t<div class=\"ui-ctl ui-ctl-textbox ui-ctl-w100 ui-ctl-sm\" :class=\"{'ui-ctl-danger': checked.page_post && !config.page_post.title}\">\n\t\t\t\t\t\t\t\t\t\t\t\t<input\n\t\t\t\t\t\t\t\t\t\t\t\t\ttype=\"text\" \n\t\t\t\t\t\t\t\t\t\t\t\t\tclass=\"ui-ctl-element\"\n\t\t\t\t\t\t\t\t\t\t\t\t\t:placeholder=\"localize.SEO_ADS_FACEBOOK_BUSINESS_LOGIN_TITLE_PLACEHOLDER\"\n\t\t\t\t\t\t\t\t\t\t\t\t\tv-model=\"config.page_post.title\"\n\t\t\t\t\t\t\t\t\t\t\t\t>\n\t\t\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t</transition>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<transition v-if=\"availableProps.messenger_menu\">\n\t\t\t\t\t\t\t<div ref=\"messenger_menu\" class=\"ui-form-row\">\n\t\t\t\t\t\t\t\t<div class=\"ui-form-label\">\n\t\t\t\t\t\t\t\t\t<label class=\"ui-ctl ui-ctl-checkbox\">\n\t\t\t\t\t\t\t\t\t\t<input type=\"checkbox\" class=\"ui-ctl-element\" v-model=\"checked.messenger_menu\">\n\t\t\t\t\t\t\t\t\t\t<div class=\"ui-ctl-label-text\">\n\t\t\t\t\t\t\t\t\t\t\t{{ localize.SEO_ADS_FACEBOOK_BUSINESS_LOGIN_MESSENGER_MENU }}\n\t\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t</label>\n\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t<transition v-if=\"checked.messenger_menu\">\n\t\t\t\t\t\t\t\t\t<div class=\"ui-form-content\">\n\t\t\t\t\t\t\t\t\t\t<div \n\t\t\t\t\t\t\t\t\t\t\tclass=\"ui-form-row-group ui-form-row-inline \"\n\t\t\t\t\t\t\t\t\t\t\t:class=\"{'ui-ctl-danger': checked.messenger_menu && !checkUrl(config.messenger_menu.cta_button_url)}\"\n\t\t\t\t\t\t\t\t\t\t>\n\t\t\t\t\t\t\t\t\t\t\t<div class=\"ui-form-row\">\n\t\t\t\t\t\t\t\t\t\t\t\t<div class=\"ui-ctl ui-ctl-textbox ui-ctl-w100 ui-ctl-sm\">\n\t\t\t\t\t\t\t\t\t\t\t\t\t<input \n\t\t\t\t\t\t\t\t\t\t\t\t\ttype=\"text\" \n\t\t\t\t\t\t\t\t\t\t\t\t\tclass=\"ui-ctl-element\"\n\t\t\t\t\t\t\t\t\t\t\t\t\t:placeholder=\"localize.SEO_ADS_FACEBOOK_BUSINESS_LOGIN_URL_PLACEHOLDER\"\n\t\t\t\t\t\t\t\t\t\t\t\t\tv-model=\"config.messenger_menu.cta_button_url\"  \n\t\t\t\t\t\t\t\t\t\t\t\t\t>\n\t\t\t\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t\t\t<div class=\"ui-form-row\">\n\t\t\t\t\t\t\t\t\t\t\t\t<div class=\"ui-ctl ui-ctl-after-icon ui-ctl-dropdown ui-ctl-w100 ui-ctl-sm\" :class=\"{'ui-ctl-danger': checked.messenger_menu && !config.messenger_menu.cta_button_text}\">\n\t\t\t\t\t\t\t\t\t\t\t\t\t<div class=\"ui-ctl-after ui-ctl-icon-angle\"></div>\n\t\t\t\t\t\t\t\t\t\t\t\t\t<select class=\"ui-ctl-element\" v-model=\"config.messenger_menu.cta_button_text\" >\n\t\t\t\t\t\t\t\t\t\t\t\t\t\t<option value=\"\" disabled selected>{{localize.SEO_ADS_FACEBOOK_BUSINESS_LOGIN_BUTTON_TEXT}}</option>\n\t\t\t\t\t\t\t\t\t\t\t\t\t\t<option value=\"Reserve\">{{localize.SEO_ADS_FACEBOOK_BUSINESS_LOGIN_RESERVE}}</option>\n\t\t\t\t\t\t\t\t\t\t\t\t\t\t<option value=\"Book Now\">{{localize.SEO_ADS_FACEBOOK_BUSINESS_LOGIN_BOOK}}</option>\n\t\t\t\t\t\t\t\t\t\t\t\t\t\t<option value=\"Buy Now\">{{localize.SEO_ADS_FACEBOOK_BUSINESS_LOGIN_BUY}}</option>\n\t\t\t\t\t\t\t\t\t\t\t\t\t</select>\n\t\t\t\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t</transition>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</transition>\n\t\t\t\t\t\t<transition v-if=\"availableProps.thread_intent\">\n\t\t\t\t\t\t\t<div ref=\"thread_intent\" class=\"ui-form-row\">\n\t\t\t\t\t\t\t<div class=\"ui-form-label\">\n\t\t\t\t\t\t\t\t<label class=\"ui-ctl ui-ctl-checkbox\">\n\t\t\t\t\t\t\t\t\t<input type=\"checkbox\" class=\"ui-ctl-element\" v-model=\"checked.thread_intent\">\n\t\t\t\t\t\t\t\t\t<div class=\"ui-ctl-label-text\">\n\t\t\t\t\t\t\t\t\t\t{{localize.SEO_ADS_FACEBOOK_BUSINESS_LOGIN_THREAD_INTENT}}\n\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t</label>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t<transition v-if=\"checked.thread_intent\">\n\t\t\t\t\t\t\t\t<div class=\"ui-form-content\">\n\t\t\t\t\t\t\t\t\t<div class=\"ui-form-row-group\">\n\t\t\t\t\t\t\t\t\t\t<div class=\"ui-form-row\">\n\t\t\t\t\t\t\t\t\t\t\t<div class=\"ui-ctl ui-ctl-textbox ui-ctl-w100 ui-ctl-sm\" :class=\"{'ui-ctl-danger': checked.thread_intent && !checkUrl(config.thread_intent.cta_button_url)}\">\n\t\t\t\t\t\t\t\t\t\t\t\t<input \n\t\t\t\t\t\t\t\t\t\t\t\ttype=\"text\" \n\t\t\t\t\t\t\t\t\t\t\t\tclass=\"ui-ctl-element\"\n\t\t\t\t\t\t\t\t\t\t\t\t:placeholder=\"localize.SEO_ADS_FACEBOOK_BUSINESS_LOGIN_URL_PLACEHOLDER\"\n\t\t\t\t\t\t\t\t\t\t\t\tv-model=\"config.thread_intent.cta_button_url\" \n\t\t\t\t\t\t\t\t\t\t\t\t>\n\t\t\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t</transition>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</transition>\n\t\t\t\t\t\t<div ref=\"messenger_chat\" class=\"ui-form-row\">\n\t\t\t\t\t\t\t<div class=\"ui-form-label\">\n\t\t\t\t\t\t\t\t<label class=\"ui-ctl ui-ctl-checkbox\">\n\t\t\t\t\t\t\t\t\t<input type=\"checkbox\" class=\"ui-ctl-element\" v-model=\"checked.messenger_chat\">\n\t\t\t\t\t\t\t\t\t<div class=\"ui-ctl-label-text\">\n\t\t\t\t\t\t\t\t\t\t{{localize.SEO_ADS_FACEBOOK_BUSINESS_LOGIN_MESSENGER_CHAT}}\n\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t</label>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t<transition v-if=\"checked.messenger_chat\" name=\"hidden-row\">\n\t\t\t\t\t\t\t\t<div class=\"ui-form-content\">\n\t\t\t\t\t\t\t\t\t<div class=\"ui-form-row-group\">\n\t\t\t\t\t\t\t\t\t\t<div \n\t\t\t\t\t\t\t\t\t\t\tv-for=\"(domain,index) in config.messenger_chat.domains\" \n\t\t\t\t\t\t\t\t\t\t\tclass=\"ui-form-row\"\n\t\t\t\t\t\t\t\t\t\t\t>\n\t\t\t\t\t\t\t\t\t\t\t<div class=\"ui-ctl ui-ctl-after-icon ui-ctl-textbox ui-ctl-w100\" :class=\"{'ui-ctl-danger': !checkDomain(config.messenger_chat.domains[index])}\">\n\t\t\t\t\t\t\t\t\t\t\t\t<button class=\"ui-ctl-after ui-ctl-icon-clear\" @click=\"removeDomain(index)\">\n\t\t\t\t\t\t\t\t\t\t\t\t</button>\n\t\t\t\t\t\t\t\t\t\t\t\t<input type=\"text\" class=\"ui-ctl-element\"\n\t\t\t\t\t\t\t\t\t\t\t\t:placeholder=\"localize.SEO_ADS_FACEBOOK_BUSINESS_LOGIN_DOMAIN_PLACEHOLDER\"\n\t\t\t\t\t\t\t\t\t\t\t\tv-model=\"config.messenger_chat.domains[index]\"\n\t\t\t\t\t\t\t\t\t\t\t\t >\n\t\t\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t\t<div class=\"ui-form-row\">\n\t\t\t\t\t\t\t\t\t\t\t<button class=\"ui-btn ui-btn-light-border ui-btn-xs\" @click=\"addDomain\">\n\t\t\t\t\t\t\t\t\t\t\t\t{{localize.SEO_ADS_FACEBOOK_BUSINESS_LOGIN_MESSENGER_CHAT_ADD}}\n\t\t\t\t\t\t\t\t\t\t\t</button>\n\t\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t</transition>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<transition v-if=\"availableProps.page_card\">\n\t\t\t\t\t\t\t<div id=\"page_card\" class=\"ui-form-row\">\n\t\t\t\t\t\t\t\t<div class=\"ui-form-label\">\n\t\t\t\t\t\t\t\t\t<label class=\"ui-ctl ui-ctl-checkbox\">\n\t\t\t\t\t\t\t\t\t\t<input type=\"checkbox\" class=\"ui-ctl-element\" v-model=\"checked.page_card\">\n\t\t\t\t\t\t\t\t\t\t<div class=\"ui-ctl-label-text\">\n\t\t\t\t\t\t\t\t\t\t\t{{localize.SEO_ADS_FACEBOOK_BUSINESS_LOGIN_PAGE_CARD}}\n\t\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t</label>\n\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t<transition v-if=\"checked.page_card\">\n\t\t\t\t\t\t\t\t\t<div class=\"ui-form-content\">\n\t\t\t\t\t\t\t\t\t\t<div class=\"ui-form-row-group ui-form-row-inline\">\n\t\t\t\t\t\t\t\t\t\t\t<div class=\"ui-form-row\">\n\t\t\t\t\t\t\t\t\t\t\t\t<div class=\"ui-ctl ui-ctl-textbox ui-ctl-w100 ui-ctl-sm\" :class=\"{'ui-ctl-danger': checked.page_card && !checkUrl(config.page_card.see_all_url)}\">\n\t\t\t\t\t\t\t\t\t\t\t\t\t<input \n\t\t\t\t\t\t\t\t\t\t\t\t\ttype=\"text\" \n\t\t\t\t\t\t\t\t\t\t\t\t\tclass=\"ui-ctl-element\"\n\t\t\t\t\t\t\t\t\t\t\t\t\t:placeholder=\"localize.SEO_ADS_FACEBOOK_BUSINESS_LOGIN_URL_PLACEHOLDER\"\n\t\t\t\t\t\t\t\t\t\t\t\t\tv-model=\"config.page_card.see_all_url\"\n\t\t\t\t\t\t\t\t\t\t\t\t\t >\n\t\t\t\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t</transition>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</transition>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t</div>\n"
	});

	var FacebookLogin = /*#__PURE__*/function (_Login) {
	  babelHelpers.inherits(FacebookLogin, _Login);
	  function FacebookLogin() {
	    babelHelpers.classCallCheck(this, FacebookLogin);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(FacebookLogin).apply(this, arguments));
	  }
	  babelHelpers.createClass(FacebookLogin, [{
	    key: "login",
	    value: function login() {
	      var _this = this,
	        _BX$SidePanel$Instanc,
	        _BX$SidePanel$Instanc2;
	      BX.SidePanel.Instance.open('seo-fbe-install', {
	        contentCallback: function contentCallback(slider) {
	          return BX.UI.SidePanel.Layout.createContent({
	            title: main_core.Loc.getMessage('SEO_ADS_FACEBOOK_BUSINESS_LOGIN_TITLE_MSGVER_1'),
	            extensions: ['seo.ads.login', 'ui.forms'],
	            design: {
	              section: false
	            },
	            content: function content() {
	              return BX.ajax.runAction('seo.api.business.setup.default', {
	                data: {},
	                analyticsLabel: {
	                  connect: "FBE",
	                  action: "connection_configuration",
	                  type: "connection"
	                }
	              }).then(function (response) {
	                var _slider$getData$set;
	                return (_slider$getData$set = slider.getData().set('setup', response.data)) !== null && _slider$getData$set !== void 0 ? _slider$getData$set : true;
	              }).then(function () {
	                return BX.ajax.runAction('seo.api.business.config.default', {
	                  data: {}
	                });
	              }).then(function (response) {
	                var _slider$getData$set2;
	                return (_slider$getData$set2 = slider.getData().set('config', response.data)) !== null && _slider$getData$set2 !== void 0 ? _slider$getData$set2 : true;
	              }).then(function () {
	                slider.getData().set('COMPONENT_KEY', new FacebookLoginComponent({
	                  propsData: {
	                    defaultSetup: slider.getData().get('setup'),
	                    defaultConfig: slider.getData().get('config')
	                  }
	                }).$mount());
	                return slider.getData().get('COMPONENT_KEY').$el;
	              });
	            },
	            buttons: function buttons(_ref) {
	              var cancelButton = _ref.cancelButton,
	                SaveButton = _ref.SaveButton;
	              return [new SaveButton({
	                onclick: function onclick() {
	                  return _this.submit();
	                },
	                text: main_core.Loc.getMessage('SEO_ADS_FACEBOOK_BUSINESS_LOGIN_SUBMIT_BUTTON')
	              }), cancelButton];
	            }
	          });
	        },
	        title: main_core.Loc.getMessage('SEO_ADS_FACEBOOK_BUSINESS_LOGIN_TITLE_MSGVER_1'),
	        width: (_BX$SidePanel$Instanc = (_BX$SidePanel$Instanc2 = BX.SidePanel.Instance.getTopSlider()) === null || _BX$SidePanel$Instanc2 === void 0 ? void 0 : _BX$SidePanel$Instanc2.getWidth()) !== null && _BX$SidePanel$Instanc !== void 0 ? _BX$SidePanel$Instanc : 850,
	        cacheable: false
	      });
	    }
	  }, {
	    key: "reject",
	    value: function reject() {
	      var _BX$SidePanel$Instanc3;
	      (_BX$SidePanel$Instanc3 = BX.SidePanel.Instance.getSlider('seo-fbe-install')) === null || _BX$SidePanel$Instanc3 === void 0 ? void 0 : _BX$SidePanel$Instanc3.close();
	    }
	  }, {
	    key: "submit",
	    value: function submit() {
	      var _this2 = this;
	      var slider = BX.SidePanel.Instance.getSlider('seo-fbe-install');
	      if (slider && slider.getData().has('COMPONENT_KEY')) {
	        slider.close();
	        if (slider.getData().get('COMPONENT_KEY').validate()) {
	          this.servicePopup = BX.util.popup('', 800, 600);
	          BX.ajax.runAction('seo.api.business.extension.install', {
	            data: {
	              engineCode: this.provider.ENGINE_CODE,
	              setup: slider.getData().get('COMPONENT_KEY').getSetup(),
	              config: slider.getData().get('COMPONENT_KEY').getConfig()
	            },
	            analyticsLabel: {
	              connect: "FBE",
	              action: "connection_start",
	              type: "connection"
	            }
	          }).then(function (response) {
	            if (response && response.data && response.data.authUrl) {
	              _this2.servicePopup.location = response.data.authUrl;
	            }
	          }, function (response) {
	            _this2.servicePopup.close();
	            BX.UI.Dialogs.MessageBox.alert(main_core.Loc.getMessage('SEO_ADS_FACEBOOK_BUSINESS_LOGIN_ERROR_CONTENT'), main_core.Loc.getMessage('SEO_ADS_FACEBOOK_BUSINESS_LOGIN_ERROR_TITLE'));
	          });
	        } else {
	          slider.getData().get('COMPONENT_KEY').alert(main_core.Loc.getMessage('SEO_ADS_FACEBOOK_BUSINESS_LOGIN_ERROR_TITLE'), main_core.Loc.getMessage('SEO_ADS_FACEBOOK_BUSINESS_LOGIN_FIELDS_ERROR_CONTENT'), function (messageBox) {
	            messageBox.close();
	            _this2.login();
	          });
	        }
	      }
	    }
	  }]);
	  return FacebookLogin;
	}(Login);

	var LoginFactory = /*#__PURE__*/function () {
	  function LoginFactory() {
	    babelHelpers.classCallCheck(this, LoginFactory);
	  }
	  babelHelpers.createClass(LoginFactory, null, [{
	    key: "getLoginObject",
	    value: function getLoginObject(provider) {
	      if (provider && provider.TYPE) {
	        var _this$pool$provider$E;
	        var loginObject;
	        switch (provider.TYPE) {
	          case "facebook":
	          case "instagram":
	            loginObject = FacebookLogin;
	            break;
	          default:
	            loginObject = Login;
	            break;
	        }
	        return this.pool[provider.ENGINE_CODE] = (_this$pool$provider$E = this.pool[provider.ENGINE_CODE]) !== null && _this$pool$provider$E !== void 0 ? _this$pool$provider$E : new loginObject({
	          provider: provider
	        });
	      }
	    }
	  }]);
	  return LoginFactory;
	}();
	babelHelpers.defineProperty(LoginFactory, "pool", {});

	exports.LoginFactory = LoginFactory;
	exports.Login = Login;
	exports.FacebookLogin = FacebookLogin;

}((this.BX.Seo.Ads = this.BX.Seo.Ads || {}),BX,BX));
//# sourceMappingURL=registry.bundle.js.map
