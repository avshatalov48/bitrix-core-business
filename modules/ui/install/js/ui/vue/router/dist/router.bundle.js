(function (exports,ui_vue) {
  'use strict';

  /*!
    * vue-router v3.6.5
    * (c) 2021 Evan You
    * @license MIT
    *
    * @source: https://unpkg.com/vue-router@3.5.3/dist/vue-router.esm.browser.js
    */

  function assert(condition, message) {
    if (!condition) {
      throw new Error("[vue-router] ".concat(message));
    }
  }

  function warn(condition, message) {
    if (!condition) {
      typeof console !== 'undefined' && console.warn("[vue-router] ".concat(message));
    }
  }

  function extend(a, b) {
    for (var key in b) {
      a[key] = b[key];
    }

    return a;
  }
  /*  */


  var encodeReserveRE = /[!'()*]/g;

  var encodeReserveReplacer = function encodeReserveReplacer(c) {
    return '%' + c.charCodeAt(0).toString(16);
  };

  var commaRE = /%2C/g; // fixed encodeURIComponent which is more conformant to RFC3986:
  // - escapes [!'()*]
  // - preserve commas

  var encode = function encode(str) {
    return encodeURIComponent(str).replace(encodeReserveRE, encodeReserveReplacer).replace(commaRE, ',');
  };

  function decode(str) {
    try {
      return decodeURIComponent(str);
    } catch (err) {
      {
        warn(false, "Error decoding \"".concat(str, "\". Leaving it intact."));
      }
    }

    return str;
  }

  function resolveQuery(query) {
    var extraQuery = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};

    var _parseQuery = arguments.length > 2 ? arguments[2] : undefined;

    var parse = _parseQuery || parseQuery;
    var parsedQuery;

    try {
      parsedQuery = parse(query || '');
    } catch (e) {
      warn(false, e.message);
      parsedQuery = {};
    }

    for (var key in extraQuery) {
      var value = extraQuery[key];
      parsedQuery[key] = Array.isArray(value) ? value.map(castQueryParamValue) : castQueryParamValue(value);
    }

    return parsedQuery;
  }

  var castQueryParamValue = function castQueryParamValue(value) {
    return value == null || babelHelpers["typeof"](value) === 'object' ? value : String(value);
  };

  function parseQuery(query) {
    var res = {};
    query = query.trim().replace(/^(\?|#|&)/, '');

    if (!query) {
      return res;
    }

    query.split('&').forEach(function (param) {
      var parts = param.replace(/\+/g, ' ').split('=');
      var key = decode(parts.shift());
      var val = parts.length > 0 ? decode(parts.join('=')) : null;

      if (res[key] === undefined) {
        res[key] = val;
      } else if (Array.isArray(res[key])) {
        res[key].push(val);
      } else {
        res[key] = [res[key], val];
      }
    });
    return res;
  }

  function stringifyQuery(obj) {
    var res = obj ? Object.keys(obj).map(function (key) {
      var val = obj[key];

      if (val === undefined) {
        return '';
      }

      if (val === null) {
        return encode(key);
      }

      if (Array.isArray(val)) {
        var result = [];
        val.forEach(function (val2) {
          if (val2 === undefined) {
            return;
          }

          if (val2 === null) {
            result.push(encode(key));
          } else {
            result.push(encode(key) + '=' + encode(val2));
          }
        });
        return result.join('&');
      }

      return encode(key) + '=' + encode(val);
    }).filter(function (x) {
      return x.length > 0;
    }).join('&') : null;
    return res ? "?".concat(res) : '';
  }
  /*  */


  var trailingSlashRE = /\/?$/;

  function createRoute(record, location, redirectedFrom, router) {
    var stringifyQuery = router && router.options.stringifyQuery;
    var query = location.query || {};

    try {
      query = clone(query);
    } catch (e) {}

    var route = {
      name: location.name || record && record.name,
      meta: record && record.meta || {},
      path: location.path || '/',
      hash: location.hash || '',
      query: query,
      params: location.params || {},
      fullPath: getFullPath(location, stringifyQuery),
      matched: record ? formatMatch(record) : []
    };

    if (redirectedFrom) {
      route.redirectedFrom = getFullPath(redirectedFrom, stringifyQuery);
    }

    return Object.freeze(route);
  }

  function clone(value) {
    if (Array.isArray(value)) {
      return value.map(clone);
    } else if (value && babelHelpers["typeof"](value) === 'object') {
      var res = {};

      for (var key in value) {
        res[key] = clone(value[key]);
      }

      return res;
    } else {
      return value;
    }
  } // the starting route that represents the initial state


  var START = createRoute(null, {
    path: '/'
  });

  function formatMatch(record) {
    var res = [];

    while (record) {
      res.unshift(record);
      record = record.parent;
    }

    return res;
  }

  function getFullPath(_ref, _stringifyQuery) {
    var path = _ref.path,
        _ref$query = _ref.query,
        query = _ref$query === void 0 ? {} : _ref$query,
        _ref$hash = _ref.hash,
        hash = _ref$hash === void 0 ? '' : _ref$hash;
    var stringify = _stringifyQuery || stringifyQuery;
    return (path || '/') + stringify(query) + hash;
  }

  function isSameRoute(a, b, onlyPath) {
    if (b === START) {
      return a === b;
    } else if (!b) {
      return false;
    } else if (a.path && b.path) {
      return a.path.replace(trailingSlashRE, '') === b.path.replace(trailingSlashRE, '') && (onlyPath || a.hash === b.hash && isObjectEqual(a.query, b.query));
    } else if (a.name && b.name) {
      return a.name === b.name && (onlyPath || a.hash === b.hash && isObjectEqual(a.query, b.query) && isObjectEqual(a.params, b.params));
    } else {
      return false;
    }
  }

  function isObjectEqual() {
    var a = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
    var b = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
    // handle null value #1566
    if (!a || !b) return a === b;
    var aKeys = Object.keys(a).sort();
    var bKeys = Object.keys(b).sort();

    if (aKeys.length !== bKeys.length) {
      return false;
    }

    return aKeys.every(function (key, i) {
      var aVal = a[key];
      var bKey = bKeys[i];
      if (bKey !== key) return false;
      var bVal = b[key]; // query values can be null and undefined

      if (aVal == null || bVal == null) return aVal === bVal; // check nested equality

      if (babelHelpers["typeof"](aVal) === 'object' && babelHelpers["typeof"](bVal) === 'object') {
        return isObjectEqual(aVal, bVal);
      }

      return String(aVal) === String(bVal);
    });
  }

  function isIncludedRoute(current, target) {
    return current.path.replace(trailingSlashRE, '/').indexOf(target.path.replace(trailingSlashRE, '/')) === 0 && (!target.hash || current.hash === target.hash) && queryIncludes(current.query, target.query);
  }

  function queryIncludes(current, target) {
    for (var key in target) {
      if (!(key in current)) {
        return false;
      }
    }

    return true;
  }

  function handleRouteEntered(route) {
    for (var i = 0; i < route.matched.length; i++) {
      var record = route.matched[i];

      for (var name in record.instances) {
        var instance = record.instances[name];
        var cbs = record.enteredCbs[name];
        if (!instance || !cbs) continue;
        delete record.enteredCbs[name];

        for (var _i = 0; _i < cbs.length; _i++) {
          if (!instance._isBeingDestroyed) cbs[_i](instance);
        }
      }
    }
  }

  var View = {
    name: 'RouterView',
    functional: true,
    props: {
      name: {
        type: String,
        "default": 'default'
      }
    },
    render: function render(_, _ref2) {
      var props = _ref2.props,
          children = _ref2.children,
          parent = _ref2.parent,
          data = _ref2.data;
      // used by devtools to display a router-view badge
      data.routerView = true; // directly use parent context's createElement() function
      // so that components rendered by router-view can resolve named slots

      var h = parent.$createElement;
      var name = props.name;
      var route = parent.$route;
      var cache = parent._routerViewCache || (parent._routerViewCache = {}); // determine current view depth, also check to see if the tree
      // has been toggled inactive but kept-alive.

      var depth = 0;
      var inactive = false;

      while (parent && parent._routerRoot !== parent) {
        var vnodeData = parent.$vnode ? parent.$vnode.data : {};

        if (vnodeData.routerView) {
          depth++;
        }

        if (vnodeData.keepAlive && parent._directInactive && parent._inactive) {
          inactive = true;
        }

        parent = parent.$parent;
      }

      data.routerViewDepth = depth; // render previous view if the tree is inactive and kept-alive

      if (inactive) {
        var cachedData = cache[name];
        var cachedComponent = cachedData && cachedData.component;

        if (cachedComponent) {
          // #2301
          // pass props
          if (cachedData.configProps) {
            fillPropsinData(cachedComponent, data, cachedData.route, cachedData.configProps);
          }

          return h(cachedComponent, data, children);
        } else {
          // render previous empty view
          return h();
        }
      }

      var matched = route.matched[depth];
      var component = matched && matched.components[name]; // render empty node if no matched route or no config component

      if (!matched || !component) {
        cache[name] = null;
        return h();
      } // cache component


      cache[name] = {
        component: component
      }; // attach instance registration hook
      // this will be called in the instance's injected lifecycle hooks

      data.registerRouteInstance = function (vm, val) {
        // val could be undefined for unregistration
        var current = matched.instances[name];

        if (val && current !== vm || !val && current === vm) {
          matched.instances[name] = val;
        }
      } // also register instance in prepatch hook
      // in case the same component instance is reused across different routes
      ;

      (data.hook || (data.hook = {})).prepatch = function (_, vnode) {
        matched.instances[name] = vnode.componentInstance;
      }; // register instance in init hook
      // in case kept-alive component be actived when routes changed


      data.hook.init = function (vnode) {
        if (vnode.data.keepAlive && vnode.componentInstance && vnode.componentInstance !== matched.instances[name]) {
          matched.instances[name] = vnode.componentInstance;
        } // if the route transition has already been confirmed then we weren't
        // able to call the cbs during confirmation as the component was not
        // registered yet, so we call it here.


        handleRouteEntered(route);
      };

      var configProps = matched.props && matched.props[name]; // save route and configProps in cache

      if (configProps) {
        extend(cache[name], {
          route: route,
          configProps: configProps
        });
        fillPropsinData(component, data, route, configProps);
      }

      return h(component, data, children);
    }
  };

  function fillPropsinData(component, data, route, configProps) {
    // resolve props
    var propsToPass = data.props = resolveProps(route, configProps);

    if (propsToPass) {
      // clone to prevent mutation
      propsToPass = data.props = extend({}, propsToPass); // pass non-declared props as attrs

      var attrs = data.attrs = data.attrs || {};

      for (var key in propsToPass) {
        if (!component.props || !(key in component.props)) {
          attrs[key] = propsToPass[key];
          delete propsToPass[key];
        }
      }
    }
  }

  function resolveProps(route, config) {
    switch (babelHelpers["typeof"](config)) {
      case 'undefined':
        return;

      case 'object':
        return config;

      case 'function':
        return config(route);

      case 'boolean':
        return config ? route.params : undefined;

      default:
        {
          warn(false, "props in \"".concat(route.path, "\" is a ").concat(babelHelpers["typeof"](config), ", ") + "expecting an object, function or boolean.");
        }
    }
  }
  /*  */


  function resolvePath(relative, base, append) {
    var firstChar = relative.charAt(0);

    if (firstChar === '/') {
      return relative;
    }

    if (firstChar === '?' || firstChar === '#') {
      return base + relative;
    }

    var stack = base.split('/'); // remove trailing segment if:
    // - not appending
    // - appending to trailing slash (last segment is empty)

    if (!append || !stack[stack.length - 1]) {
      stack.pop();
    } // resolve relative path


    var segments = relative.replace(/^\//, '').split('/');

    for (var i = 0; i < segments.length; i++) {
      var segment = segments[i];

      if (segment === '..') {
        stack.pop();
      } else if (segment !== '.') {
        stack.push(segment);
      }
    } // ensure leading slash


    if (stack[0] !== '') {
      stack.unshift('');
    }

    return stack.join('/');
  }

  function parsePath(path) {
    var hash = '';
    var query = '';
    var hashIndex = path.indexOf('#');

    if (hashIndex >= 0) {
      hash = path.slice(hashIndex);
      path = path.slice(0, hashIndex);
    }

    var queryIndex = path.indexOf('?');

    if (queryIndex >= 0) {
      query = path.slice(queryIndex + 1);
      path = path.slice(0, queryIndex);
    }

    return {
      path: path,
      query: query,
      hash: hash
    };
  }

  function cleanPath(path) {
    return path.replace(/\/(?:\s*\/)+/g, '/');
  }

  var isarray = Array.isArray || function (arr) {
    return Object.prototype.toString.call(arr) == '[object Array]';
  };
  /**
   * Expose `pathToRegexp`.
   */


  var pathToRegexp_1 = pathToRegexp;
  var parse_1 = parse;
  var compile_1 = compile;
  var tokensToFunction_1 = tokensToFunction;
  var tokensToRegExp_1 = tokensToRegExp;
  /**
   * The main path matching regexp utility.
   *
   * @type {RegExp}
   */

  var PATH_REGEXP = new RegExp([// Match escaped characters that would otherwise appear in future matches.
  // This allows the user to escape special characters that won't transform.
  '(\\\\.)', // Match Express-style parameters and un-named parameters with a prefix
  // and optional suffixes. Matches appear as:
  //
  // "/:test(\\d+)?" => ["/", "test", "\d+", undefined, "?", undefined]
  // "/route(\\d+)"  => [undefined, undefined, undefined, "\d+", undefined, undefined]
  // "/*"      => ["/", undefined, undefined, undefined, undefined, "*"]
  '([\\/.])?(?:(?:\\:(\\w+)(?:\\(((?:\\\\.|[^\\\\()])+)\\))?|\\(((?:\\\\.|[^\\\\()])+)\\))([+*?])?|(\\*))'].join('|'), 'g');
  /**
   * Parse a string for the raw tokens.
   *
   * @param  {string}  str
   * @param  {Object=} options
   * @return {!Array}
   */

  function parse(str, options) {
    var tokens = [];
    var key = 0;
    var index = 0;
    var path = '';
    var defaultDelimiter = options && options.delimiter || '/';
    var res;

    while ((res = PATH_REGEXP.exec(str)) != null) {
      var m = res[0];
      var escaped = res[1];
      var offset = res.index;
      path += str.slice(index, offset);
      index = offset + m.length; // Ignore already escaped sequences.

      if (escaped) {
        path += escaped[1];
        continue;
      }

      var next = str[index];
      var prefix = res[2];
      var name = res[3];
      var capture = res[4];
      var group = res[5];
      var modifier = res[6];
      var asterisk = res[7]; // Push the current path onto the tokens.

      if (path) {
        tokens.push(path);
        path = '';
      }

      var partial = prefix != null && next != null && next !== prefix;
      var repeat = modifier === '+' || modifier === '*';
      var optional = modifier === '?' || modifier === '*';
      var delimiter = res[2] || defaultDelimiter;
      var pattern = capture || group;
      tokens.push({
        name: name || key++,
        prefix: prefix || '',
        delimiter: delimiter,
        optional: optional,
        repeat: repeat,
        partial: partial,
        asterisk: !!asterisk,
        pattern: pattern ? escapeGroup(pattern) : asterisk ? '.*' : '[^' + escapeString(delimiter) + ']+?'
      });
    } // Match any characters still remaining.


    if (index < str.length) {
      path += str.substr(index);
    } // If the path exists, push it onto the end.


    if (path) {
      tokens.push(path);
    }

    return tokens;
  }
  /**
   * Compile a string to a template function for the path.
   *
   * @param  {string}       str
   * @param  {Object=}      options
   * @return {!function(Object=, Object=)}
   */


  function compile(str, options) {
    return tokensToFunction(parse(str, options), options);
  }
  /**
   * Prettier encoding of URI path segments.
   *
   * @param  {string}
   * @return {string}
   */


  function encodeURIComponentPretty(str) {
    return encodeURI(str).replace(/[\/?#]/g, function (c) {
      return '%' + c.charCodeAt(0).toString(16).toUpperCase();
    });
  }
  /**
   * Encode the asterisk parameter. Similar to `pretty`, but allows slashes.
   *
   * @param  {string}
   * @return {string}
   */


  function encodeAsterisk(str) {
    return encodeURI(str).replace(/[?#]/g, function (c) {
      return '%' + c.charCodeAt(0).toString(16).toUpperCase();
    });
  }
  /**
   * Expose a method for transforming tokens into the path function.
   */


  function tokensToFunction(tokens, options) {
    // Compile all the tokens into regexps.
    var matches = new Array(tokens.length); // Compile all the patterns before compilation.

    for (var i = 0; i < tokens.length; i++) {
      if (babelHelpers["typeof"](tokens[i]) === 'object') {
        matches[i] = new RegExp('^(?:' + tokens[i].pattern + ')$', flags(options));
      }
    }

    return function (obj, opts) {
      var path = '';
      var data = obj || {};
      var options = opts || {};
      var encode = options.pretty ? encodeURIComponentPretty : encodeURIComponent;

      for (var i = 0; i < tokens.length; i++) {
        var token = tokens[i];

        if (typeof token === 'string') {
          path += token;
          continue;
        }

        var value = data[token.name];
        var segment;

        if (value == null) {
          if (token.optional) {
            // Prepend partial segment prefixes.
            if (token.partial) {
              path += token.prefix;
            }

            continue;
          } else {
            throw new TypeError('Expected "' + token.name + '" to be defined');
          }
        }

        if (isarray(value)) {
          if (!token.repeat) {
            throw new TypeError('Expected "' + token.name + '" to not repeat, but received `' + JSON.stringify(value) + '`');
          }

          if (value.length === 0) {
            if (token.optional) {
              continue;
            } else {
              throw new TypeError('Expected "' + token.name + '" to not be empty');
            }
          }

          for (var j = 0; j < value.length; j++) {
            segment = encode(value[j]);

            if (!matches[i].test(segment)) {
              throw new TypeError('Expected all "' + token.name + '" to match "' + token.pattern + '", but received `' + JSON.stringify(segment) + '`');
            }

            path += (j === 0 ? token.prefix : token.delimiter) + segment;
          }

          continue;
        }

        segment = token.asterisk ? encodeAsterisk(value) : encode(value);

        if (!matches[i].test(segment)) {
          throw new TypeError('Expected "' + token.name + '" to match "' + token.pattern + '", but received "' + segment + '"');
        }

        path += token.prefix + segment;
      }

      return path;
    };
  }
  /**
   * Escape a regular expression string.
   *
   * @param  {string} str
   * @return {string}
   */


  function escapeString(str) {
    return str.replace(/([.+*?=^!:${}()[\]|\/\\])/g, '\\$1');
  }
  /**
   * Escape the capturing group by escaping special characters and meaning.
   *
   * @param  {string} group
   * @return {string}
   */


  function escapeGroup(group) {
    return group.replace(/([=!:$\/()])/g, '\\$1');
  }
  /**
   * Attach the keys as a property of the regexp.
   *
   * @param  {!RegExp} re
   * @param  {Array}   keys
   * @return {!RegExp}
   */


  function attachKeys(re, keys) {
    re.keys = keys;
    return re;
  }
  /**
   * Get the flags for a regexp from the options.
   *
   * @param  {Object} options
   * @return {string}
   */


  function flags(options) {
    return options && options.sensitive ? '' : 'i';
  }
  /**
   * Pull out keys from a regexp.
   *
   * @param  {!RegExp} path
   * @param  {!Array}  keys
   * @return {!RegExp}
   */


  function regexpToRegexp(path, keys) {
    // Use a negative lookahead to match only capturing groups.
    var groups = path.source.match(/\((?!\?)/g);

    if (groups) {
      for (var i = 0; i < groups.length; i++) {
        keys.push({
          name: i,
          prefix: null,
          delimiter: null,
          optional: false,
          repeat: false,
          partial: false,
          asterisk: false,
          pattern: null
        });
      }
    }

    return attachKeys(path, keys);
  }
  /**
   * Transform an array into a regexp.
   *
   * @param  {!Array}  path
   * @param  {Array}   keys
   * @param  {!Object} options
   * @return {!RegExp}
   */


  function arrayToRegexp(path, keys, options) {
    var parts = [];

    for (var i = 0; i < path.length; i++) {
      parts.push(pathToRegexp(path[i], keys, options).source);
    }

    var regexp = new RegExp('(?:' + parts.join('|') + ')', flags(options));
    return attachKeys(regexp, keys);
  }
  /**
   * Create a path regexp from string input.
   *
   * @param  {string}  path
   * @param  {!Array}  keys
   * @param  {!Object} options
   * @return {!RegExp}
   */


  function stringToRegexp(path, keys, options) {
    return tokensToRegExp(parse(path, options), keys, options);
  }
  /**
   * Expose a function for taking tokens and returning a RegExp.
   *
   * @param  {!Array}      tokens
   * @param  {(Array|Object)=} keys
   * @param  {Object=}     options
   * @return {!RegExp}
   */


  function tokensToRegExp(tokens, keys, options) {
    if (!isarray(keys)) {
      options =
      /** @type {!Object} */
      keys || options;
      keys = [];
    }

    options = options || {};
    var strict = options.strict;
    var end = options.end !== false;
    var route = ''; // Iterate over the tokens and create our regexp string.

    for (var i = 0; i < tokens.length; i++) {
      var token = tokens[i];

      if (typeof token === 'string') {
        route += escapeString(token);
      } else {
        var prefix = escapeString(token.prefix);
        var capture = '(?:' + token.pattern + ')';
        keys.push(token);

        if (token.repeat) {
          capture += '(?:' + prefix + capture + ')*';
        }

        if (token.optional) {
          if (!token.partial) {
            capture = '(?:' + prefix + '(' + capture + '))?';
          } else {
            capture = prefix + '(' + capture + ')?';
          }
        } else {
          capture = prefix + '(' + capture + ')';
        }

        route += capture;
      }
    }

    var delimiter = escapeString(options.delimiter || '/');
    var endsWithDelimiter = route.slice(-delimiter.length) === delimiter; // In non-strict mode we allow a slash at the end of match. If the path to
    // match already ends with a slash, we remove it for consistency. The slash
    // is valid at the end of a path match, not in the middle. This is important
    // in non-ending mode, where "/test/" shouldn't match "/test//route".

    if (!strict) {
      route = (endsWithDelimiter ? route.slice(0, -delimiter.length) : route) + '(?:' + delimiter + '(?=$))?';
    }

    if (end) {
      route += '$';
    } else {
      // In non-ending mode, we need the capturing groups to match as much as
      // possible by using a positive lookahead to the end or next path segment.
      route += strict && endsWithDelimiter ? '' : '(?=' + delimiter + '|$)';
    }

    return attachKeys(new RegExp('^' + route, flags(options)), keys);
  }
  /**
   * Normalize the given path string, returning a regular expression.
   *
   * An empty array can be passed in for the keys, which will hold the
   * placeholder key descriptions. For example, using `/user/:id`, `keys` will
   * contain `[{ name: 'id', delimiter: '/', optional: false, repeat: false }]`.
   *
   * @param  {(string|RegExp|Array)} path
   * @param  {(Array|Object)=}     keys
   * @param  {Object=}         options
   * @return {!RegExp}
   */


  function pathToRegexp(path, keys, options) {
    if (!isarray(keys)) {
      options =
      /** @type {!Object} */
      keys || options;
      keys = [];
    }

    options = options || {};

    if (path instanceof RegExp) {
      return regexpToRegexp(path,
      /** @type {!Array} */
      keys);
    }

    if (isarray(path)) {
      return arrayToRegexp(
      /** @type {!Array} */
      path,
      /** @type {!Array} */
      keys, options);
    }

    return stringToRegexp(
    /** @type {string} */
    path,
    /** @type {!Array} */
    keys, options);
  }

  pathToRegexp_1.parse = parse_1;
  pathToRegexp_1.compile = compile_1;
  pathToRegexp_1.tokensToFunction = tokensToFunction_1;
  pathToRegexp_1.tokensToRegExp = tokensToRegExp_1;
  /*  */
  // $flow-disable-line

  var regexpCompileCache = Object.create(null);

  function fillParams(path, params, routeMsg) {
    params = params || {};

    try {
      var filler = regexpCompileCache[path] || (regexpCompileCache[path] = pathToRegexp_1.compile(path)); // Fix #2505 resolving asterisk routes { name: 'not-found', params: { pathMatch: '/not-found' }}
      // and fix #3106 so that you can work with location descriptor object having params.pathMatch equal to empty string

      if (typeof params.pathMatch === 'string') params[0] = params.pathMatch;
      return filler(params, {
        pretty: true
      });
    } catch (e) {
      {
        // Fix #3072 no warn if `pathMatch` is string
        warn(typeof params.pathMatch === 'string', "missing param for ".concat(routeMsg, ": ").concat(e.message));
      }
      return '';
    } finally {
      // delete the 0 if it was added
      delete params[0];
    }
  }
  /*  */


  function normalizeLocation(raw, current, append, router) {
    var next = typeof raw === 'string' ? {
      path: raw
    } : raw; // named target

    if (next._normalized) {
      return next;
    } else if (next.name) {
      next = extend({}, raw);
      var params = next.params;

      if (params && babelHelpers["typeof"](params) === 'object') {
        next.params = extend({}, params);
      }

      return next;
    } // relative params


    if (!next.path && next.params && current) {
      next = extend({}, next);
      next._normalized = true;

      var _params = extend(extend({}, current.params), next.params);

      if (current.name) {
        next.name = current.name;
        next.params = _params;
      } else if (current.matched.length) {
        var rawPath = current.matched[current.matched.length - 1].path;
        next.path = fillParams(rawPath, _params, "path ".concat(current.path));
      } else {
        warn(false, "relative params navigation requires a current route.");
      }

      return next;
    }

    var parsedPath = parsePath(next.path || '');
    var basePath = current && current.path || '/';
    var path = parsedPath.path ? resolvePath(parsedPath.path, basePath, append || next.append) : basePath;
    var query = resolveQuery(parsedPath.query, next.query, router && router.options.parseQuery);
    var hash = next.hash || parsedPath.hash;

    if (hash && hash.charAt(0) !== '#') {
      hash = "#".concat(hash);
    }

    return {
      _normalized: true,
      path: path,
      query: query,
      hash: hash
    };
  }
  /*  */
  // work around weird flow bug


  var toTypes = [String, Object];
  var eventTypes = [String, Array];

  var noop = function noop() {};

  var warnedCustomSlot;
  var warnedTagProp;
  var warnedEventProp;
  var Link = {
    name: 'RouterLink',
    props: {
      to: {
        type: toTypes,
        required: true
      },
      tag: {
        type: String,
        "default": 'a'
      },
      custom: Boolean,
      exact: Boolean,
      exactPath: Boolean,
      append: Boolean,
      replace: Boolean,
      activeClass: String,
      exactActiveClass: String,
      ariaCurrentValue: {
        type: String,
        "default": 'page'
      },
      event: {
        type: eventTypes,
        "default": 'click'
      }
    },
    render: function render(h) {
      var _this = this;

      var router = this.$router;
      var current = this.$route;

      var _router$resolve = router.resolve(this.to, current, this.append),
          location = _router$resolve.location,
          route = _router$resolve.route,
          href = _router$resolve.href;

      var classes = {};
      var globalActiveClass = router.options.linkActiveClass;
      var globalExactActiveClass = router.options.linkExactActiveClass; // Support global empty active class

      var activeClassFallback = globalActiveClass == null ? 'router-link-active' : globalActiveClass;
      var exactActiveClassFallback = globalExactActiveClass == null ? 'router-link-exact-active' : globalExactActiveClass;
      var activeClass = this.activeClass == null ? activeClassFallback : this.activeClass;
      var exactActiveClass = this.exactActiveClass == null ? exactActiveClassFallback : this.exactActiveClass;
      var compareTarget = route.redirectedFrom ? createRoute(null, normalizeLocation(route.redirectedFrom), null, router) : route;
      classes[exactActiveClass] = isSameRoute(current, compareTarget, this.exactPath);
      classes[activeClass] = this.exact || this.exactPath ? classes[exactActiveClass] : isIncludedRoute(current, compareTarget);
      var ariaCurrentValue = classes[exactActiveClass] ? this.ariaCurrentValue : null;

      var handler = function handler(e) {
        if (guardEvent(e)) {
          if (_this.replace) {
            router.replace(location, noop);
          } else {
            router.push(location, noop);
          }
        }
      };

      var on = {
        click: guardEvent
      };

      if (Array.isArray(this.event)) {
        this.event.forEach(function (e) {
          on[e] = handler;
        });
      } else {
        on[this.event] = handler;
      }

      var data = {
        "class": classes
      };
      var scopedSlot = !this.$scopedSlots.$hasNormal && this.$scopedSlots["default"] && this.$scopedSlots["default"]({
        href: href,
        route: route,
        navigate: handler,
        isActive: classes[activeClass],
        isExactActive: classes[exactActiveClass]
      });

      if (scopedSlot) {
        if (!this.custom) {
          !warnedCustomSlot && warn(false, 'In Vue Router 4, the v-slot API will by default wrap its content with an <a> element. Use the custom prop to remove this warning:\n<router-link v-slot="{ navigate, href }" custom></router-link>\n');
          warnedCustomSlot = true;
        }

        if (scopedSlot.length === 1) {
          return scopedSlot[0];
        } else if (scopedSlot.length > 1 || !scopedSlot.length) {
          {
            warn(false, "<router-link> with to=\"".concat(this.to, "\" is trying to use a scoped slot but it didn't provide exactly one child. Wrapping the content with a span element."));
          }
          return scopedSlot.length === 0 ? h() : h('span', {}, scopedSlot);
        }
      }

      {
        if ('tag' in this.$options.propsData && !warnedTagProp) {
          warn(false, "<router-link>'s tag prop is deprecated and has been removed in Vue Router 4. Use the v-slot API to remove this warning: https://next.router.vuejs.org/guide/migration/#removal-of-event-and-tag-props-in-router-link.");
          warnedTagProp = true;
        }

        if ('event' in this.$options.propsData && !warnedEventProp) {
          warn(false, "<router-link>'s event prop is deprecated and has been removed in Vue Router 4. Use the v-slot API to remove this warning: https://next.router.vuejs.org/guide/migration/#removal-of-event-and-tag-props-in-router-link.");
          warnedEventProp = true;
        }
      }

      if (this.tag === 'a') {
        data.on = on;
        data.attrs = {
          href: href,
          'aria-current': ariaCurrentValue
        };
      } else {
        // find the first <a> child and apply listener and href
        var a = findAnchor(this.$slots["default"]);

        if (a) {
          // in case the <a> is a static node
          a.isStatic = false;
          var aData = a.data = extend({}, a.data);
          aData.on = aData.on || {}; // transform existing events in both objects into arrays so we can push later

          for (var event in aData.on) {
            var _handler = aData.on[event];

            if (event in on) {
              aData.on[event] = Array.isArray(_handler) ? _handler : [_handler];
            }
          } // append new listeners for router-link


          for (var _event in on) {
            if (_event in aData.on) {
              // on[event] is always a function
              aData.on[_event].push(on[_event]);
            } else {
              aData.on[_event] = handler;
            }
          }

          var aAttrs = a.data.attrs = extend({}, a.data.attrs);
          aAttrs.href = href;
          aAttrs['aria-current'] = ariaCurrentValue;
        } else {
          // doesn't have <a> child, apply listener to self
          data.on = on;
        }
      }

      return h(this.tag, data, this.$slots["default"]);
    }
  };

  function guardEvent(e) {
    // don't redirect with control keys
    if (e.metaKey || e.altKey || e.ctrlKey || e.shiftKey) return; // don't redirect when preventDefault called

    if (e.defaultPrevented) return; // don't redirect on right click

    if (e.button !== undefined && e.button !== 0) return; // don't redirect if `target="_blank"`

    if (e.currentTarget && e.currentTarget.getAttribute) {
      var target = e.currentTarget.getAttribute('target');
      if (/\b_blank\b/i.test(target)) return;
    } // this may be a Weex event which doesn't have this method


    if (e.preventDefault) {
      e.preventDefault();
    }

    return true;
  }

  function findAnchor(children) {
    if (children) {
      var child;

      for (var i = 0; i < children.length; i++) {
        child = children[i];

        if (child.tag === 'a') {
          return child;
        }

        if (child.children && (child = findAnchor(child.children))) {
          return child;
        }
      }
    }
  }

  var _Vue;

  function install(Vue) {
    if (install.installed && _Vue === Vue) return;
    install.installed = true;
    _Vue = Vue;

    var isDef = function isDef(v) {
      return v !== undefined;
    };

    var registerInstance = function registerInstance(vm, callVal) {
      var i = vm.$options._parentVnode;

      if (isDef(i) && isDef(i = i.data) && isDef(i = i.registerRouteInstance)) {
        i(vm, callVal);
      }
    };

    Vue.mixin({
      beforeCreate: function beforeCreate() {
        if (isDef(this.$options.router)) {
          this._routerRoot = this;
          this._router = this.$options.router;

          this._router.init(this);

          Vue.util.defineReactive(this, '_route', this._router.history.current);
        } else {
          this._routerRoot = this.$parent && this.$parent._routerRoot || this;
        }

        registerInstance(this, this);
      },
      destroyed: function destroyed() {
        registerInstance(this);
      }
    });
    Object.defineProperty(Vue.prototype, '$router', {
      get: function get() {
        return this._routerRoot._router;
      }
    });
    Object.defineProperty(Vue.prototype, '$route', {
      get: function get() {
        return this._routerRoot._route;
      }
    });
    Vue.component('RouterView', View);
    Vue.component('RouterLink', Link);
    var strats = Vue.config.optionMergeStrategies; // use the same hook merging strategy for route hooks

    strats.beforeRouteEnter = strats.beforeRouteLeave = strats.beforeRouteUpdate = strats.created;
  }
  /*  */


  var inBrowser = typeof window !== 'undefined';
  /*  */

  function createRouteMap(routes, oldPathList, oldPathMap, oldNameMap, parentRoute) {
    // the path list is used to control path matching priority
    var pathList = oldPathList || []; // $flow-disable-line

    var pathMap = oldPathMap || Object.create(null); // $flow-disable-line

    var nameMap = oldNameMap || Object.create(null);
    routes.forEach(function (route) {
      addRouteRecord(pathList, pathMap, nameMap, route, parentRoute);
    }); // ensure wildcard routes are always at the end

    for (var i = 0, l = pathList.length; i < l; i++) {
      if (pathList[i] === '*') {
        pathList.push(pathList.splice(i, 1)[0]);
        l--;
        i--;
      }
    }

    {
      // warn if routes do not include leading slashes
      var found = pathList // check for missing leading slash
      .filter(function (path) {
        return path && path.charAt(0) !== '*' && path.charAt(0) !== '/';
      });

      if (found.length > 0) {
        var pathNames = found.map(function (path) {
          return "- ".concat(path);
        }).join('\n');
        warn(false, "Non-nested routes must include a leading slash character. Fix the following routes: \n".concat(pathNames));
      }
    }
    return {
      pathList: pathList,
      pathMap: pathMap,
      nameMap: nameMap
    };
  }

  function addRouteRecord(pathList, pathMap, nameMap, route, parent, matchAs) {
    var path = route.path,
        name = route.name;
    {
      assert(path != null, "\"path\" is required in a route configuration.");
      assert(typeof route.component !== 'string', "route config \"component\" for path: ".concat(String(path || name), " cannot be a ") + "string id. Use an actual component instead.");
      warn( // eslint-disable-next-line no-control-regex
      !/[^\u0000-\u007F]+/.test(path), "Route with path \"".concat(path, "\" contains unencoded characters, make sure ") + "your path is correctly encoded before passing it to the router. Use " + "encodeURI to encode static segments of your path.");
    }
    var pathToRegexpOptions = route.pathToRegexpOptions || {};
    var normalizedPath = normalizePath(path, parent, pathToRegexpOptions.strict);

    if (typeof route.caseSensitive === 'boolean') {
      pathToRegexpOptions.sensitive = route.caseSensitive;
    }

    var record = {
      path: normalizedPath,
      regex: compileRouteRegex(normalizedPath, pathToRegexpOptions),
      components: route.components || {
        "default": route.component
      },
      alias: route.alias ? typeof route.alias === 'string' ? [route.alias] : route.alias : [],
      instances: {},
      enteredCbs: {},
      name: name,
      parent: parent,
      matchAs: matchAs,
      redirect: route.redirect,
      beforeEnter: route.beforeEnter,
      meta: route.meta || {},
      props: route.props == null ? {} : route.components ? route.props : {
        "default": route.props
      }
    };

    if (route.children) {
      // Warn if route is named, does not redirect and has a default child route.
      // If users navigate to this route by name, the default child will
      // not be rendered (GH Issue #629)
      {
        if (route.name && !route.redirect && route.children.some(function (child) {
          return /^\/?$/.test(child.path);
        })) {
          warn(false, "Named Route '".concat(route.name, "' has a default child route. ") + "When navigating to this named route (:to=\"{name: '".concat(route.name, "'}\"), ") + "the default child route will not be rendered. Remove the name from " + "this route and use the name of the default child route for named " + "links instead.");
        }
      }
      route.children.forEach(function (child) {
        var childMatchAs = matchAs ? cleanPath("".concat(matchAs, "/").concat(child.path)) : undefined;
        addRouteRecord(pathList, pathMap, nameMap, child, record, childMatchAs);
      });
    }

    if (!pathMap[record.path]) {
      pathList.push(record.path);
      pathMap[record.path] = record;
    }

    if (route.alias !== undefined) {
      var aliases = Array.isArray(route.alias) ? route.alias : [route.alias];

      for (var i = 0; i < aliases.length; ++i) {
        var alias = aliases[i];

        if (alias === path) {
          warn(false, "Found an alias with the same value as the path: \"".concat(path, "\". You have to remove that alias. It will be ignored in development.")); // skip in dev to make it work

          continue;
        }

        var aliasRoute = {
          path: alias,
          children: route.children
        };
        addRouteRecord(pathList, pathMap, nameMap, aliasRoute, parent, record.path || '/' // matchAs
        );
      }
    }

    if (name) {
      if (!nameMap[name]) {
        nameMap[name] = record;
      } else if (!matchAs) {
        warn(false, "Duplicate named routes definition: " + "{ name: \"".concat(name, "\", path: \"").concat(record.path, "\" }"));
      }
    }
  }

  function compileRouteRegex(path, pathToRegexpOptions) {
    var regex = pathToRegexp_1(path, [], pathToRegexpOptions);
    {
      var keys = Object.create(null);
      regex.keys.forEach(function (key) {
        warn(!keys[key.name], "Duplicate param keys in route with path: \"".concat(path, "\""));
        keys[key.name] = true;
      });
    }
    return regex;
  }

  function normalizePath(path, parent, strict) {
    if (!strict) path = path.replace(/\/$/, '');
    if (path[0] === '/') return path;
    if (parent == null) return path;
    return cleanPath("".concat(parent.path, "/").concat(path));
  }
  /*  */


  function createMatcher(routes, router) {
    var _createRouteMap = createRouteMap(routes),
        pathList = _createRouteMap.pathList,
        pathMap = _createRouteMap.pathMap,
        nameMap = _createRouteMap.nameMap;

    function addRoutes(routes) {
      createRouteMap(routes, pathList, pathMap, nameMap);
    }

    function addRoute(parentOrRoute, route) {
      var parent = babelHelpers["typeof"](parentOrRoute) !== 'object' ? nameMap[parentOrRoute] : undefined; // $flow-disable-line

      createRouteMap([route || parentOrRoute], pathList, pathMap, nameMap, parent); // add aliases of parent

      if (parent && parent.alias.length) {
        createRouteMap( // $flow-disable-line route is defined if parent is
        parent.alias.map(function (alias) {
          return {
            path: alias,
            children: [route]
          };
        }), pathList, pathMap, nameMap, parent);
      }
    }

    function getRoutes() {
      return pathList.map(function (path) {
        return pathMap[path];
      });
    }

    function match(raw, currentRoute, redirectedFrom) {
      var location = normalizeLocation(raw, currentRoute, false, router);
      var name = location.name;

      if (name) {
        var record = nameMap[name];
        {
          warn(record, "Route with name '".concat(name, "' does not exist"));
        }
        if (!record) return _createRoute(null, location);
        var paramNames = record.regex.keys.filter(function (key) {
          return !key.optional;
        }).map(function (key) {
          return key.name;
        });

        if (babelHelpers["typeof"](location.params) !== 'object') {
          location.params = {};
        }

        if (currentRoute && babelHelpers["typeof"](currentRoute.params) === 'object') {
          for (var key in currentRoute.params) {
            if (!(key in location.params) && paramNames.indexOf(key) > -1) {
              location.params[key] = currentRoute.params[key];
            }
          }
        }

        location.path = fillParams(record.path, location.params, "named route \"".concat(name, "\""));
        return _createRoute(record, location, redirectedFrom);
      } else if (location.path) {
        location.params = {};

        for (var i = 0; i < pathList.length; i++) {
          var path = pathList[i];
          var _record = pathMap[path];

          if (matchRoute(_record.regex, location.path, location.params)) {
            return _createRoute(_record, location, redirectedFrom);
          }
        }
      } // no match


      return _createRoute(null, location);
    }

    function redirect(record, location) {
      var originalRedirect = record.redirect;
      var redirect = typeof originalRedirect === 'function' ? originalRedirect(createRoute(record, location, null, router)) : originalRedirect;

      if (typeof redirect === 'string') {
        redirect = {
          path: redirect
        };
      }

      if (!redirect || babelHelpers["typeof"](redirect) !== 'object') {
        {
          warn(false, "invalid redirect option: ".concat(JSON.stringify(redirect)));
        }
        return _createRoute(null, location);
      }

      var re = redirect;
      var name = re.name,
          path = re.path;
      var query = location.query,
          hash = location.hash,
          params = location.params;
      query = re.hasOwnProperty('query') ? re.query : query;
      hash = re.hasOwnProperty('hash') ? re.hash : hash;
      params = re.hasOwnProperty('params') ? re.params : params;

      if (name) {
        // resolved named direct
        var targetRecord = nameMap[name];
        {
          assert(targetRecord, "redirect failed: named route \"".concat(name, "\" not found."));
        }
        return match({
          _normalized: true,
          name: name,
          query: query,
          hash: hash,
          params: params
        }, undefined, location);
      } else if (path) {
        // 1. resolve relative redirect
        var rawPath = resolveRecordPath(path, record); // 2. resolve params

        var resolvedPath = fillParams(rawPath, params, "redirect route with path \"".concat(rawPath, "\"")); // 3. rematch with existing query and hash

        return match({
          _normalized: true,
          path: resolvedPath,
          query: query,
          hash: hash
        }, undefined, location);
      } else {
        {
          warn(false, "invalid redirect option: ".concat(JSON.stringify(redirect)));
        }
        return _createRoute(null, location);
      }
    }

    function alias(record, location, matchAs) {
      var aliasedPath = fillParams(matchAs, location.params, "aliased route with path \"".concat(matchAs, "\""));
      var aliasedMatch = match({
        _normalized: true,
        path: aliasedPath
      });

      if (aliasedMatch) {
        var matched = aliasedMatch.matched;
        var aliasedRecord = matched[matched.length - 1];
        location.params = aliasedMatch.params;
        return _createRoute(aliasedRecord, location);
      }

      return _createRoute(null, location);
    }

    function _createRoute(record, location, redirectedFrom) {
      if (record && record.redirect) {
        return redirect(record, redirectedFrom || location);
      }

      if (record && record.matchAs) {
        return alias(record, location, record.matchAs);
      }

      return createRoute(record, location, redirectedFrom, router);
    }

    return {
      match: match,
      addRoute: addRoute,
      getRoutes: getRoutes,
      addRoutes: addRoutes
    };
  }

  function matchRoute(regex, path, params) {
    var m = path.match(regex);

    if (!m) {
      return false;
    } else if (!params) {
      return true;
    }

    for (var i = 1, len = m.length; i < len; ++i) {
      var key = regex.keys[i - 1];

      if (key) {
        // Fix #1994: using * with props: true generates a param named 0
        params[key.name || 'pathMatch'] = typeof m[i] === 'string' ? decode(m[i]) : m[i];
      }
    }

    return true;
  }

  function resolveRecordPath(path, record) {
    return resolvePath(path, record.parent ? record.parent.path : '/', true);
  }
  /*  */
  // use User Timing api (if present) for more accurate key precision


  var Time = inBrowser && window.performance && window.performance.now ? window.performance : Date;

  function genStateKey() {
    return Time.now().toFixed(3);
  }

  var _key = genStateKey();

  function getStateKey() {
    return _key;
  }

  function setStateKey(key) {
    return _key = key;
  }
  /*  */


  var positionStore = Object.create(null);

  function setupScroll() {
    // Prevent browser scroll behavior on History popstate
    if ('scrollRestoration' in window.history) {
      window.history.scrollRestoration = 'manual';
    } // Fix for #1585 for Firefox
    // Fix for #2195 Add optional third attribute to workaround a bug in safari https://bugs.webkit.org/show_bug.cgi?id=182678
    // Fix for #2774 Support for apps loaded from Windows file shares not mapped to network drives: replaced location.origin with
    // window.location.protocol + '//' + window.location.host
    // location.host contains the port and location.hostname doesn't


    var protocolAndPath = window.location.protocol + '//' + window.location.host;
    var absolutePath = window.location.href.replace(protocolAndPath, ''); // preserve existing history state as it could be overriden by the user

    var stateCopy = extend({}, window.history.state);
    stateCopy.key = getStateKey();
    window.history.replaceState(stateCopy, '', absolutePath);
    window.addEventListener('popstate', handlePopState);
    return function () {
      window.removeEventListener('popstate', handlePopState);
    };
  }

  function handleScroll(router, to, from, isPop) {
    if (!router.app) {
      return;
    }

    var behavior = router.options.scrollBehavior;

    if (!behavior) {
      return;
    }

    {
      assert(typeof behavior === 'function', "scrollBehavior must be a function");
    } // wait until re-render finishes before scrolling

    router.app.$nextTick(function () {
      var position = getScrollPosition();
      var shouldScroll = behavior.call(router, to, from, isPop ? position : null);

      if (!shouldScroll) {
        return;
      }

      if (typeof shouldScroll.then === 'function') {
        shouldScroll.then(function (shouldScroll) {
          scrollToPosition(shouldScroll, position);
        })["catch"](function (err) {
          {
            assert(false, err.toString());
          }
        });
      } else {
        scrollToPosition(shouldScroll, position);
      }
    });
  }

  function saveScrollPosition() {
    var key = getStateKey();

    if (key) {
      positionStore[key] = {
        x: window.pageXOffset,
        y: window.pageYOffset
      };
    }
  }

  function handlePopState(e) {
    saveScrollPosition();

    if (e.state && e.state.key) {
      setStateKey(e.state.key);
    }
  }

  function getScrollPosition() {
    var key = getStateKey();

    if (key) {
      return positionStore[key];
    }
  }

  function getElementPosition(el, offset) {
    var docEl = document.documentElement;
    var docRect = docEl.getBoundingClientRect();
    var elRect = el.getBoundingClientRect();
    return {
      x: elRect.left - docRect.left - offset.x,
      y: elRect.top - docRect.top - offset.y
    };
  }

  function isValidPosition(obj) {
    return isNumber(obj.x) || isNumber(obj.y);
  }

  function normalizePosition(obj) {
    return {
      x: isNumber(obj.x) ? obj.x : window.pageXOffset,
      y: isNumber(obj.y) ? obj.y : window.pageYOffset
    };
  }

  function normalizeOffset(obj) {
    return {
      x: isNumber(obj.x) ? obj.x : 0,
      y: isNumber(obj.y) ? obj.y : 0
    };
  }

  function isNumber(v) {
    return typeof v === 'number';
  }

  var hashStartsWithNumberRE = /^#\d/;

  function scrollToPosition(shouldScroll, position) {
    var isObject = babelHelpers["typeof"](shouldScroll) === 'object';

    if (isObject && typeof shouldScroll.selector === 'string') {
      // getElementById would still fail if the selector contains a more complicated query like #main[data-attr]
      // but at the same time, it doesn't make much sense to select an element with an id and an extra selector
      var el = hashStartsWithNumberRE.test(shouldScroll.selector) // $flow-disable-line
      ? document.getElementById(shouldScroll.selector.slice(1)) // $flow-disable-line
      : document.querySelector(shouldScroll.selector);

      if (el) {
        var offset = shouldScroll.offset && babelHelpers["typeof"](shouldScroll.offset) === 'object' ? shouldScroll.offset : {};
        offset = normalizeOffset(offset);
        position = getElementPosition(el, offset);
      } else if (isValidPosition(shouldScroll)) {
        position = normalizePosition(shouldScroll);
      }
    } else if (isObject && isValidPosition(shouldScroll)) {
      position = normalizePosition(shouldScroll);
    }

    if (position) {
      // $flow-disable-line
      if ('scrollBehavior' in document.documentElement.style) {
        window.scrollTo({
          left: position.x,
          top: position.y,
          // $flow-disable-line
          behavior: shouldScroll.behavior
        });
      } else {
        window.scrollTo(position.x, position.y);
      }
    }
  }
  /*  */


  var supportsPushState = inBrowser && function () {
    var ua = window.navigator.userAgent;

    if ((ua.indexOf('Android 2.') !== -1 || ua.indexOf('Android 4.0') !== -1) && ua.indexOf('Mobile Safari') !== -1 && ua.indexOf('Chrome') === -1 && ua.indexOf('Windows Phone') === -1) {
      return false;
    }

    return window.history && typeof window.history.pushState === 'function';
  }();

  function pushState(url, replace) {
    saveScrollPosition(); // try...catch the pushState call to get around Safari
    // DOM Exception 18 where it limits to 100 pushState calls

    var history = window.history;

    try {
      if (replace) {
        // preserve existing history state as it could be overriden by the user
        var stateCopy = extend({}, history.state);
        stateCopy.key = getStateKey();
        history.replaceState(stateCopy, '', url);
      } else {
        history.pushState({
          key: setStateKey(genStateKey())
        }, '', url);
      }
    } catch (e) {
      window.location[replace ? 'replace' : 'assign'](url);
    }
  }

  function replaceState(url) {
    pushState(url, true);
  } // When changing thing, also edit router.d.ts


  var NavigationFailureType = {
    redirected: 2,
    aborted: 4,
    cancelled: 8,
    duplicated: 16
  };

  function createNavigationRedirectedError(from, to) {
    return createRouterError(from, to, NavigationFailureType.redirected, "Redirected when going from \"".concat(from.fullPath, "\" to \"").concat(stringifyRoute(to), "\" via a navigation guard."));
  }

  function createNavigationDuplicatedError(from, to) {
    var error = createRouterError(from, to, NavigationFailureType.duplicated, "Avoided redundant navigation to current location: \"".concat(from.fullPath, "\".")); // backwards compatible with the first introduction of Errors

    error.name = 'NavigationDuplicated';
    return error;
  }

  function createNavigationCancelledError(from, to) {
    return createRouterError(from, to, NavigationFailureType.cancelled, "Navigation cancelled from \"".concat(from.fullPath, "\" to \"").concat(to.fullPath, "\" with a new navigation."));
  }

  function createNavigationAbortedError(from, to) {
    return createRouterError(from, to, NavigationFailureType.aborted, "Navigation aborted from \"".concat(from.fullPath, "\" to \"").concat(to.fullPath, "\" via a navigation guard."));
  }

  function createRouterError(from, to, type, message) {
    var error = new Error(message);
    error._isRouter = true;
    error.from = from;
    error.to = to;
    error.type = type;
    return error;
  }

  var propertiesToLog = ['params', 'query', 'hash'];

  function stringifyRoute(to) {
    if (typeof to === 'string') return to;
    if ('path' in to) return to.path;
    var location = {};
    propertiesToLog.forEach(function (key) {
      if (key in to) location[key] = to[key];
    });
    return JSON.stringify(location, null, 2);
  }

  function isError(err) {
    return Object.prototype.toString.call(err).indexOf('Error') > -1;
  }

  function isNavigationFailure(err, errorType) {
    return isError(err) && err._isRouter && (errorType == null || err.type === errorType);
  }
  /*  */


  function runQueue(queue, fn, cb) {
    var step = function step(index) {
      if (index >= queue.length) {
        cb();
      } else {
        if (queue[index]) {
          fn(queue[index], function () {
            step(index + 1);
          });
        } else {
          step(index + 1);
        }
      }
    };

    step(0);
  }
  /*  */


  function resolveAsyncComponents(matched) {
    return function (to, from, next) {
      var hasAsync = false;
      var pending = 0;
      var error = null;
      flatMapComponents(matched, function (def, _, match, key) {
        // if it's a function and doesn't have cid attached,
        // assume it's an async component resolve function.
        // we are not using Vue's default async resolving mechanism because
        // we want to halt the navigation until the incoming component has been
        // resolved.
        if (typeof def === 'function' && def.cid === undefined) {
          hasAsync = true;
          pending++;
          var resolve = once(function (resolvedDef) {
            if (isESModule(resolvedDef)) {
              resolvedDef = resolvedDef["default"];
            } // save resolved on async factory in case it's used elsewhere


            def.resolved = typeof resolvedDef === 'function' ? resolvedDef : _Vue.extend(resolvedDef);
            match.components[key] = resolvedDef;
            pending--;

            if (pending <= 0) {
              next();
            }
          });
          var reject = once(function (reason) {
            var msg = "Failed to resolve async component ".concat(key, ": ").concat(reason);
            warn(false, msg);

            if (!error) {
              error = isError(reason) ? reason : new Error(msg);
              next(error);
            }
          });
          var res;

          try {
            res = def(resolve, reject);
          } catch (e) {
            reject(e);
          }

          if (res) {
            if (typeof res.then === 'function') {
              res.then(resolve, reject);
            } else {
              // new syntax in Vue 2.3
              var comp = res.component;

              if (comp && typeof comp.then === 'function') {
                comp.then(resolve, reject);
              }
            }
          }
        }
      });
      if (!hasAsync) next();
    };
  }

  function flatMapComponents(matched, fn) {
    return flatten(matched.map(function (m) {
      return Object.keys(m.components).map(function (key) {
        return fn(m.components[key], m.instances[key], m, key);
      });
    }));
  }

  function flatten(arr) {
    return Array.prototype.concat.apply([], arr);
  }

  var hasSymbol = typeof Symbol === 'function' && babelHelpers["typeof"](Symbol.toStringTag) === 'symbol';

  function isESModule(obj) {
    return obj.__esModule || hasSymbol && obj[Symbol.toStringTag] === 'Module';
  } // in Webpack 2, require.ensure now also returns a Promise
  // so the resolve/reject functions may get called an extra time
  // if the user uses an arrow function shorthand that happens to
  // return that Promise.


  function once(fn) {
    var called = false;
    return function () {
      if (called) return;
      called = true;

      for (var _len = arguments.length, args = new Array(_len), _key2 = 0; _key2 < _len; _key2++) {
        args[_key2] = arguments[_key2];
      }

      return fn.apply(this, args);
    };
  }
  /*  */


  var History = /*#__PURE__*/function () {
    // implemented by sub-classes
    function History(router, base) {
      babelHelpers.classCallCheck(this, History);
      this.router = router;
      this.base = normalizeBase(base); // start with a route object that stands for "nowhere"

      this.current = START;
      this.pending = null;
      this.ready = false;
      this.readyCbs = [];
      this.readyErrorCbs = [];
      this.errorCbs = [];
      this.listeners = [];
    }

    babelHelpers.createClass(History, [{
      key: "listen",
      value: function listen(cb) {
        this.cb = cb;
      }
    }, {
      key: "onReady",
      value: function onReady(cb, errorCb) {
        if (this.ready) {
          cb();
        } else {
          this.readyCbs.push(cb);

          if (errorCb) {
            this.readyErrorCbs.push(errorCb);
          }
        }
      }
    }, {
      key: "onError",
      value: function onError(errorCb) {
        this.errorCbs.push(errorCb);
      }
    }, {
      key: "transitionTo",
      value: function transitionTo(location, onComplete, onAbort) {
        var _this2 = this;

        var route; // catch redirect option https://github.com/vuejs/vue-router/issues/3201

        try {
          route = this.router.match(location, this.current);
        } catch (e) {
          this.errorCbs.forEach(function (cb) {
            cb(e);
          }); // Exception should still be thrown

          throw e;
        }

        var prev = this.current;
        this.confirmTransition(route, function () {
          _this2.updateRoute(route);

          onComplete && onComplete(route);

          _this2.ensureURL();

          _this2.router.afterHooks.forEach(function (hook) {
            hook && hook(route, prev);
          }); // fire ready cbs once


          if (!_this2.ready) {
            _this2.ready = true;

            _this2.readyCbs.forEach(function (cb) {
              cb(route);
            });
          }
        }, function (err) {
          if (onAbort) {
            onAbort(err);
          }

          if (err && !_this2.ready) {
            // Initial redirection should not mark the history as ready yet
            // because it's triggered by the redirection instead
            // https://github.com/vuejs/vue-router/issues/3225
            // https://github.com/vuejs/vue-router/issues/3331
            if (!isNavigationFailure(err, NavigationFailureType.redirected) || prev !== START) {
              _this2.ready = true;

              _this2.readyErrorCbs.forEach(function (cb) {
                cb(err);
              });
            }
          }
        });
      }
    }, {
      key: "confirmTransition",
      value: function confirmTransition(route, onComplete, onAbort) {
        var _this3 = this;

        var current = this.current;
        this.pending = route;

        var abort = function abort(err) {
          // changed after adding errors with
          // https://github.com/vuejs/vue-router/pull/3047 before that change,
          // redirect and aborted navigation would produce an err == null
          if (!isNavigationFailure(err) && isError(err)) {
            if (_this3.errorCbs.length) {
              _this3.errorCbs.forEach(function (cb) {
                cb(err);
              });
            } else {
              {
                warn(false, 'uncaught error during route navigation:');
              }
              console.error(err);
            }
          }

          onAbort && onAbort(err);
        };

        var lastRouteIndex = route.matched.length - 1;
        var lastCurrentIndex = current.matched.length - 1;

        if (isSameRoute(route, current) && // in the case the route map has been dynamically appended to
        lastRouteIndex === lastCurrentIndex && route.matched[lastRouteIndex] === current.matched[lastCurrentIndex]) {
          this.ensureURL();

          if (route.hash) {
            handleScroll(this.router, current, route, false);
          }

          return abort(createNavigationDuplicatedError(current, route));
        }

        var _resolveQueue = resolveQueue(this.current.matched, route.matched),
            updated = _resolveQueue.updated,
            deactivated = _resolveQueue.deactivated,
            activated = _resolveQueue.activated;

        var queue = [].concat( // in-component leave guards
        extractLeaveGuards(deactivated), // global before hooks
        this.router.beforeHooks, // in-component update hooks
        extractUpdateHooks(updated), // in-config enter guards
        activated.map(function (m) {
          return m.beforeEnter;
        }), // async components
        resolveAsyncComponents(activated));

        var iterator = function iterator(hook, next) {
          if (_this3.pending !== route) {
            return abort(createNavigationCancelledError(current, route));
          }

          try {
            hook(route, current, function (to) {
              if (to === false) {
                // next(false) -> abort navigation, ensure current URL
                _this3.ensureURL(true);

                abort(createNavigationAbortedError(current, route));
              } else if (isError(to)) {
                _this3.ensureURL(true);

                abort(to);
              } else if (typeof to === 'string' || babelHelpers["typeof"](to) === 'object' && (typeof to.path === 'string' || typeof to.name === 'string')) {
                // next('/') or next({ path: '/' }) -> redirect
                abort(createNavigationRedirectedError(current, route));

                if (babelHelpers["typeof"](to) === 'object' && to.replace) {
                  _this3.replace(to);
                } else {
                  _this3.push(to);
                }
              } else {
                // confirm transition and pass on the value
                next(to);
              }
            });
          } catch (e) {
            abort(e);
          }
        };

        runQueue(queue, iterator, function () {
          // wait until async components are resolved before
          // extracting in-component enter guards
          var enterGuards = extractEnterGuards(activated);
          var queue = enterGuards.concat(_this3.router.resolveHooks);
          runQueue(queue, iterator, function () {
            if (_this3.pending !== route) {
              return abort(createNavigationCancelledError(current, route));
            }

            _this3.pending = null;
            onComplete(route);

            if (_this3.router.app) {
              _this3.router.app.$nextTick(function () {
                handleRouteEntered(route);
              });
            }
          });
        });
      }
    }, {
      key: "updateRoute",
      value: function updateRoute(route) {
        this.current = route;
        this.cb && this.cb(route);
      }
    }, {
      key: "setupListeners",
      value: function setupListeners() {// Default implementation is empty
      }
    }, {
      key: "teardown",
      value: function teardown() {
        // clean up event listeners
        // https://github.com/vuejs/vue-router/issues/2341
        this.listeners.forEach(function (cleanupListener) {
          cleanupListener();
        });
        this.listeners = []; // reset current history route
        // https://github.com/vuejs/vue-router/issues/3294

        this.current = START;
        this.pending = null;
      }
    }]);
    return History;
  }();

  function normalizeBase(base) {
    if (!base) {
      if (inBrowser) {
        // respect <base> tag
        var baseEl = document.querySelector('base');
        base = baseEl && baseEl.getAttribute('href') || '/'; // strip full URL origin

        base = base.replace(/^https?:\/\/[^\/]+/, '');
      } else {
        base = '/';
      }
    } // make sure there's the starting slash


    if (base.charAt(0) !== '/') {
      base = '/' + base;
    } // remove trailing slash


    return base.replace(/\/$/, '');
  }

  function resolveQueue(current, next) {
    var i;
    var max = Math.max(current.length, next.length);

    for (i = 0; i < max; i++) {
      if (current[i] !== next[i]) {
        break;
      }
    }

    return {
      updated: next.slice(0, i),
      activated: next.slice(i),
      deactivated: current.slice(i)
    };
  }

  function extractGuards(records, name, bind, reverse) {
    var guards = flatMapComponents(records, function (def, instance, match, key) {
      var guard = extractGuard(def, name);

      if (guard) {
        return Array.isArray(guard) ? guard.map(function (guard) {
          return bind(guard, instance, match, key);
        }) : bind(guard, instance, match, key);
      }
    });
    return flatten(reverse ? guards.reverse() : guards);
  }

  function extractGuard(def, key) {
    if (typeof def !== 'function') {
      // extend now so that global mixins are applied.
      def = _Vue.extend(def);
    }

    return def.options[key];
  }

  function extractLeaveGuards(deactivated) {
    return extractGuards(deactivated, 'beforeRouteLeave', bindGuard, true);
  }

  function extractUpdateHooks(updated) {
    return extractGuards(updated, 'beforeRouteUpdate', bindGuard);
  }

  function bindGuard(guard, instance) {
    if (instance) {
      return function boundRouteGuard() {
        return guard.apply(instance, arguments);
      };
    }
  }

  function extractEnterGuards(activated) {
    return extractGuards(activated, 'beforeRouteEnter', function (guard, _, match, key) {
      return bindEnterGuard(guard, match, key);
    });
  }

  function bindEnterGuard(guard, match, key) {
    return function routeEnterGuard(to, from, next) {
      return guard(to, from, function (cb) {
        if (typeof cb === 'function') {
          if (!match.enteredCbs[key]) {
            match.enteredCbs[key] = [];
          }

          match.enteredCbs[key].push(cb);
        }

        next(cb);
      });
    };
  }
  /*  */


  var HTML5History = /*#__PURE__*/function (_History) {
    babelHelpers.inherits(HTML5History, _History);

    function HTML5History(router, base) {
      var _this4;

      babelHelpers.classCallCheck(this, HTML5History);
      _this4 = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(HTML5History).call(this, router, base));
      _this4._startLocation = getLocation(_this4.base);
      return _this4;
    }

    babelHelpers.createClass(HTML5History, [{
      key: "setupListeners",
      value: function setupListeners() {
        var _this5 = this;

        if (this.listeners.length > 0) {
          return;
        }

        var router = this.router;
        var expectScroll = router.options.scrollBehavior;
        var supportsScroll = supportsPushState && expectScroll;

        if (supportsScroll) {
          this.listeners.push(setupScroll());
        }

        var handleRoutingEvent = function handleRoutingEvent() {
          var current = _this5.current; // Avoiding first `popstate` event dispatched in some browsers but first
          // history route not updated since async guard at the same time.

          var location = getLocation(_this5.base);

          if (_this5.current === START && location === _this5._startLocation) {
            return;
          }

          _this5.transitionTo(location, function (route) {
            if (supportsScroll) {
              handleScroll(router, route, current, true);
            }
          });
        };

        window.addEventListener('popstate', handleRoutingEvent);
        this.listeners.push(function () {
          window.removeEventListener('popstate', handleRoutingEvent);
        });
      }
    }, {
      key: "go",
      value: function go(n) {
        window.history.go(n);
      }
    }, {
      key: "push",
      value: function push(location, onComplete, onAbort) {
        var _this6 = this;

        var fromRoute = this.current;
        this.transitionTo(location, function (route) {
          pushState(cleanPath(_this6.base + route.fullPath));
          handleScroll(_this6.router, route, fromRoute, false);
          onComplete && onComplete(route);
        }, onAbort);
      }
    }, {
      key: "replace",
      value: function replace(location, onComplete, onAbort) {
        var _this7 = this;

        var fromRoute = this.current;
        this.transitionTo(location, function (route) {
          replaceState(cleanPath(_this7.base + route.fullPath));
          handleScroll(_this7.router, route, fromRoute, false);
          onComplete && onComplete(route);
        }, onAbort);
      }
    }, {
      key: "ensureURL",
      value: function ensureURL(push) {
        if (getLocation(this.base) !== this.current.fullPath) {
          var current = cleanPath(this.base + this.current.fullPath);
          push ? pushState(current) : replaceState(current);
        }
      }
    }, {
      key: "getCurrentLocation",
      value: function getCurrentLocation() {
        return getLocation(this.base);
      }
    }]);
    return HTML5History;
  }(History);

  function getLocation(base) {
    var path = window.location.pathname;
    var pathLowerCase = path.toLowerCase();
    var baseLowerCase = base.toLowerCase(); // base="/a" shouldn't turn path="/app" into "/a/pp"
    // https://github.com/vuejs/vue-router/issues/3555
    // so we ensure the trailing slash in the base

    if (base && (pathLowerCase === baseLowerCase || pathLowerCase.indexOf(cleanPath(baseLowerCase + '/')) === 0)) {
      path = path.slice(base.length);
    }

    return (path || '/') + window.location.search + window.location.hash;
  }
  /*  */


  var HashHistory = /*#__PURE__*/function (_History2) {
    babelHelpers.inherits(HashHistory, _History2);

    function HashHistory(router, base, fallback) {
      var _this8;

      babelHelpers.classCallCheck(this, HashHistory);
      _this8 = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(HashHistory).call(this, router, base)); // check history fallback deeplinking

      if (fallback && checkFallback(_this8.base)) {
        return babelHelpers.possibleConstructorReturn(_this8);
      }

      ensureSlash();
      return _this8;
    } // this is delayed until the app mounts
    // to avoid the hashchange listener being fired too early


    babelHelpers.createClass(HashHistory, [{
      key: "setupListeners",
      value: function setupListeners() {
        var _this9 = this;

        if (this.listeners.length > 0) {
          return;
        }

        var router = this.router;
        var expectScroll = router.options.scrollBehavior;
        var supportsScroll = supportsPushState && expectScroll;

        if (supportsScroll) {
          this.listeners.push(setupScroll());
        }

        var handleRoutingEvent = function handleRoutingEvent() {
          var current = _this9.current;

          if (!ensureSlash()) {
            return;
          }

          _this9.transitionTo(getHash(), function (route) {
            if (supportsScroll) {
              handleScroll(_this9.router, route, current, true);
            }

            if (!supportsPushState) {
              replaceHash(route.fullPath);
            }
          });
        };

        var eventType = supportsPushState ? 'popstate' : 'hashchange';
        window.addEventListener(eventType, handleRoutingEvent);
        this.listeners.push(function () {
          window.removeEventListener(eventType, handleRoutingEvent);
        });
      }
    }, {
      key: "push",
      value: function push(location, onComplete, onAbort) {
        var _this10 = this;

        var fromRoute = this.current;
        this.transitionTo(location, function (route) {
          pushHash(route.fullPath);
          handleScroll(_this10.router, route, fromRoute, false);
          onComplete && onComplete(route);
        }, onAbort);
      }
    }, {
      key: "replace",
      value: function replace(location, onComplete, onAbort) {
        var _this11 = this;

        var fromRoute = this.current;
        this.transitionTo(location, function (route) {
          replaceHash(route.fullPath);
          handleScroll(_this11.router, route, fromRoute, false);
          onComplete && onComplete(route);
        }, onAbort);
      }
    }, {
      key: "go",
      value: function go(n) {
        window.history.go(n);
      }
    }, {
      key: "ensureURL",
      value: function ensureURL(push) {
        var current = this.current.fullPath;

        if (getHash() !== current) {
          push ? pushHash(current) : replaceHash(current);
        }
      }
    }, {
      key: "getCurrentLocation",
      value: function getCurrentLocation() {
        return getHash();
      }
    }]);
    return HashHistory;
  }(History);

  function checkFallback(base) {
    var location = getLocation(base);

    if (!/^\/#/.test(location)) {
      window.location.replace(cleanPath(base + '/#' + location));
      return true;
    }
  }

  function ensureSlash() {
    var path = getHash();

    if (path.charAt(0) === '/') {
      return true;
    }

    replaceHash('/' + path);
    return false;
  }

  function getHash() {
    // We can't use window.location.hash here because it's not
    // consistent across browsers - Firefox will pre-decode it!
    var href = window.location.href;
    var index = href.indexOf('#'); // empty path

    if (index < 0) return '';
    href = href.slice(index + 1);
    return href;
  }

  function getUrl(path) {
    var href = window.location.href;
    var i = href.indexOf('#');
    var base = i >= 0 ? href.slice(0, i) : href;
    return "".concat(base, "#").concat(path);
  }

  function pushHash(path) {
    if (supportsPushState) {
      pushState(getUrl(path));
    } else {
      window.location.hash = path;
    }
  }

  function replaceHash(path) {
    if (supportsPushState) {
      replaceState(getUrl(path));
    } else {
      window.location.replace(getUrl(path));
    }
  }
  /*  */


  var AbstractHistory = /*#__PURE__*/function (_History3) {
    babelHelpers.inherits(AbstractHistory, _History3);

    function AbstractHistory(router, base) {
      var _this12;

      babelHelpers.classCallCheck(this, AbstractHistory);
      _this12 = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(AbstractHistory).call(this, router, base));
      _this12.stack = [];
      _this12.index = -1;
      return _this12;
    }

    babelHelpers.createClass(AbstractHistory, [{
      key: "push",
      value: function push(location, onComplete, onAbort) {
        var _this13 = this;

        this.transitionTo(location, function (route) {
          _this13.stack = _this13.stack.slice(0, _this13.index + 1).concat(route);
          _this13.index++;
          onComplete && onComplete(route);
        }, onAbort);
      }
    }, {
      key: "replace",
      value: function replace(location, onComplete, onAbort) {
        var _this14 = this;

        this.transitionTo(location, function (route) {
          _this14.stack = _this14.stack.slice(0, _this14.index).concat(route);
          onComplete && onComplete(route);
        }, onAbort);
      }
    }, {
      key: "go",
      value: function go(n) {
        var _this15 = this;

        var targetIndex = this.index + n;

        if (targetIndex < 0 || targetIndex >= this.stack.length) {
          return;
        }

        var route = this.stack[targetIndex];
        this.confirmTransition(route, function () {
          var prev = _this15.current;
          _this15.index = targetIndex;

          _this15.updateRoute(route);

          _this15.router.afterHooks.forEach(function (hook) {
            hook && hook(route, prev);
          });
        }, function (err) {
          if (isNavigationFailure(err, NavigationFailureType.duplicated)) {
            _this15.index = targetIndex;
          }
        });
      }
    }, {
      key: "getCurrentLocation",
      value: function getCurrentLocation() {
        var current = this.stack[this.stack.length - 1];
        return current ? current.fullPath : '/';
      }
    }, {
      key: "ensureURL",
      value: function ensureURL() {// noop
      }
    }]);
    return AbstractHistory;
  }(History);
  /*  */


  var VueRouter = /*#__PURE__*/function () {
    function VueRouter() {
      var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
      babelHelpers.classCallCheck(this, VueRouter);
      {
        warn(this instanceof VueRouter, "Router must be called with the new operator.");
      }
      this.app = null;
      this.apps = [];
      this.options = options;
      this.beforeHooks = [];
      this.resolveHooks = [];
      this.afterHooks = [];
      this.matcher = createMatcher(options.routes || [], this);
      var mode = options.mode || 'hash';
      this.fallback = mode === 'history' && !supportsPushState && options.fallback !== false;

      if (this.fallback) {
        mode = 'hash';
      }

      if (!inBrowser) {
        mode = 'abstract';
      }

      this.mode = mode;

      switch (mode) {
        case 'history':
          this.history = new HTML5History(this, options.base);
          break;

        case 'hash':
          this.history = new HashHistory(this, options.base, this.fallback);
          break;

        case 'abstract':
          this.history = new AbstractHistory(this, options.base);
          break;

        default:
          {
            assert(false, "invalid mode: ".concat(mode));
          }
      }
    }

    babelHelpers.createClass(VueRouter, [{
      key: "match",
      value: function match(raw, current, redirectedFrom) {
        return this.matcher.match(raw, current, redirectedFrom);
      }
    }, {
      key: "init",
      value: function init(app
      /* Vue component instance */
      ) {
        var _this16 = this;

        assert(install.installed, "not installed. Make sure to call `Vue.use(VueRouter)` " + "before creating root instance.");
        this.apps.push(app); // set up app destroyed handler
        // https://github.com/vuejs/vue-router/issues/2639

        app.$once('hook:destroyed', function () {
          // clean out app from this.apps array once destroyed
          var index = _this16.apps.indexOf(app);

          if (index > -1) _this16.apps.splice(index, 1); // ensure we still have a main app or null if no apps
          // we do not release the router so it can be reused

          if (_this16.app === app) _this16.app = _this16.apps[0] || null;
          if (!_this16.app) _this16.history.teardown();
        }); // main app previously initialized
        // return as we don't need to set up new history listener

        if (this.app) {
          return;
        }

        this.app = app;
        var history = this.history;

        if (history instanceof HTML5History || history instanceof HashHistory) {
          var handleInitialScroll = function handleInitialScroll(routeOrError) {
            var from = history.current;
            var expectScroll = _this16.options.scrollBehavior;
            var supportsScroll = supportsPushState && expectScroll;

            if (supportsScroll && 'fullPath' in routeOrError) {
              handleScroll(_this16, routeOrError, from, false);
            }
          };

          var setupListeners = function setupListeners(routeOrError) {
            history.setupListeners();
            handleInitialScroll(routeOrError);
          };

          history.transitionTo(history.getCurrentLocation(), setupListeners, setupListeners);
        }

        history.listen(function (route) {
          _this16.apps.forEach(function (app) {
            app._route = route;
          });
        });
      }
    }, {
      key: "beforeEach",
      value: function beforeEach(fn) {
        return registerHook(this.beforeHooks, fn);
      }
    }, {
      key: "beforeResolve",
      value: function beforeResolve(fn) {
        return registerHook(this.resolveHooks, fn);
      }
    }, {
      key: "afterEach",
      value: function afterEach(fn) {
        return registerHook(this.afterHooks, fn);
      }
    }, {
      key: "onReady",
      value: function onReady(cb, errorCb) {
        this.history.onReady(cb, errorCb);
      }
    }, {
      key: "onError",
      value: function onError(errorCb) {
        this.history.onError(errorCb);
      }
    }, {
      key: "push",
      value: function push(location, onComplete, onAbort) {
        var _this17 = this;

        // $flow-disable-line
        if (!onComplete && !onAbort && typeof Promise !== 'undefined') {
          return new Promise(function (resolve, reject) {
            _this17.history.push(location, resolve, reject);
          });
        } else {
          this.history.push(location, onComplete, onAbort);
        }
      }
    }, {
      key: "replace",
      value: function replace(location, onComplete, onAbort) {
        var _this18 = this;

        // $flow-disable-line
        if (!onComplete && !onAbort && typeof Promise !== 'undefined') {
          return new Promise(function (resolve, reject) {
            _this18.history.replace(location, resolve, reject);
          });
        } else {
          this.history.replace(location, onComplete, onAbort);
        }
      }
    }, {
      key: "go",
      value: function go(n) {
        this.history.go(n);
      }
    }, {
      key: "back",
      value: function back() {
        this.go(-1);
      }
    }, {
      key: "forward",
      value: function forward() {
        this.go(1);
      }
    }, {
      key: "getMatchedComponents",
      value: function getMatchedComponents(to) {
        var route = to ? to.matched ? to : this.resolve(to).route : this.currentRoute;

        if (!route) {
          return [];
        }

        return [].concat.apply([], route.matched.map(function (m) {
          return Object.keys(m.components).map(function (key) {
            return m.components[key];
          });
        }));
      }
    }, {
      key: "resolve",
      value: function resolve(to, current, append) {
        current = current || this.history.current;
        var location = normalizeLocation(to, current, append, this);
        var route = this.match(location, current);
        var fullPath = route.redirectedFrom || route.fullPath;
        var base = this.history.base;
        var href = createHref(base, fullPath, this.mode);
        return {
          location: location,
          route: route,
          href: href,
          // for backwards compat
          normalizedTo: location,
          resolved: route
        };
      }
    }, {
      key: "getRoutes",
      value: function getRoutes() {
        return this.matcher.getRoutes();
      }
    }, {
      key: "addRoute",
      value: function addRoute(parentOrRoute, route) {
        this.matcher.addRoute(parentOrRoute, route);

        if (this.history.current !== START) {
          this.history.transitionTo(this.history.getCurrentLocation());
        }
      }
    }, {
      key: "addRoutes",
      value: function addRoutes(routes) {
        {
          warn(false, 'router.addRoutes() is deprecated and has been removed in Vue Router 4. Use router.addRoute() instead.');
        }
        this.matcher.addRoutes(routes);

        if (this.history.current !== START) {
          this.history.transitionTo(this.history.getCurrentLocation());
        }
      }
    }, {
      key: "currentRoute",
      get: function get() {
        return this.history && this.history.current;
      }
    }]);
    return VueRouter;
  }();

  function registerHook(list, fn) {
    list.push(fn);
    return function () {
      var i = list.indexOf(fn);
      if (i > -1) list.splice(i, 1);
    };
  }

  function createHref(base, fullPath, mode) {
    var path = mode === 'hash' ? '#' + fullPath : fullPath;
    return base ? cleanPath(base + '/' + path) : path;
  } // We cannot remove this as it would be a breaking change


  VueRouter.install = install;
  VueRouter.version = '3.6.5';
  VueRouter.isNavigationFailure = isNavigationFailure;
  VueRouter.NavigationFailureType = NavigationFailureType;
  VueRouter.RouterLink = Link;
  VueRouter.RouterView = View;
  VueRouter.START_LOCATION = START; // origin-end

  VueRouter.create = function (params) {
    return new VueRouter(params);
  };

  ui_vue.VueVendor.use(VueRouter);

  exports.VueRouter = VueRouter;

}((this.BX = this.BX || {}),BX));
//# sourceMappingURL=router.bundle.js.map
