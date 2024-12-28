;(function() {
	if (!window.BX || window.BX.MPLForm || !window.app)
	{
		return;
	}

	var BX = window.BX;
//region restore text & keyboard appearance
	var repo = {
		entityId: 0,
		text: '',
		form: {},
		list: {},
		comments: {},
	};
	function setText(text) {
		repo.text = (BX.type.isNotEmptyString(text) ? text : "");
		if (BX.localStorage && repo.entityId)
		{
			var res = BX.localStorage.get('main.post.list/text');
			res = (res || {});
			if (BX.type.isNotEmptyString(repo.text))
			{
				res[repo.entityId] = repo.text;
			}
			else
			{
				delete res[repo.entityId];
			}
			BX.localStorage.set('main.post.list/text', res);
		}
	};
	function getText(entityId) {
		var text = '';
		if (BX.localStorage && entityId)
		{
			var res = BX.localStorage.get('main.post.list/text');
			if (res)
			{
				text = (res[entityId] || '');
				delete res[entityId];
				BX.localStorage.set('main.post.list/text', res);
			}
		}
		return text;
	};
	BX.addCustomEvent(window, 'OnUCFormSubmit', function() { setText(''); });
	BX.addCustomEvent("main.post.form/text", function(text) {
		text = BX.type.isArray(text) ? text[0] : text;
		setText(text);
	});
	var inner = { keyBoardIsShown : false, mention : {}};
	window.app.exec("enableCaptureKeyboard", true);
	BX.addCustomEvent("onKeyboardWillShow", function() { inner.keyBoardIsShown = true; });
	BX.addCustomEvent("onKeyboardDidHide", function() { inner.keyBoardIsShown = false; });
//endregion
//region comment
	function appendToForm(fd, key, val) {
		if (BX.type.isPlainObject(val))
		{
			for (var ii in val)
			{
				if (val.hasOwnProperty(ii))
				{
					appendToForm(fd, key + '[' + ii + ']', val[ii]);
				}
			}
		}
		else if (BX.type.isArray(val))
		{
			for (var ij = 0; ij < val.length; ij++)
			{
				appendToForm(fd, key + '[]', val[ij]);
			}
		}
		else
		{
			fd.append(key, (!!val ? val : ""));
		}
	};
	var commentObj = function(id, text, attachments) {
		this.id = id;
		this.text = (text || "");
		this.attachments = (attachments || []);
		this.node = null;
		this.mentions = {};
	};
	commentObj.prototype = {
		getText : function() {
			return this.text;
		}
	};
	/*
		@return commentObj
	 */
	commentObj.getInstance = function(id, text, attachments) {
		var res;
		if (BX.type.isArray(id) && repo["comments"][id.join("-")])
		{
			res = repo["comments"][id.join("-")];
		}
		else if (BX.type.isArray(id))
		{
			res = new commentObj(id, text, attachments);
			res.savedInRepoId = id.join("-");
			repo["comments"][id.join("-")] = res;
		}
		else if (BX.type.isObject(id) && BX.type.isNotEmptyString(id["savedInRepoId"]) && repo["comments"][id["savedInRepoId"]])
		{
			res = id;
		}
		return res;
	};
	commentObj.removeInstance = function(comment) {
		if (comment && comment["savedInRepoId"])
		{
			delete repo["comments"][comment["savedInRepoId"]];
		}
	};
//endregion
//region From like object
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
				OnUCReply : function(entityId, authorId, authorName) {
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
				}.bind(this),
				OnUCAfterRecordEdit : BX.delegate(function(entityId, id, data, act)
				{
					if (this.entitiesId[entityId]) {
						if (act === "EDIT")
						{
							this.show([entityId, id], data['messageBBCode'], data['messageFields']);
						}
						else if (act === "MODERATE")
						{
							BX.onCustomEvent(window, 'OnUCAfterRecordAdd', [
								data.messageId[0],
								data,
								{
									node: BX('record-' + data.messageId[0] + '-' + data.messageId[1])
								}
							]);
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
				}, this),
				OnUploadQueueError: function(params)
				{
					if (params.analyticsData)
					{
						BX.Runtime.loadExtension('ui.analytics')
							.then(({ sendData }) => {
								sendData({ ...params.analyticsData, status: 'error' });
							});
					}

					if (!repo.list[params.entityId])
					{
						return;
					}

					var container = repo.list[params.entityId].getCommentNode(document.getElementById(params.commentData.commentNodeId).getAttribute('bx-mpl-entity-id'));
					if (container)
					{
						this.showError({
							node: container,
							attachments: [
								{
									fieldValue: 'do not bind click',
								},
							],
						}, params.errorText);
					}
				}.bind(this),
			};

			BX.addCustomEvent(window, 'OnUCReply', this.windowEvents.OnUCReply);
			BX.addCustomEvent(window, 'OnUCAfterRecordEdit', this.windowEvents.OnUCAfterRecordEdit);
			BX.addCustomEvent(window, 'OnUploadQueueError', this.windowEvents.OnUploadQueueError);
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

				var f = function(str){
					this.comment = this.reinitComment({id : [id, 0], text : str});
					this.comment.text = str;
					this.handler.init(this.comment);
				}.bind(this);

				f(getText(id));
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
		initComment(id, text, data) {
			var comment = commentObj.getInstance(id, text, data);
			if (comment["bound"] !== "Y")
			{
				BX.addCustomEvent(comment, "onCancel", this.submitClear.bind(this)); // Release comment
				BX.addCustomEvent(comment, "onStart", this.submitStart.bind(this)); // Draw comment
				BX.addCustomEvent(comment, "onSubmit", this.submit.bind(this)); // Submit form
				BX.addCustomEvent(comment, "onError", function(c, text) {
					this.showError(comment, text);
					this.submitClear(comment);
				}.bind(this)); // Error form
				comment["bound"] = "Y";
			}
			return comment;
		},
		show(id, text, data) {
			this.comment = this.initComment(id, text, data);
			this.jsCommentId = BX.util.getRandomString(20);
			BX.onCustomEvent(this.handler, 'OnUCFormBeforeShow', [this, text, data]);
			repo.entityId = id[0];
			this.handler.show(this.comment, (!!data));
			BX.onCustomEvent(this.handler, 'OnUCFormAfterShow', [this, text, data]);
			return true;
		},
		submitClear(comment) {
			commentObj.removeInstance(comment);
			this.jsCommentId = BX.util.getRandomString(20);
			if (this.comment == comment)
			{
				this.comment = this.initComment([comment.id[0], 0], "", []);
				repo.entityId = comment.id[0];
				this.handler.init(this.comment, { clear: true });
			}
		},
		submitStart(comment, text, attachments) {
			BX.onCustomEvent(window, 'OnUCFormBeforeSubmit', [comment.id[0], comment.id[1], comment, this, text, attachments]); // Preview comment
		},
		submit(comment, analyticsData = null) {
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

			if (comment.id[1] > 0)
			{
				post_data["REVIEW_ACTION"] = "EDIT"; // @deprecated
				post_data["FILTER"] = {ID : comment.id[1]};
				post_data["ACTION"] = "EDIT";
				post_data["ID"] = comment.id[1]; // comment to edit
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

			var actionUrl = entityHdl.url;
			actionUrl = BX.util.add_url_param(actionUrl, {
				b24statAction: (comment.id[1] > 0 ? 'editComment' : 'addComment'),
				b24statContext: 'mobile',
			});

			let status = null;
			const sendAnalytics = (data) => {
				if (BX.UI.Analytics)
				{
					BX.UI.Analytics.sendData(data);
				}
				else
				{
					BX.Runtime.loadExtension('ui.analytics')
						.then(() => {
							BX.UI.Analytics.sendData(data);
						});
				}
			};

			post.Wrap({
				method: 'POST',
				url: actionUrl,
				data: {},
				formData: fd,
				type: 'json',
				processData: true,
				start: false,
				preparePost: false,
				callback: BX.proxy(function(data) {
					BX.onCustomEvent(window, 'OnUCFormResponse', [comment.id[0], comment.id[1], this, data, comment]);
					if (data.errorMessage)
					{
						this.showError(comment, data.errorMessage);
						status = 'error';
					}
					else
					{
						if (data.warningCode && data.warningCode === 'COMMENT_DUPLICATED')
						{
							var container = repo.list[data.messageId[0]].getCommentNode(data.messageId[1]);
							if (container)
							{
								this.showError(comment, data.warningMessage);
							}
							else
							{
								BX.onCustomEvent(window, 'OnUCAfterRecordAdd', [comment.id[0], data, comment]);
							}
						}
						else
						{
							BX.onCustomEvent(window, 'OnUCAfterRecordAdd', [comment.id[0], data, comment]);
						}

						status = 'success';
					}

					if (analyticsData)
					{
						sendAnalytics({ ...analyticsData, status });
					}
				}, this),
				callback_failure: BX.delegate(function(data) {
					this.showError(comment, BX.message('INCORRECT_SERVER_RESPONSE_2'));
					BX.onCustomEvent(window, 'OnUCFormResponse', [comment.id[0], comment.id[1], this, data, comment]);

					if (analyticsData)
					{
						sendAnalytics({ ...analyticsData, status: 'error' });
					}
				}, this),
			});
			post.xhr.send(fd);

			this.submitClear(comment);
		},
		showError(comment, text) {
			if (BX.type.isArray(comment))
				comment = this.initComment(comment, '', []);

			if (comment && comment.node)
			{
				comment.node.classList.add('feed-com-block-cover-undelivered');
				var errorTextNode = comment.node.querySelector('.post-comment-error-text');
				if (errorTextNode)
				{
					errorTextNode.innerHTML = text;
				}

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
						comment.node.classList.remove('feed-com-block-cover-undelivered');
						this.handler.comment = comment;
						this.handler.simpleForm.handleAppData(comment.text, true);
					}, this));
				}
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
//endregion
//region Service functions
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
				e.target.tagName.toUpperCase() === 'A'
				|| e.target.tagName.toUpperCase() === 'VIDEO'
				|| (
					e.target.tagName.toUpperCase() === 'IMG'
					&& (BX.type.isNotEmptyString(e.target.getAttribute('data-bx-image'))) // inline or attached image
				)
			)
		)
		{
			return true;
		}

		BX.eventCancelBubble(e);
		e.preventDefault();

		var node = BX(["record", ENTITY_XML_ID, ID].join("-")),
			menu = [], action;

		BX.MPL.addMenuItems(menu, node, ENTITY_XML_ID, ID);

		if (menu.length > 0)
		{
			action = new window.BXMobileApp.UI.ActionSheet({ buttons: menu }, "commentSheet" );
			action.show();
		}
		return false;
	};

	window.mobileReply = function(ENTITY_XML_ID, e) {
		BX.eventCancelBubble(e);
		e.preventDefault();
		repo["list"][ENTITY_XML_ID].reply(e.target);
		return false;
	};
	window.mobileExpand = function(node, e) {
		BX.eventCancelBubble(e);
		e.preventDefault();

		var el2 = (BX(node) ? BX.findChild(node.previousSibling, { className: 'post-comment-text'}, true) : null);
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
	window.mobileIOSVersion = function() {
		if (/iP(hone|od|ad)/.test(navigator.platform)) {
			var v = (navigator.appVersion).match(/OS (\d+)_(\d+)_?(\d+)?/);
			return [parseInt(v[1], 10), parseInt(v[2], 10), parseInt(v[3] || 0, 10)];
		}
		else
		{
			return false;
		}
	};
//endregion
	var init = function() {
		BX.MPL = function(params, staticParams, formParams)
		{
			BX.MPL.superclass.constructor.apply(this, arguments);

			this.thumb = BX.message("MPL_RECORD_THUMB");
			this.scope = "mobile";

			BX.removeCustomEvent(window, 'OnUCFormBeforeShow', this.windowEvents['OnUCFormBeforeShow']);
			BX.removeCustomEvent(window, 'OnUCFormAfterShow', this.windowEvents['OnUCFormAfterShow']);
			BX.removeCustomEvent(window, 'OnUCFormAfterHide', this.windowEvents['OnUCFormAfterHide']);
			BX.removeCustomEvent(window, 'OnUCFormBeforeHide', this.windowEvents['OnUCFormBeforeHide']);

			this.windowEvents['OnUCFormBeforeSubmit'] = function(ENTITY_XML_ID, ENTITY_ID, comment, obj, text, attachments) {
				if (this.ENTITY_XML_ID === ENTITY_XML_ID)
				{
					this.makeThumb([ENTITY_XML_ID, ENTITY_ID > 0 ? ENTITY_ID : BX.util.getRandomString(6)], comment, text, attachments);
				}
			}.bind(this);
			BX.addCustomEvent(window, 'OnUCFormBeforeSubmit', this.windowEvents['OnUCFormBeforeSubmit']);

			BX.removeCustomEvent(window, 'OnUCFormResponse', this.windowEvents['OnUCFormResponse']);
			this.windowEvents['OnUCFormResponse'] = BX.delegate(function(ENTITY_XML_ID, ENTITY_ID, obj, data, comment) {
				if (this.ENTITY_XML_ID === ENTITY_XML_ID)
				{
					this.clearThumb(comment);
				}
			}, this);
			BX.addCustomEvent(window, 'OnUCFormResponse', this.windowEvents['OnUCFormResponse']);

			BX.removeCustomEvent(window, 'OnUCAfterRecordAdd', this.windowEvents['OnUCAfterRecordAdd']);
			this.windowEvents['OnUCAfterRecordAdd'] = function(ENTITY_XML_ID, data, comment) {
				if (this.ENTITY_XML_ID === ENTITY_XML_ID)
				{
					if (comment && BX(comment.node))
					{
						comment["node"].setAttribute("id", 'record-' + data["messageId"].join('-') + '-cover');
					}
					this.add(data["messageId"], data, true, "simple");
				}
			}.bind(this);
			BX.addCustomEvent(window, 'OnUCAfterRecordAdd', this.windowEvents['OnUCAfterRecordAdd']);


			if (staticParams['SHOW_POST_FORM'] == "Y")
			{
				MPFForm.link(this.ENTITY_XML_ID, formParams);
			}

			repo["list"][this.ENTITY_XML_ID] = this;

			if (Array.isArray(window?.UIAvatars))
			{
				window?.UIAvatars.forEach((avatarParams) => {
					BX.MPL.UIAvatar(avatarParams);
				});

				window.UIAvatars = null;
			}

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
					replace(/\[user=(\d+)\]([^\001].+?)(\001)/gi, "$2").
					replace(/\001/, "[/user]");

				var html = window.fcParseTemplate(
						{
							messageFields: {
								FULL_ID: id,
								POST_MESSAGE_TEXT: text,
								POST_TIMESTAMP: (new Date().getTime() / 1000),
								AUTHOR: {
									ID: this.author.AUTHOR_ID,
									TYPE: this.author.AUTHOR_TYPE,
								},
							},
						},
						{ DATE_TIME_FORMAT: this.params.DATE_TIME_FORMAT, RIGHTS: this.rights },
						this.thumb), ob;

				ob = BX.processHTML(html, false);
				container = BX.create('DIV', {
					attrs: {
						id: (`record-${id.join('-')}-cover`),
						className: 'feed-com-block-cover post-comment-active-progress',
						'bx-mpl-xml-id': this.getXmlId(),
						'bx-mpl-entity-id': id[1],
						'bx-mpl-read-status': 'old',
					},
					style: {
						opacity: 0,
						height: 0,
						overflow: 'hidden',
					},
					html: ob.HTML });
				this.node.newComments.appendChild(container);

				var node = container,
					curPos = BX.pos(node),
					top = (curPos.top),
					size = BX.GetWindowInnerSize(),
					iosPatchNeeded = false,
					iosPatchDelta = 0,
					iOSVersion = window.mobileIOSVersion();

				if (
					window.platform == "ios"
					&& BX.type.isArray(iOSVersion)
				)
				{
					iOSVersion = iOSVersion[0];
					iosPatchNeeded = (iOSVersion >= 11 && inner.keyBoardIsShown);
					iosPatchDelta = 260;
				}

				if (
					!iosPatchNeeded
					|| (top > (size.innerHeight - iosPatchDelta)) // out of visible area
				)
				{
					window.scrollTo(0, top - iosPatchDelta);
				}

				BX.MPL.UIAvatar({
					node,
					user: {
						name: this.author.AUTHOR_NAME,
						image: this.author.AUTHOR_AVATAR,
						type: this.author.AUTHOR_TYPE,
					},
				});

				(new BX["easing"]({
					duration : 500,
					start : { opacity : 0, height : 0},
					finish : { opacity: 100, height : node.scrollHeight},
					transition : BX.easing.makeEaseInOut(BX.easing.transitions.quad),
					step : function(state){
						node.style.height = state.height + "px";
						node.style.opacity = state.opacity / 100;

						if (
							!iosPatchNeeded
							|| ((top + state.height) > (size.innerHeight - iosPatchDelta)) // out of visible area
						)
						{
							window.scrollTo(0, (top + state.height - iosPatchDelta));
						}
					},
					complete : function(){
						if (node.style.display !== 'none')
						{
							node.style.cssText = '';
						}
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
			BX.addClass(container, "post-comment-active-progress");
			message.node = container;
			return container;
		};
		BX.MPL.prototype.clearThumb = function(message) {
			if (message && BX(message.node))
			{
				BX.removeClass(message.node, "post-comment-active-progress");
			}
		};
		BX.MPL.prototype.add = function(newId, data) {
			BX.MPL.superclass.add.apply(this, [newId, data]);
			if (window["BitrixMobile"] && window["BitrixMobile"]["LazyLoad"])
				setTimeout(function() { window.BitrixMobile.LazyLoad.showImages(); }, 500);
		};
		BX.MPL.prototype.markCommentAsRead = function(id) {
			if (!this.unreadComments.has(id))
			{
				return;
			}
			var node = this.unreadComments.get(id);
			var node1 = BX.findChild(node, {attrs : {id : ['record', this.getXmlId(), id].join('-')}}, true, false);
			if (node1)
			{
				BX.removeClass(node1, "post-comment-block-new");
				BX.addClass(node1, "post-comment-block-old");
			}
			BX.MPL.superclass.markCommentAsRead.apply(this, [id]);
		};
		BX.MPL.prototype.sendPagenavigation = function() {
			if (BX(this.node.navigation))
			{
				var waiter = this.node.navigationLoader;
				if (waiter)
				{
					BX.adjust(this.node.navigationLoader, {style : {"display" : "flex"}});
				}
			}
			BX.MPL.superclass.sendPagenavigation.apply(this, arguments);
		};
		BX.MPL.prototype.buildPagenavigation = function()
		{
			if (window["BitrixMobile"] && window["BitrixMobile"]["LazyLoad"])
				setTimeout(function() { window.BitrixMobile.LazyLoad.showImages(); }, 1000);
			BX.MPL.superclass.buildPagenavigation.apply(this, arguments);
		};
		BX.MPL.prototype.completePagenavigation = function() {
			BX.MPL.superclass.completePagenavigation.apply(this, arguments);
		};
		BX.MPL.prototype.showWait = function(id) {
			var container = BX('record-' + this.ENTITY_XML_ID + '-' + id + '-cover');
			if (id > 0 && container)
				BX.addClass(container, "post-comment-active-progress");
		};
		BX.MPL.prototype.closeWait = function(id) {
			var container = BX('record-' + this.ENTITY_XML_ID + '-' + id + '-cover');
			if (id > 0 && container)
				BX.removeClass(container, "post-comment-active-progress");
		};

		BX.MPL.prototype.showError = function(id, text) {
			// var form = repo['form'][id];
			// form.showError()
		};

		BX.MPL.createInstance = function(params, staticParams, formParams) {
			return (new BX.MPL(params, staticParams, formParams));
		};

		BX.MPL.getInstance = function(entity_xml_id) {
			return repo['list'][entity_xml_id];
		};

		BX.MPL.addMenuItems = function(menuItems, commentNode, entityXmlId, id) {

			if (
				!BX.type.isArray(menuItems)
				|| !BX.type.isDomNode(commentNode)
			)
			{
				return;
			}

			if (commentNode.getAttribute('bx-mpl-menu-show') === 'N')
			{
				return;
			}

			if (commentNode.getAttribute('bx-mpl-reply-show') === 'Y')
			{
				menuItems.push({
					title: BX.message('BLOG_C_REPLY'),
					callback() {
						repo.list[entityXmlId].reply(BX(['record', entityXmlId, id, 'reply-action'].join('-')));
					},
				});
			}

			var like;

			if (
				(commentNode.getAttribute('bx-mpl-vote-id') !== '#VOTE_ID#')
				&& window.RatingLikeComments
				&& (like = window.RatingLikeComments.getById(commentNode.getAttribute('bx-mpl-vote-id')))
				&& like
			)
			{
				like.__delegatedVoteFunc = (like.__delegatedVoteFunc || BX.delegate(like.vote, like));
				menuItems.push({
					title: (like.voted ? BX.message('BPC_MES_VOTE2') : BX.message('BPC_MES_VOTE1')),
					callback: like.__delegatedVoteFunc,
				});
				menuItems.push({
					title: BX.message('BPC_MES_VOTE'),
					callback() {
						window.RatingLikeComments.List(commentNode.getAttribute('bx-mpl-vote-id'));
					},
				});
			}

			if (commentNode.getAttribute('bx-mpl-edit-show') === 'Y')
			{
				menuItems.push({
					title: BX.message('BPC_MES_EDIT'),
					callback() {
						repo.list[entityXmlId].act(commentNode.getAttribute('bx-mpl-edit-url'), id, 'EDIT');
					},
				});
			}

			if (commentNode.getAttribute('bx-mpl-moderate-show') === 'Y')
			{
				var hidden = commentNode.getAttribute('bx-mpl-moderate-approved') === 'hidden';
				menuItems.push({
					title: (hidden ? BX.message('BPC_MES_SHOW') : BX.message('BPC_MES_HIDE')),
					callback() {
						var moderateUrl = commentNode.getAttribute('bx-mpl-moderate-url')
							.replace('#action#', (hidden ? 'show' : 'hide'))
							.replace('#ACTION#', (hidden ? 'SHOW' : 'HIDE'));

						if (BX.type.isNotEmptyString(moderateUrl))
						{
							moderateUrl = BX.util.add_url_param(moderateUrl, {
								b24statAction: (hidden ? 'showComment' : 'hideComment'),
								b24statContext: 'mobile',
							});
						}

						repo.list[entityXmlId].act(moderateUrl, id, (hidden ? 'SHOW' : 'HIDE'));
					},
				});
			}

			if (commentNode.getAttribute('bx-mpl-delete-show') === 'Y')
			{
				menuItems.push({
					title: BX.message('BPC_MES_DELETE'),
					callback() {
						repo.list[entityXmlId].act(commentNode.getAttribute('bx-mpl-delete-url'), id, 'DELETE');
					},
				});
			}

			var
				commentEntityType = commentNode.getAttribute('bx-mpl-comment-entity-type'),
				postEntityType = commentNode.getAttribute('bx-mpl-post-entity-type');

			if (
				commentNode.getAttribute('bx-mpl-createtask-show') === 'Y'
				&& typeof oMSL !== 'undefined'
			)
			{
				menuItems.push({
					title: BX.message('BPC_MES_CREATETASK'),
					callback: function() {
						oMSL.createTask({
							postEntityType: (BX.type.isNotEmptyString(postEntityType) ? postEntityType : 'BLOG_POST'),
							entityType: (BX.type.isNotEmptyString(commentEntityType) ? commentEntityType : 'BLOG_COMMENT'),
							entityId: id,
						});
					},
				});
			}

			if (
				typeof oMSL !== 'undefined'
				&& BX.type.isFunction(oMSL.copyLink)
			)
			{
				menuItems.push({
					title: BX.message('BPC_MES_COPYLINK'),
					callback() {
						oMSL.copyLink({
							postEntityType: (BX.type.isNotEmptyString(postEntityType) ? postEntityType : 'BLOG_POST'),
							entityType: (BX.type.isNotEmptyString(commentEntityType) ? commentEntityType : 'BLOG_COMMENT'),
							entityId: id,
						});
					},
				});
			}

			if (
				commentNode.getAttribute('bx-mpl-edit-show') === 'Y'
				&& BX.Tasks
				&& BX.Tasks.ResultAction
				&& entityXmlId.indexOf('TASK_') === 0
				&& BX.Tasks.ResultAction.getInstance().canCreateResult(+/\d+/.exec(entityXmlId))
			)
			{
				var taskId = +/\d+/.exec(entityXmlId);
				var result = BX.Tasks.ResultManager.getInstance().getResult(taskId);

				if (
					result
					&& result.context === 'task'
					&& result.canSetAsResult
					&& result.canSetAsResult(id)
				)
				{
					menuItems.push({
						title: BX.message('BPC_MES_RESULT_V2'),
						callback() {
							BX.Tasks.ResultAction.getInstance().createFromComment(id, true);
						},
					});
				}
			}
		};

		BX.MPL.UIAvatar = ({ messageId, node, user = {} }) => {
			const { name, image, type } = user;
			const ui = BX?.UI;
			const avatarEntityMap = {
				base: ui?.AvatarRound,
				extranet: ui?.AvatarRoundExtranet,
				collaber: ui?.AvatarRoundGuest,
			};

			const Avatar = avatarEntityMap[type?.toLowerCase?.()] ?? avatarEntityMap.base;

			if (!Avatar)
			{
				return;
			}

			const selector = messageId > 0 ? `[bx-mpl-comment-id="${messageId}"]` : 'post-comment-block';
			const parentNode = node || document.querySelector(selector);

			if (!parentNode)
			{
				return;
			}

			const avatarSelector = parentNode.querySelector('.ui-post-avatar');
			const hasUIAvatar = parentNode.querySelector('.ui-avatar');

			if (hasUIAvatar || !avatarSelector)
			{
				return;
			}

			if (avatarSelector.nextSibling && avatarSelector.nextSibling.nodeName === 'I')
			{
				avatarSelector.nextSibling.remove();
			}

			const UIAvatar = new Avatar({
				size: 40,
				userName: name,
				userpicPath: image,
			});
			const classList = document.documentElement.classList;
			if (classList.contains('bx-ios') || classList.contains('bx-android'))
			{
				const computedStyle = window.getComputedStyle(document.body);
				const backgroundColor = computedStyle.backgroundColor;
				UIAvatar.setBorderInnerColor(backgroundColor);
			}

			UIAvatar.renderTo(avatarSelector);
		};

		BX.MPL.getMenuItems = function(event) {

			var
				eventData = event.getData(),
				menuItems = eventData.menuItems,
				targetNode = eventData.targetNode,
				isKeyboardShown = (window.app.enableInVersion(14) && window.platform === 'ios')
					? window.BXMobileAppContext.isKeyboardShown()
					: inner.keyBoardIsShown;

			if (isKeyboardShown)
			{
				return;
			}

			if (
				!BX.type.isDomNode(targetNode)
				|| !BX.type.isArray(menuItems)
			)
			{
				return;
			}

			if (
				targetNode.tagName
				&& (
					targetNode.tagName.toUpperCase() === 'A'
					|| (
						targetNode.tagName.toUpperCase() === 'IMG'
						&& (BX.type.isNotEmptyString(targetNode.getAttribute('data-bx-image'))) // inline or attached image
					)
				)
			)
			{
				return;
			}

			var commentNode = (
				targetNode.classList.contains('post-comment-block')
					? targetNode
					: BX.findParent(targetNode, { className: 'post-comment-block' })
			);

			if (!commentNode)
			{
				return;
			}

			var
				entityXmlId = commentNode.getAttribute('bx-mpl-entity-xml-id'),
				id = parseInt(commentNode.getAttribute('bx-mpl-comment-id'), 10);

			if (
				!BX.type.isNotEmptyString(entityXmlId)
				|| id <= 0
			)
			{
				return;
			}

			BX.MPL.addMenuItems(menuItems, commentNode, entityXmlId, id);
		};

		BX.Event.EventEmitter.subscribe(
			'BX.MPL:onGetMenuItems',
			BX.MPL.getMenuItems,
		);

		BX.addCustomEvent(window, 'OnUCHasBeenDestroyed', (ENTITY_XML_ID) => {
			delete repo.list[ENTITY_XML_ID];
		});
		BX.onCustomEvent('main.post.list/mobile', ['script.js']);
		BX.removeCustomEvent('main.post.list/default', init);
	};
	BX.addCustomEvent('main.post.list/default', init);
	if (window.FCList)
	{
		init();
	}

	BXMobileApp.addCustomEvent(window, 'onPull-unicomments', (data) => {
		var params = data.params;
		var command = data.command;

		if (params.AUX && !BX.util.in_array(params.AUX, ['createtask', 'fileversion', 'TASKINFO']) ||
			repo.list[params.ENTITY_XML_ID] <= 0)
		{
			return;
		}

		if (command === 'comment_mobile')
		{
			if (params.NEED_REQUEST === 'Y')
			{
				// TODO this section
			}
			else if (params.ACTION === 'DELETE')
			{
				BX.onCustomEvent(window, 'OnUCommentWasDeleted', [params.ENTITY_XML_ID, [params.ENTITY_XML_ID, params.ID], params]);
				BX.onCustomEvent(window, 'OnUCFeedChanged', [params.ID]);
			}
			else if (params.ACTION === 'HIDE')
			{
				BX.onCustomEvent(window, 'OnUCommentWasHidden', [params.ENTITY_XML_ID, [params.ENTITY_XML_ID, params.ID], params]);
				BX.onCustomEvent(window, 'OnUCFeedChanged', [params.ID]);
			}
			else
			{
				if (params.ACTION === 'REPLY')
					params.NEW = !params.AUTHOR || params.AUTHOR.ID != BX.message("USER_ID") ? 'Y' : 'N';
				BX.onCustomEvent(window, 'OnUCCommentWasPulled', [[params.ENTITY_XML_ID, params.ID], { messageFields: params }, params]);
			}
		}
		else if (command === 'answer' && Number(params.USER_ID) !== Number(BX.message('USER_ID')))
		{
			BX.onCustomEvent(window, 'OnUCUsersAreWriting', [params.ENTITY_XML_ID, params.USER_ID, params.NAME, params.AVATAR]);
		}
	});
})();
