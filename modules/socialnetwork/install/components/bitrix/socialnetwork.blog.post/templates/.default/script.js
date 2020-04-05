function showHiddenDestination(cont, el)
{
	BX.hide(el);
	BX('blog-destination-hidden-'+cont).style.display = 'inline';
}

function showMenuLinkInput(ind, url)
{
	var
		id = 'post-menu-' + ind + '-link',
		menuItemText = BX(id + '-text'),
		menuItemIconDone = BX(id + '-icon-done');

	if (BX.clipboard.isCopySupported())
	{
		if (menuItemText && menuItemText.getAttribute('data-block-click') == 'Y')
		{
			return;
		}

		BX.clipboard.copy(url);

		if (
			menuItemText
			&& menuItemIconDone
		)
		{
			menuItemIconDone.style.display = 'inline-block';
			BX.removeClass(BX(id + '-icon-animate'), 'post-menu-link-icon-animate');

			BX.adjust(menuItemText, {
				attrs: {
					'data-block-click': 'Y'
				}
			});

			setTimeout(function() {
				BX.addClass(BX(id + '-icon-animate'), 'post-menu-link-icon-animate');
			}, 1);

			setTimeout(function() {
				BX.adjust(menuItemText, {
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
				pos2 = BX.pos(node.parentNode);
				pos3 = BX.pos(BX.findParent(node, {'className': 'menu-popup-item'}, true));

			pos["height"] = pos2["height"] - 1;

			BX.adjust(it, {
				attrs : {"bx-height" : it.offsetHeight},
				style : { 
					overflow : "hidden", 
					display : 'block'
				},
				children : [
					BX.create('BR'),
					BX.create('DIV', { 
						attrs : {id : id},
						children : [
							BX.create('SPAN', {attrs : {"className" : "menu-popup-item-left"}}),
							BX.create('SPAN', {attrs : {"className" : "menu-popup-item-icon"}}),
							BX.create('SPAN', {attrs : {"className" : "menu-popup-item-text"},
								children : [
									BX.create('INPUT', {
											attrs : {
												id : id + '-input',
												type : "text",
												value : url 
											},
											style : {
												height : pos["height"] + 'px',
												width : (pos3["width"] - 21) + 'px'
											},
											events : { click : function(e){
												this.select();
												BX.PreventDefault(e);
											} }
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
		(new BX.fx({
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
		(new BX.fx({
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

function deleteBlogPost(id)
{
	var
		el = BX('blg-post-'+id);

	if(BX.findChild(el, {'attr': {id: 'form_c_del'}}, true, false))
	{
		BX.hide(BX('form_c_del'));
		BX(el.parentNode.parentNode).appendChild(BX('form_c_del')); // Move form
	}

	BX.ajax.get(BX.message('sonetBPDeletePath').replace('#del_post_id#', id), function(data){
		if(
			window.deletePostEr
			&& window.deletePostEr == "Y"
		)
		{
			BX.findChild(el, {className: 'feed-post-cont-wrap'}, true, false).insertBefore(
				BX.create('SPAN', {
					html: data
				}),
				BX.findChild(el, {className: 'feed-user-avatar'}, true, false)
			);
		}
		else
		{
			BX('blg-post-'+id).parentNode.innerHTML = data;
		}
	});

	return false;
}

var waitPopupBlogImage = null;
function blogShowImagePopup(src)
{
	if(!waitPopupBlogImage)
	{
		waitPopupBlogImage = new BX.PopupWindow('blogwaitPopupBlogImage', window, {
			autoHide: true,
			lightShadow: false,
			zIndex: 2,
			content: BX.create('IMG', {props: {src: src, id: 'blgimgppp'}}),
			closeByEsc: true,
			closeIcon: true
		});
	}
	else
	{
		BX('blgimgppp').src = '/bitrix/images/1.gif';
		BX('blgimgppp').src = src;
	}

	waitPopupBlogImage.setOffset({
		offsetTop: 0,
		offsetLeft: 0
	});

	setTimeout(function(){waitPopupBlogImage.adjustPosition()}, 100);	
	waitPopupBlogImage.show();

}

function __blogPostSetFollow(log_id)
{
	var strFollowOld = (BX("log_entry_follow_" + log_id, true).getAttribute("data-follow") == "Y" ? "Y" : "N");
	var strFollowNew = (strFollowOld == "Y" ? "N" : "Y");	

	if (BX("log_entry_follow_" + log_id, true))
	{
		BX.findChild(BX("log_entry_follow_" + log_id, true), { tagName: 'a' }).innerHTML = BX.message('sonetBPFollow' + strFollowNew);
		BX("log_entry_follow_" + log_id, true).setAttribute("data-follow", strFollowNew);
	}

	BX.ajax({
		url: BX.message('sonetBPSetPath'),
		method: 'POST',
		dataType: 'json',
		data: {
			"log_id": log_id,
			"action": "change_follow",
			"follow": strFollowNew,
			"sessid": BX.bitrix_sessid(),
			"site": BX.message('sonetBPSiteId')
		},
		onsuccess: function(data) {
			if (
				data["SUCCESS"] != "Y"
				&& BX("log_entry_follow_" + log_id, true)
			)
			{
				BX.findChild(BX("log_entry_follow_" + log_id, true), { tagName: 'a' }).innerHTML = BX.message('sonetBPFollow' + strFollowOld);
				BX("log_entry_follow_" + log_id, true).setAttribute("data-follow", strFollowOld);
			}
		},
		onfailure: function(data) {
			if (BX("log_entry_follow_" +log_id, true))
			{
				BX.findChild(BX("log_entry_follow_" + log_id, true), { tagName: 'a' }).innerHTML = BX.message('sonetBPFollow' + strFollowOld);
				BX("log_entry_follow_" + log_id, true).setAttribute("data-follow", strFollowOld);
			}		
		}
	});
	return false;
}

(function() {
	if (!!BX.SBPostMenu)
		return false;

	BX.SBPostMenu = function(node) {
	};

	BX.SBPostMenu.showMenu = function(params) {
		if (
			typeof params == 'undefined'
			|| typeof params.postId == 'undefined'
			|| parseInt(params.postId) <= 0
			|| typeof params.bindNode == 'undefined'
			|| !BX(params.bindNode)
		)
		{
			return false;
		}

		BX.PopupMenu.destroy('blog-post-' + params.postId);

		var
			isPublicPage = (typeof params.publicPage != 'undefined' && !!params.publicPage),
			isTasksAvailable = (typeof params.tasksAvailable != 'undefined' && !!params.tasksAvailable),
			pathToPost = (typeof params.pathToPost != 'undefined' ? params.pathToPost : ''),
			urlToEdit = (typeof params.urlToEdit != 'undefined' ? params.urlToEdit : ''),
			urlToHide = (typeof params.urlToHide != 'undefined' ? params.urlToHide : ''),
			urlToDelete = (typeof params.urlToDelete != 'undefined' ? params.urlToDelete : ''),
			voteId = (typeof params.voteId != 'undefined' ? parseInt(params.voteId) : false),
			postType = (typeof params.postType != 'undefined' ? params.postType : false);

		if (isPublicPage)
		{
			return false;
		}

		var menuWaiterPopup = new BX.PopupWindow('blog-post-' + params.postId + '-waiter', params.bindNode, {
			offsetLeft: -14,
			offsetTop: 4,
			lightShadow: false,
			angle: {position: 'top', offset: 50},
			content: BX.create("SPAN", { props: {className: "bx-ilike-wait"}})
		});

		setTimeout(function() {
			if (menuWaiterPopup)
			{
				menuWaiterPopup.show();
			}
		}, 300);


		BX.ajax({
			url: '/bitrix/components/bitrix/socialnetwork.blog.post/ajax.php',
			method: 'POST',
			dataType: 'json',
			data: {
				sessid : BX.bitrix_sessid(),
				siteId : BX.message('SITE_ID'),
				action : 'get_data',
				postId : parseInt(params.postId),
				public : (isPublicPage ? 'Y' : 'N'),
				mobile : 'N',
				group_readonly : (typeof params.group_readonly != 'undefined' && !!params.group_readonly ? 'Y' : 'N'),
				pathToPost : pathToPost,
				voteId: voteId
			},
			onsuccess: function(postData) {
				if (
					typeof postData == 'undefined'
					|| typeof postData.perms == 'undefined'
					|| (
						postData.perms <= 'D' // \Bitrix\Blog\Item\Permissions::DENY
						&& (
							typeof params.items == 'undefined'
							|| params.items.length <= 0
						)
					)
				)
				{
					menuWaiterPopup.destroy();
					return false;
				}

				var menuItems = [];

				if(!BX.util.in_array(postType, ["DRAFT", "MODERATION"]))
				{
					if (
						postData.isGroupReadOnly != 'Y'
						&& parseInt(BX.message('USER_ID')) > 0
						&& (parseInt(postData.logId) > 0)
					)
					{
						var isFavorites = (parseInt(postData.logFavoritesUserId) > 0);
						menuItems.push({
							text: BX.message(isFavorites ? "sonetLMenuFavoritesTitleY" : "sonetLMenuFavoritesTitleN"),
							onclick: function(e) {
								__logChangeFavorites(
									parseInt(postData.logId),
									'log_entry_favorites_' + parseInt(postData.logId),
									(isFavorites ? 'N' : 'Y'),
									true
								);
								return false;
							}
						});
					}

					var serverName = params.serverName;

					menuItems.push({
						text: BX.message('BLOG_HREF'),
						href: postData.urlToPost,
						class: 'feed-entry-popup-menu-link'
					});

					menuItems.push({
						text: '<span id="post-menu-' + postData.logId + '-link-text">' + BX.message('BLOG_LINK') + '</span>' +
							'<span id="post-menu-' + postData.logId + '-link-icon-animate" class="post-menu-link-icon-wrap">' +
								'<span class="post-menu-link-icon" id="post-menu-' + postData.logId + '-link-icon-done" style="display: none;">' +

								'</span>' +
							'</span>',
						onclick: function(e) {
							showMenuLinkInput(
								parseInt(postData.logId),
								serverName + postData.urlToPost
							);
							return false;
						},
						class: 'feed-entry-popup-menu-link'
					});

					if (
						parseInt(BX.message('USER_ID')) > 0
						&& postData.isGroupReadOnly != 'Y'
						&& postData.isShareForbidden != 'Y'
					)
					{
						menuItems.push({
							text: BX.message('BLOG_SHARE'),
							onclick: function() {
								showSharing(
									parseInt(params.postId),
									parseInt(postData.authorId)
								);
								this.popupWindow.close();
							}
						});
					}

					if (
						postData.perms >= 'W' // \Bitrix\Blog\Item\Permissions::FULL
						|| (
							postData.perms >= 'P' // \Bitrix\Blog\Item\Permissions::WRITE
							&& postData.authorId == BX.message('USER_ID')
						)
					)
					{
						menuItems.push({
							text: BX.message('BLOG_BLOG_BLOG_EDIT'),
							href: urlToEdit
						});
					}

					if(postData.perms >= 'T') // \Bitrix\Blog\Item\Permissions::MODERATE
					{
						menuItems.push({
							text: BX.message('BLOG_MES_HIDE'),
							onclick: function() {
								if(confirm(BX.message('BLOG_MES_HIDE_POST_CONFIRM')))
								{
									window.location = urlToHide;
									this.popupWindow.close();
								}
							}
						});
					}

					if (postData.perms >= 'W') //  // \Bitrix\Blog\Item\Permissions::FULL
					{
						menuItems.push({
							text: BX.message('BLOG_BLOG_BLOG_DELETE'),
							onclick: function() {
								if (confirm(BX.message('BLOG_MES_DELETE_POST_CONFIRM')))
								{
									if (urlToDelete.length > 0)
									{
										window.location = urlToDelete.replace('#del_post_id#', parseInt(params.postId));
									}
									else
									{
										window.deleteBlogPost(parseInt(params.postId));
									}
									this.popupWindow.close();
								}
							}
						});
					}

					if (
						isTasksAvailable
						&& postData.perms > 'D'
					)
					{
						menuItems.push({
							text: BX.message('BLOG_POST_CREATE_TASK'),
							onclick: function(e) {
								var target = e.target || e.srcElement;

								oLF.createTask({
									entityType: 'BLOG_POST',
									entityId: parseInt(params.postId)
								});
								this.popupWindow.close();

								return BX.PreventDefault(e);
							}
						});
					}

					if (postData.urlToVoteExport.length > 0)
					{
						menuItems.push({
							text: BX.message('BLOG_POST_VOTE_EXPORT'),
							href: postData.urlToVoteExport
						});
					}
				}

				var
					onclickHandler = null,
					menuItem = null,
					item = null;

				if (typeof params.items != 'undefined')
				{
					for (var key in params.items)
					{
						if (params.items.hasOwnProperty(key))
						{
							item = params.items[key];

							menuItem = {};
							if (typeof item.text_php != 'undefined')
							{
								menuItem.text = item.text_php;
							}

							if (typeof item.onclick != 'undefined')
							{
								eval("onclickHandler = " + item.onclick);
								menuItem.onclick = onclickHandler;
							}
							else if (typeof item.href != 'undefined')
							{
								menuItem.href = item.href;
							}

							menuItems.push(menuItem);
						}
					}
				}

				var popupEvents = (
					typeof params.logId != 'undefined' && parseInt(params.logId) > 0
						? {
							onPopupShow : function(ob)
							{
								if (BX('log_entry_favorites_' + parseInt(params.logId)))
								{
									var menuItems = BX.findChildren(ob.contentContainer, {'className' : 'menu-popup-item-text'}, true);
									if (menuItems != null)
									{
										for (var i = 0; i < menuItems.length; i++)
										{
											if (
												menuItems[i].innerHTML == BX.message('sonetLMenuFavoritesTitleY')
												|| menuItems[i].innerHTML == BX.message('sonetLMenuFavoritesTitleN')
											)
											{
												var favoritesMenuItem = menuItems[i];
												break;
											}
										}
									}

									if (typeof favoritesMenuItem != 'undefined')
									{
										BX(favoritesMenuItem).innerHTML = (
											BX.hasClass(BX('log_entry_favorites_' + parseInt(params.logId)), 'feed-post-important-switch-active')
												? BX.message('sonetLMenuFavoritesTitleY')
												: BX.message('sonetLMenuFavoritesTitleN')
										);
									}
								}

								if (BX('post-menu-' + parseInt(params.logId) + '-link'))
								{
									var linkMenuItem = BX.findChild(ob.popupContainer, {className: 'feed-entry-popup-menu-link'}, true, false);
									if (linkMenuItem)
									{
										var height = parseInt(!!linkMenuItem.getAttribute('bx-height') ? linkMenuItem.getAttribute('bx-height') : 0);
										if (height > 0)
										{
											BX('post-menu-' + parseInt(params.logId) + '-link').style.display = 'none';
											linkMenuItem.setAttribute('bx-status', 'hidden');
											linkMenuItem.style.height = height + 'px';
										}
									}
								}
							}
						}
						: {}
				);

				menuWaiterPopup.destroy();
				BX.PopupMenu.show('blog-post-' + params.postId, params.bindNode, menuItems,
					{
						offsetLeft: -14,
						offsetTop: 4,
						lightShadow: false,
						angle: {position: 'top', offset: 50},
						events: popupEvents
					});
				return false;
			},
			onfailure: function(data) {
				menuWaiterPopup.destroy();
				return false;
			}
		});
	};

}());

(function() {
	if (!!window.SBPImpPost)
		return false;
	window.SBPImpPost = function(node) {
		if (node.getAttribute("sbpimppost") == "Y")
			return false;
		this.CID = 'sbpimppost' + new Date().getTime();
		this.busy = false;

		this.node = node;
		this.btn = node.parentNode;
		this.block = node.parentNode.parentNode;

		this.postId = node.getAttribute("bx-blog-post-id");
		node.setAttribute("sbpimppost", "Y");

		BX.onCustomEvent(this.node, "onInit", [this]);
		if (this.postId > 0)
			this.onclick();

		return false;
	};
	window.SBPImpPost.prototype.onclick = function(){
		this.sendData();
	};
	window.SBPImpPost.prototype.showClick = function(){
		var start_anim = this.btn.offsetWidth,
			text = BX.message('BLOG_ALREADY_READ'),
			text_block = BX.create('span',{ props:{className:'have-read-text-block'}, html:'<i></i>' + text + '<span class="feed-imp-post-footer-comma">,</span>' });

		this.block.style.minWidth =  this.btn.offsetWidth-27 + 'px';

		var easing = new BX.easing({
			duration : 250,
			start : { width : start_anim },
			finish : { width : 1 },
			transition : BX.easing.makeEaseOut(BX.easing.transitions.quad),
			step : BX.delegate(function(state) { this.btn.style.width = state.width +'px' }, this),
			complete : BX.delegate(function(){
				this.btn.innerHTML = '';
				this.btn.appendChild(text_block);
				var width_2 = text_block.offsetWidth,
					easing_2 = new BX.easing({
						duration : 300,
						start : { width_2:0 },
						finish : { width_2:width_2 },
						transition : BX.easing.makeEaseOut(BX.easing.transitions.quad),
						step : BX.delegate(function(state){ this.btn.style.width = state.width_2 + 'px'; }, this)
					});
					easing_2.animate();
				}, this)
		});
		easing.animate();
	};
	window.SBPImpPost.prototype.wait = function(status){
		status = (status == 'show' ? 'show' : 'hide');
		if (status == 'show')
		{
			this.node.disabled = true;
			BX.addClass(this.node, 'ui-btn-clock');
		}
		else
		{
			BX.removeClass(this.node, 'ui-btn-clock');
		}
	};
	window.SBPImpPost.prototype.sendData = function(){
		if (this.busy)
			return false;
		this.busy = true;
		window['node'] = this.node;
		window['obj'] = this;
		this.wait('show');
		var data = {
			options : [{ post_id : this.postId, name : "BLOG_POST_IMPRTNT", value : "Y"}],
			sessid : BX.bitrix_sessid()},
			url = this.node.getAttribute('bx-url');

		BX.onCustomEvent(this.node, "onSend", [data]);
		data = BX.ajax.prepareData(data);
		if (data)
		{
			url += (url.indexOf('?') !== -1 ? "&" : "?") + data;
			data = '';
		}

		BX.ajax({
			'method': 'GET',
			'url': url,
			'dataType': 'json',
			'onsuccess': BX.delegate(function(data){
				this.busy = false;
				this.wait('hide');
				this.showClick();
				BX.onCustomEvent(this.node, "onUserVote", [data]);
				BX.onCustomEvent("onImportantPostRead", [this.postId, this.CID]);
			}, this),
			'onfailure': BX.delegate(function(data){ this.busy = false; this.wait('hide');}, this)
		});
	};

	top.SBPImpPostCounter = function(node, postId, params) {
		this.parentNode = node;
		this.node = BX.findChild(node, {"tagName" : "A"});
		if (!this.node)
			return false;

		BX.addCustomEvent(this.node, "onUserVote", BX.delegate(function(data){this.change(data);}, this));

		this.parentNode.SBPImpPostCounter = this;

		this.node.setAttribute("status", "ready");
		this.node.setAttribute("inumpage", 0);

		this.postId = postId;
		this.popup = null;
		this.data = [];
		BX.bind(node, "click", BX.proxy(function(){ this.get(); }, this));
		BX.bind(node, "mouseover", BX.proxy(function(e){this.init(e);}, this));
		BX.bind(node, "mouseout", BX.proxy(function(e){this.init(e);}, this));

		this.pathToUser = params['pathToUser'];
		this.nameTemplate = params['nameTemplate'];

		this.onPullEvent = BX.delegate(function(command, params){
			if (command == 'read' && !!params && params["POST_ID"] == this.postId)
			{
				if (!!params["data"])
				{
					this.change(params["data"]);
					if (this.popup != null)
					{
						this.popup.isNew = true;
					}
				}
			}
		}, this);
		BX.addCustomEvent("onPullEvent-socialnetwork", this.onPullEvent);
	};
	top.SBPImpPostCounter.prototype.click = function(obj) {
		obj.uController = this;
		BX.addCustomEvent(obj.node, "onUserVote", BX.proxy(this.change, this));
		BX.addCustomEvent(obj.node, "onSend", BX.proxy(function(data){
			data["PATH_TO_USER"] = this.pathToUser;
			data["NAME_TEMPLATE"] = this.nameTemplate;
			data["iNumPage"] = 0;
			data["ID"] = this.postId;
			data["post_id"] = this.postId;
			data["name"] = "BLOG_POST_IMPRTNT";
			data["value"] = "Y";
			data["return"] = "users";
		}, this));
		this.btnObj = obj;
	};

	top.SBPImpPostCounter.prototype.change = function(data) {
		if (!!data && !!data.items)
		{
			var res = false;
			this.data = [];
			for (var ii in data.items)
			{
				if (data.items.hasOwnProperty(ii))
				{
					this.data.push(data.items[ii]);
				}
			}
			if (data["StatusPage"] == "done")
			{
				this.node.setAttribute("inumpage", "done");
			}
			else
				this.node.setAttribute("inumpage", 1);
			BX.adjust(this.parentNode, {style : {display : "inline-block"}});
		}
		else
		{
			this.node.setAttribute("inumpage", "done");
			BX.hide(this.parentNode);
		}
		this.node.firstChild.innerHTML = data["RecordCount"];
	};
	top.SBPImpPostCounter.prototype.init = function(e) {
		if (!!this.node.timeoutOver){
			clearTimeout(this.node.timeoutOver);
			this.node.timeoutOver = false;
		}
		if (e.type == 'mouseover'){
			if (!this.node.mouseoverFunc) {
				this.node.mouseoverFunc = BX.delegate(function(){
					this.get();
					if (this.popup){
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
												if (!!this.popup) {
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
				}, this)
			}
			this.node.timeoutOver = setTimeout(this.node.mouseoverFunc, 400);
		}
	};

	top.SBPImpPostCounter.prototype.get = function() {
		if (this.node.getAttribute("inumpage") != "done")
			this.node.setAttribute("inumpage", (parseInt(this.node.getAttribute("inumpage")) + 1));
		this.show();
		if (this.data.length > 0) {
			this.make((this.node.getAttribute("inumpage") != "done"));
		}

		if (this.node.getAttribute("inumpage") != "done")
		{
			this.node.setAttribute("status", "busy");
			BX.ajax({
				url: "/bitrix/components/bitrix/socialnetwork.blog.blog/users.php",
				method: 'POST',
				dataType: 'json',
				data: {
					'ID' : this.postId,
					'post_id' : this.postId,
					'name' : "BLOG_POST_IMPRTNT",
					'value' : "Y",
					'iNumPage' : this.node.getAttribute("inumpage"),
					'PATH_TO_USER' : this.pathToUser,
					'NAME_TEMPLATE' : this.nameTemplate,
					'sessid': BX.bitrix_sessid(),
					'lang': BX.message('LANGUAGE_ID'),
					'site': BX.message('SITE_ID')
				},
				onsuccess: BX.proxy(function(data){
					if (!!data && !!data.items)
					{
						var res = false;
						for (var ii in data.items) {
							this.data.push(data.items[ii]);
						}
						if (data.StatusPage == "done")
						{
							this.node.setAttribute("inumpage", "done");
						}

						this.make((this.node.getAttribute("inumpage") != "done"));
					}
					else
					{
						this.node.setAttribute("inumpage", "done");
					}
					this.node.firstChild.innerHTML = data["RecordCount"];
					this.node.setAttribute("status", "ready");
				}, this),
				onfailure: BX.proxy(function(data){ this.node.setAttribute("status", "ready"); }, this)
			});
		}
	};
	top.SBPImpPostCounter.prototype.show = function()
	{
		if (this.popup == null)
		{
			this.popup = new BX.PopupWindow('bx-vote-popup-cont-' + this.postId, this.node, {
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

			this.popup.isNew = true;
			this.popup.show();
		}
		this.popup.setAngle({position:'bottom'});

		this.popup.bindOptions.forceBindPosition = true;
		this.popup.adjustPosition();
		this.popup.bindOptions.forceBindPosition = false;
	};
	top.SBPImpPostCounter.prototype.make = function(needToCheckData)
	{
		if (!this.popup)
			return true;
		needToCheckData = (needToCheckData !== false);

		var
			res1 = (this.popup && this.popup.contentContainer ? this.popup.contentContainer : BX('popup-window-content-bx-vote-popup-cont-' + this.postId)),
			node = false, res = false, data = this.data;
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
		if (!!node)
		{
			var avatarNode = null;

			for (var i in data)
			{
				if (data.hasOwnProperty(i))
				{
					if (!BX.findChild(node, {tag : "A", attr : {id : ("u" + data[i]['ID'])}}, true))
					{
						if (data[i]['PHOTO_SRC'].length > 0)
						{
							avatarNode = BX.create("IMG", {
								attrs: {src: data[i]['PHOTO_SRC']},
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
								attrs : {
									id : ("u" + data[i]['ID'])
								},
								props: {
									href: (data[i]['URL'].length > 0 ? data[i]['URL'] : '#'),
									target: "_blank",
									className: "bx-ilike-popup-img" + (!!data[i]['TYPE'] ? " bx-ilike-popup-img-" + data[i]['TYPE'] : "")
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
											html : data[i]['FULL_NAME']
										}
									)
								],
								events: {
									click: (
										data[i]['URL'].length > 0
											? function(e) { return true; }
											: function(e) { BX.PreventDefault(e); }
									)
								}
							})
						);
					}
				}
			}
			if (needToCheckData)
			{
				BX.bind(node, 'scroll' , BX.proxy(this.popupScrollCheck, this));
			}
		}
		if (this.popup.isNew)
		{
			this.popup.isNew = false;
			if (!!res1)
			{
				try{
					res1.removeChild(res1.firstChild);
				} catch(e) {}
				res1.appendChild(res);
			}
		}
		if (this.popup != null)
		{
			this.popup.bindOptions.forceBindPosition = true;
			this.popup.adjustPosition();
			this.popup.bindOptions.forceBindPosition = false;
		}
	};

	top.SBPImpPostCounter.prototype.popupScrollCheck = function()
	{
		var res = BX.proxy_context;
		if (res.scrollTop > (res.scrollHeight - res.offsetHeight) / 1.5)
		{
			BX.unbind(res, 'scroll' , BX.proxy(this.popupScrollCheck, this));
			this.get();
		}
	}
})(window);

window.BXfpdPostSelectCallback = function(item, type, search)
{
	BX.SocNetLogDestination.BXfpSelectCallback({
		item: item,
		type: type,
		bUndeleted: false,
		containerInput: BX('feed-add-post-destination-item-post'),
		valueInput: BX('feed-add-post-destination-input-post'),
		formName: BXSocNetLogDestinationFormNamePost,
		tagInputName: 'bx-destination-tag-post',
		tagLink1: BX.message('BX_FPD_LINK_1'),
		tagLink2: BX.message('BX_FPD_LINK_2')
	});
};

window.BXfpdPostClear = function()
{
	var elements = BX.findChildren(BX('feed-add-post-destination-item-post'), {className : 'feed-add-post-destination'}, true);
	if (elements != null)
	{
		for (var j = 0; j < elements.length; j++)
		{
			BX.remove(elements[j]);
		}
	}
	BX('feed-add-post-destination-input-post').value = '';

	BX.SocNetLogDestination.BXfpSetLinkName({
		formName: window.BXSocNetLogDestinationFormNamePost,
		tagInputName: 'bx-destination-tag-post',
		tagLink1: BX.message('BX_FPD_LINK_1'),
		tagLink2: BX.message('BX_FPD_LINK_2')
	});
};

window.showSharing = function(postId, userId)
{
	BXfpdPostClear();
	BX('sharePostId').value = postId;
	BX('shareUserId').value = userId;

	BX.SocNetLogDestination.obItemsSelected[BXSocNetLogDestinationFormNamePost] = {};
	if(window["postDest"+postId])
	{
		for (var i = 0; i < window["postDest"+postId].length; i++) 
		{
			if(BX.SocNetLogDestination.obItemsSelected[BXSocNetLogDestinationFormNamePost])
			{
				BX.SocNetLogDestination.obItemsSelected[BXSocNetLogDestinationFormNamePost][window["postDest"+postId][i].id] = window["postDest"+postId][i].type;
			}

			if(!BX.SocNetLogDestination.obItems[BXSocNetLogDestinationFormNamePost][window["postDest"+postId][i].type][window["postDest"+postId][i].id])
			{
				BX.SocNetLogDestination.obItems[BXSocNetLogDestinationFormNamePost][window["postDest"+postId][i].type][window["postDest"+postId][i].id] = {
					avatar: '', entityId: window["postDest"+postId][i].entityId, id: window["postDest"+postId][i].id, name: window["postDest"+postId][i].name
				};
			}
		}

		if(BXSocNetLogDestinationFormNamePost)
			BX.SocNetLogDestination.reInit(BXSocNetLogDestinationFormNamePost);

		var elements = BX.findChildren(BX('feed-add-post-destination-item-post'), {className : 'feed-add-post-destination'}, true);
		if (elements != null)
		{
			for (var j = 0; j < elements.length; j++)
			{
				BX.addClass(elements[j], 'feed-add-post-destination-undelete');
				BX.remove(elements[j].lastChild);
			}
		}

		var destForm = BX('destination-sharing');

		if (BX('blg-post-destcont-'+postId))
		{
			BX('blg-post-destcont-'+postId).appendChild(destForm);
		}

		destForm.style.height = 0;
		destForm.style.opacity = 0;
		destForm.style.overflow = 'hidden';
		destForm.style.display = 'inline-block';

		(new BX.easing({
			duration : 500,
			start : { opacity : 0, height : 0},
			finish : { opacity: 100, height : destForm.scrollHeight-40},
			transition : BX.easing.makeEaseOut(BX.easing.transitions.quad),
			step : function(state){
				destForm.style.height = state.height + "px";
				destForm.style.opacity = state.opacity / 100;
			},
			complete : function(){
				destForm.style.cssText = '';
				BX.SocNetLogDestination.openDialog(BXSocNetLogDestinationFormNamePost);
			}
		})).animate();
	}
};

window.closeSharing = function()
{
	var destForm = BX('destination-sharing');

	if (BX('sharePostSubmitButton'))
	{
		BX.removeClass(BX('sharePostSubmitButton'), 'ui-btn-clock');
	}

	(new BX.easing({
		duration : 500,
		start : { opacity: 100, height : destForm.scrollHeight-40},
		finish : { opacity : 0, height : 0},
		transition : BX.easing.makeEaseOut(BX.easing.transitions.quad),
		step : function(state){
			destForm.style.height = state.height + "px";
			destForm.style.opacity = state.opacity / 100;
		},
		complete : function(){
			BX.hide(destForm);
		}
	})).animate();
};

window.sharingPost = function()
{
	var postId = BX('sharePostId').value;
	var userId = BX('shareUserId').value;
	var shareForm = BX('blogShare');
	var actUrl = socBPDest.shareUrl.replace(/#post_id#/, postId).replace(/#user_id#/, userId);

	if (BX('sharePostSubmitButton'))
	{
		BX.addClass(BX('sharePostSubmitButton'), 'ui-btn-clock');
	}

	shareForm.action = actUrl;
	shareForm.target = '';

	var i, s = "";
	var n = shareForm.elements.length;

	var delim = '';
	for(i=0; i<n; i++)
	{
		if (s != '') delim = '&';
		var el = shareForm.elements[i];
		if (el.disabled)
			continue;

		switch(el.type.toLowerCase())
		{
			case 'text':
			case 'hidden':
				s += delim + el.name + '=' + BX.util.urlencode(el.value);
				break;
			default:
				break;
		}
	}
	s += "&save=Y&MODE=RECORD&AJAX_POST=Y&ENTITY_XML_ID=BLOG_" + postId;

	var newNodes = renderSharingPost(postId);

	BX.ajax({
		'method': 'POST',
		'dataType': 'json',
		'url': actUrl,
		'data': s,
//		'async': true,
//		'processData': false,
		'onsuccess': function(data)
		{
			if (
				typeof data == 'undefined'
				|| typeof data.status == 'undefined'
				|| data.status != 'success'
			)
			{
				hideRenderedSharingNodes(newNodes);
				if (
					typeof data.status != 'undefined'
					&& data.status == 'error'
					&& typeof data.errorMessage != 'undefined'
				)
				{
					var errorPopup = new BX.PopupWindow('error_popup', BX('blg-post-inform-' + postId), {
						lightShadow : true,
						offsetTop: -10,
						offsetLeft: 100,
						autoHide: true,
						closeByEsc: true,
						closeIcon: {
							right : "5px",
							top : "5px"
						},
						draggable: {
							restrict:true
						},
						contentColor : 'white',
						contentNoPaddings: true,
						bindOptions: {position: "bottom"},
						content : BX.create('DIV', {
							props: {
								className: 'feed-create-task-popup-content'
							},
							children: [
								BX.create('DIV', {
									props: {
										className: 'feed-create-task-popup-description'
									},
									text: data.errorMessage
								})
							]
						})
					});

					errorPopup.show();
				}
			}
			else
			{
				var true_data = data;
				BX.onCustomEvent(window, 'OnUCAfterRecordAdd', ['BLOG_' + postId, data, true_data]);
			}
		},
		onfailure: function(data)
		{
			hideRenderedSharingNodes(newNodes);
		}
	});
	closeSharing();
};

window.renderSharingPost = function(postId)
{
	var res = [];
	var nodeId = '';

	var elements = BX.findChildren(BX('feed-add-post-destination-item-post'), {className : 'feed-add-post-destination'}, true);
	if (elements != null)
	{
		var hiddenDest = BX('blog-destination-hidden-'+postId);
		if(!hiddenDest)
		{
			var el = BX.findChildren(BX('blg-post-img-'+postId), {className : 'feed-add-post-destination-new'}, true);
			var lastDest = el[el.length-1];
		}

		for (var j = 0; j < elements.length; j++)
		{
			if(!BX.hasClass(elements[j], 'feed-add-post-destination-undelete'))
			{
				var name = BX.findChild(elements[j], {className: 'feed-add-post-destination-text' }, false, false).innerHTML;
				var obj = BX.findChild(elements[j], {tag: 'input' }, false, false);
				var id = obj.value;
				var elementClassName = 'feed-add-post-destination-new';

				if(BX.hasClass(elements[j], 'feed-add-post-destination-email'))
				{
					elementClassName += ' feed-add-post-destination-new-email';
				}
				else if (BX.hasClass(elements[j], 'feed-add-post-destination-extranet'))
				{
					elementClassName += ' feed-add-post-destination-new-extranet';
				}

				var type;
				if(obj.name == "SPERM[SG][]")
					type = 'sonetgroups';
				else if(obj.name == "SPERM[DR][]")
					type = 'department';
				else if(obj.name == "SPERM[G][]")
					type = 'groups';
				else if(obj.name == "SPERM[U][]")
					type = 'users';
				else if(obj.name == "SPERM[UE][]")
					type = 'users';
				else if(obj.name == "SPERM[UA][]")
					type = 'groups';

				if (type.length > 0)
				{
					window["postDest" + postId].push({
						id: id,
						name: name,
						type: type
					});
					nodeId = 'post_' + postId + '_dest_' + id;
					res.push(nodeId);

					var destText = BX.create("span", {
						props: {
							id: nodeId
						},
						children: [
							BX.create("span", {
								html : ', '
							}),
							BX.create("a", {
								props: {
									className: elementClassName
								},
								href: '',
								html : name
							})
						]}
					);
					if(hiddenDest)
					{
						hiddenDest.appendChild(destText);
					}
					else if(lastDest)
					{
						BX(lastDest.parentNode).insertBefore(destText, lastDest.nextSibling);
					}
				}
			}
		}
	}

	return res;
};

window.hideRenderedSharingNodes = function(newNodes)
{
	var nodeId = false;
	for(i=0; i<newNodes.length; i++)
	{
		nodeId = newNodes[i];
		if (BX(nodeId))
		{
			BX.cleanNode(BX(nodeId), true);
		}
	}

};

(function() {
	if (!!BX.SBPostManager)
		return false;

	BX.SBPostManager = function() {
		this.inited = false;
		this.tagLinkPattern = '';
	};

	BX.SBPostManager.prototype.init = function(params) {
		this.tagLinkPattern = (BX.type.isNotEmptyString(params.tagLinkPattern) ? params.tagLinkPattern : '');
		this.inited = true;
	};

	BX.SBPostManager.prototype.clickTag = function(tagValue)
	{
		var result = false;

		if (
			BX.type.isNotEmptyString(tagValue)
			&& BX.type.isNotEmptyString(this.tagLinkPattern)
		)
		{
			top.location.href = this.tagLinkPattern.replace('#tag#', tagValue);
			result = true;
		}

		return result;
	};

}());

if (typeof oSBPostManager == 'undefined')
{
	oSBPostManager = new BX.SBPostManager;
	window.oSBPostManager = oSBPostManager;
}

