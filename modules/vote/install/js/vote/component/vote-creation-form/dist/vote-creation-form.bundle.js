/* eslint-disable */
this.BX = this.BX || {};
this.BX.Vote = this.BX.Vote || {};
(function (exports,ui_notification,ui_vue3,main_core_events,main_core,ui_switcher,ui_forms,vote_analytics) {
	'use strict';

	var borderWidth = 2;
	var Resize = {
	  mounted: function mounted(el) {
	    main_core.Event.bind(el, 'input', function () {
	      main_core.Dom.style(el, 'height', 0);
	      main_core.Dom.style(el, 'height', "".concat(el.scrollHeight + borderWidth, "px"));
	    });
	  }
	};

	// @vue/component
	var Answer = {
	  name: 'voteAnswer',
	  directives: {
	    resize: Resize
	  },
	  props: {
	    id: {
	      type: String,
	      required: true
	    },
	    removable: {
	      type: Boolean,
	      required: true
	    }
	  },
	  emits: ['removeAnswer', 'changeAnswer', 'onKeyDownEnter'],
	  data: function data() {
	    return {
	      answerText: ''
	    };
	  },
	  methods: {
	    changeAnswer: function changeAnswer() {
	      this.$emit('changeAnswer', this.answerText);
	    },
	    focus: function focus() {
	      this.$refs.answerField.focus();
	    },
	    handleDeleteClick: function handleDeleteClick() {
	      if (this.removable) {
	        this.$emit('removeAnswer');
	      } else {
	        this.answerText = '';
	        this.$emit('changeAnswer', this.answerText);
	      }
	    },
	    handleEnterPress: function handleEnterPress() {
	      this.$emit('onKeyDownEnter');
	    }
	  },
	  template: "\n\t\t<div class=\"vote-creation-form__answer\" :data-id=\"id\">\n\t\t\t<div class=\"vote-creation-form__answer_dnd-icon ui-icon-set --more-points\"></div>\n\t\t\t<div class=\"ui-ctl ui-ctl-textarea ui-ctl-no-resize\">\n\t\t\t\t<textarea\n\t\t\t\t\tmaxlength=\"100\"\n\t\t\t\t\tautocomplete=\"off\"\n\t\t\t\t\tclass=\"ui-ctl-element\"\n\t\t\t\t\t:data-test-id=\"'vote_creation_form_' + id\"\n\t\t\t\t\tv-resize\n\t\t\t\t\tv-model.trim=\"answerText\"\n\t\t\t\t\t@input=\"changeAnswer\"\n\t\t\t\t\t@keydown.enter.prevent=\"handleEnterPress\"\n\t\t\t\t\tref=\"answerField\"\n\t\t\t\t></textarea>\n\t\t\t\t<span\n\t\t\t\t\t:data-test-id=\"'vote_creation_form_delete_' + id\"\n\t\t\t\t\tclass=\"vote-creation-form__answer_delete\"\n\t\t\t\t\t@click=\"handleDeleteClick\"\n\t\t\t\t>\n\t\t\t\t</span>\n\t\t\t</div>\n\t\t</div>\n\t"
	};

	var Loc = {
	  methods: {
	    loc: function loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  }
	};

	var _templateObject;
	var transform = function transform(element, value) {
	  main_core.Dom.style(element, 'transform', "translate3d(0, ".concat(value, "px, 0)"));
	};
	var transformGhost = function transformGhost(draggedNode, dragContainer, ghost, transformY) {
	  var y = draggedNode.offsetTop + transformY;
	  if (y < 0) {
	    transform(ghost, 0);
	  } else if (y + ghost.offsetHeight > dragContainer.offsetHeight) {
	    transform(ghost, dragContainer.offsetHeight - ghost.offsetHeight);
	  } else {
	    transform(ghost, y);
	  }
	};
	var createGhost = function createGhost(draggedNode) {
	  var ghost = draggedNode.cloneNode(true);
	  main_core.Dom.addClass(ghost, 'vote-creation-form__answer_ghost');
	  transform(ghost, draggedNode.offsetTop);
	  main_core.Dom.prepend(ghost, draggedNode.parentElement);
	  return ghost;
	};
	var createPositionPointer = function createPositionPointer(container) {
	  var pointer = main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["<div class=\"vote-creation-form__answer_position-pointer\" hidden></div>"])));
	  main_core.Dom.prepend(pointer, container);
	  return pointer;
	};
	var dragStartHandler = function dragStartHandler(_ref, order) {
	  var target = _ref.target,
	    dragContainer = _ref.currentTarget;
	  if (!main_core.Dom.hasClass(target, 'vote-creation-form__answer_dnd-icon')) {
	    return;
	  }
	  var transformY = 0;
	  var targetNode = null;
	  var prevScrollY = window.scrollY;
	  var pointerOffset = 8;
	  var borderWidth = 1;
	  var draggedNode = target.closest('.vote-creation-form__answer');
	  var ghost = createGhost(draggedNode);
	  var positionPointer = createPositionPointer(dragContainer);
	  var mouseMoveHandler = function mouseMoveHandler(_ref2) {
	    var movementY = _ref2.movementY;
	    main_core.Dom.style(document.body, 'userSelect', 'none');
	    main_core.Dom.style(document.body, 'cursor', 'grabbing');
	    main_core.Dom.addClass(dragContainer, '--pointer-events-disabled');
	    transformY += movementY;
	    transformGhost(draggedNode, dragContainer, ghost, transformY);
	    main_core.Dom.removeClass(dragContainer, '--pointer-events-disabled');
	    var _ghost$getBoundingCli = ghost.getBoundingClientRect(),
	      x = _ghost$getBoundingCli.x,
	      y = _ghost$getBoundingCli.y;
	    var belowY = transformY > 0 ? y + ghost.offsetHeight - borderWidth : y;
	    var belowNode = document.elementFromPoint(x, belowY);
	    targetNode = belowNode === null || belowNode === void 0 ? void 0 : belowNode.closest('.vote-creation-form__answer');
	    main_core.Dom.addClass(dragContainer, '--pointer-events-disabled');
	    if (!targetNode || targetNode === draggedNode) {
	      positionPointer.hidden = true;
	      return;
	    }
	    var _targetNode = targetNode,
	      offsetTop = _targetNode.offsetTop,
	      offsetHeight = _targetNode.offsetHeight;
	    var pointerPosition = transformY > 0 ? offsetTop + offsetHeight + pointerOffset : offsetTop - pointerOffset;
	    positionPointer.hidden = false;
	    transform(positionPointer, pointerPosition);
	  };
	  var mouseUpHandler = function mouseUpHandler() {
	    main_core.Dom.style(document.body, 'userSelect', '');
	    main_core.Dom.style(document.body, 'cursor', '');
	    main_core.Dom.removeClass(dragContainer, '--pointer-events-disabled');
	    main_core.Event.unbind(document, 'mousemove', mouseMoveHandler);
	    main_core.Event.unbind(document, 'mouseup', mouseUpHandler);
	    main_core.Event.unbind(document, 'scroll', scrollHandler);
	    main_core.Dom.remove(ghost);
	    main_core.Dom.remove(positionPointer);
	    if (targetNode && targetNode !== draggedNode) {
	      order(draggedNode.dataset.id, targetNode.dataset.id, transformY > 0);
	    }
	  };
	  var scrollHandler = function scrollHandler() {
	    var shift = window.scrollY - prevScrollY;
	    prevScrollY = window.scrollY;
	    transformY += shift;
	    transformGhost(draggedNode, dragContainer, ghost, transformY);
	  };
	  main_core.Event.bind(document, 'mousemove', mouseMoveHandler);
	  main_core.Event.bind(document, 'mouseup', mouseUpHandler);
	  main_core.Event.bind(document, 'scroll', scrollHandler);
	};
	var DragAndDrop = {
	  mounted: function mounted(el, binding) {
	    main_core.Event.bind(el, 'mousedown', function (event) {
	      dragStartHandler(event, binding.value);
	    });
	  }
	};

	function _regeneratorRuntime() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/facebook/regenerator/blob/main/LICENSE */ _regeneratorRuntime = function _regeneratorRuntime() { return exports; }; var exports = {}, Op = Object.prototype, hasOwn = Op.hasOwnProperty, defineProperty = Object.defineProperty || function (obj, key, desc) { obj[key] = desc.value; }, $Symbol = "function" == typeof Symbol ? Symbol : {}, iteratorSymbol = $Symbol.iterator || "@@iterator", asyncIteratorSymbol = $Symbol.asyncIterator || "@@asyncIterator", toStringTagSymbol = $Symbol.toStringTag || "@@toStringTag"; function define(obj, key, value) { return Object.defineProperty(obj, key, { value: value, enumerable: !0, configurable: !0, writable: !0 }), obj[key]; } try { define({}, ""); } catch (err) { define = function define(obj, key, value) { return obj[key] = value; }; } function wrap(innerFn, outerFn, self, tryLocsList) { var protoGenerator = outerFn && outerFn.prototype instanceof Generator ? outerFn : Generator, generator = Object.create(protoGenerator.prototype), context = new Context(tryLocsList || []); return defineProperty(generator, "_invoke", { value: makeInvokeMethod(innerFn, self, context) }), generator; } function tryCatch(fn, obj, arg) { try { return { type: "normal", arg: fn.call(obj, arg) }; } catch (err) { return { type: "throw", arg: err }; } } exports.wrap = wrap; var ContinueSentinel = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} var IteratorPrototype = {}; define(IteratorPrototype, iteratorSymbol, function () { return this; }); var getProto = Object.getPrototypeOf, NativeIteratorPrototype = getProto && getProto(getProto(values([]))); NativeIteratorPrototype && NativeIteratorPrototype !== Op && hasOwn.call(NativeIteratorPrototype, iteratorSymbol) && (IteratorPrototype = NativeIteratorPrototype); var Gp = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(IteratorPrototype); function defineIteratorMethods(prototype) { ["next", "throw", "return"].forEach(function (method) { define(prototype, method, function (arg) { return this._invoke(method, arg); }); }); } function AsyncIterator(generator, PromiseImpl) { function invoke(method, arg, resolve, reject) { var record = tryCatch(generator[method], generator, arg); if ("throw" !== record.type) { var result = record.arg, value = result.value; return value && "object" == babelHelpers["typeof"](value) && hasOwn.call(value, "__await") ? PromiseImpl.resolve(value.__await).then(function (value) { invoke("next", value, resolve, reject); }, function (err) { invoke("throw", err, resolve, reject); }) : PromiseImpl.resolve(value).then(function (unwrapped) { result.value = unwrapped, resolve(result); }, function (error) { return invoke("throw", error, resolve, reject); }); } reject(record.arg); } var previousPromise; defineProperty(this, "_invoke", { value: function value(method, arg) { function callInvokeWithMethodAndArg() { return new PromiseImpl(function (resolve, reject) { invoke(method, arg, resolve, reject); }); } return previousPromise = previousPromise ? previousPromise.then(callInvokeWithMethodAndArg, callInvokeWithMethodAndArg) : callInvokeWithMethodAndArg(); } }); } function makeInvokeMethod(innerFn, self, context) { var state = "suspendedStart"; return function (method, arg) { if ("executing" === state) throw new Error("Generator is already running"); if ("completed" === state) { if ("throw" === method) throw arg; return doneResult(); } for (context.method = method, context.arg = arg;;) { var delegate = context.delegate; if (delegate) { var delegateResult = maybeInvokeDelegate(delegate, context); if (delegateResult) { if (delegateResult === ContinueSentinel) continue; return delegateResult; } } if ("next" === context.method) context.sent = context._sent = context.arg;else if ("throw" === context.method) { if ("suspendedStart" === state) throw state = "completed", context.arg; context.dispatchException(context.arg); } else "return" === context.method && context.abrupt("return", context.arg); state = "executing"; var record = tryCatch(innerFn, self, context); if ("normal" === record.type) { if (state = context.done ? "completed" : "suspendedYield", record.arg === ContinueSentinel) continue; return { value: record.arg, done: context.done }; } "throw" === record.type && (state = "completed", context.method = "throw", context.arg = record.arg); } }; } function maybeInvokeDelegate(delegate, context) { var methodName = context.method, method = delegate.iterator[methodName]; if (undefined === method) return context.delegate = null, "throw" === methodName && delegate.iterator["return"] && (context.method = "return", context.arg = undefined, maybeInvokeDelegate(delegate, context), "throw" === context.method) || "return" !== methodName && (context.method = "throw", context.arg = new TypeError("The iterator does not provide a '" + methodName + "' method")), ContinueSentinel; var record = tryCatch(method, delegate.iterator, context.arg); if ("throw" === record.type) return context.method = "throw", context.arg = record.arg, context.delegate = null, ContinueSentinel; var info = record.arg; return info ? info.done ? (context[delegate.resultName] = info.value, context.next = delegate.nextLoc, "return" !== context.method && (context.method = "next", context.arg = undefined), context.delegate = null, ContinueSentinel) : info : (context.method = "throw", context.arg = new TypeError("iterator result is not an object"), context.delegate = null, ContinueSentinel); } function pushTryEntry(locs) { var entry = { tryLoc: locs[0] }; 1 in locs && (entry.catchLoc = locs[1]), 2 in locs && (entry.finallyLoc = locs[2], entry.afterLoc = locs[3]), this.tryEntries.push(entry); } function resetTryEntry(entry) { var record = entry.completion || {}; record.type = "normal", delete record.arg, entry.completion = record; } function Context(tryLocsList) { this.tryEntries = [{ tryLoc: "root" }], tryLocsList.forEach(pushTryEntry, this), this.reset(!0); } function values(iterable) { if (iterable) { var iteratorMethod = iterable[iteratorSymbol]; if (iteratorMethod) return iteratorMethod.call(iterable); if ("function" == typeof iterable.next) return iterable; if (!isNaN(iterable.length)) { var i = -1, next = function next() { for (; ++i < iterable.length;) if (hasOwn.call(iterable, i)) return next.value = iterable[i], next.done = !1, next; return next.value = undefined, next.done = !0, next; }; return next.next = next; } } return { next: doneResult }; } function doneResult() { return { value: undefined, done: !0 }; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, defineProperty(Gp, "constructor", { value: GeneratorFunctionPrototype, configurable: !0 }), defineProperty(GeneratorFunctionPrototype, "constructor", { value: GeneratorFunction, configurable: !0 }), GeneratorFunction.displayName = define(GeneratorFunctionPrototype, toStringTagSymbol, "GeneratorFunction"), exports.isGeneratorFunction = function (genFun) { var ctor = "function" == typeof genFun && genFun.constructor; return !!ctor && (ctor === GeneratorFunction || "GeneratorFunction" === (ctor.displayName || ctor.name)); }, exports.mark = function (genFun) { return Object.setPrototypeOf ? Object.setPrototypeOf(genFun, GeneratorFunctionPrototype) : (genFun.__proto__ = GeneratorFunctionPrototype, define(genFun, toStringTagSymbol, "GeneratorFunction")), genFun.prototype = Object.create(Gp), genFun; }, exports.awrap = function (arg) { return { __await: arg }; }, defineIteratorMethods(AsyncIterator.prototype), define(AsyncIterator.prototype, asyncIteratorSymbol, function () { return this; }), exports.AsyncIterator = AsyncIterator, exports.async = function (innerFn, outerFn, self, tryLocsList, PromiseImpl) { void 0 === PromiseImpl && (PromiseImpl = Promise); var iter = new AsyncIterator(wrap(innerFn, outerFn, self, tryLocsList), PromiseImpl); return exports.isGeneratorFunction(outerFn) ? iter : iter.next().then(function (result) { return result.done ? result.value : iter.next(); }); }, defineIteratorMethods(Gp), define(Gp, toStringTagSymbol, "Generator"), define(Gp, iteratorSymbol, function () { return this; }), define(Gp, "toString", function () { return "[object Generator]"; }), exports.keys = function (val) { var object = Object(val), keys = []; for (var key in object) keys.push(key); return keys.reverse(), function next() { for (; keys.length;) { var key = keys.pop(); if (key in object) return next.value = key, next.done = !1, next; } return next.done = !0, next; }; }, exports.values = values, Context.prototype = { constructor: Context, reset: function reset(skipTempReset) { if (this.prev = 0, this.next = 0, this.sent = this._sent = undefined, this.done = !1, this.delegate = null, this.method = "next", this.arg = undefined, this.tryEntries.forEach(resetTryEntry), !skipTempReset) for (var name in this) "t" === name.charAt(0) && hasOwn.call(this, name) && !isNaN(+name.slice(1)) && (this[name] = undefined); }, stop: function stop() { this.done = !0; var rootRecord = this.tryEntries[0].completion; if ("throw" === rootRecord.type) throw rootRecord.arg; return this.rval; }, dispatchException: function dispatchException(exception) { if (this.done) throw exception; var context = this; function handle(loc, caught) { return record.type = "throw", record.arg = exception, context.next = loc, caught && (context.method = "next", context.arg = undefined), !!caught; } for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i], record = entry.completion; if ("root" === entry.tryLoc) return handle("end"); if (entry.tryLoc <= this.prev) { var hasCatch = hasOwn.call(entry, "catchLoc"), hasFinally = hasOwn.call(entry, "finallyLoc"); if (hasCatch && hasFinally) { if (this.prev < entry.catchLoc) return handle(entry.catchLoc, !0); if (this.prev < entry.finallyLoc) return handle(entry.finallyLoc); } else if (hasCatch) { if (this.prev < entry.catchLoc) return handle(entry.catchLoc, !0); } else { if (!hasFinally) throw new Error("try statement without catch or finally"); if (this.prev < entry.finallyLoc) return handle(entry.finallyLoc); } } } }, abrupt: function abrupt(type, arg) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.tryLoc <= this.prev && hasOwn.call(entry, "finallyLoc") && this.prev < entry.finallyLoc) { var finallyEntry = entry; break; } } finallyEntry && ("break" === type || "continue" === type) && finallyEntry.tryLoc <= arg && arg <= finallyEntry.finallyLoc && (finallyEntry = null); var record = finallyEntry ? finallyEntry.completion : {}; return record.type = type, record.arg = arg, finallyEntry ? (this.method = "next", this.next = finallyEntry.finallyLoc, ContinueSentinel) : this.complete(record); }, complete: function complete(record, afterLoc) { if ("throw" === record.type) throw record.arg; return "break" === record.type || "continue" === record.type ? this.next = record.arg : "return" === record.type ? (this.rval = this.arg = record.arg, this.method = "return", this.next = "end") : "normal" === record.type && afterLoc && (this.next = afterLoc), ContinueSentinel; }, finish: function finish(finallyLoc) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.finallyLoc === finallyLoc) return this.complete(entry.completion, entry.afterLoc), resetTryEntry(entry), ContinueSentinel; } }, "catch": function _catch(tryLoc) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.tryLoc === tryLoc) { var record = entry.completion; if ("throw" === record.type) { var thrown = record.arg; resetTryEntry(entry); } return thrown; } } throw new Error("illegal catch attempt"); }, delegateYield: function delegateYield(iterable, resultName, nextLoc) { return this.delegate = { iterator: values(iterable), resultName: resultName, nextLoc: nextLoc }, "next" === this.method && (this.arg = undefined), ContinueSentinel; } }, exports; }
	function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	// @vue/component
	var Question = {
	  name: 'voteQuestion',
	  directives: {
	    dnd: DragAndDrop,
	    resize: Resize
	  },
	  components: {
	    Answer: Answer
	  },
	  mixins: [Loc],
	  props: {
	    id: {
	      type: String,
	      required: true
	    },
	    question: {
	      type: Object,
	      required: true
	    },
	    minAnswersCount: {
	      type: Number,
	      required: true
	    },
	    maxAnswersCount: {
	      type: Number,
	      required: true
	    }
	  },
	  emits: ['addAnswer', 'removeAnswer', 'changeQuestion', 'validate'],
	  data: function data() {
	    return {
	      questionText: ''
	    };
	  },
	  computed: {
	    answersCount: function answersCount() {
	      return Object.keys(this.question.answers).length;
	    },
	    isValid: function isValid() {
	      var answers = Object.values(this.question.answers);
	      var filledAnswers = answers.filter(function (answer) {
	        return answer !== '';
	      });
	      return this.question.questionText !== '' && filledAnswers.length >= this.minAnswersCount;
	    },
	    canAddMoreAnswers: function canAddMoreAnswers() {
	      return this.answersCount < this.maxAnswersCount;
	    },
	    removable: function removable() {
	      return this.answersCount > this.minAnswersCount;
	    }
	  },
	  watch: {
	    isValid: function isValid() {
	      this.$emit('validate', this.isValid);
	    }
	  },
	  mounted: function mounted() {
	    var _this = this;
	    var switcher = new ui_switcher.Switcher({
	      node: this.$refs.multipleAnswersSwitcher,
	      size: ui_switcher.SwitcherSize.small,
	      checked: false,
	      handlers: {
	        toggled: function toggled() {
	          _this.$emit('changeQuestion', _objectSpread(_objectSpread({}, _this.question), {}, {
	            allowMultipleAnswers: switcher.checked
	          }));
	        }
	      }
	    });
	    main_core.Dom.attr(switcher.node, 'data-test-id', "vote_creation_form_allow_multiple_answers_".concat(this.id));
	  },
	  methods: {
	    changeText: function changeText() {
	      var question = _objectSpread(_objectSpread({}, this.question), {}, {
	        questionText: this.questionText
	      });
	      this.$emit('changeQuestion', question);
	    },
	    addAnswer: function addAnswer() {
	      this.$emit('addAnswer');
	    },
	    removeAnswer: function removeAnswer(answerId) {
	      this.$emit('removeAnswer', answerId);
	    },
	    changeAnswer: function changeAnswer(answerId, answerText) {
	      var answers = this.question.answers;
	      var question = _objectSpread(_objectSpread({}, this.question), {}, {
	        answers: _objectSpread(_objectSpread({}, answers), {}, babelHelpers.defineProperty({}, answerId, answerText))
	      });
	      this.$emit('changeQuestion', question);
	    },
	    orderAnswer: function orderAnswer(draggedKey, targetKey, shouldInsertBelow) {
	      var answers = this.question.answers;
	      var newKeys = Object.keys(answers).filter(function (key) {
	        return key !== draggedKey;
	      });
	      var targetIndex = newKeys.indexOf(targetKey);
	      var insertionIndex = shouldInsertBelow ? targetIndex + 1 : targetIndex;
	      newKeys.splice(insertionIndex, 0, draggedKey);
	      var newAnswers = newKeys.reduce(function (acc, key) {
	        acc[key] = answers[key];
	        return acc;
	      }, {});
	      this.$emit('changeQuestion', _objectSpread(_objectSpread({}, this.question), {}, {
	        answers: newAnswers
	      }));
	    },
	    focusQuestionField: function focusQuestionField() {
	      var textarea = this.$refs.questionField;
	      textarea.focus();
	    },
	    getAnswerRefById: function getAnswerRefById(answerId) {
	      var _this$$refs;
	      return (_this$$refs = this.$refs["answer_".concat(answerId)]) === null || _this$$refs === void 0 ? void 0 : _this$$refs[0];
	    },
	    getAnswerIdByIndex: function getAnswerIdByIndex(index) {
	      var _answerIds$index;
	      var answerIds = Object.keys(this.question.answers);
	      return (_answerIds$index = answerIds[index]) !== null && _answerIds$index !== void 0 ? _answerIds$index : null;
	    },
	    focusNewAnswer: function focusNewAnswer(newAnswerId) {
	      var _this2 = this;
	      return babelHelpers.asyncToGenerator( /*#__PURE__*/_regeneratorRuntime().mark(function _callee() {
	        var newAnswer;
	        return _regeneratorRuntime().wrap(function _callee$(_context) {
	          while (1) switch (_context.prev = _context.next) {
	            case 0:
	              newAnswer = _this2.getAnswerRefById(newAnswerId);
	              if (!newAnswer) {
	                _context.next = 5;
	                break;
	              }
	              _context.next = 4;
	              return _this2.$nextTick();
	            case 4:
	              newAnswer.focus();
	            case 5:
	            case "end":
	              return _context.stop();
	          }
	        }, _callee);
	      }))();
	    },
	    focusNextAnswer: function focusNextAnswer(currentAnswerId) {
	      var answerIds = Object.keys(this.question.answers);
	      var currentIndex = answerIds.indexOf(currentAnswerId);
	      if (currentIndex === -1) {
	        return;
	      }
	      this.focusByIndex(currentIndex + 1);
	    },
	    handleQuestionEnter: function handleQuestionEnter() {
	      this.focusByIndex(0);
	    },
	    focusByIndex: function focusByIndex(index) {
	      var targetAnswerId = this.getAnswerIdByIndex(index);
	      var answerElement = this.getAnswerRefById(targetAnswerId);
	      if (answerElement) {
	        answerElement.focus();
	      } else if (this.canAddMoreAnswers) {
	        this.addAnswer();
	      }
	    }
	  },
	  template: "\n\t\t<div class=\"vote-creation-form__question\" :data-id=\"id\">\n\t\t\t<p class=\"vote-creation-form__question_label\">\n\t\t\t\t{{loc('VOTE_QUESTION_LABEL')}}\n\t\t\t</p>\n\t\t\t<div class=\"ui-ctl ui-ctl-textarea ui-ctl-no-resize\">\n\t\t\t\t<textarea\n\t\t\t\t\tmaxlength=\"250\"\n\t\t\t\t\tclass=\"ui-ctl-element\"\n\t\t\t\t\t:data-test-id=\"'vote_creation_form_' + id\"\n\t\t\t\t\tv-model.trim=\"questionText\"\n\t\t\t\t\tv-resize\n\t\t\t\t\t@input=\"changeText\"\n\t\t\t\t\t@keydown.enter.prevent=\"handleQuestionEnter\"\n\t\t\t\t\tref=\"questionField\"\n\t\t\t\t></textarea>\n\t\t\t</div>\n\t\t\t<p class=\"vote-creation-form__question_answer-options\">\n\t\t\t\t{{loc('VOTE_QUESTION_ANSWER_OPTIONS')}}\n\t\t\t</p>\n\t\t\t<div\n\t\t\t\tclass=\"vote-creation-form__answers\"\n\t\t\t\t:class=\"{'--removable': removable}\"\n\t\t\t\tv-dnd=\"orderAnswer\"\n\t\t\t>\n\t\t\t\t<Answer\n\t\t\t\t\tv-for=\"(answer, id) in question.answers\"\n\t\t\t\t\t:key=\"id\"\n\t\t\t\t\t:id=\"id\"\n\t\t\t\t\t:ref=\"'answer_' + id\"\n\t\t\t\t\t:answer=\"answer\"\n\t\t\t\t\t:removable=\"removable\"\n\t\t\t\t\t@removeAnswer=\"removeAnswer(id)\"\n\t\t\t\t\t@changeAnswer=\"changeAnswer(id, $event)\"\n\t\t\t\t\t@onKeyDownEnter=\"focusNextAnswer(id)\"\n\t\t\t\t></Answer>\n\t\t\t</div>\n\t\t\t<div\n\t\t\t\tv-if=\"canAddMoreAnswers\"\n\t\t\t\t:data-test-id=\"'vote_creation_form_add_answer_' + id\"\n\t\t\t\tclass=\"vote-creation-form__question_add-answer\"\n\t\t\t\t@click=\"addAnswer\"\n\t\t\t>\n\t\t\t\t<div class=\"vote-creation-form__question_plus\"></div>\n\t\t\t\t<span>{{loc('VOTE_QUESTION_ADD_ANSWER')}}</span>\n\t\t\t</div>\n\t\t\t<div class=\"vote-creation-form__question_multiple-answers\">\n\t\t\t\t<span>{{loc('VOTE_QUESTION_MULTIPLE_ANSWERS')}}</span>\n\t\t\t\t<div ref=\"multipleAnswersSwitcher\"></div>\n\t\t\t</div>\n\t\t</div>\n\t"
	};

	function ownKeys$1(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread$1(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys$1(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys$1(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	// @vue/component
	var VoteForm = {
	  name: 'voteForm',
	  components: {
	    Question: Question
	  },
	  mixins: [Loc],
	  props: {
	    options: {
	      type: Object,
	      required: true
	    }
	  },
	  data: function data() {
	    return {
	      questions: {},
	      validatedQuestions: {}
	    };
	  },
	  computed: {
	    questionsCount: function questionsCount() {
	      return Object.keys(this.questions).length;
	    },
	    isFormValid: function isFormValid() {
	      var validationValues = Object.values(this.validatedQuestions);
	      if (validationValues.length === 0) {
	        return false;
	      }
	      return validationValues.every(function (value) {
	        return value;
	      });
	    },
	    canAddMoreQuestions: function canAddMoreQuestions() {
	      return this.questionsCount < this.options.maxQuestionsCount;
	    },
	    firstQuestionId: function firstQuestionId() {
	      var _Object$keys = Object.keys(this.questions),
	        _Object$keys2 = babelHelpers.slicedToArray(_Object$keys, 1),
	        firstQuestionId = _Object$keys2[0];
	      return firstQuestionId || null;
	    }
	  },
	  watch: {
	    isFormValid: function isFormValid() {
	      main_core_events.EventEmitter.emit('vote-creation-form-validate');
	    }
	  },
	  created: function created() {
	    this.questionIdCounter = 1;
	    this.answerIdCounter = 1;
	    this.settingsLabels = {
	      anonymousVote: this.loc('VOTE_SETTING_ANONYMOUS'),
	      allowRevoking: this.loc('VOTE_SETTING_ALLOW_REVOTING')
	    };
	    this.settings = {
	      anonymousVote: false,
	      allowRevoking: true
	    };
	    this.initQuestions();
	  },
	  mounted: function mounted() {
	    var _this = this;
	    Object.keys(this.settingsLabels).forEach(function (id) {
	      var switcher = new ui_switcher.Switcher({
	        node: _this.$refs[id][0],
	        size: ui_switcher.SwitcherSize.small,
	        checked: _this.settings[id],
	        handlers: {
	          toggled: function toggled() {
	            _this.settings = _objectSpread$1(_objectSpread$1({}, _this.settings), {}, babelHelpers.defineProperty({}, id, switcher.checked));
	          }
	        }
	      });
	      main_core.Dom.attr(switcher.node, 'data-test-id', "vote_creation_form_setting_".concat(id));
	    });
	    this.$nextTick(function () {
	      _this.focusFirstQuestion();
	    });
	  },
	  methods: {
	    initQuestions: function initQuestions() {
	      var _this$options = this.options,
	        minQuestionsCount = _this$options.minQuestionsCount,
	        minAnswersCount = _this$options.minAnswersCount;
	      var answersCount = minQuestionsCount * minAnswersCount;
	      var answerIndex = 1;
	      var questionId = "question_".concat(this.questionIdCounter);
	      for (var i = 0; i < answersCount; i++) {
	        if (answerIndex > minAnswersCount) {
	          answerIndex = 1;
	          questionId = "question_".concat(++this.questionIdCounter);
	        }
	        if (!this.questions[questionId]) {
	          this.validatedQuestions[questionId] = false;
	          this.questions[questionId] = {
	            questionText: '',
	            answers: {},
	            allowMultipleAnswers: false
	          };
	        }
	        var answerId = "answer_".concat(this.answerIdCounter++);
	        this.questions[questionId].answers[answerId] = '';
	        answerIndex += 1;
	      }
	    },
	    addQuestion: function addQuestion() {
	      var answers = {};
	      var minAnswersCount = this.options.minAnswersCount;
	      for (var i = 0; i < minAnswersCount; i++) {
	        answers["answer_".concat(this.answerIdCounter++)] = '';
	      }
	      var questionId = "question_".concat(++this.questionIdCounter);
	      this.questions[questionId] = {
	        questionText: '',
	        answers: answers,
	        allowMultipleAnswers: false
	      };
	      this.validatedQuestions[questionId] = false;
	    },
	    addAnswer: function addAnswer(questionId) {
	      var _this2 = this;
	      var question = this.questions[questionId];
	      var answers = question.answers;
	      var newAnswerId = "answer_".concat(this.answerIdCounter++);
	      answers[newAnswerId] = '';
	      this.$nextTick(function () {
	        var _this2$$refs;
	        var questionRef = (_this2$$refs = _this2.$refs["question_".concat(questionId)]) === null || _this2$$refs === void 0 ? void 0 : _this2$$refs[0];
	        questionRef === null || questionRef === void 0 ? void 0 : questionRef.focusNewAnswer(newAnswerId);
	      });
	    },
	    removeAnswer: function removeAnswer(questionId, answerId) {
	      var question = this.questions[questionId];
	      delete question.answers[answerId];
	    },
	    changeQuestion: function changeQuestion(questionId, data) {
	      this.questions[questionId] = data;
	    },
	    validate: function validate(questionId, value) {
	      this.validatedQuestions[questionId] = value;
	    },
	    focusFirstQuestion: function focusFirstQuestion() {
	      var _this$$refs;
	      if (!this.firstQuestionId) {
	        return;
	      }
	      var firstQuestionRef = (_this$$refs = this.$refs["question_".concat(this.firstQuestionId)]) === null || _this$$refs === void 0 ? void 0 : _this$$refs[0];
	      firstQuestionRef === null || firstQuestionRef === void 0 ? void 0 : firstQuestionRef.focusQuestionField();
	    }
	  },
	  template: "\n\t\t<div class=\"vote-creation-form\">\n\t\t\t<div class=\"vote-creation-form__questions\">\n\t\t\t\t<Question\n\t\t\t\t\tv-for=\"(question, id) in questions\"\n\t\t\t\t\t:key=\"id\"\n\t\t\t\t\t:id=\"id\"\n\t\t\t\t\t:question=\"question\"\n\t\t\t\t\t:maxAnswersCount=\"options.maxAnswersCount\"\n\t\t\t\t\t:minAnswersCount=\"options.minAnswersCount\"\n\t\t\t\t\t@addAnswer=\"addAnswer(id)\"\n\t\t\t\t\t@removeAnswer=\"removeAnswer(id, $event)\"\n\t\t\t\t\t@changeQuestion=\"changeQuestion(id, $event)\"\n\t\t\t\t\t@validate=\"validate(id, $event)\"\n\t\t\t\t\t:ref=\"'question_' + id\"\n\t\t\t\t></Question>\n\t\t\t\t<button\n\t\t\t\t\tv-if=\"canAddMoreQuestions\"\n\t\t\t\t\tclass=\"ui-btn ui-btn-light-border ui-btn-sm ui-btn-no-caps ui-btn-round vote-creation-form__add-question-btn\"\n\t\t\t\t\t@click=\"addQuestion\"\n\t\t\t\t>\n\t\t\t\t\t{{loc('VOTE_ADD_QUESTION')}}\n\t\t\t\t</button>\n\t\t\t</div>\n\t\t\t<div class=\"vote-creation-form__settings\">\n\t\t\t\t<p class=\"vote-creation-form__settings_title\">\n\t\t\t\t\t{{loc('VOTE_SETTINGS_TITLE')}}\n\t\t\t\t</p>\n\t\t\t\t<div\n\t\t\t\t\tv-for=\"(label, id) in settingsLabels\"\n\t\t\t\t\tclass=\"vote-creation-form__settings_setting\"\n\t\t\t\t>\n\t\t\t\t\t<span>{{label}}</span>\n\t\t\t\t\t<div :ref=\"id\"></div>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t</div>\n\t"
	};

	var _excluded = ["containerId", "chatId"];
	function ownKeys$2(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread$2(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys$2(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys$2(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	function _regeneratorRuntime$1() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/facebook/regenerator/blob/main/LICENSE */ _regeneratorRuntime$1 = function _regeneratorRuntime() { return exports; }; var exports = {}, Op = Object.prototype, hasOwn = Op.hasOwnProperty, defineProperty = Object.defineProperty || function (obj, key, desc) { obj[key] = desc.value; }, $Symbol = "function" == typeof Symbol ? Symbol : {}, iteratorSymbol = $Symbol.iterator || "@@iterator", asyncIteratorSymbol = $Symbol.asyncIterator || "@@asyncIterator", toStringTagSymbol = $Symbol.toStringTag || "@@toStringTag"; function define(obj, key, value) { return Object.defineProperty(obj, key, { value: value, enumerable: !0, configurable: !0, writable: !0 }), obj[key]; } try { define({}, ""); } catch (err) { define = function define(obj, key, value) { return obj[key] = value; }; } function wrap(innerFn, outerFn, self, tryLocsList) { var protoGenerator = outerFn && outerFn.prototype instanceof Generator ? outerFn : Generator, generator = Object.create(protoGenerator.prototype), context = new Context(tryLocsList || []); return defineProperty(generator, "_invoke", { value: makeInvokeMethod(innerFn, self, context) }), generator; } function tryCatch(fn, obj, arg) { try { return { type: "normal", arg: fn.call(obj, arg) }; } catch (err) { return { type: "throw", arg: err }; } } exports.wrap = wrap; var ContinueSentinel = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} var IteratorPrototype = {}; define(IteratorPrototype, iteratorSymbol, function () { return this; }); var getProto = Object.getPrototypeOf, NativeIteratorPrototype = getProto && getProto(getProto(values([]))); NativeIteratorPrototype && NativeIteratorPrototype !== Op && hasOwn.call(NativeIteratorPrototype, iteratorSymbol) && (IteratorPrototype = NativeIteratorPrototype); var Gp = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(IteratorPrototype); function defineIteratorMethods(prototype) { ["next", "throw", "return"].forEach(function (method) { define(prototype, method, function (arg) { return this._invoke(method, arg); }); }); } function AsyncIterator(generator, PromiseImpl) { function invoke(method, arg, resolve, reject) { var record = tryCatch(generator[method], generator, arg); if ("throw" !== record.type) { var result = record.arg, value = result.value; return value && "object" == babelHelpers["typeof"](value) && hasOwn.call(value, "__await") ? PromiseImpl.resolve(value.__await).then(function (value) { invoke("next", value, resolve, reject); }, function (err) { invoke("throw", err, resolve, reject); }) : PromiseImpl.resolve(value).then(function (unwrapped) { result.value = unwrapped, resolve(result); }, function (error) { return invoke("throw", error, resolve, reject); }); } reject(record.arg); } var previousPromise; defineProperty(this, "_invoke", { value: function value(method, arg) { function callInvokeWithMethodAndArg() { return new PromiseImpl(function (resolve, reject) { invoke(method, arg, resolve, reject); }); } return previousPromise = previousPromise ? previousPromise.then(callInvokeWithMethodAndArg, callInvokeWithMethodAndArg) : callInvokeWithMethodAndArg(); } }); } function makeInvokeMethod(innerFn, self, context) { var state = "suspendedStart"; return function (method, arg) { if ("executing" === state) throw new Error("Generator is already running"); if ("completed" === state) { if ("throw" === method) throw arg; return doneResult(); } for (context.method = method, context.arg = arg;;) { var delegate = context.delegate; if (delegate) { var delegateResult = maybeInvokeDelegate(delegate, context); if (delegateResult) { if (delegateResult === ContinueSentinel) continue; return delegateResult; } } if ("next" === context.method) context.sent = context._sent = context.arg;else if ("throw" === context.method) { if ("suspendedStart" === state) throw state = "completed", context.arg; context.dispatchException(context.arg); } else "return" === context.method && context.abrupt("return", context.arg); state = "executing"; var record = tryCatch(innerFn, self, context); if ("normal" === record.type) { if (state = context.done ? "completed" : "suspendedYield", record.arg === ContinueSentinel) continue; return { value: record.arg, done: context.done }; } "throw" === record.type && (state = "completed", context.method = "throw", context.arg = record.arg); } }; } function maybeInvokeDelegate(delegate, context) { var methodName = context.method, method = delegate.iterator[methodName]; if (undefined === method) return context.delegate = null, "throw" === methodName && delegate.iterator["return"] && (context.method = "return", context.arg = undefined, maybeInvokeDelegate(delegate, context), "throw" === context.method) || "return" !== methodName && (context.method = "throw", context.arg = new TypeError("The iterator does not provide a '" + methodName + "' method")), ContinueSentinel; var record = tryCatch(method, delegate.iterator, context.arg); if ("throw" === record.type) return context.method = "throw", context.arg = record.arg, context.delegate = null, ContinueSentinel; var info = record.arg; return info ? info.done ? (context[delegate.resultName] = info.value, context.next = delegate.nextLoc, "return" !== context.method && (context.method = "next", context.arg = undefined), context.delegate = null, ContinueSentinel) : info : (context.method = "throw", context.arg = new TypeError("iterator result is not an object"), context.delegate = null, ContinueSentinel); } function pushTryEntry(locs) { var entry = { tryLoc: locs[0] }; 1 in locs && (entry.catchLoc = locs[1]), 2 in locs && (entry.finallyLoc = locs[2], entry.afterLoc = locs[3]), this.tryEntries.push(entry); } function resetTryEntry(entry) { var record = entry.completion || {}; record.type = "normal", delete record.arg, entry.completion = record; } function Context(tryLocsList) { this.tryEntries = [{ tryLoc: "root" }], tryLocsList.forEach(pushTryEntry, this), this.reset(!0); } function values(iterable) { if (iterable) { var iteratorMethod = iterable[iteratorSymbol]; if (iteratorMethod) return iteratorMethod.call(iterable); if ("function" == typeof iterable.next) return iterable; if (!isNaN(iterable.length)) { var i = -1, next = function next() { for (; ++i < iterable.length;) if (hasOwn.call(iterable, i)) return next.value = iterable[i], next.done = !1, next; return next.value = undefined, next.done = !0, next; }; return next.next = next; } } return { next: doneResult }; } function doneResult() { return { value: undefined, done: !0 }; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, defineProperty(Gp, "constructor", { value: GeneratorFunctionPrototype, configurable: !0 }), defineProperty(GeneratorFunctionPrototype, "constructor", { value: GeneratorFunction, configurable: !0 }), GeneratorFunction.displayName = define(GeneratorFunctionPrototype, toStringTagSymbol, "GeneratorFunction"), exports.isGeneratorFunction = function (genFun) { var ctor = "function" == typeof genFun && genFun.constructor; return !!ctor && (ctor === GeneratorFunction || "GeneratorFunction" === (ctor.displayName || ctor.name)); }, exports.mark = function (genFun) { return Object.setPrototypeOf ? Object.setPrototypeOf(genFun, GeneratorFunctionPrototype) : (genFun.__proto__ = GeneratorFunctionPrototype, define(genFun, toStringTagSymbol, "GeneratorFunction")), genFun.prototype = Object.create(Gp), genFun; }, exports.awrap = function (arg) { return { __await: arg }; }, defineIteratorMethods(AsyncIterator.prototype), define(AsyncIterator.prototype, asyncIteratorSymbol, function () { return this; }), exports.AsyncIterator = AsyncIterator, exports.async = function (innerFn, outerFn, self, tryLocsList, PromiseImpl) { void 0 === PromiseImpl && (PromiseImpl = Promise); var iter = new AsyncIterator(wrap(innerFn, outerFn, self, tryLocsList), PromiseImpl); return exports.isGeneratorFunction(outerFn) ? iter : iter.next().then(function (result) { return result.done ? result.value : iter.next(); }); }, defineIteratorMethods(Gp), define(Gp, toStringTagSymbol, "Generator"), define(Gp, iteratorSymbol, function () { return this; }), define(Gp, "toString", function () { return "[object Generator]"; }), exports.keys = function (val) { var object = Object(val), keys = []; for (var key in object) keys.push(key); return keys.reverse(), function next() { for (; keys.length;) { var key = keys.pop(); if (key in object) return next.value = key, next.done = !1, next; } return next.done = !0, next; }; }, exports.values = values, Context.prototype = { constructor: Context, reset: function reset(skipTempReset) { if (this.prev = 0, this.next = 0, this.sent = this._sent = undefined, this.done = !1, this.delegate = null, this.method = "next", this.arg = undefined, this.tryEntries.forEach(resetTryEntry), !skipTempReset) for (var name in this) "t" === name.charAt(0) && hasOwn.call(this, name) && !isNaN(+name.slice(1)) && (this[name] = undefined); }, stop: function stop() { this.done = !0; var rootRecord = this.tryEntries[0].completion; if ("throw" === rootRecord.type) throw rootRecord.arg; return this.rval; }, dispatchException: function dispatchException(exception) { if (this.done) throw exception; var context = this; function handle(loc, caught) { return record.type = "throw", record.arg = exception, context.next = loc, caught && (context.method = "next", context.arg = undefined), !!caught; } for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i], record = entry.completion; if ("root" === entry.tryLoc) return handle("end"); if (entry.tryLoc <= this.prev) { var hasCatch = hasOwn.call(entry, "catchLoc"), hasFinally = hasOwn.call(entry, "finallyLoc"); if (hasCatch && hasFinally) { if (this.prev < entry.catchLoc) return handle(entry.catchLoc, !0); if (this.prev < entry.finallyLoc) return handle(entry.finallyLoc); } else if (hasCatch) { if (this.prev < entry.catchLoc) return handle(entry.catchLoc, !0); } else { if (!hasFinally) throw new Error("try statement without catch or finally"); if (this.prev < entry.finallyLoc) return handle(entry.finallyLoc); } } } }, abrupt: function abrupt(type, arg) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.tryLoc <= this.prev && hasOwn.call(entry, "finallyLoc") && this.prev < entry.finallyLoc) { var finallyEntry = entry; break; } } finallyEntry && ("break" === type || "continue" === type) && finallyEntry.tryLoc <= arg && arg <= finallyEntry.finallyLoc && (finallyEntry = null); var record = finallyEntry ? finallyEntry.completion : {}; return record.type = type, record.arg = arg, finallyEntry ? (this.method = "next", this.next = finallyEntry.finallyLoc, ContinueSentinel) : this.complete(record); }, complete: function complete(record, afterLoc) { if ("throw" === record.type) throw record.arg; return "break" === record.type || "continue" === record.type ? this.next = record.arg : "return" === record.type ? (this.rval = this.arg = record.arg, this.method = "return", this.next = "end") : "normal" === record.type && afterLoc && (this.next = afterLoc), ContinueSentinel; }, finish: function finish(finallyLoc) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.finallyLoc === finallyLoc) return this.complete(entry.completion, entry.afterLoc), resetTryEntry(entry), ContinueSentinel; } }, "catch": function _catch(tryLoc) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.tryLoc === tryLoc) { var record = entry.completion; if ("throw" === record.type) { var thrown = record.arg; resetTryEntry(entry); } return thrown; } } throw new Error("illegal catch attempt"); }, delegateYield: function delegateYield(iterable, resultName, nextLoc) { return this.delegate = { iterator: values(iterable), resultName: resultName, nextLoc: nextLoc }, "next" === this.method && (this.arg = undefined), ContinueSentinel; } }, exports; }
	var indexStep = 10;
	var calculateSortOrder = function calculateSortOrder(questionIndex) {
	  return String(questionIndex * indexStep + indexStep);
	};
	var parseData = function parseData(chatId) {
	  var _App$voteForm = App.voteForm,
	    questions = _App$voteForm.questions,
	    settings = _App$voteForm.settings;
	  var anonymousVote = settings.anonymousVote,
	    allowRevoking = settings.allowRevoking;
	  var data = {
	    chatId: chatId,
	    'IM_MESSAGE_VOTE_DATA[ANONYMITY]': anonymousVote ? '2' : '1',
	    'IM_MESSAGE_VOTE_DATA[OPTIONS]': allowRevoking ? '1' : '0'
	  };
	  return Object.values(questions).reduce(function (acc, question, questionIndex) {
	    var questionText = question.questionText,
	      allowMultipleAnswers = question.allowMultipleAnswers,
	      answers = question.answers;
	    var questionKey = "IM_MESSAGE_VOTE_DATA[QUESTIONS][".concat(questionIndex, "]");
	    acc["".concat(questionKey, "[QUESTION]")] = questionText;
	    acc["".concat(questionKey, "[C_SORT]")] = calculateSortOrder(questionIndex);
	    acc["".concat(questionKey, "[QUESTION_TYPE]")] = 'text';
	    acc["".concat(questionKey, "[FIELD_TYPE]")] = allowMultipleAnswers ? '1' : '0';
	    Object.values(answers).forEach(function (answer, answerIndex) {
	      var answerKey = "".concat(questionKey, "[ANSWERS][").concat(answerIndex, "]");
	      acc["".concat(answerKey, "[MESSAGE]")] = answer;
	      acc["".concat(answerKey, "[MESSAGE_TYPE]")] = 'text';
	      acc["".concat(answerKey, "[C_SORT]")] = String(answerIndex * 10 + 10);
	      acc["".concat(answerKey, "[FIELD_TYPE]")] = '0';
	    });
	    return acc;
	  }, data);
	};
	var createVote = /*#__PURE__*/function () {
	  var _ref = babelHelpers.asyncToGenerator( /*#__PURE__*/_regeneratorRuntime$1().mark(function _callee(saveButton, chatId) {
	    var parsedData, _App$voteForm2, settings, questions, anonymousVote, allowRevoking, _yield$ajax$runAction, data, messageId, _Object$values, _Object$values2, _Object$values2$, answers, allowMultipleAnswers, answersCount, _response$errors, errors, _errors, firstError;
	    return _regeneratorRuntime$1().wrap(function _callee$(_context) {
	      while (1) switch (_context.prev = _context.next) {
	        case 0:
	          parsedData = parseData(chatId);
	          _context.prev = 1;
	          _App$voteForm2 = App.voteForm, settings = _App$voteForm2.settings, questions = _App$voteForm2.questions;
	          anonymousVote = settings.anonymousVote, allowRevoking = settings.allowRevoking;
	          _context.next = 6;
	          return main_core.ajax.runAction('bitrix:vote.Integration.Im.send', {
	            data: parsedData
	          });
	        case 6:
	          _yield$ajax$runAction = _context.sent;
	          data = _yield$ajax$runAction.data;
	          messageId = data.messageId;
	          vote_analytics.VoteAnalytics.publishVote(anonymousVote, messageId);
	          _Object$values = Object.values(questions), _Object$values2 = babelHelpers.slicedToArray(_Object$values, 1), _Object$values2$ = _Object$values2[0], answers = _Object$values2$.answers, allowMultipleAnswers = _Object$values2$.allowMultipleAnswers;
	          answersCount = Object.keys(answers).length;
	          vote_analytics.VoteAnalytics.setupVote(answersCount, vote_analytics.AnalyticsEvents.setOptions, messageId);
	          vote_analytics.VoteAnalytics.setupVote(allowMultipleAnswers, vote_analytics.AnalyticsEvents.isMultipleChoice, messageId);
	          vote_analytics.VoteAnalytics.setupVote(allowRevoking, vote_analytics.AnalyticsEvents.setCancelVote, messageId);
	          BX.SidePanel.Instance.close();
	          _context.next = 24;
	          break;
	        case 18:
	          _context.prev = 18;
	          _context.t0 = _context["catch"](1);
	          main_core.Dom.removeClass(saveButton, 'ui-btn-wait');
	          errors = (_response$errors = _context.t0 === null || _context.t0 === void 0 ? void 0 : _context.t0.errors) !== null && _response$errors !== void 0 ? _response$errors : [];
	          _errors = babelHelpers.slicedToArray(errors, 1), firstError = _errors[0];
	          if (firstError) {
	            ui_notification.UI.Notification.Center.notify({
	              content: firstError.message
	            });
	          }
	        case 24:
	        case "end":
	          return _context.stop();
	      }
	    }, _callee, null, [[1, 18]]);
	  }));
	  return function createVote(_x, _x2) {
	    return _ref.apply(this, arguments);
	  };
	}();
	var toggleSaveButton = function toggleSaveButton(saveButton) {
	  main_core.Dom.toggleClass(saveButton, 'ui-btn-disabled');
	};
	var init = function init(chatId) {
	  var saveButton = document.getElementById('vote-im-edit-slider-button-create');
	  toggleSaveButton(saveButton);
	  main_core.Event.bind(saveButton, 'click', function () {
	    return createVote(saveButton, chatId);
	  });
	  var validateHandler = function validateHandler() {
	    return toggleSaveButton(saveButton);
	  };
	  main_core_events.EventEmitter.subscribe('vote-creation-form-validate', validateHandler);
	  main_core_events.EventEmitter.subscribeOnce('SidePanel.Slider:onDestroy', function () {
	    main_core_events.EventEmitter.unsubscribe('vote-creation-form-validate', validateHandler);
	  });
	};
	var App = /*#__PURE__*/function () {
	  function App() {
	    babelHelpers.classCallCheck(this, App);
	  }
	  babelHelpers.createClass(App, null, [{
	    key: "mount",
	    value: function mount(formOptions) {
	      var containerId = formOptions.containerId,
	        chatId = formOptions.chatId,
	        rest = babelHelpers.objectWithoutProperties(formOptions, _excluded);
	      var container = document.getElementById(containerId);
	      var app = ui_vue3.BitrixVue.createApp(VoteForm, {
	        options: _objectSpread$2(_objectSpread$2({}, rest), {}, {
	          minQuestionsCount: 1
	        })
	      });
	      App.voteForm = app.mount(container);
	      main_core.ready(function () {
	        return init(chatId);
	      });
	    }
	  }]);
	  return App;
	}();

	exports.App = App;

}((this.BX.Vote.Component = this.BX.Vote.Component || {}),BX,BX.Vue3,BX.Event,BX,BX.UI,BX,BX.Vote));
