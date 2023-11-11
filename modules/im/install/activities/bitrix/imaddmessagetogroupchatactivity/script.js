/* eslint-disable */
this.BX = this.BX || {};
this.BX.Im = this.BX.Im || {};
(function (exports,main_core,ui_entitySelector,im_robot_messageTemplateSelector) {
	'use strict';

	var _templateObject, _templateObject2;
	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var namespace = main_core.Reflection.namespace('BX.Im.Activity');
	var _form = /*#__PURE__*/new WeakMap();
	var _documentType = /*#__PURE__*/new WeakMap();
	var _isRobot = /*#__PURE__*/new WeakMap();
	var _currentValues = /*#__PURE__*/new WeakMap();
	var _chatSelector = /*#__PURE__*/new WeakMap();
	var _messageTemplateFields = /*#__PURE__*/new WeakMap();
	var _messageTemplateList = /*#__PURE__*/new WeakMap();
	var _messageFieldsElement = /*#__PURE__*/new WeakMap();
	var _messageTypeBtn = /*#__PURE__*/new WeakMap();
	var _setTemplate = /*#__PURE__*/new WeakSet();
	var _renderProperty = /*#__PURE__*/new WeakSet();
	var _renderRobotProperty = /*#__PURE__*/new WeakSet();
	var _renderDesignerProperty = /*#__PURE__*/new WeakSet();
	var _renderValueElement = /*#__PURE__*/new WeakSet();
	var _initChatSelector = /*#__PURE__*/new WeakSet();
	var _initTemplateSelector = /*#__PURE__*/new WeakSet();
	var ImAddMessageToGroupChatActivity = /*#__PURE__*/function () {
	  function ImAddMessageToGroupChatActivity(parameters) {
	    babelHelpers.classCallCheck(this, ImAddMessageToGroupChatActivity);
	    _classPrivateMethodInitSpec(this, _initTemplateSelector);
	    _classPrivateMethodInitSpec(this, _initChatSelector);
	    _classPrivateMethodInitSpec(this, _renderValueElement);
	    _classPrivateMethodInitSpec(this, _renderDesignerProperty);
	    _classPrivateMethodInitSpec(this, _renderRobotProperty);
	    _classPrivateMethodInitSpec(this, _renderProperty);
	    _classPrivateMethodInitSpec(this, _setTemplate);
	    _classPrivateFieldInitSpec(this, _form, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _documentType, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _isRobot, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _currentValues, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _chatSelector, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _messageTemplateFields, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _messageTemplateList, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _messageFieldsElement, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _messageTypeBtn, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldSet(this, _form, parameters.form);
	    babelHelpers.classPrivateFieldSet(this, _isRobot, parameters.isRobot);
	    babelHelpers.classPrivateFieldSet(this, _documentType, parameters.documentType);
	    babelHelpers.classPrivateFieldSet(this, _currentValues, parameters.currentValues);
	    babelHelpers.classPrivateFieldSet(this, _messageTemplateFields, parameters.messageTemplateFields);
	    babelHelpers.classPrivateFieldSet(this, _messageTemplateList, parameters.messageTemplateList);
	    babelHelpers.classPrivateFieldSet(this, _messageFieldsElement, document.getElementById('id_message_fields'));
	    babelHelpers.classPrivateFieldSet(this, _messageTypeBtn, document.querySelector('[data-role="message-type"]'));
	    if (!main_core.Type.isPlainObject(babelHelpers.classPrivateFieldGet(this, _currentValues)['message_fields'])) {
	      babelHelpers.classPrivateFieldGet(this, _currentValues)['message_fields'] = {};
	    }
	  }
	  babelHelpers.createClass(ImAddMessageToGroupChatActivity, [{
	    key: "init",
	    value: function init() {
	      var _this = this;
	      _classPrivateMethodGet(this, _initChatSelector, _initChatSelector2).call(this);
	      _classPrivateMethodGet(this, _initTemplateSelector, _initTemplateSelector2).call(this);
	      main_core.Event.bind(babelHelpers.classPrivateFieldGet(this, _chatSelector).getTargetNode(), 'click', function () {
	        babelHelpers.classPrivateFieldGet(_this, _chatSelector).show();
	      });
	      _classPrivateMethodGet(this, _setTemplate, _setTemplate2).call(this, babelHelpers.classPrivateFieldGet(this, _form)['message_template'].value, true);
	    }
	  }, {
	    key: "showTemplateMessageFields",
	    value: function showTemplateMessageFields(newMessageTemplate) {
	      var _this2 = this;
	      if (!babelHelpers.classPrivateFieldGet(this, _messageFieldsElement)) {
	        return;
	      }
	      main_core.Dom.clean(babelHelpers.classPrivateFieldGet(this, _messageFieldsElement));
	      if (babelHelpers.classPrivateFieldGet(this, _messageTemplateFields).hasOwnProperty(newMessageTemplate)) {
	        Object.entries(babelHelpers.classPrivateFieldGet(this, _messageTemplateFields)[newMessageTemplate]).forEach(function (_ref) {
	          var _ref2 = babelHelpers.slicedToArray(_ref, 2),
	            id = _ref2[0],
	            property = _ref2[1];
	          main_core.Dom.append(_classPrivateMethodGet(_this2, _renderProperty, _renderProperty2).call(_this2, id, property), babelHelpers.classPrivateFieldGet(_this2, _messageFieldsElement));
	        });
	      }
	    }
	  }]);
	  return ImAddMessageToGroupChatActivity;
	}();
	function _setTemplate2(value, forced) {
	  if (babelHelpers.classPrivateFieldGet(this, _form)['message_template'].value === value && !forced) {
	    return;
	  }
	  if (babelHelpers.classPrivateFieldGet(this, _messageTypeBtn)) {
	    babelHelpers.classPrivateFieldGet(this, _form)['message_template'].value = value;
	    babelHelpers.classPrivateFieldGet(this, _messageTypeBtn).textContent = babelHelpers.classPrivateFieldGet(this, _messageTemplateList)[value] || '';
	  }
	  this.showTemplateMessageFields(value);
	}
	function _renderProperty2(id, property) {
	  return babelHelpers.classPrivateFieldGet(this, _isRobot) ? _classPrivateMethodGet(this, _renderRobotProperty, _renderRobotProperty2).call(this, id, property) : _classPrivateMethodGet(this, _renderDesignerProperty, _renderDesignerProperty2).call(this, id, property);
	}
	function _renderRobotProperty2(id, property) {
	  return main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"bizproc-automation-popup-settings\">\n\t\t\t\t<span class=\"bizproc-automation-popup-settings-title bizproc-automation-popup-settings-title-top bizproc-automation-popup-settings-title-autocomplete\">\n\t\t\t\t\t", "\n\t\t\t\t</span>\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"])), main_core.Text.encode(property.Name), _classPrivateMethodGet(this, _renderValueElement, _renderValueElement2).call(this, id, property));
	}
	function _renderDesignerProperty2(id, property) {
	  return main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<tr>\n\t\t\t\t<td align=\"right\" width=\"40%\">\n\t\t\t\t\t", "\n\t\t\t\t\t", ":\n\t\t\t\t\t", "\n\t\t\t\t</td>\n\t\t\t\t<td width=\"60%\">\n\t\t\t\t\t", "\n\t\t\t\t</td>\n\t\t\t</tr>\n\t\t"])), property.Required ? '<span class="adm-required-field">' : '', main_core.Text.encode(property.Name), property.Required ? '</span>' : '', _classPrivateMethodGet(this, _renderValueElement, _renderValueElement2).call(this, id, property));
	}
	function _renderValueElement2(id, property) {
	  var _this3 = this;
	  var fieldName = property['FieldName'];
	  var fieldValueElement = BX.Bizproc.FieldType.renderControl(babelHelpers.classPrivateFieldGet(this, _documentType), property, fieldName, babelHelpers.classPrivateFieldGet(this, _currentValues)['message_fields'][id], babelHelpers.classPrivateFieldGet(this, _isRobot) ? 'public' : 'designer');
	  fieldValueElement.onchange = function (event) {
	    babelHelpers.classPrivateFieldGet(_this3, _currentValues)['message_fields'][id] = event.target.value;
	  };
	  return fieldValueElement;
	}
	function _initChatSelector2() {
	  var chatFieldName = 'chat_id';
	  var chatNode = babelHelpers.classPrivateFieldGet(this, _form)[chatFieldName];
	  babelHelpers.classPrivateFieldSet(this, _chatSelector, new ui_entitySelector.Dialog({
	    entities: [{
	      id: 'im-chat',
	      options: {
	        searchableChatTypes: ['C']
	      }
	    }],
	    targetNode: chatNode,
	    multiple: false,
	    enableSearch: true,
	    hideOnSelect: true,
	    height: 300,
	    width: 490,
	    autoHide: true,
	    compactView: true,
	    showAvatars: false,
	    dropdownMode: true,
	    events: {
	      'Item:onBeforeSelect': function ItemOnBeforeSelect(event) {
	        event.preventDefault();
	        chatNode.value = event.getData().item.getId();
	      }
	    }
	  }));
	  babelHelpers.classPrivateFieldGet(this, _chatSelector).load();
	}
	function _initTemplateSelector2() {
	  var _this4 = this;
	  if (!babelHelpers.classPrivateFieldGet(this, _isRobot)) {
	    main_core.Event.bind(babelHelpers.classPrivateFieldGet(this, _form)['message_template'], 'change', function (event) {
	      _classPrivateMethodGet(_this4, _setTemplate, _setTemplate2).call(_this4, event.target.value, true);
	    });
	    return;
	  }
	  var selector = new im_robot_messageTemplateSelector.MessageTemplateSelector();
	  main_core.Event.bind(babelHelpers.classPrivateFieldGet(this, _messageTypeBtn), 'click', function () {
	    selector.show(babelHelpers.classPrivateFieldGet(_this4, _messageTypeBtn), babelHelpers.classPrivateFieldGet(_this4, _form)['message_template'].value);
	  });
	  selector.subscribe('select', function (event) {
	    _classPrivateMethodGet(_this4, _setTemplate, _setTemplate2).call(_this4, event.getData().selected);
	  });
	}
	namespace.ImAddMessageToGroupChatActivity = ImAddMessageToGroupChatActivity;

}((this.BX.Im.Activity = this.BX.Im.Activity || {}),BX,BX.UI.EntitySelector,BX.IM.Robot));
//# sourceMappingURL=script.js.map
