this.BX = this.BX || {};
this.BX.Socialnetwork = this.BX.Socialnetwork || {};
(function (exports,pull_client,tasks_scrum_meetings,tasks_scrum_methodology,ui_tour,im_public,ui_entitySelector,ui_buttons,ui_popupcomponentsmaker,main_core,main_core_events,main_popup) {
	'use strict';

	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _groupId = /*#__PURE__*/new WeakMap();
	var _update = /*#__PURE__*/new WeakSet();
	var PullRequests = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(PullRequests, _EventEmitter);
	  function PullRequests(groupId) {
	    var _this;
	    babelHelpers.classCallCheck(this, PullRequests);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(PullRequests).call(this));
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _update);
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _groupId, {
	      writable: true,
	      value: void 0
	    });
	    _this.setEventNamespace('BX.Socialnetwork.Spaces.Menu.PullRequests');
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _groupId, parseInt(groupId, 10));
	    return _this;
	  }
	  babelHelpers.createClass(PullRequests, [{
	    key: "getModuleId",
	    value: function getModuleId() {
	      return 'socialnetwork';
	    }
	  }, {
	    key: "getMap",
	    value: function getMap() {
	      return {
	        workgroup_user_add: _classPrivateMethodGet(this, _update, _update2).bind(this),
	        workgroup_user_delete: _classPrivateMethodGet(this, _update, _update2).bind(this),
	        workgroup_user_update: _classPrivateMethodGet(this, _update, _update2).bind(this)
	      };
	    }
	  }]);
	  return PullRequests;
	}(main_core_events.EventEmitter);
	function _update2(data) {
	  if (parseInt(data.params.GROUP_ID, 10) === babelHelpers.classPrivateFieldGet(this, _groupId)) {
	    this.emit('update');
	  }
	}

	var MenuAjax = /*#__PURE__*/function () {
	  function MenuAjax() {
	    babelHelpers.classCallCheck(this, MenuAjax);
	  }
	  babelHelpers.createClass(MenuAjax, null, [{
	    key: "getGroupData",
	    value: function getGroupData(groupId) {
	      return BX.ajax.runAction('socialnetwork.api.workgroup.get', {
	        data: {
	          params: {
	            select: ['ACTIONS', 'NUMBER_OF_MEMBERS', 'LIST_OF_MEMBERS', 'GROUP_MEMBERS_LIST', 'PRIVACY_TYPE', 'PIN', 'USER_DATA', 'COUNTERS'],
	            groupId: groupId
	          }
	        }
	      }).then(function (response) {
	        var _response$data$USER_D, _response$data$ACTION, _response$data$ACTION2;
	        return {
	          name: response.data.NAME,
	          isPin: response.data.IS_PIN,
	          privacyCode: response.data.PRIVACY_CODE,
	          isSubscribed: (_response$data$USER_D = response.data.USER_DATA) === null || _response$data$USER_D === void 0 ? void 0 : _response$data$USER_D.IS_SUBSCRIBED,
	          numberOfMembers: response.data.NUMBER_OF_MEMBERS,
	          listOfMembers: response.data.LIST_OF_MEMBERS,
	          groupMembersList: response.data.GROUP_MEMBERS_LIST,
	          actions: {
	            canEdit: (_response$data$ACTION = response.data.ACTIONS) === null || _response$data$ACTION === void 0 ? void 0 : _response$data$ACTION.EDIT,
	            canInvite: (_response$data$ACTION2 = response.data.ACTIONS) === null || _response$data$ACTION2 === void 0 ? void 0 : _response$data$ACTION2.INVITE
	          },
	          counters: response.data.COUNTERS
	        };
	      })["catch"](function (error) {
	        return console.log(error);
	      });
	    }
	  }, {
	    key: "inviteUsers",
	    value: function inviteUsers(spaceId, users) {
	      return BX.ajax.runAction('socialnetwork.api.workgroup.updateInvitedUsers', {
	        data: {
	          spaceId: spaceId,
	          users: [0].concat(babelHelpers.toConsumableArray(users))
	        }
	      });
	    }
	  }]);
	  return MenuAjax;
	}();

	var _templateObject;
	function _regeneratorRuntime() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/facebook/regenerator/blob/main/LICENSE */ _regeneratorRuntime = function _regeneratorRuntime() { return exports; }; var exports = {}, Op = Object.prototype, hasOwn = Op.hasOwnProperty, defineProperty = Object.defineProperty || function (obj, key, desc) { obj[key] = desc.value; }, $Symbol = "function" == typeof Symbol ? Symbol : {}, iteratorSymbol = $Symbol.iterator || "@@iterator", asyncIteratorSymbol = $Symbol.asyncIterator || "@@asyncIterator", toStringTagSymbol = $Symbol.toStringTag || "@@toStringTag"; function define(obj, key, value) { return Object.defineProperty(obj, key, { value: value, enumerable: !0, configurable: !0, writable: !0 }), obj[key]; } try { define({}, ""); } catch (err) { define = function define(obj, key, value) { return obj[key] = value; }; } function wrap(innerFn, outerFn, self, tryLocsList) { var protoGenerator = outerFn && outerFn.prototype instanceof Generator ? outerFn : Generator, generator = Object.create(protoGenerator.prototype), context = new Context(tryLocsList || []); return defineProperty(generator, "_invoke", { value: makeInvokeMethod(innerFn, self, context) }), generator; } function tryCatch(fn, obj, arg) { try { return { type: "normal", arg: fn.call(obj, arg) }; } catch (err) { return { type: "throw", arg: err }; } } exports.wrap = wrap; var ContinueSentinel = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} var IteratorPrototype = {}; define(IteratorPrototype, iteratorSymbol, function () { return this; }); var getProto = Object.getPrototypeOf, NativeIteratorPrototype = getProto && getProto(getProto(values([]))); NativeIteratorPrototype && NativeIteratorPrototype !== Op && hasOwn.call(NativeIteratorPrototype, iteratorSymbol) && (IteratorPrototype = NativeIteratorPrototype); var Gp = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(IteratorPrototype); function defineIteratorMethods(prototype) { ["next", "throw", "return"].forEach(function (method) { define(prototype, method, function (arg) { return this._invoke(method, arg); }); }); } function AsyncIterator(generator, PromiseImpl) { function invoke(method, arg, resolve, reject) { var record = tryCatch(generator[method], generator, arg); if ("throw" !== record.type) { var result = record.arg, value = result.value; return value && "object" == babelHelpers["typeof"](value) && hasOwn.call(value, "__await") ? PromiseImpl.resolve(value.__await).then(function (value) { invoke("next", value, resolve, reject); }, function (err) { invoke("throw", err, resolve, reject); }) : PromiseImpl.resolve(value).then(function (unwrapped) { result.value = unwrapped, resolve(result); }, function (error) { return invoke("throw", error, resolve, reject); }); } reject(record.arg); } var previousPromise; defineProperty(this, "_invoke", { value: function value(method, arg) { function callInvokeWithMethodAndArg() { return new PromiseImpl(function (resolve, reject) { invoke(method, arg, resolve, reject); }); } return previousPromise = previousPromise ? previousPromise.then(callInvokeWithMethodAndArg, callInvokeWithMethodAndArg) : callInvokeWithMethodAndArg(); } }); } function makeInvokeMethod(innerFn, self, context) { var state = "suspendedStart"; return function (method, arg) { if ("executing" === state) throw new Error("Generator is already running"); if ("completed" === state) { if ("throw" === method) throw arg; return doneResult(); } for (context.method = method, context.arg = arg;;) { var delegate = context.delegate; if (delegate) { var delegateResult = maybeInvokeDelegate(delegate, context); if (delegateResult) { if (delegateResult === ContinueSentinel) continue; return delegateResult; } } if ("next" === context.method) context.sent = context._sent = context.arg;else if ("throw" === context.method) { if ("suspendedStart" === state) throw state = "completed", context.arg; context.dispatchException(context.arg); } else "return" === context.method && context.abrupt("return", context.arg); state = "executing"; var record = tryCatch(innerFn, self, context); if ("normal" === record.type) { if (state = context.done ? "completed" : "suspendedYield", record.arg === ContinueSentinel) continue; return { value: record.arg, done: context.done }; } "throw" === record.type && (state = "completed", context.method = "throw", context.arg = record.arg); } }; } function maybeInvokeDelegate(delegate, context) { var methodName = context.method, method = delegate.iterator[methodName]; if (undefined === method) return context.delegate = null, "throw" === methodName && delegate.iterator["return"] && (context.method = "return", context.arg = undefined, maybeInvokeDelegate(delegate, context), "throw" === context.method) || "return" !== methodName && (context.method = "throw", context.arg = new TypeError("The iterator does not provide a '" + methodName + "' method")), ContinueSentinel; var record = tryCatch(method, delegate.iterator, context.arg); if ("throw" === record.type) return context.method = "throw", context.arg = record.arg, context.delegate = null, ContinueSentinel; var info = record.arg; return info ? info.done ? (context[delegate.resultName] = info.value, context.next = delegate.nextLoc, "return" !== context.method && (context.method = "next", context.arg = undefined), context.delegate = null, ContinueSentinel) : info : (context.method = "throw", context.arg = new TypeError("iterator result is not an object"), context.delegate = null, ContinueSentinel); } function pushTryEntry(locs) { var entry = { tryLoc: locs[0] }; 1 in locs && (entry.catchLoc = locs[1]), 2 in locs && (entry.finallyLoc = locs[2], entry.afterLoc = locs[3]), this.tryEntries.push(entry); } function resetTryEntry(entry) { var record = entry.completion || {}; record.type = "normal", delete record.arg, entry.completion = record; } function Context(tryLocsList) { this.tryEntries = [{ tryLoc: "root" }], tryLocsList.forEach(pushTryEntry, this), this.reset(!0); } function values(iterable) { if (iterable) { var iteratorMethod = iterable[iteratorSymbol]; if (iteratorMethod) return iteratorMethod.call(iterable); if ("function" == typeof iterable.next) return iterable; if (!isNaN(iterable.length)) { var i = -1, next = function next() { for (; ++i < iterable.length;) if (hasOwn.call(iterable, i)) return next.value = iterable[i], next.done = !1, next; return next.value = undefined, next.done = !0, next; }; return next.next = next; } } return { next: doneResult }; } function doneResult() { return { value: undefined, done: !0 }; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, defineProperty(Gp, "constructor", { value: GeneratorFunctionPrototype, configurable: !0 }), defineProperty(GeneratorFunctionPrototype, "constructor", { value: GeneratorFunction, configurable: !0 }), GeneratorFunction.displayName = define(GeneratorFunctionPrototype, toStringTagSymbol, "GeneratorFunction"), exports.isGeneratorFunction = function (genFun) { var ctor = "function" == typeof genFun && genFun.constructor; return !!ctor && (ctor === GeneratorFunction || "GeneratorFunction" === (ctor.displayName || ctor.name)); }, exports.mark = function (genFun) { return Object.setPrototypeOf ? Object.setPrototypeOf(genFun, GeneratorFunctionPrototype) : (genFun.__proto__ = GeneratorFunctionPrototype, define(genFun, toStringTagSymbol, "GeneratorFunction")), genFun.prototype = Object.create(Gp), genFun; }, exports.awrap = function (arg) { return { __await: arg }; }, defineIteratorMethods(AsyncIterator.prototype), define(AsyncIterator.prototype, asyncIteratorSymbol, function () { return this; }), exports.AsyncIterator = AsyncIterator, exports.async = function (innerFn, outerFn, self, tryLocsList, PromiseImpl) { void 0 === PromiseImpl && (PromiseImpl = Promise); var iter = new AsyncIterator(wrap(innerFn, outerFn, self, tryLocsList), PromiseImpl); return exports.isGeneratorFunction(outerFn) ? iter : iter.next().then(function (result) { return result.done ? result.value : iter.next(); }); }, defineIteratorMethods(Gp), define(Gp, toStringTagSymbol, "Generator"), define(Gp, iteratorSymbol, function () { return this; }), define(Gp, "toString", function () { return "[object Generator]"; }), exports.keys = function (val) { var object = Object(val), keys = []; for (var key in object) keys.push(key); return keys.reverse(), function next() { for (; keys.length;) { var key = keys.pop(); if (key in object) return next.value = key, next.done = !1, next; } return next.done = !0, next; }; }, exports.values = values, Context.prototype = { constructor: Context, reset: function reset(skipTempReset) { if (this.prev = 0, this.next = 0, this.sent = this._sent = undefined, this.done = !1, this.delegate = null, this.method = "next", this.arg = undefined, this.tryEntries.forEach(resetTryEntry), !skipTempReset) for (var name in this) "t" === name.charAt(0) && hasOwn.call(this, name) && !isNaN(+name.slice(1)) && (this[name] = undefined); }, stop: function stop() { this.done = !0; var rootRecord = this.tryEntries[0].completion; if ("throw" === rootRecord.type) throw rootRecord.arg; return this.rval; }, dispatchException: function dispatchException(exception) { if (this.done) throw exception; var context = this; function handle(loc, caught) { return record.type = "throw", record.arg = exception, context.next = loc, caught && (context.method = "next", context.arg = undefined), !!caught; } for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i], record = entry.completion; if ("root" === entry.tryLoc) return handle("end"); if (entry.tryLoc <= this.prev) { var hasCatch = hasOwn.call(entry, "catchLoc"), hasFinally = hasOwn.call(entry, "finallyLoc"); if (hasCatch && hasFinally) { if (this.prev < entry.catchLoc) return handle(entry.catchLoc, !0); if (this.prev < entry.finallyLoc) return handle(entry.finallyLoc); } else if (hasCatch) { if (this.prev < entry.catchLoc) return handle(entry.catchLoc, !0); } else { if (!hasFinally) throw new Error("try statement without catch or finally"); if (this.prev < entry.finallyLoc) return handle(entry.finallyLoc); } } } }, abrupt: function abrupt(type, arg) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.tryLoc <= this.prev && hasOwn.call(entry, "finallyLoc") && this.prev < entry.finallyLoc) { var finallyEntry = entry; break; } } finallyEntry && ("break" === type || "continue" === type) && finallyEntry.tryLoc <= arg && arg <= finallyEntry.finallyLoc && (finallyEntry = null); var record = finallyEntry ? finallyEntry.completion : {}; return record.type = type, record.arg = arg, finallyEntry ? (this.method = "next", this.next = finallyEntry.finallyLoc, ContinueSentinel) : this.complete(record); }, complete: function complete(record, afterLoc) { if ("throw" === record.type) throw record.arg; return "break" === record.type || "continue" === record.type ? this.next = record.arg : "return" === record.type ? (this.rval = this.arg = record.arg, this.method = "return", this.next = "end") : "normal" === record.type && afterLoc && (this.next = afterLoc), ContinueSentinel; }, finish: function finish(finallyLoc) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.finallyLoc === finallyLoc) return this.complete(entry.completion, entry.afterLoc), resetTryEntry(entry), ContinueSentinel; } }, "catch": function _catch(tryLoc) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.tryLoc === tryLoc) { var record = entry.completion; if ("throw" === record.type) { var thrown = record.arg; resetTryEntry(entry); } return thrown; } } throw new Error("illegal catch attempt"); }, delegateYield: function delegateYield(iterable, resultName, nextLoc) { return this.delegate = { iterator: values(iterable), resultName: resultName, nextLoc: nextLoc }, "next" === this.method && (this.arg = undefined), ContinueSentinel; } }, exports; }
	function _classPrivateFieldInitSpec$1(obj, privateMap, value) { _checkPrivateRedeclaration$1(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$1(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	var _buttons = /*#__PURE__*/new WeakMap();
	var CreateChatFooter = /*#__PURE__*/function (_DefaultFooter) {
	  babelHelpers.inherits(CreateChatFooter, _DefaultFooter);
	  function CreateChatFooter(dialog, options) {
	    var _this;
	    babelHelpers.classCallCheck(this, CreateChatFooter);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(CreateChatFooter).call(this, dialog, options));
	    _classPrivateFieldInitSpec$1(babelHelpers.assertThisInitialized(_this), _buttons, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _buttons, {
	      createChatButton: null,
	      cancelButton: null
	    });
	    _this.handleDialogDestroy = _this.handleDialogDestroy.bind(babelHelpers.assertThisInitialized(_this));
	    _this.handleSelectedItemsUpdated = _this.handleSelectedItemsUpdated.bind(babelHelpers.assertThisInitialized(_this));
	    _this.bindEvents();
	    return _this;
	  }
	  babelHelpers.createClass(CreateChatFooter, [{
	    key: "getContent",
	    value: function getContent() {
	      this.options.containerClass = 'sn-ui-selector-footer-create-chat';
	      return main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div>\n\t\t\t\t", "\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"])), this.renderCreateChatButton(), this.renderCancelButton());
	    }
	  }, {
	    key: "renderCreateChatButton",
	    value: function renderCreateChatButton() {
	      babelHelpers.classPrivateFieldGet(this, _buttons).createChatButton = new ui_buttons.Button({
	        text: main_core.Loc.getMessage('SOCNET_ENTITY_SELECTOR_CREATE'),
	        round: true,
	        color: ui_buttons.ButtonColor.PRIMARY,
	        size: ui_buttons.ButtonSize.SMALL,
	        events: {
	          click: this.createChatButtonClickHandler.bind(this)
	        }
	      });
	      babelHelpers.classPrivateFieldGet(this, _buttons).createChatButton.setDisabled(true);
	      return babelHelpers.classPrivateFieldGet(this, _buttons).createChatButton.getContainer();
	    }
	  }, {
	    key: "createChatButtonClickHandler",
	    value: function createChatButtonClickHandler() {
	      var _this2 = this;
	      var userIds = this.getDialog().getSelectedItems().map(function (item) {
	        return item.getId();
	      });
	      babelHelpers.classPrivateFieldGet(this, _buttons).createChatButton.setWaiting(true);
	      this.createChatAction(userIds).then( /*#__PURE__*/function () {
	        var _ref = babelHelpers.asyncToGenerator( /*#__PURE__*/_regeneratorRuntime().mark(function _callee(response) {
	          var chatId, _yield$Runtime$loadEx, Messenger;
	          return _regeneratorRuntime().wrap(function _callee$(_context) {
	            while (1) switch (_context.prev = _context.next) {
	              case 0:
	                chatId = 'chat' + response.data;
	                _context.next = 3;
	                return main_core.Runtime.loadExtension('im.public.iframe');
	              case 3:
	                _yield$Runtime$loadEx = _context.sent;
	                Messenger = _yield$Runtime$loadEx.Messenger;
	                Messenger.openChat(chatId);
	                babelHelpers.classPrivateFieldGet(_this2, _buttons).createChatButton.setWaiting(false);
	                _this2.getDialog().deselectAll();
	                _this2.getDialog().hide();
	              case 9:
	              case "end":
	                return _context.stop();
	            }
	          }, _callee);
	        }));
	        return function (_x) {
	          return _ref.apply(this, arguments);
	        };
	      }());
	    }
	  }, {
	    key: "createChatAction",
	    value: function createChatAction(userIds) {
	      return BX.ajax.runAction('socialnetwork.api.chat.create', {
	        data: {
	          userIds: userIds
	        }
	      });
	    }
	  }, {
	    key: "renderCancelButton",
	    value: function renderCancelButton() {
	      babelHelpers.classPrivateFieldGet(this, _buttons).cancelButton = new ui_buttons.Button({
	        text: main_core.Loc.getMessage('SOCNET_ENTITY_SELECTOR_CANCEL'),
	        round: true,
	        color: ui_buttons.ButtonColor.LIGHT_BORDER,
	        size: ui_buttons.ButtonSize.SMALL,
	        events: {
	          click: this.cancelButtonClickHandler.bind(this)
	        }
	      });
	      return babelHelpers.classPrivateFieldGet(this, _buttons).cancelButton.getContainer();
	    }
	  }, {
	    key: "cancelButtonClickHandler",
	    value: function cancelButtonClickHandler() {
	      this.getDialog().hide();
	    }
	  }, {
	    key: "bindEvents",
	    value: function bindEvents() {
	      this.getDialog().subscribe('onDestroy', this.handleDialogDestroy);
	      this.getDialog().subscribe('Item:onSelect', this.handleSelectedItemsUpdated);
	      this.getDialog().subscribe('Item:onDeselect', this.handleSelectedItemsUpdated);
	    }
	  }, {
	    key: "unbindEvents",
	    value: function unbindEvents() {
	      this.getDialog().unsubscribe('onDestroy', this.handleDialogDestroy);
	      this.getDialog().unsubscribe('Item:onSelect', this.handleSelectedItemsUpdated);
	      this.getDialog().unsubscribe('Item:onDeselect', this.handleSelectedItemsUpdated);
	    }
	  }, {
	    key: "handleSelectedItemsUpdated",
	    value: function handleSelectedItemsUpdated() {
	      if (!babelHelpers.classPrivateFieldGet(this, _buttons).createChatButton) {
	        return;
	      }
	      babelHelpers.classPrivateFieldGet(this, _buttons).createChatButton.setDisabled(this.getDialog().getSelectedItems().length === 0);
	    }
	  }, {
	    key: "handleDialogDestroy",
	    value: function handleDialogDestroy() {
	      this.unbindEvents();
	    }
	  }]);
	  return CreateChatFooter;
	}(ui_entitySelector.DefaultFooter);

	var _templateObject$1;
	function _classPrivateMethodInitSpec$1(obj, privateSet) { _checkPrivateRedeclaration$2(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec$2(obj, privateMap, value) { _checkPrivateRedeclaration$2(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$2(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet$1(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _bindElement = /*#__PURE__*/new WeakMap();
	var _title = /*#__PURE__*/new WeakMap();
	var _preselectedItems = /*#__PURE__*/new WeakMap();
	var _onClose = /*#__PURE__*/new WeakMap();
	var _onLoad = /*#__PURE__*/new WeakMap();
	var _groupId$1 = /*#__PURE__*/new WeakMap();
	var _createChat = /*#__PURE__*/new WeakMap();
	var _bindSliderEvents = /*#__PURE__*/new WeakSet();
	var _isInviteSlider = /*#__PURE__*/new WeakSet();
	var _getImBar = /*#__PURE__*/new WeakSet();
	var UserSelector = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(UserSelector, _EventEmitter);
	  function UserSelector(options) {
	    var _options$preselectedI, _options$onClose, _options$onLoad, _options$groupId, _options$createChat;
	    var _this;
	    babelHelpers.classCallCheck(this, UserSelector);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(UserSelector).call(this, options));
	    _classPrivateMethodInitSpec$1(babelHelpers.assertThisInitialized(_this), _getImBar);
	    _classPrivateMethodInitSpec$1(babelHelpers.assertThisInitialized(_this), _isInviteSlider);
	    _classPrivateMethodInitSpec$1(babelHelpers.assertThisInitialized(_this), _bindSliderEvents);
	    _classPrivateFieldInitSpec$2(babelHelpers.assertThisInitialized(_this), _bindElement, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$2(babelHelpers.assertThisInitialized(_this), _title, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$2(babelHelpers.assertThisInitialized(_this), _preselectedItems, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$2(babelHelpers.assertThisInitialized(_this), _onClose, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$2(babelHelpers.assertThisInitialized(_this), _onLoad, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$2(babelHelpers.assertThisInitialized(_this), _groupId$1, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$2(babelHelpers.assertThisInitialized(_this), _createChat, {
	      writable: true,
	      value: void 0
	    });
	    _this.setEventNamespace('SocialNetwork.Spaces.UserSelector');
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _bindElement, options.bindElement);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _title, options.title);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _preselectedItems, (_options$preselectedI = options.preselectedItems) !== null && _options$preselectedI !== void 0 ? _options$preselectedI : []);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _onClose, (_options$onClose = options.onClose) !== null && _options$onClose !== void 0 ? _options$onClose : function () {});
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _onLoad, (_options$onLoad = options.onLoad) !== null && _options$onLoad !== void 0 ? _options$onLoad : function () {});
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _groupId$1, (_options$groupId = options.groupId) !== null && _options$groupId !== void 0 ? _options$groupId : 0);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _createChat, (_options$createChat = options.createChat) !== null && _options$createChat !== void 0 ? _options$createChat : false);
	    return _this;
	  }
	  babelHelpers.createClass(UserSelector, [{
	    key: "getDialog",
	    value: function getDialog() {
	      if (!this.dialog) {
	        var title = babelHelpers.classPrivateFieldGet(this, _title);
	        var UserSelectorDialogHeader = /*#__PURE__*/function (_BaseHeader) {
	          babelHelpers.inherits(UserSelectorDialogHeader, _BaseHeader);
	          function UserSelectorDialogHeader() {
	            babelHelpers.classCallCheck(this, UserSelectorDialogHeader);
	            return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(UserSelectorDialogHeader).apply(this, arguments));
	          }
	          babelHelpers.createClass(UserSelectorDialogHeader, [{
	            key: "render",
	            value: function render() {
	              return main_core.Tag.render(_templateObject$1 || (_templateObject$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t\t<div class=\"sn-spaces__entity-dialog-header\">", "</div>\n\t\t\t\t\t"])), title);
	            }
	          }]);
	          return UserSelectorDialogHeader;
	        }(ui_entitySelector.BaseHeader);
	        this.dialog = new ui_entitySelector.Dialog({
	          targetNode: babelHelpers.classPrivateFieldGet(this, _bindElement),
	          width: 400,
	          dropdownMode: true,
	          header: UserSelectorDialogHeader,
	          enableSearch: true,
	          context: 'socialnetwork.spaces',
	          preselectedItems: babelHelpers.classPrivateFieldGet(this, _preselectedItems),
	          entities: [{
	            id: 'user',
	            substituteEntityId: babelHelpers.classPrivateFieldGet(this, _createChat) ? 'project-user' : null,
	            options: {
	              footerInviteIntranetOnly: true,
	              showInvitationFooter: !babelHelpers.classPrivateFieldGet(this, _createChat),
	              projectId: babelHelpers.classPrivateFieldGet(this, _groupId$1)
	            }
	          }],
	          searchTabOptions: babelHelpers.classPrivateFieldGet(this, _createChat) && {
	            stubOptions: {
	              title: main_core.Loc.getMessage('SN_SPACES_USER_SELECTOR_SEARCH_TAB_EMPTY_TITLE'),
	              subtitle: main_core.Loc.getMessage('SN_SPACES_USER_SELECTOR_SEARCH_TAB_EMPTY_TEXT')
	            }
	          },
	          events: {
	            onHide: babelHelpers.classPrivateFieldGet(this, _onClose),
	            onLoad: babelHelpers.classPrivateFieldGet(this, _onLoad),
	            'SearchTab:onLoad': babelHelpers.classPrivateFieldGet(this, _onLoad)
	          }
	        });
	        if (babelHelpers.classPrivateFieldGet(this, _createChat)) {
	          this.dialog.setFooter(CreateChatFooter);
	        }
	        _classPrivateMethodGet$1(this, _bindSliderEvents, _bindSliderEvents2).call(this);
	      }
	      return this.dialog;
	    }
	  }, {
	    key: "reload",
	    value: function reload() {
	      this.dialog.loadState = 'UNSENT';
	      this.dialog.load();
	    }
	  }]);
	  return UserSelector;
	}(main_core_events.EventEmitter);
	function _bindSliderEvents2() {
	  var _this2 = this;
	  main_core_events.EventEmitter.subscribe('SidePanel.Slider:onOpenStart', function (event) {
	    var slider = event.target;
	    if (_classPrivateMethodGet$1(_this2, _getImBar, _getImBar2).call(_this2)) {
	      main_core.Dom.style(_classPrivateMethodGet$1(_this2, _getImBar, _getImBar2).call(_this2), 'zIndex', slider.getZindex());
	    }
	  });
	  main_core_events.EventEmitter.subscribe('SidePanel.Slider:onBeforeCloseComplete', function (event) {
	    var slider = event.target;
	    if (_classPrivateMethodGet$1(_this2, _isInviteSlider, _isInviteSlider2).call(_this2, slider) && _classPrivateMethodGet$1(_this2, _getImBar, _getImBar2).call(_this2)) {
	      main_core.Dom.style(_classPrivateMethodGet$1(_this2, _getImBar, _getImBar2).call(_this2), 'zIndex', '');
	    }
	  });
	}
	function _isInviteSlider2(slider) {
	  var _slider$options$data;
	  return ((_slider$options$data = slider.options.data) === null || _slider$options$data === void 0 ? void 0 : _slider$options$data.entitySelectorId) === this.dialog.getId();
	}
	function _getImBar2() {
	  return document.getElementById('bx-im-bar');
	}

	function _classPrivateMethodInitSpec$2(obj, privateSet) { _checkPrivateRedeclaration$3(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec$3(obj, privateMap, value) { _checkPrivateRedeclaration$3(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$3(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet$2(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _entityType = /*#__PURE__*/new WeakMap();
	var _entityId = /*#__PURE__*/new WeakMap();
	var _groupMembersList = /*#__PURE__*/new WeakMap();
	var _updateMemberNodes = /*#__PURE__*/new WeakSet();
	var Chat = /*#__PURE__*/function () {
	  function Chat(params) {
	    babelHelpers.classCallCheck(this, Chat);
	    _classPrivateMethodInitSpec$2(this, _updateMemberNodes);
	    _classPrivateFieldInitSpec$3(this, _entityType, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$3(this, _entityId, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$3(this, _groupMembersList, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldSet(this, _entityType, params.entityType);
	    babelHelpers.classPrivateFieldSet(this, _entityId, params.entityId);
	    babelHelpers.classPrivateFieldSet(this, _groupMembersList, params.groupMembersList);
	  }
	  babelHelpers.createClass(Chat, [{
	    key: "startVideoCall",
	    value: function startVideoCall() {
	      var _this = this;
	      // eslint-disable-next-line promise/catch-or-return
	      main_core.ajax.runAction('intranet.controlbutton.getVideoCallChat', {
	        data: {
	          entityType: babelHelpers.classPrivateFieldGet(this, _entityType) === 'group' ? 'workgroup' : 'user',
	          entityId: babelHelpers.classPrivateFieldGet(this, _entityId)
	        },
	        analyticsLabel: {
	          entity: babelHelpers.classPrivateFieldGet(this, _entityType)
	        }
	      }).then(function (response) {
	        if (response.data) {
	          im_public.Messenger.startVideoCall("chat".concat(response.data), true);
	        }
	        _this.chatLockCounter = 0;
	      }, function (response) {
	        if (response.errors[0].code === 'lock_error' && _this.chatLockCounter < 4) {
	          _this.chatLockCounter++;
	          _this.startVideoCall();
	        }
	      });
	    }
	  }, {
	    key: "openChat",
	    value: function openChat() {
	      var _this2 = this;
	      // eslint-disable-next-line promise/catch-or-return
	      main_core.ajax.runAction('intranet.controlbutton.getChat', {
	        data: {
	          entityType: babelHelpers.classPrivateFieldGet(this, _entityType) === 'group' ? 'workgroup' : 'user',
	          entityId: babelHelpers.classPrivateFieldGet(this, _entityId)
	        },
	        analyticsLabel: {
	          entity: babelHelpers.classPrivateFieldGet(this, _entityType)
	        }
	      }).then(function (response) {
	        if (response.data) {
	          im_public.Messenger.openChat("chat".concat(parseInt(response.data, 10)));
	        }
	        _this2.chatLockCounter = 0;
	      }, function (response) {
	        if (response.errors[0].code === 'lock_error' && _this2.chatLockCounter < 4) {
	          _this2.chatLockCounter++;
	          _this2.openChat();
	        }
	      });
	    }
	  }, {
	    key: "createChat",
	    value: function createChat(node) {
	      this.getDialog(node).show();
	    }
	  }, {
	    key: "getDialog",
	    value: function getDialog(node) {
	      if (!this.userSelector) {
	        this.userSelector = new UserSelector({
	          bindElement: node,
	          createChat: true,
	          title: main_core.Loc.getMessage('SN_SPACES_CREATE_CHAT'),
	          onLoad: _classPrivateMethodGet$2(this, _updateMemberNodes, _updateMemberNodes2).bind(this),
	          groupId: babelHelpers.classPrivateFieldGet(this, _entityId)
	        });
	      }
	      return this.userSelector.getDialog();
	    }
	  }, {
	    key: "update",
	    value: function update(groupDataPromise) {
	      var _this3 = this;
	      groupDataPromise.then(function (response) {
	        var _this3$userSelector;
	        babelHelpers.classPrivateFieldSet(_this3, _groupMembersList, response.groupMembersList);
	        (_this3$userSelector = _this3.userSelector) === null || _this3$userSelector === void 0 ? void 0 : _this3$userSelector.reload();
	      });
	    }
	  }]);
	  return Chat;
	}();
	function _updateMemberNodes2() {
	  var membersIds = babelHelpers.classPrivateFieldGet(this, _groupMembersList).filter(function (user) {
	    return user.isMember;
	  }).map(function (item) {
	    return parseInt(item.id);
	  });
	  this.getDialog().getItems().forEach(function (item) {
	    var isHidden = !membersIds.includes(item.getId());
	    item.setHidden(isHidden);
	    if (isHidden && item.isSelected()) {
	      item.deselect();
	    }
	  });
	}

	function _classPrivateMethodInitSpec$3(obj, privateSet) { _checkPrivateRedeclaration$4(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec$4(obj, privateMap, value) { _checkPrivateRedeclaration$4(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$4(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet$3(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _node = /*#__PURE__*/new WeakMap();
	var _invitedUsers = /*#__PURE__*/new WeakMap();
	var _cannotInvite = /*#__PURE__*/new WeakMap();
	var _onClose$1 = /*#__PURE__*/new WeakSet();
	var _arraysAreEqual = /*#__PURE__*/new WeakSet();
	var _updateCannotInviteNodes = /*#__PURE__*/new WeakSet();
	var _prepareInvited = /*#__PURE__*/new WeakSet();
	var _prepareCannotInvite = /*#__PURE__*/new WeakSet();
	var _prepareItems = /*#__PURE__*/new WeakSet();
	var Invite = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(Invite, _EventEmitter);
	  function Invite(options) {
	    var _this;
	    babelHelpers.classCallCheck(this, Invite);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Invite).call(this, options));
	    _classPrivateMethodInitSpec$3(babelHelpers.assertThisInitialized(_this), _prepareItems);
	    _classPrivateMethodInitSpec$3(babelHelpers.assertThisInitialized(_this), _prepareCannotInvite);
	    _classPrivateMethodInitSpec$3(babelHelpers.assertThisInitialized(_this), _prepareInvited);
	    _classPrivateMethodInitSpec$3(babelHelpers.assertThisInitialized(_this), _updateCannotInviteNodes);
	    _classPrivateMethodInitSpec$3(babelHelpers.assertThisInitialized(_this), _arraysAreEqual);
	    _classPrivateMethodInitSpec$3(babelHelpers.assertThisInitialized(_this), _onClose$1);
	    _classPrivateFieldInitSpec$4(babelHelpers.assertThisInitialized(_this), _node, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$4(babelHelpers.assertThisInitialized(_this), _invitedUsers, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$4(babelHelpers.assertThisInitialized(_this), _cannotInvite, {
	      writable: true,
	      value: void 0
	    });
	    _this.setEventNamespace('SocialNetwork.Spaces.Invite');
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _node, options.node);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _invitedUsers, _classPrivateMethodGet$3(babelHelpers.assertThisInitialized(_this), _prepareInvited, _prepareInvited2).call(babelHelpers.assertThisInitialized(_this), options.groupMembersList));
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _cannotInvite, _classPrivateMethodGet$3(babelHelpers.assertThisInitialized(_this), _prepareCannotInvite, _prepareCannotInvite2).call(babelHelpers.assertThisInitialized(_this), options.groupMembersList));
	    return _this;
	  }
	  babelHelpers.createClass(Invite, [{
	    key: "show",
	    value: function show() {
	      this.getDialog().show();
	    }
	  }, {
	    key: "close",
	    value: function close() {
	      this.getDialog().hide();
	    }
	  }, {
	    key: "isShown",
	    value: function isShown() {
	      this.getDialogPopup().isShown();
	    }
	  }, {
	    key: "getDialogPopup",
	    value: function getDialogPopup() {
	      return this.getDialog().getPopup();
	    }
	  }, {
	    key: "getDialog",
	    value: function getDialog() {
	      if (!this.dialog) {
	        var userSelector = new UserSelector({
	          bindElement: babelHelpers.classPrivateFieldGet(this, _node),
	          title: main_core.Loc.getMessage('SN_SPACES_MENU_SPACE_INVITE_MEMBERS'),
	          preselectedItems: _classPrivateMethodGet$3(this, _prepareItems, _prepareItems2).call(this),
	          onClose: _classPrivateMethodGet$3(this, _onClose$1, _onClose2).bind(this),
	          onLoad: _classPrivateMethodGet$3(this, _updateCannotInviteNodes, _updateCannotInviteNodes2).bind(this)
	        });
	        this.dialog = userSelector.getDialog();
	        this.dialog.getPopup().setAngle({
	          position: 'top',
	          offset: babelHelpers.classPrivateFieldGet(this, _node).offsetWidth + parseInt(getComputedStyle(babelHelpers.classPrivateFieldGet(this, _node)).marginLeft)
	        });
	      }
	      return this.dialog;
	    }
	  }, {
	    key: "update",
	    value: function update(groupDataPromise) {
	      var _this2 = this;
	      groupDataPromise.then(function (response) {
	        babelHelpers.classPrivateFieldSet(_this2, _invitedUsers, _classPrivateMethodGet$3(_this2, _prepareInvited, _prepareInvited2).call(_this2, response.groupMembersList));
	        babelHelpers.classPrivateFieldSet(_this2, _cannotInvite, _classPrivateMethodGet$3(_this2, _prepareCannotInvite, _prepareCannotInvite2).call(_this2, response.groupMembersList));
	        _classPrivateMethodGet$3(_this2, _updateCannotInviteNodes, _updateCannotInviteNodes2).call(_this2);
	      });
	    }
	  }]);
	  return Invite;
	}(main_core_events.EventEmitter);
	function _onClose2() {
	  var users = babelHelpers.toConsumableArray(this.dialog.getSelectedItems()).map(function (item) {
	    return item.id;
	  });
	  if (!_classPrivateMethodGet$3(this, _arraysAreEqual, _arraysAreEqual2).call(this, users, babelHelpers.classPrivateFieldGet(this, _invitedUsers))) {
	    babelHelpers.classPrivateFieldSet(this, _invitedUsers, users);
	    this.emit('usersSelected', babelHelpers.classPrivateFieldGet(this, _invitedUsers));
	  }
	  this.emit('onClose');
	}
	function _arraysAreEqual2(arr1, arr2) {
	  return babelHelpers.toConsumableArray(arr1).sort().toString() === babelHelpers.toConsumableArray(arr2).sort().toString();
	}
	function _updateCannotInviteNodes2() {
	  var _this$dialog,
	    _this3 = this;
	  (_this$dialog = this.dialog) === null || _this$dialog === void 0 ? void 0 : _this$dialog.getItems().forEach(function (item) {
	    var isNotInInvited = !babelHelpers.classPrivateFieldGet(_this3, _invitedUsers).includes(item.getId());
	    var isHidden = babelHelpers.classPrivateFieldGet(_this3, _cannotInvite).includes(item.getId());
	    item.setHidden(isHidden);
	    if ((isHidden || isNotInInvited) && item.isSelected()) {
	      item.deselect();
	    }
	  });
	}
	function _prepareInvited2(users) {
	  return users.filter(function (user) {
	    return user.invited;
	  }).map(function (user) {
	    return parseInt(user.id);
	  });
	}
	function _prepareCannotInvite2(users) {
	  return users.filter(function (user) {
	    return !user.invited;
	  }).map(function (user) {
	    return parseInt(user.id);
	  });
	}
	function _prepareItems2() {
	  return babelHelpers.classPrivateFieldGet(this, _invitedUsers).map(function (userId) {
	    return ['user', parseInt(userId)];
	  });
	}

	var _templateObject$2;
	function _classPrivateMethodInitSpec$4(obj, privateSet) { _checkPrivateRedeclaration$5(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec$5(obj, privateMap, value) { _checkPrivateRedeclaration$5(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$5(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet$4(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _logo = /*#__PURE__*/new WeakMap();
	var _getIconStyle = /*#__PURE__*/new WeakSet();
	var _getIconClass = /*#__PURE__*/new WeakSet();
	var Logo = /*#__PURE__*/function () {
	  function Logo(logo) {
	    babelHelpers.classCallCheck(this, Logo);
	    _classPrivateMethodInitSpec$4(this, _getIconClass);
	    _classPrivateMethodInitSpec$4(this, _getIconStyle);
	    _classPrivateFieldInitSpec$5(this, _logo, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldSet(this, _logo, logo);
	  }
	  babelHelpers.createClass(Logo, [{
	    key: "render",
	    value: function render() {
	      var iconClass = _classPrivateMethodGet$4(this, _getIconClass, _getIconClass2).call(this);
	      var iconStyle = _classPrivateMethodGet$4(this, _getIconStyle, _getIconStyle2).call(this);
	      return main_core.Tag.render(_templateObject$2 || (_templateObject$2 = babelHelpers.taggedTemplateLiteral(["<i class=\"", "\" style=\"", "\"></i>"])), iconClass, iconStyle);
	    }
	  }, {
	    key: "getClass",
	    value: function getClass() {
	      var result = '';
	      if (babelHelpers.classPrivateFieldGet(this, _logo).type === 'icon') {
	        if (babelHelpers.classPrivateFieldGet(this, _logo).id.length > 0) {
	          result = "sonet-common-workgroup-avatar --".concat(babelHelpers.classPrivateFieldGet(this, _logo).id);
	        } else {
	          result = 'ui-icon-common-user-group ui-icon';
	        }
	      }
	      return result;
	    }
	  }]);
	  return Logo;
	}();
	function _getIconStyle2() {
	  var result = '';
	  if (babelHelpers.classPrivateFieldGet(this, _logo).type === 'image') {
	    result = "background-image: url(".concat(babelHelpers.classPrivateFieldGet(this, _logo).id, "); background-size: cover");
	  }
	  return result;
	}
	function _getIconClass2() {
	  return babelHelpers.classPrivateFieldGet(this, _logo).type === 'image' ? 'sn-spaces__list-item_img' : '';
	}

	function _classPrivateFieldInitSpec$6(obj, privateMap, value) { _checkPrivateRedeclaration$6(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$6(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	var _pathToFeatures = /*#__PURE__*/new WeakMap();
	var _pathToUsers = /*#__PURE__*/new WeakMap();
	var _pathToInvite = /*#__PURE__*/new WeakMap();
	var _sidePanelManager = /*#__PURE__*/new WeakMap();
	var MenuRouter = /*#__PURE__*/function () {
	  function MenuRouter(params) {
	    babelHelpers.classCallCheck(this, MenuRouter);
	    _classPrivateFieldInitSpec$6(this, _pathToFeatures, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$6(this, _pathToUsers, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$6(this, _pathToInvite, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$6(this, _sidePanelManager, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldSet(this, _sidePanelManager, BX.SidePanel.Instance);
	    babelHelpers.classPrivateFieldSet(this, _pathToFeatures, params.pathToFeatures);
	    babelHelpers.classPrivateFieldSet(this, _pathToUsers, params.pathToUsers);
	    babelHelpers.classPrivateFieldSet(this, _pathToInvite, params.pathToInvite);
	  }
	  babelHelpers.createClass(MenuRouter, [{
	    key: "openGroupFeatures",
	    value: function openGroupFeatures() {
	      babelHelpers.classPrivateFieldGet(this, _sidePanelManager).open(babelHelpers.classPrivateFieldGet(this, _pathToFeatures), {
	        width: 800,
	        loader: 'group-features-loader'
	      });
	    }
	  }, {
	    key: "openGroupUsers",
	    value: function openGroupUsers(mode) {
	      var availableModes = {
	        all: 'members',
	        "in": 'requests_in',
	        out: 'requests_out'
	      };
	      var uri = new main_core.Uri(babelHelpers.classPrivateFieldGet(this, _pathToUsers));
	      uri.setQueryParams({
	        mode: availableModes[mode]
	      });
	      babelHelpers.classPrivateFieldGet(this, _sidePanelManager).open(uri.toString(), {
	        width: 1200,
	        cacheable: false,
	        loader: 'group-users-loader'
	      });
	    }
	  }, {
	    key: "openGroupInvite",
	    value: function openGroupInvite() {
	      babelHelpers.classPrivateFieldGet(this, _sidePanelManager).open(babelHelpers.classPrivateFieldGet(this, _pathToInvite), {
	        width: 950,
	        loader: 'group-invite-loader'
	      });
	    }
	  }]);
	  return MenuRouter;
	}();

	var _templateObject$3;
	var ChatAction = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(ChatAction, _EventEmitter);
	  function ChatAction() {
	    var _this;
	    babelHelpers.classCallCheck(this, ChatAction);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(ChatAction).call(this));
	    _this.setEventNamespace('BX.Socialnetwork.Spaces.Settings.ChatAction');
	    return _this;
	  }
	  babelHelpers.createClass(ChatAction, [{
	    key: "render",
	    value: function render() {
	      var _this2 = this;
	      var videoCallId = 'spaces-settings-video-call';
	      var openChatId = 'spaces-settings-open-chat';
	      var createChatId = 'spaces-settings-create-chat';
	      var node = main_core.Tag.render(_templateObject$3 || (_templateObject$3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"sn-spaces__popup-communication\">\n\t\t\t\t<div\n\t\t\t\t\tdata-id=\"", "\"\n\t\t\t\t\tclass=\"sn-spaces__popup-communication-item\"\n\t\t\t\t>\n\t\t\t\t\t<div\n\t\t\t\t\t\tclass=\"ui-icon-set --video-1\"\n\t\t\t\t\t\tstyle=\"--ui-icon-set__icon-size: 26px;\"\n\t\t\t\t\t></div>\n\t\t\t\t\t<div class=\"sn-spaces__popup-communication-item_text\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t\t<div\n\t\t\t\t\tdata-id=\"", "\"\n\t\t\t\t\tclass=\"sn-spaces__popup-communication-item\"\n\t\t\t\t>\n\t\t\t\t\t<div\n\t\t\t\t\t\tclass=\"ui-icon-set --chat-1\"\n\t\t\t\t\t\tstyle=\"--ui-icon-set__icon-size: 26px;\"\n\t\t\t\t\t></div>\n\t\t\t\t\t<div class=\"sn-spaces__popup-communication-item_text\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t\t<div\n\t\t\t\t\tdata-id=\"", "\"\n\t\t\t\t\tclass=\"sn-spaces__popup-communication-item\"\n\t\t\t\t>\n\t\t\t\t\t<div\n\t\t\t\t\t\tclass=\"ui-icon-set --add-chat\"\n\t\t\t\t\t\tstyle=\"--ui-icon-set__icon-size: 26px;\"\n\t\t\t\t\t></div>\n\t\t\t\t\t<div class=\"sn-spaces__popup-communication-item_text\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t"])), videoCallId, main_core.Loc.getMessage('SN_SPACES_MENU_CHAT_VIDEO_CALL_HD'), openChatId, main_core.Loc.getMessage('SN_SPACES_MENU_CHAT_OPEN'), createChatId, main_core.Loc.getMessage('SN_SPACES_MENU_CHAT_CREATE'));
	      main_core.Event.bind(node.querySelector("[data-id='".concat(videoCallId, "']")), 'click', function () {
	        return _this2.emit('videoCall');
	      });
	      main_core.Event.bind(node.querySelector("[data-id='".concat(openChatId, "']")), 'click', function () {
	        return _this2.emit('openChat');
	      });
	      main_core.Event.bind(node.querySelector("[data-id='".concat(createChatId, "']")), 'click', function () {
	        return _this2.emit('createChat');
	      });
	      return node;
	    }
	  }]);
	  return ChatAction;
	}(main_core_events.EventEmitter);

	var _templateObject$4;
	function _classPrivateMethodInitSpec$5(obj, privateSet) { _checkPrivateRedeclaration$7(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec$7(obj, privateMap, value) { _checkPrivateRedeclaration$7(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$7(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet$5(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _follow = /*#__PURE__*/new WeakMap();
	var _disabled = /*#__PURE__*/new WeakMap();
	var _node$1 = /*#__PURE__*/new WeakMap();
	var _toggle = /*#__PURE__*/new WeakSet();
	var _changeIcon = /*#__PURE__*/new WeakSet();
	var _changeLabel = /*#__PURE__*/new WeakSet();
	var _getLabel = /*#__PURE__*/new WeakSet();
	var _bindEvents = /*#__PURE__*/new WeakSet();
	var Follow = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(Follow, _EventEmitter);
	  function Follow(params) {
	    var _this;
	    babelHelpers.classCallCheck(this, Follow);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Follow).call(this));
	    _classPrivateMethodInitSpec$5(babelHelpers.assertThisInitialized(_this), _bindEvents);
	    _classPrivateMethodInitSpec$5(babelHelpers.assertThisInitialized(_this), _getLabel);
	    _classPrivateMethodInitSpec$5(babelHelpers.assertThisInitialized(_this), _changeLabel);
	    _classPrivateMethodInitSpec$5(babelHelpers.assertThisInitialized(_this), _changeIcon);
	    _classPrivateMethodInitSpec$5(babelHelpers.assertThisInitialized(_this), _toggle);
	    _classPrivateFieldInitSpec$7(babelHelpers.assertThisInitialized(_this), _follow, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$7(babelHelpers.assertThisInitialized(_this), _disabled, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$7(babelHelpers.assertThisInitialized(_this), _node$1, {
	      writable: true,
	      value: void 0
	    });
	    _this.setEventNamespace('BX.Socialnetwork.Spaces.Settings.Follow');
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _follow, params.follow);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _disabled, false);
	    _classPrivateMethodGet$5(babelHelpers.assertThisInitialized(_this), _bindEvents, _bindEvents2).call(babelHelpers.assertThisInitialized(_this));
	    return _this;
	  }
	  babelHelpers.createClass(Follow, [{
	    key: "render",
	    value: function render() {
	      var followId = 'spaces-settings-follow';
	      var iconClass = babelHelpers.classPrivateFieldGet(this, _follow) ? '--sound-on' : '--sound-off';
	      babelHelpers.classPrivateFieldSet(this, _node$1, main_core.Tag.render(_templateObject$4 || (_templateObject$4 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div\n\t\t\t\tdata-id=\"", "\"\n\t\t\t\tclass=\"sn-spaces__popup-item --mini\"\n\t\t\t>\n\t\t\t\t<div class=\"sn-spaces__popup-icon-round\">\n\t\t\t\t\t<div\n\t\t\t\t\t\tclass=\"ui-icon-set ", "\"\n\t\t\t\t\t\tstyle=\"--ui-icon-set__icon-size: 22px;\"\n\t\t\t\t\t></div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"sn-spaces__popup-icon-round-name\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t"])), followId, iconClass, _classPrivateMethodGet$5(this, _getLabel, _getLabel2).call(this, babelHelpers.classPrivateFieldGet(this, _follow))));
	      main_core.Event.bind(babelHelpers.classPrivateFieldGet(this, _node$1), 'click', _classPrivateMethodGet$5(this, _toggle, _toggle2).bind(this));
	      return babelHelpers.classPrivateFieldGet(this, _node$1);
	    }
	  }, {
	    key: "unDisable",
	    value: function unDisable() {
	      babelHelpers.classPrivateFieldSet(this, _disabled, false);
	    }
	  }]);
	  return Follow;
	}(main_core_events.EventEmitter);
	function _toggle2() {
	  if (babelHelpers.classPrivateFieldGet(this, _disabled)) {
	    return;
	  }
	  babelHelpers.classPrivateFieldSet(this, _disabled, true);
	  babelHelpers.classPrivateFieldSet(this, _follow, !babelHelpers.classPrivateFieldGet(this, _follow));
	  this.emit('update', babelHelpers.classPrivateFieldGet(this, _follow));
	  _classPrivateMethodGet$5(this, _changeIcon, _changeIcon2).call(this, babelHelpers.classPrivateFieldGet(this, _follow));
	  _classPrivateMethodGet$5(this, _changeLabel, _changeLabel2).call(this, babelHelpers.classPrivateFieldGet(this, _follow));
	}
	function _changeIcon2(follow) {
	  var iconNode = babelHelpers.classPrivateFieldGet(this, _node$1).querySelector('.ui-icon-set');
	  if (follow) {
	    main_core.Dom.removeClass(iconNode, '--sound-off');
	    main_core.Dom.addClass(iconNode, '--sound-on');
	  } else {
	    main_core.Dom.removeClass(iconNode, '--sound-on');
	    main_core.Dom.addClass(iconNode, '--sound-off');
	  }
	}
	function _changeLabel2(follow) {
	  var nameNode = babelHelpers.classPrivateFieldGet(this, _node$1).querySelector('.sn-spaces__popup-icon-round-name');
	  nameNode.textContent = _classPrivateMethodGet$5(this, _getLabel, _getLabel2).call(this, follow);
	}
	function _getLabel2(follow) {
	  return follow ? main_core.Loc.getMessage('SN_SPACES_MENU_FOLLOW_N') : main_core.Loc.getMessage('SN_SPACES_MENU_FOLLOW_Y');
	}
	function _bindEvents2() {
	  main_core_events.EventEmitter.subscribe('followChanged', function (event) {
	    babelHelpers.classPrivateFieldSet(this, _follow, event.data.isFollowed);
	    _classPrivateMethodGet$5(this, _changeLabel, _changeLabel2).call(this, babelHelpers.classPrivateFieldGet(this, _follow));
	    _classPrivateMethodGet$5(this, _changeIcon, _changeIcon2).call(this, babelHelpers.classPrivateFieldGet(this, _follow));
	  }.bind(this));
	}

	var _templateObject$5;
	function _classPrivateMethodInitSpec$6(obj, privateSet) { _checkPrivateRedeclaration$8(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec$8(obj, privateMap, value) { _checkPrivateRedeclaration$8(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$8(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet$6(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _popup = /*#__PURE__*/new WeakMap();
	var _contentNode = /*#__PURE__*/new WeakMap();
	var _privacyCode = /*#__PURE__*/new WeakMap();
	var _createPopup = /*#__PURE__*/new WeakSet();
	var _renderContent = /*#__PURE__*/new WeakSet();
	var _changePrivacy = /*#__PURE__*/new WeakSet();
	var GroupPrivacy = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(GroupPrivacy, _EventEmitter);
	  function GroupPrivacy(params) {
	    var _this;
	    babelHelpers.classCallCheck(this, GroupPrivacy);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(GroupPrivacy).call(this));
	    _classPrivateMethodInitSpec$6(babelHelpers.assertThisInitialized(_this), _changePrivacy);
	    _classPrivateMethodInitSpec$6(babelHelpers.assertThisInitialized(_this), _renderContent);
	    _classPrivateMethodInitSpec$6(babelHelpers.assertThisInitialized(_this), _createPopup);
	    _classPrivateFieldInitSpec$8(babelHelpers.assertThisInitialized(_this), _popup, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$8(babelHelpers.assertThisInitialized(_this), _contentNode, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$8(babelHelpers.assertThisInitialized(_this), _privacyCode, {
	      writable: true,
	      value: void 0
	    });
	    _this.setEventNamespace('BX.Socialnetwork.Spaces.Settings.Group.Privacy');
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _privacyCode, params.privacyCode);
	    _classPrivateMethodGet$6(babelHelpers.assertThisInitialized(_this), _createPopup, _createPopup2).call(babelHelpers.assertThisInitialized(_this), params.bindElement);
	    return _this;
	  }
	  babelHelpers.createClass(GroupPrivacy, [{
	    key: "show",
	    value: function show() {
	      if (babelHelpers.classPrivateFieldGet(this, _popup).isShown()) {
	        babelHelpers.classPrivateFieldGet(this, _popup).close();
	      } else {
	        babelHelpers.classPrivateFieldGet(this, _popup).show();
	      }
	    }
	  }]);
	  return GroupPrivacy;
	}(main_core_events.EventEmitter);
	function _createPopup2(bindElement) {
	  var _this2 = this;
	  babelHelpers.classPrivateFieldSet(this, _popup, new main_popup.Popup({
	    id: 'sn-post-form',
	    bindElement: bindElement,
	    content: _classPrivateMethodGet$6(this, _renderContent, _renderContent2).call(this, babelHelpers.classPrivateFieldGet(this, _privacyCode)),
	    autoHide: true,
	    angle: false,
	    width: 343,
	    closeIcon: false,
	    closeByEsc: true,
	    overlay: true,
	    padding: 12,
	    animation: 'fading-slide'
	  }));
	  babelHelpers.classPrivateFieldGet(this, _popup).subscribe('onShow', function () {
	    return _this2.emit('onShow');
	  });
	  babelHelpers.classPrivateFieldGet(this, _popup).subscribe('onAfterClose', function () {
	    return _this2.emit('onAfterClose');
	  });
	}
	function _renderContent2(privacyCode) {
	  var _this3 = this;
	  var openActiveClass = privacyCode === 'open' ? '--active' : '';
	  var closedActiveClass = privacyCode === 'closed' ? '--active' : '';
	  var secretActiveClass = privacyCode === 'secret' ? '--active' : '';
	  var openId = 'spaces-group-privacy-open';
	  var closedId = 'spaces-group-privacy-closed';
	  var secretId = 'spaces-group-privacy-secret';
	  babelHelpers.classPrivateFieldSet(this, _contentNode, main_core.Tag.render(_templateObject$5 || (_templateObject$5 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"sn-spaces__popup-menu\">\n\t\t\t\t<div data-id=\"", "\" class=\"sn-spaces__popup-menu_item ", "\">\n\t\t\t\t\t<div class=\"sn-spaces__popup-menu_item-icon --open-spaces\"></div>\n\t\t\t\t\t<div class=\"sn-spaces__popup-menu_item-info\">\n\t\t\t\t\t\t<div class=\"sn-spaces__popup-menu_item-name\">\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<div class=\"sn-spaces__popup-menu_item-description\">\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t\t<div data-id=\"", "\" class=\"sn-spaces__popup-menu_item ", "\">\n\t\t\t\t\t<div class=\"sn-spaces__popup-menu_item-icon --closed-spaces\"></div>\n\t\t\t\t\t<div class=\"sn-spaces__popup-menu_item-info\">\n\t\t\t\t\t\t<div class=\"sn-spaces__popup-menu_item-name\">\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<div class=\"sn-spaces__popup-menu_item-description\">\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t\t<div data-id=\"", "\" class=\"sn-spaces__popup-menu_item ", "\">\n\t\t\t\t\t<div class=\"sn-spaces__popup-menu_item-icon --secret-spaces\"></div>\n\t\t\t\t\t<div class=\"sn-spaces__popup-menu_item-info\">\n\t\t\t\t\t\t<div class=\"sn-spaces__popup-menu_item-name\">\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<div class=\"sn-spaces__popup-menu_item-description\">\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"sn-spaces__popup-menu_hint\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t"])), openId, openActiveClass, main_core.Loc.getMessage('SN_SPACES_MENU_INFO_VS_OPEN'), main_core.Loc.getMessage('SN_SPACES_MENU_INFO_VS_OPEN_DESC'), closedId, closedActiveClass, main_core.Loc.getMessage('SN_SPACES_MENU_INFO_VS_CLOSED'), main_core.Loc.getMessage('SN_SPACES_MENU_INFO_VS_CLOSED_DESC'), secretId, secretActiveClass, main_core.Loc.getMessage('SN_SPACES_MENU_INFO_VS_SECRET'), main_core.Loc.getMessage('SN_SPACES_MENU_INFO_VS_SECRET_DESC'), main_core.Loc.getMessage('SN_SPACES_MENU_INFO_VS_PROMPT')));
	  main_core.Event.bind(babelHelpers.classPrivateFieldGet(this, _contentNode).querySelector("[data-id='".concat(openId, "']")), 'click', function () {
	    return _classPrivateMethodGet$6(_this3, _changePrivacy, _changePrivacy2).call(_this3, 'open');
	  });
	  main_core.Event.bind(babelHelpers.classPrivateFieldGet(this, _contentNode).querySelector("[data-id='".concat(closedId, "']")), 'click', function () {
	    return _classPrivateMethodGet$6(_this3, _changePrivacy, _changePrivacy2).call(_this3, 'closed');
	  });
	  main_core.Event.bind(babelHelpers.classPrivateFieldGet(this, _contentNode).querySelector("[data-id='".concat(secretId, "']")), 'click', function () {
	    return _classPrivateMethodGet$6(_this3, _changePrivacy, _changePrivacy2).call(_this3, 'secret');
	  });
	  return babelHelpers.classPrivateFieldGet(this, _contentNode);
	}
	function _changePrivacy2(privacyCode) {
	  babelHelpers.classPrivateFieldSet(this, _privacyCode, privacyCode);
	  main_core.Dom.removeClass(babelHelpers.classPrivateFieldGet(this, _contentNode).querySelector('.--active'), '--active');
	  var node = babelHelpers.classPrivateFieldGet(this, _contentNode).querySelector("[data-id='spaces-group-privacy-".concat(privacyCode, "']"));
	  main_core.Dom.addClass(node, '--active');
	  this.emit('changePrivacy', babelHelpers.classPrivateFieldGet(this, _privacyCode));
	  babelHelpers.classPrivateFieldGet(this, _popup).close();
	}

	var _templateObject$6, _templateObject2, _templateObject3, _templateObject4, _templateObject5, _templateObject6;
	function _classPrivateMethodInitSpec$7(obj, privateSet) { _checkPrivateRedeclaration$9(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec$9(obj, privateMap, value) { _checkPrivateRedeclaration$9(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$9(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet$7(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _title$1 = /*#__PURE__*/new WeakMap();
	var _logo$1 = /*#__PURE__*/new WeakMap();
	var _privacyCode$1 = /*#__PURE__*/new WeakMap();
	var _privacyPopup = /*#__PURE__*/new WeakMap();
	var _actions = /*#__PURE__*/new WeakMap();
	var _node$2 = /*#__PURE__*/new WeakMap();
	var _titleNode = /*#__PURE__*/new WeakMap();
	var _editNode = /*#__PURE__*/new WeakMap();
	var _showPrivacy = /*#__PURE__*/new WeakSet();
	var _startEditTitle = /*#__PURE__*/new WeakSet();
	var _stopEditTitle = /*#__PURE__*/new WeakSet();
	var _setTitle = /*#__PURE__*/new WeakSet();
	var _renderTitle = /*#__PURE__*/new WeakSet();
	var _renderEdit = /*#__PURE__*/new WeakSet();
	var _renderPrivacy = /*#__PURE__*/new WeakSet();
	var _renderPencilIcon = /*#__PURE__*/new WeakSet();
	var _renderPrivacyIcon = /*#__PURE__*/new WeakSet();
	var _getPrivateLabel = /*#__PURE__*/new WeakSet();
	var _changePrivacy$1 = /*#__PURE__*/new WeakSet();
	var Info = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(Info, _EventEmitter);
	  function Info(params) {
	    var _this;
	    babelHelpers.classCallCheck(this, Info);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Info).call(this));
	    _classPrivateMethodInitSpec$7(babelHelpers.assertThisInitialized(_this), _changePrivacy$1);
	    _classPrivateMethodInitSpec$7(babelHelpers.assertThisInitialized(_this), _getPrivateLabel);
	    _classPrivateMethodInitSpec$7(babelHelpers.assertThisInitialized(_this), _renderPrivacyIcon);
	    _classPrivateMethodInitSpec$7(babelHelpers.assertThisInitialized(_this), _renderPencilIcon);
	    _classPrivateMethodInitSpec$7(babelHelpers.assertThisInitialized(_this), _renderPrivacy);
	    _classPrivateMethodInitSpec$7(babelHelpers.assertThisInitialized(_this), _renderEdit);
	    _classPrivateMethodInitSpec$7(babelHelpers.assertThisInitialized(_this), _renderTitle);
	    _classPrivateMethodInitSpec$7(babelHelpers.assertThisInitialized(_this), _setTitle);
	    _classPrivateMethodInitSpec$7(babelHelpers.assertThisInitialized(_this), _stopEditTitle);
	    _classPrivateMethodInitSpec$7(babelHelpers.assertThisInitialized(_this), _startEditTitle);
	    _classPrivateMethodInitSpec$7(babelHelpers.assertThisInitialized(_this), _showPrivacy);
	    _classPrivateFieldInitSpec$9(babelHelpers.assertThisInitialized(_this), _title$1, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$9(babelHelpers.assertThisInitialized(_this), _logo$1, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$9(babelHelpers.assertThisInitialized(_this), _privacyCode$1, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$9(babelHelpers.assertThisInitialized(_this), _privacyPopup, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$9(babelHelpers.assertThisInitialized(_this), _actions, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$9(babelHelpers.assertThisInitialized(_this), _node$2, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$9(babelHelpers.assertThisInitialized(_this), _titleNode, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$9(babelHelpers.assertThisInitialized(_this), _editNode, {
	      writable: true,
	      value: void 0
	    });
	    _this.setEventNamespace('BX.Socialnetwork.Spaces.Settings.Info');
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _title$1, params.title);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _logo$1, params.logo);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _privacyCode$1, params.privacyCode);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _actions, params.actions);
	    return _this;
	  }
	  babelHelpers.createClass(Info, [{
	    key: "render",
	    value: function render() {
	      var _this2 = this;
	      var moreBtnId = 'spaces-settings-space-info-btn';
	      babelHelpers.classPrivateFieldSet(this, _node$2, main_core.Tag.render(_templateObject$6 || (_templateObject$6 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"sn-spaces__popup-item --main\">\n\t\t\t\t<div class=\"sn-spaces__popup-settings_logo\">\n\t\t\t\t\t<div class=\"sn-spaces__list-item_icon ", "\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"sn-spaces__popup-settings_info\">\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t\t<div\n\t\t\t\t\tstyle=\"display: none;\"\n\t\t\t\t\tdata-id=\"", "\"\n\t\t\t\t\tclass=\"ui-popupcomponentmaker__btn --large --border sn-spaces__popup-settings_btn\"\n\t\t\t\t>", "</div>\n\t\t\t</div>\n\t\t"])), babelHelpers.classPrivateFieldGet(this, _logo$1).getClass(), babelHelpers.classPrivateFieldGet(this, _logo$1).render(), _classPrivateMethodGet$7(this, _renderTitle, _renderTitle2).call(this), _classPrivateMethodGet$7(this, _renderEdit, _renderEdit2).call(this), _classPrivateMethodGet$7(this, _renderPrivacy, _renderPrivacy2).call(this), moreBtnId, main_core.Loc.getMessage('SN_SPACES_MENU_INFO_MORE_BTN')));
	      main_core.Event.bind(babelHelpers.classPrivateFieldGet(this, _node$2).querySelector("[data-id='".concat(moreBtnId, "']")), 'click', function () {
	        return _this2.emit('more');
	      });
	      return babelHelpers.classPrivateFieldGet(this, _node$2);
	    }
	  }]);
	  return Info;
	}(main_core_events.EventEmitter);
	function _showPrivacy2(event) {
	  var _this3 = this;
	  if (!babelHelpers.classPrivateFieldGet(this, _privacyPopup)) {
	    babelHelpers.classPrivateFieldSet(this, _privacyPopup, new GroupPrivacy({
	      bindElement: event.target,
	      privacyCode: babelHelpers.classPrivateFieldGet(this, _privacyCode$1)
	    }));
	    babelHelpers.classPrivateFieldGet(this, _privacyPopup).subscribe('onShow', function () {
	      return _this3.emit('setAutoHide', false);
	    });
	    babelHelpers.classPrivateFieldGet(this, _privacyPopup).subscribe('onAfterClose', function () {
	      return _this3.emit('setAutoHide', true);
	    });
	    babelHelpers.classPrivateFieldGet(this, _privacyPopup).subscribe('changePrivacy', _classPrivateMethodGet$7(this, _changePrivacy$1, _changePrivacy2$1).bind(this));
	  }
	  babelHelpers.classPrivateFieldGet(this, _privacyPopup).show();
	}
	function _startEditTitle2() {
	  main_core.Dom.addClass(babelHelpers.classPrivateFieldGet(this, _titleNode), '--hidden');
	  main_core.Dom.removeClass(babelHelpers.classPrivateFieldGet(this, _editNode), '--hidden');
	  var input = babelHelpers.classPrivateFieldGet(this, _editNode).querySelector('input');
	  input.focus();
	  input.setSelectionRange(input.value.length, input.value.length);
	}
	function _stopEditTitle2() {
	  main_core.Dom.addClass(babelHelpers.classPrivateFieldGet(this, _editNode), '--hidden');
	  main_core.Dom.removeClass(babelHelpers.classPrivateFieldGet(this, _titleNode), '--hidden');
	}
	function _setTitle2(value) {
	  babelHelpers.classPrivateFieldSet(this, _title$1, value);
	  var node = babelHelpers.classPrivateFieldGet(this, _titleNode).querySelector('.sn-spaces__popup-settings_name');
	  node.textContent = main_core.Text.encode(babelHelpers.classPrivateFieldGet(this, _title$1));
	}
	function _renderTitle2() {
	  babelHelpers.classPrivateFieldSet(this, _titleNode, main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"sn-spaces__popup-settings_title\">\n\t\t\t\t<div\n\t\t\t\t\tdata-id=\"spaces-settings-space-info-name\"\n\t\t\t\t\tclass=\"sn-spaces__popup-settings_name\"\n\t\t\t\t>\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"])), main_core.Text.encode(babelHelpers.classPrivateFieldGet(this, _title$1)), babelHelpers.classPrivateFieldGet(this, _actions).canEdit ? _classPrivateMethodGet$7(this, _renderPencilIcon, _renderPencilIcon2).call(this) : ''));
	  return babelHelpers.classPrivateFieldGet(this, _titleNode);
	}
	function _renderEdit2() {
	  var _this4 = this;
	  var uiClasses = 'ui-ctl ui-ctl-textbox ui-ctl--w100 ui-ctl--transp ' + 'ui-ctl-no-border ui-ctl-xs ui-ctl-no-padding';
	  babelHelpers.classPrivateFieldSet(this, _editNode, main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div\n\t\t\t\tdata-id=\"spaces-settings-space-info-edit\"\n\t\t\t\tclass=\"sn-spaces__popup-settings_title --hidden\"\n\t\t\t>\n\t\t\t\t<div class=\"", "\">\n\t\t\t\t\t<input\n\t\t\t\t\t\ttype=\"text\"\n\t\t\t\t\t\tclass=\"ui-ctl-element sn-spaces__popup-settings_name-input\"\n\t\t\t\t\t\tvalue=\"", "\"\n\t\t\t\t\t>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t"])), uiClasses, main_core.Text.encode(babelHelpers.classPrivateFieldGet(this, _title$1))));
	  var input = babelHelpers.classPrivateFieldGet(this, _editNode).querySelector('input');
	  main_core.Event.bind(input, 'keydown', function (event) {
	    if (event.key === 'Escape' || event.key === 'Enter') {
	      input.blur();
	      event.stopImmediatePropagation();
	    }
	  });
	  main_core.Event.bind(input, 'blur', function () {
	    if (babelHelpers.classPrivateFieldGet(_this4, _title$1) !== input.value) {
	      _classPrivateMethodGet$7(_this4, _setTitle, _setTitle2).call(_this4, input.value);
	      _this4.emit('changeTitle', babelHelpers.classPrivateFieldGet(_this4, _title$1));
	    }
	    _classPrivateMethodGet$7(_this4, _stopEditTitle, _stopEditTitle2).call(_this4);
	  });
	  return babelHelpers.classPrivateFieldGet(this, _editNode);
	}
	function _renderPrivacy2() {
	  var node = main_core.Tag.render(_templateObject4 || (_templateObject4 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div\n\t\t\t\tdata-id=\"spaces-settings-space-info-privacy\"\n\t\t\t\tclass=\"sn-spaces__popup-settings_select-private\"\n\t\t\t>\n\t\t\t\t<div class=\"sn-spaces__popup-settings_select-private-text\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"])), _classPrivateMethodGet$7(this, _getPrivateLabel, _getPrivateLabel2).call(this, babelHelpers.classPrivateFieldGet(this, _privacyCode$1)), babelHelpers.classPrivateFieldGet(this, _actions).canEdit ? _classPrivateMethodGet$7(this, _renderPrivacyIcon, _renderPrivacyIcon2).call(this) : '');
	  if (babelHelpers.classPrivateFieldGet(this, _actions).canEdit) {
	    main_core.Event.bind(node, 'click', _classPrivateMethodGet$7(this, _showPrivacy, _showPrivacy2).bind(this));
	  }
	  return node;
	}
	function _renderPencilIcon2() {
	  var node = main_core.Tag.render(_templateObject5 || (_templateObject5 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div\n\t\t\t\tdata-id=\"spaces-settings-space-info-title-edit\" \n\t\t\t\tclass=\"ui-icon-set --pencil-40\"\n\t\t\t\tstyle=\"--ui-icon-set__icon-size: 18px;\"\n\t\t\t></div>\n\t\t"])));
	  main_core.Event.bind(node, 'click', _classPrivateMethodGet$7(this, _startEditTitle, _startEditTitle2).bind(this));
	  return node;
	}
	function _renderPrivacyIcon2() {
	  return main_core.Tag.render(_templateObject6 || (_templateObject6 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div\n\t\t\t\tclass=\"ui-icon-set --chevron-down\"\n\t\t\t\tstyle=\"--ui-icon-set__icon-size: 14px;\"\n\t\t\t></div>\n\t\t"])));
	}
	function _getPrivateLabel2(privacyCode) {
	  return main_core.Loc.getMessage("SN_SPACES_MENU_INFO_VS_".concat(privacyCode.toUpperCase()));
	}
	function _changePrivacy2$1(baseEvent) {
	  var privacyCode = baseEvent.getData();
	  babelHelpers.classPrivateFieldGet(this, _node$2).querySelector('.sn-spaces__popup-settings_select-private-text').textContent = _classPrivateMethodGet$7(this, _getPrivateLabel, _getPrivateLabel2).call(this, privacyCode);
	  this.emit('changePrivacy', privacyCode);
	}

	var _templateObject$7, _templateObject2$1, _templateObject3$1, _templateObject4$1, _templateObject5$1, _templateObject6$1, _templateObject7, _templateObject8, _templateObject9, _templateObject10, _templateObject11;
	function _classPrivateMethodInitSpec$8(obj, privateSet) { _checkPrivateRedeclaration$a(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec$a(obj, privateMap, value) { _checkPrivateRedeclaration$a(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$a(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet$8(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _amount = /*#__PURE__*/new WeakMap();
	var _list = /*#__PURE__*/new WeakMap();
	var _counters = /*#__PURE__*/new WeakMap();
	var _actions$1 = /*#__PURE__*/new WeakMap();
	var _renderInviteBtn = /*#__PURE__*/new WeakSet();
	var _renderList = /*#__PURE__*/new WeakSet();
	var _renderCounters = /*#__PURE__*/new WeakSet();
	var _renderOwner = /*#__PURE__*/new WeakSet();
	var _renderModerator = /*#__PURE__*/new WeakSet();
	var _renderUser = /*#__PURE__*/new WeakSet();
	var _renderAvatar = /*#__PURE__*/new WeakSet();
	var _prepareList = /*#__PURE__*/new WeakSet();
	var _canInvite = /*#__PURE__*/new WeakSet();
	var _hasCounters = /*#__PURE__*/new WeakSet();
	var _hasOutCounters = /*#__PURE__*/new WeakSet();
	var _hasInCounters = /*#__PURE__*/new WeakSet();
	var Members = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(Members, _EventEmitter);
	  function Members(params) {
	    var _this;
	    babelHelpers.classCallCheck(this, Members);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Members).call(this));
	    _classPrivateMethodInitSpec$8(babelHelpers.assertThisInitialized(_this), _hasInCounters);
	    _classPrivateMethodInitSpec$8(babelHelpers.assertThisInitialized(_this), _hasOutCounters);
	    _classPrivateMethodInitSpec$8(babelHelpers.assertThisInitialized(_this), _hasCounters);
	    _classPrivateMethodInitSpec$8(babelHelpers.assertThisInitialized(_this), _canInvite);
	    _classPrivateMethodInitSpec$8(babelHelpers.assertThisInitialized(_this), _prepareList);
	    _classPrivateMethodInitSpec$8(babelHelpers.assertThisInitialized(_this), _renderAvatar);
	    _classPrivateMethodInitSpec$8(babelHelpers.assertThisInitialized(_this), _renderUser);
	    _classPrivateMethodInitSpec$8(babelHelpers.assertThisInitialized(_this), _renderModerator);
	    _classPrivateMethodInitSpec$8(babelHelpers.assertThisInitialized(_this), _renderOwner);
	    _classPrivateMethodInitSpec$8(babelHelpers.assertThisInitialized(_this), _renderCounters);
	    _classPrivateMethodInitSpec$8(babelHelpers.assertThisInitialized(_this), _renderList);
	    _classPrivateMethodInitSpec$8(babelHelpers.assertThisInitialized(_this), _renderInviteBtn);
	    _classPrivateFieldInitSpec$a(babelHelpers.assertThisInitialized(_this), _amount, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$a(babelHelpers.assertThisInitialized(_this), _list, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$a(babelHelpers.assertThisInitialized(_this), _counters, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$a(babelHelpers.assertThisInitialized(_this), _actions$1, {
	      writable: true,
	      value: void 0
	    });
	    _this.setEventNamespace('BX.Socialnetwork.Spaces.Settings.Members');
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _amount, params.amount);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _list, params.list);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _counters, params.counters);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _actions$1, params.actions);
	    return _this;
	  }
	  babelHelpers.createClass(Members, [{
	    key: "render",
	    value: function render() {
	      var _this2 = this;
	      var inviteBtnId = 'spaces-settings-create-members-btn';
	      var node = main_core.Tag.render(_templateObject$7 || (_templateObject$7 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"sn-spaces__popup-item_expandable\">\n\t\t\t\t<div class=\"sn-spaces__popup-item --expandable\">\n\t\t\t\t\t<div class=\"sn-spaces__popup-item_icon-block\">\n\t\t\t\t\t\t<div class=\"sn-spaces__popup-item_icon\">\n\t\t\t\t\t\t\t<div\n\t\t\t\t\t\t\t\tclass=\"ui-icon-set --persons-3\"\n\t\t\t\t\t\t\t\tstyle=\"--ui-icon-set__icon-size: 27px;\"\n\t\t\t\t\t\t\t></div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"sn-spaces__popup-item_main-info\">\n\t\t\t\t\t\t<div class=\"sn-spaces__popup-item_members\">\n\t\t\t\t\t\t\t<div class=\"sn-spaces__popup-item_members-info\">\n\t\t\t\t\t\t\t\t", ": ", "\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"])), main_core.Loc.getMessage('SN_SPACES_MENU_MEMBERS_LABEL'), parseInt(babelHelpers.classPrivateFieldGet(this, _amount), 10), _classPrivateMethodGet$8(this, _canInvite, _canInvite2).call(this) ? _classPrivateMethodGet$8(this, _renderInviteBtn, _renderInviteBtn2).call(this, inviteBtnId) : '', _classPrivateMethodGet$8(this, _renderList, _renderList2).call(this), _classPrivateMethodGet$8(this, _canInvite, _canInvite2).call(this) && _classPrivateMethodGet$8(this, _hasCounters, _hasCounters2).call(this) ? _classPrivateMethodGet$8(this, _renderCounters, _renderCounters2).call(this) : '');
	      var inviteNode = node.querySelector("[data-id='".concat(inviteBtnId, "']"));
	      main_core.Event.bind(node.querySelector('.--expandable'), 'click', function (event) {
	        if (!inviteNode || !event.target.isEqualNode(inviteNode)) {
	          _this2.emit('showUsers', 'all');
	        }
	      });
	      return node;
	    }
	  }]);
	  return Members;
	}(main_core_events.EventEmitter);
	function _renderInviteBtn2(inviteBtnId) {
	  var _this3 = this;
	  var node = main_core.Tag.render(_templateObject2$1 || (_templateObject2$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div \n\t\t\t\tdata-id=\"", "\" \n\t\t\t\tclass=\"ui-popupcomponentmaker__btn --border sn----spaces__popup-settings_btn\"\n\t\t\t>\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"])), inviteBtnId, main_core.Loc.getMessage('SN_SPACES_MENU_MEMBERS_INVITE_BTN'));
	  main_core.Event.bind(node, 'click', function () {
	    return _this3.emit('invite');
	  });
	  return node;
	}
	function _renderList2() {
	  var _this4 = this;
	  var groupedMembers = _classPrivateMethodGet$8(this, _prepareList, _prepareList2).call(this);
	  var visibleAmount = 3;
	  var moderators = groupedMembers.moderators.slice(0, visibleAmount);
	  var users = groupedMembers.users.slice(0, visibleAmount);
	  var amount = babelHelpers.classPrivateFieldGet(this, _amount) - (1 + moderators.length + users.length);
	  return main_core.Tag.render(_templateObject3$1 || (_templateObject3$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"sn-spaces__popup-item_lists\">\n\t\t\t\t<div class=\"sn-spaces__popup-item_list\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t\t<div\n\t\t\t\t\tclass=\"sn-spaces__popup-item_list\"\n\t\t\t\t\tstyle=\"", "\"\n\t\t\t\t>\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t\t<div\n\t\t\t\t\tclass=\"sn-spaces__popup-item_list\"\n\t\t\t\t\tstyle=\"", "\"\n\t\t\t\t>\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t\t<div\n\t\t\t\t\tclass=\"sn-spaces__popup-item_list-quantity\"\n\t\t\t\t\tstyle=\"", "\"\n\t\t\t\t>+", "</div>\n\t\t\t</div>\n\t\t"])), _classPrivateMethodGet$8(this, _renderOwner, _renderOwner2).call(this, groupedMembers.owner), moderators.length > 0 ? '' : 'display: none;', moderators.map(function (member) {
	    return _classPrivateMethodGet$8(_this4, _renderModerator, _renderModerator2).call(_this4, member);
	  }), users.length > 0 ? '' : 'display: none;', users.map(function (member) {
	    return _classPrivateMethodGet$8(_this4, _renderUser, _renderUser2).call(_this4, member);
	  }), amount > 0 ? '' : 'display: none;', amount);
	}
	function _renderCounters2() {
	  var _this5 = this;
	  var outCounter = parseInt(babelHelpers.classPrivateFieldGet(this, _counters).workgroup_requests_out, 10);
	  var inCounter = parseInt(babelHelpers.classPrivateFieldGet(this, _counters).workgroup_requests_in, 10);
	  var outId = 'spaces-settings-members-counter-out';
	  var inId = 'spaces-settings-members-counter-in';
	  var renderOut = function renderOut() {
	    if (!_classPrivateMethodGet$8(_this5, _hasOutCounters, _hasOutCounters2).call(_this5)) {
	      return '';
	    }
	    return main_core.Tag.render(_templateObject4$1 || (_templateObject4$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div data-id=\"", "\" class=\"sn-spaces__popup-item --primary\">\n\t\t\t\t\t<div class=\"sn-spaces__popup-item_counter\">", "</div>\n\t\t\t\t\t<span class=\"\">", "</span>\n\t\t\t\t\t<div\n\t\t\t\t\t\tclass=\"ui-icon-set --chevron-right\"\n\t\t\t\t\t\tstyle=\"--ui-icon-set__icon-size: 14px;\"\n\t\t\t\t\t></div>\n\t\t\t\t</div>\n\t\t\t"])), outId, outCounter, main_core.Loc.getMessage('SN_SPACES_MENU_MEMBERS_GREEN_LABEL'));
	  };
	  var renderIn = function renderIn() {
	    if (!_classPrivateMethodGet$8(_this5, _hasInCounters, _hasInCounters2).call(_this5)) {
	      return '';
	    }
	    return main_core.Tag.render(_templateObject5$1 || (_templateObject5$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div data-id=\"", "\" class=\"sn-spaces__popup-item --warning\">\n\t\t\t\t\t<div class=\"sn-spaces__popup-item_counter\">", "</div>\n\t\t\t\t\t<span class=\"\">", "</span>\n\t\t\t\t\t<div\n\t\t\t\t\t\tclass=\"ui-icon-set --chevron-right\"\n\t\t\t\t\t\tstyle=\"--ui-icon-set__icon-size: 14px;\"\n\t\t\t\t\t></div>\n\t\t\t\t</div>\n\t\t\t"])), inId, inCounter, main_core.Loc.getMessage('SN_SPACES_MENU_MEMBERS_RED_LABEL'));
	  };
	  var node = main_core.Tag.render(_templateObject6$1 || (_templateObject6$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div>\n\t\t\t\t", "\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"])), renderOut(), renderIn());
	  main_core.Event.bind(node.querySelector("[data-id='".concat(outId, "']")), 'click', function () {
	    return _this5.emit('showUsers', 'out');
	  });
	  main_core.Event.bind(node.querySelector("[data-id='".concat(inId, "']")), 'click', function () {
	    return _this5.emit('showUsers', 'in');
	  });
	  return node;
	}
	function _renderOwner2(member) {
	  var crownIconSrc = '/bitrix/components/bitrix/socialnetwork.spaces.menu/' + 'templates/.default/images/sn-spaces__popup-icon_super-admin.svg';
	  var uiClasses = member.photo ? '' : 'ui-icon ui-icon-common-user ui-icon-xs';
	  return main_core.Tag.render(_templateObject7 || (_templateObject7 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"sn-spaces__popup-item_list-item --super-admin ", "\">\n\t\t\t\t", "\n\t\t\t\t<img src=\"", "\" class=\"sn-spaces__popup-icon_svg\" alt=\"crown\">\n\t\t\t</div>\n\t\t"])), uiClasses, _classPrivateMethodGet$8(this, _renderAvatar, _renderAvatar2).call(this, member.photo), crownIconSrc);
	}
	function _renderModerator2(member) {
	  var crownIconSrc = '/bitrix/components/bitrix/socialnetwork.spaces.menu/' + 'templates/.default/images/sn-spaces__popup-icon_admin.svg';
	  var uiClasses = member.photo ? '' : 'ui-icon ui-icon-common-user ui-icon-xs';
	  return main_core.Tag.render(_templateObject8 || (_templateObject8 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"sn-spaces__popup-item_list-item --admin ", "\">\n\t\t\t\t", "\n\t\t\t\t<img src=\"", "\" class=\"sn-spaces__popup-icon_svg\" alt=\"crown\">\n\t\t\t</div>\n\t\t"])), uiClasses, _classPrivateMethodGet$8(this, _renderAvatar, _renderAvatar2).call(this, member.photo), crownIconSrc);
	}
	function _renderUser2(member) {
	  var uiClasses = member.photo ? '' : 'ui-icon ui-icon-common-user ui-icon-xs';
	  return main_core.Tag.render(_templateObject9 || (_templateObject9 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"sn-spaces__popup-item_list-item ", "\">\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"])), uiClasses, _classPrivateMethodGet$8(this, _renderAvatar, _renderAvatar2).call(this, member.photo));
	}
	function _renderAvatar2(photo) {
	  if (photo) {
	    return main_core.Tag.render(_templateObject10 || (_templateObject10 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<i\n\t\t\t\t\tclass=\"sn-spaces__popup-item_list-item-img\"\n\t\t\t\t\tstyle=\"background-image: url('", "');\"\n\t\t\t\t></i>\n\t\t\t"])), encodeURI(photo));
	  }
	  return main_core.Tag.render(_templateObject11 || (_templateObject11 = babelHelpers.taggedTemplateLiteral(["<i></i>"])));
	}
	function _prepareList2() {
	  var members = {
	    owner: null,
	    moderators: [],
	    users: []
	  };
	  babelHelpers.classPrivateFieldGet(this, _list).forEach(function (member) {
	    if (member.isOwner) {
	      members.owner = member;
	    } else if (member.isModerator) {
	      members.moderators.push(member);
	    } else {
	      members.users.push(member);
	    }
	  });
	  return members;
	}
	function _canInvite2() {
	  return babelHelpers.classPrivateFieldGet(this, _actions$1).canEdit || babelHelpers.classPrivateFieldGet(this, _actions$1).canInvite;
	}
	function _hasCounters2() {
	  var outCounter = parseInt(babelHelpers.classPrivateFieldGet(this, _counters).workgroup_requests_out, 10);
	  var inCounter = parseInt(babelHelpers.classPrivateFieldGet(this, _counters).workgroup_requests_in, 10);
	  return outCounter > 0 || inCounter > 0;
	}
	function _hasOutCounters2() {
	  return parseInt(babelHelpers.classPrivateFieldGet(this, _counters).workgroup_requests_out, 10) > 0;
	}
	function _hasInCounters2() {
	  return parseInt(babelHelpers.classPrivateFieldGet(this, _counters).workgroup_requests_in, 10) > 0;
	}

	var _templateObject$8;
	function _classPrivateMethodInitSpec$9(obj, privateSet) { _checkPrivateRedeclaration$b(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec$b(obj, privateMap, value) { _checkPrivateRedeclaration$b(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$b(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet$9(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _pin = /*#__PURE__*/new WeakMap();
	var _disabled$1 = /*#__PURE__*/new WeakMap();
	var _node$3 = /*#__PURE__*/new WeakMap();
	var _toggle$1 = /*#__PURE__*/new WeakSet();
	var _changeIcon$1 = /*#__PURE__*/new WeakSet();
	var _changeLabel$1 = /*#__PURE__*/new WeakSet();
	var _getLabel$1 = /*#__PURE__*/new WeakSet();
	var _bindEvents$1 = /*#__PURE__*/new WeakSet();
	var Pin = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(Pin, _EventEmitter);
	  function Pin(params) {
	    var _this;
	    babelHelpers.classCallCheck(this, Pin);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Pin).call(this));
	    _classPrivateMethodInitSpec$9(babelHelpers.assertThisInitialized(_this), _bindEvents$1);
	    _classPrivateMethodInitSpec$9(babelHelpers.assertThisInitialized(_this), _getLabel$1);
	    _classPrivateMethodInitSpec$9(babelHelpers.assertThisInitialized(_this), _changeLabel$1);
	    _classPrivateMethodInitSpec$9(babelHelpers.assertThisInitialized(_this), _changeIcon$1);
	    _classPrivateMethodInitSpec$9(babelHelpers.assertThisInitialized(_this), _toggle$1);
	    _classPrivateFieldInitSpec$b(babelHelpers.assertThisInitialized(_this), _pin, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$b(babelHelpers.assertThisInitialized(_this), _disabled$1, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$b(babelHelpers.assertThisInitialized(_this), _node$3, {
	      writable: true,
	      value: void 0
	    });
	    _this.setEventNamespace('BX.Socialnetwork.Spaces.Settings.Pin');
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _pin, params.pin);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _disabled$1, false);
	    _classPrivateMethodGet$9(babelHelpers.assertThisInitialized(_this), _bindEvents$1, _bindEvents2$1).call(babelHelpers.assertThisInitialized(_this));
	    return _this;
	  }
	  babelHelpers.createClass(Pin, [{
	    key: "render",
	    value: function render() {
	      var pinId = 'spaces-settings-pin';
	      var iconClass = babelHelpers.classPrivateFieldGet(this, _pin) ? '--pin-2' : '--pin-1';
	      babelHelpers.classPrivateFieldSet(this, _node$3, main_core.Tag.render(_templateObject$8 || (_templateObject$8 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div\n\t\t\t\tdata-id=\"", "\"\n\t\t\t\tclass=\"sn-spaces__popup-item --mini\"\n\t\t\t>\n\t\t\t\t<div class=\"sn-spaces__popup-icon-round\">\n\t\t\t\t\t<div\n\t\t\t\t\t\tclass=\"ui-icon-set ", "\"\n\t\t\t\t\t\tstyle=\"--ui-icon-set__icon-size: 22px;\"\n\t\t\t\t\t></div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"sn-spaces__popup-icon-round-name\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t"])), pinId, iconClass, _classPrivateMethodGet$9(this, _getLabel$1, _getLabel2$1).call(this, babelHelpers.classPrivateFieldGet(this, _pin))));
	      main_core.Event.bind(babelHelpers.classPrivateFieldGet(this, _node$3), 'click', _classPrivateMethodGet$9(this, _toggle$1, _toggle2$1).bind(this));
	      return babelHelpers.classPrivateFieldGet(this, _node$3);
	    }
	  }, {
	    key: "unDisable",
	    value: function unDisable() {
	      babelHelpers.classPrivateFieldSet(this, _disabled$1, false);
	    }
	  }]);
	  return Pin;
	}(main_core_events.EventEmitter);
	function _toggle2$1() {
	  if (babelHelpers.classPrivateFieldGet(this, _disabled$1)) {
	    return;
	  }
	  babelHelpers.classPrivateFieldSet(this, _disabled$1, true);
	  babelHelpers.classPrivateFieldSet(this, _pin, !babelHelpers.classPrivateFieldGet(this, _pin));
	  this.emit('update', babelHelpers.classPrivateFieldGet(this, _pin));
	  _classPrivateMethodGet$9(this, _changeIcon$1, _changeIcon2$1).call(this, babelHelpers.classPrivateFieldGet(this, _pin));
	  _classPrivateMethodGet$9(this, _changeLabel$1, _changeLabel2$1).call(this, babelHelpers.classPrivateFieldGet(this, _pin));
	}
	function _changeIcon2$1(pin) {
	  var iconNode = babelHelpers.classPrivateFieldGet(this, _node$3).querySelector('.ui-icon-set');
	  if (pin) {
	    main_core.Dom.removeClass(iconNode, '--pin-1');
	    main_core.Dom.addClass(iconNode, '--pin-2');
	  } else {
	    main_core.Dom.removeClass(iconNode, '--pin-2');
	    main_core.Dom.addClass(iconNode, '--pin-1');
	  }
	}
	function _changeLabel2$1(pin) {
	  var nameNode = babelHelpers.classPrivateFieldGet(this, _node$3).querySelector('.sn-spaces__popup-icon-round-name');
	  nameNode.textContent = _classPrivateMethodGet$9(this, _getLabel$1, _getLabel2$1).call(this, pin);
	}
	function _getLabel2$1(pin) {
	  return pin ? main_core.Loc.getMessage('SN_SPACES_MENU_PIN_N') : main_core.Loc.getMessage('SN_SPACES_MENU_PIN_Y');
	}
	function _bindEvents2$1() {
	  main_core_events.EventEmitter.subscribe('pinChanged', function (event) {
	    babelHelpers.classPrivateFieldSet(this, _pin, event.data.isPinned);
	    _classPrivateMethodGet$9(this, _changeLabel$1, _changeLabel2$1).call(this, babelHelpers.classPrivateFieldGet(this, _pin));
	    _classPrivateMethodGet$9(this, _changeIcon$1, _changeIcon2$1).call(this, babelHelpers.classPrivateFieldGet(this, _pin));
	  }.bind(this));
	}

	var _templateObject$9;
	var Roles = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(Roles, _EventEmitter);
	  function Roles() {
	    var _this;
	    babelHelpers.classCallCheck(this, Roles);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Roles).call(this));
	    _this.setEventNamespace('BX.Socialnetwork.Spaces.Settings.Roles');
	    return _this;
	  }
	  babelHelpers.createClass(Roles, [{
	    key: "render",
	    value: function render() {
	      var _this2 = this;
	      var rolesId = 'spaces-settings-roles';
	      var node = main_core.Tag.render(_templateObject$9 || (_templateObject$9 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div\n\t\t\t\tdata-id=\"", "\"\n\t\t\t\tclass=\"sn-spaces__popup-item --mini Roles&Rights\"\n\t\t\t>\n\t\t\t\t<div class=\"sn-spaces__popup-icon-round\">\n\t\t\t\t\t<div\n\t\t\t\t\t\tclass=\"ui-icon-set --crown-1\"\n\t\t\t\t\t\tstyle=\"--ui-icon-set__icon-size: 22px;\"\n\t\t\t\t\t></div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"sn-spaces__popup-icon-round-name\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t"])), rolesId, main_core.Loc.getMessage('SN_SPACES_MENU_ROLES'));
	      main_core.Event.bind(node, 'click', function () {
	        return _this2.emit('click');
	      });
	      return node;
	    }
	  }]);
	  return Roles;
	}(main_core_events.EventEmitter);

	function _classPrivateMethodInitSpec$a(obj, privateSet) { _checkPrivateRedeclaration$c(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec$c(obj, privateMap, value) { _checkPrivateRedeclaration$c(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$c(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet$a(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _cache = /*#__PURE__*/new WeakMap();
	var _menu = /*#__PURE__*/new WeakMap();
	var _layout = /*#__PURE__*/new WeakMap();
	var _setParams = /*#__PURE__*/new WeakSet();
	var _getParam = /*#__PURE__*/new WeakSet();
	var _createMenu = /*#__PURE__*/new WeakSet();
	var _renderInfo = /*#__PURE__*/new WeakSet();
	var _renderChat = /*#__PURE__*/new WeakSet();
	var _renderMembers = /*#__PURE__*/new WeakSet();
	var _renderFollow = /*#__PURE__*/new WeakSet();
	var _renderPin = /*#__PURE__*/new WeakSet();
	var _renderRoles = /*#__PURE__*/new WeakSet();
	var _changeSubscribe = /*#__PURE__*/new WeakSet();
	var _changePin = /*#__PURE__*/new WeakSet();
	var _changePrivacy$2 = /*#__PURE__*/new WeakSet();
	var _changeTitle = /*#__PURE__*/new WeakSet();
	var _consoleError = /*#__PURE__*/new WeakSet();
	var GroupSettings = /*#__PURE__*/function () {
	  function GroupSettings(_params) {
	    babelHelpers.classCallCheck(this, GroupSettings);
	    _classPrivateMethodInitSpec$a(this, _consoleError);
	    _classPrivateMethodInitSpec$a(this, _changeTitle);
	    _classPrivateMethodInitSpec$a(this, _changePrivacy$2);
	    _classPrivateMethodInitSpec$a(this, _changePin);
	    _classPrivateMethodInitSpec$a(this, _changeSubscribe);
	    _classPrivateMethodInitSpec$a(this, _renderRoles);
	    _classPrivateMethodInitSpec$a(this, _renderPin);
	    _classPrivateMethodInitSpec$a(this, _renderFollow);
	    _classPrivateMethodInitSpec$a(this, _renderMembers);
	    _classPrivateMethodInitSpec$a(this, _renderChat);
	    _classPrivateMethodInitSpec$a(this, _renderInfo);
	    _classPrivateMethodInitSpec$a(this, _createMenu);
	    _classPrivateMethodInitSpec$a(this, _getParam);
	    _classPrivateMethodInitSpec$a(this, _setParams);
	    _classPrivateFieldInitSpec$c(this, _cache, {
	      writable: true,
	      value: new main_core.Cache.MemoryCache()
	    });
	    _classPrivateFieldInitSpec$c(this, _menu, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$c(this, _layout, {
	      writable: true,
	      value: {
	        members: null
	      }
	    });
	    _classPrivateMethodGet$a(this, _setParams, _setParams2).call(this, _params);
	    babelHelpers.classPrivateFieldSet(this, _menu, _classPrivateMethodGet$a(this, _createMenu, _createMenu2).call(this));
	  }
	  babelHelpers.createClass(GroupSettings, [{
	    key: "show",
	    value: function show() {
	      if (babelHelpers.classPrivateFieldGet(this, _menu).isShown()) {
	        babelHelpers.classPrivateFieldGet(this, _menu).close();
	      } else {
	        babelHelpers.classPrivateFieldGet(this, _menu).show();
	      }
	    }
	  }, {
	    key: "update",
	    value: function update(groupDataPromise) {
	      _classPrivateMethodGet$a(this, _renderMembers, _renderMembers2).call(this, groupDataPromise);
	    }
	  }]);
	  return GroupSettings;
	}();
	function _setParams2(params) {
	  babelHelpers.classPrivateFieldGet(this, _cache).set('params', params);
	}
	function _getParam2(param) {
	  return babelHelpers.classPrivateFieldGet(this, _cache).get('params')[param];
	}
	function _createMenu2() {
	  var groupDataPromise = MenuAjax.getGroupData(_classPrivateMethodGet$a(this, _getParam, _getParam2).call(this, 'groupId'));
	  return new ui_popupcomponentsmaker.PopupComponentsMaker({
	    id: 'spaces-settings-menu',
	    target: _classPrivateMethodGet$a(this, _getParam, _getParam2).call(this, 'bindElement'),
	    content: [{
	      html: [{
	        html: _classPrivateMethodGet$a(this, _renderInfo, _renderInfo2).call(this, groupDataPromise),
	        backgroundColor: '#fafafa'
	      }]
	    }, {
	      html: [{
	        html: _classPrivateMethodGet$a(this, _renderChat, _renderChat2).call(this),
	        withoutBackground: true
	      }]
	    }, {
	      html: [{
	        html: _classPrivateMethodGet$a(this, _renderMembers, _renderMembers2).call(this, groupDataPromise),
	        withoutBackground: true
	      }]
	    }, {
	      html: [{
	        html: _classPrivateMethodGet$a(this, _renderFollow, _renderFollow2).call(this, groupDataPromise),
	        backgroundColor: '#fafafa'
	      }, {
	        html: _classPrivateMethodGet$a(this, _renderPin, _renderPin2).call(this, groupDataPromise),
	        backgroundColor: '#fafafa'
	      }, {
	        html: _classPrivateMethodGet$a(this, _renderRoles, _renderRoles2).call(this),
	        backgroundColor: '#fafafa'
	      }]
	    }]
	  });
	}
	function _renderInfo2(groupDataPromise) {
	  var _this = this;
	  return new Promise(function (resolve) {
	    // eslint-disable-next-line promise/catch-or-return
	    groupDataPromise.then(function (groupData) {
	      var info = new Info({
	        title: groupData.name,
	        logo: new Logo(_classPrivateMethodGet$a(_this, _getParam, _getParam2).call(_this, 'logo')),
	        privacyCode: groupData.privacyCode,
	        actions: groupData.actions
	      });
	      info.subscribe('changePrivacy', _classPrivateMethodGet$a(_this, _changePrivacy$2, _changePrivacy2$2).bind(_this));
	      info.subscribe('changeTitle', _classPrivateMethodGet$a(_this, _changeTitle, _changeTitle2).bind(_this));
	      info.subscribe('setAutoHide', function (baseEvent) {
	        babelHelpers.classPrivateFieldGet(_this, _menu).getPopup().setAutoHide(baseEvent.getData());
	      });
	      info.subscribe('more', function () {
	        console.log('more');
	      });
	      resolve(info.render());
	    });
	  });
	}
	function _renderChat2() {
	  var _this2 = this;
	  var chat = new ChatAction();
	  chat.subscribe('videoCall', function () {
	    babelHelpers.classPrivateFieldGet(_this2, _menu).close();
	    _classPrivateMethodGet$a(_this2, _getParam, _getParam2).call(_this2, 'chat').startVideoCall();
	  });
	  chat.subscribe('openChat', function () {
	    babelHelpers.classPrivateFieldGet(_this2, _menu).close();
	    _classPrivateMethodGet$a(_this2, _getParam, _getParam2).call(_this2, 'chat').openChat();
	  });
	  chat.subscribe('createChat', function () {
	    babelHelpers.classPrivateFieldGet(_this2, _menu).close();
	    _classPrivateMethodGet$a(_this2, _getParam, _getParam2).call(_this2, 'chat').createChat(_classPrivateMethodGet$a(_this2, _getParam, _getParam2).call(_this2, 'bindElement'));
	  });
	  return chat.render();
	}
	function _renderMembers2(groupDataPromise) {
	  var _this3 = this;
	  return new Promise(function (resolve) {
	    // eslint-disable-next-line promise/catch-or-return
	    groupDataPromise.then(function (groupData) {
	      var members = new Members({
	        amount: groupData.numberOfMembers,
	        list: groupData.listOfMembers,
	        counters: groupData.counters,
	        actions: groupData.actions
	      });
	      members.subscribe('showUsers', function (baseEvent) {
	        babelHelpers.classPrivateFieldGet(_this3, _menu).close();
	        _classPrivateMethodGet$a(_this3, _getParam, _getParam2).call(_this3, 'router').openGroupUsers(baseEvent.getData());
	      });
	      members.subscribe('invite', function () {
	        babelHelpers.classPrivateFieldGet(_this3, _menu).close();
	        _classPrivateMethodGet$a(_this3, _getParam, _getParam2).call(_this3, 'router').openGroupInvite();
	      });
	      var layoutMembers = members.render();
	      if (babelHelpers.classPrivateFieldGet(_this3, _layout).members) {
	        babelHelpers.classPrivateFieldGet(_this3, _layout).members.replaceWith(layoutMembers);
	      }
	      babelHelpers.classPrivateFieldGet(_this3, _layout).members = layoutMembers;
	      resolve(babelHelpers.classPrivateFieldGet(_this3, _layout).members);
	    });
	  });
	}
	function _renderFollow2(groupDataPromise) {
	  var _this4 = this;
	  return new Promise(function (resolve) {
	    // eslint-disable-next-line promise/catch-or-return
	    groupDataPromise.then(function (groupData) {
	      var follow = new Follow({
	        follow: groupData.isSubscribed
	      });
	      follow.subscribe('update', function (baseEvent) {
	        _classPrivateMethodGet$a(_this4, _changeSubscribe, _changeSubscribe2).call(_this4, _classPrivateMethodGet$a(_this4, _getParam, _getParam2).call(_this4, 'groupId'), baseEvent.getData() === true ? 'Y' : 'N', follow);
	      });
	      resolve(follow.render());
	    });
	  });
	}
	function _renderPin2(groupDataPromise) {
	  var _this5 = this;
	  return new Promise(function (resolve) {
	    // eslint-disable-next-line promise/catch-or-return
	    groupDataPromise.then(function (groupData) {
	      var pin = new Pin({
	        pin: groupData.isPin
	      });
	      pin.subscribe('update', function (baseEvent) {
	        _classPrivateMethodGet$a(_this5, _changePin, _changePin2).call(_this5, _classPrivateMethodGet$a(_this5, _getParam, _getParam2).call(_this5, 'groupId'), baseEvent.getData() === true ? 'pin' : 'unpin', pin);
	      });
	      resolve(pin.render());
	    });
	  });
	}
	function _renderRoles2() {
	  var _this6 = this;
	  var roles = new Roles();
	  roles.subscribe('click', function () {
	    babelHelpers.classPrivateFieldGet(_this6, _menu).close();
	    _classPrivateMethodGet$a(_this6, _getParam, _getParam2).call(_this6, 'router').openGroupFeatures();
	  });
	  return roles.render();
	}
	function _changeSubscribe2(groupId, value, follow) {
	  var _this7 = this;
	  // eslint-disable-next-line promise/catch-or-return
	  main_core.ajax.runAction('socialnetwork.api.workgroup.setSubscription', {
	    data: {
	      params: {
	        groupId: groupId,
	        value: value
	      }
	    }
	  }).then(function (response) {
	    follow.unDisable();
	  })["catch"](function (error) {
	    follow.unDisable();
	    _classPrivateMethodGet$a(_this7, _consoleError, _consoleError2).call(_this7, 'changeSubscribe', error);
	  });
	}
	function _changePin2(groupId, action, pin) {
	  var _this8 = this;
	  // eslint-disable-next-line promise/catch-or-return
	  main_core.ajax.runAction('socialnetwork.api.workgroup.changePin', {
	    data: {
	      groupIdList: [groupId],
	      action: action
	    }
	  }).then(function (response) {
	    pin.unDisable();
	  })["catch"](function (error) {
	    pin.unDisable();
	    _classPrivateMethodGet$a(_this8, _consoleError, _consoleError2).call(_this8, 'changePin', error);
	  });
	}
	function _changePrivacy2$2(baseEvent) {
	  var _this9 = this;
	  var privacyCode = baseEvent.getData();
	  var fields = {};
	  if (privacyCode === 'open') {
	    fields.VISIBLE = 'Y';
	    fields.OPENED = 'Y';
	    fields.EXTERNAL = 'N';
	  }
	  if (privacyCode === 'closed') {
	    fields.VISIBLE = 'Y';
	    fields.OPENED = 'N';
	    fields.EXTERNAL = 'N';
	  }
	  if (privacyCode === 'secret') {
	    fields.VISIBLE = 'N';
	    fields.OPENED = 'N';
	    fields.EXTERNAL = 'N';
	  }

	  // eslint-disable-next-line promise/catch-or-return
	  main_core.ajax.runAction('socialnetwork.api.workgroup.update', {
	    data: {
	      groupId: _classPrivateMethodGet$a(this, _getParam, _getParam2).call(this, 'groupId'),
	      fields: fields
	    }
	  }).then(function (response) {})["catch"](function (error) {
	    _classPrivateMethodGet$a(_this9, _consoleError, _consoleError2).call(_this9, 'changePrivacy', error);
	  });
	}
	function _changeTitle2(baseEvent) {
	  var _this10 = this;
	  // eslint-disable-next-line promise/catch-or-return
	  main_core.ajax.runAction('socialnetwork.api.workgroup.update', {
	    data: {
	      groupId: _classPrivateMethodGet$a(this, _getParam, _getParam2).call(this, 'groupId'),
	      fields: {
	        NAME: baseEvent.getData()
	      }
	    }
	  }).then(function (response) {})["catch"](function (error) {
	    _classPrivateMethodGet$a(_this10, _consoleError, _consoleError2).call(_this10, 'changeTitle', error);
	  });
	}
	function _consoleError2(action, error) {
	  // eslint-disable-next-line no-console
	  console.error("GroupSettings: ".concat(action, " error"), error);
	}

	function _classPrivateMethodInitSpec$b(obj, privateSet) { _checkPrivateRedeclaration$d(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec$d(obj, privateMap, value) { _checkPrivateRedeclaration$d(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$d(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet$b(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _cache$1 = /*#__PURE__*/new WeakMap();
	var _menu$1 = /*#__PURE__*/new WeakMap();
	var _setParams$1 = /*#__PURE__*/new WeakSet();
	var _getParam$1 = /*#__PURE__*/new WeakSet();
	var UserSettings = /*#__PURE__*/function () {
	  function UserSettings(_params) {
	    babelHelpers.classCallCheck(this, UserSettings);
	    _classPrivateMethodInitSpec$b(this, _getParam$1);
	    _classPrivateMethodInitSpec$b(this, _setParams$1);
	    _classPrivateFieldInitSpec$d(this, _cache$1, {
	      writable: true,
	      value: new main_core.Cache.MemoryCache()
	    });
	    _classPrivateFieldInitSpec$d(this, _menu$1, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateMethodGet$b(this, _setParams$1, _setParams2$1).call(this, _params);
	  }
	  babelHelpers.createClass(UserSettings, [{
	    key: "show",
	    value: function show() {}
	  }]);
	  return UserSettings;
	}();
	function _setParams2$1(params) {
	  babelHelpers.classPrivateFieldGet(this, _cache$1).set('params', params);
	}

	function _classPrivateMethodInitSpec$c(obj, privateSet) { _checkPrivateRedeclaration$e(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec$e(obj, privateMap, value) { _checkPrivateRedeclaration$e(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$e(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet$c(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _cache$2 = /*#__PURE__*/new WeakMap();
	var _settings = /*#__PURE__*/new WeakMap();
	var _setParams$2 = /*#__PURE__*/new WeakSet();
	var _getParam$2 = /*#__PURE__*/new WeakSet();
	var Settings = /*#__PURE__*/function () {
	  function Settings(_params) {
	    babelHelpers.classCallCheck(this, Settings);
	    _classPrivateMethodInitSpec$c(this, _getParam$2);
	    _classPrivateMethodInitSpec$c(this, _setParams$2);
	    _classPrivateFieldInitSpec$e(this, _cache$2, {
	      writable: true,
	      value: new main_core.Cache.MemoryCache()
	    });
	    _classPrivateFieldInitSpec$e(this, _settings, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateMethodGet$c(this, _setParams$2, _setParams2$2).call(this, _params);
	  }
	  babelHelpers.createClass(Settings, [{
	    key: "show",
	    value: function show() {
	      if (!babelHelpers.classPrivateFieldGet(this, _settings)) {
	        if (_classPrivateMethodGet$c(this, _getParam$2, _getParam2$2).call(this, 'type') === 'user') {
	          babelHelpers.classPrivateFieldSet(this, _settings, new UserSettings({
	            bindElement: _classPrivateMethodGet$c(this, _getParam$2, _getParam2$2).call(this, 'bindElement'),
	            userId: _classPrivateMethodGet$c(this, _getParam$2, _getParam2$2).call(this, 'entityId')
	          }));
	        } else {
	          babelHelpers.classPrivateFieldSet(this, _settings, new GroupSettings({
	            bindElement: _classPrivateMethodGet$c(this, _getParam$2, _getParam2$2).call(this, 'bindElement'),
	            groupId: _classPrivateMethodGet$c(this, _getParam$2, _getParam2$2).call(this, 'entityId'),
	            logo: _classPrivateMethodGet$c(this, _getParam$2, _getParam2$2).call(this, 'logo'),
	            chat: _classPrivateMethodGet$c(this, _getParam$2, _getParam2$2).call(this, 'chat'),
	            router: _classPrivateMethodGet$c(this, _getParam$2, _getParam2$2).call(this, 'router')
	          }));
	        }
	      }
	      babelHelpers.classPrivateFieldGet(this, _settings).show();
	    }
	  }, {
	    key: "update",
	    value: function update(groupDataPromise) {
	      var _babelHelpers$classPr;
	      (_babelHelpers$classPr = babelHelpers.classPrivateFieldGet(this, _settings)) === null || _babelHelpers$classPr === void 0 ? void 0 : _babelHelpers$classPr.update(groupDataPromise);
	    }
	  }]);
	  return Settings;
	}();
	function _setParams2$2(params) {
	  babelHelpers.classPrivateFieldGet(this, _cache$2).set('params', params);
	}
	function _getParam2$2(param) {
	  return babelHelpers.classPrivateFieldGet(this, _cache$2).get('params')[param];
	}

	function _classPrivateMethodInitSpec$d(obj, privateSet) { _checkPrivateRedeclaration$f(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec$f(obj, privateMap, value) { _checkPrivateRedeclaration$f(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$f(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet$d(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _menu$2 = /*#__PURE__*/new WeakMap();
	var _createMenu$1 = /*#__PURE__*/new WeakSet();
	var VideoCall = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(VideoCall, _EventEmitter);
	  function VideoCall(params) {
	    var _this;
	    babelHelpers.classCallCheck(this, VideoCall);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(VideoCall).call(this));
	    _classPrivateMethodInitSpec$d(babelHelpers.assertThisInitialized(_this), _createMenu$1);
	    _classPrivateFieldInitSpec$f(babelHelpers.assertThisInitialized(_this), _menu$2, {
	      writable: true,
	      value: void 0
	    });
	    _this.setEventNamespace('BX.Socialnetwork.Spaces.VideoCall');
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _menu$2, _classPrivateMethodGet$d(babelHelpers.assertThisInitialized(_this), _createMenu$1, _createMenu2$1).call(babelHelpers.assertThisInitialized(_this), params.bindElement));
	    return _this;
	  }
	  babelHelpers.createClass(VideoCall, [{
	    key: "show",
	    value: function show() {
	      babelHelpers.classPrivateFieldGet(this, _menu$2).toggle();
	    }
	  }]);
	  return VideoCall;
	}(main_core_events.EventEmitter);
	function _createMenu2$1(bindElement) {
	  var _this2 = this;
	  var menu = new main_popup.Menu({
	    id: 'spaces-video-call-menu',
	    bindElement: bindElement,
	    closeByEsc: true,
	    angle: {
	      position: 'top',
	      offset: 31
	    }
	  });
	  menu.addMenuItem({
	    dataset: {
	      id: 'spaces-menu-video-call-hd'
	    },
	    text: main_core.Loc.getMessage('SN_SPACES_MENU_VIDEO_CALL_HD'),
	    className: 'sn-spaces-menu-video-call-hd-icon',
	    onclick: function onclick() {
	      _this2.emit('hd');
	      menu.close();
	    }
	  });
	  menu.addMenuItem({
	    delimiter: true
	  });
	  menu.addMenuItem({
	    dataset: {
	      id: 'spaces-menu-video-call-chat'
	    },
	    text: main_core.Loc.getMessage('SN_SPACES_MENU_VIDEO_CALL_CHAT'),
	    className: 'sn-spaces-menu-video-call-chat-icon',
	    onclick: function onclick() {
	      _this2.emit('chat');
	      menu.close();
	    }
	  });
	  menu.addMenuItem({
	    dataset: {
	      id: 'spaces-menu-create-chat'
	    },
	    text: main_core.Loc.getMessage('SN_SPACES_MENU_CREATE_CHAT'),
	    className: 'sn-spaces-menu-create-chat-icon',
	    onclick: function onclick() {
	      _this2.emit('createChat');
	      menu.close();
	    }
	  });
	  return menu;
	}

	var _templateObject$a, _templateObject2$2, _templateObject3$2, _templateObject4$2, _templateObject5$2, _templateObject6$2, _templateObject7$1, _templateObject8$1;
	function _regeneratorRuntime$1() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/facebook/regenerator/blob/main/LICENSE */ _regeneratorRuntime$1 = function _regeneratorRuntime() { return exports; }; var exports = {}, Op = Object.prototype, hasOwn = Op.hasOwnProperty, defineProperty = Object.defineProperty || function (obj, key, desc) { obj[key] = desc.value; }, $Symbol = "function" == typeof Symbol ? Symbol : {}, iteratorSymbol = $Symbol.iterator || "@@iterator", asyncIteratorSymbol = $Symbol.asyncIterator || "@@asyncIterator", toStringTagSymbol = $Symbol.toStringTag || "@@toStringTag"; function define(obj, key, value) { return Object.defineProperty(obj, key, { value: value, enumerable: !0, configurable: !0, writable: !0 }), obj[key]; } try { define({}, ""); } catch (err) { define = function define(obj, key, value) { return obj[key] = value; }; } function wrap(innerFn, outerFn, self, tryLocsList) { var protoGenerator = outerFn && outerFn.prototype instanceof Generator ? outerFn : Generator, generator = Object.create(protoGenerator.prototype), context = new Context(tryLocsList || []); return defineProperty(generator, "_invoke", { value: makeInvokeMethod(innerFn, self, context) }), generator; } function tryCatch(fn, obj, arg) { try { return { type: "normal", arg: fn.call(obj, arg) }; } catch (err) { return { type: "throw", arg: err }; } } exports.wrap = wrap; var ContinueSentinel = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} var IteratorPrototype = {}; define(IteratorPrototype, iteratorSymbol, function () { return this; }); var getProto = Object.getPrototypeOf, NativeIteratorPrototype = getProto && getProto(getProto(values([]))); NativeIteratorPrototype && NativeIteratorPrototype !== Op && hasOwn.call(NativeIteratorPrototype, iteratorSymbol) && (IteratorPrototype = NativeIteratorPrototype); var Gp = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(IteratorPrototype); function defineIteratorMethods(prototype) { ["next", "throw", "return"].forEach(function (method) { define(prototype, method, function (arg) { return this._invoke(method, arg); }); }); } function AsyncIterator(generator, PromiseImpl) { function invoke(method, arg, resolve, reject) { var record = tryCatch(generator[method], generator, arg); if ("throw" !== record.type) { var result = record.arg, value = result.value; return value && "object" == babelHelpers["typeof"](value) && hasOwn.call(value, "__await") ? PromiseImpl.resolve(value.__await).then(function (value) { invoke("next", value, resolve, reject); }, function (err) { invoke("throw", err, resolve, reject); }) : PromiseImpl.resolve(value).then(function (unwrapped) { result.value = unwrapped, resolve(result); }, function (error) { return invoke("throw", error, resolve, reject); }); } reject(record.arg); } var previousPromise; defineProperty(this, "_invoke", { value: function value(method, arg) { function callInvokeWithMethodAndArg() { return new PromiseImpl(function (resolve, reject) { invoke(method, arg, resolve, reject); }); } return previousPromise = previousPromise ? previousPromise.then(callInvokeWithMethodAndArg, callInvokeWithMethodAndArg) : callInvokeWithMethodAndArg(); } }); } function makeInvokeMethod(innerFn, self, context) { var state = "suspendedStart"; return function (method, arg) { if ("executing" === state) throw new Error("Generator is already running"); if ("completed" === state) { if ("throw" === method) throw arg; return doneResult(); } for (context.method = method, context.arg = arg;;) { var delegate = context.delegate; if (delegate) { var delegateResult = maybeInvokeDelegate(delegate, context); if (delegateResult) { if (delegateResult === ContinueSentinel) continue; return delegateResult; } } if ("next" === context.method) context.sent = context._sent = context.arg;else if ("throw" === context.method) { if ("suspendedStart" === state) throw state = "completed", context.arg; context.dispatchException(context.arg); } else "return" === context.method && context.abrupt("return", context.arg); state = "executing"; var record = tryCatch(innerFn, self, context); if ("normal" === record.type) { if (state = context.done ? "completed" : "suspendedYield", record.arg === ContinueSentinel) continue; return { value: record.arg, done: context.done }; } "throw" === record.type && (state = "completed", context.method = "throw", context.arg = record.arg); } }; } function maybeInvokeDelegate(delegate, context) { var methodName = context.method, method = delegate.iterator[methodName]; if (undefined === method) return context.delegate = null, "throw" === methodName && delegate.iterator["return"] && (context.method = "return", context.arg = undefined, maybeInvokeDelegate(delegate, context), "throw" === context.method) || "return" !== methodName && (context.method = "throw", context.arg = new TypeError("The iterator does not provide a '" + methodName + "' method")), ContinueSentinel; var record = tryCatch(method, delegate.iterator, context.arg); if ("throw" === record.type) return context.method = "throw", context.arg = record.arg, context.delegate = null, ContinueSentinel; var info = record.arg; return info ? info.done ? (context[delegate.resultName] = info.value, context.next = delegate.nextLoc, "return" !== context.method && (context.method = "next", context.arg = undefined), context.delegate = null, ContinueSentinel) : info : (context.method = "throw", context.arg = new TypeError("iterator result is not an object"), context.delegate = null, ContinueSentinel); } function pushTryEntry(locs) { var entry = { tryLoc: locs[0] }; 1 in locs && (entry.catchLoc = locs[1]), 2 in locs && (entry.finallyLoc = locs[2], entry.afterLoc = locs[3]), this.tryEntries.push(entry); } function resetTryEntry(entry) { var record = entry.completion || {}; record.type = "normal", delete record.arg, entry.completion = record; } function Context(tryLocsList) { this.tryEntries = [{ tryLoc: "root" }], tryLocsList.forEach(pushTryEntry, this), this.reset(!0); } function values(iterable) { if (iterable) { var iteratorMethod = iterable[iteratorSymbol]; if (iteratorMethod) return iteratorMethod.call(iterable); if ("function" == typeof iterable.next) return iterable; if (!isNaN(iterable.length)) { var i = -1, next = function next() { for (; ++i < iterable.length;) if (hasOwn.call(iterable, i)) return next.value = iterable[i], next.done = !1, next; return next.value = undefined, next.done = !0, next; }; return next.next = next; } } return { next: doneResult }; } function doneResult() { return { value: undefined, done: !0 }; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, defineProperty(Gp, "constructor", { value: GeneratorFunctionPrototype, configurable: !0 }), defineProperty(GeneratorFunctionPrototype, "constructor", { value: GeneratorFunction, configurable: !0 }), GeneratorFunction.displayName = define(GeneratorFunctionPrototype, toStringTagSymbol, "GeneratorFunction"), exports.isGeneratorFunction = function (genFun) { var ctor = "function" == typeof genFun && genFun.constructor; return !!ctor && (ctor === GeneratorFunction || "GeneratorFunction" === (ctor.displayName || ctor.name)); }, exports.mark = function (genFun) { return Object.setPrototypeOf ? Object.setPrototypeOf(genFun, GeneratorFunctionPrototype) : (genFun.__proto__ = GeneratorFunctionPrototype, define(genFun, toStringTagSymbol, "GeneratorFunction")), genFun.prototype = Object.create(Gp), genFun; }, exports.awrap = function (arg) { return { __await: arg }; }, defineIteratorMethods(AsyncIterator.prototype), define(AsyncIterator.prototype, asyncIteratorSymbol, function () { return this; }), exports.AsyncIterator = AsyncIterator, exports.async = function (innerFn, outerFn, self, tryLocsList, PromiseImpl) { void 0 === PromiseImpl && (PromiseImpl = Promise); var iter = new AsyncIterator(wrap(innerFn, outerFn, self, tryLocsList), PromiseImpl); return exports.isGeneratorFunction(outerFn) ? iter : iter.next().then(function (result) { return result.done ? result.value : iter.next(); }); }, defineIteratorMethods(Gp), define(Gp, toStringTagSymbol, "Generator"), define(Gp, iteratorSymbol, function () { return this; }), define(Gp, "toString", function () { return "[object Generator]"; }), exports.keys = function (val) { var object = Object(val), keys = []; for (var key in object) keys.push(key); return keys.reverse(), function next() { for (; keys.length;) { var key = keys.pop(); if (key in object) return next.value = key, next.done = !1, next; } return next.done = !0, next; }; }, exports.values = values, Context.prototype = { constructor: Context, reset: function reset(skipTempReset) { if (this.prev = 0, this.next = 0, this.sent = this._sent = undefined, this.done = !1, this.delegate = null, this.method = "next", this.arg = undefined, this.tryEntries.forEach(resetTryEntry), !skipTempReset) for (var name in this) "t" === name.charAt(0) && hasOwn.call(this, name) && !isNaN(+name.slice(1)) && (this[name] = undefined); }, stop: function stop() { this.done = !0; var rootRecord = this.tryEntries[0].completion; if ("throw" === rootRecord.type) throw rootRecord.arg; return this.rval; }, dispatchException: function dispatchException(exception) { if (this.done) throw exception; var context = this; function handle(loc, caught) { return record.type = "throw", record.arg = exception, context.next = loc, caught && (context.method = "next", context.arg = undefined), !!caught; } for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i], record = entry.completion; if ("root" === entry.tryLoc) return handle("end"); if (entry.tryLoc <= this.prev) { var hasCatch = hasOwn.call(entry, "catchLoc"), hasFinally = hasOwn.call(entry, "finallyLoc"); if (hasCatch && hasFinally) { if (this.prev < entry.catchLoc) return handle(entry.catchLoc, !0); if (this.prev < entry.finallyLoc) return handle(entry.finallyLoc); } else if (hasCatch) { if (this.prev < entry.catchLoc) return handle(entry.catchLoc, !0); } else { if (!hasFinally) throw new Error("try statement without catch or finally"); if (this.prev < entry.finallyLoc) return handle(entry.finallyLoc); } } } }, abrupt: function abrupt(type, arg) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.tryLoc <= this.prev && hasOwn.call(entry, "finallyLoc") && this.prev < entry.finallyLoc) { var finallyEntry = entry; break; } } finallyEntry && ("break" === type || "continue" === type) && finallyEntry.tryLoc <= arg && arg <= finallyEntry.finallyLoc && (finallyEntry = null); var record = finallyEntry ? finallyEntry.completion : {}; return record.type = type, record.arg = arg, finallyEntry ? (this.method = "next", this.next = finallyEntry.finallyLoc, ContinueSentinel) : this.complete(record); }, complete: function complete(record, afterLoc) { if ("throw" === record.type) throw record.arg; return "break" === record.type || "continue" === record.type ? this.next = record.arg : "return" === record.type ? (this.rval = this.arg = record.arg, this.method = "return", this.next = "end") : "normal" === record.type && afterLoc && (this.next = afterLoc), ContinueSentinel; }, finish: function finish(finallyLoc) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.finallyLoc === finallyLoc) return this.complete(entry.completion, entry.afterLoc), resetTryEntry(entry), ContinueSentinel; } }, "catch": function _catch(tryLoc) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.tryLoc === tryLoc) { var record = entry.completion; if ("throw" === record.type) { var thrown = record.arg; resetTryEntry(entry); } return thrown; } } throw new Error("illegal catch attempt"); }, delegateYield: function delegateYield(iterable, resultName, nextLoc) { return this.delegate = { iterator: values(iterable), resultName: resultName, nextLoc: nextLoc }, "next" === this.method && (this.arg = undefined), ContinueSentinel; } }, exports; }
	function _classPrivateMethodInitSpec$e(obj, privateSet) { _checkPrivateRedeclaration$g(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec$g(obj, privateMap, value) { _checkPrivateRedeclaration$g(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$g(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet$e(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _cache$3 = /*#__PURE__*/new WeakMap();
	var _videoCall = /*#__PURE__*/new WeakMap();
	var _scrumMeetings = /*#__PURE__*/new WeakMap();
	var _scrumMethodology = /*#__PURE__*/new WeakMap();
	var _invite = /*#__PURE__*/new WeakMap();
	var _inviteNode = /*#__PURE__*/new WeakMap();
	var _settings$1 = /*#__PURE__*/new WeakMap();
	var _chat = /*#__PURE__*/new WeakMap();
	var _router = /*#__PURE__*/new WeakMap();
	var _discussionAhaMomentShown = /*#__PURE__*/new WeakMap();
	var _groupInvitedList = /*#__PURE__*/new WeakMap();
	var _subscribeToPull = /*#__PURE__*/new WeakSet();
	var _update$1 = /*#__PURE__*/new WeakSet();
	var _setParams$3 = /*#__PURE__*/new WeakSet();
	var _initServices = /*#__PURE__*/new WeakSet();
	var _getParam$3 = /*#__PURE__*/new WeakSet();
	var _renderVideoCall = /*#__PURE__*/new WeakSet();
	var _renderScrumVideoCall = /*#__PURE__*/new WeakSet();
	var _renderScrumElements = /*#__PURE__*/new WeakSet();
	var _renderUserVideoCall = /*#__PURE__*/new WeakSet();
	var _renderInvite = /*#__PURE__*/new WeakSet();
	var _renderSettings = /*#__PURE__*/new WeakSet();
	var _renderUserSettings = /*#__PURE__*/new WeakSet();
	var _videoCallClick = /*#__PURE__*/new WeakSet();
	var _scrumVideoCallClick = /*#__PURE__*/new WeakSet();
	var _scrumElementsClick = /*#__PURE__*/new WeakSet();
	var _userVideoCallClick = /*#__PURE__*/new WeakSet();
	var _inviteClick = /*#__PURE__*/new WeakSet();
	var _getInvite = /*#__PURE__*/new WeakSet();
	var _onUsersSelected = /*#__PURE__*/new WeakSet();
	var _onInviteClose = /*#__PURE__*/new WeakSet();
	var _showSpotlight = /*#__PURE__*/new WeakSet();
	var _showAhaMoment = /*#__PURE__*/new WeakSet();
	var _inviteUsers = /*#__PURE__*/new WeakSet();
	var _getInvitationMessage = /*#__PURE__*/new WeakSet();
	var _settingsClick = /*#__PURE__*/new WeakSet();
	var _userSettingsClick = /*#__PURE__*/new WeakSet();
	var Menu = /*#__PURE__*/function () {
	  function Menu(_params) {
	    babelHelpers.classCallCheck(this, Menu);
	    _classPrivateMethodInitSpec$e(this, _userSettingsClick);
	    _classPrivateMethodInitSpec$e(this, _settingsClick);
	    _classPrivateMethodInitSpec$e(this, _getInvitationMessage);
	    _classPrivateMethodInitSpec$e(this, _inviteUsers);
	    _classPrivateMethodInitSpec$e(this, _showAhaMoment);
	    _classPrivateMethodInitSpec$e(this, _showSpotlight);
	    _classPrivateMethodInitSpec$e(this, _onInviteClose);
	    _classPrivateMethodInitSpec$e(this, _onUsersSelected);
	    _classPrivateMethodInitSpec$e(this, _getInvite);
	    _classPrivateMethodInitSpec$e(this, _inviteClick);
	    _classPrivateMethodInitSpec$e(this, _userVideoCallClick);
	    _classPrivateMethodInitSpec$e(this, _scrumElementsClick);
	    _classPrivateMethodInitSpec$e(this, _scrumVideoCallClick);
	    _classPrivateMethodInitSpec$e(this, _videoCallClick);
	    _classPrivateMethodInitSpec$e(this, _renderUserSettings);
	    _classPrivateMethodInitSpec$e(this, _renderSettings);
	    _classPrivateMethodInitSpec$e(this, _renderInvite);
	    _classPrivateMethodInitSpec$e(this, _renderUserVideoCall);
	    _classPrivateMethodInitSpec$e(this, _renderScrumElements);
	    _classPrivateMethodInitSpec$e(this, _renderScrumVideoCall);
	    _classPrivateMethodInitSpec$e(this, _renderVideoCall);
	    _classPrivateMethodInitSpec$e(this, _getParam$3);
	    _classPrivateMethodInitSpec$e(this, _initServices);
	    _classPrivateMethodInitSpec$e(this, _setParams$3);
	    _classPrivateMethodInitSpec$e(this, _update$1);
	    _classPrivateMethodInitSpec$e(this, _subscribeToPull);
	    _classPrivateFieldInitSpec$g(this, _cache$3, {
	      writable: true,
	      value: new main_core.Cache.MemoryCache()
	    });
	    _classPrivateFieldInitSpec$g(this, _videoCall, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$g(this, _scrumMeetings, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$g(this, _scrumMethodology, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$g(this, _invite, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$g(this, _inviteNode, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$g(this, _settings$1, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$g(this, _chat, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$g(this, _router, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$g(this, _discussionAhaMomentShown, {
	      writable: true,
	      value: false
	    });
	    _classPrivateFieldInitSpec$g(this, _groupInvitedList, {
	      writable: true,
	      value: []
	    });
	    _classPrivateMethodGet$e(this, _setParams$3, _setParams2$3).call(this, _params);
	    _classPrivateMethodGet$e(this, _initServices, _initServices2).call(this, _params);
	    _classPrivateMethodGet$e(this, _subscribeToPull, _subscribeToPull2).call(this);
	  }
	  babelHelpers.createClass(Menu, [{
	    key: "renderLogoTo",
	    value: function renderLogoTo(container) {
	      if (!main_core.Type.isDomNode(container)) {
	        throw new Error('BX.Socialnetwork.Spaces.Menu: HTMLElement for space not found');
	      }
	      var logo = new Logo(_classPrivateMethodGet$e(this, _getParam$3, _getParam2$3).call(this, 'logo'));
	      var logoClass = logo.getClass();
	      if (logoClass) {
	        main_core.Dom.addClass(container, logoClass);
	      }
	      main_core.Dom.append(logo.render(), container);
	    }
	  }, {
	    key: "renderUserLogoTo",
	    value: function renderUserLogoTo(container) {
	      if (!main_core.Type.isDomNode(container)) {
	        throw new Error('BX.Socialnetwork.Spaces.Menu: HTMLElement for space not found');
	      }
	      main_core.Dom.addClass(container, ['sonet-common-workgroup-avatar', '--common-space']);
	      main_core.Dom.append(main_core.Tag.render(_templateObject$a || (_templateObject$a = babelHelpers.taggedTemplateLiteral(["<i></i>"]))), container);
	    }
	  }, {
	    key: "renderToolbarTo",
	    value: function renderToolbarTo(container) {
	      main_core.Dom.append(_classPrivateMethodGet$e(this, _renderVideoCall, _renderVideoCall2).call(this), container);
	      if (_classPrivateMethodGet$e(this, _getParam$3, _getParam2$3).call(this, 'canInvite')) {
	        main_core.Dom.append(_classPrivateMethodGet$e(this, _renderInvite, _renderInvite2).call(this), container);
	      }
	      main_core.Dom.append(_classPrivateMethodGet$e(this, _renderSettings, _renderSettings2).call(this), container);
	    }
	  }, {
	    key: "renderScrumToolbarTo",
	    value: function renderScrumToolbarTo(container) {
	      main_core.Dom.append(_classPrivateMethodGet$e(this, _renderScrumVideoCall, _renderScrumVideoCall2).call(this), container);
	      main_core.Dom.append(_classPrivateMethodGet$e(this, _renderScrumElements, _renderScrumElements2).call(this), container);
	      if (_classPrivateMethodGet$e(this, _getParam$3, _getParam2$3).call(this, 'canInvite')) {
	        main_core.Dom.append(_classPrivateMethodGet$e(this, _renderInvite, _renderInvite2).call(this), container);
	      }
	      main_core.Dom.append(_classPrivateMethodGet$e(this, _renderSettings, _renderSettings2).call(this), container);
	    }
	  }, {
	    key: "renderUserToolbarTo",
	    value: function renderUserToolbarTo(container) {
	      main_core.Dom.append(_classPrivateMethodGet$e(this, _renderUserVideoCall, _renderUserVideoCall2).call(this), container);
	      main_core.Dom.append(_classPrivateMethodGet$e(this, _renderUserSettings, _renderUserSettings2).call(this), container);
	    }
	  }]);
	  return Menu;
	}();
	function _subscribeToPull2() {
	  var pullRequests = new PullRequests(_classPrivateMethodGet$e(this, _getParam$3, _getParam2$3).call(this, 'entityId'));
	  pullRequests.subscribe('update', _classPrivateMethodGet$e(this, _update$1, _update2$1).bind(this));
	  pull_client.PULL.subscribe(pullRequests);
	}
	function _update2$1() {
	  var _babelHelpers$classPr;
	  var groupDataPromise = MenuAjax.getGroupData(_classPrivateMethodGet$e(this, _getParam$3, _getParam2$3).call(this, 'entityId'));
	  (_babelHelpers$classPr = babelHelpers.classPrivateFieldGet(this, _settings$1)) === null || _babelHelpers$classPr === void 0 ? void 0 : _babelHelpers$classPr.update(groupDataPromise);
	  _classPrivateMethodGet$e(this, _getInvite, _getInvite2).call(this).update(groupDataPromise);
	  babelHelpers.classPrivateFieldGet(this, _chat).update(groupDataPromise);
	}
	function _setParams2$3(params) {
	  babelHelpers.classPrivateFieldGet(this, _cache$3).set('params', params);
	  if (params.groupMembersList) {
	    babelHelpers.classPrivateFieldSet(this, _groupInvitedList, params.groupMembersList.filter(function (user) {
	      return user.invited;
	    }).map(function (user) {
	      return parseInt(user.id, 10);
	    }));
	  }
	}
	function _initServices2(params) {
	  babelHelpers.classPrivateFieldSet(this, _chat, new Chat({
	    entityType: _classPrivateMethodGet$e(this, _getParam$3, _getParam2$3).call(this, 'type'),
	    entityId: _classPrivateMethodGet$e(this, _getParam$3, _getParam2$3).call(this, 'entityId'),
	    groupMembersList: _classPrivateMethodGet$e(this, _getParam$3, _getParam2$3).call(this, 'groupMembersList')
	  }));
	  babelHelpers.classPrivateFieldSet(this, _router, new MenuRouter({
	    pathToFeatures: params.pathToFeatures,
	    pathToUsers: params.pathToUsers,
	    pathToInvite: params.pathToInvite
	  }));
	}
	function _getParam2$3(param) {
	  return babelHelpers.classPrivateFieldGet(this, _cache$3).get('params')[param];
	}
	function _renderVideoCall2() {
	  var _ref = main_core.Tag.render(_templateObject2$2 || (_templateObject2$2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div\n\t\t\t\tref=\"node\"\n\t\t\t\tdata-id=\"spaces-video-call-menu\"\n\t\t\t\tclass=\"sn-spaces__menu-toolbar_btn\"\n\t\t\t>\n\t\t\t\t<div class=\"ui-icon-set --video-1\"></div>\n\t\t\t\t<div\n\t\t\t\t\tref=\"chevronDown\"\n\t\t\t\t\tclass=\"ui-icon-set --chevron-down\"\n\t\t\t\t\tstyle=\"--ui-icon-set__icon-size: 14px;\"\n\t\t\t\t></div>\n\t\t\t</div>\n\t\t"]))),
	    node = _ref.node,
	    chevronDown = _ref.chevronDown;
	  main_core.Event.bind(node, 'click', _classPrivateMethodGet$e(this, _videoCallClick, _videoCallClick2).bind(this, chevronDown));
	  return node;
	}
	function _renderScrumVideoCall2() {
	  var node = main_core.Tag.render(_templateObject3$2 || (_templateObject3$2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div data-id=\"spaces-scrum-video-call-menu\" class=\"sn-spaces__menu-toolbar_btn\">\n\t\t\t\t<div class=\"ui-icon-set --video-1\"></div>\n\t\t\t\t<div class=\"ui-icon-set --chevron-down\" style=\"--ui-icon-set__icon-size: 14px;\"></div>\n\t\t\t</div>\n\t\t"])));
	  main_core.Event.bind(node, 'click', _classPrivateMethodGet$e(this, _scrumVideoCallClick, _scrumVideoCallClick2).bind(this));
	  return node;
	}
	function _renderScrumElements2() {
	  var node = main_core.Tag.render(_templateObject4$2 || (_templateObject4$2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div data-id=\"spaces-scrum-elements-menu\" class=\"sn-spaces__menu-toolbar_btn\">\n\t\t\t\t<div class=\"ui-icon-set --elements\"></div>\n\t\t\t</div>\n\t\t"])));
	  main_core.Event.bind(node, 'click', _classPrivateMethodGet$e(this, _scrumElementsClick, _scrumElementsClick2).bind(this));
	  return node;
	}
	function _renderUserVideoCall2() {
	  var node = main_core.Tag.render(_templateObject5$2 || (_templateObject5$2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div data-id=\"spaces-video-call-menu\" class=\"sn-spaces__menu-toolbar_btn\">\n\t\t\t\t<div class=\"ui-icon-set --video-1\"></div>\n\t\t\t\t<div class=\"ui-icon-set --chevron-down\" style=\"--ui-icon-set__icon-size: 14px;\"></div>\n\t\t\t</div>\n\t\t"])));
	  main_core.Event.bind(node, 'click', _classPrivateMethodGet$e(this, _userVideoCallClick, _userVideoCallClick2).bind(this));
	  return node;
	}
	function _renderInvite2() {
	  var _this = this;
	  if (!babelHelpers.classPrivateFieldGet(this, _inviteNode)) {
	    babelHelpers.classPrivateFieldSet(this, _inviteNode, main_core.Tag.render(_templateObject6$2 || (_templateObject6$2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div data-id=\"spaces-invite-menu\" class=\"sn-spaces__menu-toolbar_btn\">\n\t\t\t\t\t<div class=\"ui-icon-set --person-plus\"></div>\n\t\t\t\t</div>\n\t\t\t"]))));
	    main_core.Event.bind(babelHelpers.classPrivateFieldGet(this, _inviteNode), 'click', _classPrivateMethodGet$e(this, _inviteClick, _inviteClick2).bind(this));
	    if (_classPrivateMethodGet$e(this, _getParam$3, _getParam2$3).call(this, 'isNew')) {
	      setTimeout(function () {
	        return _classPrivateMethodGet$e(_this, _showSpotlight, _showSpotlight2).call(_this, babelHelpers.classPrivateFieldGet(_this, _inviteNode));
	      }, 500);
	    }
	  }
	  return babelHelpers.classPrivateFieldGet(this, _inviteNode);
	}
	function _renderSettings2() {
	  var node = main_core.Tag.render(_templateObject7$1 || (_templateObject7$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div data-id=\"spaces-settings-menu\" class=\"sn-spaces__menu-toolbar_btn\">\n\t\t\t\t<div class=\"ui-icon-set --more\"></div>\n\t\t\t</div>\n\t\t"])));
	  main_core.Event.bind(node, 'click', _classPrivateMethodGet$e(this, _settingsClick, _settingsClick2).bind(this));
	  return node;
	}
	function _renderUserSettings2() {
	  var node = main_core.Tag.render(_templateObject8$1 || (_templateObject8$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div data-id=\"spaces-settings-menu\" class=\"sn-spaces__menu-toolbar_btn\">\n\t\t\t\t<div class=\"ui-icon-set --more\"></div>\n\t\t\t</div>\n\t\t"])));
	  main_core.Event.bind(node, 'click', _classPrivateMethodGet$e(this, _userSettingsClick, _userSettingsClick2).bind(this));
	  return node;
	}
	function _videoCallClick2(bindElement) {
	  var _this2 = this;
	  if (!babelHelpers.classPrivateFieldGet(this, _videoCall)) {
	    babelHelpers.classPrivateFieldSet(this, _videoCall, new VideoCall({
	      bindElement: bindElement
	    }));
	    babelHelpers.classPrivateFieldGet(this, _videoCall).subscribe('hd', function () {
	      babelHelpers.classPrivateFieldGet(_this2, _chat).startVideoCall();
	    });
	    babelHelpers.classPrivateFieldGet(this, _videoCall).subscribe('chat', function () {
	      babelHelpers.classPrivateFieldGet(_this2, _chat).openChat();
	    });
	    babelHelpers.classPrivateFieldGet(this, _videoCall).subscribe('createChat', function () {
	      babelHelpers.classPrivateFieldGet(_this2, _chat).createChat(bindElement);
	    });
	  }
	  babelHelpers.classPrivateFieldGet(this, _videoCall).show();
	}
	function _scrumVideoCallClick2(event) {
	  if (!babelHelpers.classPrivateFieldGet(this, _scrumMeetings)) {
	    babelHelpers.classPrivateFieldSet(this, _scrumMeetings, new tasks_scrum_meetings.Meetings({
	      groupId: _classPrivateMethodGet$e(this, _getParam$3, _getParam2$3).call(this, 'entityId')
	    }));
	  }
	  babelHelpers.classPrivateFieldGet(this, _scrumMeetings).showMenu(event.target);
	}
	function _scrumElementsClick2(event) {
	  if (!babelHelpers.classPrivateFieldGet(this, _scrumMethodology)) {
	    babelHelpers.classPrivateFieldSet(this, _scrumMethodology, new tasks_scrum_methodology.Methodology({
	      groupId: _classPrivateMethodGet$e(this, _getParam$3, _getParam2$3).call(this, 'entityId'),
	      teamSpeedPath: _classPrivateMethodGet$e(this, _getParam$3, _getParam2$3).call(this, 'pathToScrumTeamSpeed'),
	      burnDownPath: _classPrivateMethodGet$e(this, _getParam$3, _getParam2$3).call(this, 'pathToScrumBurnDown'),
	      pathToTask: _classPrivateMethodGet$e(this, _getParam$3, _getParam2$3).call(this, 'pathToGroupTasksTask')
	    }));
	  }
	  babelHelpers.classPrivateFieldGet(this, _scrumMethodology).showMenu(event.target);
	}
	function _userVideoCallClick2(event) {}
	function _inviteClick2() {
	  var invite = _classPrivateMethodGet$e(this, _getInvite, _getInvite2).call(this);
	  if (invite.isShown()) {
	    invite.close();
	  } else {
	    invite.show();
	  }
	}
	function _getInvite2() {
	  if (!babelHelpers.classPrivateFieldGet(this, _invite)) {
	    babelHelpers.classPrivateFieldSet(this, _invite, new Invite({
	      node: babelHelpers.classPrivateFieldGet(this, _inviteNode),
	      groupMembersList: _classPrivateMethodGet$e(this, _getParam$3, _getParam2$3).call(this, 'groupMembersList')
	    }));
	    babelHelpers.classPrivateFieldGet(this, _invite).subscribe('onClose', _classPrivateMethodGet$e(this, _onInviteClose, _onInviteClose2).bind(this));
	    babelHelpers.classPrivateFieldGet(this, _invite).subscribe('usersSelected', _classPrivateMethodGet$e(this, _onUsersSelected, _onUsersSelected2).bind(this));
	  }
	  return babelHelpers.classPrivateFieldGet(this, _invite);
	}
	function _onUsersSelected2(event) {
	  var users = event.data;
	  _classPrivateMethodGet$e(this, _inviteUsers, _inviteUsers2).call(this, users);
	}
	function _onInviteClose2() {
	  if (_classPrivateMethodGet$e(this, _getParam$3, _getParam2$3).call(this, 'isNew') && !babelHelpers.classPrivateFieldGet(this, _discussionAhaMomentShown)) {
	    var startDiscussionButton = document.querySelector('[data-id=spaces-discussions-add-main-btn]');
	    if (!startDiscussionButton) {
	      return;
	    }
	    _classPrivateMethodGet$e(this, _showSpotlight, _showSpotlight2).call(this, startDiscussionButton, {
	      title: main_core.Loc.getMessage('SN_SPACES_DISCUSSION_AHA_MOMENT_TITLE'),
	      text: main_core.Loc.getMessage('SN_SPACES_DISCUSSION_AHA_MOMENT_TEXT')
	    });
	    babelHelpers.classPrivateFieldSet(this, _discussionAhaMomentShown, true);
	  }
	}
	function _showSpotlight2(node) {
	  var _this3 = this;
	  var ahaMoment = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
	  main_core.Runtime.loadExtension('spotlight').then(function () {
	    var spotlight = new BX.SpotLight({
	      targetElement: node,
	      targetVertex: 'middle-center'
	    });
	    main_core.Dom.addClass(node, '--active');
	    spotlight.bindEvents({
	      onTargetEnter: function onTargetEnter() {
	        main_core.Dom.removeClass(node, '--active');
	        spotlight.close();
	      }
	    });
	    spotlight.setColor('#2fc6f6');
	    spotlight.show();
	    if (ahaMoment) {
	      _classPrivateMethodGet$e(_this3, _showAhaMoment, _showAhaMoment2).call(_this3, node, {
	        title: ahaMoment.title,
	        text: ahaMoment.text,
	        spotlight: spotlight
	      });
	    }
	  });
	}
	function _showAhaMoment2(_x, _x2) {
	  return _showAhaMoment3.apply(this, arguments);
	}
	function _showAhaMoment3() {
	  _showAhaMoment3 = babelHelpers.asyncToGenerator( /*#__PURE__*/_regeneratorRuntime$1().mark(function _callee(node, params) {
	    var _yield$Runtime$loadEx, Guide, guide, guidePopup;
	    return _regeneratorRuntime$1().wrap(function _callee$(_context) {
	      while (1) switch (_context.prev = _context.next) {
	        case 0:
	          _context.next = 2;
	          return main_core.Runtime.loadExtension('ui.tour');
	        case 2:
	          _yield$Runtime$loadEx = _context.sent;
	          Guide = _yield$Runtime$loadEx.Guide;
	          guide = new Guide({
	            simpleMode: true,
	            onEvents: true,
	            steps: [{
	              target: node,
	              title: params.title,
	              text: params.text,
	              position: 'bottom',
	              condition: {
	                top: true,
	                bottom: false,
	                color: 'primary'
	              }
	            }]
	          });
	          guide.showNextStep();
	          guidePopup = guide.getPopup();
	          guidePopup.setWidth(380);
	          guidePopup.getContentContainer().style.paddingRight = getComputedStyle(guidePopup.closeIcon)['width'];
	          guidePopup.setAngle({
	            offset: node.offsetWidth / 2 - 5
	          });
	          guidePopup.subscribe('onClose', function () {
	            return params.spotlight.close();
	          });
	          guidePopup.setAutoHide(true);
	        case 12:
	        case "end":
	          return _context.stop();
	      }
	    }, _callee);
	  }));
	  return _showAhaMoment3.apply(this, arguments);
	}
	function _inviteUsers2(users) {
	  var _this4 = this;
	  var invited = users.filter(function (userId) {
	    return !babelHelpers.classPrivateFieldGet(_this4, _groupInvitedList).includes(userId);
	  });
	  var removed = babelHelpers.classPrivateFieldGet(this, _groupInvitedList).filter(function (userId) {
	    return !users.includes(userId);
	  });
	  babelHelpers.classPrivateFieldSet(this, _groupInvitedList, users);

	  // eslint-disable-next-line promise/catch-or-return
	  MenuAjax.inviteUsers(_classPrivateMethodGet$e(this, _getParam$3, _getParam2$3).call(this, 'entityId'), users).then(function () {
	    BX.UI.Notification.Center.notify({
	      content: _classPrivateMethodGet$e(_this4, _getInvitationMessage, _getInvitationMessage2).call(_this4, invited, removed)
	    });
	  }, function (error) {
	    return console.log(error);
	  });
	}
	function _getInvitationMessage2(invited, removed) {
	  var hasInvited = invited.length > 0;
	  var hasRemoved = removed.length > 0;
	  if (hasInvited && !hasRemoved) {
	    return main_core.Loc.getMessage('SN_SPACES_INVITATIONS_SENT');
	  }
	  if (!hasInvited && hasRemoved) {
	    return main_core.Loc.getMessage('SN_SPACES_INVITATIONS_REMOVED');
	  }
	  if (hasInvited && hasRemoved) {
	    return main_core.Loc.getMessage('SN_SPACES_INVITATIONS_CHANGED');
	  }
	  return main_core.Loc.getMessage('SN_SPACES_INVITATIONS_SENT');
	}
	function _settingsClick2(event) {
	  if (!babelHelpers.classPrivateFieldGet(this, _settings$1)) {
	    babelHelpers.classPrivateFieldSet(this, _settings$1, new Settings({
	      bindElement: event.target,
	      type: _classPrivateMethodGet$e(this, _getParam$3, _getParam2$3).call(this, 'type'),
	      entityId: _classPrivateMethodGet$e(this, _getParam$3, _getParam2$3).call(this, 'entityId'),
	      logo: _classPrivateMethodGet$e(this, _getParam$3, _getParam2$3).call(this, 'logo'),
	      chat: babelHelpers.classPrivateFieldGet(this, _chat),
	      router: babelHelpers.classPrivateFieldGet(this, _router)
	    }));
	  }
	  babelHelpers.classPrivateFieldGet(this, _settings$1).show();
	}
	function _userSettingsClick2(event) {}

	exports.Menu = Menu;

}((this.BX.Socialnetwork.Spaces = this.BX.Socialnetwork.Spaces || {}),BX,BX.Tasks.Scrum,BX.Tasks.Scrum,BX.UI.Tour,BX.Messenger.v2.Lib,BX.UI.EntitySelector,BX.UI,BX.UI,BX,BX.Event,BX.Main));
//# sourceMappingURL=script.js.map
