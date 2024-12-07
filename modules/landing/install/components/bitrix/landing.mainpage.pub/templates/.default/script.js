/* eslint-disable */
this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
(function (exports) {
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

	var SearchResult = /*#__PURE__*/function () {
	  /**
	   * Constructor.
	   */
	  function SearchResult() {
	    babelHelpers.classCallCheck(this, SearchResult);
	    this.scrollToFirstBlock();
	  }

	  /**
	   * Finds first highlight word and scroll to it.
	   * @return {void}
	   */
	  babelHelpers.createClass(SearchResult, [{
	    key: "scrollToFirstBlock",
	    value: function scrollToFirstBlock() {
	      var result = document.querySelector('.landing-highlight');
	      if (result) {
	        var parent = result.parentNode;
	        while (parent) {
	          if (parent.classList.contains('block-wrapper')) {
	            window.scrollTo({
	              top: parent.offsetTop,
	              behavior: 'smooth'
	            });
	            break;
	          }
	          parent = parent.parentNode;
	        }
	      }
	    }
	  }]);
	  return SearchResult;
	}();

	var TimeStamp = /*#__PURE__*/function () {
	  /**
	   * Constructor.
	   */
	  function TimeStamp() {
	    babelHelpers.classCallCheck(this, TimeStamp);
	    this.removeTimestamp();
	  }

	  /**
	   * Removes 'ts' param from query string.
	   * @return {void}
	   */
	  babelHelpers.createClass(TimeStamp, [{
	    key: "removeTimestamp",
	    value: function removeTimestamp() {
	      var uri = window.location.toString();
	      uri = uri.replace(/(ts=[\d]+[&]*)/, '');
	      if (uri.slice(-1) === '?' || uri.slice(-1) === '&') {
	        uri = uri.slice(0, -1);
	      }
	      window.history.replaceState({}, document.title, uri);
	    }
	  }]);
	  return TimeStamp;
	}();

	function _createForOfIteratorHelper(o, allowArrayLike) { var it = typeof Symbol !== "undefined" && o[Symbol.iterator] || o["@@iterator"]; if (!it) { if (Array.isArray(o) || (it = _unsupportedIterableToArray(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = it.call(o); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it["return"] != null) it["return"](); } finally { if (didErr) throw err; } } }; }
	function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }
	function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i]; return arr2; }
	var Analytics = /*#__PURE__*/function () {
	  /**
	   * Constructor.
	   */
	  function Analytics() {
	    babelHelpers.classCallCheck(this, Analytics);
	    document.addEventListener('click', this.onClick.bind(this));
	  }

	  /**
	   * Click callback.
	   *
	   * @return {void}
	   */
	  babelHelpers.createClass(Analytics, [{
	    key: "onClick",
	    value: function onClick(event) {
	      var parent = null;
	      var currentElement = event.target;
	      if (currentElement.tagName.toLowerCase() !== 'a') {
	        return;
	      }
	      while (currentElement) {
	        if (currentElement.classList) {
	          if (currentElement.classList.contains('workarea-content-paddings')) {
	            break;
	          }
	          if (currentElement.classList.contains('block-wrapper')) {
	            parent = currentElement;
	            break;
	          }
	        }
	        currentElement = currentElement.parentElement;
	      }
	      if (parent && parent.classList) {
	        var code = '';
	        var _iterator = _createForOfIteratorHelper(parent.classList),
	          _step;
	        try {
	          for (_iterator.s(); !(_step = _iterator.n()).done;) {
	            var className = _step.value;
	            if (className !== 'block-wrapper') {
	              code = className;
	              break;
	            }
	          }
	        } catch (err) {
	          _iterator.e(err);
	        } finally {
	          _iterator.f();
	        }
	        code = code.replace('block-', 'widget-id_');
	        BX.UI.Analytics.sendData({
	          tool: 'landing',
	          category: 'vibe',
	          event: 'click_on_button',
	          p2: code
	        });
	      }
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

	exports.DiskFile = DiskFile;
	exports.SearchResult = SearchResult;
	exports.TimeStamp = TimeStamp;
	exports.Analytics = Analytics;
	exports.Pseudolinks = Pseudolinks;

}((this.BX.Landing.Pub = this.BX.Landing.Pub || {})));
//# sourceMappingURL=script.js.map
