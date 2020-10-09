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

	function createCommonjsModule(fn, module) {
	  return module = {
	    exports: {}
	  }, fn(module, module.exports), module.exports;
	}

	var _global = createCommonjsModule(function (module) {
	// https://github.com/zloirock/core-js/issues/86#issuecomment-115759028
	var global = module.exports = typeof window != 'undefined' && window.Math == Math ? window : typeof self != 'undefined' && self.Math == Math ? self // eslint-disable-next-line no-new-func
	: Function('return this')();
	if (typeof __g == 'number') __g = global; // eslint-disable-line no-undef
	});

	var hasOwnProperty = {}.hasOwnProperty;

	var _has = function (it, key) {
	  return hasOwnProperty.call(it, key);
	};

	var _fails = function (exec) {
	  try {
	    return !!exec();
	  } catch (e) {
	    return true;
	  }
	};

	// Thank's IE8 for his funny defineProperty
	var _descriptors = !_fails(function () {
	  return Object.defineProperty({}, 'a', {
	    get: function get() {
	      return 7;
	    }
	  }).a != 7;
	});

	var _core = createCommonjsModule(function (module) {
	var core = module.exports = {
	  version: '2.6.3'
	};
	if (typeof __e == 'number') __e = core; // eslint-disable-line no-undef
	});
	var _core_1 = _core.version;

	var _isObject = function (it) {
	  return babelHelpers.typeof(it) === 'object' ? it !== null : typeof it === 'function';
	};

	var _anObject = function (it) {
	  if (!_isObject(it)) throw TypeError(it + ' is not an object!');
	  return it;
	};

	var document$1 = _global.document; // typeof document.createElement is 'object' in old IE


	var is = _isObject(document$1) && _isObject(document$1.createElement);

	var _domCreate = function (it) {
	  return is ? document$1.createElement(it) : {};
	};

	var _ie8DomDefine = !_descriptors && !_fails(function () {
	  return Object.defineProperty(_domCreate('div'), 'a', {
	    get: function get() {
	      return 7;
	    }
	  }).a != 7;
	});

	// 7.1.1 ToPrimitive(input [, PreferredType])
	 // instead of the ES6 spec version, we didn't implement @@toPrimitive case
	// and the second argument - flag - preferred type is a string


	var _toPrimitive = function (it, S) {
	  if (!_isObject(it)) return it;
	  var fn, val;
	  if (S && typeof (fn = it.toString) == 'function' && !_isObject(val = fn.call(it))) return val;
	  if (typeof (fn = it.valueOf) == 'function' && !_isObject(val = fn.call(it))) return val;
	  if (!S && typeof (fn = it.toString) == 'function' && !_isObject(val = fn.call(it))) return val;
	  throw TypeError("Can't convert object to primitive value");
	};

	var dP = Object.defineProperty;
	var f = _descriptors ? Object.defineProperty : function defineProperty(O, P, Attributes) {
	  _anObject(O);
	  P = _toPrimitive(P, true);
	  _anObject(Attributes);
	  if (_ie8DomDefine) try {
	    return dP(O, P, Attributes);
	  } catch (e) {
	    /* empty */
	  }
	  if ('get' in Attributes || 'set' in Attributes) throw TypeError('Accessors not supported!');
	  if ('value' in Attributes) O[P] = Attributes.value;
	  return O;
	};

	var _objectDp = {
		f: f
	};

	var _propertyDesc = function (bitmap, value) {
	  return {
	    enumerable: !(bitmap & 1),
	    configurable: !(bitmap & 2),
	    writable: !(bitmap & 4),
	    value: value
	  };
	};

	var _hide = _descriptors ? function (object, key, value) {
	  return _objectDp.f(object, key, _propertyDesc(1, value));
	} : function (object, key, value) {
	  object[key] = value;
	  return object;
	};

	var id = 0;
	var px = Math.random();

	var _uid = function (key) {
	  return 'Symbol('.concat(key === undefined ? '' : key, ')_', (++id + px).toString(36));
	};

	var _redefine = createCommonjsModule(function (module) {
	var SRC = _uid('src');

	var TO_STRING = 'toString';
	var $toString = Function[TO_STRING];
	var TPL = ('' + $toString).split(TO_STRING);

	_core.inspectSource = function (it) {
	  return $toString.call(it);
	};

	(module.exports = function (O, key, val, safe) {
	  var isFunction = typeof val == 'function';
	  if (isFunction) _has(val, 'name') || _hide(val, 'name', key);
	  if (O[key] === val) return;
	  if (isFunction) _has(val, SRC) || _hide(val, SRC, O[key] ? '' + O[key] : TPL.join(String(key)));

	  if (O === _global) {
	    O[key] = val;
	  } else if (!safe) {
	    delete O[key];
	    _hide(O, key, val);
	  } else if (O[key]) {
	    O[key] = val;
	  } else {
	    _hide(O, key, val);
	  } // add fake Function#toString for correct work wrapped methods / constructors with methods like LoDash isNative

	})(Function.prototype, TO_STRING, function toString() {
	  return typeof this == 'function' && this[SRC] || $toString.call(this);
	});
	});

	var _aFunction = function (it) {
	  if (typeof it != 'function') throw TypeError(it + ' is not a function!');
	  return it;
	};

	// optional / simple context binding


	var _ctx = function (fn, that, length) {
	  _aFunction(fn);
	  if (that === undefined) return fn;

	  switch (length) {
	    case 1:
	      return function (a) {
	        return fn.call(that, a);
	      };

	    case 2:
	      return function (a, b) {
	        return fn.call(that, a, b);
	      };

	    case 3:
	      return function (a, b, c) {
	        return fn.call(that, a, b, c);
	      };
	  }

	  return function ()
	  /* ...args */
	  {
	    return fn.apply(that, arguments);
	  };
	};

	var PROTOTYPE = 'prototype';

	var $export = function $export(type, name, source) {
	  var IS_FORCED = type & $export.F;
	  var IS_GLOBAL = type & $export.G;
	  var IS_STATIC = type & $export.S;
	  var IS_PROTO = type & $export.P;
	  var IS_BIND = type & $export.B;
	  var target = IS_GLOBAL ? _global : IS_STATIC ? _global[name] || (_global[name] = {}) : (_global[name] || {})[PROTOTYPE];
	  var exports = IS_GLOBAL ? _core : _core[name] || (_core[name] = {});
	  var expProto = exports[PROTOTYPE] || (exports[PROTOTYPE] = {});
	  var key, own, out, exp;
	  if (IS_GLOBAL) source = name;

	  for (key in source) {
	    // contains in native
	    own = !IS_FORCED && target && target[key] !== undefined; // export native or passed

	    out = (own ? target : source)[key]; // bind timers to global for call from export context

	    exp = IS_BIND && own ? _ctx(out, _global) : IS_PROTO && typeof out == 'function' ? _ctx(Function.call, out) : out; // extend global

	    if (target) _redefine(target, key, out, type & $export.U); // export

	    if (exports[key] != out) _hide(exports, key, exp);
	    if (IS_PROTO && expProto[key] != out) expProto[key] = out;
	  }
	};

	$export.F = 1; // forced

	$export.G = 2; // global

	$export.S = 4; // static

	$export.P = 8; // proto

	$export.B = 16; // bind

	$export.W = 32; // wrap

	$export.U = 64; // safe

	$export.R = 128; // real proto method for `library`

	var _export = $export;

	var _meta = createCommonjsModule(function (module) {
	var META = _uid('meta');





	var setDesc = _objectDp.f;

	var id = 0;

	var isExtensible = Object.isExtensible || function () {
	  return true;
	};

	var FREEZE = !_fails(function () {
	  return isExtensible(Object.preventExtensions({}));
	});

	var setMeta = function setMeta(it) {
	  setDesc(it, META, {
	    value: {
	      i: 'O' + ++id,
	      // object ID
	      w: {} // weak collections IDs

	    }
	  });
	};

	var fastKey = function fastKey(it, create) {
	  // return primitive with prefix
	  if (!_isObject(it)) return babelHelpers.typeof(it) == 'symbol' ? it : (typeof it == 'string' ? 'S' : 'P') + it;

	  if (!_has(it, META)) {
	    // can't set metadata to uncaught frozen object
	    if (!isExtensible(it)) return 'F'; // not necessary to add metadata

	    if (!create) return 'E'; // add missing metadata

	    setMeta(it); // return object ID
	  }

	  return it[META].i;
	};

	var getWeak = function getWeak(it, create) {
	  if (!_has(it, META)) {
	    // can't set metadata to uncaught frozen object
	    if (!isExtensible(it)) return true; // not necessary to add metadata

	    if (!create) return false; // add missing metadata

	    setMeta(it); // return hash weak collections IDs
	  }

	  return it[META].w;
	}; // add metadata on freeze-family methods calling


	var onFreeze = function onFreeze(it) {
	  if (FREEZE && meta.NEED && isExtensible(it) && !_has(it, META)) setMeta(it);
	  return it;
	};

	var meta = module.exports = {
	  KEY: META,
	  NEED: false,
	  fastKey: fastKey,
	  getWeak: getWeak,
	  onFreeze: onFreeze
	};
	});
	var _meta_1 = _meta.KEY;
	var _meta_2 = _meta.NEED;
	var _meta_3 = _meta.fastKey;
	var _meta_4 = _meta.getWeak;
	var _meta_5 = _meta.onFreeze;

	var _library = false;

	var _shared = createCommonjsModule(function (module) {
	var SHARED = '__core-js_shared__';
	var store = _global[SHARED] || (_global[SHARED] = {});
	(module.exports = function (key, value) {
	  return store[key] || (store[key] = value !== undefined ? value : {});
	})('versions', []).push({
	  version: _core.version,
	  mode: _library ? 'pure' : 'global',
	  copyright: '(c) 2019 Denis Pushkarev (zloirock.ru)'
	});
	});

	var _wks = createCommonjsModule(function (module) {
	var store = _shared('wks');



	var _Symbol = _global.Symbol;

	var USE_SYMBOL = typeof _Symbol == 'function';

	var $exports = module.exports = function (name) {
	  return store[name] || (store[name] = USE_SYMBOL && _Symbol[name] || (USE_SYMBOL ? _Symbol : _uid)('Symbol.' + name));
	};

	$exports.store = store;
	});

	var def = _objectDp.f;



	var TAG = _wks('toStringTag');

	var _setToStringTag = function (it, tag, stat) {
	  if (it && !_has(it = stat ? it : it.prototype, TAG)) def(it, TAG, {
	    configurable: true,
	    value: tag
	  });
	};

	var f$1 = _wks;

	var _wksExt = {
		f: f$1
	};

	var defineProperty = _objectDp.f;

	var _wksDefine = function (name) {
	  var $Symbol = _core.Symbol || (_core.Symbol = _library ? {} : _global.Symbol || {});
	  if (name.charAt(0) != '_' && !(name in $Symbol)) defineProperty($Symbol, name, {
	    value: _wksExt.f(name)
	  });
	};

	var toString = {}.toString;

	var _cof = function (it) {
	  return toString.call(it).slice(8, -1);
	};

	// fallback for non-array-like ES3 and non-enumerable old V8 strings
	 // eslint-disable-next-line no-prototype-builtins


	var _iobject = Object('z').propertyIsEnumerable(0) ? Object : function (it) {
	  return _cof(it) == 'String' ? it.split('') : Object(it);
	};

	// 7.2.1 RequireObjectCoercible(argument)
	var _defined = function (it) {
	  if (it == undefined) throw TypeError("Can't call method on  " + it);
	  return it;
	};

	// to indexed object, toObject with fallback for non-array-like ES3 strings




	var _toIobject = function (it) {
	  return _iobject(_defined(it));
	};

	// 7.1.4 ToInteger
	var ceil = Math.ceil;
	var floor = Math.floor;

	var _toInteger = function (it) {
	  return isNaN(it = +it) ? 0 : (it > 0 ? floor : ceil)(it);
	};

	// 7.1.15 ToLength


	var min = Math.min;

	var _toLength = function (it) {
	  return it > 0 ? min(_toInteger(it), 0x1fffffffffffff) : 0; // pow(2, 53) - 1 == 9007199254740991
	};

	var max = Math.max;
	var min$1 = Math.min;

	var _toAbsoluteIndex = function (index, length) {
	  index = _toInteger(index);
	  return index < 0 ? max(index + length, 0) : min$1(index, length);
	};

	// false -> Array#indexOf
	// true  -> Array#includes






	var _arrayIncludes = function (IS_INCLUDES) {
	  return function ($this, el, fromIndex) {
	    var O = _toIobject($this);
	    var length = _toLength(O.length);
	    var index = _toAbsoluteIndex(fromIndex, length);
	    var value; // Array#includes uses SameValueZero equality algorithm
	    // eslint-disable-next-line no-self-compare

	    if (IS_INCLUDES && el != el) while (length > index) {
	      value = O[index++]; // eslint-disable-next-line no-self-compare

	      if (value != value) return true; // Array#indexOf ignores holes, Array#includes - not
	    } else for (; length > index; index++) {
	      if (IS_INCLUDES || index in O) {
	        if (O[index] === el) return IS_INCLUDES || index || 0;
	      }
	    }
	    return !IS_INCLUDES && -1;
	  };
	};

	var shared = _shared('keys');



	var _sharedKey = function (key) {
	  return shared[key] || (shared[key] = _uid(key));
	};

	var arrayIndexOf = _arrayIncludes(false);

	var IE_PROTO = _sharedKey('IE_PROTO');

	var _objectKeysInternal = function (object, names) {
	  var O = _toIobject(object);
	  var i = 0;
	  var result = [];
	  var key;

	  for (key in O) {
	    if (key != IE_PROTO) _has(O, key) && result.push(key);
	  } // Don't enum bug & hidden keys


	  while (names.length > i) {
	    if (_has(O, key = names[i++])) {
	      ~arrayIndexOf(result, key) || result.push(key);
	    }
	  }

	  return result;
	};

	// IE 8- don't enum bug keys
	var _enumBugKeys = 'constructor,hasOwnProperty,isPrototypeOf,propertyIsEnumerable,toLocaleString,toString,valueOf'.split(',');

	// 19.1.2.14 / 15.2.3.14 Object.keys(O)




	var _objectKeys = Object.keys || function keys(O) {
	  return _objectKeysInternal(O, _enumBugKeys);
	};

	var f$2 = Object.getOwnPropertySymbols;

	var _objectGops = {
		f: f$2
	};

	var f$3 = {}.propertyIsEnumerable;

	var _objectPie = {
		f: f$3
	};

	// all enumerable object keys, includes symbols






	var _enumKeys = function (it) {
	  var result = _objectKeys(it);
	  var getSymbols = _objectGops.f;

	  if (getSymbols) {
	    var symbols = getSymbols(it);
	    var isEnum = _objectPie.f;
	    var i = 0;
	    var key;

	    while (symbols.length > i) {
	      if (isEnum.call(it, key = symbols[i++])) result.push(key);
	    }
	  }

	  return result;
	};

	// 7.2.2 IsArray(argument)


	var _isArray = Array.isArray || function isArray(arg) {
	  return _cof(arg) == 'Array';
	};

	var _objectDps = _descriptors ? Object.defineProperties : function defineProperties(O, Properties) {
	  _anObject(O);
	  var keys = _objectKeys(Properties);
	  var length = keys.length;
	  var i = 0;
	  var P;

	  while (length > i) {
	    _objectDp.f(O, P = keys[i++], Properties[P]);
	  }

	  return O;
	};

	var document$2 = _global.document;

	var _html = document$2 && document$2.documentElement;

	// 19.1.2.2 / 15.2.3.5 Object.create(O [, Properties])






	var IE_PROTO$1 = _sharedKey('IE_PROTO');

	var Empty = function Empty() {
	  /* empty */
	};

	var PROTOTYPE$1 = 'prototype'; // Create object with fake `null` prototype: use iframe Object with cleared prototype

	var _createDict = function createDict() {
	  // Thrash, waste and sodomy: IE GC bug
	  var iframe = _domCreate('iframe');

	  var i = _enumBugKeys.length;
	  var lt = '<';
	  var gt = '>';
	  var iframeDocument;
	  iframe.style.display = 'none';

	  _html.appendChild(iframe);

	  iframe.src = 'javascript:'; // eslint-disable-line no-script-url
	  // createDict = iframe.contentWindow.Object;
	  // html.removeChild(iframe);

	  iframeDocument = iframe.contentWindow.document;
	  iframeDocument.open();
	  iframeDocument.write(lt + 'script' + gt + 'document.F=Object' + lt + '/script' + gt);
	  iframeDocument.close();
	  _createDict = iframeDocument.F;

	  while (i--) {
	    delete _createDict[PROTOTYPE$1][_enumBugKeys[i]];
	  }

	  return _createDict();
	};

	var _objectCreate = Object.create || function create(O, Properties) {
	  var result;

	  if (O !== null) {
	    Empty[PROTOTYPE$1] = _anObject(O);
	    result = new Empty();
	    Empty[PROTOTYPE$1] = null; // add "__proto__" for Object.getPrototypeOf polyfill

	    result[IE_PROTO$1] = O;
	  } else result = _createDict();

	  return Properties === undefined ? result : _objectDps(result, Properties);
	};

	// 19.1.2.7 / 15.2.3.4 Object.getOwnPropertyNames(O)


	var hiddenKeys = _enumBugKeys.concat('length', 'prototype');

	var f$4 = Object.getOwnPropertyNames || function getOwnPropertyNames(O) {
	  return _objectKeysInternal(O, hiddenKeys);
	};

	var _objectGopn = {
		f: f$4
	};

	// fallback for IE11 buggy Object.getOwnPropertyNames with iframe and window


	var gOPN = _objectGopn.f;

	var toString$1 = {}.toString;
	var windowNames = (typeof window === "undefined" ? "undefined" : babelHelpers.typeof(window)) == 'object' && window && Object.getOwnPropertyNames ? Object.getOwnPropertyNames(window) : [];

	var getWindowNames = function getWindowNames(it) {
	  try {
	    return gOPN(it);
	  } catch (e) {
	    return windowNames.slice();
	  }
	};

	var f$5 = function getOwnPropertyNames(it) {
	  return windowNames && toString$1.call(it) == '[object Window]' ? getWindowNames(it) : gOPN(_toIobject(it));
	};

	var _objectGopnExt = {
		f: f$5
	};

	var gOPD = Object.getOwnPropertyDescriptor;
	var f$6 = _descriptors ? gOPD : function getOwnPropertyDescriptor(O, P) {
	  O = _toIobject(O);
	  P = _toPrimitive(P, true);
	  if (_ie8DomDefine) try {
	    return gOPD(O, P);
	  } catch (e) {
	    /* empty */
	  }
	  if (_has(O, P)) return _propertyDesc(!_objectPie.f.call(O, P), O[P]);
	};

	var _objectGopd = {
		f: f$6
	};

	var META = _meta.KEY;







































	var gOPD$1 = _objectGopd.f;
	var dP$1 = _objectDp.f;
	var gOPN$1 = _objectGopnExt.f;
	var $Symbol = _global.Symbol;
	var $JSON = _global.JSON;

	var _stringify = $JSON && $JSON.stringify;

	var PROTOTYPE$2 = 'prototype';
	var HIDDEN = _wks('_hidden');
	var TO_PRIMITIVE = _wks('toPrimitive');
	var isEnum = {}.propertyIsEnumerable;
	var SymbolRegistry = _shared('symbol-registry');
	var AllSymbols = _shared('symbols');
	var OPSymbols = _shared('op-symbols');
	var ObjectProto = Object[PROTOTYPE$2];
	var USE_NATIVE = typeof $Symbol == 'function';
	var QObject = _global.QObject; // Don't use setters in Qt Script, https://github.com/zloirock/core-js/issues/173

	var setter = !QObject || !QObject[PROTOTYPE$2] || !QObject[PROTOTYPE$2].findChild; // fallback for old Android, https://code.google.com/p/v8/issues/detail?id=687

	var setSymbolDesc = _descriptors && _fails(function () {
	  return _objectCreate(dP$1({}, 'a', {
	    get: function get() {
	      return dP$1(this, 'a', {
	        value: 7
	      }).a;
	    }
	  })).a != 7;
	}) ? function (it, key, D) {
	  var protoDesc = gOPD$1(ObjectProto, key);
	  if (protoDesc) delete ObjectProto[key];
	  dP$1(it, key, D);
	  if (protoDesc && it !== ObjectProto) dP$1(ObjectProto, key, protoDesc);
	} : dP$1;

	var wrap = function wrap(tag) {
	  var sym = AllSymbols[tag] = _objectCreate($Symbol[PROTOTYPE$2]);

	  sym._k = tag;
	  return sym;
	};

	var isSymbol = USE_NATIVE && babelHelpers.typeof($Symbol.iterator) == 'symbol' ? function (it) {
	  return babelHelpers.typeof(it) == 'symbol';
	} : function (it) {
	  return it instanceof $Symbol;
	};

	var $defineProperty = function defineProperty(it, key, D) {
	  if (it === ObjectProto) $defineProperty(OPSymbols, key, D);
	  _anObject(it);
	  key = _toPrimitive(key, true);
	  _anObject(D);

	  if (_has(AllSymbols, key)) {
	    if (!D.enumerable) {
	      if (!_has(it, HIDDEN)) dP$1(it, HIDDEN, _propertyDesc(1, {}));
	      it[HIDDEN][key] = true;
	    } else {
	      if (_has(it, HIDDEN) && it[HIDDEN][key]) it[HIDDEN][key] = false;
	      D = _objectCreate(D, {
	        enumerable: _propertyDesc(0, false)
	      });
	    }

	    return setSymbolDesc(it, key, D);
	  }

	  return dP$1(it, key, D);
	};

	var $defineProperties = function defineProperties(it, P) {
	  _anObject(it);
	  var keys = _enumKeys(P = _toIobject(P));
	  var i = 0;
	  var l = keys.length;
	  var key;

	  while (l > i) {
	    $defineProperty(it, key = keys[i++], P[key]);
	  }

	  return it;
	};

	var $create = function create(it, P) {
	  return P === undefined ? _objectCreate(it) : $defineProperties(_objectCreate(it), P);
	};

	var $propertyIsEnumerable = function propertyIsEnumerable(key) {
	  var E = isEnum.call(this, key = _toPrimitive(key, true));
	  if (this === ObjectProto && _has(AllSymbols, key) && !_has(OPSymbols, key)) return false;
	  return E || !_has(this, key) || !_has(AllSymbols, key) || _has(this, HIDDEN) && this[HIDDEN][key] ? E : true;
	};

	var $getOwnPropertyDescriptor = function getOwnPropertyDescriptor(it, key) {
	  it = _toIobject(it);
	  key = _toPrimitive(key, true);
	  if (it === ObjectProto && _has(AllSymbols, key) && !_has(OPSymbols, key)) return;
	  var D = gOPD$1(it, key);
	  if (D && _has(AllSymbols, key) && !(_has(it, HIDDEN) && it[HIDDEN][key])) D.enumerable = true;
	  return D;
	};

	var $getOwnPropertyNames = function getOwnPropertyNames(it) {
	  var names = gOPN$1(_toIobject(it));
	  var result = [];
	  var i = 0;
	  var key;

	  while (names.length > i) {
	    if (!_has(AllSymbols, key = names[i++]) && key != HIDDEN && key != META) result.push(key);
	  }

	  return result;
	};

	var $getOwnPropertySymbols = function getOwnPropertySymbols(it) {
	  var IS_OP = it === ObjectProto;
	  var names = gOPN$1(IS_OP ? OPSymbols : _toIobject(it));
	  var result = [];
	  var i = 0;
	  var key;

	  while (names.length > i) {
	    if (_has(AllSymbols, key = names[i++]) && (IS_OP ? _has(ObjectProto, key) : true)) result.push(AllSymbols[key]);
	  }

	  return result;
	}; // 19.4.1.1 Symbol([description])


	if (!USE_NATIVE) {
	  $Symbol = function _Symbol() {
	    if (this instanceof $Symbol) throw TypeError('Symbol is not a constructor!');
	    var tag = _uid(arguments.length > 0 ? arguments[0] : undefined);

	    var $set = function $set(value) {
	      if (this === ObjectProto) $set.call(OPSymbols, value);
	      if (_has(this, HIDDEN) && _has(this[HIDDEN], tag)) this[HIDDEN][tag] = false;
	      setSymbolDesc(this, tag, _propertyDesc(1, value));
	    };

	    if (_descriptors && setter) setSymbolDesc(ObjectProto, tag, {
	      configurable: true,
	      set: $set
	    });
	    return wrap(tag);
	  };

	  _redefine($Symbol[PROTOTYPE$2], 'toString', function toString() {
	    return this._k;
	  });
	  _objectGopd.f = $getOwnPropertyDescriptor;
	  _objectDp.f = $defineProperty;
	  _objectGopn.f = _objectGopnExt.f = $getOwnPropertyNames;
	  _objectPie.f = $propertyIsEnumerable;
	  _objectGops.f = $getOwnPropertySymbols;

	  if (_descriptors && !_library) {
	    _redefine(ObjectProto, 'propertyIsEnumerable', $propertyIsEnumerable, true);
	  }

	  _wksExt.f = function (name) {
	    return wrap(_wks(name));
	  };
	}

	_export(_export.G + _export.W + _export.F * !USE_NATIVE, {
	  Symbol: $Symbol
	});

	for (var es6Symbols = // 19.4.2.2, 19.4.2.3, 19.4.2.4, 19.4.2.6, 19.4.2.8, 19.4.2.9, 19.4.2.10, 19.4.2.11, 19.4.2.12, 19.4.2.13, 19.4.2.14
	'hasInstance,isConcatSpreadable,iterator,match,replace,search,species,split,toPrimitive,toStringTag,unscopables'.split(','), j = 0; es6Symbols.length > j;) {
	  _wks(es6Symbols[j++]);
	}

	for (var wellKnownSymbols = _objectKeys(_wks.store), k = 0; wellKnownSymbols.length > k;) {
	  _wksDefine(wellKnownSymbols[k++]);
	}

	_export(_export.S + _export.F * !USE_NATIVE, 'Symbol', {
	  // 19.4.2.1 Symbol.for(key)
	  'for': function _for(key) {
	    return _has(SymbolRegistry, key += '') ? SymbolRegistry[key] : SymbolRegistry[key] = $Symbol(key);
	  },
	  // 19.4.2.5 Symbol.keyFor(sym)
	  keyFor: function keyFor(sym) {
	    if (!isSymbol(sym)) throw TypeError(sym + ' is not a symbol!');

	    for (var key in SymbolRegistry) {
	      if (SymbolRegistry[key] === sym) return key;
	    }
	  },
	  useSetter: function useSetter() {
	    setter = true;
	  },
	  useSimple: function useSimple() {
	    setter = false;
	  }
	});
	_export(_export.S + _export.F * !USE_NATIVE, 'Object', {
	  // 19.1.2.2 Object.create(O [, Properties])
	  create: $create,
	  // 19.1.2.4 Object.defineProperty(O, P, Attributes)
	  defineProperty: $defineProperty,
	  // 19.1.2.3 Object.defineProperties(O, Properties)
	  defineProperties: $defineProperties,
	  // 19.1.2.6 Object.getOwnPropertyDescriptor(O, P)
	  getOwnPropertyDescriptor: $getOwnPropertyDescriptor,
	  // 19.1.2.7 Object.getOwnPropertyNames(O)
	  getOwnPropertyNames: $getOwnPropertyNames,
	  // 19.1.2.8 Object.getOwnPropertySymbols(O)
	  getOwnPropertySymbols: $getOwnPropertySymbols
	}); // 24.3.2 JSON.stringify(value [, replacer [, space]])

	$JSON && _export(_export.S + _export.F * (!USE_NATIVE || _fails(function () {
	  var S = $Symbol(); // MS Edge converts symbol values to JSON as {}
	  // WebKit converts symbol values to JSON as null
	  // V8 throws on boxed symbols

	  return _stringify([S]) != '[null]' || _stringify({
	    a: S
	  }) != '{}' || _stringify(Object(S)) != '{}';
	})), 'JSON', {
	  stringify: function stringify(it) {
	    var args = [it];
	    var i = 1;
	    var replacer, $replacer;

	    while (arguments.length > i) {
	      args.push(arguments[i++]);
	    }

	    $replacer = replacer = args[1];
	    if (!_isObject(replacer) && it === undefined || isSymbol(it)) return; // IE8 returns string on undefined

	    if (!_isArray(replacer)) replacer = function replacer(key, value) {
	      if (typeof $replacer == 'function') value = $replacer.call(this, key, value);
	      if (!isSymbol(value)) return value;
	    };
	    args[1] = replacer;
	    return _stringify.apply($JSON, args);
	  }
	}); // 19.4.3.4 Symbol.prototype[@@toPrimitive](hint)

	$Symbol[PROTOTYPE$2][TO_PRIMITIVE] || _hide($Symbol[PROTOTYPE$2], TO_PRIMITIVE, $Symbol[PROTOTYPE$2].valueOf); // 19.4.3.5 Symbol.prototype[@@toStringTag]

	_setToStringTag($Symbol, 'Symbol'); // 20.2.1.9 Math[@@toStringTag]

	_setToStringTag(Math, 'Math', true); // 24.3.3 JSON[@@toStringTag]

	_setToStringTag(_global.JSON, 'JSON', true);

	// 19.1.2.2 / 15.2.3.5 Object.create(O [, Properties])


	_export(_export.S, 'Object', {
	  create: _objectCreate
	});

	// 19.1.2.4 / 15.2.3.6 Object.defineProperty(O, P, Attributes)


	_export(_export.S + _export.F * !_descriptors, 'Object', {
	  defineProperty: _objectDp.f
	});

	// 19.1.2.3 / 15.2.3.7 Object.defineProperties(O, Properties)


	_export(_export.S + _export.F * !_descriptors, 'Object', {
	  defineProperties: _objectDps
	});

	// most Object methods by ES6 should accept primitives






	var _objectSap = function (KEY, exec) {
	  var fn = (_core.Object || {})[KEY] || Object[KEY];
	  var exp = {};
	  exp[KEY] = exec(fn);
	  _export(_export.S + _export.F * _fails(function () {
	    fn(1);
	  }), 'Object', exp);
	};

	// 19.1.2.6 Object.getOwnPropertyDescriptor(O, P)


	var $getOwnPropertyDescriptor$1 = _objectGopd.f;

	_objectSap('getOwnPropertyDescriptor', function () {
	  return function getOwnPropertyDescriptor(it, key) {
	    return $getOwnPropertyDescriptor$1(_toIobject(it), key);
	  };
	});

	// 7.1.13 ToObject(argument)


	var _toObject = function (it) {
	  return Object(_defined(it));
	};

	// 19.1.2.9 / 15.2.3.2 Object.getPrototypeOf(O)




	var IE_PROTO$2 = _sharedKey('IE_PROTO');

	var ObjectProto$1 = Object.prototype;

	var _objectGpo = Object.getPrototypeOf || function (O) {
	  O = _toObject(O);
	  if (_has(O, IE_PROTO$2)) return O[IE_PROTO$2];

	  if (typeof O.constructor == 'function' && O instanceof O.constructor) {
	    return O.constructor.prototype;
	  }

	  return O instanceof Object ? ObjectProto$1 : null;
	};

	// 19.1.2.9 Object.getPrototypeOf(O)




	_objectSap('getPrototypeOf', function () {
	  return function getPrototypeOf(it) {
	    return _objectGpo(_toObject(it));
	  };
	});

	// 19.1.2.14 Object.keys(O)




	_objectSap('keys', function () {
	  return function keys(it) {
	    return _objectKeys(_toObject(it));
	  };
	});

	// 19.1.2.7 Object.getOwnPropertyNames(O)
	_objectSap('getOwnPropertyNames', function () {
	  return _objectGopnExt.f;
	});

	// 19.1.2.5 Object.freeze(O)


	var meta = _meta.onFreeze;

	_objectSap('freeze', function ($freeze) {
	  return function freeze(it) {
	    return $freeze && _isObject(it) ? $freeze(meta(it)) : it;
	  };
	});

	// 19.1.2.17 Object.seal(O)


	var meta$1 = _meta.onFreeze;

	_objectSap('seal', function ($seal) {
	  return function seal(it) {
	    return $seal && _isObject(it) ? $seal(meta$1(it)) : it;
	  };
	});

	// 19.1.2.15 Object.preventExtensions(O)


	var meta$2 = _meta.onFreeze;

	_objectSap('preventExtensions', function ($preventExtensions) {
	  return function preventExtensions(it) {
	    return $preventExtensions && _isObject(it) ? $preventExtensions(meta$2(it)) : it;
	  };
	});

	// 19.1.2.12 Object.isFrozen(O)


	_objectSap('isFrozen', function ($isFrozen) {
	  return function isFrozen(it) {
	    return _isObject(it) ? $isFrozen ? $isFrozen(it) : false : true;
	  };
	});

	// 19.1.2.13 Object.isSealed(O)


	_objectSap('isSealed', function ($isSealed) {
	  return function isSealed(it) {
	    return _isObject(it) ? $isSealed ? $isSealed(it) : false : true;
	  };
	});

	// 19.1.2.11 Object.isExtensible(O)


	_objectSap('isExtensible', function ($isExtensible) {
	  return function isExtensible(it) {
	    return _isObject(it) ? $isExtensible ? $isExtensible(it) : true : false;
	  };
	});

	var $assign = Object.assign; // should work with symbols and should have deterministic property order (V8 bug)

	var _objectAssign = !$assign || _fails(function () {
	  var A = {};
	  var B = {}; // eslint-disable-next-line no-undef

	  var S = Symbol();
	  var K = 'abcdefghijklmnopqrst';
	  A[S] = 7;
	  K.split('').forEach(function (k) {
	    B[k] = k;
	  });
	  return $assign({}, A)[S] != 7 || Object.keys($assign({}, B)).join('') != K;
	}) ? function assign(target, source) {
	  // eslint-disable-line no-unused-vars
	  var T = _toObject(target);
	  var aLen = arguments.length;
	  var index = 1;
	  var getSymbols = _objectGops.f;
	  var isEnum = _objectPie.f;

	  while (aLen > index) {
	    var S = _iobject(arguments[index++]);
	    var keys = getSymbols ? _objectKeys(S).concat(getSymbols(S)) : _objectKeys(S);
	    var length = keys.length;
	    var j = 0;
	    var key;

	    while (length > j) {
	      if (isEnum.call(S, key = keys[j++])) T[key] = S[key];
	    }
	  }

	  return T;
	} : $assign;

	// 19.1.3.1 Object.assign(target, source)


	_export(_export.S + _export.F, 'Object', {
	  assign: _objectAssign
	});

	// 7.2.9 SameValue(x, y)
	var _sameValue = Object.is || function is(x, y) {
	  // eslint-disable-next-line no-self-compare
	  return x === y ? x !== 0 || 1 / x === 1 / y : x != x && y != y;
	};

	// 19.1.3.10 Object.is(value1, value2)


	_export(_export.S, 'Object', {
	  is: _sameValue
	});

	// Works with __proto__ only. Old v8 can't work with null proto objects.

	/* eslint-disable no-proto */




	var check = function check(O, proto) {
	  _anObject(O);
	  if (!_isObject(proto) && proto !== null) throw TypeError(proto + ": can't set as prototype!");
	};

	var _setProto = {
	  set: Object.setPrototypeOf || ('__proto__' in {} ? // eslint-disable-line
	  function (test, buggy, set) {
	    try {
	      set = _ctx(Function.call, _objectGopd.f(Object.prototype, '__proto__').set, 2);
	      set(test, []);
	      buggy = !(test instanceof Array);
	    } catch (e) {
	      buggy = true;
	    }

	    return function setPrototypeOf(O, proto) {
	      check(O, proto);
	      if (buggy) O.__proto__ = proto;else set(O, proto);
	      return O;
	    };
	  }({}, false) : undefined),
	  check: check
	};

	// 19.1.3.19 Object.setPrototypeOf(O, proto)


	_export(_export.S, 'Object', {
	  setPrototypeOf: _setProto.set
	});

	// getting tag from 19.1.3.6 Object.prototype.toString()


	var TAG$1 = _wks('toStringTag'); // ES3 wrong here


	var ARG = _cof(function () {
	  return arguments;
	}()) == 'Arguments'; // fallback for IE11 Script Access Denied error

	var tryGet = function tryGet(it, key) {
	  try {
	    return it[key];
	  } catch (e) {
	    /* empty */
	  }
	};

	var _classof = function (it) {
	  var O, T, B;
	  return it === undefined ? 'Undefined' : it === null ? 'Null' // @@toStringTag case
	  : typeof (T = tryGet(O = Object(it), TAG$1)) == 'string' ? T // builtinTag case
	  : ARG ? _cof(O) // ES3 arguments fallback
	  : (B = _cof(O)) == 'Object' && typeof O.callee == 'function' ? 'Arguments' : B;
	};

	var test = {};
	test[_wks('toStringTag')] = 'z';

	if (test + '' != '[object z]') {
	  _redefine(Object.prototype, 'toString', function toString() {
	    return '[object ' + _classof(this) + ']';
	  }, true);
	}

	// fast apply, http://jsperf.lnkit.com/fast-apply/5
	var _invoke = function (fn, args, that) {
	  var un = that === undefined;

	  switch (args.length) {
	    case 0:
	      return un ? fn() : fn.call(that);

	    case 1:
	      return un ? fn(args[0]) : fn.call(that, args[0]);

	    case 2:
	      return un ? fn(args[0], args[1]) : fn.call(that, args[0], args[1]);

	    case 3:
	      return un ? fn(args[0], args[1], args[2]) : fn.call(that, args[0], args[1], args[2]);

	    case 4:
	      return un ? fn(args[0], args[1], args[2], args[3]) : fn.call(that, args[0], args[1], args[2], args[3]);
	  }

	  return fn.apply(that, args);
	};

	var arraySlice = [].slice;
	var factories = {};

	var construct = function construct(F, len, args) {
	  if (!(len in factories)) {
	    for (var n = [], i = 0; i < len; i++) {
	      n[i] = 'a[' + i + ']';
	    } // eslint-disable-next-line no-new-func


	    factories[len] = Function('F,a', 'return new F(' + n.join(',') + ')');
	  }

	  return factories[len](F, args);
	};

	var _bind = Function.bind || function bind(that
	/* , ...args */
	) {
	  var fn = _aFunction(this);
	  var partArgs = arraySlice.call(arguments, 1);

	  var bound = function bound()
	  /* args... */
	  {
	    var args = partArgs.concat(arraySlice.call(arguments));
	    return this instanceof bound ? construct(fn, args.length, args) : _invoke(fn, args, that);
	  };

	  if (_isObject(fn.prototype)) bound.prototype = fn.prototype;
	  return bound;
	};

	// 19.2.3.2 / 15.3.4.5 Function.prototype.bind(thisArg, args...)


	_export(_export.P, 'Function', {
	  bind: _bind
	});

	var dP$2 = _objectDp.f;

	var FProto = Function.prototype;
	var nameRE = /^\s*function ([^ (]*)/;
	var NAME = 'name'; // 19.2.4.2 name

	NAME in FProto || _descriptors && dP$2(FProto, NAME, {
	  configurable: true,
	  get: function get() {
	    try {
	      return ('' + this).match(nameRE)[1];
	    } catch (e) {
	      return '';
	    }
	  }
	});

	var HAS_INSTANCE = _wks('hasInstance');

	var FunctionProto = Function.prototype; // 19.2.3.6 Function.prototype[@@hasInstance](V)

	if (!(HAS_INSTANCE in FunctionProto)) _objectDp.f(FunctionProto, HAS_INSTANCE, {
	  value: function value(O) {
	    if (typeof this != 'function' || !_isObject(O)) return false;
	    if (!_isObject(this.prototype)) return O instanceof this; // for environment w/o native `@@hasInstance` logic enough `instanceof`, but add this:

	    while (O = _objectGpo(O)) {
	      if (this.prototype === O) return true;
	    }

	    return false;
	  }
	});

	var _stringWs = "\t\n\x0B\f\r \xA0\u1680\u180E\u2000\u2001\u2002\u2003" + "\u2004\u2005\u2006\u2007\u2008\u2009\u200A\u202F\u205F\u3000\u2028\u2029\uFEFF";

	var space = '[' + _stringWs + ']';
	var non = "\u200B\x85";
	var ltrim = RegExp('^' + space + space + '*');
	var rtrim = RegExp(space + space + '*$');

	var exporter = function exporter(KEY, exec, ALIAS) {
	  var exp = {};
	  var FORCE = _fails(function () {
	    return !!_stringWs[KEY]() || non[KEY]() != non;
	  });
	  var fn = exp[KEY] = FORCE ? exec(trim) : _stringWs[KEY];
	  if (ALIAS) exp[ALIAS] = fn;
	  _export(_export.P + _export.F * FORCE, 'String', exp);
	}; // 1 -> String#trimLeft
	// 2 -> String#trimRight
	// 3 -> String#trim


	var trim = exporter.trim = function (string, TYPE) {
	  string = String(_defined(string));
	  if (TYPE & 1) string = string.replace(ltrim, '');
	  if (TYPE & 2) string = string.replace(rtrim, '');
	  return string;
	};

	var _stringTrim = exporter;

	var $parseInt = _global.parseInt;

	var $trim = _stringTrim.trim;



	var hex = /^[-+]?0[xX]/;
	var _parseInt = $parseInt(_stringWs + '08') !== 8 || $parseInt(_stringWs + '0x16') !== 22 ? function parseInt(str, radix) {
	  var string = $trim(String(str), 3);
	  return $parseInt(string, radix >>> 0 || (hex.test(string) ? 16 : 10));
	} : $parseInt;

	// 18.2.5 parseInt(string, radix)


	_export(_export.G + _export.F * (parseInt != _parseInt), {
	  parseInt: _parseInt
	});

	var $parseFloat = _global.parseFloat;

	var $trim$1 = _stringTrim.trim;

	var _parseFloat = 1 / $parseFloat(_stringWs + '-0') !== -Infinity ? function parseFloat(str) {
	  var string = $trim$1(String(str), 3);
	  var result = $parseFloat(string);
	  return result === 0 && string.charAt(0) == '-' ? -0 : result;
	} : $parseFloat;

	// 18.2.4 parseFloat(string)


	_export(_export.G + _export.F * (parseFloat != _parseFloat), {
	  parseFloat: _parseFloat
	});

	var setPrototypeOf = _setProto.set;

	var _inheritIfRequired = function (that, target, C) {
	  var S = target.constructor;
	  var P;

	  if (S !== C && typeof S == 'function' && (P = S.prototype) !== C.prototype && _isObject(P) && setPrototypeOf) {
	    setPrototypeOf(that, P);
	  }

	  return that;
	};

	var gOPN$2 = _objectGopn.f;

	var gOPD$2 = _objectGopd.f;

	var dP$3 = _objectDp.f;

	var $trim$2 = _stringTrim.trim;

	var NUMBER = 'Number';
	var $Number = _global[NUMBER];
	var Base = $Number;
	var proto = $Number.prototype; // Opera ~12 has broken Object#toString

	var BROKEN_COF = _cof(_objectCreate(proto)) == NUMBER;
	var TRIM = 'trim' in String.prototype; // 7.1.3 ToNumber(argument)

	var toNumber = function toNumber(argument) {
	  var it = _toPrimitive(argument, false);

	  if (typeof it == 'string' && it.length > 2) {
	    it = TRIM ? it.trim() : $trim$2(it, 3);
	    var first = it.charCodeAt(0);
	    var third, radix, maxCode;

	    if (first === 43 || first === 45) {
	      third = it.charCodeAt(2);
	      if (third === 88 || third === 120) return NaN; // Number('+0x1') should be NaN, old V8 fix
	    } else if (first === 48) {
	      switch (it.charCodeAt(1)) {
	        case 66:
	        case 98:
	          radix = 2;
	          maxCode = 49;
	          break;
	        // fast equal /^0b[01]+$/i

	        case 79:
	        case 111:
	          radix = 8;
	          maxCode = 55;
	          break;
	        // fast equal /^0o[0-7]+$/i

	        default:
	          return +it;
	      }

	      for (var digits = it.slice(2), i = 0, l = digits.length, code; i < l; i++) {
	        code = digits.charCodeAt(i); // parseInt parses a string to a first unavailable symbol
	        // but ToNumber should return NaN if a string contains unavailable symbols

	        if (code < 48 || code > maxCode) return NaN;
	      }

	      return parseInt(digits, radix);
	    }
	  }

	  return +it;
	};

	if (!$Number(' 0o1') || !$Number('0b1') || $Number('+0x1')) {
	  $Number = function Number(value) {
	    var it = arguments.length < 1 ? 0 : value;
	    var that = this;
	    return that instanceof $Number // check on 1..constructor(foo) case
	    && (BROKEN_COF ? _fails(function () {
	      proto.valueOf.call(that);
	    }) : _cof(that) != NUMBER) ? _inheritIfRequired(new Base(toNumber(it)), that, $Number) : toNumber(it);
	  };

	  for (var keys = _descriptors ? gOPN$2(Base) : ( // ES3:
	  'MAX_VALUE,MIN_VALUE,NaN,NEGATIVE_INFINITY,POSITIVE_INFINITY,' + // ES6 (in case, if modules with ES6 Number statics required before):
	  'EPSILON,isFinite,isInteger,isNaN,isSafeInteger,MAX_SAFE_INTEGER,' + 'MIN_SAFE_INTEGER,parseFloat,parseInt,isInteger').split(','), j$1 = 0, key; keys.length > j$1; j$1++) {
	    if (_has(Base, key = keys[j$1]) && !_has($Number, key)) {
	      dP$3($Number, key, gOPD$2(Base, key));
	    }
	  }

	  $Number.prototype = proto;
	  proto.constructor = $Number;

	  _redefine(_global, NUMBER, $Number);
	}

	var _aNumberValue = function (it, msg) {
	  if (typeof it != 'number' && _cof(it) != 'Number') throw TypeError(msg);
	  return +it;
	};

	var _stringRepeat = function repeat(count) {
	  var str = String(_defined(this));
	  var res = '';
	  var n = _toInteger(count);
	  if (n < 0 || n == Infinity) throw RangeError("Count can't be negative");

	  for (; n > 0; (n >>>= 1) && (str += str)) {
	    if (n & 1) res += str;
	  }

	  return res;
	};

	var $toFixed = 1.0.toFixed;
	var floor$1 = Math.floor;
	var data = [0, 0, 0, 0, 0, 0];
	var ERROR = 'Number.toFixed: incorrect invocation!';
	var ZERO = '0';

	var multiply = function multiply(n, c) {
	  var i = -1;
	  var c2 = c;

	  while (++i < 6) {
	    c2 += n * data[i];
	    data[i] = c2 % 1e7;
	    c2 = floor$1(c2 / 1e7);
	  }
	};

	var divide = function divide(n) {
	  var i = 6;
	  var c = 0;

	  while (--i >= 0) {
	    c += data[i];
	    data[i] = floor$1(c / n);
	    c = c % n * 1e7;
	  }
	};

	var numToString = function numToString() {
	  var i = 6;
	  var s = '';

	  while (--i >= 0) {
	    if (s !== '' || i === 0 || data[i] !== 0) {
	      var t = String(data[i]);
	      s = s === '' ? t : s + _stringRepeat.call(ZERO, 7 - t.length) + t;
	    }
	  }

	  return s;
	};

	var pow = function pow(x, n, acc) {
	  return n === 0 ? acc : n % 2 === 1 ? pow(x, n - 1, acc * x) : pow(x * x, n / 2, acc);
	};

	var log = function log(x) {
	  var n = 0;
	  var x2 = x;

	  while (x2 >= 4096) {
	    n += 12;
	    x2 /= 4096;
	  }

	  while (x2 >= 2) {
	    n += 1;
	    x2 /= 2;
	  }

	  return n;
	};

	_export(_export.P + _export.F * (!!$toFixed && (0.00008.toFixed(3) !== '0.000' || 0.9.toFixed(0) !== '1' || 1.255.toFixed(2) !== '1.25' || 1000000000000000128.0.toFixed(0) !== '1000000000000000128') || !_fails(function () {
	  // V8 ~ Android 4.3-
	  $toFixed.call({});
	})), 'Number', {
	  toFixed: function toFixed(fractionDigits) {
	    var x = _aNumberValue(this, ERROR);
	    var f = _toInteger(fractionDigits);
	    var s = '';
	    var m = ZERO;
	    var e, z, j, k;
	    if (f < 0 || f > 20) throw RangeError(ERROR); // eslint-disable-next-line no-self-compare

	    if (x != x) return 'NaN';
	    if (x <= -1e21 || x >= 1e21) return String(x);

	    if (x < 0) {
	      s = '-';
	      x = -x;
	    }

	    if (x > 1e-21) {
	      e = log(x * pow(2, 69, 1)) - 69;
	      z = e < 0 ? x * pow(2, -e, 1) : x / pow(2, e, 1);
	      z *= 0x10000000000000;
	      e = 52 - e;

	      if (e > 0) {
	        multiply(0, z);
	        j = f;

	        while (j >= 7) {
	          multiply(1e7, 0);
	          j -= 7;
	        }

	        multiply(pow(10, j, 1), 0);
	        j = e - 1;

	        while (j >= 23) {
	          divide(1 << 23);
	          j -= 23;
	        }

	        divide(1 << j);
	        multiply(1, 1);
	        divide(2);
	        m = numToString();
	      } else {
	        multiply(0, z);
	        multiply(1 << -e, 0);
	        m = numToString() + _stringRepeat.call(ZERO, f);
	      }
	    }

	    if (f > 0) {
	      k = m.length;
	      m = s + (k <= f ? '0.' + _stringRepeat.call(ZERO, f - k) + m : m.slice(0, k - f) + '.' + m.slice(k - f));
	    } else {
	      m = s + m;
	    }

	    return m;
	  }
	});

	var $toPrecision = 1.0.toPrecision;
	_export(_export.P + _export.F * (_fails(function () {
	  // IE7-
	  return $toPrecision.call(1, undefined) !== '1';
	}) || !_fails(function () {
	  // V8 ~ Android 4.3-
	  $toPrecision.call({});
	})), 'Number', {
	  toPrecision: function toPrecision(precision) {
	    var that = _aNumberValue(this, 'Number#toPrecision: incorrect invocation!');
	    return precision === undefined ? $toPrecision.call(that) : $toPrecision.call(that, precision);
	  }
	});

	// 20.1.2.1 Number.EPSILON


	_export(_export.S, 'Number', {
	  EPSILON: Math.pow(2, -52)
	});

	// 20.1.2.2 Number.isFinite(number)


	var _isFinite = _global.isFinite;

	_export(_export.S, 'Number', {
	  isFinite: function isFinite(it) {
	    return typeof it == 'number' && _isFinite(it);
	  }
	});

	// 20.1.2.3 Number.isInteger(number)


	var floor$2 = Math.floor;

	var _isInteger = function isInteger(it) {
	  return !_isObject(it) && isFinite(it) && floor$2(it) === it;
	};

	// 20.1.2.3 Number.isInteger(number)


	_export(_export.S, 'Number', {
	  isInteger: _isInteger
	});

	// 20.1.2.4 Number.isNaN(number)


	_export(_export.S, 'Number', {
	  isNaN: function isNaN(number) {
	    // eslint-disable-next-line no-self-compare
	    return number != number;
	  }
	});

	// 20.1.2.5 Number.isSafeInteger(number)




	var abs = Math.abs;
	_export(_export.S, 'Number', {
	  isSafeInteger: function isSafeInteger(number) {
	    return _isInteger(number) && abs(number) <= 0x1fffffffffffff;
	  }
	});

	// 20.1.2.6 Number.MAX_SAFE_INTEGER


	_export(_export.S, 'Number', {
	  MAX_SAFE_INTEGER: 0x1fffffffffffff
	});

	// 20.1.2.10 Number.MIN_SAFE_INTEGER


	_export(_export.S, 'Number', {
	  MIN_SAFE_INTEGER: -0x1fffffffffffff
	});

	// 20.1.2.12 Number.parseFloat(string)


	_export(_export.S + _export.F * (Number.parseFloat != _parseFloat), 'Number', {
	  parseFloat: _parseFloat
	});

	// 20.1.2.13 Number.parseInt(string, radix)


	_export(_export.S + _export.F * (Number.parseInt != _parseInt), 'Number', {
	  parseInt: _parseInt
	});

	// 20.2.2.20 Math.log1p(x)
	var _mathLog1p = Math.log1p || function log1p(x) {
	  return (x = +x) > -1e-8 && x < 1e-8 ? x - x * x / 2 : Math.log(1 + x);
	};

	// 20.2.2.3 Math.acosh(x)




	var sqrt = Math.sqrt;
	var $acosh = Math.acosh;
	_export(_export.S + _export.F * !($acosh // V8 bug: https://code.google.com/p/v8/issues/detail?id=3509
	&& Math.floor($acosh(Number.MAX_VALUE)) == 710 // Tor Browser bug: Math.acosh(Infinity) -> NaN
	&& $acosh(Infinity) == Infinity), 'Math', {
	  acosh: function acosh(x) {
	    return (x = +x) < 1 ? NaN : x > 94906265.62425156 ? Math.log(x) + Math.LN2 : _mathLog1p(x - 1 + sqrt(x - 1) * sqrt(x + 1));
	  }
	});

	// 20.2.2.5 Math.asinh(x)


	var $asinh = Math.asinh;

	function asinh(x) {
	  return !isFinite(x = +x) || x == 0 ? x : x < 0 ? -asinh(-x) : Math.log(x + Math.sqrt(x * x + 1));
	} // Tor Browser bug: Math.asinh(0) -> -0


	_export(_export.S + _export.F * !($asinh && 1 / $asinh(0) > 0), 'Math', {
	  asinh: asinh
	});

	// 20.2.2.7 Math.atanh(x)


	var $atanh = Math.atanh; // Tor Browser bug: Math.atanh(-0) -> 0

	_export(_export.S + _export.F * !($atanh && 1 / $atanh(-0) < 0), 'Math', {
	  atanh: function atanh(x) {
	    return (x = +x) == 0 ? x : Math.log((1 + x) / (1 - x)) / 2;
	  }
	});

	// 20.2.2.28 Math.sign(x)
	var _mathSign = Math.sign || function sign(x) {
	  // eslint-disable-next-line no-self-compare
	  return (x = +x) == 0 || x != x ? x : x < 0 ? -1 : 1;
	};

	// 20.2.2.9 Math.cbrt(x)




	_export(_export.S, 'Math', {
	  cbrt: function cbrt(x) {
	    return _mathSign(x = +x) * Math.pow(Math.abs(x), 1 / 3);
	  }
	});

	// 20.2.2.11 Math.clz32(x)


	_export(_export.S, 'Math', {
	  clz32: function clz32(x) {
	    return (x >>>= 0) ? 31 - Math.floor(Math.log(x + 0.5) * Math.LOG2E) : 32;
	  }
	});

	// 20.2.2.12 Math.cosh(x)


	var exp = Math.exp;
	_export(_export.S, 'Math', {
	  cosh: function cosh(x) {
	    return (exp(x = +x) + exp(-x)) / 2;
	  }
	});

	// 20.2.2.14 Math.expm1(x)
	var $expm1 = Math.expm1;
	var _mathExpm1 = !$expm1 // Old FF bug
	|| $expm1(10) > 22025.465794806719 || $expm1(10) < 22025.4657948067165168 // Tor Browser bug
	|| $expm1(-2e-17) != -2e-17 ? function expm1(x) {
	  return (x = +x) == 0 ? x : x > -1e-6 && x < 1e-6 ? x + x * x / 2 : Math.exp(x) - 1;
	} : $expm1;

	// 20.2.2.14 Math.expm1(x)




	_export(_export.S + _export.F * (_mathExpm1 != Math.expm1), 'Math', {
	  expm1: _mathExpm1
	});

	// 20.2.2.16 Math.fround(x)


	var pow$1 = Math.pow;
	var EPSILON = pow$1(2, -52);
	var EPSILON32 = pow$1(2, -23);
	var MAX32 = pow$1(2, 127) * (2 - EPSILON32);
	var MIN32 = pow$1(2, -126);

	var roundTiesToEven = function roundTiesToEven(n) {
	  return n + 1 / EPSILON - 1 / EPSILON;
	};

	var _mathFround = Math.fround || function fround(x) {
	  var $abs = Math.abs(x);
	  var $sign = _mathSign(x);
	  var a, result;
	  if ($abs < MIN32) return $sign * roundTiesToEven($abs / MIN32 / EPSILON32) * MIN32 * EPSILON32;
	  a = (1 + EPSILON32 / EPSILON) * $abs;
	  result = a - (a - $abs); // eslint-disable-next-line no-self-compare

	  if (result > MAX32 || result != result) return $sign * Infinity;
	  return $sign * result;
	};

	// 20.2.2.16 Math.fround(x)


	_export(_export.S, 'Math', {
	  fround: _mathFround
	});

	// 20.2.2.17 Math.hypot([value1[, value2[, ... ]]])


	var abs$1 = Math.abs;
	_export(_export.S, 'Math', {
	  hypot: function hypot(value1, value2) {
	    // eslint-disable-line no-unused-vars
	    var sum = 0;
	    var i = 0;
	    var aLen = arguments.length;
	    var larg = 0;
	    var arg, div;

	    while (i < aLen) {
	      arg = abs$1(arguments[i++]);

	      if (larg < arg) {
	        div = larg / arg;
	        sum = sum * div * div + 1;
	        larg = arg;
	      } else if (arg > 0) {
	        div = arg / larg;
	        sum += div * div;
	      } else sum += arg;
	    }

	    return larg === Infinity ? Infinity : larg * Math.sqrt(sum);
	  }
	});

	// 20.2.2.18 Math.imul(x, y)


	var $imul = Math.imul; // some WebKit versions fails with big numbers, some has wrong arity

	_export(_export.S + _export.F * _fails(function () {
	  return $imul(0xffffffff, 5) != -5 || $imul.length != 2;
	}), 'Math', {
	  imul: function imul(x, y) {
	    var UINT16 = 0xffff;
	    var xn = +x;
	    var yn = +y;
	    var xl = UINT16 & xn;
	    var yl = UINT16 & yn;
	    return 0 | xl * yl + ((UINT16 & xn >>> 16) * yl + xl * (UINT16 & yn >>> 16) << 16 >>> 0);
	  }
	});

	// 20.2.2.21 Math.log10(x)


	_export(_export.S, 'Math', {
	  log10: function log10(x) {
	    return Math.log(x) * Math.LOG10E;
	  }
	});

	// 20.2.2.20 Math.log1p(x)


	_export(_export.S, 'Math', {
	  log1p: _mathLog1p
	});

	// 20.2.2.22 Math.log2(x)


	_export(_export.S, 'Math', {
	  log2: function log2(x) {
	    return Math.log(x) / Math.LN2;
	  }
	});

	// 20.2.2.28 Math.sign(x)


	_export(_export.S, 'Math', {
	  sign: _mathSign
	});

	// 20.2.2.30 Math.sinh(x)




	var exp$1 = Math.exp; // V8 near Chromium 38 has a problem with very small numbers

	_export(_export.S + _export.F * _fails(function () {
	  return !Math.sinh(-2e-17) != -2e-17;
	}), 'Math', {
	  sinh: function sinh(x) {
	    return Math.abs(x = +x) < 1 ? (_mathExpm1(x) - _mathExpm1(-x)) / 2 : (exp$1(x - 1) - exp$1(-x - 1)) * (Math.E / 2);
	  }
	});

	// 20.2.2.33 Math.tanh(x)




	var exp$2 = Math.exp;
	_export(_export.S, 'Math', {
	  tanh: function tanh(x) {
	    var a = _mathExpm1(x = +x);
	    var b = _mathExpm1(-x);
	    return a == Infinity ? 1 : b == Infinity ? -1 : (a - b) / (exp$2(x) + exp$2(-x));
	  }
	});

	// 20.2.2.34 Math.trunc(x)


	_export(_export.S, 'Math', {
	  trunc: function trunc(it) {
	    return (it > 0 ? Math.floor : Math.ceil)(it);
	  }
	});

	var fromCharCode = String.fromCharCode;
	var $fromCodePoint = String.fromCodePoint; // length should be 1, old FF problem

	_export(_export.S + _export.F * (!!$fromCodePoint && $fromCodePoint.length != 1), 'String', {
	  // 21.1.2.2 String.fromCodePoint(...codePoints)
	  fromCodePoint: function fromCodePoint(x) {
	    // eslint-disable-line no-unused-vars
	    var res = [];
	    var aLen = arguments.length;
	    var i = 0;
	    var code;

	    while (aLen > i) {
	      code = +arguments[i++];
	      if (_toAbsoluteIndex(code, 0x10ffff) !== code) throw RangeError(code + ' is not a valid code point');
	      res.push(code < 0x10000 ? fromCharCode(code) : fromCharCode(((code -= 0x10000) >> 10) + 0xd800, code % 0x400 + 0xdc00));
	    }

	    return res.join('');
	  }
	});

	_export(_export.S, 'String', {
	  // 21.1.2.4 String.raw(callSite, ...substitutions)
	  raw: function raw(callSite) {
	    var tpl = _toIobject(callSite.raw);
	    var len = _toLength(tpl.length);
	    var aLen = arguments.length;
	    var res = [];
	    var i = 0;

	    while (len > i) {
	      res.push(String(tpl[i++]));
	      if (i < aLen) res.push(String(arguments[i]));
	    }

	    return res.join('');
	  }
	});

	_stringTrim('trim', function ($trim) {
	  return function trim() {
	    return $trim(this, 3);
	  };
	});

	// true  -> String#at
	// false -> String#codePointAt


	var _stringAt = function (TO_STRING) {
	  return function (that, pos) {
	    var s = String(_defined(that));
	    var i = _toInteger(pos);
	    var l = s.length;
	    var a, b;
	    if (i < 0 || i >= l) return TO_STRING ? '' : undefined;
	    a = s.charCodeAt(i);
	    return a < 0xd800 || a > 0xdbff || i + 1 === l || (b = s.charCodeAt(i + 1)) < 0xdc00 || b > 0xdfff ? TO_STRING ? s.charAt(i) : a : TO_STRING ? s.slice(i, i + 2) : (a - 0xd800 << 10) + (b - 0xdc00) + 0x10000;
	  };
	};

	var _iterators = {};

	var IteratorPrototype = {}; // 25.1.2.1.1 %IteratorPrototype%[@@iterator]()

	_hide(IteratorPrototype, _wks('iterator'), function () {
	  return this;
	});

	var _iterCreate = function (Constructor, NAME, next) {
	  Constructor.prototype = _objectCreate(IteratorPrototype, {
	    next: _propertyDesc(1, next)
	  });
	  _setToStringTag(Constructor, NAME + ' Iterator');
	};

	var ITERATOR = _wks('iterator');

	var BUGGY = !([].keys && 'next' in [].keys()); // Safari has buggy iterators w/o `next`

	var FF_ITERATOR = '@@iterator';
	var KEYS = 'keys';
	var VALUES = 'values';

	var returnThis = function returnThis() {
	  return this;
	};

	var _iterDefine = function (Base, NAME, Constructor, next, DEFAULT, IS_SET, FORCED) {
	  _iterCreate(Constructor, NAME, next);

	  var getMethod = function getMethod(kind) {
	    if (!BUGGY && kind in proto) return proto[kind];

	    switch (kind) {
	      case KEYS:
	        return function keys() {
	          return new Constructor(this, kind);
	        };

	      case VALUES:
	        return function values() {
	          return new Constructor(this, kind);
	        };
	    }

	    return function entries() {
	      return new Constructor(this, kind);
	    };
	  };

	  var TAG = NAME + ' Iterator';
	  var DEF_VALUES = DEFAULT == VALUES;
	  var VALUES_BUG = false;
	  var proto = Base.prototype;
	  var $native = proto[ITERATOR] || proto[FF_ITERATOR] || DEFAULT && proto[DEFAULT];
	  var $default = $native || getMethod(DEFAULT);
	  var $entries = DEFAULT ? !DEF_VALUES ? $default : getMethod('entries') : undefined;
	  var $anyNative = NAME == 'Array' ? proto.entries || $native : $native;
	  var methods, key, IteratorPrototype; // Fix native

	  if ($anyNative) {
	    IteratorPrototype = _objectGpo($anyNative.call(new Base()));

	    if (IteratorPrototype !== Object.prototype && IteratorPrototype.next) {
	      // Set @@toStringTag to native iterators
	      _setToStringTag(IteratorPrototype, TAG, true); // fix for some old engines

	      if (typeof IteratorPrototype[ITERATOR] != 'function') _hide(IteratorPrototype, ITERATOR, returnThis);
	    }
	  } // fix Array#{values, @@iterator}.name in V8 / FF


	  if (DEF_VALUES && $native && $native.name !== VALUES) {
	    VALUES_BUG = true;

	    $default = function values() {
	      return $native.call(this);
	    };
	  } // Define iterator


	  if (BUGGY || VALUES_BUG || !proto[ITERATOR]) {
	    _hide(proto, ITERATOR, $default);
	  } // Plug for library


	  _iterators[NAME] = $default;
	  _iterators[TAG] = returnThis;

	  if (DEFAULT) {
	    methods = {
	      values: DEF_VALUES ? $default : getMethod(VALUES),
	      keys: IS_SET ? $default : getMethod(KEYS),
	      entries: $entries
	    };
	    if (FORCED) for (key in methods) {
	      if (!(key in proto)) _redefine(proto, key, methods[key]);
	    } else _export(_export.P + _export.F * (BUGGY || VALUES_BUG), NAME, methods);
	  }

	  return methods;
	};

	var $at = _stringAt(true); // 21.1.3.27 String.prototype[@@iterator]()


	_iterDefine(String, 'String', function (iterated) {
	  this._t = String(iterated); // target

	  this._i = 0; // next index
	  // 21.1.5.2.1 %StringIteratorPrototype%.next()
	}, function () {
	  var O = this._t;
	  var index = this._i;
	  var point;
	  if (index >= O.length) return {
	    value: undefined,
	    done: true
	  };
	  point = $at(O, index);
	  this._i += point.length;
	  return {
	    value: point,
	    done: false
	  };
	});

	var $at$1 = _stringAt(false);

	_export(_export.P, 'String', {
	  // 21.1.3.3 String.prototype.codePointAt(pos)
	  codePointAt: function codePointAt(pos) {
	    return $at$1(this, pos);
	  }
	});

	// 7.2.8 IsRegExp(argument)




	var MATCH = _wks('match');

	var _isRegexp = function (it) {
	  var isRegExp;
	  return _isObject(it) && ((isRegExp = it[MATCH]) !== undefined ? !!isRegExp : _cof(it) == 'RegExp');
	};

	// helper for String#{startsWith, endsWith, includes}




	var _stringContext = function (that, searchString, NAME) {
	  if (_isRegexp(searchString)) throw TypeError('String#' + NAME + " doesn't accept regex!");
	  return String(_defined(that));
	};

	var MATCH$1 = _wks('match');

	var _failsIsRegexp = function (KEY) {
	  var re = /./;

	  try {
	    '/./'[KEY](re);
	  } catch (e) {
	    try {
	      re[MATCH$1] = false;
	      return !'/./'[KEY](re);
	    } catch (f) {
	      /* empty */
	    }
	  }

	  return true;
	};

	var ENDS_WITH = 'endsWith';
	var $endsWith = ''[ENDS_WITH];
	_export(_export.P + _export.F * _failsIsRegexp(ENDS_WITH), 'String', {
	  endsWith: function endsWith(searchString
	  /* , endPosition = @length */
	  ) {
	    var that = _stringContext(this, searchString, ENDS_WITH);
	    var endPosition = arguments.length > 1 ? arguments[1] : undefined;
	    var len = _toLength(that.length);
	    var end = endPosition === undefined ? len : Math.min(_toLength(endPosition), len);
	    var search = String(searchString);
	    return $endsWith ? $endsWith.call(that, search, end) : that.slice(end - search.length, end) === search;
	  }
	});

	var INCLUDES = 'includes';
	_export(_export.P + _export.F * _failsIsRegexp(INCLUDES), 'String', {
	  includes: function includes(searchString
	  /* , position = 0 */
	  ) {
	    return !!~_stringContext(this, searchString, INCLUDES).indexOf(searchString, arguments.length > 1 ? arguments[1] : undefined);
	  }
	});

	_export(_export.P, 'String', {
	  // 21.1.3.13 String.prototype.repeat(count)
	  repeat: _stringRepeat
	});

	var STARTS_WITH = 'startsWith';
	var $startsWith = ''[STARTS_WITH];
	_export(_export.P + _export.F * _failsIsRegexp(STARTS_WITH), 'String', {
	  startsWith: function startsWith(searchString
	  /* , position = 0 */
	  ) {
	    var that = _stringContext(this, searchString, STARTS_WITH);
	    var index = _toLength(Math.min(arguments.length > 1 ? arguments[1] : undefined, that.length));
	    var search = String(searchString);
	    return $startsWith ? $startsWith.call(that, search, index) : that.slice(index, index + search.length) === search;
	  }
	});

	var quot = /"/g; // B.2.3.2.1 CreateHTML(string, tag, attribute, value)

	var createHTML = function createHTML(string, tag, attribute, value) {
	  var S = String(_defined(string));
	  var p1 = '<' + tag;
	  if (attribute !== '') p1 += ' ' + attribute + '="' + String(value).replace(quot, '&quot;') + '"';
	  return p1 + '>' + S + '</' + tag + '>';
	};

	var _stringHtml = function (NAME, exec) {
	  var O = {};
	  O[NAME] = exec(createHTML);
	  _export(_export.P + _export.F * _fails(function () {
	    var test = ''[NAME]('"');
	    return test !== test.toLowerCase() || test.split('"').length > 3;
	  }), 'String', O);
	};

	_stringHtml('anchor', function (createHTML) {
	  return function anchor(name) {
	    return createHTML(this, 'a', 'name', name);
	  };
	});

	_stringHtml('big', function (createHTML) {
	  return function big() {
	    return createHTML(this, 'big', '', '');
	  };
	});

	_stringHtml('blink', function (createHTML) {
	  return function blink() {
	    return createHTML(this, 'blink', '', '');
	  };
	});

	_stringHtml('bold', function (createHTML) {
	  return function bold() {
	    return createHTML(this, 'b', '', '');
	  };
	});

	_stringHtml('fixed', function (createHTML) {
	  return function fixed() {
	    return createHTML(this, 'tt', '', '');
	  };
	});

	_stringHtml('fontcolor', function (createHTML) {
	  return function fontcolor(color) {
	    return createHTML(this, 'font', 'color', color);
	  };
	});

	_stringHtml('fontsize', function (createHTML) {
	  return function fontsize(size) {
	    return createHTML(this, 'font', 'size', size);
	  };
	});

	_stringHtml('italics', function (createHTML) {
	  return function italics() {
	    return createHTML(this, 'i', '', '');
	  };
	});

	_stringHtml('link', function (createHTML) {
	  return function link(url) {
	    return createHTML(this, 'a', 'href', url);
	  };
	});

	_stringHtml('small', function (createHTML) {
	  return function small() {
	    return createHTML(this, 'small', '', '');
	  };
	});

	_stringHtml('strike', function (createHTML) {
	  return function strike() {
	    return createHTML(this, 'strike', '', '');
	  };
	});

	_stringHtml('sub', function (createHTML) {
	  return function sub() {
	    return createHTML(this, 'sub', '', '');
	  };
	});

	_stringHtml('sup', function (createHTML) {
	  return function sup() {
	    return createHTML(this, 'sup', '', '');
	  };
	});

	// 20.3.3.1 / 15.9.4.4 Date.now()


	_export(_export.S, 'Date', {
	  now: function now() {
	    return new Date().getTime();
	  }
	});

	_export(_export.P + _export.F * _fails(function () {
	  return new Date(NaN).toJSON() !== null || Date.prototype.toJSON.call({
	    toISOString: function toISOString() {
	      return 1;
	    }
	  }) !== 1;
	}), 'Date', {
	  // eslint-disable-next-line no-unused-vars
	  toJSON: function toJSON(key) {
	    var O = _toObject(this);
	    var pv = _toPrimitive(O);
	    return typeof pv == 'number' && !isFinite(pv) ? null : O.toISOString();
	  }
	});

	var getTime = Date.prototype.getTime;
	var $toISOString = Date.prototype.toISOString;

	var lz = function lz(num) {
	  return num > 9 ? num : '0' + num;
	}; // PhantomJS / old WebKit has a broken implementations


	var _dateToIsoString = _fails(function () {
	  return $toISOString.call(new Date(-5e13 - 1)) != '0385-07-25T07:06:39.999Z';
	}) || !_fails(function () {
	  $toISOString.call(new Date(NaN));
	}) ? function toISOString() {
	  if (!isFinite(getTime.call(this))) throw RangeError('Invalid time value');
	  var d = this;
	  var y = d.getUTCFullYear();
	  var m = d.getUTCMilliseconds();
	  var s = y < 0 ? '-' : y > 9999 ? '+' : '';
	  return s + ('00000' + Math.abs(y)).slice(s ? -6 : -4) + '-' + lz(d.getUTCMonth() + 1) + '-' + lz(d.getUTCDate()) + 'T' + lz(d.getUTCHours()) + ':' + lz(d.getUTCMinutes()) + ':' + lz(d.getUTCSeconds()) + '.' + (m > 99 ? m : '0' + lz(m)) + 'Z';
	} : $toISOString;

	// 20.3.4.36 / 15.9.5.43 Date.prototype.toISOString()


	 // PhantomJS / old WebKit has a broken implementations


	_export(_export.P + _export.F * (Date.prototype.toISOString !== _dateToIsoString), 'Date', {
	  toISOString: _dateToIsoString
	});

	var DateProto = Date.prototype;
	var INVALID_DATE = 'Invalid Date';
	var TO_STRING = 'toString';
	var $toString = DateProto[TO_STRING];
	var getTime$1 = DateProto.getTime;

	if (new Date(NaN) + '' != INVALID_DATE) {
	  _redefine(DateProto, TO_STRING, function toString() {
	    var value = getTime$1.call(this); // eslint-disable-next-line no-self-compare

	    return value === value ? $toString.call(this) : INVALID_DATE;
	  });
	}

	var NUMBER$1 = 'number';

	var _dateToPrimitive = function (hint) {
	  if (hint !== 'string' && hint !== NUMBER$1 && hint !== 'default') throw TypeError('Incorrect hint');
	  return _toPrimitive(_anObject(this), hint != NUMBER$1);
	};

	var TO_PRIMITIVE$1 = _wks('toPrimitive');

	var proto$1 = Date.prototype;
	if (!(TO_PRIMITIVE$1 in proto$1)) _hide(proto$1, TO_PRIMITIVE$1, _dateToPrimitive);

	// 22.1.2.2 / 15.4.3.2 Array.isArray(arg)


	_export(_export.S, 'Array', {
	  isArray: _isArray
	});

	// call something on iterator step with safe closing on error


	var _iterCall = function (iterator, fn, value, entries) {
	  try {
	    return entries ? fn(_anObject(value)[0], value[1]) : fn(value); // 7.4.6 IteratorClose(iterator, completion)
	  } catch (e) {
	    var ret = iterator['return'];
	    if (ret !== undefined) _anObject(ret.call(iterator));
	    throw e;
	  }
	};

	// check on default Array iterator


	var ITERATOR$1 = _wks('iterator');

	var ArrayProto = Array.prototype;

	var _isArrayIter = function (it) {
	  return it !== undefined && (_iterators.Array === it || ArrayProto[ITERATOR$1] === it);
	};

	var _createProperty = function (object, index, value) {
	  if (index in object) _objectDp.f(object, index, _propertyDesc(0, value));else object[index] = value;
	};

	var ITERATOR$2 = _wks('iterator');



	var core_getIteratorMethod = _core.getIteratorMethod = function (it) {
	  if (it != undefined) return it[ITERATOR$2] || it['@@iterator'] || _iterators[_classof(it)];
	};

	var ITERATOR$3 = _wks('iterator');

	var SAFE_CLOSING = false;

	try {
	  var riter = [7][ITERATOR$3]();

	  riter['return'] = function () {
	    SAFE_CLOSING = true;
	  }; // eslint-disable-next-line no-throw-literal
	} catch (e) {
	  /* empty */
	}

	var _iterDetect = function (exec, skipClosing) {
	  if (!skipClosing && !SAFE_CLOSING) return false;
	  var safe = false;

	  try {
	    var arr = [7];
	    var iter = arr[ITERATOR$3]();

	    iter.next = function () {
	      return {
	        done: safe = true
	      };
	    };

	    arr[ITERATOR$3] = function () {
	      return iter;
	    };

	    exec(arr);
	  } catch (e) {
	    /* empty */
	  }

	  return safe;
	};

	_export(_export.S + _export.F * !_iterDetect(function (iter) {
	}), 'Array', {
	  // 22.1.2.1 Array.from(arrayLike, mapfn = undefined, thisArg = undefined)
	  from: function from(arrayLike
	  /* , mapfn = undefined, thisArg = undefined */
	  ) {
	    var O = _toObject(arrayLike);
	    var C = typeof this == 'function' ? this : Array;
	    var aLen = arguments.length;
	    var mapfn = aLen > 1 ? arguments[1] : undefined;
	    var mapping = mapfn !== undefined;
	    var index = 0;
	    var iterFn = core_getIteratorMethod(O);
	    var length, result, step, iterator;
	    if (mapping) mapfn = _ctx(mapfn, aLen > 2 ? arguments[2] : undefined, 2); // if object isn't iterable or it's array with default iterator - use simple case

	    if (iterFn != undefined && !(C == Array && _isArrayIter(iterFn))) {
	      for (iterator = iterFn.call(O), result = new C(); !(step = iterator.next()).done; index++) {
	        _createProperty(result, index, mapping ? _iterCall(iterator, mapfn, [step.value, index], true) : step.value);
	      }
	    } else {
	      length = _toLength(O.length);

	      for (result = new C(length); length > index; index++) {
	        _createProperty(result, index, mapping ? mapfn(O[index], index) : O[index]);
	      }
	    }

	    result.length = index;
	    return result;
	  }
	});

	// WebKit Array.of isn't generic


	_export(_export.S + _export.F * _fails(function () {
	  function F() {
	    /* empty */
	  }

	  return !(Array.of.call(F) instanceof F);
	}), 'Array', {
	  // 22.1.2.3 Array.of( ...items)
	  of: function of()
	  /* ...args */
	  {
	    var index = 0;
	    var aLen = arguments.length;
	    var result = new (typeof this == 'function' ? this : Array)(aLen);

	    while (aLen > index) {
	      _createProperty(result, index, arguments[index++]);
	    }

	    result.length = aLen;
	    return result;
	  }
	});

	var _strictMethod = function (method, arg) {
	  return !!method && _fails(function () {
	    // eslint-disable-next-line no-useless-call
	    arg ? method.call(null, function () {
	      /* empty */
	    }, 1) : method.call(null);
	  });
	};

	var arrayJoin = [].join; // fallback for not array-like strings

	_export(_export.P + _export.F * (_iobject != Object || !_strictMethod(arrayJoin)), 'Array', {
	  join: function join(separator) {
	    return arrayJoin.call(_toIobject(this), separator === undefined ? ',' : separator);
	  }
	});

	var arraySlice$1 = [].slice; // fallback for not array-like ES3 strings and DOM objects

	_export(_export.P + _export.F * _fails(function () {
	  if (_html) arraySlice$1.call(_html);
	}), 'Array', {
	  slice: function slice(begin, end) {
	    var len = _toLength(this.length);
	    var klass = _cof(this);
	    end = end === undefined ? len : end;
	    if (klass == 'Array') return arraySlice$1.call(this, begin, end);
	    var start = _toAbsoluteIndex(begin, len);
	    var upTo = _toAbsoluteIndex(end, len);
	    var size = _toLength(upTo - start);
	    var cloned = new Array(size);
	    var i = 0;

	    for (; i < size; i++) {
	      cloned[i] = klass == 'String' ? this.charAt(start + i) : this[start + i];
	    }

	    return cloned;
	  }
	});

	var $sort = [].sort;
	var test$1 = [1, 2, 3];
	_export(_export.P + _export.F * (_fails(function () {
	  // IE8-
	  test$1.sort(undefined);
	}) || !_fails(function () {
	  // V8 bug
	  test$1.sort(null); // Old WebKit
	}) || !_strictMethod($sort)), 'Array', {
	  // 22.1.3.25 Array.prototype.sort(comparefn)
	  sort: function sort(comparefn) {
	    return comparefn === undefined ? $sort.call(_toObject(this)) : $sort.call(_toObject(this), _aFunction(comparefn));
	  }
	});

	var SPECIES = _wks('species');

	var _arraySpeciesConstructor = function (original) {
	  var C;

	  if (_isArray(original)) {
	    C = original.constructor; // cross-realm fallback

	    if (typeof C == 'function' && (C === Array || _isArray(C.prototype))) C = undefined;

	    if (_isObject(C)) {
	      C = C[SPECIES];
	      if (C === null) C = undefined;
	    }
	  }

	  return C === undefined ? Array : C;
	};

	// 9.4.2.3 ArraySpeciesCreate(originalArray, length)


	var _arraySpeciesCreate = function (original, length) {
	  return new (_arraySpeciesConstructor(original))(length);
	};

	// 0 -> Array#forEach
	// 1 -> Array#map
	// 2 -> Array#filter
	// 3 -> Array#some
	// 4 -> Array#every
	// 5 -> Array#find
	// 6 -> Array#findIndex










	var _arrayMethods = function (TYPE, $create) {
	  var IS_MAP = TYPE == 1;
	  var IS_FILTER = TYPE == 2;
	  var IS_SOME = TYPE == 3;
	  var IS_EVERY = TYPE == 4;
	  var IS_FIND_INDEX = TYPE == 6;
	  var NO_HOLES = TYPE == 5 || IS_FIND_INDEX;
	  var create = $create || _arraySpeciesCreate;
	  return function ($this, callbackfn, that) {
	    var O = _toObject($this);
	    var self = _iobject(O);
	    var f = _ctx(callbackfn, that, 3);
	    var length = _toLength(self.length);
	    var index = 0;
	    var result = IS_MAP ? create($this, length) : IS_FILTER ? create($this, 0) : undefined;
	    var val, res;

	    for (; length > index; index++) {
	      if (NO_HOLES || index in self) {
	        val = self[index];
	        res = f(val, index, O);

	        if (TYPE) {
	          if (IS_MAP) result[index] = res; // map
	          else if (res) switch (TYPE) {
	              case 3:
	                return true;
	              // some

	              case 5:
	                return val;
	              // find

	              case 6:
	                return index;
	              // findIndex

	              case 2:
	                result.push(val);
	              // filter
	            } else if (IS_EVERY) return false; // every
	        }
	      }
	    }

	    return IS_FIND_INDEX ? -1 : IS_SOME || IS_EVERY ? IS_EVERY : result;
	  };
	};

	var $forEach = _arrayMethods(0);

	var STRICT = _strictMethod([].forEach, true);

	_export(_export.P + _export.F * !STRICT, 'Array', {
	  // 22.1.3.10 / 15.4.4.18 Array.prototype.forEach(callbackfn [, thisArg])
	  forEach: function forEach(callbackfn
	  /* , thisArg */
	  ) {
	    return $forEach(this, callbackfn, arguments[1]);
	  }
	});

	var $map = _arrayMethods(1);

	_export(_export.P + _export.F * !_strictMethod([].map, true), 'Array', {
	  // 22.1.3.15 / 15.4.4.19 Array.prototype.map(callbackfn [, thisArg])
	  map: function map(callbackfn
	  /* , thisArg */
	  ) {
	    return $map(this, callbackfn, arguments[1]);
	  }
	});

	var $filter = _arrayMethods(2);

	_export(_export.P + _export.F * !_strictMethod([].filter, true), 'Array', {
	  // 22.1.3.7 / 15.4.4.20 Array.prototype.filter(callbackfn [, thisArg])
	  filter: function filter(callbackfn
	  /* , thisArg */
	  ) {
	    return $filter(this, callbackfn, arguments[1]);
	  }
	});

	var $some = _arrayMethods(3);

	_export(_export.P + _export.F * !_strictMethod([].some, true), 'Array', {
	  // 22.1.3.23 / 15.4.4.17 Array.prototype.some(callbackfn [, thisArg])
	  some: function some(callbackfn
	  /* , thisArg */
	  ) {
	    return $some(this, callbackfn, arguments[1]);
	  }
	});

	var $every = _arrayMethods(4);

	_export(_export.P + _export.F * !_strictMethod([].every, true), 'Array', {
	  // 22.1.3.5 / 15.4.4.16 Array.prototype.every(callbackfn [, thisArg])
	  every: function every(callbackfn
	  /* , thisArg */
	  ) {
	    return $every(this, callbackfn, arguments[1]);
	  }
	});

	var _arrayReduce = function (that, callbackfn, aLen, memo, isRight) {
	  _aFunction(callbackfn);
	  var O = _toObject(that);
	  var self = _iobject(O);
	  var length = _toLength(O.length);
	  var index = isRight ? length - 1 : 0;
	  var i = isRight ? -1 : 1;
	  if (aLen < 2) for (;;) {
	    if (index in self) {
	      memo = self[index];
	      index += i;
	      break;
	    }

	    index += i;

	    if (isRight ? index < 0 : length <= index) {
	      throw TypeError('Reduce of empty array with no initial value');
	    }
	  }

	  for (; isRight ? index >= 0 : length > index; index += i) {
	    if (index in self) {
	      memo = callbackfn(memo, self[index], index, O);
	    }
	  }

	  return memo;
	};

	_export(_export.P + _export.F * !_strictMethod([].reduce, true), 'Array', {
	  // 22.1.3.18 / 15.4.4.21 Array.prototype.reduce(callbackfn [, initialValue])
	  reduce: function reduce(callbackfn
	  /* , initialValue */
	  ) {
	    return _arrayReduce(this, callbackfn, arguments.length, arguments[1], false);
	  }
	});

	_export(_export.P + _export.F * !_strictMethod([].reduceRight, true), 'Array', {
	  // 22.1.3.19 / 15.4.4.22 Array.prototype.reduceRight(callbackfn [, initialValue])
	  reduceRight: function reduceRight(callbackfn
	  /* , initialValue */
	  ) {
	    return _arrayReduce(this, callbackfn, arguments.length, arguments[1], true);
	  }
	});

	var $indexOf = _arrayIncludes(false);

	var $native = [].indexOf;
	var NEGATIVE_ZERO = !!$native && 1 / [1].indexOf(1, -0) < 0;
	_export(_export.P + _export.F * (NEGATIVE_ZERO || !_strictMethod($native)), 'Array', {
	  // 22.1.3.11 / 15.4.4.14 Array.prototype.indexOf(searchElement [, fromIndex])
	  indexOf: function indexOf(searchElement
	  /* , fromIndex = 0 */
	  ) {
	    return NEGATIVE_ZERO // convert -0 to +0
	    ? $native.apply(this, arguments) || 0 : $indexOf(this, searchElement, arguments[1]);
	  }
	});

	var $native$1 = [].lastIndexOf;
	var NEGATIVE_ZERO$1 = !!$native$1 && 1 / [1].lastIndexOf(1, -0) < 0;
	_export(_export.P + _export.F * (NEGATIVE_ZERO$1 || !_strictMethod($native$1)), 'Array', {
	  // 22.1.3.14 / 15.4.4.15 Array.prototype.lastIndexOf(searchElement [, fromIndex])
	  lastIndexOf: function lastIndexOf(searchElement
	  /* , fromIndex = @[*-1] */
	  ) {
	    // convert -0 to +0
	    if (NEGATIVE_ZERO$1) return $native$1.apply(this, arguments) || 0;
	    var O = _toIobject(this);
	    var length = _toLength(O.length);
	    var index = length - 1;
	    if (arguments.length > 1) index = Math.min(index, _toInteger(arguments[1]));
	    if (index < 0) index = length + index;

	    for (; index >= 0; index--) {
	      if (index in O) if (O[index] === searchElement) return index || 0;
	    }

	    return -1;
	  }
	});

	var _arrayCopyWithin = [].copyWithin || function copyWithin(target
	/* = 0 */
	, start
	/* = 0, end = @length */
	) {
	  var O = _toObject(this);
	  var len = _toLength(O.length);
	  var to = _toAbsoluteIndex(target, len);
	  var from = _toAbsoluteIndex(start, len);
	  var end = arguments.length > 2 ? arguments[2] : undefined;
	  var count = Math.min((end === undefined ? len : _toAbsoluteIndex(end, len)) - from, len - to);
	  var inc = 1;

	  if (from < to && to < from + count) {
	    inc = -1;
	    from += count - 1;
	    to += count - 1;
	  }

	  while (count-- > 0) {
	    if (from in O) O[to] = O[from];else delete O[to];
	    to += inc;
	    from += inc;
	  }

	  return O;
	};

	// 22.1.3.31 Array.prototype[@@unscopables]
	var UNSCOPABLES = _wks('unscopables');

	var ArrayProto$1 = Array.prototype;
	if (ArrayProto$1[UNSCOPABLES] == undefined) _hide(ArrayProto$1, UNSCOPABLES, {});

	var _addToUnscopables = function (key) {
	  ArrayProto$1[UNSCOPABLES][key] = true;
	};

	// 22.1.3.3 Array.prototype.copyWithin(target, start, end = this.length)


	_export(_export.P, 'Array', {
	  copyWithin: _arrayCopyWithin
	});

	_addToUnscopables('copyWithin');

	var _arrayFill = function fill(value
	/* , start = 0, end = @length */
	) {
	  var O = _toObject(this);
	  var length = _toLength(O.length);
	  var aLen = arguments.length;
	  var index = _toAbsoluteIndex(aLen > 1 ? arguments[1] : undefined, length);
	  var end = aLen > 2 ? arguments[2] : undefined;
	  var endPos = end === undefined ? length : _toAbsoluteIndex(end, length);

	  while (endPos > index) {
	    O[index++] = value;
	  }

	  return O;
	};

	// 22.1.3.6 Array.prototype.fill(value, start = 0, end = this.length)


	_export(_export.P, 'Array', {
	  fill: _arrayFill
	});

	_addToUnscopables('fill');

	var $find = _arrayMethods(5);

	var KEY = 'find';
	var forced = true; // Shouldn't skip holes

	if (KEY in []) Array(1)[KEY](function () {
	  forced = false;
	});
	_export(_export.P + _export.F * forced, 'Array', {
	  find: function find(callbackfn
	  /* , that = undefined */
	  ) {
	    return $find(this, callbackfn, arguments.length > 1 ? arguments[1] : undefined);
	  }
	});

	_addToUnscopables(KEY);

	var $find$1 = _arrayMethods(6);

	var KEY$1 = 'findIndex';
	var forced$1 = true; // Shouldn't skip holes

	if (KEY$1 in []) Array(1)[KEY$1](function () {
	  forced$1 = false;
	});
	_export(_export.P + _export.F * forced$1, 'Array', {
	  findIndex: function findIndex(callbackfn
	  /* , that = undefined */
	  ) {
	    return $find$1(this, callbackfn, arguments.length > 1 ? arguments[1] : undefined);
	  }
	});

	_addToUnscopables(KEY$1);

	var SPECIES$1 = _wks('species');

	var _setSpecies = function (KEY) {
	  var C = _global[KEY];
	  if (_descriptors && C && !C[SPECIES$1]) _objectDp.f(C, SPECIES$1, {
	    configurable: true,
	    get: function get() {
	      return this;
	    }
	  });
	};

	_setSpecies('Array');

	var _iterStep = function (done, value) {
	  return {
	    value: value,
	    done: !!done
	  };
	};

	// 22.1.3.4 Array.prototype.entries()
	// 22.1.3.13 Array.prototype.keys()
	// 22.1.3.29 Array.prototype.values()
	// 22.1.3.30 Array.prototype[@@iterator]()


	var es6_array_iterator = _iterDefine(Array, 'Array', function (iterated, kind) {
	  this._t = _toIobject(iterated); // target

	  this._i = 0; // next index

	  this._k = kind; // kind
	  // 22.1.5.2.1 %ArrayIteratorPrototype%.next()
	}, function () {
	  var O = this._t;
	  var kind = this._k;
	  var index = this._i++;

	  if (!O || index >= O.length) {
	    this._t = undefined;
	    return _iterStep(1);
	  }

	  if (kind == 'keys') return _iterStep(0, index);
	  if (kind == 'values') return _iterStep(0, O[index]);
	  return _iterStep(0, [index, O[index]]);
	}, 'values'); // argumentsList[@@iterator] is %ArrayProto_values% (9.4.4.6, 9.4.4.7)

	_iterators.Arguments = _iterators.Array;
	_addToUnscopables('keys');
	_addToUnscopables('values');
	_addToUnscopables('entries');

	var _flags = function () {
	  var that = _anObject(this);
	  var result = '';
	  if (that.global) result += 'g';
	  if (that.ignoreCase) result += 'i';
	  if (that.multiline) result += 'm';
	  if (that.unicode) result += 'u';
	  if (that.sticky) result += 'y';
	  return result;
	};

	var dP$4 = _objectDp.f;

	var gOPN$3 = _objectGopn.f;





	var $RegExp = _global.RegExp;
	var Base$1 = $RegExp;
	var proto$2 = $RegExp.prototype;
	var re1 = /a/g;
	var re2 = /a/g; // "new" creates a new object, old webkit buggy here

	var CORRECT_NEW = new $RegExp(re1) !== re1;

	if (_descriptors && (!CORRECT_NEW || _fails(function () {
	  re2[_wks('match')] = false; // RegExp constructor can alter flags and IsRegExp works correct with @@match

	  return $RegExp(re1) != re1 || $RegExp(re2) == re2 || $RegExp(re1, 'i') != '/a/i';
	}))) {
	  $RegExp = function RegExp(p, f) {
	    var tiRE = this instanceof $RegExp;
	    var piRE = _isRegexp(p);
	    var fiU = f === undefined;
	    return !tiRE && piRE && p.constructor === $RegExp && fiU ? p : _inheritIfRequired(CORRECT_NEW ? new Base$1(piRE && !fiU ? p.source : p, f) : Base$1((piRE = p instanceof $RegExp) ? p.source : p, piRE && fiU ? _flags.call(p) : f), tiRE ? this : proto$2, $RegExp);
	  };

	  var proxy = function proxy(key) {
	    key in $RegExp || dP$4($RegExp, key, {
	      configurable: true,
	      get: function get() {
	        return Base$1[key];
	      },
	      set: function set(it) {
	        Base$1[key] = it;
	      }
	    });
	  };

	  for (var keys$1 = gOPN$3(Base$1), i = 0; keys$1.length > i;) {
	    proxy(keys$1[i++]);
	  }

	  proto$2.constructor = $RegExp;
	  $RegExp.prototype = proto$2;

	  _redefine(_global, 'RegExp', $RegExp);
	}

	_setSpecies('RegExp');

	var nativeExec = RegExp.prototype.exec; // This always refers to the native implementation, because the
	// String#replace polyfill uses ./fix-regexp-well-known-symbol-logic.js,
	// which loads this file before patching the method.

	var nativeReplace = String.prototype.replace;
	var patchedExec = nativeExec;
	var LAST_INDEX = 'lastIndex';

	var UPDATES_LAST_INDEX_WRONG = function () {
	  var re1 = /a/,
	      re2 = /b*/g;
	  nativeExec.call(re1, 'a');
	  nativeExec.call(re2, 'a');
	  return re1[LAST_INDEX] !== 0 || re2[LAST_INDEX] !== 0;
	}(); // nonparticipating capturing group, copied from es5-shim's String#split patch.


	var NPCG_INCLUDED = /()??/.exec('')[1] !== undefined;
	var PATCH = UPDATES_LAST_INDEX_WRONG || NPCG_INCLUDED;

	if (PATCH) {
	  patchedExec = function exec(str) {
	    var re = this;
	    var lastIndex, reCopy, match, i;

	    if (NPCG_INCLUDED) {
	      reCopy = new RegExp('^' + re.source + '$(?!\\s)', _flags.call(re));
	    }

	    if (UPDATES_LAST_INDEX_WRONG) lastIndex = re[LAST_INDEX];
	    match = nativeExec.call(re, str);

	    if (UPDATES_LAST_INDEX_WRONG && match) {
	      re[LAST_INDEX] = re.global ? match.index + match[0].length : lastIndex;
	    }

	    if (NPCG_INCLUDED && match && match.length > 1) {
	      // Fix browsers whose `exec` methods don't consistently return `undefined`
	      // for NPCG, like IE8. NOTE: This doesn' work for /(.?)?/
	      // eslint-disable-next-line no-loop-func
	      nativeReplace.call(match[0], reCopy, function () {
	        for (i = 1; i < arguments.length - 2; i++) {
	          if (arguments[i] === undefined) match[i] = undefined;
	        }
	      });
	    }

	    return match;
	  };
	}

	var _regexpExec = patchedExec;

	_export({
	  target: 'RegExp',
	  proto: true,
	  forced: _regexpExec !== /./.exec
	}, {
	  exec: _regexpExec
	});

	// 21.2.5.3 get RegExp.prototype.flags()
	if (_descriptors && /./g.flags != 'g') _objectDp.f(RegExp.prototype, 'flags', {
	  configurable: true,
	  get: _flags
	});

	var TO_STRING$1 = 'toString';
	var $toString$1 = /./[TO_STRING$1];

	var define = function define(fn) {
	  _redefine(RegExp.prototype, TO_STRING$1, fn, true);
	}; // 21.2.5.14 RegExp.prototype.toString()


	if (_fails(function () {
	  return $toString$1.call({
	    source: 'a',
	    flags: 'b'
	  }) != '/a/b';
	})) {
	  define(function toString() {
	    var R = _anObject(this);
	    return '/'.concat(R.source, '/', 'flags' in R ? R.flags : !_descriptors && R instanceof RegExp ? _flags.call(R) : undefined);
	  }); // FF44- RegExp#toString has a wrong name
	} else if ($toString$1.name != TO_STRING$1) {
	  define(function toString() {
	    return $toString$1.call(this);
	  });
	}

	var at = _stringAt(true); // `AdvanceStringIndex` abstract operation
	// https://tc39.github.io/ecma262/#sec-advancestringindex


	var _advanceStringIndex = function (S, index, unicode) {
	  return index + (unicode ? at(S, index).length : 1);
	};

	var builtinExec = RegExp.prototype.exec; // `RegExpExec` abstract operation
	// https://tc39.github.io/ecma262/#sec-regexpexec

	var _regexpExecAbstract = function (R, S) {
	  var exec = R.exec;

	  if (typeof exec === 'function') {
	    var result = exec.call(R, S);

	    if (babelHelpers.typeof(result) !== 'object') {
	      throw new TypeError('RegExp exec method returned something other than an Object or null');
	    }

	    return result;
	  }

	  if (_classof(R) !== 'RegExp') {
	    throw new TypeError('RegExp#exec called on incompatible receiver');
	  }

	  return builtinExec.call(R, S);
	};

	var SPECIES$2 = _wks('species');
	var REPLACE_SUPPORTS_NAMED_GROUPS = !_fails(function () {
	  // #replace needs built-in support for named groups.
	  // #match works fine because it just return the exec results, even if it has
	  // a "grops" property.
	  var re = /./;

	  re.exec = function () {
	    var result = [];
	    result.groups = {
	      a: '7'
	    };
	    return result;
	  };

	  return ''.replace(re, '$<a>') !== '7';
	});

	var SPLIT_WORKS_WITH_OVERWRITTEN_EXEC = function () {
	  // Chrome 51 has a buggy "split" implementation when RegExp#exec !== nativeExec
	  var re = /(?:)/;
	  var originalExec = re.exec;

	  re.exec = function () {
	    return originalExec.apply(this, arguments);
	  };

	  var result = 'ab'.split(re);
	  return result.length === 2 && result[0] === 'a' && result[1] === 'b';
	}();

	var _fixReWks = function (KEY, length, exec) {
	  var SYMBOL = _wks(KEY);
	  var DELEGATES_TO_SYMBOL = !_fails(function () {
	    // String methods call symbol-named RegEp methods
	    var O = {};

	    O[SYMBOL] = function () {
	      return 7;
	    };

	    return ''[KEY](O) != 7;
	  });
	  var DELEGATES_TO_EXEC = DELEGATES_TO_SYMBOL ? !_fails(function () {
	    // Symbol-named RegExp methods call .exec
	    var execCalled = false;
	    var re = /a/;

	    re.exec = function () {
	      execCalled = true;
	      return null;
	    };

	    if (KEY === 'split') {
	      // RegExp[@@split] doesn't call the regex's exec method, but first creates
	      // a new one. We need to return the patched regex when creating the new one.
	      re.constructor = {};

	      re.constructor[SPECIES$2] = function () {
	        return re;
	      };
	    }

	    re[SYMBOL]('');
	    return !execCalled;
	  }) : undefined;

	  if (!DELEGATES_TO_SYMBOL || !DELEGATES_TO_EXEC || KEY === 'replace' && !REPLACE_SUPPORTS_NAMED_GROUPS || KEY === 'split' && !SPLIT_WORKS_WITH_OVERWRITTEN_EXEC) {
	    var nativeRegExpMethod = /./[SYMBOL];
	    var fns = exec(_defined, SYMBOL, ''[KEY], function maybeCallNative(nativeMethod, regexp, str, arg2, forceStringMethod) {
	      if (regexp.exec === _regexpExec) {
	        if (DELEGATES_TO_SYMBOL && !forceStringMethod) {
	          // The native String method already delegates to @@method (this
	          // polyfilled function), leasing to infinite recursion.
	          // We avoid it by directly calling the native @@method method.
	          return {
	            done: true,
	            value: nativeRegExpMethod.call(regexp, str, arg2)
	          };
	        }

	        return {
	          done: true,
	          value: nativeMethod.call(str, regexp, arg2)
	        };
	      }

	      return {
	        done: false
	      };
	    });
	    var strfn = fns[0];
	    var rxfn = fns[1];
	    _redefine(String.prototype, KEY, strfn);
	    _hide(RegExp.prototype, SYMBOL, length == 2 // 21.2.5.8 RegExp.prototype[@@replace](string, replaceValue)
	    // 21.2.5.11 RegExp.prototype[@@split](string, limit)
	    ? function (string, arg) {
	      return rxfn.call(string, this, arg);
	    } // 21.2.5.6 RegExp.prototype[@@match](string)
	    // 21.2.5.9 RegExp.prototype[@@search](string)
	    : function (string) {
	      return rxfn.call(string, this);
	    });
	  }
	};

	// @@match logic


	_fixReWks('match', 1, function (defined, MATCH, $match, maybeCallNative) {
	  return [// `String.prototype.match` method
	  // https://tc39.github.io/ecma262/#sec-string.prototype.match
	  function match(regexp) {
	    var O = defined(this);
	    var fn = regexp == undefined ? undefined : regexp[MATCH];
	    return fn !== undefined ? fn.call(regexp, O) : new RegExp(regexp)[MATCH](String(O));
	  }, // `RegExp.prototype[@@match]` method
	  // https://tc39.github.io/ecma262/#sec-regexp.prototype-@@match
	  function (regexp) {
	    var res = maybeCallNative($match, regexp, this);
	    if (res.done) return res.value;
	    var rx = _anObject(regexp);
	    var S = String(this);
	    if (!rx.global) return _regexpExecAbstract(rx, S);
	    var fullUnicode = rx.unicode;
	    rx.lastIndex = 0;
	    var A = [];
	    var n = 0;
	    var result;

	    while ((result = _regexpExecAbstract(rx, S)) !== null) {
	      var matchStr = String(result[0]);
	      A[n] = matchStr;
	      if (matchStr === '') rx.lastIndex = _advanceStringIndex(S, _toLength(rx.lastIndex), fullUnicode);
	      n++;
	    }

	    return n === 0 ? null : A;
	  }];
	});

	var max$1 = Math.max;
	var min$2 = Math.min;
	var floor$3 = Math.floor;
	var SUBSTITUTION_SYMBOLS = /\$([$&`']|\d\d?|<[^>]*>)/g;
	var SUBSTITUTION_SYMBOLS_NO_NAMED = /\$([$&`']|\d\d?)/g;

	var maybeToString = function maybeToString(it) {
	  return it === undefined ? it : String(it);
	}; // @@replace logic


	_fixReWks('replace', 2, function (defined, REPLACE, $replace, maybeCallNative) {
	  return [// `String.prototype.replace` method
	  // https://tc39.github.io/ecma262/#sec-string.prototype.replace
	  function replace(searchValue, replaceValue) {
	    var O = defined(this);
	    var fn = searchValue == undefined ? undefined : searchValue[REPLACE];
	    return fn !== undefined ? fn.call(searchValue, O, replaceValue) : $replace.call(String(O), searchValue, replaceValue);
	  }, // `RegExp.prototype[@@replace]` method
	  // https://tc39.github.io/ecma262/#sec-regexp.prototype-@@replace
	  function (regexp, replaceValue) {
	    var res = maybeCallNative($replace, regexp, this, replaceValue);
	    if (res.done) return res.value;
	    var rx = _anObject(regexp);
	    var S = String(this);
	    var functionalReplace = typeof replaceValue === 'function';
	    if (!functionalReplace) replaceValue = String(replaceValue);
	    var global = rx.global;

	    if (global) {
	      var fullUnicode = rx.unicode;
	      rx.lastIndex = 0;
	    }

	    var results = [];

	    while (true) {
	      var result = _regexpExecAbstract(rx, S);
	      if (result === null) break;
	      results.push(result);
	      if (!global) break;
	      var matchStr = String(result[0]);
	      if (matchStr === '') rx.lastIndex = _advanceStringIndex(S, _toLength(rx.lastIndex), fullUnicode);
	    }

	    var accumulatedResult = '';
	    var nextSourcePosition = 0;

	    for (var i = 0; i < results.length; i++) {
	      result = results[i];
	      var matched = String(result[0]);
	      var position = max$1(min$2(_toInteger(result.index), S.length), 0);
	      var captures = []; // NOTE: This is equivalent to
	      //   captures = result.slice(1).map(maybeToString)
	      // but for some reason `nativeSlice.call(result, 1, result.length)` (called in
	      // the slice polyfill when slicing native arrays) "doesn't work" in safari 9 and
	      // causes a crash (https://pastebin.com/N21QzeQA) when trying to debug it.

	      for (var j = 1; j < result.length; j++) {
	        captures.push(maybeToString(result[j]));
	      }

	      var namedCaptures = result.groups;

	      if (functionalReplace) {
	        var replacerArgs = [matched].concat(captures, position, S);
	        if (namedCaptures !== undefined) replacerArgs.push(namedCaptures);
	        var replacement = String(replaceValue.apply(undefined, replacerArgs));
	      } else {
	        replacement = getSubstitution(matched, S, position, captures, namedCaptures, replaceValue);
	      }

	      if (position >= nextSourcePosition) {
	        accumulatedResult += S.slice(nextSourcePosition, position) + replacement;
	        nextSourcePosition = position + matched.length;
	      }
	    }

	    return accumulatedResult + S.slice(nextSourcePosition);
	  }]; // https://tc39.github.io/ecma262/#sec-getsubstitution

	  function getSubstitution(matched, str, position, captures, namedCaptures, replacement) {
	    var tailPos = position + matched.length;
	    var m = captures.length;
	    var symbols = SUBSTITUTION_SYMBOLS_NO_NAMED;

	    if (namedCaptures !== undefined) {
	      namedCaptures = _toObject(namedCaptures);
	      symbols = SUBSTITUTION_SYMBOLS;
	    }

	    return $replace.call(replacement, symbols, function (match, ch) {
	      var capture;

	      switch (ch.charAt(0)) {
	        case '$':
	          return '$';

	        case '&':
	          return matched;

	        case '`':
	          return str.slice(0, position);

	        case "'":
	          return str.slice(tailPos);

	        case '<':
	          capture = namedCaptures[ch.slice(1, -1)];
	          break;

	        default:
	          // \d\d?
	          var n = +ch;
	          if (n === 0) return match;

	          if (n > m) {
	            var f = floor$3(n / 10);
	            if (f === 0) return match;
	            if (f <= m) return captures[f - 1] === undefined ? ch.charAt(1) : captures[f - 1] + ch.charAt(1);
	            return match;
	          }

	          capture = captures[n - 1];
	      }

	      return capture === undefined ? '' : capture;
	    });
	  }
	});

	// @@search logic


	_fixReWks('search', 1, function (defined, SEARCH, $search, maybeCallNative) {
	  return [// `String.prototype.search` method
	  // https://tc39.github.io/ecma262/#sec-string.prototype.search
	  function search(regexp) {
	    var O = defined(this);
	    var fn = regexp == undefined ? undefined : regexp[SEARCH];
	    return fn !== undefined ? fn.call(regexp, O) : new RegExp(regexp)[SEARCH](String(O));
	  }, // `RegExp.prototype[@@search]` method
	  // https://tc39.github.io/ecma262/#sec-regexp.prototype-@@search
	  function (regexp) {
	    var res = maybeCallNative($search, regexp, this);
	    if (res.done) return res.value;
	    var rx = _anObject(regexp);
	    var S = String(this);
	    var previousLastIndex = rx.lastIndex;
	    if (!_sameValue(previousLastIndex, 0)) rx.lastIndex = 0;
	    var result = _regexpExecAbstract(rx, S);
	    if (!_sameValue(rx.lastIndex, previousLastIndex)) rx.lastIndex = previousLastIndex;
	    return result === null ? -1 : result.index;
	  }];
	});

	// 7.3.20 SpeciesConstructor(O, defaultConstructor)




	var SPECIES$3 = _wks('species');

	var _speciesConstructor = function (O, D) {
	  var C = _anObject(O).constructor;
	  var S;
	  return C === undefined || (S = _anObject(C)[SPECIES$3]) == undefined ? D : _aFunction(S);
	};

	var $min = Math.min;
	var $push = [].push;
	var $SPLIT = 'split';
	var LENGTH = 'length';
	var LAST_INDEX$1 = 'lastIndex';
	var MAX_UINT32 = 0xffffffff; // babel-minify transpiles RegExp('x', 'y') -> /x/y and it causes SyntaxError

	var SUPPORTS_Y = !_fails(function () {
	}); // @@split logic

	_fixReWks('split', 2, function (defined, SPLIT, $split, maybeCallNative) {
	  var internalSplit;

	  if ('abbc'[$SPLIT](/(b)*/)[1] == 'c' || 'test'[$SPLIT](/(?:)/, -1)[LENGTH] != 4 || 'ab'[$SPLIT](/(?:ab)*/)[LENGTH] != 2 || '.'[$SPLIT](/(.?)(.?)/)[LENGTH] != 4 || '.'[$SPLIT](/()()/)[LENGTH] > 1 || ''[$SPLIT](/.?/)[LENGTH]) {
	    // based on es5-shim implementation, need to rework it
	    internalSplit = function internalSplit(separator, limit) {
	      var string = String(this);
	      if (separator === undefined && limit === 0) return []; // If `separator` is not a regex, use native split

	      if (!_isRegexp(separator)) return $split.call(string, separator, limit);
	      var output = [];
	      var flags = (separator.ignoreCase ? 'i' : '') + (separator.multiline ? 'm' : '') + (separator.unicode ? 'u' : '') + (separator.sticky ? 'y' : '');
	      var lastLastIndex = 0;
	      var splitLimit = limit === undefined ? MAX_UINT32 : limit >>> 0; // Make `global` and avoid `lastIndex` issues by working with a copy

	      var separatorCopy = new RegExp(separator.source, flags + 'g');
	      var match, lastIndex, lastLength;

	      while (match = _regexpExec.call(separatorCopy, string)) {
	        lastIndex = separatorCopy[LAST_INDEX$1];

	        if (lastIndex > lastLastIndex) {
	          output.push(string.slice(lastLastIndex, match.index));
	          if (match[LENGTH] > 1 && match.index < string[LENGTH]) $push.apply(output, match.slice(1));
	          lastLength = match[0][LENGTH];
	          lastLastIndex = lastIndex;
	          if (output[LENGTH] >= splitLimit) break;
	        }

	        if (separatorCopy[LAST_INDEX$1] === match.index) separatorCopy[LAST_INDEX$1]++; // Avoid an infinite loop
	      }

	      if (lastLastIndex === string[LENGTH]) {
	        if (lastLength || !separatorCopy.test('')) output.push('');
	      } else output.push(string.slice(lastLastIndex));

	      return output[LENGTH] > splitLimit ? output.slice(0, splitLimit) : output;
	    }; // Chakra, V8

	  } else if ('0'[$SPLIT](undefined, 0)[LENGTH]) {
	    internalSplit = function internalSplit(separator, limit) {
	      return separator === undefined && limit === 0 ? [] : $split.call(this, separator, limit);
	    };
	  } else {
	    internalSplit = $split;
	  }

	  return [// `String.prototype.split` method
	  // https://tc39.github.io/ecma262/#sec-string.prototype.split
	  function split(separator, limit) {
	    var O = defined(this);
	    var splitter = separator == undefined ? undefined : separator[SPLIT];
	    return splitter !== undefined ? splitter.call(separator, O, limit) : internalSplit.call(String(O), separator, limit);
	  }, // `RegExp.prototype[@@split]` method
	  // https://tc39.github.io/ecma262/#sec-regexp.prototype-@@split
	  //
	  // NOTE: This cannot be properly polyfilled in engines that don't support
	  // the 'y' flag.
	  function (regexp, limit) {
	    var res = maybeCallNative(internalSplit, regexp, this, limit, internalSplit !== $split);
	    if (res.done) return res.value;
	    var rx = _anObject(regexp);
	    var S = String(this);
	    var C = _speciesConstructor(rx, RegExp);
	    var unicodeMatching = rx.unicode;
	    var flags = (rx.ignoreCase ? 'i' : '') + (rx.multiline ? 'm' : '') + (rx.unicode ? 'u' : '') + (SUPPORTS_Y ? 'y' : 'g'); // ^(? + rx + ) is needed, in combination with some S slicing, to
	    // simulate the 'y' flag.

	    var splitter = new C(SUPPORTS_Y ? rx : '^(?:' + rx.source + ')', flags);
	    var lim = limit === undefined ? MAX_UINT32 : limit >>> 0;
	    if (lim === 0) return [];
	    if (S.length === 0) return _regexpExecAbstract(splitter, S) === null ? [S] : [];
	    var p = 0;
	    var q = 0;
	    var A = [];

	    while (q < S.length) {
	      splitter.lastIndex = SUPPORTS_Y ? q : 0;
	      var z = _regexpExecAbstract(splitter, SUPPORTS_Y ? S : S.slice(q));
	      var e;

	      if (z === null || (e = $min(_toLength(splitter.lastIndex + (SUPPORTS_Y ? 0 : q)), S.length)) === p) {
	        q = _advanceStringIndex(S, q, unicodeMatching);
	      } else {
	        A.push(S.slice(p, q));
	        if (A.length === lim) return A;

	        for (var i = 1; i <= z.length - 1; i++) {
	          A.push(z[i]);
	          if (A.length === lim) return A;
	        }

	        q = p = e;
	      }
	    }

	    A.push(S.slice(p));
	    return A;
	  }];
	});

	var _anInstance = function (it, Constructor, name, forbiddenField) {
	  if (!(it instanceof Constructor) || forbiddenField !== undefined && forbiddenField in it) {
	    throw TypeError(name + ': incorrect invocation!');
	  }

	  return it;
	};

	var _forOf = createCommonjsModule(function (module) {
	var BREAK = {};
	var RETURN = {};

	var exports = module.exports = function (iterable, entries, fn, that, ITERATOR) {
	  var iterFn = ITERATOR ? function () {
	    return iterable;
	  } : core_getIteratorMethod(iterable);
	  var f = _ctx(fn, that, entries ? 2 : 1);
	  var index = 0;
	  var length, step, iterator, result;
	  if (typeof iterFn != 'function') throw TypeError(iterable + ' is not iterable!'); // fast case for arrays with default iterator

	  if (_isArrayIter(iterFn)) for (length = _toLength(iterable.length); length > index; index++) {
	    result = entries ? f(_anObject(step = iterable[index])[0], step[1]) : f(iterable[index]);
	    if (result === BREAK || result === RETURN) return result;
	  } else for (iterator = iterFn.call(iterable); !(step = iterator.next()).done;) {
	    result = _iterCall(iterator, f, step.value, entries);
	    if (result === BREAK || result === RETURN) return result;
	  }
	};

	exports.BREAK = BREAK;
	exports.RETURN = RETURN;
	});

	var process = _global.process;
	var setTask = _global.setImmediate;
	var clearTask = _global.clearImmediate;
	var MessageChannel = _global.MessageChannel;
	var Dispatch = _global.Dispatch;
	var counter = 0;
	var queue = {};
	var ONREADYSTATECHANGE = 'onreadystatechange';
	var defer, channel, port;

	var run = function run() {
	  var id = +this; // eslint-disable-next-line no-prototype-builtins

	  if (queue.hasOwnProperty(id)) {
	    var fn = queue[id];
	    delete queue[id];
	    fn();
	  }
	};

	var listener = function listener(event) {
	  run.call(event.data);
	}; // Node.js 0.9+ & IE10+ has setImmediate, otherwise:


	if (!setTask || !clearTask) {
	  setTask = function setImmediate(fn) {
	    var args = [];
	    var i = 1;

	    while (arguments.length > i) {
	      args.push(arguments[i++]);
	    }

	    queue[++counter] = function () {
	      // eslint-disable-next-line no-new-func
	      _invoke(typeof fn == 'function' ? fn : Function(fn), args);
	    };

	    defer(counter);
	    return counter;
	  };

	  clearTask = function clearImmediate(id) {
	    delete queue[id];
	  }; // Node.js 0.8-


	  if (_cof(process) == 'process') {
	    defer = function defer(id) {
	      process.nextTick(_ctx(run, id, 1));
	    }; // Sphere (JS game engine) Dispatch API

	  } else if (Dispatch && Dispatch.now) {
	    defer = function defer(id) {
	      Dispatch.now(_ctx(run, id, 1));
	    }; // Browsers with MessageChannel, includes WebWorkers

	  } else if (MessageChannel) {
	    channel = new MessageChannel();
	    port = channel.port2;
	    channel.port1.onmessage = listener;
	    defer = _ctx(port.postMessage, port, 1); // Browsers with postMessage, skip WebWorkers
	    // IE8 has postMessage, but it's sync & typeof its postMessage is 'object'
	  } else if (_global.addEventListener && typeof postMessage == 'function' && !_global.importScripts) {
	    defer = function defer(id) {
	      _global.postMessage(id + '', '*');
	    };

	    _global.addEventListener('message', listener, false); // IE8-
	  } else if (ONREADYSTATECHANGE in _domCreate('script')) {
	    defer = function defer(id) {
	      _html.appendChild(_domCreate('script'))[ONREADYSTATECHANGE] = function () {
	        _html.removeChild(this);
	        run.call(id);
	      };
	    }; // Rest old browsers

	  } else {
	    defer = function defer(id) {
	      setTimeout(_ctx(run, id, 1), 0);
	    };
	  }
	}

	var _task = {
	  set: setTask,
	  clear: clearTask
	};

	var macrotask = _task.set;

	var Observer = _global.MutationObserver || _global.WebKitMutationObserver;
	var process$1 = _global.process;
	var Promise = _global.Promise;
	var isNode = _cof(process$1) == 'process';

	var _microtask = function () {
	  var head, last, notify;

	  var flush = function flush() {
	    var parent, fn;
	    if (isNode && (parent = process$1.domain)) parent.exit();

	    while (head) {
	      fn = head.fn;
	      head = head.next;

	      try {
	        fn();
	      } catch (e) {
	        if (head) notify();else last = undefined;
	        throw e;
	      }
	    }

	    last = undefined;
	    if (parent) parent.enter();
	  }; // Node.js


	  if (isNode) {
	    notify = function notify() {
	      process$1.nextTick(flush);
	    }; // browsers with MutationObserver, except iOS Safari - https://github.com/zloirock/core-js/issues/339

	  } else if (Observer && !(_global.navigator && _global.navigator.standalone)) {
	    var toggle = true;
	    var node = document.createTextNode('');
	    new Observer(flush).observe(node, {
	      characterData: true
	    }); // eslint-disable-line no-new

	    notify = function notify() {
	      node.data = toggle = !toggle;
	    }; // environments with maybe non-completely correct, but existent Promise

	  } else if (Promise && Promise.resolve) {
	    // Promise.resolve without an argument throws an error in LG WebOS 2
	    var promise = Promise.resolve(undefined);

	    notify = function notify() {
	      promise.then(flush);
	    }; // for other environments - macrotask based on:
	    // - setImmediate
	    // - MessageChannel
	    // - window.postMessag
	    // - onreadystatechange
	    // - setTimeout

	  } else {
	    notify = function notify() {
	      // strange IE + webpack dev server bug - use .call(global)
	      macrotask.call(_global, flush);
	    };
	  }

	  return function (fn) {
	    var task = {
	      fn: fn,
	      next: undefined
	    };
	    if (last) last.next = task;

	    if (!head) {
	      head = task;
	      notify();
	    }

	    last = task;
	  };
	};

	function PromiseCapability(C) {
	  var resolve, reject;
	  this.promise = new C(function ($$resolve, $$reject) {
	    if (resolve !== undefined || reject !== undefined) throw TypeError('Bad Promise constructor');
	    resolve = $$resolve;
	    reject = $$reject;
	  });
	  this.resolve = _aFunction(resolve);
	  this.reject = _aFunction(reject);
	}

	var f$7 = function (C) {
	  return new PromiseCapability(C);
	};

	var _newPromiseCapability = {
		f: f$7
	};

	var _perform = function (exec) {
	  try {
	    return {
	      e: false,
	      v: exec()
	    };
	  } catch (e) {
	    return {
	      e: true,
	      v: e
	    };
	  }
	};

	var navigator = _global.navigator;
	var _userAgent = navigator && navigator.userAgent || '';

	var _promiseResolve = function (C, x) {
	  _anObject(C);
	  if (_isObject(x) && x.constructor === C) return x;
	  var promiseCapability = _newPromiseCapability.f(C);
	  var resolve = promiseCapability.resolve;
	  resolve(x);
	  return promiseCapability.promise;
	};

	var _redefineAll = function (target, src, safe) {
	  for (var key in src) {
	    _redefine(target, key, src[key], safe);
	  }

	  return target;
	};

	var task = _task.set;

	var microtask = _microtask();









	var PROMISE = 'Promise';
	var TypeError$1 = _global.TypeError;
	var process$2 = _global.process;
	var versions = process$2 && process$2.versions;
	var v8 = versions && versions.v8 || '';
	var $Promise = _global[PROMISE];
	var isNode$1 = _classof(process$2) == 'process';

	var empty = function empty() {
	  /* empty */
	};

	var Internal, newGenericPromiseCapability, OwnPromiseCapability, Wrapper;
	var newPromiseCapability = newGenericPromiseCapability = _newPromiseCapability.f;
	var USE_NATIVE$1 = !!function () {
	  try {
	    // correct subclassing with @@species support
	    var promise = $Promise.resolve(1);

	    var FakePromise = (promise.constructor = {})[_wks('species')] = function (exec) {
	      exec(empty, empty);
	    }; // unhandled rejections tracking support, NodeJS Promise without it fails @@species test


	    return (isNode$1 || typeof PromiseRejectionEvent == 'function') && promise.then(empty) instanceof FakePromise // v8 6.6 (Node 10 and Chrome 66) have a bug with resolving custom thenables
	    // https://bugs.chromium.org/p/chromium/issues/detail?id=830565
	    // we can't detect it synchronously, so just check versions
	    && v8.indexOf('6.6') !== 0 && _userAgent.indexOf('Chrome/66') === -1;
	  } catch (e) {
	    /* empty */
	  }
	}(); // helpers

	var isThenable = function isThenable(it) {
	  var then;
	  return _isObject(it) && typeof (then = it.then) == 'function' ? then : false;
	};

	var notify = function notify(promise, isReject) {
	  if (promise._n) return;
	  promise._n = true;
	  var chain = promise._c;
	  microtask(function () {
	    var value = promise._v;
	    var ok = promise._s == 1;
	    var i = 0;

	    var run = function run(reaction) {
	      var handler = ok ? reaction.ok : reaction.fail;
	      var resolve = reaction.resolve;
	      var reject = reaction.reject;
	      var domain = reaction.domain;
	      var result, then, exited;

	      try {
	        if (handler) {
	          if (!ok) {
	            if (promise._h == 2) onHandleUnhandled(promise);
	            promise._h = 1;
	          }

	          if (handler === true) result = value;else {
	            if (domain) domain.enter();
	            result = handler(value); // may throw

	            if (domain) {
	              domain.exit();
	              exited = true;
	            }
	          }

	          if (result === reaction.promise) {
	            reject(TypeError$1('Promise-chain cycle'));
	          } else if (then = isThenable(result)) {
	            then.call(result, resolve, reject);
	          } else resolve(result);
	        } else reject(value);
	      } catch (e) {
	        if (domain && !exited) domain.exit();
	        reject(e);
	      }
	    };

	    while (chain.length > i) {
	      run(chain[i++]);
	    } // variable length - can't use forEach


	    promise._c = [];
	    promise._n = false;
	    if (isReject && !promise._h) onUnhandled(promise);
	  });
	};

	var onUnhandled = function onUnhandled(promise) {
	  task.call(_global, function () {
	    var value = promise._v;
	    var unhandled = isUnhandled(promise);
	    var result, handler, console;

	    if (unhandled) {
	      result = _perform(function () {
	        if (isNode$1) {
	          process$2.emit('unhandledRejection', value, promise);
	        } else if (handler = _global.onunhandledrejection) {
	          handler({
	            promise: promise,
	            reason: value
	          });
	        } else if ((console = _global.console) && console.error) {
	          console.error('Unhandled promise rejection', value);
	        }
	      }); // Browsers should not trigger `rejectionHandled` event if it was handled here, NodeJS - should

	      promise._h = isNode$1 || isUnhandled(promise) ? 2 : 1;
	    }

	    promise._a = undefined;
	    if (unhandled && result.e) throw result.v;
	  });
	};

	var isUnhandled = function isUnhandled(promise) {
	  return promise._h !== 1 && (promise._a || promise._c).length === 0;
	};

	var onHandleUnhandled = function onHandleUnhandled(promise) {
	  task.call(_global, function () {
	    var handler;

	    if (isNode$1) {
	      process$2.emit('rejectionHandled', promise);
	    } else if (handler = _global.onrejectionhandled) {
	      handler({
	        promise: promise,
	        reason: promise._v
	      });
	    }
	  });
	};

	var $reject = function $reject(value) {
	  var promise = this;
	  if (promise._d) return;
	  promise._d = true;
	  promise = promise._w || promise; // unwrap

	  promise._v = value;
	  promise._s = 2;
	  if (!promise._a) promise._a = promise._c.slice();
	  notify(promise, true);
	};

	var $resolve = function $resolve(value) {
	  var promise = this;
	  var then;
	  if (promise._d) return;
	  promise._d = true;
	  promise = promise._w || promise; // unwrap

	  try {
	    if (promise === value) throw TypeError$1("Promise can't be resolved itself");

	    if (then = isThenable(value)) {
	      microtask(function () {
	        var wrapper = {
	          _w: promise,
	          _d: false
	        }; // wrap

	        try {
	          then.call(value, _ctx($resolve, wrapper, 1), _ctx($reject, wrapper, 1));
	        } catch (e) {
	          $reject.call(wrapper, e);
	        }
	      });
	    } else {
	      promise._v = value;
	      promise._s = 1;
	      notify(promise, false);
	    }
	  } catch (e) {
	    $reject.call({
	      _w: promise,
	      _d: false
	    }, e); // wrap
	  }
	}; // constructor polyfill


	if (!USE_NATIVE$1) {
	  // 25.4.3.1 Promise(executor)
	  $Promise = function Promise(executor) {
	    _anInstance(this, $Promise, PROMISE, '_h');
	    _aFunction(executor);
	    Internal.call(this);

	    try {
	      executor(_ctx($resolve, this, 1), _ctx($reject, this, 1));
	    } catch (err) {
	      $reject.call(this, err);
	    }
	  }; // eslint-disable-next-line no-unused-vars


	  Internal = function Promise(executor) {
	    this._c = []; // <- awaiting reactions

	    this._a = undefined; // <- checked in isUnhandled reactions

	    this._s = 0; // <- state

	    this._d = false; // <- done

	    this._v = undefined; // <- value

	    this._h = 0; // <- rejection state, 0 - default, 1 - handled, 2 - unhandled

	    this._n = false; // <- notify
	  };

	  Internal.prototype = _redefineAll($Promise.prototype, {
	    // 25.4.5.3 Promise.prototype.then(onFulfilled, onRejected)
	    then: function then(onFulfilled, onRejected) {
	      var reaction = newPromiseCapability(_speciesConstructor(this, $Promise));
	      reaction.ok = typeof onFulfilled == 'function' ? onFulfilled : true;
	      reaction.fail = typeof onRejected == 'function' && onRejected;
	      reaction.domain = isNode$1 ? process$2.domain : undefined;

	      this._c.push(reaction);

	      if (this._a) this._a.push(reaction);
	      if (this._s) notify(this, false);
	      return reaction.promise;
	    },
	    // 25.4.5.1 Promise.prototype.catch(onRejected)
	    'catch': function _catch(onRejected) {
	      return this.then(undefined, onRejected);
	    }
	  });

	  OwnPromiseCapability = function OwnPromiseCapability() {
	    var promise = new Internal();
	    this.promise = promise;
	    this.resolve = _ctx($resolve, promise, 1);
	    this.reject = _ctx($reject, promise, 1);
	  };

	  _newPromiseCapability.f = newPromiseCapability = function newPromiseCapability(C) {
	    return C === $Promise || C === Wrapper ? new OwnPromiseCapability(C) : newGenericPromiseCapability(C);
	  };
	}

	_export(_export.G + _export.W + _export.F * !USE_NATIVE$1, {
	  Promise: $Promise
	});

	_setToStringTag($Promise, PROMISE);

	_setSpecies(PROMISE);

	Wrapper = _core[PROMISE]; // statics

	_export(_export.S + _export.F * !USE_NATIVE$1, PROMISE, {
	  // 25.4.4.5 Promise.reject(r)
	  reject: function reject(r) {
	    var capability = newPromiseCapability(this);
	    var $$reject = capability.reject;
	    $$reject(r);
	    return capability.promise;
	  }
	});
	_export(_export.S + _export.F * (_library || !USE_NATIVE$1), PROMISE, {
	  // 25.4.4.6 Promise.resolve(x)
	  resolve: function resolve(x) {
	    return _promiseResolve(_library && this === Wrapper ? $Promise : this, x);
	  }
	});
	_export(_export.S + _export.F * !(USE_NATIVE$1 && _iterDetect(function (iter) {
	  $Promise.all(iter)['catch'](empty);
	})), PROMISE, {
	  // 25.4.4.1 Promise.all(iterable)
	  all: function all(iterable) {
	    var C = this;
	    var capability = newPromiseCapability(C);
	    var resolve = capability.resolve;
	    var reject = capability.reject;
	    var result = _perform(function () {
	      var values = [];
	      var index = 0;
	      var remaining = 1;
	      _forOf(iterable, false, function (promise) {
	        var $index = index++;
	        var alreadyCalled = false;
	        values.push(undefined);
	        remaining++;
	        C.resolve(promise).then(function (value) {
	          if (alreadyCalled) return;
	          alreadyCalled = true;
	          values[$index] = value;
	          --remaining || resolve(values);
	        }, reject);
	      });
	      --remaining || resolve(values);
	    });
	    if (result.e) reject(result.v);
	    return capability.promise;
	  },
	  // 25.4.4.4 Promise.race(iterable)
	  race: function race(iterable) {
	    var C = this;
	    var capability = newPromiseCapability(C);
	    var reject = capability.reject;
	    var result = _perform(function () {
	      _forOf(iterable, false, function (promise) {
	        C.resolve(promise).then(capability.resolve, reject);
	      });
	    });
	    if (result.e) reject(result.v);
	    return capability.promise;
	  }
	});

	var _validateCollection = function (it, TYPE) {
	  if (!_isObject(it) || it._t !== TYPE) throw TypeError('Incompatible receiver, ' + TYPE + ' required!');
	  return it;
	};

	var dP$5 = _objectDp.f;



















	var fastKey = _meta.fastKey;



	var SIZE = _descriptors ? '_s' : 'size';

	var getEntry = function getEntry(that, key) {
	  // fast case
	  var index = fastKey(key);
	  var entry;
	  if (index !== 'F') return that._i[index]; // frozen object case

	  for (entry = that._f; entry; entry = entry.n) {
	    if (entry.k == key) return entry;
	  }
	};

	var _collectionStrong = {
	  getConstructor: function getConstructor(wrapper, NAME, IS_MAP, ADDER) {
	    var C = wrapper(function (that, iterable) {
	      _anInstance(that, C, NAME, '_i');
	      that._t = NAME; // collection type

	      that._i = _objectCreate(null); // index

	      that._f = undefined; // first entry

	      that._l = undefined; // last entry

	      that[SIZE] = 0; // size

	      if (iterable != undefined) _forOf(iterable, IS_MAP, that[ADDER], that);
	    });
	    _redefineAll(C.prototype, {
	      // 23.1.3.1 Map.prototype.clear()
	      // 23.2.3.2 Set.prototype.clear()
	      clear: function clear() {
	        for (var that = _validateCollection(this, NAME), data = that._i, entry = that._f; entry; entry = entry.n) {
	          entry.r = true;
	          if (entry.p) entry.p = entry.p.n = undefined;
	          delete data[entry.i];
	        }

	        that._f = that._l = undefined;
	        that[SIZE] = 0;
	      },
	      // 23.1.3.3 Map.prototype.delete(key)
	      // 23.2.3.4 Set.prototype.delete(value)
	      'delete': function _delete(key) {
	        var that = _validateCollection(this, NAME);
	        var entry = getEntry(that, key);

	        if (entry) {
	          var next = entry.n;
	          var prev = entry.p;
	          delete that._i[entry.i];
	          entry.r = true;
	          if (prev) prev.n = next;
	          if (next) next.p = prev;
	          if (that._f == entry) that._f = next;
	          if (that._l == entry) that._l = prev;
	          that[SIZE]--;
	        }

	        return !!entry;
	      },
	      // 23.2.3.6 Set.prototype.forEach(callbackfn, thisArg = undefined)
	      // 23.1.3.5 Map.prototype.forEach(callbackfn, thisArg = undefined)
	      forEach: function forEach(callbackfn
	      /* , that = undefined */
	      ) {
	        _validateCollection(this, NAME);
	        var f = _ctx(callbackfn, arguments.length > 1 ? arguments[1] : undefined, 3);
	        var entry;

	        while (entry = entry ? entry.n : this._f) {
	          f(entry.v, entry.k, this); // revert to the last existing entry

	          while (entry && entry.r) {
	            entry = entry.p;
	          }
	        }
	      },
	      // 23.1.3.7 Map.prototype.has(key)
	      // 23.2.3.7 Set.prototype.has(value)
	      has: function has(key) {
	        return !!getEntry(_validateCollection(this, NAME), key);
	      }
	    });
	    if (_descriptors) dP$5(C.prototype, 'size', {
	      get: function get() {
	        return _validateCollection(this, NAME)[SIZE];
	      }
	    });
	    return C;
	  },
	  def: function def(that, key, value) {
	    var entry = getEntry(that, key);
	    var prev, index; // change existing entry

	    if (entry) {
	      entry.v = value; // create new entry
	    } else {
	      that._l = entry = {
	        i: index = fastKey(key, true),
	        // <- index
	        k: key,
	        // <- key
	        v: value,
	        // <- value
	        p: prev = that._l,
	        // <- previous entry
	        n: undefined,
	        // <- next entry
	        r: false // <- removed

	      };
	      if (!that._f) that._f = entry;
	      if (prev) prev.n = entry;
	      that[SIZE]++; // add to index

	      if (index !== 'F') that._i[index] = entry;
	    }

	    return that;
	  },
	  getEntry: getEntry,
	  setStrong: function setStrong(C, NAME, IS_MAP) {
	    // add .keys, .values, .entries, [@@iterator]
	    // 23.1.3.4, 23.1.3.8, 23.1.3.11, 23.1.3.12, 23.2.3.5, 23.2.3.8, 23.2.3.10, 23.2.3.11
	    _iterDefine(C, NAME, function (iterated, kind) {
	      this._t = _validateCollection(iterated, NAME); // target

	      this._k = kind; // kind

	      this._l = undefined; // previous
	    }, function () {
	      var that = this;
	      var kind = that._k;
	      var entry = that._l; // revert to the last existing entry

	      while (entry && entry.r) {
	        entry = entry.p;
	      } // get next entry


	      if (!that._t || !(that._l = entry = entry ? entry.n : that._t._f)) {
	        // or finish the iteration
	        that._t = undefined;
	        return _iterStep(1);
	      } // return step by kind


	      if (kind == 'keys') return _iterStep(0, entry.k);
	      if (kind == 'values') return _iterStep(0, entry.v);
	      return _iterStep(0, [entry.k, entry.v]);
	    }, IS_MAP ? 'entries' : 'values', !IS_MAP, true); // add [@@species], 23.1.2.2, 23.2.2.2

	    _setSpecies(NAME);
	  }
	};

	var _collection = function (NAME, wrapper, methods, common, IS_MAP, IS_WEAK) {
	  var Base = _global[NAME];
	  var C = Base;
	  var ADDER = IS_MAP ? 'set' : 'add';
	  var proto = C && C.prototype;
	  var O = {};

	  var fixMethod = function fixMethod(KEY) {
	    var fn = proto[KEY];
	    _redefine(proto, KEY, KEY == 'delete' ? function (a) {
	      return IS_WEAK && !_isObject(a) ? false : fn.call(this, a === 0 ? 0 : a);
	    } : KEY == 'has' ? function has(a) {
	      return IS_WEAK && !_isObject(a) ? false : fn.call(this, a === 0 ? 0 : a);
	    } : KEY == 'get' ? function get(a) {
	      return IS_WEAK && !_isObject(a) ? undefined : fn.call(this, a === 0 ? 0 : a);
	    } : KEY == 'add' ? function add(a) {
	      fn.call(this, a === 0 ? 0 : a);
	      return this;
	    } : function set(a, b) {
	      fn.call(this, a === 0 ? 0 : a, b);
	      return this;
	    });
	  };

	  if (typeof C != 'function' || !(IS_WEAK || proto.forEach && !_fails(function () {
	    new C().entries().next();
	  }))) {
	    // create collection constructor
	    C = common.getConstructor(wrapper, NAME, IS_MAP, ADDER);
	    _redefineAll(C.prototype, methods);
	    _meta.NEED = true;
	  } else {
	    var instance = new C(); // early implementations not supports chaining

	    var HASNT_CHAINING = instance[ADDER](IS_WEAK ? {} : -0, 1) != instance; // V8 ~  Chromium 40- weak-collections throws on primitives, but should return false

	    var THROWS_ON_PRIMITIVES = _fails(function () {
	      instance.has(1);
	    }); // most early implementations doesn't supports iterables, most modern - not close it correctly

	    var ACCEPT_ITERABLES = _iterDetect(function (iter) {
	      new C(iter);
	    }); // eslint-disable-line no-new
	    // for early implementations -0 and +0 not the same

	    var BUGGY_ZERO = !IS_WEAK && _fails(function () {
	      // V8 ~ Chromium 42- fails only with 5+ elements
	      var $instance = new C();
	      var index = 5;

	      while (index--) {
	        $instance[ADDER](index, index);
	      }

	      return !$instance.has(-0);
	    });

	    if (!ACCEPT_ITERABLES) {
	      C = wrapper(function (target, iterable) {
	        _anInstance(target, C, NAME);
	        var that = _inheritIfRequired(new Base(), target, C);
	        if (iterable != undefined) _forOf(iterable, IS_MAP, that[ADDER], that);
	        return that;
	      });
	      C.prototype = proto;
	      proto.constructor = C;
	    }

	    if (THROWS_ON_PRIMITIVES || BUGGY_ZERO) {
	      fixMethod('delete');
	      fixMethod('has');
	      IS_MAP && fixMethod('get');
	    }

	    if (BUGGY_ZERO || HASNT_CHAINING) fixMethod(ADDER); // weak collections should not contains .clear method

	    if (IS_WEAK && proto.clear) delete proto.clear;
	  }

	  _setToStringTag(C, NAME);
	  O[NAME] = C;
	  _export(_export.G + _export.W + _export.F * (C != Base), O);
	  if (!IS_WEAK) common.setStrong(C, NAME, IS_MAP);
	  return C;
	};

	var MAP = 'Map'; // 23.1 Map Objects

	var es6_map = _collection(MAP, function (get) {
	  return function Map() {
	    return get(this, arguments.length > 0 ? arguments[0] : undefined);
	  };
	}, {
	  // 23.1.3.6 Map.prototype.get(key)
	  get: function get(key) {
	    var entry = _collectionStrong.getEntry(_validateCollection(this, MAP), key);
	    return entry && entry.v;
	  },
	  // 23.1.3.9 Map.prototype.set(key, value)
	  set: function set(key, value) {
	    return _collectionStrong.def(_validateCollection(this, MAP), key === 0 ? 0 : key, value);
	  }
	}, _collectionStrong, true);

	var SET = 'Set'; // 23.2 Set Objects

	var es6_set = _collection(SET, function (get) {
	  return function Set() {
	    return get(this, arguments.length > 0 ? arguments[0] : undefined);
	  };
	}, {
	  // 23.2.3.1 Set.prototype.add(value)
	  add: function add(value) {
	    return _collectionStrong.def(_validateCollection(this, SET), value = value === 0 ? 0 : value, value);
	  }
	}, _collectionStrong);

	var getWeak = _meta.getWeak;















	var arrayFind = _arrayMethods(5);
	var arrayFindIndex = _arrayMethods(6);
	var id$1 = 0; // fallback for uncaught frozen keys

	var uncaughtFrozenStore = function uncaughtFrozenStore(that) {
	  return that._l || (that._l = new UncaughtFrozenStore());
	};

	var UncaughtFrozenStore = function UncaughtFrozenStore() {
	  this.a = [];
	};

	var findUncaughtFrozen = function findUncaughtFrozen(store, key) {
	  return arrayFind(store.a, function (it) {
	    return it[0] === key;
	  });
	};

	UncaughtFrozenStore.prototype = {
	  get: function get(key) {
	    var entry = findUncaughtFrozen(this, key);
	    if (entry) return entry[1];
	  },
	  has: function has(key) {
	    return !!findUncaughtFrozen(this, key);
	  },
	  set: function set(key, value) {
	    var entry = findUncaughtFrozen(this, key);
	    if (entry) entry[1] = value;else this.a.push([key, value]);
	  },
	  'delete': function _delete(key) {
	    var index = arrayFindIndex(this.a, function (it) {
	      return it[0] === key;
	    });
	    if (~index) this.a.splice(index, 1);
	    return !!~index;
	  }
	};
	var _collectionWeak = {
	  getConstructor: function getConstructor(wrapper, NAME, IS_MAP, ADDER) {
	    var C = wrapper(function (that, iterable) {
	      _anInstance(that, C, NAME, '_i');
	      that._t = NAME; // collection type

	      that._i = id$1++; // collection id

	      that._l = undefined; // leak store for uncaught frozen objects

	      if (iterable != undefined) _forOf(iterable, IS_MAP, that[ADDER], that);
	    });
	    _redefineAll(C.prototype, {
	      // 23.3.3.2 WeakMap.prototype.delete(key)
	      // 23.4.3.3 WeakSet.prototype.delete(value)
	      'delete': function _delete(key) {
	        if (!_isObject(key)) return false;
	        var data = getWeak(key);
	        if (data === true) return uncaughtFrozenStore(_validateCollection(this, NAME))['delete'](key);
	        return data && _has(data, this._i) && delete data[this._i];
	      },
	      // 23.3.3.4 WeakMap.prototype.has(key)
	      // 23.4.3.4 WeakSet.prototype.has(value)
	      has: function has(key) {
	        if (!_isObject(key)) return false;
	        var data = getWeak(key);
	        if (data === true) return uncaughtFrozenStore(_validateCollection(this, NAME)).has(key);
	        return data && _has(data, this._i);
	      }
	    });
	    return C;
	  },
	  def: function def(that, key, value) {
	    var data = getWeak(_anObject(key), true);
	    if (data === true) uncaughtFrozenStore(that).set(key, value);else data[that._i] = value;
	    return that;
	  },
	  ufstore: uncaughtFrozenStore
	};

	var es6_weakMap = createCommonjsModule(function (module) {

	var each = _arrayMethods(0);















	var WEAK_MAP = 'WeakMap';
	var getWeak = _meta.getWeak;
	var isExtensible = Object.isExtensible;
	var uncaughtFrozenStore = _collectionWeak.ufstore;
	var tmp = {};
	var InternalMap;

	var wrapper = function wrapper(get) {
	  return function WeakMap() {
	    return get(this, arguments.length > 0 ? arguments[0] : undefined);
	  };
	};

	var methods = {
	  // 23.3.3.3 WeakMap.prototype.get(key)
	  get: function get(key) {
	    if (_isObject(key)) {
	      var data = getWeak(key);
	      if (data === true) return uncaughtFrozenStore(_validateCollection(this, WEAK_MAP)).get(key);
	      return data ? data[this._i] : undefined;
	    }
	  },
	  // 23.3.3.5 WeakMap.prototype.set(key, value)
	  set: function set(key, value) {
	    return _collectionWeak.def(_validateCollection(this, WEAK_MAP), key, value);
	  }
	}; // 23.3 WeakMap Objects

	var $WeakMap = module.exports = _collection(WEAK_MAP, wrapper, methods, _collectionWeak, true, true); // IE11 WeakMap frozen keys fix


	if (_fails(function () {
	  return new $WeakMap().set((Object.freeze || Object)(tmp), 7).get(tmp) != 7;
	})) {
	  InternalMap = _collectionWeak.getConstructor(wrapper, WEAK_MAP);
	  _objectAssign(InternalMap.prototype, methods);
	  _meta.NEED = true;
	  each(['delete', 'has', 'get', 'set'], function (key) {
	    var proto = $WeakMap.prototype;
	    var method = proto[key];
	    _redefine(proto, key, function (a, b) {
	      // store frozen objects on internal weakmap shim
	      if (_isObject(a) && !isExtensible(a)) {
	        if (!this._f) this._f = new InternalMap();

	        var result = this._f[key](a, b);

	        return key == 'set' ? this : result; // store all the rest on native weakmap
	      }

	      return method.call(this, a, b);
	    });
	  });
	}
	});

	var WEAK_SET = 'WeakSet'; // 23.4 WeakSet Objects

	_collection(WEAK_SET, function (get) {
	  return function WeakSet() {
	    return get(this, arguments.length > 0 ? arguments[0] : undefined);
	  };
	}, {
	  // 23.4.3.1 WeakSet.prototype.add(value)
	  add: function add(value) {
	    return _collectionWeak.def(_validateCollection(this, WEAK_SET), value, true);
	  }
	}, _collectionWeak, false, true);

	var TYPED = _uid('typed_array');
	var VIEW = _uid('view');
	var ABV = !!(_global.ArrayBuffer && _global.DataView);
	var CONSTR = ABV;
	var i$1 = 0;
	var l = 9;
	var Typed;
	var TypedArrayConstructors = 'Int8Array,Uint8Array,Uint8ClampedArray,Int16Array,Uint16Array,Int32Array,Uint32Array,Float32Array,Float64Array'.split(',');

	while (i$1 < l) {
	  if (Typed = _global[TypedArrayConstructors[i$1++]]) {
	    _hide(Typed.prototype, TYPED, true);
	    _hide(Typed.prototype, VIEW, true);
	  } else CONSTR = false;
	}

	var _typed = {
	  ABV: ABV,
	  CONSTR: CONSTR,
	  TYPED: TYPED,
	  VIEW: VIEW
	};

	// https://tc39.github.io/ecma262/#sec-toindex




	var _toIndex = function (it) {
	  if (it === undefined) return 0;
	  var number = _toInteger(it);
	  var length = _toLength(number);
	  if (number !== length) throw RangeError('Wrong length!');
	  return length;
	};

	var _typedBuffer = createCommonjsModule(function (module, exports) {























	var gOPN = _objectGopn.f;

	var dP = _objectDp.f;





	var ARRAY_BUFFER = 'ArrayBuffer';
	var DATA_VIEW = 'DataView';
	var PROTOTYPE = 'prototype';
	var WRONG_LENGTH = 'Wrong length!';
	var WRONG_INDEX = 'Wrong index!';
	var $ArrayBuffer = _global[ARRAY_BUFFER];
	var $DataView = _global[DATA_VIEW];
	var Math = _global.Math;
	var RangeError = _global.RangeError; // eslint-disable-next-line no-shadow-restricted-names

	var Infinity = _global.Infinity;
	var BaseBuffer = $ArrayBuffer;
	var abs = Math.abs;
	var pow = Math.pow;
	var floor = Math.floor;
	var log = Math.log;
	var LN2 = Math.LN2;
	var BUFFER = 'buffer';
	var BYTE_LENGTH = 'byteLength';
	var BYTE_OFFSET = 'byteOffset';
	var $BUFFER = _descriptors ? '_b' : BUFFER;
	var $LENGTH = _descriptors ? '_l' : BYTE_LENGTH;
	var $OFFSET = _descriptors ? '_o' : BYTE_OFFSET; // IEEE754 conversions based on https://github.com/feross/ieee754

	function packIEEE754(value, mLen, nBytes) {
	  var buffer = new Array(nBytes);
	  var eLen = nBytes * 8 - mLen - 1;
	  var eMax = (1 << eLen) - 1;
	  var eBias = eMax >> 1;
	  var rt = mLen === 23 ? pow(2, -24) - pow(2, -77) : 0;
	  var i = 0;
	  var s = value < 0 || value === 0 && 1 / value < 0 ? 1 : 0;
	  var e, m, c;
	  value = abs(value); // eslint-disable-next-line no-self-compare

	  if (value != value || value === Infinity) {
	    // eslint-disable-next-line no-self-compare
	    m = value != value ? 1 : 0;
	    e = eMax;
	  } else {
	    e = floor(log(value) / LN2);

	    if (value * (c = pow(2, -e)) < 1) {
	      e--;
	      c *= 2;
	    }

	    if (e + eBias >= 1) {
	      value += rt / c;
	    } else {
	      value += rt * pow(2, 1 - eBias);
	    }

	    if (value * c >= 2) {
	      e++;
	      c /= 2;
	    }

	    if (e + eBias >= eMax) {
	      m = 0;
	      e = eMax;
	    } else if (e + eBias >= 1) {
	      m = (value * c - 1) * pow(2, mLen);
	      e = e + eBias;
	    } else {
	      m = value * pow(2, eBias - 1) * pow(2, mLen);
	      e = 0;
	    }
	  }

	  for (; mLen >= 8; buffer[i++] = m & 255, m /= 256, mLen -= 8) {
	  }

	  e = e << mLen | m;
	  eLen += mLen;

	  for (; eLen > 0; buffer[i++] = e & 255, e /= 256, eLen -= 8) {
	  }

	  buffer[--i] |= s * 128;
	  return buffer;
	}

	function unpackIEEE754(buffer, mLen, nBytes) {
	  var eLen = nBytes * 8 - mLen - 1;
	  var eMax = (1 << eLen) - 1;
	  var eBias = eMax >> 1;
	  var nBits = eLen - 7;
	  var i = nBytes - 1;
	  var s = buffer[i--];
	  var e = s & 127;
	  var m;
	  s >>= 7;

	  for (; nBits > 0; e = e * 256 + buffer[i], i--, nBits -= 8) {
	  }

	  m = e & (1 << -nBits) - 1;
	  e >>= -nBits;
	  nBits += mLen;

	  for (; nBits > 0; m = m * 256 + buffer[i], i--, nBits -= 8) {
	  }

	  if (e === 0) {
	    e = 1 - eBias;
	  } else if (e === eMax) {
	    return m ? NaN : s ? -Infinity : Infinity;
	  } else {
	    m = m + pow(2, mLen);
	    e = e - eBias;
	  }

	  return (s ? -1 : 1) * m * pow(2, e - mLen);
	}

	function unpackI32(bytes) {
	  return bytes[3] << 24 | bytes[2] << 16 | bytes[1] << 8 | bytes[0];
	}

	function packI8(it) {
	  return [it & 0xff];
	}

	function packI16(it) {
	  return [it & 0xff, it >> 8 & 0xff];
	}

	function packI32(it) {
	  return [it & 0xff, it >> 8 & 0xff, it >> 16 & 0xff, it >> 24 & 0xff];
	}

	function packF64(it) {
	  return packIEEE754(it, 52, 8);
	}

	function packF32(it) {
	  return packIEEE754(it, 23, 4);
	}

	function addGetter(C, key, internal) {
	  dP(C[PROTOTYPE], key, {
	    get: function get() {
	      return this[internal];
	    }
	  });
	}

	function get(view, bytes, index, isLittleEndian) {
	  var numIndex = +index;
	  var intIndex = _toIndex(numIndex);
	  if (intIndex + bytes > view[$LENGTH]) throw RangeError(WRONG_INDEX);
	  var store = view[$BUFFER]._b;
	  var start = intIndex + view[$OFFSET];
	  var pack = store.slice(start, start + bytes);
	  return isLittleEndian ? pack : pack.reverse();
	}

	function set(view, bytes, index, conversion, value, isLittleEndian) {
	  var numIndex = +index;
	  var intIndex = _toIndex(numIndex);
	  if (intIndex + bytes > view[$LENGTH]) throw RangeError(WRONG_INDEX);
	  var store = view[$BUFFER]._b;
	  var start = intIndex + view[$OFFSET];
	  var pack = conversion(+value);

	  for (var i = 0; i < bytes; i++) {
	    store[start + i] = pack[isLittleEndian ? i : bytes - i - 1];
	  }
	}

	if (!_typed.ABV) {
	  $ArrayBuffer = function ArrayBuffer(length) {
	    _anInstance(this, $ArrayBuffer, ARRAY_BUFFER);
	    var byteLength = _toIndex(length);
	    this._b = _arrayFill.call(new Array(byteLength), 0);
	    this[$LENGTH] = byteLength;
	  };

	  $DataView = function DataView(buffer, byteOffset, byteLength) {
	    _anInstance(this, $DataView, DATA_VIEW);
	    _anInstance(buffer, $ArrayBuffer, DATA_VIEW);
	    var bufferLength = buffer[$LENGTH];
	    var offset = _toInteger(byteOffset);
	    if (offset < 0 || offset > bufferLength) throw RangeError('Wrong offset!');
	    byteLength = byteLength === undefined ? bufferLength - offset : _toLength(byteLength);
	    if (offset + byteLength > bufferLength) throw RangeError(WRONG_LENGTH);
	    this[$BUFFER] = buffer;
	    this[$OFFSET] = offset;
	    this[$LENGTH] = byteLength;
	  };

	  if (_descriptors) {
	    addGetter($ArrayBuffer, BYTE_LENGTH, '_l');
	    addGetter($DataView, BUFFER, '_b');
	    addGetter($DataView, BYTE_LENGTH, '_l');
	    addGetter($DataView, BYTE_OFFSET, '_o');
	  }

	  _redefineAll($DataView[PROTOTYPE], {
	    getInt8: function getInt8(byteOffset) {
	      return get(this, 1, byteOffset)[0] << 24 >> 24;
	    },
	    getUint8: function getUint8(byteOffset) {
	      return get(this, 1, byteOffset)[0];
	    },
	    getInt16: function getInt16(byteOffset
	    /* , littleEndian */
	    ) {
	      var bytes = get(this, 2, byteOffset, arguments[1]);
	      return (bytes[1] << 8 | bytes[0]) << 16 >> 16;
	    },
	    getUint16: function getUint16(byteOffset
	    /* , littleEndian */
	    ) {
	      var bytes = get(this, 2, byteOffset, arguments[1]);
	      return bytes[1] << 8 | bytes[0];
	    },
	    getInt32: function getInt32(byteOffset
	    /* , littleEndian */
	    ) {
	      return unpackI32(get(this, 4, byteOffset, arguments[1]));
	    },
	    getUint32: function getUint32(byteOffset
	    /* , littleEndian */
	    ) {
	      return unpackI32(get(this, 4, byteOffset, arguments[1])) >>> 0;
	    },
	    getFloat32: function getFloat32(byteOffset
	    /* , littleEndian */
	    ) {
	      return unpackIEEE754(get(this, 4, byteOffset, arguments[1]), 23, 4);
	    },
	    getFloat64: function getFloat64(byteOffset
	    /* , littleEndian */
	    ) {
	      return unpackIEEE754(get(this, 8, byteOffset, arguments[1]), 52, 8);
	    },
	    setInt8: function setInt8(byteOffset, value) {
	      set(this, 1, byteOffset, packI8, value);
	    },
	    setUint8: function setUint8(byteOffset, value) {
	      set(this, 1, byteOffset, packI8, value);
	    },
	    setInt16: function setInt16(byteOffset, value
	    /* , littleEndian */
	    ) {
	      set(this, 2, byteOffset, packI16, value, arguments[2]);
	    },
	    setUint16: function setUint16(byteOffset, value
	    /* , littleEndian */
	    ) {
	      set(this, 2, byteOffset, packI16, value, arguments[2]);
	    },
	    setInt32: function setInt32(byteOffset, value
	    /* , littleEndian */
	    ) {
	      set(this, 4, byteOffset, packI32, value, arguments[2]);
	    },
	    setUint32: function setUint32(byteOffset, value
	    /* , littleEndian */
	    ) {
	      set(this, 4, byteOffset, packI32, value, arguments[2]);
	    },
	    setFloat32: function setFloat32(byteOffset, value
	    /* , littleEndian */
	    ) {
	      set(this, 4, byteOffset, packF32, value, arguments[2]);
	    },
	    setFloat64: function setFloat64(byteOffset, value
	    /* , littleEndian */
	    ) {
	      set(this, 8, byteOffset, packF64, value, arguments[2]);
	    }
	  });
	} else {
	  if (!_fails(function () {
	    $ArrayBuffer(1);
	  }) || !_fails(function () {
	    new $ArrayBuffer(-1); // eslint-disable-line no-new
	  }) || _fails(function () {
	    new $ArrayBuffer(); // eslint-disable-line no-new

	    new $ArrayBuffer(1.5); // eslint-disable-line no-new

	    new $ArrayBuffer(NaN); // eslint-disable-line no-new

	    return $ArrayBuffer.name != ARRAY_BUFFER;
	  })) {
	    $ArrayBuffer = function ArrayBuffer(length) {
	      _anInstance(this, $ArrayBuffer);
	      return new BaseBuffer(_toIndex(length));
	    };

	    var ArrayBufferProto = $ArrayBuffer[PROTOTYPE] = BaseBuffer[PROTOTYPE];

	    for (var keys = gOPN(BaseBuffer), j = 0, key; keys.length > j;) {
	      if (!((key = keys[j++]) in $ArrayBuffer)) _hide($ArrayBuffer, key, BaseBuffer[key]);
	    }

	    ArrayBufferProto.constructor = $ArrayBuffer;
	  } // iOS Safari 7.x bug


	  var view = new $DataView(new $ArrayBuffer(2));
	  var $setInt8 = $DataView[PROTOTYPE].setInt8;
	  view.setInt8(0, 2147483648);
	  view.setInt8(1, 2147483649);
	  if (view.getInt8(0) || !view.getInt8(1)) _redefineAll($DataView[PROTOTYPE], {
	    setInt8: function setInt8(byteOffset, value) {
	      $setInt8.call(this, byteOffset, value << 24 >> 24);
	    },
	    setUint8: function setUint8(byteOffset, value) {
	      $setInt8.call(this, byteOffset, value << 24 >> 24);
	    }
	  }, true);
	}

	_setToStringTag($ArrayBuffer, ARRAY_BUFFER);
	_setToStringTag($DataView, DATA_VIEW);
	_hide($DataView[PROTOTYPE], _typed.VIEW, true);
	exports[ARRAY_BUFFER] = $ArrayBuffer;
	exports[DATA_VIEW] = $DataView;
	});

	var ArrayBuffer = _global.ArrayBuffer;



	var $ArrayBuffer = _typedBuffer.ArrayBuffer;
	var $DataView = _typedBuffer.DataView;
	var $isView = _typed.ABV && ArrayBuffer.isView;
	var $slice = $ArrayBuffer.prototype.slice;
	var VIEW$1 = _typed.VIEW;
	var ARRAY_BUFFER = 'ArrayBuffer';
	_export(_export.G + _export.W + _export.F * (ArrayBuffer !== $ArrayBuffer), {
	  ArrayBuffer: $ArrayBuffer
	});
	_export(_export.S + _export.F * !_typed.CONSTR, ARRAY_BUFFER, {
	  // 24.1.3.1 ArrayBuffer.isView(arg)
	  isView: function isView(it) {
	    return $isView && $isView(it) || _isObject(it) && VIEW$1 in it;
	  }
	});
	_export(_export.P + _export.U + _export.F * _fails(function () {
	  return !new $ArrayBuffer(2).slice(1, undefined).byteLength;
	}), ARRAY_BUFFER, {
	  // 24.1.4.3 ArrayBuffer.prototype.slice(start, end)
	  slice: function slice(start, end) {
	    if ($slice !== undefined && end === undefined) return $slice.call(_anObject(this), start); // FF fix

	    var len = _anObject(this).byteLength;
	    var first = _toAbsoluteIndex(start, len);
	    var fin = _toAbsoluteIndex(end === undefined ? len : end, len);
	    var result = new (_speciesConstructor(this, $ArrayBuffer))(_toLength(fin - first));
	    var viewS = new $DataView(this);
	    var viewT = new $DataView(result);
	    var index = 0;

	    while (first < fin) {
	      viewT.setUint8(index++, viewS.getUint8(first++));
	    }

	    return result;
	  }
	});

	_setSpecies(ARRAY_BUFFER);

	_export(_export.G + _export.W + _export.F * !_typed.ABV, {
	  DataView: _typedBuffer.DataView
	});

	var _typedArray = createCommonjsModule(function (module) {

	if (_descriptors) {

	  var global = _global;

	  var fails = _fails;

	  var $export = _export;

	  var $typed = _typed;

	  var $buffer = _typedBuffer;

	  var ctx = _ctx;

	  var anInstance = _anInstance;

	  var propertyDesc = _propertyDesc;

	  var hide = _hide;

	  var redefineAll = _redefineAll;

	  var toInteger = _toInteger;

	  var toLength = _toLength;

	  var toIndex = _toIndex;

	  var toAbsoluteIndex = _toAbsoluteIndex;

	  var toPrimitive = _toPrimitive;

	  var has = _has;

	  var classof = _classof;

	  var isObject = _isObject;

	  var toObject = _toObject;

	  var isArrayIter = _isArrayIter;

	  var create = _objectCreate;

	  var getPrototypeOf = _objectGpo;

	  var gOPN = _objectGopn.f;

	  var getIterFn = core_getIteratorMethod;

	  var uid = _uid;

	  var wks = _wks;

	  var createArrayMethod = _arrayMethods;

	  var createArrayIncludes = _arrayIncludes;

	  var speciesConstructor = _speciesConstructor;

	  var ArrayIterators = es6_array_iterator;

	  var Iterators = _iterators;

	  var $iterDetect = _iterDetect;

	  var setSpecies = _setSpecies;

	  var arrayFill = _arrayFill;

	  var arrayCopyWithin = _arrayCopyWithin;

	  var $DP = _objectDp;

	  var $GOPD = _objectGopd;

	  var dP = $DP.f;
	  var gOPD = $GOPD.f;
	  var RangeError = global.RangeError;
	  var TypeError = global.TypeError;
	  var Uint8Array = global.Uint8Array;
	  var ARRAY_BUFFER = 'ArrayBuffer';
	  var SHARED_BUFFER = 'Shared' + ARRAY_BUFFER;
	  var BYTES_PER_ELEMENT = 'BYTES_PER_ELEMENT';
	  var PROTOTYPE = 'prototype';
	  var ArrayProto = Array[PROTOTYPE];
	  var $ArrayBuffer = $buffer.ArrayBuffer;
	  var $DataView = $buffer.DataView;
	  var arrayForEach = createArrayMethod(0);
	  var arrayFilter = createArrayMethod(2);
	  var arraySome = createArrayMethod(3);
	  var arrayEvery = createArrayMethod(4);
	  var arrayFind = createArrayMethod(5);
	  var arrayFindIndex = createArrayMethod(6);
	  var arrayIncludes = createArrayIncludes(true);
	  var arrayIndexOf = createArrayIncludes(false);
	  var arrayValues = ArrayIterators.values;
	  var arrayKeys = ArrayIterators.keys;
	  var arrayEntries = ArrayIterators.entries;
	  var arrayLastIndexOf = ArrayProto.lastIndexOf;
	  var arrayReduce = ArrayProto.reduce;
	  var arrayReduceRight = ArrayProto.reduceRight;
	  var arrayJoin = ArrayProto.join;
	  var arraySort = ArrayProto.sort;
	  var arraySlice = ArrayProto.slice;
	  var arrayToString = ArrayProto.toString;
	  var arrayToLocaleString = ArrayProto.toLocaleString;
	  var ITERATOR = wks('iterator');
	  var TAG = wks('toStringTag');
	  var TYPED_CONSTRUCTOR = uid('typed_constructor');
	  var DEF_CONSTRUCTOR = uid('def_constructor');
	  var ALL_CONSTRUCTORS = $typed.CONSTR;
	  var TYPED_ARRAY = $typed.TYPED;
	  var VIEW = $typed.VIEW;
	  var WRONG_LENGTH = 'Wrong length!';
	  var $map = createArrayMethod(1, function (O, length) {
	    return allocate(speciesConstructor(O, O[DEF_CONSTRUCTOR]), length);
	  });
	  var LITTLE_ENDIAN = fails(function () {
	    // eslint-disable-next-line no-undef
	    return new Uint8Array(new Uint16Array([1]).buffer)[0] === 1;
	  });
	  var FORCED_SET = !!Uint8Array && !!Uint8Array[PROTOTYPE].set && fails(function () {
	    new Uint8Array(1).set({});
	  });

	  var toOffset = function toOffset(it, BYTES) {
	    var offset = toInteger(it);
	    if (offset < 0 || offset % BYTES) throw RangeError('Wrong offset!');
	    return offset;
	  };

	  var validate = function validate(it) {
	    if (isObject(it) && TYPED_ARRAY in it) return it;
	    throw TypeError(it + ' is not a typed array!');
	  };

	  var allocate = function allocate(C, length) {
	    if (!(isObject(C) && TYPED_CONSTRUCTOR in C)) {
	      throw TypeError('It is not a typed array constructor!');
	    }

	    return new C(length);
	  };

	  var speciesFromList = function speciesFromList(O, list) {
	    return fromList(speciesConstructor(O, O[DEF_CONSTRUCTOR]), list);
	  };

	  var fromList = function fromList(C, list) {
	    var index = 0;
	    var length = list.length;
	    var result = allocate(C, length);

	    while (length > index) {
	      result[index] = list[index++];
	    }

	    return result;
	  };

	  var addGetter = function addGetter(it, key, internal) {
	    dP(it, key, {
	      get: function get() {
	        return this._d[internal];
	      }
	    });
	  };

	  var $from = function from(source
	  /* , mapfn, thisArg */
	  ) {
	    var O = toObject(source);
	    var aLen = arguments.length;
	    var mapfn = aLen > 1 ? arguments[1] : undefined;
	    var mapping = mapfn !== undefined;
	    var iterFn = getIterFn(O);
	    var i, length, values, result, step, iterator;

	    if (iterFn != undefined && !isArrayIter(iterFn)) {
	      for (iterator = iterFn.call(O), values = [], i = 0; !(step = iterator.next()).done; i++) {
	        values.push(step.value);
	      }

	      O = values;
	    }

	    if (mapping && aLen > 2) mapfn = ctx(mapfn, arguments[2], 2);

	    for (i = 0, length = toLength(O.length), result = allocate(this, length); length > i; i++) {
	      result[i] = mapping ? mapfn(O[i], i) : O[i];
	    }

	    return result;
	  };

	  var $of = function of()
	  /* ...items */
	  {
	    var index = 0;
	    var length = arguments.length;
	    var result = allocate(this, length);

	    while (length > index) {
	      result[index] = arguments[index++];
	    }

	    return result;
	  }; // iOS Safari 6.x fails here


	  var TO_LOCALE_BUG = !!Uint8Array && fails(function () {
	    arrayToLocaleString.call(new Uint8Array(1));
	  });

	  var $toLocaleString = function toLocaleString() {
	    return arrayToLocaleString.apply(TO_LOCALE_BUG ? arraySlice.call(validate(this)) : validate(this), arguments);
	  };

	  var proto = {
	    copyWithin: function copyWithin(target, start
	    /* , end */
	    ) {
	      return arrayCopyWithin.call(validate(this), target, start, arguments.length > 2 ? arguments[2] : undefined);
	    },
	    every: function every(callbackfn
	    /* , thisArg */
	    ) {
	      return arrayEvery(validate(this), callbackfn, arguments.length > 1 ? arguments[1] : undefined);
	    },
	    fill: function fill(value
	    /* , start, end */
	    ) {
	      // eslint-disable-line no-unused-vars
	      return arrayFill.apply(validate(this), arguments);
	    },
	    filter: function filter(callbackfn
	    /* , thisArg */
	    ) {
	      return speciesFromList(this, arrayFilter(validate(this), callbackfn, arguments.length > 1 ? arguments[1] : undefined));
	    },
	    find: function find(predicate
	    /* , thisArg */
	    ) {
	      return arrayFind(validate(this), predicate, arguments.length > 1 ? arguments[1] : undefined);
	    },
	    findIndex: function findIndex(predicate
	    /* , thisArg */
	    ) {
	      return arrayFindIndex(validate(this), predicate, arguments.length > 1 ? arguments[1] : undefined);
	    },
	    forEach: function forEach(callbackfn
	    /* , thisArg */
	    ) {
	      arrayForEach(validate(this), callbackfn, arguments.length > 1 ? arguments[1] : undefined);
	    },
	    indexOf: function indexOf(searchElement
	    /* , fromIndex */
	    ) {
	      return arrayIndexOf(validate(this), searchElement, arguments.length > 1 ? arguments[1] : undefined);
	    },
	    includes: function includes(searchElement
	    /* , fromIndex */
	    ) {
	      return arrayIncludes(validate(this), searchElement, arguments.length > 1 ? arguments[1] : undefined);
	    },
	    join: function join(separator) {
	      // eslint-disable-line no-unused-vars
	      return arrayJoin.apply(validate(this), arguments);
	    },
	    lastIndexOf: function lastIndexOf(searchElement
	    /* , fromIndex */
	    ) {
	      // eslint-disable-line no-unused-vars
	      return arrayLastIndexOf.apply(validate(this), arguments);
	    },
	    map: function map(mapfn
	    /* , thisArg */
	    ) {
	      return $map(validate(this), mapfn, arguments.length > 1 ? arguments[1] : undefined);
	    },
	    reduce: function reduce(callbackfn
	    /* , initialValue */
	    ) {
	      // eslint-disable-line no-unused-vars
	      return arrayReduce.apply(validate(this), arguments);
	    },
	    reduceRight: function reduceRight(callbackfn
	    /* , initialValue */
	    ) {
	      // eslint-disable-line no-unused-vars
	      return arrayReduceRight.apply(validate(this), arguments);
	    },
	    reverse: function reverse() {
	      var that = this;
	      var length = validate(that).length;
	      var middle = Math.floor(length / 2);
	      var index = 0;
	      var value;

	      while (index < middle) {
	        value = that[index];
	        that[index++] = that[--length];
	        that[length] = value;
	      }

	      return that;
	    },
	    some: function some(callbackfn
	    /* , thisArg */
	    ) {
	      return arraySome(validate(this), callbackfn, arguments.length > 1 ? arguments[1] : undefined);
	    },
	    sort: function sort(comparefn) {
	      return arraySort.call(validate(this), comparefn);
	    },
	    subarray: function subarray(begin, end) {
	      var O = validate(this);
	      var length = O.length;
	      var $begin = toAbsoluteIndex(begin, length);
	      return new (speciesConstructor(O, O[DEF_CONSTRUCTOR]))(O.buffer, O.byteOffset + $begin * O.BYTES_PER_ELEMENT, toLength((end === undefined ? length : toAbsoluteIndex(end, length)) - $begin));
	    }
	  };

	  var $slice = function slice(start, end) {
	    return speciesFromList(this, arraySlice.call(validate(this), start, end));
	  };

	  var $set = function set(arrayLike
	  /* , offset */
	  ) {
	    validate(this);
	    var offset = toOffset(arguments[1], 1);
	    var length = this.length;
	    var src = toObject(arrayLike);
	    var len = toLength(src.length);
	    var index = 0;
	    if (len + offset > length) throw RangeError(WRONG_LENGTH);

	    while (index < len) {
	      this[offset + index] = src[index++];
	    }
	  };

	  var $iterators = {
	    entries: function entries() {
	      return arrayEntries.call(validate(this));
	    },
	    keys: function keys() {
	      return arrayKeys.call(validate(this));
	    },
	    values: function values() {
	      return arrayValues.call(validate(this));
	    }
	  };

	  var isTAIndex = function isTAIndex(target, key) {
	    return isObject(target) && target[TYPED_ARRAY] && babelHelpers.typeof(key) != 'symbol' && key in target && String(+key) == String(key);
	  };

	  var $getDesc = function getOwnPropertyDescriptor(target, key) {
	    return isTAIndex(target, key = toPrimitive(key, true)) ? propertyDesc(2, target[key]) : gOPD(target, key);
	  };

	  var $setDesc = function defineProperty(target, key, desc) {
	    if (isTAIndex(target, key = toPrimitive(key, true)) && isObject(desc) && has(desc, 'value') && !has(desc, 'get') && !has(desc, 'set') // TODO: add validation descriptor w/o calling accessors
	    && !desc.configurable && (!has(desc, 'writable') || desc.writable) && (!has(desc, 'enumerable') || desc.enumerable)) {
	      target[key] = desc.value;
	      return target;
	    }

	    return dP(target, key, desc);
	  };

	  if (!ALL_CONSTRUCTORS) {
	    $GOPD.f = $getDesc;
	    $DP.f = $setDesc;
	  }

	  $export($export.S + $export.F * !ALL_CONSTRUCTORS, 'Object', {
	    getOwnPropertyDescriptor: $getDesc,
	    defineProperty: $setDesc
	  });

	  if (fails(function () {
	    arrayToString.call({});
	  })) {
	    arrayToString = arrayToLocaleString = function toString() {
	      return arrayJoin.call(this);
	    };
	  }

	  var $TypedArrayPrototype$ = redefineAll({}, proto);
	  redefineAll($TypedArrayPrototype$, $iterators);
	  hide($TypedArrayPrototype$, ITERATOR, $iterators.values);
	  redefineAll($TypedArrayPrototype$, {
	    slice: $slice,
	    set: $set,
	    constructor: function constructor() {
	      /* noop */
	    },
	    toString: arrayToString,
	    toLocaleString: $toLocaleString
	  });
	  addGetter($TypedArrayPrototype$, 'buffer', 'b');
	  addGetter($TypedArrayPrototype$, 'byteOffset', 'o');
	  addGetter($TypedArrayPrototype$, 'byteLength', 'l');
	  addGetter($TypedArrayPrototype$, 'length', 'e');
	  dP($TypedArrayPrototype$, TAG, {
	    get: function get() {
	      return this[TYPED_ARRAY];
	    }
	  }); // eslint-disable-next-line max-statements

	  module.exports = function (KEY, BYTES, wrapper, CLAMPED) {
	    CLAMPED = !!CLAMPED;
	    var NAME = KEY + (CLAMPED ? 'Clamped' : '') + 'Array';
	    var GETTER = 'get' + KEY;
	    var SETTER = 'set' + KEY;
	    var TypedArray = global[NAME];
	    var Base = TypedArray || {};
	    var TAC = TypedArray && getPrototypeOf(TypedArray);
	    var FORCED = !TypedArray || !$typed.ABV;
	    var O = {};
	    var TypedArrayPrototype = TypedArray && TypedArray[PROTOTYPE];

	    var getter = function getter(that, index) {
	      var data = that._d;
	      return data.v[GETTER](index * BYTES + data.o, LITTLE_ENDIAN);
	    };

	    var setter = function setter(that, index, value) {
	      var data = that._d;
	      if (CLAMPED) value = (value = Math.round(value)) < 0 ? 0 : value > 0xff ? 0xff : value & 0xff;
	      data.v[SETTER](index * BYTES + data.o, value, LITTLE_ENDIAN);
	    };

	    var addElement = function addElement(that, index) {
	      dP(that, index, {
	        get: function get() {
	          return getter(this, index);
	        },
	        set: function set(value) {
	          return setter(this, index, value);
	        },
	        enumerable: true
	      });
	    };

	    if (FORCED) {
	      TypedArray = wrapper(function (that, data, $offset, $length) {
	        anInstance(that, TypedArray, NAME, '_d');
	        var index = 0;
	        var offset = 0;
	        var buffer, byteLength, length, klass;

	        if (!isObject(data)) {
	          length = toIndex(data);
	          byteLength = length * BYTES;
	          buffer = new $ArrayBuffer(byteLength);
	        } else if (data instanceof $ArrayBuffer || (klass = classof(data)) == ARRAY_BUFFER || klass == SHARED_BUFFER) {
	          buffer = data;
	          offset = toOffset($offset, BYTES);
	          var $len = data.byteLength;

	          if ($length === undefined) {
	            if ($len % BYTES) throw RangeError(WRONG_LENGTH);
	            byteLength = $len - offset;
	            if (byteLength < 0) throw RangeError(WRONG_LENGTH);
	          } else {
	            byteLength = toLength($length) * BYTES;
	            if (byteLength + offset > $len) throw RangeError(WRONG_LENGTH);
	          }

	          length = byteLength / BYTES;
	        } else if (TYPED_ARRAY in data) {
	          return fromList(TypedArray, data);
	        } else {
	          return $from.call(TypedArray, data);
	        }

	        hide(that, '_d', {
	          b: buffer,
	          o: offset,
	          l: byteLength,
	          e: length,
	          v: new $DataView(buffer)
	        });

	        while (index < length) {
	          addElement(that, index++);
	        }
	      });
	      TypedArrayPrototype = TypedArray[PROTOTYPE] = create($TypedArrayPrototype$);
	      hide(TypedArrayPrototype, 'constructor', TypedArray);
	    } else if (!fails(function () {
	      TypedArray(1);
	    }) || !fails(function () {
	      new TypedArray(-1); // eslint-disable-line no-new
	    }) || !$iterDetect(function (iter) {
	      new TypedArray(); // eslint-disable-line no-new

	      new TypedArray(null); // eslint-disable-line no-new

	      new TypedArray(1.5); // eslint-disable-line no-new

	      new TypedArray(iter); // eslint-disable-line no-new
	    }, true)) {
	      TypedArray = wrapper(function (that, data, $offset, $length) {
	        anInstance(that, TypedArray, NAME);
	        var klass; // `ws` module bug, temporarily remove validation length for Uint8Array
	        // https://github.com/websockets/ws/pull/645

	        if (!isObject(data)) return new Base(toIndex(data));

	        if (data instanceof $ArrayBuffer || (klass = classof(data)) == ARRAY_BUFFER || klass == SHARED_BUFFER) {
	          return $length !== undefined ? new Base(data, toOffset($offset, BYTES), $length) : $offset !== undefined ? new Base(data, toOffset($offset, BYTES)) : new Base(data);
	        }

	        if (TYPED_ARRAY in data) return fromList(TypedArray, data);
	        return $from.call(TypedArray, data);
	      });
	      arrayForEach(TAC !== Function.prototype ? gOPN(Base).concat(gOPN(TAC)) : gOPN(Base), function (key) {
	        if (!(key in TypedArray)) hide(TypedArray, key, Base[key]);
	      });
	      TypedArray[PROTOTYPE] = TypedArrayPrototype;
	      TypedArrayPrototype.constructor = TypedArray;
	    }

	    var $nativeIterator = TypedArrayPrototype[ITERATOR];
	    var CORRECT_ITER_NAME = !!$nativeIterator && ($nativeIterator.name == 'values' || $nativeIterator.name == undefined);
	    var $iterator = $iterators.values;
	    hide(TypedArray, TYPED_CONSTRUCTOR, true);
	    hide(TypedArrayPrototype, TYPED_ARRAY, NAME);
	    hide(TypedArrayPrototype, VIEW, true);
	    hide(TypedArrayPrototype, DEF_CONSTRUCTOR, TypedArray);

	    if (CLAMPED ? new TypedArray(1)[TAG] != NAME : !(TAG in TypedArrayPrototype)) {
	      dP(TypedArrayPrototype, TAG, {
	        get: function get() {
	          return NAME;
	        }
	      });
	    }

	    O[NAME] = TypedArray;
	    $export($export.G + $export.W + $export.F * (TypedArray != Base), O);
	    $export($export.S, NAME, {
	      BYTES_PER_ELEMENT: BYTES
	    });
	    $export($export.S + $export.F * fails(function () {
	      Base.of.call(TypedArray, 1);
	    }), NAME, {
	      from: $from,
	      of: $of
	    });
	    if (!(BYTES_PER_ELEMENT in TypedArrayPrototype)) hide(TypedArrayPrototype, BYTES_PER_ELEMENT, BYTES);
	    $export($export.P, NAME, proto);
	    setSpecies(NAME);
	    $export($export.P + $export.F * FORCED_SET, NAME, {
	      set: $set
	    });
	    $export($export.P + $export.F * !CORRECT_ITER_NAME, NAME, $iterators);
	    if (TypedArrayPrototype.toString != arrayToString) TypedArrayPrototype.toString = arrayToString;
	    $export($export.P + $export.F * fails(function () {
	      new TypedArray(1).slice();
	    }), NAME, {
	      slice: $slice
	    });
	    $export($export.P + $export.F * (fails(function () {
	      return [1, 2].toLocaleString() != new TypedArray([1, 2]).toLocaleString();
	    }) || !fails(function () {
	      TypedArrayPrototype.toLocaleString.call([1, 2]);
	    })), NAME, {
	      toLocaleString: $toLocaleString
	    });
	    Iterators[NAME] = CORRECT_ITER_NAME ? $nativeIterator : $iterator;
	    if (!CORRECT_ITER_NAME) hide(TypedArrayPrototype, ITERATOR, $iterator);
	  };
	} else module.exports = function () {
	  /* empty */
	};
	});

	_typedArray('Int8', 1, function (init) {
	  return function Int8Array(data, byteOffset, length) {
	    return init(this, data, byteOffset, length);
	  };
	});

	_typedArray('Uint8', 1, function (init) {
	  return function Uint8Array(data, byteOffset, length) {
	    return init(this, data, byteOffset, length);
	  };
	});

	_typedArray('Uint8', 1, function (init) {
	  return function Uint8ClampedArray(data, byteOffset, length) {
	    return init(this, data, byteOffset, length);
	  };
	}, true);

	_typedArray('Int16', 2, function (init) {
	  return function Int16Array(data, byteOffset, length) {
	    return init(this, data, byteOffset, length);
	  };
	});

	_typedArray('Uint16', 2, function (init) {
	  return function Uint16Array(data, byteOffset, length) {
	    return init(this, data, byteOffset, length);
	  };
	});

	_typedArray('Int32', 4, function (init) {
	  return function Int32Array(data, byteOffset, length) {
	    return init(this, data, byteOffset, length);
	  };
	});

	_typedArray('Uint32', 4, function (init) {
	  return function Uint32Array(data, byteOffset, length) {
	    return init(this, data, byteOffset, length);
	  };
	});

	_typedArray('Float32', 4, function (init) {
	  return function Float32Array(data, byteOffset, length) {
	    return init(this, data, byteOffset, length);
	  };
	});

	_typedArray('Float64', 8, function (init) {
	  return function Float64Array(data, byteOffset, length) {
	    return init(this, data, byteOffset, length);
	  };
	});

	// 26.1.1 Reflect.apply(target, thisArgument, argumentsList)






	var rApply = (_global.Reflect || {}).apply;
	var fApply = Function.apply; // MS Edge argumentsList argument is optional

	_export(_export.S + _export.F * !_fails(function () {
	  rApply(function () {
	    /* empty */
	  });
	}), 'Reflect', {
	  apply: function apply(target, thisArgument, argumentsList) {
	    var T = _aFunction(target);
	    var L = _anObject(argumentsList);
	    return rApply ? rApply(T, thisArgument, L) : fApply.call(T, thisArgument, L);
	  }
	});

	// 26.1.2 Reflect.construct(target, argumentsList [, newTarget])














	var rConstruct = (_global.Reflect || {}).construct; // MS Edge supports only 2 arguments and argumentsList argument is optional
	// FF Nightly sets third argument as `new.target`, but does not create `this` from it

	var NEW_TARGET_BUG = _fails(function () {
	  function F() {
	    /* empty */
	  }

	  return !(rConstruct(function () {
	    /* empty */
	  }, [], F) instanceof F);
	});
	var ARGS_BUG = !_fails(function () {
	  rConstruct(function () {
	    /* empty */
	  });
	});
	_export(_export.S + _export.F * (NEW_TARGET_BUG || ARGS_BUG), 'Reflect', {
	  construct: function construct(Target, args
	  /* , newTarget */
	  ) {
	    _aFunction(Target);
	    _anObject(args);
	    var newTarget = arguments.length < 3 ? Target : _aFunction(arguments[2]);
	    if (ARGS_BUG && !NEW_TARGET_BUG) return rConstruct(Target, args, newTarget);

	    if (Target == newTarget) {
	      // w/o altered newTarget, optimization for 0-4 arguments
	      switch (args.length) {
	        case 0:
	          return new Target();

	        case 1:
	          return new Target(args[0]);

	        case 2:
	          return new Target(args[0], args[1]);

	        case 3:
	          return new Target(args[0], args[1], args[2]);

	        case 4:
	          return new Target(args[0], args[1], args[2], args[3]);
	      } // w/o altered newTarget, lot of arguments case


	      var $args = [null];
	      $args.push.apply($args, args);
	      return new (_bind.apply(Target, $args))();
	    } // with altered newTarget, not support built-in constructors


	    var proto = newTarget.prototype;
	    var instance = _objectCreate(_isObject(proto) ? proto : Object.prototype);
	    var result = Function.apply.call(Target, instance, args);
	    return _isObject(result) ? result : instance;
	  }
	});

	// 26.1.3 Reflect.defineProperty(target, propertyKey, attributes)






	 // MS Edge has broken Reflect.defineProperty - throwing instead of returning false


	_export(_export.S + _export.F * _fails(function () {
	  // eslint-disable-next-line no-undef
	  Reflect.defineProperty(_objectDp.f({}, 1, {
	    value: 1
	  }), 1, {
	    value: 2
	  });
	}), 'Reflect', {
	  defineProperty: function defineProperty(target, propertyKey, attributes) {
	    _anObject(target);
	    propertyKey = _toPrimitive(propertyKey, true);
	    _anObject(attributes);

	    try {
	      _objectDp.f(target, propertyKey, attributes);
	      return true;
	    } catch (e) {
	      return false;
	    }
	  }
	});

	// 26.1.4 Reflect.deleteProperty(target, propertyKey)


	var gOPD$3 = _objectGopd.f;



	_export(_export.S, 'Reflect', {
	  deleteProperty: function deleteProperty(target, propertyKey) {
	    var desc = gOPD$3(_anObject(target), propertyKey);
	    return desc && !desc.configurable ? false : delete target[propertyKey];
	  }
	});

	var Enumerate = function Enumerate(iterated) {
	  this._t = _anObject(iterated); // target

	  this._i = 0; // next index

	  var keys = this._k = []; // keys

	  var key;

	  for (key in iterated) {
	    keys.push(key);
	  }
	};

	_iterCreate(Enumerate, 'Object', function () {
	  var that = this;
	  var keys = that._k;
	  var key;

	  do {
	    if (that._i >= keys.length) return {
	      value: undefined,
	      done: true
	    };
	  } while (!((key = keys[that._i++]) in that._t));

	  return {
	    value: key,
	    done: false
	  };
	});

	_export(_export.S, 'Reflect', {
	  enumerate: function enumerate(target) {
	    return new Enumerate(target);
	  }
	});

	// 26.1.6 Reflect.get(target, propertyKey [, receiver])












	function get(target, propertyKey
	/* , receiver */
	) {
	  var receiver = arguments.length < 3 ? target : arguments[2];
	  var desc, proto;
	  if (_anObject(target) === receiver) return target[propertyKey];
	  if (desc = _objectGopd.f(target, propertyKey)) return _has(desc, 'value') ? desc.value : desc.get !== undefined ? desc.get.call(receiver) : undefined;
	  if (_isObject(proto = _objectGpo(target))) return get(proto, propertyKey, receiver);
	}

	_export(_export.S, 'Reflect', {
	  get: get
	});

	// 26.1.7 Reflect.getOwnPropertyDescriptor(target, propertyKey)






	_export(_export.S, 'Reflect', {
	  getOwnPropertyDescriptor: function getOwnPropertyDescriptor(target, propertyKey) {
	    return _objectGopd.f(_anObject(target), propertyKey);
	  }
	});

	// 26.1.8 Reflect.getPrototypeOf(target)






	_export(_export.S, 'Reflect', {
	  getPrototypeOf: function getPrototypeOf(target) {
	    return _objectGpo(_anObject(target));
	  }
	});

	// 26.1.9 Reflect.has(target, propertyKey)


	_export(_export.S, 'Reflect', {
	  has: function has(target, propertyKey) {
	    return propertyKey in target;
	  }
	});

	// 26.1.10 Reflect.isExtensible(target)




	var $isExtensible = Object.isExtensible;
	_export(_export.S, 'Reflect', {
	  isExtensible: function isExtensible(target) {
	    _anObject(target);
	    return $isExtensible ? $isExtensible(target) : true;
	  }
	});

	// all object keys, includes non-enumerable and symbols






	var Reflect$1 = _global.Reflect;

	var _ownKeys = Reflect$1 && Reflect$1.ownKeys || function ownKeys(it) {
	  var keys = _objectGopn.f(_anObject(it));
	  var getSymbols = _objectGops.f;
	  return getSymbols ? keys.concat(getSymbols(it)) : keys;
	};

	// 26.1.11 Reflect.ownKeys(target)


	_export(_export.S, 'Reflect', {
	  ownKeys: _ownKeys
	});

	// 26.1.12 Reflect.preventExtensions(target)




	var $preventExtensions = Object.preventExtensions;
	_export(_export.S, 'Reflect', {
	  preventExtensions: function preventExtensions(target) {
	    _anObject(target);

	    try {
	      if ($preventExtensions) $preventExtensions(target);
	      return true;
	    } catch (e) {
	      return false;
	    }
	  }
	});

	// 26.1.13 Reflect.set(target, propertyKey, V [, receiver])
















	function set(target, propertyKey, V
	/* , receiver */
	) {
	  var receiver = arguments.length < 4 ? target : arguments[3];
	  var ownDesc = _objectGopd.f(_anObject(target), propertyKey);
	  var existingDescriptor, proto;

	  if (!ownDesc) {
	    if (_isObject(proto = _objectGpo(target))) {
	      return set(proto, propertyKey, V, receiver);
	    }

	    ownDesc = _propertyDesc(0);
	  }

	  if (_has(ownDesc, 'value')) {
	    if (ownDesc.writable === false || !_isObject(receiver)) return false;

	    if (existingDescriptor = _objectGopd.f(receiver, propertyKey)) {
	      if (existingDescriptor.get || existingDescriptor.set || existingDescriptor.writable === false) return false;
	      existingDescriptor.value = V;
	      _objectDp.f(receiver, propertyKey, existingDescriptor);
	    } else _objectDp.f(receiver, propertyKey, _propertyDesc(0, V));

	    return true;
	  }

	  return ownDesc.set === undefined ? false : (ownDesc.set.call(receiver, V), true);
	}

	_export(_export.S, 'Reflect', {
	  set: set
	});

	// 26.1.14 Reflect.setPrototypeOf(target, proto)




	if (_setProto) _export(_export.S, 'Reflect', {
	  setPrototypeOf: function setPrototypeOf(target, proto) {
	    _setProto.check(target, proto);

	    try {
	      _setProto.set(target, proto);
	      return true;
	    } catch (e) {
	      return false;
	    }
	  }
	});

	var $includes = _arrayIncludes(true);

	_export(_export.P, 'Array', {
	  includes: function includes(el
	  /* , fromIndex = 0 */
	  ) {
	    return $includes(this, el, arguments.length > 1 ? arguments[1] : undefined);
	  }
	});

	_addToUnscopables('includes');

	var IS_CONCAT_SPREADABLE = _wks('isConcatSpreadable');

	function flattenIntoArray(target, original, source, sourceLen, start, depth, mapper, thisArg) {
	  var targetIndex = start;
	  var sourceIndex = 0;
	  var mapFn = mapper ? _ctx(mapper, thisArg, 3) : false;
	  var element, spreadable;

	  while (sourceIndex < sourceLen) {
	    if (sourceIndex in source) {
	      element = mapFn ? mapFn(source[sourceIndex], sourceIndex, original) : source[sourceIndex];
	      spreadable = false;

	      if (_isObject(element)) {
	        spreadable = element[IS_CONCAT_SPREADABLE];
	        spreadable = spreadable !== undefined ? !!spreadable : _isArray(element);
	      }

	      if (spreadable && depth > 0) {
	        targetIndex = flattenIntoArray(target, original, element, _toLength(element.length), targetIndex, depth - 1) - 1;
	      } else {
	        if (targetIndex >= 0x1fffffffffffff) throw TypeError();
	        target[targetIndex] = element;
	      }

	      targetIndex++;
	    }

	    sourceIndex++;
	  }

	  return targetIndex;
	}

	var _flattenIntoArray = flattenIntoArray;

	_export(_export.P, 'Array', {
	  flatMap: function flatMap(callbackfn
	  /* , thisArg */
	  ) {
	    var O = _toObject(this);
	    var sourceLen, A;
	    _aFunction(callbackfn);
	    sourceLen = _toLength(O.length);
	    A = _arraySpeciesCreate(O, 0);
	    _flattenIntoArray(A, O, O, sourceLen, 0, 1, callbackfn, arguments[1]);
	    return A;
	  }
	});

	_addToUnscopables('flatMap');

	_export(_export.P, 'Array', {
	  flatten: function flatten()
	  /* depthArg = 1 */
	  {
	    var depthArg = arguments[0];
	    var O = _toObject(this);
	    var sourceLen = _toLength(O.length);
	    var A = _arraySpeciesCreate(O, 0);
	    _flattenIntoArray(A, O, O, sourceLen, 0, depthArg === undefined ? 1 : _toInteger(depthArg));
	    return A;
	  }
	});

	_addToUnscopables('flatten');

	var $at$2 = _stringAt(true);

	_export(_export.P, 'String', {
	  at: function at(pos) {
	    return $at$2(this, pos);
	  }
	});

	// https://github.com/tc39/proposal-string-pad-start-end






	var _stringPad = function (that, maxLength, fillString, left) {
	  var S = String(_defined(that));
	  var stringLength = S.length;
	  var fillStr = fillString === undefined ? ' ' : String(fillString);
	  var intMaxLength = _toLength(maxLength);
	  if (intMaxLength <= stringLength || fillStr == '') return S;
	  var fillLen = intMaxLength - stringLength;
	  var stringFiller = _stringRepeat.call(fillStr, Math.ceil(fillLen / fillStr.length));
	  if (stringFiller.length > fillLen) stringFiller = stringFiller.slice(0, fillLen);
	  return left ? stringFiller + S : S + stringFiller;
	};

	// https://github.com/zloirock/core-js/issues/280


	_export(_export.P + _export.F * /Version\/10\.\d+(\.\d+)? Safari\//.test(_userAgent), 'String', {
	  padStart: function padStart(maxLength
	  /* , fillString = ' ' */
	  ) {
	    return _stringPad(this, maxLength, arguments.length > 1 ? arguments[1] : undefined, true);
	  }
	});

	// https://github.com/zloirock/core-js/issues/280


	_export(_export.P + _export.F * /Version\/10\.\d+(\.\d+)? Safari\//.test(_userAgent), 'String', {
	  padEnd: function padEnd(maxLength
	  /* , fillString = ' ' */
	  ) {
	    return _stringPad(this, maxLength, arguments.length > 1 ? arguments[1] : undefined, false);
	  }
	});

	_stringTrim('trimLeft', function ($trim) {
	  return function trimLeft() {
	    return $trim(this, 1);
	  };
	}, 'trimStart');

	_stringTrim('trimRight', function ($trim) {
	  return function trimRight() {
	    return $trim(this, 2);
	  };
	}, 'trimEnd');

	var RegExpProto = RegExp.prototype;

	var $RegExpStringIterator = function $RegExpStringIterator(regexp, string) {
	  this._r = regexp;
	  this._s = string;
	};

	_iterCreate($RegExpStringIterator, 'RegExp String', function next() {
	  var match = this._r.exec(this._s);

	  return {
	    value: match,
	    done: match === null
	  };
	});

	_export(_export.P, 'String', {
	  matchAll: function matchAll(regexp) {
	    _defined(this);
	    if (!_isRegexp(regexp)) throw TypeError(regexp + ' is not a regexp!');
	    var S = String(this);
	    var flags = 'flags' in RegExpProto ? String(regexp.flags) : _flags.call(regexp);
	    var rx = new RegExp(regexp.source, ~flags.indexOf('g') ? flags : 'g' + flags);
	    rx.lastIndex = _toLength(regexp.lastIndex);
	    return new $RegExpStringIterator(rx, S);
	  }
	});

	_wksDefine('asyncIterator');

	_wksDefine('observable');

	// https://github.com/tc39/proposal-object-getownpropertydescriptors










	_export(_export.S, 'Object', {
	  getOwnPropertyDescriptors: function getOwnPropertyDescriptors(object) {
	    var O = _toIobject(object);
	    var getDesc = _objectGopd.f;
	    var keys = _ownKeys(O);
	    var result = {};
	    var i = 0;
	    var key, desc;

	    while (keys.length > i) {
	      desc = getDesc(O, key = keys[i++]);
	      if (desc !== undefined) _createProperty(result, key, desc);
	    }

	    return result;
	  }
	});

	var isEnum$1 = _objectPie.f;

	var _objectToArray = function (isEntries) {
	  return function (it) {
	    var O = _toIobject(it);
	    var keys = _objectKeys(O);
	    var length = keys.length;
	    var i = 0;
	    var result = [];
	    var key;

	    while (length > i) {
	      if (isEnum$1.call(O, key = keys[i++])) {
	        result.push(isEntries ? [key, O[key]] : O[key]);
	      }
	    }

	    return result;
	  };
	};

	// https://github.com/tc39/proposal-object-values-entries


	var $values = _objectToArray(false);

	_export(_export.S, 'Object', {
	  values: function values(it) {
	    return $values(it);
	  }
	});

	// https://github.com/tc39/proposal-object-values-entries


	var $entries = _objectToArray(true);

	_export(_export.S, 'Object', {
	  entries: function entries(it) {
	    return $entries(it);
	  }
	});

	var _objectForcedPam = !_fails(function () {
	  var K = Math.random(); // In FF throws only define methods
	  // eslint-disable-next-line no-undef, no-useless-call

	  __defineSetter__.call(null, K, function () {
	    /* empty */
	  });

	  delete _global[K];
	});

	// B.2.2.2 Object.prototype.__defineGetter__(P, getter)


	_descriptors && _export(_export.P + _objectForcedPam, 'Object', {
	  __defineGetter__: function __defineGetter__(P, getter) {
	    _objectDp.f(_toObject(this), P, {
	      get: _aFunction(getter),
	      enumerable: true,
	      configurable: true
	    });
	  }
	});

	// B.2.2.3 Object.prototype.__defineSetter__(P, setter)


	_descriptors && _export(_export.P + _objectForcedPam, 'Object', {
	  __defineSetter__: function __defineSetter__(P, setter) {
	    _objectDp.f(_toObject(this), P, {
	      set: _aFunction(setter),
	      enumerable: true,
	      configurable: true
	    });
	  }
	});

	var getOwnPropertyDescriptor = _objectGopd.f; // B.2.2.4 Object.prototype.__lookupGetter__(P)


	_descriptors && _export(_export.P + _objectForcedPam, 'Object', {
	  __lookupGetter__: function __lookupGetter__(P) {
	    var O = _toObject(this);
	    var K = _toPrimitive(P, true);
	    var D;

	    do {
	      if (D = getOwnPropertyDescriptor(O, K)) return D.get;
	    } while (O = _objectGpo(O));
	  }
	});

	var getOwnPropertyDescriptor$1 = _objectGopd.f; // B.2.2.5 Object.prototype.__lookupSetter__(P)


	_descriptors && _export(_export.P + _objectForcedPam, 'Object', {
	  __lookupSetter__: function __lookupSetter__(P) {
	    var O = _toObject(this);
	    var K = _toPrimitive(P, true);
	    var D;

	    do {
	      if (D = getOwnPropertyDescriptor$1(O, K)) return D.set;
	    } while (O = _objectGpo(O));
	  }
	});

	var _arrayFromIterable = function (iter, ITERATOR) {
	  var result = [];
	  _forOf(iter, false, result.push, result, ITERATOR);
	  return result;
	};

	// https://github.com/DavidBruant/Map-Set.prototype.toJSON




	var _collectionToJson = function (NAME) {
	  return function toJSON() {
	    if (_classof(this) != NAME) throw TypeError(NAME + "#toJSON isn't generic");
	    return _arrayFromIterable(this);
	  };
	};

	// https://github.com/DavidBruant/Map-Set.prototype.toJSON


	_export(_export.P + _export.R, 'Map', {
	  toJSON: _collectionToJson('Map')
	});

	// https://github.com/DavidBruant/Map-Set.prototype.toJSON


	_export(_export.P + _export.R, 'Set', {
	  toJSON: _collectionToJson('Set')
	});

	var _setCollectionOf = function (COLLECTION) {
	  _export(_export.S, COLLECTION, {
	    of: function of() {
	      var length = arguments.length;
	      var A = new Array(length);

	      while (length--) {
	        A[length] = arguments[length];
	      }

	      return new this(A);
	    }
	  });
	};

	// https://tc39.github.io/proposal-setmap-offrom/#sec-map.of
	_setCollectionOf('Map');

	// https://tc39.github.io/proposal-setmap-offrom/#sec-set.of
	_setCollectionOf('Set');

	// https://tc39.github.io/proposal-setmap-offrom/#sec-weakmap.of
	_setCollectionOf('WeakMap');

	// https://tc39.github.io/proposal-setmap-offrom/#sec-weakset.of
	_setCollectionOf('WeakSet');

	var _setCollectionFrom = function (COLLECTION) {
	  _export(_export.S, COLLECTION, {
	    from: function from(source
	    /* , mapFn, thisArg */
	    ) {
	      var mapFn = arguments[1];
	      var mapping, A, n, cb;
	      _aFunction(this);
	      mapping = mapFn !== undefined;
	      if (mapping) _aFunction(mapFn);
	      if (source == undefined) return new this();
	      A = [];

	      if (mapping) {
	        n = 0;
	        cb = _ctx(mapFn, arguments[2], 2);
	        _forOf(source, false, function (nextItem) {
	          A.push(cb(nextItem, n++));
	        });
	      } else {
	        _forOf(source, false, A.push, A);
	      }

	      return new this(A);
	    }
	  });
	};

	// https://tc39.github.io/proposal-setmap-offrom/#sec-map.from
	_setCollectionFrom('Map');

	// https://tc39.github.io/proposal-setmap-offrom/#sec-set.from
	_setCollectionFrom('Set');

	// https://tc39.github.io/proposal-setmap-offrom/#sec-weakmap.from
	_setCollectionFrom('WeakMap');

	// https://tc39.github.io/proposal-setmap-offrom/#sec-weakset.from
	_setCollectionFrom('WeakSet');

	// https://github.com/tc39/proposal-global


	_export(_export.G, {
	  global: _global
	});

	// https://github.com/tc39/proposal-global


	_export(_export.S, 'System', {
	  global: _global
	});

	// https://github.com/ljharb/proposal-is-error




	_export(_export.S, 'Error', {
	  isError: function isError(it) {
	    return _cof(it) === 'Error';
	  }
	});

	// https://rwaldron.github.io/proposal-math-extensions/


	_export(_export.S, 'Math', {
	  clamp: function clamp(x, lower, upper) {
	    return Math.min(upper, Math.max(lower, x));
	  }
	});

	// https://rwaldron.github.io/proposal-math-extensions/


	_export(_export.S, 'Math', {
	  DEG_PER_RAD: Math.PI / 180
	});

	// https://rwaldron.github.io/proposal-math-extensions/


	var RAD_PER_DEG = 180 / Math.PI;
	_export(_export.S, 'Math', {
	  degrees: function degrees(radians) {
	    return radians * RAD_PER_DEG;
	  }
	});

	// https://rwaldron.github.io/proposal-math-extensions/
	var _mathScale = Math.scale || function scale(x, inLow, inHigh, outLow, outHigh) {
	  if (arguments.length === 0 // eslint-disable-next-line no-self-compare
	  || x != x // eslint-disable-next-line no-self-compare
	  || inLow != inLow // eslint-disable-next-line no-self-compare
	  || inHigh != inHigh // eslint-disable-next-line no-self-compare
	  || outLow != outLow // eslint-disable-next-line no-self-compare
	  || outHigh != outHigh) return NaN;
	  if (x === Infinity || x === -Infinity) return x;
	  return (x - inLow) * (outHigh - outLow) / (inHigh - inLow) + outLow;
	};

	// https://rwaldron.github.io/proposal-math-extensions/






	_export(_export.S, 'Math', {
	  fscale: function fscale(x, inLow, inHigh, outLow, outHigh) {
	    return _mathFround(_mathScale(x, inLow, inHigh, outLow, outHigh));
	  }
	});

	// https://gist.github.com/BrendanEich/4294d5c212a6d2254703


	_export(_export.S, 'Math', {
	  iaddh: function iaddh(x0, x1, y0, y1) {
	    var $x0 = x0 >>> 0;
	    var $x1 = x1 >>> 0;
	    var $y0 = y0 >>> 0;
	    return $x1 + (y1 >>> 0) + (($x0 & $y0 | ($x0 | $y0) & ~($x0 + $y0 >>> 0)) >>> 31) | 0;
	  }
	});

	// https://gist.github.com/BrendanEich/4294d5c212a6d2254703


	_export(_export.S, 'Math', {
	  isubh: function isubh(x0, x1, y0, y1) {
	    var $x0 = x0 >>> 0;
	    var $x1 = x1 >>> 0;
	    var $y0 = y0 >>> 0;
	    return $x1 - (y1 >>> 0) - ((~$x0 & $y0 | ~($x0 ^ $y0) & $x0 - $y0 >>> 0) >>> 31) | 0;
	  }
	});

	// https://gist.github.com/BrendanEich/4294d5c212a6d2254703


	_export(_export.S, 'Math', {
	  imulh: function imulh(u, v) {
	    var UINT16 = 0xffff;
	    var $u = +u;
	    var $v = +v;
	    var u0 = $u & UINT16;
	    var v0 = $v & UINT16;
	    var u1 = $u >> 16;
	    var v1 = $v >> 16;
	    var t = (u1 * v0 >>> 0) + (u0 * v0 >>> 16);
	    return u1 * v1 + (t >> 16) + ((u0 * v1 >>> 0) + (t & UINT16) >> 16);
	  }
	});

	// https://rwaldron.github.io/proposal-math-extensions/


	_export(_export.S, 'Math', {
	  RAD_PER_DEG: 180 / Math.PI
	});

	// https://rwaldron.github.io/proposal-math-extensions/


	var DEG_PER_RAD = Math.PI / 180;
	_export(_export.S, 'Math', {
	  radians: function radians(degrees) {
	    return degrees * DEG_PER_RAD;
	  }
	});

	// https://rwaldron.github.io/proposal-math-extensions/


	_export(_export.S, 'Math', {
	  scale: _mathScale
	});

	// https://gist.github.com/BrendanEich/4294d5c212a6d2254703


	_export(_export.S, 'Math', {
	  umulh: function umulh(u, v) {
	    var UINT16 = 0xffff;
	    var $u = +u;
	    var $v = +v;
	    var u0 = $u & UINT16;
	    var v0 = $v & UINT16;
	    var u1 = $u >>> 16;
	    var v1 = $v >>> 16;
	    var t = (u1 * v0 >>> 0) + (u0 * v0 >>> 16);
	    return u1 * v1 + (t >>> 16) + ((u0 * v1 >>> 0) + (t & UINT16) >>> 16);
	  }
	});

	// http://jfbastien.github.io/papers/Math.signbit.html


	_export(_export.S, 'Math', {
	  signbit: function signbit(x) {
	    // eslint-disable-next-line no-self-compare
	    return (x = +x) != x ? x : x == 0 ? 1 / x == Infinity : x > 0;
	  }
	});

	_export(_export.S, 'Promise', {
	  'try': function _try(callbackfn) {
	    var promiseCapability = _newPromiseCapability.f(this);
	    var result = _perform(callbackfn);
	    (result.e ? promiseCapability.reject : promiseCapability.resolve)(result.v);
	    return promiseCapability.promise;
	  }
	});

	var shared$1 = _shared('metadata');

	var store = shared$1.store || (shared$1.store = new (es6_weakMap)());

	var getOrCreateMetadataMap = function getOrCreateMetadataMap(target, targetKey, create) {
	  var targetMetadata = store.get(target);

	  if (!targetMetadata) {
	    if (!create) return undefined;
	    store.set(target, targetMetadata = new es6_map());
	  }

	  var keyMetadata = targetMetadata.get(targetKey);

	  if (!keyMetadata) {
	    if (!create) return undefined;
	    targetMetadata.set(targetKey, keyMetadata = new es6_map());
	  }

	  return keyMetadata;
	};

	var ordinaryHasOwnMetadata = function ordinaryHasOwnMetadata(MetadataKey, O, P) {
	  var metadataMap = getOrCreateMetadataMap(O, P, false);
	  return metadataMap === undefined ? false : metadataMap.has(MetadataKey);
	};

	var ordinaryGetOwnMetadata = function ordinaryGetOwnMetadata(MetadataKey, O, P) {
	  var metadataMap = getOrCreateMetadataMap(O, P, false);
	  return metadataMap === undefined ? undefined : metadataMap.get(MetadataKey);
	};

	var ordinaryDefineOwnMetadata = function ordinaryDefineOwnMetadata(MetadataKey, MetadataValue, O, P) {
	  getOrCreateMetadataMap(O, P, true).set(MetadataKey, MetadataValue);
	};

	var ordinaryOwnMetadataKeys = function ordinaryOwnMetadataKeys(target, targetKey) {
	  var metadataMap = getOrCreateMetadataMap(target, targetKey, false);
	  var keys = [];
	  if (metadataMap) metadataMap.forEach(function (_, key) {
	    keys.push(key);
	  });
	  return keys;
	};

	var toMetaKey = function toMetaKey(it) {
	  return it === undefined || babelHelpers.typeof(it) == 'symbol' ? it : String(it);
	};

	var exp$3 = function exp(O) {
	  _export(_export.S, 'Reflect', O);
	};

	var _metadata = {
	  store: store,
	  map: getOrCreateMetadataMap,
	  has: ordinaryHasOwnMetadata,
	  get: ordinaryGetOwnMetadata,
	  set: ordinaryDefineOwnMetadata,
	  keys: ordinaryOwnMetadataKeys,
	  key: toMetaKey,
	  exp: exp$3
	};

	var toMetaKey$1 = _metadata.key;
	var ordinaryDefineOwnMetadata$1 = _metadata.set;
	_metadata.exp({
	  defineMetadata: function defineMetadata(metadataKey, metadataValue, target, targetKey) {
	    ordinaryDefineOwnMetadata$1(metadataKey, metadataValue, _anObject(target), toMetaKey$1(targetKey));
	  }
	});

	var toMetaKey$2 = _metadata.key;
	var getOrCreateMetadataMap$1 = _metadata.map;
	var store$1 = _metadata.store;
	_metadata.exp({
	  deleteMetadata: function deleteMetadata(metadataKey, target
	  /* , targetKey */
	  ) {
	    var targetKey = arguments.length < 3 ? undefined : toMetaKey$2(arguments[2]);
	    var metadataMap = getOrCreateMetadataMap$1(_anObject(target), targetKey, false);
	    if (metadataMap === undefined || !metadataMap['delete'](metadataKey)) return false;
	    if (metadataMap.size) return true;
	    var targetMetadata = store$1.get(target);
	    targetMetadata['delete'](targetKey);
	    return !!targetMetadata.size || store$1['delete'](target);
	  }
	});

	var ordinaryHasOwnMetadata$1 = _metadata.has;
	var ordinaryGetOwnMetadata$1 = _metadata.get;
	var toMetaKey$3 = _metadata.key;

	var ordinaryGetMetadata = function ordinaryGetMetadata(MetadataKey, O, P) {
	  var hasOwn = ordinaryHasOwnMetadata$1(MetadataKey, O, P);
	  if (hasOwn) return ordinaryGetOwnMetadata$1(MetadataKey, O, P);
	  var parent = _objectGpo(O);
	  return parent !== null ? ordinaryGetMetadata(MetadataKey, parent, P) : undefined;
	};

	_metadata.exp({
	  getMetadata: function getMetadata(metadataKey, target
	  /* , targetKey */
	  ) {
	    return ordinaryGetMetadata(metadataKey, _anObject(target), arguments.length < 3 ? undefined : toMetaKey$3(arguments[2]));
	  }
	});

	var ordinaryOwnMetadataKeys$1 = _metadata.keys;
	var toMetaKey$4 = _metadata.key;

	var ordinaryMetadataKeys = function ordinaryMetadataKeys(O, P) {
	  var oKeys = ordinaryOwnMetadataKeys$1(O, P);
	  var parent = _objectGpo(O);
	  if (parent === null) return oKeys;
	  var pKeys = ordinaryMetadataKeys(parent, P);
	  return pKeys.length ? oKeys.length ? _arrayFromIterable(new es6_set(oKeys.concat(pKeys))) : pKeys : oKeys;
	};

	_metadata.exp({
	  getMetadataKeys: function getMetadataKeys(target
	  /* , targetKey */
	  ) {
	    return ordinaryMetadataKeys(_anObject(target), arguments.length < 2 ? undefined : toMetaKey$4(arguments[1]));
	  }
	});

	var ordinaryGetOwnMetadata$2 = _metadata.get;
	var toMetaKey$5 = _metadata.key;
	_metadata.exp({
	  getOwnMetadata: function getOwnMetadata(metadataKey, target
	  /* , targetKey */
	  ) {
	    return ordinaryGetOwnMetadata$2(metadataKey, _anObject(target), arguments.length < 3 ? undefined : toMetaKey$5(arguments[2]));
	  }
	});

	var ordinaryOwnMetadataKeys$2 = _metadata.keys;
	var toMetaKey$6 = _metadata.key;
	_metadata.exp({
	  getOwnMetadataKeys: function getOwnMetadataKeys(target
	  /* , targetKey */
	  ) {
	    return ordinaryOwnMetadataKeys$2(_anObject(target), arguments.length < 2 ? undefined : toMetaKey$6(arguments[1]));
	  }
	});

	var ordinaryHasOwnMetadata$2 = _metadata.has;
	var toMetaKey$7 = _metadata.key;

	var ordinaryHasMetadata = function ordinaryHasMetadata(MetadataKey, O, P) {
	  var hasOwn = ordinaryHasOwnMetadata$2(MetadataKey, O, P);
	  if (hasOwn) return true;
	  var parent = _objectGpo(O);
	  return parent !== null ? ordinaryHasMetadata(MetadataKey, parent, P) : false;
	};

	_metadata.exp({
	  hasMetadata: function hasMetadata(metadataKey, target
	  /* , targetKey */
	  ) {
	    return ordinaryHasMetadata(metadataKey, _anObject(target), arguments.length < 3 ? undefined : toMetaKey$7(arguments[2]));
	  }
	});

	var ordinaryHasOwnMetadata$3 = _metadata.has;
	var toMetaKey$8 = _metadata.key;
	_metadata.exp({
	  hasOwnMetadata: function hasOwnMetadata(metadataKey, target
	  /* , targetKey */
	  ) {
	    return ordinaryHasOwnMetadata$3(metadataKey, _anObject(target), arguments.length < 3 ? undefined : toMetaKey$8(arguments[2]));
	  }
	});

	var toMetaKey$9 = _metadata.key;
	var ordinaryDefineOwnMetadata$2 = _metadata.set;
	_metadata.exp({
	  metadata: function metadata(metadataKey, metadataValue) {
	    return function decorator(target, targetKey) {
	      ordinaryDefineOwnMetadata$2(metadataKey, metadataValue, (targetKey !== undefined ? _anObject : _aFunction)(target), toMetaKey$9(targetKey));
	    };
	  }
	});

	// https://github.com/rwaldron/tc39-notes/blob/master/es6/2014-09/sept-25.md#510-globalasap-for-enqueuing-a-microtask


	var microtask$1 = _microtask();

	var process$3 = _global.process;

	var isNode$2 = _cof(process$3) == 'process';
	_export(_export.G, {
	  asap: function asap(fn) {
	    var domain = isNode$2 && process$3.domain;
	    microtask$1(domain ? domain.bind(fn) : fn);
	  }
	});

	var microtask$2 = _microtask();

	var OBSERVABLE = _wks('observable');













	var RETURN = _forOf.RETURN;

	var getMethod = function getMethod(fn) {
	  return fn == null ? undefined : _aFunction(fn);
	};

	var cleanupSubscription = function cleanupSubscription(subscription) {
	  var cleanup = subscription._c;

	  if (cleanup) {
	    subscription._c = undefined;
	    cleanup();
	  }
	};

	var subscriptionClosed = function subscriptionClosed(subscription) {
	  return subscription._o === undefined;
	};

	var closeSubscription = function closeSubscription(subscription) {
	  if (!subscriptionClosed(subscription)) {
	    subscription._o = undefined;
	    cleanupSubscription(subscription);
	  }
	};

	var Subscription = function Subscription(observer, subscriber) {
	  _anObject(observer);
	  this._c = undefined;
	  this._o = observer;
	  observer = new SubscriptionObserver(this);

	  try {
	    var cleanup = subscriber(observer);
	    var subscription = cleanup;

	    if (cleanup != null) {
	      if (typeof cleanup.unsubscribe === 'function') cleanup = function cleanup() {
	        subscription.unsubscribe();
	      };else _aFunction(cleanup);
	      this._c = cleanup;
	    }
	  } catch (e) {
	    observer.error(e);
	    return;
	  }

	  if (subscriptionClosed(this)) cleanupSubscription(this);
	};

	Subscription.prototype = _redefineAll({}, {
	  unsubscribe: function unsubscribe() {
	    closeSubscription(this);
	  }
	});

	var SubscriptionObserver = function SubscriptionObserver(subscription) {
	  this._s = subscription;
	};

	SubscriptionObserver.prototype = _redefineAll({}, {
	  next: function next(value) {
	    var subscription = this._s;

	    if (!subscriptionClosed(subscription)) {
	      var observer = subscription._o;

	      try {
	        var m = getMethod(observer.next);
	        if (m) return m.call(observer, value);
	      } catch (e) {
	        try {
	          closeSubscription(subscription);
	        } finally {
	          throw e;
	        }
	      }
	    }
	  },
	  error: function error(value) {
	    var subscription = this._s;
	    if (subscriptionClosed(subscription)) throw value;
	    var observer = subscription._o;
	    subscription._o = undefined;

	    try {
	      var m = getMethod(observer.error);
	      if (!m) throw value;
	      value = m.call(observer, value);
	    } catch (e) {
	      try {
	        cleanupSubscription(subscription);
	      } finally {
	        throw e;
	      }
	    }

	    cleanupSubscription(subscription);
	    return value;
	  },
	  complete: function complete(value) {
	    var subscription = this._s;

	    if (!subscriptionClosed(subscription)) {
	      var observer = subscription._o;
	      subscription._o = undefined;

	      try {
	        var m = getMethod(observer.complete);
	        value = m ? m.call(observer, value) : undefined;
	      } catch (e) {
	        try {
	          cleanupSubscription(subscription);
	        } finally {
	          throw e;
	        }
	      }

	      cleanupSubscription(subscription);
	      return value;
	    }
	  }
	});

	var $Observable = function Observable(subscriber) {
	  _anInstance(this, $Observable, 'Observable', '_f')._f = _aFunction(subscriber);
	};

	_redefineAll($Observable.prototype, {
	  subscribe: function subscribe(observer) {
	    return new Subscription(observer, this._f);
	  },
	  forEach: function forEach(fn) {
	    var that = this;
	    return new (_core.Promise || _global.Promise)(function (resolve, reject) {
	      _aFunction(fn);
	      var subscription = that.subscribe({
	        next: function next(value) {
	          try {
	            return fn(value);
	          } catch (e) {
	            reject(e);
	            subscription.unsubscribe();
	          }
	        },
	        error: reject,
	        complete: resolve
	      });
	    });
	  }
	});
	_redefineAll($Observable, {
	  from: function from(x) {
	    var C = typeof this === 'function' ? this : $Observable;
	    var method = getMethod(_anObject(x)[OBSERVABLE]);

	    if (method) {
	      var observable = _anObject(method.call(x));
	      return observable.constructor === C ? observable : new C(function (observer) {
	        return observable.subscribe(observer);
	      });
	    }

	    return new C(function (observer) {
	      var done = false;
	      microtask$2(function () {
	        if (!done) {
	          try {
	            if (_forOf(x, false, function (it) {
	              observer.next(it);
	              if (done) return RETURN;
	            }) === RETURN) return;
	          } catch (e) {
	            if (done) throw e;
	            observer.error(e);
	            return;
	          }

	          observer.complete();
	        }
	      });
	      return function () {
	        done = true;
	      };
	    });
	  },
	  of: function of() {
	    for (var i = 0, l = arguments.length, items = new Array(l); i < l;) {
	      items[i] = arguments[i++];
	    }

	    return new (typeof this === 'function' ? this : $Observable)(function (observer) {
	      var done = false;
	      microtask$2(function () {
	        if (!done) {
	          for (var j = 0; j < items.length; ++j) {
	            observer.next(items[j]);
	            if (done) return;
	          }

	          observer.complete();
	        }
	      });
	      return function () {
	        done = true;
	      };
	    });
	  }
	});
	_hide($Observable.prototype, OBSERVABLE, function () {
	  return this;
	});
	_export(_export.G, {
	  Observable: $Observable
	});

	_setSpecies('Observable');

	// ie9- setTimeout & setInterval additional parameters fix






	var slice = [].slice;
	var MSIE = /MSIE .\./.test(_userAgent); // <- dirty ie9- check

	var wrap$1 = function wrap(set) {
	  return function (fn, time
	  /* , ...args */
	  ) {
	    var boundArgs = arguments.length > 2;
	    var args = boundArgs ? slice.call(arguments, 2) : false;
	    return set(boundArgs ? function () {
	      // eslint-disable-next-line no-new-func
	      (typeof fn == 'function' ? fn : Function(fn)).apply(this, args);
	    } : fn, time);
	  };
	};

	_export(_export.G + _export.B + _export.F * MSIE, {
	  setTimeout: wrap$1(_global.setTimeout),
	  setInterval: wrap$1(_global.setInterval)
	});

	_export(_export.G + _export.B, {
	  setImmediate: _task.set,
	  clearImmediate: _task.clear
	});

	var ITERATOR$4 = _wks('iterator');
	var TO_STRING_TAG = _wks('toStringTag');
	var ArrayValues = _iterators.Array;
	var DOMIterables = {
	  CSSRuleList: true,
	  // TODO: Not spec compliant, should be false.
	  CSSStyleDeclaration: false,
	  CSSValueList: false,
	  ClientRectList: false,
	  DOMRectList: false,
	  DOMStringList: false,
	  DOMTokenList: true,
	  DataTransferItemList: false,
	  FileList: false,
	  HTMLAllCollection: false,
	  HTMLCollection: false,
	  HTMLFormElement: false,
	  HTMLSelectElement: false,
	  MediaList: true,
	  // TODO: Not spec compliant, should be false.
	  MimeTypeArray: false,
	  NamedNodeMap: false,
	  NodeList: true,
	  PaintRequestList: false,
	  Plugin: false,
	  PluginArray: false,
	  SVGLengthList: false,
	  SVGNumberList: false,
	  SVGPathSegList: false,
	  SVGPointList: false,
	  SVGStringList: false,
	  SVGTransformList: false,
	  SourceBufferList: false,
	  StyleSheetList: true,
	  // TODO: Not spec compliant, should be false.
	  TextTrackCueList: false,
	  TextTrackList: false,
	  TouchList: false
	};

	for (var collections = _objectKeys(DOMIterables), i$2 = 0; i$2 < collections.length; i$2++) {
	  var NAME$1 = collections[i$2];
	  var explicit = DOMIterables[NAME$1];
	  var Collection = _global[NAME$1];
	  var proto$3 = Collection && Collection.prototype;
	  var key$1;

	  if (proto$3) {
	    if (!proto$3[ITERATOR$4]) _hide(proto$3, ITERATOR$4, ArrayValues);
	    if (!proto$3[TO_STRING_TAG]) _hide(proto$3, TO_STRING_TAG, NAME$1);
	    _iterators[NAME$1] = ArrayValues;
	    if (explicit) for (key$1 in es6_array_iterator) {
	      if (!proto$3[key$1]) _redefine(proto$3, key$1, es6_array_iterator[key$1], true);
	    }
	  }
	}

	if (window._main_core_polyfill) {
	  console.warn('main.core.polyfill is loaded more than once on this page');
	}

	window._main_core_polyfill = true;

}((this.window = this.window || {})));



/**
 * Element.prototype.matches polyfill
 */
;(function(element) {
	'use strict';

	if (!element.matches && element.matchesSelector)
	{
		element.matches = element.matchesSelector;
	}

	if (!element.matches)
	{
		element.matches = function(selector) {
			var matches = document.querySelectorAll(selector);
			var self = this;

			return Array.prototype.some.call(matches, function(e) {
				return e === self;
			});
		};
	}

})(Element.prototype);

;(function() {
	'use strict';

	if (!Element.prototype.closest)
	{
		/**
		 * Finds closest parent element by selector
		 * @param {string} selector
		 * @return {HTMLElement|Element|Node}
		 */
		Object.defineProperty(Element.prototype, 'closest', {
			enumerable: false,
			value: function(selector) {
				var node = this;

				while (node)
				{
					if (node.matches(selector))
					{
						return node;
					}

					node = node.parentElement;
				}

				return null;
			},
		});
	}
})();

(function (exports) {
	'use strict';

	if (!window.DOMRect || typeof DOMRect.prototype.toJSON !== 'function' || typeof DOMRect.fromRect !== 'function') {
	  window.DOMRect =
	  /*#__PURE__*/
	  function () {
	    function DOMRect(x, y, width, height) {
	      babelHelpers.classCallCheck(this, DOMRect);
	      this.x = x || 0;
	      this.y = y || 0;
	      this.width = width || 0;
	      this.height = height || 0;
	    }

	    babelHelpers.createClass(DOMRect, [{
	      key: "toJSON",
	      value: function toJSON() {
	        return {
	          top: this.top,
	          left: this.left,
	          right: this.right,
	          bottom: this.bottom,
	          width: this.width,
	          height: this.height,
	          x: this.x,
	          y: this.y
	        };
	      }
	    }, {
	      key: "top",
	      get: function get() {
	        return this.y;
	      }
	    }, {
	      key: "left",
	      get: function get() {
	        return this.x;
	      }
	    }, {
	      key: "right",
	      get: function get() {
	        return this.x + this.width;
	      }
	    }, {
	      key: "bottom",
	      get: function get() {
	        return this.y + this.height;
	      }
	    }], [{
	      key: "fromRect",
	      value: function fromRect(otherRect) {
	        return new DOMRect(otherRect.x, otherRect.y, otherRect.width, otherRect.height);
	      }
	    }]);
	    return DOMRect;
	  }();
	}

}((this.window = this.window || {})));




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
	  Object.keys(bxTmp).forEach(function (key) {
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

	var objectCtorString = Function.prototype.toString.call(Object);
	/**
	 * @memberOf BX
	 */

	var Type =
	/*#__PURE__*/
	function () {
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
	      return this.isString(value) && value !== '';
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
	      return !!value && (babelHelpers.typeof(value) === 'object' || typeof value === 'function');
	    }
	    /**
	     * Checks that value is object like
	     * @param value
	     * @return {boolean}
	     */

	  }, {
	    key: "isObjectLike",
	    value: function isObjectLike(value) {
	      return !!value && babelHelpers.typeof(value) === 'object';
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

	      var proto = Object.getPrototypeOf(value);

	      if (proto === null) {
	        return true;
	      }

	      var ctor = proto.hasOwnProperty('constructor') && proto.constructor;
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
	      return this.isArray(value) && value.length > 0;
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
	      var regExpTypedTag = /^\[object (?:Float(?:32|64)|(?:Int|Uint)(?:8|16|32)|Uint8Clamped)]$/;
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
	      return Type.isBlob(value) && Type.isObjectLike(value.lastModifiedDate) && Type.isNumber(value.lastModified) && Type.isString(value.name);
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

	var Reflection =
	/*#__PURE__*/
	function () {
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
	        var classFn = null;
	        var currentNamespace = window;
	        var namespaces = className.split('.');

	        for (var i = 0; i < namespaces.length; i += 1) {
	          var namespace = namespaces[i];

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
	      var parts = namespaceName.split('.');
	      var parent = window.BX;

	      if (parts[0] === 'BX') {
	        parts = parts.slice(1);
	      }

	      for (var i = 0; i < parts.length; i += 1) {
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

	var reEscape = /[&<>'"]/g;
	var reUnescape = /&(?:amp|#38|lt|#60|gt|#62|apos|#39|quot|#34);/g;
	var escapeEntities = {
	  '&': '&amp;',
	  '<': '&lt;',
	  '>': '&gt;',
	  "'": '&#39;',
	  '"': '&quot;'
	};
	var unescapeEntities = {
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

	var Text =
	/*#__PURE__*/
	function () {
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
	        return value.replace(reEscape, function (item) {
	          return escapeEntities[item];
	        });
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
	        return value.replace(reUnescape, function (item) {
	          return unescapeEntities[item];
	        });
	      }

	      return value;
	    }
	  }, {
	    key: "getRandom",
	    value: function getRandom() {
	      var length = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 8;
	      // eslint-disable-next-line
	      return babelHelpers.toConsumableArray(Array(length)).map(function () {
	        return (~~(Math.random() * 36)).toString(36);
	      }).join('');
	    }
	  }, {
	    key: "toNumber",
	    value: function toNumber(value) {
	      var parsedValue = Number.parseFloat(value);

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
	    value: function toBoolean(value) {
	      var trueValues = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : [];
	      var transformedValue = Type.isString(value) ? value.toLowerCase() : value;
	      return ['true', 'y', '1', 1, true].concat(babelHelpers.toConsumableArray(trueValues)).includes(transformedValue);
	    }
	  }, {
	    key: "toCamelCase",
	    value: function toCamelCase(str) {
	      if (!Type.isStringFilled(str)) {
	        return str;
	      }

	      var regex = /[-_\s]+(.)?/g;

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

	      var matches = str.match(/[A-Z]{2,}(?=[A-Z][a-z]+[0-9]*|\b)|[A-Z]?[a-z]+[0-9]*|[A-Z]|[0-9]+/g);

	      if (!matches) {
	        return str;
	      }

	      return matches.map(function (x) {
	        return x.toLowerCase();
	      }).join('-');
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

	var aliases = {
	  mousewheel: ['DOMMouseScroll'],
	  bxchange: ['change', 'cut', 'paste', 'drop', 'keyup'],
	  animationend: ['animationend', 'oAnimationEnd', 'webkitAnimationEnd', 'MSAnimationEnd'],
	  transitionend: ['webkitTransitionEnd', 'otransitionend', 'oTransitionEnd', 'msTransitionEnd', 'transitionend'],
	  fullscreenchange: ['fullscreenchange', 'webkitfullscreenchange', 'mozfullscreenchange', 'MSFullscreenChange'],
	  fullscreenerror: ['fullscreenerror', 'webkitfullscreenerror', 'mozfullscreenerror', 'MSFullscreenError']
	};

	var Registry =
	/*#__PURE__*/
	function () {
	  function Registry() {
	    babelHelpers.classCallCheck(this, Registry);
	    babelHelpers.defineProperty(this, "registry", new WeakMap());
	  }

	  babelHelpers.createClass(Registry, [{
	    key: "set",
	    value: function set(target, event, listener) {
	      var events = this.get(target);

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
	        var events = this.registry.get(target);

	        if (Type.isPlainObject(events) && Type.isSet(events[event])) {
	          events[event].delete(listener);
	        }

	        return;
	      }

	      if (Type.isString(event)) {
	        var _events = this.registry.get(target);

	        if (Type.isPlainObject(_events) && Type.isSet(_events[event])) {
	          _events[event] = new Set();
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
	  var isSupported = false;

	  try {
	    var options = Object.defineProperty({}, name, {
	      get: function get() {
	        isSupported = true;
	        return undefined;
	      }
	    });
	    window.addEventListener('test', null, options);
	  } // eslint-disable-next-line
	  catch (err) {}

	  return isSupported;
	}

	function fetchSupportedListenerOptions(options) {
	  if (!Type.isPlainObject(options)) {
	    return options;
	  }

	  return Object.keys(options).reduce(function (acc, name) {
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

	  var listenerOptions = fetchSupportedListenerOptions(options);

	  if (eventName in aliases) {
	    aliases[eventName].forEach(function (key) {
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

	  var listenerOptions = fetchSupportedListenerOptions(options);

	  if (eventName in aliases) {
	    aliases[eventName].forEach(function (key) {
	      target.removeEventListener(key, handler, listenerOptions);
	      registry.delete(target, key, handler);
	    });
	    return;
	  }

	  target.removeEventListener(eventName, handler, listenerOptions);
	  registry.delete(target, eventName, handler);
	}

	function unbindAll(target, eventName) {
	  var events = registry.get(target);
	  Object.keys(events).forEach(function (currentEvent) {
	    events[currentEvent].forEach(function (handler) {
	      if (!Type.isString(eventName) || eventName === currentEvent) {
	        unbind(target, currentEvent, handler);
	      }
	    });
	  });
	}

	function bindOnce(target, eventName, handler, options) {
	  var once = function once() {
	    unbind(target, eventName, once, options);
	    handler.apply(void 0, arguments);
	  };

	  bind(target, eventName, once, options);
	}

	var debugState = true;
	function enableDebug() {
	  debugState = true;
	}
	function disableDebug() {
	  debugState = false;
	}
	function isDebugEnabled() {
	  return debugState;
	}
	function debug() {
	  if (isDebugEnabled() && Type.isObject(window.console)) {
	    if (Type.isFunction(window.console.log)) {
	      for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
	        args[_key] = arguments[_key];
	      }

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

	function fetchExtensionSettings(html) {
	  if (Type.isStringFilled(html)) {
	    var scripts = html.match(/<script type="extension\/settings" \b[^>]*>([\s\S]*?)<\/script>/g);

	    if (Type.isArrayFilled(scripts)) {
	      return scripts.map(function (script) {
	        var _script$match = script.match(/data-extension="(.[a-z0-9_.-]+)"/),
	            _script$match2 = babelHelpers.slicedToArray(_script$match, 2),
	            extension = _script$match2[1];

	        return {
	          extension: extension,
	          script: script
	        };
	      });
	    }
	  }

	  return [];
	}

	var Extension =
	/*#__PURE__*/
	function () {
	  function Extension(options) {
	    babelHelpers.classCallCheck(this, Extension);
	    this.config = options.config || {};
	    this.name = options.extension;
	    this.state = 'scheduled'; // eslint-disable-next-line

	    var result = BX.processHTML(options.html || '');
	    this.inlineScripts = result.SCRIPT.reduce(inlineScripts, []);
	    this.externalScripts = result.SCRIPT.reduce(externalScripts, []);
	    this.externalStyles = result.STYLE.reduce(externalStyles, []);
	    this.settingsScripts = fetchExtensionSettings(result.HTML);
	  }

	  babelHelpers.createClass(Extension, [{
	    key: "load",
	    value: function load() {
	      var _this = this;

	      if (this.state === 'error') {
	        this.loadPromise = this.loadPromise || Promise.resolve(this);
	        console.warn('Extension', this.name, 'not found');
	      }

	      if (!this.loadPromise && this.state) {
	        this.state = 'load';
	        this.settingsScripts.forEach(function (entry) {
	          var isLoaded = !!document.querySelector("script[data-extension=\"".concat(entry.extension, "\"]"));

	          if (!isLoaded) {
	            document.body.insertAdjacentHTML('beforeend', entry.script);
	          }
	        });
	        this.inlineScripts.forEach(BX.evalGlobal);
	        this.loadPromise = Promise.all([loadAll(this.externalScripts), loadAll(this.externalStyles)]).then(function () {
	          _this.state = 'loaded';

	          if (Type.isPlainObject(_this.config) && _this.config.namespace) {
	            return Reflection.getClass(_this.config.namespace);
	          }

	          return window;
	        });
	      }

	      return this.loadPromise;
	    }
	  }]);
	  return Extension;
	}();

	var initialized = {};
	var ajaxController = 'main.bitrix.main.controller.loadext.getextensions';

	function makeIterable(value) {
	  return Type.isArray(value) ? value : [value];
	}
	function isInitialized(extension) {
	  return extension in initialized;
	}
	function getInitialized(extension) {
	  return initialized[extension];
	}
	function isAllInitialized(extensions) {
	  return extensions.every(isInitialized);
	}
	function loadExtensions(extensions) {
	  return Promise.all(extensions.map(function (item) {
	    return item.load();
	  }));
	}
	function mergeExports(exports) {
	  return exports.reduce(function (acc, currentExports) {
	    if (Type.isObject(currentExports)) {
	      return babelHelpers.objectSpread({}, currentExports);
	    }

	    return currentExports;
	  }, {});
	}
	function inlineScripts(acc, item) {
	  if (item.isInternal) {
	    acc.push(item.JS);
	  }

	  return acc;
	}
	function externalScripts(acc, item) {
	  if (!item.isInternal) {
	    acc.push(item.JS);
	  }

	  return acc;
	}
	function externalStyles(acc, item) {
	  if (Type.isString(item) && item !== '') {
	    acc.push(item);
	  }

	  return acc;
	}
	function request(options) {
	  return new Promise(function (resolve) {
	    // eslint-disable-next-line
	    BX.ajax.runAction(ajaxController, {
	      data: options
	    }).then(resolve);
	  });
	}
	function prepareExtensions(response) {
	  if (response.status !== 'success') {
	    response.errors.map(console.warn);
	    return [];
	  }

	  return response.data.map(function (item) {
	    var initializedExtension = getInitialized(item.extension);

	    if (initializedExtension) {
	      return initializedExtension;
	    }

	    initialized[item.extension] = new Extension(item);
	    return initialized[item.extension];
	  });
	}
	function loadAll(items) {
	  var itemsList = makeIterable(items);

	  if (!itemsList.length) {
	    return Promise.resolve();
	  }

	  return new Promise(function (resolve) {
	    // eslint-disable-next-line
	    BX.load(itemsList, resolve);
	  });
	}

	/**
	 * Loads extensions asynchronously
	 * @param {string|Array<string>} extension
	 * @return {Promise<Array<Extension>>}
	 */
	function loadExtension(extension) {
	  var extensions = makeIterable(extension);
	  var isAllInitialized$1 = isAllInitialized(extensions);

	  if (isAllInitialized$1) {
	    var initializedExtensions = extensions.map(getInitialized);
	    return loadExtensions(initializedExtensions).then(mergeExports);
	  }

	  return request({
	    extension: extensions
	  }).then(prepareExtensions).then(loadExtensions).then(mergeExports);
	}

	var cloneableTags = ['[object Object]', '[object Array]', '[object RegExp]', '[object Arguments]', '[object Date]', '[object Error]', '[object Map]', '[object Set]', '[object ArrayBuffer]', '[object DataView]', '[object Float32Array]', '[object Float64Array]', '[object Int8Array]', '[object Int16Array]', '[object Int32Array]', '[object Uint8Array]', '[object Uint16Array]', '[object Uint32Array]', '[object Uint8ClampedArray]'];

	function isCloneable(value) {
	  var isCloneableValue = Type.isObjectLike(value) && cloneableTags.includes(getTag(value));
	  return isCloneableValue || Type.isDomNode(value);
	}

	function internalClone(value, map) {
	  if (map.has(value)) {
	    return map.get(value);
	  }

	  if (isCloneable(value)) {
	    if (Type.isArray(value)) {
	      var cloned = Array.from(value);
	      map.set(value, cloned);
	      value.forEach(function (item, index) {
	        cloned[index] = internalClone(item, map);
	      });
	      return map.get(value);
	    }

	    if (Type.isDomNode(value)) {
	      return value.cloneNode(true);
	    }

	    if (Type.isMap(value)) {
	      var _result = new Map();

	      map.set(value, _result);
	      value.forEach(function (item, key) {
	        _result.set(internalClone(key, map), internalClone(item, map));
	      });
	      return _result;
	    }

	    if (Type.isSet(value)) {
	      var _result2 = new Set();

	      map.set(value, _result2);
	      value.forEach(function (item) {
	        _result2.add(internalClone(item, map));
	      });
	      return _result2;
	    }

	    if (Type.isDate(value)) {
	      return new Date(value);
	    }

	    if (Type.isRegExp(value)) {
	      var regExpFlags = /\w*$/;
	      var flags = regExpFlags.exec(value);

	      var _result3 = new RegExp(value.source);

	      if (flags && Type.isArray(flags)) {
	        _result3 = new RegExp(value.source, flags[0]);
	      }

	      _result3.lastIndex = value.lastIndex;
	      return _result3;
	    }

	    var proto = Object.getPrototypeOf(value);
	    var result = Object.assign(Object.create(proto), value);
	    map.set(value, result);
	    Object.keys(value).forEach(function (key) {
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
	  return Object.entries(target).reduce(function (acc, _ref) {
	    var _ref2 = babelHelpers.slicedToArray(_ref, 2),
	        key = _ref2[0],
	        value = _ref2[1];

	    if (!Type.isDomNode(acc[key]) && Type.isObjectLike(acc[key]) && Type.isObjectLike(value)) {
	      acc[key] = merge(acc[key], value);
	      return acc;
	    }

	    acc[key] = value;
	    return acc;
	  }, current);
	}

	function createComparator(fields) {
	  var orders = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : [];
	  return function (a, b) {
	    var field = fields[0];
	    var order = orders[0] || 'asc';

	    if (Type.isUndefined(field)) {
	      return 0;
	    }

	    var valueA = a[field];
	    var valueB = b[field];

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

	/**
	 * @memberOf BX
	 */

	var Runtime =
	/*#__PURE__*/
	function () {
	  function Runtime() {
	    babelHelpers.classCallCheck(this, Runtime);
	  }

	  babelHelpers.createClass(Runtime, null, [{
	    key: "debounce",
	    value: function debounce(func) {
	      var wait = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 0;
	      var context = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : null;
	      var timeoutId;
	      return function debounced() {
	        var _this = this;

	        for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
	          args[_key] = arguments[_key];
	        }

	        if (Type.isNumber(timeoutId)) {
	          clearTimeout(timeoutId);
	        }

	        timeoutId = setTimeout(function () {
	          func.apply(context || _this, args);
	        }, wait);
	      };
	    }
	  }, {
	    key: "throttle",
	    value: function throttle(func) {
	      var wait = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 0;
	      var context = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : null;
	      var timer = 0;
	      var invoke;
	      return function wrapper() {
	        for (var _len2 = arguments.length, args = new Array(_len2), _key2 = 0; _key2 < _len2; _key2++) {
	          args[_key2] = arguments[_key2];
	        }

	        invoke = true;

	        if (!timer) {
	          var q = function q() {
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
	    value: function html(node, _html) {
	      var params = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};

	      if (Type.isNil(_html) && Type.isDomNode(node)) {
	        return node.innerHTML;
	      } // eslint-disable-next-line


	      var parsedHtml = BX.processHTML(_html);
	      var externalCss = parsedHtml.STYLE.reduce(externalStyles, []);
	      var externalJs = parsedHtml.SCRIPT.reduce(externalScripts, []);
	      var inlineJs = parsedHtml.SCRIPT.reduce(inlineScripts, []);

	      if (Type.isDomNode(node)) {
	        if (params.htmlFirst || !externalJs.length && !externalCss.length) {
	          node.innerHTML = parsedHtml.HTML;
	        }
	      }

	      return Promise.all([loadAll(externalJs), loadAll(externalCss)]).then(function () {
	        if (Type.isDomNode(node) && (externalJs.length > 0 || externalCss.length > 0)) {
	          node.innerHTML = parsedHtml.HTML;
	        } // eslint-disable-next-line


	        inlineJs.forEach(function (script) {
	          return BX.evalGlobal(script);
	        });

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
	    value: function merge$1() {
	      for (var _len3 = arguments.length, targets = new Array(_len3), _key3 = 0; _key3 < _len3; _key3++) {
	        targets[_key3] = arguments[_key3];
	      }

	      if (Type.isArray(targets[0])) {
	        targets.unshift([]);
	      } else if (Type.isObject(targets[0])) {
	        targets.unshift({});
	      }

	      return targets.reduce(function (acc, item) {
	        return merge(acc, item);
	      }, targets[0]);
	    }
	  }, {
	    key: "orderBy",
	    value: function orderBy(collection) {
	      var fields = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : [];
	      var orders = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : [];
	      var comparator = createComparator(fields, orders);
	      return Object.values(collection).sort(comparator);
	    }
	  }, {
	    key: "destroy",
	    value: function destroy(target) {
	      var errorMessage = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 'Object is destroyed';

	      if (Type.isObject(target)) {
	        var onPropertyAccess = function onPropertyAccess() {
	          throw new Error(errorMessage);
	        };

	        var ownProperties = Object.keys(target);

	        var prototypeProperties = function () {
	          var targetPrototype = Object.getPrototypeOf(target);

	          if (Type.isObject(targetPrototype)) {
	            return Object.getOwnPropertyNames(targetPrototype);
	          }

	          return [];
	        }();

	        var uniquePropertiesList = babelHelpers.toConsumableArray(new Set([].concat(babelHelpers.toConsumableArray(ownProperties), babelHelpers.toConsumableArray(prototypeProperties))));
	        uniquePropertiesList.filter(function (name) {
	          var descriptor = Object.getOwnPropertyDescriptor(target, name);
	          return !/__(.+)__/.test(name) && (!Type.isObject(descriptor) || descriptor.configurable === true);
	        }).forEach(function (name) {
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
	babelHelpers.defineProperty(Runtime, "clone", clone);

	var _isError = Symbol.for('BX.BaseError.isError');
	/**
	 * @memberOf BX
	 */


	var BaseError =
	/*#__PURE__*/
	function () {
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
	      var code = this.getCode();
	      var message = this.getMessage();

	      if (!Type.isStringFilled(code) && !Type.isStringFilled(message)) {
	        return '';
	      } else if (!Type.isStringFilled(code)) {
	        return "Error: ".concat(message);
	      } else if (!Type.isStringFilled(message)) {
	        return code;
	      } else {
	        return "".concat(code, ": ").concat(message);
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

	var BaseEvent =
	/*#__PURE__*/
	function () {
	  function BaseEvent() {
	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {
	      data: {}
	    };
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

	var EventStore =
	/*#__PURE__*/
	function () {
	  function EventStore() {
	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, EventStore);
	    this.defaultMaxListeners = Type.isNumber(options.defaultMaxListeners) ? options.defaultMaxListeners : 10;
	    this.eventStore = new WeakMap();
	  }

	  babelHelpers.createClass(EventStore, [{
	    key: "add",
	    value: function add(target) {
	      var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	      var record = this.getRecordScheme();

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
	    value: function getOrAdd(target) {
	      var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
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

	var WarningStore =
	/*#__PURE__*/
	function () {
	  function WarningStore() {
	    babelHelpers.classCallCheck(this, WarningStore);
	    this.warnings = new Map();
	    this.printDelayed = Runtime.debounce(this.print.bind(this), 500);
	  }

	  babelHelpers.createClass(WarningStore, [{
	    key: "add",
	    value: function add(target, eventName, listeners) {
	      var contextWarnings = this.warnings.get(target);

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
	      var _iteratorNormalCompletion = true;
	      var _didIteratorError = false;
	      var _iteratorError = undefined;

	      try {
	        for (var _iterator = this.warnings[Symbol.iterator](), _step; !(_iteratorNormalCompletion = (_step = _iterator.next()).done); _iteratorNormalCompletion = true) {
	          var _step$value = babelHelpers.slicedToArray(_step.value, 2),
	              target = _step$value[0],
	              warnings = _step$value[1];

	          for (var eventName in warnings) {
	            console.groupCollapsed('Possible BX.Event.EventEmitter memory leak detected. ' + warnings[eventName].size + ' "' + eventName + '" listeners added. ' + 'Use emitter.setMaxListeners() to increase limit.');
	            console.dir(warnings[eventName].errors);
	            console.groupEnd();
	          }
	        }
	      } catch (err) {
	        _didIteratorError = true;
	        _iteratorError = err;
	      } finally {
	        try {
	          if (!_iteratorNormalCompletion && _iterator.return != null) {
	            _iterator.return();
	          }
	        } finally {
	          if (_didIteratorError) {
	            throw _iteratorError;
	          }
	        }
	      }

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

	var eventStore = new EventStore({
	  defaultMaxListeners: 10
	});
	var warningStore = new WarningStore();
	var aliasStore = new Map();
	var globalTarget = {
	  GLOBAL_TARGET: 'GLOBAL_TARGET' // this key only for debugging purposes

	};
	eventStore.add(globalTarget, {
	  maxListeners: 25
	});
	var isEmitterProperty = Symbol.for('BX.Event.EventEmitter.isEmitter');
	var namespaceProperty = Symbol('namespaceProperty');
	var targetProperty = Symbol('targetProperty');

	var EventEmitter =
	/*#__PURE__*/
	function () {
	  /** @private */
	  function EventEmitter() {
	    var _this = this;

	    babelHelpers.classCallCheck(this, EventEmitter);
	    this[targetProperty] = null;
	    this[namespaceProperty] = null;
	    this[isEmitterProperty] = true;
	    var target = this;

	    if (Object.getPrototypeOf(this) === EventEmitter.prototype && arguments.length > 0) //new EventEmitter(obj) case
	      {
	        if (!Type.isObject(arguments.length <= 0 ? undefined : arguments[0])) {
	          throw new TypeError("The \"target\" argument must be an object.");
	        }

	        target = arguments.length <= 0 ? undefined : arguments[0];
	        this.setEventNamespace(arguments.length <= 1 ? undefined : arguments[1]);
	      }

	    this[targetProperty] = target;
	    setTimeout(function () {
	      if (_this.getEventNamespace() === null) {
	        console.warn('The instance of BX.Event.EventEmitter is supposed to have an event namespace. ' + 'Use emitter.setEventNamespace() to make events more unique.');
	      }
	    }, 500);
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
	      var _this2 = this;

	      if (!Type.isPlainObject(options)) {
	        return;
	      }

	      aliases = Type.isPlainObject(aliases) ? EventEmitter.normalizeAliases(aliases) : {};
	      Object.keys(options).forEach(function (eventName) {
	        var listener = options[eventName];

	        if (!Type.isFunction(listener)) {
	          throw new TypeError("The \"listener\" argument must be of type Function. Received type ".concat(babelHelpers.typeof(listener), "."));
	        }

	        eventName = EventEmitter.normalizeEventName(eventName);

	        if (aliases[eventName]) {
	          var actualName = aliases[eventName].eventName;
	          EventEmitter.subscribe(_this2, actualName, listener, {
	            compatMode: compatMode !== false
	          });
	        } else {
	          EventEmitter.subscribe(_this2, eventName, listener, {
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
	    value: function setMaxListeners() {
	      for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
	        args[_key] = arguments[_key];
	      }

	      EventEmitter.setMaxListeners.apply(EventEmitter, [this].concat(args));
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
	    value: function incrementMaxListeners() {
	      for (var _len2 = arguments.length, args = new Array(_len2), _key2 = 0; _key2 < _len2; _key2++) {
	        args[_key2] = arguments[_key2];
	      }

	      return EventEmitter.incrementMaxListeners.apply(EventEmitter, [this].concat(args));
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
	    value: function decrementMaxListeners() {
	      for (var _len3 = arguments.length, args = new Array(_len3), _key3 = 0; _key3 < _len3; _key3++) {
	        args[_key3] = arguments[_key3];
	      }

	      return EventEmitter.decrementMaxListeners.apply(EventEmitter, [this].concat(args));
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
	        throw new TypeError("The \"eventName\" argument must be a string.");
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

	      var targetProto = Object.getPrototypeOf(target);
	      var emitter = new EventEmitter();
	      emitter.setEventNamespace(namespace);
	      Object.setPrototypeOf(emitter, targetProto);
	      Object.setPrototypeOf(target, emitter);
	      Object.getOwnPropertyNames(EventEmitter.prototype).forEach(function (method) {
	        if (['constructor'].includes(method)) {
	          return;
	        }

	        emitter[method] = function () {
	          for (var _len4 = arguments.length, args = new Array(_len4), _key4 = 0; _key4 < _len4; _key4++) {
	            args[_key4] = arguments[_key4];
	          }

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
	        throw new TypeError("The \"target\" argument must be an object.");
	      }

	      eventName = this.normalizeEventName(eventName);

	      if (!Type.isStringFilled(eventName)) {
	        throw new TypeError("The \"eventName\" argument must be a string.");
	      }

	      if (!Type.isFunction(listener)) {
	        throw new TypeError("The \"listener\" argument must be of type Function. Received type ".concat(babelHelpers.typeof(listener), "."));
	      }

	      options = Type.isPlainObject(options) ? options : {};
	      var fullEventName = this.resolveEventName(eventName, target, options.useGlobalNaming === true);

	      var _eventStore$getOrAdd = eventStore.getOrAdd(target),
	          eventsMap = _eventStore$getOrAdd.eventsMap,
	          onceMap = _eventStore$getOrAdd.onceMap;

	      var onceListeners = onceMap.get(fullEventName);
	      var listeners = eventsMap.get(fullEventName);

	      if (listeners && listeners.has(listener) || onceListeners && onceListeners.has(listener)) {
	        console.error("You cannot subscribe the same \"".concat(fullEventName, "\" event listener twice."));
	      } else {
	        if (listeners) {
	          listeners.set(listener, {
	            listener: listener,
	            options: options,
	            sort: this.getNextSequenceValue()
	          });
	        } else {
	          listeners = new Map([[listener, {
	            listener: listener,
	            options: options,
	            sort: this.getNextSequenceValue()
	          }]]);
	          eventsMap.set(fullEventName, listeners);
	        }
	      }

	      var maxListeners = this.getMaxListeners(target, eventName);

	      if (listeners.size > maxListeners) {
	        warningStore.add(target, fullEventName, listeners);
	        warningStore.printDelayed();
	      }
	    }
	  }, {
	    key: "subscribeOnce",
	    value: function subscribeOnce(target, eventName, listener) {
	      var _this3 = this;

	      if (Type.isString(target)) {
	        listener = eventName;
	        eventName = target;
	        target = this.GLOBAL_TARGET;
	      }

	      if (!Type.isObject(target)) {
	        throw new TypeError("The \"target\" argument must be an object.");
	      }

	      eventName = this.normalizeEventName(eventName);

	      if (!Type.isStringFilled(eventName)) {
	        throw new TypeError("The \"eventName\" argument must be a string.");
	      }

	      if (!Type.isFunction(listener)) {
	        throw new TypeError("The \"listener\" argument must be of type Function. Received type ".concat(babelHelpers.typeof(listener), "."));
	      }

	      var fullEventName = this.resolveEventName(eventName, target);

	      var _eventStore$getOrAdd2 = eventStore.getOrAdd(target),
	          eventsMap = _eventStore$getOrAdd2.eventsMap,
	          onceMap = _eventStore$getOrAdd2.onceMap;

	      var listeners = eventsMap.get(fullEventName);
	      var onceListeners = onceMap.get(fullEventName);

	      if (listeners && listeners.has(listener) || onceListeners && onceListeners.has(listener)) {
	        console.error("You cannot subscribe the same \"".concat(fullEventName, "\" event listener twice."));
	      } else {
	        var once = function once() {
	          _this3.unsubscribe(target, eventName, once);

	          onceListeners.delete(listener);
	          listener.apply(void 0, arguments);
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
	        throw new TypeError("The \"eventName\" argument must be a string.");
	      }

	      if (!Type.isFunction(listener)) {
	        throw new TypeError("The \"listener\" argument must be of type Function. Received type ".concat(typeof event === "undefined" ? "undefined" : babelHelpers.typeof(event), "."));
	      }

	      options = Type.isPlainObject(options) ? options : {};
	      var fullEventName = this.resolveEventName(eventName, target, options.useGlobalNaming === true);
	      var targetInfo = eventStore.get(target);
	      var listeners = targetInfo && targetInfo.eventsMap.get(fullEventName);
	      var onceListeners = targetInfo && targetInfo.onceMap.get(fullEventName);

	      if (listeners) {
	        listeners.delete(listener);
	      }

	      if (onceListeners) {
	        var once = onceListeners.get(listener);

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
	        var targetInfo = eventStore.get(target);

	        if (targetInfo) {
	          options = Type.isPlainObject(options) ? options : {};
	          var fullEventName = this.resolveEventName(eventName, target, options.useGlobalNaming === true);
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
	        throw new TypeError("The \"target\" argument must be an object.");
	      }

	      eventName = this.normalizeEventName(eventName);

	      if (!Type.isStringFilled(eventName)) {
	        throw new TypeError("The \"eventName\" argument must be a string.");
	      }

	      options = Type.isPlainObject(options) ? options : {};
	      var fullEventName = this.resolveEventName(eventName, target, options.useGlobalNaming === true);
	      var globalEvents = eventStore.get(this.GLOBAL_TARGET);
	      var globalListeners = globalEvents && globalEvents.eventsMap.get(fullEventName) || new Map();
	      var targetListeners = new Set();

	      if (target !== this.GLOBAL_TARGET) {
	        var targetEvents = eventStore.get(target);
	        targetListeners = targetEvents && targetEvents.eventsMap.get(fullEventName) || new Map();
	      }

	      var listeners = [].concat(babelHelpers.toConsumableArray(globalListeners.values()), babelHelpers.toConsumableArray(targetListeners.values()));
	      listeners.sort(function (a, b) {
	        return a.sort - b.sort;
	      });
	      var preparedEvent = this.prepareEvent(target, fullEventName, event);
	      var result = [];

	      for (var i = 0; i < listeners.length; i++) {
	        if (preparedEvent.isImmediatePropagationStopped()) {
	          break;
	        }

	        var _listeners$i = listeners[i],
	            listener = _listeners$i.listener,
	            listenerOptions = _listeners$i.options; //A previous listener could remove a current listener.

	        if (globalListeners.has(listener) || targetListeners.has(listener)) {
	          var listenerResult = void 0;

	          if (listenerOptions.compatMode) {
	            var params = [];
	            var compatData = preparedEvent.getCompatData();

	            if (compatData !== null) {
	              params = options.cloneData === true ? Runtime.clone(compatData) : compatData;
	            } else {
	              params = [preparedEvent];
	            }

	            var context = Type.isUndefined(options.thisArg) ? target : options.thisArg;
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
	      var preparedEvent = event;

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
	    value: function setMaxListeners() {
	      var target = this.GLOBAL_TARGET;
	      var eventName = null;
	      var count = undefined;

	      for (var _len5 = arguments.length, args = new Array(_len5), _key5 = 0; _key5 < _len5; _key5++) {
	        args[_key5] = arguments[_key5];
	      }

	      if (args.length === 1) {
	        count = args[0];
	      } else if (args.length === 2) {
	        if (Type.isString(args[0])) {
	          eventName = args[0];
	          count = args[1];
	        } else {
	          target = args[0];
	          count = args[1];
	        }
	      } else if (args.length >= 3) {
	        target = args[0];
	        eventName = args[1];
	        count = args[2];
	      }

	      if (!Type.isObject(target)) {
	        throw new TypeError("The \"target\" argument must be an object.");
	      }

	      if (eventName !== null && !Type.isStringFilled(eventName)) {
	        throw new TypeError("The \"eventName\" argument must be a string.");
	      }

	      if (!Type.isNumber(count) || count < 0) {
	        throw new TypeError("The value of \"count\" is out of range. It must be a non-negative number. Received ".concat(count, "."));
	      }

	      var targetInfo = eventStore.getOrAdd(target);

	      if (Type.isStringFilled(eventName)) {
	        var fullEventName = this.resolveEventName(eventName, target);
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
	        throw new TypeError("The \"target\" argument must be an object.");
	      }

	      var targetInfo = eventStore.get(target);

	      if (targetInfo) {
	        var maxListeners = targetInfo.maxListeners;

	        if (Type.isStringFilled(eventName)) {
	          var fullEventName = this.resolveEventName(eventName, target);
	          maxListeners = targetInfo.eventsMaxListeners.get(fullEventName) || maxListeners;
	        }

	        return maxListeners;
	      }

	      return this.DEFAULT_MAX_LISTENERS;
	    }
	  }, {
	    key: "addMaxListeners",
	    value: function addMaxListeners() {
	      var _this$destructMaxList = this.destructMaxListenersArgs.apply(this, arguments),
	          _this$destructMaxList2 = babelHelpers.slicedToArray(_this$destructMaxList, 3),
	          target = _this$destructMaxList2[0],
	          eventName = _this$destructMaxList2[1],
	          increment = _this$destructMaxList2[2];

	      var maxListeners = Math.max(this.getMaxListeners(target, eventName) + increment, 0);

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
	    value: function incrementMaxListeners() {
	      var _this$destructMaxList3 = this.destructMaxListenersArgs.apply(this, arguments),
	          _this$destructMaxList4 = babelHelpers.slicedToArray(_this$destructMaxList3, 3),
	          target = _this$destructMaxList4[0],
	          eventName = _this$destructMaxList4[1],
	          increment = _this$destructMaxList4[2];

	      return this.addMaxListeners(target, eventName, Math.abs(increment));
	    }
	  }, {
	    key: "decrementMaxListeners",
	    value: function decrementMaxListeners() {
	      var _this$destructMaxList5 = this.destructMaxListenersArgs.apply(this, arguments),
	          _this$destructMaxList6 = babelHelpers.slicedToArray(_this$destructMaxList5, 3),
	          target = _this$destructMaxList6[0],
	          eventName = _this$destructMaxList6[1],
	          increment = _this$destructMaxList6[2];

	      return this.addMaxListeners(target, eventName, -Math.abs(increment));
	    }
	  }, {
	    key: "destructMaxListenersArgs",
	    value: function destructMaxListenersArgs() {
	      var eventName = null;
	      var increment = 1;
	      var target = this.GLOBAL_TARGET;

	      for (var _len6 = arguments.length, args = new Array(_len6), _key6 = 0; _key6 < _len6; _key6++) {
	        args[_key6] = arguments[_key6];
	      }

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
	          eventName = args[0];
	          increment = args[1];
	        } else if (Type.isString(args[1])) {
	          target = args[0];
	          eventName = args[1];
	        } else {
	          target = args[0];
	          increment = args[1];
	        }
	      } else if (args.length >= 3) {
	        target = args[0];
	        eventName = args[1];
	        increment = args[2];
	      }

	      if (!Type.isObject(target)) {
	        throw new TypeError("The \"target\" argument must be an object.");
	      }

	      if (eventName !== null && !Type.isStringFilled(eventName)) {
	        throw new TypeError("The \"eventName\" argument must be a string.");
	      }

	      if (!Type.isNumber(increment)) {
	        throw new TypeError("The value of \"increment\" must be a number.");
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
	        throw new TypeError("The \"target\" argument must be an object.");
	      }

	      eventName = this.normalizeEventName(eventName);

	      if (!Type.isStringFilled(eventName)) {
	        throw new TypeError("The \"eventName\" argument must be a string.");
	      }

	      var targetInfo = eventStore.get(target);

	      if (!targetInfo) {
	        return new Map();
	      }

	      var fullEventName = this.resolveEventName(eventName, target);
	      return targetInfo.eventsMap.get(fullEventName) || new Map();
	    }
	  }, {
	    key: "registerAliases",
	    value: function registerAliases(aliases) {
	      aliases = this.normalizeAliases(aliases);
	      Object.keys(aliases).forEach(function (alias) {
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
	        throw new TypeError("The \"aliases\" argument must be an object.");
	      }

	      var result = Object.create(null);

	      for (var _alias in aliases) {
	        if (!Type.isStringFilled(_alias)) {
	          throw new TypeError("The alias must be an non-empty string.");
	        }

	        var options = aliases[_alias];

	        if (!options || !Type.isStringFilled(options.eventName) || !Type.isStringFilled(options.namespace)) {
	          throw new TypeError("The alias options must set the \"eventName\" and the \"namespace\".");
	        }

	        _alias = this.normalizeEventName(_alias);
	        result[_alias] = {
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
	      var _this4 = this;

	      var globalEvents = eventStore.get(this.GLOBAL_TARGET);

	      if (!globalEvents) {
	        return;
	      }

	      Object.keys(aliases).forEach(function (alias) {
	        var options = aliases[alias];
	        alias = _this4.normalizeEventName(alias);

	        var fullEventName = _this4.makeFullEventName(options.namespace, options.eventName);

	        var aliasListeners = globalEvents.eventsMap.get(alias);

	        if (aliasListeners) {
	          var listeners = globalEvents.eventsMap.get(fullEventName) || new Map();
	          globalEvents.eventsMap.set(fullEventName, new Map([].concat(babelHelpers.toConsumableArray(listeners), babelHelpers.toConsumableArray(aliasListeners))));
	          globalEvents.eventsMap.delete(alias);
	        }

	        var aliasOnceListeners = globalEvents.onceMap.get(alias);

	        if (aliasOnceListeners) {
	          var onceListeners = globalEvents.onceMap.get(fullEventName) || new Map();
	          globalEvents.onceMap.set(fullEventName, new Map([].concat(babelHelpers.toConsumableArray(onceListeners), babelHelpers.toConsumableArray(aliasOnceListeners))));
	          globalEvents.onceMap.delete(alias);
	        }

	        var aliasMaxListeners = globalEvents.eventsMaxListeners.get(alias);

	        if (aliasMaxListeners) {
	          var eventMaxListeners = globalEvents.eventsMaxListeners.get(fullEventName) || 0;
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
	     * @param eventName
	     * @param target
	     * @param useGlobalNaming
	     * @returns {string}
	     */

	  }, {
	    key: "resolveEventName",
	    value: function resolveEventName(eventName, target) {
	      var useGlobalNaming = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : false;
	      eventName = this.normalizeEventName(eventName);

	      if (!Type.isStringFilled(eventName)) {
	        return '';
	      }

	      if (this.isEventEmitter(target) && useGlobalNaming !== true) {
	        if (target.getEventNamespace() !== null && eventName.includes('.')) {
	          console.warn("Possible the wrong event name \"".concat(eventName, "\"."));
	        }

	        eventName = target.getFullEventName(eventName);
	      } else if (aliasStore.has(eventName)) {
	        var _aliasStore$get = aliasStore.get(eventName),
	            namespace = _aliasStore$get.namespace,
	            actualEventName = _aliasStore$get.eventName;

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
	      var fullName = Type.isStringFilled(namespace) ? "".concat(namespace, ":").concat(eventName) : eventName;
	      return Type.isStringFilled(fullName) ? fullName.toLowerCase() : '';
	    }
	  }]);
	  return EventEmitter;
	}();

	babelHelpers.defineProperty(EventEmitter, "GLOBAL_TARGET", globalTarget);
	babelHelpers.defineProperty(EventEmitter, "DEFAULT_MAX_LISTENERS", eventStore.getDefaultMaxListeners());
	babelHelpers.defineProperty(EventEmitter, "sequenceValue", 1);

	var stack = [];
	/**
	 * For compatibility only
	 * @type {boolean}
	 */
	// eslint-disable-next-line

	exports.isReady = false;
	function ready(handler) {
	  switch (document.readyState) {
	    case 'loading':
	      stack.push(handler);
	      break;

	    case 'interactive':
	    case 'complete':
	      if (Type.isFunction(handler)) {
	        handler();
	      }

	      exports.isReady = true;
	      break;
	  }
	}
	document.addEventListener('readystatechange', function () {
	  if (!exports.isReady) {
	    stack.forEach(ready);
	    stack = [];
	  }
	});

	/**
	 * @memberOf BX
	 */

	var Event = function Event() {
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
	    var decodedValue = Text.decode(value);
	    var result;

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
	  var _document = document,
	      documentElement = _document.documentElement,
	      body = _document.body;
	  var scrollTop = Math.max(window.pageYOffset || 0, documentElement ? documentElement.scrollTop : 0, body ? body.scrollTop : 0);
	  var scrollLeft = Math.max(window.pageXOffset || 0, documentElement ? documentElement.scrollLeft : 0, body ? body.scrollLeft : 0);
	  return {
	    scrollTop: scrollTop,
	    scrollLeft: scrollLeft
	  };
	}

	/**
	 * @memberOf BX
	 */

	var Dom =
	/*#__PURE__*/
	function () {
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
	          var parent = target.parentNode;

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
	          var preparedClassName = className.trim();

	          if (preparedClassName.length > 0) {
	            if (preparedClassName.includes(' ')) {
	              return preparedClassName.split(' ').every(function (name) {
	                return Dom.hasClass(element, name);
	              });
	            }

	            if ('classList' in element) {
	              return element.classList.contains(preparedClassName);
	            }

	            if (Type.isObject(element.className) && Type.isString(element.className.baseVal)) {
	              return element.getAttribute('class').split(' ').some(function (name) {
	                return name === preparedClassName;
	              });
	            }
	          }
	        }

	        if (Type.isArray(className) && className.length > 0) {
	          return className.every(function (name) {
	            return Dom.hasClass(element, name);
	          });
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
	          var preparedClassName = className.trim();

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

	              var names = element.className.baseVal.split(' ');

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
	          className.forEach(function (name) {
	            return Dom.addClass(element, name);
	          });
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
	          var preparedClassName = className.trim();

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
	              var names = element.className.baseVal.split(' ').filter(function (name) {
	                return name !== preparedClassName;
	              });
	              element.className.baseVal = names.join(' ');
	              return;
	            }
	          }
	        }

	        if (Type.isArray(className)) {
	          className.forEach(function (name) {
	            return Dom.removeClass(element, name);
	          });
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
	          var preparedClassName = className.trim();

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
	          className.forEach(function (name) {
	            return Dom.toggleClass(element, name);
	          });
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
	          Object.entries(prop).forEach(function (item) {
	            var _item = babelHelpers.slicedToArray(item, 2),
	                currentKey = _item[0],
	                currentValue = _item[1];

	            Dom.style(element, currentKey, currentValue);
	          });
	          return element;
	        }

	        if (Type.isString(prop)) {
	          if (Type.isUndefined(value) && element.nodeType !== Node.DOCUMENT_NODE) {
	            var computedStyle = getComputedStyle(element);

	            if (prop in computedStyle) {
	              return computedStyle[prop];
	            }

	            return computedStyle.getPropertyValue(prop);
	          }

	          if (Type.isNull(value) || value === '' || value === 'null') {
	            // eslint-disable-next-line
	            element.style[prop] = '';
	            return element;
	          }

	          if (Type.isString(value) || Type.isNumber(value)) {
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
	    value: function adjust(target) {
	      var data = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};

	      if (!target.nodeType) {
	        return null;
	      }

	      var element = target;

	      if (target.nodeType === Node.DOCUMENT_NODE) {
	        element = target.body;
	      }

	      if (Type.isPlainObject(data)) {
	        if (Type.isPlainObject(data.attrs)) {
	          Object.keys(data.attrs).forEach(function (key) {
	            if (key === 'class' || key.toLowerCase() === 'classname') {
	              element.className = data.attrs[key];
	              return;
	            } // eslint-disable-next-line


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
	          Object.keys(data.props).forEach(function (key) {
	            element[key] = data.props[key];
	          });
	        }

	        if (Type.isPlainObject(data.events)) {
	          Object.keys(data.events).forEach(function (key) {
	            Event.bind(element, key, data.events[key]);
	          });
	        }

	        if (Type.isPlainObject(data.dataset)) {
	          Object.keys(data.dataset).forEach(function (key) {
	            element.dataset[key] = data.dataset[key];
	          });
	        }

	        if (Type.isString(data.children)) {
	          data.children = [data.children];
	        }

	        if (Type.isArray(data.children) && data.children.length > 0) {
	          data.children.forEach(function (item) {
	            if (Type.isDomNode(item)) {
	              Dom.append(item, element);
	            }

	            if (Type.isString(item)) {
	              element.innerHTML += item;
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
	    value: function create(tag) {
	      var data = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	      var context = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : document;
	      var tagName = tag;
	      var options = data;

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
	        var elementRect = element.getBoundingClientRect();

	        var _getPageScroll = getPageScroll(),
	            scrollLeft = _getPageScroll.scrollLeft,
	            scrollTop = _getPageScroll.scrollTop;

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
	        var elementPosition = Dom.getPosition(element);
	        var relationElementPosition = Dom.getPosition(relationElement);
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
	          return Object.entries(_attr).forEach(function (_ref) {
	            var _ref2 = babelHelpers.slicedToArray(_ref, 2),
	                attrKey = _ref2[0],
	                attrValue = _ref2[1];

	            Dom.attr(element, attrKey, attrValue);
	          });
	        }
	      }

	      return null;
	    }
	  }]);
	  return Dom;
	}();

	var UA = navigator.userAgent.toLowerCase();
	/**
	 * @memberOf BX
	 */

	var Browser =
	/*#__PURE__*/
	function () {
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
	      return UA.includes('webkit');
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

	      var rv = -1;

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
	          var re = new RegExp('MSIE ([0-9]+[.0-9]*)');
	          var res = navigator.userAgent.match(re);

	          if (Type.isArrayLike(res) && res.length > 0) {
	            rv = parseFloat(res[1]);
	          }
	        }

	        if (navigator.appName === 'Netscape') {
	          // Alternative check for IE 11
	          rv = 11;

	          var _re = new RegExp('Trident/.*rv:([0-9]+[.0-9]*)');

	          if (_re.exec(navigator.userAgent) != null) {
	            var _res = navigator.userAgent.match(_re);

	            if (Type.isArrayLike(_res) && _res.length > 0) {
	              rv = parseFloat(_res[1]);
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
	    key: "isAndroid",
	    value: function isAndroid() {
	      return UA.includes('android');
	    }
	  }, {
	    key: "isIPad",
	    value: function isIPad() {
	      return UA.includes('ipad;');
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
	    key: "isDoctype",
	    value: function isDoctype(target) {
	      var doc = target || document;

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
	    value: function addGlobalClass() {
	      var globalClass = 'bx-core';

	      if (Dom.hasClass(document.documentElement, globalClass)) {
	        return;
	      }

	      if (Browser.isIOS()) {
	        globalClass += ' bx-ios';
	      } else if (Browser.isMac()) {
	        globalClass += ' bx-mac';
	      } else if (Browser.isAndroid()) {
	        globalClass += ' bx-android';
	      }

	      globalClass += Browser.isMobile() ? ' bx-touch' : ' bx-no-touch';
	      globalClass += Browser.isRetina() ? ' bx-retina' : ' bx-no-retina';
	      var ieVersion = -1;

	      if (/AppleWebKit/.test(navigator.userAgent)) {
	        globalClass += ' bx-chrome';
	      } else if (Browser.detectIEVersion() > 0) {
	        ieVersion = Browser.detectIEVersion();
	        globalClass += " bx-ie bx-ie".concat(ieVersion);

	        if (ieVersion > 7 && ieVersion < 10 && !Browser.isDoctype()) {
	          globalClass += ' bx-quirks';
	        }
	      } else if (/Opera/.test(navigator.userAgent)) {
	        globalClass += ' bx-opera';
	      } else if (/Gecko/.test(navigator.userAgent)) {
	        globalClass += ' bx-firefox';
	      }

	      Dom.addClass(document.documentElement, globalClass);
	    }
	  }, {
	    key: "detectAndroidVersion",
	    value: function detectAndroidVersion() {
	      var re = new RegExp('Android ([0-9]+[.0-9]*)');

	      if (re.exec(navigator.userAgent) != null) {
	        var res = navigator.userAgent.match(re);

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
	        return propertyName.replace(/([A-Z])/g, function () {
	          for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
	            args[_key] = arguments[_key];
	          }

	          return "-".concat(args[1].toLowerCase());
	        });
	      }

	      function getJsName(cssName) {
	        var reg = /(\\-([a-z]))/g;

	        if (reg.test(cssName)) {
	          return cssName.replace(reg, function () {
	            for (var _len2 = arguments.length, args = new Array(_len2), _key2 = 0; _key2 < _len2; _key2++) {
	              args[_key2] = arguments[_key2];
	            }

	            return args[2].toUpperCase();
	          });
	        }

	        return cssName;
	      }

	      var property = jsProperty.includes('-') ? getJsName(jsProperty) : jsProperty;
	      var bReturnCSSName = !!returnCSSName;
	      var ucProperty = property.charAt(0).toUpperCase() + property.slice(1);
	      var props = ['Webkit', 'Moz', 'O', 'ms'].join("".concat(ucProperty, " "));
	      var properties = "".concat(property, " ").concat(props, " ").concat(ucProperty).split(' ');
	      var obj = document.body || document.documentElement;

	      for (var i = 0; i < properties.length; i += 1) {
	        var prop = properties[i];

	        if (obj && 'style' in obj && prop in obj.style) {
	          var lowerProp = prop.substr(0, prop.length - property.length).toLowerCase();
	          var prefix = prop === property ? '' : "-".concat(lowerProp, "-");
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

	      var classNames = [];

	      for (var i = 0; i < features.length; i += 1) {
	        var support = !!Browser.isPropertySupported(features[i]);
	        classNames.push("bx-".concat(support ? '' : 'no-').concat(features[i].toLowerCase()));
	      }

	      Dom.addClass(document.documentElement, classNames.join(' '));
	    }
	  }]);
	  return Browser;
	}();

	var Cookie =
	/*#__PURE__*/
	function () {
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
	      return document.cookie.split(';').map(function (item) {
	        return item.split('=');
	      }).map(function (item) {
	        return item.map(function (subItem) {
	          return subItem.trim();
	        });
	      }).reduce(function (acc, item) {
	        var _item = babelHelpers.slicedToArray(item, 2),
	            key = _item[0],
	            value = _item[1];

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
	      var cookiesList = Cookie.getList();

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
	    value: function set(name, value) {
	      var options = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};
	      var attributes = babelHelpers.objectSpread({
	        expires: ''
	      }, options);

	      if (Type.isNumber(attributes.expires)) {
	        var now = +new Date();
	        var days = attributes.expires;
	        var dayInMs = 864e+5;
	        attributes.expires = new Date(now + days * dayInMs);
	      }

	      if (Type.isDate(attributes.expires)) {
	        attributes.expires = attributes.expires.toUTCString();
	      }

	      var safeName = decodeURIComponent(String(name)).replace(/%(23|24|26|2B|5E|60|7C)/g, decodeURIComponent).replace(/[()]/g, escape);
	      var safeValue = encodeURIComponent(String(value)).replace(/%(23|24|26|2B|3A|3C|3E|3D|2F|3F|40|5B|5D|5E|60|7B|7D|7C)/g, decodeURIComponent);
	      var stringifiedAttributes = Object.keys(attributes).reduce(function (acc, key) {
	        var attributeValue = attributes[key];

	        if (!attributeValue) {
	          return acc;
	        }

	        if (attributeValue === true) {
	          return "".concat(acc, "; ").concat(key);
	        }
	        /**
	         * Considers RFC 6265 section 5.2:
	         * ...
	         * 3. If the remaining unparsed-attributes contains a %x3B (';')
	         * character:
	         * Consume the characters of the unparsed-attributes up to,
	         * not including, the first %x3B (';') character.
	         */


	        return "".concat(acc, "; ").concat(key, "=").concat(attributeValue.split(';')[0]);
	      }, '');
	      document.cookie = "".concat(safeName, "=").concat(safeValue).concat(stringifiedAttributes);
	    }
	    /**
	     * Removes cookie
	     * @param {string} name
	     * @param {object} [options]
	     */

	  }, {
	    key: "remove",
	    value: function remove(name) {
	      var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	      Cookie.set(name, '', babelHelpers.objectSpread({}, options, {
	        expires: -1
	      }));
	    }
	  }]);
	  return Cookie;
	}();

	function objectToFormData(source) {
	  var formData = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : new FormData();
	  var pre = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : null;

	  if (Type.isUndefined(source)) {
	    return formData;
	  }

	  if (Type.isNull(source)) {
	    formData.append(pre, '');
	  } else if (Type.isArray(source)) {
	    if (!source.length) {
	      var _key = "".concat(pre, "[]");

	      formData.append(_key, '');
	    } else {
	      source.forEach(function (value, index) {
	        var key = "".concat(pre, "[").concat(index, "]");
	        objectToFormData(value, formData, key);
	      });
	    }
	  } else if (Type.isDate(source)) {
	    formData.append(pre, source.toISOString());
	  } else if (Type.isObject(source) && !Type.isFile(source) && !Type.isBlob(source)) {
	    Object.keys(source).forEach(function (property) {
	      var value = source[property];
	      var preparedProperty = property;

	      if (Type.isArray(value)) {
	        while (property.length > 2 && property.lastIndexOf('[]') === property.length - 2) {
	          preparedProperty = property.substring(0, property.length - 2);
	        }
	      }

	      var key = pre ? "".concat(pre, "[").concat(preparedProperty, "]") : preparedProperty;
	      objectToFormData(value, formData, key);
	    });
	  } else {
	    formData.append(pre, source);
	  }

	  return formData;
	}

	var Data =
	/*#__PURE__*/
	function () {
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

	var Http = function Http() {
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
	        Runtime.debug("message undefined: ".concat(value));
	        message[value] = '';
	      }
	    }
	  }

	  if (Type.isPlainObject(value)) {
	    Object.keys(value).forEach(function (key) {
	      message[key] = value[key];
	    });
	  }

	  return message[value];
	}

	if (!Type.isNil(window.BX) && Type.isFunction(window.BX.message)) {
	  Object.keys(window.BX.message).forEach(function (key) {
	    message(babelHelpers.defineProperty({}, key, window.BX.message[key]));
	  });
	}

	/**
	 * Implements interface for works with language messages
	 * @memberOf BX
	 */

	var Loc =
	/*#__PURE__*/
	function () {
	  function Loc() {
	    babelHelpers.classCallCheck(this, Loc);
	  }

	  babelHelpers.createClass(Loc, null, [{
	    key: "getMessage",

	    /**
	     * Gets message by id
	     * @param {string} messageId
	     * @return {?string}
	     */
	    value: function getMessage(messageId) {
	      return message(messageId);
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
	        message(babelHelpers.defineProperty({}, id, value));
	      }

	      if (Type.isObject(id)) {
	        message(id);
	      }
	    }
	  }]);
	  return Loc;
	}();

	var handlers = new Map();
	var children = new Map();

	var getUid = function () {
	  var incremental = 0;
	  return function () {
	    incremental += 1;
	    return incremental;
	  };
	}();

	function bindAll(element, handlersMap) {
	  handlersMap.forEach(function (handler, key) {
	    var currentElement = element.querySelector("[".concat(key, "]"));

	    if (currentElement) {
	      currentElement.removeAttribute(key);
	      var event = key.replace(/-(.*)/, '');
	      Event.bind(currentElement, event, handler);
	      handlers.delete(key);
	    }
	  });
	}

	function replaceChild(element, childrenMap) {
	  childrenMap.forEach(function (item, id) {
	    var currentElement = element.getElementById(id);

	    if (currentElement) {
	      Dom.replace(currentElement, item);
	      children.delete(id);
	    }
	  });
	}

	function render(sections) {
	  var eventAttrRe = /[ |\t]on(\w+)="$/;
	  var uselessSymbolsRe = /[\r\n\t]/g;

	  for (var _len = arguments.length, substitutions = new Array(_len > 1 ? _len - 1 : 0), _key = 1; _key < _len; _key++) {
	    substitutions[_key - 1] = arguments[_key];
	  }

	  var html = substitutions.reduce(function (acc, item, index) {
	    var preparedAcc = acc; // Process event handlers

	    var matches = acc.match(eventAttrRe);

	    if (matches && Type.isFunction(item)) {
	      var eventName = matches[1].replace(/=['|"]/, '');
	      var attrName = "".concat(eventName, "-").concat(getUid());
	      var attribute = "".concat(attrName, "=\"");
	      preparedAcc = preparedAcc.replace(eventAttrRe, " ".concat(attribute));
	      handlers.set(attrName, item);
	      preparedAcc += sections[index + 1].replace(uselessSymbolsRe, ' ').replace(/  +/g, ' ');
	      return preparedAcc;
	    } // Process element


	    if (Type.isDomNode(item)) {
	      var childKey = "tmp___".concat(getUid());
	      children.set(childKey, item);
	      preparedAcc += "<span id=\"".concat(childKey, "\"> </span>");
	      preparedAcc += sections[index + 1];
	      return preparedAcc;
	    } // Process array


	    if (Type.isArray(item)) {
	      babelHelpers.toConsumableArray(item).forEach(function (currentElement) {
	        if (Type.isDomNode(currentElement)) {
	          var _childKey = "tmp___".concat(getUid());

	          children.set(_childKey, currentElement);
	          preparedAcc += "<span id=\"".concat(_childKey, "\"> </span>");
	        }
	      });
	      preparedAcc += sections[index + 1];
	      return preparedAcc;
	    }

	    return preparedAcc + item + sections[index + 1];
	  }, sections[0]);
	  var lowercaseHtml = html.trim().toLowerCase();

	  if (lowercaseHtml.startsWith('<!doctype') || lowercaseHtml.startsWith('<html')) {
	    var doc = document.implementation.createHTMLDocument('');
	    doc.documentElement.innerHTML = html;
	    replaceChild(doc, children);
	    bindAll(doc, handlers);
	    handlers.clear();
	    return doc;
	  }

	  var parser = new DOMParser();
	  var parsedDocument = parser.parseFromString(html, 'text/html');
	  replaceChild(parsedDocument, children);
	  bindAll(parsedDocument, handlers);

	  if (parsedDocument.head.children.length && parsedDocument.body.children.length) {
	    return parsedDocument;
	  }

	  if (parsedDocument.body.children.length === 1) {
	    var _parsedDocument$body$ = babelHelpers.slicedToArray(parsedDocument.body.children, 1),
	        el = _parsedDocument$body$[0];

	    Dom.remove(el);
	    return el;
	  }

	  if (parsedDocument.body.children.length > 1) {
	    return babelHelpers.toConsumableArray(parsedDocument.body.children).map(function (item) {
	      Dom.remove(item);
	      return item;
	    });
	  }

	  if (parsedDocument.body.children.length === 0) {
	    if (parsedDocument.head.children.length === 1) {
	      var _parsedDocument$head$ = babelHelpers.slicedToArray(parsedDocument.head.children, 1),
	          _el = _parsedDocument$head$[0];

	      Dom.remove(_el);
	      return _el;
	    }

	    if (parsedDocument.head.children.length > 1) {
	      return babelHelpers.toConsumableArray(parsedDocument.head.children).map(function (item) {
	        Dom.remove(item);
	        return item;
	      });
	    }
	  }

	  return false;
	}

	function parseProps(sections) {
	  for (var _len = arguments.length, substitutions = new Array(_len > 1 ? _len - 1 : 0), _key = 1; _key < _len; _key++) {
	    substitutions[_key - 1] = arguments[_key];
	  }

	  return substitutions.reduce(function (acc, item, index) {
	    var nextSectionIndex = index + 1;

	    if (!Type.isPlainObject(item) && !Type.isArray(item)) {
	      return acc + item + sections[nextSectionIndex];
	    }

	    return "".concat(acc, "__s").concat(index).concat(sections[nextSectionIndex]);
	  }, sections[0]).replace(/[\r\t]/gm, '').split(';\n').map(function (item) {
	    return item.replace(/\n/, '');
	  }).reduce(function (acc, item) {
	    if (item !== '') {
	      var matches = item.match(/^[\w-. ]+:/);
	      var splitted = item.split(/^[\w-. ]+:/);

	      var _key2 = matches[0].replace(':', '').trim();

	      var value = splitted[1].trim();
	      var substitutionPlaceholderExp = /^__s\d+/;

	      if (substitutionPlaceholderExp.test(value)) {
	        acc[_key2] = substitutions[value.replace('__s', '')];
	        return acc;
	      }

	      acc[_key2] = value;
	    }

	    return acc;
	  }, {});
	}
	/**
	 * @memberOf BX
	 */


	var Tag =
	/*#__PURE__*/
	function () {
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
	    value: function safe(sections) {
	      for (var _len2 = arguments.length, substitutions = new Array(_len2 > 1 ? _len2 - 1 : 0), _key3 = 1; _key3 < _len2; _key3++) {
	        substitutions[_key3 - 1] = arguments[_key3];
	      }

	      return substitutions.reduce(function (acc, item, index) {
	        return acc + Text.encode(item) + sections[index + 1];
	      }, sections[0]);
	    }
	    /**
	     * Decodes all substitutions
	     * @param sections
	     * @param substitutions
	     * @return {string}
	     */

	  }, {
	    key: "unsafe",
	    value: function unsafe(sections) {
	      for (var _len3 = arguments.length, substitutions = new Array(_len3 > 1 ? _len3 - 1 : 0), _key4 = 1; _key4 < _len3; _key4++) {
	        substitutions[_key4 - 1] = arguments[_key4];
	      }

	      return substitutions.reduce(function (acc, item, index) {
	        return acc + Text.decode(item) + sections[index + 1];
	      }, sections[0]);
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

	      return function styleTagHandler() {
	        Dom.style(element, parseProps.apply(void 0, arguments));
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
	    value: function message(sections) {
	      for (var _len4 = arguments.length, substitutions = new Array(_len4 > 1 ? _len4 - 1 : 0), _key5 = 1; _key5 < _len4; _key5++) {
	        substitutions[_key5 - 1] = arguments[_key5];
	      }

	      return substitutions.reduce(function (acc, item, index) {
	        return acc + Loc.getMessage(item) + sections[index + 1];
	      }, sections[0]);
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

	      return function attrsTagHandler() {
	        Dom.attr(element, parseProps.apply(void 0, arguments));
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
	      return function (sourceKey, value, accumulator) {
	        var result = /\[(\w*)\]$/.exec(sourceKey);
	        var key = sourceKey.replace(/\[\w*\]$/, '');

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
	      return function (sourceKey, value, accumulator) {
	        var result = /(\[\])$/.exec(sourceKey);
	        var key = sourceKey.replace(/\[\]$/, '');

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
	      return function (sourceKey, value, accumulator) {
	        var key = sourceKey.replace(/\[\]$/, '');
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

	function parseQuery(input) {
	  if (!Type.isString(input)) {
	    return {};
	  }

	  var url = input.trim().replace(/^[?#&]/, '');

	  if (!url) {
	    return {};
	  }

	  return url.split('&').reduce(function (acc, param) {
	    var _param$replace$split = param.replace(/\+/g, ' ').split('='),
	        _param$replace$split2 = babelHelpers.slicedToArray(_param$replace$split, 2),
	        key = _param$replace$split2[0],
	        value = _param$replace$split2[1];

	    var keyFormat = getKeyFormat(key);
	    var formatter = getParser(keyFormat);
	    formatter(key, value, acc);
	    return acc;
	  }, {});
	}

	var urlExp = /^((\w+):)?(\/\/((\w+)?(:(\w+))?@)?([^\/\?:]+)(:(\d+))?)?(\/?([^\/\?#][^\?#]*)?)?(\?([^#]+))?(#(\w*))?/;
	function parseUrl(url) {
	  var result = url.match(urlExp);

	  if (Type.isArray(result)) {
	    var queryParams = parseQuery(result[14]);
	    return {
	      useShort: /^\/\//.test(url),
	      href: result[0] || '',
	      schema: result[2] || '',
	      host: result[8] || '',
	      port: result[10] || '',
	      path: result[11] || '',
	      query: result[14] || '',
	      queryParams: queryParams,
	      hash: result[16] || '',
	      username: result[5] || '',
	      password: result[7] || '',
	      origin: result[8] || ''
	    };
	  }

	  return {};
	}

	function buildQueryString() {
	  var params = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	  var queryString = Object.keys(params).reduce(function (acc, key) {
	    if (Type.isArray(params[key])) {
	      params[key].forEach(function (paramValue) {
	        acc.push("".concat(key, "[]=").concat(paramValue));
	      }, '');
	    }

	    if (Type.isPlainObject(params[key])) {
	      Object.keys(params[key]).forEach(function (paramIndex) {
	        acc.push("".concat(key, "[").concat(paramIndex, "]=").concat(params[key][paramIndex]));
	      }, '');
	    }

	    if (!Type.isObject(params[key]) && !Type.isArray(params[key])) {
	      acc.push("".concat(key, "=").concat(params[key]));
	    }

	    return acc;
	  }, []).join('&');

	  if (queryString.length > 0) {
	    return "?".concat(queryString);
	  }

	  return queryString;
	}

	function prepareParamValue(value) {
	  if (Type.isArray(value)) {
	    return value.map(function (item) {
	      return String(item);
	    });
	  }

	  if (Type.isPlainObject(value)) {
	    return babelHelpers.objectSpread({}, value);
	  }

	  return String(value);
	}

	var map = new WeakMap();
	/**
	 * Implements interface for works with URI
	 * @memberOf BX
	 */

	var Uri =
	/*#__PURE__*/
	function () {
	  babelHelpers.createClass(Uri, null, [{
	    key: "addParam",
	    value: function addParam(url) {
	      var params = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	      return new Uri(url).setQueryParams(params).toString();
	    }
	  }, {
	    key: "removeParam",
	    value: function removeParam(url, params) {
	      var _ref;

	      var removableParams = Type.isArray(params) ? params : [params];
	      return (_ref = new Uri(url)).removeQueryParam.apply(_ref, babelHelpers.toConsumableArray(removableParams)).toString();
	    }
	  }]);

	  function Uri() {
	    var url = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '';
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
	        map.get(this).path = "/".concat(String(path));
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
	      var params = this.getQueryParams();

	      if (key in params) {
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
	    value: function setQueryParam(key) {
	      var value = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : '';
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
	      return babelHelpers.objectSpread({}, map.get(this).queryParams);
	    }
	    /**
	     * Sets query params
	     * @param {Object<string, any>} params
	     * @return {Uri}
	     */

	  }, {
	    key: "setQueryParams",
	    value: function setQueryParams() {
	      var params = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      var currentParams = this.getQueryParams();
	      var newParams = babelHelpers.objectSpread({}, currentParams, params);
	      Object.keys(newParams).forEach(function (key) {
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
	    value: function removeQueryParam() {
	      var currentParams = babelHelpers.objectSpread({}, map.get(this).queryParams);

	      for (var _len = arguments.length, keys = new Array(_len), _key = 0; _key < _len; _key++) {
	        keys[_key] = arguments[_key];
	      }

	      keys.forEach(function (key) {
	        if (key in currentParams) {
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
	      var serialized = babelHelpers.objectSpread({}, map.get(this));
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
	      var data = babelHelpers.objectSpread({}, map.get(this));
	      var protocol = data.schema ? "".concat(data.schema, "://") : '';

	      if (data.useShort) {
	        protocol = '//';
	      }

	      var port = function () {
	        if (Type.isString(data.port) && !['', '80'].includes(data.port)) {
	          return ":".concat(data.port);
	        }

	        return '';
	      }();

	      var host = this.getHost();
	      var path = this.getPath();
	      var query = buildQueryString(data.queryParams);
	      var hash = data.hash ? "#".concat(data.hash) : '';
	      return "".concat(host ? protocol : '').concat(host).concat(host ? port : '').concat(path).concat(query).concat(hash);
	    }
	  }]);
	  return Uri;
	}();

	/**
	 * @memberOf BX
	 */
	var Validation =
	/*#__PURE__*/
	function () {
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
	      var exp = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/;
	      return exp.test(String(value).toLowerCase());
	    }
	  }]);
	  return Validation;
	}();

	var BaseCache =
	/*#__PURE__*/
	function () {
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
	      return babelHelpers.toConsumableArray(this.storage.keys());
	    }
	    /**
	     * Gets storage values
	     */

	  }, {
	    key: "values",
	    value: function values() {
	      return babelHelpers.toConsumableArray(this.storage.values());
	    }
	  }]);
	  return BaseCache;
	}();

	var MemoryCache =
	/*#__PURE__*/
	function (_BaseCache) {
	  babelHelpers.inherits(MemoryCache, _BaseCache);

	  function MemoryCache() {
	    var _babelHelpers$getProt;

	    var _this;

	    babelHelpers.classCallCheck(this, MemoryCache);

	    for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
	      args[_key] = arguments[_key];
	    }

	    _this = babelHelpers.possibleConstructorReturn(this, (_babelHelpers$getProt = babelHelpers.getPrototypeOf(MemoryCache)).call.apply(_babelHelpers$getProt, [this].concat(args)));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "storage", new Map());
	    return _this;
	  }

	  return MemoryCache;
	}(BaseCache);

	var LsStorage =
	/*#__PURE__*/
	function () {
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

	      var stack = localStorage.getItem(this.stackKey);

	      if (Type.isString(stack) && stack !== '') {
	        var parsedStack = JSON.parse(stack);

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
	        var preparedStack = JSON.stringify(this.stack);
	        localStorage.setItem(this.stackKey, preparedStack);
	      }
	    }
	  }, {
	    key: "get",
	    value: function get(key) {
	      var stack = this.getStack();
	      return stack[key];
	    }
	  }, {
	    key: "set",
	    value: function set(key, value) {
	      var stack = this.getStack();
	      stack[key] = value;
	      this.saveStack();
	    }
	  }, {
	    key: "delete",
	    value: function _delete(key) {
	      var stack = this.getStack();

	      if (key in stack) {
	        delete stack[key];
	      }
	    }
	  }, {
	    key: "has",
	    value: function has(key) {
	      var stack = this.getStack();
	      return key in stack;
	    }
	  }, {
	    key: "keys",
	    value: function keys() {
	      var stack = this.getStack();
	      return Object.keys(stack);
	    }
	  }, {
	    key: "values",
	    value: function values() {
	      var stack = this.getStack();
	      return Object.values(stack);
	    }
	  }, {
	    key: "size",
	    get: function get() {
	      var stack = this.getStack();
	      return Object.keys(stack).length;
	    }
	  }]);
	  return LsStorage;
	}();

	var LocalStorageCache =
	/*#__PURE__*/
	function (_BaseCache) {
	  babelHelpers.inherits(LocalStorageCache, _BaseCache);

	  function LocalStorageCache() {
	    var _babelHelpers$getProt;

	    var _this;

	    babelHelpers.classCallCheck(this, LocalStorageCache);

	    for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
	      args[_key] = arguments[_key];
	    }

	    _this = babelHelpers.possibleConstructorReturn(this, (_babelHelpers$getProt = babelHelpers.getPrototypeOf(LocalStorageCache)).call.apply(_babelHelpers$getProt, [this].concat(args)));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "storage", new LsStorage());
	    return _this;
	  }

	  return LocalStorageCache;
	}(BaseCache);

	/**
	 * @memberOf BX
	 */

	var Cache = function Cache() {
	  babelHelpers.classCallCheck(this, Cache);
	};

	babelHelpers.defineProperty(Cache, "MemoryCache", MemoryCache);
	babelHelpers.defineProperty(Cache, "LocalStorageCache", LocalStorageCache);

	function convertPath(path) {
	  if (Type.isStringFilled(path)) {
	    return path.split('.').reduce(function (acc, item) {
	      item.split(/\[['"]?(.+?)['"]?\]/g).forEach(function (key) {
	        if (Type.isStringFilled(key)) {
	          acc.push(key);
	        }
	      });
	      return acc;
	    }, []);
	  }

	  return [];
	}

	var SettingsCollection =
	/*#__PURE__*/
	function () {
	  function SettingsCollection() {
	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, SettingsCollection);

	    if (Type.isPlainObject(options)) {
	      Object.assign(this, options);
	    }
	  }

	  babelHelpers.createClass(SettingsCollection, [{
	    key: "get",
	    value: function get(path) {
	      var defaultValue = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
	      var convertedPath = convertPath(path);
	      return convertedPath.reduce(function (acc, key) {
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
	    Object.values(target).forEach(function (value) {
	      deepFreeze(value);
	    });
	    return Object.freeze(target);
	  }

	  return target;
	}

	var settingsStorage = new Map();

	var Extension$1 =
	/*#__PURE__*/
	function () {
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

	        var settingsScriptNode = document.querySelector("script[data-extension=\"".concat(extensionName, "\"]"));

	        if (Type.isDomNode(settingsScriptNode)) {
	          var decodedSettings = function () {
	            try {
	              return new SettingsCollection(JSON.parse(settingsScriptNode.innerHTML));
	            } catch (error) {
	              return new SettingsCollection();
	            }
	          }();

	          var frozenSettings = deepFreeze(decodedSettings);
	          settingsStorage.set(extensionName, frozenSettings);
	          return frozenSettings;
	        }
	      }

	      return deepFreeze(new SettingsCollection());
	    }
	  }]);
	  return Extension;
	}();

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

	var getClass = Reflection.getClass,
	    namespace = Reflection.namespace;
	var message$1 = message;
	/**
	 * @memberOf BX
	 */

	var replace = Dom.replace,
	    remove = Dom.remove,
	    clean = Dom.clean,
	    insertBefore = Dom.insertBefore,
	    insertAfter = Dom.insertAfter,
	    append = Dom.append,
	    prepend = Dom.prepend,
	    style = Dom.style,
	    adjust = Dom.adjust,
	    create = Dom.create,
	    isShown = Dom.isShown;
	var addClass = function addClass() {
	  Dom.addClass.apply(Dom, babelHelpers.toConsumableArray(Runtime.merge([], Array.from(arguments), [getElement(arguments[0])])));
	};
	var removeClass = function removeClass() {
	  Dom.removeClass.apply(Dom, babelHelpers.toConsumableArray(Runtime.merge(Array.from(arguments), [getElement(arguments[0])])));
	};
	var hasClass = function hasClass() {
	  return Dom.hasClass.apply(Dom, babelHelpers.toConsumableArray(Runtime.merge(Array.from(arguments), [getElement(arguments[0])])));
	};
	var toggleClass = function toggleClass() {
	  Dom.toggleClass.apply(Dom, babelHelpers.toConsumableArray(Runtime.merge(Array.from(arguments), [getElement(arguments[0])])));
	};
	var cleanNode = function cleanNode(element) {
	  var removeElement = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
	  var currentElement = getElement(element);

	  if (Type.isDomNode(currentElement)) {
	    Dom.clean(currentElement);

	    if (removeElement) {
	      Dom.remove(currentElement);
	      return currentElement;
	    }
	  }

	  return currentElement;
	};
	var getCookie = Http.Cookie.get;
	var setCookie = function setCookie(name, value) {
	  var options = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};
	  var attributes = babelHelpers.objectSpread({}, options);

	  if (Type.isNumber(attributes.expires)) {
	    attributes.expires /= 3600 * 24;
	  }

	  Http.Cookie.set(name, value, attributes);
	};
	var bind$1 = Event.bind,
	    unbind$1 = Event.unbind,
	    unbindAll$1 = Event.unbindAll,
	    bindOnce$1 = Event.bindOnce,
	    ready$1 = Event.ready;
	var debugEnableFlag = debugState,
	    debugStatus = isDebugEnabled,
	    debug$1 = debug;
	var debugEnable = function debugEnable(value) {
	  if (value) {
	    enableDebug();
	  } else {
	    disableDebug();
	  }
	};
	var clone$1 = Runtime.clone,
	    loadExt = Runtime.loadExtension,
	    debounce = Runtime.debounce,
	    throttle = Runtime.throttle,
	    html = Runtime.html; // BX.type
	var type = babelHelpers.objectSpread({}, Object.getOwnPropertyNames(Type).filter(function (key) {
	  return !['name', 'length', 'prototype', 'caller', 'arguments'].includes(key);
	}).reduce(function (acc, key) {
	  acc[key] = Type[key];
	  return acc;
	}, {}), {
	  isNotEmptyString: function isNotEmptyString(value) {
	    return Type.isString(value) && value !== '';
	  },
	  isNotEmptyObject: function isNotEmptyObject(value) {
	    return Type.isObjectLike(value) && Object.keys(value).length > 0;
	  },
	  isMapKey: Type.isObject,
	  stringToInt: function stringToInt(value) {
	    var parsed = parseInt(value);
	    return !Number.isNaN(parsed) ? parsed : 0;
	  }
	}); // BX.browser

	var browser = {
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
	}; // eslint-disable-next-line

	var ajax = window.BX ? window.BX.ajax : function () {};
	function GetWindowScrollSize() {
	  var doc = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : document;
	  return {
	    scrollWidth: doc.documentElement.scrollWidth,
	    scrollHeight: doc.documentElement.scrollHeight
	  };
	}
	function GetWindowScrollPos() {
	  var doc = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : document;
	  var win = getWindow(doc);
	  return {
	    scrollLeft: win.pageXOffset,
	    scrollTop: win.pageYOffset
	  };
	}
	function GetWindowInnerSize() {
	  var doc = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : document;
	  var win = getWindow(doc);
	  return {
	    innerWidth: win.innerWidth,
	    innerHeight: win.innerHeight
	  };
	}
	function GetWindowSize() {
	  var doc = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : document;
	  return babelHelpers.objectSpread({}, GetWindowInnerSize(doc), GetWindowScrollPos(doc), GetWindowScrollSize(doc));
	}
	function GetContext(node) {
	  return getWindow(node);
	}
	function pos(element) {
	  var relative = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;

	  if (!element) {
	    return new DOMRect().toJSON();
	  }

	  if (element.ownerDocument === document && !relative) {
	    var clientRect = element.getBoundingClientRect();
	    var root = document.documentElement;
	    var _document = document,
	        body = _document.body;
	    return {
	      top: Math.round(clientRect.top + (root.scrollTop || body.scrollTop)),
	      left: Math.round(clientRect.left + (root.scrollLeft || body.scrollLeft)),
	      width: Math.round(clientRect.right - clientRect.left),
	      height: Math.round(clientRect.bottom - clientRect.top),
	      right: Math.round(clientRect.right + (root.scrollLeft || body.scrollLeft)),
	      bottom: Math.round(clientRect.bottom + (root.scrollTop || body.scrollTop))
	    };
	  }

	  var x = 0;
	  var y = 0;
	  var w = element.offsetWidth;
	  var h = element.offsetHeight;
	  var first = true; // eslint-disable-next-line no-param-reassign

	  for (; element != null; element = element.offsetParent) {
	    if (!first && relative && BX.is_relative(element)) {
	      break;
	    }

	    x += element.offsetLeft;
	    y += element.offsetTop;

	    if (first) {
	      first = false; // eslint-disable-next-line no-continue

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
	    console.error('The "eventObject" argument must be an object. Received type ' + babelHelpers.typeof(eventObject) + '.');
	    return;
	  }

	  if (!Type.isStringFilled(eventName)) {
	    console.error('The "eventName" argument must be a string.');
	    return;
	  }

	  if (!Type.isFunction(eventHandler)) {
	    console.error('The "eventHandler" argument must be a function. Received type ' + babelHelpers.typeof(eventHandler) + '.');
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
	  var event = new BaseEvent();
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
	    console.error('The "eventHandler" argument must be a function. Received type ' + babelHelpers.typeof(eventHandler) + '.');
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

	if (global && global.window && global.window.BX) {
	  Object.assign(global.window.BX, exports);
	}

	exports.BaseError = BaseError;
	exports.Browser = Browser;
	exports.Cache = Cache;
	exports.Dom = Dom;
	exports.Event = Event;
	exports.Extension = Extension$1;
	exports.GetContext = GetContext;
	exports.GetWindowInnerSize = GetWindowInnerSize;
	exports.GetWindowScrollPos = GetWindowScrollPos;
	exports.GetWindowScrollSize = GetWindowScrollSize;
	exports.GetWindowSize = GetWindowSize;
	exports.Http = Http;
	exports.Loc = Loc;
	exports.Reflection = Reflection;
	exports.Runtime = Runtime;
	exports.Tag = Tag;
	exports.Text = Text;
	exports.Type = Type;
	exports.Uri = Uri;
	exports.Validation = Validation;
	exports.addClass = addClass;
	exports.addCustomEvent = addCustomEvent;
	exports.adjust = adjust;
	exports.ajax = ajax;
	exports.append = append;
	exports.bind = bind$1;
	exports.bindOnce = bindOnce$1;
	exports.browser = browser;
	exports.clean = clean;
	exports.cleanNode = cleanNode;
	exports.clone = clone$1;
	exports.create = create;
	exports.debounce = debounce;
	exports.debug = debug$1;
	exports.debugEnable = debugEnable;
	exports.debugEnableFlag = debugEnableFlag;
	exports.debugStatus = debugStatus;
	exports.getClass = getClass;
	exports.getCookie = getCookie;
	exports.hasClass = hasClass;
	exports.html = html;
	exports.insertAfter = insertAfter;
	exports.insertBefore = insertBefore;
	exports.isShown = isShown;
	exports.loadExt = loadExt;
	exports.message = message$1;
	exports.namespace = namespace;
	exports.onCustomEvent = onCustomEvent;
	exports.pos = pos;
	exports.prepend = prepend;
	exports.ready = ready$1;
	exports.remove = remove;
	exports.removeAllCustomEvents = removeAllCustomEvents;
	exports.removeClass = removeClass;
	exports.removeCustomEvent = removeCustomEvent;
	exports.replace = replace;
	exports.setCookie = setCookie;
	exports.style = style;
	exports.throttle = throttle;
	exports.toggleClass = toggleClass;
	exports.type = type;
	exports.unbind = unbind$1;
	exports.unbindAll = unbindAll$1;

}(this.BX = this.BX || {}));



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

	var PRELOADING = 1;
	var PRELOADED = 2;
	var LOADING = 3;
	var LOADED = 4;
	var assets = {};
	var isAsync = null;

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
				zIndex:'10000',
				textAlign:'center'
			},
			text: msg
		}));

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
		if (isAsync === null)
		{
			isAsync = "async" in doc.createElement("script") || "MozAppearance" in doc.documentElement.style || window.opera;
		}

		return isAsync ? loadAsync(items, callback, doc) : loadAsyncEmulation(items, callback, doc);
	};

	BX.convert =
		{
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

	function loadAsync(items, callback, doc)
	{
		if (!BX.type.isArray(items))
		{
			return;
		}

		function allLoaded(items)
		{
			items = items || assets;
			for (var name in items)
			{
				if (items.hasOwnProperty(name) && items[name].state !== LOADED)
				{
					return false;
				}
			}

			return true;
		}

		if (!BX.type.isFunction(callback))
		{
			callback = null;
		}

		var itemSet = {}, item, i;
		for (i = 0; i < items.length; i++)
		{
			item = items[i];
			item = getAsset(item);
			itemSet[item.name] = item;
		}

		var callbackWasCalled = false;
		if (items.length > 0)
		{
			for (i = 0; i < items.length; i++)
			{
				item = items[i];
				item = getAsset(item);
				load(item, function () {
					if (allLoaded(itemSet))
					{
						if (!callbackWasCalled)
						{
							callback && callback();
							callbackWasCalled = true;
						}

					}
				}, doc);
			}
		}
		else
		{
			if (typeof callback === 'function')
			{
				callback();
				callbackWasCalled = true;
			}
		}
	}

	function loadAsyncEmulation(items, callback, doc)
	{
		function onPreload(asset)
		{
			asset.state = PRELOADED;
			if (BX.type.isArray(asset.onpreload) && asset.onpreload)
			{
				for (var i = 0; i < asset.onpreload.length; i++)
				{
					asset.onpreload[i].call();
				}
			}
		}

		function preLoad(asset)
		{
			if (asset.state === undefined)
			{
				asset.state = PRELOADING;
				asset.onpreload = [];

				loadAsset(
					{ url: asset.url, type: "cache", ext: asset.ext},
					function () { onPreload(asset); },
					doc
				);
			}
		}

		if (!BX.type.isArray(items))
		{
			return;
		}

		if (!BX.type.isFunction(callback))
		{
			callback = null;
		}

		var rest = [].slice.call(items, 1);
		for (var i = 0; i < rest.length; i++)
		{
			preLoad(getAsset(rest[i]));
		}

		load(getAsset(items[0]), items.length === 1 ? callback : function () {
			loadAsyncEmulation.apply(null, [rest, callback, doc]);
		}, doc);
	}

	function load(asset, callback, doc)
	{
		callback = callback || BX.DoNothing;

		if (asset.state === LOADED)
		{
			callback();
			return;
		}

		if (asset.state === PRELOADING)
		{
			asset.onpreload.push(function () {
				load(asset, callback, doc);
			});
			return;
		}

		asset.state = LOADING;

		loadAsset(
			asset,
			function () {
				asset.state = LOADED;
				callback();
			},
			doc
		);
	}

	function loadAsset(asset, callback, doc)
	{
		callback = callback || BX.DoNothing;

		function error(event)
		{
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

		var ele;
		var ext = BX.type.isNotEmptyString(asset.ext) ? asset.ext : BX.util.getExtension(asset.url);

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

		var templateLink = null;
		var head = doc.head || doc.getElementsByTagName("head")[0];
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

		var topWindow = BX.PageObject.getRootWindow();
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
			style: {display: 'none'},
			children: [
				BX.create('DIV', {
					props: {className: 'bx-panel-tooltip-top-border'},
					html: '<div class="bx-panel-tooltip-corner bx-panel-tooltip-left-corner"></div><div class="bx-panel-tooltip-border"></div><div class="bx-panel-tooltip-corner bx-panel-tooltip-right-corner"></div>'
				}),
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
				})),

				BX.create('DIV', {
					props: {className: 'bx-panel-tooltip-bottom-border'},
					html: '<div class="bx-panel-tooltip-corner bx-panel-tooltip-left-corner"></div><div class="bx-panel-tooltip-border"></div><div class="bx-panel-tooltip-corner bx-panel-tooltip-right-corner"></div>'
				})
			]
		}));

		if (this.ID)
		{
			this.CONTENT.insertBefore(BX.create('A', {
				attrs: {href: 'javascript:void(0)'},
				props: {className: 'bx-panel-tooltip-close'},
				events: {click: BX.delegate(this.Close, this)}
			}), this.CONTENT.firstChild)
		}

		if (this.HINT_TITLE)
		{
			this.CONTENT.appendChild(
				BX.create('DIV', {
					props: {className: 'bx-panel-tooltip-title'},
					text: this.HINT_TITLE
				})
			)
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
	ajax_session = null,
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
			config.data = JSON.stringify(config.data);
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

		if(!!config.onprogress)
		{
			BX.bind(config.xhr, 'progress', config.onprogress);
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
					config.onfailure("timeout");
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
								config.onfailure("auth", config.xhr.status);
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
							config.onfailure("status", config.xhr.status);
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

BX.ajax.__prepareOnload = function(scripts)
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

BX.ajax.__runOnload = function()
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

	var bSessionCreated = false;
	if (null == ajax_session)
	{
		ajax_session = parseInt(Math.random() * 1000000);
		bSessionCreated = true;
	}

	if (styles.length > 0)
		BX.loadCSS(styles);

	if (config.emulateOnload)
			BX.ajax.__prepareOnload(scripts);

	var cb = BX.DoNothing;
	if(config.emulateOnload || bSessionCreated)
	{
		cb = BX.defer(function()
		{
			if (config.emulateOnload)
				BX.ajax.__runOnload();
			if (bSessionCreated)
				ajax_session = null;
			BX.onCustomEvent(config.xhr, 'onAjaxSuccessFinish', [config]);
		});
	}

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
	config.onfailure = function(reason, data)
	{
		result.reject({
			reason: reason,
			data: data
		});
	};
	config.onprogress = function(data)
	{
		if (data.position == 0 && data.totalSize == 0)
		{
			result.reject({
				reason: 'progress',
				data: data
			});
		}
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

var prepareAjaxGetParameters = function(config)
{
	var getParameters = config.getParameters || {};
	if (BX.type.isNotEmptyString(config.analyticsLabel))
	{
		getParameters.analyticsLabel = config.analyticsLabel;
	}
	else if (BX.type.isNotEmptyObject(config.analyticsLabel))
	{
		getParameters.analyticsLabel = config.analyticsLabel;
	}
	if (typeof config.mode !== 'undefined')
	{
		getParameters.mode = config.mode;
	}
	if (config.navigation)
	{
		if(config.navigation.page)
		{
			getParameters.nav = 'page-' + config.navigation.page;
		}
		if(config.navigation.size)
		{
			if(getParameters.nav)
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

	if (typeof config.json !== 'undefined')
	{
		if (!BX.type.isPlainObject(config.json))
		{
			throw new Error('Wrong `config.json`, plain object expected.')
		}

		config.headers = config.headers || [];
		config.headers.push({name: 'Content-Type', value: 'application/json'});
		config.headers.push({name: 'X-Bitrix-Csrf-Token', value: BX.bitrix_sessid()});
		if (BX.message.SITE_ID)
		{
			config.headers.push({name: 'X-Bitrix-Site-Id', value: BX.message.SITE_ID});
		}

		config.data = config.json;
		config.preparePost = false;
	}
	else if (config.data instanceof FormData)
	{
		config.preparePost = false;

		config.data.append('sessid', BX.bitrix_sessid());
		if (BX.message.SITE_ID)
		{
			config.data.append('SITE_ID', BX.message.SITE_ID);
		}
		if (typeof config.signedParameters !== 'undefined')
		{
			config.data.append('signedParameters', config.signedParameters);
		}
	}
	else
	{
		config.data = BX.type.isPlainObject(config.data) ? config.data : {};
		if (BX.message.SITE_ID)
		{
			config.data.SITE_ID = BX.message.SITE_ID;
		}
		config.data.sessid = BX.bitrix_sessid();
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
					originalConfig.data.sessid = BX.bitrix_sessid();

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
			var strings = BX.prop.getArray(assets, "string", []);
			var stringAsset = strings.join('\n');
			BX.html(null, stringAsset).then(function(){
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
		onrequeststart: config.onrequeststart
	});
};

/**
 *
 * @param {string} component
 * @param {string} action
 * @param {Object} config
 * @param {?string|?Object} [config.analyticsLabel]
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
		onrequeststart: (config.onrequeststart ? config.onrequeststart : null)
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
		obTitle.removeChild(obTitle.firstChild);
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
	BX.userOptions.path = url.indexOf('?') == -1? url+'?': url+'&';
}
BX.userOptions.save = function(sCategory, sName, sValName, sVal, bCommon)
{
	if (null == BX.userOptions.options)
		BX.userOptions.options = {};

	bCommon = !!bCommon;
	BX.userOptions.options[sCategory+'.'+sName+'.'+sValName] = [sCategory, sName, sValName, sVal, bCommon];

	var sParam = BX.userOptions.__get();
	if (sParam != '')
		document.cookie = BX.message('COOKIE_PREFIX')+"_LAST_SETTINGS=" + encodeURIComponent(sParam) + "&sessid="+BX.bitrix_sessid()+"; expires=Thu, 31 Dec 2020 23:59:59 GMT; path=/;";

	if(!BX.userOptions.bSend)
	{
		BX.userOptions.bSend = true;
		setTimeout(function(){BX.userOptions.send(null)}, BX.userOptions.delay);
	}
};

BX.userOptions.send = function(callback)
{
	var sParam = BX.userOptions.__get();
	BX.userOptions.options = null;
	BX.userOptions.bSend = false;

	if (sParam != '')
	{
		document.cookie = BX.message('COOKIE_PREFIX') + "_LAST_SETTINGS=; path=/;";
		BX.ajax({
			'method': 'GET',
			'dataType': 'html',
			'processData': false,
			'cache': false,
			'url': BX.userOptions.path+sParam+'&sessid='+BX.bitrix_sessid(),
			'onsuccess': callback
		});
	}
};

BX.userOptions.del = function(sCategory, sName, bCommon, callback)
{
	BX.ajax.get(BX.userOptions.path+'action=delete&c='+sCategory+'&n='+sName+(bCommon == true? '&common=Y':'')+'&sessid='+BX.bitrix_sessid(), callback);
};

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

	var ParamBag =
	/*#__PURE__*/
	function () {
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