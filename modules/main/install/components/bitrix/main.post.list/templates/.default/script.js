;(function(){
	window["UC"] = (window["UC"] || {});
	if (window["FCList"])
	{
		return;
	}

	var quoteData = null,
		repo = {
			listById : new Map(),
			listByXmlId : new Map()
		};

	window.FCList = function (params, add) {
		this.exemplarId = params["EXEMPLAR_ID"]/* || BX.util.getRandomString(20)*/; // To identify myself
		this.ENTITY_XML_ID = params["ENTITY_XML_ID"]; // like groupId for lists
		this.template = params["template"]; //html message
		this.scope = "web";
		this.node = {
			main : params["mainNode"],
			navigation : params["navigationNode"], // container for pagination,
			navigationLoader : params["navigationNodeLoader"], // container for pagination,
			history : params["nodeForOldMessages"],
			newComments : params["nodeForNewMessages"],
			formHolder: params["nodeFormHolder"],
			writersBlock : BX.findChild(params["nodeFormHolder"], {attrs : { id : ["record", this.getXmlId(), "writers-block"].join("-")}}, true),
			writers : BX.findChild(params["nodeFormHolder"], {attrs : { id : ["record", this.getXmlId(), "writers"].join("-")}}, true)
		};
		this.ajax = params["ajax"] || null;
		this.eventNode = this.node.main; // just object on the page to fulfill event
		this.form = (BX.type.isNotEmptyString(params["FORM_ID"]) ? UCForm.bindFormToEntity(params["FORM_ID"], this) : null);
		this.order = params["order"]; // message sort direction DESC || ASC
		this.mid = parseInt(params["mid"]); // last messageId
		this.operationIds = [];
		this.canCheckVisibleComments = true;

		this.status = "ready";
		this.msg = (this.node.navigation ? this.node.navigation.innerHTML : "");
		this.params = (add || {});
		this.rights = params["rights"];
		this.DATE_TIME_FORMAT = (this.params["DATE_TIME_FORMAT"] || null);
		this.unreadComments = new Map();
		this.comments = new Map();
		this.blankComments = new Map();
		this.blankCommentsForAjax = new Map();

		this.bindEvents = [];
		this.registerNewComment = this.registerNewComment.bind(this);
		this.registerBlankComment = this.registerBlankComment.bind(this);
		this.privateEvents = {
			onShowActions : function(el, id){
				fcShowActions(this.node.main, id, el);
			}.bind(this),
			OnUCCommentIsInDOM : this.registerNewComment, // this event is written in template
			OnUCBlankCommentIsInDOM : this.registerBlankComment, // this event is written in template
			onExpandComment : fcCommentExpand
		};
		this.windowEvents = {
			OnUCCommentWasPulled : function(id, data, params) {
				if (
					this.getXmlId() !== params["ENTITY_XML_ID"]
					|| this.isOwnOperationId(params["OPERATION_ID"])
				)
				{
					return;
				}
				this.setOperationId(params["OPERATION_ID"]);
				this.add(id, data, null, null, { live: true });

				if (this.params["NOTIFY_TAG"] && this.params["NOTIFY_TEXT"])
				{
					window["UC"]["Informer"].check(id, data, this.params["NOTIFY_TAG"], this.params["NOTIFY_TEXT"]);
				}
			}.bind(this),
			OnUCommentWasDeleted : function(ENTITY_XML_ID, id, params) {
				if (
					this.getXmlId() !== params["ENTITY_XML_ID"]
					|| this.isOwnOperationId(params["OPERATION_ID"])
					|| !this.getCommentNode(id[1])
				)
				{
					return;
				}
				this.setOperationId(params["OPERATION_ID"]);
				BX.hide(this.getCommentNode(id[1]));
				this.comments.delete(id[1]);
			}.bind(this),
			OnUCommentWasHidden : function(ENTITY_XML_ID, id, params) {
				if (
					this.getXmlId() !== params["ENTITY_XML_ID"]
					|| this.isOwnOperationId(params["OPERATION_ID"])
					|| !this.getCommentNode(id[1])
				)
				{
					return;
				}
				this.setOperationId(params["OPERATION_ID"]);

				if (
					this.rights["MODERATE"] === "Y"
					|| this.rights["MODERATE"] === "ALL"
					|| Number(params["USER_ID"]) === Number(BX.message("USER_ID"))
				)
				{
					var node = BX.findChild(this.getCommentNode(id[1]), {tagName : "DIV", className : "feed-com-block"});
					if (BX(node))
					{
						BX.addClass(node, "feed-com-block-hidden");
						BX.removeClass(node, "feed-com-block-approved");
					}
				}
				else
				{
					BX.hide(this.getCommentNode(id[1]));
				}
			}.bind(this),
			OnUCUserIsWriting : function(ENTITY_XML_ID, evObj) {
				if (evObj.sent === true)
				{
					return;
				}
				if (this.form !== null && this !== ENTITY_XML_ID)
				{
					return;
				}
				if (this.form === null && ENTITY_XML_ID !== this.ENTITY_XML_ID)
				{
					return;
				}
				evObj.sent = true;
				BX.ajax({
					url: this.url.activity,
					method: "POST",
					data: {
						AJAX_POST : "Y",
						ENTITY_XML_ID : this.ENTITY_XML_ID,
						COMMENT_EXEMPLAR_ID : this.exemplarId,
						MODE : "PUSH&PULL",
						sessid : BX.bitrix_sessid(),
						sign : params["sign"],
						PATH_TO_USER : this.params["PATH_TO_USER"],
						AVATAR_SIZE : this.params["AVATAR_SIZE"],
						NAME_TEMPLATE : this.params["NAME_TEMPLATE"],
						SHOW_LOGIN : this.params["SHOW_LOGIN"]
					}
				});
			}.bind(this),
			OnUCAfterRecordAdd : function(ENTITY_XML_ID, data) { // when the response is here
				if (this.ENTITY_XML_ID === data["messageId"][0])
				{
					this.add(data["messageId"], data, true, "simple");
				}
			}.bind(this),
			OnUCFormSubmit : BX.delegate(function(ENTITY_XML_ID, ENTITY_ID, formObject, data) {
				if (
					!formObject
					|| (
						this.form === null
						&& (ENTITY_XML_ID !== this.getXmlId())
					)
					|| (
						this.form !== null
						&& formObject.currentEntity !== this
					)
				)
				{
					return;
				}
				data["EXEMPLAR_ID"] = this.exemplarId;
				data["OPERATION_ID"] = this.getOperationId();
			}, this),
			OnUCFormResponse : function(ENTITY_XML_ID, ENTITY_ID, formObject) {},
			OnUCFormBeforeShow : function(formObject) {
				var messageId = 0;
				if (this.form === null && formObject.id[0] === this.getXmlId())
				{
					messageId = formObject.id[1];
				}
				else if (this.form !== null && formObject.currentEntity === this)
				{
					messageId = formObject.currentEntity.messageId;
				}
				else
				{
					return;
				}

				if (messageId <= 0)
				{
					BX.addClass(this.node.formHolder, "feed-com-add-box-outer-form-shown");
				}

				if (BX(this.node.writersBlock))
				{
					var node = BX("lhe_buttons_" + formObject.form.id);
					if (!node || node.style.display === "none")
					{
						node = formObject.form;
					}

					if (!this.node.writersBlockPointer)
					{
						this.node.writersBlockPointer = BX.create("DIV", {style : {display: "none"}});
						this.node.writersBlock.parentNode.insertBefore(this.node.writersBlockPointer, this.node.writersBlock);
					}
					node.appendChild(this.node.writersBlock);
				}
			}.bind(this),
			OnUCFormAfterShow : function(formObject) {

				if (formObject.id[0] !== this.getXmlId())
				{
					return;
				}

				var node = BX.findParent(this.node.main, { className: "feed-comments-block"});
				if (node)
				{
					BX.addClass(node, "feed-comments-block-editor-shown");
				}
			}.bind(this),
			OnUCFormBeforeHide : function(formObject) {
				if (
					(
						this.form === null
						&& (
							!formObject.id
							|| formObject.id[0] !== this.getXmlId()
						)
					)
					||
					(
						this.form !== null
						&& formObject.currentEntity !== this
					)
				)
				{
					return;
				}

				var node = BX.findParent(this.node.main, { className: "feed-comments-block"});
				if (node)
				{
					BX.removeClass(node, "feed-comments-block-editor-shown");
				}
			}.bind(this),
			OnUCFormAfterHide : function(formObject) {
				if (
					(
						this.form === null
						&& (
							!formObject.id
							|| formObject.id[0] !== this.getXmlId()
						)
					)
					|| (
						this.form !== null
						&& formObject.currentEntity !== this
					)
				)
				{
					return;
				}

				BX.removeClass(this.node.formHolder, "feed-com-add-box-outer-form-shown");
				//BX.show(this.node.formHolder);
				BX.focus(this.node.formHolder.firstChild);
				if (this.node.writersBlock && this.node.writersBlockPointer)
				{
					this.node.writersBlockPointer.parentNode.insertBefore(this.node.writersBlock, this.node.writersBlockPointer);
				}
			}.bind(this),
			OnUCUsersAreWriting: function (entityId, authorId, authorName, authorAvatar, timeL) {
				if (this.getXmlId() === entityId)
				{
					this.addWriter(authorId, authorName, authorAvatar, timeL);
				}
			}.bind(this),
			OnUCCommentRecalculate: function (entityId) {
				if (this.getXmlId() !== entityId)
				{
					return;
				}

				var ii;
				var nodes = BX.findChild(this.node.main, {attrs : { "bx-mpl-xml-id" : this.ENTITY_XML_ID } }, false, true);

				for (ii = 0; ii < nodes.length; ii++)
				{
					this.recalcMoreButtonComment(nodes[ii].getAttribute("bx-mpl-entity-id"));
				}
			}.bind(this),
			"BX.BXUrlPreview.onImageLoaded": function(params) {

				if (
					!BX.type.isPlainObject(params)
					|| !BX.type.isDomNode(params.imageNode)
				)
				{
					return;
				}

				var commentNode = BX.findParent(params.imageNode, { className: "feed-com-block-cover"});
				if (BX.type.isDomNode(commentNode))
				{
					this.recalcMoreButtonComment(commentNode.getAttribute("bx-mpl-entity-id"));
				}

			}.bind(this)
		};

		if (this.params["NOTIFY_TAG"] && this.params["NOTIFY_TEXT"] && window["UC"]["Informer"])
		{
			window["UC"]["InformerTags"][this.params["NOTIFY_TAG"]] = (window["UC"]["InformerTags"][this.params["NOTIFY_TAG"]] || []);
		}
		else
		{
			this.params["NOTIFY_TAG"] = null;
			this.params["NOTIFY_TEXT"] = null;
		}

		this.initialize();

		this.checkHash();
		this.registerComments();

		BX.onCustomEvent(this.eventNode, "OnUCInitialized", [this.exemplarId]);
		BX.addCustomEvent(this.eventNode, "OnUCInitialized", this.destroy.bind(this));
		this.windowEvents["OnUCInitialized"] = this.checkAndDestroy.bind(this);

		BX.Event.EventEmitter.incrementMaxListeners("OnUCInitialized");
		BX.addCustomEvent(window, "OnUCInitialized", this.windowEvents["OnUCInitialized"]);

		BX.ready((function() {
			setTimeout((function() {
				BX.onCustomEvent(window, "OnUCHasBeenInitialized", [this.ENTITY_XML_ID, this]);
			}).bind(this), 100)
		}).bind(this));
		repo.listById.set(this.exemplarId, this);
		repo.listByXmlId.set(this.getXmlId(), this);
		return this;
	};
	window.FCList.prototype = {
		getId : function() {
			return this.exemplarId;
		},
		getXmlId : function() {
			return this.ENTITY_XML_ID;
		},
		getOperationId : function() {
			var id = BX.util.getRandomString(20);
			this.operationIds.push(id);
			return id;
		},
		setOperationId : function(id) {
			if (BX.type.isNotEmptyString(id))
			{
				this.operationIds.push(id);
			}
		},
		isOwnOperationId : function(id) {
			for (var i = 0; i < this.operationIds.length; i++)
			{
				if (this.operationIds[i] === id)
				{
					return true;
				}
			}
			return false;
		},
		initialize : function() {
			this.checkVisibleComments = this.checkVisibleComments.bind(this);
			this.recalcMoreButtonComment = this.recalcMoreButtonComment.bind(this);
			this.sendCommentAsBlank = this.sendCommentAsBlank.bind(this);
			BX.Event.EventEmitter.incrementMaxListeners(scrSpy, "onRead");
			BX.addCustomEvent(scrSpy, "onRead", this.checkVisibleComments);
			scrSpy.watchNode(this.node.main);

			this.initNavigationEvents();
			this.initPostFormActivity();

			for (var ii = 0; ii < this.bindEvents.length; ii++)
			{
				BX.bind(this.bindEvents[ii][0], this.bindEvents[ii][1], this.bindEvents[ii][2]);
			}
			for (ii in this.privateEvents)
			{
				if (this.privateEvents.hasOwnProperty(ii))
				{
					BX.Event.EventEmitter.incrementMaxListeners(this.eventNode, ii);
					BX.addCustomEvent(this.eventNode, ii, this.privateEvents[ii]);
				}
			}

			if (
				!BX.type.isBoolean(this.params["USE_LIVE"])
				|| this.params["USE_LIVE"]
			)
			{
				for (ii in this.windowEvents)
				{
					if (this.windowEvents.hasOwnProperty(ii))
					{
						BX.Event.EventEmitter.incrementMaxListeners(ii);
						BX.addCustomEvent(window, ii, this.windowEvents[ii]);
					}
				}
			}

			if (BX.DD && !this.node.main.hasAttribute("dropzone"))
			{
				new BX.DD.dropFiles(this.node.main);
			}
		},
		initNavigationEvents : function() {
			if (!BX(this.node.navigation))
			{
				return;
			}

			this.bindEvents.unshift([
				this.node.navigation, "click", (function (e) {
					BX.eventCancelBubble(e);
					e.preventDefault();
					this.getPagenavigation();
					return false;
				}).bind(this)
			]);
		},
		initPostFormActivity : function() {

			this.privateEvents["onAct"] = this.act.bind(this);

			if (this.params['SHOW_POST_FORM'] !== 'Y')
			{
				return
			}

			this.privateEvents["onReply"] = this.reply.bind(this);
			this.privateEvents["onQuote"] = this.quote.bind(this);

			this.hideWriter = this.hideWriter.bind(this);
			this.quoteShow = this.quoteShow.bind(this);

			this.bindEvents.unshift([this.eventNode, "mouseup", this.privateEvents["onQuote"]]);
			//region dnd
			var timerListenEnter = 0;
			var stopListenEnter = function(e) {
				if (e && e.currentTarget.contains(e.relatedTarget))
				{
					return;
				}
				if (timerListenEnter > 0)
				{
					clearTimeout(timerListenEnter);
					timerListenEnter = 0;
				}
			}.bind(this);
			var fireDragEnter = function() {
				stopListenEnter();
				this.reply.apply(this, arguments);
			}.bind(this);
			var startListenEnter = function() {
				if (timerListenEnter <= 0)
				{
					timerListenEnter = setTimeout(fireDragEnter, 200);
				}
			}.bind(this);
			this.bindEvents.unshift([this.node.main, "dragover", startListenEnter]);
			this.bindEvents.unshift([this.node.main, "dragenter", startListenEnter]);
			this.bindEvents.unshift([this.node.main, "dragleave", stopListenEnter]);
			this.bindEvents.unshift([this.node.main, "dragexit", stopListenEnter]);
			this.bindEvents.unshift([this.node.main, "drop", stopListenEnter]);
			//region
		},
		url : {
			activity : '/bitrix/components/bitrix/main.post.list/activity.php'
		},
		destroy : function() {
			BX.removeCustomEvent(scrSpy, "onRead", this.checkVisibleComments);
			var ii, node;
			while ((node = this.bindEvents.pop()) && node)
			{
				BX.unbindAll(node[0]);
				delete node[0];
				delete node[2];
			}
			for (ii in this.privateEvents)
			{
				if (this.privateEvents.hasOwnProperty(ii))
				{
					BX.removeCustomEvent(this.eventNode, ii, this.privateEvents[ii]);
					BX.Event.EventEmitter.decrementMaxListeners(this.eventNode, ii);
					this.privateEvents[ii] = null;
				}
			}
			this.privateEvents = null;
			for (ii in this.windowEvents)
			{
				if (this.windowEvents.hasOwnProperty(ii))
				{
					BX.removeCustomEvent(window, ii, this.windowEvents[ii]);
					this.windowEvents[ii] = null;
					BX.Event.EventEmitter.decrementMaxListeners(ii);
				}
			}
			this.windowEvents = null;
			for (ii in this.node)
			{
				if (this.node.hasOwnProperty(ii))
				{
					this.node[ii] = null;
				}
			}
			this.unreadComments.clear();
			this.comments.clear();
			BX.onCustomEvent(window, "OnUCHasBeenDestroyed", [this.ENTITY_XML_ID, this]);
			repo.listById.delete(this.exemplarId);
			if (repo.listByXmlId.get(this.ENTITY_XML_ID) === this)
			{
				repo.listByXmlId.delete(this.ENTITY_XML_ID);
			}
		},
		checkAndDestroy : function(exemplarId) {
			if (this.exemplarId === exemplarId || !document.body.contains(this.eventNode))
			{
				this.destroy();
			}
		},
		quotePopup : null,
		quoteCheck : function() {
			var text = "";
			var range;
			var author = null;

			if (window.getSelection)
			{
				range = window.getSelection();
				text = range.toString();
			}
			else if (document.selection)
			{
				range = document.selection;
				text = range.createRange().text;
			}

			if (text !== "")
			{
				var endParent = BX.findParent(range.focusNode, {"tagName" : "DIV", "className" : "feed-com-block-cover"}, this.node.main);
				var startParent = BX.findParent(range.anchorNode, {"tagName" : "DIV", "className" : "feed-com-block-cover"}, this.node.main);

				if (endParent !== startParent || BX(endParent) && !endParent.hasAttribute("id"))
				{
					text = "";
				}
				else if (BX(endParent))
				{
					var node = BX(endParent.getAttribute("id").replace(/\-cover$/, "-actions-reply"));
					if (node)
					{
						author = {
							id : parseInt(node.getAttribute("bx-mpl-author-id")),
							name : node.getAttribute("bx-mpl-author-name"),
							gender : node.getAttribute("bx-mpl-author-gender")
						};
					}
				}
			}
			if (text === "")
			{
				if (this.quotePopup)
				{
					this.quotePopup.hide();
				}
				return false;
			}
			return {text : text, author : author};
		},
		quoteShow : function(e, params) {
			params = (params || this.quoteCheck() || {});

			if (!BX.type.isNotEmptyString(params["text"]))
			{
				return;
			}

			quoteData = params;
			if (this.quotePopup == null)
			{
				this.__quoteShowClick = (function() {
					if (this.form)
					{
						BX.onCustomEvent(
							this.form,
							"onQuote",
							[
								this,
								params["author"],
								params["text"]
							]
						);
					}
					else
					{
						BX.onCustomEvent(
							window,
							"OnUCQuote",
							[
								this.ENTITY_XML_ID,
								params["author"],
								params["text"],
								true
							]
						);
					}
				}).bind(this);
				this.__quoteShowHide = (function() {
					quoteData = null;
					BX.removeCustomEvent(this.quotePopup, "onQuote", this.__quoteShowClick);
					BX.removeCustomEvent(this.quotePopup, "onHide", this.__quoteShowHide);
					this.quotePopup = null;
				}).bind(this);
				this.quotePopup = new MPLQuote();
				BX.addCustomEvent(this.quotePopup, "onQuote", this.__quoteShowClick);
				BX.addCustomEvent(this.quotePopup, "onHide", this.__quoteShowHide);
			}
			this.quotePopup.show(e);
		},
		displayPagenavigation : function(status, startHeight) {
			var fxStart;
			var fxFinish = 0;
			var time;
			var el = this.node.history;

			status = (status == "hide" ? "hide" : "show");
			if (status == "hide")
			{
				fxStart = parseInt(this.node.history.offsetHeight);
				time = fxStart / 2000;

				time = (time < 0.3 ? 0.3 : (time > 0.5 ? 0.5 : time));
				el.style.overflow = "hidden";

				(new BX["easing"]({
					duration : time*1000,
					start : {height:fxStart, opacity:100},
					finish : {height:fxFinish, opacity:0},
					transition : BX.easing.makeEaseOut(BX.easing.transitions.quart),
					step : function(state){
						el.style.maxHeight = state.height + "px";
						el.style.opacity = state.opacity / 100;
					},
					complete : BX.proxy(function(){
						el.style.cssText = "";
						el.style.display = "none";
						BX.onCustomEvent(this, "OnUCListWasHidden", [this, [], el]);
					}, this)
				})).animate();
			}
			else
			{
				fxStart = parseInt(startHeight || 20);

				el.style.display = "block";
				el.style.overflow = "hidden";
				el.style.maxHeight = fxStart;

				fxFinish = parseInt(this.node.history.offsetHeight);
				time = (fxFinish - fxStart) / (2000 - fxStart);
				time = (time < 0.3 ? 0.3 : (time > 0.8 ? 0.8 : time));
				(new BX["easing"]({
					duration : time*1000,
					start : {height:fxStart, opacity:(fxStart > 0 ? 100 : 0)},
					finish : {height:fxFinish, opacity:100},
					transition : BX.easing.makeEaseOut(BX.easing.transitions.quart),
					step : function(state){
						el.style.maxHeight = state.height + "px";
						el.style.opacity = state.opacity / 100;
					},
					complete : BX.proxy(function(){
						el.style.cssText = "";
						el.style.maxHeight = "none";
						BX.onCustomEvent(this, "OnUCListWasShown", [this, [], el]);
					}, this)
				})).animate();
			}
		},
		getPagenavigation : function() {
			if (this.status == "done")
			{
				if (this.node.navigation.getAttribute("bx-visibility-status") == "visible") {
					this.displayPagenavigation("hide");
					BX.adjust(this.node.navigation, {attrs : {"bx-visibility-status" : "none"}, html : this.msg});
				} else {
					this.displayPagenavigation("show");
					BX.adjust(this.node.navigation, {attrs : {"bx-visibility-status" : "visible"}, html : BX.message("BLOG_C_HIDE")});
				}
			}
			else if (this.status == "ready")
			{
				this.sendPagenavigation();
			}
			return false;
		},
		sendPagenavigation : function() {
			this.status = "busy";
			BX.addClass(this.node.navigation, "feed-com-all-hover");
			var data = {
					AJAX_POST : "Y",
					ENTITY_XML_ID : this.ENTITY_XML_ID,
					EXEMPLAR_ID : this.exemplarId,
					MODE : "LIST",
					FILTER : (this.order == "ASC" ? {">ID" : this.mid} : {"<ID" : this.mid}),
					sessid : BX.bitrix_sessid() },
				url = BX.util.htmlspecialcharsback(this.node.navigation.getAttribute("href"));
				url = (url.indexOf('#') !== -1 ? url.substr(0, url.indexOf('#')) : url);
			var result = {url : url, data : data};
			BX.onCustomEvent(this, "OnUCListHasToBeEnlarged", [this, result]);
			url = result.url;
			data = result.data;
			data.scope = this.scope;
			var onsuccess = this.buildPagenavigation.bind(this);
			var onfailure = this.completePagenavigation.bind(this);
			if (this.ajax["navigateComment"] === true)
			{
				url = (url.indexOf("?") >= 0 ? url.substr(url.indexOf("?") + 1) : "");
				url.split("&").forEach(function (val) {
					var val1 = val.split("=");
					data[val1[0]] = val1[1];
				});
				BX.ajax.runComponentAction(this.ajax.componentName, "navigateComment", {
					mode: "class",
					data: data,
					signedParameters: this.ajax.params
				}).then(onsuccess, onfailure);
			}
			else
			{
				BX.adjust(this.node.navigationLoader, {style : {"display" : "flex"}});
				BX.ajax({
					url: (url + (url.indexOf('?') !== -1 ? "&" : "?") + BX.ajax.prepareData(data)),
					method: "GET",
					dataType: "json",
					data: "",
					onsuccess: onsuccess,
					onfailure: onfailure
				});
			}
		},
		buildPagenavigation : function(data) {
			if (data["status"] !== "success")
			{
				return this.completePagenavigation();
			}

			this.status = "ready";
			this.wait("hide");
			BX.adjust(this.node.navigationLoader, {style : {"display" : "none"}});
			BX.removeClass(this.node.navigation, "feed-com-all-hover");

			var ob = BX.processHTML(data["messageList"], false);
			var offsetHeight = this.node.history.offsetHeight;
			var container = BX.create("DIV", {html : ob.HTML});
			if (this.order === "ASC" || !this.node.history.firstChild)
			{
				this.node.history.appendChild(container);
			}
			else
			{
				this.node.history.insertBefore(container, this.node.history.firstChild);
			}
			BX.onCustomEvent(window, "OnUCFeedChanged", [[this.ENTITY_XML_ID, this.mid]]);
			this.displayPagenavigation("show", offsetHeight);

			if (BX.type.isNotEmptyString(data["navigation"]))
			{
				var res = BX.create("DIV", {html : data["navigation"]}).firstChild;

				BX.adjust(this.node.navigation, {
					attrs : {
						href : res.getAttribute("href"),
						"bx-mpl-comments-count" : res.getAttribute("bx-mpl-comments-count")
					},
					html : res.innerHTML
				});
			}
			else
			{
				BX.adjust(this.node.navigation, {
					attrs : {
						href : "#",
						"bx-visibility-status" : "visible",
						"bx-mpl-comments-count" : 0
					},
					html : BX.message("BLOG_C_HIDE"),
					events : {
						click : function(e) {
							BX.eventCancelBubble(e);
							e.preventDefault();
							return false;
						}
					}
				});
				this.status = "done";
			}

			var cnt = 0;
			var func = function()
			{
				cnt++;
				if (cnt > 100)
				{
					return;
				}
				if (container.childNodes.length <= 0)
				{
					setTimeout(func, 500);
					return;
				}
				BX.ajax.processScripts(ob.SCRIPT);
				BX.onCustomEvent(this, "OnUCListWasBuilt", [this, data, container]);
			}.bind(this);
			setTimeout(func, 500);
		},
		completePagenavigation : function() {
			this.status = "done";
			BX.removeClass(this.node.navigation, "feed-com-all-hover");
			this.wait("hide");
			BX.adjust(this.node.navigationLoader, {style : {"display" : "none"}});
		},
		getCommentsCount : function() {
			var count = 0;
			if (BX(this.node.navigation))
			{
				count = Number(this.node.navigation.getAttribute("bx-mpl-comments-count"));
			}
			return (this.comments.size + count);
		},
		wait : function(status) {
			status = (status === "show" ? "show" : "hide");
			return status;
		},
		quote : function(e, params) {
			if (e.hasOwnProperty("UCDone"))
			{
				return;
			}
			e["UCDone"] = true;
			setTimeout(this.quoteShow, 50, e, params);
		},
		reply : function(node) {
			var author = {
				id: undefined,
				name: undefined,
			};
			if (BX.type.isElementNode(node))
			{
				author.id = node.getAttribute("bx-mpl-author-id");
				author.name = node.getAttribute("bx-mpl-author-name");
			}
			else if (BX.type.isPlainObject(node))
			{
				author.id = node.id;
				author.name = node.name;
			}
			if (this.form)
			{
				BX.onCustomEvent(this.form, "onReply", [this, author]);
			}
			else
			{
				var eventResult = {caught : false};
				BX.onCustomEvent(window, "OnUCReply", [this.ENTITY_XML_ID, author.id, author.name, true, eventResult]);
			}
		},
		getPlaceholder : function(messageId) {
			if (
				!this.node["placeholderFor" + messageId]
				|| !document.body.contains(this.node["placeholderFor" + messageId])
			)
			{
				this.node["placeholderFor" + messageId] = BX.findChild(
					this.node.main,
					{attrs : { id : ["record", this.getXmlId(), messageId, "placeholder"].join("-")}},
					true
				);
			}
			return this.node["placeholderFor" + messageId];
		},
		addWriter : function(userId, name, avatar, time) {
			if (!this.node.writersBlock || !this.node.writers)
			{
				return;
			}
			userId = (userId > 0 ? userId : 0);
			if (userId <= 0)
			{
				return;
			}

			var id = ["writer", this.exemplarId, userId].join("-");
			var node = BX(id);
			var t = setTimeout(this.hideWriter, (time > 0 ? time : 40500), userId);

			if (node)
			{
				clearTimeout(node.getAttribute("bx-check-timeout"));
			}
			else
			{
				node = BX.create("DIV", {
					attrs : {
						id : id,
						className : "feed-com-avatar ui-icon ui-icon-common-user ",
						title : name
					},
					children: [
						(
							avatar && avatar.length > 0
								? BX.create("I", {
									style: {
										background: "url('" + avatar + "')",
										backgroundSize: 'cover'
									}
								})
								: null
						)
					]
				});

				this.node.writers.appendChild(node);
			}
			node.setAttribute("bx-check-timeout", (t + ""));

			BX.show(this.node.writersBlock);

			if (this.objAnswering && this.objAnswering.name !== "show")
			{
				this.objAnswering.stop();
			}

			if (!this.objAnswering || this.objAnswering.name !== "show")
			{
				this.node.writersBlock.style.display = "inline-block";
				this.objAnswering = (new BX["easing"]({
					duration : 500,
					start : { opacity : 0},
					finish : { opacity: 100},
					transition : BX.easing.makeEaseOut(BX.easing.transitions.quart),
					step : function(state) {
						this.node.writersBlock.style.opacity = state.opacity / 100;
					}.bind(this)
				}));
				this.objAnswering.name = "show";
				this.objAnswering.animate();
			}
		},
		hideWriter : function(userId) {
			if (!this.node.writers || !this.node.writersBlock)
			{
				return;
			}

			var id = ["writer", this.exemplarId, userId].join("-");
			var node = BX(id);

			if (this.node.writers.childNodes.length > 1)
			{
				(new BX["easing"]({
					duration : 500,
					start : { opacity: 100},
					finish : { opacity : 0},
					transition : BX["easing"].makeEaseOut(BX["easing"].transitions.quart),
					step : function(state){
						if (node)
							node.style.opacity = state.opacity / 100;
					},
					complete : function(){
						if (node && node.parentNode)
						{
							node.parentNode.removeChild(node);
						}
					}.bind(this)
				})).animate();
			}
			else
			{
				if (this.objAnswering && this.objAnswering.name !== "hide")
				{
					this.objAnswering.stop();
				}

				if (!this.objAnswering || this.objAnswering.name !== "hide")
				{
					this.objAnswering = (new BX["easing"]({
						duration : 500,
						start : { opacity: 100},
						finish : { opacity : 0},
						transition : BX["easing"].makeEaseOut(BX.easing.transitions.quart),
						step : function(state){
							this.node.writersBlock.style.opacity = state.opacity / 100;
						}.bind(this),
						complete : function(){
							this.node.writersBlock.style.display = "none";
							if (node && node.parentNode)
							{
								node.parentNode.removeChild(node);
							}
						}.bind(this)
					}));
					this.objAnswering.name = "hide";
					this.objAnswering.animate();
				}
			}
		},
		/*
		* @params array data Like an {
		errorMessage : "ERROR_MESSAGE",
		okMessage : "OK_MESSAGE",
		status : true,
		message : "html text",
		messageBBCode : "bbcode text",
		messageId : [ENTITY_XML_ID, RESULT],
		messageFields : {}}
		*/
		getCommentNode : function(messageId) {
			if (!this.node[messageId] ||
				!document.body.contains(this.node[messageId]))
			{
				this.node[messageId] = BX.findChild(this.node.main, {attrs : { id : ["record", this.getXmlId(), messageId, "cover"].join("-")}}, true);
			}
			return this.node[messageId];
		},
		add : function(messageId, data, edit, animation, options) {
			if (!messageId || !messageId[1] || !BX.type.isPlainObject(data))
			{
				return false;
			}

			var author = (data["AUTHOR"] || (data["messageFields"] ? data["messageFields"]["AUTHOR"] : null));
			if (author)
			{
				this.hideWriter(author["ID"]);
			}

			var container = this.getCommentNode(messageId[1]);
			if (!container && messageId[1] < this.mid)
			{
				return false;
			}

			var id = messageId.join("-");
			var html = (data["message"] ||  window.fcParseTemplate(
					{ messageFields : data["messageFields"] },
					{
						EXEMPLAR_ID : this.exemplarId,
						RIGHTS : this.rights,
						DATE_TIME_FORMAT : this.DATE_TIME_FORMAT,
						VIEW_URL : this.params.VIEW_URL,
						EDIT_URL : this.params.EDIT_URL,
						MODERATE_URL : this.params.MODERATE_URL,
						DELETE_URL : this.params.DELETE_URL,
						AUTHOR_URL : this.params.AUTHOR_URL,
						AUTHOR_URL_PARAMS : this.params.AUTHOR_URL_PARAMS,

						NAME_TEMPLATE : this.params.NAME_TEMPLATE,
						SHOW_LOGIN : this.params.SHOW_LOGIN,
						CLASSNAME : BX.type.isPlainObject(options) && options.live ? 'feed-com-block-live' : '',
					},
					this.getTemplate()
				));
			var ob = BX.processHTML(html, false);
			var results;
			var newCommentsContainer = this.node.newComments;
			var acts = [ "MODERATE", "EDIT", "DELETE" ];
			var needToCheck = false;
			var height = 0;

			//region Changing rights for messages with right = ownlast
			for (var ii in acts)
			{
				if (
					acts.hasOwnProperty(ii)
					&& this.rights[acts[ii]] === "OWNLAST"
				)
				{
					needToCheck = true;
					break;
				}
			}

			if (needToCheck)
			{
				results = (
					newCommentsContainer.lastChild
					&& BX.hasClass(newCommentsContainer.lastChild, "feed-com-block-cover")
						? [newCommentsContainer.lastChild]
						: []
				);
				var res;
				var res2;
				if (this.addCheckPreviousNodes !== true)
				{
					results = BX.findChildren(this.node.main, {tagName : "DIV", "className" : "feed-com-block-cover"}, false) || [];
					results.concat(BX.findChildren(newCommentsContainer, {tagName : "DIV",  "className" : "feed-com-block-cover"}, false) || []);
					this.addCheckPreviousNodes = true;
				}
				while ((res = results.pop()) && res)
				{
					res2 = BX.findChild(res, {attrs : {id : res.id.replace("-cover", "-actions")}}, true);
					if (res2)
					{
						if (this.rights["EDIT"] === "OWNLAST")
						{
							res2.setAttribute("bx-mpl-edit-show", "N");
						}
						if (this.rights["MODERATE"] === "OWNLAST")
						{
							res2.setAttribute("bx-mpl-moderate-show", "N");
						}
						if (this.rights["DELETE"] === "OWNLAST")
						{
							res2.setAttribute("bx-mpl-delete-show", "N");
						}
					}
				}
			}
			//endregion

			var changeOpacity = false;
			if (!container) // add
			{
				container = BX.create("DIV", {
					attrs : {
						id : ("record-" + id + "-cover"),
						className : "feed-com-block-cover",
						"bx-mpl-xml-id" : this.getXmlId(),
						"bx-mpl-entity-id" : messageId[1],
						"bx-mpl-read-status" : (Number(author["ID"]) !== Number(BX.message("USER_ID")) ? "new" : "old"),
						"bx-mpl-block" : "main",
					},
					style : {
						opacity: 0,
						height: 0,
						overflow: "hidden",
						marginBottom: 0,
					},
					html: ob.HTML,
				});
				newCommentsContainer.appendChild(container);
				changeOpacity = true;
			}
			else // edit
			{
				var containerBody = BX.create("DIV", {
						attrs : {
							id : ("record-" + id + "-cover"),
							className : "feed-com-block-cover"
						},
						style : {
							display : "none"
						},
						html : ob.HTML
					});
				var containerForRemove = container;

				// get expanded status
				var commentOuterNode = BX.findChild(containerForRemove, {
					tag: 'div',
					className: 'feed-com-text-inner',
				}, true);
				var expanded = (
					commentOuterNode
					&& commentOuterNode.classList.contains("feed-com-text-inner-expanded")
				);

				// set expanded status
				if (expanded)
				{
					commentOuterNode = BX.findChild(containerBody, {
						tag: "div",
						className: "feed-com-text-inner"
					}, true);
					if (commentOuterNode)
					{
						commentOuterNode.classList.add("feed-com-text-inner-expanded");
					}
				}

				container.parentNode.insertBefore(containerBody, container);
				container.removeAttribute("id");
				height = container.scrollHeight;
				BX.hide(container);
				BX.show(containerBody);
				container = containerBody;
				this.node[messageId[1]] = container;
				setTimeout(function() {
					BX.remove(containerForRemove);
				}, 1000);
			}

			if (
				animation !== "simple"
				&& BX.Type.isUndefined(window.BXMobileApp) // non-mobile
				&& !( // if it is not a slider over
					window.top === window &&
					BX.getClass('BX.SidePanel.Instance') &&
					BX.SidePanel.Instance.isOpen()
				)
				&& !(
					BX.type.isNotEmptyObject(BXRL) &&
					BX.type.isNotEmptyObject(BXRL.render) &&
					BX.type.isDomNode(BXRL.render.reactionsPopup) &&
					!BXRL.render.reactionsPopup.classList.contains('feed-post-emoji-popup-invisible')
				)
			)
			{
				var curPos = BX.pos(container);
				var scroll = BX.GetWindowScrollPos();
				var size = BX.GetWindowInnerSize();

				(new BX["easing"]({
					duration : 1000,
					start : { opacity : (changeOpacity ? 0 : 100), height : height},
					finish : { opacity: 100, height : container.scrollHeight + 10},
					transition : BX.easing.makeEaseOut(BX.easing.transitions.quart),
					step : function(state){
						container.style.height = state.height + "px";
						container.style.opacity = state.opacity / 100;
						if (scroll.scrollTop > 0 && curPos.top < (scroll.scrollTop + size.innerHeight))
						{
							window.scrollTo(0, scroll.scrollTop + state.height);
						}
					},
					complete : function(){
						container.style.cssText = "";
						BX.onCustomEvent(this, "OnUCRecordWasShown", [this.ENTITY_XML_ID, id, container]);
					}.bind(this)
				})).animate();
			}
			else
			{
				(new BX["easing"]({
					duration : 500,
					start : { opacity : (changeOpacity ? 0 : 100), height : height},
					finish : { opacity : 100, height : container.scrollHeight + 10},
					transition : BX.easing.makeEaseOut(BX.easing.transitions.cubic),
					step : function(state) {
						container.style.height = state.height + "px";
						container.style.opacity = state.opacity / 100;
					},
					complete : BX.proxy(function() {
						container.style.cssText = "";
						BX.onCustomEvent(this, "OnUCRecordWasShown", [this.ENTITY_XML_ID, id, container]);
					}, this)
				})).animate();
			}

			var cnt = 0,
			func = function()
			{
				if (100 < ++cnt)
				{
					return;
				}
				if (this.getCommentNode(messageId[1]).childNodes.length > 0)
				{
					BX.ajax.processScripts(ob.SCRIPT);
					if (this.params["BIND_VIEWER"] === "Y" && BX["viewElementBind"])
					{
						BX.viewElementBind(
							this.getCommentNode(messageId[1]), {},
							function(node ){
								return BX.type.isElementNode(node) && (node.getAttribute("data-bx-viewer") || node.getAttribute("data-bx-image"));
							}
						);
					}
				}
				else
				{
					setTimeout(func, 500)
				}
				BX.onCustomEvent(window, "OnUCRecordHasDrawn", [this.ENTITY_XML_ID, messageId, (data["messageFields"] || data)]);
				BX.onCustomEvent(window, "OnUCCommentWasAdded", [this.ENTITY_XML_ID, messageId, (data["messageFields"] || data)]);
				BX.onCustomEvent(window, "OnUCFeedChanged", [messageId]);
			}.bind(this);
			setTimeout(func, 500);
			return true;
		},
		act : function(url, id, act) {
			if (
				this.ajax["processComment"] !== true
				&& BX.type.isNotEmptyString(url)
				&& url.substr(0, 1) !== '/'
			)
			{
				try { eval(url); return false; }
				catch(e) {}

				if (BX.type.isFunction(url))
				{
					url(this, id, act);
					return false;
				}
			}
			this.showWait(id);

			// act in ["EDIT", "DELETE", "GET", "SHOW", "HIDE"]
			id = parseInt(id);
			var data = {
				sessid : BX.bitrix_sessid(),
				MODE : "RECORD",
				NOREDIRECT : "Y",
				AJAX_POST : "Y",
				FILTER : {"ID" : id},
				ACTION : (act === "EDIT" ? "GET" : act),
				ID : id,
				ENTITY_XML_ID : this.ENTITY_XML_ID,
				OPERATION_ID : this.getOperationId(),
				EXEMPLAR_ID: this.exemplarId,
				scope: this.scope,
			};
			url = (url.indexOf('#') !== -1 ? url.substr(0, url.indexOf('#')) : url);

			var onsuccess = function(data) {
				this.closeWait(id);
				if (data["status"] === "error")
				{
					this.showError(id, data["message"] || "Unknown error.");
					return;
				}
				var container = this.getCommentNode(id);
				if (container)
				{
					if (act === "DELETE")
					{
						BX.hide(container);
						this.comments.delete(id.toString());
						BX.onCustomEvent(window, "OnUCommentWasDeleted", [this.ENTITY_XML_ID, [this.ENTITY_XML_ID, id], data]);
					}
					else if (act !== "EDIT" && !!data["message"])
					{
						var ob = BX.processHTML(data["message"], false);
						container.innerHTML = ob.HTML;
						var cnt = 0,
							func = function()
							{
								cnt++;
								if (cnt < 100)
								{
									if (container.childNodes.length > 0)
										BX.ajax.processScripts(ob.SCRIPT);
									else
										BX.defer(func)();
								}
							};
						BX.defer(func)();
						data["okMessage"] = "";
					}
				}
				if (this.form !== null)
				{
					BX.onCustomEvent(this.form, "onEdit", [this, id, data, act]);
				}
				else
				{
					BX.onCustomEvent(window, "OnUCAfterRecordEdit", [this.ENTITY_XML_ID, id, data, act]);
				}
				BX.onCustomEvent(window, "OnUCFeedChanged", [id]);
			}.bind(this);

			var onfailure = function(data){
				this.closeWait(id);

				var errorText = data;

				if (BX.type.isNotEmptyObject(data))
				{
					if (BX.type.isArray(data.errors) && BX.type.isNotEmptyString(data.errors[0].message))
					{
						errorText = data.errors[0].message;
					}
					else if (BX.type.isNotEmptyObject(data.data) && BX.type.isNotEmptyString(data.data.message))
					{
						errorText = data.data.message;
					}
				}
				this.showError(id, errorText);
			}.bind(this);

			if (this.ajax["processComment"] === true)
			{
				BX.ajax.runComponentAction(this.ajax.componentName, "processComment", {
					mode: "class",
					data: data,
					signedParameters: this.ajax.params,
				}).then(onsuccess, onfailure);
			}
			else
			{
				BX.ajax({
					method: "GET",
					url: (url + (url.indexOf('?') !== -1 ? "&" : "?") + BX.ajax.prepareData(data)),
					data: "",
					dataType: "json",
					onsuccess: onsuccess,
					onfailure: onfailure
				});
			}
		},
		showError : function(id, text) {
			if (this.errorWindow)
			{
				this.errorWindow.close();
			}

			this.errorWindow = new BX.PopupWindow("bx-comments-error", null, {
				autoHide: false,
				zIndex: 200,
				overlay: {opacity: 50, backgroundColor: "#000000"},
				buttons: [
					new BX.PopupWindowButton({
						text : BX.message("MPL_CLOSE"),
						events : { click : BX.delegate(function() {
							if (this.errorWindow)
								this.errorWindow.close(); }, this) }
					})
				],
				closeByEsc: true,
				titleBar: {content: BX.create("span", {props : { className : "popup-window-titlebar-text feed-error-title" },
					html: '<div class="feed-error-icon"></div>' + BX.message("MPL_ERROR_OCCURRED")})},
				//titleBar: ,
				// ,
				closeIcon : true,
				contentColor : "white",
				content : '<div class="feed-error-block">' + text + '</div>'
			});
			this.errorWindow.show();
		},
		checkHash : function() {
			if (repo["hashHasBeenFound"] === true)
			{
				return;
			}
			var tw = /%23com(\d+)/gi.exec(location.href);
			var ENTITY_ID = parseInt(
				location.hash && location.hash.indexOf("#com") >= 0
					? location.hash.replace('#com', '')
					: (tw ? tw[1] : 0)
			);

			if (ENTITY_ID > 0)
			{
				var node = BX.findChild(this.node.main, {attrs : { id : "record-" + [this.ENTITY_XML_ID, ENTITY_ID].join("-") + "-cover"}}, true, false);
				if (node)
				{
					var collapsedBlock = BX.findParent(node, {attrs : {"data-bx-role": "collapsed-block"}}, this.node.main);
					if (collapsedBlock)
					{
						var checkB = collapsedBlock.querySelector("input[type=checkbox]");
						if (!checkB.checked)
						{
							checkB.click(true);
						}
					}
					repo["hashHasBeenFound"] = true;
					var curPos = BX.pos(node);
					window.scrollTo(0, curPos["top"]);
					var contentBlock = BX.findChild(node, {className: "feed-com-main-content"}, true, false);
					BX.removeClass(contentBlock, "feed-com-block-pointer-to-new feed-com-block-new");
					BX.addClass(contentBlock, "feed-com-block-pointer");
				}
			}
		},
		registerComments : function() {
			var ii;
			var nodes = BX.findChild(this.node.main, {
				tag: 'DIV',
				attrs: {
					'bx-mpl-block': 'main',
					'bx-mpl-xml-id': this.ENTITY_XML_ID,
				}
			}, true, true);
			for (ii = 0; ii < nodes.length; ii++)
			{
				if (nodes[ii].getAttribute("bx-mpl-blank-status") === "blank")
				{
					this.blankComments.set(nodes[ii].getAttribute("bx-mpl-entity-id"), nodes[ii]);
					scrSpy.set([this.getXmlId(), nodes[ii].getAttribute("bx-mpl-entity-id")].join("-"));
				}
				else
				{
					if (nodes[ii].getAttribute("bx-mpl-read-status") === "new")
					{
						this.unreadComments.set(nodes[ii].getAttribute("bx-mpl-entity-id"), nodes[ii]);
						scrSpy.set([this.getXmlId(), nodes[ii].getAttribute("bx-mpl-entity-id")].join("-"));
					}
					this.recalcMoreButtonComment(nodes[ii].getAttribute("bx-mpl-entity-id"));
					this.comments.set(nodes[ii].getAttribute("bx-mpl-entity-id"), []);
				}
			}
		},
		registerBlankComment: function(id, check) {
			if (check !== true)
			{
				setTimeout(this.registerBlankComment, 1000, id, true);
				return;
			}
			var node = this.getCommentNode(id);
			if (!node || this.comments.has(id) || this.blankComments.has(id))
			{
				return;
			}
			this.blankComments.set(id, node);
			scrSpy.set([this.getXmlId(), id].join("-"));
		},
		registerNewComment: function(id, check) {
			if (check !== true)
			{
				setTimeout(this.registerNewComment, 1000, id, true);
				return;
			}
			var node = this.getCommentNode(id);
			if (!node || this.comments.has(id))
			{
				return;
			}
			this.comments.set(id, []);
			if (node.getAttribute("bx-mpl-read-status") === "new")
			{
				scrSpy.set([this.getXmlId(), id].join("-"), node);
				this.unreadComments.set(id, node);
			}
			this.recalcMoreButtonComment(id);
		},
		recalcMoreButtonComment : function(id) {
			var commentNode = this.getCommentNode(id);
			if (!BX(commentNode))
			{
				return;
			}
			var bodyBlock = commentNode.bodyBlock || BX.findChild(commentNode, { attrs: {"bx-mpl-block" : "body"}}, true);
			if (!bodyBlock)
			{
				return;
			}
			commentNode.bodyBlock = bodyBlock;

			var textBlock = commentNode.textBlock || BX.findChild(bodyBlock, { attrs: {"bx-mpl-block" : "text"}}, true);
			if (!textBlock)
			{
				return;
			}
			commentNode.textBlock = textBlock;

			var moreButtonBlock = commentNode.moreButtonBlock || BX.findChild(commentNode, { attrs: {"bx-mpl-block" : "more-button"}}, true);
			commentNode.moreButtonBlock = moreButtonBlock;

			var pos = BX.pos(bodyBlock);
			var pos2 = BX.pos(textBlock);

			if (pos.height >= pos2.height)
			{
				moreButtonBlock.style.display = "none";
			}
			else if (moreButtonBlock.style.display !== "block")
			{
				moreButtonBlock.style.display = "block";
			}

			var ii = null;
			var onLoadImageList = BX.findChildren(bodyBlock, { attr: { "data-bx-onload" : "Y" } },true);
			var funcOnLoad = function(){this.recalcMoreButtonComment(id);}.bind(this);

			if (BX.type.isArray(onLoadImageList))
			{
				for (ii = 0; ii < onLoadImageList.length; ii++)
				{
					onLoadImageList[ii].addEventListener("load", funcOnLoad);
					onLoadImageList[ii].setAttribute("data-bx-onload", "N");
				}
			}

			var onLoadVideoList = bodyBlock.querySelectorAll('video');
			for (ii = 0; ii < onLoadVideoList.length; ii++)
			{
				onLoadVideoList[ii].addEventListener("loadedmetadata", funcOnLoad);
			}

			if (!commentNode.hasOwnProperty("__boundOnForumSpoilerToggle"))
			{
				commentNode.__boundOnForumSpoilerToggle = true;
				BX.addCustomEvent(commentNode, "onForumSpoilerToggle", funcOnLoad);
			}
		},
		checkVisibleComments : function(screenPosition) {
			if (!this.canCheckVisibleComments)
			{
				return;
			}
			var keys = this.getVisibleCommentIds(this.unreadComments, screenPosition);
			var key;
			while (key = keys.shift())
			{
				this.markCommentAsRead(key);
			}
			window.fclistdebug = true;
			keys = this.getVisibleCommentIds(this.blankComments, screenPosition);

			while (key = keys.shift())
			{
				this.markCommentAsBlank(key);
			}
		},
		getVisibleCommentIds : function(comments, screenPosition) {
			var result = [];

			if (comments.size <= 0)
			{
				return result;
			}

			var pos = BX.pos(this.node.main);
			if (
				pos.top > screenPosition.bottom
				|| pos.bottom < screenPosition.top
				|| (pos.top === 0 && pos.bottom === 0)
			)
			{
				return result;
			}

			var keys = comments.keys();
			var key = keys.next()
			var node;

			while (key.done !== true)
			{
				node = comments.get(key.value);
				if (node.offsetWidth || node.offsetHeight || node.getClientRects().length)
				{
					pos = node.pos || BX.pos(node);
					node.pos = pos;
					if (
						screenPosition.top <= pos.top
						&& pos.top <= screenPosition.bottom
						&& node.offsetParent !== null
					)
					{
						result.push(key.value);
					}
				}
				key = keys.next();
			}
			return result;
		},
		markCommentAsRead : function(id) {
			if (!this.unreadComments.has(id))
			{
				return;
			}

			var node = this.unreadComments.get(id);
			node.setAttribute("bx-mpl-read-status", "old");
			this.unreadComments.delete(id);
			scrSpy.unset([this.getXmlId(), id].join("-"));

			if (this.node.newComments.contains(node))
			{
				this.sendCommentAsRead(id);
			}

			BX.removeClass(node, "comment-new-answer");

			if (this.__checkNodeCorner !== true)
			{
				this.__checkNodeCorner = true;
				var cornerNode = BX.findChild(this.node.main, {className: "feed-com-corner"});
				if (cornerNode)
				{
					BX.addClass(cornerNode, "feed-post-block-corner-fade");
				}
			}

			var node1 = BX.findChild(node, {className: "feed-com-main-content"}, true, false);
			if (node1)
			{
				BX.removeClass(node1, "feed-com-block-pointer-to-new feed-com-block-new");
				BX.addClass(node1, "feed-com-block-read");
			}

			var node2 = BX.findChild(node, {className: "feed-com-block"}, true);
			BX.onCustomEvent(window, "OnUCCommentWasRead", [this.getXmlId(), [this.getXmlId(), id], {
					live: (node2 && node2.classList.contains('feed-com-block-live')),
					new: (!node1 || !node1.classList.contains('feed-com-block-old'))
			}]);
		},
		sendCommentAsRead : function(id) {
			if (this.ajax["readComment"] !== true)
			{
				return;
			}

			if (!this.sendCommentAsReadData)
			{
				this.sendCommentAsReadData = {
					mid : 0,
					timeoutId : 0,
					func : function() {
						BX.unbind(window, "beforeunload", this.sendCommentAsReadData.func);
						BX.removeCustomEvent(window, "onHidePageBefore", this.sendCommentAsReadData.func); //for mobile version
						this.sendCommentAsReadData.timeoutId = 0;
						BX.ajax.runComponentAction(this.ajax.componentName, "readComment", {
							mode: "class",
							data: {"ID" : this.sendCommentAsReadData.mid},
							signedParameters: this.ajax.params,
						});
					}.bind(this)
				};
			}

			if (this.sendCommentAsReadData.mid > id)
			{
				return;
			}
			this.sendCommentAsReadData.mid = id;

			if (this.sendCommentAsReadData.timeoutId <= 0)
			{
				BX.bind(window, "beforeunload", this.sendCommentAsReadData.func);
				BX.addCustomEvent(window, "onHidePageBefore", this.sendCommentAsReadData.func); //for mobile version
				this.sendCommentAsReadData.timeoutId = setTimeout(this.sendCommentAsReadData.func, 6000);
			}
		},
		markCommentAsBlank : function(id) {
			if (!this.blankComments.has(id))
			{
				return;
			}
			var node = this.blankComments.get(id);
			var block = node.parentNode;
			if (block && !block.bxwaiter)
			{
				fcShowWait(block)
			}
			this.blankComments.delete(id);
			scrSpy.unset([this.getXmlId(), id].join("-"));
			this.blankCommentsForAjax.set(id, node);
			setTimeout(this.sendCommentAsBlank, 1000);
		},
		sendCommentAsBlank : function() {
			if (
				this.ajax["getComment"] !== true
				|| this.blankCommentsForAjax.size <= 0
			)
			{
				return;
			}

			var keys = Array.from(this.blankCommentsForAjax.keys());
			var comments = new Map(this.blankCommentsForAjax);
			this.blankCommentsForAjax.clear();

			var success = function(data) {
				comments.forEach(function(node, id) {
					fcCloseWait(node.parentNode);

					var messageData = data["messageList"][id];
					if (!messageData)
					{
						node.parentNode.removeChild(node);
					}
					else
					{
						var ob = BX.processHTML(messageData["message"], false);
						node.innerHTML = ob.HTML;
						var func = function(cnt) {
							if (node.childNodes.length > 0)
							{
								BX.ajax.processScripts(ob.SCRIPT);
							}
							else if (cnt < 100)
							{
								setTimeout(func, 500, (cnt + 1));
							}
						};
						func(0);
					}
				}.bind(this));
			}.bind(this);

			BX.ajax.runComponentAction(this.ajax.componentName, "getComment", {
				mode: "class",
				data: {
					sessid : BX.bitrix_sessid(),
					MODE : "RECORDS",
					NOREDIRECT : "Y",
					AJAX_POST : "Y",
					FILTER : {ID : keys},
					ACTION : "GET",
					ID : keys,
					ENTITY_XML_ID : this.ENTITY_XML_ID,
					OPERATION_ID : this.getOperationId(),
					EXEMPLAR_ID: this.exemplarId,
					scope: this.scope,
				},
				signedParameters: this.ajax.params,
			}).then(success, success);
		},
		getTemplate : function() {
			return this.template;
		},
		showWait : function(id) {
			fcShowWait(BX("record-" + this.ENTITY_XML_ID + "-" + id + "-actions"));
		},
		closeWait : function(id) {
			fcCloseWait(BX("record-" + this.ENTITY_XML_ID + "-" + id + "-actions")||null);
		}
	};

	window.FCList.getQuoteData = function(){ return quoteData; };
	window.FCList.getInstance = function(params, add) {
		if (!repo.listByXmlId.has(params["ENTITY_XML_ID"]) && add !== undefined)
		{
			new window.FCList(params, add);
		}
		return repo.listByXmlId.get(params["ENTITY_XML_ID"]);
	};
//region functions with Node
	var lastWaitElement = null;
	var fcShowWait = function(el) {
		if (el && !BX.type.isElementNode(el))
		{
			el = null;
		}

		el = el || this;

		if (BX.type.isElementNode(el))
		{
			BX.defer(function(){el.disabled = true})();
			var waiter_parent = BX.findParent(el, BX.is_relative);

			el.bxwaiter = (waiter_parent || document.body).appendChild(BX.create("DIV", {
				props: {className: "feed-com-loader"},
				style: {position: "absolute"}
			}));
			lastWaitElement = el;

			return el.bxwaiter;
		}
		return true;
	};
	var fcCloseWait = function(el) {
		if (el && !BX.type.isElementNode(el))
		{
			el = null;
		}

		el = el || lastWaitElement || this;

		if (BX.type.isElementNode(el))
		{
			if (el.bxwaiter && el.bxwaiter.parentNode)
			{
				el.bxwaiter.parentNode.removeChild(el.bxwaiter);
				delete el.bxwaiter;
			}

			el.disabled = false;
			if (lastWaitElement == el)
				lastWaitElement = null;
		}
	};
	var fcShowActions = function(eventNode, ID, el) {
		var panels = [];
		var linkId = BX.util.getRandomString(20);
		if (el.getAttribute("bx-mpl-view-show") == "Y")
		{
			panels.push({
				text : BX.message("MPL_MES_HREF"),
				href : el.getAttribute("bx-mpl-view-url").replace(/\\#(.+)$/gi, "") + "#com" + ID,
				target : "_top"
			});
			panels.push({
				html : '<span id="record-popup-' + linkId + '-link-text">' + BX.message("B_B_MS_LINK") + '</span>' +
					'<span id="record-popup-' + linkId + '-link-icon-animate" class="comment-menu-link-icon-wrap">' +
						'<span class="comment-menu-link-icon" id="record-popup-' + linkId + '-link-icon-done" style="display: none;">' +

						'</span>' +
					'</span>',
				onclick : function() {
					var
						id = "record-popup-" + linkId + "-link",
						urlView = el.getAttribute("bx-mpl-view-url").replace(/#(.+)$/gi, "") + "#com" + ID,
						menuItemText = BX(id + "-text"),
						menuItemIconDone = BX(id + "-icon-done");

					urlView = (urlView.indexOf("http") < 0 ? (location.protocol + '//' + location.host) : "") + urlView;

					if (BX.clipboard.isCopySupported())
					{
						if (menuItemText && menuItemText.getAttribute("data-block-click") == "Y")
						{
							return;
						}

						BX.clipboard.copy(urlView);
						if (
							menuItemText
							&& menuItemIconDone
						)
						{
							menuItemIconDone.style.display = "inline-block";
							BX.removeClass(BX(id + "-icon-animate"), "comment-menu-link-icon-animate");

							BX.adjust(BX(id + "-text"), {
								attrs: {
									"data-block-click": "Y"
								}
							});

							setTimeout(function() {
								BX.addClass(BX(id + "-icon-animate"), "comment-menu-link-icon-animate");
							}, 1);

							setTimeout(function() {
								BX.adjust(BX(id + "-text"), {
									attrs: {
										"data-block-click": "N"
									}
								});
							}, 500);
						}

						return;
					}

					var it = BX.proxy_context;
					var height = parseInt(
						!!it.getAttribute('bx-height')
							? it.getAttribute('bx-height')
							: it.offsetHeight
					);

					if (it.getAttribute("bx-status") != "shown")
					{
						it.setAttribute("bx-status", "shown");
						if (!BX(id) && !!BX(id + "-text"))
						{
							var
								node = BX(id + "-text"),
								pos = BX.pos(node),
								pos2 = BX.pos(node.parentNode),
								nodes = BX.findChildren(node.parentNode.parentNode.parentNode, {className : "menu-popup-item-text"}, true);

							pos["height"] = pos2["height"] - 1;
							if (nodes)
							{
								var width = 0, pos3;
								for (var ii = 0; ii < nodes.length; ii++)
								{
									pos3 = BX.pos(nodes[ii]);
									width = Math.max(width, pos3["width"]);
								}
								pos2["width"] = width;
							}
							BX.adjust(it, {
								attrs : {"bx-height" : it.offsetHeight},
								style : { overflow : "hidden", display : "block"},
								children : [
									BX.create("BR"),
									BX.create("DIV", { attrs : {id : id},
										children : [
											BX.create("SPAN", {attrs : {"className" : "menu-popup-item-left"}}),
											BX.create("SPAN", {attrs : {"className" : "menu-popup-item-icon"}}),
											BX.create("SPAN", {attrs : {"className" : "menu-popup-item-text"},
												children : [
													BX.create("INPUT", {
															attrs : {
																id : id + "-input",
																type : "text",
																value : urlView} ,
															style : {
																height : pos2["height"] + "px",
																width : pos2["width"] + "px"
															},
															events : {
																click : function(e) {
																	this.select();
																	e.preventDefault();
																}
															}
														}
													)
												]
											})
										]
									}),
									BX.create("SPAN", {"className" : "menu-popup-item-right"})
								]
							});
						}
						(new BX["fx"]({
							time: 0.2,
							step: 0.05,
							type: "linear",
							start: height,
							finish: height * 2,
							callback: BX.delegate(function(height) {this.style.height = height + "px";}, it)
						})).start();
						BX.fx.show(BX(id), 0.2);
						BX(id + "-input").select();
					}
					else
					{
						it.setAttribute("bx-status", "hidden");
						(new BX["fx"]({
							time: 0.2,
							step: 0.05,
							type: "linear",
							start: it.offsetHeight,
							finish: height,
							callback: BX.delegate(function(height) {this.style.height = height + "px";}, it)
						})).start();
						BX.fx.hide(BX(id), 0.2);
					}
				}
			});
		}

		if (el.getAttribute("bx-mpl-edit-show") == "Y")
		{
			panels.push({
				text : BX.message("BPC_MES_EDIT"),
				onclick : function() {
					BX.onCustomEvent(eventNode, "onAct", [el.getAttribute("bx-mpl-edit-url"), ID, "EDIT"]);
					this.popupWindow.close();
					return false;
				}
			});
		}

		if (el.getAttribute("bx-mpl-moderate-show") == "Y")
		{
			var hidden = el.getAttribute("bx-mpl-moderate-approved") == "hidden";
			panels.push({
				text : (hidden ? BX.message("BPC_MES_SHOW") : BX.message("BPC_MES_HIDE")),
				onclick : function() {

					var moderateUrl = el.getAttribute("bx-mpl-moderate-url").
						replace("#action#", (hidden ? "show" : "hide")).
						replace("#ACTION#", (hidden ? "SHOW" : "HIDE"));

					if (BX.type.isNotEmptyString(moderateUrl))
					{
						moderateUrl = BX.util.add_url_param(moderateUrl, {
							b24statAction: (hidden ? "showComment" : "hideComment")
						});
					}
					BX.onCustomEvent(eventNode, "onAct", [moderateUrl, ID, (hidden ? "SHOW" : "HIDE")]);
					this.popupWindow.close();}
			});
		}
		if (el.getAttribute("bx-mpl-delete-show") == "Y")
		{
			panels.push({
				text : BX.message("BPC_MES_DELETE"),
				onclick : function() {
					if(window.confirm(BX.message("BPC_MES_DELETE_POST_CONFIRM")))
					{
						BX.onCustomEvent(eventNode, "onAct", [el.getAttribute("bx-mpl-delete-url"), ID, "DELETE"]);
					}
					this.popupWindow.close();
					return false;
				}
			});
		}

		var entityXmlId = el.getAttribute('bx-mpl-post-entity-xml-id');
		if (
			el.getAttribute('bx-mpl-edit-show') == 'Y'
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
				&& result.canUnsetAsResult
				&& result.canUnsetAsResult(parseInt(ID, 10))
			)
			{
				panels.push({
					text : BX.message("BPC_MES_DELETE_TASK_RESULT"),
					onclick : function() {
						BX.Tasks.ResultAction.getInstance().deleteFromComment(ID);
						this.popupWindow.close();
						return false;
					}
				});
			}
			else if (
				result
				&& result.context === 'task'
				&& result.canSetAsResult
				&& result.canSetAsResult(parseInt(ID, 10))
			)
			{
				panels.push({
					text : BX.message("BPC_MES_CREATE_TASK_RESULT"),
					onclick : function() {
						BX.Tasks.ResultAction.getInstance().createFromComment(ID);
						this.popupWindow.close();
						return false;
					}
				});
			}
		}

		if (
			el.getAttribute('bx-mpl-createtask-show') === 'Y'
			&& !BX.type.isUndefined(BX.Livefeed)
		)
		{
			var commentEntityType = el.getAttribute('bx-mpl-comment-entity-type');
			var postEntityType = el.getAttribute('bx-mpl-post-entity-type');

			panels.push({
				text : BX.message('BPC_MES_CREATE_TASK'),
				onclick : function() {
					BX.Livefeed.TaskCreator.create({
						postEntityType: (BX.type.isNotEmptyString(postEntityType) ? postEntityType : 'BLOG_POST'),
						entityType: (BX.type.isNotEmptyString(commentEntityType) ? commentEntityType : 'BLOG_COMMENT'),
						entityId: ID,
					});
					this.popupWindow.close();
					return false;
				}
			});
		}

		if (
			el.getAttribute('bx-mpl-createsubtask-show') === 'Y'
			&& !BX.type.isUndefined(BX.Livefeed)
		)
		{
			var postEntityXmlId = el.getAttribute('bx-mpl-post-entity-xml-id');

			var matches = postEntityXmlId.match(/^TASK_(\d+)$/i);
			if (matches)
			{
				panels.push({
					text : BX.message('BPC_MES_CREATE_SUBTASK'),
					onclick : function() {
						BX.Livefeed.TaskCreator.create({
							postEntityType: postEntityType,
							entityType: commentEntityType,
							entityId: ID,
							parentTaskId: parseInt(matches[1]),
						});
						this.popupWindow.close();
						return false;
					}
				});
			}
		}

		if (panels.length > 0)
		{
			for (var ii in panels)
			{
				if (panels.hasOwnProperty(ii))
				{
					panels[ii]["className"] = "blog-comment-popup-menu";
				}
			}

			var popupParams = {
				offsetLeft: -18,
				offsetTop: 2,
				lightShadow: false,
				angle: {position: "top", offset: 50},
				events : {
					onPopupClose : function() { this.destroy();BX.PopupMenu.Data["action-" + linkId] = null; }
				}
			};

			BX.onCustomEvent("OnUCCommentActionsShown", [eventNode, ID, panels, popupParams]);
			BX.PopupMenu.show("action-" + linkId, el,
				panels,
				popupParams
			);
		}
	};
	var fcCommentExpand = function(el) {
		BX.UI.Animations.expand({
			moreButtonNode: el,
			type: "comment",
			classBlock: "feed-com-block",
			classOuter: "feed-com-text-inner",
			classInner: "feed-com-text-inner-inner",
			heightLimit: 200,
			callback: function(el) {
				BX.onCustomEvent(window, "OnUCRecordWasExpanded", [el]);
				el.classList.add("feed-com-text-inner-expanded");

				var commentContentId = el.getAttribute("bx-content-view-xml-id");
				if (BX.type.isNotEmptyString(commentContentId))
				{
					BX.onCustomEvent(window, "OnUCFeedChanged", [ commentContentId.split("-") ]);
				}
			}
		})
	};
//endregion
	/**
	* Parse template with params
	* We work with such template as array(
	*     "messageFields" => array(
		"#ID#" =>
			$res["ID"],
		"#FULL_ID#" =>
			$arParams["ENTITY_XML_ID"]."-".$res["ID"],
		"#ENTITY_XML_ID#" =>
			$arParams["ENTITY_XML_ID"],
		"#EXEMPLAR_ID#" =>
			$arParams["EXEMPLAR_ID"],
		"#NEW#" =>
			($res["NEW"] == "Y" ? "new" : "old"),
		"#APPROVED#" =>
			($res["APPROVED"] != "Y" ? "hidden" : "approved"),
		"#DATE#" =>
			(ConvertTimeStamp($res["POST_TIMESTAMP"], "SHORT") == $todayString ? $res["POST_TIME"] : $res["POST_DATE"]),
		"#TEXT#" =>
			$res["POST_MESSAGE_TEXT"],
		"#CLASSNAME#" =>
			(isset($res["CLASSNAME"]) ? " ".$res["CLASSNAME"] : ""),
		"#VIEW_URL#" =>
			str_replace(array("#ID#", "#id#"), $res["ID"], $arParams["VIEW_URL"]),
		"#VIEW_SHOW#" =>
			($arParams["URL_VIEW"] == "" ? "N" : "Y"),
		"#EDIT_URL#" =>
			str_replace(array("#ID#", "#id#"), $res["ID"], $arParams["EDIT_URL"]),
		"#EDIT_SHOW#" =>
			($arParams["RIGHTS"]["EDIT"] == "Y" || $arParams["RIGHTS"]["EDIT"] == "ALL" ||
			$arParams["RIGHTS"]["EDIT"] == "OWN" && $USER->GetID() == $res["AUTHOR"]["ID"] ? "Y" : "N"),
		"#MODERATE_URL#" =>
			str_replace(array("#ID#", "#id#"), $res["ID"], $arParams["MODERATE_URL"]),
		"#MODERATE_SHOW#" =>
			($arParams["RIGHTS"]["MODERATE"] == "Y" || $arParams["RIGHTS"]["MODERATE"] == "ALL" ||
			$arParams["RIGHTS"]["MODERATE"] == "OWN" && $USER->GetID() == $res["AUTHOR"]["ID"] ? "Y" : "N"),
		"#DELETE_URL#" =>
			str_replace(array("#ID#", "#id#"), $res["ID"], $arParams["DELETE_URL"]),
		"#DELETE_SHOW#" =>
			($arParams["RIGHTS"]["MODERATE"] == "Y" || $arParams["RIGHTS"]["MODERATE"] == "ALL" ||
			$arParams["RIGHTS"]["MODERATE"] == "OWN" && $USER->GetID() == $res["AUTHOR"]["ID"] ? "Y" : "N"),
		"#BEFORE_HEADER#" => $res["BEFORE_HEADER"],
		"#BEFORE_ACTIONS#" => $res["BEFORE_ACTIONS"],
		"#AFTER_ACTIONS#" => $res["AFTER_ACTIONS"],
		"#AFTER_HEADER#" => $res["AFTER_HEADER"],
		"#BEFORE#" => $res["BEFORE"],
		"#AFTER#" => $res["AFTER"],
		"#BEFORE_RECORD#" => $res["BEFORE_RECORD"],
		"#AFTER_RECORD#" => $res["AFTER_RECORD"],
		"#AUTHOR_ID#" =>
			$res["AUTHOR"]["ID"],
		"#AUTHOR_AVATAR_IS#" =>
			(empty($res["AUTHOR"]["AVATAR"]) ? "N" : "Y"),
		"#AUTHOR_AVATAR#" =>
			$res["AUTHOR"]["AVATAR"],
		"#AUTHOR_URL#" =>
			str_replace(
				array("#ID#", "#id#", "#USER_ID#", "#user_id#"),
				array($res["ID"], $res["ID"], $res["AUTHOR"]["ID"], $res["AUTHOR"]["ID"]),
				$arParams["AUTHOR_URL"]),
		"#AUTHOR_NAME#" =>
			CUser::FormatName(
			$arParams["NAME_TEMPLATE"],
			array(
				"NAME" => $res["AUTHOR"]["NAME"],
				"LAST_NAME" => $res["AUTHOR"]["LAST_NAME"],
				"SECOND_NAME" => $res["AUTHOR"]["SECOND_NAME"],
				"LOGIN" => $res["AUTHOR"]["LOGIN"],
				"NAME_LIST_FORMATTED" => ""
			),
			($arParams["SHOW_LOGIN"] != "N"),
			false),
		"#SHOW_POST_FORM#" =>
			$arParams["SHOW_POST_FORM"],
		"#AUTHOR_EXTRANET_STYLE#" =>
			($res["AUTHOR"]["IS_EXTRANET"] == "Y" ? " feed-com-name-extranet" : ""),
		"background:url("") no-repeat center;" =>
			"",
	 	"#MOBILE_HINTS#" => (isset($res['SHOW_MOBILE_HINTS']) && $res['SHOW_MOBILE_HINTS'] === 'Y')
				? '<span class="feed__mobile_btn"></span>'
				: '',
	 *     )
	 * )
	 * @param data
	 * @param params
	 * @param txt
	 * @return string
	 */
	window["fcParseTemplate"] = function(data, params, txt) {
		params = (params || {});

		params["RIGHTS"] = (params["RIGHTS"] || {});
		for (var ii = 0, rights = ["MODERATE", "EDIT", "DELETE"]; ii < rights.length; ii++)
		{
			params["RIGHTS"][rights[ii]] =
				BX.util.in_array(params["RIGHTS"][rights[ii]], ["Y", "ALL", "OWN", "OWNLAST"]) ? params["RIGHTS"][rights[ii]] : "N";
		}

		params["DATE_TIME_FORMAT"] = (!!params["DATE_TIME_FORMAT"] ? params["DATE_TIME_FORMAT"] : 'd F Y G:i');
		params["TIME_FORMAT"] = (!!params["DATE_TIME_FORMAT"] && params["DATE_TIME_FORMAT"].indexOf("a") >= 0 ? 'g:i a' : 'G:i');

		params["VIEW_URL"] = (params["VIEW_URL"] || "");
		params["EDIT_URL"] = (params["EDIT_URL"] || "");
		params["MODERATE_URL"] = (params["MODERATE_URL"] || "");
		params["DELETE_URL"] = (params["DELETE_URL"] || "");
		params["AUTHOR_URL"] = (params["AUTHOR_URL"] || "");

		params["NAME_TEMPLATE"] = (params["NAME_TEMPLATE"] || "");
		params["SHOW_LOGIN"] = (params["SHOW_LOGIN"] || "");

		var res = (data && data["messageFields"] ? data["messageFields"] : data);
		var replacement = {
				"ID" : "",
				"FULL_ID" : "",
				"CONTENT_ID" : "",
				"ENTITY_XML_ID" : "",
				"EXEMPLAR_ID" : "",
				"NEW" : "old",
				"APPROVED" : "Y",
				"DATE" : "",
				"TEXT" : "",
				"CLASSNAME" : "",
				"VIEW_URL" : "",
				"VIEW_SHOW" : "N",
				"EDIT_URL" : "",
				"EDIT_SHOW" : "N",
				"MODERATE_URL" : "",
				"MODERATE_SHOW" : "N",
				"DELETE_URL" : "",
				"DELETE_SHOW" : "N",
				"CREATETASK_SHOW" : "N",
				"BEFORE_HEADER" : "",
				"BEFORE_ACTIONS" : "",
				"AFTER_ACTIONS" : "",
				"AFTER_HEADER" : "",
				"BEFORE" : "",
				"AFTER" : "",
				"BEFORE_RECORD" : "",
				"AFTER_RECORD" : "",
				"AUTHOR_ID" : 0,
				"AUTHOR_AVATAR_IS" : "N",
				"AUTHOR_AVATAR" : "",
				"AUTHOR_URL" : "",
				"AUTHOR_NAME" : "",
				"AUTHOR_EXTRANET_STYLE" : "",
				"SHOW_POST_FORM" : "Y",
				"SHOW_MENU" : "Y",
				"VOTE_ID" : "",
				"AUTHOR_TOOLTIP_PARAMS" : "",
				"background:url('') no-repeat center;" : "",
				"LIKE_REACT" : "",
				"RATING_NONEMPTY_CLASS" : "",
				"MOBILE_HINTS" : ""
			};
		if (!!res && !!data["messageFields"])
		{
			res["AUTHOR"] = (!!res["AUTHOR"] ? res["AUTHOR"] : {});
			var timestamp = parseInt(res["POST_TIMESTAMP"]) + parseInt(BX.message("USER_TZ_OFFSET")) + parseInt(BX.message("SERVER_TZ_OFFSET"));
			var dateFormat = [
				["today", params["TIME_FORMAT"]],
				["yesterday", (params["TIME_FORMAT"].indexOf("yesterday") < 0 ? 'yesterday, '+params["TIME_FORMAT"] : params["TIME_FORMAT"])],
				["", params["DATE_TIME_FORMAT"]]
			];

			var authorStyle = "";
			if (!BX.Type.isUndefined(res["AUTHOR"]["TYPE"]))
			{
				if (res["AUTHOR"]["TYPE"] === "EMAIL")
				{
					authorStyle = " feed-com-name-email";
				}
				else if (res["AUTHOR"]["TYPE"] === "EXTRANET")
				{
					authorStyle = " feed-com-name-extranet";
				}
			}
			else if (res["AUTHOR"]["IS_EXTRANET"] == "Y")
			{
				authorStyle = " feed-com-name-extranet";
			}
			var commentText = res["POST_MESSAGE_TEXT"].replace(/\001/gi, "").replace(/#/gi, "\001");
			res.AUX_LIVE_PARAMS = (BX.type.isPlainObject(res.AUX_LIVE_PARAMS) ? res.AUX_LIVE_PARAMS : {});

			if (
				!!res.AUX
				&& (
					BX.util.in_array(res.AUX, ['createentity', 'createtask', 'fileversion'])
					|| (res.AUX === 'TASKINFO' && BX.type.isNotEmptyObject(res.AUX_LIVE_PARAMS))
				)
			)
			{
				commentText = BX.CommentAux.getLiveText(res.AUX, res.AUX_LIVE_PARAMS);
			}

			replacement = {
				"ID" : res["ID"],
				"FULL_ID" : res["FULL_ID"].join("-"),
				"CONTENT_ID" : (
					res['RATING'] && res['RATING']['ENTITY_TYPE_ID'] && res['RATING']['ENTITY_ID']
						? res['RATING']['ENTITY_TYPE_ID'] + '-' + res['RATING']['ENTITY_ID']
						: ''
				),
				"ENTITY_XML_ID" : res["ENTITY_XML_ID"],
				"EXEMPLAR_ID" : params["EXEMPLAR_ID"],
				"NEW" : res["NEW"] == "Y" ? "new" : "old",
				"APPROVED" : (res["APPROVED"] != "Y" ? "hidden" : "approved"),
				"DATE" : BX.date.format(
					dateFormat,
					timestamp,
					parseInt(Date.now()/1000) + parseInt(BX.message("USER_TZ_OFFSET")) + parseInt(BX.message("SERVER_TZ_OFFSET")),
					true
				),
				"TEXT" : commentText,
				"CLASSNAME" : (res["CLASSNAME"] ? " " + res["CLASSNAME"] : "") + (BX.type.isNotEmptyString(params["CLASSNAME"]) ? ' ' + params["CLASSNAME"] : ''),
				"VIEW_URL" : params["VIEW_URL"].replace("#ID#", res["ID"]).replace("#id#", res["ID"]),
				"VIEW_SHOW" : (params["VIEW_URL"] !== "" ? "Y" : "N"),
				"EDIT_URL" : params["EDIT_URL"].replace("#ID#", res["ID"]).replace("#id#", res["ID"]),
				"EDIT_SHOW" : (
					(
						!res.AUX
						|| res.AUX.length <= 0
					)
					&& (
						params["RIGHTS"]["EDIT"] == "Y"
						|| params["RIGHTS"]["EDIT"] == "ALL"
						|| (
							params["RIGHTS"]["EDIT"] == "OWN"
							&& BX.message("USER_ID") == res["AUTHOR"]["ID"]
						)
					)
						? "Y"
						: "N"
				),
				"MODERATE_URL" : params["MODERATE_URL"].replace("#ID#", res["ID"]).replace("#id#", res["ID"]),
				"MODERATE_SHOW" : (params["RIGHTS"]["MODERATE"] == "Y" || params["RIGHTS"]["MODERATE"] == "ALL" ||
					params["RIGHTS"]["MODERATE"] == "OWN" && BX.message("USER_ID") == res["AUTHOR"]["ID"] ? "Y" : "N"),
				"DELETE_URL" : params["DELETE_URL"].replace("#ID#", res["ID"]).replace("#id#", res["ID"]),
				"DELETE_SHOW" : (
					(!res["CAN_DELETE"] || res["CAN_DELETE"] === 'Y')
					&& (
						params["RIGHTS"]["DELETE"] == "Y"
						|| params["RIGHTS"]["DELETE"] == "ALL"
						|| params["RIGHTS"]["DELETE"] == "OWN" && BX.message("USER_ID") == res["AUTHOR"]["ID"] ? "Y" : "N")
					),
				"CREATETASK_SHOW" : (
					(!res.AUX || res.AUX.length <= 0)
					&& params["RIGHTS"]["CREATETASK"] == "Y"
						? "Y"
						: "N"
				),
				"CREATESUBTASK_SHOW" : (
					(!res.AUX || res.AUX.length <= 0)
					&& BX.type.isNotEmptyString(params.RIGHTS.CREATESUBTASK)
					&& params.RIGHTS.CREATESUBTASK === "Y"
						? 'Y'
						: 'N'
				),
				"BEFORE_HEADER" : res["BEFORE_HEADER"],
				"BEFORE_ACTIONS" : res["BEFORE_ACTIONS"],
				"AFTER_ACTIONS" : res["AFTER_ACTIONS"],
				"AFTER_HEADER" : res["AFTER_HEADER"],
				"BEFORE" : res["BEFORE"],
				"AFTER" : res["AFTER"],
				"BEFORE_RECORD" : res["BEFORE_RECORD"],
				"AFTER_RECORD" : res["AFTER_RECORD"],
				"AUTHOR_ID" : res["AUTHOR"]["ID"],
				"AUTHOR_AVATAR_IS" : (!!res["AUTHOR"]["AVATAR"] ? "Y" : "N"),
				"AUTHOR_AVATAR" : (!!res["AUTHOR"]["AVATAR"] ? encodeURI(res["AUTHOR"]["AVATAR"]) : '/bitrix/images/1.gif'),
				"AUTHOR_AVATAR_BG" : (!!res["AUTHOR"]["AVATAR"] ? "background-image:url('" + encodeURI(res["AUTHOR"]["AVATAR"]) + "')" : ""),
				"AUTHOR_URL" : params["AUTHOR_URL"].
					replace("#ID#", res["ID"]).
					replace("#id#", res["ID"]).
					replace("#USER_ID#", res["AUTHOR"]["ID"]).
					replace("#user_id#", res["AUTHOR"]["ID"]) + (
						!BX.Type.isUndefined(res["AUTHOR"]["EXTERNAL_AUTH_ID"])
						&& res["AUTHOR"]["EXTERNAL_AUTH_ID"] === "email"
						&& !BX.Type.isUndefined(params["AUTHOR_URL_PARAMS"])
							? (params["AUTHOR_URL"].indexOf("?") >= 0 ? '&' : '?') + 'entityType=' + params["AUTHOR_URL_PARAMS"]["entityType"] + '&entityId=' + params["AUTHOR_URL_PARAMS"]["entityId"]
							: ''
					),
				"AUTHOR_NAME" : BX.formatName(res["AUTHOR"], params["NAME_TEMPLATE"], params["SHOW_LOGIN"]),
				"AUTHOR_EXTRANET_STYLE" : authorStyle,
				"VOTE_ID" : (res["RATING"] && res["RATING"]["VOTE_ID"] ? res["RATING"]["VOTE_ID"] : ""),
				"AUTHOR_PERSONAL_GENDER" : (BX.type.isNotEmptyString(res["AUTHOR"]["PERSONAL_GENDER"]) ? res["AUTHOR"]["PERSONAL_GENDER"] : ""),
				"AUTHOR_TOOLTIP_PARAMS": (!BX.Type.isUndefined(res["AUTHOR_TOOLTIP_PARAMS"]) ? res["AUTHOR_TOOLTIP_PARAMS"] : '{}'),
				"background:url('') no-repeat center;" : "",
				"LIKE_REACT" : (!!res["LIKE_REACT"] ? res["LIKE_REACT"] : ""),
				"RATING_NONEMPTY_CLASS" : (res["RATING"] && res["RATING"]["TOTAL_VOTES"] ? "comment-block-rating-nonempty" : ""),
				"POST_ENTITY_TYPE" : (!!params["POST_CONTENT_TYPE_ID"] ? params["POST_CONTENT_TYPE_ID"] : ""),
				"COMMENT_ENTITY_TYPE" : (!!params["COMMENT_CONTENT_TYPE_ID"] ? params["COMMENT_CONTENT_TYPE_ID"] : ""),
				"MOBILE_HINTS" : ""
			};
		}
		else
		{
			for (ii in replacement)
			{
				if (replacement.hasOwnProperty(ii))
				{
					replacement[ii] = (!!data[ii] ? data[ii] : replacement[ii]);
				}
			}
		}

		for (ii in replacement)
		{
			if (replacement.hasOwnProperty(ii))
			{
				replacement[ii] = (!!replacement[ii] ? replacement[ii] : "");
			}
		}
		replacement["SHOW_POST_FORM"] = (BX("record-" + replacement["ENTITY_XML_ID"] + '-0-placeholder') ? "Y" : "N");
		for (var ij in replacement)
		{
			if (replacement.hasOwnProperty(ij))
			{
				txt = txt.replace(new RegExp('#' + ij + '#', "g"), function() { return replacement[this]; }.bind(ij) );
			}
		}
		return txt.replace("background:url('') no-repeat center;", "").replace(/\001/gi, "#");
	};

	window['fcPull'] = function(ENTITY_XML_ID, data) {
		BX.ajax({
			url: '/bitrix/components/bitrix/main.post.list/templates/.default/component_epilog.php',
			method: "POST",
			data: {
				AJAX_POST:  "Y",
				ENTITY_XML_ID: ENTITY_XML_ID,
				MODE: "PUSH&PULL",
				sessid: BX.bitrix_sessid(),
				DATA: data,
			}
		});
	};

	var MPLQuote = function() {
		this.closeByEsc = true;
		this.autoHide = true;
		this.autoHideTimeout = 5000;

		this.node = document.createElement("A");
		BX.adjust(this.node, {
			style: {
				zIndex: BX.PopupWindow.getOption("popupZindex") + 1,
				position: "absolute",
				display: "none",
				top: "0px",
				left: "0px",
			},
			attrs : {
				className: "mpl-quote-block",
				href: "#",
			},
			events: {
				click: this.fire.bind(this),
			}
		});

		this.checkEsc = this.checkEsc.bind(this);
		this.hide = this.hide.bind(this);
		document.body.appendChild(this.node);
		BX.ZIndexManager.register(this.node);
	};
	MPLQuote.prototype = {
		show : function(e){
			var pos = this.getPosition(this.node, e);
			BX.adjust(this.node, {style : {top : pos.y + "px", left : pos.x + "px", display : "block"}});
			BX.addClass(this.node, "mpl-quote-block-show");
			BX.ZIndexManager.bringToFront(this.node);

			if (this.closeByEsc && this.closeByEscBound !== true)
			{
				this.closeByEscBound = true;
				BX.bind(document, "keyup", this.checkEsc);
			}

			if (this.autoHide && this.autoHideBound !== true)
			{
				this.autoHideBound = true;
				setTimeout(
					function() {
						BX.bind(document, "click", this.hide);
					}.bind(this), 10
				);
			}

			if (this.autoHideTimeoutPointer > 0 )
			{
				clearTimeout(this.autoHideTimeoutPointer);
				this.autoHideTimeoutPointer = 0;
			}
			if (this.autoHideTimeout > 0 && this.autoHideTimeoutBound !== true)
			{
				this.autoHideTimeoutBound = true;
				this.autoHideTimeoutPointer = setTimeout(this.hide, this.autoHideTimeout);
			}
		},
		fire: function(e) {

			e.preventDefault();

			if (!this.isShown())
			{
				return;
			}

			if (e && !(BX.getEventButton(e) & BX.MSLEFT))
			{
				return;
			}

			this.cancelBubble(e);

			this.node.style.display = "none";

			BX.onCustomEvent(this, "onQuote", [e, this]);

			setTimeout(this.hide, 50);
			return false;
		},
		hide: function() {
			BX.unbind(document, "keyup", this.checkEsc);
			this.closeByEscBound = false;
			BX.unbind(document, "click", this.hide);
			this.autoHideBound = false;

			if (this.autoHideTimeoutPointer > 0 )
			{
				clearTimeout(this.autoHideTimeoutPointer);
			}

			this.autoHideTimeoutPointer = 0;
			this.autoHideTimeoutBound = false;

			BX.onCustomEvent(this, "onHide", [this]);

			BX.remove(this.node);
		},
		getPosition: function(node, e) {
			var nodePos;
			if (e.pageX == null)
			{
				var doc = document.documentElement, body = document.body;
				var x = e.clientX + (doc && doc.scrollLeft || body && body.scrollLeft || 0) - (doc.clientLeft || 0);
				var y = e.clientY + (doc && doc.scrollTop || body && body.scrollTop || 0) - (doc.clientTop || 0);
				nodePos = {x: x, y: y};
			}
			else
			{
				nodePos = {x: e.pageX, y: e.pageY};
			}
			return {
				x: (nodePos.x + 5),
				y: (nodePos.y - 16),
			};
		},
		isShown: function() {
			return (this.node.style.display === "block");
		},
		cancelBubble: function(event) {
			if (!event)
			{
				event = window.event;
			}

			if (event.stopPropagation)
			{
				event.stopPropagation();
			}
			else
			{
				event.cancelBubble = true;
			}
		},
		checkEsc : function(event) {
			event = event || window.event;
			if (event.keyCode == 27)
			{
				this.hide(event);
			}
		}
	};
	//region Services functions
	var checkEntitiesActuality = function(xmlId) {
		var lists = repo.listById.values();
		var list = lists.next();
		while (list.done !== true) {
			if (
				list.value.getXmlId() === xmlId
				&& !document.body.contains(list.value.node.main)
			)
			{
				BX.onCustomEvent(window, "OnUCInitialized", [list.value.getId()]);
			}
			list = lists.next();
		}
	};
	var getActiveEntitiesByXmlId = function(xmlId) {
		checkEntitiesActuality(xmlId);
		var lists = repo.listById.values();
		var list = lists.next();
		var entities = new Map();
		while (list.done !== true) {
			if (list.value.getXmlId() === xmlId)
			{
				entities.set(list.value.getId(), list.value);
			}
			list = lists.next();
		}
		return entities;
	};
	//endregion
	/**
	 * This function is used for binding to the post or calendar event to quote text.
	 * @param e
	 * @param node
	 * @param xmlId
	 * @param author_id
	 * @returns {boolean}
	 */
	window.mplCheckForQuote = function(e, node, xmlId, author_id) {
		e = (document.all ? window.event : e);
		var text = "", range, author = null;

		if (window.getSelection)
		{
			range = window.getSelection();
			text = range.toString();
		}
		else if (document.selection)
		{
			range = document.selection;
			text = range.createRange().text;
		}

		if (text.length <= 0)
		{
			return;
		}

		var endParent = BX.findParent(range.focusNode, {
				tagName : node.tagName,
				className : node.className
			}, node),
			startParent = BX.findParent(range.anchorNode, {
				tagName : node.tagName,
				className : node.className
			}, node);

		if (endParent !== startParent || endParent !== node)
		{
			return;
		}
		var entities = getActiveEntitiesByXmlId(xmlId);
		if (entities.size <= 0)
		{
			return;
		}

		if (author_id && BX(author_id, true))
		{
			var tmp = BX(author_id, true);
			if (tmp && tmp.hasAttribute("bx-post-author-id"))
			{
				author = {
					id : parseInt(tmp.getAttribute("bx-post-author-id")),
					gender : tmp.getAttribute("bx-post-author-gender"),
					name : tmp.innerHTML
				}
			}
		}
		var closestEntity = null;
		if (node.__boundXmlCheckQuote === true)
		{
			closestEntity = repo.listById.get(node.__boundXmlEntityId) || null;
		}

		if (closestEntity === null)
		{
			node.__boundXmlCheckQuote = true;

			var parent = node;
			while (parent)
			{
				entities.forEach(function(value, key) {
					if (closestEntity === null && parent.contains(value.node.main))
					{
						closestEntity = value;
						return true;
					}
				});
				if (closestEntity !== null)
				{
					break;
				}
				parent = parent.parentNode;
			}
			if (closestEntity === null)
			{
				closestEntity = entities.values().next().value;
			}
			node.__boundXmlEntityId = closestEntity.getId();
			BX.addCustomEvent(window, "OnUCHasBeenDestroyed", function(xmlId, entity) {
				if (entity.getId() === node.__boundXmlEntityId)
				{
					delete node.__boundXmlEntityId;
					delete node.__boundXmlCheckQuote;
				}
			});
		}
		if (closestEntity !== null)
		{
			BX.onCustomEvent(closestEntity.eventNode, "onQuote", [e, {text : text, author : author}]);
		}
	};
	window.mplReplaceUserPath = function(text) {
		if (!BX.Type.isStringFilled(text))
		{
			return '';
		}

		if (BX("MPL_IS_EXTRANET_SITE") === "Y")
		{
			text = text.replace('/company/personal/user/', '/extranet/contacts/personal/user/');
		}
		else
		{
			text = text.replace('/extranet/contacts/personal/user/', '/company/personal/user/');
		}

		text = text.replace(
			new RegExp("[\\w\/]*\/mobile\/users\/\\?user_id=(\\d+)", "igm"),
			(
				BX("MPL_IS_EXTRANET_SITE") == "Y"
					? '/extranet/contacts/personal/user/$1/'
					: '/company/personal/user/$1/'
			)
		);

		return text;
	};
	BX.addCustomEvent(window, "BX.Livefeed:recalculateComments", function(params) {

		if (
			!BX.type.isPlainObject(params)
			|| !BX.type.isDomNode(params.rootNode)
		)
		{
			return;
		}

		var commentBlocksList = params.rootNode.querySelectorAll('.feed-comments-block');
		var commentThreadXmlId = null;

		for (var i = 0; i < commentBlocksList.length; i++)
		{
			commentThreadXmlId = commentBlocksList[i].getAttribute('data-bx-comments-entity-xml-id');
			if (BX.type.isNotEmptyString(commentThreadXmlId))
			{
				BX.onCustomEvent(window, "OnUCCommentRecalculate", [commentThreadXmlId]);
			}
		}
	});
	BX.addCustomEvent(window, 'BX.Forum.Spoiler:toggle', function(params) {
		if (!params.node)
		{
			return;
		}
		var outerBlock = BX.findParent(params.node, { attrs : {"bx-mpl-block" : "main"} });
		if (outerBlock)
		{
			BX.onCustomEvent(outerBlock, "onForumSpoilerToggle", [outerBlock.getAttribute("bx-mpl-entity-id")])
		}
	});
//region Reading messages
	var ScreenSpy = function() {
		this.timeoutSec = 2000;
		this.check = this.check.bind(this);
		this.change = this.change.bind(this);
		this.scroll = this.scroll.bind(this);
		this.nodes = new Map();
		this.timeout = 0;
		this.ready = true;
		this.window = BX.GetWindowInnerSize();
		this.screen = {
			top: this.window.scrollTop,
			bottom: (this.window.scrollTop + this.window.innerHeight),
		};
		this.watchDimensionNodes = new WeakMap();
	};
	ScreenSpy.prototype = {
		watchNode : function(node) {
			if (!this.watchDimensionNodes.has(node))
			{
				this.watchDimensionNodes.set(node, false);
				BX.bind(node, "click", this.check);
			}
		},
		set : function (id, node) {
			this.nodes.set(id, node);
			this.start();
		},
		unset : function (id) {
			this.nodes.delete(id);
			if (this.nodes.size <= 0)
			{
				this.stop();
			}
		},
		start : function() {
			if (this.ready !== true)
			{
				return;
			}
			this.ready = false;
			BX.bind(window, "resize", this.change);
			BX.bind(window, "scroll", this.scroll);
			this.scroll();
		},
		stop : function() {
			if (this.timeout > 0)
			{
				clearTimeout(this.timeout);
			}
			this.timeout = 0;
			BX.unbind(window, "resize", this.change);
			BX.unbind(window, "scroll", this.scroll);
			this.ready = true;
		},
		check : function() {
			this.timeout = 0;
			var scroll = BX.GetWindowScrollPos();
			if (this.screen.bottom > scroll.scrollTop)
			{
				var position = {
					top: scroll.scrollTop,
					bottom: this.screen.bottom
				};
				BX.onCustomEvent(this, "onRead", [position]);
			}
			if (this.screen.top !== scroll.scrollTop)
			{
				this.scroll();
			}
		},
		change : function() {
			this.window = BX.GetWindowInnerSize();
		},
		scroll : function() {
			if (this.timeout <= 0)
			{
				var scroll = BX.GetWindowScrollPos();
				this.screen = {
					top : scroll.scrollTop,
					bottom: (scroll.scrollTop + this.window.innerHeight)
				};
				this.timeout = setTimeout(this.check, this.timeoutSec);
			}
		}
	};
	var scrSpy = new ScreenSpy();
//endregion
	BX.ready(function() {
		//region for pull events
		BX.addCustomEvent(window, "onPullEvent-unicomments", function(command, params) {
			if (
				(
					params['AUX']
					&& !BX.util.in_array(params['AUX'].toLowerCase(), BX.CommentAux.getLiveTypesList())
				)
				|| getActiveEntitiesByXmlId(params["ENTITY_XML_ID"]).size <= 0
			)
			{
				return;
			}

			if (command === "comment")
			{
				if (params["NEED_REQUEST"] === "Y")
				{
					if (params["URL"]["LINK"].indexOf('#GROUPS_PATH#') >= 0 && !!BX.message("MPL_WORKGROUPS_PATH"))
					{
						params["URL"]["LINK"] = params["URL"]["LINK"].replace('#GROUPS_PATH#', BX.message("MPL_WORKGROUPS_PATH"));
					}

					var data = BX.ajax.prepareData({
						AJAX_POST : "Y",
						ENTITY_XML_ID : params["ENTITY_XML_ID"],
						MODE : "RECORD",
						FILTER : {"ID" : params["ID"]},
						sessid : BX.bitrix_sessid()
					});
					var url = params["URL"]["LINK"];
					url = (url.indexOf('#') !== -1 ? url.substr(0, url.indexOf('#')) : url);

					BX.ajax({
						url: (url + (url.indexOf('?') !== -1 ? "&" : "?") + data),
						method: "GET",
						dataType: "json",
						data: "",
						onsuccess: function(data) {
							BX.onCustomEvent(window, "OnUCCommentWasPulled", [[params["ENTITY_XML_ID"], params["ID"]], data, params]);
						}
					});
				}
				else if (params["ACTION"] === "DELETE")
				{
					BX.onCustomEvent(window, "OnUCommentWasDeleted", [params["ENTITY_XML_ID"], [params["ENTITY_XML_ID"], params["ID"]], params]);
					BX.onCustomEvent(window, "OnUCFeedChanged", [params["ID"]]);
				}
				else if (params["ACTION"] === "HIDE")
				{
					BX.onCustomEvent(window, "OnUCommentWasHidden", [params["ENTITY_XML_ID"], [params["ENTITY_XML_ID"], params["ID"]], params]);
					BX.onCustomEvent(window, "OnUCFeedChanged", [params["ID"]]);
				}
				else
				{
					if (params["ACTION"] === "REPLY")
					{
						params["NEW"] = !params["AUTHOR"] || params["AUTHOR"]["ID"] != BX.message("USER_ID") ? "Y" : "N";
					}
					BX.onCustomEvent(window, "OnUCCommentWasPulled", [[params["ENTITY_XML_ID"], params["ID"]], {messageFields : params}, params]);
				}
			}
			else if (command === "answer" && Number(params["USER_ID"]) !== Number(BX.message("USER_ID")))
			{
				BX.onCustomEvent(window, "OnUCUsersAreWriting", [params["ENTITY_XML_ID"], params["USER_ID"], params["NAME"], params["AVATAR"]]);
			}
		});
		//endregion
		BX.addCustomEvent(window, "OnUCUserReply", function(xmlId, authorId, authorName) {
			var entities = getActiveEntitiesByXmlId(xmlId);
			if (entities.size <= 0)
			{
				return;
			}
			entities.values().next().value.reply({id : authorId, name : authorName});
		});
	});
	BX.onCustomEvent("main.post.list/default", []);

	class MobileButton
	{
		constructor(options)
		{
			const { containerId } = options;
			const container = document.getElementById(`${containerId}`);
			if (!container)
			{
				return;
			}

			const mobileButtons = Array.from(container.querySelectorAll('.feed__mobile_btn'));
			this.onButtonClickHandler = this.handleButtonClick.bind(this);
			mobileButtons.forEach(mobileButton => {
				mobileButton.addEventListener('click', this.onButtonClickHandler);
			});
		}

		handleButtonClick(event)
		{
			const popup = new BX.PopupWindow({
				bindElement: event,
				content: BX.Tag.render`
					<div class="feed__mobile__popup_content">
						<div class="feed__mobile__popup_content__text">${BX.message('MPL_MOBILE_HINTS')}</div>
						<span onclick="${this.handleLinkClick.bind(this)}" class="feed__mobile__popup_content__link">${
					BX.message('MPL_MOBILE_HINTS_DETAILS')
				}</span>
					</div>`,
				bindOptions: {
					position: 'top',
				},
				darkMode: true,
				autoHide: true,
				closeByEsc: true,
				animation: 'fading',
			});
			popup.show();
		}

		handleLinkClick(event)
		{
			BX.Runtime.loadExtension('ui.qrauthorization').then(exports => {
				const { QrAuthorization } = exports;
				const qrAuthPopup = new QrAuthorization({
					title: {
						text: BX.message('MPL_MOBILE_POPUP_TITLE'),
						size: 'sm'
					},
					bottomText: {
						text: BX.message('MPL_MOBILE_POPUP_BOTTOM_TEXT'),
						size: 'sm'
					},
					popupParam: {
						overlay: true
					}
				});
				qrAuthPopup.show();
			});
		}
	}
	BX.namespace('BX.Main.PostList');
	BX.Main.PostList.MobileButton = MobileButton;
})();
