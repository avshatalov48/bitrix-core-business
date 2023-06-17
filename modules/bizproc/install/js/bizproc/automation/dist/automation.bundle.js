this.BX = this.BX || {};
this.BX.Bizproc = this.BX.Bizproc || {};
(function (exports,ui_alerts,ui_hint,bizproc_condition,main_core_events,ui_entitySelector,main_date,main_popup,bizproc_globals,bizproc_automation,ui_designTokens,ui_fonts_opensans,main_core,ui_tour) {
	'use strict';

	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	var _documentType = /*#__PURE__*/new WeakMap();
	var _category = /*#__PURE__*/new WeakMap();
	var _status = /*#__PURE__*/new WeakMap();
	var TemplateScope = /*#__PURE__*/function () {
	  function TemplateScope(rawTemplateScope) {
	    babelHelpers.classCallCheck(this, TemplateScope);
	    _classPrivateFieldInitSpec(this, _documentType, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _category, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _status, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldSet(this, _documentType, rawTemplateScope.DocumentType);
	    babelHelpers.classPrivateFieldSet(this, _category, !main_core.Type.isNil(rawTemplateScope.Category.Id) ? rawTemplateScope.Category : null);
	    babelHelpers.classPrivateFieldSet(this, _status, rawTemplateScope.Status);
	  }
	  babelHelpers.createClass(TemplateScope, [{
	    key: "getId",
	    value: function getId() {
	      if (this.hasCategory()) {
	        return "".concat(babelHelpers.classPrivateFieldGet(this, _documentType).Type, "_").concat(babelHelpers.classPrivateFieldGet(this, _category).Id, "_").concat(babelHelpers.classPrivateFieldGet(this, _status).Id);
	      }
	      return "".concat(babelHelpers.classPrivateFieldGet(this, _documentType).Type, "_").concat(babelHelpers.classPrivateFieldGet(this, _status).Id);
	    }
	  }, {
	    key: "getDocumentType",
	    value: function getDocumentType() {
	      return babelHelpers.classPrivateFieldGet(this, _documentType);
	    }
	  }, {
	    key: "getDocumentCategory",
	    value: function getDocumentCategory() {
	      return babelHelpers.classPrivateFieldGet(this, _category);
	    }
	  }, {
	    key: "getDocumentStatus",
	    value: function getDocumentStatus() {
	      return babelHelpers.classPrivateFieldGet(this, _status);
	    }
	  }, {
	    key: "hasCategory",
	    value: function hasCategory() {
	      return !main_core.Type.isNull(babelHelpers.classPrivateFieldGet(this, _category));
	    }
	  }]);
	  return TemplateScope;
	}();

	function _regeneratorRuntime() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/facebook/regenerator/blob/main/LICENSE */ _regeneratorRuntime = function _regeneratorRuntime() { return exports; }; var exports = {}, Op = Object.prototype, hasOwn = Op.hasOwnProperty, defineProperty = Object.defineProperty || function (obj, key, desc) { obj[key] = desc.value; }, $Symbol = "function" == typeof Symbol ? Symbol : {}, iteratorSymbol = $Symbol.iterator || "@@iterator", asyncIteratorSymbol = $Symbol.asyncIterator || "@@asyncIterator", toStringTagSymbol = $Symbol.toStringTag || "@@toStringTag"; function define(obj, key, value) { return Object.defineProperty(obj, key, { value: value, enumerable: !0, configurable: !0, writable: !0 }), obj[key]; } try { define({}, ""); } catch (err) { define = function define(obj, key, value) { return obj[key] = value; }; } function wrap(innerFn, outerFn, self, tryLocsList) { var protoGenerator = outerFn && outerFn.prototype instanceof Generator ? outerFn : Generator, generator = Object.create(protoGenerator.prototype), context = new Context(tryLocsList || []); return defineProperty(generator, "_invoke", { value: makeInvokeMethod(innerFn, self, context) }), generator; } function tryCatch(fn, obj, arg) { try { return { type: "normal", arg: fn.call(obj, arg) }; } catch (err) { return { type: "throw", arg: err }; } } exports.wrap = wrap; var ContinueSentinel = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} var IteratorPrototype = {}; define(IteratorPrototype, iteratorSymbol, function () { return this; }); var getProto = Object.getPrototypeOf, NativeIteratorPrototype = getProto && getProto(getProto(values([]))); NativeIteratorPrototype && NativeIteratorPrototype !== Op && hasOwn.call(NativeIteratorPrototype, iteratorSymbol) && (IteratorPrototype = NativeIteratorPrototype); var Gp = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(IteratorPrototype); function defineIteratorMethods(prototype) { ["next", "throw", "return"].forEach(function (method) { define(prototype, method, function (arg) { return this._invoke(method, arg); }); }); } function AsyncIterator(generator, PromiseImpl) { function invoke(method, arg, resolve, reject) { var record = tryCatch(generator[method], generator, arg); if ("throw" !== record.type) { var result = record.arg, value = result.value; return value && "object" == babelHelpers["typeof"](value) && hasOwn.call(value, "__await") ? PromiseImpl.resolve(value.__await).then(function (value) { invoke("next", value, resolve, reject); }, function (err) { invoke("throw", err, resolve, reject); }) : PromiseImpl.resolve(value).then(function (unwrapped) { result.value = unwrapped, resolve(result); }, function (error) { return invoke("throw", error, resolve, reject); }); } reject(record.arg); } var previousPromise; defineProperty(this, "_invoke", { value: function value(method, arg) { function callInvokeWithMethodAndArg() { return new PromiseImpl(function (resolve, reject) { invoke(method, arg, resolve, reject); }); } return previousPromise = previousPromise ? previousPromise.then(callInvokeWithMethodAndArg, callInvokeWithMethodAndArg) : callInvokeWithMethodAndArg(); } }); } function makeInvokeMethod(innerFn, self, context) { var state = "suspendedStart"; return function (method, arg) { if ("executing" === state) throw new Error("Generator is already running"); if ("completed" === state) { if ("throw" === method) throw arg; return doneResult(); } for (context.method = method, context.arg = arg;;) { var delegate = context.delegate; if (delegate) { var delegateResult = maybeInvokeDelegate(delegate, context); if (delegateResult) { if (delegateResult === ContinueSentinel) continue; return delegateResult; } } if ("next" === context.method) context.sent = context._sent = context.arg;else if ("throw" === context.method) { if ("suspendedStart" === state) throw state = "completed", context.arg; context.dispatchException(context.arg); } else "return" === context.method && context.abrupt("return", context.arg); state = "executing"; var record = tryCatch(innerFn, self, context); if ("normal" === record.type) { if (state = context.done ? "completed" : "suspendedYield", record.arg === ContinueSentinel) continue; return { value: record.arg, done: context.done }; } "throw" === record.type && (state = "completed", context.method = "throw", context.arg = record.arg); } }; } function maybeInvokeDelegate(delegate, context) { var methodName = context.method, method = delegate.iterator[methodName]; if (undefined === method) return context.delegate = null, "throw" === methodName && delegate.iterator["return"] && (context.method = "return", context.arg = undefined, maybeInvokeDelegate(delegate, context), "throw" === context.method) || "return" !== methodName && (context.method = "throw", context.arg = new TypeError("The iterator does not provide a '" + methodName + "' method")), ContinueSentinel; var record = tryCatch(method, delegate.iterator, context.arg); if ("throw" === record.type) return context.method = "throw", context.arg = record.arg, context.delegate = null, ContinueSentinel; var info = record.arg; return info ? info.done ? (context[delegate.resultName] = info.value, context.next = delegate.nextLoc, "return" !== context.method && (context.method = "next", context.arg = undefined), context.delegate = null, ContinueSentinel) : info : (context.method = "throw", context.arg = new TypeError("iterator result is not an object"), context.delegate = null, ContinueSentinel); } function pushTryEntry(locs) { var entry = { tryLoc: locs[0] }; 1 in locs && (entry.catchLoc = locs[1]), 2 in locs && (entry.finallyLoc = locs[2], entry.afterLoc = locs[3]), this.tryEntries.push(entry); } function resetTryEntry(entry) { var record = entry.completion || {}; record.type = "normal", delete record.arg, entry.completion = record; } function Context(tryLocsList) { this.tryEntries = [{ tryLoc: "root" }], tryLocsList.forEach(pushTryEntry, this), this.reset(!0); } function values(iterable) { if (iterable) { var iteratorMethod = iterable[iteratorSymbol]; if (iteratorMethod) return iteratorMethod.call(iterable); if ("function" == typeof iterable.next) return iterable; if (!isNaN(iterable.length)) { var i = -1, next = function next() { for (; ++i < iterable.length;) if (hasOwn.call(iterable, i)) return next.value = iterable[i], next.done = !1, next; return next.value = undefined, next.done = !0, next; }; return next.next = next; } } return { next: doneResult }; } function doneResult() { return { value: undefined, done: !0 }; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, defineProperty(Gp, "constructor", { value: GeneratorFunctionPrototype, configurable: !0 }), defineProperty(GeneratorFunctionPrototype, "constructor", { value: GeneratorFunction, configurable: !0 }), GeneratorFunction.displayName = define(GeneratorFunctionPrototype, toStringTagSymbol, "GeneratorFunction"), exports.isGeneratorFunction = function (genFun) { var ctor = "function" == typeof genFun && genFun.constructor; return !!ctor && (ctor === GeneratorFunction || "GeneratorFunction" === (ctor.displayName || ctor.name)); }, exports.mark = function (genFun) { return Object.setPrototypeOf ? Object.setPrototypeOf(genFun, GeneratorFunctionPrototype) : (genFun.__proto__ = GeneratorFunctionPrototype, define(genFun, toStringTagSymbol, "GeneratorFunction")), genFun.prototype = Object.create(Gp), genFun; }, exports.awrap = function (arg) { return { __await: arg }; }, defineIteratorMethods(AsyncIterator.prototype), define(AsyncIterator.prototype, asyncIteratorSymbol, function () { return this; }), exports.AsyncIterator = AsyncIterator, exports.async = function (innerFn, outerFn, self, tryLocsList, PromiseImpl) { void 0 === PromiseImpl && (PromiseImpl = Promise); var iter = new AsyncIterator(wrap(innerFn, outerFn, self, tryLocsList), PromiseImpl); return exports.isGeneratorFunction(outerFn) ? iter : iter.next().then(function (result) { return result.done ? result.value : iter.next(); }); }, defineIteratorMethods(Gp), define(Gp, toStringTagSymbol, "Generator"), define(Gp, iteratorSymbol, function () { return this; }), define(Gp, "toString", function () { return "[object Generator]"; }), exports.keys = function (val) { var object = Object(val), keys = []; for (var key in object) keys.push(key); return keys.reverse(), function next() { for (; keys.length;) { var key = keys.pop(); if (key in object) return next.value = key, next.done = !1, next; } return next.done = !0, next; }; }, exports.values = values, Context.prototype = { constructor: Context, reset: function reset(skipTempReset) { if (this.prev = 0, this.next = 0, this.sent = this._sent = undefined, this.done = !1, this.delegate = null, this.method = "next", this.arg = undefined, this.tryEntries.forEach(resetTryEntry), !skipTempReset) for (var name in this) "t" === name.charAt(0) && hasOwn.call(this, name) && !isNaN(+name.slice(1)) && (this[name] = undefined); }, stop: function stop() { this.done = !0; var rootRecord = this.tryEntries[0].completion; if ("throw" === rootRecord.type) throw rootRecord.arg; return this.rval; }, dispatchException: function dispatchException(exception) { if (this.done) throw exception; var context = this; function handle(loc, caught) { return record.type = "throw", record.arg = exception, context.next = loc, caught && (context.method = "next", context.arg = undefined), !!caught; } for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i], record = entry.completion; if ("root" === entry.tryLoc) return handle("end"); if (entry.tryLoc <= this.prev) { var hasCatch = hasOwn.call(entry, "catchLoc"), hasFinally = hasOwn.call(entry, "finallyLoc"); if (hasCatch && hasFinally) { if (this.prev < entry.catchLoc) return handle(entry.catchLoc, !0); if (this.prev < entry.finallyLoc) return handle(entry.finallyLoc); } else if (hasCatch) { if (this.prev < entry.catchLoc) return handle(entry.catchLoc, !0); } else { if (!hasFinally) throw new Error("try statement without catch or finally"); if (this.prev < entry.finallyLoc) return handle(entry.finallyLoc); } } } }, abrupt: function abrupt(type, arg) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.tryLoc <= this.prev && hasOwn.call(entry, "finallyLoc") && this.prev < entry.finallyLoc) { var finallyEntry = entry; break; } } finallyEntry && ("break" === type || "continue" === type) && finallyEntry.tryLoc <= arg && arg <= finallyEntry.finallyLoc && (finallyEntry = null); var record = finallyEntry ? finallyEntry.completion : {}; return record.type = type, record.arg = arg, finallyEntry ? (this.method = "next", this.next = finallyEntry.finallyLoc, ContinueSentinel) : this.complete(record); }, complete: function complete(record, afterLoc) { if ("throw" === record.type) throw record.arg; return "break" === record.type || "continue" === record.type ? this.next = record.arg : "return" === record.type ? (this.rval = this.arg = record.arg, this.method = "return", this.next = "end") : "normal" === record.type && afterLoc && (this.next = afterLoc), ContinueSentinel; }, finish: function finish(finallyLoc) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.finallyLoc === finallyLoc) return this.complete(entry.completion, entry.afterLoc), resetTryEntry(entry), ContinueSentinel; } }, "catch": function _catch(tryLoc) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.tryLoc === tryLoc) { var record = entry.completion; if ("throw" === record.type) { var thrown = record.arg; resetTryEntry(entry); } return thrown; } } throw new Error("illegal catch attempt"); }, delegateYield: function delegateYield(iterable, resultName, nextLoc) { return this.delegate = { iterator: values(iterable), resultName: resultName, nextLoc: nextLoc }, "next" === this.method && (this.arg = undefined), ContinueSentinel; } }, exports; }
	function _createForOfIteratorHelper(o, allowArrayLike) { var it = typeof Symbol !== "undefined" && o[Symbol.iterator] || o["@@iterator"]; if (!it) { if (Array.isArray(o) || (it = _unsupportedIterableToArray(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = it.call(o); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it["return"] != null) it["return"](); } finally { if (didErr) throw err; } } }; }
	function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }
	function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i]; return arr2; }
	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration$1(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec$1(obj, privateMap, value) { _checkPrivateRedeclaration$1(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$1(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _scheme = /*#__PURE__*/new WeakMap();
	var _filterBy = /*#__PURE__*/new WeakSet();
	var TemplatesScheme = /*#__PURE__*/function () {
	  function TemplatesScheme(_scheme2) {
	    var _this = this;
	    babelHelpers.classCallCheck(this, TemplatesScheme);
	    _classPrivateMethodInitSpec(this, _filterBy);
	    _classPrivateFieldInitSpec$1(this, _scheme, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldSet(this, _scheme, []);
	    if (main_core.Type.isArray(_scheme2)) {
	      _scheme2.forEach(function (rawScope) {
	        var scope = new TemplateScope(rawScope);
	        babelHelpers.classPrivateFieldGet(_this, _scheme).push(scope);
	      });
	    }
	  }
	  babelHelpers.createClass(TemplatesScheme, [{
	    key: "getDocumentTypes",
	    value: function getDocumentTypes() {
	      var documentTypes = new Map();
	      var _iterator = _createForOfIteratorHelper(babelHelpers.classPrivateFieldGet(this, _scheme)),
	        _step;
	      try {
	        for (_iterator.s(); !(_step = _iterator.n()).done;) {
	          var scope = _step.value;
	          documentTypes.set(scope.getDocumentType().Type, scope.getDocumentType());
	        }
	      } catch (err) {
	        _iterator.e(err);
	      } finally {
	        _iterator.f();
	      }
	      return Array.from(documentTypes.values());
	    }
	  }, {
	    key: "getTypeCategories",
	    value: function getTypeCategories(documentType) {
	      var documentCategories = new Map();
	      var _iterator2 = _createForOfIteratorHelper(babelHelpers.classPrivateFieldGet(this, _scheme)),
	        _step2;
	      try {
	        for (_iterator2.s(); !(_step2 = _iterator2.n()).done;) {
	          var scope = _step2.value;
	          if (scope.hasCategory() && scope.getDocumentType().Type === documentType.Type) {
	            var category = scope.getDocumentCategory();
	            documentCategories.set(category.Id, category);
	          }
	        }
	      } catch (err) {
	        _iterator2.e(err);
	      } finally {
	        _iterator2.f();
	      }
	      return Array.from(documentCategories.values());
	    }
	  }, {
	    key: "getTypeStatuses",
	    value: function getTypeStatuses(documentType, documentCategory) {
	      var takenStatuses = new Set();
	      if (main_core.Type.isNil(documentCategory)) {
	        documentCategory = {
	          Id: null
	        };
	      }
	      var predicate = function predicate(scope) {
	        var shouldBeTaken = scope.getDocumentType().Type === documentType.Type && (scope.hasCategory() ? scope.getDocumentCategory().Id === documentCategory.Id : true) && !takenStatuses.has(scope.getDocumentStatus().Id);
	        if (shouldBeTaken) {
	          takenStatuses.add(scope.getDocumentStatus().Id);
	        }
	        return shouldBeTaken;
	      };
	      return Array.from(_classPrivateMethodGet(this, _filterBy, _filterBy2).call(this, predicate)).map(function (scope) {
	        return scope.getDocumentStatus();
	      });
	    }
	  }]);
	  return TemplatesScheme;
	}();
	function _filterBy2(predicate) {
	  var generator = /*#__PURE__*/_regeneratorRuntime().mark(function generator(scheme) {
	    var _iterator3, _step3, scope;
	    return _regeneratorRuntime().wrap(function generator$(_context) {
	      while (1) switch (_context.prev = _context.next) {
	        case 0:
	          _iterator3 = _createForOfIteratorHelper(scheme);
	          _context.prev = 1;
	          _iterator3.s();
	        case 3:
	          if ((_step3 = _iterator3.n()).done) {
	            _context.next = 10;
	            break;
	          }
	          scope = _step3.value;
	          if (!predicate(scope)) {
	            _context.next = 8;
	            break;
	          }
	          _context.next = 8;
	          return scope;
	        case 8:
	          _context.next = 3;
	          break;
	        case 10:
	          _context.next = 15;
	          break;
	        case 12:
	          _context.prev = 12;
	          _context.t0 = _context["catch"](1);
	          _iterator3.e(_context.t0);
	        case 15:
	          _context.prev = 15;
	          _iterator3.f();
	          return _context.finish(15);
	        case 18:
	        case "end":
	          return _context.stop();
	      }
	    }, generator, null, [[1, 12, 15, 18]]);
	  });
	  return generator(babelHelpers.classPrivateFieldGet(this, _scheme));
	}

	function _classPrivateFieldInitSpec$2(obj, privateMap, value) { _checkPrivateRedeclaration$2(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$2(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	var _values = /*#__PURE__*/new WeakMap();
	var BaseContext = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(BaseContext, _EventEmitter);
	  function BaseContext(defaultValue) {
	    var _this;
	    babelHelpers.classCallCheck(this, BaseContext);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(BaseContext).call(this));
	    _classPrivateFieldInitSpec$2(babelHelpers.assertThisInitialized(_this), _values, {
	      writable: true,
	      value: void 0
	    });
	    _this.setEventNamespace('BX.Bizproc.Automation.Context');
	    if (main_core.Type.isPlainObject(defaultValue)) {
	      babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _values, defaultValue);
	    }
	    return _this;
	  }
	  babelHelpers.createClass(BaseContext, [{
	    key: "clone",
	    value: function clone() {
	      return new BaseContext(main_core.clone(babelHelpers.classPrivateFieldGet(this, _values)));
	    }
	  }, {
	    key: "getValues",
	    value: function getValues() {
	      return babelHelpers.classPrivateFieldGet(this, _values);
	    }
	  }, {
	    key: "set",
	    value: function set(name, value) {
	      var isValueChanged = this.has(name);
	      babelHelpers.classPrivateFieldGet(this, _values)[name] = value;
	      this.emit(isValueChanged ? 'valueChanged' : 'valueAdded', {
	        name: name,
	        value: value
	      });
	      return this;
	    }
	  }, {
	    key: "get",
	    value: function get(name) {
	      return babelHelpers.classPrivateFieldGet(this, _values)[name];
	    }
	  }, {
	    key: "has",
	    value: function has(name) {
	      return babelHelpers.classPrivateFieldGet(this, _values).hasOwnProperty(name);
	    }
	  }, {
	    key: "subsribeValueChanges",
	    value: function subsribeValueChanges(name, listener) {
	      this.subscribe('valueChanged', function (event) {
	        if (event.data.name === name) {
	          listener(event);
	        }
	      });
	      return this;
	    }
	  }]);
	  return BaseContext;
	}(main_core_events.EventEmitter);

	var Context = /*#__PURE__*/function (_BaseContext) {
	  babelHelpers.inherits(Context, _BaseContext);
	  function Context(props) {
	    babelHelpers.classCallCheck(this, Context);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Context).call(this, props));
	  }
	  babelHelpers.createClass(Context, [{
	    key: "clone",
	    value: function clone() {
	      var _this$userOptions;
	      // TODO - clone Tracker object when the corresponding method appears
	      return new Context(main_core.Runtime.clone(this.getValues())).set('document', this.document.clone()).set('userOptions', (_this$userOptions = this.userOptions) === null || _this$userOptions === void 0 ? void 0 : _this$userOptions.clone());
	    }
	  }, {
	    key: "getAvailableTrigger",
	    value: function getAvailableTrigger(code) {
	      return this.availableTriggers.find(function (trigger) {
	        return trigger['CODE'] === code;
	      });
	    }
	  }, {
	    key: "document",
	    get: function get() {
	      return this.get('document');
	    }
	  }, {
	    key: "signedDocument",
	    get: function get() {
	      var _this$get;
	      return (_this$get = this.get('signedDocument')) !== null && _this$get !== void 0 ? _this$get : '';
	    }
	  }, {
	    key: "ajaxUrl",
	    get: function get() {
	      var _this$get2;
	      return (_this$get2 = this.get('ajaxUrl')) !== null && _this$get2 !== void 0 ? _this$get2 : '';
	    }
	  }, {
	    key: "availableRobots",
	    get: function get() {
	      var availableRobots = this.get('availableRobots');
	      if (main_core.Type.isArray(availableRobots)) {
	        return availableRobots;
	      }
	      return [];
	    }
	  }, {
	    key: "availableTriggers",
	    get: function get() {
	      var availableTriggers = this.get('availableTriggers');
	      if (main_core.Type.isArray(availableTriggers)) {
	        return availableTriggers;
	      }
	      return [];
	    }
	  }, {
	    key: "canManage",
	    get: function get() {
	      var canManage = this.get('canManage');
	      return main_core.Type.isBoolean(canManage) && canManage;
	    }
	  }, {
	    key: "canEdit",
	    get: function get() {
	      var canEdit = this.get('canEdit');
	      return main_core.Type.isBoolean(canEdit) && canEdit;
	    }
	  }, {
	    key: "userOptions",
	    get: function get() {
	      return this.get('userOptions');
	    }
	  }, {
	    key: "tracker",
	    get: function get() {
	      return this.get('tracker');
	    },
	    set: function set(tracker) {
	      this.set('tracker', tracker);
	    }
	  }, {
	    key: "bizprocEditorUrl",
	    get: function get() {
	      return this.get('bizprocEditorUrl');
	    }
	  }, {
	    key: "constantsEditorUrl",
	    get: function get() {
	      return this.get('constantsEditorUrl');
	    }
	  }, {
	    key: "parametersEditorUrl",
	    get: function get() {
	      return this.get('parametersEditorUrl');
	    }
	  }, {
	    key: "automationGlobals",
	    get: function get() {
	      return this.get('automationGlobals');
	    }
	  }]);
	  return Context;
	}(BaseContext);

	function _classPrivateFieldInitSpec$3(obj, privateMap, value) { _checkPrivateRedeclaration$3(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$3(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classStaticPrivateFieldSpecGet(receiver, classConstructor, descriptor) { _classCheckPrivateStaticAccess(receiver, classConstructor); _classCheckPrivateStaticFieldDescriptor(descriptor, "get"); return _classApplyDescriptorGet(receiver, descriptor); }
	function _classCheckPrivateStaticFieldDescriptor(descriptor, action) { if (descriptor === undefined) { throw new TypeError("attempted to " + action + " private static field before its declaration"); } }
	function _classCheckPrivateStaticAccess(receiver, classConstructor) { if (receiver !== classConstructor) { throw new TypeError("Private static access of wrong provenance"); } }
	function _classApplyDescriptorGet(receiver, descriptor) { if (descriptor.get) { return descriptor.get.call(receiver); } return descriptor.value; }
	var _mode = /*#__PURE__*/new WeakMap();
	var _properties = /*#__PURE__*/new WeakMap();
	var ViewMode = /*#__PURE__*/function () {
	  function ViewMode(mode) {
	    babelHelpers.classCallCheck(this, ViewMode);
	    _classPrivateFieldInitSpec$3(this, _mode, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$3(this, _properties, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldSet(this, _mode, mode);
	    babelHelpers.classPrivateFieldSet(this, _properties, {});
	  }
	  babelHelpers.createClass(ViewMode, [{
	    key: "isNone",
	    value: function isNone() {
	      return babelHelpers.classPrivateFieldGet(this, _mode) === _classStaticPrivateFieldSpecGet(ViewMode, ViewMode, _none);
	    }
	  }, {
	    key: "isView",
	    value: function isView() {
	      return babelHelpers.classPrivateFieldGet(this, _mode) === _classStaticPrivateFieldSpecGet(ViewMode, ViewMode, _view);
	    }
	  }, {
	    key: "isEdit",
	    value: function isEdit() {
	      return babelHelpers.classPrivateFieldGet(this, _mode) === _classStaticPrivateFieldSpecGet(ViewMode, ViewMode, _edit);
	    }
	  }, {
	    key: "isManage",
	    value: function isManage() {
	      return babelHelpers.classPrivateFieldGet(this, _mode) === _classStaticPrivateFieldSpecGet(ViewMode, ViewMode, _manage);
	    }
	  }, {
	    key: "setProperty",
	    value: function setProperty(name, value) {
	      babelHelpers.classPrivateFieldGet(this, _properties)[name] = value;
	      return this;
	    }
	  }, {
	    key: "getProperty",
	    value: function getProperty(name) {
	      var defaultValue = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
	      if (babelHelpers.classPrivateFieldGet(this, _properties).hasOwnProperty(name)) {
	        return babelHelpers.classPrivateFieldGet(this, _properties)[name];
	      }
	      return defaultValue;
	    }
	  }, {
	    key: "intoRaw",
	    value: function intoRaw() {
	      return babelHelpers.classPrivateFieldGet(this, _mode);
	    }
	  }], [{
	    key: "none",
	    value: function none() {
	      return new ViewMode(_classStaticPrivateFieldSpecGet(ViewMode, ViewMode, _none));
	    }
	  }, {
	    key: "view",
	    value: function view() {
	      return new ViewMode(_classStaticPrivateFieldSpecGet(ViewMode, ViewMode, _view));
	    }
	  }, {
	    key: "edit",
	    value: function edit() {
	      return new ViewMode(_classStaticPrivateFieldSpecGet(ViewMode, ViewMode, _edit));
	    }
	  }, {
	    key: "manage",
	    value: function manage() {
	      return new ViewMode(_classStaticPrivateFieldSpecGet(ViewMode, ViewMode, _manage));
	    }
	  }, {
	    key: "fromRaw",
	    value: function fromRaw(mode) {
	      if (ViewMode.getAll().includes(mode)) {
	        return new ViewMode(mode);
	      }
	      return ViewMode.none();
	    }
	  }, {
	    key: "getAll",
	    value: function getAll() {
	      return [_classStaticPrivateFieldSpecGet(this, ViewMode, _none), _classStaticPrivateFieldSpecGet(this, ViewMode, _view), _classStaticPrivateFieldSpecGet(this, ViewMode, _edit), _classStaticPrivateFieldSpecGet(this, ViewMode, _manage)];
	    }
	  }]);
	  return ViewMode;
	}();
	var _none = {
	  writable: true,
	  value: 0
	};
	var _view = {
	  writable: true,
	  value: 1
	};
	var _edit = {
	  writable: true,
	  value: 2
	};
	var _manage = {
	  writable: true,
	  value: 3
	};

	function _classPrivateFieldInitSpec$4(obj, privateMap, value) { _checkPrivateRedeclaration$4(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$4(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	var _data = /*#__PURE__*/new WeakMap();
	var _deleted = /*#__PURE__*/new WeakMap();
	var _viewMode = /*#__PURE__*/new WeakMap();
	var _condition = /*#__PURE__*/new WeakMap();
	var _node = /*#__PURE__*/new WeakMap();
	var _draggableItem = /*#__PURE__*/new WeakMap();
	var _droppableItem = /*#__PURE__*/new WeakMap();
	var _droppableColumn = /*#__PURE__*/new WeakMap();
	var _stub = /*#__PURE__*/new WeakMap();
	var Trigger = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(Trigger, _EventEmitter);
	  function Trigger() {
	    var _this;
	    babelHelpers.classCallCheck(this, Trigger);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Trigger).call(this));
	    _classPrivateFieldInitSpec$4(babelHelpers.assertThisInitialized(_this), _data, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$4(babelHelpers.assertThisInitialized(_this), _deleted, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$4(babelHelpers.assertThisInitialized(_this), _viewMode, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$4(babelHelpers.assertThisInitialized(_this), _condition, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$4(babelHelpers.assertThisInitialized(_this), _node, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$4(babelHelpers.assertThisInitialized(_this), _draggableItem, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$4(babelHelpers.assertThisInitialized(_this), _droppableItem, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$4(babelHelpers.assertThisInitialized(_this), _droppableColumn, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$4(babelHelpers.assertThisInitialized(_this), _stub, {
	      writable: true,
	      value: void 0
	    });
	    _this.setEventNamespace('BX.Bizproc.Automation');
	    _this.draft = false;
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _data, {});
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _deleted, false);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _viewMode, ViewMode.none());
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _condition, new bizproc_automation.ConditionGroup());
	    return _this;
	  }
	  babelHelpers.createClass(Trigger, [{
	    key: "init",
	    value: function init(data, viewMode) {
	      babelHelpers.classPrivateFieldSet(this, _data, main_core.clone(data));
	      if (!main_core.Type.isPlainObject(babelHelpers.classPrivateFieldGet(this, _data)['APPLY_RULES'])) {
	        babelHelpers.classPrivateFieldGet(this, _data)['APPLY_RULES'] = {};
	      }
	      if (babelHelpers.classPrivateFieldGet(this, _data)['APPLY_RULES'].Condition) {
	        babelHelpers.classPrivateFieldSet(this, _condition, new bizproc_automation.ConditionGroup(babelHelpers.classPrivateFieldGet(this, _data)['APPLY_RULES'].Condition));
	      } else {
	        babelHelpers.classPrivateFieldSet(this, _condition, new bizproc_automation.ConditionGroup());
	      }
	      babelHelpers.classPrivateFieldSet(this, _viewMode, main_core.Type.isNil(viewMode) ? ViewMode.edit() : viewMode);
	      babelHelpers.classPrivateFieldSet(this, _node, this.createNode());
	    }
	  }, {
	    key: "reInit",
	    value: function reInit(data, viewMode) {
	      var node = babelHelpers.classPrivateFieldGet(this, _node);
	      babelHelpers.classPrivateFieldSet(this, _node, this.createNode());
	      if (node.parentNode) {
	        node.parentNode.replaceChild(babelHelpers.classPrivateFieldGet(this, _node), node);
	      }
	    }
	  }, {
	    key: "canEdit",
	    value: function canEdit() {
	      return bizproc_automation.getGlobalContext().canEdit;
	    }
	  }, {
	    key: "getId",
	    value: function getId() {
	      return babelHelpers.classPrivateFieldGet(this, _data)['ID'] || 0;
	    }
	  }, {
	    key: "getStatusId",
	    value: function getStatusId() {
	      return String(babelHelpers.classPrivateFieldGet(this, _data)['DOCUMENT_STATUS'] || '');
	    }
	  }, {
	    key: "getStatus",
	    value: function getStatus() {
	      var _this2 = this;
	      return bizproc_automation.getGlobalContext().document.statusList.find(function (status) {
	        return String(status.STATUS_ID) === _this2.getStatusId();
	      });
	    }
	  }, {
	    key: "getCode",
	    value: function getCode() {
	      var _babelHelpers$classPr;
	      return (_babelHelpers$classPr = babelHelpers.classPrivateFieldGet(this, _data)['CODE']) !== null && _babelHelpers$classPr !== void 0 ? _babelHelpers$classPr : '';
	    }
	  }, {
	    key: "getName",
	    value: function getName() {
	      var triggerName = babelHelpers.classPrivateFieldGet(this, _data)['NAME'];
	      if (!triggerName) {
	        var _trigger$NAME;
	        var code = this.getCode();
	        var trigger = bizproc_automation.getGlobalContext().availableTriggers.find(function (trigger) {
	          return code === trigger['CODE'];
	        });
	        triggerName = (_trigger$NAME = trigger === null || trigger === void 0 ? void 0 : trigger.NAME) !== null && _trigger$NAME !== void 0 ? _trigger$NAME : code;
	      }
	      return triggerName;
	    }
	  }, {
	    key: "setName",
	    value: function setName(name) {
	      if (main_core.Type.isString(name)) {
	        babelHelpers.classPrivateFieldGet(this, _data)['NAME'] = name;
	      }
	      return this;
	    }
	  }, {
	    key: "getApplyRules",
	    value: function getApplyRules() {
	      return babelHelpers.classPrivateFieldGet(this, _data)['APPLY_RULES'];
	    }
	  }, {
	    key: "setApplyRules",
	    value: function setApplyRules(rules) {
	      babelHelpers.classPrivateFieldGet(this, _data)['APPLY_RULES'] = rules;
	      return this;
	    }
	  }, {
	    key: "getLogStatus",
	    value: function getLogStatus() {
	      var log = bizproc_automation.getGlobalContext().tracker.getTriggerLog(this.getId());
	      return log ? log.status : null;
	    }
	  }, {
	    key: "getCondition",
	    value: function getCondition() {
	      return babelHelpers.classPrivateFieldGet(this, _condition);
	    }
	  }, {
	    key: "setCondition",
	    value: function setCondition(condition) {
	      babelHelpers.classPrivateFieldSet(this, _condition, condition);
	      return this;
	    }
	  }, {
	    key: "isBackwardsAllowed",
	    value: function isBackwardsAllowed() {
	      return babelHelpers.classPrivateFieldGet(this, _data)['APPLY_RULES']['ALLOW_BACKWARDS'] === 'Y';
	    }
	  }, {
	    key: "setAllowBackwards",
	    value: function setAllowBackwards(flag) {
	      babelHelpers.classPrivateFieldGet(this, _data)['APPLY_RULES']['ALLOW_BACKWARDS'] = flag ? 'Y' : 'N';
	      return this;
	    }
	  }, {
	    key: "getExecuteBy",
	    value: function getExecuteBy() {
	      return babelHelpers.classPrivateFieldGet(this, _data)['APPLY_RULES']['ExecuteBy'] || '';
	    }
	  }, {
	    key: "setExecuteBy",
	    value: function setExecuteBy(userId) {
	      babelHelpers.classPrivateFieldGet(this, _data)['APPLY_RULES']['ExecuteBy'] = userId;
	      return this;
	    }
	  }, {
	    key: "createNode",
	    value: function createNode() {
	      var wrapperClass = 'bizproc-automation-trigger-item-wrapper';
	      if (babelHelpers.classPrivateFieldGet(this, _viewMode).isEdit() && this.canEdit()) {
	        wrapperClass += ' bizproc-automation-trigger-item-wrapper-draggable';
	      }
	      var settingsBtn = null;
	      var copyBtn = null;
	      if (babelHelpers.classPrivateFieldGet(this, _viewMode).isEdit()) {
	        settingsBtn = main_core.Dom.create("div", {
	          attrs: {
	            className: "bizproc-automation-trigger-item-wrapper-edit"
	          },
	          text: main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_EDIT')
	        });
	        copyBtn = main_core.Dom.create('div', {
	          attrs: {
	            className: 'bizproc-automation-trigger-btn-copy'
	          },
	          text: main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_COPY') || 'copy'
	        });
	        main_core.Event.bind(copyBtn, 'click', this.onCopyButtonClick.bind(this, copyBtn));
	      }
	      if (this.getLogStatus() === bizproc_automation.TrackingStatus.COMPLETED) {
	        wrapperClass += ' bizproc-automation-trigger-item-wrapper-complete';
	      } else if (bizproc_automation.getGlobalContext().document.getPreviousStatusIdList().includes(this.getStatusId())) {
	        wrapperClass += ' bizproc-automation-trigger-item-wrapper-complete-light';
	      }
	      var triggerName = this.getName();
	      var containerClass = 'bizproc-automation-trigger-item';
	      if (this.getLogStatus() === bizproc_automation.TrackingStatus.COMPLETED) {
	        containerClass += ' --complete';
	      } else if (this.draft) {
	        containerClass += ' --draft';
	      }
	      var div = main_core.Dom.create('DIV', {
	        attrs: {
	          'data-role': 'trigger-container',
	          'className': containerClass,
	          'data-type': 'item-trigger'
	        },
	        children: [main_core.Dom.create("div", {
	          attrs: {
	            className: wrapperClass
	          },
	          children: [main_core.Dom.create("div", {
	            attrs: {
	              className: "bizproc-automation-trigger-item-wrapper-text",
	              title: triggerName
	            },
	            text: triggerName
	          })]
	        }), copyBtn, settingsBtn]
	      });
	      if (!babelHelpers.classPrivateFieldGet(this, _viewMode).isEdit()) {
	        return div;
	      }
	      if (this.canEdit()) {
	        this.registerItem(div);
	      }
	      var deleteBtn = main_core.Dom.create('SPAN', {
	        attrs: {
	          'data-role': 'btn-delete-trigger',
	          'className': 'bizproc-automation-trigger-btn-delete'
	        }
	      });
	      main_core.Event.bind(deleteBtn, 'click', this.onDeleteButtonClick.bind(this, deleteBtn));
	      div.appendChild(deleteBtn);
	      if (babelHelpers.classPrivateFieldGet(this, _viewMode).isEdit()) {
	        main_core.Event.bind(div, 'click', this.onSettingsButtonClick.bind(this, div));
	      }
	      return div;
	    }
	  }, {
	    key: "onSettingsButtonClick",
	    value: function onSettingsButtonClick(button) {
	      if (!this.canEdit()) {
	        bizproc_automation.HelpHint.showNoPermissionsHint(button);
	      } else if (!babelHelpers.classPrivateFieldGet(this, _viewMode).isManage()) {
	        this.emit('Trigger:onSettingsOpen', {
	          trigger: this
	        });
	      }
	    }
	  }, {
	    key: "onCopyButtonClick",
	    value: function onCopyButtonClick(button, event) {
	      event.stopPropagation();
	      if (!this.canEdit()) {
	        bizproc_automation.HelpHint.showNoPermissionsHint(button);
	      } else if (!babelHelpers.classPrivateFieldGet(this, _viewMode).isManage()) {
	        var trigger = new Trigger();
	        var initData = this.serialize();
	        delete initData['ID'];
	        var clearRules = this.getSettingProperties().filter(function (property) {
	          return property.Copyable === false;
	        }).map(function (property) {
	          return property.Id;
	        });
	        clearRules.forEach(function (key) {
	          return delete initData['APPLY_RULES'][key];
	        });
	        trigger.init(initData, babelHelpers.classPrivateFieldGet(this, _viewMode));
	        this.emit('Trigger:copied', {
	          trigger: trigger
	        });
	      }
	    }
	  }, {
	    key: "onSearch",
	    value: function onSearch(event) {
	      if (!babelHelpers.classPrivateFieldGet(this, _node)) {
	        return;
	      }
	      var query = event.getData().queryString;
	      var match = !query || this.getName().toLowerCase().indexOf(query) >= 0;
	      main_core.Dom[match ? 'removeClass' : 'addClass'](babelHelpers.classPrivateFieldGet(this, _node), '--search-mismatch');
	    }
	  }, {
	    key: "registerItem",
	    value: function registerItem(object) {
	      if (main_core.Type.isNil(object["__bxddid"])) {
	        object.onbxdragstart = BX.proxy(this.dragStart, this);
	        object.onbxdrag = BX.proxy(this.dragMove, this);
	        object.onbxdragstop = BX.proxy(this.dragStop, this);
	        object.onbxdraghover = BX.proxy(this.dragOver, this);
	        jsDD.registerObject(object);
	        jsDD.registerDest(object, 1);
	      }
	    }
	  }, {
	    key: "unregisterItem",
	    value: function unregisterItem(object) {
	      object.onbxdragstart = undefined;
	      object.onbxdrag = undefined;
	      object.onbxdragstop = undefined;
	      object.onbxdraghover = undefined;
	      jsDD.unregisterObject(object);
	      jsDD.unregisterDest(object);
	    }
	  }, {
	    key: "dragStart",
	    value: function dragStart() {
	      babelHelpers.classPrivateFieldSet(this, _draggableItem, BX.proxy_context);
	      if (!babelHelpers.classPrivateFieldGet(this, _draggableItem)) {
	        jsDD.stopCurrentDrag();
	        return;
	      }
	      if (!babelHelpers.classPrivateFieldGet(this, _stub)) {
	        var itemWidth = babelHelpers.classPrivateFieldGet(this, _draggableItem).offsetWidth;
	        babelHelpers.classPrivateFieldSet(this, _stub, babelHelpers.classPrivateFieldGet(this, _draggableItem).cloneNode(true));
	        babelHelpers.classPrivateFieldGet(this, _stub).style.position = "absolute";
	        babelHelpers.classPrivateFieldGet(this, _stub).classList.add("bizproc-automation-trigger-item-drag");
	        babelHelpers.classPrivateFieldGet(this, _stub).style.width = itemWidth + "px";
	        document.body.appendChild(babelHelpers.classPrivateFieldGet(this, _stub));
	      }
	    }
	  }, {
	    key: "dragMove",
	    value: function dragMove(x, y) {
	      babelHelpers.classPrivateFieldGet(this, _stub).style.left = x + "px";
	      babelHelpers.classPrivateFieldGet(this, _stub).style.top = y + "px";
	    }
	  }, {
	    key: "dragOver",
	    value: function dragOver(destination, x, y) {
	      if (babelHelpers.classPrivateFieldGet(this, _droppableItem)) {
	        babelHelpers.classPrivateFieldGet(this, _droppableItem).classList.remove("bizproc-automation-trigger-item-pre");
	      }
	      if (babelHelpers.classPrivateFieldGet(this, _droppableColumn)) {
	        babelHelpers.classPrivateFieldGet(this, _droppableColumn).classList.remove("bizproc-automation-trigger-list-pre");
	      }
	      var type = destination.getAttribute("data-type");
	      if (type === "item-trigger") {
	        babelHelpers.classPrivateFieldSet(this, _droppableItem, destination);
	        babelHelpers.classPrivateFieldSet(this, _droppableColumn, null);
	      }
	      if (type === "column-trigger") {
	        babelHelpers.classPrivateFieldSet(this, _droppableColumn, destination.querySelector('[data-role="trigger-list"]'));
	        babelHelpers.classPrivateFieldSet(this, _droppableItem, null);
	      }
	      if (babelHelpers.classPrivateFieldGet(this, _droppableItem)) {
	        babelHelpers.classPrivateFieldGet(this, _droppableItem).classList.add("bizproc-automation-trigger-item-pre");
	      }
	      if (babelHelpers.classPrivateFieldGet(this, _droppableColumn)) {
	        babelHelpers.classPrivateFieldGet(this, _droppableColumn).classList.add("bizproc-automation-trigger-list-pre");
	      }
	    }
	  }, {
	    key: "dragStop",
	    value: function dragStop(x, y, event) {
	      var _this3 = this;
	      event = event || window.event;
	      var trigger = null;
	      var isCopy = event && (event.ctrlKey || event.metaKey);
	      var copyTrigger = function copyTrigger(parent, statusId) {
	        var trigger = new Trigger();
	        var initData = parent.serialize();
	        delete initData['ID'];
	        var clearRules = _this3.getSettingProperties().filter(function (property) {
	          return property.Copyable === false;
	        }).map(function (property) {
	          return property.Id;
	        });
	        clearRules.forEach(function (key) {
	          return delete initData['APPLY_RULES'][key];
	        });
	        initData['DOCUMENT_STATUS'] = statusId;
	        trigger.init(initData, babelHelpers.classPrivateFieldGet(parent, _viewMode));
	        return trigger;
	      };
	      if (babelHelpers.classPrivateFieldGet(this, _draggableItem)) {
	        if (babelHelpers.classPrivateFieldGet(this, _droppableItem)) {
	          babelHelpers.classPrivateFieldGet(this, _droppableItem).classList.remove("bizproc-automation-trigger-item-pre");
	          var thisColumn = babelHelpers.classPrivateFieldGet(this, _droppableItem).parentNode;
	          if (!isCopy) {
	            thisColumn.insertBefore(babelHelpers.classPrivateFieldGet(this, _draggableItem), babelHelpers.classPrivateFieldGet(this, _droppableItem));
	            this.moveTo(thisColumn.getAttribute('data-status-id'));
	          } else {
	            trigger = copyTrigger(this, thisColumn.getAttribute('data-status-id'));
	            thisColumn.insertBefore(babelHelpers.classPrivateFieldGet(trigger, _node), babelHelpers.classPrivateFieldGet(this, _droppableItem));
	          }
	        } else if (babelHelpers.classPrivateFieldGet(this, _droppableColumn)) {
	          babelHelpers.classPrivateFieldGet(this, _droppableColumn).classList.remove("bizproc-automation-trigger-list-pre");
	          if (!isCopy) {
	            babelHelpers.classPrivateFieldGet(this, _droppableColumn).appendChild(babelHelpers.classPrivateFieldGet(this, _draggableItem));
	            this.moveTo(babelHelpers.classPrivateFieldGet(this, _droppableColumn).getAttribute('data-status-id'));
	          } else {
	            trigger = copyTrigger(this, babelHelpers.classPrivateFieldGet(this, _droppableColumn).getAttribute('data-status-id'));
	            babelHelpers.classPrivateFieldGet(this, _droppableColumn).appendChild(babelHelpers.classPrivateFieldGet(trigger, _node));
	          }
	        }
	        if (trigger) {
	          this.emit('Trigger:copied', {
	            trigger: trigger,
	            skipInsert: true
	          });
	        }
	      }
	      babelHelpers.classPrivateFieldGet(this, _stub).parentNode.removeChild(babelHelpers.classPrivateFieldGet(this, _stub));
	      babelHelpers.classPrivateFieldSet(this, _stub, null);
	      babelHelpers.classPrivateFieldSet(this, _draggableItem, null);
	      babelHelpers.classPrivateFieldSet(this, _droppableItem, null);
	    }
	  }, {
	    key: "onDeleteButtonClick",
	    value: function onDeleteButtonClick(button, event) {
	      event.stopPropagation();
	      if (!this.canEdit()) {
	        bizproc_automation.HelpHint.showNoPermissionsHint(button);
	      } else if (!babelHelpers.classPrivateFieldGet(this, _viewMode).isManage()) {
	        main_core.Dom.remove(button.parentNode);
	        this.emit('Trigger:deleted', {
	          trigger: this
	        });
	      }
	    }
	  }, {
	    key: "updateData",
	    value: function updateData(data) {
	      if (main_core.Type.isPlainObject(data)) {
	        babelHelpers.classPrivateFieldSet(this, _data, data);
	      } else {
	        throw 'Invalid data';
	      }
	    }
	  }, {
	    key: "markDeleted",
	    value: function markDeleted() {
	      babelHelpers.classPrivateFieldSet(this, _deleted, true);
	      return this;
	    }
	  }, {
	    key: "serialize",
	    value: function serialize() {
	      var data = main_core.clone(babelHelpers.classPrivateFieldGet(this, _data));
	      if (babelHelpers.classPrivateFieldGet(this, _deleted)) {
	        data['DELETED'] = 'Y';
	      }
	      if (!main_core.Type.isPlainObject(data.APPLY_RULES)) {
	        data.APPLY_RULES = {};
	      }
	      if (!babelHelpers.classPrivateFieldGet(this, _condition).items.length) {
	        delete data.APPLY_RULES.Condition;
	      } else {
	        data.APPLY_RULES.Condition = babelHelpers.classPrivateFieldGet(this, _condition).serialize();
	      }
	      return data;
	    }
	  }, {
	    key: "moveTo",
	    value: function moveTo(statusId) {
	      babelHelpers.classPrivateFieldGet(this, _data)['DOCUMENT_STATUS'] = statusId;
	      this.emit('Trigger:modified', {
	        trigger: this
	      });
	    }
	  }, {
	    key: "getReturnProperties",
	    value: function getReturnProperties() {
	      var _this4 = this;
	      var triggerData = bizproc_automation.getGlobalContext().availableTriggers.find(function (trigger) {
	        return trigger['CODE'] === _this4.getCode();
	      });
	      return triggerData && main_core.Type.isArray(triggerData.RETURN) ? triggerData.RETURN : [];
	    }
	  }, {
	    key: "getSettingProperties",
	    value: function getSettingProperties() {
	      var _this5 = this;
	      var triggerData = bizproc_automation.getGlobalContext().availableTriggers.find(function (trigger) {
	        return trigger['CODE'] === _this5.getCode();
	      });
	      if (triggerData.SETTINGS && triggerData.SETTINGS.Properties) {
	        return triggerData.SETTINGS.Properties;
	      }
	      return [];
	    }
	  }, {
	    key: "node",
	    get: function get() {
	      return babelHelpers.classPrivateFieldGet(this, _node);
	    }
	  }, {
	    key: "deleted",
	    get: function get() {
	      return babelHelpers.classPrivateFieldGet(this, _deleted);
	    }
	  }, {
	    key: "documentStatus",
	    get: function get() {
	      var _babelHelpers$classPr2;
	      return (_babelHelpers$classPr2 = babelHelpers.classPrivateFieldGet(this, _data)['DOCUMENT_STATUS']) !== null && _babelHelpers$classPr2 !== void 0 ? _babelHelpers$classPr2 : '';
	    }
	  }]);
	  return Trigger;
	}(main_core_events.EventEmitter);

	function _createForOfIteratorHelper$1(o, allowArrayLike) { var it = typeof Symbol !== "undefined" && o[Symbol.iterator] || o["@@iterator"]; if (!it) { if (Array.isArray(o) || (it = _unsupportedIterableToArray$1(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = it.call(o); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it["return"] != null) it["return"](); } finally { if (didErr) throw err; } } }; }
	function _unsupportedIterableToArray$1(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray$1(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray$1(o, minLen); }
	function _arrayLikeToArray$1(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i]; return arr2; }
	function _classStaticPrivateFieldSpecSet(receiver, classConstructor, descriptor, value) { _classCheckPrivateStaticAccess$1(receiver, classConstructor); _classCheckPrivateStaticFieldDescriptor$1(descriptor, "set"); _classApplyDescriptorSet(receiver, descriptor, value); return value; }
	function _classApplyDescriptorSet(receiver, descriptor, value) { if (descriptor.set) { descriptor.set.call(receiver, value); } else { if (!descriptor.writable) { throw new TypeError("attempted to set read only private field"); } descriptor.value = value; } }
	function _classStaticPrivateFieldSpecGet$1(receiver, classConstructor, descriptor) { _classCheckPrivateStaticAccess$1(receiver, classConstructor); _classCheckPrivateStaticFieldDescriptor$1(descriptor, "get"); return _classApplyDescriptorGet$1(receiver, descriptor); }
	function _classCheckPrivateStaticFieldDescriptor$1(descriptor, action) { if (descriptor === undefined) { throw new TypeError("attempted to " + action + " private static field before its declaration"); } }
	function _classCheckPrivateStaticAccess$1(receiver, classConstructor) { if (receiver !== classConstructor) { throw new TypeError("Private static access of wrong provenance"); } }
	function _classApplyDescriptorGet$1(receiver, descriptor) { if (descriptor.get) { return descriptor.get.call(receiver); } return descriptor.value; }
	var Helper = /*#__PURE__*/function () {
	  function Helper() {
	    babelHelpers.classCallCheck(this, Helper);
	  }
	  babelHelpers.createClass(Helper, null, [{
	    key: "generateUniqueId",
	    value: function generateUniqueId() {
	      var _Helper$idIncrement;
	      _classStaticPrivateFieldSpecSet(Helper, Helper, _idIncrement, (_Helper$idIncrement = _classStaticPrivateFieldSpecGet$1(Helper, Helper, _idIncrement), ++_Helper$idIncrement));
	      return 'bizproc-automation-cmp-' + _classStaticPrivateFieldSpecGet$1(Helper, Helper, _idIncrement);
	    }
	  }, {
	    key: "toJsonString",
	    value: function toJsonString(data) {
	      return JSON.stringify(data, function (i, v) {
	        if (typeof v == 'boolean') {
	          return v ? '1' : '0';
	        }
	        return v;
	      });
	    }
	  }, {
	    key: "getResponsibleUserExpression",
	    value: function getResponsibleUserExpression(fields) {
	      if (main_core.Type.isArray(fields)) {
	        var _iterator = _createForOfIteratorHelper$1(fields),
	          _step;
	        try {
	          for (_iterator.s(); !(_step = _iterator.n()).done;) {
	            var field = _step.value;
	            if (field['Id'] === 'ASSIGNED_BY_ID' || field['Id'] === 'RESPONSIBLE_ID') {
	              return '{{' + field['Name'] + '}}';
	            }
	          }
	        } catch (err) {
	          _iterator.e(err);
	        } finally {
	          _iterator.f();
	        }
	      }
	      return null;
	    }
	  }]);
	  return Helper;
	}();
	var _idIncrement = {
	  writable: true,
	  value: 0
	};

	function _classStaticPrivateFieldSpecSet$1(receiver, classConstructor, descriptor, value) { _classCheckPrivateStaticAccess$2(receiver, classConstructor); _classCheckPrivateStaticFieldDescriptor$2(descriptor, "set"); _classApplyDescriptorSet$1(receiver, descriptor, value); return value; }
	function _classApplyDescriptorSet$1(receiver, descriptor, value) { if (descriptor.set) { descriptor.set.call(receiver, value); } else { if (!descriptor.writable) { throw new TypeError("attempted to set read only private field"); } descriptor.value = value; } }
	function _classStaticPrivateFieldSpecGet$2(receiver, classConstructor, descriptor) { _classCheckPrivateStaticAccess$2(receiver, classConstructor); _classCheckPrivateStaticFieldDescriptor$2(descriptor, "get"); return _classApplyDescriptorGet$2(receiver, descriptor); }
	function _classCheckPrivateStaticFieldDescriptor$2(descriptor, action) { if (descriptor === undefined) { throw new TypeError("attempted to " + action + " private static field before its declaration"); } }
	function _classCheckPrivateStaticAccess$2(receiver, classConstructor) { if (receiver !== classConstructor) { throw new TypeError("Private static access of wrong provenance"); } }
	function _classApplyDescriptorGet$2(receiver, descriptor) { if (descriptor.get) { return descriptor.get.call(receiver); } return descriptor.value; }
	var Designer = /*#__PURE__*/function () {
	  function Designer() {
	    babelHelpers.classCallCheck(this, Designer);
	  }
	  babelHelpers.createClass(Designer, [{
	    key: "setRobotSettingsDialog",
	    value: function setRobotSettingsDialog(dialog) {
	      this.robotSettingsDialog = dialog;
	      this.robot = dialog ? dialog.robot : null;
	    }
	  }, {
	    key: "getRobotSettingsDialog",
	    value: function getRobotSettingsDialog() {
	      return this.robotSettingsDialog;
	    }
	  }, {
	    key: "setTriggerSettingsDialog",
	    value: function setTriggerSettingsDialog(dialog) {
	      this.triggerSettingsDialog = dialog;
	    }
	  }, {
	    key: "getTriggerSettingsDialog",
	    value: function getTriggerSettingsDialog() {
	      return this.triggerSettingsDialog;
	    }
	  }], [{
	    key: "getInstance",
	    value: function getInstance() {
	      if (!_classStaticPrivateFieldSpecGet$2(Designer, Designer, _instance)) {
	        _classStaticPrivateFieldSpecSet$1(Designer, Designer, _instance, new Designer());
	      }
	      return _classStaticPrivateFieldSpecGet$2(Designer, Designer, _instance);
	    }
	  }]);
	  return Designer;
	}();
	var _instance = {
	  writable: true,
	  value: void 0
	};

	function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	function _classPrivateMethodInitSpec$1(obj, privateSet) { _checkPrivateRedeclaration$5(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec$5(obj, privateMap, value) { _checkPrivateRedeclaration$5(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$5(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet$1(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _triggersContainerNode = /*#__PURE__*/new WeakMap();
	var _viewMode$1 = /*#__PURE__*/new WeakMap();
	var _triggers = /*#__PURE__*/new WeakMap();
	var _triggersData = /*#__PURE__*/new WeakMap();
	var _columnNodes = /*#__PURE__*/new WeakMap();
	var _listNodes = /*#__PURE__*/new WeakMap();
	var _modified = /*#__PURE__*/new WeakMap();
	var _renderTriggerProperties = /*#__PURE__*/new WeakSet();
	var _prepareRobotSelectProperty = /*#__PURE__*/new WeakSet();
	var _setTriggerProperties = /*#__PURE__*/new WeakSet();
	var _renderConditionGroupSelector = /*#__PURE__*/new WeakSet();
	var _setConditionGroupValue = /*#__PURE__*/new WeakSet();
	var _renderWebhookCodeProperty = /*#__PURE__*/new WeakSet();
	var _renderFieldSelectorProperty = /*#__PURE__*/new WeakSet();
	var TriggerManager = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(TriggerManager, _EventEmitter);
	  function TriggerManager(triggersContainerNode) {
	    var _this;
	    babelHelpers.classCallCheck(this, TriggerManager);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(TriggerManager).call(this));
	    _classPrivateMethodInitSpec$1(babelHelpers.assertThisInitialized(_this), _renderFieldSelectorProperty);
	    _classPrivateMethodInitSpec$1(babelHelpers.assertThisInitialized(_this), _renderWebhookCodeProperty);
	    _classPrivateMethodInitSpec$1(babelHelpers.assertThisInitialized(_this), _setConditionGroupValue);
	    _classPrivateMethodInitSpec$1(babelHelpers.assertThisInitialized(_this), _renderConditionGroupSelector);
	    _classPrivateMethodInitSpec$1(babelHelpers.assertThisInitialized(_this), _setTriggerProperties);
	    _classPrivateMethodInitSpec$1(babelHelpers.assertThisInitialized(_this), _prepareRobotSelectProperty);
	    _classPrivateMethodInitSpec$1(babelHelpers.assertThisInitialized(_this), _renderTriggerProperties);
	    _classPrivateFieldInitSpec$5(babelHelpers.assertThisInitialized(_this), _triggersContainerNode, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$5(babelHelpers.assertThisInitialized(_this), _viewMode$1, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$5(babelHelpers.assertThisInitialized(_this), _triggers, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$5(babelHelpers.assertThisInitialized(_this), _triggersData, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$5(babelHelpers.assertThisInitialized(_this), _columnNodes, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$5(babelHelpers.assertThisInitialized(_this), _listNodes, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$5(babelHelpers.assertThisInitialized(_this), _modified, {
	      writable: true,
	      value: void 0
	    });
	    _this.setEventNamespace('BX.Bizproc.Automation');
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _triggersContainerNode, triggersContainerNode);
	    return _this;
	  }
	  babelHelpers.createClass(TriggerManager, [{
	    key: "init",
	    value: function init(data, viewMode) {
	      if (!main_core.Type.isPlainObject(data)) {
	        data = {};
	      }
	      babelHelpers.classPrivateFieldSet(this, _viewMode$1, viewMode.isNone() ? ViewMode.edit() : viewMode);
	      babelHelpers.classPrivateFieldSet(this, _triggersData, main_core.Type.isArray(data.TRIGGERS) ? data.TRIGGERS : []);
	      babelHelpers.classPrivateFieldSet(this, _columnNodes, document.querySelectorAll('[data-type="column-trigger"]'));
	      babelHelpers.classPrivateFieldSet(this, _listNodes, babelHelpers.classPrivateFieldGet(this, _triggersContainerNode).querySelectorAll('[data-role="trigger-list"]'));
	      babelHelpers.classPrivateFieldSet(this, _modified, false);
	      this.initTriggers();
	      this.markModified(false);

	      //register DD
	      babelHelpers.classPrivateFieldGet(this, _columnNodes).forEach(function (columnNode) {
	        return jsDD.registerDest(columnNode, 10);
	      });
	      top.BX.addCustomEvent(top, 'Rest:AppLayout:ApplicationInstall', this.onRestAppInstall.bind(this));
	    }
	  }, {
	    key: "reInit",
	    value: function reInit(data, viewMode) {
	      if (!main_core.Type.isPlainObject(data)) {
	        data = {};
	      }
	      babelHelpers.classPrivateFieldSet(this, _viewMode$1, viewMode || ViewMode.none());
	      babelHelpers.classPrivateFieldGet(this, _listNodes).forEach(function (node) {
	        return main_core.Dom.clean(node);
	      });
	      babelHelpers.classPrivateFieldSet(this, _triggersData, main_core.Type.isArray(data.TRIGGERS) ? data.TRIGGERS : []);
	      this.initTriggers();
	      this.markModified(false);
	    }
	  }, {
	    key: "initTriggers",
	    value: function initTriggers() {
	      var _this2 = this;
	      babelHelpers.classPrivateFieldSet(this, _triggers, []);
	      babelHelpers.classPrivateFieldGet(this, _triggersData).forEach(function (triggerData) {
	        var trigger = new Trigger();
	        trigger.init(triggerData, babelHelpers.classPrivateFieldGet(_this2, _viewMode$1));
	        _this2.subscribeTriggerEvents(trigger);
	        _this2.insertTriggerNode(trigger.getStatusId(), trigger.node);
	        babelHelpers.classPrivateFieldGet(_this2, _triggers).push(trigger);
	      });
	    }
	  }, {
	    key: "subscribeTriggerEvents",
	    value: function subscribeTriggerEvents(trigger) {
	      var _this3 = this;
	      trigger.subscribe('Trigger:copied', function (event) {
	        var trigger = event.data.trigger;
	        babelHelpers.classPrivateFieldGet(_this3, _triggers).push(trigger);
	        if (!event.data.skipInsert) {
	          _this3.insertTriggerNode(trigger.getStatusId(), trigger.node);
	        }
	        _this3.subscribeTriggerEvents(trigger);
	        _this3.markModified();
	      });
	      trigger.subscribe('Trigger:modified', function () {
	        return _this3.markModified();
	      });
	      trigger.subscribe('Trigger:onSettingsOpen', function (event) {
	        _this3.openTriggerSettingsDialog(event.data.trigger);
	      });
	      trigger.subscribe('Trigger:deleted', function (event) {
	        return _this3.deleteTrigger(event.data.trigger);
	      });
	    }
	  }, {
	    key: "onSearch",
	    value: function onSearch(event) {
	      babelHelpers.classPrivateFieldGet(this, _triggers).forEach(function (trigger) {
	        return trigger.onSearch(event);
	      });
	    }
	  }, {
	    key: "enableManageMode",
	    value: function enableManageMode() {
	      babelHelpers.classPrivateFieldSet(this, _viewMode$1, ViewMode.manage());
	      var deleteButtons = document.querySelectorAll('[data-role="btn-delete-trigger"]');
	      deleteButtons.forEach(function (node) {
	        return main_core.Dom.hide(node);
	      });
	      babelHelpers.classPrivateFieldGet(this, _triggers).forEach(function (trigger) {
	        return main_core.Dom.addClass(trigger.node, '--locked-node');
	      });
	    }
	  }, {
	    key: "disableManageMode",
	    value: function disableManageMode() {
	      babelHelpers.classPrivateFieldSet(this, _viewMode$1, ViewMode.edit());
	      var deleteButtons = document.querySelectorAll('[data-role="btn-delete-trigger"]');
	      deleteButtons.forEach(function (node) {
	        return main_core.Dom.show(node);
	      });
	      babelHelpers.classPrivateFieldGet(this, _triggers).forEach(function (trigger) {
	        return main_core.Dom.removeClass(trigger.node, '--locked-node');
	      });
	    }
	  }, {
	    key: "addTrigger",
	    value: function addTrigger(triggerData, callback) {
	      var trigger = new Trigger();
	      trigger.draft = true;
	      trigger.init(triggerData, babelHelpers.classPrivateFieldGet(this, _viewMode$1));
	      this.subscribeTriggerEvents(trigger);
	      if (callback) {
	        callback.call(this, trigger);
	      }
	      this.emit('TriggerManager:trigger:add', {
	        trigger: trigger
	      });
	    }
	  }, {
	    key: "deleteTrigger",
	    value: function deleteTrigger(trigger, callback) {
	      if (trigger.getId() > 0) {
	        trigger.markDeleted();
	      } else {
	        for (var i = 0; i < babelHelpers.classPrivateFieldGet(this, _triggers).length; ++i) {
	          if (babelHelpers.classPrivateFieldGet(this, _triggers)[i] === trigger) {
	            babelHelpers.classPrivateFieldGet(this, _triggers).splice(i, 1);
	          }
	        }
	      }
	      if (callback) {
	        callback(trigger);
	      }
	      this.emit('TriggerManager:trigger:delete', {
	        trigger: trigger
	      });
	      this.markModified();
	    }
	  }, {
	    key: "enableDragAndDrop",
	    value: function enableDragAndDrop() {
	      babelHelpers.classPrivateFieldGet(this, _triggers).forEach(function (trigger) {
	        return trigger.registerItem(trigger.node);
	      });
	      babelHelpers.classPrivateFieldGet(this, _triggersContainerNode).querySelectorAll('.bizproc-automation-trigger-item-wrapper').forEach(function (node) {
	        main_core.Dom.addClass(node, 'bizproc-automation-trigger-item-wrapper-draggable');
	      });
	    }
	  }, {
	    key: "disableDragAndDrop",
	    value: function disableDragAndDrop() {
	      babelHelpers.classPrivateFieldGet(this, _triggers).forEach(function (trigger) {
	        return trigger.unregisterItem(trigger.node);
	      });
	      babelHelpers.classPrivateFieldGet(this, _triggersContainerNode).querySelectorAll('.bizproc-automation-trigger-item-wrapper').forEach(function (node) {
	        main_core.Dom.removeClass(node, 'bizproc-automation-trigger-item-wrapper-draggable');
	      });
	    }
	  }, {
	    key: "insertTrigger",
	    value: function insertTrigger(trigger) {
	      babelHelpers.classPrivateFieldGet(this, _triggers).push(trigger);
	      this.markModified(true);
	    }
	  }, {
	    key: "insertTriggerNode",
	    value: function insertTriggerNode(documentStatus, triggerNode) {
	      var listNode = babelHelpers.classPrivateFieldGet(this, _triggersContainerNode).querySelector('[data-role="trigger-list"][data-status-id="' + documentStatus + '"]');
	      if (listNode) {
	        main_core.Dom.append(triggerNode, listNode);
	      }
	    }
	  }, {
	    key: "serialize",
	    value: function serialize() {
	      return babelHelpers.classPrivateFieldGet(this, _triggers).map(function (trigger) {
	        return trigger.serialize();
	      });
	    }
	  }, {
	    key: "countAllTriggers",
	    value: function countAllTriggers() {
	      return babelHelpers.classPrivateFieldGet(this, _triggers).filter(function (trigger) {
	        return !trigger.deleted;
	      }).length;
	    }
	  }, {
	    key: "getTriggerName",
	    value: function getTriggerName(code) {
	      var _getGlobalContext$ava, _getGlobalContext$ava2;
	      return (_getGlobalContext$ava = (_getGlobalContext$ava2 = bizproc_automation.getGlobalContext().availableTriggers.find(function (trigger) {
	        return code === trigger['CODE'];
	      })) === null || _getGlobalContext$ava2 === void 0 ? void 0 : _getGlobalContext$ava2.NAME) !== null && _getGlobalContext$ava !== void 0 ? _getGlobalContext$ava : code;
	    }
	  }, {
	    key: "getAvailableTrigger",
	    value: function getAvailableTrigger(code) {
	      var availableTriggers = bizproc_automation.getGlobalContext().availableTriggers;
	      for (var i = 0; i < availableTriggers.length; ++i) {
	        if (code === availableTriggers[i]['CODE']) {
	          return availableTriggers[i];
	        }
	      }
	      return null;
	    }
	  }, {
	    key: "canEdit",
	    value: function canEdit() {
	      return bizproc_automation.getGlobalContext().canEdit;
	    }
	  }, {
	    key: "canSetExecuteBy",
	    value: function canSetExecuteBy() {
	      var _getGlobalContext$get;
	      return (_getGlobalContext$get = bizproc_automation.getGlobalContext().get('TRIGGER_CAN_SET_EXECUTE_BY')) !== null && _getGlobalContext$get !== void 0 ? _getGlobalContext$get : false;
	    }
	  }, {
	    key: "needSave",
	    value: function needSave() {
	      return babelHelpers.classPrivateFieldGet(this, _modified);
	    }
	  }, {
	    key: "markModified",
	    value: function markModified(modified) {
	      babelHelpers.classPrivateFieldSet(this, _modified, modified !== false);
	      if (babelHelpers.classPrivateFieldGet(this, _modified)) {
	        this.emit('TriggerManager:dataModified');
	      }
	    }
	  }, {
	    key: "openTriggerSettingsDialog",
	    value: function openTriggerSettingsDialog(trigger, context) {
	      var _this4 = this;
	      if (Designer.getInstance().getTriggerSettingsDialog()) {
	        if (context && context.changeTrigger) {
	          Designer.getInstance().getTriggerSettingsDialog().popup.close();
	        } else {
	          return;
	        }
	      }
	      var formName = 'bizproc_automation_trigger_dialog';
	      var form = main_core.Dom.create('form', {
	        props: {
	          name: formName
	        },
	        style: {
	          "min-width": '540px'
	        }
	      });
	      main_core.Dom.append(this.renderConditionSettings(trigger), form);
	      var iconHelp = main_core.Dom.create('div', {
	        attrs: {
	          className: 'bizproc-automation-robot-help'
	        },
	        events: {
	          click: function click(event) {
	            return _this4.emit('TriggerManager:onHelpClick', event);
	          }
	        }
	      });
	      main_core.Dom.append(iconHelp, form);
	      var title = this.getTriggerName(trigger.getCode());
	      main_core.Dom.append(main_core.Dom.create("span", {
	        attrs: {
	          className: "bizproc-automation-popup-settings-title bizproc-automation-popup-settings-title-autocomplete"
	        },
	        text: main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_TRIGGER_NAME') + ':'
	      }), form);
	      main_core.Dom.append(main_core.Dom.create("div", {
	        attrs: {
	          className: "bizproc-automation-popup-settings"
	        },
	        children: [main_core.Dom.create("input", {
	          attrs: {
	            className: 'bizproc-automation-popup-input',
	            type: "text",
	            name: "name",
	            value: trigger.getName() || title
	          }
	        })]
	      }), form);
	      var triggerData = this.getAvailableTrigger(trigger.getCode());
	      if (triggerData && triggerData['SETTINGS']) {
	        _classPrivateMethodGet$1(this, _renderTriggerProperties, _renderTriggerProperties2).call(this, trigger, triggerData['SETTINGS']['Properties'], form);
	      }
	      BX.onCustomEvent('BX.Bizproc.Automation.TriggerManager:onOpenSettingsDialog-' + trigger.getCode(), [trigger, form]);
	      if (this.canSetExecuteBy()) {
	        this.renderExecuteByControl(trigger, form);
	      }
	      this.renderAllowBackwardsControl(trigger, form);
	      main_core.Dom.addClass(babelHelpers.classPrivateFieldGet(this, _triggersContainerNode), 'automation-base-blocked');
	      Designer.getInstance().setTriggerSettingsDialog({
	        triggerManager: this,
	        trigger: trigger,
	        form: form
	      });
	      var self = this;
	      var popup = new BX.PopupWindow(Helper.generateUniqueId(), null, {
	        titleBar: title,
	        content: form,
	        closeIcon: true,
	        offsetLeft: 0,
	        offsetTop: 0,
	        closeByEsc: true,
	        draggable: {
	          restrict: false
	        },
	        overlay: false,
	        events: {
	          onPopupClose: function onPopupClose(popup) {
	            Designer.getInstance().setTriggerSettingsDialog(null);
	            self.destroySettingsDialogControls();
	            popup.destroy();
	            main_core.Dom.removeClass(babelHelpers.classPrivateFieldGet(self, _triggersContainerNode), 'automation-base-blocked');
	            self.emit('TriggerManager:onCloseTriggerSettingsDialog');
	          }
	        },
	        buttons: [new BX.PopupWindowButton({
	          text: main_core.Loc.getMessage('JS_CORE_WINDOW_SAVE'),
	          className: "popup-window-button-accept",
	          events: {
	            click: function click() {
	              var formData = BX.ajax.prepareForm(form);
	              trigger.setName(formData['data']['name']);
	              if (triggerData['SETTINGS']) {
	                _classPrivateMethodGet$1(_this4, _setTriggerProperties, _setTriggerProperties2).call(_this4, trigger, triggerData['SETTINGS']['Properties'], form);
	              }
	              BX.onCustomEvent('BX.Bizproc.Automation.TriggerManager:onSaveSettings-' + trigger.getCode(), [trigger, formData]);
	              self.setConditionSettingsFromForm(formData['data'], trigger);
	              trigger.setAllowBackwards(formData['data']['allow_backwards'] === 'Y');
	              if (self.canSetExecuteBy()) {
	                trigger.setExecuteBy(formData['data']['execute_by']);
	              }
	              if (trigger.draft) ;

	              //analytics
	              main_core.ajax.runAction('bizproc.analytics.push', {
	                analyticsLabel: "automation_trigger".concat(trigger.draft ? '_draft' : '', "_save_").concat(trigger.getCode().toLowerCase())
	              });
	              delete trigger.draft;
	              trigger.reInit();
	              self.markModified();
	              popup.close();
	            }
	          }
	        }), new BX.PopupWindowButtonLink({
	          text: main_core.Loc.getMessage('JS_CORE_WINDOW_CANCEL'),
	          className: "popup-window-button-link-cancel",
	          events: {
	            click: function click() {
	              this.popupWindow.close();
	            }
	          }
	        })]
	      });
	      Designer.getInstance().getTriggerSettingsDialog().popup = popup;
	      popup.show();

	      //analytics
	      main_core.ajax.runAction('bizproc.analytics.push', {
	        analyticsLabel: "automation_trigger".concat(trigger.draft ? '_draft' : '', "_settings_").concat(trigger.getCode().toLowerCase())
	      });
	    }
	  }, {
	    key: "renderConditionSettings",
	    value: function renderConditionSettings(trigger) {
	      var conditionGroup = trigger.getCondition().clone();
	      this.conditionSelector = new bizproc_automation.ConditionGroupSelector(conditionGroup, {
	        fields: bizproc_automation.getGlobalContext().document.getFields(),
	        showValuesSelector: false
	      });
	      var selector = this.conditionSelector;
	      return main_core.Dom.create("div", {
	        attrs: {
	          className: "bizproc-automation-popup-settings"
	        },
	        children: [main_core.Dom.create("div", {
	          attrs: {
	            className: "bizproc-automation-popup-settings-block"
	          },
	          children: [main_core.Dom.create("span", {
	            attrs: {
	              className: "bizproc-automation-popup-settings-title"
	            },
	            text: main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_CONDITION') + ":"
	          }), selector.createNode()]
	        })]
	      });
	    }
	  }, {
	    key: "renderExecuteByControl",
	    value: function renderExecuteByControl(trigger, form) {
	      main_core.Dom.append(main_core.Dom.create("span", {
	        attrs: {
	          className: "bizproc-automation-popup-settings-title bizproc-automation-popup-settings-title-top bizproc-automation-popup-settings-title-autocomplete"
	        },
	        text: main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_TRIGGER_EXECUTE_BY') + ':'
	      }), form);
	      main_core.Dom.append(main_core.Dom.create("div", {
	        attrs: {
	          className: "bizproc-automation-popup-settings"
	        },
	        children: [BX.Bizproc.FieldType.renderControl(bizproc_automation.getGlobalContext().document.getRawType(), {
	          Type: 'user'
	        }, 'execute_by', trigger.draft ? Helper.getResponsibleUserExpression(bizproc_automation.getGlobalContext().document.getFields()) : trigger.getExecuteBy())]
	      }), form);
	    }
	  }, {
	    key: "renderAllowBackwardsControl",
	    value: function renderAllowBackwardsControl(trigger, form) {
	      main_core.Dom.append(main_core.Dom.create("div", {
	        attrs: {
	          className: "bizproc-automation-popup-checkbox"
	        },
	        children: [main_core.Dom.create("div", {
	          attrs: {
	            className: "bizproc-automation-popup-checkbox-item"
	          },
	          children: [main_core.Dom.create("label", {
	            attrs: {
	              className: "bizproc-automation-popup-chk-label"
	            },
	            children: [main_core.Dom.create("input", {
	              attrs: {
	                className: 'bizproc-automation-popup-chk',
	                type: "checkbox",
	                name: "allow_backwards",
	                value: 'Y'
	              },
	              props: {
	                checked: trigger.isBackwardsAllowed()
	              }
	            }), document.createTextNode(main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_TRIGGER_ALLOW_REVERSE'))]
	          })]
	        })]
	      }), form);
	    }
	  }, {
	    key: "setConditionSettingsFromForm",
	    value: function setConditionSettingsFromForm(formFields, trigger) {
	      trigger.setCondition(bizproc_automation.ConditionGroup.createFromForm(formFields));
	      return this;
	    }
	  }, {
	    key: "onRestAppInstall",
	    value: function onRestAppInstall(installed, eventResult) {
	      eventResult.redirect = false;
	      setTimeout(function () {
	        BX.ajax({
	          method: 'POST',
	          dataType: 'json',
	          url: bizproc_automation.getGlobalContext().ajaxUrl,
	          data: {
	            ajax_action: 'get_available_triggers',
	            document_signed: bizproc_automation.getGlobalContext().signedDocument
	          },
	          onsuccess: function onsuccess(response) {
	            if (main_core.Type.isArray(response['DATA'])) {
	              bizproc_automation.getGlobalContext().set('availableTriggers', response['DATA']);
	            }
	          }
	        });
	      }, 1500);
	    }
	  }, {
	    key: "initSettingsDialogControls",
	    value: function initSettingsDialogControls(node) {
	      if (!main_core.Type.isArray(this.settingsDialogControls)) {
	        this.settingsDialogControls = [];
	      }
	      var controlNodes = node.querySelectorAll('[data-role]');
	      for (var i = 0; i < controlNodes.length; ++i) {
	        var control = null;
	        var role = controlNodes[i].getAttribute('data-role');
	        if (role === 'user-selector') {
	          control = BX.Bizproc.UserSelector.decorateNode(controlNodes[i]);
	        }
	        BX.UI.Hint.init(controlNodes[i]);
	        if (control) {
	          this.settingsDialogControls.push(control);
	        }
	      }
	    }
	  }, {
	    key: "destroySettingsDialogControls",
	    value: function destroySettingsDialogControls() {
	      if (this.conditionSelector) {
	        this.conditionSelector.destroy();
	        this.conditionSelector = null;
	      }
	      if (main_core.Type.isArray(this.settingsDialogControls)) {
	        for (var i = 0; i < this.settingsDialogControls.length; ++i) {
	          if (main_core.Type.isFunction(this.settingsDialogControls[i].destroy)) {
	            this.settingsDialogControls[i].destroy();
	          }
	        }
	      }
	      this.settingsDialogControls = null;
	    }
	  }, {
	    key: "getListByDocumentStatus",
	    value: function getListByDocumentStatus(statusId) {
	      var result = [];
	      babelHelpers.classPrivateFieldGet(this, _triggers).forEach(function (trigger) {
	        if (trigger.getStatusId() === statusId) {
	          result.push(trigger);
	        }
	      });
	      return result;
	    }
	  }, {
	    key: "getReturnProperties",
	    value: function getReturnProperties(statusId) {
	      var result = [];
	      var exists = {};
	      var triggers = this.getListByDocumentStatus(statusId);
	      triggers.forEach(function (trigger) {
	        var props = trigger.deleted ? [] : trigger.getReturnProperties();
	        if (props.length) {
	          props.forEach(function (property) {
	            if (!exists[property.Id]) {
	              result.push({
	                Id: property.Id,
	                ObjectId: 'Template',
	                Name: property.Name,
	                ObjectName: trigger.getName(),
	                Type: property.Type,
	                Expression: '{{~*:' + property.Id + '}}',
	                SystemExpression: '{=Template:' + property.Id + '}'
	              });
	              exists[property.Id] = true;
	            }
	          });
	        }
	      });
	      return result;
	    }
	  }, {
	    key: "getReturnProperty",
	    value: function getReturnProperty(statusId, propertyId) {
	      var properties = this.getReturnProperties(statusId);
	      for (var i = 0; i < properties.length; ++i) {
	        if (properties[i].Id === propertyId) {
	          return properties[i];
	        }
	      }
	      return null;
	    }
	  }]);
	  return TriggerManager;
	}(main_core_events.EventEmitter);
	function _renderTriggerProperties2(trigger, properties, form) {
	  var _this5 = this;
	  properties.forEach(function (property) {
	    var value = trigger.getApplyRules()[property.Id];
	    if (property.Type === '@condition-group-selector') {
	      _classPrivateMethodGet$1(_this5, _renderConditionGroupSelector, _renderConditionGroupSelector2).call(_this5, property, value, form);
	      return;
	    }
	    if (property.Type === '@webhook-code') {
	      _classPrivateMethodGet$1(_this5, _renderWebhookCodeProperty, _renderWebhookCodeProperty2).call(_this5, property, value, form);
	      return;
	    }
	    if (property.Type === '@field-selector') {
	      _classPrivateMethodGet$1(_this5, _renderFieldSelectorProperty, _renderFieldSelectorProperty2).call(_this5, property, value, form);
	      return;
	    }
	    var toRenderProperty = _objectSpread({
	      AllowSelection: false
	    }, property);
	    if (toRenderProperty.Type === '@robot-select') {
	      _classPrivateMethodGet$1(_this5, _prepareRobotSelectProperty, _prepareRobotSelectProperty2).call(_this5, toRenderProperty);
	    }
	    main_core.Dom.append(main_core.Dom.create("span", {
	      attrs: {
	        className: "bizproc-automation-popup-settings-title bizproc-automation-popup-settings-title-top bizproc-automation-popup-settings-title-autocomplete"
	      },
	      text: property.Name + ':'
	    }), form);
	    main_core.Dom.append(main_core.Dom.create("div", {
	      attrs: {
	        className: "bizproc-automation-popup-settings"
	      },
	      children: [BX.Bizproc.FieldType.renderControl([].concat(babelHelpers.toConsumableArray(bizproc_automation.getGlobalContext().document.getRawType()), [bizproc_automation.getGlobalContext().document.getCategoryId()]), toRenderProperty, property.Id, value || '')]
	    }), form);
	  });
	}
	function _prepareRobotSelectProperty2(property) {
	  var cmp = Designer.getInstance().component;
	  property.Options = [];
	  var filter = property.Settings.Filter;
	  var check = function check(robot) {
	    for (var field in filter) {
	      if (robot.data[field] !== filter[field]) {
	        return false;
	      }
	    }
	    return true;
	  };
	  cmp.templateManager.templates.forEach(function (template) {
	    template.robots.forEach(function (robot) {
	      if (check(robot)) {
	        property.Options.push({
	          value: robot.getId(),
	          name: robot.getProperty(property.Settings.OptionNameProperty)
	        });
	      }
	    });
	  });
	  delete property.Settings;
	  property.Type = 'select';
	}
	function _setTriggerProperties2(trigger, properties, form) {
	  var _this6 = this;
	  var values = {};
	  properties.forEach(function (property) {
	    if (property.Type === '@condition-group-selector') {
	      values[property.Id] = _classPrivateMethodGet$1(_this6, _setConditionGroupValue, _setConditionGroupValue2).call(_this6, property, form);
	      return;
	    }
	    var formData = BX.ajax.prepareForm(form);
	    values[property.Id] = formData.data[property.Id];
	  });
	  trigger.setApplyRules(values);
	}
	function _renderConditionGroupSelector2(property, value, form) {
	  var selector = new bizproc_automation.ConditionGroupSelector(new bizproc_automation.ConditionGroup(value), {
	    fields: property.Settings.Fields,
	    fieldPrefix: property.Id,
	    showValuesSelector: false
	  });
	  main_core.Dom.append(main_core.Dom.create("div", {
	    attrs: {
	      className: "bizproc-automation-popup-settings"
	    },
	    children: [main_core.Dom.create("div", {
	      attrs: {
	        className: "bizproc-automation-popup-settings-block"
	      },
	      children: [main_core.Dom.create("span", {
	        attrs: {
	          className: "bizproc-automation-popup-settings-title"
	        },
	        text: property.Name + ":"
	      }), selector.createNode()]
	    })]
	  }), form);
	}
	function _setConditionGroupValue2(property, form) {
	  var formData = BX.ajax.prepareForm(form);
	  var conditionGroup = bizproc_automation.ConditionGroup.createFromForm(formData['data'], property.Id);
	  return conditionGroup.serialize();
	}
	function _renderWebhookCodeProperty2(property, value, form) {
	  if (!value) {
	    value = main_core.Text.getRandom(5);
	  }
	  main_core.Dom.append(main_core.Dom.create("span", {
	    attrs: {
	      className: "bizproc-automation-popup-settings-title bizproc-automation-popup-settings-title-autocomplete"
	    },
	    text: property.Name + ':'
	  }), form);
	  main_core.Dom.append(main_core.Dom.create('input', {
	    props: {
	      type: 'hidden',
	      value: value,
	      name: 'code'
	    }
	  }), form);
	  var hookLinkTextarea = main_core.Dom.create("textarea", {
	    attrs: {
	      className: "bizproc-automation-popup-textarea",
	      placeholder: "...",
	      readonly: 'readonly',
	      name: 'webhook_handler'
	    },
	    events: {
	      click: function click() {
	        this.select();
	      }
	    }
	  });
	  main_core.Dom.append(main_core.Dom.create("div", {
	    attrs: {
	      className: "bizproc-automation-popup-settings"
	    },
	    children: [hookLinkTextarea]
	  }), form);
	  main_core.Dom.append(main_core.Dom.create("span", {
	    attrs: {
	      className: "bizproc-automation-popup-settings-title bizproc-automation-popup-settings-title-autocomplete"
	    },
	    text: main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_WEBHOOK_ID')
	  }), form);
	  if (property.Settings.Handler) {
	    var url = window.location.protocol + '//' + window.location.host + property.Settings.Handler;
	    url = main_core.Uri.addParam(url, {
	      code: value
	    });
	    url = url.replace('{{DOCUMENT_TYPE}}', bizproc_automation.getGlobalContext().document.getRawType()[2]);
	    url = url.replace('{{USER_ID}}', main_core.Loc.getMessage('USER_ID'));
	    if (property.Settings.Password) {
	      url = url.replace('{{PASSWORD}}', property.Settings.Password);
	    }
	    hookLinkTextarea.value = url;
	  }
	  if (!property.Settings.Password && property.Settings.PasswordLoader) {
	    var myAlertText = main_core.Loc.getMessage('BIZPROC_AUTOMATION_WEBHOOK_PASSWORD_ALERT').replace('#A1#', '<a class="bizproc-automation-popup-settings-link ' + 'bizproc-automation-popup-settings-link-light" data-role="token-gen">').replace('#A2#', '</a>');
	    var passwordAlert = new ui_alerts.Alert({
	      color: ui_alerts.AlertColor.WARNING,
	      icon: ui_alerts.AlertIcon.WARNING,
	      text: myAlertText
	    });
	    main_core.Event.bind(passwordAlert.getTextContainer().querySelector('[data-role="token-gen"]'), 'click', function () {
	      var loaderConfig = property.Settings.PasswordLoader;
	      BX.ajax.runComponentAction(loaderConfig.component, loaderConfig.action, {
	        mode: loaderConfig.mode || undefined,
	        data: {
	          documentType: [].concat(babelHelpers.toConsumableArray(bizproc_automation.getGlobalContext().document.getRawType()), [bizproc_automation.getGlobalContext().document.getCategoryId()])
	        }
	      }).then(function (response) {
	        if (response.data.error) {
	          window.alert(response.data.error);
	        } else if (response.data.password) {
	          property.Settings.Password = response.data.password;
	          hookLinkTextarea.value = hookLinkTextarea.value.replace('{{PASSWORD}}', property.Settings.Password);
	          passwordAlert.handleCloseBtnClick();
	        }
	      });
	    });
	    main_core.Dom.append(passwordAlert.getContainer(), form);
	  }
	}
	function _renderFieldSelectorProperty2(property, value, form) {
	  var menuId = '@field-selector' + Math.random();
	  var fieldName = property.Id + '[]';
	  var fieldsList = property.Settings.Fields;
	  var renderFieldCheckbox = function renderFieldCheckbox(field, listNode) {
	    var exists = listNode.querySelector('[data-field="' + field['Id'] + '"]');
	    if (exists) {
	      return;
	    }
	    main_core.Dom.append(main_core.Dom.create('div', {
	      attrs: {
	        className: 'bizproc-automation-popup-checkbox-item',
	        'data-field': field['Id']
	      },
	      children: [main_core.Dom.create('label', {
	        attrs: {
	          className: 'bizproc-automation-popup-chk-label'
	        },
	        children: [main_core.Dom.create('input', {
	          attrs: {
	            className: 'bizproc-automation-popup-chk',
	            type: 'checkbox',
	            name: fieldName,
	            value: field['Id']
	          },
	          props: {
	            checked: true
	          }
	        }), document.createTextNode(field['Name'])]
	      })]
	    }), listNode);
	  };
	  var fieldSelectorHandler = function fieldSelectorHandler(targetNode, listNode) {
	    if (BX.Main.MenuManager.getMenuById(menuId)) {
	      return BX.Main.MenuManager.getMenuById(menuId).show();
	    }
	    var menuItems = [];
	    fieldsList.forEach(function (field) {
	      menuItems.push({
	        text: BX.Text.encode(field['Name']),
	        field: field,
	        onclick: function onclick(event, item) {
	          renderFieldCheckbox(item.field, listNode);
	          this.popupWindow.close();
	        }
	      });
	    });
	    BX.Main.MenuManager.show(menuId, targetNode, menuItems, {
	      autoHide: true,
	      offsetLeft: BX.pos(this)['width'] / 2,
	      angle: {
	        position: 'top',
	        offset: 0
	      },
	      zIndex: 200,
	      className: 'bizproc-automation-inline-selector-menu',
	      events: {
	        onPopupClose: function onPopupClose(popup) {
	          popup.destroy();
	        }
	      }
	    });
	  };
	  main_core.Dom.append(main_core.Dom.create('span', {
	    attrs: {
	      className: "bizproc-automation-popup-settings-title bizproc-automation-popup-settings-title-autocomplete"
	    },
	    text: property.Name + ':'
	  }), form);
	  var fieldListNode = main_core.Dom.create('div', {
	    attrs: {
	      className: 'bizproc-automation-popup-checkbox'
	    },
	    children: []
	  });
	  main_core.Dom.append(fieldListNode, form);
	  main_core.Dom.append(main_core.Dom.create('div', {
	    attrs: {
	      className: 'bizproc-automation-popup-settings bizproc-automation-popup-settings-text'
	    },
	    children: [main_core.Dom.create('span', {
	      attrs: {
	        className: "bizproc-automation-popup-settings-link"
	      },
	      text: property.Settings.ChooseFieldLabel,
	      events: {
	        click: function click() {
	          fieldSelectorHandler(this, fieldListNode);
	        }
	      }
	    })]
	  }), form);
	  if (main_core.Type.isArray(value)) {
	    value.forEach(function (field) {
	      var foundField = fieldsList.find(function (fld) {
	        return fld.Id === field;
	      });
	      if (foundField) {
	        renderFieldCheckbox(foundField, fieldListNode);
	      }
	    });
	  }
	}

	function _classPrivateFieldInitSpec$6(obj, privateMap, value) { _checkPrivateRedeclaration$6(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$6(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	var _rawType = /*#__PURE__*/new WeakMap();
	var _id = /*#__PURE__*/new WeakMap();
	var _title = /*#__PURE__*/new WeakMap();
	var _categoryId = /*#__PURE__*/new WeakMap();
	var _statusList = /*#__PURE__*/new WeakMap();
	var _currentStatusIndex = /*#__PURE__*/new WeakMap();
	var _fields = /*#__PURE__*/new WeakMap();
	var Document = /*#__PURE__*/function () {
	  function Document(options) {
	    babelHelpers.classCallCheck(this, Document);
	    _classPrivateFieldInitSpec$6(this, _rawType, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$6(this, _id, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$6(this, _title, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$6(this, _categoryId, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$6(this, _statusList, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$6(this, _currentStatusIndex, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$6(this, _fields, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldSet(this, _rawType, options.rawDocumentType);
	    babelHelpers.classPrivateFieldSet(this, _id, options.documentId);
	    babelHelpers.classPrivateFieldSet(this, _title, options.title);
	    babelHelpers.classPrivateFieldSet(this, _categoryId, options.categoryId);
	    babelHelpers.classPrivateFieldSet(this, _statusList, []);
	    babelHelpers.classPrivateFieldSet(this, _currentStatusIndex, 0);
	    if (main_core.Type.isArray(options.statusList)) {
	      babelHelpers.classPrivateFieldSet(this, _statusList, options.statusList);
	      babelHelpers.classPrivateFieldSet(this, _currentStatusIndex, babelHelpers.classPrivateFieldGet(this, _statusList).findIndex(function (status) {
	        return status.STATUS_ID === options.statusId;
	      }));
	    } else if (main_core.Type.isStringFilled(options.statusId)) {
	      babelHelpers.classPrivateFieldGet(this, _statusList).push(options.statusId);
	    }
	    if (babelHelpers.classPrivateFieldGet(this, _currentStatusIndex) < 0) {
	      babelHelpers.classPrivateFieldSet(this, _currentStatusIndex, 0);
	    }
	    babelHelpers.classPrivateFieldSet(this, _fields, main_core.Type.isArray(options.documentFields) ? options.documentFields : []);
	  }
	  babelHelpers.createClass(Document, [{
	    key: "clone",
	    value: function clone() {
	      return new Document({
	        rawDocumentType: main_core.Runtime.clone(babelHelpers.classPrivateFieldGet(this, _rawType)),
	        documentId: babelHelpers.classPrivateFieldGet(this, _id),
	        categoryId: babelHelpers.classPrivateFieldGet(this, _categoryId),
	        statusId: this.getCurrentStatusId(),
	        statusList: main_core.Runtime.clone(babelHelpers.classPrivateFieldGet(this, _statusList)),
	        documentFields: main_core.Runtime.clone(babelHelpers.classPrivateFieldGet(this, _fields)),
	        title: babelHelpers.classPrivateFieldGet(this, _title)
	      });
	    }
	  }, {
	    key: "getId",
	    value: function getId() {
	      return babelHelpers.classPrivateFieldGet(this, _id);
	    }
	  }, {
	    key: "getRawType",
	    value: function getRawType() {
	      return babelHelpers.classPrivateFieldGet(this, _rawType);
	    }
	  }, {
	    key: "getCategoryId",
	    value: function getCategoryId() {
	      return babelHelpers.classPrivateFieldGet(this, _categoryId);
	    }
	  }, {
	    key: "getCurrentStatusId",
	    value: function getCurrentStatusId() {
	      var _babelHelpers$classPr;
	      var documentStatus = (_babelHelpers$classPr = babelHelpers.classPrivateFieldGet(this, _statusList)[babelHelpers.classPrivateFieldGet(this, _currentStatusIndex)]) === null || _babelHelpers$classPr === void 0 ? void 0 : _babelHelpers$classPr.STATUS_ID;
	      return !main_core.Type.isNil(documentStatus) ? String(documentStatus) : documentStatus;
	    }
	  }, {
	    key: "getSortedStatusId",
	    value: function getSortedStatusId(index) {
	      if (index >= 0 && index < babelHelpers.classPrivateFieldGet(this, _statusList).length) {
	        return babelHelpers.classPrivateFieldGet(this, _statusList)[index].STATUS_ID;
	      }
	      return null;
	    }
	  }, {
	    key: "getNextStatusIdList",
	    value: function getNextStatusIdList() {
	      return babelHelpers.classPrivateFieldGet(this, _statusList).slice(babelHelpers.classPrivateFieldGet(this, _currentStatusIndex) + 1).map(function (status) {
	        return status.STATUS_ID;
	      });
	    }
	  }, {
	    key: "getPreviousStatusIdList",
	    value: function getPreviousStatusIdList() {
	      return babelHelpers.classPrivateFieldGet(this, _statusList).slice(0, babelHelpers.classPrivateFieldGet(this, _currentStatusIndex)).map(function (status) {
	        return status.STATUS_ID;
	      });
	    }
	  }, {
	    key: "setStatus",
	    value: function setStatus(statusId) {
	      var newStatusId = babelHelpers.classPrivateFieldGet(this, _statusList).findIndex(function (status) {
	        return status.STATUS_ID === statusId;
	      });
	      if (newStatusId >= 0) {
	        babelHelpers.classPrivateFieldSet(this, _currentStatusIndex, newStatusId);
	      }
	      return this;
	    }
	  }, {
	    key: "getFields",
	    value: function getFields() {
	      return babelHelpers.classPrivateFieldGet(this, _fields);
	    }
	  }, {
	    key: "setFields",
	    value: function setFields(documentFields) {
	      babelHelpers.classPrivateFieldSet(this, _fields, documentFields);
	      return this;
	    }
	  }, {
	    key: "setStatusList",
	    value: function setStatusList(statusList) {
	      if (main_core.Type.isArrayFilled(statusList)) {
	        babelHelpers.classPrivateFieldSet(this, _statusList, statusList);
	      }
	      return this;
	    }
	  }, {
	    key: "title",
	    get: function get() {
	      return babelHelpers.classPrivateFieldGet(this, _title);
	    }
	  }, {
	    key: "statusList",
	    get: function get() {
	      return babelHelpers.classPrivateFieldGet(this, _statusList);
	    }
	  }]);
	  return Document;
	}();

	function _classPrivateFieldInitSpec$7(obj, privateMap, value) { _checkPrivateRedeclaration$7(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$7(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	var _basis = /*#__PURE__*/new WeakMap();
	var _type = /*#__PURE__*/new WeakMap();
	var _value = /*#__PURE__*/new WeakMap();
	var _valueType = /*#__PURE__*/new WeakMap();
	var _workTime = /*#__PURE__*/new WeakMap();
	var _waitWorkDay = /*#__PURE__*/new WeakMap();
	var _inTime = /*#__PURE__*/new WeakMap();
	var DelayInterval = /*#__PURE__*/function () {
	  function DelayInterval(params) {
	    babelHelpers.classCallCheck(this, DelayInterval);
	    _classPrivateFieldInitSpec$7(this, _basis, {
	      writable: true,
	      value: DelayInterval.BASIS_TYPE.CurrentDateTime
	    });
	    _classPrivateFieldInitSpec$7(this, _type, {
	      writable: true,
	      value: DelayInterval.DELAY_TYPE.After
	    });
	    _classPrivateFieldInitSpec$7(this, _value, {
	      writable: true,
	      value: 0
	    });
	    _classPrivateFieldInitSpec$7(this, _valueType, {
	      writable: true,
	      value: 'i'
	    });
	    _classPrivateFieldInitSpec$7(this, _workTime, {
	      writable: true,
	      value: false
	    });
	    _classPrivateFieldInitSpec$7(this, _waitWorkDay, {
	      writable: true,
	      value: false
	    });
	    _classPrivateFieldInitSpec$7(this, _inTime, {
	      writable: true,
	      value: void 0
	    });
	    if (main_core.Type.isPlainObject(params)) {
	      if (params['type']) {
	        this.setType(params['type']);
	      }
	      if (params['value']) {
	        this.setValue(params['value']);
	      }
	      if (params['valueType']) {
	        this.setValueType(params['valueType']);
	      }
	      if (params['basis']) {
	        this.setBasis(params['basis']);
	      }
	      if (params['workTime']) {
	        this.setWorkTime(params['workTime']);
	      }
	      if (params['waitWorkDay']) {
	        this.setWaitWorkDay(params['waitWorkDay']);
	      }
	      if (params['inTime']) {
	        this.setInTime(params['inTime']);
	      }
	    }
	  }
	  babelHelpers.createClass(DelayInterval, [{
	    key: "clone",
	    value: function clone() {
	      return new DelayInterval({
	        type: babelHelpers.classPrivateFieldGet(this, _type),
	        value: babelHelpers.classPrivateFieldGet(this, _value),
	        valueType: babelHelpers.classPrivateFieldGet(this, _valueType),
	        basis: babelHelpers.classPrivateFieldGet(this, _basis),
	        workTime: babelHelpers.classPrivateFieldGet(this, _workTime),
	        waitWorkDay: babelHelpers.classPrivateFieldGet(this, _waitWorkDay),
	        inTime: babelHelpers.classPrivateFieldGet(this, _inTime)
	      });
	    }
	  }, {
	    key: "setType",
	    value: function setType(type) {
	      if (type !== DelayInterval.DELAY_TYPE.After && type !== DelayInterval.DELAY_TYPE.Before && type !== DelayInterval.DELAY_TYPE.In) {
	        type = DelayInterval.DELAY_TYPE.After;
	      }
	      babelHelpers.classPrivateFieldSet(this, _type, type);
	      return this;
	    }
	  }, {
	    key: "setValue",
	    value: function setValue(value) {
	      value = parseInt(value);
	      babelHelpers.classPrivateFieldSet(this, _value, value >= 0 ? value : 0);
	      return this;
	    }
	  }, {
	    key: "setValueType",
	    value: function setValueType(valueType) {
	      if (valueType !== 'i' && valueType !== 'h' && valueType !== 'd') {
	        valueType = 'i';
	      }
	      babelHelpers.classPrivateFieldSet(this, _valueType, valueType);
	      return this;
	    }
	  }, {
	    key: "setBasis",
	    value: function setBasis(basis) {
	      if (main_core.Type.isString(basis) && basis !== '') {
	        babelHelpers.classPrivateFieldSet(this, _basis, basis);
	      }
	      return this;
	    }
	  }, {
	    key: "setWorkTime",
	    value: function setWorkTime(flag) {
	      babelHelpers.classPrivateFieldSet(this, _workTime, !!flag);
	      return this;
	    }
	  }, {
	    key: "setWaitWorkDay",
	    value: function setWaitWorkDay(flag) {
	      babelHelpers.classPrivateFieldSet(this, _waitWorkDay, !!flag);
	      return this;
	    }
	  }, {
	    key: "setInTime",
	    value: function setInTime(value) {
	      babelHelpers.classPrivateFieldSet(this, _inTime, value);
	      return this;
	    }
	  }, {
	    key: "isNow",
	    value: function isNow() {
	      return babelHelpers.classPrivateFieldGet(this, _type) === DelayInterval.DELAY_TYPE.After && babelHelpers.classPrivateFieldGet(this, _basis) === DelayInterval.BASIS_TYPE.CurrentDateTime && !babelHelpers.classPrivateFieldGet(this, _value) && !this.workTime && !this.inTime;
	    }
	  }, {
	    key: "setNow",
	    value: function setNow() {
	      this.setType(DelayInterval.DELAY_TYPE.After);
	      this.setValue(0);
	      this.setValueType('i');
	      this.setBasis(DelayInterval.BASIS_TYPE.CurrentDateTime);
	      this.setInTime(null);
	    }
	  }, {
	    key: "serialize",
	    value: function serialize() {
	      return {
	        type: babelHelpers.classPrivateFieldGet(this, _type),
	        value: babelHelpers.classPrivateFieldGet(this, _value),
	        valueType: babelHelpers.classPrivateFieldGet(this, _valueType),
	        basis: babelHelpers.classPrivateFieldGet(this, _basis),
	        workTime: babelHelpers.classPrivateFieldGet(this, _workTime) ? 1 : 0,
	        waitWorkDay: babelHelpers.classPrivateFieldGet(this, _waitWorkDay) ? 1 : 0,
	        inTime: babelHelpers.classPrivateFieldGet(this, _inTime) || null
	      };
	    }
	  }, {
	    key: "toExpression",
	    value: function toExpression(basisFields, workerExpression) {
	      var basis = babelHelpers.classPrivateFieldGet(this, _basis) ? babelHelpers.classPrivateFieldGet(this, _basis) : DelayInterval.BASIS_TYPE.CurrentDate;
	      if (!DelayInterval.isSystemBasis(basis) && main_core.Type.isArray(basisFields)) {
	        for (var i = 0, s = basisFields.length; i < s; ++i) {
	          if (basis === basisFields[i].SystemExpression) {
	            basis = basisFields[i].Expression;
	            break;
	          }
	        }
	      }
	      if (this.isNow() || babelHelpers.classPrivateFieldGet(this, _type) === DelayInterval.DELAY_TYPE.In && !babelHelpers.classPrivateFieldGet(this, _workTime) && !babelHelpers.classPrivateFieldGet(this, _inTime)) {
	        return basis;
	      }
	      var days = 0;
	      var hours = 0;
	      var minutes = 0;
	      switch (babelHelpers.classPrivateFieldGet(this, _valueType)) {
	        case 'i':
	          minutes = babelHelpers.classPrivateFieldGet(this, _value);
	          break;
	        case 'h':
	          hours = babelHelpers.classPrivateFieldGet(this, _value);
	          break;
	        case 'd':
	          days = babelHelpers.classPrivateFieldGet(this, _value);
	          break;
	      }
	      var add = '';
	      if (babelHelpers.classPrivateFieldGet(this, _type) === DelayInterval.DELAY_TYPE.Before) {
	        add = '-';
	      }
	      if (days > 0) {
	        add += days + 'd';
	      }
	      if (hours > 0) {
	        add += hours + 'h';
	      }
	      if (minutes > 0) {
	        add += minutes + 'i';
	      }
	      var fn = babelHelpers.classPrivateFieldGet(this, _workTime) ? 'workdateadd' : 'dateadd';
	      if (fn === 'workdateadd' && add === '') {
	        add = '0d';
	      }
	      var worker = '';
	      if (fn === 'workdateadd' && workerExpression) {
	        worker = workerExpression;
	      }
	      var result = fn + '(' + basis + ',"' + add + '"' + (worker ? ',' + worker : '') + ')';
	      if (babelHelpers.classPrivateFieldGet(this, _type) === DelayInterval.DELAY_TYPE.In && babelHelpers.classPrivateFieldGet(this, _inTime)) {
	        if (!babelHelpers.classPrivateFieldGet(this, _workTime)) {
	          result = basis;
	        }
	        result = "settime(".concat(result, ", ").concat(babelHelpers.classPrivateFieldGet(this, _inTime)[0] || 0, ", ").concat(babelHelpers.classPrivateFieldGet(this, _inTime)[1] || 0, ")");
	      }
	      return '=' + result;
	    }
	  }, {
	    key: "format",
	    value: function format(emptyText, fields) {
	      var str = emptyText;
	      if (babelHelpers.classPrivateFieldGet(this, _type) === DelayInterval.DELAY_TYPE.In) {
	        str = main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_IN_TIME');
	        if (main_core.Type.isArray(fields)) {
	          for (var i = 0; i < fields.length; ++i) {
	            if (babelHelpers.classPrivateFieldGet(this, _basis) === fields[i].SystemExpression) {
	              str += ' ' + fields[i].Name;
	              break;
	            }
	          }
	        }
	        if (this.inTime) {
	          str += ' ' + this.inTimeString;
	        }
	      } else if (babelHelpers.classPrivateFieldGet(this, _value)) {
	        var prefix = babelHelpers.classPrivateFieldGet(this, _type) === DelayInterval.DELAY_TYPE.After ? main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_THROUGH') : main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_FOR_TIME_1');
	        str = prefix + ' ' + this.getFormattedPeriodLabel(babelHelpers.classPrivateFieldGet(this, _value), babelHelpers.classPrivateFieldGet(this, _valueType));
	        if (main_core.Type.isArray(fields)) {
	          var fieldSuffix = babelHelpers.classPrivateFieldGet(this, _type) === DelayInterval.DELAY_TYPE.After ? main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_AFTER') : main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_BEFORE_1');
	          for (var _i = 0; _i < fields.length; ++_i) {
	            if (babelHelpers.classPrivateFieldGet(this, _basis) === fields[_i].SystemExpression) {
	              str += ' ' + fieldSuffix + ' ' + fields[_i].Name;
	              break;
	            }
	          }
	        }
	      }
	      if (babelHelpers.classPrivateFieldGet(this, _workTime)) {
	        str += ', ' + main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_IN_WORKTIME');
	      }
	      return str;
	    }
	  }, {
	    key: "getFormattedPeriodLabel",
	    value: function getFormattedPeriodLabel(value, type) {
	      var label = value + ' ';
	      var labelIndex = 0;
	      if (value > 20) {
	        value = value % 10;
	      }
	      if (value === 1) {
	        labelIndex = 0;
	      } else if (value > 1 && value < 5) {
	        labelIndex = 1;
	      } else {
	        labelIndex = 2;
	      }
	      var labels = DelayInterval.getPeriodLabels(type);
	      return label + (labels ? labels[labelIndex] : '');
	    }
	  }, {
	    key: "basis",
	    get: function get() {
	      return babelHelpers.classPrivateFieldGet(this, _basis);
	    }
	  }, {
	    key: "type",
	    get: function get() {
	      return babelHelpers.classPrivateFieldGet(this, _type);
	    }
	  }, {
	    key: "value",
	    get: function get() {
	      return babelHelpers.classPrivateFieldGet(this, _value);
	    }
	  }, {
	    key: "valueType",
	    get: function get() {
	      return babelHelpers.classPrivateFieldGet(this, _valueType);
	    }
	  }, {
	    key: "workTime",
	    get: function get() {
	      return babelHelpers.classPrivateFieldGet(this, _workTime);
	    }
	  }, {
	    key: "waitWorkDay",
	    get: function get() {
	      return babelHelpers.classPrivateFieldGet(this, _waitWorkDay);
	    }
	  }, {
	    key: "inTime",
	    get: function get() {
	      return babelHelpers.classPrivateFieldGet(this, _inTime);
	    }
	  }, {
	    key: "inTimeString",
	    get: function get() {
	      if (!babelHelpers.classPrivateFieldGet(this, _inTime)) {
	        return '';
	      }
	      return ('0' + babelHelpers.classPrivateFieldGet(this, _inTime)[0]).slice(-2) + ':' + ('0' + babelHelpers.classPrivateFieldGet(this, _inTime)[1]).slice(-2);
	    }
	  }], [{
	    key: "isSystemBasis",
	    value: function isSystemBasis(basis) {
	      return basis === this.BASIS_TYPE.CurrentDate || basis === this.BASIS_TYPE.CurrentDateTime || basis === this.BASIS_TYPE.CurrentDateTimeLocal;
	    }
	  }, {
	    key: "fromString",
	    value: function fromString(intervalString, basisFields) {
	      if (!intervalString) {
	        return new DelayInterval();
	      }
	      intervalString = intervalString.toString().trimStart().replace(/^=/, '');
	      var params = {
	        basis: DelayInterval.BASIS_TYPE.CurrentDateTime,
	        workTime: false,
	        inTime: null
	      };
	      var values = {
	        i: 0,
	        h: 0,
	        d: 0
	      };
	      if (intervalString.indexOf('settime(') === 0) {
	        intervalString = intervalString.substring(8, intervalString.length - 1);
	        var setTimeArgs = intervalString.split(',');
	        var minute = parseInt(setTimeArgs.pop().trim());
	        var hour = parseInt(setTimeArgs.pop().trim());
	        params.inTime = [hour || 0, minute || 0];
	        intervalString = setTimeArgs.join(',');
	      }
	      if (intervalString.indexOf('dateadd(') === 0 || intervalString.indexOf('workdateadd(') === 0) {
	        if (intervalString.indexOf('workdateadd(') === 0) {
	          intervalString = intervalString.substring(12, intervalString.length - 1);
	          params['workTime'] = true;
	        } else {
	          intervalString = intervalString.substring(8, intervalString.length - 1);
	        }
	        var fnArgs = intervalString.split(',');
	        params['basis'] = fnArgs[0].trim();
	        fnArgs[1] = (fnArgs[1] || '').replace(/['")]+/g, '');
	        params['type'] = fnArgs[1].indexOf('-') === 0 ? DelayInterval.DELAY_TYPE.Before : DelayInterval.DELAY_TYPE.After;
	        var match;
	        var re = /s*([\d]+)\s*(i|h|d)\s*/ig;
	        while (match = re.exec(fnArgs[1])) {
	          values[match[2]] = parseInt(match[1]);
	        }
	      } else {
	        params['basis'] = intervalString;
	      }
	      if (!DelayInterval.isSystemBasis(params['basis']) && BX.type.isArray(basisFields)) {
	        var found = false;
	        for (var i = 0, s = basisFields.length; i < s; ++i) {
	          if (params['basis'] === basisFields[i].SystemExpression || params['basis'] === basisFields[i].Expression) {
	            params['basis'] = basisFields[i].SystemExpression;
	            found = true;
	            break;
	          }
	        }
	        if (!found) {
	          params['basis'] = DelayInterval.BASIS_TYPE.CurrentDateTime;
	        }
	      }
	      var minutes = values['i'] + values['h'] * 60 + values['d'] * 60 * 24;
	      if (minutes % 1440 === 0) {
	        params['value'] = minutes / 1440;
	        params['valueType'] = 'd';
	      } else if (minutes % 60 === 0) {
	        params['value'] = minutes / 60;
	        params['valueType'] = 'h';
	      } else {
	        params['value'] = minutes;
	        params['valueType'] = 'i';
	      }
	      if (!params['value'] && (params['basis'] !== DelayInterval.BASIS_TYPE.CurrentDateTime || params.inTime) && params['basis']) {
	        params['type'] = DelayInterval.DELAY_TYPE.In;
	      }
	      return new DelayInterval(params);
	    }
	  }, {
	    key: "fromMinutes",
	    value: function fromMinutes(minutes) {
	      var value;
	      var type;
	      if (minutes % 1440 === 0) {
	        value = minutes / 1440;
	        type = 'd';
	      } else if (minutes % 60 === 0) {
	        value = minutes / 60;
	        type = 'h';
	      } else {
	        value = minutes;
	        type = 'i';
	      }
	      return [value, type];
	    }
	  }, {
	    key: "toMinutes",
	    value: function toMinutes(value, valueType) {
	      var result = 0;
	      switch (valueType) {
	        case 'i':
	          result = value;
	          break;
	        case 'h':
	          result = value * 60;
	          break;
	        case 'd':
	          result = value * 60 * 24;
	          break;
	      }
	      return result;
	    }
	  }, {
	    key: "getPeriodLabels",
	    value: function getPeriodLabels(period) {
	      var labels = [];
	      if (period === 'i') {
	        labels = [main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_MIN1'), main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_MIN2'), main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_MIN3')];
	      } else if (period === 'h') {
	        labels = [main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_HOUR1'), main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_HOUR2'), main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_HOUR3')];
	      } else if (period === 'd') {
	        labels = [main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_DAY1'), main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_DAY2'), main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_DAY3')];
	      }
	      return labels;
	    }
	  }]);
	  return DelayInterval;
	}();
	babelHelpers.defineProperty(DelayInterval, "BASIS_TYPE", {
	  CurrentDate: '{=System:Date}',
	  CurrentDateTime: '{=System:Now}',
	  CurrentDateTimeLocal: '{=System:NowLocal}'
	});
	babelHelpers.defineProperty(DelayInterval, "DELAY_TYPE", {
	  After: 'after',
	  Before: 'before',
	  In: 'in'
	});

	var HelpHint = /*#__PURE__*/function () {
	  function HelpHint() {
	    babelHelpers.classCallCheck(this, HelpHint);
	  }
	  babelHelpers.createClass(HelpHint, null, [{
	    key: "bindAll",
	    value: function bindAll(node) {
	      node.querySelectorAll('[data-text]').forEach(function (element) {
	        return HelpHint.bindToNode(element);
	      });
	    }
	  }, {
	    key: "bindToNode",
	    value: function bindToNode(node) {
	      main_core.Event.bind(node, 'mouseover', this.showHint.bind(this, node));
	      main_core.Event.bind(node, 'mouseout', this.hideHint.bind(this));
	    }
	  }, {
	    key: "isBindedToNode",
	    value: function isBindedToNode(node) {
	      var _this$popupHint, _this$popupHint$bindE;
	      return !!((_this$popupHint = this.popupHint) !== null && _this$popupHint !== void 0 && (_this$popupHint$bindE = _this$popupHint.bindElement) !== null && _this$popupHint$bindE !== void 0 && _this$popupHint$bindE.isSameNode(node));
	    }
	  }, {
	    key: "showHint",
	    value: function showHint(node) {
	      var rawText = node.getAttribute('data-text');
	      if (!rawText) {
	        return;
	      }
	      var text = main_core.Text.encode(rawText);
	      text = BX.util.nl2br(text);
	      if (!main_core.Type.isStringFilled(text)) {
	        return;
	      }
	      this.hideHint();
	      this.popupHint = new BX.PopupWindow('bizproc-automation-help-tip', node, {
	        lightShadow: true,
	        autoHide: false,
	        darkMode: true,
	        offsetLeft: 0,
	        offsetTop: 2,
	        bindOptions: {
	          position: "top"
	        },
	        events: {
	          onPopupClose: function onPopupClose() {
	            this.destroy();
	          }
	        },
	        content: main_core.Dom.create('div', {
	          attrs: {
	            style: 'padding-right: 5px; width: 250px;'
	          },
	          html: text
	        })
	      });
	      this.popupHint.setAngle({
	        offset: 32,
	        position: 'bottom'
	      });
	      this.popupHint.show();
	      return true;
	    }
	  }, {
	    key: "showNoPermissionsHint",
	    value: function showNoPermissionsHint(node) {
	      this.showAngleHint(node, main_core.Loc.getMessage('BIZPROC_AUTOMATION_RIGHTS_ERROR'));
	    }
	  }, {
	    key: "showAngleHint",
	    value: function showAngleHint(node, text) {
	      if (this.timeout) {
	        clearTimeout(this.timeout);
	      }
	      this.popupHint = BX.UI.Hint.createInstance({
	        popupParameters: {
	          width: 334,
	          height: 104,
	          closeByEsc: true,
	          autoHide: true,
	          angle: {
	            offset: main_core.Dom.getPosition(node).width / 2
	          },
	          bindOptions: {
	            position: 'top'
	          }
	        }
	      });
	      this.popupHint.close = function () {
	        this.hide();
	      };
	      this.popupHint.show(node, text);
	      this.timeout = setTimeout(this.hideHint.bind(this), 5000);
	    }
	  }, {
	    key: "hideHint",
	    value: function hideHint() {
	      if (this.popupHint) {
	        this.popupHint.close();
	      }
	      this.popupHint = null;
	    }
	  }]);
	  return HelpHint;
	}();

	var WorkflowStatus = function WorkflowStatus() {
	  babelHelpers.classCallCheck(this, WorkflowStatus);
	};
	babelHelpers.defineProperty(WorkflowStatus, "CREATED", 0);
	babelHelpers.defineProperty(WorkflowStatus, "RUNNING", 1);
	babelHelpers.defineProperty(WorkflowStatus, "COMPLETED", 2);
	babelHelpers.defineProperty(WorkflowStatus, "SUSPENDED", 3);
	babelHelpers.defineProperty(WorkflowStatus, "TERMINATED", 4);

	function _classPrivateFieldInitSpec$8(obj, privateMap, value) { _checkPrivateRedeclaration$8(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$8(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	var _type$1 = /*#__PURE__*/new WeakMap();
	var _workflowStatus = /*#__PURE__*/new WeakMap();
	var TrackingEntry = /*#__PURE__*/function () {
	  function TrackingEntry() {
	    babelHelpers.classCallCheck(this, TrackingEntry);
	    _classPrivateFieldInitSpec$8(this, _type$1, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$8(this, _workflowStatus, {
	      writable: true,
	      value: void 0
	    });
	  }
	  babelHelpers.createClass(TrackingEntry, [{
	    key: "isTriggerEntry",
	    value: function isTriggerEntry() {
	      return this.type === TrackingEntry.TRIGGER_ACTIVITY_TYPE;
	    }
	  }, {
	    key: "type",
	    get: function get() {
	      return babelHelpers.classPrivateFieldGet(this, _type$1);
	    },
	    set: function set(entryType) {
	      if (TrackingEntry.getAllActivityTypes().includes(entryType)) {
	        babelHelpers.classPrivateFieldSet(this, _type$1, entryType);
	      }
	    }
	  }, {
	    key: "workflowStatus",
	    get: function get() {
	      return babelHelpers.classPrivateFieldGet(this, _workflowStatus);
	    },
	    set: function set(entryWorkflowStatus) {
	      if (TrackingEntry.getAllWorkflowStatuses().includes(entryWorkflowStatus)) {
	        babelHelpers.classPrivateFieldSet(this, _workflowStatus, entryWorkflowStatus);
	      }
	    }
	  }], [{
	    key: "getAllActivityTypes",
	    value: function getAllActivityTypes() {
	      return [TrackingEntry.UNKNOWN_ACTIVITY_TYPE, TrackingEntry.EXECUTE_ACTIVITY_TYPE, TrackingEntry.CLOSE_ACTIVITY_TYPE, TrackingEntry.CANCEL_ACTIVITY_TYPE, TrackingEntry.FAULT_ACTIVITY_TYPE, TrackingEntry.CUSTOM_ACTIVITY_TYPE, TrackingEntry.REPORT_ACTIVITY_TYPE, TrackingEntry.ATTACHED_ENTITY_TYPE, TrackingEntry.TRIGGER_ACTIVITY_TYPE, TrackingEntry.ERROR_ACTIVITY_TYPE, TrackingEntry.DEBUG_ACTIVITY_TYPE, TrackingEntry.DEBUG_AUTOMATION_TYPE, TrackingEntry.DEBUG_DESIGNER_TYPE, TrackingEntry.DEBUG_LINK_TYPE];
	    }
	  }, {
	    key: "isKnownActivityType",
	    value: function isKnownActivityType(typeId) {
	      return TrackingEntry.getAllActivityTypes().includes(typeId);
	    }
	  }, {
	    key: "getAllWorkflowStatuses",
	    value: function getAllWorkflowStatuses() {
	      return [WorkflowStatus.CREATED, WorkflowStatus.RUNNING, WorkflowStatus.COMPLETED, WorkflowStatus.SUSPENDED, WorkflowStatus.TERMINATED];
	    }
	  }, {
	    key: "isKnownWorkflowStatus",
	    value: function isKnownWorkflowStatus(statusId) {
	      return TrackingEntry.getAllWorkflowStatuses().includes(statusId);
	    }
	  }]);
	  return TrackingEntry;
	}();
	babelHelpers.defineProperty(TrackingEntry, "UNKNOWN_ACTIVITY_TYPE", 0);
	babelHelpers.defineProperty(TrackingEntry, "EXECUTE_ACTIVITY_TYPE", 1);
	babelHelpers.defineProperty(TrackingEntry, "CLOSE_ACTIVITY_TYPE", 2);
	babelHelpers.defineProperty(TrackingEntry, "CANCEL_ACTIVITY_TYPE", 3);
	babelHelpers.defineProperty(TrackingEntry, "FAULT_ACTIVITY_TYPE", 4);
	babelHelpers.defineProperty(TrackingEntry, "CUSTOM_ACTIVITY_TYPE", 5);
	babelHelpers.defineProperty(TrackingEntry, "REPORT_ACTIVITY_TYPE", 6);
	babelHelpers.defineProperty(TrackingEntry, "ATTACHED_ENTITY_TYPE", 7);
	babelHelpers.defineProperty(TrackingEntry, "TRIGGER_ACTIVITY_TYPE", 8);
	babelHelpers.defineProperty(TrackingEntry, "ERROR_ACTIVITY_TYPE", 9);
	babelHelpers.defineProperty(TrackingEntry, "DEBUG_ACTIVITY_TYPE", 10);
	babelHelpers.defineProperty(TrackingEntry, "DEBUG_AUTOMATION_TYPE", 11);
	babelHelpers.defineProperty(TrackingEntry, "DEBUG_DESIGNER_TYPE", 12);
	babelHelpers.defineProperty(TrackingEntry, "DEBUG_LINK_TYPE", 13);

	var TrackingStatus = function TrackingStatus() {
	  babelHelpers.classCallCheck(this, TrackingStatus);
	};
	babelHelpers.defineProperty(TrackingStatus, "WAITING", 0);
	babelHelpers.defineProperty(TrackingStatus, "RUNNING", 1);
	babelHelpers.defineProperty(TrackingStatus, "COMPLETED", 2);
	babelHelpers.defineProperty(TrackingStatus, "AUTOCOMPLETED", 3);

	function _createForOfIteratorHelper$2(o, allowArrayLike) { var it = typeof Symbol !== "undefined" && o[Symbol.iterator] || o["@@iterator"]; if (!it) { if (Array.isArray(o) || (it = _unsupportedIterableToArray$2(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = it.call(o); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it["return"] != null) it["return"](); } finally { if (didErr) throw err; } } }; }
	function _unsupportedIterableToArray$2(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray$2(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray$2(o, minLen); }
	function _arrayLikeToArray$2(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i]; return arr2; }
	function _classPrivateFieldInitSpec$9(obj, privateMap, value) { _checkPrivateRedeclaration$9(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$9(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	var _entryId = /*#__PURE__*/new WeakMap();
	var RobotEntry = /*#__PURE__*/function () {
	  // TODO - change string to Date when Date appear in TrackingEntry

	  function RobotEntry(entries) {
	    babelHelpers.classCallCheck(this, RobotEntry);
	    babelHelpers.defineProperty(this, "id", '');
	    babelHelpers.defineProperty(this, "status", TrackingStatus.WAITING);
	    babelHelpers.defineProperty(this, "modified", undefined);
	    babelHelpers.defineProperty(this, "notes", []);
	    babelHelpers.defineProperty(this, "errors", []);
	    _classPrivateFieldInitSpec$9(this, _entryId, {
	      writable: true,
	      value: -1
	    });
	    babelHelpers.defineProperty(this, "workflowStatus", WorkflowStatus.CREATED);
	    if (main_core.Type.isArray(entries)) {
	      var _iterator = _createForOfIteratorHelper$2(entries),
	        _step;
	      try {
	        for (_iterator.s(); !(_step = _iterator.n()).done;) {
	          var entry = _step.value;
	          this.addEntry(entry);
	        }
	      } catch (err) {
	        _iterator.e(err);
	      } finally {
	        _iterator.f();
	      }
	    }
	  }
	  babelHelpers.createClass(RobotEntry, [{
	    key: "addEntry",
	    value: function addEntry(entry) {
	      this.id = entry.name;
	      if (babelHelpers.classPrivateFieldGet(this, _entryId) < entry.id) {
	        babelHelpers.classPrivateFieldSet(this, _entryId, entry.id);
	        this.modified = entry.datetime;
	        this.workflowStatus = entry.workflowStatus;
	        if (entry.type === bizproc_automation.TrackingEntry.CLOSE_ACTIVITY_TYPE) {
	          this.status = TrackingStatus.COMPLETED;
	        } else {
	          this.status = TrackingStatus.RUNNING;
	        }
	      }
	      if (entry.type === bizproc_automation.TrackingEntry.ERROR_ACTIVITY_TYPE) {
	        this.errors.push(entry.note);
	      } else if (entry.type === bizproc_automation.TrackingEntry.CUSTOM_ACTIVITY_TYPE) {
	        this.notes.push(entry.note);
	      }
	    }
	  }]);
	  return RobotEntry;
	}();

	var TriggerEntry =
	// TODO - change string to Date when Date appear in TrackingEntry

	function TriggerEntry(entry) {
	  babelHelpers.classCallCheck(this, TriggerEntry);
	  babelHelpers.defineProperty(this, "id", '');
	  babelHelpers.defineProperty(this, "status", TrackingStatus.COMPLETED);
	  babelHelpers.defineProperty(this, "modified", undefined);
	  if (entry.isTriggerEntry()) {
	    this.id = entry.note;
	    this.modified = entry.datetime;
	  }
	};

	function _classPrivateFieldInitSpec$a(obj, privateMap, value) { _checkPrivateRedeclaration$a(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$a(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	var _defaultSettings = /*#__PURE__*/new WeakMap();
	var _entrySettings = /*#__PURE__*/new WeakMap();
	var TrackingEntryBuilder = /*#__PURE__*/function () {
	  function TrackingEntryBuilder() {
	    babelHelpers.classCallCheck(this, TrackingEntryBuilder);
	    _classPrivateFieldInitSpec$a(this, _defaultSettings, {
	      writable: true,
	      value: {
	        id: TrackingEntry.UNKNOWN_ACTIVITY_TYPE,
	        workflowId: '',
	        type: TrackingEntry.EXECUTE_ACTIVITY_TYPE,
	        name: '',
	        title: '',
	        datetime: '',
	        note: '',
	        workflowStatus: WorkflowStatus.CREATED
	      }
	    });
	    _classPrivateFieldInitSpec$a(this, _entrySettings, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldSet(this, _entrySettings, babelHelpers.classPrivateFieldGet(this, _defaultSettings));
	  }
	  babelHelpers.createClass(TrackingEntryBuilder, [{
	    key: "setLogEntry",
	    value: function setLogEntry(logEntry) {
	      babelHelpers.classPrivateFieldSet(this, _entrySettings, Object.assign({}, babelHelpers.classPrivateFieldGet(this, _defaultSettings)));
	      logEntry = Object.assign({}, logEntry);
	      if (main_core.Type.isStringFilled(logEntry['ID'])) {
	        logEntry['ID'] = parseInt(logEntry['ID']);
	      }
	      if (main_core.Type.isStringFilled(logEntry['TYPE'])) {
	        logEntry['TYPE'] = parseInt(logEntry['TYPE']);
	      }
	      if (main_core.Type.isNumber(logEntry['ID'])) {
	        babelHelpers.classPrivateFieldGet(this, _entrySettings).id = logEntry['ID'];
	      }
	      if (main_core.Type.isStringFilled(logEntry['WORKFLOW_ID'])) {
	        babelHelpers.classPrivateFieldGet(this, _entrySettings).workflowId = logEntry['WORKFLOW_ID'];
	      }
	      if (main_core.Type.isNumber(logEntry['TYPE']) && TrackingEntry.isKnownActivityType(logEntry['TYPE'])) {
	        babelHelpers.classPrivateFieldGet(this, _entrySettings).type = logEntry['TYPE'];
	      }
	      if (main_core.Type.isStringFilled(logEntry['MODIFIED'])) {
	        babelHelpers.classPrivateFieldGet(this, _entrySettings).datetime = logEntry['MODIFIED'];
	      }
	      if (main_core.Type.isNumber(logEntry['WORKFLOW_STATUS']) && TrackingEntry.isKnownWorkflowStatus(logEntry['WORKFLOW_STATUS'])) {
	        babelHelpers.classPrivateFieldGet(this, _entrySettings).workflowStatus = logEntry['WORKFLOW_STATUS'];
	      }
	      babelHelpers.classPrivateFieldGet(this, _entrySettings).name = String(logEntry['ACTION_NAME']);
	      babelHelpers.classPrivateFieldGet(this, _entrySettings).title = String(logEntry['ACTION_TITLE']);
	      babelHelpers.classPrivateFieldGet(this, _entrySettings).note = String(logEntry['ACTION_NOTE']);
	      return this;
	    }
	  }, {
	    key: "setStatus",
	    value: function setStatus(status) {
	      babelHelpers.classPrivateFieldGet(this, _entrySettings).status = status;
	      return this;
	    }
	  }, {
	    key: "build",
	    value: function build() {
	      var entry = new TrackingEntry();
	      entry.id = babelHelpers.classPrivateFieldGet(this, _entrySettings).id;
	      entry.workflowId = babelHelpers.classPrivateFieldGet(this, _entrySettings).workflowId;
	      entry.type = babelHelpers.classPrivateFieldGet(this, _entrySettings).type;
	      entry.name = babelHelpers.classPrivateFieldGet(this, _entrySettings).name;
	      entry.title = babelHelpers.classPrivateFieldGet(this, _entrySettings).title;
	      entry.note = babelHelpers.classPrivateFieldGet(this, _entrySettings).note;
	      entry.datetime = babelHelpers.classPrivateFieldGet(this, _entrySettings).datetime;
	      entry.workflowStatus = babelHelpers.classPrivateFieldGet(this, _entrySettings).workflowStatus;
	      return entry;
	    }
	  }]);
	  return TrackingEntryBuilder;
	}();

	function _createForOfIteratorHelper$3(o, allowArrayLike) { var it = typeof Symbol !== "undefined" && o[Symbol.iterator] || o["@@iterator"]; if (!it) { if (Array.isArray(o) || (it = _unsupportedIterableToArray$3(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = it.call(o); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it["return"] != null) it["return"](); } finally { if (didErr) throw err; } } }; }
	function _unsupportedIterableToArray$3(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray$3(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray$3(o, minLen); }
	function _arrayLikeToArray$3(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i]; return arr2; }
	function _classPrivateFieldInitSpec$b(obj, privateMap, value) { _checkPrivateRedeclaration$b(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$b(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	var _ajaxUrl = /*#__PURE__*/new WeakMap();
	var _document = /*#__PURE__*/new WeakMap();
	var _triggerLogs = /*#__PURE__*/new WeakMap();
	var _robotLogs = /*#__PURE__*/new WeakMap();
	var Tracker = /*#__PURE__*/function () {
	  function Tracker(document, ajaxUrl) {
	    babelHelpers.classCallCheck(this, Tracker);
	    _classPrivateFieldInitSpec$b(this, _ajaxUrl, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$b(this, _document, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$b(this, _triggerLogs, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$b(this, _robotLogs, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldSet(this, _ajaxUrl, ajaxUrl);
	    babelHelpers.classPrivateFieldSet(this, _document, document);
	  }
	  babelHelpers.createClass(Tracker, [{
	    key: "init",
	    value: function init(log) {
	      babelHelpers.classPrivateFieldSet(this, _triggerLogs, {});
	      babelHelpers.classPrivateFieldSet(this, _robotLogs, {});
	      this.addLogs(log);
	    }
	  }, {
	    key: "reInit",
	    value: function reInit(log) {
	      this.init(log);
	    }
	  }, {
	    key: "addLogs",
	    value: function addLogs(log) {
	      if (!main_core.Type.isPlainObject(log)) {
	        log = {};
	      }
	      var logEntryBuilder = new TrackingEntryBuilder();
	      for (var _i = 0, _Object$entries = Object.entries(log); _i < _Object$entries.length; _i++) {
	        var _Object$entries$_i = babelHelpers.slicedToArray(_Object$entries[_i], 2),
	          statusId = _Object$entries$_i[0],
	          entries = _Object$entries$_i[1];
	        if (!main_core.Type.isArray(entries)) {
	          continue;
	        }
	        var _iterator = _createForOfIteratorHelper$3(entries),
	          _step;
	        try {
	          for (_iterator.s(); !(_step = _iterator.n()).done;) {
	            var rawEntry = _step.value;
	            var entry = logEntryBuilder.setLogEntry(rawEntry).build();
	            if (entry.isTriggerEntry()) {
	              this.addTriggerEntry(entry);
	            } else {
	              this.addRobotEntry(entry);
	              var robotEntry = babelHelpers.classPrivateFieldGet(this, _robotLogs)[entry.name];
	              if (!main_core.Type.isNil(babelHelpers.classPrivateFieldGet(this, _document))) {
	                var isRobotRunning = robotEntry.status === TrackingStatus.RUNNING;
	                var isWorkflowCompleted = robotEntry.workflowStatus === WorkflowStatus.COMPLETED;
	                var isCurrentStatus = babelHelpers.classPrivateFieldGet(this, _document).getCurrentStatusId() === statusId;
	                var isRobotRunningAtAnotherStatus = isRobotRunning && !isCurrentStatus;
	                var isRobotRunningAndCurrentWorkflowCompleted = isRobotRunning && isWorkflowCompleted && isCurrentStatus;
	                if (isRobotRunningAtAnotherStatus || isRobotRunningAndCurrentWorkflowCompleted) {
	                  robotEntry.status = TrackingStatus.COMPLETED;
	                }
	              }
	            }
	          }
	        } catch (err) {
	          _iterator.e(err);
	        } finally {
	          _iterator.f();
	        }
	      }
	    }
	  }, {
	    key: "addTriggerEntry",
	    value: function addTriggerEntry(entry) {
	      if (entry.isTriggerEntry()) {
	        babelHelpers.classPrivateFieldGet(this, _triggerLogs)[entry.note] = new TriggerEntry(entry);
	      }
	    }
	  }, {
	    key: "addRobotEntry",
	    value: function addRobotEntry(entry) {
	      if (entry.isTriggerEntry()) {
	        return;
	      }
	      if (!babelHelpers.classPrivateFieldGet(this, _robotLogs)[entry.name]) {
	        babelHelpers.classPrivateFieldGet(this, _robotLogs)[entry.name] = new RobotEntry([entry]);
	      } else {
	        babelHelpers.classPrivateFieldGet(this, _robotLogs)[entry.name].addEntry(entry);
	      }
	    }
	  }, {
	    key: "getRobotLog",
	    value: function getRobotLog(id) {
	      return babelHelpers.classPrivateFieldGet(this, _robotLogs)[id] || null;
	    }
	  }, {
	    key: "getTriggerLog",
	    value: function getTriggerLog(id) {
	      return babelHelpers.classPrivateFieldGet(this, _triggerLogs)[id] || null;
	    }
	  }, {
	    key: "update",
	    value: function update(documentSigned) {
	      var _this = this;
	      return BX.ajax({
	        method: 'POST',
	        dataType: 'json',
	        url: babelHelpers.classPrivateFieldGet(this, _ajaxUrl),
	        data: {
	          ajax_action: 'get_log',
	          document_signed: documentSigned
	        },
	        onsuccess: function onsuccess(response) {
	          if (response.DATA && response.DATA.LOG) {
	            _this.reInit(response.DATA.LOG);
	          }
	        }
	      });
	    }
	  }]);
	  return Tracker;
	}();

	function _createForOfIteratorHelper$4(o, allowArrayLike) { var it = typeof Symbol !== "undefined" && o[Symbol.iterator] || o["@@iterator"]; if (!it) { if (Array.isArray(o) || (it = _unsupportedIterableToArray$4(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = it.call(o); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it["return"] != null) it["return"](); } finally { if (didErr) throw err; } } }; }
	function _unsupportedIterableToArray$4(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray$4(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray$4(o, minLen); }
	function _arrayLikeToArray$4(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i]; return arr2; }
	function ownKeys$1(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread$1(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys$1(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys$1(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	function _classPrivateFieldInitSpec$c(obj, privateMap, value) { _checkPrivateRedeclaration$c(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$c(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	var _data$1 = /*#__PURE__*/new WeakMap();
	var _document$1 = /*#__PURE__*/new WeakMap();
	var _template = /*#__PURE__*/new WeakMap();
	var _tracker = /*#__PURE__*/new WeakMap();
	var _delay = /*#__PURE__*/new WeakMap();
	var _node$1 = /*#__PURE__*/new WeakMap();
	var _condition$1 = /*#__PURE__*/new WeakMap();
	var _isDraft = /*#__PURE__*/new WeakMap();
	var _isFrameMode = /*#__PURE__*/new WeakMap();
	var _viewMode$2 = /*#__PURE__*/new WeakMap();
	var Robot = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(Robot, _EventEmitter);
	  function Robot(params) {
	    var _this;
	    babelHelpers.classCallCheck(this, Robot);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Robot).call(this));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "SYSTEM_EXPRESSION_PATTERN", '\\{=\\s*(?<object>[a-z0-9_]+)\\s*\\:\\s*(?<field>[a-z0-9_\\.]+)(\\s*>\\s*(?<mod1>[a-z0-9_\\:]+)(\\s*,\\s*(?<mod2>[a-z0-9_]+))?)?\\s*\\}');
	    _classPrivateFieldInitSpec$c(babelHelpers.assertThisInitialized(_this), _data$1, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$c(babelHelpers.assertThisInitialized(_this), _document$1, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$c(babelHelpers.assertThisInitialized(_this), _template, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$c(babelHelpers.assertThisInitialized(_this), _tracker, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$c(babelHelpers.assertThisInitialized(_this), _delay, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$c(babelHelpers.assertThisInitialized(_this), _node$1, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$c(babelHelpers.assertThisInitialized(_this), _condition$1, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$c(babelHelpers.assertThisInitialized(_this), _isDraft, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$c(babelHelpers.assertThisInitialized(_this), _isFrameMode, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$c(babelHelpers.assertThisInitialized(_this), _viewMode$2, {
	      writable: true,
	      value: void 0
	    });
	    _this.setEventNamespace('BX.Bizproc.Automation');
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _document$1, params.document);
	    if (!main_core.Type.isNil(params.template)) {
	      babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _template, params.template);
	    }
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _isFrameMode, params.isFrameMode);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _viewMode$2, ViewMode.none());
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _tracker, params.tracker);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _isDraft, false);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _delay, new DelayInterval());
	    return _this;
	  }
	  babelHelpers.createClass(Robot, [{
	    key: "hasTemplate",
	    value: function hasTemplate() {
	      return !main_core.Type.isNil(babelHelpers.classPrivateFieldGet(this, _template));
	    }
	  }, {
	    key: "getTemplate",
	    value: function getTemplate() {
	      return babelHelpers.classPrivateFieldGet(this, _template);
	    }
	  }, {
	    key: "getDocument",
	    value: function getDocument() {
	      return babelHelpers.classPrivateFieldGet(this, _document$1);
	    }
	  }, {
	    key: "clone",
	    value: function clone() {
	      var clonedRobot = new Robot({
	        document: babelHelpers.classPrivateFieldGet(this, _document$1),
	        template: babelHelpers.classPrivateFieldGet(this, _template),
	        isFrameMode: babelHelpers.classPrivateFieldGet(this, _isFrameMode),
	        tracker: babelHelpers.classPrivateFieldGet(this, _tracker)
	      });
	      var robotData = _objectSpread$1(_objectSpread$1({}, BX.clone(babelHelpers.classPrivateFieldGet(this, _data$1))), {}, {
	        Name: Robot.generateName(),
	        Delay: this.getDelayInterval().serialize(),
	        Condition: this.getCondition().serialize()
	      });
	      clonedRobot.init(robotData, babelHelpers.classPrivateFieldGet(this, _viewMode$2));
	      return clonedRobot;
	    }
	  }, {
	    key: "isEqual",
	    value: function isEqual(other) {
	      return babelHelpers.classPrivateFieldGet(this, _data$1).Name === babelHelpers.classPrivateFieldGet(other, _data$1).Name;
	    }
	  }, {
	    key: "init",
	    value: function init(data, viewMode) {
	      if (main_core.Type.isPlainObject(data)) {
	        babelHelpers.classPrivateFieldSet(this, _data$1, Object.assign({}, data));
	      }
	      if (!babelHelpers.classPrivateFieldGet(this, _data$1).Name) {
	        babelHelpers.classPrivateFieldGet(this, _data$1).Name = Robot.generateName();
	      }
	      babelHelpers.classPrivateFieldSet(this, _delay, new DelayInterval(babelHelpers.classPrivateFieldGet(this, _data$1).Delay));
	      babelHelpers.classPrivateFieldSet(this, _condition$1, new bizproc_automation.ConditionGroup(babelHelpers.classPrivateFieldGet(this, _data$1).Condition));
	      if (!babelHelpers.classPrivateFieldGet(this, _data$1).Condition) {
	        babelHelpers.classPrivateFieldGet(this, _condition$1).type = bizproc_automation.ConditionGroup.CONDITION_TYPE.Mixed;
	      }
	      delete babelHelpers.classPrivateFieldGet(this, _data$1).Condition;
	      delete babelHelpers.classPrivateFieldGet(this, _data$1).Delay;
	      babelHelpers.classPrivateFieldSet(this, _viewMode$2, main_core.Type.isNil(viewMode) ? ViewMode.edit() : viewMode);
	      if (!babelHelpers.classPrivateFieldGet(this, _viewMode$2).isNone()) {
	        babelHelpers.classPrivateFieldSet(this, _node$1, this.createNode());
	      }
	    }
	  }, {
	    key: "reInit",
	    value: function reInit(data, viewMode) {
	      if (main_core.Type.isNil(viewMode) && babelHelpers.classPrivateFieldGet(this, _viewMode$2).isNone()) {
	        return;
	      }
	      var node = babelHelpers.classPrivateFieldGet(this, _node$1);
	      babelHelpers.classPrivateFieldSet(this, _node$1, this.createNode());
	      if (node.parentNode) {
	        node.parentNode.replaceChild(babelHelpers.classPrivateFieldGet(this, _node$1), node);
	      }
	    }
	  }, {
	    key: "destroy",
	    value: function destroy() {
	      main_core.Dom.remove(babelHelpers.classPrivateFieldGet(this, _node$1));
	      this.emit('Robot:destroyed');
	    }
	  }, {
	    key: "canEdit",
	    value: function canEdit() {
	      return babelHelpers.classPrivateFieldGet(this, _template).canEdit();
	    }
	  }, {
	    key: "getProperties",
	    value: function getProperties() {
	      if (babelHelpers.classPrivateFieldGet(this, _data$1) && main_core.Type.isPlainObject(babelHelpers.classPrivateFieldGet(this, _data$1).Properties)) {
	        return babelHelpers.classPrivateFieldGet(this, _data$1).Properties;
	      }
	      return {};
	    }
	  }, {
	    key: "getProperty",
	    value: function getProperty(name) {
	      return this.getProperties()[name] || null;
	    }
	  }, {
	    key: "hasProperty",
	    value: function hasProperty(name) {
	      return this.getProperties().hasOwnProperty(name);
	    }
	  }, {
	    key: "setProperty",
	    value: function setProperty(name, value) {
	      babelHelpers.classPrivateFieldGet(this, _data$1).Properties[name] = value;
	      return this;
	    }
	  }, {
	    key: "getId",
	    value: function getId() {
	      return babelHelpers.classPrivateFieldGet(this, _data$1).Name || null;
	    }
	  }, {
	    key: "getLogStatus",
	    value: function getLogStatus() {
	      var status = TrackingStatus.WAITING;
	      var log = babelHelpers.classPrivateFieldGet(this, _tracker).getRobotLog(this.getId());
	      if (log) {
	        status = log.status;
	      } else if (babelHelpers.classPrivateFieldGet(this, _data$1).DelayName) {
	        log = babelHelpers.classPrivateFieldGet(this, _tracker).getRobotLog(babelHelpers.classPrivateFieldGet(this, _data$1).DelayName);
	        if (log && log.status === TrackingStatus.RUNNING) {
	          status = TrackingStatus.RUNNING;
	        }
	      }
	      return status;
	    }
	  }, {
	    key: "getLogErrors",
	    value: function getLogErrors() {
	      var errors = [];
	      var log = babelHelpers.classPrivateFieldGet(this, _tracker).getRobotLog(this.getId());
	      if (log && log.errors) {
	        errors = log.errors;
	      }
	      return errors;
	    }
	  }, {
	    key: "getDelayNotes",
	    value: function getDelayNotes() {
	      if (babelHelpers.classPrivateFieldGet(this, _data$1).DelayName) {
	        var log = babelHelpers.classPrivateFieldGet(this, _tracker).getRobotLog(babelHelpers.classPrivateFieldGet(this, _data$1).DelayName);
	        if (log && log.status === TrackingStatus.RUNNING) {
	          return log.notes;
	        }
	      }
	      return [];
	    }
	  }, {
	    key: "selectNode",
	    value: function selectNode() {
	      if (babelHelpers.classPrivateFieldGet(this, _node$1)) {
	        main_core.Dom.addClass(babelHelpers.classPrivateFieldGet(this, _node$1), '--selected');
	        this.emit('Robot:selected');
	      }
	    }
	  }, {
	    key: "unselectNode",
	    value: function unselectNode() {
	      if (babelHelpers.classPrivateFieldGet(this, _node$1)) {
	        main_core.Dom.removeClass(babelHelpers.classPrivateFieldGet(this, _node$1), '--selected');
	        this.emit('Robot:unselected');
	      }
	    }
	  }, {
	    key: "isSelected",
	    value: function isSelected() {
	      return babelHelpers.classPrivateFieldGet(this, _node$1) && main_core.Dom.hasClass(babelHelpers.classPrivateFieldGet(this, _node$1), '--selected');
	    }
	  }, {
	    key: "enableManageMode",
	    value: function enableManageMode(isActive) {
	      var _this2 = this;
	      babelHelpers.classPrivateFieldSet(this, _viewMode$2, ViewMode.manage().setProperty('isActive', isActive));
	      if (!isActive) {
	        main_core.Dom.addClass(babelHelpers.classPrivateFieldGet(this, _node$1), '--locked-node');
	      }
	      var deleteButton = babelHelpers.classPrivateFieldGet(this, _node$1).querySelector('.bizproc-automation-robot-btn-delete');
	      main_core.Dom.hide(deleteButton);
	      babelHelpers.classPrivateFieldGet(this, _node$1).onclick = function () {
	        if (!babelHelpers.classPrivateFieldGet(_this2, _viewMode$2).isManage() || !babelHelpers.classPrivateFieldGet(_this2, _viewMode$2).getProperty('isActive', false)) {
	          return;
	        }
	        if (!_this2.isSelected()) {
	          _this2.selectNode();
	        } else {
	          _this2.unselectNode();
	        }
	      };
	    }
	  }, {
	    key: "disableManageMode",
	    value: function disableManageMode() {
	      babelHelpers.classPrivateFieldSet(this, _viewMode$2, ViewMode.edit());
	      this.unselectNode();
	      main_core.Dom.removeClass(babelHelpers.classPrivateFieldGet(this, _node$1), '--locked-node');
	      var deleteButton = babelHelpers.classPrivateFieldGet(this, _node$1).querySelector('.bizproc-automation-robot-btn-delete');
	      main_core.Dom.show(deleteButton);
	      babelHelpers.classPrivateFieldGet(this, _node$1).onclick = undefined;
	    }
	  }, {
	    key: "createNode",
	    value: function createNode() {
	      var _this3 = this;
	      var wrapperClass = 'bizproc-automation-robot-container-wrapper';
	      var containerClass = 'bizproc-automation-robot-container';
	      if (babelHelpers.classPrivateFieldGet(this, _viewMode$2).isEdit() && this.canEdit()) {
	        wrapperClass += ' bizproc-automation-robot-container-wrapper-draggable';
	      }
	      if (this.draft) {
	        containerClass += ' --draft';
	      }
	      var targetLabel = main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_TO');
	      var targetNode = main_core.Dom.create("a", {
	        attrs: {
	          className: "bizproc-automation-robot-settings-name " + (babelHelpers.classPrivateFieldGet(this, _viewMode$2).isView() ? '--mode-view' : ''),
	          title: main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_AUTOMATICALLY')
	        },
	        text: main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_AUTOMATICALLY')
	      });
	      if (main_core.Type.isPlainObject(babelHelpers.classPrivateFieldGet(this, _data$1).viewData) && babelHelpers.classPrivateFieldGet(this, _data$1).viewData.responsibleLabel) {
	        var labelText = babelHelpers.classPrivateFieldGet(this, _data$1).viewData.responsibleLabel.replace('{=Document:ASSIGNED_BY_ID}', main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_RESPONSIBLE')).replace('author', main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_RESPONSIBLE')).replace(/\{=Constant\:Constant[0-9]+\}/, main_core.Loc.getMessage('BIZPROC_AUTOMATION_ASK_CONSTANT')).replace(/\{\{~&\:Constant[0-9]+\}\}/, main_core.Loc.getMessage('BIZPROC_AUTOMATION_ASK_CONSTANT')).replace(/\{=Template\:Parameter[0-9]+\}/, main_core.Loc.getMessage('BIZPROC_AUTOMATION_ASK_PARAMETER')).replace(/\{\{~&:\:Parameter[0-9]+\}\}/, main_core.Loc.getMessage('BIZPROC_AUTOMATION_ASK_PARAMETER'));
	        if (labelText.indexOf('{=Document') >= 0) {
	          babelHelpers.classPrivateFieldGet(this, _document$1).getFields().forEach(function (field) {
	            labelText = labelText.replace(field['SystemExpression'], field['Name']);
	          });
	        }
	        if (labelText.indexOf('{=A') >= 0) {
	          babelHelpers.classPrivateFieldGet(this, _template).robots.forEach(function (robot) {
	            robot.getReturnFieldsDescription().forEach(function (field) {
	              if (field['Type'] === 'user') {
	                labelText = labelText.replace(field['SystemExpression'], robot.getTitle() + ': ' + field['Name']);
	              }
	            });
	          });
	        }
	        if (labelText.indexOf('{=GlobalVar:') >= 0 && main_core.Type.isArrayFilled(babelHelpers.classPrivateFieldGet(this, _template).globalVariables)) {
	          babelHelpers.classPrivateFieldGet(this, _template).globalVariables.forEach(function (variable) {
	            labelText = labelText.replace(variable.SystemExpression, variable.Name);
	          });
	        }
	        if (labelText.indexOf('{=GlobalConst:') >= 0 && main_core.Type.isArrayFilled(babelHelpers.classPrivateFieldGet(this, _template).globalConstants)) {
	          babelHelpers.classPrivateFieldGet(this, _template).globalConstants.forEach(function (constant) {
	            labelText = labelText.replace(constant.SystemExpression, constant.Name);
	          });
	        }
	        targetNode.textContent = labelText;
	        targetNode.setAttribute('title', labelText);
	        if (babelHelpers.classPrivateFieldGet(this, _data$1).viewData.responsibleUrl) {
	          targetNode.href = babelHelpers.classPrivateFieldGet(this, _data$1).viewData.responsibleUrl;
	          if (babelHelpers.classPrivateFieldGet(this, _isFrameMode)) {
	            targetNode.setAttribute('target', '_blank');
	          }
	        }
	        if (babelHelpers.classPrivateFieldGet(this, _viewMode$2).isEdit() && parseInt(babelHelpers.classPrivateFieldGet(this, _data$1).viewData.responsibleId) > 0) {
	          targetNode.setAttribute('bx-tooltip-user-id', babelHelpers.classPrivateFieldGet(this, _data$1).viewData.responsibleId);
	        }
	      }
	      var delayLabel = this.getDelayInterval().format(main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_AT_ONCE'), babelHelpers.classPrivateFieldGet(this, _document$1).getFields());
	      if (this.isExecuteAfterPrevious()) {
	        delayLabel = delayLabel !== main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_AT_ONCE') ? delayLabel + ', ' : '';
	        delayLabel += main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_AFTER_PREVIOUS');
	      }
	      if (this.getCondition().items.length > 0) {
	        delayLabel += ', ' + main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_BY_CONDITION');
	      }
	      var delayNode = main_core.Dom.create(babelHelpers.classPrivateFieldGet(this, _viewMode$2).isEdit() ? "a" : "span", {
	        attrs: {
	          className: babelHelpers.classPrivateFieldGet(this, _viewMode$2).isEdit() ? 'bizproc-automation-robot-link' : 'bizproc-automation-robot-text',
	          title: delayLabel
	        },
	        text: delayLabel
	      });
	      var statusNode = main_core.Dom.create("div", {
	        attrs: {
	          className: "bizproc-automation-robot-information"
	        }
	      });
	      this.subscribeOnce('Robot:destroyed', function () {
	        if (HelpHint.isBindedToNode(statusNode)) {
	          HelpHint.hideHint();
	        }
	      });
	      switch (this.getLogStatus()) {
	        case TrackingStatus.RUNNING:
	          if (babelHelpers.classPrivateFieldGet(this, _document$1).getCurrentStatusId() === babelHelpers.classPrivateFieldGet(this, _template).getStatusId()) {
	            statusNode.classList.add('--loader');
	            var delayNotes = this.getDelayNotes();
	            if (delayNotes.length) {
	              statusNode.setAttribute('data-text', delayNotes.join('\n'));
	              HelpHint.bindToNode(statusNode);
	            }
	          }
	          break;
	        case TrackingStatus.COMPLETED:
	        case TrackingStatus.AUTOCOMPLETED:
	          containerClass += ' --complete';
	          statusNode.classList.add('--complete');
	          break;
	      }
	      var errors = this.getLogErrors();
	      if (errors.length > 0) {
	        statusNode.classList.add('--errors');
	        statusNode.setAttribute('data-text', errors.join('\n'));
	        HelpHint.bindToNode(statusNode);
	      }
	      var titleClassName = 'bizproc-automation-robot-title-text';
	      if (babelHelpers.classPrivateFieldGet(this, _viewMode$2).isEdit() && this.canEdit()) {
	        titleClassName += ' bizproc-automation-robot-title-text-editable';
	      }
	      var div = main_core.Dom.create("div", {
	        attrs: {
	          className: containerClass,
	          'data-role': 'robot-container',
	          'data-type': 'item-robot',
	          'data-id': this.getId()
	        },
	        children: [main_core.Dom.create("div", {
	          props: {
	            className: "bizproc-automation-robot-container-checkbox"
	          }
	        }), main_core.Dom.create('div', {
	          attrs: {
	            className: wrapperClass
	          },
	          children: [main_core.Dom.create("div", {
	            attrs: {
	              className: "bizproc-automation-robot-deadline"
	            },
	            children: [delayNode]
	          }), main_core.Dom.create("div", {
	            attrs: {
	              className: "bizproc-automation-robot-title"
	            },
	            children: [main_core.Dom.create("div", {
	              attrs: {
	                className: titleClassName,
	                title: this.getTitle()
	              },
	              html: this.clipTitle(this.getTitle()),
	              events: {
	                click: function click(event) {
	                  if (babelHelpers.classPrivateFieldGet(_this3, _viewMode$2).isEdit() && _this3.canEdit() && !babelHelpers.classPrivateFieldGet(_this3, _viewMode$2).isManage()) {
	                    _this3.onTitleEditClick(event);
	                  }
	                }
	              }
	            })]
	          }), main_core.Dom.create("div", {
	            attrs: {
	              className: "bizproc-automation-robot-settings"
	            },
	            children: [main_core.Dom.create("div", {
	              attrs: {
	                className: "bizproc-automation-robot-settings-title"
	              },
	              text: targetLabel + ':'
	            }), targetNode]
	          }), statusNode]
	        })]
	      });
	      if (this.canEdit()) {
	        this.registerItem(div);
	      }
	      if (babelHelpers.classPrivateFieldGet(this, _viewMode$2).isEdit()) {
	        var deleteBtn = main_core.Dom.create('SPAN', {
	          attrs: {
	            className: 'bizproc-automation-robot-btn-delete'
	          }
	        });
	        main_core.Event.bind(deleteBtn, 'click', this.onDeleteButtonClick.bind(this, deleteBtn));
	        div.lastChild.appendChild(deleteBtn);
	        var copyBtn = main_core.Dom.create('div', {
	          attrs: {
	            className: 'bizproc-automation-robot-btn-copy'
	          },
	          text: main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_COPY') || 'copy'
	        });
	        main_core.Event.bind(copyBtn, 'click', this.onCopyButtonClick.bind(this, copyBtn));
	        div.appendChild(copyBtn);
	        var settingsBtn = main_core.Dom.create('div', {
	          attrs: {
	            className: 'bizproc-automation-robot-btn-settings'
	          },
	          text: main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_EDIT')
	        });
	        main_core.Event.bind(div, 'click', this.onSettingsButtonClick.bind(this, div));
	        div.appendChild(settingsBtn);
	      }
	      return div;
	    }
	  }, {
	    key: "onDeleteButtonClick",
	    value: function onDeleteButtonClick(button, event) {
	      event.stopPropagation();
	      if (!this.canEdit()) {
	        HelpHint.showNoPermissionsHint(button);
	      } else if (!babelHelpers.classPrivateFieldGet(this, _viewMode$2).isManage()) {
	        main_core.Dom.remove(babelHelpers.classPrivateFieldGet(this, _node$1));
	        babelHelpers.classPrivateFieldGet(this, _template).deleteRobot(this);
	      }
	    }
	  }, {
	    key: "onSettingsButtonClick",
	    value: function onSettingsButtonClick(button) {
	      if (!this.canEdit()) {
	        HelpHint.showNoPermissionsHint(button);
	      } else if (!babelHelpers.classPrivateFieldGet(this, _viewMode$2).isManage()) {
	        var _babelHelpers$classPr;
	        babelHelpers.classPrivateFieldGet(this, _template).openRobotSettingsDialog(this, (_babelHelpers$classPr = babelHelpers.classPrivateFieldGet(this, _data$1).DialogContext) !== null && _babelHelpers$classPr !== void 0 ? _babelHelpers$classPr : null);
	      }
	    }
	  }, {
	    key: "onCopyButtonClick",
	    value: function onCopyButtonClick(button, event) {
	      event.stopPropagation();
	      if (!this.canEdit()) {
	        HelpHint.showNoPermissionsHint(button);
	      } else if (!babelHelpers.classPrivateFieldGet(this, _viewMode$2).isManage()) {
	        var copiedRobot = this.clone();
	        var robotTitle = copiedRobot.getProperty('Title');
	        if (!main_core.Type.isNil(robotTitle)) {
	          var newTitle = robotTitle + ' ' + ' ' + main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_COPY_CAPTION');
	          copiedRobot.setProperty('Title', newTitle);
	          copiedRobot.reInit();
	        }
	        Template.copyRobotTo(babelHelpers.classPrivateFieldGet(this, _template), copiedRobot, babelHelpers.classPrivateFieldGet(this, _template).getNextRobot(this));
	      }
	    }
	  }, {
	    key: "onTitleEditClick",
	    value: function onTitleEditClick(e) {
	      e.preventDefault();
	      e.stopPropagation();
	      var formName = 'bizproc_automation_robot_title_dialog';
	      var form = main_core.Dom.create('form', {
	        props: {
	          name: formName
	        },
	        style: {
	          "min-width": '540px'
	        }
	      });
	      form.appendChild(main_core.Dom.create("span", {
	        attrs: {
	          className: "bizproc-automation-popup-settings-title bizproc-automation-popup-settings-title-autocomplete"
	        },
	        text: main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_ROBOT_NAME') + ':'
	      }));
	      form.appendChild(main_core.Dom.create("div", {
	        attrs: {
	          className: "bizproc-automation-popup-settings"
	        },
	        children: [BX.create("input", {
	          attrs: {
	            className: 'bizproc-automation-popup-input',
	            type: "text",
	            name: "name",
	            value: this.getTitle()
	          }
	        })]
	      }));
	      this.emit('Robot:title:editStart');
	      var self = this;
	      var popup = new BX.PopupWindow(bizproc_automation.Helper.generateUniqueId(), null, {
	        titleBar: main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_ROBOT_NAME'),
	        content: form,
	        closeIcon: true,
	        offsetLeft: 0,
	        offsetTop: 0,
	        closeByEsc: true,
	        draggable: {
	          restrict: false
	        },
	        overlay: false,
	        events: {
	          onPopupClose: function onPopupClose(popup) {
	            popup.destroy();
	            self.emit('Robot:title:editCompleted');
	          }
	        },
	        buttons: [new BX.PopupWindowButton({
	          text: main_core.Loc.getMessage('JS_CORE_WINDOW_SAVE'),
	          className: "popup-window-button-accept",
	          events: {
	            click: function click() {
	              var nameNode = form.elements.name;
	              self.setProperty('Title', nameNode.value);
	              self.reInit();
	              babelHelpers.classPrivateFieldGet(self, _template).markModified();
	              this.popupWindow.close();
	            }
	          }
	        }), new BX.PopupWindowButtonLink({
	          text: main_core.Loc.getMessage('JS_CORE_WINDOW_CANCEL'),
	          className: "popup-window-button-link-cancel",
	          events: {
	            click: function click() {
	              this.popupWindow.close();
	            }
	          }
	        })]
	      });
	      popup.show();
	    }
	  }, {
	    key: "onSearch",
	    value: function onSearch(event) {
	      if (!babelHelpers.classPrivateFieldGet(this, _node$1)) {
	        return;
	      }
	      var query = event.getData().queryString;
	      var match = !query || this.getTitle().toLowerCase().indexOf(query) >= 0;
	      if (match) {
	        main_core.Dom.removeClass(babelHelpers.classPrivateFieldGet(this, _node$1), '--search-mismatch');
	      } else {
	        main_core.Dom.addClass(babelHelpers.classPrivateFieldGet(this, _node$1), '--search-mismatch');
	      }
	    }
	  }, {
	    key: "clipTitle",
	    value: function clipTitle(fullTitle) {
	      var title = main_core.Text.encode(fullTitle);
	      var arrTitle = title.split(" ");
	      var lastWord = "<span>" + arrTitle[arrTitle.length - 1] + "</span>";
	      arrTitle.splice(arrTitle.length - 1);
	      title = arrTitle.join(" ") + " " + lastWord;
	      return title;
	    }
	  }, {
	    key: "updateData",
	    value: function updateData(data) {
	      if (main_core.Type.isPlainObject(data)) {
	        babelHelpers.classPrivateFieldSet(this, _data$1, data);
	      } else {
	        throw 'Invalid data';
	      }
	    }
	  }, {
	    key: "serialize",
	    value: function serialize() {
	      var result = BX.clone(babelHelpers.classPrivateFieldGet(this, _data$1));
	      delete result['viewData'];
	      delete result['DialogContext'];
	      result.Delay = babelHelpers.classPrivateFieldGet(this, _delay).serialize();
	      result.Condition = babelHelpers.classPrivateFieldGet(this, _condition$1).serialize();
	      return result;
	    }
	  }, {
	    key: "getDelayInterval",
	    value: function getDelayInterval() {
	      return babelHelpers.classPrivateFieldGet(this, _delay);
	    }
	  }, {
	    key: "setDelayInterval",
	    value: function setDelayInterval(delay) {
	      babelHelpers.classPrivateFieldSet(this, _delay, delay);
	      return this;
	    }
	  }, {
	    key: "getCondition",
	    value: function getCondition() {
	      return babelHelpers.classPrivateFieldGet(this, _condition$1);
	    }
	  }, {
	    key: "setCondition",
	    value: function setCondition(condition) {
	      babelHelpers.classPrivateFieldSet(this, _condition$1, condition);
	      return this;
	    }
	  }, {
	    key: "setExecuteAfterPrevious",
	    value: function setExecuteAfterPrevious(flag) {
	      babelHelpers.classPrivateFieldGet(this, _data$1).ExecuteAfterPrevious = flag ? 1 : 0;
	      return this;
	    }
	  }, {
	    key: "isExecuteAfterPrevious",
	    value: function isExecuteAfterPrevious() {
	      return babelHelpers.classPrivateFieldGet(this, _data$1).ExecuteAfterPrevious === 1 || babelHelpers.classPrivateFieldGet(this, _data$1).ExecuteAfterPrevious === '1';
	    }
	  }, {
	    key: "registerItem",
	    value: function registerItem(object) {
	      if (main_core.Type.isNil(object["__bxddid"])) {
	        object.onbxdragstart = BX.proxy(this.dragStart, this);
	        object.onbxdrag = BX.proxy(this.dragMove, this);
	        object.onbxdragstop = BX.proxy(this.dragStop, this);
	        object.onbxdraghover = BX.proxy(this.dragOver, this);
	        jsDD.registerObject(object);
	        jsDD.registerDest(object, 1);
	      }
	    }
	  }, {
	    key: "unregisterItem",
	    value: function unregisterItem(object) {
	      object.onbxdragstart = undefined;
	      object.onbxdrag = undefined;
	      object.onbxdragstop = undefined;
	      object.onbxdraghover = undefined;
	      jsDD.unregisterObject(object);
	      jsDD.unregisterDest(object);
	    }
	  }, {
	    key: "dragStart",
	    value: function dragStart() {
	      this.draggableItem = BX.proxy_context;
	      if (!this.draggableItem) {
	        jsDD.stopCurrentDrag();
	        return;
	      }
	      if (!this.stub) {
	        var itemWidth = this.draggableItem.offsetWidth;
	        this.stub = this.draggableItem.cloneNode(true);
	        this.stub.style.position = "absolute";
	        this.stub.classList.add("bizproc-automation-robot-container-drag");
	        this.stub.style.width = itemWidth + "px";
	        document.body.appendChild(this.stub);
	      }
	    }
	  }, {
	    key: "dragMove",
	    value: function dragMove(x, y) {
	      this.stub.style.left = x + "px";
	      this.stub.style.top = y + "px";
	    }
	  }, {
	    key: "dragOver",
	    value: function dragOver(destination, x, y) {
	      if (this.droppableItem) {
	        this.droppableItem.classList.remove("bizproc-automation-robot-container-pre");
	      }
	      if (this.droppableColumn) {
	        this.droppableColumn.classList.remove("bizproc-automation-robot-list-pre");
	      }
	      var type = destination.getAttribute("data-type");
	      if (type === "item-robot") {
	        this.droppableItem = destination;
	        this.droppableColumn = null;
	      }
	      if (type === "column-robot") {
	        this.droppableColumn = destination.querySelector('[data-role="robot-list"]');
	        this.droppableItem = null;
	      }
	      if (this.droppableItem) {
	        this.droppableItem.classList.add("bizproc-automation-robot-container-pre");
	      }
	      if (this.droppableColumn) {
	        this.droppableColumn.classList.add("bizproc-automation-robot-list-pre");
	      }
	    }
	  }, {
	    key: "dragStop",
	    value: function dragStop(x, y, event) {
	      event = event || window.event;
	      var isCopy = event && (event.ctrlKey || event.metaKey);
	      if (this.draggableItem) {
	        if (this.droppableItem) {
	          this.droppableItem.classList.remove("bizproc-automation-robot-container-pre");
	          this.emit('Robot:manage', {
	            templateNode: this.droppableItem.parentNode,
	            isCopy: isCopy,
	            droppableItem: this.droppableItem,
	            robot: this
	          });
	        } else if (this.droppableColumn) {
	          this.droppableColumn.classList.remove("bizproc-automation-robot-list-pre");
	          this.emit('Robot:manage', {
	            templateNode: this.droppableColumn,
	            isCopy: isCopy,
	            robot: this
	          });
	        }
	      }
	      this.stub.parentNode.removeChild(this.stub);
	      this.stub = null;
	      this.draggableItem = null;
	      this.droppableItem = null;
	    }
	  }, {
	    key: "moveTo",
	    value: function moveTo(template, beforeRobot) {
	      main_core.Dom.remove(babelHelpers.classPrivateFieldGet(this, _node$1));
	      babelHelpers.classPrivateFieldGet(this, _template).deleteRobot(this);
	      babelHelpers.classPrivateFieldSet(this, _template, template);
	      babelHelpers.classPrivateFieldGet(this, _template).insertRobot(this, beforeRobot);
	      babelHelpers.classPrivateFieldSet(this, _node$1, this.createNode());
	      babelHelpers.classPrivateFieldGet(this, _template).insertRobotNode(babelHelpers.classPrivateFieldGet(this, _node$1), beforeRobot ? beforeRobot.node : null);
	    }
	  }, {
	    key: "copyTo",
	    value: function copyTo(template, beforeRobot) {
	      var robot = new Robot({
	        document: babelHelpers.classPrivateFieldGet(this, _document$1),
	        template: template,
	        isFrameMode: babelHelpers.classPrivateFieldGet(this, _isFrameMode),
	        tracker: babelHelpers.classPrivateFieldGet(this, _tracker)
	      });
	      var robotData = this.serialize();
	      delete robotData['Name'];
	      delete robotData['DelayName'];
	      robot.init(robotData, babelHelpers.classPrivateFieldGet(this, _viewMode$2));
	      template.insertRobot(robot, beforeRobot);
	      template.insertRobotNode(robot.node, beforeRobot ? beforeRobot.node : null);
	      return robot;
	    }
	  }, {
	    key: "getTitle",
	    value: function getTitle() {
	      return this.getProperty('Title') || this.getDescriptionTitle();
	    }
	  }, {
	    key: "getDescriptionTitle",
	    value: function getDescriptionTitle() {
	      var _this$template$getRob, _this$template;
	      var name = 'untitled';
	      var description = (_this$template$getRob = (_this$template = this.template) === null || _this$template === void 0 ? void 0 : _this$template.getRobotDescription(babelHelpers.classPrivateFieldGet(this, _data$1)['Type'])) !== null && _this$template$getRob !== void 0 ? _this$template$getRob : {};
	      if (description['NAME']) {
	        name = description['NAME'];
	      }
	      if (description['ROBOT_SETTINGS'] && description['ROBOT_SETTINGS']['TITLE']) {
	        name = description['ROBOT_SETTINGS']['TITLE'];
	      }
	      return name;
	    }
	  }, {
	    key: "hasTitle",
	    value: function hasTitle() {
	      return this.getTitle() !== 'untitled';
	    }
	  }, {
	    key: "hasReturnFields",
	    value: function hasReturnFields() {
	      var description = this.template.getRobotDescription(babelHelpers.classPrivateFieldGet(this, _data$1)['Type']);
	      var props = babelHelpers.classPrivateFieldGet(this, _data$1)['Properties'];
	      if (!main_core.Type.isObject(description)) {
	        return false;
	      }
	      var hasReturnProperties = function hasReturnProperties() {
	        return main_core.Type.isObject(description['RETURN']) && main_core.Type.isArrayFilled(Object.values(description['RETURN']));
	      };
	      var hasAdditionalResultProperties = function hasAdditionalResultProperties() {
	        return main_core.Type.isArray(description['ADDITIONAL_RESULT']) && description['ADDITIONAL_RESULT'].some(function (addProperty) {
	          var _props$addProperty;
	          return Object.values((_props$addProperty = props[addProperty]) !== null && _props$addProperty !== void 0 ? _props$addProperty : []).length > 0;
	        });
	      };
	      return hasReturnProperties() || hasAdditionalResultProperties();
	    }
	  }, {
	    key: "getReturnFieldsDescription",
	    value: function getReturnFieldsDescription() {
	      var _this4 = this;
	      var fields = [];
	      var description = this.template.getRobotDescription(babelHelpers.classPrivateFieldGet(this, _data$1)['Type']);
	      if (description && description['RETURN']) {
	        for (var fieldId in description['RETURN']) {
	          if (description['RETURN'].hasOwnProperty(fieldId)) {
	            var field = description['RETURN'][fieldId];
	            fields.push({
	              Id: fieldId,
	              ObjectId: this.getId(),
	              ObjectName: this.getTitle(),
	              Name: field['NAME'],
	              Type: field['TYPE'],
	              Options: field['OPTIONS'] || null,
	              Expression: '{{~' + this.getId() + ':' + fieldId + ' # ' + this.getTitle() + ': ' + field['NAME'] + '}}',
	              SystemExpression: '{=' + this.getId() + ':' + fieldId + '}'
	            });
	            if (!this.appendPropertyMods) {
	              continue;
	            }

	            //generate printable version
	            if (field['TYPE'] === 'user' || field['TYPE'] === 'bool' || field['TYPE'] === 'file') {
	              var printableTag = field['TYPE'] === 'user' ? 'friendly' : 'printable';
	              fields.push({
	                Id: fieldId + '_printable',
	                ObjectId: this.getId(),
	                Name: field['NAME'] + ' ' + main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_MOD_PRINTABLE_PREFIX'),
	                Type: 'string',
	                Expression: "{{~".concat(this.getId(), ":").concat(fieldId, " > ").concat(printableTag, " # ").concat(this.getTitle(), ": ").concat(field['NAME'], "}}"),
	                SystemExpression: "{=".concat(this.getId(), ":").concat(fieldId, ">").concat(printableTag, "}")
	              });
	            }
	          }
	        }
	      }
	      if (description && main_core.Type.isArray(description['ADDITIONAL_RESULT'])) {
	        var props = babelHelpers.classPrivateFieldGet(this, _data$1)['Properties'];
	        description['ADDITIONAL_RESULT'].forEach(function (addProperty) {
	          if (props[addProperty]) {
	            for (var _fieldId in props[addProperty]) {
	              if (props[addProperty].hasOwnProperty(_fieldId)) {
	                var _field = props[addProperty][_fieldId];
	                fields.push({
	                  Id: _fieldId,
	                  ObjectId: _this4.getId(),
	                  Name: _field['Name'],
	                  Type: _field['Type'],
	                  Options: _field['Options'] || null,
	                  Expression: "{{~".concat(_this4.getId(), ":").concat(_fieldId, " # ").concat(_this4.getTitle(), ": ").concat(_field['Name'], "}}"),
	                  SystemExpression: '{=' + _this4.getId() + ':' + _fieldId + '}'
	                });

	                //generate printable version
	                if (_field['Type'] === 'user' || _field['Type'] === 'bool' || _field['Type'] === 'file') {
	                  var _printableTag = _field['Type'] === 'user' ? 'friendly' : 'printable';
	                  var expression = "{{~".concat(_this4.getId(), ":").concat(_fieldId, " > ").concat(_printableTag, " # ").concat(_this4.getTitle(), ": ").concat(_field['Name'], "}}");
	                  fields.push({
	                    Id: _fieldId + '_printable',
	                    ObjectId: _this4.getId(),
	                    Name: _field['Name'] + ' ' + main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_MOD_PRINTABLE_PREFIX'),
	                    Type: 'string',
	                    Expression: expression,
	                    SystemExpression: '{=' + _this4.getId() + ':' + _fieldId + '>' + _printableTag + '}'
	                  });
	                }
	              }
	            }
	          }
	        });
	      }
	      return fields;
	    }
	  }, {
	    key: "getReturnProperty",
	    value: function getReturnProperty(id) {
	      var fields = this.getReturnFieldsDescription();
	      for (var i = 0; i < fields.length; ++i) {
	        if (fields[i]['Id'] === id) {
	          return fields[i];
	        }
	      }
	      return null;
	    }
	  }, {
	    key: "collectUsages",
	    value: function collectUsages() {
	      var _this5 = this;
	      var properties = this.getProperties();
	      var usages = {
	        Document: new Set(),
	        Constant: new Set(),
	        Variable: new Set(),
	        Parameter: new Set(),
	        GlobalConstant: new Set(),
	        GlobalVariable: new Set(),
	        Activity: new Set()
	      };
	      Object.values(properties).forEach(function (property) {
	        return _this5.collectExpressions(property, usages);
	      });
	      var conditions = this.getCondition().serialize();
	      conditions.items.forEach(function (item) {
	        return _this5.collectParsedExpressions(item[0], usages);
	      });
	      return usages;
	    }
	  }, {
	    key: "collectExpressions",
	    value: function collectExpressions(value, usages) {
	      var _this6 = this;
	      if (main_core.Type.isArray(value)) {
	        value.forEach(function (v) {
	          return _this6.collectExpressions(v, usages);
	        });
	      } else if (main_core.Type.isPlainObject(value)) {
	        Object.values(value).forEach(function (value) {
	          return _this6.collectExpressions(value, usages);
	        });
	      } else if (main_core.Type.isStringFilled(value)) {
	        var found;
	        var systemExpressionRegExp = new RegExp(this.SYSTEM_EXPRESSION_PATTERN, 'ig');
	        while ((found = systemExpressionRegExp.exec(value)) !== null) {
	          this.collectParsedExpressions(found.groups, usages);
	        }
	      }
	    }
	  }, {
	    key: "collectParsedExpressions",
	    value: function collectParsedExpressions(parsedUsage, usages) {
	      if (main_core.Type.isPlainObject(parsedUsage) && parsedUsage['object'] && parsedUsage['field']) {
	        switch (parsedUsage['object']) {
	          case 'Document':
	            usages.Document.add(parsedUsage['field']);
	            return;
	          case 'Constant':
	            usages.Constant.add(parsedUsage['field']);
	            return;
	          case 'Variable':
	            usages.Variable.add(parsedUsage['field']);
	            return;
	          case 'Template':
	            usages.Parameter.add(parsedUsage['field']);
	            return;
	          case 'GlobalConst':
	            usages.GlobalConstant.add(parsedUsage['field']);
	            return;
	          case 'GlobalVar':
	            usages.GlobalVariable.add(parsedUsage['field']);
	            return;
	        }
	        var activityRegExp = new RegExp(/^A[_0-9]+$/, 'ig');
	        if (activityRegExp.exec(parsedUsage['object'])) {
	          usages.Activity.add([parsedUsage['object'], parsedUsage['field']]);
	        }
	      }
	    }
	  }, {
	    key: "hasBrokenLink",
	    value: function hasBrokenLink() {
	      return this.getBrokenLinks().length > 0;
	    }
	  }, {
	    key: "getBrokenLinks",
	    value: function getBrokenLinks() {
	      var usages = main_core.Runtime.clone(this.collectUsages());
	      if (!this.template) {
	        return [];
	      }
	      var objectsData = {
	        Document: babelHelpers.classPrivateFieldGet(this, _document$1).getFields(),
	        Constant: babelHelpers.classPrivateFieldGet(this, _template).getConstants(),
	        Variable: babelHelpers.classPrivateFieldGet(this, _template).getVariables(),
	        GlobalConstant: babelHelpers.classPrivateFieldGet(this, _template).globalConstants,
	        GlobalVariable: babelHelpers.classPrivateFieldGet(this, _template).globalVariables,
	        Parameter: babelHelpers.classPrivateFieldGet(this, _template).getParameters(),
	        Activity: babelHelpers.classPrivateFieldGet(this, _template).getSerializedRobots()
	      };
	      var brokenLinks = [];
	      for (var object in usages) {
	        if (usages[object].size > 0) {
	          var source = new Set();
	          for (var key in objectsData[object]) {
	            if (objectsData[object][key]['Id']) {
	              source.add(objectsData[object][key]['Id']);
	            } else if (objectsData[object][key]['Name']) {
	              source.add(objectsData[object][key]['Name']);
	            }
	          }
	          var _iterator = _createForOfIteratorHelper$4(usages[object].values()),
	            _step;
	          try {
	            for (_iterator.s(); !(_step = _iterator.n()).done;) {
	              var value = _step.value;
	              var searchInSource = value;
	              var id = value;
	              if (main_core.Type.isArray(searchInSource)) {
	                searchInSource = value[0];
	                id = value[1];
	              }
	              if (!source.has(searchInSource)) {
	                if (object === 'Activity') {
	                  brokenLinks.push('{=' + searchInSource + ':' + id + '}');
	                } else {
	                  var brokenLinkObject = object;
	                  if (brokenLinkObject === 'GlobalVariable') {
	                    brokenLinkObject = 'GlobalVar';
	                  }
	                  if (brokenLinkObject === 'GlobalConstant') {
	                    brokenLinkObject = 'GlobalConst';
	                  }
	                  if (brokenLinkObject === 'Parameter') {
	                    brokenLinkObject = 'Template';
	                  }
	                  brokenLinks.push('{=' + brokenLinkObject + ':' + searchInSource + '}');
	                }
	                continue;
	              }
	              if (object === 'Activity') {
	                var robot = babelHelpers.classPrivateFieldGet(this, _template).getRobotById(searchInSource);
	                if (!robot.getReturnProperty(id)) {
	                  brokenLinks.push('{=' + searchInSource + ':' + id + '}');
	                }
	              }
	            }
	          } catch (err) {
	            _iterator.e(err);
	          } finally {
	            _iterator.f();
	          }
	        }
	      }
	      return brokenLinks;
	    }
	  }, {
	    key: "node",
	    get: function get() {
	      return babelHelpers.classPrivateFieldGet(this, _node$1);
	    }
	  }, {
	    key: "data",
	    get: function get() {
	      return _objectSpread$1(_objectSpread$1({}, babelHelpers.classPrivateFieldGet(this, _data$1)), {}, {
	        Condition: babelHelpers.classPrivateFieldGet(this, _condition$1).serialize(),
	        Delay: babelHelpers.classPrivateFieldGet(this, _delay).serialize()
	      });
	    }
	  }, {
	    key: "draft",
	    get: function get() {
	      return babelHelpers.classPrivateFieldGet(this, _isDraft);
	    },
	    set: function set(draft) {
	      babelHelpers.classPrivateFieldSet(this, _isDraft, draft);
	    }
	  }, {
	    key: "template",
	    get: function get() {
	      return babelHelpers.classPrivateFieldGet(this, _template);
	    }
	  }], [{
	    key: "generateName",
	    value: function generateName() {
	      return 'A' + parseInt(Math.random() * 100000) + '_' + parseInt(Math.random() * 100000) + '_' + parseInt(Math.random() * 100000) + '_' + parseInt(Math.random() * 100000);
	    }
	  }]);
	  return Robot;
	}(main_core_events.EventEmitter);

	function _classPrivateFieldInitSpec$d(obj, privateMap, value) { _checkPrivateRedeclaration$d(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$d(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	var _options = /*#__PURE__*/new WeakMap();
	var UserOptions = /*#__PURE__*/function () {
	  function UserOptions(options) {
	    babelHelpers.classCallCheck(this, UserOptions);
	    _classPrivateFieldInitSpec$d(this, _options, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldSet(this, _options, options);
	  }
	  babelHelpers.createClass(UserOptions, [{
	    key: "clone",
	    value: function clone() {
	      return new UserOptions(main_core.Runtime.clone(babelHelpers.classPrivateFieldGet(this, _options)));
	    }
	  }, {
	    key: "set",
	    value: function set(category, key, value) {
	      if (!main_core.Type.isPlainObject(babelHelpers.classPrivateFieldGet(this, _options)[category])) {
	        babelHelpers.classPrivateFieldGet(this, _options)[category] = {};
	      }
	      var storedValue = babelHelpers.classPrivateFieldGet(this, _options)[category][key];
	      if (storedValue !== value) {
	        BX.userOptions.save('bizproc.automation', category, key, value, false);
	      }
	      return this;
	    }
	  }, {
	    key: "get",
	    value: function get(category, key, defaultValue) {
	      var result = defaultValue;
	      if (this.has(category, key)) {
	        result = babelHelpers.classPrivateFieldGet(this, _options)[category][key];
	      }
	      return result;
	    }
	  }, {
	    key: "has",
	    value: function has(category, key) {
	      return main_core.Type.isPlainObject(babelHelpers.classPrivateFieldGet(this, _options)[category]) && main_core.Type.isPlainObject(babelHelpers.classPrivateFieldGet(this, _options)[category][key]);
	    }
	  }]);
	  return UserOptions;
	}();

	var _templateObject, _templateObject2, _templateObject3, _templateObject4, _templateObject5;
	function ownKeys$2(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread$2(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys$2(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys$2(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	function _classPrivateMethodInitSpec$2(obj, privateSet) { _checkPrivateRedeclaration$e(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec$e(obj, privateMap, value) { _checkPrivateRedeclaration$e(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$e(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet$2(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _context = /*#__PURE__*/new WeakMap();
	var _delayMinLimitM = /*#__PURE__*/new WeakMap();
	var _userOptions = /*#__PURE__*/new WeakMap();
	var _tracker$1 = /*#__PURE__*/new WeakMap();
	var _viewMode$3 = /*#__PURE__*/new WeakMap();
	var _templateContainerNode = /*#__PURE__*/new WeakMap();
	var _templateNode = /*#__PURE__*/new WeakMap();
	var _listNode = /*#__PURE__*/new WeakMap();
	var _buttonsNode = /*#__PURE__*/new WeakMap();
	var _robots = /*#__PURE__*/new WeakMap();
	var _data$2 = /*#__PURE__*/new WeakMap();
	var _getUserSelectorAdditionalFields = /*#__PURE__*/new WeakSet();
	var _addRobotReturnFieldsToSelector = /*#__PURE__*/new WeakSet();
	var _addConstantsToSelector = /*#__PURE__*/new WeakSet();
	var _addVariablesToSelector = /*#__PURE__*/new WeakSet();
	var _getRobotsWithReturnFields = /*#__PURE__*/new WeakSet();
	var Template = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(Template, _EventEmitter);
	  function Template(params) {
	    var _params$context;
	    var _this;
	    babelHelpers.classCallCheck(this, Template);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Template).call(this));
	    _classPrivateMethodInitSpec$2(babelHelpers.assertThisInitialized(_this), _getRobotsWithReturnFields);
	    _classPrivateMethodInitSpec$2(babelHelpers.assertThisInitialized(_this), _addVariablesToSelector);
	    _classPrivateMethodInitSpec$2(babelHelpers.assertThisInitialized(_this), _addConstantsToSelector);
	    _classPrivateMethodInitSpec$2(babelHelpers.assertThisInitialized(_this), _addRobotReturnFieldsToSelector);
	    _classPrivateMethodInitSpec$2(babelHelpers.assertThisInitialized(_this), _getUserSelectorAdditionalFields);
	    _classPrivateFieldInitSpec$e(babelHelpers.assertThisInitialized(_this), _context, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$e(babelHelpers.assertThisInitialized(_this), _delayMinLimitM, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$e(babelHelpers.assertThisInitialized(_this), _userOptions, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$e(babelHelpers.assertThisInitialized(_this), _tracker$1, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$e(babelHelpers.assertThisInitialized(_this), _viewMode$3, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$e(babelHelpers.assertThisInitialized(_this), _templateContainerNode, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$e(babelHelpers.assertThisInitialized(_this), _templateNode, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$e(babelHelpers.assertThisInitialized(_this), _listNode, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$e(babelHelpers.assertThisInitialized(_this), _buttonsNode, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$e(babelHelpers.assertThisInitialized(_this), _robots, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$e(babelHelpers.assertThisInitialized(_this), _data$2, {
	      writable: true,
	      value: void 0
	    });
	    _this.setEventNamespace('BX.Bizproc.Automation');
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _context, (_params$context = params.context) !== null && _params$context !== void 0 ? _params$context : bizproc_automation.getGlobalContext());
	    _this.constants = params.constants;
	    _this.variables = params.variables;
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _templateContainerNode, params.templateContainerNode);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _delayMinLimitM, params.delayMinLimitM);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _userOptions, params.userOptions);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _tracker$1, babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _context).tracker);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _data$2, {});
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _robots, []);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _viewMode$3, ViewMode.none());
	    return _this;
	  }
	  babelHelpers.createClass(Template, [{
	    key: "init",
	    value: function init(data, viewMode) {
	      if (main_core.Type.isPlainObject(data)) {
	        babelHelpers.classPrivateFieldSet(this, _data$2, data);
	        if (!main_core.Type.isPlainObject(babelHelpers.classPrivateFieldGet(this, _data$2).CONSTANTS)) {
	          babelHelpers.classPrivateFieldGet(this, _data$2).CONSTANTS = {};
	        }
	        if (!main_core.Type.isPlainObject(babelHelpers.classPrivateFieldGet(this, _data$2).PARAMETERS)) {
	          babelHelpers.classPrivateFieldGet(this, _data$2).PARAMETERS = {};
	        }
	        if (!main_core.Type.isPlainObject(babelHelpers.classPrivateFieldGet(this, _data$2).VARIABLES)) {
	          babelHelpers.classPrivateFieldGet(this, _data$2).VARIABLES = {};
	        }
	        if (!main_core.Type.isNil(babelHelpers.classPrivateFieldGet(this, _data$2).DOCUMENT_STATUS)) {
	          babelHelpers.classPrivateFieldGet(this, _data$2).DOCUMENT_STATUS = String(babelHelpers.classPrivateFieldGet(this, _data$2).DOCUMENT_STATUS);
	        }
	        this.markExternalModified(babelHelpers.classPrivateFieldGet(this, _data$2)['IS_EXTERNAL_MODIFIED']);
	        this.markModified(false);
	      }
	      babelHelpers.classPrivateFieldSet(this, _viewMode$3, ViewMode.fromRaw(viewMode));
	      if (!babelHelpers.classPrivateFieldGet(this, _viewMode$3).isNone()) {
	        babelHelpers.classPrivateFieldSet(this, _templateNode, babelHelpers.classPrivateFieldGet(this, _templateContainerNode).querySelector('[data-role="automation-template"][data-status-id="' + babelHelpers.classPrivateFieldGet(this, _data$2).DOCUMENT_STATUS + '"]'));
	        babelHelpers.classPrivateFieldSet(this, _listNode, babelHelpers.classPrivateFieldGet(this, _templateNode).querySelector('[data-role="robot-list"]'));
	        babelHelpers.classPrivateFieldSet(this, _buttonsNode, babelHelpers.classPrivateFieldGet(this, _templateNode).querySelector('[data-role="buttons"]'));
	        this.initRobots();
	        this.initButtons();
	        if (!this.isExternalModified() && this.canEdit()) {
	          //register DD
	          jsDD.registerDest(babelHelpers.classPrivateFieldGet(this, _templateNode), 10);
	        } else {
	          jsDD.unregisterDest(babelHelpers.classPrivateFieldGet(this, _templateNode));
	        }
	      }
	    }
	  }, {
	    key: "reInit",
	    value: function reInit(data, viewMode) {
	      main_core.Dom.clean(babelHelpers.classPrivateFieldGet(this, _listNode));
	      main_core.Dom.clean(babelHelpers.classPrivateFieldGet(this, _buttonsNode));
	      this.destroy();
	      this.init(data, viewMode);
	    }
	  }, {
	    key: "destroy",
	    value: function destroy() {
	      babelHelpers.classPrivateFieldGet(this, _robots).forEach(function (robot) {
	        return robot.destroy();
	      });
	    }
	  }, {
	    key: "canEdit",
	    value: function canEdit() {
	      return babelHelpers.classPrivateFieldGet(this, _context).canEdit;
	    }
	  }, {
	    key: "initRobots",
	    value: function initRobots() {
	      babelHelpers.classPrivateFieldSet(this, _robots, []);
	      if (main_core.Type.isArray(babelHelpers.classPrivateFieldGet(this, _data$2).ROBOTS)) {
	        for (var i = 0; i < babelHelpers.classPrivateFieldGet(this, _data$2).ROBOTS.length; ++i) {
	          var robot = new Robot({
	            document: babelHelpers.classPrivateFieldGet(this, _context).document,
	            template: this,
	            isFrameMode: babelHelpers.classPrivateFieldGet(this, _context).get('isFrameMode'),
	            tracker: babelHelpers.classPrivateFieldGet(this, _tracker$1)
	          });
	          robot.init(babelHelpers.classPrivateFieldGet(this, _data$2).ROBOTS[i], babelHelpers.classPrivateFieldGet(this, _viewMode$3));
	          this.insertRobotNode(robot.node);
	          babelHelpers.classPrivateFieldGet(this, _robots).push(robot);
	        }
	      }
	    }
	  }, {
	    key: "getSelectedRobotNames",
	    value: function getSelectedRobotNames() {
	      var selectedRobots = [];
	      babelHelpers.classPrivateFieldGet(this, _robots).forEach(function (robot) {
	        if (robot.isSelected()) {
	          selectedRobots.push(robot.data.Name);
	        }
	      });
	      return selectedRobots;
	    }
	  }, {
	    key: "getSerializedRobots",
	    value: function getSerializedRobots() {
	      var serialized = [];
	      babelHelpers.classPrivateFieldGet(this, _robots).forEach(function (robot) {
	        return serialized.push(robot.serialize());
	      });
	      return serialized;
	    }
	  }, {
	    key: "getId",
	    value: function getId() {
	      return babelHelpers.classPrivateFieldGet(this, _data$2).ID;
	    }
	  }, {
	    key: "getStatusId",
	    value: function getStatusId() {
	      return babelHelpers.classPrivateFieldGet(this, _data$2).DOCUMENT_STATUS;
	    }
	  }, {
	    key: "getStatus",
	    value: function getStatus() {
	      var _this2 = this;
	      return babelHelpers.classPrivateFieldGet(this, _context).document.statusList.find(function (status) {
	        return String(status.STATUS_ID) === _this2.getStatusId();
	      });
	    }
	  }, {
	    key: "getTemplateId",
	    value: function getTemplateId() {
	      var id = parseInt(babelHelpers.classPrivateFieldGet(this, _data$2).ID);
	      return !isNaN(id) ? id : 0;
	    }
	  }, {
	    key: "initButtons",
	    value: function initButtons() {
	      if (this.isExternalModified()) {
	        this.createExternalLocker();
	      } else if (babelHelpers.classPrivateFieldGet(this, _viewMode$3).isEdit() && this.getTemplateId() > 0) {
	        this.createConstantsEditButton();
	        this.createParametersEditButton();
	        this.createExternalEditTemplateButton();
	        this.createManageModeButton();
	      }
	    }
	  }, {
	    key: "enableManageMode",
	    value: function enableManageMode(isActive) {
	      if (babelHelpers.classPrivateFieldGet(this, _listNode)) {
	        babelHelpers.classPrivateFieldSet(this, _viewMode$3, ViewMode.manage().setProperty('isActive', isActive));
	        if (isActive) {
	          main_core.Dom.addClass(babelHelpers.classPrivateFieldGet(this, _listNode), '--multiselect-mode');
	        }
	        if (this.isExternalModified()) {
	          main_core.Dom.addClass(babelHelpers.classPrivateFieldGet(this, _listNode), '--locked-node');
	        } else {
	          babelHelpers.classPrivateFieldGet(this, _robots).forEach(function (robot) {
	            return robot.enableManageMode(isActive);
	          });
	        }
	      }
	    }
	  }, {
	    key: "disableManageMode",
	    value: function disableManageMode() {
	      if (babelHelpers.classPrivateFieldGet(this, _listNode)) {
	        babelHelpers.classPrivateFieldSet(this, _viewMode$3, ViewMode.edit());
	        main_core.Dom.removeClass(babelHelpers.classPrivateFieldGet(this, _listNode), '--multiselect-mode');
	        if (this.isExternalModified()) {
	          main_core.Dom.removeClass(babelHelpers.classPrivateFieldGet(this, _listNode), '--locked-node');
	        } else {
	          babelHelpers.classPrivateFieldGet(this, _robots).forEach(function (robot) {
	            return robot.disableManageMode();
	          });
	        }
	        babelHelpers.classPrivateFieldGet(this, _templateNode).querySelectorAll('.bizproc-automation-robot-container-wrapper').forEach(function (node) {
	          main_core.Dom.addClass(node, 'bizproc-automation-robot-container-wrapper-draggable');
	        });
	      }
	    }
	  }, {
	    key: "enableDragAndDrop",
	    value: function enableDragAndDrop() {
	      babelHelpers.classPrivateFieldGet(this, _robots).forEach(function (robot) {
	        return robot.registerItem(robot.node);
	      });
	      babelHelpers.classPrivateFieldGet(this, _templateNode).querySelectorAll('.bizproc-automation-robot-container-wrapper').forEach(function (node) {
	        main_core.Dom.addClass(node, 'bizproc-automation-robot-container-wrapper-draggable');
	      });
	    }
	  }, {
	    key: "disableDragAndDrop",
	    value: function disableDragAndDrop() {
	      babelHelpers.classPrivateFieldGet(this, _robots).forEach(function (robot) {
	        return robot.unregisterItem(robot.node);
	      });
	      babelHelpers.classPrivateFieldGet(this, _templateNode).querySelectorAll('.bizproc-automation-robot-container-wrapper').forEach(function (node) {
	        main_core.Dom.removeClass(node, 'bizproc-automation-robot-container-wrapper-draggable');
	      });
	    }
	  }, {
	    key: "createExternalEditTemplateButton",
	    value: function createExternalEditTemplateButton() {
	      if (main_core.Type.isNil(babelHelpers.classPrivateFieldGet(this, _context).bizprocEditorUrl)) {
	        return false;
	      }
	      var self = this;
	      var anchor = main_core.Dom.create('a', {
	        text: main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_EXTERNAL_EDIT'),
	        props: {
	          href: '#'
	        },
	        events: {
	          click: function click(event) {
	            event.preventDefault();
	            if (!babelHelpers.classPrivateFieldGet(self, _viewMode$3).isManage()) {
	              self.onExternalEditTemplateButtonClick(this);
	            }
	          }
	        },
	        attrs: {
	          className: "bizproc-automation-robot-btn-set",
	          target: '_top'
	        }
	      });
	      if (!babelHelpers.classPrivateFieldGet(this, _context).bizprocEditorUrl.length) {
	        main_core.Dom.addClass(anchor, 'bizproc-automation-robot-btn-set-locked');
	      }
	      babelHelpers.classPrivateFieldGet(this, _buttonsNode).appendChild(anchor);
	    }
	  }, {
	    key: "createManageModeButton",
	    value: function createManageModeButton() {
	      var _this3 = this;
	      if (!babelHelpers.classPrivateFieldGet(this, _context).canManage) {
	        return;
	      }
	      var manageButton = main_core.Dom.create('a', {
	        text: main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_MANAGE_ROBOTS'),
	        attrs: {
	          className: "bizproc-automation-robot-btn-set",
	          target: '_top'
	        },
	        style: {
	          cursor: 'pointer'
	        },
	        events: {
	          click: function click(event) {
	            event.preventDefault();
	            _this3.onManageModeButtonClick(manageButton);
	          }
	        }
	      });
	      babelHelpers.classPrivateFieldGet(this, _buttonsNode).appendChild(manageButton);
	    }
	  }, {
	    key: "onManageModeButtonClick",
	    value: function onManageModeButtonClick(manageButtonNode) {
	      if (!this.canEdit()) {
	        HelpHint.showNoPermissionsHint(manageButtonNode);
	      } else {
	        this.emit('Template:enableManageMode', {
	          documentStatus: babelHelpers.classPrivateFieldGet(this, _data$2).DOCUMENT_STATUS
	        });
	      }
	    }
	  }, {
	    key: "createConstantsEditButton",
	    value: function createConstantsEditButton() {
	      if (main_core.Type.isNil(babelHelpers.classPrivateFieldGet(this, _context).constantsEditorUrl)) {
	        return false;
	      }
	      var url = !babelHelpers.classPrivateFieldGet(this, _viewMode$3).isManage() ? babelHelpers.classPrivateFieldGet(this, _context).constantsEditorUrl.replace('#ID#', this.getTemplateId()) : '#';
	      if (!url.length) {
	        return false;
	      }
	      var anchor = main_core.Dom.create('a', {
	        text: main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_CONSTANTS_EDIT'),
	        props: {
	          href: url
	        },
	        attrs: {
	          className: "bizproc-automation-robot-btn-set"
	        }
	      });
	      babelHelpers.classPrivateFieldGet(this, _buttonsNode).appendChild(anchor);
	    }
	  }, {
	    key: "createParametersEditButton",
	    value: function createParametersEditButton() {
	      if (main_core.Type.isNil(babelHelpers.classPrivateFieldGet(this, _context).parametersEditorUrl)) {
	        return false;
	      }
	      var url = babelHelpers.classPrivateFieldGet(this, _context).parametersEditorUrl.replace('#ID#', this.getTemplateId());
	      if (!url.length || babelHelpers.classPrivateFieldGet(this, _viewMode$3).isManage()) {
	        return false;
	      }
	      var anchor = main_core.Dom.create('a', {
	        text: main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_PARAMETERS_EDIT'),
	        props: {
	          href: url
	        },
	        attrs: {
	          className: "bizproc-automation-robot-btn-set"
	        }
	      });
	      babelHelpers.classPrivateFieldGet(this, _buttonsNode).appendChild(anchor);
	    }
	  }, {
	    key: "createExternalLocker",
	    value: function createExternalLocker() {
	      var div = main_core.Dom.create("div", {
	        attrs: {
	          className: "bizproc-automation-robot-container"
	        },
	        children: [main_core.Dom.create('div', {
	          attrs: {
	            className: 'bizproc-automation-robot-container-wrapper bizproc-automation-robot-container-wrapper-lock'
	          },
	          children: [main_core.Dom.create("div", {
	            attrs: {
	              className: "bizproc-automation-robot-deadline"
	            }
	          }), main_core.Dom.create("div", {
	            attrs: {
	              className: "bizproc-automation-robot-title"
	            },
	            text: main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_EXTERNAL_EDIT_TEXT')
	          })]
	        })]
	      });
	      if (babelHelpers.classPrivateFieldGet(this, _viewMode$3).isEdit()) {
	        var settingsBtn = main_core.Dom.create('div', {
	          attrs: {
	            className: 'bizproc-automation-robot-btn-settings'
	          },
	          text: main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_EDIT')
	        });
	        var self = this;
	        main_core.Event.bind(div, 'click', function (event) {
	          event.stopPropagation();
	          if (!babelHelpers.classPrivateFieldGet(self, _viewMode$3).isManage()) {
	            self.onExternalEditTemplateButtonClick(this);
	          }
	        });
	        div.appendChild(settingsBtn);
	        var deleteBtn = main_core.Dom.create('SPAN', {
	          attrs: {
	            className: 'bizproc-automation-robot-btn-delete'
	          }
	        });
	        main_core.Event.bind(deleteBtn, 'click', function (event) {
	          event.stopPropagation();
	          if (!babelHelpers.classPrivateFieldGet(self, _viewMode$3).isManage()) {
	            self.onUnsetExternalModifiedClick(this);
	          }
	        });
	        div.lastChild.appendChild(deleteBtn);
	      }
	      babelHelpers.classPrivateFieldGet(this, _listNode).appendChild(div);
	      babelHelpers.classPrivateFieldSet(this, _templateNode, div);
	    }
	  }, {
	    key: "onSearch",
	    value: function onSearch(event) {
	      if (this.isExternalModified()) {
	        this.onExternalModifiedSearch(event);
	      } else {
	        babelHelpers.classPrivateFieldGet(this, _robots).forEach(function (robot) {
	          return robot.onSearch(event);
	        });
	      }
	    }
	  }, {
	    key: "onExternalModifiedSearch",
	    value: function onExternalModifiedSearch(event) {
	      if (babelHelpers.classPrivateFieldGet(this, _templateNode)) {
	        var query = event.getData().queryString;
	        BX[!query ? 'removeClass' : 'addClass'](babelHelpers.classPrivateFieldGet(this, _templateNode), '--search-mismatch');
	      }
	    }
	  }, {
	    key: "onExternalEditTemplateButtonClick",
	    value: function onExternalEditTemplateButtonClick(button) {
	      if (!this.canEdit()) {
	        HelpHint.showNoPermissionsHint(button);
	        return;
	      }
	      if (!babelHelpers.classPrivateFieldGet(this, _context).bizprocEditorUrl.length) {
	        if (top.BX.UI && top.BX.UI.InfoHelper) {
	          top.BX.UI.InfoHelper.show('limit_office_bp_designer');
	        }
	        return;
	      }
	      var templateId = this.getTemplateId();
	      if (templateId > 0) {
	        this.openBizprocEditor(templateId);
	      }
	    }
	  }, {
	    key: "onUnsetExternalModifiedClick",
	    value: function onUnsetExternalModifiedClick(button) {
	      babelHelpers.classPrivateFieldSet(this, _templateNode, null);
	      this.markExternalModified(false);
	      this.markModified();
	      this.reInit(null, babelHelpers.classPrivateFieldGet(this, _viewMode$3).intoRaw());
	    }
	  }, {
	    key: "openBizprocEditor",
	    value: function openBizprocEditor(templateId) {
	      top.window.location.href = babelHelpers.classPrivateFieldGet(this, _context).bizprocEditorUrl.replace('#ID#', templateId);
	    }
	  }, {
	    key: "addRobot",
	    value: function addRobot(robotData, callback) {
	      var robot = new Robot({
	        document: babelHelpers.classPrivateFieldGet(this, _context).document,
	        template: this,
	        isFrameMode: babelHelpers.classPrivateFieldGet(this, _context).get('isFrameMode'),
	        tracker: babelHelpers.classPrivateFieldGet(this, _tracker$1)
	      });
	      var initData = {
	        Type: robotData['CLASS'],
	        Properties: {
	          Title: robotData['NAME']
	        },
	        DialogContext: robotData['DIALOG_CONTEXT']
	      };
	      if (babelHelpers.classPrivateFieldGet(this, _robots).length > 0) {
	        var parentRobot = babelHelpers.classPrivateFieldGet(this, _robots)[babelHelpers.classPrivateFieldGet(this, _robots).length - 1];
	        if (!parentRobot.getDelayInterval().isNow() || parentRobot.isExecuteAfterPrevious()) {
	          initData['Delay'] = parentRobot.getDelayInterval().serialize();
	          initData['ExecuteAfterPrevious'] = 1;
	        }
	      }
	      robot.draft = true;
	      robot.init(initData, babelHelpers.classPrivateFieldGet(this, _viewMode$3));
	      this.insertRobot(robot);
	      this.insertRobotNode(robot.node);
	      this.emit('Template:robot:add', {
	        robot: robot
	      });
	      if (callback) {
	        callback.call(this, robot);
	      }
	    }
	  }, {
	    key: "insertRobot",
	    value: function insertRobot(robot, beforeRobot) {
	      if (beforeRobot) {
	        for (var i = 0; i < babelHelpers.classPrivateFieldGet(this, _robots).length; ++i) {
	          if (babelHelpers.classPrivateFieldGet(this, _robots)[i] !== beforeRobot) {
	            continue;
	          }
	          babelHelpers.classPrivateFieldGet(this, _robots).splice(i, 0, robot);
	          break;
	        }
	      } else {
	        babelHelpers.classPrivateFieldGet(this, _robots).push(robot);
	      }
	      this.markModified();
	    }
	  }, {
	    key: "getNextRobot",
	    value: function getNextRobot(robot) {
	      for (var i = 0; i < babelHelpers.classPrivateFieldGet(this, _robots).length; ++i) {
	        if (babelHelpers.classPrivateFieldGet(this, _robots)[i] === robot) {
	          return babelHelpers.classPrivateFieldGet(this, _robots)[i + 1] || null;
	        }
	      }
	      return null;
	    }
	  }, {
	    key: "deleteRobot",
	    value: function deleteRobot(robot, callback) {
	      for (var i = 0; i < babelHelpers.classPrivateFieldGet(this, _robots).length; ++i) {
	        if (babelHelpers.classPrivateFieldGet(this, _robots)[i].isEqual(robot)) {
	          babelHelpers.classPrivateFieldGet(this, _robots).splice(i, 1);
	          if (callback) {
	            callback(robot);
	          }
	          this.markModified();
	          this.emit('Template:robot:delete', {
	            robot: robot
	          });
	          break;
	        }
	      }
	    }
	  }, {
	    key: "insertRobotNode",
	    value: function insertRobotNode(robotNode, beforeNode) {
	      if (beforeNode) {
	        babelHelpers.classPrivateFieldGet(this, _listNode).insertBefore(robotNode, beforeNode);
	      } else {
	        babelHelpers.classPrivateFieldGet(this, _listNode).appendChild(robotNode);
	      }
	    }
	  }, {
	    key: "openRobotSettingsDialog",
	    value: function openRobotSettingsDialog(robot, context, saveCallback) {
	      var _this4 = this;
	      if (!main_core.Type.isPlainObject(context)) {
	        context = {};
	      }
	      if (bizproc_automation.Designer.getInstance().getRobotSettingsDialog()) {
	        if (context.changeRobot) {
	          bizproc_automation.Designer.getInstance().getRobotSettingsDialog().popup.close();
	        } else {
	          return;
	        }
	      }
	      var formName = 'bizproc_automation_robot_dialog';
	      var form = main_core.Dom.create('form', {
	        props: {
	          name: formName
	        }
	      });
	      bizproc_automation.Designer.getInstance().setRobotSettingsDialog({
	        template: this,
	        context: context,
	        robot: robot,
	        form: form
	      });
	      form.appendChild(this.renderDelaySettings(robot));
	      form.appendChild(this.renderConditionSettings(robot));
	      var robotBrokenLinks = robot.getBrokenLinks();
	      if (robotBrokenLinks.length > 0) {
	        main_core.Dom.append(this.renderBrokenLinkAlert(robotBrokenLinks), form);
	      }
	      var iconHelp = main_core.Dom.create('div', {
	        attrs: {
	          className: 'bizproc-automation-robot-help'
	        },
	        events: {
	          click: function click(event) {
	            return _this4.emit('Template:help:show', event);
	          }
	        }
	      });
	      form.appendChild(iconHelp);
	      context['DOCUMENT_CATEGORY_ID'] = babelHelpers.classPrivateFieldGet(this, _context).document.getCategoryId();
	      if (main_core.Type.isPlainObject(robot.data.DialogContext) && robot.data.DialogContext.hasOwnProperty('addMenuGroup')) {
	        context['addMenuGroup'] = robot.data.DialogContext.addMenuGroup;
	      }
	      BX.ajax({
	        method: 'POST',
	        dataType: 'html',
	        url: main_core.Uri.addParam(babelHelpers.classPrivateFieldGet(this, _context).ajaxUrl, {
	          analyticsLabel: "automation_robot".concat(robot.draft ? '_draft' : '', "_settings_").concat(robot.data.Type.toLowerCase())
	        }),
	        data: {
	          ajax_action: 'get_robot_dialog',
	          document_signed: babelHelpers.classPrivateFieldGet(this, _context).signedDocument,
	          document_status: babelHelpers.classPrivateFieldGet(this, _context).document.getCurrentStatusId(),
	          context: context,
	          robot_json: Helper.toJsonString(robot.serialize()),
	          form_name: formName
	        },
	        onsuccess: function onsuccess(html) {
	          if (html) {
	            var dialogRows = main_core.Dom.create('div', {
	              html: html
	            });
	            form.appendChild(dialogRows);
	          }
	          _this4.showRobotSettingsPopup(robot, form, saveCallback);
	        }
	      });
	    }
	  }, {
	    key: "showRobotSettingsPopup",
	    value: function showRobotSettingsPopup(robot, form, saveCallback) {
	      var _this5 = this;
	      var popupMinWidth = 580;
	      var popupWidth = popupMinWidth;
	      if (babelHelpers.classPrivateFieldGet(this, _userOptions)) {
	        // TODO move from if?
	        this.emit('Template:robot:showSettings');
	        popupWidth = parseInt(babelHelpers.classPrivateFieldGet(this, _userOptions).get('defaults', 'robot_settings_popup_width', 580));
	      }
	      this.initRobotSettingsControls(robot, form);
	      if (robot.data.Type === 'CrmSendEmailActivity' || robot.data.Type === 'MailActivity' || robot.data.Type === 'RpaApproveActivity') {
	        popupMinWidth += 170;
	        if (popupWidth < popupMinWidth) {
	          popupWidth = popupMinWidth;
	        }
	      }
	      var robotTitle = main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SETTINGS_TITLE');
	      var descriptionTitle = main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SETTINGS_TITLE');
	      if (robot.hasTitle()) {
	        robotTitle = robot.getTitle();
	        descriptionTitle = robot.getDescriptionTitle();
	        if (descriptionTitle === 'untitled') {
	          descriptionTitle = robotTitle;
	        }
	      }
	      var titleBarContent = main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"popup-window-titlebar-text bizproc-automation-robot-settings-popup-titlebar\">\n\t\t\t\t<span class=\"bizproc-automation-robot-settings-popup-titlebar-text\">", "</span>\n\t\t\t\t<div class=\"ui-hint\">\n\t\t\t\t\t<span class=\"ui-hint-icon\" data-text=\"", "\"></span>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t"])), main_core.Text.encode(robotTitle), main_core.Text.encode(descriptionTitle));
	      HelpHint.bindAll(titleBarContent);
	      var me = this;
	      var popup = new BX.PopupWindow(Helper.generateUniqueId(), null, {
	        titleBar: {
	          content: titleBarContent
	        },
	        content: form,
	        closeIcon: true,
	        width: popupWidth,
	        resizable: {
	          minWidth: popupMinWidth,
	          minHeight: 100
	        },
	        offsetLeft: 0,
	        offsetTop: 0,
	        closeByEsc: true,
	        draggable: {
	          restrict: false
	        },
	        events: {
	          onPopupClose: function onPopupClose(popup) {
	            bizproc_automation.Designer.getInstance().setRobotSettingsDialog(null);
	            _this5.destroyRobotSettingsControls();
	            popup.destroy();
	            _this5.emit('Template:robot:closeSettings');
	          },
	          onPopupResize: function onPopupResize() {
	            _this5.onResizeRobotSettings();
	          },
	          onPopupResizeEnd: function onPopupResizeEnd() {
	            if (babelHelpers.classPrivateFieldGet(me, _userOptions)) {
	              babelHelpers.classPrivateFieldGet(me, _userOptions).set('defaults', 'robot_settings_popup_width', this.getWidth());
	            }
	          }
	        },
	        buttons: [new BX.PopupWindowButton({
	          text: main_core.Loc.getMessage('JS_CORE_WINDOW_SAVE'),
	          className: "popup-window-button-accept",
	          events: {
	            click: function click() {
	              var isNewRobot = robot.draft;
	              me.saveRobotSettings(form, robot, BX.delegate(function () {
	                this.popupWindow.close();
	                if (isNewRobot) {
	                  me.emit('Template:robot:add', {
	                    robot: robot
	                  });
	                }
	                if (saveCallback) {
	                  saveCallback(robot);
	                }
	              }, this), this.buttonNode);
	            }
	          }
	        }), new BX.PopupWindowButtonLink({
	          text: main_core.Loc.getMessage('JS_CORE_WINDOW_CANCEL'),
	          className: "popup-window-button-link-cancel",
	          events: {
	            click: function click() {
	              this.popupWindow.close();
	            }
	          }
	        })]
	      });
	      bizproc_automation.Designer.getInstance().getRobotSettingsDialog().popup = popup;
	      popup.show();
	    }
	  }, {
	    key: "initRobotSettingsControls",
	    value: function initRobotSettingsControls(robot, node) {
	      if (!main_core.Type.isArray(this.robotSettingsControls)) {
	        this.robotSettingsControls = [];
	      }
	      var controlNodes = node.querySelectorAll('[data-role]');
	      for (var i = 0; i < controlNodes.length; ++i) {
	        this.initRobotSettingsControl(robot, controlNodes[i]);
	      }
	    }
	  }, {
	    key: "initRobotSettingsControl",
	    value: function initRobotSettingsControl(robot, controlNode) {
	      var _this6 = this;
	      if (!main_core.Type.isArray(this.robotSettingsControls)) {
	        this.robotSettingsControls = [];
	      }
	      var role = controlNode.getAttribute('data-role');
	      var controlProps = {
	        context: new bizproc_automation.SelectorContext({
	          fields: main_core.Runtime.clone(babelHelpers.classPrivateFieldGet(this, _context).document.getFields()),
	          useSwitcherMenu: babelHelpers.classPrivateFieldGet(this, _context).get('showTemplatePropertiesMenuOnSelecting'),
	          rootGroupTitle: babelHelpers.classPrivateFieldGet(this, _context).document.title,
	          userOptions: babelHelpers.classPrivateFieldGet(this, _context).userOptions
	        }),
	        needSync: robot.draft,
	        checkbox: controlNode
	      };
	      if (role === bizproc_automation.SelectorManager.SELECTOR_ROLE_USER) {
	        var fieldProperty = JSON.parse(controlNode.getAttribute('data-property'));
	        controlProps.context.set('additionalUserFields', [].concat(babelHelpers.toConsumableArray(_classPrivateMethodGet$2(this, _getUserSelectorAdditionalFields, _getUserSelectorAdditionalFields2).call(this, fieldProperty)), babelHelpers.toConsumableArray(this.globalConstants.filter(function (constant) {
	          return constant['Type'] === 'user';
	        }).map(function (constant) {
	          return {
	            id: constant['Expression'],
	            title: constant['Name']
	          };
	        })), babelHelpers.toConsumableArray(this.globalVariables.filter(function (variable) {
	          return variable['Type'] === 'user';
	        }).map(function (variable) {
	          return {
	            id: variable['Expression'],
	            title: variable['Name']
	          };
	        }))));
	      } else if (role === bizproc_automation.SelectorManager.SELECTOR_ROLE_FILE) {
	        this.robots.forEach(function (robot) {
	          var _controlProps$context;
	          (_controlProps$context = controlProps.context.fields).push.apply(_controlProps$context, babelHelpers.toConsumableArray(robot.getReturnFieldsDescription().filter(function (field) {
	            return field['Type'] === 'file';
	          }).map(function (field) {
	            return {
	              Id: "{{~".concat(robot.getId(), ":").concat(field['Id'], "}}"),
	              Name: "".concat(robot.getTitle(), ": ").concat(field['Name']),
	              Type: 'file',
	              Expression: "{{~".concat(robot.getId(), ":").concat(field['Id'], "}}")
	            };
	          })));
	        });
	      }
	      var control = bizproc_automation.SelectorManager.createSelectorByRole(role, controlProps);
	      if (control && role !== bizproc_automation.SelectorManager.SELECTOR_ROLE_SAVE_STATE) {
	        control.renderTo(controlNode);
	        control.subscribe('onAskConstant', function (event) {
	          var _event$getData = event.getData(),
	            fieldProperty = _event$getData.fieldProperty;
	          control.onFieldSelect(_this6.addConstant(fieldProperty));
	        });
	        control.subscribe('onAskParameter', function (event) {
	          var _event$getData2 = event.getData(),
	            fieldProperty = _event$getData2.fieldProperty;
	          control.onFieldSelect(_this6.addParameter(fieldProperty));
	        });
	        control.subscribe('onOpenFieldMenu', function (event) {
	          return _this6.onOpenMenu(event, robot);
	        });
	        control.subscribe('onOpenMenu', function (event) {
	          return _this6.onOpenMenu(event, robot);
	        });
	      }
	      BX.UI.Hint.init(controlNode);
	      if (control) {
	        this.robotSettingsControls.push(control);
	      }
	    }
	  }, {
	    key: "destroyRobotSettingsControls",
	    value: function destroyRobotSettingsControls() {
	      if (this.conditionSelector) {
	        this.conditionSelector.destroy();
	        this.conditionSelector = null;
	      }
	      if (main_core.Type.isArray(this.robotSettingsControls)) {
	        for (var i = 0; i < this.robotSettingsControls.length; ++i) {
	          if (main_core.Type.isFunction(this.robotSettingsControls[i].destroy)) {
	            this.robotSettingsControls[i].destroy();
	          }
	        }
	      }
	      this.robotSettingsControls = null;
	    }
	  }, {
	    key: "onBeforeSaveRobotSettings",
	    value: function onBeforeSaveRobotSettings() {
	      if (main_core.Type.isArray(this.robotSettingsControls)) {
	        for (var i = 0; i < this.robotSettingsControls.length; ++i) {
	          if (main_core.Type.isFunction(this.robotSettingsControls[i].onBeforeSave)) {
	            this.robotSettingsControls[i].onBeforeSave();
	          }
	        }
	      }
	    }
	  }, {
	    key: "onResizeRobotSettings",
	    value: function onResizeRobotSettings() {
	      if (main_core.Type.isArray(this.robotSettingsControls)) {
	        for (var i = 0; i < this.robotSettingsControls.length; ++i) {
	          if (main_core.Type.isFunction(this.robotSettingsControls[i].onPopupResize)) {
	            this.robotSettingsControls[i].onPopupResize();
	          }
	        }
	      }
	    }
	  }, {
	    key: "renderDelaySettings",
	    value: function renderDelaySettings(robot) {
	      var delay = robot.getDelayInterval().clone();
	      var idSalt = Helper.generateUniqueId();
	      var delayTypeNode = main_core.Dom.create("input", {
	        attrs: {
	          type: "hidden",
	          name: "delay_type",
	          value: delay.type
	        }
	      });
	      var delayValueNode = main_core.Dom.create("input", {
	        attrs: {
	          type: "hidden",
	          name: "delay_value",
	          value: delay.value
	        }
	      });
	      var delayValueTypeNode = main_core.Dom.create("input", {
	        attrs: {
	          type: "hidden",
	          name: "delay_value_type",
	          value: delay.valueType
	        }
	      });
	      var delayBasisNode = main_core.Dom.create("input", {
	        attrs: {
	          type: "hidden",
	          name: "delay_basis",
	          value: delay.basis
	        }
	      });
	      var delayWorkTimeNode = main_core.Dom.create("input", {
	        attrs: {
	          type: "hidden",
	          name: "delay_worktime",
	          value: delay.workTime ? 1 : 0
	        }
	      });
	      var delayWaitWorkDayNode = main_core.Dom.create("input", {
	        attrs: {
	          type: "hidden",
	          name: "delay_wait_workday",
	          value: delay.waitWorkDay ? 1 : 0
	        }
	      });
	      var delayInTimeNode = main_core.Dom.create("input", {
	        attrs: {
	          type: "hidden",
	          name: "delay_in_time",
	          value: delay.inTimeString
	        }
	      });
	      var delayIntervalLabelNode = main_core.Dom.create("span", {
	        attrs: {
	          className: "bizproc-automation-popup-settings-link bizproc-automation-delay-interval-basis"
	        }
	      });
	      var basisFields = [];
	      var docFields = babelHelpers.classPrivateFieldGet(this, _context).document.getFields();
	      var minLimitM = babelHelpers.classPrivateFieldGet(this, _delayMinLimitM);
	      if (main_core.Type.isArray(docFields)) {
	        for (var i = 0; i < docFields.length; ++i) {
	          var field = docFields[i];
	          if (field['Type'] === 'date' || field['Type'] === 'datetime') {
	            basisFields.push(field);
	          }
	        }
	      }
	      var delayIntervalSelector = new bizproc_automation.DelayIntervalSelector({
	        labelNode: delayIntervalLabelNode,
	        onchange: function onchange(delay) {
	          delayTypeNode.value = delay.type;
	          delayValueNode.value = delay.value;
	          delayValueTypeNode.value = delay.valueType;
	          delayBasisNode.value = delay.basis;
	          delayWorkTimeNode.value = delay.workTime ? 1 : 0;
	          delayWaitWorkDayNode.value = delay.waitWorkDay ? 1 : 0;
	          delayInTimeNode.value = delay.inTimeString;
	        },
	        basisFields: basisFields,
	        minLimitM: minLimitM,
	        useAfterBasis: true,
	        showWaitWorkDay: true
	      });
	      var executeAfterPreviousBlock = null;
	      if (robot.hasTemplate()) {
	        var executeAfterPreviousCheckbox = main_core.Dom.create("input", {
	          attrs: {
	            type: "checkbox",
	            id: "param-group-3-1" + idSalt,
	            name: "execute_after_previous",
	            value: '1',
	            style: 'vertical-align: middle'
	          }
	        });
	        if (robot.isExecuteAfterPrevious()) {
	          executeAfterPreviousCheckbox.setAttribute('checked', 'checked');
	        }
	        executeAfterPreviousBlock = main_core.Dom.create("div", {
	          attrs: {
	            className: "bizproc-automation-popup-settings-block"
	          },
	          children: [executeAfterPreviousCheckbox, main_core.Dom.create("label", {
	            attrs: {
	              "for": "param-group-3-1" + idSalt,
	              style: 'color: #535C69'
	            },
	            text: main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_AFTER_PREVIOUS_WIDE')
	          })]
	        });
	      }
	      var div = main_core.Dom.create("div", {
	        attrs: {
	          className: "bizproc-automation-popup-settings bizproc-automation-popup-settings-flex"
	        },
	        children: [main_core.Dom.create("div", {
	          attrs: {
	            className: "bizproc-automation-popup-settings-block bizproc-automation-popup-settings-block-flex"
	          },
	          children: [main_core.Dom.create("span", {
	            attrs: {
	              className: "bizproc-automation-popup-settings-title-wrapper"
	            },
	            children: [delayTypeNode, delayValueNode, delayValueTypeNode, delayBasisNode, delayWorkTimeNode, delayWaitWorkDayNode, delayInTimeNode, main_core.Dom.create("span", {
	              attrs: {
	                className: "bizproc-automation-popup-settings-title bizproc-automation-popup-settings-title-left"
	              },
	              text: main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_TO_EXECUTE') + ":"
	            }), delayIntervalLabelNode]
	          })]
	        }), executeAfterPreviousBlock]
	      });
	      delayIntervalSelector.init(delay);
	      return div;
	    }
	  }, {
	    key: "setDelaySettingsFromForm",
	    value: function setDelaySettingsFromForm(formFields, robot) {
	      var delay = new DelayInterval();
	      delay.setType(formFields['delay_type']);
	      delay.setValue(formFields['delay_value']);
	      delay.setValueType(formFields['delay_value_type']);
	      delay.setBasis(formFields['delay_basis']);
	      delay.setWorkTime(formFields['delay_worktime'] === '1');
	      delay.setWaitWorkDay(formFields['delay_wait_workday'] === '1');
	      delay.setInTime(formFields['delay_in_time'] ? formFields['delay_in_time'].split(':') : null);
	      robot.setDelayInterval(delay);
	      if (robot.hasTemplate()) {
	        robot.setExecuteAfterPrevious(formFields['execute_after_previous'] && formFields['execute_after_previous'] === '1');
	      }
	      return this;
	    }
	  }, {
	    key: "renderConditionSettings",
	    value: function renderConditionSettings(robot) {
	      var _this7 = this;
	      var conditionGroup = robot.getCondition();
	      this.conditionSelector = new bizproc_automation.ConditionGroupSelector(conditionGroup, {
	        fields: babelHelpers.classPrivateFieldGet(this, _context).document.getFields(),
	        onOpenFieldMenu: function onOpenFieldMenu(event) {
	          return _this7.onOpenMenu(event, robot);
	        },
	        onOpenMenu: function onOpenMenu(event) {
	          return _this7.onOpenMenu(event, robot);
	        }
	      });
	      return main_core.Dom.create("div", {
	        attrs: {
	          className: "bizproc-automation-popup-settings"
	        },
	        children: [main_core.Dom.create("div", {
	          attrs: {
	            className: "bizproc-automation-popup-settings-block"
	          },
	          children: [main_core.Dom.create("span", {
	            attrs: {
	              className: "bizproc-automation-popup-settings-title"
	            },
	            text: main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_CONDITION') + ":"
	          }), this.conditionSelector.createNode()]
	        })]
	      });
	    }
	  }, {
	    key: "onOpenMenu",
	    value: function onOpenMenu(event, robot) {
	      _classPrivateMethodGet$2(this, _addRobotReturnFieldsToSelector, _addRobotReturnFieldsToSelector2).call(this, event, robot);
	      _classPrivateMethodGet$2(this, _addConstantsToSelector, _addConstantsToSelector2).call(this, event);
	      _classPrivateMethodGet$2(this, _addVariablesToSelector, _addVariablesToSelector2).call(this, event);
	      this.emit('Template:onSelectorMenuOpen', _objectSpread$2({
	        template: this,
	        robot: robot
	      }, event.getData()));
	    }
	  }, {
	    key: "setConditionSettingsFromForm",
	    value: function setConditionSettingsFromForm(formFields, robot) {
	      robot.setCondition(bizproc_automation.ConditionGroup.createFromForm(formFields));
	      return this;
	    }
	  }, {
	    key: "renderBrokenLinkAlert",
	    value: function renderBrokenLinkAlert() {
	      var brokenLinks = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : [];
	      var moreInfoNode = main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"bizproc-automation-robot-broken-link-full-info\">\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"])), brokenLinks.map(function (value) {
	        return main_core.Text.encode(value);
	      }).join('<br>'));
	      var showMoreLabel = main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<span class=\"bizproc-automation-robot-broken-link-show-more\">\n\t\t\t\t", "\n\t\t\t</span>\n\t\t"])), main_core.Loc.getMessage('JS_BIZPROC_AUTOMATION_BROKEN_LINK_MESSAGE_ERROR_MORE_INFO'));
	      main_core.Event.bindOnce(showMoreLabel, 'click', function () {
	        main_core.Dom.style(moreInfoNode, 'height', moreInfoNode.scrollHeight + 'px');
	        main_core.Dom.remove(showMoreLabel);
	      });
	      var closeBtn = main_core.Tag.render(_templateObject4 || (_templateObject4 = babelHelpers.taggedTemplateLiteral(["<span class=\"ui-alert-close-btn\"></span>"])));
	      var alert = main_core.Tag.render(_templateObject5 || (_templateObject5 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"ui-alert ui-alert-warning ui-alert-icon-info\">\n\t\t\t\t<div class=\"ui-alert-message\">\n\t\t\t\t\t<div>\n\t\t\t\t\t\t<span>", "</span>\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"])), main_core.Loc.getMessage('BIZPROC_AUTOMATION_BROKEN_LINK_MESSAGE_ERROR'), showMoreLabel, moreInfoNode, closeBtn);
	      main_core.Event.bindOnce(closeBtn, 'click', function () {
	        main_core.Dom.remove(alert);
	      });
	      return alert;
	    }
	  }, {
	    key: "saveRobotSettings",
	    value: function saveRobotSettings(form, robot, callback, btnNode) {
	      var _this8 = this;
	      if (btnNode) {
	        btnNode.classList.add('popup-window-button-wait');
	      }
	      this.onBeforeSaveRobotSettings();
	      var formData = BX.ajax.prepareForm(form);
	      var ajaxUrl = babelHelpers.classPrivateFieldGet(this, _context).ajaxUrl;
	      var documentSigned = babelHelpers.classPrivateFieldGet(this, _context).signedDocument;
	      BX.ajax({
	        method: 'POST',
	        dataType: 'json',
	        url: main_core.Uri.addParam(ajaxUrl, {
	          analyticsLabel: "automation_robot".concat(robot.draft ? '_draft' : '', "_save_").concat(robot.data.Type.toLowerCase())
	        }),
	        data: {
	          ajax_action: 'save_robot_settings',
	          document_signed: documentSigned,
	          robot_json: Helper.toJsonString(robot.serialize()),
	          form_data_json: Helper.toJsonString(formData['data']),
	          form_data: formData['data'] /** @bug 0135641 */
	        },

	        onsuccess: function onsuccess(response) {
	          if (btnNode) {
	            btnNode.classList.remove('popup-window-button-wait');
	          }
	          if (response.SUCCESS) {
	            robot.updateData(response.DATA.robot);
	            _this8.setDelaySettingsFromForm(formData['data'], robot);
	            _this8.setConditionSettingsFromForm(formData['data'], robot);
	            robot.draft = false;
	            robot.reInit();
	            _this8.markModified();
	            if (callback) {
	              callback(response.DATA);
	            }
	          } else {
	            alert(response.ERRORS[0]);
	          }
	        }
	      });
	    }
	  }, {
	    key: "serialize",
	    value: function serialize() {
	      var data = BX.clone(babelHelpers.classPrivateFieldGet(this, _data$2));
	      data['IS_EXTERNAL_MODIFIED'] = this.isExternalModified() ? 1 : 0;
	      data['ROBOTS'] = [];
	      for (var i = 0; i < babelHelpers.classPrivateFieldGet(this, _robots).length; ++i) {
	        data['ROBOTS'].push(babelHelpers.classPrivateFieldGet(this, _robots)[i].serialize());
	      }
	      return data;
	    }
	  }, {
	    key: "isExternalModified",
	    value: function isExternalModified() {
	      return this.externalModified === true;
	    }
	  }, {
	    key: "markExternalModified",
	    value: function markExternalModified(modified) {
	      this.externalModified = modified !== false;
	    }
	  }, {
	    key: "getRobotById",
	    value: function getRobotById(id) {
	      return babelHelpers.classPrivateFieldGet(this, _robots).find(function (robot) {
	        return robot.getId() === id;
	      });
	    }
	  }, {
	    key: "isModified",
	    value: function isModified() {
	      return this.modified;
	    }
	  }, {
	    key: "markModified",
	    value: function markModified(modified) {
	      this.modified = modified !== false;
	      if (this.modified) {
	        this.emit('Template:modified');
	      }
	    }
	  }, {
	    key: "getConstants",
	    value: function getConstants() {
	      var _this9 = this;
	      var constants = [];
	      Object.keys(babelHelpers.classPrivateFieldGet(this, _data$2).CONSTANTS).forEach(function (id) {
	        var constant = main_core.Runtime.clone(babelHelpers.classPrivateFieldGet(_this9, _data$2).CONSTANTS[id]);
	        constant.Id = id;
	        constant.ObjectId = 'Constant';
	        constant.SystemExpression = '{=Constant:' + id + '}';
	        constant.Expression = '{{~&:' + id + '}}';
	        constants.push(constant);
	      });
	      return constants;
	    }
	  }, {
	    key: "getConstant",
	    value: function getConstant(id) {
	      var constants = this.getConstants();
	      for (var i = 0; i < constants.length; ++i) {
	        if (constants[i].Id === id) {
	          return constants[i];
	        }
	      }
	      return null;
	    }
	  }, {
	    key: "addConstant",
	    value: function addConstant(property) {
	      var id = property.Id || this.generatePropertyId('Constant', babelHelpers.classPrivateFieldGet(this, _data$2).CONSTANTS);
	      if (babelHelpers.classPrivateFieldGet(this, _data$2).CONSTANTS[id]) {
	        throw "Constant with id \"".concat(id, "\" is already exists");
	      }
	      babelHelpers.classPrivateFieldGet(this, _data$2).CONSTANTS[id] = property;
	      this.emit('Template:constant:add');
	      // if (this.component)
	      // {
	      // 	BX.onCustomEvent(this.component, 'onTemplateConstantAdd', [this, this.getConstant(id)]);
	      // }

	      return this.getConstant(id);
	    }
	  }, {
	    key: "updateConstant",
	    value: function updateConstant(id, property) {
	      if (!babelHelpers.classPrivateFieldGet(this, _data$2).CONSTANTS[id]) {
	        throw "Constant with id \"".concat(id, "\" does not exists");
	      }

	      //TODO: only Description yet.
	      babelHelpers.classPrivateFieldGet(this, _data$2).CONSTANTS[id].Description = property.Description;
	      this.emit('Template:constant:update', {
	        constant: this.getConstant(id)
	      });
	      // if (this.component)
	      // {
	      // 	BX.onCustomEvent(this.component, 'onTemplateConstantUpdate', [this, this.getConstant(id)]);
	      // }

	      return this.getConstant(id);
	    }
	  }, {
	    key: "deleteConstant",
	    value: function deleteConstant(id) {
	      delete babelHelpers.classPrivateFieldGet(this, _data$2).CONSTANTS[id];
	      return true;
	    }
	  }, {
	    key: "setConstantValue",
	    value: function setConstantValue(id, value) {
	      if (babelHelpers.classPrivateFieldGet(this, _data$2).CONSTANTS[id]) {
	        babelHelpers.classPrivateFieldGet(this, _data$2).CONSTANTS[id]['Default'] = value;
	        return true;
	      }
	      return false;
	    }
	  }, {
	    key: "getParameters",
	    value: function getParameters() {
	      var _this10 = this;
	      var params = [];
	      Object.keys(babelHelpers.classPrivateFieldGet(this, _data$2).PARAMETERS).forEach(function (id) {
	        var param = BX.clone(babelHelpers.classPrivateFieldGet(_this10, _data$2).PARAMETERS[id]);
	        param.Id = id;
	        param.ObjectId = 'Template';
	        param.SystemExpression = '{=Template:' + id + '}';
	        param.Expression = '{{~*:' + id + '}}';
	        params.push(param);
	      });
	      return params;
	    }
	  }, {
	    key: "getParameter",
	    value: function getParameter(id) {
	      var params = this.getParameters();
	      for (var i = 0; i < params.length; ++i) {
	        if (params[i].Id === id) {
	          return params[i];
	        }
	      }
	      return null;
	    }
	  }, {
	    key: "addParameter",
	    value: function addParameter(property) {
	      var id = property.Id || this.generatePropertyId('Parameter', babelHelpers.classPrivateFieldGet(this, _data$2).PARAMETERS);
	      if (babelHelpers.classPrivateFieldGet(this, _data$2).PARAMETERS[id]) {
	        throw "Parameter with id \"".concat(id, "\" is already exists");
	      }
	      babelHelpers.classPrivateFieldGet(this, _data$2).PARAMETERS[id] = property;
	      this.emit('Template:parameter:add', {
	        parameter: this.getParameter(id)
	      });
	      // if (this.component)
	      // {
	      // 	BX.onCustomEvent(this.component, 'onTemplateParameterAdd', [this, this.getParameter(id)]);
	      // }

	      return this.getParameter(id);
	    }
	  }, {
	    key: "updateParameter",
	    value: function updateParameter(id, property) {
	      if (!babelHelpers.classPrivateFieldGet(this, _data$2).PARAMETERS[id]) {
	        throw "Parameter with id \"".concat(id, "\" does not exists");
	      }

	      //TODO: only Description yet.
	      babelHelpers.classPrivateFieldGet(this, _data$2).PARAMETERS[id].Description = property.Description;
	      this.emit('Template:parameter:update', {
	        parameter: this.getParameter(id)
	      });
	      // if (this.component)
	      // {
	      // 	BX.onCustomEvent(this.component, 'onTemplateParameterUpdate', [this, this.getParameter(id)]);
	      // }

	      return this.getParameter(id);
	    }
	  }, {
	    key: "deleteParameter",
	    value: function deleteParameter(id) {
	      delete babelHelpers.classPrivateFieldGet(this, _data$2).PARAMETERS[id];
	      return true;
	    }
	  }, {
	    key: "setParameterValue",
	    value: function setParameterValue(id, value) {
	      if (babelHelpers.classPrivateFieldGet(this, _data$2).PARAMETERS[id]) {
	        babelHelpers.classPrivateFieldGet(this, _data$2).PARAMETERS[id]['Default'] = value;
	        return true;
	      }
	      return false;
	    }
	  }, {
	    key: "getVariables",
	    value: function getVariables() {
	      var _this11 = this;
	      var variables = [];
	      Object.keys(babelHelpers.classPrivateFieldGet(this, _data$2).VARIABLES).forEach(function (id) {
	        var variable = main_core.Runtime.clone(babelHelpers.classPrivateFieldGet(_this11, _data$2).VARIABLES[id]);
	        variable.Id = id;
	        variable.ObjectId = 'Variable';
	        variable.SystemExpression = '{=Variable:' + id + '}';
	        variable.Expression = '{=Variable:' + id + '}';
	        variables.push(variable);
	      });
	      return variables;
	    }
	  }, {
	    key: "generatePropertyId",
	    value: function generatePropertyId(prefix, existsList) {
	      var index;
	      for (index = 1; index <= 1000; ++index) {
	        if (!existsList[prefix + index]) {
	          break; //found
	        }
	      }

	      return prefix + index;
	    }
	  }, {
	    key: "collectUsages",
	    value: function collectUsages() {
	      var usages = {
	        Document: new Set(),
	        Constant: new Set(),
	        Variable: new Set(),
	        Parameter: new Set(),
	        GlobalConstant: new Set(),
	        GlobalVariable: new Set(),
	        Activity: new Set()
	      };
	      babelHelpers.classPrivateFieldGet(this, _robots).forEach(function (robot) {
	        var robotUsages = robot.collectUsages();
	        Object.keys(usages).forEach(function (key) {
	          robotUsages[key].forEach(function (usage) {
	            if (!usages[key].has(usage)) {
	              usages[key].add(usage);
	            }
	          });
	        });
	      });
	      return usages;
	    }
	  }, {
	    key: "subscribeRobotEvents",
	    value: function subscribeRobotEvents(eventName, listener) {
	      babelHelpers.classPrivateFieldGet(this, _robots).forEach(function (robot) {
	        return robot.subscribe(eventName, listener);
	      });
	      return this;
	    }
	  }, {
	    key: "unsubscribeRobotEvents",
	    value: function unsubscribeRobotEvents(eventName, listener) {
	      babelHelpers.classPrivateFieldGet(this, _robots).forEach(function (robot) {
	        return robot.unsubscribe(eventName, listener);
	      });
	      return this;
	    }
	  }, {
	    key: "getRobotDescription",
	    value: function getRobotDescription(type) {
	      return babelHelpers.classPrivateFieldGet(this, _context).availableRobots.find(function (item) {
	        return item['CLASS'] === type;
	      });
	    }
	  }, {
	    key: "robots",
	    get: function get() {
	      return babelHelpers.classPrivateFieldGet(this, _robots);
	    }
	  }, {
	    key: "userOptions",
	    get: function get() {
	      return babelHelpers.classPrivateFieldGet(this, _userOptions);
	    }
	  }, {
	    key: "globalConstants",
	    get: function get() {
	      return babelHelpers.classPrivateFieldGet(this, _context).automationGlobals ? babelHelpers.classPrivateFieldGet(this, _context).automationGlobals.globalConstants : [];
	    }
	  }, {
	    key: "globalVariables",
	    get: function get() {
	      return babelHelpers.classPrivateFieldGet(this, _context).automationGlobals ? babelHelpers.classPrivateFieldGet(this, _context).automationGlobals.globalVariables : [];
	    }
	  }], [{
	    key: "copyRobotTo",
	    value: function copyRobotTo(dstTemplate, robot, beforeRobot) {
	      var copiedRobot = robot.copyTo(dstTemplate, beforeRobot);
	      dstTemplate.emit('Template:robot:add', {
	        robot: copiedRobot
	      });
	    }
	  }]);
	  return Template;
	}(main_core_events.EventEmitter);
	function _getUserSelectorAdditionalFields2(fieldProperty) {
	  var additionalFields = _classPrivateMethodGet$2(this, _getRobotsWithReturnFields, _getRobotsWithReturnFields2).call(this).map(function (robot) {
	    return robot.getReturnFieldsDescription().filter(function (field) {
	      return field['Type'] === 'user';
	    }).map(function (field) {
	      return {
	        id: "{{~".concat(robot.getId(), ":").concat(field['Id'], "}}"),
	        title: "".concat(robot.getTitle(), ": ").concat(field['Name'])
	      };
	    });
	  }).flat();
	  if (babelHelpers.classPrivateFieldGet(this, _context).get('showTemplatePropertiesMenuOnSelecting') && fieldProperty) {
	    var ask = this.addConstant(main_core.Runtime.clone(fieldProperty));
	    additionalFields.push({
	      id: ask.Expression,
	      title: main_core.Loc.getMessage('BIZPROC_AUTOMATION_ASK_CONSTANT'),
	      tabs: ['recents', 'bpuserroles'],
	      sort: 1
	    });
	    var param = this.addParameter(main_core.Runtime.clone(fieldProperty));
	    additionalFields.push({
	      id: param.Expression,
	      title: main_core.Loc.getMessage('BIZPROC_AUTOMATION_ASK_PARAMETER'),
	      tabs: ['recents', 'bpuserroles'],
	      sort: 2
	    });
	  }
	  return additionalFields;
	}
	function _addRobotReturnFieldsToSelector2(event, skipRobot) {
	  var selector = event.getData().selector;
	  var isMixedCondition = event.getData().isMixedCondition;
	  if (main_core.Type.isBoolean(isMixedCondition) && !isMixedCondition) {
	    return;
	  }
	  var robotMenuItems = _classPrivateMethodGet$2(this, _getRobotsWithReturnFields, _getRobotsWithReturnFields2).call(this, skipRobot).map(function (robot) {
	    return {
	      id: robot.getId(),
	      title: robot.getTitle(),
	      children: robot.getReturnFieldsDescription().map(function (field) {
	        return {
	          id: field.Expression,
	          title: field.Name,
	          customData: {
	            field: field
	          }
	        };
	      })
	    };
	  });
	  if (robotMenuItems.length > 0) {
	    selector.addGroup('__RESULT', {
	      id: '__RESULT',
	      title: main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_ROBOT_LIST'),
	      children: robotMenuItems
	    });
	  }
	}
	function _addConstantsToSelector2(event) {
	  var selector = event.getData().selector;
	  var isMixedCondition = event.getData().isMixedCondition;
	  if (main_core.Type.isBoolean(isMixedCondition) && !isMixedCondition) {
	    return;
	  }

	  // TODO - test !this.showTemplatePropertiesMenuOnSelecting
	  var constants = this.getConstants().map(function (constant) {
	    return {
	      id: constant.SystemExpression,
	      title: constant.Name,
	      supertitle: main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_TEMPLATE_CONSTANTS_LIST'),
	      customData: {
	        field: constant
	      }
	    };
	  });
	  this.globalConstants.forEach(function (constant) {
	    constants.push({
	      id: constant.SystemExpression,
	      title: constant['Name'],
	      supertitle: constant.SuperTitle,
	      customData: {
	        field: constant
	      }
	    });
	  });
	  if (main_core.Type.isArrayFilled(constants)) {
	    selector.addGroup('__CONSTANTS', {
	      id: '__CONSTANTS',
	      title: main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_CONSTANTS_LIST'),
	      children: constants
	    });
	  }
	}
	function _addVariablesToSelector2(event) {
	  var selector = event.getData().selector;
	  var isMixedCondition = event.getData().isMixedCondition;
	  if (main_core.Type.isBoolean(isMixedCondition) && !isMixedCondition) {
	    return;
	  }
	  var gVariables = this.globalVariables.map(function (variable) {
	    return {
	      id: variable.SystemExpression,
	      title: variable.Name,
	      supertitle: variable.SuperTitle,
	      customData: {
	        field: variable
	      }
	    };
	  });
	  if (main_core.Type.isArrayFilled(gVariables)) {
	    selector.addGroup('__GLOB_VARIABLES', {
	      id: '__GLOB_VARIABLES',
	      title: main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_GLOB_VARIABLES_LIST_1'),
	      children: gVariables
	    });
	  }
	}
	function _getRobotsWithReturnFields2() {
	  var skipRobot = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : undefined;
	  var skipId = (skipRobot === null || skipRobot === void 0 ? void 0 : skipRobot.getId()) || '';
	  return this.robots.filter(function (templateRobot) {
	    return templateRobot.getId() !== skipId && templateRobot.hasReturnFields();
	  });
	}

	function _classPrivateFieldInitSpec$f(obj, privateMap, value) { _checkPrivateRedeclaration$f(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$f(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	var _object = /*#__PURE__*/new WeakMap();
	var _field = /*#__PURE__*/new WeakMap();
	var _operator = /*#__PURE__*/new WeakMap();
	var _value$1 = /*#__PURE__*/new WeakMap();
	var Condition = /*#__PURE__*/function () {
	  function Condition(params, group) {
	    babelHelpers.classCallCheck(this, Condition);
	    _classPrivateFieldInitSpec$f(this, _object, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$f(this, _field, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$f(this, _operator, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$f(this, _value$1, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldSet(this, _object, 'Document');
	    babelHelpers.classPrivateFieldSet(this, _field, '');
	    babelHelpers.classPrivateFieldSet(this, _operator, '!empty');
	    babelHelpers.classPrivateFieldSet(this, _value$1, '');
	    this.parentGroup = null;
	    if (main_core.Type.isPlainObject(params)) {
	      if (params['object']) {
	        this.setObject(params['object']);
	      }
	      if (params['field']) {
	        this.setField(params['field']);
	      }
	      if (params['operator']) {
	        this.setOperator(params['operator']);
	      }
	      if ('value' in params) {
	        this.setValue(params['value']);
	      }
	    }
	    if (group) {
	      this.parentGroup = group;
	    }
	  }
	  babelHelpers.createClass(Condition, [{
	    key: "clone",
	    value: function clone() {
	      return new Condition({
	        object: babelHelpers.classPrivateFieldGet(this, _object),
	        field: babelHelpers.classPrivateFieldGet(this, _field),
	        operator: babelHelpers.classPrivateFieldGet(this, _operator),
	        value: babelHelpers.classPrivateFieldGet(this, _value$1)
	      }, this.parentGroup);
	    }
	  }, {
	    key: "setObject",
	    value: function setObject(object) {
	      if (main_core.Type.isStringFilled(object)) {
	        babelHelpers.classPrivateFieldSet(this, _object, object);
	      }
	    }
	  }, {
	    key: "setField",
	    value: function setField(field) {
	      if (main_core.Type.isStringFilled(field)) {
	        babelHelpers.classPrivateFieldSet(this, _field, field);
	      }
	    }
	  }, {
	    key: "setOperator",
	    value: function setOperator(operator) {
	      if (!operator) {
	        operator = bizproc_condition.Operator.EQUAL;
	      }
	      babelHelpers.classPrivateFieldSet(this, _operator, operator);
	    }
	  }, {
	    key: "setValue",
	    value: function setValue(value) {
	      babelHelpers.classPrivateFieldSet(this, _value$1, value);
	      if (babelHelpers.classPrivateFieldGet(this, _operator) === bizproc_condition.Operator.EQUAL && babelHelpers.classPrivateFieldGet(this, _value$1) === '') {
	        babelHelpers.classPrivateFieldSet(this, _operator, 'empty');
	      } else if (babelHelpers.classPrivateFieldGet(this, _operator) === bizproc_condition.Operator.NOT_EQUAL && babelHelpers.classPrivateFieldGet(this, _value$1) === '') {
	        babelHelpers.classPrivateFieldSet(this, _operator, '!empty');
	      }
	    }
	  }, {
	    key: "serialize",
	    value: function serialize() {
	      return {
	        object: babelHelpers.classPrivateFieldGet(this, _object),
	        field: babelHelpers.classPrivateFieldGet(this, _field),
	        operator: babelHelpers.classPrivateFieldGet(this, _operator),
	        value: babelHelpers.classPrivateFieldGet(this, _value$1)
	      };
	    }
	  }, {
	    key: "object",
	    get: function get() {
	      return babelHelpers.classPrivateFieldGet(this, _object);
	    }
	  }, {
	    key: "field",
	    get: function get() {
	      return babelHelpers.classPrivateFieldGet(this, _field);
	    }
	  }, {
	    key: "operator",
	    get: function get() {
	      return babelHelpers.classPrivateFieldGet(this, _operator);
	    }
	  }, {
	    key: "value",
	    get: function get() {
	      return babelHelpers.classPrivateFieldGet(this, _value$1);
	    }
	  }]);
	  return Condition;
	}();

	function _classPrivateFieldInitSpec$g(obj, privateMap, value) { _checkPrivateRedeclaration$g(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$g(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	var _type$2 = /*#__PURE__*/new WeakMap();
	var _items = /*#__PURE__*/new WeakMap();
	var _activityNames = /*#__PURE__*/new WeakMap();
	var ConditionGroup = /*#__PURE__*/function () {
	  function ConditionGroup(params) {
	    var _this = this;
	    babelHelpers.classCallCheck(this, ConditionGroup);
	    _classPrivateFieldInitSpec$g(this, _type$2, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$g(this, _items, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$g(this, _activityNames, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldSet(this, _type$2, ConditionGroup.CONDITION_TYPE.Field);
	    babelHelpers.classPrivateFieldSet(this, _items, []);
	    if (main_core.Type.isPlainObject(params)) {
	      if (params['type']) {
	        babelHelpers.classPrivateFieldSet(this, _type$2, params['type']);
	      }
	      if (main_core.Type.isArray(params['items'])) {
	        params['items'].forEach(function (item) {
	          var condition = new Condition(item[0], _this);
	          _this.addItem(condition, item[1]);
	        });
	      }
	      if (main_core.Type.isPlainObject(params['activityNames'])) {
	        babelHelpers.classPrivateFieldSet(this, _activityNames, params['activityNames']);
	      }
	    }
	  }
	  babelHelpers.createClass(ConditionGroup, [{
	    key: "clone",
	    value: function clone() {
	      var clonedGroup = new ConditionGroup({
	        type: babelHelpers.classPrivateFieldGet(this, _type$2)
	      });
	      babelHelpers.classPrivateFieldGet(this, _items).forEach(function (_ref) {
	        var _ref2 = babelHelpers.slicedToArray(_ref, 2),
	          condition = _ref2[0],
	          joiner = _ref2[1];
	        var clonedCondition = condition.clone();
	        clonedCondition.parentGroup = clonedGroup;
	        clonedGroup.addItem(clonedCondition, joiner);
	      });
	      return clonedGroup;
	    }
	  }, {
	    key: "addItem",
	    value: function addItem(condition, joiner) {
	      babelHelpers.classPrivateFieldGet(this, _items).push([condition, joiner]);
	    }
	  }, {
	    key: "getItems",
	    value: function getItems() {
	      return babelHelpers.classPrivateFieldGet(this, _items);
	    }
	  }, {
	    key: "serialize",
	    value: function serialize() {
	      var itemsArray = [];
	      babelHelpers.classPrivateFieldGet(this, _items).forEach(function (item) {
	        if (item.field !== '') {
	          itemsArray.push([item[0].serialize(), item[1]]);
	        }
	      });
	      return {
	        type: babelHelpers.classPrivateFieldGet(this, _type$2),
	        items: itemsArray,
	        activityNames: babelHelpers.classPrivateFieldGet(this, _activityNames)
	      };
	    }
	  }, {
	    key: "conditionNamesList",
	    get: function get() {
	      if (main_core.Type.isPlainObject(babelHelpers.classPrivateFieldGet(this, _activityNames))) {
	        return [babelHelpers.classPrivateFieldGet(this, _activityNames).Activity, babelHelpers.classPrivateFieldGet(this, _activityNames).Branch1, babelHelpers.classPrivateFieldGet(this, _activityNames).Branch2];
	      }
	      return [];
	    }
	  }, {
	    key: "type",
	    get: function get() {
	      return babelHelpers.classPrivateFieldGet(this, _type$2);
	    },
	    set: function set(type) {
	      if (Object.values(ConditionGroup.CONDITION_TYPE).includes(type)) {
	        babelHelpers.classPrivateFieldSet(this, _type$2, type);
	      }
	      return this;
	    }
	  }, {
	    key: "items",
	    get: function get() {
	      return babelHelpers.classPrivateFieldGet(this, _items);
	    }
	  }], [{
	    key: "createFromForm",
	    value: function createFromForm(formFields, prefix) {
	      var conditionGroup = new ConditionGroup();
	      if (!prefix) {
	        prefix = 'condition_';
	      }
	      if (main_core.Type.isArray(formFields[prefix + 'field'])) {
	        for (var i = 0, valueIndex = 0; i < formFields[prefix + 'field'].length; ++i, ++valueIndex) {
	          if (formFields[prefix + 'field'][i] === '') {
	            continue;
	          }
	          var condition = new Condition({}, conditionGroup);
	          condition.setObject(formFields[prefix + 'object'][i]);
	          condition.setField(formFields[prefix + 'field'][i]);
	          condition.setOperator(formFields[prefix + 'operator'][i]);
	          var value = condition.operator === bizproc_condition.Operator.BETWEEN ? [formFields[prefix + 'value'][valueIndex], formFields[prefix + 'value'][valueIndex + 1]] : formFields[prefix + 'value'][valueIndex];
	          condition.setValue(value);
	          var joiner = ConditionGroup.JOINER.And;
	          if (formFields[prefix + 'joiner'] && formFields[prefix + 'joiner'][i] === ConditionGroup.JOINER.Or) {
	            joiner = ConditionGroup.JOINER.Or;
	          }
	          if (condition.operator === bizproc_condition.Operator.BETWEEN) {
	            valueIndex++;
	          }
	          conditionGroup.addItem(condition, joiner);
	        }
	      }
	      return conditionGroup;
	    }
	  }]);
	  return ConditionGroup;
	}();
	babelHelpers.defineProperty(ConditionGroup, "CONDITION_TYPE", {
	  Field: 'field',
	  Mixed: 'mixed'
	});
	babelHelpers.defineProperty(ConditionGroup, "JOINER", {
	  And: 'AND',
	  Or: 'OR',
	  message: function message(type) {
	    if (type === this.Or) {
	      return main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_CONDITION_OR');
	    }
	    return main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_CONDITION_AND');
	  }
	});

	var _templateObject$1, _templateObject2$1, _templateObject3$1, _templateObject4$1, _templateObject5$1, _templateObject6;
	function _classPrivateMethodInitSpec$3(obj, privateSet) { _checkPrivateRedeclaration$h(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec$h(obj, privateMap, value) { _checkPrivateRedeclaration$h(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$h(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet$3(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _condition$2 = /*#__PURE__*/new WeakMap();
	var _fields$1 = /*#__PURE__*/new WeakMap();
	var _joiner = /*#__PURE__*/new WeakMap();
	var _fieldPrefix = /*#__PURE__*/new WeakMap();
	var _rootGroupTitle = /*#__PURE__*/new WeakMap();
	var _onOpenFieldMenu = /*#__PURE__*/new WeakMap();
	var _onOpenMenu = /*#__PURE__*/new WeakMap();
	var _showValuesSelector = /*#__PURE__*/new WeakMap();
	var _valueNode = /*#__PURE__*/new WeakMap();
	var _selectedField = /*#__PURE__*/new WeakMap();
	var _createValueNode = /*#__PURE__*/new WeakSet();
	var _getValueLabel = /*#__PURE__*/new WeakSet();
	var _getValueNode = /*#__PURE__*/new WeakSet();
	var ConditionSelector = /*#__PURE__*/function () {
	  function ConditionSelector(condition, options) {
	    babelHelpers.classCallCheck(this, ConditionSelector);
	    _classPrivateMethodInitSpec$3(this, _getValueNode);
	    _classPrivateMethodInitSpec$3(this, _getValueLabel);
	    _classPrivateMethodInitSpec$3(this, _createValueNode);
	    _classPrivateFieldInitSpec$h(this, _condition$2, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$h(this, _fields$1, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$h(this, _joiner, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$h(this, _fieldPrefix, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$h(this, _rootGroupTitle, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$h(this, _onOpenFieldMenu, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$h(this, _onOpenMenu, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$h(this, _showValuesSelector, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$h(this, _valueNode, {
	      writable: true,
	      value: null
	    });
	    _classPrivateFieldInitSpec$h(this, _selectedField, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldSet(this, _condition$2, condition);
	    babelHelpers.classPrivateFieldSet(this, _fields$1, []);
	    babelHelpers.classPrivateFieldSet(this, _joiner, bizproc_automation.ConditionGroup.JOINER.And);
	    babelHelpers.classPrivateFieldSet(this, _fieldPrefix, 'condition_');
	    if (main_core.Type.isPlainObject(options)) {
	      var _options$showValuesSe;
	      if (main_core.Type.isArray(options.fields)) {
	        babelHelpers.classPrivateFieldSet(this, _fields$1, options.fields.map(function (field) {
	          field.ObjectId = 'Document';
	          return field;
	        }));
	      }
	      if (options.joiner && options.joiner === bizproc_automation.ConditionGroup.JOINER.Or) {
	        babelHelpers.classPrivateFieldSet(this, _joiner, bizproc_automation.ConditionGroup.JOINER.Or);
	      }
	      if (options.fieldPrefix) {
	        babelHelpers.classPrivateFieldSet(this, _fieldPrefix, options.fieldPrefix);
	      }
	      babelHelpers.classPrivateFieldSet(this, _rootGroupTitle, options.rootGroupTitle);
	      babelHelpers.classPrivateFieldSet(this, _onOpenFieldMenu, options.onOpenFieldMenu);
	      babelHelpers.classPrivateFieldSet(this, _onOpenMenu, options.onOpenMenu);
	      babelHelpers.classPrivateFieldSet(this, _showValuesSelector, (_options$showValuesSe = options.showValuesSelector) !== null && _options$showValuesSe !== void 0 ? _options$showValuesSe : true);
	    }
	  }
	  babelHelpers.createClass(ConditionSelector, [{
	    key: "createNode",
	    value: function createNode() {
	      var conditionObjectNode = this.objectNode = main_core.Dom.create("input", {
	        attrs: {
	          type: "hidden",
	          name: babelHelpers.classPrivateFieldGet(this, _fieldPrefix) + "object[]",
	          value: babelHelpers.classPrivateFieldGet(this, _condition$2).object
	        }
	      });
	      var conditionFieldNode = this.fieldNode = main_core.Dom.create("input", {
	        attrs: {
	          type: "hidden",
	          name: babelHelpers.classPrivateFieldGet(this, _fieldPrefix) + "field[]",
	          value: babelHelpers.classPrivateFieldGet(this, _condition$2).field
	        }
	      });
	      var conditionOperatorNode = this.operatorNode = main_core.Dom.create("input", {
	        attrs: {
	          type: "hidden",
	          name: babelHelpers.classPrivateFieldGet(this, _fieldPrefix) + "operator[]",
	          value: babelHelpers.classPrivateFieldGet(this, _condition$2).operator
	        }
	      });
	      var value = main_core.Type.isArrayFilled(babelHelpers.classPrivateFieldGet(this, _condition$2).value) ? babelHelpers.classPrivateFieldGet(this, _condition$2).value[0] : babelHelpers.classPrivateFieldGet(this, _condition$2).value;
	      this.valueNode = _classPrivateMethodGet$3(this, _createValueNode, _createValueNode2).call(this, value);
	      var conditionValueNode = this.valueNode;
	      var conditionValueNode2;
	      if (babelHelpers.classPrivateFieldGet(this, _condition$2).operator === bizproc_condition.Operator.BETWEEN) {
	        var value2 = main_core.Type.isArrayFilled(babelHelpers.classPrivateFieldGet(this, _condition$2).value) && babelHelpers.classPrivateFieldGet(this, _condition$2).value.length > 1 ? babelHelpers.classPrivateFieldGet(this, _condition$2).value[1] : '';
	        babelHelpers.classPrivateFieldSet(this, _valueNode, _classPrivateMethodGet$3(this, _createValueNode, _createValueNode2).call(this, value2));
	        conditionValueNode2 = babelHelpers.classPrivateFieldGet(this, _valueNode);
	      }
	      var conditionJoinerNode = this.joinerNode = main_core.Dom.create("input", {
	        attrs: {
	          type: "hidden",
	          name: babelHelpers.classPrivateFieldGet(this, _fieldPrefix) + "joiner[]",
	          value: babelHelpers.classPrivateFieldGet(this, _joiner)
	        }
	      });
	      var labelNode = this.labelNode = main_core.Dom.create("span", {
	        attrs: {
	          className: "bizproc-automation-popup-settings-link-wrapper"
	        }
	      });
	      this.setLabelText();
	      this.bindLabelNode();
	      var removeButtonNode = main_core.Dom.create("span", {
	        attrs: {
	          className: "bizproc-automation-popup-settings-link-remove"
	        },
	        events: {
	          click: this.removeCondition.bind(this)
	        }
	      });
	      var joinerButtonNode = main_core.Dom.create("span", {
	        attrs: {
	          className: "bizproc-automation-popup-settings-link bizproc-automation-condition-joiner"
	        },
	        text: bizproc_automation.ConditionGroup.JOINER.message(babelHelpers.classPrivateFieldGet(this, _joiner))
	      });
	      main_core.Event.bind(joinerButtonNode, 'click', this.changeJoiner.bind(this, joinerButtonNode));
	      this.node = main_core.Dom.create("span", {
	        attrs: {
	          className: "bizproc-automation-popup-settings-link-wrapper bizproc-automation-condition-wrapper"
	        },
	        children: [conditionObjectNode, conditionFieldNode, conditionOperatorNode, conditionValueNode, conditionValueNode2, conditionJoinerNode, labelNode, removeButtonNode, joinerButtonNode]
	      });
	      return this.node;
	    }
	  }, {
	    key: "init",
	    value: function init(condition) {
	      babelHelpers.classPrivateFieldSet(this, _condition$2, condition);
	      this.setLabelText();
	      this.bindLabelNode();
	    }
	  }, {
	    key: "setLabelText",
	    value: function setLabelText() {
	      if (!this.labelNode || !babelHelpers.classPrivateFieldGet(this, _condition$2)) {
	        return;
	      }
	      main_core.Dom.clean(this.labelNode);
	      if (babelHelpers.classPrivateFieldGet(this, _condition$2).field !== '') {
	        var field = this.getField(babelHelpers.classPrivateFieldGet(this, _condition$2).object, babelHelpers.classPrivateFieldGet(this, _condition$2).field) || '?';
	        var valueLabel = _classPrivateMethodGet$3(this, _getValueLabel, _getValueLabel2).call(this, field);
	        main_core.Dom.append(main_core.Tag.render(_templateObject$1 || (_templateObject$1 = babelHelpers.taggedTemplateLiteral(["<span class=\"bizproc-automation-popup-settings-link\">", "</span>"])), main_core.Text.encode(field.Name)), this.labelNode);
	        main_core.Dom.append(main_core.Tag.render(_templateObject2$1 || (_templateObject2$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<span class=\"bizproc-automation-popup-settings-link\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</span>\n\t\t\t\t"])), main_core.Text.encode(this.getOperatorLabel(babelHelpers.classPrivateFieldGet(this, _condition$2).operator))), this.labelNode);
	        if (valueLabel) {
	          main_core.Dom.append(main_core.Tag.render(_templateObject3$1 || (_templateObject3$1 = babelHelpers.taggedTemplateLiteral(["<span class=\"bizproc-automation-popup-settings-link\">", "</span>"])), main_core.Text.encode(valueLabel)), this.labelNode);
	        }
	      } else {
	        main_core.Dom.append(main_core.Tag.render(_templateObject4$1 || (_templateObject4$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<span class=\"bizproc-automation-popup-settings-link\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</span>\n\t\t\t\t"])), main_core.Text.encode(this.getOperatorLabel(bizproc_condition.Operator.EMPTY))), this.labelNode);
	      }
	    }
	  }, {
	    key: "bindLabelNode",
	    value: function bindLabelNode() {
	      if (this.labelNode) {
	        main_core.Event.bind(this.labelNode, 'click', this.onLabelClick.bind(this));
	      }
	    }
	  }, {
	    key: "onLabelClick",
	    value: function onLabelClick() {
	      this.showPopup();
	    }
	  }, {
	    key: "showPopup",
	    value: function showPopup() {
	      if (this.popup) {
	        this.popup.show();
	        return;
	      }
	      var fields = this.filterFields();
	      var objectSelect = main_core.Dom.create('input', {
	        attrs: {
	          type: 'hidden',
	          className: 'bizproc-automation-popup-settings-dropdown'
	        }
	      });
	      var fieldSelect = main_core.Dom.create('input', {
	        attrs: {
	          type: 'hidden',
	          className: 'bizproc-automation-popup-settings-dropdown'
	        }
	      });
	      var fieldSelectLabel = main_core.Dom.create('div', {
	        attrs: {
	          className: 'bizproc-automation-popup-settings-dropdown',
	          readonly: 'readonly'
	        },
	        children: [fieldSelect]
	      });
	      main_core.Event.bind(fieldSelectLabel, 'click', this.onFieldSelectorClick.bind(this, fieldSelectLabel, fieldSelect, fields, objectSelect));
	      var selectedField = this.getField(babelHelpers.classPrivateFieldGet(this, _condition$2).object, babelHelpers.classPrivateFieldGet(this, _condition$2).field);
	      if (!babelHelpers.classPrivateFieldGet(this, _condition$2).field) {
	        selectedField = fields[0];
	      }
	      babelHelpers.classPrivateFieldSet(this, _selectedField, selectedField);
	      fieldSelect.value = selectedField.Id;
	      objectSelect.value = selectedField.ObjectId;
	      fieldSelectLabel.textContent = selectedField.Name;
	      var valueInput = _classPrivateMethodGet$3(this, _getValueNode, _getValueNode2).call(this, selectedField, babelHelpers.classPrivateFieldGet(this, _condition$2).value, babelHelpers.classPrivateFieldGet(this, _condition$2).operator);
	      var valueWrapper = main_core.Dom.create('div', {
	        attrs: {
	          className: 'bizproc-automation-popup-settings'
	        },
	        children: [valueInput]
	      });
	      var operatorSelect = this.createOperatorNode(selectedField, valueWrapper);
	      var operatorWrapper = main_core.Dom.create('div', {
	        attrs: {
	          className: 'bizproc-automation-popup-settings'
	        },
	        children: [operatorSelect]
	      });
	      if (babelHelpers.classPrivateFieldGet(this, _condition$2).field !== '') {
	        operatorSelect.value = babelHelpers.classPrivateFieldGet(this, _condition$2).operator;
	      }
	      var form = main_core.Dom.create("form", {
	        attrs: {
	          className: "bizproc-automation-popup-select-block"
	        },
	        children: [main_core.Dom.create('div', {
	          attrs: {
	            className: 'bizproc-automation-popup-settings'
	          },
	          children: [fieldSelectLabel]
	        }), operatorWrapper, valueWrapper]
	      });
	      main_core.Event.bind(fieldSelect, 'change', this.onFieldChange.bind(this, fieldSelect, operatorWrapper, valueWrapper, objectSelect));
	      var self = this;
	      this.popup = new BX.PopupWindow('bizproc-automation-popup-set', this.labelNode, {
	        className: 'bizproc-automation-popup-set',
	        autoHide: false,
	        closeByEsc: true,
	        closeIcon: false,
	        titleBar: false,
	        angle: true,
	        offsetLeft: 45,
	        overlay: {
	          backgroundColor: 'transparent'
	        },
	        content: form,
	        buttons: [new BX.PopupWindowButton({
	          text: main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_CHOOSE'),
	          className: "webform-button webform-button-create",
	          events: {
	            click: function click() {
	              babelHelpers.classPrivateFieldGet(self, _condition$2).setObject(objectSelect.value);
	              babelHelpers.classPrivateFieldGet(self, _condition$2).setField(fieldSelect.value);
	              babelHelpers.classPrivateFieldGet(self, _condition$2).setOperator(operatorWrapper.firstChild.value);
	              var valueInputs = valueWrapper.querySelectorAll('[name^="' + babelHelpers.classPrivateFieldGet(self, _fieldPrefix) + 'value"]');
	              if (valueInputs.length > 0) {
	                var value = valueInputs[0].value;
	                if (babelHelpers.classPrivateFieldGet(self, _condition$2).operator === bizproc_condition.Operator.BETWEEN && valueInputs.length > 1) {
	                  value = [valueInputs[0].value, valueInputs[1].value];
	                }
	                babelHelpers.classPrivateFieldGet(self, _condition$2).setValue(value);
	              } else {
	                babelHelpers.classPrivateFieldGet(self, _condition$2).setValue('');
	              }
	              self.setLabelText();
	              var field = self.getField(babelHelpers.classPrivateFieldGet(self, _condition$2).object, babelHelpers.classPrivateFieldGet(self, _condition$2).field);
	              if (field && field.Type === 'UF:address') {
	                var input = valueWrapper.querySelector('[name="' + babelHelpers.classPrivateFieldGet(self, _fieldPrefix) + 'value"]');
	                babelHelpers.classPrivateFieldGet(self, _condition$2).setValue(input ? input.value : '');
	              }
	              self.updateValueNode();
	              this.popupWindow.close();
	            }
	          }
	        }), new BX.PopupWindowButtonLink({
	          text: main_core.Loc.getMessage('JS_CORE_WINDOW_CANCEL'),
	          className: "popup-window-button-link-cancel",
	          events: {
	            click: function click() {
	              this.popupWindow.close();
	            }
	          }
	        })],
	        events: {
	          onPopupClose: function onPopupClose() {
	            this.destroy();
	            if (self.fieldDialog) {
	              self.fieldDialog.destroy();
	              delete self.fieldDialog;
	            }
	            delete self.popup;
	          }
	        }
	      });
	      this.popup.show();
	    }
	  }, {
	    key: "onFieldSelectorClick",
	    value: function onFieldSelectorClick(fieldSelectLabel, fieldSelect, fields, objectSelect, event) {
	      if (!this.fieldDialog) {
	        var globalContext = bizproc_automation.getGlobalContext();
	        var _fields2 = main_core.Runtime.clone(main_core.Type.isArrayFilled(babelHelpers.classPrivateFieldGet(this, _fields$1)) ? babelHelpers.classPrivateFieldGet(this, _fields$1) : globalContext.document.getFields());
	        this.fieldDialog = new bizproc_automation.InlineSelectorCondition({
	          context: new bizproc_automation.SelectorContext({
	            fields: _fields2,
	            rootGroupTitle: globalContext.document.title
	          }),
	          condition: babelHelpers.classPrivateFieldGet(this, _condition$2)
	        });
	        if (main_core.Type.isFunction(babelHelpers.classPrivateFieldGet(this, _onOpenFieldMenu))) {
	          this.fieldDialog.subscribe('onOpenMenu', babelHelpers.classPrivateFieldGet(this, _onOpenFieldMenu));
	        }
	        this.fieldDialog.subscribe('change', function (event) {
	          var property = event.getData().field;
	          fieldSelectLabel.textContent = property.Name;
	          fieldSelect.value = property.Id;
	          objectSelect.value = property.ObjectId;
	          BX.fireEvent(fieldSelect, 'change');
	        });
	        this.fieldDialog.renderTo(fieldSelectLabel);
	      }
	      this.fieldDialog.openMenu(event);
	    }
	  }, {
	    key: "updateValueNode",
	    value: function updateValueNode() {
	      if (babelHelpers.classPrivateFieldGet(this, _condition$2)) {
	        if (this.objectNode) {
	          this.objectNode.value = babelHelpers.classPrivateFieldGet(this, _condition$2).object;
	        }
	        if (this.fieldNode) {
	          this.fieldNode.value = babelHelpers.classPrivateFieldGet(this, _condition$2).field;
	        }
	        if (this.operatorNode) {
	          this.operatorNode.value = babelHelpers.classPrivateFieldGet(this, _condition$2).operator;
	        }
	        if (this.valueNode) {
	          this.valueNode.value = main_core.Type.isArrayFilled(babelHelpers.classPrivateFieldGet(this, _condition$2).value) ? babelHelpers.classPrivateFieldGet(this, _condition$2).value[0] : babelHelpers.classPrivateFieldGet(this, _condition$2).value;
	        }
	        if (babelHelpers.classPrivateFieldGet(this, _condition$2).operator === bizproc_condition.Operator.BETWEEN) {
	          var value2 = babelHelpers.classPrivateFieldGet(this, _condition$2).value[1] || '';
	          if (babelHelpers.classPrivateFieldGet(this, _valueNode)) {
	            babelHelpers.classPrivateFieldGet(this, _valueNode).value = value2;
	          } else {
	            babelHelpers.classPrivateFieldSet(this, _valueNode, _classPrivateMethodGet$3(this, _createValueNode, _createValueNode2).call(this, value2));
	            main_core.Dom.append(babelHelpers.classPrivateFieldGet(this, _valueNode), this.node);
	          }
	        } else if (main_core.Type.isDomNode(babelHelpers.classPrivateFieldGet(this, _valueNode))) {
	          main_core.Dom.remove(babelHelpers.classPrivateFieldGet(this, _valueNode));
	          babelHelpers.classPrivateFieldSet(this, _valueNode, null);
	        }
	      }
	    }
	  }, {
	    key: "onFieldChange",
	    value: function onFieldChange(selectNode, conditionWrapper, valueWrapper, objectSelect) {
	      var _babelHelpers$classPr;
	      var field = this.getField(objectSelect.value, selectNode.value);
	      var operatorNode = this.createOperatorNode(field, valueWrapper);

	      //clean value if field types are different
	      if (field.Type !== ((_babelHelpers$classPr = babelHelpers.classPrivateFieldGet(this, _selectedField)) === null || _babelHelpers$classPr === void 0 ? void 0 : _babelHelpers$classPr.Type)) {
	        main_core.Dom.clean(valueWrapper);
	      }
	      babelHelpers.classPrivateFieldSet(this, _selectedField, field);

	      //keep selected operator if possible
	      if (this.getOperators(field['Type'], field['Multiple'])[conditionWrapper.firstChild.value]) {
	        operatorNode.value = conditionWrapper.firstChild.value;
	      }
	      conditionWrapper.replaceChild(operatorNode, conditionWrapper.firstChild);
	      this.onOperatorChange(operatorNode, field, valueWrapper);
	    }
	  }, {
	    key: "onOperatorChange",
	    value: function onOperatorChange(selectNode, field, valueWrapper) {
	      var valueInput = valueWrapper.querySelector('[name^="' + babelHelpers.classPrivateFieldGet(this, _fieldPrefix) + 'value"]');
	      main_core.Dom.clean(valueWrapper);
	      main_core.Dom.append(_classPrivateMethodGet$3(this, _getValueNode, _getValueNode2).call(this, field, (valueInput === null || valueInput === void 0 ? void 0 : valueInput.value) || babelHelpers.classPrivateFieldGet(this, _condition$2).value, selectNode.value), valueWrapper);
	    }
	  }, {
	    key: "getField",
	    // TODO - fix this method
	    value: function getField(object, id) {
	      var field;
	      var robot = bizproc_automation.Designer.getInstance().robot;
	      var component = bizproc_automation.Designer.getInstance().component;
	      var tpl = robot ? robot.getTemplate() : null;
	      switch (object) {
	        case 'Document':
	          for (var i = 0; i < babelHelpers.classPrivateFieldGet(this, _fields$1).length; ++i) {
	            if (id === babelHelpers.classPrivateFieldGet(this, _fields$1)[i].Id) {
	              field = babelHelpers.classPrivateFieldGet(this, _fields$1)[i];
	            }
	          }
	          break;
	        case 'Template':
	          if (tpl && component && component.triggerManager) {
	            field = component.triggerManager.getReturnProperty(tpl.getStatusId(), id);
	          }
	          break;
	        case 'Constant':
	          if (tpl) {
	            field = tpl.getConstant(id);
	          }
	          break;
	        case 'GlobalConst':
	          if (component) {
	            field = component.getConstant(id);
	          }
	          break;
	        case 'GlobalVar':
	          if (component) {
	            field = component.getGVariable(id);
	          }
	          break;
	        default:
	          var foundRobot = tpl ? tpl.getRobotById(object) : null;
	          if (foundRobot) {
	            field = foundRobot.getReturnProperty(id);
	          }
	          break;
	      }
	      return field || {
	        Id: id,
	        ObjectId: object,
	        Name: id,
	        Type: 'string',
	        Expression: id,
	        SystemExpression: '{=' + object + ':' + id + '}'
	      };
	    }
	  }, {
	    key: "getOperators",
	    value: function getOperators(fieldType, multiple) {
	      var allLabels = bizproc_condition.Operator.getAllLabels();
	      var list = {
	        '!empty': allLabels[bizproc_condition.Operator.NOT_EMPTY],
	        'empty': allLabels[bizproc_condition.Operator.EMPTY],
	        '=': allLabels[bizproc_condition.Operator.EQUAL],
	        '!=': allLabels[bizproc_condition.Operator.NOT_EQUAL]
	      };
	      switch (fieldType) {
	        case 'file':
	        case 'UF:crm':
	        case 'UF:resourcebooking':
	          list = {
	            '!empty': allLabels[bizproc_condition.Operator.NOT_EMPTY],
	            'empty': allLabels[bizproc_condition.Operator.EMPTY]
	          };
	          break;
	        case 'bool':
	        case 'select':
	          if (multiple) {
	            list[bizproc_condition.Operator.CONTAIN] = allLabels[bizproc_condition.Operator.CONTAIN];
	            list[bizproc_condition.Operator.NOT_CONTAIN] = allLabels[bizproc_condition.Operator.NOT_CONTAIN];
	          }
	          break;
	        case 'user':
	          list[bizproc_condition.Operator.IN] = allLabels[bizproc_condition.Operator.IN];
	          list[bizproc_condition.Operator.NOT_IN] = allLabels[bizproc_condition.Operator.NOT_IN];
	          list[bizproc_condition.Operator.CONTAIN] = allLabels[bizproc_condition.Operator.CONTAIN];
	          list[bizproc_condition.Operator.NOT_CONTAIN] = allLabels[bizproc_condition.Operator.NOT_CONTAIN];
	          break;
	        default:
	          list[bizproc_condition.Operator.IN] = allLabels[bizproc_condition.Operator.IN];
	          list[bizproc_condition.Operator.NOT_IN] = allLabels[bizproc_condition.Operator.NOT_IN];
	          list[bizproc_condition.Operator.CONTAIN] = allLabels[bizproc_condition.Operator.CONTAIN];
	          list[bizproc_condition.Operator.NOT_CONTAIN] = allLabels[bizproc_condition.Operator.NOT_CONTAIN];
	          list[bizproc_condition.Operator.GREATER_THEN] = allLabels[bizproc_condition.Operator.GREATER_THEN];
	          list[bizproc_condition.Operator.GREATER_THEN_OR_EQUAL] = allLabels[bizproc_condition.Operator.GREATER_THEN_OR_EQUAL];
	          list[bizproc_condition.Operator.LESS_THEN] = allLabels[bizproc_condition.Operator.LESS_THEN];
	          list[bizproc_condition.Operator.LESS_THEN_OR_EQUAL] = allLabels[bizproc_condition.Operator.LESS_THEN_OR_EQUAL];
	      }

	      // todo: interface
	      // if (['time', 'date', 'datetime', 'int', 'double'].includes(fieldType) || Type.isUndefined(fieldType))
	      // {
	      // 	list[Operator.BETWEEN] = allLabels[Operator.BETWEEN];
	      // }

	      return list;
	    }
	  }, {
	    key: "getOperatorLabel",
	    value: function getOperatorLabel(id) {
	      return bizproc_condition.Operator.getOperatorLabel(id);
	    }
	  }, {
	    key: "filterFields",
	    value: function filterFields() {
	      var filtered = [];
	      for (var i = 0; i < babelHelpers.classPrivateFieldGet(this, _fields$1).length; ++i) {
	        var type = babelHelpers.classPrivateFieldGet(this, _fields$1)[i]['Type'];
	        if (type === 'bool' || type === 'date' || type === 'datetime' || type === 'double' || type === 'file' || type === 'int' || type === 'select' || type === 'string' || type === 'text' || type === 'user' || type === 'UF:money' || type === 'UF:crm' || type === 'UF:resourcebooking' || type === 'UF:url') {
	          filtered.push(babelHelpers.classPrivateFieldGet(this, _fields$1)[i]);
	        }
	      }
	      return filtered;
	    }
	  }, {
	    key: "createValueNode",
	    value: function createValueNode(docField, value) {
	      var _this = this;
	      var docType = bizproc_automation.Designer.getInstance().component ? bizproc_automation.Designer.getInstance().component.document.getRawType() : bizproc_automation.getGlobalContext().document.getRawType();
	      var field = BX.clone(docField);
	      field.Multiple = false;
	      var valueNodes = BX.Bizproc.FieldType.renderControlPublic(docType, field, babelHelpers.classPrivateFieldGet(this, _fieldPrefix) + 'value', value, false);
	      valueNodes.querySelectorAll('[data-role]').forEach(function (node) {
	        var _babelHelpers$classPr2;
	        var selector = bizproc_automation.SelectorManager.createSelectorByRole(node.dataset.role, {
	          context: new bizproc_automation.SelectorContext({
	            fields: bizproc_automation.getGlobalContext().document.getFields(),
	            useSwitcherMenu: false,
	            rootGroupTitle: (_babelHelpers$classPr2 = babelHelpers.classPrivateFieldGet(_this, _rootGroupTitle)) !== null && _babelHelpers$classPr2 !== void 0 ? _babelHelpers$classPr2 : bizproc_automation.getGlobalContext().document.title
	          })
	        });
	        if (selector) {
	          if (babelHelpers.classPrivateFieldGet(_this, _showValuesSelector) === true) {
	            if (main_core.Type.isFunction(babelHelpers.classPrivateFieldGet(_this, _onOpenMenu))) {
	              selector.subscribe('onOpenMenu', babelHelpers.classPrivateFieldGet(_this, _onOpenMenu));
	            }
	            selector.renderTo(node);
	          } else {
	            selector.targetInput = node;
	            selector.parseTargetProperties();
	          }
	        }
	      });
	      return valueNodes;
	    }
	  }, {
	    key: "createOperatorNode",
	    value: function createOperatorNode(field, valueWrapper) {
	      var select = main_core.Dom.create('select', {
	        attrs: {
	          className: 'bizproc-automation-popup-settings-dropdown'
	        }
	      });
	      var operatorList = this.getOperators(field['Type'], field['Multiple']);
	      for (var operatorId in operatorList) {
	        if (!operatorList.hasOwnProperty(operatorId)) {
	          continue;
	        }
	        select.appendChild(main_core.Dom.create('option', {
	          props: {
	            value: operatorId
	          },
	          text: operatorList[operatorId]
	        }));
	      }
	      main_core.Event.bind(select, 'change', this.onOperatorChange.bind(this, select, field, valueWrapper));
	      return select;
	    }
	  }, {
	    key: "removeCondition",
	    value: function removeCondition(event) {
	      babelHelpers.classPrivateFieldSet(this, _condition$2, null);
	      main_core.Dom.remove(this.node);
	      this.labelNode = null;
	      this.fieldNode = null;
	      this.operatorNode = null;
	      this.valueNode = null;
	      babelHelpers.classPrivateFieldSet(this, _valueNode, null);
	      this.node = null;
	      event.stopPropagation();
	    }
	  }, {
	    key: "changeJoiner",
	    value: function changeJoiner(btn, event) {
	      babelHelpers.classPrivateFieldSet(this, _joiner, babelHelpers.classPrivateFieldGet(this, _joiner) === bizproc_automation.ConditionGroup.JOINER.Or ? bizproc_automation.ConditionGroup.JOINER.And : bizproc_automation.ConditionGroup.JOINER.Or);
	      btn.textContent = bizproc_automation.ConditionGroup.JOINER.message(babelHelpers.classPrivateFieldGet(this, _joiner));
	      if (this.joinerNode) {
	        this.joinerNode.value = babelHelpers.classPrivateFieldGet(this, _joiner);
	      }
	      event.preventDefault();
	    }
	  }, {
	    key: "destroy",
	    value: function destroy() {
	      if (this.popup) {
	        this.popup.close();
	      }
	    }
	  }]);
	  return ConditionSelector;
	}();
	function _createValueNode2(value) {
	  return main_core.Tag.render(_templateObject5$1 || (_templateObject5$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<input\n\t\t\t\ttype=\"hidden\"\n\t\t\t\tname=\"", "\"\n\t\t\t\tvalue=\"", "\"\n\t\t\t>\n\t\t"])), main_core.Text.encode(babelHelpers.classPrivateFieldGet(this, _fieldPrefix) + 'value[]'), main_core.Text.encode(value));
	}
	function _getValueLabel2(field) {
	  var operator = babelHelpers.classPrivateFieldGet(this, _condition$2).operator;
	  var value = babelHelpers.classPrivateFieldGet(this, _condition$2).value;
	  if (operator === 'between') {
	    var _Loc$getMessage;
	    return (_Loc$getMessage = main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_CONDITION_BETWEEN_VALUE', {
	      '#VALUE_1#': BX.Bizproc.FieldType.formatValuePrintable(field, main_core.Type.isArrayFilled(value) ? value[0] : value),
	      '#VALUE_2#': BX.Bizproc.FieldType.formatValuePrintable(field, main_core.Type.isArrayFilled(value) ? value[1] : '')
	    })) !== null && _Loc$getMessage !== void 0 ? _Loc$getMessage : '';
	  } else if (operator.indexOf('empty') < 0) {
	    return BX.Bizproc.FieldType.formatValuePrintable(field, value);
	  }
	  return null;
	}
	function _getValueNode2(field, value, operator) {
	  if (operator === bizproc_condition.Operator.BETWEEN) {
	    var valueNode1 = this.createValueNode(field, main_core.Type.isArrayFilled(value) ? value[0] : value);
	    var valueNode2 = this.createValueNode(field, main_core.Type.isArrayFilled(value) ? value[1] : '');
	    return main_core.Tag.render(_templateObject6 || (_templateObject6 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div>\n\t\t\t\t\t", "\n\t\t\t\t\t<div>", "</div>\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"])), valueNode1, bizproc_automation.ConditionGroup.JOINER.message('AND'), valueNode2);
	  } else if (operator.indexOf('empty') < 0) {
	    return this.createValueNode(field, value);
	  }
	  return '';
	}

	function _classPrivateFieldInitSpec$i(obj, privateMap, value) { _checkPrivateRedeclaration$i(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$i(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	var _conditionGroup = /*#__PURE__*/new WeakMap();
	var _fields$2 = /*#__PURE__*/new WeakMap();
	var _fieldPrefix$1 = /*#__PURE__*/new WeakMap();
	var _itemSelectors = /*#__PURE__*/new WeakMap();
	var _onOpenFieldMenu$1 = /*#__PURE__*/new WeakMap();
	var _onOpenMenu$1 = /*#__PURE__*/new WeakMap();
	var _showValuesSelector$1 = /*#__PURE__*/new WeakMap();
	var _rootGroupTitle$1 = /*#__PURE__*/new WeakMap();
	var ConditionGroupSelector = /*#__PURE__*/function () {
	  function ConditionGroupSelector(conditionGroup, options) {
	    babelHelpers.classCallCheck(this, ConditionGroupSelector);
	    _classPrivateFieldInitSpec$i(this, _conditionGroup, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$i(this, _fields$2, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$i(this, _fieldPrefix$1, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$i(this, _itemSelectors, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$i(this, _onOpenFieldMenu$1, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$i(this, _onOpenMenu$1, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$i(this, _showValuesSelector$1, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$i(this, _rootGroupTitle$1, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldSet(this, _conditionGroup, conditionGroup);
	    babelHelpers.classPrivateFieldSet(this, _fields$2, []);
	    babelHelpers.classPrivateFieldSet(this, _fieldPrefix$1, 'condition_');
	    babelHelpers.classPrivateFieldSet(this, _itemSelectors, []);
	    if (main_core.Type.isPlainObject(options)) {
	      var _options$showValuesSe;
	      if (main_core.Type.isArray(options.fields)) {
	        babelHelpers.classPrivateFieldSet(this, _fields$2, options.fields);
	      }
	      if (options.fieldPrefix) {
	        babelHelpers.classPrivateFieldSet(this, _fieldPrefix$1, options.fieldPrefix);
	      }
	      babelHelpers.classPrivateFieldSet(this, _rootGroupTitle$1, options.rootGroupTitle);
	      babelHelpers.classPrivateFieldSet(this, _onOpenFieldMenu$1, options.onOpenFieldMenu);
	      babelHelpers.classPrivateFieldSet(this, _onOpenMenu$1, options.onOpenMenu);
	      babelHelpers.classPrivateFieldSet(this, _showValuesSelector$1, (_options$showValuesSe = options.showValuesSelector) !== null && _options$showValuesSe !== void 0 ? _options$showValuesSe : true);
	    }
	  }
	  babelHelpers.createClass(ConditionGroupSelector, [{
	    key: "createNode",
	    value: function createNode() {
	      var _this = this;
	      var conditionNodes = [];
	      babelHelpers.classPrivateFieldGet(this, _conditionGroup).getItems().forEach(function (item) {
	        var conditionSelector = new ConditionSelector(item[0], {
	          fields: babelHelpers.classPrivateFieldGet(_this, _fields$2),
	          joiner: item[1],
	          fieldPrefix: babelHelpers.classPrivateFieldGet(_this, _fieldPrefix$1),
	          rootGroupTitle: babelHelpers.classPrivateFieldGet(_this, _rootGroupTitle$1),
	          onOpenFieldMenu: babelHelpers.classPrivateFieldGet(_this, _onOpenFieldMenu$1),
	          onOpenMenu: babelHelpers.classPrivateFieldGet(_this, _onOpenMenu$1),
	          showValuesSelector: babelHelpers.classPrivateFieldGet(_this, _showValuesSelector$1)
	        });
	        babelHelpers.classPrivateFieldGet(_this, _itemSelectors).push(conditionSelector);
	        conditionNodes.push(conditionSelector.createNode());
	      });
	      var self = this;
	      conditionNodes.push(main_core.Dom.create("a", {
	        attrs: {
	          className: "bizproc-automation-popup-settings-link"
	        },
	        text: '[+]',
	        events: {
	          click: function click() {
	            self.addItem(this);
	          }
	        }
	      }));
	      return main_core.Dom.create("span", {
	        attrs: {
	          className: "bizproc-automation-popup-settings-link-wrapper"
	        },
	        children: conditionNodes
	      });
	    }
	  }, {
	    key: "addItem",
	    value: function addItem(buttonNode) {
	      var conditionSelector = new ConditionSelector(new bizproc_automation.Condition({}, babelHelpers.classPrivateFieldGet(this, _conditionGroup)), {
	        fields: babelHelpers.classPrivateFieldGet(this, _fields$2),
	        fieldPrefix: babelHelpers.classPrivateFieldGet(this, _fieldPrefix$1),
	        rootGroupTitle: babelHelpers.classPrivateFieldGet(this, _rootGroupTitle$1),
	        onOpenFieldMenu: babelHelpers.classPrivateFieldGet(this, _onOpenFieldMenu$1),
	        onOpenMenu: babelHelpers.classPrivateFieldGet(this, _onOpenMenu$1),
	        showValuesSelector: babelHelpers.classPrivateFieldGet(this, _showValuesSelector$1)
	      });
	      babelHelpers.classPrivateFieldGet(this, _itemSelectors).push(conditionSelector);
	      buttonNode.parentNode.insertBefore(conditionSelector.createNode(), buttonNode);
	    }
	  }, {
	    key: "destroy",
	    value: function destroy() {
	      babelHelpers.classPrivateFieldGet(this, _itemSelectors).forEach(function (selector) {
	        return selector.destroy();
	      });
	      babelHelpers.classPrivateFieldSet(this, _itemSelectors, []);
	    }
	  }]);
	  return ConditionGroupSelector;
	}();

	var Manager = /*#__PURE__*/function () {
	  function Manager() {
	    babelHelpers.classCallCheck(this, Manager);
	  }
	  babelHelpers.createClass(Manager, null, [{
	    key: "getSelectorByTarget",
	    value: function getSelectorByTarget(targetInput) {
	      var _Designer$getInstance;
	      // TODO - save created selectors with Manager
	      var template = (_Designer$getInstance = bizproc_automation.Designer.getInstance().getRobotSettingsDialog()) === null || _Designer$getInstance === void 0 ? void 0 : _Designer$getInstance.template;
	      if (template && main_core.Type.isArray(template.robotSettingsControls)) {
	        return template.robotSettingsControls.find(function (selector) {
	          return selector.targetInput === targetInput;
	        });
	      }
	      return undefined;
	    }
	  }, {
	    key: "createSelectorByRole",
	    value: function createSelectorByRole(role, selectorProps) {
	      if (role === this.SELECTOR_ROLE_USER) {
	        return new bizproc_automation.UserSelector(selectorProps);
	      } else if (role === this.SELECTOR_ROLE_FILE) {
	        return new bizproc_automation.FileSelector(selectorProps);
	      } else if (role === this.SELECTOR_ROLE_INLINE) {
	        return new bizproc_automation.InlineSelector(selectorProps);
	      } else if (role === this.SELECTOR_ROLE_INLINE_HTML) {
	        return new bizproc_automation.InlineSelectorHtml(selectorProps);
	      } else if (role === this.SELECTOR_ROLE_TIME) {
	        return new bizproc_automation.TimeSelector(selectorProps);
	      } else if (role === this.SELECTOR_ROLE_SAVE_STATE) {
	        return new bizproc_automation.SaveStateCheckbox(selectorProps);
	      } else {
	        return undefined;
	      }
	    }
	  }]);
	  return Manager;
	}();
	babelHelpers.defineProperty(Manager, "SELECTOR_ROLE_USER", 'user-selector');
	babelHelpers.defineProperty(Manager, "SELECTOR_ROLE_FILE", 'file-selector');
	babelHelpers.defineProperty(Manager, "SELECTOR_ROLE_INLINE", 'inline-selector-target');
	babelHelpers.defineProperty(Manager, "SELECTOR_ROLE_INLINE_HTML", 'inline-selector-html');
	babelHelpers.defineProperty(Manager, "SELECTOR_ROLE_TIME", 'time-selector');
	babelHelpers.defineProperty(Manager, "SELECTOR_ROLE_SAVE_STATE", 'save-state-checkbox');

	function _classPrivateMethodInitSpec$4(obj, privateSet) { _checkPrivateRedeclaration$j(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec$j(obj, privateMap, value) { _checkPrivateRedeclaration$j(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$j(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet$4(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _labelNode = /*#__PURE__*/new WeakMap();
	var _time = /*#__PURE__*/new WeakMap();
	var _timeValues = /*#__PURE__*/new WeakMap();
	var _timeFormat = /*#__PURE__*/new WeakMap();
	var _selector = /*#__PURE__*/new WeakMap();
	var _fillTimeFormat = /*#__PURE__*/new WeakSet();
	var _fillTimeValues = /*#__PURE__*/new WeakSet();
	var _formatTime = /*#__PURE__*/new WeakSet();
	var _setLabelText = /*#__PURE__*/new WeakSet();
	var _bindLabelNode = /*#__PURE__*/new WeakSet();
	var _onLabelClick = /*#__PURE__*/new WeakSet();
	var _showTimeSelector = /*#__PURE__*/new WeakSet();
	var InlineTimeSelector = /*#__PURE__*/function () {
	  function InlineTimeSelector(options) {
	    babelHelpers.classCallCheck(this, InlineTimeSelector);
	    _classPrivateMethodInitSpec$4(this, _showTimeSelector);
	    _classPrivateMethodInitSpec$4(this, _onLabelClick);
	    _classPrivateMethodInitSpec$4(this, _bindLabelNode);
	    _classPrivateMethodInitSpec$4(this, _setLabelText);
	    _classPrivateMethodInitSpec$4(this, _formatTime);
	    _classPrivateMethodInitSpec$4(this, _fillTimeValues);
	    _classPrivateMethodInitSpec$4(this, _fillTimeFormat);
	    _classPrivateFieldInitSpec$j(this, _labelNode, {
	      writable: true,
	      value: null
	    });
	    _classPrivateFieldInitSpec$j(this, _time, {
	      writable: true,
	      value: ''
	    });
	    _classPrivateFieldInitSpec$j(this, _timeValues, {
	      writable: true,
	      value: []
	    });
	    _classPrivateFieldInitSpec$j(this, _timeFormat, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$j(this, _selector, {
	      writable: true,
	      value: void 0
	    });
	    if (main_core.Type.isPlainObject(options)) {
	      if (main_core.Type.isElementNode(options.labelNode)) {
	        babelHelpers.classPrivateFieldSet(this, _labelNode, options.labelNode);
	      }
	    }
	    _classPrivateMethodGet$4(this, _fillTimeFormat, _fillTimeFormat2).call(this);
	    _classPrivateMethodGet$4(this, _fillTimeValues, _fillTimeValues2).call(this);
	  }
	  babelHelpers.createClass(InlineTimeSelector, [{
	    key: "init",
	    value: function init(time) {
	      if (main_core.Type.isStringFilled(time)) {
	        babelHelpers.classPrivateFieldSet(this, _time, time);
	      }
	      _classPrivateMethodGet$4(this, _setLabelText, _setLabelText2).call(this);
	      _classPrivateMethodGet$4(this, _bindLabelNode, _bindLabelNode2).call(this);
	    }
	  }]);
	  return InlineTimeSelector;
	}();
	function _fillTimeFormat2() {
	  var getFormat = function getFormat(formatId) {
	    return BX.Main.Date.convertBitrixFormat(main_core.Loc.getMessage(formatId)).replace(/:?\s*s/, '');
	  };
	  var dateFormat = getFormat('FORMAT_DATE');
	  var dateTimeFormat = getFormat('FORMAT_DATETIME');
	  babelHelpers.classPrivateFieldSet(this, _timeFormat, dateTimeFormat.replace(dateFormat, '').trim());
	}
	function _fillTimeValues2() {
	  var self = this;
	  var onclick = function onclick(event, item) {
	    event.preventDefault();
	    babelHelpers.classPrivateFieldGet(self, _labelNode).value = main_core.Text.encode(item.text);
	    this.close();
	  };
	  for (var hour = 0; hour < 24; hour++) {
	    babelHelpers.classPrivateFieldGet(this, _timeValues).push({
	      id: hour * 60,
	      text: _classPrivateMethodGet$4(this, _formatTime, _formatTime2).call(this, hour, 0),
	      onclick: onclick
	    });
	    babelHelpers.classPrivateFieldGet(this, _timeValues).push({
	      id: hour * 60 + 30,
	      text: _classPrivateMethodGet$4(this, _formatTime, _formatTime2).call(this, hour, 30),
	      onclick: onclick
	    });
	  }
	}
	function _formatTime2(hour, minute) {
	  var date = new Date();
	  date.setHours(hour, minute);
	  return main_date.DateTimeFormat.format(babelHelpers.classPrivateFieldGet(this, _timeFormat), date.getTime() / 1000);
	}
	function _setLabelText2() {
	  if (main_core.Type.isElementNode(babelHelpers.classPrivateFieldGet(this, _labelNode))) {
	    babelHelpers.classPrivateFieldGet(this, _labelNode).textContent = babelHelpers.classPrivateFieldGet(this, _time);
	  }
	}
	function _bindLabelNode2() {
	  if (main_core.Type.isElementNode(babelHelpers.classPrivateFieldGet(this, _labelNode))) {
	    main_core.Event.bind(babelHelpers.classPrivateFieldGet(this, _labelNode), 'click', _classPrivateMethodGet$4(this, _onLabelClick, _onLabelClick2).bind(this));
	  }
	}
	function _onLabelClick2(event) {
	  _classPrivateMethodGet$4(this, _showTimeSelector, _showTimeSelector2).call(this);
	  event.preventDefault();
	}
	function _showTimeSelector2() {
	  if (main_core.Type.isNil(babelHelpers.classPrivateFieldGet(this, _selector))) {
	    babelHelpers.classPrivateFieldSet(this, _selector, new main_popup.Menu({
	      autoHide: true,
	      bindElement: babelHelpers.classPrivateFieldGet(this, _labelNode),
	      items: babelHelpers.classPrivateFieldGet(this, _timeValues),
	      maxHeight: 230
	    }));
	  }
	  babelHelpers.classPrivateFieldGet(this, _selector).show();
	}

	var _templateObject$2, _templateObject2$2;
	function ownKeys$3(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread$3(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys$3(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys$3(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	function _classPrivateMethodInitSpec$5(obj, privateSet) { _checkPrivateRedeclaration$k(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec$k(obj, privateMap, value) { _checkPrivateRedeclaration$k(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$k(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet$5(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _menuGroups = /*#__PURE__*/new WeakMap();
	var _dialog = /*#__PURE__*/new WeakMap();
	var _switcherDialog = /*#__PURE__*/new WeakMap();
	var _normalizeGroup = /*#__PURE__*/new WeakSet();
	var _prepareSelectorUsingFieldType = /*#__PURE__*/new WeakSet();
	var _shouldShowField = /*#__PURE__*/new WeakSet();
	var _onKeyDown = /*#__PURE__*/new WeakSet();
	var _prepareFilesMenu = /*#__PURE__*/new WeakSet();
	var InlineSelector = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(InlineSelector, _EventEmitter);
	  function InlineSelector(props) {
	    var _this;
	    babelHelpers.classCallCheck(this, InlineSelector);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(InlineSelector).call(this));
	    _classPrivateMethodInitSpec$5(babelHelpers.assertThisInitialized(_this), _prepareFilesMenu);
	    _classPrivateMethodInitSpec$5(babelHelpers.assertThisInitialized(_this), _onKeyDown);
	    _classPrivateMethodInitSpec$5(babelHelpers.assertThisInitialized(_this), _shouldShowField);
	    _classPrivateMethodInitSpec$5(babelHelpers.assertThisInitialized(_this), _prepareSelectorUsingFieldType);
	    _classPrivateMethodInitSpec$5(babelHelpers.assertThisInitialized(_this), _normalizeGroup);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "fieldProperty", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "replaceOnWrite", false);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "menuButton", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "targetInput", null);
	    _classPrivateFieldInitSpec$k(babelHelpers.assertThisInitialized(_this), _menuGroups, {
	      writable: true,
	      value: {}
	    });
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "basisFields", []);
	    _classPrivateFieldInitSpec$k(babelHelpers.assertThisInitialized(_this), _dialog, {
	      writable: true,
	      value: null
	    });
	    _classPrivateFieldInitSpec$k(babelHelpers.assertThisInitialized(_this), _switcherDialog, {
	      writable: true,
	      value: null
	    });
	    _this.setEventNamespace('BX.Bizproc.Automation.Selector');
	    _this.context = props.context;
	    _this.basisFields = _this.context.fields;
	    return _this;
	  }
	  babelHelpers.createClass(InlineSelector, [{
	    key: "hasGroup",
	    value: function hasGroup(groupId) {
	      return babelHelpers.classPrivateFieldGet(this, _menuGroups).hasOwnProperty(groupId);
	    }
	  }, {
	    key: "addGroup",
	    value: function addGroup(groupId, group) {
	      babelHelpers.classPrivateFieldGet(this, _menuGroups)[groupId] = _classPrivateMethodGet$5(this, _normalizeGroup, _normalizeGroup2).call(this, group);
	    }
	  }, {
	    key: "addGroupItem",
	    value: function addGroupItem(groupId, item) {
	      if (this.hasGroup(groupId)) {
	        babelHelpers.classPrivateFieldGet(this, _menuGroups)[groupId].children.push(_classPrivateMethodGet$5(this, _normalizeGroup, _normalizeGroup2).call(this, item));
	      }
	    }
	  }, {
	    key: "renderWith",
	    value: function renderWith(targetInput) {
	      this.targetInput = main_core.Runtime.clone(targetInput);
	      this.targetInput.setAttribute('autocomplete', 'off');
	      this.menuButton = main_core.Tag.render(_templateObject$2 || (_templateObject$2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<span \n\t\t\t\tonclick=\"", "\"\n\t\t\t\tclass=\"bizproc-automation-popup-select-dotted\"\n\t\t\t></span>\n\t\t"])), this.openMenu.bind(this));
	      this.parseTargetProperties();
	      this.replaceOnWrite |= this.targetInput.getAttribute('data-select-mode') === 'replace';
	      return main_core.Tag.render(_templateObject2$2 || (_templateObject2$2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"bizproc-automation-popup-select\">\n\t\t\t\t", "\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"])), this.targetInput, this.menuButton);
	    }
	  }, {
	    key: "renderTo",
	    value: function renderTo(targetInput) {
	      targetInput.parentNode.replaceChild(this.renderWith(targetInput), targetInput);
	    }
	  }, {
	    key: "bindTargetEvents",
	    value: function bindTargetEvents() {
	      main_core.Event.bind(this.targetInput, 'keydown', _classPrivateMethodGet$5(this, _onKeyDown, _onKeyDown2).bind(this));
	    }
	  }, {
	    key: "parseTargetProperties",
	    value: function parseTargetProperties() {
	      this.fieldProperty = JSON.parse(this.targetInput.getAttribute('data-property'));
	      var propertyType = this.targetInput.getAttribute('data-selector-type');
	      if (!this.fieldProperty && propertyType) {
	        this.fieldProperty = {
	          Type: propertyType
	        };
	      }
	      if (this.fieldProperty) {
	        this.fieldProperty.Type = this.fieldProperty.Type || propertyType;
	        _classPrivateMethodGet$5(this, _prepareSelectorUsingFieldType, _prepareSelectorUsingFieldType2).call(this);
	      } else {
	        this.context.useSwitcherMenu = false;
	      }
	      this.replaceOnWrite |= this.targetInput.getAttribute('data-select-mode') === 'replace';
	    }
	  }, {
	    key: "openMenu",
	    value: function openMenu(event) {
	      var _this2 = this;
	      var skipPropertiesSwitcher = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
	      if (!skipPropertiesSwitcher && this.context.useSwitcherMenu && !this.targetInput.value) {
	        return this.openPropertiesSwitcherMenu();
	      }
	      if (babelHelpers.classPrivateFieldGet(this, _dialog)) {
	        babelHelpers.classPrivateFieldGet(this, _dialog).show();
	        return;
	      }
	      this.fillGroups();
	      this.onMenuOpen();
	      var menuItems = [];
	      for (var _i = 0, _Object$values = Object.values(babelHelpers.classPrivateFieldGet(this, _menuGroups)); _i < _Object$values.length; _i++) {
	        var group = _Object$values[_i];
	        if (group.children.length > 0) {
	          menuItems.push(group);
	        }
	      }
	      if (menuItems.length === 1) {
	        menuItems = menuItems[0].children;
	      }
	      var menuId = this.menuButton.getAttribute('data-selector-id');
	      if (!menuId) {
	        menuId = bizproc_automation.Helper.generateUniqueId();
	        this.menuButton.setAttribute('data-selector-id', menuId);
	      }
	      babelHelpers.classPrivateFieldSet(this, _dialog, new ui_entitySelector.Dialog({
	        targetNode: this.menuButton,
	        width: 500,
	        height: 300,
	        multiple: false,
	        dropdownMode: true,
	        enableSearch: true,
	        items: this.injectDialogMenuTitles(menuItems),
	        showAvatars: false,
	        events: {
	          'Item:onBeforeSelect': function ItemOnBeforeSelect(event) {
	            event.preventDefault();
	            var item = event.getData().item;
	            _this2.onFieldSelect(item.getCustomData().get('field'));
	          }
	        },
	        compactView: true
	      }));
	      babelHelpers.classPrivateFieldGet(this, _dialog).show();
	    }
	  }, {
	    key: "fillGroups",
	    value: function fillGroups() {
	      this.fillFieldsGroups();
	      this.fillFileGroup();
	    }
	  }, {
	    key: "fillFieldsGroups",
	    value: function fillFieldsGroups() {
	      var _this3 = this;
	      this.addGroup('ROOT', {
	        id: 'ROOT',
	        title: this.context.rootGroupTitle,
	        searchable: false
	      });
	      this.getFields().forEach(function (field) {
	        var groupKey = field.Id.indexOf('.') < 0 ? 'ROOT' : field.Id.split('.')[0];
	        var groupName = '';
	        var fieldName = field.Name;
	        if (field.Name && groupKey !== 'ROOT' && field.Name.indexOf(': ') >= 0) {
	          var names = field.Name.split(': ');
	          groupName = names.shift();
	          fieldName = names.join(': ');
	        }
	        if (field['Id'].indexOf('ASSIGNED_BY_') === 0 && field['Id'] !== 'ASSIGNED_BY_ID' && field['Id'] !== 'ASSIGNED_BY_PRINTABLE') {
	          groupKey = 'ASSIGNED_BY';
	          var _names = field.Name.split(' ');
	          groupName = _names.shift();
	          fieldName = _names.join(' ').replace('(', '').replace(')', '');
	        }
	        if (!_this3.hasGroup(groupKey)) {
	          _this3.addGroup(groupKey, {
	            id: groupKey,
	            title: groupName,
	            searchable: false
	          });
	        }
	        _this3.addGroupItem(groupKey, {
	          id: field.SystemExpression,
	          title: fieldName || field.Id,
	          customData: {
	            field: field
	          }
	        });
	      });
	    }
	  }, {
	    key: "fillFileGroup",
	    value: function fillFileGroup() {
	      var fileFields = this.getFields().filter(function (field) {
	        return field.Type === 'file';
	      });
	      if (fileFields.length > 0) {
	        this.addGroup('__FILES', {
	          id: '__FILES',
	          title: main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_FILES_LINKS'),
	          children: _classPrivateMethodGet$5(this, _prepareFilesMenu, _prepareFilesMenu2).call(this, fileFields),
	          searchable: false
	        });
	      }
	    }
	  }, {
	    key: "onMenuOpen",
	    value: function onMenuOpen() {
	      this.emit('onOpenMenu', {
	        selector: this
	      });
	    }
	  }, {
	    key: "openPropertiesSwitcherMenu",
	    value: function openPropertiesSwitcherMenu() {
	      var _self$fieldProperty;
	      var self = this;
	      main_popup.MenuManager.show(bizproc_automation.Helper.generateUniqueId(), this.menuButton, [{
	        text: main_core.Loc.getMessage('BIZPROC_AUTOMATION_ASK_CONSTANT'),
	        disabled: ((_self$fieldProperty = self.fieldProperty) === null || _self$fieldProperty === void 0 ? void 0 : _self$fieldProperty.Type) === 'file',
	        onclick: function onclick(event) {
	          this.popupWindow.close();
	          self.emit('onAskConstant', {
	            fieldProperty: self.fieldProperty
	          });
	        }
	      }, {
	        text: main_core.Loc.getMessage('BIZPROC_AUTOMATION_ASK_PARAMETER'),
	        onclick: function onclick(event) {
	          this.popupWindow.close();
	          self.emit('onAskParameter', {
	            fieldProperty: self.fieldProperty
	          });
	        }
	      }, {
	        text: main_core.Loc.getMessage('BIZPROC_AUTOMATION_ASK_MANUAL'),
	        onclick: function onclick(event) {
	          this.popupWindow.close();
	          self.openMenu(event, true);
	        }
	      }], {
	        autoHide: true,
	        offsetLeft: 20,
	        angle: {
	          position: 'top'
	        },
	        events: {
	          onPopupClose: function onPopupClose() {
	            this.destroy();
	          }
	        }
	      });
	      babelHelpers.classPrivateFieldSet(this, _switcherDialog, main_popup.MenuManager.currentItem);
	      return true;
	    }
	  }, {
	    key: "injectDialogMenuTitles",
	    value: function injectDialogMenuTitles(items) {
	      var _this4 = this;
	      items.forEach(function (parent) {
	        if (main_core.Type.isArray(parent.children)) {
	          _this4.injectDialogMenuSupertitles(parent.title, parent.children);
	        }
	      });
	      return items;
	    }
	  }, {
	    key: "injectDialogMenuSupertitles",
	    value: function injectDialogMenuSupertitles(title, children) {
	      var _this5 = this;
	      children.forEach(function (child) {
	        if (!child.supertitle) {
	          child.supertitle = title;
	        }
	        if (main_core.Type.isArrayFilled(child.children)) {
	          _this5.injectDialogMenuSupertitles(child.title, child.children);
	        }
	      });
	    }
	  }, {
	    key: "onFieldSelect",
	    value: function onFieldSelect(field) {
	      if (!field) {
	        return;
	      }
	      var inputType = this.targetInput.tagName.toLowerCase();
	      if (inputType === 'select') {
	        var expressionOption = this.targetInput.querySelector('[data-role="expression"]');
	        if (!expressionOption) {
	          expressionOption = this.targetInput.appendChild(main_core.Dom.create('option', {
	            attrs: {
	              'data-role': 'expression'
	            }
	          }));
	        }
	        expressionOption.setAttribute('value', field.Expression);
	        expressionOption.textContent = field['Expression'];
	        expressionOption.selected = true;
	      } else if (inputType === 'label') {
	        this.targetInput.textContent = field.Expression;
	        var hiddenInput = document.getElementById(this.targetInput.getAttribute('for'));
	        if (hiddenInput) {
	          hiddenInput.value = field.Expression;
	        }
	      } else {
	        if (this.replaceOnWrite) {
	          this.targetInput.value = field.Expression;
	          this.targetInput.selectionEnd = this.targetInput.value.length;
	        } else {
	          var beforePart = '';
	          var middlePart = field.Expression;
	          var afterPart = '';
	          if (main_core.Type.isStringFilled(this.targetInput.value)) {
	            beforePart = this.targetInput.value.substr(0, this.targetInput.selectionEnd);
	            afterPart = this.targetInput.value.substr(this.targetInput.selectionEnd);
	          }
	          this.targetInput.value = beforePart + middlePart + afterPart;
	          this.targetInput.selectionEnd = beforePart.length + middlePart.length;
	        }
	      }
	      BX.fireEvent(this.targetInput, 'change');
	      this.emit('Field:Selected', {
	        field: field
	      });
	    }
	  }, {
	    key: "destroy",
	    value: function destroy() {
	      if (babelHelpers.classPrivateFieldGet(this, _dialog)) {
	        babelHelpers.classPrivateFieldGet(this, _dialog).destroy();
	      }
	      if (babelHelpers.classPrivateFieldGet(this, _switcherDialog)) {
	        babelHelpers.classPrivateFieldGet(this, _switcherDialog).destroy();
	      }
	    }
	  }, {
	    key: "getFields",
	    value: function getFields() {
	      var printablePrefix = main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_MOD_PRINTABLE_PREFIX');
	      var names = this.context.fields.map(function (field) {
	        return field.Name;
	      }).join('\n');
	      var fields = [];
	      this.basisFields.forEach(function (field) {
	        field.ObjectId = 'Document';
	        var custom = field['BaseType'] === 'string' && field['Type'] !== 'string';
	        if (!custom) {
	          fields.push(field);
	        }

	        //generate printable version
	        if (field['Type'] === 'user' || field['Type'] === 'bool' || field['Type'] === 'file' || custom) {
	          var printableName = field['Name'] + ' ' + printablePrefix;
	          if (names.indexOf(printableName) < 0) {
	            var printableField = BX.clone(field);
	            var printableTag = field['Type'] === 'user' ? 'friendly' : 'printable';
	            printableField['Name'] = printableName;
	            printableField['Type'] = 'string';
	            printableField['SystemExpression'] = '{=Document:' + printableField['Id'] + ' > ' + printableTag + '}';
	            printableField['Expression'] = '{{' + field['Name'] + ' > ' + printableTag + '}}';
	            fields.push(printableField);
	          }
	        }
	        if (field['BaseType'] === 'date' || field['BaseType'] === 'datetime' || field['BaseType'] === 'time') {
	          var serverField = BX.clone(field);
	          serverField['Name'] += ' ' + main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_MOD_DATE_BY_SERVER');
	          serverField['Type'] = 'string';
	          serverField['SystemExpression'] = "{=Document:".concat(serverField['Id'], " > server}");
	          serverField['Expression'] = "{{".concat(field['Name'], " > server}}");
	          fields.push(serverField);
	          var responsibleField = BX.clone(field);
	          responsibleField['Name'] += ' ' + main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_MOD_DATE_BY_RESPONSIBLE');
	          responsibleField['Type'] = 'string';
	          responsibleField['SystemExpression'] = "{=Document:".concat(serverField['Id'], " > responsible}");
	          responsibleField['Expression'] = "{{".concat(field['Name'], " > responsible}}");
	          fields.push(responsibleField);
	        }
	      });
	      return fields;
	    }
	  }]);
	  return InlineSelector;
	}(main_core_events.EventEmitter);
	function _normalizeGroup2(group) {
	  var _this6 = this;
	  if (!main_core.Type.isArray(group.children)) {
	    group.children = [];
	  }
	  group.children = group.children.filter(function (item) {
	    var _item$customData;
	    return (_item$customData = item.customData) !== null && _item$customData !== void 0 && _item$customData.field ? _classPrivateMethodGet$5(_this6, _shouldShowField, _shouldShowField2).call(_this6, item.customData.field) : true;
	  }).map(function (childGroup) {
	    return _classPrivateMethodGet$5(_this6, _normalizeGroup, _normalizeGroup2).call(_this6, childGroup);
	  });
	  return _objectSpread$3({
	    entityId: 'bp',
	    tabs: 'recents'
	  }, group);
	}
	function _prepareSelectorUsingFieldType2() {
	  var _this7 = this,
	    _this$fieldProperty;
	  this.basisFields = this.basisFields.filter(function (field) {
	    return _classPrivateMethodGet$5(_this7, _shouldShowField, _shouldShowField2).call(_this7, field);
	  });
	  var type = (_this$fieldProperty = this.fieldProperty) === null || _this$fieldProperty === void 0 ? void 0 : _this$fieldProperty.Type;
	  if (type === 'file') {
	    this.replaceOnWrite = true;
	  } else if (type === 'date' || type === 'datetime') {
	    this.replaceOnWrite = true;
	    var delayIntervalSelector = new bizproc_automation.DelayIntervalSelector({
	      labelNode: this.targetInput,
	      basisFields: this.basisFields,
	      useAfterBasis: true,
	      onchange: function (delay) {
	        this.targetInput.value = delay.toExpression(this.basisFields, bizproc_automation.Helper.getResponsibleUserExpression(this.context.fields));
	      }.bind(this)
	    });
	    delayIntervalSelector.init(bizproc_automation.DelayInterval.fromString(this.targetInput.value, this.basisFields));
	  } else if (type === 'time') {
	    this.replaceOnWrite = true;
	    var timeSelector = new InlineTimeSelector({
	      labelNode: this.targetInput
	    });
	    timeSelector.init(this.targetInput.value);
	  }
	}
	function _shouldShowField2(field) {
	  var _this$fieldProperty2;
	  var fieldType = (_this$fieldProperty2 = this.fieldProperty) === null || _this$fieldProperty2 === void 0 ? void 0 : _this$fieldProperty2.Type;
	  if (fieldType === 'file') {
	    return field.Type === 'file';
	  } else if (fieldType === 'date' || fieldType === 'datetime') {
	    return field.Type === 'date' || field.Type === 'datetime';
	  } else if (fieldType === 'time') {
	    return field.Type === 'date' || field.Type === 'datetime' || field.Type === 'time';
	  }
	  return true;
	}
	function _onKeyDown2(event) {
	  if (event.keyCode === 45 && event.altKey === false && event.ctrlKey === false && event.shiftKey === false) {
	    this.openMenu(event);
	    event.preventDefault();
	  }
	}
	function _prepareFilesMenu2(fileFields) {
	  return fileFields.map(function (field) {
	    var exp = field['ObjectId'] === 'Document' ? '{{' + field['Name'] + ' > shortlink}}' : '{{~' + field['ObjectId'] + ':' + field['Id'] + ' > shortlink}}';
	    var title = field.Name || field.Id;
	    if (field.ObjectName) {
	      title = field.ObjectName + ': ' + title;
	    }
	    return {
	      title: title,
	      customData: {
	        field: {
	          Id: field['Id'] + '_shortlink',
	          ObjectId: field['ObjectId'],
	          Name: field['Name'],
	          Type: 'string',
	          Expression: exp,
	          SystemExpression: '{=' + field['ObjectId'] + ':' + field['Id'] + ' > shortlink}'
	        }
	      },
	      id: exp
	    };
	  });
	}

	function ownKeys$4(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread$4(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys$4(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys$4(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	function _classPrivateMethodInitSpec$6(obj, privateSet) { _checkPrivateRedeclaration$l(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec$l(obj, privateMap, value) { _checkPrivateRedeclaration$l(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$l(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet$6(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _condition$3 = /*#__PURE__*/new WeakMap();
	var _isMixedConditionGroup = /*#__PURE__*/new WeakSet();
	var InlineSelectorCondition = /*#__PURE__*/function (_InlineSelector) {
	  babelHelpers.inherits(InlineSelectorCondition, _InlineSelector);
	  function InlineSelectorCondition(props) {
	    var _this;
	    babelHelpers.classCallCheck(this, InlineSelectorCondition);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(InlineSelectorCondition).call(this, props));
	    _classPrivateMethodInitSpec$6(babelHelpers.assertThisInitialized(_this), _isMixedConditionGroup);
	    _classPrivateFieldInitSpec$l(babelHelpers.assertThisInitialized(_this), _condition$3, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _condition$3, props.condition);
	    return _this;
	  }
	  babelHelpers.createClass(InlineSelectorCondition, [{
	    key: "renderTo",
	    value: function renderTo(target) {
	      this.targetInput = target;
	      this.menuButton = target;
	      this.parseTargetProperties();
	      this.bindTargetEvents();
	    }
	  }, {
	    key: "fillGroups",
	    value: function fillGroups() {
	      this.fillFieldsGroups();
	    }
	  }, {
	    key: "onMenuOpen",
	    value: function onMenuOpen() {
	      this.emit('onOpenMenu', {
	        selector: this,
	        // TODO - rename
	        isMixedCondition: _classPrivateMethodGet$6(this, _isMixedConditionGroup, _isMixedConditionGroup2).call(this)
	      });
	    }
	  }, {
	    key: "onFieldSelect",
	    value: function onFieldSelect(field) {
	      this.emit('change', {
	        field: field
	      });
	    }
	  }, {
	    key: "getFields",
	    value: function getFields() {
	      return this.context.fields.map(function (field) {
	        return _objectSpread$4(_objectSpread$4({}, field), {}, {
	          ObjectId: 'Document'
	        });
	      });
	    }
	  }]);
	  return InlineSelectorCondition;
	}(InlineSelector);
	function _isMixedConditionGroup2() {
	  return babelHelpers.classPrivateFieldGet(this, _condition$3) && babelHelpers.classPrivateFieldGet(this, _condition$3).parentGroup && babelHelpers.classPrivateFieldGet(this, _condition$3).parentGroup.type === bizproc_automation.ConditionGroup.CONDITION_TYPE.Mixed;
	}

	var _templateObject$3;
	function _classPrivateMethodInitSpec$7(obj, privateSet) { _checkPrivateRedeclaration$m(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec$m(obj, privateMap, value) { _checkPrivateRedeclaration$m(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$m(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet$7(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _editorNode = /*#__PURE__*/new WeakMap();
	var _eventHandlers = /*#__PURE__*/new WeakMap();
	var _bindEvents = /*#__PURE__*/new WeakSet();
	var _unbindEvents = /*#__PURE__*/new WeakSet();
	var _bindEditorHooks = /*#__PURE__*/new WeakSet();
	var _getEditor = /*#__PURE__*/new WeakSet();
	var InlineSelectorHtml = /*#__PURE__*/function (_InlineSelector) {
	  babelHelpers.inherits(InlineSelectorHtml, _InlineSelector);
	  function InlineSelectorHtml() {
	    var _babelHelpers$getProt;
	    var _this;
	    babelHelpers.classCallCheck(this, InlineSelectorHtml);
	    for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
	      args[_key] = arguments[_key];
	    }
	    _this = babelHelpers.possibleConstructorReturn(this, (_babelHelpers$getProt = babelHelpers.getPrototypeOf(InlineSelectorHtml)).call.apply(_babelHelpers$getProt, [this].concat(args)));
	    _classPrivateMethodInitSpec$7(babelHelpers.assertThisInitialized(_this), _getEditor);
	    _classPrivateMethodInitSpec$7(babelHelpers.assertThisInitialized(_this), _bindEditorHooks);
	    _classPrivateMethodInitSpec$7(babelHelpers.assertThisInitialized(_this), _unbindEvents);
	    _classPrivateMethodInitSpec$7(babelHelpers.assertThisInitialized(_this), _bindEvents);
	    _classPrivateFieldInitSpec$m(babelHelpers.assertThisInitialized(_this), _editorNode, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$m(babelHelpers.assertThisInitialized(_this), _eventHandlers, {
	      writable: true,
	      value: {
	        'OnEditorInitedAfter': _classPrivateMethodGet$7(babelHelpers.assertThisInitialized(_this), _bindEditorHooks, _bindEditorHooks2).bind(babelHelpers.assertThisInitialized(_this))
	      }
	    });
	    return _this;
	  }
	  babelHelpers.createClass(InlineSelectorHtml, [{
	    key: "destroy",
	    value: function destroy() {
	      _classPrivateMethodGet$7(this, _unbindEvents, _unbindEvents2).call(this);
	    }
	  }, {
	    key: "renderTo",
	    value: function renderTo(targetInput) {
	      this.targetInput = targetInput;
	      babelHelpers.classPrivateFieldSet(this, _editorNode, targetInput.querySelector('.bx-html-editor'));
	      this.menuButton = main_core.Tag.render(_templateObject$3 || (_templateObject$3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<span\n\t\t\t\tonclick=\"", "\"\n\t\t\t\tclass=\"bizproc-automation-popup-select-dotted\"\n\t\t\t></span>\n\t\t"])), this.openMenu.bind(this));
	      this.parseTargetProperties();
	      this.bindTargetEvents();
	      targetInput.firstElementChild.appendChild(this.menuButton);
	      _classPrivateMethodGet$7(this, _bindEvents, _bindEvents2).call(this);
	    }
	  }, {
	    key: "onFieldSelect",
	    value: function onFieldSelect(field) {
	      var insertText = field.Expression;
	      var editor = _classPrivateMethodGet$7(this, _getEditor, _getEditor2).call(this);
	      if (editor && editor.InsertHtml) {
	        if (editor.synchro.IsFocusedOnTextarea()) {
	          editor.textareaView.Focus();
	          editor.textareaView.WrapWith('', '', insertText);
	        } else {
	          editor.InsertHtml(insertText);
	        }
	        editor.synchro.Sync();
	      }
	    }
	  }, {
	    key: "onBeforeSave",
	    value: function onBeforeSave() {
	      var editor = _classPrivateMethodGet$7(this, _getEditor, _getEditor2).call(this);
	      if (editor && editor.SaveContent) {
	        editor.SaveContent();
	      }
	    }
	  }, {
	    key: "onPopupResize",
	    value: function onPopupResize() {
	      var editor = _classPrivateMethodGet$7(this, _getEditor, _getEditor2).call(this);
	      if (editor && editor.ResizeSceleton) {
	        editor.ResizeSceleton();
	      }
	    }
	  }]);
	  return InlineSelectorHtml;
	}(InlineSelector);
	function _bindEvents2() {
	  for (var _i = 0, _Object$entries = Object.entries(babelHelpers.classPrivateFieldGet(this, _eventHandlers)); _i < _Object$entries.length; _i++) {
	    var _Object$entries$_i = babelHelpers.slicedToArray(_Object$entries[_i], 2),
	      name = _Object$entries$_i[0],
	      handler = _Object$entries$_i[1];
	    BX.addCustomEvent(name, handler);
	  }
	}
	function _unbindEvents2() {
	  for (var _i2 = 0, _Object$entries2 = Object.entries(babelHelpers.classPrivateFieldGet(this, _eventHandlers)); _i2 < _Object$entries2.length; _i2++) {
	    var _Object$entries2$_i = babelHelpers.slicedToArray(_Object$entries2[_i2], 2),
	      name = _Object$entries2$_i[0],
	      handler = _Object$entries2$_i[1];
	    BX.removeCustomEvent(name, handler);
	  }
	}
	function _bindEditorHooks2(editor) {
	  if (editor.dom.cont !== babelHelpers.classPrivateFieldGet(this, _editorNode)) {
	    return false;
	  }
	  var header = '';
	  var footer = '';
	  var cutHeader = function cutHeader(content) {
	    var shouldSaveHeader = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
	    return content.replace(/(^[\s\S]*?)(<body.*?>)/i, function (str) {
	      if (shouldSaveHeader) {
	        header = str;
	      }
	      return '';
	    });
	  };
	  var cutFooter = function cutFooter(content) {
	    var shouldSaveFooter = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
	    return content.replace(/(<\/body>[\s\S]*?$)/i, function (str) {
	      if (shouldSaveFooter) {
	        footer = str;
	      }
	      return '';
	    });
	  };
	  BX.addCustomEvent(editor, 'OnParse', function (mode) {
	    if (!mode) {
	      this.content = cutFooter(cutHeader(this.content, true), true);
	    }
	  });
	  BX.addCustomEvent(editor, 'OnAfterParse', function (mode) {
	    if (mode) {
	      var content = cutFooter(cutHeader(this.content));
	      if (header !== '' && footer !== '') {
	        content = header + content + footer;
	      }
	      this.content = content;
	    }
	  });
	}
	function _getEditor2() {
	  if (babelHelpers.classPrivateFieldGet(this, _editorNode)) {
	    var editorId = babelHelpers.classPrivateFieldGet(this, _editorNode).id.split('-');
	    return BXHtmlEditor.Get(editorId[editorId.length - 1]);
	  }
	  return null;
	}

	function _classPrivateMethodInitSpec$8(obj, privateSet) { _checkPrivateRedeclaration$n(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec$n(obj, privateMap, value) { _checkPrivateRedeclaration$n(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$n(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet$8(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _context$1 = /*#__PURE__*/new WeakMap();
	var _checkbox = /*#__PURE__*/new WeakMap();
	var _needSync = /*#__PURE__*/new WeakMap();
	var _getKey = /*#__PURE__*/new WeakSet();
	var _getValue = /*#__PURE__*/new WeakSet();
	var SaveStateCheckbox = /*#__PURE__*/function () {
	  function SaveStateCheckbox(props) {
	    babelHelpers.classCallCheck(this, SaveStateCheckbox);
	    _classPrivateMethodInitSpec$8(this, _getValue);
	    _classPrivateMethodInitSpec$8(this, _getKey);
	    _classPrivateFieldInitSpec$n(this, _context$1, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$n(this, _checkbox, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$n(this, _needSync, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldSet(this, _context$1, props.context);
	    babelHelpers.classPrivateFieldSet(this, _checkbox, props.checkbox);
	    babelHelpers.classPrivateFieldSet(this, _needSync, props.needSync);
	    if (props.needSync) {
	      var category = 'save_state_checkbox';
	      var savedState = babelHelpers.classPrivateFieldGet(this, _context$1).get('userOptions').get(category, _classPrivateMethodGet$8(this, _getKey, _getKey2).call(this), 'N');
	      if (savedState === 'Y') {
	        babelHelpers.classPrivateFieldGet(this, _checkbox).checked = true;
	      }
	    }
	  }
	  babelHelpers.createClass(SaveStateCheckbox, [{
	    key: "destroy",
	    value: function destroy() {
	      if (babelHelpers.classPrivateFieldGet(this, _needSync)) {
	        babelHelpers.classPrivateFieldGet(this, _context$1).get('userOptions').set('save_state_checkboxes', _classPrivateMethodGet$8(this, _getKey, _getKey2).call(this), _classPrivateMethodGet$8(this, _getValue, _getValue2).call(this));
	      }
	    }
	  }]);
	  return SaveStateCheckbox;
	}();
	function _getKey2() {
	  return babelHelpers.classPrivateFieldGet(this, _checkbox).getAttribute('data-save-state-key');
	}
	function _getValue2() {
	  return babelHelpers.classPrivateFieldGet(this, _checkbox).checked ? 'Y' : 'N';
	}

	var UserSelector = /*#__PURE__*/function (_InlineSelector) {
	  babelHelpers.inherits(UserSelector, _InlineSelector);
	  function UserSelector() {
	    babelHelpers.classCallCheck(this, UserSelector);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(UserSelector).apply(this, arguments));
	  }
	  babelHelpers.createClass(UserSelector, [{
	    key: "renderTo",
	    value: function renderTo(targetInput) {
	      this.targetInput = targetInput;
	      this.menuButton = targetInput;
	      this.fieldProperty = JSON.parse(targetInput.getAttribute('data-property'));
	      if (!this.fieldProperty) {
	        this.context.useSwitcherMenu = false;
	      }
	      var additionalUserFields = this.context.get('additionalUserFields');
	      this.userSelector = BX.Bizproc.UserSelector.decorateNode(targetInput, {
	        additionalFields: main_core.Type.isArray(additionalUserFields) ? additionalUserFields : []
	      });
	    }
	  }, {
	    key: "destroy",
	    value: function destroy() {
	      babelHelpers.get(babelHelpers.getPrototypeOf(UserSelector.prototype), "destroy", this).call(this);
	      if (this.userSelector) {
	        this.userSelector.destroy();
	        this.userSelector = null;
	      }
	    }
	  }]);
	  return UserSelector;
	}(InlineSelector);

	var _templateObject$4, _templateObject2$3, _templateObject3$2, _templateObject4$2, _templateObject5$2, _templateObject6$1, _templateObject7, _templateObject8;
	function _createForOfIteratorHelper$5(o, allowArrayLike) { var it = typeof Symbol !== "undefined" && o[Symbol.iterator] || o["@@iterator"]; if (!it) { if (Array.isArray(o) || (it = _unsupportedIterableToArray$5(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = it.call(o); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it["return"] != null) it["return"](); } finally { if (didErr) throw err; } } }; }
	function _unsupportedIterableToArray$5(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray$5(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray$5(o, minLen); }
	function _arrayLikeToArray$5(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i]; return arr2; }
	function _classPrivateMethodInitSpec$9(obj, privateSet) { _checkPrivateRedeclaration$o(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec$o(obj, privateMap, value) { _checkPrivateRedeclaration$o(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$o(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classStaticPrivateMethodGet(receiver, classConstructor, method) { _classCheckPrivateStaticAccess$3(receiver, classConstructor); return method; }
	function _classCheckPrivateStaticAccess$3(receiver, classConstructor) { if (receiver !== classConstructor) { throw new TypeError("Private static access of wrong provenance"); } }
	function _classPrivateMethodGet$9(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _type$3 = /*#__PURE__*/new WeakMap();
	var _multiple = /*#__PURE__*/new WeakMap();
	var _required = /*#__PURE__*/new WeakMap();
	var _valueInputName = /*#__PURE__*/new WeakMap();
	var _typeInputName = /*#__PURE__*/new WeakMap();
	var _useDisk = /*#__PURE__*/new WeakMap();
	var _label = /*#__PURE__*/new WeakMap();
	var _labelFile = /*#__PURE__*/new WeakMap();
	var _labelDisk = /*#__PURE__*/new WeakMap();
	var _diskUploader = /*#__PURE__*/new WeakMap();
	var _diskControllerNode = /*#__PURE__*/new WeakMap();
	var _fileItemsNode = /*#__PURE__*/new WeakMap();
	var _fileControllerNode = /*#__PURE__*/new WeakMap();
	var _menuId = /*#__PURE__*/new WeakMap();
	var _createBaseNode = /*#__PURE__*/new WeakSet();
	var _showTypeControlLayout = /*#__PURE__*/new WeakSet();
	var _showDiskControllerLayout = /*#__PURE__*/new WeakSet();
	var _hideDiskControllerLayout = /*#__PURE__*/new WeakSet();
	var _showFileControllerLayout = /*#__PURE__*/new WeakSet();
	var _hideFileControllerLayout = /*#__PURE__*/new WeakSet();
	var _getDiskUploader = /*#__PURE__*/new WeakSet();
	var _onTypeChange = /*#__PURE__*/new WeakSet();
	var _addFileItem = /*#__PURE__*/new WeakSet();
	var _isFileItemSelected = /*#__PURE__*/new WeakSet();
	var _removeFileItem = /*#__PURE__*/new WeakSet();
	var _onFileFieldAddClick = /*#__PURE__*/new WeakSet();
	var _createFileItemNode = /*#__PURE__*/new WeakSet();
	var FileSelector = /*#__PURE__*/function (_InlineSelector) {
	  babelHelpers.inherits(FileSelector, _InlineSelector);
	  function FileSelector(props) {
	    var _this;
	    babelHelpers.classCallCheck(this, FileSelector);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(FileSelector).call(this, props));
	    _classPrivateMethodInitSpec$9(babelHelpers.assertThisInitialized(_this), _createFileItemNode);
	    _classPrivateMethodInitSpec$9(babelHelpers.assertThisInitialized(_this), _onFileFieldAddClick);
	    _classPrivateMethodInitSpec$9(babelHelpers.assertThisInitialized(_this), _removeFileItem);
	    _classPrivateMethodInitSpec$9(babelHelpers.assertThisInitialized(_this), _isFileItemSelected);
	    _classPrivateMethodInitSpec$9(babelHelpers.assertThisInitialized(_this), _addFileItem);
	    _classPrivateMethodInitSpec$9(babelHelpers.assertThisInitialized(_this), _onTypeChange);
	    _classPrivateMethodInitSpec$9(babelHelpers.assertThisInitialized(_this), _getDiskUploader);
	    _classPrivateMethodInitSpec$9(babelHelpers.assertThisInitialized(_this), _hideFileControllerLayout);
	    _classPrivateMethodInitSpec$9(babelHelpers.assertThisInitialized(_this), _showFileControllerLayout);
	    _classPrivateMethodInitSpec$9(babelHelpers.assertThisInitialized(_this), _hideDiskControllerLayout);
	    _classPrivateMethodInitSpec$9(babelHelpers.assertThisInitialized(_this), _showDiskControllerLayout);
	    _classPrivateMethodInitSpec$9(babelHelpers.assertThisInitialized(_this), _showTypeControlLayout);
	    _classPrivateMethodInitSpec$9(babelHelpers.assertThisInitialized(_this), _createBaseNode);
	    _classPrivateFieldInitSpec$o(babelHelpers.assertThisInitialized(_this), _type$3, {
	      writable: true,
	      value: FileSelector.TYPE.None
	    });
	    _classPrivateFieldInitSpec$o(babelHelpers.assertThisInitialized(_this), _multiple, {
	      writable: true,
	      value: false
	    });
	    _classPrivateFieldInitSpec$o(babelHelpers.assertThisInitialized(_this), _required, {
	      writable: true,
	      value: false
	    });
	    _classPrivateFieldInitSpec$o(babelHelpers.assertThisInitialized(_this), _valueInputName, {
	      writable: true,
	      value: ''
	    });
	    _classPrivateFieldInitSpec$o(babelHelpers.assertThisInitialized(_this), _typeInputName, {
	      writable: true,
	      value: ''
	    });
	    _classPrivateFieldInitSpec$o(babelHelpers.assertThisInitialized(_this), _useDisk, {
	      writable: true,
	      value: false
	    });
	    _classPrivateFieldInitSpec$o(babelHelpers.assertThisInitialized(_this), _label, {
	      writable: true,
	      value: ''
	    });
	    _classPrivateFieldInitSpec$o(babelHelpers.assertThisInitialized(_this), _labelFile, {
	      writable: true,
	      value: ''
	    });
	    _classPrivateFieldInitSpec$o(babelHelpers.assertThisInitialized(_this), _labelDisk, {
	      writable: true,
	      value: ''
	    });
	    _classPrivateFieldInitSpec$o(babelHelpers.assertThisInitialized(_this), _diskUploader, {
	      writable: true,
	      value: null
	    });
	    _classPrivateFieldInitSpec$o(babelHelpers.assertThisInitialized(_this), _diskControllerNode, {
	      writable: true,
	      value: null
	    });
	    _classPrivateFieldInitSpec$o(babelHelpers.assertThisInitialized(_this), _fileItemsNode, {
	      writable: true,
	      value: null
	    });
	    _classPrivateFieldInitSpec$o(babelHelpers.assertThisInitialized(_this), _fileControllerNode, {
	      writable: true,
	      value: null
	    });
	    _classPrivateFieldInitSpec$o(babelHelpers.assertThisInitialized(_this), _menuId, {
	      writable: true,
	      value: void 0
	    });
	    _this.context.set('fileFields', _this.context.fields.filter(function (field) {
	      return field.Type === 'file';
	    }));
	    return _this;
	  }
	  babelHelpers.createClass(FileSelector, [{
	    key: "destroy",
	    value: function destroy() {
	      if (this.menu) {
	        this.menu.popupWindow.close();
	      }
	    }
	  }, {
	    key: "renderTo",
	    value: function renderTo(targetInput) {
	      this.targetInput = targetInput;
	      var selected = this.parseTargetProperties();
	      this.targetInput.appendChild(_classPrivateMethodGet$9(this, _createBaseNode, _createBaseNode2).call(this));
	      _classPrivateMethodGet$9(this, _showTypeControlLayout, _showTypeControlLayout2).call(this, selected);
	      // this.setFileFields()
	      // this.createDom
	    }
	  }, {
	    key: "parseTargetProperties",
	    value: function parseTargetProperties() {
	      var config = JSON.parse(this.targetInput.getAttribute('data-config'));
	      if (!main_core.Type.isPlainObject(config)) {
	        config = {};
	      }
	      babelHelpers.classPrivateFieldSet(this, _type$3, config.type || FileSelector.TYPE.File);
	      if (config.selected && !config.selected.length) {
	        babelHelpers.classPrivateFieldSet(this, _type$3, FileSelector.TYPE.None);
	      }
	      babelHelpers.classPrivateFieldSet(this, _multiple, config.multiple || false);
	      babelHelpers.classPrivateFieldSet(this, _required, config.required || false);
	      babelHelpers.classPrivateFieldSet(this, _valueInputName, config.valueInputName || '');
	      babelHelpers.classPrivateFieldSet(this, _typeInputName, config.typeInputName || '');
	      babelHelpers.classPrivateFieldSet(this, _useDisk, config.useDisk || false);
	      babelHelpers.classPrivateFieldSet(this, _label, config.label || 'Attachment');
	      babelHelpers.classPrivateFieldSet(this, _labelFile, config.labelFile || 'File');
	      babelHelpers.classPrivateFieldSet(this, _labelDisk, config.labelDisk || 'Disk');
	      if (config.selected && config.selected.length > 0) {
	        return main_core.Runtime.clone(config.selected);
	      }
	    }
	  }, {
	    key: "addItems",
	    value: function addItems(items) {
	      if (babelHelpers.classPrivateFieldGet(this, _type$3) === FileSelector.TYPE.File) {
	        var _iterator = _createForOfIteratorHelper$5(items),
	          _step;
	        try {
	          for (_iterator.s(); !(_step = _iterator.n()).done;) {
	            var fileItem = _step.value;
	            _classPrivateMethodGet$9(this, _addFileItem, _addFileItem2).call(this, fileItem);
	          }
	        } catch (err) {
	          _iterator.e(err);
	        } finally {
	          _iterator.f();
	        }
	      } else {
	        _classPrivateMethodGet$9(this, _getDiskUploader, _getDiskUploader2).call(this).setValues(_classStaticPrivateMethodGet(FileSelector, FileSelector, _convertToDiskItems).call(FileSelector, items));
	      }
	    }
	  }, {
	    key: "onFieldSelect",
	    value: function onFieldSelect(field) {
	      _classPrivateMethodGet$9(this, _addFileItem, _addFileItem2).call(this, {
	        id: field.Id,
	        expression: field.Expression,
	        name: field.Name,
	        type: FileSelector.TYPE.File
	      });
	    }
	  }, {
	    key: "onBeforeSave",
	    value: function onBeforeSave() {
	      var ids = [];
	      if (babelHelpers.classPrivateFieldGet(this, _type$3) === FileSelector.TYPE.Disk) {
	        ids = _classPrivateMethodGet$9(this, _getDiskUploader, _getDiskUploader2).call(this).getValues();
	      } else if (babelHelpers.classPrivateFieldGet(this, _type$3) === FileSelector.TYPE.File) {
	        ids = Array.from(babelHelpers.classPrivateFieldGet(this, _fileItemsNode).childNodes).map(function (node) {
	          return node.getAttribute('data-file-expression');
	        }).filter(function (id) {
	          return id !== '';
	        });
	      }
	      var _iterator2 = _createForOfIteratorHelper$5(ids),
	        _step2;
	      try {
	        for (_iterator2.s(); !(_step2 = _iterator2.n()).done;) {
	          var id = _step2.value;
	          this.targetInput.appendChild(main_core.Tag.render(_templateObject$4 || (_templateObject$4 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<input\n\t\t\t\t\ttype=\"hidden\"\n\t\t\t\t\tname=\"", "\"\n\t\t\t\t\tvalue=\"", "\"\n\t\t\t\t/>\n\t\t\t"])), babelHelpers.classPrivateFieldGet(this, _valueInputName) + (babelHelpers.classPrivateFieldGet(this, _multiple) ? '[]' : ''), id));
	        }
	      } catch (err) {
	        _iterator2.e(err);
	      } finally {
	        _iterator2.f();
	      }
	    }
	  }]);
	  return FileSelector;
	}(InlineSelector);
	function _createBaseNode2() {
	  var idSalt = bizproc_automation.Helper.generateUniqueId();
	  var fileRadio = null;
	  var fileTypeOptions = [];
	  if (this.context.get('fileFields').length > 0) {
	    fileRadio = main_core.Tag.render(_templateObject2$3 || (_templateObject2$3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<input\n\t\t\t\t\tid=\"type-1", "\"\n\t\t\t\t\tclass=\"bizproc-automation-popup-select-input\"\n\t\t\t\t\ttype=\"radio\"\n\t\t\t\t\tname=\"", "\"\n\t\t\t\t\tvalue=\"", "\"\n\t\t\t\t\t", "\n\t\t\t\t/>\n\t\t\t"])), idSalt, babelHelpers.classPrivateFieldGet(this, _typeInputName), FileSelector.TYPE.File, babelHelpers.classPrivateFieldGet(this, _type$3) === FileSelector.TYPE.File ? 'checked' : '');
	  }
	  var diskFileRadio = main_core.Tag.render(_templateObject3$2 || (_templateObject3$2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<input\n\t\t\t\tid=\"type-2", "\"\n\t\t\t\tclass=\"bizproc-automation-popup-select-input\"\n\t\t\t\ttype=\"radio\"\n\t\t\t\tname=\"", "\"\n\t\t\t\tvalue=\"", "\"\n\t\t\t\t", "\n\t\t\t/>\n\t\t"])), idSalt, babelHelpers.classPrivateFieldGet(this, _typeInputName), FileSelector.TYPE.Disk, babelHelpers.classPrivateFieldGet(this, _type$3) === FileSelector.TYPE.Disk ? 'checked' : '');
	  fileTypeOptions.push(main_core.Tag.render(_templateObject4$2 || (_templateObject4$2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<span class=\"bizproc-automation-popup-settings-title\">", ":</span>\n\t\t"])), babelHelpers.classPrivateFieldGet(this, _label)));
	  if (fileRadio) {
	    fileTypeOptions.push(fileRadio, main_core.Tag.render(_templateObject5$2 || (_templateObject5$2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<label\n\t\t\t\t\tclass=\"bizproc-automation-popup-settings-link\"\n\t\t\t\t\tfor=\"type-1", "\"\n\t\t\t\t\tonclick=\"", "\"\n\t\t\t\t>\n\t\t\t\t", "\n\t\t\t\t</label>\n\t\t\t"])), idSalt, _classPrivateMethodGet$9(this, _onTypeChange, _onTypeChange2).bind(this, FileSelector.TYPE.File), babelHelpers.classPrivateFieldGet(this, _labelFile)));
	  }
	  fileTypeOptions.push(diskFileRadio, main_core.Tag.render(_templateObject6$1 || (_templateObject6$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<label\n\t\t\t\tclass=\"bizproc-automation-popup-settings-link\"\n\t\t\t\tfor=\"type-2", "\"\n\t\t\t\tonclick=\"", "\"\n\t\t\t>\n\t\t\t", "\n\t\t\t</label>\n\t\t"])), idSalt, _classPrivateMethodGet$9(this, _onTypeChange, _onTypeChange2).bind(this, FileSelector.TYPE.Disk), babelHelpers.classPrivateFieldGet(this, _labelDisk)));
	  return main_core.Tag.render(_templateObject7 || (_templateObject7 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"bizproc-automation-popup-settings-block\">\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"])), fileTypeOptions);
	}
	function _showTypeControlLayout2(selected) {
	  if (babelHelpers.classPrivateFieldGet(this, _type$3) === FileSelector.TYPE.Disk) {
	    _classPrivateMethodGet$9(this, _hideFileControllerLayout, _hideFileControllerLayout2).call(this);
	    _classPrivateMethodGet$9(this, _showDiskControllerLayout, _showDiskControllerLayout2).call(this, selected);
	  } else if (babelHelpers.classPrivateFieldGet(this, _type$3) === FileSelector.TYPE.File) {
	    _classPrivateMethodGet$9(this, _hideDiskControllerLayout, _hideDiskControllerLayout2).call(this);
	    _classPrivateMethodGet$9(this, _showFileControllerLayout, _showFileControllerLayout2).call(this, selected);
	  } else {
	    _classPrivateMethodGet$9(this, _hideFileControllerLayout, _hideFileControllerLayout2).call(this);
	    _classPrivateMethodGet$9(this, _hideDiskControllerLayout, _hideDiskControllerLayout2).call(this);
	  }
	}
	function _showDiskControllerLayout2(selected) {
	  if (!babelHelpers.classPrivateFieldGet(this, _diskControllerNode)) {
	    babelHelpers.classPrivateFieldSet(this, _diskControllerNode, main_core.Dom.create('div'));
	    this.targetInput.appendChild(babelHelpers.classPrivateFieldGet(this, _diskControllerNode));
	    var diskUploader = _classPrivateMethodGet$9(this, _getDiskUploader, _getDiskUploader2).call(this);
	    diskUploader.layout(babelHelpers.classPrivateFieldGet(this, _diskControllerNode));
	    diskUploader.show(true);
	    if (selected) {
	      this.addItems(selected);
	    }
	  } else {
	    main_core.Dom.show(babelHelpers.classPrivateFieldGet(this, _diskControllerNode));
	  }
	}
	function _hideDiskControllerLayout2() {
	  if (babelHelpers.classPrivateFieldGet(this, _diskControllerNode)) {
	    main_core.Dom.hide(babelHelpers.classPrivateFieldGet(this, _diskControllerNode));
	  }
	}
	function _showFileControllerLayout2(selected) {
	  if (!babelHelpers.classPrivateFieldGet(this, _fileControllerNode)) {
	    babelHelpers.classPrivateFieldSet(this, _fileItemsNode, main_core.Dom.create('span'));
	    babelHelpers.classPrivateFieldSet(this, _fileControllerNode, main_core.Dom.create('div', {
	      children: [babelHelpers.classPrivateFieldGet(this, _fileItemsNode)]
	    }));
	    this.targetInput.appendChild(babelHelpers.classPrivateFieldGet(this, _fileControllerNode));
	    var addButtonNode = main_core.Dom.create('a', {
	      attrs: {
	        className: 'bizproc-automation-popup-settings-link bizproc-automation-popup-settings-link-thin'
	      },
	      text: main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_ADD')
	    });
	    babelHelpers.classPrivateFieldGet(this, _fileControllerNode).appendChild(addButtonNode);
	    main_core.Event.bind(addButtonNode, 'click', _classPrivateMethodGet$9(this, _onFileFieldAddClick, _onFileFieldAddClick2).bind(this, addButtonNode));
	    if (selected) {
	      this.addItems(selected);
	    }
	  } else {
	    main_core.Dom.show(babelHelpers.classPrivateFieldGet(this, _fileControllerNode));
	  }
	}
	function _hideFileControllerLayout2() {
	  if (babelHelpers.classPrivateFieldGet(this, _fileControllerNode)) {
	    main_core.Dom.hide(babelHelpers.classPrivateFieldGet(this, _fileControllerNode));
	  }
	}
	function _getDiskUploader2() {
	  if (!babelHelpers.classPrivateFieldGet(this, _diskUploader)) {
	    babelHelpers.classPrivateFieldSet(this, _diskUploader, BX.Bizproc.Automation.DiskUploader.create('', {
	      msg: {
	        'diskAttachFiles': main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_DISK_ATTACH_FILE'),
	        'diskAttachedFiles': main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_DISK_ATTACHED_FILES'),
	        'diskSelectFile': main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_DISK_SELECT_FILE'),
	        'diskSelectFileLegend': main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_DISK_SELECT_FILE_LEGEND'),
	        'diskUploadFile': main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_DISK_UPLOAD_FILE'),
	        'diskUploadFileLegend': main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_DISK_UPLOAD_FILE_LEGEND')
	      }
	    }));
	    babelHelpers.classPrivateFieldGet(this, _diskUploader).setMode(1);
	  }
	  return babelHelpers.classPrivateFieldGet(this, _diskUploader);
	}
	function _onTypeChange2(newType) {
	  if (babelHelpers.classPrivateFieldGet(this, _type$3) !== newType) {
	    babelHelpers.classPrivateFieldSet(this, _type$3, newType);
	    _classPrivateMethodGet$9(this, _showTypeControlLayout, _showTypeControlLayout2).call(this);
	  }
	}
	function _addFileItem2(item) {
	  if (_classPrivateMethodGet$9(this, _isFileItemSelected, _isFileItemSelected2).call(this, item)) {
	    return false;
	  }
	  var node = _classPrivateMethodGet$9(this, _createFileItemNode, _createFileItemNode2).call(this, item);
	  if (!babelHelpers.classPrivateFieldGet(this, _multiple)) {
	    main_core.Dom.clean(babelHelpers.classPrivateFieldGet(this, _fileItemsNode));
	  }
	  babelHelpers.classPrivateFieldGet(this, _fileItemsNode).appendChild(node);
	}
	function _isFileItemSelected2(item) {
	  return !!babelHelpers.classPrivateFieldGet(this, _fileItemsNode).querySelector("[data-file-id=\"".concat(item.id, "\"]"));
	}
	function _convertToDiskItems(items) {
	  return items.map(function (item) {
	    return {
	      ID: item['id'],
	      NAME: item['name'],
	      SIZE: item['size'],
	      VIEW_URL: ''
	    };
	  });
	}
	function _removeFileItem2(item) {
	  var itemNode = babelHelpers.classPrivateFieldGet(this, _fileItemsNode).querySelector("[data-file-id=\"".concat(item.id, "\"]"));
	  if (itemNode) {
	    babelHelpers.classPrivateFieldGet(this, _fileItemsNode).removeChild(itemNode);
	  }
	}
	function _onFileFieldAddClick2(addButtonNode, event) {
	  var self = this;
	  if (!babelHelpers.classPrivateFieldGet(this, _menuId)) {
	    babelHelpers.classPrivateFieldSet(this, _menuId, bizproc_automation.Helper.generateUniqueId());
	  }
	  main_popup.MenuManager.show(babelHelpers.classPrivateFieldGet(this, _menuId), addButtonNode, this.context.get('fileFields').map(function (field) {
	    return {
	      text: main_core.Text.encode(field.Name),
	      field: field,
	      onclick: function onclick(event, item) {
	        this.popupWindow.close();
	        self.onFieldSelect(field);
	      }
	    };
	  }), {
	    autoHide: true,
	    offsetLeft: main_core.Dom.getPosition(addButtonNode)['width'] / 2,
	    angle: {
	      position: 'top',
	      offset: 0
	    }
	  });

	  // this.#menu = MenuManager.currentItem;
	  event.preventDefault();
	}
	function _createFileItemNode2(item) {
	  var itemField = this.context.get('fileFields').find(function (field) {
	    return field.Expression === item.expression;
	  });
	  var label = (itemField === null || itemField === void 0 ? void 0 : itemField.Name) || '';
	  return main_core.Tag.render(_templateObject8 || (_templateObject8 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<span\n\t\t\t\tclass=\"bizproc-automation-popup-autocomplete-item\"\n\t\t\t\tdata-file-id=\"", "\"\n\t\t\t\tdata-file-expression=\"", "\"\n\t\t\t>\n\t\t\t\t<span class=\"bizproc-automation-popup-autocomplete-name\">", "</span>\n\t\t\t\t<span\n\t\t\t\t\tclass=\"bizproc-automation-popup-autocomplete-delete\"\n\t\t\t\t\tonclick=\"", "\"\n\t\t\t\t></span>\n\t\t\t</span>\n\t\t"])), item.id, item.expression, label, _classPrivateMethodGet$9(this, _removeFileItem, _removeFileItem2).bind(this, item));
	}
	babelHelpers.defineProperty(FileSelector, "TYPE", {
	  None: '',
	  Disk: 'disk',
	  File: 'file'
	});

	function _classPrivateMethodInitSpec$a(obj, privateSet) { _checkPrivateRedeclaration$p(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec$p(obj, privateMap, value) { _checkPrivateRedeclaration$p(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$p(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classStaticPrivateMethodGet$1(receiver, classConstructor, method) { _classCheckPrivateStaticAccess$4(receiver, classConstructor); return method; }
	function _classCheckPrivateStaticAccess$4(receiver, classConstructor) { if (receiver !== classConstructor) { throw new TypeError("Private static access of wrong provenance"); } }
	function _classPrivateMethodGet$a(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _clockInstance = /*#__PURE__*/new WeakMap();
	var _onTimeSelect = /*#__PURE__*/new WeakSet();
	var _getCurrentTime = /*#__PURE__*/new WeakSet();
	var _convertTimeToSeconds = /*#__PURE__*/new WeakSet();
	var TimeSelector = /*#__PURE__*/function (_InlineSelector) {
	  babelHelpers.inherits(TimeSelector, _InlineSelector);
	  function TimeSelector() {
	    var _babelHelpers$getProt;
	    var _this;
	    babelHelpers.classCallCheck(this, TimeSelector);
	    for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
	      args[_key] = arguments[_key];
	    }
	    _this = babelHelpers.possibleConstructorReturn(this, (_babelHelpers$getProt = babelHelpers.getPrototypeOf(TimeSelector)).call.apply(_babelHelpers$getProt, [this].concat(args)));
	    _classPrivateMethodInitSpec$a(babelHelpers.assertThisInitialized(_this), _convertTimeToSeconds);
	    _classPrivateMethodInitSpec$a(babelHelpers.assertThisInitialized(_this), _getCurrentTime);
	    _classPrivateMethodInitSpec$a(babelHelpers.assertThisInitialized(_this), _onTimeSelect);
	    _classPrivateFieldInitSpec$p(babelHelpers.assertThisInitialized(_this), _clockInstance, {
	      writable: true,
	      value: void 0
	    });
	    return _this;
	  }
	  babelHelpers.createClass(TimeSelector, [{
	    key: "destroy",
	    value: function destroy() {
	      if (babelHelpers.classPrivateFieldGet(this, _clockInstance)) {
	        babelHelpers.classPrivateFieldGet(this, _clockInstance).closeWnd();
	      }
	    }
	  }, {
	    key: "renderTo",
	    value: function renderTo(targetInput) {
	      var _this$constructor;
	      this.targetInput = targetInput; //this.targetInput = Runtime.clone(targetInput);

	      var datetime = new Date();
	      datetime.setHours(0, 0, 0, 0);
	      datetime.setTime(datetime.getTime() + _classPrivateMethodGet$a(this, _getCurrentTime, _getCurrentTime2).call(this) * 1000);
	      this.targetInput.value = _classStaticPrivateMethodGet$1(_this$constructor = this.constructor, TimeSelector, _formatTime$1).call(_this$constructor, datetime);
	      main_core.Event.bind(targetInput, 'click', this.showClock.bind(this));
	    }
	  }, {
	    key: "showClock",
	    value: function showClock() {
	      if (!babelHelpers.classPrivateFieldGet(this, _clockInstance)) {
	        babelHelpers.classPrivateFieldSet(this, _clockInstance, new BX.CClockSelector({
	          start_time: _classPrivateMethodGet$a(this, _getCurrentTime, _getCurrentTime2).call(this),
	          node: this.targetInput,
	          callback: _classPrivateMethodGet$a(this, _onTimeSelect, _onTimeSelect2).bind(this)
	        }));
	      }
	      babelHelpers.classPrivateFieldGet(this, _clockInstance).Show();
	    }
	  }]);
	  return TimeSelector;
	}(InlineSelector);
	function _onTimeSelect2(time) {
	  this.targetInput.value = time;
	  BX.fireEvent(this.targetInput, 'change');
	  babelHelpers.classPrivateFieldGet(this, _clockInstance).closeWnd();
	}
	function _getCurrentTime2() {
	  return _classPrivateMethodGet$a(this, _convertTimeToSeconds, _convertTimeToSeconds2).call(this, this.targetInput.value);
	}
	function _convertTimeToSeconds2(time) {
	  var timeParts = time.split(/[\s:]+/).map(function (part) {
	    return parseInt(part);
	  });
	  var _timeParts = babelHelpers.slicedToArray(timeParts, 2),
	    hours = _timeParts[0],
	    minutes = _timeParts[1];
	  if (timeParts.length === 3) {
	    var period = timeParts[2];
	    if (period === 'pm' && hours < 12) {
	      hours += 12;
	    } else if (period === 'am' && hours === 12) {
	      hours = 0;
	    }
	  }
	  return hours * 3600 + minutes * 60;
	}
	function _formatTime$1(datetime) {
	  var getFormat = function getFormat(formatId) {
	    return BX.date.convertBitrixFormat(main_core.Loc.getMessage(formatId)).replace(/:?\s*s/, '');
	  };
	  var dateFormat = getFormat('FORMAT_DATE');
	  var timeFormat = getFormat('FORMAT_DATETIME').replace(dateFormat, '').trim();
	  return BX.date.format(timeFormat, datetime);
	}

	var _templateObject$5;
	function _classPrivateMethodInitSpec$b(obj, privateSet) { _checkPrivateRedeclaration$q(obj, privateSet); privateSet.add(obj); }
	function _checkPrivateRedeclaration$q(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet$b(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _createWaitWorkDayNode = /*#__PURE__*/new WeakSet();
	var _isWorkTimeAvailable = /*#__PURE__*/new WeakSet();
	var DelayIntervalSelector$$1 = /*#__PURE__*/function () {
	  function DelayIntervalSelector$$1(options) {
	    babelHelpers.classCallCheck(this, DelayIntervalSelector$$1);
	    _classPrivateMethodInitSpec$b(this, _isWorkTimeAvailable);
	    _classPrivateMethodInitSpec$b(this, _createWaitWorkDayNode);
	    this.basisFields = [];
	    this.onchange = null;
	    if (main_core.Type.isPlainObject(options)) {
	      this.labelNode = options.labelNode;
	      this.useAfterBasis = options.useAfterBasis;
	      if (main_core.Type.isArray(options.basisFields)) {
	        this.basisFields = options.basisFields;
	      }
	      this.onchange = options.onchange;
	      this.minLimitM = options.minLimitM;
	      this.showWaitWorkDay = options.showWaitWorkDay;
	    }
	  }
	  babelHelpers.createClass(DelayIntervalSelector$$1, [{
	    key: "init",
	    value: function init(delay) {
	      this.delay = delay;
	      this.setLabelText();
	      this.bindLabelNode();
	      this.prepareBasisFields();
	    }
	  }, {
	    key: "setLabelText",
	    value: function setLabelText() {
	      if (this.delay && this.labelNode) {
	        this.labelNode.textContent = this.delay.format(main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_AT_ONCE'), this.basisFields);
	      }
	    }
	  }, {
	    key: "bindLabelNode",
	    value: function bindLabelNode() {
	      if (this.labelNode) {
	        main_core.Event.bind(this.labelNode, 'click', BX.delegate(this.onLabelClick, this));
	      }
	    }
	  }, {
	    key: "onLabelClick",
	    value: function onLabelClick(event) {
	      this.showDelayIntervalPopup();
	      event.preventDefault();
	    }
	  }, {
	    key: "showDelayIntervalPopup",
	    value: function showDelayIntervalPopup() {
	      var delay = this.delay;
	      var uid = Helper.generateUniqueId();
	      var form = main_core.Dom.create("form", {
	        attrs: {
	          className: "bizproc-automation-popup-select-block"
	        }
	      });
	      var radioNow = main_core.Dom.create("input", {
	        attrs: {
	          className: "bizproc-automation-popup-select-input",
	          id: uid + "now",
	          type: "radio",
	          value: 'now',
	          name: "type"
	        }
	      });
	      if (delay.isNow()) {
	        radioNow.setAttribute('checked', 'checked');
	      }
	      var labelNow = main_core.Dom.create("label", {
	        attrs: {
	          className: "bizproc-automation-popup-select-wrapper",
	          "for": uid + "now"
	        },
	        children: [main_core.Dom.create('span', {
	          attrs: {
	            className: 'bizproc-automation-popup-settings-title'
	          },
	          text: main_core.Loc.getMessage(this.useAfterBasis ? 'BIZPROC_AUTOMATION_CMP_BASIS_NOW' : 'BIZPROC_AUTOMATION_CMP_AT_ONCE_2')
	        })]
	      });
	      var labelNowHelpNode = main_core.Dom.create('span', {
	        attrs: {
	          className: "bizproc-automation-status-help bizproc-automation-status-help-right",
	          'data-hint': main_core.Loc.getMessage(this.useAfterBasis ? 'BIZPROC_AUTOMATION_CMP_DELAY_NOW_HELP_2' : 'BIZPROC_AUTOMATION_CMP_DELAY_NOW_HELP')
	        }
	      });
	      labelNow.appendChild(labelNowHelpNode);
	      form.appendChild(main_core.Dom.create("div", {
	        attrs: {
	          className: "bizproc-automation-popup-select-item"
	        },
	        children: [radioNow, labelNow]
	      }));
	      form.appendChild(this.createAfterControlNode());
	      if (this.basisFields.length > 0) {
	        form.appendChild(this.createBeforeControlNode());
	        form.appendChild(this.createInControlNode());
	      }
	      var workTimeRadio = main_core.Dom.create("input", {
	        attrs: {
	          type: "checkbox",
	          id: uid + "worktime",
	          name: "worktime",
	          value: '1',
	          style: 'vertical-align: middle'
	        },
	        props: {
	          checked: delay.workTime
	        }
	      });
	      var workTimeHelpNode = main_core.Dom.create('span', {
	        attrs: {
	          className: "bizproc-automation-status-help bizproc-automation-status-help-right",
	          'data-hint': main_core.Loc.getMessage('BIZPROC_AUTOMATION_DELAY_WORK_TIME_HELP')
	        }
	      });
	      form.appendChild(main_core.Dom.create("div", {
	        attrs: {
	          className: "bizproc-automation-popup-settings-title"
	        },
	        children: [workTimeRadio, main_core.Dom.create("label", {
	          attrs: {
	            className: "bizproc-automation-popup-settings-lbl",
	            "for": uid + "worktime"
	          },
	          text: main_core.Loc.getMessage('BIZPROC_AUTOMATION_DELAY_WORK_TIME_MSGVER_1')
	        }), workTimeHelpNode]
	      }));
	      if (this.showWaitWorkDay) {
	        form.appendChild(_classPrivateMethodGet$b(this, _createWaitWorkDayNode, _createWaitWorkDayNode2).call(this));
	      }
	      var self = this;
	      //init modern Help tips
	      BX.UI.Hint.init(form);
	      var popup = new BX.PopupWindow(Helper.generateUniqueId(), this.labelNode, {
	        autoHide: true,
	        closeByEsc: true,
	        closeIcon: false,
	        titleBar: false,
	        angle: true,
	        offsetLeft: 20,
	        content: form,
	        buttons: [new BX.PopupWindowButton({
	          text: main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_CHOOSE'),
	          className: 'webform-button webform-button-create bizproc-automation-button-left',
	          events: {
	            click: function click() {
	              self.saveFormData(new FormData(form));
	              this.popupWindow.close();
	            }
	          }
	        })],
	        events: {
	          onPopupClose: function onPopupClose() {
	            if (self.fieldsMenu) {
	              self.fieldsMenu.popupWindow.close();
	            }
	            if (self.valueTypeMenu) {
	              self.valueTypeMenu.popupWindow.close();
	            }
	            this.destroy();
	          }
	        },
	        overlay: {
	          backgroundColor: 'transparent'
	        }
	      });
	      popup.show();
	    }
	  }, {
	    key: "saveFormData",
	    value: function saveFormData(formData) {
	      var type = formData.get('type');
	      if (type === 'now') {
	        this.delay.setNow();
	      } else if (type === DelayInterval.DELAY_TYPE.In) {
	        this.delay.setType(DelayInterval.DELAY_TYPE.In);
	        this.delay.setValue(0);
	        this.delay.setValueType('i');
	        this.delay.setBasis(formData.get('basis_in'));
	        this.delay.setInTime(formData.get('basis_in_time') ? formData.get('basis_in_time').split(':') : null);
	      } else {
	        this.delay.setType(type);
	        this.delay.setValue(formData.get('value_' + type));
	        this.delay.setValueType(formData.get('value_type_' + type));
	        if (type === DelayInterval.DELAY_TYPE.After) {
	          if (this.useAfterBasis) {
	            this.delay.setBasis(formData.get('basis_after'));
	          } else {
	            this.delay.setBasis(DelayInterval.BASIS_TYPE.CurrentDateTime);
	          }
	          if (this.minLimitM > 0 && this.delay.basis === DelayInterval.BASIS_TYPE.CurrentDateTime && this.delay.valueType === 'i' && this.delay.value < this.minLimitM) {
	            BX.UI.Notification.Center.notify({
	              content: main_core.Loc.getMessage('BIZPROC_AUTOMATION_DELAY_MIN_LIMIT_LABEL')
	            });
	            this.delay.setValue(this.minLimitM);
	          }
	        } else {
	          this.delay.setBasis(formData.get('basis_before'));
	        }
	      }
	      this.delay.setWorkTime(formData.get('worktime'));
	      this.delay.setWaitWorkDay(formData.get('wait_workday'));
	      this.setLabelText();
	      if (this.onchange) {
	        this.onchange(this.delay);
	      }
	    }
	  }, {
	    key: "createAfterControlNode",
	    value: function createAfterControlNode() {
	      var delay = this.delay;
	      var uid = Helper.generateUniqueId();
	      var radioAfter = main_core.Dom.create("input", {
	        attrs: {
	          className: "bizproc-automation-popup-select-input",
	          id: uid,
	          type: "radio",
	          value: DelayInterval.DELAY_TYPE.After,
	          name: "type"
	        }
	      });
	      if (delay.type === DelayInterval.DELAY_TYPE.After && delay.value > 0) {
	        radioAfter.setAttribute('checked', 'checked');
	      }
	      var valueNode = main_core.Dom.create('input', {
	        attrs: {
	          type: 'text',
	          name: 'value_after',
	          className: 'bizproc-automation-popup-settings-input'
	        },
	        props: {
	          value: delay.type === DelayInterval.DELAY_TYPE.After && delay.value ? delay.value : this.minLimitM || 5
	        }
	      });
	      var labelAfter = main_core.Dom.create("label", {
	        attrs: {
	          className: "bizproc-automation-popup-select-wrapper",
	          "for": uid
	        },
	        children: [main_core.Dom.create('span', {
	          attrs: {
	            className: 'bizproc-automation-popup-settings-title'
	          },
	          text: main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_THROUGH_3')
	        }), valueNode, this.createValueTypeSelector('value_type_after')]
	      });
	      if (this.useAfterBasis) {
	        labelAfter.appendChild(main_core.Dom.create('span', {
	          attrs: {
	            className: 'bizproc-automation-popup-settings-title bizproc-automation-popup-settings-title-auto-width'
	          },
	          text: main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_AFTER')
	        }));
	        var basisField = this.getBasisField(delay.basis, true);
	        var basisValue = delay.basis;
	        if (!basisField) {
	          basisField = this.getBasisField(DelayInterval.BASIS_TYPE.CurrentDateTime, true);
	          basisValue = basisField.SystemExpression;
	        }
	        var beforeBasisValueNode = main_core.Dom.create('input', {
	          attrs: {
	            type: "hidden",
	            name: "basis_after",
	            value: basisValue
	          }
	        });
	        var self = this;
	        var beforeBasisNode = main_core.Dom.create('span', {
	          attrs: {
	            className: "bizproc-automation-popup-settings-link bizproc-automation-delay-interval-basis"
	          },
	          text: basisField ? basisField.Name : main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_CHOOSE_DATE_FIELD'),
	          events: {
	            click: function click(event) {
	              self.onBasisClick(event, this, function (field) {
	                beforeBasisNode.textContent = field.Name;
	                beforeBasisValueNode.value = field.SystemExpression;
	              }, DelayInterval.DELAY_TYPE.After);
	            }
	          }
	        });
	        labelAfter.appendChild(beforeBasisValueNode);
	        labelAfter.appendChild(beforeBasisNode);
	      }
	      if (!this.useAfterBasis) {
	        var afterHelpNode = main_core.Dom.create('span', {
	          attrs: {
	            className: "bizproc-automation-status-help bizproc-automation-status-help-right",
	            'data-hint': main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_DELAY_AFTER_HELP')
	          }
	        });
	        labelAfter.appendChild(afterHelpNode);
	      }
	      return main_core.Dom.create("div", {
	        attrs: {
	          className: "bizproc-automation-popup-select-item"
	        },
	        children: [radioAfter, labelAfter]
	      });
	    }
	  }, {
	    key: "createBeforeControlNode",
	    value: function createBeforeControlNode() {
	      var delay = this.delay;
	      var uid = Helper.generateUniqueId();
	      var radioBefore = main_core.Dom.create("input", {
	        attrs: {
	          className: "bizproc-automation-popup-select-input",
	          id: uid,
	          type: "radio",
	          value: DelayInterval.DELAY_TYPE.Before,
	          name: "type"
	        }
	      });
	      if (delay.type === DelayInterval.DELAY_TYPE.Before) {
	        radioBefore.setAttribute('checked', 'checked');
	      }
	      var valueNode = main_core.Dom.create('input', {
	        attrs: {
	          type: 'text',
	          name: 'value_before',
	          className: 'bizproc-automation-popup-settings-input'
	        },
	        props: {
	          value: delay.type === DelayInterval.DELAY_TYPE.Before && delay.value ? delay.value : this.minLimitM || 5
	        }
	      });
	      var labelBefore = main_core.Dom.create("label", {
	        attrs: {
	          className: "bizproc-automation-popup-select-wrapper",
	          "for": uid
	        },
	        children: [main_core.Dom.create('span', {
	          attrs: {
	            className: 'bizproc-automation-popup-settings-title'
	          },
	          text: main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_FOR_TIME_3')
	        }), valueNode, this.createValueTypeSelector('value_type_before'), main_core.Dom.create('span', {
	          attrs: {
	            className: 'bizproc-automation-popup-settings-title bizproc-automation-popup-settings-title-auto-width'
	          },
	          text: main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_BEFORE_1')
	        })]
	      });
	      var basisField = this.getBasisField(delay.basis);
	      var basisValue = delay.basis;
	      if (!basisField) {
	        basisField = this.basisFields[0];
	        basisValue = basisField.SystemExpression;
	      }
	      var beforeBasisValueNode = main_core.Dom.create('input', {
	        attrs: {
	          type: "hidden",
	          name: "basis_before",
	          value: basisValue
	        }
	      });
	      var self = this;
	      var beforeBasisNode = main_core.Dom.create('span', {
	        attrs: {
	          className: "bizproc-automation-popup-settings-link bizproc-automation-delay-interval-basis"
	        },
	        text: basisField ? basisField.Name : main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_CHOOSE_DATE_FIELD'),
	        events: {
	          click: function click(event) {
	            self.onBasisClick(event, this, function (field) {
	              beforeBasisNode.textContent = field.Name;
	              beforeBasisValueNode.value = field.SystemExpression;
	            }, DelayInterval.DELAY_TYPE.Before);
	          }
	        }
	      });
	      labelBefore.appendChild(beforeBasisValueNode);
	      labelBefore.appendChild(beforeBasisNode);
	      if (!this.useAfterBasis) {
	        var beforeHelpNode = main_core.Dom.create('span', {
	          attrs: {
	            className: "bizproc-automation-status-help bizproc-automation-status-help-right",
	            'data-hint': main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_DELAY_BEFORE_HELP')
	          }
	        });
	        labelBefore.appendChild(beforeHelpNode);
	      }
	      return main_core.Dom.create("div", {
	        attrs: {
	          className: "bizproc-automation-popup-select-item"
	        },
	        children: [radioBefore, labelBefore]
	      });
	    }
	  }, {
	    key: "createInControlNode",
	    value: function createInControlNode() {
	      var delay = this.delay;
	      var uid = Helper.generateUniqueId();
	      var radioIn = main_core.Dom.create("input", {
	        attrs: {
	          className: "bizproc-automation-popup-select-input",
	          id: uid,
	          type: "radio",
	          value: DelayInterval.DELAY_TYPE.In,
	          name: "type"
	        }
	      });
	      if (delay.type === DelayInterval.DELAY_TYPE.In) {
	        radioIn.setAttribute('checked', 'checked');
	      }
	      var labelIn = main_core.Dom.create("label", {
	        attrs: {
	          className: "bizproc-automation-popup-select-wrapper",
	          "for": uid
	        },
	        children: [main_core.Dom.create('span', {
	          attrs: {
	            className: 'bizproc-automation-popup-settings-title'
	          },
	          text: main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_IN_TIME_2')
	        })]
	      });
	      var basisField = this.getBasisField(delay.basis, true);
	      var basisValue = delay.basis;
	      if (!basisField) {
	        basisField = this.basisFields[0];
	        basisValue = basisField.SystemExpression;
	      }
	      var inBasisValueNode = main_core.Dom.create('input', {
	        attrs: {
	          type: "hidden",
	          name: "basis_in",
	          value: basisValue
	        }
	      });
	      var self = this;
	      var inBasisNode = main_core.Dom.create('span', {
	        attrs: {
	          className: "bizproc-automation-popup-settings-link bizproc-automation-delay-interval-basis"
	        },
	        text: basisField ? basisField.Name : main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_CHOOSE_DATE_FIELD'),
	        events: {
	          click: function click(event) {
	            self.onBasisClick(event, this, function (field) {
	              inBasisNode.textContent = field.Name;
	              inBasisValueNode.value = field.SystemExpression;
	            }, DelayInterval.DELAY_TYPE.In);
	          }
	        }
	      });
	      labelIn.appendChild(inBasisValueNode);
	      labelIn.appendChild(inBasisNode);
	      if (!this.useAfterBasis) {
	        var helpNode = main_core.Dom.create('span', {
	          attrs: {
	            className: "bizproc-automation-status-help bizproc-automation-status-help-right",
	            'data-hint': main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_DELAY_IN_HELP')
	          }
	        });
	        labelIn.appendChild(helpNode);
	      }
	      var inTime = main_core.Tag.render(_templateObject$5 || (_templateObject$5 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t <span>\n\t\t\t \tTime: <input type=\"time\" value=\"", "\" name=\"basis_in_time\"/>\n\t\t\t</span>\n\t\t"])), delay.inTimeString);

	      // Dom.append(inTime, labelIn); // TODO interface

	      return main_core.Dom.create("div", {
	        attrs: {
	          className: "bizproc-automation-popup-select-item"
	        },
	        children: [radioIn, labelIn]
	      });
	    }
	  }, {
	    key: "createValueTypeSelector",
	    value: function createValueTypeSelector(name) {
	      var delay = this.delay;
	      var labelTexts = {
	        i: main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_INTERVAL_M'),
	        h: main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_INTERVAL_H'),
	        d: main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_INTERVAL_D')
	      };
	      var label = main_core.Dom.create('label', {
	        attrs: {
	          className: 'bizproc-automation-popup-settings-link'
	        },
	        text: labelTexts[delay.valueType]
	      });
	      var input = main_core.Dom.create('input', {
	        attrs: {
	          type: 'hidden',
	          name: name
	        },
	        props: {
	          value: delay.valueType
	        }
	      });
	      main_core.Event.bind(label, 'click', this.onValueTypeSelectorClick.bind(this, label, input));
	      return main_core.Dom.create('span', {
	        children: [label, input]
	      });
	    }
	  }, {
	    key: "onValueTypeSelectorClick",
	    value: function onValueTypeSelectorClick(label, input) {
	      var uid = Helper.generateUniqueId();
	      var handler = function handler(event, item) {
	        this.popupWindow.close();
	        input.value = item.valueId;
	        label.textContent = item.text;
	      };
	      var menuItems = [{
	        text: main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_INTERVAL_M'),
	        valueId: 'i',
	        onclick: handler
	      }, {
	        text: main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_INTERVAL_H'),
	        valueId: 'h',
	        onclick: handler
	      }, {
	        text: main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_INTERVAL_D'),
	        valueId: 'd',
	        onclick: handler
	      }];
	      main_popup.MenuManager.show(uid, label, menuItems, {
	        autoHide: true,
	        offsetLeft: 25,
	        angle: {
	          position: 'top'
	        },
	        events: {
	          onPopupClose: function onPopupClose() {
	            this.destroy();
	          }
	        },
	        overlay: {
	          backgroundColor: 'transparent'
	        }
	      });
	      this.valueTypeMenu = main_popup.MenuManager.currentItem;
	    }
	  }, {
	    key: "onBasisClick",
	    value: function onBasisClick(event, labelNode, callback, delayType) {
	      var menuItems = [];
	      if (delayType === DelayInterval.DELAY_TYPE.After || delayType === DelayInterval.DELAY_TYPE.In) {
	        menuItems.push({
	          text: main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_BASIS_NOW'),
	          field: {
	            Name: main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_BASIS_NOW'),
	            SystemExpression: DelayInterval.BASIS_TYPE.CurrentDateTime
	          },
	          onclick: function onclick(event, item) {
	            if (callback) {
	              callback(item.field);
	            }
	            this.popupWindow.close();
	          }
	        }, {
	          text: main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_BASIS_DATE'),
	          field: {
	            Name: main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_BASIS_DATE'),
	            SystemExpression: DelayInterval.BASIS_TYPE.CurrentDate
	          },
	          onclick: function onclick(event, item) {
	            if (callback) {
	              callback(item.field);
	            }
	            this.popupWindow.close();
	          }
	        }, {
	          delimiter: true
	        });
	      }
	      for (var i = 0; i < this.basisFields.length; ++i) {
	        if (delayType !== DelayInterval.DELAY_TYPE.After && this.basisFields[i]['Id'].indexOf('DATE_CREATE') > -1) {
	          continue;
	        }
	        menuItems.push({
	          text: main_core.Text.encode(this.basisFields[i].Name),
	          field: this.basisFields[i],
	          onclick: function onclick(e, item) {
	            if (callback) {
	              callback(item.field || item.options.field);
	            }
	            this.popupWindow.close();
	          }
	        });
	      }
	      var menuId = labelNode.getAttribute('data-menu-id');
	      if (!menuId) {
	        menuId = Helper.generateUniqueId();
	        labelNode.setAttribute('data-menu-id', menuId);
	      }
	      main_popup.MenuManager.show(menuId, labelNode, menuItems, {
	        autoHide: true,
	        offsetLeft: BX.pos(labelNode)['width'] / 2,
	        angle: {
	          position: 'top',
	          offset: 0
	        },
	        overlay: {
	          backgroundColor: 'transparent'
	        }
	      });
	      this.fieldsMenu = main_popup.MenuManager.currentItem;
	    }
	  }, {
	    key: "getBasisField",
	    value: function getBasisField(basis, system) {
	      if (system && (basis === DelayInterval.BASIS_TYPE.CurrentDateTime || basis === DelayInterval.BASIS_TYPE.CurrentDateTimeLocal)) {
	        return {
	          Name: main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_BASIS_NOW'),
	          SystemExpression: DelayInterval.BASIS_TYPE.CurrentDateTime
	        };
	      }
	      if (system && basis === DelayInterval.BASIS_TYPE.CurrentDate) {
	        return {
	          Name: main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_BASIS_DATE'),
	          SystemExpression: DelayInterval.BASIS_TYPE.CurrentDate
	        };
	      }
	      var field = null;
	      for (var i = 0; i < this.basisFields.length; ++i) {
	        if (basis === this.basisFields[i].SystemExpression) {
	          field = this.basisFields[i];
	        }
	      }
	      return field;
	    }
	  }, {
	    key: "prepareBasisFields",
	    value: function prepareBasisFields() {
	      var fields = [];
	      for (var i = 0; i < this.basisFields.length; ++i) {
	        var fld = this.basisFields[i];
	        if (fld['Id'].indexOf('DATE_MODIFY') < 0 && fld['Id'].indexOf('EVENT_DATE') < 0 && fld['Id'].indexOf('BIRTHDATE') < 0) {
	          fields.push(fld);
	        }
	      }
	      this.basisFields = fields;
	    }
	  }]);
	  return DelayIntervalSelector$$1;
	}();
	function _createWaitWorkDayNode2() {
	  var delay = this.delay;
	  var uid = Helper.generateUniqueId();
	  var isAvailable = _classPrivateMethodGet$b(this, _isWorkTimeAvailable, _isWorkTimeAvailable2).call(this);
	  var workDayRadio = main_core.Dom.create("input", {
	    attrs: {
	      type: "checkbox",
	      id: uid + "wait_workday",
	      name: "wait_workday",
	      value: '1',
	      style: 'vertical-align: middle'
	    },
	    props: {
	      checked: delay.waitWorkDay && isAvailable
	    }
	  });
	  if (!isAvailable) {
	    workDayRadio.disabled = true;
	  }
	  var workDayHelpNode = main_core.Dom.create('span', {
	    attrs: {
	      className: "bizproc-automation-status-help bizproc-automation-status-help-right",
	      'data-hint': main_core.Loc.getMessage('BIZPROC_AUTOMATION_DELAY_WAIT_WORK_DAY_HELP')
	    }
	  });
	  var events = {};
	  if (!isAvailable) {
	    events.click = function () {
	      if (top.BX.UI && top.BX.UI.InfoHelper) {
	        top.BX.UI.InfoHelper.show('limit_office_worktime_responsible');
	      }
	    };
	  }
	  return main_core.Dom.create("div", {
	    attrs: {
	      className: "bizproc-automation-popup-select-item"
	    },
	    children: [main_core.Dom.create("div", {
	      attrs: {
	        className: "bizproc-automation-popup-settings-title"
	      },
	      children: [workDayRadio, main_core.Dom.create("label", {
	        attrs: {
	          className: "bizproc-automation-popup-settings-lbl ".concat(!isAvailable ? 'bizproc-automation-robot-btn-set-locked' : ''),
	          "for": uid + "wait_workday"
	        },
	        text: main_core.Loc.getMessage('BIZPROC_AUTOMATION_DELAY_WAIT_WORK_DAY_MSGVER_1')
	      }), workDayHelpNode]
	    })],
	    events: events
	  });
	}
	function _isWorkTimeAvailable2() {
	  var _getGlobalContext$get;
	  return (_getGlobalContext$get = getGlobalContext().get('IS_WORKTIME_AVAILABLE')) !== null && _getGlobalContext$get !== void 0 ? _getGlobalContext$get : false;
	}

	var SelectorContext = /*#__PURE__*/function (_BaseContext) {
	  babelHelpers.inherits(SelectorContext, _BaseContext);
	  function SelectorContext(props) {
	    babelHelpers.classCallCheck(this, SelectorContext);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(SelectorContext).call(this, props));
	  }
	  babelHelpers.createClass(SelectorContext, [{
	    key: "fields",
	    get: function get() {
	      var fields = this.get('fields');
	      return main_core.Type.isArray(fields) ? fields : [];
	    }
	  }, {
	    key: "useSwitcherMenu",
	    get: function get() {
	      return main_core.Type.isBoolean(this.get('useSwitcherMenu')) ? this.get('useSwitcherMenu') : false;
	    },
	    set: function set(value) {
	      this.set('useSwitcherMenu', value);
	    }
	  }, {
	    key: "rootGroupTitle",
	    get: function get() {
	      var _this$get;
	      return (_this$get = this.get('rootGroupTitle')) !== null && _this$get !== void 0 ? _this$get : '';
	    }
	  }]);
	  return SelectorContext;
	}(BaseContext);

	function _classPrivateMethodInitSpec$c(obj, privateSet) { _checkPrivateRedeclaration$r(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec$q(obj, privateMap, value) { _checkPrivateRedeclaration$r(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$r(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet$c(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _globalVariables = /*#__PURE__*/new WeakMap();
	var _globalConstants = /*#__PURE__*/new WeakMap();
	var _isCorrectMode = /*#__PURE__*/new WeakSet();
	var _getAutomationGlobalsProperty = /*#__PURE__*/new WeakSet();
	var _getExpression = /*#__PURE__*/new WeakSet();
	var _getSystemExpression = /*#__PURE__*/new WeakSet();
	var _getObjectId = /*#__PURE__*/new WeakSet();
	var _getGlobals = /*#__PURE__*/new WeakSet();
	var _setGlobals = /*#__PURE__*/new WeakSet();
	var AutomationGlobals = /*#__PURE__*/function () {
	  function AutomationGlobals(parameters) {
	    var _this = this;
	    babelHelpers.classCallCheck(this, AutomationGlobals);
	    _classPrivateMethodInitSpec$c(this, _setGlobals);
	    _classPrivateMethodInitSpec$c(this, _getGlobals);
	    _classPrivateMethodInitSpec$c(this, _getObjectId);
	    _classPrivateMethodInitSpec$c(this, _getSystemExpression);
	    _classPrivateMethodInitSpec$c(this, _getExpression);
	    _classPrivateMethodInitSpec$c(this, _getAutomationGlobalsProperty);
	    _classPrivateMethodInitSpec$c(this, _isCorrectMode);
	    _classPrivateFieldInitSpec$q(this, _globalVariables, {
	      writable: true,
	      value: []
	    });
	    _classPrivateFieldInitSpec$q(this, _globalConstants, {
	      writable: true,
	      value: []
	    });
	    if (main_core.Type.isArrayFilled(parameters.variables)) {
	      var variables = [];
	      parameters.variables.forEach(function (property) {
	        variables.push(_classPrivateMethodGet$c(_this, _getAutomationGlobalsProperty, _getAutomationGlobalsProperty2).call(_this, property.Id, property, bizproc_globals.Globals.Manager.Instance.mode.variable));
	      });
	      babelHelpers.classPrivateFieldSet(this, _globalVariables, variables);
	    }
	    if (main_core.Type.isArrayFilled(parameters.constants)) {
	      var constants = [];
	      parameters.constants.forEach(function (property) {
	        constants.push(_classPrivateMethodGet$c(_this, _getAutomationGlobalsProperty, _getAutomationGlobalsProperty2).call(_this, property.Id, property, bizproc_globals.Globals.Manager.Instance.mode.constant));
	      });
	      babelHelpers.classPrivateFieldSet(this, _globalConstants, constants);
	    }
	  }
	  babelHelpers.createClass(AutomationGlobals, [{
	    key: "updateGlobals",
	    value: function updateGlobals(mode, updatedGlobals) {
	      var _this2 = this;
	      if (!_classPrivateMethodGet$c(this, _isCorrectMode, _isCorrectMode2).call(this, mode) || Object.keys(updatedGlobals).length < 1) {
	        return;
	      }
	      var globals = _classPrivateMethodGet$c(this, _getGlobals, _getGlobals2).call(this, mode);
	      var newGlobals = [];
	      var _loop = function _loop(id) {
	        var property = updatedGlobals[id];
	        var index = globals.findIndex(function (prop) {
	          return prop.Id === id;
	        });
	        if (index > -1) {
	          if (globals[index].Name !== property.Name) {
	            globals[index].Name = property.Name;
	            globals[index].Expression = _classPrivateMethodGet$c(_this2, _getExpression, _getExpression2).call(_this2, property.Name, property.VisibilityName);
	          }
	          return "continue";
	        }
	        newGlobals.push(_classPrivateMethodGet$c(_this2, _getAutomationGlobalsProperty, _getAutomationGlobalsProperty2).call(_this2, id, property, mode));
	      };
	      for (var id in updatedGlobals) {
	        var _ret = _loop(id);
	        if (_ret === "continue") continue;
	      }
	      if (main_core.Type.isArrayFilled(newGlobals)) {
	        globals = globals.concat(newGlobals);
	      }
	      _classPrivateMethodGet$c(this, _setGlobals, _setGlobals2).call(this, mode, globals);
	    }
	  }, {
	    key: "deleteGlobals",
	    value: function deleteGlobals(mode, deletedGlobals) {
	      if (!_classPrivateMethodGet$c(this, _isCorrectMode, _isCorrectMode2).call(this, mode) || !main_core.Type.isArrayFilled(deletedGlobals)) {
	        return;
	      }
	      var globals = _classPrivateMethodGet$c(this, _getGlobals, _getGlobals2).call(this, mode);
	      deletedGlobals.forEach(function (id) {
	        var index = globals.findIndex(function (prop) {
	          return prop.Id === id;
	        });
	        if (index > -1) {
	          globals.splice(index, 1);
	        }
	      });
	      _classPrivateMethodGet$c(this, _setGlobals, _setGlobals2).call(this, mode, globals);
	    }
	  }, {
	    key: "globalVariables",
	    get: function get() {
	      return babelHelpers.classPrivateFieldGet(this, _globalVariables);
	    },
	    set: function set(variables) {
	      if (!main_core.Type.isArray(variables)) {
	        return;
	      }
	      babelHelpers.classPrivateFieldSet(this, _globalVariables, variables);
	    }
	  }, {
	    key: "globalConstants",
	    get: function get() {
	      return babelHelpers.classPrivateFieldGet(this, _globalConstants);
	    },
	    set: function set(constants) {
	      if (!main_core.Type.isArray(constants)) {
	        return;
	      }
	      babelHelpers.classPrivateFieldSet(this, _globalConstants, constants);
	    }
	  }]);
	  return AutomationGlobals;
	}();
	function _isCorrectMode2(mode) {
	  return main_core.Type.isStringFilled(mode) && Object.values(bizproc_globals.Globals.Manager.Instance.mode).includes(mode);
	}
	function _getAutomationGlobalsProperty2(id, property, mode) {
	  return {
	    ObjectId: _classPrivateMethodGet$c(this, _getObjectId, _getObjectId2).call(this, mode),
	    SuperTitle: String(property.VisibilityName),
	    Id: String(id),
	    Name: String(property.Name),
	    Type: String(property.Type),
	    BaseType: String(property.BaseType || property.Type),
	    Expression: main_core.Type.isStringFilled(property.Expression) ? property.Expression : _classPrivateMethodGet$c(this, _getExpression, _getExpression2).call(this, property.Name, property.VisibilityName),
	    SystemExpression: main_core.Type.isStringFilled(property.SystemExpression) ? property.SystemExpression : _classPrivateMethodGet$c(this, _getSystemExpression, _getSystemExpression2).call(this, mode, id),
	    Options: property.Options,
	    Multiple: main_core.Type.isBoolean(property.Multiple) ? property.Multiple : property.Multiple === 'Y',
	    Visibility: String(property.Visibility)
	  };
	}
	function _getExpression2(name, visibilityName) {
	  return '{{' + String(visibilityName) + ': ' + String(name) + '}}';
	}
	function _getSystemExpression2(mode, id) {
	  return '{=' + _classPrivateMethodGet$c(this, _getObjectId, _getObjectId2).call(this, mode) + ':' + String(id) + '}';
	}
	function _getObjectId2(mode) {
	  return mode === bizproc_globals.Globals.Manager.Instance.mode.variable ? 'GlobalVar' : 'GlobalConst';
	}
	function _getGlobals2(mode) {
	  if (mode === bizproc_globals.Globals.Manager.Instance.mode.variable) {
	    return this.globalVariables;
	  }
	  if (mode === bizproc_globals.Globals.Manager.Instance.mode.constant) {
	    return this.globalConstants;
	  }
	}
	function _setGlobals2(mode, globals) {
	  if (mode === bizproc_globals.Globals.Manager.Instance.mode.variable) {
	    babelHelpers.classPrivateFieldSet(this, _globalVariables, globals);
	  }
	  if (mode === bizproc_globals.Globals.Manager.Instance.mode.constant) {
	    babelHelpers.classPrivateFieldSet(this, _globalConstants, globals);
	  }
	}

	function _classPrivateMethodInitSpec$d(obj, privateSet) { _checkPrivateRedeclaration$s(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec$r(obj, privateMap, value) { _checkPrivateRedeclaration$s(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$s(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet$d(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _nodes = /*#__PURE__*/new WeakMap();
	var _lastColorStatusIndex = /*#__PURE__*/new WeakMap();
	var _defaultStatusColor = /*#__PURE__*/new WeakMap();
	var _fixBackgroundColors = /*#__PURE__*/new WeakSet();
	var _fixTitleColors = /*#__PURE__*/new WeakSet();
	var _isColorStatus = /*#__PURE__*/new WeakSet();
	var Statuses = /*#__PURE__*/function () {
	  function Statuses(stagesContainerNode) {
	    babelHelpers.classCallCheck(this, Statuses);
	    _classPrivateMethodInitSpec$d(this, _isColorStatus);
	    _classPrivateMethodInitSpec$d(this, _fixTitleColors);
	    _classPrivateMethodInitSpec$d(this, _fixBackgroundColors);
	    _classPrivateFieldInitSpec$r(this, _nodes, {
	      writable: true,
	      value: []
	    });
	    _classPrivateFieldInitSpec$r(this, _lastColorStatusIndex, {
	      writable: true,
	      value: -1
	    });
	    _classPrivateFieldInitSpec$r(this, _defaultStatusColor, {
	      writable: true,
	      value: '#d4d6da'
	    });
	    var stagesContainer = stagesContainerNode.querySelector('.bizproc-automation-status-list');
	    if (stagesContainer) {
	      babelHelpers.classPrivateFieldSet(this, _nodes, stagesContainer.querySelectorAll('[data-role="automation-status-title"]'));
	    }
	  }
	  babelHelpers.createClass(Statuses, [{
	    key: "init",
	    value: function init(templates) {
	      var context = bizproc_automation.getGlobalContext();
	      if (context.document.getId() <= 0) {
	        babelHelpers.classPrivateFieldSet(this, _lastColorStatusIndex, babelHelpers.classPrivateFieldGet(this, _nodes).length - 1);
	      } else {
	        babelHelpers.classPrivateFieldSet(this, _lastColorStatusIndex, templates.findIndex(function (template) {
	          return template.getStatusId() === context.document.getCurrentStatusId();
	        }));
	      }
	    }
	  }, {
	    key: "fixColors",
	    value: function fixColors() {
	      _classPrivateMethodGet$d(this, _fixBackgroundColors, _fixBackgroundColors2).call(this);
	      _classPrivateMethodGet$d(this, _fixTitleColors, _fixTitleColors2).call(this);
	    }
	  }]);
	  return Statuses;
	}();
	function _fixBackgroundColors2() {
	  var _this = this;
	  babelHelpers.classPrivateFieldGet(this, _nodes).forEach(function (statusNode, index) {
	    var backgroundNode = statusNode.querySelector('.bizproc-automation__status--bg');
	    if (backgroundNode) {
	      var color = _classPrivateMethodGet$d(_this, _isColorStatus, _isColorStatus2).call(_this, index) && statusNode.dataset.bgcolor ? statusNode.dataset.bgcolor : babelHelpers.classPrivateFieldGet(_this, _defaultStatusColor);
	      main_core.Dom.style(backgroundNode, {
	        backgroundColor: color,
	        borderColor: color
	      });
	    }
	  });
	}
	function _fixTitleColors2() {
	  var _this2 = this;
	  babelHelpers.classPrivateFieldGet(this, _nodes).forEach(function (statusNode, index) {
	    if (!_classPrivateMethodGet$d(_this2, _isColorStatus, _isColorStatus2).call(_this2, index)) {
	      return;
	    }
	    var backgroundColor = statusNode.dataset.bgcolor;
	    if (backgroundColor) {
	      var bigint = parseInt(backgroundColor, 16);
	      var red = bigint >> 16 & 255;
	      var green = bigint >> 8 & 255;
	      var blue = bigint & 255;
	      var isDarkColor = 0.21 * red + 0.72 * green + 0.07 * blue < 145;
	      if (isDarkColor) {
	        main_core.Dom.style(statusNode, 'color', 'white');
	      }
	    }
	  });
	}
	function _isColorStatus2(index) {
	  return index <= babelHelpers.classPrivateFieldGet(this, _lastColorStatusIndex);
	}

	function _classPrivateFieldInitSpec$s(obj, privateMap, value) { _checkPrivateRedeclaration$t(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$t(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	var _guide = /*#__PURE__*/new WeakMap();
	var BeginningGuide = /*#__PURE__*/function () {
	  function BeginningGuide(options) {
	    babelHelpers.classCallCheck(this, BeginningGuide);
	    _classPrivateFieldInitSpec$s(this, _guide, {
	      writable: true,
	      value: void 0
	    });
	    if (!main_core.Type.isElementNode(options.target)) {
	      throw 'options.target must be Node Element';
	    }
	    var text = main_core.Type.isStringFilled(options.text) ? options.text : main_core.Loc.getMessage('BIZPROC_AUTOMATION_TOUR_GUIDE_BEGINNING_SUBTITLE_1');
	    var article = main_core.Type.isStringFilled(options.article) ? main_core.Text.toInteger(options.article) : '';
	    babelHelpers.classPrivateFieldSet(this, _guide, new ui_tour.Guide({
	      steps: [{
	        target: options.target,
	        title: main_core.Loc.getMessage('BIZPROC_AUTOMATION_TOUR_GUIDE_BEGINNING_TITLE'),
	        text: text,
	        article: article,
	        condition: {
	          top: true,
	          bottom: false,
	          color: 'primary'
	        },
	        position: 'bottom'
	      }],
	      onEvents: true
	    }));
	    babelHelpers.classPrivateFieldGet(this, _guide).getPopup().setAutoHide(true);
	  }
	  babelHelpers.createClass(BeginningGuide, [{
	    key: "start",
	    value: function start() {
	      babelHelpers.classPrivateFieldGet(this, _guide).showNextStep();
	    }
	  }]);
	  return BeginningGuide;
	}();

	function _createForOfIteratorHelper$6(o, allowArrayLike) { var it = typeof Symbol !== "undefined" && o[Symbol.iterator] || o["@@iterator"]; if (!it) { if (Array.isArray(o) || (it = _unsupportedIterableToArray$6(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = it.call(o); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it["return"] != null) it["return"](); } finally { if (didErr) throw err; } } }; }
	function _unsupportedIterableToArray$6(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray$6(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray$6(o, minLen); }
	function _arrayLikeToArray$6(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i]; return arr2; }
	function _classPrivateMethodInitSpec$e(obj, privateSet) { _checkPrivateRedeclaration$u(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec$t(obj, privateMap, value) { _checkPrivateRedeclaration$u(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$u(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classStaticPrivateMethodGet$2(receiver, classConstructor, method) { _classCheckPrivateStaticAccess$5(receiver, classConstructor); return method; }
	function _classCheckPrivateStaticAccess$5(receiver, classConstructor) { if (receiver !== classConstructor) { throw new TypeError("Private static access of wrong provenance"); } }
	function _classPrivateMethodGet$e(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _isShownRobotGuide = /*#__PURE__*/new WeakMap();
	var _isShownTriggerGuide = /*#__PURE__*/new WeakMap();
	var _isShownSupportingRobotGuide = /*#__PURE__*/new WeakMap();
	var _showRobotGuide = /*#__PURE__*/new WeakMap();
	var _showTriggerGuide = /*#__PURE__*/new WeakMap();
	var _showSupportingRobotGuide = /*#__PURE__*/new WeakMap();
	var _guideTargets = /*#__PURE__*/new WeakMap();
	var _resolveShowGuides = /*#__PURE__*/new WeakSet();
	var _getGuide = /*#__PURE__*/new WeakSet();
	var _getRobotGuide = /*#__PURE__*/new WeakSet();
	var _getTriggerGuide = /*#__PURE__*/new WeakSet();
	var _getSupportingRobotGuide = /*#__PURE__*/new WeakSet();
	var AutomationGuide = /*#__PURE__*/function () {
	  function AutomationGuide(options) {
	    babelHelpers.classCallCheck(this, AutomationGuide);
	    _classPrivateMethodInitSpec$e(this, _getSupportingRobotGuide);
	    _classPrivateMethodInitSpec$e(this, _getTriggerGuide);
	    _classPrivateMethodInitSpec$e(this, _getRobotGuide);
	    _classPrivateMethodInitSpec$e(this, _getGuide);
	    _classPrivateMethodInitSpec$e(this, _resolveShowGuides);
	    _classPrivateFieldInitSpec$t(this, _isShownRobotGuide, {
	      writable: true,
	      value: true
	    });
	    _classPrivateFieldInitSpec$t(this, _isShownTriggerGuide, {
	      writable: true,
	      value: true
	    });
	    _classPrivateFieldInitSpec$t(this, _isShownSupportingRobotGuide, {
	      writable: true,
	      value: false
	    });
	    _classPrivateFieldInitSpec$t(this, _showRobotGuide, {
	      writable: true,
	      value: false
	    });
	    _classPrivateFieldInitSpec$t(this, _showTriggerGuide, {
	      writable: true,
	      value: false
	    });
	    _classPrivateFieldInitSpec$t(this, _showSupportingRobotGuide, {
	      writable: true,
	      value: false
	    });
	    _classPrivateFieldInitSpec$t(this, _guideTargets, {
	      writable: true,
	      value: {}
	    });
	    if (main_core.Type.isBoolean(options.isShownRobotGuide)) {
	      babelHelpers.classPrivateFieldSet(this, _isShownRobotGuide, options.isShownRobotGuide);
	    }
	    if (main_core.Type.isBoolean(options.isShownTriggerGuide)) {
	      babelHelpers.classPrivateFieldSet(this, _isShownTriggerGuide, options.isShownTriggerGuide);
	    }
	  }
	  babelHelpers.createClass(AutomationGuide, [{
	    key: "setShowRobotGuide",
	    value: function setShowRobotGuide(show, target) {
	      babelHelpers.classPrivateFieldSet(this, _showRobotGuide, show);
	      if (show) {
	        babelHelpers.classPrivateFieldGet(this, _guideTargets)['robot'] = target !== null && target !== void 0 ? target : null;
	      }
	    }
	  }, {
	    key: "setShowTriggerGuide",
	    value: function setShowTriggerGuide(show, target) {
	      babelHelpers.classPrivateFieldSet(this, _showTriggerGuide, show);
	      if (show) {
	        babelHelpers.classPrivateFieldGet(this, _guideTargets)['trigger'] = target !== null && target !== void 0 ? target : null;
	      }
	    }
	  }, {
	    key: "setShowSupportingRobotGuide",
	    value: function setShowSupportingRobotGuide(show, target) {
	      babelHelpers.classPrivateFieldSet(this, _showSupportingRobotGuide, show);
	      if (show) {
	        babelHelpers.classPrivateFieldGet(this, _guideTargets)['supportingRobot'] = target !== null && target !== void 0 ? target : null;
	      }
	    }
	  }, {
	    key: "start",
	    value: function start() {
	      _classPrivateMethodGet$e(this, _resolveShowGuides, _resolveShowGuides2).call(this);
	      var guide = _classPrivateMethodGet$e(this, _getGuide, _getGuide2).call(this);
	      if (guide) {
	        var bindElement = guide.getCurrentStep().target;
	        if (main_core.Type.isDomNode(bindElement) && document.body.contains(bindElement)) {
	          guide.showNextStep();
	        }
	      }
	    }
	  }, {
	    key: "isShownRobotGuide",
	    get: function get() {
	      return babelHelpers.classPrivateFieldGet(this, _isShownRobotGuide);
	    }
	  }, {
	    key: "isShownTriggerGuide",
	    get: function get() {
	      return babelHelpers.classPrivateFieldGet(this, _isShownTriggerGuide);
	    }
	  }]);
	  return AutomationGuide;
	}();
	function _resolveShowGuides2() {
	  // settings
	  if (babelHelpers.classPrivateFieldGet(this, _isShownTriggerGuide)) {
	    babelHelpers.classPrivateFieldSet(this, _showTriggerGuide, false);
	  }
	  if (babelHelpers.classPrivateFieldGet(this, _isShownSupportingRobotGuide)) {
	    babelHelpers.classPrivateFieldSet(this, _showSupportingRobotGuide, false);
	    babelHelpers.classPrivateFieldSet(this, _isShownRobotGuide, true);
	  }
	  if (babelHelpers.classPrivateFieldGet(this, _isShownRobotGuide)) {
	    babelHelpers.classPrivateFieldSet(this, _showRobotGuide, false);
	  }

	  // logic
	  if (babelHelpers.classPrivateFieldGet(this, _showSupportingRobotGuide)) {
	    babelHelpers.classPrivateFieldSet(this, _isShownRobotGuide, true);
	  }
	}
	function _getGuide2() {
	  var guide = null;
	  if (babelHelpers.classPrivateFieldGet(this, _showSupportingRobotGuide)) {
	    if (main_core.Type.isDomNode(babelHelpers.classPrivateFieldGet(this, _guideTargets)['supportingRobot'])) {
	      guide = _classPrivateMethodGet$e(this, _getSupportingRobotGuide, _getSupportingRobotGuide2).call(this);
	      guide.getPopup().setAutoHide(true);
	    }
	    return guide;
	  }
	  if (babelHelpers.classPrivateFieldGet(this, _showTriggerGuide)) {
	    if (main_core.Type.isDomNode(babelHelpers.classPrivateFieldGet(this, _guideTargets)['trigger'])) {
	      guide = _classPrivateMethodGet$e(this, _getTriggerGuide, _getTriggerGuide2).call(this);
	      guide.getPopup().setAutoHide(true);
	    }
	    return guide;
	  }
	  if (babelHelpers.classPrivateFieldGet(this, _showRobotGuide)) {
	    if (main_core.Type.isDomNode(babelHelpers.classPrivateFieldGet(this, _guideTargets)['robot'])) {
	      guide = _classPrivateMethodGet$e(this, _getRobotGuide, _getRobotGuide2).call(this);
	      guide.getPopup().setAutoHide(true);
	    }
	    return guide;
	  }
	  return guide;
	}
	function _getRobotGuide2() {
	  var _this = this;
	  var _this$constructor;
	  return new ui_tour.Guide({
	    steps: [{
	      target: babelHelpers.classPrivateFieldGet(this, _guideTargets)['robot'],
	      title: main_core.Loc.getMessage('BIZPROC_AUTOMATION_TOUR_GUIDE_ROBOT_TITLE_1'),
	      text: _classStaticPrivateMethodGet$2(_this$constructor = this.constructor, AutomationGuide, _getText).call(_this$constructor, [main_core.Loc.getMessage('BIZPROC_AUTOMATION_TOUR_GUIDE_ROBOT_SUBTITLE_1'), main_core.Loc.getMessage('BIZPROC_AUTOMATION_TOUR_GUIDE_ROBOT_SUBTITLE_2')]),
	      article: '16547618',
	      condition: {
	        top: false,
	        bottom: true,
	        color: 'primary'
	      },
	      position: 'top',
	      events: {
	        'onShow': function onShow() {
	          babelHelpers.classPrivateFieldSet(_this, _isShownRobotGuide, true);
	        }
	      }
	    }],
	    onEvents: true
	  });
	}
	function _getTriggerGuide2() {
	  var _this2 = this;
	  var _this$constructor2;
	  return new ui_tour.Guide({
	    steps: [{
	      target: babelHelpers.classPrivateFieldGet(this, _guideTargets)['trigger'],
	      title: main_core.Loc.getMessage('BIZPROC_AUTOMATION_TOUR_GUIDE_TRIGGER_TITLE_1'),
	      text: _classStaticPrivateMethodGet$2(_this$constructor2 = this.constructor, AutomationGuide, _getText).call(_this$constructor2, [main_core.Loc.getMessage('BIZPROC_AUTOMATION_TOUR_GUIDE_TRIGGER_SUBTITLE_1'), main_core.Loc.getMessage('BIZPROC_AUTOMATION_TOUR_GUIDE_TRIGGER_SUBTITLE_2')]),
	      article: '16547632',
	      condition: {
	        top: false,
	        bottom: true,
	        color: 'primary'
	      },
	      position: 'top',
	      events: {
	        'onShow': function onShow() {
	          babelHelpers.classPrivateFieldSet(_this2, _isShownTriggerGuide, true);
	        }
	      }
	    }],
	    onEvents: true
	  });
	}
	function _getSupportingRobotGuide2() {
	  var _this3 = this;
	  var _this$constructor3;
	  return new ui_tour.Guide({
	    steps: [{
	      target: babelHelpers.classPrivateFieldGet(this, _guideTargets)['supportingRobot'],
	      title: main_core.Loc.getMessage('BIZPROC_AUTOMATION_TOUR_GUIDE_SUPPORTING_ROBOT_TITLE'),
	      text: _classStaticPrivateMethodGet$2(_this$constructor3 = this.constructor, AutomationGuide, _getText).call(_this$constructor3, [main_core.Loc.getMessage('BIZPROC_AUTOMATION_TOUR_GUIDE_SUPPORTING_ROBOT_SUBTITLE_1'), main_core.Loc.getMessage('BIZPROC_AUTOMATION_TOUR_GUIDE_SUPPORTING_ROBOT_SUBTITLE_2')]),
	      article: '16547644',
	      condition: {
	        top: false,
	        bottom: true,
	        color: 'primary'
	      },
	      position: 'top',
	      events: {
	        'onShow': function onShow() {
	          babelHelpers.classPrivateFieldSet(_this3, _isShownSupportingRobotGuide, true);
	        }
	      }
	    }],
	    onEvents: true
	  });
	}
	function _getText(subtitles) {
	  var text = "<ul class=\"bizproc-automation-tour-guide-list\">";
	  var _iterator = _createForOfIteratorHelper$6(subtitles),
	    _step;
	  try {
	    for (_iterator.s(); !(_step = _iterator.n()).done;) {
	      var subtitle = _step.value;
	      text += "<li class=\"bizproc-automation-tour-guide-list-item\"> ".concat(main_core.Text.encode(subtitle), " </li>");
	    }
	  } catch (err) {
	    _iterator.e(err);
	  } finally {
	    _iterator.f();
	  }
	  text += "</ul>";
	  return text;
	}

	var contextInstance;
	function getGlobalContext() {
	  if (contextInstance instanceof Context) {
	    return contextInstance;
	  }
	  throw new Error('Context is not initialized yet');
	}
	function tryGetGlobalContext() {
	  try {
	    return getGlobalContext();
	  } catch (error) {
	    return null;
	  }
	}
	function setGlobalContext(context) {
	  if (context instanceof Context) {
	    contextInstance = context;
	  } else {
	    throw new Error('Unsupported Context');
	  }
	  return context;
	}

	exports.TemplatesScheme = TemplatesScheme;
	exports.Context = Context;
	exports.getGlobalContext = getGlobalContext;
	exports.tryGetGlobalContext = tryGetGlobalContext;
	exports.setGlobalContext = setGlobalContext;
	exports.TemplateScope = TemplateScope;
	exports.TriggerManager = TriggerManager;
	exports.Trigger = Trigger;
	exports.Template = Template;
	exports.Robot = Robot;
	exports.UserOptions = UserOptions;
	exports.Document = Document;
	exports.ViewMode = ViewMode;
	exports.ConditionGroup = ConditionGroup;
	exports.ConditionGroupSelector = ConditionGroupSelector;
	exports.Condition = Condition;
	exports.Designer = Designer;
	exports.SelectorManager = Manager;
	exports.InlineSelector = InlineSelector;
	exports.InlineSelectorCondition = InlineSelectorCondition;
	exports.InlineSelectorHtml = InlineSelectorHtml;
	exports.SaveStateCheckbox = SaveStateCheckbox;
	exports.UserSelector = UserSelector;
	exports.FileSelector = FileSelector;
	exports.TimeSelector = TimeSelector;
	exports.DelayInterval = DelayInterval;
	exports.DelayIntervalSelector = DelayIntervalSelector$$1;
	exports.HelpHint = HelpHint;
	exports.SelectorContext = SelectorContext;
	exports.AutomationGlobals = AutomationGlobals;
	exports.Statuses = Statuses;
	exports.Helper = Helper;
	exports.BeginningGuide = BeginningGuide;
	exports.AutomationGuide = AutomationGuide;
	exports.RobotEntry = RobotEntry;
	exports.TriggerEntry = TriggerEntry;
	exports.TrackingEntryBuilder = TrackingEntryBuilder;
	exports.TrackingEntry = TrackingEntry;
	exports.TrackingStatus = TrackingStatus;
	exports.Tracker = Tracker;
	exports.WorkflowStatus = WorkflowStatus;

}((this.BX.Bizproc.Automation = this.BX.Bizproc.Automation || {}),BX.UI,BX,BX.Bizproc,BX.Event,BX.UI.EntitySelector,BX.Main,BX.Main,BX.Bizproc,BX.Bizproc.Automation,BX,BX,BX,BX.UI.Tour));
//# sourceMappingURL=automation.bundle.js.map
