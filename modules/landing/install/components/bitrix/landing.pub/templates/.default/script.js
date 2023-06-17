this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
(function (exports,main_core,landing_sliderhacks) {
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

	var onEditButtonClick = Symbol('onEditButtonClick');
	var onBackButtonClick = Symbol('onBackButtonClick');
	var onForwardButtonClick = Symbol('onForwardButtonClick');
	var onCopyLinkButtonClick = Symbol('onCopyLinkButtonClick');
	var onUniqueViewIconClick = Symbol('onUniqueViewIconClick');
	var TopPanel = /*#__PURE__*/function () {
	  function TopPanel(data) {
	    babelHelpers.classCallCheck(this, TopPanel);
	    this.userData = data.userData;
	    main_core.Event.bind(TopPanel.getEditButton(), 'click', this[onEditButtonClick]);
	    main_core.Event.bind(TopPanel.getBackButton(), 'click', this[onBackButtonClick]);
	    main_core.Event.bind(TopPanel.getForwardButton(), 'click', this[onForwardButtonClick]);
	    main_core.Event.bind(TopPanel.getCopyLinkButton(), 'click', this[onCopyLinkButtonClick]);
	    main_core.Event.bind(TopPanel.getUniqueViewIcon(), 'click', this[onUniqueViewIconClick]);
	    TopPanel.pushHistory(window.location.toString());
	    TopPanel.checkNavButtonsActivity();
	    TopPanel.checkHints();
	    TopPanel.initUniqueViewPopup(this.userData);
	  }
	  babelHelpers.createClass(TopPanel, [{
	    key: onEditButtonClick,
	    value: function value(event) {
	      event.preventDefault();
	      var href = main_core.Dom.attr(event.currentTarget, 'href');
	      var landingId = main_core.Dom.attr(event.currentTarget, 'data-landingId');
	      if (main_core.Type.isString(href) && href !== '') {
	        TopPanel.openSlider(href, landingId);
	      }
	    }
	  }, {
	    key: onCopyLinkButtonClick,
	    value: function value(event) {
	      event.preventDefault();
	      var link = BX.util.remove_url_param(window.location.href, ["IFRAME", "IFRAME_TYPE"]);
	      var node = event.target;
	      if (BX.clipboard.isCopySupported()) {
	        BX.clipboard.copy(link);
	        this.timeoutIds = this.timeoutIds || [];
	        var popupParams = {
	          content: main_core.Loc.getMessage('LANDING_TPL_PUB_COPIED_LINK'),
	          darkMode: true,
	          autoHide: true,
	          zIndex: 1000,
	          angle: true,
	          offsetLeft: 20,
	          bindOptions: {
	            position: 'top'
	          }
	        };
	        var popup = new BX.PopupWindow('landing_clipboard_copy', node, popupParams);
	        popup.show();
	        var timeoutId;
	        while (timeoutId = this.timeoutIds.pop()) {
	          clearTimeout(timeoutId);
	        }
	        timeoutId = setTimeout(function () {
	          popup.close();
	        }, 2000);
	        this.timeoutIds.push(timeoutId);
	      }
	    }
	  }, {
	    key: onUniqueViewIconClick,
	    value: function value(event) {
	      var popup = document.querySelector('.landing-pub-top-panel-unique-view-popup');
	      if (main_core.Dom.hasClass(popup, 'hide')) {
	        main_core.Dom.removeClass(popup, 'hide');
	        setTimeout(function () {
	          main_core.Dom.addClass(popup, 'hide');
	        }, 2000);
	      } else {
	        main_core.Dom.addClass(popup, 'hide');
	      }
	    }
	  }, {
	    key: onBackButtonClick,
	    value: function value(event) {
	      event.preventDefault();
	      if (main_core.Type.isArrayFilled(TopPanel.history) && main_core.Type.isNumber(TopPanel.historyState) && TopPanel.historyState > 0) {
	        void landing_sliderhacks.SliderHacks.reloadSlider(TopPanel.history[--TopPanel.historyState]);
	        TopPanel.checkNavButtonsActivity();
	      }
	    }
	  }, {
	    key: onForwardButtonClick,
	    value: function value(event) {
	      event.preventDefault();
	      if (main_core.Type.isArrayFilled(TopPanel.history) && main_core.Type.isNumber(TopPanel.historyState) && TopPanel.historyState < TopPanel.history.length - 1) {
	        void landing_sliderhacks.SliderHacks.reloadSlider(TopPanel.history[++TopPanel.historyState]);
	        TopPanel.checkNavButtonsActivity();
	      }
	    }
	  }], [{
	    key: "getLayout",
	    value: function getLayout() {
	      return TopPanel.cache.remember('layout', function () {
	        return document.querySelector('.landing-pub-top-panel');
	      });
	    }
	  }, {
	    key: "getEditButton",
	    value: function getEditButton() {
	      return TopPanel.cache.remember('editButton', function () {
	        return TopPanel.getLayout().querySelector('.landing-pub-top-panel-edit-button');
	      });
	    }
	  }, {
	    key: "openSlider",
	    value: function openSlider(url, landingId) {
	      BX.SidePanel.Instance.open(url, {
	        cacheable: false,
	        customLeftBoundary: 60,
	        allowChangeHistory: false,
	        events: {
	          onClose: function onClose() {
	            void landing_sliderhacks.SliderHacks.reloadSlider(window.location.toString().split('#')[0] + '#landingId' + landingId);
	          }
	        }
	      });
	    } // HISTORY save
	  }, {
	    key: "pushHistory",
	    value: function pushHistory(url) {
	      if (!main_core.Type.isNumber(TopPanel.historyState)) {
	        TopPanel.historyState = -1; // will increase later
	      }

	      if (TopPanel.historyState < TopPanel.history.length - 1) {
	        TopPanel.history.splice(TopPanel.historyState + 1);
	      }
	      TopPanel.history.push(url);
	      TopPanel.historyState++;
	    }
	  }, {
	    key: "checkNavButtonsActivity",
	    value: function checkNavButtonsActivity() {
	      main_core.Dom.removeClass(TopPanel.getForwardButton(), 'ui-btn-disabled');
	      main_core.Dom.removeClass(TopPanel.getBackButton(), 'ui-btn-disabled');
	      if (!main_core.Type.isArrayFilled(TopPanel.history) || !main_core.Type.isNumber(TopPanel.historyState) || TopPanel.history.length === 1) {
	        main_core.Dom.addClass(TopPanel.getForwardButton(), 'ui-btn-disabled');
	        main_core.Dom.addClass(TopPanel.getBackButton(), 'ui-btn-disabled');
	        return;
	      }
	      if (TopPanel.historyState === 0) {
	        main_core.Dom.addClass(TopPanel.getBackButton(), 'ui-btn-disabled');
	      }
	      if (TopPanel.historyState >= TopPanel.history.length - 1) {
	        main_core.Dom.addClass(TopPanel.getForwardButton(), 'ui-btn-disabled');
	      }
	    }
	  }, {
	    key: "getBackButton",
	    value: function getBackButton() {
	      return TopPanel.cache.remember('backButton', function () {
	        var layout = TopPanel.getLayout();
	        return layout ? layout.querySelector('.landing-pub-top-panel-back') : null;
	      });
	    }
	  }, {
	    key: "getForwardButton",
	    value: function getForwardButton() {
	      return TopPanel.cache.remember('forwardButton', function () {
	        var layout = TopPanel.getLayout();
	        return layout ? layout.querySelector('.landing-pub-top-panel-forward') : null;
	      });
	    }
	  }, {
	    key: "getCopyLinkButton",
	    value: function getCopyLinkButton() {
	      return TopPanel.cache.remember('copyLinkButton', function () {
	        var layout = TopPanel.getLayout();
	        return layout ? layout.querySelector('.landing-page-link-btn') : null;
	      });
	    }
	  }, {
	    key: "getUniqueViewIcon",
	    value: function getUniqueViewIcon() {
	      return TopPanel.cache.remember('uniqueViewIcon', function () {
	        var layout = TopPanel.getLayout();
	        return layout ? layout.querySelector('.landing-pub-top-panel-unique-view') : null;
	      });
	    }
	  }, {
	    key: "checkHints",
	    value: function checkHints() {
	      var linkPage = document.querySelector('.landing-pub-top-panel-chain-link-page');
	      if (linkPage) {
	        if (parseInt(window.getComputedStyle(linkPage).width) < 200) {
	          main_core.Dom.style(linkPage, 'pointer-events', 'none');
	        } else {
	          BX.UI.Hint.init(BX('landing-pub-top-panel-chain-link-page'));
	        }
	      }
	    }
	  }, {
	    key: "initUniqueViewPopup",
	    value: function initUniqueViewPopup(userData) {
	      var setUserId = userData.id;
	      var setUserName = userData.name;
	      var avatar = userData.avatar;
	      if (setUserId.length === setUserName.length) {
	        for (var i = 0; i < setUserId.length; i++) {
	          this.createUserItem(setUserId[i], setUserName[i], avatar[i]);
	        }
	      }
	    }
	  }, {
	    key: "createUserItem",
	    value: function createUserItem(id, name, avatar) {
	      var itemContainer = document.querySelector('.landing-pub-top-panel-unique-view-popup-item-container');
	      var userUrl = window.location.origin + '/company/personal/user/' + id + '/';
	      var userItem = BX.Dom.create({
	        tag: 'div',
	        props: {
	          classList: 'landing-pub-top-panel-unique-view-popup-item'
	        }
	      });
	      var userItemAvatar;
	      if (avatar && avatar !== '') {
	        userItemAvatar = BX.Dom.create({
	          tag: 'div',
	          props: {
	            classList: 'landing-pub-top-panel-unique-view-popup-item-avatar'
	          }
	        });
	        avatar = "url('" + avatar + "')";
	        main_core.Dom.style(userItemAvatar, 'background-image', avatar);
	      } else {
	        userItemAvatar = BX.Dom.create({
	          tag: 'div',
	          props: {
	            classList: 'landing-pub-top-panel-unique-view-popup-item-avatar landing-pub-top-panel-unique-view-popup-item-avatar-empty'
	          }
	        });
	      }
	      var userItemLink = BX.Dom.create({
	        tag: 'a',
	        props: {
	          classList: 'landing-pub-top-panel-unique-view-popup-item-link'
	        },
	        text: name
	      });
	      main_core.Dom.attr(userItemLink, 'href', userUrl);
	      main_core.Dom.attr(userItemLink, 'target', '_blank');
	      main_core.Dom.append(userItemAvatar, userItem);
	      main_core.Dom.append(userItemLink, userItem);
	      main_core.Dom.append(userItem, itemContainer);
	    }
	  }]);
	  return TopPanel;
	}();
	babelHelpers.defineProperty(TopPanel, "cache", new main_core.Cache.MemoryCache());
	babelHelpers.defineProperty(TopPanel, "history", []);

	var PageTransition = /*#__PURE__*/function () {
	  /**
	   * Constructor.
	   */
	  function PageTransition() {
	    babelHelpers.classCallCheck(this, PageTransition);
	    this.init();
	  }
	  babelHelpers.createClass(PageTransition, [{
	    key: "init",
	    value: function init() {
	      var referrer = document.referrer;
	      if (referrer !== '') {
	        var isSameHost = false;
	        var isDifferentPath = false;
	        var isIframeDisabled = false;
	        var previousUrl = new URL(referrer);
	        if (previousUrl) {
	          isSameHost = window.location.host === previousUrl.hostname;
	          isDifferentPath = window.location.pathname !== previousUrl.pathname;
	          isIframeDisabled = previousUrl.searchParams.get('IFRAME') !== 'Y';
	        }
	        if (!isIframeDisabled || !isSameHost || !isDifferentPath) {
	          BX.removeClass(document.body, 'landing-page-transition');
	        }
	      } else {
	        BX.removeClass(document.body, 'landing-page-transition');
	      }
	      document.addEventListener('DOMContentLoaded', function () {
	        setTimeout(function () {
	          BX.removeClass(document.body, "landing-page-transition");
	        }, 300);
	      });
	    }
	  }]);
	  return PageTransition;
	}();

	exports.DiskFile = DiskFile;
	exports.SearchResult = SearchResult;
	exports.TimeStamp = TimeStamp;
	exports.TopPanel = TopPanel;
	exports.PageTransition = PageTransition;

}((this.BX.Landing.Pub = this.BX.Landing.Pub || {}),BX,BX.Landing));
//# sourceMappingURL=script.js.map
