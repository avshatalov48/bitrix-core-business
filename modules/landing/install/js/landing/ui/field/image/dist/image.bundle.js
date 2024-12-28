/* eslint-disable */
this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.UI = this.BX.Landing.UI || {};
(function (exports,main_core,landing_loc,landing_main,landing_ui_panel_stylepanel,landing_ui_field_textfield,landing_imageuploader,landing_ui_button_basebutton,landing_ui_button_aiimagebutton,landing_env) {
	'use strict';

	function _regeneratorRuntime() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/facebook/regenerator/blob/main/LICENSE */ _regeneratorRuntime = function _regeneratorRuntime() { return exports; }; var exports = {}, Op = Object.prototype, hasOwn = Op.hasOwnProperty, defineProperty = Object.defineProperty || function (obj, key, desc) { obj[key] = desc.value; }, $Symbol = "function" == typeof Symbol ? Symbol : {}, iteratorSymbol = $Symbol.iterator || "@@iterator", asyncIteratorSymbol = $Symbol.asyncIterator || "@@asyncIterator", toStringTagSymbol = $Symbol.toStringTag || "@@toStringTag"; function define(obj, key, value) { return Object.defineProperty(obj, key, { value: value, enumerable: !0, configurable: !0, writable: !0 }), obj[key]; } try { define({}, ""); } catch (err) { define = function define(obj, key, value) { return obj[key] = value; }; } function wrap(innerFn, outerFn, self, tryLocsList) { var protoGenerator = outerFn && outerFn.prototype instanceof Generator ? outerFn : Generator, generator = Object.create(protoGenerator.prototype), context = new Context(tryLocsList || []); return defineProperty(generator, "_invoke", { value: makeInvokeMethod(innerFn, self, context) }), generator; } function tryCatch(fn, obj, arg) { try { return { type: "normal", arg: fn.call(obj, arg) }; } catch (err) { return { type: "throw", arg: err }; } } exports.wrap = wrap; var ContinueSentinel = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} var IteratorPrototype = {}; define(IteratorPrototype, iteratorSymbol, function () { return this; }); var getProto = Object.getPrototypeOf, NativeIteratorPrototype = getProto && getProto(getProto(values([]))); NativeIteratorPrototype && NativeIteratorPrototype !== Op && hasOwn.call(NativeIteratorPrototype, iteratorSymbol) && (IteratorPrototype = NativeIteratorPrototype); var Gp = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(IteratorPrototype); function defineIteratorMethods(prototype) { ["next", "throw", "return"].forEach(function (method) { define(prototype, method, function (arg) { return this._invoke(method, arg); }); }); } function AsyncIterator(generator, PromiseImpl) { function invoke(method, arg, resolve, reject) { var record = tryCatch(generator[method], generator, arg); if ("throw" !== record.type) { var result = record.arg, value = result.value; return value && "object" == babelHelpers["typeof"](value) && hasOwn.call(value, "__await") ? PromiseImpl.resolve(value.__await).then(function (value) { invoke("next", value, resolve, reject); }, function (err) { invoke("throw", err, resolve, reject); }) : PromiseImpl.resolve(value).then(function (unwrapped) { result.value = unwrapped, resolve(result); }, function (error) { return invoke("throw", error, resolve, reject); }); } reject(record.arg); } var previousPromise; defineProperty(this, "_invoke", { value: function value(method, arg) { function callInvokeWithMethodAndArg() { return new PromiseImpl(function (resolve, reject) { invoke(method, arg, resolve, reject); }); } return previousPromise = previousPromise ? previousPromise.then(callInvokeWithMethodAndArg, callInvokeWithMethodAndArg) : callInvokeWithMethodAndArg(); } }); } function makeInvokeMethod(innerFn, self, context) { var state = "suspendedStart"; return function (method, arg) { if ("executing" === state) throw new Error("Generator is already running"); if ("completed" === state) { if ("throw" === method) throw arg; return doneResult(); } for (context.method = method, context.arg = arg;;) { var delegate = context.delegate; if (delegate) { var delegateResult = maybeInvokeDelegate(delegate, context); if (delegateResult) { if (delegateResult === ContinueSentinel) continue; return delegateResult; } } if ("next" === context.method) context.sent = context._sent = context.arg;else if ("throw" === context.method) { if ("suspendedStart" === state) throw state = "completed", context.arg; context.dispatchException(context.arg); } else "return" === context.method && context.abrupt("return", context.arg); state = "executing"; var record = tryCatch(innerFn, self, context); if ("normal" === record.type) { if (state = context.done ? "completed" : "suspendedYield", record.arg === ContinueSentinel) continue; return { value: record.arg, done: context.done }; } "throw" === record.type && (state = "completed", context.method = "throw", context.arg = record.arg); } }; } function maybeInvokeDelegate(delegate, context) { var methodName = context.method, method = delegate.iterator[methodName]; if (undefined === method) return context.delegate = null, "throw" === methodName && delegate.iterator["return"] && (context.method = "return", context.arg = undefined, maybeInvokeDelegate(delegate, context), "throw" === context.method) || "return" !== methodName && (context.method = "throw", context.arg = new TypeError("The iterator does not provide a '" + methodName + "' method")), ContinueSentinel; var record = tryCatch(method, delegate.iterator, context.arg); if ("throw" === record.type) return context.method = "throw", context.arg = record.arg, context.delegate = null, ContinueSentinel; var info = record.arg; return info ? info.done ? (context[delegate.resultName] = info.value, context.next = delegate.nextLoc, "return" !== context.method && (context.method = "next", context.arg = undefined), context.delegate = null, ContinueSentinel) : info : (context.method = "throw", context.arg = new TypeError("iterator result is not an object"), context.delegate = null, ContinueSentinel); } function pushTryEntry(locs) { var entry = { tryLoc: locs[0] }; 1 in locs && (entry.catchLoc = locs[1]), 2 in locs && (entry.finallyLoc = locs[2], entry.afterLoc = locs[3]), this.tryEntries.push(entry); } function resetTryEntry(entry) { var record = entry.completion || {}; record.type = "normal", delete record.arg, entry.completion = record; } function Context(tryLocsList) { this.tryEntries = [{ tryLoc: "root" }], tryLocsList.forEach(pushTryEntry, this), this.reset(!0); } function values(iterable) { if (iterable) { var iteratorMethod = iterable[iteratorSymbol]; if (iteratorMethod) return iteratorMethod.call(iterable); if ("function" == typeof iterable.next) return iterable; if (!isNaN(iterable.length)) { var i = -1, next = function next() { for (; ++i < iterable.length;) if (hasOwn.call(iterable, i)) return next.value = iterable[i], next.done = !1, next; return next.value = undefined, next.done = !0, next; }; return next.next = next; } } return { next: doneResult }; } function doneResult() { return { value: undefined, done: !0 }; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, defineProperty(Gp, "constructor", { value: GeneratorFunctionPrototype, configurable: !0 }), defineProperty(GeneratorFunctionPrototype, "constructor", { value: GeneratorFunction, configurable: !0 }), GeneratorFunction.displayName = define(GeneratorFunctionPrototype, toStringTagSymbol, "GeneratorFunction"), exports.isGeneratorFunction = function (genFun) { var ctor = "function" == typeof genFun && genFun.constructor; return !!ctor && (ctor === GeneratorFunction || "GeneratorFunction" === (ctor.displayName || ctor.name)); }, exports.mark = function (genFun) { return Object.setPrototypeOf ? Object.setPrototypeOf(genFun, GeneratorFunctionPrototype) : (genFun.__proto__ = GeneratorFunctionPrototype, define(genFun, toStringTagSymbol, "GeneratorFunction")), genFun.prototype = Object.create(Gp), genFun; }, exports.awrap = function (arg) { return { __await: arg }; }, defineIteratorMethods(AsyncIterator.prototype), define(AsyncIterator.prototype, asyncIteratorSymbol, function () { return this; }), exports.AsyncIterator = AsyncIterator, exports.async = function (innerFn, outerFn, self, tryLocsList, PromiseImpl) { void 0 === PromiseImpl && (PromiseImpl = Promise); var iter = new AsyncIterator(wrap(innerFn, outerFn, self, tryLocsList), PromiseImpl); return exports.isGeneratorFunction(outerFn) ? iter : iter.next().then(function (result) { return result.done ? result.value : iter.next(); }); }, defineIteratorMethods(Gp), define(Gp, toStringTagSymbol, "Generator"), define(Gp, iteratorSymbol, function () { return this; }), define(Gp, "toString", function () { return "[object Generator]"; }), exports.keys = function (val) { var object = Object(val), keys = []; for (var key in object) keys.push(key); return keys.reverse(), function next() { for (; keys.length;) { var key = keys.pop(); if (key in object) return next.value = key, next.done = !1, next; } return next.done = !0, next; }; }, exports.values = values, Context.prototype = { constructor: Context, reset: function reset(skipTempReset) { if (this.prev = 0, this.next = 0, this.sent = this._sent = undefined, this.done = !1, this.delegate = null, this.method = "next", this.arg = undefined, this.tryEntries.forEach(resetTryEntry), !skipTempReset) for (var name in this) "t" === name.charAt(0) && hasOwn.call(this, name) && !isNaN(+name.slice(1)) && (this[name] = undefined); }, stop: function stop() { this.done = !0; var rootRecord = this.tryEntries[0].completion; if ("throw" === rootRecord.type) throw rootRecord.arg; return this.rval; }, dispatchException: function dispatchException(exception) { if (this.done) throw exception; var context = this; function handle(loc, caught) { return record.type = "throw", record.arg = exception, context.next = loc, caught && (context.method = "next", context.arg = undefined), !!caught; } for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i], record = entry.completion; if ("root" === entry.tryLoc) return handle("end"); if (entry.tryLoc <= this.prev) { var hasCatch = hasOwn.call(entry, "catchLoc"), hasFinally = hasOwn.call(entry, "finallyLoc"); if (hasCatch && hasFinally) { if (this.prev < entry.catchLoc) return handle(entry.catchLoc, !0); if (this.prev < entry.finallyLoc) return handle(entry.finallyLoc); } else if (hasCatch) { if (this.prev < entry.catchLoc) return handle(entry.catchLoc, !0); } else { if (!hasFinally) throw new Error("try statement without catch or finally"); if (this.prev < entry.finallyLoc) return handle(entry.finallyLoc); } } } }, abrupt: function abrupt(type, arg) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.tryLoc <= this.prev && hasOwn.call(entry, "finallyLoc") && this.prev < entry.finallyLoc) { var finallyEntry = entry; break; } } finallyEntry && ("break" === type || "continue" === type) && finallyEntry.tryLoc <= arg && arg <= finallyEntry.finallyLoc && (finallyEntry = null); var record = finallyEntry ? finallyEntry.completion : {}; return record.type = type, record.arg = arg, finallyEntry ? (this.method = "next", this.next = finallyEntry.finallyLoc, ContinueSentinel) : this.complete(record); }, complete: function complete(record, afterLoc) { if ("throw" === record.type) throw record.arg; return "break" === record.type || "continue" === record.type ? this.next = record.arg : "return" === record.type ? (this.rval = this.arg = record.arg, this.method = "return", this.next = "end") : "normal" === record.type && afterLoc && (this.next = afterLoc), ContinueSentinel; }, finish: function finish(finallyLoc) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.finallyLoc === finallyLoc) return this.complete(entry.completion, entry.afterLoc), resetTryEntry(entry), ContinueSentinel; } }, "catch": function _catch(tryLoc) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.tryLoc === tryLoc) { var record = entry.completion; if ("throw" === record.type) { var thrown = record.arg; resetTryEntry(entry); } return thrown; } } throw new Error("illegal catch attempt"); }, delegateYield: function delegateYield(iterable, resultName, nextLoc) { return this.delegate = { iterator: values(iterable), resultName: resultName, nextLoc: nextLoc }, "next" === this.method && (this.arg = undefined), ContinueSentinel; } }, exports; }
	var Image = /*#__PURE__*/function (_TextField) {
	  babelHelpers.inherits(Image, _TextField);
	  function Image(data) {
	    var _this;
	    babelHelpers.classCallCheck(this, Image);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Image).call(this, data));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "copilotBindElement", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "aiButton", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "copilotContext", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "copilotCategory", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "useCopilotInIframe", false);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "copilotFinishInitPromise", new Promise(function () {}));
	    _this.dimensions = babelHelpers["typeof"](data.dimensions) === "object" ? data.dimensions : null;
	    _this.create2xByDefault = data.create2xByDefault !== false;
	    _this.uploadParams = babelHelpers["typeof"](data.uploadParams) === "object" ? data.uploadParams : {};
	    _this.onValueChangeHandler = data.onValueChange ? data.onValueChange : function () {};
	    _this.type = _this.content.type || "image";
	    _this.contextType = data.contextType || Image.CONTEXT_TYPE_CONTENT;
	    _this.allowClear = data.allowClear;
	    _this.isAiImageAvailable = main_core.Type.isBoolean(data.isAiImageAvailable) ? data.isAiImageAvailable : false;
	    _this.isAiImageActive = main_core.Type.isBoolean(data.isAiImageActive) ? data.isAiImageActive : false;
	    _this.aiUnactiveInfoCode = main_core.Type.isString(data.aiUnactiveInfoCode) ? data.aiUnactiveInfoCode : null;
	    _this.input.innerText = _this.content.src;
	    _this.input.hidden = true;
	    _this.input2x = _this.createInput();
	    _this.input2x.innerText = _this.content.src2x || '';
	    _this.input2x.hidden = true;
	    _this.layout.classList.add("landing-ui-field-image");
	    _this.compactMode = data.compactMode === true;
	    if (_this.compactMode) {
	      _this.layout.classList.add("landing-ui-field-image--compact");
	    }
	    _this.disableAltField = typeof data.disableAltField === "boolean" ? data.disableAltField : false;
	    _this.fileInput = Image.createFileInput(_this.selector);
	    _this.fileInput.addEventListener("change", _this.onFileInputChange.bind(babelHelpers.assertThisInitialized(_this)));
	    // Do not append input to layout! Ticket 172032

	    _this.linkInput = Image.createLinkInput();
	    _this.linkInput.onInputHandler = main_core.Runtime.debounce(_this.onLinkInput.bind(babelHelpers.assertThisInitialized(_this)), 777);
	    _this.dropzone = Image.createDropzone(_this.selector);
	    _this.dropzone.hidden = true;
	    _this.onDragOver = _this.onDragOver.bind(babelHelpers.assertThisInitialized(_this));
	    _this.onDragLeave = _this.onDragLeave.bind(babelHelpers.assertThisInitialized(_this));
	    _this.onDrop = _this.onDrop.bind(babelHelpers.assertThisInitialized(_this));
	    _this.dropzone.addEventListener("dragover", _this.onDragOver);
	    _this.dropzone.addEventListener("dragleave", _this.onDragLeave);
	    _this.dropzone.addEventListener("drop", _this.onDrop);
	    _this.clearButton = Image.createClearButton();
	    _this.clearButton.on("click", _this.onClearClick.bind(babelHelpers.assertThisInitialized(_this)));
	    _this.preview = Image.createImagePreview();
	    _this.preview.appendChild(_this.clearButton.layout);
	    _this.preview.style.backgroundImage = "url(" + _this.input.innerText.trim() + ")";
	    _this.onImageDragEnter = _this.onImageDragEnter.bind(babelHelpers.assertThisInitialized(_this));
	    _this.preview.addEventListener("dragenter", _this.onImageDragEnter);
	    _this.loader = new BX.Loader({
	      target: _this.preview
	    });
	    _this.icon = Image.createIcon();
	    _this.image = Image.createImageLayout();
	    _this.image.appendChild(_this.preview);
	    _this.image.appendChild(_this.icon);
	    _this.image.dataset.fileid = _this.content.id;
	    _this.image.dataset.fileid2x = _this.content.id2x;
	    _this.hiddenImage = main_core.Dom.create("img", {
	      props: {
	        className: "landing-ui-field-image-hidden"
	      }
	    });
	    if (main_core.Type.isPlainObject(_this.content) && "src" in _this.content) {
	      _this.hiddenImage.src = _this.content.src;
	    }
	    _this.altField = Image.createAltField();
	    _this.altField.setValue(_this.content.alt);
	    _this.left = Image.createLeftLayout();
	    _this.left.appendChild(_this.dropzone);
	    _this.left.appendChild(_this.image);
	    _this.left.appendChild(_this.hiddenImage);
	    if (_this.description) {
	      _this.left.appendChild(_this.description);
	    }
	    _this.left.appendChild(_this.altField.layout);
	    _this.left.appendChild(_this.linkInput.layout);
	    _this.uploadButton = Image.createUploadButton(_this.compactMode);
	    _this.uploadButton.on("click", _this.onUploadClick.bind(babelHelpers.assertThisInitialized(_this)));
	    _this.editButton = Image.createEditButton();
	    _this.editButton.on("click", _this.onEditClick.bind(babelHelpers.assertThisInitialized(_this)));
	    _this.right = Image.createRightLayout();

	    // ai images
	    if (_this.isAiImageAvailable && (_this.type === "background" || _this.type === "image")) {
	      _this.useCopilotInIframe = _this.uploadParams.action === 'Landing::uploadFile';
	      _this.defineCopilotCategory();
	      var copilotOptions = {
	        moduleId: 'landing',
	        contextId: _this.getAiContext(),
	        category: _this.copilotCategory,
	        useText: false,
	        useImage: true,
	        autoHide: true
	      };
	      _this.copilotContext = _this.useCopilotInIframe ? BX : top.BX;
	      _this.stylePanel = landing_ui_panel_stylepanel.StylePanel.getInstance().layout;
	      _this.stylePanelContent = landing_ui_panel_stylepanel.StylePanel.getInstance().content;
	      _this.copilotContext.Runtime.loadExtension('ai.copilot').then(function (_ref) {
	        var Copilot = _ref.Copilot,
	          CopilotEvents = _ref.CopilotEvents;
	        _this.imageCopilot = new Copilot(copilotOptions);
	        _this.imageCopilot.subscribe(CopilotEvents.FINISH_INIT, _this.imageCopilotFinishInitHandler.bind(babelHelpers.assertThisInitialized(_this)));
	        _this.imageCopilot.subscribe(CopilotEvents.IMAGE_COMPLETION_RESULT, _this.imageCopilotImageResultHandler.bind(babelHelpers.assertThisInitialized(_this)));
	        _this.imageCopilot.subscribe(CopilotEvents.IMAGE_SAVE, _this.imageCopilotSaveImageHandler.bind(babelHelpers.assertThisInitialized(_this)));
	        _this.imageCopilot.subscribe(CopilotEvents.IMAGE_CANCEL, _this.imageCopilotCancelImageHandler.bind(babelHelpers.assertThisInitialized(_this)));
	        main_core.Event.bind(_this.stylePanelContent, 'scroll', _this.onScrollContentPanel.bind(babelHelpers.assertThisInitialized(_this)));
	        main_core.Event.bind(_this.stylePanel, 'click', _this.onClickStylePanel.bind(babelHelpers.assertThisInitialized(_this)));
	        main_core.Event.EventEmitter.subscribe('BX.Landing.UI.Panel.ContentEdit:onClick', _this.onClickContentPanel.bind(babelHelpers.assertThisInitialized(_this)));
	        main_core.Event.EventEmitter.subscribe('BX.Landing.UI.Panel.BasePanel:onHide', _this.closeCopilot.bind(babelHelpers.assertThisInitialized(_this)));
	        main_core.Event.EventEmitter.subscribe('BX.Landing.UI.Panel.BasePanel:onClick', _this.onClickContentPanel.bind(babelHelpers.assertThisInitialized(_this)));
	        main_core.Event.EventEmitter.subscribe('BX.Landing.UI.Panel.BasePanel:onScroll', _this.onScrollContentPanel.bind(babelHelpers.assertThisInitialized(_this)));
	        _this.imageCopilot.init();
	      });
	      _this.aiButton = Image.createAiButton(_this.compactMode);
	      BX.bind(_this.aiButton.layout, 'click', function () {
	        if (_this.isAiImageActive) {
	          _this.onAiClick();
	        } else if (_this.aiUnactiveInfoCode && _this.aiUnactiveInfoCode.length > 0) {
	          BX.UI.InfoHelper.show(_this.aiUnactiveInfoCode);
	        }
	      });
	      _this.right.appendChild(_this.aiButton.layout);
	    }
	    _this.right.appendChild(_this.uploadButton.layout);
	    _this.right.appendChild(_this.editButton.layout);
	    _this.form = Image.createForm();
	    _this.form.appendChild(_this.left);
	    _this.form.appendChild(_this.right);
	    _this.layout.appendChild(_this.form);
	    _this.enableTextOnly();
	    if (!_this.input.innerText.trim() || _this.input.innerText.trim() === window.location.toString()) {
	      _this.showDropzone();
	    }
	    if (_this.disableAltField) {
	      _this.altField.layout.hidden = true;
	      _this.altField.layout.style.display = "none";
	      _this.altField.layout.classList.add("landing-ui-hide");
	    }
	    if (_this.content.type === "icon") {
	      _this.type = "icon";
	      _this.classList = _this.content.classList;
	      _this.showPreview();
	      _this.altField.layout.hidden = true;
	      main_core.Dom.addClass(_this.layout, 'landing-ui-field-image-icon');
	    }
	    _this.makeAsLinkWrapper = main_core.Dom.create("div", {
	      props: {
	        className: "landing-ui-field-image-make-as-link-wrapper"
	      },
	      children: [main_core.Dom.create('div', {
	        props: {
	          className: "landing-ui-field-image-make-as-link-button"
	        },
	        children: []
	      })]
	    });
	    _this.url = new BX.Landing.UI.Field.Link({
	      content: _this.content.url || {
	        text: '',
	        href: ''
	      },
	      options: {
	        siteId: landing_main.Main.getInstance().options.site_id,
	        landingId: landing_main.Main.getInstance().id
	      },
	      contentRoot: _this.contentRoot
	    });
	    _this.isDisabledUrl = _this.content.url && _this.content.url.enabled === false;
	    if (_this.isDisabledUrl) {
	      _this.content.url.href = '';
	    }
	    _this.url.left.hidden = true;
	    _this.makeAsLinkWrapper.appendChild(_this.url.layout);
	    if (!data.disableLink) {
	      _this.layout.appendChild(_this.makeAsLinkWrapper);
	    }
	    _this.content = _this.getValue();
	    BX.DOM.write(function () {
	      this.adjustPreviewBackgroundSize();
	    }.bind(babelHelpers.assertThisInitialized(_this)));
	    if (_this.getValue().type === "background" || _this.allowClear) {
	      _this.clearButton.layout.classList.add("landing-ui-show");
	    }
	    _this.uploader = new landing_imageuploader.ImageUploader({
	      uploadParams: _this.uploadParams,
	      additionalParams: {
	        context: 'imageeditor'
	      },
	      dimensions: _this.dimensions,
	      sizes: ['1x', '2x'],
	      allowSvg: landing_main.Main.getInstance().options.allow_svg === true
	    });
	    _this.adjustEditButtonState();
	    return _this;
	  }

	  /**
	   * Creates file input
	   * @return {Element}
	   */
	  babelHelpers.createClass(Image, [{
	    key: "onInputInput",
	    value: function onInputInput() {
	      this.preview.src = this.input.innerText.trim();
	    }
	  }, {
	    key: "onImageDragEnter",
	    value: function onImageDragEnter(event) {
	      event.preventDefault();
	      event.stopPropagation();
	      if (!this.imageHidden) {
	        this.showDropzone();
	        this.imageHidden = true;
	      }
	    }
	  }, {
	    key: "onDragOver",
	    value: function onDragOver(event) {
	      event.preventDefault();
	      event.stopPropagation();
	      this.dropzone.classList.add("landing-ui-active");
	    }
	  }, {
	    key: "onDragLeave",
	    value: function onDragLeave(event) {
	      event.preventDefault();
	      event.stopPropagation();
	      this.dropzone.classList.remove("landing-ui-active");
	      if (this.imageHidden) {
	        this.imageHidden = false;
	        this.showPreview();
	      }
	    }
	  }, {
	    key: "onDrop",
	    value: function onDrop(event) {
	      event.preventDefault();
	      event.stopPropagation();
	      this.dropzone.classList.remove("landing-ui-active");
	      this.onFileChange(event.dataTransfer.files[0]);
	      this.imageHidden = false;
	    }
	  }, {
	    key: "onFileChange",
	    value: function onFileChange(file) {
	      this.showLoader();
	      this.upload(file).then(this.setValue.bind(this)).then(this.hideLoader.bind(this))["catch"](function (err) {
	        console.error(err);
	        this.hideLoader();
	      }.bind(this));
	    }
	  }, {
	    key: "onFileInputChange",
	    value: function onFileInputChange(event) {
	      this.onFileChange(event.currentTarget.files[0]);
	    }
	  }, {
	    key: "onAiClick",
	    value: function () {
	      var _onAiClick = babelHelpers.asyncToGenerator( /*#__PURE__*/_regeneratorRuntime().mark(function _callee() {
	        return _regeneratorRuntime().wrap(function _callee$(_context) {
	          while (1) switch (_context.prev = _context.next) {
	            case 0:
	              _context.next = 2;
	              return this.copilotFinishInitPromise;
	            case 2:
	              this.showCopilot();
	            case 3:
	            case "end":
	              return _context.stop();
	          }
	        }, _callee, this);
	      }));
	      function onAiClick() {
	        return _onAiClick.apply(this, arguments);
	      }
	      return onAiClick;
	    }()
	    /**
	     * Return AI image button, if exists (if allow)
	     */
	  }, {
	    key: "getAiButton",
	    value: function getAiButton() {
	      return this.aiButton;
	    }
	  }, {
	    key: "showCopilot",
	    value: function showCopilot() {
	      this.copilotBindElement = this.aiButton.layout;
	      var offsetY = 3;
	      var copilotBindElementPosition = this.copilotBindElement.getBoundingClientRect();
	      this.imageCopilot.show({
	        width: 500,
	        bindElement: {
	          top: copilotBindElementPosition.bottom + offsetY,
	          left: copilotBindElementPosition.left
	        }
	      });
	      this.imageCopilot.adjustPosition({});
	    }
	  }, {
	    key: "imageCopilotFinishInitHandler",
	    value: function imageCopilotFinishInitHandler() {
	      this.copilotFinishInitPromise = Promise.resolve();
	    }
	  }, {
	    key: "imageCopilotImageResultHandler",
	    value: function imageCopilotImageResultHandler(e) {
	      var data = e.getData();
	      this.imageCopilotUrl = encodeURI(data.imageUrl);
	      if (this.copilotBindElement === this.dropzone) {
	        this.showPreview();
	      }
	      main_core.Dom.addClass(this.preview, '--shown');
	      main_core.Dom.style(this.preview, 'background-image', "url(\"".concat(this.imageCopilotUrl, "\")"));
	      main_core.Dom.style(this.preview, 'background-size', 'contain');
	    }
	  }, {
	    key: "imageCopilotSaveImageHandler",
	    value: function imageCopilotSaveImageHandler() {
	      var _this2 = this;
	      var url = this.imageCopilotUrl;
	      var proxyUrl = BX.util.add_url_param("/bitrix/tools/landing/proxy.php", {
	        "sessid": BX.bitrix_sessid(),
	        "url": url
	      });
	      BX.Landing.Utils.urlToBlob(proxyUrl).then(function (blob) {
	        blob.lastModifiedDate = new Date();
	        blob.name = url.slice(url.lastIndexOf('/') + 1);
	        return blob;
	      }).then(this.upload.bind(this)).then(this.setValue.bind(this)).then(this.hideLoader.bind(this)).then(function () {
	        main_core.Dom.removeClass(_this2.preview, '--shown');
	      });
	      this.closeCopilot();
	    }
	  }, {
	    key: "imageCopilotCancelImageHandler",
	    value: function imageCopilotCancelImageHandler() {
	      if (this.copilotBindElement === this.dropzone) {
	        this.showDropzone();
	      } else {
	        main_core.Dom.removeClass(this.preview, '--shown');
	        main_core.Dom.style(this.preview, 'background-image', "url(\"".concat(this.input.innerText.trim(), "\")"));
	      }
	    }
	  }, {
	    key: "closeCopilot",
	    value: function closeCopilot() {
	      if (this.imageCopilot.isShown()) {
	        this.imageCopilot.hide();
	        main_core.Event.EventEmitter.unsubscribe('BX.Landing.UI.Panel.BasePanel:onHide', this.closeCopilot.bind(this));
	        main_core.Event.unbind(this.stylePanel, 'click', this.onClickStylePanel.bind(this));
	      }
	    }
	  }, {
	    key: "onClickStylePanel",
	    value: function onClickStylePanel(event) {
	      if (!this.aiButton.layout.contains(event.target)) {
	        this.closeCopilot();
	      }
	    }
	  }, {
	    key: "onClickContentPanel",
	    value: function onClickContentPanel(event) {
	      var target = event.getData().event.target;
	      if (!this.aiButton.layout.contains(target)) {
	        this.closeCopilot();
	      }
	    }
	  }, {
	    key: "onScrollContentPanel",
	    value: function onScrollContentPanel() {
	      var _this$imageCopilot;
	      if (Boolean((_this$imageCopilot = this.imageCopilot) === null || _this$imageCopilot === void 0 ? void 0 : _this$imageCopilot.isShown()) === false) {
	        return;
	      }
	      if (this.imageCopilot.getPosition().inputField.top < 133) {
	        this.imageCopilot.adjustPosition({
	          hide: true
	        });
	      } else {
	        this.imageCopilot.adjustPosition({
	          hide: false
	        });
	      }
	    }
	  }, {
	    key: "defineCopilotCategory",
	    value: function defineCopilotCategory() {
	      this.copilotCategory = this.contextType === 'style' ? 'landing_designer' : this.useCopilotInIframe ? 'landing_setting' : 'landing_editor';
	    }
	  }, {
	    key: "getAiContext",
	    value: function getAiContext() {
	      return 'image_site_' + landing_env.Env.getInstance().getSiteId();
	    }
	  }, {
	    key: "onUploadClick",
	    value: function onUploadClick(event) {
	      this.bindElement = event.currentTarget;
	      event.preventDefault();
	      if (!this.uploadMenu) {
	        this.uploadMenu = BX.Main.MenuManager.create({
	          id: "upload_" + this.selector + +new Date(),
	          bindElement: this.bindElement,
	          bindOptions: {
	            forceBindPosition: true
	          },
	          items: [{
	            text: landing_loc.Loc.getMessage("LANDING_IMAGE_UPLOAD_MENU_UNSPLASH"),
	            onclick: this.onUnsplashShow.bind(this)
	          }, {
	            text: landing_loc.Loc.getMessage("LANDING_IMAGE_UPLOAD_MENU_GOOGLE"),
	            onclick: this.onGoogleShow.bind(this)
	          },
	          // {
	          // 	text: Loc.getMessage("LANDING_IMAGE_UPLOAD_MENU_PARTNER"),
	          // 	className: "landing-ui-disabled"
	          // },
	          {
	            text: landing_loc.Loc.getMessage("LANDING_IMAGE_UPLOAD_MENU_UPLOAD"),
	            onclick: this.onUploadShow.bind(this)
	          }, {
	            text: landing_loc.Loc.getMessage("LANDING_IMAGE_UPLOAD_MENU_LINK"),
	            onclick: this.onLinkShow.bind(this)
	          }],
	          events: {
	            onPopupClose: function () {
	              this.bindElement.classList.remove("landing-ui-active");
	              if (this.uploadMenu) {
	                this.uploadMenu.destroy();
	                this.uploadMenu = null;
	              }
	            }.bind(this)
	          },
	          targetContainer: this.contentRoot
	        });
	        if (!this.contentRoot) {
	          this.bindElement.parentNode.appendChild(this.uploadMenu.popupWindow.popupContainer);
	        }
	      }
	      this.bindElement.classList.add("landing-ui-active");
	      this.uploadMenu.toggle();
	      if (!this.contentRoot && this.uploadMenu) {
	        var rect = BX.pos(this.bindElement, this.bindElement.parentNode);
	        this.uploadMenu.popupWindow.popupContainer.style.top = rect.bottom + "px";
	        this.uploadMenu.popupWindow.popupContainer.style.left = "auto";
	        this.uploadMenu.popupWindow.popupContainer.style.right = "5px";
	      }
	    }
	  }, {
	    key: "onUnsplashShow",
	    value: function onUnsplashShow() {
	      this.uploadMenu.close();
	      BX.Landing.UI.Panel.Image.getInstance().show("unsplash", this.dimensions, this.loader, this.uploadParams).then(this.upload.bind(this)).then(this.setValue.bind(this)).then(this.hideLoader.bind(this))["catch"](function (err) {
	        console.error(err);
	        this.hideLoader();
	      }.bind(this));
	    }
	  }, {
	    key: "onGoogleShow",
	    value: function onGoogleShow() {
	      this.uploadMenu.close();
	      BX.Landing.UI.Panel.Image.getInstance().show("google", this.dimensions, this.loader, this.uploadParams).then(this.upload.bind(this)).then(this.setValue.bind(this)).then(this.hideLoader.bind(this))["catch"](function (err) {
	        BX.Landing.ErrorManager.getInstance().add({
	          type: 'error',
	          action: 'BAD_IMAGE',
	          hideSupportLink: true
	        });
	        console.error(err);
	        this.hideLoader();
	      }.bind(this));
	    }
	  }, {
	    key: "onUploadShow",
	    value: function onUploadShow() {
	      this.uploadMenu.close();
	      this.fileInput.click();
	    }
	  }, {
	    key: "onLinkShow",
	    value: function onLinkShow() {
	      this.uploadMenu.close();
	      this.showLinkField();
	      this.linkInput.setValue("");
	    }
	  }, {
	    key: "onEditClick",
	    value: function onEditClick(event) {
	      event.preventDefault();
	      this.edit({
	        src: this.hiddenImage.src
	      });
	    }
	  }, {
	    key: "onClearClick",
	    value: function onClearClick(event) {
	      event.preventDefault();
	      this.setValue({
	        src: ""
	      });
	      this.fileInput.value = "";
	      this.showDropzone();
	    }
	  }, {
	    key: "showDropzone",
	    value: function showDropzone() {
	      this.dropzone.hidden = false;
	      this.image.hidden = true;
	      this.altField.layout.hidden = true;
	      this.linkInput.layout.hidden = true;
	    }
	  }, {
	    key: "showPreview",
	    value: function showPreview() {
	      this.dropzone.hidden = true;
	      this.image.hidden = false;
	      this.altField.layout.hidden = false;
	      this.linkInput.layout.hidden = true;
	    }
	  }, {
	    key: "showLinkField",
	    value: function showLinkField() {
	      this.dropzone.hidden = true;
	      this.image.hidden = true;
	      this.altField.layout.hidden = true;
	      this.linkInput.layout.hidden = false;
	    }
	  }, {
	    key: "onLinkInput",
	    value: function onLinkInput(value) {
	      var _this3 = this;
	      var tmpImage = main_core.Dom.create("img");
	      tmpImage.src = value;
	      tmpImage.onload = function () {
	        _this3.showPreview();
	        _this3.setValue({
	          src: value,
	          src2x: value
	        });
	      };
	    }
	  }, {
	    key: "showLoader",
	    value: function showLoader() {
	      if (this.dropzone && !this.dropzone.hidden) {
	        this.loader.show(this.dropzone);
	        return;
	      }
	      this.loader.show(this.preview);
	    }
	  }, {
	    key: "hideLoader",
	    value: function hideLoader() {
	      this.loader.hide();
	    }
	    /**
	     * Handles click event on input field
	     * @param {MouseEvent} event
	     */
	  }, {
	    key: "onInputClick",
	    value: function onInputClick(event) {
	      event.preventDefault();
	    }
	    /**
	     * @inheritDoc
	     * @return {boolean}
	     */
	  }, {
	    key: "isChanged",
	    value: function isChanged() {
	      var lastValue = BX.Landing.Utils.clone(this.content);
	      var currentValue = BX.Landing.Utils.clone(this.getValue());
	      if (lastValue.url && main_core.Type.isString(lastValue.url)) {
	        lastValue.url = BX.Landing.Utils.decodeDataValue(lastValue.url);
	      }
	      if (currentValue.url && main_core.Type.isString(currentValue.url)) {
	        currentValue.url = BX.Landing.Utils.decodeDataValue(currentValue.url);
	      }
	      return JSON.stringify(lastValue) !== JSON.stringify(currentValue);
	    }
	    /**
	     * Adjusts preview background image size
	     */
	  }, {
	    key: "adjustPreviewBackgroundSize",
	    value: function adjustPreviewBackgroundSize() {
	      var img = main_core.Dom.create("img", {
	        attrs: {
	          src: this.getValue().src
	        }
	      });
	      img.onload = function () {
	        var preview = this.preview.getBoundingClientRect();
	        var position = "cover";
	        if (img.width > preview.width || img.height > preview.height) {
	          position = "contain";
	        }
	        if (img.width < preview.width && img.height < preview.height) {
	          position = "auto";
	        }
	        BX.DOM.write(function () {
	          this.preview.style.backgroundSize = position;
	        }.bind(this));
	      }.bind(this);
	    }
	    /**
	     * @param {object} value
	     * @param {boolean} [preventEvent = false]
	     */
	  }, {
	    key: "setValue",
	    value: function setValue(value, preventEvent) {
	      if (value.type !== "icon") {
	        if (!value || !value.src) {
	          this.input.innerText = "";
	          this.input2x.innerText = "";
	          this.preview.removeAttribute("style");
	          this.input.dataset.ext = "";
	          this.showDropzone();
	        } else {
	          this.input.innerText = value.src;
	          this.input2x.innerText = value.src2x || '';
	          this.preview.style.backgroundImage = "url(\"" + (value.src2x || value.src) + "\")";
	          this.preview.id = BX.util.getRandomString();
	          this.hiddenImage.src = value.src2x || value.src;
	          this.showPreview();
	        }
	        this.image.dataset.fileid = value && value.id ? value.id : -1;
	        this.image.dataset.fileid2x = value && value.id2x ? value.id2x : -1;
	        if (value.type === 'image') {
	          this.altField.layout.hidden = false;
	          this.altField.setValue(value.alt);
	        }
	        this.classList = [];
	      } else {
	        this.preview.style.backgroundImage = null;
	        this.classList = value.classList;
	        this.icon.innerHTML = "<span class=\"" + value.classList.join(" ") + "\"></span>";
	        this.showPreview();
	        this.type = "icon";
	        this.altField.layout.hidden = true;
	        this.altField.setValue("");
	        this.input.innerText = "";
	      }
	      if (value.url) {
	        this.url.setValue(value.url);
	      }
	      this.adjustPreviewBackgroundSize();
	      this.adjustEditButtonState();
	      this.hideLoader();
	      this.onValueChangeHandler(this);
	      BX.fireEvent(this.layout, "input");
	      var event = new BX.Event.BaseEvent({
	        data: {
	          value: this.getValue()
	        },
	        compatData: [this.getValue()]
	      });
	      if (!preventEvent) {
	        this.emit('change', event);
	      }
	    }
	  }, {
	    key: "adjustEditButtonState",
	    value: function adjustEditButtonState() {
	      var value = this.getValue();
	      if (BX.Type.isStringFilled(value.src)) {
	        this.editButton.enable();
	      } else {
	        this.editButton.disable();
	      }
	    }
	  }, {
	    key: "reset",
	    value: function reset() {
	      this.setValue({
	        type: this.getValue().type,
	        id: -1,
	        src: "",
	        alt: ""
	      });
	    }
	    /**
	     * Gets field value
	     * @return {{src, [alt]: string, [title]: string, [url]: string, [type]: string}}
	     */
	  }, {
	    key: "getValue",
	    value: function getValue() {
	      var value = {
	        type: "",
	        src: "",
	        alt: "",
	        url: ""
	      };
	      var fileId = parseInt(this.image.dataset.fileid);
	      if (main_core.Type.isNumber(fileId) && fileId > 0) {
	        value.id = fileId;
	      }
	      var fileId2x = parseInt(this.image.dataset.fileid2x);
	      if (main_core.Type.isNumber(fileId2x) && fileId2x > 0) {
	        value.id2x = fileId2x;
	      }
	      var src2x = this.input2x.innerText.trim();
	      if (main_core.Type.isString(src2x) && src2x) {
	        value.src2x = src2x;
	      }
	      if (this.type === "background" || this.type === "image") {
	        value.src = this.input.innerText.trim();
	      }
	      if (this.type === "background") {
	        value.type = "background";
	      }
	      if (this.type === "image") {
	        value.type = "image";
	        value.alt = this.altField.getValue();
	      }
	      if (this.type === "icon") {
	        value.type = "icon";
	        value.classList = this.classList;
	      }
	      value.url = Object.assign({}, this.url.getValue(), {
	        enabled: true
	      });
	      return value;
	    }
	  }, {
	    key: "edit",
	    value: function edit(data) {
	      parent.BX.Landing.ImageEditor.edit({
	        image: data.src,
	        dimensions: this.dimensions
	      }).then(function (file) {
	        var ext = file.name.split('.').pop();
	        if (!file.name.includes('.') || ext.length > 4) {
	          ext = ".".concat(file.name.split('_').pop());
	          file.name = file.name + ext;
	        }
	        return this.upload(file, {
	          context: "imageEditor"
	        });
	      }.bind(this)).then(function (result) {
	        this.setValue(result);
	      }.bind(this));

	      // Analytics hack
	      var tmpImage = document.createElement('img');
	      var imageSrc = "/bitrix/images/landing/close.svg";
	      imageSrc = BX.util.add_url_param(imageSrc, {
	        action: "openImageEditor"
	      });
	      tmpImage.src = imageSrc + "?" + +new Date();
	    }
	    /**
	     * @param {File|Blob} file
	     * @param {object} [additionalParams]
	     */
	  }, {
	    key: "upload",
	    value: function upload(file, additionalParams) {
	      if (file.type && (file.type.includes('text') || file.type.includes('html'))) {
	        BX.Landing.ErrorManager.getInstance().add({
	          type: "error",
	          action: "BAD_IMAGE"
	        });
	        return Promise.reject({
	          type: "error",
	          action: "BAD_IMAGE"
	        });
	      }
	      this.showLoader();
	      var isPng = main_core.Type.isStringFilled(file.type) && file.type.includes('png');
	      var isSvg = main_core.Type.isStringFilled(file.type) && file.type.includes('svg');
	      var checkSize = new Promise(function (resolve) {
	        var sizes = isPng || isSvg ? ['2x'] : ['1x', '2x'];
	        if (this.create2xByDefault === false) {
	          var image = document.createElement('img');
	          var objectUrl = URL.createObjectURL(file);
	          var dimensions = this.dimensions;
	          image.onload = function () {
	            URL.revokeObjectURL(objectUrl);
	            if ((this.width >= dimensions.width || this.height >= dimensions.height || this.width >= dimensions.maxWidth || this.height >= dimensions.maxHeight) === false) {
	              sizes = isPng || isSvg ? ['2x'] : ['1x'];
	            }
	            resolve(sizes);
	          };
	          image.src = objectUrl;
	        } else {
	          resolve(sizes);
	        }
	      }.bind(this));
	      return checkSize.then(function (allowedSizes) {
	        var sizes = function () {
	          if (this.create2xByDefault === false && BX.Type.isArrayFilled(allowedSizes)) {
	            return allowedSizes;
	          }
	          return isPng || isSvg ? ['2x'] : ['1x', '2x'];
	        }.bind(this)();
	        return this.uploader.setSizes(sizes).upload(file, additionalParams).then(function (result) {
	          this.hideLoader();
	          if (sizes.length === 1) {
	            return result[0];
	          }
	          return Object.assign({}, result[0], {
	            src2x: result[1].src,
	            id2x: result[1].id
	          });
	        }.bind(this));
	      }.bind(this));
	    }
	  }], [{
	    key: "createFileInput",
	    value: function createFileInput(id) {
	      return main_core.Dom.create("input", {
	        props: {
	          className: "landing-ui-field-image-dropzone-input"
	        },
	        attrs: {
	          accept: "image/*",
	          type: "file",
	          id: "file_" + id,
	          name: "picture"
	        }
	      });
	    }
	    /**
	     * Creates link input field
	     * @return {TextField}
	     */
	  }, {
	    key: "createLinkInput",
	    value: function createLinkInput() {
	      var field = new landing_ui_field_textfield.TextField({
	        id: "path_to_image",
	        placeholder: landing_loc.Loc.getMessage("LANDING_IMAGE_UPLOAD_MENU_LINK_LABEL")
	      });
	      field.enableTextOnly();
	      field.layout.hidden = true;
	      return field;
	    }
	    /**
	     * Creates dropzone
	     * @param {string} id
	     * @return {Element}
	     */
	  }, {
	    key: "createDropzone",
	    value: function createDropzone(id) {
	      return main_core.Dom.create("label", {
	        props: {
	          className: "landing-ui-field-image-dropzone"
	        },
	        children: [main_core.Dom.create("div", {
	          props: {
	            className: "landing-ui-field-image-dropzone-text"
	          },
	          html: "<div class=\"landing-ui-field-image-dropzone-title\">" + landing_loc.Loc.getMessage("LANDING_IMAGE_DROPZONE_TITLE") + "</div>" + "<div class=\"landing-ui-field-image-dropzone-subtitle\">" + landing_loc.Loc.getMessage("LANDING_IMAGE_DROPZONE_SUBTITLE") + "</div>"
	        })],
	        attrs: {
	          "for": "file_" + id
	        }
	      });
	    }
	    /**
	     * Creates clear button
	     * @return {BaseButton}
	     */
	  }, {
	    key: "createClearButton",
	    value: function createClearButton() {
	      return new landing_ui_button_basebutton.BaseButton("clear", {
	        className: "landing-ui-field-image-action-button-clear"
	      });
	    }
	    /**
	     * Creates image preview
	     * @return {Element}
	     */
	  }, {
	    key: "createImagePreview",
	    value: function createImagePreview() {
	      return main_core.Dom.create("div", {
	        props: {
	          className: "landing-ui-field-image-preview-inner"
	        }
	      });
	    }
	    /**
	     * Creates icon layout
	     * @return {Element}
	     */
	  }, {
	    key: "createIcon",
	    value: function createIcon() {
	      return main_core.Dom.create("span", {
	        props: {
	          className: "landing-ui-field-image-preview-icon"
	        }
	      });
	    }
	    /**
	     * Creates image layout
	     * @return {Element}
	     */
	  }, {
	    key: "createImageLayout",
	    value: function createImageLayout() {
	      return main_core.Dom.create("div", {
	        props: {
	          className: "landing-ui-field-image-preview"
	        }
	      });
	    }
	    /**
	     * Creates alt field
	     * @return {TextField}
	     */
	  }, {
	    key: "createAltField",
	    value: function createAltField() {
	      var field = new landing_ui_field_textfield.TextField({
	        placeholder: landing_loc.Loc.getMessage("LANDING_FIELD_IMAGE_ALT_PLACEHOLDER"),
	        className: "landing-ui-field-image-alt",
	        textOnly: true
	      });
	      return field;
	    }
	    /**
	     * Creates left layout
	     * @return {Element}
	     */
	  }, {
	    key: "createLeftLayout",
	    value: function createLeftLayout() {
	      return main_core.Dom.create("div", {
	        props: {
	          className: "landing-ui-field-image-left"
	        }
	      });
	    }
	    /**
	     * Creates upload button
	     * @return {BaseButton}
	     */
	  }, {
	    key: "createAiButton",
	    value: function createAiButton() {
	      var compactMode = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : false;
	      return new landing_ui_button_aiimagebutton.AiImageButton("ai", {
	        text: 'CoPilot',
	        className: "landing-ui-field-image-ai-button" + (compactMode ? ' --compact' : '')
	      });
	    }
	    /**
	     * Creates ia create button
	     * @return {BaseButton}
	     */
	  }, {
	    key: "createUploadButton",
	    value: function createUploadButton() {
	      return new landing_ui_button_basebutton.BaseButton("upload", {
	        text: landing_loc.Loc.getMessage("LANDING_FIELD_IMAGE_UPLOAD_BUTTON"),
	        className: "landing-ui-field-image-action-button"
	      });
	    }
	    /**
	     * Creates edit button
	     * @return {BaseButton}
	     */
	  }, {
	    key: "createEditButton",
	    value: function createEditButton() {
	      var field = new landing_ui_button_basebutton.BaseButton("edit", {
	        text: landing_loc.Loc.getMessage("LANDING_FIELD_IMAGE_EDIT_BUTTON"),
	        className: "landing-ui-field-image-action-button"
	      });
	      return field;
	    }
	    /**
	     * Creates right layout
	     * @return {Element}
	     */
	  }, {
	    key: "createRightLayout",
	    value: function createRightLayout() {
	      return main_core.Dom.create("div", {
	        props: {
	          className: "landing-ui-field-image-right"
	        }
	      });
	    }
	    /**
	     * Creates form
	     * @return {Element}
	     */
	  }, {
	    key: "createForm",
	    value: function createForm() {
	      return main_core.Dom.create("form", {
	        props: {
	          className: "landing-ui-field-image-container"
	        },
	        attrs: {
	          method: "post",
	          enctype: "multipart/form-data"
	        },
	        events: {
	          submit: function submit(event) {
	            event.preventDefault();
	          }
	        }
	      });
	    }
	  }]);
	  return Image;
	}(landing_ui_field_textfield.TextField);
	babelHelpers.defineProperty(Image, "CONTEXT_TYPE_CONTENT", 'content');
	babelHelpers.defineProperty(Image, "CONTEXT_TYPE_STYLE", 'style');

	exports.Image = Image;

}((this.BX.Landing.UI.Field = this.BX.Landing.UI.Field || {}),BX,BX.Landing,BX.Landing,BX.Landing.UI.Panel,BX.Landing.UI.Field,BX.Landing,BX.Landing.UI.Button,BX.Landing.UI.Button,BX.Landing));
//# sourceMappingURL=image.bundle.js.map
