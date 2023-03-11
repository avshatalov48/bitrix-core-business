this.BX = this.BX || {};
(function (exports,ui_buttons,main_popup,main_core,main_core_events,tasks_result) {
	'use strict';

	var Utils = /*#__PURE__*/function () {
	  function Utils() {
	    babelHelpers.classCallCheck(this, Utils);
	  }

	  babelHelpers.createClass(Utils, null, [{
	    key: "setStyle",
	    value: function setStyle(node, styles) {
	      Object.entries(styles).forEach(function (_ref) {
	        var _ref2 = babelHelpers.slicedToArray(_ref, 2),
	            key = _ref2[0],
	            value = _ref2[1];

	        node.style[key] = value;
	      });
	    }
	  }]);
	  return Utils;
	}();

	var _templateObject;

	function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }

	function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }

	var PinnedPanel = /*#__PURE__*/function () {
	  function PinnedPanel() {
	    var _this = this;

	    babelHelpers.classCallCheck(this, PinnedPanel);
	    this["class"] = {
	      pin: 'feed-post-pin',
	      post: 'feed-item-wrap',
	      postHide: 'feed-item-wrap-hide',
	      postComments: 'feed-comments-block',
	      postPinned: 'feed-post-block-pinned',
	      postPinnedHide: 'feed-post-block-pinned-hide',
	      postPinActive: 'feed-post-block-pin-active',
	      postUnfollowed: 'feed-post-block-unfollowed',
	      postExpanding: 'feed-post-block-expand',
	      panelCollapsed: 'feed-pinned-panel-collapsed',
	      panelNonEmpty: 'feed-pinned-panel-nonempty',
	      panelPosts: 'feed-pinned-panel-posts',
	      collapsedPanel: 'feed-post-collapsed-panel',
	      collapsedPanelExpand: 'feed-post-collapsed-panel-right',
	      collapsedPanelCounterPostsValue: 'feed-post-collapsed-panel-count-posts',
	      collapsedPanelCounterComments: 'feed-post-collapsed-panel-box-comments',
	      collapsedPanelCounterCommentsValue: 'feed-post-collapsed-panel-count-comments-value',
	      collapsedPanelCounterCommentsShown: 'feed-post-collapsed-panel-box-shown',
	      collapsedPanelCounterCommentsValueNew: 'feed-inform-comments-pinned-new',
	      collapsedPanelCounterCommentsValueNewValue: 'feed-inform-comments-pinned-new-value',
	      collapsedPanelCounterCommentsValueNewActive: 'feed-inform-comments-pinned-new-active',
	      collapsedPanelCounterCommentsValueOld: 'feed-inform-comments-pinned-old',
	      collapsedPanelCounterCommentsValueAll: 'feed-inform-comments-pinned-all',
	      collapsedPanelShow: 'feed-post-collapsed-panel--show',
	      collapsedPanelHide: 'feed-post-collapsed-panel--hide',
	      cancelPanel: 'feed-post-cancel-pinned-panel',
	      cancelPanelButton: 'feed-post-cancel-pinned-btn',
	      cancelPanelLabel: 'feed-post-cancel-pinned-label'
	    };
	    this.panelInitialized = false;
	    this.postsInitialized = false;
	    this.handlePostClick = this.handlePostClick.bind(this);
	    this.options = {};
	    main_core.Event.ready(function () {
	      /* for detail page without pinned panel */
	      _this.initPosts();
	    });
	  }

	  babelHelpers.createClass(PinnedPanel, [{
	    key: "resetFlags",
	    value: function resetFlags() {
	      this.panelInitialized = false;
	      this.postsInitialized = false;
	    }
	  }, {
	    key: "init",
	    value: function init() {
	      /* for list page in composite mode */
	      this.initPanel();
	      this.initPosts();
	      this.initEvents();
	    }
	  }, {
	    key: "setOptions",
	    value: function setOptions(options) {
	      this.options = _objectSpread(_objectSpread({}, this.options), options);
	    }
	  }, {
	    key: "getOption",
	    value: function getOption(optionName) {
	      return this.options[optionName];
	    }
	  }, {
	    key: "initPanel",
	    value: function initPanel() {
	      var _this2 = this;

	      if (this.panelInitialized) {
	        return;
	      }

	      var pinnedPanelNode = this.getPanelNode();

	      if (!pinnedPanelNode) {
	        return;
	      }

	      this.panelInitialized = true;
	      this.adjustCollapsedPostsPanel();
	      main_core.Event.bind(this.getCollapsedPanelNode(), 'click', function () {
	        var pinnedPanelNode = _this2.getPanelNode();

	        if (!pinnedPanelNode) {
	          return;
	        }

	        var collapsedHeight = pinnedPanelNode.offsetHeight;
	        Utils.setStyle(pinnedPanelNode, {
	          height: collapsedHeight + 'px',
	          transition: 'height .5s'
	        });
	        setTimeout(function () {
	          pinnedPanelNode.style = '';
	        }, 550);

	        _this2.hideCollapsedPanel();
	      });
	      main_core.Event.bind(pinnedPanelNode, 'click', function (event) {
	        var likeClicked = event.target.classList.contains('feed-inform-ilike') || event.target.closest('.feed-inform-ilike') !== null;
	        var followClicked = event.target.classList.contains('feed-inform-follow') || event.target.closest('.feed-inform-follow') !== null;
	        var menuClicked = event.target.classList.contains('feed-post-more-link') || event.target.closest('.feed-post-more-link') !== null || event.target.classList.contains('feed-post-right-top-menu');
	        var contentViewClicked = event.target.classList.contains('feed-inform-contentview') || event.target.closest('.feed-inform-contentview') !== null;
	        var pinClicked = event.target.classList.contains("".concat(_this2["class"].pin)) || event.target.closest(".".concat(_this2["class"].pin)) !== null;
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

	        if (postNode.classList.contains("".concat(_this2["class"].postPinned))) {
	          if (!likeClicked && !followClicked && !menuClicked && !contentViewClicked && !pinClicked) {
	            postNode.classList.remove("".concat(_this2["class"].postPinned));
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
	            var anchorNode = postNode.querySelector(".".concat(_this2["class"].postComments, " a[name=comments]"));

	            if (anchorNode) {
	              var position = main_core.Dom.getPosition(anchorNode);
	              window.scrollTo(0, position.top - 200);
	            }
	          }

	          event.stopPropagation();
	          event.preventDefault();
	        } else if (collapseClicked) {
	          postNode.classList.add("".concat(_this2["class"].postPinned));
	          event.stopPropagation();
	          event.preventDefault();
	        }
	      });
	    }
	  }, {
	    key: "initPosts",
	    value: function initPosts() {
	      var _this3 = this;

	      if (this.postsInitialized) {
	        return;
	      }

	      var postList = document.querySelectorAll('[data-livefeed-post-pinned]');

	      if (postList.length > 0) {
	        this.postsInitialized = true;
	      }

	      Array.from(postList).forEach(function (post) {
	        main_core.Event.unbind(post, 'click', _this3.handlePostClick);
	        main_core.Event.bind(post, 'click', _this3.handlePostClick);
	      });
	    }
	  }, {
	    key: "handlePostClick",
	    value: function handlePostClick(event) {
	      if (!event.target.classList.contains("".concat(this["class"].pin))) {
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

	      this.changePinned({
	        logId: logId,
	        newState: newState,
	        event: event
	      }).then(function () {});
	    }
	  }, {
	    key: "initEvents",
	    value: function initEvents() {
	      var _this4 = this;

	      main_core_events.EventEmitter.subscribe('OnUCCommentWasRead', function (event) {
	        var _event$getData = event.getData(),
	            _event$getData2 = babelHelpers.slicedToArray(_event$getData, 3),
	            xmlId = _event$getData2[0],
	            id = _event$getData2[1],
	            options = _event$getData2[2];

	        var _this4$getCommentsDat = _this4.getCommentsData(xmlId),
	            oldValue = _this4$getCommentsDat.oldValue,
	            newValue = _this4$getCommentsDat.newValue;

	        if (!!options["new"]) {
	          _this4.setCommentsData(xmlId, {
	            newValue: main_core.Type.isInteger(newValue) ? newValue - 1 : 0,
	            oldValue: main_core.Type.isInteger(oldValue) ? oldValue + 1 : 1
	          });
	        }
	      });
	      main_core_events.EventEmitter.incrementMaxListeners('OnUCCommentWasPulled');
	      main_core_events.EventEmitter.subscribe('OnUCCommentWasPulled', function (event) {
	        var _event$getData3 = event.getData(),
	            _event$getData4 = babelHelpers.slicedToArray(_event$getData3, 3),
	            id = _event$getData4[0],
	            data = _event$getData4[1],
	            params = _event$getData4[2];

	        var _id = babelHelpers.slicedToArray(id, 2),
	            xmlId = _id[0],
	            commentId = _id[1];

	        var _this4$getCommentsDat2 = _this4.getCommentsData(xmlId),
	            newValue = _this4$getCommentsDat2.newValue,
	            oldValue = _this4$getCommentsDat2.oldValue,
	            allValue = _this4$getCommentsDat2.allValue,
	            follow = _this4$getCommentsDat2.follow;

	        var commentsData = {
	          allValue: main_core.Type.isInteger(allValue) ? allValue + 1 : 1
	        };

	        if (parseInt(params.AUTHOR.ID) !== parseInt(BX.message('USER_ID'))) {
	          commentsData.newValue = main_core.Type.isInteger(newValue) ? newValue + 1 : 1;
	        } else {
	          commentsData.oldValue = main_core.Type.isInteger(oldValue) ? oldValue + 1 : 1;
	        }

	        _this4.setCommentsData(xmlId, commentsData);
	      });
	      main_core_events.EventEmitter.subscribe('OnUCommentWasDeleted', function (event) {
	        var _event$getData5 = event.getData(),
	            _event$getData6 = babelHelpers.slicedToArray(_event$getData5, 3),
	            xmlId = _event$getData6[0],
	            id = _event$getData6[1],
	            data = _event$getData6[2];

	        var _this4$getCommentsDat3 = _this4.getCommentsData(xmlId),
	            oldValue = _this4$getCommentsDat3.oldValue,
	            allValue = _this4$getCommentsDat3.allValue;

	        _this4.setCommentsData(xmlId, {
	          allValue: main_core.Type.isInteger(allValue) ? allValue - 1 : 0,
	          oldValue: main_core.Type.isInteger(oldValue) ? oldValue - 1 : 0
	        });
	      });
	    }
	  }, {
	    key: "changePinned",
	    value: function changePinned(params) {
	      var _this5 = this;

	      var logId = params.logId ? parseInt(params.logId) : 0;
	      var event = params.event ? params.event : null;
	      var node = params.node ? params.node : null;
	      var newState = params.newState ? params.newState : null;
	      var panelNode = this.getPanelNode();

	      if (!node && !event && logId > 0 && panelNode) {
	        node = panelNode.querySelector(".".concat(this["class"].post, " > [data-livefeed-id=\"").concat(logId, "\"]"));
	      }

	      if (!node && event) {
	        node = event.target;
	      }

	      return new Promise(function (resolve, reject) {
	        if (!!_this5.getOption('pinBlocked') || !node || !newState) {
	          return resolve();
	        }

	        _this5.setPostState({
	          node: node,
	          state: newState
	        });

	        var action = newState === 'Y' ? 'socialnetwork.api.livefeed.logentry.pin' : 'socialnetwork.api.livefeed.logentry.unpin';
	        main_core.ajax.runAction(action, {
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
	            _this5.setPostState({
	              node: node,
	              state: newState === 'Y' ? 'N' : 'Y'
	            });

	            return resolve();
	          } else {
	            _this5.movePost({
	              node: node,
	              state: newState
	            }).then(function () {
	              return resolve();
	            });
	          }
	        }, function (response) {
	          _this5.setPostState({
	            node: node,
	            state: newState === 'Y' ? 'N' : 'Y'
	          });

	          return resolve();
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

	      if (state === 'Y') {
	        post.classList.add("".concat(this["class"].postPinActive));
	      } else {
	        post.classList.remove("".concat(this["class"].postPinActive));
	      }

	      var pin = post.querySelector(".".concat(this["class"].pin));

	      if (pin) {
	        pin.setAttribute('title', main_core.Loc.getMessage("SONET_EXT_LIVEFEED_PIN_TITLE_".concat(state)));
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
	          },
	          headers: [{
	            name: main_core.Loc.getMessage('SONET_EXT_LIVEFEED_AJAX_ENTITY_HEADER_NAME'),
	            value: params.entityValue || ''
	          }, {
	            name: main_core.Loc.getMessage('SONET_EXT_LIVEFEED_AJAX_TOKEN_HEADER_NAME'),
	            value: params.tokenValue || ''
	          }]
	        }).then(function (response) {
	          return resolve(response.data);
	        }, function (response) {
	          return reject();
	        });
	      });
	    }
	  }, {
	    key: "movePost",
	    value: function movePost(params) {
	      var _this6 = this;

	      var state = params.state ? params.state : null;
	      var node = params.node ? params.node : null;
	      return new Promise(function (resolve, reject) {
	        if (!node || !['Y', 'N'].includes(state)) {
	          return resolve();
	        }

	        var post = node.closest('[data-livefeed-post-pinned]');

	        if (!post) {
	          return resolve();
	        }

	        var logId = parseInt(post.getAttribute('data-livefeed-id'));

	        if (!logId) {
	          return resolve();
	        }

	        var pinnedPanelNode = _this6.getPanelNode();

	        if (!pinnedPanelNode) {
	          return resolve();
	        }

	        var postToMove = post.parentNode.classList.contains("".concat(_this6["class"].post)) ? post.parentNode : post;
	        var entityValue = post.getAttribute('data-security-entity-pin');
	        var tokenValue = post.getAttribute('data-security-token-pin');

	        if (state === 'Y') {
	          var originalPostHeight = postToMove.offsetHeight;
	          postToMove.setAttribute('bx-data-height', originalPostHeight);

	          _this6.getPinnedData({
	            logId: logId,
	            entityValue: entityValue,
	            tokenValue: tokenValue
	          }).then(function (data) {
	            var pinnedPanelTitleNode = post.querySelector('.feed-post-pinned-title');
	            var pinnedPanelDescriptionNode = post.querySelector('.feed-post-pinned-desc');
	            var pinnedPanelPinNode = post.querySelector(".".concat(_this6["class"].pin));

	            if (pinnedPanelTitleNode) {
	              pinnedPanelTitleNode.innerHTML = data.TITLE;
	            }

	            if (pinnedPanelDescriptionNode) {
	              pinnedPanelDescriptionNode.innerHTML = data.DESCRIPTION;
	            }

	            if (pinnedPanelPinNode) {
	              pinnedPanelPinNode.title = main_core.Loc.getMessage('SONET_EXT_LIVEFEED_PIN_TITLE_Y');
	            }

	            post.classList.add("".concat(_this6["class"].postPinnedHide));

	            var cancelPinnedPanel = _this6.getCancelPinnedPanel({
	              logId: logId
	            });

	            var anchor = postToMove.nextSibling;
	            anchor.parentNode.insertBefore(cancelPinnedPanel, anchor);

	            _this6.centerCancelPinnedPanelElements({
	              cancelPinnedPanel: cancelPinnedPanel
	            });

	            cancelPinnedPanel.setAttribute('bx-data-height', originalPostHeight);
	            var cancelPanelHeight = cancelPinnedPanel.getAttribute('bx-data-height');
	            Utils.setStyle(cancelPinnedPanel, {
	              height: cancelPanelHeight + 'px'
	            });
	            Utils.setStyle(postToMove, {
	              position: 'absolute',
	              width: '100%',
	              height: originalPostHeight + 'px',
	              backgroundColor: 'transparent',
	              opacity: 0
	            });

	            var panelNode = _this6.getPanelNode();

	            if (panelNode) {
	              _this6.setOptions({
	                panelHeight: panelNode.offsetHeight
	              });
	            } // list.post::hide.start, cancelPanel::show.start


	            setTimeout(function () {
	              postToMove.classList.add("".concat(_this6["class"].postHide));
	              Utils.setStyle(cancelPinnedPanel, {
	                height: '53px'
	              });
	              Utils.setStyle(postToMove, {
	                height: 0,
	                opacity: 0
	              });

	              _this6.setOptions({
	                pinBlocked: true
	              });
	            }, 100); // list.post::hide.end

	            main_core.Event.unbindAll(postToMove, 'transitionend');
	            main_core.Event.bind(postToMove, 'transitionend', function (event) {
	              if (!_this6.checkTransitionProperty(event, 'height')) {
	                return;
	              }

	              main_core.Event.unbindAll(postToMove, 'transitionend');
	              var panelPostsNode = pinnedPanelNode.querySelector(".".concat(_this6["class"].panelPosts));
	              panelPostsNode.insertBefore(postToMove, panelPostsNode.firstChild);

	              _this6.adjustCollapsedPostsPanel();

	              postToMove.classList.remove("".concat(_this6["class"].postHide));
	              post.classList.remove("".concat(_this6["class"].postPinnedHide));

	              _this6.adjustPanel();

	              _this6.showCollapsedPostsPanel(); // pinnedPanel.post::show.start


	              setTimeout(function () {
	                post.classList.add("".concat(_this6["class"].postPinned));
	                Utils.setStyle(postToMove, {
	                  position: '',
	                  width: '',
	                  height: '80px',
	                  backgroundColor: '',
	                  opacity: 1
	                });

	                _this6.setOptions({
	                  pinBlocked: false
	                });

	                setTimeout(function () {
	                  postToMove.classList.remove("".concat(_this6["class"].postHide));
	                  Utils.setStyle(postToMove, {
	                    position: '',
	                    width: '',
	                    height: '',
	                    backgroundColor: '',
	                    opacity: ''
	                  });
	                }, 600); // 600 > transition 0.5
	              }, 300);
	            });
	            return resolve();
	          });
	        } else {
	          var height = postToMove.getAttribute('bx-data-height');
	          var pinnedHeight = postToMove.scrollHeight;
	          Utils.setStyle(postToMove, {
	            transition: ''
	          });
	          var cancelPinnedPanel = document.querySelector(".".concat(_this6["class"].cancelPanel, "[bx-data-log-id=\"").concat(logId, "\"]"));

	          if (main_core.Type.isDomNode(cancelPinnedPanel)) {
	            Utils.setStyle(postToMove, {
	              height: pinnedHeight + 'px'
	            }); // pinnedPanel.post::hide.start, cancelPanel::show.start

	            requestAnimationFrame(function () {
	              postToMove.classList.add("".concat(_this6["class"].postExpanding));
	              cancelPinnedPanel.classList.add("".concat(_this6["class"].postExpanding));
	              Utils.setStyle(postToMove, {
	                opacity: 0,
	                height: 0
	              });
	              Utils.setStyle(cancelPinnedPanel, {
	                opacity: 0,
	                height: 0
	              });
	            });
	            var collapsed = pinnedPanelNode.classList.contains("".concat(_this6["class"].panelCollapsed));

	            if (collapsed) {
	              cancelPinnedPanel.parentNode.insertBefore(postToMove, cancelPinnedPanel.nextSibling);

	              _this6.adjustCollapsedPostsPanel();

	              _this6.adjustPanel();
	            }

	            var showCollapsed = _this6.getCollapsedPanelNode().classList.contains("".concat(_this6["class"].collapsedPanelShow));

	            if (showCollapsed) {
	              _this6.hideCollapsedPostsPanel(); // cancelPanel::show.end


	              main_core.Event.unbindAll(cancelPinnedPanel, 'transitionend');
	              main_core.Event.bind(cancelPinnedPanel, 'transitionend', function (event) {
	                if (!_this6.checkTransitionProperty(event, 'height')) {
	                  return;
	                }

	                Utils.setStyle(postToMove, {
	                  transform: '',
	                  display: 'block'
	                });

	                _this6.animateCancel({
	                  post: post,
	                  postToMove: postToMove,
	                  cancelPinnedPanel: cancelPinnedPanel,
	                  height: height
	                });
	              });
	            } // pinnedPanel.post::hide.end


	            main_core.Event.unbindAll(postToMove, 'transitionend');
	            main_core.Event.bind(postToMove, 'transitionend', function (event) {
	              if (!_this6.checkTransitionProperty(event, 'opacity')) {
	                return;
	              }

	              if (!collapsed) {
	                cancelPinnedPanel.parentNode.insertBefore(postToMove, cancelPinnedPanel.nextSibling);

	                _this6.adjustCollapsedPostsPanel();

	                _this6.adjustPanel();
	              }

	              _this6.animateCancel({
	                post: post,
	                postToMove: postToMove,
	                cancelPinnedPanel: cancelPinnedPanel,
	                height: height
	              });
	            });
	          } else {
	            post.classList.remove("".concat(_this6["class"].postPinned));
	            pinnedPanelNode.parentNode.insertBefore(postToMove, pinnedPanelNode.nextSibling);

	            _this6.adjustPanel();

	            var _originalPostHeight = postToMove.scrollHeight;
	            postToMove.setAttribute('bx-data-height', _originalPostHeight);
	            Utils.setStyle(postToMove, {
	              opacity: 0,
	              height: '80px'
	            }); // list.post::show.start

	            setTimeout(function () {
	              Utils.setStyle(postToMove, {
	                opacity: 1,
	                height: _originalPostHeight + 'px'
	              });
	            }, 100); // list.post::show.end

	            main_core.Event.unbindAll(postToMove, 'transitionend');
	            main_core.Event.bind(postToMove, 'transitionend', function (event) {
	              if (!_this6.checkTransitionProperty(event, 'height')) {
	                return;
	              }

	              Utils.setStyle(postToMove, {
	                height: ''
	              });
	            });
	          }

	          return resolve();
	        }
	      });
	    }
	  }, {
	    key: "animateCancel",
	    value: function animateCancel(_ref) {
	      var _this7 = this;

	      var post = _ref.post,
	          postToMove = _ref.postToMove,
	          cancelPinnedPanel = _ref.cancelPinnedPanel,
	          height = _ref.height;
	      post.classList.remove("".concat(this["class"].postPinned)); // post.list:show.start, cancelPanel::hide.start

	      setTimeout(function () {
	        Utils.setStyle(postToMove, {
	          opacity: 1,
	          height: height + 'px'
	        });
	        Utils.setStyle(cancelPinnedPanel, {
	          height: 0
	        });
	        setTimeout(function () {
	          cancelPinnedPanel.remove();
	        }, 100);
	      }, 100); // post.list:show.end

	      main_core.Event.unbindAll(postToMove, 'transitionend');
	      main_core.Event.bind(postToMove, 'transitionend', function (event) {
	        if (!_this7.checkTransitionProperty(event, 'height')) {
	          return;
	        }

	        post.classList.remove("".concat(_this7["class"].postPinnedHide));
	        Utils.setStyle(postToMove, {
	          marginBottom: '',
	          height: ''
	        });
	        Utils.setStyle(cancelPinnedPanel, {
	          marginBottom: '',
	          height: ''
	        });
	        postToMove.classList.remove("".concat(_this7["class"].postExpanding));
	        cancelPinnedPanel.classList.remove("".concat(_this7["class"].postExpanding));
	      });
	    }
	  }, {
	    key: "getCancelPinnedPanel",
	    value: function getCancelPinnedPanel(params) {
	      var _this8 = this;

	      var logId = params.logId ? parseInt(params.logId) : 0;

	      if (logId <= 0) {
	        return null;
	      }

	      var cancelPinnedPanel = document.querySelector(".".concat(this["class"].cancelPanel, "[bx-data-log-id=\"").concat(logId, "\"]"));

	      if (!main_core.Type.isDomNode(cancelPinnedPanel)) {
	        cancelPinnedPanel = main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"", "\" bx-data-log-id=\"", "\">\n\t\t\t\t\t<div class=\"feed-post-cancel-pinned-panel-inner\">\n\t\t\t\t\t\t<div class=\"feed-post-cancel-pinned-content\">\n\t\t\t\t\t\t\t<span class=\"", "\">", "</span>\n\t\t\t\t\t\t\t<span class=\"feed-post-cancel-pinned-text\">", "</span>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<button class=\"ui-btn ui-btn-light-border ui-btn-round ui-btn-sm ", "\">", "</button>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\t\n\t\t\t\t"])), this["class"].cancelPanel, logId, this["class"].cancelPanelLabel, main_core.Loc.getMessage('SONET_EXT_LIVEFEED_PINNED_CANCEL_TITLE'), main_core.Loc.getMessage('SONET_EXT_LIVEFEED_PINNED_CANCEL_DESCRIPTION'), this["class"].cancelPanelButton, main_core.Loc.getMessage('SONET_EXT_LIVEFEED_PINNED_CANCEL_BUTTON'));
	        main_core.Event.bind(cancelPinnedPanel.querySelector(".".concat(this["class"].cancelPanelButton)), 'click', function () {
	          _this8.changePinned({
	            logId: logId,
	            newState: 'N'
	          }).then(function () {
	            Utils.setStyle(cancelPinnedPanel, {
	              opacity: 0,
	              height: 0
	            });
	          });
	        });
	      }

	      return cancelPinnedPanel;
	    }
	  }, {
	    key: "centerCancelPinnedPanelElements",
	    value: function centerCancelPinnedPanelElements(_ref2) {
	      var cancelPinnedPanel = _ref2.cancelPinnedPanel;

	      if (!main_core.Type.isDomNode(cancelPinnedPanel)) {
	        return;
	      } // cancelPanel::show.start


	      setTimeout(function () {
	        Utils.setStyle(cancelPinnedPanel, {
	          opacity: 1
	        });
	      }, 100);
	      Utils.setStyle(cancelPinnedPanel.querySelector(".".concat(this["class"].cancelPanelLabel)), {
	        marginLeft: cancelPinnedPanel.querySelector(".".concat(this["class"].cancelPanelButton)).getBoundingClientRect().width + 'px'
	      });
	    }
	  }, {
	    key: "getPostsCount",
	    value: function getPostsCount() {
	      var panelNode = this.getPanelNode();
	      return panelNode ? Array.from(panelNode.getElementsByClassName("".concat(this["class"].post))).length : 0;
	    }
	  }, {
	    key: "hidePinnedItems",
	    value: function hidePinnedItems() {
	      var _this9 = this;

	      var pinnedPanelNode = this.getPanelNode();

	      if (!pinnedPanelNode) {
	        return;
	      }

	      Utils.setStyle(pinnedPanelNode, {
	        height: parseInt(this.getOption('panelHeight')) + 'px'
	      });
	      Array.from(pinnedPanelNode.getElementsByClassName("".concat(this["class"].post))).reduce(function (count, item) {
	        count += item.offsetHeight;
	        Utils.setStyle(item, {
	          transition: 'opacity .1s linear, transform .2s .1s linear, height .5s linear'
	        });
	        Utils.setStyle(pinnedPanelNode, {
	          transition: 'height .5s .1s linear'
	        }); // pinnedPanel.post::hide.start

	        requestAnimationFrame(function () {
	          Utils.setStyle(item, {
	            opacity: '0!important',
	            transform: "translateY(-".concat(count, "px)")
	          });
	          Utils.setStyle(pinnedPanelNode, {
	            height: '58px'
	          });
	        }); // pinnedPanel.post::hide.end

	        main_core.Event.unbindAll(item, 'transitionend');
	        main_core.Event.bind(item, 'transitionend', function (event) {
	          if (!_this9.checkTransitionProperty(event, 'transform')) {
	            return;
	          }

	          Utils.setStyle(item, {
	            display: 'none',
	            opacity: '',
	            transform: '',
	            transition: ''
	          });
	          Utils.setStyle(pinnedPanelNode, {
	            transition: ''
	          });
	        });
	        return count;
	      }, 0);
	    }
	  }, {
	    key: "showPinnedItems",
	    value: function showPinnedItems() {
	      var _this10 = this;

	      var pinnedPanelNode = this.getPanelNode();

	      if (!pinnedPanelNode) {
	        return;
	      }

	      Array.from(pinnedPanelNode.getElementsByClassName("".concat(this["class"].post))).map(function (item, currentIndex, originalItemsList) {
	        Utils.setStyle(item, {
	          display: 'block',
	          opacity: 0
	        }); // pinnedPanel.post::show.start

	        requestAnimationFrame(function () {
	          Utils.setStyle(pinnedPanelNode, {
	            height: 84 * (currentIndex + 1) - 4 + 'px'
	          });
	          Utils.setStyle(item, {
	            transform: "translateY(".concat(0, "px)"),
	            opacity: 1
	          });
	        }); // pinnedPanel.post::show.end

	        main_core.Event.unbindAll(item, 'transitionend');
	        main_core.Event.bind(item, 'transitionend', function (event) {
	          if (!_this10.checkTransitionProperty(event, 'transform')) {
	            return;
	          }

	          Utils.setStyle(item, {
	            display: 'block',
	            height: '',
	            transform: ''
	          });
	          Utils.setStyle(pinnedPanelNode, {
	            height: ''
	          });

	          if (currentIndex + 1 === originalItemsList.length) {
	            Utils.setStyle(pinnedPanelNode, {
	              transition: '',
	              height: ''
	            });
	          }
	        });
	      });
	    }
	  }, {
	    key: "animateCollapsedPanel",
	    value: function animateCollapsedPanel() {
	      var _this11 = this;

	      // collapsedPanel::hide.start
	      requestAnimationFrame(function () {
	        var collapsedPanel = _this11.getCollapsedPanelNode();

	        Utils.setStyle(collapsedPanel, {
	          position: 'absolute',
	          top: 0,
	          width: '100%',
	          opacity: 0
	        });
	        collapsedPanel.classList.remove("".concat(_this11["class"].collapsedPanelHide));
	        collapsedPanel.classList.add("".concat(_this11["class"].collapsedPanelShow)); // collapsedPanel::show.start

	        requestAnimationFrame(function () {
	          Utils.setStyle(collapsedPanel, {
	            position: 'relative',
	            opacity: 1
	          });
	        });
	      });
	    }
	  }, {
	    key: "adjustCollapsedPostsPanel",
	    value: function adjustCollapsedPostsPanel() {
	      var _this12 = this;

	      var postsCounter = this.getPostsCount();
	      var postsCounterNode = this.getCollapsedPanelNode().querySelector(".".concat(this["class"].collapsedPanelCounterPostsValue));

	      if (postsCounterNode) {
	        postsCounterNode.innerHTML = parseInt(postsCounter);
	      }

	      var commentsCounterNode = this.getCollapsedPanelNode().querySelector(".".concat(this["class"].collapsedPanelCounterComments));
	      var commentsCounterValueNode = this.getCollapsedPanelNode().querySelector(".".concat(this["class"].collapsedPanelCounterCommentsValue));
	      var panelNode = this.getPanelNode();

	      if (commentsCounterNode && commentsCounterValueNode && panelNode) {
	        var newCommentCounter = Array.from(panelNode.querySelectorAll(".".concat(this["class"].collapsedPanelCounterCommentsValueNewValue))).reduce(function (acc, node) {
	          return acc + (node.closest(".".concat(_this12["class"].postUnfollowed)) ? 0 : parseInt(node.innerHTML));
	        }, 0);
	        commentsCounterValueNode.innerHTML = newCommentCounter;

	        if (newCommentCounter > 0) {
	          commentsCounterNode.classList.add("".concat(this["class"].collapsedPanelCounterCommentsShown));
	        } else {
	          commentsCounterNode.classList.remove("".concat(this["class"].collapsedPanelCounterCommentsShown));
	        }
	      }
	    }
	  }, {
	    key: "adjustPanel",
	    value: function adjustPanel() {
	      var _this13 = this;

	      var panelNode = this.getPanelNode();

	      if (!panelNode) {
	        return;
	      }

	      setTimeout(function () {
	        if (_this13.getPostsCount() > 0) {
	          panelNode.classList.add("".concat(_this13["class"].panelNonEmpty));
	        } else {
	          panelNode.classList.remove("".concat(_this13["class"].panelNonEmpty));
	        }
	      }, 0);
	    }
	  }, {
	    key: "showCollapsedPostsPanel",
	    value: function showCollapsedPostsPanel() {
	      if (this.getPostsCount() >= main_core.Loc.getMessage('SONET_EXT_LIVEFEED_COLLAPSED_PINNED_PANEL_ITEMS_LIMIT')) {
	        this.showCollapsedPanel();
	        this.hidePinnedItems();
	      }
	    }
	  }, {
	    key: "hideCollapsedPostsPanel",
	    value: function hideCollapsedPostsPanel() {
	      if (this.getPostsCount() < main_core.Loc.getMessage('SONET_EXT_LIVEFEED_COLLAPSED_PINNED_PANEL_ITEMS_LIMIT')) {
	        this.getPanelNode().classList.remove("".concat(this["class"].panelCollapsed));
	        this.removeCollapsedPanel();
	        this.showPinnedItems();
	      }
	    }
	  }, {
	    key: "showCollapsedPanel",
	    value: function showCollapsedPanel() {
	      this.getPanelNode().classList.add("".concat(this["class"].panelCollapsed));
	      this.animateCollapsedPanel();
	    }
	  }, {
	    key: "hideCollapsedPanel",
	    value: function hideCollapsedPanel() {
	      this.getPanelNode().classList.remove("".concat(this["class"].panelCollapsed));
	      this.showPinnedItems();
	      this.removeCollapsedPanel();
	    }
	  }, {
	    key: "removeCollapsedPanel",
	    value: function removeCollapsedPanel() {
	      var collapsedPanel = this.getCollapsedPanelNode();
	      Utils.setStyle(collapsedPanel, {
	        position: 'absolute',
	        top: 0,
	        width: '100%'
	      });
	      collapsedPanel.classList.remove("".concat(this["class"].collapsedPanelShow));
	      collapsedPanel.classList.add("".concat(this["class"].collapsedPanelHide));
	    }
	  }, {
	    key: "getCommentsNodes",
	    value: function getCommentsNodes(xmlId) {
	      var result = {
	        follow: true,
	        newNode: null,
	        newValueNode: null,
	        oldNode: null,
	        allNode: null
	      };

	      if (!main_core.Type.isStringFilled(xmlId)) {
	        return result;
	      }

	      var commentsNode = document.querySelector(".".concat(this["class"].postComments, "[data-bx-comments-entity-xml-id=\"").concat(xmlId, "\"]"));

	      if (!commentsNode) {
	        return result;
	      }

	      var postNode = commentsNode.closest(".".concat(this["class"].postPinActive));

	      if (!postNode) {
	        return result;
	      }

	      var newPinnedCommentsNode = postNode.querySelector(".".concat(this["class"].collapsedPanelCounterCommentsValueNew));
	      var newValuePinnedCommentsNode = postNode.querySelector(".".concat(this["class"].collapsedPanelCounterCommentsValueNewValue));
	      var oldPinnedCommentsNode = postNode.querySelector(".".concat(this["class"].collapsedPanelCounterCommentsValueOld));
	      var allPinnedCommentsNode = postNode.querySelector(".".concat(this["class"].collapsedPanelCounterCommentsValueAll));

	      if (!newPinnedCommentsNode || !newValuePinnedCommentsNode || !oldPinnedCommentsNode || !allPinnedCommentsNode) {
	        return result;
	      }

	      result.newNode = newPinnedCommentsNode;
	      result.newValueNode = newValuePinnedCommentsNode;
	      result.oldNode = oldPinnedCommentsNode;
	      result.allNode = allPinnedCommentsNode;
	      result.follow = commentsNode.getAttribute('data-bx-follow') !== 'N';
	      return result;
	    }
	  }, {
	    key: "getCommentsData",
	    value: function getCommentsData(xmlId) {
	      var result = {
	        newValue: null,
	        oldValue: null,
	        allValue: null
	      };

	      if (!main_core.Type.isStringFilled(xmlId)) {
	        return result;
	      }

	      var _this$getCommentsNode = this.getCommentsNodes(xmlId),
	          newValueNode = _this$getCommentsNode.newValueNode,
	          oldNode = _this$getCommentsNode.oldNode,
	          allNode = _this$getCommentsNode.allNode,
	          follow = _this$getCommentsNode.follow;

	      result.follow = follow;

	      if (!main_core.Type.isDomNode(newValueNode) || !main_core.Type.isDomNode(oldNode)) {
	        return result;
	      }

	      var newCommentsValue = 0;
	      var oldCommentsValue = 0;
	      var allCommentsValue = 0;
	      var matches = newValueNode.innerHTML.match(/(\d+)/);

	      if (matches) {
	        newCommentsValue = parseInt(matches[1]);
	      }

	      matches = oldNode.innerHTML.match(/(\d+)/);

	      if (matches) {
	        oldCommentsValue = parseInt(matches[1]);
	      }

	      matches = allNode.innerHTML.match(/(\d+)/);

	      if (matches) {
	        allCommentsValue = parseInt(matches[1]);
	      }

	      result.oldValue = oldCommentsValue;
	      result.newValue = newCommentsValue;
	      result.allValue = allCommentsValue;
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
	          newValueNode = _this$getCommentsNode2.newValueNode,
	          oldNode = _this$getCommentsNode2.oldNode,
	          allNode = _this$getCommentsNode2.allNode;

	      if (!main_core.Type.isDomNode(newNode) || !main_core.Type.isDomNode(newValueNode) || !main_core.Type.isDomNode(oldNode) || !main_core.Type.isDomNode(allNode)) {
	        return;
	      }

	      if (main_core.Type.isInteger(value.newValue)) {
	        newValueNode.innerHTML = "".concat(value.newValue);

	        if (value.newValue > 0 && !newNode.classList.contains("".concat(this["class"].collapsedPanelCounterCommentsValueNewActive))) {
	          newNode.classList.add("".concat(this["class"].collapsedPanelCounterCommentsValueNewActive));
	        } else if (value.newValue <= 0 && newNode.classList.contains("".concat(this["class"].collapsedPanelCounterCommentsValueNewActive))) {
	          newNode.classList.remove("".concat(this["class"].collapsedPanelCounterCommentsValueNewActive));
	        }
	      }

	      if (main_core.Type.isInteger(value.oldValue)) {
	        oldNode.innerHTML = value.oldValue;
	      }

	      if (main_core.Type.isInteger(value.allValue)) {
	        allNode.innerHTML = value.allValue;
	      }

	      this.adjustCollapsedPostsPanel();
	    }
	  }, {
	    key: "getCollapsedPanelNode",
	    value: function getCollapsedPanelNode() {
	      return this.getPanelNode().querySelector(".".concat(this["class"].collapsedPanel));
	    }
	  }, {
	    key: "checkTransitionProperty",
	    value: function checkTransitionProperty(event, propertyName) {
	      return event.propertyName === propertyName;
	    }
	  }]);
	  return PinnedPanel;
	}();

	var _templateObject$1, _templateObject2, _templateObject3;
	var TaskCreator = /*#__PURE__*/function () {
	  function TaskCreator() {
	    babelHelpers.classCallCheck(this, TaskCreator);
	    this.initEvents();
	  }

	  babelHelpers.createClass(TaskCreator, [{
	    key: "initEvents",
	    value: function initEvents() {
	      main_core_events.EventEmitter.subscribe('tasksTaskEvent', function (event) {
	        var _event$getCompatData = event.getCompatData(),
	            _event$getCompatData2 = babelHelpers.slicedToArray(_event$getCompatData, 2),
	            type = _event$getCompatData2[0],
	            data = _event$getCompatData2[1];

	        if (type !== 'ADD' || !main_core.Type.isPlainObject(data.options) || !main_core.Type.isBoolean(data.options.STAY_AT_PAGE) || data.options.STAY_AT_PAGE) {
	          return;
	        }

	        TaskCreator.signedFiles = null;
	      });
	      main_core_events.EventEmitter.subscribe('SidePanel.Slider:onCloseComplete', function (event) {
	        var sliderInstance = event.getTarget();

	        if (!sliderInstance) {
	          return;
	        }

	        var sliderUrl = sliderInstance.getUrl();

	        if (!main_core.Type.isStringFilled(sliderUrl) || sliderUrl !== TaskCreator.sliderUrl || !main_core.Type.isStringFilled(TaskCreator.signedFiles)) {
	          return;
	        }

	        main_core.ajax.runAction('intranet.controlbutton.clearNewTaskFiles', {
	          data: {
	            signedFiles: TaskCreator.signedFiles
	          }
	        }).then(function () {
	          TaskCreator.signedFiles = null;
	        });
	      });
	    }
	  }], [{
	    key: "create",
	    value: function create(params) {
	      var _this = this;

	      if (main_core.Loc.getMessage('SONET_EXT_LIVEFEED_INTRANET_INSTALLED') === 'Y') {
	        main_core.ajax.runAction('intranet.controlbutton.getTaskLink', {
	          data: {
	            entityType: params.entityType,
	            entityId: params.entityId,
	            postEntityType: main_core.Type.isStringFilled(params.postEntityType) ? params.postEntityType : params.entityType,
	            entityData: {}
	          }
	        }).then(function (response) {
	          if (!main_core.Type.isStringFilled(response.data.SUFFIX)) {
	            response.data.SUFFIX = '';
	          }

	          var requestData = response.data;
	          requestData.DESCRIPTION = _this.formatTaskDescription(requestData.DESCRIPTION, requestData.URL, params.entityType, requestData.SUFFIX);

	          if (parseInt(params.parentTaskId) > 0) {
	            requestData.PARENT_ID = parseInt(params.parentTaskId);
	          }

	          if (main_core.Type.isStringFilled(requestData.UF_TASK_WEBDAV_FILES_SIGN)) {
	            _this.signedFiles = requestData.UF_TASK_WEBDAV_FILES_SIGN;
	          }

	          _this.sliderUrl = response.data.link;
	          BX.SidePanel.Instance.open(response.data.link, {
	            requestMethod: 'post',
	            requestParams: requestData,
	            cacheable: false
	          });
	        });
	      } else {
	        this.createTaskPopup = new main_popup.Popup('BXCTP', null, {
	          autoHide: false,
	          zIndex: 0,
	          offsetLeft: 0,
	          offsetTop: 0,
	          overlay: false,
	          lightShadow: true,
	          closeIcon: {
	            right: '12px',
	            top: '10px'
	          },
	          draggable: {
	            restrict: true
	          },
	          closeByEsc: false,
	          contentColor: 'white',
	          contentNoPaddings: true,
	          buttons: [],
	          content: main_core.Tag.render(_templateObject$1 || (_templateObject$1 = babelHelpers.taggedTemplateLiteral(["<div id=\"BXCTP_content\" class=\"", "\"></div>"])), this.cssClass.popupContent),
	          events: {
	            onAfterPopupShow: function onAfterPopupShow() {
	              _this.createTaskSetContent(main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["<div class=\"", "\">", "</div>"])), _this.cssClass.popupTitle, main_core.Loc.getMessage('SONET_EXT_LIVEFEED_CREATE_TASK_WAIT')));

	              main_core.ajax.runAction('socialnetwork.api.livefeed.getRawEntryData', {
	                data: {
	                  params: {
	                    entityType: params.entityType,
	                    entityId: params.entityId,
	                    logId: main_core.Type.isNumber(params.logId) ? params.logId : null,
	                    additionalParams: {
	                      getSonetGroupAvailable: 'Y',
	                      getLivefeedUrl: 'Y',
	                      checkPermissions: {
	                        feature: 'tasks',
	                        operation: 'create_tasks'
	                      }
	                    }
	                  }
	                }
	              }).then(function (response) {
	                var entryTitle = main_core.Type.isStringFilled(response.data.TITLE) ? response.data.TITLE : '';
	                var entryDescription = main_core.Type.isStringFilled(response.data.DESCRIPTION) ? response.data.DESCRIPTION : '';
	                var entryDiskObjects = main_core.Type.isPlainObject(response.data.DISK_OBJECTS) ? response.data.DISK_OBJECTS : [];
	                var entryUrl = main_core.Type.isStringFilled(response.data.LIVEFEED_URL) ? response.data.LIVEFEED_URL : '';
	                var entrySuffix = main_core.Type.isStringFilled(response.data.SUFFIX) ? response.data.SUFFIX : '';
	                var groupsAvailable = main_core.Type.isPlainObject(response.data.GROUPS_AVAILABLE) ? response.data.GROUPS_AVAILABLE : [];
	                var logId = !main_core.Type.isUndefined(response.data.LOG_ID) ? parseInt(response.data.LOG_ID) : 0;

	                if ((main_core.Type.isStringFilled(entryTitle) || main_core.Type.isStringFilled(entryDescription)) && main_core.Type.isStringFilled(entryUrl)) {
	                  var taskDescription = _this.formatTaskDescription(entryDescription, entryUrl, params.entityType, entrySuffix);

	                  var taskData = {
	                    TITLE: entryTitle,
	                    DESCRIPTION: taskDescription,
	                    RESPONSIBLE_ID: main_core.Loc.getMessage('USER_ID'),
	                    CREATED_BY: main_core.Loc.getMessage('USER_ID'),
	                    UF_TASK_WEBDAV_FILES: entryDiskObjects
	                  };
	                  var sonetGroupIdList = [];

	                  for (var _i = 0, _Object$entries = Object.entries(groupsAvailable); _i < _Object$entries.length; _i++) {
	                    var _Object$entries$_i = babelHelpers.slicedToArray(_Object$entries[_i], 2),
	                        key = _Object$entries$_i[0],
	                        value = _Object$entries$_i[1];

	                    sonetGroupIdList.push(value);
	                  }

	                  if (sonetGroupIdList.length == 1) {
	                    taskData.GROUP_ID = parseInt(sonetGroupIdList[0]);
	                  }

	                  if (parseInt(params.entityType) > 0) {
	                    taskData.PARENT_ID = parseInt(params.entityType);
	                  }

	                  main_core.ajax.runComponentAction('bitrix:tasks.task', 'legacyAdd', {
	                    mode: 'class',
	                    data: {
	                      data: taskData
	                    }
	                  }).then(function (response) {
	                    var resultData = response.data;

	                    _this.createTaskSetContentSuccess(resultData.DATA.ID);

	                    main_core.ajax.runAction('socialnetwork.api.livefeed.createEntityComment', {
	                      data: {
	                        params: {
	                          postEntityType: main_core.Type.isStringFilled(params.postEntityType) ? params.postEntityType : params.entityType,
	                          sourceEntityType: params.entityType,
	                          sourceEntityId: params.entityId,
	                          entityType: 'TASK',
	                          entityId: resultData.DATA.ID,
	                          logId: main_core.Type.isNumber(params.logId) ? params.logId : logId > 0 ? logId : null
	                        }
	                      }
	                    }).then(function () {}, function () {});
	                  }, function (response) {
	                    if (response.errors && response.errors.length) {
	                      var errors = [];
	                      response.errors.forEach(function (error) {
	                        errors.push(error.message);
	                      });

	                      _this.createTaskSetContentFailure(errors);
	                    }
	                  });
	                } else {
	                  _this.createTaskSetContentFailure([main_core.Loc.getMessage('SONET_EXT_LIVEFEED_CREATE_TASK_ERROR_GET_DATA')]);
	                }
	              }, function () {
	                _this.createTaskSetContentFailure([main_core.Loc.getMessage('SONET_EXT_LIVEFEED_CREATE_TASK_ERROR_GET_DATA')]);
	              });
	            },
	            onPopupClose: function onPopupClose() {
	              _this.createTaskPopup.destroy();
	            }
	          }
	        });
	        this.createTaskPopup.show();
	      }
	    }
	  }, {
	    key: "createTaskSetContentSuccess",
	    value: function createTaskSetContentSuccess(taskId) {
	      var taskLink = main_core.Loc.getMessage('SONET_EXT_LIVEFEED_CREATE_TASK_PATH').replace('#user_id#', main_core.Loc.getMessage('USER_ID')).replace('#task_id#', taskId);
	      this.createTaskPopup.destroy();
	      window.top.BX.UI.Notification.Center.notify({
	        content: main_core.Loc.getMessage('SONET_EXT_LIVEFEED_CREATE_TASK_SUCCESS_TITLE'),
	        actions: [{
	          title: main_core.Loc.getMessage('SONET_EXT_LIVEFEED_CREATE_TASK_VIEW'),
	          events: {
	            click: function click(event, balloon, action) {
	              balloon.close();
	              window.top.BX.SidePanel.Instance.open(taskLink);
	            }
	          }
	        }]
	      });
	    }
	  }, {
	    key: "createTaskSetContentFailure",
	    value: function createTaskSetContentFailure(errors) {
	      this.createTaskSetContent(main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["<div>\n\t\t\t<div class=\"", "\">", "</div>\n\t\t\t<div class=\"", "\">", "</div>\n\t\t</div>"])), this.cssClass.popupTitle, main_core.Loc.getMessage('SONET_EXT_LIVEFEED_CREATE_TASK_FAILURE_TITLE'), this.cssClass.popupDescription, errors.join('<br>')));
	    }
	  }, {
	    key: "createTaskSetContent",
	    value: function createTaskSetContent(contentNode) {
	      var containerNode = document.getElementById('BXCTP_content');

	      if (!containerNode) {
	        return;
	      }

	      main_core.Dom.clean(containerNode);
	      containerNode.appendChild(contentNode);
	    }
	  }, {
	    key: "formatTaskDescription",
	    value: function formatTaskDescription(taskDescription, livefeedUrl, entityType, suffix) {
	      var result = taskDescription;
	      suffix = main_core.Type.isStringFilled(suffix) ? "_".concat(suffix) : '';

	      if (!!livefeedUrl && !!entityType && livefeedUrl.length > 0) {
	        result += "\n\n" + main_core.Loc.getMessage("SONET_EXT_COMMENTAUX_CREATE_TASK_".concat(entityType).concat(suffix)).replace('#A_BEGIN#', "[URL=".concat(livefeedUrl, "]")).replace('#A_END#', '[/URL]');
	      }

	      return result;
	    }
	  }]);
	  return TaskCreator;
	}();
	babelHelpers.defineProperty(TaskCreator, "createTaskPopup", null);
	babelHelpers.defineProperty(TaskCreator, "cssClass", {
	  popupContent: 'feed-create-task-popup-content',
	  popupTitle: 'feed-create-task-popup-title',
	  popupDescription: 'feed-create-task-popup-description'
	});
	babelHelpers.defineProperty(TaskCreator, "signedFiles", null);
	babelHelpers.defineProperty(TaskCreator, "sliderUrl", '');

	var _templateObject$2;

	var Post$$1 = /*#__PURE__*/function () {
	  function Post$$1() {
	    babelHelpers.classCallCheck(this, Post$$1);
	  }

	  babelHelpers.createClass(Post$$1, null, [{
	    key: "showBackgroundWarning",
	    value: function showBackgroundWarning(_ref) {
	      var urlToEdit = _ref.urlToEdit,
	          menuPopupWindow = _ref.menuPopupWindow;
	      var content = main_core.Tag.render(_templateObject$2 || (_templateObject$2 = babelHelpers.taggedTemplateLiteral(["<div>", "</div>"])), main_core.Loc.getMessage('SONET_EXT_LIVEFEED_POST_BACKGROUND_EDIT_WARNING_DESCRIPTION'));
	      var dialog = new main_popup.Popup('backgroundWarning', null, {
	        autoHide: true,
	        closeByEsc: true,
	        offsetLeft: 0,
	        offsetTop: 0,
	        draggable: true,
	        bindOnResize: false,
	        titleBar: main_core.Loc.getMessage('SONET_EXT_LIVEFEED_POST_BACKGROUND_EDIT_WARNING_TITLE'),
	        closeIcon: true,
	        className: 'sonet-livefeed-popup-warning',
	        content: content,
	        events: {},
	        cacheable: false,
	        buttons: [new ui_buttons.Button({
	          text: main_core.Loc.getMessage('SONET_EXT_LIVEFEED_POST_BACKGROUND_EDIT_WARNING_BUTTON_SUBMIT'),
	          className: 'ui-btn ui-btn-primary',
	          events: {
	            click: function click() {
	              window.location = urlToEdit;
	              dialog.close();

	              if (menuPopupWindow) {
	                menuPopupWindow.close();
	              }
	            }
	          }
	        }), new ui_buttons.Button({
	          text: main_core.Loc.getMessage('SONET_EXT_LIVEFEED_POST_BACKGROUND_EDIT_WARNING_BUTTON_CANCEL'),
	          className: 'ui-btn ui-btn-light',
	          events: {
	            click: function click() {
	              dialog.close();

	              if (menuPopupWindow) {
	                menuPopupWindow.close();
	              }
	            }
	          }
	        })]
	      });
	      dialog.show();
	      return false;
	    }
	  }, {
	    key: "showMenu",
	    value: function showMenu(params) {
	      var _this = this;

	      if (!main_core.Type.isPlainObject(params)) {
	        params = {};
	      }

	      var menuElement = params.menuElement;
	      var ind = params.ind;
	      var menuId = this.getMenuId(ind);
	      main_popup.MenuManager.destroy(menuId);
	      var log_id = !main_core.Type.isUndefined(params.log_id) ? parseInt(params.log_id) : 0;

	      if (log_id <= 0) {
	        log_id = parseInt(menuElement.getAttribute('data-log-entry-log-id'));
	      }

	      if (log_id <= 0) {
	        return false;
	      }

	      var bFavorites = params.bFavorites;

	      if (main_core.Type.isUndefined(bFavorites)) {
	        bFavorites = menuElement.getAttribute('data-log-entry-favorites') === 'Y';
	      }

	      var arMenuItemsAdditional = params.arMenuItemsAdditional;

	      if (main_core.Type.isUndefined(arMenuItemsAdditional)) {
	        arMenuItemsAdditional = menuElement.getAttribute('data-bx-items');

	        try {
	          arMenuItemsAdditional = JSON.parse(arMenuItemsAdditional);

	          if (!main_core.Type.isPlainObject(arMenuItemsAdditional)) {
	            arMenuItemsAdditional = {};
	          }
	        } catch (e) {
	          arMenuItemsAdditional = {};
	        }
	      }

	      var bindElement = params.bindElement;
	      var itemPinned = null;
	      var pinnedPostNode = bindElement.closest('[data-livefeed-post-pinned]');

	      if (pinnedPostNode) {
	        var pinnedState = pinnedPostNode.getAttribute('data-livefeed-post-pinned') === 'Y';
	        itemPinned = {
	          text: pinnedState ? main_core.Loc.getMessage('SONET_EXT_LIVEFEED_MENU_TITLE_PINNED_Y') : main_core.Loc.getMessage('SONET_EXT_LIVEFEED_MENU_TITLE_PINNED_N'),
	          className: 'menu-popup-no-icon',
	          onclick: function onclick(e) {
	            PinnedPanelInstance.changePinned({
	              logId: log_id,
	              newState: pinnedState ? 'N' : 'Y',
	              event: e,
	              node: bindElement
	            });
	            main_popup.MenuManager.getMenuById(_this.getMenuId(ind)).popupWindow.close();
	            e.preventDefault();
	          }
	        };
	      }

	      var itemFavorites = main_core.Loc.getMessage('sonetLbUseFavorites') !== 'N' ? {
	        text: bFavorites ? main_core.Loc.getMessage('SONET_EXT_LIVEFEED_MENU_TITLE_FAVORITES_Y') : main_core.Loc.getMessage('SONET_EXT_LIVEFEED_MENU_TITLE_FAVORITES_N'),
	        className: 'menu-popup-no-icon',
	        onclick: function onclick(e) {
	          __logChangeFavorites(log_id, "log_entry_favorites_".concat(log_id), bFavorites ? 'N' : 'Y', true, e);

	          e.preventDefault();
	          e.stopPropagation();
	        }
	      } : null;
	      var arItems = [itemPinned, itemFavorites, main_core.Type.isStringFilled(menuElement.getAttribute('data-log-entry-url')) ? {
	        html: "<span id=\"".concat(menuId, "-href-text\">").concat(main_core.Loc.getMessage('sonetLMenuHref'), "</span>"),
	        className: 'menu-popup-no-icon feed-entry-popup-menu feed-entry-popup-menu-href',
	        href: menuElement.getAttribute('data-log-entry-url')
	      } : null, main_core.Type.isStringFilled(menuElement.getAttribute('data-log-entry-url')) ? {
	        html: "<span id=\"".concat(menuId, "-link-text\">").concat(main_core.Loc.getMessage('sonetLMenuLink'), "</span>") + "<span id=\"".concat(menuId, "-link-icon-animate\" class=\"post-menu-link-icon-wrap\">") + "<span class=\"post-menu-link-icon\" id=\"".concat(menuId, "-link-icon-done\" style=\"display: none;\">") + '</span>' + '</span>',
	        className: 'menu-popup-no-icon feed-entry-popup-menu feed-entry-popup-menu-link',
	        onclick: function onclick(e) {
	          var menuItemText = document.getElementById("".concat(menuId, "-link-text"));
	          var menuItemIconDone = document.getElementById("".concat(menuId, "-link-icon-done"));

	          if (BX.clipboard.isCopySupported()) {
	            if (menuItemText && menuItemText.getAttribute('data-block-click') === 'Y') {
	              return;
	            }

	            BX.clipboard.copy(menuElement.getAttribute('data-log-entry-url'));

	            if (menuItemText && menuItemIconDone) {
	              menuItemIconDone.style.display = 'inline-block';
	              document.getElementById("".concat(menuId, "-link-icon-animate")).classList.remove('post-menu-link-icon-animate');
	              main_core.Dom.adjust(document.getElementById("".concat(menuId, "-link-text")), {
	                attrs: {
	                  'data-block-click': 'Y'
	                }
	              });
	              setTimeout(function () {
	                document.getElementById("".concat(menuId, "-link-icon-animate")).classList.add('post-menu-link-icon-animate');
	              }, 1);
	              setTimeout(function () {
	                main_core.Dom.adjust(document.getElementById("".concat(menuId, "-link-text")), {
	                  attrs: {
	                    'data-block-click': 'N'
	                  }
	                });
	              }, 500);
	            }

	            return;
	          }

	          var it = e.currentTarget;
	          var height = parseInt(!!it.getAttribute('bx-height') ? it.getAttribute('bx-height') : it.offsetHeight);

	          if (it.getAttribute('bx-status') !== 'shown') {
	            it.setAttribute('bx-status', 'shown');
	            var node = document.getElementById("".concat(menuId, "-link-text"));

	            if (!document.getElementById("".concat(menuId, "-link")) && !!node) {
	              var pos = BX.pos(node);
	              var pos2 = BX.pos(node.parentNode);
	              var pos3 = BX.pos(node.closest('.menu-popup-item'));
	              pos.height = pos2.height - 1;
	              main_core.Dom.adjust(it, {
	                attrs: {
	                  'bx-height': it.offsetHeight
	                },
	                style: {
	                  overflow: 'hidden',
	                  display: 'block'
	                },
	                children: [main_core.Dom.create('BR'), main_core.Dom.create('DIV', {
	                  attrs: {
	                    id: "".concat(menuId, "-link")
	                  },
	                  children: [main_core.Dom.create('SPAN', {
	                    attrs: {
	                      className: 'menu-popup-item-left'
	                    }
	                  }), main_core.Dom.create('SPAN', {
	                    attrs: {
	                      className: 'menu-popup-item-icon'
	                    }
	                  }), main_core.Dom.create('SPAN', {
	                    attrs: {
	                      className: 'menu-popup-item-text'
	                    },
	                    children: [main_core.Dom.create('INPUT', {
	                      attrs: {
	                        id: "".concat(menuId, "-link-input"),
	                        type: 'text',
	                        value: menuElement.getAttribute('data-log-entry-url')
	                      },
	                      style: {
	                        height: "".concat(pos.height, "px"),
	                        width: "".concat(pos3.width - 21, "px")
	                      },
	                      events: {
	                        click: function click(e) {
	                          e.currentTarget.select();
	                          e.stopPropagation();
	                          e.preventDefault();
	                        }
	                      }
	                    })]
	                  })]
	                }), main_core.Dom.create('SPAN', {
	                  attrs: {
	                    className: 'menu-popup-item-right'
	                  }
	                })]
	              });
	              Event.bind(document.getElementById("".concat(menuId, "-link-input")), 'click', function (e) {
	                e.currentTarget.select();
	                e.preventDefault();
	                e.stopPropagation();
	              });
	            }

	            new BX.fx({
	              time: 0.2,
	              step: 0.05,
	              type: 'linear',
	              start: height,
	              finish: height * 2,
	              callback: function (height) {
	                this.style.height = "".concat(height, "px");
	              }.bind(it)
	            }).start();
	            BX.fx.show(document.getElementById("".concat(menuId, "-link")), 0.2);
	            document.getElementById("".concat(menuId, "-link-input")).select();
	          } else {
	            it.setAttribute('bx-status', 'hidden');
	            new BX.fx({
	              time: 0.2,
	              step: 0.05,
	              type: 'linear',
	              start: it.offsetHeight,
	              finish: height,
	              callback: function (height) {
	                this.style.height = "".concat(height, "px");
	              }.bind(it)
	            }).start();
	            BX.fx.hide(document.getElementById("".concat(menuId, "-link")), 0.2);
	          }
	        }
	      } : null, main_core.Loc.getMessage('sonetLCanDelete') === 'Y' ? {
	        text: main_core.Loc.getMessage('sonetLMenuDelete'),
	        className: 'menu-popup-no-icon',
	        onclick: function onclick(e) {
	          if (confirm(main_core.Loc.getMessage('sonetLMenuDeleteConfirm'))) {
	            FeedInstance["delete"]({
	              logId: log_id,
	              nodeId: "log-entry-".concat(log_id),
	              ind: ind
	            });
	          }

	          e.stopPropagation();
	          e.preventDefault();
	        }
	      } : null, menuElement.getAttribute('data-log-entry-createtask') === 'Y' ? {
	        text: main_core.Loc.getMessage('sonetLMenuCreateTask'),
	        className: 'menu-popup-no-icon',
	        onclick: function onclick(e) {
	          TaskCreator.create({
	            entryEntityType: menuElement.getAttribute('data-log-entry-entity-type'),
	            entityType: menuElement.getAttribute('data-log-entry-entity-type'),
	            entityId: menuElement.getAttribute('data-log-entry-entity-id'),
	            logId: parseInt(menuElement.getAttribute('data-log-entry-log-id'))
	          });
	          main_popup.MenuManager.getMenuById(_this.getMenuId(ind)).popupWindow.close();
	          return e.preventDefault();
	        }
	      } : null, menuElement.getAttribute('data-log-entry-createtask') === 'Y' && menuElement.getAttribute('data-log-entry-entity-type') === 'TASK' ? {
	        text: main_core.Loc.getMessage('sonetLMenuCreateSubTask'),
	        className: 'menu-popup-no-icon',
	        onclick: function onclick(e) {
	          TaskCreator.create({
	            entryEntityType: menuElement.getAttribute('data-log-entry-entity-type'),
	            entityType: menuElement.getAttribute('data-log-entry-entity-type'),
	            entityId: menuElement.getAttribute('data-log-entry-entity-id'),
	            logId: parseInt(menuElement.getAttribute('data-log-entry-log-id')),
	            parentTaskId: parseInt(menuElement.getAttribute('data-log-entry-entity-id'))
	          });
	          main_popup.MenuManager.getMenuById(_this.getMenuId(ind)).popupWindow.close();
	          return e.preventDefault();
	        }
	      } : null];

	      if (!!arMenuItemsAdditional && main_core.Type.isArray(arMenuItemsAdditional)) {
	        arMenuItemsAdditional.forEach(function (item) {
	          if (main_core.Type.isUndefined(item.className)) {
	            item.className = 'menu-popup-no-icon';
	          }
	        });
	        arItems = arItems.concat(arMenuItemsAdditional);
	      }

	      var arParams = {
	        offsetLeft: -14,
	        offsetTop: 4,
	        lightShadow: false,
	        angle: {
	          position: 'top',
	          offset: 50
	        },
	        events: {
	          onPopupShow: function onPopupShow(ob) {
	            if (document.getElementById("log_entry_favorites_".concat(log_id))) {
	              var favoritesMenuItem = null;
	              var menuItems = ob.contentContainer.querySelectorAll('.menu-popup-item-text');
	              menuItems.forEach(function (menuItem) {
	                if (menuItem.innerHTML === main_core.Loc.getMessage('SONET_EXT_LIVEFEED_MENU_TITLE_FAVORITES_Y') || menuItem.innerHTML === main_core.Loc.getMessage('SONET_EXT_LIVEFEED_MENU_TITLE_FAVORITES_N')) {
	                  favoritesMenuItem = menuItem;
	                }
	              });

	              if (main_core.Type.isDomNode(favoritesMenuItem)) {
	                favoritesMenuItem.innerHTML = document.getElementById("log_entry_favorites_".concat(log_id)).classList.contains('feed-post-important-switch-active') ? main_core.Loc.getMessage('SONET_EXT_LIVEFEED_MENU_TITLE_FAVORITES_Y') : main_core.Loc.getMessage('SONET_EXT_LIVEFEED_MENU_TITLE_FAVORITES_N');
	              }
	            }

	            if (document.getElementById("".concat(menuId, "-link"))) {
	              var linkMenuItem = ob.popupContainer.querySelector('.feed-entry-popup-menu-link');

	              if (linkMenuItem) {
	                var height = parseInt(!!linkMenuItem.getAttribute('bx-height') ? linkMenuItem.getAttribute('bx-height') : 0);

	                if (height > 0) {
	                  document.getElementById("".concat(menuId, "-link")).style.display = 'none';
	                  linkMenuItem.setAttribute('bx-status', 'hidden');
	                  linkMenuItem.style.height = "".concat(height, "px");
	                }
	              }
	            }
	          }
	        }
	      };
	      main_popup.MenuManager.show(this.getMenuId(ind), bindElement, arItems, arParams);
	    }
	  }, {
	    key: "getMenuId",
	    value: function getMenuId(ind) {
	      return "post-menu-".concat(ind);
	    }
	  }]);
	  return Post$$1;
	}();

	var Informer = /*#__PURE__*/function () {
	  function Informer() {
	    babelHelpers.classCallCheck(this, Informer);
	    this.container = null;
	    this.wrap = null;
	    this.plus = null;
	    this.value = null;
	    this.currentSiteId = null;
	    this.currentCounterType = null;
	    this.counterDecrementStack = 0;
	    this.counterValue = 0;
	    this.lockCounterAnimation = null;
	    this["class"] = {
	      informerFixed: 'feed-new-message-informer-fixed',
	      informerAnimation: 'feed-new-message-informer-anim',
	      informerFixedAnimation: 'feed-new-message-informer-fix-anim',
	      counterText: 'feed-new-message-inf-text',
	      counterContainer: 'feed-new-message-inf-text-counter',
	      reloadContainer: 'feed-new-message-inf-text-reload',
	      icon: 'feed-new-message-icon',
	      iconRotating: 'new-message-balloon-icon-rotating',
	      plusHidden: 'feed-new-message-informer-counter-plus-hidden'
	    };
	  }

	  babelHelpers.createClass(Informer, [{
	    key: "init",
	    value: function init() {
	      this.initNodes();
	      this.initEvents();
	    }
	  }, {
	    key: "initNodes",
	    value: function initNodes() {
	      this.currentCounterType = main_core.Loc.getMessage('sonetLCounterType') ? main_core.Loc.getMessage('sonetLCounterType') : '**';
	      this.currentSiteId = main_core.Loc.getMessage('SITE_ID');
	      this.container = document.getElementById('sonet_log_counter_2_container');

	      if (this.container) {
	        this.container.addEventListener('click', this.showReloadAnimation.bind(this));
	      }

	      this.wrap = document.getElementById('sonet_log_counter_2_wrap');
	      this.plus = document.getElementById('sonet_log_counter_2_plus');
	      this.value = document.getElementById('sonet_log_counter_2');
	    }
	  }, {
	    key: "initEvents",
	    value: function initEvents() {
	      var _this = this;

	      main_core_events.EventEmitter.subscribe('onGoUp', function (event) {
	        _this.unfixWrap();
	      });
	      main_core_events.EventEmitter.subscribe('onPullEvent-main', function (event) {
	        var _event$getData = event.getData(),
	            _event$getData2 = babelHelpers.slicedToArray(_event$getData, 2),
	            command = _event$getData2[0],
	            eventParams = _event$getData2[1];

	        if (command !== 'user_counter' || !eventParams[_this.currentSiteId] || !eventParams[_this.currentSiteId][_this.currentCounterType]) {
	          return;
	        }

	        _this.changeCounter(main_core.Runtime.clone(eventParams[_this.currentSiteId][_this.currentCounterType]));
	      });
	      main_core_events.EventEmitter.subscribe('onImUpdateCounter', function (event) {
	        var _event$getData3 = event.getData(),
	            _event$getData4 = babelHelpers.slicedToArray(_event$getData3, 1),
	            counterData = _event$getData4[0];

	        if (!main_core.Type.isObjectLike(counterData) || main_core.Type.isUndefined(counterData[_this.currentCounterType])) {
	          return;
	        }

	        _this.changeCounter(counterData[_this.currentCounterType]);
	      });
	      main_core_events.EventEmitter.subscribe('OnUCCommentWasRead', function (event) {
	        var _event$getData5 = event.getData(),
	            _event$getData6 = babelHelpers.slicedToArray(_event$getData5, 3),
	            xmlId = _event$getData6[0],
	            id = _event$getData6[1],
	            options = _event$getData6[2];

	        if (!main_core.Type.isObjectLike(options) || !options.live || !options["new"]) {
	          return;
	        }

	        main_core_events.EventEmitter.emit('onCounterDecrement', new main_core_events.BaseEvent({
	          compatData: [1]
	        }));

	        _this.decrementCounter(1);
	      });
	    }
	  }, {
	    key: "changeCounter",
	    value: function changeCounter(count) {
	      this.counterValue = parseInt(count);

	      if (this.counterValue <= 0) {
	        this.counterDecrementStack = 0;
	      }

	      var valueToShow = this.counterValue - this.counterDecrementStack;
	      this.changeAnimate({
	        show: valueToShow > 0,
	        counter: valueToShow,
	        zeroCounterFromDb: valueToShow <= 0
	      });
	    }
	  }, {
	    key: "changeAnimate",
	    value: function changeAnimate(params) {
	      var _this2 = this;

	      var show = !!params.show;
	      var counterValue = parseInt(params.counter);
	      var zeroCounterFromDb = !!params.zeroCounterFromDb;

	      if (!this.container) {
	        return;
	      }

	      var counterTextNode = this.container.querySelector("span.".concat(this["class"].counterText));
	      var reloadNode = this.container.querySelector("span.".concat(this["class"].reloadContainer));

	      if (this.lockCounterAnimation) {
	        setTimeout(function () {
	          _this2.changeAnimate({
	            show: show,
	            counter: counterValue
	          });
	        }, 200);
	        return false;
	      }

	      if (show) {
	        if (this.value) {
	          this.value.innerHTML = counterValue;
	        }

	        this.showWrapAnimation();

	        if (this.plus && reloadNode && !reloadNode.classList.contains('--hidden') && counterTextNode) {
	          reloadNode.classList.add('--hidden');
	          counterTextNode.classList.remove('--hidden');
	          this.plus.classList.remove("".concat(this["class"].plusHidden));
	        }
	      } else if (this.wrap) {
	        if (zeroCounterFromDb && this.wrap.classList.contains("".concat(this["class"].informerAnimation))) {
	          if (counterTextNode && reloadNode) {
	            counterTextNode.classList.add('--hidden');
	            reloadNode.classList.remove('--hidden');
	            this.hideReloadAnimation();
	          }
	        } else {
	          setTimeout(function () {
	            _this2.hideWrapAnimation();
	          }, 400);
	        }
	      }
	    }
	  }, {
	    key: "showWrapAnimation",
	    value: function showWrapAnimation() {
	      if (!this.wrap) {
	        return;
	      }

	      this.wrap.style.visibility = 'visible';
	      this.wrap.classList.add("".concat(this["class"].informerAnimation));
	    }
	  }, {
	    key: "hideWrapAnimation",
	    value: function hideWrapAnimation() {
	      if (!this.wrap) {
	        return;
	      }

	      this.wrap.classList.remove("".concat(this["class"].informerAnimation));
	      this.wrap.style.visibility = 'hidden';
	    }
	  }, {
	    key: "showReloadAnimation",
	    value: function showReloadAnimation() {
	      if (!this.container) {
	        return;
	      }

	      var counterWaiterNode = this.container.querySelector("span.".concat(this["class"].icon));

	      if (counterWaiterNode) {
	        counterWaiterNode.classList.add(this["class"].iconRotating);
	      }
	    }
	  }, {
	    key: "hideReloadAnimation",
	    value: function hideReloadAnimation() {
	      if (!this.container) {
	        return;
	      }

	      var counterNodeWaiter = this.container.querySelector("span.".concat(this["class"].icon));

	      if (counterNodeWaiter) {
	        counterNodeWaiter.classList.remove(this["class"].iconRotating);
	      }
	    }
	  }, {
	    key: "onFeedScroll",
	    value: function onFeedScroll() {
	      if (!this.container || !this.wrap) {
	        return;
	      }

	      var top = this.wrap.parentNode.getBoundingClientRect().top; //		const counterRect = this.container.getBoundingClientRect();

	      if (top <= 53) {
	        /*
	        			if (!this.wrap.classList.contains(`${this.class.informerFixed}`))
	        			{
	        				this.container.style.left = `${(counterRect.left + (counterRect.width / 2))}px`;
	        			}
	        */
	        this.fixWrap();
	      } else {
	        this.unfixWrap(); //			this.container.style.left = 'auto';
	      }
	    }
	  }, {
	    key: "fixWrap",
	    value: function fixWrap() {
	      if (!this.wrap) {
	        return;
	      }

	      this.wrap.classList.add("".concat(this["class"].informerFixed), "".concat(this["class"].informerFixedAnimation));
	    }
	  }, {
	    key: "unfixWrap",
	    value: function unfixWrap() {
	      if (!this.wrap) {
	        return;
	      }

	      this.wrap.classList.remove("".concat(this["class"].informerFixed), "".concat(this["class"].informerFixedAnimation));
	    }
	  }, {
	    key: "recover",
	    value: function recover() {
	      if (!this.container) {
	        return;
	      }

	      var counterContainerNode = this.container.querySelector("span.".concat(this["class"].counterContainer));

	      if (!counterContainerNode) {
	        return;
	      }

	      counterContainerNode.classList.remove('--hidden');
	      this.hideReloadNode();

	      if (this.plus) {
	        this.plus.classList.add("".concat(this["class"].plusHidden));
	      }
	    }
	  }, {
	    key: "hideReloadNode",
	    value: function hideReloadNode() {
	      if (!this.container) {
	        return;
	      }

	      var reloadNode = this.container.querySelector("span.".concat(this["class"].reloadContainer));

	      if (!reloadNode) {
	        return;
	      }

	      reloadNode.classList.add('--hidden');
	    }
	  }, {
	    key: "decrementCounter",
	    value: function decrementCounter(value) {
	      this.counterDecrementStack += parseInt(value);

	      if (!this.value) {
	        return;
	      }

	      var counterValue = this.counterValue - this.counterDecrementStack;

	      if (counterValue > 0) {
	        this.value.innerHTML = counterValue;
	      } else {
	        this.changeAnimate({
	          show: false,
	          counter: 0
	        });
	      }
	    }
	  }, {
	    key: "getWrap",
	    value: function getWrap() {
	      return this.wrap;
	    }
	  }]);
	  return Informer;
	}();

	var Loader = /*#__PURE__*/function () {
	  function Loader() {
	    babelHelpers.classCallCheck(this, Loader);
	  }

	  babelHelpers.createClass(Loader, null, [{
	    key: "showRefreshFade",
	    value: function showRefreshFade() {
	      var _this = this;

	      var feedContainer = document.getElementById('log_internal_container');

	      if (feedContainer) {
	        feedContainer.classList.add(this.cssClass.feedMask);
	        feedContainer.classList.remove(this.cssClass.feedNoMask);
	      }

	      var loaderContainer = document.getElementById('feed-loader-container');

	      if (loaderContainer) {
	        loaderContainer.style.display = 'block';
	        loaderContainer.classList.remove(this.cssClass.hideLoader);
	        setTimeout(function () {
	          loaderContainer.classList.add(_this.cssClass.showLoader);
	        }, 0);
	      }
	    }
	  }, {
	    key: "hideRefreshFade",
	    value: function hideRefreshFade() {
	      var feedContainer = document.getElementById('log_internal_container');

	      if (feedContainer) {
	        feedContainer.classList.remove(this.cssClass.feedMask);
	        feedContainer.classList.add(this.cssClass.feedNoMask);
	      }

	      var loaderContainer = document.getElementById('feed-loader-container');

	      if (loaderContainer) {
	        loaderContainer.classList.remove(this.cssClass.showLoader);
	        loaderContainer.classList.add(this.cssClass.hideLoader);
	      }
	    }
	  }]);
	  return Loader;
	}();
	babelHelpers.defineProperty(Loader, "cssClass", {
	  feedMask: 'log-internal-mask',
	  feedNoMask: 'log-internal-nomask',
	  showLoader: 'livefeed-show-loader',
	  hideLoader: 'livefeed-hide-loader'
	});
	babelHelpers.defineProperty(Loader, "onAnimationEnd", function (event) {
	  if ('animationName' in event && event.animationName && event.animationName === 'hideLoader') {
	    var loaderContainer = document.getElementById('feed-loader-container');

	    if (!loaderContainer) {
	      return;
	    }

	    loaderContainer.classList.remove(Loader.cssClass.showLoader);
	    loaderContainer.classList.remove(Loader.cssClass.hideLoader);
	    loaderContainer.style.display = '';
	  }
	});

	var MoreButton$$1 = /*#__PURE__*/function () {
	  function MoreButton$$1() {
	    babelHelpers.classCallCheck(this, MoreButton$$1);
	    main_core_events.EventEmitter.subscribe('BX.Livefeed:recalculateComments', this.onRecalculateLivefeedComments.bind(this));
	  }

	  babelHelpers.createClass(MoreButton$$1, [{
	    key: "onRecalculateLivefeedComments",
	    value: function onRecalculateLivefeedComments(baseEvent) {
	      var _baseEvent$getCompatD = baseEvent.getCompatData(),
	          _baseEvent$getCompatD2 = babelHelpers.slicedToArray(_baseEvent$getCompatD, 1),
	          data = _baseEvent$getCompatD2[0];

	      if (!main_core.Type.isDomNode(data.rootNode)) {
	        return;
	      }

	      var informerBlock = data.rootNode;
	      var moreBlock = informerBlock.querySelector(".".concat(MoreButton$$1.cssClass.more));

	      if (moreBlock) {
	        informerBlock.classList.remove(MoreButton$$1.cssClass.postSeparator);
	      }

	      MoreButton$$1.recalcPost({
	        arPos: {
	          height: data.rootNode.offsetHeight + data.rootNode.offsetTop
	        },
	        informerBlock: informerBlock
	      });
	    }
	  }], [{
	    key: "recalcPost",
	    value: function recalcPost(params) {
	      if (!main_core.Type.isDomNode(params.informerBlock)) {
	        return;
	      }

	      var blockHeight = !main_core.Type.isUndefined(params.arPos) ? params.arPos.height : params.bodyBlock.offsetHeight;
	      var postBlock = params.informerBlock.closest(".".concat(this.cssClass.post));

	      if (!postBlock) {
	        return;
	      }

	      if (blockHeight <= 284) {
	        postBlock.classList.add(this.cssClass.postShort);
	        postBlock.classList.add(this.cssClass.postSeparator);
	      } else {
	        postBlock.classList.remove(this.cssClass.postShort);
	      }
	    }
	  }, {
	    key: "recalcPostsList",
	    value: function recalcPostsList() {
	      var _this = this;

	      var buttonsList = FeedInstance.getMoreButtons();
	      buttonsList.forEach(function (buttonData, key) {
	        if (!main_core.Type.isPlainObject(buttonData) || !main_core.Type.isStringFilled(buttonData.bodyBlockID)) {
	          return;
	        }

	        var bodyNode = document.getElementById(buttonData.bodyBlockID);

	        if (!bodyNode) {
	          return;
	        }

	        if (main_core.Type.isStringFilled(buttonData.outerBlockID)) {
	          var outerNode = document.getElementById(buttonData.outerBlockID);

	          if (outerNode) {
	            if (outerNode.offsetWidth < bodyNode.offsetWidth) {
	              var innerNode = outerNode.querySelector("div.".concat(_this.cssClass.postTextInner));
	              innerNode.style.overflowX = 'scroll';
	            }

	            var moreButton = outerNode.querySelector(".".concat(_this.cssClass.more));

	            if (moreButton) {
	              main_core.Event.unbindAll(moreButton, 'click');
	              main_core.Event.bind(moreButton, 'click', function (e) {
	                BX.UI.Animations.expand({
	                  moreButtonNode: e.currentTarget,
	                  type: 'post',
	                  classBlock: _this.cssClass.postText,
	                  classOuter: _this.cssClass.postTextInner,
	                  classInner: _this.cssClass.postTextInnerInner,
	                  heightLimit: 300,
	                  callback: function callback(textBlock) {
	                    _this.expand(textBlock);
	                  }
	                });
	              });
	            }
	          }
	        }

	        _this.recalcPost({
	          arPos: {
	            height: bodyNode.offsetHeight + bodyNode.offsetTop
	          },
	          informerBlock: main_core.Type.isStringFilled(buttonData.informerBlockID) ? document.getElementById(buttonData.informerBlockID) : null
	        });

	        buttonsList["delete"](key);
	      });
	      FeedInstance.setMoreButtons(buttonsList);
	      var feedContainer = document.getElementById('log_internal_container');

	      if (!feedContainer) {
	        return;
	      }

	      var onLoadImageList = feedContainer.querySelectorAll('[data-bx-onload="Y"]');
	      onLoadImageList.forEach(function (imageNode) {
	        imageNode.addEventListener('load', function (e) {
	          var outerBlock = e.currentTarget.closest(".".concat(_this.cssClass.comment));

	          if (!outerBlock) // post
	            {
	              outerBlock = e.currentTarget.closest(".".concat(_this.cssClass.post));

	              if (outerBlock) {
	                var bodyBlock = outerBlock.querySelector(".".concat(_this.cssClass.postTextInnerInner));

	                if (bodyBlock) {
	                  _this.recalcPost({
	                    bodyBlock: bodyBlock,
	                    informerBlock: outerBlock.querySelector(".".concat(_this.cssClass.more))
	                  });
	                }
	              }
	            }

	          e.currentTarget.setAttribute('data-bx-onload', 'N');
	        });
	      });
	    }
	  }, {
	    key: "recalcCommentsList",
	    value: function recalcCommentsList() {
	      main_core_events.EventEmitter.emit('OnUCMoreButtonListRecalc', new main_core_events.BaseEvent({
	        compatData: []
	      }));
	    }
	  }, {
	    key: "clearCommentsList",
	    value: function clearCommentsList() {
	      main_core_events.EventEmitter.emit('OnUCMoreButtonListClear', new main_core_events.BaseEvent({
	        compatData: []
	      }));
	    }
	  }, {
	    key: "expand",
	    value: function expand(textBlock) {
	      if (!main_core.Type.isDomNode(textBlock)) {
	        return;
	      }

	      var postBlock = textBlock.closest(".".concat(this.cssClass.post));

	      if (!postBlock) {
	        return;
	      }

	      postBlock.classList.add(this.cssClass.postShort);
	      postBlock.classList.add(this.cssClass.postSeparator);
	    }
	    /*
	    is not used actually by disk uf
	    */

	  }, {
	    key: "lazyLoadCheckVisibility",
	    value: function lazyLoadCheckVisibility(image) {
	      if (!main_core.Type.isPlainObject(image) || !main_core.Type.isDomNode(image.node)) {
	        return true;
	      }

	      var imageNode = image.node;
	      var textType = 'comment';
	      var textBlock = imageNode.closest(".".concat(this.cssClass.comment));

	      if (!textBlock) {
	        textType = 'post';
	        textBlock = imageNode.closest(".".concat(this.cssClass.postText));
	      }

	      if (!textBlock) {
	        return true;
	      }

	      var moreBlock = textBlock.querySelector("div.".concat(this.cssClass.more));

	      if (!moreBlock || moreBlock.style.display === 'none') {
	        return true;
	      }

	      return imageNode.parentNode.parentNode.offsetTop < (textType === 'comment' ? 220 : 270);
	    }
	  }]);
	  return MoreButton$$1;
	}();
	babelHelpers.defineProperty(MoreButton$$1, "cssClass", {
	  post: 'feed-post-block',
	  postShort: 'feed-post-block-short',
	  postSeparator: 'feed-post-block-separator',
	  postText: 'feed-post-text-block',
	  postTextInner: 'feed-post-text-block-inner',
	  postTextInnerInner: 'feed-post-text-block-inner-inner',
	  more: 'feed-post-text-more',
	  comment: 'feed-com-text'
	});

	var Forum = /*#__PURE__*/function () {
	  function Forum() {
	    babelHelpers.classCallCheck(this, Forum);
	  }

	  babelHelpers.createClass(Forum, null, [{
	    key: "processSpoilerToggle",
	    value: function processSpoilerToggle(event) {
	      var _event$getCompatData = event.getCompatData(),
	          _event$getCompatData2 = babelHelpers.slicedToArray(_event$getCompatData, 1),
	          params = _event$getCompatData2[0];

	      if (!main_core.Type.isPlainObject(params)) {
	        params = {};
	      }

	      if (!main_core.Type.isDomNode(params.node)) {
	        return;
	      }

	      var outerBlock = params.node.closest('.feed-post-block');

	      if (!outerBlock) {
	        return;
	      }

	      var bodyBlock = outerBlock.querySelector('.feed-post-text-block-inner-inner');

	      if (!bodyBlock) {
	        return;
	      }

	      var moreBlock = outerBlock.querySelector('.feed-post-text-more');
	      MoreButton$$1.recalcPost({
	        bodyBlock: bodyBlock,
	        informerBlock: moreBlock
	      });
	    }
	  }]);
	  return Forum;
	}();
	babelHelpers.defineProperty(Forum, "cssClass", {});

	var Filter = /*#__PURE__*/function () {
	  function Filter() {
	    babelHelpers.classCallCheck(this, Filter);
	    this.filterId = '';
	    this.filterApi = null;
	  }

	  babelHelpers.createClass(Filter, [{
	    key: "init",
	    value: function init(params) {
	      if (!main_core.Type.isPlainObject(params)) {
	        params = {};
	      }

	      if (main_core.Type.isStringFilled(params.filterId) && !main_core.Type.isUndefined(BX.Main) && !main_core.Type.isUndefined(BX.Main.filterManager)) {
	        var filterManager = BX.Main.filterManager.getById(params.filterId);
	        this.filterId = params.filterId;

	        if (filterManager) {
	          this.filterApi = filterManager.getApi();
	        }
	      }

	      this.initEvents();
	    }
	  }, {
	    key: "initEvents",
	    value: function initEvents() {
	      main_core_events.EventEmitter.subscribe('BX.Livefeed.Filter:beforeApply', function (event) {
	        Loader.showRefreshFade();
	      });
	      main_core_events.EventEmitter.subscribe('BX.Livefeed.Filter:apply', function (event) {
	        var _event$getCompatData = event.getCompatData(),
	            _event$getCompatData2 = babelHelpers.slicedToArray(_event$getCompatData, 3),
	            filterValues = _event$getCompatData2[0],
	            filterPromise = _event$getCompatData2[1],
	            filterParams = _event$getCompatData2[2];

	        if (typeof filterParams != 'undefined') {
	          filterParams.autoResolve = false;
	        }

	        PageInstance.refresh({
	          useBXMainFilter: 'Y'
	        }, filterPromise);
	      });
	      main_core_events.EventEmitter.subscribe('BX.Livefeed.Filter:searchInput', function (event) {
	        var _event$getCompatData3 = event.getCompatData(),
	            _event$getCompatData4 = babelHelpers.slicedToArray(_event$getCompatData3, 1),
	            searchString = _event$getCompatData4[0];

	        if (main_core.Type.isStringFilled(searchString)) {
	          Loader.showRefreshFade();
	        } else {
	          Loader.hideRefreshFade();
	        }
	      });
	    }
	  }, {
	    key: "initEventsCrm",
	    value: function initEventsCrm() {
	      main_core_events.EventEmitter.subscribe('BX.Livefeed.Filter:searchInput', function () {
	        PageInstance.refresh();
	      });
	    }
	  }, {
	    key: "clickTag",
	    value: function clickTag(tagValue) {
	      if (!main_core.Type.isStringFilled(tagValue) || !this.filterApi) {
	        return false;
	      }

	      this.filterApi.setFields({
	        TAG: tagValue
	      });
	      this.filterApi.apply();

	      if (main_core.Type.isStringFilled(this.filterId) && !main_core.Type.isUndefined(BX.Main) && !main_core.Type.isUndefined(BX.Main.filterManager)) {
	        var filterContainer = document.getElementById("".concat(this.filterId, "_filter_container"));

	        if (filterContainer && BX.Main.filterManager.getById(this.filterId) && (BX.Main.filterManager.getById(this.filterId).getSearch().getSquares().length > 0 || BX.Main.filterManager.getById(this.filterId).getSearch().getSearchString().length > 0)) {
	          var pagetitleContainer = filterContainer.closest('.pagetitle-wrap');

	          if (pagetitleContainer) {
	            pagetitleContainer.classList.add('pagetitle-wrap-filter-opened');
	          }
	        }
	      }

	      new BX.easing({
	        duration: 500,
	        start: {
	          scroll: window.pageYOffset
	        },
	        finish: {
	          scroll: 0
	        },
	        transition: BX.easing.makeEaseOut(BX.easing.transitions.quart),
	        step: function step(state) {
	          window.scrollTo(0, state.scroll);
	        },
	        complete: function complete() {}
	      }).animate();
	      return true;
	    }
	  }]);
	  return Filter;
	}();

	var ContentView = /*#__PURE__*/function () {
	  function ContentView() {
	    babelHelpers.classCallCheck(this, ContentView);
	  }

	  babelHelpers.createClass(ContentView, null, [{
	    key: "registerAreaList",
	    value: function registerAreaList() {
	      var container = BX('log_internal_container'),
	          fullContentArea = null;

	      if (container) {
	        var viewAreaList = BX.findChildren(container, {
	          tag: 'div',
	          className: 'feed-post-contentview'
	        }, true);

	        for (var i = 0, length = viewAreaList.length; i < length; i++) {
	          if (viewAreaList[i].id.length > 0) {
	            fullContentArea = BX.findChild(viewAreaList[i], {
	              tag: 'div',
	              className: 'feed-post-text-block-inner-inner'
	            });
	            BX.UserContentView.registerViewArea(viewAreaList[i].id, fullContentArea ? fullContentArea : null);
	          }
	        }
	      }
	    }
	  }]);
	  return ContentView;
	}();

	var _templateObject$3, _templateObject2$1;

	var Page = /*#__PURE__*/function () {
	  function Page() {
	    var _this = this;

	    babelHelpers.classCallCheck(this, Page);
	    this.loadStarted = null;
	    this.stopTrackNextPage = null;
	    this.requestMode = null;
	    this.nextPageFirst = null;
	    this.nextPageUrl = null;
	    this.scrollInitialized = null;
	    this.firstPageLastTS = 0;
	    this.firstPageLastId = 0;
	    this.useBXMainFilter = 'N';
	    this.commentFormUID = '';
	    this.blogCommentFormUID = '';
	    this.signedParameters = '';
	    this.componentName = '';
	    this["class"] = {};
	    main_core.Event.ready(function () {
	      _this.init();
	    });
	  }

	  babelHelpers.createClass(Page, [{
	    key: "init",
	    value: function init() {
	      this.loadStarted = false;
	      this.stopTrackNextPage = false;
	      this.requestMode = false;
	      this.nextPageFirst = true;
	      this.nextPageUrl = false;
	      this.scrollInitialized = false;
	    }
	  }, {
	    key: "refresh",
	    value: function refresh(params, filterPromise) {
	      var _this2 = this;

	      if (this.loadStarted) {
	        return;
	      }

	      this.setRequestModeNew();
	      params = main_core.Type.isPlainObject(params) ? params : {};
	      params.siteTemplateId = main_core.Loc.getMessage('SONET_EXT_LIVEFEED_SITE_TEMPLATE_ID');
	      params.assetsCheckSum = main_core.Loc.getMessage('sonetLAssetsCheckSum');
	      this.loadStarted = true;
	      Loader.showRefreshFade();
	      MoreButton$$1.clearCommentsList();
	      FeedInstance.clearMoreButtons();

	      if (main_core.Type.isStringFilled(this.commentFormUID)) {
	        params.commentFormUID = this.commentFormUID;
	      }

	      if (!main_core.Type.isStringFilled(params.useBXMainFilter) || params.useBXMainFilter !== 'Y') {
	        main_core_events.EventEmitter.emit('BX.Livefeed:refresh', new main_core_events.BaseEvent({
	          compatData: []
	        }));
	      }

	      InformerInstance.hideReloadNode();
	      InformerInstance.lockCounterAnimation = true;
	      this.loadStarted = false;
	      main_core.ajax.runAction('socialnetwork.api.livefeed.refresh', {
	        signedParameters: this.getSignedParameters(),
	        data: {
	          c: this.getComponentName(),
	          logajax: 'Y',
	          // compatibility
	          RELOAD: 'Y',
	          // compatibility
	          params: params
	        }
	      }).then(function (response) {
	        var responseData = main_core.Type.isPlainObject(response.data) ? response.data : {};
	        _this2.loadStarted = false;
	        Loader.hideRefreshFade();

	        if (filterPromise) {
	          filterPromise.fulfill();
	        }

	        var emptyLivefeed = main_core.Type.isPlainObject(responseData.componentResult) && main_core.Type.isStringFilled(responseData.componentResult.EMPTY) ? responseData.componentResult.EMPTY : 'N';
	        var forcePageRefresh = main_core.Type.isPlainObject(responseData.componentResult) && main_core.Type.isStringFilled(responseData.componentResult.FORCE_PAGE_REFRESH) ? responseData.componentResult.FORCE_PAGE_REFRESH : 'N';
	        var isFilterUsed = main_core.Type.isPlainObject(responseData.componentResult) && main_core.Type.isStringFilled(responseData.componentResult.FILTER_USED) && responseData.componentResult.FILTER_USED === 'Y';

	        if (forcePageRefresh === 'Y') {
	          top.window.location.reload();
	          return;
	        }

	        var loaderContainer = document.getElementById('feed-loader-container');
	        InformerInstance.lockCounterAnimation = false;
	        var feedContainer = document.getElementById('log_internal_container');

	        if (!feedContainer) {
	          return;
	        }

	        main_core.Dom.clean(feedContainer);
	        var emptyBlock = document.getElementById('feed-empty-wrap');

	        if (emptyBlock) {
	          if (emptyLivefeed === 'Y') {
	            emptyBlock.style.display = 'block';
	            var emptyTextNode = emptyBlock.querySelector('.feed-wrap-empty');

	            if (emptyTextNode) {
	              emptyTextNode.innerHTML = isFilterUsed ? main_core.Loc.getMessage('SONET_C30_T_EMPTY_SEARCH') : main_core.Loc.getMessage('SONET_C30_T_EMPTY');
	            }
	          } else {
	            emptyBlock.style.display = 'none';
	          }
	        }

	        if (loaderContainer) {
	          feedContainer.appendChild(loaderContainer);
	        }

	        if (responseData.html.length > 0) {
	          _this2.clearContainerExternal();

	          BX.LazyLoad.clearImages();
	          var pageNode = main_core.Tag.render(_templateObject$3 || (_templateObject$3 = babelHelpers.taggedTemplateLiteral(["<div id=\"content_block_", "\" class=\"feed-wrap\" style=\"display: block;\"></div>"])), Math.floor(Math.random() * 1000));
	          feedContainer.appendChild(pageNode);
	          main_core.Runtime.html(pageNode, responseData.html).then(function () {
	            MoreButton$$1.recalcPostsList();
	            MoreButton$$1.recalcCommentsList();
	            ContentView.registerAreaList();
	            PinnedPanelInstance.resetFlags();
	            PinnedPanelInstance.initPanel();
	            PinnedPanelInstance.initPosts();
	          });
	          _this2.stopTrackNextPage = false;
	          MoreButton$$1.clearCommentsList();
	          var informerWrap = InformerInstance.getWrap();

	          if (informerWrap && informerWrap.classList.contains(InformerInstance["class"].informerFixed)) {
	            new BX.easing({
	              duration: 500,
	              start: {
	                scroll: window.pageYOffset
	              },
	              finish: {
	                scroll: 0
	              },
	              transition: BX.easing.makeEaseOut(BX.easing.transitions.quart),
	              step: function step(state) {
	                window.scrollTo(0, state.scroll);
	              },
	              complete: function complete() {
	                main_core_events.EventEmitter.emit('onGoUp', []);
	              }
	            }).animate();
	          }
	        }
	      }, function () {
	        _this2.loadStarted = false;

	        if (filterPromise) {
	          filterPromise.reject();
	        }

	        Loader.hideRefreshFade();

	        _this2.showRefreshError();
	      });
	      return false;
	    }
	  }, {
	    key: "getNextPage",
	    value: function getNextPage() {
	      var _this3 = this;

	      var stubContainer = document.getElementById('feed-new-message-inf-wrap');
	      var stubFirstContainer = document.getElementById('feed-new-message-inf-wrap-first');

	      if (this.loadStarted) {
	        return false;
	      }

	      this.setRequestModeMore();
	      this.loadStarted = true;
	      InformerInstance.lockCounterAnimation = true;
	      FeedInstance.clearMoreButtons();

	      if (!this.nextPageFirst && stubContainer) {
	        stubContainer.style.display = 'block';
	      } else if (this.nextPageFirst && stubFirstContainer) {
	        stubFirstContainer.classList.add('feed-new-message-inf-wrap-first-visible');
	      }

	      var nextUrlParamsList = new main_core.Uri(this.getNextPageUrl()).getQueryParams();
	      var pageNumber = 1;
	      var prevPageLogId = '';
	      var ts = 0;
	      var noblog = 'N';
	      Object.entries(nextUrlParamsList).forEach(function (_ref) {
	        var _ref2 = babelHelpers.slicedToArray(_ref, 2),
	            key = _ref2[0],
	            value = _ref2[1];

	        if (key.match(/^PAGEN_(\d+)$/i)) {
	          pageNumber = parseInt(value);
	        } else if (key === 'pplogid') {
	          prevPageLogId = decodeURI(value);
	        } else if (key === 'ts') {
	          ts = value;
	        } else if (key === 'noblog') {
	          noblog = value;
	        }
	      });
	      var queryParams = {
	        PAGE_NUMBER: pageNumber,
	        LAST_LOG_TIMESTAMP: ts,
	        PREV_PAGE_LOG_ID: prevPageLogId,
	        siteTemplateId: main_core.Loc.getMessage('SONET_EXT_LIVEFEED_SITE_TEMPLATE_ID'),
	        useBXMainFilter: this.useBXMainFilter,
	        preset_filter_top_id: main_core.Type.isStringFilled(nextUrlParamsList.preset_filter_top_id) && nextUrlParamsList.preset_filter_top_id !== '0' ? nextUrlParamsList.preset_filter_top_id : '',
	        preset_filter_id: main_core.Type.isStringFilled(nextUrlParamsList.preset_filter_id) && nextUrlParamsList.preset_filter_id !== '0' ? nextUrlParamsList.preset_filter_id : ''
	      };

	      if (main_core.Type.isStringFilled(this.commentFormUID)) {
	        queryParams.commentFormUID = this.commentFormUID;
	      }

	      if (main_core.Type.isStringFilled(this.blogCommentFormUID)) {
	        queryParams.blogCommentFormUID = this.blogCommentFormUID;
	      }

	      var queryData = {
	        c: this.getComponentName(),
	        logajax: 'Y',
	        // compatibility with socialnetwork.blog.post.comment
	        noblog: noblog,
	        // compatibility with socialnetwork.blog.post.comment
	        params: queryParams
	      };

	      if (!main_core.Type.isUndefined(nextUrlParamsList.CREATED_BY_ID)) {
	        queryData.flt_created_by_id = parseInt(nextUrlParamsList.CREATED_BY_ID);
	      }

	      if (!main_core.Type.isUndefined(nextUrlParamsList.flt_date_datesel)) {
	        queryData.flt_date_datesel = nextUrlParamsList.flt_date_datesel;
	      }

	      if (!main_core.Type.isUndefined(nextUrlParamsList.flt_date_from)) {
	        queryData.flt_date_from = decodeURIComponent(nextUrlParamsList.flt_date_from);
	      }

	      if (!main_core.Type.isUndefined(nextUrlParamsList.flt_date_to)) {
	        queryData.flt_date_to = decodeURIComponent(nextUrlParamsList.flt_date_to);
	      }

	      main_core.ajax.runAction('socialnetwork.api.livefeed.getNextPage', {
	        signedParameters: this.getSignedParameters(),
	        data: queryData
	      }).then(function (response) {
	        var responseData = main_core.Type.isPlainObject(response.data) ? response.data : {};
	        _this3.loadStarted = false;
	        var stubContainer = document.getElementById('feed-new-message-inf-wrap');

	        if (stubContainer) {
	          main_core.Dom.clean(stubContainer);
	          main_core.Dom.remove(stubContainer);
	        }

	        InformerInstance.lockCounterAnimation = false;
	        var lastEntryTimestamp = main_core.Type.isPlainObject(responseData.componentResult) && !main_core.Type.isUndefined(responseData.componentResult.LAST_TS) ? parseInt(responseData.componentResult.LAST_TS) : 0;
	        var lastEntryId = main_core.Type.isPlainObject(responseData.componentResult) && !main_core.Type.isUndefined(responseData.componentResult.LAST_ID) ? parseInt(responseData.componentResult.LAST_ID) : null;

	        if (responseData.html.length > 0 && lastEntryTimestamp > 0 && (parseInt(_this3.firstPageLastTS) <= 0 || lastEntryTimestamp < parseInt(_this3.firstPageLastTS) || lastEntryTimestamp == parseInt(_this3.firstPageLastTS) && !main_core.Type.isNull(lastEntryId) && lastEntryId < parseInt(_this3.firstPageLastId))) {
	          MoreButton$$1.clearCommentsList();
	          var contentBlockId = "content_block_".concat(Math.floor(Math.random() * 1000));
	          var pageNode = main_core.Tag.render(_templateObject2$1 || (_templateObject2$1 = babelHelpers.taggedTemplateLiteral(["<div id=\"", "\" class=\"feed-wrap\" style=\"display:", ";\"></div>"])), contentBlockId, _this3.nextPageFirst ? 'none' : 'block');
	          var feedContainer = document.getElementById('log_internal_container');

	          if (!feedContainer) {
	            return;
	          }

	          feedContainer.appendChild(pageNode);
	          main_core.Runtime.html(pageNode, responseData.html).then(function () {
	            if (pageNumber > 2) {
	              _this3.stopTrackNextPage = false;
	              MoreButton$$1.recalcPostsList();
	              ContentView.registerAreaList();
	              MoreButton$$1.recalcCommentsList();
	              PinnedPanelInstance.resetFlags();
	              PinnedPanelInstance.initPosts();
	            }
	          });

	          _this3.clearContainerExternal();

	          if (pageNumber === 2) {
	            document.getElementById('feed-new-message-inf-text-first').style.display = 'block';
	            document.getElementById('feed-new-message-inf-loader-first').style.display = 'none';
	            stubFirstContainer.classList.add('feed-new-message-inf-wrap-first-active');

	            var f = function f() {
	              _this3.stopTrackNextPage = false;

	              if (pageNode) {
	                pageNode.style.display = 'block';
	              }

	              main_core.Event.unbind(document.getElementById('sonet_log_more_container_first'), 'click', f);
	              stubFirstContainer.style.display = 'none';
	              MoreButton$$1.recalcPostsList();
	              ContentView.registerAreaList();
	              MoreButton$$1.recalcCommentsList();
	              main_core_events.EventEmitter.emit('BX.Livefeed:recalculateComments', new main_core_events.BaseEvent({
	                compatData: [{
	                  rootNode: pageNode
	                }]
	              }));
	              PinnedPanelInstance.resetFlags();
	              PinnedPanelInstance.initPosts();
	            };

	            main_core.Event.bind(document.getElementById('sonet_log_more_container_first'), 'click', f);
	          } else {
	            if (pageNode) {
	              pageNode.style.display = 'block';
	            }
	          }

	          _this3.nextPageFirst = false;
	        } else if (document.getElementById('feed-new-message-inf-wrap-first')) {
	          document.getElementById('feed-new-message-inf-wrap-first').style.display = 'none';
	        }
	      }, function () {
	        _this3.loadStarted = false;
	        _this3.stopTrackNextPage = false;
	        var stubContainer = document.getElementById('feed-new-message-inf-wrap');

	        if (stubContainer) {
	          stubContainer.style.display = 'none';
	        }

	        InformerInstance.lockCounterAnimation = false;

	        _this3.clearContainerExternal();
	      });
	      return false;
	    }
	  }, {
	    key: "clearContainerExternal",
	    value: function clearContainerExternal() {
	      if (this.requestMode === 'new') {
	        InformerInstance.hideWrapAnimation();
	        InformerInstance.recover();
	      }

	      InformerInstance.hideReloadAnimation();
	      var counterPreset = document.getElementById('sonet_log_counter_preset');

	      if (counterPreset && this.requestMode === 'new') {
	        counterPreset.style.display = 'none';
	      }
	    }
	  }, {
	    key: "setRequestModeNew",
	    value: function setRequestModeNew() {
	      this.requestMode = 'new';
	    }
	  }, {
	    key: "setRequestModeMore",
	    value: function setRequestModeMore() {
	      this.requestMode = 'more';
	    }
	  }, {
	    key: "showRefreshError",
	    value: function showRefreshError() {
	      InformerInstance.lockCounterAnimation = false;
	      this.clearContainerExternal();
	    }
	  }, {
	    key: "setSignedParameters",
	    value: function setSignedParameters(value) {
	      this.signedParameters = value;
	    }
	  }, {
	    key: "getSignedParameters",
	    value: function getSignedParameters() {
	      return this.signedParameters;
	    }
	  }, {
	    key: "setComponentName",
	    value: function setComponentName(value) {
	      this.componentName = value;
	    }
	  }, {
	    key: "getComponentName",
	    value: function getComponentName() {
	      return this.componentName;
	    }
	  }, {
	    key: "setNextPageUrl",
	    value: function setNextPageUrl(value) {
	      this.nextPageUrl = value;
	    }
	  }, {
	    key: "getNextPageUrl",
	    value: function getNextPageUrl() {
	      return this.nextPageUrl;
	    }
	  }, {
	    key: "initScroll",
	    value: function initScroll() {
	      if (this.scrollInitialized) {
	        return;
	      }

	      this.scrollInitialized = true;
	      document.addEventListener('scroll', this.onFeedScroll.bind(this));
	    }
	  }, {
	    key: "onFeedScroll",
	    value: function onFeedScroll() {
	      if (!this.stopTrackNextPage) {
	        var maxScroll = document.documentElement.scrollHeight - window.innerHeight - 500;

	        if (window.pageYOffset >= maxScroll && this.getNextPageUrl()) {
	          this.stopTrackNextPage = true;
	          this.getNextPage();
	        }
	      }

	      InformerInstance.onFeedScroll();
	    }
	  }]);
	  return Page;
	}();

	var _templateObject$4;
	var CommentForm = /*#__PURE__*/function () {
	  function CommentForm() {
	    babelHelpers.classCallCheck(this, CommentForm);
	  }

	  babelHelpers.createClass(CommentForm, null, [{
	    key: "appendResultFieldTaskIds",
	    value: function appendResultFieldTaskIds(taskIdList) {
	      if (!main_core.Type.isArray(taskIdList)) {
	        return;
	      }

	      taskIdList = taskIdList.map(function (value) {
	        return parseInt(value);
	      });
	      this.resultFieldTaskIdList = [].concat(babelHelpers.toConsumableArray(this.resultFieldTaskIdList), babelHelpers.toConsumableArray(taskIdList));
	    }
	  }, {
	    key: "appendTaskResultComments",
	    value: function appendTaskResultComments(data) {
	      if (main_core.Type.isUndefined(tasks_result.ResultManager)) {
	        return;
	      }

	      this.taskResultCommentsData = Object.assign(this.taskResultCommentsData, data);
	      Object.entries(this.taskResultCommentsData).forEach(function (_ref) {
	        var _ref2 = babelHelpers.slicedToArray(_ref, 2),
	            taskId = _ref2[0],
	            commentsIdList = _ref2[1];

	        tasks_result.ResultManager.getInstance().initResult({
	          context: 'task',
	          taskId: parseInt(taskId),
	          comments: commentsIdList
	        });
	      });
	    }
	  }, {
	    key: "onAfterShow",
	    value: function onAfterShow(obj, text, data) {
	      if (!main_core.Type.isPlainObject(data)) {
	        data = {};
	      }

	      main_core_events.EventEmitter.emit('OnBeforeSocialnetworkCommentShowedUp', new main_core_events.BaseEvent({
	        compatData: ['socialnetwork']
	      }));
	      var postData = {
	        ENTITY_XML_ID: obj.currentEntity.ENTITY_XML_ID,
	        ENTITY_TYPE: obj.currentEntity.ENTITY_XML_ID.split('_')[0],
	        ENTITY_ID: obj.currentEntity.ENTITY_XML_ID.split('_')[1],
	        parentId: obj.id[1],
	        comment_post_id: obj.currentEntity.ENTITY_XML_ID.split('_')[1],
	        edit_id: obj.id[1],
	        act: obj.id[1] > 0 ? 'edit' : 'add'
	      };
	      Object.entries(postData).forEach(function (_ref3) {
	        var _ref4 = babelHelpers.slicedToArray(_ref3, 2),
	            key = _ref4[0],
	            value = _ref4[1];

	        if (!obj.form[key]) {
	          obj.form.appendChild(main_core.Tag.render(_templateObject$4 || (_templateObject$4 = babelHelpers.taggedTemplateLiteral(["<input type=\"hidden\" name=\"", "\">"])), key));
	        }

	        obj.form[key].value = value;
	      });
	      this.onLightEditorShow(text, data);

	      if (!BX.Type.isUndefined(BX.Tasks)) {
	        var matches = obj.currentEntity.ENTITY_XML_ID.match(/^TASK_(\d+)$/i);

	        if (matches && this.resultFieldTaskIdList.includes(parseInt(matches[1]))) {
	          BX.Tasks.ResultManager.showField();
	        } else {
	          BX.Tasks.ResultManager.hideField();
	        }
	      }
	    }
	  }, {
	    key: "onLightEditorShow",
	    value: function onLightEditorShow(content, data) {
	      if (!main_core.Type.isPlainObject(data)) {
	        data = {};
	      }

	      var result = {};

	      if (main_core.Type.isPlainObject(data.UF)) {
	        result = data.UF;
	      } else {
	        if (data.arFiles) {
	          var value = {};
	          data.arFiles.forEach(function (fileId, index) {
	            var container = document.getElementById("wdif-doc-".concat(fileId));
	            var name = container.querySelector('.feed-com-file-name');
	            var size = container.querySelector('.feed-con-file-size');
	            value["F".concat(index)] = {
	              FILE_ID: fileId,
	              FILE_NAME: name ? name.innerHTML : 'noname',
	              FILE_SIZE: size ? size.innerHTML : 'unknown',
	              CONTENT_TYPE: 'notimage/xyz'
	            };
	          });
	          result.UF_SONET_COM_DOC = {
	            USER_TYPE_ID: 'file',
	            FIELD_NAME: 'UF_SONET_COM_FILE[]',
	            VALUE: value
	          };
	        }

	        if (data.arDocs) {
	          result.UF_SONET_COM_FILE = {
	            USER_TYPE_ID: 'webdav_element',
	            FIELD_NAME: 'UF_SONET_COM_DOC[]',
	            VALUE: main_core.Runtime.clone(data.arDocs)
	          };
	        }

	        if (data.arDFiles) {
	          result.UF_SONET_COM_FILE = {
	            USER_TYPE_ID: 'disk_file',
	            FIELD_NAME: 'UF_SONET_COM_DOC[]',
	            VALUE: main_core.Runtime.clone(data.arDFiles)
	          };
	        }
	      }

	      LHEPostForm.reinitData(window.SLEC.editorId, content, result);
	    }
	  }]);
	  return CommentForm;
	}();
	babelHelpers.defineProperty(CommentForm, "resultFieldTaskIdList", []);
	babelHelpers.defineProperty(CommentForm, "taskResultCommentsData", {});

	var _templateObject$5, _templateObject2$2;

	var Feed = /*#__PURE__*/function () {
	  function Feed() {
	    babelHelpers.classCallCheck(this, Feed);
	    this.entryData = {};
	    this.feedInitialized = false;
	    this.moreButtonDataList = new Map();
	  }

	  babelHelpers.createClass(Feed, [{
	    key: "initOnce",
	    value: function initOnce(params) {
	      var loaderContainer = document.getElementById('feed-loader-container');

	      if (!main_core.Type.isPlainObject(params)) {
	        params = {};
	      }

	      if (main_core.Type.isStringFilled(params.signedParameters)) {
	        PageInstance.setSignedParameters(params.signedParameters);
	      }

	      if (main_core.Type.isStringFilled(params.componentName)) {
	        PageInstance.setComponentName(params.componentName);
	      }

	      if (main_core.Type.isStringFilled(params.commentFormUID)) {
	        PageInstance.commentFormUID = params.commentFormUID;
	      }

	      if (loaderContainer) {
	        main_core.Event.bind(loaderContainer, 'animationend', Loader.onAnimationEnd);
	        main_core.Event.bind(loaderContainer, 'webkitAnimationEnd', Loader.onAnimationEnd);
	        main_core.Event.bind(loaderContainer, 'oanimationend', Loader.onAnimationEnd);
	        main_core.Event.bind(loaderContainer, 'MSAnimationEnd', Loader.onAnimationEnd);
	      }

	      main_core_events.EventEmitter.subscribe('BX.Forum.Spoiler:toggle', Forum.processSpoilerToggle);
	      FilterInstance.init({
	        filterId: params.filterId
	      });

	      if (main_core.Type.isStringFilled(params.crmEntityTypeName) && !main_core.Type.isUndefined(params.crmEntityId) && parseInt(params.crmEntityId) > 0) {
	        FilterInstance.initEventsCrm();
	      }

	      BX.UserContentView.init();
	      document.getElementById('log_internal_container').addEventListener('click', function (e) {
	        var tagValue = e.target.getAttribute('bx-tag-value');

	        if (!main_core.Type.isStringFilled(tagValue)) {
	          return;
	        }

	        if (FilterInstance.clickTag(tagValue)) {
	          e.preventDefault();
	          e.stopPropagation();
	        }
	      }, true);
	      var noTasksNotificationCloseIcon = document.getElementById('feed-notification-notasks-close-btn');
	      var noTasksNotificationReadButton = document.getElementById('feed-notification-notasks-read-btn');

	      if (noTasksNotificationCloseIcon) {
	        main_core.Event.bind(noTasksNotificationCloseIcon, 'click', this.setNoTasksNotificationRead.bind(this));
	      }

	      if (noTasksNotificationReadButton) {
	        main_core.Event.bind(noTasksNotificationReadButton, 'click', this.setNoTasksNotificationRead.bind(this));
	      }
	    }
	  }, {
	    key: "init",
	    value: function init() {
	      if (this.feedInitialized) {
	        return;
	      }

	      PinnedPanelInstance.init();
	      InformerInstance.init();
	      this.feedInitialized = true;
	    }
	  }, {
	    key: "changeFollow",
	    value: function changeFollow(params) {
	      var _this = this;

	      var logId = params.logId ? parseInt(params.logId) : 0;

	      if (!logId) {
	        return false;
	      }

	      var followNode = document.getElementById('log_entry_follow_' + logId);
	      var valueOld = followNode && followNode.getAttribute('data-follow') === 'Y' ? 'Y' : 'N';
	      var valueNew = valueOld === 'Y' ? 'N' : 'Y';
	      this.renderFollow({
	        logId: logId,
	        value: valueNew
	      });
	      main_core.ajax.runAction('socialnetwork.api.livefeed.changeFollow', {
	        data: {
	          logId: logId,
	          value: valueNew
	        },
	        analyticsLabel: {
	          b24statAction: valueNew === 'Y' ? 'setFollow' : 'setUnfollow'
	        }
	      }).then(function (response) {
	        if (!response.data.success) {
	          _this.renderFollow({
	            logId: logId,
	            value: valueOld
	          });
	        }
	      }, function () {
	        _this.renderFollow({
	          logId: logId,
	          value: valueOld
	        });
	      });
	      return false;
	    }
	  }, {
	    key: "renderFollow",
	    value: function renderFollow(params) {
	      var logId = params.logId ? parseInt(params.logId) : 0;

	      if (!logId) {
	        return;
	      }

	      var followNode = document.getElementById('log_entry_follow_' + logId);
	      var value = params.value && params.value === 'Y' ? 'Y' : 'N';

	      if (followNode) {
	        followNode.setAttribute('data-follow', value);
	      }

	      var textNode = followNode ? followNode.querySelector('a') : null;

	      if (textNode) {
	        textNode.innerHTML = main_core.Loc.getMessage('SONET_EXT_LIVEFEED_FOLLOW_TITLE_' + value);
	      }

	      var postNode = followNode ? followNode.closest('.feed-post-block') : null;

	      if (postNode) {
	        if (value === 'N') {
	          postNode.classList.add('feed-post-block-unfollowed');
	        } else if (value === 'Y') {
	          postNode.classList.remove('feed-post-block-unfollowed');
	        }
	      }
	    }
	  }, {
	    key: "changeFavorites",
	    value: function changeFavorites(params) {
	      var _this2 = this;

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
	          _this2.entryData[logId].favorites = response.data.newValue == 'Y';
	        }

	        _this2.adjustFavoritesControlItem(nodeToAdjust, response.data.newValue);

	        _this2.adjustFavoritesMenuItem(menuItem, response.data.newValue);
	      }, function () {
	        _this2.entryData[logId].favorites = !_this2.entryData[logId].favorites;
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
	  }, {
	    key: "delete",
	    value: function _delete(params) {
	      var _this3 = this;

	      var logId = params.logId ? parseInt(params.logId) : 0;
	      var node = main_core.Type.isStringFilled(params.nodeId) ? document.getElementById(params.nodeId) : null;
	      var ind = params.ind ? params.ind : '';

	      if (logId <= 0 || !node) {
	        return;
	      }

	      main_core.ajax.runAction('socialnetwork.api.livefeed.deleteEntry', {
	        data: {
	          logId: logId
	        },
	        analyticsLabel: {
	          b24statAction: 'deleteLogEntry'
	        }
	      }).then(function (response) {
	        if (response.data.success) {
	          if (!main_core.Type.isUndefined(ind)) {
	            main_popup.MenuManager.destroy(Post$$1.getMenuId(ind));
	          }

	          _this3.deleteSuccess(node);
	        } else {
	          _this3.deleteFailure(node);
	        }
	      }, function () {
	        _this3.deleteFailure(node);
	      });
	    }
	  }, {
	    key: "deleteSuccess",
	    value: function deleteSuccess(node) {
	      if (!main_core.Type.isDomNode(node)) {
	        return;
	      }

	      new BX.fx({
	        time: 0.5,
	        step: 0.05,
	        type: 'linear',
	        start: node.offsetHeight,
	        finish: 56,
	        callback: function callback(height) {
	          node.style.height = "".concat(height, "px");
	        },
	        callback_start: function callback_start() {
	          node.style.overflow = 'hidden';
	          node.style.minHeight = 0;
	        },
	        callback_complete: function callback_complete() {
	          node.style.marginBottom = 0;
	          main_core.Dom.clean(node);
	          node.classList.add('feed-post-block-deleted');
	          node.appendChild(main_core.Tag.render(_templateObject$5 || (_templateObject$5 = babelHelpers.taggedTemplateLiteral(["<div class=\"feed-add-successfully\"><span class=\"feed-add-info-text\"><span class=\"feed-add-info-icon\"></span><span>", "</span></span></span></div>"])), main_core.Loc.getMessage('SONET_EXT_LIVEFEED_DELETE_SUCCESS')));
	        }
	      }).start();
	    }
	  }, {
	    key: "deleteFailure",
	    value: function deleteFailure(node) {
	      if (!main_core.Type.isDomNode(node)) {
	        return;
	      }

	      node.insertBefore(main_core.Tag.render(_templateObject2$2 || (_templateObject2$2 = babelHelpers.taggedTemplateLiteral(["<div class=\"feed-add-error\" style=\"margin: 18px 37px 4px 84px;\"><span class=\"feed-add-info-text\"><span class=\"feed-add-info-icon\"></span><span>", "</span></span></div>"])), main_core.Loc.getMessage('sonetLMenuDeleteFailure')), node.firstChild);
	    }
	  }, {
	    key: "setMoreButtons",
	    value: function setMoreButtons(value) {
	      this.moreButtonDataList = value;
	    }
	  }, {
	    key: "getMoreButtons",
	    value: function getMoreButtons() {
	      return this.moreButtonDataList;
	    }
	  }, {
	    key: "clearMoreButtons",
	    value: function clearMoreButtons() {
	      this.moreButtonDataList.clear();
	    }
	  }, {
	    key: "addMoreButton",
	    value: function addMoreButton(key, data) {
	      this.moreButtonDataList.set(key, data);
	    }
	  }, {
	    key: "setNoTasksNotificationRead",
	    value: function setNoTasksNotificationRead(event) {
	      var notificationNode = event.currentTarget.closest('.feed-notification-container');

	      if (!notificationNode) {
	        return;
	      }

	      main_core.ajax.runAction('socialnetwork.api.livefeed.readNoTasksNotification', {
	        data: {}
	      }).then(function (response) {
	        if (!response.data.success) {
	          return;
	        }

	        notificationNode.style.height = notificationNode.offsetHeight + 'px';
	        setTimeout(function () {
	          notificationNode.classList.add('feed-notification-container-collapsed');
	        }, 10);
	        setTimeout(function () {
	          notificationNode.parentNode.removeChild(notificationNode);
	        }, 250);
	      }, function () {});
	    }
	  }]);
	  return Feed;
	}();

	var FeedInstance = new Feed();
	var PinnedPanelInstance = new PinnedPanel();
	var InformerInstance = new Informer();
	var FilterInstance = new Filter();
	var PageInstance = new Page();
	var MoreButtonInstance = new MoreButton$$1();
	new TaskCreator();

	exports.FeedInstance = FeedInstance;
	exports.PinnedPanelInstance = PinnedPanelInstance;
	exports.InformerInstance = InformerInstance;
	exports.FilterInstance = FilterInstance;
	exports.PageInstance = PageInstance;
	exports.MoreButtonInstance = MoreButtonInstance;
	exports.Post = Post$$1;
	exports.TaskCreator = TaskCreator;
	exports.Loader = Loader;
	exports.MoreButton = MoreButton$$1;
	exports.ContentView = ContentView;
	exports.CommentForm = CommentForm;

}((this.BX.Livefeed = this.BX.Livefeed || {}),BX.UI,BX.Main,BX,BX.Event,BX.Tasks));
//# sourceMappingURL=livefeed.bundle.js.map
