this.BX = this.BX || {};
(function (exports,main_core,main_popup,main_core_events) {
	'use strict';

	var ListPopup = /*#__PURE__*/function () {
	  function ListPopup() {
	    babelHelpers.classCallCheck(this, ListPopup);
	  }

	  babelHelpers.createClass(ListPopup, null, [{
	    key: "getListPopup",
	    value: function getListPopup(params) {
	      var _this = this;

	      var likeId = params.likeId;
	      var likeInstance = RatingLike$1.getInstance(likeId);
	      var target = params.target;
	      var reaction = params.reaction;
	      var nodeId = params.nodeId;

	      if (this.popupLikeId === likeId) {
	        return false;
	      }

	      if (likeInstance.popupContentPage != 1) {
	        return;
	      }

	      this.List(likeId, 1, reaction, true);
	      likeInstance.popupTimeoutIdShow = setTimeout(function () {
	        _this.getListPopupShow({
	          likeId: likeId,
	          reaction: reaction,
	          target: target,
	          nodeId: nodeId
	        });
	      }, 100);
	    }
	  }, {
	    key: "getListPopupShow",
	    value: function getListPopupShow(params) {
	      var _this2 = this;

	      var likeId = params.likeId;
	      var likeInstance = RatingLike$1.getInstance(likeId);
	      var target = params.target;
	      var reaction = params.reaction;
	      var nodeId = params.nodeId;
	      likeInstance.resultPopupAnimation = true;
	      setTimeout(function () {
	        _this2.getListPopupAnimation({
	          likeId: likeId
	        });
	      }, 500);

	      if (likeInstance.mouseInShowPopupNode[reaction]) {
	        this.OpenWindow(likeId, null, target, nodeId);
	      }
	    }
	  }, {
	    key: "getListPopupAnimation",
	    value: function getListPopupAnimation(params) {
	      var likeId = params.likeId;
	      var likeInstance = RatingLike$1.getInstance(likeId);
	      likeInstance.resultPopupAnimation = false;
	    }
	  }, {
	    key: "OpenWindow",
	    value: function OpenWindow(likeId, clickEvent, target, targetId) {
	      var _this3 = this;

	      var likeInstance = RatingLike$1.getInstance(likeId);

	      if (Number(likeInstance.countText.innerHTML) === 0) {
	        return;
	      }

	      var bindNode = likeInstance.template === 'standart' ? likeInstance.count : likeInstance.version === 2 ? main_core.Type.isDomNode(target) ? target : main_core.Type.isStringFilled(targetId) && document.getElementById(targetId) ? document.getElementById(targetId) : null : likeInstance.box;

	      if (!main_core.Type.isDomNode(bindNode)) {
	        return;
	      }

	      if (likeInstance.popup == null) {
	        var globalZIndex = this.getGlobalIndex(bindNode);
	        var popupClassNameList = [];

	        if (likeInstance.topPanel) {
	          popupClassNameList.push('bx-ilike-wrap-block-react-wrap');
	        }

	        if (RatingManager.mobile) {
	          popupClassNameList.push('bx-ilike-mobile-wrap');
	        }

	        likeInstance.popup = new main_popup.Popup({
	          id: "ilike-popup-".concat(likeId),
	          bindElement: bindNode,
	          lightShadow: true,
	          offsetTop: 0,
	          offsetLeft: !main_core.Type.isUndefined(clickEvent) && !main_core.Type.isNull(clickEvent) && !main_core.Type.isUndefined(clickEvent.offsetX) ? clickEvent.offsetX - 100 : likeInstance.version == 2 ? -30 : 5,
	          autoHide: true,
	          closeByEsc: true,
	          zIndexAbsolute: globalZIndex > 1000 ? globalZIndex + 1 : 1000,
	          bindOptions: {
	            position: 'top'
	          },
	          animation: 'fading-slide',
	          events: {
	            onPopupClose: function onPopupClose() {
	              _this3.popupLikeId = null;
	            },
	            onPopupDestroy: function onPopupDestroy() {}
	          },
	          content: document.getElementById("bx-ilike-popup-cont-".concat(likeId)),
	          className: popupClassNameList.join(' ')
	        });

	        if (!likeInstance.topPanel && !RatingManager.mobile) {
	          likeInstance.popup.setAngle({});
	          document.getElementById("ilike-popup-".concat(likeId)).addEventListener('mouseout', function () {
	            clearTimeout(likeInstance.popupTimeout);
	            likeInstance.popupTimeout = setTimeout(function () {
	              likeInstance.popup.close();
	            }, 1000);
	          });
	          document.getElementById("ilike-popup-".concat(likeId)).addEventListener('mouseover', function () {
	            clearTimeout(likeInstance.popupTimeout);
	          });
	        }
	      } else {
	        if (!main_core.Type.isUndefined(clickEvent) && !main_core.Type.isNull(clickEvent) && !main_core.Type.isUndefined(clickEvent.offsetX)) {
	          likeInstance.popup.offsetLeft = clickEvent.offsetX - 100;
	        }

	        likeInstance.popup.setBindElement(bindNode);
	      }

	      if (this.popupLikeId !== likeId) {
	        var popupLikeInstance = RatingLike$1.getInstance(this.popupLikeId);

	        if (popupLikeInstance) {
	          popupLikeInstance.popup.close();
	        }
	      }

	      this.popupLikeId = likeId;
	      likeInstance.popup.show();
	      this.AdjustWindow(likeId);
	    }
	  }, {
	    key: "getGlobalIndex",
	    value: function getGlobalIndex(element) {
	      var index = 0;
	      var propertyValue = '';

	      do {
	        propertyValue = main_core.Dom.style(element, 'z-index');

	        if (propertyValue !== 'auto') {
	          index = !Number.isNaN(parseInt(propertyValue)) ? index : 0;
	        }

	        element = element.offsetParent;
	      } while (element && element.tagName !== 'BODY');

	      return index;
	    }
	  }, {
	    key: "removeOnClose",
	    value: function removeOnClose() {
	      main_core_events.EventEmitter.unsubscribe(BX.SidePanel.Instance.getTopSlider().getWindow(), 'SidePanel.Slider:onClose', this.removeOnCloseHandler);
	      var popupLikeInstance = RatingLike$1.getInstance(this.popupLikeId);

	      if (popupLikeInstance) {
	        popupLikeInstance.popup.close();
	      }
	    }
	  }, {
	    key: "AdjustWindow",
	    value: function AdjustWindow(likeId) {
	      var likeInstance = RatingLike$1.getInstance(likeId);

	      if (!likeInstance.popup) {
	        return;
	      }

	      likeInstance.popup.bindOptions.forceBindPosition = true;
	      likeInstance.popup.adjustPosition();
	      likeInstance.popup.bindOptions.forceBindPosition = false;
	    }
	  }, {
	    key: "PopupScroll",
	    value: function PopupScroll(likeId) {
	      var _this4 = this;

	      var likeInstance = RatingLike$1.getInstance(likeId);
	      var contentContainerNodeList = likeInstance.popupContent.querySelectorAll('.bx-ilike-popup-content'); // reactions

	      if (contentContainerNodeList.length <= 0) {
	        contentContainerNodeList = [likeInstance.popupContent];
	      }

	      contentContainerNodeList.forEach(function (contentContainerNode) {
	        contentContainerNode.addEventListener('scroll', function (e) {
	          if (e.target.scrollTop <= (e.target.scrollHeight - e.target.offsetHeight) / 1.5) {
	            return;
	          }

	          _this4.List(likeId, null, likeInstance.version == 2 ? RatingRender.popupCurrentReaction : false);

	          main_core.Event.unbindAll(e.target);
	        });
	      });
	    }
	  }, {
	    key: "List",
	    value: function List(likeId, page, reaction, clear) {
	      var _this5 = this;

	      var likeInstance = RatingLike$1.getInstance(likeId);

	      if (Number(likeInstance.countText.innerHTML) === 0) {
	        return false;
	      }

	      reaction = main_core.Type.isStringFilled(reaction) ? reaction : '';

	      if (main_core.Type.isNull(page)) {
	        page = likeInstance.version === 2 ? !main_core.Type.isUndefined(RatingRender.popupPagesList[reaction]) ? RatingRender.popupPagesList[reaction] : 1 : likeInstance.popupContentPage;
	      }

	      if (clear && Number(page) === 1 && likeInstance.version === 2) {
	        RatingRender.clearPopupContent({
	          likeId: likeId
	        });
	      }

	      if (likeInstance.listXHR) {
	        likeInstance.listXHR.abort();
	      }

	      main_core.ajax.runAction('main.rating.list', {
	        data: {
	          params: {
	            RATING_VOTE_TYPE_ID: likeInstance.entityTypeId,
	            RATING_VOTE_ENTITY_ID: likeInstance.entityId,
	            RATING_VOTE_LIST_PAGE: page,
	            RATING_VOTE_REACTION: reaction === 'all' ? '' : reaction,
	            PATH_TO_USER_PROFILE: likeInstance.pathToUserProfile
	          }
	        },
	        onrequeststart: function onrequeststart(xhr) {
	          likeInstance.listXHR = xhr;
	        }
	      }).then(function (result) {
	        _this5.onListSuccess(result.data, {
	          likeId: likeId,
	          reaction: reaction,
	          page: page,
	          clear: clear
	        });
	      }, function () {});
	      return false;
	    }
	  }, {
	    key: "onListSuccess",
	    value: function onListSuccess(data, params) {
	      if (!data) {
	        return false;
	      }

	      var likeInstance = RatingLike$1.getInstance(params.likeId);
	      likeInstance.countText.innerHTML = data.items_all;

	      if (Number(data.items_page) === 0) {
	        likeInstance.popup.close();
	        return false;
	      }

	      if (likeInstance.version === 2) {
	        RatingRender.buildPopupContent({
	          likeId: params.likeId,
	          reaction: params.reaction,
	          rating: likeInstance,
	          page: params.page,
	          data: data,
	          clear: params.clear
	        });
	        likeInstance.topPanel.setAttribute('data-popup', 'Y');
	      } else {
	        RatingRender.buildPopupContentNoReactions({
	          rating: likeInstance,
	          page: params.page,
	          data: data
	        });
	      }

	      this.AdjustWindow(params.likeId);
	      this.PopupScroll(params.likeId);
	    }
	  }, {
	    key: "onResultClick",
	    value: function onResultClick(params) {
	      var likeId = main_core.Type.isStringFilled(params.likeId) ? params.likeId : false;
	      var clickEvent = !main_core.Type.isUndefined(params.event) ? params.event : false;
	      var reaction = main_core.Type.isStringFilled(params.reaction) ? params.reaction : '';
	      var likeInstance = RatingLike$1.getInstance(likeId);

	      if (likeInstance.resultPopupAnimation) {
	        return;
	      }

	      if (likeInstance.popup && likeInstance.popup.isShown()) {
	        likeInstance.popup.close();
	      } else {
	        clearTimeout(likeInstance.popupTimeoutIdList);
	        clearTimeout(likeInstance.popupTimeoutIdShow);

	        if (likeInstance.popupContentPage == 1 && (likeInstance.topPanel.getAttribute('data-popup') !== 'Y' || likeInstance.popupCurrentReaction != reaction)) {
	          this.List(likeId, 1, reaction, true);
	        }

	        this.OpenWindow(likeId, clickEvent.currentTarget === likeInstance.count ? null : clickEvent, clickEvent.currentTarget, clickEvent.currentTarget.id);
	      }
	    }
	  }, {
	    key: "onResultMouseEnter",
	    value: function onResultMouseEnter(params) {
	      var _this6 = this;

	      var likeId = main_core.Type.isStringFilled(params.likeId) ? params.likeId : false;
	      var mouseEnterEvent = !main_core.Type.isUndefined(params.event) ? params.event : null;
	      var reaction = main_core.Type.isStringFilled(params.reaction) ? params.reaction : '';
	      var nodeId = mouseEnterEvent && main_core.Type.isStringFilled(mouseEnterEvent.currentTarget.id) ? mouseEnterEvent.currentTarget.id : '';
	      var likeInstance = RatingLike$1.getInstance(likeId);
	      likeInstance.mouseInShowPopupNode[reaction] = true;
	      clearTimeout(likeInstance.popupTimeoutIdList);
	      clearTimeout(likeInstance.popupTimeoutIdShow);
	      likeInstance.popupTimeoutIdList = setTimeout(function () {
	        _this6.getListPopup({
	          likeId: likeId,
	          target: mouseEnterEvent.currentTarget,
	          reaction: reaction,
	          nodeId: nodeId
	        });
	      }, 300);
	    }
	  }, {
	    key: "onResultMouseLeave",
	    value: function onResultMouseLeave(params) {
	      var likeId = main_core.Type.isStringFilled(params.likeId) ? params.likeId : false;
	      var reaction = main_core.Type.isStringFilled(params.reaction) ? params.reaction : '';
	      var likeInstance = RatingLike$1.getInstance(likeId);
	      likeInstance.mouseInShowPopupNode[reaction] = false;
	      likeInstance.resultPopupAnimation = false;
	    }
	  }]);
	  return ListPopup;
	}();
	babelHelpers.defineProperty(ListPopup, "popupLikeId", null);
	babelHelpers.defineProperty(ListPopup, "removeOnCloseHandler", ListPopup.removeOnClose.bind(ListPopup));

	var RatingRender = /*#__PURE__*/function () {
	  function RatingRender() {
	    babelHelpers.classCallCheck(this, RatingRender);
	  }

	  babelHelpers.createClass(RatingRender, null, [{
	    key: "getTopUsersText",
	    value: function getTopUsersText(params) {
	      var currentUserId = Number(main_core.Loc.getMessage('USER_ID'));
	      var you = !main_core.Type.isUndefined(params.you) ? !!params.you : false;
	      var topList = !main_core.Type.isUndefined(params.top) && main_core.Type.isArray(params.top) ? params.top : [];
	      var more = !main_core.Type.isUndefined(params.more) ? Number(params.more) : 0;
	      var result = '';

	      if (topList.length <= 0 && !you && (RatingManager.mobile || more <= 0)) {
	        return result;
	      }

	      if (RatingManager.mobile) {
	        if (you) {
	          topList.push({
	            ID: currentUserId,
	            NAME_FORMATTED: main_core.Loc.getMessage('RATING_LIKE_TOP_TEXT3_YOU'),
	            WEIGHT: 1
	          });
	        }

	        result = main_core.Loc.getMessage("RATING_LIKE_TOP_TEXT3_".concat(topList.length > 1 ? '2' : '1')).replace('#OVERFLOW_START#', RatingManager.mobile ? '<span class="feed-post-emoji-text-item-overflow">' : '').replace('#OVERFLOW_END#', RatingManager.mobile ? '</span>' : '');
	      } else {
	        result = main_core.Loc.getMessage("RATING_LIKE_TOP_TEXT2_".concat(you ? 'YOU_' : '').concat(topList.length).concat(more > 0 ? '_MORE' : '')).replace('#OVERFLOW_START#', RatingManager.mobile ? '<span class="feed-post-emoji-text-item-overflow">' : '').replace('#OVERFLOW_END#', RatingManager.mobile ? '</span>' : '').replace('#MORE_START#', RatingManager.mobile ? '<span class="feed-post-emoji-text-item-more">' : '&nbsp;').replace('#MORE_END#', RatingManager.mobile ? '</span>' : '');
	      }

	      if (RatingManager.mobile) {
	        topList.sort(function (a, b) {
	          if (parseInt(a.ID) === currentUserId) {
	            return -1;
	          }

	          if (parseInt(b.ID) === currentUserId) {
	            return 1;
	          }

	          if (parseFloat(a.WEIGHT) === parseFloat(b.WEIGHT)) {
	            return 0;
	          }

	          return parseFloat(a.WEIGHT) > parseFloat(b.WEIGHT) ? -1 : 1;
	        });
	        var userNameList = topList.map(function (item) {
	          return item.NAME_FORMATTED;
	        });
	        var userNameBegin = '';
	        var userNameEnd = '';

	        if (userNameList.length === 1) {
	          userNameBegin = userNameList.pop();
	          userNameEnd = '';
	        } else {
	          userNameBegin = userNameList.slice(0, userNameList.length - 1).join(main_core.Loc.getMessage('RATING_LIKE_TOP_TEXT3_USERLIST_SEPARATOR').replace(/#USERNAME#/g, ''));
	          userNameEnd = userNameList[userNameList.length - 1];
	        }

	        result = result.replace('#USER_LIST_BEGIN#', userNameBegin).replace('#USER_LIST_END#', userNameEnd);
	      } else {
	        topList.forEach(function (item, i) {
	          result = result.replace("#USER_".concat(Number(i) + 1, "#"), "<span class=\"feed-post-emoji-text-item\">".concat(item.NAME_FORMATTED, "</span>"));
	        });
	        result = result.replace('#USERS_MORE#', '<span class="feed-post-emoji-text-item">' + more + '</span>');
	      }

	      return result;
	    }
	  }, {
	    key: "getUserReaction",
	    value: function getUserReaction(params) {
	      return main_core.Type.isDomNode(params.userReactionNode) ? params.userReactionNode.getAttribute('data-value') : '';
	    }
	  }, {
	    key: "setReaction",
	    value: function setReaction(params) {
	      if (main_core.Type.isUndefined(params.rating) || !main_core.Type.isStringFilled(params.likeId)) {
	        return;
	      }

	      var action = main_core.Type.isStringFilled(params.action) ? params.action : 'add';

	      if (!['add', 'cancel', 'change'].includes(action)) {
	        return;
	      }

	      var likeId = params.likeId;
	      var rating = params.rating;
	      var userReaction = main_core.Type.isStringFilled(params.userReaction) ? params.userReaction : main_core.Loc.getMessage('RATING_LIKE_REACTION_DEFAULT');
	      var userReactionOld = main_core.Type.isStringFilled(params.userReactionOld) ? params.userReactionOld : main_core.Loc.getMessage('RATING_LIKE_REACTION_DEFAULT');

	      if (action === 'change' && userReaction === userReactionOld) {
	        return;
	      }

	      var totalCount = !main_core.Type.isUndefined(params.totalCount) ? Number(params.totalCount) : null;
	      var currentUserId = Number(main_core.Loc.getMessage('USER_ID'));
	      var userId = !main_core.Type.isUndefined(params.userId) ? Number(params.userId) : currentUserId;
	      var userReactionNode = this.getNode(rating.userReactionNode);
	      var reactionsNode = this.getNode(rating.reactionsNode);
	      var topPanel = this.getNode(rating.topPanel);
	      var topPanelContainer = this.getNode(rating.topPanelContainer);
	      var topUsersText = this.getNode(rating.topUsersText);
	      var countText = this.getNode(rating.countText);
	      var buttonText = this.getNode(rating.buttonText);

	      if (userId === currentUserId // not pull
	      && userReactionNode) {
	        userReactionNode.setAttribute('data-value', ['add', 'change'].includes(action) ? userReaction : '');
	      }
	      var elements = [];
	      var elementsNew = [];

	      if (totalCount !== null && topPanel && topUsersText && reactionsNode) {
	        if (totalCount > 0) {
	          topPanelContainer.classList.add('feed-post-emoji-top-panel-container-active');

	          if (!topPanel.classList.contains('feed-post-emoji-container-toggle')) {
	            topPanel.classList.add('feed-post-emoji-container-toggle');
	            topUsersText.classList.add('feed-post-emoji-move-to-right');
	            reactionsNode.classList.add('feed-post-emoji-icon-box-show');
	          }
	        } else if (totalCount <= 0) {
	          topPanelContainer.classList.remove('feed-post-emoji-top-panel-container-active');

	          if (topPanel.classList.contains('feed-post-emoji-container-toggle')) {
	            topPanel.classList.remove('feed-post-emoji-container-toggle');
	            topUsersText.classList.remove('feed-post-emoji-move-to-right');
	            reactionsNode.classList.remove('feed-post-emoji-icon-box-show');
	          }
	        }
	      }

	      if (totalCount !== null && countText) {
	        if (totalCount <= 0 && !countText.classList.contains('feed-post-emoji-text-counter-invisible')) {
	          countText.classList.add('feed-post-emoji-text-counter-invisible');
	        } else if (totalCount > 0 && countText.classList.contains('feed-post-emoji-text-counter-invisible')) {
	          countText.classList.remove('feed-post-emoji-text-counter-invisible');
	        }
	      }

	      if (reactionsNode) {
	        var reactionsContainer = reactionsNode.querySelector('.feed-post-emoji-icon-container');
	        elements = reactionsNode.querySelectorAll('.feed-post-emoji-icon-item');

	        if (reactionsContainer) {
	          var found = false;
	          var newValue = false;
	          elements.forEach(function (element) {
	            var reactionValue = element.getAttribute('data-reaction');
	            var reactionCount = Number(element.getAttribute('data-value'));

	            if (reactionValue === userReaction) {
	              found = true;

	              if (action === 'cancel') {
	                newValue = reactionCount > 0 ? reactionCount - 1 : 0;
	              } else if (['add', 'change'].includes(action)) {
	                newValue = reactionCount + 1;
	              }

	              if (newValue > 0) {
	                elementsNew.push({
	                  reaction: reactionValue,
	                  count: newValue,
	                  animate: false
	                });
	              }
	            } else if (action === 'change' && reactionValue === userReactionOld) {
	              newValue = reactionCount > 0 ? reactionCount - 1 : 0;

	              if (newValue > 0) {
	                elementsNew.push({
	                  reaction: reactionValue,
	                  count: newValue,
	                  animate: false
	                });
	              }
	            } else {
	              elementsNew.push({
	                reaction: reactionValue,
	                count: reactionCount,
	                animate: false
	              });
	            }
	          });

	          if (['add', 'change'].includes(action) && !found) {
	            elementsNew.push({
	              reaction: userReaction,
	              count: 1,
	              animate: true
	            });
	          }

	          main_core.Dom.clean(reactionsContainer);

	          if (topPanel) {
	            if (elementsNew.length > 0) {
	              topPanel.classList.add('feed-post-emoji-container-nonempty');
	            } else {
	              topPanel.classList.remove('feed-post-emoji-container-nonempty');
	            }

	            if (RatingManager.mobile) {
	              var commentNode = topPanel.closest('.post-comment-block');

	              if (commentNode) {
	                if (elementsNew.length > 0) {
	                  commentNode.classList.add('comment-block-rating-nonempty');
	                } else {
	                  commentNode.classList.remove('comment-block-rating-nonempty');
	                }
	              }
	            }
	          }

	          this.drawReactions({
	            likeId: likeId,
	            container: reactionsContainer,
	            data: elementsNew
	          });
	        }
	      }

	      if (userId === currentUserId && buttonText) {
	        if (['add', 'change'].includes(action)) {
	          buttonText.innerHTML = main_core.Loc.getMessage("RATING_LIKE_EMOTION_".concat(userReaction.toUpperCase(), "_CALC"));

	          if (RatingManager.mobile) {
	            buttonText.parentElement.className = '';
	            buttonText.parentElement.classList.add('bx-ilike-left-wrap', 'bx-you-like-button', "bx-you-like-button-".concat(userReaction.toLowerCase()));
	          }
	        } else {
	          buttonText.innerHTML = main_core.Loc.getMessage('RATING_LIKE_EMOTION_LIKE_CALC');

	          if (RatingManager.mobile) {
	            buttonText.parentElement.className = 'bx-ilike-left-wrap';
	          }
	        }
	      }
	    }
	  }, {
	    key: "drawReactions",
	    value: function drawReactions(params) {
	      var container = main_core.Type.isDomNode(params.container) ? params.container : null;
	      var data = main_core.Type.isArray(params.data) ? params.data : [];
	      var likeId = main_core.Type.isStringFilled(params.likeId) ? params.likeId : '';

	      if (!container || !main_core.Type.isStringFilled(likeId)) {
	        return;
	      }

	      var reactionEvents = RatingManager.mobile ? {} : {
	        click: this.resultReactionClick.bind(this),
	        mouseenter: this.resultReactionMouseEnter.bind(this),
	        mouseleave: this.resultReactionMouseLeave.bind(this)
	      };
	      main_core.Dom.clean(container);
	      var reactionsData = {};
	      data.forEach(function (element, i) {
	        var classList = ['feed-post-emoji-icon-item', "feed-post-emoji-icon-".concat(element.reaction), "feed-post-emoji-icon-item-".concat(i + 1)];

	        if (element.animate) {
	          if (i >= 1) {
	            classList.push('feed-post-emoji-icon-animate');
	          } else if (data.length == 1) {
	            classList.push('feed-post-emoji-animation-pop');
	          }
	        }

	        container.appendChild(main_core.Dom.create('span', {
	          props: {
	            id: "bx-ilike-result-reaction-".concat(element.reaction, "-").concat(likeId),
	            className: classList.join(' ')
	          },
	          attrs: {
	            'data-reaction': element.reaction,
	            'data-value': element.count,
	            'data-like-id': likeId,
	            title: main_core.Loc.getMessage("RATING_LIKE_EMOTION_".concat(element.reaction.toUpperCase(), "_CALC"))
	          },
	          events: reactionEvents
	        }));
	        reactionsData[element.reaction] = element.count;
	      });
	      container.setAttribute('data-reactions-data', JSON.stringify(reactionsData));
	    }
	  }, {
	    key: "showReactionsPopup",
	    value: function showReactionsPopup(params) {
	      var _this = this;

	      var bindElement = this.getNode(params.bindElement);
	      var likeId = main_core.Type.isStringFilled(params.likeId) ? params.likeId : '';

	      if (!bindElement || !main_core.Type.isStringFilled(likeId)) {
	        return false;
	      }

	      this.reactionsPopupLikeId = likeId;

	      if (this.reactionsPopup === null) {
	        var reactionsNodesList = [];
	        this.reactionsList.forEach(function (currentEmotion) {
	          reactionsNodesList.push(main_core.Dom.create('div', {
	            props: {
	              className: "feed-post-emoji-icon-item feed-post-emoji-icon-".concat(currentEmotion)
	            },
	            attrs: {
	              'data-reaction': currentEmotion,
	              title: main_core.Loc.getMessage("RATING_LIKE_EMOTION_".concat(currentEmotion.toUpperCase(), "_CALC"))
	            }
	          }));
	        });
	        this.reactionsPopup = main_core.Dom.create('div', {
	          props: {
	            className: "feed-post-emoji-popup-container ".concat(RatingManager.mobile ? '--mobile' : '')
	          },
	          children: [main_core.Dom.create('div', {
	            props: {
	              className: 'feed-post-emoji-icon-inner'
	            },
	            children: reactionsNodesList
	          })]
	        });
	        this.reactionsPopup.addEventListener(RatingManager.mobile ? 'touchend' : 'click', function (e) {
	          var reactionNode = e.target.classList.contains('feed-post-emoji-icon-item') ? e.target : e.target.closest('.feed-post-emoji-icon-item');

	          if (reactionNode) {
	            RatingLike$1.ClickVote(e, _this.reactionsPopupLikeId, reactionNode.getAttribute('data-reaction'), true);
	          }

	          e.preventDefault();
	        });
	        main_core.Dom.append(this.reactionsPopup, document.body);
	      } else if (this.reactionsPopup.classList.contains('feed-post-emoji-popup-invisible')) {
	        this.reactionsPopup.classList.remove('feed-post-emoji-popup-invisible');
	      } else if (RatingManager.mobile && this.reactionsPopup.classList.contains('feed-post-emoji-popup-invisible-final-mobile')) {
	        this.reactionsPopup.classList.remove('feed-post-emoji-popup-invisible-final-mobile');
	      } else {
	        return;
	      }

	      this.reactionsPopupMouseOutHandler = this.getReactionsPopupMouseOutHandler(likeId);
	      var bindElementPosition = main_core.pos(bindElement);

	      if (bindElement.closest('.feed-com-informers-bottom') && bindElement.closest('.iframe-comments-cont, .task-iframe-popup')) {
	        bindElementPosition.left += 100;
	      }

	      var inverted = bindElementPosition.top - main_core.GetWindowSize().scrollTop < 80;
	      var deltaY = inverted ? 15 : -45;

	      if (inverted) {
	        this.reactionsPopup.classList.add('feed-post-emoji-popup-inverted');
	      } else {
	        this.reactionsPopup.classList.remove('feed-post-emoji-popup-inverted');
	      }

	      var likeInstance = RatingLike$1.getInstance(likeId);

	      if (RatingManager.mobile) {
	        this.touchMoveDeltaY = inverted ? 60 : -45;
	        main_core.Dom.adjust(this.reactionsPopup, {
	          style: {
	            left: '12px',
	            top: (inverted ? bindElementPosition.top - 23 : bindElementPosition.top - 28) + deltaY + 'px',
	            width: '330px',
	            borderRadius: '61px'
	          }
	        });
	        this.reactionsPopup.classList.remove('feed-post-emoji-popup-invisible-final');
	        this.reactionsPopup.classList.add('feed-post-emoji-popup-active-final');
	        this.reactionsPopup.classList.add('feed-post-emoji-popup-active-final-item');
	        likeInstance.box.classList.add('feed-post-emoji-control-active');
	        this.reactionsPopupMobileDisableScroll();
	      } else {
	        this.reactionsPopupAnimation = new BX.easing({
	          duration: 300,
	          start: {
	            width: 100,
	            left: bindElementPosition.left + bindElementPosition.width / 2 - 50,
	            top: (inverted ? bindElementPosition.top - 30 : bindElementPosition.top + 30) + deltaY,
	            borderRadius: 0,
	            opacity: 0
	          },
	          finish: {
	            width: 305,
	            left: bindElementPosition.left + bindElementPosition.width / 2 - 133,
	            top: bindElementPosition.top + deltaY - 5,
	            borderRadius: 50,
	            opacity: 100
	          },
	          transition: BX.easing.makeEaseInOut(BX.easing.transitions.cubic),
	          step: function step(state) {
	            _this.reactionsPopup.style.width = "".concat(state.width, "px");
	            _this.reactionsPopup.style.left = "".concat(state.left, "px");
	            _this.reactionsPopup.style.top = "".concat(state.top, "px");
	            _this.reactionsPopup.style.borderRadius = "".concat(state.borderRadius, "px");
	            _this.reactionsPopup.style.opacity = state.opacity / 100;
	            _this.reactionsPopupOpacityState = state.opacity;
	          },
	          complete: function complete() {
	            _this.reactionsPopup.style.opacity = '';

	            _this.reactionsPopup.classList.add('feed-post-emoji-popup-active-final');

	            likeInstance.box.classList.add('feed-post-emoji-control-active');
	          }
	        });
	        this.reactionsPopupAnimation.animate();
	        setTimeout(function () {
	          var reactions = _this.reactionsPopup.querySelectorAll('.feed-post-emoji-icon-item');

	          _this.reactionsPopupAnimation2 = new BX.easing({
	            duration: 140,
	            start: {
	              opacity: 0
	            },
	            finish: {
	              opacity: 100
	            },
	            transition: BX.easing.transitions.cubic,
	            step: function step(state) {
	              reactions[0].style.opacity = state.opacity / 100;
	              reactions[1].style.opacity = state.opacity / 100;
	              reactions[2].style.opacity = state.opacity / 100;
	              reactions[3].style.opacity = state.opacity / 100;
	              reactions[4].style.opacity = state.opacity / 100;
	              reactions[5].style.opacity = state.opacity / 100;
	              reactions[6].style.opacity = state.opacity / 100;
	            },
	            complete: function complete() {
	              _this.reactionsPopup.classList.add('feed-post-emoji-popup-active-final-item');

	              reactions[0].style.opacity = '';
	              reactions[1].style.opacity = '';
	              reactions[2].style.opacity = '';
	              reactions[3].style.opacity = '';
	              reactions[4].style.opacity = '';
	              reactions[5].style.opacity = '';
	              reactions[6].style.opacity = '';
	            }
	          });

	          _this.reactionsPopupAnimation2.animate();
	        }, 100);
	      }

	      if (!this.reactionsPopup.classList.contains('feed-post-emoji-popup-active')) {
	        this.reactionsPopup.classList.add('feed-post-emoji-popup-active');
	      }

	      if (!RatingManager.mobile) {
	        document.addEventListener('mousemove', this.reactionsPopupMouseOutHandler);
	      } else {
	        this.touchScrollTop = main_core.GetWindowSize().scrollTop;
	        this.hasMobileTouchMoved = null;
	        window.addEventListener('touchend', this.reactionsPopupMobileTouchEndHandler);
	        window.addEventListener('touchmove', this.reactionsPopupMobileTouchMoveHandler);
	      }
	    }
	  }, {
	    key: "reactionsPopupMobileTouchEnd",
	    value: function reactionsPopupMobileTouchEnd(e) {
	      var coords = {
	        x: e.changedTouches[0].pageX,
	        // e.touches[0].clientX + window.pageXOffset
	        y: e.changedTouches[0].pageY // e.touches[0].clientY + window.pageYOffset

	      };

	      if (this.hasMobileTouchMoved === true) {
	        var userReaction = null;
	        var reactionNode = this.reactionsPopupMobileGetHoverNode(coords.x, coords.y);

	        if (reactionNode && (userReaction = reactionNode.getAttribute('data-reaction'))) {
	          RatingLike$1.ClickVote(e, this.reactionsPopupLikeId, userReaction, true);
	        }

	        this.reactionsPopupMobileHideHandler();
	      } else // show reactions popup and handle clicks
	        {
	          window.addEventListener('touchend', this.reactionsPopupMobileHideHandler);
	        }

	      window.removeEventListener('touchend', this.reactionsPopupMobileTouchEndHandler);
	      window.removeEventListener('touchmove', this.reactionsPopupMobileTouchMoveHandler);
	      this.touchStartPosition = null;
	      e.preventDefault();
	    }
	  }, {
	    key: "reactionsPopupMobileTouchMove",
	    value: function reactionsPopupMobileTouchMove(e) {
	      var coords = {
	        x: e.touches[0].pageX,
	        // e.touches[0].clientX + window.pageXOffset
	        y: e.touches[0].pageY // e.touches[0].clientY + window.pageYOffset

	      };
	      this.touchCurrentPosition = {
	        x: coords.x,
	        y: coords.y
	      };

	      if (this.touchStartPosition === null) {
	        this.touchStartPosition = {
	          x: coords.x,
	          y: coords.y
	        };
	      } else {
	        if (this.hasMobileTouchMoved !== true) {
	          this.hasMobileTouchMoved = !this.reactionsPopupMobileCheckTouchMove();
	        }
	      }

	      if (this.hasMobileTouchMoved === true) {
	        var reactionNode = this.reactionsPopupMobileGetHoverNode(coords.x, coords.y);

	        if (reactionNode) {
	          if (this.currentReactionNodeHover && this.currentReactionNodeHover !== reactionNode) {
	            this.reactionsPopupMobileRemoveHover(this.currentReactionNodeHover);
	          }

	          this.reactionsPopupMobileAddHover(reactionNode);
	          this.currentReactionNodeHover = reactionNode;
	        } else if (this.currentReactionNodeHover) {
	          this.reactionsPopupMobileRemoveHover(this.currentReactionNodeHover);
	        }
	      } else {
	        if (this.currentReactionNodeHover) {
	          this.reactionsPopupMobileRemoveHover(this.currentReactionNodeHover);
	        }
	      }
	    }
	  }, {
	    key: "blockReactionsPopup",
	    value: function blockReactionsPopup() {
	      var _this2 = this;

	      if (this.blockShowPopupTimeout) {
	        window.clearTimeout(this.blockShowPopupTimeout);
	      }

	      this.blockShowPopup = true;
	      this.blockShowPopupTimeout = setTimeout(function () {
	        _this2.blockShowPopup = false;
	      }, 500);
	    }
	  }, {
	    key: "hideReactionsPopup",
	    value: function hideReactionsPopup(params) {
	      var _this3 = this;

	      var likeId = main_core.Type.isStringFilled(params.likeId) ? params.likeId : false;

	      if (this.reactionsPopup) {
	        if (RatingManager.mobile) {
	          this.reactionsPopup.classList.add('feed-post-emoji-popup-invisible-final');
	          this.reactionsPopup.classList.add('feed-post-emoji-popup-invisible-final-mobile');
	          this.reactionsPopup.classList.remove('feed-post-emoji-popup-active');
	          this.reactionsPopup.classList.remove('feed-post-emoji-popup-active-final');
	          this.reactionsPopup.classList.remove('feed-post-emoji-popup-active-final-item');
	          this.reactionsPopupMobileEnableScroll();
	        } else {
	          if (this.reactionsPopupAnimation) {
	            this.reactionsPopupAnimation.stop();
	          }

	          if (this.reactionsPopupAnimation2) {
	            this.reactionsPopupAnimation2.stop();
	          }

	          this.reactionsPopup.classList.add('feed-post-emoji-popup-invisible');
	          this.reactionsPopupAnimation4 = new BX.easing({
	            duration: 500,
	            start: {
	              opacity: this.reactionsPopupOpacityState
	            },
	            finish: {
	              opacity: 0
	            },
	            transition: BX.easing.transitions.linear,
	            step: function step(state) {
	              _this3.reactionsPopup.style.opacity = state.opacity / 100;
	              _this3.reactionsPopupOpacityState = state.opacity;
	            },
	            complete: function complete() {
	              _this3.reactionsPopup.style.opacity = '';

	              _this3.reactionsPopup.classList.add('feed-post-emoji-popup-invisible-final');

	              _this3.reactionsPopup.classList.remove('feed-post-emoji-popup-active');

	              _this3.reactionsPopup.classList.remove('feed-post-emoji-popup-active-final');

	              _this3.reactionsPopup.classList.remove('feed-post-emoji-popup-active-final-item');
	            }
	          });
	          this.reactionsPopupAnimation4.animate();
	        }

	        this.reactionsPopupLikeId = null;

	        if (likeId) {
	          RatingLike$1.getInstance(likeId).box.classList.remove('feed-post-emoji-control-active');
	        }
	      }

	      this.reactionsPopupMobileRemoveHover(this.currentReactionNodeHover);

	      if (likeId) {
	        this.bindReactionsPopup({
	          likeId: likeId
	        });
	      }
	    }
	  }, {
	    key: "reactionsPopupMobileCheckTouchMove",
	    value: function reactionsPopupMobileCheckTouchMove() {
	      if (this.touchStartPosition === null) {
	        return true;
	      } else {
	        if (Math.abs(this.touchCurrentPosition.x - this.touchStartPosition.x) > 5 || Math.abs(this.touchCurrentPosition.y - this.touchStartPosition.y) > 5) {
	          return false;
	        }
	      }

	      return true;
	    }
	  }, {
	    key: "reactionsPopupMobileHide",
	    value: function reactionsPopupMobileHide(e) {
	      window.removeEventListener('touchend', this.reactionsPopupMobileHideHandler);

	      if (this.reactionsPopupLikeId) {
	        this.hideReactionsPopup({
	          likeId: this.reactionsPopupLikeId
	        });

	        if (e) {
	          e.preventDefault();
	        }
	      }
	    }
	  }, {
	    key: "reactionsPopupMobileGetHoverNode",
	    value: function reactionsPopupMobileGetHoverNode(x, y) {
	      var reactionNode = null;
	      var userReaction = null;
	      var result = null;

	      if (((reactionNode = document.elementFromPoint(x, y + this.touchMoveDeltaY - this.touchScrollTop)) || ( // icon above/below a finger
	      reactionNode = document.elementFromPoint(x, y - this.touchScrollTop)) // icon is under a finger
	      ) && (userReaction = reactionNode.getAttribute('data-reaction')) && main_core.Type.isStringFilled(userReaction)) {
	        result = reactionNode;
	      }

	      return result;
	    }
	  }, {
	    key: "reactionsPopupMobileAddHover",
	    value: function reactionsPopupMobileAddHover(reactionNode) {
	      if (!reactionNode) {
	        return;
	      }

	      reactionNode.classList.add('feed-post-emoji-icon-item-hover');
	    }
	  }, {
	    key: "reactionsPopupMobileRemoveHover",
	    value: function reactionsPopupMobileRemoveHover(reactionNode) {
	      if (!reactionNode) {
	        return;
	      }

	      reactionNode.classList.remove('feed-post-emoji-icon-item-hover');
	    }
	  }, {
	    key: "reactionsPopupMobileEnableScroll",
	    value: function reactionsPopupMobileEnableScroll() {
	      document.removeEventListener('touchmove', this.touchMoveScrollListener, {
	        passive: false
	      });
	      main_core_events.EventEmitter.emit('onPullDownEnable');

	      if (this.mobileOverlay !== null) {
	        main_core.Dom.clean(this.mobileOverlay);
	        main_core.Dom.remove(this.mobileOverlay);
	        this.mobileOverlay = null;
	      }
	    }
	  }, {
	    key: "reactionsPopupMobileDisableScroll",
	    value: function reactionsPopupMobileDisableScroll() {
	      var _this4 = this;

	      document.addEventListener('touchmove', this.touchMoveScrollListener, {
	        passive: false
	      });
	      main_core_events.EventEmitter.emit('onPullDownDisable');

	      if (!main_core.Type.isNull(this.mobileOverlay)) {
	        return;
	      }

	      this.mobileOverlay = main_core.Dom.create('DIV', {
	        props: {
	          className: 'feed-post-emoji-popup-mobile-overlay'
	        }
	      });
	      setTimeout(function () {
	        if (main_core.Type.isNull(_this4.mobileOverlay)) {
	          return;
	        }

	        main_core.Dom.append(_this4.mobileOverlay, document.body);
	      }, 1000); // to avoid blink
	    }
	  }, {
	    key: "bindReactionsPopup",
	    value: function bindReactionsPopup(params) {
	      if (RatingManager.mobile) {
	        return false;
	      }

	      var likeId = main_core.Type.isStringFilled(params.likeId) ? params.likeId : '';

	      if (!main_core.Type.isStringFilled(likeId)) {
	        return false;
	      }

	      var likeInstance = RatingLike$1.getInstance(likeId);

	      if (!likeInstance) {
	        return false;
	      }

	      likeInstance.mouseOverHandler = main_core.Runtime.debounce(this.getMouseOverHandler(likeId), 500);
	      likeInstance.box.addEventListener('mouseenter', likeInstance.mouseOverHandler);
	      likeInstance.box.addEventListener('mouseleave', this.blockReactionsPopup);
	    }
	  }, {
	    key: "touchMoveScrollListener",
	    value: function touchMoveScrollListener(e) {
	      e.preventDefault();
	    }
	  }, {
	    key: "getReactionsPopupMouseOutHandler",
	    value: function getReactionsPopupMouseOutHandler(likeId) {
	      var _this5 = this;

	      return function (e) {
	        var popupPosition = _this5.reactionsPopup.getBoundingClientRect();

	        var inverted = _this5.reactionsPopup.classList.contains('feed-post-emoji-popup-inverted');

	        if (e.clientX >= popupPosition.left && e.clientX <= popupPosition.right && e.clientY >= popupPosition.top - (inverted ? 25 : 0) && e.clientY <= popupPosition.bottom + (inverted ? 0 : 25)) {
	          return;
	        }

	        _this5.blockReactionsPopup();

	        _this5.hideReactionsPopup({
	          likeId: likeId
	        });

	        document.removeEventListener('mousemove', _this5.reactionsPopupMouseOutHandler);
	        _this5.reactionsPopupMouseOutHandler = null;
	      };
	    }
	  }, {
	    key: "getMouseOverHandler",
	    value: function getMouseOverHandler(likeId) {
	      var _this6 = this;

	      return function () {
	        var likeInstance = RatingLike$1.getInstance(likeId);

	        if (!_this6.afterClickBlockShowPopup) {
	          if (_this6.blockShowPopup) {
	            return;
	          }

	          if (RatingManager.mobile) {
	            app.exec('callVibration');
	          }

	          _this6.showReactionsPopup({
	            bindElement: likeInstance.box,
	            likeId: likeId
	          });
	        }

	        likeInstance.box.removeEventListener('mouseenter', likeInstance.mouseOverHandler);
	        likeInstance.box.removeEventListener('mouseleave', _this6.blockReactionsPopup.bind(_this6));
	      };
	    }
	  }, {
	    key: "buildPopupContent",
	    value: function buildPopupContent(params) {
	      var _this7 = this;

	      var clear = params.clear ? Boolean(params.clear) : false;
	      var likeId = main_core.Type.isStringFilled(params.likeId) ? params.likeId : '';
	      var rating = params.rating;
	      var requestReaction = main_core.Type.isStringFilled(params.reaction) ? params.reaction : '';
	      var page = Number(params.page) > 0 ? Number(params.page) : 1;
	      var data = params.data;
	      var reactionsList = [];
	      var reactionsCount = 0;

	      if (clear && page === 1) {
	        this.clearPopupContent({
	          likeId: likeId
	        });
	      }

	      this.popupCurrentReaction = main_core.Type.isStringFilled(requestReaction) ? requestReaction : 'all';

	      if (requestReaction.length <= 0 || requestReaction == 'all') // first current tab
	        {
	          this.popupSizeInitialized = false;
	          document.getElementById("bx-ilike-popup-cont-".concat(likeId)).style.height = 'auto';
	          document.getElementById("bx-ilike-popup-cont-".concat(likeId)).style.minWidth = 'auto';
	        }

	      if (!main_core.Type.isStringFilled(requestReaction)) {
	        this.popupPagesList = {};
	      }

	      this.popupPagesList[requestReaction == '' ? 'all' : requestReaction] = page + 1;

	      if (main_core.Type.isPlainObject(data.reactions)) {
	        Object.entries(data.reactions).forEach(function (_ref) {
	          var _ref2 = babelHelpers.slicedToArray(_ref, 2),
	              reaction = _ref2[0],
	              count = _ref2[1];

	          if (Number(count) <= 0) {
	            return;
	          }

	          reactionsList.push({
	            reaction: reaction,
	            count: Number(count)
	          });
	          reactionsCount++;
	        });
	      }

	      var tabsNode = main_core.Dom.create('span', {
	        props: {
	          className: 'bx-ilike-popup-head'
	        }
	      });

	      if (reactionsCount > 1) {
	        var headClassList = ['bx-ilike-popup-head-item'];

	        if (!main_core.Type.isStringFilled(requestReaction) || requestReaction == 'all') {
	          headClassList.push('bx-ilike-popup-head-item-current');
	        }

	        tabsNode.appendChild(main_core.Dom.create('span', {
	          props: {
	            className: headClassList.join(' ')
	          },
	          children: [main_core.Dom.create('span', {
	            props: {
	              className: 'bx-ilike-popup-head-icon feed-post-emoji-icon-all'
	            }
	          }), main_core.Dom.create('span', {
	            props: {
	              className: 'bx-ilike-popup-head-text'
	            },
	            html: main_core.Loc.getMessage('RATING_LIKE_POPUP_ALL').replace('#CNT#', Number(data.items_all))
	          })],
	          events: {
	            click: function click(e) {
	              _this7.changePopupTab({
	                likeId: likeId,
	                rating: rating,
	                reaction: 'all'
	              });

	              e.preventDefault();
	            }
	          }
	        }));
	      }

	      if (reactionsCount === 0) {
	        reactionsList.push({
	          reaction: main_core.Loc.getMessage('RATING_LIKE_REACTION_DEFAULT'),
	          count: Number(data.items_all)
	        });
	      }

	      reactionsList.sort(function (a, b) {
	        var sample = {
	          like: 0,
	          kiss: 1,
	          laugh: 2,
	          wonder: 3,
	          cry: 4,
	          angry: 5,
	          facepalm: 6
	        };

	        if (sample[a.reaction] < sample[b.reaction]) {
	          return -1;
	        }

	        if (sample[a.reaction] > sample[b.reaction]) {
	          return 1;
	        }

	        return 0;
	      });
	      reactionsList.forEach(function (reactionData) {
	        var headItemClassList = ['bx-ilike-popup-head-item'];

	        if (requestReaction === reactionData.reaction) {
	          headItemClassList.push('bx-ilike-popup-head-item-current');
	        }

	        tabsNode.appendChild(main_core.Dom.create('span', {
	          props: {
	            className: headItemClassList.join(' ')
	          },
	          attrs: {
	            title: main_core.Loc.getMessage("RATING_LIKE_EMOTION_".concat(reactionData.reaction.toUpperCase(), "_CALC"))
	          },
	          children: [main_core.Dom.create('span', {
	            props: {
	              className: ['bx-ilike-popup-head-icon', 'feed-post-emoji-icon-item', "feed-post-emoji-icon-".concat(reactionData.reaction)].join(' ')
	            }
	          }), main_core.Dom.create('span', {
	            props: {
	              className: 'bx-ilike-popup-head-text'
	            },
	            html: reactionData.count
	          })],
	          events: {
	            click: function click(e) {
	              var popupContent = document.getElementById("bx-ilike-popup-cont-".concat(likeId));
	              var popupContentPosition = popupContent.getBoundingClientRect();

	              if (requestReaction.length <= 0 || requestReaction === 'all') // first current tab
	                {
	                  _this7.popupSizeInitialized = true;
	                  popupContent.style.height = "".concat(popupContentPosition.height, "px");
	                  popupContent.style.minWidth = "".concat(popupContentPosition.width, "px");
	                } else {
	                if (popupContentPosition.width > Number(popupContent.style.minWidth)) {
	                  popupContent.style.minWidth = "".concat(popupContentPosition.width, "px");
	                }
	              }

	              _this7.changePopupTab({
	                likeId: likeId,
	                rating: rating,
	                reaction: reactionData.reaction
	              });

	              e.preventDefault();
	            }
	          }
	        }));
	      });
	      var usersNode = rating.popupContent.querySelector('.bx-ilike-popup-content-container');
	      var usersNodeExists = false;

	      if (!usersNode) {
	        usersNode = main_core.Dom.create('span', {
	          props: {
	            className: 'bx-ilike-popup-content-container'
	          }
	        });
	      } else {
	        usersNodeExists = true;
	      }

	      usersNode.querySelectorAll('.bx-ilike-popup-content').forEach(function (contentNode) {
	        contentNode.classList.add('bx-ilike-popup-content-invisible');
	      });
	      var reactionUsersNode = usersNode.querySelector(".bx-ilike-popup-content-".concat(this.popupCurrentReaction));

	      if (!reactionUsersNode) {
	        reactionUsersNode = main_core.Dom.create('span', {
	          props: {
	            className: ['bx-ilike-popup-content', "bx-ilike-popup-content-".concat(this.popupCurrentReaction)].join(' ')
	          }
	        });
	        usersNode.appendChild(reactionUsersNode);
	      } else {
	        reactionUsersNode.classList.remove('bx-ilike-popup-content-invisible');
	      }

	      data.items.forEach(function (item) {
	        var userItemClassList = ['bx-ilike-popup-user-item'];

	        if (main_core.Type.isStringFilled(item.USER_TYPE)) {
	          userItemClassList.push("bx-ilike-popup-user-item-".concat(item.USER_TYPE));
	        }

	        reactionUsersNode.appendChild(main_core.Dom.create('a', {
	          props: {
	            className: userItemClassList.join(' ')
	          },
	          attrs: {
	            href: item.URL,
	            target: '_blank'
	          },
	          children: [main_core.Dom.create('span', {
	            props: {
	              className: 'bx-ilike-popup-user-icon'
	            },
	            style: main_core.Type.isStringFilled(item.PHOTO_SRC) ? {
	              'background-image': "url(\"".concat(item.PHOTO_SRC, "\")")
	            } : {}
	          }), main_core.Dom.create('span', {
	            props: {
	              className: 'bx-ilike-popup-user-name'
	            },
	            html: item.FULL_NAME
	          }), main_core.Dom.create('span', {
	            props: {
	              className: 'bx-ilike-popup-user-status'
	            }
	          })]
	        }));
	      });
	      var waitNode = rating.popupContent.querySelector('.bx-ilike-wait');

	      if (waitNode) {
	        main_core.Dom.clean(waitNode);
	        main_core.Dom.remove(waitNode);
	      }

	      var tabsNodeOld = rating.popupContent.querySelector('.bx-ilike-popup-head');

	      if (tabsNodeOld) {
	        tabsNodeOld.parentNode.insertBefore(tabsNode, tabsNodeOld);
	        tabsNodeOld.parentNode.removeChild(tabsNodeOld);
	      } else {
	        rating.popupContent.appendChild(tabsNode);
	      }

	      if (!usersNodeExists) {
	        rating.popupContent.appendChild(usersNode);
	      }
	    }
	  }, {
	    key: "clearPopupContent",
	    value: function clearPopupContent(params) {
	      var likeId = main_core.Type.isStringFilled(params.likeId) ? params.likeId : '';
	      var likeInstance = RatingLike$1.getInstance(likeId);
	      likeInstance.popupContent.innerHTML = '';
	      document.getElementById("bx-ilike-popup-cont-".concat(likeId)).style.height = 'auto';
	      document.getElementById("bx-ilike-popup-cont-".concat(likeId)).style.minWidth = 'auto';
	      likeInstance.popupContent.appendChild(main_core.Dom.create('span', {
	        props: {
	          className: 'bx-ilike-wait'
	        }
	      }));
	    }
	  }, {
	    key: "changePopupTab",
	    value: function changePopupTab(params) {
	      var likeId = main_core.Type.isStringFilled(params.likeId) ? params.likeId : '';
	      var rating = params.rating;
	      var reaction = main_core.Type.isStringFilled(params.reaction) ? params.reaction : '';
	      var contentContainerNode = rating.popupContent.querySelector('.bx-ilike-popup-content-container');

	      if (!contentContainerNode) {
	        return false;
	      }

	      var reactionUsersNode = contentContainerNode.querySelector('.bx-ilike-popup-content-' + reaction);

	      if (reactionUsersNode) {
	        this.popupCurrentReaction = main_core.Type.isStringFilled(reaction) ? reaction : 'all';
	        rating.popupContent.querySelectorAll('.bx-ilike-popup-head-item').forEach(function (tabNode) {
	          tabNode.classList.remove('bx-ilike-popup-head-item-current');
	          var reactionTabNode = tabNode.querySelector(".feed-post-emoji-icon-".concat(reaction));

	          if (reactionTabNode) {
	            tabNode.classList.add('bx-ilike-popup-head-item-current');
	          }
	        });
	        contentContainerNode.querySelectorAll('.bx-ilike-popup-content').forEach(function (contentNode) {
	          contentNode.classList.add('bx-ilike-popup-content-invisible');
	        });
	        reactionUsersNode.classList.remove('bx-ilike-popup-content-invisible');
	      } else {
	        ListPopup.List(likeId, 1, reaction);
	      }
	    }
	  }, {
	    key: "buildPopupContentNoReactions",
	    value: function buildPopupContentNoReactions(params) {
	      var page = Number(params.page) > 0 ? Number(params.page) : 1;
	      var likeInstance = !main_core.Type.isUndefined(params.rating) ? params.rating : null;
	      var data = params.data;

	      if (!likeInstance) {
	        return false;
	      }

	      if (page === 1) {
	        likeInstance.popupContent.innerHTML = '';
	        likeInstance.popupContent.appendChild(main_core.Dom.create('span', {
	          props: {
	            className: 'bx-ilike-bottom_scroll'
	          }
	        }));
	      }

	      likeInstance.popupContentPage += 1;
	      data.items.forEach(function (item) {
	        var avatarNode = null;

	        if (main_core.Type.isStringFilled(item.PHOTO_SRC)) {
	          avatarNode = main_core.Dom.create('img', {
	            attrs: {
	              src: item.PHOTO_SRC
	            },
	            props: {
	              className: 'bx-ilike-popup-avatar-img'
	            }
	          });
	        } else {
	          avatarNode = main_core.Dom.create('img', {
	            attrs: {
	              src: '/bitrix/images/main/blank.gif'
	            },
	            props: {
	              className: 'bx-ilike-popup-avatar-img bx-ilike-popup-avatar-img-default'
	            }
	          });
	        }

	        var imgClassList = ['bx-ilike-popup-img'];

	        if (main_core.Type.isStringFilled(item.USER_TYPE)) {
	          imgClassList.push("bx-ilike-popup-img-".concat(item.USER_TYPE));
	        }

	        likeInstance.popupContent.appendChild(main_core.Dom.create('a', {
	          attrs: {
	            href: item.URL,
	            target: '_blank'
	          },
	          props: {
	            className: imgClassList.join(' ')
	          },
	          children: [main_core.Dom.create('span', {
	            props: {
	              className: 'bx-ilike-popup-avatar-new'
	            },
	            children: [avatarNode, main_core.Dom.create('span', {
	              props: {
	                className: 'bx-ilike-popup-avatar-status-icon'
	              }
	            })]
	          }), main_core.Dom.create('span', {
	            props: {
	              className: 'bx-ilike-popup-name-new'
	            },
	            html: item.FULL_NAME
	          })]
	        }));
	      });
	    }
	  }, {
	    key: "afterClick",
	    value: function afterClick(params) {
	      var likeId = main_core.Type.isStringFilled(params.likeId) ? params.likeId : '';

	      if (!main_core.Type.isStringFilled(likeId)) {
	        return;
	      }

	      this.afterClickBlockShowPopup = true;
	      this.afterClickHandler = this.getAfterClickHandler(likeId);
	      RatingLike$1.getInstance(likeId).box.addEventListener('mouseleave', this.afterClickHandler);
	    }
	  }, {
	    key: "getAfterClickHandler",
	    value: function getAfterClickHandler(likeId) {
	      var _this8 = this;

	      return function () {
	        _this8.afterClickBlockShowPopup = false;
	        RatingLike$1.getInstance(likeId).box.removeEventListener('mouseleave', _this8.afterClickHandler);
	      };
	    }
	  }, {
	    key: "resultReactionClick",
	    value: function resultReactionClick(e) {
	      var likeId = e.currentTarget.getAttribute('data-like-id');
	      var reaction = e.currentTarget.getAttribute('data-reaction');

	      if (!main_core.Type.isSet(reaction)) {
	        reaction = '';
	      }

	      ListPopup.onResultClick({
	        likeId: likeId,
	        event: e,
	        reaction: reaction
	      });
	      e.stopPropagation();
	    }
	  }, {
	    key: "resultReactionMouseEnter",
	    value: function resultReactionMouseEnter(e) {
	      var likeId = e.currentTarget.getAttribute('data-like-id');
	      var reaction = e.currentTarget.getAttribute('data-reaction');
	      ListPopup.onResultMouseEnter({
	        likeId: likeId,
	        event: e,
	        reaction: reaction
	      });
	    }
	  }, {
	    key: "resultReactionMouseLeave",
	    value: function resultReactionMouseLeave(e) {
	      var likeId = e.currentTarget.getAttribute('data-like-id');
	      var reaction = e.currentTarget.getAttribute('data-reaction');
	      ListPopup.onResultMouseLeave({
	        likeId: likeId,
	        reaction: reaction
	      });
	    }
	  }, {
	    key: "openMobileReactionsPage",
	    value: function openMobileReactionsPage(params) {
	      BXMobileApp.PageManager.loadPageBlank({
	        url: "".concat(main_core.Loc.getMessage('SITE_DIR'), "mobile/like/result.php"),
	        title: main_core.Loc.getMessage('RATING_LIKE_RESULTS'),
	        backdrop: {
	          mediumPositionPercent: 65
	        },
	        cache: true,
	        data: {
	          entityTypeId: params.entityTypeId,
	          entityId: params.entityId
	        }
	      });
	    }
	  }, {
	    key: "onRatingLike",
	    value: function onRatingLike(eventData) {
	      RatingLike$1.repo.forEach(function (likeInstance, likeId) {
	        if (likeInstance.entityTypeId !== eventData.entityTypeId && Number(likeInstance.entityId) !== Number(eventData.entityId)) {
	          return;
	        }

	        var voteAction = main_core.Type.isStringFilled(eventData.voteAction) ? eventData.voteAction.toUpperCase() : 'ADD';
	        voteAction = voteAction === 'PLUS' ? 'ADD' : voteAction;

	        if (Number(eventData.userId) === Number(main_core.Loc.getMessage('USER_ID')) && likeInstance.button) {
	          if (voteAction === 'CANCEL') {
	            likeInstance.button.classList.remove('bx-you-like-button');
	          } else {
	            likeInstance.button.classList.add('bx-you-like-button');
	          }
	        }

	        RatingLike$1.Draw(likeId, {
	          TYPE: voteAction,
	          USER_ID: eventData.userId,
	          ENTITY_TYPE_ID: eventData.entityTypeId,
	          ENTITY_ID: eventData.entityId,
	          USER_DATA: eventData.userData,
	          REACTION: eventData.voteReaction,
	          REACTION_OLD: eventData.voteReactionOld,
	          TOTAL_POSITIVE_VOTES: eventData.itemsAll
	        });
	      });
	    }
	  }, {
	    key: "onMobileCommentsGet",
	    value: function onMobileCommentsGet() {
	      var ratingEmojiSelectorPopup = document.querySelector('.feed-post-emoji-popup-container');

	      if (ratingEmojiSelectorPopup) {
	        ratingEmojiSelectorPopup.style.top = 0;
	        ratingEmojiSelectorPopup.style.left = 0;
	        ratingEmojiSelectorPopup.classList.remove('feed-post-emoji-popup-active');
	        ratingEmojiSelectorPopup.classList.remove('feed-post-emoji-popup-active-final');
	        ratingEmojiSelectorPopup.classList.remove('feed-post-emoji-popup-active-final-item');
	        ratingEmojiSelectorPopup.classList.add('feed-post-emoji-popup-invisible-final');
	        ratingEmojiSelectorPopup.classList.add('feed-post-emoji-popup-invisible-final-mobile');
	      }
	    }
	  }, {
	    key: "getNode",
	    value: function getNode(node) {
	      if (main_core.Type.isDomNode(node)) {
	        return node;
	      } else if (main_core.Type.isStringFilled(node)) {
	        return document.getElementById(node);
	      } else {
	        return null;
	      }
	    }
	  }]);
	  return RatingRender;
	}();
	babelHelpers.defineProperty(RatingRender, "reactionsList", ['like', 'kiss', 'laugh', 'wonder', 'cry', 'angry', 'facepalm']);
	babelHelpers.defineProperty(RatingRender, "popupCurrentReaction", false);
	babelHelpers.defineProperty(RatingRender, "popupPagesList", []);
	babelHelpers.defineProperty(RatingRender, "popupSizeInitialized", false);
	babelHelpers.defineProperty(RatingRender, "blockShowPopup", false);
	babelHelpers.defineProperty(RatingRender, "blockShowPopupTimeout", false);
	babelHelpers.defineProperty(RatingRender, "afterClickBlockShowPopup", false);
	babelHelpers.defineProperty(RatingRender, "afterClickHandler", null);
	babelHelpers.defineProperty(RatingRender, "touchStartPosition", null);
	babelHelpers.defineProperty(RatingRender, "touchCurrentPosition", {
	  x: null,
	  y: null
	});
	babelHelpers.defineProperty(RatingRender, "currentReactionNodeHover", null);
	babelHelpers.defineProperty(RatingRender, "touchMoveDeltaY", null);
	babelHelpers.defineProperty(RatingRender, "touchScrollTop", 0);
	babelHelpers.defineProperty(RatingRender, "hasMobileTouchMoved", null);
	babelHelpers.defineProperty(RatingRender, "mobileOverlay", null);
	babelHelpers.defineProperty(RatingRender, "reactionsPopup", null);
	babelHelpers.defineProperty(RatingRender, "reactionsPopupAnimation", null);
	babelHelpers.defineProperty(RatingRender, "reactionsPopupAnimation2", null);
	babelHelpers.defineProperty(RatingRender, "reactionsPopupLikeId", null);
	babelHelpers.defineProperty(RatingRender, "reactionsPopupMouseOutHandler", null);
	babelHelpers.defineProperty(RatingRender, "reactionsPopupOpacityState", 0);
	babelHelpers.defineProperty(RatingRender, "reactionsPopupTouchStartIn", null);
	babelHelpers.defineProperty(RatingRender, "reactionsPopupPositionY", null);
	babelHelpers.defineProperty(RatingRender, "blockTouchEndByScroll", false);
	babelHelpers.defineProperty(RatingRender, "reactionsPopupMobileTouchEndHandler", RatingRender.reactionsPopupMobileTouchEnd.bind(RatingRender));
	babelHelpers.defineProperty(RatingRender, "reactionsPopupMobileTouchMoveHandler", RatingRender.reactionsPopupMobileTouchMove.bind(RatingRender));
	babelHelpers.defineProperty(RatingRender, "reactionsPopupMobileHideHandler", RatingRender.reactionsPopupMobileHide.bind(RatingRender));

	var RatingManager = /*#__PURE__*/function () {
	  function RatingManager() {
	    babelHelpers.classCallCheck(this, RatingManager);
	  }

	  babelHelpers.createClass(RatingManager, null, [{
	    key: "init",
	    value: function init(params) {
	      var _this = this;

	      if (!main_core.Type.isPlainObject(params)) {
	        params = {};
	      }

	      if (this.initialized) {
	        return;
	      }

	      this.mobile = !main_core.Type.isUndefined(params.mobile) && !!params.mobile;
	      this.initialized = true;
	      this.setDisplayHeight();

	      if (!this.mobile) {
	        window.addEventListener('scroll', main_core.Runtime.throttle(function () {
	          _this.getInViewScope();
	        }, 80), {
	          passive: true
	        });
	        window.addEventListener('resize', this.setDisplayHeight.bind(this));
	      }

	      main_core_events.EventEmitter.subscribe('onBeforeMobileLivefeedRefresh', RatingRender.reactionsPopupMobileHide);
	      main_core_events.EventEmitter.subscribe('BX.MobileLF:onCommentsGet', RatingRender.onMobileCommentsGet);

	      if (this.mobile) {
	        // new one
	        BXMobileApp.addCustomEvent('onRatingLike', RatingRender.onRatingLike);
	      }

	      if (this.mobile) {
	        BXMobileApp.addCustomEvent('onPull-main', function (data) {
	          if (data.command == 'rating_vote') {
	            RatingLike.LiveUpdate(data.params);
	          }
	        });
	      } else {
	        main_core_events.EventEmitter.subscribe('onPullEvent-main', function (event) {
	          var _event$getCompatData = event.getCompatData(),
	              _event$getCompatData2 = babelHelpers.slicedToArray(_event$getCompatData, 2),
	              command = _event$getCompatData2[0],
	              params = _event$getCompatData2[1];

	          if (command === 'rating_vote') {
	            RatingLike.LiveUpdate(params);
	          }
	        });

	        if (!main_core.Type.isUndefined(window.BX.SidePanel) && BX.SidePanel.Instance.getTopSlider()) {
	          main_core_events.EventEmitter.subscribe(BX.SidePanel.Instance.getTopSlider().getWindow(), 'SidePanel.Slider:onCloseComplete', ListPopup.removeOnCloseHandler);
	        }
	      }
	    }
	  }, {
	    key: "setDisplayHeight",
	    value: function setDisplayHeight() {
	      this.displayHeight = document.documentElement.clientHeight;
	    }
	  }, {
	    key: "getInViewScope",
	    value: function getInViewScope() {
	      var _this2 = this;

	      var ratingNode = null;
	      this.delayedList.forEach(function (value, key) {
	        ratingNode = BX(_this2.getNode(key));

	        if (!ratingNode) {
	          return;
	        }

	        if (_this2.isNodeVisibleOnScreen(ratingNode)) {
	          _this2.fireAnimation(key);
	        }
	      });
	    }
	  }, {
	    key: "addNode",
	    value: function addNode(entityId, node) {
	      if (!main_core.Type.isDomNode(node) //			|| !Type.isUndefined(this.ratingNodeList.get(entityId))
	      ) {
	          return;
	        }

	      this.ratingNodeList.set(entityId, node);
	    }
	  }, {
	    key: "getNode",
	    value: function getNode(entityId) {
	      var node = this.ratingNodeList.get(entityId);
	      return !main_core.Type.isUndefined(node) ? node : false;
	    }
	  }, {
	    key: "isNodeVisibleOnScreen",
	    value: function isNodeVisibleOnScreen(node) {
	      var coords = node.getBoundingClientRect();
	      var visibleAreaTop = Number(this.displayHeight / 10);
	      var visibleAreaBottom = Number(this.displayHeight * 9 / 10);
	      return (coords.top > 0 && coords.top < visibleAreaBottom || coords.bottom > visibleAreaTop && coords.bottom < this.displayHeight) && (this.mobile || !(coords.top < visibleAreaTop && coords.bottom < visibleAreaTop || coords.top > visibleAreaBottom && coords.bottom > visibleAreaBottom));
	    }
	  }, {
	    key: "fireAnimation",
	    value: function fireAnimation(key) {
	      this.delayedList.delete(key);
	    }
	  }, {
	    key: "addEntity",
	    value: function addEntity(entityId, ratingObject) {
	      if (!this.entityList.includes(entityId) && ratingObject.topPanelContainer) {
	        this.entityList.push(entityId);
	        this.addNode(entityId, ratingObject.topPanelContainer);
	      }
	    }
	  }, {
	    key: "live",
	    value: function live(params) {
	      if (main_core.Type.isUndefined(params.TYPE) || params.TYPE !== 'ADD' || !main_core.Type.isStringFilled(params.ENTITY_TYPE_ID) || main_core.Type.isUndefined(params.ENTITY_ID) || Number(params.ENTITY_ID) <= 0) {
	        return;
	      }

	      var key = "".concat(params.ENTITY_TYPE_ID, "_").concat(params.ENTITY_ID);

	      if (!this.checkEntity(key)) {
	        return;
	      }

	      var ratingNode = this.getNode(key);

	      if (!ratingNode) {
	        return false;
	      }

	      if (this.isNodeVisibleOnScreen(ratingNode)) {
	        this.fireAnimation(key);
	      } else {
	        this.addDelayed(params);
	      }
	    }
	  }, {
	    key: "checkEntity",
	    value: function checkEntity(entityId) {
	      return this.entityList.includes(entityId);
	    }
	  }, {
	    key: "addDelayed",
	    value: function addDelayed(liveParams) {
	      if (!main_core.Type.isStringFilled(liveParams.ENTITY_TYPE_ID) || main_core.Type.isUndefined(liveParams.ENTITY_ID) || Number(liveParams.ENTITY_ID) <= 0) {
	        return;
	      }

	      var key = "".concat(liveParams.ENTITY_TYPE_ID, "_").concat(liveParams.ENTITY_ID);
	      var delayedListItem = this.delayedList.get(key);

	      if (main_core.Type.isUndefined(delayedListItem)) {
	        delayedListItem = [];
	      }

	      delayedListItem.push(liveParams);
	      this.delayedList.set(key, delayedListItem);
	    }
	  }]);
	  return RatingManager;
	}();
	babelHelpers.defineProperty(RatingManager, "mobile", false);
	babelHelpers.defineProperty(RatingManager, "initialized", false);
	babelHelpers.defineProperty(RatingManager, "displayHeight", 0);
	babelHelpers.defineProperty(RatingManager, "startScrollTop", 0);
	babelHelpers.defineProperty(RatingManager, "entityList", []);
	babelHelpers.defineProperty(RatingManager, "ratingNodeList", new Map());
	babelHelpers.defineProperty(RatingManager, "delayedList", new Map());

	var RatingLike$1 = /*#__PURE__*/function () {
	  function RatingLike(likeId, entityTypeId, entityId, available, userId, localize, template, pathToUserProfile) {
	    babelHelpers.classCallCheck(this, RatingLike);

	    if (main_core.Type.isObject(arguments[0])) {
	      var params = arguments[0];
	      this.likeId = main_core.Type.isStringFilled(params.likeId) ? params.likeId : '';
	      this.entityTypeId = main_core.Type.isStringFilled(params.entityTypeId) ? params.entityTypeId : '';
	      this.entityId = !main_core.Type.isUndefined(params.entityId) ? Number(params.entityId) : 0;
	      this.available = main_core.Type.isStringFilled(params.available) ? params.available === 'Y' : false;
	      this.userId = !main_core.Type.isUndefined(params.userId) ? Number(params.userId) : 0;
	      this.localize = main_core.Type.isPlainObject(params.localize) ? params.localize : {};
	      this.template = main_core.Type.isStringFilled(params.template) ? params.template : '';
	      this.pathToUserProfile = main_core.Type.isStringFilled(params.pathToUserProfile) ? params.pathToUserProfile : '';
	    } else {
	      this.likeId = main_core.Type.isStringFilled(arguments[0]) ? arguments[0] : '';
	      this.entityTypeId = main_core.Type.isStringFilled(arguments[1]) ? arguments[1] : '';
	      this.entityId = !main_core.Type.isUndefined(arguments[2]) ? Number(arguments[2]) : 0;
	      this.available = main_core.Type.isStringFilled(arguments[3]) ? arguments[3] === 'Y' : false;
	      this.userId = !main_core.Type.isUndefined(arguments[4]) ? Number(arguments[4]) : 0;
	      this.localize = main_core.Type.isPlainObject(arguments[5]) ? arguments[5] : {};
	      this.template = main_core.Type.isStringFilled(arguments[6]) ? arguments[6] : '';
	      this.pathToUserProfile = main_core.Type.isStringFilled(arguments[7]) ? arguments[7] : '';
	    }

	    var key = "".concat(this.entityTypeId, "_").concat(this.entityId);
	    this.enabled = true;
	    this.box = document.getElementById("bx-ilike-button-".concat(this.likeId));

	    if (this.box === null) {
	      this.enabled = false;
	      return false;
	    }

	    this.box.setAttribute('data-rating-vote-id', likeId);
	    this.button = this.box.querySelector('.bx-ilike-left-wrap');
	    this.buttonText = this.button.querySelector('.bx-ilike-text');
	    this.count = this.box.querySelector('span.bx-ilike-right-wrap');

	    if (!this.count) {
	      this.count = document.getElementById("bx-ilike-count-".concat(this.likeId));
	    }

	    this.countText = this.count.querySelector('.bx-ilike-right');
	    this.topPanelContainer = document.getElementById("feed-post-emoji-top-panel-container-".concat(this.likeId));
	    this.topPanel = document.getElementById("feed-post-emoji-top-panel-".concat(this.likeId));
	    this.topUsersText = document.getElementById("bx-ilike-top-users-".concat(this.likeId));
	    this.topUsersDataNode = document.getElementById("bx-ilike-top-users-data-".concat(this.likeId));
	    this.userReactionNode = document.getElementById("bx-ilike-user-reaction-".concat(this.likeId));
	    this.reactionsNode = document.getElementById("feed-post-emoji-icons-".concat(this.likeId));
	    this.popup = null;
	    this.popupId = null;
	    this.popupTimeoutIdShow = null;
	    this.popupTimeoutIdList = null;
	    this.popupContent = document.getElementById("bx-ilike-popup-cont-".concat(this.likeId)).querySelector('span.bx-ilike-popup');
	    this.popupContentPage = 1;
	    this.popupTimeout = false;
	    this.likeTimeout = false;
	    this.mouseOverHandler = null;
	    this.version = main_core.Type.isDomNode(this.topPanel) ? 2 : 1;
	    this.mouseInShowPopupNode = {};
	    this.listXHR = null;

	    if (this.template === 'light' && main_core.Type.isDomNode(this.reactionsNode)) {
	      var container = this.reactionsNode.querySelector('.feed-post-emoji-icon-container');

	      if (container) {
	        var reactionsData = container.getAttribute('data-reactions-data');

	        try {
	          reactionsData = JSON.parse(reactionsData);
	          var elementsNew = [];
	          Object.entries(reactionsData).forEach(function (_ref) {
	            var _ref2 = babelHelpers.slicedToArray(_ref, 2),
	                reaction = _ref2[0],
	                count = _ref2[1];

	            elementsNew.push({
	              reaction: reaction,
	              count: count,
	              animate: false
	            });
	          });
	          RatingRender.drawReactions({
	            likeId: likeId,
	            container: container,
	            data: elementsNew
	          });
	        } catch (e) {}
	      }
	    }

	    if (!main_core.Type.isUndefined(RatingLike.lastVoteRepo.get(key))) {
	      this.lastVote = RatingLike.lastVoteRepo.get(key);
	      var ratingNode = template === 'standart' ? this.button : this.count;

	      if (this.lastVote === 'plus') {
	        ratingNode.classList.add('bx-you-like');
	      } else {
	        ratingNode.classList.remove('bx-you-like');
	      }
	    } else {
	      this.lastVote = (this.template === 'standart' ? this.button : this.count).classList.contains('bx-you-like') ? 'plus' : 'cancel';
	      RatingLike.lastVoteRepo.set(key, this.lastVote);
	    }

	    if (!main_core.Type.isUndefined(RatingLike.lastReactionRepo.get(key))) {
	      this.lastReaction = RatingLike.lastReactionRepo.get(key);
	      this.count.setAttribute('data-myreaction', this.lastReaction);
	    } else {
	      var lastReaction = this.count.getAttribute('data-myreaction');
	      this.lastReaction = main_core.Type.isStringFilled(lastReaction) ? lastReaction : 'like';
	      RatingLike.lastReactionRepo.set(key, this.lastReaction);
	    }

	    if (this.topPanelContainer) {
	      RatingManager.addEntity(key, this);
	    }

	    return this;
	  }

	  babelHelpers.createClass(RatingLike, null, [{
	    key: "setInstance",
	    value: function setInstance(likeId, likeInstance) {
	      this.repo.set(likeId, likeInstance);
	      window.BXRL[likeId] = likeInstance;
	    }
	  }, {
	    key: "getInstance",
	    value: function getInstance(likeId) {
	      return this.repo.get(likeId);
	    }
	  }, {
	    key: "ClickVote",
	    value: function ClickVote(e, likeId, userReaction, forceAdd) {
	      var _this = this;

	      if (main_core.Type.isUndefined(userReaction)) {
	        userReaction = 'like';
	      }

	      var likeInstance = this.getInstance(likeId);
	      var container = likeInstance.template === 'standart' ? e.target : likeInstance.count;

	      if (likeInstance.version === 2 && likeInstance.userReactionNode) {
	        RatingRender.hideReactionsPopup({
	          likeId: likeId
	        });
	        RatingRender.blockReactionsPopup();
	        document.removeEventListener('mousemove', RatingRender.reactionsPopupMouseOutHandler);
	      }

	      clearTimeout(likeInstance.likeTimeout);
	      var active = container.classList.contains('bx-you-like');
	      forceAdd = !!forceAdd;
	      var change = false;
	      var userReactionOld = false;

	      if (active && !forceAdd) {
	        userReaction = likeInstance.version === 2 ? RatingRender.getUserReaction({
	          userReactionNode: likeInstance.userReactionNode
	        }) : false;
	        likeInstance.buttonText.innerHTML = likeInstance.localize['LIKE_N'];
	        likeInstance.countText.innerHTML = Number(likeInstance.countText.innerHTML) - 1;
	        container.classList.remove('bx-you-like');
	        likeInstance.button.classList.remove('bx-you-like-button');

	        if (userReaction) {
	          likeInstance.button.classList.remove("bx-you-like-button-".concat(userReaction));
	        }

	        likeInstance.likeTimeout = setTimeout(function () {
	          if (likeInstance.lastVote != 'cancel') {
	            _this.Vote(likeId, 'cancel', userReaction);
	          }
	        }, 1000);
	      } else if (active && forceAdd) {
	        change = true;
	        userReactionOld = likeInstance.version === 2 ? RatingRender.getUserReaction({
	          userReactionNode: likeInstance.userReactionNode
	        }) : false;

	        if (userReaction != userReactionOld) {
	          if (userReactionOld) {
	            likeInstance.button.classList.remove("bx-you-like-button-".concat(userReactionOld));
	          }

	          likeInstance.button.classList.add("bx-you-like-button-".concat(userReaction));
	          likeInstance.likeTimeout = setTimeout(function () {
	            _this.Vote(likeId, 'change', userReaction, userReactionOld);
	          }, 1000);
	        }
	      } else if (!active) {
	        likeInstance.buttonText.innerHTML = likeInstance.localize['LIKE_Y'];
	        likeInstance.countText.innerHTML = Number(likeInstance.countText.innerHTML) + 1;
	        container.classList.add('bx-you-like');
	        likeInstance.button.classList.add('bx-you-like-button');
	        likeInstance.button.classList.add("bx-you-like-button-".concat(userReaction));
	        likeInstance.likeTimeout = setTimeout(function () {
	          if (likeInstance.lastVote !== 'plus') {
	            _this.Vote(likeId, 'plus', userReaction);
	          } else if (userReaction !== likeInstance.lastReaction) // http://jabber.bx/view.php?id=99339
	            {
	              _this.Vote(likeId, 'change', userReaction, likeInstance.lastReaction);
	            }
	        }, 1000);
	      }

	      if (likeInstance.version === 2) {
	        if (change) {
	          RatingRender.setReaction({
	            likeId: likeId,
	            rating: likeInstance,
	            action: 'change',
	            userReaction: userReaction,
	            userReactionOld: userReactionOld,
	            totalCount: Number(likeInstance.countText.innerHTML)
	          });
	        } else {
	          RatingRender.setReaction({
	            likeId: likeId,
	            rating: likeInstance,
	            action: active ? 'cancel' : 'add',
	            userReaction: userReaction,
	            totalCount: Number(likeInstance.countText.innerHTML)
	          });
	        }
	      }

	      if (!change && likeInstance.version === 2) {
	        var dataUsers = likeInstance.topUsersDataNode ? JSON.parse(likeInstance.topUsersDataNode.getAttribute('data-users')) : false;

	        if (dataUsers) {
	          dataUsers.TOP = Object.values(dataUsers.TOP);
	          likeInstance.topUsersText.innerHTML = RatingRender.getTopUsersText({
	            you: !active,
	            top: dataUsers.TOP,
	            more: dataUsers.MORE
	          });
	        }
	      }

	      if (likeInstance.template === 'light' && !likeInstance.userReactionNode) {
	        var cont = likeInstance.box;
	        var likeNode = cont.cloneNode(true);
	        likeNode.id = 'like_anim'; // to not dublicate original id

	        var type = 'normal';

	        if (cont.closest('.feed-com-informers-bottom')) {
	          type = 'comment';
	        } else if (cont.closest('.feed-post-informers')) {
	          type = 'post';
	        }

	        likeNode.classList.remove('bx-ilike-button-hover');
	        likeNode.classList.add('bx-like-anim');
	        main_core.Dom.adjust(cont.parentNode, {
	          style: {
	            position: 'relative'
	          }
	        });
	        main_core.Dom.adjust(likeNode, {
	          style: {
	            position: 'absolute',
	            whiteSpace: 'nowrap',
	            top: type === 'post' ? '1px' : type === 'comment' ? '0' : ''
	          }
	        });
	        main_core.Dom.adjust(cont, {
	          style: {
	            visibility: 'hidden'
	          }
	        });
	        main_core.Dom.prepend(likeNode, cont.parentNode);
	        new BX.easing({
	          duration: 140,
	          start: {
	            scale: 100
	          },
	          finish: {
	            scale: type === 'comment' ? 110 : 115
	          },
	          transition: BX.easing.transitions.quad,
	          step: function step(state) {
	            likeNode.style.transform = "scale(".concat(state.scale / 100, ")");
	          },
	          complete: function complete() {
	            var likeThumbNode = main_core.Dom.create('SPAN', {
	              props: {
	                className: active ? 'bx-ilike-icon' : 'bx-ilike-icon bx-ilike-icon-orange'
	              }
	            });
	            main_core.Dom.adjust(likeThumbNode, {
	              style: {
	                position: 'absolute',
	                whiteSpace: 'nowrap'
	              }
	            });
	            main_core.Dom.prepend(likeThumbNode, cont.parentNode);
	            new BX.easing({
	              duration: 140,
	              start: {
	                scale: type == 'comment' ? 110 : 115
	              },
	              finish: {
	                scale: 100
	              },
	              transition: BX.easing.transitions.quad,
	              step: function step(state) {
	                likeNode.style.transform = "scale(".concat(state.scale / 100, ")");
	              },
	              complete: function complete() {}
	            }).animate();
	            var propsStart = {
	              opacity: 100,
	              scale: type === 'comment' ? 110 : 115,
	              top: 0
	            };
	            var propsFinish = {
	              opacity: 0,
	              scale: 200,
	              top: type === 'comment' ? -3 : -2
	            };

	            if (type !== 'comment') {
	              propsStart.left = -5;
	              propsFinish.left = -13;
	            }

	            new BX.easing({
	              duration: 200,
	              start: propsStart,
	              finish: propsFinish,
	              transition: BX.easing.transitions.linear,
	              step: function step(state) {
	                likeThumbNode.style.transform = "scale(".concat(state.scale / 100, ")");
	                likeThumbNode.style.opacity = state.opacity / 100;

	                if (type !== 'comment') {
	                  likeThumbNode.style.left = "".concat(state.left, "px");
	                }

	                likeThumbNode.style.top = "".concat(state.top, "px");
	              },
	              complete: function complete() {
	                likeNode.parentNode.removeChild(likeNode);
	                likeThumbNode.parentNode.removeChild(likeThumbNode);
	                main_core.Dom.adjust(cont.parentNode, {
	                  style: {
	                    position: 'static'
	                  }
	                });
	                main_core.Dom.adjust(cont, {
	                  style: {
	                    visibility: 'visible'
	                  }
	                });
	              }
	            }).animate();
	          }
	        }).animate();
	      }

	      likeInstance.box.classList.remove('bx-ilike-button-hover');
	    }
	  }, {
	    key: "Draw",
	    value: function Draw(likeId, params) {
	      var likeInstance = this.getInstance(likeId);
	      likeInstance.countText.innerHTML = Number(params.TOTAL_POSITIVE_VOTES);

	      if (!main_core.Type.isUndefined(params.TYPE) && !main_core.Type.isUndefined(params.USER_ID) && Number(params.USER_ID) > 0 && !main_core.Type.isUndefined(params.USER_DATA) && !main_core.Type.isUndefined(params.USER_DATA.WEIGHT)) {
	        var userWeight = parseFloat(params.USER_DATA.WEIGHT);
	        var usersData = likeInstance.topUsersDataNode ? JSON.parse(likeInstance.topUsersDataNode.getAttribute('data-users')) : false;

	        if (params.TYPE != 'CHANGE' && main_core.Type.isPlainObject(usersData)) {
	          usersData.TOP = Object.values(usersData.TOP);
	          var recalcNeeded = usersData.TOP.length < 2;
	          Object.values(usersData.TOP).forEach(function (item) {
	            if (recalcNeeded) {
	              return;
	            }

	            if (params.TYPE === 'ADD' && userWeight > item.WEIGHT || params.TYPE === 'CANCEL' && params.USER_ID === item.ID) {
	              recalcNeeded = true;
	            }
	          });

	          if (recalcNeeded) {
	            if (params.TYPE === 'ADD' && Number(params.USER_ID) !== Number(main_core.Loc.getMessage('USER_ID'))) {
	              if (!usersData.TOP.find(function (a) {
	                return Number(a.ID) === Number(params.USER_ID);
	              })) {
	                usersData.TOP.push({
	                  ID: Number(params.USER_ID),
	                  NAME_FORMATTED: params.USER_DATA.NAME_FORMATTED,
	                  WEIGHT: parseFloat(params.USER_DATA.WEIGHT)
	                });
	              }
	            } else if (params.TYPE === 'CANCEL') {
	              usersData.TOP = usersData.TOP.filter(function (a) {
	                return Number(a.ID) !== Number(params.USER_ID);
	              });
	            }

	            usersData.TOP.sort(function (a, b) {
	              if (parseFloat(a.WEIGHT) === parseFloat(b.WEIGHT)) {
	                return 0;
	              }

	              return parseFloat(a.WEIGHT) > parseFloat(b.WEIGHT) ? -1 : 1;
	            });

	            if (usersData.TOP.length > 2 && params.TYPE === 'ADD') {
	              usersData.TOP.pop();
	              usersData.MORE++;
	            }
	          } else {
	            if (params.TYPE === 'ADD') {
	              usersData.MORE = !main_core.Type.isUndefined(usersData.MORE) ? Number(usersData.MORE) + 1 : 1;
	            } else if (params.TYPE === 'CANCEL') {
	              usersData.MORE = !main_core.Type.isUndefined(usersData.MORE) && Number(usersData.MORE) > 0 ? Number(usersData.MORE) - 1 : 0;
	            }
	          }

	          likeInstance.topUsersDataNode.setAttribute('data-users', JSON.stringify(usersData));

	          if (likeInstance.topUsersText) {
	            likeInstance.topUsersText.innerHTML = RatingRender.getTopUsersText({
	              you: Number(params.USER_ID) === Number(main_core.Loc.getMessage('USER_ID')) ? params.TYPE !== 'CANCEL' : likeInstance.count.classList.contains('bx-you-like'),
	              top: usersData.TOP,
	              more: usersData.MORE
	            });
	          }
	        }

	        if (main_core.Type.isStringFilled(params.REACTION) && main_core.Type.isStringFilled(params.REACTION_OLD) && params.TYPE === 'CHANGE') {
	          RatingRender.setReaction({
	            likeId: likeId,
	            rating: likeInstance,
	            action: 'change',
	            userReaction: params.REACTION,
	            userReactionOld: params.REACTION_OLD,
	            totalCount: params.TOTAL_POSITIVE_VOTES,
	            userId: params.USER_ID
	          });
	        } else if (main_core.Type.isStringFilled(params.REACTION) && ['ADD', 'CANCEL'].includes(params.TYPE)) {
	          RatingRender.setReaction({
	            likeId: likeId,
	            rating: likeInstance,
	            userReaction: params.REACTION,
	            action: params.TYPE === 'ADD' ? 'add' : 'cancel',
	            totalCount: params.TOTAL_POSITIVE_VOTES,
	            userId: params.USER_ID
	          });
	        }
	      }

	      if (likeInstance.topPanel) {
	        likeInstance.topPanel.setAttribute('data-popup', 'N');
	      }

	      if (!likeInstance.userReactionNode) {
	        likeInstance.count.insertBefore(main_core.Dom.create('span', {
	          props: {
	            className: 'bx-ilike-plus-one'
	          },
	          style: {
	            width: "".concat(element.countText.clientWidth - 8, "px"),
	            height: "".concat(element.countText.clientHeight - 8, "px")
	          },
	          html: params.TYPE === 'ADD' ? '+1' : '-1'
	        }), element.count.firstChild);
	      }

	      if (likeInstance.popup) {
	        likeInstance.popup.close();
	        likeInstance.popupContentPage = 1;
	      }
	    }
	  }, {
	    key: "Vote",
	    value: function Vote(likeId, voteAction, voteReaction, voteReactionOld) {
	      var _this2 = this;

	      if (!main_core.Type.isStringFilled(voteReaction)) {
	        voteReaction = 'like';
	      }

	      var ajaxInstance = RatingManager.mobile ? new MobileAjaxWrapper() : main_core.ajax;
	      var likeInstance = this.getInstance(likeId);

	      var successCallback = function successCallback(response) {
	        var data = response.data;
	        likeInstance.lastVote = data.action;
	        likeInstance.lastReaction = voteReaction;
	        var key = "".concat(likeInstance.entityTypeId, "_").concat(likeInstance.entityId);

	        _this2.lastVoteRepo.set(key, data.action);

	        _this2.lastReactionRepo.set(key, data.voteReaction);

	        likeInstance.countText.innerHTML = data.items_all;
	        likeInstance.popupContentPage = 1;
	        likeInstance.popupContent.innerHTML = '';
	        likeInstance.popupContent.appendChild(main_core.Dom.create('span', {
	          props: {
	            className: 'bx-ilike-wait'
	          }
	        }));

	        if (likeInstance.topPanel) {
	          likeInstance.topPanel.setAttribute('data-popup', 'N');
	        }

	        ListPopup.AdjustWindow(likeId);
	        var popup = document.getElementById("ilike-popup-".concat(likeId));

	        if (popup && popup.style.display === 'block') {
	          ListPopup.List(likeId, null, '', true);
	        }

	        if (likeInstance.version >= 2 && RatingManager.mobile) {
	          BXMobileApp.onCustomEvent('onRatingLike', {
	            action: data.action,
	            ratingId: likeId,
	            entityTypeId: likeInstance.entityTypeId,
	            entityId: likeInstance.entityId,
	            voteAction: voteAction,
	            voteReaction: voteReaction,
	            voteReactionOld: voteReactionOld,
	            userId: main_core.Loc.getMessage('USER_ID'),
	            userData: !main_core.Type.isUndefined(data.user_data) ? data.user_data : null,
	            itemsAll: data.items_all
	          }, true);
	        }
	      };

	      var failureCallback = function failureCallback() {
	        var dataUsers = likeInstance.topUsersDataNode ? JSON.parse(likeInstance.topUsersDataNode.getAttribute('data-users')) : false;

	        if (likeInstance.version == 2) {
	          if (voteAction === 'change') {
	            RatingRender.setReaction({
	              likeId: likeId,
	              rating: likeInstance,
	              action: voteAction,
	              userReaction: voteReaction,
	              userReactionOld: voteReactionOld,
	              totalCount: Number(likeInstance.countText.innerHTML)
	            });
	          } else {
	            RatingRender.setReaction({
	              likeId: likeId,
	              rating: likeInstance,
	              action: voteAction === 'cancel' ? 'add' : 'cancel',
	              userReaction: voteReaction,
	              totalCount: voteAction == 'cancel' ? Number(likeInstance.countText.innerHTML) + 1 : Number(likeInstance.countText.innerHTML) - 1
	            });
	          }

	          if (likeInstance.buttonText) {
	            if (voteAction === 'add') {
	              likeInstance.buttonText.innerHTML = main_core.Loc.getMessage('RATING_LIKE_EMOTION_LIKE_CALC');
	            } else if (voteAction === 'change') {
	              likeInstance.buttonText.innerHTML = main_core.Loc.getMessage("RATING_LIKE_EMOTION_".concat(voteReactionOld.toUpperCase(), "_CALC"));
	            } else {
	              likeInstance.buttonText.innerHTML = main_core.Loc.getMessage("RATING_LIKE_EMOTION_".concat(voteReaction.toUpperCase(), "_CALC"));
	            }
	          }
	        }

	        if (dataUsers && voteAction !== 'change' && likeInstance.version == 2) {
	          likeInstance.topUsersText.innerHTML = RatingRender.getTopUsersText({
	            you: voteAction === 'cancel',
	            // negative
	            top: Object.values(dataUsers.TOP),
	            more: dataUsers.MORE
	          });
	        }
	      };

	      var analyticsLabel = {
	        b24statAction: 'addLike'
	      };

	      if (likeInstance.version >= 2 && RatingManager.mobile) {
	        analyticsLabel.b24statContext = 'mobile';
	      }

	      ajaxInstance.runAction('main.rating.vote', {
	        data: {
	          params: {
	            RATING_VOTE_TYPE_ID: likeInstance.entityTypeId,
	            RATING_VOTE_ENTITY_ID: likeInstance.entityId,
	            RATING_VOTE_ACTION: voteAction,
	            RATING_VOTE_REACTION: voteReaction
	          }
	        },
	        analyticsLabel: analyticsLabel
	      }).then(successCallback, failureCallback);
	      return false;
	    }
	  }, {
	    key: "LiveUpdate",
	    value: function LiveUpdate(params) {
	      var _this3 = this;

	      if (Number(params.USER_ID) === Number(main_core.Loc.getMessage('USER_ID'))) {
	        return false;
	      }

	      this.repo.forEach(function (likeInstance, likeId) {
	        if (likeInstance.entityTypeId !== params.ENTITY_TYPE_ID || Number(likeInstance.entityId) !== Number(params.ENTITY_ID)) {
	          return;
	        }

	        _this3.Draw(likeId, params);
	      });
	      RatingManager.live(params);
	    }
	  }, {
	    key: "Set",
	    value: function Set(likeId, entityTypeId, entityId, available, userId, localize, template, pathToUserProfile, pathToAjax, mobile) {
	      var _this4 = this;

	      mobile = !!mobile;

	      if (template === undefined) {
	        template = 'standart';
	      }

	      if (this.additionalParams.get('pathToUserProfile')) {
	        pathToUserProfile = this.additionalParams.get('pathToUserProfile');
	      }

	      var likeInstance = this.getInstance(likeId);

	      if (likeInstance && likeInstance.tryToSet > 5) {
	        return;
	      }

	      var tryToSend = likeInstance && likeInstance.tryToSet ? likeInstance.tryToSet : 1;
	      likeInstance = new RatingLike(likeId, entityTypeId, entityId, available, userId, localize, template, pathToUserProfile);
	      this.setInstance(likeId, likeInstance);

	      if (likeInstance.enabled) {
	        this.Init(likeId, {
	          mobile: mobile
	        });
	      } else {
	        setTimeout(function () {
	          likeInstance.tryToSet = tryToSend + 1;

	          _this4.Set(likeId, entityTypeId, entityId, available, userId, localize, template, pathToUserProfile, pathToAjax, mobile);
	        }, 500);
	      }
	    }
	  }, {
	    key: "setParams",
	    value: function setParams(params) {
	      if (!main_core.Type.isUndefined(params.pathToUserProfile)) {
	        this.additionalParams.set('pathToUserProfile', params.pathToUserProfile);
	      }
	    }
	  }, {
	    key: "Init",
	    value: function Init(likeId, params) {
	      params = !main_core.Type.isUndefined(params) ? params : {};
	      RatingManager.init(params);
	      var likeInstance = this.getInstance(likeId); // like/unlike button

	      if (likeInstance.available) {
	        var eventNode = likeInstance.template === 'standart' ? likeInstance.button : likeInstance.buttonText;

	        if (!RatingManager.mobile) {
	          var eventNodeNew = eventNode.closest('.feed-new-like');

	          if (eventNodeNew) {
	            eventNode = eventNodeNew;
	          }
	        }

	        if (likeInstance.version >= 2 && RatingManager.mobile) {
	          eventNode.removeEventListener('touchstart', this.mobileTouchStartHandler);
	          eventNode.addEventListener('touchstart', this.mobileTouchStartHandler);
	        }

	        var eventName = RatingManager.mobile ? 'touchend' : 'click';
	        eventNode.removeEventListener(eventName, this.buttonClickHandler);
	        eventNode.addEventListener(eventName, this.buttonClickHandler);

	        if (!RatingManager.mobile) {
	          // Hover/unHover like-button
	          likeInstance.box.addEventListener('mouseover', function () {
	            likeInstance.box.classList.add('bx-ilike-button-hover');
	          });
	          likeInstance.box.addEventListener('mouseout', function () {
	            likeInstance.box.classList.remove('bx-ilike-button-hover');
	          });
	        } else {
	          likeInstance.topPanel.removeEventListener('click', this.mobileTopPanelClickHandler);
	          likeInstance.topPanel.addEventListener('click', this.mobileTopPanelClickHandler);
	        }
	      } else if (main_core.Type.isDomNode(likeInstance.buttonText)) {
	        likeInstance.buttonText.innerHTML = likeInstance.localize['LIKE_D'];
	        likeInstance.buttonText.classList.add('bx-ilike-text-unavailable');
	      } // get like-user-list


	      var clickShowPopupNode = likeInstance.topUsersText ? likeInstance.topUsersText : likeInstance.count;

	      if (!RatingManager.mobile) {
	        clickShowPopupNode.addEventListener('mouseenter', function (e) {
	          ListPopup.onResultMouseEnter({
	            likeId: likeId,
	            event: e,
	            nodeId: e.currentTarget.id
	          });
	        });
	        clickShowPopupNode.addEventListener('mouseleave', function (e) {
	          ListPopup.onResultMouseLeave({
	            likeId: likeId
	          });
	        });
	        clickShowPopupNode.addEventListener('click', function (e) {
	          ListPopup.onResultClick({
	            likeId: likeId,
	            event: e,
	            nodeId: e.currentTarget.id
	          });
	        });
	      }

	      if (likeInstance.version === 2 && likeInstance.available && likeInstance.userReactionNode) {
	        RatingRender.bindReactionsPopup({
	          likeId: likeId
	        });
	      }
	    }
	  }, {
	    key: "mobileTouchStartHandler",
	    value: function mobileTouchStartHandler() {
	      RatingManager.startScrollTop = document.documentElement && document.documentElement.scrollTop || document.body.scrollTop;
	    }
	  }, {
	    key: "buttonClickHandler",
	    value: function buttonClickHandler(e) {
	      var likeInstanceNode = e.currentTarget.closest('[data-rating-vote-id]');

	      if (!main_core.Type.isDomNode(likeInstanceNode)) {
	        return;
	      }

	      var likeId = likeInstanceNode.getAttribute('data-rating-vote-id');

	      if (!main_core.Type.isStringFilled(likeId)) {
	        return;
	      }

	      var likeInstance = RatingLike.getInstance(likeId);

	      if (likeInstance.version >= 2 && RatingManager.mobile && RatingRender.blockTouchEndByScroll) {
	        RatingRender.blockTouchEndByScroll = false;
	        return;
	      }

	      if (likeInstance.version < 2 || !RatingManager.mobile || !RatingRender.reactionsPopupLikeId) {
	        if (likeInstance.version >= 2 && RatingManager.mobile) {
	          var currentScrollTop = document.documentElement && document.documentElement.scrollTop || document.body.scrollTop;

	          if (Math.abs(currentScrollTop - RatingManager.startScrollTop) > 2) {
	            return;
	          }
	        }

	        RatingLike.ClickVote(e, likeId);
	      }

	      if (likeInstance.version == 2) {
	        RatingRender.afterClick({
	          likeId: likeId
	        });
	      }

	      e.preventDefault();
	    }
	  }, {
	    key: "mobileTopPanelClickHandler",
	    value: function mobileTopPanelClickHandler(e) {
	      var likeInstanceNode = e.currentTarget.querySelector('[data-like-id]');

	      if (!main_core.Type.isDomNode(likeInstanceNode)) {
	        return;
	      }

	      var likeId = likeInstanceNode.getAttribute('data-like-id');

	      if (!main_core.Type.isStringFilled(likeId)) {
	        return;
	      }

	      var likeInstance = RatingLike.getInstance(likeId);
	      RatingRender.openMobileReactionsPage({
	        entityTypeId: likeInstance.entityTypeId,
	        entityId: likeInstance.entityId
	      });
	      e.stopPropagation();
	    }
	  }]);
	  return RatingLike;
	}();
	babelHelpers.defineProperty(RatingLike$1, "repo", new Map());
	babelHelpers.defineProperty(RatingLike$1, "lastVoteRepo", new Map());
	babelHelpers.defineProperty(RatingLike$1, "lastReactionRepo", new Map());
	babelHelpers.defineProperty(RatingLike$1, "additionalParams", new Map());

	if (main_core.Type.isUndefined(window.BXRL)) {
	  window.BXRL = {};
	}

	window.BXRL.manager = RatingManager;
	window.BXRL.render = RatingRender;
	window.RatingLike = RatingLike$1;

}((this.BX.Main = this.BX.Main || {}),BX,BX.Main,BX.Event));
//# sourceMappingURL=main.rating.js.map
