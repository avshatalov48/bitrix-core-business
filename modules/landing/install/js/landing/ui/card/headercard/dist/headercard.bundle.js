this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.UI = this.BX.Landing.UI || {};
(function (exports,landing_ui_card_basecard,main_core) {
	'use strict';

	var _templateObject;
	/**
	 * @memberOf BX.Landing.UI.Card
	 */

	var HeaderCard = /*#__PURE__*/function (_BaseCard) {
	  babelHelpers.inherits(HeaderCard, _BaseCard);

	  function HeaderCard(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, HeaderCard);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(HeaderCard).call(this, options));
	    main_core.Dom.addClass(_this.getLayout(), 'landing-ui-card-headercard');

	    if (options.bottomMargin === false) {
	      _this.setBottomMargin(options.bottomMargin);
	    }

	    if (main_core.Type.isNumber(options.level)) {
	      main_core.Dom.addClass(_this.getLayout(), "landing-ui-card-headercard-".concat(options.level));
	    }

	    if (main_core.Type.isStringFilled(options.description)) {
	      _this.setDescription(options.description);
	    }

	    return _this;
	  }

	  babelHelpers.createClass(HeaderCard, [{
	    key: "getDescriptionLayout",
	    value: function getDescriptionLayout() {
	      return this.cache.remember('descriptionLayout', function () {
	        return main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<span class=\"landing-ui-card-headercard-description\"></span>\n\t\t\t"])));
	      });
	    }
	  }, {
	    key: "setDescription",
	    value: function setDescription(descriptionText) {
	      var descriptionLayout = this.getDescriptionLayout();

	      if (!this.body.contains(descriptionLayout)) {
	        main_core.Dom.append(descriptionLayout, this.body);
	      }

	      descriptionLayout.textContent = descriptionText;
	    }
	  }, {
	    key: "setBottomMargin",
	    value: function setBottomMargin(value) {
	      if (value === true) {
	        main_core.Dom.removeClass(this.getLayout(), 'landing-ui-card-headercard-without-bottom-margin');
	      } else {
	        main_core.Dom.addClass(this.getLayout(), 'landing-ui-card-headercard-without-bottom-margin');
	      }
	    }
	  }]);
	  return HeaderCard;
	}(landing_ui_card_basecard.BaseCard);

	exports.HeaderCard = HeaderCard;

}((this.BX.Landing.UI.Card = this.BX.Landing.UI.Card || {}),BX.Landing.UI.Card,BX));
//# sourceMappingURL=headercard.bundle.js.map
