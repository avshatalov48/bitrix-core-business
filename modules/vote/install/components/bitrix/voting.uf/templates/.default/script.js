;(function(window) {
	var BX = window.BX;
	if (BX["Vote"])
		return;
	var actionUrl = (window["app"] ? BX.message("SITE_DIR") + "mobile/?mobile_action=vote" : "/bitrix/tools/vote/uf.php");

	BX.Vote = (function() {
		var d = function(node, params) {
			this.node = node;
			this.form = BX.findChild(this.node, {tagName : "FORM"}, true);
			this.id = params["id"];
			this.voteId = params["voteId"];
			this.params = params;
			var n, answer, i;
			// Error Node
			this.errorNode = BX.findChild(this.node, {attribute : {"data-bx-vote-role" : "error"}}, true);
			// Buttons
			for (var ii in this.buttons)
			{
				if (this.buttons.hasOwnProperty(ii))
				{
					n = BX.findChild(this.node, {attribute : {"data-bx-vote-button" : ii}}, true);
					if (n && BX.type.isFunction(this[ii]))
					{
						this[ii] = BX.delegate(this[ii], this);
						this.buttons[ii] = n;
						BX.bind(n, "click", this[ii]);
					}
				}
			}
			// Answers
			n = BX.findChildren(this.node, {tagName : "TR"}, true);
			while (n && (answer = n.pop()) && answer && answer.hasAttribute("data-bx-vote-answer"))
			{
				i = BX.findChild(answer, {"tagName" : "A", attribute : {"data-bx-vote-result" : "counter"}}, true);
				if (i)
				{
					if (window["app"])
					{
						BX.bind(answer, "click", BX.proxy(this.checkMobileUsers, this));
					}
					else
					{

						BX.bind(i, "click", BX.proxy(this.checkUsers, this));
						BX.adjust(i, {attrs : {"data-bx-vote-answer" : answer.getAttribute("data-bx-vote-answer")}});
					}
				}
			}

			this.onPullEvent = BX.delegate(function(command, params)
			{
				if (command == 'voting' && !!params && params["VOTE_ID"] == this.voteId && BX(this.node))
				{
					this.adjustResults(params);
				}
			}, this);

			if (window["app"])
			{
				app.onCustomEvent('onPullExtendWatch', {id: 'VOTE_' + this.voteId});
				BX.addCustomEvent('onPull-vote', this.onPullEvent);
			}
			else if (BX["PULL"])
			{
				BX.PULL.extendWatch('VOTE_' + this.voteId);
				BX.addCustomEvent("onPullEvent-vote", this.onPullEvent);
			}
		};
		d.prototype = {
			buttons: {
				showVoteForm: null,
				showResults: null,
				actVoting: null,
				stopOrResume: null,
				exportXls: null
			},
			params: {
			},
			url: actionUrl,
			showVoteForm: function(e) {
				if (this.node.getAttribute("data-bx-vote-lamp") == "green")
				{
					var f = BX.proxy(function(data) {
						if (data && data.data && data.data.event)
							this.adjustBallot(data.data.attach, data.data.event);
						this.node.setAttribute("data-bx-vote-form", "shown");
					}, this),
						ff = BX.proxy(function(error) {
						this.node.setAttribute("data-bx-vote-form", "shown");
					}, this);
					this.send({ action: "getBallot" }, e.target, f, ff);
				}
				BX.eventCancelBubble(e);
				return BX.PreventDefault(e);
			},
			showResults: function(e) {
				this.node.setAttribute("data-bx-vote-result", (this.node.getAttribute("data-bx-vote-result") == "shown" ? "hidden" : "shown"));
				BX.eventCancelBubble(e);
				return BX.PreventDefault(e);
			},
			stopOrResume: function (e) {
				this.send({ action: this.node.getAttribute("data-bx-vote-lamp") == "red" ? "resume": "stop" }, e.target, BX.proxy(function(data) {
					if (data["action"] == "stop")
					{
						this.node.setAttribute("data-bx-vote-result", "shown");
						this.node.setAttribute("data-bx-vote-form", "hidden");
						this.node.setAttribute("data-bx-vote-lamp", "red");
					}
					else
					{
						if (this.node.getAttribute("data-bx-vote-status") !== "voted")
							this.node.setAttribute("data-bx-vote-form", "shown");

						this.node.setAttribute("data-bx-vote-lamp", "green");
					}
					if (data["data"] && data["data"]["attach"])
						this.adjustResults(data["data"]["attach"]);
				}, this));
				BX.eventCancelBubble(e);
				return BX.PreventDefault(e);
			},
			exportXls: function (e) {
				BX.eventCancelBubble(e);
				top.location.href = BX.util.add_url_param(this.url, {action: "exportXls", attachId: this.id, sessid: BX.bitrix_sessid()});
				return BX.PreventDefault(e);
			},
			actVoting: function(e) {
				var data = BX.ajax.prepareForm(this.form).data;
				data["action"] = "vote";
				this.send(data, e.target, BX.proxy(function(data) {
					this.node.setAttribute("data-bx-vote-form", "hidden");
					this.node.setAttribute("data-bx-vote-result", "shown");
					this.adjustResults(data.data.attach);
				}, this), BX.proxy(function() {
					this.node.setAttribute("data-bx-vote-form", "shown");
				}, this));
				BX.eventCancelBubble(e);
				return BX.PreventDefault(e);
			},
			send: function(data, el, success, fail) {
				BX.addClass(el, "ui-btn-clock");
				data["sessid"] = BX.bitrix_sessid();
				data["attachId"] = this.id;
				BX.ajax({
					method: 'POST',
					url: BX.util.add_url_param(this.url, {action: data["action"], attachId: this.id}),
					data: data,
					dataType: 'json',
					onsuccess: BX.proxy(function(data) {
						BX.removeClass(el, 'ui-btn-clock');
						if (data.status == "success")
						{
							this.showError(null);
							if (BX.type.isFunction(success))
								success.apply(this, arguments);
						}
						else
						{
							if (data.status == "error" && data["errors"])
								this.showError(data["errors"]);
							if (BX.type.isFunction(fail))
								fail.apply(this, arguments);
						}
					}, this),
					onfailure: BX.proxy(function(){
						BX.removeClass(el, 'ui-btn-clock');
						if (BX.type.isFunction(fail))
							fail.apply(this, arguments);
					}, this)
				});
			},
			adjustBallot: function(attachment, event) {
				var q, a, e, i, j, es, qu, an, v,
					attach = attachment["QUESTIONS"],
					ballot = event["ballot"],
					extras = event["extras"];
				for (q in attach)
				{
					if (attach.hasOwnProperty(q))
					{
						qu = attach[q];
						e = [qu["FIELD_NAME"], qu["FIELD_NAME"] + "[]"];
						v = (ballot[q] || {});
						while(i = e.shift())
						{
							if (this.form.elements[i])
							{
								es = BX(this.form.elements[i]) ? [this.form.elements[i]] : this.form.elements[i];
								for (i = 0; i < es.length;i++)
								{
									if (v[es[i].value])
									{
										es[i].checked = "checked";
									}
									else
									{
										delete es[i].checked;
									}
								}
							}
						}

						for (a in attach[q]["ANSWERS"])
						{
							if (attach[q]["ANSWERS"].hasOwnProperty(a))
							{
								an = attach[q]["ANSWERS"][a];
								if (an["FIELD_TYPE"] >= 4)
								{
									if (this.form.elements[an["MESSAGE_FIELD_NAME"]])
										this.form.elements[an["MESSAGE_FIELD_NAME"]].value = (ballot[q] && ballot[q][a] && ballot[q][a]["MESSAGE"] ? ballot[q][a]["MESSAGE"] : "");
									else
										this.form.elements[an["FIELD_NAME"]].value = (ballot[q] && ballot[q][a] && ballot[q][a]["MESSAGE"] ? ballot[q][a]["MESSAGE"] : "");
								}
							}
						}
					}
				}
				for (i in extras)
				{
					if (extras.hasOwnProperty(i) &&
						(q = (BX(this.form.elements[String(attachment["FIELD_NAME"]).replace("#ENTITY_ID#", i)]))))
					{
						if (q.value == extras[i])
							q.checked = true;
						else
							delete q.checked;
					}
				}
			},
			adjustResults: function(attachment) {
				var questions = attachment["QUESTIONS"];
				BX.onCustomEvent(this.node, 'OnBeforeChangeData');
				var question, answer, i, q, per, n;
				for (q in questions)
				{
					if (questions.hasOwnProperty(q))
					{
						question = BX.findChild(this.node, {"attr": {"id": "question" + q}}, true);
						if (question)
						{
							for (i in questions[q]["ANSWERS"])
							{
								if (questions[q]["ANSWERS"].hasOwnProperty(i))
								{
									answer = BX.findChild(question, {"attr": {"data-bx-vote-answer": i}}, true);
									if (!!answer)
									{
										per = parseInt(questions[q]["ANSWERS"][i]["PERCENT"]);
										per = (isNaN(per) ? 0 : per);
										n = BX.findChild(answer, {attribute: {"data-bx-vote-result" : "counter"}}, true);
										BX.adjust(n, {"html" : questions[q]["ANSWERS"][i]["COUNTER"] + ""});
										delete n["VOTED_USER_OBJ"];
										BX.adjust(BX.findChild(answer, {"tagName" : "SPAN", attribute : {"data-bx-vote-result" : "percent"}}, true),
											{"html" : per + '%'});
										BX.adjust(BX.findChild(answer, {"tagName" : "DIV", attribute : {"data-bx-vote-result" : "bar"}}, true),
											{"style" : {"width" : per + '%'}});
									}
								}
							}
						}
					}
				}
				n = BX.findChild(this.node, {"tagName" : "DIV", attribute : {"data-bx-vote-result" : "counter"}}, true);
				BX.adjust(n, {"html" : attachment["COUNTER"] + ""});
				BX.onCustomEvent(this.controller, 'OnAfterChangeData');
			},
			checkUsers : function(event)
			{
				var node = event ? BX(event.currentTarget) : null;
				if (node.hasAttribute("data-bx-vote-answer"))
				{
					if (!node['VOTED_USER_OBJ'])
					{
						node.VOTED_USER_OBJ = new BVotedUser(
							node.getAttribute("data-bx-vote-answer"),
							node,
							{
								nameTemplate : this.params["nameTemplate"],
								urlTemplate : this.params["urlTemplate"],
								attachId : this.id
							}
						);
					}
					node.VOTED_USER_OBJ.click();
				}
			},
			checkMobileUsers : function(e) {
				if (this.node && this.node.getAttribute("data-bx-vote-form") !== "shown")
				{
					var node = BX.proxy_context,
						i = BX.findChild(node, {"tagName" : "A", attribute : {"data-bx-vote-result" : "counter"}}, true);

					if (i && parseInt(i.innerHTML) > 0)
					{
						BX.PreventDefault(e);
						app.openBXTable({
							url: BX.util.add_url_param(this.url, {action : "getMobileVoted", attachId : this.id, answerId : node.getAttribute("data-bx-vote-answer"), sessid : BX.bitrix_sessid()}),
							TABLE_SETTINGS : {
								markmode : false,
								cache: false
							}
						});
						return false;
					}
				}
				return true;
			},
			showError : function(errors) {
				var textError = "";
				if (BX.type.isArray(errors))
				{
					var t = [];
					for (var i = 0; i < errors.length; i++)
					{
						t.push(errors[i]["message"])
					}
					t = t.join("<br />");
					textError = (t === "" ? "Unknown error" : t);
					this.errorNode.innerHTML = textError;
					this.node.setAttribute("data-bx-vote-error", "shown");
				}
				else
				{
					this.errorNode.innerHTML = "";
					this.node.setAttribute("data-bx-vote-error", "hidden");
				}
			}
	};
		return d;
	})();

	var BVotedUser = (function()
	{
		var d = function(answerId, target, params)
		{
			this.id = ['vote', answerId, new Date().getTime()].join('-');
			this.answerId = answerId;
			this.node = target;
			this.status = 'ready';
			this.iNumPage = 0;

			this.urlTemplate = params["urlTemplate"];
			this.nameTemplate = params["nameTemplate"];
			this.attachId = params["attachId"];
			this.data = [];
			this.queue = [];
			this.popup = null;
			this.popupScrollCheck = this.popupScrollCheck.bind(this);
		};

		d.prototype = {
			url: actionUrl,
			click: function()
			{
				var votersCount = parseInt(this.node.innerHTML);
				if (votersCount > 0)
				{
					this.showPopup()
						.then(() => {
							if (this.data.length > 0)
							{
								this.buildVoters(this.data);
							}
						})
					;
				}
			},

			buildVoters: function(items)
			{
				var popupContainer = this.popup ? this.popup.getPopupContainer() : null;
				if (popupContainer === null)
				{
					return;
				}

				var container = popupContainer.querySelector('.bx-ilike-popup');
				popupContainer.querySelector('.bx-ilike-wrap-block').removeAttribute('style');
				var changed = false;
				items.forEach((voter) => {
					var voterId = BX.util.htmlspecialchars(voter['ID']);
					var voterName = BX.util.htmlspecialchars(voter['FULL_NAME']);
					var voterPhoto = BX.type.isNotEmptyString(voter['PHOTO_SRC']) ?
						encodeURI(BX.util.htmlspecialchars(voter['PHOTO_SRC'])) : null;
					var voterType = BX.type.isNotEmptyString(voter['TYPE']) ?
						BX.util.htmlspecialchars(voter['TYPE']) : null;

					var answerId = ["a", this.answerId,  "u", voterId].join('');
					if (container.querySelector('a#' + answerId) === null)
					{
						var html = [
							'<span class="bx-ilike-popup-avatar-new">',
								'<img src="', (voterPhoto ?? '/bitrix/images/main/blank.gif'),
									'" class="bx-ilike-popup-avatar-img', (voterPhoto ? '' : ' bx-ilike-popup-avatar-img-default'),
								'" />',
								'<span class="bx-ilike-popup-avatar-status-icon"></span>',
							'</span>',
							'<span class="bx-ilike-popup-name-new">', voterName, '</span>'
						].join('');

						container.appendChild(
							BX.create(voter['ID'] !== "HIDDEN" && this.urlTemplate ? "A" : "SPAN", {
								attrs: {id: answerId},
								props: {
									href: this.urlTemplate.replace(/#(USER_ID|ID)#/i, voterId),
									target: "_blank",
									className: "bx-ilike-popup-img" + (voterType ? " bx-ilike-popup-img-" + voterType : "")
								},
								html: html,
							})
						);
					}
					changed = true;
				});

				if (changed)
				{
					this.popup.adjustPosition({forceBindPosition: true});
					this.popupScrollCheck({currentTarget: container});
				}
			},

			makeError: function(errors)
			{
				var contentContainer = this.popup ? this.popup.getPopupContainer() : null;
				if (contentContainer === null)
				{
					return;
				}

				var text = (errors || [{message: BX.message("VOTE_ERROR_DEFAULT")}]).map((item) => {
					return item.message;
				}).join('');
				contentContainer.innerHTML = '<div class="bx-vote-popup-error-block">' + text + '</div>';
				this.popup.adjustPosition({forceBindPosition: true});
			},

			showPopup: function()
			{
				return new Promise((resolve) => {
					var popup = this.getPopup();
					popup.show();
					popup.setAngle({position:'bottom'});
					popup.adjustPosition({forceBindPosition: true});
					resolve(popup);
				})
			},

			getPopup: function()
			{
				if (this.popup)
				{
					return this.popup;
				}

				this.popup = new BX.PopupWindow('bx-vote-popup-cont-' + this.id, this.node, {
					lightShadow: true,
					offsetTop: -2,
					offsetLeft: 3,
					autoHide: true,
					closeByEsc: true,
					cacheable: false,
					bindOptions: {position: "top"},
					content: [
						'<span class="bx-ilike-wrap-block" style="display: none;">' +
							'<span class="bx-ilike-popup"><span class="bx-ilike-bottom_scroll"></span></span>' +
						'</span>',
						'<span class="bx-ilike-wait"></span>'
					].join(''),
					events: {
						onFirstShow: () => {
							this.send();
							BX.bind(
								this.popup.contentContainer.querySelector('.bx-ilike-popup'),
								'scroll' ,
								this.popupScrollCheck
							)
						},
						onClose: () => {
							this.popup = null;
						}
					}
				});
				return this.popup;
			},

			popupScrollCheck: function()
			{
				var container = this.popup ? this.popup.contentContainer : null;
				if (container === null)
				{
					return;
				}

				var res = container.querySelector('.bx-ilike-popup');
				if (
					(res.scrollTop > (res.scrollHeight - res.offsetHeight) / 1.5)
					||
					container.offsetHeight > res.offsetHeight
				)
				{
					this.send();
				}
			},

			send: function()
			{
				if (this.status !== 'ready')
				{
					if (this.status === 'busy')
					{
						this.queue.push(this.send.bind(this));
					}
					else if (this.status === 'done')
					{
						this.finalize();
					}
					return;
				}

				this.status = 'busy';

				BX.ajax({
					url: BX.util.add_url_param(this.url, {action: "getVoted", attachId: this.attachId, answerId: this.answerId}),
					method: 'POST',
					dataType: 'json',
					data: {
						iNumPage: (++this.iNumPage),
						nameTemplate: this.nameTemplate,
						sessid: BX.bitrix_sessid()
					},
					onsuccess: function(data)
					{
						if (data && data.status === "success")
						{
							data = data.data;

							this.buildVoters(data.items);
							this.data = this.data.concat(data.items);

							if (data["statusPage"] === "done" || data.items.length <= 0)
							{
								this.status = 'done';
								this.finalize();
							}
							else
							{
								this.status = 'ready';
								var f = this.queue.shift();
								if (f)
								{
									f.call(this);
								}
							}
						}
						else
						{
							this.status = 'error';
							this.makeError(data.errors);
							this.finalize();
						}
					}.bind(this),
					onfailure: function()
					{
						this.status = 'error';
						this.makeError();
						this.finalize();
					}.bind(this)
				});
			},

			finalize: function()
			{
				this.queue = [];
				var popupContainer = this.popup ? this.popup.getPopupContainer() : null;
				if (popupContainer === null)
				{
					return;
				}

				popupContainer.querySelector('.bx-ilike-wait').style.display = "none";

				BX.unbind(
					popupContainer.querySelector('.bx-ilike-popup'),
					'scroll' ,
					this.popupScrollCheck
				);
			}
		};
		return d;
	})();
})(window);