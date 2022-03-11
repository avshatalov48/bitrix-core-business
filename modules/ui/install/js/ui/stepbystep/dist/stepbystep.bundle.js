this.BX = this.BX || {};
(function (exports,main_core_events,ui_hint,main_core) {
	'use strict';

	var _templateObject, _templateObject2, _templateObject3, _templateObject4, _templateObject5;

	var StepByStepItem = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(StepByStepItem, _EventEmitter);

	  function StepByStepItem() {
	    var _this;

	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    var number = arguments.length > 1 ? arguments[1] : undefined;
	    babelHelpers.classCallCheck(this, StepByStepItem);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(StepByStepItem).call(this));
	    _this.header = options === null || options === void 0 ? void 0 : options.header;
	    _this.node = options === null || options === void 0 ? void 0 : options.node;
	    _this.number = number;
	    _this.isFirst = (options === null || options === void 0 ? void 0 : options.isFirst) || '';
	    _this.isLast = (options === null || options === void 0 ? void 0 : options.isLast) || '';
	    _this["class"] = main_core.Type.isString(options === null || options === void 0 ? void 0 : options.nodeClass) ? options.nodeClass : null;
	    _this.backgroundColor = main_core.Type.isString(options === null || options === void 0 ? void 0 : options.backgroundColor) ? options.backgroundColor : null;
	    _this.layout = {
	      container: null
	    };
	    return _this;
	  }

	  babelHelpers.createClass(StepByStepItem, [{
	    key: "getHeader",
	    value: function getHeader() {
	      if (main_core.Type.isString(this.header)) {
	        return main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-stepbystep__section-item--title\">", "</div>\n\t\t\t"])), this.header);
	      }

	      if (main_core.Type.isObject(this.header)) {
	        var titleWrapper = main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-stepbystep__section-item--title\">\n\n\t\t\t\t</div>\n\t\t\t"])));

	        if (this.header.title) {
	          titleWrapper.innerText = this.header.title;
	        }

	        if (main_core.Type.isString(this.header.hint)) {
	          var hintNode = main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<span data-hint=\"", "\" class=\"ui-hint ui-stepbystep__section-item--hint\">\n\t\t\t\t\t\t<i class=\"ui-hint-icon\"></i>\n\t\t\t\t\t</span>\n\t\t\t\t"])), this.header.hint);
	          titleWrapper.appendChild(hintNode);
	          this.initHint(titleWrapper);
	        }

	        return titleWrapper;
	      }

	      return '';
	    }
	  }, {
	    key: "initHint",
	    value: function initHint(node) {
	      BX.UI.Hint.init(node);
	    }
	  }, {
	    key: "getContent",
	    value: function getContent() {
	      if (this.node) {
	        return main_core.Tag.render(_templateObject4 || (_templateObject4 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-stepbystep__section-item--content\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"])), this.node);
	      }

	      return '';
	    }
	  }, {
	    key: "getContainer",
	    value: function getContainer() {
	      if (!this.layout.container) {
	        this.layout.container = main_core.Tag.render(_templateObject5 || (_templateObject5 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-stepbystep__section-item\">\n\t\t\t\t\t<div class=\"ui-stepbystep__section-item--counter\">\n\t\t\t\t\t\t<div class=\"ui-stepbystep__section-item--counter-number \n\t\t\t\t\t\t\t", " ", " ", "\">\n\t\t\t\t\t\t\t<span>", "</span>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"ui-stepbystep__section-item--information\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t"])), this.counterClass, this.isFirst, this.isLast, this.number, this.getHeader(), this.getContent());

	        if (this.backgroundColor) {
	          this.layout.container.style.backgroundColor = this.backgroundColor;
	        }

	        if (this["class"]) {
	          this.layout.container.classList.add(this["class"]);
	        }
	      }

	      return this.layout.container;
	    }
	  }]);
	  return StepByStepItem;
	}(main_core_events.EventEmitter);

	var _templateObject$1;
	var StepByStep = /*#__PURE__*/function () {
	  function StepByStep() {
	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, StepByStep);
	    this.target = options.target || null;
	    this.content = options.content || null;
	    this.contentWrapper = null;
	    this.items = [];
	    this.counter = 0;
	  }

	  babelHelpers.createClass(StepByStep, [{
	    key: "getItem",
	    value: function getItem(item) {
	      if (item instanceof StepByStepItem) {
	        return item;
	      }

	      this.counter++;

	      if (this.counter === 1) {
	        item.isFirst = '--first';
	      }

	      if (this.counter === this.content.length) {
	        item.isLast = '--last';
	      }

	      item = new StepByStepItem(item, this.counter);

	      if (this.items.indexOf(item) === -1) {
	        this.items.push(item);
	      }

	      return item;
	    }
	  }, {
	    key: "getContentWrapper",
	    value: function getContentWrapper() {
	      var _this = this;

	      if (!this.contentWrapper) {
	        this.contentWrapper = main_core.Tag.render(_templateObject$1 || (_templateObject$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-stepbystep__content ui-stepbystep__scope\"></div>\n\t\t\t"])));
	        this.content.map(function (item) {
	          item.html.map(function (itemObj) {
	            _this.contentWrapper.appendChild(_this.getItem(itemObj).getContainer());
	          });
	        });
	      }

	      return this.contentWrapper;
	    }
	  }, {
	    key: "init",
	    value: function init() {
	      if (this.target && this.content) {
	        main_core.Dom.clean(this.target);
	        this.target.appendChild(this.getContentWrapper());
	      }
	    }
	  }]);
	  return StepByStep;
	}();

	exports.StepByStep = StepByStep;

}((this.BX.UI = this.BX.UI || {}),BX.Event,BX,BX));
//# sourceMappingURL=stepbystep.bundle.js.map
