/* eslint-disable */
this.BX = this.BX || {};
this.BX.Bizproc = this.BX.Bizproc || {};
this.BX.Bizproc.Integration = this.BX.Bizproc.Integration || {};
(function (exports,ui_formElements_view,ui_formElements_field,main_core,main_core_events,ui_section) {
	'use strict';

	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }
	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _buildAdditionalSection = /*#__PURE__*/new WeakSet();
	var _subscribeAnalytics = /*#__PURE__*/new WeakSet();
	var AutomationPage = /*#__PURE__*/function (_BaseSettingsPage) {
	  babelHelpers.inherits(AutomationPage, _BaseSettingsPage);
	  babelHelpers.createClass(AutomationPage, null, [{
	    key: "type",
	    get: function get() {
	      return 'automation';
	    }
	  }]);
	  function AutomationPage() {
	    var _Loc$getMessage, _Loc$getMessage2;
	    var _this;
	    babelHelpers.classCallCheck(this, AutomationPage);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(AutomationPage).call(this));
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _subscribeAnalytics);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _buildAdditionalSection);
	    _this.titlePage = (_Loc$getMessage = main_core.Loc.getMessage('BIZPROC_INTRANET_SETTINGS_TITLE_PAGE_AUTOMATION')) !== null && _Loc$getMessage !== void 0 ? _Loc$getMessage : '';
	    _this.descriptionPage = (_Loc$getMessage2 = main_core.Loc.getMessage('BIZPROC_INTRANET_SETTINGS_DESCRIPTION_PAGE_AUTOMATION')) !== null && _Loc$getMessage2 !== void 0 ? _Loc$getMessage2 : '';
	    return _this;
	  }
	  babelHelpers.createClass(AutomationPage, [{
	    key: "getType",
	    value: function getType() {
	      return this.constructor.type;
	    }
	  }, {
	    key: "appendSections",
	    value: function appendSections(contentNode) {
	      _classPrivateMethodGet(this, _buildAdditionalSection, _buildAdditionalSection2).call(this).renderTo(contentNode);
	    }
	  }]);
	  return AutomationPage;
	}(ui_formElements_field.BaseSettingsPage);
	function _buildAdditionalSection2() {
	  if (!this.hasValue('SECTION_MAIN')) {
	    return null;
	  }
	  var additionalSection = new ui_section.Section(this.getValue('SECTION_MAIN'));
	  var sectionSettings = new ui_formElements_field.SettingsSection({
	    section: additionalSection,
	    parent: this
	  });
	  if (this.hasValue('crm_activity_wait_for_closure_task')) {
	    var showQuitField = new ui_formElements_view.Checker(this.getValue('crm_activity_wait_for_closure_task'));
	    _classPrivateMethodGet(this, _subscribeAnalytics, _subscribeAnalytics2).call(this, showQuitField, 'crm_activity_wait_for_closure_task');
	    AutomationPage.addToSectionHelper(showQuitField, sectionSettings);
	  }
	  if (this.hasValue('crm_activity_wait_for_closure_comments')) {
	    var newUserField = new ui_formElements_view.Checker(this.getValue('crm_activity_wait_for_closure_comments'));
	    _classPrivateMethodGet(this, _subscribeAnalytics, _subscribeAnalytics2).call(this, newUserField, 'crm_activity_wait_for_closure_comments');
	    AutomationPage.addToSectionHelper(newUserField, sectionSettings);
	  }
	  return sectionSettings;
	}
	function _subscribeAnalytics2(checker, tag) {
	  var _this2 = this;
	  main_core_events.EventEmitter.subscribe(checker, 'change', function () {
	    var _this2$getAnalytic;
	    (_this2$getAnalytic = _this2.getAnalytic()) === null || _this2$getAnalytic === void 0 ? void 0 : _this2$getAnalytic.addEvent("bizproc_".concat(tag), {
	      tool: 'crm',
	      category: 'bizproc_operations',
	      event: 'settings_edit',
	      type: tag,
	      c_element: checker.isChecked() ? 'on' : 'off'
	    });
	  });
	}

	main_core_events.EventEmitter.subscribe(main_core_events.EventEmitter.GLOBAL_TARGET, 'BX.Intranet.Settings:onExternalPageLoaded:automation', function () {
	  return new AutomationPage();
	});

}((this.BX.Bizproc.Integration.IntranetSettings = this.BX.Bizproc.Integration.IntranetSettings || {}),BX.UI.FormElements,BX.UI.FormElements,BX,BX.Event,BX.UI));
//# sourceMappingURL=index.bundle.js.map
