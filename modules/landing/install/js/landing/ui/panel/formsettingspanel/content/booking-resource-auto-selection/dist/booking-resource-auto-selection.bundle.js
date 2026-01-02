/* eslint-disable */
this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.Ui = this.BX.Landing.Ui || {};
this.BX.Landing.Ui.Panel = this.BX.Landing.Ui.Panel || {};
this.BX.Landing.Ui.Panel.Formsettingspanel = this.BX.Landing.Ui.Panel.Formsettingspanel || {};
(function (exports,main_core,main_core_events,booking_const,landing_loc,landing_ui_card_headercard,landing_ui_card_messagecard,landing_ui_panel_basepresetpanel,landing_ui_form_formsettingsform) {
	'use strict';

	var resourceAutoSelectionIcon = "/bitrix/js/landing/ui/panel/formsettingspanel/content/booking-resource-auto-selection/dist/images/resource-auto-selection-icon.svg";

	var _templateObject;
	function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _options = /*#__PURE__*/new WeakMap();
	var _getFormOptionData = /*#__PURE__*/new WeakSet();
	var _getHeaderCard = /*#__PURE__*/new WeakSet();
	var _getMessageCard = /*#__PURE__*/new WeakSet();
	var _getSettingsForm = /*#__PURE__*/new WeakSet();
	var BookingResourceAutoSelection = /*#__PURE__*/function (_ContentWrapper) {
	  babelHelpers.inherits(BookingResourceAutoSelection, _ContentWrapper);
	  function BookingResourceAutoSelection(options) {
	    var _this;
	    babelHelpers.classCallCheck(this, BookingResourceAutoSelection);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(BookingResourceAutoSelection).call(this, options));
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _getSettingsForm);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _getMessageCard);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _getHeaderCard);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _getFormOptionData);
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _options, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _options, options);
	    _this.setEventNamespace('BX.Landing.UI.Panel.FormSettingsPanel.BookingResourceAutoSelectionContent');
	    _this.addItem(_classPrivateMethodGet(babelHelpers.assertThisInitialized(_this), _getHeaderCard, _getHeaderCard2).call(babelHelpers.assertThisInitialized(_this)));
	    _this.addItem(_classPrivateMethodGet(babelHelpers.assertThisInitialized(_this), _getMessageCard, _getMessageCard2).call(babelHelpers.assertThisInitialized(_this)));
	    _this.addItem(_classPrivateMethodGet(babelHelpers.assertThisInitialized(_this), _getSettingsForm, _getSettingsForm2).call(babelHelpers.assertThisInitialized(_this)));
	    return _this;
	  }
	  babelHelpers.createClass(BookingResourceAutoSelection, [{
	    key: "getLayout",
	    value: function getLayout() {
	      return this.cache.remember('layout', function () {
	        return main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["<div class=\"landing-ui-content-booking-resource-auto-selection\"></div>"])));
	      });
	    }
	  }, {
	    key: "valueReducer",
	    value: function valueReducer(value) {
	      var checked = Boolean(_classPrivateMethodGet(this, _getSettingsForm, _getSettingsForm2).call(this).isOpened());
	      return {
	        data: _classPrivateMethodGet(this, _getFormOptionData, _getFormOptionData2).call(this, checked),
	        bookingResourceAutoSelection: _objectSpread(_objectSpread({}, value), {}, {
	          use: checked
	        })
	      };
	    }
	  }, {
	    key: "onChange",
	    value: function onChange(event) {
	      this.emit('onChange', _objectSpread(_objectSpread({}, event.getData()), {}, {
	        skipPrepare: true
	      }));
	    }
	  }]);
	  return BookingResourceAutoSelection;
	}(landing_ui_panel_basepresetpanel.ContentWrapper);
	function _getFormOptionData2(checked) {
	  var _babelHelpers$classPr, _babelHelpers$classPr2, _babelHelpers$classPr3, _babelHelpers$classPr4;
	  var bookingField = (_babelHelpers$classPr = babelHelpers.classPrivateFieldGet(this, _options)) === null || _babelHelpers$classPr === void 0 ? void 0 : (_babelHelpers$classPr2 = _babelHelpers$classPr.formOptions) === null || _babelHelpers$classPr2 === void 0 ? void 0 : (_babelHelpers$classPr3 = _babelHelpers$classPr2.data) === null || _babelHelpers$classPr3 === void 0 ? void 0 : (_babelHelpers$classPr4 = _babelHelpers$classPr3.fields) === null || _babelHelpers$classPr4 === void 0 ? void 0 : _babelHelpers$classPr4.find(function (_ref) {
	    var id = _ref.id;
	    return id === 'BOOKING_BOOKING';
	  });
	  if (!bookingField) {
	    var _babelHelpers$classPr5, _babelHelpers$classPr6;
	    return (_babelHelpers$classPr5 = babelHelpers.classPrivateFieldGet(this, _options)) === null || _babelHelpers$classPr5 === void 0 ? void 0 : (_babelHelpers$classPr6 = _babelHelpers$classPr5.formOptions) === null || _babelHelpers$classPr6 === void 0 ? void 0 : _babelHelpers$classPr6.data;
	  }
	  var settingsData = bookingField.settingsData || {};
	  settingsData[booking_const.CrmFormSettingsDataPropName.isAutoSelectionOn] = checked;
	  settingsData[booking_const.CrmFormSettingsDataPropName.autoSelection] = Object.assign(settingsData[booking_const.CrmFormSettingsDataPropName.autoSelection] || {}, {
	    hasSlotsAllAvailableResources: false
	  });
	  bookingField.settingsData = _objectSpread({}, settingsData);
	  return babelHelpers.classPrivateFieldGet(this, _options).formOptions.data;
	}
	function _getHeaderCard2() {
	  return new landing_ui_card_headercard.HeaderCard({
	    title: landing_loc.Loc.getMessage('LANDING_FORM_BOOKING_RESOURCE_AUTO_SELECTION_TITLE')
	  });
	}
	function _getMessageCard2() {
	  return new landing_ui_card_messagecard.MessageCard({
	    id: 'bookingResourceAutoSelectionMessage',
	    header: landing_loc.Loc.getMessage('LANDING_FORM_BOOKING_RESOURCE_AUTO_SELECTION_MESSAGE_HEADER'),
	    description: landing_loc.Loc.getMessage('LANDING_FORM_BOOKING_RESOURCE_AUTO_SELECTION_MESSAGE_DESCRIPTION'),
	    restoreState: true,
	    closeable: false,
	    angle: false,
	    more: function more() {
	      var helper = main_core.Reflection.getClass('top.BX.Helper');
	      if (helper) {
	        BX.Helper.show('redirect=detail&code=25366370');
	      }
	    },
	    icon: resourceAutoSelectionIcon
	  });
	}
	function _getSettingsForm2() {
	  var _this2 = this;
	  return this.cache.remember('bookingResourceAutoSelectionForm', function () {
	    return new landing_ui_form_formsettingsform.FormSettingsForm({
	      id: 'bookingResourceAutoSelection',
	      title: landing_loc.Loc.getMessage('LANDING_FORM_BOOKING_RESOURCE_AUTO_SELECTION_SELECTOR'),
	      toggleable: true,
	      opened: _this2.options.formOptions.bookingResourceAutoSelection.use,
	      fields: []
	    });
	  });
	}

	exports.default = BookingResourceAutoSelection;

}((this.BX.Landing.Ui.Panel.Formsettingspanel.Content = this.BX.Landing.Ui.Panel.Formsettingspanel.Content || {}),BX,BX.Event,BX.Booking.Const,BX.Landing,BX.Landing.UI.Card,BX.Landing.UI.Card,BX.Landing.UI.Panel,BX.Landing.UI.Form));
//# sourceMappingURL=booking-resource-auto-selection.bundle.js.map
