this.BX = this.BX || {};
(function (exports,main_popup,main_core_events,bizproc_automation,ui_fonts_opensans,main_core) {
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

	function _createForOfIteratorHelper(o, allowArrayLike) { var it = typeof Symbol !== "undefined" && o[Symbol.iterator] || o["@@iterator"]; if (!it) { if (Array.isArray(o) || (it = _unsupportedIterableToArray(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = it.call(o); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it["return"] != null) it["return"](); } finally { if (didErr) throw err; } } }; }

	function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

	function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

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
	  var generator = /*#__PURE__*/regeneratorRuntime.mark(function generator(scheme) {
	    var _iterator3, _step3, scope;

	    return regeneratorRuntime.wrap(function generator$(_context) {
	      while (1) {
	        switch (_context.prev = _context.next) {
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
	      }
	    }, generator, null, [[1, 12, 15, 18]]);
	  });
	  return generator(babelHelpers.classPrivateFieldGet(this, _scheme));
	}

	function _classPrivateFieldInitSpec$2(obj, privateMap, value) { _checkPrivateRedeclaration$2(obj, privateMap); privateMap.set(obj, value); }

	function _checkPrivateRedeclaration$2(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

	function _classStaticPrivateFieldSpecGet(receiver, classConstructor, descriptor) { _classCheckPrivateStaticAccess(receiver, classConstructor); _classCheckPrivateStaticFieldDescriptor(descriptor, "get"); return _classApplyDescriptorGet(receiver, descriptor); }

	function _classCheckPrivateStaticFieldDescriptor(descriptor, action) { if (descriptor === undefined) { throw new TypeError("attempted to " + action + " private static field before its declaration"); } }

	function _classCheckPrivateStaticAccess(receiver, classConstructor) { if (receiver !== classConstructor) { throw new TypeError("Private static access of wrong provenance"); } }

	function _classApplyDescriptorGet(receiver, descriptor) { if (descriptor.get) { return descriptor.get.call(receiver); } return descriptor.value; }

	var _mode = /*#__PURE__*/new WeakMap();

	var _properties = /*#__PURE__*/new WeakMap();

	var ViewMode = /*#__PURE__*/function () {
	  function ViewMode(mode) {
	    babelHelpers.classCallCheck(this, ViewMode);

	    _classPrivateFieldInitSpec$2(this, _mode, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec$2(this, _properties, {
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

	function _classPrivateFieldInitSpec$3(obj, privateMap, value) { _checkPrivateRedeclaration$3(obj, privateMap); privateMap.set(obj, value); }

	function _checkPrivateRedeclaration$3(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

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

	    _classPrivateFieldInitSpec$3(babelHelpers.assertThisInitialized(_this), _data, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec$3(babelHelpers.assertThisInitialized(_this), _deleted, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec$3(babelHelpers.assertThisInitialized(_this), _viewMode, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec$3(babelHelpers.assertThisInitialized(_this), _condition, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec$3(babelHelpers.assertThisInitialized(_this), _node, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec$3(babelHelpers.assertThisInitialized(_this), _draggableItem, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec$3(babelHelpers.assertThisInitialized(_this), _droppableItem, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec$3(babelHelpers.assertThisInitialized(_this), _droppableColumn, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec$3(babelHelpers.assertThisInitialized(_this), _stub, {
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
	      return babelHelpers.classPrivateFieldGet(this, _data)['DOCUMENT_STATUS'] || '';
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
	              className: "bizproc-automation-trigger-item-wrapper-text"
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
	        delete initData['ID']; //TODO: refactoring

	        if (initData['CODE'] === 'WEBHOOK') {
	          initData['APPLY_RULES'] = {};
	        }

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
	      event = event || window.event;
	      var trigger = null;
	      var isCopy = event && (event.ctrlKey || event.metaKey);

	      var copyTrigger = function copyTrigger(parent, statusId) {
	        var trigger = new Trigger();
	        var initData = parent.serialize();
	        delete initData['ID']; //TODO: refactoring

	        if (initData['CODE'] === 'WEBHOOK') {
	          initData['APPLY_RULES'] = {};
	        }

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
	      var _this2 = this;

	      var triggerData = bizproc_automation.getGlobalContext().availableTriggers.find(function (trigger) {
	        return trigger['CODE'] === _this2.getCode();
	      });
	      return triggerData && main_core.Type.isArray(triggerData.RETURN) ? triggerData.RETURN : [];
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

	function _createForOfIteratorHelper$1(o, allowArrayLike) { var it = typeof Symbol !== "undefined" && o[Symbol.iterator] || o["@@iterator"]; if (!it) { if (Array.isArray(o) || (it = _unsupportedIterableToArray$1(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = it.call(o); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it["return"] != null) it["return"](); } finally { if (didErr) throw err; } } }; }

	function _unsupportedIterableToArray$1(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray$1(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray$1(o, minLen); }

	function _arrayLikeToArray$1(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

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
	      _classStaticPrivateFieldSpecSet(Helper, Helper, _idIncrement, +_classStaticPrivateFieldSpecGet$1(Helper, Helper, _idIncrement) + 1);

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

	function _classPrivateFieldInitSpec$4(obj, privateMap, value) { _checkPrivateRedeclaration$4(obj, privateMap); privateMap.set(obj, value); }

	function _checkPrivateRedeclaration$4(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

	var _triggersContainerNode = /*#__PURE__*/new WeakMap();

	var _viewMode$1 = /*#__PURE__*/new WeakMap();

	var _triggers = /*#__PURE__*/new WeakMap();

	var _triggersData = /*#__PURE__*/new WeakMap();

	var _columnNodes = /*#__PURE__*/new WeakMap();

	var _listNodes = /*#__PURE__*/new WeakMap();

	var _buttonsNodes = /*#__PURE__*/new WeakMap();

	var _modified = /*#__PURE__*/new WeakMap();

	var TriggerManager = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(TriggerManager, _EventEmitter);

	  function TriggerManager(triggersContainerNode) {
	    var _this;

	    babelHelpers.classCallCheck(this, TriggerManager);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(TriggerManager).call(this));

	    _classPrivateFieldInitSpec$4(babelHelpers.assertThisInitialized(_this), _triggersContainerNode, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec$4(babelHelpers.assertThisInitialized(_this), _viewMode$1, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec$4(babelHelpers.assertThisInitialized(_this), _triggers, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec$4(babelHelpers.assertThisInitialized(_this), _triggersData, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec$4(babelHelpers.assertThisInitialized(_this), _columnNodes, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec$4(babelHelpers.assertThisInitialized(_this), _listNodes, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec$4(babelHelpers.assertThisInitialized(_this), _buttonsNodes, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec$4(babelHelpers.assertThisInitialized(_this), _modified, {
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
	      babelHelpers.classPrivateFieldSet(this, _buttonsNodes, babelHelpers.classPrivateFieldGet(this, _triggersContainerNode).querySelectorAll('[data-role="trigger-buttons"]'));
	      babelHelpers.classPrivateFieldSet(this, _modified, false);
	      this.initButtons();
	      this.initTriggers();
	      this.markModified(false); //register DD

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
	      babelHelpers.classPrivateFieldGet(this, _buttonsNodes).forEach(function (node) {
	        return main_core.Dom.clean(node);
	      });
	      babelHelpers.classPrivateFieldSet(this, _triggersData, main_core.Type.isArray(data.TRIGGERS) ? data.TRIGGERS : []);
	      this.initTriggers();
	      this.initButtons();
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
	    key: "initButtons",
	    value: function initButtons() {
	      var _this4 = this;

	      if (babelHelpers.classPrivateFieldGet(this, _viewMode$1).isEdit()) {
	        babelHelpers.classPrivateFieldGet(this, _buttonsNodes).forEach(function (node) {
	          return _this4.createAddButton(node);
	        });
	      }
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
	    key: "createAddButton",
	    value: function createAddButton(containerNode) {
	      var self = this;
	      var div = main_core.Dom.create('span', {
	        events: {
	          click: function click(event) {
	            if (!self.canEdit()) {
	              HelpHint.showNoPermissionsHint(this);
	            } else if (!babelHelpers.classPrivateFieldGet(self, _viewMode$1).isManage()) {
	              self.onAddButtonClick(this);
	            }
	          }
	        },
	        attrs: {
	          className: 'bizproc-automation-btn-add',
	          'data-status-id': containerNode.getAttribute('data-status-id')
	        },
	        children: [main_core.Dom.create('span', {
	          attrs: {
	            className: 'bizproc-automation-btn-add-text'
	          },
	          text: main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_ADD')
	        })]
	      });
	      containerNode.appendChild(div);
	    }
	  }, {
	    key: "onAddButtonClick",
	    value: function onAddButtonClick(button, context) {
	      var _this5 = this;

	      var self = this;

	      var onMenuClick = function onMenuClick(event, item) {
	        self.addTrigger(item.triggerData, function (trigger) {
	          this.openTriggerSettingsDialog(trigger, context);
	        });
	        this.popupWindow.close();
	      };

	      var menuItems = [];
	      bizproc_automation.getGlobalContext().availableTriggers.forEach(function (availableTrigger) {
	        if (availableTrigger.CODE === 'APP') {
	          menuItems.push(_this5.createAppTriggerMenuItem(button.getAttribute('data-status-id'), availableTrigger));
	        } else {
	          menuItems.push({
	            text: availableTrigger.NAME,
	            triggerData: {
	              DOCUMENT_STATUS: button.getAttribute('data-status-id') || context.statusId,
	              CODE: availableTrigger.CODE
	            },
	            onclick: onMenuClick
	          });
	        }
	      });
	      main_popup.MenuManager.show(Helper.generateUniqueId(), button, menuItems, {
	        autoHide: true,
	        offsetLeft: main_core.Dom.getPosition(button)['width'] / 2,
	        angle: {
	          position: 'top',
	          offset: 0
	        },
	        events: {
	          onPopupClose: function onPopupClose() {
	            this.destroy();
	          }
	        }
	      });
	    }
	  }, {
	    key: "onChangeTriggerClick",
	    value: function onChangeTriggerClick(statusId, event) {
	      this.onAddButtonClick(event.target, {
	        changeTrigger: true,
	        statusId: statusId
	      });
	    }
	  }, {
	    key: "createAppTriggerMenuItem",
	    value: function createAppTriggerMenuItem(status, triggerData) {
	      var self = this;

	      var onMenuClick = function onMenuClick(e, item) {
	        self.addTrigger(item.triggerData, function (trigger) {
	          this.openTriggerSettingsDialog(trigger);
	        });
	        this.getRootMenuWindow().close();
	      };

	      var menuItems = [];

	      for (var i = 0; i < triggerData['APP_LIST'].length; ++i) {
	        var item = triggerData['APP_LIST'][i];
	        var itemName = '[' + item['APP_NAME'] + '] ' + item['NAME'];
	        menuItems.push({
	          text: main_core.Text.encode(itemName),
	          triggerData: {
	            DOCUMENT_STATUS: status,
	            NAME: itemName,
	            CODE: triggerData.CODE,
	            APPLY_RULES: {
	              APP_ID: item['APP_ID'],
	              CODE: item['CODE']
	            }
	          },
	          onclick: onMenuClick
	        });
	      }

	      if (main_core.Reflection.getClass('BX.rest.Marketplace')) {
	        if (menuItems.length) {
	          menuItems.push({
	            delimiter: true
	          });
	        }

	        menuItems.push({
	          text: main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_CATEGORY_OTHER_MARKETPLACE_2'),
	          onclick: function onclick() {
	            BX.rest.Marketplace.open({
	              PLACEMENT: bizproc_automation.getGlobalContext().get('marketplaceRobotCategory')
	            });
	            this.getRootMenuWindow().close();
	          }
	        });
	      }

	      return {
	        text: triggerData.NAME,
	        items: menuItems
	      };
	    }
	  }, {
	    key: "addTrigger",
	    value: function addTrigger(triggerData, callback) {
	      var trigger = new Trigger();
	      trigger.init(triggerData, babelHelpers.classPrivateFieldGet(this, _viewMode$1));
	      this.subscribeTriggerEvents(trigger);
	      trigger.draft = true;

	      if (callback) {
	        callback.call(this, trigger);
	      }
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
	    key: "insertTriggerNode",
	    value: function insertTriggerNode(documentStatus, triggerNode) {
	      var listNode = babelHelpers.classPrivateFieldGet(this, _triggersContainerNode).querySelector('[data-role="trigger-list"][data-status-id="' + documentStatus + '"]');

	      if (listNode) {
	        listNode.appendChild(triggerNode);
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
	      var _this6 = this;

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
	      form.appendChild(this.renderConditionSettings(trigger));
	      var iconHelp = main_core.Dom.create('div', {
	        attrs: {
	          className: 'bizproc-automation-robot-help'
	        },
	        events: {
	          click: function click(event) {
	            return _this6.emit('TriggerManager:onHelpClick', event);
	          }
	        }
	      });
	      form.appendChild(iconHelp);
	      var title = this.getTriggerName(trigger.getCode());
	      form.appendChild(main_core.Dom.create("span", {
	        attrs: {
	          className: "bizproc-automation-popup-settings-title bizproc-automation-popup-settings-title-autocomplete"
	        },
	        text: main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_TRIGGER_NAME') + ':'
	      }));
	      form.appendChild(main_core.Dom.create("div", {
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
	      })); //TODO: refactoring

	      var triggerData = this.getAvailableTrigger(trigger.getCode());

	      if (trigger.getCode() === 'WEBHOOK') {
	        if (!trigger.getApplyRules()['code']) {
	          trigger.getApplyRules()['code'] = main_core.Text.getRandom(5);
	        }

	        form.appendChild(main_core.Dom.create("span", {
	          attrs: {
	            className: "bizproc-automation-popup-settings-title bizproc-automation-popup-settings-title-autocomplete"
	          },
	          text: "URL:"
	        }));
	        form.appendChild(main_core.Dom.create('input', {
	          props: {
	            type: 'hidden',
	            value: trigger.getApplyRules()['code'],
	            name: 'code'
	          }
	        }));
	        var hookLinkTextarea = main_core.Dom.create("textarea", {
	          attrs: {
	            className: "bizproc-automation-popup-textarea",
	            placeholder: "...",
	            readonly: 'readonly',
	            name: 'webhook_handler'
	          },
	          events: {
	            click: function click(e) {
	              this.select();
	            }
	          }
	        });
	        form.appendChild(main_core.Dom.create("div", {
	          attrs: {
	            className: "bizproc-automation-popup-settings"
	          },
	          children: [hookLinkTextarea]
	        }));
	        form.appendChild(main_core.Dom.create("span", {
	          attrs: {
	            className: "bizproc-automation-popup-settings-title bizproc-automation-popup-settings-title-autocomplete"
	          },
	          text: main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_WEBHOOK_ID')
	        }));

	        if (triggerData && triggerData['HANDLER']) {
	          var url = window.location.protocol + '//' + window.location.host + triggerData['HANDLER'];
	          url = main_core.Uri.addParam(url, {
	            code: trigger.getApplyRules()['code']
	          });
	          url = url.replace('{{DOCUMENT_TYPE}}', bizproc_automation.getGlobalContext().document.getRawType()[2]);
	          hookLinkTextarea.value = url;
	        }
	      } else if (trigger.getCode() === 'EMAIL_LINK') {
	        form.appendChild(main_core.Dom.create("span", {
	          attrs: {
	            className: "bizproc-automation-popup-settings-title bizproc-automation-popup-settings-title-autocomplete"
	          },
	          text: main_core.Loc.getMessage('BIZPROC_AUTOMATION_TRIGGER_EMAIL_LINK_URL') + ':'
	        }));
	        form.appendChild(main_core.Dom.create("div", {
	          attrs: {
	            className: "bizproc-automation-popup-settings"
	          },
	          children: [main_core.Dom.create("textarea", {
	            attrs: {
	              className: "bizproc-automation-popup-textarea",
	              placeholder: "https://example.com"
	            },
	            props: {
	              name: 'url'
	            },
	            text: trigger.getApplyRules()['url'] || ''
	          })]
	        }));
	      } else if (trigger.getCode() === 'WEBFORM') {
	        if (triggerData && triggerData['WEBFORM_LIST']) {
	          var select = main_core.Dom.create('select', {
	            attrs: {
	              className: 'bizproc-automation-popup-settings-dropdown'
	            },
	            props: {
	              name: 'form_id',
	              value: ''
	            },
	            children: [main_core.Dom.create('option', {
	              props: {
	                value: ''
	              },
	              text: main_core.Loc.getMessage('BIZPROC_AUTOMATION_TRIGGER_WEBFORM_ANY')
	            })]
	          });

	          for (var i = 0; i < triggerData['WEBFORM_LIST'].length; ++i) {
	            var item = triggerData['WEBFORM_LIST'][i];
	            select.appendChild(main_core.Dom.create('option', {
	              props: {
	                value: item['ID']
	              },
	              text: item['NAME']
	            }));
	          }

	          if (main_core.Type.isPlainObject(trigger.getApplyRules()) && trigger.getApplyRules()['form_id']) {
	            select.value = trigger.getApplyRules()['form_id'];
	          }

	          var div = main_core.Dom.create('div', {
	            attrs: {
	              className: 'bizproc-automation-popup-settings'
	            },
	            children: [main_core.Dom.create('span', {
	              attrs: {
	                className: 'bizproc-automation-popup-settings-title'
	              },
	              text: main_core.Loc.getMessage('BIZPROC_AUTOMATION_TRIGGER_WEBFORM_LABEL') + ':'
	            }), select]
	          });
	          form.appendChild(div);
	        }
	      } else if (trigger.getCode() === 'CALLBACK') {
	        if (triggerData && triggerData['WEBFORM_LIST']) {
	          var _select = main_core.Dom.create('select', {
	            attrs: {
	              className: 'bizproc-automation-popup-settings-dropdown'
	            },
	            props: {
	              name: 'form_id',
	              value: ''
	            },
	            children: [main_core.Dom.create('option', {
	              props: {
	                value: ''
	              },
	              text: main_core.Loc.getMessage('BIZPROC_AUTOMATION_TRIGGER_WEBFORM_ANY')
	            })]
	          });

	          for (var _i = 0; _i < triggerData['WEBFORM_LIST'].length; ++_i) {
	            var _item = triggerData['WEBFORM_LIST'][_i];

	            _select.appendChild(main_core.Dom.create('option', {
	              props: {
	                value: _item['ID']
	              },
	              text: _item['NAME']
	            }));
	          }

	          if (main_core.Type.isPlainObject(trigger.getApplyRules()) && trigger.getApplyRules()['form_id']) {
	            _select.value = trigger.getApplyRules()['form_id'];
	          }

	          var _div = main_core.Dom.create('div', {
	            attrs: {
	              className: 'bizproc-automation-popup-settings'
	            },
	            children: [main_core.Dom.create('span', {
	              attrs: {
	                className: 'bizproc-automation-popup-settings-title'
	              },
	              text: main_core.Loc.getMessage('BIZPROC_AUTOMATION_TRIGGER_WEBFORM_LABEL') + ':'
	            }), _select]
	          });

	          form.appendChild(_div);
	        }
	      } else if (trigger.getCode() === 'STATUS') {
	        if (triggerData && triggerData['STATUS_LIST']) {
	          var _select2 = main_core.Dom.create('select', {
	            attrs: {
	              className: 'bizproc-automation-popup-settings-dropdown'
	            },
	            props: {
	              name: 'STATUS',
	              value: ''
	            },
	            children: [main_core.Dom.create('option', {
	              props: {
	                value: ''
	              },
	              text: main_core.Loc.getMessage('BIZPROC_AUTOMATION_TRIGGER_STATUS_ANY')
	            })]
	          });

	          for (var _i2 = 0; _i2 < triggerData['STATUS_LIST'].length; ++_i2) {
	            var _item2 = triggerData['STATUS_LIST'][_i2];

	            _select2.appendChild(main_core.Dom.create('option', {
	              props: {
	                value: _item2['ID']
	              },
	              text: _item2['NAME']
	            }));
	          }

	          if (main_core.Type.isPlainObject(trigger.getApplyRules()) && trigger.getApplyRules()['STATUS']) {
	            _select2.value = trigger.getApplyRules()['STATUS'];
	          }

	          var _div2 = main_core.Dom.create('div', {
	            attrs: {
	              className: 'bizproc-automation-popup-settings'
	            },
	            children: [main_core.Dom.create('span', {
	              attrs: {
	                className: 'bizproc-automation-popup-settings-title'
	              },
	              text: triggerData['STATUS_LABEL'] + ':'
	            }), _select2]
	          });

	          form.appendChild(_div2);
	        }
	      } else if (trigger.getCode() == 'CALL') {
	        if (triggerData && triggerData['LINES']) {
	          var _select3 = main_core.Dom.create('select', {
	            attrs: {
	              className: 'bizproc-automation-popup-settings-dropdown'
	            },
	            props: {
	              name: 'LINE_NUMBER',
	              value: ''
	            },
	            children: [main_core.Dom.create('option', {
	              props: {
	                value: ''
	              },
	              text: main_core.Loc.getMessage('BIZPROC_AUTOMATION_TRIGGER_WEBFORM_ANY')
	            })]
	          });

	          for (var _i3 = 0; _i3 < triggerData['LINES'].length; ++_i3) {
	            var _item3 = triggerData['LINES'][_i3];

	            _select3.appendChild(main_core.Dom.create('option', {
	              props: {
	                value: _item3['LINE_NUMBER']
	              },
	              text: _item3['SHORT_NAME']
	            }));
	          }

	          if (trigger.getApplyRules()['LINE_NUMBER']) {
	            _select3.value = trigger.getApplyRules()['LINE_NUMBER'];
	          }

	          var _div3 = main_core.Dom.create('div', {
	            attrs: {
	              className: 'bizproc-automation-popup-settings'
	            },
	            children: [main_core.Dom.create('span', {
	              attrs: {
	                className: 'bizproc-automation-popup-settings-title'
	              },
	              text: main_core.Loc.getMessage('BIZPROC_AUTOMATION_TRIGGER_CALL_LABEL') + ':'
	            }), _select3]
	          });

	          form.appendChild(_div3);
	        }
	      } else if (trigger.getCode() == 'OPENLINE' || trigger.getCode() == 'OPENLINE_MSG') {
	        if (triggerData && triggerData['CONFIG_LIST']) {
	          var _select4 = main_core.Dom.create('select', {
	            attrs: {
	              className: 'bizproc-automation-popup-settings-dropdown'
	            },
	            props: {
	              name: 'config_id',
	              value: ''
	            },
	            children: [main_core.Dom.create('option', {
	              props: {
	                value: ''
	              },
	              text: main_core.Loc.getMessage('BIZPROC_AUTOMATION_TRIGGER_WEBFORM_ANY')
	            })]
	          });

	          for (var _i4 = 0; _i4 < triggerData['CONFIG_LIST'].length; ++_i4) {
	            var _item4 = triggerData['CONFIG_LIST'][_i4];

	            _select4.appendChild(main_core.Dom.create('option', {
	              props: {
	                value: _item4['ID']
	              },
	              text: _item4['NAME']
	            }));
	          }

	          if (main_core.Type.isPlainObject(trigger.getApplyRules()) && trigger.getApplyRules()['config_id']) {
	            _select4.value = trigger.getApplyRules()['config_id'];
	          }

	          var _div4 = main_core.Dom.create('div', {
	            attrs: {
	              className: 'bizproc-automation-popup-settings'
	            },
	            children: [main_core.Dom.create('span', {
	              attrs: {
	                className: 'bizproc-automation-popup-settings-title'
	              },
	              text: main_core.Loc.getMessage('BIZPROC_AUTOMATION_TRIGGER_OPENLINE_LABEL') + ':'
	            }), _select4]
	          });

	          form.appendChild(_div4);
	        }
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
	      var titleBar = trigger.draft ? this.createChangeTriggerTitleBar(title, trigger.documentStatus) : null;
	      var self = this;
	      var popup = new BX.PopupWindow(BX.Bizproc.Helper.generateUniqueId(), null, {
	        titleBar: titleBar || title,
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
	          }
	        },
	        buttons: [new BX.PopupWindowButton({
	          text: main_core.Loc.getMessage('JS_CORE_WINDOW_SAVE'),
	          className: "popup-window-button-accept",
	          events: {
	            click: function click() {
	              var formData = BX.ajax.prepareForm(form);
	              trigger.setName(formData['data']['name']); //TODO: refactoring

	              if (trigger.getCode() === 'WEBFORM') {
	                trigger.setApplyRules({
	                  form_id: formData['data']['form_id']
	                });
	              }

	              if (trigger.getCode() === 'CALLBACK') {
	                trigger.setApplyRules({
	                  form_id: formData['data']['form_id']
	                });
	              }

	              if (trigger.getCode() === 'STATUS') {
	                trigger.setApplyRules({
	                  STATUS: formData['data']['STATUS']
	                });
	              }

	              if (trigger.getCode() === 'CALL' && 'LINE_NUMBER' in formData['data']) {
	                trigger.setApplyRules({
	                  LINE_NUMBER: formData['data']['LINE_NUMBER']
	                });
	              }

	              if (trigger.getCode() === 'OPENLINE' || trigger.getCode() === 'OPENLINE_MSG') {
	                trigger.setApplyRules({
	                  config_id: formData['data']['config_id']
	                });
	              }

	              if (trigger.getCode() === 'WEBHOOK') {
	                trigger.setApplyRules({
	                  code: formData['data']['code']
	                });
	              }

	              if (trigger.getCode() === 'EMAIL_LINK') {
	                trigger.setApplyRules({
	                  url: formData['data']['url']
	                });
	              }

	              BX.onCustomEvent('BX.Bizproc.Automation.TriggerManager:onSaveSettings-' + trigger.getCode(), [trigger, formData]);
	              self.setConditionSettingsFromForm(formData['data'], trigger);
	              trigger.setAllowBackwards(formData['data']['allow_backwards'] === 'Y');

	              if (self.canSetExecuteBy()) {
	                trigger.setExecuteBy(formData['data']['execute_by']);
	              }

	              if (trigger.draft) {
	                babelHelpers.classPrivateFieldGet(self, _triggers).push(trigger);
	                self.insertTriggerNode(trigger.getStatusId(), trigger.node);
	              }

	              delete trigger.draft;
	              trigger.reInit();
	              self.markModified();
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
	      Designer.getInstance().getTriggerSettingsDialog().popup = popup;
	      popup.show();
	    }
	  }, {
	    key: "createChangeTriggerTitleBar",
	    value: function createChangeTriggerTitleBar(title, statusId) {
	      return {
	        content: main_core.Dom.create('div', {
	          props: {
	            className: 'popup-window-titlebar-text bizproc-automation-popup-titlebar-with-link'
	          },
	          children: [document.createTextNode(title), main_core.Dom.create('span', {
	            props: {
	              className: 'bizproc-automation-popup-titlebar-link'
	            },
	            text: main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_CHANGE_TRIGGER'),
	            events: {
	              click: this.onChangeTriggerClick.bind(this, statusId)
	            }
	          })]
	        })
	      };
	    }
	  }, {
	    key: "renderConditionSettings",
	    value: function renderConditionSettings(trigger) {
	      var conditionGroup = trigger.getCondition().clone();
	      this.conditionSelector = new bizproc_automation.ConditionGroupSelector(conditionGroup, {
	        fields: bizproc_automation.getGlobalContext().document.getFields()
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
	      form.appendChild(main_core.Dom.create("span", {
	        attrs: {
	          className: "bizproc-automation-popup-settings-title bizproc-automation-popup-settings-title-top bizproc-automation-popup-settings-title-autocomplete"
	        },
	        text: main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_TRIGGER_EXECUTE_BY') + ':'
	      }));
	      form.appendChild(main_core.Dom.create("div", {
	        attrs: {
	          className: "bizproc-automation-popup-settings"
	        },
	        children: [BX.Bizproc.FieldType.renderControl(bizproc_automation.getGlobalContext().document.getRawType(), {
	          Type: 'user'
	        }, 'execute_by', trigger.draft ? Helper.getResponsibleUserExpression(bizproc_automation.getGlobalContext().document.getFields()) : trigger.getExecuteBy())]
	      }));
	    }
	  }, {
	    key: "renderAllowBackwardsControl",
	    value: function renderAllowBackwardsControl(trigger, form) {
	      form.appendChild(main_core.Dom.create("div", {
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
	      }));
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

	function _classPrivateFieldInitSpec$5(obj, privateMap, value) { _checkPrivateRedeclaration$5(obj, privateMap); privateMap.set(obj, value); }

	function _checkPrivateRedeclaration$5(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

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

	    _classPrivateFieldInitSpec$5(this, _rawType, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec$5(this, _id, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec$5(this, _title, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec$5(this, _categoryId, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec$5(this, _statusList, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec$5(this, _currentStatusIndex, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec$5(this, _fields, {
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

	      return (_babelHelpers$classPr = babelHelpers.classPrivateFieldGet(this, _statusList)[babelHelpers.classPrivateFieldGet(this, _currentStatusIndex)]) === null || _babelHelpers$classPr === void 0 ? void 0 : _babelHelpers$classPr.STATUS_ID;
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
	  }]);
	  return Document;
	}();

	function _classPrivateFieldInitSpec$6(obj, privateMap, value) { _checkPrivateRedeclaration$6(obj, privateMap); privateMap.set(obj, value); }

	function _checkPrivateRedeclaration$6(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

	var _basis = /*#__PURE__*/new WeakMap();

	var _type = /*#__PURE__*/new WeakMap();

	var _value = /*#__PURE__*/new WeakMap();

	var _valueType = /*#__PURE__*/new WeakMap();

	var _workTime = /*#__PURE__*/new WeakMap();

	var _localTime = /*#__PURE__*/new WeakMap();

	var DelayInterval = /*#__PURE__*/function () {
	  function DelayInterval(params) {
	    babelHelpers.classCallCheck(this, DelayInterval);

	    _classPrivateFieldInitSpec$6(this, _basis, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec$6(this, _type, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec$6(this, _value, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec$6(this, _valueType, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec$6(this, _workTime, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec$6(this, _localTime, {
	      writable: true,
	      value: void 0
	    });

	    babelHelpers.classPrivateFieldSet(this, _basis, DelayInterval.BASIS_TYPE.CurrentDateTime);
	    babelHelpers.classPrivateFieldSet(this, _type, DelayInterval.DELAY_TYPE.After);
	    babelHelpers.classPrivateFieldSet(this, _value, 0);
	    babelHelpers.classPrivateFieldSet(this, _valueType, 'i');
	    babelHelpers.classPrivateFieldSet(this, _workTime, false);
	    babelHelpers.classPrivateFieldSet(this, _localTime, false);

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

	      if (params['localTime']) {
	        this.setLocalTime(params['localTime']);
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
	        localTime: babelHelpers.classPrivateFieldGet(this, _localTime)
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
	    key: "setLocalTime",
	    value: function setLocalTime(flag) {
	      babelHelpers.classPrivateFieldSet(this, _localTime, !!flag);
	      return this;
	    }
	  }, {
	    key: "isNow",
	    value: function isNow() {
	      return babelHelpers.classPrivateFieldGet(this, _type) === DelayInterval.DELAY_TYPE.After && babelHelpers.classPrivateFieldGet(this, _basis) === DelayInterval.BASIS_TYPE.CurrentDateTime && !babelHelpers.classPrivateFieldGet(this, _value);
	    }
	  }, {
	    key: "setNow",
	    value: function setNow() {
	      this.setType(DelayInterval.DELAY_TYPE.After);
	      this.setValue(0);
	      this.setValueType('i');
	      this.setBasis(DelayInterval.BASIS_TYPE.CurrentDateTime);
	    }
	  }, {
	    key: "serialize",
	    value: function serialize() {
	      return {
	        type: babelHelpers.classPrivateFieldGet(this, _type),
	        value: babelHelpers.classPrivateFieldGet(this, _value),
	        valueType: babelHelpers.classPrivateFieldGet(this, _valueType),
	        basis: babelHelpers.classPrivateFieldGet(this, _basis),
	        workTime: babelHelpers.classPrivateFieldGet(this, _workTime) ? 1 : 0
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

	      if (!babelHelpers.classPrivateFieldGet(this, _workTime) && (babelHelpers.classPrivateFieldGet(this, _type) === DelayInterval.DELAY_TYPE.In || this.isNow())) {
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

	      return '=' + fn + '(' + basis + ',"' + add + '"' + (worker ? ',' + worker : '') + ')';
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
	    key: "localTime",
	    get: function get() {
	      return babelHelpers.classPrivateFieldGet(this, _localTime);
	    }
	  }], [{
	    key: "isSystemBasis",
	    value: function isSystemBasis(basis) {
	      return basis === this.BASIS_TYPE.CurrentDate || basis === this.BASIS_TYPE.CurrentDateTime || basis === this.BASIS_TYPE.CurrentDateTimeLocal;
	    }
	  }, {
	    key: "fromString",
	    value: function fromString(intervalString, basisFields) {
	      intervalString = intervalString.toString();
	      var params = {
	        basis: DelayInterval.BASIS_TYPE.CurrentDateTime,
	        i: 0,
	        h: 0,
	        d: 0,
	        workTime: false,
	        localTime: false
	      };

	      if (intervalString.indexOf('=dateadd(') === 0 || intervalString.indexOf('=workdateadd(') === 0) {
	        if (intervalString.indexOf('=workdateadd(') === 0) {
	          intervalString = intervalString.substring(13);
	          params['workTime'] = true;
	        } else {
	          intervalString = intervalString.substring(9);
	        }

	        var fnArgs = intervalString.split(',');
	        params['basis'] = fnArgs[0].trim();
	        fnArgs[1] = fnArgs[1].replace(/['")]+/g, '');
	        params['type'] = fnArgs[1].indexOf('-') === 0 ? DelayInterval.DELAY_TYPE.Before : DelayInterval.DELAY_TYPE.After;
	        var match;
	        var re = /s*([\d]+)\s*(i|h|d)\s*/ig;

	        while (match = re.exec(fnArgs[1])) {
	          params[match[2]] = parseInt(match[1]);
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

	      var minutes = params['i'] + params['h'] * 60 + params['d'] * 60 * 24;

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

	      if (!params['value'] && params['basis'] !== DelayInterval.BASIS_TYPE.CurrentDateTime && params['basis']) {
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

	var WorkflowStatus = function WorkflowStatus() {
	  babelHelpers.classCallCheck(this, WorkflowStatus);
	};
	babelHelpers.defineProperty(WorkflowStatus, "CREATED_WORKFLOW_STATUS", 0);
	babelHelpers.defineProperty(WorkflowStatus, "RUNNING_WORKFLOW_STATUS", 1);
	babelHelpers.defineProperty(WorkflowStatus, "COMPLETED_WORKFLOW_STATUS", 2);
	babelHelpers.defineProperty(WorkflowStatus, "SUSPENDED_WORKFLOW_STATUS", 3);
	babelHelpers.defineProperty(WorkflowStatus, "TERMINATED_WORKFLOW_STATUS", 4);

	function _classPrivateFieldInitSpec$7(obj, privateMap, value) { _checkPrivateRedeclaration$7(obj, privateMap); privateMap.set(obj, value); }

	function _checkPrivateRedeclaration$7(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

	var _type$1 = /*#__PURE__*/new WeakMap();

	var _workflowStatus = /*#__PURE__*/new WeakMap();

	var TrackingEntry = /*#__PURE__*/function () {
	  function TrackingEntry() {
	    babelHelpers.classCallCheck(this, TrackingEntry);

	    _classPrivateFieldInitSpec$7(this, _type$1, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec$7(this, _workflowStatus, {
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
	      return [WorkflowStatus.CREATED_WORKFLOW_STATUS, WorkflowStatus.RUNNING_WORKFLOW_STATUS, WorkflowStatus.COMPLETED_WORKFLOW_STATUS, WorkflowStatus.SUSPENDED_WORKFLOW_STATUS, WorkflowStatus.TERMINATED_WORKFLOW_STATUS];
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

	function _arrayLikeToArray$2(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

	function _classPrivateFieldInitSpec$8(obj, privateMap, value) { _checkPrivateRedeclaration$8(obj, privateMap); privateMap.set(obj, value); }

	function _checkPrivateRedeclaration$8(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

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

	    _classPrivateFieldInitSpec$8(this, _entryId, {
	      writable: true,
	      value: -1
	    });

	    babelHelpers.defineProperty(this, "workflowStatus", WorkflowStatus.CREATED_WORKFLOW_STATUS);

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

	var TriggerEntry = // TODO - change string to Date when Date appear in TrackingEntry
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

	function _classPrivateFieldInitSpec$9(obj, privateMap, value) { _checkPrivateRedeclaration$9(obj, privateMap); privateMap.set(obj, value); }

	function _checkPrivateRedeclaration$9(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

	var _defaultSettings = /*#__PURE__*/new WeakMap();

	var _entrySettings = /*#__PURE__*/new WeakMap();

	var TrackingEntryBuilder = /*#__PURE__*/function () {
	  function TrackingEntryBuilder() {
	    babelHelpers.classCallCheck(this, TrackingEntryBuilder);

	    _classPrivateFieldInitSpec$9(this, _defaultSettings, {
	      writable: true,
	      value: {
	        id: TrackingEntry.UNKNOWN_ACTIVITY_TYPE,
	        workflowId: '',
	        type: TrackingEntry.EXECUTE_ACTIVITY_TYPE,
	        name: '',
	        title: '',
	        datetime: '',
	        note: '',
	        workflowStatus: WorkflowStatus.CREATED_WORKFLOW_STATUS
	      }
	    });

	    _classPrivateFieldInitSpec$9(this, _entrySettings, {
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

	function _arrayLikeToArray$3(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

	function _classPrivateFieldInitSpec$a(obj, privateMap, value) { _checkPrivateRedeclaration$a(obj, privateMap); privateMap.set(obj, value); }

	function _checkPrivateRedeclaration$a(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

	var _ajaxUrl = /*#__PURE__*/new WeakMap();

	var _document = /*#__PURE__*/new WeakMap();

	var _triggerLogs = /*#__PURE__*/new WeakMap();

	var _robotLogs = /*#__PURE__*/new WeakMap();

	var Tracker = /*#__PURE__*/function () {
	  function Tracker(document, ajaxUrl) {
	    babelHelpers.classCallCheck(this, Tracker);

	    _classPrivateFieldInitSpec$a(this, _ajaxUrl, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec$a(this, _document, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec$a(this, _triggerLogs, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec$a(this, _robotLogs, {
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
	                var isWorkflowCompleted = robotEntry.workflowStatus === WorkflowStatus.COMPLETED_WORKFLOW_STATUS;
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

	function _arrayLikeToArray$4(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

	function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }

	function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }

	function _classPrivateFieldInitSpec$b(obj, privateMap, value) { _checkPrivateRedeclaration$b(obj, privateMap); privateMap.set(obj, value); }

	function _checkPrivateRedeclaration$b(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

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

	    _classPrivateFieldInitSpec$b(babelHelpers.assertThisInitialized(_this), _data$1, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec$b(babelHelpers.assertThisInitialized(_this), _document$1, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec$b(babelHelpers.assertThisInitialized(_this), _template, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec$b(babelHelpers.assertThisInitialized(_this), _tracker, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec$b(babelHelpers.assertThisInitialized(_this), _delay, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec$b(babelHelpers.assertThisInitialized(_this), _node$1, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec$b(babelHelpers.assertThisInitialized(_this), _condition$1, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec$b(babelHelpers.assertThisInitialized(_this), _isDraft, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec$b(babelHelpers.assertThisInitialized(_this), _isFrameMode, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec$b(babelHelpers.assertThisInitialized(_this), _viewMode$2, {
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

	      var robotData = _objectSpread({
	        Name: Robot.generateName(),
	        Delay: this.getDelayInterval().clone(),
	        Condition: this.getCondition().clone()
	      }, BX.clone(babelHelpers.classPrivateFieldGet(this, _data$1)));

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
	        babelHelpers.classPrivateFieldSet(this, _data$1, data);
	      }

	      if (!babelHelpers.classPrivateFieldGet(this, _data$1).Name) {
	        babelHelpers.classPrivateFieldGet(this, _data$1).Name = Robot.generateName();
	      }

	      babelHelpers.classPrivateFieldSet(this, _delay, new DelayInterval(babelHelpers.classPrivateFieldGet(this, _data$1).Delay));
	      babelHelpers.classPrivateFieldSet(this, _condition$1, new bizproc_automation.ConditionGroup(babelHelpers.classPrivateFieldGet(this, _data$1).Condition));

	      if (!babelHelpers.classPrivateFieldGet(this, _data$1).Condition) {
	        babelHelpers.classPrivateFieldGet(this, _condition$1).type = bizproc_automation.ConditionGroup.CONDITION_TYPE.Mixed;
	      }

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

	      var targetLabel = main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_TO');
	      var targetNode = main_core.Dom.create("a", {
	        attrs: {
	          className: "bizproc-automation-robot-settings-name",
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

	        targetNode.textContent = labelText;
	        targetNode.setAttribute('title', labelText);

	        if (babelHelpers.classPrivateFieldGet(this, _data$1).viewData.responsibleUrl) {
	          targetNode.href = babelHelpers.classPrivateFieldGet(this, _data$1).viewData.responsibleUrl;

	          if (babelHelpers.classPrivateFieldGet(this, _isFrameMode)) {
	            targetNode.setAttribute('target', '_blank');
	          }
	        }

	        if (parseInt(babelHelpers.classPrivateFieldGet(this, _data$1).viewData.responsibleId) > 0) {
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
	                className: titleClassName
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
	        babelHelpers.classPrivateFieldGet(this, _template).openRobotSettingsDialog(this);
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
	      var popup = new BX.PopupWindow(BX.Bizproc.Helper.generateUniqueId(), null, {
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
	      var name = 'untitled';
	      var description = this.template.getRobotDescription(babelHelpers.classPrivateFieldGet(this, _data$1)['Type']);

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
	              Expression: '{{~' + this.getId() + ':' + fieldId + ' # ' + this.getTitle() + ': ' + field['NAME'] + '}}',
	              SystemExpression: '{=' + this.getId() + ':' + fieldId + '}'
	            });

	            if (!this.appendPropertyMods) {
	              continue;
	            } //generate printable version


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
	                }); //generate printable version

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
	      var usages = BX.clone(this.collectUsages());

	      if (!this.template) {
	        return false;
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
	                return true;
	              }

	              if (object === 'Activity') {
	                var robot = babelHelpers.classPrivateFieldGet(this, _template).getRobotById(searchInSource);

	                if (!robot.getReturnProperty(id)) {
	                  return true;
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

	      return false;
	    }
	  }, {
	    key: "node",
	    get: function get() {
	      return babelHelpers.classPrivateFieldGet(this, _node$1);
	    }
	  }, {
	    key: "data",
	    get: function get() {
	      return babelHelpers.classPrivateFieldGet(this, _data$1);
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

	function _classPrivateFieldInitSpec$c(obj, privateMap, value) { _checkPrivateRedeclaration$c(obj, privateMap); privateMap.set(obj, value); }

	function _checkPrivateRedeclaration$c(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

	var _options = /*#__PURE__*/new WeakMap();

	var UserOptions = /*#__PURE__*/function () {
	  function UserOptions(options) {
	    babelHelpers.classCallCheck(this, UserOptions);

	    _classPrivateFieldInitSpec$c(this, _options, {
	      writable: true,
	      value: void 0
	    });

	    babelHelpers.classPrivateFieldSet(this, _options, options);
	  }

	  babelHelpers.createClass(UserOptions, [{
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

	function _classPrivateFieldInitSpec$d(obj, privateMap, value) { _checkPrivateRedeclaration$d(obj, privateMap); privateMap.set(obj, value); }

	function _checkPrivateRedeclaration$d(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

	var _selectors = /*#__PURE__*/new WeakMap();

	var _delayMinLimitM = /*#__PURE__*/new WeakMap();

	var _userOptions = /*#__PURE__*/new WeakMap();

	var _tracker$1 = /*#__PURE__*/new WeakMap();

	var _viewMode$3 = /*#__PURE__*/new WeakMap();

	var _templateContainerNode = /*#__PURE__*/new WeakMap();

	var _templateNode = /*#__PURE__*/new WeakMap();

	var _listNode = /*#__PURE__*/new WeakMap();

	var _buttonsNode = /*#__PURE__*/new WeakMap();

	var _topButtonsNode = /*#__PURE__*/new WeakMap();

	var _robots = /*#__PURE__*/new WeakMap();

	var _data$2 = /*#__PURE__*/new WeakMap();

	var Template = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(Template, _EventEmitter);

	  function Template(params) {
	    var _this;

	    babelHelpers.classCallCheck(this, Template);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Template).call(this));

	    _classPrivateFieldInitSpec$d(babelHelpers.assertThisInitialized(_this), _selectors, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec$d(babelHelpers.assertThisInitialized(_this), _delayMinLimitM, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec$d(babelHelpers.assertThisInitialized(_this), _userOptions, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec$d(babelHelpers.assertThisInitialized(_this), _tracker$1, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec$d(babelHelpers.assertThisInitialized(_this), _viewMode$3, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec$d(babelHelpers.assertThisInitialized(_this), _templateContainerNode, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec$d(babelHelpers.assertThisInitialized(_this), _templateNode, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec$d(babelHelpers.assertThisInitialized(_this), _listNode, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec$d(babelHelpers.assertThisInitialized(_this), _buttonsNode, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec$d(babelHelpers.assertThisInitialized(_this), _topButtonsNode, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec$d(babelHelpers.assertThisInitialized(_this), _robots, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec$d(babelHelpers.assertThisInitialized(_this), _data$2, {
	      writable: true,
	      value: void 0
	    });

	    _this.setEventNamespace('BX.Bizproc.Automation');

	    _this.constants = params.constants;
	    _this.globalConstants = params.globalConstants;
	    _this.variables = params.variables;
	    _this.globalVariables = params.globalVariables;
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _templateContainerNode, params.templateContainerNode);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _selectors, params.selectors);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _delayMinLimitM, params.delayMinLimitM);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _userOptions, params.userOptions);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _tracker$1, bizproc_automation.getGlobalContext().tracker);
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

	        this.markExternalModified(babelHelpers.classPrivateFieldGet(this, _data$2)['IS_EXTERNAL_MODIFIED']);
	        this.markModified(false);
	      }

	      babelHelpers.classPrivateFieldSet(this, _viewMode$3, ViewMode.fromRaw(viewMode));

	      if (!babelHelpers.classPrivateFieldGet(this, _viewMode$3).isNone()) {
	        babelHelpers.classPrivateFieldSet(this, _templateNode, babelHelpers.classPrivateFieldGet(this, _templateContainerNode).querySelector('[data-role="automation-template"][data-status-id="' + babelHelpers.classPrivateFieldGet(this, _data$2).DOCUMENT_STATUS + '"]'));
	        babelHelpers.classPrivateFieldSet(this, _listNode, babelHelpers.classPrivateFieldGet(this, _templateNode).querySelector('[data-role="robot-list"]'));
	        babelHelpers.classPrivateFieldSet(this, _buttonsNode, babelHelpers.classPrivateFieldGet(this, _templateNode).querySelector('[data-role="buttons"]'));
	        babelHelpers.classPrivateFieldSet(this, _topButtonsNode, babelHelpers.classPrivateFieldGet(this, _templateNode).querySelector('[data-role="top-buttons"]'));
	        this.initRobots();
	        this.initButtons();
	        this.updateTopButtonsVisibility();

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
	      main_core.Dom.clean(babelHelpers.classPrivateFieldGet(this, _topButtonsNode));
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
	      return bizproc_automation.getGlobalContext().canEdit;
	    }
	  }, {
	    key: "initRobots",
	    value: function initRobots() {
	      babelHelpers.classPrivateFieldSet(this, _robots, []);

	      if (main_core.Type.isArray(babelHelpers.classPrivateFieldGet(this, _data$2).ROBOTS)) {
	        for (var i = 0; i < babelHelpers.classPrivateFieldGet(this, _data$2).ROBOTS.length; ++i) {
	          var robot = new Robot({
	            document: bizproc_automation.getGlobalContext().document,
	            template: this,
	            isFrameMode: bizproc_automation.getGlobalContext().get('isFrameMode'),
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
	      } else if (babelHelpers.classPrivateFieldGet(this, _viewMode$3).isEdit()) {
	        this.createAddButton();

	        if (this.getTemplateId() > 0) {
	          this.createConstantsEditButton();
	          this.createParametersEditButton();
	          this.createExternalEditTemplateButton();
	          this.createManageModeButton();
	        }
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
	    key: "createAddButton",
	    value: function createAddButton() {
	      var _this2 = this;

	      var anchor = function anchor() {
	        return main_core.Dom.create('span', {
	          events: {
	            click: function click(event) {
	              if (!_this2.canEdit()) {
	                HelpHint.showNoPermissionsHint(event.target);
	              } else if (!babelHelpers.classPrivateFieldGet(_this2, _viewMode$3).isManage()) {
	                _this2.onAddButtonClick(event.target);
	              }
	            }
	          },
	          attrs: {
	            className: 'bizproc-automation-robot-btn-add'
	          },
	          children: [main_core.Dom.create('span', {
	            attrs: {
	              className: 'bizproc-automation-btn-add-text'
	            },
	            text: main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_ADD')
	          })]
	        });
	      };

	      if (babelHelpers.classPrivateFieldGet(this, _topButtonsNode)) {
	        babelHelpers.classPrivateFieldGet(this, _topButtonsNode).appendChild(anchor());
	      }

	      if (babelHelpers.classPrivateFieldGet(this, _buttonsNode)) {
	        babelHelpers.classPrivateFieldGet(this, _buttonsNode).appendChild(anchor());
	      }
	    }
	  }, {
	    key: "updateTopButtonsVisibility",
	    value: function updateTopButtonsVisibility() {
	      if (babelHelpers.classPrivateFieldGet(this, _topButtonsNode)) {
	        var fn = babelHelpers.classPrivateFieldGet(this, _robots) && babelHelpers.classPrivateFieldGet(this, _robots).length < 1 ? 'hide' : 'show';

	        if (this.isExternalModified()) {
	          fn = 'show';
	        }

	        BX[fn](babelHelpers.classPrivateFieldGet(this, _topButtonsNode));
	      }
	    }
	  }, {
	    key: "createExternalEditTemplateButton",
	    value: function createExternalEditTemplateButton() {
	      if (main_core.Type.isNil(bizproc_automation.getGlobalContext().bizprocEditorUrl)) {
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

	      if (!bizproc_automation.getGlobalContext().bizprocEditorUrl.length) {
	        main_core.Dom.addClass(anchor, 'bizproc-automation-robot-btn-set-locked');
	      }

	      babelHelpers.classPrivateFieldGet(this, _buttonsNode).appendChild(anchor);
	    }
	  }, {
	    key: "createManageModeButton",
	    value: function createManageModeButton() {
	      var _this3 = this;

	      if (!bizproc_automation.getGlobalContext().canManage) {
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
	      if (main_core.Type.isNil(bizproc_automation.getGlobalContext().constantsEditorUrl)) {
	        return false;
	      }

	      var url = !babelHelpers.classPrivateFieldGet(this, _viewMode$3).isManage() ? bizproc_automation.getGlobalContext().constantsEditorUrl.replace('#ID#', this.getTemplateId()) : '#';

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
	      if (main_core.Type.isNil(bizproc_automation.getGlobalContext().parametersEditorUrl)) {
	        return false;
	      }

	      var url = bizproc_automation.getGlobalContext().parametersEditorUrl.replace('#ID#', this.getTemplateId());

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
	      if (babelHelpers.classPrivateFieldGet(this, _topButtonsNode)) {
	        babelHelpers.classPrivateFieldGet(this, _topButtonsNode).appendChild(main_core.Dom.create('span', {
	          attrs: {
	            className: 'bizproc-automation-robot-btn-prohibit'
	          }
	        }));
	      }

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
	    key: "onAddButtonClick",
	    value: function onAddButtonClick(button, context) {
	      var menuItems = {
	        employee: [],
	        client: [],
	        ads: [],
	        other: []
	      };
	      var availableRobots = bizproc_automation.getGlobalContext().availableRobots;

	      if (!main_core.Type.isPlainObject(context)) {
	        context = {};
	      }

	      var self = this;

	      var menuItemClickHandler = function menuItemClickHandler(event, item) {
	        var robotData = BX.clone(item.robotData);

	        if (robotData['ROBOT_SETTINGS'] && robotData['ROBOT_SETTINGS']['TITLE_CATEGORY'] && robotData['ROBOT_SETTINGS']['TITLE_CATEGORY'][item.category]) {
	          robotData['NAME'] = robotData['ROBOT_SETTINGS']['TITLE_CATEGORY'][item.category];
	        } else if (robotData['ROBOT_SETTINGS'] && robotData['ROBOT_SETTINGS']['TITLE']) {
	          robotData['NAME'] = robotData['ROBOT_SETTINGS']['TITLE'];
	        }

	        self.addRobot(robotData, function (robot) {
	          context.ADD_MENU_CATEGORY = item.category;
	          this.openRobotSettingsDialog(robot, context);
	        });
	        this.getRootMenuWindow().close();
	      };

	      for (var i = 0; i < availableRobots.length; ++i) {
	        if (availableRobots[i]['EXCLUDED']) {
	          continue;
	        }

	        var settings = main_core.Type.isPlainObject(availableRobots[i]['ROBOT_SETTINGS']) ? availableRobots[i]['ROBOT_SETTINGS'] : {};
	        var title = availableRobots[i].NAME;

	        if (settings['TITLE']) {
	          title = settings['TITLE'];
	        }

	        var categories = [];

	        if (settings['CATEGORY']) {
	          categories = main_core.Type.isArray(settings['CATEGORY']) ? settings['CATEGORY'] : [settings['CATEGORY']];
	        }

	        if (!categories.length) {
	          categories.push('other');
	        }

	        for (var j = 0; j < categories.length; ++j) {
	          if (!menuItems[categories[j]]) {
	            continue;
	          }

	          menuItems[categories[j]].push({
	            text: title,
	            robotData: availableRobots[i],
	            category: categories[j],
	            onclick: menuItemClickHandler
	          });
	        }
	      }

	      if (menuItems['other'].length > 0) {
	        menuItems['other'].push({
	          delimiter: true
	        });
	      }

	      if (main_core.Reflection.getClass('BX.rest.Marketplace')) {
	        menuItems['other'].push({
	          text: main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_CATEGORY_OTHER_MARKETPLACE_2'),
	          onclick: function onclick() {
	            BX.rest.Marketplace.open({}, bizproc_automation.getGlobalContext().get('marketplaceRobotCategory'));
	            this.getRootMenuWindow().close();
	          }
	        });
	      } else {
	        menuItems['other'].push({
	          text: main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_CATEGORY_OTHER_MARKETPLACE_2'),
	          href: '/marketplace/category/%category%/'.replace('%category%', bizproc_automation.getGlobalContext().get('marketplaceRobotCategory')),
	          target: '_blank'
	        });
	      }

	      var menuId = button.getAttribute('data-menu-id');

	      if (!menuId) {
	        menuId = Helper.generateUniqueId();
	        button.setAttribute('data-menu-id', menuId);
	      }

	      var rootMenuItems = [];

	      if (menuItems['employee'].length > 0) {
	        rootMenuItems.push({
	          text: main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_CATEGORY_EMPLOYEE'),
	          items: menuItems['employee']
	        });
	      }

	      if (menuItems['client'].length > 0) {
	        rootMenuItems.push({
	          text: main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_CATEGORY_CLIENT'),
	          items: menuItems['client']
	        });
	      }

	      if (menuItems['ads'].length > 0) {
	        rootMenuItems.push({
	          text: main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_CATEGORY_ADS'),
	          items: menuItems['ads']
	        });
	      }

	      rootMenuItems.push({
	        text: main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_CATEGORY_OTHER'),
	        items: menuItems['other']
	      });
	      main_popup.MenuManager.show(menuId, button, rootMenuItems, {
	        autoHide: true,
	        offsetLeft: BX.pos(button)['width'] / 2,
	        angle: {
	          position: 'top',
	          offset: 0
	        },
	        maxHeight: 550
	      });
	    }
	  }, {
	    key: "onChangeRobotClick",
	    value: function onChangeRobotClick(event) {
	      this.onAddButtonClick(event.target, {
	        changeRobot: true
	      });
	    }
	  }, {
	    key: "onExternalEditTemplateButtonClick",
	    value: function onExternalEditTemplateButtonClick(button) {
	      if (!this.canEdit()) {
	        HelpHint.showNoPermissionsHint(button);
	        return;
	      }

	      if (!bizproc_automation.getGlobalContext().bizprocEditorUrl.length) {
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
	      top.window.location.href = bizproc_automation.getGlobalContext().bizprocEditorUrl.replace('#ID#', templateId);
	    }
	  }, {
	    key: "addRobot",
	    value: function addRobot(robotData, callback) {
	      var robot = new Robot({
	        document: bizproc_automation.getGlobalContext().document,
	        template: this,
	        isFrameMode: bizproc_automation.getGlobalContext().get('isFrameMode'),
	        tracker: babelHelpers.classPrivateFieldGet(this, _tracker$1)
	      });
	      var initData = {
	        Type: robotData['CLASS'],
	        Properties: {
	          Title: robotData['NAME']
	        }
	      };

	      if (babelHelpers.classPrivateFieldGet(this, _robots).length > 0) {
	        var parentRobot = babelHelpers.classPrivateFieldGet(this, _robots)[babelHelpers.classPrivateFieldGet(this, _robots).length - 1];

	        if (!parentRobot.getDelayInterval().isNow() || parentRobot.isExecuteAfterPrevious()) {
	          initData['Delay'] = parentRobot.getDelayInterval().serialize();
	          initData['ExecuteAfterPrevious'] = 1;
	        }
	      }

	      robot.init(initData, babelHelpers.classPrivateFieldGet(this, _viewMode$3));
	      robot.draft = true;

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
	          break;
	        }
	      }

	      if (callback) {
	        callback(robot);
	      }

	      this.markModified();
	      this.updateTopButtonsVisibility();
	    }
	  }, {
	    key: "insertRobotNode",
	    value: function insertRobotNode(robotNode, beforeNode) {
	      if (beforeNode) {
	        babelHelpers.classPrivateFieldGet(this, _listNode).insertBefore(robotNode, beforeNode);
	      } else {
	        babelHelpers.classPrivateFieldGet(this, _listNode).appendChild(robotNode);
	      }

	      this.updateTopButtonsVisibility();
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

	      if (robot.hasBrokenLink()) {
	        form.appendChild(this.renderBrokenLinkAlert());
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
	      context['DOCUMENT_CATEGORY_ID'] = bizproc_automation.getGlobalContext().document.getCategoryId();
	      BX.ajax({
	        method: 'POST',
	        dataType: 'html',
	        url: bizproc_automation.getGlobalContext().ajaxUrl,
	        data: {
	          ajax_action: 'get_robot_dialog',
	          document_signed: bizproc_automation.getGlobalContext().signedDocument,
	          document_status: bizproc_automation.getGlobalContext().document.getCurrentStatusId(),
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

	      var titleBar;
	      var robotTitle = robot.hasTitle() ? robot.getTitle() : main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SETTINGS_TITLE');

	      if (robot.draft) {
	        titleBar = this.createChangeRobotTitleBar(robotTitle);
	      }

	      var me = this;
	      var popup = new BX.PopupWindow(Helper.generateUniqueId(), null, {
	        titleBar: titleBar || robotTitle,
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
	            _this5.currentRobot = null;
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
	              me.saveRobotSettings(form, robot, BX.delegate(function () {
	                this.popupWindow.close();
	                me.emit('Template:robot:add', {
	                  robot: robot
	                });

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
	      this.currentRobot = robot;
	      bizproc_automation.Designer.getInstance().getRobotSettingsDialog().popup = popup;
	      popup.show();
	    }
	  }, {
	    key: "createChangeRobotTitleBar",
	    value: function createChangeRobotTitleBar(title) {
	      return {
	        content: BX.Dom.create('div', {
	          props: {
	            className: 'popup-window-titlebar-text bizproc-automation-popup-titlebar-with-link'
	          },
	          children: [document.createTextNode(title), main_core.Dom.create('span', {
	            props: {
	              className: 'bizproc-automation-popup-titlebar-link'
	            },
	            text: main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_CHANGE_ROBOT'),
	            events: {
	              click: this.onChangeRobotClick.bind(this)
	            }
	          })]
	        })
	      };
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
	      if (!main_core.Type.isArray(this.robotSettingsControls)) {
	        this.robotSettingsControls = [];
	      }

	      var control = null;
	      var role = controlNode.getAttribute('data-role');

	      if (role === 'user-selector') {
	        control = new (babelHelpers.classPrivateFieldGet(this, _selectors).userSelector)(robot, controlNode, babelHelpers.classPrivateFieldGet(this, _data$2));
	      } else if (role === 'file-selector') {
	        control = new (babelHelpers.classPrivateFieldGet(this, _selectors).fileSelector)(robot, controlNode);
	      } else if (role === 'inline-selector-target') {
	        control = new (babelHelpers.classPrivateFieldGet(this, _selectors).inlineSelector)(robot, controlNode, babelHelpers.classPrivateFieldGet(this, _data$2));
	      } else if (role === 'inline-selector-html') {
	        control = new (babelHelpers.classPrivateFieldGet(this, _selectors).inlineSelectorHtml)(robot, controlNode);
	      } else if (role === 'time-selector') {
	        control = new (babelHelpers.classPrivateFieldGet(this, _selectors).timeSelector)(controlNode);
	      } else if (role === 'save-state-checkbox') {
	        control = new (babelHelpers.classPrivateFieldGet(this, _selectors).saveStateCheckbox)(controlNode, robot);
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
	      var delayIntervalLabelNode = main_core.Dom.create("span", {
	        attrs: {
	          className: "bizproc-automation-popup-settings-link bizproc-automation-delay-interval-basis"
	        }
	      });
	      var basisFields = [];
	      var docFields = bizproc_automation.getGlobalContext().document.getFields();
	      var minLimitM = babelHelpers.classPrivateFieldGet(this, _delayMinLimitM);

	      if (main_core.Type.isArray(docFields)) {
	        for (var i = 0; i < docFields.length; ++i) {
	          var field = docFields[i];

	          if (field['Type'] == 'date' || field['Type'] == 'datetime') {
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
	        },
	        basisFields: basisFields,
	        minLimitM: minLimitM,
	        useAfterBasis: true
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
	            children: [delayTypeNode, delayValueNode, delayValueTypeNode, delayBasisNode, delayWorkTimeNode, main_core.Dom.create("span", {
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
	      robot.setDelayInterval(delay);

	      if (robot.hasTemplate()) {
	        robot.setExecuteAfterPrevious(formFields['execute_after_previous'] && formFields['execute_after_previous'] === '1');
	      }

	      return this;
	    }
	  }, {
	    key: "renderConditionSettings",
	    value: function renderConditionSettings(robot) {
	      var conditionGroup = robot.getCondition();
	      var selector = this.conditionSelector = new bizproc_automation.ConditionGroupSelector(conditionGroup, {
	        fields: bizproc_automation.getGlobalContext().document.getFields()
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
	          }), selector.createNode()]
	        })]
	      });
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
	      var alert = main_core.Dom.create('div', {
	        attrs: {
	          className: 'ui-alert ui-alert-warning ui-alert-icon-info ui-alert-xs'
	        }
	      });
	      var message = main_core.Dom.create('span', {
	        attrs: {
	          className: 'ui-alert-message'
	        },
	        text: main_core.Loc.getMessage('BIZPROC_AUTOMATION_BROKEN_LINK_MESSAGE_ERROR')
	      });
	      alert.appendChild(message);
	      alert.appendChild(main_core.Dom.create('span', {
	        attrs: {
	          className: 'ui-alert-close-btn'
	        },
	        events: {
	          click: function click() {
	            alert.style.display = 'none';
	          }
	        }
	      }));
	      return alert;
	    }
	  }, {
	    key: "saveRobotSettings",
	    value: function saveRobotSettings(form, robot, callback, btnNode) {
	      var _this6 = this;

	      if (btnNode) {
	        btnNode.classList.add('popup-window-button-wait');
	      }

	      this.onBeforeSaveRobotSettings();
	      var formData = BX.ajax.prepareForm(form);
	      var ajaxUrl = bizproc_automation.getGlobalContext().ajaxUrl;
	      var documentSigned = bizproc_automation.getGlobalContext().signedDocument;
	      BX.ajax({
	        method: 'POST',
	        dataType: 'json',
	        url: ajaxUrl,
	        data: {
	          ajax_action: 'save_robot_settings',
	          document_signed: documentSigned,
	          robot_json: Helper.toJsonString(robot.serialize()),
	          form_data_json: Helper.toJsonString(formData['data']),
	          form_data: formData['data']
	          /** @bug 0135641 */

	        },
	        onsuccess: function onsuccess(response) {
	          if (btnNode) {
	            btnNode.classList.remove('popup-window-button-wait');
	          }

	          if (response.SUCCESS) {
	            robot.updateData(response.DATA.robot);

	            _this6.setDelaySettingsFromForm(formData['data'], robot);

	            _this6.setConditionSettingsFromForm(formData['data'], robot);

	            if (robot.draft) {
	              babelHelpers.classPrivateFieldGet(_this6, _robots).push(robot);

	              _this6.insertRobotNode(robot.node);
	            }

	            robot.draft = false;
	            robot.reInit();

	            _this6.markModified();

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
	      var _this7 = this;

	      var constants = [];
	      Object.keys(babelHelpers.classPrivateFieldGet(this, _data$2).CONSTANTS).forEach(function (id) {
	        var constant = BX.clone(babelHelpers.classPrivateFieldGet(_this7, _data$2).CONSTANTS[id]);
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
	      this.emit('Template:constant:add'); // if (this.component)
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
	      } //TODO: only Description yet.


	      babelHelpers.classPrivateFieldGet(this, _data$2).CONSTANTS[id].Description = property.Description;
	      this.emit('Template:constant:update', {
	        constant: this.getConstant(id)
	      }); // if (this.component)
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
	      var _this8 = this;

	      var params = [];
	      Object.keys(babelHelpers.classPrivateFieldGet(this, _data$2).PARAMETERS).forEach(function (id) {
	        var param = BX.clone(babelHelpers.classPrivateFieldGet(_this8, _data$2).PARAMETERS[id]);
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
	      }); // if (this.component)
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
	      } //TODO: only Description yet.


	      babelHelpers.classPrivateFieldGet(this, _data$2).PARAMETERS[id].Description = property.Description;
	      this.emit('Template:parameter:update', {
	        parameter: this.getParameter(id)
	      }); // if (this.component)
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
	      var _this9 = this;

	      var variables = [];
	      Object.keys(babelHelpers.classPrivateFieldGet(this, _data$2).VARIABLES).forEach(function (id) {
	        var variable = BX.clone(babelHelpers.classPrivateFieldGet(_this9, _data$2).VARIABLES[id]);
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
	      return bizproc_automation.getGlobalContext().availableRobots.find(function (item) {
	        return item['CLASS'] === type;
	      });
	    }
	  }, {
	    key: "setGlobalVariables",
	    value: function setGlobalVariables() {
	      var globalVariables = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : [];
	      this.globalVariables = globalVariables;
	      return this;
	    }
	  }, {
	    key: "setGlobalConstants",
	    value: function setGlobalConstants() {
	      var globalConstants = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : [];
	      this.globalConstants = globalConstants;
	      return this;
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

	function _classPrivateFieldInitSpec$e(obj, privateMap, value) { _checkPrivateRedeclaration$e(obj, privateMap); privateMap.set(obj, value); }

	function _checkPrivateRedeclaration$e(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

	var _object = /*#__PURE__*/new WeakMap();

	var _field = /*#__PURE__*/new WeakMap();

	var _operator = /*#__PURE__*/new WeakMap();

	var _value$1 = /*#__PURE__*/new WeakMap();

	var Condition = /*#__PURE__*/function () {
	  function Condition(params, group) {
	    babelHelpers.classCallCheck(this, Condition);

	    _classPrivateFieldInitSpec$e(this, _object, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec$e(this, _field, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec$e(this, _operator, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec$e(this, _value$1, {
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
	        operator = '=';
	      }

	      babelHelpers.classPrivateFieldSet(this, _operator, operator);
	    }
	  }, {
	    key: "setValue",
	    value: function setValue(value) {
	      babelHelpers.classPrivateFieldSet(this, _value$1, value);

	      if (babelHelpers.classPrivateFieldGet(this, _operator) === '=' && babelHelpers.classPrivateFieldGet(this, _value$1) === '') {
	        babelHelpers.classPrivateFieldSet(this, _operator, 'empty');
	      } else if (babelHelpers.classPrivateFieldGet(this, _operator) === '!=' && babelHelpers.classPrivateFieldGet(this, _value$1) === '') {
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

	function _classPrivateFieldInitSpec$f(obj, privateMap, value) { _checkPrivateRedeclaration$f(obj, privateMap); privateMap.set(obj, value); }

	function _checkPrivateRedeclaration$f(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

	var _type$2 = /*#__PURE__*/new WeakMap();

	var _items = /*#__PURE__*/new WeakMap();

	var ConditionGroup = /*#__PURE__*/function () {
	  function ConditionGroup(params) {
	    var _this = this;

	    babelHelpers.classCallCheck(this, ConditionGroup);

	    _classPrivateFieldInitSpec$f(this, _type$2, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec$f(this, _items, {
	      writable: true,
	      value: void 0
	    });

	    babelHelpers.classPrivateFieldSet(this, _type$2, ConditionGroup.CONDITION_TYPE.field);
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
	        items: itemsArray
	      };
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
	        for (var i = 0; i < formFields[prefix + 'field'].length; ++i) {
	          if (formFields[prefix + 'field'][i] === '') {
	            continue;
	          }

	          var condition = new Condition({}, conditionGroup);
	          condition.setObject(formFields[prefix + 'object'][i]);
	          condition.setField(formFields[prefix + 'field'][i]);
	          condition.setOperator(formFields[prefix + 'operator'][i]);
	          condition.setValue(formFields[prefix + 'value'][i]);
	          var joiner = ConditionGroup.JOINER.And;

	          if (formFields[prefix + 'joiner'] && formFields[prefix + 'joiner'][i] === ConditionGroup.JOINER.Or) {
	            joiner = ConditionGroup.JOINER.Or;
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

	function _classPrivateFieldInitSpec$g(obj, privateMap, value) { _checkPrivateRedeclaration$g(obj, privateMap); privateMap.set(obj, value); }

	function _checkPrivateRedeclaration$g(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

	var _condition$2 = /*#__PURE__*/new WeakMap();

	var _fields$1 = /*#__PURE__*/new WeakMap();

	var _joiner = /*#__PURE__*/new WeakMap();

	var _fieldPrefix = /*#__PURE__*/new WeakMap();

	var _inlineSelector = /*#__PURE__*/new WeakMap();

	var ConditionSelector = /*#__PURE__*/function () {
	  function ConditionSelector(condition, options) {
	    babelHelpers.classCallCheck(this, ConditionSelector);

	    _classPrivateFieldInitSpec$g(this, _condition$2, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec$g(this, _fields$1, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec$g(this, _joiner, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec$g(this, _fieldPrefix, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec$g(this, _inlineSelector, {
	      writable: true,
	      value: void 0
	    });

	    babelHelpers.classPrivateFieldSet(this, _condition$2, condition);
	    babelHelpers.classPrivateFieldSet(this, _fields$1, []);
	    babelHelpers.classPrivateFieldSet(this, _joiner, bizproc_automation.ConditionGroup.JOINER.And);
	    babelHelpers.classPrivateFieldSet(this, _fieldPrefix, 'condition_');

	    if (main_core.Type.isPlainObject(options)) {
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
	      var conditionValueNode = this.valueNode = main_core.Dom.create("input", {
	        attrs: {
	          type: "hidden",
	          name: babelHelpers.classPrivateFieldGet(this, _fieldPrefix) + "value[]",
	          value: babelHelpers.classPrivateFieldGet(this, _condition$2).value
	        }
	      });
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
	        children: [conditionObjectNode, conditionFieldNode, conditionOperatorNode, conditionValueNode, conditionJoinerNode, labelNode, removeButtonNode, joinerButtonNode]
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
	        var valueLabel = babelHelpers.classPrivateFieldGet(this, _condition$2).operator.indexOf('empty') < 0 ? BX.Bizproc.FieldType.formatValuePrintable(field, babelHelpers.classPrivateFieldGet(this, _condition$2).value) : null;
	        this.labelNode.appendChild(main_core.Dom.create("span", {
	          attrs: {
	            className: "bizproc-automation-popup-settings-link"
	          },
	          text: field.Name
	        }));
	        this.labelNode.appendChild(main_core.Dom.create("span", {
	          attrs: {
	            className: "bizproc-automation-popup-settings-link"
	          },
	          text: this.getOperatorLabel(babelHelpers.classPrivateFieldGet(this, _condition$2).operator)
	        }));

	        if (valueLabel) {
	          this.labelNode.appendChild(main_core.Dom.create("span", {
	            attrs: {
	              className: "bizproc-automation-popup-settings-link"
	            },
	            text: valueLabel
	          }));
	        }
	      } else {
	        this.labelNode.appendChild(main_core.Dom.create("span", {
	          attrs: {
	            className: "bizproc-automation-popup-settings-link"
	          },
	          text: main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_CONDITION_EMPTY')
	        }));
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

	      fieldSelect.value = selectedField.Id;
	      objectSelect.value = selectedField.ObjectId;
	      fieldSelectLabel.textContent = selectedField.Name;
	      var valueInput = babelHelpers.classPrivateFieldGet(this, _condition$2).operator.indexOf('empty') < 0 ? this.createValueNode(selectedField, babelHelpers.classPrivateFieldGet(this, _condition$2).value) : null;
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
	              var valueInput = valueWrapper.querySelector('[name^="' + babelHelpers.classPrivateFieldGet(self, _fieldPrefix) + 'value"]');

	              if (valueInput) {
	                babelHelpers.classPrivateFieldGet(self, _condition$2).setValue(valueInput.value);
	              } else {
	                babelHelpers.classPrivateFieldGet(self, _condition$2).setValue('');
	              }

	              self.setLabelText();
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
	        this.fieldDialog = new BX.Bizproc.Automation.Selector.InlineSelectorCondition(fieldSelectLabel, fields, function (property) {
	          fieldSelectLabel.textContent = property.Name;
	          fieldSelect.value = property.Id;
	          objectSelect.value = property.ObjectId;
	          BX.fireEvent(fieldSelect, 'change');
	        }, babelHelpers.classPrivateFieldGet(this, _condition$2));
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
	          this.valueNode.value = babelHelpers.classPrivateFieldGet(this, _condition$2).value;
	        }
	      }
	    }
	  }, {
	    key: "onFieldChange",
	    value: function onFieldChange(selectNode, conditionWrapper, valueWrapper, objectSelect) {
	      var field = this.getField(objectSelect.value, selectNode.value);
	      var operatorNode = this.createOperatorNode(field, valueWrapper);
	      conditionWrapper.replaceChild(operatorNode, conditionWrapper.firstChild);
	      this.onOperatorChange(operatorNode, field, valueWrapper);
	    }
	  }, {
	    key: "onOperatorChange",
	    value: function onOperatorChange(selectNode, field, valueWrapper) {
	      main_core.Dom.clean(valueWrapper);

	      if (selectNode.value.indexOf('empty') < 0) {
	        var valueNode = this.createValueNode(field);
	        valueWrapper.appendChild(valueNode);
	      }
	    } // TODO - fix this method

	  }, {
	    key: "getField",
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
	      var list = {
	        '!empty': main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_CONDITION_NOT_EMPTY'),
	        'empty': main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_CONDITION_EMPTY'),
	        '=': main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_CONDITION_EQ'),
	        '!=': main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_CONDITION_NE')
	      };

	      switch (fieldType) {
	        case 'file':
	        case 'UF:crm':
	        case 'UF:resourcebooking':
	          list = {
	            '!empty': main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_CONDITION_NOT_EMPTY'),
	            'empty': main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_CONDITION_EMPTY')
	          };
	          break;

	        case 'bool':
	        case 'select':
	          if (multiple) {
	            list['contain'] = main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_CONDITION_CONTAIN');
	            list['!contain'] = main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_CONDITION_NOT_CONTAIN');
	          }

	          break;

	        case 'user':
	          list['in'] = main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_CONDITION_IN');
	          list['!in'] = main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_CONDITION_NOT_IN');
	          list['contain'] = main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_CONDITION_CONTAIN');
	          list['!contain'] = main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_CONDITION_NOT_CONTAIN');
	          break;

	        default:
	          list['in'] = main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_CONDITION_IN');
	          list['!in'] = main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_CONDITION_NOT_IN');
	          list['contain'] = main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_CONDITION_CONTAIN');
	          list['!contain'] = main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_CONDITION_NOT_CONTAIN');
	          list['>'] = main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_CONDITION_GT');
	          list['>='] = main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_CONDITION_GTE');
	          list['<'] = main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_CONDITION_LT');
	          list['<='] = main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_CONDITION_LTE');
	      }

	      return list;
	    }
	  }, {
	    key: "getOperatorLabel",
	    value: function getOperatorLabel(id) {
	      return this.getOperators()[id];
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
	      var docType = bizproc_automation.Designer.getInstance().component ? bizproc_automation.Designer.getInstance().component.document.getRawType() : BX.Bizproc.Automation.API.documentType;
	      var field = BX.clone(docField);
	      field.Multiple = false;
	      return BX.Bizproc.FieldType.renderControl(docType, field, babelHelpers.classPrivateFieldGet(this, _fieldPrefix) + 'value', value);
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
	      this.labelNode = this.fieldNode = this.operatorNode = this.valueNode = this.node = null;
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

	function _classPrivateFieldInitSpec$h(obj, privateMap, value) { _checkPrivateRedeclaration$h(obj, privateMap); privateMap.set(obj, value); }

	function _checkPrivateRedeclaration$h(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

	var _conditionGroup = /*#__PURE__*/new WeakMap();

	var _fields$2 = /*#__PURE__*/new WeakMap();

	var _fieldPrefix$1 = /*#__PURE__*/new WeakMap();

	var _itemSelectors = /*#__PURE__*/new WeakMap();

	var ConditionGroupSelector = /*#__PURE__*/function () {
	  function ConditionGroupSelector(conditionGroup, options) {
	    babelHelpers.classCallCheck(this, ConditionGroupSelector);

	    _classPrivateFieldInitSpec$h(this, _conditionGroup, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec$h(this, _fields$2, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec$h(this, _fieldPrefix$1, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec$h(this, _itemSelectors, {
	      writable: true,
	      value: void 0
	    });

	    babelHelpers.classPrivateFieldSet(this, _conditionGroup, conditionGroup);
	    babelHelpers.classPrivateFieldSet(this, _fields$2, []);
	    babelHelpers.classPrivateFieldSet(this, _fieldPrefix$1, 'condition_');
	    babelHelpers.classPrivateFieldSet(this, _itemSelectors, []);

	    if (main_core.Type.isPlainObject(options)) {
	      if (main_core.Type.isArray(options.fields)) {
	        babelHelpers.classPrivateFieldSet(this, _fields$2, options.fields);
	      }

	      if (options.fieldPrefix) {
	        babelHelpers.classPrivateFieldSet(this, _fieldPrefix$1, options.fieldPrefix);
	      }
	    }
	  }

	  babelHelpers.createClass(ConditionGroupSelector, [{
	    key: "createNode",
	    value: function createNode() {
	      var me = this;
	      var conditionNodes = [];
	      var fields = babelHelpers.classPrivateFieldGet(this, _fields$2);
	      babelHelpers.classPrivateFieldGet(this, _conditionGroup).getItems().forEach(function (item) {
	        var conditionSelector = new ConditionSelector(item[0], {
	          fields: fields,
	          joiner: item[1],
	          fieldPrefix: babelHelpers.classPrivateFieldGet(me, _fieldPrefix$1)
	        });
	        babelHelpers.classPrivateFieldGet(this, _itemSelectors).push(conditionSelector);
	        conditionNodes.push(conditionSelector.createNode());
	      }, this);
	      conditionNodes.push(main_core.Dom.create("a", {
	        attrs: {
	          className: "bizproc-automation-popup-settings-link"
	        },
	        text: '[+]',
	        events: {
	          click: function click() {
	            me.addItem(this);
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
	        fieldPrefix: babelHelpers.classPrivateFieldGet(this, _fieldPrefix$1)
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

	var DelayIntervalSelector = /*#__PURE__*/function () {
	  function DelayIntervalSelector(options) {
	    babelHelpers.classCallCheck(this, DelayIntervalSelector);
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
	    }
	  }

	  babelHelpers.createClass(DelayIntervalSelector, [{
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
	          'data-hint': main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_DELAY_WORKTIME_HELP_2')
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
	          text: main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_WORK_TIME')
	        }), workTimeHelpNode]
	      }));
	      var self = this; //init modern Help tips

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
	              var formData = BX.ajax.prepareForm(form);
	              self.saveFormData(formData['data']);
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
	      if (formData['type'] === 'now') {
	        this.delay.setNow();
	      } else if (formData['type'] === DelayInterval.DELAY_TYPE.In) {
	        this.delay.setType(DelayInterval.DELAY_TYPE.In);
	        this.delay.setValue(0);
	        this.delay.setValueType('i');
	        this.delay.setBasis(formData['basis_in']);
	      } else {
	        this.delay.setType(formData['type']);
	        this.delay.setValue(formData['value_' + formData['type']]);
	        this.delay.setValueType(formData['value_type_' + formData['type']]);

	        if (formData['type'] === DelayInterval.DELAY_TYPE.After) {
	          if (this.useAfterBasis) {
	            this.delay.setBasis(formData['basis_after']);
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
	          this.delay.setBasis(formData['basis_before']);
	        }
	      }

	      this.delay.setWorkTime(formData['worktime']);
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
	      var basisField = this.getBasisField(delay.basis);
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
	            });
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

	      if (delayType === DelayInterval.DELAY_TYPE.After) {
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
	  return DelayIntervalSelector;
	}();

	function _classPrivateFieldInitSpec$i(obj, privateMap, value) { _checkPrivateRedeclaration$i(obj, privateMap); privateMap.set(obj, value); }

	function _checkPrivateRedeclaration$i(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

	var _values = /*#__PURE__*/new WeakMap();

	var Context = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(Context, _EventEmitter);

	  function Context(defaultValue) {
	    var _this;

	    babelHelpers.classCallCheck(this, Context);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Context).call(this));

	    _classPrivateFieldInitSpec$i(babelHelpers.assertThisInitialized(_this), _values, {
	      writable: true,
	      value: void 0
	    });

	    _this.setEventNamespace('BX.Bizproc.Automation.Context');

	    if (main_core.Type.isPlainObject(defaultValue)) {
	      babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _values, defaultValue);
	    }

	    return _this;
	  }

	  babelHelpers.createClass(Context, [{
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
	  return Context;
	}(main_core_events.EventEmitter);

	var AutomationContext = /*#__PURE__*/function (_Context) {
	  babelHelpers.inherits(AutomationContext, _Context);

	  function AutomationContext(props) {
	    babelHelpers.classCallCheck(this, AutomationContext);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(AutomationContext).call(this, props));
	  }

	  babelHelpers.createClass(AutomationContext, [{
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
	  }]);
	  return AutomationContext;
	}(Context);

	function getGlobalContext() {
	  main_core.Reflection.namespace('BX.Bizproc.Automation');
	  var context = BX.Bizproc.Automation.Context;

	  if (context instanceof AutomationContext) {
	    return context;
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
	  main_core.Reflection.namespace('BX.Bizproc.Automation');
	  BX.Bizproc.Automation.Context = context;
	  return context;
	}

	exports.TemplatesScheme = TemplatesScheme;
	exports.AutomationContext = AutomationContext;
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
	exports.DelayInterval = DelayInterval;
	exports.DelayIntervalSelector = DelayIntervalSelector;
	exports.HelpHint = HelpHint;
	exports.Helper = Helper;
	exports.RobotEntry = RobotEntry;
	exports.TriggerEntry = TriggerEntry;
	exports.TrackingEntryBuilder = TrackingEntryBuilder;
	exports.TrackingEntry = TrackingEntry;
	exports.TrackingStatus = TrackingStatus;
	exports.Tracker = Tracker;

}((this.BX.Bizproc = this.BX.Bizproc || {}),BX.Main,BX.Event,BX.Bizproc,BX,BX));
//# sourceMappingURL=automation.bundle.js.map
