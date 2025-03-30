/* eslint-disable */
this.BX = this.BX || {};
this.BX.Desktop = this.BX.Desktop || {};
this.BX.Desktop.Field = this.BX.Desktop.Field || {};
(function (exports,ui_entitySelector,main_core,main_core_events) {
	'use strict';

	var _templateObject, _templateObject2, _templateObject3, _templateObject4;
	var Dialog = /*#__PURE__*/function () {
	  function Dialog(params) {
	    var _this = this;
	    babelHelpers.classCallCheck(this, Dialog);
	    babelHelpers.defineProperty(this, "targetNode", null);
	    babelHelpers.defineProperty(this, "wrapper", null);
	    babelHelpers.defineProperty(this, "valuesWrapper", null);
	    babelHelpers.defineProperty(this, "input", null);
	    babelHelpers.defineProperty(this, "dialogSelector", null);
	    babelHelpers.defineProperty(this, "tagSelector", null);
	    babelHelpers.defineProperty(this, "selectedItems", new Set());
	    babelHelpers.defineProperty(this, "items", new Set());
	    babelHelpers.defineProperty(this, "messages", []);
	    this.targetNode = document.getElementById(params.targetNodeId);
	    if (this.targetNode === null) {
	      throw new Error("Target node: ".concat(params.targetNodeId, " not found"));
	    }
	    this.fieldName = params.fieldName.toLowerCase();
	    this.fieldNameForEvent = params.fieldNameForEvent;
	    this.emptyValueTitle = params.emptyValueTitle;
	    this.fieldTitle = params.fieldTitle;
	    this.context = params.context;
	    this.messages = params.messages;
	    this.isMultiple = params.isMultiple === 'true';
	    this.prepareItems(params);
	    this.createWrappers();
	    if (this.isMultiple) {
	      main_core.Runtime.loadExtension('ui.entity-selector').then(function (exports) {
	        _this.tagSelector = _this.getTagSelector(exports.TagSelector);
	        _this.tagSelector.renderTo(_this.wrapper);
	        _this.adjustLayout(false);
	      });
	    } else {
	      main_core.Runtime.loadExtension('ui.entity-selector').then(function (exports) {
	        _this.dialogSelector = _this.getDialogSelector(exports.Dialog);
	        _this.prepareInput(_this.targetNode);
	        main_core.Event.bind(_this.targetNode, 'click', function () {
	          _this.show();
	        });
	        if (_this.selectedItems.size) {
	          var selectedItems = babelHelpers.toConsumableArray(_this.selectedItems);
	          _this.input.value = selectedItems[0].title;
	        }
	        _this.adjustLayout(false);
	      });
	    }
	  }
	  babelHelpers.createClass(Dialog, [{
	    key: "prepareItems",
	    value: function prepareItems(params) {
	      var _this2 = this;
	      var values = params.items;
	      if (!Array.isArray(values)) {
	        if (values === '') {
	          return;
	        }
	        values = [values];
	      }
	      var entityId = this.fieldName;
	      values.forEach(function (element) {
	        var setItem = {
	          id: element.VALUE,
	          entityId: entityId,
	          title: element.NAME,
	          tabs: entityId
	        };
	        _this2.items.add(setItem);
	        if (element.IS_SELECTED === true) {
	          _this2.selectedItems.add(setItem);
	        }
	      });
	    }
	  }, {
	    key: "prepareInput",
	    value: function prepareInput(node) {
	      this.input = main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<input \n\t\t\t\tname=\"", "_input\" \n\t\t\t\ttype=\"text\" \n\t\t\t\tclass=\"ui-ctl-element main-ui-control main-enum-dialog-input\" \n\t\t\t\tautocomplete=\"off\"\n\t\t\t\tplaceholder=\"", "\"\n\t\t\t/>\n\t\t"])), node.id, this.emptyValueTitle);
	      main_core.Dom.append(this.input, node);
	      var dialogSelector = this.dialogSelector;
	      var input = this.input;
	      main_core.Event.bind(this.input, 'keyup', function (event) {
	        if (!input.value.length) {
	          dialogSelector.search('');
	          dialogSelector.clearSearch();
	          dialogSelector.deselectAll();
	          dialogSelector.hide();
	        } else {
	          var selectedItems = dialogSelector.getSelectedItems();
	          if (!selectedItems.some(function (item) {
	            return item.title.getText() === input.value;
	          })) {
	            dialogSelector.show();
	            dialogSelector.clearSearch();
	            dialogSelector.search(input.value);
	          }
	        }
	      });
	    }
	  }, {
	    key: "createWrappers",
	    value: function createWrappers() {
	      this.createWrapper();
	      this.createValuesWrapper();
	    }
	  }, {
	    key: "createWrapper",
	    value: function createWrapper() {
	      this.wrapper = main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-ctl-w100\"></div>"])));
	      this.targetNode.appendChild(this.wrapper);
	    }
	  }, {
	    key: "createValuesWrapper",
	    value: function createValuesWrapper() {
	      this.valuesWrapper = main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["<div></div>"])));
	      this.wrapper.appendChild(this.valuesWrapper);
	    }
	  }, {
	    key: "show",
	    value: function show() {
	      this.dialogSelector.show();
	    }
	  }, {
	    key: "getDialogSelector",
	    value: function getDialogSelector(entitySelector) {
	      var options = this.getDialogOptions();
	      options.targetNode = this.targetNode;
	      options.events = {
	        'Item:onSelect': this.onElementSelect.bind(this),
	        'Item:onDeselect': this.onElementDeselect.bind(this)
	      };
	      return new entitySelector(options);
	    }
	  }, {
	    key: "getTagSelector",
	    value: function getTagSelector(tagSelector) {
	      return new tagSelector({
	        addButtonCaption: this.getMessage('addButtonCaption'),
	        addButtonCaptionMore: this.getMessage('addButtonCaptionMore'),
	        showCreateButton: false,
	        dialogOptions: this.getDialogOptions(),
	        items: this.getDialogSelectedItems(),
	        height: 240,
	        textBoxWidth: '100%',
	        events: {
	          onTagAdd: this.onElementSelect.bind(this),
	          onTagRemove: this.onElementDeselect.bind(this)
	        }
	      });
	    }
	  }, {
	    key: "getDialogOptions",
	    value: function getDialogOptions() {
	      return {
	        context: this.context,
	        items: this.getDialogItems(),
	        selectedItems: this.getDialogSelectedItems(),
	        height: 240,
	        dropdownMode: true,
	        showAvatars: false,
	        compactView: true,
	        multiple: this.isMultiple,
	        enableSearch: false,
	        tabs: [{
	          id: this.fieldName,
	          title: this.fieldTitle
	        }]
	      };
	    }
	  }, {
	    key: "getDialogItems",
	    value: function getDialogItems() {
	      return babelHelpers.toConsumableArray(this.items);
	    }
	  }, {
	    key: "getDialogSelectedItems",
	    value: function getDialogSelectedItems() {
	      return babelHelpers.toConsumableArray(this.selectedItems);
	    }
	  }, {
	    key: "onElementSelect",
	    value: function onElementSelect(event) {
	      var item = this.getItemFromEventData(event);
	      if (!this.isMultiple) {
	        this.selectedItems.clear();
	        this.input.value = item.getTitle();
	      }
	      this.selectedItems.add(this.createOption(item));
	      this.adjustLayout();
	    }
	  }, {
	    key: "onElementDeselect",
	    value: function onElementDeselect(event) {
	      var item = this.getItemFromEventData(event);
	      var unselectedItem = this.createOption(item);
	      if (!this.isMultiple) {
	        this.selectedItems.clear();
	        this.input.value = '';
	      }

	      // remove object "unselectedItem" from selectedItems array
	      this.selectedItems = new Set(babelHelpers.toConsumableArray(this.selectedItems).filter(function (element) {
	        return JSON.stringify(element) !== JSON.stringify(unselectedItem);
	      }));
	      this.adjustLayout();
	    }
	  }, {
	    key: "getItemFromEventData",
	    value: function getItemFromEventData(event) {
	      return this.isMultiple ? event.getData().tag : event.getData().item;
	    }
	  }, {
	    key: "createOption",
	    value: function createOption(item) {
	      return {
	        id: item.id,
	        entityId: this.fieldName,
	        title: item.title,
	        tabs: this.fieldName
	      };
	    }
	  }, {
	    key: "adjustLayout",
	    value: function adjustLayout() {
	      var _this3 = this;
	      var isChanged = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : true;
	      this.clearValueItems();
	      if (this.selectedItems.size) {
	        this.selectedItems.forEach(function (item) {
	          _this3.adjustItem(item.id);
	        });
	      } else {
	        this.adjustItem('');
	      }
	      if (isChanged) {
	        BX.fireEvent(document.getElementById(this.fieldNameForEvent), 'change');
	      }
	    }
	  }, {
	    key: "clearValueItems",
	    value: function clearValueItems() {
	      this.valuesWrapper.innerHTML = '';
	    }
	  }, {
	    key: "adjustItem",
	    value: function adjustItem(id) {
	      this.valuesWrapper.appendChild(this.createInputTag(id));
	    }
	  }, {
	    key: "createInputTag",
	    value: function createInputTag(id) {
	      return main_core.Tag.render(_templateObject4 || (_templateObject4 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<input name=\"", "\" type=\"hidden\" value=\"", "\"/>\n\t\t"])), this.fieldName.toUpperCase(), id);
	    }
	  }, {
	    key: "getMessage",
	    value: function getMessage(key) {
	      var _this$messages$key;
	      return (_this$messages$key = this.messages[key]) !== null && _this$messages$key !== void 0 ? _this$messages$key : null;
	    }
	  }]);
	  return Dialog;
	}();

	var Ui = /*#__PURE__*/function () {
	  function Ui(params) {
	    babelHelpers.classCallCheck(this, Ui);
	    babelHelpers.defineProperty(this, "fieldName", null);
	    babelHelpers.defineProperty(this, "container", null);
	    babelHelpers.defineProperty(this, "valueContainerId", null);
	    babelHelpers.defineProperty(this, "value", null);
	    babelHelpers.defineProperty(this, "items", null);
	    babelHelpers.defineProperty(this, "defaultFieldName", null);
	    babelHelpers.defineProperty(this, "block", null);
	    babelHelpers.defineProperty(this, "formName", null);
	    babelHelpers.defineProperty(this, "params", {});
	    this.fieldName = params['fieldName'] || '';
	    this.container = document.getElementById(params['container']);
	    this.valueContainerId = params['valueContainerId'] || '';
	    this.value = params['value'];
	    this.items = params['items'];
	    this.block = params['block'];
	    this.defaultFieldName = params['defaultFieldName'] || this.fieldName + '_default';
	    this.formName = params['formName'] || '';
	    this.params = params['params'] || {};
	    this.bindElement();
	  }
	  babelHelpers.createClass(Ui, [{
	    key: "bindElement",
	    value: function bindElement() {
	      this.container.appendChild(BX.decl({
	        block: this.block,
	        name: this.fieldName,
	        items: this.items,
	        value: this.value,
	        params: this.params,
	        valueDelete: false
	      }));
	      this.onChangeHandler = this.onChange.bind(this);
	      main_core_events.EventEmitter.subscribe('UI::Select::change', this.onChangeHandler);
	      BX.bind(this.container, 'click', BX.defer(function () {
	        this.onChange({
	          params: this.params,
	          node: this.container.firstChild
	        });
	      }.bind(this)));
	    }
	  }, {
	    key: "onChange",
	    value: function onChange(eventNode) {
	      var controlObject;
	      if (eventNode instanceof main_core_events.BaseEvent) {
	        var data = eventNode.getData();
	        controlObject = data[0];
	      } else {
	        controlObject = eventNode;
	      }
	      if (!document.getElementById(this.valueContainerId)) {
	        return;
	      }
	      var currentValue = null;
	      if (controlObject.node !== null && controlObject.node.getAttribute('data-name') === this.fieldName) {
	        currentValue = JSON.parse(controlObject.node.getAttribute('data-value'));
	      } else {
	        return;
	      }
	      this.changeValue(currentValue);
	    }
	  }, {
	    key: "changeValue",
	    value: function changeValue(currentValue) {
	      var s = '';
	      if (!main_core.Type.isArray(currentValue)) {
	        if (currentValue === null) {
	          currentValue = [{
	            VALUE: ''
	          }];
	        } else {
	          currentValue = [currentValue];
	        }
	      }
	      if (currentValue.length > 0) {
	        for (var i = 0; i < currentValue.length; i++) {
	          s += "<input type=\"hidden\" name=\"".concat(this.fieldName, "\" value=\"").concat(main_core.Text.encode(currentValue[i].VALUE), "\" />");
	        }
	      } else {
	        s += "<input type=\"hidden\" name=\"".concat(this.fieldName, "\" value=\"\" />");
	      }
	      document.getElementById(this.valueContainerId).innerHTML = s;
	      BX.fireEvent(document.getElementById(this.defaultFieldName), 'change');
	    }
	  }]);
	  return Ui;
	}();

	exports.Dialog = Dialog;
	exports.Ui = Ui;

}((this.BX.Desktop.Field.Enum = this.BX.Desktop.Field.Enum || {}),BX.UI.EntitySelector,BX,BX.Event));
//# sourceMappingURL=display.bundle.js.map
