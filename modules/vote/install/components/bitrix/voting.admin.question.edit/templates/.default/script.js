(function (exports,main_polyfill_core,main_core,main_core_events,ui_dialogs_messagebox) {
	'use strict';

	/**
	 * @package bitrix
	 * @subpackage vote
	 * @copyright 2001-2019 Bitrix
	 */
	BX.namespace('BX.Vote');

	var answer = /*#__PURE__*/function () {
	  function answer(id, data) {
	    babelHelpers.classCallCheck(this, answer);
	    this.id = null;
	    this.params = {
	      isNew: true,
	      isSaved: false
	    };

	    if (id > 0) {
	      this.params.isNew = false;
	      this.id = id;
	    } else if (id !== 0) {
	      this.id = id;
	    }

	    this.data = {
	      MESSAGE: "",
	      MESSAGE_TYPE: "text",
	      //		IMAGE_ID : "",
	      FIELD_TYPE: 0 //		FIELD_WIDTH : 0, //out of date
	      //		FIELD_HEIGHT : 0, //out of date
	      //		FIELD_PARAM : "", //out of date
	      //		ACTIVE : "Y",
	      //		C_SORT : 0,
	      //		COLOR : "" //out of date

	    };
	    this.adjust(data);
	    main_core_events.EventEmitter.subscribe(this, 'onApply', this.apply.bind(this));
	    main_core_events.EventEmitter.subscribe(this, 'onDelete', this.delete.bind(this));
	  }

	  babelHelpers.createClass(answer, [{
	    key: "adjust",
	    value: function adjust(data) {
	      var i,
	          d = BX.type.isPlainObject(data) ? data : {};

	      for (i in this.data) {
	        if (this.data.hasOwnProperty(i)) {
	          if (d[i]) this.data[i] = d[i];
	        }
	      }
	    }
	  }, {
	    key: "getId",
	    value: function getId() {
	      return this.id;
	    }
	  }, {
	    key: "getData",
	    value: function getData() {
	      return this.data;
	    }
	  }, {
	    key: "apply",
	    value: function apply(_ref) {
	      var _ref$data = babelHelpers.slicedToArray(_ref.data, 1),
	          fromData = _ref$data[0];

	      this.adjust(fromData);
	      return this;
	    }
	  }, {
	    key: "delete",
	    value: function _delete() {
	      return this;
	    }
	  }]);
	  return answer;
	}();

	answer.repo = {};

	answer.getItem = function (id, data) {
	  var item = new answer(id, BX.type.isPlainObject(data) ? data : {});

	  if (id !== 0 && answer.repo[id]) {
	    answer.repo[id] = null;
	    delete answer.repo[id];
	    answer.repo[id] = item;
	  }

	  return item;
	};

	var entityType = /*#__PURE__*/function () {
	  function entityType(values) {
	    babelHelpers.classCallCheck(this, entityType);
	    this.values = [];
	    this.valuesById = {};
	    this.valuesByCode = {};
	    values.forEach(function (current) {
	      this.values.push(current);
	      this.valuesById[current["ID"].toLowerCase()] = current;
	      this.valuesByCode[current["CODE"].toLowerCase()] = current;
	    }.bind(this));
	  }

	  babelHelpers.createClass(entityType, [{
	    key: "getByCode",
	    value: function getByCode(code) {
	      return this.valuesByCode[code];
	    }
	  }, {
	    key: "getById",
	    value: function getById(id) {
	      return this.valuesById[id];
	    }
	  }, {
	    key: "getIdByCode",
	    value: function getIdByCode(code) {
	      if (this.valuesByCode.hasOwnProperty(code)) {
	        return this.valuesByCode[code]["ID"];
	      }

	      return null;
	    }
	  }]);
	  return entityType;
	}();

	var answerTypes = {
	  setTypes: function setTypes(values) {
	    answerTypes.obj = new entityType(values);
	  },
	  getValues: function getValues() {
	    return answerTypes.obj.values;
	  },
	  isTextType: function isTextType(id) {
	    var item = answerTypes.obj.getById(id);

	    if (BX.type.isPlainObject(item)) {
	      return item["CODE"].toUpperCase().substr(0, 4) === "TEXT";
	    }

	    return false;
	  }
	};
	var questionTypes = {
	  setTypes: function setTypes(values) {
	    questionTypes.obj = new entityType(values);
	  },
	  isCompatibilityMode: function isCompatibilityMode() {
	    var val = BX('FIELD_TYPE').value;
	    return String(val).toLowerCase() === questionTypes.obj.getIdByCode("compatibility");
	  },
	  getActive: function getActive() {
	    return String(BX('FIELD_TYPE').value).toUpperCase();
	  }
	};

	var answerEditor = /*#__PURE__*/function () {
	  function answerEditor() {
	    babelHelpers.classCallCheck(this, answerEditor);
	    this.id = 'Editor';
	    this.popup = null;
	    this.reset();
	    this.debug = true;
	    return this;
	  }

	  babelHelpers.createClass(answerEditor, [{
	    key: "onApply",
	    value: function onApply(formData) {
	      main_core_events.EventEmitter.emit(this.answer, 'onApply', [formData]);
	      main_core_events.EventEmitter.emit(this, 'onApply', [this.answer.getId(), this.answer.getData(), Object.assign({}, this.gridData)]);
	      this.reset();
	    }
	  }, {
	    key: "onCancel",
	    value: function onCancel() {
	      main_core_events.EventEmitter.emit(this.answer, "onCancel", []);
	      this.reset();
	    }
	  }, {
	    key: "onDelete",
	    value: function onDelete() {
	      main_core_events.EventEmitter.emit(this.answer, "onDelete", []);
	      this.reset();
	    }
	  }, {
	    key: "setGridData",
	    value: function setGridData(gridData) {
	      this.gridData = {
	        gridInstanceId: gridData["gridInstanceId"],
	        gridId: gridData["gridId"],
	        maxSort: gridData["maxSort"]
	      };
	      return this;
	    }
	  }, {
	    key: "getGridId",
	    value: function getGridId() {
	      return this.gridData.gridId;
	    }
	  }, {
	    key: "setAnswer",
	    value: function setAnswer(id, data) {
	      var item = answer.getItem(id, data);
	      if (this.answer !== null && this.answer !== item) this.onCancel();
	      this.answer = item;
	      return this;
	    }
	  }, {
	    key: "reset",
	    value: function reset() {
	      delete this.answer;
	      this.answer = null;
	      delete this.gridData;
	      this.gridData = {
	        id: null,
	        gridId: null,
	        maxSort: null
	      };
	    }
	  }, {
	    key: "show",
	    value: function show() {
	      this.showEditor(this.answer.getData());
	    }
	  }, {
	    key: "showEditor",
	    value: function showEditor(data) {
	      if (this.popup !== null) this.popup.close();
	      var isTextMode = false;
	      var fieldType = String(data["FIELD_TYPE"]);
	      var htmlReg = '';
	      answerTypes.getValues().forEach(function (current) {
	        htmlReg += ['<option value="' + current["ID"] + '"' + (fieldType === current["ID"] ? ' selected' : '') + '>', current["TITLE"], '</option>'].join('');
	      });
	      htmlReg = ['<div class="ui-form-block ui-form-block-html-text">\
					<label class="ui-ctl ui-ctl-radio ui-ctl-wa ui-ctl-xs"> \
						<input type="radio" name="answer[MESSAGE_TYPE]" ' + (data["MESSAGE_TYPE"] === "html" ? "" : "checked") + ' class="ui-ctl-element" value="text"> \
						<div class="ui-ctl-label-text">text</div> \
					</label>\
					<label className="ui-ctl ui-ctl-wa ui-ctl-xs"><div class="ui-ctl-label-text">&nbsp;/&nbsp;</div></label> \
					<label class="ui-ctl ui-ctl-radio ui-ctl-wa ui-ctl-xs"> \
						<input type="radio" name="answer[MESSAGE_TYPE]" ' + (data["MESSAGE_TYPE"] === "html" ? "checked" : "") + ' class="ui-ctl-element" value="html"> \
						<div class="ui-ctl-label-text">html</div> \
					</label>\
				</div>\
				<div class="ui-form-block">\
					<div class="ui-ctl ui-ctl-textarea" id="answer_MESSAGE_block">\
						<textarea name="answer[MESSAGE]" class="ui-ctl-element" id="ANSWER_MESSAGE" placeholder="' + main_core.Loc.getMessage('VOTE_ANSWER_PLACEHOLDER') + '">' + BX.util.htmlspecialchars(data["MESSAGE"]) + '</textarea>\
					</div>\
				</div>\
				<input id="answer_FIELD_TYPE" name="answer[FIELD_TYPE]" type="hidden" value="" class="ui-ctl-element"> ' + (questionTypes.isCompatibilityMode() === true ? '<div class="ui-form-block">\
					<label for="id1" class="ui-ctl-label-text">' + main_core.Loc.getMessage('VOTE_ANSWER_FIELD_TYPE') + '</label>\
					<div class="ui-ctl ui-ctl-after-icon ui-ctl-dropdown">\
						<div class="ui-ctl-after ui-ctl-icon-angle"></div>\
						<select name="answer[FIELD_TYPE]" class="ui-ctl-element">' + htmlReg + '</select>\
					</div>\
				</div> ' : '<input  name="answer[FIELD_TYPE]" type="hidden" value="' + questionTypes.getActive() + '">')].join();
	      var htmlText = '';
	      answerTypes.getValues().forEach(function (current) {
	        if (current["CODE"].substring(0, 4).toUpperCase() === "TEXT") {
	          isTextMode = isTextMode || fieldType === current["ID"];
	          htmlText += ['<option value="' + current["ID"] + '"' + (fieldType === current["ID"] ? ' selected' : '') + '>', current["TITLE"], '</option>'].join("");
	        }
	      });
	      htmlText = ['<div class="ui-form-block ui-form-block-html-text">\
					<label class="ui-ctl ui-ctl-radio ui-ctl-wa ui-ctl-xs"> \
						<input type="radio" name="answer[MESSAGE_TYPE]" ' + (data["MESSAGE_TYPE"] === "html" ? "" : "checked") + ' class="ui-ctl-element" value="text"> \
						<div class="ui-ctl-label-text">text</div> \
					</label>\
					<label className="ui-ctl ui-ctl-wa ui-ctl-xs"><div class="ui-ctl-label-text">&nbsp;/&nbsp;</div></label> \
					<label class="ui-ctl ui-ctl-radio ui-ctl-wa ui-ctl-xs"> \
						<input type="radio" name="answer[MESSAGE_TYPE]" ' + (data["MESSAGE_TYPE"] === "html" ? "checked" : "") + ' class="ui-ctl-element" value="html"> \
						<div class="ui-ctl-label-text">html</div> \
					</label>\
				</div>\
				<div class="ui-form-block"> \
					<div class="ui-ctl ui-ctl-textarea" id="answer_MESSAGE_block">\
						<textarea name="answer[MESSAGE]" class="ui-ctl-element" id="ANSWER_MESSAGE" placeholder="' + main_core.Loc.getMessage('VOTE_ANSWER_PLACEHOLDER1') + '">' + BX.util.htmlspecialchars(data["MESSAGE"] || main_core.Loc.getMessage('VOTE_ANSWER_TEXT_OTHER')) + '</textarea>\
					</div>\
				</div>\
				<div class="ui-form-block">\
					<label for="id1" class="ui-ctl-label-text">' + main_core.Loc.getMessage('VOTE_ANSWER_FIELD_TYPE') + '</label>\
					<div class="ui-ctl ui-ctl-after-icon ui-ctl-dropdown">\
						<div class="ui-ctl-after ui-ctl-icon-angle"></div>\
						<select name="answer[FIELD_TYPE]" class="ui-ctl-element">', htmlText, '</select>\
					</div>\
				</div> '].join();
	      var editorNode = BX.create("DIV", {
	        attrs: {
	          id: this.id + 'Proper',
	          className: "vote-edit-popup"
	        },
	        html: ['<div class="vote-edit-inp-wrap">\
					<form onsubmit="return false;" id="', this.id, '_form">\
						', isTextMode ? htmlText : htmlReg, '</form>\
				</div>'].join("")
	      });

	      var onApplyPopupPressed = function () {
	        var d = BX.ajax.prepareForm(BX(this.id + '_form'));

	        if (BX.type.isNotEmptyString(d.data.answer.MESSAGE)) {
	          this.onApply(d.data.answer);
	          this.popup.fired = true;
	          this.popup.close();
	        } else {
	          BX.addClass(BX('answer_MESSAGE_block'), 'ui-ctl-danger');
	        }
	      }.bind(this);

	      this.popup = BX.PopupWindowManager.create('popup' + this.id, null, {
	        titleBar: main_core.Loc.getMessage('VOTE_ANSWER_MESSAGE'),
	        className: "vote-answer",
	        autoHide: false,
	        lightShadow: true,
	        closeIcon: true,
	        closeByEsc: true,
	        zIndex: 1,
	        content: editorNode,
	        overlay: {},
	        events: {
	          onPopupClose: function () {
	            if (this.popup.fired !== true) {
	              main_core_events.EventEmitter.emit(this, "onCancel", [this]);
	            }

	            this.popup.destroy();
	            this.popup = null;
	          }.bind(this),
	          onAfterPopupShow: function () {
	            setTimeout(function () {
	              if (BX(this.id + '_form')) {
	                BX.focus(BX(this.id + '_form').elements["ANSWER_MESSAGE"]);
	                BX.bind(BX(this.id + '_form').elements["ANSWER_MESSAGE"], "keydown", function (e) {
	                  if ((e.ctrlKey === true || e.altKey === true) && e.keyCode === 13) {
	                    onApplyPopupPressed();
	                  }
	                });
	              }
	            }.bind(this), 50);
	          }.bind(this)
	        },
	        buttons: [new BX.PopupWindowButton({
	          text: main_core.Loc.getMessage('VOTE_SAVE'),
	          className: "",
	          events: {
	            click: onApplyPopupPressed
	          }
	        }), new BX.PopupWindowButton({
	          text: main_core.Loc.getMessage('VOTE_CANCEL'),
	          className: "",
	          events: {
	            click: function () {
	              this.onCancel();
	              this.popup.fired = true;
	              this.popup.close();
	            }.bind(this)
	          }
	        })]
	      });
	      this.popup.show();
	      this.popup.adjustPosition();
	    }
	  }]);
	  return answerEditor;
	}();

	BX.Vote.addTextAnswer = function (gridInstanceId) {
	  initEditor(gridInstanceId, 0, {
	    FIELD_TYPE: 4
	  });
	};

	BX.Vote.addAnswer = function (gridInstanceId, answerData) {
	  initEditor(gridInstanceId, 0, answerData);
	};

	BX.Vote.editAnswer = function (gridInstanceId, rowId) {
	  var grid = BX.Main.gridManager.getInstanceById(answerPopupParams[gridInstanceId]["gridId"]);
	  var data = grid !== null ? grid.getRows().getById(rowId).getEditData() : {};
	  initEditor(gridInstanceId, rowId, data);
	};

	BX.Vote.setTypes = function (types) {
	  questionTypes.setTypes(types.questionTypes);
	  answerTypes.setTypes(types.answerTypes);
	};

	BX.Vote.setParams = function (gridInstanceId, params) {
	  setTimeout(function () {
	    bindForm(params['formId'], params.gridId, gridInstanceId);
	    BX.bind(document, "keydown", function (e) {
	      if (e.keyCode === 45 && e.ctrlKey === false && e.metaKey === false && e.altKey === false && (!BX(e.target) || BX(e.target).tagName === 'BODY')) {
	        BX.Vote.addAnswer(gridInstanceId, {});
	      }
	    });
	  }, 50);
	  answerPopupParams[gridInstanceId] = {
	    gridInstanceId: gridInstanceId,
	    gridId: params.gridId,
	    maxSort: params["maxSort"] || 100
	  };
	  main_core_events.EventEmitter.subscribeOnce('onVoteQuestionDelete', function (_ref2) {
	    var _ref2$data = babelHelpers.slicedToArray(_ref2.data, 2),
	        voteId = _ref2$data[0],
	        questionId = _ref2$data[1];

	    if (confirm(main_core.Loc.getMessage('VOTE_DELETE_RECORD_CONFIRM'))) {
	      main_core.ajax.runComponentAction('bitrix:voting.admin.question.edit', 'delete', {
	        mode: 'class',
	        signedParameters: params.componentSignedParams
	      }).then(function () {
	        window.location = main_core.Uri.addParam('/bitrix/admin/vote_question_list.php', {
	          VOTE_ID: voteId,
	          lang: main_core.Loc.getMessage('LANGUAGE_ID')
	        });
	      }, function (_ref3) {
	        var errors = _ref3.errors;
	        var errorMessages = [];
	        errors.forEach(function (_ref4) {
	          var message = _ref4.message;
	          errorMessages.push(message);
	        });
	        ui_dialogs_messagebox.MessageBox.alert(errorMessages.join(' '));
	      });
	    }
	  });
	};

	var answerPopup = null;
	var answerPopupParams = {};

	var initEditor = function initEditor(gridInstanceId, id, data) {
	  if (answerPopup === null) {
	    answerPopup = new answerEditor();
	    main_core_events.EventEmitter.subscribe(answerPopup, 'onApply', function (_ref5) {
	      var _ref5$data = babelHelpers.slicedToArray(_ref5.data, 3),
	          answerId = _ref5$data[0],
	          data = _ref5$data[1],
	          gridData = _ref5$data[2];

	      var gridId = gridData['gridId'];
	      var grid = BX.Main.gridManager.getInstanceById(gridId);

	      if (grid instanceof BX.Main.grid) {
	        var newRowData = BX.clone(data);

	        if (answerId !== null) {
	          grid.updateRow(answerId, newRowData, null, function () {});
	        } else {
	          answerPopupParams[gridData["gridInstanceId"]]["maxSort"] += 100;
	          newRowData["C_SORT"] = answerPopupParams[gridData["gridInstanceId"]]["maxSort"];
	          grid.addRow(newRowData, null, function () {});
	        }
	      }
	    });
	  }

	  answerPopup.setGridData(answerPopupParams[gridInstanceId]).setAnswer(id, data).show();
	};

	main_core_events.EventEmitter.subscribe('Grid::beforeRequest', function (_ref6) {
	  var _ref6$data = babelHelpers.slicedToArray(_ref6.data, 2),
	      gridData = _ref6$data[0],
	      args = _ref6$data[1];

	  console.log('gridData, args: ', gridData, args);
	  var i;

	  for (i in answerPopupParams) {
	    if (answerPopupParams.hasOwnProperty(i)) {
	      if (answerPopupParams[i]["gridId"] === args.gridId) {
	        args.data.gridId = args.gridId;
	        args.data.gridInstanceId = answerPopupParams[i]["gridInstanceId"];
	      }
	    }
	  }
	});

	var bindForm = function bindForm(formId, gridId, gridInstanceId) {
	  var form = BX(formId, true);
	  var controlName = "save";

	  var func = function func(event) {
	    form.removeEventListener('submit', func);
	    var grid = BX.Main.gridManager.getInstanceById(gridId);

	    if (!grid.getRows().hasEditable()) {
	      prepareForm(form, gridId, gridInstanceId);
	      return true;
	    }

	    var func1 = function func1(_ref7) {
	      var _ref7$data = babelHelpers.slicedToArray(_ref7.data, 1),
	          someGrid = _ref7$data[0];

	      if (someGrid === grid) {
	        form.appendChild(BX.create("INPUT", {
	          "props": {
	            "type": "hidden",
	            "name": controlName,
	            "value": "Y"
	          }
	        }));
	        prepareForm(form, gridId, gridInstanceId);
	        form.submit();
	      }
	    };

	    main_core_events.EventEmitter.subscribeOnce("Grid::updated", func1);
	    grid.editSelectedSave();
	    return BX.PreventDefault(event);
	  };

	  form.addEventListener('submit', func);

	  if (form.elements['apply']) {
	    form.elements['apply'].addEventListener('mousedown', function () {
	      controlName = "apply";
	    });
	  }

	  var f = function f(e) {
	    var el = e.target;
	    var rows = BX.Main.gridManager.getInstanceById(gridId).getRows();

	    if (!questionTypes.isCompatibilityMode() && rows) {
	      var ids = [];
	      rows.getRows().forEach(function (current) {
	        var attrs = BX.parseJSON(BX.data(current.getNode(), "item"), current);

	        if (attrs && BX.type.isPlainObject(attrs) && attrs.hasOwnProperty("field_type") && String(attrs["field_type"]) !== String(el.value) && !answerTypes.isTextType(attrs["field_type"])) {
	          current.select();
	          ids.push(current.getId());
	        }
	      });

	      if (ids.length > 0) {
	        BX.Main.gridManager.getInstanceById(gridId).reloadTable("POST", {
	          ID: ids,
	          action_button_grid_vote_answer: "change_answer_type",
	          FIELD_TYPE: this.value
	        });
	      }
	    }
	  };

	  document.querySelectorAll('#' + formId + ' [name=FIELD_TYPE]').forEach(function (node) {
	    node.addEventListener('change', f);
	  });
	};

	var prepareForm = function prepareForm(form, gridId
	/*, gridInstanceId*/
	) {
	  var grid = BX.Main.gridManager.getInstanceById(gridId);

	  if (grid) {
	    var rows = grid.getRows().getRows();
	    rows.forEach(function (current) {
	      if (current.getIndex() < 1 || current.getId() === "template_0") {
	        return;
	      }

	      var id = current.getId();
	      form.appendChild(BX.create("INPUT", {
	        props: {
	          type: "hidden",
	          name: "ANSWER[" + id.toLowerCase() + "][ID]",
	          value: id
	        }
	      }));
	      var attrs = BX.parseJSON(BX.data(current.getNode(), "item"), current);

	      var func = function func(prefix, params, depth) {
	        var key;

	        for (var j in params) {
	          if (params.hasOwnProperty(j)) {
	            key = "[" + (depth > 0 ? j : String(j).toUpperCase()) + "]";

	            if (BX.type.isPlainObject(params[j])) {
	              func(prefix + key, params[j], depth + 1);
	            } else {
	              form.appendChild(BX.create('INPUT', {
	                props: {
	                  type: "hidden",
	                  name: prefix + key,
	                  value: params[j]
	                }
	              }));
	            }
	          }
	        }
	      };

	      if (BX.type.isPlainObject(attrs)) {
	        func("ANSWER[" + id + "]", attrs, 0);
	      }
	    });
	  }
	};

	var picker = null;

	BX.Vote.showColorPicker = function (input) {
	  if (picker === null) {
	    picker = new BX.ColorPicker({
	      bindElement: null,
	      popupOptions: {
	        angle: true
	      }
	    });
	  }

	  picker.open({
	    selectedColor: BX.type.isNotEmptyString(input.value) ? input.value : null,
	    bindElement: input,
	    onColorSelected: function onColorSelected(color) {
	      input.value = color;
	    }
	  });
	};

}((this.window = this.window || {}),window,BX,BX.Event,BX.UI.Dialogs));
//# sourceMappingURL=script.js.map
