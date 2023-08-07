this.BX = this.BX || {};
this.BX.Socialnetwork = this.BX.Socialnetwork || {};
(function (exports,socialnetwork_ui_grid,tasks_tour,main_core_events,main_popup,ui_buttons,socialnetwork_common,pull_client,main_core) {
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
	    key: "act",
	    value: function act(params, event) {
	      var _this = this;
	      if (event) {
	        event.stopPropagation();
	        event.preventDefault();
	      }
	      return new Promise(function (resolve, reject) {
	        if (['addToFavorites', 'removeFromFavorites'].includes(params.action)) {
	          return _this.setFavorites({
	            groupId: params.groupId,
	            value: params.action === 'addToFavorites'
	          }).then(function (response) {
	            _this.processActionSuccess(params);
	          }, function (response) {
	            _this.processActionFailure(params, response.message);
	          });
	        } else {
	          return main_core.ajax.runComponentAction(_this.componentName, 'act', {
	            mode: 'class',
	            signedParameters: _this.signedParameters,
	            data: {
	              action: params.action,
	              fields: {
	                groupId: params.groupId
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
	        }
	      });
	    }
	  }, {
	    key: "processActionSuccess",
	    value: function processActionSuccess(params) {
	      var eventCode = null;
	      var message = '';
	      switch (params.action) {
	        case 'addToFavorites':
	          message = main_core.Loc.getMessage('SGL_GROUP_ACTION_SUCCESS_NOTIFICATION_ADD_TO_FAVORITES');
	          break;
	        case 'removeFromFavorites':
	          message = main_core.Loc.getMessage('SGL_GROUP_ACTION_SUCCESS_NOTIFICATION_REMOVE_FROM_FAVORITES');
	          break;
	        case 'addToArchive':
	          message = main_core.Loc.getMessage('SGL_GROUP_ACTION_SUCCESS_NOTIFICATION_ADD_TO_ARCHIVE');
	          break;
	        case 'removeFromArchive':
	          message = main_core.Loc.getMessage('SGL_GROUP_ACTION_SUCCESS_NOTIFICATION_REMOVE_FROM_ARCHIVE');
	          break;
	        case 'join':
	          eventCode = 'afterJoinRequestSend';
	          break;
	        case 'setOwner':
	          eventCode = 'afterOwnerSet';
	          break;
	        case 'setScrumMaster':
	          eventCode = 'afterSetScrumMaster';
	          break;
	        case 'deleteOutgoingRequest':
	          eventCode = 'afterRequestOutDelete';
	          break;
	        case 'deleteIncomingRequest':
	          eventCode = 'afterRequestInDelete';
	          break;
	        default:
	      }
	      if (message !== '') {
	        BX.UI.Notification.Center.notify({
	          content: message
	        });
	      }
	      if (eventCode && top.BX.SidePanel && window !== top.window) {
	        top.BX.SidePanel.Instance.postMessageAll(window, 'sonetGroupEvent', {
	          code: eventCode
	        });
	      }
	      if (!BX.PULL) {
	        this.parent.reload();
	      }
	    }
	  }, {
	    key: "processActionFailure",
	    value: function processActionFailure(params, errorMessage) {
	      if (!main_core.Type.isStringFilled(errorMessage)) {
	        errorMessage = main_core.Loc.getMessage('SOCIALNETWORK_GROUP_LIST_ACTION_FAILURE');
	      }
	      BX.UI.Notification.Center.notify({
	        content: errorMessage
	      });
	    }
	  }, {
	    key: "setFavorites",
	    value: function setFavorites(params) {
	      var _this2 = this;
	      var newValue = params.value;
	      var oldValue = !params.value;
	      return new Promise(function (resolve, reject) {
	        socialnetwork_common.Common.setFavoritesAjax({
	          groupId: params.groupId,
	          favoritesValue: oldValue,
	          callback: {
	            success: function success(data) {
	              var eventData = {
	                code: 'afterSetFavorites',
	                data: {
	                  groupId: data.ID,
	                  value: data.RESULT === 'Y'
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
	              resolve();
	            },
	            failure: function failure(response) {
	              reject({
	                message: response.ERROR
	              });
	            }
	          }
	        });
	      });
	    }
	  }, {
	    key: "groupAction",
	    value: function groupAction(action) {
	      var _this3 = this;
	      var buttonTitle = '';
	      switch (action) {
	        case 'addToArchive':
	          buttonTitle = main_core.Loc.getMessage('SOCIALNETWORK_GROUP_LIST_GROUP_ACTION_BUTTON_ADD');
	          break;
	        case 'removeFromArchive':
	          buttonTitle = main_core.Loc.getMessage('SOCIALNETWORK_GROUP_LIST_GROUP_ACTION_BUTTON_RETURN');
	          break;
	        case 'delete':
	          buttonTitle = main_core.Loc.getMessage('SOCIALNETWORK_GROUP_LIST_GROUP_ACTION_BUTTON_DELETE');
	          break;
	        default:
	          action = '';
	      }
	      if (action === '') {
	        return;
	      }
	      var buttons = [new ui_buttons.SendButton({
	        text: buttonTitle,
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
	            data[gridInstance.getActionKey()] = action;
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
	        id: 'bx-sgl-group-delete-confirm',
	        autoHide: false,
	        closeByEsc: true,
	        buttons: buttons,
	        events: {
	          onPopupClose: function onPopupClose(popup) {
	            popup.destroy();
	          }
	        },
	        content: main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["<div>", "</div>"])), main_core.Loc.getMessage('SOCIALNETWORK_GROUP_LIST_GROUP_ACTION_CONFIRM_TEXT')),
	        padding: 20
	      });
	      confirmPopup.show();
	    }
	  }]);
	  return ActionManager;
	}();

	var UserCounterManager = /*#__PURE__*/function () {
	  function UserCounterManager(options) {
	    babelHelpers.classCallCheck(this, UserCounterManager);
	    this.gridController = options.gridController;
	    this.url = options.url;
	    this.columnId = options.columnId;
	    this.sliderOptions = options.sliderOptions;
	    this.useTasksCounters = options.useTasksCounters;
	    this.timer = null;
	    this.queueCounterData = new Map();
	  }
	  babelHelpers.createClass(UserCounterManager, [{
	    key: "processCounterItem",
	    value: function processCounterItem(counterData, groupId) {
	      var _this = this;
	      if (this.useTasksCounters) {
	        return;
	      }
	      if (groupId === 0 && Number(counterData.value) === 0) {
	        this.gridController.getInstance().getRows().getRows().forEach(function (targetRow) {
	          if (!main_core.Type.isUndefined(counterData.scrum) && counterData.scrum !== targetRow.getNode().getAttribute('data-scrum')) {
	            return;
	          }
	          _this.setRowCounter(targetRow, counterData, groupId);
	        });
	        return;
	      }
	      if (!this.gridController.getInstance().isRowExist(groupId)) {
	        return;
	      }
	      this.setRowCounter(this.gridController.getInstance().getRowById(groupId), counterData, groupId);
	    }
	  }, {
	    key: "setRowCounter",
	    value: function setRowCounter(targetRow, counterData, groupId) {
	      var rowCounterData = {};
	      var url = this.url.replace('#id#', groupId).replace('#ID#', groupId).replace('#GROUP_ID#', groupId).replace('#group_id#', groupId);
	      rowCounterData[this.columnId] = {
	        value: 0,
	        type: 'right',
	        events: groupId > 0 ? {
	          click: BX.SidePanel.Instance.open.bind(BX.SidePanel.Instance, url, this.sliderOptions)
	        } : {},
	        color: 'ui-counter-danger',
	        "class": 'sonet-ui-grid-counter'
	      };
	      var storedCounterData = {};
	      try {
	        eval("storedCounterData = ".concat(targetRow.getNode().getAttribute('data-counters'), ";"));
	      } catch (e) {}
	      if (storedCounterData === null || counterData.type === 'tasks_expired' && targetRow.getNode().getAttribute('data-scrum') === 'Y') {
	        return;
	      }
	      var sumValue = 0;
	      Object.entries(storedCounterData).forEach(function (_ref) {
	        var _ref2 = babelHelpers.slicedToArray(_ref, 1),
	          key = _ref2[0];
	        if (key === counterData.type) {
	          storedCounterData[key] = counterData.value.toString();
	        }
	        sumValue += Number(storedCounterData[key]);
	      });
	      targetRow.getNode().setAttribute('data-counters', "(".concat(JSON.stringify(storedCounterData), ")"));
	      rowCounterData[this.columnId].value = Number(sumValue);
	      targetRow.setCounters(rowCounterData);
	    }
	  }]);
	  return UserCounterManager;
	}();

	var PullControllerSocialnetwork = /*#__PURE__*/function () {
	  babelHelpers.createClass(PullControllerSocialnetwork, null, [{
	    key: "getInstance",
	    value: function getInstance() {}
	  }, {
	    key: "events",
	    get: function get() {
	      return {
	        add: 'add',
	        update: 'update',
	        "delete": 'delete',
	        userAdd: 'userAdd',
	        userUpdate: 'userUpdate',
	        userDelete: 'userDelete',
	        favoritesChanged: 'favoritesChanged',
	        pinChanged: 'pinChanged'
	      };
	    }
	  }]);
	  function PullControllerSocialnetwork(options) {
	    babelHelpers.classCallCheck(this, PullControllerSocialnetwork);
	    this.componentName = options.componentName;
	    this.signedParameters = options.signedParameters;
	    this.gridController = options.gridController;
	    this.pullController = options.pullController;
	    this.gridPinController = this.gridController.getInstance().getPinController();
	    this.grid = this.gridController.getGrid();
	  }
	  babelHelpers.createClass(PullControllerSocialnetwork, [{
	    key: "getModuleId",
	    value: function getModuleId() {
	      return 'socialnetwork';
	    }
	  }, {
	    key: "getMap",
	    value: function getMap() {
	      var _ref;
	      return _ref = {}, babelHelpers.defineProperty(_ref, main_core.Loc.getMessage('PUSH_EVENT_WORKGROUP_ADD'), this.onWorkgroupAdd.bind(this)), babelHelpers.defineProperty(_ref, main_core.Loc.getMessage('PUSH_EVENT_WORKGROUP_UPDATE'), this.onWorkgroupUpdate.bind(this)), babelHelpers.defineProperty(_ref, main_core.Loc.getMessage('PUSH_EVENT_WORKGROUP_DELETE'), this.onWorkgroupDelete.bind(this)), babelHelpers.defineProperty(_ref, main_core.Loc.getMessage('PUSH_EVENT_WORKGROUP_USER_ADD'), this.onWorkgroupUserAdd.bind(this)), babelHelpers.defineProperty(_ref, main_core.Loc.getMessage('PUSH_EVENT_WORKGROUP_USER_UPDATE'), this.onWorkgroupUserUpdate.bind(this)), babelHelpers.defineProperty(_ref, main_core.Loc.getMessage('PUSH_EVENT_WORKGROUP_USER_DELETE'), this.onWorkgroupUserDelete.bind(this)), babelHelpers.defineProperty(_ref, main_core.Loc.getMessage('PUSH_EVENT_WORKGROUP_FAVORITES_CHANGED'), this.onWorkgroupFavoritesChanged.bind(this)), babelHelpers.defineProperty(_ref, main_core.Loc.getMessage('PUSH_EVENT_WORKGROUP_PIN_CHANGED'), this.onWorkgroupPinChanged.bind(this)), _ref;
	    }
	  }, {
	    key: "onWorkgroupAdd",
	    value: function onWorkgroupAdd(data) {
	      var _this = this;
	      var params = {
	        event: PullControllerSocialnetwork.events.add,
	        moveParams: {
	          rowBefore: this.gridPinController.getLastPinnedRowId(),
	          rowAfter: this.gridController.getInstance().getFirstRowId()
	        }
	      };
	      this.pullController.checkExistence(data.params.GROUP_ID).then(function (response) {
	        return _this.pullController.onCheckExistenceSuccess(response, data.params.GROUP_ID, params);
	      }, function (response) {
	        return console.error(response);
	      });
	    }
	  }, {
	    key: "onWorkgroupUpdate",
	    value: function onWorkgroupUpdate(data) {
	      var _this2 = this;
	      var params = {
	        event: PullControllerSocialnetwork.events.update
	      };
	      this.pullController.checkExistence(data.params.GROUP_ID).then(function (response) {
	        return _this2.pullController.onCheckExistenceSuccess(response, data.params.GROUP_ID, params);
	      }, function (response) {
	        return console.error(response);
	      });
	    }
	  }, {
	    key: "onWorkgroupDelete",
	    value: function onWorkgroupDelete(data) {
	      this.pullController.removeRow(data.params.GROUP_ID);
	    }
	  }, {
	    key: "onWorkgroupUserAdd",
	    value: function onWorkgroupUserAdd(data) {
	      var _this3 = this;
	      var params = {
	        event: PullControllerSocialnetwork.events.userAdd
	      };
	      this.pullController.checkExistence(data.params.GROUP_ID).then(function (response) {
	        return _this3.pullController.onCheckExistenceSuccess(response, data.params.GROUP_ID, params);
	      }, function (response) {
	        return console.error(response);
	      });
	    }
	  }, {
	    key: "onWorkgroupUserUpdate",
	    value: function onWorkgroupUserUpdate(data) {
	      var _this4 = this;
	      var params = {
	        event: PullControllerSocialnetwork.events.userUpdate
	      };
	      this.pullController.checkExistence(data.params.GROUP_ID).then(function (response) {
	        return _this4.pullController.onCheckExistenceSuccess(response, data.params.GROUP_ID, params);
	      }, function (response) {
	        return console.error(response);
	      });
	    }
	  }, {
	    key: "onWorkgroupUserDelete",
	    value: function onWorkgroupUserDelete(data) {
	      var _this5 = this;
	      var params = {
	        event: PullControllerSocialnetwork.events.userDelete
	      };
	      this.pullController.checkExistence(data.params.GROUP_ID).then(function (response) {
	        return _this5.pullController.onCheckExistenceSuccess(response, data.params.GROUP_ID, params);
	      }, function (response) {
	        return console.error(response);
	      });
	    }
	  }, {
	    key: "onWorkgroupFavoritesChanged",
	    value: function onWorkgroupFavoritesChanged(data) {
	      var params = {
	        event: PullControllerSocialnetwork.events.favoritesChanged
	      };
	      this.pullController.moveToDirectPlace(data.GROUP_ID, null, params);
	    }
	  }, {
	    key: "onWorkgroupPinChanged",
	    value: function onWorkgroupPinChanged(data) {
	      if (!main_core.Type.isStringFilled(data.ACTION) || !['pin', 'unpin'].includes(data.ACTION)) {
	        return;
	      }
	      var params = {
	        event: PullControllerSocialnetwork.events.pinChanged
	      };
	      this.pullController.moveToDirectPlace(data.GROUP_ID, null, params);
	    }
	  }]);
	  return PullControllerSocialnetwork;
	}();

	var PullControllerMainUserCounter = /*#__PURE__*/function () {
	  function PullControllerMainUserCounter(options) {
	    babelHelpers.classCallCheck(this, PullControllerMainUserCounter);
	    this.componentName = options.componentName;
	    this.signedParameters = options.signedParameters;
	    this.userCounterManager = options.userCounterManager;
	    this.timer = null;
	    this.queueCounterData = new Map();
	  }
	  babelHelpers.createClass(PullControllerMainUserCounter, [{
	    key: "getModuleId",
	    value: function getModuleId() {
	      return 'main';
	    }
	  }, {
	    key: "getMap",
	    value: function getMap() {
	      return babelHelpers.defineProperty({}, main_core.Loc.getMessage('PUSH_EVENT_MAIN_USER_COUNTER'), this.onUserCounter.bind(this));
	    }
	  }, {
	    key: "onUserCounter",
	    value: function onUserCounter(data) {
	      var _this = this;
	      var siteId = main_core.Loc.getMessage('SITE_ID');
	      var eventCounterData = main_core.Type.isPlainObject(data[siteId]) ? data[siteId] : {};
	      if (!this.timer) {
	        this.timer = setTimeout(function () {
	          _this.freeCounterQueue();
	        }, 1000);
	      }
	      Object.entries(eventCounterData).forEach(function (_ref2) {
	        var _ref3 = babelHelpers.slicedToArray(_ref2, 2),
	          key = _ref3[0],
	          value = _ref3[1];
	        var matches = key.match(/^\*\*SG(\d+)/i);
	        if (matches) {
	          var groupId = Number(matches[1]);
	          value = Number(value);
	          if (groupId === 0 && value !== 0) {
	            return;
	          }
	          var counterData = {
	            type: 'livefeed',
	            value: value
	          };
	          _this.queueCounterData.set(groupId, counterData);
	        }
	      });
	    }
	  }, {
	    key: "freeCounterQueue",
	    value: function freeCounterQueue() {
	      this.queueCounterData.forEach(function (counterData, groupId) {
	        // todo oh this.userCounterManager.processCounterItem(counterData, groupId);
	      });
	      this.queueCounterData.clear();
	      this.timer = null;
	    }
	  }]);
	  return PullControllerMainUserCounter;
	}();

	var PullControllerTasks = /*#__PURE__*/function () {
	  babelHelpers.createClass(PullControllerTasks, null, [{
	    key: "events",
	    get: function get() {
	      return {
	        pinChanged: 'pinChanged'
	      };
	    }
	  }, {
	    key: "counterEvents",
	    get: function get() {
	      return ['onAfterTaskAdd', 'onAfterTaskDelete', 'onAfterTaskRestore', 'onAfterTaskView', 'onAfterTaskMute', 'onAfterCommentAdd', 'onAfterCommentDelete', 'onProjectPermUpdate'];
	    }
	  }, {
	    key: "movingProjectEvents",
	    get: function get() {
	      return ['onAfterTaskAdd', 'onAfterCommentAdd'];
	    }
	  }]);
	  function PullControllerTasks(options) {
	    babelHelpers.classCallCheck(this, PullControllerTasks);
	    this.componentName = options.componentName;
	    this.signedParameters = options.signedParameters;
	    this.gridController = options.gridController;
	    this.pullController = options.pullController;
	    this.gridPinController = this.gridController.getInstance().getPinController();
	    this.grid = this.gridController.getGrid();
	    this.timer = null;
	    this.counterData = new Map();
	  }
	  babelHelpers.createClass(PullControllerTasks, [{
	    key: "getModuleId",
	    value: function getModuleId() {
	      return 'tasks';
	    }
	  }, {
	    key: "getMap",
	    value: function getMap() {
	      var _ref;
	      return _ref = {}, babelHelpers.defineProperty(_ref, main_core.Loc.getMessage('PUSH_EVENT_TASKS_PROJECT_COUNTER'), this.onTasksProjectCounter.bind(this)), babelHelpers.defineProperty(_ref, main_core.Loc.getMessage('PUSH_EVENT_TASKS_PROJECT_READ_ALL'), this.onTasksProjectCommentsReadAll.bind(this)), babelHelpers.defineProperty(_ref, main_core.Loc.getMessage('PUSH_EVENT_TASKS_SCRUM_READ_ALL'), this.onTasksProjectCommentsReadAll.bind(this)), babelHelpers.defineProperty(_ref, main_core.Loc.getMessage('PUSH_EVENT_TASKS_COMMENT_READ_ALL'), this.onTasksProjectCommentsReadAll.bind(this)), _ref;
	    }
	  }, {
	    key: "onTasksProjectCounter",
	    value: function onTasksProjectCounter(data) {
	      var _this = this;
	      var groupId = data.GROUP_ID;
	      var event = data.EVENT;
	      if (!PullControllerTasks.counterEvents.includes(event)) {
	        return;
	      }
	      if (!this.timer) {
	        this.timer = setTimeout(function () {
	          _this.freeCounterQueue();
	        }, 1000);
	      }
	      if (PullControllerTasks.movingProjectEvents.includes(event) || !this.counterData.has(groupId)) {
	        this.counterData.set(groupId, event);
	      }
	    }
	  }, {
	    key: "freeCounterQueue",
	    value: function freeCounterQueue() {
	      var _this2 = this;
	      this.counterData.forEach(function (event, groupId) {
	        var params = {
	          event: event,
	          highlightParams: {
	            skip: true
	          },
	          updateItemCondition: function updateItemCondition(event) {
	            return PullControllerTasks.movingProjectEvents.includes(event);
	          }
	        };
	        if (PullControllerTasks.movingProjectEvents.includes(event)) {
	          params.moveParams = {
	            rowBefore: _this2.gridPinController.getIsPinned(groupId) ? 0 : _this2.gridPinController.getLastPinnedRowId(),
	            rowAfter: _this2.gridController.getInstance().getFirstRowId()
	          };
	          params.highlightParams = {
	            skip: false
	          };
	        }
	        _this2.pullController.checkExistence(groupId).then(function (response) {
	          return _this2.pullController.onCheckExistenceSuccess(response, groupId, params);
	        }, function (response) {
	          return console.error(response);
	        });
	      });
	      this.counterData.clear();
	      this.timer = null;
	    }
	  }, {
	    key: "onTasksProjectCommentsReadAll",
	    value: function onTasksProjectCommentsReadAll(data) {
	      var groupId = data.GROUP_ID;
	      if (groupId) {
	        if (this.gridController.getInstance().isRowExist(groupId)) {
	          this.updateCounter([groupId]);
	        }
	      } else {
	        this.updateCounter(this.gridController.getInstance().getItems());
	      }
	    }
	  }, {
	    key: "updateCounter",
	    value: function updateCounter(rowIds) {
	      var _this3 = this;
	      this.pullController.checkExistence(rowIds).then(function (response) {
	        var projects = response.data;
	        if (projects) {
	          Object.keys(projects).forEach(function (projectId) {
	            if (_this3.gridController.getInstance().isRowExist(projectId)) {
	              _this3.gridController.getInstance().getRowById(projectId).setCounters(projects[projectId].counters);
	            }
	          });
	        }
	      }, function (response) {
	        return console.error(response);
	      });
	    }
	  }]);
	  return PullControllerTasks;
	}();

	var PullControllerTasksUserCounter = /*#__PURE__*/function () {
	  function PullControllerTasksUserCounter(options) {
	    babelHelpers.classCallCheck(this, PullControllerTasksUserCounter);
	    this.componentName = options.componentName;
	    this.signedParameters = options.signedParameters;
	    this.gridController = options.gridController;
	    this.pullController = options.pullController;
	    this.gridPinController = this.gridController.getInstance().getPinController();
	    this.grid = this.gridController.getGrid();
	    this.userCounterManager = options.userCounterManager;
	    this.timer = null;
	    this.queueCounterData = new Map();
	  }
	  babelHelpers.createClass(PullControllerTasksUserCounter, [{
	    key: "getModuleId",
	    value: function getModuleId() {
	      return 'tasks';
	    }
	  }, {
	    key: "getMap",
	    value: function getMap() {
	      var _ref;
	      return _ref = {}, babelHelpers.defineProperty(_ref, main_core.Loc.getMessage('PUSH_EVENT_TASKS_USER_COUNTER'), this.onUserCounter.bind(this)), babelHelpers.defineProperty(_ref, main_core.Loc.getMessage('PUSH_EVENT_TASKS_PROJECT_READ_ALL'), this.onProjectReadAllComments.bind(this)), babelHelpers.defineProperty(_ref, main_core.Loc.getMessage('PUSH_EVENT_TASKS_SCRUM_READ_ALL'), this.onScrumReadAllComments.bind(this)), _ref;
	    }
	  }, {
	    key: "onUserCounter",
	    value: function onUserCounter(data) {
	      var _this = this;
	      if (!this.timer) {
	        this.timer = setTimeout(function () {
	          _this.freeCounterQueue();
	        }, 1000);
	      }
	      Object.entries(data).forEach(function (_ref2) {
	        var _ref3 = babelHelpers.slicedToArray(_ref2, 2),
	          key = _ref3[0],
	          value = _ref3[1];
	        if (key == Number(key) && Number(key) > 0 && main_core.Type.isPlainObject(value.view_all)) {
	          if (!main_core.Type.isUndefined(value.view_all.new_comments)) {
	            _this.queueCounterData.set("".concat(key, "_new_comments"), {
	              groupId: key,
	              type: 'tasks_new_comments',
	              value: Number(value.view_all.new_comments)
	            });
	          }
	          if (!main_core.Type.isUndefined(value.view_all.expired)) {
	            _this.queueCounterData.set("".concat(key, "_expired"), {
	              groupId: key,
	              type: 'tasks_expired',
	              value: Number(value.view_all.expired)
	            });
	          }
	        }
	      });
	    }
	  }, {
	    key: "onProjectReadAllComments",
	    value: function onProjectReadAllComments(data) {
	      var _this2 = this;
	      if (Number(data.GROUP_ID) !== 0 && Number(data.USER_ID) !== Number(main_core.Loc.getMessage('USER_ID'))) {
	        return;
	      }
	      if (!this.timer) {
	        this.timer = setTimeout(function () {
	          _this2.freeCounterQueue();
	        }, 1000);
	      }
	      this.queueCounterData.set("0_new_comments", {
	        groupId: 0,
	        type: 'tasks_new_comments',
	        value: 0,
	        scrum: 'N'
	      });
	    }
	  }, {
	    key: "onScrumReadAllComments",
	    value: function onScrumReadAllComments(data) {
	      var _this3 = this;
	      if (Number(data.GROUP_ID) !== 0 && Number(data.USER_ID) !== Number(main_core.Loc.getMessage('USER_ID'))) {
	        return;
	      }
	      if (!this.timer) {
	        this.timer = setTimeout(function () {
	          _this3.freeCounterQueue();
	        }, 1000);
	      }
	      this.queueCounterData.set("0_new_comments", {
	        groupId: 0,
	        type: 'tasks_new_comments',
	        value: 0,
	        scrum: 'Y'
	      });
	    }
	  }, {
	    key: "freeCounterQueue",
	    value: function freeCounterQueue() {
	      this.queueCounterData.forEach(function (counterData) {
	        // todo oh this.userCounterManager.processCounterItem(counterData, Number(counterData.groupId));
	      });
	      this.queueCounterData.clear();
	      this.timer = null;
	    }
	  }]);
	  return PullControllerTasksUserCounter;
	}();

	var PullManager = /*#__PURE__*/function () {
	  function PullManager(options) {
	    babelHelpers.classCallCheck(this, PullManager);
	    this.componentName = options.componentName;
	    this.signedParameters = options.signedParameters;
	    this.urls = options.urls;
	    this.useTasksCounters = options.useTasksCounters;
	    this.livefeedCounterColumnId = options.livefeedCounterColumnId;
	    this.livefeedCounterSliderOptions = options.livefeedCounterSliderOptions;
	    this.gridController = options.gridController;
	    this.grid = this.gridController.getGrid();
	    this.init();
	  }
	  babelHelpers.createClass(PullManager, [{
	    key: "init",
	    value: function init() {
	      this.userCounterManagerInstance = new UserCounterManager({
	        gridController: this.gridController,
	        url: this.urls.groupLivefeedUrl,
	        columnId: this.livefeedCounterColumnId,
	        sliderOptions: this.livefeedCounterSliderOptions,
	        useTasksCounters: this.useTasksCounters
	      });
	      this.pullControllerSocialnetwork = new PullControllerSocialnetwork({
	        componentName: this.componentName,
	        signedParameters: this.signedParameters,
	        gridController: this.gridController,
	        userCounterManager: this.userCounterManagerInstance,
	        pullController: this
	      });
	      pull_client.PULL.subscribe(this.pullControllerSocialnetwork);
	      this.pullControllerMainUserCounter = new PullControllerMainUserCounter({
	        componentName: this.componentName,
	        signedParameters: this.signedParameters,
	        userCounterManager: this.userCounterManagerInstance
	      });
	      pull_client.PULL.subscribe(this.pullControllerMainUserCounter);
	      this.pullControllerTasksUserCounter = new PullControllerTasksUserCounter({
	        componentName: this.componentName,
	        signedParameters: this.signedParameters,
	        gridController: this.gridController,
	        userCounterManager: this.userCounterManagerInstance
	      });
	      pull_client.PULL.subscribe(this.pullControllerTasksUserCounter);
	      if (this.useTasksCounters) {
	        this.pullControllerTasks = new PullControllerTasks({
	          componentName: this.componentName,
	          signedParameters: this.signedParameters,
	          gridController: this.gridController,
	          pullController: this
	        });
	        pull_client.PULL.subscribe(this.pullControllerTasks);
	      }
	    }
	  }, {
	    key: "checkExistence",
	    value: function checkExistence(groupId) {
	      var _this = this;
	      return new Promise(function (resolve, reject) {
	        main_core.ajax.runComponentAction(_this.componentName, 'checkExistence', {
	          mode: 'class',
	          data: {
	            groupIdList: main_core.Type.isArray(groupId) ? groupId : [groupId]
	          },
	          signedParameters: _this.signedParameters
	        }).then(function (response) {
	          return resolve(response);
	        }, function (response) {
	          return reject(response);
	        });
	      });
	    }
	  }, {
	    key: "onCheckExistenceSuccess",
	    value: function onCheckExistenceSuccess(response, groupId, params) {
	      if (main_core.Type.isUndefined(response.data[groupId])) {
	        return;
	      }
	      if (response.data[groupId] === false) {
	        this.grid.removeRow(groupId);
	        return;
	      }
	      this.moveToDirectPlace(groupId, response.data[groupId], params);
	    }
	  }, {
	    key: "isActivityRealtimeMode",
	    value: function isActivityRealtimeMode() {
	      var sort = this.gridController.getInstance().getSort();
	      return sort.DATE_UPDATE && sort.DATE_UPDATE === 'desc';
	    }
	  }, {
	    key: "updateItem",
	    value: function updateItem(rowId, rowData, params) {
	      if (!this.gridController.getInstance().hasItem(rowId)) {
	        this.gridController.getInstance().addItem(rowId);
	        this.addRow(rowId, rowData, params);
	      } else {
	        this.updateRow(rowId, rowData, params);
	      }
	    }
	  }, {
	    key: "addRow",
	    value: function addRow(rowId, rowData, params) {
	      if (this.gridController.getInstance().isRowExist(rowId) || main_core.Type.isUndefined(rowData) || main_core.Type.isNull(rowData)) {
	        return;
	      }
	      this.gridController.getInstance().addRow(rowId, rowData, params);
	    }
	  }, {
	    key: "updateRow",
	    value: function updateRow(rowId, rowData, params) {
	      if (!this.gridController.getInstance().isRowExist(rowId) || main_core.Type.isUndefined(rowData)) {
	        return;
	      }
	      this.gridController.getInstance().updateRow(rowId, rowData, params);
	    }
	  }, {
	    key: "removeRow",
	    value: function removeRow(rowId) {
	      if (!this.gridController.getInstance().isRowExist(rowId)) {
	        return;
	      }
	      this.gridController.getInstance().removeRow(rowId);
	    }
	  }, {
	    key: "moveToDirectPlace",
	    value: function moveToDirectPlace(groupId, data, params) {
	      var _this2 = this;
	      params = params || {};
	      main_core.ajax.runComponentAction(this.componentName, 'findWorkgroupPlace', {
	        mode: 'class',
	        data: {
	          groupId: groupId,
	          currentPage: this.gridController.getInstance().getCurrentPage()
	        },
	        signedParameters: this.signedParameters
	      }).then(function (response) {
	        if (response.data === null) {
	          return;
	        }
	        var _response$data = response.data,
	          workgroupBefore = _response$data.workgroupBefore,
	          workgroupAfter = _response$data.workgroupAfter;
	        if (workgroupBefore === false && workgroupAfter === false) {
	          _this2.removeRow(groupId);
	        } else {
	          if (workgroupBefore && _this2.gridController.getInstance().isRowExist(workgroupBefore) || workgroupAfter && _this2.gridController.getInstance().isRowExist(workgroupAfter)) {
	            params.moveParams = {
	              rowBefore: workgroupBefore,
	              rowAfter: workgroupAfter
	            };
	          } else {
	            params.moveParams = {
	              skip: true
	            };
	          }
	          _this2.updateItem(groupId, data, params);
	        }
	      });
	    }
	  }]);
	  return PullManager;
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
	    this.sort = params.sort;
	    this.items = params.items;
	    this.pageSize = main_core.Type.isNumber(params.pageSize) ? parseInt(params.pageSize) : 10;
	    this.gridStub = !main_core.Type.isUndefined(params.gridStub) ? params.gridStub : null;
	    this.defaultFilterPresetId = main_core.Type.isStringFilled(params.defaultFilterPresetId) && !['requests_in', 'requests_out'].includes(params.defaultFilterPresetId) ? params.defaultFilterPresetId : 'tmp_filter';
	    this.defaultCounter = main_core.Type.isStringFilled(params.defaultCounter) ? params.defaultCounter : '';
	    this.gridContainer = main_core.Type.isStringFilled(params.gridContainerId) ? document.getElementById(params.gridContainerId) : null;
	    this.urls = params.urls;
	    this.useTasksCounters = main_core.Type.isBoolean(params.useTasksCounters) ? params.useTasksCounters : false;
	    this.livefeedCounterColumnId = main_core.Type.isStringFilled(params.livefeedCounterColumnId) ? params.livefeedCounterColumnId : 'NAME';
	    this.livefeedCounterSliderOptions = main_core.Type.isPlainObject(params.livefeedCounterSliderOptions) ? params.livefeedCounterSliderOptions : {};
	    this.init(params);
	  }
	  babelHelpers.createClass(Manager, [{
	    key: "init",
	    value: function init(params) {
	      this.initEvents();
	      this.actionManagerInstance = new ActionManager({
	        parent: this
	      });
	      this.gridController = new socialnetwork_ui_grid.Controller({
	        id: this.id,
	        sort: this.sort,
	        items: this.items,
	        pageSize: this.pageSize,
	        gridStub: this.gridStub,
	        componentName: this.componentName,
	        signedParameters: this.signedParameters
	      });
	      Manager.repo.set(this.id, this);
	      socialnetwork_ui_grid.ActionController.setOptions({
	        gridInstance: this.gridController.getGrid(),
	        componentName: this.componentName,
	        signedParameters: this.signedParameters
	      });
	      var filter = new socialnetwork_ui_grid.Filter({
	        filterId: this.filterId,
	        defaultFilterPresetId: this.defaultFilterPresetId,
	        gridId: this.id,
	        signedParameters: this.signedParameters
	      });
	      if (this.useTasksCounters) {
	        this.tasksTour = new tasks_tour.Tour({
	          tours: params.tours
	        });
	        this.tasksTour.subscribe('FirstProject:afterProjectCreated', this.onAfterGroupCreated.bind(this));
	        this.tasksTour.subscribe('FirstScrum:afterScrumCreated', this.onAfterGroupCreated.bind(this));
	      }
	      this.initPull();
	      if (filter) {
	        socialnetwork_ui_grid.TagController.setOptions({
	          filter: filter
	        });
	      }
	    }
	  }, {
	    key: "initPull",
	    value: function initPull() {
	      this.pullManager = new PullManager({
	        componentName: this.componentName,
	        signedParameters: this.signedParameters,
	        urls: this.urls,
	        livefeedCounterColumnId: this.livefeedCounterColumnId,
	        livefeedCounterSliderOptions: this.livefeedCounterSliderOptions,
	        gridController: this.gridController,
	        useTasksCounters: this.useTasksCounters
	      });
	    }
	  }, {
	    key: "setActionsPanel",
	    value: function setActionsPanel(actionPanel) {
	      socialnetwork_ui_grid.ActionController.setActionsPanel(actionPanel);
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
	        if (!BX.PULL && sliderEvent.getEventId() === 'sonetGroupEvent' && !main_core.Type.isUndefined(sliderEvent.data) && main_core.Type.isStringFilled(sliderEvent.data.code) && ['afterCreate', 'afterEdit', 'afterInvite', 'afterJoinRequestSend', 'afterLeave', 'afterDelete'].includes(sliderEvent.data.code)) {
	          _this.reload();
	        }
	      });
	      main_core_events.EventEmitter.subscribe('Tasks.Toolbar:onItem', function (event) {
	        var data = event.getData();
	        if (data.counter && data.counter.filter) {
	          var filter = data.counter.filter.getFilter();
	          _this.toggleByField(babelHelpers.defineProperty({}, data.counter.filterField, data.counter.filterValue), filter);
	        }
	      });
	      main_core_events.EventEmitter.subscribe('Socialnetwork.Toolbar:onItem', function (event) {
	        var data = event.getData();
	        if (data.counter && data.counter.filter) {
	          var filter = data.counter.filter.getFilter();
	          _this.toggleByField(babelHelpers.defineProperty({}, data.counter.filterField, data.counter.filterValue), filter);
	        }
	      });
	    }
	  }, {
	    key: "toggleByField",
	    value: function toggleByField(filterData, filter) {
	      if (!filter) {
	        return;
	      }
	      var name = Object.keys(filterData)[0];
	      var value = filterData[name];
	      var fields = filter.getFilterFieldsValues();
	      if (!this.isFilteredByFieldValue(name, value, fields)) {
	        filter.getApi().extendFilter(babelHelpers.defineProperty({}, name, value));
	        return;
	      }
	      filter.getFilterFields().forEach(function (field) {
	        if (field.getAttribute('data-name') === name) {
	          filter.getFields().deleteField(field);
	        }
	      });
	      filter.getSearch().apply();
	    }
	  }, {
	    key: "isFilteredByField",
	    value: function isFilteredByField(field, fields) {
	      if (!Object.keys(fields).includes(field)) {
	        return false;
	      }
	      if (main_core.Type.isArray(fields[field])) {
	        return fields[field].length > 0;
	      }
	      return fields[field] !== '';
	    }
	  }, {
	    key: "isFilteredByFieldValue",
	    value: function isFilteredByFieldValue(field, value, fields) {
	      return this.isFilteredByField(field, fields) && fields[field] === value;
	    }
	  }, {
	    key: "reload",
	    value: function reload() {
	      var _this2 = this;
	      var gridInstance = BX.Main.gridManager.getInstanceById(this.gridId);
	      if (!gridInstance) {
	        return;
	      }
	      gridInstance.reloadTable('POST', {
	        apply_filter: 'Y'
	      }, function () {
	        _this2.gridController.getInstance().getPinController().colorPinnedRows();
	      });
	    }
	  }, {
	    key: "processClickEvent",
	    value: function processClickEvent(e) {
	      var targetNode = e.target;
	      if (!targetNode.classList.contains('sonet-group-grid-action')) {
	        return;
	      }
	      var action = targetNode.getAttribute('data-bx-action');
	      var groupId = targetNode.getAttribute('data-bx-group-id');
	      if (!main_core.Type.isStringFilled(action) || !main_core.Type.isStringFilled(groupId)) {
	        return;
	      }
	      groupId = parseInt(groupId);
	      if (groupId <= 0) {
	        return;
	      }
	      targetNode.classList.add('--inactive');
	      this.actionManagerInstance.act({
	        action: action,
	        groupId: groupId
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
	  }, {
	    key: "onAfterGroupCreated",
	    value: function onAfterGroupCreated(baseEvent) {
	      var projectId = baseEvent.getData();
	      var isRowExist = this.gridController.getInstance().isRowExist(projectId);
	      if (isRowExist) {
	        var targetRow = this.gridController.getInstance().getRowNodeById(projectId);
	        var target = targetRow.querySelector('.sonet-group-grid-name-text');
	        this.tasksTour.showFinalStep(projectId, target);
	      } else {
	        main_core_events.EventEmitter.subscribeOnce('SocialNetwork.Projects.Grid:RowAdd', this.onGridRowAdded.bind(this, projectId));
	      }
	    }
	  }, {
	    key: "onGridRowAdded",
	    value: function onGridRowAdded(projectId, baseEvent) {
	      var _baseEvent$getData = baseEvent.getData(),
	        id = _baseEvent$getData.id;
	      if (Number(id) === Number(projectId)) {
	        var targetRow = this.gridController.getInstance().getRowNodeById(projectId);
	        var target = targetRow.querySelector('.sonet-group-grid-name-text');
	        this.tasksTour.showFinalStep(target);
	      }
	    }
	  }]);
	  return Manager;
	}();
	babelHelpers.defineProperty(Manager, "repo", new Map());

	exports.Manager = Manager;

}((this.BX.Socialnetwork.WorkgroupList = this.BX.Socialnetwork.WorkgroupList || {}),BX.Socialnetwork.UI.Grid,BX.Tasks.Tour,BX.Event,BX.Main,BX.UI,BX.Socialnetwork.UI,BX,BX));
//# sourceMappingURL=script.js.map
