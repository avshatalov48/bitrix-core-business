this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.Ui = this.BX.Landing.Ui || {};
this.BX.Landing.Ui.Panel = this.BX.Landing.Ui.Panel || {};
this.BX.Landing.Ui.Panel.Formsettingspanel = this.BX.Landing.Ui.Panel.Formsettingspanel || {};
(function (exports,landing_loc,landing_ui_card_headercard,landing_ui_panel_basepresetpanel,landing_ui_field_accordionfield,landing_ui_card_messagecard,ui_designTokens,main_core) {
	'use strict';

	var yandexIcon = "/bitrix/js/landing/ui/panel/formsettingspanel/content/analytics/dist/images/yandex.svg";

	var googleIcon = "/bitrix/js/landing/ui/panel/formsettingspanel/content/analytics/dist/images/google.svg";

	var _templateObject;
	function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	var ContentTableCell = /*#__PURE__*/function () {
	  function ContentTableCell() {
	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, ContentTableCell);
	    this.options = _objectSpread({}, options);
	  }
	  babelHelpers.createClass(ContentTableCell, [{
	    key: "render",
	    value: function render() {
	      return main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"landing-ui-content-table-cell\">\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"])), main_core.Text.encode(this.options.content));
	    }
	  }]);
	  return ContentTableCell;
	}();

	var _templateObject$1;
	function ownKeys$1(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread$1(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys$1(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys$1(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	var ContentTableRow = /*#__PURE__*/function () {
	  function ContentTableRow(options) {
	    babelHelpers.classCallCheck(this, ContentTableRow);
	    this.options = _objectSpread$1({}, options);
	  }
	  babelHelpers.createClass(ContentTableRow, [{
	    key: "render",
	    value: function render() {
	      var headClass = this.options.head ? ' landing-ui-content-table-row-head' : '';
	      return main_core.Tag.render(_templateObject$1 || (_templateObject$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"landing-ui-content-table-row", "\">\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"])), headClass, this.options.columns.map(function (cell) {
	        return cell.render();
	      }));
	    }
	  }]);
	  return ContentTableRow;
	}();

	var _templateObject$2, _templateObject2;
	function ownKeys$2(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread$2(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys$2(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys$2(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	var ContentTable = /*#__PURE__*/function () {
	  function ContentTable(options) {
	    babelHelpers.classCallCheck(this, ContentTable);
	    this.options = _objectSpread$2({}, options);
	    this.headRow = new ContentTableRow({
	      columns: this.options.columns.map(function (columnOptions) {
	        return new ContentTableCell(columnOptions);
	      }),
	      head: true
	    });
	    this.rows = this.options.rows.map(function (rowOptions) {
	      return new ContentTableRow({
	        columns: rowOptions.columns.map(function (cellOptions) {
	          return new ContentTableCell(cellOptions);
	        })
	      });
	    });
	  }
	  babelHelpers.createClass(ContentTable, [{
	    key: "getTitleLayout",
	    value: function getTitleLayout() {
	      if (Type.isStringFilled(this.options.title)) {
	        return main_core.Tag.render(_templateObject$2 || (_templateObject$2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-content-table-title\">", "</div>\n\t\t\t"])), this.options.title);
	      }
	      return '';
	    }
	  }, {
	    key: "render",
	    value: function render() {
	      return main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"landing-ui-content-table-wrapper\">\n\t\t\t\t\n\t\t\t\t<div class=\"landing-ui-content-table\">\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t"])), this.headRow.render(), this.rows.map(function (row) {
	        return row.render();
	      }));
	    }
	  }]);
	  return ContentTable;
	}();

	var AnalyticsContent = /*#__PURE__*/function (_ContentWrapper) {
	  babelHelpers.inherits(AnalyticsContent, _ContentWrapper);
	  function AnalyticsContent(options) {
	    var _this;
	    babelHelpers.classCallCheck(this, AnalyticsContent);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(AnalyticsContent).call(this, options));
	    _this.setEventNamespace('BX.Landing.UI.Panel.FormSettingsPanel.AgreementsContent');
	    var header = new landing_ui_card_headercard.HeaderCard({
	      title: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_ANALYTICS_TITLE')
	    });
	    var items = [];
	    if (landing_loc.Loc.getMessage('LANGUAGE_ID') === 'ru') {
	      items.push({
	        id: 'yandex',
	        title: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_ANALYTICS_ITEM_YANDEX_METRIKA'),
	        icon: yandexIcon,
	        checked: true,
	        switcher: false,
	        content: _this.getYandexTable()
	      });
	    }
	    items.push({
	      id: 'google',
	      title: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_ANALYTICS_ITEM_GOOGLE_ANALYTICS'),
	      icon: googleIcon,
	      checked: true,
	      switcher: false,
	      content: _this.getGoogleTable()
	    });
	    var accordionField = new landing_ui_field_accordionfield.AccordionField({
	      selector: 'analytics',
	      title: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_ANALYTICS_ITEMS_FIELD_TITLE'),
	      items: items
	    });
	    var message = new landing_ui_card_messagecard.MessageCard({
	      id: 'analyticsMessage',
	      header: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_ANALYTICS_MESSAGE_TITLE'),
	      description: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_ANALYTICS_MESSAGE_DESCRIPTION'),
	      angle: false,
	      restoreState: true
	    });
	    _this.addItem(header);
	    _this.addItem(message);
	    _this.addItem(accordionField);
	    return _this;
	  }
	  babelHelpers.createClass(AnalyticsContent, [{
	    key: "getYandexTable",
	    value: function getYandexTable() {
	      var table = new ContentTable({
	        columns: [{
	          id: 'title',
	          content: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_ANALYTICS_TABLE_NAME_COLUMN_TITLE')
	        }, {
	          id: 'id',
	          content: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_ANALYTICS_TABLE_ID_COLUMN_TITLE')
	        }],
	        rows: this.options.formOptions.analytics.steps.map(function (row) {
	          return {
	            columns: [{
	              content: row.name
	            }, {
	              content: row.event
	            }]
	          };
	        })
	      });
	      return table.render();
	    }
	  }, {
	    key: "getGoogleTable",
	    value: function getGoogleTable() {
	      var table = new ContentTable({
	        columns: [{
	          id: 'title',
	          content: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_ANALYTICS_TABLE_NAME_COLUMN_TITLE')
	        }, {
	          id: 'id',
	          content: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_ANALYTICS_TABLE_ID_COLUMN_TITLE')
	        }],
	        rows: this.options.formOptions.analytics.steps.map(function (row) {
	          return {
	            columns: [{
	              content: row.name
	            }, {
	              content: row.code
	            }]
	          };
	        })
	      });
	      return table.render();
	    }
	  }]);
	  return AnalyticsContent;
	}(landing_ui_panel_basepresetpanel.ContentWrapper);

	exports.default = AnalyticsContent;

}((this.BX.Landing.Ui.Panel.Formsettingspanel.Content = this.BX.Landing.Ui.Panel.Formsettingspanel.Content || {}),BX.Landing,BX.Landing.UI.Card,BX.Landing.UI.Panel,BX.Landing.UI.Field,BX.Landing.UI.Card,BX,BX));
//# sourceMappingURL=analytics.bundle.js.map
