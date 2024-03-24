this.BX = this.BX || {};
this.BX.Socialnetwork = this.BX.Socialnetwork || {};
(function (exports,main_core_events,main_core,main_popup,ui_buttons) {
	'use strict';

	var _templateObject;
	var ActionManager = /*#__PURE__*/function () {
	  function ActionManager(params) {
	    babelHelpers.classCallCheck(this, ActionManager);
	    this.parent = params.parent;
	    this.componentName = main_core.Type.isStringFilled(this.parent.componentName) ? this.parent.componentName : '';
	    this.signedParameters = main_core.Type.isStringFilled(this.parent.signedParameters) ? this.parent.signedParameters : '';
	    this.gridId = main_core.Type.isStringFilled(this.parent.gridId) ? this.parent.gridId : '';
	    this.useSlider = main_core.Type.isBoolean(this.parent.useSlider) ? this.parent.useSlider : false;
	  }
	  babelHelpers.createClass(ActionManager, [{
	    key: "viewProfile",
	    value: function viewProfile(params) {
	      var userId = parseInt(!main_core.Type.isUndefined(params.userId) ? params.userId : 0);
	      var pathToUser = main_core.Type.isStringFilled(params.pathToUser) ? params.pathToUser : '';
	      if (userId <= 0 || !main_core.Type.isStringFilled(pathToUser)) {
	        return;
	      }
	      pathToUser = pathToUser.replace('#ID#', userId).replace('#USER_ID#', userId).replace('#user_id#', userId);
	      if (this.useSlider) {
	        BX.SidePanel.Instance.open(pathToUser, {
	          cacheable: false,
	          allowChangeHistory: true,
	          contentClassName: 'bitrix24-profile-slider-content',
	          loader: 'intranet:profile',
	          width: 1100
	        });
	      } else {
	        window.location.href = pathToUser;
	      }
	    }
	  }, {
	    key: "act",
	    value: function act(params) {
	      var _this = this;
	      return new Promise(function (resolve, reject) {
	        return main_core.ajax.runComponentAction(_this.componentName, 'act', {
	          mode: 'class',
	          signedParameters: _this.signedParameters,
	          data: {
	            action: params.action,
	            fields: {
	              userId: params.userId
	            }
	          }
	        }).then(function (response) {
	          if (response.data.success) {
	            _this.processActionSuccess(params);
	          } else {
	            _this.processActionFailure(params);
	          }
	          resolve(response);
	        }, function (response) {
	          if (response.errors) {
	            _this.processActionFailure(params, response.errors[0].message);
	          }
	          reject(response);
	        });
	      });
	    }
	  }, {
	    key: "processActionSuccess",
	    value: function processActionSuccess(params) {
	      var eventCode = null;
	      switch (params.action) {
	        case 'exclude':
	          eventCode = 'afterUserExclude';
	          break;
	        case 'setOwner':
	          eventCode = 'afterOwnerSet';
	          break;
	        case 'setScrumMaster':
	          eventCode = 'afterSetScrumMaster';
	          break;
	        case 'setModerator':
	          eventCode = 'afterModeratorAdd';
	          break;
	        case 'removeModerator':
	          eventCode = 'afterModeratorRemove';
	          break;
	        case 'acceptIncomingRequest':
	        case 'rejectIncomingRequest':
	          eventCode = 'afterRequestDelete';
	          break;
	        case 'deleteOutgoingRequest':
	          eventCode = 'afterRequestOutDelete';
	          break;
	        default:
	      }
	      if (eventCode && top.BX.SidePanel && window !== top.window) {
	        top.BX.SidePanel.Instance.postMessageAll(window, 'sonetGroupEvent', {
	          code: eventCode
	        });
	      }
	      if (params.action === 'reinvite') {
	        BX.UI.Notification.Center.notify({
	          content: main_core.Loc.getMessage('SOCIALNETWORK_GROUP_USER_LIST_ACTION_REINVITE_SUCCESS')
	        });
	      } else {
	        this.parent.reload();
	      }
	    }
	  }, {
	    key: "processActionFailure",
	    value: function processActionFailure(params, errorMessage) {
	      if (!main_core.Type.isStringFilled(errorMessage)) {
	        errorMessage = main_core.Loc.getMessage('SOCIALNETWORK_GROUP_USER_LIST_ACTION_FAILURE');
	      }
	      BX.UI.Notification.Center.notify({
	        content: errorMessage
	      });
	    }
	  }, {
	    key: "disconnectDepartment",
	    value: function disconnectDepartment(params) {
	      var _this2 = this;
	      var id = parseInt(!main_core.Type.isUndefined(params.id) ? params.id : 0);
	      if (id <= 0) {
	        return;
	      }
	      main_core.ajax.runComponentAction(this.componentName, 'disconnectDepartment', {
	        mode: 'class',
	        signedParameters: this.signedParameters,
	        data: {
	          fields: {
	            id: id
	          }
	        }
	      }).then(function (response) {
	        if (response.data.success) {
	          if (top.BX.SidePanel && window !== top.window) {
	            top.BX.SidePanel.Instance.postMessageAll(window, 'sonetGroupEvent', {
	              code: 'afterDeptUnconnect'
	            });
	          }
	          _this2.parent.reload();
	        }
	      });
	    }
	  }, {
	    key: "groupDelete",
	    value: function groupDelete() {
	      var _this3 = this;
	      var buttons = [new ui_buttons.SendButton({
	        text: main_core.Loc.getMessage('SOCIALNETWORK_GROUP_USER_LIST_GROUP_ACTION_BUTTON_DELETE'),
	        events: {
	          click: function click() {
	            main_popup.PopupManager.getCurrentPopup().destroy();
	            var gridInstance = BX.Main.gridManager.getInstanceById(_this3.gridId);
	            if (!gridInstance) {
	              return;
	            }
	            var data = {
	              ID: gridInstance.getRows().getSelectedIds(),
	              apply_filter: 'Y'
	            };
	            data[gridInstance.getActionKey()] = 'delete';
	            data[gridInstance.getForAllKey()] = 'N';
	            gridInstance.reloadTable('POST', data);
	          }
	        }
	      }), new ui_buttons.CancelButton({
	        events: {
	          click: function click() {
	            main_popup.PopupManager.getCurrentPopup().destroy();
	          }
	        }
	      })];
	      var confirmPopup = main_popup.PopupManager.create({
	        id: 'bx-sgul-group-delete-confirm',
	        autoHide: false,
	        closeByEsc: true,
	        buttons: buttons,
	        events: {
	          onPopupClose: function onPopupClose(popup) {
	            popup.destroy();
	          }
	        },
	        content: main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["<div>", "</div>"])), main_core.Loc.getMessage('SOCIALNETWORK_GROUP_USER_LIST_GROUP_ACTION_CONFIRM_TEXT')),
	        padding: 20
	      });
	      confirmPopup.show();
	    }
	  }]);
	  return ActionManager;
	}();

	var Manager = /*#__PURE__*/function () {
	  babelHelpers.createClass(Manager, null, [{
	    key: "getById",
	    value: function getById(id) {
	      return Manager.repo.get(id);
	    }
	  }]);
	  function Manager(params) {
	    babelHelpers.classCallCheck(this, Manager);
	    this.id = params.id;
	    this.componentName = params.componentName;
	    this.signedParameters = params.signedParameters;
	    this.useSlider = params.useSlider;
	    this.gridId = params.gridId;
	    this.filterId = main_core.Type.isStringFilled(params.filterId) ? params.filterId : null;
	    this.defaultFilterPresetId = main_core.Type.isStringFilled(params.defaultFilterPresetId) && !['requests_in', 'requests_out'].includes(params.defaultFilterPresetId) ? params.defaultFilterPresetId : 'tmp_filter';
	    this.defaultCounter = main_core.Type.isStringFilled(params.defaultCounter) ? params.defaultCounter : '';
	    this.gridContainer = main_core.Type.isStringFilled(params.gridContainerId) ? document.getElementById(params.gridContainerId) : null;
	    this.urls = params.urls;
	    this.actionManagerInstance = new ActionManager({
	      parent: this
	    });
	    Manager.repo.set(this.id, this);
	    this.init();
	  }
	  babelHelpers.createClass(Manager, [{
	    key: "init",
	    value: function init() {
	      this.initEvents();
	    }
	  }, {
	    key: "initEvents",
	    value: function initEvents() {
	      var _this = this;
	      if (this.gridContainer) {
	        this.gridContainer.addEventListener('click', this.processClickEvent.bind(this));
	      }
	      main_core_events.EventEmitter.subscribe('SidePanel.Slider:onMessage', function (event) {
	        var _event$getCompatData = event.getCompatData(),
	          _event$getCompatData2 = babelHelpers.slicedToArray(_event$getCompatData, 1),
	          sliderEvent = _event$getCompatData2[0];
	        if (sliderEvent.getEventId() === 'sonetGroupEvent' && !main_core.Type.isUndefined(sliderEvent.data) && main_core.Type.isStringFilled(sliderEvent.data.code) && ['afterInvite', 'afterIncomingRequestCancel'].includes(sliderEvent.data.code)) {
	          _this.reload();
	        }
	      });
	      main_core_events.EventEmitter.subscribe('Socialnetwork.Toolbar:onItem', function (event) {
	        var data = event.getData();
	        if (data.counter) {
	          if (data.counter.filter && data.counter.filterPresetId && data.counter.filter.filterManager) {
	            var filterApi = data.counter.filter.getFilter().getApi();
	            var filterManager = data.counter.filter.filterManager;
	            filterApi.setFilter({
	              preset_id: filterManager.getPreset().getCurrentPresetId() === data.counter.filterPresetId ? _this.defaultFilterPresetId : data.counter.filterPresetId
	            });
	          } else if (main_core.Type.isStringFilled(data.counter.type)) {
	            var url = '';
	            if (data.counter.type === _this.defaultCounter) {
	              url = _this.urls.users;
	            } else {
	              switch (data.counter.type) {
	                case 'workgroup_requests_in':
	                  url = _this.urls.requests;
	                  break;
	                case 'workgroup_requests_out':
	                  url = _this.urls.requestsOut;
	                  break;
	                default:
	              }
	            }
	            if (main_core.Type.isStringFilled(url)) {
	              window.location = url.replace('#group_id#', _this.g);
	            }
	          }
	        }
	      });
	      var filterInstance = BX.Main.filterManager.getById(this.filterId);
	      if (filterInstance) {
	        var filterEmitter = filterInstance.getEmitter();
	        var filterApi = filterInstance.getApi();
	        filterEmitter.subscribe('init', function (event) {
	          var _event$getData = event.getData(),
	            field = _event$getData.field;
	          if (field.id === 'INITIATED_BY_TYPE') {
	            field.subscribe('change', function () {
	              if (main_core.Type.isStringFilled(field.getValue())) {
	                var filterFieldsValues = filterInstance.getFilterFieldsValues();
	                if (main_core.Type.isStringFilled(filterFieldsValues.INITIATED_BY_TYPE)) {
	                  if (JSON.stringify(Object.values(filterFieldsValues.ROLE)) === JSON.stringify(['Z'])) {
	                    return;
	                  }
	                  filterFieldsValues.ROLE = {
	                    0: 'Z'
	                  };
	                  filterApi.setFields(filterFieldsValues);
	                }
	              }
	            });
	          }
	        });
	        main_core_events.EventEmitter.subscribe('BX.Grid.SettingsWindow:save', function (event) {
	          var _event$getData2 = event.getData(),
	            _event$getData3 = babelHelpers.slicedToArray(_event$getData2, 1),
	            settingsWindow = _event$getData3[0];
	          if (!settingsWindow || !settingsWindow.parent || settingsWindow.parent.getId() !== _this.gridId) {
	            return;
	          }
	          filterApi.setFilter({
	            preset_id: _this.defaultFilterPresetId
	          });
	        });
	      }
	    }
	  }, {
	    key: "reload",
	    value: function reload() {
	      var gridInstance = BX.Main.gridManager.getInstanceById(this.gridId);
	      if (!gridInstance) {
	        return;
	      }
	      gridInstance.reloadTable('POST', {
	        apply_filter: 'Y'
	      });
	    }
	  }, {
	    key: "processClickEvent",
	    value: function processClickEvent(e) {
	      var targetNode = e.target;
	      if (!targetNode.hasAttribute('data-bx-action')) {
	        return;
	      }
	      var action = targetNode.getAttribute('data-bx-action');
	      if (action === 'disconnectDepartment') {
	        var departmentId = Number(targetNode.getAttribute('data-bx-department'));
	        if (departmentId > 0) {
	          this.getActionManager().disconnectDepartment({
	            id: departmentId
	          });
	        }
	        return;
	      }
	      var userId = targetNode.getAttribute('data-bx-user-id');
	      if (!main_core.Type.isStringFilled(action) || !main_core.Type.isStringFilled(userId)) {
	        return;
	      }
	      userId = parseInt(userId);
	      if (userId <= 0) {
	        return;
	      }
	      targetNode.classList.add('--inactive');
	      this.actionManagerInstance.act({
	        action: action,
	        userId: userId
	      }).then(function () {
	        targetNode.classList.remove('--inactive');
	      }, function () {
	        targetNode.classList.remove('--inactive');
	      });
	      e.preventDefault();
	      e.stopPropagation();
	    }
	  }, {
	    key: "getActionManager",
	    value: function getActionManager() {
	      return this.actionManagerInstance;
	    }
	  }]);
	  return Manager;
	}();
	babelHelpers.defineProperty(Manager, "repo", new Map());

	exports.Manager = Manager;

}((this.BX.Socialnetwork.WorkgroupUserList = this.BX.Socialnetwork.WorkgroupUserList || {}),BX.Event,BX,BX.Main,BX.UI));
//# sourceMappingURL=script.js.map
