;(function(){
	window["UC"] = (window["UC"] || {});
	if (window["FCList"])
		return;

	var safeEditing = true,
		safeEditingCurrentObj = null,
		quoteData = null,
		repo = {commentExemplarId : {}};

	window.FCList = function (params, add) {
		this.CID = params["CID"];
		this.ENTITY_XML_ID = params["ENTITY_XML_ID"];
		this.container = params["container"];
		this.nav = params["nav"];
		this.mid = params["mid"];
		this.order = params["order"];
		this.status = "ready";
		this.msg = (!!this.nav ? this.nav.innerHTML : '');
		this.params = (add || {});
		this.pullNewRecords = {};
		this.rights = params["rights"];
		this.DATE_TIME_FORMAT = (this.params["DATE_TIME_FORMAT"] || null);
		this.comments = {};
		this.bindEvents = [
			[
				this.nav, "click", BX.proxy(function (e) {
					BX.eventCancelBubble(e);
					BX.PreventDefault(e);
					this.get();
					return false;
				}, this)
			]
		];

		this.exemplarId = BX.util.getRandomString(20);
		this.windowEvents = {
			OnUCUserIsWriting : BX.delegate(function(ENTITY_XML_ID, id, commentExemplarId) {
				if (this.ENTITY_XML_ID == ENTITY_XML_ID) {
					BX.ajax({
						url: this.url.activity,
						method: 'POST',
						data: {
							AJAX_POST : "Y",
							ENTITY_XML_ID : this.ENTITY_XML_ID,
							COMMENT_EXEMPLAR_ID : commentExemplarId,
							MODE : "PUSH&PULL",
							sessid : BX.bitrix_sessid(),
							sign : params["sign"],
							"PATH_TO_USER" : this.params["PATH_TO_USER"],
							"AVATAR_SIZE" : this.params["AVATAR_SIZE"],
							"NAME_TEMPLATE" : this.params["NAME_TEMPLATE"],
							"SHOW_LOGIN" : this.params["SHOW_LOGIN"]
						}
					});
				}
			}, this),
			OnUCAfterRecordAdd : BX.delegate(function(ENTITY_XML_ID, data) {
				if (this.ENTITY_XML_ID == ENTITY_XML_ID) {
					this.add(data["messageId"], data, true, "simple");
				}
			}, this),
			OnUCFormSubmit : BX.delegate(function(ENTITY_XML_ID, ENTITY_ID, obj, data) {
				if (this.ENTITY_XML_ID == ENTITY_XML_ID) {
					data["EXEMPLAR_ID"] = this.exemplarId;
					this.pullNewRecords[ENTITY_XML_ID + '-0'] = "busy";
				}
			}, this),
			OnUCFormResponse : BX.delegate(function(ENTITY_XML_ID, ENTITY_ID/*, obj, data*/) {
				if (this.ENTITY_XML_ID == ENTITY_XML_ID)
				{
					this.pullNewRecords[ENTITY_XML_ID + '-0'] = "ready";
					this.pullNewRecords[ENTITY_XML_ID + '-' + ENTITY_ID] = "done";
				}
			}, this),
			OnUCUserQuote : BX.delegate(function(ENTITY_XML_ID) {
				if (this.ENTITY_XML_ID == ENTITY_XML_ID && this.quote && this.quote.popup)
				{
					this.quote.popup.hide();
				}
			}, this),
			'onPullEvent-unicomments' : BX.delegate(function(command, params) {
				if (
					this.ENTITY_XML_ID == params["ENTITY_XML_ID"]
					&& (
						(params["USER_ID"] + '') != (BX.message("USER_ID") + '')
						||
						( params["EXEMPLAR_ID"] && params["EXEMPLAR_ID"] != this.exemplarId )
						|| (
							typeof params["AUX"] != 'undefined'
							&& BX.util.in_array(params["AUX"], ['createtask', 'fileversion'])
						)
					)
				)
				{
					if (command === 'comment' && params["ID"])
					{
						if (params["COMMENT_EXEMPLAR_ID"])
							repo.commentExemplarId[params["ENTITY_XML_ID"] + '_' + params["COMMENT_EXEMPLAR_ID"]] = true;
						this.pullNewRecord(params);
					}
					else if (command === 'answer' &&
						((params["USER_ID"] + '') !== (BX.message("USER_ID") + '')) &&
						(!params["COMMENT_EXEMPLAR_ID"] || repo.commentExemplarId[params["ENTITY_XML_ID"] + '_' + params["COMMENT_EXEMPLAR_ID"]] !== true)
					)
					{
						this.pullNewAuthor(params["USER_ID"], params["NAME"], params["AVATAR"]);
					}
				}
			}, this)
		};

		if (this.params && this.params["NOTIFY_TAG"] && !!this.params["NOTIFY_TEXT"] && !!window["UC"]["Informer"])
		{
			this.windowEvents['OnUCCommentWasPulled'] = BX.delegate(function(id, data)
			{
				if (this.ENTITY_XML_ID == id[0]) { window["UC"]["Informer"].check(id, data, this.params["NOTIFY_TAG"], this.params["NOTIFY_TEXT"]) }
			}, this);
			window["UC"]["InformerTags"][this.params["NOTIFY_TAG"]] = (window["UC"]["InformerTags"][this.params["NOTIFY_TAG"]] || []);
		}
		var ii;
		for (ii = 0; ii < this.bindEvents.length; ii++)
		{
			BX.bind(this.bindEvents[ii][0], this.bindEvents[ii][1], this.bindEvents[ii][2]);
		}
		for (ii in this.windowEvents)
		{
			if (this.windowEvents.hasOwnProperty(ii))
			{
				BX.addCustomEvent(window, ii, this.windowEvents[ii]);
			}
		}

		var tw = /%23com(\d+)/gi.exec(location.href),
			com = parseInt(location.hash && location.hash.indexOf("#com") >= 0 ?
				location.hash.replace("#com", "") : (tw ? tw[1] : 0));
		if (com > 0)
			this.checkHash(com);

		if (this.params["BIND_VIEWER"] == "Y" && BX["viewElementBind"])
		{
			BX.viewElementBind(
				BX('record-' + this.ENTITY_XML_ID + '-new').parentNode, {},
				function(node){
					return BX.type.isElementNode(node) && (node.getAttribute('data-bx-viewer') || node.getAttribute('data-bx-image'));
				}
			);
		}

		this.init(params);

		if (this.mid > 0 && BX('record-' + [this.ENTITY_XML_ID, this.mid].join('-') + '-cover'))
		{
			var n = BX('record-' + [this.ENTITY_XML_ID, this.mid].join('-') + '-cover').parentNode.firstChild,
				r = new RegExp("record-(" + this.ENTITY_XML_ID + ")-([0-9]+)-cover", "gi");
			while (BX(n))
			{
				if (n["hasAttribute"] && n.hasAttribute("id") && r.test(n.getAttribute("id")))
				{
					(n.getAttribute("id") + "").replace(r, function(str, ENTITY_XML_ID, mid) {
						traceForReading([ENTITY_XML_ID, mid]);
					});
				}
				n = n.nextSibling;
			}
		}
		if (repo[this.ENTITY_XML_ID])
			repo[this.ENTITY_XML_ID].destroy();
		repo[this.ENTITY_XML_ID] = this;

		BX.ready(function() {
			setTimeout(BX.delegate(function() {
				BX.onCustomEvent(window, "OnUCHasBeenInitialized", [this.ENTITY_XML_ID, this]);
			}, this), 100)
		});

		return this;
	};
	window.FCList.prototype = {
		url : {
			activity : '/bitrix/components/bitrix/main.post.list/activity.php'
		},
		destroy : function()
		{
			var ii, node;
			while ((node = this.bindEvents.pop()) && node)
			{
				BX.unbindAll(node[0]);
				node[0] = null;
				node[2] = null;
			}
			for (ii in this.windowEvents)
			{
				if (this.windowEvents.hasOwnProperty(ii))
				{
					BX.removeCustomEvent(window, ii, this.windowEvents[ii]);
					this.windowEvents[ii] = null;
				}
			}
			this.windowEvents = null;
			delete repo[this.ENTITY_XML_ID];
			BX.onCustomEvent(window, "OnUCHasBeenDestroyed", [this.ENTITY_XML_ID, this]);
		},
		init : function()
		{
			if (this.params["SHOW_POST_FORM"] == "Y")
			{
				this.quote.show = BX.delegate(function(e, params) {
						setTimeout(BX.delegate( function() { this.quoteShow(e, params); }, this ), 50);
					}, this
				);
				var res = BX('record-' + this.ENTITY_XML_ID + '-new'),
					nodes = BX.findChildren(res.parentNode, {"tagName" : "DIV", "className" : "feed-com-block-cover"}, false);
				nodes = (!!nodes ? nodes : []);
				nodes.push(res);
				if (!!this.container)
					nodes.push(this.container);

				for (var ii = 0; ii < nodes.length; ii++)
				{
					BX.bind(nodes[ii], "mouseup", this.quote.show);
				}
				// dnd
				var dnd = BX('record-' + this.ENTITY_XML_ID + '-switcher');
				if (dnd && !dnd.bxDndIsBound)
				{
					dnd.bxDndIsBound = "Y";
					BX.bind(dnd, "dragenter", BX.delegate(this.reply, this));
				}
				BX.addCustomEvent(window, "onQuote"+this.ENTITY_XML_ID, this.quote.show);
			} // only for small informer at the left bottom screens part
		},
		quote : {
			show : BX.DoNothing(),
			popup : null
		},
		quoteCheck : function() {
			var text = '', range, author = null;
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
			if (text != "")
			{
				var parent = BX('record-' + this.ENTITY_XML_ID + '-new'),
					endParent = BX.findParent(range.focusNode, {"tagName" : "DIV", "className" : "feed-com-block-cover"}, parent.parentNode),
					startParent = BX.findParent(range.anchorNode, {"tagName" : "DIV", "className" : "feed-com-block-cover"}, parent.parentNode);
				if (endParent != startParent || BX(endParent) && !endParent.hasAttribute("id"))
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
			if (text == "")
			{
				if (!!this.quote.popup)
					this.quote.popup.hide();
				return false;
			}
			return {text : text, author : author};
		},
		quoteShow : function(e, params) {
			params = (params || this.quoteCheck());

			if (!params || !params['text'])
			{
				quoteData = null;
				return;
			}
			quoteData = params;

			if (this.quote.popup == null)
			{
				this.quote.popup = new MPLQuote({
					id : this.ENTITY_XML_ID,
					closeByEsc : true,
					autoHide : true,
					autoHideTimeout : 2500,
					events : {
						click : BX.delegate(function(e) {
							BX.PreventDefault(e);
							BX.eventCancelBubble(e);
							safeEditingCurrentObj = safeEditing;
							BX.onCustomEvent(window, "OnUCUserQuote", [this.ENTITY_XML_ID, params['author'], params['text'], safeEditingCurrentObj]);
							this.quote.popup.hide();
							return false;
						}, this)
					},
					classEvents : {
						onQuoteHide : BX.proxy(function() { quoteData = null; this.quote.popup = null; }, this)
					}
				});
			}
			this.quote.popup.show(e);
		},
		display : function(status, startHeight)
		{
			var fxStart = 0, fxFinish = 0,
				time = 0,
				el = this.container;
			status = (status == "hide" ? "hide" : "show");
			if (status == "hide")
			{
				fxStart = parseInt(this.container.offsetHeight);
				time = fxStart / 2000;

				time = (time < 0.3 ? 0.3 : (time > 0.5 ? 0.5 : time));
				el.style.overflow = 'hidden';

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
						el.style.cssText = '';
						el.style.display = "none";
						BX.onCustomEvent(this, 'OnUCListWasHidden', [this, [], el]);
					}, this)
				})).animate();
			}
			else
			{
				fxStart = parseInt(startHeight || 20);

				el.style.display = "block";
				el.style.overflow = 'hidden';
				el.style.maxHeight = fxStart;

				fxFinish = parseInt(this.container.offsetHeight);
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
						el.style.cssText = '';
						el.style.maxHeight = 'none';
						BX.onCustomEvent(this, 'OnUCListWasShown', [this, [], el]);
					}, this)
				})).animate();
			}
		},
		get : function()
		{
			if (this.status == "done")
			{
				if (this.nav.getAttribute("bx-visibility-status") == "visible") {
					this.display("hide");
					BX.adjust(this.nav, {attrs : {"bx-visibility-status" : "none"}, html : this.msg});
				} else {
					this.display("show");
					BX.adjust(this.nav, {attrs : {"bx-visibility-status" : "visible"}, html : BX.message("BLOG_C_HIDE")});
				}
			}
			else if (this.status == "ready")
			{
				this.send();
			}
			return false;
		},
		send : function() {
			this.status = "busy";
			BX.addClass(this.nav, "feed-com-all-hover");
			var data = BX.ajax.prepareData({
					AJAX_POST : "Y",
					ENTITY_XML_ID : this.ENTITY_XML_ID,
					MODE : "LIST",
					FILTER : (this.order == "ASC" ? {">ID" : this.mid} : {"<ID" : this.mid}),
					sessid : BX.bitrix_sessid() } ),
				url = BX.util.htmlspecialcharsback(this.nav.getAttribute("href"));
				url = (url.indexOf('#') !== -1 ? url.substr(0, url.indexOf('#')) : url);
			var result = {url : url, data : data};
			BX.onCustomEvent(this, "OnUCListHasToBeEnlarged", [this, result]);
			url = result.url;
			data = result.data;
			BX.ajax({
				url: (url + (url.indexOf('?') !== -1 ? "&" : "?") + data),
				method: 'GET',
				dataType: 'json',
				data: '',
				onsuccess: BX.proxy(this.build, this),
				onfailure: BX.proxy(this.complete, this)
			});
		},
		build : function(data) {
			this.status = "ready";
			this.wait("hide");
			BX.removeClass(this.nav, "feed-com-all-hover");
			if (!!data && data["status"] == "success")
			{
				var res = (!!data["navigation"] ? BX.create('DIV', {html : data["navigation"]}) : null),
					ob = BX.processHTML(data["messageList"], false);

				var offsetHeight = this.container.offsetHeight,
					container = BX.create("DIV", {html : ob.HTML});
				if (this.order == "ASC" || !this.container.firstChild)
				{
					this.container.appendChild(container);
				}
				else
				{
					this.container.insertBefore(container, this.container.firstChild);
				}
				BX.onCustomEvent(window, "OnUCFeedChanged", [[this.ENTITY_XML_ID, this.mid]]);


				this.display('show', offsetHeight);
				if (!!res)
					res = res.firstChild;
				if (!!res)
					BX.adjust(this.nav, {attrs : {href : res.getAttribute("href")}, html : res.innerHTML});
				else {
					BX.adjust(this.nav, {
						attrs : {href : "#", "bx-visibility-status" : "visible"}, html : BX.message("BLOG_C_HIDE"),
						events : { click : function(e) {
							BX.eventCancelBubble(e);
							BX.PreventDefault(e);
							return false;
						}}});
					this.status = "done";
				}
				var cnt = 0,
					func = BX.delegate(function()
				{
					cnt++;
					if (cnt < 100)
					{
						if (this.container.childNodes.length > 0)
						{
							BX.ajax.processScripts(ob.SCRIPT);
							var first = container.firstChild,
								last = container.lastChild,
								min = 0,
								max = 0;
							if (first && first.hasAttribute("id"))
							{
								min = parseInt(first.getAttribute("id").
									replace("record-" + this.ENTITY_XML_ID + "-", "").
									replace("-cover", "")
								);
								min = (min > 0 ? min : 0);
							}
							if (last && last.hasAttribute("id"))
							{
								max = parseInt(last.getAttribute("id").
									replace("record-" + this.ENTITY_XML_ID + "-", "").
									replace("-cover", "")
								);
								max = (max > 0 ? max : 0);
							}
							if (min > max)
							{
								max = max + min;
								min = max - min;
								max = max - min;
							}
							container.setAttribute("bx-mpl-min", min + '');
							container.setAttribute("bx-mpl-max", max + '');
							container.setAttribute("bx-mpl-loaded", "Y");
							BX.onCustomEvent(this, "OnUCListWasBuilt", [this, data, container]);
						}
						else
							BX.defer(func)();
					}
				}, this);
				BX.defer(func)();
			}
		},
		complete : function() {
			this.status = "done";
			BX.removeClass(this.nav, "feed-com-all-hover");
			this.wait("hide");
		},
		wait : function(status) {
			status = (status == "show" ? "show" : "hide");
			return status;
		},
		reply : function(node) {
			safeEditingCurrentObj = safeEditing;
			if (BX.type.isElementNode(node))
				BX.onCustomEvent(window, 'OnUCUserReply', [this.ENTITY_XML_ID, node.getAttribute("bx-mpl-author-id"), node.getAttribute("bx-mpl-author-name"), safeEditingCurrentObj]);
			else
				BX.onCustomEvent(window, 'OnUCUserReply', [this.ENTITY_XML_ID, undefined, undefined, safeEditingCurrentObj]);
		},
		/*
		* @params array data Like an {
		errorMessage : "ERROR_MESSAGE",
		okMessage : "OK_MESSAGE",
		status : true,
		message : "html text",
		messageBBCode : "bbcode text",
		messageId : {ENTITY_XML_ID, RESULT},
		messageFields : {}}
		*/
		add : function(id, data, edit, animation) {
			if (!(!!data && !!id && parseInt(id[1]) > 0))
				return false;
			var
				container = BX('record-' + id.join('-') + '-cover'),
				html = (!!data["message"] ? data["message"] :  window.fcParseTemplate(
					{ messageFields : data["messageFields"] },
					{
						RIGHTS : this.rights,
						DATE_TIME_FORMAT : this.DATE_TIME_FORMAT,
						VIEW_URL : this.params.VIEW_URL,
						EDIT_URL : this.params.EDIT_URL,
						MODERATE_URL : this.params.MODERATE_URL,
						DELETE_URL : this.params.DELETE_URL,
						AUTHOR_URL : this.params.AUTHOR_URL,
						AUTHOR_URL_PARAMS : this.params.AUTHOR_URL_PARAMS,

						NAME_TEMPLATE : this.params.NAME_TEMPLATE,
						SHOW_LOGIN : this.params.SHOW_LOGIN
					},
					this.getTemplate()
				)),
				ob = BX.processHTML(html, false),
				results,
				newCommentsContainer = BX('record-' + id[0] + '-new'),
				acts = ["MODERATE", "EDIT", "DELETE"],
				needToCheck = false,
				height = 0;
			for (var ii in acts)
			{
				if (acts.hasOwnProperty(ii))
				{
					if (this.rights[acts[ii]] == "OWNLAST")
					{
						needToCheck = true;
						break;
					}
				}
			}
			if (needToCheck)
			{
				results = (!!newCommentsContainer.lastChild && newCommentsContainer.lastChild.className == "feed-com-block-cover" ? [newCommentsContainer.lastChild] : []);
				var res, res2;
				if (this.addCheckPreviousNodes !== true)
				{
					results = BX.findChildren(newCommentsContainer.parentNode, {tagName : "DIV", "className" : "feed-com-block-cover"}, false);
					var results2 = BX.findChildren(newCommentsContainer, {tagName : "DIV",  "className" : "feed-com-block-cover"}, false);
					results = (!!results ? results : []); results2 = (!!results2 ? results2 : []);
					while (results2.length > 0 && (res = results2.pop()) && !!res)
						results.push(res);
					this.addCheckPreviousNodes = true;
				}
				while ((res = results.pop()) && res)
				{
					res2 = BX(res.id.replace("-cover", "-actions"));
					if (!!res2)
					{
						if (this.rights["EDIT"] == "OWNLAST")
							res2.setAttribute("bx-mpl-edit-show", "N");
						if (this.rights["MODERATE"] == "OWNLAST")
							res2.setAttribute("bx-mpl-moderate-show", "N");
						if (this.rights["DELETE"] == "OWNLAST")
							res2.setAttribute("bx-mpl-delete-show", "N");
					}
				}
			}

			var changeOpacity = false;
			if (!container)
			{
				container = BX.create("DIV", {
					attrs : {id : ("record-" + id.join('-') + '-cover'), "className" : "feed-com-block-cover"},
					style : {opacity : 0, height : 0, overflow: "hidden"},
					html : ob.HTML});
				newCommentsContainer.appendChild(container);
				changeOpacity = true;
			}
			else
			{
				var containerBody = BX.create("DIV", {
					attrs : {id : ("record-" + id.join('-') + '-cover'), "className" : "feed-com-block-cover"},
					style : {display : "none"},
					html : ob.HTML}), containerForRemove = container;
				container.parentNode.insertBefore(containerBody, container);
				container.removeAttribute("id");
				height = container.scrollHeight;
				BX.hide(container);
				BX.show(containerBody);
				container = containerBody;
				setTimeout(function() {
					BX.remove(containerForRemove);
				}, 1000);
			}

			if (animation !== "simple")
			{
				var curPos = BX.pos(container),
					scroll = BX.GetWindowScrollPos(),
					size = BX.GetWindowInnerSize();
				(new BX["easing"]({
					duration : 1000,
					start : { opacity : (changeOpacity ? 0 : 100), height : height},
					finish : { opacity: 100, height : container.scrollHeight},
					transition : BX.easing.makeEaseOut(BX.easing.transitions.quart),
					step : function(state){
						container.style.height = state.height + "px";
						container.style.opacity = state.opacity / 100;
						if (scroll.scrollTop > 0 && curPos.top < (scroll.scrollTop + size.innerHeight))
							window.scrollTo(0, scroll.scrollTop + state.height);
					},

					complete : function(){
						container.style.cssText = '';
					}
				})).animate();
			}
			else
			{
				(new BX["easing"]({
					duration : 500,
					start : { height : height, opacity : (changeOpacity ? 0 : 100)},
					finish : { height : container.scrollHeight, opacity : 100},
					transition : BX.easing.makeEaseOut(BX.easing.transitions.cubic),
					step : function(state) {
						container.style.height = state.height + "px";
						container.style.opacity = state.opacity / 100;
					},
					complete : BX.proxy(function() {
						container.style.cssText = '';
						BX.onCustomEvent(this, 'OnUCRecordWasShown', [this.ENTITY_XML_ID, id, container]);
					}, this)
				})).animate();
			}


			var cnt = 0,
			func = function()
			{
				cnt++;
				if (cnt < 100)
				{
					var node = BX("record-" + id.join('-') + '-cover');
					if (node && node.childNodes.length > 0)
					{
						BX.ajax.processScripts(ob.SCRIPT);
						if (this.params["BIND_VIEWER"] == "Y" && BX["viewElementBind"])
						{
							BX.viewElementBind(
								node, {},
								function(node){
									return BX.type.isElementNode(node) && (node.getAttribute('data-bx-viewer') || node.getAttribute('data-bx-image'));
								}
							);
						}
					}
					else
						BX.defer(func, this)();
				}
				BX.onCustomEvent(window, 'OnUCRecordHasDrawn', [this.ENTITY_XML_ID, id, data["messageFields"]]);
				BX.onCustomEvent(window, "OnUCFeedChanged", [id]);
			};
			BX.defer(func, this)();
			return true;
		},
		pullNewAuthor : function(authorId, authorName, authorAvatar) {
			BX.onCustomEvent(window, 'OnUCUsersAreWriting', [this.ENTITY_XML_ID, authorId, authorName, authorAvatar]);
		},
		pullNewRecord : function(params) {
			var id = [this.ENTITY_XML_ID, parseInt(params["ID"])];
			if (!!this.pullNewRecords[id.join('-')] && this.pullNewRecords[id.join('-')] == "busy")
				return true;
			else if (!!this.pullNewRecords[id[0] + '-0'] && this.pullNewRecords[id[0] + '-0'] == "busy")
				return setTimeout(BX.proxy(function () {this.pullNewRecord(params)}, this), 100);

			BX.onCustomEvent(window, "OnUCBeforeCommentWillBePulled", [id, params]);

			if (params["NEED_REQUEST"] == "Y")
			{
				if (
					params['URL']['LINK'].indexOf('#GROUPS_PATH#') >= 0
					&& !!BX.message('MPL_WORKGROUPS_PATH')
				)
					params['URL']['LINK'] = params['URL']['LINK'].replace('#GROUPS_PATH#', BX.message('MPL_WORKGROUPS_PATH'));

				this.pullNewRecords[id.join('-')] = "busy";
				var data = BX.ajax.prepareData( {
					AJAX_POST : "Y",
					ENTITY_XML_ID : this.ENTITY_XML_ID,
					MODE : "RECORD",
					FILTER : {"ID" : params["ID"]},
					sessid : BX.bitrix_sessid() }),
					url = params['URL']['LINK'];
					url = (url.indexOf('#') !== -1 ? url.substr(0, url.indexOf('#')) : url);
				BX.ajax({
					url: (url + (url.indexOf('?') !== -1 ? "&" : "?") + data),
					method: 'GET',
					dataType: 'json',
					data: '',
					onsuccess: BX.delegate(function(data){
						if (!!BX('record-' + id.join('-') + '-cover'))
							return;
						this.add([this.ENTITY_XML_ID, parseInt(params["ID"])], data);
						var node = BX('record-' + id.join('-') + '-cover'),
							node1 = BX.findChild(node, {className: 'feed-com-block'}, true, false);
						BX.addClass(node, 'comment-new-answer');
						BX.addClass(node1, 'feed-com-block-pointer-to-new feed-com-block-new');
						this.pullNewRecords[id.join('-')] = "done";
						if (BX('record-' + id[0] + '-corner'))
						{
							BX.addClass(BX('record-' + id[0] + '-corner'), (BX.hasClass(node1, "feed-com-block-new") ? "feed-post-block-yellow-corner" :""));
							BX('record-' + id[0] + '-corner').removeAttribute("id");
						}
						BX.onCustomEvent(window, "OnUCCommentWasPulled", [id, data]);
					}, this)
				});
			}
			else if (params["ACTION"] == "DELETE")
			{
				if (BX('record-' + this.ENTITY_XML_ID + '-' + params["ID"]))
					BX.fx.hide(BX('record-' + this.ENTITY_XML_ID + '-' + params["ID"]), 'scroll',  {time : 0.2});

				BX.onCustomEvent(window, 'OnUCommentWasDeleted', [this.ENTITY_XML_ID, [this.ENTITY_XML_ID, params["ID"]], params]);
				BX.onCustomEvent(window, "OnUCFeedChanged", [params["ID"]]);
			}
			else if (params["ACTION"] == "HIDE")
			{
				var node0 = BX('record-' + this.ENTITY_XML_ID + '-' + params["ID"]),
					node2 = node0 ? BX.findChild(node0, {"tagName" : "DIV", "className" : "feed-com-block"}, true) : null;

				if (BX(node2))
				{
					if (BX.hasClass(node2, ("blog-comment-user-" + BX.message("USER_ID"))))
					{
						BX.removeClass(node2, "feed-com-block-approved");
						BX.addClass(node2, "feed-com-block-hidden");
					}
					else
					{
						BX.fx.hide(node0, 'scroll',  {time : 0.2});
						BX.onCustomEvent(window, 'OnUCommentWasHidden', [this.ENTITY_XML_ID, [this.ENTITY_XML_ID, params["ID"]], params]);
						BX.onCustomEvent(window, "OnUCFeedChanged", [params["ID"]]);
					}
				}
			}
			else if (params["ACTION"] == "EDIT" && !BX('record-' + this.ENTITY_XML_ID + '-' + params["ID"]))
			{
				BX.DoNothing();
			}
			//else if (params["ACTION"] == "MODERATE" && !BX('record-' + this.ENTITY_XML_ID + '-' + params["ID"]))
			//{
			//	TODO: We have to show moderated messages in a right order
			//}
			else
			{
				if (params && !(params["AUTHOR"] && (params["AUTHOR"]["ID"] + '') == (BX.message("USER_ID") + '')))
					params["NEW"] = "Y";
				this.add(id, {"messageFields" : params});
				var node = BX('record-' + id.join('-') + '-cover'),
					node1 = BX.findChild(node, {className: 'feed-com-block'}, true, false);
				if (BX('record-' + id[0] + '-corner'))
				{
					BX.addClass(BX('record-' + id[0] + '-corner'), (params["NEW"] == "Y" ? "feed-post-block-yellow-corner" :""));
					BX('record-' + id[0] + '-corner').removeAttribute("id");
				}

				BX.addClass(node, 'comment-new-answer');
				if (params["NEW"] == "Y")
				{
					BX.addClass(node1, 'feed-com-block-pointer-to-new feed-com-block-new');
				}
				this.pullNewRecords[id.join('-')] = "done";
				BX.onCustomEvent(window, "OnUCCommentWasPulled", [id, {"messageFields" : params}])
			}
			return true;
		},
		act : function(url, id, act) {
			if (url.substr(0, 1) != '/')
			{
				try { eval(url); return false; }
				catch(e) {}
				if (BX.type.isFunction(url)) {
					url(this, id, act);
					return false;
				}
			}
			this.showWait(id);
			act = (act === "EDIT" ? "EDIT" : (act === "DELETE" ? "DELETE" : "MODERATE"));
			id = parseInt(id);
			var data = BX.ajax.prepareData( {
				sessid : BX.bitrix_sessid(),
				MODE : "RECORD",
				NOREDIRECT : "Y",
				AJAX_POST : "Y",
				FILTER : {"ID" : id},
				ENTITY_XML_ID : this.ENTITY_XML_ID } );
			url = (url.indexOf('#') !== -1 ? url.substr(0, url.indexOf('#')) : url);

			BX.ajax({
				'method': 'GET',
				'url': (url + (url.indexOf('?') !== -1 ? "&" : "?") + data),
				'data': '',
				dataType: 'json',
				onsuccess: BX.proxy(function(data) {
					this.closeWait(id);
					if (data["status"] == "error")
					{
						this.showError(id, data["message"] || "Unknown error.");
					}
					else
					{
						if (act !== "EDIT")
						{
							var container = BX('record-' + this.ENTITY_XML_ID + '-' + id + '-cover');
							if (!!data['message'] && !!container)
							{
								var
									ob = BX.processHTML(data["message"], false);
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
								data['okMessage'] = '';
							}
							else if (act == "DELETE" && !!data['okMessage'])
							{
								BX.hide(BX('record-' + this.ENTITY_XML_ID + '-' + id));
								BX.onCustomEvent(window, 'OnUCommentWasDeleted', [this.ENTITY_XML_ID, [this.ENTITY_XML_ID, id]]);
							}
						}
						BX.onCustomEvent(window, 'OnUCAfterRecordEdit', [this.ENTITY_XML_ID, id, data, act]);
						BX.onCustomEvent(window, "OnUCFeedChanged", [id]);
					}
					this.busy = false;
				}, this),
				onfailure: BX.delegate(function(data){
					this.closeWait(id);
					this.showError(id, data);
				}, this)
			});
			return false;
		},
		showError : function(id, text) {
			if (this.errorWindow)
				this.errorWindow.close();

			this.errorWindow = new BX.PopupWindow('bx-comments-error', null, {
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
				titleBar: {content: BX.create('span', {props : { className : "popup-window-titlebar-text feed-error-title" },
					html: '<div class="feed-error-icon"></div>' + BX.message("MPL_ERROR_OCCURRED")})},
				//titleBar: ,
				// ,
				closeIcon : true,
				contentColor : "white",
				content : '<div class="feed-error-block">' + text + '</div>'
			});
			this.errorWindow.show();
		},
		checkHash : function(ENTITY_ID) {
			var id = [this.ENTITY_XML_ID, ENTITY_ID],
				node = BX('record-' + id.join('-') + '-cover');
			if (!!node)
			{
				var curPos = BX.pos(node);
				window.scrollTo(0, curPos["top"]);
				node = BX.findChild(node, {className: 'feed-com-block'}, true, false);
				BX.removeClass(node, "feed-com-block-pointer-to-new feed-com-block-new");
				BX.addClass(node, "feed-com-block-pointer");
			}
		},
		getTemplate : function()
		{
			return BX.message("MPL_RECORD_TEMPLATE");
		},
		showWait : function(id)
		{
			window.fcShowWait(BX('record-' + this.ENTITY_XML_ID + '-' + id + '-actions'));
		},
		closeWait : function(id)
		{
			window.fcCloseWait(BX('record-' + this.ENTITY_XML_ID + '-' + id + '-actions')||null);
		}
	};

	window.FCList.getQuoteData = function(){ return quoteData; };
	window.FCList.getInstance = function(params, add) {
		if (!repo[params["ENTITY_XML_ID"]])
			new window.FCList(params, add);
		return repo[params["ENTITY_XML_ID"]];
	};

	var lastWaitElement = null;
	window["fcShowWait"] = function(el) {
		if (el && !BX.type.isElementNode(el))
			el = null;
		el = el || this;

		if (BX.type.isElementNode(el))
		{
			BX.defer(function(){el.disabled = true})();
			var waiter_parent = BX.findParent(el, BX.is_relative);

			el.bxwaiter = (waiter_parent || document.body).appendChild(BX.create('DIV', {
				props: {className: 'feed-com-loader'},
				style: {position: 'absolute'}
			}));
			lastWaitElement = el;

			return el.bxwaiter;
		}
		return true;
	};

	window["fcCloseWait"] = function(el) {
		if (el && !BX.type.isElementNode(el))
			el = null;
		el = el || lastWaitElement || this;

		if (BX.type.isElementNode(el))
		{
			if (el.bxwaiter && el.bxwaiter.parentNode)
			{
				el.bxwaiter.parentNode.removeChild(el.bxwaiter);
				el.bxwaiter = null;
			}

			el.disabled = false;
			if (lastWaitElement == el)
				lastWaitElement = null;
		}
	};

	window["fcShowActions"] = function(ENTITY_XML_ID, ID, el) {
		var panels = [];
		if (el.getAttribute('bx-mpl-view-show') == 'Y')
		{
			panels.push({
				text : BX.message("MPL_MES_HREF"),
				href : el.getAttribute('bx-mpl-view-url').replace(/\\#(.+)$/gi, "") + "#com" + ID
			});
			panels.push({
				text : '<span id="record-popup-' + ENTITY_XML_ID + '-' + ID + '-link-text">' + BX.message("B_B_MS_LINK") + '</span>' +
					'<span id="record-popup-' + ENTITY_XML_ID + '-' + ID + '-link-icon-animate" class="comment-menu-link-icon-wrap">' +
						'<span class="comment-menu-link-icon" id="record-popup-' + ENTITY_XML_ID + '-' + ID + '-link-icon-done" style="display: none;">' +

						'</span>' +
					'</span>',
				onclick : function() {
					var
						id = 'record-popup-' + ENTITY_XML_ID + '-' + ID + '-link',
						urlView = el.getAttribute('bx-mpl-view-url').replace(/#(.+)$/gi, "") + "#com" + ID,
						menuItemText = BX(id + '-text'),
						menuItemIconDone = BX(id + '-icon-done');

					urlView = (urlView.indexOf('http') < 0 ? (location.protocol + '//' + location.host) : '') + urlView;

					if (BX.clipboard.isCopySupported())
					{
						if (menuItemText && menuItemText.getAttribute('data-block-click') == 'Y')
						{
							return;
						}

						BX.clipboard.copy(urlView);
						if (
							menuItemText
							&& menuItemIconDone
						)
						{
							menuItemIconDone.style.display = 'inline-block';
							BX.removeClass(BX(id + '-icon-animate'), 'comment-menu-link-icon-animate');

							BX.adjust(BX(id + '-text'), {
								attrs: {
									'data-block-click': 'Y'
								}
							});

							setTimeout(function() {
								BX.addClass(BX(id + '-icon-animate'), 'comment-menu-link-icon-animate');
							}, 1);

							setTimeout(function() {
								BX.adjust(BX(id + '-text'), {
									attrs: {
										'data-block-click': 'N'
									}
								});
							}, 500);
						}

						return;
					}

					var
						it = BX.proxy_context,
						height = parseInt(!!it.getAttribute("bx-height") ? it.getAttribute("bx-height") : it.offsetHeight);

					if (it.getAttribute("bx-status") != "shown")
					{
						it.setAttribute("bx-status", "shown");
						if (!BX(id) && !!BX(id + '-text'))
						{
							var
								node = BX(id + '-text'),
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
								style : { overflow : "hidden", display : 'block'},
								children : [
									BX.create('BR'),
									BX.create('DIV', { attrs : {id : id},
										children : [
											BX.create('SPAN', {attrs : {"className" : "menu-popup-item-left"}}),
											BX.create('SPAN', {attrs : {"className" : "menu-popup-item-icon"}}),
											BX.create('SPAN', {attrs : {"className" : "menu-popup-item-text"},
												children : [
													BX.create('INPUT', {
															attrs : {
																id : id + '-input',
																type : "text",
																value : urlView} ,
															style : {
																height : pos2["height"] + 'px',
																width : pos2["width"] + 'px'
															},
															events : { click : function(e){ this.select(); BX.PreventDefault(e);} }
														}
													)
												]
											})
										]
									}),
									BX.create('SPAN', {"className" : "menu-popup-item-right"})
								]
							});
						}
						(new BX["fx"]({
							time: 0.2,
							step: 0.05,
							type: 'linear',
							start: height,
							finish: height * 2,
							callback: BX.delegate(function(height) {this.style.height = height + 'px';}, it)
						})).start();
						BX.fx.show(BX(id), 0.2);
						BX(id + '-input').select();
					}
					else
					{
						it.setAttribute("bx-status", "hidden");
						(new BX["fx"]({
							time: 0.2,
							step: 0.05,
							type: 'linear',
							start: it.offsetHeight,
							finish: height,
							callback: BX.delegate(function(height) {this.style.height = height + 'px';}, it)
						})).start();
						BX.fx.hide(BX(id), 0.2);
					}
				}
			});
		}
		if (el.getAttribute('bx-mpl-edit-show') == 'Y')
			panels.push({
				text : BX.message("BPC_MES_EDIT"),
				onclick : function() { window['UC'][ENTITY_XML_ID].act(el.getAttribute('bx-mpl-edit-url'), ID, 'EDIT'); this.popupWindow.close(); return false;}
			});
		if (el.getAttribute('bx-mpl-moderate-show') == 'Y')
		{
			var hidden = el.getAttribute('bx-mpl-moderate-approved') == 'hidden';
			panels.push({
				text : (hidden ? BX.message("BPC_MES_SHOW") : BX.message("BPC_MES_HIDE")),
				onclick : function() { window['UC'][ENTITY_XML_ID].
					act(el.getAttribute('bx-mpl-moderate-url').
					replace("#action#", (hidden ? "show" : "hide")).
					replace("#ACTION#", (hidden ? "SHOW" : "HIDE")), ID, 'MODERATE');
					this.popupWindow.close();}
			});
		}
		if (el.getAttribute('bx-mpl-delete-show') == 'Y')
		{
			panels.push({
				text : BX.message("BPC_MES_DELETE"),
				onclick : function() {
					if(window.confirm(BX.message("BPC_MES_DELETE_POST_CONFIRM")))
						window['UC'][ENTITY_XML_ID].act(el.getAttribute('bx-mpl-delete-url'), ID, 'DELETE');
					this.popupWindow.close(); return false;
				}
			});
		}

		if (
			el.getAttribute('bx-mpl-createtask-show') == 'Y'
			&& typeof oLF != 'undefined'
		)
		{
			panels.push({
				text : BX.message("BPC_MES_CREATE_TASK"),
				onclick : function() {
					oLF.createTask({
						entityType: 'BLOG_COMMENT',
						entityId: ID
					});
					this.popupWindow.close(); return false;
				}
			});
		}

		if (panels.length > 0) {
			for (var ii in panels)
			{
				if (panels.hasOwnProperty(ii))
				{
					panels[ii]['className'] = 'blog-comment-popup-menu';
				}
			}
			BX.PopupMenu.show('action-' + ENTITY_XML_ID + '-' + ID, el,
				panels,
				{
					offsetLeft: -18,
					offsetTop: 2,
					lightShadow: false,
					angle: {position: 'top', offset: 50},
					events : {
						onPopupClose : function() { this.destroy();BX.PopupMenu.Data['action-' + ENTITY_XML_ID + '-' + ID] = null; }
					}
				}
			);
		}
	};

	window["fcCommentExpand"] = function(el) {
		BX.UI.Animations.expand({
			moreButtonNode: el,
			type: 'comment',
			classBlock: 'feed-com-block',
			classOuter: 'feed-com-text-inner',
			classInner: 'feed-com-text-inner-inner',
			heightLimit: 200,
			callback: function(el) {
				BX.onCustomEvent(window, 'OnUCRecordWasExpanded', [el]);
				var commentContentId = el.getAttribute('bx-content-view-xml-id');
				if (BX.type.isNotEmptyString(commentContentId))
				{
					BX.onCustomEvent(window, "OnUCFeedChanged", [ commentContentId.split('-') ]);
				}
			}
		})
	};

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
			($res["AUTHOR"]["IS_EXTRANET"] == "Y" ? ' feed-com-name-extranet' : ''),
		"background:url('') no-repeat center;" =>
			""
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
		params["TIME_FORMAT"] = (!!params["DATE_TIME_FORMAT"] && params["DATE_TIME_FORMAT"].indexOf('a') >= 0 ? 'g:i a' : 'G:i');

		params["VIEW_URL"] = (params["VIEW_URL"] || '');
		params["EDIT_URL"] = (params["EDIT_URL"] || '');
		params["MODERATE_URL"] = (params["MODERATE_URL"] || '');
		params["DELETE_URL"] = (params["DELETE_URL"] || '');
		params["AUTHOR_URL"] = (params["AUTHOR_URL"] || '');

		params["NAME_TEMPLATE"] = (params["NAME_TEMPLATE"] || '');
		params["SHOW_LOGIN"] = (params["SHOW_LOGIN"] || '');
		var res = (data && data["messageFields"] ? data["messageFields"] : data),
			replacement = {
				"ID" : '',
				"FULL_ID" : '',
				"CONTENT_ID" : '',
				"ENTITY_XML_ID" : '',
				"NEW" : "old",
				"APPROVED" : 'Y',
				"DATE" : '',
				"TEXT" : '',
				"CLASSNAME" : '',
				"VIEW_URL" : '',
				"VIEW_SHOW" : 'N',
				"EDIT_URL" : '',
				"EDIT_SHOW" : 'N',
				"MODERATE_URL" : '',
				"MODERATE_SHOW" : 'N',
				"DELETE_URL" : '',
				"DELETE_SHOW" : 'N',
				"CREATETASK_SHOW" : 'N',
				"BEFORE_HEADER" : '',
				"BEFORE_ACTIONS" : '',
				"AFTER_ACTIONS" : '',
				"AFTER_HEADER" : '',
				"BEFORE" : '',
				"AFTER" : '',
				"BEFORE_RECORD" : '',
				"AFTER_RECORD" : '',
				"AUTHOR_ID" : 0,
				"AUTHOR_AVATAR_IS" : 'N',
				"AUTHOR_AVATAR" : '',
				"AUTHOR_URL" : '',
				"AUTHOR_NAME" : '',
				"AUTHOR_EXTRANET_STYLE" : '',
				"SHOW_POST_FORM" : 'Y',
				"VOTE_ID" : "",
				"AUTHOR_TOOLTIP_PARAMS" : '',
				"background:url('') no-repeat center;" : ""
			};
		if (!!res && !!data["messageFields"])
		{
			res["AUTHOR"] = (!!res["AUTHOR"] ? res["AUTHOR"] : {});
			var timestamp = parseInt(res["POST_TIMESTAMP"]) + parseInt(BX.message('USER_TZ_OFFSET')) + parseInt(BX.message('SERVER_TZ_OFFSET'));

			var dateFormat = [
				['today', (params["TIME_FORMAT"].indexOf("today") < 0 ? 'today, '+params["TIME_FORMAT"] : params["TIME_FORMAT"])],
				['yesterday', (params["TIME_FORMAT"].indexOf("yesterday") < 0 ? 'yesterday, '+params["TIME_FORMAT"] : params["TIME_FORMAT"])],
				['', params["DATE_TIME_FORMAT"]]
			];

			var authorStyle = '';
			if (typeof res["AUTHOR"]["TYPE"] != 'undefined')
			{
				if (res["AUTHOR"]["TYPE"] == 'EMAIL')
				{
					authorStyle = ' feed-com-name-email';
				}
				else if (res["AUTHOR"]["TYPE"] == 'EXTRANET')
				{
					authorStyle = ' feed-com-name-extranet';
				}
			}
			else if (res["AUTHOR"]["IS_EXTRANET"] == "Y")
			{
				authorStyle = ' feed-com-name-extranet';
			}
			var commentText = (
				!!res.AUX
				&& res.AUX.length > 0
					? BX.CommentAux.getLiveText(res.AUX, (!!res.AUX_LIVE_PARAMS ? res.AUX_LIVE_PARAMS : {} ))
					: res["POST_MESSAGE_TEXT"].replace(/\001/gi, "").replace(/#/gi, "\001")
			);

			replacement = {
				"ID" : res["ID"],
				"FULL_ID" : res["FULL_ID"].join('-'),
				"CONTENT_ID" : (res["RATING"] && res["RATING"]["ENTITY_TYPE_ID"] && res["RATING"]["ENTITY_ID"] ? res["RATING"]["ENTITY_TYPE_ID"] + "-" + res["RATING"]["ENTITY_ID"] : ""),
				"ENTITY_XML_ID" : res["ENTITY_XML_ID"],
				"NEW" : res["NEW"] == "Y" ? "new" : "old",
				"APPROVED" : (res["APPROVED"] != "Y" ? "hidden" : "approved"),
				"DATE" : BX.date.format(
					dateFormat,
					timestamp, false, true
				),
				"TEXT" : commentText,
				"CLASSNAME" : (res["CLASSNAME"] ? " " + res["CLASSNAME"] : ""),
				"VIEW_URL" : params["VIEW_URL"].replace("#ID#", res["ID"]).replace("#id#", res["ID"]),
				"VIEW_SHOW" : (params["VIEW_URL"] !== '' ? "Y" : "N"),
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
				"DELETE_SHOW" : (params["RIGHTS"]["DELETE"] == "Y" || params["RIGHTS"]["DELETE"] == "ALL" ||
					params["RIGHTS"]["DELETE"] == "OWN" && BX.message("USER_ID") == res["AUTHOR"]["ID"] ? "Y" : "N"),
				"CREATETASK_SHOW" : (
					(!res.AUX || res.AUX.length <= 0)
					&& params["RIGHTS"]["CREATETASK"] == "Y"
						? "Y"
						: "N"
				),
				"BEFORE_HEADER" : res['BEFORE_HEADER'],
				"BEFORE_ACTIONS" : res['BEFORE_ACTIONS'],
				"AFTER_ACTIONS" : res['AFTER_ACTIONS'],
				"AFTER_HEADER" : res['AFTER_HEADER'],
				"BEFORE" : res['BEFORE'],
				"AFTER" : res['AFTER'],
				"BEFORE_RECORD" : res['BEFORE_RECORD'],
				"AFTER_RECORD" : res['AFTER_RECORD'],
				"AUTHOR_ID" : res["AUTHOR"]["ID"],
				"AUTHOR_AVATAR_IS" : (!!res["AUTHOR"]["AVATAR"] ? "Y" : "N"),
				"AUTHOR_AVATAR" : (!!res["AUTHOR"]["AVATAR"] ? res["AUTHOR"]["AVATAR"] : '/bitrix/images/1.gif'),
				"AUTHOR_URL" : params["AUTHOR_URL"].
					replace("#ID#", res["ID"]).
					replace("#id#", res["ID"]).
					replace("#USER_ID#", res["AUTHOR"]["ID"]).
					replace("#user_id#", res["AUTHOR"]["ID"]) + (
						typeof res["AUTHOR"]["EXTERNAL_AUTH_ID"] != 'undefined'
						&& res["AUTHOR"]["EXTERNAL_AUTH_ID"] == 'email'
						&& typeof params["AUTHOR_URL_PARAMS"] != 'undefined'
							? (params["AUTHOR_URL"].indexOf("?") >= 0 ? '&' : '?') + 'entityType=' + params["AUTHOR_URL_PARAMS"]["entityType"] + '&entityId=' + params["AUTHOR_URL_PARAMS"]["entityId"]
							: ''
					),
				"AUTHOR_NAME" : BX.formatName(res["AUTHOR"], params["NAME_TEMPLATE"], params["SHOW_LOGIN"]),
				"AUTHOR_EXTRANET_STYLE" : authorStyle,
				"VOTE_ID" : (res["RATING"] && res["RATING"]["VOTE_ID"] ? res["RATING"]["VOTE_ID"] : ""),
				"AUTHOR_TOOLTIP_PARAMS" : (typeof res["AUTHOR_TOOLTIP_PARAMS"] != 'undefined' ? res["AUTHOR_TOOLTIP_PARAMS"] : '{}'),
				"background:url('') no-repeat center;" : ""
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
				replacement[ii] = (!!replacement[ii] ? replacement[ii] : '');
			}
		}
		replacement["SHOW_POST_FORM"] = (BX('record-' + replacement["ENTITY_XML_ID"] + '-0-placeholder') ? "Y" : "N");
		for (var ij in replacement)
		{
			if (replacement.hasOwnProperty(ij))
			{
				txt = txt.replace(new RegExp('#' + ij + '#', "g"), replacement[ij]);
			}
		}
		return txt.replace("background:url('') no-repeat center;", "").replace(/\001/gi, "#");
	};

	window["fcPull"] = function(ENTITY_XML_ID, data) {
		BX.ajax({
			url: '/bitrix/components/bitrix/main.post.list/templates/.default/component_epilog.php',
			method: 'POST',
			data: {
				AJAX_POST :  "Y",
				ENTITY_XML_ID : ENTITY_XML_ID,
				MODE : "PUSH&PULL",
				sessid : BX.bitrix_sessid(),
				DATA : data
			}
		});
	};

	var newCommentsToCheckForReading = { data : [], screen : {}, timeout : 0 },
		traceForReading = function(id) {
		newCommentsToCheckForReading.data.push(id);
		newCommentsToCheckForReading.screen = (newCommentsToCheckForReading.screen || {
			scrollTop : BX.GetWindowScrollPos().scrollTop,
			time : new Date().getTime()
		});
		newCommentsToCheckForReading.screen["checked"] = false;
		newCommentsToCheckForReading.timeout = (newCommentsToCheckForReading.timeout || setTimeout(markReadComments, 1000));
	};
	BX.addCustomEvent(window, 'OnUCRecordHasDrawn', function(ENTITY_XML_ID, id){traceForReading(id);});

	var markReadComments = function() {
		var scroll = BX.GetWindowScrollPos();
		if(scroll.scrollTop != newCommentsToCheckForReading.screen["scrollTop"])
		{
			newCommentsToCheckForReading.screen["time"] = new Date().getTime();
			newCommentsToCheckForReading.screen["scrollTop"] = scroll.scrollTop;
			newCommentsToCheckForReading.screen["checked"] = false;
		}
		else if(!newCommentsToCheckForReading.screen["checked"] &&
			(new Date().getTime() - newCommentsToCheckForReading.screen["time"] > 3000))
		{
			newCommentsToCheckForReading.screen["time"] = new Date().getTime();
			newCommentsToCheckForReading.screen["checked"] = true;

			var commentsReadToCounter = 0,
				size = BX.GetWindowInnerSize(),
				res = [],
				node, pos, node1, i,
				commentsBlockNode, cornerNode;
			for (i = 0; i < newCommentsToCheckForReading.data.length; i++)
			{
				node = BX('record-' + newCommentsToCheckForReading.data[i].join('-') + '-cover');
				if (node)
				{
					pos = BX.pos(node);
					if (pos.top >= scroll.scrollTop && pos.top <= (scroll.scrollTop +size.innerHeight - 20))
					{
						BX.onCustomEvent(window, 'OnUCCommentWasRead', [newCommentsToCheckForReading.data[i], node]);
						BX.removeClass(node, 'comment-new-answer');

						commentsBlockNode = BX.findParent(node, { className: 'feed-comments-block'});
						if (commentsBlockNode)
						{
							cornerNode = BX.findChild(commentsBlockNode, {className: 'feed-com-corner'});
							if (cornerNode)
							{
								BX.addClass(cornerNode, "feed-post-block-corner-fade");
							}
						}

						node1 = BX.findChild(node, {className: 'feed-com-block'}, true, false);
						BX.removeClass(node1, 'feed-com-block-pointer-to-new feed-com-block-new');
						BX.addClass(node1, 'feed-com-block-read');
						commentsReadToCounter++;
					}
					else
					{
						res.push(newCommentsToCheckForReading.data[i]);
					}
				}
			}
			newCommentsToCheckForReading.data = res;
			if(commentsReadToCounter > 0)
				BX.onCustomEvent(window, 'onCounterDecrement', [commentsReadToCounter]);
		}

		if (newCommentsToCheckForReading.data.length > 0)
			newCommentsToCheckForReading.timeout = setTimeout(markReadComments, 1000);
		else
		{
			newCommentsToCheckForReading.timeout = 0;
		}
	};

	var MPLQuote = function(params) {
		this.params = params;
		this.id = params["id"];
		this.closeByEsc = !!params["closeByEsc"];
		this.autoHide = !!params["autoHide"];
		this.autoHideTimeout = (!!params["autoHideTimeout"] ? parseInt(params["autoHideTimeout"]) : 0);

		if (this.params.classEvents)
		{
			for (var eventName in this.params.classEvents)
				if (this.params.classEvents.hasOwnProperty(eventName))
					BX.addCustomEvent(this, eventName, this.params.classEvents[eventName]);
		}

		this.node = document.createElement("A");
		BX.adjust(this.node, {
			props : {
				id : this.id
			},
			style : {
				zIndex: BX.PopupWindow.getOption("popupZindex") + this.params.zIndex,
				position: "absolute",
				display: "none",
				top: "0px",
				left: "0px"
			},
			attrs : {
				"className" : "mpl-quote-block",
				href : "#"
			},
			events : this.params.events
		});

		document.body.appendChild(this.node);
	};
	MPLQuote.prototype = {
		show : function(e){
			var pos = this.getPosition(this.node, e);
			BX.adjust(this.node, {style : {top : pos.y + 'px', left : pos.x + 'px', display : 'block'}});
			BX.addClass(this.node, "mpl-quote-block-show");
			if (this.closeByEsc && !this.isCloseByEscBinded)
			{
				this.isCloseByEscBinded = BX.delegate(this._onKeyUp, this);
				BX.bind(document, "keyup", this.isCloseByEscBinded);
			}

			if (this.params.autoHide && !this.isAutoHideBinded)
			{
				setTimeout(
					BX.proxy(function() {
						BX.bind(this.node, "click", this.cancelBubble);
						this.isAutoHideBinded = BX.delegate(this.hide, this);
						BX.bind(document, "click", this.isAutoHideBinded);
					}, this), 0
				);
			}

			if (this.autoHideTimeout > 0 && this.autoHideTimeoutInt <= 0)
			{
				if (!this.autoHideTimeoutBinded)
					this.autoHideTimeoutBinded = BX.delegate(this.hide, this);
				this.autoHideTimeoutInt = setTimeout(this.autoHideTimeoutBinded, this.autoHideTimeout);
			}
		},
		hide : function(event) {
			if (!this.isShown())
				return;

			if (event && !(BX.getEventButton(event) & BX.MSLEFT))
				return;

			this.node.style.display = "none";

			if (this.isCloseByEscBinded)
			{
				BX.unbind(document, "keyup", this.isCloseByEscBinded);
				this.isCloseByEscBinded = false;
			}

			if (this.autoHideTimeout > 0)
			{
				clearTimeout(this.autoHideTimeoutInt);
				this.autoHideTimeoutInt = 0;
			}
			setTimeout(BX.proxy(this._hide, this), 0);
		},
		_hide : function()
		{
			BX.onCustomEvent(this, "onQuoteHide", [this]);
			if (this.params.autoHide && this.isAutoHideBinded)
			{
				BX.unbind(this.node, "click", this.cancelBubble);
				BX.unbind(document, "click", this.isAutoHideBinded);
				this.isAutoHideBinded = false;
			}
			BX.remove(this.node);
		},
		getPosition : function(node, e) {
			var nodePos;
			if (e.pageX == null) {
				var doc = document.documentElement, body = document.body;
				var x = e.clientX + (doc && doc.scrollLeft || body && body.scrollLeft || 0) - (doc.clientLeft || 0);
				var y = e.clientY + (doc && doc.scrollTop || body && body.scrollTop || 0) - (doc.clientTop || 0);
				nodePos = {x: x, y: y};
			} else {
				nodePos = {x: e.pageX, y: e.pageY};
			}
			return {'x': nodePos.x + 5, 'y':nodePos.y - 16};
		},
		isShown : function()
		{
			return this.node.style.display == "block";
		},
		cancelBubble : function(event)
		{
			if(!event)
				event = window.event;

			if (event.stopPropagation)
				event.stopPropagation();
			else
				event.cancelBubble = true;
		},
		_onKeyUp : function(event)
		{
			event = event || window.event;
			if (event.keyCode == 27)
				this.hide(event);
		}
	};

	window.mplCheckForQuote = function(e, node, ENTITY_XML_ID, author_id) {
		e = (document.all ? window.event : e);
		var text = '', range, author = null;

		if (window.getSelection) {
			range = window.getSelection();
			text = range.toString();
		} else if (document.selection) {
			range = document.selection;
			text = range.createRange().text;
		}
		if (text != "")
		{
			var endParent = BX.findParent(range.focusNode, {"tagName" : node.tagName, "className" : node.className}, node),
				startParent = BX.findParent(range.anchorNode, {"tagName" : node.tagName, "className" : node.className}, node);
			if (endParent != startParent || endParent != node) {
				text = "";
			} else {
				if (!!author_id && BX(author_id, true))
				{
					var tmp = BX(author_id, true);
					if (!!tmp && tmp.hasAttribute("bx-post-author-id"))
					{
						author = {
							id : parseInt(tmp.getAttribute("bx-post-author-id")),
							name : tmp.innerHTML
						}
					}
				}
			}
		}
		if (text != "") {
			BX.onCustomEvent(window, "onQuote" + ENTITY_XML_ID, [e, {text : text, author : author}]);
			return true;
		}
		return false;
	};

	window.mplReplaceUserPath = function(text) {
		if (
			typeof text != 'string'
			|| text.length <= 0
		)
		{
			return '';
		}

		if (BX('MPL_IS_EXTRANET_SITE') == 'Y')
		{
			text = text.replace('/company/personal/user/', '/extranet/contacts/personal/user/');
		}
		else
		{
			text = text.replace('/extranet/contacts/personal/user/', '/company/personal/user/');
		}

		text = text.replace(
			new RegExp("[\\w\/]*\/mobile\/users\/\\?user_id=(\\d+)", 'igm'),
			(
				BX('MPL_IS_EXTRANET_SITE') == 'Y'
					? '/extranet/contacts/personal/user/$1/'
					: '/company/personal/user/$1/'
			)
		);

		return text;
	};

	BX.onCustomEvent("main.post.list/default", ["script.js"]);
})();
