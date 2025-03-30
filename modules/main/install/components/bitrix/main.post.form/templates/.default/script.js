/* eslint-disable */
;(function() {

	if (window['LHEPostForm'])
	{
		return;
	}

this.BX = this.BX || {};
(function (exports,main_core_events,main_polyfill_intersectionobserver,main_popup,main_core) {
	'use strict';

	var Default = /*#__PURE__*/function () {
	  function Default(editor, htmlEditor) {
	    babelHelpers.classCallCheck(this, Default);
	    babelHelpers.defineProperty(this, "id", 'SomeParser');
	    babelHelpers.defineProperty(this, "buttonParams", {
	      name: 'Some parser name',
	      iconClassName: 'some-parser-class',
	      disabledForTextarea: false,
	      src: '/icon.png',
	      toolbarSort: 205,
	      compact: false
	    });
	    this.editor = editor;
	    this.htmlEditor = htmlEditor;
	    this.handler = this.handler.bind(this);
	  }
	  babelHelpers.createClass(Default, [{
	    key: "handler",
	    value: function handler() {}
	  }, {
	    key: "parse",
	    value: function parse(text) {
	      return text;
	    }
	  }, {
	    key: "unparse",
	    value: function unparse(bxTag, oNode) {
	      return '';
	    }
	  }, {
	    key: "hasButton",
	    value: function hasButton() {
	      return this.buttonParams !== null;
	    }
	  }, {
	    key: "getButton",
	    value: function getButton() {
	      if (this.buttonParams === null) {
	        return null;
	      }
	      return {
	        id: this.id,
	        name: this.buttonParams.name,
	        iconClassName: this.buttonParams.iconClassName,
	        disabledForTextarea: this.buttonParams.disabledForTextarea,
	        src: this.buttonParams.src,
	        toolbarSort: this.buttonParams.toolbarSort,
	        compact: this.buttonParams.compact === true,
	        handler: this.handler
	      };
	    }
	  }, {
	    key: "getParser",
	    value: function getParser() {
	      var _this = this;
	      return {
	        name: this.id,
	        obj: {
	          Parse: function Parse(parserId, text) {
	            return _this.parse(text);
	          },
	          UnParse: this.unparse.bind(this)
	        }
	      };
	    }
	  }]);
	  return Default;
	}();

	var Spoiler = /*#__PURE__*/function (_Default) {
	  babelHelpers.inherits(Spoiler, _Default);
	  function Spoiler() {
	    var _babelHelpers$getProt;
	    var _this;
	    babelHelpers.classCallCheck(this, Spoiler);
	    for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
	      args[_key] = arguments[_key];
	    }
	    _this = babelHelpers.possibleConstructorReturn(this, (_babelHelpers$getProt = babelHelpers.getPrototypeOf(Spoiler)).call.apply(_babelHelpers$getProt, [this].concat(args)));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "id", 'spoiler');
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "buttonParams", {
	      name: main_core.Loc.getMessage('MPF_SPOILER'),
	      iconClassName: 'spoiler',
	      disabledForTextarea: false,
	      src: main_core.Loc.getMessage('MPF_TEMPLATE_FOLDER') + '/images/lhespoiler.svg',
	      toolbarSort: 205
	    });
	    return _this;
	  }
	  babelHelpers.createClass(Spoiler, [{
	    key: "handler",
	    value: function handler() {
	      var result;
	      // Iframe
	      if (!this.htmlEditor.bbCode || !this.htmlEditor.synchro.IsFocusedOnTextarea()) {
	        result = this.htmlEditor.action.actions.formatBlock.exec('formatBlock', 'blockquote', 'bx-spoiler', false, {
	          bxTagParams: {
	            tag: "spoiler"
	          }
	        });
	      } else
	        // bbcode + textarea
	        {
	          result = this.htmlEditor.action.actions.formatBbCode.exec('quote', {
	            tag: 'SPOILER'
	          });
	        }
	      return result;
	    }
	  }, {
	    key: "parse",
	    value: function parse(content, pLEditor) {
	      if (/\[spoiler(([^\]])*)\]/gi.test(content)) {
	        content = content.replace(/[\x01-\x02]/gi, '').replace(/\[spoiler([^\]]*)\]/gi, '\x01$1\x01').replace(/\[\/spoiler]/gi, '\x02');
	        var reg2 = /(?:\x01([^\x01]*)\x01)([^\x01-\x02]+)\x02/gi;
	        while (content.match(reg2)) {
	          content = content.replace(reg2, function (str, title, body) {
	            title = title.replace(/^(="|='|=)/gi, '').replace(/("|')?$/gi, '');
	            return "<blockquote class=\"bx-spoiler\" id=\"".concat(this.htmlEditor.SetBxTag(false, {
	              tag: "spoiler"
	            }), "\" title=\"").concat(title, "\">").concat(body, "</blockquote>");
	          }.bind(this));
	        }
	      }
	      content = content.replace(/\001([^\001]*)\001/gi, '[spoiler$1]').replace(/\002/gi, '[/spoiler]');
	      return content;
	    }
	  }, {
	    key: "unparse",
	    value: function unparse(bxTag, oNode) {
	      var name = '';
	      for (var i = 0; i < oNode.node.childNodes.length; i++) {
	        name += this.htmlEditor.bbParser.GetNodeHtml(oNode.node.childNodes[i]);
	      }
	      name = name.trim();
	      if (name !== '') {
	        return "[SPOILER" + (oNode.node.hasAttribute("title") ? '=' + oNode.node.getAttribute("title") : '') + "]" + name + "[/SPOILER]";
	      }
	      return "";
	    }
	  }]);
	  return Spoiler;
	}(Default);

	var PostUser = /*#__PURE__*/function (_Default) {
	  babelHelpers.inherits(PostUser, _Default);
	  function PostUser(editor, htmlEditor) {
	    var _this;
	    babelHelpers.classCallCheck(this, PostUser);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(PostUser).call(this, editor, htmlEditor));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "id", 'postuser');
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "buttonParams", null);
	    main_core_events.EventEmitter.subscribe(htmlEditor, 'OnIframeKeydown', function (_ref) {
	      var _ref$compatData = babelHelpers.slicedToArray(_ref.compatData, 1),
	        event = _ref$compatData[0];
	      if (window.onKeyDownHandler) {
	        window.onKeyDownHandler(event, htmlEditor, htmlEditor.formID);
	      }
	    });
	    main_core_events.EventEmitter.subscribe(htmlEditor, 'OnIframeKeyup', function (_ref2) {
	      var _ref2$compatData = babelHelpers.slicedToArray(_ref2.compatData, 1),
	        event = _ref2$compatData[0];
	      if (window.onKeyUpHandler) {
	        window.onKeyUpHandler(event, htmlEditor, htmlEditor.formID);
	      }
	    });
	    main_core_events.EventEmitter.subscribe(htmlEditor, 'OnIframeClick', function () {
	      if (window['BXfpdStopMent' + htmlEditor.formID]) {
	        window['BXfpdStopMent' + htmlEditor.formID]();
	      }
	    });
	    main_core_events.EventEmitter.subscribe(htmlEditor, 'OnTextareaKeyup', function (_ref3) {
	      var _ref3$compatData = babelHelpers.slicedToArray(_ref3.compatData, 1),
	        event = _ref3$compatData[0];
	      if (htmlEditor.textareaView && htmlEditor.textareaView.GetCursorPosition && window.onTextareaKeyUpHandler) {
	        window.onTextareaKeyUpHandler(event, htmlEditor, htmlEditor.formID);
	      }
	    });
	    main_core_events.EventEmitter.subscribe(htmlEditor, 'OnTextareaKeydown', function (_ref4) {
	      var _ref4$compatData = babelHelpers.slicedToArray(_ref4.compatData, 1),
	        event = _ref4$compatData[0];
	      if (htmlEditor.textareaView && htmlEditor.textareaView.GetCursorPosition && window.onTextareaKeyDownHandler) {
	        window.onTextareaKeyDownHandler(event, htmlEditor, htmlEditor.formID);
	      }
	    });
	    return _this;
	  }
	  babelHelpers.createClass(PostUser, [{
	    key: "parse",
	    value: function parse(content, pLEditor) {
	      var _this2 = this;
	      content = content.replace(/\[USER\s*=\s*(\d+)\](.*?)\[\/USER\]/ig, function (str, id, name) {
	        name = name.trim();
	        if (name === '') {
	          return '';
	        }
	        var tagId = _this2.htmlEditor.SetBxTag(false, {
	          tag: _this2.id,
	          userId: id,
	          userName: name
	        });
	        return "<span id=\"".concat(tagId, "\" class=\"bxhtmled-metion\">").concat(name, "</span>");
	      }).replace(/\[PROJECT\s*=\s*(\d+)\](.*?)\[\/PROJECT\]/ig, function (str, id, name) {
	        name = name.trim();
	        if (name === '') {
	          return '';
	        }
	        var tagId = _this2.htmlEditor.SetBxTag(false, {
	          tag: _this2.id,
	          projectId: id,
	          projectName: name
	        });
	        return "<span id=\"".concat(tagId, "\" class=\"bxhtmled-metion\">").concat(name, "</span>");
	      }).replace(/\[DEPARTMENT\s*=\s*(\d+)\](.*?)\[\/DEPARTMENT\]/ig, function (str, id, name) {
	        name = name.trim();
	        if (name === '') {
	          return '';
	        }
	        var tagId = _this2.htmlEditor.SetBxTag(false, {
	          tag: _this2.id,
	          departmentId: id,
	          departmentName: name
	        });
	        return "<span id=\"".concat(tagId, "\" class=\"bxhtmled-metion\">").concat(name, "</span>");
	      });
	      return content;
	    }
	  }, {
	    key: "unparse",
	    value: function unparse(bxTag, oNode) {
	      var _this3 = this;
	      var text = '';
	      oNode.node.childNodes.forEach(function (node) {
	        text += _this3.htmlEditor.bbParser.GetNodeHtml(node);
	      });
	      text = String(text).trim();
	      var result = '';
	      if (main_core.Type.isStringFilled(text)) {
	        if (!main_core.Type.isUndefined(bxTag.userId)) {
	          result = "[USER=".concat(bxTag.userId, "]").concat(text, "[/USER]");
	        } else if (!main_core.Type.isUndefined(bxTag.projectId)) {
	          result = "[PROJECT=".concat(bxTag.projectId, "]").concat(text, "[/PROJECT]");
	        } else if (!main_core.Type.isUndefined(bxTag.departmentId)) {
	          result = "[DEPARTMENT=".concat(bxTag.departmentId, "]").concat(text, "[/DEPARTMENT]");
	        }
	      }
	      return result;
	    }
	  }]);
	  return PostUser;
	}(Default);

	var Controller = /*#__PURE__*/function () {
	  function Controller(cid, container, editor) {
	    babelHelpers.classCallCheck(this, Controller);
	    babelHelpers.defineProperty(this, "actionPool", []);
	    this.cid = cid;
	    this.container = container;
	    this.editor = editor;
	    main_core_events.EventEmitter.subscribe(editor.getEventObject(), 'onShowControllers', function (_ref) {
	      var data = _ref.data;
	      main_core_events.EventEmitter.emit(container.parentNode, 'BFileDLoadFormController', new main_core_events.BaseEvent({
	        compatData: [data]
	      }));
	    });
	    main_core_events.EventEmitter.subscribe(editor.getEventObject(), 'onCollectControllers', function (event) {
	      event.data[cid] = {
	        values: []
	      };
	    });
	  }
	  babelHelpers.createClass(Controller, [{
	    key: "exec",
	    value: function exec() {
	      var callback = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
	      if (callback) {
	        this.actionPool.push(callback);
	      }
	      if (this.isReady) {
	        try {
	          var action;
	          while ((action = this.actionPool.shift()) && action) {
	            action.apply(this);
	          }
	        } catch (e) {
	          console.log('error in attachments controllers: ', e);
	        }
	      }
	    }
	  }, {
	    key: "getId",
	    value: function getId() {
	      return this.cid;
	    }
	  }, {
	    key: "getFieldName",
	    value: function getFieldName() {
	      return null;
	    }
	  }, {
	    key: "reinitFrom",
	    value: function reinitFrom(data) {
	      var _this = this;
	      this.exec(function () {
	        if (!_this.getFieldName()) {
	          return;
	        }
	        _this.container.querySelector("inptut[name=\"".concat(_this.getFieldName(), "\"]")).forEach(function (inputFile) {
	          inputFile.parentNode.removeChild(inputFile);
	        });
	      });
	    }
	  }, {
	    key: "isReady",
	    get: function get() {
	      return true;
	    }
	  }]);
	  return Controller;
	}();

	var DiskController = /*#__PURE__*/function (_Controller) {
	  babelHelpers.inherits(DiskController, _Controller);
	  function DiskController(cid, container, editor) {
	    var _this;
	    babelHelpers.classCallCheck(this, DiskController);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(DiskController).call(this, cid, container, editor));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "diskUfUploader", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "diskUfHandler", null);
	    var _catchHandler = function _catchHandler(diskUfUploader) {
	      _this.diskUfUploader = diskUfUploader;
	      _this.exec();
	      var func = function func(BaseEvent) {
	        main_core_events.EventEmitter.emit(editor.getEventObject(), 'onUploadsHasBeenChanged', BaseEvent);
	      };
	      main_core_events.EventEmitter.subscribe(_this.diskUfUploader, 'onFileIsInited', func); // new diskUfUploader
	      main_core_events.EventEmitter.subscribe(_this.diskUfUploader, 'ChangeFileInput', func); // old diskUfUploader
	    };

	    if (BX.UploaderManager.getById(cid)) {
	      _catchHandler(BX.UploaderManager.getById(cid));
	    }
	    main_core_events.EventEmitter.subscribeOnce(container.parentNode, 'DiskDLoadFormControllerInit', function (_ref) {
	      var _ref$compatData = babelHelpers.slicedToArray(_ref.compatData, 1),
	        diskUfHandler = _ref$compatData[0];
	      _this.diskUfHandler = diskUfHandler;
	      if (cid === diskUfHandler.CID && !_this.diskUfUploader) {
	        _catchHandler(diskUfHandler.agent);
	      }
	    });
	    main_core_events.EventEmitter.subscribe(editor.getEventObject(), 'onShowControllers', function (_ref2) {
	      var data = _ref2.data;
	      main_core_events.EventEmitter.emit(container.parentNode, 'DiskLoadFormController', new main_core_events.BaseEvent({
	        compatData: [data]
	      }));
	    });
	    return _this;
	  }
	  babelHelpers.createClass(DiskController, [{
	    key: "getFieldName",
	    value: function getFieldName() {
	      if (this.diskUfHandler) {
	        return this.diskUfHandler.params.controlName;
	      }
	      return null;
	    }
	  }, {
	    key: "reinitFrom",
	    value: function reinitFrom(data) {
	      var _this2 = this;
	      this.exec(function () {
	        if (!_this2.getFieldName()) {
	          return;
	        }
	        Array.from(_this2.container.querySelectorAll("inptut[name=\"".concat(_this2.getFieldName(), "\"]"))).forEach(function (inputFile) {
	          inputFile.parentNode.removeChild(inputFile);
	        });
	        var values = null;
	        for (var ii in data) {
	          if (data.hasOwnProperty(ii) && data[ii] && data[ii]['USER_TYPE_ID'] === 'disk_file' && data[ii]['FIELD_NAME'] === _this2.getFieldName()) {
	            values = data[ii]['VALUE'];
	          }
	        }
	        if (values) {
	          var files = {};
	          values.forEach(function (id) {
	            var node = document.querySelector('#disk-attach-' + id);
	            if (node.tagName !== "A") {
	              node = node.querySelector('img');
	            }
	            if (node) {
	              files['E' + id] = {
	                type: 'file',
	                id: id,
	                name: node.getAttribute("data-bx-title") || node.getAttribute("data-title"),
	                size: node.getAttribute("data-bx-size") || '',
	                sizeInt: node.getAttribute("data-bx-size") || '',
	                width: node.getAttribute("data-bx-width"),
	                height: node.getAttribute("data-bx-height"),
	                storage: 'disk',
	                previewUrl: node.tagName === "A" ? '' : node.getAttribute("data-bx-src") || node.getAttribute("data-src"),
	                fileId: node.getAttribute("bx-attach-file-id")
	              };
	              if (node.hasAttribute("bx-attach-xml-id")) files['E' + id]["xmlId"] = node.getAttribute("bx-attach-xml-id");
	              if (node.hasAttribute("bx-attach-file-type")) files['E' + id]["fileType"] = node.getAttribute("bx-attach-file-type");
	            }
	          });
	          _this2.diskUfHandler.selectFile({}, {}, files);
	        }
	      });
	    }
	  }, {
	    key: "isReady",
	    get: function get() {
	      return !!this.diskUfUploader;
	    }
	  }]);
	  return DiskController;
	}(Controller);

	var _templateObject;
	/*
	* @deprecated
	* */
	var UploadFile = /*#__PURE__*/function (_Default) {
	  babelHelpers.inherits(UploadFile, _Default);
	  function UploadFile(editor, htmlEditor) {
	    var _this;
	    babelHelpers.classCallCheck(this, UploadFile);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(UploadFile).call(this, editor, htmlEditor));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "id", 'uploadfile');
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "buttonParams", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "regexp", /\[FILE ID=((?:\s|\S)*?)?\]/ig);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "values", new Map());
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "controllers", new Map());
	    _this.checkButtonsDebounced = main_core.Runtime.debounce(_this.checkButtons, 500, babelHelpers.assertThisInitialized(_this));
	    _this.init();
	    main_core_events.EventEmitter.subscribe(editor.getEditor(), 'OnContentChanged', _this.checkButtons.bind(babelHelpers.assertThisInitialized(_this)));
	    main_core_events.EventEmitter.subscribe(editor.getEventObject(), 'onReinitializeBefore', function (_ref) {
	      var _ref$data = babelHelpers.slicedToArray(_ref.data, 2),
	        text = _ref$data[0],
	        data = _ref$data[1];
	      _this.reinit(text, data);
	    });
	    return _this;
	  }
	  babelHelpers.createClass(UploadFile, [{
	    key: "init",
	    value: function init() {
	      var _this2 = this;
	      Array.from(this.editor.getContainer().querySelectorAll('.file-selectdialog')).forEach(function (selectorNode, index) {
	        var cid = selectorNode.id.replace('file-selectdialog-', '');
	        var controller = _this2.controllers.get(cid);
	        if (!controller) {
	          controller = new Controller(cid, selectorNode, _this2.editor);
	          main_core_events.EventEmitter.subscribe(selectorNode.parentNode, 'OnFileUploadSuccess', function (_ref2) {
	            var _ref2$data = babelHelpers.slicedToArray(_ref2.data, 2),
	              element_id = _ref2$data[0].element_id,
	              _ref2$data$ = _ref2$data[1],
	              id = _ref2$data$.id,
	              doc_prefix = _ref2$data$.doc_prefix,
	              CID = _ref2$data$.CID;
	            if (cid === id) {
	              var securityNode = document.querySelector('#' + _this2.editor.getFormId()) ? document.querySelector('#' + _this2.editor.getFormId()).querySelector('#upload-cid') : null;
	              if (securityNode) {
	                securityNode.value = CID;
	              }
	              var _this2$parseFile = _this2.parseFile(selectorNode.querySelector('#' + doc_prefix + element_id)),
	                _this2$parseFile2 = babelHelpers.slicedToArray(_this2$parseFile, 2),
	                _id = _this2$parseFile2[0],
	                file = _this2$parseFile2[1];
	              _this2.values.set(_id, file);
	            }
	          });
	          main_core_events.EventEmitter.subscribe(selectorNode.parentNode, 'OnFileUploadRemove', function (_ref3) {
	            var _ref3$compatData = babelHelpers.slicedToArray(_ref3.compatData, 2),
	              fileId = _ref3$compatData[0],
	              id = _ref3$compatData[1].id;
	            if (cid === id && _this2.values.has(fileId)) {
	              _this2.values["delete"](fileId);
	              _this2.deleteFile([fileId]);
	            }
	          });
	          if (index === 0) {
	            main_core_events.EventEmitter.subscribe(_this2.editor.getEventObject(), 'onFilesHaveCaught', function (event) {
	              event.stopImmediatePropagation();
	              if (window['BfileFD' + cid]) {
	                window['BfileFD' + cid].agent.UploadDroppedFiles(babelHelpers.toConsumableArray(event.getData()));
	              }
	            });
	          }
	        }
	        if (selectorNode.querySelector('table.files-list')) {
	          Array.from(selectorNode.querySelector('table.files-list').querySelectorAll('tr')).forEach(function (tr) {
	            var _this2$parseFile3 = _this2.parseFile(tr),
	              _this2$parseFile4 = babelHelpers.slicedToArray(_this2$parseFile3, 2),
	              id = _this2$parseFile4[0],
	              file = _this2$parseFile4[1];
	            _this2.values.set(id, file);
	          });
	        }
	      });
	    }
	  }, {
	    key: "parseFile",
	    value: function parseFile(tr) {
	      var _this3 = this;
	      var id = tr.id.replace('wd-doc', '');
	      var data = {
	        id: id,
	        name: tr.querySelector('[data-role="name"]') ? tr.querySelector('[data-role="name"]').innerHTML : tr.querySelector('span.f-wrap').innerHTML,
	        node: tr,
	        buttonNode: tr.querySelector('[data-role="button-insert"]'),
	        image: {
	          src: null,
	          lowsrc: null,
	          width: null,
	          height: null
	        }
	      };
	      var insertFile = function insertFile() {
	        _this3.insertFile(id, tr);
	      };
	      var nameNode = tr.querySelector('.f-wrap');
	      if (nameNode) {
	        nameNode.addEventListener('click', insertFile);
	        nameNode.style.cursor = 'pointer';
	        nameNode.title = main_core.Loc.getMessage('MPF_FILE');
	      }
	      var imageNode = tr.querySelector('img');
	      if (imageNode) {
	        imageNode.addEventListener('click', insertFile);
	        imageNode.title = main_core.Loc.getMessage('MPF_FILE');
	        imageNode.style.cursor = 'pointer';
	        data.image.lowsrc = imageNode.lowsrc || imageNode.src;
	        data.image.src = imageNode.rel || imageNode.src;
	        data.image.width = imageNode.getAttribute('data-bx-full-width');
	        data.image.height = imageNode.getAttribute('data-bx-full-height');
	      }
	      if (tr instanceof HTMLTableRowElement && tr.querySelector('.files-info')) {
	        if (!data.buttonNode) {
	          data.buttonNode = main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n<span type=\"button\" onclick=\"", "\" data-role=\"button-insert\" class=\"insert-btn\">\n\t<span data-role=\"insert-btn\" class=\"insert-btn-text\">", "</span>\n\t<span data-role=\"in-text-btn\" class=\"insert-btn-text\">", "</span>\n</span>"])), insertFile, main_core.Loc.getMessage('MPF_FILE_INSERT_IN_TEXT'), main_core.Loc.getMessage('MPF_FILE_IN_TEXT'));
	          tr.querySelector('.files-info').appendChild(data.buttonNode);
	          this.checkButtonsDebounced();
	        }
	      }
	      return [id, data];
	    }
	  }, {
	    key: "buildHTML",
	    value: function buildHTML(id, data) {
	      var htmlData = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : null;
	      var tagId = this.htmlEditor.SetBxTag(false, {
	        tag: this.id,
	        fileId: id
	      });
	      var html = "<span data-bx-file-id=\"".concat(id, "\" id=\"").concat(tagId, "\" style=\"color: #2067B0; border-bottom: 1px dashed #2067B0; margin:0 2px;\">").concat(data.name, "</span>");
	      if (data.image.src) {
	        var additional = [];
	        if (htmlData) {
	          additional.push("style=\"width:".concat(htmlData.width, "px;height:").concat(htmlData.height, "px;\""));
	        } else if (data.image.width && data.image.height) {
	          additional.push("style=\"width:".concat(data.image.width, "px;height:").concat(data.image.height, "px;\" "));
	          additional.push("onload=\"this.style.width='auto';this.style.height='auto';\"");
	        }
	        html = "<img style=\"max-width: 90%;\"  data-bx-file-id=\"".concat(id, "\" id=\"").concat(tagId, "\" src=\"").concat(data.image.src, "\" lowsrc=\"").concat(data.image.lowsrc, "\" ").concat(additional.join(' '), "/>");
	      }
	      return html;
	    }
	  }, {
	    key: "buildText",
	    value: function buildText(id, params) {
	      return "[FILE ID=".concat(id).concat(params || '', "]");
	    }
	  }, {
	    key: "insertFile",
	    value: function insertFile(id, node) {
	      var data = this.values.get(String(id));
	      if (data) {
	        main_core_events.EventEmitter.emit(this.editor.getEventObject(), 'OnInsertContent', [this.buildText(id), this.buildHTML(id, data)]);
	      }
	    }
	  }, {
	    key: "deleteFile",
	    value: function deleteFile(fileIds) {
	      var content = this.htmlEditor.GetContent();
	      if (this.htmlEditor.GetViewMode() === 'wysiwyg') {
	        var doc = this.htmlEditor.GetIframeDoc();
	        for (var ii in this.htmlEditor.bxTags) {
	          if (this.htmlEditor.bxTags.hasOwnProperty(ii) && babelHelpers["typeof"](this.htmlEditor.bxTags[ii]) === 'object' && this.htmlEditor.bxTags[ii]['tag'] === this.id && fileIds.indexOf(String(this.htmlEditor.bxTags[ii]['fileId'])) >= 0 && doc.getElementById(ii)) {
	            var node = doc.getElementById(ii);
	            node.parentNode.removeChild(node);
	          }
	        }
	        this.htmlEditor.SaveContent();
	      } else /* if (this.regexp.test(content))*/
	        {
	          var content2 = content.replace(this.regexp, function (str, foundId) {
	            return fileIds.indexOf(foundId) >= 0 ? '' : str;
	          });
	          this.htmlEditor.SetContent(content2);
	          this.htmlEditor.Focus();
	        }
	    }
	  }, {
	    key: "checkButtons",
	    value: function checkButtons(event) {
	      var content = event ? event.compatData[0] : this.htmlEditor.GetContent();
	      var matches = babelHelpers.toConsumableArray(content.matchAll(this.regexp)).map(function (_ref4) {
	        var _ref5 = babelHelpers.slicedToArray(_ref4, 2),
	          match = _ref5[0],
	          id = _ref5[1];
	        return id;
	      });
	      this.values.forEach(function (data, id) {
	        if (!data.buttonNode) {
	          return;
	        }
	        var mark = matches.indexOf(id) >= 0;
	        if (mark === true && data.buttonNode.className !== 'insert-text') {
	          data.buttonNode.className = 'insert-text';
	          data.buttonNode.querySelector('[data-role="insert-btn"]').style.display = 'none';
	          data.buttonNode.querySelector('[data-role="in-text-btn"]').style.display = '';
	        } else if (mark !== true && data.buttonNode.className !== 'insert-btn') {
	          data.buttonNode.className = 'insert-btn';
	          data.buttonNode.querySelector('[data-role="insert-btn"]').style.display = '';
	          data.buttonNode.querySelector('[data-role="in-text-btn"]').style.display = 'none';
	        }
	      });
	    }
	  }, {
	    key: "reinit",
	    value: function reinit(text, data) {
	      this.values.forEach(function (file, id) {
	        if (file.node && file.node.parentNode) {
	          file.node.parentNode.removeChild(file.node);
	        }
	      });
	      this.values.clear();
	      this.controllers.forEach(function (controller) {
	        controller.reinitFrom(data);
	      });
	    }
	  }, {
	    key: "parse",
	    value: function parse(content) {
	      if (!this.regexp.test(content)) {
	        return content;
	      }
	      content = content.replace(this.regexp, function (str, id, width, height) {
	        if (this.values.has(id)) {
	          return this.buildHTML(id, this.values.get(id), width > 0 && height > 0 ? {
	            width: width,
	            height: height
	          } : null);
	        }
	        return str;
	      }.bind(this));
	      return content;
	    }
	  }, {
	    key: "unparse",
	    value: function unparse(bxTag, _ref6) {
	      var node = _ref6.node;
	      var width = parseInt(node.hasAttribute('width') ? node.getAttribute('width') : 0);
	      var height = parseInt(node.hasAttribute('height') ? node.getAttribute('height') : 0);
	      var params = '';
	      if (width > 0 && height > 0) {
	        params = ' WIDTH=' + width + ' HEIGHT=' + height;
	      }
	      var id = node.getAttribute('data-bx-file-id');
	      return this.buildText(id, params);
	    }
	  }]);
	  return UploadFile;
	}(Default);

	/*
	* @deprecated
	* */
	var UploadImage = /*#__PURE__*/function (_Default) {
	  babelHelpers.inherits(UploadImage, _Default);
	  function UploadImage(editor, htmlEditor) {
	    var _this;
	    babelHelpers.classCallCheck(this, UploadImage);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(UploadImage).call(this, editor, htmlEditor));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "id", 'uploadimage');
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "buttonParams", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "regexp", /\[IMAGE ID=((?:\s|\S)*?)?\]/ig);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "values", new Map());
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "controllers", new Map());
	    _this.init();
	    console.log('PostImage: ');
	    main_core_events.EventEmitter.subscribe(editor.getEventObject(), 'onReinitializeBefore', function (_ref) {
	      var _ref$data = babelHelpers.slicedToArray(_ref.data, 2),
	        text = _ref$data[0],
	        data = _ref$data[1];
	      _this.reinit(text, data);
	    });
	    return _this;
	  }
	  babelHelpers.createClass(UploadImage, [{
	    key: "init",
	    value: function init() {
	      var _this2 = this;
	      Array.from(this.editor.getContainer().querySelectorAll('.file-selectdialog')).forEach(function (selectorNode) {
	        var cid = selectorNode.id.replace('file-selectdialog-', '');
	        var controller = _this2.controllers.get(cid);
	        if (!controller) {
	          controller = new Controller(cid, selectorNode, _this2.editor);
	          main_core_events.EventEmitter.subscribe(selectorNode.parentNode, 'OnFileUploadSuccess', function (_ref2) {
	            var _ref2$data = babelHelpers.slicedToArray(_ref2.data, 2),
	              element_id = _ref2$data[0].element_id,
	              _ref2$data$ = _ref2$data[1],
	              id = _ref2$data$.id,
	              doc_prefix = _ref2$data$.doc_prefix,
	              CID = _ref2$data$.CID;
	            if (cid === id) {
	              var securityNode = document.querySelector('#' + _this2.editor.getFormId()) ? document.querySelector('#' + _this2.editor.getFormId()).querySelector('#upload-cid') : null;
	              if (securityNode) {
	                securityNode.value = CID;
	              }
	              var _this2$parseFile = _this2.parseFile(selectorNode.querySelector('#' + doc_prefix + element_id)),
	                _this2$parseFile2 = babelHelpers.slicedToArray(_this2$parseFile, 2),
	                _id = _this2$parseFile2[0],
	                file = _this2$parseFile2[1];
	              _this2.values.set(_id, file);
	            }
	          });
	          main_core_events.EventEmitter.subscribe(selectorNode.parentNode, 'OnFileUploadRemove', function (_ref3) {
	            var _ref3$compatData = babelHelpers.slicedToArray(_ref3.compatData, 2),
	              fileId = _ref3$compatData[0],
	              id = _ref3$compatData[1].id;
	            if (cid === id && _this2.values.has(fileId)) {
	              _this2.values["delete"](fileId);
	            }
	          });
	        }
	        if (selectorNode.querySelector('table.files-list')) {
	          Array.from(selectorNode.querySelector('table.files-list').querySelectorAll('tr')).forEach(function (tr) {
	            var _this2$parseFile3 = _this2.parseFile(tr),
	              _this2$parseFile4 = babelHelpers.slicedToArray(_this2$parseFile3, 2),
	              id = _this2$parseFile4[0],
	              file = _this2$parseFile4[1];
	            _this2.values.set(id, file);
	          });
	        }
	      });
	    }
	  }, {
	    key: "parseFile",
	    value: function parseFile(tr) {
	      var id = tr.id.replace('wd-doc', '');
	      var data = {
	        id: id,
	        name: tr.querySelector('[data-role="name"]') ? tr.querySelector('[data-role="name"]').innerHTML : tr.querySelector('span.f-wrap').innerHTML,
	        node: tr,
	        image: {
	          src: null,
	          lowsrc: null,
	          width: null,
	          height: null
	        }
	      };
	      return [id, data];
	    }
	  }, {
	    key: "reinit",
	    value: function reinit(text, data) {
	      this.values.forEach(function (file, id) {
	        if (file.node && file.node.parentNode) {
	          file.node.parentNode.removeChild(file.node);
	        }
	      });
	      this.values.clear();
	      this.controllers.forEach(function (controller) {
	        controller.reinitFrom(data);
	      });
	    }
	  }, {
	    key: "parse",
	    value: function parse(content) {
	      return content;
	    }
	  }, {
	    key: "unparse",
	    value: function unparse(bxTag, _ref4) {
	      var node = _ref4.node;
	      return '';
	    }
	  }]);
	  return UploadImage;
	}(Default);

	var _templateObject$1;
	/*
	* @deprecated
	* */
	var DiskFile = /*#__PURE__*/function (_UploadFile) {
	  babelHelpers.inherits(DiskFile, _UploadFile);
	  function DiskFile() {
	    var _babelHelpers$getProt;
	    var _this;
	    babelHelpers.classCallCheck(this, DiskFile);
	    for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
	      args[_key] = arguments[_key];
	    }
	    _this = babelHelpers.possibleConstructorReturn(this, (_babelHelpers$getProt = babelHelpers.getPrototypeOf(DiskFile)).call.apply(_babelHelpers$getProt, [this].concat(args)));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "id", 'diskfile');
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "regexp", /\[(?:DOCUMENT ID|DISK FILE ID)=([n0-9]+)\]/ig);
	    return _this;
	  }
	  babelHelpers.createClass(DiskFile, [{
	    key: "init",
	    value: function init() {
	      var _this2 = this;
	      Array.from(this.editor.getContainer().querySelectorAll('.diskuf-selectdialog')).forEach(function (selectorNode, index) {
	        var cid = selectorNode.id.replace('diskuf-selectdialog-', '');
	        var controller = _this2.controllers.get(cid);
	        if (!controller) {
	          controller = new DiskController(cid, selectorNode, _this2.editor);
	          _this2.controllers.set(cid, controller);
	          main_core_events.EventEmitter.subscribe(selectorNode.parentNode, 'OnFileUploadSuccess', function (_ref) {
	            var _ref$data = babelHelpers.slicedToArray(_ref.data, 3),
	              element_id = _ref$data[0].element_id,
	              CID = _ref$data[1].CID,
	              blob = _ref$data[2];
	            if (controller.getId() !== CID || _this2.values.has(element_id)) {
	              return;
	            }
	            var _this2$parseFile = _this2.parseFile(selectorNode.querySelector('#disk-edit-attach' + element_id)),
	              _this2$parseFile2 = babelHelpers.slicedToArray(_this2$parseFile, 3),
	              id = _this2$parseFile2[0],
	              fileId = _this2$parseFile2[1],
	              file = _this2$parseFile2[2];
	            _this2.values.set(id, file);
	            if (id !== fileId) {
	              _this2.values.set(fileId, file);
	            }
	            if (blob && blob['insertImageAfterUpload'] && file.image.src) {
	              _this2.insertFile(id, file.node);
	            }
	          });
	          main_core_events.EventEmitter.subscribe(selectorNode.parentNode, 'OnFileUploadRemove', function (_ref2) {
	            var _ref2$compatData = babelHelpers.slicedToArray(_ref2.compatData, 2),
	              fileId = _ref2$compatData[0],
	              CID = _ref2$compatData[1].CID;
	            if (controller.getId() === CID && _this2.values.has(fileId)) {
	              var file = _this2.values.get(fileId);
	              _this2.values["delete"](file.id);
	              _this2.values["delete"](file.fileId);
	              _this2.deleteFile([file.id, file.fileId]);
	            }
	          });
	          main_core_events.EventEmitter.subscribe(selectorNode.parentNode, 'OnFileUploadFailed', function (_ref3) {
	            var _ref3$compatData = babelHelpers.slicedToArray(_ref3.compatData, 3),
	              file = _ref3$compatData[0],
	              CID = _ref3$compatData[1].CID,
	              blob = _ref3$compatData[2];
	            if (controller.getId() === CID && blob && blob["referrerToEditor"]) {
	              BX.onCustomEvent(blob["referrerToEditor"], "OnImageDataUriCaughtFailed", []);
	              BX.onCustomEvent(_this2.editor, "OnImageDataUriCaughtFailed", [blob["referrerToEditor"]]);
	            }
	          });
	          if (index === 0) {
	            initVideoReceptionForTheFirstController(_this2, controller, selectorNode, _this2.editor);
	            initImageReceptionForTheFirstController(_this2, controller, selectorNode, _this2.editor);
	            main_core_events.EventEmitter.subscribe(_this2.editor.getEventObject(), 'onFilesHaveCaught', function (event) {
	              event.stopImmediatePropagation();
	              controller.diskUfUploader.onChange(babelHelpers.toConsumableArray(event.getData()));
	            });
	          }
	        }
	        if (selectorNode.querySelector('table.files-list')) {
	          Array.from(selectorNode.querySelector('table.files-list').querySelectorAll('tr')).forEach(function (tr) {
	            var _this2$parseFile3 = _this2.parseFile(tr),
	              _this2$parseFile4 = babelHelpers.slicedToArray(_this2$parseFile3, 3),
	              id = _this2$parseFile4[0],
	              fileId = _this2$parseFile4[1],
	              file = _this2$parseFile4[2];
	            _this2.values.set(id, file);
	            if (id !== fileId) {
	              _this2.values.set(fileId, file);
	            }
	          });
	        }
	      });
	    }
	  }, {
	    key: "parseFile",
	    value: function parseFile(tr) {
	      var _this3 = this;
	      var id = String(tr.id.replace('disk-edit-attach', ''));
	      var data = {
	        id: id,
	        name: tr.querySelector('[data-role="name"]') ? tr.querySelector('[data-role="name"]').innerHTML : tr.querySelector('span.f-wrap').innerHTML,
	        fileId: tr.getAttribute('bx-attach-file-id'),
	        node: tr,
	        buttonNode: tr.querySelector('[data-role="button-insert"]'),
	        image: {
	          src: null,
	          lowsrc: null,
	          width: null,
	          height: null
	        }
	      };
	      var nameNode = tr.querySelector('.f-wrap');
	      var insertFile = function insertFile() {
	        _this3.insertFile(id, tr);
	      };
	      if (nameNode) {
	        nameNode.addEventListener('click', insertFile);
	        nameNode.style.cursor = 'pointer';
	        nameNode.title = main_core.Loc.getMessage('MPF_FILE');
	      }
	      var imageNode = tr.querySelector('img.files-preview');
	      if (imageNode && (imageNode.src.indexOf('bitrix/tools/disk/uf.php') >= 0 || imageNode.src.indexOf('/disk/showFile/') >= 0)) {
	        imageNode.addEventListener('click', insertFile);
	        imageNode.title = main_core.Loc.getMessage('MPF_FILE');
	        imageNode.style.cursor = 'pointer';
	        data.image.lowsrc = imageNode.lowsrc || imageNode.src;
	        data.image.src = (imageNode.rel || imageNode.getAttribute('data-bx-src') || imageNode.src).replace(/&(width|height)=\d+/gi, '');
	        var handler = function handler() {
	          data.image.width = imageNode.getAttribute('data-bx-full-width');
	          data.image.height = imageNode.getAttribute('data-bx-full-height');
	        };
	        imageNode.addEventListener('load', handler);
	        if (imageNode.complete) {
	          handler();
	        }
	      }
	      if (tr instanceof HTMLTableRowElement && !data.buttonNode) {
	        data.buttonNode = main_core.Tag.render(_templateObject$1 || (_templateObject$1 = babelHelpers.taggedTemplateLiteral(["\n<span class=\"insert-btn\" data-role=\"button-insert\" onclick=\"", "\">\n\t<span data-role=\"insert-btn\" class=\"insert-btn-text\">", "</span>\n\t<span data-role=\"in-text-btn\" class=\"insert-btn-text\" style=\"display: none;\">", "</span>\n</span>"])), insertFile, main_core.Loc.getMessage('MPF_FILE_INSERT_IN_TEXT'), main_core.Loc.getMessage('MPF_FILE_IN_TEXT'));
	        setTimeout(function () {
	          if (tr.querySelector('.files-info')) {
	            tr.querySelector('.files-info').appendChild(data.buttonNode);
	            _this3.checkButtonsDebounced();
	          }
	        });
	      }
	      return [id, data.fileId, data];
	    }
	  }, {
	    key: "buildText",
	    value: function buildText(id, params) {
	      return "[DISK FILE ID=".concat(id).concat(params || '', "]");
	    }
	  }]);
	  return DiskFile;
	}(UploadFile);
	function initVideoReceptionForTheFirstController(diskFileParser, controller, selectorNode, editor) {
	  main_core_events.EventEmitter.subscribe(editor.getEventObject(), 'OnVideoHasCaught', function (event) {
	    var fileToUpload = event.getData();
	    var onSuccess = function onSuccess(_ref4) {
	      var _ref4$data = babelHelpers.slicedToArray(_ref4.data, 3),
	        element_id = _ref4$data[0].element_id;
	      babelHelpers.objectDestructuringEmpty(_ref4$data[1]);
	      var blob = _ref4$data[2];
	      if (fileToUpload === blob && diskFileParser.values.has(element_id)) {
	        main_core_events.EventEmitter.unsubscribe(selectorNode.parentNode, 'OnFileUploadSuccess', onSuccess);
	        diskFileParser.insertFile(element_id, diskFileParser.values.get(element_id).node);
	      }
	    };
	    main_core_events.EventEmitter.subscribe(selectorNode.parentNode, 'OnFileUploadSuccess', onSuccess);
	    controller.exec(function () {
	      controller.diskUfUploader.onChange([fileToUpload]);
	    });
	    event.stopImmediatePropagation();
	  });
	}
	function initImageReceptionForTheFirstController(diskFileParser, controller, selectorNode, editor) {
	  main_core_events.EventEmitter.subscribe(editor.getEventObject(), 'OnImageHasCaught', function (event) {
	    event.stopImmediatePropagation();
	    var fileToUpload = event.getData();
	    return new Promise(function (resolve, reject) {
	      var onSuccess = function onSuccess(_ref5) {
	        var _ref5$data = babelHelpers.slicedToArray(_ref5.data, 3),
	          element_id = _ref5$data[0].element_id;
	        babelHelpers.objectDestructuringEmpty(_ref5$data[1]);
	        var blob = _ref5$data[2];
	        if (fileToUpload === blob && diskFileParser.values.has(element_id)) {
	          main_core_events.EventEmitter.unsubscribe(selectorNode.parentNode, 'OnFileUploadSuccess', onSuccess);
	          main_core_events.EventEmitter.unsubscribe(selectorNode.parentNode, 'OnFileUploadFailed', onFailed);
	          var file = diskFileParser.values.get(element_id);
	          var html = diskFileParser.buildHTML(element_id, file);
	          resolve({
	            image: file.image,
	            html: html
	          });
	        }
	      };
	      var onFailed = function onFailed(_ref6) {
	        var _ref6$data = babelHelpers.slicedToArray(_ref6.data, 3),
	          file = _ref6$data[0];
	        babelHelpers.objectDestructuringEmpty(_ref6$data[1]);
	        var blob = _ref6$data[2];
	        if (fileToUpload === blob) {
	          main_core_events.EventEmitter.unsubscribe(selectorNode.parentNode, 'OnFileUploadSuccess', onSuccess);
	          main_core_events.EventEmitter.unsubscribe(selectorNode.parentNode, 'OnFileUploadFailed', onFailed);
	          reject();
	        }
	      };
	      main_core_events.EventEmitter.subscribe(selectorNode.parentNode, 'OnFileUploadSuccess', onSuccess);
	      main_core_events.EventEmitter.subscribe(selectorNode.parentNode, 'OnFileUploadFailed', onFailed);
	      controller.exec(function () {
	        controller.diskUfUploader.onChange([event.getData()]);
	      });
	    });
	  });
	}

	var AIImageGenerator = /*#__PURE__*/function (_Default) {
	  babelHelpers.inherits(AIImageGenerator, _Default);
	  function AIImageGenerator() {
	    var _babelHelpers$getProt;
	    var _this;
	    babelHelpers.classCallCheck(this, AIImageGenerator);
	    for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
	      args[_key] = arguments[_key];
	    }
	    _this = babelHelpers.possibleConstructorReturn(this, (_babelHelpers$getProt = babelHelpers.getPrototypeOf(AIImageGenerator)).call.apply(_babelHelpers$getProt, [this].concat(args)));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "id", 'ai-image-generator');
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "buttonParams", {
	      name: 'AI image generator',
	      iconClassName: 'feed-add-post-editor-btn-ai-image',
	      disabledForTextarea: false,
	      toolbarSort: 398,
	      compact: true
	    });
	    return _this;
	  }
	  babelHelpers.createClass(AIImageGenerator, [{
	    key: "handler",
	    value: function handler() {
	      var _this2 = this;
	      if (!this.editor.isImageCopilotEnabledBySettings()) {
	        top.BX.UI.InfoHelper.show('limit_copilot_off');
	        return;
	      }
	      main_core.Runtime.loadExtension('ai.picker').then(function () {
	        var aiImagePicker = new BX.AI.Picker({
	          moduleId: 'main',
	          contextId: 'image_' + main_core.Loc.getMessage('USER_ID'),
	          analyticLabel: 'main_post_form_comments_ai_image',
	          saveImages: false,
	          history: true,
	          onSelect: function onSelect(imageURL) {
	            fetch(imageURL).then(function (response) {
	              return response.blob();
	            }).then(function (myBlob) {
	              BX.onCustomEvent(window, 'onAddVideoMessage', [myBlob, _this2.editor.getFormId()]);
	            });
	          }
	        });
	        aiImagePicker.setLangSpace(BX.AI.Picker.LangSpace.image);
	        aiImagePicker.image();
	      });
	    }
	  }, {
	    key: "parse",
	    value: function parse(content, pLEditor) {
	      return content;
	    }
	  }, {
	    key: "unparse",
	    value: function unparse(bxTag, oNode) {
	      return '';
	    }
	  }]);
	  return AIImageGenerator;
	}(Default);

	function getKnownParser(parserId, editor, htmlEditor) {
	  if (parserId === 'Spoiler') {
	    return new Spoiler(editor, htmlEditor);
	  }
	  if (parserId === 'MentionUser') {
	    return new PostUser(editor, htmlEditor);
	  }
	  if (parserId === 'UploadImage') {
	    return new UploadImage(editor, htmlEditor);
	  }
	  if (parserId === 'UploadFile') {
	    return new UploadFile(editor, htmlEditor);
	  }
	  if (parserId === 'AIImage') {
	    return new AIImageGenerator(editor, htmlEditor);
	  }
	  if (babelHelpers["typeof"](parserId) === 'object' && parserId['disk_file']) {
	    return new DiskFile(editor, htmlEditor);
	  }
	  return null;
	}

	function bindAutoSave(htmlEditor, formNode) {
	  if (!formNode) {
	    return;
	  }
	  BX.addCustomEvent(formNode, 'onAutoSavePrepare', function (ob) {
	    ob.FORM.setAttribute("bx-lhe-autosave-prepared", "Y");
	    setTimeout(function () {
	      BX.addCustomEvent(htmlEditor, 'OnContentChanged', function (text) {
	        ob["mpfTextContent"] = text;
	        ob.Init();
	      });
	    }, 1500);
	  });
	  BX.addCustomEvent(formNode, 'onAutoSave', function (ob, form_data) {
	    if (BX.type.isNotEmptyString(ob['mpfTextContent'])) form_data['text'] = ob['mpfTextContent'];
	  });
	  BX.addCustomEvent(formNode, 'onAutoSaveRestore', function (ob, form_data) {
	    if (form_data['text'] && /[^\s]+/gi.test(form_data['text'])) {
	      htmlEditor.CheckAndReInit(form_data['text']);
	    }
	  });
	  if (formNode.hasAttribute("bx-lhe-autosave-prepared") && formNode.BXAUTOSAVE) {
	    formNode.removeAttribute("bx-lhe-autosave-prepared");
	    setTimeout(formNode.BXAUTOSAVE.Prepare, 100);
	  }
	}

	function showPanelEditor(editor, htmlEditor, editorParams) {
	  var save = false;
	  if (editorParams.showPanelEditor !== true && editorParams.showPanelEditor !== false) {
	    editorParams.showPanelEditor = !htmlEditor.toolbar.IsShown();
	    save = true;
	  }
	  editor.exec(function () {
	    var buttonNode = editor.getContainer().querySelector('[data-bx-role="button-show-panel-editor"]');
	    if (editorParams.showPanelEditor) {
	      htmlEditor.dom.toolbarCont.style.opacity = 'inherit';
	      htmlEditor.toolbar.Show();
	      if (buttonNode) {
	        buttonNode.classList.add('feed-add-post-form-btn-active');
	      }
	    } else {
	      htmlEditor.toolbar.Hide();
	      if (buttonNode) {
	        buttonNode.classList.remove('feed-add-post-form-btn-active');
	      }
	    }
	  });
	  if (save !== false) {
	    BX.userOptions.save('main.post.form', 'postEdit', 'showBBCode', editorParams.showPanelEditor ? 'Y' : 'N');
	  }
	}

	function showUrlPreview(htmlEditor, editorParams) {
	  if (!(editorParams.urlPreviewId && window['BXUrlPreview'] && BX(editorParams.urlPreviewId))) {
	    return;
	  }
	  var urlPreview = new BXUrlPreview(BX(editorParams.urlPreviewId));
	  var OnAfterUrlConvert = function OnAfterUrlConvert(url) {
	    urlPreview.attachUrlPreview({
	      url: url
	    });
	  };
	  var OnBeforeCommandExec = function OnBeforeCommandExec(isContentAction, action, oAction, value) {
	    if (action === 'createLink' && BX.type.isPlainObject(value) && value.hasOwnProperty('href')) {
	      urlPreview.attachUrlPreview({
	        url: value.href
	      });
	    }
	  };
	  BX.addCustomEvent(htmlEditor, 'OnAfterUrlConvert', OnAfterUrlConvert);
	  BX.addCustomEvent(htmlEditor, 'OnAfterLinkInserted', OnAfterUrlConvert);
	  BX.addCustomEvent(htmlEditor, 'OnBeforeCommandExec', OnBeforeCommandExec);
	  BX.addCustomEvent(htmlEditor, 'OnReinitialize', function (text, data) {
	    urlPreview.detachUrlPreview();
	    var urlPreviewId;
	    for (var uf in data) {
	      if (data.hasOwnProperty(uf) && data[uf].hasOwnProperty('USER_TYPE_ID') && data[uf]['USER_TYPE_ID'] === 'url_preview') {
	        urlPreviewId = data[uf]['VALUE'];
	        break;
	      }
	    }
	    if (urlPreviewId) {
	      urlPreview.attachUrlPreview({
	        id: urlPreviewId
	      });
	    }
	  });
	}

	function customizeHTMLEditor(editor, htmlEditor) {
	  editor.exec(function () {
	    // Contextmenu changing for images/files
	    htmlEditor.contextMenu.items['postimage'] = htmlEditor.contextMenu.items['postdocument'] = htmlEditor.contextMenu.items['postfile'] = [{
	      TEXT: main_core.Loc.getMessage('BXEdDelFromText'),
	      bbMode: true,
	      ACTION: function ACTION() {
	        var node = htmlEditor.contextMenu.GetTargetItem('postimage');
	        if (!node) node = htmlEditor.contextMenu.GetTargetItem('postdocument');
	        if (!node) node = htmlEditor.contextMenu.GetTargetItem('postfile');
	        if (node && node.element) {
	          htmlEditor.selection.RemoveNode(node.element);
	        }
	        htmlEditor.contextMenu.Hide();
	      }
	    }];
	    if (htmlEditor.toolbar.controls && htmlEditor.toolbar.controls.FontSelector) {
	      htmlEditor.toolbar.controls.FontSelector.SetWidth(45);
	    }
	  });
	}

	function bindHTML(editor) {
	  var submitButton = document.querySelector('#lhe_button_submit_' + editor.getFormId());
	  if (submitButton) {
	    submitButton.addEventListener('click', function (event) {
	      main_core_events.EventEmitter.emit(editor.getEventObject(), 'OnButtonClick', ['submit']);
	      event.preventDefault();
	      event.stopPropagation();
	    });
	  }
	  var cancelButton = document.querySelector('#lhe_button_cancel_' + editor.getFormId());
	  if (cancelButton) {
	    cancelButton.addEventListener('click', function (event) {
	      main_core_events.EventEmitter.emit(editor.getEventObject(), 'OnButtonClick', ['cancel']);
	      event.preventDefault();
	      event.stopPropagation();
	    });
	  }
	}

	function bindToolbar(editor, htmlEditor) {
	  var toolbar = editor.getContainer().querySelector('[data-bx-role="toolbar"]');
	  if (toolbar.querySelector('[data-id="file"]')) {
	    var fileButton = toolbar.querySelector('[data-id="file"]');
	    if (fileButton) {
	      fileButton.addEventListener('click', function () {
	        main_core_events.EventEmitter.emit(editor.getEventObject(), 'onShowControllers', fileButton.hasAttribute('data-bx-button-status') ? 'hide' : 'show');
	      });
	      main_core_events.EventEmitter.subscribe(editor.getEventObject(), 'onShowControllers', function (_ref) {
	        var data = _ref.data;
	        if (data.toString() === 'show') {
	          fileButton.setAttribute('data-bx-button-status', 'active');
	        } else {
	          fileButton.removeAttribute('data-bx-button-status');
	        }
	      });
	      fileButton.setAttribute('data-bx-files-count', 0);
	      main_core_events.EventEmitter.subscribe(editor.getEventObject(), 'onShowControllers:File:Increment', function (_ref2) {
	        var data = _ref2.data;
	        var count = data > 0 ? data : 1;
	        var filesCount = Math.max(parseInt(fileButton.getAttribute('data-bx-files-count') || 0) + count, 0);
	        if (filesCount > 0) {
	          if (!fileButton['counterObject']) {
	            fileButton['counterObject'] = new BX.UI.Counter({
	              value: filesCount,
	              color: BX.UI.Counter.Color.GRAY,
	              animate: true
	            });
	            var container = fileButton.querySelector('span');
	            container.appendChild(fileButton['counterObject'].getContainer());
	          } else {
	            fileButton['counterObject'].update(filesCount);
	          }
	        }
	        fileButton.setAttribute('data-bx-files-count', filesCount);
	      });
	      main_core_events.EventEmitter.subscribe(editor.getEventObject(), 'onShowControllers:File:Decrement', function (_ref3) {
	        var data = _ref3.data;
	        var count = data > 0 ? data : 1;
	        var filesCount = Math.max(parseInt(fileButton.getAttribute('data-bx-files-count') || 0) - count, 0);
	        fileButton.setAttribute('data-bx-files-count', filesCount);
	        if (fileButton['counterObject']) {
	          fileButton['counterObject'].update(filesCount);
	        }
	      });
	    }
	  }
	  if (toolbar.querySelector('[data-id="search-tag"]')) {
	    window['BXPostFormTags_' + editor.getFormId()] = new BXPostFormTags(editor.getFormId(), toolbar.querySelector('[data-id="search-tag"]'));
	  }
	  if (toolbar.querySelector('[data-id="create-link"]')) {
	    toolbar.querySelector('[data-id="create-link"]').addEventListener('click', function (event) {
	      htmlEditor.toolbar.controls.InsertLink.OnClick(event);
	    });
	  }
	  if (toolbar.querySelector('[data-id="video"]')) {
	    toolbar.querySelector('[data-id="video"]').addEventListener('click', function (event) {
	      htmlEditor.toolbar.controls.InsertVideo.OnClick(event);
	    });
	  }
	  if (toolbar.querySelector('[data-id="quote"]')) {
	    var quoteNode = toolbar.querySelector('[data-id="quote"]');
	    quoteNode.setAttribute('data-bx-type', 'action');
	    quoteNode.setAttribute('data-bx-action', 'quote');
	    quoteNode.addEventListener('mousedown', function (event) {
	      htmlEditor.toolbar.controls.Quote.OnMouseDown.apply(htmlEditor.toolbar.controls.Quote, [event]);
	      htmlEditor.CheckCommand(quoteNode);
	    });
	  }
	  if (editor.getContainer().querySelector('[data-bx-role="button-show-panel-editor"]')) {
	    editor.getContainer().querySelector('[data-bx-role="button-show-panel-editor"]').addEventListener('click', function () {
	      editor.showPanelEditor();
	    });
	  }
	  var copilot = toolbar.querySelector('[data-id="copilot"]');
	  if (copilot) {
	    copilot.addEventListener('click', function () {
	      if (!editor.isTextCopilotEnabledBySettings()) {
	        top.BX.UI.InfoHelper.show('limit_copilot_off');
	        return;
	      }
	      editor.showCopilot();
	    });
	  }
	}

	var _templateObject$2;
	var intersectionObserver;
	function observeIntersection(entity, callback) {
	  if (!intersectionObserver) {
	    intersectionObserver = new IntersectionObserver(function (entries) {
	      entries.forEach(function (entry) {
	        if (entry.isIntersecting) {
	          intersectionObserver.unobserve(entry.target);
	          var observedCallback = entry.target.observedCallback;
	          delete entry.target.observedCallback;
	          setTimeout(observedCallback);
	        }
	      });
	    }, {
	      threshold: 0
	    });
	  }
	  entity.observedCallback = callback;
	  intersectionObserver.observe(entity);
	}
	var justCounter = 0;
	var Toolbar = /*#__PURE__*/function () {
	  function Toolbar(eventObject, container) {
	    babelHelpers.classCallCheck(this, Toolbar);
	    this.container = container.querySelector('[data-bx-role="toolbar"]');
	    this.adjustMorePosition = this.adjustMorePosition.bind(this);
	    this.moreItem = container.querySelector('[data-bx-role="toolbar-item-more"]');
	    this.moreItem.addEventListener('click', this.showSubmenu.bind(this));
	    observeIntersection(this.container, this.adjustMorePosition);
	    window.addEventListener('resize', this.adjustMorePosition);
	  }
	  babelHelpers.createClass(Toolbar, [{
	    key: "insertAfter",
	    value: function insertAfter(button, buttonId) {
	      if (!main_core.Type.isElementNode(button['BODY']) && !main_core.Type.isStringFilled(button['BODY'])) {
	        return;
	      }
	      var item = main_core.Tag.render(_templateObject$2 || (_templateObject$2 = babelHelpers.taggedTemplateLiteral(["<div class=\"main-post-form-toolbar-button\" data-bx-role=\"toolbar-item\"></div>"])));
	      if (main_core.Type.isElementNode(button['BODY'])) {
	        item.appendChild(button['BODY']);
	      } else {
	        item.innerHTML = button['BODY'];
	      }
	      if (button['ID']) {
	        item.setAttribute('data-id', button['ID']);
	      }
	      if (buttonId !== null) {
	        var found = false;
	        var itemBefore = null;
	        Array.from(this.container.querySelectorAll('[data-bx-role="toolbar-item"]')).forEach(function (toolbarItem) {
	          if (found === true && itemBefore === null) {
	            itemBefore = toolbarItem;
	          } else if (found === false && toolbarItem && toolbarItem.dataset && toolbarItem.dataset.id === buttonId) {
	            found = true;
	          }
	        });
	        if (itemBefore) {
	          itemBefore.parentNode.insertBefore(item, itemBefore);
	        }
	      }
	      if (!item.parentNode) {
	        this.container.appendChild(item);
	      }
	      this.adjustMorePosition();
	    }
	  }, {
	    key: "getItems",
	    value: function getItems() {
	      return Array.from(this.container.querySelectorAll('[data-bx-role="toolbar-item"]'));
	    }
	  }, {
	    key: "getVisibleItems",
	    value: function getVisibleItems() {
	      var _this = this;
	      var visibleItems = [];
	      Array.from(this.container.querySelectorAll('[data-bx-role="toolbar-item"]')).forEach(function (item) {
	        if (item.offsetTop > _this.container.clientHeight / 2) {
	          visibleItems.push(item);
	        }
	      });
	      return visibleItems;
	    }
	  }, {
	    key: "getHiddenItems",
	    value: function getHiddenItems() {
	      var hiddenItems = [];
	      Array.from(this.container.querySelectorAll('[data-bx-role="toolbar-item"]')).forEach(function (item) {
	        if (item.offsetTop > 0) {
	          hiddenItems.push(item);
	        }
	      });
	      return hiddenItems;
	    }
	  }, {
	    key: "adjustMorePosition",
	    value: function adjustMorePosition() {
	      var visibleItemsLength = this.getVisibleItems().length;
	      if (visibleItemsLength <= 0 || visibleItemsLength >= this.getItems().length) {
	        this.moreItem.style.display = 'none';
	      } else {
	        this.moreItem.style.display = '';
	      }
	    }
	  }, {
	    key: "getPopup",
	    value: function getPopup() {
	      var _this2 = this;
	      if (!this.popup) {
	        this.popup = main_popup.PopupManager.create({
	          id: 'main_post_form_toolbar_' + justCounter++,
	          className: 'main-post-form-toolbar-popup',
	          cacheable: false,
	          content: this.getPopupContainer(),
	          closeByEsc: true,
	          autoHide: true,
	          angle: true,
	          bindElement: this.moreItem,
	          offsetTop: -5,
	          offsetLeft: 5,
	          events: {
	            onClose: function onClose() {
	              Array.from(_this2.getPopupContainer().querySelectorAll('[data-bx-role="toolbar-item"]')).forEach(function (item) {
	                _this2.container.appendChild(item);
	              });
	              delete _this2.popup;
	            }
	          }
	        });
	      }
	      return this.popup;
	    }
	  }, {
	    key: "getPopupContainer",
	    value: function getPopupContainer() {
	      if (!this.popupContainer) {
	        this.popupContainer = document.createElement('DIV');
	      }
	      return this.popupContainer;
	    }
	  }, {
	    key: "showSubmenu",
	    value: function showSubmenu() {
	      var _this3 = this;
	      var hiddenItems = this.getHiddenItems();
	      if (hiddenItems.length <= 0) {
	        return;
	      }
	      hiddenItems.forEach(function (item) {
	        _this3.getPopupContainer().appendChild(item);
	      });
	      this.getPopup().show();
	    }
	  }]);
	  return Toolbar;
	}();

	var TasksLimit = /*#__PURE__*/function () {
	  function TasksLimit() {
	    babelHelpers.classCallCheck(this, TasksLimit);
	  }
	  babelHelpers.createClass(TasksLimit, null, [{
	    key: "showPopup",
	    value: function showPopup(params) {
	      var tasksLimitPopup = main_popup.PopupManager.getPopupById(this.getPopupId());
	      if (!tasksLimitPopup) {
	        tasksLimitPopup = new main_popup.Popup(this.getPopupId(), null, {
	          content: this.getTasksLimitPopupContent(),
	          lightShadow: false,
	          offsetLeft: 20,
	          autoHide: false,
	          angle: {
	            position: 'bottom'
	          },
	          closeByEsc: false,
	          closeIcon: true
	        });
	      }
	      tasksLimitPopup.setBindElement(params.bindPosition);
	      tasksLimitPopup.show();
	    }
	  }, {
	    key: "getPopupId",
	    value: function getPopupId() {
	      return 'bx-post-mention-tasks-limit-popup';
	    }
	  }, {
	    key: "getTasksLimitPopupContent",
	    value: function getTasksLimitPopupContent() {
	      return main_core.Dom.create('DIV', {
	        style: {
	          width: '400px',
	          padding: '10px'
	        },
	        children: [main_core.Dom.create('SPAN', {
	          html: main_core.Loc.getMessage('MPF_MENTION_TASKS_LIMIT').replace('#A_BEGIN#', '<a href="javascript:void(0);" onclick="BX.Main.PostFormTasksLimit.onClickTasksLimitPopupSlider(this);">').replace('#A_END#', '</a>')
	        })]
	      });
	    }
	  }, {
	    key: "onClickTasksLimitPopupSlider",
	    value: function onClickTasksLimitPopupSlider(bindElement) {
	      var _this = this;
	      BX.Runtime.loadExtension('ui.info-helper').then(function (_ref) {
	        var FeaturePromotersRegistry = _ref.FeaturePromotersRegistry;
	        if (FeaturePromotersRegistry) {
	          FeaturePromotersRegistry.getPromoter({
	            code: 'limit_tasks_observers_participants',
	            bindElement: bindElement
	          }).show();
	        } else {
	          _this.hidePopup();
	          BX.UI.InfoHelper.show('limit_tasks_observers_participants', {
	            isLimit: true,
	            limitAnalyticsLabels: {
	              module: 'tasks',
	              source: 'postForm',
	              subject: 'auditor'
	            }
	          });
	        }
	      });
	    }
	  }, {
	    key: "hidePopup",
	    value: function hidePopup() {
	      var tasksLimitPopup = main_popup.PopupManager.getPopupById(this.getPopupId());
	      if (tasksLimitPopup) {
	        tasksLimitPopup.close();
	      }
	    }
	  }]);
	  return TasksLimit;
	}();

	function _classStaticPrivateFieldSpecGet(receiver, classConstructor, descriptor) { _classCheckPrivateStaticAccess(receiver, classConstructor); _classCheckPrivateStaticFieldDescriptor(descriptor, "get"); return _classApplyDescriptorGet(receiver, descriptor); }
	function _classCheckPrivateStaticFieldDescriptor(descriptor, action) { if (descriptor === undefined) { throw new TypeError("attempted to " + action + " private static field before its declaration"); } }
	function _classCheckPrivateStaticAccess(receiver, classConstructor) { if (receiver !== classConstructor) { throw new TypeError("Private static access of wrong provenance"); } }
	function _classApplyDescriptorGet(receiver, descriptor) { if (descriptor.get) { return descriptor.get.call(receiver); } return descriptor.value; }
	var Editor = /*#__PURE__*/function () {
	  function Editor(options, editorParams) {
	    var _this = this;
	    babelHelpers.classCallCheck(this, Editor);
	    babelHelpers.defineProperty(this, "jobs", new Map());
	    babelHelpers.defineProperty(this, "editorParams", {
	      height: 100,
	      ctrlEnterHandler: null,
	      parsers: null,
	      showPanelEditor: false,
	      lazyLoad: true,
	      urlPreviewId: null,
	      tasksLimitExceeded: false
	    });
	    babelHelpers.defineProperty(this, "actionQueue", []);
	    this.id = options['id'];
	    this.name = options['name'];
	    this.formId = options['formId'];
	    this.eventNode = options.eventNode || document.querySelector('#div' + (this.name || this.id));
	    this.eventNode.dataset.bxHtmlEditable = 'Y';
	    this.formEntityType = null;
	    Editor.repo.set(this.getId(), this);
	    if (!main_core.Type.isArray(editorParams.parsers) && main_core.Type.isPlainObject(editorParams.parsers)) {
	      editorParams.parsers = Object.values(editorParams.parsers);
	    }
	    this.setEditorParams(editorParams);
	    this.bindEvents(window['BXHtmlEditor'] ? window['BXHtmlEditor'].Get(this.getId()) : null);
	    this.toolbar = new Toolbar(this.getEventObject(), this.getContainer());
	    this.inited = true;
	    if (this.name !== null) {
	      window[this.name] = this;
	    }
	    BX.onCustomEvent(this, 'onInitialized', [this, this.getFormId()]);

	    //region Compatibility for crm.timeline
	    main_core_events.EventEmitter.subscribe(this.getEventObject(), 'OnFileUploadSuccess', function (_ref) {
	      var compatData = _ref.compatData;
	      BX.onCustomEvent(_this.getEventObject(), 'onFileIsAdded', compatData);
	    });
	    //endregion

	    main_core_events.EventEmitter.subscribe(this.getEventObject(), 'onBusy', function (_ref2) {
	      var handler = _ref2.data;
	      if (_this.jobs.size <= 0) {
	        main_core_events.EventEmitter.emit(_this.getEventObject(), 'onLHEIsBusy');
	      }
	      _this.jobs.set(handler, (_this.jobs.get(handler) || 0) + 1);
	    });
	    main_core_events.EventEmitter.subscribe(this.getEventObject(), 'onReady', function (_ref3) {
	      var handler = _ref3.data;
	      if (_this.jobs.size <= 0 || !_this.jobs.has(handler)) {
	        return;
	      }
	      var counter = _this.jobs.get(handler);
	      if (counter <= 1) {
	        _this.jobs["delete"](handler);
	        if (_this.jobs.size <= 0) {
	          main_core_events.EventEmitter.emit(_this.getEventObject(), 'onLHEIsReady');
	        }
	      } else {
	        _this.jobs.set(handler, --counter);
	      }
	    });
	  }
	  babelHelpers.createClass(Editor, [{
	    key: "setEditorParams",
	    value: function setEditorParams(editorParams) {
	      this.editorParams = Object.assign(this.editorParams, editorParams);
	    }
	  }, {
	    key: "bindEvents",
	    value: function bindEvents() {
	      var _this2 = this;
	      var htmlEditor = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
	      this.events = {};
	      [['OnEditorInitedBefore', this.OnEditorInitedBefore.bind(this)], ['OnCreateIframeAfter', this.OnCreateIframeAfter.bind(this)], ['OnEditorInitedAfter', this.OnEditorInitedAfter.bind(this)]].forEach(function (_ref4) {
	        var _ref5 = babelHelpers.slicedToArray(_ref4, 2),
	          eventName = _ref5[0],
	          closure = _ref5[1];
	        if (!htmlEditor) {
	          _this2.events[eventName] = function (htmlEditor) {
	            if (htmlEditor.id === _this2.getId()) {
	              //!it important to use deprecated eventEmitter
	              BX.removeCustomEvent(eventName, _this2.events[eventName]);
	              delete _this2.events[eventName];
	              closure(htmlEditor);
	            }
	          };
	          //!it important to use deprecated eventEmitter
	          BX.addCustomEvent(eventName, _this2.events[eventName]);
	        } else {
	          closure(htmlEditor);
	        }
	      });
	      main_core_events.EventEmitter.subscribe(this.getEventObject(), 'OnShowLHE', this.OnShowLHE.bind(this));
	      main_core_events.EventEmitter.subscribe(this.getEventObject(), 'OnButtonClick', this.OnButtonClick.bind(this));
	      main_core_events.EventEmitter.subscribe(this.getEventObject(), 'OnParserRegister', function (_ref6) {
	        var parser = _ref6.data;
	        _this2.addParser(parser);
	      });
	      main_core_events.EventEmitter.subscribe(this.getEventObject(), 'OnGetHTMLEditor', function (_ref7) {
	        var someObjectToReceiveHTMLEditor = _ref7.data;
	        someObjectToReceiveHTMLEditor.htmlEditor = _this2.getEditor();
	      });
	      main_core_events.EventEmitter.subscribe(this.getEventObject(), 'OnInsertContent', function (_ref8) {
	        var _ref8$data = babelHelpers.slicedToArray(_ref8.data, 2),
	          text = _ref8$data[0],
	          html = _ref8$data[1];
	        _this2.insertContent(text, html);
	      });
	      main_core_events.EventEmitter.subscribe(this.getEventObject(), 'OnAddButton', function (_ref9) {
	        var _ref9$data = babelHelpers.slicedToArray(_ref9.data, 2),
	          button = _ref9$data[0],
	          beforeButton = _ref9$data[1];
	        _this2.getToolbar().insertAfter(button, beforeButton);
	      });
	      bindHTML(this);
	    }
	  }, {
	    key: "getId",
	    value: function getId() {
	      return this.id;
	    }
	  }, {
	    key: "setEditor",
	    value: function setEditor(htmlEditor) {
	      var _this3 = this;
	      if (this.htmlEditor === htmlEditor) {
	        return;
	      }
	      this.htmlEditor = htmlEditor;
	      htmlEditor.formID = this.getFormId();
	      main_core_events.EventEmitter.subscribe(htmlEditor, 'OnCtrlEnter', function () {
	        htmlEditor.SaveContent();
	        if (main_core.Type.isFunction(_this3.editorParams.ctrlEnterHandler)) {
	          _this3.editorParams.ctrlEnterHandler();
	        } else if (main_core.Type.isStringFilled(_this3.editorParams.ctrlEnterHandler) && window[_this3.editorParams.ctrlEnterHandler]) {
	          window[_this3.editorParams.ctrlEnterHandler]();
	        } else if (document.forms[_this3.getFormId()]) {
	          BX.submit(document.forms[_this3.getFormId()]);
	        }
	      });
	      this.editorParams['height'] = htmlEditor.config['height'];
	      console.groupCollapsed('main.post.form: parsers: ', this.getId());
	      this.editorParams.parsers.forEach(function (parserId) {
	        var parser = getKnownParser(parserId, _this3, htmlEditor);
	        if (parser) {
	          console.groupCollapsed(parserId);
	          console.log(parser);
	          if (parser.hasButton()) {
	            htmlEditor.AddButton(parser.getButton());
	          }
	          htmlEditor.AddParser(parser.getParser());
	          console.groupEnd(parserId);
	        }
	      });
	      console.groupEnd('main.post.form: parsers: ', this.getId());

	      //region Catching external files
	      // paste an image from IO buffer into editor
	      main_core_events.EventEmitter.subscribe(htmlEditor, 'OnImageDataUriHandle', function (_ref10) {
	        var _ref10$compatData = babelHelpers.slicedToArray(_ref10.compatData, 2),
	          editor = _ref10$compatData[0],
	          imageBase64 = _ref10$compatData[1];
	        var blob = BX.UploaderUtils.dataURLToBlob(imageBase64.src);
	        if (blob && blob.size > 0 && blob.type.indexOf('image/') === 0) {
	          main_core_events.EventEmitter.emit(_this3.getEventObject(), 'onShowControllers', 'show');
	          blob.name = blob.name || imageBase64.title || 'image.' + blob.type.substr(6);
	          blob.referrerToEditor = imageBase64;
	          main_core_events.EventEmitter.emit(_this3.getEventObject(), 'OnImageHasCaught', new main_core_events.BaseEvent({
	            data: blob
	          })).forEach(function (result) {
	            result.then(function (_ref11) {
	              var image = _ref11.image,
	                html = _ref11.html;
	              main_core_events.EventEmitter.emit(htmlEditor, 'OnImageDataUriCaughtUploaded', new main_core_events.BaseEvent({
	                compatData: [imageBase64, image, {
	                  replacement: html
	                }]
	              }));
	            })["catch"](function () {
	              main_core_events.EventEmitter.emit(htmlEditor, 'OnImageDataUriCaughtFailed', new main_core_events.BaseEvent({
	                compatData: [imageBase64]
	              }));
	            });
	          });
	        }
	      });

	      // paste a video into editor
	      main_core_events.EventEmitter.subscribe(main_core_events.EventEmitter.GLOBAL_TARGET, 'onAddVideoMessage', function (_ref12) {
	        var _ref12$compatData = babelHelpers.slicedToArray(_ref12.compatData, 2),
	          file = _ref12$compatData[0],
	          formID = _ref12$compatData[1];
	        if (!formID || _this3.getFormId() !== formID) {
	          return;
	        }
	        main_core_events.EventEmitter.emit(_this3.getEventObject(), 'onShowControllers', 'show');
	        main_core_events.EventEmitter.emit(_this3.getEventObject(), 'OnVideoHasCaught', new main_core_events.BaseEvent({
	          data: file
	        }));
	      });
	      // DnD

	      if (this.editorParams.isDnDEnabled) {
	        (function () {
	          var placeHolder = BX('micro' + (_this3.name || _this3.id));
	          var active = false;
	          var timeoutId = 0;
	          var activate = function activate(e) {
	            e.preventDefault();
	            e.stopPropagation();
	            if (timeoutId > 0) {
	              clearTimeout(timeoutId);
	              timeoutId = 0;
	            }
	            if (active === true) {
	              return;
	            }
	            var isFileTransfer = e && e['dataTransfer'] && e['dataTransfer']['types'] && e['dataTransfer']['types'].indexOf('Files') >= 0;
	            if (isFileTransfer) {
	              active = true;
	              _this3.getContainer().classList.add('feed-add-post-dnd-over');
	              if (placeHolder) {
	                placeHolder.classList.add('feed-add-post-micro-dnd-ready');
	              }
	            }
	            return true;
	          };
	          var disActivate = function disActivate(e) {
	            e.preventDefault();
	            e.stopPropagation();
	            if (timeoutId > 0) {
	              clearTimeout(timeoutId);
	            }
	            timeoutId = setTimeout(function () {
	              active = false;
	              _this3.getContainer().classList.remove('feed-add-post-dnd-over');
	              if (placeHolder) {
	                placeHolder.classList.remove('feed-add-post-micro-dnd-ready');
	              }
	            }, 100);
	            return false;
	          };
	          var catchFiles = function catchFiles(e) {
	            disActivate(e);
	            if (e && e['dataTransfer'] && e['dataTransfer']['types'] && e['dataTransfer']['types'].indexOf('Files') >= 0 && e['dataTransfer']['files'] && e['dataTransfer']['files'].length > 0) {
	              main_core_events.EventEmitter.emit(_this3.getEventObject(), 'OnShowLHE', new main_core_events.BaseEvent({
	                compatData: ['justShow', {
	                  onShowControllers: 'show'
	                }]
	              }));
	              main_core_events.EventEmitter.emit(_this3.getEventObject(), 'onFilesHaveCaught', new main_core_events.BaseEvent({
	                data: e['dataTransfer']['files']
	              }));
	              main_core_events.EventEmitter.emit(_this3.getEventObject(), 'onFilesHaveDropped', {
	                event: e
	              });
	            }
	            return false;
	          };
	          _this3.getContainer().addEventListener('dragover', activate);
	          _this3.getContainer().addEventListener('dragenter', activate);
	          _this3.getContainer().addEventListener('dragleave', disActivate);
	          _this3.getContainer().addEventListener('dragexit', disActivate);
	          _this3.getContainer().addEventListener('drop', catchFiles);
	          _this3.getContainer().setAttribute('dropzone', 'copy f:*\/*');
	          if (!document.body.hasAttribute('dropzone')) {
	            document.body.setAttribute('dropzone', 'copy f:*/*');
	            document.body.addEventListener('dragover', function (e) {
	              e.preventDefault();
	              e.stopPropagation();
	              return true;
	            });
	            document.body.addEventListener('drop', function (e) {
	              e.preventDefault();
	              e.stopPropagation();
	              if (e && e['dataTransfer'] && e['dataTransfer']['types'] && e['dataTransfer']['types'].indexOf('Files') >= 0 && e['dataTransfer']['files'] && e['dataTransfer']['files'].length > 0) {
	                var lhe;
	                var iteratorBuffer;
	                var iterator = _classStaticPrivateFieldSpecGet(this.constructor, Editor, _shownForms).keys();
	                while ((iteratorBuffer = iterator.next()) && iteratorBuffer.done !== true && iteratorBuffer.value) {
	                  lhe = iteratorBuffer.value;
	                }
	                if (lhe) {
	                  main_core_events.EventEmitter.emit(lhe.getEventObject(), 'OnShowLHE', new main_core_events.BaseEvent({
	                    compatData: ['justShow', {
	                      onShowControllers: 'show'
	                    }]
	                  }));
	                  main_core_events.EventEmitter.emit(lhe.getEventObject(), 'onFilesHaveCaught', new main_core_events.BaseEvent({
	                    data: e['dataTransfer']['files']
	                  }));
	                  main_core_events.EventEmitter.emit(lhe.getEventObject(), 'onFilesHaveDropped', {
	                    event: e
	                  });
	                }
	              }
	              return false;
	            }.bind(_this3));
	          }
	          if (placeHolder) {
	            placeHolder.addEventListener('dragenter', function (e) {
	              activate(e);
	              main_core_events.EventEmitter.emit(_this3.getEventObject(), 'OnShowLHE', new main_core_events.BaseEvent({
	                compatData: ['justShow', {
	                  onShowControllers: 'show'
	                }]
	              }));
	            });
	          }
	          main_core_events.EventEmitter.subscribe(_this3.getEditor(), 'OnIframeDrop', function (_ref13) {
	            var _ref13$data = babelHelpers.slicedToArray(_ref13.data, 1),
	              e = _ref13$data[0];
	            return catchFiles(e);
	          });
	          main_core_events.EventEmitter.subscribe(_this3.getEditor(), 'OnIframeDragOver', function (_ref14) {
	            var _ref14$data = babelHelpers.slicedToArray(_ref14.data, 1),
	              e = _ref14$data[0];
	            return activate(e);
	          });
	          main_core_events.EventEmitter.subscribe(_this3.getEditor(), 'OnIframeDragLeave', function (_ref15) {
	            var _ref15$data = babelHelpers.slicedToArray(_ref15.data, 1),
	              e = _ref15$data[0];
	            return disActivate(e);
	          });
	        })();
	      }
	      //endregion

	      main_core_events.EventEmitter.subscribe(htmlEditor, 'OnInsertContent', function (_ref16) {
	        var _ref16$data = babelHelpers.slicedToArray(_ref16.data, 2),
	          text = _ref16$data[0],
	          html = _ref16$data[1];
	        _this3.insertContent(text, html);
	      });

	      //region Visible customization
	      showPanelEditor(this, htmlEditor, this.editorParams);
	      showUrlPreview(htmlEditor, this.editorParams);
	      customizeHTMLEditor(this, htmlEditor);
	      bindAutoSave(htmlEditor, BX(this.getFormId()));
	      bindToolbar(this, htmlEditor);
	      //endregion
	      main_core_events.EventEmitter.subscribe(this.getEventObject(), 'OnAfterShowLHE', function () {
	        _this3.getEditor().AllowBeforeUnloadHandler();
	      });
	      main_core_events.EventEmitter.subscribe(this.getEventObject(), 'OnAfterHideLHE', function () {
	        TasksLimit.hidePopup();
	        _this3.getEditor().DenyBeforeUnloadHandler();
	      });
	      main_core_events.EventEmitter.subscribe(htmlEditor, 'OnIframeClick', function () {
	        var event = new MouseEvent('click', {
	          bubbles: true,
	          cancelable: true,
	          view: window
	        });
	        htmlEditor.iframeView.container.dispatchEvent(event);
	      });
	    }
	  }, {
	    key: "getEditor",
	    value: function getEditor() {
	      return this.htmlEditor;
	    }
	  }, {
	    key: "getFormId",
	    value: function getFormId() {
	      return this.formId;
	    }
	  }, {
	    key: "getEventObject",
	    value: function getEventObject() {
	      return this.eventNode;
	    }
	  }, {
	    key: "getContainer",
	    value: function getContainer() {
	      return this.eventNode;
	    }
	  }, {
	    key: "getToolbar",
	    value: function getToolbar() {
	      return this.toolbar;
	    }
	  }, {
	    key: "OnEditorInitedBefore",
	    value: function OnEditorInitedBefore(htmlEditor) {
	      this.setEditor(htmlEditor);
	    }
	  }, {
	    key: "OnCreateIframeAfter",
	    value: function OnCreateIframeAfter() {
	      if (this.editorIsLoaded !== true) {
	        this.editorIsLoaded = true;
	        this.exec();
	        main_core_events.EventEmitter.emit(this, 'OnEditorIsLoaded', []);
	      }
	    }
	  }, {
	    key: "OnEditorInitedAfter",
	    value: function OnEditorInitedAfter(htmlEditor) {
	      if (!this.editorParams.lazyLoad) {
	        main_core_events.EventEmitter.emit(this.getEventObject(), 'OnShowLHE', new main_core_events.BaseEvent({
	          compatData: ['justShow', htmlEditor, false]
	        }));
	      }
	      if (htmlEditor.sandbox && htmlEditor.sandbox.inited) {
	        this.OnCreateIframeAfter();
	      }
	    }
	  }, {
	    key: "addParser",
	    value: function addParser(parser) {
	      var _this4 = this;
	      this.exec(function () {
	        parser.init(_this4.getEditor());
	        _this4.getEditor().AddParser({
	          name: parser.id,
	          obj: {
	            Parse: function Parse(parserId, text) {
	              return parser.parse(text);
	            },
	            UnParse: parser.unparse
	          }
	        });
	        if (!_this4['addParserAfterDebounced']) {
	          _this4.addParserAfterDebounced = main_core.Runtime.debounce(function () {
	            var content = _this4.getEditor().GetContent();
	            if (/&#9[13];/gi.test(content)) {
	              _this4.getEditor().SetContent(content.replace(/&#91;/ig, "[").replace(/&#93;/ig, "]"), true);
	            }
	          }, 100);
	        }
	        _this4.addParserAfterDebounced();
	      });
	    }
	  }, {
	    key: "insertContent",
	    value: function insertContent(text) {
	      var _this5 = this;
	      var html = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
	      this.exec(function () {
	        var editorMode = _this5.getEditor().GetViewMode();
	        if (editorMode === 'wysiwyg') {
	          var range = _this5.getEditor().selection.GetRange();
	          _this5.getEditor().InsertHtml(html || text, range);
	          setTimeout(_this5.getEditor().AutoResizeSceleton.bind(_this5.getEditor()), 500);
	          setTimeout(_this5.getEditor().AutoResizeSceleton.bind(_this5.getEditor()), 1000);
	        } else {
	          _this5.getEditor().textareaView.Focus();
	          if (!_this5.getEditor().bbCode) {
	            var doc = _this5.getEditor().GetIframeDoc();
	            var dummy = doc.createElement('DIV');
	            dummy.style.display = 'none';
	            dummy.innerHTML = text;
	            doc.body.appendChild(dummy);
	            text = _this5.getEditor().Parse(text, true, false);
	            dummy.parentNode.removeChild(dummy);
	          }
	          _this5.getEditor().textareaView.WrapWith('', '', text);
	        }
	      });
	    }
	  }, {
	    key: "reinit",
	    value: function reinit(text, data) {
	      var _this6 = this;
	      var showControllers = 'hide';
	      if (main_core.Type.isPlainObject(data) && Object.values(data).length) {
	        Object.values(data).forEach(function (property) {
	          if (property && property['VALUE']) {
	            showControllers = 'show';
	          }
	        });
	      }
	      main_core_events.EventEmitter.emitAsync(this.getEventObject(), 'onReinitializeBeforeAsync', [text, data]).then(function () {
	        main_core_events.EventEmitter.emit(_this6.getEventObject(), 'onShowControllers', showControllers);
	        main_core_events.EventEmitter.emit(_this6.getEventObject(), 'onReinitializeBefore', [text, data]);
	        _this6.getEditor().CheckAndReInit(main_core.Type.isString(text) ? text : '');
	        BX.onCustomEvent(_this6.getEditor(), 'onReinitialize', [_this6, text, data]);
	        if (_this6.editorParams['height']) {
	          _this6.oEditor.SetConfigHeight(_this6.editorParams['height']);
	          _this6.oEditor.ResizeSceleton();
	        }
	      });
	    }
	  }, {
	    key: "OnShowLHE",
	    value: function OnShowLHE(_ref17) {
	      var _this7 = this;
	      var data = _ref17.data,
	        compatData = _ref17.compatData;
	      var _ref18 = data || compatData,
	        _ref19 = babelHelpers.slicedToArray(_ref18, 3),
	        show = _ref19[0],
	        setFocus = _ref19[1],
	        FCFormId = _ref19[2];
	      if (!this.getEditor() && window['BXHtmlEditor']) {
	        window['BXHtmlEditor'].Get(this.getId()).Init();
	      }
	      show = show === false || show === 'hide' || show === 'justShow' ? show : true;
	      var placeHolder = BX('micro' + (this.name || this.id));
	      if (placeHolder) {
	        placeHolder.style.display = show === true || show === 'justShow' ? 'none' : 'block';
	      }
	      if (show === 'hide') {
	        _classStaticPrivateFieldSpecGet(this.constructor, Editor, _shownForms)["delete"](this);
	        main_core_events.EventEmitter.emit(this.getEventObject(), 'OnBeforeHideLHE');
	        if (this.getContainer().style.display === 'none') {
	          main_core_events.EventEmitter.emit(this.getEventObject(), 'OnAfterHideLHE');
	          main_core_events.EventEmitter.emit(this.getEventObject(), 'onShowControllers', 'hide');
	        } else {
	          new BX['easing']({
	            duration: 200,
	            start: {
	              opacity: 100,
	              height: this.getContainer().scrollHeight
	            },
	            finish: {
	              opacity: 0,
	              height: 20
	            },
	            transition: BX.easing.makeEaseOut(BX.easing.transitions.quad),
	            step: function step(state) {
	              _this7.getContainer().style.height = state.height + 'px';
	              _this7.getContainer().style.opacity = state.opacity / 100;
	            },
	            complete: function complete() {
	              _this7.getContainer().style.cssText = '';
	              _this7.getContainer().style.display = 'none';
	              main_core_events.EventEmitter.emit(_this7.getEventObject(), 'OnAfterHideLHE');
	              main_core_events.EventEmitter.emit(_this7.getEventObject(), 'onShowControllers', 'hide');
	            }
	          }).animate();
	        }
	      } else if (show) {
	        _classStaticPrivateFieldSpecGet(this.constructor, Editor, _shownForms).set(this);
	        this.formEntityType = main_core.Type.isArray(FCFormId) && main_core.Type.isStringFilled(FCFormId[0]) && FCFormId[0].match(/^TASK_(\d+)$/i) ? 'task' : null;
	        if (setFocus && main_core.Type.isPlainObject(setFocus)) {
	          if (setFocus['onShowControllers']) {
	            main_core_events.EventEmitter.emit(this.getEventObject(), 'onShowControllers', setFocus['onShowControllers']);
	          }
	        }
	        main_core_events.EventEmitter.emit(this.getEventObject(), 'OnBeforeShowLHE');
	        if (show === 'justShow' || this.getContainer().style.display === 'block') {
	          this.getContainer().style.display = 'block';
	          main_core_events.EventEmitter.emit(this.getEventObject(), 'OnAfterShowLHE'); //To remember: Here is set a text -> reinitData-> reinit -> editor.CheckAndReInit()
	          if (setFocus !== false) {
	            this.getEditor().Focus();
	          }
	        } else {
	          main_core.Dom.adjust(this.getContainer(), {
	            style: {
	              display: 'block',
	              overflow: 'hidden',
	              height: '20px',
	              opacity: 0.1
	            }
	          });
	          new BX['easing']({
	            duration: 200,
	            start: {
	              opacity: 10,
	              height: 20
	            },
	            finish: {
	              opacity: 100,
	              height: this.getContainer().scrollHeight
	            },
	            transition: BX.easing.makeEaseOut(BX.easing.transitions.quad),
	            step: function step(state) {
	              _this7.getContainer().style.height = state.height + 'px';
	              _this7.getContainer().style.opacity = state.opacity / 100;
	            },
	            complete: function complete() {
	              main_core_events.EventEmitter.emit(_this7.getEventObject(), 'OnAfterShowLHE'); //To remember: Here is set a text -> reinitData-> reinit -> editor.CheckAndReInit()
	              _this7.getEditor().Focus();
	              _this7.getContainer().style.cssText = "";
	            }
	          }).animate();
	        }
	      } else {
	        _classStaticPrivateFieldSpecGet(this.constructor, Editor, _shownForms)["delete"](this);
	        main_core_events.EventEmitter.emit(this.getEventObject(), 'OnBeforeHideLHE');
	        main_core_events.EventEmitter.emit(this.getEventObject(), 'onShowControllers', 'hide');
	        this.getContainer().style.display = 'none';
	        main_core_events.EventEmitter.emit(this.getEventObject(), 'OnAfterHideLHE');
	      }
	    }
	  }, {
	    key: "OnButtonClick",
	    value: function OnButtonClick(_ref20) {
	      var _ref20$data = babelHelpers.slicedToArray(_ref20.data, 1),
	        action = _ref20$data[0];
	      if (action !== 'cancel') {
	        var res = {
	          result: true
	        };
	        main_core_events.EventEmitter.emit(this.getEventObject(), 'OnClickBeforeSubmit', new main_core_events.BaseEvent({
	          compatData: [this, res]
	        }));
	        if (res['result'] !== false) {
	          main_core_events.EventEmitter.emit(this.getEventObject(), 'OnClickSubmit', new main_core_events.BaseEvent({
	            compatData: [this]
	          }));
	        }
	      } else {
	        main_core_events.EventEmitter.emit(this.getEventObject(), 'OnClickCancel', new main_core_events.BaseEvent({
	          compatData: [this]
	        }));
	        main_core_events.EventEmitter.emit(this.getEventObject(), 'OnShowLHE', new main_core_events.BaseEvent({
	          compatData: ['hide']
	        }));
	      }
	    } //region compatibility
	  }, {
	    key: "exec",
	    value: function exec(func, args) {
	      if (typeof func == 'function') {
	        this.actionQueue.push([func, args]);
	      }
	      if (this.editorIsLoaded === true) {
	        var res;
	        while ((res = this.actionQueue.shift()) && res) {
	          res[0].apply(this, res[1]);
	        }
	      }
	    }
	  }, {
	    key: "showPanelEditor",
	    value: function showPanelEditor$$1() {
	      showPanelEditor(this, this.getEditor(), {});
	    }
	  }, {
	    key: "getContent",
	    value: function getContent() {
	      return this.oEditor ? this.oEditor.GetContent() : '';
	    }
	  }, {
	    key: "setContent",
	    value: function setContent(text) {
	      if (this.getEditor()) {
	        this.getEditor().SetContent(text);
	      }
	    }
	  }, {
	    key: "controllerInit",
	    value: function controllerInit(status) {
	      main_core_events.EventEmitter.emit(this.getEventObject(), 'onShowControllers', status === 'hide' ? 'hide' : 'show');
	    }
	  }, {
	    key: "showCopilot",
	    value: function showCopilot() {
	      this.getEditor().SetView('wysiwyg');
	      this.getEditor().ShowCopilotAtTheBottom();
	    }
	  }, {
	    key: "isTextCopilotEnabledBySettings",
	    value: function isTextCopilotEnabledBySettings() {
	      var isEnabled = this.getEditor().config.isCopilotTextEnabledBySettings;
	      return main_core.Type.isNil(isEnabled) || isEnabled;
	    }
	  }, {
	    key: "isImageCopilotEnabledBySettings",
	    value: function isImageCopilotEnabledBySettings() {
	      var isEnabled = this.getEditor().config.isCopilotImageEnabledBySettings;
	      return main_core.Type.isNil(isEnabled) || isEnabled;
	    }
	  }, {
	    key: "isReady",
	    get: function get() {
	      return this.editorIsLoaded;
	    }
	  }, {
	    key: "oEditor",
	    get: function get() {
	      return this.getEditor();
	    }
	  }, {
	    key: "oEditorId",
	    get: function get() {
	      return this.getId();
	    }
	  }, {
	    key: "formID",
	    get: function get() {
	      return this.getFormId();
	    }
	  }, {
	    key: "params",
	    get: function get() {
	      return {
	        formID: this.getFormId()
	      };
	    }
	  }, {
	    key: "controllers",
	    get: function get() {
	      var event = new main_core_events.BaseEvent();
	      var data = {};
	      event.setData(data);
	      main_core_events.EventEmitter.emit(this.getEventObject(), 'onCollectControllers', event);
	      var result = {};
	      Object.keys(data).forEach(function (fieldName) {
	        result[fieldName] = Object.assign({}, data[fieldName]);
	        result[fieldName]['values'] = {};
	        if (main_core.Type.isArray(data[fieldName]['values'])) {
	          data[fieldName]['values'].forEach(function (id) {
	            result[fieldName]['values'][id] = {
	              id: id
	            };
	          });
	        } else if (main_core.Type.isPlainObject(data[fieldName]['values'])) {
	          result[fieldName]['values'] = Object.assign({}, data[fieldName]['values']);
	        }
	      });
	      return result;
	    }
	  }, {
	    key: "arFiles",
	    get: function get() {
	      var event = new main_core_events.BaseEvent();
	      var data = {};
	      event.setData(data);
	      main_core_events.EventEmitter.emit(this.getEventObject(), 'onCollectControllers', event);
	      var result = {};
	      Object.keys(data).forEach(function (fieldName) {
	        if (data[fieldName]['values']) {
	          data[fieldName]['values'].forEach(function (id) {
	            result[id] = [fieldName];
	          });
	        }
	      });
	      return result;
	    } //endregion
	  }]);
	  return Editor;
	}();
	babelHelpers.defineProperty(Editor, "repo", new Map());
	var _shownForms = {
	  writable: true,
	  value: new Map()
	};

	window['LHEPostForm'] = {
	  //region compatibility
	  getEditor: function getEditor(editor) {
	    return window["BXHtmlEditor"] ? window["BXHtmlEditor"].Get(babelHelpers["typeof"](editor) == "object" ? editor.id : editor) : null;
	  },
	  getHandler: function getHandler(editor) {
	    var id = main_core.Type.isStringFilled(editor) ? editor : editor.id;
	    return Editor.repo.get(id);
	  },
	  getHandlerByFormId: function getHandlerByFormId(formId) {
	    var result = null;
	    Editor.repo.forEach(function (editor) {
	      if (editor.getFormId() === formId) {
	        result = editor;
	      }
	    });
	    return result;
	  },
	  reinitData: function reinitData(editorID, text, data) {
	    var files = {};
	    if (!main_core.Type.isPlainObject(data)) {
	      data = {};
	    }
	    Object.entries(data).forEach(function (_ref) {
	      var _ref2 = babelHelpers.slicedToArray(_ref, 2),
	        userFieldName = _ref2[0],
	        userField = _ref2[1];
	      if (main_core.Type.isPlainObject(userField) && userField['USER_TYPE_ID'] && userField['VALUE'] && Object.values(userField['VALUE']).length > 0) {
	        files[userFieldName] = userField;
	      }
	    });
	    var handler = this.getHandler(editorID);
	    if (handler && (handler.isReady || main_core.Type.isStringFilled(text) || Object.values(files).length > 0)) {
	      handler.exec(handler.reinit, [text, files]);
	    }
	    return false;
	  },
	  reinitDataBefore: function reinitDataBefore(editorID) {
	    var handler = Editor.repo.get(editorID);
	    if (handler && handler.getEventObject()) {
	      main_core_events.EventEmitter.emit(handler.getEventObject(), 'onReinitializeBefore', [handler]);
	    }
	  }
	  //endregion
	};

	exports.PostForm = Editor;
	exports.PostFormTasksLimit = TasksLimit;

}((this.BX.Main = this.BX.Main || {}),BX.Event,BX,BX.Main,BX));



;(function(){
	if (window["BXPostFormTags"])
		return;
var repo = {
	selector : {},
	mentionParams: {},
};

window.BXPostFormTags = function(formID, buttonID)
{
	this.popup = null;
	this.formID = formID;
	this.buttonID = buttonID;
	this.sharpButton = null;
	this.addNewLink = null;
	this.tagsArea = null;
	this.hiddenField = null;
	this.popupContent = null;

	BX.ready(BX.proxy(this.init, this));
};

window.BXPostFormTags.prototype.init = function()
{
	this.sharpButton = BX(this.buttonID);
	this.addNewLink = BX("post-tags-add-new-" + this.formID);
	this.tagsArea = BX("post-tags-block-" + this.formID);
	this.tagsContainer = BX("post-tags-container-" + this.formID);
	this.hiddenField = BX("post-tags-hidden-" + this.formID);
	this.popupContent = BX("post-tags-popup-content-" + this.formID);
	this.popupInput = BX.findChild(this.popupContent, { tag : "input" });

	var tags = BX.findChildren(this.tagsContainer, { className : "feed-add-post-del-but" }, true);
	for (var i = 0, cnt = tags.length; i < cnt; i++ )
	{
		BX.bind(tags[i], "click", BX.proxy(this.onTagDelete, {
			obj : this,
			tagBox : tags[i].parentNode,
			tagValue : tags[i].parentNode.getAttribute("data-tag")
		}));
	}

	BX.bind(this.sharpButton, "click", BX.proxy(this.onButtonClick, this));
	BX.bind(this.addNewLink, "click", BX.proxy(this.onAddNewClick, this));
};

window.BXPostFormTags.prototype.onTagDelete = function()
{
	BX.remove(this.tagBox);
	this.obj.hiddenField.value = this.obj.hiddenField.value.replace(this.tagValue + ',', '').replace('  ', ' ');
};

window.BXPostFormTags.prototype.show = function()
{
	if (this.popup === null)
	{
		this.popup = new BX.PopupWindow("bx-post-tag-popup", this.addNewLink, {
			content : this.popupContent,
			lightShadow : false,
			offsetTop: 8,
			offsetLeft: 10,
			autoHide: true,
			angle : true,
			closeByEsc: true,
			zIndex: -840,
			buttons: [
				new BX.PopupWindowButton({
					text : BX.message("TAG_ADD"),
					events : {
						click : BX.proxy(this.onTagAdd, this)
					}
				})
			]
		});

		BX.bind(this.popupInput, "keydown", BX.proxy(this.onKeyPress, this));
		BX.bind(this.popupInput, "keyup", BX.proxy(this.onKeyPress, this));
	}

	this.popup.show();
	BX.focus(this.popupInput);
};

window.BXPostFormTags.prototype.addTag = function(tagStr)
{
	var tags = BX.type.isNotEmptyString(tagStr) ? tagStr.split(",") : this.popupInput.value.split(",");
	var result = [];
	for (var i = 0; i < tags.length; i++ )
	{
		var tag = BX.util.trim(tags[i]);
		if (tag.length > 0)
		{
			var allTags = this.hiddenField.value.split(",");
			if (!BX.util.in_array(tag, allTags))
			{
				var newTagDelete;
				var newTag = BX.create("span", {
					children : [
						(newTagDelete = BX.create("span", { attrs : { "class": "feed-add-post-del-but" }}))
					],
					attrs : { "class": "feed-add-post-tags" }
				});

				newTag.insertBefore(document.createTextNode(tag), newTagDelete);
				this.tagsContainer.insertBefore(newTag, this.addNewLink);

				BX.bind(newTagDelete, "click", BX.proxy(this.onTagDelete, {
					obj : this,
					tagBox : newTag,
					tagValue : tag
				}));

				this.hiddenField.value += tag + ',';

				result.push(tag);
			}
		}
	}

	return result;
};

window.BXPostFormTags.prototype.onTagAdd = function()
{
	this.addTag();
	this.popupInput.value = "";
	this.popup.close();
};

window.BXPostFormTags.prototype.onAddNewClick = function(event)
{
	event = event || window.event;
	this.show();
	BX.PreventDefault(event);
};

window.BXPostFormTags.prototype.onButtonClick = function(event)
{
	event = event || window.event;
	BX.show(this.tagsArea);
	this.show();
	BX.PreventDefault(event);
};

window.BXPostFormTags.prototype.onKeyPress = function(event)
{
	event = event || window.event;
	var key = (event.keyCode ? event.keyCode : (event.which ? event.which : null));
	if (key == 13)
	{
		setTimeout(BX.proxy(this.onTagAdd, this), 0);
	}
};

window.BXPostFormImportant = function(formID, buttonID, inputName)
{
	if (inputName)
	{
		this.formID = formID;
		this.buttonID = buttonID;
		this.inputName = inputName;

		this.fireButton = null;
		this.activeBlock = null;
		this.hiddenField = null;

		BX.ready(BX.proxy(this.init, this));
	}

	return false;
};
window.BXPostFormImportant.prototype.init = function()
{
	this.fireButton = BX(this.buttonID);
	this.activeBlock = BX(this.buttonID + '-active');

	var form = BX(this.formID);
	if (form)
	{
		this.hiddenField = form[this.inputName];
		if (
			this.hiddenField
			&& this.hiddenField.value == 1
		)
		{
			this.showActive();
		}
	}

	BX.bind(this.fireButton, "click", BX.proxy(function(event) {
		event = event || window.event;
		this.showActive();
		BX.PreventDefault(event);
	}, this));

	BX.bind(this.activeBlock, "click", BX.proxy(function(event) {
		event = event || window.event;
		this.hideActive();
		BX.PreventDefault(event);
	}, this));
};
window.BXPostFormImportant.prototype.showActive = function(event)
{
	BX.hide(this.fireButton);
	BX.show(this.activeBlock, 'inline-block');

	if (this.hiddenField)
	{
		this.hiddenField.value = 1;
	}

	return false;
};
window.BXPostFormImportant.prototype.hideActive = function(event)
{
	BX.hide(this.activeBlock);
	BX.show(this.fireButton, 'inline-block');

	if (this.hiddenField)
	{
		this.hiddenField.value = 0;
	}

	return false;
};

var lastWaitElement = null;
window.MPFbuttonShowWait = function(el)
{
	if (el && !BX.type.isElementNode(el))
	{
		el = null;
	}

	el = el || this;
	el = (el ? (el.tagName == "A" ? el : el.parentNode) : el);
	if (el)
	{
		BX.addClass(el, "ui-btn-clock");
		lastWaitElement = el;
		BX.defer(function(){el.disabled = true})();
	}
};

var MPFMention = {
	listen: false,
	plus : false,
	text : '',
	bSearch: false,
	node: null,
	mode: null
};
BX.addCustomEvent(window, 'onInitialized', function(someObject) {
	if (someObject && someObject.eventNode)
	{
		BX.onCustomEvent(someObject.eventNode, 'OnClickCancel', function(){
			MPFMention.node = null;
		});
	}
});

BX.addCustomEvent(window, 'BX.MPF.MentionSelector:open', function(params) {

	var formId = (BX.Type.isStringFilled(params.formId) ? params.formId : '');
	if (
		!BX.Type.isStringFilled(formId)
		|| BX.Type.isUndefined(repo.mentionParams[formId])
	)
	{
		return;
	}

	var bindNode = (BX.Type.isDomNode(params.bindNode) ? params.bindNode : null);
	var bindPosition = (BX.type.isNotEmptyObject(params.bindPosition) ? params.bindPosition : null);

	var selectorId = window.MPFgetSelectorId('bx-mention-' + formId + '-id') + (bindNode ? '-withsearch' : '');
	var dialog = BX.UI.EntitySelector.Dialog.getById(selectorId);
	if (!dialog)
	{
		window.MPFcreateSelectorDialog({
			formId: formId,
			selectorId: selectorId,
			enableSearch: !!bindNode,
			params: repo.mentionParams[formId],
		});

		dialog = BX.UI.EntitySelector.Dialog.getById(selectorId);
	}

	if (!dialog)
	{
		return;
	}

	dialog.deselectAll();
	dialog.search('');
	dialog.show();

	var popupBindOptions = {};
	if (BX.Type.isDomNode(bindNode))
	{
		dialog.focusSearch();
		dialog.popup.setBindElement(bindNode);
		popupBindOptions.position = 'top';
	}
	else if (BX.type.isNotEmptyObject(bindPosition))
	{
		bindPosition.top -= 5;
		dialog.popup.setBindElement(bindPosition);
	}
	dialog.popup.adjustPosition(popupBindOptions);
});

window.onKeyDownHandler = function(e, editor, formID)
{
	var keyCode = e.keyCode;

	if (!window['BXfpdStopMent' + formID])
	{
		return true;
	}

	var selectorId = window.MPFgetSelectorId('bx-mention-' + formID + '-id');

	if (
		keyCode === editor.KEY_CODES['backspace']
		&& MPFMention.node
	)
	{
		var mentText = BX.util.trim(editor.util.GetTextContent(MPFMention.node));
		if (
			mentText === '+'
			|| mentText === '@'
			|| (
				MPFMention.mode == 'button'
				&& mentText.length == 1
			)
		)
		{
			window['BXfpdStopMent' + formID]();
		}
		else if (
			MPFMention.mode == 'button'
			&& mentText.length == 1
		)
		{
			window['BXfpdStopMent' + formID]();
		}
	}

	if (
		BX.util.in_array(keyCode, [ 107, 187 ])
		|| (
			(e.shiftKey || e.modifiers > 3)
			&& BX.util.in_array(keyCode, [ 50, 43, 61 ])
		)
		|| (
			e.altKey
			&& BX.util.in_array(keyCode, [ 76 ])
		) /* German @ == Alt + L*/
		|| (
			e.altKey
			&& e.ctrlKey
			&& BX.util.in_array(keyCode, [ 81 ])
			&& e.key === '@'
		) /* Win LA Spanish @ == Ctrl + Alt + Q */
		|| (
			e.altKey
			&& BX.util.in_array(keyCode, [ 71, 81 ])
			&& e.key === '@'
		) /* MacOS ES Spanish @ == Alt + G, MacOS LA Spanish @ = Alt + Q */
		|| (
			e.altKey
			&& BX.util.in_array(keyCode, [ 50 ])
			&& e.key === '@'
		) /* MacOS PT Portugal @ == Alt + 2 */
		|| (
			BX.Type.isFunction(e.getModifierState)
			&& !!e.getModifierState('AltGraph')
			&& BX.util.in_array(keyCode, [ 81, 50, 48 ])
			&& !BX.Type.isUndefined(e.key)
			&& e.key === '@'
		) /* Win German @ == AltGr + Q, Win Spanish @ == AltGr + 2, Win French @ == AltGr + 0 */
		|| (
			BX.util.in_array(keyCode, [ 192 ])
			&& e.key === '@'
		) /* MacOS FR */
	)
	{
		setTimeout(function()
		{
			var range = editor.selection.GetRange();
			var doc = editor.GetIframeDoc();
			var txt = (range ? range.endContainer.textContent : '');
			var determiner = (txt ? txt.slice(range.endOffset - 1, range.endOffset) : '');
			var prevS = (txt ? txt.slice(range.endOffset - 2, range.endOffset-1) : '');

			if (
				(determiner == "@" || determiner == "+")
				&& (
					!prevS
					|| BX.util.in_array(prevS, ["+", "@", ",", "("])
					|| (
						prevS.length == 1
						&& BX.util.trim(prevS) === ""
					)
				)
			)
			{
				MPFMention.listen = true;
				MPFMention.listenFlag = true;
				MPFMention.text = '';
				MPFMention.leaveContent = true;
				MPFMention.mode = 'plus';

				range.setStart(range.endContainer, range.endOffset - 1);
				range.setEnd(range.endContainer, range.endOffset);
				editor.selection.SetSelection(range);
				MPFMention.node = BX.create("SPAN", {props: {id: "bx-mention-node"}}, doc);
				editor.selection.Surround(MPFMention.node, range);
				range.setStart(MPFMention.node, 1);
				range.setEnd(MPFMention.node, 1);
				editor.selection.SetSelection(range);

				if (BX.Type.isStringFilled(selectorId))
				{
					BX.onCustomEvent(window, 'BX.MPF.MentionSelector:open', [{
						formId: formID,
						bindPosition: getMentionNodePosition(MPFMention.node, editor)
					}]);
				}
			}
		}, 10);
	}

	if (MPFMention.listen)
	{
		var activeDialogTab = null;
		var dialog = (
			BX.Type.isStringFilled(selectorId)
				? BX.UI.EntitySelector.Dialog.getById(selectorId)
				: null
		);
		if (
			dialog
			&& dialog.getActiveTab()
		)
		{
			activeDialogTab = dialog.getActiveTab().getId();
		}

		var key = null;
		switch (keyCode)
		{
			case editor.KEY_CODES.enter:
				key = 'Enter';
				break;
			case 9:
				key = 'Tab';
				break;
			case editor.KEY_CODES.up:
				key = 'ArrowUp';
				break;
			case editor.KEY_CODES.down:
				key = 'ArrowDown';
				break;
			case editor.KEY_CODES.left:
				if (activeDialogTab === 'departments')
				{
					key = 'ArrowLeft';
				}
				break;
			case editor.KEY_CODES.right:
				if (activeDialogTab === 'departments')
				{
					key = 'ArrowRight';
				}
				break;
		}

		if (key)
		{
			var event = new KeyboardEvent('keydown', {
				key: key,
				keyCode: keyCode,
				bubbles: true,
				cancelable: true,
				view: window,
			});

			if (!document.dispatchEvent(event))
			{
				editor.iframeKeyDownPreventDefault = true;
				e.stopPropagation();
				e.preventDefault();
			}
		}
	}

	if (
		!MPFMention.listen
		&& MPFMention.listenFlag
		&& keyCode === editor.KEY_CODES["enter"]
	)
	{
		var range = editor.selection.GetRange();
		if (range.collapsed)
		{
			var node = range.endContainer;
			var doc = editor.GetIframeDoc();

			if (node)
			{
				if (node.className !== 'bxhtmled-metion')
				{
					node = BX.findParent(node, function(n)
					{
						return n.className == 'bxhtmled-metion';
					}, doc.body);
				}

				if (node && node.className == 'bxhtmled-metion')
				{
					editor.selection.SetAfter(node);
				}
			}
		}
	}
};

window.onKeyUpHandler = function(e, editor, formID)
{
	var keyCode = e.keyCode;
	var range;
	var mentText;

	if (!window['BXfpdStopMent' + formID])
	{
		return true;
	}

	if (MPFMention.listen === true)
	{
		if (keyCode == editor.KEY_CODES.escape) //ESC
		{
			var event = new KeyboardEvent('keyup', {
				key: 'Escape',
				keyCode: keyCode,
				bubbles: true,
				cancelable: true,
				view: window,
			});

			if (!document.dispatchEvent(event))
			{
				e.stopPropagation();
				e.preventDefault();
			}

			window['BXfpdStopMent' + formID]();
		}
		else if (
			keyCode !== editor.KEY_CODES.enter
			&& keyCode !== editor.KEY_CODES.left
			&& keyCode !== editor.KEY_CODES.right
			&& keyCode !== editor.KEY_CODES.up
			&& keyCode !== editor.KEY_CODES.down
		)
		{
			if (BX(MPFMention.node))
			{
				mentText = BX.util.trim(editor.util.GetTextContent(MPFMention.node));
				var mentTextOrig = mentText;

				mentText = mentText.replace(/^[\+@]*/, '');
				MPFMention.bSearch = BX.Type.isStringFilled(mentText);

				var selectorId = window.MPFgetSelectorId('bx-mention-' + formID + '-id');
				var dialog = BX.UI.EntitySelector.Dialog.getById(selectorId);

				if (
					BX.Type.isStringFilled(mentText)
					&& dialog
				)
				{
					dialog.search(mentText);
				}

				if (
					MPFMention.leaveContent
					&& MPFMention._lastText
				)
				{
					if (mentTextOrig === '')
					{
						window['BXfpdStopMent' + formID]();
					}
					else if (
						mentTextOrig !== ''
						&& mentText === ''
					)
					{
						MPFMention.bSearch = false;
						if (dialog)
						{
							dialog.search('');
						}
					}
				}

				MPFMention.lastText = mentText;
				MPFMention._lastText = mentTextOrig;

			}
			else
			{
				window['BXfpdStopMent' + formID]();
			}
		}
	}
	else
	{
		if (
			!e.shiftKey &&
			(keyCode === editor.KEY_CODES["space"] ||
			keyCode === editor.KEY_CODES["escape"] ||
			keyCode === 188 ||
			keyCode === 190
			))
		{
			range = editor.selection.GetRange();
			if (range.collapsed)
			{
				var node = range.endContainer;
				var doc = editor.GetIframeDoc();

				if (node)
				{
					if (node.className !== 'bxhtmled-metion')
					{
						node = BX.findParent(node, function(n)
						{
							return n.className == 'bxhtmled-metion';
						}, doc.body);
					}

					if (node && node.className == 'bxhtmled-metion')
					{
						mentText = editor.util.GetTextContent(node);
						var matchSep = mentText.match(/[\s\.\,]$/);
						if (matchSep || keyCode === editor.KEY_CODES["escape"])
						{
							node.innerHTML = mentText.replace(/[\s\.\,]$/, '');
							var sepNode = BX.create('SPAN', {html: matchSep || editor.INVISIBLE_SPACE}, doc);
							editor.util.InsertAfter(sepNode, node);
							editor.selection.SetAfter(sepNode);
						}
					}
				}
			}
		}
	}
};

window.onTextareaKeyDownHandler = function(e, editor, formID)
{
	var keyCode = e.keyCode;

	if (MPFMention.listen && keyCode == editor.KEY_CODES.enter)
	{
		editor.textareaKeyDownPreventDefault = true;
		e.stopPropagation();
		e.preventDefault();
	}
};

window.onTextareaKeyUpHandler = function(e, editor, formID)
{
	var cursor = null;
	var value = '';
	var keyCode = e.keyCode;

	var selectorId = window.MPFgetSelectorId('bx-mention-' + formID + '-id');

	if (MPFMention.listen === true)
	{
		if (keyCode == 27) //ESC
		{
			window['BXfpdStopMent' + formID]();
		}
		else if (keyCode !== 13)
		{
			value = editor.textareaView.GetValue(false);
			cursor = editor.textareaView.GetCursorPosition();

			var mentText = '';
			var mentTextOrig = '';

			if (value.indexOf('+') !== -1 || value.indexOf('@') !== -1)
			{
				var valueBefore = value.substr(0, cursor);
				var charPos = Math.max(valueBefore.lastIndexOf('+'), valueBefore.lastIndexOf('@'));

				if (charPos >= 0)
				{
					mentText = valueBefore.substr(charPos);
					mentTextOrig = mentText;

					mentText = mentText.replace(/^[\+@]*/, '');
					MPFMention.bSearch = BX.Type.isStringFilled(mentText);

					var dialog = BX.UI.EntitySelector.Dialog.getById(selectorId);

					if (
						BX.Type.isStringFilled(mentText)
						&& dialog
					)
					{
						dialog.search(mentText);
					}
				}
			}

			if (MPFMention._lastText)
			{
				if (mentTextOrig === '')
				{
					window['BXfpdStopMent' + formID]();
				}
				else if (
					mentTextOrig !== ''
					&& mentText === ''
				)
				{
					MPFMention.bSearch = false;
					if (dialog)
					{
						dialog.search('');
					}
				}
			}

			MPFMention.lastText = mentText;
			MPFMention._lastText = mentTextOrig;
		}
	}
	else
	{
		if (keyCode == 16)
		{
			var _this = this;
			this.shiftPressed = true;
			if (this.shiftTimeout)
			{
				this.shiftTimeout = clearTimeout(this.shiftTimeout);
			}

			this.shiftTimeout = setTimeout(function()
			{
				_this.shiftPressed = false;
			}, 100);
		}

		if (keyCode == 107 || (e.shiftKey || e.modifiers > 3 || this.shiftPressed) &&
			BX.util.in_array(keyCode, [187, 50, 107, 43, 61]))
		{
			cursor = editor.textareaView.element.selectionStart;
			if (cursor > 0)
			{
				value = editor.textareaView.element.value;
				var lastChar = value.substr(cursor - 1, 1);

				if (lastChar && (lastChar === '+' || lastChar === '@'))
				{
					MPFMention.listen = true;
					MPFMention.listenFlag = true;
					MPFMention.text = '';
					MPFMention.textarea = true;
					MPFMention.bSearch = false;
					MPFMention.mode = 'plus';

					BX.onCustomEvent(window, 'BX.MPF.MentionSelector:open', [{
						formId: formID,
						bindPosition: BX.pos(document.getElementById('bx-b-mention-' + formID)),
					}]);
				}
			}
		}
	}
};

var getMentionNodePosition = function(mention, editor)
{
	var mentPos = BX.pos(mention);
	var editorPos = BX.pos(editor.dom.areaCont);
	var editorDocScroll = BX.GetWindowScrollPos(editor.GetIframeDoc());
	var top = editorPos.top + mentPos.bottom - editorDocScroll.scrollTop + 2;
	var left = editorPos.left + mentPos.right - editorDocScroll.scrollLeft;

	return {top: top, left: left};
};

window.BxInsertMention = function (params)
{
	var item = params.item;
	var type = params.type;
	var formID = params.formID;
	var editorId = params.editorId;
	var bNeedComa = params.bNeedComa;
	var editor = LHEPostForm.getEditor(editorId);
	var spaceNode;

		if (
		(
			type === 'user'
			|| type === 'project'
			|| type === 'department'
		)
		&& item
		&& item.entityId > 0
		&& editor
	)
	{
		if (editor.GetViewMode() == 'wysiwyg') // WYSIWYG
		{
			var doc = editor.GetIframeDoc();
			var range = editor.selection.GetRange();
			var mention = BX.create('SPAN',
					{
						props: {className: 'bxhtmled-metion'},
						text: BX.util.htmlspecialcharsback(item.name)
					}, doc);
				// &nbsp; - for chrome
			spaceNode = BX.create('SPAN', {html: (bNeedComa ? ',&nbsp;' : '&nbsp;')}, doc);

			var bxTagData = {
				tag: 'postuser',
				params: {
					value : item.entityId
				},
			};

			switch (type)
			{
				case 'project':
					bxTagData.projectId = item.entityId;
					bxTagData.projectName = item.name;
					break;
				case 'department':
					bxTagData.departmentId = item.entityId;
					bxTagData.departmentName = item.name;
					break;
				default:
					bxTagData.userId = item.entityId;
					bxTagData.userName = item.name;
			}

			editor.SetBxTag(mention, bxTagData);

			if (
				BX(MPFMention.node)
				&& MPFMention.node.parentNode
			)
			{
				editor.util.ReplaceNode(MPFMention.node, mention);
			}
			else
			{
				editor.selection.InsertNode(mention, range);
			}

			if (mention && mention.parentNode)
			{
				var parentMention = BX.findParent(mention, {className: 'bxhtmled-metion'}, doc.body);
				if (parentMention)
				{
					editor.util.InsertAfter(mention, parentMention);
				}
			}

			if (mention && mention.parentNode)
			{
				editor.util.InsertAfter(spaceNode, mention);
				editor.selection.SetAfter(spaceNode);
			}
		}
		else if (editor.GetViewMode() == 'code' && editor.bbCode) // BB Codes
		{
			editor.textareaView.Focus();

			var value = editor.textareaView.GetValue(false);
			var cursor = editor.textareaView.GetCursorPosition();
			var valueBefore = value.substr(0, cursor);
			var charPos = Math.max(valueBefore.lastIndexOf('+'), valueBefore.lastIndexOf('@'));

			if (charPos >= 0 && cursor > charPos)
			{
				editor.textareaView.SetValue(value.substr(0, charPos) + value.substr(cursor));
				editor.textareaView.element.setSelectionRange(charPos, charPos);
			}

			var bbCode = '';
			switch (type)
			{
				case 'user':
					bbCode = 'USER';
					break;
				case 'project':
					bbCode = 'PROJECT';
					break;
				case 'department':
					bbCode = 'DEPARTMENT';
					break;
				default:
			}

			editor.textareaView.WrapWith(false, false, "[" + bbCode + "=" + item.entityId + "]" + item.name + "[/" + bbCode + "]" + (bNeedComa ? ', ' : ' '));
		}

		if (params.fireAddEvent === true)
		{
			BX.onCustomEvent(window, 'onMentionAdd', [ item, type ]);
		}

		if (window['BXfpdStopMent' + formID])
		{
			window['BXfpdStopMent' + formID]();
		}

		MPFMention["text"] = '';

		if (editor.GetViewMode() == 'wysiwyg') // WYSIWYG
		{
			editor.Focus();
			editor.selection.SetAfter(spaceNode);
		}

		var handler = LHEPostForm.getHandler(editorId);

		if (
			handler
			&& handler.formEntityType === 'task'
			&& handler.editorParams.tasksLimitExceeded
		)
		{
			BX.Main.PostFormTasksLimit.showPopup({
				bindPosition: getMentionNodePosition(MPFMention.node, editor),
			});
		}

	}
};

window.MPFgetSelectorId = function(formId)
{
	var result = false;
	var formNode = BX(formId);
	if (!formNode)
	{
		return result;
	}

	result = formNode.getAttribute('data-bx-selector-id');
	return result;
};

window.MPFcreateSelectorDialog = function(dialogParams)
{
	new BX.UI.EntitySelector.Dialog({
		targetNode: 'mpf-mention-' + dialogParams.formId,
		id: dialogParams.selectorId,
		context: 'MENTION',
		multiple: false,
		enableSearch: dialogParams.enableSearch,
		clearSearchOnSelect: true,
		hideOnSelect: true,
		hideByEsc: true,
		entities: dialogParams.params.entities,
		height: 300,
		width: 400,
		compactView: true,
		events: {
			onShow: function() {
				window.BXfpdOnDialogOpen();
			},
			onHide: function() {
				window.BXfpdOnDialogClose({
					editorId: dialogParams.params.editorId,
				});
			},
			'Item:onSelect': function (event) {
				var selectedItem = event.getData().item;
				if (selectedItem)
				{
					window['BXfpdSelectCallbackMent' + dialogParams.formId]({
						item: {
							name: selectedItem.getTitle(),
							entityId: selectedItem.getId(),
							entityType: selectedItem.getEntityType(),
						},
						entityType: selectedItem.getEntityId(),
					});
				}
			}
		},
	});
};


window.MPFMentionInit = function(formId, params)
{
	repo.mentionParams[formId] = params;

	if (params.initDestination === true)
	{
		BX.addCustomEvent('onAutoSaveRestoreDestination', function(params) {

			if (
				BX.type.isNotEmptyObject(params)
				&& BX.type.isNotEmptyObject(params.data)
				&& BX.type.isNotEmptyString(params.data.DEST_DATA)
				&& BX.type.isNotEmptyString(params.formId)
				&& params.formId == formId
				&& BX.UI.EntitySelector
			)
			{
				var destData = JSON.parse(params.data.DEST_DATA);
				if (!Array.isArray(destData))
				{
					return;
				}

				var selectorInstance = BX.UI.EntitySelector.Dialog.getById('oPostFormLHE_blogPostForm');
				if (!BX.type.isNotEmptyObject(selectorInstance))
				{
					return;
				}

				selectorInstance.preselectedItems = destData;
				selectorInstance.setPreselectedItems(destData);
			}
		});

		BX.addCustomEvent(window, "onMentionAdd", function(item, type) {

			var selectorInstance = BX.UI.EntitySelector.Dialog.getById('oPostFormLHE_blogPostForm');
			if (!BX.type.isNotEmptyObject(selectorInstance))
			{
				return;
			}

			var entityType = '';
			if (type === 'user')
			{
				if (item.isExtranet === 'Y')
				{
					entityType = 'extranet';
				}
				else if (item.isEmail === 'Y')
				{
					entityType = 'email';
				}
				else
				{
					entityType = 'employee';
				}
			}
			else if (type === 'project')
			{
				if (item.isExtranet === 'Y')
				{
					entityType = 'extranet';
				}
			}

			if (item.entityType !== 'collaber')
			{
				selectorInstance.addItem({
					avatar: item.avatar,
					customData: {
						email: (BX.Type.isStringFilled(item.email) ? item.email : ''),
					},
					entityId: type,
					entityType: entityType,
					id: item.entityId,
					title: item.name
				}).select();
			}
		});
	}

	window["BXfpdSelectCallbackMent" + formId] = function(callbackParams) // item, type, search
	{
		window.BxInsertMention({
			item: callbackParams.item,
			type: callbackParams.entityType.toLowerCase(),
			formID: formId,
			editorId: params.editorId,
			fireAddEvent: params.initDestination
		});
	};

	window["BXfpdStopMent" + formId] = function ()
	{
		var selectorId = window.MPFgetSelectorId('bx-mention-' + formId + '-id');
		var dialog = BX.UI.EntitySelector.Dialog.getById(selectorId);
		if (dialog)
		{
			dialog.hide();
		}
	};

	if (BX(formId))
	{
		BX.addCustomEvent(BX(formId), 'OnUCFormAfterShow', function(ucFormManager) {
			if (
				!BX.type.isNotEmptyObject(ucFormManager)
				|| !BX.type.isArray(ucFormManager.id)
				|| !BX.Type.isStringFilled(ucFormManager.id[0])
			)
			{
				return;
			}

			var reg = new RegExp('EVENT\_(\\d+)','i'); // calendar test
			if (!reg.test(ucFormManager.id[0]))
			{
				return;
			}
		});
	}

	var handler = LHEPostForm.getHandlerByFormId(formId);
	if (handler)
	{
		handler.exec();
	}

	BX.ready(function() {
			var ment = BX('bx-b-mention-' + formId);

			BX.bind(
				ment,
				"click",
				function(e)
				{
					if (MPFMention.listen !== true)
					{
						var editor = LHEPostForm.getEditor(params.editorId);
						var doc = editor.GetIframeDoc();

						if (editor.GetViewMode() == 'wysiwyg' && doc)
						{
							MPFMention.listen = true;
							MPFMention.listenFlag = true;
							MPFMention.text = '';
							MPFMention.leaveContent = false;
							MPFMention.mode = 'button';

							var range = editor.selection.GetRange();

							if (BX(MPFMention.node))
							{
								BX.remove(BX(MPFMention.node));
							}
							editor.InsertHtml('<span id="bx-mention-node">' + editor.INVISIBLE_SPACE + '</span>', range);

							setTimeout(function()
							{
								BX.onCustomEvent(window, 'BX.MPF.MentionSelector:open', [{
									formId: formId,
									bindNode: ment,
								}]);

								MPFMention.node = doc.getElementById('bx-mention-node');
								if (MPFMention.node)
								{
									range.setStart(MPFMention.node, 0);
									if (
										MPFMention.node.firstChild
										&& MPFMention.node.firstChild.nodeType == 3
										&& MPFMention.node.firstChild.nodeValue.length > 0
									)
									{
										range.setEnd(MPFMention.node, 1);
									}
									else
									{
										range.setEnd(MPFMention.node, 0);
									}
									editor.selection.SetSelection(range);
								}

								editor.Focus();
							}, 100);
						}
						else if (editor.GetViewMode() == 'code')
						{
							MPFMention.listen = true;
							MPFMention.listenFlag = true;
							MPFMention.text = '';
							MPFMention.leaveContent = false;
							MPFMention.mode = 'button';

							// TODO: get current cusrsor position

							setTimeout(function()
							{
								BX.onCustomEvent(window, 'BX.MPF.MentionSelector:open', [{
									formId: formId,
									bindNode: ment
								}]);
							}, 100);
						}

						BX.onCustomEvent(ment, 'mentionClick');
					}
				}
			);
		}
	);
};

window.BXfpdOnDialogOpen = function ()
{
	MPFMention.listen = true;
	MPFMention.listenFlag = true;
};

window.BXfpdOnDialogClose = function (params)
{
	MPFMention.listen = false;

	setTimeout(function()
	{
		MPFMention.listenFlag = false;
		if (!MPFMention.listen)
		{
			var editor = LHEPostForm.getEditor(params.editorId);
			if (editor)
			{
				editor.Focus();
			}
		}
	}, 100);
};


	MPFEntitySelector = function(params)
	{
		this.selector = null;
		this.inputNode = null;
		this.messages = {};

		if (!BX.Type.isStringFilled(params.id))
		{
			return null;
		}

		if (repo.selector[params.id])
		{
			return repo.selector[params.id];
		}

		repo.selector[params.id] = this.init(params);
	};

	MPFEntitySelector.prototype.init = function(params)
	{
		if (!BX.type.isPlainObject(params))
		{
			params = {};
		}

		if (
			!BX.Type.isStringFilled(params.id)
			|| !BX.Type.isStringFilled(params.tagNodeId)
			|| !BX(params.tagNodeId)
		)
		{
			return null;
		}

		if (
			BX.Type.isStringFilled(params.inputNodeId)
			&& BX(params.inputNodeId)
		)
		{
			this.inputNode = BX(params.inputNodeId);
		}

		if (BX.type.isNotEmptyObject(params.messages))
		{
			this.messages = params.messages;
		}

		this.selector = new BX.UI.EntitySelector.TagSelector({

			id: params.id,
			dialogOptions: {
				id: params.id,
				context: (BX.Type.isStringFilled(params.context) ? params.context : null),

				preselectedItems: (BX.type.isArray(params.preselectedItems) ? params.preselectedItems : []),

				events: {
					'Item:onSelect': function() {
						this.recalcValue(this.selector.getDialog().getSelectedItems());
					}.bind(this),
					'Item:onDeselect': function() {
						this.recalcValue(this.selector.getDialog().getSelectedItems());
					}.bind(this)
				},
				entities: [
					{
						id: 'meta-user',
						options: {
							'all-users': {
								title: this.messages.allUsersTitle,
								allowView: (
									BX.type.isBoolean(params.allowToAll)
									&& params.allowToAll
								)
							}
						}
					},
					{
						id: 'user',
						options: {
							collabers: (BX.type.isBoolean(params.collabers) ? params.collabers : true),
							emailUsers: (BX.type.isBoolean(params.allowSearchEmailUsers) ? params.allowSearchEmailUsers : false),
							inviteGuestLink: (BX.type.isBoolean(params.allowSearchEmailUsers) ? params.allowSearchEmailUsers : false),
							myEmailUsers: true,
							footerInviteIntranetOnly: true,
						}
					},
					{
						id: 'project',
						options: {
							features: {
								blog:  [ 'premoderate_post', 'moderate_post', 'write_post', 'full_post' ]
							},
							'!type': ['collab'],
						}
					},
					{
						id: 'department',
						options: {
							selectMode: 'usersAndDepartments',
							allowFlatDepartments: false,
						}
					}
				]
			},
			addButtonCaption: BX.message('BX_FPD_LINK_1'),
			addButtonCaptionMore: BX.message('BX_FPD_LINK_2')
		});

		this.selector.renderTo(document.getElementById(params.tagNodeId));

		return this.selector;
	};

	MPFEntitySelector.prototype.recalcValue = function(selectedItems)
	{
		if (
			!BX.type.isArray(selectedItems)
			|| !this.inputNode
		)
		{
			return;
		}

		var result = [];

		selectedItems.forEach(function(item) {
			result.push([ item.entityId, item.id ]);
		});

		this.inputNode.value = JSON.stringify(result);
	};

	window.MPFEntitySelector = MPFEntitySelector;

})();


})();
//# sourceMappingURL=script.js.map