/* eslint-disable */
;(function() {

	if (typeof window.BX === 'function')
	{
		return;
	}

/**
 * Babel external helpers
 * (c) 2018 Babel
 * @license MIT
 */
(function (global) {
  var babelHelpers = global.babelHelpers = {};

  function _typeof(obj) {
    if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") {
      babelHelpers.typeof = _typeof = function (obj) {
        return typeof obj;
      };
    } else {
      babelHelpers.typeof = _typeof = function (obj) {
        return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj;
      };
    }

    return _typeof(obj);
  }

  babelHelpers.typeof = _typeof;
  var REACT_ELEMENT_TYPE;

  function _createRawReactElement(type, props, key, children) {
    if (!REACT_ELEMENT_TYPE) {
      REACT_ELEMENT_TYPE = typeof Symbol === "function" && Symbol.for && Symbol.for("react.element") || 0xeac7;
    }

    var defaultProps = type && type.defaultProps;
    var childrenLength = arguments.length - 3;

    if (!props && childrenLength !== 0) {
      props = {
        children: void 0
      };
    }

    if (props && defaultProps) {
      for (var propName in defaultProps) {
        if (props[propName] === void 0) {
          props[propName] = defaultProps[propName];
        }
      }
    } else if (!props) {
      props = defaultProps || {};
    }

    if (childrenLength === 1) {
      props.children = children;
    } else if (childrenLength > 1) {
      var childArray = new Array(childrenLength);

      for (var i = 0; i < childrenLength; i++) {
        childArray[i] = arguments[i + 3];
      }

      props.children = childArray;
    }

    return {
      $$typeof: REACT_ELEMENT_TYPE,
      type: type,
      key: key === undefined ? null : '' + key,
      ref: null,
      props: props,
      _owner: null
    };
  }

  babelHelpers.jsx = _createRawReactElement;

  function _asyncIterator(iterable) {
    var method;

    if (typeof Symbol === "function") {
      if (Symbol.asyncIterator) {
        method = iterable[Symbol.asyncIterator];
        if (method != null) return method.call(iterable);
      }

      if (Symbol.iterator) {
        method = iterable[Symbol.iterator];
        if (method != null) return method.call(iterable);
      }
    }

    throw new TypeError("Object is not async iterable");
  }

  babelHelpers.asyncIterator = _asyncIterator;

  function _AwaitValue(value) {
    this.wrapped = value;
  }

  babelHelpers.AwaitValue = _AwaitValue;

  function AsyncGenerator(gen) {
    var front, back;

    function send(key, arg) {
      return new Promise(function (resolve, reject) {
        var request = {
          key: key,
          arg: arg,
          resolve: resolve,
          reject: reject,
          next: null
        };

        if (back) {
          back = back.next = request;
        } else {
          front = back = request;
          resume(key, arg);
        }
      });
    }

    function resume(key, arg) {
      try {
        var result = gen[key](arg);
        var value = result.value;
        var wrappedAwait = value instanceof babelHelpers.AwaitValue;
        Promise.resolve(wrappedAwait ? value.wrapped : value).then(function (arg) {
          if (wrappedAwait) {
            resume("next", arg);
            return;
          }

          settle(result.done ? "return" : "normal", arg);
        }, function (err) {
          resume("throw", err);
        });
      } catch (err) {
        settle("throw", err);
      }
    }

    function settle(type, value) {
      switch (type) {
        case "return":
          front.resolve({
            value: value,
            done: true
          });
          break;

        case "throw":
          front.reject(value);
          break;

        default:
          front.resolve({
            value: value,
            done: false
          });
          break;
      }

      front = front.next;

      if (front) {
        resume(front.key, front.arg);
      } else {
        back = null;
      }
    }

    this._invoke = send;

    if (typeof gen.return !== "function") {
      this.return = undefined;
    }
  }

  if (typeof Symbol === "function" && Symbol.asyncIterator) {
    AsyncGenerator.prototype[Symbol.asyncIterator] = function () {
      return this;
    };
  }

  AsyncGenerator.prototype.next = function (arg) {
    return this._invoke("next", arg);
  };

  AsyncGenerator.prototype.throw = function (arg) {
    return this._invoke("throw", arg);
  };

  AsyncGenerator.prototype.return = function (arg) {
    return this._invoke("return", arg);
  };

  babelHelpers.AsyncGenerator = AsyncGenerator;

  function _wrapAsyncGenerator(fn) {
    return function () {
      return new babelHelpers.AsyncGenerator(fn.apply(this, arguments));
    };
  }

  babelHelpers.wrapAsyncGenerator = _wrapAsyncGenerator;

  function _awaitAsyncGenerator(value) {
    return new babelHelpers.AwaitValue(value);
  }

  babelHelpers.awaitAsyncGenerator = _awaitAsyncGenerator;

  function _asyncGeneratorDelegate(inner, awaitWrap) {
    var iter = {},
        waiting = false;

    function pump(key, value) {
      waiting = true;
      value = new Promise(function (resolve) {
        resolve(inner[key](value));
      });
      return {
        done: false,
        value: awaitWrap(value)
      };
    }

    ;

    if (typeof Symbol === "function" && Symbol.iterator) {
      iter[Symbol.iterator] = function () {
        return this;
      };
    }

    iter.next = function (value) {
      if (waiting) {
        waiting = false;
        return value;
      }

      return pump("next", value);
    };

    if (typeof inner.throw === "function") {
      iter.throw = function (value) {
        if (waiting) {
          waiting = false;
          throw value;
        }

        return pump("throw", value);
      };
    }

    if (typeof inner.return === "function") {
      iter.return = function (value) {
        return pump("return", value);
      };
    }

    return iter;
  }

  babelHelpers.asyncGeneratorDelegate = _asyncGeneratorDelegate;

  function asyncGeneratorStep(gen, resolve, reject, _next, _throw, key, arg) {
    try {
      var info = gen[key](arg);
      var value = info.value;
    } catch (error) {
      reject(error);
      return;
    }

    if (info.done) {
      resolve(value);
    } else {
      Promise.resolve(value).then(_next, _throw);
    }
  }

  function _asyncToGenerator(fn) {
    return function () {
      var self = this,
          args = arguments;
      return new Promise(function (resolve, reject) {
        var gen = fn.apply(self, args);

        function _next(value) {
          asyncGeneratorStep(gen, resolve, reject, _next, _throw, "next", value);
        }

        function _throw(err) {
          asyncGeneratorStep(gen, resolve, reject, _next, _throw, "throw", err);
        }

        _next(undefined);
      });
    };
  }

  babelHelpers.asyncToGenerator = _asyncToGenerator;

  function _classCallCheck(instance, Constructor) {
    if (!(instance instanceof Constructor)) {
      throw new TypeError("Cannot call a class as a function");
    }
  }

  babelHelpers.classCallCheck = _classCallCheck;

  function _defineProperties(target, props) {
    for (var i = 0; i < props.length; i++) {
      var descriptor = props[i];
      descriptor.enumerable = descriptor.enumerable || false;
      descriptor.configurable = true;
      if ("value" in descriptor) descriptor.writable = true;
      Object.defineProperty(target, descriptor.key, descriptor);
    }
  }

  function _createClass(Constructor, protoProps, staticProps) {
    if (protoProps) _defineProperties(Constructor.prototype, protoProps);
    if (staticProps) _defineProperties(Constructor, staticProps);
    return Constructor;
  }

  babelHelpers.createClass = _createClass;

  function _defineEnumerableProperties(obj, descs) {
    for (var key in descs) {
      var desc = descs[key];
      desc.configurable = desc.enumerable = true;
      if ("value" in desc) desc.writable = true;
      Object.defineProperty(obj, key, desc);
    }

    if (Object.getOwnPropertySymbols) {
      var objectSymbols = Object.getOwnPropertySymbols(descs);

      for (var i = 0; i < objectSymbols.length; i++) {
        var sym = objectSymbols[i];
        var desc = descs[sym];
        desc.configurable = desc.enumerable = true;
        if ("value" in desc) desc.writable = true;
        Object.defineProperty(obj, sym, desc);
      }
    }

    return obj;
  }

  babelHelpers.defineEnumerableProperties = _defineEnumerableProperties;

  function _defaults(obj, defaults) {
    var keys = Object.getOwnPropertyNames(defaults);

    for (var i = 0; i < keys.length; i++) {
      var key = keys[i];
      var value = Object.getOwnPropertyDescriptor(defaults, key);

      if (value && value.configurable && obj[key] === undefined) {
        Object.defineProperty(obj, key, value);
      }
    }

    return obj;
  }

  babelHelpers.defaults = _defaults;

  function _defineProperty(obj, key, value) {
    if (key in obj) {
      Object.defineProperty(obj, key, {
        value: value,
        enumerable: true,
        configurable: true,
        writable: true
      });
    } else {
      obj[key] = value;
    }

    return obj;
  }

  babelHelpers.defineProperty = _defineProperty;

  function _extends() {
    babelHelpers.extends = _extends = Object.assign || function (target) {
      for (var i = 1; i < arguments.length; i++) {
        var source = arguments[i];

        for (var key in source) {
          if (Object.prototype.hasOwnProperty.call(source, key)) {
            target[key] = source[key];
          }
        }
      }

      return target;
    };

    return _extends.apply(this, arguments);
  }

  babelHelpers.extends = _extends;

  function _objectSpread(target) {
    for (var i = 1; i < arguments.length; i++) {
      var source = arguments[i] != null ? arguments[i] : {};
      var ownKeys = Object.keys(source);

      if (typeof Object.getOwnPropertySymbols === 'function') {
        ownKeys = ownKeys.concat(Object.getOwnPropertySymbols(source).filter(function (sym) {
          return Object.getOwnPropertyDescriptor(source, sym).enumerable;
        }));
      }

      ownKeys.forEach(function (key) {
        babelHelpers.defineProperty(target, key, source[key]);
      });
    }

    return target;
  }

  babelHelpers.objectSpread = _objectSpread;

  function _inherits(subClass, superClass) {
    if (typeof superClass !== "function" && superClass !== null) {
      throw new TypeError("Super expression must either be null or a function");
    }

    subClass.prototype = Object.create(superClass && superClass.prototype, {
      constructor: {
        value: subClass,
        writable: true,
        configurable: true
      }
    });
    if (superClass) babelHelpers.setPrototypeOf(subClass, superClass);
  }

  babelHelpers.inherits = _inherits;

  function _inheritsLoose(subClass, superClass) {
    subClass.prototype = Object.create(superClass.prototype);
    subClass.prototype.constructor = subClass;
    subClass.__proto__ = superClass;
  }

  babelHelpers.inheritsLoose = _inheritsLoose;

  function _getPrototypeOf(o) {
    babelHelpers.getPrototypeOf = _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) {
      return o.__proto__ || Object.getPrototypeOf(o);
    };
    return _getPrototypeOf(o);
  }

  babelHelpers.getPrototypeOf = _getPrototypeOf;

  function _setPrototypeOf(o, p) {
    babelHelpers.setPrototypeOf = _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) {
      o.__proto__ = p;
      return o;
    };

    return _setPrototypeOf(o, p);
  }

  babelHelpers.setPrototypeOf = _setPrototypeOf;

  function isNativeReflectConstruct() {
    if (typeof Reflect === "undefined" || !Reflect.construct) return false;
    if (Reflect.construct.sham) return false;
    if (typeof Proxy === "function") return true;

    try {
      Date.prototype.toString.call(Reflect.construct(Date, [], function () {}));
      return true;
    } catch (e) {
      return false;
    }
  }

  function _construct(Parent, args, Class) {
    if (isNativeReflectConstruct()) {
      babelHelpers.construct = _construct = Reflect.construct;
    } else {
      babelHelpers.construct = _construct = function _construct(Parent, args, Class) {
        var a = [null];
        a.push.apply(a, args);
        var Constructor = Function.bind.apply(Parent, a);
        var instance = new Constructor();
        if (Class) babelHelpers.setPrototypeOf(instance, Class.prototype);
        return instance;
      };
    }

    return _construct.apply(null, arguments);
  }

  babelHelpers.construct = _construct;

  function _isNativeFunction(fn) {
    return Function.toString.call(fn).indexOf("[native code]") !== -1;
  }

  babelHelpers.isNativeFunction = _isNativeFunction;

  function _wrapNativeSuper(Class) {
    var _cache = typeof Map === "function" ? new Map() : undefined;

    babelHelpers.wrapNativeSuper = _wrapNativeSuper = function _wrapNativeSuper(Class) {
      if (Class === null || !babelHelpers.isNativeFunction(Class)) return Class;

      if (typeof Class !== "function") {
        throw new TypeError("Super expression must either be null or a function");
      }

      if (typeof _cache !== "undefined") {
        if (_cache.has(Class)) return _cache.get(Class);

        _cache.set(Class, Wrapper);
      }

      function Wrapper() {
        return babelHelpers.construct(Class, arguments, babelHelpers.getPrototypeOf(this).constructor);
      }

      Wrapper.prototype = Object.create(Class.prototype, {
        constructor: {
          value: Wrapper,
          enumerable: false,
          writable: true,
          configurable: true
        }
      });
      return babelHelpers.setPrototypeOf(Wrapper, Class);
    };

    return _wrapNativeSuper(Class);
  }

  babelHelpers.wrapNativeSuper = _wrapNativeSuper;

  function _instanceof(left, right) {
    if (right != null && typeof Symbol !== "undefined" && right[Symbol.hasInstance]) {
      return right[Symbol.hasInstance](left);
    } else {
      return left instanceof right;
    }
  }

  babelHelpers.instanceof = _instanceof;

  function _interopRequireDefault(obj) {
    return obj && obj.__esModule ? obj : {
      default: obj
    };
  }

  babelHelpers.interopRequireDefault = _interopRequireDefault;

  function _interopRequireWildcard(obj) {
    if (obj && obj.__esModule) {
      return obj;
    } else {
      var newObj = {};

      if (obj != null) {
        for (var key in obj) {
          if (Object.prototype.hasOwnProperty.call(obj, key)) {
            var desc = Object.defineProperty && Object.getOwnPropertyDescriptor ? Object.getOwnPropertyDescriptor(obj, key) : {};

            if (desc.get || desc.set) {
              Object.defineProperty(newObj, key, desc);
            } else {
              newObj[key] = obj[key];
            }
          }
        }
      }

      newObj.default = obj;
      return newObj;
    }
  }

  babelHelpers.interopRequireWildcard = _interopRequireWildcard;

  function _newArrowCheck(innerThis, boundThis) {
    if (innerThis !== boundThis) {
      throw new TypeError("Cannot instantiate an arrow function");
    }
  }

  babelHelpers.newArrowCheck = _newArrowCheck;

  function _objectDestructuringEmpty(obj) {
    if (obj == null) throw new TypeError("Cannot destructure undefined");
  }

  babelHelpers.objectDestructuringEmpty = _objectDestructuringEmpty;

  function _objectWithoutPropertiesLoose(source, excluded) {
    if (source == null) return {};
    var target = {};
    var sourceKeys = Object.keys(source);
    var key, i;

    for (i = 0; i < sourceKeys.length; i++) {
      key = sourceKeys[i];
      if (excluded.indexOf(key) >= 0) continue;
      target[key] = source[key];
    }

    return target;
  }

  babelHelpers.objectWithoutPropertiesLoose = _objectWithoutPropertiesLoose;

  function _objectWithoutProperties(source, excluded) {
    if (source == null) return {};
    var target = babelHelpers.objectWithoutPropertiesLoose(source, excluded);
    var key, i;

    if (Object.getOwnPropertySymbols) {
      var sourceSymbolKeys = Object.getOwnPropertySymbols(source);

      for (i = 0; i < sourceSymbolKeys.length; i++) {
        key = sourceSymbolKeys[i];
        if (excluded.indexOf(key) >= 0) continue;
        if (!Object.prototype.propertyIsEnumerable.call(source, key)) continue;
        target[key] = source[key];
      }
    }

    return target;
  }

  babelHelpers.objectWithoutProperties = _objectWithoutProperties;

  function _assertThisInitialized(self) {
    if (self === void 0) {
      throw new ReferenceError("this hasn't been initialised - super() hasn't been called");
    }

    return self;
  }

  babelHelpers.assertThisInitialized = _assertThisInitialized;

  function _possibleConstructorReturn(self, call) {
    if (call && (typeof call === "object" || typeof call === "function")) {
      return call;
    }

    return babelHelpers.assertThisInitialized(self);
  }

  babelHelpers.possibleConstructorReturn = _possibleConstructorReturn;

  function _superPropBase(object, property) {
    while (!Object.prototype.hasOwnProperty.call(object, property)) {
      object = babelHelpers.getPrototypeOf(object);
      if (object === null) break;
    }

    return object;
  }

  babelHelpers.superPropBase = _superPropBase;

  function _get(target, property, receiver) {
    if (typeof Reflect !== "undefined" && Reflect.get) {
      babelHelpers.get = _get = Reflect.get;
    } else {
      babelHelpers.get = _get = function _get(target, property, receiver) {
        var base = babelHelpers.superPropBase(target, property);
        if (!base) return;
        var desc = Object.getOwnPropertyDescriptor(base, property);

        if (desc.get) {
          return desc.get.call(receiver);
        }

        return desc.value;
      };
    }

    return _get(target, property, receiver || target);
  }

  babelHelpers.get = _get;

  function set(target, property, value, receiver) {
    if (typeof Reflect !== "undefined" && Reflect.set) {
      set = Reflect.set;
    } else {
      set = function set(target, property, value, receiver) {
        var base = babelHelpers.superPropBase(target, property);
        var desc;

        if (base) {
          desc = Object.getOwnPropertyDescriptor(base, property);

          if (desc.set) {
            desc.set.call(receiver, value);
            return true;
          } else if (!desc.writable) {
            return false;
          }
        }

        desc = Object.getOwnPropertyDescriptor(receiver, property);

        if (desc) {
          if (!desc.writable) {
            return false;
          }

          desc.value = value;
          Object.defineProperty(receiver, property, desc);
        } else {
          babelHelpers.defineProperty(receiver, property, value);
        }

        return true;
      };
    }

    return set(target, property, value, receiver);
  }

  function _set(target, property, value, receiver, isStrict) {
    var s = set(target, property, value, receiver || target);

    if (!s && isStrict) {
      throw new Error('failed to set property');
    }

    return value;
  }

  babelHelpers.set = _set;

  function _taggedTemplateLiteral(strings, raw) {
    if (!raw) {
      raw = strings.slice(0);
    }

    return Object.freeze(Object.defineProperties(strings, {
      raw: {
        value: Object.freeze(raw)
      }
    }));
  }

  babelHelpers.taggedTemplateLiteral = _taggedTemplateLiteral;

  function _taggedTemplateLiteralLoose(strings, raw) {
    if (!raw) {
      raw = strings.slice(0);
    }

    strings.raw = raw;
    return strings;
  }

  babelHelpers.taggedTemplateLiteralLoose = _taggedTemplateLiteralLoose;

  function _temporalRef(val, name) {
    if (val === babelHelpers.temporalUndefined) {
      throw new ReferenceError(name + " is not defined - temporal dead zone");
    } else {
      return val;
    }
  }

  babelHelpers.temporalRef = _temporalRef;

  function _readOnlyError(name) {
    throw new Error("\"" + name + "\" is read-only");
  }

  babelHelpers.readOnlyError = _readOnlyError;

  function _classNameTDZError(name) {
    throw new Error("Class \"" + name + "\" cannot be referenced in computed property keys.");
  }

  babelHelpers.classNameTDZError = _classNameTDZError;
  babelHelpers.temporalUndefined = {};

  function _slicedToArray(arr, i) {
    return babelHelpers.arrayWithHoles(arr) || babelHelpers.iterableToArrayLimit(arr, i) || babelHelpers.nonIterableRest();
  }

  babelHelpers.slicedToArray = _slicedToArray;

  function _slicedToArrayLoose(arr, i) {
    return babelHelpers.arrayWithHoles(arr) || babelHelpers.iterableToArrayLimitLoose(arr, i) || babelHelpers.nonIterableRest();
  }

  babelHelpers.slicedToArrayLoose = _slicedToArrayLoose;

  function _toArray(arr) {
    return babelHelpers.arrayWithHoles(arr) || babelHelpers.iterableToArray(arr) || babelHelpers.nonIterableRest();
  }

  babelHelpers.toArray = _toArray;

  function _toConsumableArray(arr) {
    return babelHelpers.arrayWithoutHoles(arr) || babelHelpers.iterableToArray(arr) || babelHelpers.nonIterableSpread();
  }

  babelHelpers.toConsumableArray = _toConsumableArray;

  function _arrayWithoutHoles(arr) {
    if (Array.isArray(arr)) {
      for (var i = 0, arr2 = new Array(arr.length); i < arr.length; i++) arr2[i] = arr[i];

      return arr2;
    }
  }

  babelHelpers.arrayWithoutHoles = _arrayWithoutHoles;

  function _arrayWithHoles(arr) {
    if (Array.isArray(arr)) return arr;
  }

  babelHelpers.arrayWithHoles = _arrayWithHoles;

  function _iterableToArray(iter) {
    if (Symbol.iterator in Object(iter) || Object.prototype.toString.call(iter) === "[object Arguments]") return Array.from(iter);
  }

  babelHelpers.iterableToArray = _iterableToArray;

  function _iterableToArrayLimit(arr, i) {
    var _arr = [];
    var _n = true;
    var _d = false;
    var _e = undefined;

    try {
      for (var _i = arr[Symbol.iterator](), _s; !(_n = (_s = _i.next()).done); _n = true) {
        _arr.push(_s.value);

        if (i && _arr.length === i) break;
      }
    } catch (err) {
      _d = true;
      _e = err;
    } finally {
      try {
        if (!_n && _i["return"] != null) _i["return"]();
      } finally {
        if (_d) throw _e;
      }
    }

    return _arr;
  }

  babelHelpers.iterableToArrayLimit = _iterableToArrayLimit;

  function _iterableToArrayLimitLoose(arr, i) {
    var _arr = [];

    for (var _iterator = arr[Symbol.iterator](), _step; !(_step = _iterator.next()).done;) {
      _arr.push(_step.value);

      if (i && _arr.length === i) break;
    }

    return _arr;
  }

  babelHelpers.iterableToArrayLimitLoose = _iterableToArrayLimitLoose;

  function _nonIterableSpread() {
    throw new TypeError("Invalid attempt to spread non-iterable instance");
  }

  babelHelpers.nonIterableSpread = _nonIterableSpread;

  function _nonIterableRest() {
    throw new TypeError("Invalid attempt to destructure non-iterable instance");
  }

  babelHelpers.nonIterableRest = _nonIterableRest;

  function _skipFirstGeneratorNext(fn) {
    return function () {
      var it = fn.apply(this, arguments);
      it.next();
      return it;
    };
  }

  babelHelpers.skipFirstGeneratorNext = _skipFirstGeneratorNext;

  function _toPropertyKey(key) {
    if (typeof key === "symbol") {
      return key;
    } else {
      return String(key);
    }
  }

  babelHelpers.toPropertyKey = _toPropertyKey;

  function _initializerWarningHelper(descriptor, context) {
    throw new Error('Decorating class property failed. Please ensure that ' + 'proposal-class-properties is enabled and set to use loose mode. ' + 'To use proposal-class-properties in spec mode with decorators, wait for ' + 'the next major version of decorators in stage 2.');
  }

  babelHelpers.initializerWarningHelper = _initializerWarningHelper;

  function _initializerDefineProperty(target, property, descriptor, context) {
    if (!descriptor) return;
    Object.defineProperty(target, property, {
      enumerable: descriptor.enumerable,
      configurable: descriptor.configurable,
      writable: descriptor.writable,
      value: descriptor.initializer ? descriptor.initializer.call(context) : void 0
    });
  }

  babelHelpers.initializerDefineProperty = _initializerDefineProperty;

  function _applyDecoratedDescriptor(target, property, decorators, descriptor, context) {
    var desc = {};
    Object['ke' + 'ys'](descriptor).forEach(function (key) {
      desc[key] = descriptor[key];
    });
    desc.enumerable = !!desc.enumerable;
    desc.configurable = !!desc.configurable;

    if ('value' in desc || desc.initializer) {
      desc.writable = true;
    }

    desc = decorators.slice().reverse().reduce(function (desc, decorator) {
      return decorator(target, property, desc) || desc;
    }, desc);

    if (context && desc.initializer !== void 0) {
      desc.value = desc.initializer ? desc.initializer.call(context) : void 0;
      desc.initializer = undefined;
    }

    if (desc.initializer === void 0) {
      Object['define' + 'Property'](target, property, desc);
      desc = null;
    }

    return desc;
  }

  babelHelpers.applyDecoratedDescriptor = _applyDecoratedDescriptor;
  var id = 0;

  function _classPrivateFieldKey(name) {
    return "__private_" + id++ + "_" + name;
  }

  babelHelpers.classPrivateFieldLooseKey = _classPrivateFieldKey;

  function _classPrivateFieldBase(receiver, privateKey) {
    if (!Object.prototype.hasOwnProperty.call(receiver, privateKey)) {
      throw new TypeError("attempted to use private field on non-instance");
    }

    return receiver;
  }

  babelHelpers.classPrivateFieldLooseBase = _classPrivateFieldBase;

  function _classPrivateFieldGet(receiver, privateMap) {
    if (!privateMap.has(receiver)) {
      throw new TypeError("attempted to get private field on non-instance");
    }

    return privateMap.get(receiver).value;
  }

  babelHelpers.classPrivateFieldGet = _classPrivateFieldGet;

  function _classPrivateFieldSet(receiver, privateMap, value) {
    if (!privateMap.has(receiver)) {
      throw new TypeError("attempted to set private field on non-instance");
    }

    var descriptor = privateMap.get(receiver);

    if (!descriptor.writable) {
      throw new TypeError("attempted to set read only private field");
    }

    descriptor.value = value;
    return value;
  }

  babelHelpers.classPrivateFieldSet = _classPrivateFieldSet;
})(typeof global === "undefined" ? window : global);

/**
 * Copyright (c) 2014-present, Facebook, Inc.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */

!(function(global) {
	"use strict";

	var Op = Object.prototype;
	var hasOwn = Op.hasOwnProperty;
	var undefined; // More compressible than void 0.
	var $Symbol = typeof Symbol === "function" ? Symbol : {};
	var iteratorSymbol = $Symbol.iterator || "@@iterator";
	var asyncIteratorSymbol = $Symbol.asyncIterator || "@@asyncIterator";
	var toStringTagSymbol = $Symbol.toStringTag || "@@toStringTag";

	// Define the runtime globally (as expected by generated code) as either
	// module.exports (if we're in a module) or a new, empty object.
	var runtime = global.regeneratorRuntime = {};

	function wrap(innerFn, outerFn, self, tryLocsList) {
		// If outerFn provided and outerFn.prototype is a Generator, then outerFn.prototype instanceof Generator.
		var protoGenerator = outerFn && outerFn.prototype instanceof Generator ? outerFn : Generator;
		var generator = Object.create(protoGenerator.prototype);
		var context = new Context(tryLocsList || []);

		// The ._invoke method unifies the implementations of the .next,
		// .throw, and .return methods.
		generator._invoke = makeInvokeMethod(innerFn, self, context);

		return generator;
	}
	runtime.wrap = wrap;

	// Try/catch helper to minimize deoptimizations. Returns a completion
	// record like context.tryEntries[i].completion. This interface could
	// have been (and was previously) designed to take a closure to be
	// invoked without arguments, but in all the cases we care about we
	// already have an existing method we want to call, so there's no need
	// to create a new function object. We can even get away with assuming
	// the method takes exactly one argument, since that happens to be true
	// in every case, so we don't have to touch the arguments object. The
	// only additional allocation required is the completion record, which
	// has a stable shape and so hopefully should be cheap to allocate.
	function tryCatch(fn, obj, arg) {
		try {
			return { type: "normal", arg: fn.call(obj, arg) };
		} catch (err) {
			return { type: "throw", arg: err };
		}
	}

	var GenStateSuspendedStart = "suspendedStart";
	var GenStateSuspendedYield = "suspendedYield";
	var GenStateExecuting = "executing";
	var GenStateCompleted = "completed";

	// Returning this object from the innerFn has the same effect as
	// breaking out of the dispatch switch statement.
	var ContinueSentinel = {};

	// Dummy constructor functions that we use as the .constructor and
	// .constructor.prototype properties for functions that return Generator
	// objects. For full spec compliance, you may wish to configure your
	// minifier not to mangle the names of these two functions.
	function Generator() {}
	function GeneratorFunction() {}
	function GeneratorFunctionPrototype() {}

	// This is a polyfill for %IteratorPrototype% for environments that
	// don't natively support it.
	var IteratorPrototype = {};
	IteratorPrototype[iteratorSymbol] = function () {
		return this;
	};

	var getProto = Object.getPrototypeOf;
	var NativeIteratorPrototype = getProto && getProto(getProto(values([])));
	if (NativeIteratorPrototype &&
		NativeIteratorPrototype !== Op &&
		hasOwn.call(NativeIteratorPrototype, iteratorSymbol)) {
		// This environment has a native %IteratorPrototype%; use it instead
		// of the polyfill.
		IteratorPrototype = NativeIteratorPrototype;
	}

	var Gp = GeneratorFunctionPrototype.prototype =
		Generator.prototype = Object.create(IteratorPrototype);
	GeneratorFunction.prototype = Gp.constructor = GeneratorFunctionPrototype;
	GeneratorFunctionPrototype.constructor = GeneratorFunction;
	GeneratorFunctionPrototype[toStringTagSymbol] =
		GeneratorFunction.displayName = "GeneratorFunction";

	// Helper for defining the .next, .throw, and .return methods of the
	// Iterator interface in terms of a single ._invoke method.
	function defineIteratorMethods(prototype) {
		["next", "throw", "return"].forEach(function(method) {
			prototype[method] = function(arg) {
				return this._invoke(method, arg);
			};
		});
	}

	runtime.isGeneratorFunction = function(genFun) {
		var ctor = typeof genFun === "function" && genFun.constructor;
		return ctor
			? ctor === GeneratorFunction ||
			// For the native GeneratorFunction constructor, the best we can
			// do is to check its .name property.
			(ctor.displayName || ctor.name) === "GeneratorFunction"
			: false;
	};

	runtime.mark = function(genFun) {
		if (Object.setPrototypeOf) {
			Object.setPrototypeOf(genFun, GeneratorFunctionPrototype);
		} else {
			genFun.__proto__ = GeneratorFunctionPrototype;
			if (!(toStringTagSymbol in genFun)) {
				genFun[toStringTagSymbol] = "GeneratorFunction";
			}
		}
		genFun.prototype = Object.create(Gp);
		return genFun;
	};

	// Within the body of any async function, `await x` is transformed to
	// `yield regeneratorRuntime.awrap(x)`, so that the runtime can test
	// `hasOwn.call(value, "__await")` to determine if the yielded value is
	// meant to be awaited.
	runtime.awrap = function(arg) {
		return { __await: arg };
	};

	function AsyncIterator(generator) {
		function invoke(method, arg, resolve, reject) {
			var record = tryCatch(generator[method], generator, arg);
			if (record.type === "throw") {
				reject(record.arg);
			} else {
				var result = record.arg;
				var value = result.value;
				if (value &&
					typeof value === "object" &&
					hasOwn.call(value, "__await")) {
					return Promise.resolve(value.__await).then(function(value) {
						invoke("next", value, resolve, reject);
					}, function(err) {
						invoke("throw", err, resolve, reject);
					});
				}

				return Promise.resolve(value).then(function(unwrapped) {
					// When a yielded Promise is resolved, its final value becomes
					// the .value of the Promise<{value,done}> result for the
					// current iteration. If the Promise is rejected, however, the
					// result for this iteration will be rejected with the same
					// reason. Note that rejections of yielded Promises are not
					// thrown back into the generator function, as is the case
					// when an awaited Promise is rejected. This difference in
					// behavior between yield and await is important, because it
					// allows the consumer to decide what to do with the yielded
					// rejection (swallow it and continue, manually .throw it back
					// into the generator, abandon iteration, whatever). With
					// await, by contrast, there is no opportunity to examine the
					// rejection reason outside the generator function, so the
					// only option is to throw it from the await expression, and
					// let the generator function handle the exception.
					result.value = unwrapped;
					resolve(result);
				}, reject);
			}
		}

		var previousPromise;

		function enqueue(method, arg) {
			function callInvokeWithMethodAndArg() {
				return new Promise(function(resolve, reject) {
					invoke(method, arg, resolve, reject);
				});
			}

			return previousPromise =
				// If enqueue has been called before, then we want to wait until
				// all previous Promises have been resolved before calling invoke,
				// so that results are always delivered in the correct order. If
				// enqueue has not been called before, then it is important to
				// call invoke immediately, without waiting on a callback to fire,
				// so that the async generator function has the opportunity to do
				// any necessary setup in a predictable way. This predictability
				// is why the Promise constructor synchronously invokes its
				// executor callback, and why async functions synchronously
				// execute code before the first await. Since we implement simple
				// async functions in terms of async generators, it is especially
				// important to get this right, even though it requires care.
				previousPromise ? previousPromise.then(
					callInvokeWithMethodAndArg,
					// Avoid propagating failures to Promises returned by later
					// invocations of the iterator.
					callInvokeWithMethodAndArg
				) : callInvokeWithMethodAndArg();
		}

		// Define the unified helper method that is used to implement .next,
		// .throw, and .return (see defineIteratorMethods).
		this._invoke = enqueue;
	}

	defineIteratorMethods(AsyncIterator.prototype);
	AsyncIterator.prototype[asyncIteratorSymbol] = function () {
		return this;
	};
	runtime.AsyncIterator = AsyncIterator;

	// Note that simple async functions are implemented on top of
	// AsyncIterator objects; they just return a Promise for the value of
	// the final result produced by the iterator.
	runtime.async = function(innerFn, outerFn, self, tryLocsList) {
		var iter = new AsyncIterator(
			wrap(innerFn, outerFn, self, tryLocsList)
		);

		return runtime.isGeneratorFunction(outerFn)
			? iter // If outerFn is a generator, return the full iterator.
			: iter.next().then(function(result) {
				return result.done ? result.value : iter.next();
			});
	};

	function makeInvokeMethod(innerFn, self, context) {
		var state = GenStateSuspendedStart;

		return function invoke(method, arg) {
			if (state === GenStateExecuting) {
				throw new Error("Generator is already running");
			}

			if (state === GenStateCompleted) {
				if (method === "throw") {
					throw arg;
				}

				// Be forgiving, per 25.3.3.3.3 of the spec:
				// https://people.mozilla.org/~jorendorff/es6-draft.html#sec-generatorresume
				return doneResult();
			}

			context.method = method;
			context.arg = arg;

			while (true) {
				var delegate = context.delegate;
				if (delegate) {
					var delegateResult = maybeInvokeDelegate(delegate, context);
					if (delegateResult) {
						if (delegateResult === ContinueSentinel) continue;
						return delegateResult;
					}
				}

				if (context.method === "next") {
					// Setting context._sent for legacy support of Babel's
					// function.sent implementation.
					context.sent = context._sent = context.arg;

				} else if (context.method === "throw") {
					if (state === GenStateSuspendedStart) {
						state = GenStateCompleted;
						throw context.arg;
					}

					context.dispatchException(context.arg);

				} else if (context.method === "return") {
					context.abrupt("return", context.arg);
				}

				state = GenStateExecuting;

				var record = tryCatch(innerFn, self, context);
				if (record.type === "normal") {
					// If an exception is thrown from innerFn, we leave state ===
					// GenStateExecuting and loop back for another invocation.
					state = context.done
						? GenStateCompleted
						: GenStateSuspendedYield;

					if (record.arg === ContinueSentinel) {
						continue;
					}

					return {
						value: record.arg,
						done: context.done
					};

				} else if (record.type === "throw") {
					state = GenStateCompleted;
					// Dispatch the exception by looping back around to the
					// context.dispatchException(context.arg) call above.
					context.method = "throw";
					context.arg = record.arg;
				}
			}
		};
	}

	// Call delegate.iterator[context.method](context.arg) and handle the
	// result, either by returning a { value, done } result from the
	// delegate iterator, or by modifying context.method and context.arg,
	// setting context.delegate to null, and returning the ContinueSentinel.
	function maybeInvokeDelegate(delegate, context) {
		var method = delegate.iterator[context.method];
		if (method === undefined) {
			// A .throw or .return when the delegate iterator has no .throw
			// method always terminates the yield* loop.
			context.delegate = null;

			if (context.method === "throw") {
				if (delegate.iterator.return) {
					// If the delegate iterator has a return method, give it a
					// chance to clean up.
					context.method = "return";
					context.arg = undefined;
					maybeInvokeDelegate(delegate, context);

					if (context.method === "throw") {
						// If maybeInvokeDelegate(context) changed context.method from
						// "return" to "throw", let that override the TypeError below.
						return ContinueSentinel;
					}
				}

				context.method = "throw";
				context.arg = new TypeError(
					"The iterator does not provide a 'throw' method");
			}

			return ContinueSentinel;
		}

		var record = tryCatch(method, delegate.iterator, context.arg);

		if (record.type === "throw") {
			context.method = "throw";
			context.arg = record.arg;
			context.delegate = null;
			return ContinueSentinel;
		}

		var info = record.arg;

		if (! info) {
			context.method = "throw";
			context.arg = new TypeError("iterator result is not an object");
			context.delegate = null;
			return ContinueSentinel;
		}

		if (info.done) {
			// Assign the result of the finished delegate to the temporary
			// variable specified by delegate.resultName (see delegateYield).
			context[delegate.resultName] = info.value;

			// Resume execution at the desired location (see delegateYield).
			context.next = delegate.nextLoc;

			// If context.method was "throw" but the delegate handled the
			// exception, let the outer generator proceed normally. If
			// context.method was "next", forget context.arg since it has been
			// "consumed" by the delegate iterator. If context.method was
			// "return", allow the original .return call to continue in the
			// outer generator.
			if (context.method !== "return") {
				context.method = "next";
				context.arg = undefined;
			}

		} else {
			// Re-yield the result returned by the delegate method.
			return info;
		}

		// The delegate iterator is finished, so forget it and continue with
		// the outer generator.
		context.delegate = null;
		return ContinueSentinel;
	}

	// Define Generator.prototype.{next,throw,return} in terms of the
	// unified ._invoke helper method.
	defineIteratorMethods(Gp);

	Gp[toStringTagSymbol] = "Generator";

	// A Generator should always return itself as the iterator object when the
	// @@iterator function is called on it. Some browsers' implementations of the
	// iterator prototype chain incorrectly implement this, causing the Generator
	// object to not be returned from this call. This ensures that doesn't happen.
	// See https://github.com/facebook/regenerator/issues/274 for more details.
	Gp[iteratorSymbol] = function() {
		return this;
	};

	Gp.toString = function() {
		return "[object Generator]";
	};

	function pushTryEntry(locs) {
		var entry = { tryLoc: locs[0] };

		if (1 in locs) {
			entry.catchLoc = locs[1];
		}

		if (2 in locs) {
			entry.finallyLoc = locs[2];
			entry.afterLoc = locs[3];
		}

		this.tryEntries.push(entry);
	}

	function resetTryEntry(entry) {
		var record = entry.completion || {};
		record.type = "normal";
		delete record.arg;
		entry.completion = record;
	}

	function Context(tryLocsList) {
		// The root entry object (effectively a try statement without a catch
		// or a finally block) gives us a place to store values thrown from
		// locations where there is no enclosing try statement.
		this.tryEntries = [{ tryLoc: "root" }];
		tryLocsList.forEach(pushTryEntry, this);
		this.reset(true);
	}

	runtime.keys = function(object) {
		var keys = [];
		for (var key in object) {
			keys.push(key);
		}
		keys.reverse();

		// Rather than returning an object with a next method, we keep
		// things simple and return the next function itself.
		return function next() {
			while (keys.length) {
				var key = keys.pop();
				if (key in object) {
					next.value = key;
					next.done = false;
					return next;
				}
			}

			// To avoid creating an additional object, we just hang the .value
			// and .done properties off the next function object itself. This
			// also ensures that the minifier will not anonymize the function.
			next.done = true;
			return next;
		};
	};

	function values(iterable) {
		if (iterable) {
			var iteratorMethod = iterable[iteratorSymbol];
			if (iteratorMethod) {
				return iteratorMethod.call(iterable);
			}

			if (typeof iterable.next === "function") {
				return iterable;
			}

			if (!isNaN(iterable.length)) {
				var i = -1, next = function next() {
					while (++i < iterable.length) {
						if (hasOwn.call(iterable, i)) {
							next.value = iterable[i];
							next.done = false;
							return next;
						}
					}

					next.value = undefined;
					next.done = true;

					return next;
				};

				return next.next = next;
			}
		}

		// Return an iterator with no values.
		return { next: doneResult };
	}
	runtime.values = values;

	function doneResult() {
		return { value: undefined, done: true };
	}

	Context.prototype = {
		constructor: Context,

		reset: function(skipTempReset) {
			this.prev = 0;
			this.next = 0;
			// Resetting context._sent for legacy support of Babel's
			// function.sent implementation.
			this.sent = this._sent = undefined;
			this.done = false;
			this.delegate = null;

			this.method = "next";
			this.arg = undefined;

			this.tryEntries.forEach(resetTryEntry);

			if (!skipTempReset) {
				for (var name in this) {
					// Not sure about the optimal order of these conditions:
					if (name.charAt(0) === "t" &&
						hasOwn.call(this, name) &&
						!isNaN(+name.slice(1))) {
						this[name] = undefined;
					}
				}
			}
		},

		stop: function() {
			this.done = true;

			var rootEntry = this.tryEntries[0];
			var rootRecord = rootEntry.completion;
			if (rootRecord.type === "throw") {
				throw rootRecord.arg;
			}

			return this.rval;
		},

		dispatchException: function(exception) {
			if (this.done) {
				throw exception;
			}

			var context = this;
			function handle(loc, caught) {
				record.type = "throw";
				record.arg = exception;
				context.next = loc;

				if (caught) {
					// If the dispatched exception was caught by a catch block,
					// then let that catch block handle the exception normally.
					context.method = "next";
					context.arg = undefined;
				}

				return !! caught;
			}

			for (var i = this.tryEntries.length - 1; i >= 0; --i) {
				var entry = this.tryEntries[i];
				var record = entry.completion;

				if (entry.tryLoc === "root") {
					// Exception thrown outside of any try block that could handle
					// it, so set the completion value of the entire function to
					// throw the exception.
					return handle("end");
				}

				if (entry.tryLoc <= this.prev) {
					var hasCatch = hasOwn.call(entry, "catchLoc");
					var hasFinally = hasOwn.call(entry, "finallyLoc");

					if (hasCatch && hasFinally) {
						if (this.prev < entry.catchLoc) {
							return handle(entry.catchLoc, true);
						} else if (this.prev < entry.finallyLoc) {
							return handle(entry.finallyLoc);
						}

					} else if (hasCatch) {
						if (this.prev < entry.catchLoc) {
							return handle(entry.catchLoc, true);
						}

					} else if (hasFinally) {
						if (this.prev < entry.finallyLoc) {
							return handle(entry.finallyLoc);
						}

					} else {
						throw new Error("try statement without catch or finally");
					}
				}
			}
		},

		abrupt: function(type, arg) {
			for (var i = this.tryEntries.length - 1; i >= 0; --i) {
				var entry = this.tryEntries[i];
				if (entry.tryLoc <= this.prev &&
					hasOwn.call(entry, "finallyLoc") &&
					this.prev < entry.finallyLoc) {
					var finallyEntry = entry;
					break;
				}
			}

			if (finallyEntry &&
				(type === "break" ||
					type === "continue") &&
				finallyEntry.tryLoc <= arg &&
				arg <= finallyEntry.finallyLoc) {
				// Ignore the finally entry if control is not jumping to a
				// location outside the try/catch block.
				finallyEntry = null;
			}

			var record = finallyEntry ? finallyEntry.completion : {};
			record.type = type;
			record.arg = arg;

			if (finallyEntry) {
				this.method = "next";
				this.next = finallyEntry.finallyLoc;
				return ContinueSentinel;
			}

			return this.complete(record);
		},

		complete: function(record, afterLoc) {
			if (record.type === "throw") {
				throw record.arg;
			}

			if (record.type === "break" ||
				record.type === "continue") {
				this.next = record.arg;
			} else if (record.type === "return") {
				this.rval = this.arg = record.arg;
				this.method = "return";
				this.next = "end";
			} else if (record.type === "normal" && afterLoc) {
				this.next = afterLoc;
			}

			return ContinueSentinel;
		},

		finish: function(finallyLoc) {
			for (var i = this.tryEntries.length - 1; i >= 0; --i) {
				var entry = this.tryEntries[i];
				if (entry.finallyLoc === finallyLoc) {
					this.complete(entry.completion, entry.afterLoc);
					resetTryEntry(entry);
					return ContinueSentinel;
				}
			}
		},

		"catch": function(tryLoc) {
			for (var i = this.tryEntries.length - 1; i >= 0; --i) {
				var entry = this.tryEntries[i];
				if (entry.tryLoc === tryLoc) {
					var record = entry.completion;
					if (record.type === "throw") {
						var thrown = record.arg;
						resetTryEntry(entry);
					}
					return thrown;
				}
			}

			// The context.catch method must only be called with a location
			// argument that corresponds to a known catch block.
			throw new Error("illegal catch attempt");
		},

		delegateYield: function(iterable, resultName, nextLoc) {
			this.delegate = {
				iterator: values(iterable),
				resultName: resultName,
				nextLoc: nextLoc
			};

			if (this.method === "next") {
				// Deliberately forget the last sent value so that we don't
				// accidentally pass it on to the delegate.
				this.arg = undefined;
			}

			return ContinueSentinel;
		}
	};
})(
	// In sloppy mode, unbound `this` refers to the global object, fallback to
	// Function constructor if we're in global strict mode. That is sadly a form
	// of indirect eval which violates Content Security Policy.
	(function() { return this })() || Function("return this")()
);

(function (exports) {
	'use strict';

	var commonjsGlobal = typeof window !== 'undefined' ? window : typeof global !== 'undefined' ? global : typeof self !== 'undefined' ? self : {};
	function createCommonjsModule(fn, module) {
	  return module = {
	    exports: {}
	  }, fn(module, module.exports), module.exports;
	}

	var check = function (it) {
	  return it && it.Math == Math && it;
	}; // https://github.com/zloirock/core-js/issues/86#issuecomment-115759028


	var global_1 = // eslint-disable-next-line es/no-global-this -- safe
	check(typeof globalThis == 'object' && globalThis) || check(typeof window == 'object' && window) || // eslint-disable-next-line no-restricted-globals -- safe
	check(typeof self == 'object' && self) || check(typeof commonjsGlobal == 'object' && commonjsGlobal) || // eslint-disable-next-line no-new-func -- fallback
	function () {
	  return this;
	}() || Function('return this')();

	var fails = function (exec) {
	  try {
	    return !!exec();
	  } catch (error) {
	    return true;
	  }
	};

	// Detect IE8's incomplete defineProperty implementation


	var descriptors = !fails(function () {
	  // eslint-disable-next-line es/no-object-defineproperty -- required for testing
	  return Object.defineProperty({}, 1, {
	    get: function () {
	      return 7;
	    }
	  })[1] != 7;
	});

	var call = Function.prototype.call;
	var functionCall = call.bind ? call.bind(call) : function () {
	  return call.apply(call, arguments);
	};

	var $propertyIsEnumerable = {}.propertyIsEnumerable; // eslint-disable-next-line es/no-object-getownpropertydescriptor -- safe

	var getOwnPropertyDescriptor = Object.getOwnPropertyDescriptor; // Nashorn ~ JDK8 bug

	var NASHORN_BUG = getOwnPropertyDescriptor && !$propertyIsEnumerable.call({
	  1: 2
	}, 1); // `Object.prototype.propertyIsEnumerable` method implementation
	// https://tc39.es/ecma262/#sec-object.prototype.propertyisenumerable

	var f = NASHORN_BUG ? function propertyIsEnumerable(V) {
	  var descriptor = getOwnPropertyDescriptor(this, V);
	  return !!descriptor && descriptor.enumerable;
	} : $propertyIsEnumerable;

	var objectPropertyIsEnumerable = {
		f: f
	};

	var createPropertyDescriptor = function (bitmap, value) {
	  return {
	    enumerable: !(bitmap & 1),
	    configurable: !(bitmap & 2),
	    writable: !(bitmap & 4),
	    value: value
	  };
	};

	var FunctionPrototype = Function.prototype;
	var bind = FunctionPrototype.bind;
	var call$1 = FunctionPrototype.call;
	var callBind = bind && bind.bind(call$1);
	var functionUncurryThis = bind ? function (fn) {
	  return fn && callBind(call$1, fn);
	} : function (fn) {
	  return fn && function () {
	    return call$1.apply(fn, arguments);
	  };
	};

	var toString = functionUncurryThis({}.toString);
	var stringSlice = functionUncurryThis(''.slice);

	var classofRaw = function (it) {
	  return stringSlice(toString(it), 8, -1);
	};

	var Object$1 = global_1.Object;
	var split = functionUncurryThis(''.split); // fallback for non-array-like ES3 and non-enumerable old V8 strings

	var indexedObject = fails(function () {
	  // throws an error in rhino, see https://github.com/mozilla/rhino/issues/346
	  // eslint-disable-next-line no-prototype-builtins -- safe
	  return !Object$1('z').propertyIsEnumerable(0);
	}) ? function (it) {
	  return classofRaw(it) == 'String' ? split(it, '') : Object$1(it);
	} : Object$1;

	var TypeError$1 = global_1.TypeError; // `RequireObjectCoercible` abstract operation
	// https://tc39.es/ecma262/#sec-requireobjectcoercible

	var requireObjectCoercible = function (it) {
	  if (it == undefined) throw TypeError$1("Can't call method on " + it);
	  return it;
	};

	// toObject with fallback for non-array-like ES3 strings




	var toIndexedObject = function (it) {
	  return indexedObject(requireObjectCoercible(it));
	};

	// `IsCallable` abstract operation
	// https://tc39.es/ecma262/#sec-iscallable
	var isCallable = function (argument) {
	  return typeof argument == 'function';
	};

	var isObject = function (it) {
	  return typeof it == 'object' ? it !== null : isCallable(it);
	};

	var aFunction = function (argument) {
	  return isCallable(argument) ? argument : undefined;
	};

	var getBuiltIn = function (namespace, method) {
	  return arguments.length < 2 ? aFunction(global_1[namespace]) : global_1[namespace] && global_1[namespace][method];
	};

	var objectIsPrototypeOf = functionUncurryThis({}.isPrototypeOf);

	var engineUserAgent = getBuiltIn('navigator', 'userAgent') || '';

	var process = global_1.process;
	var Deno = global_1.Deno;
	var versions = process && process.versions || Deno && Deno.version;
	var v8 = versions && versions.v8;
	var match, version;

	if (v8) {
	  match = v8.split('.'); // in old Chrome, versions of V8 isn't V8 = Chrome / 10
	  // but their correct versions are not interesting for us

	  version = match[0] > 0 && match[0] < 4 ? 1 : +(match[0] + match[1]);
	} // BrowserFS NodeJS `process` polyfill incorrectly set `.v8` to `0.0`
	// so check `userAgent` even if `.v8` exists, but 0


	if (!version && engineUserAgent) {
	  match = engineUserAgent.match(/Edge\/(\d+)/);

	  if (!match || match[1] >= 74) {
	    match = engineUserAgent.match(/Chrome\/(\d+)/);
	    if (match) version = +match[1];
	  }
	}

	var engineV8Version = version;

	/* eslint-disable es/no-symbol -- required for testing */


	 // eslint-disable-next-line es/no-object-getownpropertysymbols -- required for testing


	var nativeSymbol = !!Object.getOwnPropertySymbols && !fails(function () {
	  var symbol = Symbol(); // Chrome 38 Symbol has incorrect toString conversion
	  // `get-own-property-symbols` polyfill symbols converted to object are not Symbol instances

	  return !String(symbol) || !(Object(symbol) instanceof Symbol) || // Chrome 38-40 symbols are not inherited from DOM collections prototypes to instances
	  !Symbol.sham && engineV8Version && engineV8Version < 41;
	});

	/* eslint-disable es/no-symbol -- required for testing */


	var useSymbolAsUid = nativeSymbol && !Symbol.sham && typeof Symbol.iterator == 'symbol';

	var Object$2 = global_1.Object;
	var isSymbol = useSymbolAsUid ? function (it) {
	  return typeof it == 'symbol';
	} : function (it) {
	  var $Symbol = getBuiltIn('Symbol');
	  return isCallable($Symbol) && objectIsPrototypeOf($Symbol.prototype, Object$2(it));
	};

	var String$1 = global_1.String;

	var tryToString = function (argument) {
	  try {
	    return String$1(argument);
	  } catch (error) {
	    return 'Object';
	  }
	};

	var TypeError$2 = global_1.TypeError; // `Assert: IsCallable(argument) is true`

	var aCallable = function (argument) {
	  if (isCallable(argument)) return argument;
	  throw TypeError$2(tryToString(argument) + ' is not a function');
	};

	// `GetMethod` abstract operation
	// https://tc39.es/ecma262/#sec-getmethod


	var getMethod = function (V, P) {
	  var func = V[P];
	  return func == null ? undefined : aCallable(func);
	};

	var TypeError$3 = global_1.TypeError; // `OrdinaryToPrimitive` abstract operation
	// https://tc39.es/ecma262/#sec-ordinarytoprimitive

	var ordinaryToPrimitive = function (input, pref) {
	  var fn, val;
	  if (pref === 'string' && isCallable(fn = input.toString) && !isObject(val = functionCall(fn, input))) return val;
	  if (isCallable(fn = input.valueOf) && !isObject(val = functionCall(fn, input))) return val;
	  if (pref !== 'string' && isCallable(fn = input.toString) && !isObject(val = functionCall(fn, input))) return val;
	  throw TypeError$3("Can't convert object to primitive value");
	};

	var isPure = false;

	// eslint-disable-next-line es/no-object-defineproperty -- safe


	var defineProperty = Object.defineProperty;

	var setGlobal = function (key, value) {
	  try {
	    defineProperty(global_1, key, {
	      value: value,
	      configurable: true,
	      writable: true
	    });
	  } catch (error) {
	    global_1[key] = value;
	  }

	  return value;
	};

	var SHARED = '__core-js_shared__';
	var store = global_1[SHARED] || setGlobal(SHARED, {});
	var sharedStore = store;

	var shared = createCommonjsModule(function (module) {
	(module.exports = function (key, value) {
	  return sharedStore[key] || (sharedStore[key] = value !== undefined ? value : {});
	})('versions', []).push({
	  version: '3.19.2',
	  mode: 'global',
	  copyright: '(c) 2021 Denis Pushkarev (zloirock.ru)'
	});
	});

	var Object$3 = global_1.Object; // `ToObject` abstract operation
	// https://tc39.es/ecma262/#sec-toobject

	var toObject = function (argument) {
	  return Object$3(requireObjectCoercible(argument));
	};

	var hasOwnProperty = functionUncurryThis({}.hasOwnProperty); // `HasOwnProperty` abstract operation
	// https://tc39.es/ecma262/#sec-hasownproperty

	var hasOwnProperty_1 = Object.hasOwn || function hasOwn(it, key) {
	  return hasOwnProperty(toObject(it), key);
	};

	var id = 0;
	var postfix = Math.random();
	var toString$1 = functionUncurryThis(1.0.toString);

	var uid = function (key) {
	  return 'Symbol(' + (key === undefined ? '' : key) + ')_' + toString$1(++id + postfix, 36);
	};

	var WellKnownSymbolsStore = shared('wks');
	var Symbol$1 = global_1.Symbol;
	var symbolFor = Symbol$1 && Symbol$1['for'];
	var createWellKnownSymbol = useSymbolAsUid ? Symbol$1 : Symbol$1 && Symbol$1.withoutSetter || uid;

	var wellKnownSymbol = function (name) {
	  if (!hasOwnProperty_1(WellKnownSymbolsStore, name) || !(nativeSymbol || typeof WellKnownSymbolsStore[name] == 'string')) {
	    var description = 'Symbol.' + name;

	    if (nativeSymbol && hasOwnProperty_1(Symbol$1, name)) {
	      WellKnownSymbolsStore[name] = Symbol$1[name];
	    } else if (useSymbolAsUid && symbolFor) {
	      WellKnownSymbolsStore[name] = symbolFor(description);
	    } else {
	      WellKnownSymbolsStore[name] = createWellKnownSymbol(description);
	    }
	  }

	  return WellKnownSymbolsStore[name];
	};

	var TypeError$4 = global_1.TypeError;
	var TO_PRIMITIVE = wellKnownSymbol('toPrimitive'); // `ToPrimitive` abstract operation
	// https://tc39.es/ecma262/#sec-toprimitive

	var toPrimitive = function (input, pref) {
	  if (!isObject(input) || isSymbol(input)) return input;
	  var exoticToPrim = getMethod(input, TO_PRIMITIVE);
	  var result;

	  if (exoticToPrim) {
	    if (pref === undefined) pref = 'default';
	    result = functionCall(exoticToPrim, input, pref);
	    if (!isObject(result) || isSymbol(result)) return result;
	    throw TypeError$4("Can't convert object to primitive value");
	  }

	  if (pref === undefined) pref = 'number';
	  return ordinaryToPrimitive(input, pref);
	};

	// `ToPropertyKey` abstract operation
	// https://tc39.es/ecma262/#sec-topropertykey


	var toPropertyKey = function (argument) {
	  var key = toPrimitive(argument, 'string');
	  return isSymbol(key) ? key : key + '';
	};

	var document$1 = global_1.document; // typeof document.createElement is 'object' in old IE

	var EXISTS = isObject(document$1) && isObject(document$1.createElement);

	var documentCreateElement = function (it) {
	  return EXISTS ? document$1.createElement(it) : {};
	};

	// Thank's IE8 for his funny defineProperty


	var ie8DomDefine = !descriptors && !fails(function () {
	  // eslint-disable-next-line es/no-object-defineproperty -- requied for testing
	  return Object.defineProperty(documentCreateElement('div'), 'a', {
	    get: function () {
	      return 7;
	    }
	  }).a != 7;
	});

	// eslint-disable-next-line es/no-object-getownpropertydescriptor -- safe


	var $getOwnPropertyDescriptor = Object.getOwnPropertyDescriptor; // `Object.getOwnPropertyDescriptor` method
	// https://tc39.es/ecma262/#sec-object.getownpropertydescriptor

	var f$1 = descriptors ? $getOwnPropertyDescriptor : function getOwnPropertyDescriptor(O, P) {
	  O = toIndexedObject(O);
	  P = toPropertyKey(P);
	  if (ie8DomDefine) try {
	    return $getOwnPropertyDescriptor(O, P);
	  } catch (error) {
	    /* empty */
	  }
	  if (hasOwnProperty_1(O, P)) return createPropertyDescriptor(!functionCall(objectPropertyIsEnumerable.f, O, P), O[P]);
	};

	var objectGetOwnPropertyDescriptor = {
		f: f$1
	};

	var String$2 = global_1.String;
	var TypeError$5 = global_1.TypeError; // `Assert: Type(argument) is Object`

	var anObject = function (argument) {
	  if (isObject(argument)) return argument;
	  throw TypeError$5(String$2(argument) + ' is not an object');
	};

	var TypeError$6 = global_1.TypeError; // eslint-disable-next-line es/no-object-defineproperty -- safe

	var $defineProperty = Object.defineProperty; // `Object.defineProperty` method
	// https://tc39.es/ecma262/#sec-object.defineproperty

	var f$2 = descriptors ? $defineProperty : function defineProperty(O, P, Attributes) {
	  anObject(O);
	  P = toPropertyKey(P);
	  anObject(Attributes);
	  if (ie8DomDefine) try {
	    return $defineProperty(O, P, Attributes);
	  } catch (error) {
	    /* empty */
	  }
	  if ('get' in Attributes || 'set' in Attributes) throw TypeError$6('Accessors not supported');
	  if ('value' in Attributes) O[P] = Attributes.value;
	  return O;
	};

	var objectDefineProperty = {
		f: f$2
	};

	var createNonEnumerableProperty = descriptors ? function (object, key, value) {
	  return objectDefineProperty.f(object, key, createPropertyDescriptor(1, value));
	} : function (object, key, value) {
	  object[key] = value;
	  return object;
	};

	var functionToString = functionUncurryThis(Function.toString); // this helper broken in `core-js@3.4.1-3.4.4`, so we can't use `shared` helper

	if (!isCallable(sharedStore.inspectSource)) {
	  sharedStore.inspectSource = function (it) {
	    return functionToString(it);
	  };
	}

	var inspectSource = sharedStore.inspectSource;

	var WeakMap = global_1.WeakMap;
	var nativeWeakMap = isCallable(WeakMap) && /native code/.test(inspectSource(WeakMap));

	var keys = shared('keys');

	var sharedKey = function (key) {
	  return keys[key] || (keys[key] = uid(key));
	};

	var hiddenKeys = {};

	var OBJECT_ALREADY_INITIALIZED = 'Object already initialized';
	var TypeError$7 = global_1.TypeError;
	var WeakMap$1 = global_1.WeakMap;
	var set, get, has;

	var enforce = function (it) {
	  return has(it) ? get(it) : set(it, {});
	};

	var getterFor = function (TYPE) {
	  return function (it) {
	    var state;

	    if (!isObject(it) || (state = get(it)).type !== TYPE) {
	      throw TypeError$7('Incompatible receiver, ' + TYPE + ' required');
	    }

	    return state;
	  };
	};

	if (nativeWeakMap || sharedStore.state) {
	  var store$1 = sharedStore.state || (sharedStore.state = new WeakMap$1());
	  var wmget = functionUncurryThis(store$1.get);
	  var wmhas = functionUncurryThis(store$1.has);
	  var wmset = functionUncurryThis(store$1.set);

	  set = function (it, metadata) {
	    if (wmhas(store$1, it)) throw new TypeError$7(OBJECT_ALREADY_INITIALIZED);
	    metadata.facade = it;
	    wmset(store$1, it, metadata);
	    return metadata;
	  };

	  get = function (it) {
	    return wmget(store$1, it) || {};
	  };

	  has = function (it) {
	    return wmhas(store$1, it);
	  };
	} else {
	  var STATE = sharedKey('state');
	  hiddenKeys[STATE] = true;

	  set = function (it, metadata) {
	    if (hasOwnProperty_1(it, STATE)) throw new TypeError$7(OBJECT_ALREADY_INITIALIZED);
	    metadata.facade = it;
	    createNonEnumerableProperty(it, STATE, metadata);
	    return metadata;
	  };

	  get = function (it) {
	    return hasOwnProperty_1(it, STATE) ? it[STATE] : {};
	  };

	  has = function (it) {
	    return hasOwnProperty_1(it, STATE);
	  };
	}

	var internalState = {
	  set: set,
	  get: get,
	  has: has,
	  enforce: enforce,
	  getterFor: getterFor
	};

	var FunctionPrototype$1 = Function.prototype; // eslint-disable-next-line es/no-object-getownpropertydescriptor -- safe

	var getDescriptor = descriptors && Object.getOwnPropertyDescriptor;
	var EXISTS$1 = hasOwnProperty_1(FunctionPrototype$1, 'name'); // additional protection from minified / mangled / dropped function names

	var PROPER = EXISTS$1 && function something() {
	  /* empty */
	}.name === 'something';

	var CONFIGURABLE = EXISTS$1 && (!descriptors || descriptors && getDescriptor(FunctionPrototype$1, 'name').configurable);
	var functionName = {
	  EXISTS: EXISTS$1,
	  PROPER: PROPER,
	  CONFIGURABLE: CONFIGURABLE
	};

	var redefine = createCommonjsModule(function (module) {
	var CONFIGURABLE_FUNCTION_NAME = functionName.CONFIGURABLE;

	var getInternalState = internalState.get;
	var enforceInternalState = internalState.enforce;
	var TEMPLATE = String(String).split('String');
	(module.exports = function (O, key, value, options) {
	  var unsafe = options ? !!options.unsafe : false;
	  var simple = options ? !!options.enumerable : false;
	  var noTargetGet = options ? !!options.noTargetGet : false;
	  var name = options && options.name !== undefined ? options.name : key;
	  var state;

	  if (isCallable(value)) {
	    if (String(name).slice(0, 7) === 'Symbol(') {
	      name = '[' + String(name).replace(/^Symbol\(([^)]*)\)/, '$1') + ']';
	    }

	    if (!hasOwnProperty_1(value, 'name') || CONFIGURABLE_FUNCTION_NAME && value.name !== name) {
	      createNonEnumerableProperty(value, 'name', name);
	    }

	    state = enforceInternalState(value);

	    if (!state.source) {
	      state.source = TEMPLATE.join(typeof name == 'string' ? name : '');
	    }
	  }

	  if (O === global_1) {
	    if (simple) O[key] = value;else setGlobal(key, value);
	    return;
	  } else if (!unsafe) {
	    delete O[key];
	  } else if (!noTargetGet && O[key]) {
	    simple = true;
	  }

	  if (simple) O[key] = value;else createNonEnumerableProperty(O, key, value); // add fake Function#toString for correct work wrapped methods / constructors with methods like LoDash isNative
	})(Function.prototype, 'toString', function toString() {
	  return isCallable(this) && getInternalState(this).source || inspectSource(this);
	});
	});

	var ceil = Math.ceil;
	var floor = Math.floor; // `ToIntegerOrInfinity` abstract operation
	// https://tc39.es/ecma262/#sec-tointegerorinfinity

	var toIntegerOrInfinity = function (argument) {
	  var number = +argument; // eslint-disable-next-line no-self-compare -- safe

	  return number !== number || number === 0 ? 0 : (number > 0 ? floor : ceil)(number);
	};

	var max = Math.max;
	var min = Math.min; // Helper for a popular repeating case of the spec:
	// Let integer be ? ToInteger(index).
	// If integer < 0, let result be max((length + integer), 0); else let result be min(integer, length).

	var toAbsoluteIndex = function (index, length) {
	  var integer = toIntegerOrInfinity(index);
	  return integer < 0 ? max(integer + length, 0) : min(integer, length);
	};

	var min$1 = Math.min; // `ToLength` abstract operation
	// https://tc39.es/ecma262/#sec-tolength

	var toLength = function (argument) {
	  return argument > 0 ? min$1(toIntegerOrInfinity(argument), 0x1FFFFFFFFFFFFF) : 0; // 2 ** 53 - 1 == 9007199254740991
	};

	// `LengthOfArrayLike` abstract operation
	// https://tc39.es/ecma262/#sec-lengthofarraylike


	var lengthOfArrayLike = function (obj) {
	  return toLength(obj.length);
	};

	// `Array.prototype.{ indexOf, includes }` methods implementation


	var createMethod = function (IS_INCLUDES) {
	  return function ($this, el, fromIndex) {
	    var O = toIndexedObject($this);
	    var length = lengthOfArrayLike(O);
	    var index = toAbsoluteIndex(fromIndex, length);
	    var value; // Array#includes uses SameValueZero equality algorithm
	    // eslint-disable-next-line no-self-compare -- NaN check

	    if (IS_INCLUDES && el != el) while (length > index) {
	      value = O[index++]; // eslint-disable-next-line no-self-compare -- NaN check

	      if (value != value) return true; // Array#indexOf ignores holes, Array#includes - not
	    } else for (; length > index; index++) {
	      if ((IS_INCLUDES || index in O) && O[index] === el) return IS_INCLUDES || index || 0;
	    }
	    return !IS_INCLUDES && -1;
	  };
	};

	var arrayIncludes = {
	  // `Array.prototype.includes` method
	  // https://tc39.es/ecma262/#sec-array.prototype.includes
	  includes: createMethod(true),
	  // `Array.prototype.indexOf` method
	  // https://tc39.es/ecma262/#sec-array.prototype.indexof
	  indexOf: createMethod(false)
	};

	var indexOf = arrayIncludes.indexOf;



	var push = functionUncurryThis([].push);

	var objectKeysInternal = function (object, names) {
	  var O = toIndexedObject(object);
	  var i = 0;
	  var result = [];
	  var key;

	  for (key in O) !hasOwnProperty_1(hiddenKeys, key) && hasOwnProperty_1(O, key) && push(result, key); // Don't enum bug & hidden keys


	  while (names.length > i) if (hasOwnProperty_1(O, key = names[i++])) {
	    ~indexOf(result, key) || push(result, key);
	  }

	  return result;
	};

	// IE8- don't enum bug keys
	var enumBugKeys = ['constructor', 'hasOwnProperty', 'isPrototypeOf', 'propertyIsEnumerable', 'toLocaleString', 'toString', 'valueOf'];

	var hiddenKeys$1 = enumBugKeys.concat('length', 'prototype'); // `Object.getOwnPropertyNames` method
	// https://tc39.es/ecma262/#sec-object.getownpropertynames
	// eslint-disable-next-line es/no-object-getownpropertynames -- safe

	var f$3 = Object.getOwnPropertyNames || function getOwnPropertyNames(O) {
	  return objectKeysInternal(O, hiddenKeys$1);
	};

	var objectGetOwnPropertyNames = {
		f: f$3
	};

	// eslint-disable-next-line es/no-object-getownpropertysymbols -- safe
	var f$4 = Object.getOwnPropertySymbols;

	var objectGetOwnPropertySymbols = {
		f: f$4
	};

	var concat = functionUncurryThis([].concat); // all object keys, includes non-enumerable and symbols

	var ownKeys = getBuiltIn('Reflect', 'ownKeys') || function ownKeys(it) {
	  var keys = objectGetOwnPropertyNames.f(anObject(it));
	  var getOwnPropertySymbols = objectGetOwnPropertySymbols.f;
	  return getOwnPropertySymbols ? concat(keys, getOwnPropertySymbols(it)) : keys;
	};

	var copyConstructorProperties = function (target, source) {
	  var keys = ownKeys(source);
	  var defineProperty = objectDefineProperty.f;
	  var getOwnPropertyDescriptor = objectGetOwnPropertyDescriptor.f;

	  for (var i = 0; i < keys.length; i++) {
	    var key = keys[i];
	    if (!hasOwnProperty_1(target, key)) defineProperty(target, key, getOwnPropertyDescriptor(source, key));
	  }
	};

	var replacement = /#|\.prototype\./;

	var isForced = function (feature, detection) {
	  var value = data[normalize(feature)];
	  return value == POLYFILL ? true : value == NATIVE ? false : isCallable(detection) ? fails(detection) : !!detection;
	};

	var normalize = isForced.normalize = function (string) {
	  return String(string).replace(replacement, '.').toLowerCase();
	};

	var data = isForced.data = {};
	var NATIVE = isForced.NATIVE = 'N';
	var POLYFILL = isForced.POLYFILL = 'P';
	var isForced_1 = isForced;

	var getOwnPropertyDescriptor$1 = objectGetOwnPropertyDescriptor.f;










	/*
	  options.target      - name of the target object
	  options.global      - target is the global object
	  options.stat        - export as static methods of target
	  options.proto       - export as prototype methods of target
	  options.real        - real prototype method for the `pure` version
	  options.forced      - export even if the native feature is available
	  options.bind        - bind methods to the target, required for the `pure` version
	  options.wrap        - wrap constructors to preventing global pollution, required for the `pure` version
	  options.unsafe      - use the simple assignment of property instead of delete + defineProperty
	  options.sham        - add a flag to not completely full polyfills
	  options.enumerable  - export as enumerable property
	  options.noTargetGet - prevent calling a getter on target
	  options.name        - the .name of the function if it does not match the key
	*/


	var _export = function (options, source) {
	  var TARGET = options.target;
	  var GLOBAL = options.global;
	  var STATIC = options.stat;
	  var FORCED, target, key, targetProperty, sourceProperty, descriptor;

	  if (GLOBAL) {
	    target = global_1;
	  } else if (STATIC) {
	    target = global_1[TARGET] || setGlobal(TARGET, {});
	  } else {
	    target = (global_1[TARGET] || {}).prototype;
	  }

	  if (target) for (key in source) {
	    sourceProperty = source[key];

	    if (options.noTargetGet) {
	      descriptor = getOwnPropertyDescriptor$1(target, key);
	      targetProperty = descriptor && descriptor.value;
	    } else targetProperty = target[key];

	    FORCED = isForced_1(GLOBAL ? key : TARGET + (STATIC ? '.' : '#') + key, options.forced); // contained in target

	    if (!FORCED && targetProperty !== undefined) {
	      if (typeof sourceProperty == typeof targetProperty) continue;
	      copyConstructorProperties(sourceProperty, targetProperty);
	    } // add a flag to not completely full polyfills


	    if (options.sham || targetProperty && targetProperty.sham) {
	      createNonEnumerableProperty(sourceProperty, 'sham', true);
	    } // extend global


	    redefine(target, key, sourceProperty, options);
	  }
	};

	var correctPrototypeGetter = !fails(function () {
	  function F() {
	    /* empty */
	  }

	  F.prototype.constructor = null; // eslint-disable-next-line es/no-object-getprototypeof -- required for testing

	  return Object.getPrototypeOf(new F()) !== F.prototype;
	});

	var IE_PROTO = sharedKey('IE_PROTO');
	var Object$4 = global_1.Object;
	var ObjectPrototype = Object$4.prototype; // `Object.getPrototypeOf` method
	// https://tc39.es/ecma262/#sec-object.getprototypeof

	var objectGetPrototypeOf = correctPrototypeGetter ? Object$4.getPrototypeOf : function (O) {
	  var object = toObject(O);
	  if (hasOwnProperty_1(object, IE_PROTO)) return object[IE_PROTO];
	  var constructor = object.constructor;

	  if (isCallable(constructor) && object instanceof constructor) {
	    return constructor.prototype;
	  }

	  return object instanceof Object$4 ? ObjectPrototype : null;
	};

	var String$3 = global_1.String;
	var TypeError$8 = global_1.TypeError;

	var aPossiblePrototype = function (argument) {
	  if (typeof argument == 'object' || isCallable(argument)) return argument;
	  throw TypeError$8("Can't set " + String$3(argument) + ' as a prototype');
	};

	/* eslint-disable no-proto -- safe */




	 // `Object.setPrototypeOf` method
	// https://tc39.es/ecma262/#sec-object.setprototypeof
	// Works with __proto__ only. Old v8 can't work with null proto objects.
	// eslint-disable-next-line es/no-object-setprototypeof -- safe


	var objectSetPrototypeOf = Object.setPrototypeOf || ('__proto__' in {} ? function () {
	  var CORRECT_SETTER = false;
	  var test = {};
	  var setter;

	  try {
	    // eslint-disable-next-line es/no-object-getownpropertydescriptor -- safe
	    setter = functionUncurryThis(Object.getOwnPropertyDescriptor(Object.prototype, '__proto__').set);
	    setter(test, []);
	    CORRECT_SETTER = test instanceof Array;
	  } catch (error) {
	    /* empty */
	  }

	  return function setPrototypeOf(O, proto) {
	    anObject(O);
	    aPossiblePrototype(proto);
	    if (CORRECT_SETTER) setter(O, proto);else O.__proto__ = proto;
	    return O;
	  };
	}() : undefined);

	// `Object.keys` method
	// https://tc39.es/ecma262/#sec-object.keys
	// eslint-disable-next-line es/no-object-keys -- safe


	var objectKeys = Object.keys || function keys(O) {
	  return objectKeysInternal(O, enumBugKeys);
	};

	// `Object.defineProperties` method
	// https://tc39.es/ecma262/#sec-object.defineproperties
	// eslint-disable-next-line es/no-object-defineproperties -- safe


	var objectDefineProperties = descriptors ? Object.defineProperties : function defineProperties(O, Properties) {
	  anObject(O);
	  var props = toIndexedObject(Properties);
	  var keys = objectKeys(Properties);
	  var length = keys.length;
	  var index = 0;
	  var key;

	  while (length > index) objectDefineProperty.f(O, key = keys[index++], props[key]);

	  return O;
	};

	var html = getBuiltIn('document', 'documentElement');

	/* global ActiveXObject -- old IE, WSH */














	var GT = '>';
	var LT = '<';
	var PROTOTYPE = 'prototype';
	var SCRIPT = 'script';
	var IE_PROTO$1 = sharedKey('IE_PROTO');

	var EmptyConstructor = function () {
	  /* empty */
	};

	var scriptTag = function (content) {
	  return LT + SCRIPT + GT + content + LT + '/' + SCRIPT + GT;
	}; // Create object with fake `null` prototype: use ActiveX Object with cleared prototype


	var NullProtoObjectViaActiveX = function (activeXDocument) {
	  activeXDocument.write(scriptTag(''));
	  activeXDocument.close();
	  var temp = activeXDocument.parentWindow.Object;
	  activeXDocument = null; // avoid memory leak

	  return temp;
	}; // Create object with fake `null` prototype: use iframe Object with cleared prototype


	var NullProtoObjectViaIFrame = function () {
	  // Thrash, waste and sodomy: IE GC bug
	  var iframe = documentCreateElement('iframe');
	  var JS = 'java' + SCRIPT + ':';
	  var iframeDocument;
	  iframe.style.display = 'none';
	  html.appendChild(iframe); // https://github.com/zloirock/core-js/issues/475

	  iframe.src = String(JS);
	  iframeDocument = iframe.contentWindow.document;
	  iframeDocument.open();
	  iframeDocument.write(scriptTag('document.F=Object'));
	  iframeDocument.close();
	  return iframeDocument.F;
	}; // Check for document.domain and active x support
	// No need to use active x approach when document.domain is not set
	// see https://github.com/es-shims/es5-shim/issues/150
	// variation of https://github.com/kitcambridge/es5-shim/commit/4f738ac066346
	// avoid IE GC bug


	var activeXDocument;

	var NullProtoObject = function () {
	  try {
	    activeXDocument = new ActiveXObject('htmlfile');
	  } catch (error) {
	    /* ignore */
	  }

	  NullProtoObject = typeof document != 'undefined' ? document.domain && activeXDocument ? NullProtoObjectViaActiveX(activeXDocument) // old IE
	  : NullProtoObjectViaIFrame() : NullProtoObjectViaActiveX(activeXDocument); // WSH

	  var length = enumBugKeys.length;

	  while (length--) delete NullProtoObject[PROTOTYPE][enumBugKeys[length]];

	  return NullProtoObject();
	};

	hiddenKeys[IE_PROTO$1] = true; // `Object.create` method
	// https://tc39.es/ecma262/#sec-object.create

	var objectCreate = Object.create || function create(O, Properties) {
	  var result;

	  if (O !== null) {
	    EmptyConstructor[PROTOTYPE] = anObject(O);
	    result = new EmptyConstructor();
	    EmptyConstructor[PROTOTYPE] = null; // add "__proto__" for Object.getPrototypeOf polyfill

	    result[IE_PROTO$1] = O;
	  } else result = NullProtoObject();

	  return Properties === undefined ? result : objectDefineProperties(result, Properties);
	};

	var createProperty = function (object, key, value) {
	  var propertyKey = toPropertyKey(key);
	  if (propertyKey in object) objectDefineProperty.f(object, propertyKey, createPropertyDescriptor(0, value));else object[propertyKey] = value;
	};

	var Array$1 = global_1.Array;
	var max$1 = Math.max;

	var arraySliceSimple = function (O, start, end) {
	  var length = lengthOfArrayLike(O);
	  var k = toAbsoluteIndex(start, length);
	  var fin = toAbsoluteIndex(end === undefined ? length : end, length);
	  var result = Array$1(max$1(fin - k, 0));

	  for (var n = 0; k < fin; k++, n++) createProperty(result, n, O[k]);

	  result.length = n;
	  return result;
	};

	var replace = functionUncurryThis(''.replace);
	var split$1 = functionUncurryThis(''.split);
	var join = functionUncurryThis([].join);

	var TEST = function (arg) {
	  return String(Error(arg).stack);
	}('zxcasd');

	var V8_OR_CHAKRA_STACK_ENTRY = /\n\s*at [^:]*:[^\n]*/;
	var IS_V8_OR_CHAKRA_STACK = V8_OR_CHAKRA_STACK_ENTRY.test(TEST);
	var IS_FIREFOX_OR_SAFARI_STACK = /@[^\n]*\n/.test(TEST) && !/zxcasd/.test(TEST);

	var clearErrorStack = function (stack, dropEntries) {
	  if (typeof stack != 'string') return stack;

	  if (IS_V8_OR_CHAKRA_STACK) {
	    while (dropEntries--) stack = replace(stack, V8_OR_CHAKRA_STACK_ENTRY, '');
	  } else if (IS_FIREFOX_OR_SAFARI_STACK) {
	    return join(arraySliceSimple(split$1(stack, '\n'), dropEntries), '\n');
	  }

	  return stack;
	};

	// `InstallErrorCause` abstract operation
	// https://tc39.es/proposal-error-cause/#sec-errorobjects-install-error-cause


	var installErrorCause = function (O, options) {
	  if (isObject(options) && 'cause' in options) {
	    createNonEnumerableProperty(O, 'cause', options.cause);
	  }
	};

	var bind$1 = functionUncurryThis(functionUncurryThis.bind); // optional / simple context binding

	var functionBindContext = function (fn, that) {
	  aCallable(fn);
	  return that === undefined ? fn : bind$1 ? bind$1(fn, that) : function ()
	  /* ...args */
	  {
	    return fn.apply(that, arguments);
	  };
	};

	var iterators = {};

	var ITERATOR = wellKnownSymbol('iterator');
	var ArrayPrototype = Array.prototype; // check on default Array iterator

	var isArrayIteratorMethod = function (it) {
	  return it !== undefined && (iterators.Array === it || ArrayPrototype[ITERATOR] === it);
	};

	var TO_STRING_TAG = wellKnownSymbol('toStringTag');
	var test = {};
	test[TO_STRING_TAG] = 'z';
	var toStringTagSupport = String(test) === '[object z]';

	var TO_STRING_TAG$1 = wellKnownSymbol('toStringTag');
	var Object$5 = global_1.Object; // ES3 wrong here

	var CORRECT_ARGUMENTS = classofRaw(function () {
	  return arguments;
	}()) == 'Arguments'; // fallback for IE11 Script Access Denied error

	var tryGet = function (it, key) {
	  try {
	    return it[key];
	  } catch (error) {
	    /* empty */
	  }
	}; // getting tag from ES6+ `Object.prototype.toString`


	var classof = toStringTagSupport ? classofRaw : function (it) {
	  var O, tag, result;
	  return it === undefined ? 'Undefined' : it === null ? 'Null' // @@toStringTag case
	  : typeof (tag = tryGet(O = Object$5(it), TO_STRING_TAG$1)) == 'string' ? tag // builtinTag case
	  : CORRECT_ARGUMENTS ? classofRaw(O) // ES3 arguments fallback
	  : (result = classofRaw(O)) == 'Object' && isCallable(O.callee) ? 'Arguments' : result;
	};

	var ITERATOR$1 = wellKnownSymbol('iterator');

	var getIteratorMethod = function (it) {
	  if (it != undefined) return getMethod(it, ITERATOR$1) || getMethod(it, '@@iterator') || iterators[classof(it)];
	};

	var TypeError$9 = global_1.TypeError;

	var getIterator = function (argument, usingIterator) {
	  var iteratorMethod = arguments.length < 2 ? getIteratorMethod(argument) : usingIterator;
	  if (aCallable(iteratorMethod)) return anObject(functionCall(iteratorMethod, argument));
	  throw TypeError$9(tryToString(argument) + ' is not iterable');
	};

	var iteratorClose = function (iterator, kind, value) {
	  var innerResult, innerError;
	  anObject(iterator);

	  try {
	    innerResult = getMethod(iterator, 'return');

	    if (!innerResult) {
	      if (kind === 'throw') throw value;
	      return value;
	    }

	    innerResult = functionCall(innerResult, iterator);
	  } catch (error) {
	    innerError = true;
	    innerResult = error;
	  }

	  if (kind === 'throw') throw value;
	  if (innerError) throw innerResult;
	  anObject(innerResult);
	  return value;
	};

	var TypeError$a = global_1.TypeError;

	var Result = function (stopped, result) {
	  this.stopped = stopped;
	  this.result = result;
	};

	var ResultPrototype = Result.prototype;

	var iterate = function (iterable, unboundFunction, options) {
	  var that = options && options.that;
	  var AS_ENTRIES = !!(options && options.AS_ENTRIES);
	  var IS_ITERATOR = !!(options && options.IS_ITERATOR);
	  var INTERRUPTED = !!(options && options.INTERRUPTED);
	  var fn = functionBindContext(unboundFunction, that);
	  var iterator, iterFn, index, length, result, next, step;

	  var stop = function (condition) {
	    if (iterator) iteratorClose(iterator, 'normal', condition);
	    return new Result(true, condition);
	  };

	  var callFn = function (value) {
	    if (AS_ENTRIES) {
	      anObject(value);
	      return INTERRUPTED ? fn(value[0], value[1], stop) : fn(value[0], value[1]);
	    }

	    return INTERRUPTED ? fn(value, stop) : fn(value);
	  };

	  if (IS_ITERATOR) {
	    iterator = iterable;
	  } else {
	    iterFn = getIteratorMethod(iterable);
	    if (!iterFn) throw TypeError$a(tryToString(iterable) + ' is not iterable'); // optimisation for array iterators

	    if (isArrayIteratorMethod(iterFn)) {
	      for (index = 0, length = lengthOfArrayLike(iterable); length > index; index++) {
	        result = callFn(iterable[index]);
	        if (result && objectIsPrototypeOf(ResultPrototype, result)) return result;
	      }

	      return new Result(false);
	    }

	    iterator = getIterator(iterable, iterFn);
	  }

	  next = iterator.next;

	  while (!(step = functionCall(next, iterator)).done) {
	    try {
	      result = callFn(step.value);
	    } catch (error) {
	      iteratorClose(iterator, 'throw', error);
	    }

	    if (typeof result == 'object' && result && objectIsPrototypeOf(ResultPrototype, result)) return result;
	  }

	  return new Result(false);
	};

	var String$4 = global_1.String;

	var toString_1 = function (argument) {
	  if (classof(argument) === 'Symbol') throw TypeError('Cannot convert a Symbol value to a string');
	  return String$4(argument);
	};

	var normalizeStringArgument = function (argument, $default) {
	  return argument === undefined ? arguments.length < 2 ? '' : $default : toString_1(argument);
	};

	var errorStackInstallable = !fails(function () {
	  var error = Error('a');
	  if (!('stack' in error)) return true; // eslint-disable-next-line es/no-object-defineproperty -- safe

	  Object.defineProperty(error, 'stack', createPropertyDescriptor(1, 7));
	  return error.stack !== 7;
	});

	var TO_STRING_TAG$2 = wellKnownSymbol('toStringTag');
	var Error$1 = global_1.Error;
	var push$1 = [].push;

	var $AggregateError = function AggregateError(errors, message
	/* , options */
	) {
	  var options = arguments.length > 2 ? arguments[2] : undefined;
	  var isInstance = objectIsPrototypeOf(AggregateErrorPrototype, this);
	  var that;

	  if (objectSetPrototypeOf) {
	    that = objectSetPrototypeOf(new Error$1(undefined), isInstance ? objectGetPrototypeOf(this) : AggregateErrorPrototype);
	  } else {
	    that = isInstance ? this : objectCreate(AggregateErrorPrototype);
	    createNonEnumerableProperty(that, TO_STRING_TAG$2, 'Error');
	  }

	  createNonEnumerableProperty(that, 'message', normalizeStringArgument(message, ''));
	  if (errorStackInstallable) createNonEnumerableProperty(that, 'stack', clearErrorStack(that.stack, 1));
	  installErrorCause(that, options);
	  var errorsArray = [];
	  iterate(errors, push$1, {
	    that: errorsArray
	  });
	  createNonEnumerableProperty(that, 'errors', errorsArray);
	  return that;
	};

	if (objectSetPrototypeOf) objectSetPrototypeOf($AggregateError, Error$1);else copyConstructorProperties($AggregateError, Error$1);
	var AggregateErrorPrototype = $AggregateError.prototype = objectCreate(Error$1.prototype, {
	  constructor: createPropertyDescriptor(1, $AggregateError),
	  message: createPropertyDescriptor(1, ''),
	  name: createPropertyDescriptor(1, 'AggregateError')
	}); // `AggregateError` constructor
	// https://tc39.es/ecma262/#sec-aggregate-error-constructor

	_export({
	  global: true
	}, {
	  AggregateError: $AggregateError
	});

	var UNSCOPABLES = wellKnownSymbol('unscopables');
	var ArrayPrototype$1 = Array.prototype; // Array.prototype[@@unscopables]
	// https://tc39.es/ecma262/#sec-array.prototype-@@unscopables

	if (ArrayPrototype$1[UNSCOPABLES] == undefined) {
	  objectDefineProperty.f(ArrayPrototype$1, UNSCOPABLES, {
	    configurable: true,
	    value: objectCreate(null)
	  });
	} // add a key to Array.prototype[@@unscopables]


	var addToUnscopables = function (key) {
	  ArrayPrototype$1[UNSCOPABLES][key] = true;
	};

	// `Array.prototype.at` method
	// https://github.com/tc39/proposal-relative-indexing-method


	_export({
	  target: 'Array',
	  proto: true
	}, {
	  at: function at(index) {
	    var O = toObject(this);
	    var len = lengthOfArrayLike(O);
	    var relativeIndex = toIntegerOrInfinity(index);
	    var k = relativeIndex >= 0 ? relativeIndex : len + relativeIndex;
	    return k < 0 || k >= len ? undefined : O[k];
	  }
	});
	addToUnscopables('at');

	// `IsArray` abstract operation
	// https://tc39.es/ecma262/#sec-isarray
	// eslint-disable-next-line es/no-array-isarray -- safe


	var isArray = Array.isArray || function isArray(argument) {
	  return classofRaw(argument) == 'Array';
	};

	var un$Reverse = functionUncurryThis([].reverse);
	var test$1 = [1, 2]; // `Array.prototype.reverse` method
	// https://tc39.es/ecma262/#sec-array.prototype.reverse
	// fix for Safari 12.0 bug
	// https://bugs.webkit.org/show_bug.cgi?id=188794

	_export({
	  target: 'Array',
	  proto: true,
	  forced: String(test$1) === String(test$1.reverse())
	}, {
	  reverse: function reverse() {
	    // eslint-disable-next-line no-self-assign -- dirty hack
	    if (isArray(this)) this.length = this.length;
	    return un$Reverse(this);
	  }
	});

	// eslint-disable-next-line es/no-typed-arrays -- safe
	var arrayBufferNative = typeof ArrayBuffer != 'undefined' && typeof DataView != 'undefined';

	var redefineAll = function (target, src, options) {
	  for (var key in src) redefine(target, key, src[key], options);

	  return target;
	};

	var TypeError$b = global_1.TypeError;

	var anInstance = function (it, Prototype) {
	  if (objectIsPrototypeOf(Prototype, it)) return it;
	  throw TypeError$b('Incorrect invocation');
	};

	var RangeError = global_1.RangeError; // `ToIndex` abstract operation
	// https://tc39.es/ecma262/#sec-toindex

	var toIndex = function (it) {
	  if (it === undefined) return 0;
	  var number = toIntegerOrInfinity(it);
	  var length = toLength(number);
	  if (number !== length) throw RangeError('Wrong length or index');
	  return length;
	};

	// IEEE754 conversions based on https://github.com/feross/ieee754


	var Array$2 = global_1.Array;
	var abs = Math.abs;
	var pow = Math.pow;
	var floor$1 = Math.floor;
	var log = Math.log;
	var LN2 = Math.LN2;

	var pack = function (number, mantissaLength, bytes) {
	  var buffer = Array$2(bytes);
	  var exponentLength = bytes * 8 - mantissaLength - 1;
	  var eMax = (1 << exponentLength) - 1;
	  var eBias = eMax >> 1;
	  var rt = mantissaLength === 23 ? pow(2, -24) - pow(2, -77) : 0;
	  var sign = number < 0 || number === 0 && 1 / number < 0 ? 1 : 0;
	  var index = 0;
	  var exponent, mantissa, c;
	  number = abs(number); // eslint-disable-next-line no-self-compare -- NaN check

	  if (number != number || number === Infinity) {
	    // eslint-disable-next-line no-self-compare -- NaN check
	    mantissa = number != number ? 1 : 0;
	    exponent = eMax;
	  } else {
	    exponent = floor$1(log(number) / LN2);
	    c = pow(2, -exponent);

	    if (number * c < 1) {
	      exponent--;
	      c *= 2;
	    }

	    if (exponent + eBias >= 1) {
	      number += rt / c;
	    } else {
	      number += rt * pow(2, 1 - eBias);
	    }

	    if (number * c >= 2) {
	      exponent++;
	      c /= 2;
	    }

	    if (exponent + eBias >= eMax) {
	      mantissa = 0;
	      exponent = eMax;
	    } else if (exponent + eBias >= 1) {
	      mantissa = (number * c - 1) * pow(2, mantissaLength);
	      exponent = exponent + eBias;
	    } else {
	      mantissa = number * pow(2, eBias - 1) * pow(2, mantissaLength);
	      exponent = 0;
	    }
	  }

	  while (mantissaLength >= 8) {
	    buffer[index++] = mantissa & 255;
	    mantissa /= 256;
	    mantissaLength -= 8;
	  }

	  exponent = exponent << mantissaLength | mantissa;
	  exponentLength += mantissaLength;

	  while (exponentLength > 0) {
	    buffer[index++] = exponent & 255;
	    exponent /= 256;
	    exponentLength -= 8;
	  }

	  buffer[--index] |= sign * 128;
	  return buffer;
	};

	var unpack = function (buffer, mantissaLength) {
	  var bytes = buffer.length;
	  var exponentLength = bytes * 8 - mantissaLength - 1;
	  var eMax = (1 << exponentLength) - 1;
	  var eBias = eMax >> 1;
	  var nBits = exponentLength - 7;
	  var index = bytes - 1;
	  var sign = buffer[index--];
	  var exponent = sign & 127;
	  var mantissa;
	  sign >>= 7;

	  while (nBits > 0) {
	    exponent = exponent * 256 + buffer[index--];
	    nBits -= 8;
	  }

	  mantissa = exponent & (1 << -nBits) - 1;
	  exponent >>= -nBits;
	  nBits += mantissaLength;

	  while (nBits > 0) {
	    mantissa = mantissa * 256 + buffer[index--];
	    nBits -= 8;
	  }

	  if (exponent === 0) {
	    exponent = 1 - eBias;
	  } else if (exponent === eMax) {
	    return mantissa ? NaN : sign ? -Infinity : Infinity;
	  } else {
	    mantissa = mantissa + pow(2, mantissaLength);
	    exponent = exponent - eBias;
	  }

	  return (sign ? -1 : 1) * mantissa * pow(2, exponent - mantissaLength);
	};

	var ieee754 = {
	  pack: pack,
	  unpack: unpack
	};

	// `Array.prototype.fill` method implementation
	// https://tc39.es/ecma262/#sec-array.prototype.fill


	var arrayFill = function fill(value
	/* , start = 0, end = @length */
	) {
	  var O = toObject(this);
	  var length = lengthOfArrayLike(O);
	  var argumentsLength = arguments.length;
	  var index = toAbsoluteIndex(argumentsLength > 1 ? arguments[1] : undefined, length);
	  var end = argumentsLength > 2 ? arguments[2] : undefined;
	  var endPos = end === undefined ? length : toAbsoluteIndex(end, length);

	  while (endPos > index) O[index++] = value;

	  return O;
	};

	var defineProperty$1 = objectDefineProperty.f;





	var TO_STRING_TAG$3 = wellKnownSymbol('toStringTag');

	var setToStringTag = function (it, TAG, STATIC) {
	  if (it && !hasOwnProperty_1(it = STATIC ? it : it.prototype, TO_STRING_TAG$3)) {
	    defineProperty$1(it, TO_STRING_TAG$3, {
	      configurable: true,
	      value: TAG
	    });
	  }
	};

	var getOwnPropertyNames = objectGetOwnPropertyNames.f;

	var defineProperty$2 = objectDefineProperty.f;









	var PROPER_FUNCTION_NAME = functionName.PROPER;
	var CONFIGURABLE_FUNCTION_NAME = functionName.CONFIGURABLE;
	var getInternalState = internalState.get;
	var setInternalState = internalState.set;
	var ARRAY_BUFFER = 'ArrayBuffer';
	var DATA_VIEW = 'DataView';
	var PROTOTYPE$1 = 'prototype';
	var WRONG_LENGTH = 'Wrong length';
	var WRONG_INDEX = 'Wrong index';
	var NativeArrayBuffer = global_1[ARRAY_BUFFER];
	var $ArrayBuffer = NativeArrayBuffer;
	var ArrayBufferPrototype = $ArrayBuffer && $ArrayBuffer[PROTOTYPE$1];
	var $DataView = global_1[DATA_VIEW];
	var DataViewPrototype = $DataView && $DataView[PROTOTYPE$1];
	var ObjectPrototype$1 = Object.prototype;
	var Array$3 = global_1.Array;
	var RangeError$1 = global_1.RangeError;
	var fill = functionUncurryThis(arrayFill);
	var reverse = functionUncurryThis([].reverse);
	var packIEEE754 = ieee754.pack;
	var unpackIEEE754 = ieee754.unpack;

	var packInt8 = function (number) {
	  return [number & 0xFF];
	};

	var packInt16 = function (number) {
	  return [number & 0xFF, number >> 8 & 0xFF];
	};

	var packInt32 = function (number) {
	  return [number & 0xFF, number >> 8 & 0xFF, number >> 16 & 0xFF, number >> 24 & 0xFF];
	};

	var unpackInt32 = function (buffer) {
	  return buffer[3] << 24 | buffer[2] << 16 | buffer[1] << 8 | buffer[0];
	};

	var packFloat32 = function (number) {
	  return packIEEE754(number, 23, 4);
	};

	var packFloat64 = function (number) {
	  return packIEEE754(number, 52, 8);
	};

	var addGetter = function (Constructor, key) {
	  defineProperty$2(Constructor[PROTOTYPE$1], key, {
	    get: function () {
	      return getInternalState(this)[key];
	    }
	  });
	};

	var get$1 = function (view, count, index, isLittleEndian) {
	  var intIndex = toIndex(index);
	  var store = getInternalState(view);
	  if (intIndex + count > store.byteLength) throw RangeError$1(WRONG_INDEX);
	  var bytes = getInternalState(store.buffer).bytes;
	  var start = intIndex + store.byteOffset;
	  var pack = arraySliceSimple(bytes, start, start + count);
	  return isLittleEndian ? pack : reverse(pack);
	};

	var set$1 = function (view, count, index, conversion, value, isLittleEndian) {
	  var intIndex = toIndex(index);
	  var store = getInternalState(view);
	  if (intIndex + count > store.byteLength) throw RangeError$1(WRONG_INDEX);
	  var bytes = getInternalState(store.buffer).bytes;
	  var start = intIndex + store.byteOffset;
	  var pack = conversion(+value);

	  for (var i = 0; i < count; i++) bytes[start + i] = pack[isLittleEndian ? i : count - i - 1];
	};

	if (!arrayBufferNative) {
	  $ArrayBuffer = function ArrayBuffer(length) {
	    anInstance(this, ArrayBufferPrototype);
	    var byteLength = toIndex(length);
	    setInternalState(this, {
	      bytes: fill(Array$3(byteLength), 0),
	      byteLength: byteLength
	    });
	    if (!descriptors) this.byteLength = byteLength;
	  };

	  ArrayBufferPrototype = $ArrayBuffer[PROTOTYPE$1];

	  $DataView = function DataView(buffer, byteOffset, byteLength) {
	    anInstance(this, DataViewPrototype);
	    anInstance(buffer, ArrayBufferPrototype);
	    var bufferLength = getInternalState(buffer).byteLength;
	    var offset = toIntegerOrInfinity(byteOffset);
	    if (offset < 0 || offset > bufferLength) throw RangeError$1('Wrong offset');
	    byteLength = byteLength === undefined ? bufferLength - offset : toLength(byteLength);
	    if (offset + byteLength > bufferLength) throw RangeError$1(WRONG_LENGTH);
	    setInternalState(this, {
	      buffer: buffer,
	      byteLength: byteLength,
	      byteOffset: offset
	    });

	    if (!descriptors) {
	      this.buffer = buffer;
	      this.byteLength = byteLength;
	      this.byteOffset = offset;
	    }
	  };

	  DataViewPrototype = $DataView[PROTOTYPE$1];

	  if (descriptors) {
	    addGetter($ArrayBuffer, 'byteLength');
	    addGetter($DataView, 'buffer');
	    addGetter($DataView, 'byteLength');
	    addGetter($DataView, 'byteOffset');
	  }

	  redefineAll(DataViewPrototype, {
	    getInt8: function getInt8(byteOffset) {
	      return get$1(this, 1, byteOffset)[0] << 24 >> 24;
	    },
	    getUint8: function getUint8(byteOffset) {
	      return get$1(this, 1, byteOffset)[0];
	    },
	    getInt16: function getInt16(byteOffset
	    /* , littleEndian */
	    ) {
	      var bytes = get$1(this, 2, byteOffset, arguments.length > 1 ? arguments[1] : undefined);
	      return (bytes[1] << 8 | bytes[0]) << 16 >> 16;
	    },
	    getUint16: function getUint16(byteOffset
	    /* , littleEndian */
	    ) {
	      var bytes = get$1(this, 2, byteOffset, arguments.length > 1 ? arguments[1] : undefined);
	      return bytes[1] << 8 | bytes[0];
	    },
	    getInt32: function getInt32(byteOffset
	    /* , littleEndian */
	    ) {
	      return unpackInt32(get$1(this, 4, byteOffset, arguments.length > 1 ? arguments[1] : undefined));
	    },
	    getUint32: function getUint32(byteOffset
	    /* , littleEndian */
	    ) {
	      return unpackInt32(get$1(this, 4, byteOffset, arguments.length > 1 ? arguments[1] : undefined)) >>> 0;
	    },
	    getFloat32: function getFloat32(byteOffset
	    /* , littleEndian */
	    ) {
	      return unpackIEEE754(get$1(this, 4, byteOffset, arguments.length > 1 ? arguments[1] : undefined), 23);
	    },
	    getFloat64: function getFloat64(byteOffset
	    /* , littleEndian */
	    ) {
	      return unpackIEEE754(get$1(this, 8, byteOffset, arguments.length > 1 ? arguments[1] : undefined), 52);
	    },
	    setInt8: function setInt8(byteOffset, value) {
	      set$1(this, 1, byteOffset, packInt8, value);
	    },
	    setUint8: function setUint8(byteOffset, value) {
	      set$1(this, 1, byteOffset, packInt8, value);
	    },
	    setInt16: function setInt16(byteOffset, value
	    /* , littleEndian */
	    ) {
	      set$1(this, 2, byteOffset, packInt16, value, arguments.length > 2 ? arguments[2] : undefined);
	    },
	    setUint16: function setUint16(byteOffset, value
	    /* , littleEndian */
	    ) {
	      set$1(this, 2, byteOffset, packInt16, value, arguments.length > 2 ? arguments[2] : undefined);
	    },
	    setInt32: function setInt32(byteOffset, value
	    /* , littleEndian */
	    ) {
	      set$1(this, 4, byteOffset, packInt32, value, arguments.length > 2 ? arguments[2] : undefined);
	    },
	    setUint32: function setUint32(byteOffset, value
	    /* , littleEndian */
	    ) {
	      set$1(this, 4, byteOffset, packInt32, value, arguments.length > 2 ? arguments[2] : undefined);
	    },
	    setFloat32: function setFloat32(byteOffset, value
	    /* , littleEndian */
	    ) {
	      set$1(this, 4, byteOffset, packFloat32, value, arguments.length > 2 ? arguments[2] : undefined);
	    },
	    setFloat64: function setFloat64(byteOffset, value
	    /* , littleEndian */
	    ) {
	      set$1(this, 8, byteOffset, packFloat64, value, arguments.length > 2 ? arguments[2] : undefined);
	    }
	  });
	} else {
	  var INCORRECT_ARRAY_BUFFER_NAME = PROPER_FUNCTION_NAME && NativeArrayBuffer.name !== ARRAY_BUFFER;
	  /* eslint-disable no-new -- required for testing */

	  if (!fails(function () {
	    NativeArrayBuffer(1);
	  }) || !fails(function () {
	    new NativeArrayBuffer(-1);
	  }) || fails(function () {
	    new NativeArrayBuffer();
	    new NativeArrayBuffer(1.5);
	    new NativeArrayBuffer(NaN);
	    return INCORRECT_ARRAY_BUFFER_NAME && !CONFIGURABLE_FUNCTION_NAME;
	  })) {
	    /* eslint-enable no-new -- required for testing */
	    $ArrayBuffer = function ArrayBuffer(length) {
	      anInstance(this, ArrayBufferPrototype);
	      return new NativeArrayBuffer(toIndex(length));
	    };

	    $ArrayBuffer[PROTOTYPE$1] = ArrayBufferPrototype;

	    for (var keys$1 = getOwnPropertyNames(NativeArrayBuffer), j = 0, key; keys$1.length > j;) {
	      if (!((key = keys$1[j++]) in $ArrayBuffer)) {
	        createNonEnumerableProperty($ArrayBuffer, key, NativeArrayBuffer[key]);
	      }
	    }

	    ArrayBufferPrototype.constructor = $ArrayBuffer;
	  } else if (INCORRECT_ARRAY_BUFFER_NAME && CONFIGURABLE_FUNCTION_NAME) {
	    createNonEnumerableProperty(NativeArrayBuffer, 'name', ARRAY_BUFFER);
	  } // WebKit bug - the same parent prototype for typed arrays and data view


	  if (objectSetPrototypeOf && objectGetPrototypeOf(DataViewPrototype) !== ObjectPrototype$1) {
	    objectSetPrototypeOf(DataViewPrototype, ObjectPrototype$1);
	  } // iOS Safari 7.x bug


	  var testView = new $DataView(new $ArrayBuffer(2));
	  var $setInt8 = functionUncurryThis(DataViewPrototype.setInt8);
	  testView.setInt8(0, 2147483648);
	  testView.setInt8(1, 2147483649);
	  if (testView.getInt8(0) || !testView.getInt8(1)) redefineAll(DataViewPrototype, {
	    setInt8: function setInt8(byteOffset, value) {
	      $setInt8(this, byteOffset, value << 24 >> 24);
	    },
	    setUint8: function setUint8(byteOffset, value) {
	      $setInt8(this, byteOffset, value << 24 >> 24);
	    }
	  }, {
	    unsafe: true
	  });
	}

	setToStringTag($ArrayBuffer, ARRAY_BUFFER);
	setToStringTag($DataView, DATA_VIEW);
	var arrayBuffer = {
	  ArrayBuffer: $ArrayBuffer,
	  DataView: $DataView
	};

	var noop = function () {
	  /* empty */
	};

	var empty = [];
	var construct = getBuiltIn('Reflect', 'construct');
	var constructorRegExp = /^\s*(?:class|function)\b/;
	var exec = functionUncurryThis(constructorRegExp.exec);
	var INCORRECT_TO_STRING = !constructorRegExp.exec(noop);

	var isConstructorModern = function (argument) {
	  if (!isCallable(argument)) return false;

	  try {
	    construct(noop, empty, argument);
	    return true;
	  } catch (error) {
	    return false;
	  }
	};

	var isConstructorLegacy = function (argument) {
	  if (!isCallable(argument)) return false;

	  switch (classof(argument)) {
	    case 'AsyncFunction':
	    case 'GeneratorFunction':
	    case 'AsyncGeneratorFunction':
	      return false;
	    // we can't check .prototype since constructors produced by .bind haven't it
	  }

	  return INCORRECT_TO_STRING || !!exec(constructorRegExp, inspectSource(argument));
	}; // `IsConstructor` abstract operation
	// https://tc39.es/ecma262/#sec-isconstructor


	var isConstructor = !construct || fails(function () {
	  var called;
	  return isConstructorModern(isConstructorModern.call) || !isConstructorModern(Object) || !isConstructorModern(function () {
	    called = true;
	  }) || called;
	}) ? isConstructorLegacy : isConstructorModern;

	var TypeError$c = global_1.TypeError; // `Assert: IsConstructor(argument) is true`

	var aConstructor = function (argument) {
	  if (isConstructor(argument)) return argument;
	  throw TypeError$c(tryToString(argument) + ' is not a constructor');
	};

	var SPECIES = wellKnownSymbol('species'); // `SpeciesConstructor` abstract operation
	// https://tc39.es/ecma262/#sec-speciesconstructor

	var speciesConstructor = function (O, defaultConstructor) {
	  var C = anObject(O).constructor;
	  var S;
	  return C === undefined || (S = anObject(C)[SPECIES]) == undefined ? defaultConstructor : aConstructor(S);
	};

	var ArrayBuffer$1 = arrayBuffer.ArrayBuffer;
	var DataView$1 = arrayBuffer.DataView;
	var DataViewPrototype$1 = DataView$1.prototype;
	var un$ArrayBufferSlice = functionUncurryThis(ArrayBuffer$1.prototype.slice);
	var getUint8 = functionUncurryThis(DataViewPrototype$1.getUint8);
	var setUint8 = functionUncurryThis(DataViewPrototype$1.setUint8);
	var INCORRECT_SLICE = fails(function () {
	  return !new ArrayBuffer$1(2).slice(1, undefined).byteLength;
	}); // `ArrayBuffer.prototype.slice` method
	// https://tc39.es/ecma262/#sec-arraybuffer.prototype.slice

	_export({
	  target: 'ArrayBuffer',
	  proto: true,
	  unsafe: true,
	  forced: INCORRECT_SLICE
	}, {
	  slice: function slice(start, end) {
	    if (un$ArrayBufferSlice && end === undefined) {
	      return un$ArrayBufferSlice(anObject(this), start); // FF fix
	    }

	    var length = anObject(this).byteLength;
	    var first = toAbsoluteIndex(start, length);
	    var fin = toAbsoluteIndex(end === undefined ? length : end, length);
	    var result = new (speciesConstructor(this, ArrayBuffer$1))(toLength(fin - first));
	    var viewSource = new DataView$1(this);
	    var viewTarget = new DataView$1(result);
	    var index = 0;

	    while (first < fin) {
	      setUint8(viewTarget, index++, getUint8(viewSource, first++));
	    }

	    return result;
	  }
	});

	// `Object.fromEntries` method
	// https://github.com/tc39/proposal-object-from-entries


	_export({
	  target: 'Object',
	  stat: true
	}, {
	  fromEntries: function fromEntries(iterable) {
	    var obj = {};
	    iterate(iterable, function (k, v) {
	      createProperty(obj, k, v);
	    }, {
	      AS_ENTRIES: true
	    });
	    return obj;
	  }
	});

	// `Object.hasOwn` method
	// https://github.com/tc39/proposal-accessible-object-hasownproperty


	_export({
	  target: 'Object',
	  stat: true
	}, {
	  hasOwn: hasOwnProperty_1
	});

	var PromiseCapability = function (C) {
	  var resolve, reject;
	  this.promise = new C(function ($$resolve, $$reject) {
	    if (resolve !== undefined || reject !== undefined) throw TypeError('Bad Promise constructor');
	    resolve = $$resolve;
	    reject = $$reject;
	  });
	  this.resolve = aCallable(resolve);
	  this.reject = aCallable(reject);
	}; // `NewPromiseCapability` abstract operation
	// https://tc39.es/ecma262/#sec-newpromisecapability


	var f$5 = function (C) {
	  return new PromiseCapability(C);
	};

	var newPromiseCapability = {
		f: f$5
	};

	var perform = function (exec) {
	  try {
	    return {
	      error: false,
	      value: exec()
	    };
	  } catch (error) {
	    return {
	      error: true,
	      value: error
	    };
	  }
	};

	// `Promise.allSettled` method
	// https://tc39.es/ecma262/#sec-promise.allsettled


	_export({
	  target: 'Promise',
	  stat: true
	}, {
	  allSettled: function allSettled(iterable) {
	    var C = this;
	    var capability = newPromiseCapability.f(C);
	    var resolve = capability.resolve;
	    var reject = capability.reject;
	    var result = perform(function () {
	      var promiseResolve = aCallable(C.resolve);
	      var values = [];
	      var counter = 0;
	      var remaining = 1;
	      iterate(iterable, function (promise) {
	        var index = counter++;
	        var alreadyCalled = false;
	        remaining++;
	        functionCall(promiseResolve, C, promise).then(function (value) {
	          if (alreadyCalled) return;
	          alreadyCalled = true;
	          values[index] = {
	            status: 'fulfilled',
	            value: value
	          };
	          --remaining || resolve(values);
	        }, function (error) {
	          if (alreadyCalled) return;
	          alreadyCalled = true;
	          values[index] = {
	            status: 'rejected',
	            reason: error
	          };
	          --remaining || resolve(values);
	        });
	      });
	      --remaining || resolve(values);
	    });
	    if (result.error) reject(result.value);
	    return capability.promise;
	  }
	});

	var PROMISE_ANY_ERROR = 'No one promise resolved'; // `Promise.any` method
	// https://tc39.es/ecma262/#sec-promise.any

	_export({
	  target: 'Promise',
	  stat: true
	}, {
	  any: function any(iterable) {
	    var C = this;
	    var AggregateError = getBuiltIn('AggregateError');
	    var capability = newPromiseCapability.f(C);
	    var resolve = capability.resolve;
	    var reject = capability.reject;
	    var result = perform(function () {
	      var promiseResolve = aCallable(C.resolve);
	      var errors = [];
	      var counter = 0;
	      var remaining = 1;
	      var alreadyResolved = false;
	      iterate(iterable, function (promise) {
	        var index = counter++;
	        var alreadyRejected = false;
	        remaining++;
	        functionCall(promiseResolve, C, promise).then(function (value) {
	          if (alreadyRejected || alreadyResolved) return;
	          alreadyResolved = true;
	          resolve(value);
	        }, function (error) {
	          if (alreadyRejected || alreadyResolved) return;
	          alreadyRejected = true;
	          errors[index] = error;
	          --remaining || reject(new AggregateError(errors, PROMISE_ANY_ERROR));
	        });
	      });
	      --remaining || reject(new AggregateError(errors, PROMISE_ANY_ERROR));
	    });
	    if (result.error) reject(result.value);
	    return capability.promise;
	  }
	});

	var nativePromiseConstructor = global_1.Promise;

	var promiseResolve = function (C, x) {
	  anObject(C);
	  if (isObject(x) && x.constructor === C) return x;
	  var promiseCapability = newPromiseCapability.f(C);
	  var resolve = promiseCapability.resolve;
	  resolve(x);
	  return promiseCapability.promise;
	};

	// Safari bug https://bugs.webkit.org/show_bug.cgi?id=200829


	var NON_GENERIC = !!nativePromiseConstructor && fails(function () {
	  nativePromiseConstructor.prototype['finally'].call({
	    then: function () {
	      /* empty */
	    }
	  }, function () {
	    /* empty */
	  });
	}); // `Promise.prototype.finally` method
	// https://tc39.es/ecma262/#sec-promise.prototype.finally

	_export({
	  target: 'Promise',
	  proto: true,
	  real: true,
	  forced: NON_GENERIC
	}, {
	  'finally': function (onFinally) {
	    var C = speciesConstructor(this, getBuiltIn('Promise'));
	    var isFunction = isCallable(onFinally);
	    return this.then(isFunction ? function (x) {
	      return promiseResolve(C, onFinally()).then(function () {
	        return x;
	      });
	    } : onFinally, isFunction ? function (e) {
	      return promiseResolve(C, onFinally()).then(function () {
	        throw e;
	      });
	    } : onFinally);
	  }
	}); // makes sure that native promise-based APIs `Promise#finally` properly works with patched `Promise#then`

	if (!isPure && isCallable(nativePromiseConstructor)) {
	  var method = getBuiltIn('Promise').prototype['finally'];

	  if (nativePromiseConstructor.prototype['finally'] !== method) {
	    redefine(nativePromiseConstructor.prototype, 'finally', method, {
	      unsafe: true
	    });
	  }
	}

	var ITERATOR$2 = wellKnownSymbol('iterator');
	var BUGGY_SAFARI_ITERATORS = false; // `%IteratorPrototype%` object
	// https://tc39.es/ecma262/#sec-%iteratorprototype%-object

	var IteratorPrototype, PrototypeOfArrayIteratorPrototype, arrayIterator;
	/* eslint-disable es/no-array-prototype-keys -- safe */

	if ([].keys) {
	  arrayIterator = [].keys(); // Safari 8 has buggy iterators w/o `next`

	  if (!('next' in arrayIterator)) BUGGY_SAFARI_ITERATORS = true;else {
	    PrototypeOfArrayIteratorPrototype = objectGetPrototypeOf(objectGetPrototypeOf(arrayIterator));
	    if (PrototypeOfArrayIteratorPrototype !== Object.prototype) IteratorPrototype = PrototypeOfArrayIteratorPrototype;
	  }
	}

	var NEW_ITERATOR_PROTOTYPE = IteratorPrototype == undefined || fails(function () {
	  var test = {}; // FF44- legacy iterators case

	  return IteratorPrototype[ITERATOR$2].call(test) !== test;
	});
	if (NEW_ITERATOR_PROTOTYPE) IteratorPrototype = {}; // `%IteratorPrototype%[@@iterator]()` method
	// https://tc39.es/ecma262/#sec-%iteratorprototype%-@@iterator

	if (!isCallable(IteratorPrototype[ITERATOR$2])) {
	  redefine(IteratorPrototype, ITERATOR$2, function () {
	    return this;
	  });
	}

	var iteratorsCore = {
	  IteratorPrototype: IteratorPrototype,
	  BUGGY_SAFARI_ITERATORS: BUGGY_SAFARI_ITERATORS
	};

	var IteratorPrototype$1 = iteratorsCore.IteratorPrototype;









	var returnThis = function () {
	  return this;
	};

	var createIteratorConstructor = function (IteratorConstructor, NAME, next) {
	  var TO_STRING_TAG = NAME + ' Iterator';
	  IteratorConstructor.prototype = objectCreate(IteratorPrototype$1, {
	    next: createPropertyDescriptor(1, next)
	  });
	  setToStringTag(IteratorConstructor, TO_STRING_TAG, false, true);
	  iterators[TO_STRING_TAG] = returnThis;
	  return IteratorConstructor;
	};

	var MATCH = wellKnownSymbol('match'); // `IsRegExp` abstract operation
	// https://tc39.es/ecma262/#sec-isregexp

	var isRegexp = function (it) {
	  var isRegExp;
	  return isObject(it) && ((isRegExp = it[MATCH]) !== undefined ? !!isRegExp : classofRaw(it) == 'RegExp');
	};

	// `RegExp.prototype.flags` getter implementation
	// https://tc39.es/ecma262/#sec-get-regexp.prototype.flags


	var regexpFlags = function () {
	  var that = anObject(this);
	  var result = '';
	  if (that.global) result += 'g';
	  if (that.ignoreCase) result += 'i';
	  if (that.multiline) result += 'm';
	  if (that.dotAll) result += 's';
	  if (that.unicode) result += 'u';
	  if (that.sticky) result += 'y';
	  return result;
	};

	var charAt = functionUncurryThis(''.charAt);
	var charCodeAt = functionUncurryThis(''.charCodeAt);
	var stringSlice$1 = functionUncurryThis(''.slice);

	var createMethod$1 = function (CONVERT_TO_STRING) {
	  return function ($this, pos) {
	    var S = toString_1(requireObjectCoercible($this));
	    var position = toIntegerOrInfinity(pos);
	    var size = S.length;
	    var first, second;
	    if (position < 0 || position >= size) return CONVERT_TO_STRING ? '' : undefined;
	    first = charCodeAt(S, position);
	    return first < 0xD800 || first > 0xDBFF || position + 1 === size || (second = charCodeAt(S, position + 1)) < 0xDC00 || second > 0xDFFF ? CONVERT_TO_STRING ? charAt(S, position) : first : CONVERT_TO_STRING ? stringSlice$1(S, position, position + 2) : (first - 0xD800 << 10) + (second - 0xDC00) + 0x10000;
	  };
	};

	var stringMultibyte = {
	  // `String.prototype.codePointAt` method
	  // https://tc39.es/ecma262/#sec-string.prototype.codepointat
	  codeAt: createMethod$1(false),
	  // `String.prototype.at` method
	  // https://github.com/mathiasbynens/String.prototype.at
	  charAt: createMethod$1(true)
	};

	var charAt$1 = stringMultibyte.charAt; // `AdvanceStringIndex` abstract operation
	// https://tc39.es/ecma262/#sec-advancestringindex


	var advanceStringIndex = function (S, index, unicode) {
	  return index + (unicode ? charAt$1(S, index).length : 1);
	};

	// babel-minify and Closure Compiler transpiles RegExp('a', 'y') -> /a/y and it causes SyntaxError


	var $RegExp = global_1.RegExp;
	var UNSUPPORTED_Y = fails(function () {
	  var re = $RegExp('a', 'y');
	  re.lastIndex = 2;
	  return re.exec('abcd') != null;
	}); // UC Browser bug
	// https://github.com/zloirock/core-js/issues/1008

	var MISSED_STICKY = UNSUPPORTED_Y || fails(function () {
	  return !$RegExp('a', 'y').sticky;
	});
	var BROKEN_CARET = UNSUPPORTED_Y || fails(function () {
	  // https://bugzilla.mozilla.org/show_bug.cgi?id=773687
	  var re = $RegExp('^r', 'gy');
	  re.lastIndex = 2;
	  return re.exec('str') != null;
	});
	var regexpStickyHelpers = {
	  BROKEN_CARET: BROKEN_CARET,
	  MISSED_STICKY: MISSED_STICKY,
	  UNSUPPORTED_Y: UNSUPPORTED_Y
	};

	// babel-minify and Closure Compiler transpiles RegExp('.', 's') -> /./s and it causes SyntaxError


	var $RegExp$1 = global_1.RegExp;
	var regexpUnsupportedDotAll = fails(function () {
	  var re = $RegExp$1('.', 's');
	  return !(re.dotAll && re.exec('\n') && re.flags === 's');
	});

	// babel-minify and Closure Compiler transpiles RegExp('(?<a>b)', 'g') -> /(?<a>b)/g and it causes SyntaxError


	var $RegExp$2 = global_1.RegExp;
	var regexpUnsupportedNcg = fails(function () {
	  var re = $RegExp$2('(?<a>b)', 'g');
	  return re.exec('b').groups.a !== 'b' || 'b'.replace(re, '$<a>c') !== 'bc';
	});

	/* eslint-disable regexp/no-empty-capturing-group, regexp/no-empty-group, regexp/no-lazy-ends -- testing */

	/* eslint-disable regexp/no-useless-quantifier -- testing */















	var getInternalState$1 = internalState.get;





	var nativeReplace = shared('native-string-replace', String.prototype.replace);
	var nativeExec = RegExp.prototype.exec;
	var patchedExec = nativeExec;
	var charAt$2 = functionUncurryThis(''.charAt);
	var indexOf$1 = functionUncurryThis(''.indexOf);
	var replace$1 = functionUncurryThis(''.replace);
	var stringSlice$2 = functionUncurryThis(''.slice);

	var UPDATES_LAST_INDEX_WRONG = function () {
	  var re1 = /a/;
	  var re2 = /b*/g;
	  functionCall(nativeExec, re1, 'a');
	  functionCall(nativeExec, re2, 'a');
	  return re1.lastIndex !== 0 || re2.lastIndex !== 0;
	}();

	var UNSUPPORTED_Y$1 = regexpStickyHelpers.BROKEN_CARET; // nonparticipating capturing group, copied from es5-shim's String#split patch.

	var NPCG_INCLUDED = /()??/.exec('')[1] !== undefined;
	var PATCH = UPDATES_LAST_INDEX_WRONG || NPCG_INCLUDED || UNSUPPORTED_Y$1 || regexpUnsupportedDotAll || regexpUnsupportedNcg;

	if (PATCH) {
	  patchedExec = function exec(string) {
	    var re = this;
	    var state = getInternalState$1(re);
	    var str = toString_1(string);
	    var raw = state.raw;
	    var result, reCopy, lastIndex, match, i, object, group;

	    if (raw) {
	      raw.lastIndex = re.lastIndex;
	      result = functionCall(patchedExec, raw, str);
	      re.lastIndex = raw.lastIndex;
	      return result;
	    }

	    var groups = state.groups;
	    var sticky = UNSUPPORTED_Y$1 && re.sticky;
	    var flags = functionCall(regexpFlags, re);
	    var source = re.source;
	    var charsAdded = 0;
	    var strCopy = str;

	    if (sticky) {
	      flags = replace$1(flags, 'y', '');

	      if (indexOf$1(flags, 'g') === -1) {
	        flags += 'g';
	      }

	      strCopy = stringSlice$2(str, re.lastIndex); // Support anchored sticky behavior.

	      if (re.lastIndex > 0 && (!re.multiline || re.multiline && charAt$2(str, re.lastIndex - 1) !== '\n')) {
	        source = '(?: ' + source + ')';
	        strCopy = ' ' + strCopy;
	        charsAdded++;
	      } // ^(? + rx + ) is needed, in combination with some str slicing, to
	      // simulate the 'y' flag.


	      reCopy = new RegExp('^(?:' + source + ')', flags);
	    }

	    if (NPCG_INCLUDED) {
	      reCopy = new RegExp('^' + source + '$(?!\\s)', flags);
	    }

	    if (UPDATES_LAST_INDEX_WRONG) lastIndex = re.lastIndex;
	    match = functionCall(nativeExec, sticky ? reCopy : re, strCopy);

	    if (sticky) {
	      if (match) {
	        match.input = stringSlice$2(match.input, charsAdded);
	        match[0] = stringSlice$2(match[0], charsAdded);
	        match.index = re.lastIndex;
	        re.lastIndex += match[0].length;
	      } else re.lastIndex = 0;
	    } else if (UPDATES_LAST_INDEX_WRONG && match) {
	      re.lastIndex = re.global ? match.index + match[0].length : lastIndex;
	    }

	    if (NPCG_INCLUDED && match && match.length > 1) {
	      // Fix browsers whose `exec` methods don't consistently return `undefined`
	      // for NPCG, like IE8. NOTE: This doesn' work for /(.?)?/
	      functionCall(nativeReplace, match[0], reCopy, function () {
	        for (i = 1; i < arguments.length - 2; i++) {
	          if (arguments[i] === undefined) match[i] = undefined;
	        }
	      });
	    }

	    if (match && groups) {
	      match.groups = object = objectCreate(null);

	      for (i = 0; i < groups.length; i++) {
	        group = groups[i];
	        object[group[0]] = match[group[1]];
	      }
	    }

	    return match;
	  };
	}

	var regexpExec = patchedExec;

	var TypeError$d = global_1.TypeError; // `RegExpExec` abstract operation
	// https://tc39.es/ecma262/#sec-regexpexec

	var regexpExecAbstract = function (R, S) {
	  var exec = R.exec;

	  if (isCallable(exec)) {
	    var result = functionCall(exec, R, S);
	    if (result !== null) anObject(result);
	    return result;
	  }

	  if (classofRaw(R) === 'RegExp') return functionCall(regexpExec, R, S);
	  throw TypeError$d('RegExp#exec called on incompatible receiver');
	};

	/* eslint-disable es/no-string-prototype-matchall -- safe */













































	var MATCH_ALL = wellKnownSymbol('matchAll');
	var REGEXP_STRING = 'RegExp String';
	var REGEXP_STRING_ITERATOR = REGEXP_STRING + ' Iterator';
	var setInternalState$1 = internalState.set;
	var getInternalState$2 = internalState.getterFor(REGEXP_STRING_ITERATOR);
	var RegExpPrototype = RegExp.prototype;
	var TypeError$e = global_1.TypeError;
	var getFlags = functionUncurryThis(regexpFlags);
	var stringIndexOf = functionUncurryThis(''.indexOf);
	var un$MatchAll = functionUncurryThis(''.matchAll);
	var WORKS_WITH_NON_GLOBAL_REGEX = !!un$MatchAll && !fails(function () {
	  un$MatchAll('a', /./);
	});
	var $RegExpStringIterator = createIteratorConstructor(function RegExpStringIterator(regexp, string, $global, fullUnicode) {
	  setInternalState$1(this, {
	    type: REGEXP_STRING_ITERATOR,
	    regexp: regexp,
	    string: string,
	    global: $global,
	    unicode: fullUnicode,
	    done: false
	  });
	}, REGEXP_STRING, function next() {
	  var state = getInternalState$2(this);
	  if (state.done) return {
	    value: undefined,
	    done: true
	  };
	  var R = state.regexp;
	  var S = state.string;
	  var match = regexpExecAbstract(R, S);
	  if (match === null) return {
	    value: undefined,
	    done: state.done = true
	  };

	  if (state.global) {
	    if (toString_1(match[0]) === '') R.lastIndex = advanceStringIndex(S, toLength(R.lastIndex), state.unicode);
	    return {
	      value: match,
	      done: false
	    };
	  }

	  state.done = true;
	  return {
	    value: match,
	    done: false
	  };
	});

	var $matchAll = function (string) {
	  var R = anObject(this);
	  var S = toString_1(string);
	  var C, flagsValue, flags, matcher, $global, fullUnicode;
	  C = speciesConstructor(R, RegExp);
	  flagsValue = R.flags;

	  if (flagsValue === undefined && objectIsPrototypeOf(RegExpPrototype, R) && !('flags' in RegExpPrototype)) {
	    flagsValue = getFlags(R);
	  }

	  flags = flagsValue === undefined ? '' : toString_1(flagsValue);
	  matcher = new C(C === RegExp ? R.source : R, flags);
	  $global = !!~stringIndexOf(flags, 'g');
	  fullUnicode = !!~stringIndexOf(flags, 'u');
	  matcher.lastIndex = toLength(R.lastIndex);
	  return new $RegExpStringIterator(matcher, S, $global, fullUnicode);
	}; // `String.prototype.matchAll` method
	// https://tc39.es/ecma262/#sec-string.prototype.matchall


	_export({
	  target: 'String',
	  proto: true,
	  forced: WORKS_WITH_NON_GLOBAL_REGEX
	}, {
	  matchAll: function matchAll(regexp) {
	    var O = requireObjectCoercible(this);
	    var flags, S, matcher, rx;

	    if (regexp != null) {
	      if (isRegexp(regexp)) {
	        flags = toString_1(requireObjectCoercible('flags' in RegExpPrototype ? regexp.flags : getFlags(regexp)));
	        if (!~stringIndexOf(flags, 'g')) throw TypeError$e('`.matchAll` does not allow non-global regexes');
	      }

	      if (WORKS_WITH_NON_GLOBAL_REGEX) return un$MatchAll(O, regexp);
	      matcher = getMethod(regexp, MATCH_ALL);
	      if (matcher === undefined && isPure && classofRaw(regexp) == 'RegExp') matcher = $matchAll;
	      if (matcher) return functionCall(matcher, regexp, O);
	    } else if (WORKS_WITH_NON_GLOBAL_REGEX) return un$MatchAll(O, regexp);

	    S = toString_1(O);
	    rx = new RegExp(regexp, 'g');
	    return isPure ? functionCall($matchAll, rx, S) : rx[MATCH_ALL](S);
	  }
	});
	isPure || MATCH_ALL in RegExpPrototype || redefine(RegExpPrototype, MATCH_ALL, $matchAll);

	var floor$2 = Math.floor;
	var charAt$3 = functionUncurryThis(''.charAt);
	var replace$2 = functionUncurryThis(''.replace);
	var stringSlice$3 = functionUncurryThis(''.slice);
	var SUBSTITUTION_SYMBOLS = /\$([$&'`]|\d{1,2}|<[^>]*>)/g;
	var SUBSTITUTION_SYMBOLS_NO_NAMED = /\$([$&'`]|\d{1,2})/g; // `GetSubstitution` abstract operation
	// https://tc39.es/ecma262/#sec-getsubstitution

	var getSubstitution = function (matched, str, position, captures, namedCaptures, replacement) {
	  var tailPos = position + matched.length;
	  var m = captures.length;
	  var symbols = SUBSTITUTION_SYMBOLS_NO_NAMED;

	  if (namedCaptures !== undefined) {
	    namedCaptures = toObject(namedCaptures);
	    symbols = SUBSTITUTION_SYMBOLS;
	  }

	  return replace$2(replacement, symbols, function (match, ch) {
	    var capture;

	    switch (charAt$3(ch, 0)) {
	      case '$':
	        return '$';

	      case '&':
	        return matched;

	      case '`':
	        return stringSlice$3(str, 0, position);

	      case "'":
	        return stringSlice$3(str, tailPos);

	      case '<':
	        capture = namedCaptures[stringSlice$3(ch, 1, -1)];
	        break;

	      default:
	        // \d\d?
	        var n = +ch;
	        if (n === 0) return match;

	        if (n > m) {
	          var f = floor$2(n / 10);
	          if (f === 0) return match;
	          if (f <= m) return captures[f - 1] === undefined ? charAt$3(ch, 1) : captures[f - 1] + charAt$3(ch, 1);
	          return match;
	        }

	        capture = captures[n - 1];
	    }

	    return capture === undefined ? '' : capture;
	  });
	};

	var REPLACE = wellKnownSymbol('replace');
	var RegExpPrototype$1 = RegExp.prototype;
	var TypeError$f = global_1.TypeError;
	var getFlags$1 = functionUncurryThis(regexpFlags);
	var indexOf$2 = functionUncurryThis(''.indexOf);
	var replace$3 = functionUncurryThis(''.replace);
	var stringSlice$4 = functionUncurryThis(''.slice);
	var max$2 = Math.max;

	var stringIndexOf$1 = function (string, searchValue, fromIndex) {
	  if (fromIndex > string.length) return -1;
	  if (searchValue === '') return fromIndex;
	  return indexOf$2(string, searchValue, fromIndex);
	}; // `String.prototype.replaceAll` method
	// https://tc39.es/ecma262/#sec-string.prototype.replaceall


	_export({
	  target: 'String',
	  proto: true
	}, {
	  replaceAll: function replaceAll(searchValue, replaceValue) {
	    var O = requireObjectCoercible(this);
	    var IS_REG_EXP, flags, replacer, string, searchString, functionalReplace, searchLength, advanceBy, replacement;
	    var position = 0;
	    var endOfLastMatch = 0;
	    var result = '';

	    if (searchValue != null) {
	      IS_REG_EXP = isRegexp(searchValue);

	      if (IS_REG_EXP) {
	        flags = toString_1(requireObjectCoercible('flags' in RegExpPrototype$1 ? searchValue.flags : getFlags$1(searchValue)));
	        if (!~indexOf$2(flags, 'g')) throw TypeError$f('`.replaceAll` does not allow non-global regexes');
	      }

	      replacer = getMethod(searchValue, REPLACE);

	      if (replacer) {
	        return functionCall(replacer, searchValue, O, replaceValue);
	      } else if (isPure && IS_REG_EXP) {
	        return replace$3(toString_1(O), searchValue, replaceValue);
	      }
	    }

	    string = toString_1(O);
	    searchString = toString_1(searchValue);
	    functionalReplace = isCallable(replaceValue);
	    if (!functionalReplace) replaceValue = toString_1(replaceValue);
	    searchLength = searchString.length;
	    advanceBy = max$2(1, searchLength);
	    position = stringIndexOf$1(string, searchString, 0);

	    while (position !== -1) {
	      replacement = functionalReplace ? toString_1(replaceValue(searchString, position, string)) : getSubstitution(searchString, string, position, [], undefined, replaceValue);
	      result += stringSlice$4(string, endOfLastMatch, position) + replacement;
	      endOfLastMatch = position + searchLength;
	      position = stringIndexOf$1(string, searchString, position + advanceBy);
	    }

	    if (endOfLastMatch < string.length) {
	      result += stringSlice$4(string, endOfLastMatch);
	    }

	    return result;
	  }
	});

	var ITERATOR$3 = wellKnownSymbol('iterator');
	var SAFE_CLOSING = false;

	var checkCorrectnessOfIteration = function (exec, SKIP_CLOSING) {
	  if (!SKIP_CLOSING && !SAFE_CLOSING) return false;
	  var ITERATION_SUPPORT = false;

	  try {
	    var object = {};

	    object[ITERATOR$3] = function () {
	      return {
	        next: function () {
	          return {
	            done: ITERATION_SUPPORT = true
	          };
	        }
	      };
	    };

	    exec(object);
	  } catch (error) {
	    /* empty */
	  }

	  return ITERATION_SUPPORT;
	};

	var defineProperty$3 = objectDefineProperty.f;











	var Int8Array = global_1.Int8Array;
	var Int8ArrayPrototype = Int8Array && Int8Array.prototype;
	var Uint8ClampedArray = global_1.Uint8ClampedArray;
	var Uint8ClampedArrayPrototype = Uint8ClampedArray && Uint8ClampedArray.prototype;
	var TypedArray = Int8Array && objectGetPrototypeOf(Int8Array);
	var TypedArrayPrototype = Int8ArrayPrototype && objectGetPrototypeOf(Int8ArrayPrototype);
	var ObjectPrototype$2 = Object.prototype;
	var TypeError$g = global_1.TypeError;
	var TO_STRING_TAG$4 = wellKnownSymbol('toStringTag');
	var TYPED_ARRAY_TAG = uid('TYPED_ARRAY_TAG');
	var TYPED_ARRAY_CONSTRUCTOR = uid('TYPED_ARRAY_CONSTRUCTOR'); // Fixing native typed arrays in Opera Presto crashes the browser, see #595

	var NATIVE_ARRAY_BUFFER_VIEWS = arrayBufferNative && !!objectSetPrototypeOf && classof(global_1.opera) !== 'Opera';
	var TYPED_ARRAY_TAG_REQIRED = false;
	var NAME, Constructor, Prototype;
	var TypedArrayConstructorsList = {
	  Int8Array: 1,
	  Uint8Array: 1,
	  Uint8ClampedArray: 1,
	  Int16Array: 2,
	  Uint16Array: 2,
	  Int32Array: 4,
	  Uint32Array: 4,
	  Float32Array: 4,
	  Float64Array: 8
	};
	var BigIntArrayConstructorsList = {
	  BigInt64Array: 8,
	  BigUint64Array: 8
	};

	var isView = function isView(it) {
	  if (!isObject(it)) return false;
	  var klass = classof(it);
	  return klass === 'DataView' || hasOwnProperty_1(TypedArrayConstructorsList, klass) || hasOwnProperty_1(BigIntArrayConstructorsList, klass);
	};

	var isTypedArray = function (it) {
	  if (!isObject(it)) return false;
	  var klass = classof(it);
	  return hasOwnProperty_1(TypedArrayConstructorsList, klass) || hasOwnProperty_1(BigIntArrayConstructorsList, klass);
	};

	var aTypedArray = function (it) {
	  if (isTypedArray(it)) return it;
	  throw TypeError$g('Target is not a typed array');
	};

	var aTypedArrayConstructor = function (C) {
	  if (isCallable(C) && (!objectSetPrototypeOf || objectIsPrototypeOf(TypedArray, C))) return C;
	  throw TypeError$g(tryToString(C) + ' is not a typed array constructor');
	};

	var exportTypedArrayMethod = function (KEY, property, forced) {
	  if (!descriptors) return;
	  if (forced) for (var ARRAY in TypedArrayConstructorsList) {
	    var TypedArrayConstructor = global_1[ARRAY];
	    if (TypedArrayConstructor && hasOwnProperty_1(TypedArrayConstructor.prototype, KEY)) try {
	      delete TypedArrayConstructor.prototype[KEY];
	    } catch (error) {
	      /* empty */
	    }
	  }

	  if (!TypedArrayPrototype[KEY] || forced) {
	    redefine(TypedArrayPrototype, KEY, forced ? property : NATIVE_ARRAY_BUFFER_VIEWS && Int8ArrayPrototype[KEY] || property);
	  }
	};

	var exportTypedArrayStaticMethod = function (KEY, property, forced) {
	  var ARRAY, TypedArrayConstructor;
	  if (!descriptors) return;

	  if (objectSetPrototypeOf) {
	    if (forced) for (ARRAY in TypedArrayConstructorsList) {
	      TypedArrayConstructor = global_1[ARRAY];
	      if (TypedArrayConstructor && hasOwnProperty_1(TypedArrayConstructor, KEY)) try {
	        delete TypedArrayConstructor[KEY];
	      } catch (error) {
	        /* empty */
	      }
	    }

	    if (!TypedArray[KEY] || forced) {
	      // V8 ~ Chrome 49-50 `%TypedArray%` methods are non-writable non-configurable
	      try {
	        return redefine(TypedArray, KEY, forced ? property : NATIVE_ARRAY_BUFFER_VIEWS && TypedArray[KEY] || property);
	      } catch (error) {
	        /* empty */
	      }
	    } else return;
	  }

	  for (ARRAY in TypedArrayConstructorsList) {
	    TypedArrayConstructor = global_1[ARRAY];

	    if (TypedArrayConstructor && (!TypedArrayConstructor[KEY] || forced)) {
	      redefine(TypedArrayConstructor, KEY, property);
	    }
	  }
	};

	for (NAME in TypedArrayConstructorsList) {
	  Constructor = global_1[NAME];
	  Prototype = Constructor && Constructor.prototype;
	  if (Prototype) createNonEnumerableProperty(Prototype, TYPED_ARRAY_CONSTRUCTOR, Constructor);else NATIVE_ARRAY_BUFFER_VIEWS = false;
	}

	for (NAME in BigIntArrayConstructorsList) {
	  Constructor = global_1[NAME];
	  Prototype = Constructor && Constructor.prototype;
	  if (Prototype) createNonEnumerableProperty(Prototype, TYPED_ARRAY_CONSTRUCTOR, Constructor);
	} // WebKit bug - typed arrays constructors prototype is Object.prototype


	if (!NATIVE_ARRAY_BUFFER_VIEWS || !isCallable(TypedArray) || TypedArray === Function.prototype) {
	  // eslint-disable-next-line no-shadow -- safe
	  TypedArray = function TypedArray() {
	    throw TypeError$g('Incorrect invocation');
	  };

	  if (NATIVE_ARRAY_BUFFER_VIEWS) for (NAME in TypedArrayConstructorsList) {
	    if (global_1[NAME]) objectSetPrototypeOf(global_1[NAME], TypedArray);
	  }
	}

	if (!NATIVE_ARRAY_BUFFER_VIEWS || !TypedArrayPrototype || TypedArrayPrototype === ObjectPrototype$2) {
	  TypedArrayPrototype = TypedArray.prototype;
	  if (NATIVE_ARRAY_BUFFER_VIEWS) for (NAME in TypedArrayConstructorsList) {
	    if (global_1[NAME]) objectSetPrototypeOf(global_1[NAME].prototype, TypedArrayPrototype);
	  }
	} // WebKit bug - one more object in Uint8ClampedArray prototype chain


	if (NATIVE_ARRAY_BUFFER_VIEWS && objectGetPrototypeOf(Uint8ClampedArrayPrototype) !== TypedArrayPrototype) {
	  objectSetPrototypeOf(Uint8ClampedArrayPrototype, TypedArrayPrototype);
	}

	if (descriptors && !hasOwnProperty_1(TypedArrayPrototype, TO_STRING_TAG$4)) {
	  TYPED_ARRAY_TAG_REQIRED = true;
	  defineProperty$3(TypedArrayPrototype, TO_STRING_TAG$4, {
	    get: function () {
	      return isObject(this) ? this[TYPED_ARRAY_TAG] : undefined;
	    }
	  });

	  for (NAME in TypedArrayConstructorsList) if (global_1[NAME]) {
	    createNonEnumerableProperty(global_1[NAME], TYPED_ARRAY_TAG, NAME);
	  }
	}

	var arrayBufferViewCore = {
	  NATIVE_ARRAY_BUFFER_VIEWS: NATIVE_ARRAY_BUFFER_VIEWS,
	  TYPED_ARRAY_CONSTRUCTOR: TYPED_ARRAY_CONSTRUCTOR,
	  TYPED_ARRAY_TAG: TYPED_ARRAY_TAG_REQIRED && TYPED_ARRAY_TAG,
	  aTypedArray: aTypedArray,
	  aTypedArrayConstructor: aTypedArrayConstructor,
	  exportTypedArrayMethod: exportTypedArrayMethod,
	  exportTypedArrayStaticMethod: exportTypedArrayStaticMethod,
	  isView: isView,
	  isTypedArray: isTypedArray,
	  TypedArray: TypedArray,
	  TypedArrayPrototype: TypedArrayPrototype
	};

	/* eslint-disable no-new -- required for testing */






	var NATIVE_ARRAY_BUFFER_VIEWS$1 = arrayBufferViewCore.NATIVE_ARRAY_BUFFER_VIEWS;

	var ArrayBuffer$2 = global_1.ArrayBuffer;
	var Int8Array$1 = global_1.Int8Array;
	var typedArrayConstructorsRequireWrappers = !NATIVE_ARRAY_BUFFER_VIEWS$1 || !fails(function () {
	  Int8Array$1(1);
	}) || !fails(function () {
	  new Int8Array$1(-1);
	}) || !checkCorrectnessOfIteration(function (iterable) {
	  new Int8Array$1();
	  new Int8Array$1(null);
	  new Int8Array$1(1.5);
	  new Int8Array$1(iterable);
	}, true) || fails(function () {
	  // Safari (11+) bug - a reason why even Safari 13 should load a typed array polyfill
	  return new Int8Array$1(new ArrayBuffer$2(2), 1, undefined).length !== 1;
	});

	var floor$3 = Math.floor; // `IsIntegralNumber` abstract operation
	// https://tc39.es/ecma262/#sec-isintegralnumber
	// eslint-disable-next-line es/no-number-isinteger -- safe

	var isIntegralNumber = Number.isInteger || function isInteger(it) {
	  return !isObject(it) && isFinite(it) && floor$3(it) === it;
	};

	var RangeError$2 = global_1.RangeError;

	var toPositiveInteger = function (it) {
	  var result = toIntegerOrInfinity(it);
	  if (result < 0) throw RangeError$2("The argument can't be less than 0");
	  return result;
	};

	var RangeError$3 = global_1.RangeError;

	var toOffset = function (it, BYTES) {
	  var offset = toPositiveInteger(it);
	  if (offset % BYTES) throw RangeError$3('Wrong offset');
	  return offset;
	};

	var aTypedArrayConstructor$1 = arrayBufferViewCore.aTypedArrayConstructor;

	var typedArrayFrom = function from(source
	/* , mapfn, thisArg */
	) {
	  var C = aConstructor(this);
	  var O = toObject(source);
	  var argumentsLength = arguments.length;
	  var mapfn = argumentsLength > 1 ? arguments[1] : undefined;
	  var mapping = mapfn !== undefined;
	  var iteratorMethod = getIteratorMethod(O);
	  var i, length, result, step, iterator, next;

	  if (iteratorMethod && !isArrayIteratorMethod(iteratorMethod)) {
	    iterator = getIterator(O, iteratorMethod);
	    next = iterator.next;
	    O = [];

	    while (!(step = functionCall(next, iterator)).done) {
	      O.push(step.value);
	    }
	  }

	  if (mapping && argumentsLength > 2) {
	    mapfn = functionBindContext(mapfn, arguments[2]);
	  }

	  length = lengthOfArrayLike(O);
	  result = new (aTypedArrayConstructor$1(C))(length);

	  for (i = 0; length > i; i++) {
	    result[i] = mapping ? mapfn(O[i], i) : O[i];
	  }

	  return result;
	};

	var SPECIES$1 = wellKnownSymbol('species');
	var Array$4 = global_1.Array; // a part of `ArraySpeciesCreate` abstract operation
	// https://tc39.es/ecma262/#sec-arrayspeciescreate

	var arraySpeciesConstructor = function (originalArray) {
	  var C;

	  if (isArray(originalArray)) {
	    C = originalArray.constructor; // cross-realm fallback

	    if (isConstructor(C) && (C === Array$4 || isArray(C.prototype))) C = undefined;else if (isObject(C)) {
	      C = C[SPECIES$1];
	      if (C === null) C = undefined;
	    }
	  }

	  return C === undefined ? Array$4 : C;
	};

	// `ArraySpeciesCreate` abstract operation
	// https://tc39.es/ecma262/#sec-arrayspeciescreate


	var arraySpeciesCreate = function (originalArray, length) {
	  return new (arraySpeciesConstructor(originalArray))(length === 0 ? 0 : length);
	};

	var push$2 = functionUncurryThis([].push); // `Array.prototype.{ forEach, map, filter, some, every, find, findIndex, filterReject }` methods implementation

	var createMethod$2 = function (TYPE) {
	  var IS_MAP = TYPE == 1;
	  var IS_FILTER = TYPE == 2;
	  var IS_SOME = TYPE == 3;
	  var IS_EVERY = TYPE == 4;
	  var IS_FIND_INDEX = TYPE == 6;
	  var IS_FILTER_REJECT = TYPE == 7;
	  var NO_HOLES = TYPE == 5 || IS_FIND_INDEX;
	  return function ($this, callbackfn, that, specificCreate) {
	    var O = toObject($this);
	    var self = indexedObject(O);
	    var boundFunction = functionBindContext(callbackfn, that);
	    var length = lengthOfArrayLike(self);
	    var index = 0;
	    var create = specificCreate || arraySpeciesCreate;
	    var target = IS_MAP ? create($this, length) : IS_FILTER || IS_FILTER_REJECT ? create($this, 0) : undefined;
	    var value, result;

	    for (; length > index; index++) if (NO_HOLES || index in self) {
	      value = self[index];
	      result = boundFunction(value, index, O);

	      if (TYPE) {
	        if (IS_MAP) target[index] = result; // map
	        else if (result) switch (TYPE) {
	            case 3:
	              return true;
	            // some

	            case 5:
	              return value;
	            // find

	            case 6:
	              return index;
	            // findIndex

	            case 2:
	              push$2(target, value);
	            // filter
	          } else switch (TYPE) {
	            case 4:
	              return false;
	            // every

	            case 7:
	              push$2(target, value);
	            // filterReject
	          }
	      }
	    }

	    return IS_FIND_INDEX ? -1 : IS_SOME || IS_EVERY ? IS_EVERY : target;
	  };
	};

	var arrayIteration = {
	  // `Array.prototype.forEach` method
	  // https://tc39.es/ecma262/#sec-array.prototype.foreach
	  forEach: createMethod$2(0),
	  // `Array.prototype.map` method
	  // https://tc39.es/ecma262/#sec-array.prototype.map
	  map: createMethod$2(1),
	  // `Array.prototype.filter` method
	  // https://tc39.es/ecma262/#sec-array.prototype.filter
	  filter: createMethod$2(2),
	  // `Array.prototype.some` method
	  // https://tc39.es/ecma262/#sec-array.prototype.some
	  some: createMethod$2(3),
	  // `Array.prototype.every` method
	  // https://tc39.es/ecma262/#sec-array.prototype.every
	  every: createMethod$2(4),
	  // `Array.prototype.find` method
	  // https://tc39.es/ecma262/#sec-array.prototype.find
	  find: createMethod$2(5),
	  // `Array.prototype.findIndex` method
	  // https://tc39.es/ecma262/#sec-array.prototype.findIndex
	  findIndex: createMethod$2(6),
	  // `Array.prototype.filterReject` method
	  // https://github.com/tc39/proposal-array-filtering
	  filterReject: createMethod$2(7)
	};

	var SPECIES$2 = wellKnownSymbol('species');

	var setSpecies = function (CONSTRUCTOR_NAME) {
	  var Constructor = getBuiltIn(CONSTRUCTOR_NAME);
	  var defineProperty = objectDefineProperty.f;

	  if (descriptors && Constructor && !Constructor[SPECIES$2]) {
	    defineProperty(Constructor, SPECIES$2, {
	      configurable: true,
	      get: function () {
	        return this;
	      }
	    });
	  }
	};

	// makes subclassing work correct for wrapped built-ins


	var inheritIfRequired = function ($this, dummy, Wrapper) {
	  var NewTarget, NewTargetPrototype;
	  if ( // it can work only with native `setPrototypeOf`
	  objectSetPrototypeOf && // we haven't completely correct pre-ES6 way for getting `new.target`, so use this
	  isCallable(NewTarget = dummy.constructor) && NewTarget !== Wrapper && isObject(NewTargetPrototype = NewTarget.prototype) && NewTargetPrototype !== Wrapper.prototype) objectSetPrototypeOf($this, NewTargetPrototype);
	  return $this;
	};

	var typedArrayConstructor = createCommonjsModule(function (module) {













































	var getOwnPropertyNames = objectGetOwnPropertyNames.f;



	var forEach = arrayIteration.forEach;











	var getInternalState = internalState.get;
	var setInternalState = internalState.set;
	var nativeDefineProperty = objectDefineProperty.f;
	var nativeGetOwnPropertyDescriptor = objectGetOwnPropertyDescriptor.f;
	var round = Math.round;
	var RangeError = global_1.RangeError;
	var ArrayBuffer = arrayBuffer.ArrayBuffer;
	var ArrayBufferPrototype = ArrayBuffer.prototype;
	var DataView = arrayBuffer.DataView;
	var NATIVE_ARRAY_BUFFER_VIEWS = arrayBufferViewCore.NATIVE_ARRAY_BUFFER_VIEWS;
	var TYPED_ARRAY_CONSTRUCTOR = arrayBufferViewCore.TYPED_ARRAY_CONSTRUCTOR;
	var TYPED_ARRAY_TAG = arrayBufferViewCore.TYPED_ARRAY_TAG;
	var TypedArray = arrayBufferViewCore.TypedArray;
	var TypedArrayPrototype = arrayBufferViewCore.TypedArrayPrototype;
	var aTypedArrayConstructor = arrayBufferViewCore.aTypedArrayConstructor;
	var isTypedArray = arrayBufferViewCore.isTypedArray;
	var BYTES_PER_ELEMENT = 'BYTES_PER_ELEMENT';
	var WRONG_LENGTH = 'Wrong length';

	var fromList = function (C, list) {
	  aTypedArrayConstructor(C);
	  var index = 0;
	  var length = list.length;
	  var result = new C(length);

	  while (length > index) result[index] = list[index++];

	  return result;
	};

	var addGetter = function (it, key) {
	  nativeDefineProperty(it, key, {
	    get: function () {
	      return getInternalState(this)[key];
	    }
	  });
	};

	var isArrayBuffer = function (it) {
	  var klass;
	  return objectIsPrototypeOf(ArrayBufferPrototype, it) || (klass = classof(it)) == 'ArrayBuffer' || klass == 'SharedArrayBuffer';
	};

	var isTypedArrayIndex = function (target, key) {
	  return isTypedArray(target) && !isSymbol(key) && key in target && isIntegralNumber(+key) && key >= 0;
	};

	var wrappedGetOwnPropertyDescriptor = function getOwnPropertyDescriptor(target, key) {
	  key = toPropertyKey(key);
	  return isTypedArrayIndex(target, key) ? createPropertyDescriptor(2, target[key]) : nativeGetOwnPropertyDescriptor(target, key);
	};

	var wrappedDefineProperty = function defineProperty(target, key, descriptor) {
	  key = toPropertyKey(key);

	  if (isTypedArrayIndex(target, key) && isObject(descriptor) && hasOwnProperty_1(descriptor, 'value') && !hasOwnProperty_1(descriptor, 'get') && !hasOwnProperty_1(descriptor, 'set') // TODO: add validation descriptor w/o calling accessors
	  && !descriptor.configurable && (!hasOwnProperty_1(descriptor, 'writable') || descriptor.writable) && (!hasOwnProperty_1(descriptor, 'enumerable') || descriptor.enumerable)) {
	    target[key] = descriptor.value;
	    return target;
	  }

	  return nativeDefineProperty(target, key, descriptor);
	};

	if (descriptors) {
	  if (!NATIVE_ARRAY_BUFFER_VIEWS) {
	    objectGetOwnPropertyDescriptor.f = wrappedGetOwnPropertyDescriptor;
	    objectDefineProperty.f = wrappedDefineProperty;
	    addGetter(TypedArrayPrototype, 'buffer');
	    addGetter(TypedArrayPrototype, 'byteOffset');
	    addGetter(TypedArrayPrototype, 'byteLength');
	    addGetter(TypedArrayPrototype, 'length');
	  }

	  _export({
	    target: 'Object',
	    stat: true,
	    forced: !NATIVE_ARRAY_BUFFER_VIEWS
	  }, {
	    getOwnPropertyDescriptor: wrappedGetOwnPropertyDescriptor,
	    defineProperty: wrappedDefineProperty
	  });

	  module.exports = function (TYPE, wrapper, CLAMPED) {
	    var BYTES = TYPE.match(/\d+$/)[0] / 8;
	    var CONSTRUCTOR_NAME = TYPE + (CLAMPED ? 'Clamped' : '') + 'Array';
	    var GETTER = 'get' + TYPE;
	    var SETTER = 'set' + TYPE;
	    var NativeTypedArrayConstructor = global_1[CONSTRUCTOR_NAME];
	    var TypedArrayConstructor = NativeTypedArrayConstructor;
	    var TypedArrayConstructorPrototype = TypedArrayConstructor && TypedArrayConstructor.prototype;
	    var exported = {};

	    var getter = function (that, index) {
	      var data = getInternalState(that);
	      return data.view[GETTER](index * BYTES + data.byteOffset, true);
	    };

	    var setter = function (that, index, value) {
	      var data = getInternalState(that);
	      if (CLAMPED) value = (value = round(value)) < 0 ? 0 : value > 0xFF ? 0xFF : value & 0xFF;
	      data.view[SETTER](index * BYTES + data.byteOffset, value, true);
	    };

	    var addElement = function (that, index) {
	      nativeDefineProperty(that, index, {
	        get: function () {
	          return getter(this, index);
	        },
	        set: function (value) {
	          return setter(this, index, value);
	        },
	        enumerable: true
	      });
	    };

	    if (!NATIVE_ARRAY_BUFFER_VIEWS) {
	      TypedArrayConstructor = wrapper(function (that, data, offset, $length) {
	        anInstance(that, TypedArrayConstructorPrototype);
	        var index = 0;
	        var byteOffset = 0;
	        var buffer, byteLength, length;

	        if (!isObject(data)) {
	          length = toIndex(data);
	          byteLength = length * BYTES;
	          buffer = new ArrayBuffer(byteLength);
	        } else if (isArrayBuffer(data)) {
	          buffer = data;
	          byteOffset = toOffset(offset, BYTES);
	          var $len = data.byteLength;

	          if ($length === undefined) {
	            if ($len % BYTES) throw RangeError(WRONG_LENGTH);
	            byteLength = $len - byteOffset;
	            if (byteLength < 0) throw RangeError(WRONG_LENGTH);
	          } else {
	            byteLength = toLength($length) * BYTES;
	            if (byteLength + byteOffset > $len) throw RangeError(WRONG_LENGTH);
	          }

	          length = byteLength / BYTES;
	        } else if (isTypedArray(data)) {
	          return fromList(TypedArrayConstructor, data);
	        } else {
	          return functionCall(typedArrayFrom, TypedArrayConstructor, data);
	        }

	        setInternalState(that, {
	          buffer: buffer,
	          byteOffset: byteOffset,
	          byteLength: byteLength,
	          length: length,
	          view: new DataView(buffer)
	        });

	        while (index < length) addElement(that, index++);
	      });
	      if (objectSetPrototypeOf) objectSetPrototypeOf(TypedArrayConstructor, TypedArray);
	      TypedArrayConstructorPrototype = TypedArrayConstructor.prototype = objectCreate(TypedArrayPrototype);
	    } else if (typedArrayConstructorsRequireWrappers) {
	      TypedArrayConstructor = wrapper(function (dummy, data, typedArrayOffset, $length) {
	        anInstance(dummy, TypedArrayConstructorPrototype);
	        return inheritIfRequired(function () {
	          if (!isObject(data)) return new NativeTypedArrayConstructor(toIndex(data));
	          if (isArrayBuffer(data)) return $length !== undefined ? new NativeTypedArrayConstructor(data, toOffset(typedArrayOffset, BYTES), $length) : typedArrayOffset !== undefined ? new NativeTypedArrayConstructor(data, toOffset(typedArrayOffset, BYTES)) : new NativeTypedArrayConstructor(data);
	          if (isTypedArray(data)) return fromList(TypedArrayConstructor, data);
	          return functionCall(typedArrayFrom, TypedArrayConstructor, data);
	        }(), dummy, TypedArrayConstructor);
	      });
	      if (objectSetPrototypeOf) objectSetPrototypeOf(TypedArrayConstructor, TypedArray);
	      forEach(getOwnPropertyNames(NativeTypedArrayConstructor), function (key) {
	        if (!(key in TypedArrayConstructor)) {
	          createNonEnumerableProperty(TypedArrayConstructor, key, NativeTypedArrayConstructor[key]);
	        }
	      });
	      TypedArrayConstructor.prototype = TypedArrayConstructorPrototype;
	    }

	    if (TypedArrayConstructorPrototype.constructor !== TypedArrayConstructor) {
	      createNonEnumerableProperty(TypedArrayConstructorPrototype, 'constructor', TypedArrayConstructor);
	    }

	    createNonEnumerableProperty(TypedArrayConstructorPrototype, TYPED_ARRAY_CONSTRUCTOR, TypedArrayConstructor);

	    if (TYPED_ARRAY_TAG) {
	      createNonEnumerableProperty(TypedArrayConstructorPrototype, TYPED_ARRAY_TAG, CONSTRUCTOR_NAME);
	    }

	    exported[CONSTRUCTOR_NAME] = TypedArrayConstructor;
	    _export({
	      global: true,
	      forced: TypedArrayConstructor != NativeTypedArrayConstructor,
	      sham: !NATIVE_ARRAY_BUFFER_VIEWS
	    }, exported);

	    if (!(BYTES_PER_ELEMENT in TypedArrayConstructor)) {
	      createNonEnumerableProperty(TypedArrayConstructor, BYTES_PER_ELEMENT, BYTES);
	    }

	    if (!(BYTES_PER_ELEMENT in TypedArrayConstructorPrototype)) {
	      createNonEnumerableProperty(TypedArrayConstructorPrototype, BYTES_PER_ELEMENT, BYTES);
	    }

	    setSpecies(CONSTRUCTOR_NAME);
	  };
	} else module.exports = function () {
	  /* empty */
	};
	});

	// `Float32Array` constructor
	// https://tc39.es/ecma262/#sec-typedarray-objects


	typedArrayConstructor('Float32', function (init) {
	  return function Float32Array(data, byteOffset, length) {
	    return init(this, data, byteOffset, length);
	  };
	});

	// `Float64Array` constructor
	// https://tc39.es/ecma262/#sec-typedarray-objects


	typedArrayConstructor('Float64', function (init) {
	  return function Float64Array(data, byteOffset, length) {
	    return init(this, data, byteOffset, length);
	  };
	});

	// `Int8Array` constructor
	// https://tc39.es/ecma262/#sec-typedarray-objects


	typedArrayConstructor('Int8', function (init) {
	  return function Int8Array(data, byteOffset, length) {
	    return init(this, data, byteOffset, length);
	  };
	});

	// `Int16Array` constructor
	// https://tc39.es/ecma262/#sec-typedarray-objects


	typedArrayConstructor('Int16', function (init) {
	  return function Int16Array(data, byteOffset, length) {
	    return init(this, data, byteOffset, length);
	  };
	});

	// `Int32Array` constructor
	// https://tc39.es/ecma262/#sec-typedarray-objects


	typedArrayConstructor('Int32', function (init) {
	  return function Int32Array(data, byteOffset, length) {
	    return init(this, data, byteOffset, length);
	  };
	});

	// `Uint8Array` constructor
	// https://tc39.es/ecma262/#sec-typedarray-objects


	typedArrayConstructor('Uint8', function (init) {
	  return function Uint8Array(data, byteOffset, length) {
	    return init(this, data, byteOffset, length);
	  };
	});

	// `Uint8ClampedArray` constructor
	// https://tc39.es/ecma262/#sec-typedarray-objects


	typedArrayConstructor('Uint8', function (init) {
	  return function Uint8ClampedArray(data, byteOffset, length) {
	    return init(this, data, byteOffset, length);
	  };
	}, true);

	// `Uint16Array` constructor
	// https://tc39.es/ecma262/#sec-typedarray-objects


	typedArrayConstructor('Uint16', function (init) {
	  return function Uint16Array(data, byteOffset, length) {
	    return init(this, data, byteOffset, length);
	  };
	});

	// `Uint32Array` constructor
	// https://tc39.es/ecma262/#sec-typedarray-objects


	typedArrayConstructor('Uint32', function (init) {
	  return function Uint32Array(data, byteOffset, length) {
	    return init(this, data, byteOffset, length);
	  };
	});

	var aTypedArray$1 = arrayBufferViewCore.aTypedArray;
	var exportTypedArrayMethod$1 = arrayBufferViewCore.exportTypedArrayMethod; // `%TypedArray%.prototype.at` method
	// https://github.com/tc39/proposal-relative-indexing-method

	exportTypedArrayMethod$1('at', function at(index) {
	  var O = aTypedArray$1(this);
	  var len = lengthOfArrayLike(O);
	  var relativeIndex = toIntegerOrInfinity(index);
	  var k = relativeIndex >= 0 ? relativeIndex : len + relativeIndex;
	  return k < 0 || k >= len ? undefined : O[k];
	});

	var exportTypedArrayStaticMethod$1 = arrayBufferViewCore.exportTypedArrayStaticMethod;

	 // `%TypedArray%.from` method
	// https://tc39.es/ecma262/#sec-%typedarray%.from


	exportTypedArrayStaticMethod$1('from', typedArrayFrom, typedArrayConstructorsRequireWrappers);

	var aTypedArrayConstructor$2 = arrayBufferViewCore.aTypedArrayConstructor;
	var exportTypedArrayStaticMethod$2 = arrayBufferViewCore.exportTypedArrayStaticMethod; // `%TypedArray%.of` method
	// https://tc39.es/ecma262/#sec-%typedarray%.of

	exportTypedArrayStaticMethod$2('of', function of()
	/* ...items */
	{
	  var index = 0;
	  var length = arguments.length;
	  var result = new (aTypedArrayConstructor$2(this))(length);

	  while (length > index) result[index] = arguments[index++];

	  return result;
	}, typedArrayConstructorsRequireWrappers);

	var floor$4 = Math.floor;

	var mergeSort = function (array, comparefn) {
	  var length = array.length;
	  var middle = floor$4(length / 2);
	  return length < 8 ? insertionSort(array, comparefn) : merge(array, mergeSort(arraySliceSimple(array, 0, middle), comparefn), mergeSort(arraySliceSimple(array, middle), comparefn), comparefn);
	};

	var insertionSort = function (array, comparefn) {
	  var length = array.length;
	  var i = 1;
	  var element, j;

	  while (i < length) {
	    j = i;
	    element = array[i];

	    while (j && comparefn(array[j - 1], element) > 0) {
	      array[j] = array[--j];
	    }

	    if (j !== i++) array[j] = element;
	  }

	  return array;
	};

	var merge = function (array, left, right, comparefn) {
	  var llength = left.length;
	  var rlength = right.length;
	  var lindex = 0;
	  var rindex = 0;

	  while (lindex < llength || rindex < rlength) {
	    array[lindex + rindex] = lindex < llength && rindex < rlength ? comparefn(left[lindex], right[rindex]) <= 0 ? left[lindex++] : right[rindex++] : lindex < llength ? left[lindex++] : right[rindex++];
	  }

	  return array;
	};

	var arraySort = mergeSort;

	var firefox = engineUserAgent.match(/firefox\/(\d+)/i);
	var engineFfVersion = !!firefox && +firefox[1];

	var engineIsIeOrEdge = /MSIE|Trident/.test(engineUserAgent);

	var webkit = engineUserAgent.match(/AppleWebKit\/(\d+)\./);
	var engineWebkitVersion = !!webkit && +webkit[1];

	var Array$5 = global_1.Array;
	var aTypedArray$2 = arrayBufferViewCore.aTypedArray;
	var exportTypedArrayMethod$2 = arrayBufferViewCore.exportTypedArrayMethod;
	var Uint16Array = global_1.Uint16Array;
	var un$Sort = Uint16Array && functionUncurryThis(Uint16Array.prototype.sort); // WebKit

	var ACCEPT_INCORRECT_ARGUMENTS = !!un$Sort && !(fails(function () {
	  un$Sort(new Uint16Array(2), null);
	}) && fails(function () {
	  un$Sort(new Uint16Array(2), {});
	}));
	var STABLE_SORT = !!un$Sort && !fails(function () {
	  // feature detection can be too slow, so check engines versions
	  if (engineV8Version) return engineV8Version < 74;
	  if (engineFfVersion) return engineFfVersion < 67;
	  if (engineIsIeOrEdge) return true;
	  if (engineWebkitVersion) return engineWebkitVersion < 602;
	  var array = new Uint16Array(516);
	  var expected = Array$5(516);
	  var index, mod;

	  for (index = 0; index < 516; index++) {
	    mod = index % 4;
	    array[index] = 515 - index;
	    expected[index] = index - 2 * mod + 3;
	  }

	  un$Sort(array, function (a, b) {
	    return (a / 4 | 0) - (b / 4 | 0);
	  });

	  for (index = 0; index < 516; index++) {
	    if (array[index] !== expected[index]) return true;
	  }
	});

	var getSortCompare = function (comparefn) {
	  return function (x, y) {
	    if (comparefn !== undefined) return +comparefn(x, y) || 0; // eslint-disable-next-line no-self-compare -- NaN check

	    if (y !== y) return -1; // eslint-disable-next-line no-self-compare -- NaN check

	    if (x !== x) return 1;
	    if (x === 0 && y === 0) return 1 / x > 0 && 1 / y < 0 ? 1 : -1;
	    return x > y;
	  };
	}; // `%TypedArray%.prototype.sort` method
	// https://tc39.es/ecma262/#sec-%typedarray%.prototype.sort


	exportTypedArrayMethod$2('sort', function sort(comparefn) {
	  if (comparefn !== undefined) aCallable(comparefn);
	  if (STABLE_SORT) return un$Sort(this, comparefn);
	  return arraySort(aTypedArray$2(this), getSortCompare(comparefn));
	}, !STABLE_SORT || ACCEPT_INCORRECT_ARGUMENTS);

	// iterable DOM collections
	// flag - `iterable` interface - 'entries', 'keys', 'values', 'forEach' methods
	var domIterables = {
	  CSSRuleList: 0,
	  CSSStyleDeclaration: 0,
	  CSSValueList: 0,
	  ClientRectList: 0,
	  DOMRectList: 0,
	  DOMStringList: 0,
	  DOMTokenList: 1,
	  DataTransferItemList: 0,
	  FileList: 0,
	  HTMLAllCollection: 0,
	  HTMLCollection: 0,
	  HTMLFormElement: 0,
	  HTMLSelectElement: 0,
	  MediaList: 0,
	  MimeTypeArray: 0,
	  NamedNodeMap: 0,
	  NodeList: 1,
	  PaintRequestList: 0,
	  Plugin: 0,
	  PluginArray: 0,
	  SVGLengthList: 0,
	  SVGNumberList: 0,
	  SVGPathSegList: 0,
	  SVGPointList: 0,
	  SVGStringList: 0,
	  SVGTransformList: 0,
	  SourceBufferList: 0,
	  StyleSheetList: 0,
	  TextTrackCueList: 0,
	  TextTrackList: 0,
	  TouchList: 0
	};

	// in old WebKit versions, `element.classList` is not an instance of global `DOMTokenList`


	var classList = documentCreateElement('span').classList;
	var DOMTokenListPrototype = classList && classList.constructor && classList.constructor.prototype;
	var domTokenListPrototype = DOMTokenListPrototype === Object.prototype ? undefined : DOMTokenListPrototype;

	var PROPER_FUNCTION_NAME$1 = functionName.PROPER;
	var CONFIGURABLE_FUNCTION_NAME$1 = functionName.CONFIGURABLE;
	var IteratorPrototype$2 = iteratorsCore.IteratorPrototype;
	var BUGGY_SAFARI_ITERATORS$1 = iteratorsCore.BUGGY_SAFARI_ITERATORS;
	var ITERATOR$4 = wellKnownSymbol('iterator');
	var KEYS = 'keys';
	var VALUES = 'values';
	var ENTRIES = 'entries';

	var returnThis$1 = function () {
	  return this;
	};

	var defineIterator = function (Iterable, NAME, IteratorConstructor, next, DEFAULT, IS_SET, FORCED) {
	  createIteratorConstructor(IteratorConstructor, NAME, next);

	  var getIterationMethod = function (KIND) {
	    if (KIND === DEFAULT && defaultIterator) return defaultIterator;
	    if (!BUGGY_SAFARI_ITERATORS$1 && KIND in IterablePrototype) return IterablePrototype[KIND];

	    switch (KIND) {
	      case KEYS:
	        return function keys() {
	          return new IteratorConstructor(this, KIND);
	        };

	      case VALUES:
	        return function values() {
	          return new IteratorConstructor(this, KIND);
	        };

	      case ENTRIES:
	        return function entries() {
	          return new IteratorConstructor(this, KIND);
	        };
	    }

	    return function () {
	      return new IteratorConstructor(this);
	    };
	  };

	  var TO_STRING_TAG = NAME + ' Iterator';
	  var INCORRECT_VALUES_NAME = false;
	  var IterablePrototype = Iterable.prototype;
	  var nativeIterator = IterablePrototype[ITERATOR$4] || IterablePrototype['@@iterator'] || DEFAULT && IterablePrototype[DEFAULT];
	  var defaultIterator = !BUGGY_SAFARI_ITERATORS$1 && nativeIterator || getIterationMethod(DEFAULT);
	  var anyNativeIterator = NAME == 'Array' ? IterablePrototype.entries || nativeIterator : nativeIterator;
	  var CurrentIteratorPrototype, methods, KEY; // fix native

	  if (anyNativeIterator) {
	    CurrentIteratorPrototype = objectGetPrototypeOf(anyNativeIterator.call(new Iterable()));

	    if (CurrentIteratorPrototype !== Object.prototype && CurrentIteratorPrototype.next) {
	      if (objectGetPrototypeOf(CurrentIteratorPrototype) !== IteratorPrototype$2) {
	        if (objectSetPrototypeOf) {
	          objectSetPrototypeOf(CurrentIteratorPrototype, IteratorPrototype$2);
	        } else if (!isCallable(CurrentIteratorPrototype[ITERATOR$4])) {
	          redefine(CurrentIteratorPrototype, ITERATOR$4, returnThis$1);
	        }
	      } // Set @@toStringTag to native iterators


	      setToStringTag(CurrentIteratorPrototype, TO_STRING_TAG, true, true);
	    }
	  } // fix Array.prototype.{ values, @@iterator }.name in V8 / FF


	  if (PROPER_FUNCTION_NAME$1 && DEFAULT == VALUES && nativeIterator && nativeIterator.name !== VALUES) {
	    if (CONFIGURABLE_FUNCTION_NAME$1) {
	      createNonEnumerableProperty(IterablePrototype, 'name', VALUES);
	    } else {
	      INCORRECT_VALUES_NAME = true;

	      defaultIterator = function values() {
	        return functionCall(nativeIterator, this);
	      };
	    }
	  } // export additional methods


	  if (DEFAULT) {
	    methods = {
	      values: getIterationMethod(VALUES),
	      keys: IS_SET ? defaultIterator : getIterationMethod(KEYS),
	      entries: getIterationMethod(ENTRIES)
	    };
	    if (FORCED) for (KEY in methods) {
	      if (BUGGY_SAFARI_ITERATORS$1 || INCORRECT_VALUES_NAME || !(KEY in IterablePrototype)) {
	        redefine(IterablePrototype, KEY, methods[KEY]);
	      }
	    } else _export({
	      target: NAME,
	      proto: true,
	      forced: BUGGY_SAFARI_ITERATORS$1 || INCORRECT_VALUES_NAME
	    }, methods);
	  } // define iterator


	  if (IterablePrototype[ITERATOR$4] !== defaultIterator) {
	    redefine(IterablePrototype, ITERATOR$4, defaultIterator, {
	      name: DEFAULT
	    });
	  }

	  iterators[NAME] = defaultIterator;
	  return methods;
	};

	var ARRAY_ITERATOR = 'Array Iterator';
	var setInternalState$2 = internalState.set;
	var getInternalState$3 = internalState.getterFor(ARRAY_ITERATOR); // `Array.prototype.entries` method
	// https://tc39.es/ecma262/#sec-array.prototype.entries
	// `Array.prototype.keys` method
	// https://tc39.es/ecma262/#sec-array.prototype.keys
	// `Array.prototype.values` method
	// https://tc39.es/ecma262/#sec-array.prototype.values
	// `Array.prototype[@@iterator]` method
	// https://tc39.es/ecma262/#sec-array.prototype-@@iterator
	// `CreateArrayIterator` internal method
	// https://tc39.es/ecma262/#sec-createarrayiterator

	var es_array_iterator = defineIterator(Array, 'Array', function (iterated, kind) {
	  setInternalState$2(this, {
	    type: ARRAY_ITERATOR,
	    target: toIndexedObject(iterated),
	    // target
	    index: 0,
	    // next index
	    kind: kind // kind

	  }); // `%ArrayIteratorPrototype%.next` method
	  // https://tc39.es/ecma262/#sec-%arrayiteratorprototype%.next
	}, function () {
	  var state = getInternalState$3(this);
	  var target = state.target;
	  var kind = state.kind;
	  var index = state.index++;

	  if (!target || index >= target.length) {
	    state.target = undefined;
	    return {
	      value: undefined,
	      done: true
	    };
	  }

	  if (kind == 'keys') return {
	    value: index,
	    done: false
	  };
	  if (kind == 'values') return {
	    value: target[index],
	    done: false
	  };
	  return {
	    value: [index, target[index]],
	    done: false
	  };
	}, 'values'); // argumentsList[@@iterator] is %ArrayProto_values%
	// https://tc39.es/ecma262/#sec-createunmappedargumentsobject
	// https://tc39.es/ecma262/#sec-createmappedargumentsobject

	iterators.Arguments = iterators.Array; // https://tc39.es/ecma262/#sec-array.prototype-@@unscopables

	addToUnscopables('keys');
	addToUnscopables('values');
	addToUnscopables('entries');

	var ITERATOR$5 = wellKnownSymbol('iterator');
	var TO_STRING_TAG$5 = wellKnownSymbol('toStringTag');
	var ArrayValues = es_array_iterator.values;

	var handlePrototype = function (CollectionPrototype, COLLECTION_NAME) {
	  if (CollectionPrototype) {
	    // some Chrome versions have non-configurable methods on DOMTokenList
	    if (CollectionPrototype[ITERATOR$5] !== ArrayValues) try {
	      createNonEnumerableProperty(CollectionPrototype, ITERATOR$5, ArrayValues);
	    } catch (error) {
	      CollectionPrototype[ITERATOR$5] = ArrayValues;
	    }

	    if (!CollectionPrototype[TO_STRING_TAG$5]) {
	      createNonEnumerableProperty(CollectionPrototype, TO_STRING_TAG$5, COLLECTION_NAME);
	    }

	    if (domIterables[COLLECTION_NAME]) for (var METHOD_NAME in es_array_iterator) {
	      // some Chrome versions have non-configurable methods on DOMTokenList
	      if (CollectionPrototype[METHOD_NAME] !== es_array_iterator[METHOD_NAME]) try {
	        createNonEnumerableProperty(CollectionPrototype, METHOD_NAME, es_array_iterator[METHOD_NAME]);
	      } catch (error) {
	        CollectionPrototype[METHOD_NAME] = es_array_iterator[METHOD_NAME];
	      }
	    }
	  }
	};

	for (var COLLECTION_NAME in domIterables) {
	  handlePrototype(global_1[COLLECTION_NAME] && global_1[COLLECTION_NAME].prototype, COLLECTION_NAME);
	}

	handlePrototype(domTokenListPrototype, 'DOMTokenList');

	var FunctionPrototype$2 = Function.prototype;
	var apply = FunctionPrototype$2.apply;
	var bind$2 = FunctionPrototype$2.bind;
	var call$2 = FunctionPrototype$2.call; // eslint-disable-next-line es/no-reflect -- safe

	var functionApply = typeof Reflect == 'object' && Reflect.apply || (bind$2 ? call$2.bind(apply) : function () {
	  return call$2.apply(apply, arguments);
	});

	var arraySlice = functionUncurryThis([].slice);

	var engineIsIos = /(?:ipad|iphone|ipod).*applewebkit/i.test(engineUserAgent);

	var engineIsNode = classofRaw(global_1.process) == 'process';

	var set$2 = global_1.setImmediate;
	var clear = global_1.clearImmediate;
	var process$1 = global_1.process;
	var Dispatch = global_1.Dispatch;
	var Function$1 = global_1.Function;
	var MessageChannel = global_1.MessageChannel;
	var String$5 = global_1.String;
	var counter = 0;
	var queue = {};
	var ONREADYSTATECHANGE = 'onreadystatechange';
	var location, defer, channel, port;

	try {
	  // Deno throws a ReferenceError on `location` access without `--location` flag
	  location = global_1.location;
	} catch (error) {
	  /* empty */
	}

	var run = function (id) {
	  if (hasOwnProperty_1(queue, id)) {
	    var fn = queue[id];
	    delete queue[id];
	    fn();
	  }
	};

	var runner = function (id) {
	  return function () {
	    run(id);
	  };
	};

	var listener = function (event) {
	  run(event.data);
	};

	var post = function (id) {
	  // old engines have not location.origin
	  global_1.postMessage(String$5(id), location.protocol + '//' + location.host);
	}; // Node.js 0.9+ & IE10+ has setImmediate, otherwise:


	if (!set$2 || !clear) {
	  set$2 = function setImmediate(fn) {
	    var args = arraySlice(arguments, 1);

	    queue[++counter] = function () {
	      functionApply(isCallable(fn) ? fn : Function$1(fn), undefined, args);
	    };

	    defer(counter);
	    return counter;
	  };

	  clear = function clearImmediate(id) {
	    delete queue[id];
	  }; // Node.js 0.8-


	  if (engineIsNode) {
	    defer = function (id) {
	      process$1.nextTick(runner(id));
	    }; // Sphere (JS game engine) Dispatch API

	  } else if (Dispatch && Dispatch.now) {
	    defer = function (id) {
	      Dispatch.now(runner(id));
	    }; // Browsers with MessageChannel, includes WebWorkers
	    // except iOS - https://github.com/zloirock/core-js/issues/624

	  } else if (MessageChannel && !engineIsIos) {
	    channel = new MessageChannel();
	    port = channel.port2;
	    channel.port1.onmessage = listener;
	    defer = functionBindContext(port.postMessage, port); // Browsers with postMessage, skip WebWorkers
	    // IE8 has postMessage, but it's sync & typeof its postMessage is 'object'
	  } else if (global_1.addEventListener && isCallable(global_1.postMessage) && !global_1.importScripts && location && location.protocol !== 'file:' && !fails(post)) {
	    defer = post;
	    global_1.addEventListener('message', listener, false); // IE8-
	  } else if (ONREADYSTATECHANGE in documentCreateElement('script')) {
	    defer = function (id) {
	      html.appendChild(documentCreateElement('script'))[ONREADYSTATECHANGE] = function () {
	        html.removeChild(this);
	        run(id);
	      };
	    }; // Rest old browsers

	  } else {
	    defer = function (id) {
	      setTimeout(runner(id), 0);
	    };
	  }
	}

	var task = {
	  set: set$2,
	  clear: clear
	};

	var FORCED = !global_1.setImmediate || !global_1.clearImmediate; // http://w3c.github.io/setImmediate/

	_export({
	  global: true,
	  bind: true,
	  enumerable: true,
	  forced: FORCED
	}, {
	  // `setImmediate` method
	  // http://w3c.github.io/setImmediate/#si-setImmediate
	  setImmediate: task.set,
	  // `clearImmediate` method
	  // http://w3c.github.io/setImmediate/#si-clearImmediate
	  clearImmediate: task.clear
	});

	var engineIsIosPebble = /ipad|iphone|ipod/i.test(engineUserAgent) && global_1.Pebble !== undefined;

	var engineIsWebosWebkit = /web0s(?!.*chrome)/i.test(engineUserAgent);

	var getOwnPropertyDescriptor$2 = objectGetOwnPropertyDescriptor.f;

	var macrotask = task.set;









	var MutationObserver = global_1.MutationObserver || global_1.WebKitMutationObserver;
	var document$2 = global_1.document;
	var process$2 = global_1.process;
	var Promise = global_1.Promise; // Node.js 11 shows ExperimentalWarning on getting `queueMicrotask`

	var queueMicrotaskDescriptor = getOwnPropertyDescriptor$2(global_1, 'queueMicrotask');
	var queueMicrotask = queueMicrotaskDescriptor && queueMicrotaskDescriptor.value;
	var flush, head, last, notify, toggle, node, promise, then; // modern engines have queueMicrotask method

	if (!queueMicrotask) {
	  flush = function () {
	    var parent, fn;
	    if (engineIsNode && (parent = process$2.domain)) parent.exit();

	    while (head) {
	      fn = head.fn;
	      head = head.next;

	      try {
	        fn();
	      } catch (error) {
	        if (head) notify();else last = undefined;
	        throw error;
	      }
	    }

	    last = undefined;
	    if (parent) parent.enter();
	  }; // browsers with MutationObserver, except iOS - https://github.com/zloirock/core-js/issues/339
	  // also except WebOS Webkit https://github.com/zloirock/core-js/issues/898


	  if (!engineIsIos && !engineIsNode && !engineIsWebosWebkit && MutationObserver && document$2) {
	    toggle = true;
	    node = document$2.createTextNode('');
	    new MutationObserver(flush).observe(node, {
	      characterData: true
	    });

	    notify = function () {
	      node.data = toggle = !toggle;
	    }; // environments with maybe non-completely correct, but existent Promise

	  } else if (!engineIsIosPebble && Promise && Promise.resolve) {
	    // Promise.resolve without an argument throws an error in LG WebOS 2
	    promise = Promise.resolve(undefined); // workaround of WebKit ~ iOS Safari 10.1 bug

	    promise.constructor = Promise;
	    then = functionBindContext(promise.then, promise);

	    notify = function () {
	      then(flush);
	    }; // Node.js without promises

	  } else if (engineIsNode) {
	    notify = function () {
	      process$2.nextTick(flush);
	    }; // for other environments - macrotask based on:
	    // - setImmediate
	    // - MessageChannel
	    // - window.postMessag
	    // - onreadystatechange
	    // - setTimeout

	  } else {
	    // strange IE + webpack dev server bug - use .bind(global)
	    macrotask = functionBindContext(macrotask, global_1);

	    notify = function () {
	      macrotask(flush);
	    };
	  }
	}

	var microtask = queueMicrotask || function (fn) {
	  var task$$1 = {
	    fn: fn,
	    next: undefined
	  };
	  if (last) last.next = task$$1;

	  if (!head) {
	    head = task$$1;
	    notify();
	  }

	  last = task$$1;
	};

	var process$3 = global_1.process; // `queueMicrotask` method
	// https://html.spec.whatwg.org/multipage/timers-and-user-prompts.html#dom-queuemicrotask

	_export({
	  global: true,
	  enumerable: true,
	  noTargetGet: true
	}, {
	  queueMicrotask: function queueMicrotask(fn) {
	    var domain = engineIsNode && process$3.domain;
	    microtask(domain ? domain.bind(fn) : fn);
	  }
	});

	// File generated automatically. Don't modify it.

}((this.window = this.window || {})));



if (window._main_polyfill_core)
{
	console.warn('main.polyfill.core is loaded more than once on this page');
}

window._main_polyfill_core = true;


(function (exports) {
	'use strict';

	var bxTmp = window.BX;
	window.BX = function (node) {
	  if (window.BX.type.isNotEmptyString(node)) {
	    return document.getElementById(node);
	  }
	  if (window.BX.type.isDomNode(node)) {
	    return node;
	  }
	  if (window.BX.type.isFunction(node)) {
	    return window.BX.ready(node);
	  }
	  return null;
	};
	if (bxTmp) {
	  Object.keys(bxTmp).forEach(key => {
	    window.BX[key] = bxTmp[key];
	  });
	}
	exports = window.BX;

	/**
	 * Gets object.toString result
	 * @param value
	 * @return {string}
	 */
	function getTag(value) {
	  return Object.prototype.toString.call(value);
	}

	const objectCtorString = Function.prototype.toString.call(Object);

	/**
	 * @memberOf BX
	 */
	let Type = /*#__PURE__*/function () {
	  function Type() {
	    babelHelpers.classCallCheck(this, Type);
	  }
	  babelHelpers.createClass(Type, null, [{
	    key: "isString",
	    /**
	     * Checks that value is string
	     * @param value
	     * @return {boolean}
	     */
	    value: function isString(value) {
	      return typeof value === 'string';
	    }
	    /**
	     * Returns true if a value is not empty string
	     * @param value
	     * @returns {boolean}
	     */
	  }, {
	    key: "isStringFilled",
	    value: function isStringFilled(value) {
	      return Type.isString(value) && value !== '';
	    }
	    /**
	     * Checks that value is function
	     * @param value
	     * @return {boolean}
	     */
	  }, {
	    key: "isFunction",
	    value: function isFunction(value) {
	      return typeof value === 'function';
	    }
	    /**
	     * Checks that value is object
	     * @param value
	     * @return {boolean}
	     */
	  }, {
	    key: "isObject",
	    value: function isObject(value) {
	      return !!value && (typeof value === 'object' || typeof value === 'function');
	    }
	    /**
	     * Checks that value is object like
	     * @param value
	     * @return {boolean}
	     */
	  }, {
	    key: "isObjectLike",
	    value: function isObjectLike(value) {
	      return !!value && typeof value === 'object';
	    }
	    /**
	     * Checks that value is plain object
	     * @param value
	     * @return {boolean}
	     */
	  }, {
	    key: "isPlainObject",
	    value: function isPlainObject(value) {
	      if (!Type.isObjectLike(value) || getTag(value) !== '[object Object]') {
	        return false;
	      }
	      const proto = Object.getPrototypeOf(value);
	      if (proto === null) {
	        return true;
	      }
	      const ctor = proto.hasOwnProperty('constructor') && proto.constructor;
	      return typeof ctor === 'function' && Function.prototype.toString.call(ctor) === objectCtorString;
	    }
	    /**
	     * Checks that value is boolean
	     * @param value
	     * @return {boolean}
	     */
	  }, {
	    key: "isBoolean",
	    value: function isBoolean(value) {
	      return value === true || value === false;
	    }
	    /**
	     * Checks that value is number
	     * @param value
	     * @return {boolean}
	     */
	  }, {
	    key: "isNumber",
	    value: function isNumber(value) {
	      return !Number.isNaN(value) && typeof value === 'number';
	    }
	    /**
	     * Checks that value is integer
	     * @param value
	     * @return {boolean}
	     */
	  }, {
	    key: "isInteger",
	    value: function isInteger(value) {
	      return Type.isNumber(value) && value % 1 === 0;
	    }
	    /**
	     * Checks that value is float
	     * @param value
	     * @return {boolean}
	     */
	  }, {
	    key: "isFloat",
	    value: function isFloat(value) {
	      return Type.isNumber(value) && !Type.isInteger(value);
	    }
	    /**
	     * Checks that value is nil
	     * @param value
	     * @return {boolean}
	     */
	  }, {
	    key: "isNil",
	    value: function isNil(value) {
	      return value === null || value === undefined;
	    }
	    /**
	     * Checks that value is array
	     * @param value
	     * @return {boolean}
	     */
	  }, {
	    key: "isArray",
	    value: function isArray(value) {
	      return !Type.isNil(value) && Array.isArray(value);
	    }
	    /**
	     * Returns true if a value is an array and it has at least one element
	     * @param value
	     * @returns {boolean}
	     */
	  }, {
	    key: "isArrayFilled",
	    value: function isArrayFilled(value) {
	      return Type.isArray(value) && value.length > 0;
	    }
	    /**
	     * Checks that value is array like
	     * @param value
	     * @return {boolean}
	     */
	  }, {
	    key: "isArrayLike",
	    value: function isArrayLike(value) {
	      return !Type.isNil(value) && !Type.isFunction(value) && value.length > -1 && value.length <= Number.MAX_SAFE_INTEGER;
	    }
	    /**
	     * Checks that value is Date
	     * @param value
	     * @return {boolean}
	     */
	  }, {
	    key: "isDate",
	    value: function isDate(value) {
	      return Type.isObjectLike(value) && getTag(value) === '[object Date]';
	    }
	    /**
	     * Checks that is DOM node
	     * @param value
	     * @return {boolean}
	     */
	  }, {
	    key: "isDomNode",
	    value: function isDomNode(value) {
	      return Type.isObjectLike(value) && !Type.isPlainObject(value) && 'nodeType' in value;
	    }
	    /**
	     * Checks that value is element node
	     * @param value
	     * @return {boolean}
	     */
	  }, {
	    key: "isElementNode",
	    value: function isElementNode(value) {
	      return Type.isDomNode(value) && value.nodeType === Node.ELEMENT_NODE;
	    }
	    /**
	     * Checks that value is text node
	     * @param value
	     * @return {boolean}
	     */
	  }, {
	    key: "isTextNode",
	    value: function isTextNode(value) {
	      return Type.isDomNode(value) && value.nodeType === Node.TEXT_NODE;
	    }
	    /**
	     * Checks that value is Map
	     * @param value
	     * @return {boolean}
	     */
	  }, {
	    key: "isMap",
	    value: function isMap(value) {
	      return Type.isObjectLike(value) && getTag(value) === '[object Map]';
	    }
	    /**
	     * Checks that value is Set
	     * @param value
	     * @return {boolean}
	     */
	  }, {
	    key: "isSet",
	    value: function isSet(value) {
	      return Type.isObjectLike(value) && getTag(value) === '[object Set]';
	    }
	    /**
	     * Checks that value is WeakMap
	     * @param value
	     * @return {boolean}
	     */
	  }, {
	    key: "isWeakMap",
	    value: function isWeakMap(value) {
	      return Type.isObjectLike(value) && getTag(value) === '[object WeakMap]';
	    }
	    /**
	     * Checks that value is WeakSet
	     * @param value
	     * @return {boolean}
	     */
	  }, {
	    key: "isWeakSet",
	    value: function isWeakSet(value) {
	      return Type.isObjectLike(value) && getTag(value) === '[object WeakSet]';
	    }
	    /**
	     * Checks that value is prototype
	     * @param value
	     * @return {boolean}
	     */
	  }, {
	    key: "isPrototype",
	    value: function isPrototype(value) {
	      return (typeof (value && value.constructor) === 'function' && value.constructor.prototype || Object.prototype) === value;
	    }
	    /**
	     * Checks that value is regexp
	     * @param value
	     * @return {boolean}
	     */
	  }, {
	    key: "isRegExp",
	    value: function isRegExp(value) {
	      return Type.isObjectLike(value) && getTag(value) === '[object RegExp]';
	    }
	    /**
	     * Checks that value is null
	     * @param value
	     * @return {boolean}
	     */
	  }, {
	    key: "isNull",
	    value: function isNull(value) {
	      return value === null;
	    }
	    /**
	     * Checks that value is undefined
	     * @param value
	     * @return {boolean}
	     */
	  }, {
	    key: "isUndefined",
	    value: function isUndefined(value) {
	      return typeof value === 'undefined';
	    }
	    /**
	     * Checks that value is ArrayBuffer
	     * @param value
	     * @return {boolean}
	     */
	  }, {
	    key: "isArrayBuffer",
	    value: function isArrayBuffer(value) {
	      return Type.isObjectLike(value) && getTag(value) === '[object ArrayBuffer]';
	    }
	    /**
	     * Checks that value is typed array
	     * @param value
	     * @return {boolean}
	     */
	  }, {
	    key: "isTypedArray",
	    value: function isTypedArray(value) {
	      const regExpTypedTag = /^\[object (?:Float(?:32|64)|(?:Int|Uint)(?:8|16|32)|Uint8Clamped)]$/;
	      return Type.isObjectLike(value) && regExpTypedTag.test(getTag(value));
	    }
	    /**
	     * Checks that value is Blob
	     * @param value
	     * @return {boolean}
	     */
	  }, {
	    key: "isBlob",
	    value: function isBlob(value) {
	      return Type.isObjectLike(value) && Type.isNumber(value.size) && Type.isString(value.type) && Type.isFunction(value.slice);
	    }
	    /**
	     * Checks that value is File
	     * @param value
	     * @return {boolean}
	     */
	  }, {
	    key: "isFile",
	    value: function isFile(value) {
	      return Type.isBlob(value) && Type.isString(value.name) && (Type.isNumber(value.lastModified) || Type.isObjectLike(value.lastModifiedDate));
	    }
	    /**
	     * Checks that value is FormData
	     * @param value
	     * @return {boolean}
	     */
	  }, {
	    key: "isFormData",
	    value: function isFormData(value) {
	      return value instanceof FormData;
	    }
	  }]);
	  return Type;
	}();

	/**
	 * @memberOf BX
	 */
	let Reflection = /*#__PURE__*/function () {
	  function Reflection() {
	    babelHelpers.classCallCheck(this, Reflection);
	  }
	  babelHelpers.createClass(Reflection, null, [{
	    key: "getClass",
	    /**
	     * Gets link to function by function name
	     * @param className
	     * @return {?Function}
	     */
	    value: function getClass(className) {
	      if (Type.isString(className) && !!className) {
	        let classFn = null;
	        let currentNamespace = window;
	        const namespaces = className.split('.');
	        for (let i = 0; i < namespaces.length; i += 1) {
	          const namespace = namespaces[i];
	          if (!currentNamespace[namespace]) {
	            return null;
	          }
	          currentNamespace = currentNamespace[namespace];
	          classFn = currentNamespace;
	        }
	        return classFn;
	      }
	      if (Type.isFunction(className)) {
	        return className;
	      }
	      return null;
	    }
	    /**
	     * Creates a namespace or returns a link to a previously created one
	     * @param {String} namespaceName
	     * @return {Object<string, any> | Function | null}
	     */
	  }, {
	    key: "namespace",
	    value: function namespace(namespaceName) {
	      let parts = namespaceName.split('.');
	      let parent = window.BX;
	      if (parts[0] === 'BX') {
	        parts = parts.slice(1);
	      }
	      for (let i = 0; i < parts.length; i += 1) {
	        if (Type.isUndefined(parent[parts[i]])) {
	          parent[parts[i]] = {};
	        }
	        parent = parent[parts[i]];
	      }
	      return parent;
	    }
	  }]);
	  return Reflection;
	}();

	const reEscape = /[&<>'"]/g;
	const reUnescape = /&(?:amp|#38|lt|#60|gt|#62|apos|#39|quot|#34);/g;
	const escapeEntities = {
	  '&': '&amp;',
	  '<': '&lt;',
	  '>': '&gt;',
	  "'": '&#39;',
	  '"': '&quot;'
	};
	const unescapeEntities = {
	  '&amp;': '&',
	  '&#38;': '&',
	  '&lt;': '<',
	  '&#60;': '<',
	  '&gt;': '>',
	  '&#62;': '>',
	  '&apos;': "'",
	  '&#39;': "'",
	  '&quot;': '"',
	  '&#34;': '"'
	};

	/**
	 * @memberOf BX
	 */
	let Text = /*#__PURE__*/function () {
	  function Text() {
	    babelHelpers.classCallCheck(this, Text);
	  }
	  babelHelpers.createClass(Text, null, [{
	    key: "encode",
	    /**
	     * Encodes all unsafe entities
	     * @param {string} value
	     * @return {string}
	     */
	    value: function encode(value) {
	      if (Type.isString(value)) {
	        return value.replace(reEscape, item => escapeEntities[item]);
	      }
	      return value;
	    }
	    /**
	     * Decodes all encoded entities
	     * @param {string} value
	     * @return {string}
	     */
	  }, {
	    key: "decode",
	    value: function decode(value) {
	      if (Type.isString(value)) {
	        return value.replace(reUnescape, item => unescapeEntities[item]);
	      }
	      return value;
	    }
	  }, {
	    key: "getRandom",
	    value: function getRandom(length = 8) {
	      // eslint-disable-next-line
	      return [...Array(length)].map(() => (~~(Math.random() * 36)).toString(36)).join('');
	    }
	  }, {
	    key: "toNumber",
	    value: function toNumber(value) {
	      const parsedValue = Number.parseFloat(value);
	      if (Type.isNumber(parsedValue)) {
	        return parsedValue;
	      }
	      return 0;
	    }
	  }, {
	    key: "toInteger",
	    value: function toInteger(value) {
	      return Text.toNumber(Number.parseInt(value, 10));
	    }
	  }, {
	    key: "toBoolean",
	    value: function toBoolean(value, trueValues = []) {
	      const transformedValue = Type.isString(value) ? value.toLowerCase() : value;
	      return ['true', 'y', '1', 1, true, ...trueValues].includes(transformedValue);
	    }
	  }, {
	    key: "toCamelCase",
	    value: function toCamelCase(str) {
	      if (!Type.isStringFilled(str)) {
	        return str;
	      }
	      const regex = /[-_\s]+(.)?/g;
	      if (!regex.test(str)) {
	        return str.match(/^[A-Z]+$/) ? str.toLowerCase() : str[0].toLowerCase() + str.slice(1);
	      }
	      str = str.toLowerCase();
	      str = str.replace(regex, function (match, letter) {
	        return letter ? letter.toUpperCase() : '';
	      });
	      return str[0].toLowerCase() + str.substr(1);
	    }
	  }, {
	    key: "toPascalCase",
	    value: function toPascalCase(str) {
	      if (!Type.isStringFilled(str)) {
	        return str;
	      }
	      return this.capitalize(this.toCamelCase(str));
	    }
	  }, {
	    key: "toKebabCase",
	    value: function toKebabCase(str) {
	      if (!Type.isStringFilled(str)) {
	        return str;
	      }
	      const matches = str.match(/[A-Z]{2,}(?=[A-Z][a-z]+[0-9]*|\b)|[A-Z]?[a-z]+[0-9]*|[A-Z]|[0-9]+/g);
	      if (!matches) {
	        return str;
	      }
	      return matches.map(x => x.toLowerCase()).join('-');
	    }
	  }, {
	    key: "capitalize",
	    value: function capitalize(str) {
	      if (!Type.isStringFilled(str)) {
	        return str;
	      }
	      return str[0].toUpperCase() + str.substr(1);
	    }
	  }]);
	  return Text;
	}();

	const aliases = {
	  mousewheel: ['DOMMouseScroll'],
	  bxchange: ['change', 'cut', 'paste', 'drop', 'keyup'],
	  animationend: ['animationend', 'oAnimationEnd', 'webkitAnimationEnd', 'MSAnimationEnd'],
	  transitionend: ['webkitTransitionEnd', 'otransitionend', 'oTransitionEnd', 'msTransitionEnd', 'transitionend'],
	  fullscreenchange: ['fullscreenchange', 'webkitfullscreenchange', 'mozfullscreenchange', 'MSFullscreenChange'],
	  fullscreenerror: ['fullscreenerror', 'webkitfullscreenerror', 'mozfullscreenerror', 'MSFullscreenError']
	};

	let Registry = /*#__PURE__*/function () {
	  function Registry() {
	    babelHelpers.classCallCheck(this, Registry);
	    babelHelpers.defineProperty(this, "registry", new WeakMap());
	  }
	  babelHelpers.createClass(Registry, [{
	    key: "set",
	    value: function set(target, event, listener) {
	      const events = this.get(target);
	      if (!Type.isSet(events[event])) {
	        events[event] = new Set();
	      }
	      events[event].add(listener);
	      this.registry.set(target, events);
	    }
	  }, {
	    key: "get",
	    value: function get(target) {
	      return this.registry.get(target) || {};
	    }
	  }, {
	    key: "has",
	    value: function has(target, event, listener) {
	      if (event && listener) {
	        return this.registry.has(target) && this.registry.get(target)[event].has(listener);
	      }
	      return this.registry.has(target);
	    }
	  }, {
	    key: "delete",
	    value: function _delete(target, event, listener) {
	      if (!Type.isDomNode(target)) {
	        return;
	      }
	      if (Type.isString(event) && Type.isFunction(listener)) {
	        const events = this.registry.get(target);
	        if (Type.isPlainObject(events) && Type.isSet(events[event])) {
	          events[event].delete(listener);
	        }
	        return;
	      }
	      if (Type.isString(event)) {
	        const events = this.registry.get(target);
	        if (Type.isPlainObject(events) && Type.isSet(events[event])) {
	          events[event] = new Set();
	        }
	        return;
	      }
	      this.registry.delete(target);
	    }
	  }]);
	  return Registry;
	}();
	var registry = new Registry();

	function isOptionSupported(name) {
	  let isSupported = false;
	  try {
	    const options = Object.defineProperty({}, name, {
	      get() {
	        isSupported = true;
	        return undefined;
	      }
	    });
	    window.addEventListener('test', null, options);
	  }
	  // eslint-disable-next-line
	  catch (err) {}
	  return isSupported;
	}
	function fetchSupportedListenerOptions(options) {
	  if (!Type.isPlainObject(options)) {
	    return options;
	  }
	  return Object.keys(options).reduce((acc, name) => {
	    if (isOptionSupported(name)) {
	      acc[name] = options[name];
	    }
	    return acc;
	  }, {});
	}

	function bind(target, eventName, handler, options) {
	  if (!Type.isObject(target) || !Type.isFunction(target.addEventListener)) {
	    return;
	  }
	  const listenerOptions = fetchSupportedListenerOptions(options);
	  if (eventName in aliases) {
	    aliases[eventName].forEach(key => {
	      target.addEventListener(key, handler, listenerOptions);
	      registry.set(target, eventName, handler);
	    });
	    return;
	  }
	  target.addEventListener(eventName, handler, listenerOptions);
	  registry.set(target, eventName, handler);
	}

	function unbind(target, eventName, handler, options) {
	  if (!Type.isObject(target) || !Type.isFunction(target.removeEventListener)) {
	    return;
	  }
	  const listenerOptions = fetchSupportedListenerOptions(options);
	  if (eventName in aliases) {
	    aliases[eventName].forEach(key => {
	      target.removeEventListener(key, handler, listenerOptions);
	      registry.delete(target, key, handler);
	    });
	    return;
	  }
	  target.removeEventListener(eventName, handler, listenerOptions);
	  registry.delete(target, eventName, handler);
	}

	function unbindAll(target, eventName) {
	  const events = registry.get(target);
	  Object.keys(events).forEach(currentEvent => {
	    events[currentEvent].forEach(handler => {
	      if (!Type.isString(eventName) || eventName === currentEvent) {
	        unbind(target, currentEvent, handler);
	      }
	    });
	  });
	}

	function bindOnce(target, eventName, handler, options) {
	  const once = function once(...args) {
	    unbind(target, eventName, once, options);
	    handler(...args);
	  };
	  bind(target, eventName, once, options);
	}

	// eslint-disable-next-line
	let debugState = true;
	function enableDebug() {
	  debugState = true;
	}
	function disableDebug() {
	  debugState = false;
	}
	function isDebugEnabled() {
	  return debugState;
	}
	function debug(...args) {
	  if (isDebugEnabled() && Type.isObject(window.console)) {
	    if (Type.isFunction(window.console.log)) {
	      window.console.log('BX.debug: ', args.length > 0 ? args : args[0]);
	      if (args[0] instanceof Error && args[0].stack) {
	        window.console.log('BX.debug error stack trace', args[0].stack);
	      }
	    }
	    if (Type.isFunction(window.console.trace)) {
	      // eslint-disable-next-line
	      console.trace();
	    }
	  }
	}

	var debugNs = /*#__PURE__*/Object.freeze({
		get debugState () { return debugState; },
		enableDebug: enableDebug,
		disableDebug: disableDebug,
		isDebugEnabled: isDebugEnabled,
		default: debug
	});

	const extensionsStorage = new Map();

	const ajaxController = 'main.bitrix.main.controller.loadext.getextensions';
	function loadAssets(options) {
	  return new Promise(resolve => {
	    // eslint-disable-next-line
	    BX.ajax.runAction(ajaxController, {
	      data: options
	    }).then(resolve);
	  });
	}

	function fetchInlineScripts(acc, item) {
	  if (item.isInternal) {
	    acc.push(item.JS);
	  }
	  return acc;
	}
	function fetchExternalScripts(acc, item) {
	  if (!item.isInternal) {
	    acc.push(item.JS);
	  }
	  return acc;
	}
	function fetchExternalStyles(acc, item) {
	  if (Type.isString(item) && item !== '') {
	    acc.push(item);
	  }
	  return acc;
	}
	function fetchExtensionSettings(html) {
	  if (Type.isStringFilled(html)) {
	    const scripts = html.match(/<script type="extension\/settings" \b[^>]*>([\s\S]*?)<\/script>/g);
	    if (Type.isArrayFilled(scripts)) {
	      return scripts.map(script => {
	        const [, extension] = script.match(/data-extension="(.[a-z0-9_.-]+)"/);
	        return {
	          extension,
	          script
	        };
	      });
	    }
	  }
	  return [];
	}
	function loadAll(items) {
	  const itemsList = Type.isArray(items) ? items : [items];
	  if (!itemsList.length) {
	    return Promise.resolve();
	  }
	  return new Promise(resolve => {
	    // eslint-disable-next-line
	    BX.load(itemsList, resolve);
	  });
	}

	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	const defaultOptions = {
	  loaded: false
	};
	var _state = /*#__PURE__*/new WeakMap();
	var _name = /*#__PURE__*/new WeakMap();
	var _namespace = /*#__PURE__*/new WeakMap();
	var _promise = /*#__PURE__*/new WeakMap();
	let Extension = /*#__PURE__*/function () {
	  function Extension(options) {
	    babelHelpers.classCallCheck(this, Extension);
	    _classPrivateFieldInitSpec(this, _state, {
	      writable: true,
	      value: Extension.State.LOADING
	    });
	    _classPrivateFieldInitSpec(this, _name, {
	      writable: true,
	      value: ''
	    });
	    _classPrivateFieldInitSpec(this, _namespace, {
	      writable: true,
	      value: ''
	    });
	    _classPrivateFieldInitSpec(this, _promise, {
	      writable: true,
	      value: null
	    });
	    const preparedOptions = {
	      ...defaultOptions,
	      ...options
	    };
	    babelHelpers.classPrivateFieldSet(this, _name, preparedOptions.name);
	    babelHelpers.classPrivateFieldSet(this, _namespace, Type.isStringFilled(preparedOptions.namespace) ? preparedOptions.namespace : 'window');
	    if (preparedOptions.loaded) {
	      babelHelpers.classPrivateFieldSet(this, _state, Extension.State.LOADED);
	    }
	  }
	  babelHelpers.createClass(Extension, [{
	    key: "load",
	    value: function load() {
	      if (babelHelpers.classPrivateFieldGet(this, _state) === Extension.State.LOADED && !babelHelpers.classPrivateFieldGet(this, _promise)) {
	        babelHelpers.classPrivateFieldSet(this, _promise, Promise.resolve(Reflection.getClass(babelHelpers.classPrivateFieldGet(this, _namespace))));
	      }
	      if (babelHelpers.classPrivateFieldGet(this, _promise)) {
	        return babelHelpers.classPrivateFieldGet(this, _promise);
	      }
	      babelHelpers.classPrivateFieldSet(this, _state, Extension.State.LOADING);
	      babelHelpers.classPrivateFieldSet(this, _promise, new Promise(resolve => {
	        void loadAssets({
	          extension: [babelHelpers.classPrivateFieldGet(this, _name)]
	        }).then(assetsResult => {
	          if (!Type.isArrayFilled(assetsResult.data)) {
	            resolve(window);
	          }
	          const extensionData = assetsResult.data.at(0);
	          if (Type.isPlainObject(extensionData.config) && Type.isStringFilled(extensionData.config.namespace)) {
	            babelHelpers.classPrivateFieldSet(this, _namespace, extensionData.config.namespace);
	          }
	          const result = BX.processHTML(extensionData.html || '');
	          const inlineScripts = result.SCRIPT.reduce(fetchInlineScripts, []);
	          const externalScripts = result.SCRIPT.reduce(fetchExternalScripts, []);
	          const externalStyles = result.STYLE.reduce(fetchExternalStyles, []);
	          const settingsScripts = fetchExtensionSettings(result.HTML);
	          settingsScripts.forEach(entry => {
	            document.body.insertAdjacentHTML('beforeend', entry.script);
	          });
	          inlineScripts.forEach(script => {
	            BX.evalGlobal(script);
	          });
	          void Promise.all([loadAll(externalScripts), loadAll(externalStyles)]).then(() => {
	            babelHelpers.classPrivateFieldSet(this, _state, Extension.State.LOADED);
	            if (babelHelpers.classPrivateFieldGet(this, _namespace)) {
	              return Reflection.getClass(babelHelpers.classPrivateFieldGet(this, _namespace));
	            }
	            return window;
	          }).then(exports => {
	            resolve(exports);
	          });
	        });
	      }));
	      return babelHelpers.classPrivateFieldGet(this, _promise);
	    }
	  }]);
	  return Extension;
	}();
	babelHelpers.defineProperty(Extension, "State", {
	  LOADED: 'LOADED',
	  LOADING: 'LOADING'
	});

	async function loadExtension(...extensionName) {
	  const extensionNames = extensionName.flat();
	  const result = extensionNames.map(name => {
	    if (extensionsStorage.has(name)) {
	      return extensionsStorage.get(name).load();
	    }
	    const extension = new Extension({
	      name
	    });
	    extensionsStorage.set(name, extension);
	    return extension.load();
	  });
	  return Promise.all(result).then(exports => {
	    return exports.reduce((acc, currentExports) => {
	      if (Type.isPlainObject(currentExports)) {
	        return {
	          ...acc,
	          ...currentExports
	        };
	      }
	      return acc;
	    }, {});
	  });
	}

	const cloneableTags = ['[object Object]', '[object Array]', '[object RegExp]', '[object Arguments]', '[object Date]', '[object Error]', '[object Map]', '[object Set]', '[object ArrayBuffer]', '[object DataView]', '[object Float32Array]', '[object Float64Array]', '[object Int8Array]', '[object Int16Array]', '[object Int32Array]', '[object Uint8Array]', '[object Uint16Array]', '[object Uint32Array]', '[object Uint8ClampedArray]'];
	function isCloneable(value) {
	  const isCloneableValue = Type.isObjectLike(value) && cloneableTags.includes(getTag(value));
	  return isCloneableValue || Type.isDomNode(value);
	}
	function internalClone(value, map) {
	  if (map.has(value)) {
	    return map.get(value);
	  }
	  if (isCloneable(value)) {
	    if (Type.isArray(value)) {
	      const cloned = Array.from(value);
	      map.set(value, cloned);
	      value.forEach((item, index) => {
	        cloned[index] = internalClone(item, map);
	      });
	      return map.get(value);
	    }
	    if (Type.isDomNode(value)) {
	      return value.cloneNode(true);
	    }
	    if (Type.isMap(value)) {
	      const result = new Map();
	      map.set(value, result);
	      value.forEach((item, key) => {
	        result.set(internalClone(key, map), internalClone(item, map));
	      });
	      return result;
	    }
	    if (Type.isSet(value)) {
	      const result = new Set();
	      map.set(value, result);
	      value.forEach(item => {
	        result.add(internalClone(item, map));
	      });
	      return result;
	    }
	    if (Type.isDate(value)) {
	      return new Date(value);
	    }
	    if (Type.isRegExp(value)) {
	      const regExpFlags = /\w*$/;
	      const flags = regExpFlags.exec(value);
	      let result = new RegExp(value.source);
	      if (flags && Type.isArray(flags)) {
	        result = new RegExp(value.source, flags[0]);
	      }
	      result.lastIndex = value.lastIndex;
	      return result;
	    }
	    const proto = Object.getPrototypeOf(value);
	    const result = Object.assign(Object.create(proto), value);
	    map.set(value, result);
	    Object.keys(value).forEach(key => {
	      result[key] = internalClone(value[key], map);
	    });
	    return result;
	  }
	  return value;
	}

	/**
	 * Clones any cloneable object
	 * @param value
	 * @return {*}
	 */
	function clone(value) {
	  return internalClone(value, new WeakMap());
	}

	function merge(current, target) {
	  return Object.entries(target).reduce((acc, [key, value]) => {
	    if (!Type.isDomNode(acc[key]) && Type.isObjectLike(acc[key]) && Type.isObjectLike(value)) {
	      acc[key] = merge(acc[key], value);
	      return acc;
	    }
	    acc[key] = value;
	    return acc;
	  }, current);
	}

	function createComparator(fields, orders = []) {
	  return (a, b) => {
	    const field = fields[0];
	    const order = orders[0] || 'asc';
	    if (Type.isUndefined(field)) {
	      return 0;
	    }
	    let valueA = a[field];
	    let valueB = b[field];
	    if (Type.isString(valueA) && Type.isString(valueB)) {
	      valueA = valueA.toLowerCase();
	      valueB = valueB.toLowerCase();
	    }
	    if (valueA < valueB) {
	      return order === 'asc' ? -1 : 1;
	    }
	    if (valueA > valueB) {
	      return order === 'asc' ? 1 : -1;
	    }
	    return createComparator(fields.slice(1), orders.slice(1))(a, b);
	  };
	}

	function registerExtension(options) {
	  if (!extensionsStorage.has(options.name)) {
	    extensionsStorage.set(options.name, new Extension(options));
	  }
	}

	/**
	 * @memberOf BX
	 */
	let Runtime = /*#__PURE__*/function () {
	  function Runtime() {
	    babelHelpers.classCallCheck(this, Runtime);
	  }
	  babelHelpers.createClass(Runtime, null, [{
	    key: "debounce",
	    value: function debounce(func, wait = 0, context = null) {
	      let timeoutId;
	      return function debounced(...args) {
	        if (Type.isNumber(timeoutId)) {
	          clearTimeout(timeoutId);
	        }
	        timeoutId = setTimeout(() => {
	          func.apply(context || this, args);
	        }, wait);
	      };
	    }
	  }, {
	    key: "throttle",
	    value: function throttle(func, wait = 0, context = null) {
	      let timer = 0;
	      let invoke;
	      return function wrapper(...args) {
	        invoke = true;
	        if (!timer) {
	          const q = function q() {
	            if (invoke) {
	              func.apply(context || this, args);
	              invoke = false;
	              timer = setTimeout(q, wait);
	            } else {
	              timer = null;
	            }
	          };
	          q();
	        }
	      };
	    }
	  }, {
	    key: "html",
	    value: function html(node, _html, params = {}) {
	      if (Type.isNil(_html) && Type.isDomNode(node)) {
	        return node.innerHTML;
	      }

	      // eslint-disable-next-line
	      const parsedHtml = BX.processHTML(_html);
	      const externalCss = parsedHtml.STYLE.reduce(fetchExternalStyles, []);
	      const externalJs = parsedHtml.SCRIPT.reduce(fetchExternalScripts, []);
	      const inlineJs = parsedHtml.SCRIPT.reduce(fetchInlineScripts, []);
	      if (Type.isDomNode(node)) {
	        if (params.htmlFirst || !externalJs.length && !externalCss.length) {
	          if (params.useAdjacentHTML) {
	            node.insertAdjacentHTML('beforeend', parsedHtml.HTML);
	          } else {
	            node.innerHTML = parsedHtml.HTML;
	          }
	        }
	      }
	      return Promise.all([loadAll(externalJs), loadAll(externalCss)]).then(() => {
	        if (Type.isDomNode(node) && (externalJs.length > 0 || externalCss.length > 0)) {
	          if (params.useAdjacentHTML) {
	            node.insertAdjacentHTML('beforeend', parsedHtml.HTML);
	          } else {
	            node.innerHTML = parsedHtml.HTML;
	          }
	        }

	        // eslint-disable-next-line
	        inlineJs.forEach(script => BX.evalGlobal(script));
	        if (Type.isFunction(params.callback)) {
	          params.callback();
	        }
	      });
	    }
	    /**
	     * Merges objects or arrays
	     * @param targets
	     * @return {any}
	     */
	  }, {
	    key: "merge",
	    value: function merge$$1(...targets) {
	      if (Type.isArray(targets[0])) {
	        targets.unshift([]);
	      } else if (Type.isObject(targets[0])) {
	        targets.unshift({});
	      }
	      return targets.reduce((acc, item) => {
	        return merge(acc, item);
	      }, targets[0]);
	    }
	  }, {
	    key: "orderBy",
	    value: function orderBy(collection, fields = [], orders = []) {
	      const comparator = createComparator(fields, orders);
	      return Object.values(collection).sort(comparator);
	    }
	  }, {
	    key: "destroy",
	    value: function destroy(target, errorMessage = 'Object is destroyed') {
	      if (Type.isObject(target)) {
	        const onPropertyAccess = () => {
	          throw new Error(errorMessage);
	        };
	        const ownProperties = Object.keys(target);
	        const prototypeProperties = (() => {
	          const targetPrototype = Object.getPrototypeOf(target);
	          if (Type.isObject(targetPrototype)) {
	            return Object.getOwnPropertyNames(targetPrototype);
	          }
	          return [];
	        })();
	        const uniquePropertiesList = [...new Set([...ownProperties, ...prototypeProperties])];
	        uniquePropertiesList.filter(name => {
	          const descriptor = Object.getOwnPropertyDescriptor(target, name);
	          return !/__(.+)__/.test(name) && (!Type.isObject(descriptor) || descriptor.configurable === true);
	        }).forEach(name => {
	          Object.defineProperty(target, name, {
	            get: onPropertyAccess,
	            set: onPropertyAccess,
	            configurable: false
	          });
	        });
	        Object.setPrototypeOf(target, null);
	      }
	    }
	  }]);
	  return Runtime;
	}();
	babelHelpers.defineProperty(Runtime, "debug", debug);
	babelHelpers.defineProperty(Runtime, "loadExtension", loadExtension);
	babelHelpers.defineProperty(Runtime, "registerExtension", registerExtension);
	babelHelpers.defineProperty(Runtime, "clone", clone);

	const _isError = Symbol.for('BX.BaseError.isError');

	/**
	 * @memberOf BX
	 */
	let BaseError = /*#__PURE__*/function () {
	  function BaseError(message, code, customData) {
	    babelHelpers.classCallCheck(this, BaseError);
	    this[_isError] = true;
	    this.message = '';
	    this.code = null;
	    this.customData = null;
	    this.setMessage(message);
	    this.setCode(code);
	    this.setCustomData(customData);
	  }

	  /**
	   * Returns a brief description of the error
	   * @returns {string}
	   */
	  babelHelpers.createClass(BaseError, [{
	    key: "getMessage",
	    value: function getMessage() {
	      return this.message;
	    }
	    /**
	     * Sets a message of the error
	     * @param {string} message
	     * @returns {this}
	     */
	  }, {
	    key: "setMessage",
	    value: function setMessage(message) {
	      if (Type.isString(message)) {
	        this.message = message;
	      }
	      return this;
	    }
	    /**
	     * Returns a code of the error
	     * @returns {?string}
	     */
	  }, {
	    key: "getCode",
	    value: function getCode() {
	      return this.code;
	    }
	    /**
	     * Sets a code of the error
	     * @param {string} code
	     * @returns {this}
	     */
	  }, {
	    key: "setCode",
	    value: function setCode(code) {
	      if (Type.isStringFilled(code) || code === null) {
	        this.code = code;
	      }
	      return this;
	    }
	    /**
	     * Returns custom data of the error
	     * @returns {null|*}
	     */
	  }, {
	    key: "getCustomData",
	    value: function getCustomData() {
	      return this.customData;
	    }
	    /**
	     * Sets custom data of the error
	     * @returns {this}
	     */
	  }, {
	    key: "setCustomData",
	    value: function setCustomData(customData) {
	      if (!Type.isUndefined(customData)) {
	        this.customData = customData;
	      }
	      return this;
	    }
	  }, {
	    key: "toString",
	    value: function toString() {
	      const code = this.getCode();
	      const message = this.getMessage();
	      if (!Type.isStringFilled(code) && !Type.isStringFilled(message)) {
	        return '';
	      } else if (!Type.isStringFilled(code)) {
	        return `Error: ${message}`;
	      } else if (!Type.isStringFilled(message)) {
	        return code;
	      } else {
	        return `${code}: ${message}`;
	      }
	    }
	    /**
	     * Returns true if the object is an instance of BaseError
	     * @param error
	     * @returns {boolean}
	     */
	  }], [{
	    key: "isError",
	    value: function isError(error) {
	      return Type.isObject(error) && error[_isError] === true;
	    }
	  }]);
	  return BaseError;
	}();

	/**
	 * Implements base event object interface
	 */
	let BaseEvent = /*#__PURE__*/function () {
	  function BaseEvent(options = {
	    data: {}
	  }) {
	    babelHelpers.classCallCheck(this, BaseEvent);
	    this.type = '';
	    this.data = null;
	    this.target = null;
	    this.compatData = null;
	    this.defaultPrevented = false;
	    this.immediatePropagationStopped = false;
	    this.errors = [];
	    this.setData(options.data);
	    this.setCompatData(options.compatData);
	  }
	  babelHelpers.createClass(BaseEvent, [{
	    key: "getType",
	    /**
	     * Returns the name of the event
	     * @returns {string}
	     */
	    value: function getType() {
	      return this.type;
	    }
	    /**
	     *
	     * @param {string} type
	     */
	  }, {
	    key: "setType",
	    value: function setType(type) {
	      if (Type.isStringFilled(type)) {
	        this.type = type;
	      }
	      return this;
	    }
	    /**
	     * Returns an event data
	     */
	  }, {
	    key: "getData",
	    value: function getData() {
	      return this.data;
	    }
	    /**
	     * Sets an event data
	     * @param data
	     */
	  }, {
	    key: "setData",
	    value: function setData(data) {
	      if (!Type.isUndefined(data)) {
	        this.data = data;
	      }
	      return this;
	    }
	    /**
	     * Returns arguments for BX.addCustomEvent handlers (deprecated).
	     * @returns {array | null}
	     */
	  }, {
	    key: "getCompatData",
	    value: function getCompatData() {
	      return this.compatData;
	    }
	    /**
	     * Sets arguments for BX.addCustomEvent handlers (deprecated)
	     * @param data
	     */
	  }, {
	    key: "setCompatData",
	    value: function setCompatData(data) {
	      if (Type.isArrayLike(data)) {
	        this.compatData = data;
	      }
	      return this;
	    }
	    /**
	     * Sets a event target
	     * @param target
	     */
	  }, {
	    key: "setTarget",
	    value: function setTarget(target) {
	      this.target = target;
	      return this;
	    }
	    /**
	     * Returns a event target
	     */
	  }, {
	    key: "getTarget",
	    value: function getTarget() {
	      return this.target;
	    }
	    /**
	     * Returns an array of event errors
	     * @returns {[]}
	     */
	  }, {
	    key: "getErrors",
	    value: function getErrors() {
	      return this.errors;
	    }
	    /**
	     * Adds an error of the event.
	     * Event listeners can prevent emitter's default action and set the reason of this behavior.
	     * @param error
	     */
	  }, {
	    key: "setError",
	    value: function setError(error) {
	      if (BaseError.isError(error)) {
	        this.errors.push(error);
	      }
	    }
	    /**
	     * Prevents default action
	     */
	  }, {
	    key: "preventDefault",
	    value: function preventDefault() {
	      this.defaultPrevented = true;
	    }
	    /**
	     * Checks that is default action prevented
	     * @return {boolean}
	     */
	  }, {
	    key: "isDefaultPrevented",
	    value: function isDefaultPrevented() {
	      return this.defaultPrevented;
	    }
	    /**
	     * Stops event immediate propagation
	     */
	  }, {
	    key: "stopImmediatePropagation",
	    value: function stopImmediatePropagation() {
	      this.immediatePropagationStopped = true;
	    }
	    /**
	     * Checks that is immediate propagation stopped
	     * @return {boolean}
	     */
	  }, {
	    key: "isImmediatePropagationStopped",
	    value: function isImmediatePropagationStopped() {
	      return this.immediatePropagationStopped;
	    }
	  }], [{
	    key: "create",
	    value: function create(options) {
	      return new this(options);
	    }
	  }]);
	  return BaseEvent;
	}();

	let EventStore = /*#__PURE__*/function () {
	  function EventStore(options = {}) {
	    babelHelpers.classCallCheck(this, EventStore);
	    this.defaultMaxListeners = Type.isNumber(options.defaultMaxListeners) ? options.defaultMaxListeners : 10;
	    this.eventStore = new WeakMap();
	  }
	  babelHelpers.createClass(EventStore, [{
	    key: "add",
	    value: function add(target, options = {}) {
	      const record = this.getRecordScheme();
	      if (Type.isNumber(options.maxListeners)) {
	        record.maxListeners = options.maxListeners;
	      }
	      this.eventStore.set(target, record);
	      return record;
	    }
	  }, {
	    key: "get",
	    value: function get(target) {
	      return this.eventStore.get(target);
	    }
	  }, {
	    key: "getOrAdd",
	    value: function getOrAdd(target, options = {}) {
	      return this.get(target) || this.add(target, options);
	    }
	  }, {
	    key: "delete",
	    value: function _delete(context) {
	      this.eventStore.delete(context);
	    }
	  }, {
	    key: "getRecordScheme",
	    value: function getRecordScheme() {
	      return {
	        eventsMap: new Map(),
	        onceMap: new Map(),
	        maxListeners: this.getDefaultMaxListeners(),
	        eventsMaxListeners: new Map()
	      };
	    }
	  }, {
	    key: "getDefaultMaxListeners",
	    value: function getDefaultMaxListeners() {
	      return this.defaultMaxListeners;
	    }
	  }]);
	  return EventStore;
	}();

	let WarningStore = /*#__PURE__*/function () {
	  function WarningStore() {
	    babelHelpers.classCallCheck(this, WarningStore);
	    this.warnings = new Map();
	    this.printDelayed = Runtime.debounce(this.print.bind(this), 500);
	  }
	  babelHelpers.createClass(WarningStore, [{
	    key: "add",
	    value: function add(target, eventName, listeners) {
	      let contextWarnings = this.warnings.get(target);
	      if (!contextWarnings) {
	        contextWarnings = Object.create(null);
	        this.warnings.set(target, contextWarnings);
	      }
	      if (!contextWarnings[eventName]) {
	        contextWarnings[eventName] = {};
	      }
	      contextWarnings[eventName].size = listeners.size;
	      if (!Type.isArray(contextWarnings[eventName].errors)) {
	        contextWarnings[eventName].errors = [];
	      }
	      contextWarnings[eventName].errors.push(new Error());
	    }
	  }, {
	    key: "print",
	    value: function print() {
	      this.warnings.forEach(warnings => {
	        for (let eventName in warnings) {
	          console.groupCollapsed('Possible BX.Event.EventEmitter memory leak detected. ' + warnings[eventName].size + ' "' + eventName + '" listeners added. ' + 'Use emitter.setMaxListeners() to increase limit.');
	          console.dir(warnings[eventName].errors);
	          console.groupEnd();
	        }
	      });
	      this.clear();
	    }
	  }, {
	    key: "clear",
	    value: function clear() {
	      this.warnings.clear();
	    }
	  }, {
	    key: "printDelayed",
	    value: function printDelayed() {}
	  }]);
	  return WarningStore;
	}();

	const eventStore = new EventStore({
	  defaultMaxListeners: 10
	});
	const warningStore = new WarningStore();
	const aliasStore = new Map();
	const globalTarget = {
	  GLOBAL_TARGET: 'GLOBAL_TARGET' // this key only for debugging purposes
	};

	eventStore.add(globalTarget, {
	  maxListeners: 25
	});
	const isEmitterProperty = Symbol.for('BX.Event.EventEmitter.isEmitter');
	const namespaceProperty = Symbol('namespaceProperty');
	const targetProperty = Symbol('targetProperty');
	let EventEmitter = /*#__PURE__*/function () {
	  /** @private */

	  function EventEmitter(...args) {
	    babelHelpers.classCallCheck(this, EventEmitter);
	    this[targetProperty] = null;
	    this[namespaceProperty] = null;
	    this[isEmitterProperty] = true;
	    let target = this;
	    if (Object.getPrototypeOf(this) === EventEmitter.prototype && args.length > 0)
	      //new EventEmitter(obj) case
	      {
	        if (!Type.isObject(args[0])) {
	          throw new TypeError(`The "target" argument must be an object.`);
	        }
	        target = args[0];
	        this.setEventNamespace(args[1]);
	      }
	    this[targetProperty] = target;
	  }

	  /**
	   * Makes a target observable
	   * @param {object} target
	   * @param {string} namespace
	   */
	  babelHelpers.createClass(EventEmitter, [{
	    key: "setEventNamespace",
	    value: function setEventNamespace(namespace) {
	      if (Type.isStringFilled(namespace)) {
	        this[namespaceProperty] = namespace;
	      }
	    }
	  }, {
	    key: "getEventNamespace",
	    value: function getEventNamespace() {
	      return this[namespaceProperty];
	    }
	    /**
	     * Subscribes listener on specified global event
	     * @param {object} target
	     * @param {string} eventName
	     * @param {Function<BaseEvent>} listener
	     * @param {object} options
	     */
	  }, {
	    key: "subscribe",
	    /**
	     * Subscribes a listener on a specified event
	     * @param {string} eventName
	     * @param {Function<BaseEvent>} listener
	     * @return {this}
	     */
	    value: function subscribe(eventName, listener) {
	      EventEmitter.subscribe(this, eventName, listener);
	      return this;
	    }
	    /**
	     *
	     * @param {object} options
	     * @param {object} [aliases]
	     * @param {boolean} [compatMode=false]
	     */
	  }, {
	    key: "subscribeFromOptions",
	    value: function subscribeFromOptions(options, aliases, compatMode) {
	      if (!Type.isPlainObject(options)) {
	        return;
	      }
	      aliases = Type.isPlainObject(aliases) ? EventEmitter.normalizeAliases(aliases) : {};
	      Object.keys(options).forEach(eventName => {
	        const listener = EventEmitter.normalizeListener(options[eventName]);
	        eventName = EventEmitter.normalizeEventName(eventName);
	        if (aliases[eventName]) {
	          const {
	            eventName: actualName
	          } = aliases[eventName];
	          EventEmitter.subscribe(this, actualName, listener, {
	            compatMode: compatMode !== false
	          });
	        } else {
	          EventEmitter.subscribe(this, eventName, listener, {
	            compatMode: compatMode === true
	          });
	        }
	      });
	    }
	    /**
	     * Subscribes a listener that is called at
	     * most once for a specified event.
	     * @param {object} target
	     * @param {string} eventName
	     * @param {Function<BaseEvent>} listener
	     */
	  }, {
	    key: "subscribeOnce",
	    /**
	     * Subscribes a listener that is called at most once for a specified event.
	     * @param {string} eventName
	     * @param {Function<BaseEvent>} listener
	     * @return {this}
	     */
	    value: function subscribeOnce(eventName, listener) {
	      EventEmitter.subscribeOnce(this, eventName, listener);
	      return this;
	    }
	    /**
	     * Unsubscribes an event listener
	     * @param {object} target
	     * @param {string} eventName
	     * @param {Function<BaseEvent>} listener
	     * @param options
	     */
	  }, {
	    key: "unsubscribe",
	    /**
	     * Unsubscribes an event listener
	     * @param {string} eventName
	     * @param {Function<BaseEvent>} listener
	     * @return {this}
	     */
	    value: function unsubscribe(eventName, listener) {
	      EventEmitter.unsubscribe(this, eventName, listener);
	      return this;
	    }
	    /**
	     * Unsubscribes all event listeners
	     * @param {object} target
	     * @param {string} eventName
	     * @param options
	     */
	  }, {
	    key: "unsubscribeAll",
	    /**
	     * Unsubscribes all event listeners
	     * @param {string} [eventName]
	     */
	    value: function unsubscribeAll(eventName) {
	      EventEmitter.unsubscribeAll(this, eventName);
	    }
	    /**
	     *
	     * @param {object} target
	     * @param {string} eventName
	     * @param {BaseEvent | any} event
	     * @param {object} options
	     * @returns {Array}
	     */
	  }, {
	    key: "emit",
	    /**
	     * Emits specified event with specified event object
	     * @param {string} eventName
	     * @param {BaseEvent | any} event
	     * @return {this}
	     */
	    value: function emit(eventName, event) {
	      if (this.getEventNamespace() === null) {
	        console.warn('The instance of BX.Event.EventEmitter is supposed to have an event namespace. ' + 'Use emitter.setEventNamespace() to make events more unique.');
	      }
	      EventEmitter.emit(this, eventName, event);
	      return this;
	    }
	    /**
	     * Emits global event and returns a promise that is resolved when
	     * all promise returned from event handlers are resolved,
	     * or rejected when at least one of the returned promise is rejected.
	     * Importantly. You can return any value from synchronous handlers, not just promise
	     * @param {object} target
	     * @param {string} eventName
	     * @param {BaseEvent | any} event
	     * @return {Promise<Array>}
	     */
	  }, {
	    key: "emitAsync",
	    /**
	     * Emits event and returns a promise that is resolved when
	     * all promise returned from event handlers are resolved,
	     * or rejected when at least one of the returned promise is rejected.
	     * Importantly. You can return any value from synchronous handlers, not just promise
	     * @param {string} eventName
	     * @param {BaseEvent|any} event
	     * @return {Promise<Array>}
	     */
	    value: function emitAsync(eventName, event) {
	      if (this.getEventNamespace() === null) {
	        console.warn('The instance of BX.Event.EventEmitter is supposed to have an event namespace. ' + 'Use emitter.setEventNamespace() to make events more unique.');
	      }
	      return EventEmitter.emitAsync(this, eventName, event);
	    }
	    /**
	     * @private
	     * @param {object} target
	     * @param {string} eventName
	     * @param {BaseEvent|any} event
	     * @returns {BaseEvent}
	     */
	  }, {
	    key: "setMaxListeners",
	    /**
	     * Sets max events listeners count
	     * this.setMaxListeners(10) - sets the default value for all events
	     * this.setMaxListeners("onClose", 10) sets the value for onClose event
	     * @return {this}
	     * @param args
	     */
	    value: function setMaxListeners(...args) {
	      EventEmitter.setMaxListeners(this, ...args);
	      return this;
	    }
	    /**
	     * Returns max event listeners count
	     * @param {object} target
	     * @param {string} [eventName]
	     * @returns {number}
	     */
	  }, {
	    key: "getMaxListeners",
	    /**
	     * Returns max event listeners count
	     * @param {string} [eventName]
	     * @returns {number}
	     */
	    value: function getMaxListeners(eventName) {
	      return EventEmitter.getMaxListeners(this, eventName);
	    }
	    /**
	     * Adds or subtracts max listeners count
	     * Event.EventEmitter.addMaxListeners() - adds one max listener for all events of global target
	     * Event.EventEmitter.addMaxListeners(3) - adds three max listeners for all events of global target
	     * Event.EventEmitter.addMaxListeners(-1) - subtracts one max listener for all events of global target
	     * Event.EventEmitter.addMaxListeners('onClose') - adds one max listener for onClose event of global target
	     * Event.EventEmitter.addMaxListeners('onClose', 2) - adds two max listeners for onClose event of global target
	     * Event.EventEmitter.addMaxListeners('onClose', -1) - subtracts one max listener for onClose event of global target
	     *
	     * Event.EventEmitter.addMaxListeners(obj) - adds one max listener for all events of 'obj' target
	     * Event.EventEmitter.addMaxListeners(obj, 3) - adds three max listeners for all events of 'obj' target
	     * Event.EventEmitter.addMaxListeners(obj, -1) - subtracts one max listener for all events of 'obj' target
	     * Event.EventEmitter.addMaxListeners(obj, 'onClose') - adds one max listener for onClose event of 'obj' target
	     * Event.EventEmitter.addMaxListeners(obj, 'onClose', 2) - adds two max listeners for onClose event of 'obj' target
	     * Event.EventEmitter.addMaxListeners(obj, 'onClose', -1) - subtracts one max listener for onClose event of 'obj' target
	     * @param args
	     * @returns {number}
	     */
	  }, {
	    key: "incrementMaxListeners",
	    /**
	     * Increases max listeners count
	     * this.incrementMaxListeners() - adds one max listener for all events
	     * this.incrementMaxListeners(3) - adds three max listeners for all events
	     * this.incrementMaxListeners('onClose') - adds one max listener for onClose event
	     * this.incrementMaxListeners('onClose', 2) - adds two max listeners for onClose event
	     */
	    value: function incrementMaxListeners(...args) {
	      return EventEmitter.incrementMaxListeners(this, ...args);
	    }
	    /**
	     * Decreases max listeners count
	     *
	     * Event.EventEmitter.decrementMaxListeners() - subtracts one max listener for all events of global target
	     * Event.EventEmitter.decrementMaxListeners(3) - subtracts three max listeners for all events of global target
	     * Event.EventEmitter.decrementMaxListeners('onClose') - subtracts one max listener for onClose event of global target
	     * Event.EventEmitter.decrementMaxListeners('onClose', 2) - subtracts two max listeners for onClose event of global target
	     *
	     * Event.EventEmitter.decrementMaxListeners(obj) - subtracts one max listener for all events of 'obj' target
	     * Event.EventEmitter.decrementMaxListeners(obj, 3) - subtracts three max listeners for all events of 'obj' target
	     * Event.EventEmitter.decrementMaxListeners(obj, 'onClose') - subtracts one max listener for onClose event of 'obj' target
	     * Event.EventEmitter.decrementMaxListeners(obj, 'onClose', 2) - subtracts two max listeners for onClose event of 'obj' target
	     */
	  }, {
	    key: "decrementMaxListeners",
	    /**
	     * Increases max listeners count
	     * this.decrementMaxListeners() - subtracts one max listener for all events
	     * this.decrementMaxListeners(3) - subtracts three max listeners for all events
	     * this.decrementMaxListeners('onClose') - subtracts one max listener for onClose event
	     * this.decrementMaxListeners('onClose', 2) - subtracts two max listeners for onClose event
	     */
	    value: function decrementMaxListeners(...args) {
	      return EventEmitter.decrementMaxListeners(this, ...args);
	    }
	    /**
	     * @private
	     * @param {Array} args
	     * @returns Array
	     */
	  }, {
	    key: "getListeners",
	    /**
	     * Gets listeners list for specified event
	     * @param {string} eventName
	     */
	    value: function getListeners(eventName) {
	      return EventEmitter.getListeners(this, eventName);
	    }
	    /**
	     * Returns a full event name with namespace
	     * @param {string} eventName
	     * @returns {string}
	     */
	  }, {
	    key: "getFullEventName",
	    value: function getFullEventName(eventName) {
	      if (!Type.isStringFilled(eventName)) {
	        throw new TypeError(`The "eventName" argument must be a string.`);
	      }
	      return EventEmitter.makeFullEventName(this.getEventNamespace(), eventName);
	    }
	    /**
	     * Registers aliases (old event names for BX.onCustomEvent)
	     * @param aliases
	     */
	  }], [{
	    key: "makeObservable",
	    value: function makeObservable(target, namespace) {
	      if (!Type.isObject(target)) {
	        throw new TypeError('The "target" argument must be an object.');
	      }
	      if (!Type.isStringFilled(namespace)) {
	        throw new TypeError('The "namespace" must be an non-empty string.');
	      }
	      if (EventEmitter.isEventEmitter(target)) {
	        throw new TypeError('The "target" is an event emitter already.');
	      }
	      const targetProto = Object.getPrototypeOf(target);
	      const emitter = new EventEmitter();
	      emitter.setEventNamespace(namespace);
	      Object.setPrototypeOf(emitter, targetProto);
	      Object.setPrototypeOf(target, emitter);
	      Object.getOwnPropertyNames(EventEmitter.prototype).forEach(method => {
	        if (['constructor'].includes(method)) {
	          return;
	        }
	        emitter[method] = function (...args) {
	          return EventEmitter.prototype[method].apply(target, args);
	        };
	      });
	    }
	  }, {
	    key: "subscribe",
	    value: function subscribe(target, eventName, listener, options) {
	      if (Type.isString(target)) {
	        options = listener;
	        listener = eventName;
	        eventName = target;
	        target = this.GLOBAL_TARGET;
	      }
	      if (!Type.isObject(target)) {
	        throw new TypeError(`The "target" argument must be an object.`);
	      }
	      eventName = this.normalizeEventName(eventName);
	      if (!Type.isStringFilled(eventName)) {
	        throw new TypeError(`The "eventName" argument must be a string.`);
	      }
	      listener = this.normalizeListener(listener);
	      options = Type.isPlainObject(options) ? options : {};
	      const fullEventName = this.resolveEventName(eventName, target, options.useGlobalNaming === true);
	      const {
	        eventsMap,
	        onceMap
	      } = eventStore.getOrAdd(target);
	      const onceListeners = onceMap.get(fullEventName);
	      let listeners = eventsMap.get(fullEventName);
	      if (listeners && listeners.has(listener) || onceListeners && onceListeners.has(listener)) {
	        console.error(`You cannot subscribe the same "${fullEventName}" event listener twice.`);
	      } else {
	        if (listeners) {
	          listeners.set(listener, {
	            listener,
	            options,
	            sort: this.getNextSequenceValue()
	          });
	        } else {
	          listeners = new Map([[listener, {
	            listener,
	            options,
	            sort: this.getNextSequenceValue()
	          }]]);
	          eventsMap.set(fullEventName, listeners);
	        }
	      }
	      const maxListeners = this.getMaxListeners(target, eventName);
	      if (listeners.size > maxListeners) {
	        warningStore.add(target, fullEventName, listeners);
	        warningStore.printDelayed();
	      }
	    }
	  }, {
	    key: "subscribeOnce",
	    value: function subscribeOnce(target, eventName, listener) {
	      if (Type.isString(target)) {
	        listener = eventName;
	        eventName = target;
	        target = this.GLOBAL_TARGET;
	      }
	      if (!Type.isObject(target)) {
	        throw new TypeError(`The "target" argument must be an object.`);
	      }
	      eventName = this.normalizeEventName(eventName);
	      if (!Type.isStringFilled(eventName)) {
	        throw new TypeError(`The "eventName" argument must be a string.`);
	      }
	      listener = this.normalizeListener(listener);
	      const fullEventName = this.resolveEventName(eventName, target);
	      const {
	        eventsMap,
	        onceMap
	      } = eventStore.getOrAdd(target);
	      const listeners = eventsMap.get(fullEventName);
	      let onceListeners = onceMap.get(fullEventName);
	      if (listeners && listeners.has(listener) || onceListeners && onceListeners.has(listener)) {
	        console.error(`You cannot subscribe the same "${fullEventName}" event listener twice.`);
	      } else {
	        const once = (...args) => {
	          this.unsubscribe(target, eventName, once);
	          onceListeners.delete(listener);
	          listener(...args);
	        };
	        if (onceListeners) {
	          onceListeners.set(listener, once);
	        } else {
	          onceListeners = new Map([[listener, once]]);
	          onceMap.set(fullEventName, onceListeners);
	        }
	        this.subscribe(target, eventName, once);
	      }
	    }
	  }, {
	    key: "unsubscribe",
	    value: function unsubscribe(target, eventName, listener, options) {
	      if (Type.isString(target)) {
	        listener = eventName;
	        eventName = target;
	        target = this.GLOBAL_TARGET;
	      }
	      eventName = this.normalizeEventName(eventName);
	      if (!Type.isStringFilled(eventName)) {
	        throw new TypeError(`The "eventName" argument must be a string.`);
	      }
	      listener = this.normalizeListener(listener);
	      options = Type.isPlainObject(options) ? options : {};
	      const fullEventName = this.resolveEventName(eventName, target, options.useGlobalNaming === true);
	      const targetInfo = eventStore.get(target);
	      const listeners = targetInfo && targetInfo.eventsMap.get(fullEventName);
	      const onceListeners = targetInfo && targetInfo.onceMap.get(fullEventName);
	      if (listeners) {
	        listeners.delete(listener);
	      }
	      if (onceListeners) {
	        const once = onceListeners.get(listener);
	        if (once) {
	          onceListeners.delete(listener);
	          listeners.delete(once);
	        }
	      }
	    }
	  }, {
	    key: "unsubscribeAll",
	    value: function unsubscribeAll(target, eventName, options) {
	      if (Type.isString(target)) {
	        eventName = target;
	        target = this.GLOBAL_TARGET;
	      }
	      if (Type.isStringFilled(eventName)) {
	        const targetInfo = eventStore.get(target);
	        if (targetInfo) {
	          options = Type.isPlainObject(options) ? options : {};
	          const fullEventName = this.resolveEventName(eventName, target, options.useGlobalNaming === true);
	          targetInfo.eventsMap.delete(fullEventName);
	          targetInfo.onceMap.delete(fullEventName);
	        }
	      } else if (Type.isNil(eventName)) {
	        if (target === this.GLOBAL_TARGET) {
	          console.error('You cannot unsubscribe all global listeners.');
	        } else {
	          eventStore.delete(target);
	        }
	      }
	    }
	  }, {
	    key: "emit",
	    value: function emit(target, eventName, event, options) {
	      if (Type.isString(target)) {
	        options = event;
	        event = eventName;
	        eventName = target;
	        target = this.GLOBAL_TARGET;
	      }
	      if (!Type.isObject(target)) {
	        throw new TypeError(`The "target" argument must be an object.`);
	      }
	      eventName = this.normalizeEventName(eventName);
	      if (!Type.isStringFilled(eventName)) {
	        throw new TypeError(`The "eventName" argument must be a string.`);
	      }
	      options = Type.isPlainObject(options) ? options : {};
	      const fullEventName = this.resolveEventName(eventName, target, options.useGlobalNaming === true);
	      const globalEvents = eventStore.get(this.GLOBAL_TARGET);
	      const globalListeners = globalEvents && globalEvents.eventsMap.get(fullEventName) || new Map();
	      let targetListeners = new Set();
	      if (target !== this.GLOBAL_TARGET) {
	        const targetEvents = eventStore.get(target);
	        targetListeners = targetEvents && targetEvents.eventsMap.get(fullEventName) || new Map();
	      }
	      const listeners = [...globalListeners.values(), ...targetListeners.values()];
	      listeners.sort(function (a, b) {
	        return a.sort - b.sort;
	      });
	      const preparedEvent = this.prepareEvent(target, fullEventName, event);
	      const result = [];
	      for (let i = 0; i < listeners.length; i++) {
	        if (preparedEvent.isImmediatePropagationStopped()) {
	          break;
	        }
	        const {
	          listener,
	          options: listenerOptions
	        } = listeners[i];

	        //A previous listener could remove a current listener.
	        if (globalListeners.has(listener) || targetListeners.has(listener)) {
	          let listenerResult;
	          if (listenerOptions.compatMode) {
	            let params = [];
	            const compatData = preparedEvent.getCompatData();
	            if (compatData !== null) {
	              params = options.cloneData === true ? Runtime.clone(compatData) : compatData;
	            } else {
	              params = [preparedEvent];
	            }
	            const context = Type.isUndefined(options.thisArg) ? target : options.thisArg;
	            listenerResult = listener.apply(context, params);
	          } else {
	            listenerResult = Type.isUndefined(options.thisArg) ? listener(preparedEvent) : listener.call(options.thisArg, preparedEvent);
	          }
	          result.push(listenerResult);
	        }
	      }
	      return result;
	    }
	  }, {
	    key: "emitAsync",
	    value: function emitAsync(target, eventName, event) {
	      if (Type.isString(target)) {
	        event = eventName;
	        eventName = target;
	        target = this.GLOBAL_TARGET;
	      }
	      return Promise.all(this.emit(target, eventName, event));
	    }
	  }, {
	    key: "prepareEvent",
	    value: function prepareEvent(target, eventName, event) {
	      let preparedEvent = event;
	      if (!(event instanceof BaseEvent)) {
	        preparedEvent = new BaseEvent();
	        preparedEvent.setData(event);
	      }
	      preparedEvent.setTarget(this.isEventEmitter(target) ? target[targetProperty] : target);
	      preparedEvent.setType(eventName);
	      return preparedEvent;
	    }
	    /**
	     * @private
	     * @returns {number}
	     */
	  }, {
	    key: "getNextSequenceValue",
	    value: function getNextSequenceValue() {
	      return this.sequenceValue++;
	    }
	    /**
	     * Sets max global events listeners count
	     * Event.EventEmitter.setMaxListeners(10) - sets the default value for all events (global target)
	     * Event.EventEmitter.setMaxListeners("onClose", 10) - sets the value for onClose event (global target)
	     * Event.EventEmitter.setMaxListeners(obj, 10) - sets the default value for all events (obj target)
	     * Event.EventEmitter.setMaxListeners(obj, "onClose", 10); - sets the value for onClose event (obj target)
	     * @return {void}
	     * @param args
	     */
	  }, {
	    key: "setMaxListeners",
	    value: function setMaxListeners(...args) {
	      let target = this.GLOBAL_TARGET;
	      let eventName = null;
	      let count = undefined;
	      if (args.length === 1) {
	        count = args[0];
	      } else if (args.length === 2) {
	        if (Type.isString(args[0])) {
	          [eventName, count] = args;
	        } else {
	          [target, count] = args;
	        }
	      } else if (args.length >= 3) {
	        [target, eventName, count] = args;
	      }
	      if (!Type.isObject(target)) {
	        throw new TypeError(`The "target" argument must be an object.`);
	      }
	      if (eventName !== null && !Type.isStringFilled(eventName)) {
	        throw new TypeError(`The "eventName" argument must be a string.`);
	      }
	      if (!Type.isNumber(count) || count < 0) {
	        throw new TypeError(`The value of "count" is out of range. It must be a non-negative number. Received ${count}.`);
	      }
	      const targetInfo = eventStore.getOrAdd(target);
	      if (Type.isStringFilled(eventName)) {
	        const fullEventName = this.resolveEventName(eventName, target);
	        targetInfo.eventsMaxListeners.set(fullEventName, count);
	      } else {
	        targetInfo.maxListeners = count;
	      }
	    }
	  }, {
	    key: "getMaxListeners",
	    value: function getMaxListeners(target, eventName) {
	      if (Type.isString(target)) {
	        eventName = target;
	        target = this.GLOBAL_TARGET;
	      } else if (Type.isNil(target)) {
	        target = this.GLOBAL_TARGET;
	      }
	      if (!Type.isObject(target)) {
	        throw new TypeError(`The "target" argument must be an object.`);
	      }
	      const targetInfo = eventStore.get(target);
	      if (targetInfo) {
	        let maxListeners = targetInfo.maxListeners;
	        if (Type.isStringFilled(eventName)) {
	          const fullEventName = this.resolveEventName(eventName, target);
	          maxListeners = targetInfo.eventsMaxListeners.get(fullEventName) || maxListeners;
	        }
	        return maxListeners;
	      }
	      return this.DEFAULT_MAX_LISTENERS;
	    }
	  }, {
	    key: "addMaxListeners",
	    value: function addMaxListeners(...args) {
	      const [target, eventName, increment] = this.destructMaxListenersArgs(...args);
	      const maxListeners = Math.max(this.getMaxListeners(target, eventName) + increment, 0);
	      if (Type.isStringFilled(eventName)) {
	        EventEmitter.setMaxListeners(target, eventName, maxListeners);
	      } else {
	        EventEmitter.setMaxListeners(target, maxListeners);
	      }
	      return maxListeners;
	    }
	    /**
	     * Increases max listeners count
	     *
	     * Event.EventEmitter.incrementMaxListeners() - adds one max listener for all events of global target
	     * Event.EventEmitter.incrementMaxListeners(3) - adds three max listeners for all events of global target
	     * Event.EventEmitter.incrementMaxListeners('onClose') - adds one max listener for onClose event of global target
	     * Event.EventEmitter.incrementMaxListeners('onClose', 2) - adds two max listeners for onClose event of global target
	     *
	     * Event.EventEmitter.incrementMaxListeners(obj) - adds one max listener for all events of 'obj' target
	     * Event.EventEmitter.incrementMaxListeners(obj, 3) - adds three max listeners for all events of 'obj' target
	     * Event.EventEmitter.incrementMaxListeners(obj, 'onClose') - adds one max listener for onClose event of 'obj' target
	     * Event.EventEmitter.incrementMaxListeners(obj, 'onClose', 2) - adds two max listeners for onClose event of 'obj' target
	     */
	  }, {
	    key: "incrementMaxListeners",
	    value: function incrementMaxListeners(...args) {
	      const [target, eventName, increment] = this.destructMaxListenersArgs(...args);
	      return this.addMaxListeners(target, eventName, Math.abs(increment));
	    }
	  }, {
	    key: "decrementMaxListeners",
	    value: function decrementMaxListeners(...args) {
	      const [target, eventName, increment] = this.destructMaxListenersArgs(...args);
	      return this.addMaxListeners(target, eventName, -Math.abs(increment));
	    }
	  }, {
	    key: "destructMaxListenersArgs",
	    value: function destructMaxListenersArgs(...args) {
	      let eventName = null;
	      let increment = 1;
	      let target = this.GLOBAL_TARGET;
	      if (args.length === 1) {
	        if (Type.isNumber(args[0])) {
	          increment = args[0];
	        } else if (Type.isString(args[0])) {
	          eventName = args[0];
	        } else {
	          target = args[0];
	        }
	      } else if (args.length === 2) {
	        if (Type.isString(args[0])) {
	          [eventName, increment] = args;
	        } else if (Type.isString(args[1])) {
	          [target, eventName] = args;
	        } else {
	          [target, increment] = args;
	        }
	      } else if (args.length >= 3) {
	        [target, eventName, increment] = args;
	      }
	      if (!Type.isObject(target)) {
	        throw new TypeError(`The "target" argument must be an object.`);
	      }
	      if (eventName !== null && !Type.isStringFilled(eventName)) {
	        throw new TypeError(`The "eventName" argument must be a string.`);
	      }
	      if (!Type.isNumber(increment)) {
	        throw new TypeError(`The value of "increment" must be a number.`);
	      }
	      return [target, eventName, increment];
	    }
	    /**
	     * Gets listeners list for a specified event
	     * @param {object} target
	     * @param {string} eventName
	     */
	  }, {
	    key: "getListeners",
	    value: function getListeners(target, eventName) {
	      if (Type.isString(target)) {
	        eventName = target;
	        target = this.GLOBAL_TARGET;
	      }
	      if (!Type.isObject(target)) {
	        throw new TypeError(`The "target" argument must be an object.`);
	      }
	      eventName = this.normalizeEventName(eventName);
	      if (!Type.isStringFilled(eventName)) {
	        throw new TypeError(`The "eventName" argument must be a string.`);
	      }
	      const targetInfo = eventStore.get(target);
	      if (!targetInfo) {
	        return new Map();
	      }
	      const fullEventName = this.resolveEventName(eventName, target);
	      return targetInfo.eventsMap.get(fullEventName) || new Map();
	    }
	  }, {
	    key: "registerAliases",
	    value: function registerAliases(aliases) {
	      aliases = this.normalizeAliases(aliases);
	      Object.keys(aliases).forEach(alias => {
	        aliasStore.set(alias, {
	          eventName: aliases[alias].eventName,
	          namespace: aliases[alias].namespace
	        });
	      });
	      EventEmitter.mergeEventAliases(aliases);
	    }
	    /**
	     * @private
	     * @param aliases
	     */
	  }, {
	    key: "normalizeAliases",
	    value: function normalizeAliases(aliases) {
	      if (!Type.isPlainObject(aliases)) {
	        throw new TypeError(`The "aliases" argument must be an object.`);
	      }
	      const result = Object.create(null);
	      for (let alias in aliases) {
	        if (!Type.isStringFilled(alias)) {
	          throw new TypeError(`The alias must be an non-empty string.`);
	        }
	        const options = aliases[alias];
	        if (!options || !Type.isStringFilled(options.eventName) || !Type.isStringFilled(options.namespace)) {
	          throw new TypeError(`The alias options must set the "eventName" and the "namespace".`);
	        }
	        alias = this.normalizeEventName(alias);
	        result[alias] = {
	          eventName: options.eventName,
	          namespace: options.namespace
	        };
	      }
	      return result;
	    }
	    /**
	     * @private
	     */
	  }, {
	    key: "mergeEventAliases",
	    value: function mergeEventAliases(aliases) {
	      const globalEvents = eventStore.get(this.GLOBAL_TARGET);
	      if (!globalEvents) {
	        return;
	      }
	      Object.keys(aliases).forEach(alias => {
	        const options = aliases[alias];
	        alias = this.normalizeEventName(alias);
	        const fullEventName = this.makeFullEventName(options.namespace, options.eventName);
	        const aliasListeners = globalEvents.eventsMap.get(alias);
	        if (aliasListeners) {
	          const listeners = globalEvents.eventsMap.get(fullEventName) || new Map();
	          globalEvents.eventsMap.set(fullEventName, new Map([...listeners, ...aliasListeners]));
	          globalEvents.eventsMap.delete(alias);
	        }
	        const aliasOnceListeners = globalEvents.onceMap.get(alias);
	        if (aliasOnceListeners) {
	          const onceListeners = globalEvents.onceMap.get(fullEventName) || new Map();
	          globalEvents.onceMap.set(fullEventName, new Map([...onceListeners, ...aliasOnceListeners]));
	          globalEvents.onceMap.delete(alias);
	        }
	        const aliasMaxListeners = globalEvents.eventsMaxListeners.get(alias);
	        if (aliasMaxListeners) {
	          const eventMaxListeners = globalEvents.eventsMaxListeners.get(fullEventName) || 0;
	          globalEvents.eventsMaxListeners.set(fullEventName, Math.max(eventMaxListeners, aliasMaxListeners));
	          globalEvents.eventsMaxListeners.delete(alias);
	        }
	      });
	    }
	    /**
	     * Returns true if the target is an instance of Event.EventEmitter
	     * @param {object} target
	     * @returns {boolean}
	     */
	  }, {
	    key: "isEventEmitter",
	    value: function isEventEmitter(target) {
	      return Type.isObject(target) && target[isEmitterProperty] === true;
	    }
	    /**
	     * @private
	     * @param {string} eventName
	     * @returns {string}
	     */
	  }, {
	    key: "normalizeEventName",
	    value: function normalizeEventName(eventName) {
	      if (!Type.isStringFilled(eventName)) {
	        return '';
	      }
	      return eventName.toLowerCase();
	    }
	    /**
	     * @private
	     */
	  }, {
	    key: "normalizeListener",
	    value: function normalizeListener(listener) {
	      if (Type.isString(listener)) {
	        listener = Reflection.getClass(listener);
	      }
	      if (!Type.isFunction(listener)) {
	        throw new TypeError(`The "listener" argument must be of type Function. Received type ${typeof listener}.`);
	      }
	      return listener;
	    }
	    /**
	     * @private
	     * @param eventName
	     * @param target
	     * @param useGlobalNaming
	     * @returns {string}
	     */
	  }, {
	    key: "resolveEventName",
	    value: function resolveEventName(eventName, target, useGlobalNaming = false) {
	      eventName = this.normalizeEventName(eventName);
	      if (!Type.isStringFilled(eventName)) {
	        return '';
	      }
	      if (this.isEventEmitter(target) && useGlobalNaming !== true) {
	        if (target.getEventNamespace() !== null && eventName.includes('.')) {
	          console.warn(`Possible the wrong event name "${eventName}".`);
	        }
	        eventName = target.getFullEventName(eventName);
	      } else if (aliasStore.has(eventName)) {
	        const {
	          namespace,
	          eventName: actualEventName
	        } = aliasStore.get(eventName);
	        eventName = this.makeFullEventName(namespace, actualEventName);
	      }
	      return eventName;
	    }
	    /**
	     * @private
	     * @param {string} namespace
	     * @param {string} eventName
	     * @returns {string}
	     */
	  }, {
	    key: "makeFullEventName",
	    value: function makeFullEventName(namespace, eventName) {
	      const fullName = Type.isStringFilled(namespace) ? `${namespace}:${eventName}` : eventName;
	      return Type.isStringFilled(fullName) ? fullName.toLowerCase() : '';
	    }
	  }]);
	  return EventEmitter;
	}();
	babelHelpers.defineProperty(EventEmitter, "GLOBAL_TARGET", globalTarget);
	babelHelpers.defineProperty(EventEmitter, "DEFAULT_MAX_LISTENERS", eventStore.getDefaultMaxListeners());
	babelHelpers.defineProperty(EventEmitter, "sequenceValue", 1);

	let stack = [];
	/**
	 * For compatibility only
	 * @type {boolean}
	 */
	// eslint-disable-next-line
	exports.isReady = false;
	function ready(handler) {
	  if (!Type.isFunction(handler)) {
	    return;
	  }
	  if (exports.isReady) {
	    handler();
	  } else {
	    stack.push(handler);
	  }
	}
	bindOnce(document, 'DOMContentLoaded', () => {
	  exports.isReady = true;
	  stack.forEach(handler => {
	    handler();
	  });
	  stack = [];
	});

	/**
	 * @memberOf BX
	 */
	let Event = function Event() {
	  babelHelpers.classCallCheck(this, Event);
	};
	babelHelpers.defineProperty(Event, "bind", bind);
	babelHelpers.defineProperty(Event, "bindOnce", bindOnce);
	babelHelpers.defineProperty(Event, "unbind", unbind);
	babelHelpers.defineProperty(Event, "unbindAll", unbindAll);
	babelHelpers.defineProperty(Event, "ready", ready);
	babelHelpers.defineProperty(Event, "EventEmitter", EventEmitter);
	babelHelpers.defineProperty(Event, "BaseEvent", BaseEvent);

	function encodeAttributeValue(value) {
	  if (Type.isPlainObject(value) || Type.isArray(value)) {
	    return JSON.stringify(value);
	  }
	  return Text.encode(Text.decode(value));
	}

	function decodeAttributeValue(value) {
	  if (Type.isString(value)) {
	    const decodedValue = Text.decode(value);
	    let result;
	    try {
	      result = JSON.parse(decodedValue);
	    } catch (e) {
	      result = decodedValue;
	    }
	    if (result === decodedValue) {
	      if (/^[\d.]+[.]?\d+$/.test(result)) {
	        return Number(result);
	      }
	    }
	    if (result === 'true' || result === 'false') {
	      return Boolean(result);
	    }
	    return result;
	  }
	  return value;
	}

	function getPageScroll() {
	  const {
	    documentElement,
	    body
	  } = document;
	  const scrollTop = Math.max(window.pageYOffset || 0, documentElement ? documentElement.scrollTop : 0, body ? body.scrollTop : 0);
	  const scrollLeft = Math.max(window.pageXOffset || 0, documentElement ? documentElement.scrollLeft : 0, body ? body.scrollLeft : 0);
	  return {
	    scrollTop,
	    scrollLeft
	  };
	}

	/**
	 * @memberOf BX
	 */
	let Dom = /*#__PURE__*/function () {
	  function Dom() {
	    babelHelpers.classCallCheck(this, Dom);
	  }
	  babelHelpers.createClass(Dom, null, [{
	    key: "replace",
	    /**
	     * Replaces old html element to new html element
	     * @param oldElement
	     * @param newElement
	     */
	    value: function replace(oldElement, newElement) {
	      if (Type.isDomNode(oldElement) && Type.isDomNode(newElement)) {
	        if (Type.isDomNode(oldElement.parentNode)) {
	          oldElement.parentNode.replaceChild(newElement, oldElement);
	        }
	      }
	    }
	    /**
	     * Removes element
	     * @param element
	     */
	  }, {
	    key: "remove",
	    value: function remove(element) {
	      if (Type.isDomNode(element) && Type.isDomNode(element.parentNode)) {
	        element.parentNode.removeChild(element);
	      }
	    }
	    /**
	     * Cleans element
	     * @param element
	     */
	  }, {
	    key: "clean",
	    value: function clean(element) {
	      if (Type.isDomNode(element)) {
	        while (element.childNodes.length > 0) {
	          element.removeChild(element.firstChild);
	        }
	        return;
	      }
	      if (Type.isString(element)) {
	        Dom.clean(document.getElementById(element));
	      }
	    }
	    /**
	     * Inserts element before target element
	     * @param current
	     * @param target
	     */
	  }, {
	    key: "insertBefore",
	    value: function insertBefore(current, target) {
	      if (Type.isDomNode(current) && Type.isDomNode(target)) {
	        if (Type.isDomNode(target.parentNode)) {
	          target.parentNode.insertBefore(current, target);
	        }
	      }
	    }
	    /**
	     * Inserts element after target element
	     * @param current
	     * @param target
	     */
	  }, {
	    key: "insertAfter",
	    value: function insertAfter(current, target) {
	      if (Type.isDomNode(current) && Type.isDomNode(target)) {
	        if (Type.isDomNode(target.parentNode)) {
	          const parent = target.parentNode;
	          if (Type.isDomNode(target.nextSibling)) {
	            parent.insertBefore(current, target.nextSibling);
	            return;
	          }
	          parent.appendChild(current);
	        }
	      }
	    }
	    /**
	     * Appends element to target element
	     * @param current
	     * @param target
	     */
	  }, {
	    key: "append",
	    value: function append(current, target) {
	      if (Type.isDomNode(current) && Type.isDomNode(target)) {
	        target.appendChild(current);
	      }
	    }
	    /**
	     * Prepends element to target element
	     * @param current
	     * @param target
	     */
	  }, {
	    key: "prepend",
	    value: function prepend(current, target) {
	      if (Type.isDomNode(current) && Type.isDomNode(target)) {
	        if (Type.isDomNode(target.firstChild)) {
	          target.insertBefore(current, target.firstChild);
	          return;
	        }
	        Dom.append(current, target);
	      }
	    }
	    /**
	     * Checks that element contains class name or class names
	     * @param element
	     * @param className
	     * @return {Boolean}
	     */
	  }, {
	    key: "hasClass",
	    value: function hasClass(element, className) {
	      if (Type.isElementNode(element)) {
	        if (Type.isString(className)) {
	          const preparedClassName = className.trim();
	          if (preparedClassName.length > 0) {
	            if (preparedClassName.includes(' ')) {
	              return preparedClassName.split(' ').every(name => Dom.hasClass(element, name));
	            }
	            if ('classList' in element) {
	              return element.classList.contains(preparedClassName);
	            }
	            if (Type.isObject(element.className) && Type.isString(element.className.baseVal)) {
	              return element.getAttribute('class').split(' ').some(name => name === preparedClassName);
	            }
	          }
	        }
	        if (Type.isArray(className) && className.length > 0) {
	          return className.every(name => Dom.hasClass(element, name));
	        }
	      }
	      return false;
	    }
	    /**
	     * Adds class name
	     * @param element
	     * @param className
	     */
	  }, {
	    key: "addClass",
	    value: function addClass(element, className) {
	      if (Type.isElementNode(element)) {
	        if (Type.isString(className)) {
	          const preparedClassName = className.trim();
	          if (preparedClassName.length > 0) {
	            if (preparedClassName.includes(' ')) {
	              Dom.addClass(element, preparedClassName.split(' '));
	              return;
	            }
	            if ('classList' in element) {
	              element.classList.add(preparedClassName);
	              return;
	            }
	            if (Type.isObject(element.className) && Type.isString(element.className.baseVal)) {
	              if (element.className.baseVal === '') {
	                element.className.baseVal = preparedClassName;
	                return;
	              }
	              const names = element.className.baseVal.split(' ');
	              if (!names.includes(preparedClassName)) {
	                names.push(preparedClassName);
	                element.className.baseVal = names.join(' ').trim();
	                return;
	              }
	            }
	            return;
	          }
	        }
	        if (Type.isArray(className)) {
	          className.forEach(name => Dom.addClass(element, name));
	        }
	      }
	    }
	    /**
	     * Removes class name
	     * @param element
	     * @param className
	     */
	  }, {
	    key: "removeClass",
	    value: function removeClass(element, className) {
	      if (Type.isElementNode(element)) {
	        if (Type.isString(className)) {
	          const preparedClassName = className.trim();
	          if (preparedClassName.length > 0) {
	            if (preparedClassName.includes(' ')) {
	              Dom.removeClass(element, preparedClassName.split(' '));
	              return;
	            }
	            if ('classList' in element) {
	              element.classList.remove(preparedClassName);
	              return;
	            }
	            if (Type.isObject(element.className) && Type.isString(element.className.baseVal)) {
	              const names = element.className.baseVal.split(' ').filter(name => name !== preparedClassName);
	              element.className.baseVal = names.join(' ');
	              return;
	            }
	          }
	        }
	        if (Type.isArray(className)) {
	          className.forEach(name => Dom.removeClass(element, name));
	        }
	      }
	    }
	    /**
	     * Toggles class name
	     * @param element
	     * @param className
	     */
	  }, {
	    key: "toggleClass",
	    value: function toggleClass(element, className) {
	      if (Type.isElementNode(element)) {
	        if (Type.isString(className)) {
	          const preparedClassName = className.trim();
	          if (preparedClassName.length > 0) {
	            if (preparedClassName.includes(' ')) {
	              Dom.toggleClass(element, preparedClassName.split(' '));
	              return;
	            }
	            element.classList.toggle(preparedClassName);
	            return;
	          }
	        }
	        if (Type.isArray(className)) {
	          className.forEach(name => Dom.toggleClass(element, name));
	        }
	      }
	    }
	    /**
	     * Styles element
	     */
	  }, {
	    key: "style",
	    value: function style(element, prop, value) {
	      if (Type.isElementNode(element)) {
	        if (Type.isNull(prop)) {
	          element.removeAttribute('style');
	          return element;
	        }
	        if (Type.isPlainObject(prop)) {
	          Object.entries(prop).forEach(item => {
	            const [currentKey, currentValue] = item;
	            Dom.style(element, currentKey, currentValue);
	          });
	          return element;
	        }
	        if (Type.isString(prop)) {
	          if (Type.isUndefined(value) && element.nodeType !== Node.DOCUMENT_NODE) {
	            const computedStyle = getComputedStyle(element);
	            if (prop in computedStyle) {
	              return computedStyle[prop];
	            }
	            return computedStyle.getPropertyValue(prop);
	          }
	          if (Type.isNull(value) || value === '' || value === 'null') {
	            if (String(prop).startsWith('--')) {
	              // eslint-disable-next-line
	              element.style.removeProperty(prop);
	              return element;
	            }

	            // eslint-disable-next-line
	            element.style[prop] = '';
	            return element;
	          }
	          if (Type.isString(value) || Type.isNumber(value)) {
	            if (String(prop).startsWith('--')) {
	              // eslint-disable-next-line
	              element.style.setProperty(prop, value);
	              return element;
	            }

	            // eslint-disable-next-line
	            element.style[prop] = value;
	            return element;
	          }
	        }
	      }
	      return null;
	    }
	    /**
	     * Adjusts element
	     * @param target
	     * @param data
	     * @return {*}
	     */
	  }, {
	    key: "adjust",
	    value: function adjust(target, data = {}) {
	      if (!target.nodeType) {
	        return null;
	      }
	      let element = target;
	      if (target.nodeType === Node.DOCUMENT_NODE) {
	        element = target.body;
	      }
	      if (Type.isPlainObject(data)) {
	        if (Type.isPlainObject(data.attrs)) {
	          Object.keys(data.attrs).forEach(key => {
	            if (key === 'class' || key.toLowerCase() === 'classname') {
	              element.className = data.attrs[key];
	              return;
	            }

	            // eslint-disable-next-line
	            if (data.attrs[key] == '') {
	              element.removeAttribute(key);
	              return;
	            }
	            element.setAttribute(key, data.attrs[key]);
	          });
	        }
	        if (Type.isPlainObject(data.style)) {
	          Dom.style(element, data.style);
	        }
	        if (Type.isPlainObject(data.props)) {
	          Object.keys(data.props).forEach(key => {
	            element[key] = data.props[key];
	          });
	        }
	        if (Type.isPlainObject(data.events)) {
	          Object.keys(data.events).forEach(key => {
	            Event.bind(element, key, data.events[key]);
	          });
	        }
	        if (Type.isPlainObject(data.dataset)) {
	          Object.keys(data.dataset).forEach(key => {
	            element.dataset[key] = data.dataset[key];
	          });
	        }
	        if (Type.isString(data.children)) {
	          data.children = [data.children];
	        }
	        if (Type.isArray(data.children) && data.children.length > 0) {
	          data.children.forEach(item => {
	            if (Type.isDomNode(item)) {
	              Dom.append(item, element);
	            }
	            if (Type.isString(item)) {
	              element.insertAdjacentHTML('beforeend', item);
	            }
	          });
	          return element;
	        }
	        if ('text' in data && !Type.isNil(data.text)) {
	          element.innerText = data.text;
	          return element;
	        }
	        if ('html' in data && !Type.isNil(data.html)) {
	          element.innerHTML = data.html;
	        }
	      }
	      return element;
	    }
	    /**
	     * Creates element
	     * @param tag
	     * @param data
	     * @param context
	     * @return {HTMLElement|HTMLBodyElement}
	     */
	  }, {
	    key: "create",
	    value: function create(tag, data = {}, context = document) {
	      let tagName = tag;
	      let options = data;
	      if (Type.isObjectLike(tag)) {
	        options = tag;
	        tagName = tag.tag;
	      }
	      return Dom.adjust(context.createElement(tagName), options);
	    }
	    /**
	     * Shows element
	     * @param element
	     */
	  }, {
	    key: "show",
	    value: function show(element) {
	      if (Type.isDomNode(element)) {
	        // eslint-disable-next-line
	        element.hidden = false;
	      }
	    }
	    /**
	     * Hides element
	     * @param element
	     */
	  }, {
	    key: "hide",
	    value: function hide(element) {
	      if (Type.isDomNode(element)) {
	        // eslint-disable-next-line
	        element.hidden = true;
	      }
	    }
	    /**
	     * Checks that element is shown
	     * @param element
	     * @return {*|boolean}
	     */
	  }, {
	    key: "isShown",
	    value: function isShown(element) {
	      return Type.isDomNode(element) && !element.hidden && element.style.getPropertyValue('display') !== 'none';
	    }
	    /**
	     * Toggles element visibility
	     * @param element
	     */
	  }, {
	    key: "toggle",
	    value: function toggle(element) {
	      if (Type.isDomNode(element)) {
	        if (Dom.isShown(element)) {
	          Dom.hide(element);
	        } else {
	          Dom.show(element);
	        }
	      }
	    }
	    /**
	     * Gets element position relative page
	     * @param {HTMLElement} element
	     * @return {DOMRect}
	     */
	  }, {
	    key: "getPosition",
	    value: function getPosition(element) {
	      if (Type.isDomNode(element)) {
	        const elementRect = element.getBoundingClientRect();
	        const {
	          scrollLeft,
	          scrollTop
	        } = getPageScroll();
	        return new DOMRect(elementRect.left + scrollLeft, elementRect.top + scrollTop, elementRect.width, elementRect.height);
	      }
	      return new DOMRect();
	    }
	    /**
	     * Gets element position relative specified element position
	     * @param {HTMLElement} element
	     * @param {HTMLElement} relationElement
	     * @return {DOMRect}
	     */
	  }, {
	    key: "getRelativePosition",
	    value: function getRelativePosition(element, relationElement) {
	      if (Type.isDomNode(element) && Type.isDomNode(relationElement)) {
	        const elementPosition = Dom.getPosition(element);
	        const relationElementPosition = Dom.getPosition(relationElement);
	        return new DOMRect(elementPosition.left - relationElementPosition.left, elementPosition.top - relationElementPosition.top, elementPosition.width, elementPosition.height);
	      }
	      return new DOMRect();
	    }
	  }, {
	    key: "attr",
	    value: function attr(element, _attr, value) {
	      if (Type.isElementNode(element)) {
	        if (Type.isString(_attr)) {
	          if (!Type.isNil(value)) {
	            return element.setAttribute(_attr, encodeAttributeValue(value));
	          }
	          if (Type.isNull(value)) {
	            return element.removeAttribute(_attr);
	          }
	          return decodeAttributeValue(element.getAttribute(_attr));
	        }
	        if (Type.isPlainObject(_attr)) {
	          return Object.entries(_attr).forEach(([attrKey, attrValue]) => {
	            Dom.attr(element, attrKey, attrValue);
	          });
	        }
	      }
	      return null;
	    }
	  }]);
	  return Dom;
	}();

	const UA = navigator.userAgent.toLowerCase();

	/**
	 * @memberOf BX
	 */
	let Browser = /*#__PURE__*/function () {
	  function Browser() {
	    babelHelpers.classCallCheck(this, Browser);
	  }
	  babelHelpers.createClass(Browser, null, [{
	    key: "isOpera",
	    value: function isOpera() {
	      return UA.includes('opera');
	    }
	  }, {
	    key: "isIE",
	    value: function isIE() {
	      return 'attachEvent' in document && !Browser.isOpera();
	    }
	  }, {
	    key: "isIE6",
	    value: function isIE6() {
	      return UA.includes('msie 6');
	    }
	  }, {
	    key: "isIE7",
	    value: function isIE7() {
	      return UA.includes('msie 7');
	    }
	  }, {
	    key: "isIE8",
	    value: function isIE8() {
	      return UA.includes('msie 8');
	    }
	  }, {
	    key: "isIE9",
	    value: function isIE9() {
	      return 'documentMode' in document && document.documentMode >= 9;
	    }
	  }, {
	    key: "isIE10",
	    value: function isIE10() {
	      return 'documentMode' in document && document.documentMode >= 10;
	    }
	  }, {
	    key: "isSafari",
	    value: function isSafari() {
	      return UA.includes('safari') && !UA.includes('chrome');
	    }
	  }, {
	    key: "isFirefox",
	    value: function isFirefox() {
	      return UA.includes('firefox');
	    }
	  }, {
	    key: "isChrome",
	    value: function isChrome() {
	      return UA.includes('chrome');
	    }
	  }, {
	    key: "detectIEVersion",
	    value: function detectIEVersion() {
	      if (Browser.isOpera() || Browser.isSafari() || Browser.isFirefox() || Browser.isChrome()) {
	        return -1;
	      }
	      let rv = -1;
	      if (!!window.MSStream && !window.ActiveXObject && 'ActiveXObject' in window) {
	        rv = 11;
	      } else if (Browser.isIE10()) {
	        rv = 10;
	      } else if (Browser.isIE9()) {
	        rv = 9;
	      } else if (Browser.isIE()) {
	        rv = 8;
	      }
	      if (rv === -1 || rv === 8) {
	        if (navigator.appName === 'Microsoft Internet Explorer') {
	          const re = new RegExp('MSIE ([0-9]+[.0-9]*)');
	          const res = navigator.userAgent.match(re);
	          if (Type.isArrayLike(res) && res.length > 0) {
	            rv = parseFloat(res[1]);
	          }
	        }
	        if (navigator.appName === 'Netscape') {
	          // Alternative check for IE 11
	          rv = 11;
	          const re = new RegExp('Trident/.*rv:([0-9]+[.0-9]*)');
	          if (re.exec(navigator.userAgent) != null) {
	            const res = navigator.userAgent.match(re);
	            if (Type.isArrayLike(res) && res.length > 0) {
	              rv = parseFloat(res[1]);
	            }
	          }
	        }
	      }
	      return rv;
	    }
	  }, {
	    key: "isIE11",
	    value: function isIE11() {
	      return Browser.detectIEVersion() >= 11;
	    }
	  }, {
	    key: "isMac",
	    value: function isMac() {
	      return UA.includes('macintosh');
	    }
	  }, {
	    key: "isWin",
	    value: function isWin() {
	      return UA.includes('windows');
	    }
	  }, {
	    key: "isLinux",
	    value: function isLinux() {
	      return UA.includes('linux') && !Browser.isAndroid();
	    }
	  }, {
	    key: "isAndroid",
	    value: function isAndroid() {
	      return UA.includes('android');
	    }
	  }, {
	    key: "isIPad",
	    value: function isIPad() {
	      return UA.includes('ipad;') || this.isMac() && this.isTouchDevice();
	    }
	  }, {
	    key: "isIPhone",
	    value: function isIPhone() {
	      return UA.includes('iphone;');
	    }
	  }, {
	    key: "isIOS",
	    value: function isIOS() {
	      return Browser.isIPad() || Browser.isIPhone();
	    }
	  }, {
	    key: "isMobile",
	    value: function isMobile() {
	      return Browser.isIPhone() || Browser.isIPad() || Browser.isAndroid() || UA.includes('mobile') || UA.includes('touch');
	    }
	  }, {
	    key: "isRetina",
	    value: function isRetina() {
	      return window.devicePixelRatio && window.devicePixelRatio >= 2;
	    }
	  }, {
	    key: "isTouchDevice",
	    value: function isTouchDevice() {
	      return 'ontouchstart' in window || navigator.maxTouchPoints > 0 || navigator.msMaxTouchPoints > 0;
	    }
	  }, {
	    key: "isDoctype",
	    value: function isDoctype(target) {
	      const doc = target || document;
	      if (doc.compatMode) {
	        return doc.compatMode === 'CSS1Compat';
	      }
	      return doc.documentElement && doc.documentElement.clientHeight;
	    }
	  }, {
	    key: "isLocalStorageSupported",
	    value: function isLocalStorageSupported() {
	      try {
	        localStorage.setItem('test', 'test');
	        localStorage.removeItem('test');
	        return true;
	      } catch (e) {
	        return false;
	      }
	    }
	  }, {
	    key: "addGlobalClass",
	    value: function addGlobalClass(target) {
	      let globalClass = 'bx-core';
	      target = Type.isElementNode(target) ? target : document.documentElement;
	      if (Dom.hasClass(target, globalClass)) {
	        return;
	      }
	      if (Browser.isIOS()) {
	        globalClass += ' bx-ios';
	      } else if (Browser.isWin()) {
	        globalClass += ' bx-win';
	      } else if (Browser.isMac()) {
	        globalClass += ' bx-mac';
	      } else if (Browser.isLinux()) {
	        globalClass += ' bx-linux';
	      } else if (Browser.isAndroid()) {
	        globalClass += ' bx-android';
	      }
	      globalClass += Browser.isMobile() ? ' bx-touch' : ' bx-no-touch';
	      globalClass += Browser.isRetina() ? ' bx-retina' : ' bx-no-retina';
	      if (/AppleWebKit/.test(navigator.userAgent)) {
	        globalClass += ' bx-chrome';
	      } else if (/Opera/.test(navigator.userAgent)) {
	        globalClass += ' bx-opera';
	      } else if (Browser.isFirefox()) {
	        globalClass += ' bx-firefox';
	      }
	      Dom.addClass(target, globalClass);
	    }
	  }, {
	    key: "detectAndroidVersion",
	    value: function detectAndroidVersion() {
	      const re = new RegExp('Android ([0-9]+[.0-9]*)');
	      if (re.exec(navigator.userAgent) != null) {
	        const res = navigator.userAgent.match(re);
	        if (Type.isArrayLike(res) && res.length > 0) {
	          return parseFloat(res[1]);
	        }
	      }
	      return 0;
	    }
	  }, {
	    key: "isPropertySupported",
	    value: function isPropertySupported(jsProperty, returnCSSName) {
	      if (jsProperty === '') {
	        return false;
	      }
	      function getCssName(propertyName) {
	        return propertyName.replace(/([A-Z])/g, (...args) => `-${args[1].toLowerCase()}`);
	      }
	      function getJsName(cssName) {
	        const reg = /(\\-([a-z]))/g;
	        if (reg.test(cssName)) {
	          return cssName.replace(reg, (...args) => args[2].toUpperCase());
	        }
	        return cssName;
	      }
	      const property = jsProperty.includes('-') ? getJsName(jsProperty) : jsProperty;
	      const bReturnCSSName = !!returnCSSName;
	      const ucProperty = property.charAt(0).toUpperCase() + property.slice(1);
	      const props = ['Webkit', 'Moz', 'O', 'ms'].join(`${ucProperty} `);
	      const properties = `${property} ${props} ${ucProperty}`.split(' ');
	      const obj = document.body || document.documentElement;
	      for (let i = 0; i < properties.length; i += 1) {
	        const prop = properties[i];
	        if (obj && 'style' in obj && prop in obj.style) {
	          const lowerProp = prop.substr(0, prop.length - property.length).toLowerCase();
	          const prefix = prop === property ? '' : `-${lowerProp}-`;
	          return bReturnCSSName ? prefix + getCssName(property) : prop;
	        }
	      }
	      return false;
	    }
	  }, {
	    key: "addGlobalFeatures",
	    value: function addGlobalFeatures(features) {
	      if (!Type.isArray(features)) {
	        return;
	      }
	      const classNames = [];
	      for (let i = 0; i < features.length; i += 1) {
	        const support = !!Browser.isPropertySupported(features[i]);
	        classNames.push(`bx-${support ? '' : 'no-'}${features[i].toLowerCase()}`);
	      }
	      Dom.addClass(document.documentElement, classNames.join(' '));
	    }
	  }]);
	  return Browser;
	}();

	let Cookie = /*#__PURE__*/function () {
	  function Cookie() {
	    babelHelpers.classCallCheck(this, Cookie);
	  }
	  babelHelpers.createClass(Cookie, null, [{
	    key: "getList",
	    /**
	     * Gets cookies list for current domain
	     * @return {object}
	     */
	    value: function getList() {
	      return document.cookie.split(';').map(item => item.split('=')).map(item => item.map(subItem => subItem.trim())).reduce((acc, item) => {
	        const [key, value] = item;
	        acc[decodeURIComponent(key)] = decodeURIComponent(value);
	        return acc;
	      }, {});
	    }
	    /**
	     * Gets cookie value
	     * @param {string} name
	     * @return {*}
	     */
	  }, {
	    key: "get",
	    value: function get(name) {
	      const cookiesList = Cookie.getList();
	      if (name in cookiesList) {
	        return cookiesList[name];
	      }
	      return undefined;
	    }
	    /**
	     * Sets cookie
	     * @param {string} name
	     * @param {*} value
	     * @param {object} [options]
	     */
	  }, {
	    key: "set",
	    value: function set(name, value, options = {}) {
	      const attributes = {
	        expires: '',
	        ...options
	      };
	      if (Type.isNumber(attributes.expires)) {
	        const now = +new Date();
	        const days = attributes.expires;
	        const dayInMs = 864e+5;
	        attributes.expires = new Date(now + days * dayInMs);
	      }
	      if (Type.isDate(attributes.expires)) {
	        attributes.expires = attributes.expires.toUTCString();
	      }
	      const safeName = decodeURIComponent(String(name)).replace(/%(23|24|26|2B|5E|60|7C)/g, decodeURIComponent).replace(/[()]/g, escape);
	      const safeValue = encodeURIComponent(String(value)).replace(/%(23|24|26|2B|3A|3C|3E|3D|2F|3F|40|5B|5D|5E|60|7B|7D|7C)/g, decodeURIComponent);
	      const stringifiedAttributes = Object.keys(attributes).reduce((acc, key) => {
	        const attributeValue = attributes[key];
	        if (!attributeValue) {
	          return acc;
	        }
	        if (attributeValue === true) {
	          return `${acc}; ${key}`;
	        }

	        /**
	         * Considers RFC 6265 section 5.2:
	         * ...
	         * 3. If the remaining unparsed-attributes contains a %x3B (';')
	         * character:
	         * Consume the characters of the unparsed-attributes up to,
	         * not including, the first %x3B (';') character.
	         */
	        return `${acc}; ${key}=${attributeValue.split(';')[0]}`;
	      }, '');
	      document.cookie = `${safeName}=${safeValue}${stringifiedAttributes}`;
	    }
	    /**
	     * Removes cookie
	     * @param {string} name
	     * @param {object} [options]
	     */
	  }, {
	    key: "remove",
	    value: function remove(name, options = {}) {
	      Cookie.set(name, '', {
	        ...options,
	        expires: -1
	      });
	    }
	  }]);
	  return Cookie;
	}();

	function objectToFormData(source, formData = new FormData(), pre = null) {
	  if (Type.isUndefined(source)) {
	    return formData;
	  }
	  if (Type.isNull(source)) {
	    formData.append(pre, '');
	  } else if (Type.isArray(source)) {
	    if (!source.length) {
	      const key = `${pre}[]`;
	      formData.append(key, '');
	    } else {
	      source.forEach((value, index) => {
	        const key = `${pre}[${index}]`;
	        objectToFormData(value, formData, key);
	      });
	    }
	  } else if (Type.isDate(source)) {
	    formData.append(pre, source.toISOString());
	  } else if (Type.isObject(source) && !Type.isFile(source) && !Type.isBlob(source)) {
	    Object.keys(source).forEach(property => {
	      const value = source[property];
	      let preparedProperty = property;
	      if (Type.isArray(value)) {
	        while (preparedProperty.length > 2 && preparedProperty.lastIndexOf('[]') === preparedProperty.length - 2) {
	          preparedProperty = preparedProperty.substring(0, preparedProperty.length - 2);
	        }
	      }
	      const key = pre ? `${pre}[${preparedProperty}]` : preparedProperty;
	      objectToFormData(value, formData, key);
	    });
	  } else {
	    formData.append(pre, source);
	  }
	  return formData;
	}

	let Data = /*#__PURE__*/function () {
	  function Data() {
	    babelHelpers.classCallCheck(this, Data);
	  }
	  babelHelpers.createClass(Data, null, [{
	    key: "convertObjectToFormData",
	    /**
	     * Converts object to FormData
	     * @param source
	     * @return {FormData}
	     */
	    value: function convertObjectToFormData(source) {
	      return objectToFormData(source);
	    }
	  }]);
	  return Data;
	}();

	/**
	 * @memberOf BX
	 */
	let Http = function Http() {
	  babelHelpers.classCallCheck(this, Http);
	};
	babelHelpers.defineProperty(Http, "Cookie", Cookie);
	babelHelpers.defineProperty(Http, "Data", Data);

	function message(value) {
	  if (Type.isString(value)) {
	    if (Type.isNil(message[value])) {
	      // eslint-disable-next-line
	      EventEmitter.emit('onBXMessageNotFound', new BaseEvent({
	        compatData: [value]
	      }));
	      if (Type.isNil(message[value])) {
	        Runtime.debug(`message undefined: ${value}`);
	        message[value] = '';
	      }
	    }
	  }
	  if (Type.isPlainObject(value)) {
	    Object.keys(value).forEach(key => {
	      message[key] = value[key];
	    });
	  }
	  return message[value];
	}
	if (!Type.isNil(window.BX) && Type.isFunction(window.BX.message)) {
	  Object.keys(window.BX.message).forEach(key => {
	    message({
	      [key]: window.BX.message[key]
	    });
	  });
	}

	/**
	 * Implements interface for works with language messages
	 * @memberOf BX
	 */
	let Loc = /*#__PURE__*/function () {
	  function Loc() {
	    babelHelpers.classCallCheck(this, Loc);
	  }
	  babelHelpers.createClass(Loc, null, [{
	    key: "getMessage",
	    /**
	     * Gets message by id
	     * @param {string} messageId
	     * @param {object} replacements
	     * @return {?string}
	     */
	    value: function getMessage(messageId, replacements = null) {
	      let mess = message(messageId);
	      if (Type.isString(mess) && Type.isPlainObject(replacements)) {
	        const escape = str => String(str).replace(/[\\^$*+?.()|[\]{}]/g, '\\$&');
	        Object.keys(replacements).forEach(replacement => {
	          const globalRegexp = new RegExp(escape(replacement), 'gi');
	          mess = mess.replace(globalRegexp, () => {
	            return Type.isNil(replacements[replacement]) ? '' : String(replacements[replacement]);
	          });
	        });
	      }
	      return mess;
	    }
	  }, {
	    key: "hasMessage",
	    value: function hasMessage(messageId) {
	      return Type.isString(messageId) && !Type.isNil(message[messageId]);
	    }
	    /**
	     * Sets message or messages
	     * @param {string | Object<string, string>} id
	     * @param {string} [value]
	     */
	  }, {
	    key: "setMessage",
	    value: function setMessage(id, value) {
	      if (Type.isString(id) && Type.isString(value)) {
	        message({
	          [id]: value
	        });
	      }
	      if (Type.isObject(id)) {
	        message(id);
	      }
	    }
	    /**
	     * Gets plural message by id and number
	     * @param {string} messageId
	     * @param {number} value
	     * @param {object} [replacements]
	     * @return {?string}
	     */
	  }, {
	    key: "getMessagePlural",
	    value: function getMessagePlural(messageId, value, replacements = null) {
	      let result = '';
	      if (Type.isNumber(value)) {
	        if (this.hasMessage(`${messageId}_PLURAL_${this.getPluralForm(value)}`)) {
	          result = this.getMessage(`${messageId}_PLURAL_${this.getPluralForm(value)}`, replacements);
	        } else {
	          result = this.getMessage(`${messageId}_PLURAL_1`, replacements);
	        }
	      } else {
	        result = this.getMessage(messageId, replacements);
	      }
	      return result;
	    }
	    /**
	     * Gets language plural form id by number
	     * see http://docs.translatehouse.org/projects/localization-guide/en/latest/l10n/pluralforms.html
	     * @param {number} value
	     * @param {string} [languageId]
	     * @return {?number}
	     */
	  }, {
	    key: "getPluralForm",
	    value: function getPluralForm(value, languageId) {
	      let pluralForm;
	      if (!Type.isStringFilled(languageId)) {
	        languageId = message('LANGUAGE_ID');
	      }
	      if (value < 0) {
	        value = -1 * value;
	      }
	      switch (languageId) {
	        case 'ar':
	          pluralForm = value !== 1 ? 1 : 0;
	          /*
	          				if (value === 0)
	          				{
	          					pluralForm = 0;
	          				}
	          				else if (value === 1)
	          				{
	          					pluralForm = 1;
	          				}
	          				else if (value === 2)
	          				{
	          					pluralForm = 2;
	          				}
	          				else if (
	          					value % 100 >= 3
	          					&& value % 100 <= 10
	          				)
	          				{
	          					pluralForm = 3;
	          				}
	          				else if (value % 100 >= 11)
	          				{
	          					pluralForm = 4;
	          				}
	          				else
	          				{
	          					pluralForm = 5;
	          				}
	           */
	          break;
	        case 'br':
	        case 'fr':
	        case 'tr':
	          pluralForm = value > 1 ? 1 : 0;
	          break;
	        case 'de':
	        case 'en':
	        case 'hi':
	        case 'it':
	        case 'la':
	          pluralForm = value !== 1 ? 1 : 0;
	          break;
	        case 'ru':
	        case 'ua':
	          if (value % 10 === 1 && value % 100 !== 11) {
	            pluralForm = 0;
	          } else if (value % 10 >= 2 && value % 10 <= 4 && (value % 100 < 10 || value % 100 >= 20)) {
	            pluralForm = 1;
	          } else {
	            pluralForm = 2;
	          }
	          break;
	        case 'pl':
	          if (value === 1) {
	            pluralForm = 0;
	          } else if (value % 10 >= 2 && value % 10 <= 4 && (value % 100 < 10 || value % 100 >= 20)) {
	            pluralForm = 1;
	          } else {
	            pluralForm = 2;
	          }
	          break;
	        case 'id':
	        case 'ja':
	        case 'ms':
	        case 'sc':
	        case 'tc':
	        case 'th':
	        case 'vn':
	          pluralForm = 0;
	          break;
	        default:
	          pluralForm = 1;
	          break;
	      }
	      return pluralForm;
	    }
	  }]);
	  return Loc;
	}();

	const voidElements = ['area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input', 'link', 'meta', 'param', 'source', 'track', 'wbr'];
	function isVoidElement(element) {
	  return voidElements.includes(element);
	}

	const matchers = {
	  tag: /<[a-zA-Z0-9\-\!\/](?:"[^"]*"|'[^']*'|[^'">])*>|{{uid[0-9]+}}/g,
	  comment: /<!--(?!<!)[^\[>].*?-->/g,
	  tagName: /<\/?([^\s]+?)[/\s>]/,
	  attributes: /\s([\w\-_:.]+)\s?\n?=\s?\n?"([^"]+)?"|\s([\w\-_:.]+)\s?\n?=\s?\n?'([^']+)?'|\s([\w\-_:.]+)/gs,
	  placeholder: /{{uid[0-9]+}}/g
	};

	function parseTag(tag) {
	  const tagResult = {
	    type: 'tag',
	    name: '',
	    svg: false,
	    attrs: {},
	    children: [],
	    voidElement: false
	  };
	  if (tag.startsWith('<!--')) {
	    const endIndex = tag.indexOf('-->');
	    const openTagLength = '<!--'.length;
	    return {
	      type: 'comment',
	      content: endIndex !== -1 ? tag.slice(openTagLength, endIndex) : ''
	    };
	  }
	  const tagNameMatch = tag.match(matchers.tagName);
	  if (Type.isArrayFilled(tagNameMatch)) {
	    const [, tagName] = tagNameMatch;
	    tagResult.name = tagName;
	    tagResult.svg = tagName === 'svg';
	    tagResult.voidElement = isVoidElement(tagName) || tag.trim().endsWith('/>');
	  }
	  const reg = new RegExp(matchers.attributes);
	  for (;;) {
	    const result = reg.exec(tag);
	    if (!Type.isNil(result)) {
	      // Attributes with double quotes
	      const [, attrName, attrValue] = result;
	      if (!Type.isNil(attrName)) {
	        tagResult.attrs[attrName] = Type.isStringFilled(attrValue) ? attrValue : '';
	      } else {
	        // Attributes with single quotes
	        const [,,, attrName, attrValue] = result;
	        if (!Type.isNil(attrName)) {
	          tagResult.attrs[attrName] = Type.isStringFilled(attrValue) ? attrValue : '';
	        } else {
	          // Attributes without value
	          const [,,,,, attrName] = result;
	          tagResult.attrs[attrName] = '';
	        }
	      }
	    } else {
	      break;
	    }
	  }
	  return tagResult;
	}

	function parseText(input) {
	  const preparedText = input.replace(/[\n\r\t]$/, '');
	  const placeholders = preparedText.match(matchers.placeholder);
	  return preparedText.split(matchers.placeholder).reduce((acc, item, index) => {
	    if (Type.isStringFilled(item)) {
	      acc.push(...item.split(/\n/).reduce((textAcc, text) => {
	        const preparedItemText = text.replace(/[\t\r]/g, '');
	        if (Type.isStringFilled(preparedItemText)) {
	          textAcc.push({
	            type: 'text',
	            content: preparedItemText
	          });
	        }
	        return textAcc;
	      }, []));
	    }
	    if (placeholders && placeholders[index]) {
	      acc.push({
	        type: 'placeholder',
	        uid: parseInt(placeholders[index].replace(/{{uid|}}/, ''))
	      });
	    }
	    return acc;
	  }, []);
	}

	function parse(html, substitutions) {
	  const result = [];
	  if (html.indexOf('<') !== 0 && !html.startsWith('{{')) {
	    const end = html.indexOf('<');
	    result.push(...parseText(end === -1 ? html : html.slice(0, end)));
	  }
	  const commentsContent = [];
	  let commentIndex = -1;
	  html = html.replace(matchers.comment, tag => {
	    commentIndex += 1;
	    commentsContent.push(tag.replace(/^<!--|-->$/g, ''));
	    return `<!--{{cUid${commentIndex}}}-->`;
	  });
	  const arr = [];
	  let level = -1;
	  let current;
	  html.replace(matchers.tag, (tag, index) => {
	    const start = index + tag.length;
	    const nextChar = html.charAt(start);
	    let parent;
	    if (tag.startsWith('<!--')) {
	      const comment = parseTag(tag, substitutions);
	      comment.content = commentsContent[tag.replace(/<!--{{cUid|}}-->/g, '')];
	      if (level < 0) {
	        result.push(comment);
	        return result;
	      }
	      parent = arr[level];
	      parent.children.push(comment);
	      return result;
	    }
	    if (tag.startsWith('{{')) {
	      const [placeholder] = parseText(tag);
	      if (level < 0) {
	        result.push(placeholder);
	        return result;
	      }
	      parent = arr[level];
	      parent.children.push(placeholder);
	      return result;
	    }
	    if (!tag.startsWith('</')) {
	      level++;
	      current = parseTag(tag, substitutions);
	      if (!current.voidElement && nextChar && nextChar !== '<') {
	        current.children.push(...parseText(html.slice(start, html.indexOf('<', start))));
	      }
	      if (level === 0) {
	        result.push(current);
	      }
	      parent = arr[level - 1];
	      if (parent) {
	        if (!current.svg) {
	          current.svg = parent.svg;
	        }
	        parent.children.push(current);
	      }
	      arr[level] = current;
	    }
	    if (tag.startsWith('</') || current.voidElement) {
	      if (level > -1 && (current.voidElement || current.name === tag.slice(2, -1))) {
	        level--;
	        current = level === -1 ? result : arr[level];
	      }
	      if (nextChar && nextChar !== '<') {
	        parent = level === -1 ? result : arr[level].children;
	        const end = html.indexOf('<', start);
	        const content = html.slice(start, end === -1 ? undefined : end);
	        if (end > -1 && level + parent.length >= 0 || content !== ' ') {
	          parent.push(...parseText(content));
	        }
	      }
	    }
	  });
	  return result;
	}

	const appendElement = (current, target) => {
	  if (Type.isDomNode(current) && Type.isDomNode(target)) {
	    if (target.nodeName !== 'TEMPLATE') {
	      Dom.append(current, target);
	    } else {
	      // eslint-disable-next-line bitrix-rules/no-native-dom-methods
	      target.content.append(current);
	    }
	  }
	};
	function renderNode(options) {
	  const {
	    node,
	    parentElement,
	    substitutions,
	    refs = []
	  } = options;
	  if (node.type === 'tag') {
	    const element = (() => {
	      if (node.svg) {
	        return document.createElementNS('http://www.w3.org/2000/svg', node.name);
	      }
	      return document.createElement(node.name);
	    })();
	    if (Object.hasOwn(node.attrs, 'ref')) {
	      refs.push([node.attrs.ref, element]);
	      delete node.attrs.ref;
	    }
	    Object.entries(node.attrs).forEach(([key, value]) => {
	      if (key.startsWith('on') && new RegExp(matchers.placeholder).test(value)) {
	        const substitution = substitutions[parseInt(value.replace(/{{uid|}}/, '')) - 1];
	        if (Type.isFunction(substitution)) {
	          const bindFunctionName = key.endsWith('once') ? 'bindOnce' : 'bind';
	          Event[bindFunctionName](element, key.replace(/^on|once$/g, ''), substitution);
	        } else {
	          element.setAttribute(key, substitution);
	        }
	      } else {
	        if (new RegExp(matchers.placeholder).test(value)) {
	          const preparedValue = value.split(/{{|}}/).reduce((acc, item) => {
	            if (item.startsWith('uid')) {
	              const substitution = substitutions[parseInt(item.replace('uid', '')) - 1];
	              return `${acc}${substitution}`;
	            }
	            return `${acc}${item}`;
	          }, '');
	          element.setAttribute(key, preparedValue);
	        } else {
	          element.setAttribute(key, Text.decode(value));
	        }
	      }
	    });
	    node.children.forEach(childNode => {
	      const result = renderNode({
	        node: childNode,
	        parentElement: element,
	        substitutions,
	        refs
	      });
	      if (Type.isArray(result)) {
	        result.forEach(subChildElement => {
	          appendElement(subChildElement, element);
	        });
	      } else {
	        appendElement(result, element);
	      }
	    });
	    return element;
	  }
	  if (node.type === 'comment') {
	    return document.createComment(node.content);
	  }
	  if (node.type === 'text') {
	    if (parentElement) {
	      if (parentElement.nodeName !== 'TEMPLATE') {
	        parentElement.insertAdjacentHTML('beforeend', node.content);
	      } else {
	        parentElement.content.append(node.content);
	      }
	      return;
	    }
	    return document.createTextNode(node.content);
	  }
	  if (node.type === 'placeholder') {
	    return substitutions[node.uid - 1];
	  }
	}

	function render(sections, ...substitutions) {
	  const html = sections.reduce((acc, item, index) => {
	    if (index > 0) {
	      const substitution = substitutions[index - 1];
	      if (Type.isString(substitution) || Type.isNumber(substitution)) {
	        return `${acc}${substitution}${item}`;
	      }
	      return `${acc}{{uid${index}}}${item}`;
	    }
	    return acc;
	  }, sections[0]).replace(/^[\r\n\t\s]+/gm, '').replace(/>[\n]+/g, '>').replace(/[}][\n]+/g, '}');
	  const ast = parse(html);
	  if (ast.length === 1) {
	    const refs = [];
	    const renderedNode = renderNode({
	      node: ast[0],
	      substitutions,
	      refs
	    });
	    if (Type.isArrayFilled(refs)) {
	      return Object.fromEntries([['root', renderedNode], ...refs]);
	    }
	    return renderedNode;
	  }
	  if (ast.length > 1) {
	    const refs = [];
	    const renderedNodes = ast.map(node => {
	      return renderNode({
	        node,
	        substitutions,
	        refs
	      });
	    });
	    if (Type.isArrayFilled(refs)) {
	      return Object.fromEntries([['root', renderedNodes], ...refs]);
	    }
	    return renderedNodes;
	  }
	  return false;
	}

	function parseProps(sections, ...substitutions) {
	  return substitutions.reduce((acc, item, index) => {
	    const nextSectionIndex = index + 1;
	    if (!Type.isPlainObject(item) && !Type.isArray(item)) {
	      return acc + item + sections[nextSectionIndex];
	    }
	    return `${acc}__s${index}${sections[nextSectionIndex]}`;
	  }, sections[0]).replace(/[\r\t]/gm, '').split(';\n').map(item => item.replace(/\n/, '')).reduce((acc, item) => {
	    if (item !== '') {
	      const matches = item.match(/^[\w-. ]+:/);
	      const splitted = item.split(/^[\w-. ]+:/);
	      const key = matches[0].replace(':', '').trim();
	      const value = splitted[1].trim();
	      const substitutionPlaceholderExp = /^__s\d+/;
	      if (substitutionPlaceholderExp.test(value)) {
	        acc[key] = substitutions[value.replace('__s', '')];
	        return acc;
	      }
	      acc[key] = value;
	    }
	    return acc;
	  }, {});
	}

	/**
	 * @memberOf BX
	 */
	let Tag = /*#__PURE__*/function () {
	  function Tag() {
	    babelHelpers.classCallCheck(this, Tag);
	  }
	  babelHelpers.createClass(Tag, null, [{
	    key: "safe",
	    /**
	     * Encodes all substitutions
	     * @param sections
	     * @param substitutions
	     * @return {string}
	     */
	    value: function safe(sections, ...substitutions) {
	      return substitutions.reduce((acc, item, index) => acc + Text.encode(item) + sections[index + 1], sections[0]);
	    }
	    /**
	     * Decodes all substitutions
	     * @param sections
	     * @param substitutions
	     * @return {string}
	     */
	  }, {
	    key: "unsafe",
	    value: function unsafe(sections, ...substitutions) {
	      return substitutions.reduce((acc, item, index) => acc + Text.decode(item) + sections[index + 1], sections[0]);
	    }
	    /**
	     * Adds styles to specified element
	     * @param {HTMLElement} element
	     * @return {Function}
	     */
	  }, {
	    key: "style",
	    value: function style(element) {
	      if (!Type.isDomNode(element)) {
	        throw new Error('element is not HTMLElement');
	      }
	      return function styleTagHandler(...args) {
	        Dom.style(element, parseProps(...args));
	      };
	    }
	    /**
	     * Replace all messages identifiers to real messages
	     * @param sections
	     * @param substitutions
	     * @return {string}
	     */
	  }, {
	    key: "message",
	    value: function message(sections, ...substitutions) {
	      return substitutions.reduce((acc, item, index) => acc + Loc.getMessage(item) + sections[index + 1], sections[0]);
	    }
	  }, {
	    key: "attrs",
	    /**
	     * Adds attributes to specified element
	     * @param element
	     * @return {Function}
	     */
	    value: function attrs(element) {
	      if (!Type.isDomNode(element)) {
	        throw new Error('element is not HTMLElement');
	      }
	      return function attrsTagHandler(...args) {
	        Dom.attr(element, parseProps(...args));
	      };
	    }
	  }]);
	  return Tag;
	}();
	babelHelpers.defineProperty(Tag, "render", render);
	babelHelpers.defineProperty(Tag, "attr", Tag.attrs);

	function getParser(format) {
	  switch (format) {
	    case 'index':
	      return (sourceKey, value, accumulator) => {
	        const result = /\[(\w*)\]$/.exec(sourceKey);
	        const key = sourceKey.replace(/\[\w*\]$/, '');
	        if (Type.isNil(result)) {
	          accumulator[key] = value;
	          return;
	        }
	        if (Type.isUndefined(accumulator[key])) {
	          accumulator[key] = {};
	        }
	        accumulator[key][result[1]] = value;
	      };
	    case 'bracket':
	      return (sourceKey, value, accumulator) => {
	        const result = /(\[\])$/.exec(sourceKey);
	        const key = sourceKey.replace(/\[\]$/, '');
	        if (Type.isNil(result)) {
	          accumulator[key] = value;
	          return;
	        }
	        if (Type.isUndefined(accumulator[key])) {
	          accumulator[key] = [value];
	          return;
	        }
	        accumulator[key] = [].concat(accumulator[key], value);
	      };
	    default:
	      return (sourceKey, value, accumulator) => {
	        const key = sourceKey.replace(/\[\]$/, '');
	        accumulator[key] = value;
	      };
	  }
	}
	function getKeyFormat(key) {
	  if (/^\w+\[([\w]+)\]$/.test(key)) {
	    return 'index';
	  }
	  if (/^\w+\[\]$/.test(key)) {
	    return 'bracket';
	  }
	  return 'default';
	}
	function isAllowedKey(key) {
	  return !String(key).startsWith('__proto__');
	}
	function parseQuery(input) {
	  if (!Type.isString(input)) {
	    return {};
	  }
	  const url = input.trim().replace(/^[?#&]/, '');
	  if (!url) {
	    return {};
	  }
	  return {
	    ...url.split('&').reduce((acc, param) => {
	      const [key, value] = param.replace(/\+/g, ' ').split('=');
	      if (isAllowedKey(key)) {
	        const keyFormat = getKeyFormat(key);
	        const formatter = getParser(keyFormat);
	        formatter(key, value, acc);
	      }
	      return acc;
	    }, Object.create(null))
	  };
	}
	const urlExp = /^((\w+):)?(\/\/((\w+)?(:(\w+))?@)?([^\/\?:]+)(:(\d+))?)?(\/?([^\/\?#][^\?#]*)?)?(\?([^#]+))?(#(\w*))?/;
	function parseUrl(url) {
	  const result = url.match(urlExp);
	  if (Type.isArray(result)) {
	    const queryParams = parseQuery(result[14]);
	    return {
	      useShort: /^\/\//.test(url),
	      href: result[0] || '',
	      schema: result[2] || '',
	      host: result[8] || '',
	      port: result[10] || '',
	      path: result[11] || '',
	      query: result[14] || '',
	      queryParams,
	      hash: result[16] || '',
	      username: result[5] || '',
	      password: result[7] || '',
	      origin: result[8] || ''
	    };
	  }
	  return {};
	}

	function buildQueryString(params = {}) {
	  const queryString = Object.keys(params).reduce((acc, key) => {
	    if (Type.isArray(params[key])) {
	      params[key].forEach(paramValue => {
	        acc.push(`${key}[]=${paramValue}`);
	      }, '');
	    }
	    if (Type.isPlainObject(params[key])) {
	      Object.keys(params[key]).forEach(paramIndex => {
	        acc.push(`${key}[${paramIndex}]=${params[key][paramIndex]}`);
	      }, '');
	    }
	    if (!Type.isObject(params[key]) && !Type.isArray(params[key])) {
	      acc.push(`${key}=${params[key]}`);
	    }
	    return acc;
	  }, []).join('&');
	  if (queryString.length > 0) {
	    return `?${queryString}`;
	  }
	  return queryString;
	}

	function prepareParamValue(value) {
	  if (Type.isArray(value)) {
	    return value.map(item => String(item));
	  }
	  if (Type.isPlainObject(value)) {
	    return {
	      ...value
	    };
	  }
	  return String(value);
	}

	const map = new WeakMap();

	/**
	 * Implements interface for works with URI
	 * @memberOf BX
	 */
	let Uri = /*#__PURE__*/function () {
	  babelHelpers.createClass(Uri, null, [{
	    key: "addParam",
	    value: function addParam(url, params = {}) {
	      return new Uri(url).setQueryParams(params).toString();
	    }
	  }, {
	    key: "removeParam",
	    value: function removeParam(url, params) {
	      const removableParams = Type.isArray(params) ? params : [params];
	      return new Uri(url).removeQueryParam(...removableParams).toString();
	    }
	  }]);
	  function Uri(url = '') {
	    babelHelpers.classCallCheck(this, Uri);
	    map.set(this, parseUrl(url));
	  }

	  /**
	   * Gets schema
	   * @return {?string}
	   */
	  babelHelpers.createClass(Uri, [{
	    key: "getSchema",
	    value: function getSchema() {
	      return map.get(this).schema;
	    }
	    /**
	     * Sets schema
	     * @param {string} schema
	     * @return {Uri}
	     */
	  }, {
	    key: "setSchema",
	    value: function setSchema(schema) {
	      map.get(this).schema = String(schema);
	      return this;
	    }
	    /**
	     * Gets host
	     * @return {?string}
	     */
	  }, {
	    key: "getHost",
	    value: function getHost() {
	      return map.get(this).host;
	    }
	    /**
	     * Sets host
	     * @param {string} host
	     * @return {Uri}
	     */
	  }, {
	    key: "setHost",
	    value: function setHost(host) {
	      map.get(this).host = String(host);
	      return this;
	    }
	    /**
	     * Gets port
	     * @return {?string}
	     */
	  }, {
	    key: "getPort",
	    value: function getPort() {
	      return map.get(this).port;
	    }
	    /**
	     * Sets port
	     * @param {String | Number} port
	     * @return {Uri}
	     */
	  }, {
	    key: "setPort",
	    value: function setPort(port) {
	      map.get(this).port = String(port);
	      return this;
	    }
	    /**
	     * Gets path
	     * @return {?string}
	     */
	  }, {
	    key: "getPath",
	    value: function getPath() {
	      return map.get(this).path;
	    }
	    /**
	     * Sets path
	     * @param {string} path
	     * @return {Uri}
	     */
	  }, {
	    key: "setPath",
	    value: function setPath(path) {
	      if (!/^\//.test(path)) {
	        map.get(this).path = `/${String(path)}`;
	        return this;
	      }
	      map.get(this).path = String(path);
	      return this;
	    }
	    /**
	     * Gets query
	     * @return {?string}
	     */
	  }, {
	    key: "getQuery",
	    value: function getQuery() {
	      return buildQueryString(map.get(this).queryParams);
	    }
	    /**
	     * Gets query param value by name
	     * @param {string} key
	     * @return {?string}
	     */
	  }, {
	    key: "getQueryParam",
	    value: function getQueryParam(key) {
	      const params = this.getQueryParams();
	      if (Object.hasOwn(params, key)) {
	        return params[key];
	      }
	      return null;
	    }
	    /**
	     * Sets query param
	     * @param {string} key
	     * @param [value]
	     * @return {Uri}
	     */
	  }, {
	    key: "setQueryParam",
	    value: function setQueryParam(key, value = '') {
	      map.get(this).queryParams[key] = prepareParamValue(value);
	      return this;
	    }
	    /**
	     * Gets query params
	     * @return {Object<string, any>}
	     */
	  }, {
	    key: "getQueryParams",
	    value: function getQueryParams() {
	      return {
	        ...map.get(this).queryParams
	      };
	    }
	    /**
	     * Sets query params
	     * @param {Object<string, any>} params
	     * @return {Uri}
	     */
	  }, {
	    key: "setQueryParams",
	    value: function setQueryParams(params = {}) {
	      const currentParams = this.getQueryParams();
	      const newParams = {
	        ...currentParams,
	        ...params
	      };
	      Object.keys(newParams).forEach(key => {
	        newParams[key] = prepareParamValue(newParams[key]);
	      });
	      map.get(this).queryParams = newParams;
	      return this;
	    }
	    /**
	     * Removes query params by name
	     * @param keys
	     * @return {Uri}
	     */
	  }, {
	    key: "removeQueryParam",
	    value: function removeQueryParam(...keys) {
	      const currentParams = {
	        ...map.get(this).queryParams
	      };
	      keys.forEach(key => {
	        if (Object.hasOwn(currentParams, key)) {
	          delete currentParams[key];
	        }
	      });
	      map.get(this).queryParams = currentParams;
	      return this;
	    }
	    /**
	     * Gets fragment
	     * @return {?string}
	     */
	  }, {
	    key: "getFragment",
	    value: function getFragment() {
	      return map.get(this).hash;
	    }
	    /**
	     * Sets fragment
	     * @param {string} hash
	     * @return {Uri}
	     */
	  }, {
	    key: "setFragment",
	    value: function setFragment(hash) {
	      map.get(this).hash = String(hash);
	      return this;
	    }
	    /**
	     * Serializes URI
	     * @return {Object}
	     */
	  }, {
	    key: "serialize",
	    value: function serialize() {
	      const serialized = {
	        ...map.get(this)
	      };
	      serialized.href = this.toString();
	      return serialized;
	    }
	    /**
	     * Gets URI string
	     * @return {string}
	     */
	  }, {
	    key: "toString",
	    value: function toString() {
	      const data = {
	        ...map.get(this)
	      };
	      let protocol = data.schema ? `${data.schema}://` : '';
	      if (data.useShort) {
	        protocol = '//';
	      }
	      const port = (() => {
	        if (Type.isString(data.port) && !['', '80'].includes(data.port)) {
	          return `:${data.port}`;
	        }
	        return '';
	      })();
	      const host = this.getHost();
	      const path = this.getPath();
	      const query = buildQueryString(data.queryParams);
	      const hash = data.hash ? `#${data.hash}` : '';
	      return `${host ? protocol : ''}${host}${host ? port : ''}${path}${query}${hash}`;
	    }
	  }]);
	  return Uri;
	}();

	/**
	 * @memberOf BX
	 */
	let Validation = /*#__PURE__*/function () {
	  function Validation() {
	    babelHelpers.classCallCheck(this, Validation);
	  }
	  babelHelpers.createClass(Validation, null, [{
	    key: "isEmail",
	    /**
	     * Checks that value is valid email
	     * @param value
	     * @return {boolean}
	     */
	    value: function isEmail(value) {
	      const exp = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/;
	      return exp.test(String(value).toLowerCase());
	    }
	  }]);
	  return Validation;
	}();

	let BaseCache = /*#__PURE__*/function () {
	  function BaseCache() {
	    babelHelpers.classCallCheck(this, BaseCache);
	    babelHelpers.defineProperty(this, "storage", new Map());
	  }
	  babelHelpers.createClass(BaseCache, [{
	    key: "get",
	    /**
	     * Gets cached value or default value
	     */
	    value: function get(key, defaultValue) {
	      if (!this.storage.has(key)) {
	        if (Type.isFunction(defaultValue)) {
	          return defaultValue();
	        }
	        if (!Type.isUndefined(defaultValue)) {
	          return defaultValue;
	        }
	      }
	      return this.storage.get(key);
	    }
	    /**
	     * Sets cache entry
	     */
	  }, {
	    key: "set",
	    value: function set(key, value) {
	      this.storage.set(key, value);
	    }
	    /**
	     * Deletes cache entry
	     */
	  }, {
	    key: "delete",
	    value: function _delete(key) {
	      this.storage.delete(key);
	    }
	    /**
	     * Checks that storage contains entry with specified key
	     */
	  }, {
	    key: "has",
	    value: function has(key) {
	      return this.storage.has(key);
	    }
	    /**
	     * Gets cached value if exists,
	     */
	  }, {
	    key: "remember",
	    value: function remember(key, defaultValue) {
	      if (!this.storage.has(key)) {
	        if (Type.isFunction(defaultValue)) {
	          this.storage.set(key, defaultValue());
	        } else if (!Type.isUndefined(defaultValue)) {
	          this.storage.set(key, defaultValue);
	        }
	      }
	      return this.storage.get(key);
	    }
	    /**
	     * Gets storage size
	     */
	  }, {
	    key: "size",
	    value: function size() {
	      return this.storage.size;
	    }
	    /**
	     * Gets storage keys
	     */
	  }, {
	    key: "keys",
	    value: function keys() {
	      return [...this.storage.keys()];
	    }
	    /**
	     * Gets storage values
	     */
	  }, {
	    key: "values",
	    value: function values() {
	      return [...this.storage.values()];
	    }
	  }]);
	  return BaseCache;
	}();

	let MemoryCache = /*#__PURE__*/function (_BaseCache) {
	  babelHelpers.inherits(MemoryCache, _BaseCache);
	  function MemoryCache(...args) {
	    var _this;
	    babelHelpers.classCallCheck(this, MemoryCache);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(MemoryCache).call(this, ...args));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "storage", new Map());
	    return _this;
	  }
	  return MemoryCache;
	}(BaseCache);

	let LsStorage = /*#__PURE__*/function () {
	  function LsStorage() {
	    babelHelpers.classCallCheck(this, LsStorage);
	    babelHelpers.defineProperty(this, "stackKey", 'BX.Cache.Storage.LsStorage.stack');
	    babelHelpers.defineProperty(this, "stack", null);
	  }
	  babelHelpers.createClass(LsStorage, [{
	    key: "getStack",
	    /**
	     * @private
	     */
	    value: function getStack() {
	      if (Type.isPlainObject(this.stack)) {
	        return this.stack;
	      }
	      const stack = localStorage.getItem(this.stackKey);
	      if (Type.isString(stack) && stack !== '') {
	        const parsedStack = JSON.parse(stack);
	        if (Type.isPlainObject(parsedStack)) {
	          this.stack = parsedStack;
	          return this.stack;
	        }
	      }
	      this.stack = {};
	      return this.stack;
	    }
	    /**
	     * @private
	     */
	  }, {
	    key: "saveStack",
	    value: function saveStack() {
	      if (Type.isPlainObject(this.stack)) {
	        const preparedStack = JSON.stringify(this.stack);
	        localStorage.setItem(this.stackKey, preparedStack);
	      }
	    }
	  }, {
	    key: "get",
	    value: function get(key) {
	      const stack = this.getStack();
	      return stack[key];
	    }
	  }, {
	    key: "set",
	    value: function set(key, value) {
	      const stack = this.getStack();
	      stack[key] = value;
	      this.saveStack();
	    }
	  }, {
	    key: "delete",
	    value: function _delete(key) {
	      const stack = this.getStack();
	      if (key in stack) {
	        delete stack[key];
	      }
	    }
	  }, {
	    key: "has",
	    value: function has(key) {
	      const stack = this.getStack();
	      return key in stack;
	    }
	  }, {
	    key: "keys",
	    value: function keys() {
	      const stack = this.getStack();
	      return Object.keys(stack);
	    }
	  }, {
	    key: "values",
	    value: function values() {
	      const stack = this.getStack();
	      return Object.values(stack);
	    }
	  }, {
	    key: "size",
	    get: function () {
	      const stack = this.getStack();
	      return Object.keys(stack).length;
	    }
	  }]);
	  return LsStorage;
	}();

	let LocalStorageCache = /*#__PURE__*/function (_BaseCache) {
	  babelHelpers.inherits(LocalStorageCache, _BaseCache);
	  function LocalStorageCache(...args) {
	    var _this;
	    babelHelpers.classCallCheck(this, LocalStorageCache);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(LocalStorageCache).call(this, ...args));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "storage", new LsStorage());
	    return _this;
	  }
	  return LocalStorageCache;
	}(BaseCache);

	/**
	 * @memberOf BX
	 */
	let Cache = function Cache() {
	  babelHelpers.classCallCheck(this, Cache);
	};
	babelHelpers.defineProperty(Cache, "BaseCache", BaseCache);
	babelHelpers.defineProperty(Cache, "MemoryCache", MemoryCache);
	babelHelpers.defineProperty(Cache, "LocalStorageCache", LocalStorageCache);

	function convertPath(path) {
	  if (Type.isStringFilled(path)) {
	    return path.split('.').reduce((acc, item) => {
	      item.split(/\[['"]?(.+?)['"]?\]/g).forEach(key => {
	        if (Type.isStringFilled(key)) {
	          acc.push(key);
	        }
	      });
	      return acc;
	    }, []);
	  }
	  return [];
	}

	let SettingsCollection = /*#__PURE__*/function () {
	  function SettingsCollection(options = {}) {
	    babelHelpers.classCallCheck(this, SettingsCollection);
	    if (Type.isPlainObject(options)) {
	      Object.assign(this, options);
	    }
	  }
	  babelHelpers.createClass(SettingsCollection, [{
	    key: "get",
	    value: function get(path, defaultValue = null) {
	      const convertedPath = convertPath(path);
	      return convertedPath.reduce((acc, key) => {
	        if (!Type.isNil(acc) && acc !== defaultValue) {
	          if (!Type.isUndefined(acc[key])) {
	            return acc[key];
	          }
	          return defaultValue;
	        }
	        return acc;
	      }, this);
	    }
	  }]);
	  return SettingsCollection;
	}();

	function deepFreeze(target) {
	  if (Type.isObject(target)) {
	    Object.values(target).forEach(value => {
	      deepFreeze(value);
	    });
	    return Object.freeze(target);
	  }
	  return target;
	}

	const settingsStorage = new Map();
	let Extension$1 = /*#__PURE__*/function () {
	  function Extension() {
	    babelHelpers.classCallCheck(this, Extension);
	  }
	  babelHelpers.createClass(Extension, null, [{
	    key: "getSettings",
	    value: function getSettings(extensionName) {
	      if (Type.isStringFilled(extensionName)) {
	        if (settingsStorage.has(extensionName)) {
	          return settingsStorage.get(extensionName);
	        }
	        const settingsScriptNode = document.querySelector(`script[data-extension="${extensionName}"]`);
	        if (Type.isDomNode(settingsScriptNode)) {
	          const decodedSettings = (() => {
	            try {
	              return new SettingsCollection(JSON.parse(settingsScriptNode.innerHTML));
	            } catch (error) {
	              return new SettingsCollection();
	            }
	          })();
	          const frozenSettings = deepFreeze(decodedSettings);
	          settingsStorage.set(extensionName, frozenSettings);
	          return frozenSettings;
	        }
	      }
	      return deepFreeze(new SettingsCollection());
	    }
	  }]);
	  return Extension;
	}();

	let _Symbol$iterator;
	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration$1(obj, privateSet); privateSet.add(obj); }
	function _checkPrivateRedeclaration$1(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _searchIndexToInsert = /*#__PURE__*/new WeakSet();
	_Symbol$iterator = Symbol.iterator;
	let OrderedArray = /*#__PURE__*/function () {
	  function OrderedArray(comparator = null) {
	    babelHelpers.classCallCheck(this, OrderedArray);
	    _classPrivateMethodInitSpec(this, _searchIndexToInsert);
	    babelHelpers.defineProperty(this, "comparator", null);
	    babelHelpers.defineProperty(this, "items", []);
	    this.comparator = Type.isFunction(comparator) ? comparator : null;
	  }
	  babelHelpers.createClass(OrderedArray, [{
	    key: "add",
	    value: function add(item) {
	      let index = -1;
	      if (this.comparator) {
	        index = _classPrivateMethodGet(this, _searchIndexToInsert, _searchIndexToInsert2).call(this, item);
	        this.items.splice(index, 0, item);
	      } else {
	        this.items.push(item);
	      }
	      return index;
	    }
	  }, {
	    key: "has",
	    value: function has(item) {
	      return this.items.includes(item);
	    }
	  }, {
	    key: "getIndex",
	    value: function getIndex(item) {
	      return this.items.indexOf(item);
	    }
	  }, {
	    key: "getByIndex",
	    value: function getByIndex(index) {
	      if (Type.isNumber(index) && index >= 0) {
	        const item = this.items[index];
	        return Type.isUndefined(item) ? null : item;
	      }
	      return null;
	    }
	  }, {
	    key: "getFirst",
	    value: function getFirst() {
	      const first = this.items[0];
	      return Type.isUndefined(first) ? null : first;
	    }
	  }, {
	    key: "getLast",
	    value: function getLast() {
	      const last = this.items[this.count() - 1];
	      return Type.isUndefined(last) ? null : last;
	    }
	  }, {
	    key: "count",
	    value: function count() {
	      return this.items.length;
	    }
	  }, {
	    key: "delete",
	    value: function _delete(item) {
	      const index = this.getIndex(item);
	      if (index !== -1) {
	        this.items.splice(index, 1);
	        return true;
	      }
	      return false;
	    }
	  }, {
	    key: "clear",
	    value: function clear() {
	      this.items = [];
	    }
	  }, {
	    key: _Symbol$iterator,
	    value: function () {
	      return this.items[Symbol.iterator]();
	    }
	  }, {
	    key: "forEach",
	    value: function forEach(callbackfn, thisArg) {
	      return this.items.forEach(callbackfn, thisArg);
	    }
	  }, {
	    key: "getAll",
	    value: function getAll() {
	      return this.items;
	    }
	  }, {
	    key: "getComparator",
	    value: function getComparator() {
	      return this.comparator;
	    }
	  }, {
	    key: "sort",
	    value: function sort() {
	      const comparator = this.getComparator();
	      if (comparator === null) {
	        return;
	      }

	      /*
	      Simple implementation
	      this.items.sort((item1, item2) => {
	      	return comparator(item1, item2);
	      });
	      */

	      // For stable sorting https://v8.dev/features/stable-sort
	      const length = this.items.length;
	      const indexes = new Array(length);
	      for (let i = 0; i < length; i++) {
	        indexes[i] = i;
	      }

	      // If the comparator returns zero, use the original indexes
	      indexes.sort((index1, index2) => {
	        return comparator(this.items[index1], this.items[index2]) || index1 - index2;
	      });
	      for (let i = 0; i < length; i++) {
	        indexes[i] = this.items[indexes[i]];
	      }
	      for (let i = 0; i < length; i++) {
	        this.items[i] = indexes[i];
	      }
	    }
	  }]);
	  return OrderedArray;
	}();
	function _searchIndexToInsert2(value) {
	  let low = 0;
	  let high = this.items.length;
	  while (low < high) {
	    const mid = Math.floor((low + high) / 2);
	    if (this.comparator(this.items[mid], value) >= 0) {
	      high = mid;
	    } else {
	      low = mid + 1;
	    }
	  }
	  return low;
	}

	let ZIndexComponent = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(ZIndexComponent, _EventEmitter);
	  function ZIndexComponent(element, componentOptions = {}) {
	    var _this;
	    babelHelpers.classCallCheck(this, ZIndexComponent);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(ZIndexComponent).call(this));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "sort", 0);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "alwaysOnTop", false);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "zIndex", 0);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "element", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "overlay", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "overlayGap", -5);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "stack", null);
	    _this.setEventNamespace('BX.Main.ZIndexManager.Component');
	    if (!Type.isElementNode(element)) {
	      throw new Error('ZIndexManager.Component: The argument \'element\' must be a DOM element.');
	    }
	    _this.element = element;
	    const options = Type.isPlainObject(componentOptions) ? componentOptions : {};
	    _this.setAlwaysOnTop(options.alwaysOnTop);
	    _this.setOverlay(options.overlay);
	    _this.setOverlayGap(options.overlayGap);
	    _this.subscribeFromOptions(options.events);
	    return _this;
	  }
	  babelHelpers.createClass(ZIndexComponent, [{
	    key: "getSort",
	    value: function getSort() {
	      return this.sort;
	    }
	    /**
	     * @internal
	     * @param sort
	     */
	  }, {
	    key: "setSort",
	    value: function setSort(sort) {
	      if (Type.isNumber(sort)) {
	        this.sort = sort;
	      }
	    }
	    /**
	     * @internal
	     * @param stack
	     */
	  }, {
	    key: "setStack",
	    value: function setStack(stack) {
	      this.stack = stack;
	    }
	  }, {
	    key: "getStack",
	    value: function getStack() {
	      return this.stack;
	    }
	  }, {
	    key: "getZIndex",
	    value: function getZIndex() {
	      return this.zIndex;
	    }
	    /**
	     * @internal
	     */
	  }, {
	    key: "setZIndex",
	    value: function setZIndex(zIndex) {
	      const changed = this.getZIndex() !== zIndex;
	      this.getElement().style.setProperty('z-index', zIndex, 'important');
	      this.zIndex = zIndex;
	      if (this.getOverlay() !== null) {
	        this.getOverlay().style.setProperty('z-index', zIndex + this.getOverlayGap(), 'important');
	      }
	      if (changed) {
	        this.emit('onZIndexChange', {
	          component: this
	        });
	      }
	    }
	  }, {
	    key: "getAlwaysOnTop",
	    value: function getAlwaysOnTop() {
	      return this.alwaysOnTop;
	    }
	  }, {
	    key: "setAlwaysOnTop",
	    value: function setAlwaysOnTop(value) {
	      if (Type.isNumber(value) || Type.isBoolean(value)) {
	        this.alwaysOnTop = value;
	      }
	    }
	  }, {
	    key: "getElement",
	    value: function getElement() {
	      return this.element;
	    }
	  }, {
	    key: "setOverlay",
	    value: function setOverlay(overlay, gap) {
	      if (Type.isElementNode(overlay) || overlay === null) {
	        this.overlay = overlay;
	        this.setOverlayGap(gap);
	        if (this.getStack()) {
	          this.getStack().sort();
	        }
	      }
	    }
	  }, {
	    key: "getOverlay",
	    value: function getOverlay() {
	      return this.overlay;
	    }
	  }, {
	    key: "setOverlayGap",
	    value: function setOverlayGap(gap) {
	      if (Type.isNumber(gap)) {
	        this.overlayGap = gap;
	      }
	    }
	  }, {
	    key: "getOverlayGap",
	    value: function getOverlayGap() {
	      return this.overlayGap;
	    }
	  }]);
	  return ZIndexComponent;
	}(EventEmitter);

	let ZIndexStack = /*#__PURE__*/function () {
	  function ZIndexStack(container) {
	    babelHelpers.classCallCheck(this, ZIndexStack);
	    babelHelpers.defineProperty(this, "container", null);
	    babelHelpers.defineProperty(this, "components", null);
	    babelHelpers.defineProperty(this, "elements", new WeakMap());
	    babelHelpers.defineProperty(this, "baseIndex", 1000);
	    babelHelpers.defineProperty(this, "baseStep", 50);
	    babelHelpers.defineProperty(this, "sortCount", 0);
	    if (!Type.isDomNode(container)) {
	      throw new Error('ZIndexManager.Stack: The \'container\' argument must be a DOM element.');
	    }
	    this.container = container;
	    const comparator = (componentA, componentB) => {
	      let result = (componentA.getAlwaysOnTop() || 0) - (componentB.getAlwaysOnTop() || 0);
	      if (!result) {
	        result = componentA.getSort() - componentB.getSort();
	      }
	      return result;
	    };
	    this.components = new OrderedArray(comparator);
	  }
	  babelHelpers.createClass(ZIndexStack, [{
	    key: "getBaseIndex",
	    value: function getBaseIndex() {
	      return this.baseIndex;
	    }
	  }, {
	    key: "setBaseIndex",
	    value: function setBaseIndex(index) {
	      if (Type.isNumber(index) && index >= 0) {
	        this.baseIndex = index;
	        this.sort();
	      }
	    }
	  }, {
	    key: "setBaseStep",
	    value: function setBaseStep(step) {
	      if (Type.isNumber(step) && step > 0) {
	        this.baseStep = step;
	        this.sort();
	      }
	    }
	  }, {
	    key: "getBaseStep",
	    value: function getBaseStep() {
	      return this.baseStep;
	    }
	  }, {
	    key: "register",
	    value: function register(element, options = {}) {
	      if (this.getComponent(element)) {
	        console.warn('ZIndexManager: You cannot register the element twice.', element);
	        return this.getComponent(element);
	      }
	      const component = new ZIndexComponent(element, options);
	      component.setStack(this);
	      component.setSort(++this.sortCount);
	      this.elements.set(element, component);
	      this.components.add(component);
	      this.sort();
	      return component;
	    }
	  }, {
	    key: "unregister",
	    value: function unregister(element) {
	      const component = this.elements.get(element);
	      this.components.delete(component);
	      this.elements.delete(element);
	      this.sort();
	    }
	  }, {
	    key: "getComponent",
	    value: function getComponent(element) {
	      return this.elements.get(element) || null;
	    }
	  }, {
	    key: "getComponents",
	    value: function getComponents() {
	      return this.components.getAll();
	    }
	  }, {
	    key: "getMaxZIndex",
	    value: function getMaxZIndex() {
	      const last = this.components.getLast();
	      return last ? last.getZIndex() : this.baseIndex;
	    }
	  }, {
	    key: "sort",
	    value: function sort() {
	      this.components.sort();
	      let zIndex = this.baseIndex;
	      this.components.forEach(component => {
	        component.setZIndex(zIndex);
	        zIndex += this.baseStep;
	      });
	    }
	  }, {
	    key: "bringToFront",
	    value: function bringToFront(element) {
	      const component = this.getComponent(element);
	      if (!component) {
	        console.error('ZIndexManager: element was not found in the stack.', element);
	        return null;
	      }
	      component.setSort(++this.sortCount);
	      this.sort();
	      return component;
	    }
	  }]);
	  return ZIndexStack;
	}();

	function _classStaticPrivateMethodGet(receiver, classConstructor, method) { _classCheckPrivateStaticAccess(receiver, classConstructor); return method; }
	function _classCheckPrivateStaticAccess(receiver, classConstructor) { if (receiver !== classConstructor) { throw new TypeError("Private static access of wrong provenance"); } }
	/**
	 * @memberof BX
	 */
	let ZIndexManager = /*#__PURE__*/function () {
	  function ZIndexManager() {
	    babelHelpers.classCallCheck(this, ZIndexManager);
	  }
	  babelHelpers.createClass(ZIndexManager, null, [{
	    key: "register",
	    value: function register(element, options = {}) {
	      const parentNode = _classStaticPrivateMethodGet(this, ZIndexManager, _getParentNode).call(this, element);
	      if (!parentNode) {
	        return null;
	      }
	      const stack = this.getOrAddStack(parentNode);
	      return stack.register(element, options);
	    }
	  }, {
	    key: "unregister",
	    value: function unregister(element) {
	      const parentNode = _classStaticPrivateMethodGet(this, ZIndexManager, _getParentNode).call(this, element);
	      const stack = this.getStack(parentNode);
	      if (stack) {
	        stack.unregister(element);
	      }
	    }
	  }, {
	    key: "addStack",
	    value: function addStack(container) {
	      const stack = new ZIndexStack(container);
	      this.stacks.set(container, stack);
	      return stack;
	    }
	  }, {
	    key: "getStack",
	    value: function getStack(container) {
	      return this.stacks.get(container) || null;
	    }
	  }, {
	    key: "getOrAddStack",
	    value: function getOrAddStack(container) {
	      return this.getStack(container) || this.addStack(container);
	    }
	  }, {
	    key: "getComponent",
	    value: function getComponent(element) {
	      const parentNode = _classStaticPrivateMethodGet(this, ZIndexManager, _getParentNode).call(this, element, true);
	      if (!parentNode) {
	        return null;
	      }
	      const stack = this.getStack(parentNode);
	      return stack ? stack.getComponent(element) : null;
	    }
	  }, {
	    key: "bringToFront",
	    value: function bringToFront(element) {
	      const parentNode = _classStaticPrivateMethodGet(this, ZIndexManager, _getParentNode).call(this, element);
	      const stack = this.getStack(parentNode);
	      if (stack) {
	        return stack.bringToFront(element);
	      }
	      return null;
	    }
	  }]);
	  return ZIndexManager;
	}();
	function _getParentNode(element, suppressWarnings = false) {
	  if (!Type.isElementNode(element)) {
	    if (!suppressWarnings) {
	      console.error('ZIndexManager: The argument \'element\' must be a DOM element.', element);
	    }
	    return null;
	  } else if (!Type.isElementNode(element.parentNode)) {
	    if (!suppressWarnings) {
	      console.error('ZIndexManager: The \'element\' doesn\'t have a parent node.', element);
	    }
	    return null;
	  }
	  return element.parentNode;
	}
	babelHelpers.defineProperty(ZIndexManager, "stacks", new WeakMap());

	var collections = {
	  OrderedArray,
	  SettingsCollection
	};

	function getElement(element) {
	  if (Type.isString(element)) {
	    return document.getElementById(element);
	  }
	  return element;
	}

	function getWindow(element) {
	  if (Type.isElementNode(element)) {
	    return element.ownerDocument.parentWindow || element.ownerDocument.defaultView || window;
	  }
	  if (Type.isDomNode(element)) {
	    return element.parentWindow || element.defaultView || window;
	  }
	  return window;
	}

	/* eslint-disable prefer-rest-params */

	// BX.*
	const {
	  getClass,
	  namespace
	} = Reflection;
	const message$1 = message;

	/**
	 * @memberOf BX
	 */
	const {
	  replace,
	  remove,
	  clean,
	  insertBefore,
	  insertAfter,
	  append,
	  prepend,
	  style,
	  adjust,
	  create,
	  isShown
	} = Dom;
	const addClass = function addClass() {
	  Dom.addClass(...Runtime.merge([], Array.from(arguments), [getElement(arguments[0])]));
	};
	const removeClass = function removeClass() {
	  Dom.removeClass(...Runtime.merge(Array.from(arguments), [getElement(arguments[0])]));
	};
	const hasClass = function hasClass() {
	  return Dom.hasClass(...Runtime.merge(Array.from(arguments), [getElement(arguments[0])]));
	};
	const toggleClass = function toggleClass() {
	  Dom.toggleClass(...Runtime.merge(Array.from(arguments), [getElement(arguments[0])]));
	};
	const cleanNode = (element, removeElement = false) => {
	  const currentElement = getElement(element);
	  if (Type.isDomNode(currentElement)) {
	    Dom.clean(currentElement);
	    if (removeElement) {
	      Dom.remove(currentElement);
	      return currentElement;
	    }
	  }
	  return currentElement;
	};
	const getCookie = Http.Cookie.get;
	const setCookie = (name, value, options = {}) => {
	  const attributes = {
	    ...options
	  };
	  if (Type.isNumber(attributes.expires)) {
	    attributes.expires /= 3600 * 24;
	  }
	  Http.Cookie.set(name, value, attributes);
	};
	const {
	  bind: bind$1,
	  unbind: unbind$1,
	  unbindAll: unbindAll$1,
	  bindOnce: bindOnce$1,
	  ready: ready$1
	} = Event;
	const {
	  debugState: debugEnableFlag,
	  isDebugEnabled: debugStatus,
	  default: debug$1
	} = debugNs;
	const debugEnable = value => {
	  if (value) {
	    enableDebug();
	  } else {
	    disableDebug();
	  }
	};
	const {
	  clone: clone$1,
	  loadExtension: loadExt,
	  debounce,
	  throttle,
	  html
	} = Runtime;

	// BX.type
	const type = {
	  ...Object.getOwnPropertyNames(Type).filter(key => !['name', 'length', 'prototype', 'caller', 'arguments'].includes(key)).reduce((acc, key) => {
	    acc[key] = Type[key];
	    return acc;
	  }, {}),
	  isNotEmptyString: value => Type.isString(value) && value !== '',
	  isNotEmptyObject: value => Type.isObjectLike(value) && Object.keys(value).length > 0,
	  isMapKey: Type.isObject,
	  stringToInt: value => {
	    const parsed = parseInt(value);
	    return !Number.isNaN(parsed) ? parsed : 0;
	  }
	};

	// BX.browser
	const browser = {
	  IsOpera: Browser.isOpera,
	  IsIE: Browser.isIE,
	  IsIE6: Browser.isIE6,
	  IsIE7: Browser.isIE7,
	  IsIE8: Browser.isIE8,
	  IsIE9: Browser.isIE9,
	  IsIE10: Browser.isIE10,
	  IsIE11: Browser.isIE11,
	  IsSafari: Browser.isSafari,
	  IsFirefox: Browser.isFirefox,
	  IsChrome: Browser.isChrome,
	  DetectIeVersion: Browser.detectIEVersion,
	  IsMac: Browser.isMac,
	  IsAndroid: Browser.isAndroid,
	  isIPad: Browser.isIPad,
	  isIPhone: Browser.isIPhone,
	  IsIOS: Browser.isIOS,
	  IsMobile: Browser.isMobile,
	  isRetina: Browser.isRetina,
	  IsDoctype: Browser.isDoctype,
	  SupportLocalStorage: Browser.isLocalStorageSupported,
	  addGlobalClass: Browser.addGlobalClass,
	  DetectAndroidVersion: Browser.detectAndroidVersion,
	  isPropertySupported: Browser.isPropertySupported,
	  addGlobalFeatures: Browser.addGlobalFeatures
	};

	// eslint-disable-next-line
	const ajax = window.BX ? window.BX.ajax : () => {};
	function GetWindowScrollSize(doc = document) {
	  return {
	    scrollWidth: doc.documentElement.scrollWidth,
	    scrollHeight: doc.documentElement.scrollHeight
	  };
	}
	function GetWindowScrollPos(doc = document) {
	  const win = getWindow(doc);
	  return {
	    scrollLeft: win.pageXOffset,
	    scrollTop: win.pageYOffset
	  };
	}
	function GetWindowInnerSize(doc = document) {
	  const win = getWindow(doc);
	  return {
	    innerWidth: win.innerWidth,
	    innerHeight: win.innerHeight
	  };
	}
	function GetWindowSize(doc = document) {
	  return {
	    ...GetWindowInnerSize(doc),
	    ...GetWindowScrollPos(doc),
	    ...GetWindowScrollSize(doc)
	  };
	}
	function GetContext(node) {
	  return getWindow(node);
	}
	function pos(element, relative = false) {
	  if (!element) {
	    return new DOMRect().toJSON();
	  }
	  if (element.ownerDocument === document && !relative) {
	    const clientRect = element.getBoundingClientRect();
	    const root = document.documentElement;
	    const {
	      body
	    } = document;
	    return {
	      top: Math.round(clientRect.top + (root.scrollTop || body.scrollTop)),
	      left: Math.round(clientRect.left + (root.scrollLeft || body.scrollLeft)),
	      width: Math.round(clientRect.right - clientRect.left),
	      height: Math.round(clientRect.bottom - clientRect.top),
	      right: Math.round(clientRect.right + (root.scrollLeft || body.scrollLeft)),
	      bottom: Math.round(clientRect.bottom + (root.scrollTop || body.scrollTop))
	    };
	  }
	  let x = 0;
	  let y = 0;
	  const w = element.offsetWidth;
	  const h = element.offsetHeight;
	  let first = true;

	  // eslint-disable-next-line no-param-reassign
	  for (; element != null; element = element.offsetParent) {
	    if (!first && relative && BX.is_relative(element)) {
	      break;
	    }
	    x += element.offsetLeft;
	    y += element.offsetTop;
	    if (first) {
	      first = false;
	      // eslint-disable-next-line no-continue
	      continue;
	    }
	    x += Text.toNumber(Dom.style(element, 'border-left-width'));
	    y += Text.toNumber(Dom.style(element, 'border-top-width'));
	  }
	  return new DOMRect(x, y, w, h).toJSON();
	}
	function addCustomEvent(eventObject, eventName, eventHandler) {
	  if (Type.isString(eventObject)) {
	    eventHandler = eventName;
	    eventName = eventObject;
	    eventObject = EventEmitter.GLOBAL_TARGET;
	  }
	  if (eventObject === window) {
	    eventObject = EventEmitter.GLOBAL_TARGET;
	  }
	  if (!Type.isObject(eventObject)) {
	    console.error('The "eventObject" argument must be an object. Received type ' + typeof eventObject + '.');
	    return;
	  }
	  if (!Type.isStringFilled(eventName)) {
	    console.error('The "eventName" argument must be a string.');
	    return;
	  }
	  if (!Type.isFunction(eventHandler)) {
	    console.error('The "eventHandler" argument must be a function. Received type ' + typeof eventHandler + '.');
	    return;
	  }
	  eventName = eventName.toLowerCase();
	  EventEmitter.subscribe(eventObject, eventName, eventHandler, {
	    compatMode: true,
	    useGlobalNaming: true
	  });
	}
	function onCustomEvent(eventObject, eventName, eventParams, secureParams) {
	  if (Type.isString(eventObject)) {
	    secureParams = eventParams;
	    eventParams = eventName;
	    eventName = eventObject;
	    eventObject = EventEmitter.GLOBAL_TARGET;
	  }
	  if (!Type.isObject(eventObject) || eventObject === window) {
	    eventObject = EventEmitter.GLOBAL_TARGET;
	  }
	  if (!eventParams) {
	    eventParams = [];
	  }
	  eventName = eventName.toLowerCase();
	  const event = new BaseEvent();
	  event.setData(eventParams);
	  event.setCompatData(eventParams);
	  EventEmitter.emit(eventObject, eventName, event, {
	    cloneData: secureParams === true,
	    useGlobalNaming: true
	  });
	}
	function removeCustomEvent(eventObject, eventName, eventHandler) {
	  if (Type.isString(eventObject)) {
	    eventHandler = eventName;
	    eventName = eventObject;
	    eventObject = EventEmitter.GLOBAL_TARGET;
	  }
	  if (!Type.isFunction(eventHandler)) {
	    console.error('The "eventHandler" argument must be a function. Received type ' + typeof eventHandler + '.');
	    return;
	  }
	  if (eventObject === window) {
	    eventObject = EventEmitter.GLOBAL_TARGET;
	  }
	  eventName = eventName.toLowerCase();
	  EventEmitter.unsubscribe(eventObject, eventName, eventHandler, {
	    useGlobalNaming: true
	  });
	}
	function removeAllCustomEvents(eventObject, eventName) {
	  if (Type.isString(eventObject)) {
	    eventName = eventObject;
	    eventObject = EventEmitter.GLOBAL_TARGET;
	  }
	  if (eventObject === window) {
	    eventObject = EventEmitter.GLOBAL_TARGET;
	  }
	  eventName = eventName.toLowerCase();
	  EventEmitter.unsubscribeAll(eventObject, eventName, {
	    useGlobalNaming: true
	  });
	}

	if (typeof global === 'object' && global.window && global.window.BX) {
	  Object.assign(global.window.BX, exports);
	}

	exports.Type = Type;
	exports.Reflection = Reflection;
	exports.Text = Text;
	exports.Dom = Dom;
	exports.Browser = Browser;
	exports.Event = Event;
	exports.Http = Http;
	exports.Runtime = Runtime;
	exports.Loc = Loc;
	exports.Tag = Tag;
	exports.Uri = Uri;
	exports.Validation = Validation;
	exports.Cache = Cache;
	exports.BaseError = BaseError;
	exports.Extension = Extension$1;
	exports.ZIndexManager = ZIndexManager;
	exports.Collections = collections;
	exports.getClass = getClass;
	exports.namespace = namespace;
	exports.message = message$1;
	exports.replace = replace;
	exports.remove = remove;
	exports.clean = clean;
	exports.insertBefore = insertBefore;
	exports.insertAfter = insertAfter;
	exports.append = append;
	exports.prepend = prepend;
	exports.style = style;
	exports.adjust = adjust;
	exports.create = create;
	exports.isShown = isShown;
	exports.addClass = addClass;
	exports.removeClass = removeClass;
	exports.hasClass = hasClass;
	exports.toggleClass = toggleClass;
	exports.cleanNode = cleanNode;
	exports.getCookie = getCookie;
	exports.setCookie = setCookie;
	exports.bind = bind$1;
	exports.unbind = unbind$1;
	exports.unbindAll = unbindAll$1;
	exports.bindOnce = bindOnce$1;
	exports.ready = ready$1;
	exports.debugEnableFlag = debugEnableFlag;
	exports.debugStatus = debugStatus;
	exports.debug = debug$1;
	exports.debugEnable = debugEnable;
	exports.clone = clone$1;
	exports.loadExt = loadExt;
	exports.debounce = debounce;
	exports.throttle = throttle;
	exports.html = html;
	exports.type = type;
	exports.browser = browser;
	exports.ajax = ajax;
	exports.GetWindowScrollSize = GetWindowScrollSize;
	exports.GetWindowScrollPos = GetWindowScrollPos;
	exports.GetWindowInnerSize = GetWindowInnerSize;
	exports.GetWindowSize = GetWindowSize;
	exports.GetContext = GetContext;
	exports.pos = pos;
	exports.addCustomEvent = addCustomEvent;
	exports.onCustomEvent = onCustomEvent;
	exports.removeCustomEvent = removeCustomEvent;
	exports.removeAllCustomEvents = removeAllCustomEvents;

}((this.BX = this.BX || {})));



(function(BX) {
	/* list of registered proxy functions */
	var proxyList = new WeakMap();
	var deferList = new WeakMap();

	/* List of denied event handlers */
	var deniedEvents = [];

	/* list of registered custom events */
	var customEvents = new WeakMap();
	var customEventsCnt = 0;

	/* list of external garbage collectors */
	var garbageCollectors = [];

	/* list of loaded CSS files */
	var cssList = [];
	var cssInit = false;

	/* list of loaded JS files */
	var jsList = [];
	var jsInit = false;

	var eventTypes = {
		click: 'MouseEvent',
		dblclick: 'MouseEvent',
		mousedown: 'MouseEvent',
		mousemove: 'MouseEvent',
		mouseout: 'MouseEvent',
		mouseover: 'MouseEvent',
		mouseup: 'MouseEvent',
		focus: 'MouseEvent',
		blur: 'MouseEvent'
	};

	var lastWait = [];

	var CHECK_FORM_ELEMENTS = {tagName: /^INPUT|SELECT|TEXTAREA|BUTTON$/i};

	BX.MSLEFT = 1;
	BX.MSMIDDLE = 2;
	BX.MSRIGHT = 4;

	BX.AM_PM_UPPER = 1;
	BX.AM_PM_LOWER = 2;
	BX.AM_PM_NONE = false;

	BX.ext = function(ob)
	{
		for (var i in ob)
		{
			if(ob.hasOwnProperty(i))
			{
				this[i] = ob[i];
			}
		}
	};

	var r = {
		script: /<script([^>]*)>/ig,
		script_end: /<\/script>/ig,
		script_src: /src=["\']([^"\']+)["\']/i,
		script_type: /type=["\']([^"\']+)["\']/i,
		space: /\s+/,
		ltrim: /^[\s\r\n]+/g,
		rtrim: /[\s\r\n]+$/g,
		style: /<link.*?(rel="stylesheet"|type="text\/css")[^>]*>/i,
		style_href: /href=["\']([^"\']+)["\']/i
	};

	BX.processHTML = function(data, scriptsRunFirst)
	{
		var matchScript, matchStyle, matchSrc, matchHref, matchType, scripts = [], styles = [];
		var textIndexes = [];
		var lastIndex = r.script.lastIndex = r.script_end.lastIndex = 0;

		while ((matchScript = r.script.exec(data)) !== null)
		{
			r.script_end.lastIndex = r.script.lastIndex;
			var matchScriptEnd = r.script_end.exec(data);
			if (matchScriptEnd === null)
			{
				break;
			}

			// skip script tags of special types
			var skipTag = false;
			if ((matchType = matchScript[1].match(r.script_type)) !== null)
			{
				if(
					matchType[1] == 'text/html'
					|| matchType[1] == 'text/template'
					|| matchType[1] == 'extension/settings'
				)
				{
					skipTag = true;
				}
			}

			if(skipTag)
			{
				textIndexes.push([lastIndex, r.script_end.lastIndex - lastIndex]);
			}
			else
			{
				textIndexes.push([lastIndex, matchScript.index - lastIndex]);

				var bRunFirst = scriptsRunFirst || (matchScript[1].indexOf('bxrunfirst') != '-1');

				if ((matchSrc = matchScript[1].match(r.script_src)) !== null)
				{
					scripts.push({"bRunFirst": bRunFirst, "isInternal": false, "JS": matchSrc[1]});
				}
				else
				{
					var start = matchScript.index + matchScript[0].length;
					var js = data.substr(start, matchScriptEnd.index-start);

					scripts.push({"bRunFirst": bRunFirst, "isInternal": true, "JS": js});
				}
			}

			lastIndex = matchScriptEnd.index + 9;
			r.script.lastIndex = lastIndex;
		}

		textIndexes.push([lastIndex, lastIndex === 0 ? data.length : data.length - lastIndex]);
		var pureData = "";
		for (var i = 0, length = textIndexes.length; i < length; i++)
		{
			if (BX.type.isString(data) && BX.type.isFunction(data.substr))
			{
				pureData += data.substr(textIndexes[i][0], textIndexes[i][1]);
			}
		}

		while ((matchStyle = pureData.match(r.style)) !== null)
		{
			if ((matchHref = matchStyle[0].match(r.style_href)) !== null && matchStyle[0].indexOf('media="') < 0)
			{
				styles.push(matchHref[1]);
			}

			pureData = pureData.replace(matchStyle[0], '');
		}

		return {'HTML': pureData, 'SCRIPT': scripts, 'STYLE': styles};
	};

	/* OO emulation utility */
	BX.extend = function(child, parent)
	{
		var f = function() {};
		f.prototype = parent.prototype;

		child.prototype = new f();
		child.prototype.constructor = child;

		child.superclass = parent.prototype;
		child.prototype.superclass = parent.prototype;
		if(parent.prototype.constructor == Object.prototype.constructor)
		{
			parent.prototype.constructor = parent;
		}
	};

	BX.is_subclass_of = function(ob, parent_class)
	{
		if (ob instanceof parent_class)
			return true;

		if (parent_class.superclass)
			return BX.is_subclass_of(ob, parent_class.superclass);

		return false;
	};

	BX.clearNodeCache = function()
	{
		return false;
	};

	BX.bitrix_sessid = function() {return BX.message("bitrix_sessid"); };

	/**
	 * Creates document fragment with child nodes.
	 *
	 * @param {Node[]} nodes
	 * @return {DocumentFragment}
	 */
	BX.createFragment = function(nodes)
	{
		var fragment = document.createDocumentFragment();

		if(!BX.type.isArray(nodes))
		{
			return fragment;
		}
		for(var i = 0; i < nodes.length; i++)
		{
			fragment.appendChild(nodes[i]);
		}

		return fragment;
	};

	/**
	 * @deprecated
	 * @use BX.style
	 * @param element
	 * @param opacity
	 */
	BX.setOpacity = function(element, opacity)
	{
		var opacityValue = parseFloat(opacity);

		if (!isNaN(opacityValue) && BX.type.isDomNode(element))
		{
			opacityValue = opacityValue < 1 ? opacityValue : opacityValue / 100;
			BX.style(element, 'opacity', opacityValue);
		}
	};

	/**
	 * @deprecated
	 * @param el
	 * @return {*}
	 */
	BX.hoverEvents = function(el)
	{
		if (el)
			return BX.adjust(el, {events: BX.hoverEvents()});
		else
			return {mouseover: BX.hoverEventsHover, mouseout: BX.hoverEventsHout};
	};

	/**
	 * @deprecated
	 */
	BX.hoverEventsHover = function(){BX.addClass(this,'bx-hover');this.BXHOVER=true;};
	/**
	 * @deprecated
	 */
	BX.hoverEventsHout = function(){BX.removeClass(this,'bx-hover');this.BXHOVER=false;};

	/**
	 * @deprecated
	 */
	BX.focusEvents = function(el)
	{
		if (el)
			return BX.adjust(el, {events: BX.focusEvents()});
		else
			return {mouseover: BX.focusEventsFocus, mouseout: BX.focusEventsBlur};
	};

	/**
	 * @deprecated
	 */
	BX.focusEventsFocus = function(){BX.addClass(this,'bx-focus');this.BXFOCUS=true;};
	/**
	 * @deprecated
	 */
	BX.focusEventsBlur = function(){BX.removeClass(this,'bx-focus');this.BXFOCUS=false;};

	BX.setUnselectable = function(node)
	{
		BX.style(node, {
			'userSelect': 'none',
			'MozUserSelect': 'none',
			'WebkitUserSelect': 'none',
			'KhtmlUserSelect': 'none',
		});
		node.setAttribute('unSelectable', 'on');
	};

	BX.setSelectable = function(node)
	{
		BX.style(node, {
			'userSelect': null,
			'MozUserSelect': null,
			'WebkitUserSelect': null,
			'KhtmlUserSelect': null,
		});
		node.removeAttribute('unSelectable');
	};

	BX.styleIEPropertyName = function(name)
	{
		if (name == 'float')
			name = BX.browser.IsIE() ? 'styleFloat' : 'cssFloat';
		else
		{
			var res = BX.browser.isPropertySupported(name);
			if (res)
			{
				name = res;
			}
			else
			{
				var reg = /(\-([a-z]){1})/g;
				if (reg.test(name))
				{
					name = name.replace(reg, function () {return arguments[2].toUpperCase();});
				}
			}
		}
		return name;
	};

	BX.focus = function(el)
	{
		try
		{
			el.focus();
			return true;
		}
		catch (e)
		{
			return false;
		}
	};

	BX.firstChild = function(el)
	{
		return BX.type.isDomNode(el) ? el.firstElementChild : null;
	};

	BX.lastChild = function(el)
	{
		return BX.type.isDomNode(el) ? el.lastElementChild : null;
	};

	BX.previousSibling = function(el)
	{
		return BX.type.isDomNode(el) ? el.previousElementSibling : null;
	};

	BX.nextSibling = function(el)
	{
		return BX.type.isDomNode(el) ? el.nextElementSibling : null;
	};

	/*
		params: {
			obj : html node
			className : className value
			recursive : used only for older browsers to optimize the tree traversal, in new browsers the search is always recursively, default - true
		}

		Search all nodes with className
	*/
	/**
	 * @deprecated
	 * @use .querySelectorAll
	 * @param obj
	 * @param className
	 * @param recursive
	 * @return {*}
	 */
	BX.findChildrenByClassName = function(obj, className, recursive)
	{
		if(!obj || !obj.childNodes) return null;

		var result = [];
		if (typeof(obj.getElementsByClassName) == 'undefined')
		{
			recursive = recursive !== false;
			result = BX.findChildren(obj, {className : className}, recursive);
		}
		else
		{
			var col = obj.getElementsByClassName(className);
			for (i=0,l=col.length;i<l;i++)
			{
				result[i] = col[i];
			}
		}
		return result;
	};

	/*
		params: {
			obj : html node
			className : className value
			recursive : used only for older browsers to optimize the tree traversal, in new browsers the search is always recursively, default - true
		}

		Search first node with className
	*/
	/**
	 * @deprecated
	 * @use .querySelector
	 * @param obj
	 * @param className
	 * @param recursive
	 * @return {*}
	 */
	BX.findChildByClassName = function(obj, className, recursive)
	{
		if(!obj || !obj.childNodes) return null;

		var result = null;
		if (typeof(obj.getElementsByClassName) == 'undefined')
		{
			recursive = recursive !== false;
			result = BX.findChild(obj, {className : className}, recursive);
		}
		else
		{
			var col = obj.getElementsByClassName(className);
			if (col && typeof(col[0]) != 'undefined')
			{
				result = col[0];
			}
			else
			{
				result = null;
			}
		}
		return result;
	};

	/*
		params: {
			tagName|tag : 'tagName',
			className|class : 'className',
			attribute : {attribute : value, attribute : value} | attribute | [attribute, attribute....],
			property : {prop: value, prop: value} | prop | [prop, prop]
		}

		all values can be RegExps or strings
	*/
	/**
	 * @deprecated
	 * @use .querySelectorAll
	 * @param obj
	 * @param params
	 * @param recursive
	 * @return {*|Node}
	 */
	BX.findChildren = function(obj, params, recursive)
	{
		return BX.findChild(obj, params, recursive, true);
	};

	/**
	 * @deprecated
	 * @use .querySelectorAll
	 * @param obj
	 * @param params
	 * @param recursive
	 * @param get_all
	 * @return {*}
	 */
	BX.findChild = function(obj, params, recursive, get_all)
	{
		if(!obj || !obj.childNodes) return null;

		recursive = !!recursive; get_all = !!get_all;

		var n = obj.childNodes.length, result = [];

		for (var j=0; j<n; j++)
		{
			var child = obj.childNodes[j];

			if (_checkNode(child, params))
			{
				if (get_all)
					result.push(child);
				else
					return child;
			}

			if(recursive == true)
			{
				var res = BX.findChild(child, params, recursive, get_all);
				if (res)
				{
					if (get_all)
						result = BX.util.array_merge(result, res);
					else
						return res;
				}
			}
		}

		if (get_all || result.length > 0)
			return result;
		else
			return null;
	};

	/**
	 * @deprecated
	 * @use .closest()
	 * @param obj
	 * @param params
	 * @param maxParent
	 * @return {*}
	 */
	BX.findParent = function(obj, params, maxParent)
	{
		if(!obj)
			return null;

		var o = obj;
		while(o.parentNode)
		{
			var parent = o.parentNode;

			if (_checkNode(parent, params))
				return parent;

			o = parent;

			if (!!maxParent &&
				(BX.type.isFunction(maxParent)
					|| typeof maxParent == 'object'))
			{
				if (BX.type.isElementNode(maxParent))
				{
					if (o == maxParent)
						break;
				}
				else
				{
					if (_checkNode(o, maxParent))
						break;
				}
			}
		}
		return null;
	};

	/**
	 * @deprecated
	 * @use .querySelector
	 * @param obj
	 * @param params
	 * @return {*}
	 */
	BX.findNextSibling = function(obj, params)
	{
		if(!obj)
			return null;
		var o = obj;
		while(o.nextSibling)
		{
			var sibling = o.nextSibling;
			if (_checkNode(sibling, params))
				return sibling;
			o = sibling;
		}
		return null;
	};

	/**
	 * @deprecated
	 * @use .querySelector
	 * @param obj
	 * @param params
	 * @return {*}
	 */
	BX.findPreviousSibling = function(obj, params)
	{
		if(!obj)
			return null;

		var o = obj;
		while(o.previousSibling)
		{
			var sibling = o.previousSibling;
			if(_checkNode(sibling, params))
				return sibling;
			o = sibling;
		}
		return null;
	};

	BX.checkNode = function(obj, params)
	{
		return _checkNode(obj, params);
	};

	/**
	 * @deprecated
	 * @use .querySelectorAll
	 * @param form
	 * @return {Array}
	 */
	BX.findFormElements = function(form)
	{
		if (BX.type.isString(form))
			form = document.forms[form]||BX(form);

		var res = [];

		if (BX.type.isElementNode(form))
		{
			if (form.tagName.toUpperCase() == 'FORM')
			{
				res = form.elements;
			}
			else
			{
				res = BX.findChildren(form, CHECK_FORM_ELEMENTS, true);
			}
		}

		return res;
	};

	/**
	 * @deprecated
	 * @use .contains()
	 * @param whichNode
	 * @param forNode
	 * @return {boolean}
	 */
	BX.isParentForNode = function(whichNode, forNode)
	{
		if (BX.type.isDomNode(whichNode) && BX.type.isDomNode(forNode))
		{
			return whichNode.contains(forNode);
		}

		return false;
	};

	BX.getCaretPosition = function(node)
	{
		var pos = 0;

		if(node.selectionStart || node.selectionStart == 0)
		{
			pos = node.selectionStart;
		}
		else if(document.selection)
		{
			node.focus();
			var selection = document.selection.createRange();
			selection.moveStart('character', -node.value.length);
			pos = selection.text.length;
		}

		return (pos);
	};

	BX.setCaretPosition = function(node, pos)
	{
		if(!BX.isNodeInDom(node) || BX.isNodeHidden(node) || node.disabled)
		{
			return;
		}

		if(node.setSelectionRange)
		{
			node.focus();
			node.setSelectionRange(pos, pos);
		}
		else if(node.createTextRange)
		{
			var range = node.createTextRange();
			range.collapse(true);
			range.moveEnd('character', pos);
			range.moveStart('character', pos);
			range.select();
		}
	};

	// access private. use BX.mergeEx instead.
	// todo: refactor BX.merge, make it work through BX.mergeEx
	BX.merge = function(){
		var arg = Array.prototype.slice.call(arguments);

		if(arg.length < 2)
			return {};

		var result = arg.shift();

		for(var i = 0; i < arg.length; i++)
		{
			for(var k in arg[i]){

				if(typeof arg[i] == 'undefined' || arg[i] == null)
					continue;

				if(arg[i].hasOwnProperty(k)){

					if(typeof arg[i][k] == 'undefined' || arg[i][k] == null)
						continue;

					if(typeof arg[i][k] == 'object' && !BX.type.isDomNode(arg[i][k]) && (typeof arg[i][k]['isUIWidget'] == 'undefined')){

						// go deeper

						var isArray = 'length' in arg[i][k];

						if(typeof result[k] != 'object')
							result[k] = isArray ? [] : {};

						if(isArray)
							BX.util.array_merge(result[k], arg[i][k]);
						else
							BX.merge(result[k], arg[i][k]);

					}else
						result[k] = arg[i][k];
				}
			}
		}

		return result;
	};

	BX.mergeEx = function()
	{
		var arg = Array.prototype.slice.call(arguments);
		if(arg.length < 2)
		{
			return {};
		}

		var result = arg.shift();
		for (var i = 0; i < arg.length; i++)
		{
			for (var k in arg[i])
			{
				if (typeof arg[i] == "undefined" || arg[i] == null || !arg[i].hasOwnProperty(k))
				{
					continue;
				}

				if (BX.type.isPlainObject(arg[i][k]) && BX.type.isPlainObject(result[k]))
				{
					BX.mergeEx(result[k], arg[i][k]);
				}
				else
				{
					result[k] = BX.type.isPlainObject(arg[i][k]) ? BX.clone(arg[i][k]) : arg[i][k];
				}
			}
		}

		return result;
	};

	BX.getEventButton = function(e)
	{
		e = e || window.event;

		var flags = 0;

		if (typeof e.which != 'undefined')
		{
			switch (e.which)
			{
				case 1: flags = flags|BX.MSLEFT; break;
				case 2: flags = flags|BX.MSMIDDLE; break;
				case 3: flags = flags|BX.MSRIGHT; break;
			}
		}
		else if (typeof e.button != 'undefined')
		{
			flags = event.button;
		}

		return flags || BX.MSLEFT;
	};

	var captured_events = null, _bind = null;
	BX.CaptureEvents = function(el_c, evname_c)
	{
		if (_bind)
			return;

		_bind = BX.bind;
		captured_events = [];

		BX.bind = function(el, evname, func)
		{
			if (el === el_c && evname === evname_c)
				captured_events.push(func);

			_bind.apply(this, arguments);
		}
	};

	BX.CaptureEventsGet = function()
	{
		if (_bind)
		{
			BX.bind = _bind;

			var captured = captured_events;

			_bind = null;
			captured_events = null;
			return captured;
		}
		return null;
	};

	// Don't even try to use it for submit event!
	BX.fireEvent = function(ob,ev)
	{
		var result = false, e = null;
		if (BX.type.isDomNode(ob))
		{
			result = true;
			if (document.createEventObject)
			{
				// IE
				if (eventTypes[ev] != 'MouseEvent')
				{
					e = document.createEventObject();
					e.type = ev;
					result = ob.fireEvent('on' + ev, e);
				}

				if (ob[ev])
				{
					ob[ev]();
				}
			}
			else
			{
				// non-IE
				e = null;

				switch (eventTypes[ev])
				{
					case 'MouseEvent':
						e = document.createEvent('MouseEvent');
						try
						{
							e.initMouseEvent(ev, true, true, top, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, null);
						}
						catch (initException)
						{
							e.initMouseEvent(ev, true, true, window, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, null);
						}

					break;
					default:
						e = document.createEvent('Event');
						e.initEvent(ev, true, true);
				}

				result = ob.dispatchEvent(e);
			}
		}

		return result;
	};

	BX.getWheelData = function(e)
	{
		e = e || window.event;
		e.wheelData = e.detail ? e.detail * -1 : e.wheelDelta / 40;
		return e.wheelData;
	};

	BX.proxy_context = null;

	BX.delegate = function (func, thisObject)
	{
		if (!func || !thisObject)
			return func;

		return function() {
			var cur = BX.proxy_context;
			BX.proxy_context = this;
			var res = func.apply(thisObject, arguments);
			BX.proxy_context = cur;
			return res;
		}
	};

	BX.delegateLater = function (func_name, thisObject, contextObject)
	{
		return function()
		{
			if (thisObject[func_name])
			{
				var cur = BX.proxy_context;
				BX.proxy_context = this;
				var res = thisObject[func_name].apply(contextObject||thisObject, arguments);
				BX.proxy_context = cur;
				return res;
			}
			return null;
		}
	};

	BX.proxy = function(func, thisObject)
	{
		return getObjectDelegate(func, thisObject, proxyList);
	};

	BX.defer = function(func, thisObject)
	{
		if (!!thisObject)
			return BX.defer_proxy(func, thisObject);
		else
			return function() {
				var arg = arguments;
				setTimeout(function(){func.apply(this,arg)}, 10);
			};
	};

	BX.defer_proxy = function(func, thisObject)
	{
		return getObjectDelegate(func, thisObject, deferList, BX.defer);
	};

	/**
	 *
	 * @private
	 */
	function getObjectDelegate(func, thisObject, collection, decorator)
	{
		if (!BX.type.isFunction(func) || !BX.type.isMapKey(thisObject))
		{
			return func;
		}

		var objectDelegates = collection.get(thisObject);
		if (!objectDelegates)
		{
			objectDelegates = new WeakMap();
			collection.set(thisObject, objectDelegates);
		}

		var delegate = objectDelegates.get(func);
		if (!delegate)
		{
			delegate = decorator ? decorator(BX.delegate(func, thisObject)) : BX.delegate(func, thisObject);
			objectDelegates.set(func, delegate);
		}

		return delegate;
	}

	BX.once = function(el, evname, func)
	{
		var fn = function()
		{
			BX.unbind(el, evname, fn);
			func.apply(this, arguments);
		};

		return fn;
	};

	BX.bindDelegate = function (elem, eventName, isTarget, handler)
	{
		var h = BX.delegateEvent(isTarget, handler);
		BX.bind(elem, eventName, h);
		return h;
	};

	BX.delegateEvent = function(isTarget, handler)
	{
		return function(e)
		{
			e = e || window.event;
			var target = e.target || e.srcElement;

			while (target != this)
			{
				if (_checkNode(target, isTarget))
				{
					return handler.call(target, e);
				}
				if (target && target.parentNode)
					target = target.parentNode;
				else
					break;
			}
			return null;
		}
	};

	BX.False = function() {return false;};
	BX.DoNothing = function() {};

	// TODO: also check event handlers set via BX.bind()
	BX.denyEvent = function(el, ev)
	{
		deniedEvents.push([el, ev, el['on' + ev]]);
		el['on' + ev] = BX.DoNothing;
	};

	BX.allowEvent = function(el, ev)
	{
		for(var i=0, len=deniedEvents.length; i<len; i++)
		{
			if (deniedEvents[i][0] == el && deniedEvents[i][1] == ev)
			{
				el['on' + ev] = deniedEvents[i][2];
				BX.util.deleteFromArray(deniedEvents, i);
				return;
			}
		}
	};

	BX.fixEventPageXY = function(event)
	{
		BX.fixEventPageX(event);
		BX.fixEventPageY(event);
		return event;
	};

	BX.fixEventPageX = function(event)
	{
		if (event.pageX == null && event.clientX != null)
		{
			event.pageX =
				event.clientX +
				(document.documentElement && document.documentElement.scrollLeft || document.body && document.body.scrollLeft || 0) -
				(document.documentElement.clientLeft || 0);
		}

		return event;
	};

	BX.fixEventPageY = function(event)
	{
		if (event.pageY == null && event.clientY != null)
		{
			event.pageY =
				event.clientY +
				(document.documentElement && document.documentElement.scrollTop || document.body && document.body.scrollTop || 0) -
				(document.documentElement.clientTop || 0);
		}

		return event;
	};

	/**
	 * @deprecated
	 * @see e.preventDefault()
	 */
	BX.PreventDefault = function(e)
	{
		if(!e) e = window.event;
		if(e.stopPropagation)
		{
			e.preventDefault();
			e.stopPropagation();
		}
		else
		{
			e.cancelBubble = true;
			e.returnValue = false;
		}
		return false;
	};

	/**
	 * @deprecated
	 * @see e.preventDefault();
	 *
	 * @param e
	 * @return {boolean}
	 */
	BX.eventReturnFalse = function(e)
	{
		e=e||window.event;
		if (e && e.preventDefault) e.preventDefault();
		else e.returnValue = false;
		return false;
	};

	/**
	 * @deprecated
	 * @see e.stopPropagation()
	 * @param e
	 */
	BX.eventCancelBubble = function(e)
	{
		e=e||window.event;
		if(e && e.stopPropagation)
			e.stopPropagation();
		else
			e.cancelBubble = true;
	};

	BX.bindDebouncedChange = function(node, fn, fnInstant, timeout, ctx)
	{
		ctx = ctx || window;
		timeout = timeout || 300;

		var dataTag = 'bx-dc-previous-value';
		BX.data(node, dataTag, node.value);

		var act = function(fn, val){

			var pVal = BX.data(node, dataTag);

			if(typeof pVal == 'undefined' || pVal != val){
				if(typeof ctx != 'object')
					fn(val);
				else
					fn.apply(ctx, [val]);
			}
		};

		var actD = BX.debounce(function(){
			var val = node.value;
			act(fn, val);
			BX.data(node, dataTag, val);
		}, timeout);

		BX.bind(node, 'keyup', actD);
		BX.bind(node, 'change', actD);
		BX.bind(node, 'input', actD);

		if(BX.type.isFunction(fnInstant)){

			var actI = function(){
				act(fnInstant, node.value);
			};

			BX.bind(node, 'keyup', actI);
			BX.bind(node, 'change', actI);
			BX.bind(node, 'input', actI);
		}
	};

	BX.parseJSON = function(data, context)
	{
		var result = null;
		if (BX.type.isNotEmptyString(data))
		{
			try {
				if (data.indexOf("\n") >= 0)
					eval('result = ' + data);
				else
					result = (new Function("return " + data))();
			} catch(e) {
				BX.onCustomEvent(context, 'onParseJSONFailure', [data, context])
			}
		}
		else if(BX.type.isPlainObject(data))
		{
			return data;
		}

		return result;
	};

	BX.submit = function(obForm, action_name, action_value, onAfterSubmit)
	{
		action_name = action_name || 'save';
		if (!obForm['BXFormSubmit_' + action_name])
		{
			obForm['BXFormSubmit_' + action_name] = obForm.appendChild(BX.create('INPUT', {
				'props': {
					'type': 'submit',
					'name': action_name,
					'value': action_value || 'Y'
				},
				'style': {
					'display': 'none'
				}
			}));
		}

		if (obForm.sessid)
			obForm.sessid.value = BX.bitrix_sessid();

		setTimeout(BX.delegate(function() {BX.fireEvent(this, 'click'); if (onAfterSubmit) onAfterSubmit();}, obForm['BXFormSubmit_' + action_name]), 10);
	};

	BX.show = function(ob, displayType)
	{
		if (ob.BXDISPLAY || !_checkDisplay(ob, displayType))
		{
			ob.style.display = ob.BXDISPLAY;
		}
	};

	BX.hide = function(ob, displayType)
	{
		if (!ob.BXDISPLAY)
			_checkDisplay(ob, displayType);

		ob.style.display = 'none';
	};

	BX.toggle = function(ob, values)
	{
		if (!values && BX.type.isElementNode(ob))
		{
			var bShow = true;
			if (ob.BXDISPLAY)
				bShow = !_checkDisplay(ob);
			else
				bShow = ob.style.display == 'none';

			if (bShow)
				BX.show(ob);
			else
				BX.hide(ob);
		}
		else if (BX.type.isArray(values))
		{
			for (var i=0,len=values.length; i<len; i++)
			{
				if (ob == values[i])
				{
					ob = values[i==len-1 ? 0 : i+1];
					break;
				}
			}
			if (i==len)
				ob = values[0];
		}

		return ob;
	};

	function _checkDisplay(ob, displayType)
	{
		if (typeof displayType != 'undefined')
			ob.BXDISPLAY = displayType;

		var d = ob.style.display || BX.style(ob, 'display');
		if (d != 'none')
		{
			ob.BXDISPLAY = ob.BXDISPLAY || d;
			return true;
		}
		else
		{
			ob.BXDISPLAY = ob.BXDISPLAY || 'block';
			return false;
		}
	}

	/* some useful util functions */

	BX.util = {
		/**
		 * @deprecated
		 * @use [].filter(value => !BX.Type.isNil(value))
		 * @param ar
		 * @return {*}
		 */
		array_values: function(ar)
		{
			if (!BX.type.isArray(ar))
				return BX.util._array_values_ob(ar);
			var arv = [];
			for(var i=0,l=ar.length;i<l;i++)
				if (ar[i] !== null && typeof ar[i] != 'undefined')
					arv.push(ar[i]);
			return arv;
		},

		/**
		 * @deprecated
		 * @use Object.values([]).filter(value => !BX.Type.isNil(value))
		 * @param ar
		 * @return {Array}
		 * @private
		 */
		_array_values_ob: function(ar)
		{
			var arv = [];
			for(var i in ar)
				if (ar[i] !== null && typeof ar[i] != 'undefined')
					arv.push(ar[i]);
			return arv;
		},

		/**
		 * @deprecated
		 * @use
		 * @param ar
		 * @return {*}
		 */
		array_keys: function(ar)
		{
			if (!BX.type.isArray(ar))
				return BX.util._array_keys_ob(ar);
			var arv = [];
			for(var i=0,l=ar.length;i<l;i++)
				if (ar[i] !== null && typeof ar[i] != 'undefined')
					arv.push(i);
			return arv;
		},

		_array_keys_ob: function(ar)
		{
			var arv = [];
			for(var i in ar)
				if (ar[i] !== null && typeof ar[i] != 'undefined')
					arv.push(i);
			return arv;
		},

		object_keys: function(obj)
		{
			var arv = [];
			for(var k in obj)
			{
				if(obj.hasOwnProperty(k))
				{
					arv.push(k);
				}
			}
			return arv;
		},

		/**
		 * @deprecated
		 * @use firstArr.concat(secondArr);
		 * @param first
		 * @param second
		 * @return {*[]}
		 */
		array_merge: function(first, second)
		{
			if (!BX.type.isArray(first)) first = [];
			if (!BX.type.isArray(second)) second = [];

			var i = first.length, j = 0;

			if (typeof second.length === "number")
			{
				for (var l = second.length; j < l; j++)
				{
					first[i++] = second[j];
				}
			}
			else
			{
				while (second[j] !== undefined)
				{
					first[i++] = second[j++];
				}
			}

			first.length = i;

			return first;
		},

		array_flip: function (object)
		{
			var newObject = {};

			for (var key in object)
			{
				newObject[object[key]] = key;
			}

			return newObject;
		},

		array_diff: function(ar1, ar2, hash)
		{
			hash = BX.type.isFunction(hash) ? hash : null;
			var i, length, v, h, map = {}, result = [];
			for(i = 0, length = ar2.length; i < length; i++)
			{
				v = ar2[i];
				h = hash ? hash(v) : v;
				map[h] = true;
			}

			for(i = 0, length = ar1.length; i < length; i++)
			{
				v = ar1[i];
				h = hash ? hash(v) : v;
				if(typeof(map[h]) === "undefined")
				{
					result.push(v);
				}
			}
			return result;
		},

		/**
		 * @deprecated
		 * @use Set
		 */
		array_unique: function(ar)
		{
			var i=0,j,len=ar.length;
			if(len<2) return ar;

			for (; i<len-1;i++)
			{
				for (j=i+1; j<len;j++)
				{
					if (ar[i]==ar[j])
					{
						ar.splice(j--,1); len--;
					}
				}
			}

			return ar;
		},

		/**
		 * @deprecated
		 * @use myArr.includes(needle)
		 */
		in_array: function(needle, haystack)
		{
			for(var i=0; i<haystack.length; i++)
			{
				if(haystack[i] == needle)
					return true;
			}
			return false;
		},

		/**
		 * @deprecated
		 * @use myArr.findIndex(item => item === needle);
		 */
		array_search: function(needle, haystack)
		{
			for(var i=0; i<haystack.length; i++)
			{
				if(haystack[i] == needle)
					return i;
			}
			return -1;
		},

		object_search_key: function(needle, haystack)
		{
			if (typeof haystack[needle] != 'undefined')
				return haystack[needle];

			for(var i in haystack)
			{
				if (typeof haystack[i] == "object")
				{
					var result = BX.util.object_search_key(needle, haystack[i]);
					if (result !== false)
						return result;
				}
			}
			return false;
		},

		trim: function(s)
		{
			if (BX.type.isString(s))
			{
				return s.trim();
			}

			return s;
		},

		urlencode: function(s){return encodeURIComponent(s);},

		// it may also be useful. via sVD.
		deleteFromArray: function(ar, ind) {return ar.slice(0, ind).concat(ar.slice(ind + 1));},
		insertIntoArray: function(ar, ind, el) {return ar.slice(0, ind).concat([el]).concat(ar.slice(ind));},

		htmlspecialchars: function(str)
		{
			return BX.Text.encode(str);
		},

		htmlspecialcharsback: function(str)
		{
			return BX.Text.decode(str);
		},

		// Quote regular expression characters plus an optional character
		preg_quote: function(str, delimiter)
		{
			if(!str.replace)
				return str;
			return str.replace(new RegExp('[.\\\\+*?\\[\\^\\]$(){}=!<>|:\\' + (delimiter || '') + '-]', 'g'), '\\$&');
		},

		jsencode: function(str)
		{
			if (!str || !str.replace)
				return str;

			var escapes =
				[
					{ c: "\\\\", r: "\\\\" }, // should be first
					{ c: "\\t", r: "\\t" },
					{ c: "\\n", r: "\\n" },
					{ c: "\\r", r: "\\r" },
					{ c: "\"", r: "\\\"" },
					{ c: "'", r: "\\'" },
					{ c: "<", r: "\\x3C" },
					{ c: ">", r: "\\x3E" },
					{ c: "\\u2028", r: "\\u2028" },
					{ c: "\\u2029", r: "\\u2029" }
				];
			for (var i = 0; i < escapes.length; i++)
				str = str.replace(new RegExp(escapes[i].c, 'g'), escapes[i].r);
			return str;
		},

		getCssName: function(jsName)
		{
			if (!BX.type.isNotEmptyString(jsName))
			{
				return "";
			}

			return jsName.replace(/[A-Z]/g, function(match) {
				return "-" + match.toLowerCase();
			});
		},

		getJsName: function(cssName)
		{
			var regex = /\-([a-z]){1}/g;
			if (regex.test(cssName))
			{
				return cssName.replace(regex, function(match, letter) {
					return letter.toUpperCase();
				});
			}

			return cssName;
		},

		nl2br: function(str)
		{
			if (!str || !str.replace)
				return str;

			return str.replace(/([^>])\n/g, '$1<br/>');
		},

		/**
		 * @deprecated
		 * @use .padStart() / .padEnd()
		 * @param input
		 * @param pad_length
		 * @param pad_string
		 * @param pad_type
		 * @return {*}
		 */
		str_pad: function(input, pad_length, pad_string, pad_type)
		{
			pad_string = pad_string || ' ';
			pad_type = pad_type || 'right';
			input = input.toString();

			if (pad_type === 'left')
			{
				return BX.util.str_pad_left(input, pad_length, pad_string);
			}

			return BX.util.str_pad_right(input, pad_length, pad_string);
		},

		str_pad_left: function(input, pad_length, pad_string)
		{
			return input.toString().padStart(pad_length, pad_string);
		},

		str_pad_right: function(input, pad_length, pad_string)
		{
			return input.toString().padEnd(pad_length, pad_string);
		},

		strip_tags: function(str)
		{
			return str.split(/<[^>]+>/g).join('');
		},

		strip_php_tags: function(str)
		{
			return str.replace(/<\?(.|[\r\n])*?\?>/g, '');
		},

		popup: function(url, width, height)
		{
			var w, h;
			if(BX.browser.IsOpera())
			{
				w = document.body.offsetWidth;
				h = document.body.offsetHeight;
			}
			else
			{
				w = screen.width;
				h = screen.height;
			}
			return window.open(url, '', 'status=no,scrollbars=yes,resizable=yes,width='+width+',height='+height+',top='+Math.floor((h - height)/2-14)+',left='+Math.floor((w - width)/2-5));
		},

		shuffle: function(array)
		{
			var temporaryValue, randomIndex;
			var currentIndex = array.length;

			while (0 !== currentIndex)
			{
				randomIndex = Math.floor(Math.random() * currentIndex);
				currentIndex -= 1;

				temporaryValue = array[currentIndex];
				array[currentIndex] = array[randomIndex];
				array[randomIndex] = temporaryValue;
			}

			return array;
		},

		// BX.util.objectSort(object, sortBy, sortDir) - Sort object by property
		// function params: 1 - object for sort, 2 - sort by property, 3 - sort direction (asc/desc)
		// return: sort array [[objectElement], [objectElement]] in sortDir direction

		// example: BX.util.objectSort({'L1': {'name': 'Last'}, 'F1': {'name': 'First'}}, 'name', 'asc');
		// return: [{'name' : 'First'}, {'name' : 'Last'}]
		objectSort: function(object, sortBy, sortDir)
		{
			sortDir = sortDir == 'asc'? 'asc': 'desc';

			var arItems = [], i;
			for (i in object)
			{
				if (object.hasOwnProperty(i) && object[i][sortBy])
				{
					arItems.push([i, object[i][sortBy]]);
				}
			}

			if (sortDir == 'asc')
			{
				arItems.sort(function(i, ii) {
					var s1, s2;
					if (BX.type.isDate(i[1]))
					{
						s1 = i[1].getTime();
					}
					else if (!isNaN(i[1]))
					{
						s1 = parseInt(i[1]);
					}
					else
					{
						s1 = i[1].toString().toLowerCase();
					}

					if (BX.type.isDate(ii[1]))
					{
						s2 = ii[1].getTime();
					}
					else if (!isNaN(ii[1]))
					{
						s2 = parseInt(ii[1]);
					}
					else
					{
						s2 = ii[1].toString().toLowerCase();
					}

					if (s1 > s2)
						return 1;
					else if (s1 < s2)
						return -1;
					else
						return 0;
				});
			}
			else
			{
				arItems.sort(function(i, ii) {
					var s1, s2;
					if (BX.type.isDate(i[1]))
					{
						s1 = i[1].getTime();
					}
					else if (!isNaN(i[1]))
					{
						s1 = parseInt(i[1]);
					}
					else
					{
						s1 = i[1].toString().toLowerCase();
					}

					if (BX.type.isDate(ii[1]))
					{
						s2 = ii[1].getTime();
					}
					else if (!isNaN(ii[1]))
					{
						s2 = parseInt(ii[1]);
					}
					else
					{
						s2 = ii[1].toString().toLowerCase();
					}

					if (s1 < s2)
						return 1;
					else if (s1 > s2)
						return -1;
					else
						return 0;
				});
			}

			var arReturnArray = Array();
			for (i = 0; i < arItems.length; i++)
			{
				arReturnArray.push(object[arItems[i][0]]);
			}

			return arReturnArray;
		},

		objectMerge: function()
		{
			return BX.mergeEx.apply(window, arguments);
		},

		objectClone : function(object)
		{
			return BX.clone(object, true);
		},

		// #fdf9e5 => {r=253, g=249, b=229}
		hex2rgb: function(color)
		{
			var rgb = color.replace(/[# ]/g,"").replace(/^(.)(.)(.)$/,'$1$1$2$2$3$3').match(/.{2}/g);
			for (var i=0;  i<3; i++)
			{
				rgb[i] = parseInt(rgb[i], 16);
			}
			return {'r':rgb[0],'g':rgb[1],'b':rgb[2]};
		},

		/**
		 * @deprecated
		 * @use BX.Uri
		 * @param url
		 * @param param
		 * @return {string}
		 */
		remove_url_param: function(url, param)
		{
			return BX.Uri.removeParam(url, param);
		},

		/*
		{'param1': 'value1', 'param2': 'value2'}
		 */
		/**
		 * @deprecated
		 * @use BX.Uri
		 * @param url
		 * @param params
		 * @return {string}
		 */
		add_url_param: function(url, params)
		{
			var preparedParams = Object.entries(params).reduce(function(acc, item) {
				acc[item[0]] = BX.type.isArray(item[1]) ? item[1].join() : item[1];
				return acc;
			}, {});

			return BX.Uri.addParam(url, preparedParams);
		},

		/*
	{'param1': 'value1', 'param2': 'value2'}
	 */
		buildQueryString: function(params)
		{
			var result = '';
			for (var key in params)
			{
				var value = params[key];
				if(BX.type.isArray(value))
				{
					value.forEach(function(valueElement, index)
					{
						result += encodeURIComponent(key + "[" + index + "]") + "=" + encodeURIComponent(valueElement) + "&";
					});
				}
				else
				{
					result += encodeURIComponent(key) + "=" + encodeURIComponent(value) + "&";
				}
			}

			if(result.length > 0)
			{
				result = result.substr(0, result.length - 1);
			}
			return result;
		},

		even: function(digit)
		{
			return (parseInt(digit) % 2 == 0);
		},

		hashCode: function(str)
		{
			if(!BX.type.isNotEmptyString(str))
			{
				return 0;
			}

			var hash = 0;
			for (var i = 0; i < str.length; i++)
			{
				var c = str.charCodeAt(i);
				hash = ((hash << 5) - hash) + c;
				hash = hash & hash;
			}
			return hash;
		},

		getRandomString: function (length)
		{
			return BX.Text.getRandom(length);
		},

		number_format: function(number, decimals, dec_point, thousands_sep)
		{
			var i, j, kw, kd, km, sign = '';
			decimals = Math.abs(decimals);
			if (isNaN(decimals) || decimals < 0)
			{
				decimals = 2;
			}
			dec_point = dec_point || ',';
			if (typeof thousands_sep === 'undefined')
				thousands_sep = '.';

			number = (+number || 0).toFixed(decimals);
			if (number < 0)
			{
				sign = '-';
				number = -number;
			}

			i = parseInt(number, 10) + '';
			j = (i.length > 3 ? i.length % 3 : 0);

			km = (j ? i.substr(0, j) + thousands_sep : '');
			kw = i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + thousands_sep);
			kd = (decimals ? dec_point + Math.abs(number - i).toFixed(decimals).replace(/-/, '0').slice(2) : '');

			return sign + km + kw + kd;
		},

		getExtension: function (url)
		{
			url = url || "";
			var items = url.split("?")[0].split(".");
			return items[items.length-1].toLowerCase();
		},
		addObjectToForm: function(object, form, prefix)
		{
			if(!BX.type.isString(prefix))
			{
				prefix = "";
			}

			for(var key in object)
			{
				if(!object.hasOwnProperty(key))
				{
					continue;
				}

				var value = object[key];
				var name = prefix !== "" ? (prefix + "[" + key + "]") : key;
				if(BX.type.isArray(value))
				{
					var obj = {};
					for(var i = 0; i < value.length; i++)
					{
						obj[i] = value[i];
					}

					BX.util.addObjectToForm(obj, form, name);
				}
				else if(BX.type.isPlainObject(value))
				{
					BX.util.addObjectToForm(value, form, name);
				}
				else
				{
					value = BX.type.isFunction(value.toString) ? value.toString() : "";
					if(value !== "")
					{
						form.appendChild(BX.create("INPUT", { attrs: { type: "hidden", name: name, value: value } }));
					}
				}
			}
		},

		observe: function(object, enable)
		{
			console.error('BX.util.observe: function is no longer supported by browser.');
			return false;
		},

		escapeRegExp: function(str)
		{
			return str.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&");
		}
	};

	BX.validation = {
		checkIfEmail: function(s)
		{
			var atom = "[=a-z0-9_+~'!$&*^`|#%/?{}-]";
			return (new RegExp('^\\s*'+atom+'+(\\.'+atom+'+)*@([a-z0-9-]+\\.)+[a-z0-9-]{2,20}\\s*$', 'i')).test(s);
		},
		checkIfPhone: function(s)
		{
			var regexp = new RegExp(
				typeof(BX.PhoneNumber) === "undefined"
					? BX.PhoneNumber.getValidNumberPattern()
					: '^\\s*\\+?\s*[0-9(-)\\s]+\\s*$',
				'i'
			);
			return regexp.test(s);
		}
	};

	BX.prop =
		{
			get: function(object, key, defaultValue)
			{
				return object && object.hasOwnProperty(key) ? object[key] : defaultValue;
			},
			getObject: function(object, key, defaultValue)
			{
				return object && BX.type.isPlainObject(object[key]) ? object[key] : defaultValue;
			},
			getElementNode: function(object, key, defaultValue)
			{
				return object && BX.type.isElementNode(object[key]) ? object[key] : defaultValue;
			},
			getArray: function(object, key, defaultValue)
			{
				return object && BX.type.isArray(object[key]) ? object[key] : defaultValue;
			},
			getFunction: function(object, key, defaultValue)
			{
				return object && BX.type.isFunction(object[key]) ? object[key] : defaultValue;
			},
			getNumber: function(object, key, defaultValue)
			{
				if(!(object && object.hasOwnProperty(key)))
				{
					return defaultValue;
				}

				var value = object[key];
				if(BX.type.isNumber(value))
				{
					return value;
				}

				value = parseFloat(value);
				return !isNaN(value) ? value : defaultValue;
			},
			getInteger: function(object, key, defaultValue)
			{
				if(!(object && object.hasOwnProperty(key)))
				{
					return defaultValue;
				}

				var value = object[key];
				if(BX.type.isNumber(value))
				{
					return value;
				}

				value = parseInt(value);
				return !isNaN(value) ? value : defaultValue;
			},
			getBoolean: function(object, key, defaultValue)
			{
				if(!(object && object.hasOwnProperty(key)))
				{
					return defaultValue;
				}

				var value = object[key];
				return (BX.type.isBoolean(value)
						? value
						: (BX.type.isString(value) ? (value.toLowerCase() === "true") : !!value)
				);
			},
			getString: function(object, key, defaultValue)
			{
				if(!(object && object.hasOwnProperty(key)))
				{
					return defaultValue;
				}

				var value = object[key];
				return BX.type.isString(value) ? value : (value ? value.toString() : '');
			},
			extractDate: function(datetime)
			{
				if(!BX.type.isDate(datetime))
				{
					datetime = new Date();
				}

				datetime.setHours(0);
				datetime.setMinutes(0);
				datetime.setSeconds(0);
				datetime.setMilliseconds(0);

				return datetime;
			}
		};

	BX.isNodeInDom = function(node, doc)
	{
		return node === (doc || document) ? true :
			(node.parentNode ? BX.isNodeInDom(node.parentNode) : false);
	};

	BX.isNodeHidden = function(node)
	{
		if (node === document)
			return false;
		else if (BX.style(node, 'display') == 'none')
			return true;
		else
			return (node.parentNode ? BX.isNodeHidden(node.parentNode) : true);
	};

	BX.evalPack = function(code)
	{
		while (code.length > 0)
		{
			var c = code.shift();

			if (c.TYPE == 'SCRIPT_EXT' || c.TYPE == 'SCRIPT_SRC')
			{
				BX.loadScript(c.DATA, function() {BX.evalPack(code)});
				return;
			}
			else if (c.TYPE == 'SCRIPT')
			{
				BX.evalGlobal(c.DATA);
			}
		}
	};

	BX.evalGlobal = function(data)
	{
		if (data)
		{
			var head = document.getElementsByTagName("head")[0] || document.documentElement,
				script = document.createElement("script");

			script.type = "text/javascript";

			if (!BX.browser.IsIE())
			{
				script.appendChild(document.createTextNode(data));
			}
			else
			{
				script.text = data;
			}

			head.insertBefore(script, head.firstChild);
			head.removeChild(script);
		}
	};

	BX.garbage = function(call, thisObject)
	{
		garbageCollectors.push({callback: call, context: thisObject});
	};

	BX.GetDocElement = function (pDoc)
	{
		pDoc = pDoc || document;
		return (BX.browser.IsDoctype(pDoc) ? pDoc.documentElement : pDoc.body);
	};

	BX.scrollTop = function(node, val){
		if(typeof val != 'undefined'){

			if(node == window){
				throw new Error('scrollTop() for window is not implemented');
			}else
				node.scrollTop = parseInt(val);

		}else{

			if(node == window)
				return BX.GetWindowScrollPos().scrollTop;

			return node.scrollTop;
		}
	};

	BX.scrollLeft = function(node, val){
		if(typeof val != 'undefined'){

			if(node == window){
				throw new Error('scrollLeft() for window is not implemented');
			}else
				node.scrollLeft = parseInt(val);

		}else{

			if(node == window)
				return BX.GetWindowScrollPos().scrollLeft;

			return node.scrollLeft;
		}
	};

	BX.hide_object = function(ob)
	{
		ob = BX(ob);
		ob.style.position = 'absolute';
		ob.style.top = '-1000px';
		ob.style.left = '-1000px';
		ob.style.height = '10px';
		ob.style.width = '10px';
	};

	BX.is_relative = function(el)
	{
		var p = BX.style(el, 'position');
		return p == 'relative' || p == 'absolute';
	};

	BX.is_float = function(el)
	{
		var p = BX.style(el, 'float');
		return p == 'right' || p == 'left';
	};

	BX.is_fixed = function(el)
	{
		var p = BX.style(el, 'position');
		return p == 'fixed';
	};

	BX.width = function(node, val){
		if(typeof val != 'undefined')
			BX.style(node, 'width', parseInt(val)+'px');
		else{

			if(node == window)
				return window.innerWidth;

			//return parseInt(BX.style(node, 'width'));
			return BX.pos(node).width;
		}
	};

	BX.height = function(node, val){
		if(typeof val != 'undefined')
			BX.style(node, 'height', parseInt(val)+'px');
		else{

			if(node == window)
				return window.innerHeight;

			//return parseInt(BX.style(node, 'height'));
			return BX.pos(node).height;
		}
	};

	BX.align = function(pos, w, h, type)
	{
		if (type)
			type = type.toLowerCase();
		else
			type = '';

		var pDoc = document;
		if (BX.type.isElementNode(pos))
		{
			pDoc = pos.ownerDocument;
			pos = BX.pos(pos);
		}

		var x = pos["left"], y = pos["bottom"];

		var scroll = BX.GetWindowScrollPos(pDoc);
		var size = BX.GetWindowInnerSize(pDoc);

		if((size.innerWidth + scroll.scrollLeft) - (pos["left"] + w) < 0)
		{
			if(pos["right"] - w >= 0 )
				x = pos["right"] - w;
			else
				x = scroll.scrollLeft;
		}

		if(((size.innerHeight + scroll.scrollTop) - (pos["bottom"] + h) < 0) || ~type.indexOf('top'))
		{
			if(pos["top"] - h >= 0 || ~type.indexOf('top'))
				y = pos["top"] - h;
			else
				y = scroll.scrollTop;
		}

		return {'left':x, 'top':y};
	};

	BX.scrollToNode = function(node)
	{
		var obNode = BX(node);

		if (obNode.scrollIntoView)
			obNode.scrollIntoView(true);
		else
		{
			var arNodePos = BX.pos(obNode);
			window.scrollTo(arNodePos.left, arNodePos.top);
		}
	};

	/* non-xhr loadings */
	BX.showWait = function(node, msg)
	{
		node = BX(node) || document.body || document.documentElement;
		msg = msg || BX.message('JS_CORE_LOADING');

		var container_id = node.id || Math.random();

		var obMsg = node.bxmsg = document.body.appendChild(BX.create('DIV', {
			props: {
				id: 'wait_' + container_id
			},
			style: {
				background: 'url("/bitrix/js/main/core/images/wait.gif") no-repeat scroll 10px center #fcf7d1',
				border: '1px solid #E1B52D',
				color: 'black',
				fontFamily: 'Verdana,Arial,sans-serif',
				fontSize: '11px',
				padding: '10px 30px 10px 37px',
				position: 'absolute',
				textAlign:'center'
			},
			text: msg
		}));

		BX.ZIndexManager.register(obMsg);
		BX.ZIndexManager.bringToFront(obMsg);

		setTimeout(BX.delegate(_adjustWait, node), 10);

		lastWait[lastWait.length] = obMsg;
		return obMsg;
	};

	BX.closeWait = function(node, obMsg)
	{
		if(node && !obMsg)
			obMsg = node.bxmsg;
		if(node && !obMsg && BX.hasClass(node, 'bx-core-waitwindow'))
			obMsg = node;
		if(node && !obMsg)
			obMsg = BX('wait_' + node.id);
		if(!obMsg)
			obMsg = lastWait.pop();

		if (obMsg && obMsg.parentNode)
		{
			for (var i=0,len=lastWait.length;i<len;i++)
			{
				if (obMsg == lastWait[i])
				{
					lastWait = BX.util.deleteFromArray(lastWait, i);
					break;
				}
			}

			BX.ZIndexManager.unregister(obMsg);
			obMsg.parentNode.removeChild(obMsg);
			if (node) node.bxmsg = null;
			BX.cleanNode(obMsg, true);
		}
	};

	BX.setJSList = function(scripts)
	{
		if (BX.type.isArray(scripts))
		{
			scripts = scripts.map(function(script) {
				return normalizeUrl(script)
			});

			jsList = jsList.concat(scripts);
		}
	};

	BX.getJSList = function()
	{
		initJsList();
		return jsList;
	};

	BX.setCSSList = function(cssFiles)
	{
		if (BX.type.isArray(cssFiles))
		{
			cssFiles = cssFiles.map(function(cssFile) {
				return normalizeUrl(cssFile);
			});

			cssList = cssList.concat(cssFiles);
		}
	};

	BX.getCSSList = function()
	{
		initCssList();
		return cssList;
	};

	BX.getJSPath = function(js)
	{
		return js.replace(/^(http[s]*:)*\/\/[^\/]+/i, '');
	};

	BX.getCSSPath = function(css)
	{
		return css.replace(/^(http[s]*:)*\/\/[^\/]+/i, '');
	};

	BX.getCDNPath = function(path)
	{
		return path;
	};

	BX.loadScript = function(script, callback, doc)
	{
		if (BX.type.isString(script))
		{
			script = [script];
		}

		return BX.load(script, callback, doc);
	};

	BX.loadCSS = function(css, doc, win)
	{
		if (BX.type.isString(css))
		{
			css = [css];
		}

		if (BX.type.isArray(css))
		{
			css = css.map(function(url) {
				return { url: url, ext: "css" }
			});

			BX.load(css, null, doc);
		}
	};

	const LOADING = 3;
	const LOADED = 4;
	const assets = {};
	const loadingAssetCallbacks = {};

	BX.load = function(items, callback, doc)
	{
		if (!BX.isReady)
		{
			var _args = arguments;
			BX.ready(function() {
				BX.load.apply(this, _args);
			});

			return null;
		}

		doc = doc || document;

		callback = BX.Type.isFunction(callback) ? callback : () => {};

		return loadAsync(items, callback, doc);
	};

	function loadAsync(items, callback, doc)
	{
		if (!BX.type.isArray(items))
		{
			callback();

			return;
		}

		function onLoad()
		{
			const nextAsset = queue.shift();
			if (nextAsset)
			{
				load(nextAsset, onLoad, doc);
			}
			else if (allLoaded())
			{
				callback();
			}
		}

		function allLoaded()
		{
			for (const name in assetMap)
			{
				if (assetMap[name].state !== LOADED)
				{
					return false;
				}
			}

			return true;
		}

		const queue = [];
		const assetMap = {};
		items.forEach(item => {
			const asset = getAsset(item);
			if (asset && asset.state !== LOADED)
			{
				queue.push(asset);
				assetMap[asset.name] = asset;
			}
		});

		if (queue.length > 0)
		{
			const maxParallelLoads = 6;
			const parallelLoads = Math.min(queue.length, maxParallelLoads);
			const firstPackage = queue.splice(0, parallelLoads);
			firstPackage.forEach(asset => {
				load(asset, onLoad, doc);
			});
		}
		else
		{
			callback();
		}
	}

	function load(asset, callback, doc)
	{
		callback = callback || BX.DoNothing;

		if (asset.state === LOADED)
		{
			callback();
			return;
		}

		if (asset.state === LOADING)
		{
			if (!BX.Type.isArray(loadingAssetCallbacks[asset.name]))
			{
				loadingAssetCallbacks[asset.name] = [];
			}

			loadingAssetCallbacks[asset.name].push(callback);

			return;
		}

		asset.state = LOADING;

		loadAsset(
			asset,
			function () {
				asset.state = LOADED;
				callback();
				if (BX.Type.isArrayFilled(loadingAssetCallbacks[asset.name]))
				{
					for (const cb of loadingAssetCallbacks[asset.name])
					{
						cb();
					}
				}

				delete loadingAssetCallbacks[asset.name];
			},
			doc
		);
	}

	function loadAsset(asset, callback, doc)
	{
		callback = callback || BX.DoNothing;

		function error(event)
		{
			window.clearTimeout(asset.errorTimeout);
			window.clearTimeout(asset.cssTimeout);
			ele.onload = ele.onreadystatechange = ele.onerror = null;
			callback();
		}

		function process(event)
		{
			event = event || window.event;
			if (event.type === "load" || (/loaded|complete/.test(ele.readyState) && (!doc.documentMode || doc.documentMode < 9)))
			{
				window.clearTimeout(asset.errorTimeout);
				window.clearTimeout(asset.cssTimeout);
				ele.onload = ele.onreadystatechange = ele.onerror = null;
				callback();
			}
		}

		function isCssLoaded()
		{
			if (asset.state !== LOADED && asset.cssRetries <= 20)
			{
				for (var i = 0, l = doc.styleSheets.length; i < l; i++)
				{
					if (doc.styleSheets[i].href === ele.href)
					{
						process({"type": "load"});
						return;
					}
				}

				asset.cssRetries++;
				asset.cssTimeout = window.setTimeout(isCssLoaded, 250);
			}
		}

		let ele = null;
		const ext = BX.type.isNotEmptyString(asset.ext) ? asset.ext : BX.util.getExtension(asset.url);

		if (ext === "css")
		{
			ele = doc.createElement("link");
			ele.type = "text/" + (asset.type || "css");
			ele.rel = "stylesheet";
			ele.href = asset.url;

			asset.cssRetries = 0;
			asset.cssTimeout = window.setTimeout(isCssLoaded, 500);
		}
		else
		{
			ele = doc.createElement("script");
			ele.type = "text/" + (asset.type || "javascript");
			ele.src = asset.url;
		}

		ele.onload = ele.onreadystatechange = process;
		ele.onerror = error;

		ele.async = false;
		ele.defer = false;

		asset.errorTimeout = window.setTimeout(function () {
			error({type: "timeout"});
		}, 7000);

		if (ext === "css")
		{
			cssList.push(normalizeMinUrl(normalizeUrl(asset.url)));
		}
		else
		{
			jsList.push(normalizeMinUrl(normalizeUrl(asset.url)));
		}

		let templateLink = null;
		const head = doc.head || doc.getElementsByTagName("head")[0];
		if (ext === "css" && (templateLink = getTemplateLink(head)) !== null)
		{
			templateLink.parentNode.insertBefore(ele, templateLink);
		}
		else
		{
			head.insertBefore(ele, head.lastChild);
		}
	}

	function getAsset(item)
	{
		var asset = {};
		if (typeof item === "object")
		{
			asset = item;
			asset.name = asset.name ? asset.name : BX.util.hashCode(item.url);
		}
		else
		{
			asset = { name: BX.util.hashCode(item), url : item };
		}

		var ext = BX.type.isNotEmptyString(asset.ext) ? asset.ext : BX.util.getExtension(asset.url);
		if ((ext === "css" && isCssLoaded(asset.url)) || isScriptLoaded(asset.url))
		{
			asset.state = LOADED;
		}

		var existing = assets[asset.name];
		if (existing && existing.url === asset.url)
		{
			return existing;
		}

		assets[asset.name] = asset;

		return asset;
	}

	function normalizeUrl(url)
	{
		if (!BX.type.isNotEmptyString(url))
		{
			return "";
		}

		url = BX.getJSPath(url);
		url = url.replace(/\?[0-9]*$/, "");

		return url;
	}

	function normalizeMinUrl(url)
	{
		if (!BX.type.isNotEmptyString(url))
		{
			return "";
		}

		var minPos = url.indexOf(".min");
		return minPos >= 0 ? url.substr(0, minPos) + url.substr(minPos + 4) : url;
	}

	function isCssLoaded(fileSrc)
	{
		initCssList();

		fileSrc = normalizeUrl(fileSrc);
		var fileSrcMin = normalizeMinUrl(fileSrc);

		return (fileSrc !== fileSrcMin && BX.util.in_array(fileSrcMin, cssList)) || BX.util.in_array(fileSrc, cssList);
	}

	function initCssList()
	{
		if(!cssInit)
		{
			var linksCol = document.getElementsByTagName('link');

			if(!!linksCol && linksCol.length > 0)
			{
				for(var i = 0; i < linksCol.length; i++)
				{
					var href = linksCol[i].getAttribute('href');
					if (BX.type.isNotEmptyString(href))
					{
						href = normalizeMinUrl(normalizeUrl(href));
						cssList.push(href);
					}
				}
			}
			cssInit = true;
		}
	}

	function getTemplateLink(head)
	{
		var findLink = function(tag)
		{
			var links = head.getElementsByTagName(tag);
			for (var i = 0, length = links.length; i < length; i++)
			{
				var templateStyle = links[i].getAttribute("data-template-style");
				if (BX.type.isNotEmptyString(templateStyle) && templateStyle == "true")
				{
					return links[i];
				}
			}

			return null;
		};

		var link = findLink("link");
		if (link === null)
		{
			link = findLink("style");
		}

		return link;
	}

	function isScriptLoaded(fileSrc)
	{
		initJsList();

		fileSrc = normalizeUrl(fileSrc);
		var fileSrcMin = normalizeMinUrl(fileSrc);

		return (fileSrc !== fileSrcMin && BX.util.in_array(fileSrcMin, jsList)) || BX.util.in_array(fileSrc, jsList);
	}

	function initJsList()
	{
		if(!jsInit)
		{
			var scriptCol = document.getElementsByTagName('script');

			if(!!scriptCol && scriptCol.length > 0)
			{
				for(var i=0; i<scriptCol.length; i++)
				{
					var src = scriptCol[i].getAttribute('src');

					if (BX.type.isNotEmptyString(src))
					{
						src = normalizeMinUrl(normalizeUrl(src));
						jsList.push(src);
					}
				}
			}
			jsInit = true;
		}
	}

	function reloadInternal(back_url, bAddClearCache)
	{
		if (back_url === true)
		{
			bAddClearCache = true;
			back_url = null;
		}

		var topWindow = (function() {
			if (BX.PageObject && BX.PageObject.getRootWindow)
			{
				return BX.PageObject.getRootWindow();
			}

			return window.top;
		})();
		var new_href = back_url || topWindow.location.href;

		var hashpos = new_href.indexOf('#'), hash = '';

		if (hashpos != -1)
		{
			hash = new_href.substr(hashpos);
			new_href = new_href.substr(0, hashpos);
		}

		if (bAddClearCache && new_href.indexOf('clear_cache=Y') < 0)
			new_href += (new_href.indexOf('?') == -1 ? '?' : '&') + 'clear_cache=Y';

		if (hash)
		{
			// hack for clearing cache in ajax mode components with history emulation
			if (bAddClearCache && (hash.substr(0, 5) == 'view/' || hash.substr(0, 6) == '#view/') && hash.indexOf('clear_cache%3DY') < 0)
				hash += (hash.indexOf('%3F') == -1 ? '%3F' : '%26') + 'clear_cache%3DY';

			new_href = new_href.replace(/(\?|\&)_r=[\d]*/, '');
			new_href += (new_href.indexOf('?') == -1 ? '?' : '&') + '_r='+Math.round(Math.random()*10000) + hash;
		}

		topWindow.location.href = new_href;
	}

	BX.reload = function(back_url, bAddClearCache)
	{
		if (window !== window.top)
		{
			BX.Runtime
				.loadExtension('main.pageobject')
				.then(function() {
					reloadInternal(back_url, bAddClearCache);
				});
		}
		else
		{
			reloadInternal(back_url, bAddClearCache);
		}
	};

	BX.clearCache = function()
	{
		BX.showWait();
		BX.reload(true);
	};

	BX.template = function(tpl, callback, bKillTpl)
	{
		BX.ready(function() {
			_processTpl(BX(tpl), callback, bKillTpl);
		});
	};

	BX.isAmPmMode = function(returnConst)
	{
		if (returnConst === true)
		{
			return BX.message.AMPM_MODE;
		}
		return BX.message.AMPM_MODE !== false;
	};

	BX.formatDate = function(date, format)
	{
		date = date || new Date();

		var bTime = date.getHours() || date.getMinutes() || date.getSeconds(),
			str = !!format
				? format :
				(bTime ? BX.message('FORMAT_DATETIME') : BX.message('FORMAT_DATE')
				);

		return str.replace(/YYYY/ig, date.getFullYear())
			.replace(/MMMM/ig, BX.util.str_pad_left((date.getMonth()+1).toString(), 2, '0'))
			.replace(/MM/ig, BX.util.str_pad_left((date.getMonth()+1).toString(), 2, '0'))
			.replace(/DD/ig, BX.util.str_pad_left(date.getDate().toString(), 2, '0'))
			.replace(/HH/ig, BX.util.str_pad_left(date.getHours().toString(), 2, '0'))
			.replace(/MI/ig, BX.util.str_pad_left(date.getMinutes().toString(), 2, '0'))
			.replace(/SS/ig, BX.util.str_pad_left(date.getSeconds().toString(), 2, '0'));
	};
	BX.formatName = function(user, template, login)
	{
		user = user || {};
		template = (template || '');
		var replacement = {
			TITLE : (user["TITLE"] || ''),
			NAME : (user["NAME"] || ''),
			LAST_NAME : (user["LAST_NAME"] || ''),
			SECOND_NAME : (user["SECOND_NAME"] || ''),
			LOGIN : (user["LOGIN"] || ''),
			NAME_SHORT : user["NAME"] ? user["NAME"].substr(0, 1) + '.' : '',
			LAST_NAME_SHORT : user["LAST_NAME"] ? user["LAST_NAME"].substr(0, 1) + '.' : '',
			SECOND_NAME_SHORT : user["SECOND_NAME"] ? user["SECOND_NAME"].substr(0, 1) + '.' : '',
			EMAIL : (user["EMAIL"] || ''),
			ID : (user["ID"] || ''),
			NOBR : "",
			'/NOBR' : ""
		}, result = template;
		for (var ii in replacement)
		{
			if (replacement.hasOwnProperty(ii))
			{
				result = result.replace("#" + ii+ "#", replacement[ii])
			}
		}
		result = result.replace(/([\s]+)/gi, " ").trim();
		if (result == "")
		{
			result = (login == "Y" ? replacement["LOGIN"] : "");
			result = (result == "" ? "Noname" : result);
		}
		return result;
	};

	BX.getNumMonth = function(month)
	{
		var wordMonthCut = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'];
		var wordMonth = ['january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december'];

		var q = month.toUpperCase();
		for (i = 1; i <= 12; i++)
		{
			if (q == BX.message('MON_'+i).toUpperCase() || q == BX.message('MONTH_'+i).toUpperCase() || q == wordMonthCut[i-1].toUpperCase() || q == wordMonth[i-1].toUpperCase())
			{
				return i;
			}
		}
		return month;
	};

	BX.parseDate = function(str, bUTC, formatDate, formatDatetime)
	{
		if (BX.type.isNotEmptyString(str))
		{
			if (!formatDate)
				formatDate = BX.message('FORMAT_DATE');
			if (!formatDatetime)
				formatDatetime = BX.message('FORMAT_DATETIME');

			var regMonths = '';
			for (i = 1; i <= 12; i++)
			{
				regMonths = regMonths + '|' + BX.message('MON_'+i);
			}

			var expr = new RegExp('([0-9]+|[a-z]+' + regMonths + ')', 'ig');
			var aDate = str.match(expr),
				aFormat = formatDate.match(/(DD|MI|MMMM|MM|M|YYYY)/ig),
				i, cnt,
				aDateArgs=[], aFormatArgs=[],
				aResult={};

			if (!aDate)
				return null;

			if(aDate.length > aFormat.length)
			{
				aFormat = formatDatetime.match(/(DD|MI|MMMM|MM|M|YYYY|HH|H|SS|TT|T|GG|G)/ig);
			}

			for(i = 0, cnt = aDate.length; i < cnt; i++)
			{
				if(BX.util.trim(aDate[i]) != '')
				{
					aDateArgs[aDateArgs.length] = aDate[i];
				}
			}

			for(i = 0, cnt = aFormat.length; i < cnt; i++)
			{
				if(BX.util.trim(aFormat[i]) != '')
				{
					aFormatArgs[aFormatArgs.length] = aFormat[i];
				}
			}


			var m = BX.util.array_search('MMMM', aFormatArgs);
			if (m > 0)
			{
				aDateArgs[m] = BX.getNumMonth(aDateArgs[m]);
				aFormatArgs[m] = "MM";
			}
			else
			{
				m = BX.util.array_search('M', aFormatArgs);
				if (m > 0)
				{
					aDateArgs[m] = BX.getNumMonth(aDateArgs[m]);
					aFormatArgs[m] = "MM";
				}
			}

			for(i = 0, cnt = aFormatArgs.length; i < cnt; i++)
			{
				var k = aFormatArgs[i].toUpperCase();
				aResult[k] = k == 'T' || k == 'TT' ? aDateArgs[i] : parseInt(aDateArgs[i], 10);
			}

			if(aResult['DD'] > 0 && aResult['MM'] > 0 && aResult['YYYY'] > 0)
			{
				var d = new Date();

				if(bUTC)
				{
					d.setUTCDate(1);
					d.setUTCFullYear(aResult['YYYY']);
					d.setUTCMonth(aResult['MM'] - 1);
					d.setUTCDate(aResult['DD']);
					d.setUTCHours(0, 0, 0, 0);
				}
				else
				{
					d.setDate(1);
					d.setFullYear(aResult['YYYY']);
					d.setMonth(aResult['MM'] - 1);
					d.setDate(aResult['DD']);
					d.setHours(0, 0, 0, 0);
				}

				if(
					(!isNaN(aResult['HH']) || !isNaN(aResult['GG']) || !isNaN(aResult['H']) || !isNaN(aResult['G']))
					&& !isNaN(aResult['MI'])
				)
				{
					if (!isNaN(aResult['H']) || !isNaN(aResult['G']))
					{
						var bPM = (aResult['T']||aResult['TT']||'am').toUpperCase()=='PM';
						var h = parseInt(aResult['H']||aResult['G']||0, 10);
						if(bPM)
						{
							aResult['HH'] = h + (h == 12 ? 0 : 12);
						}
						else
						{
							aResult['HH'] = h < 12 ? h : 0;
						}
					}
					else
					{
						aResult['HH'] = parseInt(aResult['HH']||aResult['GG']||0, 10);
					}

					if (isNaN(aResult['SS']))
						aResult['SS'] = 0;

					if(bUTC)
					{
						d.setUTCHours(aResult['HH'], aResult['MI'], aResult['SS']);
					}
					else
					{
						d.setHours(aResult['HH'], aResult['MI'], aResult['SS']);
					}
				}

				return d;
			}
		}

		return null;
	};

	BX.selectUtils =
		{
			addNewOption: function(oSelect, opt_value, opt_name, do_sort, check_unique)
			{
				oSelect = BX(oSelect);
				if(oSelect)
				{
					var n = oSelect.length;
					if(check_unique !== false)
					{
						for(var i=0;i<n;i++)
						{
							if(oSelect[i].value==opt_value)
							{
								return;
							}
						}
					}

					oSelect.options[n] = new Option(opt_name, opt_value, false, false);
				}

				if(do_sort === true)
				{
					this.sortSelect(oSelect);
				}
			},

			deleteOption: function(oSelect, opt_value)
			{
				oSelect = BX(oSelect);
				if(oSelect)
				{
					for(var i=0;i<oSelect.length;i++)
					{
						if(oSelect[i].value==opt_value)
						{
							oSelect.remove(i);
							break;
						}
					}
				}
			},

			deleteSelectedOptions: function(oSelect)
			{
				oSelect = BX(oSelect);
				if(oSelect)
				{
					var i=0;
					while(i<oSelect.length)
					{
						if(oSelect[i].selected)
						{
							oSelect[i].selected=false;
							oSelect.remove(i);
						}
						else
						{
							i++;
						}
					}
				}
			},

			deleteAllOptions: function(oSelect)
			{
				oSelect = BX(oSelect);
				if(oSelect)
				{
					for(var i=oSelect.length-1; i>=0; i--)
					{
						oSelect.remove(i);
					}
				}
			},

			optionCompare: function(record1, record2)
			{
				var value1 = record1.optText.toLowerCase();
				var value2 = record2.optText.toLowerCase();
				if (value1 > value2) return(1);
				if (value1 < value2) return(-1);
				return(0);
			},

			sortSelect: function(oSelect)
			{
				oSelect = BX(oSelect);
				if(oSelect)
				{
					var myOptions = [];
					var n = oSelect.options.length;
					var i;
					for (i=0;i<n;i++)
					{
						myOptions[i] = {
							optText:oSelect[i].text,
							optValue:oSelect[i].value
						};
					}
					myOptions.sort(this.optionCompare);
					oSelect.length=0;
					n = myOptions.length;
					for(i=0;i<n;i++)
					{
						oSelect[i] = new Option(myOptions[i].optText, myOptions[i].optValue, false, false);
					}
				}
			},

			selectAllOptions: function(oSelect)
			{
				oSelect = BX(oSelect);
				if(oSelect)
				{
					var n = oSelect.length;
					for(var i=0;i<n;i++)
					{
						oSelect[i].selected=true;
					}
				}
			},

			selectOption: function(oSelect, opt_value)
			{
				oSelect = BX(oSelect);
				if(oSelect)
				{
					var n = oSelect.length;
					for(var i=0;i<n;i++)
					{
						oSelect[i].selected = (oSelect[i].value == opt_value);
					}
				}
			},

			addSelectedOptions: function(oSelect, to_select_id, check_unique, do_sort)
			{
				oSelect = BX(oSelect);
				if(!oSelect)
					return;
				var n = oSelect.length;
				for(var i=0; i<n; i++)
					if(oSelect[i].selected)
						this.addNewOption(to_select_id, oSelect[i].value, oSelect[i].text, do_sort, check_unique);
			},

			moveOptionsUp: function(oSelect)
			{
				oSelect = BX(oSelect);
				if(!oSelect)
					return;
				var n = oSelect.length;
				for(var i=0; i<n; i++)
				{
					if(oSelect[i].selected && i>0 && oSelect[i-1].selected == false)
					{
						var option = new Option(oSelect[i].text, oSelect[i].value);
						oSelect[i] = new Option(oSelect[i-1].text, oSelect[i-1].value);
						oSelect[i].selected = false;
						oSelect[i-1] = option;
						oSelect[i-1].selected = true;
					}
				}
			},

			moveOptionsDown: function(oSelect)
			{
				oSelect = BX(oSelect);
				if(!oSelect)
					return;
				var n = oSelect.length;
				for(var i=n-1; i>=0; i--)
				{
					if(oSelect[i].selected && i<n-1 && oSelect[i+1].selected == false)
					{
						var option = new Option(oSelect[i].text, oSelect[i].value);
						oSelect[i] = new Option(oSelect[i+1].text, oSelect[i+1].value);
						oSelect[i].selected = false;
						oSelect[i+1] = option;
						oSelect[i+1].selected = true;
					}
				}
			}
		};

	BX.getEventTarget = function(e)
	{
		if(e.target)
		{
			return e.target;
		}
		else if(e.srcElement)
		{
			return e.srcElement;
		}
		return null;
	};

	BX.convert = {
		toNumber: function(value)
		{
			if(BX.type.isNumber(value))
			{
				return value;
			}

			value = Number(value);
			return !isNaN(value) ? value : 0;
		},
		nodeListToArray: function(nodes)
		{
			try
			{
				return (Array.prototype.slice.call(nodes, 0));
			}
			catch (ex)
			{
				var ary = [];
				for(var i = 0, l = nodes.length; i < l; i++)
				{
					ary.push(nodes[i]);
				}
				return ary;
			}
		}
	};

	/******* HINT ***************/
// if function has 2 params - the 2nd one is hint html. otherwise hint_html is third and hint_title - 2nd;
// '<div onmouseover="BX.hint(this, 'This is &lt;b&gt;Hint&lt;/b&gt;')"'>;
// BX.hint(el, 'This is <b>Hint</b>') - this won't work, use constructor
	BX.hint = function(el, hint_title, hint_html, hint_id)
	{
		if (null == hint_html)
		{
			hint_html = hint_title;
			hint_title = '';
		}

		if (null == el.BXHINT)
		{
			el.BXHINT = new BX.CHint({
				parent: el, hint: hint_html, title: hint_title, id: hint_id
			});
			el.BXHINT.Show();
		}
	};

	BX.hint_replace = function(el, hint_title, hint_html)
	{
		if (null == hint_html)
		{
			hint_html = hint_title;
			hint_title = '';
		}

		if (!el || !el.parentNode || !hint_html)
			return null;

		var obHint = new BX.CHint({
			hint: hint_html,
			title: hint_title
		});

		obHint.CreateParent();

		el.parentNode.insertBefore(obHint.PARENT, el);
		el.parentNode.removeChild(el);

		obHint.PARENT.style.marginLeft = '5px';

		return el;
	};

	BX.CHint = function(params)
	{
		if (BX.CHint.cssLoaded === false)
		{
			BX.load(['/bitrix/js/main/core/css/core_hint.css']);
			BX.CHint.cssLoaded = true;
		}

		this.PARENT = BX(params.parent);

		this.HINT = params.hint;
		this.HINT_TITLE = params.title;

		this.PARAMS = {};
		for (var i in this.defaultSettings)
		{
			if (null == params[i])
				this.PARAMS[i] = this.defaultSettings[i];
			else
				this.PARAMS[i] = params[i];
		}

		if (null != params.id)
			this.ID = params.id;

		this.timer = null;
		this.bInited = false;
		this.msover = true;

		if (this.PARAMS.showOnce)
		{
			this.__show();
			this.msover = false;
			this.timer = setTimeout(BX.proxy(this.__hide, this), this.PARAMS.hide_timeout);
		}
		else if (this.PARENT)
		{
			BX.bind(this.PARENT, 'mouseover', BX.proxy(this.Show, this));
			BX.bind(this.PARENT, 'mouseout', BX.proxy(this.Hide, this));
		}
	};

	BX.CHint.cssLoaded = false;

	BX.CHint.openHints = new Set();

	BX.CHint.globalDisabled = false;

	BX.CHint.handleMenuOpen = function() {
		BX.CHint.globalDisabled = true;

		BX.CHint.openHints.forEach(function(hint) {
			hint.__hide_immediately();
		});
	};

	BX.CHint.handleMenuClose = function() {
		BX.CHint.globalDisabled = false;
	};

	BX.addCustomEvent('onMenuOpen', BX.CHint.handleMenuOpen);
	BX.addCustomEvent('onMenuClose', BX.CHint.handleMenuClose);

	BX.CHint.prototype.defaultSettings = {
		show_timeout: 1000,
		hide_timeout: 500,
		dx: 2,
		showOnce: false,
		preventHide: true,
		min_width: 250
	};

	BX.CHint.prototype.CreateParent = function(element, params)
	{
		if (this.PARENT)
		{
			BX.unbind(this.PARENT, 'mouseover', BX.proxy(this.Show, this));
			BX.unbind(this.PARENT, 'mouseout', BX.proxy(this.Hide, this));
		}

		if (!params) params = {};
		var type = 'icon';

		if (params.type && (params.type == "link" || params.type == "icon"))
			type = params.type;

		if (element)
			type = "element";

		if (type == "icon")
		{
			element = BX.create('IMG', {
				props: {
					src: params.iconSrc
						? params.iconSrc
						: "/bitrix/js/main/core/images/hint.gif"
				}
			});
		}
		else if (type == "link")
		{
			element = BX.create("A", {
				props: {href: 'javascript:void(0)'},
				html: '[?]'
			});
		}

		this.PARENT = element;

		BX.bind(this.PARENT, 'mouseover', BX.proxy(this.Show, this));
		BX.bind(this.PARENT, 'mouseout', BX.proxy(this.Hide, this));

		return this.PARENT;
	};

	BX.CHint.prototype.Show = function()
	{
		this.msover = true;

		if (null != this.timer)
			clearTimeout(this.timer);

		this.timer = setTimeout(BX.proxy(this.__show, this), this.PARAMS.show_timeout);
	};

	BX.CHint.prototype.Hide = function()
	{
		this.msover = false;

		if (null != this.timer)
			clearTimeout(this.timer);

		this.timer = setTimeout(BX.proxy(this.__hide, this), this.PARAMS.hide_timeout);
	};

	BX.CHint.prototype.__show = function()
	{
		if (!this.msover || this.disabled || BX.CHint.globalDisabled) return;
		if (!this.bInited) this.Init();

		if (this.prepareAdjustPos())
		{
			this.DIV.style.display = 'block';
			BX.ZIndexManager.bringToFront(this.DIV);

			this.adjustPos();

			BX.CHint.openHints.add(this);

			BX.bind(window, 'scroll', BX.proxy(this.__onscroll, this));

			if (this.PARAMS.showOnce)
			{
				this.timer = setTimeout(BX.proxy(this.__hide, this), this.PARAMS.hide_timeout);
			}
		}
	};

	BX.CHint.prototype.__onscroll = function()
	{
		if (!BX.admin || !BX.admin.panel || !BX.admin.panel.isFixed()) return;

		if (this.scrollTimer) clearTimeout(this.scrollTimer);

		this.DIV.style.display = 'none';
		this.scrollTimer = setTimeout(BX.proxy(this.Reopen, this), this.PARAMS.show_timeout);
	};

	BX.CHint.prototype.Reopen = function()
	{
		if (null != this.timer) clearTimeout(this.timer);
		this.timer = setTimeout(BX.proxy(this.__show, this), 50);
	};

	BX.CHint.prototype.__hide = function()
	{
		if (this.msover) return;
		if (!this.bInited) return;

		BX.unbind(window, 'scroll', BX.proxy(this.Reopen, this));

		BX.CHint.openHints.delete(this);

		if (this.PARAMS.showOnce)
		{
			this.Destroy();
		}
		else
		{
			this.DIV.style.display = 'none';
		}
	};

	BX.CHint.prototype.__hide_immediately = function()
	{
		this.msover = false;
		this.__hide();
	};

	BX.CHint.prototype.Init = function()
	{
		this.DIV = document.body.appendChild(BX.create('DIV', {
			props: {className: 'bx-panel-tooltip'},
			style: {
				display: 'none',
				position: 'absolute',
				visibility: 'hidden'
			},
			children: [
				(this.CONTENT = BX.create('DIV', {
					props: {className: 'bx-panel-tooltip-content'},
					children: [
						BX.create('DIV', {
							props: {className: 'bx-panel-tooltip-underlay'},
							children: [
								BX.create('DIV', {props: {className: 'bx-panel-tooltip-underlay-bg'}})
							]
						})
					]
				}))
			]
		}));

		BX.ZIndexManager.register(this.DIV);

		if (this.ID)
		{
			this.CONTENT.insertBefore(BX.create('A', {
				attrs: {href: 'javascript:void(0)'},
				props: {className: 'bx-panel-tooltip-close'},
				events: {click: BX.delegate(this.Close, this)}
			}), this.CONTENT.firstChild);
		}

		if (this.HINT_TITLE)
		{
			this.CONTENT.appendChild(
				BX.create('DIV', {
					props: {className: 'bx-panel-tooltip-title'},
					text: this.HINT_TITLE
				})
			);
		}

		if (this.HINT)
		{
			this.CONTENT_TEXT = this.CONTENT.appendChild(BX.create('DIV', {props: {className: 'bx-panel-tooltip-text'}})).appendChild(BX.create('SPAN', {html: this.HINT}));
		}

		if (this.PARAMS.preventHide)
		{
			BX.bind(this.DIV, 'mouseout', BX.proxy(this.Hide, this));
			BX.bind(this.DIV, 'mouseover', BX.proxy(this.Show, this));
		}

		this.bInited = true;
	};

	BX.CHint.prototype.setContent = function(content)
	{
		this.HINT = content;

		if (this.CONTENT_TEXT)
			this.CONTENT_TEXT.innerHTML = this.HINT;
		else
			this.CONTENT_TEXT = this.CONTENT.appendChild(BX.create('DIV', {props: {className: 'bx-panel-tooltip-text'}})).appendChild(BX.create('SPAN', {html: this.HINT}));
	};

	BX.CHint.prototype.prepareAdjustPos = function()
	{
		this._wnd = {scrollPos: BX.GetWindowScrollPos(),scrollSize:BX.GetWindowScrollSize()};
		return BX.style(this.PARENT, 'display') != 'none';
	};

	BX.CHint.prototype.getAdjustPos = function()
	{
		var res = {}, pos = BX.pos(this.PARENT), min_top = 0;

		res.top = pos.bottom + this.PARAMS.dx;

		if (BX.admin && BX.admin.panel.DIV)
		{
			min_top = BX.admin.panel.DIV.offsetHeight + this.PARAMS.dx;

			if (BX.admin.panel.isFixed())
			{
				min_top += this._wnd.scrollPos.scrollTop;
			}
		}

		if (res.top < min_top)
			res.top = min_top;
		else
		{
			if (res.top + this.DIV.offsetHeight > this._wnd.scrollSize.scrollHeight)
				res.top = pos.top - this.PARAMS.dx - this.DIV.offsetHeight;
		}

		res.left = pos.left;
		if (pos.left < this.PARAMS.dx)
			pos.left = this.PARAMS.dx;
		else
		{
			var floatWidth = this.DIV.offsetWidth;

			var max_left = this._wnd.scrollSize.scrollWidth - floatWidth - this.PARAMS.dx;

			if (res.left > max_left)
				res.left = max_left;
		}

		return res;
	};

	BX.CHint.prototype.adjustWidth = function()
	{
		if (this.bWidthAdjusted) return;

		var w = this.DIV.offsetWidth, h = this.DIV.offsetHeight;

		if (w > this.PARAMS.min_width)
			w = Math.round(Math.sqrt(1.618*w*h));

		if (w < this.PARAMS.min_width)
			w = this.PARAMS.min_width;

		this.DIV.style.width = w + "px";

		if (this._adjustWidthInt)
			clearInterval(this._adjustWidthInt);
		this._adjustWidthInt = setInterval(BX.delegate(this._adjustWidthInterval, this), 5);

		this.bWidthAdjusted = true;
	};

	BX.CHint.prototype._adjustWidthInterval = function()
	{
		if (!this.DIV || this.DIV.style.display == 'none')
			clearInterval(this._adjustWidthInt);

		var
			dW = 20,
			maxWidth = 1500,
			w = this.DIV.offsetWidth,
			w1 = this.CONTENT_TEXT.offsetWidth;

		if (w > 0 && w1 > 0 && w - w1 < dW && w < maxWidth)
		{
			this.DIV.style.width = (w + dW) + "px";
			return;
		}

		clearInterval(this._adjustWidthInt);
	};

	BX.CHint.prototype.adjustPos = function()
	{
		this.adjustWidth();

		var pos = this.getAdjustPos();

		this.DIV.style.top = pos.top + 'px';
		this.DIV.style.left = pos.left + 'px';
	};

	BX.CHint.prototype.Close = function()
	{
		if (this.ID && BX.WindowManager)
			BX.WindowManager.saveWindowOptions(this.ID, {display: 'off'});
		this.__hide_immediately();
		this.Destroy();
	};

	BX.CHint.prototype.Destroy = function()
	{
		if (this.PARENT)
		{
			BX.unbind(this.PARENT, 'mouseover', BX.proxy(this.Show, this));
			BX.unbind(this.PARENT, 'mouseout', BX.proxy(this.Hide, this));
		}

		if (this.DIV)
		{
			BX.unbind(this.DIV, 'mouseover', BX.proxy(this.Show, this));
			BX.unbind(this.DIV, 'mouseout', BX.proxy(this.Hide, this));

			BX.ZIndexManager.unregister(this.DIV);

			BX.cleanNode(this.DIV, true);
		}
	};

	BX.CHint.prototype.enable = function(){this.disabled = false;};
	BX.CHint.prototype.disable = function(){this.__hide_immediately(); this.disabled = true;};


	function _adjustWait()
	{
		if (!this.bxmsg) return;

		var arContainerPos = BX.pos(this),
			div_top = arContainerPos.top;

		if (div_top < BX.GetDocElement().scrollTop)
			div_top = BX.GetDocElement().scrollTop + 5;

		this.bxmsg.style.top = (div_top + 5) + 'px';

		if (this == BX.GetDocElement())
		{
			this.bxmsg.style.right = '5px';
		}
		else
		{
			this.bxmsg.style.left = (arContainerPos.right - this.bxmsg.offsetWidth - 5) + 'px';
		}
	}

	function _processTpl(tplNode, cb, bKillTpl)
	{
		if (tplNode)
		{
			if (bKillTpl)
				tplNode.parentNode.removeChild(tplNode);

			var res = {}, nodes = BX.findChildren(tplNode, {attribute: 'data-role'}, true);

			for (var i = 0, l = nodes.length; i < l; i++)
			{
				res[nodes[i].getAttribute('data-role')] = nodes[i];
			}

			cb.apply(tplNode, [res]);
		}
	}

	function _checkNode(obj, params)
	{
		params = params || {};

		if (BX.type.isFunction(params))
			return params.call(window, obj);

		if (!params.allowTextNodes && !BX.type.isElementNode(obj))
			return false;
		var i,j,len;
		for (i in params)
		{
			if(params.hasOwnProperty(i))
			{
				switch(i)
				{
					case 'tag':
					case 'tagName':
						if (BX.type.isString(params[i]))
						{
							if (obj.tagName.toUpperCase() != params[i].toUpperCase())
								return false;
						}
						else if (params[i] instanceof RegExp)
						{
							if (!params[i].test(obj.tagName))
								return false;
						}
						break;

					case 'class':
					case 'className':
						if (BX.type.isString(params[i]))
						{
							if (!BX.hasClass(obj, params[i]))
								return false;
						}
						else if (params[i] instanceof RegExp)
						{
							if (!BX.type.isString(obj.className) || !params[i].test(obj.className))
								return false;
						}
						break;

					case 'attr':
					case 'attrs':
					case 'attribute':
						if (BX.type.isString(params[i]))
						{
							if (!obj.getAttribute(params[i]))
								return false;
						}
						else if (BX.type.isArray(params[i]))
						{
							for (j = 0, len = params[i].length; j < len; j++)
							{
								if (params[i] && !obj.getAttribute(params[i]))
									return false;
							}
						}
						else
						{
							for (j in params[i])
							{
								if(params[i].hasOwnProperty(j))
								{
									var q = obj.getAttribute(j);
									if (params[i][j] instanceof RegExp)
									{
										if (!BX.type.isString(q) || !params[i][j].test(q))
										{
											return false;
										}
									}
									else
									{
										if (q != '' + params[i][j])
										{
											return false;
										}
									}
								}
							}
						}
						break;

					case 'property':
					case 'props':
						if (BX.type.isString(params[i]))
						{
							if (!obj[params[i]])
								return false;
						}
						else if (BX.type.isArray(params[i]))
						{
							for (j = 0, len = params[i].length; j < len; j++)
							{
								if (params[i] && !obj[params[i]])
									return false;
							}
						}
						else
						{
							for (j in params[i])
							{
								if (BX.type.isString(params[i][j]))
								{
									if (obj[j] != params[i][j])
										return false;
								}
								else if (params[i][j] instanceof RegExp)
								{
									if (!BX.type.isString(obj[j]) || !params[i][j].test(obj[j]))
										return false;
								}
							}
						}
						break;

					case 'callback':
						return params[i](obj);
				}
			}
		}

		return true;
	}

	/* garbage collector */
	function Trash()
	{
		var i,len;

		for (i = 0, len = garbageCollectors.length; i<len; i++)
		{
			try {
				garbageCollectors[i].callback.apply(garbageCollectors[i].context || window);
				delete garbageCollectors[i];
				garbageCollectors[i] = null;
			} catch (e) {}
		}
	}

	if(window.attachEvent) // IE
		window.attachEvent("onunload", Trash);
	else if(window.addEventListener) // Gecko / W3C
		window.addEventListener('unload', Trash, false);
	else
		window.onunload = Trash;
	/* \garbage collector */

// set empty ready handler
	BX(BX.DoNothing);
	window.BX = BX;

	BX.browser.addGlobalClass();

	/* data storage */
	BX.data = function(node, key, value)
	{
		if(typeof node == 'undefined')
			return undefined;

		if(typeof key == 'undefined')
			return undefined;

		if(typeof value != 'undefined')
		{
			// write to manager
			dataStorage.set(node, key, value);
		}
		else
		{
			var data;

			// from manager
			if((data = dataStorage.get(node, key)) != undefined)
			{
				return data;
			}
			else
			{
				// from attribute data-*
				if('getAttribute' in node)
				{
					data = node.getAttribute('data-'+key.toString());
					if(data === null)
					{
						return undefined;
					}
					return data;
				}
			}

			return undefined;
		}
	};

	BX.DataStorage = function()
	{

		this.keyOffset = 1;
		this.data = {};
		this.uniqueTag = 'BX-'+Math.random();

		this.resolve = function(owner, create){
			if(typeof owner[this.uniqueTag] == 'undefined')
				if(create)
				{
					try
					{
						Object.defineProperty(owner, this.uniqueTag, {
							value: this.keyOffset++
						});
					}
					catch(e)
					{
						owner[this.uniqueTag] = this.keyOffset++;
					}
				}
				else
					return undefined;

			return owner[this.uniqueTag];
		};
		this.get = function(owner, key){
			if((owner != document && !BX.type.isElementNode(owner)) || typeof key == 'undefined')
				return undefined;

			owner = this.resolve(owner, false);

			if(typeof owner == 'undefined' || typeof this.data[owner] == 'undefined')
				return undefined;

			return this.data[owner][key];
		};
		this.set = function(owner, key, value){

			if((owner != document && !BX.type.isElementNode(owner)) || typeof value == 'undefined')
				return;

			var o = this.resolve(owner, true);

			if(typeof this.data[o] == 'undefined')
				this.data[o] = {};

			this.data[o][key] = value;
		};
	};

// some internal variables for new logic
	var dataStorage = new BX.DataStorage();	// manager which BX.data() uses to keep data
})(window.BX);


;(function(window)
{
	/****************** ATTENTION *******************************
	 * Please do not use Bitrix CoreJS in this class.
	 * This class can be called on page without Bitrix Framework
	*************************************************************/

	if (!window.BX)
	{
		window.BX = {};
	}

	var BX = window.BX;

	BX.Promise = function(fn, ctx) // fn is future-reserved
	{
		this.state = null;
		this.value = null;
		this.reason = null;
		this.next = null;
		this.ctx = ctx || this;

		this.onFulfilled = [];
		this.onRejected = [];
	};
	BX.Promise.prototype.fulfill = function(value)
	{
		this.checkState();

		this.value = value;
		this.state = true;
		this.execute();
	};
	BX.Promise.prototype.reject = function(reason)
	{
		this.checkState();

		this.reason = reason;
		this.state = false;
		this.execute();
	};
	BX.Promise.prototype.then = function(onFulfilled, onRejected)
	{
		if(typeof (onFulfilled) == "function" || onFulfilled instanceof Function)
		{
			this.onFulfilled.push(onFulfilled);
		}
		if(typeof (onRejected) == "function" || onRejected instanceof Function)
		{
			this.onRejected.push(onRejected);
		}

		if(this.next === null)
		{
			this.next = new BX.Promise(null, this.ctx);
		}

		if(this.state !== null) // if promise was already resolved, execute immediately
		{
			this.execute();
		}

		return this.next;
	};

	BX.Promise.prototype.catch = function(onRejected)
	{
		if(typeof (onRejected) == "function" || onRejected instanceof Function)
		{
			this.onRejected.push(onRejected);
		}

		if(this.next === null)
		{
			this.next = new BX.Promise(null, this.ctx);
		}

		if(this.state !== null) // if promise was already resolved, execute immediately
		{
			this.execute();
		}

		return this.next;
	};

	BX.Promise.prototype.setAutoResolve = function(way, ms)
	{
		this.timer = setTimeout(function(){
			if(this.state === null)
			{
				this[way ? 'fulfill' : 'reject']();
			}
		}.bind(this), ms || 15);
	};
	BX.Promise.prototype.cancelAutoResolve = function()
	{
		clearTimeout(this.timer);
	};
	/**
	 * Resolve function. This function allows promise chaining, like ..then().then()...
	 * Typical usage:
	 *
	 * var p = new Promise();
	 *
	 * p.then(function(value){
	 *  return someValue; // next promise in the chain will be fulfilled with someValue
	 * }).then(function(value){
	 *
	 *  var p1 = new Promise();
	 *  *** some async code here, that eventually resolves p1 ***
	 *
	 *  return p1; // chain will resume when p1 resolved (fulfilled or rejected)
	 * }).then(function(value){
	 *
	 *  // you can also do
	 *  var e = new Error();
	 *  throw e;
	 *  // it will cause next promise to be rejected with e
	 *
	 *  return someOtherValue;
	 * }).then(function(value){
	 *  ...
	 * }, function(reason){
	 *  // promise was rejected with reason
	 * })...;
	 *
	 * p.fulfill('let`s start this chain');
	 *
	 * @param x
	 */
	BX.Promise.prototype.resolve = function(x)
	{
		var this_ = this;

		if(this === x)
		{
			this.reject(new TypeError('Promise cannot fulfill or reject itself')); // avoid recursion
		}
		// allow "pausing" promise chaining until promise x is fulfilled or rejected
		else if(x && x.toString() === "[object BX.Promise]")
		{
			x.then(function(value){
				this_.fulfill(value);
			}, function(reason){
				this_.reject(reason);
			});
		}
		else // auto-fulfill this promise
		{
			this.fulfill(x);
		}
	};

	BX.Promise.prototype.toString = function()
	{
		return "[object BX.Promise]";
	};

	BX.Promise.prototype.execute = function()
	{
		if(this.state === null)
		{
			//then() must not be called before BX.Promise resolve() happens
			return;
		}

		var value = undefined;
		var reason = undefined;
		var x = undefined;
		var k;
		if(this.state === true) // promise was fulfill()-ed
		{
			if(this.onFulfilled.length)
			{
				try
				{
					for(k = 0; k < this.onFulfilled.length; k++)
					{
						x = this.onFulfilled[k].apply(this.ctx, [this.value]);
						if(typeof x != 'undefined')
						{
							value = x;
						}
					}
				}
				catch(e)
				{
					if('console' in window)
					{
						console.dir(e);
					}

					if (typeof BX.debug !== 'undefined')
					{
						BX.debug(e);
					}

					reason = e; // reject next
				}
			}
			else
			{
				value = this.value; // resolve next
			}
		}
		else if(this.state === false) // promise was reject()-ed
		{
			if(this.onRejected.length)
			{
				try
				{
					for(k = 0; k < this.onRejected.length; k++)
					{
						x = this.onRejected[k].apply(this.ctx, [this.reason]);
						if(typeof x != 'undefined')
						{
							value = x;
						}
					}
				}
				catch(e)
				{
					if('console' in window)
					{
						console.dir(e);
					}

					if (typeof BX.debug !== 'undefined')
					{
						BX.debug(e);
					}

					reason = e; // reject next
				}
			}
			else
			{
				reason = this.reason; // reject next
			}
		}

		if(this.next !== null)
		{
			if(typeof reason != 'undefined')
			{
				this.next.reject(reason);
			}
			else if(typeof value != 'undefined')
			{
				this.next.resolve(value);
			}
		}
	};
	BX.Promise.prototype.checkState = function()
	{
		if(this.state !== null)
		{
			throw new Error('You can not do fulfill() or reject() multiple times');
		}
	};
})(window);



;(function(window){

if (window.BX.ajax)
	return;

var
	BX = window.BX,

	tempDefaultConfig = {},
	defaultConfig = {
		method: 'GET', // request method: GET|POST
		dataType: 'html', // type of data loading: html|json|script
		timeout: 0, // request timeout in seconds. 0 for browser-default
		async: true, // whether request is asynchronous or not
		processData: true, // any data processing is disabled if false, only callback call
		scriptsRunFirst: false, // whether to run _all_ found scripts before onsuccess call. script tag can have an attribute "bxrunfirst" to turn  this flag on only for itself
		emulateOnload: true,
		skipAuthCheck: false, // whether to check authorization failure (SHOUD be set to true for CORS requests)
		start: true, // send request immediately (if false, request can be started manually via XMLHttpRequest object returned)
		cache: true, // whether NOT to add random addition to URL
		preparePost: true, // whether set Content-Type x-www-form-urlencoded in POST
		headers: false, // add additional headers, example: [{'name': 'If-Modified-Since', 'value': 'Wed, 15 Aug 2012 08:59:08 GMT'}, {'name': 'If-None-Match', 'value': '0'}]
		lsTimeout: 30, //local storage data TTL. useless without lsId.
		lsForce: false //wheter to force query instead of using localStorage data. useless without lsId.
/*
other parameters:
	url: url to get/post
	data: data to post
	onsuccess: successful request callback. BX.proxy may be used.
	onfailure: request failure callback. BX.proxy may be used.
	onprogress: request progress callback. BX.proxy may be used.

	lsId: local storage id - for constantly updating queries which can communicate via localStorage. core_ls.js needed

any of the default parameters can be overridden. defaults can be changed by BX.ajax.Setup() - for all further requests!
*/
	},
	loadedScripts = {},
	loadedScriptsQueue = [],
	r = {
		'url_utf': /[^\034-\254]+/g,
		'script_self': /\/bitrix\/js\/main\/core\/core(_ajax)*.js$/i,
		'script_self_window': /\/bitrix\/js\/main\/core\/core_window.js$/i,
		'script_self_admin': /\/bitrix\/js\/main\/core\/core_admin.js$/i,
		'script_onload': /window.onload/g
	};

// low-level method
BX.ajax = function(config)
{
	var status, data;

	if (!config || !config.url || !BX.type.isString(config.url))
	{
		return false;
	}

	for (var i in tempDefaultConfig)
		if (typeof (config[i]) == "undefined") config[i] = tempDefaultConfig[i];

	tempDefaultConfig = {};

	for (i in defaultConfig)
		if (typeof (config[i]) == "undefined") config[i] = defaultConfig[i];

	config.method = config.method.toUpperCase();

	if (!BX.localStorage)
		config.lsId = null;

	if (BX.browser.IsIE())
	{
		var result = r.url_utf.exec(config.url);
		if (result)
		{
			do
			{
				config.url = config.url.replace(result, BX.util.urlencode(result));
				result = r.url_utf.exec(config.url);
			} while (result);
		}
	}

	if(config.dataType == 'json')
		config.emulateOnload = false;

	if (!config.cache && config.method == 'GET')
		config.url = BX.ajax._uncache(config.url);

	if (config.method == 'POST')
	{
		if (config.preparePost)
		{
			config.data = BX.ajax.prepareData(config.data);
		}
		else if (getLastContentTypeHeader(config.headers) === 'application/json')
		{
			const isJson = (
				BX.Type.isPlainObject(config.data)
				|| BX.Type.isString(config.data)
				|| BX.Type.isNumber(config.data)
				|| BX.Type.isBoolean(config.data)
				|| BX.Type.isArray(config.data)
			);

			if (isJson)
			{
				config.data = JSON.stringify(config.data);
			}
		}
	}

	var bXHR = true;
	if (config.lsId && !config.lsForce)
	{
		var v = BX.localStorage.get('ajax-' + config.lsId);
		if (v !== null)
		{
			bXHR = false;

			var lsHandler = function(lsData) {
				if (lsData.key == 'ajax-' + config.lsId && lsData.value != 'BXAJAXWAIT')
				{
					var data = lsData.value,
						bRemove = !!lsData.oldValue && data == null;
					if (!bRemove)
						BX.ajax.__run(config, data);
					else if (config.onfailure)
						config.onfailure("timeout");

					BX.removeCustomEvent('onLocalStorageChange', lsHandler);
				}
			};

			if (v == 'BXAJAXWAIT')
			{
				BX.addCustomEvent('onLocalStorageChange', lsHandler);
			}
			else
			{
				setTimeout(function() {lsHandler({key: 'ajax-' + config.lsId, value: v})}, 10);
			}
		}
	}

	if (bXHR)
	{
		config.xhr = BX.ajax.xhr();
		if (!config.xhr) return;

		if (config.lsId)
		{
			BX.localStorage.set('ajax-' + config.lsId, 'BXAJAXWAIT', config.lsTimeout);
		}

		if (BX.Type.isFunction(config.onprogress))
		{
			BX.bind(config.xhr, 'progress', config.onprogress);
		}

		if (BX.Type.isFunction(config.onprogressupload) && config.xhr.upload)
		{
			BX.bind(config.xhr.upload, 'progress', config.onprogressupload);
		}

		config.xhr.open(config.method, config.url, config.async);

		if (!config.skipBxHeader && !BX.ajax.isCrossDomain(config.url))
		{
			config.xhr.setRequestHeader('Bx-ajax', 'true');
		}

		if (config.method == 'POST' && config.preparePost)
		{
			config.xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		}
		if (typeof(config.headers) == "object")
		{
			for (i = 0; i < config.headers.length; i++)
				config.xhr.setRequestHeader(config.headers[i].name, config.headers[i].value);
		}

		var bRequestCompleted = false;
		var onreadystatechange = config.xhr.onreadystatechange = function(additional)
		{
			if (bRequestCompleted)
				return;

			if (additional === 'timeout')
			{
				if (config.onfailure)
				{
					config.onfailure('timeout', '', config);
				}

				BX.onCustomEvent(config.xhr, 'onAjaxFailure', ['timeout', '', config]);

				config.xhr.onreadystatechange = BX.DoNothing;
				config.xhr.abort();

				if (config.async)
				{
					config.xhr = null;
				}
			}
			else
			{
				if (config.xhr.readyState == 4 || additional == 'run')
				{
					status = BX.ajax.xhrSuccess(config.xhr) ? "success" : "error";
					bRequestCompleted = true;
					config.xhr.onreadystatechange = BX.DoNothing;

					if (status == 'success')
					{
						var authHeader = (!!config.skipAuthCheck || BX.ajax.isCrossDomain(config.url))
							? false
							: config.xhr.getResponseHeader('X-Bitrix-Ajax-Status');

						if(!!authHeader && authHeader == 'Authorize')
						{
							if (config.onfailure)
							{
								config.onfailure('auth', config.xhr.status, config);
							}

							BX.onCustomEvent(config.xhr, 'onAjaxFailure', ['auth', config.xhr.status, config]);
						}
						else
						{
							var data = config.xhr.responseText;

							if (config.lsId)
							{
								BX.localStorage.set('ajax-' + config.lsId, data, config.lsTimeout);
							}

							BX.ajax.__run(config, data);
						}
					}
					else
					{
						if (config.onfailure)
						{
							config.onfailure('status', config.xhr.status, config);
						}

						BX.onCustomEvent(config.xhr, 'onAjaxFailure', ['status', config.xhr.status, config]);
					}

					if (config.async)
					{
						config.xhr = null;
					}
				}
			}
		};

		if (config.async && config.timeout > 0)
		{
			setTimeout(function() {
				if (config.xhr && !bRequestCompleted)
				{
					onreadystatechange("timeout");
				}
			}, config.timeout * 1000);
		}

		if (config.start)
		{
			config.xhr.send(config.data);

			if (!config.async)
			{
				onreadystatechange('run');
			}
		}

		return config.xhr;
	}
};

BX.ajax.xhr = function()
{
	if (window.XMLHttpRequest)
	{
		try {return new XMLHttpRequest();} catch(e){}
	}
	else if (window.ActiveXObject)
	{
		try { return new window.ActiveXObject("Msxml2.XMLHTTP.6.0"); }
			catch(e) {}
		try { return new window.ActiveXObject("Msxml2.XMLHTTP.3.0"); }
			catch(e) {}
		try { return new window.ActiveXObject("Msxml2.XMLHTTP"); }
			catch(e) {}
		try { return new window.ActiveXObject("Microsoft.XMLHTTP"); }
			catch(e) {}
		throw new Error("This browser does not support XMLHttpRequest.");
	}

	return null;
};

BX.ajax.isCrossDomain = function(url, location)
{
	location = location || window.location;

	//Relative URL gets a current protocol
	if (url.indexOf("//") === 0)
	{
		url = location.protocol + url;
	}

	//Fast check
	if (url.indexOf("http") !== 0)
	{
		return false;
	}

	var link = window.document.createElement("a");
	link.href = url;

	return  link.protocol !== location.protocol ||
			link.hostname !== location.hostname ||
			BX.ajax.getHostPort(link.protocol, link.host) !== BX.ajax.getHostPort(location.protocol, location.host);
};

BX.ajax.getHostPort = function(protocol, host)
{
	var match = /:(\d+)$/.exec(host);
	if (match)
	{
		return match[1];
	}
	else
	{
		if (protocol === "http:")
		{
			return "80";
		}
		else if (protocol === "https:")
		{
			return "443";
		}
	}

	return "";
};

BX.ajax.__prepareOnload = function(scripts, ajax_session)
{
	if (scripts.length > 0)
	{
		BX.ajax['onload_' + ajax_session] = null;

		for (var i=0,len=scripts.length;i<len;i++)
		{
			if (scripts[i].isInternal)
			{
				scripts[i].JS = scripts[i].JS.replace(r.script_onload, 'BX.ajax.onload_' + ajax_session);
			}
		}
	}

	BX.CaptureEventsGet();
	BX.CaptureEvents(window, 'load');
};

BX.ajax.__runOnload = function(ajax_session)
{
	if (null != BX.ajax['onload_' + ajax_session])
	{
		BX.ajax['onload_' + ajax_session].apply(window);
		BX.ajax['onload_' + ajax_session] = null;
	}

	var h = BX.CaptureEventsGet();

	if (h)
	{
		for (var i=0; i<h.length; i++)
			h[i].apply(window);
	}
};

BX.ajax.__run = function(config, data)
{
	if (!config.processData)
	{
		if (config.onsuccess)
		{
			config.onsuccess(data);
		}

		BX.onCustomEvent(config.xhr, 'onAjaxSuccess', [data, config]);
	}
	else
	{
		data = BX.ajax.processRequestData(data, config);
	}
};


BX.ajax._onParseJSONFailure = function(data)
{
	this.jsonFailure = true;
	this.jsonResponse = data;
	this.jsonProactive = /^\[WAF\]/.test(data);
};

BX.ajax.processRequestData = function(data, config)
{
	var result, scripts = [], styles = [];
	switch (config.dataType.toUpperCase())
	{
		case 'JSON':

			var context = config.xhr || {};
			BX.addCustomEvent(context, 'onParseJSONFailure', BX.proxy(BX.ajax._onParseJSONFailure, config));
			result = BX.parseJSON(data, context);
			BX.removeCustomEvent(context, 'onParseJSONFailure', BX.proxy(BX.ajax._onParseJSONFailure, config));

			if(!!result && BX.type.isArray(result['bxjs']))
			{
				for(var i = 0; i < result['bxjs'].length; i++)
				{
					if(BX.type.isNotEmptyString(result['bxjs'][i]))
					{
						scripts.push({
							"isInternal": false,
							"JS": result['bxjs'][i],
							"bRunFirst": config.scriptsRunFirst
						});
					}
					else
					{
						scripts.push(result['bxjs'][i])
					}
				}
			}

			if(!!result && BX.type.isArray(result['bxcss']))
			{
				styles = result['bxcss'];
			}

		break;
		case 'SCRIPT':
			scripts.push({"isInternal": true, "JS": data, "bRunFirst": config.scriptsRunFirst});
			result = data;
		break;

		default: // HTML
			var ob = BX.processHTML(data, config.scriptsRunFirst);
			result = ob.HTML; scripts = ob.SCRIPT; styles = ob.STYLE;
		break;
	}

	if (styles.length > 0)
	{
		BX.loadCSS(styles);
	}

	let ajax_session = null;
	if (config.emulateOnload)
	{
		ajax_session = parseInt(Math.random() * 1000000);
		BX.ajax.__prepareOnload(scripts, ajax_session);
	}

	const cb = BX.defer(function()
	{
		if (config.emulateOnload)
		{
			BX.ajax.__runOnload(ajax_session);
		}

		BX.onCustomEvent(config.xhr, 'onAjaxSuccessFinish', [config]);
	});

	try
	{
		if (!!config.jsonFailure)
		{
			throw {type: 'json_failure', data: config.jsonResponse, bProactive: config.jsonProactive};
		}

		config.scripts = scripts;

		BX.ajax.processScripts(config.scripts, true);

		if (config.onsuccess)
		{
			config.onsuccess(result);
		}

		BX.onCustomEvent(config.xhr, 'onAjaxSuccess', [result, config]);

		BX.ajax.processScripts(config.scripts, false, cb);
	}
	catch (e)
	{
		if (config.onfailure)
			config.onfailure("processing", e);
		BX.onCustomEvent(config.xhr, 'onAjaxFailure', ['processing', e, config]);
	}
};

BX.ajax.processScripts = function(scripts, bRunFirst, cb)
{
	var scriptsExt = [], scriptsInt = '';

	cb = cb || BX.DoNothing;

	for (var i = 0, length = scripts.length; i < length; i++)
	{
		if (typeof bRunFirst != 'undefined' && bRunFirst != !!scripts[i].bRunFirst)
			continue;

		if (scripts[i].isInternal)
			scriptsInt += ';' + scripts[i].JS;
		else
			scriptsExt.push(scripts[i].JS);
	}

	scriptsExt = BX.util.array_unique(scriptsExt);
	var inlineScripts = scriptsInt.length > 0 ? function() { BX.evalGlobal(scriptsInt); } : BX.DoNothing;

	if (scriptsExt.length > 0)
	{
		BX.load(scriptsExt, function() {
			inlineScripts();
			cb();
		});
	}
	else
	{
		inlineScripts();
		cb();
	}
};

// TODO: extend this function to use with any data objects or forms
BX.ajax.prepareData = function(arData, prefix)
{
	var data = '';
	if (BX.type.isString(arData))
		data = arData;
	else if (null != arData)
	{
		for(var i in arData)
		{
			if (arData.hasOwnProperty(i))
			{
				if (data.length > 0)
					data += '&';
				var name = BX.util.urlencode(i);
				if(prefix)
					name = prefix + '[' + name + ']';
				if(typeof arData[i] == 'object')
					data += BX.ajax.prepareData(arData[i], name);
				else
					data += name + '=' + BX.util.urlencode(arData[i]);
			}
		}
	}
	return data;
};

BX.ajax.xhrSuccess = function(xhr)
{
	return (xhr.status >= 200 && xhr.status < 300) || xhr.status === 304 || xhr.status === 1223 || xhr.status === 0;
};

BX.ajax.Setup = function(config, bTemp)
{
	bTemp = !!bTemp;

	for (var i in config)
	{
		if (bTemp)
			tempDefaultConfig[i] = config[i];
		else
			defaultConfig[i] = config[i];
	}
};

BX.ajax.replaceLocalStorageValue = function(lsId, data, ttl)
{
	if (!!BX.localStorage)
		BX.localStorage.set('ajax-' + lsId, data, ttl);
};


BX.ajax._uncache = function(url)
{
	return url + ((url.indexOf('?') !== -1 ? "&" : "?") + '_=' + (new Date()).getTime());
};

/* simple interface */
BX.ajax.get = function(url, data, callback)
{
	if (BX.type.isFunction(data))
	{
		callback = data;
		data = '';
	}

	data = BX.ajax.prepareData(data);

	if (data)
	{
		url += (url.indexOf('?') !== -1 ? "&" : "?") + data;
		data = '';
	}

	return BX.ajax({
		'method': 'GET',
		'dataType': 'html',
		'url': url,
		'data':  '',
		'onsuccess': callback
	});
};

BX.ajax.getCaptcha = function(callback)
{
	return BX.ajax.loadJSON('/bitrix/tools/ajax_captcha.php', callback);
};

BX.ajax.insertToNode = function(url, node)
{
	node = BX(node);
	if (!!node)
	{
		var eventArgs = { cancel: false };
		BX.onCustomEvent('onAjaxInsertToNode', [{ url: url, node: node, eventArgs: eventArgs }]);
		if(eventArgs.cancel === true)
		{
			return;
		}

		var show = null;
		if (!tempDefaultConfig.denyShowWait)
		{
			show = BX.showWait(node);
			delete tempDefaultConfig.denyShowWait;
		}

		return BX.ajax.get(url, function(data) {
			node.innerHTML = data;
			BX.closeWait(node, show);
		});
	}
};

BX.ajax.post = function(url, data, callback)
{
	data = BX.ajax.prepareData(data);

	return BX.ajax({
		'method': 'POST',
		'dataType': 'html',
		'url': url,
		'data':  data,
		'onsuccess': callback
	});
};

/**
 * BX.ajax with BX.Promise
 *
 * @param config
 * @returns {BX.Promise|false}
 */
BX.ajax.promise = function(config)
{
	var result = new BX.Promise();

	config.onsuccess = function(data)
	{
		result.fulfill(data);
	};
	config.onfailure = function(reason, httpStatus, config)
	{
		result.reject({
			reason: reason,
			data: httpStatus,
			ajaxConfig: config,
			xhr: config.xhr
		});
	};

	var xhr = BX.ajax(config);
	if (xhr)
	{
		if (typeof config.onrequeststart === 'function')
		{
			config.onrequeststart(xhr);
		}
	}
	else
	{
		result.reject({
			reason: "init",
			data: false
		});
	}

	return result;
};

/* load and execute external file script with onload emulation */
BX.ajax.loadScriptAjax = function(script_src, callback, bPreload)
{
	if (BX.type.isArray(script_src))
	{
		for (var i=0,len=script_src.length;i<len;i++)
		{
			BX.ajax.loadScriptAjax(script_src[i], callback, bPreload);
		}
	}
	else
	{
		var script_src_test = script_src.replace(/\.js\?.*/, '.js');

		if (r.script_self.test(script_src_test)) return;
		if (r.script_self_window.test(script_src_test) && BX.CWindow) return;
		if (r.script_self_admin.test(script_src_test) && BX.admin) return;

		if (typeof loadedScripts[script_src_test] == 'undefined')
		{
			if (!!bPreload)
			{
				loadedScripts[script_src_test] = '';
				return BX.loadScript(script_src);
			}
			else
			{
				return BX.ajax({
					url: script_src,
					method: 'GET',
					dataType: 'script',
					processData: true,
					emulateOnload: false,
					scriptsRunFirst: true,
					async: false,
					start: true,
					onsuccess: function(result) {
						loadedScripts[script_src_test] = result;
						if (callback)
							callback(result);
					}
				});
			}
		}
		else if (callback)
		{
			callback(loadedScripts[script_src_test]);
		}
	}
};

/* non-xhr loadings */
BX.ajax.loadJSON = function(url, data, callback, callback_failure)
{
	if (BX.type.isFunction(data))
	{
		callback_failure = callback;
		callback = data;
		data = '';
	}

	data = BX.ajax.prepareData(data);

	if (data)
	{
		url += (url.indexOf('?') !== -1 ? "&" : "?") + data;
		data = '';
	}

	return BX.ajax({
		'method': 'GET',
		'dataType': 'json',
		'url': url,
		'onsuccess': callback,
		'onfailure': callback_failure
	});
};

var getLastContentTypeHeader = function (headers) {
	if (!BX.Type.isArray(headers))
	{
		return null;
	}
	var lastHeader = headers
		.filter(function (header) {
			return header.name === 'Content-Type';
		})
		.pop();

	return lastHeader ? lastHeader.value : null;
};

/**
 * @see isValidAnalyticsData in ui.analytics
* */
const isValidAnalyticsData = function (analytics)
{
	if (!BX.Type.isPlainObject(analytics))
	{
		console.error('BX.ajax: {analytics} must be an object.');

		return false;
	}

	const requiredFields = ['event', 'tool', 'category'];
	for (const field of requiredFields)
	{
		if (!BX.Type.isStringFilled(analytics[field]))
		{
			console.error(`BX.ajax: The "${field}" property in the "analytics" object must be a non-empty string.`);

			return false;
		}
	}

	const additionalFields = ['p1', 'p2', 'p3', 'p4', 'p5'];
	for (const field of additionalFields)
	{
		const value = analytics[field];
		if (!BX.Type.isStringFilled(value))
		{
			continue;
		}

		if (value.split('_').length > 2)
		{
			console.error(`BX.ajax: The "${field}" property (${value}) in the "analytics" object must be a string containing a single underscore.`);

			return false;
		}
	}

	return true;
};

const processAnalyticsDataToGetParameters = function(config)
{
	const getParameters = {};
	if (BX.Type.isStringFilled(config.analyticsLabel) || BX.Type.isPlainObject(config.analyticsLabel))
	{
		getParameters.analyticsLabel = config.analyticsLabel;
	}

	if (BX.Type.isPlainObject(config.analytics))
	{
		if (config.analyticsLabel)
		{
			delete getParameters.analyticsLabel;
			console.error('BX.ajax: Only {analytics} or {analyticsLabel} should be used. If both are present, {analyticsLabel} will be ignored.');
		}

		if (isValidAnalyticsData(config.analytics))
		{
			getParameters.st = config.analytics;
		}
		else
		{
			console.error('BX.ajax: {analytics} is invalid and is skipped.');
		}
	}

	return getParameters;
};

const prepareAjaxGetParameters = function(config)
{
	let getParameters = config.getParameters || {};
	getParameters = { ...getParameters, ...processAnalyticsDataToGetParameters(config) };

	if (typeof config.mode !== 'undefined')
	{
		getParameters.mode = config.mode;
	}
	if (config.navigation)
	{
		if (config.navigation.page)
		{
			getParameters.nav = 'page-' + config.navigation.page;
		}
		if (config.navigation.size)
		{
			if (getParameters.nav)
			{
				getParameters.nav += '-';
			}
			else
			{
				getParameters.nav = '';
			}
			getParameters.nav += 'size-' + config.navigation.size;
		}
	}

	return getParameters;
};

var prepareAjaxConfig = function(config)
{
	config = BX.type.isPlainObject(config) ? config : {};

	config.headers = config.headers || [];
	config.headers.push({name: 'X-Bitrix-Csrf-Token', value: BX.bitrix_sessid()});
	if (BX.message.SITE_ID)
	{
		config.headers.push({name: 'X-Bitrix-Site-Id', value: BX.message.SITE_ID});
	}

	if (typeof config.json !== 'undefined')
	{
		if (!BX.type.isPlainObject(config.json))
		{
			throw new Error('Wrong `config.json`, plain object expected.')
		}

		config.headers.push({name: 'Content-Type', value: 'application/json'});
		config.data = config.json;
		config.preparePost = false;
	}
	else if (config.data instanceof FormData)
	{
		config.preparePost = false;
		if (typeof config.signedParameters !== 'undefined')
		{
			config.data.append('signedParameters', config.signedParameters);
		}
	}
	else if (BX.type.isPlainObject(config.data) || BX.Type.isNil(config.data))
	{
		config.data = BX.type.isPlainObject(config.data) ? config.data : {};
		if (typeof config.signedParameters !== 'undefined')
		{
			config.data.signedParameters = config.signedParameters;
		}
	}

	if (!config.method)
	{
		config.method = 'POST'
	}

	return config;
};

var buildAjaxPromiseToRestoreCsrf = function(config, withoutRestoringCsrf)
{
	withoutRestoringCsrf = withoutRestoringCsrf || false;
	var originalConfig = BX.clone(config);
	var request = null;

	var onrequeststart = config.onrequeststart;
	config.onrequeststart = function(xhr) {
		request = xhr;
		if (BX.type.isFunction(onrequeststart))
		{
			onrequeststart(xhr);
		}
	};
	var onrequeststartOrig = originalConfig.onrequeststart;
	originalConfig.onrequeststart = function(xhr) {
		request = xhr;
		if (BX.type.isFunction(onrequeststartOrig))
		{
			onrequeststartOrig(xhr);
		}
	};

	var promise = BX.ajax.promise(config);

	return promise.then(function(response) {
		if (!withoutRestoringCsrf && BX.type.isPlainObject(response) && BX.type.isArray(response.errors))
		{
			var csrfProblem = false;
			response.errors.forEach(function(error) {
				if (error.code === 'invalid_csrf' && error.customData.csrf)
				{
					BX.message({'bitrix_sessid': error.customData.csrf});

					originalConfig.headers = originalConfig.headers || [];
					originalConfig.headers = originalConfig.headers.filter(function(header) {
						return header && header.name !== 'X-Bitrix-Csrf-Token';
					});
					originalConfig.headers.push({name: 'X-Bitrix-Csrf-Token', value: BX.bitrix_sessid()});

					csrfProblem = true;
				}
			});

			if (csrfProblem)
			{
				return buildAjaxPromiseToRestoreCsrf(originalConfig, true);
			}
		}

		if (!BX.type.isPlainObject(response) || response.status !== 'success')
		{
			var errorPromise = new BX.Promise();
			errorPromise.reject(response);

			return errorPromise;
		}

		return response;
	}).catch(function(data) {
		var ajaxReject = new BX.Promise();

		var originalJsonResponse;
		if (BX.type.isPlainObject(data) && data.xhr && data.xhr.responseText)
		{
			try
			{
				originalJsonResponse = JSON.parse(data.xhr.responseText);
				data = originalJsonResponse;
			}
			catch (err)
			{}
		}

		if (BX.type.isPlainObject(data) && data.status && data.hasOwnProperty('data'))
		{
			ajaxReject.reject(data);
		}
		else
		{
			ajaxReject.reject({
				status: 'error',
				data: {
					ajaxRejectData: data
				},
				errors: [
					{
						code: 'NETWORK_ERROR',
						message: 'Network error'
					}
				]
			});
		}

		return ajaxReject;
	}).then(function(response){

		var assetsLoaded = new BX.Promise();

		var headers = request.getAllResponseHeaders().trim().split(/[\r\n]+/);
		var headerMap = {};
		headers.forEach(function (line) {
			var parts = line.split(': ');
			var header = parts.shift().toLowerCase();
			headerMap[header] = parts.join(': ');
		});

		if (!headerMap['x-process-assets'])
		{
			assetsLoaded.fulfill(response);

			return assetsLoaded;
		}

		var assets = BX.prop.getObject(BX.prop.getObject(response, "data", {}), "assets", {});

		var inlineScripts = [];
		if (BX.Type.isArrayFilled(assets.string))
		{
			assets.string
				.reduce(function(acc, item) {
					if (String(item).length > 0 && !acc.includes(item))
					{
						acc.push(item);
					}

					return acc;
				}, [])
				.forEach(function(item) {
					if (String(item).startsWith('<script type="extension/settings"'))
					{
						BX.html(document.head, item, { useAdjacentHTML: true });
					}
					else
					{
						inlineScripts.push(item);
					}
				});
		}

		var promise = new Promise(function(resolve, reject) {
			var css = BX.prop.getArray(assets, "css", []);
			BX.load(css, function(){
				BX.loadScript(
					BX.prop.getArray(assets, "js", []),
					resolve
				);
			});
		});

		promise.then(function(){
			var stringAsset = inlineScripts.join('\n');
			BX.html(document.head, stringAsset, { useAdjacentHTML: true }).then(function(){
				assetsLoaded.fulfill(response);
			});
		});

		return assetsLoaded;
	});
};

/**
 *
 * @param {string} action
 * @param {Object} config
 * @param {?string|?Object} [config.analyticsLabel]
 * @param {?Object} [config.analytics]
 * @param {string} [config.analytics.event]
 * @param {string} [config.analytics.tool]
 * @param {string} [config.analytics.category]
 * @param {?string} [config.analytics.c_section]
 * @param {?string} [config.analytics.c_sub_section]
 * @param {?string} [config.analytics.c_element]
 * @param {?string} [config.analytics.type]
 * @param {?string} [config.analytics.p1]
 * @param {?string} [config.analytics.p2]
 * @param {?string} [config.analytics.p3]
 * @param {?string} [config.analytics.p4]
 * @param {?string} [config.analytics.p5]
 * @param {?('success' | 'error' | 'attempt' | 'cancel')} [config.analytics.status]
 * @param {string} [config.method='POST']
 * @param {Object} [config.data]
 * @param {?Object} [config.getParameters]
 * @param {?Object} [config.headers]
 * @param {?Object} [config.timeout]
 * @param {Object} [config.navigation]
 * @param {number} [config.navigation.page]
 */
BX.ajax.runAction = function(action, config)
{
	config = prepareAjaxConfig(config);
	var getParameters = prepareAjaxGetParameters(config);
	getParameters.action = action;

	var url = '/bitrix/services/main/ajax.php?' + BX.ajax.prepareData(getParameters);
	return buildAjaxPromiseToRestoreCsrf({
		method: config.method,
		dataType: 'json',
		url: url,
		data: config.data,
		timeout: config.timeout,
		preparePost: config.preparePost,
		headers: config.headers,
		onrequeststart: config.onrequeststart,
		onprogress: config.onprogress,
		onprogressupload: config.onprogressupload
	});
};

/**
 *
 * @param {string} component
 * @param {string} action
 * @param {Object} config
 * @param {?string|?Object} [config.analyticsLabel]
 * @param {?Object} [config.analytics]
 * @param {string} [config.analytics.event]
 * @param {string} [config.analytics.tool]
 * @param {string} [config.analytics.category]
 * @param {?string} [config.analytics.c_section]
 * @param {?string} [config.analytics.c_sub_section]
 * @param {?string} [config.analytics.c_element]
 * @param {?string} [config.analytics.type]
 * @param {?string} [config.analytics.p1]
 * @param {?string} [config.analytics.p2]
 * @param {?string} [config.analytics.p3]
 * @param {?string} [config.analytics.p4]
 * @param {?string} [config.analytics.p5]
 * @param {?string} [config.signedParameters]
 * @param {string} [config.method='POST']
 * @param {string} [config.mode='ajax'] Ajax or class.
 * @param {Object} [config.data]
 * @param {?Object} [config.getParameters]
 * @param {?array} [config.headers]
 * @param {?number} [config.timeout]
 * @param {Object} [config.navigation]
 */
BX.ajax.runComponentAction = function (component, action, config)
{
	config = prepareAjaxConfig(config);
	config.mode = config.mode || 'ajax';

	var getParameters = prepareAjaxGetParameters(config);
	getParameters.c = component;
	getParameters.action = action;

	var url = '/bitrix/services/main/ajax.php?' + BX.ajax.prepareData(getParameters);

	return buildAjaxPromiseToRestoreCsrf({
		method: config.method,
		dataType: 'json',
		url: url,
		data: config.data,
		timeout: config.timeout,
		preparePost: config.preparePost,
		headers: config.headers,
		onrequeststart: (config.onrequeststart ? config.onrequeststart : null),
		onprogress: config.onprogress,
		onprogressupload: config.onprogressupload
	});
};

/*
arObs = [{
	url: url,
	type: html|script|json|css,
	callback: function
}]
*/
BX.ajax.load = function(arObs, callback)
{
	if (!BX.type.isArray(arObs))
		arObs = [arObs];

	var cnt = 0;

	if (!BX.type.isFunction(callback))
		callback = BX.DoNothing;

	var handler = function(data)
		{
			if (BX.type.isFunction(this.callback))
				this.callback(data);

			if (++cnt >= len)
				callback();
		};

	for (var i = 0, len = arObs.length; i<len; i++)
	{
		switch(arObs[i].type.toUpperCase())
		{
			case 'SCRIPT':
				BX.loadScript([arObs[i].url], BX.proxy(handler, arObs[i]));
			break;
			case 'CSS':
				BX.loadCSS([arObs[i].url]);

				if (++cnt >= len)
					callback();
			break;
			case 'JSON':
				BX.ajax.loadJSON(arObs[i].url, BX.proxy(handler, arObs[i]));
			break;

			default:
				BX.ajax.get(arObs[i].url, '', BX.proxy(handler, arObs[i]));
			break;
		}
	}
};

/* ajax form sending */
BX.ajax.submit = function(obForm, callback)
{
	if (!obForm.target)
	{
		if (null == obForm.BXFormTarget)
		{
			var frame_name = 'formTarget_' + Math.random();
			obForm.BXFormTarget = document.body.appendChild(BX.create('IFRAME', {
				props: {
					name: frame_name,
					id: frame_name,
					src: 'javascript:void(0)'
				},
				style: {
					display: 'none'
				}
			}));
		}

		obForm.target = obForm.BXFormTarget.name;
	}

	obForm.BXFormCallback = callback;
	BX.bind(obForm.BXFormTarget, 'load', BX.proxy(BX.ajax._submit_callback, obForm));

	BX.submit(obForm);

	return false;
};

BX.ajax.submitComponentForm = function(obForm, container, bWait)
{
	if (!obForm.target)
	{
		if (null == obForm.BXFormTarget)
		{
			var frame_name = 'formTarget_' + Math.random();
			obForm.BXFormTarget = document.body.appendChild(BX.create('IFRAME', {
				props: {
					name: frame_name,
					id: frame_name,
					src: 'javascript:void(0)'
				},
				style: {
					display: 'none'
				}
			}));
		}

		obForm.target = obForm.BXFormTarget.name;
	}

	if (!!bWait)
		var w = BX.showWait(container);

	obForm.BXFormCallback = function(d) {
		if (!!bWait)
			BX.closeWait(w);

		var callOnload = function(){
			if(!!window.bxcompajaxframeonload)
			{
				setTimeout(function(){window.bxcompajaxframeonload();window.bxcompajaxframeonload=null;}, 10);
			}
		};

		BX(container).innerHTML = d;
		BX.onCustomEvent('onAjaxSuccess', [null,null,callOnload]);
	};

	BX.bind(obForm.BXFormTarget, 'load', BX.proxy(BX.ajax._submit_callback, obForm));

	return true;
};

// func will be executed in form context
BX.ajax._submit_callback = function()
{
	//opera and IE8 triggers onload event even on empty iframe
	try
	{
		if(this.BXFormTarget.contentWindow.location.href.indexOf('http') != 0)
			return;
	} catch (e) {
		return;
	}

	if (this.BXFormCallback)
		this.BXFormCallback.apply(this, [this.BXFormTarget.contentWindow.document.body.innerHTML]);

	BX.unbindAll(this.BXFormTarget);
};

BX.ajax.prepareForm = function(obForm, data)
{
	data = (!!data ? data : {});
	var i, ii, el,
		_data = [],
		n = obForm.elements.length,
		files = 0, length = 0;
	if(!!obForm)
	{
		for (i = 0; i < n; i++)
		{
			el = obForm.elements[i];
			if (el.disabled)
				continue;

			if(!el.type)
				continue;

			switch(el.type.toLowerCase())
			{
				case 'text':
				case 'textarea':
				case 'password':
				case 'number':
				case 'hidden':
				case 'select-one':
					_data.push({name: el.name, value: el.value});
					length += (el.name.length + el.value.length);
					break;
				case 'file':
					if (!!el.files)
					{
						for (ii = 0; ii < el.files.length; ii++)
						{
							files++;
							_data.push({name: el.name, value: el.files[ii], file : true});
							length += el.files[ii].size;
						}
					}
					break;
				case 'radio':
				case 'checkbox':
					if(el.checked)
					{
						_data.push({name: el.name, value: el.value});
						length += (el.name.length + el.value.length);
					}
					break;
				case 'select-multiple':
					for (var j = 0; j < el.options.length; j++)
					{
						if (el.options[j].selected)
						{
							_data.push({name : el.name, value : el.options[j].value});
							length += (el.name.length + el.options[j].length);
						}
					}
					break;
				default:
					break;
			}
		}

		i = 0; length = 0;
		var current = data, name, rest, pp, tmpKey;

		while(i < _data.length)
		{
			var p = _data[i].name.indexOf('[');
			if (tmpKey)
			{
				current[_data[i].name] = {};
				current[_data[i].name][tmpKey.replace(/\[|\]/gi, '')] = _data[i].value;
				current = data;
				tmpKey = null;
				i++;
			}
			else if (p == -1)
			{
				current[_data[i].name] = _data[i].value;
				current = data;
				i++;
			}
			else
			{
				name = _data[i].name.substring(0, p);
				rest = _data[i].name.substring(p+1);
				pp = rest.indexOf(']');

				if(pp == -1)
				{
					if (!current[name])
						current[name] = [];
					current = data;
					i++;
				}
				else if(pp == 0)
				{
					if (!current[name])
						current[name] = [];
					//No index specified - so take the next integer
					current = current[name];
					_data[i].name = '' + current.length;
					if (rest.substring(pp+1).indexOf('[') === 0)
						tmpKey = rest.substring(0, pp) + rest.substring(pp+1);
				}
				else
				{
					if (!current[name])
						current[name] = {};
					//Now index name becomes and name and we go deeper into the array
					current = current[name];
					_data[i].name = rest.substring(0, pp) + rest.substring(pp+1);
				}
			}
		}
	}
	return {data : data, filesCount : files, roughSize : length};
};
BX.ajax.submitAjax = function(obForm, config)
{
	config = (config !== null && typeof config == "object" ? config : {});
	config.url = (config["url"] || obForm.getAttribute("action"));

	var additionalData = (config["data"] || {});
	config.data = BX.ajax.prepareForm(obForm).data;
	for (var ii in additionalData)
	{
		if (additionalData.hasOwnProperty(ii))
		{
			config.data[ii] = additionalData[ii];
		}
	}

	if (!window["FormData"])
	{
		BX.ajax(config);
	}
	else
	{
		var isFile = function(item)
		{
			var res = Object.prototype.toString.call(item);
			return (res == '[object File]' || res == '[object Blob]');
		},
		appendToForm = function(fd, key, val)
		{
			if (!!val && typeof val == "object" && !isFile(val))
			{
				for (var ii in val)
				{
					if (val.hasOwnProperty(ii))
					{
						appendToForm(fd, (key == '' ? ii : key + '[' + ii + ']'), val[ii]);
					}
				}
			}
			else
				fd.append(key, (!!val ? val : ''));
		},
		prepareData = function(arData)
		{
			var data = {};
			if (null != arData)
			{
				if(typeof arData == 'object')
				{
					for(var i in arData)
					{
						if (arData.hasOwnProperty(i))
						{
							var name = BX.util.urlencode(i);
							if(typeof arData[i] == 'object' && arData[i]["file"] !== true)
								data[name] = prepareData(arData[i]);
							else if (arData[i]["file"] === true)
								data[name] = arData[i]["value"];
							else
								data[name] = BX.util.urlencode(arData[i]);
						}
					}
				}
				else
					data = BX.util.urlencode(arData);
			}
			return data;
		},
		fd = new window.FormData();

		if (config.method !== 'POST')
		{
			config.data = BX.ajax.prepareData(config.data);
			if (config.data)
			{
				config.url += (config.url.indexOf('?') !== -1 ? "&" : "?") + config.data;
				config.data = '';
			}
		}
		else
		{
			if (config.preparePost === true)
				config.data = prepareData(config.data);
			appendToForm(fd, '', config.data);
			config.data = fd;
		}

		config.preparePost = false;
		config.start = false;

		var xhr = BX.ajax(config);
		if (!!config["onprogress"])
			xhr.upload.addEventListener(
				'progress',
				function(e){
					var percent = null;
					if(e.lengthComputable && (e.total || e["totalSize"])) {
						percent = e.loaded * 100 / (e.total || e["totalSize"]);
					}
					config["onprogress"](e, percent);
				}
			);
		xhr.send(fd);
	}
};

BX.ajax.UpdatePageData = function (arData)
{
	if (arData.TITLE)
		BX.ajax.UpdatePageTitle(arData.TITLE);
	if (arData.WINDOW_TITLE || arData.TITLE)
		BX.ajax.UpdateWindowTitle(arData.WINDOW_TITLE || arData.TITLE);
	if (arData.NAV_CHAIN)
		BX.ajax.UpdatePageNavChain(arData.NAV_CHAIN);
	if (arData.CSS && arData.CSS.length > 0)
		BX.loadCSS(arData.CSS);
	if (arData.SCRIPTS && arData.SCRIPTS.length > 0)
	{
		var f = function(result,config,cb){

			if(!!config && BX.type.isArray(config.scripts))
			{
				for(var i=0,l=arData.SCRIPTS.length;i<l;i++)
				{
					config.scripts.push({isInternal:false,JS:arData.SCRIPTS[i]});
				}
			}
			else
			{
				BX.loadScript(arData.SCRIPTS,cb);
			}

			BX.removeCustomEvent('onAjaxSuccess',f);
		};
		BX.addCustomEvent('onAjaxSuccess',f);
	}
	else
	{
		var f1 = function(result,config,cb){
			if(BX.type.isFunction(cb))
			{
				cb();
			}
			BX.removeCustomEvent('onAjaxSuccess',f1);
		};
		BX.addCustomEvent('onAjaxSuccess', f1);
	}
};

BX.ajax.UpdatePageTitle = function(title)
{
	var obTitle = BX('pagetitle');
	if (obTitle)
	{
		BX.remove(obTitle.firstChild);
		if (!obTitle.firstChild)
			obTitle.appendChild(document.createTextNode(title));
		else
			obTitle.insertBefore(document.createTextNode(title), obTitle.firstChild);
	}
};

BX.ajax.UpdateWindowTitle = function(title)
{
	document.title = title;
};

BX.ajax.UpdatePageNavChain = function(nav_chain)
{
	var obNavChain = BX('navigation');
	if (obNavChain)
	{
		obNavChain.innerHTML = nav_chain;
	}
};

/* user options handling */
BX.userOptions = {
	options: null,
	bSend: false,
	delay: 5000,
	path: '/bitrix/admin/user_options.php?'
};

BX.userOptions.setAjaxPath = function(url)
{
	// eslint-disable-next-line no-console
	console.warn('BX.userOptions.setAjaxPath is deprecated. There is no way to change ajax path.');
};
BX.userOptions.save = function(category, name, valueName, value, common)
{
	if (BX.userOptions.options === null)
	{
		BX.userOptions.options = {};
	}

	common = Boolean(common);
	BX.userOptions.options[`${category}.${name}.${valueName}`] = [category, name, valueName, value, common];

	const stringPackedValue = BX.userOptions.__get();
	if (stringPackedValue)
	{
		document.cookie = `${BX.message('COOKIE_PREFIX')}_LAST_SETTINGS=${encodeURIComponent(stringPackedValue)}&sessid=${BX.bitrix_sessid()}; expires=Thu, 31 Dec ${(new Date()).getFullYear() + 1} 23:59:59 GMT; path=/;`;
	}

	if (!BX.userOptions.bSend)
	{
		BX.userOptions.bSend = true;
		setTimeout(() => {
			BX.userOptions.send(null);
		}, BX.userOptions.delay);
	}
};

BX.userOptions.send = function(callback)
{
	const values = BX.userOptions.__get_values({ backwardCompatibility: true});

	BX.userOptions.options = null;
	BX.userOptions.bSend = false;

	if (values)
	{
		document.cookie = `${BX.message('COOKIE_PREFIX')}_LAST_SETTINGS=; path=/;`;

		BX.ajax.runAction(
			'main.userOption.saveOptions',
			{
				json: {
					newValues: values,
				},
			},
		).then((response) => {
			if (BX.type.isFunction(callback))
			{
				callback(response);
			}
		});
	}
};

BX.userOptions.del = function(category, name, common, callback)
{
	BX.ajax.runAction(
		'main.userOption.deleteOption',
		{
			json: {
				category,
				name,
				common,
			},
		},
	).then((response) => {
		if (BX.type.isFunction(callback))
		{
			callback(response);
		}
	});
};

BX.userOptions.__get_values = function({ backwardCompatibility })
{
	if (!BX.userOptions || !BX.Type.isPlainObject(BX.userOptions.options))
	{
		return null;
	}

	const CATEGORY = 0;
	const NAME = 1;
	const VALUE_NAME = 2;
	const VALUE = 3;
	const IS_DEFAULT = 4;

	const packedValues = { p: [] };
	let currentIndex = -1;
	let previousOptionIdentifier = '';

	Object.entries(BX.userOptions.options).forEach(([key, userOption]) => {
		const category = userOption[CATEGORY];
		const name = userOption[NAME];
		const currentOptionIdentifier = `${category}.${name}`;

		if (previousOptionIdentifier !== currentOptionIdentifier)
		{
			currentIndex++;
			packedValues.p.push({
				c: category,
				n: name,
				v: {},
			});
			if (userOption[IS_DEFAULT] === true)
			{
				packedValues.p[currentIndex].d = 'Y';
			}
			previousOptionIdentifier = currentOptionIdentifier;
		}

		if (userOption[VALUE_NAME] === null)
		{
			packedValues.p[currentIndex].v = userOption[VALUE];
		}
		else
		{
			let data = userOption[VALUE];
			if (backwardCompatibility && Array.isArray(userOption[VALUE]))
			{
				data = userOption[VALUE].join(',');
			}
			packedValues.p[currentIndex].v[userOption[VALUE_NAME]] = data;
		}
	});

	return packedValues.p.length > 0 ? packedValues.p : null;
};

/**
 * @deprecated Use instead BX.userOptions.__get_values.
 * */
BX.userOptions.__get = function()
{
	if (!BX.userOptions.options) return '';

	var sParam = '', n = -1, prevParam = '', aOpt, i;

	for (i in BX.userOptions.options)
	{
		if(BX.userOptions.options.hasOwnProperty(i))
		{
			aOpt = BX.userOptions.options[i];

			if (prevParam != aOpt[0]+'.'+aOpt[1])
			{
				n++;
				sParam += '&p['+n+'][c]='+BX.util.urlencode(aOpt[0]);
				sParam += '&p['+n+'][n]='+BX.util.urlencode(aOpt[1]);
				if (aOpt[4] == true)
					sParam += '&p['+n+'][d]=Y';
				prevParam = aOpt[0]+'.'+aOpt[1];
			}

			var valueName = aOpt[2];
			var value = aOpt[3];

			if (valueName === null)
			{
				sParam += '&p['+n+'][v]='+BX.util.urlencode(value);
			}
			else
			{
				sParam += '&p['+n+'][v]['+BX.util.urlencode(valueName)+']='+BX.util.urlencode(value);
			}
		}
	}

	return sParam.substr(1);
};

BX.ajax.history = {
	expected_hash: '',

	obParams: null,

	obFrame: null,
	obImage: null,

	obTimer: null,

	bInited: false,
	bHashCollision: false,
	bPushState: !!(history.pushState && BX.type.isFunction(history.pushState)),

	startState: null,

	init: function(obParams)
	{
		if (BX.ajax.history.bInited)
			return;

		this.obParams = obParams;
		var obCurrentState = this.obParams.getState();

		if (BX.ajax.history.bPushState)
		{
			BX.ajax.history.expected_hash = window.location.pathname;
			if (window.location.search)
				BX.ajax.history.expected_hash += window.location.search;

			BX.ajax.history.put(obCurrentState, BX.ajax.history.expected_hash, '', true);
			// due to some strange thing, chrome calls popstate event on page start. so we should delay it
			setTimeout(function(){BX.bind(window, 'popstate', BX.ajax.history.__hashListener);}, 500);
		}
		else
		{
			BX.ajax.history.expected_hash = window.location.hash;

			if (!BX.ajax.history.expected_hash || BX.ajax.history.expected_hash == '#')
				BX.ajax.history.expected_hash = '__bx_no_hash__';

			jsAjaxHistoryContainer.put(BX.ajax.history.expected_hash, obCurrentState);
			BX.ajax.history.obTimer = setTimeout(BX.ajax.history.__hashListener, 500);

			if (BX.browser.IsIE())
			{
				BX.ajax.history.obFrame = document.createElement('IFRAME');
				BX.hide_object(BX.ajax.history.obFrame);

				document.body.appendChild(BX.ajax.history.obFrame);

				BX.ajax.history.obFrame.contentWindow.document.open();
				BX.ajax.history.obFrame.contentWindow.document.write(BX.ajax.history.expected_hash);
				BX.ajax.history.obFrame.contentWindow.document.close();
			}
			else if (BX.browser.IsOpera())
			{
				BX.ajax.history.obImage = document.createElement('IMG');
				BX.hide_object(BX.ajax.history.obImage);

				document.body.appendChild(BX.ajax.history.obImage);

				BX.ajax.history.obImage.setAttribute('src', 'javascript:location.href = \'javascript:BX.ajax.history.__hashListener();\';');
			}
		}

		BX.ajax.history.bInited = true;
	},

	__hashListener: function(e)
	{
		e = e || window.event || {state:false};

		if (BX.ajax.history.bPushState)
		{
			BX.ajax.history.obParams.setState(e.state||BX.ajax.history.startState);
		}
		else
		{
			if (BX.ajax.history.obTimer)
			{
				window.clearTimeout(BX.ajax.history.obTimer);
				BX.ajax.history.obTimer = null;
			}

			var current_hash;
			if (null != BX.ajax.history.obFrame)
				current_hash = BX.ajax.history.obFrame.contentWindow.document.body.innerText;
			else
				current_hash = window.location.hash;

			if (!current_hash || current_hash == '#')
				current_hash = '__bx_no_hash__';

			if (current_hash.indexOf('#') == 0)
				current_hash = current_hash.substring(1);

			if (current_hash != BX.ajax.history.expected_hash)
			{
				var state = jsAjaxHistoryContainer.get(current_hash);
				if (state)
				{
					BX.ajax.history.obParams.setState(state);

					BX.ajax.history.expected_hash = current_hash;
					if (null != BX.ajax.history.obFrame)
					{
						var __hash = current_hash == '__bx_no_hash__' ? '' : current_hash;
						if (window.location.hash != __hash && window.location.hash != '#' + __hash)
							window.location.hash = __hash;
					}
				}
			}

			BX.ajax.history.obTimer = setTimeout(BX.ajax.history.__hashListener, 500);
		}
	},

	put: function(state, new_hash, new_hash1, bStartState)
	{
		if (this.bPushState)
		{
			if(!bStartState)
			{
				history.pushState(state, '', new_hash);
			}
			else
			{
				BX.ajax.history.startState = state;
			}
		}
		else
		{
			if (typeof new_hash1 != 'undefined')
				new_hash = new_hash1;
			else
				new_hash = 'view' + new_hash;

			jsAjaxHistoryContainer.put(new_hash, state);
			BX.ajax.history.expected_hash = new_hash;

			window.location.hash = BX.util.urlencode(new_hash);

			if (null != BX.ajax.history.obFrame)
			{
				BX.ajax.history.obFrame.contentWindow.document.open();
				BX.ajax.history.obFrame.contentWindow.document.write(new_hash);
				BX.ajax.history.obFrame.contentWindow.document.close();
			}
		}
	},

	checkRedirectStart: function(param_name, param_value)
	{
		var current_hash = window.location.hash;
		if (current_hash.substring(0, 1) == '#') current_hash = current_hash.substring(1);

		var test = current_hash.substring(0, 5);
		if (test == 'view/' || test == 'view%')
		{
			BX.ajax.history.bHashCollision = true;
			document.write('<' + 'div id="__ajax_hash_collision_' + param_value + '" style="display: none;">');
		}
	},

	checkRedirectFinish: function(param_name, param_value)
	{
		document.write('</div>');

		var current_hash = window.location.hash;
		if (current_hash.substring(0, 1) == '#') current_hash = current_hash.substring(1);

		BX.ready(function ()
		{
			var test = current_hash.substring(0, 5);
			if (test == 'view/' || test == 'view%')
			{
				var obColNode = BX('__ajax_hash_collision_' + param_value);
				var obNode = obColNode.firstChild;
				BX.cleanNode(obNode);
				obColNode.style.display = 'block';

				// IE, Opera and Chrome automatically modifies hash with urlencode, but FF doesn't ;-(
				if (test != 'view%')
					current_hash = BX.util.urlencode(current_hash);

				current_hash += (current_hash.indexOf('%3F') == -1 ? '%3F' : '%26') + param_name + '=' + param_value;

				var url = '/bitrix/tools/ajax_redirector.php?hash=' + current_hash;

				BX.ajax.insertToNode(url, obNode);
			}
		});
	}
};

BX.ajax.component = function(node)
{
	this.node = node;
};

BX.ajax.component.prototype.getState = function()
{
	var state = {
		'node': this.node,
		'title': window.document.title,
		'data': BX(this.node).innerHTML
	};

	var obNavChain = BX('navigation');
	if (null != obNavChain)
		state.nav_chain = obNavChain.innerHTML;

	BX.onCustomEvent(BX(state.node), "onComponentAjaxHistoryGetState", [state]);

	return state;
};

BX.ajax.component.prototype.setState = function(state)
{
	BX(state.node).innerHTML = state.data;
	BX.ajax.UpdatePageTitle(state.title);

	if (state.nav_chain)
	{
		BX.ajax.UpdatePageNavChain(state.nav_chain);
	}

	BX.onCustomEvent(BX(state.node), "onComponentAjaxHistorySetState", [state]);
};

var jsAjaxHistoryContainer = {
	arHistory: {},

	put: function(hash, state)
	{
		this.arHistory[hash] = state;
	},

	get: function(hash)
	{
		return this.arHistory[hash];
	}
};


BX.ajax.FormData = function()
{
	this.elements = [];
	this.files = [];
	this.features = {};
	this.isSupported();
	this.log('BX FormData init');
};

BX.ajax.FormData.isSupported = function()
{
	var f = new BX.ajax.FormData();
	var result = f.features.supported;
	f = null;
	return result;
};

BX.ajax.FormData.prototype.log = function(o)
{
	if (false) {
		try {
			if (BX.browser.IsIE()) o = JSON.stringify(o);
			console.log(o);
		} catch(e) {}
	}
};

BX.ajax.FormData.prototype.isSupported = function()
{
	var f = {};
	f.fileReader = (window.FileReader && window.FileReader.prototype.readAsBinaryString);
	f.readFormData = f.sendFormData = !!(window.FormData);
	f.supported = !!(f.readFormData && f.sendFormData);
	this.features = f;
	this.log('features:');
	this.log(f);

	return f.supported;
};

BX.ajax.FormData.prototype.append = function(name, value)
{
	if (typeof(value) === 'object') { // seems to be files element
		this.files.push({'name': name, 'value':value});
	} else {
		this.elements.push({'name': name, 'value':value});
	}
};

BX.ajax.FormData.prototype.send = function(url, callbackOk, callbackProgress, callbackError)
{
	this.log('FD send');
	this.xhr = BX.ajax({
			'method': 'POST',
			'dataType': 'html',
			'url': url,
			'onsuccess': callbackOk,
			'onfailure': callbackError,
			'start': false,
			'preparePost':false
		});

	if (callbackProgress)
	{
		this.xhr.upload.addEventListener(
			'progress',
			function(e) {
				if (e.lengthComputable)
					callbackProgress(e.loaded / (e.total || e.totalSize));
			},
			false
		);
	}

	if (this.features.readFormData && this.features.sendFormData)
	{
		var fd = new FormData();
		this.log('use browser formdata');
		for (var i in this.elements)
		{
			if(this.elements.hasOwnProperty(i))
				fd.append(this.elements[i].name,this.elements[i].value);
		}
		for (i in this.files)
		{
			if(this.files.hasOwnProperty(i))
				fd.append(this.files[i].name, this.files[i].value);
		}
		this.xhr.send(fd);
	}

	return this.xhr;
};

BX.addCustomEvent('onAjaxFailure', BX.debug);
})(window);


(function (exports,main_core) {
	'use strict';

	var LazyLoad = {
	  observer: null,
	  images: {},
	  imageStatus: {
	    hidden: -2,
	    error: -1,
	    "undefined": 0,
	    inited: 1,
	    loaded: 2
	  },
	  imageTypes: {
	    image: 1,
	    background: 2
	  },
	  initObserver: function initObserver() {
	    this.observer = new IntersectionObserver(this.onIntersection.bind(this), {
	      rootMargin: '20% 0% 20% 0%',
	      threshold: 0.10
	    });
	  },
	  onIntersection: function onIntersection(entries) {
	    entries.forEach(function (entry) {
	      if (entry.isIntersecting) {
	        this.showImage(entry.target);
	      }
	    }.bind(this));
	  },
	  registerImage: function registerImage(id, isImageVisibleCallback, options) {
	    if (this.observer === null) {
	      this.initObserver();
	    }

	    options = options || {};

	    if (!main_core.Type.isStringFilled(id)) {
	      return;
	    }

	    if (main_core.Type.isObject(this.images[id])) {
	      return;
	    }

	    var element = document.getElementById(id);

	    if (!main_core.Type.isDomNode(element)) {
	      return;
	    }

	    this.observer.observe(element);
	    this.images[id] = {
	      id: id,
	      node: null,
	      src: null,
	      dataSrcName: options.dataSrcName || 'src',
	      type: null,
	      func: main_core.Type.isFunction(isImageVisibleCallback) ? isImageVisibleCallback : null,
	      status: this.imageStatus.undefined
	    };
	  },
	  registerImages: function registerImages(ids, isImageVisibleCallback, options) {
	    if (main_core.Type.isArray(ids)) {
	      for (var i = 0, length = ids.length; i < length; i++) {
	        this.registerImage(ids[i], isImageVisibleCallback, options);
	      }
	    }
	  },
	  showImage: function showImage(imageNode) {
	    var imageNodeId = imageNode.id;

	    if (!main_core.Type.isStringFilled(imageNodeId)) {
	      return;
	    }

	    var image = this.images[imageNodeId];

	    if (!main_core.Type.isPlainObject(image)) {
	      return;
	    }

	    if (image.status == this.imageStatus.undefined) {
	      this.initImage(image);
	    }

	    if (image.status !== this.imageStatus.inited) {
	      return;
	    }

	    if (!image.node || !image.node.parentNode) {
	      image.node = null;
	      image.status = this.imageStatus.error;
	      return;
	    }

	    if (image.type == this.imageTypes.image) {
	      image.node.src = image.src;
	    } else {
	      image.node.style.backgroundImage = "url('" + image.src + "')";
	    }

	    image.node.dataset[image.dataSrcName] = "";
	    image.status = this.imageStatus.loaded;
	  },
	  showImages: function showImages(checkOwnVisibility) {
	    checkOwnVisibility = checkOwnVisibility !== false;

	    for (var id in this.images) {
	      if (!this.images.hasOwnProperty(id)) {
	        continue;
	      }

	      var image = this.images[id];

	      if (image.status == this.imageStatus.undefined) {
	        this.initImage(image);
	      }

	      if (image.status !== this.imageStatus.inited) {
	        continue;
	      }

	      if (!image.node || !image.node.parentNode) {
	        image.node = null;
	        image.status = this.imageStatus.error;
	        continue;
	      }

	      var isImageVisible = true;

	      if (checkOwnVisibility && main_core.Type.isFunction(image.func)) {
	        isImageVisible = image.func(image);
	      }

	      if (isImageVisible === true && this.isElementVisibleOnScreen(image.node)) {
	        if (image.type == this.imageTypes.image) {
	          image.node.src = image.src;
	        } else {
	          image.node.style.backgroundImage = "url('" + image.src + "')";
	        }

	        image.node.dataset[image.dataSrcName] = "";
	        image.status = this.imageStatus.loaded;
	      }
	    }
	  },
	  initImage: function initImage(image) {
	    image.status = this.imageStatus.error;
	    var node = document.getElementById(image.id);

	    if (!main_core.Type.isDomNode(node)) {
	      return;
	    }

	    var src = node.dataset[image.dataSrcName];

	    if (main_core.Type.isStringFilled(src)) {
	      image.node = node;
	      image.src = src;
	      image.status = this.imageStatus.inited;
	      image.type = image.node.tagName.toLowerCase() == "img" ? this.imageTypes.image : this.imageTypes.background;
	    }
	  },
	  isElementVisibleOnScreen: function isElementVisibleOnScreen(element) {
	    var coords = this.getElementCoords(element);
	    var windowTop = window.pageYOffset || document.documentElement.scrollTop;
	    var windowBottom = windowTop + document.documentElement.clientHeight;
	    coords.bottom = coords.top + element.offsetHeight;
	    return coords.top > windowTop && coords.top < windowBottom || // topVisible
	    coords.bottom < windowBottom && coords.bottom > windowTop // bottomVisible
	    ;
	  },
	  isElementVisibleOn2Screens: function isElementVisibleOn2Screens(element) {
	    var windowHeight = document.documentElement.clientHeight;
	    var windowTop = window.pageYOffset || document.documentElement.scrollTop;
	    var windowBottom = windowTop + windowHeight;
	    var coords = this.getElementCoords(element);
	    coords.bottom = coords.top + element.offsetHeight;
	    windowTop -= windowHeight;
	    windowBottom += windowHeight;
	    return coords.top > windowTop && coords.top < windowBottom || // topVisible
	    coords.bottom < windowBottom && coords.bottom > windowTop // bottomVisible
	    ;
	  },
	  getElementCoords: function getElementCoords(element) {
	    var box = element.getBoundingClientRect();
	    return {
	      originTop: box.top,
	      originLeft: box.left,
	      top: box.top + window.pageYOffset,
	      left: box.left + window.pageXOffset
	    };
	  },
	  onScroll: function onScroll() {},
	  clearImages: function clearImages() {
	    this.images = [];
	  }
	};

	exports.LazyLoad = LazyLoad;

}((this.BX = this.BX || {}),BX));



(function (exports) {
	'use strict';

	var ParamBag = /*#__PURE__*/function () {
	  function ParamBag() {
	    var params = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, ParamBag);

	    if (!!params && babelHelpers.typeof(params) === 'object') {
	      this.params = new Map(Object.entries(params));
	    } else {
	      this.params = new Map();
	    }
	  }

	  babelHelpers.createClass(ParamBag, [{
	    key: "getParam",
	    value: function getParam(key) {
	      var defaultValue = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;

	      if (this.params.has(key)) {
	        return this.params.get(key);
	      }

	      return defaultValue;
	    }
	  }, {
	    key: "setParam",
	    value: function setParam(key, value) {
	      this.params.set(key, value);
	    }
	  }, {
	    key: "clear",
	    value: function clear() {
	      this.params.clear();
	    }
	  }], [{
	    key: "create",
	    value: function create() {
	      var params = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      return new ParamBag(params);
	    }
	  }]);
	  return ParamBag;
	}();

	exports.ParamBag = ParamBag;

}((this.BX = this.BX || {})));



(function() {
	BX.FixFontSize = function(params)
	{
		var widthNode, computedStyles, width;

		this.node = null;
		this.prevWindowSize = 0;
		this.prevWrapperSize = 0;
		this.mainWrapper = null;
		this.textWrapper = null;
		this.objList = params.objList;
		this.minFontSizeList = [];
		this.minFontSize = 0;

		if (params.onresize)
		{
			this.prevWindowSize = window.innerWidth || document.documentElement.clientWidth;
			BX.bind(window, 'resize', BX.throttle(this.onResize, 350, this));
		}

		if (params.onAdaptiveResize)
		{
			widthNode = this.objList[0].scaleBy || this.objList[0].node;
			computedStyles = getComputedStyle(widthNode);
			this.prevWrapperSize = parseInt(computedStyles["width"]) - parseInt(computedStyles["paddingLeft"]) - parseInt(computedStyles["paddingRight"]);
			BX.bind(window, 'resize', BX.throttle(this.onAdaptiveResize, 350, this));
		}

		this.createTestNodes();
		this.decrease();
	};

	BX.FixFontSize.prototype =
		{
			createTestNodes: function()
			{
				this.textWrapper = BX.create('div',{
					style : {
						display : 'inline-block',
						whiteSpace : 'nowrap'
					}
				});

				this.mainWrapper = BX.create('div',{
					style : {
						height : 0,
						overflow : 'hidden'
					},
					children : [this.textWrapper]
				});

			},
			insertTestNodes: function()
			{
				document.body.appendChild(this.mainWrapper);
			},
			removeTestNodes: function()
			{
				document.body.removeChild(this.mainWrapper);
			},
			decrease: function()
			{
				var width,
					fontSize,
					widthNode,
					computedStyles;

				this.insertTestNodes();

				for(var i=this.objList.length-1; i>=0; i--)
				{
					widthNode = this.objList[i].scaleBy || this.objList[i].node;
					computedStyles = getComputedStyle(widthNode);
					width  = parseInt(computedStyles["width"]) - parseInt(computedStyles["paddingLeft"]) - parseInt(computedStyles["paddingRight"]);
					fontSize = parseInt(getComputedStyle(this.objList[i].node)["font-size"]);

					this.textWrapperSetStyle(this.objList[i].node);

					if(this.textWrapperInsertText(this.objList[i].node))
					{
						while(this.textWrapper.offsetWidth > width && fontSize > 0)
						{
							this.textWrapper.style.fontSize = --fontSize + 'px';
						}

						if(this.objList[i].smallestValue)
						{
							this.minFontSize = this.minFontSize ? Math.min(this.minFontSize, fontSize) : fontSize;

							this.minFontSizeList.push(this.objList[i].node)
						}
						else
						{
							this.objList[i].node.style.fontSize = fontSize + 'px';
						}
					}
				}

				if(this.minFontSizeList.length > 0)
					this.setMinFont();

				this.removeTestNodes();

			},
			increase: function()
			{
				this.insertTestNodes();
				var width,
					fontSize,
					widthNode,
					computedStyles;

				this.insertTestNodes();

				for(var i=this.objList.length-1; i>=0; i--)
				{
					widthNode = this.objList[i].scaleBy || this.objList[i].node;
					computedStyles = getComputedStyle(widthNode);
					width  = parseInt(computedStyles["width"]) - parseInt(computedStyles["paddingLeft"]) - parseInt(computedStyles["paddingRight"]);
					fontSize = parseInt(getComputedStyle(this.objList[i].node)["font-size"]);

					this.textWrapperSetStyle(this.objList[i].node);

					if(this.textWrapperInsertText(this.objList[i].node))
					{
						while(this.textWrapper.offsetWidth < width && fontSize < this.objList[i].maxFontSize)
						{
							this.textWrapper.style.fontSize = ++fontSize + 'px';
						}

						fontSize--;

						if(this.objList[i].smallestValue)
						{
							this.minFontSize = this.minFontSize ? Math.min(this.minFontSize, fontSize) : fontSize;

							this.minFontSizeList.push(this.objList[i].node)
						}
						else
						{
							this.objList[i].node.style.fontSize = fontSize + 'px';
						}
					}
				}

				if(this.minFontSizeList.length > 0)
					this.setMinFont();

				this.removeTestNodes();
			},
			setMinFont : function()
			{
				for(var i = this.minFontSizeList.length-1; i>=0; i--)
				{
					this.minFontSizeList[i].style.fontSize = this.minFontSize + 'px';
				}

				this.minFontSize = 0;
			},
			onResize : function()
			{
				var width = window.innerWidth || document.documentElement.clientWidth;

				if(this.prevWindowSize > width)
					this.decrease();

				else if (this.prevWindowSize < width)
					this.increase();

				this.prevWindowSize = width;
			},
			onAdaptiveResize : function()
			{
				var widthNode = this.objList[0].scaleBy || this.objList[0].node,
					computedStyles = getComputedStyle(widthNode),
					width = parseInt(computedStyles["width"]) - parseInt(computedStyles["paddingLeft"]) - parseInt(computedStyles["paddingRight"]);

				if (this.prevWrapperSize > width)
					this.decrease();
				else if (this.prevWrapperSize < width)
					this.increase();

				this.prevWrapperSize = width;
			},
			textWrapperInsertText : function(node)
			{
				if(node.textContent){
					this.textWrapper.textContent = node.textContent;
					return true;
				}
				else if(node.innerText)
				{
					this.textWrapper.innerText = node.innerText;
					return true;
				}
				else {
					return false;
				}
			},
			textWrapperSetStyle : function(node)
			{
				this.textWrapper.style.fontFamily = getComputedStyle(node)["font-family"];
				this.textWrapper.style.fontSize = getComputedStyle(node)["font-size"];
				this.textWrapper.style.fontStyle = getComputedStyle(node)["font-style"];
				this.textWrapper.style.fontWeight = getComputedStyle(node)["font-weight"];
				this.textWrapper.style.lineHeight = getComputedStyle(node)["line-height"];
			}
		};

	BX.FixFontSize.init = function(params)
	{
		return new BX.FixFontSize(params);
	};
})();

})();
//# sourceMappingURL=core.js.map