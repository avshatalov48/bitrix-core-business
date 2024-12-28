/* eslint-disable */
(function (exports,main_core,main_core_events,ui_alerts,bizproc_workflow_starter) {
	'use strict';

	var _templateObject;
	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var namespace = main_core.Reflection.namespace('BX.Bizproc.Component');
	var _signedDocumentType = /*#__PURE__*/new WeakMap();
	var _signedDocumentId = /*#__PURE__*/new WeakMap();
	var _counters = /*#__PURE__*/new WeakMap();
	var _onAfterGridUpdated = /*#__PURE__*/new WeakSet();
	var _renderStartedByMeNow = /*#__PURE__*/new WeakSet();
	var WorkflowStartList = /*#__PURE__*/function () {
	  function WorkflowStartList(options) {
	    babelHelpers.classCallCheck(this, WorkflowStartList);
	    _classPrivateMethodInitSpec(this, _renderStartedByMeNow);
	    _classPrivateMethodInitSpec(this, _onAfterGridUpdated);
	    _classPrivateFieldInitSpec(this, _signedDocumentType, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _signedDocumentId, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _counters, {
	      writable: true,
	      value: new Map()
	    });
	    if (!main_core.Type.isPlainObject(options)) {
	      return;
	    }
	    this.gridId = options.gridId;
	    this.createTemplateButton = options.createTemplateButton;
	    this.errorsContainerDiv = options.errorsContainerDiv;
	    if (main_core.Type.isStringFilled(options.signedDocumentType)) {
	      babelHelpers.classPrivateFieldSet(this, _signedDocumentType, options.signedDocumentType);
	    }
	    if (main_core.Type.isStringFilled(options.signedDocumentId)) {
	      babelHelpers.classPrivateFieldSet(this, _signedDocumentId, options.signedDocumentId);
	    }
	  }
	  babelHelpers.createClass(WorkflowStartList, [{
	    key: "init",
	    value: function init() {
	      var _this = this;
	      if (this.createTemplateButton) {
	        main_core.Event.bind(this.createTemplateButton, 'click', function (event) {
	          return _this.createTemplate();
	        });
	      }
	      BX.UI.Hint.init(document);
	      if (this.getGrid()) {
	        BX.Bizproc.Component.WorkflowStartList.colorPinnedRows(this.getGrid());
	      }
	      main_core_events.EventEmitter.subscribe('Grid::updated', _classPrivateMethodGet(this, _onAfterGridUpdated, _onAfterGridUpdated2).bind(this));
	    }
	  }, {
	    key: "createTemplate",
	    value: function createTemplate() {
	      alert('Create Template');
	    }
	  }, {
	    key: "editTemplate",
	    value: function editTemplate(templateId) {
	      alert('Edit Template ' + templateId);
	    }
	  }, {
	    key: "showErrors",
	    value: function showErrors(errors) {
	      var _this2 = this;
	      this.errorsContainerDiv.style.margin = '10px';
	      errors.forEach(function (error) {
	        var alert = new ui_alerts.Alert({
	          text: error.message,
	          color: ui_alerts.AlertColor.DANGER,
	          closeBtn: true,
	          animated: true
	        });
	        alert.renderTo(_this2.errorsContainerDiv);
	      });
	    }
	  }, {
	    key: "reloadGrid",
	    value: function reloadGrid() {
	      var grid = this.getGrid();
	      if (grid) {
	        grid.reload();
	      }
	    }
	  }, {
	    key: "getGrid",
	    value: function getGrid() {
	      if (this.gridId) {
	        return BX.Main.gridManager && BX.Main.gridManager.getInstanceById(this.gridId);
	      }
	      return null;
	    }
	  }, {
	    key: "startWorkflow",
	    value: function startWorkflow(event, templateId) {
	      var _this3 = this;
	      event.preventDefault();
	      var id = main_core.Text.toNumber(templateId);
	      if (id <= 0 || !babelHelpers.classPrivateFieldGet(this, _signedDocumentType) || !babelHelpers.classPrivateFieldGet(this, _signedDocumentId)) {
	        return;
	      }
	      var afterSuccessStart = function afterSuccessStart() {
	        var _this3$getGrid;
	        var slider = BX.SidePanel.Instance.getSliderByWindow(window);
	        if (slider) {
	          slider.close();
	          return;
	        }
	        if (!babelHelpers.classPrivateFieldGet(_this3, _counters).has(templateId)) {
	          babelHelpers.classPrivateFieldGet(_this3, _counters).set(templateId, 0);
	        }
	        babelHelpers.classPrivateFieldGet(_this3, _counters).set(templateId, babelHelpers.classPrivateFieldGet(_this3, _counters).get(templateId) + 1);
	        (_this3$getGrid = _this3.getGrid()) === null || _this3$getGrid === void 0 ? void 0 : _this3$getGrid.reload();
	      };
	      bizproc_workflow_starter.Starter.singleStart({
	        signedDocumentId: babelHelpers.classPrivateFieldGet(this, _signedDocumentId),
	        signedDocumentType: babelHelpers.classPrivateFieldGet(this, _signedDocumentType),
	        templateId: id
	      }, afterSuccessStart);
	    }
	  }], [{
	    key: "changePin",
	    value: function changePin(templateId, gridId, event) {
	      var eventData = event.getData();
	      var button = eventData.button;
	      if (main_core.Dom.hasClass(button, BX.Grid.CellActionState.ACTIVE)) {
	        BX.Bizproc.Component.WorkflowStartList.action('unpin', templateId, gridId);
	        main_core.Dom.removeClass(button, BX.Grid.CellActionState.ACTIVE);
	      } else {
	        BX.Bizproc.Component.WorkflowStartList.action('pin', templateId, gridId);
	        main_core.Dom.addClass(button, BX.Grid.CellActionState.ACTIVE);
	      }
	      var grid = BX.Main.gridManager.getInstanceById(gridId);
	      if (grid) {
	        BX.Bizproc.Component.WorkflowStartList.colorPinnedRows(grid);
	      }
	    }
	  }, {
	    key: "action",
	    value: function action(_action, templateId, gridId) {
	      var component = 'bitrix:bizproc.workflow.start.list';
	      BX.ajax.runComponentAction(component, _action, {
	        mode: 'class',
	        data: {
	          templateId: templateId
	        }
	      }).then(function (response) {
	        var grid = BX.Main.gridManager.getInstanceById(gridId);
	        if (grid) {
	          grid.reload();
	        }
	      });
	    }
	  }, {
	    key: "colorPinnedRows",
	    value: function colorPinnedRows(grid) {
	      grid.getRows().getRows().forEach(function (row) {
	        var node = row.getNode();
	        if (main_core.Type.isElementNode(node.querySelector('.main-grid-cell-content-action-pin.main-grid-cell-content-action-active'))) {
	          main_core.Dom.addClass(node, 'bizproc-workflow-start-list-item-pinned');
	        } else {
	          main_core.Dom.removeClass(node, 'bizproc-workflow-start-list-item-pinned');
	        }
	      });
	    }
	  }]);
	  return WorkflowStartList;
	}();
	function _onAfterGridUpdated2() {
	  var _this4 = this;
	  if (this.getGrid()) {
	    BX.UI.Hint.init(this.getGrid().getContainer());
	    BX.Bizproc.Component.WorkflowStartList.colorPinnedRows(this.getGrid());
	  }
	  babelHelpers.classPrivateFieldGet(this, _counters).forEach(function (value, key) {
	    var counter = document.querySelector("[data-role=\"template-".concat(key, "-counter\"]"));
	    if (main_core.Type.isElementNode(counter)) {
	      main_core.Dom.clean(counter);
	      main_core.Dom.append(_classPrivateMethodGet(_this4, _renderStartedByMeNow, _renderStartedByMeNow2).call(_this4, key), counter);
	    }
	  });
	}
	function _renderStartedByMeNow2(templateId) {
	  var message = main_core.Text.encode(main_core.Loc.getMessage('BIZPROC_CMP_TMP_WORKKFLOW_START_LIST_START_COUNTER', {
	    '#COUNTER#': babelHelpers.classPrivateFieldGet(this, _counters).get(templateId)
	  }));
	  message = message.replace('[bold]', '<span class="bizproc-workflow-start-list-column-start-counter">');
	  message = message.replace('[/bold]', '</span>');
	  return main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-typography-text-xs\">", "</div>"])), message);
	}
	namespace.WorkflowStartList = WorkflowStartList;

}((this.window = this.window || {}),BX,BX.Event,BX.UI,BX.Bizproc.Workflow));
//# sourceMappingURL=script.js.map
