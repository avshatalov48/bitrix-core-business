;(function(window) {
	var BX = window.BX;
	if (BX["Vote"])
		return;
	BX.Vote = (function() {
		var d = function(){

		};
		d.prototype = {

		};
		return d;
	})();

	var repo = {};
	BX.Vote.init = function(params) {
		repo[params['cid'] + '_count'] = (repo[params['cid'] + '_count'] || 1);
		if (repo[params['cid'] + '_count'] > 100)
			throw "Vote with cid " + params['cid'] + " could not be found.";
		else if (!BX('vote-' + params['cid']))
			setTimeout(function(){ BX.Vote.init(params);}, 100);
		else if (!BX('vote-' + params['cid']).hasAttribute("data-bx-bound"))
		{
			BX('vote-' + params['cid']).setAttribute("data-bx-bound", "vote");

			repo[params['cid']] = new BVotedUser({
				'CID' : params['cid'],
				'controller': BX('vote-' + params['cid']),
				'urlTemplate' : params['urlTemplate'],
				'nameTemplate' : params['nameTemplate'],
				'url' : params['url'],
				'voteId' : params['id'],
				'startCheck' : params['startCheck']
			});

			// continue or stop voting
			if (BX('vote-' + params['cid'] + '-stop'))
			{
				BX.bind(BX('vote-' + params['cid'] + '-stop'), "click", function(e) {
					voteAJAX(this, params['cid'], this.href, {
						VOTE_ID : params['id'],
						stopVoting : params['id']
					});
					return BX.PreventDefault(e);
				});
			}
			else if (BX('vote-' + params['cid'] + '-resume'))
			{
				BX.bind(BX('vote-' + params['cid'] + '-resume'), "click", function(e) {
					voteAJAX(this, params['cid'], this.href, {
						VOTE_ID : params['id'],
						resumeVoting : params['id']
					});
					return BX.PreventDefault(e);
				});
			}
			if (BX('vote-' + params['cid'] + '-revote'))
			{
				BX.bind(BX('vote-' + params['cid'] + '-revote'), "click", function(e) {
					voteAJAX(this, params['cid'], this.href, {
						VOTE_ID : params['id'],
						view_form : 'Y'
					});
					return BX.PreventDefault(e);
				});
			}
			if (BX('vote-' + params['cid'] + '-act'))
			{

				BX.bind(BX('vote-' + params['cid'] + '-act'), "click", function(e) {
					BX.addClass(BX('vote-' + params['cid'] + '-act'), "feed-add-button-load");
					var form = BX('vote-form-' + params['cid']);
					if (!!form)
					{
						voteAJAX(
							this,
							params['cid'],
							form.action,
							BX.ajax.prepareForm(form).data
						);
					}
					return BX.PreventDefault(e);
				});
			}
			if (BX('vote-' + params['cid'] + '-results'))
			{
				BX.bind(BX('vote-' + params['cid'] + '-results'), "click", function(e) {
					var link = this,
						controller = repo[params['cid']].controller;

					VCLinkShowWait(link);

					BX.addCustomEvent(
						controller,
						'OnBeforeChangeData',
						function()
						{
							var res = BX.findParent(controller, {"className" : "bx-vote-block"});
							BX.addClass(res, "bx-vote-block-result");
							VCLinkCloseWait(link);
						}
					);

					BX.addCustomEvent(
						controller,
						'OnAfterChangeData',
						function()
						{
							if (!!link)
								BX.hide(link);
						}
					);

					repo[params['cid']].send(true);

					return BX.PreventDefault(e);
				});
			}
		}
	};

	var
		VCLinkCloseWait = function(el) { BX.removeClass(el, 'bx-vote-loading'); },
		VCLinkShowWait = function(el) { BX.addClass(el, 'bx-vote-loading'); },
		voteAJAX = function(link, CID, url, data)
		{
			if (link.disabled === true)
				return false;

			url = url.
				replace(/.AJAX_RESULT=Y/g,'').
				replace(/.AJAX_POST=Y/g,'').
				replace(/.sessid=[^&]*/g, '').
				replace(/.VOTE_ID=([\d]+)/,'').
				replace(/.view_form=Y/g, '').
				replace(/.view_result=Y/g, '');

			data["AJAX_POST"] = "Y";
			data["sessid"] = BX.bitrix_sessid();

			VCLinkShowWait(link);

			BX.ajax({
				'method': 'POST',
				'processData': false,
				'url': url,
				'data': data,
				'onsuccess': function(result)
				{
					VCLinkCloseWait(link);

					var
						ob = BX.processHTML(result, false),
						res = BX.findParent(link, {"className" : "bx-vote-block"});

					if (!!res)
					{
						res.innerHTML = ob.HTML;

						BX.removeClass(res, "bx-vote-block-result");
						BX.removeClass(res, "bx-vote-block-result-view");

						if (ob.HTML.indexOf('<form') < 0)
						{
							BX.addClass(res, "bx-vote-block-result");
						}
						BX.defer(function()
						{
							BX.ajax.processScripts(ob.SCRIPT);
						})();
					}
					if (repo[CID])
					{
						repo[CID].__destruct();
						repo[CID] = null;
						delete repo[CID];
					}
				}
			});
			return true;
		};

	var BVotedUser = (function(){
		var d = function(params) {
			this.CID = params["CID"];
			this.url = params["url"];
			this.urlTemplate = params["urlTemplate"];
			this.nameTemplate = params["nameTemplate"];
			this.dateTemplate = params["dateTemplate"];
			this.data = {};
			this.popup = null;
			this.controller = params["controller"];
			this.startCheck = (!!params["startCheck"] ? parseInt(params["startCheck"]) : false);
			this.voteId = params["voteId"];
			this.status = "ready";
			this.__construct();
		};
		d.prototype = {
			__construct : function() {
				var res = BX.findChildren(this.controller, {"tagName" : "A", "className" : "bx-vote-voted-users"}, true), ii,
					f = BX.delegate(function() { this.get(); }, this);
				for (ii in res)
				{
					if (res.hasOwnProperty(ii))
					{
						BX.bind(res[ii], "click", f);
						//BX.bind(res[ii], "mouseover", BX.proxy(function(e) { this.init(e); }, this));
						//BX.bind(res[ii], "mouseout", BX.proxy(function(e) { this.init(e); }, this));
					}
				}

				this.onPullEvent = BX.delegate(function(command, params)
				{
					if (command == 'voting' && !!params && params["VOTE_ID"] == this.voteId)
					{
						var res = BX.findParent(this.controller, {"className" : "bx-vote-block"});
						if (!!res && BX.hasClass(res, "bx-vote-block-result"))
						{
							this.changeData(params);
						}
					}
				}, this);
				BX.addCustomEvent("onPullEvent-vote", this.onPullEvent);
			},
			__destruct : function() {
				var res = BX.findChildren(this.controller, {"tagName" : "A", "className" : "bx-vote-voted-users"}, true), ii;
				if (!!res)
				{
					for (ii in res)
					{
						if (res.hasOwnProperty(ii))
						{
							BX.unbindAll(res[ii]);
						}
					}
				}
				BX.removeCustomEvent("onPullEvent", this.onPullEvent);
			},
			init : function(e) {
				var node = BX.proxy_context;
				if (!!node.timeoutOver)
				{
					clearTimeout(node.timeoutOver);
					node.timeoutOver = false;
				}
				if (e.type == 'mouseover')
				{
					node.timeoutOver = setTimeout(BX.proxy(function()
					{
						this.get(node);
						if (this.popup)
						{
							BX.bind(
								this.popup.popupContainer,
								'mouseout',
								BX.proxy(
									function()
									{
										this.popup.timeoutOut = setTimeout(
											BX.proxy(
												function()
												{
													if (this.node == node && !!this.popup)
													{
														this.popup.close();
													}
												}, this),
											400
										);
									},
									this
								)
							);
							BX.bind(
								this.popup.popupContainer,
								'mouseover' ,
								BX.proxy(
									function()
									{
										if (this.popup.timeoutOut)
											clearTimeout(this.popup.timeoutOut);
									},
									this
								)
							);
						}
					}, this), 400);
				}
			},
			getID : function() {
				return 'vote' + new Date().getTime();
			},
			make : function(data, needToCheckData) {
				if (!this.popup)
					return true;
				needToCheckData = (needToCheckData !== false);
				var
					res1 = (this.popup && this.popup.contentContainer ? this.popup.contentContainer : BX('popup-window-content-bx-vote-popup-cont-' + this.CID)),
					node = false, res = false, i;
				if (this.popup.isNew)
				{
					node = BX.create("SPAN", {
							props : {className : "bx-ilike-popup"},
							children : [
								BX.create("SPAN", {
									props : {className: "bx-ilike-bottom_scroll"}
								})
							]
						}
					);
					res = BX.create("SPAN", {
						props : {className : "bx-ilike-wrap-block"},
						children : [
							node
						]
					});
				}
				else
				{
					node = BX.findChild(this.popup.contentContainer, {className : "bx-ilike-popup"}, true);
				}
				if (!!node && typeof data.items == "object")
				{
					var avatarNode = null;
					for (i in data.items)
					{
						if (data.items.hasOwnProperty(i) && !BX.findChild(node, {tag : "A", attr : {id : ("a" + data["answer_id"] + "u" + data.items[i]['ID'])}}, true))
						{

							if (data.items[i]['PHOTO_SRC'].length > 0)
							{
								avatarNode = BX.create("IMG", {
									attrs: {src: encodeURI(data.items[i]['PHOTO_SRC'])},
									props: {className: "bx-ilike-popup-avatar-img"}
								});
							}
							else
							{
								avatarNode = BX.create("IMG", {
									attrs: {src: '/bitrix/images/main/blank.gif'},
									props: {className: "bx-ilike-popup-avatar-img bx-ilike-popup-avatar-img-default"}
								});
							}

							node.appendChild(
								BX.create("A", {
									attrs : {id : ("a" + data["answer_id"] + "u" + data.items[i]['ID'])},
									props: {
										href:data.items[i]['URL'],
										target: "_blank",
										className: "bx-ilike-popup-img" + (!!data.items[i]['TYPE'] ? " bx-ilike-popup-img-" + data.items[i]['TYPE'] : "")
									},
									text: "",
									children: [
										BX.create("SPAN", {
												props: {className: "bx-ilike-popup-avatar-new"},
												children: [
													avatarNode,
													BX.create("SPAN", {
														props: {className: "bx-ilike-popup-avatar-status-icon"}
													})
												]
											}
										),
										BX.create("SPAN", {
												props: {className: "bx-ilike-popup-name-new"},
												html : data.items[i]['FULL_NAME']
											}
										)
									]
								})
							);
						}
					}
				}
				if (this.popup.isNew)
				{
					this.popup.isNew = false;
					if (!!res1)
					{
						try
						{
							res1.removeChild(res1.firstChild);
						}
						catch(e)
						{

						}
						res1.appendChild(res);
					}
				}

				this.adjustWindow();
				if (needToCheckData)
					this.popupScroll();
				return true;
			},
			show : function() {
				if (this.popup != null && this.node.id != this.popup.nodeID)
					this.popup.close();

				if (this.popup == null)
				{
					this.popup = new BX.PopupWindow('bx-vote-popup-cont-' + this.CID, this.node, {
						lightShadow : true,
						offsetTop: -2,
						offsetLeft: 3,
						autoHide: true,
						closeByEsc: true,
						bindOptions: {position: "top"},
						events : {
							onPopupClose : function() { this.destroy() },
							onPopupDestroy : BX.proxy(function() { this.popup = null; }, this)
						},
						content : BX.create("SPAN", { props: {className: "bx-ilike-wait"}})
					});

					this.popup.nodeID = this.node.id;
					this.popup.isNew = true;
					this.popup.show();
				}
				this.popup.setAngle({position:'bottom'});
				this.adjustWindow();
			},
			adjustWindow : function() {
				if (this.popup != null)
				{
					this.popup.bindOptions.forceBindPosition = true;
					this.popup.adjustPosition();
					this.popup.bindOptions.forceBindPosition = false;
				}
			},
			popupScroll : function() {
				if (this.popup)
				{
					var res = BX.findChild(this.popup.contentContainer, {"className" : "bx-ilike-popup"}, true);
					BX.bind(res, 'scroll' , BX.proxy(this.popupScrollCheck, this));
				}
			},
			popupScrollCheck : function() {
				var res = BX.proxy_context;
				if (res.scrollTop > (res.scrollHeight - res.offsetHeight) / 1.5)
				{
					BX.unbind(res, 'scroll' , BX.proxy(this.popupScrollCheck, this));
					this.get(this.popup.bindElement);
				}
			},
			get : function(node) {
				this.node = (!!node ? node : BX.proxy_context);
				if (!this.node)
					return false;
				if (!this.node.getAttribute("id"))
					this.node.setAttribute("id", this.getID());
				if ((!this.node.getAttribute("rel") && !this.node.getAttribute("rev")) || parseInt(this.node.innerHTML) <= 0)
					return false;

				if (this.node.getAttribute("status") === "busy")
					return false;
				if (!this.node.getAttribute("inumpage"))
					this.node.setAttribute("inumpage", "1");
				else if (this.node.getAttribute("inumpage") != "done")
					this.node.setAttribute("inumpage", (parseInt(this.node.getAttribute("inumpage")) + 1) + "");

				this.show();

				if (this.data[this.node.getAttribute("id")])
					this.make(this.data[this.node.getAttribute("id")], (this.node.getAttribute("inumpage") != "done"));

				if (this.node.getAttribute("inumpage") != "done")
				{
					this.node.setAttribute("status", "busy");
					BX.ajax({
						url: "/bitrix/components/bitrix/voting.current/templates/.userfield/users.php",
						method: 'POST',
						dataType: 'json',
						data: {
							'ID' : this.node.getAttribute("rel"),
							'answer_id'  : this.node.getAttribute("rev"),
							'request_id' : this.node.getAttribute("id"),
							'iNumPage' : this.node.getAttribute("inumpage"),
							'URL_TEMPLATE' : this.urlTemplate,
							'NAME_TEMPLATE' : this.nameTemplate,
							'sessid': BX.bitrix_sessid()
						},
						onsuccess: BX.proxy(function(data) {
							if (!!data && !!data.items)
							{
								data["StatusPage"] = (!!data["StatusPage"] ? data["StatusPage"] : false);
								if (data.StatusPage == "done" || data.items.length <= 0)
									this.node.setAttribute("inumpage", "done");
								var res, items = (this.data[this.node.getAttribute("id")] ? this.data[this.node.getAttribute("id")]["items"] : []);
								for (res=0; res<data.items.length; res++)
								{
									items.push(data.items[res]);
								}

								this.data[this.node.getAttribute("id")] = data;
								this.data[this.node.getAttribute("id")]["items"] = items;

								this.make(this.data[this.node.getAttribute("id")], (this.node.getAttribute("inumpage") != "done"));
							}
							this.node.setAttribute("status", "ready");
						}, this),
						onfailure: BX.proxy(function() { this.node.setAttribute("status", "ready"); }, this)
					});
				}
				return true;
			},
			send : function() {
				if (this.status === "ready")
				{
					this.status = "busy";
					BX.ajax({
						url: this.url.replace(/.AJAX_RESULT=Y/g,'').
						replace(/.AJAX_POST=Y/g,'').
						replace(/.sessid=[^&]*/g, '').
						replace(/.VOTE_ID=([\d]+)/,'').
						replace(/.view_form=Y/g, '').
						replace(/.view_result=Y/g, ''),
						method: 'POST',
						dataType: 'json',
						data: {
							'VOTE_ID' : this.voteId,
							'AJAX_RESULT' : 'Y',
							'view_result' : 'Y',
							'sessid': BX.bitrix_sessid()
						},
						onsuccess: BX.proxy(function(data) { this.changeData(data);this.status = "ready"; }, this),
						onfailure: BX.proxy(function() { this.status = "ready"; }, this)
					});
				}
			},
			changeData : function(data) {
				data = data["QUESTIONS"];
				BX.onCustomEvent(this.controller, 'OnBeforeChangeData');
				var question, answer, i, q, per;
				for (q in data)
				{
					if (data.hasOwnProperty(q))
					{
						question = BX.findChild(this.controller, {"attr" : {"id" : "question" + q}}, true);
						if (!!question)
						{
							for (i in data[q])
							{
								if (data[q].hasOwnProperty(i))
								{
									answer = BX.findChild(question, {"attr" : {"id" : ("answer" + i)}}, true);
									if (!!answer)
									{
										per = parseInt(data[q][i]["PERCENT"]);
										per = (isNaN(per) ? 0 : per);
										BX.adjust(BX.findChild(answer, {"tagName" : "A", "className" : "bx-vote-voted-users"}, true),
											{"attrs" : {"id" : "", "rel" : data[q][i]["USERS"], "rev" : i, "inumpage" : false},
												"html" : data[q][i]["COUNTER"]});
										BX.adjust(BX.findChild(answer, {"tagName" : "SPAN", "className" : "bx-vote-data-percent"}, true),
											{"html" : per + '%'});
										BX.adjust(BX.findChild(answer, {"tagName" : "DIV", "className" : "bx-vote-result-bar"}, true),
											{"style" : {"width" : per + '%'}});
									}
								}
							}
						}
					}
				}
				BX.onCustomEvent(this.controller, 'OnAfterChangeData');
			}
		};
		return d;
	})();
})(window);