this.BX = this.BX || {};
this.BX.Main = this.BX.Main || {};
(function (exports,ui_lottie,main_core,main_popup,main_core_events) {
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
	            RATING_VOTE_KEY_SIGNED: likeInstance.keySigned,
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
	        if (Number(data.list_page) === 1) {
	          likeInstance.popup.close();
	        }
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

	var v = "5.9.1";
	var fr = 25;
	var ip = 0;
	var op = 30;
	var w = 40;
	var h = 40;
	var nm = "em_01";
	var ddd = 0;
	var assets = [];
	var layers = [{
	  ddd: 0,
	  ind: 1,
	  ty: 4,
	  nm: "Layer 1/Emotions Outlines 2",
	  sr: 1,
	  ks: {
	    o: {
	      a: 0,
	      k: 100,
	      ix: 11
	    },
	    r: {
	      a: 1,
	      k: [{
	        i: {
	          x: [0.833],
	          y: [0.702]
	        },
	        o: {
	          x: [0.167],
	          y: [0.167]
	        },
	        t: 0,
	        s: [0]
	      }, {
	        i: {
	          x: [0.833],
	          y: [0.803]
	        },
	        o: {
	          x: [0.167],
	          y: [0.116]
	        },
	        t: 1,
	        s: [-0.443]
	      }, {
	        i: {
	          x: [0.833],
	          y: [0.826]
	        },
	        o: {
	          x: [0.167],
	          y: [0.144]
	        },
	        t: 2,
	        s: [-1.586]
	      }, {
	        i: {
	          x: [0.833],
	          y: [0.84]
	        },
	        o: {
	          x: [0.167],
	          y: [0.16]
	        },
	        t: 3,
	        s: [-3.149]
	      }, {
	        i: {
	          x: [0.833],
	          y: [0.856]
	        },
	        o: {
	          x: [0.167],
	          y: [0.174]
	        },
	        t: 4,
	        s: [-4.851]
	      }, {
	        i: {
	          x: [0.833],
	          y: [0.884]
	        },
	        o: {
	          x: [0.167],
	          y: [0.197]
	        },
	        t: 5,
	        s: [-6.414]
	      }, {
	        i: {
	          x: [0.833],
	          y: [0.942]
	        },
	        o: {
	          x: [0.167],
	          y: [0.298]
	        },
	        t: 6,
	        s: [-7.557]
	      }, {
	        i: {
	          x: [0.833],
	          y: [0.457]
	        },
	        o: {
	          x: [0.167],
	          y: [-0.196]
	        },
	        t: 7,
	        s: [-8]
	      }, {
	        i: {
	          x: [0.833],
	          y: [0.749]
	        },
	        o: {
	          x: [0.167],
	          y: [0.098]
	        },
	        t: 8,
	        s: [-7.868]
	      }, {
	        i: {
	          x: [0.833],
	          y: [0.783]
	        },
	        o: {
	          x: [0.167],
	          y: [0.125]
	        },
	        t: 9,
	        s: [-7.139]
	      }, {
	        i: {
	          x: [0.833],
	          y: [0.8]
	        },
	        o: {
	          x: [0.167],
	          y: [0.135]
	        },
	        t: 10,
	        s: [-5.67]
	      }, {
	        i: {
	          x: [0.833],
	          y: [0.835]
	        },
	        o: {
	          x: [0.167],
	          y: [0.143]
	        },
	        t: 11,
	        s: [-3.305]
	      }, {
	        i: {
	          x: [0.833],
	          y: [0.863]
	        },
	        o: {
	          x: [0.167],
	          y: [0.169]
	        },
	        t: 12,
	        s: [-0.008]
	      }, {
	        i: {
	          x: [0.833],
	          y: [0.872]
	        },
	        o: {
	          x: [0.167],
	          y: [0.212]
	        },
	        t: 13,
	        s: [3.205]
	      }, {
	        i: {
	          x: [0.833],
	          y: [0.923]
	        },
	        o: {
	          x: [0.167],
	          y: [0.239]
	        },
	        t: 14,
	        s: [5.294]
	      }, {
	        i: {
	          x: [0.833],
	          y: [-0.288]
	        },
	        o: {
	          x: [0.167],
	          y: [-1.045]
	        },
	        t: 15,
	        s: [6.409]
	      }, {
	        i: {
	          x: [0.833],
	          y: [0.779]
	        },
	        o: {
	          x: [0.167],
	          y: [0.089]
	        },
	        t: 16,
	        s: [6.327]
	      }, {
	        i: {
	          x: [0.833],
	          y: [0.82]
	        },
	        o: {
	          x: [0.167],
	          y: [0.134]
	        },
	        t: 17,
	        s: [5.136]
	      }, {
	        i: {
	          x: [0.833],
	          y: [0.884]
	        },
	        o: {
	          x: [0.167],
	          y: [0.155]
	        },
	        t: 18,
	        s: [3.172]
	      }, {
	        i: {
	          x: [0.833],
	          y: [0.87]
	        },
	        o: {
	          x: [0.167],
	          y: [0.293]
	        },
	        t: 19,
	        s: [0.902]
	      }, {
	        i: {
	          x: [0.833],
	          y: [0.865]
	        },
	        o: {
	          x: [0.167],
	          y: [0.231]
	        },
	        t: 20,
	        s: [0]
	      }, {
	        i: {
	          x: [0.833],
	          y: [0.897]
	        },
	        o: {
	          x: [0.167],
	          y: [0.218]
	        },
	        t: 21,
	        s: [-0.509]
	      }, {
	        i: {
	          x: [0.833],
	          y: [1.079]
	        },
	        o: {
	          x: [0.167],
	          y: [0.432]
	        },
	        t: 22,
	        s: [-0.824]
	      }, {
	        i: {
	          x: [0.833],
	          y: [0.745]
	        },
	        o: {
	          x: [0.167],
	          y: [0.041]
	        },
	        t: 23,
	        s: [-0.899]
	      }, {
	        i: {
	          x: [0.833],
	          y: [0.816]
	        },
	        o: {
	          x: [0.167],
	          y: [0.124]
	        },
	        t: 24,
	        s: [-0.752]
	      }, {
	        i: {
	          x: [0.833],
	          y: [0.841]
	        },
	        o: {
	          x: [0.167],
	          y: [0.153]
	        },
	        t: 25,
	        s: [-0.451]
	      }, {
	        i: {
	          x: [0.833],
	          y: [0.86]
	        },
	        o: {
	          x: [0.167],
	          y: [0.175]
	        },
	        t: 26,
	        s: [-0.089]
	      }, {
	        i: {
	          x: [0.833],
	          y: [0.886]
	        },
	        o: {
	          x: [0.167],
	          y: [0.206]
	        },
	        t: 27,
	        s: [0.241]
	      }, {
	        i: {
	          x: [0.833],
	          y: [0.976]
	        },
	        o: {
	          x: [0.167],
	          y: [0.311]
	        },
	        t: 28,
	        s: [0.465]
	      }, {
	        i: {
	          x: [0.833],
	          y: [0.681]
	        },
	        o: {
	          x: [0.167],
	          y: [-0.035]
	        },
	        t: 29,
	        s: [0.547]
	      }, {
	        i: {
	          x: [0.833],
	          y: [0.807]
	        },
	        o: {
	          x: [0.167],
	          y: [0.113]
	        },
	        t: 30,
	        s: [0.489]
	      }, {
	        i: {
	          x: [0.833],
	          y: [0.833]
	        },
	        o: {
	          x: [0.167],
	          y: [0.147]
	        },
	        t: 31,
	        s: [0.325]
	      }, {
	        t: 32,
	        s: [0.109]
	      }],
	      ix: 10
	    },
	    p: {
	      a: 1,
	      k: [{
	        i: {
	          x: 0.833,
	          y: 0.706
	        },
	        o: {
	          x: 0.167,
	          y: 0.167
	        },
	        t: 0,
	        s: [10.908, 24.986, 0],
	        to: [0, -0.008, 0],
	        ti: [0, 0.029, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.802
	        },
	        o: {
	          x: 0.167,
	          y: 0.116
	        },
	        t: 1,
	        s: [10.908, 24.936, 0],
	        to: [0, -0.029, 0],
	        ti: [0, 0.05, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.813
	        },
	        o: {
	          x: 0.167,
	          y: 0.144
	        },
	        t: 2,
	        s: [10.908, 24.81, 0],
	        to: [0, -0.05, 0],
	        ti: [0, 0.065, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.821
	        },
	        o: {
	          x: 0.167,
	          y: 0.15
	        },
	        t: 3,
	        s: [10.908, 24.636, 0],
	        to: [0, -0.065, 0],
	        ti: [0, 0.078, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.826
	        },
	        o: {
	          x: 0.167,
	          y: 0.156
	        },
	        t: 4,
	        s: [10.908, 24.419, 0],
	        to: [0, -0.078, 0],
	        ti: [0, 0.087, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.828
	        },
	        o: {
	          x: 0.167,
	          y: 0.16
	        },
	        t: 5,
	        s: [10.908, 24.17, 0],
	        to: [0, -0.087, 0],
	        ti: [0, 0.093, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.829
	        },
	        o: {
	          x: 0.167,
	          y: 0.162
	        },
	        t: 6,
	        s: [10.908, 23.9, 0],
	        to: [0, -0.093, 0],
	        ti: [0, 0.098, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.834
	        },
	        o: {
	          x: 0.167,
	          y: 0.163
	        },
	        t: 7,
	        s: [10.908, 23.614, 0],
	        to: [0, -0.098, 0],
	        ti: [0, 0.099, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.839
	        },
	        o: {
	          x: 0.167,
	          y: 0.168
	        },
	        t: 8,
	        s: [10.908, 23.314, 0],
	        to: [0, -0.099, 0],
	        ti: [0, 0.095, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.865
	        },
	        o: {
	          x: 0.167,
	          y: 0.173
	        },
	        t: 9,
	        s: [10.908, 23.019, 0],
	        to: [0, -0.095, 0],
	        ti: [0, 0.018, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.781
	        },
	        o: {
	          x: 0.167,
	          y: 0.218
	        },
	        t: 10,
	        s: [10.908, 22.745, 0],
	        to: [0, -0.018, 0],
	        ti: [0, -0.074, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.795
	        },
	        o: {
	          x: 0.167,
	          y: 0.134
	        },
	        t: 11,
	        s: [10.908, 22.91, 0],
	        to: [0, 0.074, 0],
	        ti: [0, -0.113, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.807
	        },
	        o: {
	          x: 0.167,
	          y: 0.14
	        },
	        t: 12,
	        s: [10.908, 23.186, 0],
	        to: [0, 0.113, 0],
	        ti: [0, -0.156, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.781
	        },
	        o: {
	          x: 0.167,
	          y: 0.147
	        },
	        t: 13,
	        s: [10.908, 23.589, 0],
	        to: [0, 0.156, 0],
	        ti: [0, -0.233, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.866
	        },
	        o: {
	          x: 0.167,
	          y: 0.134
	        },
	        t: 14,
	        s: [10.908, 24.12, 0],
	        to: [0, 0.233, 0],
	        ti: [0, -0.232, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.865
	        },
	        o: {
	          x: 0.167,
	          y: 0.22
	        },
	        t: 15,
	        s: [10.908, 24.986, 0],
	        to: [0, 0.232, 0],
	        ti: [0, -0.142, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.895
	        },
	        o: {
	          x: 0.167,
	          y: 0.218
	        },
	        t: 16,
	        s: [10.908, 25.513, 0],
	        to: [0, 0.142, 0],
	        ti: [0, -0.067, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.766
	        },
	        o: {
	          x: 0.167,
	          y: 0.407
	        },
	        t: 17,
	        s: [10.908, 25.839, 0],
	        to: [0, 0.067, 0],
	        ti: [0, 0.012, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.745
	        },
	        o: {
	          x: 0.167,
	          y: 0.13
	        },
	        t: 18,
	        s: [10.908, 25.917, 0],
	        to: [0, -0.012, 0],
	        ti: [0, 0.077, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.816
	        },
	        o: {
	          x: 0.167,
	          y: 0.124
	        },
	        t: 19,
	        s: [10.908, 25.765, 0],
	        to: [0, -0.077, 0],
	        ti: [0, 0.114, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.841
	        },
	        o: {
	          x: 0.167,
	          y: 0.153
	        },
	        t: 20,
	        s: [10.908, 25.453, 0],
	        to: [0, -0.114, 0],
	        ti: [0, 0.119, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.86
	        },
	        o: {
	          x: 0.167,
	          y: 0.175
	        },
	        t: 21,
	        s: [10.908, 25.078, 0],
	        to: [0, -0.119, 0],
	        ti: [0, 0.096, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.886
	        },
	        o: {
	          x: 0.167,
	          y: 0.206
	        },
	        t: 22,
	        s: [10.908, 24.737, 0],
	        to: [0, -0.096, 0],
	        ti: [0, 0.053, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.857
	        },
	        o: {
	          x: 0.167,
	          y: 0.311
	        },
	        t: 23,
	        s: [10.908, 24.504, 0],
	        to: [0, -0.053, 0],
	        ti: [0, 0.004, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.684
	        },
	        o: {
	          x: 0.167,
	          y: 0.2
	        },
	        t: 24,
	        s: [10.908, 24.419, 0],
	        to: [0, -0.004, 0],
	        ti: [0, -0.038, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.807
	        },
	        o: {
	          x: 0.167,
	          y: 0.113
	        },
	        t: 25,
	        s: [10.908, 24.479, 0],
	        to: [0, 0.038, 0],
	        ti: [0, -0.066, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.836
	        },
	        o: {
	          x: 0.167,
	          y: 0.147
	        },
	        t: 26,
	        s: [10.908, 24.649, 0],
	        to: [0, 0.066, 0],
	        ti: [0, -0.073, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.855
	        },
	        o: {
	          x: 0.167,
	          y: 0.169
	        },
	        t: 27,
	        s: [10.908, 24.873, 0],
	        to: [0, 0.073, 0],
	        ti: [0, -0.063, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.878
	        },
	        o: {
	          x: 0.167,
	          y: 0.196
	        },
	        t: 28,
	        s: [10.908, 25.09, 0],
	        to: [0, 0.063, 0],
	        ti: [0, -0.039, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.89
	        },
	        o: {
	          x: 0.167,
	          y: 0.262
	        },
	        t: 29,
	        s: [10.908, 25.251, 0],
	        to: [0, 0.039, 0],
	        ti: [0, -0.01, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.615
	        },
	        o: {
	          x: 0.167,
	          y: 0.336
	        },
	        t: 30,
	        s: [10.908, 25.326, 0],
	        to: [0, 0.01, 0],
	        ti: [0, 0.018, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.833
	        },
	        o: {
	          x: 0.167,
	          y: 0.106
	        },
	        t: 31,
	        s: [10.908, 25.31, 0],
	        to: [0, -0.018, 0],
	        ti: [0, 0.015, 0]
	      }, {
	        t: 32,
	        s: [10.908, 25.22, 0]
	      }],
	      ix: 2,
	      l: 2
	    },
	    a: {
	      a: 0,
	      k: [8.158, 25.236, 0],
	      ix: 1,
	      l: 2
	    },
	    s: {
	      a: 0,
	      k: [100, 100, 100],
	      ix: 6,
	      l: 2
	    }
	  },
	  ao: 0,
	  ef: [{
	    ty: 34,
	    nm: "Puppet",
	    np: 6,
	    mn: "ADBE FreePin3",
	    ix: 1,
	    en: 1,
	    ef: [{
	      ty: 7,
	      nm: "Puppet Engine",
	      mn: "ADBE FreePin3 Puppet Engine",
	      ix: 1,
	      v: {
	        a: 0,
	        k: 2,
	        ix: 1
	      }
	    }, {
	      ty: 0,
	      nm: "Mesh Rotation Refinement",
	      mn: "ADBE FreePin3 Auto Rotate Pins",
	      ix: 2,
	      v: {
	        a: 0,
	        k: 20,
	        ix: 2
	      }
	    }, {
	      ty: 7,
	      nm: "On Transparent",
	      mn: "ADBE FreePin3 On Transparent",
	      ix: 3,
	      v: {
	        a: 0,
	        k: 0,
	        ix: 3
	      }
	    }, {
	      ty: "",
	      nm: "arap",
	      np: 3,
	      mn: "ADBE FreePin3 ARAP Group",
	      ix: 4,
	      en: 1,
	      ef: [{
	        ty: 6,
	        nm: "Auto-traced Shapes",
	        mn: "ADBE FreePin3 Outlines",
	        ix: 1,
	        v: 0
	      }, {
	        ty: "",
	        nm: "Mesh",
	        np: 1,
	        mn: "ADBE FreePin3 Mesh Group",
	        ix: 2,
	        en: 1,
	        ef: []
	      }]
	    }]
	  }],
	  shapes: [{
	    ty: "gr",
	    it: [{
	      ind: 0,
	      ty: "sh",
	      ix: 1,
	      ks: {
	        a: 0,
	        k: {
	          i: [[0.144, 0], [0, 0], [-0.027, -0.154], [0, 0], [-0.159, 0], [0, 0], [0, 0.172], [0, 0]],
	          o: [[0, 0], [-0.16, 0], [0, 0], [0.028, 0.155], [0, 0], [0.176, 0], [0, 0], [0, -0.141]],
	          v: [[-5.635, -1.652], [-8.715, -1.652], [-8.971, -1.355], [-7.182, 8.697], [-6.859, 8.964], [-5.719, 8.964], [-5.4, 8.652], [-5.376, -1.396]],
	          c: true
	        },
	        ix: 2
	      },
	      nm: "Path 1",
	      mn: "ADBE Vector Shape - Group",
	      hd: false
	    }, {
	      ind: 1,
	      ty: "sh",
	      ix: 2,
	      ks: {
	        a: 0,
	        k: {
	          i: [[0.611, 0], [0, 0], [0.046, 0.112], [-0.211, 0.477], [0.277, 1.05], [1.336, 0.061], [0.21, -0.366], [0, -0.074], [0.061, -0.891], [1.084, -1.441], [0.113, -0.017], [0.279, -0.037], [0, -0.038], [0, 0], [-0.064, -0.011], [-0.553, -0.178], [-1.157, -0.39], [-0.064, 0], [0, 0], [-0.035, 0.72], [0.164, 0.365], [-0.057, 0.011], [1.073, 1.065], [-0.038, 0.01], [-0.138, 0.589], [0.067, 0.221], [0.166, 0.22], [-0.067, 0.022], [0.004, 0.641]],
	          o: [[0, 0], [-0.123, 0], [-0.192, -0.48], [0.353, -1.027], [-0.29, -0.723], [-0.418, 0.061], [-0.037, 0.065], [0, 0], [-0.062, 0.891], [-0.069, 0.092], [-0.406, 0.061], [-0.066, 0.009], [0, 0], [0, 0.029], [0.219, 0.036], [0.694, 0.222], [0.061, 0.021], [0, 0], [0.702, -0.125], [0.01, -0.402], [-0.023, -0.052], [0.697, -0.127], [-0.028, -0.028], [0.592, -0.151], [0.054, -0.225], [-0.079, -0.264], [-0.042, -0.056], [0.589, -0.199], [0.068, -0.628]],
	          v: [[7.882, -1.992], [3.62, -1.992], [3.333, -2.174], [3.359, -3.673], [3.475, -6.859], [1.773, -8.964], [0.779, -8.285], [0.727, -8.068], [0.727, -5.896], [-2.124, -1.292], [-2.402, -1.124], [-3.863, -0.923], [-3.967, -0.794], [-3.967, 7.292], [-3.867, 7.398], [-2.549, 7.712], [-0.118, 8.852], [0.075, 8.883], [5.51, 8.883], [6.77, 7.439], [6.536, 6.275], [6.6, 6.156], [7.094, 3.641], [7.109, 3.566], [8.292, 2.354], [8.26, 1.674], [7.891, 0.943], [7.939, 0.794], [8.93, -0.609]],
	          c: true
	        },
	        ix: 2
	      },
	      nm: "Path 2",
	      mn: "ADBE Vector Shape - Group",
	      hd: false
	    }, {
	      ty: "mm",
	      mm: 1,
	      nm: "Merge Paths 1",
	      mn: "ADBE Vector Filter - Merge",
	      hd: false
	    }, {
	      ty: "fl",
	      c: {
	        a: 0,
	        k: [1, 1, 1, 1],
	        ix: 4
	      },
	      o: {
	        a: 0,
	        k: 100,
	        ix: 5
	      },
	      r: 1,
	      bm: 0,
	      nm: "Fill 1",
	      mn: "ADBE Vector Graphic - Fill",
	      hd: false
	    }, {
	      ty: "tr",
	      p: {
	        a: 0,
	        k: [17.132, 16.273],
	        ix: 2
	      },
	      a: {
	        a: 0,
	        k: [0, 0],
	        ix: 1
	      },
	      s: {
	        a: 0,
	        k: [100, 100],
	        ix: 3
	      },
	      r: {
	        a: 0,
	        k: 0,
	        ix: 6
	      },
	      o: {
	        a: 0,
	        k: 100,
	        ix: 7
	      },
	      sk: {
	        a: 0,
	        k: 0,
	        ix: 4
	      },
	      sa: {
	        a: 0,
	        k: 0,
	        ix: 5
	      },
	      nm: "Transform"
	    }],
	    nm: "Group 1",
	    np: 4,
	    cix: 2,
	    bm: 0,
	    ix: 1,
	    mn: "ADBE Vector Group",
	    hd: false
	  }],
	  ip: 0,
	  op: 33,
	  st: 0,
	  bm: 0
	}, {
	  ddd: 0,
	  ind: 2,
	  ty: 4,
	  nm: "Layer 1/Emotions Outlines",
	  sr: 1,
	  ks: {
	    o: {
	      a: 0,
	      k: 100,
	      ix: 11
	    },
	    r: {
	      a: 0,
	      k: 0,
	      ix: 10
	    },
	    p: {
	      a: 0,
	      k: [20, 17, 0],
	      ix: 2,
	      l: 2
	    },
	    a: {
	      a: 0,
	      k: [17.25, 17.25, 0],
	      ix: 1,
	      l: 2
	    },
	    s: {
	      a: 0,
	      k: [100, 100, 100],
	      ix: 6,
	      l: 2
	    }
	  },
	  ao: 0,
	  shapes: [{
	    ty: "gr",
	    it: [{
	      ind: 0,
	      ty: "sh",
	      ix: 1,
	      ks: {
	        a: 0,
	        k: {
	          i: [[-9.389, 0], [0, -9.389], [9.389, 0], [0, 9.389]],
	          o: [[9.389, 0], [0, 9.389], [-9.389, 0], [0, -9.389]],
	          v: [[0, -17], [17, 0], [0, 17], [-17, 0]],
	          c: true
	        },
	        ix: 2
	      },
	      nm: "Path 1",
	      mn: "ADBE Vector Shape - Group",
	      hd: false
	    }, {
	      ty: "fl",
	      c: {
	        a: 0,
	        k: [0.384313755409, 0.662745098039, 0.952941236309, 1],
	        ix: 4
	      },
	      o: {
	        a: 0,
	        k: 100,
	        ix: 5
	      },
	      r: 1,
	      bm: 0,
	      nm: "Fill 1",
	      mn: "ADBE Vector Graphic - Fill",
	      hd: false
	    }, {
	      ty: "tr",
	      p: {
	        a: 0,
	        k: [17.25, 17.25],
	        ix: 2
	      },
	      a: {
	        a: 0,
	        k: [0, 0],
	        ix: 1
	      },
	      s: {
	        a: 0,
	        k: [100, 100],
	        ix: 3
	      },
	      r: {
	        a: 0,
	        k: 0,
	        ix: 6
	      },
	      o: {
	        a: 0,
	        k: 100,
	        ix: 7
	      },
	      sk: {
	        a: 0,
	        k: 0,
	        ix: 4
	      },
	      sa: {
	        a: 0,
	        k: 0,
	        ix: 5
	      },
	      nm: "Transform"
	    }],
	    nm: "Group 2",
	    np: 2,
	    cix: 2,
	    bm: 0,
	    ix: 1,
	    mn: "ADBE Vector Group",
	    hd: false
	  }],
	  ip: 0,
	  op: 33,
	  st: 0,
	  bm: 0
	}];
	var markers = [];
	var likeAnimatedEmojiData = {
	  v: v,
	  fr: fr,
	  ip: ip,
	  op: op,
	  w: w,
	  h: h,
	  nm: nm,
	  ddd: ddd,
	  assets: assets,
	  layers: layers,
	  markers: markers
	};

	var v$1 = "5.9.1";
	var fr$1 = 25;
	var ip$1 = 0;
	var op$1 = 30;
	var w$1 = 40;
	var h$1 = 40;
	var nm$1 = "em_02";
	var ddd$1 = 0;
	var assets$1 = [];
	var layers$1 = [{
	  ddd: 0,
	  ind: 1,
	  ty: 4,
	  nm: "Group 1 :M",
	  sr: 1,
	  ks: {
	    o: {
	      a: 0,
	      k: 100,
	      ix: 11
	    },
	    r: {
	      a: 0,
	      k: 0,
	      ix: 10
	    },
	    p: {
	      a: 1,
	      k: [{
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: 0,
	        s: [19.888, 22.482, 0],
	        to: [0, -0.375, 0],
	        ti: [0, -0.167, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: 4,
	        s: [19.888, 20.232, 0],
	        to: [0, 0.167, 0],
	        ti: [0, -0.125, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: 7.979,
	        s: [19.888, 23.482, 0],
	        to: [0, 0.125, 0],
	        ti: [0, -0.146, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: 12.764,
	        s: [19.888, 20.982, 0],
	        to: [0, 0.146, 0],
	        ti: [0, -0.042, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: 16.647,
	        s: [19.888, 24.357, 0],
	        to: [0, 0.042, 0],
	        ti: [0, 0.312, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: 22.427,
	        s: [19.888, 21.232, 0],
	        to: [0, -0.312, 0],
	        ti: [0, -0.208, 0]
	      }, {
	        t: 27.400390625,
	        s: [19.888, 22.482, 0]
	      }],
	      ix: 2,
	      l: 2
	    },
	    a: {
	      a: 0,
	      k: [17.139, 22.732, 0],
	      ix: 1,
	      l: 2
	    },
	    s: {
	      a: 0,
	      k: [100, 100, 100],
	      ix: 6,
	      l: 2
	    }
	  },
	  ao: 0,
	  shapes: [{
	    ty: "gr",
	    it: [{
	      ty: "gr",
	      it: [{
	        ind: 0,
	        ty: "sh",
	        ix: 1,
	        ks: {
	          a: 0,
	          k: {
	            i: [[0, -1.596], [6.293, 0], [0, 3.023], [-5.122, 0]],
	            o: [[0, 3.024], [-6.292, 0], [0, -1.63], [5.122, 0]],
	            v: [[9.273, -3.113], [0, 4.742], [-9.273, -3.111], [0, -3.189]],
	            c: true
	          },
	          ix: 2
	        },
	        nm: "Path 1",
	        mn: "ADBE Vector Shape - Group",
	        hd: false
	      }, {
	        ty: "fl",
	        c: {
	          a: 0,
	          k: [1, 1, 1, 1],
	          ix: 4
	        },
	        o: {
	          a: 0,
	          k: 100,
	          ix: 5
	        },
	        r: 1,
	        bm: 0,
	        nm: "Fill 1",
	        mn: "ADBE Vector Graphic - Fill",
	        hd: false
	      }, {
	        ty: "tr",
	        p: {
	          a: 0,
	          k: [17.138, 22.377],
	          ix: 2
	        },
	        a: {
	          a: 0,
	          k: [0, 0],
	          ix: 1
	        },
	        s: {
	          a: 0,
	          k: [100, 100],
	          ix: 3
	        },
	        r: {
	          a: 0,
	          k: 0,
	          ix: 6
	        },
	        o: {
	          a: 0,
	          k: 100,
	          ix: 7
	        },
	        sk: {
	          a: 0,
	          k: 0,
	          ix: 4
	        },
	        sa: {
	          a: 0,
	          k: 0,
	          ix: 5
	        },
	        nm: "Transform"
	      }],
	      nm: "Group 1",
	      np: 2,
	      cix: 2,
	      bm: 0,
	      ix: 1,
	      mn: "ADBE Vector Group",
	      hd: false
	    }, {
	      ty: "tr",
	      p: {
	        a: 0,
	        k: [17.138, 22.819],
	        ix: 2
	      },
	      a: {
	        a: 0,
	        k: [17.138, 22.819],
	        ix: 1
	      },
	      s: {
	        a: 0,
	        k: [100, 100],
	        ix: 3
	      },
	      r: {
	        a: 0,
	        k: 0,
	        ix: 6
	      },
	      o: {
	        a: 0,
	        k: 100,
	        ix: 7
	      },
	      sk: {
	        a: 0,
	        k: 0,
	        ix: 4
	      },
	      sa: {
	        a: 0,
	        k: 0,
	        ix: 5
	      },
	      nm: "Transform"
	    }],
	    nm: "Group 1",
	    np: 1,
	    cix: 2,
	    bm: 0,
	    ix: 1,
	    mn: "ADBE Vector Group",
	    hd: false
	  }, {
	    ty: "gr",
	    it: [{
	      ty: "gr",
	      it: [{
	        ind: 0,
	        ty: "sh",
	        ix: 1,
	        ks: {
	          a: 0,
	          k: {
	            i: [[0, -2.332], [7.202, 0], [0, 4.104], [-6.085, 0]],
	            o: [[0, 4.105], [-7.201, 0], [0, -2.367], [6.086, 0]],
	            v: [[10.757, -3.799], [-0.001, 6.165], [-10.757, -3.798], [-0.001, -4.253]],
	            c: true
	          },
	          ix: 2
	        },
	        nm: "Path 1",
	        mn: "ADBE Vector Shape - Group",
	        hd: false
	      }, {
	        ty: "fl",
	        c: {
	          a: 0,
	          k: [0.698039233685, 0.392156898975, 0.137254908681, 1],
	          ix: 4
	        },
	        o: {
	          a: 0,
	          k: 100,
	          ix: 5
	        },
	        r: 1,
	        bm: 0,
	        nm: "Fill 1",
	        mn: "ADBE Vector Graphic - Fill",
	        hd: false
	      }, {
	        ty: "tr",
	        p: {
	          a: 0,
	          k: [17.139, 22.141],
	          ix: 2
	        },
	        a: {
	          a: 0,
	          k: [0, 0],
	          ix: 1
	        },
	        s: {
	          a: 0,
	          k: [100, 100],
	          ix: 3
	        },
	        r: {
	          a: 0,
	          k: 0,
	          ix: 6
	        },
	        o: {
	          a: 0,
	          k: 100,
	          ix: 7
	        },
	        sk: {
	          a: 0,
	          k: 0,
	          ix: 4
	        },
	        sa: {
	          a: 0,
	          k: 0,
	          ix: 5
	        },
	        nm: "Transform"
	      }],
	      nm: "Group 2",
	      np: 2,
	      cix: 2,
	      bm: 0,
	      ix: 1,
	      mn: "ADBE Vector Group",
	      hd: false
	    }, {
	      ty: "tr",
	      p: {
	        a: 0,
	        k: [17.139, 22.732],
	        ix: 2
	      },
	      a: {
	        a: 0,
	        k: [17.139, 22.732],
	        ix: 1
	      },
	      s: {
	        a: 0,
	        k: [100, 100],
	        ix: 3
	      },
	      r: {
	        a: 0,
	        k: 0,
	        ix: 6
	      },
	      o: {
	        a: 0,
	        k: 100,
	        ix: 7
	      },
	      sk: {
	        a: 0,
	        k: 0,
	        ix: 4
	      },
	      sa: {
	        a: 0,
	        k: 0,
	        ix: 5
	      },
	      nm: "Transform"
	    }],
	    nm: "Group 2",
	    np: 1,
	    cix: 2,
	    bm: 0,
	    ix: 2,
	    mn: "ADBE Vector Group",
	    hd: false
	  }],
	  ip: 0,
	  op: 30,
	  st: 0,
	  bm: 0
	}, {
	  ddd: 0,
	  ind: 2,
	  ty: 4,
	  nm: "Group 3 :M",
	  sr: 1,
	  ks: {
	    o: {
	      a: 0,
	      k: 100,
	      ix: 11
	    },
	    r: {
	      a: 0,
	      k: 0,
	      ix: 10
	    },
	    p: {
	      a: 1,
	      k: [{
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: 0,
	        s: [19.167, 11.243, 0],
	        to: [0, -0.375, 0],
	        ti: [0, -0.167, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: 4,
	        s: [19.167, 8.993, 0],
	        to: [0, 0.167, 0],
	        ti: [0, -0.125, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: 7.979,
	        s: [19.167, 12.243, 0],
	        to: [0, 0.125, 0],
	        ti: [0, 0.083, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: 12.764,
	        s: [19.167, 9.743, 0],
	        to: [0, -0.083, 0],
	        ti: [0, -0.125, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: 16.647,
	        s: [19.167, 11.743, 0],
	        to: [0, 0.125, 0],
	        ti: [0, 0.083, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: 21.432,
	        s: [19.167, 10.493, 0],
	        to: [0, -0.083, 0],
	        ti: [0, -0.125, 0]
	      }, {
	        t: 27.400390625,
	        s: [19.167, 11.243, 0]
	      }],
	      ix: 2,
	      l: 2
	    },
	    a: {
	      a: 0,
	      k: [16.417, 11.493, 0],
	      ix: 1,
	      l: 2
	    },
	    s: {
	      a: 0,
	      k: [100, 100, 100],
	      ix: 6,
	      l: 2
	    }
	  },
	  ao: 0,
	  shapes: [{
	    ty: "gr",
	    it: [{
	      ty: "gr",
	      it: [{
	        ind: 0,
	        ty: "sh",
	        ix: 1,
	        ks: {
	          a: 0,
	          k: {
	            i: [[1.007, 0.088], [0.848, -0.551], [0.007, -0.072], [0, 0], [-0.157, 0.074], [-0.788, -0.069], [-0.746, -0.701], [-0.015, 0.19], [0, 0], [0.052, 0.048]],
	            o: [[-1.008, -0.088], [-0.06, 0.039], [0, 0], [-0.015, 0.173], [0.701, -0.335], [1.017, 0.089], [0.137, 0.13], [0, 0], [0.006, -0.072], [-0.743, -0.689]],
	            v: [[0.132, -1.337], [-2.693, -0.592], [-2.798, -0.415], [-2.854, 0.247], [-2.536, 0.475], [-0.284, 0.053], [2.395, 1.294], [2.771, 1.146], [2.863, 0.074], [2.79, -0.117]],
	            c: true
	          },
	          ix: 2
	        },
	        nm: "Path 1",
	        mn: "ADBE Vector Shape - Group",
	        hd: false
	      }, {
	        ty: "fl",
	        c: {
	          a: 0,
	          k: [0.47058826685, 0.243137270212, 0.066666670144, 1],
	          ix: 4
	        },
	        o: {
	          a: 0,
	          k: 100,
	          ix: 5
	        },
	        r: 1,
	        bm: 0,
	        nm: "Fill 1",
	        mn: "ADBE Vector Graphic - Fill",
	        hd: false
	      }, {
	        ty: "tr",
	        p: {
	          a: 0,
	          k: [10.476, 11.491],
	          ix: 2
	        },
	        a: {
	          a: 0,
	          k: [0, 0],
	          ix: 1
	        },
	        s: {
	          a: 0,
	          k: [100, 100],
	          ix: 3
	        },
	        r: {
	          a: 0,
	          k: 0,
	          ix: 6
	        },
	        o: {
	          a: 0,
	          k: 100,
	          ix: 7
	        },
	        sk: {
	          a: 0,
	          k: 0,
	          ix: 4
	        },
	        sa: {
	          a: 0,
	          k: 0,
	          ix: 5
	        },
	        nm: "Transform"
	      }],
	      nm: "Group 3",
	      np: 2,
	      cix: 2,
	      bm: 0,
	      ix: 1,
	      mn: "ADBE Vector Group",
	      hd: false
	    }, {
	      ty: "tr",
	      p: {
	        a: 0,
	        k: [10.48, 11.493],
	        ix: 2
	      },
	      a: {
	        a: 0,
	        k: [10.48, 11.493],
	        ix: 1
	      },
	      s: {
	        a: 0,
	        k: [100, 100],
	        ix: 3
	      },
	      r: {
	        a: 0,
	        k: 0,
	        ix: 6
	      },
	      o: {
	        a: 0,
	        k: 100,
	        ix: 7
	      },
	      sk: {
	        a: 0,
	        k: 0,
	        ix: 4
	      },
	      sa: {
	        a: 0,
	        k: 0,
	        ix: 5
	      },
	      nm: "Transform"
	    }],
	    nm: "Group 3",
	    np: 1,
	    cix: 2,
	    bm: 0,
	    ix: 1,
	    mn: "ADBE Vector Group",
	    hd: false
	  }, {
	    ty: "gr",
	    it: [{
	      ty: "gr",
	      it: [{
	        ind: 0,
	        ty: "sh",
	        ix: 1,
	        ks: {
	          a: 0,
	          k: {
	            i: [[0, 0], [0.077, 0.049], [0.998, -0.088], [0.739, -0.676], [-0.008, -0.092], [0, 0], [-0.18, 0.162], [-0.973, 0.085], [-0.674, -0.302], [0.019, 0.224]],
	            o: [[-0.008, -0.092], [-0.841, -0.539], [-0.996, 0.086], [-0.068, 0.062], [0, 0], [0.021, 0.243], [0.729, -0.65], [0.751, -0.066], [0.202, 0.091], [0, 0]],
	            v: [[2.802, -0.355], [2.668, -0.582], [-0.129, -1.308], [-2.763, -0.112], [-2.857, 0.135], [-2.78, 1.044], [-2.296, 1.234], [0.286, 0.081], [2.44, 0.457], [2.846, 0.165]],
	            c: true
	          },
	          ix: 2
	        },
	        nm: "Path 1",
	        mn: "ADBE Vector Shape - Group",
	        hd: false
	      }, {
	        ty: "fl",
	        c: {
	          a: 0,
	          k: [0.47058826685, 0.243137270212, 0.066666670144, 1],
	          ix: 4
	        },
	        o: {
	          a: 0,
	          k: 100,
	          ix: 5
	        },
	        r: 1,
	        bm: 0,
	        nm: "Fill 1",
	        mn: "ADBE Vector Graphic - Fill",
	        hd: false
	      }, {
	        ty: "tr",
	        p: {
	          a: 0,
	          k: [22.365, 11.463],
	          ix: 2
	        },
	        a: {
	          a: 0,
	          k: [0, 0],
	          ix: 1
	        },
	        s: {
	          a: 0,
	          k: [100, 100],
	          ix: 3
	        },
	        r: {
	          a: 0,
	          k: 0,
	          ix: 6
	        },
	        o: {
	          a: 0,
	          k: 100,
	          ix: 7
	        },
	        sk: {
	          a: 0,
	          k: 0,
	          ix: 4
	        },
	        sa: {
	          a: 0,
	          k: 0,
	          ix: 5
	        },
	        nm: "Transform"
	      }],
	      nm: "Group 4",
	      np: 2,
	      cix: 2,
	      bm: 0,
	      ix: 1,
	      mn: "ADBE Vector Group",
	      hd: false
	    }, {
	      ty: "tr",
	      p: {
	        a: 0,
	        k: [22.359, 11.457],
	        ix: 2
	      },
	      a: {
	        a: 0,
	        k: [22.359, 11.457],
	        ix: 1
	      },
	      s: {
	        a: 0,
	        k: [100, 100],
	        ix: 3
	      },
	      r: {
	        a: 0,
	        k: 0,
	        ix: 6
	      },
	      o: {
	        a: 0,
	        k: 100,
	        ix: 7
	      },
	      sk: {
	        a: 0,
	        k: 0,
	        ix: 4
	      },
	      sa: {
	        a: 0,
	        k: 0,
	        ix: 5
	      },
	      nm: "Transform"
	    }],
	    nm: "Group 4",
	    np: 1,
	    cix: 2,
	    bm: 0,
	    ix: 2,
	    mn: "ADBE Vector Group",
	    hd: false
	  }],
	  ip: 0,
	  op: 30,
	  st: 0,
	  bm: 0
	}, {
	  ddd: 0,
	  ind: 3,
	  ty: 4,
	  nm: "Group 5",
	  sr: 1,
	  ks: {
	    o: {
	      a: 0,
	      k: 100,
	      ix: 11
	    },
	    r: {
	      a: 0,
	      k: 0,
	      ix: 10
	    },
	    p: {
	      a: 0,
	      k: [20, 17, 0],
	      ix: 2,
	      l: 2
	    },
	    a: {
	      a: 0,
	      k: [17.25, 17.25, 0],
	      ix: 1,
	      l: 2
	    },
	    s: {
	      a: 0,
	      k: [100, 100, 100],
	      ix: 6,
	      l: 2
	    }
	  },
	  ao: 0,
	  shapes: [{
	    ty: "gr",
	    it: [{
	      ind: 0,
	      ty: "sh",
	      ix: 1,
	      ks: {
	        a: 0,
	        k: {
	          i: [[-9.389, 0], [0, -9.389], [9.389, 0], [0, 9.389]],
	          o: [[9.389, 0], [0, 9.389], [-9.389, 0], [0, -9.389]],
	          v: [[0, -17], [17, 0], [0, 17], [-17, 0]],
	          c: true
	        },
	        ix: 2
	      },
	      nm: "Path 1",
	      mn: "ADBE Vector Shape - Group",
	      hd: false
	    }, {
	      ty: "fl",
	      c: {
	        a: 0,
	        k: [0.964705942191, 0.878431432387, 0.443137284821, 1],
	        ix: 4
	      },
	      o: {
	        a: 0,
	        k: 100,
	        ix: 5
	      },
	      r: 1,
	      bm: 0,
	      nm: "Fill 1",
	      mn: "ADBE Vector Graphic - Fill",
	      hd: false
	    }, {
	      ty: "tr",
	      p: {
	        a: 0,
	        k: [17.25, 17.25],
	        ix: 2
	      },
	      a: {
	        a: 0,
	        k: [0, 0],
	        ix: 1
	      },
	      s: {
	        a: 0,
	        k: [100, 100],
	        ix: 3
	      },
	      r: {
	        a: 0,
	        k: 0,
	        ix: 6
	      },
	      o: {
	        a: 0,
	        k: 100,
	        ix: 7
	      },
	      sk: {
	        a: 0,
	        k: 0,
	        ix: 4
	      },
	      sa: {
	        a: 0,
	        k: 0,
	        ix: 5
	      },
	      nm: "Transform"
	    }],
	    nm: "Group 5",
	    np: 2,
	    cix: 2,
	    bm: 0,
	    ix: 1,
	    mn: "ADBE Vector Group",
	    hd: false
	  }],
	  ip: 0,
	  op: 30,
	  st: 0,
	  bm: 0
	}];
	var markers$1 = [];
	var laughAnimatedEmojiData = {
	  v: v$1,
	  fr: fr$1,
	  ip: ip$1,
	  op: op$1,
	  w: w$1,
	  h: h$1,
	  nm: nm$1,
	  ddd: ddd$1,
	  assets: assets$1,
	  layers: layers$1,
	  markers: markers$1
	};

	var v$2 = "5.9.1";
	var fr$2 = 25;
	var ip$2 = 0;
	var op$2 = 45;
	var w$2 = 40;
	var h$2 = 40;
	var nm$2 = "em_03";
	var ddd$2 = 0;
	var assets$2 = [];
	var layers$2 = [{
	  ddd: 0,
	  ind: 1,
	  ty: 4,
	  nm: "Group 1",
	  sr: 1,
	  ks: {
	    o: {
	      a: 0,
	      k: 100,
	      ix: 11
	    },
	    r: {
	      a: 0,
	      k: 0,
	      ix: 10
	    },
	    p: {
	      a: 1,
	      k: [{
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: 4,
	        s: [20.619, 24.041, 0],
	        to: [0.667, -0.25, 0],
	        ti: [0.708, 0.583, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.784,
	          y: 0
	        },
	        t: 18,
	        s: [24.619, 22.541, 0],
	        to: [-0.413, -0.34, 0],
	        ti: [2.093, 0.432, 0]
	      }, {
	        i: {
	          x: 0.139,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: 31,
	        s: [18.842, 19.508, 0],
	        to: [-1.495, -0.309, 0],
	        ti: [-0.296, -0.756, 0]
	      }, {
	        t: 44,
	        s: [20.619, 24.041, 0]
	      }],
	      ix: 2,
	      l: 2
	    },
	    a: {
	      a: 0,
	      k: [17.869, 24.291, 0],
	      ix: 1,
	      l: 2
	    },
	    s: {
	      a: 1,
	      k: [{
	        i: {
	          x: [0.667, 0, 0.667],
	          y: [1, 0.999, 1]
	        },
	        o: {
	          x: [0.333, 0.333, 0.333],
	          y: [0, 0, 0]
	        },
	        t: 10,
	        s: [100, 100, 100]
	      }, {
	        i: {
	          x: [0.667, 0.667, 0.667],
	          y: [1, 1, 1]
	        },
	        o: {
	          x: [0.333, 0.333, 0.333],
	          y: [0, 0, 0]
	        },
	        t: 23,
	        s: [100, 140, 100]
	      }, {
	        i: {
	          x: [0, 0.667, 0.667],
	          y: [1, 1, 1]
	        },
	        o: {
	          x: [1, 0.888, 0.333],
	          y: [0, 0, 0]
	        },
	        t: 30,
	        s: [100, 140, 100]
	      }, {
	        t: 38,
	        s: [100, 100, 100]
	      }],
	      ix: 6,
	      l: 2
	    }
	  },
	  ao: 0,
	  shapes: [{
	    ty: "gr",
	    it: [{
	      d: 1,
	      ty: "el",
	      s: {
	        a: 0,
	        k: [9, 9],
	        ix: 2
	      },
	      p: {
	        a: 0,
	        k: [0, 0],
	        ix: 3
	      },
	      nm: "Ellipse Path 1",
	      mn: "ADBE Vector Shape - Ellipse",
	      hd: false
	    }, {
	      ty: "fl",
	      c: {
	        a: 0,
	        k: [0.470588265213, 0.243137269862, 0.066666666667, 1],
	        ix: 4
	      },
	      o: {
	        a: 0,
	        k: 100,
	        ix: 5
	      },
	      r: 1,
	      bm: 0,
	      nm: "Fill 1",
	      mn: "ADBE Vector Graphic - Fill",
	      hd: false
	    }, {
	      ty: "tr",
	      p: {
	        a: 0,
	        k: [17.869, 24.291],
	        ix: 2
	      },
	      a: {
	        a: 0,
	        k: [0, 0],
	        ix: 1
	      },
	      s: {
	        a: 0,
	        k: [100, 100],
	        ix: 3
	      },
	      r: {
	        a: 0,
	        k: 0,
	        ix: 6
	      },
	      o: {
	        a: 0,
	        k: 100,
	        ix: 7
	      },
	      sk: {
	        a: 0,
	        k: 0,
	        ix: 4
	      },
	      sa: {
	        a: 0,
	        k: 0,
	        ix: 5
	      },
	      nm: "Transform"
	    }],
	    nm: "Group 1",
	    np: 2,
	    cix: 2,
	    bm: 0,
	    ix: 1,
	    mn: "ADBE Vector Group",
	    hd: false
	  }],
	  ip: 0,
	  op: 45,
	  st: 0,
	  bm: 0
	}, {
	  ddd: 0,
	  ind: 2,
	  ty: 4,
	  nm: "Group 2",
	  sr: 1,
	  ks: {
	    o: {
	      a: 0,
	      k: 100,
	      ix: 11
	    },
	    r: {
	      a: 0,
	      k: 0,
	      ix: 10
	    },
	    p: {
	      a: 1,
	      k: [{
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: 4,
	        s: [25.962, 12.071, 0],
	        to: [0.667, -0.25, 0],
	        ti: [0.708, 0.583, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.784,
	          y: 0
	        },
	        t: 18,
	        s: [29.962, 10.571, 0],
	        to: [-0.413, -0.34, 0],
	        ti: [2.093, 0.432, 0]
	      }, {
	        i: {
	          x: 0.139,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: 31,
	        s: [24.185, 7.538, 0],
	        to: [-1.495, -0.309, 0],
	        ti: [-0.296, -0.756, 0]
	      }, {
	        t: 44,
	        s: [25.962, 12.071, 0]
	      }],
	      ix: 2,
	      l: 2
	    },
	    a: {
	      a: 0,
	      k: [23.212, 12.321, 0],
	      ix: 1,
	      l: 2
	    },
	    s: {
	      a: 1,
	      k: [{
	        i: {
	          x: [0.667, 0.667, 0.667],
	          y: [1, 1, 1]
	        },
	        o: {
	          x: [0.333, 0.333, 0.333],
	          y: [0, 0, 0]
	        },
	        t: 18,
	        s: [100, 100, 100]
	      }, {
	        i: {
	          x: [0.667, 0.667, 0.667],
	          y: [1, 1, 1]
	        },
	        o: {
	          x: [0.333, 0.333, 0.333],
	          y: [0, 0, 0]
	        },
	        t: 31,
	        s: [57, 100, 100]
	      }, {
	        t: 38,
	        s: [100, 100, 100]
	      }],
	      ix: 6,
	      l: 2
	    }
	  },
	  ao: 0,
	  shapes: [{
	    ty: "gr",
	    it: [{
	      ty: "gr",
	      it: [{
	        d: 1,
	        ty: "el",
	        s: {
	          a: 0,
	          k: [5, 5],
	          ix: 2
	        },
	        p: {
	          a: 0,
	          k: [0, 0],
	          ix: 3
	        },
	        nm: "Ellipse Path 1",
	        mn: "ADBE Vector Shape - Ellipse",
	        hd: false
	      }, {
	        ty: "fl",
	        c: {
	          a: 0,
	          k: [0.47058826685, 0.243137270212, 0.066666670144, 1],
	          ix: 4
	        },
	        o: {
	          a: 0,
	          k: 100,
	          ix: 5
	        },
	        r: 1,
	        bm: 0,
	        nm: "Fill 1",
	        mn: "ADBE Vector Graphic - Fill",
	        hd: false
	      }, {
	        ty: "tr",
	        p: {
	          a: 0,
	          k: [23.212, 12.321],
	          ix: 2
	        },
	        a: {
	          a: 0,
	          k: [0, 0],
	          ix: 1
	        },
	        s: {
	          a: 0,
	          k: [100, 100],
	          ix: 3
	        },
	        r: {
	          a: 0,
	          k: 0,
	          ix: 6
	        },
	        o: {
	          a: 0,
	          k: 100,
	          ix: 7
	        },
	        sk: {
	          a: 0,
	          k: 0,
	          ix: 4
	        },
	        sa: {
	          a: 0,
	          k: 0,
	          ix: 5
	        },
	        nm: "Transform"
	      }],
	      nm: "Group 2",
	      np: 2,
	      cix: 2,
	      bm: 0,
	      ix: 1,
	      mn: "ADBE Vector Group",
	      hd: false
	    }, {
	      ty: "tr",
	      p: {
	        a: 0,
	        k: [23.212, 12.321],
	        ix: 2
	      },
	      a: {
	        a: 0,
	        k: [23.212, 12.321],
	        ix: 1
	      },
	      s: {
	        a: 0,
	        k: [100, 100],
	        ix: 3
	      },
	      r: {
	        a: 0,
	        k: 0,
	        ix: 6
	      },
	      o: {
	        a: 0,
	        k: 100,
	        ix: 7
	      },
	      sk: {
	        a: 0,
	        k: 0,
	        ix: 4
	      },
	      sa: {
	        a: 0,
	        k: 0,
	        ix: 5
	      },
	      nm: "Transform"
	    }],
	    nm: "Group 2",
	    np: 1,
	    cix: 2,
	    bm: 0,
	    ix: 1,
	    mn: "ADBE Vector Group",
	    hd: false
	  }],
	  ip: 0,
	  op: 45,
	  st: 0,
	  bm: 0
	}, {
	  ddd: 0,
	  ind: 3,
	  ty: 4,
	  nm: "Group 3",
	  sr: 1,
	  ks: {
	    o: {
	      a: 0,
	      k: 100,
	      ix: 11
	    },
	    r: {
	      a: 0,
	      k: 0,
	      ix: 10
	    },
	    p: {
	      a: 1,
	      k: [{
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: 4,
	        s: [13.379, 12.071, 0],
	        to: [0.667, -0.25, 0],
	        ti: [0.708, 0.583, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.784,
	          y: 0
	        },
	        t: 18,
	        s: [17.379, 10.571, 0],
	        to: [-0.413, -0.34, 0],
	        ti: [2.093, 0.432, 0]
	      }, {
	        i: {
	          x: 0.139,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: 31,
	        s: [11.602, 7.538, 0],
	        to: [-1.495, -0.309, 0],
	        ti: [-0.296, -0.756, 0]
	      }, {
	        t: 44,
	        s: [13.379, 12.071, 0]
	      }],
	      ix: 2,
	      l: 2
	    },
	    a: {
	      a: 0,
	      k: [10.629, 12.321, 0],
	      ix: 1,
	      l: 2
	    },
	    s: {
	      a: 1,
	      k: [{
	        i: {
	          x: [0.667, 0.667, 0.667],
	          y: [1, 1, 1]
	        },
	        o: {
	          x: [0.333, 0.333, 0.333],
	          y: [0, 0, 0]
	        },
	        t: 18,
	        s: [100, 100, 100]
	      }, {
	        i: {
	          x: [0.667, 0.667, 0.667],
	          y: [1, 1, 1]
	        },
	        o: {
	          x: [0.333, 0.333, 0.333],
	          y: [0, 0, 0]
	        },
	        t: 31,
	        s: [57, 100, 100]
	      }, {
	        t: 38,
	        s: [100, 100, 100]
	      }],
	      ix: 6,
	      l: 2
	    }
	  },
	  ao: 0,
	  shapes: [{
	    ty: "gr",
	    it: [{
	      ty: "gr",
	      it: [{
	        d: 1,
	        ty: "el",
	        s: {
	          a: 0,
	          k: [5, 5],
	          ix: 2
	        },
	        p: {
	          a: 0,
	          k: [0, 0],
	          ix: 3
	        },
	        nm: "Ellipse Path 1",
	        mn: "ADBE Vector Shape - Ellipse",
	        hd: false
	      }, {
	        ty: "fl",
	        c: {
	          a: 0,
	          k: [0.47058826685, 0.243137270212, 0.066666670144, 1],
	          ix: 4
	        },
	        o: {
	          a: 0,
	          k: 100,
	          ix: 5
	        },
	        r: 1,
	        bm: 0,
	        nm: "Fill 1",
	        mn: "ADBE Vector Graphic - Fill",
	        hd: false
	      }, {
	        ty: "tr",
	        p: {
	          a: 0,
	          k: [10.629, 12.321],
	          ix: 2
	        },
	        a: {
	          a: 0,
	          k: [0, 0],
	          ix: 1
	        },
	        s: {
	          a: 0,
	          k: [100, 100],
	          ix: 3
	        },
	        r: {
	          a: 0,
	          k: 0,
	          ix: 6
	        },
	        o: {
	          a: 0,
	          k: 100,
	          ix: 7
	        },
	        sk: {
	          a: 0,
	          k: 0,
	          ix: 4
	        },
	        sa: {
	          a: 0,
	          k: 0,
	          ix: 5
	        },
	        nm: "Transform"
	      }],
	      nm: "Group 3",
	      np: 2,
	      cix: 2,
	      bm: 0,
	      ix: 1,
	      mn: "ADBE Vector Group",
	      hd: false
	    }, {
	      ty: "tr",
	      p: {
	        a: 0,
	        k: [10.629, 12.321],
	        ix: 2
	      },
	      a: {
	        a: 0,
	        k: [10.629, 12.321],
	        ix: 1
	      },
	      s: {
	        a: 0,
	        k: [100, 100],
	        ix: 3
	      },
	      r: {
	        a: 0,
	        k: 0,
	        ix: 6
	      },
	      o: {
	        a: 0,
	        k: 100,
	        ix: 7
	      },
	      sk: {
	        a: 0,
	        k: 0,
	        ix: 4
	      },
	      sa: {
	        a: 0,
	        k: 0,
	        ix: 5
	      },
	      nm: "Transform"
	    }],
	    nm: "Group 3",
	    np: 1,
	    cix: 2,
	    bm: 0,
	    ix: 1,
	    mn: "ADBE Vector Group",
	    hd: false
	  }],
	  ip: 0,
	  op: 45,
	  st: 0,
	  bm: 0
	}, {
	  ddd: 0,
	  ind: 4,
	  ty: 4,
	  nm: "Group 4 :M",
	  sr: 1,
	  ks: {
	    o: {
	      a: 0,
	      k: 100,
	      ix: 11
	    },
	    r: {
	      a: 0,
	      k: 0,
	      ix: 10
	    },
	    p: {
	      a: 1,
	      k: [{
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: 4,
	        s: [19.573, 6.265, 0],
	        to: [0.667, -0.25, 0],
	        ti: [0.708, 0.583, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.784,
	          y: 0
	        },
	        t: 18,
	        s: [23.573, 4.765, 0],
	        to: [-0.413, -0.34, 0],
	        ti: [2.093, 0.432, 0]
	      }, {
	        i: {
	          x: 0.139,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: 31,
	        s: [17.796, 1.731, 0],
	        to: [-1.495, -0.309, 0],
	        ti: [-0.296, -0.756, 0]
	      }, {
	        t: 44,
	        s: [19.573, 6.265, 0]
	      }],
	      ix: 2,
	      l: 2
	    },
	    a: {
	      a: 0,
	      k: [16.823, 6.515, 0],
	      ix: 1,
	      l: 2
	    },
	    s: {
	      a: 0,
	      k: [100, 100, 100],
	      ix: 6,
	      l: 2
	    }
	  },
	  ao: 0,
	  shapes: [{
	    ty: "gr",
	    it: [{
	      ty: "gr",
	      it: [{
	        ind: 0,
	        ty: "sh",
	        ix: 1,
	        ks: {
	          a: 0,
	          k: {
	            i: [[-0.013, 0.304], [-0.196, 0.087], [-1.038, -0.245], [-0.374, -0.307], [0.005, -0.12], [0, 0], [0.333, 0.193], [0.349, 0.082], [0.905, -0.305]],
	            o: [[0.007, -0.185], [1.081, -0.487], [0.536, 0.127], [0.094, 0.078], [0, 0], [-0.016, 0.368], [-0.281, -0.162], [-0.853, -0.201], [-0.334, 0.112]],
	            v: [[-2.557, 0.16], [-2.219, -0.284], [1.055, -0.7], [2.421, -0.036], [2.557, 0.277], [2.555, 0.313], [1.723, 0.744], [0.779, 0.374], [-1.911, 0.56]],
	            c: true
	          },
	          ix: 2
	        },
	        nm: "Path 1",
	        mn: "ADBE Vector Shape - Group",
	        hd: false
	      }, {
	        ty: "fl",
	        c: {
	          a: 0,
	          k: [0.47058826685, 0.243137270212, 0.066666670144, 1],
	          ix: 4
	        },
	        o: {
	          a: 0,
	          k: 100,
	          ix: 5
	        },
	        r: 1,
	        bm: 0,
	        nm: "Fill 1",
	        mn: "ADBE Vector Graphic - Fill",
	        hd: false
	      }, {
	        ty: "tr",
	        p: {
	          a: 0,
	          k: [10.494, 6.514],
	          ix: 2
	        },
	        a: {
	          a: 0,
	          k: [0, 0],
	          ix: 1
	        },
	        s: {
	          a: 0,
	          k: [100, 100],
	          ix: 3
	        },
	        r: {
	          a: 0,
	          k: 0,
	          ix: 6
	        },
	        o: {
	          a: 0,
	          k: 100,
	          ix: 7
	        },
	        sk: {
	          a: 0,
	          k: 0,
	          ix: 4
	        },
	        sa: {
	          a: 0,
	          k: 0,
	          ix: 5
	        },
	        nm: "Transform"
	      }],
	      nm: "Group 4",
	      np: 2,
	      cix: 2,
	      bm: 0,
	      ix: 1,
	      mn: "ADBE Vector Group",
	      hd: false
	    }, {
	      ty: "tr",
	      p: {
	        a: 0,
	        k: [10.493, 6.515],
	        ix: 2
	      },
	      a: {
	        a: 0,
	        k: [10.493, 6.515],
	        ix: 1
	      },
	      s: {
	        a: 0,
	        k: [100, 100],
	        ix: 3
	      },
	      r: {
	        a: 0,
	        k: 0,
	        ix: 6
	      },
	      o: {
	        a: 0,
	        k: 100,
	        ix: 7
	      },
	      sk: {
	        a: 0,
	        k: 0,
	        ix: 4
	      },
	      sa: {
	        a: 0,
	        k: 0,
	        ix: 5
	      },
	      nm: "Transform"
	    }],
	    nm: "Group 4",
	    np: 1,
	    cix: 2,
	    bm: 0,
	    ix: 1,
	    mn: "ADBE Vector Group",
	    hd: false
	  }, {
	    ty: "gr",
	    it: [{
	      ty: "gr",
	      it: [{
	        ind: 0,
	        ty: "sh",
	        ix: 1,
	        ks: {
	          a: 0,
	          k: {
	            i: [[0.013, 0.304], [0.195, 0.087], [1.038, -0.245], [0.373, -0.307], [-0.005, -0.12], [0, 0], [-0.332, 0.193], [-0.349, 0.082], [-0.905, -0.305]],
	            o: [[-0.008, -0.185], [-1.082, -0.487], [-0.537, 0.127], [-0.095, 0.078], [0, 0], [0.015, 0.368], [0.281, -0.162], [0.853, -0.201], [0.334, 0.112]],
	            v: [[2.557, 0.16], [2.219, -0.284], [-1.055, -0.7], [-2.421, -0.036], [-2.557, 0.277], [-2.555, 0.313], [-1.724, 0.744], [-0.779, 0.374], [1.911, 0.56]],
	            c: true
	          },
	          ix: 2
	        },
	        nm: "Path 1",
	        mn: "ADBE Vector Shape - Group",
	        hd: false
	      }, {
	        ty: "fl",
	        c: {
	          a: 0,
	          k: [0.47058826685, 0.243137270212, 0.066666670144, 1],
	          ix: 4
	        },
	        o: {
	          a: 0,
	          k: 100,
	          ix: 5
	        },
	        r: 1,
	        bm: 0,
	        nm: "Fill 1",
	        mn: "ADBE Vector Graphic - Fill",
	        hd: false
	      }, {
	        ty: "tr",
	        p: {
	          a: 0,
	          k: [23.152, 6.514],
	          ix: 2
	        },
	        a: {
	          a: 0,
	          k: [0, 0],
	          ix: 1
	        },
	        s: {
	          a: 0,
	          k: [100, 100],
	          ix: 3
	        },
	        r: {
	          a: 0,
	          k: 0,
	          ix: 6
	        },
	        o: {
	          a: 0,
	          k: 100,
	          ix: 7
	        },
	        sk: {
	          a: 0,
	          k: 0,
	          ix: 4
	        },
	        sa: {
	          a: 0,
	          k: 0,
	          ix: 5
	        },
	        nm: "Transform"
	      }],
	      nm: "Group 5",
	      np: 2,
	      cix: 2,
	      bm: 0,
	      ix: 1,
	      mn: "ADBE Vector Group",
	      hd: false
	    }, {
	      ty: "tr",
	      p: {
	        a: 0,
	        k: [23.152, 6.515],
	        ix: 2
	      },
	      a: {
	        a: 0,
	        k: [23.152, 6.515],
	        ix: 1
	      },
	      s: {
	        a: 0,
	        k: [100, 100],
	        ix: 3
	      },
	      r: {
	        a: 0,
	        k: 0,
	        ix: 6
	      },
	      o: {
	        a: 0,
	        k: 100,
	        ix: 7
	      },
	      sk: {
	        a: 0,
	        k: 0,
	        ix: 4
	      },
	      sa: {
	        a: 0,
	        k: 0,
	        ix: 5
	      },
	      nm: "Transform"
	    }],
	    nm: "Group 5",
	    np: 1,
	    cix: 2,
	    bm: 0,
	    ix: 2,
	    mn: "ADBE Vector Group",
	    hd: false
	  }],
	  ip: 0,
	  op: 45,
	  st: 0,
	  bm: 0
	}, {
	  ddd: 0,
	  ind: 5,
	  ty: 4,
	  nm: "Group 6",
	  sr: 1,
	  ks: {
	    o: {
	      a: 0,
	      k: 100,
	      ix: 11
	    },
	    r: {
	      a: 0,
	      k: 0,
	      ix: 10
	    },
	    p: {
	      a: 0,
	      k: [20, 17, 0],
	      ix: 2,
	      l: 2
	    },
	    a: {
	      a: 0,
	      k: [17.25, 17.25, 0],
	      ix: 1,
	      l: 2
	    },
	    s: {
	      a: 0,
	      k: [100, 100, 100],
	      ix: 6,
	      l: 2
	    }
	  },
	  ao: 0,
	  shapes: [{
	    ty: "gr",
	    it: [{
	      ind: 0,
	      ty: "sh",
	      ix: 1,
	      ks: {
	        a: 0,
	        k: {
	          i: [[-9.389, 0], [0, -9.389], [9.389, 0], [0, 9.389]],
	          o: [[9.389, 0], [0, 9.389], [-9.389, 0], [0, -9.389]],
	          v: [[0, -17], [17, 0], [0, 17], [-17, 0]],
	          c: true
	        },
	        ix: 2
	      },
	      nm: "Path 1",
	      mn: "ADBE Vector Shape - Group",
	      hd: false
	    }, {
	      ty: "fl",
	      c: {
	        a: 0,
	        k: [0.964705942191, 0.878431432387, 0.443137284821, 1],
	        ix: 4
	      },
	      o: {
	        a: 0,
	        k: 100,
	        ix: 5
	      },
	      r: 1,
	      bm: 0,
	      nm: "Fill 1",
	      mn: "ADBE Vector Graphic - Fill",
	      hd: false
	    }, {
	      ty: "tr",
	      p: {
	        a: 0,
	        k: [17.25, 17.25],
	        ix: 2
	      },
	      a: {
	        a: 0,
	        k: [0, 0],
	        ix: 1
	      },
	      s: {
	        a: 0,
	        k: [100, 100],
	        ix: 3
	      },
	      r: {
	        a: 0,
	        k: 0,
	        ix: 6
	      },
	      o: {
	        a: 0,
	        k: 100,
	        ix: 7
	      },
	      sk: {
	        a: 0,
	        k: 0,
	        ix: 4
	      },
	      sa: {
	        a: 0,
	        k: 0,
	        ix: 5
	      },
	      nm: "Transform"
	    }],
	    nm: "Group 6",
	    np: 2,
	    cix: 2,
	    bm: 0,
	    ix: 1,
	    mn: "ADBE Vector Group",
	    hd: false
	  }],
	  ip: 0,
	  op: 45,
	  st: 0,
	  bm: 0
	}];
	var markers$2 = [];
	var wonderAnimatedEmojiData = {
	  v: v$2,
	  fr: fr$2,
	  ip: ip$2,
	  op: op$2,
	  w: w$2,
	  h: h$2,
	  nm: nm$2,
	  ddd: ddd$2,
	  assets: assets$2,
	  layers: layers$2,
	  markers: markers$2
	};

	var v$3 = "5.9.1";
	var fr$3 = 25;
	var ip$3 = 0;
	var op$3 = 70;
	var w$3 = 40;
	var h$3 = 40;
	var nm$3 = "em_04";
	var ddd$3 = 0;
	var assets$3 = [];
	var layers$3 = [{
	  ddd: 0,
	  ind: 1,
	  ty: 4,
	  nm: "Group 13",
	  sr: 1,
	  ks: {
	    o: {
	      a: 1,
	      k: [{
	        i: {
	          x: [0.833],
	          y: [0.833]
	        },
	        o: {
	          x: [0.167],
	          y: [0.167]
	        },
	        t: -11,
	        s: [100]
	      }, {
	        t: -3,
	        s: [0]
	      }],
	      ix: 11
	    },
	    r: {
	      a: 0,
	      k: 0,
	      ix: 10
	    },
	    p: {
	      a: 0,
	      k: [11.503, 22.765, 0],
	      ix: 2,
	      l: 2
	    },
	    a: {
	      a: 0,
	      k: [27.065, 17.327, 0],
	      ix: 1,
	      l: 2
	    },
	    s: {
	      a: 1,
	      k: [{
	        i: {
	          x: [0.667, 0.667, 0.667],
	          y: [1, 1, 1]
	        },
	        o: {
	          x: [0.333, 0.333, 0.333],
	          y: [0, 0, 0]
	        },
	        t: -25,
	        s: [0, 0, 100]
	      }, {
	        t: -4,
	        s: [-100, 100, 100]
	      }],
	      ix: 6,
	      l: 2
	    }
	  },
	  ao: 0,
	  shapes: [{
	    ty: "gr",
	    it: [{
	      ind: 0,
	      ty: "sh",
	      ix: 1,
	      ks: {
	        a: 0,
	        k: {
	          i: [[-1.141, 0.201], [-0.363, -2.054], [1.141, -0.201], [0.363, 2.055]],
	          o: [[1.141, -0.201], [0.362, 2.055], [-1.142, 0.201], [-0.362, -2.054]],
	          v: [[-0.656, -3.72], [2.067, -0.365], [0.656, 3.72], [-2.067, 0.364]],
	          c: true
	        },
	        ix: 2
	      },
	      nm: "Path 1",
	      mn: "ADBE Vector Shape - Group",
	      hd: false
	    }, {
	      ty: "fl",
	      c: {
	        a: 0,
	        k: [0.160784313725, 0.709803921569, 0.886274569642, 1],
	        ix: 4
	      },
	      o: {
	        a: 0,
	        k: 100,
	        ix: 5
	      },
	      r: 1,
	      bm: 0,
	      nm: "Fill 1",
	      mn: "ADBE Vector Graphic - Fill",
	      hd: false
	    }, {
	      ty: "tr",
	      p: {
	        a: 0,
	        k: [29.234, 21.065],
	        ix: 2
	      },
	      a: {
	        a: 0,
	        k: [0, 0],
	        ix: 1
	      },
	      s: {
	        a: 0,
	        k: [100, 100],
	        ix: 3
	      },
	      r: {
	        a: 0,
	        k: 0,
	        ix: 6
	      },
	      o: {
	        a: 0,
	        k: 100,
	        ix: 7
	      },
	      sk: {
	        a: 0,
	        k: 0,
	        ix: 4
	      },
	      sa: {
	        a: 0,
	        k: 0,
	        ix: 5
	      },
	      nm: "Transform"
	    }],
	    nm: "Group 1",
	    np: 2,
	    cix: 2,
	    bm: 0,
	    ix: 1,
	    mn: "ADBE Vector Group",
	    hd: false
	  }],
	  ip: 0,
	  op: 29,
	  st: -41,
	  bm: 0
	}, {
	  ddd: 0,
	  ind: 2,
	  ty: 4,
	  nm: "Group 12",
	  sr: 1,
	  ks: {
	    o: {
	      a: 1,
	      k: [{
	        i: {
	          x: [0.833],
	          y: [0.833]
	        },
	        o: {
	          x: [0.167],
	          y: [0.167]
	        },
	        t: 0,
	        s: [100]
	      }, {
	        t: 8,
	        s: [0]
	      }],
	      ix: 11
	    },
	    r: {
	      a: 0,
	      k: 0,
	      ix: 10
	    },
	    p: {
	      a: 0,
	      k: [27.565, 18.577, 0],
	      ix: 2,
	      l: 2
	    },
	    a: {
	      a: 0,
	      k: [27.065, 17.327, 0],
	      ix: 1,
	      l: 2
	    },
	    s: {
	      a: 0,
	      k: [100, 100, 100],
	      ix: 6,
	      l: 2
	    }
	  },
	  ao: 0,
	  shapes: [{
	    ty: "gr",
	    it: [{
	      ind: 0,
	      ty: "sh",
	      ix: 1,
	      ks: {
	        a: 0,
	        k: {
	          i: [[-1.141, 0.201], [-0.363, -2.054], [1.141, -0.201], [0.363, 2.055]],
	          o: [[1.141, -0.201], [0.362, 2.055], [-1.142, 0.201], [-0.362, -2.054]],
	          v: [[-0.656, -3.72], [2.067, -0.365], [0.656, 3.72], [-2.067, 0.364]],
	          c: true
	        },
	        ix: 2
	      },
	      nm: "Path 1",
	      mn: "ADBE Vector Shape - Group",
	      hd: false
	    }, {
	      ty: "fl",
	      c: {
	        a: 0,
	        k: [0.160784313725, 0.709803921569, 0.886274569642, 1],
	        ix: 4
	      },
	      o: {
	        a: 0,
	        k: 100,
	        ix: 5
	      },
	      r: 1,
	      bm: 0,
	      nm: "Fill 1",
	      mn: "ADBE Vector Graphic - Fill",
	      hd: false
	    }, {
	      ty: "tr",
	      p: {
	        a: 0,
	        k: [29.234, 21.065],
	        ix: 2
	      },
	      a: {
	        a: 0,
	        k: [0, 0],
	        ix: 1
	      },
	      s: {
	        a: 0,
	        k: [100, 100],
	        ix: 3
	      },
	      r: {
	        a: 0,
	        k: 0,
	        ix: 6
	      },
	      o: {
	        a: 0,
	        k: 100,
	        ix: 7
	      },
	      sk: {
	        a: 0,
	        k: 0,
	        ix: 4
	      },
	      sa: {
	        a: 0,
	        k: 0,
	        ix: 5
	      },
	      nm: "Transform"
	    }],
	    nm: "Group 1",
	    np: 2,
	    cix: 2,
	    bm: 0,
	    ix: 1,
	    mn: "ADBE Vector Group",
	    hd: false
	  }],
	  ip: -14,
	  op: 40,
	  st: -14,
	  bm: 0
	}, {
	  ddd: 0,
	  ind: 3,
	  ty: 4,
	  nm: "Group 5 :M 2",
	  sr: 1,
	  ks: {
	    o: {
	      a: 0,
	      k: 100,
	      ix: 11
	    },
	    r: {
	      a: 0,
	      k: 0,
	      ix: 10
	    },
	    p: {
	      a: 1,
	      k: [{
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: -11,
	        s: [20.025, 17.177, 0],
	        to: [0, -1.042, 0],
	        ti: [0, 1.042, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.833
	        },
	        o: {
	          x: 0.333,
	          y: 0.333
	        },
	        t: -4,
	        s: [20.025, 10.927, 0],
	        to: [0, 0, 0],
	        ti: [0, 0, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: 33,
	        s: [20.025, 10.927, 0],
	        to: [0, 1.042, 0],
	        ti: [0, -1.042, 0]
	      }, {
	        t: 41,
	        s: [20.025, 17.177, 0]
	      }],
	      ix: 2,
	      l: 2
	    },
	    a: {
	      a: 0,
	      k: [19.025, 12.927, 0],
	      ix: 1,
	      l: 2
	    },
	    s: {
	      a: 0,
	      k: [100, 100, 100],
	      ix: 6,
	      l: 2
	    }
	  },
	  ao: 0,
	  shapes: [{
	    ty: "gr",
	    it: [{
	      ty: "gr",
	      it: [{
	        ind: 0,
	        ty: "sh",
	        ix: 1,
	        ks: {
	          a: 0,
	          k: {
	            i: [[0.082, 0.293], [-0.159, 0.144], [-1.063, 0.087], [-0.45, -0.176], [-0.033, -0.116], [0, 0], [0.375, 0.08], [0.357, -0.03], [0.766, -0.57]],
	            o: [[-0.05, -0.178], [0.879, -0.797], [0.549, -0.045], [0.114, 0.045], [0, 0], [0.099, 0.356], [-0.318, -0.068], [-0.874, 0.072], [-0.283, 0.21]],
	            v: [[-2.455, 0.827], [-2.271, 0.3], [0.715, -1.107], [2.219, -0.898], [2.445, -0.642], [2.454, -0.608], [1.797, 0.06], [0.784, 0], [-1.717, 1.008]],
	            c: true
	          },
	          ix: 2
	        },
	        nm: "Path 1",
	        mn: "ADBE Vector Shape - Group",
	        hd: false
	      }, {
	        ty: "fl",
	        c: {
	          a: 0,
	          k: [0.47058826685, 0.243137270212, 0.066666670144, 1],
	          ix: 4
	        },
	        o: {
	          a: 0,
	          k: 100,
	          ix: 5
	        },
	        r: 1,
	        bm: 0,
	        nm: "Fill 1",
	        mn: "ADBE Vector Graphic - Fill",
	        hd: false
	      }, {
	        ty: "tr",
	        p: {
	          a: 0,
	          k: [12.182, 12.927],
	          ix: 2
	        },
	        a: {
	          a: 0,
	          k: [0, 0],
	          ix: 1
	        },
	        s: {
	          a: 0,
	          k: [100, 100],
	          ix: 3
	        },
	        r: {
	          a: 0,
	          k: 0,
	          ix: 6
	        },
	        o: {
	          a: 0,
	          k: 100,
	          ix: 7
	        },
	        sk: {
	          a: 0,
	          k: 0,
	          ix: 4
	        },
	        sa: {
	          a: 0,
	          k: 0,
	          ix: 5
	        },
	        nm: "Transform"
	      }],
	      nm: "Group 5",
	      np: 2,
	      cix: 2,
	      bm: 0,
	      ix: 1,
	      mn: "ADBE Vector Group",
	      hd: false
	    }, {
	      ty: "tr",
	      p: {
	        a: 0,
	        k: [12.182, 12.927],
	        ix: 2
	      },
	      a: {
	        a: 0,
	        k: [12.182, 12.927],
	        ix: 1
	      },
	      s: {
	        a: 0,
	        k: [100, 100],
	        ix: 3
	      },
	      r: {
	        a: 0,
	        k: 0,
	        ix: 6
	      },
	      o: {
	        a: 0,
	        k: 100,
	        ix: 7
	      },
	      sk: {
	        a: 0,
	        k: 0,
	        ix: 4
	      },
	      sa: {
	        a: 0,
	        k: 0,
	        ix: 5
	      },
	      nm: "Transform"
	    }],
	    nm: "Group 5",
	    np: 1,
	    cix: 2,
	    bm: 0,
	    ix: 1,
	    mn: "ADBE Vector Group",
	    hd: false
	  }, {
	    ty: "gr",
	    it: [{
	      ty: "gr",
	      it: [{
	        ind: 0,
	        ty: "sh",
	        ix: 1,
	        ks: {
	          a: 0,
	          k: {
	            i: [[-0.082, 0.293], [0.159, 0.144], [1.063, 0.087], [0.45, -0.176], [0.032, -0.116], [0, 0], [-0.376, 0.08], [-0.357, -0.03], [-0.767, -0.57]],
	            o: [[0.05, -0.178], [-0.878, -0.797], [-0.55, -0.045], [-0.114, 0.045], [0, 0], [-0.099, 0.356], [0.317, -0.068], [0.873, 0.072], [0.282, 0.21]],
	            v: [[2.455, 0.827], [2.271, 0.3], [-0.714, -1.107], [-2.219, -0.898], [-2.444, -0.642], [-2.454, -0.608], [-1.796, 0.06], [-0.783, 0], [1.718, 1.008]],
	            c: true
	          },
	          ix: 2
	        },
	        nm: "Path 1",
	        mn: "ADBE Vector Shape - Group",
	        hd: false
	      }, {
	        ty: "fl",
	        c: {
	          a: 0,
	          k: [0.47058826685, 0.243137270212, 0.066666670144, 1],
	          ix: 4
	        },
	        o: {
	          a: 0,
	          k: 100,
	          ix: 5
	        },
	        r: 1,
	        bm: 0,
	        nm: "Fill 1",
	        mn: "ADBE Vector Graphic - Fill",
	        hd: false
	      }, {
	        ty: "tr",
	        p: {
	          a: 0,
	          k: [25.868, 12.927],
	          ix: 2
	        },
	        a: {
	          a: 0,
	          k: [0, 0],
	          ix: 1
	        },
	        s: {
	          a: 0,
	          k: [100, 100],
	          ix: 3
	        },
	        r: {
	          a: 0,
	          k: 0,
	          ix: 6
	        },
	        o: {
	          a: 0,
	          k: 100,
	          ix: 7
	        },
	        sk: {
	          a: 0,
	          k: 0,
	          ix: 4
	        },
	        sa: {
	          a: 0,
	          k: 0,
	          ix: 5
	        },
	        nm: "Transform"
	      }],
	      nm: "Group 6",
	      np: 2,
	      cix: 2,
	      bm: 0,
	      ix: 1,
	      mn: "ADBE Vector Group",
	      hd: false
	    }, {
	      ty: "tr",
	      p: {
	        a: 0,
	        k: [25.868, 12.927],
	        ix: 2
	      },
	      a: {
	        a: 0,
	        k: [25.868, 12.927],
	        ix: 1
	      },
	      s: {
	        a: 0,
	        k: [100, 100],
	        ix: 3
	      },
	      r: {
	        a: 0,
	        k: 0,
	        ix: 6
	      },
	      o: {
	        a: 0,
	        k: 100,
	        ix: 7
	      },
	      sk: {
	        a: 0,
	        k: 0,
	        ix: 4
	      },
	      sa: {
	        a: 0,
	        k: 0,
	        ix: 5
	      },
	      nm: "Transform"
	    }],
	    nm: "Group 6",
	    np: 1,
	    cix: 2,
	    bm: 0,
	    ix: 2,
	    mn: "ADBE Vector Group",
	    hd: false
	  }],
	  ip: 0,
	  op: 45,
	  st: -25,
	  bm: 0
	}, {
	  ddd: 0,
	  ind: 4,
	  ty: 4,
	  nm: "Group 11",
	  sr: 1,
	  ks: {
	    o: {
	      a: 0,
	      k: 100,
	      ix: 11
	    },
	    r: {
	      a: 0,
	      k: 0,
	      ix: 10
	    },
	    p: {
	      a: 1,
	      k: [{
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: -10,
	        s: [20, 29.883, 0],
	        to: [0, -1.042, 0],
	        ti: [0, 1.042, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.833
	        },
	        o: {
	          x: 0.333,
	          y: 0.333
	        },
	        t: -3,
	        s: [20, 23.633, 0],
	        to: [0, 0, 0],
	        ti: [0, 0, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: 33,
	        s: [20, 23.633, 0],
	        to: [0, 1.042, 0],
	        ti: [0, -1.042, 0]
	      }, {
	        t: 41,
	        s: [20, 29.883, 0]
	      }],
	      ix: 2,
	      l: 2
	    },
	    a: {
	      a: 0,
	      k: [19, 25.633, 0],
	      ix: 1,
	      l: 2
	    },
	    s: {
	      a: 0,
	      k: [100, 100, 100],
	      ix: 6,
	      l: 2
	    }
	  },
	  ao: 0,
	  shapes: [{
	    ty: "gr",
	    it: [{
	      ind: 0,
	      ty: "sh",
	      ix: 1,
	      ks: {
	        a: 0,
	        k: {
	          i: [[0, 0], [-0.239, 0.195], [-2.538, -0.001], [-1.954, -1.593], [0, 0.307], [0, 0], [0.077, 0.069], [2.747, 0.001], [2.042, -1.839], [0, -0.105]],
	          o: [[0, 0.308], [1.955, -1.592], [2.538, 0], [0.239, 0.195], [0, 0], [0, -0.105], [-2.042, -1.84], [-2.747, 0], [-0.078, 0.07], [0, 0]],
	          v: [[-7.445, 1.859], [-6.848, 2.142], [0.001, -0.38], [6.848, 2.145], [7.445, 1.863], [7.445, 0.869], [7.323, 0.593], [0.001, -2.34], [-7.322, 0.589], [-7.444, 0.865]],
	          c: true
	        },
	        ix: 2
	      },
	      nm: "Path 1",
	      mn: "ADBE Vector Shape - Group",
	      hd: false
	    }, {
	      ty: "fl",
	      c: {
	        a: 0,
	        k: [0.470588265213, 0.243137269862, 0.066666666667, 1],
	        ix: 4
	      },
	      o: {
	        a: 0,
	        k: 100,
	        ix: 5
	      },
	      r: 1,
	      bm: 0,
	      nm: "Fill 1",
	      mn: "ADBE Vector Graphic - Fill",
	      hd: false
	    }, {
	      ty: "tr",
	      p: {
	        a: 0,
	        k: [19, 25.689],
	        ix: 2
	      },
	      a: {
	        a: 0,
	        k: [0, 0],
	        ix: 1
	      },
	      s: {
	        a: 0,
	        k: [100, 100],
	        ix: 3
	      },
	      r: {
	        a: 0,
	        k: 0,
	        ix: 6
	      },
	      o: {
	        a: 0,
	        k: 100,
	        ix: 7
	      },
	      sk: {
	        a: 0,
	        k: 0,
	        ix: 4
	      },
	      sa: {
	        a: 0,
	        k: 0,
	        ix: 5
	      },
	      nm: "Transform"
	    }],
	    nm: "Group 2",
	    np: 2,
	    cix: 2,
	    bm: 0,
	    ix: 1,
	    mn: "ADBE Vector Group",
	    hd: false
	  }],
	  ip: 0,
	  op: 45,
	  st: -25,
	  bm: 0
	}, {
	  ddd: 0,
	  ind: 5,
	  ty: 4,
	  nm: "Shape Layer 4",
	  parent: 7,
	  sr: 1,
	  ks: {
	    o: {
	      a: 0,
	      k: 100,
	      ix: 11
	    },
	    r: {
	      a: 0,
	      k: 0,
	      ix: 10
	    },
	    p: {
	      a: 1,
	      k: [{
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: -25,
	        s: [19, 23, 0],
	        to: [0, -0.417, 0],
	        ti: [0, 0, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: -23,
	        s: [19, 20.5, 0],
	        to: [0, 0, 0],
	        ti: [0, -0.417, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 0.667
	        },
	        o: {
	          x: 0.333,
	          y: 0.333
	        },
	        t: -20,
	        s: [19, 23, 0],
	        to: [0, 0, 0],
	        ti: [0, 0, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: -5,
	        s: [19, 23, 0],
	        to: [0, -0.417, 0],
	        ti: [0, 0, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: -3,
	        s: [19, 20.5, 0],
	        to: [0, 0, 0],
	        ti: [0, -0.417, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 0.667
	        },
	        o: {
	          x: 0.333,
	          y: 0.333
	        },
	        t: 0,
	        s: [19, 23, 0],
	        to: [0, 0, 0],
	        ti: [0, 0, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: 18,
	        s: [19, 23, 0],
	        to: [0, -0.417, 0],
	        ti: [0, 0, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: 20,
	        s: [19, 20.5, 0],
	        to: [0, 0, 0],
	        ti: [0, -0.417, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 0.667
	        },
	        o: {
	          x: 0.333,
	          y: 0.333
	        },
	        t: 23,
	        s: [19, 23, 0],
	        to: [0, 0, 0],
	        ti: [0, 0, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: 39,
	        s: [19, 23, 0],
	        to: [0, -0.417, 0],
	        ti: [0, 0, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: 41,
	        s: [19, 20.5, 0],
	        to: [0, 0, 0],
	        ti: [0, -0.417, 0]
	      }, {
	        t: 44,
	        s: [19, 23, 0]
	      }],
	      ix: 2,
	      l: 2
	    },
	    a: {
	      a: 0,
	      k: [0, 0, 0],
	      ix: 1,
	      l: 2
	    },
	    s: {
	      a: 0,
	      k: [80, 100, 100],
	      ix: 6,
	      l: 2
	    }
	  },
	  ao: 0,
	  shapes: [{
	    ty: "rc",
	    d: 1,
	    s: {
	      a: 0,
	      k: [26, 4],
	      ix: 2
	    },
	    p: {
	      a: 0,
	      k: [0, 0],
	      ix: 3
	    },
	    r: {
	      a: 0,
	      k: 0,
	      ix: 4
	    },
	    nm: "Rectangle Path 1",
	    mn: "ADBE Vector Shape - Rect",
	    hd: false
	  }, {
	    ty: "fl",
	    c: {
	      a: 0,
	      k: [0.964705942191, 0.878431432387, 0.443137284821, 1],
	      ix: 4
	    },
	    o: {
	      a: 0,
	      k: 100,
	      ix: 5
	    },
	    r: 1,
	    bm: 0,
	    nm: "Fill 1",
	    mn: "ADBE Vector Graphic - Fill",
	    hd: false
	  }],
	  ip: 0,
	  op: 45,
	  st: -25,
	  bm: 0
	}, {
	  ddd: 0,
	  ind: 6,
	  ty: 4,
	  nm: "Shape Layer 3",
	  parent: 7,
	  sr: 1,
	  ks: {
	    o: {
	      a: 0,
	      k: 100,
	      ix: 11
	    },
	    r: {
	      a: 0,
	      k: 0,
	      ix: 10
	    },
	    p: {
	      a: 1,
	      k: [{
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: -25,
	        s: [19, 12, 0],
	        to: [0, 0.417, 0],
	        ti: [0, 0, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: -23,
	        s: [19, 14.5, 0],
	        to: [0, 0, 0],
	        ti: [0, 0.417, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 0.667
	        },
	        o: {
	          x: 0.333,
	          y: 0.333
	        },
	        t: -20,
	        s: [19, 12, 0],
	        to: [0, 0, 0],
	        ti: [0, 0, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: -5,
	        s: [19, 12, 0],
	        to: [0, 0.417, 0],
	        ti: [0, 0, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: -3,
	        s: [19, 14.5, 0],
	        to: [0, 0, 0],
	        ti: [0, 0.417, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 0.667
	        },
	        o: {
	          x: 0.333,
	          y: 0.333
	        },
	        t: 0,
	        s: [19, 12, 0],
	        to: [0, 0, 0],
	        ti: [0, 0, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: 18,
	        s: [19, 12, 0],
	        to: [0, 0.417, 0],
	        ti: [0, 0, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: 20,
	        s: [19, 14.5, 0],
	        to: [0, 0, 0],
	        ti: [0, 0.417, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 0.667
	        },
	        o: {
	          x: 0.333,
	          y: 0.333
	        },
	        t: 23,
	        s: [19, 12, 0],
	        to: [0, 0, 0],
	        ti: [0, 0, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: 39,
	        s: [19, 12, 0],
	        to: [0, 0.417, 0],
	        ti: [0, 0, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: 41,
	        s: [19, 14.5, 0],
	        to: [0, 0, 0],
	        ti: [0, 0.417, 0]
	      }, {
	        t: 44,
	        s: [19, 12, 0]
	      }],
	      ix: 2,
	      l: 2
	    },
	    a: {
	      a: 0,
	      k: [0, 0, 0],
	      ix: 1,
	      l: 2
	    },
	    s: {
	      a: 0,
	      k: [80, 100, 100],
	      ix: 6,
	      l: 2
	    }
	  },
	  ao: 0,
	  shapes: [{
	    ty: "rc",
	    d: 1,
	    s: {
	      a: 0,
	      k: [26, 4],
	      ix: 2
	    },
	    p: {
	      a: 0,
	      k: [0, 0],
	      ix: 3
	    },
	    r: {
	      a: 0,
	      k: 0,
	      ix: 4
	    },
	    nm: "Rectangle Path 1",
	    mn: "ADBE Vector Shape - Rect",
	    hd: false
	  }, {
	    ty: "fl",
	    c: {
	      a: 0,
	      k: [0.964705942191, 0.878431432387, 0.443137284821, 1],
	      ix: 4
	    },
	    o: {
	      a: 0,
	      k: 100,
	      ix: 5
	    },
	    r: 1,
	    bm: 0,
	    nm: "Fill 1",
	    mn: "ADBE Vector Graphic - Fill",
	    hd: false
	  }],
	  ip: 0,
	  op: 45,
	  st: -25,
	  bm: 0
	}, {
	  ddd: 0,
	  ind: 7,
	  ty: 4,
	  nm: "Group 3 :M 2",
	  sr: 1,
	  ks: {
	    o: {
	      a: 0,
	      k: 100,
	      ix: 11
	    },
	    r: {
	      a: 0,
	      k: 0,
	      ix: 10
	    },
	    p: {
	      a: 1,
	      k: [{
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: -12,
	        s: [20.065, 21.464, 0],
	        to: [0, -1.042, 0],
	        ti: [0, 1.042, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.833
	        },
	        o: {
	          x: 0.333,
	          y: 0.333
	        },
	        t: -5,
	        s: [20.065, 15.214, 0],
	        to: [0, 0, 0],
	        ti: [0, 0, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: 33,
	        s: [20.065, 15.214, 0],
	        to: [0, 1.042, 0],
	        ti: [0, -1.042, 0]
	      }, {
	        t: 41,
	        s: [20.065, 21.464, 0]
	      }],
	      ix: 2,
	      l: 2
	    },
	    a: {
	      a: 0,
	      k: [19.065, 17.214, 0],
	      ix: 1,
	      l: 2
	    },
	    s: {
	      a: 0,
	      k: [100, 100, 100],
	      ix: 6,
	      l: 2
	    }
	  },
	  ao: 0,
	  shapes: [{
	    ty: "gr",
	    it: [{
	      ty: "gr",
	      it: [{
	        ind: 0,
	        ty: "sh",
	        ix: 1,
	        ks: {
	          a: 0,
	          k: {
	            i: [[-0.915, 0], [0, -1.34], [0.915, 0], [0, 1.341]],
	            o: [[0.915, 0], [0, 1.341], [-0.915, 0], [0, -1.34]],
	            v: [[0, -2.427], [1.657, 0], [0, 2.427], [-1.657, 0]],
	            c: true
	          },
	          ix: 2
	        },
	        nm: "Path 1",
	        mn: "ADBE Vector Shape - Group",
	        hd: false
	      }, {
	        ty: "fl",
	        c: {
	          a: 0,
	          k: [0.47058826685, 0.243137270212, 0.066666670144, 1],
	          ix: 4
	        },
	        o: {
	          a: 0,
	          k: 100,
	          ix: 5
	        },
	        r: 1,
	        bm: 0,
	        nm: "Fill 1",
	        mn: "ADBE Vector Graphic - Fill",
	        hd: false
	      }, {
	        ty: "tr",
	        p: {
	          a: 0,
	          k: [25.357, 17.214],
	          ix: 2
	        },
	        a: {
	          a: 0,
	          k: [0, 0],
	          ix: 1
	        },
	        s: {
	          a: 0,
	          k: [100, 100],
	          ix: 3
	        },
	        r: {
	          a: 0,
	          k: 0,
	          ix: 6
	        },
	        o: {
	          a: 0,
	          k: 100,
	          ix: 7
	        },
	        sk: {
	          a: 0,
	          k: 0,
	          ix: 4
	        },
	        sa: {
	          a: 0,
	          k: 0,
	          ix: 5
	        },
	        nm: "Transform"
	      }],
	      nm: "Group 3",
	      np: 2,
	      cix: 2,
	      bm: 0,
	      ix: 1,
	      mn: "ADBE Vector Group",
	      hd: false
	    }, {
	      ty: "tr",
	      p: {
	        a: 0,
	        k: [25.357, 17.214],
	        ix: 2
	      },
	      a: {
	        a: 0,
	        k: [25.357, 17.214],
	        ix: 1
	      },
	      s: {
	        a: 0,
	        k: [100, 100],
	        ix: 3
	      },
	      r: {
	        a: 0,
	        k: 0,
	        ix: 6
	      },
	      o: {
	        a: 0,
	        k: 100,
	        ix: 7
	      },
	      sk: {
	        a: 0,
	        k: 0,
	        ix: 4
	      },
	      sa: {
	        a: 0,
	        k: 0,
	        ix: 5
	      },
	      nm: "Transform"
	    }],
	    nm: "Group 3",
	    np: 1,
	    cix: 2,
	    bm: 0,
	    ix: 1,
	    mn: "ADBE Vector Group",
	    hd: false
	  }, {
	    ty: "gr",
	    it: [{
	      ty: "gr",
	      it: [{
	        ind: 0,
	        ty: "sh",
	        ix: 1,
	        ks: {
	          a: 0,
	          k: {
	            i: [[-0.915, 0], [0, -1.34], [0.915, 0], [0, 1.341]],
	            o: [[0.915, 0], [0, 1.341], [-0.915, 0], [0, -1.34]],
	            v: [[0, -2.427], [1.657, 0], [0, 2.427], [-1.657, 0]],
	            c: true
	          },
	          ix: 2
	        },
	        nm: "Path 1",
	        mn: "ADBE Vector Shape - Group",
	        hd: false
	      }, {
	        ty: "fl",
	        c: {
	          a: 0,
	          k: [0.47058826685, 0.243137270212, 0.066666670144, 1],
	          ix: 4
	        },
	        o: {
	          a: 0,
	          k: 100,
	          ix: 5
	        },
	        r: 1,
	        bm: 0,
	        nm: "Fill 1",
	        mn: "ADBE Vector Graphic - Fill",
	        hd: false
	      }, {
	        ty: "tr",
	        p: {
	          a: 0,
	          k: [12.774, 17.214],
	          ix: 2
	        },
	        a: {
	          a: 0,
	          k: [0, 0],
	          ix: 1
	        },
	        s: {
	          a: 0,
	          k: [100, 100],
	          ix: 3
	        },
	        r: {
	          a: 0,
	          k: 0,
	          ix: 6
	        },
	        o: {
	          a: 0,
	          k: 100,
	          ix: 7
	        },
	        sk: {
	          a: 0,
	          k: 0,
	          ix: 4
	        },
	        sa: {
	          a: 0,
	          k: 0,
	          ix: 5
	        },
	        nm: "Transform"
	      }],
	      nm: "Group 4",
	      np: 2,
	      cix: 2,
	      bm: 0,
	      ix: 1,
	      mn: "ADBE Vector Group",
	      hd: false
	    }, {
	      ty: "tr",
	      p: {
	        a: 0,
	        k: [12.774, 17.214],
	        ix: 2
	      },
	      a: {
	        a: 0,
	        k: [12.774, 17.214],
	        ix: 1
	      },
	      s: {
	        a: 0,
	        k: [100, 100],
	        ix: 3
	      },
	      r: {
	        a: 0,
	        k: 0,
	        ix: 6
	      },
	      o: {
	        a: 0,
	        k: 100,
	        ix: 7
	      },
	      sk: {
	        a: 0,
	        k: 0,
	        ix: 4
	      },
	      sa: {
	        a: 0,
	        k: 0,
	        ix: 5
	      },
	      nm: "Transform"
	    }],
	    nm: "Group 4",
	    np: 1,
	    cix: 2,
	    bm: 0,
	    ix: 2,
	    mn: "ADBE Vector Group",
	    hd: false
	  }],
	  ip: 0,
	  op: 45,
	  st: -25,
	  bm: 0
	}, {
	  ddd: 0,
	  ind: 8,
	  ty: 4,
	  nm: "Group 10",
	  sr: 1,
	  ks: {
	    o: {
	      a: 0,
	      k: 100,
	      ix: 11
	    },
	    r: {
	      a: 0,
	      k: 0,
	      ix: 10
	    },
	    p: {
	      a: 0,
	      k: [20, 17, 0],
	      ix: 2,
	      l: 2
	    },
	    a: {
	      a: 0,
	      k: [17.25, 17.25, 0],
	      ix: 1,
	      l: 2
	    },
	    s: {
	      a: 0,
	      k: [100, 100, 100],
	      ix: 6,
	      l: 2
	    }
	  },
	  ao: 0,
	  shapes: [{
	    ty: "gr",
	    it: [{
	      ind: 0,
	      ty: "sh",
	      ix: 1,
	      ks: {
	        a: 0,
	        k: {
	          i: [[-9.389, 0], [0, -9.389], [9.389, 0], [0, 9.389]],
	          o: [[9.389, 0], [0, 9.389], [-9.389, 0], [0, -9.389]],
	          v: [[0, -17], [17, 0], [0, 17], [-17, 0]],
	          c: true
	        },
	        ix: 2
	      },
	      nm: "Path 1",
	      mn: "ADBE Vector Shape - Group",
	      hd: false
	    }, {
	      ty: "fl",
	      c: {
	        a: 0,
	        k: [0.964705942191, 0.878431432387, 0.443137284821, 1],
	        ix: 4
	      },
	      o: {
	        a: 0,
	        k: 100,
	        ix: 5
	      },
	      r: 1,
	      bm: 0,
	      nm: "Fill 1",
	      mn: "ADBE Vector Graphic - Fill",
	      hd: false
	    }, {
	      ty: "tr",
	      p: {
	        a: 0,
	        k: [17.25, 17.25],
	        ix: 2
	      },
	      a: {
	        a: 0,
	        k: [0, 0],
	        ix: 1
	      },
	      s: {
	        a: 0,
	        k: [100, 100],
	        ix: 3
	      },
	      r: {
	        a: 0,
	        k: 0,
	        ix: 6
	      },
	      o: {
	        a: 0,
	        k: 100,
	        ix: 7
	      },
	      sk: {
	        a: 0,
	        k: 0,
	        ix: 4
	      },
	      sa: {
	        a: 0,
	        k: 0,
	        ix: 5
	      },
	      nm: "Transform"
	    }],
	    nm: "Group 6",
	    np: 2,
	    cix: 2,
	    bm: 0,
	    ix: 1,
	    mn: "ADBE Vector Group",
	    hd: false
	  }],
	  ip: 0,
	  op: 45,
	  st: -25,
	  bm: 0
	}, {
	  ddd: 0,
	  ind: 9,
	  ty: 4,
	  nm: "Group 14",
	  sr: 1,
	  ks: {
	    o: {
	      a: 1,
	      k: [{
	        i: {
	          x: [0.833],
	          y: [0.833]
	        },
	        o: {
	          x: [0.167],
	          y: [0.167]
	        },
	        t: 59,
	        s: [100]
	      }, {
	        t: 67,
	        s: [0]
	      }],
	      ix: 11
	    },
	    r: {
	      a: 0,
	      k: 0,
	      ix: 10
	    },
	    p: {
	      a: 0,
	      k: [11.503, 22.765, 0],
	      ix: 2,
	      l: 2
	    },
	    a: {
	      a: 0,
	      k: [27.065, 17.327, 0],
	      ix: 1,
	      l: 2
	    },
	    s: {
	      a: 1,
	      k: [{
	        i: {
	          x: [0.667, 0.667, 0.667],
	          y: [1, 1, 1]
	        },
	        o: {
	          x: [0.333, 0.333, 0.333],
	          y: [0, 0, 0]
	        },
	        t: 45,
	        s: [0, 0, 100]
	      }, {
	        t: 66,
	        s: [-100, 100, 100]
	      }],
	      ix: 6,
	      l: 2
	    }
	  },
	  ao: 0,
	  shapes: [{
	    ty: "gr",
	    it: [{
	      ind: 0,
	      ty: "sh",
	      ix: 1,
	      ks: {
	        a: 0,
	        k: {
	          i: [[-1.141, 0.201], [-0.363, -2.054], [1.141, -0.201], [0.363, 2.055]],
	          o: [[1.141, -0.201], [0.362, 2.055], [-1.142, 0.201], [-0.362, -2.054]],
	          v: [[-0.656, -3.72], [2.067, -0.365], [0.656, 3.72], [-2.067, 0.364]],
	          c: true
	        },
	        ix: 2
	      },
	      nm: "Path 1",
	      mn: "ADBE Vector Shape - Group",
	      hd: false
	    }, {
	      ty: "fl",
	      c: {
	        a: 0,
	        k: [0.160784313725, 0.709803921569, 0.886274569642, 1],
	        ix: 4
	      },
	      o: {
	        a: 0,
	        k: 100,
	        ix: 5
	      },
	      r: 1,
	      bm: 0,
	      nm: "Fill 1",
	      mn: "ADBE Vector Graphic - Fill",
	      hd: false
	    }, {
	      ty: "tr",
	      p: {
	        a: 0,
	        k: [29.234, 21.065],
	        ix: 2
	      },
	      a: {
	        a: 0,
	        k: [0, 0],
	        ix: 1
	      },
	      s: {
	        a: 0,
	        k: [100, 100],
	        ix: 3
	      },
	      r: {
	        a: 0,
	        k: 0,
	        ix: 6
	      },
	      o: {
	        a: 0,
	        k: 100,
	        ix: 7
	      },
	      sk: {
	        a: 0,
	        k: 0,
	        ix: 4
	      },
	      sa: {
	        a: 0,
	        k: 0,
	        ix: 5
	      },
	      nm: "Transform"
	    }],
	    nm: "Group 1",
	    np: 2,
	    cix: 2,
	    bm: 0,
	    ix: 1,
	    mn: "ADBE Vector Group",
	    hd: false
	  }],
	  ip: 45,
	  op: 70,
	  st: 29,
	  bm: 0
	}, {
	  ddd: 0,
	  ind: 10,
	  ty: 4,
	  nm: "Group 1",
	  sr: 1,
	  ks: {
	    o: {
	      a: 0,
	      k: 100,
	      ix: 11
	    },
	    r: {
	      a: 0,
	      k: 0,
	      ix: 10
	    },
	    p: {
	      a: 0,
	      k: [27.565, 18.577, 0],
	      ix: 2,
	      l: 2
	    },
	    a: {
	      a: 0,
	      k: [27.065, 17.327, 0],
	      ix: 1,
	      l: 2
	    },
	    s: {
	      a: 1,
	      k: [{
	        i: {
	          x: [0.667, 0.667, 0.667],
	          y: [1, 1, 1]
	        },
	        o: {
	          x: [0.333, 0.333, 0.333],
	          y: [0, 0, 0]
	        },
	        t: 60,
	        s: [0, 0, 100]
	      }, {
	        t: 70,
	        s: [100, 100, 100]
	      }],
	      ix: 6,
	      l: 2
	    }
	  },
	  ao: 0,
	  shapes: [{
	    ty: "gr",
	    it: [{
	      ind: 0,
	      ty: "sh",
	      ix: 1,
	      ks: {
	        a: 0,
	        k: {
	          i: [[-1.141, 0.201], [-0.363, -2.054], [1.141, -0.201], [0.363, 2.055]],
	          o: [[1.141, -0.201], [0.362, 2.055], [-1.142, 0.201], [-0.362, -2.054]],
	          v: [[-0.656, -3.72], [2.067, -0.365], [0.656, 3.72], [-2.067, 0.364]],
	          c: true
	        },
	        ix: 2
	      },
	      nm: "Path 1",
	      mn: "ADBE Vector Shape - Group",
	      hd: false
	    }, {
	      ty: "fl",
	      c: {
	        a: 0,
	        k: [0.160784313725, 0.709803921569, 0.886274569642, 1],
	        ix: 4
	      },
	      o: {
	        a: 0,
	        k: 100,
	        ix: 5
	      },
	      r: 1,
	      bm: 0,
	      nm: "Fill 1",
	      mn: "ADBE Vector Graphic - Fill",
	      hd: false
	    }, {
	      ty: "tr",
	      p: {
	        a: 0,
	        k: [29.234, 21.065],
	        ix: 2
	      },
	      a: {
	        a: 0,
	        k: [0, 0],
	        ix: 1
	      },
	      s: {
	        a: 0,
	        k: [100, 100],
	        ix: 3
	      },
	      r: {
	        a: 0,
	        k: 0,
	        ix: 6
	      },
	      o: {
	        a: 0,
	        k: 100,
	        ix: 7
	      },
	      sk: {
	        a: 0,
	        k: 0,
	        ix: 4
	      },
	      sa: {
	        a: 0,
	        k: 0,
	        ix: 5
	      },
	      nm: "Transform"
	    }],
	    nm: "Group 1",
	    np: 2,
	    cix: 2,
	    bm: 0,
	    ix: 1,
	    mn: "ADBE Vector Group",
	    hd: false
	  }],
	  ip: 49,
	  op: 70,
	  st: 49,
	  bm: 0
	}, {
	  ddd: 0,
	  ind: 11,
	  ty: 4,
	  nm: "Group 5 :M",
	  sr: 1,
	  ks: {
	    o: {
	      a: 0,
	      k: 100,
	      ix: 11
	    },
	    r: {
	      a: 0,
	      k: 0,
	      ix: 10
	    },
	    p: {
	      a: 1,
	      k: [{
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: 59,
	        s: [20.025, 17.177, 0],
	        to: [0, -1.042, 0],
	        ti: [0, 1.042, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.833
	        },
	        o: {
	          x: 0.333,
	          y: 0.333
	        },
	        t: 66,
	        s: [20.025, 10.927, 0],
	        to: [0, 0, 0],
	        ti: [0, 0, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: 103,
	        s: [20.025, 10.927, 0],
	        to: [0, 1.042, 0],
	        ti: [0, -1.042, 0]
	      }, {
	        t: 111,
	        s: [20.025, 17.177, 0]
	      }],
	      ix: 2,
	      l: 2
	    },
	    a: {
	      a: 0,
	      k: [19.025, 12.927, 0],
	      ix: 1,
	      l: 2
	    },
	    s: {
	      a: 0,
	      k: [100, 100, 100],
	      ix: 6,
	      l: 2
	    }
	  },
	  ao: 0,
	  shapes: [{
	    ty: "gr",
	    it: [{
	      ty: "gr",
	      it: [{
	        ind: 0,
	        ty: "sh",
	        ix: 1,
	        ks: {
	          a: 0,
	          k: {
	            i: [[0.082, 0.293], [-0.159, 0.144], [-1.063, 0.087], [-0.45, -0.176], [-0.033, -0.116], [0, 0], [0.375, 0.08], [0.357, -0.03], [0.766, -0.57]],
	            o: [[-0.05, -0.178], [0.879, -0.797], [0.549, -0.045], [0.114, 0.045], [0, 0], [0.099, 0.356], [-0.318, -0.068], [-0.874, 0.072], [-0.283, 0.21]],
	            v: [[-2.455, 0.827], [-2.271, 0.3], [0.715, -1.107], [2.219, -0.898], [2.445, -0.642], [2.454, -0.608], [1.797, 0.06], [0.784, 0], [-1.717, 1.008]],
	            c: true
	          },
	          ix: 2
	        },
	        nm: "Path 1",
	        mn: "ADBE Vector Shape - Group",
	        hd: false
	      }, {
	        ty: "fl",
	        c: {
	          a: 0,
	          k: [0.47058826685, 0.243137270212, 0.066666670144, 1],
	          ix: 4
	        },
	        o: {
	          a: 0,
	          k: 100,
	          ix: 5
	        },
	        r: 1,
	        bm: 0,
	        nm: "Fill 1",
	        mn: "ADBE Vector Graphic - Fill",
	        hd: false
	      }, {
	        ty: "tr",
	        p: {
	          a: 0,
	          k: [12.182, 12.927],
	          ix: 2
	        },
	        a: {
	          a: 0,
	          k: [0, 0],
	          ix: 1
	        },
	        s: {
	          a: 0,
	          k: [100, 100],
	          ix: 3
	        },
	        r: {
	          a: 0,
	          k: 0,
	          ix: 6
	        },
	        o: {
	          a: 0,
	          k: 100,
	          ix: 7
	        },
	        sk: {
	          a: 0,
	          k: 0,
	          ix: 4
	        },
	        sa: {
	          a: 0,
	          k: 0,
	          ix: 5
	        },
	        nm: "Transform"
	      }],
	      nm: "Group 5",
	      np: 2,
	      cix: 2,
	      bm: 0,
	      ix: 1,
	      mn: "ADBE Vector Group",
	      hd: false
	    }, {
	      ty: "tr",
	      p: {
	        a: 0,
	        k: [12.182, 12.927],
	        ix: 2
	      },
	      a: {
	        a: 0,
	        k: [12.182, 12.927],
	        ix: 1
	      },
	      s: {
	        a: 0,
	        k: [100, 100],
	        ix: 3
	      },
	      r: {
	        a: 0,
	        k: 0,
	        ix: 6
	      },
	      o: {
	        a: 0,
	        k: 100,
	        ix: 7
	      },
	      sk: {
	        a: 0,
	        k: 0,
	        ix: 4
	      },
	      sa: {
	        a: 0,
	        k: 0,
	        ix: 5
	      },
	      nm: "Transform"
	    }],
	    nm: "Group 5",
	    np: 1,
	    cix: 2,
	    bm: 0,
	    ix: 1,
	    mn: "ADBE Vector Group",
	    hd: false
	  }, {
	    ty: "gr",
	    it: [{
	      ty: "gr",
	      it: [{
	        ind: 0,
	        ty: "sh",
	        ix: 1,
	        ks: {
	          a: 0,
	          k: {
	            i: [[-0.082, 0.293], [0.159, 0.144], [1.063, 0.087], [0.45, -0.176], [0.032, -0.116], [0, 0], [-0.376, 0.08], [-0.357, -0.03], [-0.767, -0.57]],
	            o: [[0.05, -0.178], [-0.878, -0.797], [-0.55, -0.045], [-0.114, 0.045], [0, 0], [-0.099, 0.356], [0.317, -0.068], [0.873, 0.072], [0.282, 0.21]],
	            v: [[2.455, 0.827], [2.271, 0.3], [-0.714, -1.107], [-2.219, -0.898], [-2.444, -0.642], [-2.454, -0.608], [-1.796, 0.06], [-0.783, 0], [1.718, 1.008]],
	            c: true
	          },
	          ix: 2
	        },
	        nm: "Path 1",
	        mn: "ADBE Vector Shape - Group",
	        hd: false
	      }, {
	        ty: "fl",
	        c: {
	          a: 0,
	          k: [0.47058826685, 0.243137270212, 0.066666670144, 1],
	          ix: 4
	        },
	        o: {
	          a: 0,
	          k: 100,
	          ix: 5
	        },
	        r: 1,
	        bm: 0,
	        nm: "Fill 1",
	        mn: "ADBE Vector Graphic - Fill",
	        hd: false
	      }, {
	        ty: "tr",
	        p: {
	          a: 0,
	          k: [25.868, 12.927],
	          ix: 2
	        },
	        a: {
	          a: 0,
	          k: [0, 0],
	          ix: 1
	        },
	        s: {
	          a: 0,
	          k: [100, 100],
	          ix: 3
	        },
	        r: {
	          a: 0,
	          k: 0,
	          ix: 6
	        },
	        o: {
	          a: 0,
	          k: 100,
	          ix: 7
	        },
	        sk: {
	          a: 0,
	          k: 0,
	          ix: 4
	        },
	        sa: {
	          a: 0,
	          k: 0,
	          ix: 5
	        },
	        nm: "Transform"
	      }],
	      nm: "Group 6",
	      np: 2,
	      cix: 2,
	      bm: 0,
	      ix: 1,
	      mn: "ADBE Vector Group",
	      hd: false
	    }, {
	      ty: "tr",
	      p: {
	        a: 0,
	        k: [25.868, 12.927],
	        ix: 2
	      },
	      a: {
	        a: 0,
	        k: [25.868, 12.927],
	        ix: 1
	      },
	      s: {
	        a: 0,
	        k: [100, 100],
	        ix: 3
	      },
	      r: {
	        a: 0,
	        k: 0,
	        ix: 6
	      },
	      o: {
	        a: 0,
	        k: 100,
	        ix: 7
	      },
	      sk: {
	        a: 0,
	        k: 0,
	        ix: 4
	      },
	      sa: {
	        a: 0,
	        k: 0,
	        ix: 5
	      },
	      nm: "Transform"
	    }],
	    nm: "Group 6",
	    np: 1,
	    cix: 2,
	    bm: 0,
	    ix: 2,
	    mn: "ADBE Vector Group",
	    hd: false
	  }],
	  ip: 45,
	  op: 70,
	  st: 45,
	  bm: 0
	}, {
	  ddd: 0,
	  ind: 12,
	  ty: 4,
	  nm: "Group 2",
	  sr: 1,
	  ks: {
	    o: {
	      a: 0,
	      k: 100,
	      ix: 11
	    },
	    r: {
	      a: 0,
	      k: 0,
	      ix: 10
	    },
	    p: {
	      a: 1,
	      k: [{
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: 60,
	        s: [20, 29.883, 0],
	        to: [0, -1.042, 0],
	        ti: [0, 1.042, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.833
	        },
	        o: {
	          x: 0.333,
	          y: 0.333
	        },
	        t: 67,
	        s: [20, 23.633, 0],
	        to: [0, 0, 0],
	        ti: [0, 0, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: 103,
	        s: [20, 23.633, 0],
	        to: [0, 1.042, 0],
	        ti: [0, -1.042, 0]
	      }, {
	        t: 111,
	        s: [20, 29.883, 0]
	      }],
	      ix: 2,
	      l: 2
	    },
	    a: {
	      a: 0,
	      k: [19, 25.633, 0],
	      ix: 1,
	      l: 2
	    },
	    s: {
	      a: 0,
	      k: [100, 100, 100],
	      ix: 6,
	      l: 2
	    }
	  },
	  ao: 0,
	  shapes: [{
	    ty: "gr",
	    it: [{
	      ind: 0,
	      ty: "sh",
	      ix: 1,
	      ks: {
	        a: 0,
	        k: {
	          i: [[0, 0], [-0.239, 0.195], [-2.538, -0.001], [-1.954, -1.593], [0, 0.307], [0, 0], [0.077, 0.069], [2.747, 0.001], [2.042, -1.839], [0, -0.105]],
	          o: [[0, 0.308], [1.955, -1.592], [2.538, 0], [0.239, 0.195], [0, 0], [0, -0.105], [-2.042, -1.84], [-2.747, 0], [-0.078, 0.07], [0, 0]],
	          v: [[-7.445, 1.859], [-6.848, 2.142], [0.001, -0.38], [6.848, 2.145], [7.445, 1.863], [7.445, 0.869], [7.323, 0.593], [0.001, -2.34], [-7.322, 0.589], [-7.444, 0.865]],
	          c: true
	        },
	        ix: 2
	      },
	      nm: "Path 1",
	      mn: "ADBE Vector Shape - Group",
	      hd: false
	    }, {
	      ty: "fl",
	      c: {
	        a: 0,
	        k: [0.470588265213, 0.243137269862, 0.066666666667, 1],
	        ix: 4
	      },
	      o: {
	        a: 0,
	        k: 100,
	        ix: 5
	      },
	      r: 1,
	      bm: 0,
	      nm: "Fill 1",
	      mn: "ADBE Vector Graphic - Fill",
	      hd: false
	    }, {
	      ty: "tr",
	      p: {
	        a: 0,
	        k: [19, 25.689],
	        ix: 2
	      },
	      a: {
	        a: 0,
	        k: [0, 0],
	        ix: 1
	      },
	      s: {
	        a: 0,
	        k: [100, 100],
	        ix: 3
	      },
	      r: {
	        a: 0,
	        k: 0,
	        ix: 6
	      },
	      o: {
	        a: 0,
	        k: 100,
	        ix: 7
	      },
	      sk: {
	        a: 0,
	        k: 0,
	        ix: 4
	      },
	      sa: {
	        a: 0,
	        k: 0,
	        ix: 5
	      },
	      nm: "Transform"
	    }],
	    nm: "Group 2",
	    np: 2,
	    cix: 2,
	    bm: 0,
	    ix: 1,
	    mn: "ADBE Vector Group",
	    hd: false
	  }],
	  ip: 45,
	  op: 70,
	  st: 45,
	  bm: 0
	}, {
	  ddd: 0,
	  ind: 13,
	  ty: 4,
	  nm: "Shape Layer 2",
	  parent: 15,
	  sr: 1,
	  ks: {
	    o: {
	      a: 0,
	      k: 100,
	      ix: 11
	    },
	    r: {
	      a: 0,
	      k: 0,
	      ix: 10
	    },
	    p: {
	      a: 1,
	      k: [{
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: 45,
	        s: [19, 23, 0],
	        to: [0, -0.417, 0],
	        ti: [0, 0, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: 47,
	        s: [19, 20.5, 0],
	        to: [0, 0, 0],
	        ti: [0, -0.417, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 0.667
	        },
	        o: {
	          x: 0.333,
	          y: 0.333
	        },
	        t: 50,
	        s: [19, 23, 0],
	        to: [0, 0, 0],
	        ti: [0, 0, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: 65,
	        s: [19, 23, 0],
	        to: [0, -0.417, 0],
	        ti: [0, 0, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: 67,
	        s: [19, 20.5, 0],
	        to: [0, 0, 0],
	        ti: [0, -0.417, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 0.667
	        },
	        o: {
	          x: 0.333,
	          y: 0.333
	        },
	        t: 70,
	        s: [19, 23, 0],
	        to: [0, 0, 0],
	        ti: [0, 0, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: 88,
	        s: [19, 23, 0],
	        to: [0, -0.417, 0],
	        ti: [0, 0, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: 90,
	        s: [19, 20.5, 0],
	        to: [0, 0, 0],
	        ti: [0, -0.417, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 0.667
	        },
	        o: {
	          x: 0.333,
	          y: 0.333
	        },
	        t: 93,
	        s: [19, 23, 0],
	        to: [0, 0, 0],
	        ti: [0, 0, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: 109,
	        s: [19, 23, 0],
	        to: [0, -0.417, 0],
	        ti: [0, 0, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: 111,
	        s: [19, 20.5, 0],
	        to: [0, 0, 0],
	        ti: [0, -0.417, 0]
	      }, {
	        t: 114,
	        s: [19, 23, 0]
	      }],
	      ix: 2,
	      l: 2
	    },
	    a: {
	      a: 0,
	      k: [0, 0, 0],
	      ix: 1,
	      l: 2
	    },
	    s: {
	      a: 0,
	      k: [80, 100, 100],
	      ix: 6,
	      l: 2
	    }
	  },
	  ao: 0,
	  shapes: [{
	    ty: "rc",
	    d: 1,
	    s: {
	      a: 0,
	      k: [26, 4],
	      ix: 2
	    },
	    p: {
	      a: 0,
	      k: [0, 0],
	      ix: 3
	    },
	    r: {
	      a: 0,
	      k: 0,
	      ix: 4
	    },
	    nm: "Rectangle Path 1",
	    mn: "ADBE Vector Shape - Rect",
	    hd: false
	  }, {
	    ty: "fl",
	    c: {
	      a: 0,
	      k: [0.964705942191, 0.878431432387, 0.443137284821, 1],
	      ix: 4
	    },
	    o: {
	      a: 0,
	      k: 100,
	      ix: 5
	    },
	    r: 1,
	    bm: 0,
	    nm: "Fill 1",
	    mn: "ADBE Vector Graphic - Fill",
	    hd: false
	  }],
	  ip: 45,
	  op: 70,
	  st: 45,
	  bm: 0
	}, {
	  ddd: 0,
	  ind: 14,
	  ty: 4,
	  nm: "Shape Layer 1",
	  parent: 15,
	  sr: 1,
	  ks: {
	    o: {
	      a: 0,
	      k: 100,
	      ix: 11
	    },
	    r: {
	      a: 0,
	      k: 0,
	      ix: 10
	    },
	    p: {
	      a: 1,
	      k: [{
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: 45,
	        s: [19, 12, 0],
	        to: [0, 0.417, 0],
	        ti: [0, 0, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: 47,
	        s: [19, 14.5, 0],
	        to: [0, 0, 0],
	        ti: [0, 0.417, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 0.667
	        },
	        o: {
	          x: 0.333,
	          y: 0.333
	        },
	        t: 50,
	        s: [19, 12, 0],
	        to: [0, 0, 0],
	        ti: [0, 0, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: 65,
	        s: [19, 12, 0],
	        to: [0, 0.417, 0],
	        ti: [0, 0, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: 67,
	        s: [19, 14.5, 0],
	        to: [0, 0, 0],
	        ti: [0, 0.417, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 0.667
	        },
	        o: {
	          x: 0.333,
	          y: 0.333
	        },
	        t: 70,
	        s: [19, 12, 0],
	        to: [0, 0, 0],
	        ti: [0, 0, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: 88,
	        s: [19, 12, 0],
	        to: [0, 0.417, 0],
	        ti: [0, 0, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: 90,
	        s: [19, 14.5, 0],
	        to: [0, 0, 0],
	        ti: [0, 0.417, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 0.667
	        },
	        o: {
	          x: 0.333,
	          y: 0.333
	        },
	        t: 93,
	        s: [19, 12, 0],
	        to: [0, 0, 0],
	        ti: [0, 0, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: 109,
	        s: [19, 12, 0],
	        to: [0, 0.417, 0],
	        ti: [0, 0, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: 111,
	        s: [19, 14.5, 0],
	        to: [0, 0, 0],
	        ti: [0, 0.417, 0]
	      }, {
	        t: 114,
	        s: [19, 12, 0]
	      }],
	      ix: 2,
	      l: 2
	    },
	    a: {
	      a: 0,
	      k: [0, 0, 0],
	      ix: 1,
	      l: 2
	    },
	    s: {
	      a: 0,
	      k: [80, 100, 100],
	      ix: 6,
	      l: 2
	    }
	  },
	  ao: 0,
	  shapes: [{
	    ty: "rc",
	    d: 1,
	    s: {
	      a: 0,
	      k: [26, 4],
	      ix: 2
	    },
	    p: {
	      a: 0,
	      k: [0, 0],
	      ix: 3
	    },
	    r: {
	      a: 0,
	      k: 0,
	      ix: 4
	    },
	    nm: "Rectangle Path 1",
	    mn: "ADBE Vector Shape - Rect",
	    hd: false
	  }, {
	    ty: "fl",
	    c: {
	      a: 0,
	      k: [0.964705942191, 0.878431432387, 0.443137284821, 1],
	      ix: 4
	    },
	    o: {
	      a: 0,
	      k: 100,
	      ix: 5
	    },
	    r: 1,
	    bm: 0,
	    nm: "Fill 1",
	    mn: "ADBE Vector Graphic - Fill",
	    hd: false
	  }],
	  ip: 45,
	  op: 70,
	  st: 45,
	  bm: 0
	}, {
	  ddd: 0,
	  ind: 15,
	  ty: 4,
	  nm: "Group 3 :M",
	  sr: 1,
	  ks: {
	    o: {
	      a: 0,
	      k: 100,
	      ix: 11
	    },
	    r: {
	      a: 0,
	      k: 0,
	      ix: 10
	    },
	    p: {
	      a: 1,
	      k: [{
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: 58,
	        s: [20.065, 21.464, 0],
	        to: [0, -1.042, 0],
	        ti: [0, 1.042, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.833
	        },
	        o: {
	          x: 0.333,
	          y: 0.333
	        },
	        t: 65,
	        s: [20.065, 15.214, 0],
	        to: [0, 0, 0],
	        ti: [0, 0, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: 103,
	        s: [20.065, 15.214, 0],
	        to: [0, 1.042, 0],
	        ti: [0, -1.042, 0]
	      }, {
	        t: 111,
	        s: [20.065, 21.464, 0]
	      }],
	      ix: 2,
	      l: 2
	    },
	    a: {
	      a: 0,
	      k: [19.065, 17.214, 0],
	      ix: 1,
	      l: 2
	    },
	    s: {
	      a: 0,
	      k: [100, 100, 100],
	      ix: 6,
	      l: 2
	    }
	  },
	  ao: 0,
	  shapes: [{
	    ty: "gr",
	    it: [{
	      ty: "gr",
	      it: [{
	        ind: 0,
	        ty: "sh",
	        ix: 1,
	        ks: {
	          a: 0,
	          k: {
	            i: [[-0.915, 0], [0, -1.34], [0.915, 0], [0, 1.341]],
	            o: [[0.915, 0], [0, 1.341], [-0.915, 0], [0, -1.34]],
	            v: [[0, -2.427], [1.657, 0], [0, 2.427], [-1.657, 0]],
	            c: true
	          },
	          ix: 2
	        },
	        nm: "Path 1",
	        mn: "ADBE Vector Shape - Group",
	        hd: false
	      }, {
	        ty: "fl",
	        c: {
	          a: 0,
	          k: [0.47058826685, 0.243137270212, 0.066666670144, 1],
	          ix: 4
	        },
	        o: {
	          a: 0,
	          k: 100,
	          ix: 5
	        },
	        r: 1,
	        bm: 0,
	        nm: "Fill 1",
	        mn: "ADBE Vector Graphic - Fill",
	        hd: false
	      }, {
	        ty: "tr",
	        p: {
	          a: 0,
	          k: [25.357, 17.214],
	          ix: 2
	        },
	        a: {
	          a: 0,
	          k: [0, 0],
	          ix: 1
	        },
	        s: {
	          a: 0,
	          k: [100, 100],
	          ix: 3
	        },
	        r: {
	          a: 0,
	          k: 0,
	          ix: 6
	        },
	        o: {
	          a: 0,
	          k: 100,
	          ix: 7
	        },
	        sk: {
	          a: 0,
	          k: 0,
	          ix: 4
	        },
	        sa: {
	          a: 0,
	          k: 0,
	          ix: 5
	        },
	        nm: "Transform"
	      }],
	      nm: "Group 3",
	      np: 2,
	      cix: 2,
	      bm: 0,
	      ix: 1,
	      mn: "ADBE Vector Group",
	      hd: false
	    }, {
	      ty: "tr",
	      p: {
	        a: 0,
	        k: [25.357, 17.214],
	        ix: 2
	      },
	      a: {
	        a: 0,
	        k: [25.357, 17.214],
	        ix: 1
	      },
	      s: {
	        a: 0,
	        k: [100, 100],
	        ix: 3
	      },
	      r: {
	        a: 0,
	        k: 0,
	        ix: 6
	      },
	      o: {
	        a: 0,
	        k: 100,
	        ix: 7
	      },
	      sk: {
	        a: 0,
	        k: 0,
	        ix: 4
	      },
	      sa: {
	        a: 0,
	        k: 0,
	        ix: 5
	      },
	      nm: "Transform"
	    }],
	    nm: "Group 3",
	    np: 1,
	    cix: 2,
	    bm: 0,
	    ix: 1,
	    mn: "ADBE Vector Group",
	    hd: false
	  }, {
	    ty: "gr",
	    it: [{
	      ty: "gr",
	      it: [{
	        ind: 0,
	        ty: "sh",
	        ix: 1,
	        ks: {
	          a: 0,
	          k: {
	            i: [[-0.915, 0], [0, -1.34], [0.915, 0], [0, 1.341]],
	            o: [[0.915, 0], [0, 1.341], [-0.915, 0], [0, -1.34]],
	            v: [[0, -2.427], [1.657, 0], [0, 2.427], [-1.657, 0]],
	            c: true
	          },
	          ix: 2
	        },
	        nm: "Path 1",
	        mn: "ADBE Vector Shape - Group",
	        hd: false
	      }, {
	        ty: "fl",
	        c: {
	          a: 0,
	          k: [0.47058826685, 0.243137270212, 0.066666670144, 1],
	          ix: 4
	        },
	        o: {
	          a: 0,
	          k: 100,
	          ix: 5
	        },
	        r: 1,
	        bm: 0,
	        nm: "Fill 1",
	        mn: "ADBE Vector Graphic - Fill",
	        hd: false
	      }, {
	        ty: "tr",
	        p: {
	          a: 0,
	          k: [12.774, 17.214],
	          ix: 2
	        },
	        a: {
	          a: 0,
	          k: [0, 0],
	          ix: 1
	        },
	        s: {
	          a: 0,
	          k: [100, 100],
	          ix: 3
	        },
	        r: {
	          a: 0,
	          k: 0,
	          ix: 6
	        },
	        o: {
	          a: 0,
	          k: 100,
	          ix: 7
	        },
	        sk: {
	          a: 0,
	          k: 0,
	          ix: 4
	        },
	        sa: {
	          a: 0,
	          k: 0,
	          ix: 5
	        },
	        nm: "Transform"
	      }],
	      nm: "Group 4",
	      np: 2,
	      cix: 2,
	      bm: 0,
	      ix: 1,
	      mn: "ADBE Vector Group",
	      hd: false
	    }, {
	      ty: "tr",
	      p: {
	        a: 0,
	        k: [12.774, 17.214],
	        ix: 2
	      },
	      a: {
	        a: 0,
	        k: [12.774, 17.214],
	        ix: 1
	      },
	      s: {
	        a: 0,
	        k: [100, 100],
	        ix: 3
	      },
	      r: {
	        a: 0,
	        k: 0,
	        ix: 6
	      },
	      o: {
	        a: 0,
	        k: 100,
	        ix: 7
	      },
	      sk: {
	        a: 0,
	        k: 0,
	        ix: 4
	      },
	      sa: {
	        a: 0,
	        k: 0,
	        ix: 5
	      },
	      nm: "Transform"
	    }],
	    nm: "Group 4",
	    np: 1,
	    cix: 2,
	    bm: 0,
	    ix: 2,
	    mn: "ADBE Vector Group",
	    hd: false
	  }],
	  ip: 45,
	  op: 70,
	  st: 45,
	  bm: 0
	}, {
	  ddd: 0,
	  ind: 16,
	  ty: 4,
	  nm: "Group 6",
	  sr: 1,
	  ks: {
	    o: {
	      a: 0,
	      k: 100,
	      ix: 11
	    },
	    r: {
	      a: 0,
	      k: 0,
	      ix: 10
	    },
	    p: {
	      a: 0,
	      k: [20, 17, 0],
	      ix: 2,
	      l: 2
	    },
	    a: {
	      a: 0,
	      k: [17.25, 17.25, 0],
	      ix: 1,
	      l: 2
	    },
	    s: {
	      a: 0,
	      k: [100, 100, 100],
	      ix: 6,
	      l: 2
	    }
	  },
	  ao: 0,
	  shapes: [{
	    ty: "gr",
	    it: [{
	      ind: 0,
	      ty: "sh",
	      ix: 1,
	      ks: {
	        a: 0,
	        k: {
	          i: [[-9.389, 0], [0, -9.389], [9.389, 0], [0, 9.389]],
	          o: [[9.389, 0], [0, 9.389], [-9.389, 0], [0, -9.389]],
	          v: [[0, -17], [17, 0], [0, 17], [-17, 0]],
	          c: true
	        },
	        ix: 2
	      },
	      nm: "Path 1",
	      mn: "ADBE Vector Shape - Group",
	      hd: false
	    }, {
	      ty: "fl",
	      c: {
	        a: 0,
	        k: [0.964705942191, 0.878431432387, 0.443137284821, 1],
	        ix: 4
	      },
	      o: {
	        a: 0,
	        k: 100,
	        ix: 5
	      },
	      r: 1,
	      bm: 0,
	      nm: "Fill 1",
	      mn: "ADBE Vector Graphic - Fill",
	      hd: false
	    }, {
	      ty: "tr",
	      p: {
	        a: 0,
	        k: [17.25, 17.25],
	        ix: 2
	      },
	      a: {
	        a: 0,
	        k: [0, 0],
	        ix: 1
	      },
	      s: {
	        a: 0,
	        k: [100, 100],
	        ix: 3
	      },
	      r: {
	        a: 0,
	        k: 0,
	        ix: 6
	      },
	      o: {
	        a: 0,
	        k: 100,
	        ix: 7
	      },
	      sk: {
	        a: 0,
	        k: 0,
	        ix: 4
	      },
	      sa: {
	        a: 0,
	        k: 0,
	        ix: 5
	      },
	      nm: "Transform"
	    }],
	    nm: "Group 6",
	    np: 2,
	    cix: 2,
	    bm: 0,
	    ix: 1,
	    mn: "ADBE Vector Group",
	    hd: false
	  }],
	  ip: 45,
	  op: 70,
	  st: 45,
	  bm: 0
	}];
	var markers$3 = [];
	var cryAnimatedEmojiData = {
	  v: v$3,
	  fr: fr$3,
	  ip: ip$3,
	  op: op$3,
	  w: w$3,
	  h: h$3,
	  nm: nm$3,
	  ddd: ddd$3,
	  assets: assets$3,
	  layers: layers$3,
	  markers: markers$3
	};

	var v$4 = "5.9.1";
	var fr$4 = 25;
	var ip$4 = 0;
	var op$4 = 85;
	var w$4 = 40;
	var h$4 = 40;
	var nm$4 = "em_05";
	var ddd$4 = 0;
	var assets$4 = [];
	var layers$4 = [{
	  ddd: 0,
	  ind: 1,
	  ty: 4,
	  nm: "Shape Layer 2",
	  parent: 5,
	  sr: 1,
	  ks: {
	    o: {
	      a: 1,
	      k: [{
	        i: {
	          x: [0.833],
	          y: [0.833]
	        },
	        o: {
	          x: [0.167],
	          y: [0.167]
	        },
	        t: 56,
	        s: [75]
	      }, {
	        i: {
	          x: [0.833],
	          y: [0.833]
	        },
	        o: {
	          x: [0.167],
	          y: [0.167]
	        },
	        t: 63,
	        s: [0]
	      }, {
	        i: {
	          x: [0.833],
	          y: [0.833]
	        },
	        o: {
	          x: [0.167],
	          y: [0.167]
	        },
	        t: 78,
	        s: [0]
	      }, {
	        t: 83,
	        s: [75]
	      }],
	      ix: 11
	    },
	    r: {
	      a: 0,
	      k: 0,
	      ix: 10
	    },
	    p: {
	      a: 0,
	      k: [32.125, 21.75, 0],
	      ix: 2,
	      l: 2
	    },
	    a: {
	      a: 0,
	      k: [0, 0, 0],
	      ix: 1,
	      l: 2
	    },
	    s: {
	      a: 0,
	      k: [4, 14, 100],
	      ix: 6,
	      l: 2
	    }
	  },
	  ao: 0,
	  shapes: [{
	    d: 1,
	    ty: "el",
	    s: {
	      a: 0,
	      k: [100, 100],
	      ix: 2
	    },
	    p: {
	      a: 0,
	      k: [0, 0],
	      ix: 3
	    },
	    nm: "Ellipse Path 1",
	    mn: "ADBE Vector Shape - Ellipse",
	    hd: false
	  }, {
	    ty: "fl",
	    c: {
	      a: 0,
	      k: [1, 0.592156862745, 0.58431372549, 1],
	      ix: 4
	    },
	    o: {
	      a: 0,
	      k: 100,
	      ix: 5
	    },
	    r: 1,
	    bm: 0,
	    nm: "Fill 1",
	    mn: "ADBE Vector Graphic - Fill",
	    hd: false
	  }, {
	    ty: "fl",
	    c: {
	      a: 0,
	      k: [1, 0.592156862745, 0.58431372549, 1],
	      ix: 4
	    },
	    o: {
	      a: 0,
	      k: 100,
	      ix: 5
	    },
	    r: 1,
	    bm: 0,
	    nm: "Fill 2",
	    mn: "ADBE Vector Graphic - Fill",
	    hd: false
	  }],
	  ip: 0,
	  op: 85,
	  st: 0,
	  bm: 0
	}, {
	  ddd: 0,
	  ind: 2,
	  ty: 4,
	  nm: "Shape Layer 1",
	  parent: 5,
	  sr: 1,
	  ks: {
	    o: {
	      a: 1,
	      k: [{
	        i: {
	          x: [0.833],
	          y: [0.833]
	        },
	        o: {
	          x: [0.167],
	          y: [0.167]
	        },
	        t: 56,
	        s: [75]
	      }, {
	        i: {
	          x: [0.833],
	          y: [0.833]
	        },
	        o: {
	          x: [0.167],
	          y: [0.167]
	        },
	        t: 63,
	        s: [0]
	      }, {
	        i: {
	          x: [0.833],
	          y: [0.833]
	        },
	        o: {
	          x: [0.167],
	          y: [0.167]
	        },
	        t: 78,
	        s: [0]
	      }, {
	        t: 83,
	        s: [75]
	      }],
	      ix: 11
	    },
	    r: {
	      a: 0,
	      k: 0,
	      ix: 10
	    },
	    p: {
	      a: 0,
	      k: [6.938, 21.75, 0],
	      ix: 2,
	      l: 2
	    },
	    a: {
	      a: 0,
	      k: [0, 0, 0],
	      ix: 1,
	      l: 2
	    },
	    s: {
	      a: 0,
	      k: [4, 14, 100],
	      ix: 6,
	      l: 2
	    }
	  },
	  ao: 0,
	  shapes: [{
	    d: 1,
	    ty: "el",
	    s: {
	      a: 0,
	      k: [100, 100],
	      ix: 2
	    },
	    p: {
	      a: 0,
	      k: [0, 0],
	      ix: 3
	    },
	    nm: "Ellipse Path 1",
	    mn: "ADBE Vector Shape - Ellipse",
	    hd: false
	  }, {
	    ty: "fl",
	    c: {
	      a: 0,
	      k: [1, 0.592156862745, 0.58431372549, 1],
	      ix: 4
	    },
	    o: {
	      a: 0,
	      k: 100,
	      ix: 5
	    },
	    r: 1,
	    bm: 0,
	    nm: "Fill 1",
	    mn: "ADBE Vector Graphic - Fill",
	    hd: false
	  }, {
	    ty: "fl",
	    c: {
	      a: 0,
	      k: [1, 0.592156862745, 0.58431372549, 1],
	      ix: 4
	    },
	    o: {
	      a: 0,
	      k: 100,
	      ix: 5
	    },
	    r: 1,
	    bm: 0,
	    nm: "Fill 2",
	    mn: "ADBE Vector Graphic - Fill",
	    hd: false
	  }],
	  ip: 0,
	  op: 85,
	  st: 0,
	  bm: 0
	}, {
	  ddd: 0,
	  ind: 3,
	  ty: 4,
	  nm: "Group 1",
	  sr: 1,
	  ks: {
	    o: {
	      a: 0,
	      k: 100,
	      ix: 11
	    },
	    r: {
	      a: 0,
	      k: 0,
	      ix: 10
	    },
	    p: {
	      a: 1,
	      k: [{
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: 0,
	        s: [13.776, 15.896, 0],
	        to: [0, -0.625, 0],
	        ti: [0, 0.292, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: 9,
	        s: [13.776, 12.146, 0],
	        to: [0, -0.292, 0],
	        ti: [0, -0.333, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 0.667
	        },
	        o: {
	          x: 0.333,
	          y: 0.333
	        },
	        t: 15,
	        s: [13.776, 14.146, 0],
	        to: [0, 0, 0],
	        ti: [0, 0, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: 56,
	        s: [13.776, 14.146, 0],
	        to: [0, -1.604, 0],
	        ti: [0, 1.604, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 0.667
	        },
	        o: {
	          x: 0.333,
	          y: 0.333
	        },
	        t: 63,
	        s: [13.776, 4.521, 0],
	        to: [0, 0, 0],
	        ti: [0, 0, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: 78,
	        s: [13.776, 4.521, 0],
	        to: [0, 1.896, 0],
	        ti: [0, -1.896, 0]
	      }, {
	        t: 83,
	        s: [13.776, 15.896, 0]
	      }],
	      ix: 2,
	      l: 2
	    },
	    a: {
	      a: 0,
	      k: [12.776, 17.896, 0],
	      ix: 1,
	      l: 2
	    },
	    s: {
	      a: 0,
	      k: [100, 100, 100],
	      ix: 6,
	      l: 2
	    }
	  },
	  ao: 0,
	  shapes: [{
	    ty: "gr",
	    it: [{
	      ty: "gr",
	      it: [{
	        ind: 0,
	        ty: "sh",
	        ix: 1,
	        ks: {
	          a: 0,
	          k: {
	            i: [[0.268, 0.008], [1.033, 0.845], [0.252, 0.549], [-0.071, 0.132], [0, 0], [-0.269, -0.399], [-0.347, -0.284], [-1.185, -0.154], [0.181, -0.335]],
	            o: [[-1.483, -0.046], [-0.534, -0.437], [-0.064, -0.14], [0, 0], [0.219, -0.406], [0.226, 0.336], [0.849, 0.694], [0.438, 0.057], [-0.11, 0.203]],
	            v: [[2.484, 2.023], [-1.426, 0.695], [-3.225, -1.137], [-3.206, -1.564], [-3.184, -1.604], [-2.012, -1.633], [-0.525, -0.361], [2.6, 0.906], [3.107, 1.71]],
	            c: true
	          },
	          ix: 2
	        },
	        nm: "Path 1",
	        mn: "ADBE Vector Shape - Group",
	        hd: false
	      }, {
	        ty: "fl",
	        c: {
	          a: 0,
	          k: [0.47058826685, 0.243137270212, 0.066666670144, 1],
	          ix: 4
	        },
	        o: {
	          a: 0,
	          k: 100,
	          ix: 5
	        },
	        r: 1,
	        bm: 0,
	        nm: "Fill 1",
	        mn: "ADBE Vector Graphic - Fill",
	        hd: false
	      }, {
	        ty: "tr",
	        p: {
	          a: 0,
	          k: [12.826, 17.844],
	          ix: 2
	        },
	        a: {
	          a: 0,
	          k: [0, 0],
	          ix: 1
	        },
	        s: {
	          a: 0,
	          k: [100, 100],
	          ix: 3
	        },
	        r: {
	          a: 0,
	          k: 0,
	          ix: 6
	        },
	        o: {
	          a: 0,
	          k: 100,
	          ix: 7
	        },
	        sk: {
	          a: 0,
	          k: 0,
	          ix: 4
	        },
	        sa: {
	          a: 0,
	          k: 0,
	          ix: 5
	        },
	        nm: "Transform"
	      }],
	      nm: "Group 1",
	      np: 2,
	      cix: 2,
	      bm: 0,
	      ix: 1,
	      mn: "ADBE Vector Group",
	      hd: false
	    }, {
	      ty: "tr",
	      p: {
	        a: 0,
	        k: [12.776, 17.896],
	        ix: 2
	      },
	      a: {
	        a: 0,
	        k: [12.776, 17.896],
	        ix: 1
	      },
	      s: {
	        a: 0,
	        k: [100, 100],
	        ix: 3
	      },
	      r: {
	        a: 0,
	        k: 0,
	        ix: 6
	      },
	      o: {
	        a: 0,
	        k: 100,
	        ix: 7
	      },
	      sk: {
	        a: 0,
	        k: 0,
	        ix: 4
	      },
	      sa: {
	        a: 0,
	        k: 0,
	        ix: 5
	      },
	      nm: "Transform"
	    }],
	    nm: "Group 1",
	    np: 1,
	    cix: 2,
	    bm: 0,
	    ix: 1,
	    mn: "ADBE Vector Group",
	    hd: false
	  }],
	  ip: 0,
	  op: 85,
	  st: 0,
	  bm: 0
	}, {
	  ddd: 0,
	  ind: 4,
	  ty: 4,
	  nm: "Group 2",
	  sr: 1,
	  ks: {
	    o: {
	      a: 0,
	      k: 100,
	      ix: 11
	    },
	    r: {
	      a: 0,
	      k: 0,
	      ix: 10
	    },
	    p: {
	      a: 1,
	      k: [{
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: 0,
	        s: [25.514, 15.911, 0],
	        to: [0, -0.625, 0],
	        ti: [0, 0.292, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: 9,
	        s: [25.514, 12.161, 0],
	        to: [0, -0.292, 0],
	        ti: [0, -0.333, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 0.667
	        },
	        o: {
	          x: 0.333,
	          y: 0.333
	        },
	        t: 15,
	        s: [25.514, 14.161, 0],
	        to: [0, 0, 0],
	        ti: [0, 0, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: 56,
	        s: [25.514, 14.161, 0],
	        to: [0, -1.604, 0],
	        ti: [0, 1.604, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 0.667
	        },
	        o: {
	          x: 0.333,
	          y: 0.333
	        },
	        t: 63,
	        s: [25.514, 4.536, 0],
	        to: [0, 0, 0],
	        ti: [0, 0, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: 78,
	        s: [25.514, 4.536, 0],
	        to: [0, 1.896, 0],
	        ti: [0, -1.896, 0]
	      }, {
	        t: 83,
	        s: [25.514, 15.911, 0]
	      }],
	      ix: 2,
	      l: 2
	    },
	    a: {
	      a: 0,
	      k: [24.514, 17.911, 0],
	      ix: 1,
	      l: 2
	    },
	    s: {
	      a: 0,
	      k: [100, 100, 100],
	      ix: 6,
	      l: 2
	    }
	  },
	  ao: 0,
	  shapes: [{
	    ty: "gr",
	    it: [{
	      ty: "gr",
	      it: [{
	        ind: 0,
	        ty: "sh",
	        ix: 1,
	        ks: {
	          a: 0,
	          k: {
	            i: [[-0.268, 0.008], [-1.033, 0.845], [-0.252, 0.549], [0.072, 0.132], [0, 0], [0.268, -0.399], [0.347, -0.284], [1.185, -0.154], [-0.181, -0.335]],
	            o: [[1.483, -0.047], [0.534, -0.437], [0.063, -0.14], [0, 0], [-0.22, -0.406], [-0.227, 0.336], [-0.849, 0.694], [-0.437, 0.057], [0.109, 0.203]],
	            v: [[-2.484, 2.023], [1.426, 0.695], [3.225, -1.137], [3.205, -1.564], [3.184, -1.604], [2.012, -1.633], [0.526, -0.361], [-2.601, 0.906], [-3.107, 1.71]],
	            c: true
	          },
	          ix: 2
	        },
	        nm: "Path 1",
	        mn: "ADBE Vector Shape - Group",
	        hd: false
	      }, {
	        ty: "fl",
	        c: {
	          a: 0,
	          k: [0.47058826685, 0.243137270212, 0.066666670144, 1],
	          ix: 4
	        },
	        o: {
	          a: 0,
	          k: 100,
	          ix: 5
	        },
	        r: 1,
	        bm: 0,
	        nm: "Fill 1",
	        mn: "ADBE Vector Graphic - Fill",
	        hd: false
	      }, {
	        ty: "tr",
	        p: {
	          a: 0,
	          k: [24.464, 17.859],
	          ix: 2
	        },
	        a: {
	          a: 0,
	          k: [0, 0],
	          ix: 1
	        },
	        s: {
	          a: 0,
	          k: [100, 100],
	          ix: 3
	        },
	        r: {
	          a: 0,
	          k: 0,
	          ix: 6
	        },
	        o: {
	          a: 0,
	          k: 100,
	          ix: 7
	        },
	        sk: {
	          a: 0,
	          k: 0,
	          ix: 4
	        },
	        sa: {
	          a: 0,
	          k: 0,
	          ix: 5
	        },
	        nm: "Transform"
	      }],
	      nm: "Group 2",
	      np: 2,
	      cix: 2,
	      bm: 0,
	      ix: 1,
	      mn: "ADBE Vector Group",
	      hd: false
	    }, {
	      ty: "tr",
	      p: {
	        a: 0,
	        k: [24.514, 17.911],
	        ix: 2
	      },
	      a: {
	        a: 0,
	        k: [24.514, 17.911],
	        ix: 1
	      },
	      s: {
	        a: 0,
	        k: [100, 100],
	        ix: 3
	      },
	      r: {
	        a: 0,
	        k: 0,
	        ix: 6
	      },
	      o: {
	        a: 0,
	        k: 100,
	        ix: 7
	      },
	      sk: {
	        a: 0,
	        k: 0,
	        ix: 4
	      },
	      sa: {
	        a: 0,
	        k: 0,
	        ix: 5
	      },
	      nm: "Transform"
	    }],
	    nm: "Group 2",
	    np: 1,
	    cix: 2,
	    bm: 0,
	    ix: 1,
	    mn: "ADBE Vector Group",
	    hd: false
	  }],
	  ip: 0,
	  op: 85,
	  st: 0,
	  bm: 0
	}, {
	  ddd: 0,
	  ind: 5,
	  ty: 4,
	  nm: "Group 3",
	  sr: 1,
	  ks: {
	    o: {
	      a: 0,
	      k: 100,
	      ix: 11
	    },
	    r: {
	      a: 0,
	      k: 0,
	      ix: 10
	    },
	    p: {
	      a: 1,
	      k: [{
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: 0,
	        s: [13.773, 18.001, 0],
	        to: [0, -0.625, 0],
	        ti: [0, 0.292, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: 9,
	        s: [13.773, 14.251, 0],
	        to: [0, -0.292, 0],
	        ti: [0, -0.333, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 0.667
	        },
	        o: {
	          x: 0.333,
	          y: 0.333
	        },
	        t: 15,
	        s: [13.773, 16.251, 0],
	        to: [0, 0, 0],
	        ti: [0, 0, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: 56,
	        s: [13.773, 16.251, 0],
	        to: [0, -0.771, 0],
	        ti: [0, 0.771, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 0.667
	        },
	        o: {
	          x: 0.333,
	          y: 0.333
	        },
	        t: 63,
	        s: [13.773, 11.626, 0],
	        to: [0, 0, 0],
	        ti: [0, 0, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: 78,
	        s: [13.773, 11.626, 0],
	        to: [0, 1.062, 0],
	        ti: [0, -1.062, 0]
	      }, {
	        t: 83,
	        s: [13.773, 18.001, 0]
	      }],
	      ix: 2,
	      l: 2
	    },
	    a: {
	      a: 0,
	      k: [12.773, 20.001, 0],
	      ix: 1,
	      l: 2
	    },
	    s: {
	      a: 0,
	      k: [100, 100, 100],
	      ix: 6,
	      l: 2
	    }
	  },
	  ao: 0,
	  shapes: [{
	    ty: "gr",
	    it: [{
	      ty: "gr",
	      it: [{
	        ind: 0,
	        ty: "sh",
	        ix: 1,
	        ks: {
	          a: 0,
	          k: {
	            i: [[0, -0.92], [-0.909, 0], [0, 0.92], [0.908, 0]],
	            o: [[0, 0.92], [0.908, 0], [0, -0.92], [-0.909, 0]],
	            v: [[-1.645, 0.001], [0, 1.665], [1.645, 0.001], [0, -1.665]],
	            c: true
	          },
	          ix: 2
	        },
	        nm: "Path 1",
	        mn: "ADBE Vector Shape - Group",
	        hd: false
	      }, {
	        ty: "fl",
	        c: {
	          a: 0,
	          k: [0.47058826685, 0.243137270212, 0.066666670144, 1],
	          ix: 4
	        },
	        o: {
	          a: 0,
	          k: 100,
	          ix: 5
	        },
	        r: 1,
	        bm: 0,
	        nm: "Fill 1",
	        mn: "ADBE Vector Graphic - Fill",
	        hd: false
	      }, {
	        ty: "tr",
	        p: {
	          a: 0,
	          k: [12.773, 20.001],
	          ix: 2
	        },
	        a: {
	          a: 0,
	          k: [0, 0],
	          ix: 1
	        },
	        s: {
	          a: 0,
	          k: [100, 100],
	          ix: 3
	        },
	        r: {
	          a: 0,
	          k: 0,
	          ix: 6
	        },
	        o: {
	          a: 0,
	          k: 100,
	          ix: 7
	        },
	        sk: {
	          a: 0,
	          k: 0,
	          ix: 4
	        },
	        sa: {
	          a: 0,
	          k: 0,
	          ix: 5
	        },
	        nm: "Transform"
	      }],
	      nm: "Group 3",
	      np: 2,
	      cix: 2,
	      bm: 0,
	      ix: 1,
	      mn: "ADBE Vector Group",
	      hd: false
	    }, {
	      ty: "tr",
	      p: {
	        a: 0,
	        k: [12.773, 20.001],
	        ix: 2
	      },
	      a: {
	        a: 0,
	        k: [12.773, 20.001],
	        ix: 1
	      },
	      s: {
	        a: 0,
	        k: [100, 100],
	        ix: 3
	      },
	      r: {
	        a: 0,
	        k: 0,
	        ix: 6
	      },
	      o: {
	        a: 0,
	        k: 100,
	        ix: 7
	      },
	      sk: {
	        a: 0,
	        k: 0,
	        ix: 4
	      },
	      sa: {
	        a: 0,
	        k: 0,
	        ix: 5
	      },
	      nm: "Transform"
	    }],
	    nm: "Group 3",
	    np: 1,
	    cix: 2,
	    bm: 0,
	    ix: 1,
	    mn: "ADBE Vector Group",
	    hd: false
	  }],
	  ip: 0,
	  op: 85,
	  st: 0,
	  bm: 0
	}, {
	  ddd: 0,
	  ind: 6,
	  ty: 4,
	  nm: "Group 4",
	  sr: 1,
	  ks: {
	    o: {
	      a: 0,
	      k: 100,
	      ix: 11
	    },
	    r: {
	      a: 0,
	      k: 0,
	      ix: 10
	    },
	    p: {
	      a: 1,
	      k: [{
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: 0,
	        s: [25.242, 18.001, 0],
	        to: [0, -0.625, 0],
	        ti: [0, 0.292, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: 9,
	        s: [25.242, 14.251, 0],
	        to: [0, -0.292, 0],
	        ti: [0, -0.333, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 0.667
	        },
	        o: {
	          x: 0.333,
	          y: 0.333
	        },
	        t: 15,
	        s: [25.242, 16.251, 0],
	        to: [0, 0, 0],
	        ti: [0, 0, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: 56,
	        s: [25.242, 16.251, 0],
	        to: [0, -0.771, 0],
	        ti: [0, 0.771, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 0.667
	        },
	        o: {
	          x: 0.333,
	          y: 0.333
	        },
	        t: 63,
	        s: [25.242, 11.626, 0],
	        to: [0, 0, 0],
	        ti: [0, 0, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: 78,
	        s: [25.242, 11.626, 0],
	        to: [0, 1.062, 0],
	        ti: [0, -1.062, 0]
	      }, {
	        t: 83,
	        s: [25.242, 18.001, 0]
	      }],
	      ix: 2,
	      l: 2
	    },
	    a: {
	      a: 0,
	      k: [24.242, 20.001, 0],
	      ix: 1,
	      l: 2
	    },
	    s: {
	      a: 0,
	      k: [100, 100, 100],
	      ix: 6,
	      l: 2
	    }
	  },
	  ao: 0,
	  shapes: [{
	    ty: "gr",
	    it: [{
	      ty: "gr",
	      it: [{
	        ind: 0,
	        ty: "sh",
	        ix: 1,
	        ks: {
	          a: 0,
	          k: {
	            i: [[0, -0.92], [0.908, 0], [0, 0.92], [-0.909, 0]],
	            o: [[0, 0.92], [-0.909, 0], [0, -0.92], [0.908, 0]],
	            v: [[1.645, 0.001], [0, 1.665], [-1.645, 0.001], [0, -1.665]],
	            c: true
	          },
	          ix: 2
	        },
	        nm: "Path 1",
	        mn: "ADBE Vector Shape - Group",
	        hd: false
	      }, {
	        ty: "fl",
	        c: {
	          a: 0,
	          k: [0.47058826685, 0.243137270212, 0.066666670144, 1],
	          ix: 4
	        },
	        o: {
	          a: 0,
	          k: 100,
	          ix: 5
	        },
	        r: 1,
	        bm: 0,
	        nm: "Fill 1",
	        mn: "ADBE Vector Graphic - Fill",
	        hd: false
	      }, {
	        ty: "tr",
	        p: {
	          a: 0,
	          k: [24.242, 20.001],
	          ix: 2
	        },
	        a: {
	          a: 0,
	          k: [0, 0],
	          ix: 1
	        },
	        s: {
	          a: 0,
	          k: [100, 100],
	          ix: 3
	        },
	        r: {
	          a: 0,
	          k: 0,
	          ix: 6
	        },
	        o: {
	          a: 0,
	          k: 100,
	          ix: 7
	        },
	        sk: {
	          a: 0,
	          k: 0,
	          ix: 4
	        },
	        sa: {
	          a: 0,
	          k: 0,
	          ix: 5
	        },
	        nm: "Transform"
	      }],
	      nm: "Group 4",
	      np: 2,
	      cix: 2,
	      bm: 0,
	      ix: 1,
	      mn: "ADBE Vector Group",
	      hd: false
	    }, {
	      ty: "tr",
	      p: {
	        a: 0,
	        k: [24.242, 20.001],
	        ix: 2
	      },
	      a: {
	        a: 0,
	        k: [24.242, 20.001],
	        ix: 1
	      },
	      s: {
	        a: 0,
	        k: [100, 100],
	        ix: 3
	      },
	      r: {
	        a: 0,
	        k: 0,
	        ix: 6
	      },
	      o: {
	        a: 0,
	        k: 100,
	        ix: 7
	      },
	      sk: {
	        a: 0,
	        k: 0,
	        ix: 4
	      },
	      sa: {
	        a: 0,
	        k: 0,
	        ix: 5
	      },
	      nm: "Transform"
	    }],
	    nm: "Group 4",
	    np: 1,
	    cix: 2,
	    bm: 0,
	    ix: 1,
	    mn: "ADBE Vector Group",
	    hd: false
	  }],
	  ip: 0,
	  op: 85,
	  st: 0,
	  bm: 0
	}, {
	  ddd: 0,
	  ind: 7,
	  ty: 4,
	  nm: "Group 5",
	  sr: 1,
	  ks: {
	    o: {
	      a: 0,
	      k: 100,
	      ix: 11
	    },
	    r: {
	      a: 0,
	      k: 0,
	      ix: 10
	    },
	    p: {
	      a: 1,
	      k: [{
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: 0,
	        s: [20.127, 25.524, 0],
	        to: [0, -0.625, 0],
	        ti: [0, 0.292, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: 9,
	        s: [20.127, 21.774, 0],
	        to: [0, -0.292, 0],
	        ti: [0, -0.333, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 0.667
	        },
	        o: {
	          x: 0.333,
	          y: 0.333
	        },
	        t: 15,
	        s: [20.127, 23.774, 0],
	        to: [0, 0, 0],
	        ti: [0, 0, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: 56,
	        s: [20.127, 23.774, 0],
	        to: [0, -0.771, 0],
	        ti: [0, 0.771, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 0.667
	        },
	        o: {
	          x: 0.333,
	          y: 0.333
	        },
	        t: 63,
	        s: [20.127, 19.149, 0],
	        to: [0, 0, 0],
	        ti: [0, 0, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: 78,
	        s: [20.127, 19.149, 0],
	        to: [0, 1.062, 0],
	        ti: [0, -1.062, 0]
	      }, {
	        t: 83,
	        s: [20.127, 25.524, 0]
	      }],
	      ix: 2,
	      l: 2
	    },
	    a: {
	      a: 0,
	      k: [19.127, 27.524, 0],
	      ix: 1,
	      l: 2
	    },
	    s: {
	      a: 0,
	      k: [100, 100, 100],
	      ix: 6,
	      l: 2
	    }
	  },
	  ao: 0,
	  shapes: [{
	    ty: "gr",
	    it: [{
	      ind: 0,
	      ty: "sh",
	      ix: 1,
	      ks: {
	        a: 0,
	        k: {
	          i: [[0, 0], [-0.182, 0.195], [-1.936, -0.001], [-1.49, -1.597], [0, 0.308], [0, 0], [0.059, 0.07], [2.095, 0.001], [1.558, -1.843], [0, -0.105]],
	          o: [[0, 0.309], [1.491, -1.596], [1.936, 0], [0.183, 0.195], [0, 0], [0, -0.105], [-1.558, -1.844], [-2.096, -0.001], [-0.059, 0.07], [0, 0]],
	          v: [[-5.68, 1.74], [-5.224, 2.024], [0, -0.249], [5.224, 2.027], [5.68, 1.744], [5.68, 0.747], [5.587, 0.47], [0.002, -2.221], [-5.586, 0.467], [-5.679, 0.744]],
	          c: true
	        },
	        ix: 2
	      },
	      nm: "Path 1",
	      mn: "ADBE Vector Shape - Group",
	      hd: false
	    }, {
	      ty: "fl",
	      c: {
	        a: 0,
	        k: [0.470588265213, 0.243137269862, 0.066666666667, 1],
	        ix: 4
	      },
	      o: {
	        a: 0,
	        k: 100,
	        ix: 5
	      },
	      r: 1,
	      bm: 0,
	      nm: "Fill 1",
	      mn: "ADBE Vector Graphic - Fill",
	      hd: false
	    }, {
	      ty: "tr",
	      p: {
	        a: 0,
	        k: [19.127, 27.58],
	        ix: 2
	      },
	      a: {
	        a: 0,
	        k: [0, 0],
	        ix: 1
	      },
	      s: {
	        a: 0,
	        k: [100, 100],
	        ix: 3
	      },
	      r: {
	        a: 0,
	        k: 0,
	        ix: 6
	      },
	      o: {
	        a: 0,
	        k: 100,
	        ix: 7
	      },
	      sk: {
	        a: 0,
	        k: 0,
	        ix: 4
	      },
	      sa: {
	        a: 0,
	        k: 0,
	        ix: 5
	      },
	      nm: "Transform"
	    }],
	    nm: "Group 5",
	    np: 2,
	    cix: 2,
	    bm: 0,
	    ix: 1,
	    mn: "ADBE Vector Group",
	    hd: false
	  }],
	  ip: 0,
	  op: 85,
	  st: 0,
	  bm: 0
	}, {
	  ddd: 0,
	  ind: 8,
	  ty: 4,
	  nm: "Group 9",
	  sr: 1,
	  ks: {
	    o: {
	      a: 1,
	      k: [{
	        i: {
	          x: [0.833],
	          y: [0.833]
	        },
	        o: {
	          x: [0.167],
	          y: [0.167]
	        },
	        t: 53,
	        s: [100]
	      }, {
	        i: {
	          x: [0.833],
	          y: [0.833]
	        },
	        o: {
	          x: [0.167],
	          y: [0.167]
	        },
	        t: 63,
	        s: [0]
	      }, {
	        i: {
	          x: [0.833],
	          y: [0.833]
	        },
	        o: {
	          x: [0.167],
	          y: [0.167]
	        },
	        t: 72,
	        s: [0]
	      }, {
	        t: 83,
	        s: [100]
	      }],
	      ix: 11
	    },
	    r: {
	      a: 0,
	      k: 0,
	      ix: 10
	    },
	    p: {
	      a: 0,
	      k: [20, 17, 0],
	      ix: 2,
	      l: 2
	    },
	    a: {
	      a: 0,
	      k: [19, 19, 0],
	      ix: 1,
	      l: 2
	    },
	    s: {
	      a: 0,
	      k: [100, 100, 100],
	      ix: 6,
	      l: 2
	    }
	  },
	  ao: 0,
	  shapes: [{
	    ty: "gr",
	    it: [{
	      ind: 0,
	      ty: "sh",
	      ix: 1,
	      ks: {
	        a: 0,
	        k: {
	          i: [[-9.389, 0], [0, -9.389], [9.389, 0], [0, 9.389]],
	          o: [[9.389, 0], [0, 9.389], [-9.389, 0], [0, -9.389]],
	          v: [[0, -17], [17, 0], [0, 17], [-17, 0]],
	          c: true
	        },
	        ix: 2
	      },
	      nm: "Path 1",
	      mn: "ADBE Vector Shape - Group",
	      hd: false
	    }, {
	      ty: "fl",
	      c: {
	        a: 0,
	        k: [1, 0.725490196078, 0.717647058824, 1],
	        ix: 4
	      },
	      o: {
	        a: 0,
	        k: 100,
	        ix: 5
	      },
	      r: 1,
	      bm: 0,
	      nm: "Fill 1",
	      mn: "ADBE Vector Graphic - Fill",
	      hd: false
	    }, {
	      ty: "tr",
	      p: {
	        a: 0,
	        k: [19, 19],
	        ix: 2
	      },
	      a: {
	        a: 0,
	        k: [0, 0],
	        ix: 1
	      },
	      s: {
	        a: 0,
	        k: [100, 100],
	        ix: 3
	      },
	      r: {
	        a: 0,
	        k: 0,
	        ix: 6
	      },
	      o: {
	        a: 0,
	        k: 100,
	        ix: 7
	      },
	      sk: {
	        a: 0,
	        k: 0,
	        ix: 4
	      },
	      sa: {
	        a: 0,
	        k: 0,
	        ix: 5
	      },
	      nm: "Transform"
	    }],
	    nm: "Group 8",
	    np: 2,
	    cix: 2,
	    bm: 0,
	    ix: 1,
	    mn: "ADBE Vector Group",
	    hd: false
	  }],
	  ip: 0,
	  op: 85,
	  st: 0,
	  bm: 0
	}, {
	  ddd: 0,
	  ind: 9,
	  ty: 4,
	  nm: "Group 8",
	  sr: 1,
	  ks: {
	    o: {
	      a: 0,
	      k: 100,
	      ix: 11
	    },
	    r: {
	      a: 0,
	      k: 0,
	      ix: 10
	    },
	    p: {
	      a: 0,
	      k: [20, 17, 0],
	      ix: 2,
	      l: 2
	    },
	    a: {
	      a: 0,
	      k: [19, 19, 0],
	      ix: 1,
	      l: 2
	    },
	    s: {
	      a: 0,
	      k: [100, 100, 100],
	      ix: 6,
	      l: 2
	    }
	  },
	  ao: 0,
	  shapes: [{
	    ty: "gr",
	    it: [{
	      ind: 0,
	      ty: "sh",
	      ix: 1,
	      ks: {
	        a: 0,
	        k: {
	          i: [[-9.389, 0], [0, -9.389], [9.389, 0], [0, 9.389]],
	          o: [[9.389, 0], [0, 9.389], [-9.389, 0], [0, -9.389]],
	          v: [[0, -17], [17, 0], [0, 17], [-17, 0]],
	          c: true
	        },
	        ix: 2
	      },
	      nm: "Path 1",
	      mn: "ADBE Vector Shape - Group",
	      hd: false
	    }, {
	      ty: "fl",
	      c: {
	        a: 0,
	        k: [0.964705882353, 0.878431372549, 0.443137254902, 1],
	        ix: 4
	      },
	      o: {
	        a: 0,
	        k: 100,
	        ix: 5
	      },
	      r: 1,
	      bm: 0,
	      nm: "Fill 1",
	      mn: "ADBE Vector Graphic - Fill",
	      hd: false
	    }, {
	      ty: "tr",
	      p: {
	        a: 0,
	        k: [19, 19],
	        ix: 2
	      },
	      a: {
	        a: 0,
	        k: [0, 0],
	        ix: 1
	      },
	      s: {
	        a: 0,
	        k: [100, 100],
	        ix: 3
	      },
	      r: {
	        a: 0,
	        k: 0,
	        ix: 6
	      },
	      o: {
	        a: 0,
	        k: 100,
	        ix: 7
	      },
	      sk: {
	        a: 0,
	        k: 0,
	        ix: 4
	      },
	      sa: {
	        a: 0,
	        k: 0,
	        ix: 5
	      },
	      nm: "Transform"
	    }],
	    nm: "Group 8",
	    np: 2,
	    cix: 2,
	    bm: 0,
	    ix: 1,
	    mn: "ADBE Vector Group",
	    hd: false
	  }],
	  ip: 0,
	  op: 85,
	  st: 0,
	  bm: 0
	}];
	var markers$4 = [];
	var angryAnimatedEmojiData = {
	  v: v$4,
	  fr: fr$4,
	  ip: ip$4,
	  op: op$4,
	  w: w$4,
	  h: h$4,
	  nm: nm$4,
	  ddd: ddd$4,
	  assets: assets$4,
	  layers: layers$4,
	  markers: markers$4
	};

	var v$5 = "5.9.1";
	var fr$5 = 25;
	var ip$5 = 0;
	var op$5 = 60;
	var w$5 = 40;
	var h$5 = 40;
	var nm$5 = "em_06";
	var ddd$5 = 0;
	var assets$5 = [];
	var layers$5 = [{
	  ddd: 0,
	  ind: 1,
	  ty: 4,
	  nm: "Group 1 :M",
	  sr: 1,
	  ks: {
	    o: {
	      a: 0,
	      k: 100,
	      ix: 11
	    },
	    r: {
	      a: 1,
	      k: [{
	        i: {
	          x: [0.833],
	          y: [1]
	        },
	        o: {
	          x: [0.167],
	          y: [0]
	        },
	        t: 0,
	        s: [0]
	      }, {
	        i: {
	          x: [0.833],
	          y: [1]
	        },
	        o: {
	          x: [0.167],
	          y: [0]
	        },
	        t: 1,
	        s: [0]
	      }, {
	        i: {
	          x: [0.833],
	          y: [1]
	        },
	        o: {
	          x: [0.167],
	          y: [0]
	        },
	        t: 2,
	        s: [0]
	      }, {
	        i: {
	          x: [0.833],
	          y: [1]
	        },
	        o: {
	          x: [0.167],
	          y: [0]
	        },
	        t: 3,
	        s: [0]
	      }, {
	        i: {
	          x: [0.833],
	          y: [1]
	        },
	        o: {
	          x: [0.167],
	          y: [0]
	        },
	        t: 4,
	        s: [0]
	      }, {
	        i: {
	          x: [0.833],
	          y: [1]
	        },
	        o: {
	          x: [0.167],
	          y: [0]
	        },
	        t: 5,
	        s: [0]
	      }, {
	        i: {
	          x: [0.833],
	          y: [1]
	        },
	        o: {
	          x: [0.167],
	          y: [0]
	        },
	        t: 6,
	        s: [0]
	      }, {
	        i: {
	          x: [0.833],
	          y: [1]
	        },
	        o: {
	          x: [0.167],
	          y: [0]
	        },
	        t: 7,
	        s: [0]
	      }, {
	        i: {
	          x: [0.833],
	          y: [1]
	        },
	        o: {
	          x: [0.167],
	          y: [0]
	        },
	        t: 8,
	        s: [0]
	      }, {
	        i: {
	          x: [0.833],
	          y: [1.06]
	        },
	        o: {
	          x: [0.167],
	          y: [0]
	        },
	        t: 9,
	        s: [0]
	      }, {
	        i: {
	          x: [0.833],
	          y: [0.702]
	        },
	        o: {
	          x: [0.167],
	          y: [0.083]
	        },
	        t: 10,
	        s: [0]
	      }, {
	        i: {
	          x: [0.833],
	          y: [0.803]
	        },
	        o: {
	          x: [0.167],
	          y: [0.116]
	        },
	        t: 11,
	        s: [-0.72]
	      }, {
	        i: {
	          x: [0.833],
	          y: [0.826]
	        },
	        o: {
	          x: [0.167],
	          y: [0.144]
	        },
	        t: 12,
	        s: [-2.577]
	      }, {
	        i: {
	          x: [0.833],
	          y: [0.84]
	        },
	        o: {
	          x: [0.167],
	          y: [0.16]
	        },
	        t: 13,
	        s: [-5.117]
	      }, {
	        i: {
	          x: [0.833],
	          y: [0.856]
	        },
	        o: {
	          x: [0.167],
	          y: [0.174]
	        },
	        t: 14,
	        s: [-7.883]
	      }, {
	        i: {
	          x: [0.833],
	          y: [0.884]
	        },
	        o: {
	          x: [0.167],
	          y: [0.197]
	        },
	        t: 15,
	        s: [-10.423]
	      }, {
	        i: {
	          x: [0.833],
	          y: [0.897]
	        },
	        o: {
	          x: [0.167],
	          y: [0.298]
	        },
	        t: 16,
	        s: [-12.28]
	      }, {
	        i: {
	          x: [0.833],
	          y: [0.865]
	        },
	        o: {
	          x: [0.167],
	          y: [0.43]
	        },
	        t: 17,
	        s: [-13]
	      }, {
	        i: {
	          x: [0.833],
	          y: [0.897]
	        },
	        o: {
	          x: [0.167],
	          y: [0.218]
	        },
	        t: 18,
	        s: [-13.173]
	      }, {
	        i: {
	          x: [0.833],
	          y: [1.079]
	        },
	        o: {
	          x: [0.167],
	          y: [0.432]
	        },
	        t: 19,
	        s: [-13.28]
	      }, {
	        i: {
	          x: [0.833],
	          y: [0.745]
	        },
	        o: {
	          x: [0.167],
	          y: [0.041]
	        },
	        t: 20,
	        s: [-13.305]
	      }, {
	        i: {
	          x: [0.833],
	          y: [0.816]
	        },
	        o: {
	          x: [0.167],
	          y: [0.124]
	        },
	        t: 21,
	        s: [-13.256]
	      }, {
	        i: {
	          x: [0.833],
	          y: [0.841]
	        },
	        o: {
	          x: [0.167],
	          y: [0.153]
	        },
	        t: 22,
	        s: [-13.153]
	      }, {
	        i: {
	          x: [0.833],
	          y: [0.86]
	        },
	        o: {
	          x: [0.167],
	          y: [0.175]
	        },
	        t: 23,
	        s: [-13.03]
	      }, {
	        i: {
	          x: [0.833],
	          y: [0.886]
	        },
	        o: {
	          x: [0.167],
	          y: [0.206]
	        },
	        t: 24,
	        s: [-12.918]
	      }, {
	        i: {
	          x: [0.833],
	          y: [0.976]
	        },
	        o: {
	          x: [0.167],
	          y: [0.311]
	        },
	        t: 25,
	        s: [-12.842]
	      }, {
	        i: {
	          x: [0.833],
	          y: [0.681]
	        },
	        o: {
	          x: [0.167],
	          y: [-0.035]
	        },
	        t: 26,
	        s: [-12.814]
	      }, {
	        i: {
	          x: [0.833],
	          y: [0.807]
	        },
	        o: {
	          x: [0.167],
	          y: [0.113]
	        },
	        t: 27,
	        s: [-12.834]
	      }, {
	        i: {
	          x: [0.833],
	          y: [0.836]
	        },
	        o: {
	          x: [0.167],
	          y: [0.147]
	        },
	        t: 28,
	        s: [-12.89]
	      }, {
	        i: {
	          x: [0.833],
	          y: [0.855]
	        },
	        o: {
	          x: [0.167],
	          y: [0.169]
	        },
	        t: 29,
	        s: [-12.963]
	      }, {
	        i: {
	          x: [0.833],
	          y: [0.878]
	        },
	        o: {
	          x: [0.167],
	          y: [0.196]
	        },
	        t: 30,
	        s: [-13.034]
	      }, {
	        i: {
	          x: [0.833],
	          y: [0.934]
	        },
	        o: {
	          x: [0.167],
	          y: [0.262]
	        },
	        t: 31,
	        s: [-13.087]
	      }, {
	        i: {
	          x: [0.833],
	          y: [-0.789]
	        },
	        o: {
	          x: [0.167],
	          y: [-0.311]
	        },
	        t: 32,
	        s: [-13.111]
	      }, {
	        i: {
	          x: [0.833],
	          y: [-0.358]
	        },
	        o: {
	          x: [0.167],
	          y: [0.087]
	        },
	        t: 33,
	        s: [-13.106]
	      }, {
	        i: {
	          x: [0.833],
	          y: [0.833]
	        },
	        o: {
	          x: [0.167],
	          y: [0.089]
	        },
	        t: 34,
	        s: [-13]
	      }, {
	        i: {
	          x: [0.833],
	          y: [0.833]
	        },
	        o: {
	          x: [0.167],
	          y: [0.167]
	        },
	        t: 35,
	        s: [-11.375]
	      }, {
	        i: {
	          x: [0.833],
	          y: [0.833]
	        },
	        o: {
	          x: [0.167],
	          y: [0.167]
	        },
	        t: 36,
	        s: [-9.75]
	      }, {
	        i: {
	          x: [0.833],
	          y: [0.833]
	        },
	        o: {
	          x: [0.167],
	          y: [0.167]
	        },
	        t: 37,
	        s: [-8.125]
	      }, {
	        i: {
	          x: [0.833],
	          y: [0.833]
	        },
	        o: {
	          x: [0.167],
	          y: [0.167]
	        },
	        t: 38,
	        s: [-6.5]
	      }, {
	        i: {
	          x: [0.833],
	          y: [0.833]
	        },
	        o: {
	          x: [0.167],
	          y: [0.167]
	        },
	        t: 39,
	        s: [-4.875]
	      }, {
	        i: {
	          x: [0.833],
	          y: [0.833]
	        },
	        o: {
	          x: [0.167],
	          y: [0.167]
	        },
	        t: 40,
	        s: [-3.25]
	      }, {
	        i: {
	          x: [0.833],
	          y: [0.824]
	        },
	        o: {
	          x: [0.167],
	          y: [0.167]
	        },
	        t: 41,
	        s: [-1.625]
	      }, {
	        i: {
	          x: [0.833],
	          y: [0.865]
	        },
	        o: {
	          x: [0.167],
	          y: [0.158]
	        },
	        t: 42,
	        s: [0]
	      }, {
	        i: {
	          x: [0.833],
	          y: [0.897]
	        },
	        o: {
	          x: [0.167],
	          y: [0.218]
	        },
	        t: 43,
	        s: [1.807]
	      }, {
	        i: {
	          x: [0.833],
	          y: [1.079]
	        },
	        o: {
	          x: [0.167],
	          y: [0.432]
	        },
	        t: 44,
	        s: [2.923]
	      }, {
	        i: {
	          x: [0.833],
	          y: [0.745]
	        },
	        o: {
	          x: [0.167],
	          y: [0.041]
	        },
	        t: 45,
	        s: [3.189]
	      }, {
	        i: {
	          x: [0.833],
	          y: [0.816]
	        },
	        o: {
	          x: [0.167],
	          y: [0.124]
	        },
	        t: 46,
	        s: [2.669]
	      }, {
	        i: {
	          x: [0.833],
	          y: [0.841]
	        },
	        o: {
	          x: [0.167],
	          y: [0.153]
	        },
	        t: 47,
	        s: [1.601]
	      }, {
	        i: {
	          x: [0.833],
	          y: [0.86]
	        },
	        o: {
	          x: [0.167],
	          y: [0.175]
	        },
	        t: 48,
	        s: [0.315]
	      }, {
	        i: {
	          x: [0.833],
	          y: [0.886]
	        },
	        o: {
	          x: [0.167],
	          y: [0.206]
	        },
	        t: 49,
	        s: [-0.854]
	      }, {
	        i: {
	          x: [0.833],
	          y: [0.976]
	        },
	        o: {
	          x: [0.167],
	          y: [0.311]
	        },
	        t: 50,
	        s: [-1.651]
	      }, {
	        i: {
	          x: [0.833],
	          y: [0.681]
	        },
	        o: {
	          x: [0.167],
	          y: [-0.035]
	        },
	        t: 51,
	        s: [-1.942]
	      }, {
	        i: {
	          x: [0.833],
	          y: [0.807]
	        },
	        o: {
	          x: [0.167],
	          y: [0.113]
	        },
	        t: 52,
	        s: [-1.736]
	      }, {
	        i: {
	          x: [0.833],
	          y: [0.836]
	        },
	        o: {
	          x: [0.167],
	          y: [0.147]
	        },
	        t: 53,
	        s: [-1.153]
	      }, {
	        i: {
	          x: [0.833],
	          y: [0.855]
	        },
	        o: {
	          x: [0.167],
	          y: [0.169]
	        },
	        t: 54,
	        s: [-0.387]
	      }, {
	        i: {
	          x: [0.833],
	          y: [0.878]
	        },
	        o: {
	          x: [0.167],
	          y: [0.196]
	        },
	        t: 55,
	        s: [0.357]
	      }, {
	        i: {
	          x: [0.833],
	          y: [0.934]
	        },
	        o: {
	          x: [0.167],
	          y: [0.262]
	        },
	        t: 56,
	        s: [0.907]
	      }, {
	        i: {
	          x: [0.833],
	          y: [0.446]
	        },
	        o: {
	          x: [0.167],
	          y: [-0.311]
	        },
	        t: 57,
	        s: [1.164]
	      }, {
	        i: {
	          x: [0.833],
	          y: [0.833]
	        },
	        o: {
	          x: [0.167],
	          y: [0.098]
	        },
	        t: 58,
	        s: [1.11]
	      }, {
	        t: 59,
	        s: [0.803]
	      }],
	      ix: 10
	    },
	    p: {
	      a: 1,
	      k: [{
	        i: {
	          x: 0.833,
	          y: 0.833
	        },
	        o: {
	          x: 0.167,
	          y: 0.167
	        },
	        t: 0,
	        s: [20.644, 34, 0],
	        to: [0, 0, 0],
	        ti: [0, 0, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.833
	        },
	        o: {
	          x: 0.167,
	          y: 0.167
	        },
	        t: 1,
	        s: [20.644, 34, 0],
	        to: [0, 0, 0],
	        ti: [0, 0, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.833
	        },
	        o: {
	          x: 0.167,
	          y: 0.167
	        },
	        t: 2,
	        s: [20.644, 34, 0],
	        to: [0, 0, 0],
	        ti: [0, 0, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.833
	        },
	        o: {
	          x: 0.167,
	          y: 0.167
	        },
	        t: 3,
	        s: [20.644, 34, 0],
	        to: [0, 0, 0],
	        ti: [0, 0, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.833
	        },
	        o: {
	          x: 0.167,
	          y: 0.167
	        },
	        t: 4,
	        s: [20.644, 34, 0],
	        to: [0, 0, 0],
	        ti: [0, 0, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.833
	        },
	        o: {
	          x: 0.167,
	          y: 0.167
	        },
	        t: 5,
	        s: [20.644, 34, 0],
	        to: [0, 0, 0],
	        ti: [0, 0, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.833
	        },
	        o: {
	          x: 0.167,
	          y: 0.167
	        },
	        t: 6,
	        s: [20.644, 34, 0],
	        to: [0, 0, 0],
	        ti: [0, 0, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.833
	        },
	        o: {
	          x: 0.167,
	          y: 0.167
	        },
	        t: 7,
	        s: [20.644, 34, 0],
	        to: [0, 0, 0],
	        ti: [0, 0, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.833
	        },
	        o: {
	          x: 0.167,
	          y: 0.167
	        },
	        t: 8,
	        s: [20.644, 34, 0],
	        to: [0, 0, 0],
	        ti: [0, 0, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.833
	        },
	        o: {
	          x: 0.167,
	          y: 0.167
	        },
	        t: 9,
	        s: [20.644, 34, 0],
	        to: [0, 0, 0],
	        ti: [0, 0, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.703
	        },
	        o: {
	          x: 0.167,
	          y: 0.083
	        },
	        t: 10,
	        s: [20.644, 34, 0],
	        to: [0.003, 0.042, 0],
	        ti: [-0.012, -0.149, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.803
	        },
	        o: {
	          x: 0.167,
	          y: 0.116
	        },
	        t: 11,
	        s: [20.665, 34.251, 0],
	        to: [0.012, 0.149, 0],
	        ti: [-0.021, -0.253, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.826
	        },
	        o: {
	          x: 0.167,
	          y: 0.144
	        },
	        t: 12,
	        s: [20.719, 34.895, 0],
	        to: [0.021, 0.253, 0],
	        ti: [-0.025, -0.306, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.84
	        },
	        o: {
	          x: 0.167,
	          y: 0.16
	        },
	        t: 13,
	        s: [20.792, 35.772, 0],
	        to: [0.025, 0.306, 0],
	        ti: [-0.025, -0.306, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.856
	        },
	        o: {
	          x: 0.167,
	          y: 0.174
	        },
	        t: 14,
	        s: [20.871, 36.728, 0],
	        to: [0.025, 0.306, 0],
	        ti: [-0.021, -0.254, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.884
	        },
	        o: {
	          x: 0.167,
	          y: 0.197
	        },
	        t: 15,
	        s: [20.945, 37.607, 0],
	        to: [0.021, 0.254, 0],
	        ti: [-0.012, -0.149, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.897
	        },
	        o: {
	          x: 0.167,
	          y: 0.297
	        },
	        t: 16,
	        s: [20.998, 38.249, 0],
	        to: [0.012, 0.149, 0],
	        ti: [-0.004, -0.052, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.865
	        },
	        o: {
	          x: 0.167,
	          y: 0.44
	        },
	        t: 17,
	        s: [21.019, 38.5, 0],
	        to: [0.004, 0.052, 0],
	        ti: [-0.001, -0.016, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.895
	        },
	        o: {
	          x: 0.167,
	          y: 0.218
	        },
	        t: 18,
	        s: [21.024, 38.559, 0],
	        to: [0.001, 0.016, 0],
	        ti: [-0.001, -0.007, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.759
	        },
	        o: {
	          x: 0.167,
	          y: 0.419
	        },
	        t: 19,
	        s: [21.027, 38.595, 0],
	        to: [0.001, 0.007, 0],
	        ti: [0, 0.001, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.746
	        },
	        o: {
	          x: 0.167,
	          y: 0.13
	        },
	        t: 20,
	        s: [21.028, 38.604, 0],
	        to: [0, -0.001, 0],
	        ti: [0.001, 0.009, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.816
	        },
	        o: {
	          x: 0.167,
	          y: 0.124
	        },
	        t: 21,
	        s: [21.026, 38.587, 0],
	        to: [-0.001, -0.009, 0],
	        ti: [0.001, 0.013, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.841
	        },
	        o: {
	          x: 0.167,
	          y: 0.153
	        },
	        t: 22,
	        s: [21.023, 38.552, 0],
	        to: [-0.001, -0.013, 0],
	        ti: [0.001, 0.013, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.86
	        },
	        o: {
	          x: 0.167,
	          y: 0.175
	        },
	        t: 23,
	        s: [21.02, 38.51, 0],
	        to: [-0.001, -0.013, 0],
	        ti: [0.001, 0.011, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.886
	        },
	        o: {
	          x: 0.167,
	          y: 0.206
	        },
	        t: 24,
	        s: [21.017, 38.473, 0],
	        to: [-0.001, -0.011, 0],
	        ti: [0, 0.006, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.857
	        },
	        o: {
	          x: 0.167,
	          y: 0.31
	        },
	        t: 25,
	        s: [21.015, 38.447, 0],
	        to: [0, -0.006, 0],
	        ti: [0, 0, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.681
	        },
	        o: {
	          x: 0.167,
	          y: 0.202
	        },
	        t: 26,
	        s: [21.014, 38.437, 0],
	        to: [0, 0, 0],
	        ti: [0, -0.004, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.807
	        },
	        o: {
	          x: 0.167,
	          y: 0.113
	        },
	        t: 27,
	        s: [21.014, 38.444, 0],
	        to: [0, 0.004, 0],
	        ti: [-0.001, -0.007, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.836
	        },
	        o: {
	          x: 0.167,
	          y: 0.147
	        },
	        t: 28,
	        s: [21.016, 38.463, 0],
	        to: [0.001, 0.007, 0],
	        ti: [-0.001, -0.008, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.855
	        },
	        o: {
	          x: 0.167,
	          y: 0.169
	        },
	        t: 29,
	        s: [21.018, 38.488, 0],
	        to: [0.001, 0.008, 0],
	        ti: [-0.001, -0.007, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.878
	        },
	        o: {
	          x: 0.167,
	          y: 0.196
	        },
	        t: 30,
	        s: [21.02, 38.512, 0],
	        to: [0.001, 0.007, 0],
	        ti: [0, -0.004, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.861
	        },
	        o: {
	          x: 0.167,
	          y: 0.262
	        },
	        t: 31,
	        s: [21.022, 38.53, 0],
	        to: [0, 0.004, 0],
	        ti: [0, -0.001, 0]
	      }, {
	        i: {
	          x: 0.864,
	          y: 0
	        },
	        o: {
	          x: 0.167,
	          y: 0.216
	        },
	        t: 32,
	        s: [21.022, 38.538, 0],
	        to: [0, 0.001, 0],
	        ti: [0.001, 0.006, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.272
	        },
	        o: {
	          x: 0.167,
	          y: 0.09
	        },
	        t: 33,
	        s: [21.022, 38.536, 0],
	        to: [-0.001, -0.006, 0],
	        ti: [0.008, 0.1, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.834
	        },
	        o: {
	          x: 0.167,
	          y: 0.094
	        },
	        t: 34,
	        s: [21.019, 38.5, 0],
	        to: [-0.008, -0.1, 0],
	        ti: [0.016, 0.188, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.833
	        },
	        o: {
	          x: 0.167,
	          y: 0.168
	        },
	        t: 35,
	        s: [20.972, 37.934, 0],
	        to: [-0.016, -0.188, 0],
	        ti: [0.016, 0.187, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.834
	        },
	        o: {
	          x: 0.167,
	          y: 0.166
	        },
	        t: 36,
	        s: [20.925, 37.375, 0],
	        to: [-0.016, -0.187, 0],
	        ti: [0.016, 0.188, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.833
	        },
	        o: {
	          x: 0.167,
	          y: 0.167
	        },
	        t: 37,
	        s: [20.878, 36.812, 0],
	        to: [-0.016, -0.188, 0],
	        ti: [0.016, 0.187, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.833
	        },
	        o: {
	          x: 0.167,
	          y: 0.167
	        },
	        t: 38,
	        s: [20.832, 36.25, 0],
	        to: [-0.016, -0.187, 0],
	        ti: [0.016, 0.187, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.834
	        },
	        o: {
	          x: 0.167,
	          y: 0.167
	        },
	        t: 39,
	        s: [20.785, 35.688, 0],
	        to: [-0.016, -0.187, 0],
	        ti: [0.016, 0.187, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.832
	        },
	        o: {
	          x: 0.167,
	          y: 0.167
	        },
	        t: 40,
	        s: [20.738, 35.125, 0],
	        to: [-0.016, -0.187, 0],
	        ti: [0.016, 0.188, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.825
	        },
	        o: {
	          x: 0.167,
	          y: 0.165
	        },
	        t: 41,
	        s: [20.691, 34.568, 0],
	        to: [-0.016, -0.188, 0],
	        ti: [0.017, 0.199, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.865
	        },
	        o: {
	          x: 0.167,
	          y: 0.159
	        },
	        t: 42,
	        s: [20.644, 34, 0],
	        to: [-0.017, -0.199, 0],
	        ti: [0.014, 0.169, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.895
	        },
	        o: {
	          x: 0.167,
	          y: 0.218
	        },
	        t: 43,
	        s: [20.592, 33.375, 0],
	        to: [-0.014, -0.169, 0],
	        ti: [0.007, 0.08, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.767
	        },
	        o: {
	          x: 0.167,
	          y: 0.405
	        },
	        t: 44,
	        s: [20.56, 32.989, 0],
	        to: [-0.007, -0.08, 0],
	        ti: [-0.001, -0.015, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.745
	        },
	        o: {
	          x: 0.167,
	          y: 0.13
	        },
	        t: 45,
	        s: [20.552, 32.897, 0],
	        to: [0.001, 0.015, 0],
	        ti: [-0.008, -0.092, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.816
	        },
	        o: {
	          x: 0.167,
	          y: 0.124
	        },
	        t: 46,
	        s: [20.567, 33.077, 0],
	        to: [0.008, 0.092, 0],
	        ti: [-0.011, -0.136, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.841
	        },
	        o: {
	          x: 0.167,
	          y: 0.153
	        },
	        t: 47,
	        s: [20.598, 33.446, 0],
	        to: [0.011, 0.136, 0],
	        ti: [-0.012, -0.142, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.86
	        },
	        o: {
	          x: 0.167,
	          y: 0.175
	        },
	        t: 48,
	        s: [20.635, 33.891, 0],
	        to: [0.012, 0.142, 0],
	        ti: [-0.009, -0.113, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.886
	        },
	        o: {
	          x: 0.167,
	          y: 0.206
	        },
	        t: 49,
	        s: [20.669, 34.296, 0],
	        to: [0.009, 0.113, 0],
	        ti: [-0.005, -0.063, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.857
	        },
	        o: {
	          x: 0.167,
	          y: 0.311
	        },
	        t: 50,
	        s: [20.692, 34.571, 0],
	        to: [0.005, 0.063, 0],
	        ti: [0, -0.005, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.685
	        },
	        o: {
	          x: 0.167,
	          y: 0.199
	        },
	        t: 51,
	        s: [20.7, 34.672, 0],
	        to: [0, 0.005, 0],
	        ti: [0.004, 0.045, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.807
	        },
	        o: {
	          x: 0.167,
	          y: 0.113
	        },
	        t: 52,
	        s: [20.694, 34.601, 0],
	        to: [-0.004, -0.045, 0],
	        ti: [0.006, 0.078, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.836
	        },
	        o: {
	          x: 0.167,
	          y: 0.147
	        },
	        t: 53,
	        s: [20.677, 34.399, 0],
	        to: [-0.006, -0.078, 0],
	        ti: [0.007, 0.087, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.855
	        },
	        o: {
	          x: 0.167,
	          y: 0.169
	        },
	        t: 54,
	        s: [20.655, 34.134, 0],
	        to: [-0.007, -0.087, 0],
	        ti: [0.006, 0.075, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.878
	        },
	        o: {
	          x: 0.167,
	          y: 0.196
	        },
	        t: 55,
	        s: [20.634, 33.877, 0],
	        to: [-0.006, -0.075, 0],
	        ti: [0.004, 0.047, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.89
	        },
	        o: {
	          x: 0.167,
	          y: 0.262
	        },
	        t: 56,
	        s: [20.618, 33.686, 0],
	        to: [-0.004, -0.047, 0],
	        ti: [0.001, 0.012, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.615
	        },
	        o: {
	          x: 0.167,
	          y: 0.335
	        },
	        t: 57,
	        s: [20.611, 33.598, 0],
	        to: [-0.001, -0.012, 0],
	        ti: [-0.002, -0.021, 0]
	      }, {
	        i: {
	          x: 0.833,
	          y: 0.833
	        },
	        o: {
	          x: 0.167,
	          y: 0.106
	        },
	        t: 58,
	        s: [20.612, 33.616, 0],
	        to: [0.002, 0.021, 0],
	        ti: [-0.001, -0.018, 0]
	      }, {
	        t: 59,
	        s: [20.621, 33.722, 0]
	      }],
	      ix: 2,
	      l: 2
	    },
	    a: {
	      a: 0,
	      k: [19.644, 36, 0],
	      ix: 1,
	      l: 2
	    },
	    s: {
	      a: 0,
	      k: [100, 100, 100],
	      ix: 6,
	      l: 2
	    }
	  },
	  ao: 0,
	  shapes: [{
	    ty: "gr",
	    it: [{
	      ty: "gr",
	      it: [{
	        ind: 0,
	        ty: "sh",
	        ix: 1,
	        ks: {
	          a: 0,
	          k: {
	            i: [[0.869, -0.456], [-0.593, -0.701], [0, 0], [0, 0], [0, 0], [0.402, -0.32], [0.309, 0.305], [0, 0], [0, 0], [0, 0], [0.178, 0.143], [0, 0], [0.628, -0.629], [-0.518, -0.58], [0, 0], [0, 0], [0, 0], [0, 0], [0.133, -0.141], [0.248, 0.131], [0, 0], [0, 0], [0.529, -0.53], [-0.41, -0.685], [0, 0], [0, 0], [0, 0], [-1.492, -1.606], [0, 0], [0, 0], [0.19, -0.169], [0.155, 0.066], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0.163, -0.132], [-0.511, -0.888], [0, 0], [-1.412, -1.566], [0, 0], [-0.605, -1.17], [0, 0], [-2.284, 0], [-2.755, 3.049], [0, 0], [0, 0], [0.004, 0.391], [0, 0], [-1.496, 4.072], [0, 0], [0, 0], [1.957, -1.789], [0.973, -1.558], [0, 0], [0, 0], [0.675, -0.021], [0.499, 0.435], [0, 0], [0, 0]],
	            o: [[-0.874, 0.458], [0, 0], [0, 0], [0, 0], [0.131, 0.159], [-0.289, 0.23], [0, 0], [0, 0], [0, 0], [-0.491, -0.472], [0, 0], [-0.623, -0.397], [-0.629, 0.628], [0, 0], [0, 0], [0, 0], [0, 0], [0.24, 0.271], [-0.119, 0.127], [0, 0], [0, 0], [-0.33, -0.3], [-0.61, 0.609], [0, 0], [0, 0], [0, 0], [0.529, 0.624], [0, 0], [0, 0], [0.134, 0.133], [-0.168, 0.15], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [-0.359, -0.357], [-0.552, 0.446], [0, 0], [0.692, 1.085], [0, 0], [1.016, 1.126], [0, 0], [1.935, 0.825], [4.317, 0], [0, 0], [0, 0], [-0.069, -0.638], [0, 0], [0.052, -0.963], [0, 0], [0, 0], [-0.051, -0.397], [-0.832, 0.76], [0, 0], [0, 0], [-0.281, 0.477], [-0.618, 0.019], [0, 0], [0, 0], [-0.699, -0.635]],
	            v: [[-7.988, -12.021], [-8.379, -10.314], [-8.279, -10.202], [-1.107, -2.879], [-1.078, -2.847], [-1.196, -1.992], [-2.093, -2.105], [-6.427, -6.486], [-8.886, -8.929], [-9.551, -9.573], [-10.555, -10.496], [-10.655, -10.57], [-12.527, -10.347], [-12.628, -7.97], [-12.232, -7.561], [-11.103, -6.429], [-4.312, 0.304], [-3.596, 1.025], [-3.571, 1.823], [-4.303, 1.935], [-4.381, 1.881], [-12.288, -5.995], [-13.726, -5.697], [-13.747, -3.375], [-13.694, -3.295], [-13.607, -3.179], [-13.336, -2.851], [-10.304, 0.494], [-8.537, 2.386], [-6.348, 4.711], [-6.332, 5.425], [-6.99, 5.603], [-7.038, 5.571], [-11.603, 0.734], [-11.799, 0.532], [-12.332, -0.038], [-12.53, -0.241], [-13.379, -0.722], [-13.364, 1.832], [-13.231, 2.05], [-8.994, 7.552], [-8.577, 8.009], [-6.332, 11.177], [-6.521, 11.101], [-0.15, 12.387], [10.884, 7.387], [10.85, 7.126], [10.784, 6.55], [10.676, 5.008], [10.679, 4.779], [13.002, -2.773], [13.457, -3.998], [14.372, -6.409], [11.525, -6.687], [8.965, -3.364], [8.563, -2.711], [7.981, -1.734], [6.737, -0.833], [4.981, -1.612], [4.848, -1.732], [-5.48, -11.51]],
	            c: true
	          },
	          ix: 2
	        },
	        nm: "Path 1",
	        mn: "ADBE Vector Shape - Group",
	        hd: false
	      }, {
	        ind: 1,
	        ty: "sh",
	        ix: 2,
	        ks: {
	          a: 0,
	          k: {
	            i: [[0, 0], [0, 0], [0, 0], [0.998, -0.731], [0, 0], [0.159, -0.103], [0, 0], [0.348, -0.186], [0.182, -0.088], [0.517, -0.192], [0.85, -0.169], [0, 0], [0, 0], [0, 0], [0.527, -0.013], [0, 0], [0.21, 0.007], [0.368, 0.035], [0, 0], [0, 0], [0, 0], [0.282, 0.06], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [1.119, 1.241], [0, 0], [0, 0], [0, 0], [0, 0], [0.47, 0.817], [-1.092, 1.029], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [-1.122, 1.122], [-0.177, 0.121], [0, 0], [0, 0], [0, 0], [-0.788, 1.044], [0, 0], [0, 0], [-1.101, -0.464], [0, 0], [0, 0], [0, 0], [-0.457, 0.309], [0, 0], [0, 0], [-1.173, -1.078], [0, 0], [0, 0], [-0.165, -0.075], [0, 0], [0, 0], [0, 0], [-1.078, 0.985], [-1.187, -0.627], [-0.153, -0.722], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0.047, -0.719], [0, 0], [-0.069, -0.65]],
	            o: [[0, 0], [0, 0], [-0.819, 0.926], [0, 0], [-0.156, 0.109], [0, 0], [-0.335, 0.21], [-0.178, 0.096], [-0.492, 0.242], [-0.803, 0.298], [0, 0], [0, 0], [0, 0], [-0.516, 0.064], [0, 0], [-0.212, 0], [-0.375, -0.012], [0, 0], [0, 0], [0, 0], [-0.287, -0.047], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [-0.688, -1.48], [0, 0], [0, 0], [0, 0], [0, 0], [-1.336, -1.65], [-0.82, -1.424], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [-0.794, -1.329], [0.159, -0.159], [0, 0], [0, 0], [0, 0], [-0.636, -1.1], [0, 0], [0, 0], [0.949, -0.949], [0, 0], [0, 0], [0, 0], [0.193, -0.405], [0, 0], [0, 0], [1.444, -0.758], [0, 0], [0, 0], [0.226, 0.213], [0, 0], [0, 0], [0, 0], [1.444, -2.409], [1.295, -1.183], [0.777, 0.41], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [-1.284, 3.531], [0, 0], [-0.005, 0.348], [0, 0]],
	            v: [[12.293, 6.371], [12.589, 8.059], [12.297, 8.399], [9.562, 10.895], [9.255, 11.115], [8.783, 11.433], [8.56, 11.576], [7.535, 12.171], [6.995, 12.447], [5.48, 13.098], [2.998, 13.801], [2.618, 13.872], [2.235, 13.933], [1.85, 13.986], [0.286, 14.102], [-0.11, 14.107], [-0.744, 14.096], [-1.86, 14.025], [-2.108, 14], [-2.634, 13.935], [-3.068, 13.87], [-3.922, 13.71], [-4.35, 13.614], [-4.806, 13.499], [-5.248, 13.375], [-5.597, 13.269], [-5.982, 13.142], [-6.167, 13.078], [-6.597, 12.919], [-6.941, 12.783], [-6.945, 12.757], [-7.302, 12.607], [-9.687, 9.017], [-10.273, 8.371], [-10.627, 7.961], [-10.988, 7.53], [-11.364, 7.07], [-14.664, 2.58], [-14.458, -1.77], [-14.414, -1.809], [-14.687, -2.137], [-14.847, -2.339], [-14.966, -2.5], [-15.035, -2.605], [-14.787, -6.758], [-14.281, -7.178], [-14.101, -7.291], [-14.02, -7.337], [-14.082, -7.436], [-13.854, -11.103], [-13.723, -11.263], [-13.588, -11.408], [-10.376, -12.113], [-10.193, -12.03], [-10.073, -11.967], [-10.041, -12.04], [-9.064, -13.124], [-8.86, -13.252], [-8.685, -13.349], [-4.477, -12.627], [-4.327, -12.483], [5.878, -2.823], [6.496, -2.386], [6.573, -2.356], [6.599, -2.349], [7.077, -3.151], [10.513, -7.795], [14.364, -8.694], [15.832, -6.768], [15.86, -6.603], [15.909, -6.226], [15.172, -4.295], [14.598, -2.76], [14.195, -1.661], [12.178, 4.816], [12.176, 4.877], [12.273, 6.376]],
	            c: true
	          },
	          ix: 2
	        },
	        nm: "Path 2",
	        mn: "ADBE Vector Shape - Group",
	        hd: false
	      }, {
	        ty: "mm",
	        mm: 1,
	        nm: "Merge Paths 1",
	        mn: "ADBE Vector Filter - Merge",
	        hd: false
	      }, {
	        ty: "fl",
	        c: {
	          a: 0,
	          k: [0.823529481888, 0.607843160629, 0.235294133425, 1],
	          ix: 4
	        },
	        o: {
	          a: 0,
	          k: 100,
	          ix: 5
	        },
	        r: 1,
	        bm: 0,
	        nm: "Fill 1",
	        mn: "ADBE Vector Graphic - Fill",
	        hd: false
	      }, {
	        ty: "tr",
	        p: {
	          a: 0,
	          k: [19.505, 21.893],
	          ix: 2
	        },
	        a: {
	          a: 0,
	          k: [0, 0],
	          ix: 1
	        },
	        s: {
	          a: 0,
	          k: [100, 100],
	          ix: 3
	        },
	        r: {
	          a: 0,
	          k: 0,
	          ix: 6
	        },
	        o: {
	          a: 0,
	          k: 100,
	          ix: 7
	        },
	        sk: {
	          a: 0,
	          k: 0,
	          ix: 4
	        },
	        sa: {
	          a: 0,
	          k: 0,
	          ix: 5
	        },
	        nm: "Transform"
	      }],
	      nm: "Group 1",
	      np: 4,
	      cix: 2,
	      bm: 0,
	      ix: 1,
	      mn: "ADBE Vector Group",
	      hd: false
	    }, {
	      ty: "tr",
	      p: {
	        a: 0,
	        k: [19.644, 22.077],
	        ix: 2
	      },
	      a: {
	        a: 0,
	        k: [19.644, 22.077],
	        ix: 1
	      },
	      s: {
	        a: 0,
	        k: [100, 100],
	        ix: 3
	      },
	      r: {
	        a: 0,
	        k: 0,
	        ix: 6
	      },
	      o: {
	        a: 0,
	        k: 100,
	        ix: 7
	      },
	      sk: {
	        a: 0,
	        k: 0,
	        ix: 4
	      },
	      sa: {
	        a: 0,
	        k: 0,
	        ix: 5
	      },
	      nm: "Transform"
	    }],
	    nm: "Group 1",
	    np: 1,
	    cix: 2,
	    bm: 0,
	    ix: 1,
	    mn: "ADBE Vector Group",
	    hd: false
	  }, {
	    ty: "gr",
	    it: [{
	      ty: "gr",
	      it: [{
	        ind: 0,
	        ty: "sh",
	        ix: 1,
	        ks: {
	          a: 0,
	          k: {
	            i: [[-0.92, 0.483], [-0.699, -0.635], [0, 0], [-0.675, 0.021], [-0.262, 0.428], [0, 0], [-0.992, 0.906], [-0.051, -0.397], [0, 0], [0.013, -1.034], [-0.352, -2.247], [4.315, 0], [1.646, 0.494], [0.093, 0.213], [0, 0], [1.333, 1.477], [0, 0], [0.511, 0.888], [-0.552, 0.446], [-0.593, -0.593], [0, 0], [-0.189, 0.169], [0.134, 0.133], [0, 0], [0.192, 0.321], [-0.61, 0.61], [-0.33, -0.3], [0, 0], [-0.217, -0.188], [-0.132, 0.142], [0.24, 0.271], [0, 0], [0.45, 0.505], [-0.629, 0.628], [-0.623, -0.397], [0, 0], [-4.981, -5.063], [-0.289, 0.23], [0.131, 0.159], [0, 0], [0, 0]],
	            o: [[0.869, -0.456], [0, 0], [0.515, 0.485], [0.623, -0.019], [0, 0], [1.274, -2.126], [1.957, -1.789], [0, 0], [-2.035, 5.386], [-0.011, 0.853], [-2.99, 2.818], [-1.835, 0], [-0.08, -0.199], [0, 0], [-0.829, -1.85], [0, 0], [-1.509, -1.632], [-0.511, -0.888], [0.305, -0.247], [0, 0], [0.117, 0.121], [0.19, -0.169], [0, 0], [-2.355, -2.528], [-0.409, -0.685], [0.529, -0.529], [0, 0], [1.449, 1.431], [0.241, 0.211], [0.133, -0.141], [0, 0], [-1.791, -1.787], [-0.518, -0.579], [0.628, -0.629], [0, 0], [0.661, 0.532], [0.309, 0.305], [0.402, -0.32], [0, 0], [0, 0], [-0.702, -0.756]],
	            v: [[-8.006, -12.822], [-5.498, -12.311], [4.83, -2.534], [6.719, -1.635], [7.897, -2.428], [8.357, -3.201], [11.507, -7.488], [14.354, -7.21], [13.73, -5.572], [10.658, 4.057], [11.169, 8.708], [-0.134, 13.279], [-5.374, 12.516], [-5.632, 11.902], [-5.776, 11.574], [-8.595, 7.208], [-8.853, 6.926], [-13.382, 1.031], [-13.397, -1.523], [-11.621, -0.068], [-7.056, 4.77], [-6.35, 4.624], [-6.366, 3.91], [-9.946, 0.096], [-13.766, -4.176], [-13.744, -6.499], [-12.306, -6.796], [-12.001, -6.499], [-4.399, 1.079], [-3.589, 1.021], [-3.614, 0.223], [-4.078, -0.245], [-12.646, -8.773], [-12.545, -11.148], [-10.673, -11.372], [-10.574, -11.299], [-2.111, -2.906], [-1.214, -2.794], [-1.096, -3.648], [-1.125, -3.68], [-8.297, -11.003]],
	            c: true
	          },
	          ix: 2
	        },
	        nm: "Path 1",
	        mn: "ADBE Vector Shape - Group",
	        hd: false
	      }, {
	        ty: "fl",
	        c: {
	          a: 0,
	          k: [1, 1, 1, 1],
	          ix: 4
	        },
	        o: {
	          a: 0,
	          k: 100,
	          ix: 5
	        },
	        r: 1,
	        bm: 0,
	        nm: "Fill 1",
	        mn: "ADBE Vector Graphic - Fill",
	        hd: false
	      }, {
	        ty: "tr",
	        p: {
	          a: 0,
	          k: [19.481, 22.593],
	          ix: 2
	        },
	        a: {
	          a: 0,
	          k: [0, 0],
	          ix: 1
	        },
	        s: {
	          a: 0,
	          k: [100, 100],
	          ix: 3
	        },
	        r: {
	          a: 0,
	          k: 0,
	          ix: 6
	        },
	        o: {
	          a: 0,
	          k: 100,
	          ix: 7
	        },
	        sk: {
	          a: 0,
	          k: 0,
	          ix: 4
	        },
	        sa: {
	          a: 0,
	          k: 0,
	          ix: 5
	        },
	        nm: "Transform"
	      }],
	      nm: "Group 2",
	      np: 2,
	      cix: 2,
	      bm: 0,
	      ix: 1,
	      mn: "ADBE Vector Group",
	      hd: false
	    }, {
	      ty: "tr",
	      p: {
	        a: 0,
	        k: [19.588, 22.715],
	        ix: 2
	      },
	      a: {
	        a: 0,
	        k: [19.588, 22.715],
	        ix: 1
	      },
	      s: {
	        a: 0,
	        k: [100, 100],
	        ix: 3
	      },
	      r: {
	        a: 0,
	        k: 0,
	        ix: 6
	      },
	      o: {
	        a: 0,
	        k: 100,
	        ix: 7
	      },
	      sk: {
	        a: 0,
	        k: 0,
	        ix: 4
	      },
	      sa: {
	        a: 0,
	        k: 0,
	        ix: 5
	      },
	      nm: "Transform"
	    }],
	    nm: "Group 2",
	    np: 1,
	    cix: 2,
	    bm: 0,
	    ix: 2,
	    mn: "ADBE Vector Group",
	    hd: false
	  }],
	  ip: 0,
	  op: 60,
	  st: 0,
	  bm: 0
	}, {
	  ddd: 0,
	  ind: 2,
	  ty: 4,
	  nm: "Group 7",
	  sr: 1,
	  ks: {
	    o: {
	      a: 0,
	      k: 100,
	      ix: 11
	    },
	    r: {
	      a: 0,
	      k: 0,
	      ix: 10
	    },
	    p: {
	      a: 1,
	      k: [{
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: 10,
	        s: [13.129, 19.446, 0],
	        to: [0, -1.146, 0],
	        ti: [0, 1.146, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 0.667
	        },
	        o: {
	          x: 0.333,
	          y: 0.333
	        },
	        t: 17,
	        s: [13.129, 12.571, 0],
	        to: [0, 0, 0],
	        ti: [0, 0, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: 37,
	        s: [13.129, 12.571, 0],
	        to: [0, 1.208, 0],
	        ti: [0, -1.208, 0]
	      }, {
	        t: 42,
	        s: [13.129, 19.821, 0]
	      }],
	      ix: 2,
	      l: 2
	    },
	    a: {
	      a: 0,
	      k: [10.629, 12.321, 0],
	      ix: 1,
	      l: 2
	    },
	    s: {
	      a: 0,
	      k: [100, 100, 100],
	      ix: 6,
	      l: 2
	    }
	  },
	  ao: 0,
	  shapes: [{
	    ty: "gr",
	    it: [{
	      ty: "gr",
	      it: [{
	        d: 1,
	        ty: "el",
	        s: {
	          a: 0,
	          k: [5, 5],
	          ix: 2
	        },
	        p: {
	          a: 0,
	          k: [0, 0],
	          ix: 3
	        },
	        nm: "Ellipse Path 1",
	        mn: "ADBE Vector Shape - Ellipse",
	        hd: false
	      }, {
	        ty: "fl",
	        c: {
	          a: 0,
	          k: [0.47058826685, 0.243137270212, 0.066666670144, 1],
	          ix: 4
	        },
	        o: {
	          a: 0,
	          k: 100,
	          ix: 5
	        },
	        r: 1,
	        bm: 0,
	        nm: "Fill 1",
	        mn: "ADBE Vector Graphic - Fill",
	        hd: false
	      }, {
	        ty: "tr",
	        p: {
	          a: 0,
	          k: [10.629, 12.321],
	          ix: 2
	        },
	        a: {
	          a: 0,
	          k: [0, 0],
	          ix: 1
	        },
	        s: {
	          a: 0,
	          k: [100, 100],
	          ix: 3
	        },
	        r: {
	          a: 0,
	          k: 0,
	          ix: 6
	        },
	        o: {
	          a: 0,
	          k: 100,
	          ix: 7
	        },
	        sk: {
	          a: 0,
	          k: 0,
	          ix: 4
	        },
	        sa: {
	          a: 0,
	          k: 0,
	          ix: 5
	        },
	        nm: "Transform"
	      }],
	      nm: "Group 3",
	      np: 2,
	      cix: 2,
	      bm: 0,
	      ix: 1,
	      mn: "ADBE Vector Group",
	      hd: false
	    }, {
	      ty: "tr",
	      p: {
	        a: 0,
	        k: [10.629, 12.321],
	        ix: 2
	      },
	      a: {
	        a: 0,
	        k: [10.629, 12.321],
	        ix: 1
	      },
	      s: {
	        a: 0,
	        k: [100, 100],
	        ix: 3
	      },
	      r: {
	        a: 0,
	        k: 0,
	        ix: 6
	      },
	      o: {
	        a: 0,
	        k: 100,
	        ix: 7
	      },
	      sk: {
	        a: 0,
	        k: 0,
	        ix: 4
	      },
	      sa: {
	        a: 0,
	        k: 0,
	        ix: 5
	      },
	      nm: "Transform"
	    }],
	    nm: "Group 3",
	    np: 1,
	    cix: 2,
	    bm: 0,
	    ix: 1,
	    mn: "ADBE Vector Group",
	    hd: false
	  }],
	  ip: 0,
	  op: 60,
	  st: 0,
	  bm: 0
	}, {
	  ddd: 0,
	  ind: 3,
	  ty: 4,
	  nm: "Group 6",
	  sr: 1,
	  ks: {
	    o: {
	      a: 0,
	      k: 100,
	      ix: 11
	    },
	    r: {
	      a: 0,
	      k: 0,
	      ix: 10
	    },
	    p: {
	      a: 1,
	      k: [{
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: 10,
	        s: [25.712, 19.446, 0],
	        to: [0, -1.146, 0],
	        ti: [0, 1.146, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 0.667
	        },
	        o: {
	          x: 0.333,
	          y: 0.333
	        },
	        t: 17,
	        s: [25.712, 12.571, 0],
	        to: [0, 0, 0],
	        ti: [0, 0, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: 37,
	        s: [25.712, 12.571, 0],
	        to: [0, 1.208, 0],
	        ti: [0, -1.208, 0]
	      }, {
	        t: 42,
	        s: [25.712, 19.821, 0]
	      }],
	      ix: 2,
	      l: 2
	    },
	    a: {
	      a: 0,
	      k: [23.212, 12.321, 0],
	      ix: 1,
	      l: 2
	    },
	    s: {
	      a: 0,
	      k: [100, 100, 100],
	      ix: 6,
	      l: 2
	    }
	  },
	  ao: 0,
	  shapes: [{
	    ty: "gr",
	    it: [{
	      ty: "gr",
	      it: [{
	        d: 1,
	        ty: "el",
	        s: {
	          a: 0,
	          k: [5, 5],
	          ix: 2
	        },
	        p: {
	          a: 0,
	          k: [0, 0],
	          ix: 3
	        },
	        nm: "Ellipse Path 1",
	        mn: "ADBE Vector Shape - Ellipse",
	        hd: false
	      }, {
	        ty: "fl",
	        c: {
	          a: 0,
	          k: [0.47058826685, 0.243137270212, 0.066666670144, 1],
	          ix: 4
	        },
	        o: {
	          a: 0,
	          k: 100,
	          ix: 5
	        },
	        r: 1,
	        bm: 0,
	        nm: "Fill 1",
	        mn: "ADBE Vector Graphic - Fill",
	        hd: false
	      }, {
	        ty: "tr",
	        p: {
	          a: 0,
	          k: [23.212, 12.321],
	          ix: 2
	        },
	        a: {
	          a: 0,
	          k: [0, 0],
	          ix: 1
	        },
	        s: {
	          a: 0,
	          k: [100, 100],
	          ix: 3
	        },
	        r: {
	          a: 0,
	          k: 0,
	          ix: 6
	        },
	        o: {
	          a: 0,
	          k: 100,
	          ix: 7
	        },
	        sk: {
	          a: 0,
	          k: 0,
	          ix: 4
	        },
	        sa: {
	          a: 0,
	          k: 0,
	          ix: 5
	        },
	        nm: "Transform"
	      }],
	      nm: "Group 2",
	      np: 2,
	      cix: 2,
	      bm: 0,
	      ix: 1,
	      mn: "ADBE Vector Group",
	      hd: false
	    }, {
	      ty: "tr",
	      p: {
	        a: 0,
	        k: [23.212, 12.321],
	        ix: 2
	      },
	      a: {
	        a: 0,
	        k: [23.212, 12.321],
	        ix: 1
	      },
	      s: {
	        a: 0,
	        k: [100, 100],
	        ix: 3
	      },
	      r: {
	        a: 0,
	        k: 0,
	        ix: 6
	      },
	      o: {
	        a: 0,
	        k: 100,
	        ix: 7
	      },
	      sk: {
	        a: 0,
	        k: 0,
	        ix: 4
	      },
	      sa: {
	        a: 0,
	        k: 0,
	        ix: 5
	      },
	      nm: "Transform"
	    }],
	    nm: "Group 2",
	    np: 1,
	    cix: 2,
	    bm: 0,
	    ix: 1,
	    mn: "ADBE Vector Group",
	    hd: false
	  }],
	  ip: 0,
	  op: 60,
	  st: 0,
	  bm: 0
	}, {
	  ddd: 0,
	  ind: 4,
	  ty: 4,
	  nm: "Group 2",
	  sr: 1,
	  ks: {
	    o: {
	      a: 0,
	      k: 100,
	      ix: 11
	    },
	    r: {
	      a: 0,
	      k: 0,
	      ix: 10
	    },
	    p: {
	      a: 1,
	      k: [{
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: 10,
	        s: [25.514, 12.161, 0],
	        to: [0, -0.854, 0],
	        ti: [0, 0.854, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 0.667
	        },
	        o: {
	          x: 0.333,
	          y: 0.333
	        },
	        t: 17,
	        s: [25.514, 7.036, 0],
	        to: [0, 0, 0],
	        ti: [0, 0, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: 37,
	        s: [25.514, 7.036, 0],
	        to: [0, 0.854, 0],
	        ti: [0, -0.854, 0]
	      }, {
	        t: 42,
	        s: [25.514, 12.161, 0]
	      }],
	      ix: 2,
	      l: 2
	    },
	    a: {
	      a: 0,
	      k: [24.514, 17.911, 0],
	      ix: 1,
	      l: 2
	    },
	    s: {
	      a: 0,
	      k: [100, 100, 100],
	      ix: 6,
	      l: 2
	    }
	  },
	  ao: 0,
	  shapes: [{
	    ty: "gr",
	    it: [{
	      ty: "gr",
	      it: [{
	        ind: 0,
	        ty: "sh",
	        ix: 1,
	        ks: {
	          a: 0,
	          k: {
	            i: [[-0.268, 0.008], [-1.033, 0.845], [-0.252, 0.549], [0.072, 0.132], [0, 0], [0.268, -0.399], [0.347, -0.284], [1.185, -0.154], [-0.181, -0.335]],
	            o: [[1.483, -0.047], [0.534, -0.437], [0.063, -0.14], [0, 0], [-0.22, -0.406], [-0.227, 0.336], [-0.849, 0.694], [-0.437, 0.057], [0.109, 0.203]],
	            v: [[-2.484, 2.023], [1.426, 0.695], [3.225, -1.137], [3.205, -1.564], [3.184, -1.604], [2.012, -1.633], [0.526, -0.361], [-2.601, 0.906], [-3.107, 1.71]],
	            c: true
	          },
	          ix: 2
	        },
	        nm: "Path 1",
	        mn: "ADBE Vector Shape - Group",
	        hd: false
	      }, {
	        ty: "fl",
	        c: {
	          a: 0,
	          k: [0.47058826685, 0.243137270212, 0.066666670144, 1],
	          ix: 4
	        },
	        o: {
	          a: 0,
	          k: 100,
	          ix: 5
	        },
	        r: 1,
	        bm: 0,
	        nm: "Fill 1",
	        mn: "ADBE Vector Graphic - Fill",
	        hd: false
	      }, {
	        ty: "tr",
	        p: {
	          a: 0,
	          k: [24.464, 17.859],
	          ix: 2
	        },
	        a: {
	          a: 0,
	          k: [0, 0],
	          ix: 1
	        },
	        s: {
	          a: 0,
	          k: [100, 100],
	          ix: 3
	        },
	        r: {
	          a: 0,
	          k: 0,
	          ix: 6
	        },
	        o: {
	          a: 0,
	          k: 100,
	          ix: 7
	        },
	        sk: {
	          a: 0,
	          k: 0,
	          ix: 4
	        },
	        sa: {
	          a: 0,
	          k: 0,
	          ix: 5
	        },
	        nm: "Transform"
	      }],
	      nm: "Group 2",
	      np: 2,
	      cix: 2,
	      bm: 0,
	      ix: 1,
	      mn: "ADBE Vector Group",
	      hd: false
	    }, {
	      ty: "tr",
	      p: {
	        a: 0,
	        k: [24.514, 17.911],
	        ix: 2
	      },
	      a: {
	        a: 0,
	        k: [24.514, 17.911],
	        ix: 1
	      },
	      s: {
	        a: 0,
	        k: [100, 100],
	        ix: 3
	      },
	      r: {
	        a: 0,
	        k: 0,
	        ix: 6
	      },
	      o: {
	        a: 0,
	        k: 100,
	        ix: 7
	      },
	      sk: {
	        a: 0,
	        k: 0,
	        ix: 4
	      },
	      sa: {
	        a: 0,
	        k: 0,
	        ix: 5
	      },
	      nm: "Transform"
	    }],
	    nm: "Group 2",
	    np: 1,
	    cix: 2,
	    bm: 0,
	    ix: 1,
	    mn: "ADBE Vector Group",
	    hd: false
	  }],
	  ip: 0,
	  op: 60,
	  st: 0,
	  bm: 0
	}, {
	  ddd: 0,
	  ind: 5,
	  ty: 4,
	  nm: "Group 1",
	  sr: 1,
	  ks: {
	    o: {
	      a: 0,
	      k: 100,
	      ix: 11
	    },
	    r: {
	      a: 0,
	      k: 0,
	      ix: 10
	    },
	    p: {
	      a: 1,
	      k: [{
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: 10,
	        s: [13.776, 12.161, 0],
	        to: [0, -0.857, 0],
	        ti: [0, 0.857, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 0.667
	        },
	        o: {
	          x: 0.333,
	          y: 0.333
	        },
	        t: 17,
	        s: [13.776, 7.021, 0],
	        to: [0, 0, 0],
	        ti: [0, 0, 0]
	      }, {
	        i: {
	          x: 0.667,
	          y: 1
	        },
	        o: {
	          x: 0.333,
	          y: 0
	        },
	        t: 37,
	        s: [13.776, 7.021, 0],
	        to: [0, 0.857, 0],
	        ti: [0, -0.857, 0]
	      }, {
	        t: 42,
	        s: [13.776, 12.161, 0]
	      }],
	      ix: 2,
	      l: 2
	    },
	    a: {
	      a: 0,
	      k: [12.776, 17.896, 0],
	      ix: 1,
	      l: 2
	    },
	    s: {
	      a: 0,
	      k: [100, 100, 100],
	      ix: 6,
	      l: 2
	    }
	  },
	  ao: 0,
	  shapes: [{
	    ty: "gr",
	    it: [{
	      ty: "gr",
	      it: [{
	        ind: 0,
	        ty: "sh",
	        ix: 1,
	        ks: {
	          a: 0,
	          k: {
	            i: [[0.268, 0.008], [1.033, 0.845], [0.252, 0.549], [-0.071, 0.132], [0, 0], [-0.269, -0.399], [-0.347, -0.284], [-1.185, -0.154], [0.181, -0.335]],
	            o: [[-1.483, -0.046], [-0.534, -0.437], [-0.064, -0.14], [0, 0], [0.219, -0.406], [0.226, 0.336], [0.849, 0.694], [0.438, 0.057], [-0.11, 0.203]],
	            v: [[2.484, 2.023], [-1.426, 0.695], [-3.225, -1.137], [-3.206, -1.564], [-3.184, -1.604], [-2.012, -1.633], [-0.525, -0.361], [2.6, 0.906], [3.107, 1.71]],
	            c: true
	          },
	          ix: 2
	        },
	        nm: "Path 1",
	        mn: "ADBE Vector Shape - Group",
	        hd: false
	      }, {
	        ty: "fl",
	        c: {
	          a: 0,
	          k: [0.47058826685, 0.243137270212, 0.066666670144, 1],
	          ix: 4
	        },
	        o: {
	          a: 0,
	          k: 100,
	          ix: 5
	        },
	        r: 1,
	        bm: 0,
	        nm: "Fill 1",
	        mn: "ADBE Vector Graphic - Fill",
	        hd: false
	      }, {
	        ty: "tr",
	        p: {
	          a: 0,
	          k: [12.826, 17.844],
	          ix: 2
	        },
	        a: {
	          a: 0,
	          k: [0, 0],
	          ix: 1
	        },
	        s: {
	          a: 0,
	          k: [100, 100],
	          ix: 3
	        },
	        r: {
	          a: 0,
	          k: 0,
	          ix: 6
	        },
	        o: {
	          a: 0,
	          k: 100,
	          ix: 7
	        },
	        sk: {
	          a: 0,
	          k: 0,
	          ix: 4
	        },
	        sa: {
	          a: 0,
	          k: 0,
	          ix: 5
	        },
	        nm: "Transform"
	      }],
	      nm: "Group 1",
	      np: 2,
	      cix: 2,
	      bm: 0,
	      ix: 1,
	      mn: "ADBE Vector Group",
	      hd: false
	    }, {
	      ty: "tr",
	      p: {
	        a: 0,
	        k: [12.776, 17.896],
	        ix: 2
	      },
	      a: {
	        a: 0,
	        k: [12.776, 17.896],
	        ix: 1
	      },
	      s: {
	        a: 0,
	        k: [100, 100],
	        ix: 3
	      },
	      r: {
	        a: 0,
	        k: 0,
	        ix: 6
	      },
	      o: {
	        a: 0,
	        k: 100,
	        ix: 7
	      },
	      sk: {
	        a: 0,
	        k: 0,
	        ix: 4
	      },
	      sa: {
	        a: 0,
	        k: 0,
	        ix: 5
	      },
	      nm: "Transform"
	    }],
	    nm: "Group 1",
	    np: 1,
	    cix: 2,
	    bm: 0,
	    ix: 1,
	    mn: "ADBE Vector Group",
	    hd: false
	  }],
	  ip: 0,
	  op: 60,
	  st: 0,
	  bm: 0
	}, {
	  ddd: 0,
	  ind: 6,
	  ty: 4,
	  nm: "Group 4",
	  sr: 1,
	  ks: {
	    o: {
	      a: 0,
	      k: 100,
	      ix: 11
	    },
	    r: {
	      a: 0,
	      k: 0,
	      ix: 10
	    },
	    p: {
	      a: 0,
	      k: [20, 17, 0],
	      ix: 2,
	      l: 2
	    },
	    a: {
	      a: 0,
	      k: [19, 19, 0],
	      ix: 1,
	      l: 2
	    },
	    s: {
	      a: 0,
	      k: [100, 100, 100],
	      ix: 6,
	      l: 2
	    }
	  },
	  ao: 0,
	  shapes: [{
	    ty: "gr",
	    it: [{
	      ind: 0,
	      ty: "sh",
	      ix: 1,
	      ks: {
	        a: 0,
	        k: {
	          i: [[-9.389, 0], [0, -9.389], [9.389, 0], [0, 9.389]],
	          o: [[9.389, 0], [0, 9.389], [-9.389, 0], [0, -9.389]],
	          v: [[0, -17], [17, 0], [0, 17], [-17, 0]],
	          c: true
	        },
	        ix: 2
	      },
	      nm: "Path 1",
	      mn: "ADBE Vector Shape - Group",
	      hd: false
	    }, {
	      ty: "fl",
	      c: {
	        a: 0,
	        k: [0.964705942191, 0.878431432387, 0.443137284821, 1],
	        ix: 4
	      },
	      o: {
	        a: 0,
	        k: 100,
	        ix: 5
	      },
	      r: 1,
	      bm: 0,
	      nm: "Fill 1",
	      mn: "ADBE Vector Graphic - Fill",
	      hd: false
	    }, {
	      ty: "tr",
	      p: {
	        a: 0,
	        k: [19, 19],
	        ix: 2
	      },
	      a: {
	        a: 0,
	        k: [0, 0],
	        ix: 1
	      },
	      s: {
	        a: 0,
	        k: [100, 100],
	        ix: 3
	      },
	      r: {
	        a: 0,
	        k: 0,
	        ix: 6
	      },
	      o: {
	        a: 0,
	        k: 100,
	        ix: 7
	      },
	      sk: {
	        a: 0,
	        k: 0,
	        ix: 4
	      },
	      sa: {
	        a: 0,
	        k: 0,
	        ix: 5
	      },
	      nm: "Transform"
	    }],
	    nm: "Group 4",
	    np: 2,
	    cix: 2,
	    bm: 0,
	    ix: 1,
	    mn: "ADBE Vector Group",
	    hd: false
	  }],
	  ip: 0,
	  op: 60,
	  st: 0,
	  bm: 0
	}];
	var markers$5 = [];
	var facepalmAnimatedEmojiData = {
	  v: v$5,
	  fr: fr$5,
	  ip: ip$5,
	  op: op$5,
	  w: w$5,
	  h: h$5,
	  nm: nm$5,
	  ddd: ddd$5,
	  assets: assets$5,
	  layers: layers$5,
	  markers: markers$5
	};

	var v$6 = "5.9.1";
	var fr$6 = 25;
	var ip$6 = 0;
	var op$6 = 21;
	var w$6 = 40;
	var h$6 = 40;
	var nm$6 = "em_07";
	var ddd$6 = 0;
	var assets$6 = [];
	var layers$6 = [{
	  ddd: 0,
	  ind: 1,
	  ty: 4,
	  nm: "Group 1 :M 2",
	  sr: 1,
	  ks: {
	    o: {
	      a: 0,
	      k: 100,
	      ix: 11
	    },
	    r: {
	      a: 0,
	      k: 0,
	      ix: 10
	    },
	    p: {
	      a: 0,
	      k: [20.142, 10.824, 0],
	      ix: 2,
	      l: 2
	    },
	    a: {
	      a: 0,
	      k: [19.142, 12.824, 0],
	      ix: 1,
	      l: 2
	    },
	    s: {
	      a: 0,
	      k: [100, 100, 100],
	      ix: 6,
	      l: 2
	    }
	  },
	  ao: 0,
	  shapes: [{
	    ty: "gr",
	    it: [{
	      ty: "gr",
	      it: [{
	        ind: 0,
	        ty: "sh",
	        ix: 1,
	        ks: {
	          a: 0,
	          k: {
	            i: [[1.007, 0.088], [0.848, -0.551], [0.007, -0.072], [0, 0], [-0.156, 0.074], [-0.788, -0.069], [-0.746, -0.701], [-0.015, 0.19], [0, 0], [0.052, 0.048]],
	            o: [[-1.008, -0.088], [-0.06, 0.039], [0, 0], [-0.015, 0.173], [0.702, -0.335], [1.017, 0.089], [0.137, 0.13], [0, 0], [0.006, -0.072], [-0.743, -0.689]],
	            v: [[0.132, -1.337], [-2.693, -0.592], [-2.798, -0.415], [-2.854, 0.247], [-2.537, 0.475], [-0.284, 0.053], [2.394, 1.294], [2.77, 1.146], [2.862, 0.074], [2.789, -0.117]],
	            c: true
	          },
	          ix: 2
	        },
	        nm: "Path 1",
	        mn: "ADBE Vector Shape - Group",
	        hd: false
	      }, {
	        ty: "fl",
	        c: {
	          a: 0,
	          k: [0.47058826685, 0.243137270212, 0.066666670144, 1],
	          ix: 4
	        },
	        o: {
	          a: 0,
	          k: 100,
	          ix: 5
	        },
	        r: 1,
	        bm: 0,
	        nm: "Fill 1",
	        mn: "ADBE Vector Graphic - Fill",
	        hd: false
	      }, {
	        ty: "tr",
	        p: {
	          a: 0,
	          k: [13.201, 12.822],
	          ix: 2
	        },
	        a: {
	          a: 0,
	          k: [0, 0],
	          ix: 1
	        },
	        s: {
	          a: 0,
	          k: [100, 100],
	          ix: 3
	        },
	        r: {
	          a: 0,
	          k: 0,
	          ix: 6
	        },
	        o: {
	          a: 0,
	          k: 100,
	          ix: 7
	        },
	        sk: {
	          a: 0,
	          k: 0,
	          ix: 4
	        },
	        sa: {
	          a: 0,
	          k: 0,
	          ix: 5
	        },
	        nm: "Transform"
	      }],
	      nm: "Group 1",
	      np: 2,
	      cix: 2,
	      bm: 0,
	      ix: 1,
	      mn: "ADBE Vector Group",
	      hd: false
	    }, {
	      ty: "tr",
	      p: {
	        a: 0,
	        k: [13.206, 12.824],
	        ix: 2
	      },
	      a: {
	        a: 0,
	        k: [13.206, 12.824],
	        ix: 1
	      },
	      s: {
	        a: 0,
	        k: [100, 100],
	        ix: 3
	      },
	      r: {
	        a: 0,
	        k: 0,
	        ix: 6
	      },
	      o: {
	        a: 0,
	        k: 100,
	        ix: 7
	      },
	      sk: {
	        a: 0,
	        k: 0,
	        ix: 4
	      },
	      sa: {
	        a: 0,
	        k: 0,
	        ix: 5
	      },
	      nm: "Transform"
	    }],
	    nm: "Group 1",
	    np: 1,
	    cix: 2,
	    bm: 0,
	    ix: 1,
	    mn: "ADBE Vector Group",
	    hd: false
	  }, {
	    ty: "gr",
	    it: [{
	      ty: "gr",
	      it: [{
	        ind: 0,
	        ty: "sh",
	        ix: 1,
	        ks: {
	          a: 0,
	          k: {
	            i: [[0, 0], [0.077, 0.049], [0.998, -0.088], [0.739, -0.676], [-0.008, -0.093], [0, 0], [-0.18, 0.161], [-0.974, 0.085], [-0.674, -0.302], [0.019, 0.224]],
	            o: [[-0.008, -0.092], [-0.841, -0.539], [-0.996, 0.086], [-0.068, 0.062], [0, 0], [0.021, 0.243], [0.729, -0.651], [0.751, -0.066], [0.202, 0.091], [0, 0]],
	            v: [[2.802, -0.355], [2.668, -0.582], [-0.129, -1.308], [-2.763, -0.112], [-2.857, 0.136], [-2.78, 1.044], [-2.296, 1.235], [0.286, 0.081], [2.44, 0.457], [2.846, 0.165]],
	            c: true
	          },
	          ix: 2
	        },
	        nm: "Path 1",
	        mn: "ADBE Vector Shape - Group",
	        hd: false
	      }, {
	        ty: "fl",
	        c: {
	          a: 0,
	          k: [0.47058826685, 0.243137270212, 0.066666670144, 1],
	          ix: 4
	        },
	        o: {
	          a: 0,
	          k: 100,
	          ix: 5
	        },
	        r: 1,
	        bm: 0,
	        nm: "Fill 1",
	        mn: "ADBE Vector Graphic - Fill",
	        hd: false
	      }, {
	        ty: "tr",
	        p: {
	          a: 0,
	          k: [25.09, 12.794],
	          ix: 2
	        },
	        a: {
	          a: 0,
	          k: [0, 0],
	          ix: 1
	        },
	        s: {
	          a: 0,
	          k: [100, 100],
	          ix: 3
	        },
	        r: {
	          a: 0,
	          k: 0,
	          ix: 6
	        },
	        o: {
	          a: 0,
	          k: 100,
	          ix: 7
	        },
	        sk: {
	          a: 0,
	          k: 0,
	          ix: 4
	        },
	        sa: {
	          a: 0,
	          k: 0,
	          ix: 5
	        },
	        nm: "Transform"
	      }],
	      nm: "Group 2",
	      np: 2,
	      cix: 2,
	      bm: 0,
	      ix: 1,
	      mn: "ADBE Vector Group",
	      hd: false
	    }, {
	      ty: "tr",
	      p: {
	        a: 0,
	        k: [25.085, 12.787],
	        ix: 2
	      },
	      a: {
	        a: 0,
	        k: [25.085, 12.787],
	        ix: 1
	      },
	      s: {
	        a: 0,
	        k: [100, 100],
	        ix: 3
	      },
	      r: {
	        a: 0,
	        k: 0,
	        ix: 6
	      },
	      o: {
	        a: 0,
	        k: 100,
	        ix: 7
	      },
	      sk: {
	        a: 0,
	        k: 0,
	        ix: 4
	      },
	      sa: {
	        a: 0,
	        k: 0,
	        ix: 5
	      },
	      nm: "Transform"
	    }],
	    nm: "Group 2",
	    np: 1,
	    cix: 2,
	    bm: 0,
	    ix: 2,
	    mn: "ADBE Vector Group",
	    hd: false
	  }],
	  ip: 0,
	  op: 21,
	  st: 0,
	  bm: 0
	}, {
	  ddd: 0,
	  ind: 2,
	  ty: 4,
	  nm: "Group 3",
	  sr: 1,
	  ks: {
	    o: {
	      a: 0,
	      k: 100,
	      ix: 11
	    },
	    r: {
	      a: 0,
	      k: 0,
	      ix: 10
	    },
	    p: {
	      a: 0,
	      k: [20, 22.427, 0],
	      ix: 2,
	      l: 2
	    },
	    a: {
	      a: 0,
	      k: [19, 24.428, 0],
	      ix: 1,
	      l: 2
	    },
	    s: {
	      a: 1,
	      k: [{
	        i: {
	          x: [0.667, 0.667, 0.667],
	          y: [1, 1, 1]
	        },
	        o: {
	          x: [0.333, 0.333, 0.333],
	          y: [0, 0, 0]
	        },
	        t: 0,
	        s: [100, 100, 100]
	      }, {
	        i: {
	          x: [0.667, 0.667, 0.667],
	          y: [1, 1, 1]
	        },
	        o: {
	          x: [0.333, 0.333, 0.333],
	          y: [0, 0, 0]
	        },
	        t: 3,
	        s: [80, 80, 100]
	      }, {
	        i: {
	          x: [0.667, 0.667, 0.667],
	          y: [1, 1, 1]
	        },
	        o: {
	          x: [0.333, 0.333, 0.333],
	          y: [0, 0, 0]
	        },
	        t: 6,
	        s: [80, 80, 100]
	      }, {
	        i: {
	          x: [0.833, 0.833, 0.833],
	          y: [0.833, 0.833, 1]
	        },
	        o: {
	          x: [0.333, 0.333, 0.333],
	          y: [0, 0, 0]
	        },
	        t: 9,
	        s: [60, 60, 100]
	      }, {
	        i: {
	          x: [0.833, 0.833, 0.833],
	          y: [0.833, 0.833, 0.833]
	        },
	        o: {
	          x: [0.167, 0.167, 0.167],
	          y: [0.167, 0.167, 0.167]
	        },
	        t: 12,
	        s: [120, 120, 100]
	      }, {
	        i: {
	          x: [0.833, 0.833, 0.833],
	          y: [0.833, 0.833, 0.833]
	        },
	        o: {
	          x: [0.167, 0.167, 0.167],
	          y: [0.167, 0.167, 0.167]
	        },
	        t: 15,
	        s: [120, 120, 100]
	      }, {
	        t: 19,
	        s: [100, 100, 100]
	      }],
	      ix: 6,
	      l: 2
	    }
	  },
	  ao: 0,
	  shapes: [{
	    ty: "gr",
	    it: [{
	      ind: 0,
	      ty: "sh",
	      ix: 1,
	      ks: {
	        a: 0,
	        k: {
	          i: [[0.509, -0.209], [0.027, -1.264], [-0.023, -0.284], [-0.106, -0.326], [-0.559, -0.561], [-0.485, -0.355], [-0.778, -0.511], [0, 0], [-0.635, 0.418], [-0.643, 0.479], [-0.413, 0.45], [-0.151, 0.805], [-0.014, 0.29], [0.145, 0.412], [1.052, 0.491], [0.684, 0.057], [0.427, -0.052], [0.659, -0.392], [0.368, -0.456], [0.011, -0.012], [0.054, 0.062], [0.857, 0.335], [0.578, 0.046], [0.163, 0], [0.217, -0.021]],
	          o: [[-1.497, 0.615], [-0.006, 0.285], [0.026, 0.337], [0.222, 0.685], [0.408, 0.408], [0.741, 0.542], [0.662, 0.435], [0, 0], [0.68, -0.448], [0.512, -0.385], [0.611, -0.669], [0.054, -0.287], [0.021, -0.424], [-0.319, -0.905], [-0.587, -0.273], [-0.43, -0.036], [-0.827, 0.1], [-0.556, 0.329], [-0.011, 0.012], [-0.063, -0.068], [-0.534, -0.603], [-0.517, -0.202], [-0.165, -0.013], [-0.217, 0], [-0.575, 0.057]],
	          v: [[-6.65, -5.389], [-9.039, -2.352], [-9, -1.499], [-8.792, -0.505], [-7.601, 1.357], [-6.246, 2.491], [-3.946, 4.051], [0.028, 5.829], [4.388, 3.751], [6.389, 2.374], [7.798, 1.135], [8.927, -1.079], [9.024, -1.945], [8.875, -3.204], [6.828, -5.305], [4.917, -5.792], [3.63, -5.775], [1.405, -5.029], [0.028, -3.844], [-0.004, -3.807], [-0.172, -4.001], [-2.237, -5.426], [-3.88, -5.798], [-4.372, -5.818], [-5.022, -5.786]],
	          c: true
	        },
	        ix: 2
	      },
	      nm: "Path 1",
	      mn: "ADBE Vector Shape - Group",
	      hd: false
	    }, {
	      ty: "fl",
	      c: {
	        a: 0,
	        k: [0.941176530427, 0.239215701234, 0.215686289469, 1],
	        ix: 4
	      },
	      o: {
	        a: 0,
	        k: 100,
	        ix: 5
	      },
	      r: 1,
	      bm: 0,
	      nm: "Fill 1",
	      mn: "ADBE Vector Graphic - Fill",
	      hd: false
	    }, {
	      ty: "tr",
	      p: {
	        a: 0,
	        k: [19.005, 24.422],
	        ix: 2
	      },
	      a: {
	        a: 0,
	        k: [0, 0],
	        ix: 1
	      },
	      s: {
	        a: 0,
	        k: [100, 100],
	        ix: 3
	      },
	      r: {
	        a: 0,
	        k: 0,
	        ix: 6
	      },
	      o: {
	        a: 0,
	        k: 100,
	        ix: 7
	      },
	      sk: {
	        a: 0,
	        k: 0,
	        ix: 4
	      },
	      sa: {
	        a: 0,
	        k: 0,
	        ix: 5
	      },
	      nm: "Transform"
	    }],
	    nm: "Group 3",
	    np: 2,
	    cix: 2,
	    bm: 0,
	    ix: 1,
	    mn: "ADBE Vector Group",
	    hd: false
	  }],
	  ip: 0,
	  op: 21,
	  st: 0,
	  bm: 0
	}, {
	  ddd: 0,
	  ind: 3,
	  ty: 4,
	  nm: "Group 4",
	  sr: 1,
	  ks: {
	    o: {
	      a: 0,
	      k: 100,
	      ix: 11
	    },
	    r: {
	      a: 0,
	      k: 0,
	      ix: 10
	    },
	    p: {
	      a: 0,
	      k: [20, 17, 0],
	      ix: 2,
	      l: 2
	    },
	    a: {
	      a: 0,
	      k: [19, 19, 0],
	      ix: 1,
	      l: 2
	    },
	    s: {
	      a: 0,
	      k: [100, 100, 100],
	      ix: 6,
	      l: 2
	    }
	  },
	  ao: 0,
	  shapes: [{
	    ty: "gr",
	    it: [{
	      ind: 0,
	      ty: "sh",
	      ix: 1,
	      ks: {
	        a: 0,
	        k: {
	          i: [[-9.389, 0], [0, -9.389], [9.389, 0], [0, 9.389]],
	          o: [[9.389, 0], [0, 9.389], [-9.389, 0], [0, -9.389]],
	          v: [[0, -17], [17, 0], [0, 17], [-17, 0]],
	          c: true
	        },
	        ix: 2
	      },
	      nm: "Path 1",
	      mn: "ADBE Vector Shape - Group",
	      hd: false
	    }, {
	      ty: "fl",
	      c: {
	        a: 0,
	        k: [0.964705942191, 0.878431432387, 0.443137284821, 1],
	        ix: 4
	      },
	      o: {
	        a: 0,
	        k: 100,
	        ix: 5
	      },
	      r: 1,
	      bm: 0,
	      nm: "Fill 1",
	      mn: "ADBE Vector Graphic - Fill",
	      hd: false
	    }, {
	      ty: "tr",
	      p: {
	        a: 0,
	        k: [19, 19],
	        ix: 2
	      },
	      a: {
	        a: 0,
	        k: [0, 0],
	        ix: 1
	      },
	      s: {
	        a: 0,
	        k: [100, 100],
	        ix: 3
	      },
	      r: {
	        a: 0,
	        k: 0,
	        ix: 6
	      },
	      o: {
	        a: 0,
	        k: 100,
	        ix: 7
	      },
	      sk: {
	        a: 0,
	        k: 0,
	        ix: 4
	      },
	      sa: {
	        a: 0,
	        k: 0,
	        ix: 5
	      },
	      nm: "Transform"
	    }],
	    nm: "Group 4",
	    np: 2,
	    cix: 2,
	    bm: 0,
	    ix: 1,
	    mn: "ADBE Vector Group",
	    hd: false
	  }],
	  ip: 0,
	  op: 21,
	  st: 0,
	  bm: 0
	}];
	var markers$6 = [];
	var kissAnimatedEmojiData = {
	  v: v$6,
	  fr: fr$6,
	  ip: ip$6,
	  op: op$6,
	  w: w$6,
	  h: h$6,
	  nm: nm$6,
	  ddd: ddd$6,
	  assets: assets$6,
	  layers: layers$6,
	  markers: markers$6
	};

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
	              if (newValue > 0 && newValue > reactionCount) {
	                elementsNew.push({
	                  reaction: reactionValue,
	                  count: newValue,
	                  animate: {
	                    type: 'pop'
	                  }
	                });
	              } else if (newValue > 0) {
	                elementsNew.push({
	                  reaction: reactionValue,
	                  count: reactionCount,
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
	      var _this = this;
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
	        var classList = ['feed-post-emoji-icon-item', "feed-post-emoji-icon-item-".concat(i + 1)];
	        if (element !== null && element !== void 0 && element.animate) {
	          var _element$animate;
	          if (((_element$animate = element.animate) === null || _element$animate === void 0 ? void 0 : _element$animate.type) === 'pop') {
	            classList.push('feed-post-emoji-animation-pop');
	          } else if (i >= 1) {
	            classList.push('feed-post-emoji-icon-animate');
	          } else if (data.length == 1) {
	            classList.push('feed-post-emoji-animation-pop');
	          }
	        }
	        var emojiContainer = main_core.Dom.create('div', {
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
	        });
	        var animation = ui_lottie.Lottie.loadAnimation({
	          animationData: _this.reactionsAnimationData[element.reaction],
	          container: emojiContainer,
	          loop: false,
	          autoplay: false,
	          renderer: 'svg',
	          rendererSettings: {
	            viewBoxOnly: true
	          }
	        });
	        if (Boolean(element.animate)) {
	          setTimeout(function () {
	            animation.play();
	          }, 200);
	        }
	        container.appendChild(emojiContainer);
	        reactionsData[element.reaction] = element.count;
	      });
	      container.setAttribute('data-reactions-data', JSON.stringify(reactionsData));
	    }
	  }, {
	    key: "showReactionsPopup",
	    value: function showReactionsPopup(params) {
	      var _this2 = this;
	      var bindElement = this.getNode(params.bindElement);
	      var likeId = main_core.Type.isStringFilled(params.likeId) ? params.likeId : '';
	      if (!bindElement || !main_core.Type.isStringFilled(likeId)) {
	        return false;
	      }
	      this.reactionsPopupLikeId = likeId;
	      if (this.reactionsPopup === null) {
	        var reactionsNodesList = [];
	        this.reactionsList.forEach(function (currentEmotion, index) {
	          var emojiItem = main_core.Dom.create('div', {
	            props: {
	              className: "feed-post-emoji-icon-item"
	            },
	            attrs: {
	              'data-reaction': currentEmotion,
	              title: main_core.Loc.getMessage("RATING_LIKE_EMOTION_".concat(currentEmotion.toUpperCase(), "_CALC"))
	            }
	          });
	          ui_lottie.Lottie.loadAnimation({
	            renderer: 'svg',
	            container: emojiItem,
	            animationData: _this2.reactionsAnimationData[currentEmotion]
	          });
	          reactionsNodesList.push(emojiItem);
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
	            RatingLike$1.ClickVote(e, _this2.reactionsPopupLikeId, reactionNode.getAttribute('data-reaction'), true);
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
	            width: 300,
	            left: bindElementPosition.left + bindElementPosition.width / 2 - 133,
	            top: bindElementPosition.top + deltaY - 5,
	            borderRadius: 50,
	            opacity: 100
	          },
	          transition: BX.easing.makeEaseInOut(BX.easing.transitions.cubic),
	          step: function step(state) {
	            if (!_this2.reactionsPopup) {
	              _this2.reactionsPopupAnimation.stop();
	              return;
	            }
	            _this2.reactionsPopup.style.width = "".concat(state.width, "px");
	            _this2.reactionsPopup.style.left = "".concat(state.left, "px");
	            _this2.reactionsPopup.style.top = "".concat(state.top, "px");
	            _this2.reactionsPopup.style.borderRadius = "".concat(state.borderRadius, "px");
	            _this2.reactionsPopup.style.opacity = state.opacity / 100;
	            _this2.reactionsPopupOpacityState = state.opacity;
	          },
	          complete: function complete() {
	            if (!_this2.reactionsPopup) {
	              return;
	            }
	            _this2.reactionsPopup.style.opacity = '';
	            _this2.reactionsPopup.classList.add('feed-post-emoji-popup-active-final');
	            likeInstance.box.classList.add('feed-post-emoji-control-active');
	            if (main_core.Type.isFunction(params.onComplete)) {
	              params.onComplete();
	            }
	          }
	        });
	        this.reactionsPopupAnimation.animate();
	        setTimeout(function () {
	          if (!_this2.reactionsPopup) {
	            return;
	          }
	          var reactions = _this2.reactionsPopup.querySelectorAll('.feed-post-emoji-icon-item');
	          _this2.reactionsPopupAnimation2 = new BX.easing({
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
	              _this2.reactionsPopup.classList.add('feed-post-emoji-popup-active-final-item');
	              reactions[0].style.opacity = '';
	              reactions[1].style.opacity = '';
	              reactions[2].style.opacity = '';
	              reactions[3].style.opacity = '';
	              reactions[4].style.opacity = '';
	              reactions[5].style.opacity = '';
	              reactions[6].style.opacity = '';
	            }
	          });
	          _this2.reactionsPopupAnimation2.animate();
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
	      } else
	        // show reactions popup and handle clicks
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
	      var _this3 = this;
	      if (this.blockShowPopupTimeout) {
	        window.clearTimeout(this.blockShowPopupTimeout);
	      }
	      this.blockShowPopup = true;
	      this.blockShowPopupTimeout = setTimeout(function () {
	        _this3.blockShowPopup = false;
	      }, 500);
	    }
	  }, {
	    key: "hideReactionsPopup",
	    value: function hideReactionsPopup(params) {
	      var _this4 = this;
	      var likeId = main_core.Type.isStringFilled(params.likeId) ? params.likeId : false;
	      if (this.reactionsPopup) {
	        if (RatingManager.mobile) {
	          this.reactionsPopup.classList.add('feed-post-emoji-popup-invisible-final');
	          this.reactionsPopup.classList.add('feed-post-emoji-popup-invisible-final-mobile');
	          this.reactionsPopup.classList.remove('feed-post-emoji-popup-active');
	          this.reactionsPopup.classList.remove('feed-post-emoji-popup-active-final');
	          this.reactionsPopup.classList.remove('feed-post-emoji-popup-active-final-item');
	          this.reactionsPopupMobileEnableScroll();
	          main_core.Dom.remove(this.reactionsPopup);
	          this.reactionsPopup = null;
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
	              _this4.reactionsPopup.style.opacity = state.opacity / 100;
	              _this4.reactionsPopupOpacityState = state.opacity;
	            },
	            complete: function complete() {
	              _this4.reactionsPopup.style.opacity = '';
	              _this4.reactionsPopup.classList.add('feed-post-emoji-popup-invisible-final');
	              _this4.reactionsPopup.classList.remove('feed-post-emoji-popup-active');
	              _this4.reactionsPopup.classList.remove('feed-post-emoji-popup-active-final');
	              _this4.reactionsPopup.classList.remove('feed-post-emoji-popup-active-final-item');
	              main_core.Dom.remove(_this4.reactionsPopup);
	              _this4.reactionsPopup = null;
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
	      var nodeAboveFinger = document.elementFromPoint(x, y + this.touchMoveDeltaY - this.touchScrollTop);
	      var nodeBelowFinger = document.elementFromPoint(x, y - this.touchScrollTop);
	      var iconNodeAboveFinger = nodeAboveFinger === null || nodeAboveFinger === void 0 ? void 0 : nodeAboveFinger.closest('[data-reaction]');
	      var iconNodeBelowFinger = nodeBelowFinger === null || nodeBelowFinger === void 0 ? void 0 : nodeBelowFinger.closest('[data-reaction]');
	      var reactionNode = iconNodeAboveFinger || iconNodeBelowFinger;
	      var userReaction = reactionNode === null || reactionNode === void 0 ? void 0 : reactionNode.getAttribute('data-reaction');
	      return main_core.Type.isStringFilled(userReaction) ? reactionNode : null;
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
	      var _this5 = this;
	      document.addEventListener('touchmove', this.touchMoveScrollListener, {
	        passive: false
	      });
	      if (app) {
	        app.exec('disableTabScrolling');
	      }
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
	        if (main_core.Type.isNull(_this5.mobileOverlay)) {
	          return;
	        }
	        main_core.Dom.append(_this5.mobileOverlay, document.body);
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
	      var _this6 = this;
	      return function (e) {
	        if (!_this6.reactionsPopup) {
	          document.removeEventListener('mousemove', _this6.reactionsPopupMouseOutHandler);
	          _this6.reactionsPopupMouseOutHandler = null;
	          return;
	        }
	        var popupPosition = _this6.reactionsPopup.getBoundingClientRect();
	        var inverted = _this6.reactionsPopup.classList.contains('feed-post-emoji-popup-inverted');
	        if (e.clientX >= popupPosition.left && e.clientX <= popupPosition.right && e.clientY >= popupPosition.top - (inverted ? 25 : 0) && e.clientY <= popupPosition.bottom + (inverted ? 0 : 25)) {
	          return;
	        }
	        _this6.blockReactionsPopup();
	        _this6.hideReactionsPopup({
	          likeId: likeId
	        });
	        document.removeEventListener('mousemove', _this6.reactionsPopupMouseOutHandler);
	        _this6.reactionsPopupMouseOutHandler = null;
	      };
	    }
	  }, {
	    key: "getMouseOverHandler",
	    value: function getMouseOverHandler(likeId) {
	      var _this7 = this;
	      return function () {
	        var _this7$reactionsPopup, _this7$reactionsPopup2;
	        var likeInstance = RatingLike$1.getInstance(likeId);
	        if (_this7.reactionsPopup && !((_this7$reactionsPopup = _this7.reactionsPopup) !== null && _this7$reactionsPopup !== void 0 && _this7$reactionsPopup.classList.contains('feed-post-emoji-popup-invisible')) && !(RatingManager.mobile && (_this7$reactionsPopup2 = _this7.reactionsPopup) !== null && _this7$reactionsPopup2 !== void 0 && _this7$reactionsPopup2.classList.contains('feed-post-emoji-popup-invisible-final-mobile'))) {
	          return;
	        }
	        if (!_this7.afterClickBlockShowPopup) {
	          if (_this7.blockShowPopup) {
	            return;
	          }
	          if (RatingManager.mobile) {
	            app.exec('callVibration');
	          }
	          _this7.showReactionsPopup({
	            bindElement: likeInstance.box,
	            likeId: likeId,
	            onComplete: function onComplete() {
	              likeInstance.box.removeEventListener('mouseenter', likeInstance.mouseOverHandler);
	              likeInstance.box.removeEventListener('mouseleave', _this7.blockReactionsPopup.bind(_this7));
	            }
	          });
	        }
	      };
	    }
	  }, {
	    key: "buildPopupContent",
	    value: function buildPopupContent(params) {
	      var _this8 = this;
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
	      if (requestReaction.length <= 0 || requestReaction == 'all')
	        // first current tab
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
	              _this8.changePopupTab({
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
	              if (requestReaction.length <= 0 || requestReaction === 'all')
	                // first current tab
	                {
	                  _this8.popupSizeInitialized = true;
	                  popupContent.style.height = "".concat(popupContentPosition.height, "px");
	                  popupContent.style.minWidth = "".concat(popupContentPosition.width, "px");
	                } else {
	                if (popupContentPosition.width > Number(popupContent.style.minWidth)) {
	                  popupContent.style.minWidth = "".concat(popupContentPosition.width, "px");
	                }
	              }
	              _this8.changePopupTab({
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
	              'background-image': "url(\"".concat(encodeURI(item.PHOTO_SRC), "\")")
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
	              src: encodeURI(item.PHOTO_SRC)
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
	      var _this9 = this;
	      return function () {
	        _this9.afterClickBlockShowPopup = false;
	        RatingLike$1.getInstance(likeId).box.removeEventListener('mouseleave', _this9.afterClickHandler);
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
	babelHelpers.defineProperty(RatingRender, "reactionsAnimationData", {
	  like: likeAnimatedEmojiData,
	  kiss: kissAnimatedEmojiData,
	  laugh: laughAnimatedEmojiData,
	  wonder: wonderAnimatedEmojiData,
	  cry: cryAnimatedEmojiData,
	  angry: angryAnimatedEmojiData,
	  facepalm: facepalmAnimatedEmojiData
	});
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
	      if (!main_core.Type.isDomNode(node)
	      //			|| !Type.isUndefined(this.ratingNodeList.get(entityId))
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
	      this.delayedList["delete"](key);
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
	  function RatingLike(params) {
	    babelHelpers.classCallCheck(this, RatingLike);
	    this.likeId = main_core.Type.isStringFilled(params.likeId) ? params.likeId : '';
	    this.keySigned = main_core.Type.isStringFilled(params.keySigned) ? params.keySigned : '';
	    this.entityTypeId = main_core.Type.isStringFilled(params.entityTypeId) ? params.entityTypeId : '';
	    this.entityId = !main_core.Type.isUndefined(params.entityId) ? Number(params.entityId) : 0;
	    this.available = main_core.Type.isStringFilled(params.available) ? params.available === 'Y' : false;
	    this.userId = !main_core.Type.isUndefined(params.userId) ? Number(params.userId) : 0;
	    this.localize = main_core.Type.isPlainObject(params.localize) ? params.localize : {};
	    this.template = main_core.Type.isStringFilled(params.template) ? params.template : '';
	    this.pathToUserProfile = main_core.Type.isStringFilled(params.pathToUserProfile) ? params.pathToUserProfile : '';
	    var key = "".concat(this.entityTypeId, "_").concat(this.entityId);
	    this.enabled = true;
	    this.box = document.getElementById("bx-ilike-button-".concat(this.likeId));
	    if (this.box === null) {
	      this.enabled = false;
	      return false;
	    }
	    this.box.setAttribute('data-rating-vote-id', this.likeId);
	    if (this.keySigned === '') {
	      var keySigned = this.box.getAttribute('data-vote-key-signed');
	      this.keySigned = keySigned ? keySigned : '';
	    }
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
	            likeId: this.likeId,
	            container: container,
	            data: elementsNew
	          });
	        } catch (e) {}
	      }
	    }
	    if (!main_core.Type.isUndefined(RatingLike.lastVoteRepo.get(key))) {
	      this.lastVote = RatingLike.lastVoteRepo.get(key);
	      var ratingNode = this.template === 'standart' ? this.button : this.count;
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
	          } else if (userReaction !== likeInstance.lastReaction)
	            // http://jabber.bx/view.php?id=99339
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
	            RATING_VOTE_KEY_SIGNED: likeInstance.keySigned,
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
	    value: function Set(params) {
	      var _this4 = this;
	      var mobile = !!params.mobile;
	      if (params.template === undefined) {
	        params.template = 'standart';
	      }
	      if (this.additionalParams.get('pathToUserProfile')) {
	        params.pathToUserProfile = this.additionalParams.get('pathToUserProfile');
	      }
	      var likeInstance = this.getInstance(params.likeId);
	      if (likeInstance && likeInstance.tryToSet > 5) {
	        return;
	      }
	      var tryToSend = likeInstance && likeInstance.tryToSet ? likeInstance.tryToSet : 1;
	      likeInstance = new RatingLike(params);
	      this.setInstance(params.likeId, likeInstance);
	      if (likeInstance.enabled) {
	        this.Init(params.likeId, {
	          mobile: mobile
	        });
	      } else {
	        setTimeout(function () {
	          likeInstance.tryToSet = tryToSend + 1;
	          _this4.Set(params);
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
	      var likeInstance = this.getInstance(likeId);

	      // like/unlike button
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
	      }
	      // get like-user-list
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

	var lottieAnimations = Object.freeze({
	  like: likeAnimatedEmojiData,
	  laugh: laughAnimatedEmojiData,
	  wonder: wonderAnimatedEmojiData,
	  cry: cryAnimatedEmojiData,
	  angry: angryAnimatedEmojiData,
	  facepalm: facepalmAnimatedEmojiData,
	  admire: kissAnimatedEmojiData
	});
	if (main_core.Type.isUndefined(window.BXRL)) {
	  window.BXRL = {};
	}
	window.BXRL.manager = RatingManager;
	window.BXRL.render = RatingRender;
	window.RatingLike = RatingLike$1;

	exports.lottieAnimations = lottieAnimations;

}((this.BX.Main.Rating = this.BX.Main.Rating || {}),BX.UI,BX,BX.Main,BX.Event));
//# sourceMappingURL=main.rating.js.map
