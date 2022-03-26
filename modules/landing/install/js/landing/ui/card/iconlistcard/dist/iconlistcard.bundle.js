this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.UI = this.BX.Landing.UI || {};
(function (exports,main_core,main_core_events,landing_loc,landing_ui_card_basecard,landing_ui_card_iconoptionscard) {
	'use strict';

	var _templateObject, _templateObject2, _templateObject3, _templateObject4, _templateObject5;
	/**
	 * @memberOf BX.Landing.UI.Card
	 */

	var IconListCard = /*#__PURE__*/function (_BaseCard) {
	  babelHelpers.inherits(IconListCard, _BaseCard);

	  function IconListCard(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, IconListCard);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(IconListCard).call(this, options));

	    _this.setEventNamespace('BX.Landing.UI.Card.IconListCard');

	    _this.title = '';
	    _this.items = new Map();
	    _this.activeIcon = null;
	    _this.previewOptionsCard = new landing_ui_card_iconoptionscard.IconOptionsCard();

	    _this.previewOptionsCard.subscribe('onChange', _this.onPreviewOptionClick.bind(babelHelpers.assertThisInitialized(_this)));

	    main_core.Dom.append(_this.previewOptionsCard.getLayout(), _this.getPreviewOptions());
	    return _this;
	  }

	  babelHelpers.createClass(IconListCard, [{
	    key: "getLayout",
	    value: function getLayout() {
	      var _this2 = this;

	      return this.cache.remember('layout', function () {
	        return main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-card landing-ui-card-icons\">\n\t\t\t\t\t<div class=\"landing-ui-card-header-wrapper\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"landing-ui-card-body-wrapper\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t"])), _this2.getHeader(), _this2.getPreview(), _this2.getBody());
	      });
	    }
	  }, {
	    key: "getPreview",
	    value: function getPreview() {
	      return this.cache.remember('preview', function () {
	        return main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-card-preview --hide\">\n\t\t\t\t\t<div class=\"landing-ui-card-preview-icon\"></div>\n\t\t\t\t\t<div class=\"landing-ui-card-preview-options\"></div>\n\t\t\t\t</div>\n\t\t\t"])));
	      });
	    }
	  }, {
	    key: "getPreviewIcon",
	    value: function getPreviewIcon() {
	      return this.getPreview().querySelector('.landing-ui-card-preview-icon');
	    }
	  }, {
	    key: "getPreviewOptions",
	    value: function getPreviewOptions() {
	      return this.getPreview().querySelector('.landing-ui-card-preview-options');
	    }
	  }, {
	    key: "setPreviewIcon",
	    value: function setPreviewIcon(className) {
	      var icon = main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["<span class=\"", "\"></span>"])), className);
	      main_core.Dom.clean(this.getPreviewIcon());
	      main_core.Dom.append(icon, this.getPreviewIcon());
	    }
	  }, {
	    key: "addItem",
	    value: function addItem(item, additional) {
	      var _this3 = this;

	      if (this.getBody().childElementCount === 0) {
	        this.itemsContainer = main_core.Tag.render(_templateObject4 || (_templateObject4 = babelHelpers.taggedTemplateLiteral(["<div class=\"landing-ui-card-icons-container\"></div>"])));
	        main_core.Dom.append(this.itemsContainer, this.getBody());
	      }

	      var icon = main_core.Tag.render(_templateObject5 || (_templateObject5 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"landing-ui-card landing-ui-card-icon\">\n\t\t\t\t<span class=\"", "\"></span>\n\t\t\t</div>\n\t\t"])), item);
	      main_core.Event.bind(icon, 'click', this.onItemClick.bind(this, icon, additional));
	      main_core.Dom.append(icon, this.itemsContainer); // todo: need?
	      // duplicate control

	      var styles = getComputedStyle(icon.querySelector('span'), ':before');
	      requestAnimationFrame(function () {
	        var content = styles.getPropertyValue('content');

	        if (content === 'none') {
	          console.warn('Attention, item "' + item + '" has no content');
	        }

	        if (_this3.items.has(content)) {
	          icon.hidden = true;
	        } else {
	          _this3.items.set(content, true);
	        }
	      });
	    }
	  }, {
	    key: "onItemClick",
	    value: function onItemClick(item, additional) {
	      var prevActive = this.getBody().querySelector('.landing-ui-card-icon.--active');

	      if (prevActive) {
	        main_core.Dom.removeClass(prevActive, '--active');
	      }

	      main_core.Dom.addClass(item, '--active');
	      this.activeIcon = item.firstElementChild.className;

	      if (main_core.Type.isObject(additional)) {
	        this.setPreviewIcon(additional.defaultOption);
	        this.previewOptionsCard.setOptions(additional.options, additional.defaultOption);
	      } else {
	        this.setPreviewIcon(this.activeIcon);
	        this.previewOptionsCard.setOptions([this.activeIcon], this.activeIcon);
	      }

	      main_core.Dom.removeClass(this.getPreview(), '--hide');
	    }
	  }, {
	    key: "onPreviewOptionClick",
	    value: function onPreviewOptionClick(event) {
	      var option = event.getData().option;
	      this.activeIcon = option;
	      this.setPreviewIcon(option);
	    }
	  }, {
	    key: "getActiveIcon",
	    value: function getActiveIcon() {
	      return this.activeIcon;
	    }
	  }, {
	    key: "getActiveOptions",
	    value: function getActiveOptions() {
	      return this.previewOptionsCard.getOptions();
	    }
	  }]);
	  return IconListCard;
	}(landing_ui_card_basecard.BaseCard);

	exports.IconListCard = IconListCard;

}((this.BX.Landing.UI.Card = this.BX.Landing.UI.Card || {}),BX,BX.Event,BX.Landing,BX.Landing.UI.Card,BX.Landing.UI.Card));
//# sourceMappingURL=iconlistcard.bundle.js.map
