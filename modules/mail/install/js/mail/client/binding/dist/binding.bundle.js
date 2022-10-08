this.BX = this.BX || {};
this.BX.Mail = this.BX.Mail || {};
(function (exports,main_core,ui_notification,main_core_events) {
	'use strict';

	var _templateObject;

	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }

	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

	function _classStaticPrivateFieldSpecGet(receiver, classConstructor, descriptor) { _classCheckPrivateStaticAccess(receiver, classConstructor); _classCheckPrivateStaticFieldDescriptor(descriptor, "get"); return _classApplyDescriptorGet(receiver, descriptor); }

	function _classCheckPrivateStaticFieldDescriptor(descriptor, action) { if (descriptor === undefined) { throw new TypeError("attempted to " + action + " private static field before its declaration"); } }

	function _classCheckPrivateStaticAccess(receiver, classConstructor) { if (receiver !== classConstructor) { throw new TypeError("Private static access of wrong provenance"); } }

	function _classApplyDescriptorGet(receiver, descriptor) { if (descriptor.get) { return descriptor.get.call(receiver); } return descriptor.value; }

	var _text = /*#__PURE__*/new WeakMap();

	var _active = /*#__PURE__*/new WeakMap();

	var _id = /*#__PURE__*/new WeakMap();

	var _href = /*#__PURE__*/new WeakMap();

	var _bindingType = /*#__PURE__*/new WeakMap();

	var _wait = /*#__PURE__*/new WeakMap();

	var _node = /*#__PURE__*/new WeakMap();

	var _messageId = /*#__PURE__*/new WeakMap();

	var _messageSimpleId = /*#__PURE__*/new WeakMap();

	var _createHref = /*#__PURE__*/new WeakMap();

	var _waitCSSClassName = /*#__PURE__*/new WeakMap();

	var _errorType = /*#__PURE__*/new WeakMap();

	var _phrases = /*#__PURE__*/new WeakMap();

	var _phrasesFull = /*#__PURE__*/new WeakMap();

	var _classes = /*#__PURE__*/new WeakMap();

	var Item = /*#__PURE__*/function () {
	  babelHelpers.createClass(Item, [{
	    key: "isError",
	    value: function isError(errorKey) {
	      if (_classStaticPrivateFieldSpecGet(Item, Item, _errorPhrases)[errorKey] !== undefined) {
	        return true;
	      }

	      return false;
	    }
	  }, {
	    key: "isActive",
	    value: function isActive() {
	      return babelHelpers.classPrivateFieldGet(this, _active);
	    }
	  }, {
	    key: "getId",
	    value: function getId() {
	      return babelHelpers.classPrivateFieldGet(this, _id);
	    }
	  }, {
	    key: "getMessageId",
	    value: function getMessageId() {
	      var simple = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : false;

	      if (!simple) {
	        return babelHelpers.classPrivateFieldGet(this, _messageId);
	      } else {
	        return babelHelpers.classPrivateFieldGet(this, _messageSimpleId);
	      }
	    }
	  }]);

	  function Item() {
	    var config = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {
	      type: '',
	      id: ''
	    };
	    babelHelpers.classCallCheck(this, Item);

	    _classPrivateFieldInitSpec(this, _text, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec(this, _active, {
	      writable: true,
	      value: false
	    });

	    _classPrivateFieldInitSpec(this, _id, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec(this, _href, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec(this, _bindingType, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec(this, _wait, {
	      writable: true,
	      value: false
	    });

	    _classPrivateFieldInitSpec(this, _node, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec(this, _messageId, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec(this, _messageSimpleId, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec(this, _createHref, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec(this, _waitCSSClassName, {
	      writable: true,
	      value: 'ui-btn-wait'
	    });

	    _classPrivateFieldInitSpec(this, _errorType, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec(this, _phrases, {
	      writable: true,
	      value: {
	        'crm': 'MAIL_BINDING_CRM_',
	        'chat': 'MAIL_BINDING_CHAT_',
	        'task': 'MAIL_BINDING_TASK_',
	        'post': 'MAIL_BINDING_POST_',
	        'meeting': 'MAIL_BINDING_MEETING_'
	      }
	    });

	    _classPrivateFieldInitSpec(this, _phrasesFull, {
	      writable: true,
	      value: {
	        'crm': 'MAIL_BINDING_CRM_TITLE',
	        'chat': 'MAIL_BINDING_CHAT_TITLE',
	        'task': 'MAIL_BINDING_TASK_TITLE',
	        'post': 'MAIL_BINDING_POST_TITLE',
	        'meeting': 'MAIL_BINDING_MEETING_TITLE'
	      }
	    });

	    _classPrivateFieldInitSpec(this, _classes, {
	      writable: true,
	      value: {
	        'crm': 'mail-binding-crm',
	        'chat': 'mail-binding-chat',
	        'task': 'mail-binding-task',
	        'post': 'mail-binding-post',
	        'meeting': 'mail-binding-meeting'
	      }
	    });

	    babelHelpers.classPrivateFieldSet(this, _errorType, config['errorType']);
	    babelHelpers.classPrivateFieldSet(this, _messageId, config['messageId']);
	    babelHelpers.classPrivateFieldSet(this, _id, config['id']);
	    babelHelpers.classPrivateFieldSet(this, _href, config['href']);
	    babelHelpers.classPrivateFieldSet(this, _bindingType, config['type']);
	    babelHelpers.classPrivateFieldSet(this, _messageSimpleId, config['messageSimpleId']);
	    babelHelpers.classPrivateFieldSet(this, _createHref, config['createHref']);

	    if (babelHelpers.classPrivateFieldGet(this, _id)) {
	      babelHelpers.classPrivateFieldSet(this, _active, true);
	    }

	    if (this.isActive()) {
	      babelHelpers.classPrivateFieldSet(this, _text, main_core.Loc.getMessage(babelHelpers.classPrivateFieldGet(this, _phrases)[babelHelpers.classPrivateFieldGet(this, _bindingType)] + 'ACTIVE'));
	    } else {
	      babelHelpers.classPrivateFieldSet(this, _text, main_core.Loc.getMessage(babelHelpers.classPrivateFieldGet(this, _phrases)[babelHelpers.classPrivateFieldGet(this, _bindingType)] + 'NOT_ACTIVE'));
	    }
	  }

	  babelHelpers.createClass(Item, [{
	    key: "getType",
	    value: function getType() {
	      return babelHelpers.classPrivateFieldGet(this, _bindingType);
	    }
	  }, {
	    key: "onClick",
	    value: function onClick(event) {
	      if (this.isError(babelHelpers.classPrivateFieldGet(this, _errorType))) {
	        Item.showError(babelHelpers.classPrivateFieldGet(this, _errorType));
	        return;
	      }

	      if (this.isActive()) {
	        //to join the chat if you left it
	        if (this.getType() === 'chat') {
	          BX.Mail.Secretary.getInstance(this.getMessageId(true)).openChat();
	        }
	      } else if (!babelHelpers.classPrivateFieldGet(this, _wait)) {
	        switch (this.getType()) {
	          case 'crm':
	            this.startWait();
	            BX.Mail.Client.Message.List["mail-client-list-manager"].onCrmClick(this.getMessageId());
	            break;

	          case 'chat':
	            BX.Mail.Secretary.getInstance(this.getMessageId(true)).openChat();
	            break;

	          case 'task':
	            top.BX.SidePanel.Instance.open(babelHelpers.classPrivateFieldGet(this, _createHref));
	            break;

	          case 'post':
	            top.BX.SidePanel.Instance.open(babelHelpers.classPrivateFieldGet(this, _createHref));
	            break;

	          case 'meeting':
	            BX.Mail.Secretary.getInstance(this.getMessageId(true)).openCalendarEvent();
	            break;
	        }
	      }
	    }
	  }, {
	    key: "getHref",
	    value: function getHref() {
	      return babelHelpers.classPrivateFieldGet(this, _href);
	    }
	  }, {
	    key: "setText",
	    value: function setText(text) {
	      babelHelpers.classPrivateFieldGet(this, _node).textContent = text;
	    }
	  }, {
	    key: "getNode",
	    value: function getNode() {
	      return babelHelpers.classPrivateFieldGet(this, _node);
	    }
	  }, {
	    key: "startWait",
	    value: function startWait() {
	      babelHelpers.classPrivateFieldSet(this, _wait, true);
	      this.getNode().classList.add(babelHelpers.classPrivateFieldGet(this, _waitCSSClassName));
	    }
	  }, {
	    key: "stopWait",
	    value: function stopWait() {
	      babelHelpers.classPrivateFieldSet(this, _wait, false);
	      this.getNode().classList.remove(babelHelpers.classPrivateFieldGet(this, _waitCSSClassName));
	    }
	  }, {
	    key: "setActive",
	    value: function setActive(href) {
	      this.stopWait();
	      this.getNode().classList.remove("mail-ui-not-active");
	      this.getNode().classList.add("mail-ui-active");
	      this.setText(main_core.Loc.getMessage(babelHelpers.classPrivateFieldGet(this, _phrases)[this.getType()] + 'ACTIVE'));
	      this.getNode().setAttribute("href", href);
	      babelHelpers.classPrivateFieldSet(this, _active, true);
	      this.updateTitle();
	    }
	  }, {
	    key: "deactivation",
	    value: function deactivation() {
	      this.stopWait();
	      this.getNode().classList.add("mail-ui-not-active");
	      this.getNode().classList.remove("mail-ui-active");
	      this.setText(main_core.Loc.getMessage(babelHelpers.classPrivateFieldGet(this, _phrases)[this.getType()] + 'NOT_ACTIVE'));
	      this.getNode().removeAttribute("href");
	      babelHelpers.classPrivateFieldSet(this, _active, false);
	      this.updateTitle();
	    }
	  }, {
	    key: "getTitle",
	    value: function getTitle() {
	      return main_core.Loc.getMessage(babelHelpers.classPrivateFieldGet(this, _phrasesFull)[this.getType()] + (this.isActive() ? '_ACTIVE' : ''));
	    }
	  }, {
	    key: "updateTitle",
	    value: function updateTitle() {
	      this.getNode().removeAttribute("title");
	      this.getNode().setAttribute("title", this.getTitle());
	    }
	  }, {
	    key: "render",
	    value: function render() {
	      var activeClass = this.isActive() ? 'mail-ui-active' : 'mail-ui-not-active';
	      var item = main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<a class=\"mail-ui-binding ui-btn-light-border ui-btn ui-btn-xs ui-btn-round ui-btn-no-caps ", " ", " js-bind-", "\">\n\t\t\t\t", "\n\t\t\t</a>"])), babelHelpers.classPrivateFieldGet(this, _classes)[this.getType()], activeClass, this.getMessageId(true), babelHelpers.classPrivateFieldGet(this, _text));
	      babelHelpers.classPrivateFieldSet(this, _node, item);
	      babelHelpers.classPrivateFieldGet(this, _node).object = this;
	      this.updateTitle();

	      item.onclick = function () {
	        this.object.onClick();
	      };

	      item.ondblclick = function (event) {
	        event.stopPropagation();
	      };

	      item.setActive = function (href) {
	        this.object.setActive(href);
	      };

	      item.deactivation = function () {
	        this.object.deactivation();
	      };

	      item.startWait = function () {
	        this.object.startWait();
	      };

	      item.stopWait = function () {
	        this.object.stopWait();
	      };

	      if (babelHelpers.classPrivateFieldGet(this, _errorType) === 'crm-install-permission-error' && this.getHref()) {
	        babelHelpers.classPrivateFieldSet(this, _errorType, 'crm-install-permission-open-error');
	      }

	      if (this.isActive() && !this.isError(babelHelpers.classPrivateFieldGet(this, _errorType))) {
	        item.setAttribute("href", this.getHref());
	      }

	      return item;
	    }
	  }], [{
	    key: "showError",
	    value: function showError(key) {
	      ui_notification.UI.Notification.Center.notify({
	        content: main_core.Loc.getMessage(_classStaticPrivateFieldSpecGet(Item, Item, _errorPhrases)[key])
	      });
	    }
	  }]);
	  return Item;
	}();
	var _errorPhrases = {
	  writable: true,
	  value: {
	    'crm-install-error': 'MAIL_BINDING_CRM_ERROR',
	    'calendar-install-error': 'MAIL_BINDING_MEETING_ERROR',
	    'tasks-install-error': 'MAIL_BINDING_TASK_ERROR',
	    'chat-install-error': 'MAIL_BINDING_CHAT_ERROR',
	    'socialnetwork-install-error': 'MAIL_BINDING_POST_ERROR',
	    'crm-install-permission-error': 'MAIL_BINDING_CRM_PERMISSION_SAVE_ERROR',
	    'crm-install-permission-open-error': 'MAIL_BINDING_CRM_PERMISSION_OPEN_ERROR',
	    'crm-install-permission-working-error': 'MAIL_BINDING_CRM_PERMISSION_WORKING_ERROR'
	  }
	};

	function _classPrivateFieldInitSpec$1(obj, privateMap, value) { _checkPrivateRedeclaration$1(obj, privateMap); privateMap.set(obj, value); }

	function _checkPrivateRedeclaration$1(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

	var _mailboxId = /*#__PURE__*/new WeakMap();

	var _selectors = /*#__PURE__*/new WeakMap();

	var Binding = /*#__PURE__*/function () {
	  babelHelpers.createClass(Binding, [{
	    key: "getMailbox",
	    value: function getMailbox() {
	      return babelHelpers.classPrivateFieldGet(this, _mailboxId);
	    }
	  }]);

	  function Binding(mailboxId) {
	    var _this = this;

	    babelHelpers.classCallCheck(this, Binding);

	    _classPrivateFieldInitSpec$1(this, _mailboxId, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec$1(this, _selectors, {
	      writable: true,
	      value: {
	        CRM_ACTIVITY: '.mail-binding-crm',
	        TASKS_TASK: '.mail-binding-task',
	        IM_CHAT: '.mail-binding-chat',
	        BLOG_POST: '.mail-binding-post',
	        CALENDAR_EVENT: '.mail-binding-meeting'
	      }
	    });

	    babelHelpers.classPrivateFieldSet(this, _mailboxId, mailboxId);
	    main_core_events.EventEmitter.subscribe('onPullEvent-mail', function (event) {
	      var data = event.getData();

	      if (data[0] === "messageBindingCreated" && (data[1]['mailboxId'] === _this.getMailbox() || data[1]['mailboxId'] === String(_this.getMailbox()))) {
	        var binding = data[1];
	        var messageSimpleId = binding['messageId'];
	        var bindingWrapper = document.querySelector("" + ('.js-bind-' + messageSimpleId) + babelHelpers.classPrivateFieldGet(_this, _selectors)[binding['entityType']] + "");

	        if (bindingWrapper) {
	          bindingWrapper.setActive(binding['bindingEntityLink']);
	        }
	      }

	      if (data[0] === "messageBindingDeleted" && (data[1]['mailboxId'] === _this.getMailbox() || data[1]['mailboxId'] === String(_this.getMailbox()))) {
	        var _binding = data[1];
	        var _messageSimpleId = _binding['messageId'];

	        var _bindingWrapper = document.querySelector("" + ('.js-bind-' + _messageSimpleId) + babelHelpers.classPrivateFieldGet(_this, _selectors)[_binding['entityType']] + "");

	        if (_bindingWrapper) {
	          _bindingWrapper.deactivation();
	        }
	      }
	    });
	  }

	  babelHelpers.createClass(Binding, null, [{
	    key: "build",
	    value: function build(config) {
	      var item = new Item(config);
	      return item.render();
	    }
	  }, {
	    key: "replaceElement",
	    value: function replaceElement(object) {
	      var parent = object.parentNode;
	      var newObject = this.build({
	        type: object.getAttribute('bind-type'),
	        id: object.getAttribute('bind-id'),
	        messageId: object.getAttribute('message-id'),
	        messageSimpleId: object.getAttribute('message-simple-id'),
	        href: object.getAttribute('bind-href'),
	        createHref: object.getAttribute('create-href'),
	        errorType: object.getAttribute('error-type')
	      });
	      parent.replaceChild(newObject, object);
	    }
	  }, {
	    key: "initButtons",
	    value: function initButtons() {
	      var context = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : document.body;
	      var elements = Array.from(context.getElementsByClassName('mail-ui-binding-data'));

	      for (var _i = 0, _elements = elements; _i < _elements.length; _i++) {
	        var element = _elements[_i];
	        this.replaceElement(element);
	      }
	    }
	  }]);
	  return Binding;
	}();

	exports.Binding = Binding;
	exports.Item = Item;

}((this.BX.Mail.Client = this.BX.Mail.Client || {}),BX,BX,BX.Event));
//# sourceMappingURL=binding.bundle.js.map
