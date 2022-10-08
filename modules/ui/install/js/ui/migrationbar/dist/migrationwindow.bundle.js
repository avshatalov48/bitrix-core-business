this.BX = this.BX || {};
(function (exports,main_core,ui_buttons,main_popup) {
	'use strict';

	var _templateObject, _templateObject2, _templateObject3, _templateObject4, _templateObject5, _templateObject6, _templateObject7, _templateObject8, _templateObject9, _templateObject10;

	var MigrationBar = /*#__PURE__*/function () {
	  function MigrationBar(_ref) {
	    var target = _ref.target,
	        title = _ref.title,
	        items = _ref.items,
	        buttons = _ref.buttons,
	        link = _ref.link,
	        hint = _ref.hint,
	        wrapperWidth = _ref.wrapperWidth,
	        wrapperHeight = _ref.wrapperHeight,
	        containerWidth = _ref.containerWidth,
	        containerHeight = _ref.containerHeight;
	    babelHelpers.classCallCheck(this, MigrationBar);
	    this.target = main_core.Type.isDomNode(target) ? target : null;
	    this.title = main_core.Type.isString(title) ? title : null;
	    this.items = main_core.Type.isArray(items) ? items : [];
	    this.buttons = main_core.Type.isArray(buttons) ? buttons : null;
	    this.link = main_core.Type.isObject(link) ? link : null;
	    this.hint = main_core.Type.isString(hint) ? hint : null;
	    this.wrapperWidth = main_core.Type.isNumber(wrapperWidth) ? wrapperWidth : null;
	    this.wrapperHeight = main_core.Type.isNumber(wrapperHeight) ? wrapperHeight : null;
	    this.containerWidth = main_core.Type.isNumber(containerWidth) ? containerWidth : null;
	    this.containerHeight = main_core.Type.isNumber(containerHeight) ? containerHeight : null;
	    this.layout = {
	      wrapper: null,
	      container: null,
	      title: null,
	      text: null
	    };
	    this.wrapper = null;
	    this.container = null;
	    this.titleContainer = null;
	    this.titleText = null;
	    this.cross = null;
	    this.itemContainer = null;
	    this.buttonsContainer = null;
	    this.itemNode = null;
	    this.linkNode = null;
	    this.hintWindow = null;
	    this.hintNode = null;
	    this.linkShown = true;
	  }

	  babelHelpers.createClass(MigrationBar, [{
	    key: "getWrapper",
	    value: function getWrapper() {
	      if (!this.wrapper) {
	        this.wrapper = main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-migration-bar__wrap\"></div>\n\t\t\t"])));
	      }

	      return this.wrapper;
	    }
	  }, {
	    key: "getContainer",
	    value: function getContainer() {
	      if (!this.container) {
	        this.container = main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-migration-bar__container ui-migration-window__scope\">\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t</div>\t\n\t\t\t"])), this.getCross(), this.title ? this.getTitle() : '', this.getItemContainer(), this.getButtonsContainer());
	      }

	      return this.container;
	    }
	  }, {
	    key: "getTitle",
	    value: function getTitle() {
	      if (!this.titleContainer) {
	        this.titleContainer = main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-migration-bar__title\">\n\t\t\t\t\t<div class=\"ui-migration-bar__title-name\">", "</div>\n\t\t\t\t</div>\n\t\t\t"])), this.title);
	      }

	      return this.titleContainer;
	    }
	  }, {
	    key: "getTitleText",
	    value: function getTitleText() {
	      if (!this.titleText) {
	        this.titleText = main_core.Tag.render(_templateObject4 || (_templateObject4 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-migration-bar__title-name\">", "</div>\n\t\t\t"])), this.title);
	      }

	      return this.titleText;
	    }
	  }, {
	    key: "getCross",
	    value: function getCross() {
	      if (!this.cross) {
	        this.cross = main_core.Tag.render(_templateObject5 || (_templateObject5 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-migration-bar__cross\">\n\t\t\t\t\t<div class=\"ui-migration-bar__cross-icon\"></div>\n\t\t\t\t</div>\n\t\t\t"])));
	      }

	      return this.cross;
	    }
	  }, {
	    key: "getButtonsContainer",
	    value: function getButtonsContainer() {
	      if (!this.buttonsContainer) {
	        this.buttonsContainer = main_core.Tag.render(_templateObject6 || (_templateObject6 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-migration-bar__btn-container\"></div>\n\t\t\t"])));
	      }

	      return this.buttonsContainer;
	    }
	  }, {
	    key: "getItemContainer",
	    value: function getItemContainer() {
	      if (!this.itemContainer) {
	        this.itemContainer = main_core.Tag.render(_templateObject7 || (_templateObject7 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-migration-bar__item-container\"></div>\n\t\t\t"])));
	      }

	      return this.itemContainer;
	    }
	  }, {
	    key: "getImage",
	    value: function getImage() {
	      return this.items;
	    }
	  }, {
	    key: "getLink",
	    value: function getLink() {
	      if (!this.linkNode) {
	        this.linkNode = main_core.Tag.render(_templateObject8 || (_templateObject8 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<a class=\"ui-migration-bar__link\">", "</a>\n\t\t\t"])), this.link ? this.link.text : '');
	      }

	      return this.linkNode;
	    }
	  }, {
	    key: "getHintIcon",
	    value: function getHintIcon() {
	      if (!this.hintNode) {
	        this.hintNode = main_core.Tag.render(_templateObject9 || (_templateObject9 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-migration-bar__hint-icon\"></div>\n\t\t\t"])));
	      }

	      return this.hintNode;
	    }
	  }, {
	    key: "adjustHint",
	    value: function adjustHint() {
	      if (this.hint) {
	        main_core.Dom.append(this.getHintIcon(), this.getTitleText());
	        this.hintWindow = new main_popup.Popup('ui-migration-window-hint', this.getHintIcon(), {
	          content: this.hint,
	          lightShadow: true,
	          autoHide: true,
	          closeByEsc: true,
	          darkMode: true,
	          offsetLeft: this.getHintIcon().offsetWidth / 2,
	          angle: {
	            postion: 'top'
	          }
	        });
	      }
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
	    key: "adjustButton",
	    value: function adjustButton() {
	      var _this = this;

	      if (this.buttons !== []) {
	        this.buttons.forEach(function (button) {
	          button = new ui_buttons.Button({
	            id: button.id,
	            color: button.color ? ui_buttons.Button.Color[button.color] : ui_buttons.Button.Color.PRIMARY,
	            size: button.size ? ui_buttons.Button.Size[button.size] : ui_buttons.Button.Size.MEDIUM,
	            round: button.round ? button.round : false,
	            text: button.title,
	            events: button.events
	          });

	          _this.getButtonsContainer().appendChild(button.render());
	        });
	      }
	    }
	  }, {
	    key: "setEvents",
	    value: function setEvents() {
	      var _this2 = this;

	      this.getCross().addEventListener('click', function () {
	        _this2.getWrapper().style.display = 'none';

	        _this2.getWrapper().remove();
	      });

	      if (this.hint) {
	        this.getHintIcon().addEventListener('mouseover', function () {
	          _this2.hintWindow.show();
	        });
	      }

	      if (this.link) {
	        this.linkNode.setAttribute('href', this.link.href);
	        this.linkNode.setAttribute('target', this.link.target);
	        this.linkNode.addEventListener('click', function () {
	          _this2.link.target.event.click();
	        });
	      }
	    }
	  }, {
	    key: "render",
	    value: function render() {
	      var _this3 = this;

	      if (this.target) {
	        this.target.appendChild(this.getWrapper());
	        this.getWrapper().appendChild(this.getContainer());

	        if (this.hint) {
	          this.adjustHint();
	        }

	        if (this.wrapperWidth) {
	          this.getWrapper().style.minWidth = this.wrapperWidth + 'px';
	        }

	        if (this.wrapperHeight) {
	          this.getWrapper().style.minHeight = this.wrapperHeight + 'px';
	        }

	        if (this.containerWidth) {
	          this.getContainer().style.minWidth = this.containerWidth + 'px';
	        }

	        if (this.containerHeight) {
	          this.getContainer().style.minHeight = this.containerHeight + 'px';
	        }
	      }

	      if (this.items !== []) {
	        this.items.forEach(function (item) {
	          var itemNode = item;
	          itemNode = main_core.Tag.render(_templateObject10 || (_templateObject10 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<img class=\"ui-migration-bar__item\">\n\t\t\t"])));

	          _this3.getItemContainer().appendChild(itemNode);

	          var itemKeys = Object.keys(item);

	          for (var i = 0; i < itemKeys.length; i++) {
	            var event = itemKeys[i];
	            itemNode.setAttribute(event, item[event]);
	          }
	        });
	      }

	      if (this.link) {
	        main_core.Dom.append(this.getLink(), this.getItemContainer());
	      }
	    }
	  }, {
	    key: "init",
	    value: function init() {
	      this.adjustItemData();
	      this.adjustButton();
	      this.render();
	      this.setEvents();
	    }
	  }]);
	  return MigrationBar;
	}();

	exports.MigrationBar = MigrationBar;

}((this.BX.UI = this.BX.UI || {}),BX,BX.UI,BX.Main));
//# sourceMappingURL=migrationwindow.bundle.js.map
