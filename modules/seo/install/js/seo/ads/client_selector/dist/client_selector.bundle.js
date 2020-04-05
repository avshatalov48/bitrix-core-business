this.BX = this.BX || {};
this.BX.Seo = this.BX.Seo || {};
(function (exports,main_core,main_loader) {
	'use strict';

	function _templateObject5() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"seo-ads-client-popup\">\n\t\t\t<div class=\"seo-ads-client-popup-text\">\n\t\t\t", "\n\t\t\t</div>\n\t\t</div>"]);

	  _templateObject5 = function _templateObject5() {
	    return data;
	  };

	  return data;
	}

	function _templateObject4() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"seo-ads-client-menu-avatar\"></div>"]);

	  _templateObject4 = function _templateObject4() {
	    return data;
	  };

	  return data;
	}

	function _templateObject3() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"seo-ads-client-menu-avatar\" style=\"background-image: url('", "');\"></div>"]);

	  _templateObject3 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div>\n\t\t\t", "\n\t\t\t<span class=\"seo-ads-client-menu-popup-user\">", "</span>\n\t\t\t<span class=\"seo-ads-client-menu-popup-shutoff\" data-role=\"client-remove\" data-client-id=\"", "\">", "</span>\n\t\t</div>"]);

	  _templateObject2 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t<div class=\"seo-ads-client\">\n\t\t\t<div class=\"seo-ads-client-selector\">\n\t\t\t\t<div class=\"seo-ads-client-selector-avatar\" data-role=\"user-avatar\"></div>\n\t\t\t\t<div class=\"seo-ads-client-selector-user\">\n\t\t\t\t\t<a target=\"_top\" data-role=\"user-name user-link\" class=\"seo-ads-client-selector-user-link\" title=\"\"></a>\n\t\t\t\t</div>\n\t\t\t\t<span class=\"seo-ads-client-selector-arrow\"></span>\n\t\t\t\t<span class=\"seo-ads-client-selector-loader\"></span>\n\t\t\t</div>\n\t\t\t<div class=\"seo-ads-client-note\">\n\t\t\t", "\n\t\t\t</div>\n\t\t</div>\n\t\t"]);

	  _templateObject = function _templateObject() {
	    return data;
	  };

	  return data;
	}
	var ClientSelector =
	/*#__PURE__*/
	function () {
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
	      return main_core.Tag.render(_templateObject(), main_core.Loc.getMessage('SEO_ADS_CLIENT_NOTE'));
	    }
	  }, {
	    key: "getMenuItemHtml",
	    value: function getMenuItemHtml(item) {
	      var html = main_core.Tag.render(_templateObject2(), item.PICTURE ? main_core.Tag.render(_templateObject3(), item.PICTURE) : main_core.Tag.render(_templateObject4()), item.NAME, item.CLIENT_ID, main_core.Loc.getMessage('SEO_ADS_CLIENT_DISCONNECT'));
	      return html.innerHTML;
	    }
	  }, {
	    key: "getRemoveConfirmPopupHtml",
	    value: function getRemoveConfirmPopupHtml(item) {
	      return main_core.Tag.render(_templateObject5(), main_core.Loc.getMessage('SEO_ADS_CLIENT_REMOVE').replace('#NAME#', item.NAME));
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
	      var _iteratorNormalCompletion = true;
	      var _didIteratorError = false;
	      var _iteratorError = undefined;

	      try {
	        for (var _iterator = this.items[Symbol.iterator](), _step; !(_iteratorNormalCompletion = (_step = _iterator.next()).done); _iteratorNormalCompletion = true) {
	          var item = _step.value;
	          menuItems.push({
	            text: this.getMenuItemHtml(item),
	            className: "seo-ads-client-menu menu-popup-no-icon",
	            onclick: this.onSelectItem.bind(this, item)
	          });
	        }
	      } catch (err) {
	        _didIteratorError = true;
	        _iteratorError = err;
	      } finally {
	        try {
	          if (!_iteratorNormalCompletion && _iterator.return != null) {
	            _iterator.return();
	          }
	        } finally {
	          if (_didIteratorError) {
	            throw _iteratorError;
	          }
	        }
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
	      var _iteratorNormalCompletion2 = true;
	      var _didIteratorError2 = false;
	      var _iteratorError2 = undefined;

	      try {
	        for (var _iterator2 = removeClientLinks[Symbol.iterator](), _step2; !(_iteratorNormalCompletion2 = (_step2 = _iterator2.next()).done); _iteratorNormalCompletion2 = true) {
	          var removeClientLink = _step2.value;
	          main_core.Event.bind(removeClientLink, "click", function (event) {
	            event.stopPropagation();
	            var clientId = BX.data(event.target, "client-id");

	            _this.closeMenu();

	            var _iteratorNormalCompletion3 = true;
	            var _didIteratorError3 = false;
	            var _iteratorError3 = undefined;

	            try {
	              for (var _iterator3 = _this.items[Symbol.iterator](), _step3; !(_iteratorNormalCompletion3 = (_step3 = _iterator3.next()).done); _iteratorNormalCompletion3 = true) {
	                var _item = _step3.value;

	                if (_item.CLIENT_ID == clientId) {
	                  _this.confirmRemoveItem(_item);
	                }
	              }
	            } catch (err) {
	              _didIteratorError3 = true;
	              _iteratorError3 = err;
	            } finally {
	              try {
	                if (!_iteratorNormalCompletion3 && _iterator3.return != null) {
	                  _iterator3.return();
	                }
	              } finally {
	                if (_didIteratorError3) {
	                  throw _iteratorError3;
	                }
	              }
	            }
	          });
	        }
	      } catch (err) {
	        _didIteratorError2 = true;
	        _iteratorError2 = err;
	      } finally {
	        try {
	          if (!_iteratorNormalCompletion2 && _iterator2.return != null) {
	            _iterator2.return();
	          }
	        } finally {
	          if (_didIteratorError2) {
	            throw _iteratorError2;
	          }
	        }
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
