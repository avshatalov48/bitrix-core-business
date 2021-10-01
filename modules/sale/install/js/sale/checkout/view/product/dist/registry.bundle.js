this.BX = this.BX || {};
this.BX.Sale = this.BX.Sale || {};
this.BX.Sale.Checkout = this.BX.Sale.Checkout || {};
this.BX.Sale.Checkout.View = this.BX.Sale.Checkout.View || {};
(function (exports,sale_checkout_view_element_animatePrice,sale_checkout_view_mixins,currency_currencyCore,sale_checkout_view_element_button,ui_vue,catalog_skuTree,sale_checkout_const,main_core_events) {
	'use strict';

	ui_vue.BitrixVue.component('sale-checkout-view-product-price', {
	  props: ['item'],
	  computed: {
	    hasDiscount: function hasDiscount() {
	      return this.item.discount.sum !== 0;
	    }
	  },
	  // language=Vue
	  template: "\n\t\t<div class=\"checkout-item-price-block\">\n\t\t\t<div v-if=\"hasDiscount\"\n\t\t\t\tclass=\"checkout-item-price-discount-container\">\n\t\t\t\t<span class=\"checkout-item-price-discount\">\n\t\t\t\t\t<sale-checkout-view-element-animate_price :sum=\"this.item.baseSum\" :currency=\"this.item.currency\" />\n\t\t\t\t</span>\n\t\t\t\t<span class=\"checkout-item-price-discount-diff\">\n\t\t\t\t\t<sale-checkout-view-element-animate_price :sum=\"this.item.discount.sum\" :currency=\"this.item.currency\" :prefix=\"'-'\" />\n\t\t\t\t</span>\n\t\t\t</div>\n\t\t\t<span class=\"checkout-item-price\">\n\t\t\t\t<sale-checkout-view-element-animate_price :sum=\"this.item.sum\" :currency=\"this.item.currency\"/>\n\t\t\t</span>\n\t\t</div>\n\t"
	});

	ui_vue.BitrixVue.component('sale-checkout-view-product-props_list', {
	  props: ['list'],
	  methods: {
	    isShow: function isShow(item) {
	      return item.name !== '' && item.value !== '';
	    }
	  },
	  // language=Vue
	  template: "\n\t\t<div>\n\t\t<div  v-for=\"(item, index) in list\" v-if=\"isShow(item)\" class=\"checkout-basket-item-props\" :key=\"index\">{{item.name}}: <strong>{{item.value}}</strong></div>\n\t\t</div>\n\t"
	});

	ui_vue.BitrixVue.component('sale-checkout-view-product-item_backdrop', {
	  props: ['item', 'index'],
	  mixins: [sale_checkout_view_mixins.MixinProductItemEdit],
	  // language=Vue
	  template: "\n      <div class=\"checkout-basket-item-backdrop-wrapper js-backdrop-open-change-sku\" style=\"\">\n\t\t  <sale-checkout-view-element-button-backdrop_overlay_close class=\"js-backdrop-open-change-sku\" :index=\"this.index\"/>\n\t\t  <div class=\"checkout-basket-item-backdrop-container js-backdrop-open-change-sku\">\n            <div class=\"checkout-basket-item-detail-header justify-content-between align-items-center\">\n              <div class=\"checkout-basket-item-detail-swipe-btn-container\" id=\"bx_3966226736_424_7e1b8e3524755c391129a9d7e6f2d206_prebuy_swipe_btn\">\n                <div class=\"checkout-basket-item-detail-swipe-btn\"/>\n              </div>\n            </div>\n\t\t\t<div class=\"checkout-basket-item-backdrop-inner\">\n\t\t\t  <div class=\"checkout-basket-item-backdrop-main\">\n\t\t\t\t<div class=\"checkout-basket-item-head\">\n                  <a :href=\"item.product.detailPageUrl\" class=\"checkout-basket-item-image-block\">\n                    <img :src=\"getSrc\" :alt=\"item.name\"\n                         class=\"checkout-basket-item-image\">\n                  </a>\n                  <h2 class=\"checkout-basket-item-name-block\">\n                    <a :href=\"item.product.detailPageUrl\" class=\"checkout-basket-item-name-text\">{{item.name}}</a>\n                  </h2>  \n\t\t\t\t</div>\n\t\t\t\t\n\t\t\t\t<div class=\"checkout-basket-item-info-container\" v-if=\"hasProps() || hasSkyTree()\">\n\t\t\t\t  <div class=\"checkout-basket-item-info-block\">\n\t\t\t\t\t<sale-checkout-view-product-props_list :list=\"item.props\" v-if=\"hasProps()\"/>\n\t\t\t\t\t<sale-checkout-view-product-sku_tree :tree=\"item.sku.tree\" :index=\"index\" v-if=\"hasSkyTree()\"/>\n\t\t\t\t  </div>\n\t\t\t\t</div>\t\n\t\t\t  </div>\n\t\t\t  <div class=\"checkout-basket-item-backdrop-bottom\">\n                <div class=\"checkout-basket-item-summary-info\">\n                  <div class=\"checkout-item-quantity-block\">\n                    <div class=\"checkout-item-quantity-field-container\">\n                      <slot name=\"button-minus\" />\n                      <div class=\"checkout-item-quantity-field-block\">\n                        <input disabled class=\"checkout-item-quantity-field\" type=\"text\" inputmode=\"numeric\" :value=\"item.quantity\">\n                        <div class=\"checkout-item-quantity-field\">{{item.quantity}}</div>\n                      </div>\n                      <slot name=\"button-plus\" />\n                      <slot name=\"quantity-description\" />\n                    </div>\n                  </div>\n                  <sale-checkout-view-product-price :item=\"item\" :index=\"index\" />\n                </div>\n                <sale-checkout-view-element-button-backdrop_sku_change :index=\"index\"/>\n\t\t\t  </div>\n\t\t\t</div>\n\t\t  </div>\n      </div>\n\t"
	});

	ui_vue.BitrixVue.component('sale-checkout-view-product-item_deleted', {
	  props: ['item', 'index'],
	  methods: {
	    getSrc: function getSrc() {
	      return encodeURI(this.item.product.picture);
	    }
	  },
	  computed: {
	    localize: function localize() {
	      return Object.freeze(ui_vue.BitrixVue.getFilteredPhrases('CHECKOUT_VIEW_PRODUCT_INFO_DELETED_'));
	    }
	  },
	  // language=Vue
	  template: "\n      <div class=\"checkout-basket-item-container\">\n        <div class=\"checkout-basket-item-inner\">\n          <a href=\"\" class=\"checkout-basket-item-image-block\">\n            <img :src=\"getSrc()\" :alt=\"item.name\" class=\"checkout-basket-item-image\">\n          </a>\n          <div class=\"checkout-basket-item-info-container\">\n            <h2 class=\"checkout-basket-item-name-block\">\n\t\t\t\t<span class=\"checkout-basket-item-name-text\"><strong>{{localize.CHECKOUT_VIEW_PRODUCT_INFO_DELETED_WAS_DELETED}}</strong> {{item.name}}</span>\n            </h2>\n          </div>\n          <sale-checkout-view-element-button-restore :index=\"index\"/>\n        </div>\n      </div>\n\t"
	});

	ui_vue.BitrixVue.component('sale-checkout-view-product-item_view', {
	  props: ['item'],
	  computed: {
	    getSrc: function getSrc() {
	      return encodeURI(this.item.product.picture);
	    }
	  },
	  // language=Vue
	  template: "\n      <div class=\"checkout-basket-item-container\">\n<!--      <div class=\"checkout-basket-item-label\">{{item.name}}</div>-->\n\t\t  <div class=\"checkout-basket-item-inner\">\n\t\t\t<a :href=\"item.product.detailPageUrl\" class=\"checkout-basket-item-image-block\">\n\t\t\t  <img :src=\"getSrc\" :alt=\"item.name\" class=\"checkout-basket-item-image\">\n\t\t\t</a>\n\t\t\t<div class=\"checkout-basket-item-info-container\">\n\t\t\t  <h2 class=\"checkout-basket-item-name-block\">\n\t\t\t\t<a :href=\"item.product.detailPageUrl\" class=\"checkout-basket-item-name-text\">{{item.name}}</a>\n\t\t\t  </h2>\n\t\t\t  <div class=\"checkout-basket-item-info-block\">\n\t\t\t\t<sale-checkout-view-product-props_list :list=\"item.props\"/>\n\t\t\t  </div>\n\t\t\t</div>\n\t\t\t<div class=\"checkout-basket-item-summary-info\">\n\t\t\t  <div class=\"checkout-item-quantity-block\">\n\t\t\t\t<div class=\"checkout-item-quantity-block-text\">{{item.quantity}} {{item.measureText}}</div>\n\t\t\t  </div>\n\t\t\t  <sale-checkout-view-product-price :item=\"item\"/>\n\t\t\t</div>\n\t\t  </div>\n      </div>\n\t"
	});

	ui_vue.BitrixVue.component('sale-checkout-view-product-item_edit', {
	  props: ['item', 'index'],
	  mixins: [sale_checkout_view_mixins.MixinProductItemEdit],
	  // language=Vue
	  template: "\n      <div class=\"checkout-basket-item-container\">\n<!--      <div class=\"checkout-basket-item-label\">{{item.name}}</div>-->\n      <div class=\"checkout-basket-item-inner\">\n        <a :href=\"item.product.detailPageUrl\" class=\"checkout-basket-item-image-block\">\n          <img :src=\"getSrc\" :alt=\"item.name\" class=\"checkout-basket-item-image\">\n        </a>\n        <div class=\"checkout-basket-item-info-container\">\n          <h2 class=\"checkout-basket-item-name-block\">\n            <a :href=\"item.product.detailPageUrl\" class=\"checkout-basket-item-name-text\">{{item.name}}</a>\n          </h2>\n          <div class=\"checkout-basket-item-info-block\">\n            <sale-checkout-view-product-props_list :list=\"item.props\" v-if=\"hasProps()\"/>\n            <div class=\"checkout-basket-desktop-only\">\n            \t<sale-checkout-view-product-sku_tree :tree=\"item.sku.tree\" :index=\"index\" v-if=\"hasSkyTree()\"/>\n            </div>\n            <slot name=\"button-change-sku\"/>\n<!--            <div class=\"checkout-item-warning-container\">-->\n<!--              <div class=\"text-danger\">Available: 344 pcs.</div>-->\n<!--              <div class=\"text-danger\">Unknown error</div>-->\n<!--            </div>-->\n          </div>\n\t\t  \n        </div>\n        <div class=\"checkout-basket-item-summary-info\">\n          <div class=\"checkout-item-quantity-block\">\n            <div class=\"checkout-item-quantity-field-container\">\n              <slot name=\"button-minus\"/>\n              <div class=\"checkout-item-quantity-field-block\">\n                <input disabled class=\"checkout-item-quantity-field\" type=\"text\" inputmode=\"numeric\" :value=\"item.quantity\">\n\t\t\t\t<div class=\"checkout-item-quantity-field\">{{item.quantity}}</div>\n              </div>\n              <slot name=\"button-plus\"/>\n              <slot name=\"quantity-description\"/>\n            </div>\n          </div>\n          <sale-checkout-view-product-price :item=\"item\"/>\n          <slot name=\"button-remove\"/>\n        </div>\n      </div>\n      </div>\n\t"
	});

	ui_vue.BitrixVue.component('sale-checkout-view-product-measure', {
	  props: ['item'],
	  // language=Vue
	  template: "\n      <span class=\"checkout-item-quantity-description\">\n\t\t  <span class=\"checkout-item-quantity-description-text\">{{item.measureText}}</span>\n\t\t  <span class=\"checkout-item-quantity-description-price\"/>\n\t\t</span>\n\t"
	});

	ui_vue.BitrixVue.component('sale-checkout-view-product-price_measure', {
	  props: ['item'],
	  computed: {
	    priceFormatted: function priceFormatted() {
	      return currency_currencyCore.CurrencyCore.currencyFormat(this.item.price, this.item.currency, true);
	    }
	  },
	  // language=Vue
	  template: "\n      <span class=\"checkout-item-quantity-description\">\n\t\t  <span class=\"checkout-item-quantity-description-text\"><div v-html=\"priceFormatted + '/' + item.measureText\"/></span>\n\t\t  <span class=\"checkout-item-quantity-description-price\"/>\n\t\t</span>\n\t"
	});

	ui_vue.BitrixVue.component('sale-checkout-view-product-row', {
	  props: ['item', 'index', 'mode'],
	  mixins: [sale_checkout_view_mixins.MixinLoader],
	  data: function data() {
	    return {
	      showBackdropMobileMenu: 'N',
	      showBackdropChangeSku: 'N'
	    };
	  },
	  computed: {
	    config: function config() {
	      return {
	        status: this.item.status
	      };
	    },
	    hasSkuPropsColor: function hasSkuPropsColor() {
	      var tree = this.item.sku.tree;
	      return tree.hasOwnProperty('EXISTING_VALUES') && tree.EXISTING_VALUES.hasOwnProperty('COLOR_REF');
	    },
	    isBackdropMobileMenu: function isBackdropMobileMenu() {
	      return this.showBackdropMobileMenu === 'Y';
	    },
	    isBackdropChangeSku: function isBackdropChangeSku() {
	      return this.showBackdropChangeSku === 'Y';
	    },
	    isDeleted: function isDeleted() {
	      return this.item.deleted === 'Y';
	    },
	    isLocked: function isLocked() {
	      return this.item.status === sale_checkout_const.Loader.status.wait;
	    },
	    getConstMode: function getConstMode() {
	      return sale_checkout_const.Application.mode;
	    },
	    buttonMinusDisabled: function buttonMinusDisabled() {
	      return this.item.quantity - this.item.product.ratio < this.item.product.ratio;
	    },
	    buttonPlusDisabled: function buttonPlusDisabled() {
	      return this.item.quantity + this.item.product.ratio > this.item.product.availableQuantity;
	    },
	    getObjectClass: function getObjectClass() {
	      var classes = ['checkout-item'];

	      if (this.hasSkuPropsColor) {
	        classes.push('checkout-basket-item--has-sku-color');
	      }

	      if (this.isDeleted) {
	        classes.push('checkout-basket-item-deleted');
	      }

	      if (this.isLocked) {
	        classes.push('checkout-basket-item-locked');
	      }

	      if (this.isBackdropChangeSku) {
	        classes.push('active-backdrop-open-change-sku');
	      }

	      if (this.isBackdropMobileMenu) {
	        classes.push('active-backdrop-open-mobile-menu');
	      }

	      return classes;
	    }
	  },
	  created: function created() {
	    var _this = this;

	    main_core_events.EventEmitter.subscribe(sale_checkout_const.EventType.basket.backdropOpenMobileMenu, function (event) {
	      var index = event.getData().index;

	      if (index === _this.index) {
	        _this.showBackdropMobileMenu = 'Y';
	      }
	    });
	    main_core_events.EventEmitter.subscribe(sale_checkout_const.EventType.basket.backdropOpenChangeSku, function (event) {
	      var index = event.getData().index;

	      if (index === _this.index) {
	        _this.showBackdropChangeSku = 'Y';
	      }
	    });
	    main_core_events.EventEmitter.subscribe(sale_checkout_const.EventType.basket.backdropClose, function (event) {
	      var index = event.getData().index;

	      if (index === _this.index) {
	        _this.showBackdropMobileMenu = 'N';
	        _this.showBackdropChangeSku = 'N';
	      }
	    });
	  },
	  beforeDestroy: function beforeDestroy() {
	    main_core_events.EventEmitter.unsubscribe(sale_checkout_const.EventType.basket.backdropOpenMobileMenu);
	    main_core_events.EventEmitter.unsubscribe(sale_checkout_const.EventType.basket.backdropOpenChangeSku);
	    main_core_events.EventEmitter.unsubscribe(sale_checkout_const.EventType.basket.backdropClose);
	  },
	  // language=Vue
	  template: "\n\t\t<div class=\"checkout-basket-item\" :class=\"getObjectClass\" style='position: relative;' ref=\"container\">\n\t\t\t<template v-if=\"isDeleted\">\n              <sale-checkout-view-product-item_deleted :item=\"item\" :index=\"index\"/>\n\t\t\t</template>\n            <template v-else>\n              <template v-if=\"mode === getConstMode.edit\">\n                <sale-checkout-view-product-item_edit :item=\"item\" :index=\"index\" :mode=\"mode\">\n                  <template v-slot:button-minus><sale-checkout-view-element-button-minus :class=\"{'checkout-item-quantity-btn-disabled': buttonMinusDisabled}\" :index=\"index\"/></template>\n                  <template v-slot:button-plus><sale-checkout-view-element-button-plus :class=\"{'checkout-item-quantity-btn-disabled': buttonPlusDisabled}\" :index=\"index\"/></template>\n                  <template v-slot:button-remove>\n                    <div class=\"checkout-basket-item-remove-btn-block\">\n\t\t\t\t\t\t<sale-checkout-view-element-button-remove :index=\"index\"/>\n                      \t<sale-checkout-view-element-button-item_mobile_menu :index=\"index\"/>\n\t\t\t\t\t</div>\n\t\t\t\t  </template>\n                  <template v-slot:quantity-description>\n\t\t\t\t\t<template v-if=\"buttonMinusDisabled\"><sale-checkout-view-product-measure :item=\"item\"/></template>\n\t\t\t\t\t<template v-else><sale-checkout-view-product-price_measure :item=\"item\"/></template>\n\t\t\t\t  </template>\n                  <template v-slot:button-change-sku>\n\t\t\t\t\t<sale-checkout-view-element-button-item_change_sku :index=\"index\"/>\n\t\t\t\t  </template>\n                </sale-checkout-view-product-item_edit>\n\n                <sale-checkout-view-product-item_backdrop_remove :index=\"index\"/>\n\t\t\t\t<sale-checkout-view-product-item_backdrop :item=\"item\" :index=\"index\" >\n                  <template v-slot:button-minus><sale-checkout-view-element-button-minus :class=\"{'checkout-item-quantity-btn-disabled': buttonMinusDisabled}\" :index=\"index\"/></template>\n                  <template v-slot:button-plus><sale-checkout-view-element-button-plus :class=\"{'checkout-item-quantity-btn-disabled': buttonPlusDisabled}\" :index=\"index\"/></template>\n\t\t\t\t</sale-checkout-view-product-item_backdrop>\n\t\t\t\t\n              </template>\n              <template v-else>\n\t\t\t\t<sale-checkout-view-product-item_view :item=\"item\"/>\n              </template>\n            </template>\n\t\t</div>\n\t"
	});

	ui_vue.BitrixVue.component('sale-checkout-view-product-list', {
	  props: ['items', 'mode'],
	  // language=Vue
	  template: "\n\t\t<div>\n\t\t\t<sale-checkout-view-product-row v-for=\"(item, index) in items\" :key=\"index\"\n\t\t\t\t\t\t\t\t\t\t\t:item=\"item\" :index=\"index\" :mode=\"mode\" />\n\t\t</div>\n\t"
	});

	ui_vue.BitrixVue.component('sale-checkout-view-product-summary', {
	  props: ['total', 'mode'],
	  methods: {
	    hasDiscount: function hasDiscount() {
	      return this.total.discount.sum !== 0;
	    },
	    backdropTotalOpen: function backdropTotalOpen() {
	      main_core_events.EventEmitter.emit(sale_checkout_const.EventType.basket.backdropTotalOpen);
	    }
	  },
	  computed: {
	    localize: function localize() {
	      return Object.freeze(ui_vue.BitrixVue.getFilteredPhrases('CHECKOUT_VIEW_SUMMARY_'));
	    },
	    priceFormatted: function priceFormatted() {
	      return currency_currencyCore.CurrencyCore.currencyFormat(this.total.price, this.total.currency, true);
	    },
	    basePriceFormatted: function basePriceFormatted() {
	      return currency_currencyCore.CurrencyCore.currencyFormat(this.total.basePrice, this.total.currency, true);
	    },
	    discountSumFormatted: function discountSumFormatted() {
	      return currency_currencyCore.CurrencyCore.currencyFormat(this.total.discount.sum, this.total.currency, true);
	    },
	    getConstMode: function getConstMode() {
	      return sale_checkout_const.Application.mode;
	    }
	  },
	  // language=Vue
	  template: "\n\t  <div class=\"checkout-basket-summary-container\">\n<!--\t\tregion mobile -->\n\t  \t<div class=\"checkout-basket-mobile-only checkout-basket-summary-discount checkout-summary-item-discount\" v-if=\"hasDiscount()\">\n\t\t\t<div class=\"checkout-basket-summary-text\">{{localize.CHECKOUT_VIEW_SUMMARY_BASKET_PROFIT}}</div>\n\t\t\t<div class=\"checkout-item-price-block\">\n\t\t\t  <span class=\"checkout-summary-item-price-discount\" v-html=\"'-' + discountSumFormatted\"></span>\n\t\t\t</div>\n\t\t</div>\n<!--\t  endregion-->\n\n<!--\t  region web-->\n\t\t<div class=\"checkout-basket-summary\">\n\t\t  <div class=\"checkout-basket-summary-text\">{{localize.CHECKOUT_VIEW_SUMMARY_BASKET_ITEMS}}</div>\n\t\t  <div class=\"checkout-item-price-block\">\n\t\t\t<div class=\"checkout-item-price-discount-container checkout-basket-desktop-only\" v-if=\"hasDiscount()\">\n\t\t\t  <span class=\"checkout-item-price-discount\" v-html=\"basePriceFormatted\"></span>\n\t\t\t  <span class=\"checkout-item-price-discount-diff\" v-html=\"'-' + discountSumFormatted\"></span>\n\t\t\t</div>\n\t\t\t<span class=\"checkout-item-price\" v-html=\"priceFormatted\"></span>\n\t\t  </div>\n\t\t  <template v-if=\"mode === getConstMode.view\">\n\t\t\t<div class=\"d-block w-100 text-right\">\n\t\t\t  <span class=\"checkout-basket-total-backdrop-btn checkout-basket-mobile-only\" @click=\"backdropTotalOpen\">{{localize.CHECKOUT_VIEW_SUMMARY_DETAILS}}</span>\n\t\t\t</div>\n\t\t  </template>\n\t\t</div>\n<!--\t  endregion-->\n\t  </div>\n\n\t"
	});

	ui_vue.BitrixVue.component('sale-checkout-view-product', {
	  props: ['items', 'total', 'mode', 'config'],
	  mixins: [sale_checkout_view_mixins.MixinLoader],
	  computed: {
	    isLocked: function isLocked() {
	      return this.config.status === sale_checkout_const.Loader.status.wait;
	    },
	    getObjectClass: function getObjectClass() {
	      var classes = ['checkout-basket-list-items'];

	      if (this.mode === sale_checkout_const.Application.mode.view) {
	        classes.push('checkout-basket-list-items-view-mode');
	      }

	      if (this.isLocked) {
	        classes.push('checkout-basket-item-locked');
	      }

	      return classes;
	    }
	  },
	  // language=Vue
	  template: "\n    \t<div :class=\"getObjectClass\" ref=\"container\">\n\t\t\t<sale-checkout-view-product-list :items=\"items\" :mode=\"mode\"/>\n\t\t\t<sale-checkout-view-product-summary :total=\"total\" :mode=\"mode\"/>\n\t\t</div>\n\t"
	});

	ui_vue.BitrixVue.component('sale-checkout-view-product-item_backdrop_remove', {
	  props: ['index'],
	  // language=Vue
	  template: "\n      <div class=\"checkout-basket-item-backdrop-wrapper js-backdrop-open-mobile-menu\">\n\t      <sale-checkout-view-element-button-backdrop_overlay_close class=\"js-backdrop-open-mobile-menu\" :index=\"index\"/>\n\t\t  <div class=\"checkout-basket-item-backdrop-container js-backdrop-open-mobile-menu\">\n            <div class=\"checkout-basket-item-detail-header justify-content-between align-items-center\">\n              <div class=\"checkout-basket-item-detail-header-separate\"/>\n              <div class=\"checkout-basket-item-detail-swipe-btn-container\" id=\"bx_3966226736_424_7e1b8e3524755c391129a9d7e6f2d206_prebuy_swipe_btn\">\n                <div class=\"checkout-basket-item-detail-swipe-btn\"/>\n              </div>\n              <sale-checkout-view-element-button-backdrop_close :index=\"index\"/>\n            </div>\n\t\t\t<div class=\"checkout-basket-item-backdrop-inner\">\n\t\t\t\t<sale-checkout-view-element-button-backdrop_remove_remove :index=\"index\"/>\n\t\t\t\t<sale-checkout-view-element-button-backdrop_remove_cancel :index=\"index\"/>\n\t\t\t</div>\n\t\t  </div>\n      </div>\n\t"
	});

	ui_vue.BitrixVue.component('sale-checkout-view-product-sku_tree', {
	  props: ['tree', 'index'],
	  data: function data() {
	    return {
	      skuTree: new catalog_skuTree.SkuTree({
	        skuTree: this.tree,
	        selectable: true,
	        hideUnselected: false
	      })
	    };
	  },
	  computed: {
	    getHash: function getHash() {
	      return this.prepareValues(this.tree.SELECTED_VALUES);
	    }
	  },
	  methods: {
	    prepareValues: function prepareValues(values) {
	      return Object.keys(values).concat(Object.values(values)).join();
	    },
	    appendBlockHtml: function appendBlockHtml() {
	      var wrapper = this.$refs.container;
	      wrapper.appendChild(this.skuTree.layout());
	    }
	  },
	  watch: {
	    getHash: function getHash() {
	      var selectedValues = this.tree.SELECTED_VALUES;

	      try {
	        for (var propertyId in selectedValues) {
	          if (!selectedValues.hasOwnProperty(propertyId)) {
	            continue;
	          }

	          this.skuTree.setSelectedProperty(propertyId, selectedValues[propertyId]);
	        }
	      } catch (e) {}

	      this.skuTree.toggleSkuProperties();
	    }
	  },
	  mounted: function mounted() {
	    var _this = this;

	    this.appendBlockHtml();

	    if (this.skuTree) {
	      this.skuTree.subscribe(sale_checkout_const.EventType.basket.changeSkuOriginName, function (event) {
	        main_core_events.EventEmitter.emit(sale_checkout_const.EventType.basket.changeSku, {
	          index: _this.index,
	          data: event.getData()
	        });
	      });
	    }
	  },
	  // language=Vue
	  template: "<div>\n\t  \t<div ref=\"container\"/>\n    </div>\n\t"
	});

}((this.BX.Sale.Checkout.View.Product = this.BX.Sale.Checkout.View.Product || {}),BX.Sale.Checkout.View.Element,BX.Sale.Checkout.View.Mixins,BX.Currency,BX.Sale.Checkout.View.Element,BX,BX.Catalog.SkuTree,BX.Sale.Checkout.Const,BX.Event));
//# sourceMappingURL=registry.bundle.js.map
