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

	BX.ajax.runAction('socialnetwork.api.livefeed.blogpost.delete', {
		data: {
			id: id,
		},
	}).then(function () {
		BX.Livefeed.FeedInstance.deleteSuccess(document.getElementById('blg-post-' + id));
	}.bind(this), function (response) {

		BX.findChild(el, {className: 'feed-post-cont-wrap'}, true, false).insertBefore(
			BX.create('span', {
				children: [
					BX.create('div', {
						props: {
							className: 'feed-add-error',
						},
						children: [
							BX.create('span', {
								props: {
									className: 'feed-add-info-icon',
								}
							}),
							BX.create('span', {
								props: {
									className: 'feed-add-info-text',
								},
								html: response.errors[0].message
							}),
						]
					})
				],
			}),
			BX.findChild(el, {className: 'feed-user-avatar'}, true, false)
		);
	}.bind(this));

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
	return BX.Livefeed.FeedInstance.changeFollow({
		logId: log_id
	});
}

(function() {
	if (!!BX.SBPostMenu)
		return false;

	BX.SBPostMenu = function(node) {
	};

	BX.SBPostMenu.showMenu = function(params) {

		if (
			typeof params == 'undefined'
			|| typeof params.event === 'undefined'
		)
		{
			return false;
		}

		var bindNode = params.event.currentTarget;
		if (!BX.type.isDomNode(bindNode))
		{
			return false;
		}

		var menuNode = params.menuNode;
		if (!BX.type.isDomNode(menuNode))
		{
			return false;
		}

		var postId = parseInt(menuNode.getAttribute('data-bx-post-id'));
		if (postId <= 0)
		{
			return false;
		}

		const context = params.context ? params.context : '';
		const sonetGroupId = params.sonetGroupId ? parseInt(params.sonetGroupId, 10) : 0;

		BX.PopupMenu.destroy('blog-post-' + postId);

		var isPublicPage = menuNode.getAttribute('data-bx-public-page');
		isPublicPage = (isPublicPage === 'Y');

		var isTasksAvailable = menuNode.getAttribute('data-bx-tasks-available');
		isTasksAvailable = (isTasksAvailable === 'Y');

		var isGroupReadOnly = menuNode.getAttribute('data-bx-group-read-only');
		isGroupReadOnly = (isGroupReadOnly === 'Y');

		var items = menuNode.getAttribute('data-bx-items');
		try
		{
			items = JSON.parse(items);
			if (!BX.type.isPlainObject(items))
			{
				items = {};
			}
		}
		catch(e)
		{
			items = {};
		}

		var pathToPost = menuNode.getAttribute('data-bx-path-to-post');
		var urlToEdit = menuNode.getAttribute('data-bx-path-to-edit');
		var urlToHide = menuNode.getAttribute('data-bx-path-to-hide');
		var urlToDelete = menuNode.getAttribute('data-bx-path-to-delete');
		var urlToPub = menuNode.getAttribute('data-bx-path-to-pub');
		var voteId = parseInt(menuNode.getAttribute('data-bx-vote-id'));
		var postType = menuNode.getAttribute('data-bx-post-type');
		var serverName = menuNode.getAttribute('data-bx-server-name');

		if (BX.type.isNotEmptyString(urlToHide))
		{
			urlToHide = BX.util.remove_url_param(urlToHide, [ 'b24statAction' ]);
			urlToHide = BX.util.add_url_param(urlToHide, {
				b24statAction: 'hidePost'
			});
		}

		if (isPublicPage)
		{
			return false;
		}

		var menuWaiterPopup = new BX.PopupWindow('blog-post-' + postId + '-waiter', bindNode, {
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

		BX.ajax.runAction('socialnetwork.api.livefeed.blogpost.getData', {
			data: {
				params: {
					postId : postId,
					public : (isPublicPage ? 'Y' : 'N'),
					mobile : 'N',
					groupReadOnly : (isGroupReadOnly ? 'Y' : 'N'),
					pathToPost : pathToPost,
					voteId: voteId,
					checkModeration : 'Y',
				}
			},
		}).then(function (response) {

			var postData = response.data;

			if (
				postData.perms <= 'D' // \Bitrix\Blog\Item\Permissions::DENY
				&& items.length <= 0
			)
			{
				menuWaiterPopup.destroy();
				return false;
			}

			var menuItems = [];

			if(!BX.util.in_array(postType, [ 'DRAFT', 'MODERATION' ]))
			{
				if (
					parseInt(BX.message('USER_ID')) > 0
					&& (parseInt(postData.logId) > 0)
				)
				{
					var isPinned = (parseInt(postData.logPinnedUserId) > 0);
					menuItems.push({
						text: BX.message(isPinned ? 'SONET_EXT_LIVEFEED_MENU_TITLE_PINNED_Y' : 'SONET_EXT_LIVEFEED_MENU_TITLE_PINNED_N'),
						onclick: function (e)
						{
							BX.Livefeed.PinnedPanelInstance.changePinned({
								logId: parseInt(postData.logId),
								newState: (isPinned ? 'N' : 'Y'),
								event: e,
								node: bindNode
							});
							this.popupWindow.close();
							return e.preventDefault();
						}
					});
				}

				if (
					postData.isGroupReadOnly != 'Y'
					&& parseInt(BX.message('USER_ID')) > 0
					&& (parseInt(postData.logId) > 0)
				)
				{
					var isFavorites = (parseInt(postData.logFavoritesUserId) > 0);
					menuItems.push({
						text: BX.message(isFavorites ? "SONET_EXT_LIVEFEED_MENU_TITLE_FAVORITES_Y" : "SONET_EXT_LIVEFEED_MENU_TITLE_FAVORITES_N"),
						onclick: function (e)
						{
							__logChangeFavorites(
								parseInt(postData.logId),
								'log_entry_favorites_' + parseInt(postData.logId),
								(isFavorites ? 'N' : 'Y'),
								true,
								e
							);
							return false;
						}
					});
				}

				menuItems.push({
					text: BX.message('BLOG_HREF'),
					href: postData.urlToPost,
					class: 'feed-entry-popup-menu-link',
					target: '_top'
				});

				menuItems.push({
					html: '<span id="post-menu-' + postData.logId + '-link-text">' + BX.message('BLOG_LINK') + '</span>' +
						'<span id="post-menu-' + postData.logId + '-link-icon-animate" class="post-menu-link-icon-wrap">' +
						'<span class="post-menu-link-icon" id="post-menu-' + postData.logId + '-link-icon-done" style="display: none;">' +

						'</span>' +
						'</span>',
					onclick: function (e)
					{
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
						onclick: function ()
						{
							showSharing(
								postId,
								parseInt(postData.authorId)
							);
							this.popupWindow.close();
						}
					});
				}
			}

			if (
				postData.perms >= 'W' // \Bitrix\Blog\Item\Permissions::FULL
				|| (
					postData.perms >= 'P' // \Bitrix\Blog\Item\Permissions::WRITE
					&& postData.authorId == BX.message('USER_ID')
				)
			)
			{
				var editParams = {
					text: BX.message('BLOG_BLOG_BLOG_EDIT'),
				};
				if (context === 'spaces')
				{
					editParams.onclick = function(event, menuItem) {
						menuItem.getMenuWindow()?.getPopupWindow()?.close();
						BX.Livefeed.Post.editSpacesPost(postId, sonetGroupId);
					};
				}
				else if (BX.type.isNotEmptyString(postData.backgroundCode))
				{
					editParams.onclick = function() {
						BX.Livefeed.Post.showBackgroundWarning({
							urlToEdit: urlToEdit,
							menuPopupWindow: this.popupWindow
						});
					}
				}
				else
				{
					editParams.href = urlToEdit;
					editParams.target = '_top';
				}
				menuItems.push(editParams);
			}

			if (
				!BX.util.in_array(postType, [ 'DRAFT', 'MODERATION' ])
				&& context !== 'spaces'
			)
			{
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

				if (
					isTasksAvailable
					&& postData.perms > 'D'
				)
				{
					menuItems.push({
						text: BX.message('BLOG_POST_CREATE_TASK'),
						onclick: function(e) {
							BX.Livefeed.TaskCreator.create({
								entityType: 'BLOG_POST',
								entityId: postId,
							});
							this.popupWindow.close();
							return e.preventDefault();
						}
					});
				}

				if (typeof BX.Landing !== 'undefined')
				{
					if (typeof BX.Landing.UI.Note.Menu.getMenuItem !== 'undefined')
					{
						menuItems.push(BX.Landing.UI.Note.Menu.getMenuItem('blog', postId));
					}
				}

				if (postData.urlToVoteExport.length > 0)
				{
					menuItems.push({
						text: BX.message('BLOG_POST_VOTE_EXPORT'),
						href: postData.urlToVoteExport,
						target: '_top'
					});
				}
			}

			if(
				postType == 'DRAFT'
				&& postData.allowModerate == 'Y'
				&& BX.type.isNotEmptyString(urlToPub)
				&& context !== 'spaces'
			)
			{
				menuItems.push({
					text: BX.message('BLOG_POST_MOD_PUB'),
					href: urlToPub,
					target: '_top'
				});
			}

			if (postData.perms >= 'W') //  // \Bitrix\Blog\Item\Permissions::FULL
			{
				menuItems.push({
					text: BX.message('BLOG_BLOG_BLOG_DELETE'),
					onclick: function() {
						if (confirm(BX.message('BLOG_MES_DELETE_POST_CONFIRM')))
						{
							if (
								urlToDelete.length > 0
								&& context !== 'spaces'
							)
							{
								window.location = urlToDelete.replace('#del_post_id#', postId);
							}
							else
							{
								window.deleteBlogPost(postId);
							}
							this.popupWindow.close();
						}
					}
				});
			}

			var
				onclickHandler = null,
				menuItem = null,
				item = null;


			for (var key in items)
			{
				if (items.hasOwnProperty(key))
				{
					item = items[key];

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

			menuWaiterPopup.destroy();
			BX.PopupMenu.show('blog-post-' + postId, bindNode, menuItems,
				{
					offsetLeft: -14,
					offsetTop: 4,
					lightShadow: false,
					angle: {position: 'top', offset: 50},
					events: {}
				});
			return false;
		}, function (response) {
			menuWaiterPopup.destroy();
			return false;
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
			step : BX.delegate(function(state) {
				this.btn.style.width = state.width +'px'
			}, this),
			complete : BX.delegate(function(){
				this.btn.innerHTML = '';
				this.btn.appendChild(text_block);
				var width_2 = text_block.scrollWidth + 31; // 31 - image width
				var easing_2 = new BX.easing({
						duration : 300,
						start : { width_2:0 },
						finish : { width_2:width_2 },
						transition : BX.easing.makeEaseOut(BX.easing.transitions.quad),
						step : BX.delegate(function(state){
							this.btn.style.width = state.width_2 + 'px';
						}, this)
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
			options: [
				{
					post_id: this.postId,
					name: 'BLOG_POST_IMPRTNT',
					value : 'Y'
				}
			],
			sessid: BX.bitrix_sessid()
		};
		BX.onCustomEvent(this.node, 'onSend', [ data ]);

		BX.ajax.runAction('socialnetwork.api.livefeed.blogpost.important.vote', {
			data: {
				params: {
					POST_ID : this.postId
				}
			},
		}).then(function (response) {
			if (
				!BX.type.isNotEmptyString(response.data.success)
				|| response.data.success != 'Y'
			)
			{
				return false;
			}

			this.busy = false;
			this.wait('hide');
			this.showClick();
			BX.onCustomEvent("onImportantPostRead", [this.postId, this.CID]);

			BX.ajax.runAction('socialnetwork.api.livefeed.blogpost.important.getUsers', {
				data: {
					params: {
						POST_ID: this.postId,
						NAME: 'BLOG_POST_IMPRTNT',
						VALUE: 'Y',
						PAGE_NUMBER: data.iNumPage,
						PATH_TO_USER: data.PATH_TO_USER,
						NAME_TEMPLATE: data.NAME_TEMPLATE
					}
				}
			}).then(function(response) {
				var resultData = response.data;
				BX.onCustomEvent(this.node, "onUserVote", [ resultData ]);
			}.bind(this), function(response) {

			}.bind(this));

		}.bind(this), function (response) {
			this.busy = false;
			this.wait('hide');
		}.bind(this));
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
			BX.adjust(this.parentNode, {style : {display : "flex"}});
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

			BX.ajax.runAction('socialnetwork.api.livefeed.blogpost.important.getUsers', {
				data: {
					params: {
						POST_ID: this.postId,
						NAME: 'BLOG_POST_IMPRTNT',
						VALUE: 'Y',
						PAGE_NUMBER: this.node.getAttribute("inumpage"),
						PATH_TO_USER: this.pathToUser,
						NAME_TEMPLATE: this.nameTemplate
					}
				}
			}).then(function(response) {
				var resultData = response.data;

				if (BX.type.isNotEmptyObject(resultData.items))
				{
					for (var ii in resultData.items)
					{
						if (resultData.items.hasOwnProperty(ii))
						{
							this.data.push(resultData.items[ii]);
						}
					}
					if (resultData.StatusPage == 'done')
					{
						this.node.setAttribute('inumpage', 'done');
					}

					this.make((this.node.getAttribute('inumpage') != 'done'));
				}
				else
				{
					this.node.setAttribute('inumpage', 'done');
				}
				this.node.firstChild.innerHTML = resultData['RecordCount'];
				this.node.setAttribute('status', 'ready');
			}.bind(this), function(response) {
				if (!!this.popup) {
					this.popup.close();
				}
				this.node.setAttribute('status', 'ready');
			}.bind(this));
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
								attrs: {src: encodeURI(data[i]['PHOTO_SRC'])},
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
			this.get();
			BX.unbindAll(res);
		}
	}

	window.entitySelectorRepo = {};

})(window);

window.showSharing = function(postId, userId)
{
	BX('sharePostId').value = postId;
	BX('shareUserId').value = userId;
	var selectorId = BX('blogShare').getAttribute('bx-selector-id');

	if (!BX.type.isNotEmptyString(selectorId))
	{
		return;
	}

	if(!window["postDest" + postId])
	{
		return;
	}

	var tagNodeId = 'entity-selector-' + selectorId;
	var inputNodeId = 'entity-selector-data-' + selectorId;

	BX.clean(tagNodeId);

	window.entitySelectorRepo[postId] = new SBPEntitySelector({
		id: selectorId + postId,
		context: 'BLOG_POST',
		tagNodeId: tagNodeId,
		inputNodeId: inputNodeId,
		preselectedItems: window["postDest" + postId],
		allowSearchEmailUsers: window.oSBPostManager && !!window.oSBPostManager.allowSearchEmailUsers,
		allowToAll: window.oSBPostManager && !!window.oSBPostManager.allowToAll
	});

	if (document.getElementById(inputNodeId))
	{
		document.getElementById(inputNodeId).value = JSON.stringify(window["postDest" + postId]);
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
		}
	})).animate();
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
	var shareForm = BX('blogShare');

	if (BX('sharePostSubmitButton'))
	{
		BX.addClass(BX('sharePostSubmitButton'), 'ui-btn-clock');
	}

	var i = 0,
		name = '',
		matches = null,
		multiple = null,
		key = null,
		s = {
			postId: postId,
			pathToUser: oSBPostManager.pathToUser,
			pathToPost: oSBPostManager.pathToPost,
			readOnly: oSBPostManager.readOnly
		};

	for(i = 0; i < shareForm.elements.length; i++)
	{
		var el = shareForm.elements[i];

		if (el.disabled)
		{
			continue;
		}

		name = el.name;
		multiple = false;
		matches = /^(.*)\[(.*)\]$/.exec(name);
		if (matches)
		{
			name = matches[1];
			multiple = true;
			key = (BX.type.isNotEmptyString(matches[2]) ? matches[2] : false);
		}

		switch(el.type.toLowerCase())
		{
			case 'text':
			case 'hidden':
				if (multiple)
				{
					if (typeof s[name] === 'undefined')
					{
						s[name] = (key ? {} : []);
					}
					if (BX.type.isArray(s[name]))
					{
						s[name].push(el.value);
					}
					else if (key)
					{
						s[name][key] = el.value;
					}
				}
				else
				{
					s[name] = el.value;
				}
				break;
			default:
				break;
		}
	}

	var newNodes = renderSharingPost(postId, s.DEST_DATA);

	BX.ajax.runAction('socialnetwork.api.livefeed.blogpost.share', {
		data: {
			params: s,
			MODE: 'RECORD', // main.post.list
			ENTITY_XML_ID: 'BLOG_' + postId,
			AJAX_POST: 'Y'
		},
		analyticsLabel: {
			b24statAction: 'sharePost'
		}
	}).then(function(data) {

		if (
			!BX.type.isNotEmptyObject(data)
			|| !BX.type.isNotEmptyString(data.status)
			|| data.status != 'success'
		)
		{
			hideRenderedSharingNodes(newNodes);

			if (
				!BX.type.isNotEmptyString(data.status)
				&& data.status == 'error'
				&& !!BX.type.isNotEmptyString(data.errorMessage)
			)
			{
				sharingPostError({
					postId: postId,
					errorMessage: data.errorMessage
				});
			}

		}
		else
		{
			var true_data = data;
			BX.onCustomEvent(window, 'OnUCAfterRecordAdd', ['BLOG_' + postId, data, true_data]);
		}

	}, function(response) {
		sharingPostError({
			postId: postId,
			errorMessage: response.errors[0].message
		});

		hideRenderedSharingNodes(newNodes);
	});

	closeSharing();
};

window.sharingPostError = function(params)
{
	var errorPopup = new BX.PopupWindow('error_popup', BX('blg-post-inform-' + params.postId), {
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
					text: params.errorMessage
				})
			]
		})
	});

	errorPopup.show();
};

window.renderSharingPost = function(postId, destData)
{
	if (!BX.type.isNotEmptyString(destData))
	{
		return;
	}

	var res = [];

	try
	{
		destData = JSON.parse(destData);
		if (!BX.type.isArray(destData))
		{
			destData = [];
		}
	}
	catch(e)
	{
		destData = [];
	}

	if (
		destData.length <= 0
		|| !window.entitySelectorRepo[postId]
		|| !window.entitySelectorRepo[postId].selector
	)
	{
		return;
	}

	var entitySelector = window.entitySelectorRepo[postId].selector;
	var lastDest = null;
	var hiddenDest = document.getElementById('blog-destination-hidden-' + postId);
	if (!hiddenDest)
	{
		var destinationList = document.getElementById('blg-post-img-' + postId).querySelectorAll('.feed-add-post-destination-new');
		if (destinationList)
		{
			lastDest = destinationList[destinationList.length - 1];
		}
	}

	destData.forEach(function (item) {

		var found = false;
		window['postDest' + postId].forEach(function(existingItem) {
			if (found)
			{
				return;
			}

			found = (existingItem[0] == item[0] && existingItem[1] == item[1]);
		});

		if (found)
		{
			return;
		}

		var tag = entitySelector.getTag({
			id: item[1],
			entityId: item[0],
		})

		var elementClassName = 'feed-add-post-destination-new';

		if (tag.getEntityType() === 'email')
		{
			elementClassName += ' feed-add-post-destination-new-email';
		}
		else if (tag.getEntityType() === 'extranet')
		{
			elementClassName += ' feed-add-post-destination-new-extranet';
		}

		var link = tag.getLink();
		var nodeId = 'post_' + postId + '_dest_' + tag.getEntityId() + '_' + tag.getId();
		res.push(nodeId);

		var destText = null;
		if (BX.type.isNotEmptyString(link))
		{
			destText = BX.create('a', {
				props: {
					className: elementClassName,
				},
				attrs: {
					href: link,
					'bx-tooltip-user-id': (tag.getEntityId() === 'user' ? tag.getId() : ''),
				},
				text: tag.getTitle(),
			});
		}
		else
		{
			destText = BX.create('span', {
				props: {
					className: elementClassName,
				},
				text: tag.getTitle(),
			});
		}

		destText = BX.create('span', {
			attrs: {
				id: nodeId,
			},
			children: [
				BX.create('span', {
					html: ', '
				}),
				destText,
			]}
		);

		if (hiddenDest)
		{
			hiddenDest.appendChild(destText);
		}
		else if (lastDest)
		{
			lastDest.parentNode.insertBefore(destText, lastDest.nextSibling);
		}
	});

	window['postDest' + postId] = BX.clone(destData);

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
		this.readOnly = 'N';
		this.pathToUser = '';
		this.pathToPost = '';
		this.allowToAll = false;
	};

	BX.SBPostManager.prototype.init = function(params) {
		this.tagLinkPattern = (BX.type.isNotEmptyString(params.tagLinkPattern) ? params.tagLinkPattern : '');
		this.inited = true;
		this.readOnly = (BX.type.isNotEmptyString(params.readOnly) && params.readOnly == 'Y' ? 'Y' : 'N');
		this.pathToUser = (BX.type.isNotEmptyString(params.pathToUser) ? params.pathToUser : '');
		this.pathToPost = (BX.type.isNotEmptyString(params.pathToPost) ? params.pathToPost : '');
		this.allowToAll = (BX.type.isBoolean(params.allowToAll) ? params.allowToAll : false);
	};

	BX.SBPostManager.prototype.clickTag = function(tagValue)
	{
		var result = false;

		if (
			BX.type.isNotEmptyString(tagValue)
			&& BX.type.isNotEmptyString(this.tagLinkPattern)
		)
		{
			top.location.href = this.tagLinkPattern.replace('#tag#', BX.util.urlencode(tagValue));
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

(function() {

	SBPEntitySelector = function(params)
	{
		this.selector = null;
		this.inputNode = null;

		if (!BX.type.isNotEmptyString(params.id))
		{
			return null;
		}

		this.init(params);
	};

	SBPEntitySelector.prototype.init = function(params)
	{
		if (!BX.type.isPlainObject(params))
		{
			params = {};
		}

		if (
			!BX.type.isNotEmptyString(params.id)
			|| !BX.type.isNotEmptyString(params.tagNodeId)
			|| !BX(params.tagNodeId)
		)
		{
			return null;
		}

		if (
			BX.type.isNotEmptyString(params.inputNodeId)
			&& BX(params.inputNodeId)
		)
		{
			this.inputNode = BX(params.inputNodeId);
		}

		var preselectedItems = (BX.type.isArray(params.preselectedItems) ? params.preselectedItems : []);

		this.selector = new BX.UI.EntitySelector.TagSelector({
			id: params.id,
			dialogOptions: {
				id: params.id,
				context: (BX.type.isNotEmptyString(params.context) ? params.context : null),

				preselectedItems: preselectedItems,
				undeselectedItems: preselectedItems,

				events: {
					'Item:onSelect': function() {
						this.recalcValue(this.selector.getDialog().getSelectedItems());
					}.bind(this),
					'Item:onDeselect': function() {
						this.recalcValue(this.selector.getDialog().getSelectedItems());
					}.bind(this)
				},
				entities: [
					{
						id: 'meta-user',
						options: {
							'all-users': {
								allowView: (
									BX.type.isBoolean(params.allowToAll)
									&& params.allowToAll
								)
							}
						}
					},
					{
						id: 'user',
						options: {
							emailUsers: (BX.type.isBoolean(params.allowSearchEmailUsers) ? params.allowSearchEmailUsers : false),
							myEmailUsers: true
						}
					},
					{
						id: 'project',
						options: {
							features: {
								blog:  [ 'premoderate_post', 'moderate_post', 'write_post', 'full_post' ]
							}
						}
					},
					{
						id: 'department',
						options: {
							selectMode: 'usersAndDepartments',
							allowFlatDepartments: false,
						}
					}
				]
			},
			addButtonCaption: BX.message('BX_FPD_SHARE_LINK_1'),
			addButtonCaptionMore: BX.message('BX_FPD_SHARE_LINK_2')
		});

		this.selector.renderTo(document.getElementById(params.tagNodeId));

		return this.selector;
	};

	SBPEntitySelector.prototype.recalcValue = function(selectedItems)
	{
		if (
			!BX.type.isArray(selectedItems)
			|| !this.inputNode
		)
		{
			return;
		}

		var result = [];

		selectedItems.forEach(function(item) {
			result.push([ item.entityId, item.id ]);
		});

		this.inputNode.value = JSON.stringify(result);
	};

	window.SBPEntitySelector = SBPEntitySelector;
})();