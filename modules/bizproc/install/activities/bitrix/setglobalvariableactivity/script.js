(function (exports,main_core,bizproc_globals,ui_entitySelector) {
	'use strict';

	var _templateObject, _templateObject2, _templateObject3, _templateObject4, _templateObject5, _templateObject6, _templateObject7, _templateObject8, _templateObject9, _templateObject10, _templateObject11, _templateObject12, _templateObject13, _templateObject14, _templateObject15, _templateObject16, _templateObject17, _templateObject18, _templateObject19, _templateObject20, _templateObject21, _templateObject22, _templateObject23, _templateObject24, _templateObject25, _templateObject26, _templateObject27, _templateObject28, _templateObject29, _templateObject30, _templateObject31;
	var namespace = main_core.Reflection.namespace('BX.Bizproc.Activity');

	var SetGlobalVariableActivity = /*#__PURE__*/function () {
	  function SetGlobalVariableActivity(options) {
	    babelHelpers.classCallCheck(this, SetGlobalVariableActivity);

	    if (main_core.Type.isPlainObject(options)) {
	      var _options$constants, _options$documentFiel;

	      this.isRobot = options.isRobot;
	      this.documentType = options.documentType;
	      this.signedDocumentType = options.signedDocumentType;
	      this.variables = options.variables;
	      this.constants = (_options$constants = options.constants) !== null && _options$constants !== void 0 ? _options$constants : {};
	      this.documentFields = (_options$documentFiel = options.documentFields) !== null && _options$documentFiel !== void 0 ? _options$documentFiel : {};
	      this.currentValues = options.currentValues;
	      this.visibilityMessages = options.visibilityMessages;
	      this.formName = options.formName;
	      this.addRowTable = options.addRowTable;
	    }
	  }

	  babelHelpers.createClass(SetGlobalVariableActivity, [{
	    key: "init",
	    value: function init() {
	      this.initObjectNames();
	      this.initNodeIdNames();
	      this.initNodeAttributeNames();
	      this.initAvailableOptions();
	      this.rowIndex = -1;
	      this.numberOfTypes = 9;
	      var addAssignmentExpression = this.isRobot ? 'addAssignmentExpressionRobot' : 'addAssignmentExpressionDesigner';

	      if (Object.keys(this.currentValues).length <= 0) {
	        this[addAssignmentExpression]();
	      }

	      for (var variableExpression in this.currentValues) {
	        this[addAssignmentExpression](variableExpression, this.currentValues[variableExpression]);
	      }

	      if (this.isRobot) ; else {
	        this.addExpressionButtonDesigner();
	      }
	    }
	  }, {
	    key: "initObjectNames",
	    value: function initObjectNames() {
	      this.gVarObjectName = 'GlobalVar';
	      this.gConstObjectName = 'GlobalConst';
	      this.documentObjectName = 'Document';
	      this.helperObjectName = 'Default';
	    }
	  }, {
	    key: "isGVariableVisibility",
	    value: function isGVariableVisibility(visibility) {
	      return visibility.startsWith(this.gVarObjectName);
	    }
	  }, {
	    key: "isGConstantVisibility",
	    value: function isGConstantVisibility(visibility) {
	      return visibility.startsWith(this.gConstObjectName);
	    }
	  }, {
	    key: "isDocumentVisibility",
	    value: function isDocumentVisibility(visibility) {
	      return visibility.startsWith(this.documentObjectName);
	    }
	  }, {
	    key: "isHelperVisibility",
	    value: function isHelperVisibility(visibility) {
	      return visibility.startsWith(this.helperObjectName);
	    }
	  }, {
	    key: "initNodeIdNames",
	    value: function initNodeIdNames() {
	      this.addRowTableNodeId = 'bp_sgva_addrow_table';
	      this.addButtonNodeId = 'bp_sgva_add_button';
	      this.hiddenInputsNodeId = 'bp_sgva_results_';
	      this.variableRole = 'bp_sgva_variable_';
	      this.parameterRole = 'bp_sgva_value_';
	    }
	  }, {
	    key: "initNodeAttributeNames",
	    value: function initNodeAttributeNames() {
	      this.indexAttributeName = 'bp_sgva_index';
	      this.inputIndexAttributeName = 'bp_sgva_input_index';
	    }
	  }, {
	    key: "initAvailableOptions",
	    value: function initAvailableOptions() {
	      this.availableOptions = this.getAvailableOptions();
	      this.availableOptionsByGroupId = this.getAvailableOptionsByGroup();
	    }
	  }, {
	    key: "getAvailableOptions",
	    value: function getAvailableOptions() {
	      var options = new Map();
	      this.fillOptions(this.variables, options);
	      this.fillOptions(this.constants, options);
	      this.fillOptions(this.documentFields, options);
	      options.set('variable', {
	        id: '',
	        title: BX.message('BPSGVA_VARIABLE'),
	        customData: {
	          property: {
	            Type: 'string',
	            Multiple: false
	          },
	          groupId: this.helperObjectName,
	          title: BX.message('BPSGVA_VARIABLE')
	        }
	      });
	      options.set('parameter', {
	        id: '',
	        title: BX.message('BPSGVA_PARAMETER'),
	        customData: {
	          property: {
	            Type: 'string',
	            Multiple: false
	          },
	          groupId: this.helperObjectName,
	          title: BX.message('BPSGVA_PARAMETER')
	        }
	      });
	      options.set('clear', {
	        id: '',
	        title: BX.message('BPSGVA_CLEAR'),
	        customData: {
	          property: {
	            Type: 'string',
	            Multiple: false
	          },
	          groupId: this.helperObjectName,
	          title: BX.message('BPSGVA_CLEAR')
	        }
	      });
	      return options;
	    }
	  }, {
	    key: "fillOptions",
	    value: function fillOptions(source, options) {
	      var optionId, optionProperty, optionsSource;

	      for (var groupName in source) {
	        optionsSource = source[groupName];

	        if (optionsSource['children']) {
	          optionsSource = optionsSource['children'];
	        }

	        for (var i in optionsSource) {
	          optionId = optionsSource[i]['id'];
	          optionProperty = optionsSource[i];
	          options.set(optionId, optionProperty);
	        }
	      }
	    }
	  }, {
	    key: "getAvailableOptionsByGroup",
	    value: function getAvailableOptionsByGroup() {
	      var options = new Map();
	      this.fillOptionsByGroupWithGlobals(this.variables, options, this.gVarObjectName);
	      this.fillOptionsByGroupWithGlobals(this.constants, options, this.gConstObjectName);
	      var items = [];

	      for (var i in this.documentFields) {
	        items.push(this.documentFields[i]);
	      }

	      options.set(this.documentObjectName + ':' + this.documentObjectName, items);
	      return options;
	    }
	  }, {
	    key: "fillOptionsByGroupWithGlobals",
	    value: function fillOptionsByGroupWithGlobals(source, options, topGroupName) {
	      for (var subGroupName in source) {
	        var key = topGroupName + ':' + subGroupName;
	        options.set(key, source[subGroupName]);
	      }
	    }
	  }, {
	    key: "addAssignmentExpressionRobot",
	    value: function addAssignmentExpressionRobot(variableId, values) {
	      if (main_core.Type.isString(values)) {
	        values = {
	          0: values
	        };
	      }

	      var incomingData = {
	        variable: variableId,
	        values: values
	      };
	      this.modifyIncomingDataRobot(incomingData);
	      var addRowTable = this.addRowTable;
	      this.rowIndex++;
	      var newRow = main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["<div class=\"bizproc-automation-popup-settings\"></div>"])));
	      var rowInputs = main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["<div id=\"", "\"></div>"])), this.hiddenInputsNodeId + this.rowIndex);
	      var dataRow = main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div \n\t\t\t\tclass=\"bizproc-automation-popup-settings bizproc-automation-popup-settings-text\" \n\t\t\t\tstyle=\"display: flex; align-items: flex-start\"\n\t\t\t></div>\n\t\t"])));
	      dataRow.appendChild(this.createVariableRowRobot(incomingData.variable, rowInputs));
	      var parameterRowWrapper = main_core.Tag.render(_templateObject4 || (_templateObject4 = babelHelpers.taggedTemplateLiteral(["<div class=\"bizproc-automation-popup-settings-title\"></div>"])));
	      parameterRowWrapper.setAttribute('data-role', this.parameterRole + this.rowIndex);

	      if (incomingData.values.length <= 0) {
	        var option = BX.clone(this.getOptionPropertiesRobot('clear'));
	        option['multiple'] = incomingData.variable.property.Multiple;
	        option['type'] = incomingData.variable.property.Type;
	        option['inputIndex'] = 0;
	        parameterRowWrapper.appendChild(this.createParameterRowRobot(this.rowIndex, option, rowInputs));
	      }

	      for (var i in incomingData.values) {
	        var _option = BX.clone(incomingData.values[i]);

	        _option['multiple'] = incomingData.variable.property.Multiple;
	        _option['type'] = incomingData.variable.property.Type;
	        _option['inputIndex'] = i;
	        parameterRowWrapper.appendChild(this.createParameterRowRobot(this.rowIndex, _option, rowInputs));
	      }

	      if (incomingData.variable.property.Multiple && incomingData.variable.property.Type !== 'user') {
	        parameterRowWrapper.appendChild(this.createAddParameterRowRobot(this.rowIndex, incomingData.values.length));
	      }

	      dataRow.appendChild(parameterRowWrapper);
	      newRow.appendChild(dataRow);
	      newRow.appendChild(rowInputs);
	      addRowTable.appendChild(newRow);
	    }
	  }, {
	    key: "modifyIncomingDataRobot",
	    value: function modifyIncomingDataRobot(incomingData) {
	      var option = this.getOptionPropertiesRobot(incomingData.variable);

	      if (incomingData.variable === undefined || option.groupId === this.helperObjectName + ':text') {
	        incomingData.variable = BX.clone(this.getOptionPropertiesRobot('variable'));
	        var valueOption = BX.clone(this.getOptionPropertiesRobot('parameter'));
	        incomingData.values = [{
	          id: valueOption.id,
	          title: valueOption.title
	        }];
	        return;
	      }

	      var valuesOptions = [];

	      switch (option.property.Type) {
	        case 'select':
	          valuesOptions = this.getIncomingValuesSelect(incomingData);
	          break;

	        case 'bool':
	          valuesOptions = this.getIncomingValuesBool(incomingData);
	          break;

	        default:
	          for (var i in incomingData.values) {
	            var _valueOption = this.getOptionPropertiesRobot(incomingData.values[i]);

	            if (incomingData.values[i] === '') {
	              _valueOption = this.getOptionPropertiesRobot('clear');
	            }

	            valuesOptions.push({
	              id: _valueOption.id,
	              title: _valueOption.title
	            });
	          }

	      }

	      incomingData.variable = BX.clone(option);
	      incomingData.values = valuesOptions;
	    }
	  }, {
	    key: "getOptionPropertiesRobot",
	    value: function getOptionPropertiesRobot(optionId) {
	      var option = this.availableOptions.get(optionId);

	      if (option === undefined) {
	        return this.getDefaultOptionProperties(optionId);
	      }

	      return this.getShortOptionProperties(option);
	    }
	  }, {
	    key: "getDefaultOptionProperties",
	    value: function getDefaultOptionProperties(optionId) {
	      return {
	        id: optionId,
	        property: {
	          Type: 'string',
	          Multiple: false
	        },
	        groupId: this.helperObjectName + ':text',
	        title: optionId
	      };
	    }
	  }, {
	    key: "getShortOptionProperties",
	    value: function getShortOptionProperties(option) {
	      return {
	        id: option.id,
	        property: option.customData.property,
	        groupId: option.customData.groupId,
	        title: option.customData.title
	      };
	    }
	  }, {
	    key: "getIncomingValuesSelect",
	    value: function getIncomingValuesSelect(incomingData) {
	      var option = this.getOptionPropertiesRobot(incomingData.variable);
	      var title,
	          valueOption,
	          valuesOptions = [],
	          isExpressionOption;

	      for (var i in incomingData.values) {
	        title = BX.message('BPSGVA_CLEAR');

	        if (incomingData.values[i] !== '') {
	          valueOption = this.getOptionPropertiesRobot(incomingData.values[i]);
	          isExpressionOption = true;
	          title = valueOption.title;
	        }

	        if (option.property.Options[incomingData.values[i]] !== undefined) {
	          isExpressionOption = false;
	          title = option.property.Options[incomingData.values[i]];
	        }

	        valuesOptions.push({
	          id: incomingData.values[i],
	          title: title,
	          isExpressionOption: isExpressionOption
	        });
	      }

	      return valuesOptions;
	    }
	  }, {
	    key: "getIncomingValuesBool",
	    value: function getIncomingValuesBool(incomingData) {
	      var title,
	          valueOption,
	          valuesOptions = [];

	      for (var i in incomingData.values) {
	        var isExpressionOption = false;

	        switch (incomingData.values[i]) {
	          case 'Y':
	            title = BX.message('BPSGVA_BOOL_YES');
	            break;

	          case 'N':
	            title = BX.message('BPSGVA_BOOL_NO');
	            break;

	          case '':
	            title = BX.message('BPSGVA_CLEAR');
	            break;

	          default:
	            valueOption = this.getOptionPropertiesRobot(incomingData.values[i]);
	            title = valueOption.title;
	            isExpressionOption = true;
	        }

	        valuesOptions.push({
	          id: incomingData.values[i],
	          title: title,
	          isExpressionOption: isExpressionOption
	        });
	      }

	      return valuesOptions;
	    }
	  }, {
	    key: "createVariableRowRobot",
	    value: function createVariableRowRobot(variableData, rowInputs) {
	      var div = main_core.Tag.render(_templateObject5 || (_templateObject5 = babelHelpers.taggedTemplateLiteral(["<div></div>"])));
	      var variableNode = main_core.Tag.render(_templateObject6 || (_templateObject6 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<span class=\"bizproc-automation-popup-settings-link setglobalvariableactivity-underline\"></span>\n\t\t"])));
	      variableNode.setAttribute('data-role', this.variableRole + this.rowIndex);
	      variableNode.setAttribute(this.indexAttributeName, String(this.rowIndex));
	      var data = this.getDataForTitleReplacement(variableData, variableNode.getAttribute('data-role'));
	      data.multiple = false;
	      data.type = 'string';
	      data.inputIndex = 0;

	      if (data.title === '') {
	        data.title = BX.message('BPSGVA_VARIABLE');
	      }

	      this.replaceTitleAndHiddenInputRobot(variableNode, data, rowInputs);
	      BX.bind(variableNode, 'click', BX.proxy(this.onVariableSelectClickRobot, this));
	      div.appendChild(variableNode);
	      return div;
	    }
	  }, {
	    key: "createParameterRowRobot",
	    value: function createParameterRowRobot(index, valueData, rowInputs) {
	      var _this = this;

	      var wrapper = main_core.Tag.render(_templateObject7 || (_templateObject7 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"bizproc-automation-popup-settings-title setglobalvariableactivity-parameter-wrapper\"></div>\n\t\t"])));
	      var equal = main_core.Tag.render(_templateObject8 || (_templateObject8 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"bizproc-automation-popup-settings-title setglobalvariableactivity-symbol-equal\"> = </div>\n\t\t"])));
	      var div = main_core.Tag.render(_templateObject9 || (_templateObject9 = babelHelpers.taggedTemplateLiteral(["<div></div>"])));
	      var parameter = main_core.Tag.render(_templateObject10 || (_templateObject10 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<span class=\"bizproc-automation-popup-settings-link setglobalvariableactivity-underline\"></span>\n\t\t"])));
	      parameter.setAttribute('data-role', this.parameterRole + index);
	      parameter.setAttribute(this.indexAttributeName, String(index));
	      parameter.setAttribute(this.inputIndexAttributeName, String(valueData.inputIndex));
	      wrapper.appendChild(equal);
	      div.appendChild(parameter);
	      wrapper.appendChild(div);
	      var data = BX.clone(this.getDataForTitleReplacement(valueData, parameter.getAttribute('data-role')));
	      data.isExpressionOption = valueData.isExpressionOption;

	      if (data.title === '') {
	        data.title = BX.message('BPSGVA_CLEAR');
	      }

	      this.replaceTitleAndHiddenInputRobot(parameter, data, rowInputs);
	      BX.bind(parameter, 'click', BX.proxy(function (event) {
	        _this.onParameterSelectClickRobot(event, valueData.inputIndex);
	      }, this));
	      return wrapper;
	    }
	  }, {
	    key: "getDataForTitleReplacement",
	    value: function getDataForTitleReplacement(data, role) {
	      return {
	        inputValue: data.id,
	        title: data.title,
	        multiple: data.multiple,
	        type: data.type,
	        inputIndex: data.inputIndex,
	        property: data.property,
	        role: role
	      };
	    }
	  }, {
	    key: "replaceTitleAndHiddenInputRobot",
	    value: function replaceTitleAndHiddenInputRobot(target, data, rowInputs) {
	      var _this$getTitleForRepl;

	      target.innerText = (_this$getTitleForRepl = this.getTitleForReplacement(data)) !== null && _this$getTitleForRepl !== void 0 ? _this$getTitleForRepl : data.title;
	      this.replaceHiddenInputRobot(data, rowInputs);
	    }
	  }, {
	    key: "getTitleForReplacement",
	    value: function getTitleForReplacement(data) {
	      var type = data.type;
	      var title = data.title;
	      var value = data.inputValue;

	      if (type === 'bool') {
	        if (['Y', 'N'].includes(value)) {
	          return BX.Bizproc.FieldType.formatValuePrintable({
	            Type: type
	          }, value);
	        }

	        return null;
	      }

	      return BX.Bizproc.FieldType.formatValuePrintable({
	        Type: type
	      }, title);
	    }
	  }, {
	    key: "replaceHiddenInputRobot",
	    value: function replaceHiddenInputRobot(data, rowInputs) {
	      var inputValue = data.inputValue;
	      var role = data.role + '_input';
	      var input = document.querySelectorAll('[data-role="' + role + '"]'); // single input

	      if (input.length >= 1 && !data.multiple) {
	        input[0].name = data.isExpressionOption ? data.role + '_text' : data.role;
	        input[0].value = data.inputValue;
	        return;
	      } // multiple input


	      if (input.length >= 1 && data.multiple) {
	        var inputKeys = Object.keys(input);

	        for (var i in inputKeys) {
	          var inputIndex = input[inputKeys[i]].getAttribute(this.inputIndexAttributeName);

	          if (data.inputIndex === inputIndex) {
	            input[i].name = data.isExpressionOption ? data.role + '_text' : data.role + '[]';
	            input[i].value = data.inputValue;
	            return;
	          }
	        }
	      } // create input


	      input = main_core.Tag.render(_templateObject11 || (_templateObject11 = babelHelpers.taggedTemplateLiteral(["<input type=\"hidden\">"])));

	      if (data.isExpressionOption) {
	        input.name = data.role + '_text';
	      } else {
	        input.name = data.multiple ? data.role + '[]' : data.role;
	      }

	      input.value = inputValue;
	      input.setAttribute('data-role', role);
	      input.setAttribute(this.inputIndexAttributeName, String(data.inputIndex));
	      rowInputs.appendChild(input);
	    }
	  }, {
	    key: "onVariableSelectClickRobot",
	    value: function onVariableSelectClickRobot(event) {
	      var target = event.target;
	      var inputValue = this.getVariableInputValue(target.getAttribute('data-role'));
	      var index = target.getAttribute(this.indexAttributeName);
	      var form = this.createFormForMenuRobot('variable', inputValue, index);
	      var me = this;
	      var popup = new BX.PopupWindow(target.id + '_popup', target, {
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
	          className: 'webform-button webform-button-create',
	          events: {
	            click: function click() {
	              var _target$getAttribute;

	              var rowInputs = document.getElementById(me.hiddenInputsNodeId + index);
	              var input = me.findInputInFormRobot(form);
	              var data = me.getDataForTitleReplacement(me.getOptionPropertiesRobot(input.value), target.getAttribute('data-role'));
	              data.multiple = false;
	              data.type = 'string';
	              data.inputIndex = (_target$getAttribute = target.getAttribute(me.inputIndexAttributeName)) !== null && _target$getAttribute !== void 0 ? _target$getAttribute : '0';

	              if (data.title === '') {
	                data.title = BX.message('BPSGVA_VARIABLE');
	              }

	              me.replaceTitleAndHiddenInputRobot(target, data, rowInputs);
	              me.changeParameterExpressionRobot(index, data);
	              popup.close();
	            }
	          }
	        }), new BX.PopupWindowButtonLink({
	          text: BX.message('BIZPROC_AUTOMATION_CMP_CANCEL'),
	          className: 'popup-window-button-link',
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
	    key: "getVariableInputValue",
	    value: function getVariableInputValue(role) {
	      var inputRole = role + '_input';
	      var inputs = document.querySelectorAll('[data-role="' + inputRole + '"]');
	      return inputs.length >= 1 ? inputs['0'].value : '';
	    }
	  }, {
	    key: "createFormForMenuRobot",
	    value: function createFormForMenuRobot(typeMenu, inputValue, index) {
	      var me = this;
	      var form = main_core.Tag.render(_templateObject12 || (_templateObject12 = babelHelpers.taggedTemplateLiteral(["<form class=\"bizproc-automation-popup-select-block\"></form>"])));
	      var fieldsListWrapper = main_core.Tag.render(_templateObject13 || (_templateObject13 = babelHelpers.taggedTemplateLiteral(["<div class=\"bizproc-automation-popup-settings\"></div>"])));
	      var labelFieldsList = main_core.Tag.render(_templateObject14 || (_templateObject14 = babelHelpers.taggedTemplateLiteral(["<div class=\"bizproc-automation-robot-settings-title\"></div>"])));
	      labelFieldsList.innerText = BX.message('BPSGVA_LIST_OF_VALUES');
	      var formInputWrapper = this.createInputForMenuFormRobot(typeMenu, index, inputValue);
	      var formInput = this.findInputInFormRobot(formInputWrapper);
	      var filterType = typeMenu === 'variable' ? 'string' : this.getVariableOptionFromVariableInput(index).property.Type;
	      var fieldsSelectNode = main_core.Tag.render(_templateObject15 || (_templateObject15 = babelHelpers.taggedTemplateLiteral(["<div class=\"bizproc-automation-popup-settings-dropdown\" readonly=\"readonly\"></div>"])));
	      BX.bind(fieldsSelectNode, 'click', function () {
	        var _me$availableOptionsB;

	        var items = (_me$availableOptionsB = me.availableOptionsByGroupId.get(visibilitySelect.value)) !== null && _me$availableOptionsB !== void 0 ? _me$availableOptionsB : [];
	        var filterItems = me.filterItemsInStandardMenuRobot(filterType, items);
	        var visibilityInfo = me.getVisibilityInfoForDialog(visibilitySelect.value);
	        var dialogOptions = me.getDialogOptions(filterItems, visibilityInfo);
	        dialogOptions['targetNode'] = fieldsSelectNode;
	        dialogOptions['events'] = {
	          'Item:onBeforeSelect': function ItemOnBeforeSelect(event) {
	            var dialogItem = event.data.item;
	            fieldsSelectNode.innerText = dialogItem.customData.get('title');

	            if (!formInput) {
	              main_core.Dom.append(main_core.Tag.render(_templateObject16 || (_templateObject16 = babelHelpers.taggedTemplateLiteral(["<input type=\"hidden\" name=\"bp_sgva_field_input\" value=\"", "\">"])), main_core.Text.encode(dialogItem.id)), formInputWrapper);
	              return;
	            }

	            if (formInput.tagName !== 'SELECT') {
	              formInput.value = dialogItem.id;
	            } else {
	              me.resolveAdditionOptionInSelectRobot(formInput, dialogItem);
	            }
	          },
	          onHide: function onHide(event) {
	            event.target.destroy();
	          },
	          'Search:onItemCreateAsync': function SearchOnItemCreateAsync(event) {
	            return new Promise(function (resolve) {
	              var query = event.getData().searchQuery.query;
	              var dialog = event.getTarget();
	              var context = {
	                visibilityInfo: visibilityInfo,
	                index: index
	              };
	              me.onCreateGlobalsClick(dialog, context, query, me, resolve);
	            });
	          }
	        };
	        var dialog = new ui_entitySelector.Dialog(dialogOptions);

	        if (filterItems.length <= 0) {
	          dialog.setFooter(me.getFooter({
	            visibilityInfo: visibilityInfo,
	            index: index
	          }, dialog));
	        }

	        dialog.show();
	      });
	      var visibilityWrapper = main_core.Tag.render(_templateObject17 || (_templateObject17 = babelHelpers.taggedTemplateLiteral(["<div class=\"bizproc-automation-popup-settings\"></div>"])));
	      var visibilitySelect = main_core.Tag.render(_templateObject18 || (_templateObject18 = babelHelpers.taggedTemplateLiteral(["<select class=\"bizproc-automation-popup-settings-dropdown\"></select>"])));
	      BX.bind(visibilitySelect, 'change', BX.proxy(function () {
	        me.changeParameterSelectInFormRobot(visibilitySelect.value, fieldsSelectNode, labelFieldsList, formInputWrapper);
	      }, this));
	      var visibilityOptions = this.getVisibilityNamesForSelect(typeMenu);

	      for (var groupId in visibilityOptions) {
	        var optionNode = main_core.Tag.render(_templateObject19 || (_templateObject19 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<option value=\"", "\">\n\t\t\t\t\t", "\n\t\t\t\t</option>\n\t\t\t"])), BX.util.htmlspecialchars(groupId), BX.util.htmlspecialchars(visibilityOptions[groupId]));
	        visibilitySelect.appendChild(optionNode);
	      }

	      var option = this.getOptionPropertiesRobot(inputValue);

	      if (option.groupId === this.helperObjectName) {
	        option.groupId = this.helperObjectName + ':text';
	        option.id = inputValue;
	      }

	      visibilitySelect.value = this.getVisibilityRelativeToVariableType(option, filterType);

	      if (visibilitySelect.selectedIndex === -1) {
	        visibilitySelect.selectedIndex = 0;
	      }

	      this.changeParameterSelectInFormRobot(visibilitySelect.value, fieldsSelectNode, labelFieldsList, formInputWrapper);
	      fieldsSelectNode.innerText = option.title !== '' ? option.title : BX.message('BPSGVA_EMPTY');

	      if (visibilitySelect.value === this.helperObjectName + ':text' && option.groupId !== this.helperObjectName + ':text') {
	        if (formInput) {
	          formInput.value = this.convertFieldExpression(option);
	        }
	      } else {
	        if (formInput) {
	          formInput.value = option.id;
	        }
	      }

	      visibilityWrapper.appendChild(main_core.Tag.render(_templateObject20 || (_templateObject20 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"bizproc-automation-robot-settings-title\">\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"])), BX.util.htmlspecialchars(BX.message('BPSGVA_TYPE_OF_PARAMETER'))));
	      visibilityWrapper.appendChild(visibilitySelect);
	      fieldsListWrapper.appendChild(labelFieldsList);
	      fieldsListWrapper.appendChild(fieldsSelectNode);
	      fieldsListWrapper.append(formInputWrapper);
	      form.appendChild(visibilityWrapper);
	      form.appendChild(fieldsListWrapper);
	      return form;
	    }
	  }, {
	    key: "createInputForMenuFormRobot",
	    value: function createInputForMenuFormRobot(type, index, inputValue) {
	      if (type === 'variable') {
	        var _wrapper = main_core.Tag.render(_templateObject21 || (_templateObject21 = babelHelpers.taggedTemplateLiteral(["<div class=\"bizproc-automation-popup-select\"></div>"])));

	        var _input = main_core.Tag.render(_templateObject22 || (_templateObject22 = babelHelpers.taggedTemplateLiteral(["<input class=\"bizproc-automation-popup-input\" type=\"hidden\" style=\"width: 280px\">"])));

	        _wrapper.appendChild(_input);

	        return _wrapper;
	      }

	      var variableOption = this.getVariableOptionFromVariableInput(index);
	      var wrapper;

	      switch (variableOption.property.Type) {
	        case 'user':
	          wrapper = BX.Bizproc.FieldType.renderControl(this.documentType, variableOption.property, 'bp_sgva_field_input', inputValue);
	          break;

	        case 'select':
	        case 'bool':
	          wrapper = BX.Bizproc.FieldType.renderControl(this.documentType, {
	            Type: variableOption.property.Type,
	            Options: variableOption.property.Options
	          }, 'bp_sgva_field_input', inputValue);
	          break;

	        default:
	          wrapper = BX.Bizproc.FieldType.renderControl(this.documentType, {
	            Type: variableOption.property.Type
	          }, 'bp_sgva_field_input', variableOption.id);
	      }

	      wrapper.style.width = '280px';
	      var input = this.findInputInFormRobot(wrapper);

	      if (['bool', 'select'].includes(variableOption.property.Type)) {
	        if (input.value !== inputValue) {
	          var option = this.getOptionPropertiesRobot(inputValue);
	          this.resolveAdditionOptionInSelectRobot(input, option);
	        }
	      }

	      if (input) {
	        input.style.width = '100%';
	      }

	      return wrapper;
	    }
	  }, {
	    key: "getVariableOptionFromVariableInput",
	    value: function getVariableOptionFromVariableInput(index) {
	      var variableInput = document.querySelector('[data-role="' + this.variableRole + index + '_input"]');
	      var variableId = variableInput ? variableInput.value : '';
	      return this.getOptionPropertiesRobot(variableId);
	    }
	  }, {
	    key: "findInputInFormRobot",
	    value: function findInputInFormRobot(form) {
	      var inputs = form.getElementsByTagName('input');

	      if (inputs.length >= 1) {
	        return inputs[inputs.length - 1];
	      }

	      inputs = form.getElementsByTagName('textarea');

	      if (inputs.length >= 1) {
	        return inputs[inputs.length - 1];
	      }

	      inputs = form.getElementsByTagName('select');

	      if (inputs.length >= 1) {
	        return inputs[inputs.length - 1];
	      }
	    }
	  }, {
	    key: "resolveAdditionOptionInSelectRobot",
	    value: function resolveAdditionOptionInSelectRobot(input, option) {
	      var selectOptions = input.options;
	      var opt = selectOptions[selectOptions.length - 1];

	      if (opt.getAttribute('data-role') !== 'expression') {
	        opt = main_core.Tag.render(_templateObject23 || (_templateObject23 = babelHelpers.taggedTemplateLiteral(["<option></option>"])));
	        opt.setAttribute('data-role', 'expression');
	        input.appendChild(opt);
	      }

	      opt.value = option.id;

	      if (!option.customData) {
	        opt.text = option.title;
	      } else {
	        opt.text = option.customData.get('title');
	      }

	      opt.setAttribute('selected', 'selected');

	      if (!opt.selected) {
	        opt.selected = true;
	      }
	    }
	  }, {
	    key: "filterItemsInStandardMenuRobot",
	    value: function filterItemsInStandardMenuRobot(variableType, items) {
	      var filter = this.getFilterByVariableType(variableType);

	      if (filter.length === this.numberOfTypes) {
	        return items;
	      }

	      var filterItems = [];

	      for (var i in items) {
	        if (items[i].children) {
	          var filterChildrenItems = this.filterItemsInStandardMenuRobot(variableType, items[i].children);

	          if (filterChildrenItems.length >= 1) {
	            var menuItem = items[i];
	            menuItem.children = filterChildrenItems;
	            filterItems.push(menuItem);
	          }
	        } else {
	          if (filter.includes(items[i].customData.property.Type)) {
	            filterItems.push(items[i]);
	          }
	        }
	      }

	      return filterItems;
	    }
	  }, {
	    key: "getFilterByVariableType",
	    value: function getFilterByVariableType(type) {
	      switch (type) {
	        case 'double':
	          return ['int', 'double'];

	        case 'datetime':
	          return ['date', 'datetime'];

	        case 'date':
	        case 'int':
	        case 'user':
	          return [type];

	        default:
	          // this.numberOfTypes = 9
	          return ['string', 'text', 'select', 'bool', 'int', 'double', 'date', 'datetime', 'user'];
	      }
	    }
	  }, {
	    key: "getVisibilityInfoForDialog",
	    value: function getVisibilityInfoForDialog(visibility) {
	      var recentStubOptions = {};
	      var searchStubOptions = {};
	      var searchFooterOptions = {};
	      var mode = '';
	      var objectName = '';

	      if (this.isGVariableVisibility(visibility)) {
	        recentStubOptions = {
	          title: BX.message('BPSGVA_GVARIABLE_NO_EXIST'),
	          subtitle: BX.message('BPSGVA_CREATE_GVARIABLE_QUESTION'),
	          arrow: true
	        };
	        searchStubOptions = {
	          title: BX.message('BPSGVA_GVARIABLE_NOT_FOUND'),
	          subtitle: BX.message('BPSGVA_CREATE_GVARIABLE_QUESTION'),
	          arrow: true
	        };
	        searchFooterOptions = {
	          label: BX.message('BPSGVA_CREATE_GVARIABLE')
	        };
	        mode = bizproc_globals.Globals.Manager.Instance.mode.variable;
	        objectName = this.gVarObjectName;
	      } else if (this.isGConstantVisibility(visibility)) {
	        recentStubOptions = {
	          title: BX.message('BPSGVA_GCONSTANT_NO_EXIST'),
	          subtitle: BX.message('BPSGVA_CREATE_GCONSTANT_QUESTION'),
	          arrow: true
	        };
	        searchStubOptions = {
	          title: BX.message('BPSGVA_GCONSTANT_NOT_FOUND'),
	          subtitle: BX.message('BPSGVA_CREATE_GCONSTANT_QUESTION'),
	          arrow: true
	        };
	        searchFooterOptions = {
	          label: BX.message('BPSGVA_CREATE_GCONSTANT')
	        };
	        mode = bizproc_globals.Globals.Manager.Instance.mode.constant;
	        objectName = this.gConstObjectName;
	      } else if (this.isDocumentVisibility(visibility)) {
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
	    value: function getFooter(context, dialog) {
	      var me = this;
	      var footer = main_core.Tag.render(_templateObject24 || (_templateObject24 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<span class=\"ui-selector-footer-link ui-selector-footer-link-add\" style=\"border: none\">\n\t\t\t\t", "\n\t\t\t</span>\n\t\t"])), BX.util.htmlspecialchars(context.visibilityInfo.searchFooterOptions.label));
	      BX.bind(footer, 'click', function () {
	        me.onCreateGlobalsClick(dialog, context, '', me);
	      });
	      return footer;
	    }
	  }, {
	    key: "onCreateGlobalsClick",
	    value: function onCreateGlobalsClick(dialog, context, query, me, resolve) {
	      var variableType = me.getVariableOptionFromVariableInput(context.index).property.Type;
	      var optionAvailableTypes = me.getFilterByVariableType(variableType);
	      var visibility = context.visibilityInfo.visibility;
	      var additionalContext = {
	        visibility: visibility.slice(visibility.indexOf(':') + 1),
	        availableTypes: optionAvailableTypes
	      };
	      bizproc_globals.Globals.Manager.Instance.createGlobals(context.visibilityInfo.mode, me.signedDocumentType, query, additionalContext).then(function (slider) {
	        var newContext = {
	          'objectName': context.visibilityInfo.objectName,
	          'visibility': context.visibilityInfo.visibility,
	          'index': context.index
	        };
	        me.onAfterCreateGlobals(dialog, slider, newContext);

	        if (resolve) {
	          resolve();
	        }
	      });
	    }
	  }, {
	    key: "onAfterCreateGlobals",
	    value: function onAfterCreateGlobals(dialog, slider, context) {
	      var _this$availableOption;

	      var info = slider.getData().entries();
	      var keys = Object.keys(info);

	      if (keys.length <= 0) {
	        return;
	      }

	      var id = keys[0];
	      var property = BX.clone(info[keys[0]]);
	      property.Multiple = property.Multiple === 'Y';
	      var variableType = this.getVariableOptionFromVariableInput(context.index).property.Type;
	      var optionAvailableTypes = this.getFilterByVariableType(variableType);
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

	      if (item.customData.groupId === context.visibility && optionAvailableTypes.includes(item.customData.property.Type)) {
	        dialog.setFooter(null);
	        dialog.addItem(item);
	      }

	      this.availableOptions.set(item.id, item);
	      var groupItems = (_this$availableOption = this.availableOptionsByGroupId.get(item.customData.groupId)) !== null && _this$availableOption !== void 0 ? _this$availableOption : [];
	      groupItems.push(item);
	      this.availableOptionsByGroupId.set(item.customData.groupId, groupItems);
	    }
	  }, {
	    key: "changeParameterSelectInFormRobot",
	    value: function changeParameterSelectInFormRobot(visibility, target, label, inputWrapper) {
	      if (visibility !== this.helperObjectName + ':text') {
	        target.style.display = 'inline-block';
	        target.innerText = BX.message('BPSGVA_EMPTY');
	        label.innerText = BX.message('BPSGVA_LIST_OF_VALUES');
	        inputWrapper.style.display = 'none';
	      } else {
	        target.style.display = 'none';
	        label.innerText = BX.message('BPSGVA_INPUT_TEXT');
	        inputWrapper.style.display = '';
	      }

	      var input = this.findInputInFormRobot(inputWrapper);

	      if (input) {
	        input.value = '';
	      }
	    }
	  }, {
	    key: "getVisibilityNamesForSelect",
	    value: function getVisibilityNamesForSelect(type) {
	      var list = {};
	      var textMessages = {};
	      textMessages[this.helperObjectName] = {
	        'text': BX.message('BPSGVA_TEXT')
	      };
	      var source = Object.assign({}, this.visibilityMessages, textMessages);

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
	    key: "getVisibilityRelativeToVariableType",
	    value: function getVisibilityRelativeToVariableType(option, variableType) {
	      var optionAvailableTypes = this.getFilterByVariableType(variableType);

	      if (option.groupId === this.helperObjectName + ':text' || optionAvailableTypes.includes(option.property.Type)) {
	        return option.groupId;
	      }

	      return this.helperObjectName + ':text';
	    }
	  }, {
	    key: "changeParameterExpressionRobot",
	    value: function changeParameterExpressionRobot(index, variable) {
	      var parameterNode = document.querySelector('[data-role="' + this.parameterRole + index + '"]');
	      this.deleteOldValueRowsRobot(parameterNode);
	      var rowInputs = document.getElementById(this.hiddenInputsNodeId + index);
	      var option = BX.clone(this.getOptionPropertiesRobot('parameter'));
	      option['multiple'] = variable.property.Multiple;
	      option['inputIndex'] = '0';
	      parameterNode.appendChild(this.createParameterRowRobot(index, option, rowInputs));

	      if (variable.property.Multiple && variable.property.Type !== 'user') {
	        var inputIndex = variable.inputIndex !== '0' ? variable.inputIndex : '1';
	        parameterNode.appendChild(this.createAddParameterRowRobot(index, inputIndex));
	      }
	    }
	  }, {
	    key: "deleteOldValueRowsRobot",
	    value: function deleteOldValueRowsRobot(node) {
	      var role = node.getAttribute('data-role');
	      node.innerHTML = '';
	      var oldInputs = document.querySelectorAll('[data-role="' + role + '_input"]');

	      for (var i in Object.keys(oldInputs)) {
	        oldInputs[i].remove();
	      }
	    }
	  }, {
	    key: "createAddParameterRowRobot",
	    value: function createAddParameterRowRobot(index, inputIndex) {
	      var addWrapper = main_core.Tag.render(_templateObject25 || (_templateObject25 = babelHelpers.taggedTemplateLiteral(["<div class=\"bizproc-automation-popup-settings-title\" style=\"display:flex;\"></div>"])));
	      var addExpression = main_core.Tag.render(_templateObject26 || (_templateObject26 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"bizproc-type-control-clone-btn setglobalvariableactivity-dashed-grey setglobalvariableactivity-add-parameter\">\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"])), BX.util.htmlspecialchars(BX.message('BPSGVA_ADD_PARAMETER')));
	      addExpression.setAttribute(this.indexAttributeName, String(index));
	      addExpression.setAttribute(this.inputIndexAttributeName, String(inputIndex));
	      BX.bind(addExpression, 'click', BX.proxy(this.onAddParameterButtonClickRobot, this));
	      addWrapper.appendChild(addExpression);
	      return addWrapper;
	    }
	  }, {
	    key: "onAddParameterButtonClickRobot",
	    value: function onAddParameterButtonClickRobot(event) {
	      var index = event.target.getAttribute(this.indexAttributeName);
	      var rowInputs = document.getElementById(this.hiddenInputsNodeId + index);
	      var inputIndex = event.target.getAttribute(this.inputIndexAttributeName);
	      var option = BX.clone(this.getOptionPropertiesRobot('parameter'));
	      option['multiple'] = true;
	      option['inputIndex'] = inputIndex;
	      event.target.parentNode.before(this.createParameterRowRobot(index, option, rowInputs));
	      event.target.setAttribute(this.inputIndexAttributeName, Number(inputIndex) + 1);
	    }
	  }, {
	    key: "onParameterSelectClickRobot",
	    value: function onParameterSelectClickRobot(event, inputIndex) {
	      var target = event.target;
	      var inputValue = this.getParameterInputValue(target.getAttribute('data-role') + '_input', inputIndex);
	      var index = target.getAttribute(this.indexAttributeName);
	      var form = this.createFormForMenuRobot('all', inputValue, index);
	      var me = this;
	      var popup = new BX.PopupWindow(target.id + '_popup', target, {
	        className: 'bizproc-automation-popup-set',
	        closeByEsc: true,
	        autoHide: true,
	        offsetTop: 5,
	        overlay: {
	          backgroundColor: 'transparent'
	        },
	        content: form,
	        buttons: [new BX.PopupWindowButton({
	          text: BX.message('BIZPROC_AUTOMATION_CMP_CHOOSE'),
	          className: 'webform-button webform-button-create',
	          events: {
	            click: function click() {
	              var _target$getAttribute2;

	              var rowInputs = document.getElementById(me.hiddenInputsNodeId + index);
	              var variableOption = me.getVariableOptionFromVariableInput(index);
	              var input = me.findInputInFormRobot(form);
	              var data;

	              if (input.tagName === 'SELECT') {
	                var id = input.selectedOptions[0].value;
	                var title = id !== '' ? input.selectedOptions[0].text : BX.message('BPSGVA_CLEAR');
	                data = me.getDataForTitleReplacement({
	                  id: id,
	                  title: title
	                }, target.getAttribute('data-role'));
	                data.isExpressionOption = input.selectedOptions[0].getAttribute('data-role') === 'expression';
	              } else {
	                var option = BX.clone(me.getOptionPropertiesRobot(input.value));

	                if (option.groupId === me.helperObjectName) {
	                  option.id = input.value;
	                  option.title = input.value;
	                }

	                data = me.getDataForTitleReplacement(me.getOptionPropertiesRobot(input.value), target.getAttribute('data-role'));
	              }

	              data.inputIndex = (_target$getAttribute2 = target.getAttribute(me.inputIndexAttributeName)) !== null && _target$getAttribute2 !== void 0 ? _target$getAttribute2 : '0';
	              data.multiple = variableOption.property.Multiple;
	              data.type = variableOption.property.Type;

	              if (data.title === '') {
	                data.title = BX.message('BPSGVA_CLEAR');
	              }

	              me.replaceTitleAndHiddenInputRobot(target, data, rowInputs);
	              popup.close();
	            }
	          }
	        }), new BX.PopupWindowButtonLink({
	          text: BX.message('BIZPROC_AUTOMATION_CMP_CANCEL'),
	          className: 'popup-window-button-link',
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
	    key: "getParameterInputValue",
	    value: function getParameterInputValue(role, index) {
	      var inputs = document.querySelectorAll('[data-role="' + role + '"]', index);
	      var keys = Object.keys(inputs);

	      for (var i in keys) {
	        if (String(inputs[keys[i]].getAttribute(this.inputIndexAttributeName)) === String(index)) {
	          return inputs[keys[i]].value;
	        }
	      }

	      return '';
	    }
	  }, {
	    key: "addExpressionButtonRobot",
	    value: function addExpressionButtonRobot() {
	      var buttonAdd = document.getElementById(this.addButtonNodeId);
	      buttonAdd.innerText = BX.message('BPSGVA_ADD_VARIABLE');
	      BX.bind(buttonAdd, 'click', BX.proxy(function () {
	        this.addAssignmentExpressionRobot();
	      }, this));
	    }
	  }, {
	    key: "addExpressionButtonDesigner",
	    value: function addExpressionButtonDesigner() {
	      var _this2 = this;

	      var button = main_core.Tag.render(_templateObject27 || (_templateObject27 = babelHelpers.taggedTemplateLiteral(["<a href='#'>", "</a>"])), main_core.Loc.getMessage('BPSGVA_PD_ADD'));
	      main_core.Event.bind(button, 'click', function (event) {
	        _this2.addAssignmentExpressionDesigner();

	        event.preventDefault();
	      });
	      main_core.Dom.insertAfter(button, this.addRowTable);
	    }
	  }, {
	    key: "convertFieldExpression",
	    value: function convertFieldExpression(option) {
	      if (this.isDocumentVisibility(option.groupId)) {
	        return '{{' + option.property.Name + '}}';
	      }

	      if (this.isGVariableVisibility(option.groupId)) {
	        var messages = this.visibilityMessages[this.gVarObjectName];
	        var visibility = option.property.Visibility;
	        var name = option.property.Name;
	        return '{{' + messages[visibility] + ': ' + name + '}}';
	      }

	      if (this.isGConstantVisibility(option.groupId)) {
	        var _messages = this.visibilityMessages[this.gConstObjectName];
	        var _visibility = option.property.Visibility;
	        var _name = option.property.Name;
	        return '{{' + _messages[_visibility] + ': ' + _name + '}}';
	      }

	      return option.id;
	    }
	  }, {
	    key: "addAssignmentExpressionDesigner",
	    value: function addAssignmentExpressionDesigner(variable, value) {
	      var addRowTable = this.addRowTable;
	      this.rowIndex++;
	      var newRow = addRowTable.insertRow(-1);
	      newRow.id = 'delete_row_' + this.rowIndex;
	      var cellSelect = newRow.insertCell(-1);
	      var newSelect = main_core.Tag.render(_templateObject28 || (_templateObject28 = babelHelpers.taggedTemplateLiteral(["<select name=\"", "\"></select>"])), this.variableRole + this.rowIndex);
	      newSelect.setAttribute(this.indexAttributeName, this.rowIndex);
	      var me = this;

	      newSelect.onchange = function () {
	        me.changeFieldTypeDesigner(this.getAttribute(me.indexAttributeName), this.options[this.selectedIndex].value, null);
	      };

	      var objectVisibilityMessages = this.visibilityMessages[this.gVarObjectName];

	      for (var visibility in objectVisibilityMessages) {
	        var optgroupLabel = objectVisibilityMessages[visibility];
	        var optgroup = main_core.Tag.render(_templateObject29 || (_templateObject29 = babelHelpers.taggedTemplateLiteral(["<optgroup label=\"", "\"></optgroup>"])), BX.util.htmlspecialchars(optgroupLabel));
	        var groupOptions = this.availableOptionsByGroupId.get(this.gVarObjectName + ':' + visibility);

	        if (!groupOptions) {
	          continue;
	        }

	        var optionNode = void 0;

	        for (var i in groupOptions) {
	          optionNode = main_core.Tag.render(_templateObject30 || (_templateObject30 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<option value=\"", "\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</option>\n\t\t\t\t"])), BX.util.htmlspecialchars(groupOptions[i]['id']), BX.util.htmlspecialchars(groupOptions[i]['customData']['title']));
	          optgroup.appendChild(optionNode);
	        }

	        newSelect.appendChild(optgroup);
	      }

	      newSelect.value = variable;

	      if (newSelect.selectedIndex === -1) {
	        newSelect.selectedIndex = 0;
	      }

	      cellSelect.appendChild(newSelect);
	      var cellSymbolEquals = newRow.insertCell(-1);
	      cellSymbolEquals.innerHTML = '=';
	      var cellValue = newRow.insertCell(-1);
	      cellValue.id = 'id_td_variable_value_' + this.rowIndex;
	      cellValue.innerHTML = '';
	      var cellDeleteRow = newRow.insertCell(-1);
	      cellDeleteRow.aligh = 'right';
	      var deleteLink = main_core.Tag.render(_templateObject31 || (_templateObject31 = babelHelpers.taggedTemplateLiteral(["<a href=\"#\">", "</a>"])), BX.util.htmlspecialchars(BX.message('BPSGVA_PD_DELETE')));
	      var index = this.rowIndex;
	      main_core.Event.bind(deleteLink, 'click', function (event) {
	        me.deleteConditionDesigner(index);
	        event.preventDefault();
	      });
	      cellDeleteRow.appendChild(deleteLink);

	      if (main_core.Type.isArray(value)) {
	        for (var _i in value) {
	          var item = this.getOptionPropertiesRobot(value[_i]);

	          if (item.groupId === this.helperObjectName + ':text') {
	            continue;
	          }

	          value[_i] = this.convertFieldExpression(item);
	        }
	      } else {
	        var _item = this.getOptionPropertiesRobot(value);

	        if (_item.groupId !== this.helperObjectName + ':text') {
	          value = this.convertFieldExpression(_item);
	        }
	      }

	      if (value === undefined) {
	        value = null;
	      }

	      this.changeFieldTypeDesigner(this.rowIndex, newSelect.value, value);
	    }
	  }, {
	    key: "changeFieldTypeDesigner",
	    value: function changeFieldTypeDesigner(index, field, value) {
	      BX.showWait();
	      var valueTd = document.getElementById('id_td_variable_value_' + index);
	      var separatingSymbol = field.indexOf(':');
	      var fieldId = field;

	      if (separatingSymbol !== -1) {
	        fieldId = field.slice(separatingSymbol + 1, field.length - 1);
	      }

	      objFieldsGlobalVar.GetFieldInputControl(objFieldsGlobalVar.arDocumentFields[fieldId], value, {
	        'Field': fieldId,
	        'Form': this.formName
	      }, function (v) {
	        if (v === undefined) {
	          valueTd.innerHTML = '';
	        } else {
	          valueTd.innerHTML = v;

	          if (typeof BX.Bizproc.Selector !== 'undefined') {
	            BX.Bizproc.Selector.initSelectors(valueTd);
	          }
	        }

	        BX.closeWait();
	      }, true);
	    }
	  }, {
	    key: "deleteConditionDesigner",
	    value: function deleteConditionDesigner(index) {
	      var addrowTable = document.getElementById(this.addRowTableNodeId);
	      var count = addrowTable.rows.length;

	      for (var i = 0; i < count; i++) {
	        if (addrowTable.rows[i].id !== 'delete_row_' + index) {
	          continue;
	        }

	        addrowTable.deleteRow(i);
	        break;
	      }
	    }
	  }]);
	  return SetGlobalVariableActivity;
	}();

	namespace.SetGlobalVariableActivity = SetGlobalVariableActivity;

}((this.window = this.window || {}),BX,BX.Bizproc,BX.UI.EntitySelector));
//# sourceMappingURL=script.js.map
