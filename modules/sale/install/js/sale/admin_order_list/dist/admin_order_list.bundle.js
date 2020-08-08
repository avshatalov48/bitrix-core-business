this.BX = this.BX || {};
(function (exports) {
	'use strict';

	var OrderList = function OrderList() {
	  babelHelpers.classCallCheck(this, OrderList);
	};

	var IntegrationOrderList = /*#__PURE__*/function (_OrderList) {
	  babelHelpers.inherits(IntegrationOrderList, _OrderList);

	  function IntegrationOrderList() {
	    var _babelHelpers$getProt;

	    var _this;

	    babelHelpers.classCallCheck(this, IntegrationOrderList);

	    for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
	      args[_key] = arguments[_key];
	    }

	    _this = babelHelpers.possibleConstructorReturn(this, (_babelHelpers$getProt = babelHelpers.getPrototypeOf(IntegrationOrderList)).call.apply(_babelHelpers$getProt, [this].concat(args)));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "dialog", null);
	    return _this;
	  }

	  babelHelpers.createClass(IntegrationOrderList, [{
	    key: "initialize",
	    value: function initialize(settings) {
	      this._settings = settings ? settings : {};
	      this._restEntityInfo = BX.prop.getObject(this._settings, "restEntityInfo", {});
	      this._form = BX.prop.getElementNode(this._settings, "form");
	      this.entityId = BX.prop.getInteger(this._restEntityInfo, 'entityId', 0);
	      this.entityTypeId = BX.prop.getInteger(this._restEntityInfo, 'entityTypeId', 0);
	    }
	  }, {
	    key: "confirmToOpenNewOrder",
	    value: function confirmToOpenNewOrder(title, message, url) {
	      this.dialog = new BX.PopupWindow('adm-sale-order-alert-dialog', null, {
	        autoHide: false,
	        draggable: true,
	        offsetLeft: 0,
	        offsetTop: 0,
	        bindOptions: {
	          forceBindPosition: false
	        },
	        closeByEsc: true,
	        closeIcon: true,
	        titleBar: title,
	        contentColor: 'white',
	        content: BX.create('span', {
	          html: BX.util.htmlspecialchars(message).replace(/\n/g, "<br>\n"),
	          style: {
	            backgroundColor: "white"
	          }
	        })
	      });
	      var buttons = [new BX.PopupWindowButton({
	        text: BX.message('SALE_O_CONTEXT_B_IS_SYNC_B24_REGISTRY_CREATE'),
	        className: "popup-window-button-accept",
	        events: {
	          click: function click() {
	            window.open(url);
	            BX.delegate(this.onPopupClose, this);
	            BX.Sale.AdminIntegrationOrderList.closeApplication();
	          }
	        }
	      }), new BX.PopupWindowButton({
	        text: BX.message('SALE_O_CONTEXT_B_IS_SYNC_B24_REGISTRY_CLOSE'),
	        className: "popup-window-button-link-cancel",
	        events: {
	          click: function click() {
	            BX.delegate(this.onPopupClose, this);
	            BX.Sale.AdminIntegrationOrderList.closeApplication();
	          }
	        }
	      })];
	      this.dialog.setButtons(buttons);
	      this.dialog.show();
	    }
	  }, {
	    key: "popup",
	    value: function popup(title, message) {
	      this.dialog = new BX.PopupWindow('adm-sale-order-alert-dialog', null, {
	        autoHide: false,
	        draggable: true,
	        offsetLeft: 0,
	        offsetTop: 0,
	        bindOptions: {
	          forceBindPosition: false
	        },
	        closeByEsc: true,
	        closeIcon: true,
	        titleBar: title,
	        contentColor: 'white',
	        content: BX.create('span', {
	          html: BX.util.htmlspecialchars(message).replace(/\n/g, "<br>\n"),
	          style: {
	            backgroundColor: "white"
	          }
	        })
	      });
	      var buttons = [new BX.PopupWindowButton({
	        text: BX.message('SALE_O_CONTEXT_B_IS_SYNC_B24_REGISTRY_CLOSE'),
	        className: "popup-window-button-link-cancel",
	        events: {
	          click: BX.delegate(this.onPopupClose, this)
	        }
	      })];
	      this.dialog.setButtons(buttons);
	      this.dialog.show();
	    }
	  }, {
	    key: "onPopupClose",
	    value: function onPopupClose() {
	      this.dialog.close();
	      this.dialog.destroy();
	    }
	  }, {
	    key: "getSendedOrders",
	    value: function getSendedOrders() {
	      var orders = [];
	      var checksList = [];
	      checksList = this.getCheckedCheckBoxList(this._form);

	      for (var i = 0; i < checksList.length; i++) {
	        var row = BX.findParent(checksList[i], {
	          tag: 'TR'
	        });

	        if (!!row) {
	          var spanList = row.getElementsByTagName('span');

	          if (!!spanList) {
	            for (var n = 0; n < spanList.length; n++) {
	              if (spanList[n].id == 'IS_SYNC_B24_' + checksList[i].value) {
	                if (spanList[n].innerText == BX.message('SALE_O_CONTEXT_B_IS_SYNC_B24_REGISTRY_SEND_YES')) {
	                  orders.push(checksList[i].value);
	                }
	              }
	            }
	          }
	        }
	      }

	      return orders;
	    }
	  }, {
	    key: "getCheckedCheckBoxList",
	    value: function getCheckedCheckBoxList(form) {
	      var lnt = form.elements.length;
	      var list = [];

	      for (var i = 0; i < lnt; i++) {
	        if (form.elements[i].tagName.toUpperCase() == "INPUT" && form.elements[i].type.toUpperCase() == "CHECKBOX" && form.elements[i].name.toUpperCase() == "ID[]" && form.elements[i].checked == true) {
	          list.push(form.elements[i]);
	        }
	      }

	      return list;
	    }
	  }, {
	    key: "sendOrdersToRestApplication",
	    value: function sendOrdersToRestApplication() {
	      var ordersListForm = this._form;
	      var boxList = this.getCheckedCheckBoxList(this._form);

	      if (BX('tbl_sale_order_check_all') && ordersListForm) {
	        if (boxList.length == 0) {
	          this.popup(BX.message('SALE_O_CONTEXT_B_IS_SYNC_B24_TITLE'), BX.message('SALE_O_CONTEXT_B_IS_SYNC_B24_SELECTION_NEEDED'));
	        } else {
	          var ordersSend = this.getSendedOrders();

	          if (tbl_sale_order.num_checked > 3) {
	            this.popup(BX.message('SALE_O_CONTEXT_B_IS_SYNC_B24_TITLE'), BX.message('SALE_O_CONTEXT_B_IS_SYNC_B24_SELECTION_MORE_THREE'));
	          } else if (ordersSend.length > 0) {
	            this.popup(BX.message('SALE_O_CONTEXT_B_IS_SYNC_B24_TITLE'), BX.message('SALE_O_CONTEXT_B_IS_SYNC_B24_REGISTRY_SENDED') + ': ' + ordersSend.toString());
	          } else {
	            this.redirect();
	          }
	        }
	      }
	    }
	  }, {
	    key: "getValuesCheckedCheckBox",
	    value: function getValuesCheckedCheckBox(form) {
	      var boxList = this.getCheckedCheckBoxList(form);
	      var list = [];

	      for (var i = 0; i < boxList.length; i++) {
	        list.push(boxList[i].value);
	      }

	      return list;
	    }
	  }, {
	    key: "redirect",
	    value: function redirect() {
	      var url = 'sale_app_rest_sender.php';
	      url = BX.Uri.addParam(url, {
	        orderIds: this.getValuesCheckedCheckBox(this._form),
	        entityId: this.entityId,
	        entityTypeId: this.entityTypeId,
	        IFRAME: 'Y'
	      });
	      document.location.href = url;
	    }
	  }, {
	    key: "closeApplication",
	    value: function closeApplication() {
	      BX24.closeApplication();
	    }
	  }]);
	  return IntegrationOrderList;
	}(OrderList);

	var AdminOrderList = new OrderList();
	var AdminIntegrationOrderList = new IntegrationOrderList();

	exports.AdminOrderList = AdminOrderList;
	exports.AdminIntegrationOrderList = AdminIntegrationOrderList;

}((this.BX.Sale = this.BX.Sale || {})));
//# sourceMappingURL=admin_order_list.bundle.js.map
