this.BX = this.BX || {};
(function (exports,main_core,ui_dialogs_messagebox,ui_notification,main_popup,ui_buttons) {
	'use strict';

	function _templateObject() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div><p><b>", "</b></p><div>", "</div></div>"]);

	  _templateObject = function _templateObject() {
	    return data;
	  };

	  return data;
	}

	function _createForOfIteratorHelper(o, allowArrayLike) { var it; if (typeof Symbol === "undefined" || o[Symbol.iterator] == null) { if (Array.isArray(o) || (it = _unsupportedIterableToArray(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = o[Symbol.iterator](); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it.return != null) it.return(); } finally { if (didErr) throw err; } } }; }

	function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

	function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var instance = null;

	var _startScriptInternal = new WeakSet();

	var _keepAliveQueue = new WeakSet();

	var _findGridInstance = new WeakSet();

	var Manager = /*#__PURE__*/function () {
	  function Manager() {
	    babelHelpers.classCallCheck(this, Manager);

	    _findGridInstance.add(this);

	    _keepAliveQueue.add(this);

	    _startScriptInternal.add(this);

	    babelHelpers.defineProperty(this, "scriptEditUrl", '/bitrix/components/bitrix/bizproc.script.edit/');
	    babelHelpers.defineProperty(this, "scriptListUrl", '/bitrix/components/bitrix/bizproc.script.list/');
	    babelHelpers.defineProperty(this, "scriptQueueListUrl", '/bitrix/components/bitrix/bizproc.script.queue.list/');
	    babelHelpers.defineProperty(this, "scriptQueueDocumentListUrl", '/bitrix/components/bitrix/bizproc.script.queue.document.list/');
	  }

	  babelHelpers.createClass(Manager, [{
	    key: "startScript",
	    value: function startScript(scriptId, placement) {
	      var _this = this;

	      var documentIds = this.getDocumentIds.apply(this, babelHelpers.toConsumableArray(placement.split(':')));

	      if (!documentIds.length) {
	        ui_dialogs_messagebox.MessageBox.alert(main_core.Loc.getMessage('BIZPROC_SCRIPT_MANAGER_START_NOTHING_SELECTED'));
	        return;
	      }

	      var startCallback = function startCallback() {
	        _classPrivateMethodGet(_this, _startScriptInternal, _startScriptInternal2).call(_this, scriptId, documentIds);

	        return true;
	      };

	      if (documentIds.length > 1) {
	        ui_dialogs_messagebox.MessageBox.confirm(main_core.Loc.getMessage('BIZPROC_SCRIPT_MANAGER_START_TEXT_START').replace('#CNT#', documentIds.length), startCallback, main_core.Loc.getMessage('BIZPROC_SCRIPT_MANAGER_START_BUTTON_START'));
	      } else {
	        startCallback();
	      }
	    }
	  }, {
	    key: "showFillParametersPopup",
	    value: function showFillParametersPopup(scriptId, documentIds, parameters, documentType) {
	      var _this2 = this;

	      var form = this.renderParametersPopupContent(parameters, documentType);
	      var popup = new main_popup.Popup(null, null, {
	        events: {
	          onPopupClose: function onPopupClose() {
	            popup.destroy();
	          }
	        },
	        titleBar: main_core.Loc.getMessage('BIZPROC_SCRIPT_MANAGER_START_PARAMS_POPUP_TITLE'),
	        content: form,
	        minWidth: 500,
	        buttons: [new ui_buttons.Button({
	          text: main_core.Loc.getMessage('BIZPROC_SCRIPT_MANAGER_START_BUTTON_SEND_PARAMS'),
	          color: ui_buttons.Button.Color.SUCCESS,
	          onclick: function onclick() {
	            var paramFields = {};

	            var _iterator = _createForOfIteratorHelper(new FormData(form).entries()),
	                _step;

	            try {
	              for (_iterator.s(); !(_step = _iterator.n()).done;) {
	                var field = _step.value;
	                paramFields[field[0]] = field[1];
	              }
	            } catch (err) {
	              _iterator.e(err);
	            } finally {
	              _iterator.f();
	            }

	            _classPrivateMethodGet(_this2, _startScriptInternal, _startScriptInternal2).call(_this2, scriptId, documentIds, paramFields, popup);
	          }
	        }), new BX.UI.Button({
	          text: main_core.Loc.getMessage('UI_MESSAGE_BOX_CANCEL_CAPTION'),
	          color: BX.UI.Button.Color.LINK,
	          onclick: function onclick() {
	            popup.close();
	          }
	        })]
	      });
	      popup.show();
	    }
	  }, {
	    key: "renderParametersPopupContent",
	    value: function renderParametersPopupContent(parameters, documentType) {
	      var form = main_core.Dom.create('form');
	      parameters.forEach(function (param) {
	        var field = BX.Bizproc.FieldType.renderControl(documentType, param, param.Id, param.Default || '');
	        main_core.Dom.append(main_core.Tag.render(_templateObject(), main_core.Text.encode(param.Name), field), form);
	      });
	      return form;
	    }
	  }, {
	    key: "getDocumentIds",
	    value: function getDocumentIds(section, entity) {
	      var ids = [];

	      if (section === 'crm_switcher') {
	        var grid = _classPrivateMethodGet(this, _findGridInstance, _findGridInstance2).call(this, entity);

	        if (grid) {
	          ids = grid.getRows().getSelectedIds();
	        } else if (BX.CRM && BX.CRM.Kanban && BX.CRM.Kanban.Grid && BX.CRM.Kanban.Grid.Instance) {
	          ids = BX.CRM.Kanban.Grid.Instance.getCheckedId();
	        }
	      } else if (section === 'crm_detail') {
	        ids = [BX.Crm.EntityEditor.getDefault().getEntityId()];
	      } //Prepare crm document ids


	      if (main_core.Type.isArrayFilled(ids)) {
	        ids = ids.map(function (id) {
	          return "".concat(entity.toUpperCase(), "_").concat(id);
	        });
	      }

	      return ids;
	    }
	  }, {
	    key: "createScript",
	    value: function createScript(documentType, placement) {
	      return Manager.openSlider(main_core.Uri.addParam(this.scriptEditUrl, {
	        documentType: documentType,
	        placement: placement
	      }), {
	        width: 930,
	        cacheable: false,
	        allowChangeHistory: false
	      });
	    }
	  }, {
	    key: "showScriptList",
	    value: function showScriptList(documentType, placement) {
	      Manager.openSlider(main_core.Uri.addParam(this.scriptListUrl, {
	        documentType: documentType,
	        placement: placement
	      }), {
	        cacheable: false,
	        allowChangeHistory: false
	      }).then(function (slider) {
	        if (slider.isLoaded()) ;
	      });
	    }
	  }, {
	    key: "showScriptQueueList",
	    value: function showScriptQueueList(scriptId) {
	      Manager.openSlider(main_core.Uri.addParam(this.scriptQueueListUrl, {
	        scriptId: scriptId
	      }), {
	        cacheable: false,
	        allowChangeHistory: false
	      });
	    }
	  }, {
	    key: "showScriptQueueDocumentList",
	    value: function showScriptQueueDocumentList(queueId) {
	      Manager.openSlider(main_core.Uri.addParam(this.scriptQueueDocumentListUrl, {
	        queueId: queueId
	      }), {
	        cacheable: false,
	        allowChangeHistory: false
	      });
	    }
	  }, {
	    key: "editScript",
	    value: function editScript(scriptId, placement) {
	      return Manager.openSlider(main_core.Uri.addParam(this.scriptEditUrl, {
	        scriptId: scriptId,
	        placement: placement
	      }), {
	        width: 930,
	        cacheable: false,
	        allowChangeHistory: false
	      });
	    }
	  }, {
	    key: "deleteScript",
	    value: function deleteScript(scriptId) {
	      return main_core.ajax.runAction('bizproc.script.delete', {
	        analyticsLabel: 'bizprocScriptDelete',
	        data: {
	          scriptId: scriptId
	        }
	      });
	    }
	  }, {
	    key: "activateScript",
	    value: function activateScript(scriptId) {
	      return main_core.ajax.runAction('bizproc.script.activate', {
	        analyticsLabel: 'bizprocScriptActivate',
	        data: {
	          scriptId: scriptId
	        }
	      });
	    }
	  }, {
	    key: "deactivateScript",
	    value: function deactivateScript(scriptId) {
	      return main_core.ajax.runAction('bizproc.script.deactivate', {
	        analyticsLabel: 'bizprocScriptDeactivate',
	        data: {
	          scriptId: scriptId
	        }
	      });
	    }
	  }, {
	    key: "terminateScriptQueue",
	    value: function terminateScriptQueue(queueId) {
	      main_core.ajax.runAction('bizproc.script.terminateQueue', {
	        analyticsLabel: 'bizprocScriptTerminateQueue',
	        data: {
	          queueId: queueId
	        }
	      }).then(function (response) {
	        if (response.data.error) {
	          ui_dialogs_messagebox.MessageBox.alert(response.data.error);
	        }
	      });
	    }
	  }, {
	    key: "deleteScriptQueue",
	    value: function deleteScriptQueue(queueId) {
	      main_core.ajax.runAction('bizproc.script.deleteQueue', {
	        analyticsLabel: 'bizprocScriptDeleteQueue',
	        data: {
	          queueId: queueId
	        }
	      }).then(function (response) {
	        if (response.data.error) {
	          ui_dialogs_messagebox.MessageBox.alert(response.data.error);
	        }
	      });
	    }
	  }], [{
	    key: "openSlider",
	    value: function openSlider(url, options) {
	      if (!main_core.Type.isPlainObject(options)) {
	        options = {};
	      }

	      options = babelHelpers.objectSpread({}, {
	        cacheable: false,
	        allowChangeHistory: true,
	        events: {}
	      }, options);
	      return new Promise(function (resolve) {
	        if (main_core.Type.isString(url) && url.length > 1) {
	          options.events.onClose = function (event) {
	            resolve(event.getSlider());
	          };

	          BX.SidePanel.Instance.open(url, options);
	        } else {
	          resolve();
	        }
	      });
	    }
	  }, {
	    key: "Instance",
	    get: function get() {
	      if (instance === null) {
	        instance = new Manager();
	      }

	      return instance;
	    }
	  }]);
	  return Manager;
	}();

	var _startScriptInternal2 = function _startScriptInternal2(scriptId, documentIds) {
	  var _this3 = this;

	  var parameters = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};
	  var popup = arguments.length > 3 ? arguments[3] : undefined;
	  main_core.ajax.runAction('bizproc.script.start', {
	    analyticsLabel: 'bizprocScriptStart',
	    data: {
	      scriptId: scriptId,
	      documentIds: documentIds,
	      parameters: parameters
	    }
	  }).then(function (response) {
	    if (response.data.error) {
	      ui_dialogs_messagebox.MessageBox.alert(response.data.error);
	    }

	    if (response.data.status === 'FILL_PARAMETERS') {
	      _this3.showFillParametersPopup(scriptId, documentIds, response.data.parameters, response.data.documentType);
	    } else if (response.data.status === 'INVALID_PARAMETERS') ; else if (response.data.status === 'QUEUED') {
	      if (popup) {
	        popup.close();
	      }

	      ui_notification.UI.Notification.Center.notify({
	        content: main_core.Loc.getMessage('BIZPROC_SCRIPT_MANAGER_START_QUEUED')
	      });

	      _classPrivateMethodGet(_this3, _keepAliveQueue, _keepAliveQueue2).call(_this3, response.data.queueId);
	    }
	  });
	};

	var _keepAliveQueue2 = function _keepAliveQueue2(queueId) {
	  var _this4 = this;

	  var delay = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 500;
	  setTimeout(function () {
	    main_core.ajax.runAction('bizproc.script.execQueue', {
	      data: {
	        queueId: queueId
	      }
	    }).then(function (response) {
	      if (!response.data.finished) {
	        _classPrivateMethodGet(_this4, _keepAliveQueue, _keepAliveQueue2).call(_this4, queueId, delay);
	      } else {
	        ui_notification.UI.Notification.Center.notify({
	          content: main_core.Loc.getMessage('BIZPROC_SCRIPT_MANAGER_START_FINISHED')
	        });
	      }
	    });
	  }, delay);
	};

	var _findGridInstance2 = function _findGridInstance2(entity) {
	  if (!BX.Main.gridManager) {
	    return null;
	  }

	  var gridId = "CRM_".concat(entity.toUpperCase(), "_LIST");
	  var grid = BX.Main.gridManager.data.find(function (current) {
	    return current.id.indexOf(gridId) === 0;
	  });
	  return grid ? grid.instance : null;
	};

	var instance$1 = null;

	var Market = /*#__PURE__*/function () {
	  function Market() {
	    babelHelpers.classCallCheck(this, Market);
	  }

	  babelHelpers.createClass(Market, [{
	    key: "showForPlacement",
	    value: function showForPlacement(placement) {
	      if (BX.rest && BX.rest.Marketplace) {
	        BX.rest.Marketplace.open({
	          PLACEMENT: placement
	        });
	      }
	    }
	  }], [{
	    key: "Instance",
	    get: function get() {
	      if (instance$1 === null) {
	        instance$1 = new Market();
	      }

	      return instance$1;
	    }
	  }]);
	  return Market;
	}();

	var Script = {
	  Market: Market,
	  Manager: Manager
	};

	exports.Script = Script;

}((this.BX.Bizproc = this.BX.Bizproc || {}),BX,BX.UI.Dialogs,BX,BX.Main,BX.UI));
//# sourceMappingURL=script.bundle.js.map
