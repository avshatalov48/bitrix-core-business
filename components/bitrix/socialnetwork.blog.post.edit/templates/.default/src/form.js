import {Type, ajax as Ajax, Loc, Dom, Reflection } from 'main.core';
import { BaseEvent, EventEmitter } from 'main.core.events';
import AjaxProcessor from './ajaxprocessor';

export default class PostForm
{
	static instance = null;

	lazyLoad: boolean = null;
	ajaxUrl: string = '';
	inited: boolean = false;
	loaded: boolean = false;
	container: HTMLElement = null;
	containerMicro: HTMLElement = null;
	containerMicroInner: HTMLElement = null;
	clickDisabled: boolean = false;
	lastWait: HTMLElement[] = [];
	animationStartHeight: number = 0;
	initedEditorsList: string[] = [];
	options: { [key: string]: any } = {};

	static setInstance(instance)
	{
		PostForm.instance = instance;
	}

	static getInstance()
	{
		return PostForm.instance;
	}

	constructor(params)
	{
		this.init(params);
		PostForm.setInstance(this);
	}

	setOption(key: string, value: any): void
	{
		if (!Type.isStringFilled(key))
		{
			return;
		}

		this.options[key] = value;
	}

	onShow(): void
	{
		if(
			!Type.isStringFilled(this.options.startVideoRecorder)
			|| this.options.startVideoRecorder !== 'Y'
		)
		{
			return;
		}

		setTimeout(() => {
			const editorForm = document.getElementById('divoPostFormLHE_blogPostForm');
			if (!editorForm)
			{
				return;
			}

			EventEmitter.emit(editorForm, 'OnShowLHE', new BaseEvent({
				compatData: [ 'justShow' ]
			}));

			BX.VideoRecorder.start('blogPostForm', 'post');
		}, 500);
	};

	onSliderClose(): void
	{
		const sliderInstance = BX.SidePanel.Instance.getTopSlider();
		if (!sliderInstance)
		{
			return;
		}

		BX.SidePanel.Instance.postMessageAll(window, 'SidePanel.Wrapper:onClose', {
			sliderData: sliderInstance.getData(),
		});
	};

	init(params: { [key: string]: any }): void
	{
		if (this.inited !== true)
		{
			this.inited = true;
			this.lazyLoad = !Type.isUndefined(params.lazyLoad) ? !!params.lazyLoad : false;
			this.ajaxUrl = Type.isStringFilled(params.ajaxUrl) ? params.ajaxUrl : '';
			this.container = Type.isDomNode(params.container) ? params.container : null;
			this.containerMicro = Type.isDomNode(params.containerMicro) ? params.containerMicro : null;
			this.containerMicroInner = Type.isDomNode(params.containerMicroInner) ? params.containerMicroInner : null;
			this.successPostId = !Type.isUndefined(params.successPostId) && parseInt(params.successPostId) > 0 ? parseInt(params.successPostId) : 0;

			//region dnd
			if (this.containerMicro)
			{
				this.containerMicro.setAttribute('dropzone', 'copy f:*\/*');

				let timerListenEnter = 0;
				const stopListenEnter = (event) => {
					if (timerListenEnter > 0)
					{
						clearTimeout(timerListenEnter);
						timerListenEnter = 0;
					}
					event.stopPropagation();
					event.preventDefault();
				};
				const fireDragEnter = (event) => {
					stopListenEnter(event);
					this.containerMicro.click();
				};
				const startListenEnter = (event) => {
					if (timerListenEnter <= 0)
					{
						timerListenEnter = setTimeout(() => { fireDragEnter(event); }, 3000);
					}
					event.stopPropagation();
					event.preventDefault();
				};

				this.containerMicro.addEventListener('dragover', startListenEnter);
				this.containerMicro.addEventListener('dragenter', startListenEnter);
				this.containerMicro.addEventListener('dragleave', stopListenEnter);
				this.containerMicro.addEventListener('dragexit', stopListenEnter);
				this.containerMicro.addEventListener('drop', stopListenEnter);
			}
			//region
		}

		const sliderInstance = BX.SidePanel.Instance.getTopSlider();
		if (sliderInstance)
		{
			if (this.successPostId > 0)
			{
				BX.SidePanel.Instance.postMessage(window, 'Socialnetwork.PostForm:onAdd', {
					originatorSliderId: sliderInstance.getData().get('sliderId'),
					successPostId: this.successPostId,
				});

				BX.SidePanel.Instance.close();
			}
			else if (!sliderInstance.getData().get('initialized'))
			{
				EventEmitter.subscribe(sliderInstance, 'BX.Socialnetwork.SidePanel.Slider:onClose', this.onSliderClose);
				sliderInstance.getData().set('initialized', true);
			}
		}
	};

	get(params: { [key: string]: any }): void
	{
		if (this.clickDisabled)
		{
			return;
		}

		if (
			this.lazyLoad
			&& !this.loaded
		)
		{
			this.clickDisabled = true;
			this.animationStartHeight = this.containerMicro.offsetHeight;

			if (
				Type.isStringFilled(params.loaderType)
				&& params.loaderType === 'tab'
			)
			{
				this.showWaitTab();
			}
			else
			{
				this.containerMicroInner.style.display = 'none';
				this.showWait(this.containerMicro);
			}

			Ajax({
				method: 'POST',
				dataType: 'json',
				url: this.ajaxUrl,
				data: {
					action: 'SBPE_get_full_form',
					sessid: Loc.getMessage('bitrix_sessid')
				},
				onsuccess: (result) => {
					this.loaded = true;
					this.clickDisabled = false;
					this.closeWait();

					if(result.success)
					{
						this.processAjaxBlock(result.PROPS, params.callback);
					}
				},
				onfailure: () => {
					this.clickDisabled = false;
					this.closeWait();
					this.containerMicroInner.style.display = 'block';
				}
			});
		}
		else if (Type.isFunction(params.callback))
		{
			params.callback();
		}
	};

	processAjaxBlock(block, callbackExternal: Function): void
	{
		if (!block)
		{
			return;
		}

		const processor = new AjaxProcessor();

		processor.processCSS(block, () => {
			processor.processAjaxBlockInsertHTML(block, this.container, callbackExternal);
		});
		processor.processExternalJS(block, () => {
			processor.processInlineJS(block, callbackExternal);
		});
	};

	showWait(node: HTMLElement): HTMLElement
	{
		const waiterNode = node.bxmsg = document.body.appendChild(Dom.create('DIV', {
			props: {
				id: `wait_${node.id}`,
				className: 'feed-add-post-loader-cont'
			},
			html: '<svg class="feed-add-post-loader" viewBox="25 25 50 50"><circle class="feed-add-post-loader-path" cx="50" cy="50" r="20" fill="none" stroke-miterlimit="10"></circle><circle class="feed-add-post-loader-inner-path" cx="50" cy="50" r="20" fill="none" stroke-miterlimit="10"></circle></svg>'
		}));

		setTimeout(() => {
			this.adjustWait(node);
		}, 10);
		this.lastWait.push(waiterNode);

		return waiterNode;
	};

	showWaitTab(): void
	{
		if (
			!BX('feed-add-post-more-icon')
			|| !BX('feed-add-post-more-icon-waiter')
		)
		{
			return;
		}

		BX('feed-add-post-more-icon').style.display = 'none';
		BX('feed-add-post-more-icon-waiter').style.display = 'block';
	};

	closeWait(): void
	{
		const waiterNode = this.containerMicro.bxmsg;

		if (waiterNode && waiterNode.parentNode)
		{
			for (let i=0, len=this.lastWait.length; i<len; i++)
			{
				if (waiterNode === this.lastWait[i])
				{
					this.lastWait = BX.util.deleteFromArray(this.lastWait, i);
					break;
				}
			}

			waiterNode.parentNode.removeChild(waiterNode);
			if (this.containerMicro)
			{
				this.containerMicro.bxmsg = null;
			}

			Dom.clean(waiterNode);
			Dom.remove(waiterNode);
		}

		if (
			BX('feed-add-post-more-icon')
			&& BX('feed-add-post-more-icon-waiter')
			&& BX('feed-add-post-more-icon').style.display === 'none'
		)
		{
			BX('feed-add-post-more-icon').style.display = 'block';
			BX('feed-add-post-more-icon-waiter').style.display = 'none';
		}
	};

	adjustWait(node: HTMLElement): void
	{
		if (!node.bxmsg)
		{
			return;
		}

		const nodePosition = BX.pos(node);
		let topDelta = nodePosition.top + 15;

		if (topDelta < BX.GetDocElement().scrollTop)
		{
			topDelta = BX.GetDocElement().scrollTop + 5;
		}

		node.bxmsg.style.top = `${(topDelta + 5)}px`;

		if (node === BX.GetDocElement())
		{
			node.bxmsg.style.right = '5px';
		}
		else
		{
			node.bxmsg.style.left = `${(nodePosition.left + parseInt((nodePosition.width - node.bxmsg.offsetWidth) / 2))}px`;
		}
	};

	tasksTaskEvent(taskId: number): void
	{
		if (!Reflection.getClass('BX.UI.Notification.Center'))
		{
			return;
		}

		const taskLink = Loc.getMessage('PATH_TO_USER_TASKS_TASK').replace('#user_id#', Loc.getMessage('USER_ID')).replace('#task_id#', taskId).replace('#action#', 'view');

		window.top.BX.UI.Notification.Center.notify({
			content: Loc.getMessage('BLOG_POST_EDIT_T_CREATE_TASK_SUCCESS_TITLE'),
			actions: [{
				title: Loc.getMessage('BLOG_POST_EDIT_T_CREATE_TASK_BUTTON_TITLE'),
				events: {
					click: (event, balloon, action) => {
						balloon.close();
						window.top.BX.SidePanel.Instance.open(taskLink);
					}
				}
			}]
		});
	};
}