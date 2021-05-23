(function (exports,main_core,main_core_events,ui_notification) {
	'use strict';

	function _templateObject7() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<input value=\"\" type=\"text\" class=\"ui-ctl-element\">\n\t\t"]);

	  _templateObject7 = function _templateObject7() {
	    return data;
	  };

	  return data;
	}

	function _templateObject6() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"bizproc-script-edit-item\">\n\t\t\t\t<div class=\"bizproc-script-edit-subtitle\">", "</div>\n\t\t\t\t<div class=\"bizproc-script-edit-text\">", "</div>\n\t\t\t\t<a onclick=\"", "\" class=\"ui-link ui-link-secondary ui-link-dashed\">", "</a>\n\t\t\t\t<div class=\"bizproc-script-edit-field\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t</div>"]);

	  _templateObject6 = function _templateObject6() {
	    return data;
	  };

	  return data;
	}

	function _templateObject5() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"ui-slider-section\">\n\t\t\t\t<div class=\"ui-slider-heading-4 ui-slider-heading-4--bizproc-icon\">", "</div>\n\t\t\t\t", "\n\t\t\t</div>"]);

	  _templateObject5 = function _templateObject5() {
	    return data;
	  };

	  return data;
	}

	function _templateObject4() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"bizproc-script-edit-item\">\n\t\t\t\t<div class=\"bizproc-script-edit-title\">", "</div>\n\t\t\t\t<div class=\"bizproc-script-edit-text\">", "</div>\n\t\t\t</div>"]);

	  _templateObject4 = function _templateObject4() {
	    return data;
	  };

	  return data;
	}

	function _templateObject3() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"bizproc-script-edit-item\">\n\t\t\t\t<div class=\"bizproc-script-edit-title\">", "</div>\n\t\t\t\t<div class=\"bizproc-script-edit-text\">", "</div>\n\t\t\t</div>"]);

	  _templateObject3 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-alert ui-alert-default ui-alert-xs ui-alert-icon-info\">\n\t\t\t\t\t<span class=\"ui-alert-message\">", "</span>\n\t\t\t\t</div>"]);

	  _templateObject2 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject() {
	  var data = babelHelpers.taggedTemplateLiteral(["<form data-role=\"constant-list\" onsubmit=\"return false;\">", "</form>"]);

	  _templateObject = function _templateObject() {
	    return data;
	  };

	  return data;
	}

	function _createForOfIteratorHelper(o, allowArrayLike) { var it; if (typeof Symbol === "undefined" || o[Symbol.iterator] == null) { if (Array.isArray(o) || (it = _unsupportedIterableToArray(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = o[Symbol.iterator](); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it.return != null) it.return(); } finally { if (didErr) throw err; } } }; }

	function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

	function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var namespace = main_core.Reflection.namespace('BX.Bizproc');

	var toJsonString = function toJsonString(data) {
	  return JSON.stringify(data, function (i, v) {
	    if (typeof v === 'boolean') {
	      return v ? '1' : '0';
	    }

	    return v;
	  });
	};

	var _getRobotsTemplate = new WeakSet();

	var _activateSection = new WeakSet();

	var _validateScriptName = new WeakSet();

	var _validateConstants = new WeakSet();

	var ScriptEditComponent = /*#__PURE__*/function () {
	  function ScriptEditComponent(options) {
	    babelHelpers.classCallCheck(this, ScriptEditComponent);

	    _validateConstants.add(this);

	    _validateScriptName.add(this);

	    _activateSection.add(this);

	    _getRobotsTemplate.add(this);

	    babelHelpers.defineProperty(this, "constantPrefix", 'Constant__');
	    babelHelpers.defineProperty(this, "parameterPrefix", 'Parameter__');

	    if (main_core.Type.isPlainObject(options)) {
	      this.baseNode = options.baseNode;
	      this.leftMenuNode = options.leftMenuNode;
	      this.saveButtonNode = options.saveButtonNode;
	      this.formNode = options.formNode;
	      this.documentType = options.documentType;
	      this.signedParameters = options.signedParameters;
	      this.saveCallback = options.saveCallback;
	    }

	    this.automationDesigner = BX.Bizproc.Automation.Designer.component;
	  }

	  babelHelpers.createClass(ScriptEditComponent, [{
	    key: "init",
	    value: function init() {
	      var _this = this;

	      if (this.saveButtonNode) {
	        main_core.Event.bind(this.saveButtonNode, 'click', this.saveHandler.bind(this));
	      }

	      if (this.baseNode && this.leftMenuNode) {
	        this.initMenu();
	      }

	      if (this.formNode) {
	        this.scriptNameNode = this.formNode.elements.NAME;
	        main_core.Event.bind(this.scriptNameNode, 'blur', function () {
	          if (!main_core.Type.isStringFilled(_this.scriptNameNode.value)) {
	            main_core.Dom.addClass(_this.scriptNameNode.closest('.ui-ctl'), 'ui-ctl-danger');
	          } else {
	            main_core.Dom.removeClass(_this.scriptNameNode.closest('.ui-ctl'), 'ui-ctl-danger');
	          }
	        });
	      }

	      if (this.automationDesigner) {
	        main_core_events.EventEmitter.subscribe(this.automationDesigner, 'onTemplateConstantAdd', function () {
	          if (_this.configsMenuItem) {
	            _this.configsMenuItem.addNoticeIcon();
	          }
	        });
	      }
	    }
	  }, {
	    key: "saveHandler",
	    value: function saveHandler() {
	      var _this2 = this;

	      var form = new FormData(this.formNode);
	      var scriptFields = {};

	      var _iterator = _createForOfIteratorHelper(form.entries()),
	          _step;

	      try {
	        for (_iterator.s(); !(_step = _iterator.n()).done;) {
	          var field = _step.value;
	          scriptFields[field[0]] = field[1];
	        }
	      } catch (err) {
	        _iterator.e(err);
	      } finally {
	        _iterator.f();
	      }

	      if (!_classPrivateMethodGet(this, _validateScriptName, _validateScriptName2).call(this, scriptFields.NAME)) {
	        main_core.Dom.removeClass(this.saveButtonNode, 'ui-btn-wait');
	        return false;
	      }

	      var robotsTemplate = _classPrivateMethodGet(this, _getRobotsTemplate, _getRobotsTemplate2).call(this);

	      this.setTemplateValues(robotsTemplate);

	      if (!_classPrivateMethodGet(this, _validateConstants, _validateConstants2).call(this, robotsTemplate.getConstants(), robotsTemplate.collectUsages().Constant)) {
	        main_core.Dom.removeClass(this.saveButtonNode, 'ui-btn-wait');
	        return false;
	      }

	      BX.ajax.runComponentAction('bitrix:bizproc.script.edit', 'saveScript', {
	        analyticsLabel: scriptFields.ID > 0 ? 'bizprocScriptUpdate' : 'bizprocScriptAdd',
	        data: {
	          signedParameters: this.signedParameters,
	          documentType: this.documentType,
	          script: scriptFields,
	          robotsTemplate: toJsonString(robotsTemplate.serialize())
	        }
	      }).then(function (result) {
	        if (main_core.Type.isFunction(_this2.saveCallback)) {
	          _this2.saveCallback(result);
	        }
	      });
	    }
	  }, {
	    key: "initMenu",
	    value: function initMenu() {
	      var _this3 = this;

	      Array.from(this.leftMenuNode.querySelectorAll('[data-role="menu-item"]')).forEach(function (el) {
	        main_core.Event.bind(el, 'click', _this3.menuActivateHandler.bind(_this3, el.getAttribute('data-page')));

	        if (el.getAttribute('data-page') === 'configs' && BX.UI.DropdownMenuItem.getItemByNode) {
	          _this3.configsMenuItem = BX.UI.DropdownMenuItem.getItemByNode(el);
	        }
	      });
	    }
	  }, {
	    key: "menuActivateHandler",
	    value: function menuActivateHandler(page) {
	      var _this4 = this;

	      Array.from(this.baseNode.querySelectorAll('[data-section]')).forEach(function (el) {
	        if (el.getAttribute('data-section') === page) {
	          if (page === 'configs' && main_core.Dom.hasClass(el, 'bizproc-script-edit-block-hidden')) {
	            _this4.showConfigsHandler(el);
	          } else {
	            _this4.setTemplateValues(_classPrivateMethodGet(_this4, _getRobotsTemplate, _getRobotsTemplate2).call(_this4));
	          }

	          main_core.Dom.removeClass(el, 'bizproc-script-edit-block-hidden');
	        } else {
	          main_core.Dom.addClass(el, 'bizproc-script-edit-block-hidden');
	        }
	      });
	    }
	  }, {
	    key: "showConfigsHandler",
	    value: function showConfigsHandler(configsNode) {
	      var _this5 = this;

	      main_core.Dom.clean(configsNode);

	      var robotsTemplate = _classPrivateMethodGet(this, _getRobotsTemplate, _getRobotsTemplate2).call(this);

	      var constants = robotsTemplate.getConstants();
	      var parameters = robotsTemplate.getParameters();
	      var robotNodes = [];
	      robotsTemplate.robots.forEach(function (robot) {
	        var node = _this5.renderRobotConfigBlock(robot, constants, parameters);

	        if (node) {
	          robotNodes.push(node);
	        }
	      });

	      if (robotNodes.length) {
	        main_core.Dom.append(main_core.Tag.render(_templateObject(), robotNodes), configsNode);
	      } else {
	        return main_core.Dom.append(main_core.Tag.render(_templateObject2(), main_core.Loc.getMessage('BIZPROC_SCRIPT_EDIT_SECTION_CONFIGS_EMPTY')), configsNode);
	      }
	    }
	  }, {
	    key: "renderRobotConfigBlock",
	    value: function renderRobotConfigBlock(robot, constants, parameters) {
	      var _this6 = this;

	      var usages = robot.collectUsages();
	      var itemNodes = [];

	      if (!usages.Constant.size && !usages.Parameter.size) {
	        return null;
	      }

	      if (usages.Constant.size) {
	        itemNodes.push(main_core.Tag.render(_templateObject3(), main_core.Loc.getMessage('BIZPROC_SCRIPT_EDIT_CONSTANT_LABEL'), main_core.Loc.getMessage('BIZPROC_SCRIPT_EDIT_CONSTANT_DESCRIPTION')));
	        usages.Constant.forEach(function (constId) {
	          var constant = constants.find(function (c) {
	            return c.Id === constId;
	          });

	          if (constant) {
	            itemNodes.push(_this6.renderPropertyBlock(constant, _this6.constantPrefix));
	          }
	        });
	      }

	      if (usages.Parameter.size) {
	        itemNodes.push(main_core.Tag.render(_templateObject4(), main_core.Loc.getMessage('BIZPROC_SCRIPT_EDIT_PARAMETER_LABEL'), main_core.Loc.getMessage('BIZPROC_SCRIPT_EDIT_PARAMETER_DESCRIPTION')));
	        usages.Parameter.forEach(function (paramId) {
	          var parameter = parameters.find(function (p) {
	            return p.Id === paramId;
	          });

	          if (parameter) {
	            itemNodes.push(_this6.renderPropertyBlock(parameter, _this6.parameterPrefix));
	          }
	        });
	      }

	      return main_core.Tag.render(_templateObject5(), main_core.Text.encode(robot.getTitle()), itemNodes);
	    }
	  }, {
	    key: "renderPropertyBlock",
	    value: function renderPropertyBlock(property, prefix) {
	      var control = BX.Bizproc.FieldType.renderControl(this.automationDesigner.documentType, property, prefix + property.Id, property.Default);
	      return main_core.Tag.render(_templateObject6(), main_core.Text.encode(property.Name), main_core.Text.encode(property.Description), this.changePropertyDescription.bind(this, prefix, property), main_core.Loc.getMessage('BIZPROC_SCRIPT_EDIT_BTN_CHANGE'), control);
	    }
	  }, {
	    key: "changePropertyDescription",
	    value: function changePropertyDescription(prefix, property, event) {
	      var _this7 = this;

	      var element = event.currentTarget;
	      var wrapper = element.previousElementSibling;
	      main_core.Dom.hide(element);
	      var inputElement = main_core.Tag.render(_templateObject7());
	      inputElement.value = property.Description || '';
	      main_core.Dom.clean(wrapper);
	      main_core.Dom.append(inputElement, wrapper);
	      inputElement.focus();

	      var applyNewDescription = function applyNewDescription() {
	        var text = inputElement.value.trim();
	        property.Description = text;
	        main_core.Dom.clean(wrapper);
	        wrapper.textContent = text;
	        main_core.Dom.show(element);

	        var robotsTemplate = _classPrivateMethodGet(_this7, _getRobotsTemplate, _getRobotsTemplate2).call(_this7);

	        if (prefix === _this7.constantPrefix) {
	          robotsTemplate.updateConstant(property.Id, property);
	        } else {
	          robotsTemplate.updateParameter(property.Id, property);
	        }
	      };

	      main_core.Event.bind(inputElement, 'blur', applyNewDescription);
	      main_core.Event.bind(inputElement, 'keydown', function (event) {
	        if (event.keyCode === 13) {
	          main_core.Event.unbind(inputElement, 'blur', applyNewDescription);
	          applyNewDescription();
	        }
	      });
	    }
	  }, {
	    key: "setTemplateValues",
	    value: function setTemplateValues(template) {
	      var _this8 = this;

	      var formNode = this.baseNode ? this.baseNode.querySelector('[data-role="constant-list"]') : null;

	      if (!formNode) {
	        return;
	      }

	      var form = new FormData(formNode);
	      template.getConstants().forEach(function (constant) {
	        template.setConstantValue(constant.Id, form.get(_this8.constantPrefix + constant.Id));
	      });
	      template.getParameters().forEach(function (param) {
	        template.setParameterValue(param.Id, form.get(_this8.parameterPrefix + param.Id));
	      });
	    }
	  }]);
	  return ScriptEditComponent;
	}();

	var _getRobotsTemplate2 = function _getRobotsTemplate2() {
	  return this.automationDesigner.templateManager.templates[0];
	};

	var _activateSection2 = function _activateSection2(section) {
	  if (BX.UI.DropdownMenuItem.getItemByNode) {
	    var menuItem = BX.UI.DropdownMenuItem.getItemByNode(this.leftMenuNode.querySelector("[data-page=\"".concat(section, "\"]")));
	    this.menuActivateHandler(section);
	    menuItem && menuItem.setActiveHandler();
	  }

	  if (section === 'general') {
	    this.scriptNameNode.focus();
	  }

	  if (section !== 'configs') {
	    this.setTemplateValues(_classPrivateMethodGet(this, _getRobotsTemplate, _getRobotsTemplate2).call(this));
	  }
	};

	var _validateScriptName2 = function _validateScriptName2(name) {
	  if (!main_core.Type.isStringFilled(name)) {
	    ui_notification.UI.Notification.Center.notify({
	      content: main_core.Loc.getMessage('BIZPROC_SCRIPT_EDIT_VALIDATION_EMPTY_NAME')
	    });

	    _classPrivateMethodGet(this, _activateSection, _activateSection2).call(this, 'general');

	    return false;
	  }

	  return true;
	};

	var _validateConstants2 = function _validateConstants2(constants, usedConstants) {
	  var result = true;
	  constants.forEach(function (constant) {
	    if (usedConstants.has(constant.Id) && !main_core.Type.isStringFilled(constant.Default)) {
	      result = false;
	    }
	  });

	  if (!result) {
	    ui_notification.UI.Notification.Center.notify({
	      content: main_core.Loc.getMessage('BIZPROC_SCRIPT_EDIT_VALIDATION_EMPTY_CONFIGS')
	    });

	    _classPrivateMethodGet(this, _activateSection, _activateSection2).call(this, 'configs');
	  }

	  return result;
	};

	namespace.ScriptEditComponent = ScriptEditComponent;

}((this.window = this.window || {}),BX,BX.Event,BX));
//# sourceMappingURL=script.js.map
