this.BX = this.BX || {};
(function (exports,main_core_events,main_loader,main_core,main_popup) {
	'use strict';

	var _templateObject;

	var PopupComponentsMakerItem = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(PopupComponentsMakerItem, _EventEmitter);

	  function PopupComponentsMakerItem() {
	    var _this;

	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, PopupComponentsMakerItem);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(PopupComponentsMakerItem).call(this));
	    _this.html = main_core.Type.isDomNode(options === null || options === void 0 ? void 0 : options.html) ? options.html : null;
	    _this.awaitContent = main_core.Type.isBoolean(options === null || options === void 0 ? void 0 : options.awaitContent) ? options === null || options === void 0 ? void 0 : options.awaitContent : null;
	    _this.flex = main_core.Type.isNumber(options === null || options === void 0 ? void 0 : options.flex) ? options.flex : null;
	    _this.withoutBackground = main_core.Type.isBoolean(options === null || options === void 0 ? void 0 : options.withoutBackground) ? options.withoutBackground : null;
	    _this.backgroundColor = main_core.Type.isString(options === null || options === void 0 ? void 0 : options.backgroundColor) ? options.backgroundColor : null;
	    _this.marginBottom = main_core.Type.isNumber(options === null || options === void 0 ? void 0 : options.marginBottom) ? options.marginBottom : null;
	    _this.disabled = main_core.Type.isBoolean(options === null || options === void 0 ? void 0 : options.disabled) ? options.disabled : null;
	    _this.overflow = main_core.Type.isBoolean(options === null || options === void 0 ? void 0 : options.overflow) ? options.overflow : null;
	    _this.displayBlock = main_core.Type.isBoolean(options === null || options === void 0 ? void 0 : options.displayBlock) ? options.displayBlock : null;
	    _this.layout = {
	      container: null
	    };

	    if (_this.awaitContent) {
	      _this["await"]();
	    }

	    return _this;
	  }

	  babelHelpers.createClass(PopupComponentsMakerItem, [{
	    key: "getLoader",
	    value: function getLoader() {
	      if (!this.loader) {
	        this.loader = new main_loader.Loader({
	          target: this.getContainer(),
	          size: 45
	        });
	      }

	      return this.loader;
	    }
	  }, {
	    key: "await",
	    value: function _await() {
	      this.getContainer().classList.add('--awaiting');
	      this.showLoader();
	    }
	  }, {
	    key: "stopAwait",
	    value: function stopAwait() {
	      this.getContainer().classList.remove('--awaiting');
	      this.hideLoader();
	    }
	  }, {
	    key: "showLoader",
	    value: function showLoader() {
	      void this.getLoader().show();
	    }
	  }, {
	    key: "hideLoader",
	    value: function hideLoader() {
	      void this.getLoader().hide();
	    }
	  }, {
	    key: "getContent",
	    value: function getContent() {
	      if (this.html) {
	        return this.html;
	      }

	      return '';
	    }
	  }, {
	    key: "updateContent",
	    value: function updateContent(node) {
	      if (main_core.Type.isDomNode(node)) {
	        main_core.Dom.clean(this.getContainer());
	        this.getContainer().appendChild(node);
	      }
	    }
	  }, {
	    key: "setBackgroundColor",
	    value: function setBackgroundColor(color) {
	      if (main_core.Type.isString(color)) {
	        this.getContainer().style.backgroundColor = color;
	      }
	    }
	  }, {
	    key: "getContainer",
	    value: function getContainer() {
	      if (!this.layout.container) {
	        this.layout.container = main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-qr-popupcomponentmaker__content--section-item\">", "</div>\n\t\t\t"])), this.getContent());

	        if (this.backgroundColor) {
	          this.layout.container.style.backgroundColor = this.backgroundColor;
	        }

	        if (this.withoutBackground && !this.backgroundColor) {
	          this.layout.container.classList.add('--transparent');
	        }

	        if (this.flex) {
	          this.layout.container.style.flex = this.flex;
	        }

	        if (this.disabled) {
	          this.layout.container.classList.add('--disabled');
	        }

	        if (this.overflow) {
	          this.layout.container.classList.add('--overflow-hidden');
	        }

	        if (this.displayBlock) {
	          this.layout.container.classList.add('--block');
	        }
	      }

	      return this.layout.container;
	    }
	  }]);
	  return PopupComponentsMakerItem;
	}(main_core_events.EventEmitter);

	var _templateObject$1, _templateObject2, _templateObject3;
	var PopupComponentsMaker = /*#__PURE__*/function () {
	  function PopupComponentsMaker() {
	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, PopupComponentsMaker);
	    this.id = main_core.Type.isString(options.id) ? options.id : null;
	    this.target = main_core.Type.isElementNode(options.target) ? options.target : null;
	    this.content = options.content || null;
	    this.contentWrapper = null;
	    this.popup = null;
	    this.loader = null;
	    this.items = [];
	    this.width = main_core.Type.isNumber(options.width) ? options.width : null;
	    this.cacheable = main_core.Type.isBoolean(options.cacheable) ? options.cacheable : true;
	  }

	  babelHelpers.createClass(PopupComponentsMaker, [{
	    key: "getItems",
	    value: function getItems() {
	      return this.items;
	    }
	  }, {
	    key: "getItem",
	    value: function getItem(item) {
	      if (item instanceof PopupComponentsMakerItem) {
	        return item;
	      }

	      item = new PopupComponentsMakerItem(item);

	      if (this.items.indexOf(item) === -1) {
	        this.items.push(item);
	      }

	      return item;
	    }
	  }, {
	    key: "getPopup",
	    value: function getPopup() {
	      if (!this.popup) {
	        var popupWidth = this.width ? this.width : 350;
	        var popupId = this.id ? this.id + '-popup' : null;
	        this.popup = new main_popup.Popup(popupId, this.target, {
	          className: 'ui-qr-popupcomponentmaker',
	          // background: 'transparent',
	          contentBackground: 'transparent',
	          angle: {
	            offset: popupWidth / 2 - 16
	          },
	          width: popupWidth,
	          offsetLeft: -(popupWidth / 2) + (this.target ? this.target.offsetWidth / 2 : 0) + 40,
	          autoHide: true,
	          closeByEsc: true,
	          padding: 13,
	          animation: 'fading-slide',
	          content: this.getContentWrapper(),
	          cacheable: this.cacheable
	        });
	        this.popup.getContentContainer().style.overflowX = null;
	      }

	      return this.popup;
	    }
	  }, {
	    key: "isShown",
	    value: function isShown() {
	      return this.getPopup().isShown();
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "getContentWrapper",
	    value: function getContentWrapper() {
	      var _this = this;

	      if (!this.contentWrapper) {
	        this.contentWrapper = main_core.Tag.render(_templateObject$1 || (_templateObject$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-qr-popupcomponentmaker__content\"></div>\n\t\t\t"])));

	        if (!this.content) {
	          return;
	        }

	        this.content.map(function (item) {
	          var _item$html;

	          var sectionNode = _this.getSection();

	          if (item !== null && item !== void 0 && item.marginBottom) {
	            main_core.Type.isNumber(item.marginBottom) ? sectionNode.style.marginBottom = item.marginBottom + 'px' : null;
	          }

	          if (main_core.Type.isDomNode(item === null || item === void 0 ? void 0 : item.html)) {
	            sectionNode.appendChild(_this.getItem(item).getContainer());

	            _this.contentWrapper.appendChild(sectionNode);
	          }

	          if (main_core.Type.isArray(item === null || item === void 0 ? void 0 : item.html)) {
	            var innerSection = main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t\t<div class=\"ui-qr-popupcomponentmaker__content--section-item --flex-column --transparent\"></div>\n\t\t\t\t\t"])));
	            item.html.map(function (itemObj) {
	              var _itemObj$html;

	              if (itemObj !== null && itemObj !== void 0 && (_itemObj$html = itemObj.html) !== null && _itemObj$html !== void 0 && _itemObj$html.then) {
	                _this.adjustPromise(itemObj, sectionNode);

	                main_core.Type.isNumber(itemObj === null || itemObj === void 0 ? void 0 : itemObj.marginBottom) ? sectionNode.style.marginBottom = itemObj.marginBottom + 'px' : null;
	              } else {
	                if (main_core.Type.isArray(itemObj === null || itemObj === void 0 ? void 0 : itemObj.html)) {
	                  itemObj.html.map(function (itemInner) {
	                    innerSection.appendChild(_this.getItem(itemInner).getContainer());
	                  });
	                  sectionNode.appendChild(innerSection);
	                } else {
	                  sectionNode.appendChild(_this.getItem(itemObj).getContainer());
	                }
	              }
	            });

	            _this.contentWrapper.appendChild(sectionNode);
	          }

	          if (main_core.Type.isFunction(item === null || item === void 0 ? void 0 : (_item$html = item.html) === null || _item$html === void 0 ? void 0 : _item$html.then)) {
	            _this.adjustPromise(item, sectionNode);

	            _this.contentWrapper.appendChild(sectionNode);
	          }
	        });
	      }

	      return this.contentWrapper;
	    }
	  }, {
	    key: "adjustPromise",
	    value: function adjustPromise(item, sectionNode) {
	      item.awaitContent = true;
	      var itemObj = this.getItem(item);

	      if (sectionNode) {
	        var _item$html2;

	        sectionNode.appendChild(itemObj.getContainer());
	        item === null || item === void 0 ? void 0 : (_item$html2 = item.html) === null || _item$html2 === void 0 ? void 0 : _item$html2.then(function (node) {
	          if (main_core.Type.isDomNode(node)) {
	            itemObj.stopAwait();
	            itemObj.updateContent(node);
	          }
	        });
	      }
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "getSection",
	    value: function getSection() {
	      return main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"ui-qr-popupcomponentmaker__content--section\"></div>\n\t\t"])));
	    }
	  }, {
	    key: "show",
	    value: function show() {
	      if (!main_core.Type.isDomNode(this.target)) {
	        return;
	      }

	      this.getPopup().show();
	    }
	  }, {
	    key: "close",
	    value: function close() {
	      this.getPopup().close();
	    }
	  }]);
	  return PopupComponentsMaker;
	}();

	exports.PopupComponentsMaker = PopupComponentsMaker;

}((this.BX.UI = this.BX.UI || {}),BX.Event,BX,BX,BX.Main));
//# sourceMappingURL=popupcomponentsmaker.bundle.js.map
