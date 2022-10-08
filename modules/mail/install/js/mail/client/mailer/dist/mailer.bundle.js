this.BX = this.BX || {};
this.BX.Mail = this.BX.Mail || {};
(function (exports,mail_client_filtertoolbar,mail_client_binding,main_core_events) {
	'use strict';

	function _createForOfIteratorHelper(o, allowArrayLike) { var it = typeof Symbol !== "undefined" && o[Symbol.iterator] || o["@@iterator"]; if (!it) { if (Array.isArray(o) || (it = _unsupportedIterableToArray(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = it.call(o); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it["return"] != null) it["return"](); } finally { if (didErr) throw err; } } }; }

	function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

	function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }

	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

	var _filter = /*#__PURE__*/new WeakMap();

	var _filterToolbar = /*#__PURE__*/new WeakMap();

	var _binding = /*#__PURE__*/new WeakMap();

	var _mailboxId = /*#__PURE__*/new WeakMap();

	var Mailer = /*#__PURE__*/function () {
	  function Mailer() {
	    var _this = this;

	    var config = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {
	      filterId: '',
	      mailboxId: 0,
	      syncAvailable: true
	    };
	    babelHelpers.classCallCheck(this, Mailer);

	    _classPrivateFieldInitSpec(this, _filter, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec(this, _filterToolbar, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec(this, _binding, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec(this, _mailboxId, {
	      writable: true,
	      value: void 0
	    });

	    babelHelpers.defineProperty(this, "focusReset", false);
	    //delete the loader (the envelope is bouncing)
	    var elements = top.document.getElementsByClassName('mail-loader-modifier');

	    var _iterator = _createForOfIteratorHelper(elements),
	        _step;

	    try {
	      for (_iterator.s(); !(_step = _iterator.n()).done;) {
	        var element = _step.value;
	        element.classList.remove('mail-loader-modifier');
	      }
	    } catch (err) {
	      _iterator.e(err);
	    } finally {
	      _iterator.f();
	    }

	    babelHelpers.classPrivateFieldSet(this, _mailboxId, config['mailboxId']);
	    babelHelpers.classPrivateFieldSet(this, _filter, BX.Main.filterManager.getById(config['filterId']));
	    this.sendApplyFilterEventForMenuRefresh(); //Removing the focus from the filter field

	    if (document.activeElement) {
	      document.activeElement.blur();
	    }

	    var mailCounterWrapper = document.querySelector('[data-role="mail-counter-toolbar"]');
	    var filterToolbar = new mail_client_filtertoolbar.FilterToolbar({
	      wrapper: mailCounterWrapper,
	      filter: babelHelpers.classPrivateFieldGet(this, _filter)
	    });
	    filterToolbar.build();
	    babelHelpers.classPrivateFieldSet(this, _filterToolbar, filterToolbar);
	    babelHelpers.classPrivateFieldSet(this, _binding, new mail_client_binding.Binding(babelHelpers.classPrivateFieldGet(this, _mailboxId)));
	    mail_client_binding.Binding.initButtons();
	    main_core_events.EventEmitter.subscribe('Grid::updated', function (event) {
	      var _event$getCompatData = event.getCompatData(),
	          _event$getCompatData2 = babelHelpers.slicedToArray(_event$getCompatData, 1),
	          grid = _event$getCompatData2[0];

	      if (grid !== {} && grid !== undefined && BX.Mail.Home.Grid.getId() === grid.getId()) {
	        mail_client_binding.Binding.initButtons();
	      }
	    });
	    main_core_events.EventEmitter.subscribe('BX.Main.Filter:apply', function (event) {
	      var dir = babelHelpers.classPrivateFieldGet(_this, _filter).getFilterFieldsValues()['DIR'];
	      BX.Mail.Home.Counters.setDirectory(dir);
	    });

	    if (!config['syncAvailable']) {
	      top.BX.UI.InfoHelper.show('limit_contact_center_mail_box_number');
	      var lock = false;

	      var handler = function handler() {
	        if (!lock) {
	          lock = true;
	          top.BX.removeCustomEvent("SidePanel.Slider:onCloseComplete", handler);
	          top.BX.SidePanel.Instance.close();
	        }
	      };

	      top.BX.addCustomEvent("SidePanel.Slider:onCloseComplete", handler);
	    }
	  }

	  babelHelpers.createClass(Mailer, [{
	    key: "sendApplyFilterEventForMenuRefresh",
	    value: function sendApplyFilterEventForMenuRefresh() {
	      if (!!babelHelpers.classPrivateFieldGet(this, _filter) && babelHelpers.classPrivateFieldGet(this, _filter) instanceof BX.Main.Filter) {
	        setTimeout(function () {
	          main_core_events.EventEmitter.emit('BX.Main.Filter:apply', new main_core_events.BaseEvent());
	        }, 1);
	      }
	    }
	  }, {
	    key: "setFilterDir",
	    value: function setFilterDir(name) {
	      if (!!babelHelpers.classPrivateFieldGet(this, _filter) && babelHelpers.classPrivateFieldGet(this, _filter) instanceof BX.Main.Filter) {
	        var FilterApi = babelHelpers.classPrivateFieldGet(this, _filter).getApi();
	        FilterApi.setFields({
	          'DIR': name
	        });
	        FilterApi.apply();
	      }
	    }
	  }, {
	    key: "getFilterToolbar",
	    value: function getFilterToolbar() {
	      return babelHelpers.classPrivateFieldGet(this, _filterToolbar);
	    }
	  }]);
	  return Mailer;
	}();

	exports.Mailer = Mailer;

}((this.BX.Mail.Client = this.BX.Mail.Client || {}),BX.Mail.Client,BX.Mail.Client,BX.Event));
//# sourceMappingURL=mailer.bundle.js.map
