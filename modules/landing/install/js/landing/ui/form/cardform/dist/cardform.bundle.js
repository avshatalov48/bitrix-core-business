this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.UI = this.BX.Landing.UI || {};
(function (exports,main_core,landing_ui_form_baseform) {
	'use strict';

	function _templateObject() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"landing-ui-form-cards-item\">\n\t\t\t\t<div class=\"landing-ui-form-cards-item-inner\">\n\t\t\t\t\t<div class=\"landing-ui-form-card-item-header\" onclick=\"", "\">\n\t\t\t\t\t\t<div class=\"landing-ui-form-card-item-header-left\">\n\t\t\t\t\t\t\t<div class=\"landing-ui-form-card-item-header-left-inner\">\n\t\t\t\t\t\t\t\t<span class=\"landing-ui-form-card-item-header-drag landing-ui-drag\"></span>\n\t\t\t\t\t\t\t\t<span class=\"landing-ui-form-card-item-header-title\">", "</span>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t<div class=\"landing-ui-form-card-item-header-edit\">\n\t\t\t\t\t\t\t\t<span class=\"fa fa-pencil\"></span>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<div class=\"landing-ui-form-card-item-header-right\">\n\t\t\t\t\t\t\t<div \n\t\t\t\t\t\t\t\tclass=\"landing-ui-form-card-item-header-remove\"\n\t\t\t\t\t\t\t\tonclick=\"", "\"\n\t\t\t\t\t\t\t>\n\t\t\t\t\t\t\t\t<span class=\"fa fa-remove\"></span>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t"]);

	  _templateObject = function _templateObject() {
	    return data;
	  };

	  return data;
	}
	/**
	 * @memberOf BX.Landing.UI.Form
	 */

	var CardForm = /*#__PURE__*/function (_BaseForm) {
	  babelHelpers.inherits(CardForm, _BaseForm);

	  function CardForm(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, CardForm);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(CardForm).call(this, options));

	    _this.setEventNamespace('BX.Landing.UI.Form.CardForm');

	    main_core.Dom.addClass(_this.layout, 'landing-ui-form-card');
	    _this.onItemClick = main_core.Runtime.throttle(_this.onItemClick, 200, babelHelpers.assertThisInitialized(_this));
	    _this.onRemoveItemClick = _this.onRemoveItemClick.bind(babelHelpers.assertThisInitialized(_this));
	    _this.wrapper = _this.getWrapper();
	    _this.labelBindings = options.labelBindings;
	    _this.preset = options.preset;

	    var _this$selector$split = _this.selector.split('@');

	    var _this$selector$split2 = babelHelpers.slicedToArray(_this$selector$split, 2);

	    _this.oldIndex = _this$selector$split2[1];
	    return _this;
	  }

	  babelHelpers.createClass(CardForm, [{
	    key: "getWrapper",
	    value: function getWrapper() {
	      return main_core.Tag.render(_templateObject(), this.onItemClick, this.label, this.onRemoveItemClick, this.getNode());
	    } // eslint-disable-next-line class-methods-use-this

	  }, {
	    key: "onItemClick",
	    value: function onItemClick(event) {
	      event.preventDefault();

	      if (main_core.Type.isDomNode(event.currentTarget)) {
	        var target = event.currentTarget.closest('.landing-ui-form-cards-item');

	        if (!main_core.Dom.hasClass(target, 'landing-ui-form-cards-item-expand')) {
	          main_core.Dom.addClass(target, 'landing-ui-form-cards-item-expand');
	          BX.Landing.Utils.onTransitionEnd(target).then(function () {
	            main_core.Dom.style(target, {
	              overflow: 'visible'
	            });
	          });
	          main_core.Dom.style(target, {
	            height: 'auto'
	          });
	        } else {
	          main_core.Dom.removeClass(target, 'landing-ui-form-cards-item-expand');
	          main_core.Dom.style(target, null);
	        }
	      }
	    }
	  }, {
	    key: "onRemoveItemClick",
	    value: function onRemoveItemClick(event) {
	      event.preventDefault();
	      event.stopPropagation();

	      if (!this.getLayout().closest('.landing-ui-disallow-remove')) {
	        main_core.Dom.remove(this.wrapper);
	        this.emit('onRemove');
	      }
	    }
	  }, {
	    key: "serialize",
	    value: function serialize() {
	      return this.fields.reduce(function (res, field) {
	        var _field$selector$split = field.selector.split('@'),
	            _field$selector$split2 = babelHelpers.slicedToArray(_field$selector$split, 1),
	            index = _field$selector$split2[0];

	        res[index] = field.getValue();
	        return res;
	      }, {});
	    }
	  }, {
	    key: "getPreset",
	    value: function getPreset() {
	      return this.preset || null;
	    }
	  }]);
	  return CardForm;
	}(landing_ui_form_baseform.BaseForm);

	exports.CardForm = CardForm;

}((this.BX.Landing.UI.Form = this.BX.Landing.UI.Form || {}),BX,BX.Landing.UI.Form));
//# sourceMappingURL=cardform.bundle.js.map
