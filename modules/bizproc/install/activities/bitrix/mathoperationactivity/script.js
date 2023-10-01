/* eslint-disable */
(function (exports,main_core,ui_entitySelector,bizproc_globals) {
	'use strict';

	var _templateObject, _templateObject2, _templateObject3, _templateObject4, _templateObject5, _templateObject6, _templateObject7, _templateObject8, _templateObject9, _templateObject10, _templateObject11, _templateObject12, _templateObject13, _templateObject14, _templateObject15, _templateObject16, _templateObject17, _templateObject18, _templateObject19, _templateObject20, _templateObject21, _templateObject22, _templateObject23, _templateObject24, _templateObject25, _templateObject26, _templateObject27;
	var namespace = main_core.Reflection.namespace('BX.Bizproc.Activity');
	var MathOperationActivity = /*#__PURE__*/function () {
	  function MathOperationActivity(options) {
	    babelHelpers.classCallCheck(this, MathOperationActivity);
	    if (main_core.Type.isPlainObject(options)) {
	      this.isRobot = options.isRobot;
	      this.signedDocumentType = options.signedDocumentType;
	      this.variables = options.variables;
	      this.constants = options.constants;
	      this.documentFields = options.documentFields;
	      this.operations = options.operations;
	      this.currentValues = options.currentValues;
	      this.visibilityMessages = options.visibilityMessages;
	      this.addRowTable = options.addRowTable;
	    }
	  }
	  babelHelpers.createClass(MathOperationActivity, [{
	    key: "init",
	    value: function init() {
	      this.initObjectNames();
	      this.initNodeAttributeNames();
	      this.initNodeIdNames();
	      this.initAvailableOptions();
	      this.availableTypes = ['int', 'integer', 'double'];
	      this.rowIndex = -1;
	      var addCondition = this.isRobot ? 'addConditionRobot' : 'addConditionDesigner';
	      if (Object.keys(this.currentValues).length <= 0) {
	        this[addCondition]('variable', ['parameter', '+', 'parameter']);
	      }
	      for (var variableId in this.currentValues) {
	        this[addCondition](variableId, this.currentValues[variableId]);
	      }
	    }
	  }, {
	    key: "initObjectNames",
	    value: function initObjectNames() {
	      this.gVarObjectName = 'GlobalVar';
	      this.gConstObjectName = 'GlobalConst';
	      this.documentObjectName = 'Document';
	      this.operationObjectName = 'Operation';
	      this.helperObjectName = 'Default';
	    }
	  }, {
	    key: "isGVariable",
	    value: function isGVariable(visibility) {
	      return visibility.startsWith(this.gVarObjectName);
	    }
	  }, {
	    key: "isGConstant",
	    value: function isGConstant(visibility) {
	      return visibility.startsWith(this.gConstObjectName);
	    }
	  }, {
	    key: "isDocument",
	    value: function isDocument(visibility) {
	      return visibility.startsWith(this.documentObjectName);
	    }
	  }, {
	    key: "initNodeAttributeNames",
	    value: function initNodeAttributeNames() {
	      this.indexAttributeName = 'bp_moa_index';
	    }
	  }, {
	    key: "initNodeIdNames",
	    value: function initNodeIdNames() {
	      this.variableIdName = 'bp_moa_variable_';
	      this.parameter1IdName = 'bp_moa_common1_';
	      this.operationIdName = 'bp_moa_operation_';
	      this.parameter2IdName = 'bp_moa_common2_';
	      this.resultIdName = 'bp_moa_results_';
	      this.operationMenuIdName = 'bp_moa_operations_menu_';
	    }
	  }, {
	    key: "initAvailableOptions",
	    value: function initAvailableOptions() {
	      this.options = this.getAvailableOptions();
	      this.optionsByGroup = this.getAvailableOptionsByGroup();
	    }
	  }, {
	    key: "getAvailableOptions",
	    value: function getAvailableOptions() {
	      var options = new Map();
	      this.fillOptions(this.variables, options);
	      this.fillOptions(this.constants, options);
	      this.fillOptions(this.documentFields, options);
	      var source = this.operations;
	      for (var i in source) {
	        options.set(source[i], {
	          title: source[i],
	          groupId: this.operationObjectName,
	          value: source[i]
	        });
	      }
	      options.set('variable', {
	        title: BX.message('BPMOA_CHOOSE_VARIABLE'),
	        groupId: this.helperObjectName,
	        value: ''
	      });
	      options.set('parameter', {
	        title: BX.message('BPMOA_CHOOSE_PARAMETER'),
	        groupId: this.helperObjectName,
	        value: ''
	      });
	      options.set('operation', {
	        title: '+',
	        groupId: this.helperObjectName,
	        value: '+'
	      });
	      return options;
	    }
	  }, {
	    key: "fillOptions",
	    value: function fillOptions(source, options) {
	      var optionId, optionsSource;
	      for (var groupName in source) {
	        optionsSource = source[groupName];
	        if (optionsSource['children']) {
	          optionsSource = optionsSource['children'];
	        }
	        for (var i in optionsSource) {
	          optionId = optionsSource[i]['id'];
	          options.set(optionId, this.createShortOptionProperty(optionId, optionsSource[i]));
	        }
	      }
	    }
	  }, {
	    key: "createShortOptionProperty",
	    value: function createShortOptionProperty(id, property) {
	      return {
	        title: property['customData']['title'],
	        groupId: property['customData']['groupId'],
	        value: id
	      };
	    }
	  }, {
	    key: "getAvailableOptionsByGroup",
	    value: function getAvailableOptionsByGroup() {
	      var options = new Map();
	      var items;
	      this.fillOptionsByGroupWithGlobals(this.variables, options, this.gVarObjectName);
	      this.fillOptionsByGroupWithGlobals(this.constants, options, this.gConstObjectName);
	      items = [];
	      for (var i in this.documentFields) {
	        items.push(this.documentFields[i]);
	      }
	      options.set(this.documentObjectName + ':' + this.documentObjectName, items);
	      options.set(this.operationObjectName, this.getOperationGroupOptions());
	      return options;
	    }
	  }, {
	    key: "fillOptionsByGroupWithGlobals",
	    value: function fillOptionsByGroupWithGlobals(source, options, topGroupName) {
	      var key;
	      for (var subGroupName in source) {
	        key = topGroupName + ':' + subGroupName;
	        options.set(key, source[subGroupName]);
	      }
	    }
	  }, {
	    key: "getOperationGroupOptions",
	    value: function getOperationGroupOptions() {
	      var items = [];
	      var source = this.operations;
	      var me = this;
	      for (var i in source) {
	        items.push({
	          text: source[i],
	          onclick: function onclick(event, item) {
	            var target = this.bindElement;
	            if (target) {
	              target.innerText = item.text;
	              me.resolveHiddenInput(target, item.text, document.getElementById(me.resultIdName + target.getAttribute(me.indexAttributeName)));
	              this.popupWindow.close();
	            }
	          }
	        });
	      }
	      return items;
	    }
	  }, {
	    key: "addConditionRobot",
	    value: function addConditionRobot(variableId, mathCondition) {
	      var properties = this.getPropertiesInfo(variableId, mathCondition);
	      var me = this;
	      var addRowTable = this.addRowTable;
	      this.rowIndex++;
	      var newRow = BX.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["<div class=\"bizproc-automation-popup-settings\"></div>"])));
	      var rowProperties = BX.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"bizproc-automation-popup-settings bizproc-automation-popup-settings-text\"></div>\n\t\t"])));
	      var rowInputs = BX.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["<div id=\"", "\"></div>"])), this.resultIdName + this.rowIndex);

	      /* region Variable Wrapper */

	      var variableSpan = BX.Tag.render(_templateObject4 || (_templateObject4 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<span class=\"bizproc-automation-popup-settings-link\" id=\"", "\"></span>\n\t\t"])), this.variableIdName + this.rowIndex);
	      variableSpan.setAttribute(this.indexAttributeName, this.rowIndex);
	      this.replaceTitleSelector(variableSpan, properties['variable'].title, rowInputs);
	      BX.bind(variableSpan, 'click', function (event) {
	        me.onFieldSelectClick(event, 'variable', me);
	      });
	      rowProperties.appendChild(variableSpan);

	      /* endregion */

	      rowProperties.appendChild(BX.Tag.render(_templateObject5 || (_templateObject5 = babelHelpers.taggedTemplateLiteral(["<span> = </span>"]))));
	      var parameter1Span = this.getParameterSpan(this.parameter1IdName + this.rowIndex, properties['parameter1'].title, rowInputs);
	      rowProperties.appendChild(parameter1Span);

	      /* region Operation Wrapper*/

	      var operationSpan = BX.Tag.render(_templateObject6 || (_templateObject6 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<span \n\t\t\t\tclass=\"bizproc-automation-popup-settings-link bizproc-automation-condition-joiner\" \n\t\t\t\tid=\"", "\"\n\t\t\t></span>\n\t\t"])), this.operationIdName + this.rowIndex);
	      operationSpan.setAttribute(this.indexAttributeName, this.rowIndex);
	      this.replaceTitleSelector(operationSpan, properties['operation'].title, rowInputs);
	      BX.bind(operationSpan, 'click', function (event) {
	        me.onOperationSelectClick(event, me);
	      });
	      rowProperties.appendChild(operationSpan);

	      /* endregion */

	      var parameter2Span = this.getParameterSpan(this.parameter2IdName + this.rowIndex, properties['parameter2'].title, rowInputs);
	      rowProperties.appendChild(parameter2Span);
	      newRow.appendChild(rowProperties);
	      newRow.appendChild(rowInputs);
	      addRowTable.appendChild(newRow);
	    }
	  }, {
	    key: "getPropertiesInfo",
	    value: function getPropertiesInfo(variableId, mathCondition) {
	      var properties = {
	        'variable': {
	          value: variableId,
	          defaultValue: 'variable'
	        },
	        'parameter1': {
	          value: mathCondition[0],
	          defaultValue: 'parameter'
	        },
	        'operation': {
	          value: mathCondition[1],
	          defaultValue: '+'
	        },
	        'parameter2': {
	          value: mathCondition[2],
	          defaultValue: 'parameter'
	        }
	      };
	      var infos = {};
	      for (var i in properties) {
	        infos[i] = this.getPropertyInfo(properties[i].value, properties[i].defaultValue);
	      }
	      return infos;
	    }
	  }, {
	    key: "getPropertyInfo",
	    value: function getPropertyInfo(item, defaultValue) {
	      if (this.options.get(item) === undefined) {
	        item = Number(item);
	        if (isNaN(item)) {
	          return {
	            title: defaultValue
	          };
	        }
	      }
	      return {
	        title: item
	      };
	    }
	  }, {
	    key: "getParameterSpan",
	    value: function getParameterSpan(id, title, rowInputs) {
	      var parameterSpan = BX.Tag.render(_templateObject7 || (_templateObject7 = babelHelpers.taggedTemplateLiteral(["<span class=\"bizproc-automation-popup-settings-link\" id=\"", "\"></span>"])), id);
	      parameterSpan.setAttribute(this.indexAttributeName, this.rowIndex);
	      this.replaceTitleSelector(parameterSpan, title, rowInputs);
	      var me = this;
	      BX.bind(parameterSpan, 'click', function (event) {
	        me.onFieldSelectClick(event, 'all', me);
	      });
	      return parameterSpan;
	    }
	  }, {
	    key: "onFieldSelectClick",
	    value: function onFieldSelectClick(event, type, me) {
	      var target = event.target;
	      var targetId = target.id;
	      var itemValue = document.getElementById(targetId + '_input').value;
	      var form = me.createFormForMenu(type, itemValue);
	      var popup = new BX.PopupWindow(targetId + '_popup', target, {
	        className: 'bizproc-automation-popup-set',
	        autoHide: true,
	        closeByEsc: true,
	        offsetTop: 5,
	        overlay: {
	          backgroundColor: 'transparent'
	        },
	        content: form,
	        buttons: [new BX.PopupWindowButton({
	          text: BX.message('BIZPROC_AUTOMATION_CMP_CHOOSE'),
	          className: "webform-button webform-button-create",
	          events: {
	            click: function click() {
	              var formInput = form.getElementsByTagName('input')[0];
	              var rowInput = document.getElementById(me.resultIdName + target.getAttribute(me.indexAttributeName));
	              me.replaceTitleSelector(target, formInput.value, rowInput);
	              popup.close();
	            }
	          }
	        }), new BX.PopupWindowButtonLink({
	          text: BX.message('BIZPROC_AUTOMATION_CMP_CANCEL'),
	          className: "popup-window-button-link",
	          events: {
	            click: function click() {
	              popup.close();
	            }
	          }
	        })],
	        events: {
	          onPopupClose: function onPopupClose() {
	            this.destroy();
	          }
	        }
	      });
	      popup.show();
	    }
	  }, {
	    key: "onOperationSelectClick",
	    value: function onOperationSelectClick(event, me) {
	      var _me$optionsByGroup$ge;
	      var target = event.target;
	      BX.Main.MenuManager.show(me.operationMenuIdName + Math.random(), target, (_me$optionsByGroup$ge = me.optionsByGroup.get(me.operationObjectName)) !== null && _me$optionsByGroup$ge !== void 0 ? _me$optionsByGroup$ge : [], {
	        autoHide: true,
	        className: 'bizproc-automation-inline-selector-menu',
	        overlay: {
	          backgroundColor: 'transparent'
	        },
	        minHeight: 50,
	        minWidth: 40,
	        events: {
	          onPopupClose: function onPopupClose() {
	            this.destroy();
	          }
	        }
	      });
	    }
	  }, {
	    key: "replaceTitleSelector",
	    value: function replaceTitleSelector(target, itemValue, rowInputs) {
	      var item = this.options.get(itemValue);
	      var inputValue;
	      if (target && item !== undefined) {
	        target.innerText = item['title'];
	        inputValue = item['value'];
	      } else if (target && !isNaN(Number(itemValue))) {
	        inputValue = Number(itemValue);
	        if (String(target.id).startsWith(this.variableIdName) && inputValue === 0) {
	          return;
	        }
	        target.innerText = inputValue;
	      } else {
	        return;
	      }
	      if (rowInputs) {
	        this.resolveHiddenInput(target, inputValue, rowInputs);
	      }
	    }
	  }, {
	    key: "resolveHiddenInput",
	    value: function resolveHiddenInput(source, value, target) {
	      var input = document.getElementById(source.id + '_input');
	      if (input) {
	        input.name = source.id;
	        input.value = value;
	        return;
	      }
	      var name = source.id;
	      var id = name + '_input';
	      target.appendChild(BX.Tag.render(_templateObject8 || (_templateObject8 = babelHelpers.taggedTemplateLiteral(["<input type=\"hidden\" id=\"", "\" name=\"", "\" value=\"", "\">"])), id, name, value));
	    }
	  }, {
	    key: "createFormForMenu",
	    value: function createFormForMenu(type, itemValue) {
	      var me = this;
	      var form = main_core.Tag.render(_templateObject9 || (_templateObject9 = babelHelpers.taggedTemplateLiteral(["<form class=\"bizproc-automation-popup-select-block\"></form>"])));
	      var fieldsListWrapper = main_core.Tag.render(_templateObject10 || (_templateObject10 = babelHelpers.taggedTemplateLiteral(["<div class=\"bizproc-automation-popup-settings\"></div>"])));
	      var labelFieldsList = main_core.Tag.render(_templateObject11 || (_templateObject11 = babelHelpers.taggedTemplateLiteral(["<div class=\"bizproc-automation-robot-settings-title\"></div>"])));
	      labelFieldsList.innerText = BX.message('BPMOA_LIST_OF_VALUES');
	      var formInput = main_core.Tag.render(_templateObject12 || (_templateObject12 = babelHelpers.taggedTemplateLiteral(["<input class=\"bizproc-automation-popup-input\" type=\"hidden\" style=\"width: 280px;\">"])));
	      var fieldsSelectNode = main_core.Tag.render(_templateObject13 || (_templateObject13 = babelHelpers.taggedTemplateLiteral(["<div class=\"bizproc-automation-popup-settings-dropdown\" readonly=\"readonly\"></div>"])));
	      BX.bind(fieldsSelectNode, 'click', function () {
	        var _me$optionsByGroup$ge2;
	        var items = (_me$optionsByGroup$ge2 = me.optionsByGroup.get(visibilitySelect.value)) !== null && _me$optionsByGroup$ge2 !== void 0 ? _me$optionsByGroup$ge2 : [];
	        var visibilityInfo = me.getVisibilityInfoForDialog(visibilitySelect.value);
	        var dialogOptions = me.getDialogOptions(items, visibilityInfo);
	        dialogOptions['targetNode'] = this;
	        dialogOptions['events'] = {
	          'Item:onBeforeSelect': function ItemOnBeforeSelect(event) {
	            var item = event.data.item;
	            fieldsSelectNode.innerText = item.customData.get('title');
	            formInput.value = item.id;
	          },
	          onHide: function onHide(event) {
	            event.target.destroy();
	          },
	          'Search:onItemCreateAsync': function SearchOnItemCreateAsync(event) {
	            return new Promise(function (resolve) {
	              var query = event.getData().searchQuery.query;
	              var dialog = event.getTarget();
	              me.onCreateGlobalsClick(dialog, visibilityInfo, query, me, resolve);
	            });
	          }
	        };
	        var dialog = new ui_entitySelector.Dialog(dialogOptions);
	        if (items.length <= 0) {
	          dialog.setFooter(me.getFooter(visibilityInfo, dialog));
	        }
	        dialog.show();
	      });
	      var visibilityWrapper = main_core.Tag.render(_templateObject14 || (_templateObject14 = babelHelpers.taggedTemplateLiteral(["<div class=\"bizproc-automation-popup-settings\"></div>"])));
	      var visibilitySelect = main_core.Tag.render(_templateObject15 || (_templateObject15 = babelHelpers.taggedTemplateLiteral(["<select class=\"bizproc-automation-popup-settings-dropdown\"></select>"])));
	      BX.bind(visibilitySelect, 'change', function () {
	        me.changeSelectForField(this.value, fieldsSelectNode, labelFieldsList, formInput);
	      });
	      var options = this.getVisibilityNamesForSelect(type);
	      for (var groupId in options) {
	        var optionNode = main_core.Tag.render(_templateObject16 || (_templateObject16 = babelHelpers.taggedTemplateLiteral(["<option value=\"", "\"></option>"])), BX.util.htmlspecialchars(groupId));
	        optionNode.innerText = options[groupId];
	        visibilitySelect.appendChild(optionNode);
	      }
	      var item = this.options.get(itemValue);
	      visibilitySelect.value = item ? item['groupId'] : this.helperObjectName + ':number';
	      if (visibilitySelect.selectedIndex === -1) {
	        visibilitySelect.selectedIndex = 0;
	      }
	      this.changeSelectForField(visibilitySelect.value, fieldsSelectNode, labelFieldsList, formInput);
	      if (item && item['groupId'] !== this.helperObjectName) {
	        fieldsSelectNode.innerText = item['title'];
	        formInput.value = itemValue;
	      } else {
	        fieldsSelectNode.innerText = BX.message('BPMOA_EMPTY');
	        formInput.value = itemValue;
	      }
	      visibilityWrapper.appendChild(main_core.Tag.render(_templateObject17 || (_templateObject17 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"bizproc-automation-robot-settings-title\">\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"])), BX.util.htmlspecialchars(BX.message('BPMOA_TYPE_OF_PARAMETER'))));
	      visibilityWrapper.appendChild(visibilitySelect);
	      fieldsListWrapper.appendChild(labelFieldsList);
	      fieldsListWrapper.appendChild(fieldsSelectNode);
	      fieldsListWrapper.append(formInput);
	      form.appendChild(visibilityWrapper);
	      form.appendChild(fieldsListWrapper);
	      return form;
	    }
	  }, {
	    key: "getVisibilityInfoForDialog",
	    value: function getVisibilityInfoForDialog(visibility) {
	      var recentStubOptions = {};
	      var searchStubOptions = {};
	      var searchFooterOptions = {};
	      var mode = '';
	      var objectName = '';
	      if (this.isGVariable(visibility)) {
	        recentStubOptions = {
	          title: BX.message('BPMOA_GVARIABLE_NO_EXIST'),
	          subtitle: BX.message('BPMOA_CREATE_GVARIABLE_QUESTION'),
	          arrow: true
	        };
	        searchStubOptions = {
	          title: BX.message('BPMOA_GVARIABLE_NOT_FOUND'),
	          subtitle: BX.message('BPMOA_CREATE_GVARIABLE_QUESTION'),
	          arrow: true
	        };
	        searchFooterOptions = {
	          label: BX.message('BPMOA_CREATE_GVARIABLE')
	        };
	        mode = bizproc_globals.Globals.Manager.Instance.mode.variable;
	        objectName = this.gVarObjectName;
	      } else if (this.isGConstant(visibility)) {
	        recentStubOptions = {
	          title: BX.message('BPMOA_GCONSTANT_NO_EXIST'),
	          subtitle: BX.message('BPMOA_CREATE_GCONSTANT_QUESTION'),
	          arrow: true
	        };
	        searchStubOptions = {
	          title: BX.message('BPMOA_GCONSTANT_NOT_FOUND'),
	          subtitle: BX.message('BPMOA_CREATE_GCONSTANT_QUESTION'),
	          arrow: true
	        };
	        searchFooterOptions = {
	          label: BX.message('BPMOA_CREATE_GCONSTANT')
	        };
	        mode = bizproc_globals.Globals.Manager.Instance.mode.constant;
	        objectName = this.gConstObjectName;
	      } else if (this.isDocument(visibility)) {
	        objectName = this.documentObjectName;
	      }
	      return {
	        recentStubOptions: recentStubOptions,
	        searchStubOptions: searchStubOptions,
	        searchFooterOptions: searchFooterOptions,
	        mode: mode,
	        objectName: objectName,
	        visibility: visibility
	      };
	    }
	  }, {
	    key: "getDialogOptions",
	    value: function getDialogOptions(items, visibilityInfo) {
	      var options = {
	        width: 480,
	        height: 300,
	        multiple: false,
	        dropdownMode: true,
	        enableSearch: true,
	        showAvatars: false,
	        compactView: true,
	        items: items,
	        tagSelectorOptions: {
	          textBoxWidth: 400
	        }
	      };
	      var extraOptions = {
	        recentTabOptions: {
	          stub: true,
	          icon: '',
	          stubOptions: visibilityInfo.recentStubOptions
	        },
	        searchTabOptions: {
	          stub: true,
	          stubOptions: visibilityInfo.searchStubOptions
	        },
	        searchOptions: {
	          allowCreateItem: true,
	          footerOptions: visibilityInfo.searchFooterOptions
	        }
	      };
	      if (visibilityInfo.objectName === this.gVarObjectName || visibilityInfo.objectName === this.gConstObjectName) {
	        return Object.assign(options, extraOptions);
	      }
	      return options;
	    }
	  }, {
	    key: "getFooter",
	    value: function getFooter(visibilityInfo, dialog) {
	      var me = this;
	      var footer = main_core.Tag.render(_templateObject18 || (_templateObject18 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<span class=\"ui-selector-footer-link ui-selector-footer-link-add\" style=\"border: none\">\n\t\t\t\t", "\n\t\t\t</span>\n\t\t"])), BX.util.htmlspecialchars(visibilityInfo.searchFooterOptions.label));
	      BX.bind(footer, 'click', function () {
	        me.onCreateGlobalsClick(dialog, visibilityInfo, '', me);
	      });
	      return footer;
	    }
	  }, {
	    key: "onCreateGlobalsClick",
	    value: function onCreateGlobalsClick(dialog, visibilityInfo, query, me, resolve) {
	      var visibility = visibilityInfo.visibility;
	      var additionalContext = {
	        visibility: visibility.slice(visibility.indexOf(':') + 1),
	        availableTypes: me.availableTypes
	      };
	      bizproc_globals.Globals.Manager.Instance.createGlobals(visibilityInfo.mode, me.signedDocumentType, query, additionalContext).then(function (slider) {
	        var context = {
	          'objectName': visibilityInfo.objectName,
	          'visibility': visibilityInfo.visibility
	        };
	        me.onAfterCreateGlobals(dialog, slider, context);
	        if (resolve) {
	          resolve();
	        }
	      });
	    }
	  }, {
	    key: "onAfterCreateGlobals",
	    value: function onAfterCreateGlobals(dialog, slider, context) {
	      var _this$optionsByGroup$;
	      var info = slider.getData().entries();
	      var keys = Object.keys(info);
	      if (keys.length <= 0) {
	        return;
	      }
	      var id = keys[0];
	      var property = info[keys[0]];
	      if (!this.availableTypes.includes(property['Type'])) {
	        return;
	      }
	      var item = {
	        entityId: 'bp',
	        tabs: 'recents',
	        title: property['Name'],
	        id: '{=' + context.objectName + ':' + id + '}',
	        customData: {
	          groupId: context.objectName + ':' + property['Visibility'],
	          property: property,
	          title: property['Name']
	        }
	      };
	      if (item.customData.groupId === context.visibility) {
	        dialog.setFooter(null);
	        dialog.addItem(item);
	      }
	      this.options.set(item.id, this.createShortOptionProperty(item.id, item));
	      var groupItems = (_this$optionsByGroup$ = this.optionsByGroup.get(item.customData.groupId)) !== null && _this$optionsByGroup$ !== void 0 ? _this$optionsByGroup$ : [];
	      groupItems.push(item);
	      this.optionsByGroup.set(item.customData.groupId, groupItems);
	    }
	  }, {
	    key: "changeSelectForField",
	    value: function changeSelectForField(value, target, label, input) {
	      if (value !== this.helperObjectName + ':number') {
	        target.style.display = '';
	        label.innerText = BX.message('BPMOA_LIST_OF_VALUES');
	        target.innerText = BX.message('BPMOA_EMPTY');
	        input.type = 'hidden';
	        input.value = '';
	        return;
	      }
	      label.innerText = BX.message('BPMOA_INPUT_NUMBER');
	      target.style.display = 'none';
	      input.type = 'text';
	      input.value = '0';
	    }
	  }, {
	    key: "getVisibilityNamesForSelect",
	    value: function getVisibilityNamesForSelect(type) {
	      var list = {};
	      var numberMessages = {};
	      numberMessages[this.helperObjectName] = {
	        'number': BX.message('BPMOA_NUMBER')
	      };
	      var source = Object.assign({}, this.visibilityMessages, numberMessages);
	      for (var topGroupName in source) {
	        if (type === 'variable' && topGroupName !== this.gVarObjectName) {
	          continue;
	        }
	        for (var subGroupName in source[topGroupName]) {
	          list[topGroupName + ':' + subGroupName] = source[topGroupName][subGroupName];
	        }
	      }
	      return list;
	    }
	  }, {
	    key: "addConditionDesigner",
	    value: function addConditionDesigner(variableId, mathCondition) {
	      var addRowTable = this.addRowTable;
	      this.rowIndex++;
	      var newRow;
	      var cell, select;
	      if (!mathCondition) {
	        mathCondition = [NaN, null, NaN];
	      }
	      newRow = addRowTable.insertRow(-1);

	      /* region Variable Wrapper */

	      cell = newRow.insertCell(-1);
	      cell.style.minWidth = '50px';
	      select = BX.Tag.render(_templateObject19 || (_templateObject19 = babelHelpers.taggedTemplateLiteral(["<select name=\"", "\" style=\"width: 100%;\"></select>"])), this.variableIdName + this.rowIndex);
	      this.appendChildToSelectDesigner(select, 'variable');
	      select.value = variableId;
	      if (select.selectedIndex === -1) {
	        select.selectedIndex = 0;
	      }
	      cell.appendChild(select);

	      /* endregion*/

	      cell = newRow.insertCell(-1);
	      cell.innerText = '=';
	      this.appendParameterSelectDesigner(newRow, this.parameter1IdName + this.rowIndex, mathCondition[0]);

	      /* region Operation Wrapper */

	      cell = newRow.insertCell(-1);
	      cell.style.minWidth = '45px';
	      select = BX.Tag.render(_templateObject20 || (_templateObject20 = babelHelpers.taggedTemplateLiteral(["<select name=\"", "\" style=\"width: 100%\"></select>"])), this.operationIdName + this.rowIndex);
	      for (var i in this.operations) {
	        select.appendChild(BX.Tag.render(_templateObject21 || (_templateObject21 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<option value=\"", "\">\n\t\t\t\t\t", "\n\t\t\t\t</option>\n\t\t\t"])), BX.util.htmlspecialchars(this.operations[i]), BX.util.htmlspecialchars(this.operations[i])));
	      }
	      select.value = mathCondition[1];
	      if (select.selectedIndex === -1) {
	        select.selectedIndex = 0;
	      }
	      cell.appendChild(select);

	      /* endregion */

	      this.appendParameterSelectDesigner(newRow, this.parameter2IdName + this.rowIndex, mathCondition[2]);
	    }
	  }, {
	    key: "appendChildToSelectDesigner",
	    value: function appendChildToSelectDesigner(select, type) {
	      for (var objectName in this.visibilityMessages) {
	        if (type === 'variable' && objectName !== this.gVarObjectName) {
	          continue;
	        }
	        var objectVisibilityMessages = this.visibilityMessages[objectName];
	        for (var visibility in objectVisibilityMessages) {
	          var optgroupLabel = objectVisibilityMessages[visibility];
	          var optgroup = BX.Tag.render(_templateObject22 || (_templateObject22 = babelHelpers.taggedTemplateLiteral(["<optgroup label=\"", "\"></optgroup>"])), BX.util.htmlspecialchars(optgroupLabel));
	          var groupOptions = this.optionsByGroup.get(objectName + ':' + visibility);
	          if (!groupOptions) {
	            continue;
	          }
	          var optionNode = void 0,
	            id = void 0,
	            title = void 0;
	          for (var i in groupOptions) {
	            var groupOption = groupOptions[i];
	            if (groupOption['children']) {
	              for (var j in groupOption['children']) {
	                id = groupOption['children'][j].id;
	                title = groupOption['children'][j].customData.title;
	                optionNode = BX.Tag.render(_templateObject23 || (_templateObject23 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t\t\t\t<option value=\"", "\">\n\t\t\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t\t\t</option>\n\t\t\t\t\t\t\t"])), BX.util.htmlspecialchars(id), BX.util.htmlspecialchars(title));
	                optgroup.appendChild(optionNode);
	              }
	            } else {
	              id = groupOption['id'];
	              title = groupOption['customData']['title'];
	              optionNode = BX.Tag.render(_templateObject24 || (_templateObject24 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t\t\t<option value=\"", "\">\n\t\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t\t</option>\n\t\t\t\t\t\t"])), BX.util.htmlspecialchars(id), BX.util.htmlspecialchars(title));
	              optgroup.appendChild(optionNode);
	            }
	          }
	          select.appendChild(optgroup);
	        }
	      }
	    }
	  }, {
	    key: "changeInputDesigner",
	    value: function changeInputDesigner(target, value) {
	      if (target.options[target.selectedIndex].value === '') {
	        target.after(BX.Tag.render(_templateObject25 || (_templateObject25 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<input \n\t\t\t\t\ttype=\"text\"\n\t\t\t\t\tname=\"", "\"\n\t\t\t\t\tstyle=\"width: 100px; height: 27px;\" \n\t\t\t\t\tvalue=\"", "\"\n\t\t\t\t>\n\t\t\t"])), target.name, isFinite(value) ? value : 0));
	      } else {
	        var input = document.getElementsByName(target.name)[1];
	        if (input) {
	          input.remove();
	        }
	      }
	    }
	  }, {
	    key: "appendParameterSelectDesigner",
	    value: function appendParameterSelectDesigner(newRow, id, value) {
	      var me = this;
	      var cell = newRow.insertCell(-1);
	      var select = BX.Tag.render(_templateObject26 || (_templateObject26 = babelHelpers.taggedTemplateLiteral(["<select name=\"", "\" style=\"width: 100%\"></select>"])), BX.util.htmlspecialchars(id));
	      BX.bind(select, 'change', function () {
	        me.changeInputDesigner(this, value);
	      });
	      select.appendChild(BX.Tag.render(_templateObject27 || (_templateObject27 = babelHelpers.taggedTemplateLiteral(["<option value=\"\">", "</option>"])), BX.util.htmlspecialchars(BX.message('BPMOA_NUMBER'))));
	      this.appendChildToSelectDesigner(select);
	      select.value = value;
	      if (select.selectedIndex === -1) {
	        select.selectedIndex = 0;
	      }
	      cell.appendChild(select);
	      this.changeInputDesigner(select, value);
	    }
	  }]);
	  return MathOperationActivity;
	}();
	namespace.MathOperationActivity = MathOperationActivity;

}((this.window = this.window || {}),BX,BX.UI.EntitySelector,BX.Bizproc));
//# sourceMappingURL=script.js.map
