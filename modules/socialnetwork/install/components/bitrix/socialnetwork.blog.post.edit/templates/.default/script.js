;(function(){
	if (window["SBPETabs"])
		return;

	window.SBPEFullForm = function()
	{
		this.lazyLoad = null;
		this.ajaxUrl = '';
		this.inited = false;
		this.loaded = false;
		this.container = null;
		this.containerMicro = null;
		this.containerMicroInner = null;
		this.clickDisabled = false;
		this.lastWait = [];
		this.animationStartHeight = 0;
	};

	window.SBPEFullForm.instance = null;

	window.SBPEFullForm.getInstance = function()
	{
		if (window.SBPEFullForm.instance == null)
		{
			window.SBPEFullForm.instance = new SBPEFullForm();
		}

		return window.SBPEFullForm.instance;
	};

	window.SBPEFullForm.prototype = {

		init : function(params)
		{
			if (this.inited !== true)
			{
				this.inited = true;
				this.lazyLoad = typeof params.lazyLoad != 'undefined' ? params.lazyLoad : null;
				this.ajaxUrl = typeof params.ajaxUrl != 'undefined' ? params.ajaxUrl : '';
				this.container = typeof params.container != 'undefined' ? params.container : null;
				this.containerMicro = typeof params.containerMicro != 'undefined' ? params.containerMicro : null;
				this.containerMicroInner = typeof params.containerMicroInner != 'undefined' ? params.containerMicroInner : null;
			}
		},

		get : function(params)
		{
			var _this = this;

			if (_this.clickDisabled)
			{
				return;
			}

			if (
				_this.lazyLoad
				&& !_this.loaded
			)
			{
				_this.clickDisabled = true;
				_this.animationStartHeight = this.containerMicro.offsetHeight;

				_this.containerMicroInner.style.display = 'none';
				_this.showWait(_this.containerMicro);

				BX.ajax({
					method: 'POST',
					dataType: 'json',
					url: this.ajaxUrl,
					data: {
						action: 'SBPE_get_full_form',
						sessid: BX.bitrix_sessid()
					},
					onsuccess: BX.delegate(function(result) {
						_this.loaded = true;
						_this.clickDisabled = false;
						_this.closeWait();

						if(result.success)
						{
							_this.processAjaxBlock(result.PROPS, params.callback);
						}
						else
						{
						}
					}),
					onfailure: function(result) {
						_this.clickDisabled = false;
						_this.closeWait();
						_this.containerMicroInner.style.display = 'block';
					}
				});
			}
			else
			{
				params.callback();
			}
		},

		processAjaxBlock : function(block, callbackExternal)
		{
			if (!block)
			{
				return;
			}

			var _this = this;
			var htmlWasInserted = false;
			var scriptsLoaded = false;

			processCSS(insertHTML);
			processExternalJS(processInlineJS);

			function processCSS(callback)
			{
				if (
					BX.type.isArray(block.CSS)
					&& block.CSS.length > 0
				)
				{
					BX.load(block.CSS, callback);
				}
				else
				{
					callback();
				}
			}

			function insertHTML()
			{
				_this.container.appendChild(BX.create('DIV', {
					html: block.CONTENT
				}));

				htmlWasInserted = true;
				if (scriptsLoaded)
				{
					processInlineJS();
				}
			}

			function processExternalJS(callback)
			{
				if (
					BX.type.isArray(block.JS)
					&& block.JS.length > 0
				)
				{
					BX.load(block.JS, callback); // to initialize
				}
				else
				{
					callback();
				}
			}

			function processInlineJS()
			{
				scriptsLoaded = true;
				if (htmlWasInserted)
				{
					BX.ajax.processRequestData(block.CONTENT, {
						scriptsRunFirst: false,
						dataType: "HTML"
					});
					callbackExternal();
				}
			}
		},

		showWait : function(node)
		{
			node = BX(node) || document.body || document.documentElement;

			var obMsg = node.bxmsg = document.body.appendChild(BX.create('DIV', {
				props: {
					id: 'wait_' + node.id,
					className: 'feed-add-post-loader-cont'
				},
				html: '<svg class="feed-add-post-loader" viewBox="25 25 50 50"><circle class="feed-add-post-loader-path" cx="50" cy="50" r="20" fill="none" stroke-miterlimit="10"></circle><circle class="feed-add-post-loader-inner-path" cx="50" cy="50" r="20" fill="none" stroke-miterlimit="10"></circle></svg>'
			}));

			setTimeout(BX.delegate(this.adjustWait, node), 10);
			this.lastWait[this.lastWait.length] = obMsg;

			return obMsg;
		},

		adjustWait : function()
		{
			if (!this.bxmsg) return;

			var arContainerPos = BX.pos(this),
				div_top = arContainerPos.top + 15;

			if (div_top < BX.GetDocElement().scrollTop)
				div_top = BX.GetDocElement().scrollTop + 5;

			this.bxmsg.style.top = (div_top + 5) + 'px';

			if (this == BX.GetDocElement())
			{
					this.bxmsg.style.right = '5px';
			}
			else
			{
				this.bxmsg.style.left = (arContainerPos.left + parseInt((arContainerPos.width - this.bxmsg.offsetWidth) / 2)) + 'px';
			}
		},

		closeWait : function()
		{
			var node = this.containerMicro;
			obMsg = node.bxmsg;

			if (obMsg && obMsg.parentNode)
			{
				for (var i=0,len=this.lastWait.length;i<len;i++)
				{
					if (obMsg == this.lastWait[i])
					{
						this.lastWait = BX.util.deleteFromArray(this.lastWait, i);
						break;
					}
				}

				obMsg.parentNode.removeChild(obMsg);
				if (node)
					node.bxmsg = null;
				BX.cleanNode(obMsg, true);
			}
		},

		tasksTaskEvent : function(taskId)
		{
			this.showTaskPopup(taskId);
		},

		showTaskPopup : function(taskId)
		{
			this.createTaskPopup = new BX.PopupWindow("blogPostEditCreateTaskPopup", null, {
				autoHide: false,
				zIndex: 0,
				offsetLeft: 0,
				offsetTop: 0,
				overlay: false,
				lightShadow: true,
				closeIcon: {
					right : "12px",
					top : "10px"
				},
				draggable: {
					restrict:true
				},
				closeByEsc: false,
				contentColor : 'white',
				contentNoPaddings: true,
				buttons: [],
				content: BX.create('DIV', {
					attrs: {
						id: 'blogPostEditCreateTaskPopup_content'
					},
					props: {
						className: 'feed-create-task-popup-content'
					}
				}),
				events: {
					onAfterPopupShow: BX.proxy(function()
					{
						this.setTaskPopupContent(BX.create('DIV', {
							children: [
								BX.create('DIV', {
									props: {
										className: 'feed-create-task-popup-title'
									},
									html: BX.message('sonetBPECreateTaskSuccessTitle')
								}),
								BX.create('DIV', {
									props: {
										className: 'feed-create-task-popup-description'
									},
									html: BX.message('sonetBPECreateTaskSuccessDescription')
								})
							]
						}));

						this.createTaskPopup.setButtons([
							new BX.PopupWindowButton({
								text : BX.message('sonetBPECreateTaskButtonTitle'),
								events : {
									click : BX.proxy(function() {
										this.createTaskPopup.destroy();

										var taskLink = BX.message('PATH_TO_USER_TASKS_TASK').replace('#user_id#', BX.message('USER_ID')).replace('#task_id#', taskId).replace('#action#', 'view');
										if (
											typeof BX.Bitrix24 != 'undefined'
											&& typeof BX.Bitrix24.PageSlider != 'undefined'
										)
										{
											BX.Bitrix24.PageSlider.open(taskLink);
										}
										else
										{
											window.open(taskLink, '_blank');
										}
									}, this)
								}
							})
						]);
					}, this),
					onPopupClose: BX.proxy(function() {
						this.createTaskPopup.destroy();
					}, this)
				}
			});

			this.createTaskPopup.params.zIndex = (BX.WindowManager ? BX.WindowManager.GetZIndex() : 0);
			this.createTaskPopup.show();
		},

		setTaskPopupContent : function(contentNode)
		{
			if (BX('blogPostEditCreateTaskPopup_content'))
			{
				var containerNode = BX('blogPostEditCreateTaskPopup_content');
				BX.cleanNode(containerNode);
				containerNode.appendChild(contentNode);
			}
		}

	};

	window.SBPETabs = function()
	{
		if (window.SBPETabs.instance != null)
		{
			throw "SBPETabs is a singleton. Use SBPETabs.getInstance to get an object.";
		}

		this.tabs = {};
		this.bodies = {};
		this.active = null;
		this.animation = null;
		this.animationStartHeight = 0;

		this.menu = null;
		this.menuItems = [];
		this.lastWait = [];
		this.clickDisabled = false;
		this.createTaskPopup = null;

		if (this.inited !== true)
			this.init();

		window.SBPETabs.instance = this;
	};

	window.SBPETabs.instance = null;

	window.SBPETabs.getInstance = function()
	{
		if (window.SBPETabs.instance == null)
		{
			window.SBPETabs.instance = new SBPETabs();
		}

		return window.SBPETabs.instance;
	};

	window.SBPETabs.changePostFormTab = function(type, iblock)
	{
		var tabsObj = window.SBPETabs.getInstance();

		if (tabsObj.clickDisabled)
			return false;

		return tabsObj.setActive(type, iblock);
	};

	window.SBPETabs.prototype = {

		_createOnclick : function(id, name, onclick)
		{
			return function()
			{
				var btn = BX("feed-add-post-form-link-more", true);
				var btnText = BX("feed-add-post-form-link-text", true);
				btnText.innerHTML = name;

				if (id != 'lists')
				{
					btn.className = "feed-add-post-form-link feed-add-post-form-link-more feed-add-post-form-link-active feed-add-post-form-" + id + "-link";
					window.SBPETabs.changePostFormTab(id);
				}
				else
				{
					btn.className = "feed-add-post-form-link feed-add-post-form-link-more feed-add-post-form-" + id + "-link";
				}

				if (BX.type.isNotEmptyString(onclick))
				{
					BX.evalGlobal(onclick);
				}

				this.popupWindow.close();
			}
		},

		init : function()
		{
			this.tabContainer = BX('feed-add-post-form-tab');
			var arTabs = BX.findChildren(this.tabContainer, {'tag':'span', 'className': 'feed-add-post-form-link'}, true);
			this.arrow = BX('feed-add-post-form-tab-arrow');
			this.tabs = {}; this.bodies = {};

			for (var i = 0; i < arTabs.length; i++)
			{
				var id = arTabs[i].getAttribute("id").replace("feed-add-post-form-tab-", "");
				this.tabs[id] = arTabs[i];
				if (this.tabs[id].style.display == "none")
				{
					this.menuItems.push({
						tabId : id,
						text : arTabs[i].getAttribute("data-name"),
						className : "menu-popup-no-icon feed-add-post-form-" + id + " feed-add-post-form-" + id + "-more",
						onclick : this._createOnclick(id, arTabs[i].getAttribute("data-name"), arTabs[i].getAttribute("data-onclick"))
					});

					this.tabs[id] = this.tabs[id].parentNode;
				}

				this.bodies[id] = BX('feed-add-post-content-' + id);
			}

			if (!!this.tabs['file'])
				this.bodies['file'] = [this.bodies['message']];
			if (!!this.tabs['calendar'])
				this.bodies['calendar'] = [this.bodies['calendar']];
			if (!!this.tabs['vote'])
				this.bodies['vote'] = [this.bodies['message'], this.bodies['vote']];
			if (!!this.tabs['more'])
				this.bodies['more'] = null;
			if (!!this.tabs['important'])
				this.bodies['important'] = [this.bodies['message'], this.bodies['important']];
			if (!!this.tabs['grat'])
				this.bodies['grat'] = [this.bodies['message'], this.bodies['grat']];
			if (!!this.tabs['lists'])
				this.bodies['lists'] = [this.bodies['lists']];
			if (!!this.tabs['tasks'])
				this.bodies['tasks'] = [this.bodies['tasks']];

			for (var ii in this.bodies)
			{
				if (this.bodies.hasOwnProperty(ii) && BX.type.isDomNode(this.bodies[ii]))
					this.bodies[ii] = [this.bodies[ii]];
			}
			this.inited = true;
			this.previousTab = false;
			if (BX('bx-b-uploadfile-blogPostForm'))
			{
				BX('bx-b-uploadfile-blogPostForm').setAttribute("bx-press", "pressOut");
				BX.bind(BX('bx-b-uploadfile-blogPostForm'), "mousedown", BX.delegate(function(){
					BX('bx-b-uploadfile-blogPostForm').setAttribute("bx-press", (BX('bx-b-uploadfile-blogPostForm').getAttribute("bx-press") == "pressOut" ? "pressOn" : "pressOut"));
				}, this));
			}
			BX.onCustomEvent(this.tabContainer, "onObjectInit", [this]);

			var form = BX('blogPostForm');
			if (form)
			{
				if (!form.changePostFormTab)
				{
					form.appendChild( BX.create('INPUT', {
						props : {
							'type': 'hidden',
							'name': 'changePostFormTab',
							'value': ''
						}
					}));
				}

				BX.addCustomEvent(window, "changePostFormTab", function(type) {
					if (type != "more")
					{
						form.changePostFormTab.value = type;
					}
				});

				if (form["UF_BLOG_POST_IMPRTNT"])
				{
					BX.addCustomEvent(window, "changePostFormTab", function(type) {
						if (type != "more")
						{
							form["UF_BLOG_POST_IMPRTNT"].value = type == "important" ? 1 : 0;
						}
					});
				}
			}
		},

		setActive : function(type, iblock)
		{
			if (type == null || this.active == type && type != 'lists')
				return this.active;
			else if (!this.tabs[type])
				return false;
			var ii, jj;

			var needAnimation = (type !== "tasks" || this.isTaskTabLoaded());
			if (needAnimation)
			{
				this.startAnimation();
			}

			for (ii in this.tabs)
			{
				if (this.tabs.hasOwnProperty(ii) && ii != type)
				{
					BX.removeClass(this.tabs[ii], 'feed-add-post-form-link-active');
					if (this.bodies[ii] == null || this.bodies[type] == null)
						continue;
					for (jj = 0; jj < this.bodies[ii].length; jj++)
					{
						if (this.bodies[type][jj] != this.bodies[ii][jj])
							BX.adjust(this.bodies[ii][jj], {style : {display : "none"}});
					}
				}
			}

			if (!!this.tabs[type])
			{
				this.active = type;

				var tabPosTab = BX.pos(this.tabs[type], true);

				this.arrow.style.display = "block";
				this.arrow.style.top = tabPosTab.bottom + "px";
				var leftStart = parseInt(this.arrow.style.left) || 0;
				var widthStart = parseInt(this.arrow.style.width) || 0;
				(new BX.easing({
					duration : 200,
					start : { left: leftStart, width:  widthStart },
					finish : { left: tabPosTab.left, width: tabPosTab.width },
					transition : BX.easing.makeEaseInOut(BX.easing.transitions.quart),

					step : BX.proxy(function(state){
						this.arrow.style.left = state.left + "px";
						this.arrow.style.width = state.width + "px";
					}, this),

					complete : BX.proxy(function() {
						this.arrow.style.display = "none";
						BX.addClass(this.tabs[type], 'feed-add-post-form-link-active');
					}, this)

				})).animate();

				if (this.previousTab == 'file' || type == 'file')
				{
					var
						nodeFile = null,
						nodeDocs = null,
						hasValuesFile = false,
						hasValuesDocs = false,
						messageBody = BX('divoPostFormLHE_blogPostForm');

					if (
						!!messageBody
						&& !!messageBody.childNodes
						&& messageBody.childNodes.length > 0
					)
					{
						for (ii in messageBody.childNodes)
						{
							if (messageBody.childNodes.hasOwnProperty(ii) && messageBody.childNodes[ii].className == "file-selectdialog")
							{
								nodeFile = messageBody.childNodes[ii];
								var
									values1 = BX.findChild(nodeFile, {'className': 'file-placeholder-tbody'}, true),
									values2 = BX.findChildren(nodeFile, {'className': 'feed-add-photo-block'}, true);
								if (values1.rows > 0 || !!values2 && values2.length > 1)
									hasValuesFile = true;
							}
							else if (BX.type.isNotEmptyString(messageBody.childNodes[ii].className) &&
								(messageBody.childNodes[ii].className.indexOf("wduf-selectdialog") >= 0 ||
									messageBody.childNodes[ii].className.indexOf('diskuf-selectdialog') >= 0))
							{
								nodeDocs = messageBody.childNodes[ii];
								var webdavValues = BX.findChildren(nodeDocs, {"className" : "wd-inline-file"}, true);
								hasValuesDocs = (!!webdavValues && webdavValues.length > 0);
							}
							else if(
								BX.type.isElementNode(messageBody.childNodes[ii])
								&& !BX.hasClass(messageBody.childNodes[ii], 'urlpreview')
							)
							{
								BX.adjust(messageBody.childNodes[ii], {style : {display : (type == 'file' ? "none" : "")}});
							}
						}

						if (type == 'file')
						{
							if (!!window["PlEditorblogPostForm"])
							{
								if (!window["PlEditorblogPostForm"]["SBPEBinded"])
								{
									window["PlEditorblogPostForm"].SBPEBinded = true;
									BX.addCustomEvent(window["PlEditorblogPostForm"].eventNode, "onUploadsHasBeenChanged", function(wdObj)
									{
										if (wdObj.dialogName == 'AttachFileDialog' && wdObj.urlUpload.indexOf('&dropped=Y') < 0)
										{
											wdObj.urlUpload = wdObj.agent.uploadFileUrl = wdObj.urlUpload.replace('&random_folder=Y', '&dropped=Y');
										}
										BX('bx-b-uploadfile-blogPostForm').setAttribute("bx-press", "pressOn");
										window.SBPETabs.changePostFormTab("message");
									});
								}
								window["PlEditorblogPostForm"].controllerInit('show');
							}
							window["PlEditorblogPostForm"].controllerInit('show');
							BX.addClass(messageBody, "feed-add-post-form");
							BX.addClass(messageBody, "feed-add-post-edit-form");
							BX.addClass(messageBody, "feed-add-post-edit-form-file");
						}
						else
						{
							BX.removeClass(messageBody, "feed-add-post-form");
							BX.removeClass(messageBody, "feed-add-post-edit-form");
							BX.removeClass(messageBody, "feed-add-post-edit-form-file");
							if (!hasValuesFile && !hasValuesDocs && BX('bx-b-uploadfile-blogPostForm').getAttribute("bx-press")=="pressOut" && !!window["PlEditorblogPostForm"]) {
								window["PlEditorblogPostForm"].controllerInit('hide');
							}
						}
					}
				}

				if (
					BX('divoPostFormLHE_blogPostForm')
					&& BX('divoPostFormLHE_blogPostForm').style.display == "none"
				)
				{
					BX.onCustomEvent(BX('divoPostFormLHE_blogPostForm' ), 'OnShowLHE', ['justShow']);
				}

				if(type == 'lists')
				{
					BX.onCustomEvent('onDisplayClaimLiveFeed', [iblock]);
				}
				this.previousTab = type;
				if (!!this.bodies[type])
				{
					for (jj = 0; jj < this.bodies[type].length; jj++)
					{
						if (!!this.bodies[type][jj])
						{
							BX.adjust(this.bodies[type][jj], {style : {display : "block"}});
						}
					}
				}
			}

			if (needAnimation)
			{
				this.endAnimation();
			}

			if(type != 'lists')
				this.restoreMoreMenu();

			BX.onCustomEvent(window, "changePostFormTab", [type]);
			return this.active;
		},

		startAnimation : function()
		{
			if (this.animation)
			{
				this.animation.stop();
			}

			var container = BX("microblog-form", true);
			if (!container)
			{
				return false;
			}

			if (window.SBPEFullForm.getInstance().animationStartHeight > 0)
			{
				this.animationStartHeight = window.SBPEFullForm.getInstance().animationStartHeight;
				window.SBPEFullForm.getInstance().animationStartHeight = 0;
			}
			else
			{
				this.animationStartHeight = container.parentNode.offsetHeight;
			}

			container.parentNode.style.height = this.animationStartHeight + "px";
			container.parentNode.style.overflowY = "hidden";
			container.parentNode.style.position = "relative";
			container.style.opacity = 0;
		},

		endAnimation : function()
		{
			var container = BX("microblog-form", true);
			if (!container)
			{
				return false;
			}

			this.animation = new BX.easing({
				duration : 500,
				start : { height: this.animationStartHeight, opacity : 0 },
				finish : { height: container.offsetHeight + container.offsetTop, opacity : 100 },
				transition : BX.easing.makeEaseOut(BX.easing.transitions.quart),

				step : function(state){
					container.parentNode.style.height = state.height + "px";
					container.style.opacity = state.opacity / 100;
				},

				complete : BX.proxy(function() {
					container.style.cssText = "";
					container.parentNode.style.cssText = "";
					this.animation = null;
				}, this)

			});

			this.animation.animate();
		},

		collapse : function()
		{
			window.SBPETabs.changePostFormTab("message");
			if (window.SBPEFullForm.getInstance().containerMicroInner)
			{
				window.SBPEFullForm.getInstance().containerMicroInner.style.display = 'block';
			}
			this.startAnimation();
			BX.onCustomEvent(BX("divoPostFormLHE_blogPostForm"), "OnShowLHE", [false]);
			BX.onCustomEvent(window, 'onExtAutoSaveReset_blogPostForm', []);
			this.endAnimation();

			this.active = null;
		},

		showMoreMenu : function()
		{
			if (!this.menu)
			{
				this.menu = BX.PopupMenu.create(
					"feed-add-post-form-popup",
					BX("feed-add-post-form-link-text"),
					this.menuItems,
					{
						className: "feed-add-post-form-popup",
						closeByEsc : true,
						offsetTop: 5,
						offsetLeft: 3,
						angle: true
					}
				);
			}

			this.menu.popupWindow.show();
		},

		restoreMoreMenu : function()
		{
			var itemCnt = this.menuItems.length;
			if (itemCnt < 1)
			{
				return;
			}

			for (var i = 0; i < itemCnt; i++)
			{
				if (this.active == this.menuItems[i]["tabId"])
				{
					return;
				}
			}

			var btn = BX("feed-add-post-form-link-more", true);
			var btnText = BX("feed-add-post-form-link-text", true);
			btn.className = "feed-add-post-form-link feed-add-post-form-link-more";
			btnText.innerHTML = BX.message("SBPE_MORE");
		},

		getLists : function()
		{
			var tabContainer = (BX('feed-add-post-form-tab-lists') && BX('feed-add-post-form-tab-lists').style.display != 'none' ? BX('feed-add-post-form-tab-lists') : BX('feed-add-post-form-link-more')),
				tabs = BX.findChildren(tabContainer, {'tag':'span', 'className': 'feed-add-post-form-link-lists'}, true),
				tabsDefault = BX.findChildren(tabContainer, {'tag':'span', 'className': 'feed-add-post-form-link-lists-default'}, true),
				menuItemsListsDefault = [],
				menuItemsLists = [];

			if(tabs.length)
			{
				menuItemsLists = this.getMenuItems(tabs, this.createOnclickLists);
				menuItemsListsDefault = this.getMenuItemsDefault(tabsDefault);
				menuItemsLists = menuItemsLists.concat(menuItemsListsDefault);
				this.showMoreMenuLists(menuItemsLists);
			}
			else
			{
				var showMoreMenuLists = this.showMoreMenuLists,
					getMenuItems = this.getMenuItems,
					getMenuItemsDefault = this.getMenuItemsDefault,
					createOnclickLists = this.createOnclickLists,
					siteId = null;

				if(BX('bx-lists-select-site-id'))
				{
					siteId = BX('bx-lists-select-site-id').value;
				}

				BX.ajax({
					method: 'POST',
					dataType: 'json',
					url: '/bitrix/components/bitrix/socialnetwork.blog.post.edit/post.ajax.php',
					data: {
						bitrix_processes: 1,
						siteId: siteId,
						sessid: BX.bitrix_sessid()
					},
					onsuccess: BX.delegate(function(result) {
						if(result.success)
						{
							var k = null;
							for(k in result.lists)
							{
								if (result.lists.hasOwnProperty(k))
								{
									tabContainer.appendChild(BX.create('span', {
										attrs: {
											'data-name': result.lists[k].NAME,
											'data-picture': result.lists[k].PICTURE,
											'data-description': result.lists[k].DESCRIPTION,
											'data-picture-small': result.lists[k].PICTURE_SMALL,
											'data-code': result.lists[k].CODE,
											'iblockId': result.lists[k].ID
										},
										props:{
											className: 'feed-add-post-form-link-lists',
											id: 'feed-add-post-form-tab-lists'
										},
										style : {
											display: 'none'
										}
									}));
								}
							}

							tabs = BX.findChildren(tabContainer, {'tag':'span', 'className': 'feed-add-post-form-link-lists'}, true);
							menuItemsLists = getMenuItems(tabs, createOnclickLists);

							if(!tabsDefault.length)
							{
								for(k in result.permissions)
								{
									if (result.permissions.hasOwnProperty(k))
									{
										var onclick;
										if(k == 'new')
										{
											onclick = 'document.location.href = "'+BX('bx-lists-lists-page').value+'0/edit/"';
										}
										else if(k == 'market')
										{
											if(result.admin && BX('bx-lists-lists-page'))
											{
												onclick = 'document.location.href = "'+BX('bx-lists-lists-page').value+'?bp_catalog=y"';
											}
											else
											{
												if(BX('bx-lists-random-string'))
												{
													onclick = 'BX.Lists["LiveFeedClass_'+BX('bx-lists-random-string').value+'"].errorPopup("'+BX.message('LISTS_CATALOG_PROCESSES_ACCESS_DENIED')+'");';
												}
											}
										}
										else if(k == 'settings')
										{
											onclick = 'document.location.href = "'+BX('bx-lists-lists-page').value+'"';
										}
										tabContainer.appendChild(BX.create('span', {
											attrs: {
												'data-name': result.permissions[k],
												'data-picture-small': '',
												'data-key': k,
												'data-onclick': onclick
											},
											props:{
												className: 'feed-add-post-form-link-lists-default',
												id: 'feed-add-post-form-tab-lists'
											},
											style : {
												display: 'none'
											}
										}));
									}
								}
								tabsDefault = BX.findChildren(tabContainer, {'tag':'span', 'className': 'feed-add-post-form-link-lists-default'}, true);
							}
							menuItemsListsDefault = getMenuItemsDefault(tabsDefault);
							menuItemsLists = menuItemsLists.concat(menuItemsListsDefault);
							showMoreMenuLists(menuItemsLists);
						}
						else
						{
							tabContainer.appendChild(BX.create('span', {
								attrs: {
									'data-name': result.error,
									'data-picture-small': ''
								},
								props:{
									className: 'feed-add-post-form-link-lists-default',
									id: 'feed-add-post-form-tab-lists'
								},
								style : {
									display: 'none'
								}
							}));
							tabs = BX.findChildren(tabContainer, {'tag':'span', 'className': 'feed-add-post-form-link-lists-default'}, true);
							menuItemsLists = getMenuItems(tabs, 0);
							showMoreMenuLists(menuItemsLists);
						}
					})
				});
			}
		},

		isTaskTabLoaded: function() {
			var contentContainer = BX('feed-add-post-content-tasks-container');
			return contentContainer && contentContainer.children.length;
		},

		getTaskForm : function()
		{
			var tabContainer = (BX('feed-add-post-form-tab-tasks') && BX('feed-add-post-form-tab-tasks').style.display != 'none' ? BX('feed-add-post-form-tab-tasks') : BX('feed-add-post-form-link-more')),
				content = BX('feed-add-post-content-tasks'),
				contentContainer = BX('feed-add-post-content-tasks-container');
			if (
				contentContainer
				&& contentContainer.innerHTML.length <= 0
				&& !this.clickDisabled
			)
			{
				this.clickDisabled = true;
				window.SBPEFullForm.getInstance().showWait(contentContainer);
				this.startAnimation();

				var componentParameters = {
					GROUP_ID: BX.message('TASK_SOCNET_GROUP_ID'),
					PATH_TO_USER_TASKS: BX.message('PATH_TO_USER_TASKS'),
					PATH_TO_USER_TASKS_TASK: BX.message('PATH_TO_USER_TASKS_TASK'),
					PATH_TO_GROUP_TASKS: BX.message('PATH_TO_GROUP_TASKS'),
					PATH_TO_GROUP_TASKS_TASK: BX.message('PATH_TO_GROUP_TASKS_TASK'),
					PATH_TO_USER_PROFILE: BX.message('PATH_TO_USER_PROFILE'),
					PATH_TO_GROUP: BX.message('PATH_TO_GROUP'),
					PATH_TO_USER_TASKS_PROJECTS_OVERVIEW: BX.message('PATH_TO_USER_TASKS_PROJECTS_OVERVIEW'),
					PATH_TO_USER_TASKS_TEMPLATES: BX.message('PATH_TO_USER_TASKS_TEMPLATES'),
					PATH_TO_USER_TEMPLATES_TEMPLATE: BX.message('PATH_TO_USER_TEMPLATES_TEMPLATE'),
					ENABLE_FOOTER: 'N',
					TEMPLATE_CONTROLLER_ID: 'lifefeed_task_form',
					ENABLE_FORM: 'N',
					BACKURL: BX.message('TASK_SUBMIT_BACKURL')
				};

				BX.Tasks.Util.Query.runOnce(
					'ui.task.edit',
					{
						parameters: {
							COMPONENT_PARAMETERS: componentParameters
						}
					}
				).then(
					BX.proxy(function(result)
					{
						if(result.isSuccess())
						{
							BX.html(contentContainer, result.getData());
							BX.adjust(content, {
								style: {
									display : 'block'
								}
							});

							return true;
						}
						else
						{
							this.closeWait(contentContainer);
							this.endAnimation();
							throw new Error();
						}
					}, this),
					BX.proxy(function(reason)
					{
						this.closeWait(contentContainer);
						this.endAnimation();
						throw new Error();
					}, this)
				).then(
					BX.proxy(function()
					{
						this.clickDisabled = false;
						this.closeWait(contentContainer);
						this.endAnimation();
					}, this)
				);
			}
			else
			{
				this.startAnimation();
				this.endAnimation();
			}

			BX.onCustomEvent(BX('lifefeed_task_form' ), 'OnShowLHE', ['justShow']);
		},

		closeWait : function(node)
		{
			obMsg = node.bxmsg;

			if (obMsg && obMsg.parentNode)
			{
				for (var i=0,len=this.lastWait.length;i<len;i++)
				{
					if (obMsg == this.lastWait[i])
					{
						this.lastWait = BX.util.deleteFromArray(this.lastWait, i);
						break;
					}
				}

				obMsg.parentNode.removeChild(obMsg);
				if (node)
					node.bxmsg = null;
				BX.cleanNode(obMsg, true);
			}
		},

		getMenuItems : function(tabs, createOnclickLists)
		{
			var menuItemsLists = [];
			for (var i = 0; i < tabs.length; i++)
			{
				var id = tabs[i].getAttribute("id").replace("feed-add-post-form-tab-", "");
				if(createOnclickLists)
				{
					menuItemsLists.push({
						tabId : id,
						text : BX.util.htmlspecialchars(tabs[i].getAttribute("data-name")),
						className : "feed-add-post-form-" + id + "feed-add-post-form-" + id + "-item",
						onclick : createOnclickLists(
							id,
							[
								tabs[i].getAttribute("iblockId"),
								tabs[i].getAttribute("data-name"),
								tabs[i].getAttribute("data-description"),
								tabs[i].getAttribute("data-picture"),
								tabs[i].getAttribute("data-code")
							]
						)
					});
				}
				else
				{
					menuItemsLists.push({
						tabId : id,
						text : tabs[i].getAttribute("data-name"),
						className : "feed-add-post-form-" + id,
						onclick : ''
					});
				}
			}
			return menuItemsLists;
		},

		getMenuItemsDefault : function(tabs)
		{
			var menuItemsLists = [];
			for (var i = 0; i < tabs.length; i++)
			{
				menuItemsLists.push({
					text : BX.util.htmlspecialchars(tabs[i].getAttribute("data-name")),
					className : "feed-add-post-form-lists-default-"+tabs[i].getAttribute("data-key"),
					onclick : tabs[i].getAttribute("data-onclick")
				});
			}
			return menuItemsLists;
		},

		showMoreMenuLists : function(menuItemsLists)
		{
			var menuBindElement = (BX('feed-add-post-form-tab-lists').style.display != 'none' ? BX('feed-add-post-form-tab-lists') : BX('feed-add-post-form-link-more'));
			var menu = BX.PopupMenu.create(
				"lists",
				menuBindElement,
				menuItemsLists,
				{
					closeByEsc : true,
					offsetTop: 5,
					offsetLeft: 12,
					angle: true
				}
			);

			var spanIcon = BX.findChildren(BX('popup-window-content-menu-popup-lists'), {'tag':'span', 'className': 'menu-popup-item-icon'}, true),
				spanDataPicture = BX.findChildren(menuBindElement, {'tag':'span', 'className': 'feed-add-post-form-link-lists'}, true),
				spanDataPictureDefault = BX.findChildren(menuBindElement, {'tag':'span', 'className': 'feed-add-post-form-link-lists-default'}, true);
			spanDataPicture = spanDataPicture.concat(spanDataPictureDefault);

			for(var i = 0; i < spanIcon.length; i++)
			{
				if(spanDataPicture[i].getAttribute('data-picture-small'))
				{
					spanIcon[i].innerHTML = spanDataPicture[i].getAttribute('data-picture-small');
				}
			}

			menu.popupWindow.show();
		},

		createOnclickLists : function(id, iblock)
		{
			return function()
			{
				window.SBPETabs.changePostFormTab(id, iblock);
				this.popupWindow.close();
			}
		}
	};


	window.BXfpGratSelectCallback = function(item/*, type_user, name*/)
	{
		BXfpGratMedalSelectCallback(item, 'grat');
	};

	window.BXfpMedalSelectCallback = function(item/*, type_user, name*/)
	{
		BXfpGratMedalSelectCallback(item, 'medal');
	};

	window.BXfpGratMedalSelectCallback = function(item, type)
	{
		if (type != 'grat')
			type = 'medal';

		var prefix = 'U';

		BX('feed-add-post-'+type+'-item').appendChild(
			BX.create("span", {
				attrs : { 'data-id' : item.id },
				props : { className : "feed-add-post-"+type+" feed-add-post-destination-users" },
				children: [
					BX.create("input", {
						attrs : { 'type' : 'hidden', 'name' : (type == 'grat' ? 'GRAT' : 'MEDAL')+'['+prefix+'][]', 'value' : item.id }
					}),
					BX.create("span", {
						props : { 'className' : "feed-add-post-"+type+"-text" },
						html : item.name
					}),
					BX.create("span", {
						props : { 'className' : "feed-add-post-del-but"},
						events : {
							'click' : function(e){
								BX.SocNetLogDestination.deleteItem(item.id, 'users', window["BXSocNetLogGratFormName"]);
								BX.PreventDefault(e)
							},
							'mouseover' : function(){
								BX.addClass(this.parentNode, 'feed-add-post-'+type+'-hover')
							},
							'mouseout' : function(){
								BX.removeClass(this.parentNode, 'feed-add-post-'+type+'-hover')
							}
						}
					})
				]
			})
		);

		BX('feed-add-post-'+type+'-input').value = '';

		BX.SocNetLogDestination.BXfpSetLinkName({
			formName: (type == 'grat' ? window["BXSocNetLogGratFormName"] : window["BXSocNetLogMedalFormName"]),
			tagInputName: 'bx-' + type + '-tag',
			tagLink1: BX.message('BX_FPGRATMEDAL_LINK_1'),
			tagLink2: BX.message('BX_FPGRATMEDAL_LINK_2')
		});
	};

	if (!!BX.SocNetGratSelector)
		return;

	BX.SocNetPostDateEndData =
		{
			isInitialized: false,
			popupShowingPeriods: null,
			customDateStyleModifier: 'feed-add-post-expire-date-customize',
			popupTriggerSelector: '.js-important-till-popup-trigger',
			customDatePopupOptionClass: 'js-custom-date-end',
			customDateFinalSelector: '.js-date-post-showing-custom',
			postExpireDateBlockSelector: '.js-post-expire-date-block'
		};
	BX.SocNetGratSelector =
		{
			popupWindow: null,
			obWindowCloseIcon: {},
			sendEvent: true,
			obCallback: {},
			gratsContentElement: null,
			itemSelectedImageItem: {},
			itemSelectedInput: {},

			searchTimeout: null,
			obDepartmentEnable: {},
			obSonetgroupsEnable: {},
			obLastEnable: {},
			obWindowClass: {},
			obPathToAjax: {},
			obDepartmentLoad: {},
			obDepartmentSelectDisable: {},
			obItems: {},
			obItemsLast: {},
			obItemsSelected: {},

			obElementSearchInput: {},
			obElementBindMainPopup: {},
			obElementBindSearchPopup: {}
		};
	BX.SocNetPostDateEndData.init = function ()
	{
		if (this.isInitialized)
		{
			return;
		}
		this.addEventHandlers();
		if (!this.formDateTimeEditing.value)
		{
			this.customDateSelectedTitle.innerText = this.getCurrentDate();
		}
		this.isInitialized = true;
	};

	BX.SocNetPostDateEndData.addEventHandlers = function ()
	{
		this.postExpireDateBlock = document.querySelector(this.postExpireDateBlockSelector);
		this.formUfInputDateCustom = document.querySelector('.js-form-post-end-time');
		this.formDateDuration = document.querySelector('.js-form-post-end-period');
		this.formDateTimeEditing = document.querySelector('.js-form-editing-post-end-time');
		this.popupTrigger = document.querySelector(this.popupTriggerSelector);
		if (this.popupTrigger)
		{
			this.popupTrigger.addEventListener("click", function (event)
			{
				BX.SocNetPostDateEndData.showPostEndPeriodsPopup();
			});
		}

		this.customDateSelectedTitle = document.querySelector(this.customDateFinalSelector);
		if (this.customDateSelectedTitle)
		{
			this.customDateSelectedTitle.addEventListener("click", (function (event) {
				var curDate = new Date();
				var curTimestamp = Math.round(curDate / 1000) - curDate.getTimezoneOffset() * 60;
				if (this.formDateTimeEditing.value)
				{
					curDate = BX.parseDate(this.formDateTimeEditing.value);
					curTimestamp = BX.date.convertToUTC(curDate);
				}
				BX.calendar({
					node: this.customDateSelectedTitle,
					form: "blogPostForm",
					value: curTimestamp,
					bTime: false,
					'callback': function(){
						return true;
					},
					'callback_after': BX.SocNetPostDateEndData.onEndDateSet.bind(BX.SocNetPostDateEndData)
				});
			}).bind(this));
		}
	};
	BX.SocNetPostDateEndData.showPostEndPeriodsPopup = function()
	{
		if (!this.popupShowingPeriods)
		{
			this.createPopupShowingPeriods();
		}
		this.popupShowingPeriods.popupWindow.show();
	};
	BX.SocNetPostDateEndData.createPopupShowingPeriods = function()
	{
		if (!this.menuItems)
		{
			this.menuItems = this.createPopupItems();
		}
		this.popupShowingPeriods = BX.PopupMenu.create(
			"feed-add-post-form-popup42",
			BX("js-post-expire-date-wrapper"),
			this.menuItems,
			{
				className: "feed-add-post-expire-date-options",
				closeByEsc : true,
				angle: true
			}
		);
	};
	BX.SocNetPostDateEndData.createPopupItems = function()
	{
		var menuPostDurationItems = [];
		var selectOptions = BX.findChildren(document.querySelector('.js-post-showing-duration-options-container'), {'className': 'js-post-showing-duration-option'}, true);
		if (selectOptions)
		{
			selectOptions.forEach(function(element){
				menuPostDurationItems.push({
					onclick: this.onPopupItemClick.bind(this),
					dataset: {
						value: element.getAttribute('data-value'),
						class: element.getAttribute('data-class')
					},
					text: element.getAttribute('data-text'),
					className: 'menu-popup-item menu-popup-no-icon ' + element.getAttribute('data-class')
				});
			}.bind(this));
			return menuPostDurationItems;
		}
		return [];
	};
	BX.SocNetPostDateEndData.onPopupItemClick = function(event) {
		var element = event.currentTarget;
		if (element.getAttribute('data-class') == this.customDatePopupOptionClass)
		{
			this.postExpireDateBlock.classList.add(this.customDateStyleModifier);
			if (this.formDateTimeEditing.value)
			{
				this.formUfInputDateCustom.value = this.formDateTimeEditing.value;
				this.customDateSelectedTitle.innerText = this.formDateTimeEditing.value;
			}
			else
			{
				this.formUfInputDateCustom.value = this.getCurrentDate();
			}
		}
		else
		{
			this.postExpireDateBlock.classList.remove(this.customDateStyleModifier);
			this.formUfInputDateCustom.value = null;
		}
		this.popupTrigger.innerText = element.innerText.toLowerCase();
		this.formDateDuration.value = element.getAttribute('data-value').toUpperCase();
		this.popupShowingPeriods.popupWindow.close();
	};
	BX.SocNetPostDateEndData.onEndDateSet = function (value)
	{
		if (!value)
		{
			return;
		}
		this.formDateTimeEditing.value = this.getFormattedDate(value);
		this.formUfInputDateCustom.value = this.getFormattedDate(value);
		this.customDateSelectedTitle.innerText = this.getFormattedDate(value);
	};
	BX.SocNetPostDateEndData.getFormattedDate = function (value)
	{
		return BX.date.format(BX.date.convertBitrixFormat(BX.message('FORMAT_DATE')), value);
	};
	BX.SocNetPostDateEndData.getCurrentDate = function ()
	{
		return BX.SocNetPostDateEndData.getFormattedDate(new Date());
	};
	BX.SocNetGratSelector.init = function(arParams)
	{
		if(!arParams.name)
			arParams.name = 'lm';

		BX.SocNetGratSelector.obCallback[arParams.name] = arParams.callback;
		BX.SocNetGratSelector.obWindowCloseIcon[arParams.name] = typeof (arParams.obWindowCloseIcon) == 'undefined' ? true : arParams.obWindowCloseIcon;
		BX.SocNetGratSelector.itemSelectedImageItem[arParams.name] = arParams.itemSelectedImageItem;
		BX.SocNetGratSelector.itemSelectedInput[arParams.name] = arParams.itemSelectedInput;
	};
	/**
	 * @return boolean
	 */
	BX.SocNetGratSelector.openDialog = function(name)
	{
		if(!name)
			name = 'lm';

		if (BX.SocNetGratSelector.popupWindow != null)
		{
			BX.SocNetGratSelector.popupWindow.close();
			return false;
		}

		var arGratsItems = [];
		for (var i = 0; i < arGrats.length; i++)
		{
			arGratsItems[arGratsItems.length] = BX.create("span", {
				props: {
					className: 'feed-add-grat-box ' + arGrats[i].style
				},
				attrs: {
					'title': arGrats[i].title
				},
				events: {
					'click' : BX.delegate(function(e){
						BX.SocNetGratSelector.selectItem(name, this.code, this.style, this.title);
						BX.PreventDefault(e)
					}, arGrats[i])
				}
			});
		}
		var arGratsRows = [];
		var rownum = 1;
		for (i = 0; i < arGratsItems.length; i++)
		{
			if (i >= arGratsItems.length/2)
				rownum = 2;

			if (arGratsRows[rownum] == null || arGratsRows[rownum] == 'undefined')
				arGratsRows[rownum] = BX.create("div", {
					props: {
						className: 'feed-add-grat-list-row'
					}
				});
			arGratsRows[rownum].appendChild(arGratsItems[i]);
		}

		BX.SocNetGratSelector.gratsContentElement = BX.create("div", {
			children: [
				BX.create("div", {
					props: {
						className: 'feed-add-grat-list-title'
					},
					html: BX.message('BLOG_GRAT_POPUP_TITLE')
				}),
				BX.create("div", {
					props: {
						className: 'feed-add-grat-list'
					},
					children: arGratsRows
				})
			]
		});

		BX.SocNetGratSelector.popupWindow = new BX.PopupWindow('BXSocNetGratSelector', BX('feed-add-post-grat-type-selected'), {
			autoHide: true,
			offsetLeft: 25,
			bindOptions: { forceBindPosition: true },
			closeByEsc: true,
			closeIcon : BX.SocNetGratSelector.obWindowCloseIcon[name] ? { 'top': '5px', 'right': '10px' } : false,
			events : {
				onPopupShow : function() {
					if(BX.SocNetGratSelector.sendEvent && BX.SocNetGratSelector.obCallback[name] && BX.SocNetGratSelector.obCallback[name].openDialog)
						BX.SocNetGratSelector.obCallback[name].openDialog();
				},
				onPopupClose : function() {
					this.destroy();
				},
				onPopupDestroy : BX.proxy(function() {
					BX.SocNetGratSelector.popupWindow = null;
					if(BX.SocNetGratSelector.sendEvent && BX.SocNetGratSelector.obCallback[name] && BX.SocNetGratSelector.obCallback[name].closeDialog)
						BX.SocNetGratSelector.obCallback[name].closeDialog();
				}, this)
			},
			content: BX.SocNetGratSelector.gratsContentElement,
			angle: {
				position: "bottom",
				offset : 20
			},
			lightShadow: true
		});
		BX.SocNetGratSelector.popupWindow.setAngle({});
		BX.SocNetGratSelector.popupWindow.show();
		return true;
	};

	BX.SocNetGratSelector.selectItem = function(name, code, style, title)
	{
		var gratSpan = BX.findChild(BX.SocNetGratSelector.itemSelectedImageItem[name], { tag: 'span' }, false, false);
		if (
			typeof (gratSpan) != 'undefined'
			&& gratSpan
		)
		{
			gratSpan.className = 'feed-add-grat-box ' + style;
		}

		BX.SocNetGratSelector.itemSelectedImageItem[name].title = title;
		BX.SocNetGratSelector.itemSelectedInput[name].value = code;
		BX.SocNetGratSelector.popupWindow.close();
	};

	var BlogPostAutoSave = function (autoSaveRestoreMethod, initRestore) {
			var
				formId = 'blogPostForm',
				form = BX(formId),
				titleID = 'POST_TITLE',
				title = BX(titleID),
				tags = BX(formId).TAGS,
				bindLHEEvents = function(_ob)
				{
					BX.bind(title, 'keydown', BX.proxy(_ob.Init, _ob));
					BX.bind(tags, 'keydown', BX.proxy(_ob.Init, _ob));
				};

			if (!form)
				return;

			initRestore = (typeof initRestore != 'undefined' ? !!initRestore : true);

			BX.addCustomEvent(form, 'onAutoSavePrepare', function (ob/*, h*/) {
				ob.DISABLE_STANDARD_NOTIFY = true;
				var _ob=ob;
				setTimeout(function() { bindLHEEvents(_ob) }, 100);
			});

			BX.addCustomEvent(form, 'onAutoSave', function(ob, form_data) {
				form_data['TAGS'] = BX(formId).TAGS.value;
				delete form_data['POST_MESSAGE'];
			});
			if (autoSaveRestoreMethod == 'Y')
			{
				BX.addCustomEvent(form, 'onAutoSaveRestoreFound', function(ob, data) {
					var text = (BX.util.trim(data['text' + formId]) || ''),
						title = (BX.util.trim(data[titleID]) || '');
					if (text.length < 1 && title.length < 1) return;
					ob.Restore();
				});
			}
			else
			{
				BX.addCustomEvent(form, 'onAutoSaveRestoreFound', BX.delegate(function(ob, data) {
					var text = (BX.util.trim(data['text' + formId]) || ''),
						title = (BX.util.trim(data[titleID]) || '');
					if (text.length < 1 && title.length < 1) return;
					var
						messageBody = BX('microoPostFormLHE_blogPostForm'),
						textNode = BX.create('DIV', {
							attrs : {
								className : "feed-add-successfully"
							},
							children : [
								BX.create('SPAN', {
									attrs : {
										className : "feed-add-info-icon"
									}}),
								BX.create('A', {
									attrs : {
										className : "feed-add-info-text",
										href : "#"
									},
									events : {
										click : function(){
											ob.Restore();
											textNode.parentNode.removeChild(textNode);
											return false;
										}
									},
									text : BX.message('BLOG_POST_AUTOSAVE2')
								})
							]
						});
					if (messageBody)
					{
						messageBody.parentNode.insertBefore(textNode, messageBody);
					}
				}, this));
			}

			if (initRestore)
			{
				BX.addCustomEvent(form, 'onAutoSaveRestore', function(ob, data) {
					BX(titleID).value = data[titleID];
					if(data[titleID].length > 0 && data[titleID] != BX(titleID).getAttribute("placeholder"))
					{
						if(BX('divoPostFormLHE_blogPostForm').style.display != "none")
							window['showPanelTitle_' + formId](true);
						else
							window["bShowTitle"] = true;
						if (!!BX(titleID).__onchange)
							BX(titleID).__onchange();
					}

					var formTags = window["BXPostFormTags_" + formId];
					if(data['TAGS'].length > 0 && formTags)
					{
						var tags = formTags.addTag(data['TAGS']);
						if (tags.length > 0)
						{
							BX.show(formTags.tagsArea);
						}
					}

					if(BX.SocNetLogDestination)
					{
						var i;
						if(data['SPERM[DR][]'])
						{
							for (i = 0; i < data['SPERM[DR][]'].length; i++ )
							{
								BX.SocNetLogDestination.selectItem(BXSocNetLogDestinationFormName, '', 3, data['SPERM[DR][]'][i], 'department', false);
							}
						}
						if(data['SPERM[SG][]'])
						{
							for (i = 0; i < data['SPERM[SG][]'].length; i++ )
							{
								BX.SocNetLogDestination.selectItem(BXSocNetLogDestinationFormName, '', 3, data['SPERM[SG][]'][i], 'sonetgroups', false);
							}
						}
						if(data['SPERM[U][]'])
						{
							for (i = 0; i < data['SPERM[U][]'].length; i++ )
							{
								BX.SocNetLogDestination.selectItem(BXSocNetLogDestinationFormName, '', 3, data['SPERM[U][]'][i], 'users', false);
							}
						}
						if(!data['SPERM[UA][]'])
						{
							BX.SocNetLogDestination.deleteItem('UA', 'groups', BXSocNetLogDestinationFormName);
						}
					}

					bindLHEEvents(ob);
				});
			}

		},
		formParams = {},
		reinit = function(formID)
		{
			if (formParams[formID] && formParams[formID]["editorID"])
			{
				if (formParams[formID]["editor"])
					formParams[formID]["editor"](formParams[formID]['text']);
				else
					setTimeout(function(){reinit(formID);}, 50);
			}
		};

	BX.SocnetBlogPostInit = function(formID, params)
	{
		formParams[formID] = {
			editorID : params['editorID'],
			showTitle : (!!params['showTitle']),
			submitted : false,
			text : params['text'],
			autoSave : params['autoSave'],
			handler : (LHEPostForm && LHEPostForm.getHandler(params['editorID'])),
			editor : (LHEPostForm && LHEPostForm.getEditor(params['editorID'])),
			restoreAutosave : !!params['restoreAutosave']
		};
		window['showPanelTitle_' + formID] = function(show, saveChanges)
		{
			show = ( show === true || show === false ? show : (BX('blog-title').style.display == "none") );
			saveChanges = (saveChanges !== false);
			var
				bShowTitleCopy = formParams[formID]['showTitle'],
				node = BX("lhe_button_title_" + formID),
				nodeBlock = BX("feed-add-post-block" + formID),
				stv = (BX('show_title') || {});

			if(show)
			{
				BX.show(BX('blog-title'));
				BX.focus(BX('POST_TITLE'));
				formParams[formID]['showTitle'] = true;
				stv.value = "Y";
				if (node)
				{
					BX.addClass(node, 'feed-add-post-form-btn-active');
				}
				if (nodeBlock)
				{
					BX.addClass(nodeBlock, 'blog-post-edit-open');
				}
			}
			else
			{
				BX.hide(BX('blog-title'));
				formParams[formID]['showTitle'] = false;
				stv.value = "N";
				if (node)
					BX.removeClass(node, 'feed-add-post-form-btn-active');
			}
			if (saveChanges)
				BX.userOptions.save('socialnetwork', 'postEdit', 'showTitle', (formParams[formID]['showTitle'] ? 'Y' : 'N'));
			else
				formParams[formID]['showTitle'] = bShowTitleCopy;
		};

		window["setBlogPostFormSubmitted"] = function(value)
		{
			if (BX("blog-submit-button-save"))
			{
				if (value)
				{
					BX.addClass(BX("blog-submit-button-save"), 'ui-btn-clock');
				}
				else
				{
					BX.removeClass(BX("blog-submit-button-save"), 'ui-btn-clock');
				}
			}

			formParams[formID]["submitted"] = value;
		};

		window["submitBlogPostForm"] = function(editor, value)
		{
			if (typeof editor != "object")
			{
				value = editor;
				editor = LHEPostForm.getEditor(formParams[formID]['editorID']);
			}

			if (editor && editor.id == formParams[formID]['editorID'])
			{
				if(formParams[formID]["submitted"])
					return false;

				editor.SaveContent();

				if(!value)
				{
					value = 'save';
				}

				if(BX('blog-title').style.display == "none")
				{
					BX('POST_TITLE').value = "";
				}

				var submitButton = null;

				if (
					value == 'save'
					&& BX("blog-submit-button-save")
				)
				{
					submitButton = BX("blog-submit-button-save");
				}
				else if (
					value == 'draft'
					&& BX("blog-submit-button-draft")
				)
				{
					submitButton = BX("blog-submit-button-draft");
				}

				if (submitButton)
				{
					BX.addClass(submitButton, 'ui-btn-clock');
					BX.addClass(submitButton, 'ui-btn-disabled');
					submitButton.disabled = true;

					window.addEventListener('beforeunload', BX.proxy(function(event) { // is called on every sumbit, with or without dialog
						var __submitButton = this.submitButton;
						setTimeout(function() {
							BX.removeClass(__submitButton, 'ui-btn-clock');
							BX.removeClass(__submitButton, 'ui-btn-disabled');
							__submitButton.disabled = false;
							formParams[formID]["submitted"] = false;
						}, 3000); // timeout needed to process a form on a back-end
					}, { submitButton: submitButton}));
				}

				BX.submit(BX(formID), value);

				formParams[formID]["submitted"] = true;
			}
		};

		var onHandlerInited = function(obj, form) {
				if (form == formID)
				{
					formParams[formID]["handler"] = obj;
					BX.addCustomEvent(obj.eventNode, 'OnControlClick', function() {window.SBPETabs.changePostFormTab('message');});
					var OnAfterShowLHE = function()
						{
							var div = [BX('feed-add-post-form-notice-blockblogPostForm'),
								BX('feed-add-buttons-blockblogPostForm'),
								BX('feed-add-post-content-message-add-ins')];
							for (var ii = 0; ii < div.length; ii++)
							{
								if (!!div[ii])
								{
									BX.adjust(div[ii], { style : { display : "block", height : "auto", opacity : 1 } } );
								}
							}
							if(formParams[formID]["showTitle"])
								window['showPanelTitle_' + formID](true, false);
						},
						OnAfterHideLHE = function()
						{
							var ii,
								div = [
									BX('feed-add-post-form-notice-blockblogPostForm'),
									BX('feed-add-buttons-blockblogPostForm'),
									BX('feed-add-post-content-message-add-ins')];
							for (ii = 0; ii < div.length; ii++)
							{
								if (!!div[ii])
								{
									BX.adjust(div[ii], {style:{display:"block",height:"0px", opacity:0}});
								}
							}
							if(formParams[formID]["showTitle"])
								window['showPanelTitle_' + formID](false, false);
						};
					BX.addCustomEvent(obj.eventNode, 'OnAfterShowLHE', OnAfterShowLHE);
					BX.addCustomEvent(obj.eventNode, 'OnAfterHideLHE', OnAfterHideLHE);

					if (obj.eventNode.style.display == 'none')
						OnAfterHideLHE();
					else
						OnAfterShowLHE();

				}
			},
			onEditorInited = function(editor)
			{
				if (editor.id == formParams[formID]["editorID"])
				{
					formParams[formID]["editor"] = editor;
					if(formParams[formID]["autoSave"] != "N")
						new BlogPostAutoSave(formParams[formID]["autoSave"], formParams[formID]["restoreAutosave"]);

					var
						f = window[editor.id + 'Files'],
						handler = LHEPostForm.getHandler(editor.id),
						intId, id, node, needToReparse = [],
						controller = null;
					for (id in handler['controllers'])
					{
						if (handler['controllers'].hasOwnProperty(id))
						{
							if (handler['controllers'][id]["parser"] && handler['controllers'][id]["parser"]["bxTag"] == "postimage")
							{
								controller = handler['controllers'][id];
								break;
							}
						}
					}
					var closure = function(a, b) { return function() { a.insertFile(b); } },
						closure2 = function(a, b, c) { return function() {
							if (controller)
							{
								controller.deleteFile(b, {});
								BX.remove(BX('wd-doc' + b));
								BX.ajax({ method: 'GET', url: c});
							}
							else
							{
								a.deleteFile(b, c, a, {controlID : 'common'});
							}
						} };

					for (intId in f)
					{
						if (f.hasOwnProperty(intId))
						{
							if (controller)
							{
								controller.addFile(f[intId]);
							}
							else
							{
								id = handler.checkFile(intId, "common", f[intId]);
								needToReparse.push(intId);
								if (!!id && BX('wd-doc'+intId) && !BX('wd-doc'+intId).hasOwnProperty("bx-bound"))
								{
									BX('wd-doc'+intId).setAttribute('bx-bound', 'Y');
									if ((node = BX.findChild(BX('wd-doc'+intId), {className: 'feed-add-img-wrap'}, true, false)) && node)
									{
										BX.bind(node, "click", closure(handler, id));
										node.style.cursor = "pointer";
									}
									if ((node = BX.findChild(BX('wd-doc'+intId), {className: 'feed-add-img-title'}, true, false)) && node)
									{
										BX.bind(node, "click", closure(handler, id));
										node.style.cursor = "pointer";
									}
									if ((node = BX.findChild(BX('wd-doc'+intId), {className: 'feed-add-post-del-but'}, true, false)) && node)
									{
										BX.bind(node, "click", closure2(handler, intId, f[intId]['del_url']));
										node.style.cursor = "pointer";
									}
								}
							}
							if ((node = BX.findChild(BX('wd-doc'+intId), {className: 'feed-add-post-del-but'}, true, false)) && node)
							{
								BX.bind(node, "click", closure2(handler, intId, f[intId]['del_url']));
								node.style.cursor = "pointer";
							}
						}
					}

					if (needToReparse.length > 0)
					{
						editor.SaveContent();
						var content = editor.GetContent();
						content = content.replace(new RegExp('\\&\\#91\\;IMG ID=(' + needToReparse.join("|") + ')([WIDTHHEIGHT=0-9 ]*)\\&\\#93\\;','gim'), '[IMG ID=$1$2]');
						editor.SetContent(content);
						editor.Focus();
					}
				}
			};

		BX.addCustomEvent(window, 'onInitialized', onHandlerInited);
		if (formParams[formID]["handler"])
			onHandlerInited(formParams[formID]["handler"], formID);
		BX.addCustomEvent(window, 'OnEditorInitedAfter', onEditorInited);
		if (formParams[formID]["editor"])
			onEditorInited(formParams[formID]["editor"]);

		BX.addCustomEvent(window, 'onSocNetLogMoveBody', function(p){ if(p == 'sonet_log_microblog_container') { reinit(formID); } } );

		BX.ready(function() {
			if (BX.browser.IsIE() && BX('POST_TITLE'))
			{
				var showTitlePlaceholderBlur = function(e)
				{
					if (!this.value || this.value == this.getAttribute("placeholder")) {
						this.value = this.getAttribute("placeholder");
						BX.removeClass(this, 'feed-add-post-inp-active');
					}
				};
				BX.bind(BX('POST_TITLE'), "blur", showTitlePlaceholderBlur);
				showTitlePlaceholderBlur.apply(BX('POST_TITLE'));
				BX('POST_TITLE').__onchange = BX.delegate(
					function(e) {
						if ( this.value == this.getAttribute("placeholder") ) { this.value = ''; }
						if ( this.className.indexOf('feed-add-post-inp-active') < 0 ) { BX.addClass(this, 'feed-add-post-inp-active'); }
					},
					BX('POST_TITLE')
				);
				BX.bind(BX('POST_TITLE'), "click", BX('POST_TITLE').__onchange);
				BX.bind(BX('POST_TITLE'), "keydown", BX('POST_TITLE').__onchange);
				BX.bind(BX('POST_TITLE').form, "submit", function(){if(BX('POST_TITLE').value == BX('POST_TITLE').getAttribute("placeholder")){BX('POST_TITLE').value='';}});
			}
			if (params['activeTab'] !== '')
				window.SBPETabs.changePostFormTab(params['activeTab']);
		});
	};
})();