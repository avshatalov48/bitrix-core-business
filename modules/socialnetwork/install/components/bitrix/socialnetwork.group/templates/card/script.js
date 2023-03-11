this.BX = this.BX || {};
(function (exports,ui_graph_circle,socialnetwork_common,main_popup,main_core,main_core_events,main_loader) {
	'use strict';

	var _templateObject;

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
	      new main_popup.Popup("sgm-error".concat(Math.random()), null, {
	        autoHide: true,
	        lightShadow: false,
	        zIndex: 2,
	        content: main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["<div class=\"sonet-sgm-error-text-block\">", "</div>"])), errorText),
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
	    this.groupId = parseInt(params.groupId);

	    if (this.containerNode) {
	      main_core_events.EventEmitter.subscribe('BX.Socialnetwork.WorkgroupMenu:onSetFavorites', function (event) {
	        var _event$getCompatData = event.getCompatData(),
	            _event$getCompatData2 = babelHelpers.slicedToArray(_event$getCompatData, 1),
	            params = _event$getCompatData2[0];

	        _this.setValue(params.value);
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
	      var sonetGroupMenu = socialnetwork_common.SonetGroupMenu.getInstance();
	      this.setValue(newValue);
	      sonetGroupMenu.favoritesValue = newValue;
	      sonetGroupMenu.setItemTitle(newValue);
	      socialnetwork_common.Common.setFavoritesAjax({
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
	          }
	        }
	      });
	      event.preventDefault();
	    }
	  }]);
	  return WorkgroupCardFavorites;
	}();

	var _templateObject$1;

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
	      this["switch"](this.buttonNode, action === 'set');
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
	      })["catch"](function (response) {
	        _this2["switch"](_this2.buttonNode, !(action === 'set'));

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
	        node.classList.remove('ui-btn-icon-unfollow');
	        node.classList.add('ui-btn-icon-follow');
	        this.showNotifyHint(node, main_core.Loc.getMessage('SGCSSubscribeButtonHintOn'));
	      } else {
	        node.classList.remove('ui-btn-active');
	        node.classList.add('ui-btn-icon-unfollow');
	        node.classList.remove('ui-btn-icon-follow');
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
	          content: main_core.Tag.render(_templateObject$1 || (_templateObject$1 = babelHelpers.taggedTemplateLiteral(["<div class=\"sonet-sgm-notify-hint-content\" style=\"display: none;\"><span id=\"sgm_notify_hint_text\">", "</span></div>"])), hintText),
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

	var WorkgroupCardThemePicker = /*#__PURE__*/function () {
	  function WorkgroupCardThemePicker(params) {
	    babelHelpers.classCallCheck(this, WorkgroupCardThemePicker);
	    this.containerNode = params.containerNode;
	    this.init();
	  }

	  babelHelpers.createClass(WorkgroupCardThemePicker, [{
	    key: "init",
	    value: function init() {
	      var _this = this;

	      if (!this.containerNode) {
	        return;
	      }

	      var themePickerInstance = BX.Intranet.Bitrix24.ThemePicker.Singleton;
	      var themePickerNode = this.containerNode.querySelector('.socialnetwork-group-slider-theme-btn');

	      if (themePickerNode) {
	        themePickerNode.addEventListener('click', function () {
	          themePickerInstance.showDialog(true);
	        });
	      }

	      main_core_events.EventEmitter.subscribe('Intranet.ThemePicker:onSave', function (event) {
	        var _event$getData = event.getData(),
	            _event$getData2 = babelHelpers.slicedToArray(_event$getData, 1),
	            data = _event$getData2[0];

	        if (!main_core.Type.isPlainObject(data.theme) || window === top.window) {
	          return;
	        }

	        themePickerInstance.applyTheme(data.theme.id);
	        themePickerInstance.saveTheme(data.theme.id);

	        _this.draw(data.theme);
	      });
	    }
	  }, {
	    key: "draw",
	    value: function draw(theme) {
	      var themeBoxNode = this.containerNode.querySelector('.socialnetwork-group-slider-theme-box');

	      if (!themeBoxNode) {
	        return;
	      }

	      themeBoxNode.style.backgroundImage = main_core.Type.isStringFilled(theme.previewImage) ? "url('".concat(theme.previewImage, "')") : 'none';
	      themeBoxNode.style.backgroundColor = main_core.Type.isStringFilled(theme.previewColor) ? theme.previewColor : 'transparent';
	    }
	  }]);
	  return WorkgroupCardThemePicker;
	}();

	var WorkgroupCardAvatar = /*#__PURE__*/function () {
	  function WorkgroupCardAvatar(params) {
	    var _params$componentName, _params$signedParamet;

	    babelHelpers.classCallCheck(this, WorkgroupCardAvatar);
	    this.componentName = (_params$componentName = params.componentName) !== null && _params$componentName !== void 0 ? _params$componentName : '';
	    this.signedParameters = (_params$signedParamet = params.signedParameters) !== null && _params$signedParamet !== void 0 ? _params$signedParamet : '';
	    this.containerNode = params.containerNode;
	    this.groupId = parseInt(params.groupId);
	    this.init();
	  }

	  babelHelpers.createClass(WorkgroupCardAvatar, [{
	    key: "init",
	    value: function init() {
	      var _this = this;

	      if (!this.containerNode) {
	        return;
	      }

	      var avatarEditor = new BX.AvatarEditor({
	        enableCamera: false
	      });
	      var editButtonNode = this.containerNode.querySelector('.socialnetwork-group-slider-group-logo-btn');

	      if (!editButtonNode) {
	        return;
	      }

	      editButtonNode.addEventListener('click', function () {
	        avatarEditor.show('file');
	      });
	      main_core_events.EventEmitter.subscribe(avatarEditor, 'onApply', function (event) {
	        var _event$getCompatData = event.getCompatData(),
	            _event$getCompatData2 = babelHelpers.slicedToArray(_event$getCompatData, 1),
	            file = _event$getCompatData2[0];

	        var formData = new FormData();

	        if (!file.name) {
	          file.name = 'tmp.png';
	        }

	        formData.append('newPhoto', file, file.name);

	        _this.changePhoto(formData);
	      });
	    }
	  }, {
	    key: "changePhoto",
	    value: function changePhoto(formData) {
	      var _this2 = this;

	      if (this.componentName === '') {
	        return;
	      }

	      var loader = null;
	      var boxNode = this.containerNode.querySelector('.socialnetwork-group-slider-group-logo-box');

	      if (boxNode) {
	        loader = this.showLoader({
	          node: boxNode,
	          loader: null,
	          size: 50
	        });
	      }

	      main_core.ajax.runComponentAction(this.componentName, 'loadPhoto', {
	        signedParameters: this.signedParameters,
	        mode: 'ajax',
	        data: formData
	      }).then(function (response) {
	        if (main_core.Type.isPlainObject(response.data)) {
	          if (!boxNode) {
	            return;
	          }

	          var avatarNode = boxNode.querySelector('i');

	          if (!avatarNode) {
	            return;
	          }

	          boxNode.className = 'sonet-common-workgroup-avatar socialnetwork-group-slider-group-logo-box';

	          if (main_core.Type.isStringFilled(response.data.imageSrc)) {
	            boxNode.className += ' ui-icon ui-icon-common-user-group';
	            avatarNode.style = "background: url('".concat(encodeURI(response.data.imageSrc), "') no-repeat center center; background-size: cover;");
	          } else {
	            avatarNode.style = 'background: none;';
	          }
	        }

	        _this2.hideLoader({
	          loader: loader
	        });
	      }, function (response) {
	        _this2.hideLoader({
	          loader: loader
	        }); //			this.showErrorPopup(response["errors"][0].message);

	      });
	    }
	  }, {
	    key: "showLoader",
	    value: function showLoader(params) {
	      var loader = null;

	      if (main_core.Type.isDomNode(params.node)) {
	        if (params.loader === null) {
	          loader = new main_loader.Loader({
	            target: params.node,
	            size: !main_core.Type.isUndefined(params.size) ? Number(params.size) : 40
	          });
	        } else {
	          loader = params.loader;
	        }

	        loader.show();
	      }

	      return loader;
	    }
	  }, {
	    key: "hideLoader",
	    value: function hideLoader(params) {
	      if (params.loader !== null) {
	        params.loader.hide();
	      }

	      if (params.loader !== null) {
	        params.loader = null;
	      }
	    }
	  }]);
	  return WorkgroupCardAvatar;
	}();

	var WorkgroupCard = /*#__PURE__*/function () {
	  function WorkgroupCard() {
	    babelHelpers.classCallCheck(this, WorkgroupCard);
	    this.componentName = '';
	    this.signedParameters = '';
	    this.instance = null;
	    this.currentUserId = null;
	    this.userRole = null;
	    this.initiatedByType = null;
	    this.initiatedByUserId = null;
	    this.userIsMember = null;
	    this.userIsAutoMember = null;
	    this.userIsScrumMaster = null;
	    this.canInitiate = null;
	    this.canModify = null;
	    this.canLeave = null;
	    this.groupId = null;
	    this.isProject = null;
	    this.isScrumProject = null;
	    this.styles = null;
	    this.urls = null;
	    this.containerNode = null;
	    this.menuButtonNode = null;
	    this.editFeaturesAllowed = true;
	    this.copyFeatureAllowed = true;
	    this.favoritesInstance = null;
	  }

	  babelHelpers.createClass(WorkgroupCard, [{
	    key: "init",
	    value: function init(params) {
	      var _params$componentName,
	          _params$signedParamet,
	          _this = this;

	      if (main_core.Type.isUndefined(params) || main_core.Type.isUndefined(params.groupId) || parseInt(params.groupId) <= 0) {
	        return;
	      }

	      this.componentName = (_params$componentName = params.componentName) !== null && _params$componentName !== void 0 ? _params$componentName : '';
	      this.signedParameters = (_params$signedParamet = params.signedParameters) !== null && _params$signedParamet !== void 0 ? _params$signedParamet : '';
	      this.currentUserId = parseInt(params.currentUserId);
	      this.groupId = parseInt(params.groupId);
	      this.groupType = params.groupType;
	      this.isProject = !!params.isProject;
	      this.isScrumProject = !!params.isScrumProject;
	      this.isOpened = !!params.isOpened;
	      this.userRole = params.userRole;
	      this.initiatedByType = params.initiatedByType;
	      this.initiatedByUserId = parseInt(params.initiatedByUserId);
	      this.userIsMember = !!params.userIsMember;
	      this.userIsAutoMember = !!params.userIsAutoMember;
	      this.userIsScrumMaster = this.isScrumProject && (main_core.Type.isBoolean(params.userIsScrumMaster) ? params.userIsScrumMaster : false);
	      this.canInitiate = !!params.canInitiate;
	      this.canProcessRequestsIn = !!params.canProcessRequestsIn;
	      this.canModify = !!params.canModify;
	      this.canLeave = main_core.Type.isBoolean(params.canLeave) ? params.canLeave : this.userIsMember && this.userRole !== 'A' && !this.userIsAutoMember && !this.userIsScrumMaster;
	      this.containerNode = main_core.Type.isStringFilled(params.containerNodeId) ? document.getElementById(params.containerNodeId) : null;
	      this.menuButtonNode = main_core.Type.isStringFilled(params.menuButtonNodeId) ? document.getElementById(params.menuButtonNodeId) : null;
	      this.editFeaturesAllowed = !main_core.Type.isUndefined(params.editFeaturesAllowed) ? !!params.editFeaturesAllowed : true;
	      this.copyFeatureAllowed = !main_core.Type.isUndefined(params.copyFeatureAllowed) ? !!params.copyFeatureAllowed : true;
	      this.favoritesInstance = new WorkgroupCardFavorites({
	        groupId: this.groupId,
	        value: !!params.favoritesValue,
	        containerNode: this.containerNode
	      });
	      this.subscriptionInstance = new WorkgroupCardSubscription({
	        groupId: this.groupId,
	        buttonNode: main_core.Type.isStringFilled(params.subscribeButtonNodeId) ? document.getElementById(params.subscribeButtonNodeId) : null
	      });
	      new WorkgroupCardThemePicker({
	        containerNode: this.containerNode
	      });
	      new WorkgroupCardAvatar({
	        componentName: this.componentName,
	        signedParameters: this.signedParameters,
	        containerNode: this.containerNode,
	        groupId: this.groupId
	      });

	      if (this.containerNode && main_core.Type.isPlainObject(params.styles)) {
	        this.styles = params.styles;

	        if (main_core.Type.isPlainObject(params.styles.tags) && main_core.Type.isStringFilled(params.styles.tags.box)) {
	          this.containerNode.querySelectorAll('[bx-tag-value]').forEach(function (node) {
	            node.addEventListener('click', function (e) {
	              var tagValue = e.target.getAttribute('bx-tag-value');

	              if (main_core.Type.isStringFilled(tagValue)) {
	                _this.clickTag(tagValue);
	              }

	              e.preventDefault();
	            }, true);
	          });
	        }

	        if (main_core.Type.isPlainObject(params.tasksEfficiency) && params.tasksEfficiency.available === true) {
	          var circleNode = this.containerNode.querySelector('.socialnetwork-group-slider-efficency');

	          if (circleNode) {
	            var circle = new ui_graph_circle.Circle(circleNode, 131, Number(params.tasksEfficiency.value), null, null);
	            circle.show();
	          }
	        }

	        var efficiencyHelperNode = this.containerNode.querySelector('[data-role="efficiency-helper"]');

	        if (efficiencyHelperNode) {
	          efficiencyHelperNode.addEventListener('click', function () {
	            top.BX.Helper.show('redirect=detail&code=6576263');
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
	            userIsScrumMaster: _this.userIsScrumMaster,
	            userRole: _this.userRole,
	            initiatedByType: _this.initiatedByType,
	            initiatedByUserId: _this.initiatedByUserId,
	            editFeaturesAllowed: _this.editFeaturesAllowed,
	            copyFeatureAllowed: _this.copyFeatureAllowed,
	            isProject: _this.isProject,
	            isScrumProject: _this.isScrumProject,
	            isOpened: _this.isOpened,
	            perms: {
	              canInitiate: _this.canInitiate,
	              canProcessRequestsIn: _this.canProcessRequestsIn,
	              canModify: _this.canModify,
	              canLeave: _this.canLeave
	            },
	            urls: {
	              requestUser: main_core.Loc.getMessage('SGCSPathToRequestUser'),
	              edit: main_core.Loc.getMessage('SGCSPathToEdit'),
	              "delete": main_core.Loc.getMessage('SGCSPathToDelete'),
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
	      if (!main_core.Type.isStringFilled(tagValue)) {
	        return;
	      }

	      top.location.href = main_core.Loc.getMessage('SGCSPathToGroupTag').replace('#tag#', tagValue);
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

	      if (eventData.code === 'afterJoinRequestSend' && parseInt(eventData.data.groupId) === this.groupId) {
	        BX.SocialnetworkUICommon.reload();
	      } else if (eventData.code === 'afterEdit' && main_core.Type.isPlainObject(eventData.data.group) && parseInt(eventData.data.group.ID) === this.groupId) {
	        BX.SocialnetworkUICommon.reload();
	      } else if (['afterDelete', 'afterLeave', 'afterIncomingRequestCancel'].includes(eventData.code) && !main_core.Type.isUndefined(eventData.data.groupId) && parseInt(eventData.data.groupId) === this.groupId) {
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

}((this.BX.Socialnetwork = this.BX.Socialnetwork || {}),BX.UI.Graph,BX.Socialnetwork.UI,BX.Main,BX,BX.Event,BX));
//# sourceMappingURL=script.js.map
