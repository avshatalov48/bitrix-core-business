this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.Ui = this.BX.Landing.Ui || {};
(function (exports,landing_ui_field_basefield,main_loader,main_core,landing_loc,landing_ui_field_smallswitch) {
	'use strict';

	var defaultPaySystemImage = "/bitrix/js/landing/ui/field/paysystemsselectorfield/dist/image/default-pay-system-image.svg";

	var _templateObject, _templateObject2, _templateObject3, _templateObject4, _templateObject5;

	function _regeneratorRuntime() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/facebook/regenerator/blob/main/LICENSE */ _regeneratorRuntime = function _regeneratorRuntime() { return exports; }; var exports = {}, Op = Object.prototype, hasOwn = Op.hasOwnProperty, $Symbol = "function" == typeof Symbol ? Symbol : {}, iteratorSymbol = $Symbol.iterator || "@@iterator", asyncIteratorSymbol = $Symbol.asyncIterator || "@@asyncIterator", toStringTagSymbol = $Symbol.toStringTag || "@@toStringTag"; function define(obj, key, value) { return Object.defineProperty(obj, key, { value: value, enumerable: !0, configurable: !0, writable: !0 }), obj[key]; } try { define({}, ""); } catch (err) { define = function define(obj, key, value) { return obj[key] = value; }; } function wrap(innerFn, outerFn, self, tryLocsList) { var protoGenerator = outerFn && outerFn.prototype instanceof Generator ? outerFn : Generator, generator = Object.create(protoGenerator.prototype), context = new Context(tryLocsList || []); return generator._invoke = function (innerFn, self, context) { var state = "suspendedStart"; return function (method, arg) { if ("executing" === state) throw new Error("Generator is already running"); if ("completed" === state) { if ("throw" === method) throw arg; return doneResult(); } for (context.method = method, context.arg = arg;;) { var delegate = context.delegate; if (delegate) { var delegateResult = maybeInvokeDelegate(delegate, context); if (delegateResult) { if (delegateResult === ContinueSentinel) continue; return delegateResult; } } if ("next" === context.method) context.sent = context._sent = context.arg;else if ("throw" === context.method) { if ("suspendedStart" === state) throw state = "completed", context.arg; context.dispatchException(context.arg); } else "return" === context.method && context.abrupt("return", context.arg); state = "executing"; var record = tryCatch(innerFn, self, context); if ("normal" === record.type) { if (state = context.done ? "completed" : "suspendedYield", record.arg === ContinueSentinel) continue; return { value: record.arg, done: context.done }; } "throw" === record.type && (state = "completed", context.method = "throw", context.arg = record.arg); } }; }(innerFn, self, context), generator; } function tryCatch(fn, obj, arg) { try { return { type: "normal", arg: fn.call(obj, arg) }; } catch (err) { return { type: "throw", arg: err }; } } exports.wrap = wrap; var ContinueSentinel = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} var IteratorPrototype = {}; define(IteratorPrototype, iteratorSymbol, function () { return this; }); var getProto = Object.getPrototypeOf, NativeIteratorPrototype = getProto && getProto(getProto(values([]))); NativeIteratorPrototype && NativeIteratorPrototype !== Op && hasOwn.call(NativeIteratorPrototype, iteratorSymbol) && (IteratorPrototype = NativeIteratorPrototype); var Gp = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(IteratorPrototype); function defineIteratorMethods(prototype) { ["next", "throw", "return"].forEach(function (method) { define(prototype, method, function (arg) { return this._invoke(method, arg); }); }); } function AsyncIterator(generator, PromiseImpl) { function invoke(method, arg, resolve, reject) { var record = tryCatch(generator[method], generator, arg); if ("throw" !== record.type) { var result = record.arg, value = result.value; return value && "object" == babelHelpers["typeof"](value) && hasOwn.call(value, "__await") ? PromiseImpl.resolve(value.__await).then(function (value) { invoke("next", value, resolve, reject); }, function (err) { invoke("throw", err, resolve, reject); }) : PromiseImpl.resolve(value).then(function (unwrapped) { result.value = unwrapped, resolve(result); }, function (error) { return invoke("throw", error, resolve, reject); }); } reject(record.arg); } var previousPromise; this._invoke = function (method, arg) { function callInvokeWithMethodAndArg() { return new PromiseImpl(function (resolve, reject) { invoke(method, arg, resolve, reject); }); } return previousPromise = previousPromise ? previousPromise.then(callInvokeWithMethodAndArg, callInvokeWithMethodAndArg) : callInvokeWithMethodAndArg(); }; } function maybeInvokeDelegate(delegate, context) { var method = delegate.iterator[context.method]; if (undefined === method) { if (context.delegate = null, "throw" === context.method) { if (delegate.iterator["return"] && (context.method = "return", context.arg = undefined, maybeInvokeDelegate(delegate, context), "throw" === context.method)) return ContinueSentinel; context.method = "throw", context.arg = new TypeError("The iterator does not provide a 'throw' method"); } return ContinueSentinel; } var record = tryCatch(method, delegate.iterator, context.arg); if ("throw" === record.type) return context.method = "throw", context.arg = record.arg, context.delegate = null, ContinueSentinel; var info = record.arg; return info ? info.done ? (context[delegate.resultName] = info.value, context.next = delegate.nextLoc, "return" !== context.method && (context.method = "next", context.arg = undefined), context.delegate = null, ContinueSentinel) : info : (context.method = "throw", context.arg = new TypeError("iterator result is not an object"), context.delegate = null, ContinueSentinel); } function pushTryEntry(locs) { var entry = { tryLoc: locs[0] }; 1 in locs && (entry.catchLoc = locs[1]), 2 in locs && (entry.finallyLoc = locs[2], entry.afterLoc = locs[3]), this.tryEntries.push(entry); } function resetTryEntry(entry) { var record = entry.completion || {}; record.type = "normal", delete record.arg, entry.completion = record; } function Context(tryLocsList) { this.tryEntries = [{ tryLoc: "root" }], tryLocsList.forEach(pushTryEntry, this), this.reset(!0); } function values(iterable) { if (iterable) { var iteratorMethod = iterable[iteratorSymbol]; if (iteratorMethod) return iteratorMethod.call(iterable); if ("function" == typeof iterable.next) return iterable; if (!isNaN(iterable.length)) { var i = -1, next = function next() { for (; ++i < iterable.length;) { if (hasOwn.call(iterable, i)) return next.value = iterable[i], next.done = !1, next; } return next.value = undefined, next.done = !0, next; }; return next.next = next; } } return { next: doneResult }; } function doneResult() { return { value: undefined, done: !0 }; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, define(Gp, "constructor", GeneratorFunctionPrototype), define(GeneratorFunctionPrototype, "constructor", GeneratorFunction), GeneratorFunction.displayName = define(GeneratorFunctionPrototype, toStringTagSymbol, "GeneratorFunction"), exports.isGeneratorFunction = function (genFun) { var ctor = "function" == typeof genFun && genFun.constructor; return !!ctor && (ctor === GeneratorFunction || "GeneratorFunction" === (ctor.displayName || ctor.name)); }, exports.mark = function (genFun) { return Object.setPrototypeOf ? Object.setPrototypeOf(genFun, GeneratorFunctionPrototype) : (genFun.__proto__ = GeneratorFunctionPrototype, define(genFun, toStringTagSymbol, "GeneratorFunction")), genFun.prototype = Object.create(Gp), genFun; }, exports.awrap = function (arg) { return { __await: arg }; }, defineIteratorMethods(AsyncIterator.prototype), define(AsyncIterator.prototype, asyncIteratorSymbol, function () { return this; }), exports.AsyncIterator = AsyncIterator, exports.async = function (innerFn, outerFn, self, tryLocsList, PromiseImpl) { void 0 === PromiseImpl && (PromiseImpl = Promise); var iter = new AsyncIterator(wrap(innerFn, outerFn, self, tryLocsList), PromiseImpl); return exports.isGeneratorFunction(outerFn) ? iter : iter.next().then(function (result) { return result.done ? result.value : iter.next(); }); }, defineIteratorMethods(Gp), define(Gp, toStringTagSymbol, "Generator"), define(Gp, iteratorSymbol, function () { return this; }), define(Gp, "toString", function () { return "[object Generator]"; }), exports.keys = function (object) { var keys = []; for (var key in object) { keys.push(key); } return keys.reverse(), function next() { for (; keys.length;) { var key = keys.pop(); if (key in object) return next.value = key, next.done = !1, next; } return next.done = !0, next; }; }, exports.values = values, Context.prototype = { constructor: Context, reset: function reset(skipTempReset) { if (this.prev = 0, this.next = 0, this.sent = this._sent = undefined, this.done = !1, this.delegate = null, this.method = "next", this.arg = undefined, this.tryEntries.forEach(resetTryEntry), !skipTempReset) for (var name in this) { "t" === name.charAt(0) && hasOwn.call(this, name) && !isNaN(+name.slice(1)) && (this[name] = undefined); } }, stop: function stop() { this.done = !0; var rootRecord = this.tryEntries[0].completion; if ("throw" === rootRecord.type) throw rootRecord.arg; return this.rval; }, dispatchException: function dispatchException(exception) { if (this.done) throw exception; var context = this; function handle(loc, caught) { return record.type = "throw", record.arg = exception, context.next = loc, caught && (context.method = "next", context.arg = undefined), !!caught; } for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i], record = entry.completion; if ("root" === entry.tryLoc) return handle("end"); if (entry.tryLoc <= this.prev) { var hasCatch = hasOwn.call(entry, "catchLoc"), hasFinally = hasOwn.call(entry, "finallyLoc"); if (hasCatch && hasFinally) { if (this.prev < entry.catchLoc) return handle(entry.catchLoc, !0); if (this.prev < entry.finallyLoc) return handle(entry.finallyLoc); } else if (hasCatch) { if (this.prev < entry.catchLoc) return handle(entry.catchLoc, !0); } else { if (!hasFinally) throw new Error("try statement without catch or finally"); if (this.prev < entry.finallyLoc) return handle(entry.finallyLoc); } } } }, abrupt: function abrupt(type, arg) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.tryLoc <= this.prev && hasOwn.call(entry, "finallyLoc") && this.prev < entry.finallyLoc) { var finallyEntry = entry; break; } } finallyEntry && ("break" === type || "continue" === type) && finallyEntry.tryLoc <= arg && arg <= finallyEntry.finallyLoc && (finallyEntry = null); var record = finallyEntry ? finallyEntry.completion : {}; return record.type = type, record.arg = arg, finallyEntry ? (this.method = "next", this.next = finallyEntry.finallyLoc, ContinueSentinel) : this.complete(record); }, complete: function complete(record, afterLoc) { if ("throw" === record.type) throw record.arg; return "break" === record.type || "continue" === record.type ? this.next = record.arg : "return" === record.type ? (this.rval = this.arg = record.arg, this.method = "return", this.next = "end") : "normal" === record.type && afterLoc && (this.next = afterLoc), ContinueSentinel; }, finish: function finish(finallyLoc) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.finallyLoc === finallyLoc) return this.complete(entry.completion, entry.afterLoc), resetTryEntry(entry), ContinueSentinel; } }, "catch": function _catch(tryLoc) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.tryLoc === tryLoc) { var record = entry.completion; if ("throw" === record.type) { var thrown = record.arg; resetTryEntry(entry); } return thrown; } } throw new Error("illegal catch attempt"); }, delegateYield: function delegateYield(iterable, resultName, nextLoc) { return this.delegate = { iterator: values(iterable), resultName: resultName, nextLoc: nextLoc }, "next" === this.method && (this.arg = undefined), ContinueSentinel; } }, exports; }

	function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }

	function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }

	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }

	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }

	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }

	var _disabledPaySystems = /*#__PURE__*/new WeakMap();

	var _allPaySystems = /*#__PURE__*/new WeakMap();

	var _onFetchPaySystemsError = /*#__PURE__*/new WeakMap();

	var _showMorePaySystemsBtn = /*#__PURE__*/new WeakMap();

	var _morePaySystemsBtnSidePanelPath = /*#__PURE__*/new WeakMap();

	var _minLoaderShowTime = /*#__PURE__*/new WeakMap();

	var _getPaySystemsList = /*#__PURE__*/new WeakSet();

	var _updatePaySystems = /*#__PURE__*/new WeakSet();

	var _updateAndRenderPaySystems = /*#__PURE__*/new WeakSet();

	var _renderLayout = /*#__PURE__*/new WeakSet();

	var _renderRecommendedPaySystems = /*#__PURE__*/new WeakSet();

	var _renderShowMorePaySystemsBtn = /*#__PURE__*/new WeakSet();

	var _getShowMorePaySystemsBtn = /*#__PURE__*/new WeakSet();

	var _getRecommendedPaySystemsLayout = /*#__PURE__*/new WeakSet();

	var _getActivePaySystemLayout = /*#__PURE__*/new WeakSet();

	var _onRecommendedSliderClose = /*#__PURE__*/new WeakSet();

	var _renderActivePaySystems = /*#__PURE__*/new WeakSet();

	var _isPaySystemActiveInForm = /*#__PURE__*/new WeakSet();

	var _onPaySystemSwitchChange = /*#__PURE__*/new WeakSet();

	var _getDefaultPaySystemLayout = /*#__PURE__*/new WeakSet();

	var _getLoader = /*#__PURE__*/new WeakSet();

	var _onMorePaySystemSliderClose = /*#__PURE__*/new WeakSet();

	var PaySystemsSelectorField = /*#__PURE__*/function (_BaseField) {
	  babelHelpers.inherits(PaySystemsSelectorField, _BaseField);

	  // in milliseconds
	  function PaySystemsSelectorField() {
	    var _this;

	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, PaySystemsSelectorField);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(PaySystemsSelectorField).call(this, options));

	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _onMorePaySystemSliderClose);

	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _getLoader);

	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _getDefaultPaySystemLayout);

	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _onPaySystemSwitchChange);

	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _isPaySystemActiveInForm);

	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _renderActivePaySystems);

	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _onRecommendedSliderClose);

	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _getActivePaySystemLayout);

	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _getRecommendedPaySystemsLayout);

	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _getShowMorePaySystemsBtn);

	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _renderShowMorePaySystemsBtn);

	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _renderRecommendedPaySystems);

	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _renderLayout);

	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _updateAndRenderPaySystems);

	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _updatePaySystems);

	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _getPaySystemsList);

	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _disabledPaySystems, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _allPaySystems, {
	      writable: true,
	      value: {
	        active: [],
	        recommended: []
	      }
	    });

	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _onFetchPaySystemsError, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _showMorePaySystemsBtn, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _morePaySystemsBtnSidePanelPath, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _minLoaderShowTime, {
	      writable: true,
	      value: 3000
	    });

	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _disabledPaySystems, Reflect.has(options, 'disabledPaySystems') ? options.disabledPaySystems : []);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _onFetchPaySystemsError, Reflect.has(options, 'onFetchPaySystemsError') ? options.onFetchPaySystemsError : function () {});
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _showMorePaySystemsBtn, Reflect.has(options, 'showMorePaySystemsBtn') ? options.showMorePaySystemsBtn : false);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _morePaySystemsBtnSidePanelPath, Reflect.has(options, 'morePaySystemsBtnSidePanelPath') ? options.morePaySystemsBtnSidePanelPath : '');
	    main_core.Dom.clean(_this.getLayout());

	    _classPrivateMethodGet(babelHelpers.assertThisInitialized(_this), _updateAndRenderPaySystems, _updateAndRenderPaySystems2).call(babelHelpers.assertThisInitialized(_this), true);

	    return _this;
	  }

	  babelHelpers.createClass(PaySystemsSelectorField, [{
	    key: "getValue",
	    value: function getValue() {
	      return {
	        allPaySystems: _objectSpread({}, babelHelpers.classPrivateFieldGet(this, _allPaySystems)),
	        disabledPaySystems: babelHelpers.toConsumableArray(babelHelpers.classPrivateFieldGet(this, _disabledPaySystems))
	      };
	    }
	  }]);
	  return PaySystemsSelectorField;
	}(landing_ui_field_basefield.BaseField);

	function _getPaySystemsList2() {
	  return BX.ajax.runAction('crm.api.form.paysystem.list', {
	    json: {}
	  }).then(function (response) {
	    return response.data;
	  });
	}

	function _updatePaySystems2() {
	  var _this2 = this;

	  return _classPrivateMethodGet(this, _getPaySystemsList, _getPaySystemsList2).call(this).then(function (paySystems) {
	    var oldPaySystemIds = babelHelpers.classPrivateFieldGet(_this2, _allPaySystems).active.map(function (ps) {
	      return ps.id;
	    }).sort();
	    var newPaySystemIds = paySystems.active.map(function (ps) {
	      return ps.id;
	    }).sort();
	    babelHelpers.classPrivateFieldSet(_this2, _allPaySystems, paySystems);
	    var result = {
	      paySystems: paySystems,
	      isUpdated: false
	    };

	    if (oldPaySystemIds.length !== newPaySystemIds.length) {
	      result.isUpdated = true;
	      return result;
	    }

	    for (var index = 0; index < oldPaySystemIds.length; index++) {
	      if (oldPaySystemIds[index] !== newPaySystemIds[index]) {
	        result.isUpdated = true;
	        return result;
	      }
	    }

	    return result;
	  })["catch"](function (response) {
	    babelHelpers.classPrivateFieldGet(_this2, _onFetchPaySystemsError).call(_this2, response.errors);
	  });
	}

	function _updateAndRenderPaySystems2() {
	  var _this3 = this;

	  var useLoaderOnFetchStart = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : true;
	  var minLoaderShowTime = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 0;

	  if (useLoaderOnFetchStart) {
	    main_core.Dom.clean(this.getLayout());

	    _classPrivateMethodGet(this, _getLoader, _getLoader2).call(this).show();
	  }

	  var loaderEndTime = useLoaderOnFetchStart ? Date.now() + minLoaderShowTime : null;
	  return _classPrivateMethodGet(this, _updatePaySystems, _updatePaySystems2).call(this).then( /*#__PURE__*/function () {
	    var _ref2 = babelHelpers.asyncToGenerator( /*#__PURE__*/_regeneratorRuntime().mark(function _callee(_ref) {
	      var paySystems, isUpdated;
	      return _regeneratorRuntime().wrap(function _callee$(_context) {
	        while (1) {
	          switch (_context.prev = _context.next) {
	            case 0:
	              paySystems = _ref.paySystems, isUpdated = _ref.isUpdated;

	              if (!isUpdated) {
	                _context.next = 9;
	                break;
	              }

	              main_core.Dom.clean(_this3.getLayout());

	              _classPrivateMethodGet(_this3, _getLoader, _getLoader2).call(_this3).show();

	              loaderEndTime = loaderEndTime !== null ? loaderEndTime : Date.now() + minLoaderShowTime;
	              _context.next = 7;
	              return new Promise(function (resolve) {
	                return setTimeout(resolve, loaderEndTime - Date.now());
	              });

	            case 7:
	              _classPrivateMethodGet(_this3, _getLoader, _getLoader2).call(_this3).hide();

	              _classPrivateMethodGet(_this3, _renderLayout, _renderLayout2).call(_this3);

	            case 9:
	              return _context.abrupt("return", paySystems);

	            case 10:
	            case "end":
	              return _context.stop();
	          }
	        }
	      }, _callee);
	    }));

	    return function (_x) {
	      return _ref2.apply(this, arguments);
	    };
	  }());
	}

	function _renderLayout2() {
	  main_core.Dom.clean(this.getLayout());

	  _classPrivateMethodGet(this, _renderActivePaySystems, _renderActivePaySystems2).call(this);

	  _classPrivateMethodGet(this, _renderRecommendedPaySystems, _renderRecommendedPaySystems2).call(this);

	  if (babelHelpers.classPrivateFieldGet(this, _showMorePaySystemsBtn) && babelHelpers.classPrivateFieldGet(this, _morePaySystemsBtnSidePanelPath)) {
	    _classPrivateMethodGet(this, _renderShowMorePaySystemsBtn, _renderShowMorePaySystemsBtn2).call(this);
	  }
	}

	function _renderRecommendedPaySystems2() {
	  var _this4 = this;

	  babelHelpers.classPrivateFieldGet(this, _allPaySystems).recommended.forEach(function (paySystem) {
	    main_core.Dom.append(_classPrivateMethodGet(_this4, _getRecommendedPaySystemsLayout, _getRecommendedPaySystemsLayout2).call(_this4, paySystem), _this4.getLayout());
	  });
	}

	function _renderShowMorePaySystemsBtn2() {
	  main_core.Dom.append(_classPrivateMethodGet(this, _getShowMorePaySystemsBtn, _getShowMorePaySystemsBtn2).call(this), this.getLayout());
	}

	function _getShowMorePaySystemsBtn2() {
	  var _this5 = this;

	  return this.cache.remember('showMorePaySystemsBtn', function () {
	    var btnLayout = main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<button type=\"button\" class=\"landing-ui-content-pay-system-more-ps\">\n\t\t\t\t<span class=\"landing-ui-content-pay-system-more-ps-text\">\n\t\t\t\t\t", "\n\t\t\t\t</span>\n\t\t\t</button>\n\t\t\t"])), landing_loc.Loc.getMessage('LANDING_FORM_PAY_SYSTEMS_CONNECT_OTHER_PAY_SYSTEM'));

	    btnLayout.onclick = function () {
	      BX.SidePanel.Instance.open(babelHelpers.classPrivateFieldGet(_this5, _morePaySystemsBtnSidePanelPath), {
	        events: {
	          onCloseComplete: function onCloseComplete(event) {
	            return _classPrivateMethodGet(_this5, _onMorePaySystemSliderClose, _onMorePaySystemSliderClose2).call(_this5, event);
	          }
	        }
	      });
	    };

	    return btnLayout;
	  });
	}

	function _getRecommendedPaySystemsLayout2(paySystemData) {
	  var _this6 = this;

	  return this.cache.remember('recommendedPaySystem:' + paySystemData.id, function () {
	    var paySystemLayout = _classPrivateMethodGet(_this6, _getDefaultPaySystemLayout, _getDefaultPaySystemLayout2).call(_this6, paySystemData.title, paySystemData.image);

	    var connectBtnLayout = main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class=\"landing-ui-field-pay-system-selector-connect-recommended\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t"])), landing_loc.Loc.getMessage('LANDING_FORM_PAY_SYSTEMS_CONNECT_RECOMMENDED_PAY_SYSTEM_TEXT'));
	    main_core.Event.bind(connectBtnLayout, 'click', function () {
	      return BX.SidePanel.Instance.open(paySystemData.editPath, {
	        events: {
	          onCloseComplete: function onCloseComplete(event) {
	            _classPrivateMethodGet(_this6, _onRecommendedSliderClose, _onRecommendedSliderClose2).call(_this6, event);
	          }
	        }
	      });
	    });
	    main_core.Dom.append(connectBtnLayout, paySystemLayout);
	    return paySystemLayout;
	  });
	}

	function _getActivePaySystemLayout2(paySystemData) {
	  var _this7 = this;

	  return this.cache.remember('formPaySystem:' + paySystemData.id, function () {
	    var _paySystemData$image;

	    var paySystemLayout = _classPrivateMethodGet(_this7, _getDefaultPaySystemLayout, _getDefaultPaySystemLayout2).call(_this7, paySystemData.title, (_paySystemData$image = paySystemData.image) !== null && _paySystemData$image !== void 0 ? _paySystemData$image : defaultPaySystemImage);

	    var switcher = new landing_ui_field_smallswitch.SmallSwitch({
	      value: _classPrivateMethodGet(_this7, _isPaySystemActiveInForm, _isPaySystemActiveInForm2).call(_this7, paySystemData.id)
	    });
	    main_core.Dom.addClass(switcher.getLayout(), 'landing-ui-field-pay-system-selector-ps-switch');
	    main_core.Dom.append(switcher.getLayout(), paySystemLayout);
	    switcher.subscribe('onChange', function () {
	      return _classPrivateMethodGet(_this7, _onPaySystemSwitchChange, _onPaySystemSwitchChange2).call(_this7, paySystemData);
	    });
	    return paySystemLayout;
	  });
	}

	function _onRecommendedSliderClose2(event) {
	  _classPrivateMethodGet(this, _updateAndRenderPaySystems, _updateAndRenderPaySystems2).call(this, false, babelHelpers.classPrivateFieldGet(this, _minLoaderShowTime));
	}

	function _renderActivePaySystems2() {
	  var _this8 = this;

	  var paySystemSortRule = function paySystemSortRule(paySystem1, paySystem2) {
	    // sort by active status
	    var paySystem1ActivationStatus = _classPrivateMethodGet(_this8, _isPaySystemActiveInForm, _isPaySystemActiveInForm2).call(_this8, paySystem1.id);

	    var paySystem2ActivationStatus = _classPrivateMethodGet(_this8, _isPaySystemActiveInForm, _isPaySystemActiveInForm2).call(_this8, paySystem2.id);

	    if (paySystem1ActivationStatus !== paySystem2ActivationStatus) {
	      return paySystem1ActivationStatus ? -1 : 1;
	    } // sort by id


	    return paySystem2.id - paySystem1.id;
	  };

	  babelHelpers.classPrivateFieldGet(this, _allPaySystems).active.sort(paySystemSortRule).forEach(function (paySystem) {
	    main_core.Dom.append(_classPrivateMethodGet(_this8, _getActivePaySystemLayout, _getActivePaySystemLayout2).call(_this8, paySystem), _this8.getLayout());
	  });
	}

	function _isPaySystemActiveInForm2(paySystemId) {
	  return !babelHelpers.classPrivateFieldGet(this, _disabledPaySystems).includes(paySystemId);
	}

	function _onPaySystemSwitchChange2(paySystemData) {
	  if (_classPrivateMethodGet(this, _isPaySystemActiveInForm, _isPaySystemActiveInForm2).call(this, paySystemData.id)) {
	    babelHelpers.classPrivateFieldGet(this, _disabledPaySystems).push(paySystemData.id);
	  } else {
	    babelHelpers.classPrivateFieldGet(this, _disabledPaySystems).splice(babelHelpers.classPrivateFieldGet(this, _disabledPaySystems).indexOf(paySystemData.id), 1);
	  }

	  this.emit('onChange');
	}

	function _getDefaultPaySystemLayout2(title, image) {
	  var paySystemLayout = main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"landing-ui-field-pay-system-selector-ps-wrapper\">\n\t\t\t\t<div class=\"landing-ui-field-pay-system-selector-ps-img\"></div>\n\t\t\t</div>\n\t\t"])));
	  main_core.Dom.append(main_core.Tag.render(_templateObject4 || (_templateObject4 = babelHelpers.taggedTemplateLiteral(["<img src=\"", "\">"])), image), paySystemLayout.children[0]);
	  main_core.Dom.append(main_core.Tag.render(_templateObject5 || (_templateObject5 = babelHelpers.taggedTemplateLiteral(["<div class=\"landing-ui-field-pay-system-ps-title\">", "</div>"])), main_core.Text.encode(title)), paySystemLayout);
	  return paySystemLayout;
	}

	function _getLoader2() {
	  var _this9 = this;

	  return this.cache.remember('loader', function () {
	    return new main_loader.Loader({
	      target: _this9.layout,
	      size: 50,
	      mode: 'inline',
	      offset: {
	        top: '5px',
	        left: '250px'
	      }
	    });
	  });
	}

	function _onMorePaySystemSliderClose2(event) {
	  _classPrivateMethodGet(this, _updateAndRenderPaySystems, _updateAndRenderPaySystems2).call(this, false, babelHelpers.classPrivateFieldGet(this, _minLoaderShowTime));
	}

	exports.PaySystemsSelectorField = PaySystemsSelectorField;

}((this.BX.Landing.Ui.Field = this.BX.Landing.Ui.Field || {}),BX.Landing.UI.Field,BX,BX,BX.Landing,BX.Landing.UI.Field));
//# sourceMappingURL=paysystemsselectorfield.bundle.js.map
