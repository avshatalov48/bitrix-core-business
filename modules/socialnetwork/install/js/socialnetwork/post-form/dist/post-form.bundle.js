/* eslint-disable */
this.BX = this.BX || {};
(function (exports,main_popup,ui_entitySelector,ui_buttons,ui_uploader_core,main_core_events,main_core) {
	'use strict';

	function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _cache = /*#__PURE__*/new WeakMap();
	var _getData = /*#__PURE__*/new WeakSet();
	var PostData = /*#__PURE__*/function () {
	  function PostData(data) {
	    babelHelpers.classCallCheck(this, PostData);
	    _classPrivateMethodInitSpec(this, _getData);
	    _classPrivateFieldInitSpec(this, _cache, {
	      writable: true,
	      value: new main_core.Cache.MemoryCache()
	    });
	    this.setData(data);
	  }
	  babelHelpers.createClass(PostData, [{
	    key: "setData",
	    value: function setData(data) {
	      babelHelpers.classPrivateFieldGet(this, _cache).set('data', data);
	    }
	  }, {
	    key: "setFormData",
	    value: function setFormData(formData) {
	      var currentData = babelHelpers.classPrivateFieldGet(this, _cache).get('data');
	      this.setData(_objectSpread(_objectSpread({}, currentData), formData));
	    }
	  }, {
	    key: "prepareRequestData",
	    value: function prepareRequestData() {
	      return {
	        POST_TITLE: _classPrivateMethodGet(this, _getData, _getData2).call(this, 'title'),
	        POST_MESSAGE: _classPrivateMethodGet(this, _getData, _getData2).call(this, 'message'),
	        DEST_DATA: _classPrivateMethodGet(this, _getData, _getData2).call(this, 'recipients'),
	        UF_BLOG_POST_FILE: _classPrivateMethodGet(this, _getData, _getData2).call(this, 'fileIds'),
	        TAGS: _classPrivateMethodGet(this, _getData, _getData2).call(this, 'tags')
	      };
	    }
	  }, {
	    key: "validateRequestData",
	    value: function validateRequestData() {
	      if (!this.getMessage()) {
	        return main_core.Loc.getMessage('SN_PF_REQUEST_TEXT_VALIDATION_ERROR');
	      }
	      if (!this.getRecipients()) {
	        return main_core.Loc.getMessage('SN_PF_REQUEST_RECIPIENTS_VALIDATION_ERROR');
	      }
	      return '';
	    }
	  }, {
	    key: "getTitle",
	    value: function getTitle() {
	      return main_core.Type.isStringFilled(_classPrivateMethodGet(this, _getData, _getData2).call(this, 'title')) ? _classPrivateMethodGet(this, _getData, _getData2).call(this, 'title') : '';
	    }
	  }, {
	    key: "getMessage",
	    value: function getMessage() {
	      return main_core.Type.isStringFilled(_classPrivateMethodGet(this, _getData, _getData2).call(this, 'message')) ? _classPrivateMethodGet(this, _getData, _getData2).call(this, 'message') : '';
	    }
	  }, {
	    key: "getRecipients",
	    value: function getRecipients() {
	      return main_core.Type.isStringFilled(_classPrivateMethodGet(this, _getData, _getData2).call(this, 'recipients')) ? _classPrivateMethodGet(this, _getData, _getData2).call(this, 'recipients') : '';
	    }
	  }, {
	    key: "setRecipients",
	    value: function setRecipients(recipients) {
	      var currentData = babelHelpers.classPrivateFieldGet(this, _cache).get('data');
	      var newData = {
	        recipients: recipients
	      };
	      this.setData(_objectSpread(_objectSpread({}, currentData), newData));
	    }
	  }, {
	    key: "getAllUsersTitle",
	    value: function getAllUsersTitle() {
	      return _classPrivateMethodGet(this, _getData, _getData2).call(this, 'allUsersTitle');
	    }
	  }, {
	    key: "isAllowEmailInvitation",
	    value: function isAllowEmailInvitation() {
	      return _classPrivateMethodGet(this, _getData, _getData2).call(this, 'allowEmailInvitation') === true;
	    }
	  }, {
	    key: "isAllowToAll",
	    value: function isAllowToAll() {
	      return _classPrivateMethodGet(this, _getData, _getData2).call(this, 'allowToAll') === true;
	    }
	  }]);
	  return PostData;
	}();
	function _getData2(param) {
	  return babelHelpers.classPrivateFieldGet(this, _cache).get('data')[param];
	}

	var _templateObject;
	function _classPrivateMethodInitSpec$1(obj, privateSet) { _checkPrivateRedeclaration$1(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec$1(obj, privateMap, value) { _checkPrivateRedeclaration$1(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$1(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet$1(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _formId = /*#__PURE__*/new WeakMap();
	var _LHEId = /*#__PURE__*/new WeakMap();
	var _isShownPostTitle = /*#__PURE__*/new WeakMap();
	var _LHEPostForm = /*#__PURE__*/new WeakMap();
	var _eventNode = /*#__PURE__*/new WeakMap();
	var _showPostTitleBtn = /*#__PURE__*/new WeakMap();
	var _editor = /*#__PURE__*/new WeakMap();
	var _userFieldControl = /*#__PURE__*/new WeakMap();
	var _blockFileShowEvent = /*#__PURE__*/new WeakMap();
	var _editorInited = /*#__PURE__*/new WeakSet();
	var _addMention = /*#__PURE__*/new WeakSet();
	var _getEntityType = /*#__PURE__*/new WeakSet();
	var _appendButtonShowingPostTitle = /*#__PURE__*/new WeakSet();
	var _toggleVisibilityPostTitle = /*#__PURE__*/new WeakSet();
	var PostFormManager = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(PostFormManager, _EventEmitter);
	  function PostFormManager(params) {
	    var _this;
	    babelHelpers.classCallCheck(this, PostFormManager);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(PostFormManager).call(this));
	    _classPrivateMethodInitSpec$1(babelHelpers.assertThisInitialized(_this), _toggleVisibilityPostTitle);
	    _classPrivateMethodInitSpec$1(babelHelpers.assertThisInitialized(_this), _appendButtonShowingPostTitle);
	    _classPrivateMethodInitSpec$1(babelHelpers.assertThisInitialized(_this), _getEntityType);
	    _classPrivateMethodInitSpec$1(babelHelpers.assertThisInitialized(_this), _addMention);
	    _classPrivateMethodInitSpec$1(babelHelpers.assertThisInitialized(_this), _editorInited);
	    _classPrivateFieldInitSpec$1(babelHelpers.assertThisInitialized(_this), _formId, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$1(babelHelpers.assertThisInitialized(_this), _LHEId, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$1(babelHelpers.assertThisInitialized(_this), _isShownPostTitle, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$1(babelHelpers.assertThisInitialized(_this), _LHEPostForm, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$1(babelHelpers.assertThisInitialized(_this), _eventNode, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$1(babelHelpers.assertThisInitialized(_this), _showPostTitleBtn, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$1(babelHelpers.assertThisInitialized(_this), _editor, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$1(babelHelpers.assertThisInitialized(_this), _userFieldControl, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$1(babelHelpers.assertThisInitialized(_this), _blockFileShowEvent, {
	      writable: true,
	      value: false
	    });
	    _this.setEventNamespace('BX.Socialnetwork.PostFormManager');
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _formId, params.formId);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _LHEId, params.LHEId);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _isShownPostTitle, params.isShownPostTitle === true);
	    main_core_events.EventEmitter.subscribe('OnEditorInitedAfter', function (event) {
	      var _event$getData = event.getData(),
	        _event$getData2 = babelHelpers.slicedToArray(_event$getData, 1),
	        editor = _event$getData2[0];
	      _classPrivateMethodGet$1(babelHelpers.assertThisInitialized(_this), _editorInited, _editorInited2).call(babelHelpers.assertThisInitialized(_this), editor);
	    });
	    main_core_events.EventEmitter.subscribe('onMentionAdd', _classPrivateMethodGet$1(babelHelpers.assertThisInitialized(_this), _addMention, _addMention2).bind(babelHelpers.assertThisInitialized(_this)));
	    return _this;
	  }
	  babelHelpers.createClass(PostFormManager, [{
	    key: "initLHE",
	    value: function initLHE() {
	      var _this2 = this;
	      if (!window.LHEPostForm) {
	        throw new Error('BX.Socialnetwork.PostFormManager: LHEPostForm not found');
	      }
	      babelHelpers.classPrivateFieldSet(this, _LHEPostForm, window.LHEPostForm);
	      var handler = babelHelpers.classPrivateFieldGet(this, _LHEPostForm).getHandler(babelHelpers.classPrivateFieldGet(this, _LHEId));
	      babelHelpers.classPrivateFieldSet(this, _eventNode, handler.eventNode);
	      main_core_events.EventEmitter.emit(babelHelpers.classPrivateFieldGet(this, _eventNode), 'OnShowLHE', ['show']);
	      _classPrivateMethodGet$1(this, _appendButtonShowingPostTitle, _appendButtonShowingPostTitle2).call(this);
	      babelHelpers.classPrivateFieldSet(this, _userFieldControl, BX.Disk.Uploader.UserFieldControl.getById(babelHelpers.classPrivateFieldGet(this, _formId)));
	      main_core_events.EventEmitter.subscribe(babelHelpers.classPrivateFieldGet(this, _eventNode), 'onShowControllers', function (_ref) {
	        var data = _ref.data;
	        if (babelHelpers.classPrivateFieldGet(_this2, _blockFileShowEvent) === false && data.toString() === 'show') {
	          setTimeout(function () {
	            _this2.emit('showControllers');
	          }, 100);
	        }
	        babelHelpers.classPrivateFieldSet(_this2, _blockFileShowEvent, false);
	      });
	      main_core_events.EventEmitter.subscribe(babelHelpers.classPrivateFieldGet(this, _eventNode), 'onShowControllers:File:Increment', function (_ref2) {
	        var data = _ref2.data;
	        babelHelpers.classPrivateFieldSet(_this2, _blockFileShowEvent, true);
	      });
	    }
	  }, {
	    key: "getEditorText",
	    value: function getEditorText() {
	      return babelHelpers.classPrivateFieldGet(this, _editor).GetContent();
	    }
	  }, {
	    key: "clearEditorText",
	    value: function clearEditorText() {
	      var _this3 = this;
	      main_core_events.EventEmitter.subscribeOnce(babelHelpers.classPrivateFieldGet(this, _editor), 'OnSetContentAfter', function () {
	        babelHelpers.classPrivateFieldGet(_this3, _editor).ResizeSceleton();
	      });
	      babelHelpers.classPrivateFieldGet(this, _editor).SetContent('');
	    }
	  }, {
	    key: "focusToEditor",
	    value: function focusToEditor() {
	      if (babelHelpers.classPrivateFieldGet(this, _editor)) {
	        babelHelpers.classPrivateFieldGet(this, _editor).Focus();
	      }
	    }
	  }]);
	  return PostFormManager;
	}(main_core_events.EventEmitter);
	function _editorInited2(editor) {
	  var _this4 = this;
	  if (editor.id === babelHelpers.classPrivateFieldGet(this, _LHEId)) {
	    babelHelpers.classPrivateFieldSet(this, _editor, editor);
	    this.emit('editorInited');
	    main_core_events.EventEmitter.subscribe(editor, 'OnFullscreenExpand', function () {
	      _this4.emit('fullscreenExpand');
	    });
	  }
	}
	function _addMention2(baseEvent) {
	  var _baseEvent$getCompatD = baseEvent.getCompatData(),
	    _baseEvent$getCompatD2 = babelHelpers.slicedToArray(_baseEvent$getCompatD, 2),
	    entity = _baseEvent$getCompatD2[0],
	    type = _baseEvent$getCompatD2[1];
	  var entityType = _classPrivateMethodGet$1(this, _getEntityType, _getEntityType2).call(this, type, entity);
	  this.emit('addMention', {
	    type: type,
	    entity: entity,
	    entityType: entityType
	  });
	}
	function _getEntityType2(type, entity) {
	  var entityType = '';
	  if (type === 'user') {
	    if (entity.isExtranet === 'Y') {
	      entityType = 'extranet';
	    } else if (entity.isEmail === 'Y') {
	      entityType = 'email';
	    } else {
	      entityType = 'employee';
	    }
	  } else if (type === 'project') {
	    if (entity.isExtranet === 'Y') {
	      entityType = 'extranet';
	    }
	  }
	  return entityType;
	}
	function _appendButtonShowingPostTitle2() {
	  var activeClass = babelHelpers.classPrivateFieldGet(this, _isShownPostTitle) ? 'feed-add-post-form-btn-active' : '';
	  babelHelpers.classPrivateFieldSet(this, _showPostTitleBtn, main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div\n\t\t\t\tdata-id=\"sn-post-form-manager-show-title-btn\"\n\t\t\t\tclass=\"feed-add-post-form-title-btn ", "\"\n\t\t\t\ttitle=\"", "\"\n\t\t\t>\n\t\t\t</div>\n\t\t"])), activeClass, main_core.Loc.getMessage('SN_PF_TITLE_PLACEHOLDER')));
	  main_core.Event.bind(babelHelpers.classPrivateFieldGet(this, _showPostTitleBtn), 'click', _classPrivateMethodGet$1(this, _toggleVisibilityPostTitle, _toggleVisibilityPostTitle2).bind(this));
	  var containerWithAdditionalButtons = babelHelpers.classPrivateFieldGet(this, _eventNode).querySelector('.feed-add-post-form-but-more-open');
	  main_core.Dom.append(babelHelpers.classPrivateFieldGet(this, _showPostTitleBtn), containerWithAdditionalButtons);
	}
	function _toggleVisibilityPostTitle2() {
	  this.emit('toggleVisibilityPostTitle');
	  babelHelpers.classPrivateFieldSet(this, _isShownPostTitle, !babelHelpers.classPrivateFieldGet(this, _isShownPostTitle));
	  main_core.Dom.toggleClass(babelHelpers.classPrivateFieldGet(this, _showPostTitleBtn), 'feed-add-post-form-btn-active');
	}

	function _classPrivateFieldInitSpec$2(obj, privateMap, value) { _checkPrivateRedeclaration$2(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$2(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	var _pathToDefaultRedirect = /*#__PURE__*/new WeakMap();
	var _pathToGroupRedirect = /*#__PURE__*/new WeakMap();
	var PostFormRouter = /*#__PURE__*/function () {
	  function PostFormRouter(params) {
	    babelHelpers.classCallCheck(this, PostFormRouter);
	    _classPrivateFieldInitSpec$2(this, _pathToDefaultRedirect, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$2(this, _pathToGroupRedirect, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldSet(this, _pathToDefaultRedirect, main_core.Type.isString(params.pathToDefaultRedirect) ? params.pathToDefaultRedirect : '');
	    babelHelpers.classPrivateFieldSet(this, _pathToGroupRedirect, main_core.Type.isString(params.pathToGroupRedirect) ? params.pathToGroupRedirect : '');
	  }
	  babelHelpers.createClass(PostFormRouter, [{
	    key: "redirectTo",
	    value: function redirectTo(groupId) {
	      if (groupId) {
	        if (babelHelpers.classPrivateFieldGet(this, _pathToGroupRedirect)) {
	          top.BX.Socialnetwork.Spaces.space.reloadPageContent(babelHelpers.classPrivateFieldGet(this, _pathToGroupRedirect).replace('#group_id#', groupId));
	        } else {
	          top.BX.Socialnetwork.Spaces.space.reloadPageContent();
	        }
	      } else {
	        // eslint-disable-next-line no-lonely-if
	        if (babelHelpers.classPrivateFieldGet(this, _pathToDefaultRedirect)) {
	          top.BX.Socialnetwork.Spaces.space.reloadPageContent(babelHelpers.classPrivateFieldGet(this, _pathToDefaultRedirect));
	        } else {
	          top.BX.Socialnetwork.Spaces.space.reloadPageContent();
	        }
	      }
	    }
	  }]);
	  return PostFormRouter;
	}();

	function _classPrivateMethodInitSpec$2(obj, privateSet) { _checkPrivateRedeclaration$3(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec$3(obj, privateMap, value) { _checkPrivateRedeclaration$3(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$3(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet$2(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _formId$1 = /*#__PURE__*/new WeakMap();
	var _form = /*#__PURE__*/new WeakMap();
	var _getInput = /*#__PURE__*/new WeakSet();
	var _getContainer = /*#__PURE__*/new WeakSet();
	var _hideContainer = /*#__PURE__*/new WeakSet();
	var PostFormTags = /*#__PURE__*/function () {
	  function PostFormTags(formId, form) {
	    babelHelpers.classCallCheck(this, PostFormTags);
	    _classPrivateMethodInitSpec$2(this, _hideContainer);
	    _classPrivateMethodInitSpec$2(this, _getContainer);
	    _classPrivateMethodInitSpec$2(this, _getInput);
	    _classPrivateFieldInitSpec$3(this, _formId$1, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$3(this, _form, {
	      writable: true,
	      value: void 0
	    });
	    if (!main_core.Type.isString(formId) || !formId) {
	      throw new Error('BX.Socialnetwork.PostFormTags: formId not found');
	    }
	    if (!main_core.Type.isDomNode(form)) {
	      throw new Error('BX.Socialnetwork.PostFormTags: form not found');
	    }
	    babelHelpers.classPrivateFieldSet(this, _formId$1, formId);
	    babelHelpers.classPrivateFieldSet(this, _form, form);
	  }
	  babelHelpers.createClass(PostFormTags, [{
	    key: "isFilled",
	    value: function isFilled() {
	      var input = _classPrivateMethodGet$2(this, _getInput, _getInput2).call(this);
	      return main_core.Type.isDomNode(input) && input.value;
	    }
	  }, {
	    key: "getValue",
	    value: function getValue() {
	      var input = _classPrivateMethodGet$2(this, _getInput, _getInput2).call(this);
	      if (!main_core.Type.isDomNode(input)) {
	        return '';
	      }
	      return input.value;
	    }
	  }, {
	    key: "clear",
	    value: function clear() {
	      _classPrivateMethodGet$2(this, _getContainer, _getContainer2).call(this).querySelectorAll('.feed-add-post-del-but').forEach(function (tag) {
	        tag.click();
	      });
	      _classPrivateMethodGet$2(this, _hideContainer, _hideContainer2).call(this);
	    }
	  }]);
	  return PostFormTags;
	}();
	function _getInput2() {
	  return _classPrivateMethodGet$2(this, _getContainer, _getContainer2).call(this).querySelector('input[name="TAGS"]');
	}
	function _getContainer2() {
	  return babelHelpers.classPrivateFieldGet(this, _form).querySelector("#post-tags-block-".concat(babelHelpers.classPrivateFieldGet(this, _formId$1)));
	}
	function _hideContainer2() {
	  main_core.Dom.style(_classPrivateMethodGet$2(this, _getContainer, _getContainer2).call(this), 'display', 'none');
	}

	var _templateObject$1, _templateObject2, _templateObject3, _templateObject4;
	function _classPrivateMethodInitSpec$3(obj, privateSet) { _checkPrivateRedeclaration$4(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec$4(obj, privateMap, value) { _checkPrivateRedeclaration$4(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$4(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet$3(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var UserOptions = main_core.Reflection.namespace('BX.userOptions');
	var NotificationCenter = main_core.Reflection.namespace('BX.UI.Notification.Center');
	var _postId = /*#__PURE__*/new WeakMap();
	var _groupId = /*#__PURE__*/new WeakMap();
	var _isShownPostTitle$1 = /*#__PURE__*/new WeakMap();
	var _initData = /*#__PURE__*/new WeakMap();
	var _formId$2 = /*#__PURE__*/new WeakMap();
	var _jsObjName = /*#__PURE__*/new WeakMap();
	var _LHEId$1 = /*#__PURE__*/new WeakMap();
	var _sended = /*#__PURE__*/new WeakMap();
	var _editMode = /*#__PURE__*/new WeakMap();
	var _popup = /*#__PURE__*/new WeakMap();
	var _sendBtn = /*#__PURE__*/new WeakMap();
	var _postData = /*#__PURE__*/new WeakMap();
	var _postFormManager = /*#__PURE__*/new WeakMap();
	var _postFormRouter = /*#__PURE__*/new WeakMap();
	var _postFormTags = /*#__PURE__*/new WeakMap();
	var _node = /*#__PURE__*/new WeakMap();
	var _titleNode = /*#__PURE__*/new WeakMap();
	var _recipientSelector = /*#__PURE__*/new WeakMap();
	var _errorLayout = /*#__PURE__*/new WeakMap();
	var _selector = /*#__PURE__*/new WeakMap();
	var _init = /*#__PURE__*/new WeakSet();
	var _createPopup = /*#__PURE__*/new WeakSet();
	var _firstShow = /*#__PURE__*/new WeakSet();
	var _onAfterShow = /*#__PURE__*/new WeakSet();
	var _afterClose = /*#__PURE__*/new WeakSet();
	var _sendForm = /*#__PURE__*/new WeakSet();
	var _clearForm = /*#__PURE__*/new WeakSet();
	var _collectFormData = /*#__PURE__*/new WeakSet();
	var _clearFiles = /*#__PURE__*/new WeakSet();
	var _showError = /*#__PURE__*/new WeakSet();
	var _hideError = /*#__PURE__*/new WeakSet();
	var _renderMainPostForm = /*#__PURE__*/new WeakSet();
	var _renderForm = /*#__PURE__*/new WeakSet();
	var _renderErrorAlert = /*#__PURE__*/new WeakSet();
	var _renderRecipientSelector = /*#__PURE__*/new WeakSet();
	var _initRecipientSelector = /*#__PURE__*/new WeakSet();
	var _clearSelector = /*#__PURE__*/new WeakSet();
	var _initTagsSelector = /*#__PURE__*/new WeakSet();
	var _changeSelectedRecipients = /*#__PURE__*/new WeakSet();
	var _renderTitle = /*#__PURE__*/new WeakSet();
	var _afterEditorInit = /*#__PURE__*/new WeakSet();
	var _toggleVisibilityPostTitle$1 = /*#__PURE__*/new WeakSet();
	var _changePostFormPosition = /*#__PURE__*/new WeakSet();
	var _addMention$1 = /*#__PURE__*/new WeakSet();
	var _showControllers = /*#__PURE__*/new WeakSet();
	var _consoleError = /*#__PURE__*/new WeakSet();
	var PostForm = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(PostForm, _EventEmitter);
	  function PostForm(params) {
	    var _this;
	    babelHelpers.classCallCheck(this, PostForm);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(PostForm).call(this));
	    _classPrivateMethodInitSpec$3(babelHelpers.assertThisInitialized(_this), _consoleError);
	    _classPrivateMethodInitSpec$3(babelHelpers.assertThisInitialized(_this), _showControllers);
	    _classPrivateMethodInitSpec$3(babelHelpers.assertThisInitialized(_this), _addMention$1);
	    _classPrivateMethodInitSpec$3(babelHelpers.assertThisInitialized(_this), _changePostFormPosition);
	    _classPrivateMethodInitSpec$3(babelHelpers.assertThisInitialized(_this), _toggleVisibilityPostTitle$1);
	    _classPrivateMethodInitSpec$3(babelHelpers.assertThisInitialized(_this), _afterEditorInit);
	    _classPrivateMethodInitSpec$3(babelHelpers.assertThisInitialized(_this), _renderTitle);
	    _classPrivateMethodInitSpec$3(babelHelpers.assertThisInitialized(_this), _changeSelectedRecipients);
	    _classPrivateMethodInitSpec$3(babelHelpers.assertThisInitialized(_this), _initTagsSelector);
	    _classPrivateMethodInitSpec$3(babelHelpers.assertThisInitialized(_this), _clearSelector);
	    _classPrivateMethodInitSpec$3(babelHelpers.assertThisInitialized(_this), _initRecipientSelector);
	    _classPrivateMethodInitSpec$3(babelHelpers.assertThisInitialized(_this), _renderRecipientSelector);
	    _classPrivateMethodInitSpec$3(babelHelpers.assertThisInitialized(_this), _renderErrorAlert);
	    _classPrivateMethodInitSpec$3(babelHelpers.assertThisInitialized(_this), _renderForm);
	    _classPrivateMethodInitSpec$3(babelHelpers.assertThisInitialized(_this), _renderMainPostForm);
	    _classPrivateMethodInitSpec$3(babelHelpers.assertThisInitialized(_this), _hideError);
	    _classPrivateMethodInitSpec$3(babelHelpers.assertThisInitialized(_this), _showError);
	    _classPrivateMethodInitSpec$3(babelHelpers.assertThisInitialized(_this), _clearFiles);
	    _classPrivateMethodInitSpec$3(babelHelpers.assertThisInitialized(_this), _collectFormData);
	    _classPrivateMethodInitSpec$3(babelHelpers.assertThisInitialized(_this), _clearForm);
	    _classPrivateMethodInitSpec$3(babelHelpers.assertThisInitialized(_this), _sendForm);
	    _classPrivateMethodInitSpec$3(babelHelpers.assertThisInitialized(_this), _afterClose);
	    _classPrivateMethodInitSpec$3(babelHelpers.assertThisInitialized(_this), _onAfterShow);
	    _classPrivateMethodInitSpec$3(babelHelpers.assertThisInitialized(_this), _firstShow);
	    _classPrivateMethodInitSpec$3(babelHelpers.assertThisInitialized(_this), _createPopup);
	    _classPrivateMethodInitSpec$3(babelHelpers.assertThisInitialized(_this), _init);
	    _classPrivateFieldInitSpec$4(babelHelpers.assertThisInitialized(_this), _postId, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$4(babelHelpers.assertThisInitialized(_this), _groupId, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$4(babelHelpers.assertThisInitialized(_this), _isShownPostTitle$1, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$4(babelHelpers.assertThisInitialized(_this), _initData, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$4(babelHelpers.assertThisInitialized(_this), _formId$2, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$4(babelHelpers.assertThisInitialized(_this), _jsObjName, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$4(babelHelpers.assertThisInitialized(_this), _LHEId$1, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$4(babelHelpers.assertThisInitialized(_this), _sended, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$4(babelHelpers.assertThisInitialized(_this), _editMode, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$4(babelHelpers.assertThisInitialized(_this), _popup, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$4(babelHelpers.assertThisInitialized(_this), _sendBtn, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$4(babelHelpers.assertThisInitialized(_this), _postData, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$4(babelHelpers.assertThisInitialized(_this), _postFormManager, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$4(babelHelpers.assertThisInitialized(_this), _postFormRouter, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$4(babelHelpers.assertThisInitialized(_this), _postFormTags, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$4(babelHelpers.assertThisInitialized(_this), _node, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$4(babelHelpers.assertThisInitialized(_this), _titleNode, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$4(babelHelpers.assertThisInitialized(_this), _recipientSelector, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$4(babelHelpers.assertThisInitialized(_this), _errorLayout, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$4(babelHelpers.assertThisInitialized(_this), _selector, {
	      writable: true,
	      value: void 0
	    });
	    _this.setEventNamespace('BX.Socialnetwork.PostForm');
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _postId, main_core.Type.isInteger(parseInt(params.postId, 10)) ? parseInt(params.postId, 10) : 0);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _groupId, main_core.Type.isInteger(parseInt(params.groupId, 10)) ? parseInt(params.groupId, 10) : 0);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _formId$2, "blogPostForm_".concat(main_core.Text.getRandom().toLowerCase()));
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _jsObjName, "oPostFormLHE_blogPostForm".concat(babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _formId$2)));
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _LHEId$1, "idPostFormLHE_".concat(babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _formId$2)));
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _sended, false);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _editMode, babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _postId) > 0);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _postFormRouter, new PostFormRouter({
	      pathToDefaultRedirect: params.pathToDefaultRedirect,
	      pathToGroupRedirect: params.pathToGroupRedirect
	    }));
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _errorLayout, {});
	    return _this;
	  }
	  babelHelpers.createClass(PostForm, [{
	    key: "show",
	    value: function show() {
	      var _this2 = this;
	      if (babelHelpers.classPrivateFieldGet(this, _popup)) {
	        return new Promise(function (resolve, reject) {
	          babelHelpers.classPrivateFieldGet(_this2, _popup).subscribeOnce('onShow', function () {
	            resolve();
	          });
	          babelHelpers.classPrivateFieldGet(_this2, _popup).show();
	        });
	      }
	      return new Promise(function (resolve, reject) {
	        _classPrivateMethodGet$3(_this2, _init, _init2).call(_this2).then(function () {
	          _classPrivateMethodGet$3(_this2, _createPopup, _createPopup2).call(_this2);
	          babelHelpers.classPrivateFieldGet(_this2, _popup).subscribeOnce('onShow', function () {
	            resolve();
	          });
	          babelHelpers.classPrivateFieldGet(_this2, _popup).show();
	        })["catch"](function () {
	          return reject();
	        });
	      });
	    }
	  }]);
	  return PostForm;
	}(main_core_events.EventEmitter);
	function _init2() {
	  var _this3 = this;
	  return main_core.ajax.runAction('socialnetwork.api.livefeed.blogpost.getPostFormInitData', {
	    data: {
	      postId: babelHelpers.classPrivateFieldGet(this, _postId),
	      groupId: babelHelpers.classPrivateFieldGet(this, _groupId)
	    }
	  }).then(function (response) {
	    babelHelpers.classPrivateFieldSet(_this3, _initData, response.data);
	    babelHelpers.classPrivateFieldSet(_this3, _postData, new PostData(babelHelpers.classPrivateFieldGet(_this3, _initData)));
	    babelHelpers.classPrivateFieldSet(_this3, _isShownPostTitle$1, babelHelpers.classPrivateFieldGet(_this3, _initData).isShownPostTitle === 'Y');
	    babelHelpers.classPrivateFieldSet(_this3, _postFormManager, new PostFormManager({
	      formId: babelHelpers.classPrivateFieldGet(_this3, _formId$2),
	      LHEId: babelHelpers.classPrivateFieldGet(_this3, _LHEId$1),
	      isShownPostTitle: babelHelpers.classPrivateFieldGet(_this3, _isShownPostTitle$1)
	    }));
	    babelHelpers.classPrivateFieldGet(_this3, _postFormManager).subscribe('editorInited', _classPrivateMethodGet$3(_this3, _afterEditorInit, _afterEditorInit2).bind(_this3));
	    babelHelpers.classPrivateFieldGet(_this3, _postFormManager).subscribe('toggleVisibilityPostTitle', _classPrivateMethodGet$3(_this3, _toggleVisibilityPostTitle$1, _toggleVisibilityPostTitle2$1).bind(_this3));
	    babelHelpers.classPrivateFieldGet(_this3, _postFormManager).subscribe('fullscreenExpand', _classPrivateMethodGet$3(_this3, _changePostFormPosition, _changePostFormPosition2).bind(_this3));
	    babelHelpers.classPrivateFieldGet(_this3, _postFormManager).subscribe('addMention', _classPrivateMethodGet$3(_this3, _addMention$1, _addMention2$1).bind(_this3));
	    babelHelpers.classPrivateFieldGet(_this3, _postFormManager).subscribe('showControllers', _classPrivateMethodGet$3(_this3, _showControllers, _showControllers2).bind(_this3));
	    return _this3;
	  })["catch"](function (error) {
	    _classPrivateMethodGet$3(_this3, _consoleError, _consoleError2).call(_this3, 'init', error);
	  });
	}
	function _createPopup2() {
	  var _this4 = this;
	  babelHelpers.classPrivateFieldSet(this, _popup, new main_popup.Popup({
	    id: babelHelpers.classPrivateFieldGet(this, _formId$2),
	    className: 'sn-post-form-popup --normal',
	    content: _classPrivateMethodGet$3(this, _renderForm, _renderForm2).call(this),
	    contentNoPaddings: true,
	    minHeight: 370,
	    width: 720,
	    disableScroll: true,
	    draggable: false,
	    overlay: true,
	    padding: 0,
	    buttons: [babelHelpers.classPrivateFieldSet(this, _sendBtn, new ui_buttons.Button({
	      text: main_core.Loc.getMessage('SN_PF_SEND_BTN'),
	      color: ui_buttons.ButtonColor.PRIMARY,
	      onclick: function onclick() {
	        _classPrivateMethodGet$3(_this4, _sendForm, _sendForm2).call(_this4);
	      }
	    })), new ui_buttons.Button({
	      text: main_core.Loc.getMessage('SN_PF_CANCEL_BTN'),
	      color: ui_buttons.ButtonColor.LINK,
	      onclick: function onclick() {
	        babelHelpers.classPrivateFieldGet(_this4, _popup).close();
	      }
	    })],
	    events: {
	      onFirstShow: _classPrivateMethodGet$3(this, _firstShow, _firstShow2).bind(this),
	      onAfterShow: _classPrivateMethodGet$3(this, _onAfterShow, _onAfterShow2).bind(this),
	      onAfterClose: _classPrivateMethodGet$3(this, _afterClose, _afterClose2).bind(this)
	    }
	  }));
	}
	function _firstShow2() {
	  var _this5 = this;
	  babelHelpers.classPrivateFieldGet(this, _sendBtn).setWaiting(true);
	  _classPrivateMethodGet$3(this, _initRecipientSelector, _initRecipientSelector2).call(this);

	  // eslint-disable-next-line promise/catch-or-return
	  _classPrivateMethodGet$3(this, _renderMainPostForm, _renderMainPostForm2).call(this).then(function (runtimePromise) {
	    // eslint-disable-next-line promise/catch-or-return,promise/no-nesting
	    runtimePromise.then(function () {
	      babelHelpers.classPrivateFieldGet(_this5, _postFormManager).initLHE();
	    });
	  });
	}
	function _onAfterShow2() {
	  _classPrivateMethodGet$3(this, _initTagsSelector, _initTagsSelector2).call(this);
	  babelHelpers.classPrivateFieldGet(this, _postFormManager).focusToEditor();
	}
	function _afterClose2() {
	  if (babelHelpers.classPrivateFieldGet(this, _sended)) {
	    _classPrivateMethodGet$3(this, _clearForm, _clearForm2).call(this);
	    if (BX.Livefeed && BX.Livefeed.PageInstance) {
	      BX.Livefeed.PageInstance.refresh();
	    } else {
	      babelHelpers.classPrivateFieldGet(this, _postFormRouter).redirectTo(babelHelpers.classPrivateFieldGet(this, _groupId));
	    }
	  }
	}
	function _sendForm2() {
	  var _this6 = this;
	  if (babelHelpers.classPrivateFieldGet(this, _sendBtn).isWaiting()) {
	    return;
	  }
	  _classPrivateMethodGet$3(this, _hideError, _hideError2).call(this);
	  babelHelpers.classPrivateFieldGet(this, _postData).setFormData(_classPrivateMethodGet$3(this, _collectFormData, _collectFormData2).call(this));
	  var errorMessage = babelHelpers.classPrivateFieldGet(this, _postData).validateRequestData();
	  if (errorMessage) {
	    _classPrivateMethodGet$3(this, _showError, _showError2).call(this, errorMessage);
	    babelHelpers.classPrivateFieldGet(this, _postFormManager).focusToEditor();
	    return;
	  }
	  babelHelpers.classPrivateFieldGet(this, _sendBtn).setWaiting(true);
	  var action = "socialnetwork.api.livefeed.blogpost.".concat(babelHelpers.classPrivateFieldGet(this, _postId) ? 'update' : 'add');
	  var data = babelHelpers.classPrivateFieldGet(this, _postId) ? {
	    id: babelHelpers.classPrivateFieldGet(this, _postId),
	    params: babelHelpers.classPrivateFieldGet(this, _postData).prepareRequestData()
	  } : {
	    params: babelHelpers.classPrivateFieldGet(this, _postData).prepareRequestData()
	  };
	  main_core.ajax.runAction(action, {
	    data: data,
	    analyticsLabel: {
	      b24statAction: 'addLogEntry',
	      b24statContext: 'spaces'
	    }
	  }).then(function (response) {
	    babelHelpers.classPrivateFieldSet(_this6, _sended, true);
	    babelHelpers.classPrivateFieldGet(_this6, _popup).close();
	  })["catch"](function (error) {
	    babelHelpers.classPrivateFieldGet(_this6, _sendBtn).setWaiting(false);
	    _classPrivateMethodGet$3(_this6, _consoleError, _consoleError2).call(_this6, 'sendForm', error);
	  });
	}
	function _clearForm2() {
	  babelHelpers.classPrivateFieldGet(this, _postData).setData(babelHelpers.classPrivateFieldGet(this, _initData));
	  _classPrivateMethodGet$3(this, _clearSelector, _clearSelector2).call(this);
	  babelHelpers.classPrivateFieldGet(this, _titleNode).querySelector('input').value = '';
	  babelHelpers.classPrivateFieldGet(this, _postFormManager).clearEditorText();
	  _classPrivateMethodGet$3(this, _clearFiles, _clearFiles2).call(this);
	  babelHelpers.classPrivateFieldGet(this, _postFormTags).clear();
	  babelHelpers.classPrivateFieldSet(this, _sended, false);
	  babelHelpers.classPrivateFieldGet(this, _sendBtn).setWaiting(false);
	}
	function _collectFormData2() {
	  var postFormData = {
	    title: babelHelpers.classPrivateFieldGet(this, _titleNode).querySelector('input').value,
	    message: babelHelpers.classPrivateFieldGet(this, _postFormManager).getEditorText()
	  };
	  postFormData.recipients = babelHelpers.classPrivateFieldGet(this, _postData).getRecipients();
	  var fileIds = [];
	  var userFieldControl = BX.Disk.Uploader.UserFieldControl.getById(babelHelpers.classPrivateFieldGet(this, _formId$2));
	  userFieldControl.getFiles().forEach(function (file) {
	    if (file.getServerFileId() !== null) {
	      fileIds.push(file.getServerFileId());
	    }
	  });
	  postFormData.fileIds = fileIds;
	  if (babelHelpers.classPrivateFieldGet(this, _postFormTags).isFilled()) {
	    postFormData.tags = babelHelpers.classPrivateFieldGet(this, _postFormTags).getValue();
	  }
	  return postFormData;
	}
	function _clearFiles2() {
	  var userFieldControl = BX.Disk.Uploader.UserFieldControl.getById(babelHelpers.classPrivateFieldGet(this, _formId$2));
	  userFieldControl.clear();
	  userFieldControl.hide();
	}
	function _showError2(message) {
	  main_core.Dom.removeClass(babelHelpers.classPrivateFieldGet(this, _errorLayout).container, '--hidden');
	  babelHelpers.classPrivateFieldGet(this, _errorLayout).message.textContent = main_core.Text.encode(message);
	}
	function _hideError2() {
	  main_core.Dom.addClass(babelHelpers.classPrivateFieldGet(this, _errorLayout).container, '--hidden');
	  babelHelpers.classPrivateFieldGet(this, _errorLayout).message.textContent = '';
	}
	function _renderMainPostForm2() {
	  var _this7 = this;
	  return main_core.ajax.runAction('socialnetwork.api.livefeed.blogpost.getMainPostForm', {
	    data: {
	      params: {
	        formId: babelHelpers.classPrivateFieldGet(this, _formId$2),
	        jsObjName: babelHelpers.classPrivateFieldGet(this, _jsObjName),
	        LHEId: babelHelpers.classPrivateFieldGet(this, _LHEId$1),
	        postId: babelHelpers.classPrivateFieldGet(this, _postId),
	        text: babelHelpers.classPrivateFieldGet(this, _postData).getMessage()
	      }
	    }
	  }).then(function (response) {
	    return main_core.Runtime.html(babelHelpers.classPrivateFieldGet(_this7, _node).querySelector('#sn-post-form'), response.data.html, {
	      htmlFirst: true
	    });
	  })["catch"](function (error) {
	    _classPrivateMethodGet$3(_this7, _consoleError, _consoleError2).call(_this7, 'afterShow', error);
	  });
	}
	function _renderForm2() {
	  babelHelpers.classPrivateFieldSet(this, _node, main_core.Tag.render(_templateObject$1 || (_templateObject$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"sn-post-form__discussion\">\n\t\t\t\t", "\n\t\t\t\t", "\n\t\t\t\t", "\n\t\t\t\t<div id=\"sn-post-form\"></div>\n\t\t\t</div>\n\t\t"])), _classPrivateMethodGet$3(this, _renderErrorAlert, _renderErrorAlert2).call(this), _classPrivateMethodGet$3(this, _renderRecipientSelector, _renderRecipientSelector2).call(this), _classPrivateMethodGet$3(this, _renderTitle, _renderTitle2).call(this)));
	  return babelHelpers.classPrivateFieldGet(this, _node);
	}
	function _renderErrorAlert2() {
	  var _ref = main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div\n\t\t\t\tclass=\"sn-post-form__discussion-error-alert ui-alert ui-alert-danger --hidden\"\n\t\t\t\tref=\"container\"\n\t\t\t>\n\t\t\t\t<span class=\"ui-alert-message\" ref=\"message\"></span>\n\t\t\t</div>\n\t\t"]))),
	    container = _ref.container,
	    message = _ref.message;
	  babelHelpers.classPrivateFieldGet(this, _errorLayout).container = container;
	  babelHelpers.classPrivateFieldGet(this, _errorLayout).message = message;
	  return container;
	}
	function _renderRecipientSelector2() {
	  babelHelpers.classPrivateFieldSet(this, _recipientSelector, main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"sn-post-form__discussion-row\"></div>\n\t\t"]))));
	  return babelHelpers.classPrivateFieldGet(this, _recipientSelector);
	}
	function _initRecipientSelector2() {
	  var _this8 = this;
	  var selectorId = 'sn-post-form-recipient-selector';
	  babelHelpers.classPrivateFieldSet(this, _selector, new ui_entitySelector.TagSelector({
	    id: selectorId,
	    dialogOptions: {
	      id: selectorId,
	      context: 'PostForm',
	      preselectedItems: main_core.Type.isStringFilled(babelHelpers.classPrivateFieldGet(this, _postData).getRecipients()) ? JSON.parse(babelHelpers.classPrivateFieldGet(this, _postData).getRecipients()) : [],
	      entities: [{
	        id: 'meta-user',
	        options: {
	          'all-users': {
	            title: babelHelpers.classPrivateFieldGet(this, _postData).getAllUsersTitle(),
	            allowView: babelHelpers.classPrivateFieldGet(this, _postData).isAllowToAll()
	          }
	        }
	      }, {
	        id: 'user',
	        options: {
	          emailUsers: babelHelpers.classPrivateFieldGet(this, _postData).isAllowEmailInvitation(),
	          inviteGuestLink: babelHelpers.classPrivateFieldGet(this, _postData).isAllowEmailInvitation(),
	          myEmailUsers: true
	        }
	      }, {
	        id: 'project',
	        options: {
	          features: {
	            blog: ['premoderate_post', 'moderate_post', 'write_post', 'full_post']
	          }
	        }
	      }, {
	        id: 'department',
	        options: {
	          selectMode: 'usersAndDepartments',
	          allowFlatDepartments: false
	        }
	      }],
	      events: {
	        'Item:onSelect': function ItemOnSelect() {
	          _classPrivateMethodGet$3(_this8, _changeSelectedRecipients, _changeSelectedRecipients2).call(_this8, babelHelpers.classPrivateFieldGet(_this8, _selector).getDialog().getSelectedItems());
	        },
	        'Item:onDeselect': function ItemOnDeselect() {
	          _classPrivateMethodGet$3(_this8, _changeSelectedRecipients, _changeSelectedRecipients2).call(_this8, babelHelpers.classPrivateFieldGet(_this8, _selector).getDialog().getSelectedItems());
	        }
	      }
	    }
	  }));
	  babelHelpers.classPrivateFieldGet(this, _selector).renderTo(babelHelpers.classPrivateFieldGet(this, _recipientSelector));
	  return babelHelpers.classPrivateFieldGet(this, _selector);
	}
	function _clearSelector2() {
	  main_core.Dom.clean(babelHelpers.classPrivateFieldGet(this, _recipientSelector));
	  _classPrivateMethodGet$3(this, _initRecipientSelector, _initRecipientSelector2).call(this);
	}
	function _initTagsSelector2() {
	  if (!babelHelpers.classPrivateFieldGet(this, _postFormTags)) {
	    babelHelpers.classPrivateFieldSet(this, _postFormTags, new PostFormTags(babelHelpers.classPrivateFieldGet(this, _formId$2), babelHelpers.classPrivateFieldGet(this, _node)));
	  }
	}
	function _changeSelectedRecipients2(selectedItems) {
	  var recipients = [];
	  selectedItems.forEach(function (item) {
	    recipients.push([item.entityId, item.id]);
	  });
	  babelHelpers.classPrivateFieldGet(this, _postData).setRecipients(recipients.length > 0 ? JSON.stringify(recipients) : '');
	}
	function _renderTitle2() {
	  var uiClasses = 'ui-ctl ui-ctl-textbox ui-ctl-no-border ui-ctl-w100 ui-ctl-no-padding ui-ctl-xs';
	  var hiddenClass = babelHelpers.classPrivateFieldGet(this, _isShownPostTitle$1) ? '' : '--hidden';
	  babelHelpers.classPrivateFieldSet(this, _titleNode, main_core.Tag.render(_templateObject4 || (_templateObject4 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"sn-post-form__discussion-row ", "\">\n\t\t\t\t<div class=\"", "\">\n\t\t\t\t\t<input\n\t\t\t\t\t\ttype=\"text\"\n\t\t\t\t\t\tclass=\"ui-ctl-element sn-post-form__discussion_title\"\n\t\t\t\t\t\tplaceholder=\"", "\"\n\t\t\t\t\t\tdata-id=\"sn-post-form-title-input\"\n\t\t\t\t\t\tvalue=\"", "\"\n\t\t\t\t\t>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t"])), hiddenClass, uiClasses, main_core.Loc.getMessage('SN_PF_TITLE_PLACEHOLDER'), main_core.Text.encode(babelHelpers.classPrivateFieldGet(this, _postData).getTitle())));
	  return babelHelpers.classPrivateFieldGet(this, _titleNode);
	}
	function _afterEditorInit2() {
	  babelHelpers.classPrivateFieldGet(this, _sendBtn).setWaiting(false);
	}
	function _toggleVisibilityPostTitle2$1() {
	  main_core.Dom.toggleClass(babelHelpers.classPrivateFieldGet(this, _titleNode), '--hidden');
	  var isShown = !main_core.Dom.hasClass(babelHelpers.classPrivateFieldGet(this, _titleNode), '--hidden');
	  if (isShown) {
	    babelHelpers.classPrivateFieldGet(this, _titleNode).querySelector('input').focus();
	  }
	  UserOptions.save('socialnetwork', 'postEdit', 'showTitle', isShown ? 'Y' : 'N');
	}
	function _changePostFormPosition2() {
	  main_core.Dom.toggleClass(babelHelpers.classPrivateFieldGet(this, _popup).getPopupContainer(), '--normal');
	}
	function _addMention2$1(baseEvent) {
	  var _baseEvent$getData = baseEvent.getData(),
	    type = _baseEvent$getData.type,
	    entity = _baseEvent$getData.entity,
	    entityType = _baseEvent$getData.entityType;
	  babelHelpers.classPrivateFieldGet(this, _selector).getDialog().addItem({
	    avatar: entity.avatar,
	    customData: {
	      email: main_core.Type.isStringFilled(entity.email) ? entity.email : ''
	    },
	    entityId: type,
	    entityType: entityType,
	    id: entity.entityId,
	    title: entity.name
	  }).select();
	}
	function _showControllers2(baseEvent) {
	  var contentContainer = babelHelpers.classPrivateFieldGet(this, _popup).getContentContainer();
	  contentContainer.scrollTo({
	    top: contentContainer.scrollHeight - contentContainer.clientHeight,
	    behavior: 'smooth'
	  });
	}
	function _consoleError2(action, error) {
	  // todo
	  NotificationCenter.notify({
	    content: main_core.Loc.getMessage('SN_PF_REQUEST_ERROR')
	  });

	  // eslint-disable-next-line no-console
	  console.error("PostForm: ".concat(action, " error"), error);
	}

	exports.PostForm = PostForm;

}((this.BX.Socialnetwork = this.BX.Socialnetwork || {}),BX.Main,BX.UI.EntitySelector,BX.UI,BX.UI.Uploader,BX.Event,BX));
//# sourceMappingURL=post-form.bundle.js.map
