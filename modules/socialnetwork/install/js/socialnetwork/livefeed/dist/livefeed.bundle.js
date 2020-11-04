this.BX = this.BX || {};
(function (exports,main_core,main_core_events,main_popup) {
	'use strict';

	var PinnedPanel = /*#__PURE__*/function () {
	  function PinnedPanel() {
	    babelHelpers.classCallCheck(this, PinnedPanel);
	    this.init();
	  }

	  babelHelpers.createClass(PinnedPanel, [{
	    key: "init",
	    value: function init() {
	      var _this = this;

	      this.initPanel();
	      this.initPosts();
	      this.initEvents();
	      main_core_events.EventEmitter.subscribe('onFrameDataProcessed', function () {
	        _this.initPanel();

	        _this.initPosts();
	      });
	    }
	  }, {
	    key: "initPanel",
	    value: function initPanel() {
	      var pinnedPanelNode = this.getPanelNode();

	      if (!pinnedPanelNode) {
	        return;
	      }

	      main_core.Event.bind(pinnedPanelNode, 'click', function (event) {
	        var likeClicked = event.target.classList.contains('feed-inform-ilike') || event.target.closest('.feed-inform-ilike') !== null;
	        var followClicked = event.target.classList.contains('feed-inform-follow') || event.target.closest('.feed-inform-follow') !== null;
	        var menuClicked = event.target.classList.contains('feed-post-more-link') || event.target.closest('.feed-post-more-link') !== null || event.target.classList.contains('feed-post-right-top-menu');
	        var contentViewClicked = event.target.classList.contains('feed-inform-contentview') || event.target.closest('.feed-inform-contentview') !== null;
	        var pinClicked = event.target.classList.contains('feed-post-pin') || event.target.closest('.feed-post-pin') !== null;
	        var collapseClicked = event.target.classList.contains('feed-post-pinned-link-collapse');
	        var commentsClicked = event.target.classList.contains('feed-inform-comments-pinned') || event.target.closest('.feed-inform-comments-pinned') !== null;
	        var postNode = null;

	        if (event.target.classList.contains('feed-post-block')) {
	          postNode = event.target;
	        } else {
	          postNode = event.target.closest('.feed-post-block');
	        }

	        if (!postNode) {
	          return;
	        }

	        if (postNode.classList.contains('feed-post-block-pinned')) {
	          if (!likeClicked && !followClicked && !menuClicked && !contentViewClicked && !pinClicked) {
	            postNode.classList.remove('feed-post-block-pinned');
	            var menuId = postNode.getAttribute('data-menu-id');

	            if (menuId) {
	              main_popup.MenuManager.destroy(menuId);
	            }

	            var _event = new main_core_events.BaseEvent({
	              compatData: [{
	                rootNode: postNode
	              }],
	              data: {
	                rootNode: postNode
	              }
	            });

	            main_core_events.EventEmitter.emit('BX.Livefeed:recalculateComments', _event);
	          }

	          if (commentsClicked) {
	            var anchorNode = postNode.querySelector('.feed-comments-block a[name=comments]');

	            if (anchorNode) {
	              var position = main_core.Dom.getPosition(anchorNode);
	              window.scrollTo(0, position.top - 200);
	            }
	          }

	          event.stopPropagation();
	          event.preventDefault();
	        } else if (collapseClicked) {
	          postNode.classList.add('feed-post-block-pinned');
	          event.stopPropagation();
	          event.preventDefault();
	        }
	      });
	    }
	  }, {
	    key: "initPosts",
	    value: function initPosts() {
	      var _this2 = this;

	      var postList = document.querySelectorAll('[data-livefeed-post-pinned]');
	      postList.forEach(function (post) {
	        main_core.Event.bind(post, 'click', function (event) {
	          if (!event.target.classList.contains('feed-post-pin')) {
	            return;
	          }

	          var post = event.target.closest('[data-livefeed-id]');

	          if (!post) {
	            return;
	          }

	          var newState = post.getAttribute('data-livefeed-post-pinned') === 'Y' ? 'N' : 'Y';
	          var logId = parseInt(post.getAttribute('data-livefeed-id'));

	          if (logId <= 0) {
	            return;
	          }

	          _this2.changePinned({
	            logId: logId,
	            newState: newState,
	            event: event
	          });
	        });
	      });
	    }
	  }, {
	    key: "initEvents",
	    value: function initEvents() {
	      var _this3 = this;

	      main_core_events.EventEmitter.subscribe('OnUCCommentWasRead', function (event) {
	        var _event$getData = event.getData(),
	            _event$getData2 = babelHelpers.slicedToArray(_event$getData, 3),
	            xmlId = _event$getData2[0],
	            id = _event$getData2[1],
	            options = _event$getData2[2];

	        var _this3$getCommentsDat = _this3.getCommentsData(xmlId),
	            oldValue = _this3$getCommentsDat.oldValue,
	            newValue = _this3$getCommentsDat.newValue;

	        if (!!options.new) {
	          _this3.setCommentsData(xmlId, {
	            newValue: main_core.Type.isInteger(newValue) ? newValue - 1 : 0,
	            oldValue: main_core.Type.isInteger(oldValue) ? oldValue + 1 : 1
	          });
	        }
	      });
	      main_core_events.EventEmitter.subscribe('OnUCCommentWasPulled', function (event) {
	        var _event$getData3 = event.getData(),
	            _event$getData4 = babelHelpers.slicedToArray(_event$getData3, 3),
	            id = _event$getData4[0],
	            data = _event$getData4[1],
	            params = _event$getData4[2];

	        var _id = babelHelpers.slicedToArray(id, 2),
	            xmlId = _id[0],
	            commentId = _id[1];

	        var _this3$getCommentsDat2 = _this3.getCommentsData(xmlId),
	            newValue = _this3$getCommentsDat2.newValue,
	            oldValue = _this3$getCommentsDat2.oldValue,
	            follow = _this3$getCommentsDat2.follow;

	        if (parseInt(params.AUTHOR.ID) !== parseInt(BX.message('USER_ID')) && follow) {
	          _this3.setCommentsData(xmlId, {
	            newValue: main_core.Type.isInteger(newValue) ? newValue + 1 : 1
	          });
	        } else {
	          _this3.setCommentsData(xmlId, {
	            oldValue: main_core.Type.isInteger(oldValue) ? oldValue + 1 : 1
	          });
	        }
	      });
	      main_core_events.EventEmitter.subscribe('OnUCommentWasDeleted', function (event) {
	        var _event$getData5 = event.getData(),
	            _event$getData6 = babelHelpers.slicedToArray(_event$getData5, 3),
	            xmlId = _event$getData6[0],
	            id = _event$getData6[1],
	            data = _event$getData6[2];

	        var _this3$getCommentsDat3 = _this3.getCommentsData(xmlId),
	            oldValue = _this3$getCommentsDat3.oldValue;

	        _this3.setCommentsData(xmlId, {
	          oldValue: main_core.Type.isInteger(oldValue) ? oldValue - 1 : 0
	        });
	      });
	    }
	  }, {
	    key: "changePinned",
	    value: function changePinned(params) {
	      var _this4 = this;

	      var logId = params.logId ? parseInt(params.logId) : 0;
	      var node = params.node ? params.node : null;
	      var event = params.event ? params.event : null;
	      var newState = params.newState ? params.newState : null;

	      if (!logId || !newState) {
	        return;
	      }

	      this.setPostState({
	        node: node ? node : event.target,
	        state: newState
	      });
	      main_core.ajax.runAction('socialnetwork.api.livefeed.logentry.' + (newState === 'Y' ? 'pin' : 'unpin'), {
	        data: {
	          params: {
	            logId: logId
	          }
	        },
	        analyticsLabel: {
	          b24statAction: newState === 'Y' ? 'pinLivefeedEntry' : 'unpinLivefeedEntry'
	        }
	      }).then(function (response) {
	        if (!response.data.success) {
	          _this4.setPostState({
	            node: node ? node : event.target,
	            state: newState === 'Y' ? 'N' : 'Y'
	          });
	        } else {
	          _this4.movePost({
	            node: node ? node : event.target,
	            state: newState
	          });
	        }
	      }, function (response) {
	        _this4.setPostState({
	          node: node ? node : event.target,
	          state: newState === 'Y' ? 'N' : 'Y'
	        });
	      });
	    }
	  }, {
	    key: "setPostState",
	    value: function setPostState(params) {
	      var state = params.state ? params.state : null;
	      var node = params.node ? params.node : null;

	      if (!node || !['Y', 'N'].includes(state)) {
	        return;
	      }

	      var post = node.closest('[data-livefeed-post-pinned]');

	      if (!post) {
	        return;
	      }

	      post.setAttribute('data-livefeed-post-pinned', state);
	      post.classList.remove('feed-post-block-pin-active');
	      post.classList.remove('feed-post-block-pin-inactive');

	      if (state === 'Y') {
	        post.classList.add('feed-post-block-pin-active', state);
	      } else {
	        post.classList.add('feed-post-block-pin-inactive', state);
	      }
	    }
	  }, {
	    key: "getPanelNode",
	    value: function getPanelNode() {
	      return document.querySelector('[data-livefeed-pinned-panel]');
	    }
	  }, {
	    key: "getPinnedData",
	    value: function getPinnedData(params) {
	      var logId = params.logId ? parseInt(params.logId) : 0;

	      if (logId <= 0) {
	        return Promise.reject();
	      }

	      return new Promise(function (resolve, reject) {
	        main_core.ajax.runAction('socialnetwork.api.livefeed.logentry.getPinData', {
	          data: {
	            params: {
	              logId: logId
	            }
	          }
	        }).then(function (response) {
	          resolve(response.data);
	        }, function (response) {
	          reject();
	        });
	      });
	    }
	  }, {
	    key: "movePost",
	    value: function movePost(params) {
	      var state = params.state ? params.state : null;
	      var node = params.node ? params.node : null;

	      if (!node || !['Y', 'N'].includes(state)) {
	        return;
	      }

	      var post = node.closest('[data-livefeed-post-pinned]');

	      if (!post) {
	        return;
	      }

	      var logId = parseInt(post.getAttribute('data-livefeed-id'));

	      if (!logId) {
	        return;
	      }

	      var pinnedPanelNode = this.getPanelNode();

	      if (!pinnedPanelNode) {
	        return;
	      }

	      var postToMove = post.parentNode.classList.contains('feed-item-wrap') ? post.parentNode : post;

	      if (state === 'Y') {
	        this.getPinnedData({
	          logId: logId
	        }).then(function (data) {
	          var pinnedPanelTitleNode = post.querySelector('.feed-post-pinned-title');
	          var pinnedPanelDescriptionNode = post.querySelector('.feed-post-pinned-desc');
	          var pinnedPanelPinNode = post.querySelector('.feed-post-pin');

	          if (pinnedPanelTitleNode) {
	            pinnedPanelTitleNode.innerHTML = data.TITLE;
	          }

	          if (pinnedPanelDescriptionNode) {
	            pinnedPanelDescriptionNode.innerHTML = data.DESCRIPTION;
	          }

	          if (pinnedPanelPinNode) {
	            pinnedPanelPinNode.title = main_core.Loc.getMessage('SONET_EXT_LIVEFEED_PIN_TITLE_Y');
	          }

	          post.classList.add('feed-post-block-pinned');
	          pinnedPanelNode.insertBefore(postToMove, pinnedPanelNode.firstChild);
	        });
	      } else {
	        post.classList.remove('feed-post-block-pinned');
	        pinnedPanelNode.parentNode.insertBefore(postToMove, pinnedPanelNode.nextSibling);
	      }
	    }
	  }, {
	    key: "getCommentsNodes",
	    value: function getCommentsNodes(xmlId) {
	      var result = {
	        follow: true,
	        newNode: null,
	        oldNode: null
	      };

	      if (!main_core.Type.isStringFilled(xmlId)) {
	        return result;
	      }

	      var commentsNode = document.querySelector(".feed-comments-block[data-bx-comments-entity-xml-id=\"".concat(xmlId, "\"]"));

	      if (!commentsNode) {
	        return result;
	      }

	      var postNode = commentsNode.closest('.feed-post-block-pin-active');

	      if (!postNode) {
	        return result;
	      }

	      var newPinnedCommentsNode = postNode.querySelector('.feed-inform-comments-pinned-new');
	      var oldPinnedCommentsNode = postNode.querySelector('.feed-inform-comments-pinned-old');

	      if (!newPinnedCommentsNode || !oldPinnedCommentsNode) {
	        return result;
	      }

	      result.newNode = newPinnedCommentsNode;
	      result.oldNode = oldPinnedCommentsNode;
	      result.follow = commentsNode.getAttribute('data-bx-follow') !== 'N';
	      return result;
	    }
	  }, {
	    key: "getCommentsData",
	    value: function getCommentsData(xmlId) {
	      var result = {
	        newValue: null,
	        oldValue: null
	      };

	      if (!main_core.Type.isStringFilled(xmlId)) {
	        return result;
	      }

	      var _this$getCommentsNode = this.getCommentsNodes(xmlId),
	          newNode = _this$getCommentsNode.newNode,
	          oldNode = _this$getCommentsNode.oldNode,
	          follow = _this$getCommentsNode.follow;

	      result.follow = follow;

	      if (!main_core.Type.isDomNode(newNode) || !main_core.Type.isDomNode(oldNode)) {
	        return result;
	      }

	      var newCommentsValue = 0;
	      var oldCommentsValue = 0;
	      var matches = newNode.innerHTML.match(/\+(\d+)/);

	      if (matches) {
	        newCommentsValue = parseInt(matches[1]);
	      }

	      matches = oldNode.innerHTML.match(/(\d+)/);

	      if (matches) {
	        oldCommentsValue = parseInt(matches[1]);
	      }

	      result.oldValue = oldCommentsValue;
	      result.newValue = newCommentsValue;
	      return result;
	    }
	  }, {
	    key: "setCommentsData",
	    value: function setCommentsData(xmlId, value) {
	      if (!main_core.Type.isStringFilled(xmlId)) {
	        return;
	      }

	      var _this$getCommentsNode2 = this.getCommentsNodes(xmlId),
	          newNode = _this$getCommentsNode2.newNode,
	          oldNode = _this$getCommentsNode2.oldNode;

	      if (!main_core.Type.isDomNode(newNode) || !main_core.Type.isDomNode(oldNode)) {
	        return;
	      }

	      if (main_core.Type.isInteger(value.newValue)) {
	        newNode.innerHTML = "+".concat(value.newValue);

	        if (value.newValue > 0 && !newNode.classList.contains('feed-inform-comments-pinned-new-active')) {
	          newNode.classList.add('feed-inform-comments-pinned-new-active');
	        } else if (value.newValue <= 0 && newNode.classList.contains('feed-inform-comments-pinned-new-active')) {
	          newNode.classList.remove('feed-inform-comments-pinned-new-active');
	        }
	      }

	      if (main_core.Type.isInteger(value.oldValue)) {
	        oldNode.innerHTML = value.oldValue;
	      }
	    }
	  }]);
	  return PinnedPanel;
	}();

	var Feed = /*#__PURE__*/function () {
	  function Feed() {
	    babelHelpers.classCallCheck(this, Feed);
	    this.init();
	    this.entryData = {};
	  }

	  babelHelpers.createClass(Feed, [{
	    key: "init",
	    value: function init() {}
	  }, {
	    key: "changeFavorites",
	    value: function changeFavorites(params) {
	      var _this = this;

	      var logId = params.logId ? parseInt(params.logId) : 0;
	      var event = params.event ? params.event : null;
	      var node = params.node ? params.node : null;
	      var newState = params.newState ? params.newState : null;

	      if (main_core.Type.isStringFilled(node)) {
	        node = document.getElementById(node);
	      }

	      if (!logId) {
	        return;
	      }

	      var menuItem = null;

	      if (event) {
	        menuItem = event.target;

	        if (!menuItem.classList.contains('menu-popup-item-text')) {
	          menuItem = menuItem.querySelector('.menu-popup-item-text');
	        }
	      }

	      var nodeToAdjust = null;

	      if (main_core.Type.isDomNode(node)) {
	        nodeToAdjust = node.classList.contains('feed-post-important-switch') ? node : node.querySelector('.feed-post-important-switch');
	      }

	      if (typeof this.entryData[logId] == 'undefined') {
	        this.entryData[logId] = {};
	      }

	      if (typeof this.entryData[logId].favorites != 'undefined') {
	        newState = this.entryData[logId].favorites ? 'N' : 'Y';
	        this.entryData[logId].favorites = !this.entryData[logId].favorites;
	      } else if (nodeToAdjust) {
	        newState = nodeToAdjust.classList.contains('feed-post-important-switch-active') ? 'N' : 'Y';
	        this.entryData[logId].favorites = newState == 'Y';
	      }

	      if (!newState) {
	        return;
	      }

	      this.adjustFavoritesControlItem(nodeToAdjust, newState);
	      this.adjustFavoritesMenuItem(menuItem, newState);
	      main_core.ajax.runAction('socialnetwork.api.livefeed.changeFavorites', {
	        data: {
	          logId: logId,
	          value: newState
	        },
	        analyticsLabel: {
	          b24statAction: newState == 'Y' ? 'addFavorites' : 'removeFavorites'
	        }
	      }).then(function (response) {
	        if (main_core.Type.isStringFilled(response.data.newValue) && ['Y', 'N'].includes(response.data.newValue)) {
	          _this.entryData[logId].favorites = response.data.newValue == 'Y';
	        }

	        _this.adjustFavoritesControlItem(nodeToAdjust, response.data.newValue);

	        _this.adjustFavoritesMenuItem(menuItem, response.data.newValue);
	      }, function (response) {
	        _this.entryData[logId].favorites = !_this.entryData[logId].favorites;
	      });
	    }
	  }, {
	    key: "adjustFavoritesMenuItem",
	    value: function adjustFavoritesMenuItem(menuItemNode, state) {
	      if (!main_core.Type.isDomNode(menuItemNode) || !['Y', 'N'].includes(state)) {
	        return;
	      }

	      menuItemNode.innerHTML = this.getMenuTitle(state === 'Y');
	    }
	  }, {
	    key: "adjustFavoritesControlItem",
	    value: function adjustFavoritesControlItem(node, state) {
	      if (!main_core.Type.isDomNode(node) || !['Y', 'N'].includes(state)) {
	        return;
	      }

	      node.title = this.getMenuTitle(state === 'Y');

	      if (state == 'Y') {
	        node.classList.add('feed-post-important-switch-active');
	      } else {
	        node.classList.remove('feed-post-important-switch-active');
	      }
	    }
	  }, {
	    key: "getMenuTitle",
	    value: function getMenuTitle(state) {
	      return main_core.Loc.getMessage("SONET_EXT_LIVEFEED_MENU_TITLE_FAVORITES_".concat(state ? 'Y' : 'N'));
	    }
	  }]);
	  return Feed;
	}();

	exports.FeedInstance = null;
	exports.PinnedPanelInstance = null;
	main_core.Event.ready(function () {
	  exports.FeedInstance = new Feed();
	  exports.PinnedPanelInstance = new PinnedPanel();
	});

}((this.BX.Livefeed = this.BX.Livefeed || {}),BX,BX.Event,BX.Main));
//# sourceMappingURL=livefeed.bundle.js.map
