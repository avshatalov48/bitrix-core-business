this.BX = this.BX || {};
(function (exports,main_popup,ui_buttons,main_core,main_core_events) {
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

	function _templateObject() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"", "\" bx-data-log-id=\"", "\">\n\t\t\t\t\t<div class=\"feed-post-cancel-pinned-panel-inner\">\n\t\t\t\t\t\t<div class=\"feed-post-cancel-pinned-content\">\n\t\t\t\t\t\t\t<span class=\"", "\">", "</span>\n\t\t\t\t\t\t\t<span class=\"feed-post-cancel-pinned-text\">", "</span>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<button class=\"ui-btn ui-btn-light-border ui-btn-round ui-btn-sm ", "\">", "</button>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\t\n\t\t\t\t"]);

	  _templateObject = function _templateObject() {
	    return data;
	  };

	  return data;
	}

	var PinnedPanel = /*#__PURE__*/function () {
	  function PinnedPanel() {
	    babelHelpers.classCallCheck(this, PinnedPanel);
	    this.class = {
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
	    /* for detail page without pinned panel */

	    this.initPosts();
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
	      this.options = babelHelpers.objectSpread({}, this.options, options);
	    }
	  }, {
	    key: "getOption",
	    value: function getOption(optionName) {
	      return this.options[optionName];
	    }
	  }, {
	    key: "initPanel",
	    value: function initPanel() {
	      var _this = this;

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
	        var pinnedPanelNode = _this.getPanelNode();

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

	        _this.hideCollapsedPanel();
	      });
	      main_core.Event.bind(pinnedPanelNode, 'click', function (event) {
	        var likeClicked = event.target.classList.contains('feed-inform-ilike') || event.target.closest('.feed-inform-ilike') !== null;
	        var followClicked = event.target.classList.contains('feed-inform-follow') || event.target.closest('.feed-inform-follow') !== null;
	        var menuClicked = event.target.classList.contains('feed-post-more-link') || event.target.closest('.feed-post-more-link') !== null || event.target.classList.contains('feed-post-right-top-menu');
	        var contentViewClicked = event.target.classList.contains('feed-inform-contentview') || event.target.closest('.feed-inform-contentview') !== null;
	        var pinClicked = event.target.classList.contains("".concat(_this.class.pin)) || event.target.closest(".".concat(_this.class.pin)) !== null;
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

	        if (postNode.classList.contains("".concat(_this.class.postPinned))) {
	          if (!likeClicked && !followClicked && !menuClicked && !contentViewClicked && !pinClicked) {
	            postNode.classList.remove("".concat(_this.class.postPinned));
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
	            var anchorNode = postNode.querySelector(".".concat(_this.class.postComments, " a[name=comments]"));

	            if (anchorNode) {
	              var position = main_core.Dom.getPosition(anchorNode);
	              window.scrollTo(0, position.top - 200);
	            }
	          }

	          event.stopPropagation();
	          event.preventDefault();
	        } else if (collapseClicked) {
	          postNode.classList.add("".concat(_this.class.postPinned));
	          event.stopPropagation();
	          event.preventDefault();
	        }
	      });
	    }
	  }, {
	    key: "initPosts",
	    value: function initPosts() {
	      var _this2 = this;

	      if (this.postsInitialized) {
	        return;
	      }

	      var postList = document.querySelectorAll('[data-livefeed-post-pinned]');

	      if (postList.length > 0) {
	        this.postsInitialized = true;
	      }

	      Array.from(postList).forEach(function (post) {
	        main_core.Event.unbind(post, 'click', _this2.handlePostClick);
	        main_core.Event.bind(post, 'click', _this2.handlePostClick);
	      });
	    }
	  }, {
	    key: "handlePostClick",
	    value: function handlePostClick(event) {
	      if (!event.target.classList.contains("".concat(this.class.pin))) {
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

	        var _this3$getCommentsDat2 = _this3.getCommentsData(xmlId),
	            newValue = _this3$getCommentsDat2.newValue,
	            oldValue = _this3$getCommentsDat2.oldValue,
	            allValue = _this3$getCommentsDat2.allValue,
	            follow = _this3$getCommentsDat2.follow;

	        var commentsData = {
	          allValue: main_core.Type.isInteger(allValue) ? allValue + 1 : 1
	        };

	        if (parseInt(params.AUTHOR.ID) !== parseInt(BX.message('USER_ID'))) {
	          commentsData.newValue = main_core.Type.isInteger(newValue) ? newValue + 1 : 1;
	        } else {
	          commentsData.oldValue = main_core.Type.isInteger(oldValue) ? oldValue + 1 : 1;
	        }

	        _this3.setCommentsData(xmlId, commentsData);
	      });
	      main_core_events.EventEmitter.subscribe('OnUCommentWasDeleted', function (event) {
	        var _event$getData5 = event.getData(),
	            _event$getData6 = babelHelpers.slicedToArray(_event$getData5, 3),
	            xmlId = _event$getData6[0],
	            id = _event$getData6[1],
	            data = _event$getData6[2];

	        var _this3$getCommentsDat3 = _this3.getCommentsData(xmlId),
	            oldValue = _this3$getCommentsDat3.oldValue,
	            allValue = _this3$getCommentsDat3.allValue;

	        _this3.setCommentsData(xmlId, {
	          allValue: main_core.Type.isInteger(allValue) ? allValue - 1 : 0,
	          oldValue: main_core.Type.isInteger(oldValue) ? oldValue - 1 : 0
	        });
	      });
	    }
	  }, {
	    key: "changePinned",
	    value: function changePinned(params) {
	      var _this4 = this;

	      var logId = params.logId ? parseInt(params.logId) : 0;
	      var event = params.event ? params.event : null;
	      var node = params.node ? params.node : null;
	      var newState = params.newState ? params.newState : null;
	      var panelNode = this.getPanelNode();

	      if (!node && !event && logId > 0 && panelNode) {
	        node = panelNode.querySelector(".".concat(this.class.post, " > [data-livefeed-id=\"").concat(logId, "\"]"));
	      }

	      if (!node && event) {
	        node = event.target;
	      }

	      return new Promise(function (resolve, reject) {
	        if (!!_this4.getOption('pinBlocked') || !node || !newState) {
	          return resolve();
	        }

	        _this4.setPostState({
	          node: node,
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
	              node: node,
	              state: newState === 'Y' ? 'N' : 'Y'
	            });

	            return resolve();
	          } else {
	            _this4.movePost({
	              node: node,
	              state: newState
	            }).then(function () {
	              return resolve();
	            });
	          }
	        }, function (response) {
	          _this4.setPostState({
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
	        post.classList.add("".concat(this.class.postPinActive));
	      } else {
	        post.classList.remove("".concat(this.class.postPinActive));
	      }

	      var pin = post.querySelector(".".concat(this.class.pin));

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
	          }
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
	      var _this5 = this;

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

	        var pinnedPanelNode = _this5.getPanelNode();

	        if (!pinnedPanelNode) {
	          return resolve();
	        }

	        var postToMove = post.parentNode.classList.contains("".concat(_this5.class.post)) ? post.parentNode : post;

	        if (state === 'Y') {
	          var originalPostHeight = postToMove.offsetHeight;
	          postToMove.setAttribute('bx-data-height', originalPostHeight);

	          _this5.getPinnedData({
	            logId: logId
	          }).then(function (data) {
	            var pinnedPanelTitleNode = post.querySelector('.feed-post-pinned-title');
	            var pinnedPanelDescriptionNode = post.querySelector('.feed-post-pinned-desc');
	            var pinnedPanelPinNode = post.querySelector(".".concat(_this5.class.pin));

	            if (pinnedPanelTitleNode) {
	              pinnedPanelTitleNode.innerHTML = data.TITLE;
	            }

	            if (pinnedPanelDescriptionNode) {
	              pinnedPanelDescriptionNode.innerHTML = data.DESCRIPTION;
	            }

	            if (pinnedPanelPinNode) {
	              pinnedPanelPinNode.title = main_core.Loc.getMessage('SONET_EXT_LIVEFEED_PIN_TITLE_Y');
	            }

	            post.classList.add("".concat(_this5.class.postPinnedHide));

	            var cancelPinnedPanel = _this5.getCancelPinnedPanel({
	              logId: logId
	            });

	            var anchor = postToMove.nextSibling;
	            anchor.parentNode.insertBefore(cancelPinnedPanel, anchor);

	            _this5.centerCancelPinnedPanelElements({
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

	            var panelNode = _this5.getPanelNode();

	            if (panelNode) {
	              _this5.setOptions({
	                panelHeight: panelNode.offsetHeight
	              });
	            } // list.post::hide.start, cancelPanel::show.start


	            setTimeout(function () {
	              postToMove.classList.add("".concat(_this5.class.postHide));
	              Utils.setStyle(cancelPinnedPanel, {
	                height: '53px'
	              });
	              Utils.setStyle(postToMove, {
	                height: 0,
	                opacity: 0
	              });

	              _this5.setOptions({
	                pinBlocked: true
	              });
	            }, 100); // list.post::hide.end

	            main_core.Event.unbindAll(postToMove, 'transitionend');
	            main_core.Event.bind(postToMove, 'transitionend', function (event) {
	              if (!_this5.checkTransitionProperty(event, 'height')) {
	                return;
	              }

	              main_core.Event.unbindAll(postToMove, 'transitionend');
	              var panelPostsNode = pinnedPanelNode.querySelector(".".concat(_this5.class.panelPosts));
	              panelPostsNode.insertBefore(postToMove, panelPostsNode.firstChild);

	              _this5.adjustCollapsedPostsPanel();

	              postToMove.classList.remove("".concat(_this5.class.postHide));
	              post.classList.remove("".concat(_this5.class.postPinnedHide));

	              _this5.adjustPanel();

	              _this5.showCollapsedPostsPanel(); // pinnedPanel.post::show.start


	              setTimeout(function () {
	                post.classList.add("".concat(_this5.class.postPinned));
	                Utils.setStyle(postToMove, {
	                  position: '',
	                  width: '',
	                  height: '80px',
	                  backgroundColor: '',
	                  opacity: 1
	                });

	                _this5.setOptions({
	                  pinBlocked: false
	                });

	                setTimeout(function () {
	                  postToMove.classList.remove("".concat(_this5.class.postHide));
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
	          var cancelPinnedPanel = document.querySelector(".".concat(_this5.class.cancelPanel, "[bx-data-log-id=\"").concat(logId, "\"]"));

	          if (main_core.Type.isDomNode(cancelPinnedPanel)) {
	            Utils.setStyle(postToMove, {
	              height: pinnedHeight + 'px'
	            }); // pinnedPanel.post::hide.start, cancelPanel::show.start

	            requestAnimationFrame(function () {
	              postToMove.classList.add("".concat(_this5.class.postExpanding));
	              cancelPinnedPanel.classList.add("".concat(_this5.class.postExpanding));
	              Utils.setStyle(postToMove, {
	                opacity: 0,
	                height: 0
	              });
	              Utils.setStyle(cancelPinnedPanel, {
	                opacity: 0,
	                height: 0
	              });
	            });
	            var collapsed = pinnedPanelNode.classList.contains("".concat(_this5.class.panelCollapsed));

	            if (collapsed) {
	              cancelPinnedPanel.parentNode.insertBefore(postToMove, cancelPinnedPanel.nextSibling);

	              _this5.adjustCollapsedPostsPanel();

	              _this5.adjustPanel();
	            }

	            var showCollapsed = _this5.getCollapsedPanelNode().classList.contains("".concat(_this5.class.collapsedPanelShow));

	            if (showCollapsed) {
	              _this5.hideCollapsedPostsPanel(); // cancelPanel::show.end


	              main_core.Event.unbindAll(cancelPinnedPanel, 'transitionend');
	              main_core.Event.bind(cancelPinnedPanel, 'transitionend', function (event) {
	                if (!_this5.checkTransitionProperty(event, 'height')) {
	                  return;
	                }

	                Utils.setStyle(postToMove, {
	                  transform: '',
	                  display: 'block'
	                });

	                _this5.animateCancel({
	                  post: post,
	                  postToMove: postToMove,
	                  cancelPinnedPanel: cancelPinnedPanel,
	                  height: height
	                });
	              });
	            } // pinnedPanel.post::hide.end


	            main_core.Event.unbindAll(postToMove, 'transitionend');
	            main_core.Event.bind(postToMove, 'transitionend', function (event) {
	              if (!_this5.checkTransitionProperty(event, 'opacity')) {
	                return;
	              }

	              if (!collapsed) {
	                cancelPinnedPanel.parentNode.insertBefore(postToMove, cancelPinnedPanel.nextSibling);

	                _this5.adjustCollapsedPostsPanel();

	                _this5.adjustPanel();
	              }

	              _this5.animateCancel({
	                post: post,
	                postToMove: postToMove,
	                cancelPinnedPanel: cancelPinnedPanel,
	                height: height
	              });
	            });
	          } else {
	            post.classList.remove("".concat(_this5.class.postPinned));
	            pinnedPanelNode.parentNode.insertBefore(postToMove, pinnedPanelNode.nextSibling);

	            _this5.adjustPanel();

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
	              if (!_this5.checkTransitionProperty(event, 'height')) {
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
	      var _this6 = this;

	      var post = _ref.post,
	          postToMove = _ref.postToMove,
	          cancelPinnedPanel = _ref.cancelPinnedPanel,
	          height = _ref.height;
	      post.classList.remove("".concat(this.class.postPinned)); // post.list:show.start, cancelPanel::hide.start

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
	        if (!_this6.checkTransitionProperty(event, 'height')) {
	          return;
	        }

	        post.classList.remove("".concat(_this6.class.postPinnedHide));
	        Utils.setStyle(postToMove, {
	          marginBottom: '',
	          height: ''
	        });
	        Utils.setStyle(cancelPinnedPanel, {
	          marginBottom: '',
	          height: ''
	        });
	        postToMove.classList.remove("".concat(_this6.class.postExpanding));
	        cancelPinnedPanel.classList.remove("".concat(_this6.class.postExpanding));
	      });
	    }
	  }, {
	    key: "getCancelPinnedPanel",
	    value: function getCancelPinnedPanel(params) {
	      var _this7 = this;

	      var logId = params.logId ? parseInt(params.logId) : 0;

	      if (logId <= 0) {
	        return null;
	      }

	      var cancelPinnedPanel = document.querySelector(".".concat(this.class.cancelPanel, "[bx-data-log-id=\"").concat(logId, "\"]"));

	      if (!main_core.Type.isDomNode(cancelPinnedPanel)) {
	        cancelPinnedPanel = main_core.Tag.render(_templateObject(), this.class.cancelPanel, logId, this.class.cancelPanelLabel, main_core.Loc.getMessage('SONET_EXT_LIVEFEED_PINNED_CANCEL_TITLE'), main_core.Loc.getMessage('SONET_EXT_LIVEFEED_PINNED_CANCEL_DESCRIPTION'), this.class.cancelPanelButton, main_core.Loc.getMessage('SONET_EXT_LIVEFEED_PINNED_CANCEL_BUTTON'));
	        main_core.Event.bind(cancelPinnedPanel.querySelector(".".concat(this.class.cancelPanelButton)), 'click', function () {
	          _this7.changePinned({
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
	      Utils.setStyle(cancelPinnedPanel.querySelector(".".concat(this.class.cancelPanelLabel)), {
	        marginLeft: cancelPinnedPanel.querySelector(".".concat(this.class.cancelPanelButton)).getBoundingClientRect().width + 'px'
	      });
	    }
	  }, {
	    key: "getPostsCount",
	    value: function getPostsCount() {
	      var panelNode = this.getPanelNode();
	      return panelNode ? Array.from(panelNode.getElementsByClassName("".concat(this.class.post))).length : 0;
	    }
	  }, {
	    key: "hidePinnedItems",
	    value: function hidePinnedItems() {
	      var _this8 = this;

	      var pinnedPanelNode = this.getPanelNode();

	      if (!pinnedPanelNode) {
	        return;
	      }

	      Utils.setStyle(pinnedPanelNode, {
	        height: parseInt(this.getOption('panelHeight')) + 'px'
	      });
	      Array.from(pinnedPanelNode.getElementsByClassName("".concat(this.class.post))).reduce(function (count, item) {
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
	          if (!_this8.checkTransitionProperty(event, 'transform')) {
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
	      var _this9 = this;

	      var pinnedPanelNode = this.getPanelNode();

	      if (!pinnedPanelNode) {
	        return;
	      }

	      Array.from(pinnedPanelNode.getElementsByClassName("".concat(this.class.post))).map(function (item, currentIndex, originalItemsList) {
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
	          if (!_this9.checkTransitionProperty(event, 'transform')) {
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
	      var _this10 = this;

	      // collapsedPanel::hide.start
	      requestAnimationFrame(function () {
	        var collapsedPanel = _this10.getCollapsedPanelNode();

	        Utils.setStyle(collapsedPanel, {
	          position: 'absolute',
	          top: 0,
	          width: '100%',
	          opacity: 0
	        });
	        collapsedPanel.classList.remove("".concat(_this10.class.collapsedPanelHide));
	        collapsedPanel.classList.add("".concat(_this10.class.collapsedPanelShow)); // collapsedPanel::show.start

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
	      var _this11 = this;

	      var postsCounter = this.getPostsCount();
	      var postsCounterNode = this.getCollapsedPanelNode().querySelector(".".concat(this.class.collapsedPanelCounterPostsValue));

	      if (postsCounterNode) {
	        postsCounterNode.innerHTML = parseInt(postsCounter);
	      }

	      var commentsCounterNode = this.getCollapsedPanelNode().querySelector(".".concat(this.class.collapsedPanelCounterComments));
	      var commentsCounterValueNode = this.getCollapsedPanelNode().querySelector(".".concat(this.class.collapsedPanelCounterCommentsValue));
	      var panelNode = this.getPanelNode();

	      if (commentsCounterNode && commentsCounterValueNode && panelNode) {
	        var newCommentCounter = Array.from(panelNode.querySelectorAll(".".concat(this.class.collapsedPanelCounterCommentsValueNewValue))).reduce(function (acc, node) {
	          return acc + (node.closest(".".concat(_this11.class.postUnfollowed)) ? 0 : parseInt(node.innerHTML));
	        }, 0);
	        commentsCounterValueNode.innerHTML = newCommentCounter;

	        if (newCommentCounter > 0) {
	          commentsCounterNode.classList.add("".concat(this.class.collapsedPanelCounterCommentsShown));
	        } else {
	          commentsCounterNode.classList.remove("".concat(this.class.collapsedPanelCounterCommentsShown));
	        }
	      }
	    }
	  }, {
	    key: "adjustPanel",
	    value: function adjustPanel() {
	      var _this12 = this;

	      var panelNode = this.getPanelNode();

	      if (!panelNode) {
	        return;
	      }

	      setTimeout(function () {
	        if (_this12.getPostsCount() > 0) {
	          panelNode.classList.add("".concat(_this12.class.panelNonEmpty));
	        } else {
	          panelNode.classList.remove("".concat(_this12.class.panelNonEmpty));
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
	        this.getPanelNode().classList.remove("".concat(this.class.panelCollapsed));
	        this.removeCollapsedPanel();
	        this.showPinnedItems();
	      }
	    }
	  }, {
	    key: "showCollapsedPanel",
	    value: function showCollapsedPanel() {
	      this.getPanelNode().classList.add("".concat(this.class.panelCollapsed));
	      this.animateCollapsedPanel();
	    }
	  }, {
	    key: "hideCollapsedPanel",
	    value: function hideCollapsedPanel() {
	      this.getPanelNode().classList.remove("".concat(this.class.panelCollapsed));
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
	      collapsedPanel.classList.remove("".concat(this.class.collapsedPanelShow));
	      collapsedPanel.classList.add("".concat(this.class.collapsedPanelHide));
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

	      var commentsNode = document.querySelector(".".concat(this.class.postComments, "[data-bx-comments-entity-xml-id=\"").concat(xmlId, "\"]"));

	      if (!commentsNode) {
	        return result;
	      }

	      var postNode = commentsNode.closest(".".concat(this.class.postPinActive));

	      if (!postNode) {
	        return result;
	      }

	      var newPinnedCommentsNode = postNode.querySelector(".".concat(this.class.collapsedPanelCounterCommentsValueNew));
	      var newValuePinnedCommentsNode = postNode.querySelector(".".concat(this.class.collapsedPanelCounterCommentsValueNewValue));
	      var oldPinnedCommentsNode = postNode.querySelector(".".concat(this.class.collapsedPanelCounterCommentsValueOld));
	      var allPinnedCommentsNode = postNode.querySelector(".".concat(this.class.collapsedPanelCounterCommentsValueAll));

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

	        if (value.newValue > 0 && !newNode.classList.contains("".concat(this.class.collapsedPanelCounterCommentsValueNewActive))) {
	          newNode.classList.add("".concat(this.class.collapsedPanelCounterCommentsValueNewActive));
	        } else if (value.newValue <= 0 && newNode.classList.contains("".concat(this.class.collapsedPanelCounterCommentsValueNewActive))) {
	          newNode.classList.remove("".concat(this.class.collapsedPanelCounterCommentsValueNewActive));
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
	      return this.getPanelNode().querySelector(".".concat(this.class.collapsedPanel));
	    }
	  }, {
	    key: "checkTransitionProperty",
	    value: function checkTransitionProperty(event, propertyName) {
	      return event.propertyName === propertyName;
	    }
	  }]);
	  return PinnedPanel;
	}();

	function _templateObject$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div>", "</div>"]);

	  _templateObject$1 = function _templateObject() {
	    return data;
	  };

	  return data;
	}

	var Post = /*#__PURE__*/function () {
	  function Post() {
	    babelHelpers.classCallCheck(this, Post);
	  }

	  babelHelpers.createClass(Post, [{
	    key: "showBackgroundWarning",
	    value: function showBackgroundWarning(_ref) {
	      var urlToEdit = _ref.urlToEdit,
	          menuPopupWindow = _ref.menuPopupWindow;
	      var content = main_core.Tag.render(_templateObject$1(), main_core.Loc.getMessage('SONET_EXT_LIVEFEED_POST_BACKGROUND_EDIT_WARNING_DESCRIPTION'));
	      var dialog = new main_popup.Popup('create id here', null, {
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
	  }]);
	  return Post;
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
	    this.class = {
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
	            params = _event$getData2[1];

	        if (command !== 'user_counter' || !params[_this.currentSiteId] || !params[_this.currentSiteId][_this.currentCounterType]) {
	          return;
	        }

	        _this.changeCounter(main_core.Runtime.clone(params[_this.currentSiteId][_this.currentCounterType]));
	      });
	      main_core_events.EventEmitter.subscribe('onImUpdateCounter', function (event) {
	        var _event$getData3 = event.getData(),
	            _event$getData4 = babelHelpers.slicedToArray(_event$getData3, 1),
	            arCount = _event$getData4[0];

	        if (!main_core.Type.isObjectLike(arCount) || main_core.Type.isUndefined(arCount[_this.currentCounterType])) {
	          return;
	        }

	        _this.changeCounter(arCount[_this.currentCounterType]);
	      });
	      main_core_events.EventEmitter.subscribe('OnUCCommentWasRead', function (event) {
	        var _event$getData5 = event.getData(),
	            _event$getData6 = babelHelpers.slicedToArray(_event$getData5, 3),
	            xmlId = _event$getData6[0],
	            id = _event$getData6[1],
	            options = _event$getData6[2];

	        if (!main_core.Type.isObjectLike(options) || !options.live || !options.new) {
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
	        this.decrementStack = 0;
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

	      var counterTextNode = this.container.querySelector("span.".concat(this.class.counterText));
	      var reloadNode = this.container.querySelector("span.".concat(this.class.reloadContainer));

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

	        if (this.plus && reloadNode && reloadNode.style.display !== 'none' && counterTextNode) {
	          reloadNode.style.display = 'none';
	          counterTextNode.style.display = 'inline-block';
	          this.plus.classList.remove("".concat(this.class.plusHidden));
	        }
	      } else if (this.wrap) {
	        if (zeroCounterFromDb && this.wrap.classList.contains("".concat(this.class.informerAnimation))) {
	          if (counterTextNode && reloadNode) {
	            counterTextNode.style.display = 'none';
	            reloadNode.style.display = 'inline-block';
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
	      this.wrap.classList.add("".concat(this.class.informerAnimation));
	    }
	  }, {
	    key: "hideWrapAnimation",
	    value: function hideWrapAnimation() {
	      if (!this.wrap) {
	        return;
	      }

	      this.wrap.classList.remove("".concat(this.class.informerAnimation));
	      this.wrap.style.visibility = 'hidden';
	    }
	  }, {
	    key: "showReloadAnimation",
	    value: function showReloadAnimation() {
	      if (!this.container) {
	        return;
	      }

	      var counterWaiterNode = this.container.querySelector("span.".concat(this.class.icon));

	      if (counterWaiterNode) {
	        counterWaiterNode.classList.add(this.class.iconRotating);
	      }
	    }
	  }, {
	    key: "hideReloadAnimation",
	    value: function hideReloadAnimation() {
	      if (!this.container) {
	        return;
	      }

	      var counterNodeWaiter = this.container.querySelector("span.".concat(this.class.icon));

	      if (counterNodeWaiter) {
	        counterNodeWaiter.classList.remove(this.class.iconRotating);
	      }
	    }
	  }, {
	    key: "onFeedScroll",
	    value: function onFeedScroll() {
	      if (!this.container || !this.wrap) {
	        return;
	      }

	      var top = this.wrap.parentNode.getBoundingClientRect().top;
	      var counterRect = this.container.getBoundingClientRect();

	      if (top <= 0) {
	        if (!this.wrap.classList.contains("".concat(this.class.informerFixed))) {
	          this.container.style.left = "".concat(counterRect.left + counterRect.width / 2, "px");
	        }

	        this.fixWrap();
	      } else {
	        this.unfixWrap();
	        this.container.style.left = 'auto';
	      }
	    }
	  }, {
	    key: "fixWrap",
	    value: function fixWrap() {
	      if (!this.wrap) {
	        return;
	      }

	      this.wrap.classList.add("".concat(this.class.informerFixed), "".concat(this.class.informerFixedAnimation));
	    }
	  }, {
	    key: "unfixWrap",
	    value: function unfixWrap() {
	      if (!this.wrap) {
	        return;
	      }

	      this.wrap.classList.remove("".concat(this.class.informerFixed), "".concat(this.class.informerFixedAnimation));
	    }
	  }, {
	    key: "recover",
	    value: function recover() {
	      if (!this.container) {
	        return;
	      }

	      var counterContainerNode = this.container.querySelector("span.".concat(this.class.counterContainer));

	      if (!counterContainerNode) {
	        return;
	      }

	      counterContainerNode.style.display = 'inline-block';
	      this.hideReloadNode();

	      if (this.plus) {
	        this.plus.classList.add("".concat(this.class.plusHidden));
	      }
	    }
	  }, {
	    key: "hideReloadNode",
	    value: function hideReloadNode() {
	      if (!this.container) {
	        return;
	      }

	      var reloadContainerNode = this.container.querySelector("span.".concat(this.class.reloadContainer));

	      if (!reloadContainerNode) {
	        return;
	      }

	      reloadContainerNode.style.display = 'none';
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
	  }]);
	  return Informer;
	}();

	var Feed = /*#__PURE__*/function () {
	  function Feed() {
	    babelHelpers.classCallCheck(this, Feed);
	    this.entryData = {};
	    this.feedInitialized = false;
	  }

	  babelHelpers.createClass(Feed, [{
	    key: "init",
	    value: function init() {
	      if (this.feedInitialized) {
	        return;
	      }

	      exports.PinnedPanelInstance.init();
	      exports.InformerInstance.init();
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
	      }, function (response) {
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
	  }]);
	  return Feed;
	}();

	exports.FeedInstance = null;
	exports.PinnedPanelInstance = null;
	exports.PostInstance = null;
	exports.InformerInstance = null;
	main_core.Event.ready(function () {
	  exports.FeedInstance = new Feed();
	  exports.PinnedPanelInstance = new PinnedPanel();
	  exports.PostInstance = new Post();
	  exports.InformerInstance = new Informer();
	});

}((this.BX.Livefeed = this.BX.Livefeed || {}),BX.Main,BX.UI,BX,BX.Event));
//# sourceMappingURL=livefeed.bundle.js.map
