this.BX = this.BX || {};
(function (exports,main_core) {
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

	function _createForOfIteratorHelper(o, allowArrayLike) { var it = typeof Symbol !== "undefined" && o[Symbol.iterator] || o["@@iterator"]; if (!it) { if (Array.isArray(o) || (it = _unsupportedIterableToArray(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = it.call(o); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it.return != null) it.return(); } finally { if (didErr) throw err; } } }; }

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

	exports.TemplatesScheme = TemplatesScheme;
	exports.TemplateScope = TemplateScope;

}((this.BX.Bizproc = this.BX.Bizproc || {}),BX));
//# sourceMappingURL=automation.bundle.js.map
