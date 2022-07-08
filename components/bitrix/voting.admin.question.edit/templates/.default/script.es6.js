/**
 * @package bitrix
 * @subpackage vote
 * @copyright 2001-2019 Bitrix
 */
import 'main.polyfill.core';
import {ajax, Uri, Loc} from 'main.core';
import {EventEmitter, BaseEvent} from 'main.core.events';
import {MessageBox} from 'ui.dialogs.messagebox';

BX.namespace('BX.Vote');

class answer {
	constructor(id, data)
	{
		this.id = null;
		this.params = {
			isNew : true,
			isSaved : false
		};
		if (id > 0)
		{
			this.params.isNew = false;
			this.id = id;
		}
		else if (id !== 0)
		{
			this.id = id;
		}

		this.data =  {
			MESSAGE : "",
			MESSAGE_TYPE : "text",
	//		IMAGE_ID : "",
			FIELD_TYPE : 0,
	//		FIELD_WIDTH : 0, //out of date
	//		FIELD_HEIGHT : 0, //out of date
	//		FIELD_PARAM : "", //out of date
	//		ACTIVE : "Y",
	//		C_SORT : 0,
	//		COLOR : "" //out of date
		};

		this.adjust(data);
		EventEmitter.subscribe(this, 'onApply', this.apply.bind(this));
		EventEmitter.subscribe(this, 'onDelete', this.delete.bind(this));
	}
	adjust(data)
	{
		let i, d = BX.type.isPlainObject(data) ? data : {};
		for (i in this.data)
		{
			if (this.data.hasOwnProperty(i))
			{
				if (d[i])
					this.data[i] = d[i];
			}
		}
	}
	getId()
	{
		return this.id;
	}
	getData()
	{
		return this.data;
	}
	apply({data: [fromData]})
	{
		this.adjust(fromData);
		return this;
	}
	delete()
	{
		return this;
	}
}

answer.repo = {};
answer.getItem = function(id, data) {
	const item = new answer(id, BX.type.isPlainObject(data) ? data : {});
	if (id !== 0 && answer.repo[id])
	{
		answer.repo[id] = null;
		delete answer.repo[id];
		answer.repo[id] = item;
	}
	return item;
};

class entityType
{
	constructor (values)
	{
		this.values = [];
		this.valuesById = {};
		this.valuesByCode = {};
		values.forEach(function(current) {
			this.values.push(current);
			this.valuesById[current["ID"].toLowerCase()] = current;
			this.valuesByCode[current["CODE"].toLowerCase()] = current;
		}.bind(this));
	}
	getByCode(code)
	{
		return this.valuesByCode[code];
	}
	getById(id)
	{
		return this.valuesById[id];
	}
	getIdByCode(code)
	{
		if (this.valuesByCode.hasOwnProperty(code))
		{
			return this.valuesByCode[code]["ID"];
		}
		return null;
	}
}

const answerTypes = {
	setTypes : function(values) {
		answerTypes.obj = new entityType(values);
	},
	getValues : function() {
		return answerTypes.obj.values;
	},
	isTextType : function(id) {
		let item = answerTypes.obj.getById(id);
		if (BX.type.isPlainObject(item))
		{
			return (item["CODE"].toUpperCase().substr(0, 4) === "TEXT");
		}
		return false;
	}
};
const questionTypes = {
	setTypes : function(values) {
		questionTypes.obj = new entityType(values);
	},
	isCompatibilityMode : function() {
		let val = BX('FIELD_TYPE').value;
		return (String(val).toLowerCase() === questionTypes.obj.getIdByCode("compatibility"));
	},
	getActive : function() {
		return String(BX('FIELD_TYPE').value).toUpperCase();
	}
};

class answerEditor
{
	constructor()
	{
		this.id = 'Editor';
		this.popup = null;
		this.reset();

		this.debug = true;

		return this;
	}
	onApply(formData)
	{
		EventEmitter.emit(this.answer, 'onApply', [formData]);
		EventEmitter.emit(this, 'onApply', [
			this.answer.getId(),
			this.answer.getData(),
			Object.assign({}, this.gridData)
		]);
		this.reset();
	}
	onCancel()
	{
		EventEmitter.emit(this.answer, "onCancel", []);
		this.reset();
	}
	onDelete()
	{
		EventEmitter.emit(this.answer, "onDelete", []);
		this.reset();
	}
	setGridData(gridData)
	{
		this.gridData = {
			gridInstanceId : gridData["gridInstanceId"],
			gridId : gridData["gridId"],
			maxSort : gridData["maxSort"],
		};
		return this;
	}
	getGridId()
	{
		return this.gridData.gridId;
	}
	setAnswer(id, data)
	{
		const item = answer.getItem(id, data);
		if (this.answer !== null && this.answer !== item)
			this.onCancel();
		this.answer = item;
		return this;
	}
	reset()
	{
		delete this.answer;
		this.answer = null;
		delete this.gridData;
		this.gridData = {
			id : null,
			gridId : null,
			maxSort : null
		};
	}
	show()
	{
		this.showEditor(this.answer.getData());
	}
	showEditor(data)
	{
		if (this.popup !== null)
			this.popup.close();
		let isTextMode = false;
		let fieldType = String(data["FIELD_TYPE"]);
		let htmlReg = '';
		answerTypes.getValues().forEach(function(current){
			htmlReg += ['<option value="' + current["ID"] + '"' + (fieldType === current["ID"] ? ' selected' : '') + '>', current["TITLE"], '</option>'].join('');
		});
		htmlReg = [
				'<div class="ui-form-block ui-form-block-html-text">\
					<label class="ui-ctl ui-ctl-radio ui-ctl-wa ui-ctl-xs"> \
						<input type="radio" name="answer[MESSAGE_TYPE]" ' + (data["MESSAGE_TYPE"] === "html" ? "" : "checked" ) + ' class="ui-ctl-element" value="text"> \
						<div class="ui-ctl-label-text">text</div> \
					</label>\
					<label className="ui-ctl ui-ctl-wa ui-ctl-xs"><div class="ui-ctl-label-text">&nbsp;/&nbsp;</div></label> \
					<label class="ui-ctl ui-ctl-radio ui-ctl-wa ui-ctl-xs"> \
						<input type="radio" name="answer[MESSAGE_TYPE]" ' + (data["MESSAGE_TYPE"] === "html" ? "checked" : "" ) + ' class="ui-ctl-element" value="html"> \
						<div class="ui-ctl-label-text">html</div> \
					</label>\
				</div>\
				<div class="ui-form-block">\
					<div class="ui-ctl ui-ctl-textarea" id="answer_MESSAGE_block">\
						<textarea name="answer[MESSAGE]" class="ui-ctl-element" id="ANSWER_MESSAGE" placeholder="' + Loc.getMessage('VOTE_ANSWER_PLACEHOLDER') + '">' + BX.util.htmlspecialchars(data["MESSAGE"]) +'</textarea>\
					</div>\
				</div>\
				<input id="answer_FIELD_TYPE" name="answer[FIELD_TYPE]" type="hidden" value="" class="ui-ctl-element"> ' +
				(questionTypes.isCompatibilityMode() === true ?
				'<div class="ui-form-block">\
					<label for="id1" class="ui-ctl-label-text">' + Loc.getMessage('VOTE_ANSWER_FIELD_TYPE') + '</label>\
					<div class="ui-ctl ui-ctl-after-icon ui-ctl-dropdown">\
						<div class="ui-ctl-after ui-ctl-icon-angle"></div>\
						<select name="answer[FIELD_TYPE]" class="ui-ctl-element">' + htmlReg + '</select>\
					</div>\
				</div> ' : '<input  name="answer[FIELD_TYPE]" type="hidden" value="' + questionTypes.getActive() + '">')].join();
		let htmlText = '';
		answerTypes.getValues().forEach(function(current){
			if (current["CODE"].substring(0, 4).toUpperCase() === "TEXT")
			{
				isTextMode = isTextMode || (fieldType === current["ID"]);
				htmlText += ['<option value="' + current["ID"] + '"' + (fieldType === current["ID"] ? ' selected' : '') + '>', current["TITLE"], '</option>'].join("");
			}
		});
		htmlText = [
				'<div class="ui-form-block ui-form-block-html-text">\
					<label class="ui-ctl ui-ctl-radio ui-ctl-wa ui-ctl-xs"> \
						<input type="radio" name="answer[MESSAGE_TYPE]" ' + (data["MESSAGE_TYPE"] === "html" ? "" : "checked" ) + ' class="ui-ctl-element" value="text"> \
						<div class="ui-ctl-label-text">text</div> \
					</label>\
					<label className="ui-ctl ui-ctl-wa ui-ctl-xs"><div class="ui-ctl-label-text">&nbsp;/&nbsp;</div></label> \
					<label class="ui-ctl ui-ctl-radio ui-ctl-wa ui-ctl-xs"> \
						<input type="radio" name="answer[MESSAGE_TYPE]" ' + (data["MESSAGE_TYPE"] === "html" ? "checked" : "" ) + ' class="ui-ctl-element" value="html"> \
						<div class="ui-ctl-label-text">html</div> \
					</label>\
				</div>\
				<div class="ui-form-block"> \
					<div class="ui-ctl ui-ctl-textarea" id="answer_MESSAGE_block">\
						<textarea name="answer[MESSAGE]" class="ui-ctl-element" id="ANSWER_MESSAGE" placeholder="' + Loc.getMessage('VOTE_ANSWER_PLACEHOLDER1') + '">'
							+ BX.util.htmlspecialchars(data["MESSAGE"] || Loc.getMessage('VOTE_ANSWER_TEXT_OTHER')) +
						'</textarea>\
					</div>\
				</div>\
				<div class="ui-form-block">\
					<label for="id1" class="ui-ctl-label-text">' + Loc.getMessage('VOTE_ANSWER_FIELD_TYPE') + '</label>\
					<div class="ui-ctl ui-ctl-after-icon ui-ctl-dropdown">\
						<div class="ui-ctl-after ui-ctl-icon-angle"></div>\
						<select name="answer[FIELD_TYPE]" class="ui-ctl-element">', htmlText, '</select>\
					</div>\
				</div> '
			].join();
		let editorNode = BX.create("DIV", {
			attrs : {
				id : this.id + 'Proper',
				className : "vote-edit-popup"
			},
			html : [
				'<div class="vote-edit-inp-wrap">\
					<form onsubmit="return false;" id="', this.id , '_form">\
						',
				(isTextMode ? htmlText : htmlReg),
					'</form>\
				</div>'].join("")
		});
		const onApplyPopupPressed = function() {
			const d = BX.ajax.prepareForm(BX(this.id + '_form'));
			if (BX.type.isNotEmptyString(d.data.answer.MESSAGE))
			{
				this.onApply(d.data.answer);
				this.popup.fired = true;
				this.popup.close();
			}
			else
			{
				BX.addClass(BX('answer_MESSAGE_block'), 'ui-ctl-danger');
			}
		}.bind(this);

		this.popup = BX.PopupWindowManager.create(
			'popup' + this.id,
			null,
			{
				titleBar: Loc.getMessage('VOTE_ANSWER_MESSAGE'),
				className : "vote-answer",
				autoHide : false,
				lightShadow : true,
				closeIcon : true,
				closeByEsc : true,
				zIndex : 1,
				content : editorNode,
				overlay : {},
				events : {
					onPopupClose : function(){
						if (this.popup.fired !== true)
						{
							EventEmitter.emit(this, "onCancel", [this]);
						}
						this.popup.destroy();
						this.popup = null;
					}.bind(this),
					onAfterPopupShow : function(){
						setTimeout(function() {
							if (BX(this.id + '_form'))
							{
								BX.focus(BX(this.id + '_form').elements["ANSWER_MESSAGE"]);
								BX.bind(BX(this.id + '_form').elements["ANSWER_MESSAGE"], "keydown", function(e) {
									if ((e.ctrlKey === true || e.altKey === true) && e.keyCode === 13)
									{
										onApplyPopupPressed();
									}
								});
							}
						}.bind(this), 50)
					}.bind(this)
				},
				buttons : [
					new BX.PopupWindowButton(
						{
							text : Loc.getMessage('VOTE_SAVE'),
							className : "",
							events : {
								click : onApplyPopupPressed
							}
						}
					),
					new BX.PopupWindowButton(
						{
							text : Loc.getMessage('VOTE_CANCEL'),
							className : "",
							events : { click : function(){
								this.onCancel();
								this.popup.fired = true;
								this.popup.close();
							}.bind(this) } } )
				]
			}
		);
		this.popup.show();
		this.popup.adjustPosition();
	}
}

BX.Vote.addTextAnswer = function(gridInstanceId) { initEditor(gridInstanceId, 0, {FIELD_TYPE : 4}); };
BX.Vote.addAnswer = function(gridInstanceId, answerData) { initEditor(gridInstanceId, 0, answerData); };
BX.Vote.editAnswer = function(gridInstanceId, rowId) {
	const grid = BX.Main.gridManager.getInstanceById(answerPopupParams[gridInstanceId]["gridId"]);
	let data = (grid !== null ? grid.getRows().getById(rowId).getEditData() : {});
	initEditor(gridInstanceId, rowId, data);
};
BX.Vote.setTypes = function(types) {
	questionTypes.setTypes(types.questionTypes);
	answerTypes.setTypes(types.answerTypes);
};
BX.Vote.setParams = function(gridInstanceId, params) {
	setTimeout(function(){
		bindForm(params['formId'], params.gridId, gridInstanceId);
		BX.bind(document, "keydown", function(e) {
			if (e.keyCode === 45 &&
				(e.ctrlKey === false && e.metaKey === false && e.altKey === false) &&
				(!BX(e.target) || BX(e.target).tagName === 'BODY'))
			{
				BX.Vote.addAnswer(gridInstanceId, {});
			}
		});
	}, 50);
	answerPopupParams[gridInstanceId] = {
		gridInstanceId : gridInstanceId,
		gridId : params.gridId,
		maxSort : (params["maxSort"] || 100)
	};

	EventEmitter.subscribeOnce('onVoteQuestionDelete', ({data: [voteId, questionId]}) => {
		if (confirm(Loc.getMessage('VOTE_DELETE_RECORD_CONFIRM')))
		{
			ajax.runComponentAction('bitrix:voting.admin.question.edit',
				'delete',
				{
					mode: 'class',
					signedParameters: params.componentSignedParams,
				}
			)
			.then(() => {
				window.location = Uri.addParam(
					'/bitrix/admin/vote_question_list.php',
					{VOTE_ID: voteId, lang: Loc.getMessage('LANGUAGE_ID')});
			}, ({errors}) => {
				const errorMessages = [];
				errors.forEach(({message}) => {
					errorMessages.push(message);
				});
				MessageBox.alert(errorMessages.join(' '));
			});
		}
	});
};

let answerPopup = null;
let answerPopupParams = {};
const initEditor = function(gridInstanceId, id, data) {
	if (answerPopup === null)
	{
		answerPopup = new answerEditor();
		EventEmitter.subscribe(
			answerPopup,
			'onApply',
			function({data: [answerId, data, gridData]})
			{
				const gridId = gridData['gridId'];
				const grid = BX.Main.gridManager.getInstanceById(gridId);

				if (grid instanceof BX.Main.grid)
				{
					let newRowData = BX.clone(data);
					if (answerId !== null)
					{
						grid.updateRow(answerId, newRowData, null, function(){});
					}
					else
					{
						answerPopupParams[gridData["gridInstanceId"]]["maxSort"] += 100;
						newRowData["C_SORT"] = answerPopupParams[gridData["gridInstanceId"]]["maxSort"];
						grid.addRow(newRowData, null, function(){});
					}
				}
			}
		);
	}
	answerPopup.
		setGridData(answerPopupParams[gridInstanceId]).
		setAnswer(id, data).
		show();
};
EventEmitter.subscribe(
	'Grid::beforeRequest',
	function({data: [gridData, args]})
	{
		console.log('gridData, args: ', gridData, args);
		let i;
		for (i in answerPopupParams)
		{
			if (answerPopupParams.hasOwnProperty(i))
			{
				if (answerPopupParams[i]["gridId"] === args.gridId)
				{
					args.data.gridId = args.gridId;
					args.data.gridInstanceId = answerPopupParams[i]["gridInstanceId"];
				}
			}
		}
	});
const bindForm = function(formId, gridId, gridInstanceId) {
	let form = BX(formId, true);
	let controlName = "save";
	let func = (function(event) {
		form.removeEventListener('submit', func);
		let grid = BX.Main.gridManager.getInstanceById(gridId);
		if (!grid.getRows().hasEditable())
		{
			prepareForm(form, gridId, gridInstanceId);
			return true;
		}
		let func1 = function({data: [someGrid]}) {
			if (someGrid === grid)
			{
				form.appendChild(BX.create("INPUT", { "props": { "type": "hidden", "name": controlName, "value":  "Y" } } ));
				prepareForm(form, gridId, gridInstanceId);
				form.submit();
			}
		};
		EventEmitter.subscribeOnce("Grid::updated", func1);
		grid.editSelectedSave();
		return BX.PreventDefault(event);
	});

	form.addEventListener('submit', func);
	if (form.elements['apply'])
	{
		form.elements['apply'].addEventListener('mousedown', function () { controlName = "apply"; });
	}

	const f = function(e) {
		const el = e.target;
		const rows = BX.Main.gridManager.getInstanceById(gridId).getRows();
		if (!questionTypes.isCompatibilityMode() && rows)
		{
			let ids = [];
			rows.getRows().forEach(function(current){
				const attrs = BX.parseJSON(BX.data(current.getNode(), "item"), current);
				if (attrs &&
					BX.type.isPlainObject(attrs) &&
					attrs.hasOwnProperty("field_type") &&
					String(attrs["field_type"]) !== String(el.value) &&
					!answerTypes.isTextType(attrs["field_type"]))
				{
					current.select();
					ids.push(current.getId());
				}
			});
			if (ids.length > 0)
			{
				BX.Main.gridManager.getInstanceById(gridId).reloadTable(
					"POST",
					{
						ID: ids,
						action_button_grid_vote_answer: "change_answer_type",
						FIELD_TYPE : this.value
					});
			}
		}
	};
	document.querySelectorAll('#' + formId + ' [name=FIELD_TYPE]').forEach((node) => {
		node.addEventListener('change', f);
	});
};
const prepareForm = function(form, gridId/*, gridInstanceId*/) {
	const grid = BX.Main.gridManager.getInstanceById(gridId);
	if (grid)
	{
		const rows = grid.getRows().getRows();
		rows.forEach(function(current) {
			if (current.getIndex() < 1 || current.getId() === "template_0")
			{
				return;
			}
			const id = current.getId();

			form.appendChild(BX.create("INPUT", {
					props : {
						type : "hidden",
						name : "ANSWER[" + id.toLowerCase() + "][ID]",
						value : id
					}
				}
			));
			const attrs = BX.parseJSON(BX.data(current.getNode(), "item"), current);
			var func = function(prefix, params, depth)
			{
				var key;
				for (var j in params)
				{
					if (params.hasOwnProperty(j))
					{
						key = "[" + (depth > 0 ? j : String(j).toUpperCase()) + "]";
						if (BX.type.isPlainObject(params[j]))
						{
							func(prefix + key, params[j], depth + 1);
						}
						else
						{
							form.appendChild(BX.create('INPUT', {
									props : {
										type : "hidden",
										name : prefix + key,
										value : params[j]
									}
								}
							));
						}
					}
				}
			};
			if (BX.type.isPlainObject(attrs))
			{
				func("ANSWER[" + id + "]", attrs, 0);
			}
		});
	}
};
let picker = null;
BX.Vote.showColorPicker = function(input)
{
	if (picker === null)
	{
		picker = new BX.ColorPicker({
			bindElement: null,
			popupOptions: {
				angle: true,
			}
		})
	}
	picker.open({
		selectedColor: BX.type.isNotEmptyString(input.value) ? input.value : null,
		bindElement: input,
		onColorSelected: function (color) { input.value = color; }
	});
}