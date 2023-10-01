/* eslint-disable */
this.BX = this.BX || {};
(function (exports,main_core,ui_dialogs_messagebox,ui_notification,main_popup,ui_buttons) {
	'use strict';

	var _templateObject;
	function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }
	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var instance = null;
	var _startScriptInternal = /*#__PURE__*/new WeakSet();
	var _keepAliveQueue = /*#__PURE__*/new WeakSet();
	var _showFillParametersPopup = /*#__PURE__*/new WeakSet();
	var _findGridInstance = /*#__PURE__*/new WeakSet();
	var Manager = /*#__PURE__*/function () {
	  function Manager() {
	    babelHelpers.classCallCheck(this, Manager);
	    _classPrivateMethodInitSpec(this, _findGridInstance);
	    _classPrivateMethodInitSpec(this, _showFillParametersPopup);
	    _classPrivateMethodInitSpec(this, _keepAliveQueue);
	    _classPrivateMethodInitSpec(this, _startScriptInternal);
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
	    key: "renderParametersPopupContent",
	    value: function renderParametersPopupContent(parameters, documentType) {
	      var form = main_core.Dom.create('form', {
	        attrs: {
	          className: 'bp-script-start-form'
	        }
	      });
	      parameters.forEach(function (param) {
	        var field = BX.Bizproc.FieldType.renderControl(documentType, param, param.Id, param.Default || '');
	        var description = param.Description ? main_core.Dom.create('span', {
	          text: param.Description,
	          attrs: {
	            className: 'bp-script-start-form-row-desc'
	          }
	        }) : '';
	        main_core.Dom.append(main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class=\"bp-script-start-form-row\">\n\t\t\t\t\t\t<span class=\"bp-script-start-form-row-title\">", "</span>\n\t\t\t\t\t\t", "\n\t\t\t\t\t\t<div class=\"bp-script-start-form-row-field\">", "</div>\n\t\t\t\t\t</div>\n\t\t\t\t"])), main_core.Text.encode(param.Name), description, field), form);
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
	      }

	      //Prepare crm document ids
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
	      options = _objectSpread(_objectSpread({}, {
	        cacheable: false,
	        allowChangeHistory: true,
	        events: {}
	      }), options);
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
	function _startScriptInternal2(scriptId, documentIds) {
	  var _this2 = this;
	  var parameters = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};
	  var popup = arguments.length > 3 ? arguments[3] : undefined;
	  var data = {
	    scriptId: scriptId,
	    documentIds: documentIds,
	    parameters: parameters
	  };
	  if (parameters instanceof FormData) {
	    data = parameters;
	    data.set('scriptId', scriptId);
	    documentIds.forEach(function (id) {
	      return data.append('documentIds[]', id);
	    });
	  }
	  main_core.ajax.runAction('bizproc.script.start', {
	    analyticsLabel: 'bizprocScriptStart',
	    data: data
	  }).then(function (response) {
	    if (response.data.error) {
	      ui_dialogs_messagebox.MessageBox.alert(response.data.error);
	    }
	    if (response.data.status === 'FILL_PARAMETERS') {
	      _classPrivateMethodGet(_this2, _showFillParametersPopup, _showFillParametersPopup2).call(_this2, scriptId, documentIds, response.data);
	    } else if (response.data.status === 'INVALID_PARAMETERS') ; else if (response.data.status === 'QUEUED') {
	      if (popup) {
	        popup.close();
	      }
	      ui_notification.UI.Notification.Center.notify({
	        content: main_core.Loc.getMessage('BIZPROC_SCRIPT_MANAGER_START_QUEUED')
	      });
	      _classPrivateMethodGet(_this2, _keepAliveQueue, _keepAliveQueue2).call(_this2, response.data.queueId);
	    }
	  })["catch"](function (response) {
	    return ui_dialogs_messagebox.MessageBox.alert(response.errors.pop().message);
	  });
	}
	function _keepAliveQueue2(queueId) {
	  var _this3 = this;
	  var delay = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 500;
	  setTimeout(function () {
	    main_core.ajax.runAction('bizproc.script.execQueue', {
	      data: {
	        queueId: queueId
	      }
	    }).then(function (response) {
	      if (!response.data.finished) {
	        _classPrivateMethodGet(_this3, _keepAliveQueue, _keepAliveQueue2).call(_this3, queueId, delay);
	      } else {
	        ui_notification.UI.Notification.Center.notify({
	          content: main_core.Loc.getMessage('BIZPROC_SCRIPT_MANAGER_START_FINISHED')
	        });
	      }
	    });
	  }, delay);
	}
	function _showFillParametersPopup2(scriptId, documentIds, _ref) {
	  var _this4 = this;
	  var parameters = _ref.parameters,
	    documentType = _ref.documentType,
	    scriptName = _ref.scriptName;
	  var form = this.renderParametersPopupContent(parameters, documentType);
	  var popup = new main_popup.Popup(null, null, {
	    events: {
	      onPopupClose: function onPopupClose() {
	        popup.destroy();
	      }
	    },
	    titleBar: scriptName || main_core.Loc.getMessage('BIZPROC_SCRIPT_MANAGER_START_PARAMS_POPUP_TITLE'),
	    content: form,
	    width: 595,
	    contentNoPaddings: true,
	    buttons: [new ui_buttons.Button({
	      text: main_core.Loc.getMessage('BIZPROC_SCRIPT_MANAGER_START_BUTTON_SEND_PARAMS'),
	      color: ui_buttons.Button.Color.SUCCESS,
	      onclick: function onclick() {
	        _classPrivateMethodGet(_this4, _startScriptInternal, _startScriptInternal2).call(_this4, scriptId, documentIds, new FormData(form), popup);
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
	function _findGridInstance2(entity) {
	  if (!BX.Main.gridManager) {
	    return null;
	  }
	  var gridId = "CRM_".concat(entity.toUpperCase(), "_LIST");
	  var grid = BX.Main.gridManager.data.find(function (current) {
	    return current.id.indexOf(gridId) === 0 || current.id.indexOf('crm-type-item-list') === 0;
	  });
	  return grid ? grid.instance : null;
	}

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
