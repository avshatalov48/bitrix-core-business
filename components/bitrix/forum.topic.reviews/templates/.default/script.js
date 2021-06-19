this.BX = this.BX || {};
this.BX.Forum = this.BX.Forum || {};
(function (exports,main_core,main_core_events) {
	'use strict';

	var Form = /*#__PURE__*/function () {
	  babelHelpers.createClass(Form, null, [{
	    key: "makeQuote",
	    value: function makeQuote(formId, _ref) {
	      var entity = _ref.entity,
	          messageId = _ref.messageId,
	          text = _ref.text;

	      if (Form.instances[formId]) {
	        Form.instances[formId].quote({
	          entity: entity,
	          messageId: messageId,
	          text: text
	        });
	      }
	    }
	  }, {
	    key: "makeReply",
	    value: function makeReply(formId, _ref2) {
	      var entity = _ref2.entity,
	          messageId = _ref2.messageId,
	          text = _ref2.text;

	      if (Form.instances[formId]) {
	        Form.instances[formId].reply({
	          entity: entity,
	          messageId: messageId,
	          text: text
	        });
	      }
	    }
	  }, {
	    key: "create",
	    value: function create(_ref3) {
	      var formId = _ref3.formId,
	          editorId = _ref3.editorId,
	          formNode = _ref3.formNode,
	          useAjax = _ref3.useAjax;

	      if (!Form.instances[formId]) {
	        Form.instances[formId] = new Form({
	          formId: formId,
	          editorId: editorId,
	          formNode: formNode,
	          useAjax: useAjax
	        });
	      }

	      return Form.instances[formId];
	    }
	  }]);

	  function Form(_ref4) {
	    var formId = _ref4.formId,
	        editorId = _ref4.editorId,
	        formNode = _ref4.formNode,
	        useAjax = _ref4.useAjax;
	    babelHelpers.classCallCheck(this, Form);
	    this.formId = formId;
	    this.editorId = editorId;
	    this.currentEntity = {
	      entity: null,
	      messageId: null
	    };
	    this.init = this.init.bind(this);
	    main_core_events.EventEmitter.subscribe('OnEditorInitedAfter', this.init);
	    this.useAjax = useAjax === true;
	    this.formNode = formNode;
	    this.formNode.addEventListener('submit', this.submit.bind(this));
	    this.container = this.formNode.parentNode;
	    this.onSuccess = this.onSuccess.bind(this);
	    this.onFailure = this.onFailure.bind(this);
	  }

	  babelHelpers.createClass(Form, [{
	    key: "init",
	    value: function init(_ref5) {
	      var target = _ref5.target;

	      if (target.id !== this.editorId) {
	        return;
	      }

	      this.editor = target;
	      target.insertImageAfterUpload = true;
	      main_core_events.EventEmitter.unsubscribe('OnEditorInitedAfter', this.init);
	      BX.bind(BX('post_message_hidden'), "focus", function () {
	        target.Focus();
	      });
	    }
	  }, {
	    key: "submit",
	    value: function submit(event) {
	      var text = '';

	      if (this.getLHE().editorIsLoaded) {
	        this.getLHE().oEditor.SaveContent();
	        text = this.getLHE().oEditor.GetContent();
	      }

	      var error = [];

	      if (text.length <= 0) {
	        error.push(main_core.Loc.getMessage('JERROR_NO_MESSAGE'));
	      } else if (text.length > Form.maxMessageLength) {
	        error.push(main_core.Loc.getMessage('JERROR_MAX_LEN').replace(/#MAX_LENGTH#/gi, Form.maxMessageLength).replace(/#LENGTH#/gi, text.length));
	      } else if (this.isOccupied()) {
	        error.push('Occupied');
	      }

	      if (error.length <= 0) {
	        this.occupy();

	        if (!this.useAjax) {
	          return true;
	        }

	        this.send();
	      } else {
	        alert(error.join(''));
	      }

	      event.stopPropagation();
	      event.preventDefault();
	      return false;
	    }
	  }, {
	    key: "isOccupied",
	    value: function isOccupied() {
	      return this.busy === true;
	    }
	  }, {
	    key: "occupy",
	    value: function occupy() {
	      this.busy = true;
	      this.formNode.querySelectorAll("input[type=submit]").forEach(function (input) {
	        input.disabled = true;
	      });
	    }
	  }, {
	    key: "release",
	    value: function release() {
	      this.busy = false;
	      this.formNode.querySelectorAll("input[type=submit]").forEach(function (input) {
	        input.disabled = false;
	      });
	    }
	  }, {
	    key: "send",
	    value: function send() {
	      var secretNode = document.createElement('input');
	      secretNode.type = 'hidden';
	      secretNode.name = 'dataType';
	      secretNode.value = 'json';
	      this.formNode.appendChild(secretNode);
	      BX.ajax.submitAjax(this.formNode, {
	        method: 'POST',
	        url: this.formNode.action,
	        dataType: 'json',
	        onsuccess: this.onSuccess,
	        onfailure: this.onFailure
	      });
	      this.formNode.removeChild(secretNode);
	    }
	  }, {
	    key: "onSuccess",
	    value: function onSuccess(_ref6) {
	      var status = _ref6.status,
	          action = _ref6.action,
	          data = _ref6.data,
	          errors = _ref6.errors;
	      this.release();

	      if (status !== 'success') {
	        return this.showError(data.errorHtml, errors);
	      } else if (action === 'preview') {
	        return this.showPreview(data.previewHtml);
	      } else if (action === 'add') {
	        // Legacy sake for
	        main_core_events.EventEmitter.emit('onForumCommentAJAXPost', [data, this.formNode]);
	        main_core_events.EventEmitter.emit(this.currentEntity.entity, 'onForumCommentAdded', data);
	        return this.clear();
	      }

	      this.showError('There is nothing');
	    }
	  }, {
	    key: "onFailure",
	    value: function onFailure() {
	      this.release();
	      this.showError('<b class="error">Some error with response</b>');
	    }
	  }, {
	    key: "showError",
	    value: function showError(errorHTML) {
	      var errorNode = this.container.querySelector('div[data-bx-role=error]');
	      errorNode.innerHTML = errorHTML;
	      this.container.setAttribute('data-bx-status', 'errored');
	      errorNode.style.display = 'block';
	    }
	  }, {
	    key: "hideError",
	    value: function hideError() {
	      var errorNode = this.container.querySelector('div[data-bx-role=error]');
	      errorNode.innerHTML = '';
	      this.container.removeAttribute('data-bx-status', 'errored');
	      errorNode.style.display = 'none';
	    }
	  }, {
	    key: "showPreview",
	    value: function showPreview(previewHTML) {
	      var previewNode = this.container.querySelector('div[data-bx-role=preview]');
	      previewNode.innerHTML = previewHTML;
	      this.container.setAttribute('data-bx-status', 'preview');
	      previewNode.style.display = 'block';
	    }
	  }, {
	    key: "hidePreview",
	    value: function hidePreview() {
	      var previewNode = this.container.querySelector('div[data-bx-role=preview]');
	      previewNode.innerHTML = '';
	      this.container.setAttribute('data-bx-status', 'preview');
	      previewNode.style.display = 'none';
	    }
	  }, {
	    key: "isFormReady",
	    value: function isFormReady(_ref7) {
	      var entity = _ref7.entity,
	          messageId = _ref7.messageId;

	      if (this.currentEntity.entity === null || this.currentEntity.entity === entity) {
	        return true;
	      }

	      return window.confirm("Do you want to miss all changes?");
	    }
	  }, {
	    key: "parseText",
	    value: function parseText(text) {
	      var editor = this.getLHE().oEditor;
	      var tmpTxt = text;

	      if (tmpTxt.length > 0 && editor.GetViewMode() === "wysiwyg") {
	        var reg = /^\[USER\=(\d+)\](.+?)\[\/USER\]/i;

	        if (reg.test(tmpTxt)) {
	          tmpTxt = tmpTxt.replace(reg, function () {
	            var userId = parseInt(arguments[1]);
	            var userName = main_core.Text.encode(arguments[2]);
	            var result = "<span>".concat(userName, "</span>");

	            if (userId > 0) {
	              var tagId = editor.SetBxTag(false, {
	                tag: "postuser",
	                params: {
	                  value: userId
	                }
	              });
	              result = "<span id=\"".concat(tagId, "\" class=\"bxhtmled-metion\">").concat(userName, "</span>");
	            }

	            return result;
	          }.bind(this));
	        }
	      }

	      return tmpTxt;
	    }
	  }, {
	    key: "reply",
	    value: function reply(_ref8) {
	      var _this = this;

	      var entity = _ref8.entity,
	          messageId = _ref8.messageId,
	          text = _ref8.text;
	      this.show({
	        entity: entity,
	        messageId: messageId
	      }).then(function () {
	        if (text !== '') {
	          var editor = _this.getLHE().oEditor;

	          var tmpText = _this.parseText(text);

	          editor.action.Exec("insertHTML", tmpText);
	        }
	      });
	    }
	  }, {
	    key: "quote",
	    value: function quote(_ref9) {
	      var _this2 = this;

	      var entity = _ref9.entity,
	          messageId = _ref9.messageId,
	          text = _ref9.text;
	      this.show({
	        entity: entity,
	        messageId: messageId
	      }).then(function () {
	        var editor = _this2.getLHE().oEditor;

	        if (!editor.toolbar.controls.Quote) {
	          return;
	        }

	        var tmpText = _this2.parseText(text);

	        if (editor.action.actions.quote.setExternalSelectionFromRange) {
	          editor.action.actions.quote.setExternalSelection(tmpText);
	        }

	        editor.action.Exec("quote");
	      });
	    }
	  }, {
	    key: "clear",
	    value: function clear() {
	      this.hideError();
	      this.hidePreview();
	      this.editor.CheckAndReInit('');

	      if (this.editor.fAutosave && this.editor.pEditorDocument) {
	        this.editor.pEditorDocument.addEventListener('keydown', this.editor.fAutosave.Init.bind(this.editor.fAutosave));
	      }

	      this.formNode.querySelectorAll('.reviews-preview').forEach(function (node) {
	        node.parentNode.removeChild(node);
	      });
	      this.formNode.querySelectorAll('input[type="file"]').forEach(function (node) {
	        var newNode = node.cloneNode();
	        newNode.value = '';
	        node.parentNode.replaceChild(newNode, node);
	      });
	      var visibilityCheckbox = this.formNode.querySelector('[data-bx-role="attach-visibility"]');

	      if (visibilityCheckbox) {
	        visibilityCheckbox.checked = false;
	      }

	      var captchaWord = this.formNode.querySelector('input[name="captcha_word"]');

	      if (captchaWord) {
	        captchaWord.value = '';
	        var captchaCode = this.formNode.querySelector('input[name="captcha_code"]');
	        var captchaImage = this.formNode.querySelector('img[name="captcha_image"]');
	        BX.ajax.getCaptcha(function (result) {
	          captchaCode.value = result['captcha_sid'];
	          captchaImage.src = '/bitrix/tools/captcha.php?captcha_code=' + result['captcha_sid'];
	        });
	      }

	      var subscribeCheckbox = this.formNode.querySelector('input[name="TOPIC_SUBSCRIBE"]');

	      if (subscribeCheckbox && subscribeCheckbox.checked) {
	        subscribeCheckbox.disabled = true;
	      }
	    }
	  }, {
	    key: "show",
	    value: function show(_ref10) {
	      var _this3 = this;

	      var entity = _ref10.entity,
	          messageId = _ref10.messageId;
	      return new Promise(function (resolve, reject) {
	        if (!_this3.isFormReady({
	          entity: entity,
	          messageId: messageId
	        })) {
	          return reject();
	        }

	        var loaded = !!_this3.getLHE() && !!_this3.getLHE().editorIsLoaded;

	        if (loaded && _this3.currentEntity.entity === entity && _this3.currentEntity.messageId === messageId) {
	          _this3.getLHE().oEditor.Focus();

	          return resolve();
	        }

	        _this3.currentEntity.entity = entity;
	        _this3.currentEntity.messageId = messageId;
	        _this3.container.style.display = 'block';
	        main_core_events.EventEmitter.emit(_this3.currentEntity.entity, 'onForumCommentFormShow', []);
	        main_core_events.EventEmitter.emit(_this3.getLHEEventNode(), 'OnShowLHE', ['show']);

	        if (loaded !== true) {
	          _this3.getLHE().exec(function () {
	            _this3.show({
	              entity: entity,
	              messageId: messageId
	            }).then(resolve, reject);
	          });
	        } else {
	          resolve();
	        }
	      });
	    }
	  }, {
	    key: "getLHE",
	    value: function getLHE() {
	      return LHEPostForm.getHandlerByFormId(this.formId);
	    }
	  }, {
	    key: "getLHEEventNode",
	    value: function getLHEEventNode() {
	      if (!this.handlerEventNode && this.getLHE()) {
	        this.handlerEventNode = this.getLHE().eventNode;
	      }

	      return this.handlerEventNode;
	    }
	  }]);
	  return Form;
	}();

	babelHelpers.defineProperty(Form, "maxMessageLength", 64000);
	babelHelpers.defineProperty(Form, "instances", {});

	var Entity = /*#__PURE__*/function () {
	  function Entity(_ref) {
	    var formId = _ref.formId,
	        container = _ref.container,
	        preorder = _ref.preorder,
	        ajaxPost = _ref.ajaxPost;
	    babelHelpers.classCallCheck(this, Entity);
	    this.formId = formId;
	    this.container = container;
	    this.preorder = preorder === true;
	    this.ajaxPost = ajaxPost === true;
	    this.reply = this.reply.bind(this);
	    this.quote = this.quote.bind(this);
	    this.parseResponse = this.parseResponse.bind(this);
	    this.init();
	  }

	  babelHelpers.createClass(Entity, [{
	    key: "init",
	    value: function init() {
	      var _this = this;

	      this.container.querySelectorAll("[data-bx-role=add-new-message]").forEach(function (node) {
	        node.addEventListener('click', function () {
	          _this.reply({
	            node: null
	          });
	        });
	      });
	      this.bindMessages();
	      this.bindNavigation();
	      main_core_events.EventEmitter.subscribe(this, 'onForumCommentAdded', this.parseResponse);
	      main_core_events.EventEmitter.subscribeOnce(this, 'onForumCommentFormShow', function () {
	        this.container.querySelectorAll("[data-bx-role=add-new-message]").forEach(function (node) {
	          node.parentNode.removeChild(node);
	        });
	      }.bind(this));
	    }
	  }, {
	    key: "bindMessages",
	    value: function bindMessages() {
	      var _this2 = this;

	      this.container.querySelectorAll('table').forEach(function (node) {
	        node.querySelectorAll('a[data-bx-act]').forEach(function (actNode) {
	          var action = actNode.dataset.bxAct;

	          if (action === 'reply') {
	            main_core.Event.bind(actNode, 'click', function (event) {
	              _this2.reply({
	                node: node
	              });
	            });
	          } else if (action === 'quote') {
	            main_core.Event.bind(actNode, 'click', function (event) {
	              _this2.quote({
	                node: node
	              });
	            });
	          } else if (action === 'hide' || action === 'show') {
	            main_core.Event.bind(actNode, 'click', function (event) {
	              _this2.moderate({
	                node: node,
	                action: action,
	                actNode: actNode
	              });

	              event.stopPropagation();
	              event.preventDefault();
	            });
	          } else if (action === 'del') {
	            main_core.Event.bind(actNode, 'click', function (event) {
	              _this2.delete({
	                node: node
	              });

	              event.stopPropagation();
	              event.preventDefault();
	            });
	          }
	        });
	      });
	    }
	  }, {
	    key: "bindNavigation",
	    value: function bindNavigation() {
	      var _this3 = this;

	      if (!this.ajaxPost) {
	        return;
	      }

	      this.container.querySelector('div[data-bx-role=navigation-container-top]').querySelectorAll('a').forEach(function (node) {
	        main_core.Event.bindOnce(node, 'click', function (event) {
	          _this3.navigate({
	            node: node
	          });

	          event.stopPropagation();
	          event.preventDefault();
	        });
	      });
	      this.container.querySelector('div[data-bx-role=navigation-container-bottom]').querySelectorAll('a').forEach(function (node) {
	        main_core.Event.bind(node, 'click', function (event) {
	          _this3.navigate({
	            node: node
	          });

	          event.stopPropagation();
	          event.preventDefault();
	        });
	      });
	    }
	  }, {
	    key: "parseResponse",
	    value: function parseResponse(_ref2) {
	      var data = _ref2.data;
	      main_core.Runtime.html(this.container.querySelector('div[data-bx-role=messages-container]'), data.messages);
	      main_core.Runtime.html(this.container.querySelector('div[data-bx-role=navigation-container-top]'), data.navigationTop);
	      main_core.Runtime.html(this.container.querySelector('div[data-bx-role=navigation-container-bottom]'), data.navigationBottom);
	      setTimeout(function (messageId) {
	        this.bindMessages();
	        this.bindNavigation();

	        if (messageId > 0) {
	          BX.scrollToNode(this.container.querySelector('table[id=message' + messageId + ']'));
	        }
	      }.bind(this), 0, data.messageId);
	    }
	  }, {
	    key: "getPlaceholder",
	    value: function getPlaceholder()
	    /*messageId*/
	    {
	      return this.container.querySelector("[data-bx-role=placeholder]");
	    }
	  }, {
	    key: "navigate",
	    value: function navigate(_ref3) {
	      var node = _ref3.node;
	      return BX.ajax({
	        'method': 'GET',
	        'dataType': 'json',
	        'url': main_core.Uri.addParam(node.href, {
	          ajax: 'y'
	        }),
	        'onsuccess': this.parseResponse
	      });
	    }
	  }, {
	    key: "reply",
	    value: function reply(_ref4) {
	      var node = _ref4.node;
	      var text = node !== null ? "[USER=".concat(node.dataset.bxAuthorId, "]").concat(node.dataset.bxAuthorName, "[/USER],&nbsp;") : '';
	      Form.makeReply(this.formId, {
	        entity: this,
	        messageId: 0,
	        text: text
	      });
	    }
	  }, {
	    key: "quote",
	    value: function quote(_ref5) {
	      var node = _ref5.node;
	      var text = ["[USER=".concat(node.dataset.bxAuthorId, "]").concat(node.dataset.bxAuthorName, "[/USER]<br>"), node.querySelector('div[data-bx-role=text]').innerHTML].join('');
	      Form.makeQuote(this.formId, {
	        entity: this,
	        messageId: 0,
	        text: text
	      });
	    }
	  }, {
	    key: "moderate",
	    value: function moderate(_ref6) {
	      var node = _ref6.node,
	          actNode = _ref6.actNode;
	      main_core.ajax.runComponentAction('bitrix:forum.topic.reviews', actNode.dataset.bxAct + 'Message', {
	        mode: 'class',
	        data: {
	          id: node.dataset.bxMessageId
	        }
	      }).then(function (_ref7) {
	        var data = _ref7.data;
	        actNode.dataset.bxAct = data.APPROVED === 'Y' ? 'hide' : 'show';

	        if (data.APPROVED === 'Y') {
	          node.classList.remove('reviews-post-hidden');
	        } else {
	          node.classList.add('reviews-post-hidden');
	        }
	      });
	    }
	  }, {
	    key: "delete",
	    value: function _delete(_ref8) {
	      var node = _ref8.node;
	      main_core.ajax.runComponentAction('bitrix:forum.topic.reviews', 'deleteMessage', {
	        mode: 'class',
	        data: {
	          id: node.dataset.bxMessageId
	        }
	      }).then(function () {
	        node.parentNode.removeChild(node);
	      });
	    }
	  }]);
	  return Entity;
	}();

	exports.Entity = Entity;
	exports.Form = Form;

}((this.BX.Forum.Reviews = this.BX.Forum.Reviews || {}),BX,BX.Event));
//# sourceMappingURL=script.js.map
