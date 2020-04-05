;(function(){
	if (!window["BX"] || window["BX"]["MPLForm"] || !window["app"])
		return;

	var BX = window["BX"],
		repo = {
			entityId : 0,
			text : "",
			form : {},
			list : {},
			comments : {},
			commentExemplarId : {}
		},
		makeId = function(ENTITY_XMIL_ID, ID) {
			return ENTITY_XMIL_ID + '-' + (ID > 0 ? ID : '0');
		};
	var setText = function(text) {
		repo.text = (BX.type.isNotEmptyString(text) ? text : "");
		if (BX["localStorage"] && repo.entityId)
		{
			var res = BX.localStorage.get("main.post.list/text");
			res = (res || {});
			if (BX.type.isNotEmptyString(repo.text))
			{
				res[repo.entityId] = repo.text;
			}
			else
			{
				delete res[repo.entityId];
			}
			BX.localStorage.set("main.post.list/text", res);
		}
	},
	getText = function(entityId) {
		var text = "";
		if (BX["localStorage"] && entityId)
		{
			var res = BX.localStorage.get("main.post.list/text");
			if (res)
			{
				text = (res[entityId] || "");
				delete res[entityId];
				BX.localStorage.set("main.post.list/text", res);
			}
		}
		return text;
	};
	BX.addCustomEvent(window, 'OnUCFormSubmit', function(){ setText(''); });

	BXMobileApp.addCustomEvent("main.post.form/text", function(text){
		text = BX.type.isArray(text) ? text[0] : text;
		setText(text);
	});
	var inner = {
		keyBoardIsShown : false,
		mention : {}
	},
		appendToForm = function(fd, key, val) {
		if (!!val && typeof val == "object")
		{
			for (var ii in val)
			{
				if (val.hasOwnProperty(ii))
				{
					appendToForm(fd, key + '[' + ii + ']', val[ii]);
				}
			}
		}
		else
		{
			fd.append(key, (!!val ? val : ''));
		}
	};
	window.app.exec("enableCaptureKeyboard", true);
	BX.addCustomEvent("onKeyboardWillShow", function() { inner.keyBoardIsShown = true; });
	BX.addCustomEvent("onKeyboardDidHide", function() { inner.keyBoardIsShown = false; });
	BX.addCustomEvent("OnUCCommentWasRead", function(id) {
		var node = BX('record-' + id.join('-'));
		if (node)
		{
			BX.removeClass(node, "post-comment-block-new");
		}
	});

	var commentObj = function(id, text, attachments) {
		this.id = id;
		this.text = (text || "");
		this.attachments = (attachments || []);
		this.mentions = {};
	};
	commentObj.prototype = {
		text : "",
		attachments : [],
		node : null,
		getText : function() {
			return this.text;
		}
	};
	/*
		@return commentObj
	 */
	commentObj.getInstance = function(id, text, attachments) {
		var res = null;
		if (!BX.type.isArray(id) && id && id["___id"] && repo["comments"][id["___id"]])
		{
			res = id;
		}
		else if (repo["comments"][id.join("-")])
		{
			res = repo["comments"][id.join("-")];
		}
		else
		{
			res = new commentObj(id, text, attachments);
			res.___id = id.join("-");
			repo["comments"][id.join("-")] = res;
		}
		return res;
	};
	commentObj.removeInstance = function(comment) {
		if (comment && comment["___id"])
			delete repo["comments"][comment["___id"]];
	};
	var MPFForm = function(id) {
		this.bindEvents();
		repo['form'][this.handlerId] = this;
		this.entitiesId = {};

		this.comment = null;

		this.handlerId = id;
		this.handler = null;
		this.handlerEvents = {
			onMPFUserIsWriting : BX.delegate(this.writing, this),
			onMPFHasBeenDestroyed : BX.delegate(this.reboot, this)
		};

		this.visible = false;

		this.bindHandler = BX.delegate(this.bindHandler, this);
		BX.addCustomEvent(window, "onMPFIsInitialized", this.bindHandler);
		if (BX["MPF"])
			this.bindHandler(BX["MPF"].getInstance(this.handlerId));
		this.jsCommentId = BX.util.getRandomString(20);
	};
	MPFForm.prototype = {
		bindHandler : function(handler) {
			if (handler && handler.id == this.handlerId)
			{
				this.handler = handler;

				BX.removeCustomEvent(window, "onMPFIsInitialized", this.bindHandler);

				for (var ii in this.handlerEvents)
				{
					if (this.handlerEvents.hasOwnProperty(ii))
					{
						BX.addCustomEvent(this.handler, ii, this.handlerEvents[ii]);
					}
				}

				this.closeWait();
				BX.onCustomEvent(this, 'OnUCFormInit', [this]);
			}
		},
		bindEvents : function() {
			this.windowEvents = {
				OnUCUserReply : BX.delegate(function(entityId, authorId, authorName) {
					if (this.entitiesId[entityId])
					{
						var comment = [entityId, 0];
						authorId = parseInt(authorId);
						if (authorId > 0 && authorName)
						{
							comment = this.initComment(comment, "", false);
							comment.mentions[authorName] = '[USER=' + authorId + ']' + authorName + '[/USER]';
							var text = (this.handler && this.handler.simpleForm ? this.handler.simpleForm.writingParams["~text"] : comment.text);
							comment.text = text + (text == "" ? "" : " ") + '[USER=' + authorId + ']' + authorName + '[/USER]' + ', ';
						}
						this.show(comment, comment.text, false);
					}
				}, this),

				OnUCAfterRecordEdit : BX.delegate(function(entityId, id, data, act)
				{
					if (this.entitiesId[entityId]) {
						if (act === "EDIT")
						{
							this.show([entityId, id], data['messageBBCode'], data['messageFields']);
						}
						else if (data['errorMessage'])
						{
							this.showError([entityId, id], data['errorMessage']);
						}
						else if (data['okMessage'])
						{
							this.showNote([entityId, id], data['okMessage']);
						}
					}
				}, this)
			};

			BX.addCustomEvent(window, 'OnUCUserReply', this.windowEvents.OnUCUserReply);
			BX.addCustomEvent(window, 'OnUCAfterRecordEdit', this.windowEvents.OnUCAfterRecordEdit);
		},
		reboot : function(id, oldObj, newObj) {
			for (var ii in this.handlerEvents)
			{
				if (this.handlerEvents.hasOwnProperty(ii))
				{
					BX.removeCustomEvent(this.handler, ii, this.handlerEvents[ii]);
				}
			}
			this.bindHandler(newObj);
		},
		linkEntity : function(id, data) {
			if (this.handler === null)
			{
				this._linkEntity = BX.delegate(function(){this.linkEntity(id, data);}, this);
				BX.addCustomEvent(this, 'OnUCFormInit', this._linkEntity);
			}
			else
			{
				if (this["_linkEntity"])
					BX.removeCustomEvent(this, 'OnUCFormInit', this["_linkEntity"]);
				this.entitiesId[id] = data;
				repo.entityId = id;

				var f = BX.proxy(function(str){
					this.comment = this.reinitComment({id : [id, 0], text : str});
					this.comment.text = str;
					this.handler.init(this.comment);
				}, this);

				if (false && window["platform"] == "ios")
				{
					window.BXMobileApp.UI.Page.TextPanel.getText(f);
				}
				else
				{
					f(getText(id));
				}
			}
		},
		writing : function(comment) {
			BX.onCustomEvent(window, 'OnUCUserIsWriting', [comment["id"][0], comment["id"][1], this.jsCommentId]);
		},
		reinitComment : function(comment) {
			var id = [comment["id"][0], 0],
				text = (comment["text"] || "");
			commentObj.removeInstance(comment);
			return this.initComment(id, text, []);
		},
		initComment : function(id, text, data) {
			var comment = commentObj.getInstance(id, text, data);
			if (comment["bound"] !== "Y")
			{
				BX.addCustomEvent(comment, "onCancel", BX.delegate(BX.delegate(this.submitClear, this))); // Release comment
				BX.addCustomEvent(comment, "onStart", BX.delegate(BX.delegate(this.submitStart, this))); // Draw comment
				BX.addCustomEvent(comment, "onSubmit", BX.delegate(BX.delegate(this.submit, this))); // Submit form
				BX.addCustomEvent(comment, "onError", BX.delegate(BX.delegate(function(c, text) {
					this.showError(comment, text);
					this.submitClear(comment);
				}, this))); // Error form
				comment["bound"] = "Y";
			}
			return comment;
		},
		show : function(id, text, data) {
			this.comment = this.initComment(id, text, data);
			this.jsCommentId = BX.util.getRandomString(20);
			BX.onCustomEvent(this.handler, 'OnUCFormBeforeShow', [this, text, data]);
			repo.entityId = id[0];
			this.handler.show(this.comment, (!!data));
			BX.onCustomEvent(this.handler, 'OnUCFormAfterShow', [this, text, data]);
			return true;
		},
		submitClear : function(comment) {
			commentObj.removeInstance(comment);
			this.jsCommentId = BX.util.getRandomString(20);
			if (this.comment == comment)
			{

				this.comment = this.initComment([comment.id[0], 0], "", []);
				repo.entityId = comment.id[0];
				this.handler.init(this.comment);
			}
		},
		submitStart : function(comment, text, attachments) {
			BX.onCustomEvent(window, 'OnUCFormBeforeSubmit', [comment.id[0], comment.id[1], comment, this, text, attachments]); // Preview commetn
		},
		submit : function(comment) {
			var text = comment.getText(),
				attachments = comment.attachments,
				entityHdl = this.entitiesId[comment.id[0]],
				post_data = this.handler.getForm({
					ENTITY_XML_ID : comment.id[0],
					REVIEW_TEXT : text,
					NOREDIRECT : "Y",
					MODE : "RECORD",
					AJAX_POST : "Y",
					id : comment.id,
					sessid : BX.bitrix_sessid(),
					SITE_ID : BX.message("SITE_ID"),
					LANGUAGE_ID : BX.message("LANGUAGE_ID")
				}),
				post = new window.MobileAjaxWrapper(),
				fd = new window.FormData(),
				ii;
			if (this.jsCommentId !== null)
				post_data['COMMENT_EXEMPLAR_ID'] = this.jsCommentId;

			if (comment.id[1] > 0)
			{
				post_data['REVIEW_ACTION'] = "EDIT";
				post_data["FILTER"] = {ID : comment.id[1]};
				if (post_data["act"]) // if it is a socialnetwork
				{
					post_data["act"] = "edit";
					post_data["edit_id"] = comment.id[1];
				}
			}
			if (entityHdl['fields'])
			{
				for (ii in entityHdl['fields'])
				{
					if (entityHdl['fields'].hasOwnProperty(ii))
					{
						post_data[ii] = entityHdl['fields'][ii];
					}
				}
			}

			BX.onCustomEvent(window, 'OnUCFormSubmit', [comment.id[0], comment.id[1], this, post_data]);
			for (ii in post_data)
			{
				if (post_data.hasOwnProperty(ii))
				{
					appendToForm(fd, ii, post_data[ii]);
				}
			}
			if (attachments)
			{
				for (var ij = 0; ij < attachments.length; ij++)
				{
					appendToForm(fd, attachments[ij]["fieldName"], attachments[ij]["fieldValue"]);
				}
			}

			post.Wrap({
				method: 'POST',
				url: entityHdl['url'],
				data: {},
				type: 'json',
				processData : true,
				start : false,
				preparePost : false,
				callback: BX.proxy(function(data) {
					BX.onCustomEvent(window, 'OnUCFormResponse', [comment.id[0], comment.id[1], this, data, comment]);
					if (data['errorMessage'])
						this.showError(comment, data['errorMessage']);
					else
						BX.onCustomEvent(window, 'OnUCAfterRecordAdd', [comment.id[0], comment.id[1], this, data, comment]);
				}, this),
				callback_failure: BX.delegate(function(data) {
					BX.onCustomEvent(window, 'OnUCFormResponse', [comment.id[0], comment.id[1], this, data, comment]);
					this.showError(comment, BX.message('INCORRECT_SERVER_RESPONSE'));
				}, this)
			});
			post.xhr.send(fd);

			this.submitClear(comment);
		},
		showError : function(comment, text) {
			if (BX.type.isArray(comment))
				comment = this.initComment(comment, "", []);

			text = '<div class="feed-add-info-text"><span class="feed-add-info-icon"></span>' +
					'<b>' + BX.message('FC_ERROR') + '</b><br />' + text + '</div>';
			if (comment && comment.node)
			{
				BX.addClass(comment.node, "feed-com-block-cover-undelivered");

				var bindUndelivered = (
					typeof comment.attachments == 'undefined'
					|| comment.attachments.length <= 0
				);

				if (
					!bindUndelivered
					&& BX.type.isArray(comment.attachments)
				)
				{
					bindUndelivered = true;

					for (var ij = 0; ij < comment.attachments.length; ij++)
					{
						if (
							BX.type.isNotEmptyString(comment.attachments[ij].fieldValue) // attached UF
							|| BX.type.isNotEmptyString(comment.attachments[ij].url) // attached file
						)
						{
							bindUndelivered = false;
							break;
						}
					}
				}

				if (bindUndelivered)
				{
					BX.bind(comment.node, 'click', BX.proxy(function(e) {
						BX.unbindAll(comment.node);
						BX.removeClass(comment.node, "feed-com-block-cover-undelivered");
						this.handler.comment = comment;
						this.handler.simpleForm.handleAppData(comment.text, true);
					}, this));
				}

/*
				node = BX.findChild(comment.node, {'tagName' : "DIV", 'className' : "post-comment-text"}, true);
				if (node)
					node.innerHTML += text;
*/
			}
			else if (text)
			{
/*
				var container = BX.create("DIV", {
					attrs : {"className" : ".feed-com-block-cover feed-com-block-cover-error"},
					html : text});
				BX.show(node);
*/
			}
		},
		showNote : function(id, text) {
			/*
			return window.alert('Note: ' + text);
			var node = this._getPlacehoder(), nodes = BX.findChildren(node, {'tagName' : "DIV", 'className' : "feed-add-successfully"}, true), res = null;
			if (!!nodes)
			{
				while ((res = nodes.pop()) && !!res) {
					BX.remove(res);
				}
			}
			node.insertBefore(BX.create('div', {attrs : {"class": "feed-add-successfully"},
				html: '<span class="feed-add-info-text"><span class="feed-add-info-icon"></span>' + text + '</span>'}),
				node.firstChild);
			BX.show(node);*/
		},
		showWait : function() {
			this.handler.hide();
			this.handler.showWait();
		},
		closeWait : function() {
			this.handler.closeWait();
		}
	};
	MPFForm.link = function(ENTITY_XML_ID, form) {
		var id = form['id'];
		repo['form'][id] = (repo['form'][id] || (new MPFForm(id)));
		repo['form'][id].linkEntity(ENTITY_XML_ID, form);
	};

	window.mobileShowActions = function(ENTITY_XML_ID, ID, e) {
		e = e || window.event;

		var isKeyboardShown = (window.app.enableInVersion(14) && window.platform == "ios")
								? window.BXMobileAppContext.isKeyboardShown()
								: inner.keyBoardIsShown;


		if(isKeyboardShown)
		{
			return true;
		}

		if (
			e
			&& e.target
			&& e.target.tagName
			&& (
				e.target.tagName.toUpperCase() == 'A'
				|| (
					e.target.tagName.toUpperCase() == 'IMG'
					&& (BX.type.isNotEmptyString(e.target.getAttribute('data-bx-image'))) // inline or attached image
				)
			)
		)
		{
			return true;
		}

		BX.eventCancelBubble(e);
		BX.PreventDefault(e);

		var node = BX('record-' + makeId(ENTITY_XML_ID, ID)),
			menu = [], action;

		if (node.getAttribute("bx-mpl-reply-show") == "Y")
			menu.push({
				title: BX.message('BLOG_C_REPLY'),
				callback: function() {
					repo["list"][ENTITY_XML_ID].reply(BX('record-' + makeId(ENTITY_XML_ID, ID) + '-reply-action'));
				}
			});
		var like;
		if ((node.getAttribute("bx-mpl-vote-id") != "#VOTE_ID#") && window["RatingLikeComments"] &&
			(like = window.RatingLikeComments.getById(node.getAttribute('bx-mpl-vote-id'))) && like)
		{
			like["__delegatedVoteFunc"] = (like["__delegatedVoteFunc"] || BX.delegate(like.vote, like));
			menu.push({title: (like.voted ? BX.message("BPC_MES_VOTE2") : BX.message("BPC_MES_VOTE1")),
				callback: like["__delegatedVoteFunc"]});
			menu.push({ title: BX.message('BPC_MES_VOTE'),
				callback: function() { window.RatingLikeComments.List(node.getAttribute('bx-mpl-vote-id'));}});
		}

		if (node.getAttribute("bx-mpl-edit-show") == "Y")
			menu.push({
				title: BX.message('BPC_MES_EDIT'),
				callback: function() { repo["list"][ENTITY_XML_ID].act(node.getAttribute('bx-mpl-edit-url'), ID, 'EDIT'); }});
		if (node.getAttribute("bx-mpl-moderate-show") == "Y")
		{
			var hidden = node.getAttribute('bx-mpl-moderate-approved') == 'hidden';
			menu.push({
				title: (hidden ? BX.message("BPC_MES_SHOW") : BX.message("BPC_MES_HIDE")),
				callback: function() {
					repo["list"][ENTITY_XML_ID].act(node.getAttribute('bx-mpl-moderate-url').
						replace("#action#", (hidden ? "show" : "hide")).
						replace("#ACTION#", (hidden ? "SHOW" : "HIDE")),
						ID, 'MODERATE');
				}
			});
		}
		if (node.getAttribute("bx-mpl-delete-show") == "Y")
			menu.push({
				title: BX.message('BPC_MES_DELETE'),
				callback: function() { repo["list"][ENTITY_XML_ID].act(node.getAttribute('bx-mpl-delete-url'), ID, 'DELETE'); }});
		if (node.getAttribute("bx-mpl-createtask-show") == "Y")
			menu.push({
				title: BX.message('BPC_MES_CREATETASK'),
				callback: function() {
					if (typeof oMSL != 'undefined')
					{
						oMSL.createTask({
							entityType: 'BLOG_COMMENT',
							entityId: ID
						});
					}
				}});
		if (menu.length > 0)
		{
			action = new window.BXMobileApp.UI.ActionSheet({ buttons: menu }, "commentSheet" );
			action.show();
		}
		return false;
	};
	window.mobileReply = function(ENTITY_XML_ID, e) {
		BX.eventCancelBubble(e);
		BX.PreventDefault(e);
		repo["list"][ENTITY_XML_ID].reply(e.target);
		return false;
	};
	window.mobileExpand = function(node, e) {
		BX.eventCancelBubble(e);
		BX.PreventDefault(e);

		var el2 = (BX(node) ? node.previousSibling : null);
		if (BX(el2))
		{
			var el = el2.parentNode,
				fxStart = 200,
				fxFinish = parseInt(el2.offsetHeight),
				start1 = {height:fxStart},
				finish1 = {height:fxFinish};

			BX.remove(node);

			var time = (fxFinish - fxStart) / (2000 - fxStart);
			time = (time < 0.3 ? 0.3 : (time > 0.8 ? 0.8 : time));

			el.style.maxHeight = start1.height+'px';
			el.style.overflow = 'hidden';

			(new BX["easing"]({
				duration : time*1000,
				start : start1,
				finish : finish1,
				transition : BX.easing.makeEaseOut(BX.easing.transitions.quart),
				step : function(state){
					el.style.maxHeight = state.height + "px";
					el.style.opacity = state.opacity / 100;
				},
				complete : function(){
					el.style.cssText = '';
					el.style.maxHeight = 'none';
					BX.onCustomEvent(window, 'OnUCRecordWasExpanded', [el]);
					BX.LazyLoad.showImages(true);
				}
			})).animate();

		}
		return false;
	};

	var init = function(window) {
		BX.MPL = function(params, staticParams, formParams)
		{
			BX.MPL.superclass.constructor.apply(this, arguments);

			this.template = BX.message("MPL_RECORD_TEMPLATE");
			this.thumb = BX.message("MPL_RECORD_THUMB");
			this.thumbForFile = BX.message("MPL_RECORD_THUMB_FILE");

			BX.removeCustomEvent(window, 'OnUCAfterRecordAdd', this.windowEvents['OnUCAfterRecordAdd']);
			BX.removeCustomEvent(window, 'OnUCFormResponse', this.windowEvents['OnUCFormResponse']);

			this.postCounter = 0;
			this.windowEvents['OnUCFormBeforeSubmit'] = BX.delegate(function(ENTITY_XML_ID, ENTITY_ID, comment, obj, text, attachments) {
				if (this.ENTITY_XML_ID == ENTITY_XML_ID) {
					var id = [ENTITY_XML_ID, (ENTITY_ID > 0 ? ENTITY_ID : 'new_' + this.postCounter++)];
					this.makeThumb(id, comment, text, attachments);
					this.pullNewRecords[ENTITY_XML_ID + '-' + ENTITY_ID] = "busy";
				}
			}, this);
			this.windowEvents['OnUCAfterRecordAdd'] = BX.delegate(function(ENTITY_XML_ID, ENTITY_ID, obj, data, comment) {
				if (this.ENTITY_XML_ID == ENTITY_XML_ID) {
					this.add(comment, data["messageId"], data, true, "simple");
				}
			}, this);
			this.windowEvents['OnUCFormResponse'] = BX.delegate(function(ENTITY_XML_ID, ENTITY_ID, obj, data, comment) {
				if (this.ENTITY_XML_ID == ENTITY_XML_ID)
				{
					this.pullNewRecords[ENTITY_XML_ID + '-0'] = "ready";
					this.pullNewRecords[ENTITY_XML_ID + '-' + ENTITY_ID] = "done";
					this.clearThumb(comment);
				}
			}, this);
			this.windowEvents['onPull-unicomments'] = BX.delegate(function(data) {
				var params = data.params;
				if (
					data.command == "comment_mobile"
					&& params["ENTITY_XML_ID"] == this.ENTITY_XML_ID
					&& (
						((params["USER_ID"] + '') != (BX.message("USER_ID") + ''))
						||
						( params["EXEMPLAR_ID"] && params["EXEMPLAR_ID"] != this.exemplarId )
						||
						(
							typeof params["AUX"] != 'undefined'
							&& BX.util.in_array(params["AUX"], ['createtask', 'fileversion'])
						)
					)
				)
				{
					if (data.command == 'comment_mobile' && params["ID"])
					{
						if (params["COMMENT_EXEMPLAR_ID"])
							repo.commentExemplarId[params["ENTITY_XML_ID"] + '_' + params["COMMENT_EXEMPLAR_ID"]] = true;
						this.pullNewRecord(params);
					}
					else if (data.command === 'answer' &&
						((params["USER_ID"] + '') !== (BX.message("USER_ID") + '')) &&
						(!params["COMMENT_EXEMPLAR_ID"] || repo.commentExemplarId[params["ENTITY_XML_ID"] + '_' + params["COMMENT_EXEMPLAR_ID"]] !== true)
					)
					{
						this.pullNewAuthor(params["USER_ID"], params["NAME"], params["AVATAR"]);
					}
				}
			}, this);

			BX.addCustomEvent(window, 'OnUCFormResponse', this.windowEvents['OnUCFormResponse']);
			BX.addCustomEvent(window, 'OnUCAfterRecordAdd', this.windowEvents['OnUCAfterRecordAdd']);
			BX.addCustomEvent(window, 'OnUCFormBeforeSubmit', this.windowEvents['OnUCFormBeforeSubmit']);
			BXMobileApp.addCustomEvent(window, 'onPull-unicomments', this.windowEvents['onPull-unicomments']);

			if (staticParams['SHOW_POST_FORM'] == "Y")
				MPFForm.link(this.ENTITY_XML_ID, formParams);

			repo["list"][this.ENTITY_XML_ID] = this;
			return this;
		};
		BX.extend(BX.MPL, window["FCList"]);
		BX.MPL.prototype.init = function() {};
		BX.MPL.prototype.url["activity"] = BX.message("SITE_DIR") + 'mobile/?mobile_action=comment_activity';
		BX.MPL.prototype.makeThumb = function(id, message, txt, attachments) {
			var container = (message.node || BX('record-' + id.join('-') + '-cover'));
			if (!container)
			{
				var text = (BX.type.isString(txt) ? txt : "");
				text = BX.util.htmlspecialchars(text).replace(/\n/gi, "<br />");
				text = text.replace(/\001/, '').
					replace(/(\[\/user\])/gi, "\001").
					replace(/\[user=(\d+)\]([^\001]?.+)(\001)/gi, "$2").
					replace(/\001/, "[/user]");

				var html = window.fcParseTemplate(
					{ messageFields : { FULL_ID : id, POST_MESSAGE_TEXT : text, POST_TIMESTAMP : (new Date().getTime() / 1000) } },
					{ DATE_TIME_FORMAT : this.params.DATE_TIME_FORMAT, RIGHTS : this.rights },
					(BX.type.isArray(attachments) && attachments.length > 0 ? this.thumbForFile : this.thumb)), ob;

				ob = BX.processHTML(html, false);
				container = BX.create("DIV", {
					attrs : {id : ("record-" + id.join('-') + '-cover'), "className" : "feed-com-block-cover"},
					style : {opacity : 0, height : 0, overflow: "hidden"},
					html : ob.HTML});
				BX('record-' + id[0] + '-new').appendChild(container);

				var node = container,
					curPos = BX.pos(node),
					size = BX.GetWindowInnerSize(),
					top = (curPos.top - size.innerHeight);

				if (BX.GetWindowScrollPos()["scrollTop"] < top)
					window.scrollTo(0, top);

				var scroll = BX.GetWindowScrollPos();

				(new BX["easing"]({
					duration : 500,
					start : { opacity : 0, height : 0},
					finish : { opacity: 100, height : node.scrollHeight},
					transition : BX.easing.makeEaseOut(BX.easing.transitions.quart),
					step : function(state){
						node.style.height = state.height + "px";
						node.style.opacity = state.opacity / 100;
						if ((scroll.scrollTop + size.innerHeight) < (curPos.top + state.height))
						{
							window.scrollTo(0, (top + state.height));
						}
					},

					complete : function(){
						if (node.style.display !== 'none')
							node.style.cssText = '';
					}
				})).animate();

				var cnt = 0,
				func = function()
				{
					cnt++;
					if (cnt < 100)
					{
						var node = BX("record-" + id.join('-') + '-cover');
						if (node && node.childNodes.length > 0)
							BX.ajax.processScripts(ob.SCRIPT);
						else
							BX.defer(func, this)();
					}
				};
				BX.defer(func, this)();
			}
			BX.addClass(container, "feed-com-block-cover-wait");
			message.node = container;
			return container;
		};
		BX.MPL.prototype.clearThumb = function(message) {
			if (message && BX(message.node))
			{
				BX.removeClass(message.node, "feed-com-block-cover-wait");
			}
		};
		BX.MPL.prototype.add = function(comment, newId, data) {
			if (BX.type.isArray(comment))
			{
				BX.MPL.superclass.add.apply(this, arguments);
			}
			else if (BX(comment["node"]))
			{
				comment["node"].setAttribute("id", 'record-' + newId.join('-') + '-cover');
				BX.MPL.superclass.add.apply(this, [newId, data, true, "simple"]);
			}
			else
			{
				BX.MPL.superclass.add.apply(this, [newId, data]);
			}
			if (window["BitrixMobile"] && window["BitrixMobile"]["LazyLoad"])
				setTimeout(function() { window.BitrixMobile.LazyLoad.showImages(); }, 500);
		};
		BX.MPL.prototype.send = function() {
			if (BX(this.nav))
				BX.addClass(this.nav.parentNode, "post-comments-button-waiter");
			BX.MPL.superclass.send.apply(this, arguments);
		};
		BX.MPL.prototype.build = function() {
			if (BX(this.nav))
				BX.removeClass(this.nav.parentNode, "post-comments-button-waiter");
			BX.MPL.superclass.build.apply(this, arguments);
		};
		BX.MPL.prototype.complete = function() {
			if (BX(this.nav))
				BX.removeClass(this.nav.parentNode, "post-comments-button-waiter");
			BX.MPL.superclass.complete.apply(this, arguments);
		};
		BX.MPL.prototype.showWait = function(id) {
			var container = BX('record-' + this.ENTITY_XML_ID + '-' + id + '-cover');
			if (id > 0 && container)
				BX.addClass(container, "feed-com-block-cover-wait");
		};
		BX.MPL.prototype.closeWait = function(id) {
			var container = BX('record-' + this.ENTITY_XML_ID + '-' + id + '-cover');
			if (id > 0 && container)
				BX.removeClass(container, "feed-com-block-cover-wait");
		};
		BX.MPL.createInstance = function(params, staticParams, formParams) {
			return (new BX.MPL(params, staticParams, formParams));
		};

		BX.MPL.getInstance = function(entity_xml_id) {
			return repo['list'][entity_xml_id];
		};

		BX.addCustomEvent(window, "OnUCHasBeenDestroyed", function(ENTITY_XML_ID) {
			delete repo["list"][ENTITY_XML_ID];
		});
		BX.onCustomEvent("main.post.list/mobile", ["script.js"]);
		BX.removeCustomEvent("main.post.list/default", function(){ init(window); });
	};
	BX.addCustomEvent("main.post.list/default", function(){ init(window); });
	if (window["FCList"])
		init(window);
})();