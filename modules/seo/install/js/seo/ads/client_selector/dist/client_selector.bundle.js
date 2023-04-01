this.BX = this.BX || {};
this.BX.Seo = this.BX.Seo || {};
(function (exports,main_core,main_loader) {
	'use strict';

	var _templateObject, _templateObject2, _templateObject3, _templateObject4, _templateObject5;

	function _createForOfIteratorHelper(o, allowArrayLike) { var it = typeof Symbol !== "undefined" && o[Symbol.iterator] || o["@@iterator"]; if (!it) { if (Array.isArray(o) || (it = _unsupportedIterableToArray(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = it.call(o); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it["return"] != null) it["return"](); } finally { if (didErr) throw err; } } }; }

	function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

	function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }
	var ClientSelector = /*#__PURE__*/function () {
	  function ClientSelector(container, params) {
	    babelHelpers.classCallCheck(this, ClientSelector);
	    this.container = container;
	    this.canAddItems = !!params.canAddItems;
	    this.canUnSelectItem = !!params.canUnSelectItem;
	    this.onNewItemCallback = params.events && main_core.Type.isFunction(params.events.onNewItem) ? params.events.onNewItem : null;
	    this.onSelectItemCallback = params.events && main_core.Type.isFunction(params.events.onSelectItem) ? params.events.onSelectItem : null;
	    this.onUnSelectItemCallback = params.events && main_core.Type.isFunction(params.events.onUnSelectItem) ? params.events.onUnSelectItem : null;
	    this.onRemoveItemCallback = params.events && main_core.Type.isFunction(params.events.onRemoveItem) ? params.events.onRemoveItem : null;
	    this.init();
	    this.setSelected(params.selected);
	    this.setItems(params.items ? params.items : {});
	    this.enabled = true;
	    this.loader = new main_loader.Loader({
	      size: 20
	    });
	  }

	  babelHelpers.createClass(ClientSelector, [{
	    key: "setSelected",
	    value: function setSelected(item) {
	      this.selected = item;
	      this.closeMenu();
	      this.updateClientHtml();
	    }
	  }, {
	    key: "setItems",
	    value: function setItems(items) {
	      this.closeMenu();
	      this.items = items;
	    }
	  }, {
	    key: "init",
	    value: function init() {
	      main_core.Dom.append(this.getHtml(), this.container);
	      this.updateClientHtml();
	      main_core.Event.bind(this.container, 'click', this.onContainerClick.bind(this));
	    }
	  }, {
	    key: "enable",
	    value: function enable() {
	      this.enabled = true;
	      var selector = this.getSelectorNode();
	      selector ? selector.classList.remove('seo-ads-client-selector-loading') : false;
	      this.loader.hide();
	    }
	  }, {
	    key: "disable",
	    value: function disable() {
	      this.enabled = false;
	      var selector = this.getSelectorNode();
	      selector ? selector.classList.add('seo-ads-client-selector-loading') : false;
	      this.loader.hide();

	      if (selector) {
	        selector.classList.add('seo-ads-client-selector-loading');
	        var loader = selector.getElementsByClassName('seo-ads-client-selector-loader')[0];
	        this.loader.show(loader);
	      }
	    }
	  }, {
	    key: "getHtml",
	    value: function getHtml() {
	      return main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t<div class=\"seo-ads-client\">\n\t\t\t<div class=\"seo-ads-client-selector\">\n\t\t\t\t<div class=\"seo-ads-client-selector-avatar\" data-role=\"user-avatar\"></div>\n\t\t\t\t<div class=\"seo-ads-client-selector-user\">\n\t\t\t\t\t<a target=\"_top\" data-role=\"user-name user-link\" class=\"seo-ads-client-selector-user-link\" title=\"\"></a>\n\t\t\t\t</div>\n\t\t\t\t<span class=\"seo-ads-client-selector-arrow\"></span>\n\t\t\t\t<span class=\"seo-ads-client-selector-loader\"></span>\n\t\t\t</div>\n\t\t\t<div class=\"seo-ads-client-note\">\n\t\t\t", "\n\t\t\t</div>\n\t\t</div>\n\t\t"])), main_core.Loc.getMessage('SEO_ADS_CLIENT_NOTE'));
	    }
	  }, {
	    key: "getMenuItemHtml",
	    value: function getMenuItemHtml(item) {
	      var name = BX.util.htmlspecialchars(item.NAME);
	      var html = main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["<div>\n\t\t\t", "\n\t\t\t<span class=\"seo-ads-client-menu-popup-user\">", "</span>\n\t\t\t<span class=\"seo-ads-client-menu-popup-shutoff\" data-role=\"client-remove\" data-client-id=\"", "\">", "</span>\n\t\t</div>"])), item.PICTURE ? main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["<div class=\"seo-ads-client-menu-avatar\" style=\"background-image: url('", "');\"></div>"])), item.PICTURE) : main_core.Tag.render(_templateObject4 || (_templateObject4 = babelHelpers.taggedTemplateLiteral(["<div class=\"seo-ads-client-menu-avatar\"></div>"]))), name, item.CLIENT_ID, main_core.Loc.getMessage('SEO_ADS_CLIENT_DISCONNECT'));
	      return html.innerHTML;
	    }
	  }, {
	    key: "getRemoveConfirmPopupHtml",
	    value: function getRemoveConfirmPopupHtml(item) {
	      var name = BX.util.htmlspecialchars(item.NAME);
	      return main_core.Tag.render(_templateObject5 || (_templateObject5 = babelHelpers.taggedTemplateLiteral(["<div class=\"seo-ads-client-popup\">\n\t\t\t<div class=\"seo-ads-client-popup-text\">\n\t\t\t", "\n\t\t\t</div>\n\t\t</div>"])), main_core.Loc.getMessage('SEO_ADS_CLIENT_REMOVE').replace('#NAME#', name));
	    }
	  }, {
	    key: "updateClientHtml",
	    value: function updateClientHtml() {
	      var userAvatar = '';
	      var userName = '';
	      var userLink = '';
	      var empty = false;

	      if (this.selected) {
	        userAvatar = this.selected.hasOwnProperty('PICTURE') ? this.selected.PICTURE : '';
	        userName = this.selected.hasOwnProperty('NAME') ? this.selected.NAME : main_core.Loc.getMessage('SEO_ADS_CLIENT_SELECTOR_UNTITLED');
	        userLink = this.selected.hasOwnProperty('LINK') ? this.selected.LINK : '';
	      } else {
	        userName = main_core.Loc.getMessage('SEO_ADS_CLIENT_SELECTOR_EMPTY');
	        empty = true;
	      }

	      var selector = this.getSelectorNode();

	      if (empty) {
	        selector ? selector.classList.add('seo-ads-client-selector-empty') : false;
	      } else {
	        selector ? selector.classList.remove('seo-ads-client-selector-empty') : false;
	      }

	      var avatarNode = this.container.querySelector('[data-role="user-avatar"]');
	      var nameNode = this.container.querySelector('[data-role*="user-name"]');
	      var linkNode = this.container.querySelector('[data-role*="user-link"]');
	      if (userAvatar) avatarNode.style.backgroundImage = "url('" + userAvatar + "')";else avatarNode.style.removeProperty('background-image');
	      nameNode.textContent = userName;
	      if (userLink) linkNode.setAttribute('href', userLink);else linkNode.removeAttribute('href');
	    }
	  }, {
	    key: "onSelectItem",
	    value: function onSelectItem(item) {
	      this.setSelected(item);
	      if (main_core.Type.isFunction(this.onSelectItemCallback)) this.onSelectItemCallback(item);
	    }
	  }, {
	    key: "onUnSelectItem",
	    value: function onUnSelectItem() {
	      this.setSelected(null);

	      if (main_core.Type.isFunction(this.onUnSelectItemCallback)) {
	        this.onUnSelectItemCallback();
	      }
	    }
	  }, {
	    key: "onRemoveItem",
	    value: function onRemoveItem(item) {
	      if (main_core.Type.isFunction(this.onRemoveItemCallback)) this.onRemoveItemCallback(item);
	    }
	  }, {
	    key: "onContainerClick",
	    value: function onContainerClick() {
	      var _this = this;

	      if (!this.enabled) {
	        return;
	      }

	      var menuItems = [];

	      var _iterator = _createForOfIteratorHelper(this.items),
	          _step;

	      try {
	        for (_iterator.s(); !(_step = _iterator.n()).done;) {
	          var item = _step.value;
	          menuItems.push({
	            html: this.getMenuItemHtml(item),
	            className: "seo-ads-client-menu menu-popup-no-icon",
	            onclick: this.onSelectItem.bind(this, item)
	          });
	        }
	      } catch (err) {
	        _iterator.e(err);
	      } finally {
	        _iterator.f();
	      }

	      if (this.canUnSelectItem) {
	        menuItems.push({
	          delimiter: true
	        }, {
	          text: main_core.Loc.getMessage('SEO_ADS_CLIENT_NO_ACCOUNT'),
	          onclick: this.onUnSelectItem.bind(this)
	        });
	      }

	      if (this.canAddItems) {
	        menuItems.push({
	          delimiter: true
	        }, {
	          text: main_core.Loc.getMessage('SEO_ADS_CLIENT_ADD'),
	          onclick: function onclick() {
	            _this.closeMenu();

	            if (main_core.Type.isFunction(_this.onNewItemCallback)) _this.onNewItemCallback();
	          }
	        });
	      }

	      var selector = this.getSelectorNode();
	      BX.PopupMenu.show("clientsMenuDropdown", this.container, menuItems, {
	        offsetTop: 0,
	        offsetLeft: 42,
	        angle: true,
	        events: {
	          onPopupClose: function onPopupClose() {
	            selector ? selector.classList.remove('seo-ads-client-selector-active') : false;
	            BX.PopupMenu.destroy('clientsMenuDropdown');
	          }
	        }
	      });
	      selector ? selector.classList.add('seo-ads-client-selector-active') : false;
	      var removeClientLinks = BX.PopupMenu.currentItem.popupWindow.getContentContainer().querySelectorAll('[data-role="client-remove"]');

	      var _iterator2 = _createForOfIteratorHelper(removeClientLinks),
	          _step2;

	      try {
	        for (_iterator2.s(); !(_step2 = _iterator2.n()).done;) {
	          var removeClientLink = _step2.value;
	          main_core.Event.bind(removeClientLink, "click", function (event) {
	            event.stopPropagation();
	            var clientId = BX.data(event.target, "client-id");

	            _this.closeMenu();

	            var _iterator3 = _createForOfIteratorHelper(_this.items),
	                _step3;

	            try {
	              for (_iterator3.s(); !(_step3 = _iterator3.n()).done;) {
	                var _item = _step3.value;

	                if (_item.CLIENT_ID == clientId) {
	                  _this.confirmRemoveItem(_item);
	                }
	              }
	            } catch (err) {
	              _iterator3.e(err);
	            } finally {
	              _iterator3.f();
	            }
	          });
	        }
	      } catch (err) {
	        _iterator2.e(err);
	      } finally {
	        _iterator2.f();
	      }
	    }
	  }, {
	    key: "confirmRemoveItem",
	    value: function confirmRemoveItem(item) {
	      var _this2 = this;

	      var confirmPopup = new BX.PopupWindow({
	        content: this.getRemoveConfirmPopupHtml(item),
	        autoHide: true,
	        cacheable: false,
	        closeIcon: true,
	        closeByEsc: true,
	        buttons: [new BX.UI.Button({
	          text: main_core.Loc.getMessage('SEO_ADS_CLIENT_DISCONNECT'),
	          color: BX.UI.Button.Color.DANGER,
	          onclick: function onclick(event) {
	            confirmPopup.close();

	            _this2.onRemoveItem(item);
	          }
	        }), new BX.UI.Button({
	          text: main_core.Loc.getMessage('SEO_ADS_CLIENT_BTN_CANCEL'),
	          color: BX.UI.Button.Color.LINK,
	          onclick: function onclick() {
	            confirmPopup.close();
	          }
	        })]
	      });
	      confirmPopup.show();
	    }
	  }, {
	    key: "closeMenu",
	    value: function closeMenu() {
	      if (BX.PopupMenu.currentItem) {
	        BX.PopupMenu.currentItem.close();
	      }
	    }
	  }, {
	    key: "destroy",
	    value: function destroy() {
	      if (BX.PopupMenu.currentItem) {
	        BX.PopupMenu.currentItem.close();
	      }

	      this.container.innerHTML = '';
	    }
	  }, {
	    key: "getSelectorNode",
	    value: function getSelectorNode() {
	      var selector = this.container.getElementsByClassName('seo-ads-client-selector');
	      if (selector) selector = selector[0];
	      return selector;
	    }
	  }]);
	  return ClientSelector;
	}();

	exports.ClientSelector = ClientSelector;

}((this.BX.Seo.Ads = this.BX.Seo.Ads || {}),BX,BX));
//# sourceMappingURL=client_selector.bundle.js.map
