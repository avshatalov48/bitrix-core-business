this.BX = this.BX || {};
this.BX.Socialnetwork = this.BX.Socialnetwork || {};
(function (exports,im_public,main_popup,ui_buttons,ui_popupcomponentsmaker,main_core) {
	'use strict';

	var Waiter = /*#__PURE__*/function () {
	  babelHelpers.createClass(Waiter, null, [{
	    key: "getInstance",
	    value: function getInstance() {
	      if (main_core.Type.isNull(Waiter.instance)) {
	        Waiter.instance = new Waiter();
	      }
	      return Waiter.instance;
	    }
	  }]);
	  function Waiter() {
	    babelHelpers.classCallCheck(this, Waiter);
	    this.waitTimeout = null;
	    this.waitPopup = null;
	  }
	  babelHelpers.createClass(Waiter, [{
	    key: "show",
	    value: function show(timeout) {
	      var _this = this;
	      if (timeout !== 0) {
	        return this.waitTimeout = setTimeout(function () {
	          _this.show(0);
	        }, 50);
	      }
	      if (!this.waitPopup) {
	        this.waitPopup = new BX.PopupWindow('sonet_common_wait_popup', window, {
	          autoHide: true,
	          lightShadow: true,
	          zIndex: 2,
	          content: BX.create('DIV', {
	            props: {
	              className: 'sonet-wait-cont'
	            },
	            children: [BX.create('DIV', {
	              props: {
	                className: 'sonet-wait-icon'
	              }
	            }), BX.create('DIV', {
	              props: {
	                className: 'sonet-wait-text'
	              },
	              html: BX.message('SONET_EXT_COMMON_WAIT')
	            })]
	          })
	        });
	      } else {
	        this.waitPopup.setBindElement(window);
	      }
	      this.waitPopup.show();
	    }
	  }, {
	    key: "hide",
	    value: function hide() {
	      if (this.waitTimeout) {
	        clearTimeout(this.waitTimeout);
	        this.waitTimeout = null;
	      }
	      if (this.waitPopup) {
	        this.waitPopup.close();
	      }
	    }
	  }]);
	  return Waiter;
	}();
	babelHelpers.defineProperty(Waiter, "instance", null);

	var SonetGroupMenu = /*#__PURE__*/function () {
	  function SonetGroupMenu() {
	    babelHelpers.classCallCheck(this, SonetGroupMenu);
	    this.menuPopup = null;
	    this.menuItem = null;
	    this.favoritesValue = null;
	  }
	  babelHelpers.createClass(SonetGroupMenu, [{
	    key: "setItemTitle",
	    value: function setItemTitle(value) {
	      if (!main_core.Type.isDomNode(this.menuItem)) {
	        return;
	      }
	      this.menuItem.innerHTML = value ? main_core.Loc.getMessage('SONET_EXT_COMMON_GROUP_MENU_FAVORITES_REMOVE') : main_core.Loc.getMessage('SONET_EXT_COMMON_GROUP_MENU_FAVORITES_ADD');
	    }
	  }], [{
	    key: "getInstance",
	    value: function getInstance() {
	      var _this = this;
	      if (main_core.Type.isNull(this.instance)) {
	        this.instance = new SonetGroupMenu();
	        BX.addCustomEvent('SidePanel.Slider:onClose', function () {
	          if (_this.instance.menuPopup) {
	            _this.instance.menuPopup.close();
	          }
	        });
	        BX.addCustomEvent('BX.Socialnetwork.WorkgroupMenuIcon:onSetFavorites', function (params) {
	          _this.getInstance().setItemTitle(params.value);
	        });
	      }
	      return this.instance;
	    }
	  }]);
	  return SonetGroupMenu;
	}();
	babelHelpers.defineProperty(SonetGroupMenu, "instance", null);

	var RecallJoinRequest = /*#__PURE__*/function () {
	  function RecallJoinRequest(params) {
	    babelHelpers.classCallCheck(this, RecallJoinRequest);
	    this.successPopup = null;
	    this.groupId = !main_core.Type.isUndefined(params.GROUP_ID) ? Number(params.GROUP_ID) : 0;
	    this.relationId = !main_core.Type.isUndefined(params.RELATION_ID) ? Number(params.RELATION_ID) : 0;
	    this.urls = {
	      rejectOutgoingRequest: main_core.Type.isStringFilled(params.URL_REJECT_OUTGOING_REQUEST) ? params.URL_REJECT_OUTGOING_REQUEST : '',
	      groupsList: main_core.Type.isStringFilled(params.URL_GROUPS_LIST) ? params.URL_GROUPS_LIST : ''
	    };
	    this.project = main_core.Type.isBoolean(params.PROJECT) ? params.PROJECT : false;
	    this.scrum = main_core.Type.isBoolean(params.SCRUM) ? params.SCRUM : false;
	  }
	  babelHelpers.createClass(RecallJoinRequest, [{
	    key: "showPopup",
	    value: function showPopup() {
	      var _this = this;
	      if (this.relationId <= 0 || !main_core.Type.isStringFilled(this.urls.rejectOutgoingRequest)) {
	        return;
	      }
	      var recallTitle = main_core.Loc.getMessage('SONET_EXT_COMMON_RECALL_JOIN_POPUP_TITLE2');
	      var recallText = main_core.Loc.getMessage('SONET_EXT_COMMON_RECALL_JOIN_POPUP_TEXT2');
	      if (this.scrum) {
	        recallTitle = main_core.Loc.getMessage('SONET_EXT_COMMON_RECALL_JOIN_POPUP_TITLE2_SCRUM');
	        recallText = main_core.Loc.getMessage('SONET_EXT_COMMON_RECALL_JOIN_POPUP_TEXT2_SCRUM');
	      } else if (this.project) {
	        recallTitle = main_core.Loc.getMessage('SONET_EXT_COMMON_RECALL_JOIN_POPUP_TITLE2_PROJECT');
	        recallText = main_core.Loc.getMessage('SONET_EXT_COMMON_RECALL_JOIN_POPUP_TEXT2_PROJECT');
	      }
	      this.successPopup = new main_popup.Popup('bx-group-join-successfull-request-popup', window, {
	        width: 420,
	        autoHide: false,
	        lightShadow: false,
	        zIndex: 1000,
	        overlay: true,
	        cachable: false,
	        content: main_core.Dom.create('DIV', {
	          children: [main_core.Dom.create('DIV', {
	            text: recallTitle,
	            props: {
	              className: 'sonet-group-join-successfull-request-popup-title'
	            }
	          }), main_core.Dom.create('DIV', {
	            text: recallText,
	            props: {
	              className: 'sonet-group-join-successfull-request-popup-text'
	            }
	          })]
	        }),
	        buttons: [new ui_buttons.Button({
	          size: ui_buttons.Button.Size.MEDIUM,
	          text: main_core.Loc.getMessage('SONET_EXT_COMMON_RECALL_JOIN_POPUP_CLOSE_BUTTON'),
	          events: {
	            click: function click(button) {
	              _this.onClose(button.getContainer());
	            }
	          }
	        }), new ui_buttons.Button({
	          size: ui_buttons.Button.Size.MEDIUM,
	          color: ui_buttons.Button.Color.LINK,
	          text: main_core.Loc.getMessage('SONET_EXT_COMMON_RECALL_JOIN_POPUP_CANCEL_BUTTON'),
	          events: {
	            click: function click(button) {
	              _this.onCancelRequest(button.getContainer());
	            }
	          }
	        })],
	        closeByEsc: false,
	        closeIcon: false
	      });
	      this.successPopup.show();
	    }
	  }, {
	    key: "onClose",
	    value: function onClose(button) {
	      var _this2 = this;
	      if (this.groupId <= 0 || !main_core.Type.isDomNode(button)) {
	        return;
	      }
	      RecallJoinRequest.showButtonWait(button);
	      main_core.ajax.runAction('socialnetwork.api.usertogroup.setHideRequestPopup', {
	        data: {
	          groupId: this.groupId
	        }
	      }).then(function (response) {
	        RecallJoinRequest.hideButtonWait(button);
	        _this2.successPopup.close();
	      }, function () {
	        RecallJoinRequest.hideButtonWait(button);
	      });
	    }
	  }, {
	    key: "onCancelRequest",
	    value: function onCancelRequest(button) {
	      var _this3 = this;
	      if (this.groupId <= 0 || !main_core.Type.isDomNode(button)) {
	        return;
	      }
	      var errorNode = document.getElementById('bx-group-delete-request-error');
	      RecallJoinRequest.hideError(errorNode);
	      RecallJoinRequest.showButtonWait(button);
	      main_core.ajax.runAction('socialnetwork.api.usertogroup.cancelIncomingRequest', {
	        data: {
	          groupId: this.groupId,
	          userId: parseInt(main_core.Loc.getMessage('USER_ID'))
	        }
	      }).then(function (response) {
	        RecallJoinRequest.hideButtonWait(button);
	        _this3.successPopup.destroy();
	        if (main_core.Type.isStringFilled(_this3.urls.groupsList)) {
	          top.location.href = _this3.urls.groupsList;
	        }
	        _this3.reload();
	      })["catch"](function (response) {
	        RecallJoinRequest.showError(main_core.Loc.getMessage('SONET_EXT_COMMON_AJAX_ERROR'), errorNode);
	        //			RecallJoinRequest.showError(deleteResponseData.ERROR_MESSAGE, errorNode);
	        RecallJoinRequest.hideButtonWait(button);
	      });
	    }
	  }], [{
	    key: "showButtonWait",
	    value: function showButtonWait(buttonNode) {
	      if (main_core.Type.isStringFilled(buttonNode)) {
	        buttonNode = document.getElementById(buttonNode);
	      }
	      if (!main_core.Type.isDomNode(buttonNode)) {
	        return;
	      }
	      buttonNode.classList.add('ui-btn-clock');
	      buttonNode.disabled = true;
	      buttonNode.style.cursor = 'auto';
	    }
	  }, {
	    key: "hideButtonWait",
	    value: function hideButtonWait(buttonNode) {
	      if (main_core.Type.isStringFilled(buttonNode)) {
	        buttonNode = document.getElementById(buttonNode);
	      }
	      if (!main_core.Type.isDomNode(buttonNode)) {
	        return;
	      }
	      buttonNode.classList.remove('ui-btn-clock');
	      buttonNode.disabled = false;
	      buttonNode.style.cursor = 'cursor';
	    }
	  }, {
	    key: "showError",
	    value: function showError(errorText, errorNode) {
	      if (main_core.Type.isStringFilled(errorNode)) {
	        errorNode = document.getElementById(errorNode);
	      }
	      if (!main_core.Type.isDomNode(errorNode)) {
	        return;
	      }
	      errorNode.innerHTML = errorText;
	      errorNode.classList.remove('sonet-ui-form-error-block-invisible');
	    }
	  }, {
	    key: "hideError",
	    value: function hideError(errorNode) {
	      if (main_core.Type.isStringFilled(errorNode)) {
	        errorNode = document.getElementById(errorNode);
	      }
	      if (!main_core.Type.isDomNode(errorNode)) {
	        return;
	      }
	      errorNode.classList.add('sonet-ui-form-error-block-invisible');
	    }
	  }]);
	  return RecallJoinRequest;
	}();

	var Common = /*#__PURE__*/function () {
	  function Common() {
	    babelHelpers.classCallCheck(this, Common);
	  }
	  babelHelpers.createClass(Common, null, [{
	    key: "showGroupMenuPopup",
	    value: function showGroupMenuPopup(params) {
	      var _this = this;
	      var bindElement = params.bindElement;
	      if (main_core.Type.isStringFilled(bindElement)) {
	        bindElement = document.getElementById(bindElement);
	      }
	      var currentUserId = parseInt(main_core.Loc.getMessage('USER_ID'));
	      var sonetGroupMenu = SonetGroupMenu.getInstance();
	      if (bindElement.tagName === 'BUTTON') {
	        bindElement.classList.add('ui-btn-active');
	      }
	      var menu = [];
	      var itemTitle = '';
	      if (currentUserId > 0) {
	        menu.push({
	          text: !!sonetGroupMenu.favoritesValue ? main_core.Loc.getMessage('SONET_EXT_COMMON_GROUP_MENU_FAVORITES_REMOVE') : main_core.Loc.getMessage('SONET_EXT_COMMON_GROUP_MENU_FAVORITES_ADD'),
	          title: !!sonetGroupMenu.favoritesValue ? main_core.Loc.getMessage('SONET_EXT_COMMON_GROUP_MENU_FAVORITES_REMOVE') : main_core.Loc.getMessage('SONET_EXT_COMMON_GROUP_MENU_FAVORITES_ADD'),
	          id: 'set-group-favorite',
	          onclick: function onclick() {
	            var favoritesValue = sonetGroupMenu.favoritesValue;
	            sonetGroupMenu.setItemTitle(!favoritesValue);
	            sonetGroupMenu.favoritesValue = !favoritesValue;
	            _this.setFavoritesAjax({
	              groupId: params.groupId,
	              favoritesValue: favoritesValue,
	              callback: {
	                success: function success(data) {
	                  BX.onCustomEvent(window, 'BX.Socialnetwork.WorkgroupFavorites:onSet', [{
	                    id: params.groupId,
	                    name: data.NAME,
	                    url: data.URL,
	                    extranet: !main_core.Type.isUndefined(data.EXTRANET) ? data.EXTRANET : 'N'
	                  }, !favoritesValue]);
	                  BX.onCustomEvent(window, 'BX.Socialnetwork.WorkgroupMenu:onSetFavorites', [{
	                    groupId: params.groupId,
	                    value: !favoritesValue
	                  }]);
	                  window.top.BX.SidePanel.Instance.postMessageAll(window, 'sonetGroupEvent', {
	                    code: 'afterSetFavorites',
	                    data: {
	                      groupId: data.ID,
	                      value: data.RESULT === 'Y'
	                    }
	                  });
	                },
	                failure: function failure() {
	                  sonetGroupMenu.favoritesValue = favoritesValue;
	                  sonetGroupMenu.setItemTitle(favoritesValue);
	                }
	              }
	            });
	          }
	        });
	        if (params.perms.canInitiate) {
	          itemTitle = main_core.Loc.getMessage('SONET_EXT_COMMON_GROUP_MENU_REQU');
	          if (!!params.isScrumProject) {
	            itemTitle = main_core.Loc.getMessage('SONET_EXT_COMMON_GROUP_MENU_REQU_SCRUM');
	          } else if (!!params.isProject) {
	            itemTitle = main_core.Loc.getMessage('SONET_EXT_COMMON_GROUP_MENU_REQU_PROJECT');
	          }
	          menu.push({
	            text: itemTitle,
	            title: itemTitle,
	            href: params.urls.requestUser
	          });
	        }
	        if (params.perms.canModify) {
	          itemTitle = main_core.Loc.getMessage('SONET_EXT_COMMON_GROUP_MENU_EDIT');
	          if (!!params.isScrumProject) {
	            itemTitle = main_core.Loc.getMessage('SONET_EXT_COMMON_GROUP_MENU_EDIT_SCRUM');
	          } else if (!!params.isProject) {
	            itemTitle = main_core.Loc.getMessage('SONET_EXT_COMMON_GROUP_MENU_EDIT_PROJECT');
	          }
	          menu.push({
	            text: itemTitle,
	            title: itemTitle,
	            href: params.urls.edit
	          });
	          if (!params.hideArchiveLinks) {
	            var featuresItem = {
	              text: main_core.Loc.getMessage('SONET_EXT_COMMON_GROUP_MENU_FEAT'),
	              title: main_core.Loc.getMessage('SONET_EXT_COMMON_GROUP_MENU_FEAT')
	            };
	            if (params.editFeaturesAllowed) {
	              featuresItem.href = params.urls.features;
	            } else {
	              featuresItem.onclick = function () {
	                B24.licenseInfoPopup.show('sonetGroupFeatures', main_core.Loc.getMessage('SONET_EXT_COMMON_B24_SONET_GROUP_FEATURES_TITLE'), "<span>".concat(main_core.Loc.getMessage('SONET_EXT_COMMON_B24_SONET_GROUP_FEATURES_TEXT'), "</span>"), true);
	              };
	            }
	            menu.push(featuresItem);
	          }
	          itemTitle = main_core.Loc.getMessage('SONET_EXT_COMMON_GROUP_MENU_DELETE');
	          if (!!params.isScrumProject) {
	            itemTitle = main_core.Loc.getMessage('SONET_EXT_COMMON_GROUP_MENU_DELETE_SCRUM');
	          } else if (!!params.isProject) {
	            itemTitle = main_core.Loc.getMessage('SONET_EXT_COMMON_GROUP_MENU_DELETE_PROJECT');
	          }
	          menu.push({
	            text: itemTitle,
	            title: itemTitle,
	            href: params.urls["delete"]
	          });
	        }
	        menu.push({
	          text: params.perms.canModerate ? main_core.Loc.getMessage('SONET_EXT_COMMON_GROUP_MENU_MEMBERS_EDIT') : main_core.Loc.getMessage('SONET_EXT_COMMON_GROUP_MENU_MEMBERS_VIEW'),
	          title: params.perms.canModerate ? main_core.Loc.getMessage('SONET_EXT_COMMON_GROUP_MENU_MEMBERS_EDIT') : main_core.Loc.getMessage('SONET_EXT_COMMON_GROUP_MENU_MEMBERS_VIEW'),
	          href: params.urls.members
	        });
	        if (params.perms.canInitiate) {
	          if (params.perms.canProcessRequestsIn) {
	            menu.push({
	              text: main_core.Loc.getMessage('SONET_EXT_COMMON_GROUP_MENU_REQ_IN'),
	              title: main_core.Loc.getMessage('SONET_EXT_COMMON_GROUP_MENU_REQ_IN'),
	              href: params.urls.requests
	            });
	          }
	          itemTitle = main_core.Loc.getMessage('SONET_EXT_COMMON_GROUP_MENU_REQ_OUT');
	          if (!!params.isScrumProject) {
	            itemTitle = main_core.Loc.getMessage('SONET_EXT_COMMON_GROUP_MENU_REQ_OUT_SCRUM');
	          } else if (!!params.isProject) {
	            itemTitle = main_core.Loc.getMessage('SONET_EXT_COMMON_GROUP_MENU_REQ_OUT_PROJECT');
	          }
	          menu.push({
	            text: itemTitle,
	            title: itemTitle,
	            href: params.urls.requestsOut
	          });
	        }
	        if (params.perms.canModify) {
	          itemTitle = main_core.Loc.getMessage('SONET_EXT_COMMON_GROUP_MENU_COPY');
	          if (!!params.isScrumProject) {
	            itemTitle = main_core.Loc.getMessage('SONET_EXT_COMMON_GROUP_MENU_COPY_SCRUM');
	          } else if (!!params.isProject) {
	            itemTitle = main_core.Loc.getMessage('SONET_EXT_COMMON_GROUP_MENU_COPY_PROJECT');
	          }
	          var copyGroupItem = {
	            text: itemTitle,
	            title: itemTitle
	          };
	          if (params.copyFeatureAllowed) {
	            copyGroupItem.href = params.urls.copy;
	          } else {
	            copyGroupItem.onclick = function () {
	              if (!!params.isProject) {
	                BX.UI.InfoHelper.show('limit_task_copy_project', {
	                  isLimit: true,
	                  limitAnalyticsLabels: {
	                    module: 'socialnetwork',
	                    source: 'projectCardActions'
	                  }
	                });
	              } else {
	                BX.UI.InfoHelper.show('limit_task_copy_group', {
	                  isLimit: true,
	                  limitAnalyticsLabels: {
	                    module: 'socialnetwork',
	                    source: 'projectCardActions'
	                  }
	                });
	              }
	            };
	          }
	          if (!params.isScrumProject)
	            // todo remove after scrum copy will done
	            {
	              menu.push(copyGroupItem);
	            }
	        }
	        if ((!main_core.Type.isStringFilled(params.userRole) || params.userRole === main_core.Loc.getMessage('USER_TO_GROUP_ROLE_REQUEST') && params.initiatedByType === main_core.Loc.getMessage('USER_TO_GROUP_INITIATED_BY_GROUP')) && !params.hideArchiveLinks) {
	          itemTitle = main_core.Loc.getMessage('SONET_EXT_COMMON_GROUP_MENU_JOIN');
	          if (!!params.isScrumProject) {
	            itemTitle = main_core.Loc.getMessage('SONET_EXT_COMMON_GROUP_MENU_JOIN_SCRUM');
	          } else if (!!params.isProject) {
	            itemTitle = main_core.Loc.getMessage('SONET_EXT_COMMON_GROUP_MENU_JOIN_PROJECT');
	          }
	          var userRequestItem = {
	            text: itemTitle,
	            title: itemTitle
	          };
	          if (!!params.isOpened) {
	            userRequestItem.onclick = function () {
	              _this.sendJoinRequest(params);
	            };
	          } else {
	            userRequestItem.href = params.urls.userRequestGroup;
	          }
	          menu.push(userRequestItem);
	        }
	        if (main_core.Type.isStringFilled(params.userRole) && params.userRole === main_core.Loc.getMessage('USER_TO_GROUP_ROLE_REQUEST') && params.initiatedByType === main_core.Loc.getMessage('USER_TO_GROUP_INITIATED_BY_USER') && parseInt(params.initiatedByUserId) === currentUserId) {
	          itemTitle = main_core.Loc.getMessage('SONET_EXT_COMMON_GROUP_MENU_DELETE_REQUEST');
	          menu.push({
	            text: itemTitle,
	            title: itemTitle,
	            onclick: function onclick() {
	              _this.cancelIncomingRequest(params);
	            }
	          });
	        }
	        if (main_core.Type.isBoolean(params.perms.canLeave) && params.perms.canLeave || !main_core.Type.isBoolean(params.perms.canLeave) && params.userIsMember && !params.userIsAutoMember && params.userRole !== main_core.Loc.getMessage('USER_TO_GROUP_ROLE_OWNER')) {
	          itemTitle = main_core.Loc.getMessage('SONET_EXT_COMMON_GROUP_MENU_EXIT');
	          if (!!params.isScrumProject) {
	            itemTitle = main_core.Loc.getMessage('SONET_EXT_COMMON_GROUP_MENU_EXIT_SCRUM');
	          } else if (!!params.isProject) {
	            itemTitle = main_core.Loc.getMessage('SONET_EXT_COMMON_GROUP_MENU_EXIT_PROJECT');
	          }
	          menu.push({
	            text: itemTitle,
	            title: itemTitle,
	            href: params.urls.userLeaveGroup
	          });
	        }
	        if (params.canPickTheme) {
	          menu.push({
	            text: main_core.Loc.getMessage('SONET_EXT_COMMON_GROUP_MENU_THEME_DIALOG'),
	            title: main_core.Loc.getMessage('SONET_EXT_COMMON_GROUP_MENU_THEME_DIALOG'),
	            onclick: function onclick() {
	              BX.Intranet.Bitrix24.ThemePicker.Singleton.showDialog(false);
	            }
	          });
	        }
	      }
	      var popup = main_popup.MenuManager.create('group-profile-menu', bindElement, menu, {
	        offsetTop: 5,
	        offsetLeft: bindElement.offsetWidth - 18,
	        angle: true,
	        events: {
	          onPopupClose: function onPopupClose() {
	            if (bindElement.tagName === 'BUTTON') {
	              bindElement.classList.remove('ui-btn-active');
	            }
	          }
	        }
	      });
	      var item = popup.getMenuItem('set-group-favorite');
	      if (item) {
	        sonetGroupMenu.menuItem = item.layout.text;
	      }
	      popup.popupWindow.show();
	      sonetGroupMenu.menuPopup = popup;
	    }
	  }, {
	    key: "sendJoinRequest",
	    value: function sendJoinRequest(params) {
	      Waiter.getInstance().show();
	      if (SonetGroupMenu.getInstance() && SonetGroupMenu.getInstance().menuPopup) {
	        SonetGroupMenu.getInstance().menuPopup.close();
	      }
	      main_core.ajax({
	        url: params.urls.userRequestGroup,
	        method: 'POST',
	        dataType: 'json',
	        data: {
	          groupID: params.groupId,
	          MESSAGE: '',
	          ajax_request: 'Y',
	          save: 'Y',
	          sessid: main_core.Loc.getMessage('bitrix_sessid')
	        },
	        onsuccess: function onsuccess(responseData) {
	          Waiter.getInstance().hide();
	          if (main_core.Type.isStringFilled(responseData.MESSAGE) && responseData.MESSAGE === 'SUCCESS' && main_core.Type.isStringFilled(responseData.URL)) {
	            BX.onCustomEvent(window.top, 'sonetGroupEvent', [{
	              code: 'afterJoinRequestSend',
	              data: {
	                groupId: params.groupId
	              }
	            }]);
	            top.location.href = responseData.URL;
	          }
	        },
	        onfailure: function onfailure() {
	          Waiter.getInstance().hide();
	        }
	      });
	    }
	  }, {
	    key: "cancelIncomingRequest",
	    value: function cancelIncomingRequest(params) {
	      var _this2 = this;
	      Waiter.getInstance().show();
	      if (SonetGroupMenu.getInstance() && SonetGroupMenu.getInstance().menuPopup) {
	        SonetGroupMenu.getInstance().menuPopup.close();
	      }
	      main_core.ajax.runAction('socialnetwork.api.usertogroup.cancelIncomingRequest', {
	        data: {
	          groupId: params.groupId,
	          userId: parseInt(main_core.Loc.getMessage('USER_ID'))
	        }
	      }).then(function (response) {
	        Waiter.getInstance().hide();
	        window.top.BX.SidePanel.Instance.postMessageAll(window, 'sonetGroupEvent', {
	          code: 'afterIncomingRequestCancel',
	          data: {
	            groupId: params.groupId
	          }
	        });
	        _this2.reload();
	      })["catch"](function (response) {
	        Waiter.getInstance().hide();
	      });
	    }
	  }, {
	    key: "setFavoritesAjax",
	    value: function setFavoritesAjax(params) {
	      main_core.ajax.runAction('socialnetwork.api.workgroup.setFavorites', {
	        data: {
	          params: {
	            groupId: params.groupId,
	            value: params.favoritesValue === false ? 'Y' : 'N',
	            getAdditionalResultData: true
	          }
	        },
	        analyticsLabel: {
	          b24statAction: params.favoritesValue ? 'removeFavSonetGroup' : 'addFavSonetGroup'
	        }
	      }).then(function (response) {
	        params.callback.success(response.data);
	      })["catch"](function (response) {
	        params.callback.failure({
	          ERROR: response.errors[0].message
	        });
	      });
	    }
	  }, {
	    key: "reload",
	    value: function reload() {
	      if (top !== window)
	        // current page in slider
	        {
	          if (!main_core.Type.isUndefined(top.BX.SidePanel)) {
	            top.BX.SidePanel.Instance.getSliderByWindow(window).showLoader();
	          }
	          window.location.reload();
	        } else if (!main_core.Type.isUndefined(top.BX.SidePanel) && top.BX.SidePanel.Instance.isOpen())
	        // there's an open slider
	        {
	          top.location.href = top.BX.SidePanel.Instance.getPageUrl();
	        } else {
	        top.location.reload();
	      }
	    }
	  }, {
	    key: "reloadBlock",
	    value: function reloadBlock(params) {
	      if (!main_core.Type.isPlainObject(params) || !main_core.Type.isStringFilled(params.blockId) || !document.getElementById(params.blockId)) {
	        return;
	      }
	      var url = '';
	      if (!main_core.Type.isUndefined(top.BX.SidePanel) && top.BX.SidePanel.Instance.isOpen())
	        // there's an open slider
	        {
	          url = top.BX.SidePanel.Instance.getPageUrl();
	        } else {
	        url = window.location.href;
	      }
	      main_core.ajax.promise({
	        url: url,
	        method: 'POST',
	        dataType: 'json',
	        data: {
	          BLOCK_RELOAD: 'Y',
	          BLOCK_ID: params.blockId
	        }
	      }).then(function (data) {
	        if (!main_core.Type.isUndefined(data.CONTENT)) {
	          document.getElementById(params.blockId).innerHTML = data.CONTENT;
	          setTimeout(function () {
	            main_core.ajax.processRequestData(data.CONTENT, {
	              dataType: 'HTML'
	            });
	          }, 0);
	        }
	      });
	    }
	  }, {
	    key: "closeGroupCardMenu",
	    value: function closeGroupCardMenu(node) {
	      if (!node) {
	        return;
	      }
	      var doc = node.ownerDocument;
	      var win = doc.defaultView || doc.parentWindow;
	      if (!win || main_core.Type.isUndefined(win.BX.Socialnetwork.UIGroupMenu) || !win.BX.Socialnetwork.UIGroupMenu.getInstance().menuPopup) {
	        return;
	      }
	      win.BX.Socialnetwork.UIGroupMenu.getInstance().menuPopup.close();
	    }
	  }, {
	    key: "openMessenger",
	    value: function openMessenger(groupId) {
	      return main_core.ajax.runAction('socialnetwork.api.workgroup.getChatId', {
	        data: {
	          groupId: parseInt(groupId, 10)
	        }
	      }).then(function (response) {
	        if (response.data) {
	          im_public.Messenger.openChat("chat".concat(parseInt(response.data, 10)));
	        }
	      })["catch"](function () {});
	    }
	  }]);
	  return Common;
	}();
	babelHelpers.defineProperty(Common, "showError", RecallJoinRequest.showError);
	babelHelpers.defineProperty(Common, "hideError", RecallJoinRequest.hideError);
	babelHelpers.defineProperty(Common, "showButtonWait", RecallJoinRequest.showButtonWait);
	babelHelpers.defineProperty(Common, "hideButtonWait", RecallJoinRequest.hideButtonWait);

	var Widget = /*#__PURE__*/function () {
	  function Widget() {
	    babelHelpers.classCallCheck(this, Widget);
	    this.widget = null;
	  }
	  babelHelpers.createClass(Widget, [{
	    key: "show",
	    value: function show(targetNode) {
	      if (this.widget) {
	        if (this.widget.isShown()) {
	          this.widget.close();
	          return;
	        }
	      }
	      var data = this.getData({
	        targetNode: targetNode
	      });
	      if (main_core.Type.isNull(data)) {
	        return;
	      }
	      this.widget = this.getWidget({
	        targetNode: targetNode,
	        data: data
	      });
	      if (this.widget) {
	        this.widget.show();
	      }
	    }
	  }, {
	    key: "hide",
	    value: function hide() {
	      if (this.widget && this.widget.isShown()) {
	        this.widget.close();
	      }
	    }
	  }, {
	    key: "getData",
	    value: function getData(params) {
	      return {};
	    }
	  }, {
	    key: "getWidget",
	    value: function getWidget(params) {
	      return null;
	    }
	  }]);
	  return Widget;
	}();

	var _templateObject, _templateObject2, _templateObject3;
	var WorkgroupWidget = /*#__PURE__*/function (_Widget) {
	  babelHelpers.inherits(WorkgroupWidget, _Widget);
	  function WorkgroupWidget(params) {
	    var _this;
	    babelHelpers.classCallCheck(this, WorkgroupWidget);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(WorkgroupWidget).call(this));
	    _this.groupId = !main_core.Type.isUndefined(params.groupId) ? parseInt(params.groupId) : 0;
	    _this.avatarPath = main_core.Type.isStringFilled(params.avatarPath) ? params.avatarPath : '';
	    _this.avatarType = main_core.Type.isStringFilled(params.avatarType) ? params.avatarType : '';
	    _this.projectTypeCode = main_core.Type.isStringFilled(params.projectTypeCode) ? params.projectTypeCode : '';
	    _this.urls = main_core.Type.isPlainObject(params.urls) ? params.urls : {};
	    _this.perms = main_core.Type.isPlainObject(params.perms) ? params.perms : {};
	    return _this;
	  }
	  babelHelpers.createClass(WorkgroupWidget, [{
	    key: "getData",
	    value: function getData(params) {
	      var data = null;
	      var targetNode = params.targetNode;
	      if (!main_core.Type.isDomNode(targetNode)) {
	        return data;
	      }
	      data = targetNode.getAttribute('data-workgroup');
	      try {
	        data = JSON.parse(data);
	      } catch (err) {
	        data = null;
	      }
	      return data;
	    }
	  }, {
	    key: "getWidget",
	    value: function getWidget(params) {
	      var targetNode = main_core.Type.isDomNode(params.targetNode) ? params.targetNode : null;
	      if (!targetNode) {
	        return null;
	      }
	      var data = main_core.Type.isPlainObject(params.data) ? params.data : {};
	      return new ui_popupcomponentsmaker.PopupComponentsMaker({
	        target: targetNode,
	        content: [{
	          html: [{
	            html: this.renderAbout(data)
	          }]
	        }, {
	          html: [{
	            html: this.renderMembers(data)
	          }, {
	            html: this.renderRoles(data)
	          }]
	        }]
	      });
	    }
	  }, {
	    key: "renderAbout",
	    value: function renderAbout() {
	      var _this2 = this;
	      var avatar = '<i></i>';
	      if (main_core.Type.isStringFilled(this.avatarPath)) {
	        avatar = "<i style=\"background: #fff url('".concat(encodeURI(this.avatarPath), "') no-repeat; background-size: cover;\"></i>");
	      }
	      var title = '';
	      var description = '';
	      switch (this.projectTypeCode.toLowerCase()) {
	        case 'project':
	          title = main_core.Loc.getMessage('SONET_EXT_COMMON_WORKGROUP_WIDGET_ABOUT_TITLE_PROJECT');
	          description = main_core.Loc.getMessage('SONET_EXT_COMMON_WORKGROUP_WIDGET_ABOUT_DESCRIPTION_PROJECT');
	          break;
	        case 'scrum':
	          title = main_core.Loc.getMessage('SONET_EXT_COMMON_WORKGROUP_WIDGET_ABOUT_TITLE_SCRUM');
	          description = main_core.Loc.getMessage('SONET_EXT_COMMON_WORKGROUP_WIDGET_ABOUT_DESCRIPTION_SCRUM');
	          break;
	        default:
	          title = main_core.Loc.getMessage('SONET_EXT_COMMON_WORKGROUP_WIDGET_ABOUT_TITLE_GROUP');
	          description = main_core.Loc.getMessage('SONET_EXT_COMMON_WORKGROUP_WIDGET_ABOUT_DESCRIPTION_GROUP');
	      }
	      var classList = ['sonet-common-widget-avatar'];
	      if (!main_core.Type.isStringFilled(this.avatarPath) && main_core.Type.isStringFilled(this.avatarType)) {
	        classList.push('sonet-common-workgroup-avatar');
	        classList.push("--".concat(this.avatarType));
	      } else {
	        classList.push('ui-icon');
	        classList.push('ui-icon-common-user-group');
	      }
	      var node = main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"sonet-common-widget-item\">\n\t\t\t\t<div class=\"sonet-common-widget-item-container\">\n\t\t\t\t\t<div class=\"", "\">", "</div>\n\t\t\t\t\t<div class=\"sonet-common-widget-item-content\">\n\t\t\t\t\t\t<div class=\"sonet-common-widget-item-title\">", "</div>\n\t\t\t\t\t\t<div class=\"sonet-common-widget-item-description\">", "</div>\t\t\t\t\t\t\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t"])), classList.join(' '), avatar, title, description);
	      main_core.Event.bind(node, 'click', function () {
	        if (!main_core.Type.isStringFilled(_this2.urls.card)) {
	          return;
	        }
	        BX.SidePanel.Instance.open(_this2.urls.card, {
	          width: 900,
	          loader: 'socialnetwork:group-card'
	        });
	        _this2.hide();
	      });
	      return node;
	    }
	  }, {
	    key: "renderMembers",
	    value: function renderMembers() {
	      var _this3 = this;
	      var node = main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"sonet-common-widget-item\">\n\t\t\t\t<div class=\"sonet-common-widget-item-container\">\n\t\t\t\t\t<div class=\"sonet-common-widget-icon ui-icon ui-icon-common-light-company\"><i></i></div>\n\t\t\t\t\t<div class=\"sonet-common-widget-item-content\">\n\t\t\t\t\t\t<div class=\"sonet-common-widget-item-title\">", "</div>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t"])), main_core.Loc.getMessage('SONET_EXT_COMMON_WORKGROUP_WIDGET_MEMBERS_TITLE'));
	      main_core.Event.bind(node, 'click', function () {
	        if (!main_core.Type.isStringFilled(_this3.urls.members)) {
	          return;
	        }
	        BX.SidePanel.Instance.open(_this3.urls.members, {
	          width: 1200,
	          loader: 'group-users-loader'
	        });
	        _this3.hide();
	      });
	      return node;
	    }
	  }, {
	    key: "renderRoles",
	    value: function renderRoles() {
	      var _this4 = this;
	      var canOpen = main_core.Type.isBoolean(this.perms.canModify) && this.perms.canModify;
	      var hint = !canOpen ? "data-hint=\"".concat(main_core.Loc.getMessage('SONET_EXT_COMMON_WORKGROUP_WIDGET_ROLES_TITLE_NO_PERMISSIONS'), "\" data-hint-no-icon") : '';
	      var node = main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"sonet-common-widget-item\" ", ">\n\t\t\t\t<div class=\"sonet-common-widget-item-container\">\n\t\t\t\t\t<div class=\"sonet-common-widget-icon ui-icon ui-icon-service-light-roles-rights\"><i></i></div>\n\t\t\t\t\t<div class=\"sonet-common-widget-item-content\">\n\t\t\t\t\t\t<div class=\"sonet-common-widget-item-title\">", "</div>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t"])), hint, main_core.Loc.getMessage('SONET_EXT_COMMON_WORKGROUP_WIDGET_ROLES_TITLE'));
	      main_core.Event.bind(node, 'click', function () {
	        if (!canOpen || !main_core.Type.isStringFilled(_this4.urls.features)) {
	          return;
	        }
	        BX.SidePanel.Instance.open(_this4.urls.features, {
	          width: 800,
	          loader: 'group-features-loader'
	        });
	        _this4.hide();
	      });
	      return node;
	    }
	  }]);
	  return WorkgroupWidget;
	}(Widget);

	/** @deprecated use BX.Socialnetwork.UI.Common */
	BX.SocialnetworkUICommon = Common;

	/** @deprecated use BX.Socialnetwork.UI.Waiter */
	BX.SocialnetworkUICommon.Waiter = Waiter;

	/** @deprecated use BX.Socialnetwork.UI.GroupMenu */
	BX.SocialnetworkUICommon.SonetGroupMenu = SonetGroupMenu;

	/** @deprecated use BX.Socialnetwork.UI.WorkgroupWidget */
	BX.Socialnetwork.UIWorkgroupWidget = WorkgroupWidget;

	exports.Common = Common;
	exports.Waiter = Waiter;
	exports.SonetGroupMenu = SonetGroupMenu;
	exports.WorkgroupWidget = WorkgroupWidget;
	exports.RecallJoinRequest = RecallJoinRequest;

}((this.BX.Socialnetwork.UI = this.BX.Socialnetwork.UI || {}),BX.Messenger.v2.Lib,BX.Main,BX.UI,BX.UI,BX));
//# sourceMappingURL=common.bundle.js.map
