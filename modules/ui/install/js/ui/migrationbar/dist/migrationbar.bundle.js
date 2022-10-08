this.BX = this.BX || {};
(function (exports,main_core,ui_buttons,main_popup) {
	'use strict';

	var _templateObject, _templateObject2, _templateObject3, _templateObject4, _templateObject5, _templateObject6, _templateObject7, _templateObject8, _templateObject9;

	var MigrationBar = /*#__PURE__*/function () {
	  function MigrationBar(_ref) {
	    var target = _ref.target,
	        title = _ref.title,
	        cross = _ref.cross,
	        items = _ref.items,
	        buttons = _ref.buttons,
	        link = _ref.link,
	        hint = _ref.hint,
	        width = _ref.width,
	        height = _ref.height,
	        minWidth = _ref.minWidth,
	        minHeight = _ref.minHeight;
	    babelHelpers.classCallCheck(this, MigrationBar);
	    this.target = main_core.Type.isDomNode(target) ? target : null;
	    this.title = main_core.Type.isString(title) || main_core.Type.isObject(title) ? title : null;
	    this.cross = main_core.Type.isBoolean(cross) ? cross : true;
	    this.items = main_core.Type.isArray(items) ? items : [];
	    this.buttons = main_core.Type.isArray(buttons) ? buttons : null;
	    this.link = main_core.Type.isObject(link) ? link : null;
	    this.hint = main_core.Type.isString(hint) ? hint : null;
	    this.width = main_core.Type.isNumber(width) ? width : null;
	    this.height = main_core.Type.isNumber(height) ? height : null;
	    this.minWidth = main_core.Type.isNumber(minWidth) ? minWidth : null;
	    this.minHeight = main_core.Type.isNumber(minHeight) ? minHeight : null;
	    this.layout = {
	      wrapper: null,
	      container: null,
	      items: null,
	      title: null,
	      text: null,
	      link: null,
	      remove: null,
	      buttons: null
	    };
	    this.popupHint = null;
	  }

	  babelHelpers.createClass(MigrationBar, [{
	    key: "getWrapper",
	    value: function getWrapper() {
	      if (!this.layout.wrapper) {
	        this.layout.wrapper = main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-migration-bar__wrap\"></div>\n\t\t\t"])));
	      }

	      return this.layout.wrapper;
	    }
	  }, {
	    key: "getContainer",
	    value: function getContainer() {
	      var _this = this;

	      if (!this.layout.container) {
	        this.layout.container = main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-migration-bar__container ui-migration-bar__scope --show\">\n\t\t\t\t\t", "\n\t\t\t\t\t<div class=\"ui-migration-bar__content\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"])), this.cross ? this.getCross() : '', this.title ? this.getTitle() : '', this.getItemContainer(), this.getButtonsContainer());
	        this.layout.container.addEventListener('animationend', function () {
	          _this.layout.container.classList.remove('--show');
	        }, {
	          once: true
	        });

	        if (this.width) {
	          this.layout.container.style.setProperty('width', this.width + 'px');
	        }

	        if (this.height) {
	          this.layout.container.style.setProperty('height', this.height + 'px');
	        }

	        if (this.minWidth) {
	          this.layout.container.style.setProperty('min-width', this.minWidth + 'px');
	        }

	        if (this.minHeight) {
	          this.layout.container.style.setProperty('min-height', this.minHeight + 'px');
	        }
	      }

	      return this.layout.container;
	    }
	  }, {
	    key: "getTitle",
	    value: function getTitle() {
	      if (!this.layout.title) {
	        var _this$title, _this$title2;

	        var isTitleObject = main_core.Type.isObject(this.title);
	        var titleText = isTitleObject ? (_this$title = this.title) === null || _this$title === void 0 ? void 0 : _this$title.text : this.title;
	        var alignTitle = isTitleObject ? (_this$title2 = this.title) === null || _this$title2 === void 0 ? void 0 : _this$title2.align : null;
	        this.layout.title = main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-migration-bar__title ", "\">\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"])), alignTitle ? '--align-' + alignTitle : '', titleText, this.hint ? this.getHint() : '');
	      }

	      return this.layout.title;
	    }
	  }, {
	    key: "getCross",
	    value: function getCross() {
	      var _this2 = this;

	      if (!this.layout.remove) {
	        this.layout.remove = main_core.Tag.render(_templateObject4 || (_templateObject4 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-migration-bar__remove\">\n\t\t\t\t\t<div class=\"ui-migration-bar__remove-icon\"></div>\n\t\t\t\t</div>\n\t\t\t"])));
	        this.layout.remove.addEventListener('click', function () {
	          return _this2.remove();
	        });
	      }

	      return this.layout.remove;
	    }
	  }, {
	    key: "getButtonsContainer",
	    value: function getButtonsContainer() {
	      if (!this.layout.buttons) {
	        this.layout.buttons = main_core.Tag.render(_templateObject5 || (_templateObject5 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-migration-bar__btn-container\"></div>\n\t\t\t"])));
	      }

	      return this.layout.buttons;
	    }
	  }, {
	    key: "getItemContainer",
	    value: function getItemContainer() {
	      if (!this.layout.items) {
	        this.layout.items = main_core.Tag.render(_templateObject6 || (_templateObject6 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-migration-bar__item-container\"></div>\n\t\t\t"])));
	      }

	      return this.layout.items;
	    }
	  }, {
	    key: "getImage",
	    value: function getImage() {
	      return this.items;
	    }
	  }, {
	    key: "getLink",
	    value: function getLink() {
	      var _this3 = this;

	      if (!this.layout.link) {
	        var _this$link;

	        var linkNode = (_this$link = this.link) !== null && _this$link !== void 0 && _this$link.href ? 'a' : 'div';
	        this.layout.link = main_core.Tag.render(_templateObject7 || (_templateObject7 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<", " class=\"ui-migration-bar__link\">", "</", ">\n\t\t\t"])), linkNode, this.link.text, linkNode);

	        var setCursorPointerMode = function setCursorPointerMode() {
	          _this3.layout.link.classList.add('--cursor-pointer');
	        };

	        if (this.link.href) {
	          setCursorPointerMode();
	          this.layout.link.href = this.link.href;
	        }

	        if (this.link.target) {
	          this.layout.link.target = this.link.target;
	        }

	        if (this.link.events) {
	          setCursorPointerMode();
	          var eventKeys = Object.keys(this.link.events);
	          eventKeys.forEach(function (event) {
	            _this3.layout.link.addEventListener(event, function () {
	              _this3.link.events[event]();
	            });
	          });
	        }
	      }

	      return this.layout.link;
	    }
	  }, {
	    key: "getHint",
	    value: function getHint() {
	      var _this4 = this;

	      if (!this.layout.hint) {
	        this.layout.hint = main_core.Tag.render(_templateObject8 || (_templateObject8 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-migration-bar__hint\">\n\t\t\t\t\t<div class=\"ui-migration-bar__hint-icon\"></div>\n\t\t\t\t</div>\n\t\t\t"])));
	        var popupHintWidth = 200;
	        var hintIconWidth = 20;
	        this.popupHint = new main_popup.Popup(null, this.layout.hint, {
	          darkMode: true,
	          content: this.hint,
	          angle: {
	            offset: popupHintWidth / 2 - 16
	          },
	          width: popupHintWidth,
	          offsetLeft: -(popupHintWidth / 2) + hintIconWidth / 2 + 40,
	          animation: 'fading-slide'
	        });
	        this.layout.hint.addEventListener('mouseover', function () {
	          _this4.popupHint.show();
	        });
	        this.layout.hint.addEventListener('mouseleave', function () {
	          _this4.popupHint.close();
	        });
	      }

	      return this.layout.hint;
	    }
	  }, {
	    key: "adjustItemData",
	    value: function adjustItemData() {
	      this.items = this.items.map(function (item) {
	        return {
	          id: item.id ? item.id : null,
	          src: item.src ? item.src : null,
	          events: item.events ? item.events : null
	        };
	      });
	    }
	  }, {
	    key: "setButtons",
	    value: function setButtons() {
	      var _this5 = this;

	      if (this.buttons.length > 0) {
	        this.buttons.forEach(function (button) {
	          var option = Object.assign({}, button);
	          button = new ui_buttons.Button(option);

	          _this5.getButtonsContainer().appendChild(button.render());
	        });
	      }
	    }
	  }, {
	    key: "render",
	    value: function render() {
	      var _this6 = this,
	          _this$link2;

	      if (this.target) {
	        this.getWrapper().style.setProperty('height', this.target.offsetHeight + 'px');
	        this.target.appendChild(this.getWrapper());
	        this.getWrapper().appendChild(this.getContainer());
	      }

	      if (this.items.length > 0) {
	        this.items.forEach(function (item) {
	          var itemNode = item;
	          itemNode = main_core.Tag.render(_templateObject9 || (_templateObject9 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<img class=\"ui-migration-bar__item\">\n\t\t\t\t"])));

	          _this6.getItemContainer().appendChild(itemNode);

	          var itemKeys = Object.keys(item);

	          for (var i = 0; i < itemKeys.length; i++) {
	            var event = itemKeys[i];
	            itemNode.setAttribute(event, item[event]);
	          }
	        });
	      }

	      if ((_this$link2 = this.link) !== null && _this$link2 !== void 0 && _this$link2.text) {
	        this.getItemContainer().appendChild(this.getLink());
	      }
	    }
	  }, {
	    key: "remove",
	    value: function remove() {
	      var _this7 = this;

	      this.getContainer().classList.add('--close');
	      this.getContainer().addEventListener('animationend', function () {
	        _this7.getContainer().classList.remove('--close');

	        _this7.getContainer().remove();

	        _this7.getWrapper().remove();
	      }, {
	        once: true
	      });
	    }
	  }, {
	    key: "show",
	    value: function show() {
	      this.adjustItemData();
	      this.setButtons();
	      this.render();
	    }
	  }]);
	  return MigrationBar;
	}();

	exports.MigrationBar = MigrationBar;

}((this.BX.UI = this.BX.UI || {}),BX,BX.UI,BX.Main));
//# sourceMappingURL=migrationbar.bundle.js.map
