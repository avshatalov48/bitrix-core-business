this.BX = this.BX || {};
this.BX.Sale = this.BX.Sale || {};
this.BX.Sale.PaymentPay = this.BX.Sale.PaymentPay || {};
this.BX.Sale.PaymentPay.Mixins = this.BX.Sale.PaymentPay.Mixins || {};
(function (exports,ui_vue) {
	'use strict';

	var button = {
	  computed: {
	    classes: function classes() {
	      var classes = ['landing-block-node-button', 'text-uppercase', 'btn', 'btn-xl', 'pr-7', 'pl-7', 'u-btn-primary', 'g-font-weight-700', 'g-font-size-12', 'g-rounded-50'];

	      if (this.loading) {
	        classes.push('loading');
	      }

	      return classes;
	    }
	  },
	  methods: {
	    onClick: function onClick(event) {
	      this.$emit('click', event);
	    }
	  }
	};

	var button$1 = {
	  computed: {
	    classes: function classes() {
	      return {
	        'order-payment-method-item-button': true,
	        'btn': true,
	        'btn-primary': true,
	        'rounded-pill': true,
	        'pay-mode': true,
	        'btn-wait': this.loading
	      };
	    }
	  },
	  methods: {
	    onClick: function onClick(event) {
	      this.$emit('click', event);
	    }
	  }
	};

	var check = {
	  computed: {
	    processing: function processing() {
	      return this.status === 'P';
	    },
	    downloadable: function downloadable() {
	      return this.status === 'Y' && this.link !== '';
	    }
	  }
	};

	var paySystemInfo = {
	  props: {
	    paySystems: {
	      type: Array,
	      default: [],
	      required: false
	    }
	  },
	  data: function data() {
	    return {
	      selectedPaySystem: null
	    };
	  },
	  computed: {
	    selectedName: function selectedName() {
	      return this.selectedPaySystem ? this.selectedPaySystem.NAME : '';
	    },
	    selectedDescription: function selectedDescription() {
	      return this.selectedPaySystem ? BX.util.htmlspecialchars(this.selectedPaySystem.DESCRIPTION) : '';
	    }
	  },
	  methods: {
	    showInfo: function showInfo(paySystem) {
	      this.selectedPaySystem = paySystem;
	    },
	    logoStyle: function logoStyle(paySystem) {
	      var defaultLogo = '/bitrix/js/salescenter/payment-pay/payment-method/images/default_logo.png';
	      var src = paySystem.LOGOTIP || defaultLogo;
	      return "background-image: url(\"".concat(BX.util.htmlspecialchars(src), "\")");
	    }
	  }
	};

	var paySystemList = {
	  methods: {
	    isItemLoading: function isItemLoading(paySystemId) {
	      return this.selectedPaySystem === paySystemId && this.loading;
	    },
	    startPayment: function startPayment(paySystemId) {
	      this.$emit('start-payment', paySystemId);
	    }
	  }
	};

	var paySystemRow = {
	  methods: {
	    onClick: function onClick() {
	      this.$emit('click', this.id);
	    }
	  },
	  computed: {
	    logoStyle: function logoStyle() {
	      var defaultLogo = '/bitrix/js/sale/payment-pay/images/default_logo.png';
	      var src = this.logo || defaultLogo;
	      return "background-image: url(\"".concat(BX.util.htmlspecialchars(src), "\")");
	    }
	  }
	};

	var paymentInfo = {
	  methods: {
	    onClick: function onClick() {
	      this.$emit('start-payment', this.paySystem.ID);
	    },
	    getCheckTitle: function getCheckTitle(check) {
	      var title = this.localize.PAYMENT_PAY_PAYMENT_SYSTEM_COMPONENTS_11;
	      return title.replace('#CHECK_ID#', check.id).replace('#DATE_CREATE#', check.dateFormatted);
	    }
	  }
	};

	var resetPanel = {
	  methods: {
	    reset: function reset() {
	      this.$emit('reset');
	    }
	  }
	};

	exports.MixinPaymentInfoButton = button;
	exports.MixinButton = button$1;
	exports.MixinCheck = check;
	exports.MixinPaySystemInfo = paySystemInfo;
	exports.MixinPaySystemList = paySystemList;
	exports.MixinPaySystemRow = paySystemRow;
	exports.MixinPaymentInfo = paymentInfo;
	exports.MixinResetPanel = resetPanel;

}((this.BX.Sale.PaymentPay.Mixins.PaymentSystem = this.BX.Sale.PaymentPay.Mixins.PaymentSystem || {}),BX));
//# sourceMappingURL=registry.bundle.js.map
