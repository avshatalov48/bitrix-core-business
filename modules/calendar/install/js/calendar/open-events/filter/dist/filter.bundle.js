/* eslint-disable */
this.BX = this.BX || {};
this.BX.Calendar = this.BX.Calendar || {};
(function (exports,main_core_events) {
	'use strict';

	const MIN_QUERY_LENGTH = 3;
	var _filterId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("filterId");
	var _filter = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("filter");
	var _bindEvents = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("bindEvents");
	var _beforeApplyHandler = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("beforeApplyHandler");
	var _applyHandler = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("applyHandler");
	var _isFilterEmpty = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isFilterEmpty");
	var _arePresetsEmpty = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("arePresetsEmpty");
	var _isSearchEmpty = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isSearchEmpty");
	class Filter extends main_core_events.EventEmitter {
	  constructor(_filterId2) {
	    super();
	    Object.defineProperty(this, _isSearchEmpty, {
	      value: _isSearchEmpty2
	    });
	    Object.defineProperty(this, _arePresetsEmpty, {
	      value: _arePresetsEmpty2
	    });
	    Object.defineProperty(this, _isFilterEmpty, {
	      value: _isFilterEmpty2
	    });
	    Object.defineProperty(this, _applyHandler, {
	      value: _applyHandler2
	    });
	    Object.defineProperty(this, _beforeApplyHandler, {
	      value: _beforeApplyHandler2
	    });
	    Object.defineProperty(this, _bindEvents, {
	      value: _bindEvents2
	    });
	    Object.defineProperty(this, _filterId, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _filter, {
	      writable: true,
	      value: void 0
	    });
	    this.setEventNamespace('Calendar.OpenEvents.Filter');
	    babelHelpers.classPrivateFieldLooseBase(this, _filterId)[_filterId] = _filterId2;
	    babelHelpers.classPrivateFieldLooseBase(this, _filter)[_filter] = BX.Main.filterManager.getById(babelHelpers.classPrivateFieldLooseBase(this, _filterId)[_filterId]);
	    babelHelpers.classPrivateFieldLooseBase(this, _bindEvents)[_bindEvents]();
	  }
	  get id() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _filterId)[_filterId];
	  }
	  get fields() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _filter)[_filter].getFilterFieldsValues();
	  }
	  isDateFieldApplied() {
	    return this.fields.DATE_datesel && this.fields.DATE_datesel !== 'NONE';
	  }
	  getFilterFieldsKey() {
	    return JSON.stringify(this.fields);
	  }
	}
	function _bindEvents2() {
	  this.beforeApplyHandler = babelHelpers.classPrivateFieldLooseBase(this, _beforeApplyHandler)[_beforeApplyHandler].bind(this);
	  this.applyHandler = babelHelpers.classPrivateFieldLooseBase(this, _applyHandler)[_applyHandler].bind(this);
	  main_core_events.EventEmitter.subscribe('BX.Main.Filter:beforeApply', this.beforeApplyHandler);
	  main_core_events.EventEmitter.subscribe('BX.Main.Filter:apply', this.applyHandler);
	}
	function _beforeApplyHandler2(event) {
	  const [filterId] = event.getData();
	  if (filterId !== babelHelpers.classPrivateFieldLooseBase(this, _filterId)[_filterId]) {
	    return;
	  }
	  this.emit('beforeApply');
	}
	function _applyHandler2(event) {
	  const [filterId] = event.getData();
	  if (filterId !== babelHelpers.classPrivateFieldLooseBase(this, _filterId)[_filterId]) {
	    return;
	  }
	  if (babelHelpers.classPrivateFieldLooseBase(this, _isFilterEmpty)[_isFilterEmpty]()) {
	    this.emit('clear');
	  } else {
	    this.emit('apply');
	  }
	}
	function _isFilterEmpty2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _arePresetsEmpty)[_arePresetsEmpty]() && babelHelpers.classPrivateFieldLooseBase(this, _isSearchEmpty)[_isSearchEmpty]();
	}
	function _arePresetsEmpty2() {
	  return !babelHelpers.classPrivateFieldLooseBase(this, _filter)[_filter].getSearch().getLastSquare();
	}
	function _isSearchEmpty2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _filter)[_filter].getSearch().getSearchString().length < MIN_QUERY_LENGTH;
	}

	exports.Filter = Filter;

}((this.BX.Calendar.OpenEvents = this.BX.Calendar.OpenEvents || {}),BX.Event));
//# sourceMappingURL=filter.bundle.js.map
