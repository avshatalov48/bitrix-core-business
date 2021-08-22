this.BX = this.BX || {};
(function (exports,main_core_events,main_core,main_popup) {
	'use strict';

	function _templateObject() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"sonet-sgm-error-text-block\">", "</div>"]);

	  _templateObject = function _templateObject() {
	    return data;
	  };

	  return data;
	}

	var WorkgroupCardUtil = /*#__PURE__*/function () {
	  function WorkgroupCardUtil() {
	    babelHelpers.classCallCheck(this, WorkgroupCardUtil);
	  }

	  babelHelpers.createClass(WorkgroupCardUtil, null, [{
	    key: "processAJAXError",
	    value: function processAJAXError(errorCode) {
	      if (errorCode.indexOf('SESSION_ERROR', 0) === 0) {
	        this.showError(main_core.Loc.getMessage('SGMErrorSessionWrong'));
	      } else if (errorCode.indexOf('CURRENT_USER_NOT_AUTH', 0) === 0) {
	        this.showError(main_core.Loc.getMessage('SGMErrorCurrentUserNotAuthorized'));
	      } else if (errorCode.indexOf('SONET_MODULE_NOT_INSTALLED', 0) === 0) {
	        this.showError(main_core.Loc.getMessage('SGMErrorModuleNotInstalled'));
	      } else {
	        this.showError(errorCode);
	      }

	      return false;
	    }
	  }, {
	    key: "showError",
	    value: function showError(errorText) {
	      new main_popup.Popup('sgm-error' + Math.random(), null, {
	        autoHide: true,
	        lightShadow: false,
	        zIndex: 2,
	        content: main_core.Tag.render(_templateObject(), errorText),
	        closeByEsc: true,
	        closeIcon: true
	      }).show();
	    }
	  }]);
	  return WorkgroupCardUtil;
	}();

	var WorkgroupCardFavorites = /*#__PURE__*/function () {
	  function WorkgroupCardFavorites(params) {
	    var _this = this;

	    babelHelpers.classCallCheck(this, WorkgroupCardFavorites);
	    this.value = !!params.value;
	    this.containerNode = params.containerNode;
	    this.styles = params.styles;
	    this.groupId = parseInt(params.groupId);

	    if (this.containerNode) {
	      if (main_core.Type.isPlainObject(this.styles) && main_core.Type.isStringFilled(this.styles.switch)) {
	        this.containerNode.querySelectorAll(".".concat(this.styles.switch)).forEach(function (node) {
	          node.addEventListener('click', function (e) {
	            _this.set(e);
	          }, true);
	        });
	      }

	      main_core_events.EventEmitter.subscribe('BX.Socialnetwork.WorkgroupMenu:onSetFavorites', function (event) {
	        var _event$getCompatData = event.getCompatData(),
	            _event$getCompatData2 = babelHelpers.slicedToArray(_event$getCompatData, 1),
	            params = _event$getCompatData2[0];

	        _this.setValue(params.value);

	        if (parseInt(params.groupId) === _this.groupId) {
	          var targetNode = _this.containerNode.querySelector(".".concat(_this.styles.switch));

	          if (targetNode) {
	            _this.switch(targetNode, params.value);
	          }
	        }
	      });
	    }
	  }

	  babelHelpers.createClass(WorkgroupCardFavorites, [{
	    key: "setValue",
	    value: function setValue(value) {
	      this.value = value;
	    }
	  }, {
	    key: "getValue",
	    value: function getValue() {
	      return this.value;
	    }
	  }, {
	    key: "set",
	    value: function set(event) {
	      var _this2 = this;

	      var currentValue = this.getValue();
	      var newValue = !currentValue;
	      var sonetGroupMenu = BX.SocialnetworkUICommon.SonetGroupMenu.getInstance();
	      this.setValue(newValue);
	      sonetGroupMenu.favoritesValue = newValue;
	      sonetGroupMenu.setItemTitle(newValue);
	      var targetNode = event.target.classList.contains(this.styles.switch) ? event.target : null;

	      if (!targetNode) {
	        targetNode = this.containerNode.querySelector(".".concat(this.styles.switch));
	      }

	      if (targetNode) {
	        this.switch(targetNode, newValue);
	      }

	      BX.SocialnetworkUICommon.setFavoritesAjax({
	        groupId: this.groupId,
	        favoritesValue: currentValue,
	        callback: {
	          success: function success(data) {
	            var eventData = {
	              code: 'afterSetFavorites',
	              data: {
	                groupId: data.ID,
	                value: data.RESULT == 'Y'
	              }
	            };
	            window.top.BX.SidePanel.Instance.postMessageAll(window, 'sonetGroupEvent', eventData);

	            if (main_core.Type.isStringFilled(data.NAME) && main_core.Type.isStringFilled(data.URL)) {
	              main_core_events.EventEmitter.emit('BX.Socialnetwork.WorkgroupFavorites:onSet', new main_core_events.BaseEvent({
	                compatData: [{
	                  id: _this2.groupId,
	                  name: data.NAME,
	                  url: data.URL,
	                  extranet: main_core.Type.isStringFilled(data.EXTRANET) ? data.EXTRANET : 'N'
	                }, newValue]
	              }));
	            }
	          },
	          failure: function failure(data) {
	            _this2.setValue(currentValue);

	            sonetGroupMenu.favoritesValue = currentValue;
	            sonetGroupMenu.setItemTitle(currentValue);

	            if (main_core.Type.isStringFilled(data.ERROR)) {
	              WorkgroupCardUtil.processAJAXError(data.ERROR);
	            }

	            _this2.switch(targetNode, currentValue);
	          }
	        }
	      });
	      event.preventDefault();
	    }
	  }, {
	    key: "switch",
	    value: function _switch(node, active) {
	      if (!main_core.Type.isDomNode(node) || !main_core.Type.isPlainObject(this.styles) || !main_core.Type.isStringFilled(this.styles.activeSwitch)) {
	        return;
	      }

	      if (active) {
	        node.classList.add(this.styles.activeSwitch);
	      } else {
	        node.classList.remove(this.styles.activeSwitch);
	      }
	    }
	  }]);
	  return WorkgroupCardFavorites;
	}();

	function _templateObject$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"sonet-sgm-notify-hint-content\" style=\"display: none;\"><span id=\"sgm_notify_hint_text\">", "</span></div>"]);

	  _templateObject$1 = function _templateObject() {
	    return data;
	  };

	  return data;
	}

	var WorkgroupCardSubscription = /*#__PURE__*/function () {
	  function WorkgroupCardSubscription(params) {
	    var _this = this;

	    babelHelpers.classCallCheck(this, WorkgroupCardSubscription);
	    this.groupId = parseInt(params.groupId);
	    this.buttonNode = params.buttonNode;
	    this.notifyHintTimeout = null;
	    this.notifyHintPopup = null;
	    this.notifyHintTime = 3000;

	    if (this.buttonNode) {
	      this.buttonNode.addEventListener('click', function () {
	        _this.set();
	      }, true);
	    }
	  }

	  babelHelpers.createClass(WorkgroupCardSubscription, [{
	    key: "set",
	    value: function set() {
	      var _this2 = this;

	      var action = !this.buttonNode.classList.contains('ui-btn-active') ? 'set' : 'unset';
	      this.switch(this.buttonNode, action === 'set');
	      main_core.ajax.runAction('socialnetwork.api.workgroup.setSubscription', {
	        data: {
	          params: {
	            groupId: this.groupId,
	            value: action === 'set' ? 'Y' : 'N'
	          }
	        }
	      }).then(function (data) {
	        var eventData = {
	          code: 'afterSetSubscribe',
	          data: {
	            groupId: _this2.groupId,
	            value: data.RESULT == 'Y'
	          }
	        };
	        window.top.BX.SidePanel.Instance.postMessageAll(window, 'sonetGroupEvent', eventData);
	      }).catch(function (response) {
	        _this2.switch(_this2.buttonNode, !(action === 'set'));

	        WorkgroupCardUtil.processAJAXError(response.errors[0].message);
	      });
	    }
	  }, {
	    key: "switch",
	    value: function _switch(node, active) {
	      if (!main_core.Type.isDomNode(node)) {
	        return;
	      }

	      if (!!active) {
	        node.classList.add('ui-btn-active');
	        node.classList.remove('ui-btn-icon-follow');
	        node.classList.add('ui-btn-icon-unfollow');
	        node.innerHTML = main_core.Loc.getMessage('SGCSSubscribeTitleY');
	        this.showNotifyHint(node, main_core.Loc.getMessage('SGCSSubscribeButtonHintOn'));
	      } else {
	        node.classList.remove('ui-btn-active');
	        node.classList.add('ui-btn-icon-follow');
	        node.classList.remove('ui-btn-icon-unfollow');
	        node.innerHTML = main_core.Loc.getMessage('SGCSSubscribeTitleN');
	        this.showNotifyHint(node, main_core.Loc.getMessage('SGCSSubscribeButtonHintOff'));
	      }
	    }
	  }, {
	    key: "showNotifyHint",
	    value: function showNotifyHint(node, hintText) {
	      var _this3 = this;

	      if (this.notifyHintTimeout) {
	        clearTimeout(this.notifyHintTimeout);
	        this.notifyHintTimeout = null;
	      }

	      if (main_core.Type.isNull(this.notifyHintPopup)) {
	        this.notifyHintPopup = new main_popup.Popup('sgm_notify_hint', node, {
	          autoHide: true,
	          lightShadow: true,
	          zIndex: 2,
	          content: main_core.Tag.render(_templateObject$1(), hintText),
	          closeByEsc: true,
	          closeIcon: false,
	          offsetLeft: 21,
	          offsetTop: 2
	        });
	        this.notifyHintPopup.TEXT = document.getElementById('sgm_notify_hint_text');
	        this.notifyHintPopup.setBindElement(node);
	      } else {
	        this.notifyHintPopup.TEXT.innerHTML = hintText;
	        this.notifyHintPopup.setBindElement(node);
	      }

	      this.notifyHintPopup.setAngle({});
	      this.notifyHintPopup.show();
	      this.notifyHintTimeout = setTimeout(function () {
	        _this3.notifyHintPopup.close();
	      }, this.notifyHintTime);
	    }
	  }]);
	  return WorkgroupCardSubscription;
	}();

	var WorkgroupCard = /*#__PURE__*/function () {
	  function WorkgroupCard() {
	    babelHelpers.classCallCheck(this, WorkgroupCard);
	    this.instance = null;
	    this.currentUserId = null;
	    this.userRole = null;
	    this.canInitiate = null;
	    this.canModify = null;
	    this.groupId = null;
	    this.isProject = null;
	    this.styles = null;
	    this.urls = null;
	    this.containerNode = null;
	    this.menuButtonNode = null;
	    this.editFeaturesAllowed = true;
	    this.favoritesInstance = null;
	  }

	  babelHelpers.createClass(WorkgroupCard, [{
	    key: "init",
	    value: function init(params) {
	      var _this = this;

	      if (main_core.Type.isUndefined(params) || main_core.Type.isUndefined(params.groupId) || parseInt(params.groupId) <= 0) {
	        return;
	      }

	      this.currentUserId = parseInt(params.currentUserId);
	      this.groupId = parseInt(params.groupId);
	      this.groupType = params.groupType;
	      this.isProject = !!params.isProject;
	      this.isOpened = !!params.isOpened;
	      this.canInitiate = !!params.canInitiate;
	      this.canProcessRequestsIn = !!params.canProcessRequestsIn;
	      this.canModify = !!params.canModify;
	      this.userRole = params.userRole;
	      this.userIsMember = !!params.userIsMember;
	      this.userIsAutoMember = !!params.userIsAutoMember;
	      this.containerNode = main_core.Type.isStringFilled(params.containerNodeId) ? document.getElementById(params.containerNodeId) : null;
	      this.menuButtonNode = main_core.Type.isStringFilled(params.menuButtonNodeId) ? document.getElementById(params.menuButtonNodeId) : null;
	      this.editFeaturesAllowed = !main_core.Type.isUndefined(params.editFeaturesAllowed) ? !!params.editFeaturesAllowed : true;
	      this.favoritesInstance = new WorkgroupCardFavorites({
	        groupId: this.groupId,
	        value: !!params.favoritesValue,
	        containerNode: this.containerNode,
	        styles: params.styles.fav
	      });
	      this.subscriptionInstance = new WorkgroupCardSubscription({
	        groupId: this.groupId,
	        buttonNode: main_core.Type.isStringFilled(params.subscribeButtonNodeId) ? document.getElementById(params.subscribeButtonNodeId) : null
	      });

	      if (this.containerNode && main_core.Type.isPlainObject(params.styles)) {
	        this.styles = params.styles;

	        if (main_core.Type.isPlainObject(params.styles.tags) && main_core.Type.isStringFilled(params.styles.tags.box)) {
	          this.containerNode.querySelectorAll(".".concat(params.styles.tags.box)).forEach(function (node) {
	            node.addEventListener('click', function (e) {
	              var tagValue = e.target.getAttribute('bx-tag-value');

	              if (main_core.Type.isStringFilled(tagValue)) {
	                _this.clickTag(tagValue);
	              }

	              e.preventDefault();
	            }, true);
	          });
	        }

	        if (main_core.Type.isPlainObject(params.styles.users) && main_core.Type.isStringFilled(params.styles.users.box) && main_core.Type.isStringFilled(params.styles.users.item)) {
	          this.containerNode.querySelectorAll(".".concat(params.styles.users.box)).forEach(function (node) {
	            node.addEventListener('click', function (e) {
	              var userNode = e.target;

	              if (!userNode.hasAttribute('bx-user-id')) {
	                userNode = userNode.closest(".".concat(params.styles.users.item));
	              }

	              var userId = userNode.getAttribute('bx-user-id');

	              if (parseInt(userId) > 0) {
	                _this.clickUser(userId);
	              }

	              e.preventDefault();
	            }, true);
	          });
	        }
	      }

	      if (main_core.Type.isPlainObject(params.urls)) {
	        this.urls = params.urls;
	      }

	      if (main_core.Type.isDomNode(this.menuButtonNode)) {
	        var sonetGroupMenu = BX.SocialnetworkUICommon.SonetGroupMenu.getInstance();
	        sonetGroupMenu.favoritesValue = this.favoritesInstance.getValue();
	        this.menuButtonNode.addEventListener('click', function () {
	          BX.SocialnetworkUICommon.showGroupMenuPopup({
	            bindElement: _this.menuButtonNode,
	            groupId: _this.groupId,
	            groupType: _this.groupType,
	            userIsMember: _this.userIsMember,
	            userIsAutoMember: _this.userIsAutoMember,
	            userRole: _this.userRole,
	            editFeaturesAllowed: _this.editFeaturesAllowed,
	            isProject: _this.isProject,
	            isOpened: _this.isOpened,
	            perms: {
	              canInitiate: _this.canInitiate,
	              canProcessRequestsIn: _this.canProcessRequestsIn,
	              canModify: _this.canModify
	            },
	            urls: {
	              requestUser: main_core.Loc.getMessage('SGCSPathToRequestUser'),
	              edit: main_core.Loc.getMessage('SGCSPathToEdit'),
	              delete: main_core.Loc.getMessage('SGCSPathToDelete'),
	              features: main_core.Loc.getMessage('SGCSPathToFeatures'),
	              members: main_core.Loc.getMessage('SGCSPathToMembers'),
	              requests: main_core.Loc.getMessage('SGCSPathToRequests'),
	              requestsOut: main_core.Loc.getMessage('SGCSPathToRequestsOut'),
	              userRequestGroup: main_core.Loc.getMessage('SGCSPathToUserRequestGroup'),
	              userLeaveGroup: main_core.Loc.getMessage('SGCSPathToUserLeaveGroup'),
	              copy: main_core.Loc.getMessage('SGCSPathToCopy')
	            }
	          });
	        }, true);
	      }

	      main_core_events.EventEmitter.subscribe('SidePanel.Slider:onMessage', this.onSliderMessage.bind(this));
	    }
	  }, {
	    key: "clickTag",
	    value: function clickTag(tagValue) {
	      if (!main_core.Type.isStringFilled(tagValue.length)) {
	        return;
	      }

	      top.location.href = main_core.Loc.getMessage('SGCSPathToGroupTag').replace('#tag#', tagValue);
	    }
	  }, {
	    key: "clickUser",
	    value: function clickUser(userId) {
	      if (parseInt(userId) <= 0) {
	        return;
	      }

	      top.location.href = main_core.Loc.getMessage('SGCSPathToUserProfile').replace('#user_id#', userId);
	    }
	  }, {
	    key: "onSliderMessage",
	    value: function onSliderMessage(event) {
	      var _event$getCompatData = event.getCompatData(),
	          _event$getCompatData2 = babelHelpers.slicedToArray(_event$getCompatData, 1),
	          sliderEvent = _event$getCompatData2[0];

	      if (sliderEvent.getEventId() !== 'sonetGroupEvent') {
	        return;
	      }

	      var eventData = sliderEvent.getData();

	      if (!main_core.Type.isStringFilled(eventData.code) || !main_core.Type.isPlainObject(eventData.data)) {
	        return;
	      }

	      if (eventData.code === 'afterEdit' && main_core.Type.isPlainObject(eventData.data.group) && parseInt(eventData.data.group.ID) === this.groupId) {
	        BX.SocialnetworkUICommon.reload();
	      } else if (['afterDelete', 'afterLeave'].includes(eventData.code) && !main_core.Type.isUndefined(eventData.data.groupId) && parseInt(eventData.data.groupId) === this.groupId) {
	        if (window !== top.window) // frame
	          {
	            top.BX.SidePanel.Instance.getSliderByWindow(window).close();
	          }

	        top.location.href = this.urls.groupsList;
	      }
	    }
	  }]);
	  return WorkgroupCard;
	}();

	exports.WorkgroupCard = WorkgroupCard;

}((this.BX.Socialnetwork = this.BX.Socialnetwork || {}),BX.Event,BX,BX.Main));
//# sourceMappingURL=script.js.map
