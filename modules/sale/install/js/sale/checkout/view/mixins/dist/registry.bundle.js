this.BX = this.BX || {};
this.BX.Sale = this.BX.Sale || {};
this.BX.Sale.Checkout = this.BX.Sale.Checkout || {};
this.BX.Sale.Checkout.View = this.BX.Sale.Checkout.View || {};
(function (exports,sale_checkout_const,main_loader) {
    'use strict';

    var loader = {
      methods: {
        changeStatus: function changeStatus() {
          if (this.config.status === sale_checkout_const.Loader.status.wait) {
            this.loader.show(this.$refs.container);
          } else {
            this.loader.hide();
          }
        },
        initLoader: function initLoader() {
          this.loader = new main_loader.Loader({
            size: 64
          });
        }
      },
      computed: {
        getStatus: function getStatus() {
          return this.config.status;
        }
      },
      watch: {
        getStatus: function getStatus() {
          this.changeStatus();
        }
      },
      created: function created() {
        this.initLoader();
      },
      mounted: function mounted() {
        if (this.config.status === sale_checkout_const.Loader.status.wait) {
          this.loader.show(this.$refs.container);
        }
      }
    };

    var buttonWait = {
      data: function data() {
        return {
          wait: false
        };
      },
      methods: {
        setWait: function setWait() {
          this.wait = true;
        }
      },
      computed: {
        getObjectClass: function getObjectClass() {
          var classes = ['btn', 'btn-checkout-order-status', 'btn-md', 'rounded-pill'];

          if (this.wait) {
            classes.push('btn-wait');
          }

          return classes;
        }
      }
    };

    var productItemEdit = {
      computed: {
        getSrc: function getSrc() {
          return encodeURI(this.item.product.picture);
        }
      },
      methods: {
        hasSkyTree: function hasSkyTree() {
          var _this$item$sku$tree$S;

          var tree = (_this$item$sku$tree$S = this.item.sku.tree.SELECTED_VALUES) !== null && _this$item$sku$tree$S !== void 0 ? _this$item$sku$tree$S : {};
          return Object.keys(tree).length > 0;
        },
        hasProps: function hasProps() {
          return this.item.props.length > 0;
        }
      }
    };

    exports.MixinLoader = loader;
    exports.MixinButtonWait = buttonWait;
    exports.MixinProductItemEdit = productItemEdit;

}((this.BX.Sale.Checkout.View.Mixins = this.BX.Sale.Checkout.View.Mixins || {}),BX.Sale.Checkout.Const,BX));
//# sourceMappingURL=registry.bundle.js.map
