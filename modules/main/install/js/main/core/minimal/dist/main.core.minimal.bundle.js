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
	var TypeError$f = global_1.TypeError;
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
	  throw TypeError$f('Target is not a typed array');
	};

	var aTypedArrayConstructor = function (C) {
	  if (isCallable(C) && (!objectSetPrototypeOf || objectIsPrototypeOf(TypedArray, C))) return C;
	  throw TypeError$f(tryToString(C) + ' is not a typed array constructor');
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
	    throw TypeError$f('Incorrect invocation');
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

	var floor$2 = Math.floor; // `IsIntegralNumber` abstract operation
	// https://tc39.es/ecma262/#sec-isintegralnumber
	// eslint-disable-next-line es/no-number-isinteger -- safe

	var isIntegralNumber = Number.isInteger || function isInteger(it) {
	  return !isObject(it) && isFinite(it) && floor$2(it) === it;
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

	var floor$3 = Math.floor;

	var mergeSort = function (array, comparefn) {
	  var length = array.length;
	  var middle = floor$3(length / 2);
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

	var Type = /*#__PURE__*/function () {
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
	      return !!value && (babelHelpers["typeof"](value) === 'object' || typeof value === 'function');
	    }
	    /**
	     * Checks that value is object like
	     * @param value
	     * @return {boolean}
	     */

	  }, {
	    key: "isObjectLike",
	    value: function isObjectLike(value) {
	      return !!value && babelHelpers["typeof"](value) === 'object';
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

	var Reflection = /*#__PURE__*/function () {
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

	var Text = /*#__PURE__*/function () {
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

	var Registry = /*#__PURE__*/function () {
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
	          events[event]["delete"](listener);
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

	      this.registry["delete"](target);
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
	      registry["delete"](target, key, handler);
	    });
	    return;
	  }

	  target.removeEventListener(eventName, handler, listenerOptions);
	  registry["delete"](target, eventName, handler);
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

	var Extension = /*#__PURE__*/function () {
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

	function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }

	function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
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
	      return _objectSpread({}, currentExports);
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
	  var isAllInitialized$$1 = isAllInitialized(extensions);

	  if (isAllInitialized$$1) {
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

	var Runtime = /*#__PURE__*/function () {
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
	          if (params.useAdjacentHTML) {
	            node.insertAdjacentHTML('beforeend', parsedHtml.HTML);
	          } else {
	            node.innerHTML = parsedHtml.HTML;
	          }
	        }
	      }

	      return Promise.all([loadAll(externalJs), loadAll(externalCss)]).then(function () {
	        if (Type.isDomNode(node) && (externalJs.length > 0 || externalCss.length > 0)) {
	          if (params.useAdjacentHTML) {
	            node.insertAdjacentHTML('beforeend', parsedHtml.HTML);
	          } else {
	            node.innerHTML = parsedHtml.HTML;
	          }
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
	    value: function merge$$1() {
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

	var _isError = Symbol["for"]('BX.BaseError.isError');
	/**
	 * @memberOf BX
	 */


	var BaseError = /*#__PURE__*/function () {
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

	var BaseEvent = /*#__PURE__*/function () {
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

	var EventStore = /*#__PURE__*/function () {
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
	      this.eventStore["delete"](context);
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

	var WarningStore = /*#__PURE__*/function () {
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
	      this.warnings.forEach(function (warnings) {
	        for (var eventName in warnings) {
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
	var isEmitterProperty = Symbol["for"]('BX.Event.EventEmitter.isEmitter');
	var namespaceProperty = Symbol('namespaceProperty');
	var targetProperty = Symbol('targetProperty');

	var EventEmitter = /*#__PURE__*/function () {
	  /** @private */
	  function EventEmitter() {
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
	      var _this = this;

	      if (!Type.isPlainObject(options)) {
	        return;
	      }

	      aliases = Type.isPlainObject(aliases) ? EventEmitter.normalizeAliases(aliases) : {};
	      Object.keys(options).forEach(function (eventName) {
	        var listener = EventEmitter.normalizeListener(options[eventName]);
	        eventName = EventEmitter.normalizeEventName(eventName);

	        if (aliases[eventName]) {
	          var actualName = aliases[eventName].eventName;
	          EventEmitter.subscribe(_this, actualName, listener, {
	            compatMode: compatMode !== false
	          });
	        } else {
	          EventEmitter.subscribe(_this, eventName, listener, {
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

	      listener = this.normalizeListener(listener);
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
	      var _this2 = this;

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

	      listener = this.normalizeListener(listener);
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
	          _this2.unsubscribe(target, eventName, once);

	          onceListeners["delete"](listener);
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

	      listener = this.normalizeListener(listener);
	      options = Type.isPlainObject(options) ? options : {};
	      var fullEventName = this.resolveEventName(eventName, target, options.useGlobalNaming === true);
	      var targetInfo = eventStore.get(target);
	      var listeners = targetInfo && targetInfo.eventsMap.get(fullEventName);
	      var onceListeners = targetInfo && targetInfo.onceMap.get(fullEventName);

	      if (listeners) {
	        listeners["delete"](listener);
	      }

	      if (onceListeners) {
	        var once = onceListeners.get(listener);

	        if (once) {
	          onceListeners["delete"](listener);
	          listeners["delete"](once);
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
	          targetInfo.eventsMap["delete"](fullEventName);
	          targetInfo.onceMap["delete"](fullEventName);
	        }
	      } else if (Type.isNil(eventName)) {
	        if (target === this.GLOBAL_TARGET) {
	          console.error('You cannot unsubscribe all global listeners.');
	        } else {
	          eventStore["delete"](target);
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
	      var _this3 = this;

	      var globalEvents = eventStore.get(this.GLOBAL_TARGET);

	      if (!globalEvents) {
	        return;
	      }

	      Object.keys(aliases).forEach(function (alias) {
	        var options = aliases[alias];
	        alias = _this3.normalizeEventName(alias);

	        var fullEventName = _this3.makeFullEventName(options.namespace, options.eventName);

	        var aliasListeners = globalEvents.eventsMap.get(alias);

	        if (aliasListeners) {
	          var listeners = globalEvents.eventsMap.get(fullEventName) || new Map();
	          globalEvents.eventsMap.set(fullEventName, new Map([].concat(babelHelpers.toConsumableArray(listeners), babelHelpers.toConsumableArray(aliasListeners))));
	          globalEvents.eventsMap["delete"](alias);
	        }

	        var aliasOnceListeners = globalEvents.onceMap.get(alias);

	        if (aliasOnceListeners) {
	          var onceListeners = globalEvents.onceMap.get(fullEventName) || new Map();
	          globalEvents.onceMap.set(fullEventName, new Map([].concat(babelHelpers.toConsumableArray(onceListeners), babelHelpers.toConsumableArray(aliasOnceListeners))));
	          globalEvents.onceMap["delete"](alias);
	        }

	        var aliasMaxListeners = globalEvents.eventsMaxListeners.get(alias);

	        if (aliasMaxListeners) {
	          var eventMaxListeners = globalEvents.eventsMaxListeners.get(fullEventName) || 0;
	          globalEvents.eventsMaxListeners.set(fullEventName, Math.max(eventMaxListeners, aliasMaxListeners));
	          globalEvents.eventsMaxListeners["delete"](alias);
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
	        throw new TypeError("The \"listener\" argument must be of type Function. Received type ".concat(babelHelpers["typeof"](listener), "."));
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

	    default:
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

	var Dom = /*#__PURE__*/function () {
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

	var Browser = /*#__PURE__*/function () {
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

	function ownKeys$1(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }

	function _objectSpread$1(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys$1(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys$1(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }

	var Cookie = /*#__PURE__*/function () {
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

	      var attributes = _objectSpread$1({
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
	      Cookie.set(name, '', _objectSpread$1(_objectSpread$1({}, options), {}, {
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

	var Data = /*#__PURE__*/function () {
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

	var Loc = /*#__PURE__*/function () {
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
	    value: function getMessage(messageId) {
	      var replacements = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
	      var mess = message(messageId);

	      if (Type.isString(mess) && Type.isPlainObject(replacements)) {
	        Object.keys(replacements).forEach(function (replacement) {
	          var globalRegexp = new RegExp(replacement, 'gi');
	          mess = mess.replace(globalRegexp, function () {
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
	        message(babelHelpers.defineProperty({}, id, value));
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
	    value: function getMessagePlural(messageId, value) {
	      var replacements = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : null;
	      var result = '';

	      if (Type.isNumber(value)) {
	        if (this.hasMessage("".concat(messageId, "_PLURAL_").concat(this.getPluralForm(value)))) {
	          result = this.getMessage("".concat(messageId, "_PLURAL_").concat(this.getPluralForm(value)), replacements);
	        } else {
	          result = this.getMessage("".concat(messageId, "_PLURAL_1"), replacements);
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
	      var pluralForm;

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
	      handlers["delete"](key);
	    }
	  });
	}

	function replaceChild(element, childrenMap) {
	  childrenMap.forEach(function (item, id) {
	    var currentElement = element.getElementById(id);

	    if (currentElement) {
	      Dom.replace(currentElement, item);
	      children["delete"](id);
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


	var Tag = /*#__PURE__*/function () {
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

	function ownKeys$2(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }

	function _objectSpread$2(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys$2(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys$2(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	function prepareParamValue(value) {
	  if (Type.isArray(value)) {
	    return value.map(function (item) {
	      return String(item);
	    });
	  }

	  if (Type.isPlainObject(value)) {
	    return _objectSpread$2({}, value);
	  }

	  return String(value);
	}

	function ownKeys$3(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }

	function _objectSpread$3(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys$3(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys$3(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	var map = new WeakMap();
	/**
	 * Implements interface for works with URI
	 * @memberOf BX
	 */

	var Uri = /*#__PURE__*/function () {
	  babelHelpers.createClass(Uri, null, [{
	    key: "addParam",
	    value: function addParam(url) {
	      var params = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	      return new Uri(url).setQueryParams(params).toString();
	    }
	  }, {
	    key: "removeParam",
	    value: function removeParam(url, params) {
	      var _Uri;

	      var removableParams = Type.isArray(params) ? params : [params];
	      return (_Uri = new Uri(url)).removeQueryParam.apply(_Uri, babelHelpers.toConsumableArray(removableParams)).toString();
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
	      return _objectSpread$3({}, map.get(this).queryParams);
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

	      var newParams = _objectSpread$3(_objectSpread$3({}, currentParams), params);

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
	      var currentParams = _objectSpread$3({}, map.get(this).queryParams);

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
	      var serialized = _objectSpread$3({}, map.get(this));

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
	      var data = _objectSpread$3({}, map.get(this));

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
	var Validation = /*#__PURE__*/function () {
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

	var BaseCache = /*#__PURE__*/function () {
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
	      this.storage["delete"](key);
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

	var MemoryCache = /*#__PURE__*/function (_BaseCache) {
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

	var LsStorage = /*#__PURE__*/function () {
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

	var LocalStorageCache = /*#__PURE__*/function (_BaseCache) {
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

	var _Symbol$iterator;

	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }

	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }

	var _searchIndexToInsert = /*#__PURE__*/new WeakSet();

	_Symbol$iterator = Symbol.iterator;

	var OrderedArray = /*#__PURE__*/function () {
	  function OrderedArray() {
	    var comparator = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
	    babelHelpers.classCallCheck(this, OrderedArray);

	    _classPrivateMethodInitSpec(this, _searchIndexToInsert);

	    babelHelpers.defineProperty(this, "comparator", null);
	    babelHelpers.defineProperty(this, "items", []);
	    this.comparator = Type.isFunction(comparator) ? comparator : null;
	  }

	  babelHelpers.createClass(OrderedArray, [{
	    key: "add",
	    value: function add(item) {
	      var index = -1;

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
	        var item = this.items[index];
	        return Type.isUndefined(item) ? null : item;
	      }

	      return null;
	    }
	  }, {
	    key: "getFirst",
	    value: function getFirst() {
	      var first = this.items[0];
	      return Type.isUndefined(first) ? null : first;
	    }
	  }, {
	    key: "getLast",
	    value: function getLast() {
	      var last = this.items[this.count() - 1];
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
	      var index = this.getIndex(item);

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
	    value: function value() {
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
	      var _this = this;

	      var comparator = this.getComparator();

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


	      var length = this.items.length;
	      var indexes = new Array(length);

	      for (var i = 0; i < length; i++) {
	        indexes[i] = i;
	      } // If the comparator returns zero, use the original indexes


	      indexes.sort(function (index1, index2) {
	        return comparator(_this.items[index1], _this.items[index2]) || index1 - index2;
	      });

	      for (var _i = 0; _i < length; _i++) {
	        indexes[_i] = this.items[indexes[_i]];
	      }

	      for (var _i2 = 0; _i2 < length; _i2++) {
	        this.items[_i2] = indexes[_i2];
	      }
	    }
	  }]);
	  return OrderedArray;
	}();

	function _searchIndexToInsert2(value) {
	  var low = 0;
	  var high = this.items.length;

	  while (low < high) {
	    var mid = Math.floor((low + high) / 2);

	    if (this.comparator(this.items[mid], value) >= 0) {
	      high = mid;
	    } else {
	      low = mid + 1;
	    }
	  }

	  return low;
	}

	var ZIndexComponent = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(ZIndexComponent, _EventEmitter);

	  function ZIndexComponent(element) {
	    var _this;

	    var componentOptions = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
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
	    var options = Type.isPlainObject(componentOptions) ? componentOptions : {};

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
	      var changed = this.getZIndex() !== zIndex;
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

	var ZIndexStack = /*#__PURE__*/function () {
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

	    var comparator = function comparator(componentA, componentB) {
	      var result = (componentA.getAlwaysOnTop() || 0) - (componentB.getAlwaysOnTop() || 0);

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
	    value: function register(element) {
	      var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};

	      if (this.getComponent(element)) {
	        console.warn('ZIndexManager: You cannot register the element twice.', element);
	        return this.getComponent(element);
	      }

	      var component = new ZIndexComponent(element, options);
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
	      var component = this.elements.get(element);
	      this.components["delete"](component);
	      this.elements["delete"](element);
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
	      var last = this.components.getLast();
	      return last ? last.getZIndex() : this.baseIndex;
	    }
	  }, {
	    key: "sort",
	    value: function sort() {
	      var _this = this;

	      this.components.sort();
	      var zIndex = this.baseIndex;
	      this.components.forEach(function (component) {
	        component.setZIndex(zIndex);
	        zIndex += _this.baseStep;
	      });
	    }
	  }, {
	    key: "bringToFront",
	    value: function bringToFront(element) {
	      var component = this.getComponent(element);

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
	var ZIndexManager = /*#__PURE__*/function () {
	  function ZIndexManager() {
	    babelHelpers.classCallCheck(this, ZIndexManager);
	  }

	  babelHelpers.createClass(ZIndexManager, null, [{
	    key: "register",
	    value: function register(element) {
	      var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};

	      var parentNode = _classStaticPrivateMethodGet(this, ZIndexManager, _getParentNode).call(this, element);

	      if (!parentNode) {
	        return null;
	      }

	      var stack = this.getOrAddStack(parentNode);
	      return stack.register(element, options);
	    }
	  }, {
	    key: "unregister",
	    value: function unregister(element) {
	      var parentNode = _classStaticPrivateMethodGet(this, ZIndexManager, _getParentNode).call(this, element);

	      var stack = this.getStack(parentNode);

	      if (stack) {
	        stack.unregister(element);
	      }
	    }
	  }, {
	    key: "addStack",
	    value: function addStack(container) {
	      var stack = new ZIndexStack(container);
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
	      var parentNode = _classStaticPrivateMethodGet(this, ZIndexManager, _getParentNode).call(this, element, true);

	      if (!parentNode) {
	        return null;
	      }

	      var stack = this.getStack(parentNode);
	      return stack ? stack.getComponent(element) : null;
	    }
	  }, {
	    key: "bringToFront",
	    value: function bringToFront(element) {
	      var parentNode = _classStaticPrivateMethodGet(this, ZIndexManager, _getParentNode).call(this, element);

	      var stack = this.getStack(parentNode);

	      if (stack) {
	        return stack.bringToFront(element);
	      }

	      return null;
	    }
	  }]);
	  return ZIndexManager;
	}();

	function _getParentNode(element) {
	  var suppressWarnings = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;

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

	var SettingsCollection = /*#__PURE__*/function () {
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

	var Extension$1 = /*#__PURE__*/function () {
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

	function ownKeys$4(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }

	function _objectSpread$4(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys$4(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys$4(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }

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

	  var attributes = _objectSpread$4({}, options);

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
	var type = _objectSpread$4(_objectSpread$4({}, Object.getOwnPropertyNames(Type).filter(function (key) {
	  return !['name', 'length', 'prototype', 'caller', 'arguments'].includes(key);
	}).reduce(function (acc, key) {
	  acc[key] = Type[key];
	  return acc;
	}, {})), {}, {
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
	  return _objectSpread$4(_objectSpread$4(_objectSpread$4({}, GetWindowInnerSize(doc)), GetWindowScrollPos(doc)), GetWindowScrollSize(doc));
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
	    console.error('The "eventObject" argument must be an object. Received type ' + babelHelpers["typeof"](eventObject) + '.');
	    return;
	  }

	  if (!Type.isStringFilled(eventName)) {
	    console.error('The "eventName" argument must be a string.');
	    return;
	  }

	  if (!Type.isFunction(eventHandler)) {
	    console.error('The "eventHandler" argument must be a function. Received type ' + babelHelpers["typeof"](eventHandler) + '.');
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
	    console.error('The "eventHandler" argument must be a function. Received type ' + babelHelpers["typeof"](eventHandler) + '.');
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

	//import './internal/bx';

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
	exports.ZIndexManager = ZIndexManager;
	exports.Extension = Extension$1;
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



})();
//# sourceMappingURL=main.core.minimal.bundle.js.map