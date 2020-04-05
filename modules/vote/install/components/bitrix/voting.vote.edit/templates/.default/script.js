(function() {
	if (window.BVoteConstructor)
		return;
	BX.addCustomEvent('onClickMulti', function(node){
		var answers = BX.findChildren(node.parentNode.previousSibling, {attribute : {"data-bx-answer-field" : "field-type"}}, true);
		for (var i = 0; i < answers.length; i++)
		{
			answers[i].value = node.checked ? "1" : "0";
		}
	});
	//region Collecion
	var Collection = function(params) {
		this.map = new Map();
		this.maxSort = 0;
		this.currentId = 0;
		params = (params || {});
		this.maxCount = parseInt(params["maxCount"] || 0);
		this.onEntityHasBeenDeleted = this.onEntityHasBeenDeleted.bind(this);
	};
	Collection.prototype = {
		set : function (key, obj) {
			this.map.set(key, obj);
			BX.addCustomEvent(obj, "onEntityHasBeenDeleted", this.onEntityHasBeenDeleted);
			this.maxSort = Math.max(this.maxSort, obj.getSort());
			this.currentId++;
		},
		get : function (key) {
			return this.map.get(key)
		},
		keys : function() {
			return this.map.keys();
		},
		delete : function(key) {
			return this.map.delete(key);
		},
		onEntityHasBeenDeleted : function (entityId, entitySort, entity) {
			this.delete(entityId);
			if (this.maxSort === parseInt(entitySort))
			{
				var maxSort = 0;
				this.map.forEach(function(value) {
					maxSort = Math.max(maxSort, parseInt(value.getSort()));
				});
				this.maxSort = maxSort;
			}
		},
		getNextId : function() {
			return this.currentId;
		},
		getMaxSort : function() {
			return this.maxSort;
		},
		canAdd : function() {
			return !(this.maxCount > 0 && this.maxCount <= this.map.size);
		}
	};
	//endregion
	//region Entity
	var Entity = function(node, param) {
		this.id = [(param["groupId"] || "vote"), node.getAttribute("data-bx-number").toString()];
		this.eventObject = param["eventObject"];
		this.nodes = {
			main : node,
			sort : null
		};
		this.delete = this.delete.bind(this);
		this.__sortDD = this.__sortDD.bind(this);
		this.onbxdragstart = this.onbxdragstart.bind(this);
		this.onbxdragstop = this.onbxdragstop.bind(this);
		this.onbxdrag = this.onbxdrag.bind(this);
		this.onbxdraghout = this.onbxdraghout.bind(this);
		this.onbxdestdraghover = this.onbxdestdraghover.bind(this);
		this.onbxdestdraghout = this.onbxdestdraghout.bind(this);
		this.onbxdestdragfinish = this.onbxdestdragfinish.bind(this);

		BX.addCustomEvent(this.eventObject, "onEntityHasBeenSorted", this.__sortDD);
		this.init(param);
		Entity.repo.set(this.id, this);
	};
	Entity.repo = new Map();
	Entity.getByFullId = function(id) {
		return Entity.repo.get(id);
	};
	Entity.deleteByFullId = function(id) {
		return Entity.repo.delete(id);
	};
	Entity.prototype = {
		init : function(param) { },
		bind : function(node) {
			var nodes, ii, methd;
			if (BX(node))
			{
				nodes = BX.findChildren(node, {attribute : "data-bx-action"}, true);
				if (node.hasAttribute("data-bx-action"))
				{
					nodes.unshift(node)
				}
			}
			else
			{
				nodes = BX.findChildren(this.nodes.main, {attribute : "data-bx-action"}, true);
			}
			for (ii = 0; ii < nodes.length; ii++)
			{
				if (!nodes[ii].hasAttribute("data-bx-bo und"))
				{
					methd = "__" + nodes[ii].getAttribute("data-bx-action");
					if (typeof this[methd] === "function")
					{
						nodes[ii].setAttribute("data-bx-bound", "Y");
						BX.bind(nodes[ii], "click", this[methd].bind(this));
					}
				}
			}
			this.nodes.main["entityId"] = this.getFullId();
		},
		initDD : function(){
			if (window["jsDD"] && !this.nodes.main.hasAttribute("data-bx-drag-sensitive"))
			{
				var nodes = BX.findChildren(this.nodes.main, {attribute : "data-bx-action"}, true),
					ii;

				for (ii = 0; ii < nodes.length; ii++)
				{
					if ((nodes[ii].getAttribute("data-bx-action") === "sortDD") &&
						!nodes[ii].hasAttribute("data-bx-bound-dd") &&
						nodes[ii].getAttribute("data-bx-bound") === "Y"
					)
					{
						BX.unbindAll(nodes[ii], "click");
						this.nodes.dd = nodes[ii];
						this.nodes.dd.setAttribute("data-bx-bound-dd", "Y");
						this.nodes.dd["entityId"] = this.getFullId();
						BX.addClass(this.nodes.dd, "bx-drag-draggable");
						this.nodes.main.hasAttribute("data-bx-drag-sensitive", "Y");

						BX.addClass(this.nodes.main, "bx-drag-drag-sensitive");
						window.jsDD.registerObject(this.nodes.dd);
						this.nodes.dd.onbxdragstart = this.onbxdragstart;
						this.nodes.dd.onbxdragstop = this.onbxdragstop;
						this.nodes.dd.onbxdrag = this.onbxdrag;
						this.nodes.dd.onbxdraghout = this.onbxdraghout;

						window.jsDD.registerDest(this.nodes.main);
						this.nodes.main.onbxdestdraghover = this.onbxdestdraghover;
						this.nodes.main.onbxdestdraghout = this.onbxdestdraghout;
						this.nodes.main.onbxdestdragfinish = this.onbxdestdragfinish;
						var inputs = BX.findChild(this.nodes.main, {tagName : "INPUT", props : {"type" : "text"}}, true, true);
						for (ii = 0; ii <= inputs.length; ii++)
						{
							BX.bind(inputs[ii], "mousedown", BX.eventCancelBubble);
						}
					}
				}
			}
		},
		getSort : function() {
			if (!this.__sort)
				this.__sort = parseInt(this.nodes["sort"].value);
			return this.__sort;
		},
		setSort : function(sort) {
			this.__sort = this.nodes["sort"].value = parseInt(sort);
		},
		__sortDD: function(fromSort, toSort, fromEntity, toEntity) {
			if (BX.type.isInteger(fromSort) && fromEntity.getGroupId() === this.getGroupId())
			{
				if (this === fromEntity)
				{
					this.setSort(toSort);
				}
				else if (fromSort > toSort)
				{
					if (toSort <= this.getSort() && this.getSort() <= fromSort)
					{
						this.setSort(this.getSort() + 10);
					}
				}
				else if (fromSort < toSort)
				{
					if (fromSort < this.getSort() && this.getSort() <= toSort)
					{
						this.setSort(this.getSort() - 10);
					}
				}
			}
		},
		__sortDown: function() {
			if (this.nodes.main.nextSibling && this.nodes.main.nextSibling["entityId"])
			{
				var entity = Entity.getByFullId(this.nodes.main.nextSibling["entityId"]);
				this.nodes.main.parentNode.insertBefore(this.nodes.main.nextSibling, this.nodes.main);
				BX.onCustomEvent(this.eventObject, "onEntityHasBeenSorted", [this.getSort(), entity.getSort(), this, entity]);
			}
		},
		__sortUp: function() {
			if (this.nodes.main.previousSibling && this.nodes.main.previousSibling["entityId"])
			{
				var entity = Entity.getByFullId(this.nodes.main.previousSibling["entityId"]);
				this.nodes.main.parentNode.insertBefore(this.nodes.main, this.nodes.main.previousSibling);
				BX.onCustomEvent(this.eventObject, "onEntityHasBeenSorted", [this.getSort(), entity.getSort(), this, entity]);
			}
		},
		getFullId : function(join) {
			return (join === true ? this.id.join("_") : this.id);
		},
		getId : function() {
			return this.id[1];
		},
		getGroupId : function() {
			return this.id[0];
		},
		"delete" : function() {
			var buf = [], ii,
			sort = this.getSort();
			for (ii in this.nodes)
			{
				if (this.nodes.hasOwnProperty(ii))
				{
					buf.push(ii);
				}
			}
			for (ii = 0; ii <= buf.length; ii++)
			{
				if (this.nodes.hasOwnProperty(buf[ii]))
				{
					this.nodes[buf[ii]] = null;
					delete this.nodes[buf[ii]];
				}
			}
			Entity.deleteByFullId(this.getFullId());
			BX.onCustomEvent(this, "onEntityHasBeenDeleted", [this.getFullId(), sort, this]);
		},
		onbxdragstart : function() {
			var __dragNode = BX.create("DIV", {
					attrs : {
						className : "bx-drag-object",
					},
					style : {
						position : "absolute",
						zIndex : 10,
						width : this.nodes.main.parentNode.clientWidth + 'px'
					},
					html : this.nodes.main.outerHTML.replace(new RegExp(this.nodes.main.getAttribute("id"), "gi"), "DragCopy")
				});
			this.nodes.drag = __dragNode;
			this.nodes.dd["entitySort"] = this.getSort();
			this.dragPos = {
				"main" : BX.pos(this.nodes.main),
				"parent" : BX.pos(this.nodes.main.parentNode)
			};
			document.body.appendChild(this.nodes.drag);
			BX.addClass(this.nodes.main, "bx-drag-source");
			__dragNode = null;
		},
		onbxdragstop : function() {
			if (this.dragPos)
			{
				BX.removeClass(this.nodes.main, "bx-drag-source");
				this.nodes.drag.parentNode.removeChild(this.nodes.drag);
				this.nodes.drag = null;
				delete this.nodes.drag;
				delete this.dragPos;
			}
			return true;
		},
		onbxdrag : function(x, y) {
			if (this.nodes.drag)
			{
				if (this.dragPos)
				{
					if (!this.dragPos.main.deltaX)
						this.dragPos.main.deltaX = this.dragPos.main.left - x;
					if (!this.dragPos.main.deltaY)
						this.dragPos.main.deltaY = this.dragPos.main.top - y;
					x += this.dragPos.main.deltaX;
					y += this.dragPos.main.deltaY;
					y = Math.min(Math.max(y, this.dragPos.parent.top), this.dragPos.parent.bottom);
				}
				this.nodes.drag.style.left = x + 'px';
				this.nodes.drag.style.top = y + 'px';
			}
		},
		onbxdraghout : function() { },
		onbxdestdraghover : function(currentNode) {
			if (this.nodes.dd !== currentNode)
			{
				var sort = parseInt(currentNode["entitySort"]);
				if (this.getSort() < sort)
					BX.addClass(this.nodes.main, "bx-drag-over-up");
				else
					BX.addClass(this.nodes.main, "bx-drag-over-down");
			}
		},
		onbxdestdraghout : function() {
			BX.removeClass(this.nodes.main, "bx-drag-over-up");
			BX.removeClass(this.nodes.main, "bx-drag-over-down");
		},
		onbxdestdragfinish : function(currentNode) {
			BX.removeClass(this.nodes.main, "bx-drag-over-up");
			BX.removeClass(this.nodes.main, "bx-drag-over-down");
			if (this.nodes.dd !== currentNode)
			{
				var entity = Entity.getByFullId(currentNode["entityId"]);
				if (entity.getGroupId() === this.getGroupId())
				{
					var sort = parseInt(currentNode["entitySort"]);
					if (this.getSort() < sort)
					{
						this.nodes.main.parentNode.insertBefore(entity.nodes.main, this.nodes.main);
						BX.onCustomEvent(this.eventObject, "onEntityHasBeenSorted", [entity.getSort(), this.getSort(), entity, this]);
					}
					else
					{
						if (this.nodes.main.nextSibling)
							this.nodes.main.parentNode.insertBefore(entity.nodes.main, this.nodes.main.nextSibling);
						else
							this.nodes.main.parentNode.appendChild(entity.nodes.main);
						BX.onCustomEvent(this.eventObject, "onEntityHasBeenSorted", [entity.getSort(), this.getSort(), entity, this]);
					}
				}
			}
		}
	};
	//endregion
	//region Question
	var Question = function(node, param) {
		this.answers = new Collection({maxCount : param["maxAnswersCount"]});
		this.visibleId = 0;
		Question.superclass.constructor.apply(this, arguments);
		this.nodes.sort = BX.findChild(this.nodes["main"], {attribute : {"data-bx-question-field" : "C_SORT"}}, true);
	};
	BX.extend(Question, Entity);
	Question.prototype.init = function(params) {
		this.nodes.answerList = BX.findChild(this.nodes.main, {attribute : {"data-bx-role" : "answer-list"}}, true);
		var nodes = BX.findChild(this.nodes.answerList, {tagName : "LI", attribute : {"data-bx-role" : "answer"}}, false, true),
			ii, res;
		for (ii = 0; ii < nodes.length; ii++)
		{
			res = new Answer(
				nodes[ii],
				{
					eventObject : params["eventObject"],
					groupId : this.getId()
				}
			);
			this.answers.set(res.getId(), res);
			this.visibleId++;
		}
		this.bind();
		this.initDD();
		this.addAnswer = this.addAnswer.bind(this);
		this.toggleAddNode = this.toggleAddNode.bind(this);
		this.toggleAddNode(null, null);
		BX.addCustomEvent(this.eventObject, "onAnswerHasBeenAdded", this.toggleAddNode);
		BX.addCustomEvent(this.eventObject, "onAnswerHasBeenDeleted", this.toggleAddNode);
	};
	Question.prototype.toggleAddNode = function(answerId, answer) {
		if (answer === null || answer.getGroupId() === this.getId())
		{
			var nodes = BX.findChild(this.nodes["main"], {attribute : {"data-bx-action" : "adda"}}, true, true),
				ii;
			for (ii = 0; ii < nodes.length; ii++)
			{
				BX.unbind(nodes[ii], "focus", this.addAnswer);
			}
			if (this.answers.canAdd())
			{
				BX.bind(nodes[nodes.length - 1], "focus", this.addAnswer);
			}
		}
	};
	Question.prototype.addAnswer = function() {
		var answerId = this.answers.getNextId(),
		replacement = {
			"#Q#" : this.getId(),
			"#A#" : answerId,
			"#A_FIELD_TYPE#": (BX("multi_" + this.getId()) && BX("multi_" + this.getId()).checked ? "1" : "0"),
			"#A_VALUE#" : "",
			"#A_C_SORT#" : this.answers.getMaxSort() + 10,
			"#A_PH#" : (++this.visibleId)
		},
		text = BX.message('VOTE_TEMPLATE_ANSWER'), ii;
		for (ii in replacement)
		{
			if (replacement.hasOwnProperty(ii))
			{
				text = text.replace(new RegExp(ii, "gi"), replacement[ii]);
			}
		}
		var node = (BX.create('DIV', {'html' : text})).firstChild;
		this.nodes.answerList.appendChild(node);
		var res = new Answer(
			node,
			{
				eventObject : this.eventObject,
				groupId : this.getId()
			}
		);
		this.bind(node);
		this.initDD(node);
		this.answers.set(res.getId(), res);
		BX.onCustomEvent(this.eventObject, "onAnswerHasBeenAdded", [res.getFullId(), res]);
	};
	Question.prototype.__delq = function() {
		var buf = BX.findChild(this.nodes.main, {attribute : {"data-bx-question-field" : "MESSAGE"}}, true);
		if (!buf || buf.value === '' || confirm(BX.message("VVE_QUESTION_DELETE")))
		{
			var ids = this.answers.keys(),
				answerId = ids.next();
			buf = this.nodes.main;
			while(!answerId.done)
			{
				this.answers.get(answerId.value).delete();
				answerId = ids.next();
			}
			this.delete();
			buf.parentNode.removeChild(buf);
			BX.onCustomEvent(this.eventObject, "onQuestionHasBeenDeleted", [this.getFullId(), this]);
		}
	};
	//endregion
	//region Answer
	var Answer = function(node, param) {
		Answer.superclass.constructor.apply(this, arguments);
		this.nodes.sort = BX.findChild(this.nodes["main"], {attribute : {"data-bx-answer-field" : "C_SORT"}}, true);
	};
	BX.extend(Answer, Entity);
	Answer.prototype.init = function(param) {
		this.bind();
		this.initDD(this.nodes.main);
	};
	Answer.prototype.__dela = function(e) {
		var buf = BX.findChild(this.nodes.main, {attribute : {"data-bx-answer-field" : "MESSAGE"}}, true), ii;
		if (!buf || buf.value === '' || confirm(BX.message("VVE_ANS_DELETE")))
		{
			buf = this.nodes.main;
			this.delete();
			buf.parentNode.removeChild(buf);
			BX.onCustomEvent(this.eventObject, "onAnswerHasBeenDeleted", [this.getFullId(), this]);
		}
	};
	//endregion
	//region Vote
top.BVoteConstructor = window.BVoteConstructor = function(Params)
{
	this.controller = Params.controller;
	this.questions = new Collection({maxCount : Params["maxQ"]});
	this.nodes = {
		questionList : BX.findChild(this.controller, { attribute : {"data-bx-role" : "question-list"}}, true),
		action : BX.findChild(this.controller, { attribute : {"data-bx-action" : "addq"}}, true)
	};
	this.toggleAddNode = this.toggleAddNode.bind(this);
	this.visibleId = 0;
	this.maxAnswersCount = Params["maxA"];
	this.init(Params);
};
window.BVoteConstructor.prototype.init = function() {
	var qNodes = BX.findChild(
		this.controller,
		{
			tagName : "LI",
			attribute : {"data-bx-role" : "question"}
		},
		true,
		true
	), ii, res;
	for (ii = 0; ii < qNodes.length; ii++)
	{
		res = new Question(
			qNodes[ii],
			{
				eventObject : this,
				maxAnswersCount : this.maxAnswersCount
			}
		);
		this.questions.set(res.getId(), res);
	}

	this.toggleAddNode();
	BX.addCustomEvent(this, "onQuestionHasBeenAdded", this.toggleAddNode);
	BX.addCustomEvent(this, "onQuestionHasBeenDeleted", this.toggleAddNode);
	BX.bind(this.nodes.action, "click", this.addq.bind(this));
};
window.BVoteConstructor.prototype.toggleAddNode = function() {
	if (this.questions.canAdd())
	{
		BX.show(this.nodes.action);
	}
	else
	{
		BX.hide(this.nodes.action);
	}
};

window.BVoteConstructor.prototype.addq = function(e) {
	BX.PreventDefault(e);
	if (this.questions.canAdd())
	{
		var res = BX.message('VOTE_TEMPLATE_ANSWER').
			replace(/#A#/gi, 0).
			replace(/#A_PH#/gi, 1).
			replace(/#A_FIELD_TYPE#/gi, "0").
			replace(/#A_C_SORT#/gi, "10").
			replace(/#A_VALUE#/gi, "") +
			BX.message('VOTE_TEMPLATE_ANSWER').
			replace(/#A#/gi, 1).
			replace(/#A_PH#/gi, 2).
			replace(/#A_FIELD_TYPE#/gi, "0").
			replace(/#A_C_SORT#/gi, "20").
			replace(/#A_VALUE#/gi, "");
		res = BX.create("DIV", {html : BX.message('VOTE_TEMPLATE_QUESTION').
			replace(/#ANSWERS#/gi, res).
			replace(/#Q#/gi, this.questions.getNextId()).
			replace(/#Q_C_SORT#/gi, this.questions.getMaxSort() + 10).
			replace(/#Q_PH#/gi, (++this.visibleId)).
			replace(/#Q_VALUE#/gi, "").replace(/#Q_MULTY#/gi, "")});
		var buf = new Question(
			res.firstChild,
			{
				eventObject : this,
				maxAnswersCount : this.maxAnswersCount
			}
		);
		buf.visibleId = 2;
		this.questions.set(buf.getId(), buf);
		this.nodes.questionList.appendChild(res.firstChild);
		BX.onCustomEvent(this, "onQuestionHasBeenAdded", [buf.getFullId(), buf]);
	}
};
	//endregion
})();