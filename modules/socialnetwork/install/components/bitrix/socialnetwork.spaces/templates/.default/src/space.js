import { ajax, AjaxError, AjaxResponse, Cache, Dom, Event, Type, Uri } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { BaseEvent } from 'main.core.events';
import { Disk } from './disk';

import { Frame, LoadInfo } from './frame';
import { Overlay } from './overlay';

type Params = {
	pageId: string,
	contentUrl: string,
	userId: number,
	groupId: number,
}

export class Space
{
	#cache = new Cache.MemoryCache();

	#frame: Frame;
	#disk: Disk;
	#overlays: Map<string, Overlay> = new Map();
	#popupIds: Set<string> = new Set();

	constructor(params: Params)
	{
		this.setParams(params);

		this.#initServices();
	}

	setParams(params: Params): void
	{
		this.#cache.set('params', params);
	}

	renderContentTo(container: HTMLElement): void
	{
		if (!Type.isDomNode(container))
		{
			throw new Error('BX.Socialnetwork.Spaces.Space: HTMLElement for space not found');
		}

		this.#frame.renderTo(container);
	}

	reloadPageContent(pageUrl?: string): void
	{
		const uri = new Uri(pageUrl);

		const pageType = this.#getPageType(uri);

		let viewMode = '';
		let viewSize = '';
		let fState = '';
		if (pageType === 'tasks')
		{
			fState = uri.getQueryParam('F_STATE');
			viewMode = uri.getQueryParam('tab') ?? '';
		}

		let isTrashMode = false;
		if (pageType === 'files')
		{
			if (uri.getPath().includes('trashcan'))
			{
				isTrashMode = true;
			}

			viewMode = uri.getQueryParam('viewMode') ?? '';
			viewSize = uri.getQueryParam('viewSize') ?? '';
		}

		ajax.runComponentAction(
			'bitrix:socialnetwork.spaces',
			'getPageView',
			{
				mode: 'class',
				data: {
					pageType: pageType,
					userId: this.#getParam('userId'),
					groupId: this.#getParam('groupId'),
					params: {
						isTrashMode,
						viewMode,
						viewSize,
					},
					F_STATE: fState,
				},
			},
		)
			.then((response: AjaxResponse) => {
				this.#frame.reload(response.data, pageUrl);
			})
			.catch((error: AjaxError) => {
				this.#consoleError('getPageView', error);
			})
		;
	}

	showOverlay(popupId: string, frameOverlay: HTMLElement): void
	{
		this.#blockScroll(popupId);

		const topOverlay = (
			this.#overlays.has(popupId)
				? this.#overlays.get(popupId)
				: new Overlay({
					popupId,
					workpiece: frameOverlay,
					containerWithoutOverlay: this.#frame.getFrameNode(),
				})
		);

		this.#overlays.set(popupId, topOverlay);

		topOverlay.append();
	}

	hideOverlay(popupId: string): void
	{
		if (this.#overlays.has(popupId))
		{
			this.#overlays.get(popupId).remove();
		}

		this.#unblockScroll(popupId);
	}

	#showOverlays(): void
	{
		this.#overlays.forEach((overlay: Overlay) => overlay.show());
	}

	#hideOverlays(): void
	{
		this.#overlays.forEach((overlay: Overlay) => overlay.hide());
	}

	#removeOverlays(): void
	{
		this.#overlays.forEach((overlay: Overlay) => overlay.remove());
	}

	#blockScroll(popupId: string)
	{
		this.#popupIds.add(popupId);

		Dom.addClass(
			this.#frame.getWindow().document.querySelector('.sn-spaces__wrapper'),
			'--scroll-disabled',
		);
	}

	#unblockScroll(popupId: string)
	{
		this.#popupIds.delete(popupId);

		if (this.#popupIds.size === 0)
		{
			Dom.removeClass(
				this.#frame.getWindow().document.querySelector('.sn-spaces__wrapper'),
				'--scroll-disabled',
			);
		}
	}

	#initServices(): void
	{
		this.#frame = new Frame({
			pageId: this.#getParam('pageId'),
			pageView: this.#getParam('pageView'),
			id: 'sn-spaces-iframe',
			src: this.#getParam('contentUrl'),
			className: 'sn-spaces-iframe',
		});

		this.#frame.subscribe('load', (baseEvent: BaseEvent) => {
			const info: LoadInfo = baseEvent.getData();

			const uri = new Uri(info.src);
			uri.removeQueryParam(['IFRAME']);

			this.#changeBrowserHistory(uri.toString());

			if (this.#getParam('pageId') === 'files')
			{
				this.#disk = new Disk({
					window: info.window,
				});
				this.#disk.subscribe('changePage', (innerBaseEvent: BaseEvent) => {
					this.#changeBrowserHistory(innerBaseEvent.getData());
				});
			}
		});

		this.#frame.subscribe('unload', () => {
			this.#removeOverlays();
		});

		EventEmitter.subscribe('SidePanel.Slider:onOpen', () => {
			this.#hideOverlays();
		});
		EventEmitter.subscribe('SidePanel.Slider:onClose', () => {
			this.#showOverlays();
		});

		new MutationObserver(() => {
			const theme = BX.Intranet.Bitrix24.ThemePicker.Singleton.getAppliedThemeId();
			const themeStyles = document.head.querySelectorAll(`link[data-theme-id="${theme}"`);
			// eslint-disable-next-line promise/catch-or-return
			Promise.all([...themeStyles].map((link) => new Promise((resolve) => {
				Event.bind(link, 'load', resolve);
			}))).then(() => this.#updateBaseTheme());
		}).observe(document.head, { childList: true, subtree: false });
	}

	#updateBaseTheme(): void
	{
		const currentTheme = BX.Intranet.Bitrix24.ThemePicker.Singleton.getAppliedThemeId();
		const baseTheme = currentTheme.match(/(.*):/)[1];
		const document = this.#frame.getFrameNode().contentDocument;
		if (!document.body)
		{
			return;
		}

		document.body.className = document.body.className.replace(/bitrix24-\S*-theme/, '');
		Dom.addClass(document.body, `bitrix24-${baseTheme}-theme`);
	}

	#getParam(param: string): any
	{
		return this.#cache.get('params')[param];
	}

	#changeBrowserHistory(url: string): void
	{
		window.history.replaceState({}, '', url);
	}

	#getPageType(uri: Uri): string
	{
		if (uri.getPath().includes('general'))
		{
			return 'discussions';
		}

		if (uri.getPath().includes('tasks'))
		{
			return 'tasks';
		}

		if (uri.getPath().includes('calendar'))
		{
			return 'calendar';
		}

		if (uri.getPath().includes('disk'))
		{
			return 'files';
		}

		return 'discussions';
	}

	#consoleError(action: string, error: AjaxError)
	{
		// eslint-disable-next-line no-console
		console.error(`Spaces: ${action} error`, error);
	}
}
