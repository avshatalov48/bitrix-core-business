import {Type, Loc, Dom, Event, Runtime, ajax as Ajax } from 'main.core';
import { BaseEvent, EventEmitter } from 'main.core.events';
import PostForm from './form';
import { MenuManager } from "main.popup";

export default class PostFormTabs extends EventEmitter
{
	static instance = null;

	inited: boolean = false;

	tabs: { [key: string]: any } = {};
	bodies: { [key: string]: any } = {};
	active = null;
	animation = null;
	animationStartHeight:number = 0;
	previousTab = null;
	menu = null;
	listsMenu = null;
	menuItems = [];
	lastWait: HTMLElement[] = [];
	clickDisabled: boolean = false;

	tabContainer: HTMLElement = null;
	arrow: HTMLElement = null;

	config = {
		id: {
			message: 'message',
			task: 'tasks',
			calendar: 'calendar',
			file: 'file',
			gratitude: 'grat',
			important: 'important',
			vote: 'vote',
			more: 'more',
			listItem: 'lists',
		},
	};

	static setInstance(instance)
	{
		PostFormTabs.instance = instance;
	}

	static getInstance()
	{
		if (PostFormTabs.instance === null)
		{
			new PostFormTabs();
		}

		return PostFormTabs.instance;
	}

	constructor()
	{
		super();
		this.setEventNamespace('BX.Socialnetwork.Livefeed.Post.Form.Tabs');
		this.init();
		this.emit('onInitialized', { tabsInstance: this });

		PostFormTabs.setInstance(this);
		window.SBPETabs = this;
	}

	init(): void
	{
		this.tabContainer = document.getElementById('feed-add-post-form-tab');
		this.arrow = document.getElementById('feed-add-post-form-tab-arrow');
		this.tabs = {};
		this.bodies = {};

		const tabsList = this.tabContainer && this.tabContainer.querySelectorAll('span.feed-add-post-form-link');
		if (tabsList)
		{
			for (let i = 0; i < tabsList.length; i++)
			{
				const id = tabsList[i].getAttribute('id').replace('feed-add-post-form-tab-', '');
				const limited = tabsList[i].getAttribute('limited');

				this.tabs[id] = tabsList[i];

				if (this.tabs[id].style.display === 'none')
				{
					this.menuItems.push({
						tabId : id,
						text : tabsList[i].getAttribute('data-name'),
						className : `menu-popup-no-icon feed-add-post-form-${id} feed-add-post-form-${id}-more`,
						onclick : this.createOnClick(id, tabsList[i].getAttribute('data-name'), tabsList[i].getAttribute('data-onclick'), (tabsList[i].getAttribute('data-limited') === 'Y'))
					});

					this.tabs[id] = this.tabs[id].parentNode;
				}

				this.bodies[id] = document.getElementById(`feed-add-post-content-${id}`);
			}
		}

		if (!!this.tabs[this.config.id.file])
		{
			this.bodies[this.config.id.file] = [ this.bodies[this.config.id.message] ];
		}

		if (!!this.tabs[this.config.id.calendar])
		{
			this.bodies[this.config.id.calendar] = [ this.bodies[this.config.id.calendar] ];
		}

		if (!!this.tabs[this.config.id.vote])
		{
			this.bodies[this.config.id.vote] = [
				this.bodies[this.config.id.message],
				this.bodies[this.config.id.vote],
			];
		}

		if (!!this.tabs[this.config.id.more])
		{
			this.bodies[this.config.id.more] = null;
		}

		if (!!this.tabs[this.config.id.important])
		{
			this.bodies[this.config.id.important] = [
				this.bodies[this.config.id.message],
				this.bodies[this.config.id.important],
			];
		}

		if (!!this.tabs[this.config.id.gratitude])
		{
			this.bodies[this.config.id.gratitude] = [
				this.bodies[this.config.id.message],
				this.bodies[this.config.id.gratitude],
			];
		}

		if (!!this.tabs[this.config.id.listItem])
		{
			this.bodies[this.config.id.listItem] = [ this.bodies[this.config.id.listItem] ];
		}

		if (!!this.tabs[this.config.id.task])
		{
			this.bodies[this.config.id.task] = [ this.bodies[this.config.id.task] ];
		}

		for (let ii in this.bodies)
		{
			if (
				this.bodies.hasOwnProperty(ii)
				&& Type.isDomNode(this.bodies[ii])
			)
			{
				this.bodies[ii] = [ this.bodies[ii] ];
			}

		}

		this.inited = true;
		this.previousTab = false;

		const uploadFileNode = document.getElementById('bx-b-uploadfile-blogPostForm');
		if (uploadFileNode)
		{
			uploadFileNode.setAttribute('bx-press', 'pressOut');
			Event.bind(uploadFileNode, 'mousedown', () => {
				uploadFileNode.setAttribute("bx-press", (uploadFileNode.getAttribute("bx-press") == "pressOut" ? "pressOn" : "pressOut"));
			});
		}

		const form = document.getElementById('blogPostForm');
		if (!form)
		{
			return
		}

		if (!form.changePostFormTab)
		{
			form.appendChild(Dom.create('INPUT', {
				props : {
					type: 'hidden',
					name: 'changePostFormTab',
					value: ''
				}
			}));
		}

		this.subscribe('changePostFormTab', (event) => {
			const { type } = event.getData();

			if (type === this.config.id.more)
			{
				return;
			}

			form.changePostFormTab.value = type;

			if (form['UF_BLOG_POST_IMPRTNT'])
			{
				form['UF_BLOG_POST_IMPRTNT'].value = (type === this.config.id.important ? '1' : '0');
			}
		});
	};

	createOnClick(id, name:string, onclick: string, limited: boolean)
	{
		return () => {
			const btn = document.getElementById('feed-add-post-form-link-more');
			const btnText = document.getElementById('feed-add-post-form-link-text');

			if (!limited)
			{
				btnText.innerHTML = name;
				if (id !== this.config.id.listItem)
				{
					btn.className = `feed-add-post-form-link feed-add-post-form-link-more feed-add-post-form-link-active feed-add-post-form-${id}-link`;
					this.changePostFormTab(id, false);
				}
				else
				{
					btn.className = `feed-add-post-form-link feed-add-post-form-link-more feed-add-post-form-${id}-link`;
				}
			}

			if (Type.isStringFilled(onclick))
			{
				BX.evalGlobal(onclick);
			}

			this.menu.popupWindow.close();
		}
	};

	changePostFormTab(type, iblock)
	{
		if (this.clickDisabled)
		{
			return false;
		}

		return this.setActive(type, iblock);
	};

	setActive(type?:string, iblock)
	{
		if (
			Type.isNull(type)
			|| (
				this.active === type
				&& type !== this.config.id.listItem
			)
		)
		{
			return this.active;
		}
		else if (!this.tabs[type])
		{
			return false;
		}

		const needAnimation = (type !== this.config.id.task || this.isTaskTabLoaded());
		if (needAnimation)
		{
			this.startAnimation();
		}

		for (let ii in this.tabs)
		{
			if (this.tabs.hasOwnProperty(ii) && ii !== type)
			{
				this.tabs[ii].classList.remove('feed-add-post-form-link-active');
				if (
					this.bodies[ii] == null
					|| this.bodies[type] == null
				)
				{
					continue;
				}

				for (let jj = 0; jj < this.bodies[ii].length; jj++)
				{
					if (this.bodies[type][jj] != this.bodies[ii][jj])
					{
						Dom.adjust(this.bodies[ii][jj], {
							style: {
								display: 'none'
							}
						});
					}

				}
			}
		}

		if (!!this.tabs[type])
		{
			this.active = type;

			const tabPosTab = BX.pos(this.tabs[type], true);

			this.arrow.style.display = 'block';
			this.arrow.style.top = `${tabPosTab.bottom}px`;

			const leftStart = parseInt(this.arrow.style.left) || 0;
			const widthStart = parseInt(this.arrow.style.width) || 0;

			(new BX.easing({
				duration : 200,
				start: { left: leftStart, width:  widthStart },
				finish: { left: tabPosTab.left, width: tabPosTab.width },
				transition: BX.easing.makeEaseInOut(BX.easing.transitions.quart),
				step : (state) => {
					this.arrow.style.left = `${state.left}px`;
					this.arrow.style.width = `${state.width}px`;
				},

				complete : () => {
					this.arrow.style.display = 'none';
					this.tabs[type].classList.add('feed-add-post-form-link-active');
				}
			})).animate();

			if (
				this.previousTab === this.config.id.file
				|| type === this.config.id.file
			)
			{
				let hasValuesFile = false;
				let hasValuesDocs = false;

				const messageBody = document.getElementById('divoPostFormLHE_blogPostForm');

				if (
					!!messageBody
					&& !!messageBody.childNodes
					&& messageBody.childNodes.length > 0
				)
				{
					for (let ii in messageBody.childNodes)
					{
						if (!messageBody.childNodes.hasOwnProperty(ii))
						{
							continue;
						}

						if (messageBody.childNodes[ii].className === 'file-selectdialog')
						{
							const nodeFile = messageBody.childNodes[ii];
							const values1 = nodeFile.querySelector('.file-placeholder-tbody');
							const values2 = nodeFile.querySelector('.feed-add-photo-block');
							if (values1.rows > 0 || !!values2 && values2.length > 1)
							{
								hasValuesFile = true;
							}
						}
						else if (
							Type.isStringFilled(messageBody.childNodes[ii].className)
							&& (
								messageBody.childNodes[ii].className.indexOf('wduf-selectdialog') >= 0
								|| messageBody.childNodes[ii].className.indexOf('diskuf-selectdialog') >= 0
							)
						)
						{
							const nodeDocs = messageBody.childNodes[ii];
							const webdavValues = nodeDocs.querySelectorAll('.wd-inline-file');

							hasValuesDocs = (!!webdavValues && webdavValues.length > 0);
						}
						else if(
							Type.isDomNode(messageBody.childNodes[ii])
							&& messageBody.childNodes[ii].classList
							&& !messageBody.childNodes[ii].classList.contains('urlpreview')
							&& !messageBody.childNodes[ii].classList.contains('feed-add-post-strings-blocks')
						)
						{
							Dom.adjust(messageBody.childNodes[ii], {
								style: {
									display: (type === this.config.id.file ? 'none' : '')
								}
							});
						}
					}

					if (type === this.config.id.file)
					{
						if (
							!!window['PlEditorblogPostForm']
							&& !window['PlEditorblogPostForm'].SBPEBinded
						)
						{
							window['PlEditorblogPostForm'].SBPEBinded = true;

							EventEmitter.subscribe(window["PlEditorblogPostForm"].eventNode, 'onUploadsHasBeenChanged', (event: BaseEvent) => {

								const wdObj = event.getData()[1];

								if (
									wdObj.dialogName === 'AttachFileDialog'
									&& wdObj.urlUpload.indexOf('&dropped=Y') < 0
								)
								{
									wdObj.urlUpload = wdObj.agent.uploadFileUrl = wdObj.urlUpload.replace('&random_folder=Y', '&dropped=Y');
								}

								document.getElementById('bx-b-uploadfile-blogPostForm').setAttribute('bx-press', 'pressOn');
								if (this.active !== this.config.id.file)
								{
									this.changePostFormTab(this.config.id.message);
								}
							});
						}

						window['PlEditorblogPostForm'].controllerInit('show');
						messageBody.classList.add('feed-add-post-form', 'feed-add-post-edit-form', 'feed-add-post-edit-form-file');
					}
					else
					{
						messageBody.classList.remove('feed-add-post-form', 'feed-add-post-edit-form', 'feed-add-post-edit-form-file');
						if (
							!hasValuesFile
							&& !hasValuesDocs
							&& document.getElementById('bx-b-uploadfile-blogPostForm').getAttribute('bx-press') === 'pressOut'
							&& !!window['PlEditorblogPostForm']
						)
						{
							window['PlEditorblogPostForm'].controllerInit('hide');
						}
					}
				}
			}

			const editorForm = document.getElementById('divoPostFormLHE_blogPostForm');
			if (
				editorForm
				&& editorForm.style.display === 'none'
			)
			{
				EventEmitter.emit(editorForm, 'OnShowLHE', new BaseEvent({
					compatData: [ 'justShow' ]
				}));
			}

			if (type === this.config.id.listItem)
			{
				EventEmitter.emit('onDisplayClaimLiveFeed', new BaseEvent({
					compatData: [ iblock ]
				}));
			}

			this.previousTab = type;

			if (!!this.bodies[type])
			{
				for (let jj = 0; jj < this.bodies[type].length; jj++)
				{
					if (!!this.bodies[type][jj])
					{
						Dom.adjust(this.bodies[type][jj], {
							style: {
								display: 'block'
							}
						});
					}
				}
			}
		}

		if (needAnimation)
		{
			this.endAnimation();
		}

		if(type !== this.config.id.listItem)
		{
			this.restoreMoreMenu();
		}

		this.emit('changePostFormTab', { type });

		return this.active;
	};

	isTaskTabLoaded()
	{
		const contentContainer = document.getElementById('feed-add-post-content-tasks-container');
		return (contentContainer && contentContainer.children.length);
	};

	collapse()
	{
		this.active = null;
		let postEditSlider = false;

		const currentSlider = (window !== top.window ? BX.SidePanel.Instance.getSliderByWindow(window) : null);

		if (window !== top.window) // slider
		{
			if (
				currentSlider
				&& currentSlider.url.match(/\/user\/(\d+)\/blog\/edit\//)
			)
			{
				postEditSlider = true;
			}
		}

		if (!postEditSlider)
		{
			this.changePostFormTab("message");
			const formInstance = PostForm.getInstance();
			if (
				formInstance
				&& Type.isDomNode(formInstance.containerMicroInner)
			)
			{
				formInstance.containerMicroInner.style.display = 'block';
			}
			this.startAnimation();
		}

		const editorForm = document.getElementById('divoPostFormLHE_blogPostForm')
		if (editorForm)
		{
			EventEmitter.emit(editorForm, 'OnShowLHE', new BaseEvent({
				compatData: [ false ]
			}));
		}

		EventEmitter.emit('onExtAutoSaveReset_blogPostForm', new BaseEvent({
			compatData: [ ]
		}));

		if (!postEditSlider)
		{
			this.endAnimation();
		}
		else
		{
			if (currentSlider)
			{
				EventEmitter.emit(window.top, 'SidePanel.Slider:onClose', new BaseEvent({
					compatData: [ currentSlider.getEvent('onClose') ]
				}));
			}
			BX.SidePanel.Instance.close();
		}
	};

	startAnimation():void
	{
		if (this.animation)
		{
			this.animation.stop();
		}

		const container = document.getElementById('microblog-form');
		if (!container)
		{
			return;
		}

		if (PostForm.getInstance().animationStartHeight > 0)
		{
			this.animationStartHeight = PostForm.getInstance().animationStartHeight;
			PostForm.getInstance().animationStartHeight = 0;
		}
		else
		{
			this.animationStartHeight = container.parentNode.offsetHeight;
		}

		container.parentNode.style.height = `${this.animationStartHeight}px`;
		container.parentNode.style.overflowY = 'hidden';
		container.parentNode.style.position = 'relative';
		container.style.opacity = 0;
	};

	endAnimation():void
	{
		const container = document.getElementById('microblog-form');
		if (!container)
		{
			return;
		}

		this.animation = new BX.easing({
			duration: 500,
			start: {
				height: this.animationStartHeight,
				opacity: 0
			},
			finish: {
				height: container.offsetHeight + container.offsetTop,
				opacity: 100
			},
			transition: BX.easing.makeEaseOut(BX.easing.transitions.quart),
			step: (state) => {
				container.parentNode.style.height = `${state.height}px`;
				container.style.opacity = state.opacity / 100;
			},

			complete : () => {
				container.style.cssText = '';
				container.parentNode.style.cssText = '';
				this.animation = null;
			}
		});

		this.animation.animate();
	};

	showMoreMenu()
	{
		if (!this.menu)
		{
			this.menu = MenuManager.create(
				'feed-add-post-form-popup',
				document.getElementById('feed-add-post-form-link-text'),
				this.menuItems,
				{
					className: 'feed-add-post-form-popup',
					closeByEsc: true,
					offsetTop: 5,
					offsetLeft: 3,
					angle: true
				}
			);
		}

		this.menu.popupWindow.show();
	};

	restoreMoreMenu(): void
	{
		const itemCnt = this.menuItems.length;
		if (itemCnt < 1)
		{
			return;
		}

		for (let i = 0; i < itemCnt; i++)
		{
			if (this.active === this.menuItems[i]['tabId'])
			{
				return;
			}
		}

		const btn = document.getElementById('feed-add-post-form-link-more');
		const btnText = document.getElementById('feed-add-post-form-link-text');

		btn.className = 'feed-add-post-form-link feed-add-post-form-link-more';
		btnText.innerHTML = Loc.getMessage('SBPE_MORE');
	};

	getTaskForm()
	{
		const tabContainer = (
			document.getElementById('feed-add-post-form-tab-tasks')
			&& document.getElementById('feed-add-post-form-tab-tasks').style.display !== 'none'
				? document.getElementById('feed-add-post-form-tab-tasks')
				: document.getElementById('feed-add-post-form-link-more')
		);
		const content = document.getElementById('feed-add-post-content-tasks');
		const contentContainer = document.getElementById('feed-add-post-content-tasks-container');

		if (
			contentContainer
			&& contentContainer.innerHTML.length <= 0
			&& !this.clickDisabled
		)
		{
			this.clickDisabled = true;
			PostForm.getInstance().showWait(contentContainer);
			this.startAnimation();

			const componentParameters = {
				GROUP_ID: Loc.getMessage('TASK_SOCNET_GROUP_ID'),
				PATH_TO_USER_TASKS: Loc.getMessage('PATH_TO_USER_TASKS'),
				PATH_TO_USER_TASKS_TASK: Loc.getMessage('PATH_TO_USER_TASKS_TASK'),
				PATH_TO_GROUP_TASKS: Loc.getMessage('PATH_TO_GROUP_TASKS'),
				PATH_TO_GROUP_TASKS_TASK: Loc.getMessage('PATH_TO_GROUP_TASKS_TASK'),
				PATH_TO_USER_PROFILE: Loc.getMessage('PATH_TO_USER_PROFILE'),
				PATH_TO_GROUP: Loc.getMessage('PATH_TO_GROUP'),
				PATH_TO_USER_TASKS_PROJECTS_OVERVIEW: Loc.getMessage('PATH_TO_USER_TASKS_PROJECTS_OVERVIEW'),
				PATH_TO_USER_TASKS_TEMPLATES: Loc.getMessage('PATH_TO_USER_TASKS_TEMPLATES'),
				PATH_TO_USER_TEMPLATES_TEMPLATE: Loc.getMessage('PATH_TO_USER_TEMPLATES_TEMPLATE'),
				ENABLE_FOOTER: 'N',
				TEMPLATE_CONTROLLER_ID: 'livefeed_task_form',
				ENABLE_FORM: 'N',
				BACKURL: Loc.getMessage('TASK_SUBMIT_BACKURL')
			};

			Ajax.runComponentAction('bitrix:tasks.task', 'uiEdit', {
				mode: 'class',
				data: {
					parameters: {
						COMPONENT_PARAMETERS: componentParameters,
					},
				},
			}).then((response) => {

				Runtime.html(contentContainer, response.data.html)
					.then(() => {
						this.clickDisabled = false;
						this.closeWait(contentContainer);
						this.endAnimation();

						EventEmitter.emit(
							document.getElementById('divlivefeed_task_form'),
							'OnShowLHE',
							new BaseEvent({compatData: ['justShow']})
						);
					})
				;

				Dom.adjust(content, {
					style: {
						display : 'block',
					},
				});
			}, (response) => {

				this.clickDisabled = false;
				this.closeWait(contentContainer);
				this.endAnimation();

				if (response.errors && response.errors.length)
				{
					const errors = [];

					response.errors.forEach((error) => {
						errors.push(error.message);
					});
					throw new Error(errors.join(' '));
				}
			});
		}
		else
		{
			this.startAnimation();
			this.endAnimation();
		}
	};

	closeWait(node)
	{
		const waiterNode = node.bxmsg;

		if (
			waiterNode
			&& waiterNode.parentNode
		)
		{
			for (let i=0,len=this.lastWait.length; i<len; i++)
			{
				if (waiterNode === this.lastWait[i])
				{
					this.lastWait = BX.util.deleteFromArray(this.lastWait, i);
					break;
				}
			}

			waiterNode.parentNode.removeChild(waiterNode);
			if (node)
			{
				node.bxmsg = null;
			}

			Dom.clean(waiterNode);
			Dom.remove(waiterNode);
		}
	};

	getLists()
	{
		const tabContainer = (
			document.getElementById('feed-add-post-form-tab-lists')
			&& document.getElementById('feed-add-post-form-tab-lists').style.display !== 'none'
				? document.getElementById('feed-add-post-form-tab-lists')
				: document.getElementById('feed-add-post-form-link-more')
		);
		let tabs = tabContainer.querySelectorAll('span.feed-add-post-form-link-lists');
		let tabsDefault = tabContainer.querySelectorAll('span.feed-add-post-form-link-lists-default');
		let menuItemsListsDefault = [];
		let menuItemsLists = [];

		if(tabs.length)
		{
			menuItemsLists = this.getMenuItems(tabs, this.createOnclickLists);
			menuItemsListsDefault = this.getMenuItemsDefault(tabsDefault);
			menuItemsLists = menuItemsLists.concat(menuItemsListsDefault);
			this.showMoreMenuLists(menuItemsLists);
		}
		else
		{
			let siteId = null;

			if(document.getElementById('bx-lists-select-site-id'))
			{
				siteId = document.getElementById('bx-lists-select-site-id').value;
			}

			Ajax({
				method: 'POST',
				dataType: 'json',
				url: '/bitrix/components/bitrix/socialnetwork.blog.post.edit/post.ajax.php',
				data: {
					bitrix_processes: 1,
					siteId: siteId,
					sessid: Loc.getMessage('bitrix_sessid')
				},
				onsuccess: (result) => {
					if(result.success)
					{
						for(let k in result.lists)
						{
							if (!result.lists.hasOwnProperty(k))
							{
								continue;
							}

							tabContainer.appendChild(Dom.create('span', {
								attrs: {
									'data-name': result.lists[k].NAME,
									'data-picture': result.lists[k].PICTURE,
									'data-description': result.lists[k].DESCRIPTION,
									'data-picture-small': result.lists[k].PICTURE_SMALL,
									'data-code': result.lists[k].CODE,
									'iblockId': result.lists[k].ID
								},
								props: {
									className: 'feed-add-post-form-link-lists',
									id: 'feed-add-post-form-tab-lists'
								},
								style: {
									display: 'none'
								}
							}));
						}

						tabs = tabContainer.querySelectorAll('span.feed-add-post-form-link-lists');
						menuItemsLists = this.getMenuItems(tabs, this.createOnclickLists);

						if(!tabsDefault.length)
						{
							for(let k in result.permissions)
							{
								if (!result.permissions.hasOwnProperty(k))
								{
									continue;
								}

								let onclick;
								if(k === 'new')
								{
									onclick = `document.location.href = "${document.getElementById('bx-lists-lists-page').value}0/edit/"`;
								}
								else if(k === 'market')
								{
									if(
										result.admin
										&& document.getElementById('bx-lists-lists-page')
									)
									{
										onclick = `document.location.href = "${document.getElementById('bx-lists-lists-page').value}?bp_catalog=y"`;
									}
									else
									{
										if(document.getElementById('bx-lists-random-string'))
										{
											onclick = `BX.Lists["LiveFeedClass_${BX('bx-lists-random-string').value}"].errorPopup("${Loc.getMessage('LISTS_CATALOG_PROCESSES_ACCESS_DENIED')}");`;
										}
									}
								}
								else if(k === 'settings')
								{
									onclick = `document.location.href = "${BX('bx-lists-lists-page').value}"`;
								}

								tabContainer.appendChild(Dom.create('span', {
									attrs: {
										'data-name': result.permissions[k],
										'data-picture-small': '',
										'data-key': k,
										'data-onclick': onclick
									},
									props: {
										className: 'feed-add-post-form-link-lists-default',
										id: 'feed-add-post-form-tab-lists'
									},
									style: {
										display: 'none'
									}
								}));
							}

							tabsDefault = tabContainer.querySelectorAll('span.feed-add-post-form-link-lists-default');
						}

						menuItemsListsDefault = this.getMenuItemsDefault(tabsDefault);
						menuItemsLists = menuItemsLists.concat(menuItemsListsDefault);
						this.showMoreMenuLists(menuItemsLists);
					}
					else
					{
						tabContainer.appendChild(Dom.create('span', {
							attrs: {
								'data-name': result.error,
								'data-picture-small': ''
							},
							props: {
								className: 'feed-add-post-form-link-lists-default',
								id: 'feed-add-post-form-tab-lists'
							},
							style: {
								display: 'none'
							}
						}));

						tabs = tabContainer.querySelectorAll('span.feed-add-post-form-link-lists-default');
						menuItemsLists = this.getMenuItems(tabs, false);
						this.showMoreMenuLists(menuItemsLists);
					}
				}
			});
		}
	};

	getMenuItems(tabs, createOnclickLists)
	{
		const menuItemsLists = [];

		for (let i = 0; i < tabs.length; i++)
		{
			const id = tabs[i].getAttribute('id').replace('feed-add-post-form-tab-', '');

			if(createOnclickLists)
			{
				menuItemsLists.push({
					tabId: id,
					text: BX.util.htmlspecialchars(tabs[i].getAttribute("data-name")),
					className: `feed-add-post-form-${id} feed-add-post-form-${id}-item`,
					onclick: createOnclickLists(
						id,
						[
							tabs[i].getAttribute('iblockId'),
							tabs[i].getAttribute('data-name'),
							tabs[i].getAttribute('data-description'),
							tabs[i].getAttribute('data-picture'),
							tabs[i].getAttribute('data-code')
						]
					)
				});
			}
			else
			{
				menuItemsLists.push({
					tabId: id,
					text: tabs[i].getAttribute('data-name'),
					className: `feed-add-post-form-${id}`,
					onclick: ''
				});
			}
		}

		return menuItemsLists;
	};

	getMenuItemsDefault(tabs)
	{
		const menuItemsLists = [];

		for (let i = 0; i < tabs.length; i++)
		{
			menuItemsLists.push({
				text: BX.util.htmlspecialchars(tabs[i].getAttribute('data-name')),
				className: `feed-add-post-form-lists-default-${tabs[i].getAttribute('data-key')}`,
				onclick: tabs[i].getAttribute('data-onclick')
			});
		}

		return menuItemsLists;
	};

	showMoreMenuLists(menuItemsLists)
	{
		const menuBindElement = (
			document.getElementById('feed-add-post-form-tab-lists').style.display !== 'none'
				? document.getElementById('feed-add-post-form-tab-lists')
				: document.getElementById('feed-add-post-form-link-more')
		);

		this.listsMenu = MenuManager.create(
			'lists',
			menuBindElement,
			menuItemsLists,
			{
				closeByEsc: true,
				offsetTop: 5,
				offsetLeft: 12,
				angle: true
			}
		);

		const spanIcon = document.getElementById('popup-window-content-menu-popup-lists').querySelectorAll('span.menu-popup-item-icon');
		let spanDataPicture = menuBindElement.querySelectorAll('span.feed-add-post-form-link-lists');
		const spanDataPictureDefault = menuBindElement.querySelectorAll('span.feed-add-post-form-link-lists-default');
		spanDataPicture = Array.from(spanDataPicture).concat(Array.from(spanDataPictureDefault));

		for(let i = 0; i < spanIcon.length; i++)
		{
			if(!spanDataPicture[i].getAttribute('data-picture-small'))
			{
				continue;
			}

			spanIcon[i].innerHTML = spanDataPicture[i].getAttribute('data-picture-small');
		}

		this.listsMenu.popupWindow.show();
	};

	createOnclickLists(id, iblock)
	{
		return () =>
		{
			PostFormTabs.getInstance().changePostFormTab(id, iblock);
			PostFormTabs.getInstance().listsMenu.popupWindow.close();
			PostFormTabs.getInstance().menu.popupWindow.close();
		}
	};
}
