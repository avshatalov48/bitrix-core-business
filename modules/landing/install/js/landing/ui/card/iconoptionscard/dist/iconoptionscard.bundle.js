this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.UI = this.BX.Landing.UI || {};
(function (exports,main_core,landing_ui_card_basecard,landing_loc,landing_ui_panel_iconpanel) {
	'use strict';

	var _templateObject, _templateObject2;
	/**
	 * @memberOf BX.Landing.UI.Card
	 */

	var IconOptionsCard = /*#__PURE__*/function (_BaseCard) {
	  babelHelpers.inherits(IconOptionsCard, _BaseCard);

	  function IconOptionsCard() {
	    var _this;

	    babelHelpers.classCallCheck(this, IconOptionsCard);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(IconOptionsCard).call(this));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "options", []);

	    _this.setEventNamespace('BX.Landing.UI.Card.IconOptionsCard');

	    return _this;
	  }

	  babelHelpers.createClass(IconOptionsCard, [{
	    key: "getLayout",
	    value: function getLayout() {
	      return this.cache.remember('layout', function () {
	        return main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-card landing-ui-card-icon-options --hide\">\n\t\t\t\t\t<div class=\"landing-ui-card-icon-options-title\">", "</div>\n\t\t\t\t\t<div class=\"landing-ui-card-icon-options-items\"></div>\n\t\t\t\t</div>\n\t\t\t"])), landing_loc.Loc.getMessage('LANDING_ICONS_CHANGE_STYLE'));
	      });
	    }
	  }, {
	    key: "getOptionsLayout",
	    value: function getOptionsLayout() {
	      return this.getLayout().querySelector('.landing-ui-card-icon-options-items');
	    }
	  }, {
	    key: "getOptions",
	    value: function getOptions() {
	      return this.options;
	    }
	  }, {
	    key: "setOptions",
	    value: function setOptions(options, activeOption) {
	      var _this2 = this;

	      if (options.length > 0) {
	        this.options = options;
	        main_core.Dom.clean(this.getOptionsLayout());
	        main_core.Dom.removeClass(this.getLayout(), '--hide');
	        options.forEach(function (option) {
	          var isActive = option === activeOption ? ' --active' : '';
	          var optionLayout = main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["<span class=\"", "", "\"></span>"])), option, isActive);
	          main_core.Event.bind(optionLayout, 'click', _this2.onOptionClick.bind(_this2, option));
	          main_core.Dom.append(optionLayout, _this2.getOptionsLayout());
	        });
	      }
	    }
	  }, {
	    key: "onOptionClick",
	    value: function onOptionClick(option) {
	      this.getOptionsLayout().querySelectorAll('span').forEach(function (optionItem) {
	        main_core.Dom.removeClass(optionItem, '--active');

	        if (main_core.Dom.hasClass(optionItem, option)) {
	          main_core.Dom.addClass(optionItem, '--active');
	        }
	      });
	      this.emit('onChange', {
	        option: option
	      });
	    }
	  }, {
	    key: "setOptionsByItem",
	    value: function setOptionsByItem(classList) {
	      var _this3 = this;

	      landing_ui_panel_iconpanel.IconPanel.getLibraries().then(function (libraries) {
	        if (classList.length > 0) {
	          var iconOptions = new Set();
	          var iconOptionActive;
	          libraries.forEach(function (library) {
	            library.categories.forEach(function (category) {
	              category.items.forEach(function (item) {
	                if (main_core.Type.isObject(item)) {
	                  var foundedOptions = item.options.filter(function (option) {
	                    return classList.every(function (iconClass) {
	                      return option.split(' ').includes(iconClass);
	                    });
	                  });

	                  if (foundedOptions.length > 0) {
	                    item.options.forEach(function (option) {
	                      iconOptions.add(option);
	                    });
	                    iconOptionActive = foundedOptions[0];
	                  }
	                } else {
	                  if (classList.every(function (iconClass) {
	                    return item.split(' ').includes(iconClass);
	                  })) {
	                    iconOptions.add(item);
	                    iconOptionActive = item;
	                  }
	                }
	              });
	            });
	          });

	          if (iconOptions.size > 0) {
	            _this3.setOptions(babelHelpers.toConsumableArray(iconOptions), iconOptionActive);
	          }
	        }
	      });
	    }
	  }]);
	  return IconOptionsCard;
	}(landing_ui_card_basecard.BaseCard);

	exports.IconOptionsCard = IconOptionsCard;

}((this.BX.Landing.UI.Card = this.BX.Landing.UI.Card || {}),BX,BX.Landing.UI.Card,BX.Landing,BX.Landing.UI.Panel));
//# sourceMappingURL=iconoptionscard.bundle.js.map
