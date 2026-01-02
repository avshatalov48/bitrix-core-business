/* eslint-disable */
this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
(function (exports,main_core,landing_metrika,ui_lottie) {
	'use strict';

	var DiskFile = /*#__PURE__*/function () {
	  /**
	   * Constructor.
	   */
	  function DiskFile() {
	    babelHelpers.classCallCheck(this, DiskFile);
	    document.addEventListener('click', this.onClick.bind(this));
	  }

	  /**
	   * Click callback.
	   *
	   * @return {void}
	   */
	  babelHelpers.createClass(DiskFile, [{
	    key: "onClick",
	    value: function onClick(event) {
	      var target = event.target;
	      var href = target.getAttribute('href') || target.getAttribute('data-pseudo-url') && JSON.parse(target.getAttribute('data-pseudo-url')).href;
	      if (!href) {
	        var parentNode = target.parentNode;
	        if (parentNode.nodeName === 'A') {
	          href = parentNode.getAttribute('href');
	          target = parentNode;
	        } else {
	          var grandParentNode = parentNode.parentNode;
	          if (grandParentNode.nodeName === 'A') {
	            href = grandParentNode.getAttribute('href');
	            target = grandParentNode;
	          }
	        }
	      }
	      if (target.getAttribute('data-viewer-type')) {
	        return;
	      }
	      if (href && href.indexOf('/bitrix/services/main/ajax.php?action=landing.api.diskFile.download') === 0) {
	        BX.ajax.get(href.replace('landing.api.diskFile.download', 'landing.api.diskFile.view'), function (data) {
	          if (typeof data === 'string') {
	            data = JSON.parse(data);
	          }
	          if (!data.data) {
	            return;
	          }
	          Object.keys(data.data).map(function (key) {
	            target.setAttribute(key, data.data[key]);
	          });
	          target.click();
	        });
	        event.preventDefault();
	        event.stopPropagation();
	        return false;
	      }
	    }
	  }]);
	  return DiskFile;
	}();

	function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	function _createForOfIteratorHelper(o, allowArrayLike) { var it = typeof Symbol !== "undefined" && o[Symbol.iterator] || o["@@iterator"]; if (!it) { if (Array.isArray(o) || (it = _unsupportedIterableToArray(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = it.call(o); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it["return"] != null) it["return"](); } finally { if (didErr) throw err; } } }; }
	function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }
	function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i]; return arr2; }

	// Block and analytics constants
	var CATEGORY = 'vibe';
	var SECTION_ACTIVE_PAGE = 'active_page';
	var SECTION_PREVIEW_PAGE = 'preview_page';
	var P1_TEMPLATE_CODE = 'templateCode';
	var P2_WIDGET_ID = 'widgetId';
	var TRIAL_BUTTON_ID = 'trialButton';
	var EVENT_DEMO_ACTIVATED = 'demo_activated';
	var EVENT_CLICK_ON_BUTTON = 'click_on_button';

	// HTML tag and attribute constants
	var TAG_A = 'a';
	var TAG_BUTTON = 'button';
	var DATA_PSEUDO_URL = 'data-pseudo-url';
	var ATTR_HREF = 'href';

	// Other string constants
	var IS_LIGHT_METRIKA = true;
	var BLOCK_WRAPPER_CLASS = 'block-wrapper';
	var BLOCK_PREFIX = 'block-';
	var DASH = '-';
	var DOT = '.';
	var B24URL_TYPE = 'b24url';
	var SLIDER_TYPE = 'slider';
	var OTHER_URL_TYPE = 'otherurl';
	var REGEX_SLIDER = /BX\.Helper\.show\(["'].*?code=(\d+)["']\)/;
	var REGEX_PSEUDO_URL = /^\/|^https?:\/\/|^#/;
	var QUOT_ENTITY = '&quot;';
	var QUOTE = '"';
	/**
	 * @typedef {Object} AnalyticsOptions
	 * @property {boolean} isPublished - Whether the page is published.
	 * @property {string} templateCode - The template code for analytics.
	 * @property {Metrika} metrika - Instance of the analytics sending class.
	 * @property {HTMLElement[]} clickableElements - Array of HTML elements that are tracked for analytics.
	 */

	/**
	 * Analytics class for tracking user interactions on landing page blocks.
	 */
	var Analytics = /*#__PURE__*/function () {
	  /**
	   * Constructor.
	   * @param {AnalyticsOptions} options - Configuration options.
	   */
	  function Analytics(options) {
	    babelHelpers.classCallCheck(this, Analytics);
	    this.isPublished = options.isPublished;
	    this.templateCode = options.templateCode;
	    this.metrika = new landing_metrika.Metrika(IS_LIGHT_METRIKA);
	    this.clickableElements = [];
	    this.initEventListeners();
	  }

	  /**
	   * Initializes click event listeners on all block elements.
	   * @returns {void}
	   */
	  babelHelpers.createClass(Analytics, [{
	    key: "initEventListeners",
	    value: function initEventListeners() {
	      var _this = this;
	      var blocks = babelHelpers.toConsumableArray(document.getElementsByClassName(BLOCK_WRAPPER_CLASS));
	      var _iterator = _createForOfIteratorHelper(blocks),
	        _step;
	      try {
	        var _loop = function _loop() {
	          var _this$clickableElemen;
	          var block = _step.value;
	          var elements = _this.findClickableElements(block);
	          (_this$clickableElemen = _this.clickableElements).push.apply(_this$clickableElemen, babelHelpers.toConsumableArray(elements));
	          var code = _this.getBlockCode(block);
	          elements.forEach(function (element) {
	            main_core.Event.bind(element, 'click', function (event) {
	              return _this.onClick(event, code);
	            });
	          });
	        };
	        for (_iterator.s(); !(_step = _iterator.n()).done;) {
	          _loop();
	        }
	      } catch (err) {
	        _iterator.e(err);
	      } finally {
	        _iterator.f();
	      }
	    }
	    /**
	     * Finds all clickable elements within a block.
	     * @param {HTMLElement} block - The block element to search within.
	     * @returns {HTMLElement[]} Array of clickable elements.
	     */
	  }, {
	    key: "findClickableElements",
	    value: function findClickableElements(block) {
	      var _this2 = this;
	      var elements = babelHelpers.toConsumableArray(block.querySelectorAll("".concat(TAG_A, ", ").concat(TAG_BUTTON, ", [").concat(DATA_PSEUDO_URL, "]")));
	      return elements.filter(function (el) {
	        return _this2.isClickableElement(el);
	      });
	    }
	    /**
	     * Determines if an element is considered clickable for analytics.
	     * @param {HTMLElement} element - The element to check.
	     * @returns {boolean} True if the element is clickable, false otherwise.
	     */
	  }, {
	    key: "isClickableElement",
	    value: function isClickableElement(element) {
	      var tag = element.tagName.toLowerCase();
	      if (tag === TAG_A) {
	        return true;
	      }
	      if (element.closest(TAG_A)) {
	        return false;
	      }
	      return tag === TAG_BUTTON || element.hasAttribute(DATA_PSEUDO_URL);
	    }
	    /**
	     * Extracts a unique code for the block based on its class names.
	     * @param {HTMLElement} block - The block element.
	     * @returns {string} The unique block code.
	     */
	  }, {
	    key: "getBlockCode",
	    value: function getBlockCode(block) {
	      var className = babelHelpers.toConsumableArray(block.classList).find(function (name) {
	        return name.startsWith(BLOCK_PREFIX) && name !== BLOCK_WRAPPER_CLASS;
	      });
	      if (!className) {
	        return '';
	      }
	      return className.replace(BLOCK_PREFIX, '').replaceAll(DASH, DOT);
	    }
	    /**
	     * Handles click events on tracked elements and sends analytics data.
	     * @param {MouseEvent} event - The click event object.
	     * @param {string} code - Unique block code.
	     * @returns {void}
	     */
	  }, {
	    key: "onClick",
	    value: function onClick(event, code) {
	      var target = event.currentTarget;
	      var data = {
	        event: this.getEventName(target),
	        p2: [P2_WIDGET_ID, code],
	        p4: this.getTrackingParameter(target)
	      };
	      this.sendAnalytics(data);
	    }
	    /**
	     * Determines the name of the analytics event based on the clicked element.
	     * @param {HTMLElement} target - The clicked element.
	     * @returns {string} The event name for analytics.
	     */
	  }, {
	    key: "getEventName",
	    value: function getEventName(target) {
	      return target.id === TRIAL_BUTTON_ID ? EVENT_DEMO_ACTIVATED : EVENT_CLICK_ON_BUTTON;
	    }
	    /**
	     * Extracts and classifies tracking parameters from the clicked element.
	     * Detects sliders, internal portal links, or external URLs.
	     * @param {HTMLElement} target - The clicked element.
	     * @returns {Array} Tracking parameters array.
	     */
	  }, {
	    key: "getTrackingParameter",
	    value: function getTrackingParameter(target) {
	      var href = this.extractHrefFromPseudoUrl(target) || this.extractHrefFromElement(target);
	      if (!href) {
	        return undefined;
	      }
	      var sliderMatch = href.match(REGEX_SLIDER);
	      if (sliderMatch) {
	        return [SLIDER_TYPE, sliderMatch[1]];
	      }
	      if (href.startsWith('/') || href.includes(window.location.origin)) {
	        return [B24URL_TYPE, href];
	      }
	      return [OTHER_URL_TYPE, href];
	    }
	    /**
	     * Attempts to parse a pseudo-URL from a `data-pseudo-url` attribute.
	     * Validates format and URL prefix before returning.
	     * @param {HTMLElement} target - The element to extract from.
	     * @returns {string|null} The extracted href or null.
	     */
	  }, {
	    key: "extractHrefFromPseudoUrl",
	    value: function extractHrefFromPseudoUrl(target) {
	      if (!target.hasAttribute(DATA_PSEUDO_URL)) {
	        return null;
	      }
	      var raw = target.getAttribute(DATA_PSEUDO_URL);
	      if (!raw) {
	        return null;
	      }
	      try {
	        var data = JSON.parse(raw.replaceAll(QUOT_ENTITY, QUOTE));
	        if (data && data.href && data.enabled) {
	          if (!REGEX_PSEUDO_URL.test(data.href)) {
	            return '';
	          }
	          return data.href;
	        }
	      } catch (_unused) {
	        return null;
	      }
	      return null;
	    }
	    /**
	     * Retrieves the href attribute from the closest ancestor anchor element.
	     * @param {HTMLElement} target - The element to start searching from.
	     * @returns {string|null} The href value or null.
	     */
	  }, {
	    key: "extractHrefFromElement",
	    value: function extractHrefFromElement(target) {
	      var linkElement = target.closest(TAG_A);
	      return linkElement ? linkElement.getAttribute(ATTR_HREF) || null : null;
	    }
	    /**
	     * Merges common analytics fields with the event-specific data.
	     * Constructs the final object to be sent to Metrika.
	     * @param {Record<string, any>} data - Dynamic analytics fields.
	     * @returns {Record<string, any>} Full analytics data object.
	     */
	  }, {
	    key: "getAnalyticsData",
	    value: function getAnalyticsData(data) {
	      return _objectSpread({
	        category: CATEGORY,
	        c_section: this.isPublished ? SECTION_ACTIVE_PAGE : SECTION_PREVIEW_PAGE,
	        p1: [P1_TEMPLATE_CODE, this.templateCode]
	      }, data);
	    }
	    /**
	     * Sends the finalized analytics data object to the Metrika service.
	     * @param {Record<string, any>} data - Analytics payload to be transmitted.
	     * @returns {void}
	     */
	  }, {
	    key: "sendAnalytics",
	    value: function sendAnalytics(data) {
	      this.metrika.sendData(this.getAnalyticsData(data));
	    }
	  }]);
	  return Analytics;
	}();

	var Pseudolinks = /*#__PURE__*/function () {
	  /**
	   * Constructor.
	   */
	  function Pseudolinks() {
	    var _this = this;
	    babelHelpers.classCallCheck(this, Pseudolinks);
	    var checkPageLoaded = setInterval(function () {
	      if (document.readyState === 'complete') {
	        _this.initPseudoLinks();
	        clearInterval(checkPageLoaded);
	      }
	    }, 500);
	  }

	  /**
	   * Click callback.
	   *
	   * @return {void}
	   */
	  babelHelpers.createClass(Pseudolinks, [{
	    key: "initPseudoLinks",
	    value: function initPseudoLinks() {
	      var _this2 = this;
	      var pseudoLinks = [].slice.call(document.querySelectorAll('[data-pseudo-url*="{"]'));
	      if (pseudoLinks.length > 0) {
	        pseudoLinks.forEach(function (link) {
	          var linkOptionsJson = link.getAttribute('data-pseudo-url');
	          var linkOptions = JSON.parse(linkOptionsJson);
	          if (linkOptions.href && linkOptions.enabled && linkOptions.href.indexOf('/bitrix/services/main/ajax.php?action=landing.api.diskFile.download') !== 0) {
	            if (linkOptions.target === '_self' || linkOptions.target === '_blank') {
	              link.addEventListener('click', function (event) {
	                event.preventDefault();
	                var url = null;
	                try {
	                  url = new URL(linkOptions.href);
	                } catch (error) {
	                  console.error(error);
	                }
	                if (url) {
	                  var isSameHost = url.hostname === window.location.hostname;
	                  var isIframe = url.searchParams.get('IFRAME') === 'Y';
	                  if (isSameHost && !isIframe) {
	                    var isDifferentPath = url.pathname !== window.location.pathname;
	                    if (isDifferentPath) {
	                      BX.addClass(document.body, 'landing-page-transition');
	                      linkOptions.href = url.href;
	                      setTimeout(function () {
	                        _this2.openPseudoLinks(linkOptions, event);
	                      }, 400);
	                      setTimeout(function () {
	                        BX.removeClass(document.body, 'landing-page-transition');
	                      }, 3000);
	                    }
	                  } else {
	                    _this2.openPseudoLinks(linkOptions, event);
	                  }
	                }
	              });
	            }
	          }
	        });
	      }
	    }
	  }, {
	    key: "openPseudoLinks",
	    value: function openPseudoLinks(linkOptions, event) {
	      if (linkOptions.href.indexOf('/bitrix/services/main/ajax.php?action=landing.api.diskFile.download') === 0) {
	        return;
	      }
	      if (linkOptions.query) {
	        linkOptions.href += linkOptions.href.indexOf('?') === -1 ? '?' : '&';
	        linkOptions.href += linkOptions.query;
	      }
	      if (this.isValidURL(linkOptions.href)) {
	        top.open(linkOptions.href, linkOptions.target);
	      }
	    }
	  }, {
	    key: "isValidURL",
	    value: function isValidURL(url) {
	      try {
	        new URL(url);
	        return true;
	      } catch (_unused) {
	        return false;
	      }
	    }
	  }]);
	  return Pseudolinks;
	}();

	var fr = 60;
	var v = "5.9.6";
	var ip = 0;
	var op = 359;
	var w = 264;
	var h = 214;
	var nm = "vibe loader";
	var ddd = 0;
	var markers = [];
	var assets = [{
	  nm: "left",
	  fr: 60,
	  id: "mbrmuh9d29059991",
	  layers: [{
	    ty: 3,
	    ddd: 0,
	    ind: 15,
	    hd: false,
	    nm: "vibe loader - Null",
	    sr: 1,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    ao: 0,
	    ip: 0,
	    op: 360,
	    st: 0,
	    bm: 0
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 16,
	    hd: false,
	    nm: "left - Null",
	    sr: 1,
	    parent: 15,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [18, 102]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    ao: 0,
	    ip: 0,
	    op: 360,
	    st: 0,
	    bm: 0
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 17,
	    hd: false,
	    nm: "vibe loader - Shape Mask",
	    sr: 1,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    ao: 0,
	    ip: 0,
	    op: 360,
	    st: 0,
	    bm: 0,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[0.01, 0], [263.99, 0], [264, 0.01], [264, 213.99], [263.99, 214], [0.01, 214], [0, 213.99], [0, 0.01], [0.01, 0], [0.01, 0]],
	            i: [[0, 0], [0, 0], [0, -0.0055], [0, 0], [0.0055, 0], [0, 0], [0, 0.0055], [0, 0], [-0.0055, 0], [0, 0]],
	            o: [[0, 0], [0.005519999999989977, 0], [0, 0], [0, 0.005519999999989977], [0, 0], [-0.00552, 0], [0, 0], [0, -0.00552], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 100
	        },
	        c: {
	          a: 0,
	          k: [0, 1, 0, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }]
	  }]
	}, {
	  nm: "Frame 1684599022",
	  fr: 60,
	  id: "mbrmuh9e77797556",
	  layers: [{
	    ty: 3,
	    ddd: 0,
	    ind: 18,
	    hd: false,
	    nm: "vibe loader - Null",
	    sr: 1,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    ao: 0,
	    ip: 0,
	    op: 360,
	    st: 0,
	    bm: 0
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 19,
	    hd: false,
	    nm: "left - Null",
	    sr: 1,
	    parent: 18,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [18, 102]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    ao: 0,
	    ip: 0,
	    op: 360,
	    st: 0,
	    bm: 0
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 20,
	    hd: false,
	    nm: "Frame 1684599022 - Null",
	    sr: 1,
	    parent: 19,
	    ks: {
	      a: {
	        a: 0,
	        k: [46.8368, 24.2666]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [53.2858, 34.4668]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 1,
	        k: [{
	          t: 202.074,
	          s: [0.1, 0.1],
	          o: {
	            x: [0.42],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          }
	        }, {
	          t: 250.074,
	          s: [100, 100]
	        }]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    ao: 0,
	    ip: 0,
	    op: 360,
	    st: 0,
	    bm: 0
	  }, {
	    ddd: 0,
	    ind: 21,
	    ty: 0,
	    nm: "left",
	    td: 1,
	    refId: "mbrmuh9d29059991",
	    sr: 1,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    ao: 0,
	    w: 264,
	    h: 214,
	    ip: 0,
	    op: 360,
	    st: 0,
	    hd: false,
	    bm: 0
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 22,
	    hd: false,
	    nm: "Frame 1684599022 - Shape Mask",
	    sr: 1,
	    parent: 20,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    ao: 0,
	    ip: 0,
	    op: 360,
	    st: 0,
	    bm: 0,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[3, 0], [90.6735, 0], [93.6735, 3], [93.6735, 45.5333], [90.6735, 48.5333], [3, 48.5333], [0, 45.5333], [0, 3], [3, 0], [3, 0]],
	            i: [[0, 0], [0, 0], [0, -1.6568], [0, 0], [1.6569, 0], [0, 0], [0, 1.6568], [0, 0], [-1.6568, 0], [0, 0]],
	            o: [[0, 0], [1.6568500000000057, 0], [0, 0], [0, 1.6568499999999986], [0, 0], [-1.65685, 0], [0, 0], [0, -1.65685], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 100
	        },
	        c: {
	          a: 0,
	          k: [0, 1, 0, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    tt: 1
	  }]
	}, {
	  nm: "[GROUP] Shape - Null / Shape / Shape / Ellipse 10089 - Null / Ellipse 10089",
	  fr: 60,
	  id: "mbrr207srazgm0si",
	  layers: [{
	    ty: 3,
	    ddd: 0,
	    ind: 23,
	    hd: false,
	    nm: "vibe loader - Null",
	    sr: 1,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    ao: 0,
	    ip: 0,
	    op: 360,
	    st: 0,
	    bm: 0
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 24,
	    hd: false,
	    nm: "1-1 - Null",
	    sr: 1,
	    parent: 23,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [18, 22]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    ao: 0,
	    ip: 0,
	    op: 360,
	    st: 0,
	    bm: 0
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 25,
	    hd: false,
	    nm: "ill - Null",
	    sr: 1,
	    parent: 24,
	    ks: {
	      a: {
	        a: 0,
	        k: [22, 22]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [36, 34]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 1,
	        k: [{
	          t: 49.914,
	          s: [0.1, 0.1],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          }
	        }, {
	          t: 97.91399999999999,
	          s: [100, 100]
	        }]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    ao: 0,
	    ip: 0,
	    op: 360,
	    st: 0,
	    bm: 0
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 26,
	    hd: false,
	    nm: "Shape - Null",
	    sr: 1,
	    parent: 25,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [8.9999, 11]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    ao: 0,
	    ip: 0,
	    op: 360,
	    st: 0,
	    bm: 0
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 27,
	    hd: false,
	    nm: "Shape",
	    sr: 1,
	    parent: 26,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    ao: 0,
	    ip: 0,
	    op: 360,
	    st: 0,
	    bm: 0,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 10,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[17.8153, 9.2502], [17.8443, 9.0095], [19.0227, 8.2875], [23.5575, 9.4453], [24.2937, 10.408], [24.2937, 19.5568], [25.5317, 19.5568], [25.9132, 19.9383], [25.9132, 22.4832], [25.5317, 22.8646], [0.3815, 22.8646], [0, 22.4832], [0, 19.9383], [0.3815, 19.5568], [1.6196, 19.5568], [1.6196, 3.5356], [2.9794, 1.9031], [14.3163, 0.0215], [14.5762, 0.0001], [16.1958, 1.654], [16.1958, 19.5568], [17.8153, 19.5568], [17.8153, 9.2502]],
	            i: [[0, 0], [-0.0191, 0.0787], [-0.5206, -0.1329], [0, 0], [0, -0.4554], [0, 0], [0, 0], [0, -0.2107], [0, 0], [0.2107, 0], [0, 0], [0, 0.2107], [0, 0], [-0.2107, 0], [0, 0], [0, 0], [-0.7839, 0.1301], [0, 0], [-0.0871, 0], [0, -0.9134], [0, 0], [0, 0], [0, 0]],
	            o: [[0, -0.08112999999999992], [0.13019999999999854, -0.5316799999999997], [0, 0], [0.43270000000000053, 0.11044000000000054], [0, 0], [0, 0], [0.21069999999999922, 0], [0, 0], [0, 0.21069999999999922], [0, 0], [-0.21069, 0], [0, 0], [0, -0.21069999999999922], [0, 0], [0, 0], [0, -0.8110200000000001], [0, 0], [0.08610000000000007, -0.01426], [0.8943999999999992, 0], [0, 0], [0, 0], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[8.601, 19.3945], [8.601, 14.3129], [5.6973, 14.3129], [5.6973, 19.3945], [8.601, 19.3945]],
	            i: [[0, 0], [0, 0], [0, 0], [0, 0], [0, 0]],
	            o: [[0, 0], [0, 0], [0, 0], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[13.0282, 17.2167], [13.0282, 14.3129], [10.1245, 14.3129], [10.1245, 17.2167], [13.0282, 17.2167]],
	            i: [[0, 0], [0, 0], [0, 0], [0, 0], [0, 0]],
	            o: [[0, 0], [0, 0], [0, 0], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[22.3934, 14.1682], [19.4896, 14.1682], [19.4896, 17.0719], [22.3934, 17.0719], [22.3934, 14.1682]],
	            i: [[0, 0], [0, 0], [0, 0], [0, 0], [0, 0]],
	            o: [[0, 0], [0, 0], [0, 0], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[13.0282, 5.4573], [10.1245, 5.4573], [10.1245, 8.361], [13.0282, 8.361], [13.0282, 5.4573]],
	            i: [[0, 0], [0, 0], [0, 0], [0, 0], [0, 0]],
	            o: [[0, 0], [0, 0], [0, 0], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[8.601, 5.4573], [5.6973, 5.4573], [5.6973, 8.361], [8.601, 8.361], [8.601, 5.4573]],
	            i: [[0, 0], [0, 0], [0, 0], [0, 0], [0, 0]],
	            o: [[0, 0], [0, 0], [0, 0], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[13.0282, 9.8851], [10.1245, 9.8851], [10.1245, 12.7889], [13.0282, 12.7889], [13.0282, 9.8851]],
	            i: [[0, 0], [0, 0], [0, 0], [0, 0], [0, 0]],
	            o: [[0, 0], [0, 0], [0, 0], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[8.601, 9.8851], [5.6973, 9.8851], [5.6973, 12.7889], [8.601, 12.7889], [8.601, 9.8851]],
	            i: [[0, 0], [0, 0], [0, 0], [0, 0], [0, 0]],
	            o: [[0, 0], [0, 0], [0, 0], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 100
	        },
	        c: {
	          a: 0,
	          k: [1, 1, 1, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 2
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }]
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 28,
	    hd: false,
	    nm: "Shape",
	    sr: 1,
	    parent: 26,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    ao: 0,
	    ip: 0,
	    op: 360,
	    st: 0,
	    bm: 0,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 10,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[17.8153, 9.2502], [17.8443, 9.0095], [19.0227, 8.2875], [23.5575, 9.4453], [24.2937, 10.408], [24.2937, 19.5568], [25.5317, 19.5568], [25.9132, 19.9383], [25.9132, 22.4832], [25.5317, 22.8646], [0.3815, 22.8646], [0, 22.4832], [0, 19.9383], [0.3815, 19.5568], [1.6196, 19.5568], [1.6196, 3.5356], [2.9794, 1.9031], [14.3163, 0.0215], [14.5762, 0.0001], [16.1958, 1.654], [16.1958, 19.5568], [17.8153, 19.5568], [17.8153, 9.2502]],
	            i: [[0, 0], [-0.0191, 0.0787], [-0.5206, -0.1329], [0, 0], [0, -0.4554], [0, 0], [0, 0], [0, -0.2107], [0, 0], [0.2107, 0], [0, 0], [0, 0.2107], [0, 0], [-0.2107, 0], [0, 0], [0, 0], [-0.7839, 0.1301], [0, 0], [-0.0871, 0], [0, -0.9134], [0, 0], [0, 0], [0, 0]],
	            o: [[0, -0.08112999999999992], [0.13019999999999854, -0.5316799999999997], [0, 0], [0.43270000000000053, 0.11044000000000054], [0, 0], [0, 0], [0.21069999999999922, 0], [0, 0], [0, 0.21069999999999922], [0, 0], [-0.21069, 0], [0, 0], [0, -0.21069999999999922], [0, 0], [0, 0], [0, -0.8110200000000001], [0, 0], [0.08610000000000007, -0.01426], [0.8943999999999992, 0], [0, 0], [0, 0], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[8.601, 19.3945], [8.601, 14.3129], [5.6973, 14.3129], [5.6973, 19.3945], [8.601, 19.3945]],
	            i: [[0, 0], [0, 0], [0, 0], [0, 0], [0, 0]],
	            o: [[0, 0], [0, 0], [0, 0], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[13.0282, 17.2167], [13.0282, 14.3129], [10.1245, 14.3129], [10.1245, 17.2167], [13.0282, 17.2167]],
	            i: [[0, 0], [0, 0], [0, 0], [0, 0], [0, 0]],
	            o: [[0, 0], [0, 0], [0, 0], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[22.3934, 14.1682], [19.4896, 14.1682], [19.4896, 17.0719], [22.3934, 17.0719], [22.3934, 14.1682]],
	            i: [[0, 0], [0, 0], [0, 0], [0, 0], [0, 0]],
	            o: [[0, 0], [0, 0], [0, 0], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[13.0282, 5.4573], [10.1245, 5.4573], [10.1245, 8.361], [13.0282, 8.361], [13.0282, 5.4573]],
	            i: [[0, 0], [0, 0], [0, 0], [0, 0], [0, 0]],
	            o: [[0, 0], [0, 0], [0, 0], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[8.601, 5.4573], [5.6973, 5.4573], [5.6973, 8.361], [8.601, 8.361], [8.601, 5.4573]],
	            i: [[0, 0], [0, 0], [0, 0], [0, 0], [0, 0]],
	            o: [[0, 0], [0, 0], [0, 0], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[13.0282, 9.8851], [10.1245, 9.8851], [10.1245, 12.7889], [13.0282, 12.7889], [13.0282, 9.8851]],
	            i: [[0, 0], [0, 0], [0, 0], [0, 0], [0, 0]],
	            o: [[0, 0], [0, 0], [0, 0], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[8.601, 9.8851], [5.6973, 9.8851], [5.6973, 12.7889], [8.601, 12.7889], [8.601, 9.8851]],
	            i: [[0, 0], [0, 0], [0, 0], [0, 0], [0, 0]],
	            o: [[0, 0], [0, 0], [0, 0], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 100
	        },
	        c: {
	          a: 0,
	          k: [1, 1, 1, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 2
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }, {
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "rc",
	        nm: "Rectangle",
	        hd: false,
	        p: {
	          a: 0,
	          k: [13.5566, 12.03225]
	        },
	        s: {
	          a: 0,
	          k: [54.2264, 48.129]
	        },
	        r: {
	          a: 0,
	          k: 0
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 0
	        },
	        c: {
	          a: 0,
	          k: [0, 1, 0, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: [{
	      nm: "Drop Shadow",
	      ty: 25,
	      en: 1,
	      ef: [{
	        ty: 2,
	        nm: "Shadow Color",
	        v: {
	          a: 0,
	          k: [0, 0, 0, 1]
	        }
	      }, {
	        ty: 0,
	        nm: "Opacity",
	        v: {
	          a: 0,
	          k: 5
	        }
	      }, {
	        ty: 1,
	        nm: "Direction",
	        v: {
	          a: 0,
	          k: 90
	        }
	      }, {
	        ty: 0,
	        nm: "Distance",
	        v: {
	          a: 0,
	          k: 1
	        }
	      }, {
	        ty: 0,
	        nm: "Softness",
	        v: {
	          a: 0,
	          k: 2
	        }
	      }, {
	        ty: 4,
	        nm: "Shadow Only",
	        v: {
	          a: 0,
	          k: 0
	        }
	      }]
	    }]
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 29,
	    hd: false,
	    nm: "Ellipse 10089 - Null",
	    sr: 1,
	    parent: 25,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    ao: 0,
	    ip: 0,
	    op: 360,
	    st: 0,
	    bm: 0
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 30,
	    hd: false,
	    nm: "Ellipse 10089",
	    sr: 1,
	    parent: 29,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    ao: 0,
	    ip: 0,
	    op: 360,
	    st: 0,
	    bm: 0,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[44, 22], [22, 44], [0, 22], [22, 0], [44, 22], [44, 22]],
	            i: [[0, 0], [12.1506, 0], [0, 12.1506], [-12.1506, 0], [0, -12.1506], [0, 0]],
	            o: [[0, 12.150599999999997], [-12.1506, 0], [0, -12.1506], [12.150599999999997, 0], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 100
	        },
	        c: {
	          a: 0,
	          k: [0.996078431372549, 0.8862745098039215, 0.8627450980392157, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }]
	  }]
	}, {
	  nm: "[FRAME] vibe loader - Null / 1-1 - Null / ill / 3 - Null / 3 / 2 - Null / 2 / 1 - Null / 1 / bg - Null / bg",
	  fr: 60,
	  id: "mbrr207r8s9ajm5g",
	  layers: [{
	    ty: 3,
	    ddd: 0,
	    ind: 31,
	    hd: false,
	    nm: "vibe loader - Null",
	    sr: 1,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    ao: 0,
	    ip: 0,
	    op: 360,
	    st: 0,
	    bm: 0
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 32,
	    hd: false,
	    nm: "1-1 - Null",
	    sr: 1,
	    parent: 31,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [18, 22]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    ao: 0,
	    ip: 0,
	    op: 360,
	    st: 0,
	    bm: 0
	  }, {
	    ddd: 0,
	    ind: 33,
	    ty: 0,
	    nm: "ill",
	    refId: "mbrr207srazgm0si",
	    sr: 1,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    ao: 0,
	    w: 264,
	    h: 214,
	    ip: 0,
	    op: 360,
	    st: 0,
	    hd: false,
	    bm: 0
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 34,
	    hd: false,
	    nm: "3 - Null",
	    sr: 1,
	    parent: 32,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 4]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [70, 50]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    ao: 0,
	    ip: 0,
	    op: 360,
	    st: 0,
	    bm: 0
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 35,
	    hd: false,
	    nm: "3",
	    sr: 1,
	    parent: 34,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    ao: 0,
	    ip: 0,
	    op: 360,
	    st: 0,
	    bm: 0,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 1,
	          k: [{
	            t: 106.63199999999999,
	            s: [{
	              c: true,
	              v: [[0.01, 0], [-0.01, 0], [0, 0.01], [0, 7.99], [-0.01, 8], [0.01, 8], [0, 7.99], [0, 0.01], [0.01, 0], [0.01, 0]],
	              i: [[0, 0], [0, 0], [0, -0.0055], [0, 0], [0.0055, 0], [0, 0], [0, 0.0055], [0, 0], [-0.0055, 0], [0, 0]],
	              o: [[0, 0], [0.00552, 0], [0, 0], [0, 0.005519999999999747], [0, 0], [-0.00552, 0], [0, 0], [0, -0.00552], [0, 0], [0, 0]]
	            }],
	            o: {
	              x: [0.42],
	              y: [0]
	            },
	            i: {
	              x: [0.58],
	              y: [1]
	            }
	          }, {
	            t: 125.47200000000001,
	            s: [{
	              c: true,
	              v: [[4, 0], [56, 0], [60, 4], [60, 4], [56, 8], [4, 8], [0, 4], [0, 4], [4, 0], [4, 0]],
	              i: [[0, 0], [0, 0], [0, -2.2091], [0, 0], [2.2091, 0], [0, 0], [0, 2.2091], [0, 0], [-2.2091, 0], [0, 0]],
	              o: [[0, 0], [2.209139999999998, 0], [0, 0], [0, 2.2091399999999997], [0, 0], [-2.20914, 0], [0, 0], [0, -2.20914], [0, 0], [0, 0]]
	            }]
	          }]
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 9
	        },
	        c: {
	          a: 0,
	          k: [0.3764705882352941, 0.5058823529411764, 0.9019607843137255, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }]
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 36,
	    hd: false,
	    nm: "2 - Null",
	    sr: 1,
	    parent: 32,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 4]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [70, 34]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    ao: 0,
	    ip: 0,
	    op: 360,
	    st: 0,
	    bm: 0
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 37,
	    hd: false,
	    nm: "2",
	    sr: 1,
	    parent: 36,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    ao: 0,
	    ip: 0,
	    op: 360,
	    st: 0,
	    bm: 0,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 1,
	          k: [{
	            t: 90.132,
	            s: [{
	              c: true,
	              v: [[0.01, 0], [-0.01, 0], [0, 0.01], [0, 7.99], [-0.01, 8], [0.01, 8], [0, 7.99], [0, 0.01], [0.01, 0], [0.01, 0]],
	              i: [[0, 0], [0, 0], [0, -0.0055], [0, 0], [0.0055, 0], [0, 0], [0, 0.0055], [0, 0], [-0.0055, 0], [0, 0]],
	              o: [[0, 0], [0.00552, 0], [0, 0], [0, 0.005519999999999747], [0, 0], [-0.00552, 0], [0, 0], [0, -0.00552], [0, 0], [0, 0]]
	            }],
	            o: {
	              x: [0.42],
	              y: [0]
	            },
	            i: {
	              x: [0.58],
	              y: [1]
	            }
	          }, {
	            t: 104.32199999999999,
	            s: [{
	              c: true,
	              v: [[4, 0], [90, 0], [94, 4], [94, 4], [90, 8], [4, 8], [0, 4], [0, 4], [4, 0], [4, 0]],
	              i: [[0, 0], [0, 0], [0, -2.2091], [0, 0], [2.2091, 0], [0, 0], [0, 2.2091], [0, 0], [-2.2091, 0], [0, 0]],
	              o: [[0, 0], [2.209140000000005, 0], [0, 0], [0, 2.2091399999999997], [0, 0], [-2.20914, 0], [0, 0], [0, -2.20914], [0, 0], [0, 0]]
	            }]
	          }]
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 9
	        },
	        c: {
	          a: 0,
	          k: [0.3764705882352941, 0.5058823529411764, 0.9019607843137255, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }]
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 38,
	    hd: false,
	    nm: "1 - Null",
	    sr: 1,
	    parent: 32,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 4]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [70, 18]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    ao: 0,
	    ip: 0,
	    op: 360,
	    st: 0,
	    bm: 0
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 39,
	    hd: false,
	    nm: "1",
	    sr: 1,
	    parent: 38,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    ao: 0,
	    ip: 0,
	    op: 360,
	    st: 0,
	    bm: 0,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 1,
	          k: [{
	            t: 71.658,
	            s: [{
	              c: true,
	              v: [[0.01, 0], [-0.01, 0], [0, 0.01], [0, 7.99], [-0.01, 8], [0.01, 8], [0, 7.99], [0, 0.01], [0.01, 0], [0.01, 0]],
	              i: [[0, 0], [0, 0], [0, -0.0055], [0, 0], [0.0055, 0], [0, 0], [0, 0.0055], [0, 0], [-0.0055, 0], [0, 0]],
	              o: [[0, 0], [0.00552, 0], [0, 0], [0, 0.005519999999999747], [0, 0], [-0.00552, 0], [0, 0], [0, -0.00552], [0, 0], [0, 0]]
	            }],
	            o: {
	              x: [0.42],
	              y: [0]
	            },
	            i: {
	              x: [0.58],
	              y: [1]
	            }
	          }, {
	            t: 90.474,
	            s: [{
	              c: true,
	              v: [[4, 0], [90, 0], [94, 4], [94, 4], [90, 8], [4, 8], [0, 4], [0, 4], [4, 0], [4, 0]],
	              i: [[0, 0], [0, 0], [0, -2.2091], [0, 0], [2.2091, 0], [0, 0], [0, 2.2091], [0, 0], [-2.2091, 0], [0, 0]],
	              o: [[0, 0], [2.209140000000005, 0], [0, 0], [0, 2.2091399999999997], [0, 0], [-2.20914, 0], [0, 0], [0, -2.20914], [0, 0], [0, 0]]
	            }]
	          }]
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 9
	        },
	        c: {
	          a: 0,
	          k: [0.3764705882352941, 0.5058823529411764, 0.9019607843137255, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }]
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 40,
	    hd: false,
	    nm: "bg - Null",
	    sr: 1,
	    parent: 32,
	    ks: {
	      a: {
	        a: 0,
	        k: [114, 34.5]
	      },
	      o: {
	        a: 1,
	        k: [{
	          t: 1.716,
	          s: [0],
	          o: {
	            x: [0.5],
	            y: [0]
	          },
	          i: {
	            x: [0.5],
	            y: [1]
	          }
	        }, {
	          t: 49.716,
	          s: [100]
	        }]
	      },
	      p: {
	        a: 1,
	        k: [{
	          t: 1.716,
	          s: [114, 14.5],
	          o: {
	            x: [0.5],
	            y: [0]
	          },
	          i: {
	            x: [0.5],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 49.716,
	          s: [114, 34.5]
	        }]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    ao: 0,
	    ip: 0,
	    op: 360,
	    st: 0,
	    bm: 0
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 41,
	    hd: false,
	    nm: "bg",
	    sr: 1,
	    parent: 40,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 1,
	        k: [{
	          t: 1.716,
	          s: [0],
	          o: {
	            x: [0.5],
	            y: [0]
	          },
	          i: {
	            x: [0.5],
	            y: [1]
	          }
	        }, {
	          t: 49.716,
	          s: [100]
	        }]
	      }
	    },
	    ao: 0,
	    ip: 0,
	    op: 360,
	    st: 0,
	    bm: 0,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[12, 0], [216, 0], [228, 12], [228, 57], [216, 69], [12, 69], [0, 57], [0, 12], [12, 0], [12, 0]],
	            i: [[0, 0], [0, 0], [0, -6.6274], [0, 0], [6.6274, 0], [0, 0], [0, 6.6274], [0, 0], [-6.6274, 0], [0, 0]],
	            o: [[0, 0], [6.627420000000001, 0], [0, 0], [0, 6.627420000000001], [0, 0], [-6.62742, 0], [0, 0], [0, -6.62742], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 100
	        },
	        c: {
	          a: 0,
	          k: [1, 1, 1, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }]
	  }]
	}, {
	  nm: "[FRAME] vibe loader - Null / right - Null / Ellipse 10088 - Null / Ellipse 10088 / Ellipse 63 - Null / Ellipse 63 / Ellipse 62 - Null / Ellipse 62 / 2 - Null / 2 / 1 - Null / 1 / bg2 - Null / bg2",
	  fr: 60,
	  id: "mbrr20816ro91xdd",
	  layers: [{
	    ty: 3,
	    ddd: 0,
	    ind: 42,
	    hd: false,
	    nm: "vibe loader - Null",
	    sr: 1,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    ao: 0,
	    ip: 0,
	    op: 360,
	    st: 0,
	    bm: 0
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 43,
	    hd: false,
	    nm: "right - Null",
	    sr: 1,
	    parent: 42,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [139, 102]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    ao: 0,
	    ip: 0,
	    op: 360,
	    st: 0,
	    bm: 0
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 44,
	    hd: false,
	    nm: "Ellipse 10088 - Null",
	    sr: 1,
	    parent: 43,
	    ks: {
	      a: {
	        a: 0,
	        k: [3.4468, 3.4441]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [30.6472, 34.4668]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 1,
	        k: [{
	          t: 218.91,
	          s: [0.1, 0.1],
	          o: {
	            x: [0.42],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          }
	        }, {
	          t: 256.89000000000004,
	          s: [100, 100]
	        }]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    ao: 0,
	    ip: 0,
	    op: 360,
	    st: 0,
	    bm: 0
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 45,
	    hd: false,
	    nm: "Ellipse 10088",
	    sr: 1,
	    parent: 44,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    ao: 0,
	    ip: 0,
	    op: 360,
	    st: 0,
	    bm: 0,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[6.8936, 3.4441], [3.4468, 6.8882], [0, 3.4441], [3.4468, 0], [6.8936, 3.4441], [6.8936, 3.4441]],
	            i: [[0, 0], [1.9036, 0], [0, 1.9021], [-1.9036, 0], [0, -1.9021], [0, 0]],
	            o: [[0, 1.90211], [-1.9036, 0], [0, -1.90211], [1.9036000000000004, 0], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 78
	        },
	        c: {
	          a: 0,
	          k: [0.9803921568627451, 0.6549019607843137, 0.17254901960784313, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }]
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 46,
	    hd: false,
	    nm: "Ellipse 63 - Null",
	    sr: 1,
	    parent: 43,
	    ks: {
	      a: {
	        a: 0,
	        k: [7.8981, 11.5605]
	      },
	      o: {
	        a: 0,
	        k: 50
	      },
	      p: {
	        a: 0,
	        k: [26.8256, 34.6169]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 1,
	        k: [{
	          t: 204.978,
	          s: [0.1, 0.1],
	          o: {
	            x: [0.42],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          }
	        }, {
	          t: 242.93399999999997,
	          s: [100, 100]
	        }]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    ao: 0,
	    ip: 0,
	    op: 360,
	    st: 0,
	    bm: 0
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 47,
	    hd: false,
	    nm: "Ellipse 63",
	    sr: 1,
	    parent: 46,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 50
	      }
	    },
	    ao: 0,
	    ip: 0,
	    op: 360,
	    st: 0,
	    bm: 0,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[11.7199, 2.6683], [8.7806, 0.0739], [6.6365, 0.8586], [2.5592, 4.106], [0.2951, 8.7992], [0.2923, 14.0092], [2.5513, 18.7048], [6.625, 21.9567], [11.7072, 23.1212], [13.98, 22.9014], [15.5062, 19.2915], [15.3423, 18.9503], [11.7137, 17.1839], [11.7137, 17.1839], [9.2082, 16.6098], [7.1998, 15.0066], [6.0861, 12.6917], [6.0875, 10.1232], [7.2037, 7.8095], [9.2138, 6.2085], [9.2138, 6.2085], [11.7199, 3.047], [11.7199, 2.6683]],
	            i: [[0, 0], [1.5884, -0.4112], [0.6896, -0.3317], [1.0978, -1.3747], [0.3925, -1.7144], [-0.3906, -1.7148], [-1.0963, -1.3759], [-1.5851, -0.7645], [-1.7601, -0.0019], [-0.7463, 0.1466], [0.7101, 1.4781], [0, 0], [1.4316, 0.0016], [0, 0], [0.7814, 0.3769], [0.5405, 0.6783], [0.1926, 0.8454], [-0.1935, 0.8452], [-0.5412, 0.6777], [-0.7818, 0.3761], [0, 0], [0, 1.4305], [0, 0]],
	            o: [[0, -1.63955], [-0.7363, 0.19061000000000003], [-1.58589, 0.76282], [-1.09782, 1.3746600000000004], [-0.39246, 1.7144200000000005], [0.3905799999999999, 1.7148400000000006], [1.09632, 1.3758599999999994], [1.5850600000000004, 0.7645499999999998], [0.7653199999999991, 0.0008300000000005525], [1.6100399999999997, -0.31616], [0, 0], [-0.6195599999999999, -1.2896199999999993], [0, 0], [-0.8677200000000003, -0.0009499999999995623], [-0.7814300000000003, -0.37691999999999837], [-0.5404799999999996, -0.6783000000000001], [-0.1925600000000003, -0.8454200000000007], [0.1934800000000001, -0.8452099999999998], [0.54122, -0.6777100000000003], [0, 0], [1.2899600000000007, -0.6204799999999997], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 78
	        },
	        c: {
	          a: 0,
	          k: [0, 0.4588235294117647, 1, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }]
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 48,
	    hd: false,
	    nm: "Ellipse 62 - Null",
	    sr: 1,
	    parent: 43,
	    ks: {
	      a: {
	        a: 0,
	        k: [20.7403, 20.8553]
	      },
	      o: {
	        a: 0,
	        k: 50
	      },
	      p: {
	        a: 0,
	        k: [30.6473, 34.5655]
	      },
	      r: {
	        a: 0,
	        k: -90
	      },
	      s: {
	        a: 1,
	        k: [{
	          t: 192.35999999999999,
	          s: [0.1, 0.1],
	          o: {
	            x: [0.42],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          }
	        }, {
	          t: 230.316,
	          s: [100, 100]
	        }]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    ao: 0,
	    ip: 0,
	    op: 360,
	    st: 0,
	    bm: 0
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 49,
	    hd: false,
	    nm: "Ellipse 62",
	    sr: 1,
	    parent: 48,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 50
	      }
	    },
	    ao: 0,
	    ip: 0,
	    op: 360,
	    st: 0,
	    bm: 0,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[38.6041, 20.8553], [41.4516, 17.7902], [37.2824, 8.0439], [25.9502, 0.6371], [12.4618, 1.7593], [2.5073, 10.9372], [0.286, 24.2991], [6.735, 36.2082], [19.1337, 41.6406], [29.5653, 39.7939], [30.5683, 35.7309], [30.5683, 35.7309], [26.1563, 34.5613], [19.6367, 35.5089], [10.8956, 31.679], [6.3491, 23.2831], [7.9151, 13.863], [14.933, 7.3926], [24.4423, 6.6014], [32.4315, 11.8232], [35.2099, 17.8014], [38.604, 20.8552], [38.6041, 20.8553]],
	            i: [[0, 0], [0.2495, 1.6805], [2.2159, 2.8486], [4.4957, 1.1383], [4.2461, -1.8656], [2.2053, -4.0825], [-0.7658, -4.5772], [-3.4138, -3.141], [-4.6218, -0.3797], [-3.2446, 1.4974], [0.9297, 1.4215], [0, 0], [1.5825, -0.6149], [2.2301, 0.1832], [2.4067, 2.2144], [0.5398, 3.227], [-1.5548, 2.8782], [-2.9935, 1.3153], [-3.1694, -0.8025], [-2.0083, -2.5817], [-0.4593, -2.1647], [-1.6976, 0], [0, 0]],
	            o: [[1.697580000000002, 0], [-0.525109999999998, -3.5368899999999996], [-2.8486899999999977, -3.66204], [-4.495650000000001, -1.13831], [-4.24611, 1.86563], [-2.20534, 4.08254], [0.7657499999999999, 4.57723], [3.4138100000000007, 3.1409999999999982], [3.5951699999999995, 0.2953899999999976], [1.54157, -0.71143], [0, 0], [-0.9297199999999997, -1.4214600000000033], [-2.06128, 0.8009399999999971], [-3.258330000000001, -0.267710000000001], [-2.406740000000001, -2.214410000000001], [-0.53986, -3.2269499999999987], [1.5547700000000004, -2.87819], [2.9935100000000006, -1.31527], [3.169430000000002, 0.8025099999999998], [1.3745499999999993, 1.767009999999999], [0.35260999999999854, 1.6618600000000008], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 78
	        },
	        c: {
	          a: 0,
	          k: [0.10588235294117647, 0.807843137254902, 0.4823529411764706, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }]
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 50,
	    hd: false,
	    nm: "2 - Null",
	    sr: 1,
	    parent: 43,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 3.5]
	      },
	      o: {
	        a: 0,
	        k: 70
	      },
	      p: {
	        a: 0,
	        k: [61, 40.5]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    ao: 0,
	    ip: 0,
	    op: 360,
	    st: 0,
	    bm: 0
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 51,
	    hd: false,
	    nm: "2",
	    sr: 1,
	    parent: 50,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 70
	      }
	    },
	    ao: 0,
	    ip: 0,
	    op: 360,
	    st: 0,
	    bm: 0,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 1,
	          k: [{
	            t: 270.23999999999995,
	            s: [{
	              c: true,
	              v: [[0.01, 0], [-0.01, 0], [0, 0.01], [0, 6.99], [-0.01, 7], [0.01, 7], [0, 6.99], [0, 0.01], [0.01, 0], [0.01, 0]],
	              i: [[0, 0], [0, 0], [0, -0.0055], [0, 0], [0.0055, 0], [0, 0], [0, 0.0055], [0, 0], [-0.0055, 0], [0, 0]],
	              o: [[0, 0], [0.00552, 0], [0, 0], [0, 0.005519999999999747], [0, 0], [-0.00552, 0], [0, 0], [0, -0.00552], [0, 0], [0, 0]]
	            }],
	            o: {
	              x: [0.5],
	              y: [0.35]
	            },
	            i: {
	              x: [0.15],
	              y: [1]
	            }
	          }, {
	            t: 280.47,
	            s: [{
	              c: true,
	              v: [[3.5, 0], [26.5, 0], [30, 3.5], [30, 3.5], [26.5, 7], [3.5, 7], [0, 3.5], [0, 3.5], [3.5, 0], [3.5, 0]],
	              i: [[0, 0], [0, 0], [0, -1.933], [0, 0], [1.933, 0], [0, 0], [0, 1.933], [0, 0], [-1.933, 0], [0, 0]],
	              o: [[0, 0], [1.9329999999999998, 0], [0, 0], [0, 1.9329999999999998], [0, 0], [-1.933, 0], [0, 0], [0, -1.933], [0, 0], [0, 0]]
	            }]
	          }]
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 68
	        },
	        c: {
	          a: 0,
	          k: [0.8745098039215686, 0.8784313725490196, 0.8901960784313725, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }]
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 52,
	    hd: false,
	    nm: "1 - Null",
	    sr: 1,
	    parent: 43,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 3.5]
	      },
	      o: {
	        a: 0,
	        k: 70
	      },
	      p: {
	        a: 0,
	        k: [61, 24.5]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    ao: 0,
	    ip: 0,
	    op: 360,
	    st: 0,
	    bm: 0
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 53,
	    hd: false,
	    nm: "1",
	    sr: 1,
	    parent: 52,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 70
	      }
	    },
	    ao: 0,
	    ip: 0,
	    op: 360,
	    st: 0,
	    bm: 0,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 1,
	          k: [{
	            t: 259.734,
	            s: [{
	              c: true,
	              v: [[0.01, 0], [-0.01, 0], [0, 0.01], [0, 6.99], [-0.01, 7], [0.01, 7], [0, 6.99], [0, 0.01], [0.01, 0], [0.01, 0]],
	              i: [[0, 0], [0, 0], [0, -0.0055], [0, 0], [0.0055, 0], [0, 0], [0, 0.0055], [0, 0], [-0.0055, 0], [0, 0]],
	              o: [[0, 0], [0.00552, 0], [0, 0], [0, 0.005519999999999747], [0, 0], [-0.00552, 0], [0, 0], [0, -0.00552], [0, 0], [0, 0]]
	            }],
	            o: {
	              x: [0.5],
	              y: [0.35]
	            },
	            i: {
	              x: [0.15],
	              y: [1]
	            }
	          }, {
	            t: 272.802,
	            s: [{
	              c: true,
	              v: [[3.5, 0], [33.5, 0], [37, 3.5], [37, 3.5], [33.5, 7], [3.5, 7], [0, 3.5], [0, 3.5], [3.5, 0], [3.5, 0]],
	              i: [[0, 0], [0, 0], [0, -1.933], [0, 0], [1.933, 0], [0, 0], [0, 1.933], [0, 0], [-1.933, 0], [0, 0]],
	              o: [[0, 0], [1.9329999999999998, 0], [0, 0], [0, 1.9329999999999998], [0, 0], [-1.933, 0], [0, 0], [0, -1.933], [0, 0], [0, 0]]
	            }]
	          }]
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 68
	        },
	        c: {
	          a: 0,
	          k: [0.8745098039215686, 0.8784313725490196, 0.8901960784313725, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }]
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 54,
	    hd: false,
	    nm: "bg2 - Null",
	    sr: 1,
	    parent: 43,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 1,
	        k: [{
	          t: 141.16799999999998,
	          s: [0],
	          o: {
	            x: [0.5],
	            y: [0]
	          },
	          i: {
	            x: [0.5],
	            y: [1]
	          }
	        }, {
	          t: 189.168,
	          s: [100]
	        }]
	      },
	      p: {
	        a: 1,
	        k: [{
	          t: 141.16799999999998,
	          s: [0, -69],
	          o: {
	            x: [0.5],
	            y: [0]
	          },
	          i: {
	            x: [0.5],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 189.168,
	          s: [0, 0]
	        }]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    ao: 0,
	    ip: 0,
	    op: 360,
	    st: 0,
	    bm: 0
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 55,
	    hd: false,
	    nm: "bg2",
	    sr: 1,
	    parent: 54,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 1,
	        k: [{
	          t: 141.16799999999998,
	          s: [0],
	          o: {
	            x: [0.5],
	            y: [0]
	          },
	          i: {
	            x: [0.5],
	            y: [1]
	          }
	        }, {
	          t: 189.168,
	          s: [100]
	        }]
	      }
	    },
	    ao: 0,
	    ip: 0,
	    op: 360,
	    st: 0,
	    bm: 0,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[12, 0], [95, 0], [107, 12], [107, 57], [95, 69], [12, 69], [0, 57], [0, 12], [12, 0], [12, 0]],
	            i: [[0, 0], [0, 0], [0, -6.6274], [0, 0], [6.6274, 0], [0, 0], [0, 6.6274], [0, 0], [-6.6274, 0], [0, 0]],
	            o: [[0, 0], [6.627420000000001, 0], [0, 0], [0, 6.627420000000001], [0, 0], [-6.62742, 0], [0, 0], [0, -6.62742], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 100
	        },
	        c: {
	          a: 0,
	          k: [1, 1, 1, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }]
	  }]
	}, {
	  nm: "Frame 1684599022",
	  fr: 60,
	  id: "mbrr2087tm9nwyh3",
	  layers: [{
	    ty: 3,
	    ddd: 0,
	    ind: 68,
	    hd: false,
	    nm: "vibe loader - Null",
	    sr: 1,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    ao: 0,
	    ip: 0,
	    op: 360,
	    st: 0,
	    bm: 0
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 69,
	    hd: false,
	    nm: "left - Null",
	    sr: 1,
	    parent: 68,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [18, 102]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    ao: 0,
	    ip: 0,
	    op: 360,
	    st: 0,
	    bm: 0
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 70,
	    hd: false,
	    nm: "Frame 1684599022 - Null",
	    sr: 1,
	    parent: 69,
	    ks: {
	      a: {
	        a: 0,
	        k: [46.8368, 24.2666]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [53.2858, 34.4668]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 1,
	        k: [{
	          t: 202.074,
	          s: [0.1, 0.1],
	          o: {
	            x: [0.42],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          }
	        }, {
	          t: 250.074,
	          s: [100, 100]
	        }]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    ao: 0,
	    ip: 0,
	    op: 360,
	    st: 0,
	    bm: 0
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 59,
	    hd: false,
	    nm: "Path - Null",
	    sr: 1,
	    parent: 70,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 12
	      },
	      p: {
	        a: 0,
	        k: [90.551, 14.8]
	      },
	      r: {
	        a: 0,
	        k: -179.9999
	      },
	      s: {
	        a: 0,
	        k: [100, -99.99]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    ao: 0,
	    ip: 0,
	    op: 360,
	    st: 0,
	    bm: 0
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 60,
	    hd: false,
	    nm: "Path",
	    sr: 1,
	    parent: 59,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 12
	      }
	    },
	    ao: 0,
	    ip: 0,
	    op: 360,
	    st: 0,
	    bm: 0,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[0, 35], [88, 35], [66.1043, 14.0907], [61.9299, 14.0157], [52.8745, 22.1005], [48.4566, 21.7669], [30.9869, 1.1293], [26.3037, 1.061], [0.0001, 30.5006], [0, 35]],
	            i: [[0, 0], [0, 0], [0, 0], [1.2193, -1.0886], [0, 0], [1.1559, 1.3655], [0, 0], [1.2877, -1.4413], [0, 0], [0, 0]],
	            o: [[0, 0], [0, 0], [-1.1852499999999964, -1.13185], [0, 0], [-1.3200300000000027, 1.1785500000000013], [0, 0], [-1.2513599999999983, -1.47828], [0, 0], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 78
	        },
	        c: {
	          a: 0,
	          k: [0.41568627450980394, 0.17254901960784313, 0.9803921568627451, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 2
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }]
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 61,
	    hd: false,
	    nm: "Path - Null",
	    sr: 1,
	    parent: 70,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 10
	      },
	      p: {
	        a: 0,
	        k: [43.4858, 3.0893]
	      },
	      r: {
	        a: 0,
	        k: -179.9999
	      },
	      s: {
	        a: 0,
	        k: [100, -99.99]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    ao: 0,
	    ip: 0,
	    op: 360,
	    st: 0,
	    bm: 0
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 62,
	    hd: false,
	    nm: "Path",
	    sr: 1,
	    parent: 61,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 10
	      }
	    },
	    ao: 0,
	    ip: 0,
	    op: 360,
	    st: 0,
	    bm: 0,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[7.8061, 15.6], [15.6122, 7.8], [7.8061, 0], [0, 7.8], [7.8061, 15.6], [7.8061, 15.6]],
	            i: [[0, 0], [0, 4.3078], [4.3112, 0], [0, -4.3078], [-4.3112, 0], [0, 0]],
	            o: [[4.3112, 0], [0, -4.30782], [-4.3112, 0], [0, 4.30782], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 78
	        },
	        c: {
	          a: 0,
	          k: [0.41568627450980394, 0.17254901960784313, 0.9803921568627451, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 2
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }]
	  }]
	}, {
	  nm: "[FRAME] vibe loader - Null / left - Null / vibe loader - Null / left - Null / Frame 1684599022 - Null / vibe loader - Null / left - Null / Frame 1684599022 - Null / Frame 1684599022 / Frame 1684599022 / bg3 - Null / bg3",
	  fr: 60,
	  id: "mbrr20874fw0asec",
	  layers: [{
	    ty: 3,
	    ddd: 0,
	    ind: 63,
	    hd: false,
	    nm: "vibe loader - Null",
	    sr: 1,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    ao: 0,
	    ip: 0,
	    op: 360,
	    st: 0,
	    bm: 0
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 64,
	    hd: false,
	    nm: "left - Null",
	    sr: 1,
	    parent: 63,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [18, 102]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    ao: 0,
	    ip: 0,
	    op: 360,
	    st: 0,
	    bm: 0
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 65,
	    hd: false,
	    nm: "vibe loader - Null",
	    sr: 1,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    ao: 0,
	    ip: 0,
	    op: 360,
	    st: 0,
	    bm: 0
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 66,
	    hd: false,
	    nm: "left - Null",
	    sr: 1,
	    parent: 65,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [18, 102]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    ao: 0,
	    ip: 0,
	    op: 360,
	    st: 0,
	    bm: 0
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 67,
	    hd: false,
	    nm: "Frame 1684599022 - Null",
	    sr: 1,
	    parent: 66,
	    ks: {
	      a: {
	        a: 0,
	        k: [46.8368, 24.2666]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [53.2858, 34.4668]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 1,
	        k: [{
	          t: 202.074,
	          s: [0.1, 0.1],
	          o: {
	            x: [0.42],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          }
	        }, {
	          t: 250.074,
	          s: [100, 100]
	        }]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    ao: 0,
	    ip: 0,
	    op: 360,
	    st: 0,
	    bm: 0
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 68,
	    hd: false,
	    nm: "vibe loader - Null",
	    sr: 1,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    ao: 0,
	    ip: 0,
	    op: 360,
	    st: 0,
	    bm: 0
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 69,
	    hd: false,
	    nm: "left - Null",
	    sr: 1,
	    parent: 68,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [18, 102]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    ao: 0,
	    ip: 0,
	    op: 360,
	    st: 0,
	    bm: 0
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 70,
	    hd: false,
	    nm: "Frame 1684599022 - Null",
	    sr: 1,
	    parent: 69,
	    ks: {
	      a: {
	        a: 0,
	        k: [46.8368, 24.2666]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [53.2858, 34.4668]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 1,
	        k: [{
	          t: 202.074,
	          s: [0.1, 0.1],
	          o: {
	            x: [0.42],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          }
	        }, {
	          t: 250.074,
	          s: [100, 100]
	        }]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    ao: 0,
	    ip: 0,
	    op: 360,
	    st: 0,
	    bm: 0
	  }, {
	    ddd: 0,
	    ind: 71,
	    ty: 0,
	    nm: "Frame 1684599022",
	    td: 1,
	    refId: "mbrmuh9e77797556",
	    sr: 1,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    ao: 0,
	    w: 264,
	    h: 214,
	    ip: 0,
	    op: 360,
	    st: 0,
	    hd: false,
	    bm: 0
	  }, {
	    ddd: 0,
	    ind: 72,
	    ty: 0,
	    nm: "Frame 1684599022",
	    refId: "mbrr2087tm9nwyh3",
	    sr: 1,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    ao: 0,
	    w: 264,
	    h: 214,
	    ip: 0,
	    op: 360,
	    st: 0,
	    hd: false,
	    bm: 0,
	    tt: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 73,
	    hd: false,
	    nm: "bg3 - Null",
	    sr: 1,
	    parent: 64,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 1,
	        k: [{
	          t: 121.87799999999999,
	          s: [0],
	          o: {
	            x: [0.5],
	            y: [0]
	          },
	          i: {
	            x: [0.5],
	            y: [1]
	          }
	        }, {
	          t: 169.87800000000001,
	          s: [100]
	        }]
	      },
	      p: {
	        a: 1,
	        k: [{
	          t: 121.87799999999999,
	          s: [0, -69],
	          o: {
	            x: [0.5],
	            y: [0]
	          },
	          i: {
	            x: [0.5],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 169.87800000000001,
	          s: [0, 0]
	        }]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    ao: 0,
	    ip: 0,
	    op: 360,
	    st: 0,
	    bm: 0
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 74,
	    hd: false,
	    nm: "bg3",
	    sr: 1,
	    parent: 73,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 1,
	        k: [{
	          t: 121.87799999999999,
	          s: [0],
	          o: {
	            x: [0.5],
	            y: [0]
	          },
	          i: {
	            x: [0.5],
	            y: [1]
	          }
	        }, {
	          t: 169.87800000000001,
	          s: [100]
	        }]
	      }
	    },
	    ao: 0,
	    ip: 0,
	    op: 360,
	    st: 0,
	    bm: 0,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[12, 0], [95, 0], [107, 12], [107, 57], [95, 69], [12, 69], [0, 57], [0, 12], [12, 0], [12, 0]],
	            i: [[0, 0], [0, 0], [0, -6.6274], [0, 0], [6.6274, 0], [0, 0], [0, 6.6274], [0, 0], [-6.6274, 0], [0, 0]],
	            o: [[0, 0], [6.627420000000001, 0], [0, 0], [0, 6.627420000000001], [0, 0], [-6.62742, 0], [0, 0], [0, -6.62742], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 100
	        },
	        c: {
	          a: 0,
	          k: [1, 1, 1, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }]
	  }]
	}, {
	  nm: "[FRAME] vibe loader - Null / 1-1 / right / left / line bottom2 - Null / line bottom2 / line bottom1 - Null / line bottom1 / bg - Null / bg / bg",
	  fr: 60,
	  id: "mbrr207ry5vyvyim",
	  layers: [{
	    ty: 3,
	    ddd: 0,
	    ind: 75,
	    hd: false,
	    nm: "vibe loader - Null",
	    sr: 1,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    ao: 0,
	    ip: 0,
	    op: 360,
	    st: 0,
	    bm: 0
	  }, {
	    ddd: 0,
	    ind: 76,
	    ty: 0,
	    nm: "1-1",
	    refId: "mbrr207r8s9ajm5g",
	    sr: 1,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    ao: 0,
	    w: 264,
	    h: 214,
	    ip: 0,
	    op: 360,
	    st: 0,
	    hd: false,
	    bm: 0
	  }, {
	    ddd: 0,
	    ind: 77,
	    ty: 0,
	    nm: "right",
	    refId: "mbrr20816ro91xdd",
	    sr: 1,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    ao: 0,
	    w: 264,
	    h: 214,
	    ip: 0,
	    op: 360,
	    st: 0,
	    hd: false,
	    bm: 0
	  }, {
	    ddd: 0,
	    ind: 78,
	    ty: 0,
	    nm: "left",
	    refId: "mbrr20874fw0asec",
	    sr: 1,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    ao: 0,
	    w: 264,
	    h: 214,
	    ip: 0,
	    op: 360,
	    st: 0,
	    hd: false,
	    bm: 0
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 79,
	    hd: false,
	    nm: "line bottom2 - Null",
	    sr: 1,
	    parent: 75,
	    ks: {
	      a: {
	        a: 0,
	        k: [30, 3.5]
	      },
	      o: {
	        a: 1,
	        k: [{
	          t: 284.61,
	          s: [0],
	          o: {
	            x: [0.5],
	            y: [0]
	          },
	          i: {
	            x: [0.5],
	            y: [1]
	          }
	        }, {
	          t: 332.61,
	          s: [20]
	        }]
	      },
	      p: {
	        a: 1,
	        k: [{
	          t: 284.61,
	          s: [173, 178.5],
	          o: {
	            x: [0.5],
	            y: [0]
	          },
	          i: {
	            x: [0.5],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 332.61,
	          s: [173, 185.5]
	        }]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    ao: 0,
	    ip: 120,
	    op: 360,
	    st: 0,
	    bm: 0
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 80,
	    hd: false,
	    nm: "line bottom2",
	    sr: 1,
	    parent: 79,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 1,
	        k: [{
	          t: 284.61,
	          s: [0],
	          o: {
	            x: [0.5],
	            y: [0]
	          },
	          i: {
	            x: [0.5],
	            y: [1]
	          }
	        }, {
	          t: 332.61,
	          s: [20]
	        }]
	      }
	    },
	    ao: 0,
	    ip: 120,
	    op: 360,
	    st: 0,
	    bm: 0,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[3.5, 0], [56.5, 0], [60, 3.5], [60, 3.5], [56.5, 7], [3.5, 7], [0, 3.5], [0, 3.5], [3.5, 0], [3.5, 0]],
	            i: [[0, 0], [0, 0], [0, -1.933], [0, 0], [1.933, 0], [0, 0], [0, 1.933], [0, 0], [-1.933, 0], [0, 0]],
	            o: [[0, 0], [1.9329999999999998, 0], [0, 0], [0, 1.9329999999999998], [0, 0], [-1.933, 0], [0, 0], [0, -1.933], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 100
	        },
	        c: {
	          a: 0,
	          k: [1, 1, 1, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }]
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 81,
	    hd: false,
	    nm: "line bottom1 - Null",
	    sr: 1,
	    parent: 75,
	    ks: {
	      a: {
	        a: 0,
	        k: [30, 3.5]
	      },
	      o: {
	        a: 1,
	        k: [{
	          t: 289.41,
	          s: [0],
	          o: {
	            x: [0.5],
	            y: [0]
	          },
	          i: {
	            x: [0.5],
	            y: [1]
	          }
	        }, {
	          t: 337.40999999999997,
	          s: [20]
	        }]
	      },
	      p: {
	        a: 1,
	        k: [{
	          t: 289.41,
	          s: [52, 178.5],
	          o: {
	            x: [0.5],
	            y: [0]
	          },
	          i: {
	            x: [0.5],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 337.40999999999997,
	          s: [52, 185.5]
	        }]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    ao: 0,
	    ip: 120,
	    op: 360,
	    st: 0,
	    bm: 0
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 82,
	    hd: false,
	    nm: "line bottom1",
	    sr: 1,
	    parent: 81,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 1,
	        k: [{
	          t: 289.41,
	          s: [0],
	          o: {
	            x: [0.5],
	            y: [0]
	          },
	          i: {
	            x: [0.5],
	            y: [1]
	          }
	        }, {
	          t: 337.40999999999997,
	          s: [20]
	        }]
	      }
	    },
	    ao: 0,
	    ip: 120,
	    op: 360,
	    st: 0,
	    bm: 0,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[3.5, 0], [56.5, 0], [60, 3.5], [60, 3.5], [56.5, 7], [3.5, 7], [0, 3.5], [0, 3.5], [3.5, 0], [3.5, 0]],
	            i: [[0, 0], [0, 0], [0, -1.933], [0, 0], [1.933, 0], [0, 0], [0, 1.933], [0, 0], [-1.933, 0], [0, 0]],
	            o: [[0, 0], [1.9329999999999998, 0], [0, 0], [0, 1.9329999999999998], [0, 0], [-1.933, 0], [0, 0], [0, -1.933], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 100
	        },
	        c: {
	          a: 0,
	          k: [1, 1, 1, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }]
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 83,
	    hd: false,
	    nm: "bg - Null",
	    sr: 1,
	    parent: 75,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 60
	      },
	      p: {
	        a: 0,
	        k: [2, 4]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    ao: 0,
	    ip: 0,
	    op: 360,
	    st: 0,
	    bm: 0
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 84,
	    hd: false,
	    nm: "bg",
	    sr: 1,
	    parent: 83,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 60
	      }
	    },
	    ao: 0,
	    ip: 0,
	    op: 360,
	    st: 0,
	    bm: 0,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[24, 0], [236, 0], [260, 24], [260, 182], [236, 206], [24, 206], [0, 182], [0, 24], [24, 0], [24, 0]],
	            i: [[0, 0], [0, 0], [0, -13.2548], [0, 0], [13.2548, 0], [0, 0], [0, 13.2548], [0, 0], [-13.2548, 0], [0, 0]],
	            o: [[0, 0], [13.254829999999998, 0], [0, 0], [0, 13.254829999999998], [0, 0], [-13.25483, 0], [0, 0], [0, -13.25483], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 100
	        },
	        c: {
	          a: 0,
	          k: [0.7686274509803922, 0.9019607843137255, 1, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }]
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 85,
	    hd: false,
	    nm: "bg",
	    sr: 1,
	    parent: 83,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 60
	      }
	    },
	    ao: 0,
	    ip: 0,
	    op: 360,
	    st: 0,
	    bm: 0,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[24, 0], [236, 0], [260, 24], [260, 182], [236, 206], [24, 206], [0, 182], [0, 24], [24, 0], [24, 0]],
	            i: [[0, 0], [0, 0], [0, -13.2548], [0, 0], [13.2548, 0], [0, 0], [0, 13.2548], [0, 0], [-13.2548, 0], [0, 0]],
	            o: [[0, 0], [13.254829999999998, 0], [0, 0], [0, 13.254829999999998], [0, 0], [-13.25483, 0], [0, 0], [0, -13.25483], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 100
	        },
	        c: {
	          a: 0,
	          k: [0.7686274509803922, 0.9019607843137255, 1, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }, {
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "rc",
	        nm: "Rectangle",
	        hd: false,
	        p: {
	          a: 0,
	          k: [130.5, 103.5]
	        },
	        s: {
	          a: 0,
	          k: [522, 414]
	        },
	        r: {
	          a: 0,
	          k: 0
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 0
	        },
	        c: {
	          a: 0,
	          k: [0, 1, 0, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: [{
	      nm: "Drop Shadow",
	      ty: 25,
	      en: 1,
	      ef: [{
	        ty: 2,
	        nm: "Shadow Color",
	        v: {
	          a: 0,
	          k: [0, 0, 0, 1]
	        }
	      }, {
	        ty: 0,
	        nm: "Opacity",
	        v: {
	          a: 0,
	          k: 12
	        }
	      }, {
	        ty: 1,
	        nm: "Direction",
	        v: {
	          a: 0,
	          k: 90
	        }
	      }, {
	        ty: 0,
	        nm: "Distance",
	        v: {
	          a: 0,
	          k: 2.16
	        }
	      }, {
	        ty: 0,
	        nm: "Softness",
	        v: {
	          a: 0,
	          k: 7.56
	        }
	      }, {
	        ty: 4,
	        nm: "Shadow Only",
	        v: {
	          a: 0,
	          k: 0
	        }
	      }]
	    }]
	  }]
	}];
	var layers = [{
	  ddd: 0,
	  ind: 1,
	  ty: 0,
	  nm: "vibe loader",
	  refId: "mbrr207ry5vyvyim",
	  sr: 1,
	  ks: {
	    a: {
	      a: 0,
	      k: [0, 0]
	    },
	    p: {
	      a: 0,
	      k: [0, 0]
	    },
	    s: {
	      a: 0,
	      k: [100, 100]
	    },
	    sk: {
	      a: 0,
	      k: 0
	    },
	    sa: {
	      a: 0,
	      k: 0
	    },
	    r: {
	      a: 0,
	      k: 0
	    },
	    o: {
	      a: 0,
	      k: 100
	    }
	  },
	  ao: 0,
	  w: 264,
	  h: 214,
	  ip: 0,
	  op: 360,
	  st: 0,
	  hd: false,
	  bm: 0
	}];
	var meta = {
	  a: "",
	  d: "",
	  tc: "",
	  g: "Aninix"
	};
	var blockAnimation = {
	  fr: fr,
	  v: v,
	  ip: ip,
	  op: op,
	  w: w,
	  h: h,
	  nm: nm,
	  ddd: ddd,
	  markers: markers,
	  assets: assets,
	  layers: layers,
	  meta: meta
	};

	var renderLottieAnimation = function renderLottieAnimation(target) {
	  if (!target) {
	    console.error('Target container is not specified for Lottie animation.');
	    return;
	  }
	  var animation = ui_lottie.Lottie.loadAnimation({
	    container: target,
	    renderer: 'svg',
	    loop: false,
	    autoplay: true,
	    animationData: blockAnimation
	  });
	  setInterval(function () {
	    animation.stop();
	    animation.play();
	  }, 15000);
	};

	exports.DiskFile = DiskFile;
	exports.Analytics = Analytics;
	exports.Pseudolinks = Pseudolinks;
	exports.renderLottieAnimation = renderLottieAnimation;

}((this.BX.Landing.Pub = this.BX.Landing.Pub || {}),BX,BX.Landing,BX.UI));
//# sourceMappingURL=script.js.map
