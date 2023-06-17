this.BX = this.BX || {};
(function (exports,main_popup,main_core_events,main_core,main_loader) {
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
	    _this.backgroundImage = main_core.Type.isString(options === null || options === void 0 ? void 0 : options.backgroundImage) ? options.backgroundImage : null;
	    _this.marginBottom = main_core.Type.isNumber(options === null || options === void 0 ? void 0 : options.marginBottom) ? options.marginBottom : null;
	    _this.disabled = main_core.Type.isBoolean(options === null || options === void 0 ? void 0 : options.disabled) ? options.disabled : null;
	    _this.secondary = main_core.Type.isBoolean(options === null || options === void 0 ? void 0 : options.secondary) ? options.secondary : null;
	    _this.overflow = main_core.Type.isBoolean(options === null || options === void 0 ? void 0 : options.overflow) ? options.overflow : null;
	    _this.displayBlock = main_core.Type.isBoolean(options === null || options === void 0 ? void 0 : options.displayBlock) ? options.displayBlock : null;
	    _this.attrs = main_core.Type.isPlainObject(options === null || options === void 0 ? void 0 : options.attrs) ? options.attrs : null;
	    _this.minHeight = main_core.Type.isString(options === null || options === void 0 ? void 0 : options.minHeight) ? options.minHeight : null;
	    _this.sizeLoader = main_core.Type.isNumber(options === null || options === void 0 ? void 0 : options.sizeLoader) ? options.sizeLoader : 45;
	    _this.asyncSecondary = (options === null || options === void 0 ? void 0 : options.asyncSecondary) instanceof Promise ? options.asyncSecondary : null;
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
	          size: this.sizeLoader
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
	    key: "getMarginBottom",
	    value: function getMarginBottom() {
	      return this.marginBottom;
	    }
	  }, {
	    key: "getContainer",
	    value: function getContainer() {
	      var _this2 = this;
	      if (!this.layout.container) {
	        this.layout.container = main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-popupcomponentmaker__content--section-item\">", "</div>\n\t\t\t"])), this.getContent());
	        if (this.backgroundColor) {
	          this.layout.container.style.backgroundColor = this.backgroundColor;
	        }
	        if (this.backgroundImage) {
	          this.layout.container.style.backgroundImage = this.backgroundImage;
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
	        if (this.disabled) {
	          this.layout.container.classList.add('--disabled');
	        }
	        if (this.secondary) {
	          main_core.Dom.addClass(this.layout.container, '--secondary');
	        }
	        if (this.overflow) {
	          this.layout.container.classList.add('--overflow-hidden');
	        }
	        if (this.displayBlock) {
	          this.layout.container.classList.add('--block');
	        }
	        if (this.attrs) {
	          main_core.Dom.adjust(this.layout.container, {
	            attrs: this.attrs
	          });
	        }
	        if (this.minHeight) {
	          main_core.Dom.style(this.layout.container, 'min-height', this.minHeight);
	        }
	        if (this.asyncSecondary) {
	          this.asyncSecondary.then(function (secondary) {
	            if (secondary === false) {
	              main_core.Dom.removeClass(_this2.layout.container, '--secondary');
	            } else {
	              main_core.Dom.addClass(_this2.layout.container, '--secondary');
	            }
	          });
	        }
	      }
	      return this.layout.container;
	    }
	  }]);
	  return PopupComponentsMakerItem;
	}(main_core_events.EventEmitter);

	var _templateObject$1, _templateObject2, _templateObject3;
	var PopupComponentsMaker = /*#__PURE__*/function () {
	  function PopupComponentsMaker(_ref) {
	    var id = _ref.id,
	      target = _ref.target,
	      content = _ref.content,
	      width = _ref.width,
	      cacheable = _ref.cacheable,
	      contentPadding = _ref.contentPadding,
	      padding = _ref.padding,
	      blurBackground = _ref.blurBackground;
	    babelHelpers.classCallCheck(this, PopupComponentsMaker);
	    this.id = main_core.Type.isString(id) ? id : null;
	    this.target = main_core.Type.isElementNode(target) ? target : null;
	    this.content = content || null;
	    this.contentWrapper = null;
	    this.popup = null;
	    this.loader = null;
	    this.items = [];
	    this.width = main_core.Type.isNumber(width) ? width : null;
	    this.cacheable = main_core.Type.isBoolean(cacheable) ? cacheable : true;
	    this.contentPadding = main_core.Type.isNumber(contentPadding) ? contentPadding : 0;
	    this.padding = main_core.Type.isNumber(padding) ? padding : 13;
	    this.blurBlackground = main_core.Type.isBoolean(blurBackground) ? blurBackground : false;
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
	      var _this = this;
	      if (!this.popup) {
	        var popupWidth = this.width ? this.width : 350;
	        var popupId = this.id ? this.id + '-popup' : null;
	        this.popup = new main_popup.Popup(popupId, this.target, {
	          className: 'ui-popupcomponentmaker',
	          contentBackground: 'transparent',
	          contentPadding: this.contentPadding,
	          angle: {
	            offset: popupWidth / 2 - 16
	          },
	          width: popupWidth,
	          offsetLeft: -(popupWidth / 2) + (this.target ? this.target.offsetWidth / 2 : 0) + 40,
	          autoHide: true,
	          closeByEsc: true,
	          padding: this.padding,
	          animation: 'fading-slide',
	          content: this.getContentWrapper(),
	          cacheable: this.cacheable
	        });
	        if (this.blurBlackground) {
	          main_core.Dom.addClass(this.popup.getPopupContainer(), 'popup-with-radius');
	          this.setBlurBackground();
	          main_core_events.EventEmitter.subscribe(main_core_events.EventEmitter.GLOBAL_TARGET, 'BX.Intranet.Bitrix24:ThemePicker:onThemeApply', function () {
	            setTimeout(function () {
	              _this.setBlurBackground();
	            }, 200);
	          });
	        }
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
	      var _this2 = this;
	      if (!this.contentWrapper) {
	        this.contentWrapper = main_core.Tag.render(_templateObject$1 || (_templateObject$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-popupcomponentmaker__content\"></div>\n\t\t\t"])));
	        if (!this.content) {
	          return;
	        }
	        this.content.map(function (item) {
	          var _item$html;
	          var sectionNode = _this2.getSection();
	          if (item !== null && item !== void 0 && item.marginBottom) {
	            main_core.Type.isNumber(item.marginBottom) ? sectionNode.style.marginBottom = item.marginBottom + 'px' : null;
	          }
	          if (item !== null && item !== void 0 && item.className) {
	            main_core.Dom.addClass(sectionNode, item.className);
	          }
	          if (item !== null && item !== void 0 && item.attrs) {
	            main_core.Dom.adjust(sectionNode, {
	              attrs: item.attrs
	            });
	          }
	          if (main_core.Type.isDomNode(item === null || item === void 0 ? void 0 : item.html)) {
	            sectionNode.appendChild(_this2.getItem(item).getContainer());
	            _this2.contentWrapper.appendChild(sectionNode);
	          }
	          if (main_core.Type.isArray(item === null || item === void 0 ? void 0 : item.html)) {
	            var innerSection = main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t\t<div class=\"ui-popupcomponentmaker__content--section-item --flex-column --transparent\"></div>\n\t\t\t\t\t"])));
	            item.html.map(function (itemObj) {
	              var _itemObj$html;
	              if (itemObj !== null && itemObj !== void 0 && (_itemObj$html = itemObj.html) !== null && _itemObj$html !== void 0 && _itemObj$html.then) {
	                _this2.adjustPromise(itemObj, sectionNode);
	                main_core.Type.isNumber(itemObj === null || itemObj === void 0 ? void 0 : itemObj.marginBottom) ? sectionNode.style.marginBottom = itemObj.marginBottom + 'px' : null;
	              } else {
	                if (main_core.Type.isArray(itemObj === null || itemObj === void 0 ? void 0 : itemObj.html)) {
	                  itemObj.html.map(function (itemInner) {
	                    innerSection.appendChild(_this2.getItem(itemInner).getContainer());
	                  });
	                  sectionNode.appendChild(innerSection);
	                } else {
	                  sectionNode.appendChild(_this2.getItem(itemObj).getContainer());
	                }
	              }
	            });
	            _this2.contentWrapper.appendChild(sectionNode);
	          }
	          if (main_core.Type.isFunction(item === null || item === void 0 ? void 0 : (_item$html = item.html) === null || _item$html === void 0 ? void 0 : _item$html.then)) {
	            _this2.adjustPromise(item, sectionNode);
	            _this2.contentWrapper.appendChild(sectionNode);
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
	      return main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"ui-popupcomponentmaker__content--section\"></div>\n\t\t"])));
	    }
	  }, {
	    key: "setBlurBackground",
	    value: function setBlurBackground() {
	      var container = this.getPopup().getPopupContainer();
	      var windowStyles = window.getComputedStyle(document.body);
	      var backgroundImage = windowStyles.backgroundImage;
	      var backgroundColor = windowStyles.backgroundColor;
	      if (main_core.Type.isDomNode(container)) {
	        main_core.Dom.addClass(container, 'popup-window-blur');
	      }
	      var blurStyle = main_core.Dom.create('style', {
	        attrs: {
	          type: 'text/css',
	          id: 'styles-widget-blur'
	        }
	      });
	      var styles = '.popup-window-content:after { ' + 'background-image: ' + backgroundImage + ';' + 'background-color: ' + backgroundColor + '} ';
	      styles = document.createTextNode(styles);
	      blurStyle.appendChild(styles);
	      var stylesWithAngle = '.popup-window-angly:after { ' + 'background-color: ' + backgroundColor + '} ';
	      stylesWithAngle = document.createTextNode(stylesWithAngle);
	      blurStyle.appendChild(stylesWithAngle);
	      var headStyle = document.head.querySelector('#styles-widget-blur');
	      if (headStyle) {
	        main_core.Dom.replace(headStyle, blurStyle);
	      } else {
	        document.head.appendChild(blurStyle);
	      }
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

	exports.PopupComponentsMakerItem = PopupComponentsMakerItem;
	exports.PopupComponentsMaker = PopupComponentsMaker;

}((this.BX.UI = this.BX.UI || {}),BX.Main,BX.Event,BX,BX));
//# sourceMappingURL=popupcomponentsmaker.bundle.js.map
