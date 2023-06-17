this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.UI = this.BX.Landing.UI || {};
(function (exports,main_core,main_core_events) {
	'use strict';

	var _templateObject;

	/**
	 * @memberOf BX.Landing.UI.Panel
	 */
	var BasePanel = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(BasePanel, _EventEmitter);
	  babelHelpers.createClass(BasePanel, null, [{
	    key: "makeId",
	    value: function makeId() {
	      return "landing_ui_panel_".concat(main_core.Text.getRandom());
	    }
	  }, {
	    key: "createLayout",
	    value: function createLayout(id) {
	      return main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"landing-ui-panel landing-ui-hide\" data-id=\"", "\"></div>\n\t\t"])), id);
	    }
	  }]);
	  function BasePanel() {
	    var _this;
	    var id = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
	    babelHelpers.classCallCheck(this, BasePanel);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(BasePanel).call(this));
	    _this.setEventNamespace('BX.Landing.UI.Panel.BasePanel');
	    _this.id = main_core.Type.isString(id) ? id : BasePanel.makeId();
	    _this.layout = BasePanel.createLayout(_this.id);
	    _this.classShow = 'landing-ui-show';
	    _this.classHide = 'landing-ui-hide';
	    _this.forms = new BX.Landing.UI.Collection.FormCollection();
	    _this.contextDocument = document;
	    _this.contextWindow = _this.contextDocument.defaultView;
	    return _this;
	  }

	  // eslint-disable-next-line no-unused-vars
	  babelHelpers.createClass(BasePanel, [{
	    key: "show",
	    value: function show(options) {
	      if (!this.isShown()) {
	        return BX.Landing.Utils.Show(this.layout);
	      }
	      return Promise.resolve();
	    }
	  }, {
	    key: "hide",
	    value: function hide() {
	      if (this.isShown()) {
	        return BX.Landing.Utils.Hide(this.layout);
	      }
	      return Promise.resolve();
	    }
	  }, {
	    key: "isShown",
	    value: function isShown() {
	      return !main_core.Dom.hasClass(this.layout, this.classHide);
	    }
	  }, {
	    key: "setContent",
	    value: function setContent(content) {
	      this.clear();
	      if (main_core.Type.isString(content)) {
	        this.layout.innerHTML = content;
	      } else if (main_core.Type.isDomNode(content)) {
	        this.appendContent(content);
	      } else if (main_core.Type.isArray(content)) {
	        content.forEach(this.appendContent, this);
	      }
	    }
	  }, {
	    key: "appendContent",
	    value: function appendContent(content) {
	      if (main_core.Type.isDomNode(content)) {
	        this.layout.appendChild(content);
	      }
	    }
	  }, {
	    key: "prependContent",
	    value: function prependContent(content) {
	      if (main_core.Type.isDomNode(content)) {
	        main_core.Dom.prepend(content, this.layout);
	      }
	    }
	  }, {
	    key: "renderTo",
	    value: function renderTo(target) {
	      if (main_core.Type.isDomNode(target)) {
	        main_core.Dom.append(this.layout, target);
	      }
	    }
	  }, {
	    key: "remove",
	    value: function remove() {
	      main_core.Dom.remove(this.layout);
	    }
	  }, {
	    key: "appendForm",
	    value: function appendForm(form) {
	      this.layout.appendChild(form.getNode());
	    }
	  }, {
	    key: "clear",
	    value: function clear() {
	      main_core.Dom.clean(this.layout);
	    }
	  }, {
	    key: "setLayoutClass",
	    value: function setLayoutClass(className) {
	      main_core.Dom.addClass(this.layout, className);
	    }
	  }, {
	    key: "setContextDocument",
	    value: function setContextDocument(contextDocument) {
	      this.contextDocument = contextDocument;
	      this.contextWindow = this.contextDocument.defaultView;
	    }
	  }]);
	  return BasePanel;
	}(main_core_events.EventEmitter);

	exports.BasePanel = BasePanel;

}((this.BX.Landing.UI.Panel = this.BX.Landing.UI.Panel || {}),BX,BX.Event));
//# sourceMappingURL=base.bundle.js.map
