this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.UI = this.BX.Landing.UI || {};
(function (exports,landing_ui_field_basefield,main_core,ui_entitySelector) {
	'use strict';

	var UserSelect = /*#__PURE__*/function (_BaseField) {
	  babelHelpers.inherits(UserSelect, _BaseField);
	  function UserSelect(options) {
	    var _options$userId;
	    var _this;
	    babelHelpers.classCallCheck(this, UserSelect);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(UserSelect).call(this, options));
	    _this.setEventNamespace('BX.Landing.UI.Field.UserSelectField');
	    _this.userId = parseInt((_options$userId = options.userId) !== null && _options$userId !== void 0 ? _options$userId : 0);
	    main_core.Dom.addClass(_this.layout, 'landing-ui-field-userselect');
	    _this.createDialog();
	    return _this;
	  }
	  babelHelpers.createClass(UserSelect, [{
	    key: "createDialog",
	    value: function createDialog() {
	      this.dialog = new ui_entitySelector.TagSelector({
	        multiple: false,
	        dialogOptions: {
	          preselectedItems: [['user', this.userId]],
	          enableSearch: true,
	          multiple: false,
	          autoHide: true,
	          hideByEsc: true,
	          context: 'LANDING_USER_SELECT',
	          entities: [{
	            id: 'user'
	          }],
	          popupOptions: {
	            targetContainer: parent.document.body
	          }
	        }
	      });
	      this.dialog.renderTo(this.input);
	    }
	  }, {
	    key: "reset",
	    value: function reset() {
	      this.setValue(0);
	    }
	  }, {
	    key: "getValue",
	    value: function getValue() {
	      var _this2 = this;
	      this.dialog.getTags().forEach(function (tag) {
	        _this2.userId = tag.getId();
	      });
	      return this.userId;
	    }
	  }, {
	    key: "setValue",
	    value: function setValue(userId) {
	      this.userId = Math.max(0, userId);
	    }
	  }]);
	  return UserSelect;
	}(landing_ui_field_basefield.BaseField);

	exports.UserSelect = UserSelect;

}((this.BX.Landing.UI.Field = this.BX.Landing.UI.Field || {}),BX.Landing.UI.Field,BX,BX.UI.EntitySelector));
//# sourceMappingURL=userselect.bundle.js.map
