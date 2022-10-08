this.BX = this.BX || {};
(function (exports,main_core,ui_designTokens,ui_forms,main_core_events) {
	'use strict';

	var LayoutForm = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(LayoutForm, _EventEmitter);

	  function LayoutForm(params) {
	    var _this$params$containe;

	    var _this;

	    babelHelpers.classCallCheck(this, LayoutForm);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(LayoutForm).call(this));

	    _this.setEventNamespace('BX.UI.LayoutForm');

	    _this.params = params !== null && params !== void 0 ? params : {};
	    _this.container = (_this$params$containe = _this.params.container) !== null && _this$params$containe !== void 0 ? _this$params$containe : document.documentElement;
	    _this.nodes = null;

	    _this.init();

	    return _this;
	  }

	  babelHelpers.createClass(LayoutForm, [{
	    key: "init",
	    value: function init() {
	      var _this2 = this;

	      this.nodes = [].slice.call(this.container.querySelectorAll('[' + LayoutForm.HIDDEN_ATTRIBUTE + ']'));
	      this.nodes.forEach(function (node) {
	        main_core.Event.bind(node, "click", function (event) {
	          event.preventDefault();

	          _this2.toggleBLock(node);

	          _this2.emit('onToggle', {
	            checkbox: node.querySelector(LayoutForm.CHECKBOX_SELECTOR)
	          });
	        });
	        node.querySelector(LayoutForm.CHECKBOX_SELECTOR).style.pointerEvents = 'none';

	        _this2.checkInitialBlockVisibility(node);
	      });
	    }
	  }, {
	    key: "checkInitialBlockVisibility",
	    value: function checkInitialBlockVisibility(node) {
	      var checkbox = node.querySelector(LayoutForm.CHECKBOX_SELECTOR);

	      if (checkbox && checkbox.checked) {
	        var content = node.nextElementSibling;

	        if (content) {
	          content.style.height = 'auto';
	          main_core.Dom.addClass(content, LayoutForm.SHOW_CLASS);
	        }
	      }
	    }
	  }, {
	    key: "toggleBLock",
	    value: function toggleBLock(node) {
	      var checkbox = node.querySelector(LayoutForm.CHECKBOX_SELECTOR);

	      if (checkbox) {
	        var content = node.nextElementSibling;

	        if (content) {
	          var height = content.scrollHeight;

	          if (height > 0) {
	            if (!checkbox.checked) {
	              checkbox.checked = true;
	              content.style.height = height + 'px';
	              main_core.Dom.addClass(content, LayoutForm.SHOW_CLASS);

	              var onTransitionEnd = function onTransitionEnd() {
	                content.style.height = 'auto';
	                main_core.Event.unbind(content, 'transitionend', onTransitionEnd);
	              };

	              main_core.Event.bind(content, 'transitionend', onTransitionEnd);
	            } else {
	              checkbox.checked = false;
	              content.style.height = height + 'px';
	              requestAnimationFrame(function () {
	                content.style.height = 0;
	                main_core.Dom.removeClass(content, LayoutForm.SHOW_CLASS);
	              });
	            }
	          }
	        }
	      }
	    }
	  }]);
	  return LayoutForm;
	}(main_core_events.EventEmitter);
	babelHelpers.defineProperty(LayoutForm, "HIDDEN_ATTRIBUTE", 'data-form-row-hidden');
	babelHelpers.defineProperty(LayoutForm, "SHOW_CLASS", 'ui-form-row-hidden-show');
	babelHelpers.defineProperty(LayoutForm, "CHECKBOX_SELECTOR", '.ui-ctl-element[type="checkbox"]');

	exports.LayoutForm = LayoutForm;

}((this.BX.UI = this.BX.UI || {}),BX,BX,BX,BX.Event));
//# sourceMappingURL=layout-form.bundle.js.map
