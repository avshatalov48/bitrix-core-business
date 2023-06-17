;(function() {

	if (!!window.UCForm)
		return;

	var repo = {};
	window.UCForm = function(formId)
	{
		this.formId = formId;
		this.form = BX(this.formId);
		this.entities = new Map();
		this.xmls = new Map();

		if (formId === "")
		{
			// we have to wait for FCForm and bind to the first appropriate form
		}
		else
		{
			this.initialize();
		}
		this.startCheckWriting = this.startCheckWriting.bind(this);
		this.id = null;
		this.currentEntity = null;
		this.onSubmitSuccess = this.onSubmitSuccess.bind(this);
		this.onSubmitFailed = this.onSubmitFailed.bind(this);
	};
	window.UCForm.prototype = {
		initialize : function() {
			this.bindWindowEvents();
			this.bindPrivateEvents();

			this.__bindLHEEvents = (function(handler, formId) {
				if (formId === this.formId) {
					this.handler = handler;
					this.bindLHEEvents();
				}
			}).bind(this);
			BX.addCustomEvent(window, "onInitialized", this.__bindLHEEvents);
			if (this.getLHE())
				this.bindLHEEvents();

			BX.onCustomEvent(this.form, "OnUCFormInit", [this]);
		},
		bindWindowEvents : function() {
			this.windowEvents = {
				// Lock the submit button when inserting an image.
				OnImageDataUriHandle : this.showWait.bind(this),
				OnImageDataUriCaughtUploaded : this.closeWait.bind(this),
				OnImageDataUriCaughtFailed : this.closeWait.bind(this)
			};

			for (var ii in this.windowEvents)
			{
				if (this.windowEvents.hasOwnProperty(ii))
				{
					BX.addCustomEvent(window, ii, this.windowEvents[ii]);
				}
			}
		},
		unbindWindowEvents : function() {
			for (var ii in this.windowEvents)
			{
				if (this.windowEvents.hasOwnProperty(ii))
				{
					BX.removeCustomEvent(window, ii, this.windowEvents[ii]);
				}
			}
		},
		bindPrivateEvents : function() {
			this.privateEvents = {
				onQuote : this.onQuote.bind(this),
				onReply : this.onReply.bind(this),
				onEdit : this.onEdit.bind(this)
			};
			for (var ii in this.privateEvents)
			{
				if (this.privateEvents.hasOwnProperty(ii))
				{
					BX.addCustomEvent(this, ii, this.privateEvents[ii]);
				}
			}
		},
		bindLHEEvents : function() {
			if (!this.getLHE())
			{
				return;
			}
			// region change Submit handlers
			this.getLHE().exec(function() {
				BX.removeAllCustomEvents(this.getLHE().oEditor, "OnCtrlEnter");
				BX.addCustomEvent(this.getLHE().oEditor, "OnCtrlEnter", function() {
					this.getLHE().oEditor.SaveContent();
					BX.onCustomEvent(eventNode, "OnButtonClick", ["submit"]);
				}.bind(this));
			}.bind(this));
			//endregion

			var eventNode = this.getLHEEventNode();
			// hide writing area
			BX.addCustomEvent(eventNode, "OnBeforeHideLHE", function(/*show, obj*/) {
				BX.removeClass(document.documentElement, "bx-ios-fix-frame-focus");
				if (top && top["document"])
				{
					BX.removeClass(top["document"]["documentElement"], "bx-ios-fix-frame-focus");
				}
				BX.onCustomEvent(this.form, "OnUCFormBeforeHide", [this, this.currentEntity]);
			}.bind(this));
			BX.addCustomEvent(eventNode, "OnAfterHideLHE", function() {
				if (this.currentEntity !== null)
				{
					var node = this.currentEntity.getPlaceholder(this.currentEntity.messageId);
					if (node)
						BX.hide(node);
				}

				BX.onCustomEvent(this.form, "OnUCFormAfterHide", [this, this.currentEntity]);

				this.stopCheckWriting();
				this.clear();
				BX.onCustomEvent(window, "OnUCFeedChanged", [this.id]);
			}.bind(this));
			BX.addCustomEvent(eventNode, "OnBeforeShowLHE", function() {
				if (BX.browser.IsIOS() && BX.browser.IsMobile())
				{
					BX.addClass(window["document"]["documentElement"], "bx-ios-fix-frame-focus");
					if (top && top["document"])
						BX.addClass(top["document"]["documentElement"], "bx-ios-fix-frame-focus");
				}
			}.bind(this));
			BX.addCustomEvent(eventNode, "OnAfterShowLHE", function() {
				this.startCheckWriting();
				BX.onCustomEvent(window, "OnUCFeedChanged", [this.id]);
			}.bind(this));



			BX.addCustomEvent(eventNode, "OnClickSubmit", this.submit.bind(this));
			BX.addCustomEvent(eventNode, "OnClickCancel", this.cancel.bind(this));

			BX.removeCustomEvent(window, "onInitialized", this.__bindLHEEvents);
			delete this.__bindLHEEvents;
		},
		getLHE : function() {
			if (!this.handler)
			{
				this.handler = LHEPostForm.getHandlerByFormId(this.formId);
			}
			return this.handler;
		},
		getLHEEventNode : function() {
			if (!this.handlerEventNode && this.getLHE())
			{
				this.handlerEventNode = this.getLHE().eventNode;
			}
			return this.handlerEventNode
		},
		/**
		 * @param entity FCList
		 */
		bindEntity : function(entity) {
			if (this.entities.has(entity.getId()))
			{
				BX.onCustomEvent(this.form, "onUnbindEntity", [entity]);
				this.entities.delete(entity.getId());
			}
			this.entities.set(entity.getId(), entity);
			this.xmls.set(entity.getXmlId(), entity);
		},
		onInitEditorFrame: function(callback)
		{
			this.getLHE().exec(function() {
				BX.addCustomEvent(this.getLHE().oEditor, 'OnAfterIframeInit', () => {
					callback();
					BX.removeAllCustomEvents(this.getLHE().oEditor, 'OnAfterIframeInit');
				});
			}.bind(this));
		},
		onQuote : function(entity, author, text, safeEdit, loaded) {
			if (this.isFormOccupied(entity))
			{
				return;
			}

			const quote = () => {
				var origRes = BX.util.htmlspecialchars(text);
				if (!this.getLHE().oEditor.toolbar.controls.Quote)
				{
					BX.DoNothing();
				}
				else if (!author && !text)
				{
					this.getLHE().oEditor.action.Exec("quote");
				}
				else
				{
					text = origRes;
					var haveWrittenText = author && author.gender ?
						BX.message("MPL_HAVE_WRITTEN_"+author.gender) : BX.message("MPL_HAVE_WRITTEN");
					if (this.getLHE().oEditor.GetViewMode() == "wysiwyg") // BB Codes
					{
						text = text.replace(/\n/g, "<br/>");
						if (author)
						{
							if (author.id > 0)
							{
								author = '<span id="' + this.getLHE().oEditor.SetBxTag(false, {tag: "postuser", userId: author.id, userName: author.name}) +
									'" class="bxhtmled-metion">' + author.name.replace(/</gi, "&lt;").replace(/>/gi, "&gt;") + "</span>";
							}
							else
							{
								author = "<span>" + author.name.replace(/</gi, "&lt;").replace(/>/gi, "&gt;") + "</span>";
							}
							author = (author !== "" ? (author + haveWrittenText + "<br/>") : "");

							text = author + text;
						}
					}
					else if(this.getLHE().oEditor.bbCode)
					{
						if (author)
						{
							if (author.id > 0)
							{
								author = "[USER=" + author.id + "]" + author.name + "[/USER]";
							}
							else
							{
								author = author.name;
							}
							author = (author !== "" ? (author + haveWrittenText + "\n") : "");
							text = author + text;
						}
					}

					if (this.getLHE().oEditor.action.actions.quote.setExternalSelectionFromRange)
					{
						// Here we take selected text via editor tools
						// we don't use "res"
						this.getLHE().oEditor.action.actions.quote.setExternalSelectionFromRange();
						var extSel = this.getLHE().oEditor.action.actions.quote.getExternalSelection();

						// removing container containing emoji
						let tmpExtSel = BX.create('DIV', {html: extSel});
						let emojiContainer = tmpExtSel.querySelector('.feed-post-emoji-container')
						if (emojiContainer)
						{
							tmpExtSel.removeChild(emojiContainer);
							extSel = tmpExtSel.innerHTML;
						}

						if (extSel === "" && origRes !== "")
						{
							extSel = origRes;
						}
						extSel = (BX.type.isNotEmptyString(author) ? author : "") + extSel;
						if (BX.type.isNotEmptyString(extSel))
							this.getLHE().oEditor.action.actions.quote.setExternalSelection(extSel);
					}
					else
					{
						// For compatibility with old fileman (< 16.0.1)
						this.getLHE().oEditor.action.actions.quote.setExternalSelection(text);
					}
					this.getLHE().oEditor.action.Exec("quote");
				}
			}

			// if the editor is reinit to a new location after editing.
			const isReinitAction = (
				this.currentEntity
				&& parseInt(this.currentEntity.messageId, 10) // only for edit messageId is not false
			);

			if (this.currentEntity && !isReinitAction)
			{
				this.show(entity);
				quote();
			}
			else
			{
				this.show(entity);
				this.onInitEditorFrame(quote);
			}
		},
		onReply : function(entity, author) {
			if (this.isFormOccupied(entity))
			{
				return;
			}

			if (this.currentEntity)
			{
				this.show(entity);
				this.insertMention(author);
			}
			else
			{
				this.show(entity);
				this.onInitEditorFrame(this.insertMention.bind(this, author));
			}
		},
		onEdit : function(entity, messageId, data, act) {
			if (act === "EDIT")
			{
				this.show(entity, messageId, data['messageBBCode'], data['messageFields']);
				return;
			}
			this.hide(true);
			this.setCurrentEntity(entity, messageId);
			if (data['errorMessage'])
			{
				this.showError(data['errorMessage']);
			}
			else if (data['okMessage'])
			{
				this.showNote(data['okMessage']);
				this.nullCurrentEntity();
			}
		},
		isFormOccupied : function(entity) {
			if (this.currentEntity !== null && this.currentEntity !== entity && this.isFormChanged())
			{
				return !window.confirm(BX.message("MPL_SAFE_EDIT"));
			}
			return false;
		},
		isFormChanged : function() {
			if (
				this.getLHE() &&
				this.getLHE().editorIsLoaded &&
				this.getLHE().oEditor.IsContentChanged()
			)
			{
				return true;
			}
			return false;
		},
		insertMention : function(user) {
			if (user.id > 0)
			{
				this.getLHE().exec(window.BxInsertMention, [{
					item: {entityId: user.id, name: user.name},
					type: 'user',
					formID: this.formId,
					editorId: this.getLHE().oEditorId,
					bNeedComa: true,
					insertHtml: true
				}]);
			}
		},
		startCheckWriting : function() {
			if (!this.getLHE().editorIsLoaded ||
				this._checkWriteTimeout === false)
			{
				return;
			}
			this.__content_length = (this.__content_length > 0 ? this.__content_length : 0);
			var content = this.getLHE().oEditor.GetContent(),
				time = 2000;
			if (content.length >= 4 && this.__content_length !== content.length && this.id)
			{
				BX.onCustomEvent(this.form, "OnUCUserIsWriting", [this.currentEntity, {sent : false}]);
				time = 30000;
			}
			this._checkWriteTimeout = setTimeout(this.startCheckWriting, time);
			this.__content_length = content.length;
		},
		stopCheckWriting : function() {
			clearTimeout(this._checkWriteTimeout);
			this._checkWriteTimeout = false;
			this.__content_length = 0;
		},
		setCurrentEntity : function(entity, messageId) {
			this.currentEntity = entity;
			this.currentEntity.messageId = messageId;
			this.currentEntity.operationId = BX.util.getRandomString(20);

			this.id = [this.currentEntity.getXmlId(), messageId]; // for custom templates
		},
		nullCurrentEntity : function() {
			delete this.currentEntity.messageId;
			this.currentEntity = null;

			this.id = null;
		},
		hide : function(quick) {
			if (this.getLHEEventNode() && this.getLHEEventNode().style.display !== "none")
			{
				BX.onCustomEvent(this.getLHEEventNode(), "OnShowLHE", [(quick === true ? false : "hide")]);
			}
			if (quick)
			{
				document.body.appendChild(this.form);
			}
		},
		clear : function() {
			var res = this.currentEntity ? this.currentEntity.getPlaceholder(this.currentEntity.messageId) : null;
			if (res)
			{
				BX.hide(res);
				this.clearNotification(res, "feed-add-error");
			}
			BX.onCustomEvent(this.form, "OnUCFormClear", [this]);

			var filesForm = BX.findChild(this.form, {"className": "wduf-placeholder-tbody" }, true, false);
			if (filesForm !== null && typeof filesForm != "undefined")
				BX.cleanNode(filesForm, false);
			filesForm = BX.findChild(this.form, {"className": "wduf-selectdialog" }, true, false);
			if (filesForm !== null && typeof filesForm != "undefined")
				BX.hide(filesForm);

			filesForm = BX.findChild(this.form, {"className": "file-placeholder-tbody" }, true, false);
			if (filesForm !== null && typeof filesForm != "undefined")
				BX.cleanNode(filesForm, false);
			this.nullCurrentEntity();
		},
		show : function(entity, messageId, text, data) {
			messageId = parseInt(messageId > 0 ? messageId : 0);
			var placeholderNode = entity.getPlaceholder(messageId);

			if (this.currentEntity === entity && this.currentEntity.messageId === messageId)
			{
				this.getLHE().oEditor.Focus();
				setTimeout(function() {
					if (!this.isElementCompletelyVisibleOnScreen(placeholderNode))
					{
						placeholderNode.scrollIntoView({
							behavior: 'smooth',
							block: 'end',
							inline: 'nearest',
						});
					}
				}.bind(this), 100);
				return true;
			}

			if (
				this.getLHEEventNode()
				&& this.getLHEEventNode().style.display !== "none"
				&& BX.Dom.getPosition(placeholderNode).y > BX.Dom.getPosition(this.getLHEEventNode()).y
			)
			{
				window.scrollTo(window.scrollX, window.scrollY - this.getLHEEventNode().offsetHeight + 10);
			}

			this.hide(true);

			this.setCurrentEntity(entity, messageId);

			BX.removeClass(placeholderNode, "feed-com-add-box-no-form");
			BX.removeClass(placeholderNode, "feed-com-add-box-header");
			placeholderNode.appendChild(this.form);
			BX.onCustomEvent(this.form, "OnUCFormBeforeShow", [this, text, data]);
			BX.show(placeholderNode);
			BX.onCustomEvent(this.getLHEEventNode(), "OnShowLHE", ["show", null, this.id]);
			BX.onCustomEvent(this.form, "OnUCFormAfterShow", [this, text, data]);
			return true;
		},
		isElementCompletelyVisibleOnScreen: function(element)
		{
			var coords = BX.LazyLoad.getElementCoords(element);
			var windowTop = window.pageYOffset || document.documentElement.scrollTop;
			var windowBottom = windowTop + document.documentElement.clientHeight;

			coords.bottom = coords.top + element.offsetHeight;

			return (
				coords.top > windowTop
				&& coords.top < windowBottom
				&& coords.bottom < windowBottom
				&& coords.bottom > windowTop
			);
		},
		onSubmitSuccess : function(data) {
			this.closeWait();
			var true_data = data, ENTITY_XML_ID = this.id[0];
			BX.onCustomEvent(this.form, "OnUCFormResponse", [this, data]);
			if (!!this.OnUCFormResponseData)
				data = this.OnUCFormResponseData;
			if (!!data)
			{
				if (data["errorMessage"])
				{
					this.showError(data["errorMessage"]);
				}
				else if (data["status"] == "error")
				{
					this.showError((BX.type.isNotEmptyString(data["message"]) ? data["message"] : ""));
				}
				else
				{
					BX.onCustomEvent(this.form, "OnUCAfterRecordAdd", [this.id[0], data, true_data]);
					this.hide(true);
				}
			}
			this.busy = false;
			BX.onCustomEvent(window, "OnUCFormResponse", [ENTITY_XML_ID, data["messageId"], this, data]);
		},
		onSubmitFailed : function(data) {
			this.closeWait();
			if (BX.type.isPlainObject(data))
			{
				var message = "Unknown error.";
				if (
					BX.type.isPlainObject(data["data"]) &&
					BX.type.isPlainObject(data["data"]["ajaxRejectData"]) &&
					BX.type.isNotEmptyString(data["data"]["ajaxRejectData"]["message"])
				)
				{
					message = data["data"]["ajaxRejectData"]["message"];
				}
				else if (BX.type.isArray(data["errors"]))
				{
					message = data["errors"].map(function(error) { return error.message; }).join('<br \>');
				}
				this.showError(message);
			}

			this.busy = false;
			BX.onCustomEvent(window, "OnUCFormResponse", [this.id[0], this.id[1], this, []]);
		},
		submit : function() {
			if (this.busy === true)
			{
				return "busy";
			}


			var text = (this.getLHE().editorIsLoaded ? this.getLHE().oEditor.GetContent() : '');

			if (!text)
			{
				this.showError(BX.message("JERROR_NO_MESSAGE"));
				return false;
			}
			this.showWait();
			this.busy = true;

			var post_data = {};
			convertFormToArray(this.form, post_data);
			post_data["REVIEW_TEXT"] = text;
			post_data["NOREDIRECT"] = "Y";
			post_data["MODE"] = "RECORD";
			post_data["AJAX_POST"] = "Y";
			post_data["id"] = this.id;
			post_data["SITE_ID"] = BX.message("SITE_ID");
			post_data["LANGUAGE_ID"] = BX.message("LANGUAGE_ID");
			post_data["ACTION"] = "ADD";

			if (this.currentEntity !== null && this.currentEntity.messageId > 0)
			{
				post_data["REVIEW_ACTION"] = "EDIT"; //@deprecated
				post_data["FILTER"] = {"ID" : this.id[1]};
				post_data["ACTION"] = "EDIT";
				post_data["ID"] = this.id[1];
			}
			BX.onCustomEvent(this.form, "OnUCFormSubmit", [this, post_data]);
			BX.onCustomEvent(window, "OnUCFormSubmit", [this.id[0], this.id[1], this, post_data]);

			if (this.currentEntity !== null && this.currentEntity.ajax["processComment"] === true)
			{
				BX.ajax.runComponentAction(this.currentEntity.ajax.componentName, "processComment", {
					mode: 'class',
					data: post_data,
					signedParameters: this.currentEntity.ajax.params,
				}).then(this.onSubmitSuccess, this.onSubmitFailed);
			}
			else
			{
				var actionUrl = this.form.action;
				actionUrl = BX.util.remove_url_param(actionUrl, [ "b24statAction" ]);
				actionUrl = BX.util.add_url_param(actionUrl, { b24statAction: (this.id[1] > 0 ? "editComment" : "addComment") });
				this.form.action = actionUrl;
				BX.ajax({
					method: "POST",
					url: this.form.action,
					data: post_data,
					dataType: "json",
					onsuccess: this.onSubmitSuccess,
					onfailure: this.onSubmitFailed
				});
			}
			return false;
		},
		cancel : function() {},
		clearNotification : function(node, className) {
			var nodes = BX.findChildren(node, {tagName : "DIV", className : className}, true);
			if (nodes)
			{
				var res = nodes.pop();
				do {
					BX.remove(res);
				} while ((res = nodes.pop()) && !!res);
			}
		},
		showError : function(text) {
			if (!text || this.currentEntity === null)
				return;

			var node = this.currentEntity.getPlaceholder(this.currentEntity.messageId);

			this.clearNotification(node, "feed-add-error");
			BX.addClass(node, (!node.firstChild ? "feed-com-add-box-no-form" : "feed-com-add-box-header"));

			node.insertBefore(BX.create(
				"div", {
					attrs : {
						class: "feed-add-error"
					},
					html: '<span class="feed-add-info-text"><span class="feed-add-info-icon"></span>' + '<b>' + BX.message("FC_ERROR") + '</b><br />' + text + '</span>'
				}),
				node.firstChild);

			BX.show(node);
		},
		showNote : function(text) {
			if (!text || this.currentEntity === null)
				return;

			var node = this.currentEntity.getPlaceholder(this.currentEntity.messageId);
			this.clearNotification(node, "feed-add-error");
			this.clearNotification(node, "feed-add-successfully");
			BX.addClass(node, (!node.firstChild ? "feed-com-add-box-no-form" : "feed-com-add-box-header"));

			node.insertBefore(BX.create("div", {attrs : {"class": "feed-add-successfully"},
				html: '<span class="feed-add-info-text"><span class="feed-add-info-icon"></span>' + text + "</span>"}),
				node.firstChild);
			BX.addClass(node, "comment-deleted");
			BX.show(node);
		},
		showWait : function() {
			var el = BX("lhe_button_submit_" + this.form.id);
			this.busy = true;
			if (!!el)
			{
				BX.addClass(el, "ui-btn-clock");
				BX.defer(function(){el.disabled = true})();
			}
		},
		closeWait : function() {
			var el = BX("lhe_button_submit_" + this.form.id);
			this.busy = false;
			if (!!el )
			{
				el.disabled = false ;
				BX.removeClass(el, "ui-btn-clock");
			}
		},
	};
	window.UCForm.bindFormToEntity =  function(formId, entity) {
		var form = repo[formId] || new UCForm(formId);
		form.bindEntity(entity);
		repo[formId] = form;
		return form;
	};
	window.convertFormToArray = function(form, data)
	{
		data = (!!data ? data : []);
		if(!!form){
			var
				i,
				_data = [],
				n = form.elements.length;

			for(i=0; i<n; i++)
			{
				var el = form.elements[i];
				if (el.disabled)
					continue;
				switch(el.type.toLowerCase())
				{
					case "text":
					case "textarea":
					case "password":
					case "hidden":
					case "select-one":
						_data.push({name: el.name, value: el.value});
						break;
					case "radio":
					case "checkbox":
						if(el.checked)
							_data.push({name: el.name, value: el.value});
						break;
					case "select-multiple":
						for (var j = 0; j < el.options.length; j++) {
							if (el.options[j].selected)
								_data.push({name : el.name, value : el.options[j].value});
						}
						break;
					default:
						break;
				}
			}

			var current = data;
			i = 0;

			while(i < _data.length)
			{
				var p = _data[i].name.indexOf("[");
				if (p == -1) {
					current[_data[i].name] = _data[i].value;
					current = data;
					i++;
				}
				else
				{
					var name = _data[i].name.substring(0, p);
					var rest = _data[i].name.substring(p+1);
					if(!current[name])
						current[name] = [];

					var pp = rest.indexOf("]");
					if(pp == -1)
					{
						current = data;
						i++;
					}
					else if(pp === 0)
					{
						//No index specified - so take the next integer
						current = current[name];
						_data[i].name = "" + current.length;
					}
					else
					{
						//Now index name becomes and name and we go deeper into the array
						current = current[name];
						_data[i].name = rest.substring(0, pp) + rest.substring(pp+1);
					}
				}
			}
		}
		return data;
	};

	window["fRefreshCaptcha"] = function(form)
	{
		var captchaIMAGE = null,
			captchaHIDDEN = BX.findChild(form, {attr : {"name": "captcha_code"}}, true),
			captchaINPUT = BX.findChild(form, {attr: {"name":"captcha_word"}}, true),
			captchaDIV = BX.findChild(form, {"className":"comments-reply-field-captcha-image"}, true);
		if (captchaDIV)
			captchaIMAGE = BX.findChild(captchaDIV, {"tag":"img"});
		if (captchaHIDDEN && captchaINPUT && captchaIMAGE)
		{
			captchaINPUT.value = "";
			BX.ajax.getCaptcha(function(result) {
				captchaHIDDEN.value = result["captcha_sid"];
				captchaIMAGE.src = "/bitrix/tools/captcha.php?captcha_code="+result["captcha_sid"];
			});
		}
	};
})();
