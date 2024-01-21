/* eslint-disable */
(function (exports,main_core,ui_alerts,ui_cnt,ui_entitySelector,main_popup) {
	'use strict';

	var _templateObject, _templateObject2;
	function _createForOfIteratorHelper(o, allowArrayLike) { var it = typeof Symbol !== "undefined" && o[Symbol.iterator] || o["@@iterator"]; if (!it) { if (Array.isArray(o) || (it = _unsupportedIterableToArray(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = it.call(o); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it["return"] != null) it["return"](); } finally { if (didErr) throw err; } } }; }
	function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }
	function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i]; return arr2; }
	var namespace = main_core.Reflection.namespace('BX.Bizproc.Component');
	var UserProcesses = /*#__PURE__*/function () {
	  function UserProcesses(options) {
	    babelHelpers.classCallCheck(this, UserProcesses);
	    babelHelpers.defineProperty(this, "delegateToUserId", 0);
	    babelHelpers.defineProperty(this, "workflowTasks", {});
	    babelHelpers.defineProperty(this, "tasksWorkflowsMap", {});
	    if (main_core.Type.isPlainObject(options)) {
	      this.gridId = options.gridId;
	      if (main_core.Type.isArray(options.errors)) {
	        this.showErrors(options.errors);
	      }
	      this.actionPanel = {
	        wrapperElementId: options.actionPanelUserWrapperId,
	        actionButtonName: "".concat(this.gridId, "_action_button")
	      };
	      this.currentUserId = options.currentUserId;
	    }
	    this.bindAnchors();
	    this.init();
	  }
	  babelHelpers.createClass(UserProcesses, [{
	    key: "init",
	    value: function init() {
	      this.actionPanel.userWrapperElement = document.getElementById(this.actionPanel.wrapperElementId);
	      var _iterator = _createForOfIteratorHelper(document.querySelectorAll('[data-role="workflow-tasks-data"]')),
	        _step;
	      try {
	        for (_iterator.s(); !(_step = _iterator.n()).done;) {
	          var workflowTasksWrapper = _step.value;
	          var workflowId = workflowTasksWrapper.dataset.workflowId;
	          var tasks = JSON.parse(workflowTasksWrapper.dataset.tasks);
	          if (main_core.Type.isStringFilled(workflowId) && main_core.Type.isArray(tasks)) {
	            this.workflowTasks[workflowId] = tasks;
	            var _iterator2 = _createForOfIteratorHelper(tasks),
	              _step2;
	            try {
	              for (_iterator2.s(); !(_step2 = _iterator2.n()).done;) {
	                var task = _step2.value;
	                this.tasksWorkflowsMap[task.id] = workflowId;
	              }
	            } catch (err) {
	              _iterator2.e(err);
	            } finally {
	              _iterator2.f();
	            }
	          }
	        }
	      } catch (err) {
	        _iterator.e(err);
	      } finally {
	        _iterator.f();
	      }
	      this.initUserSelector();
	      this.initTasksColumn();
	      this.onActionPanelChanged();
	    }
	  }, {
	    key: "bindAnchors",
	    value: function bindAnchors() {
	      var _this = this;
	      BX.SidePanel.Instance.bindAnchors({
	        rules: [{
	          condition: ['/rpa/task/'],
	          options: {
	            width: 580,
	            cacheable: false,
	            allowChangeHistory: false,
	            events: {
	              onClose: function onClose() {
	                return _this.reloadGrid();
	              }
	            }
	          }
	        }]
	      });
	    }
	  }, {
	    key: "initTasksColumn",
	    value: function initTasksColumn() {
	      var taskNameContainers = document.querySelectorAll('.bp-task-container');
	      var _iterator3 = _createForOfIteratorHelper(taskNameContainers),
	        _step3;
	      try {
	        for (_iterator3.s(); !(_step3 = _iterator3.n()).done;) {
	          var container = _step3.value;
	          this.renderParallelTasksTo(container);
	        }
	      } catch (err) {
	        _iterator3.e(err);
	      } finally {
	        _iterator3.f();
	      }
	    }
	  }, {
	    key: "renderParallelTasksTo",
	    value: function renderParallelTasksTo(container) {
	      var taskId = parseInt(container.dataset.taskId, 10);
	      var workflowId = this.tasksWorkflowsMap[taskId];
	      if (main_core.Type.isNumber(taskId) && main_core.Type.isStringFilled(workflowId)) {
	        var _this$workflowTasks$w;
	        var tasks = (_this$workflowTasks$w = this.workflowTasks[workflowId]) !== null && _this$workflowTasks$w !== void 0 ? _this$workflowTasks$w : [];
	        if (tasks.length > 1) {
	          // eslint-disable-next-line unicorn/no-this-assignment
	          var self = this;
	          var menuTasks = tasks.filter(function (task) {
	            return task.id !== taskId;
	          }).map(function (task) {
	            return {
	              id: task.id,
	              text: task.name,
	              onclick: function onclick() {
	                this.close();
	                var bindElement = this.getPopupWindow().bindElement;
	                var currentTaskContainer = bindElement.parentElement.parentElement.parentElement;
	                var gridCell = currentTaskContainer.parentElement;
	                main_core.Dom.clean(gridCell);
	                main_core.Runtime.html(gridCell, task.renderedName)["catch"](function (err) {
	                  return console.error(err);
	                });
	                var newContainer = gridCell.querySelector('.bp-task-container');
	                if (newContainer) {
	                  self.renderParallelTasksTo(newContainer);
	                }
	              }
	            };
	          });
	          if (!main_core.Type.isNil(container.lastElementChild)) {
	            var activeTasks = tasks.filter(function (task) {
	              return task.canComplete;
	            });
	            if (activeTasks.length > 0) {
	              main_core.Dom.prepend(this.renderParallelTasksCounter(activeTasks.length), container);
	            }
	            main_core.Dom.append(this.renderParallelTasksLabel(workflowId, taskId, menuTasks), container.lastElementChild);
	          }
	        }
	      }
	    }
	  }, {
	    key: "renderParallelTasksCounter",
	    value: function renderParallelTasksCounter(count) {
	      var counter = new ui_cnt.Counter({
	        value: count,
	        color: ui_cnt.CounterColor.DANGER
	      });
	      return main_core.Dom.create('div', {
	        style: {
	          paddingRight: '5px'
	        },
	        children: [counter.createContainer()]
	      });
	    }
	  }, {
	    key: "renderParallelTasksLabel",
	    value: function renderParallelTasksLabel(workflowId, showTaskId, tasks) {
	      var _ref = main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<span class=\"bp-task-label-container\">\n\t\t\t\t<a ref=\"label\" class=\"bp-task-label\">\n\t\t\t\t\t", "\n\t\t\t\t</a>\n\t\t\t</span>\n\t\t"])), main_core.Loc.getMessage('BIZPROC_USER_PROCESSES_TEMPLATE_TASKS_LABEL', {
	          '#COUNT#': tasks.length
	        })),
	        label = _ref.label,
	        root = _ref.root;
	      label.onclick = function (event) {
	        event.preventDefault();
	        var menuId = "bp-workflow-".concat(workflowId, "-parallel-tasks-with-task-id-").concat(showTaskId);
	        var menu = main_popup.MenuManager.create({
	          id: menuId,
	          angle: true,
	          items: tasks
	        });
	        menu.getPopupWindow().setBindElement(label);
	        menu.show();
	      };
	      return root;
	    }
	  }, {
	    key: "initStartWorkflowButton",
	    value: function initStartWorkflowButton(buttonId) {
	      var button = main_core.Type.isStringFilled(buttonId) && document.getElementById(buttonId);
	      var lists = main_core.Type.isStringFilled(button === null || button === void 0 ? void 0 : button.dataset.lists) && JSON.parse(button.dataset.lists);
	      if (lists) {
	        var popupMenu = new main_popup.Menu({
	          angle: true,
	          offsetLeft: main_core.Dom.getPosition(button).width / 2,
	          autoHide: true,
	          bindElement: button,
	          closeByEsc: true,
	          items: Object.values(lists).map(function (list) {
	            return {
	              text: list.name,
	              href: list.url,
	              className: 'feed-add-post-form-link-lists',
	              dataset: {
	                iconUrl: list.icon
	              }
	            };
	          })
	        });
	        var popupElement = popupMenu.getMenuContainer();
	        var _iterator4 = _createForOfIteratorHelper(popupElement.querySelectorAll('.menu-popup-item-icon')),
	          _step4;
	        try {
	          for (_iterator4.s(); !(_step4 = _iterator4.n()).done;) {
	            var iconElement = _step4.value;
	            main_core.Dom.append(main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t\t<img src = \"", "\" alt=\"\" width = \"19\" height = \"16\"/>\n\t\t\t\t\t"])), iconElement.parentElement.dataset.iconUrl), iconElement);
	          }
	        } catch (err) {
	          _iterator4.e(err);
	        } finally {
	          _iterator4.f();
	        }
	        button.onclick = function (event) {
	          event.preventDefault();
	          popupMenu.show();
	        };
	      }
	    }
	  }, {
	    key: "initUserSelector",
	    value: function initUserSelector() {
	      var _this2 = this;
	      this.delegateToSelector = new ui_entitySelector.TagSelector({
	        multiple: false,
	        tagMaxWidth: 180,
	        events: {
	          onTagAdd: function onTagAdd(event) {
	            _this2.delegateToUserId = parseInt(event.getData().tag.getId(), 10);
	            if (!main_core.Type.isInteger(_this2.delegateToUserId)) {
	              _this2.delegateToUserId = 0;
	            }
	          },
	          onTagRemove: function onTagRemove() {
	            _this2.delegateToUserId = 0;
	          }
	        },
	        dialogOptions: {
	          entities: [{
	            id: 'user',
	            options: {
	              intranetUsersOnly: true,
	              inviteEmployeeLink: false
	            }
	          }]
	        }
	      });
	      if (main_core.Type.isDomNode(this.actionPanel.userWrapperElement)) {
	        this.delegateToSelector.renderTo(this.actionPanel.userWrapperElement);
	      }
	    }
	  }, {
	    key: "showErrors",
	    value: function showErrors(errors) {
	      var errorsContainer = document.getElementById('bp-user-processes-errors-container');
	      if (errorsContainer) {
	        var errorCounter = 0;
	        var fixStyles = function fixStyles() {
	          if (errorCounter > 0) {
	            main_core.Dom.style(errorsContainer, {
	              margin: '10px'
	            });
	          } else {
	            main_core.Dom.style(errorsContainer, {
	              margin: '0px'
	            });
	          }
	        };
	        var _iterator5 = _createForOfIteratorHelper(errors),
	          _step5;
	        try {
	          for (_iterator5.s(); !(_step5 = _iterator5.n()).done;) {
	            var error = _step5.value;
	            errorCounter += 1;
	            var alert = new ui_alerts.Alert({
	              text: main_core.Text.encode(error.message),
	              color: ui_alerts.AlertColor.DANGER,
	              closeBtn: true,
	              animated: true
	            });
	            alert.renderTo(errorsContainer);
	            if (alert.getCloseBtn()) {
	              // eslint-disable-next-line no-loop-func
	              alert.getCloseBtn().onclick = function () {
	                errorCounter -= 1;
	                fixStyles();
	              };
	            }
	          }
	        } catch (err) {
	          _iterator5.e(err);
	        } finally {
	          _iterator5.f();
	        }
	        fixStyles();
	      }
	    }
	  }, {
	    key: "onActionPanelChanged",
	    value: function onActionPanelChanged() {
	      var grid = this.getGrid();
	      var actionPanel = grid === null || grid === void 0 ? void 0 : grid.getActionsPanel();
	      if (actionPanel) {
	        var action = actionPanel.getValues()[this.actionPanel.actionButtonName];
	        if (!main_core.Type.isString(action) || action.includes('set_status')) {
	          main_core.Dom.hide(this.actionPanel.userWrapperElement);
	        } else {
	          main_core.Dom.show(this.actionPanel.userWrapperElement);
	        }
	      }
	    }
	  }, {
	    key: "applyActionPanelValues",
	    value: function applyActionPanelValues() {
	      var grid = this.getGrid();
	      var actionsPanel = grid === null || grid === void 0 ? void 0 : grid.getActionsPanel();
	      if (grid && actionsPanel) {
	        var _actionsPanel$getForA;
	        var isApplyingForAll = ((_actionsPanel$getForA = actionsPanel.getForAllCheckbox()) === null || _actionsPanel$getForA === void 0 ? void 0 : _actionsPanel$getForA.checked) === true;
	        // TODO - implement doing all tasks
	        if (isApplyingForAll) {
	          this.showErrors([{
	            message: 'Not implemented currently'
	          }]);
	        }
	        var action = actionsPanel.getValues()[this.actionPanel.actionButtonName];
	        if (main_core.Type.isString(action)) {
	          var selectedTasks = this.getSelectedTaskIds(grid.getRows().getSelectedIds());
	          if (action.includes('set_status_')) {
	            var status = parseInt(action.split('_').pop(), 10);
	            if (main_core.Type.isNumber(status)) {
	              this.setTasksStatuses(selectedTasks, status);
	            }
	          } else if (action.startsWith('delegate_to')) {
	            this.delegateTasks(selectedTasks, this.delegateToUserId);
	          }
	        }
	      }
	    }
	  }, {
	    key: "getSelectedTaskIds",
	    value: function getSelectedTaskIds(selectedWorkflowIds) {
	      var _this3 = this;
	      return selectedWorkflowIds.map(function (workflowId) {
	        var _this3$workflowTasks$;
	        return (_this3$workflowTasks$ = _this3.workflowTasks[workflowId][0]) === null || _this3$workflowTasks$ === void 0 ? void 0 : _this3$workflowTasks$.id;
	      }).filter(function (taskId) {
	        return main_core.Type.isNumber(taskId);
	      });
	    }
	  }, {
	    key: "setTasksStatuses",
	    value: function setTasksStatuses(taskIds, newStatus) {
	      var _this4 = this;
	      // eslint-disable-next-line promise/catch-or-return
	      main_core.ajax.runAction('bizproc.task.doInlineTasks', {
	        data: {
	          taskIds: taskIds,
	          newStatus: newStatus
	        }
	      })["catch"](function (response) {
	        _this4.showErrors(response.errors);
	        _this4.reloadGrid();
	      }).then(function () {
	        return _this4.reloadGrid();
	      });
	    }
	  }, {
	    key: "delegateTasks",
	    value: function delegateTasks(taskIds, toUserId) {
	      var _this5 = this;
	      // eslint-disable-next-line promise/catch-or-return
	      main_core.ajax.runComponentAction('bitrix:bizproc.user.processes', 'delegateTasks', {
	        mode: 'class',
	        data: {
	          taskIds: taskIds,
	          toUserId: toUserId
	        }
	      })["catch"](function (response) {
	        _this5.showErrors(response.errors);
	        _this5.reloadGrid();
	      }).then(function () {
	        return _this5.reloadGrid();
	      });
	    }
	  }, {
	    key: "reloadGrid",
	    value: function reloadGrid() {
	      var grid = this.getGrid();
	      if (grid) {
	        grid.reload();
	      } else {
	        console.warn('Grid not found');
	      }
	    }
	  }, {
	    key: "updateTaskData",
	    value: function updateTaskData(taskId) {
	      location.reload();
	    }
	  }, {
	    key: "getGrid",
	    value: function getGrid() {
	      if (this.gridId) {
	        var _BX$Main$gridManager;
	        return (_BX$Main$gridManager = BX.Main.gridManager) === null || _BX$Main$gridManager === void 0 ? void 0 : _BX$Main$gridManager.getInstanceById(this.gridId);
	      }
	      return null;
	    }
	  }]);
	  return UserProcesses;
	}();
	namespace.UserProcesses = UserProcesses;

}((this.window = this.window || {}),BX,BX.UI,BX.UI,BX.UI.EntitySelector,BX.Main));
//# sourceMappingURL=script.js.map
