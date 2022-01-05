this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
(function (exports,main_core,main_core_events) {
	'use strict';

	var _templateObject;
	var ShortView = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(ShortView, _EventEmitter);

	  function ShortView(params) {
	    var _this;

	    babelHelpers.classCallCheck(this, ShortView);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(ShortView).call(this, params));

	    _this.setEventNamespace('BX.UI.ShortView');

	    _this.setShortView(params.isShortView);

	    _this.node = null;
	    return _this;
	  }

	  babelHelpers.createClass(ShortView, [{
	    key: "renderTo",
	    value: function renderTo(container) {
	      if (!main_core.Type.isDomNode(container)) {
	        throw new Error('UI ShortView: HTMLElement not found');
	      }

	      main_core.Dom.append(this.render(), container);
	    }
	  }, {
	    key: "render",
	    value: function render() {
	      var checked = this.getShortView() === 'Y' ? 'checked' : '';
	      this.node = main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"tasks-scrum__switcher--container tasks-scrum__scope-switcher\">\n\t\t\t\t<label class=\"tasks-scrum__switcher--label\">\n\t\t\t\t<div class=\"tasks-scrum__switcher--label-text\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t\t<input type=\"checkbox\" class=\"tasks-scrum__switcher--checkbox\" ", ">\n\t\t\t\t<span class=\"tasks-scrum__switcher-cursor\"></span>\n\t\t\t\t</label>\n\t\t\t</div>\n\t\t"])), main_core.Loc.getMessage('UI_SHORT_VIEW_LABEL'), checked);
	      main_core.Event.bind(this.node, 'change', this.onChange.bind(this));
	      return this.node;
	    }
	  }, {
	    key: "setShortView",
	    value: function setShortView(value) {
	      this.shortView = value === 'Y' ? 'Y' : 'N';
	    }
	  }, {
	    key: "getShortView",
	    value: function getShortView() {
	      return this.shortView;
	    }
	  }, {
	    key: "onChange",
	    value: function onChange() {
	      var checkboxNode = this.node.querySelector('input[type="checkbox"]');
	      this.setShortView(checkboxNode.checked ? 'Y' : 'N');
	      this.emit('change', this.getShortView());
	    }
	  }]);
	  return ShortView;
	}(main_core_events.EventEmitter);

	exports.ShortView = ShortView;

}((this.BX.UI.ShortView = this.BX.UI.ShortView || {}),BX,BX.Event));
//# sourceMappingURL=short.view.bundle.js.map
