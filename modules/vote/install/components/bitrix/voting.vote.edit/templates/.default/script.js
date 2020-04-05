(function() {
if (window.BVoteConstructor)
	return;
// uploader section
	BX.addCustomEvent('onClickMulti', function(node){
		var answers = BX.findChildren(node.parentNode.previousSibling, {attribute : {"data-bx-answer-field" : "field-type"}}, true);
		for (var i = 0; i < answers.length; i++)
		{
			answers[i].value = node.checked ? "1" : "0";
		}
	});
top.BVoteConstructor = window.BVoteConstructor = function(Params)
{
	this.controller = Params.controller;
	this.maxQ = parseInt(Params['maxQ']);
	this.maxA = parseInt(Params['maxA']);
	this.q = {num : 0, cnt : 0};
	this.a = [{num : 0, cnt : 0}];
	this.InitVoteForm();

};
window.BVoteConstructor.prototype.checkAnswerAdding = function(qId) {
	var nodeQuestion = BX('question_' + qId);
	if (this.a[qId].list) {
		if (this.a[qId].list.firstChild) {
			BX.unbindAll(nodeQuestion);
			var node = this.a[qId].list.firstChild;
			do {
				BX.unbind(node.firstChild, "focus", BX.proxy(this._do, this));
			} while ((node = node.nextSibling) && BX(node));
		}
	}

	if (this.maxA > 0 && this.a[qId].cnt >= this.maxA) {
		if (this.a[qId].node) { BX.hide(this.a[qId].node); }
		return false;
	}
	if (this.a[qId].node) { BX.show(this.a[qId].node); }
	else if (this.a[qId].list) {
		if (this.a[qId].list.lastChild) {
			BX.bind(this.a[qId].list.lastChild.firstChild, "focus", BX.proxy(this._do, this));
		} else {
			BX.bind(nodeQuestion, "focus", BX.proxy(this._do, this));
		}
	}
	return true;
};

window.BVoteConstructor.prototype.checkQuestionAdding = function() {
	if (this.maxQ > 0 && this.q.cnt >= this.maxQ)
	{
		if (this.q.node)
			this.q.node.style.display = "none";
		return false;
	}
	if (this.q.node)
		this.q.node.style.display = "";
	return true;
};

window.BVoteConstructor.prototype.InitVoteForm = function() {
	var
		vOl = BX.findChild(this.controller, {"tagName" : "OL", "className" : "vote-questions"}, true),
		vLi = vOl.childNodes,
		regexp = /question_(\d+)/ig,
		num = !!vLi ? regexp.exec(vOl.lastChild.firstChild.firstChild.id) : [0, 0, 0];
	this.q.cnt = vLi.length;
	this.q.num = parseInt(num[1]);
	this.q.node = BX.findChild(this.controller, {"tagName" : "A", "className" : "addq"}, true);
	var
		aOl,
		aLi,
		regexpa,
		ii;
	for (ii = 0; ii < vLi.length; ii++)
	{
		if (vLi[ii] && vLi[ii]["tagName"] == "LI")
		{
			aOl = BX.findChild(vLi[ii], {"tagName" : "OL"}, true);
			aLi = BX.findChildren(aOl, {"tagName" : "LI"}, false);
			regexpa = /answer_(\d+)__(\d+)_/gi;
			num = [0, 0, 0];
			if (aOl.lastChild)
			{
				num = regexpa.exec(aOl.lastChild.firstChild.id);
			}
			else
			{
				num = regexp.exec(vLi[ii].firstChild.firstChild.id);
				num[2] = 0;
			}
			this.a[num[1]] = {
				cnt : aLi.length,
				num : parseInt(num[2]),
				node: false,
				"list": aOl};
			this.checkAnswerAdding(num[1]);
		}
	}
	this.checkQuestionAdding();

	var nodeTags = ["LABEL", "A"],
		a;
	for (var nodeTag in nodeTags)
	{
		if (nodeTags.hasOwnProperty(nodeTag))
		{
			a = BX.findChildren(vOl.parentNode, {"tagName" : nodeTags[nodeTag]}, true);
			for (ii in a)
			{
				if (a.hasOwnProperty(ii) && !BX.hasClass(a[ii], "vote-checkbox-label"))
				{
					BX.bind(a[ii], "click", BX.delegate(this._do, this));
				}
			}
		}
	}
};

window.BVoteConstructor.prototype._do = function(e)
{
	BX.PreventDefault(e);
	var
		reg = /(add|del)\w/,
		node = BX.proxy_context,
		className = reg.exec(BX.proxy_context.className),
		res,
		ii,
		a,
		q,
		aOl,
		qOl,
		regexp;
	if (!!className)
	{
		switch (className[0])
		{
			case "adda" :
				var qLi = BX.findParent(node, {"className" : "vote-question", "tagName" : "li"});
				aOl = BX.findChild(qLi, {"tagName" : "OL"}, true);
				regexp = /answer_(\d+)__(\d+)_/i;
				q = regexp.exec(node.getAttribute("id"));
				if (!q)
				{
					regexp = /question_(\d+)/i;
					q = regexp.exec(node.getAttribute("id"));
				}
				q = (!!q ? q[1] : null);
				if (q != null && this.checkAnswerAdding(q))
				{
					this.a[q].num++; this.a[q].cnt++;
					res = BX.create('DIV', {'html' : BX.message('VOTE_TEMPLATE_ANSWER').
							replace(/#Q#/gi, q).replace(/#A#/gi, this.a[q].num).
							replace(/#A_FIELD_TYPE#/gi, (BX("multi_" + q) && BX("multi_" + q).checked ? "1" : "0")).
							replace(/#A_VALUE#/gi, "").replace(/#A_PH#/gi, (this.a[q].num + 1))});
					a = BX.findChildren(res.firstChild, {"tagName" : "LABEL"}, true);
					for (ii in a)
					{
						if (a.hasOwnProperty(ii) && !BX.hasClass(a[ii], "vote-checkbox-label"))
						{
							BX.bind(a[ii], "click", BX.delegate(this._do, this));
						}
					}
					aOl.appendChild(res.firstChild);
					this.checkAnswerAdding(q);
				}
				break;
			case "dela" :
				regexp = /answer_(\d+)__(\d+)_/i;
				q = regexp.exec(node.getAttribute("for"));
				q = (!!q ? q[1] : null);
				var aLi = BX.findParent(node, {"tagName" : "li"});
				aOl = BX.findParent(aLi, {"tagName" : "OL"});
				node = BX(node.getAttribute("for"));
				if (node.value != '' && !confirm(BX.message("VVE_ANS_DELETE")))
					return false;
				aOl.removeChild(aLi);
				this.a[q].cnt--;
				this.checkAnswerAdding(q);
				break;
			case "addq" :
				if (this.checkQuestionAdding())
				{
					qOl = BX.findChild(node.parentNode, {"tag" : "OL"}, false);
					this.q.num++; this.q.cnt++;
					res = BX.message('VOTE_TEMPLATE_ANSWER').replace(/#A#/gi, 0).replace(/#A_PH#/gi, 1).replace(/#A_FIELD_TYPE#/gi, "0").replace(/#A_VALUE#/gi, "") +
						BX.message('VOTE_TEMPLATE_ANSWER').replace(/#A#/gi, 1).replace(/#A_PH#/gi, 2).replace(/#A_FIELD_TYPE#/gi, "0").replace(/#A_VALUE#/gi, "");
					res = BX.create("DIV", {html : BX.message('VOTE_TEMPLATE_QUESTION').
						replace(/#ANSWERS#/gi, res).replace(/#Q#/gi, this.q.num).
						replace(/#Q_VALUE#/gi, "").replace(/#Q_MULTY#/gi, "")});
					a = BX.findChildren(res.firstChild, {"tagName" : "LABEL"}, true);
					for (ii in a)
					{
						if (a.hasOwnProperty(ii) && !BX.hasClass(a[ii], "vote-checkbox-label"))
						{
							BX.bind(a[ii], "click", BX.delegate(this._do, this));
						}
					}

					this.a[this.q.num] = {
						num : 1,
						cnt : 2,
						node: false,
						"list": BX.findChild(res, {"tag" : "OL"}, true, false)};

					qOl.appendChild(res.firstChild);
					BX('question_' + this.q.num).focus();
					this.checkQuestionAdding();
					this.checkAnswerAdding(this.q.num);
				}
				break;
			case "delq" :
				q = node.getAttribute("for");
				var question = node.previousSibling;
				qOl = BX.findParent(question, {"tagName" : "OL"});
				q = parseInt(q.replace(/question_/gi, ""));
				if (question.value != '' && !confirm(BX.message("VVE_QUESTION_DELETE")))
					return false;
				qOl.removeChild(BX.findParent(question, {"tagName" : "LI"}));
				this.q.cnt--;
				this.checkQuestionAdding();
				break;
		}
	}
	return true;
};
})();