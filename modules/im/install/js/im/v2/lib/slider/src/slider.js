import { Event, ZIndexManager, Runtime } from 'main.core';
import { SidePanel, type SliderManager } from 'main.sidepanel';
import { EventEmitter } from 'main.core.events';

import { Core } from 'im.v2.application.core';
import { EventType, Layout } from 'im.v2.const';
import { Logger } from 'im.v2.lib.logger';
import { Launch } from 'im.v2.application.launch';
import { DesktopManager } from 'im.v2.lib.desktop';
import { LayoutManager } from 'im.v2.lib.layout';
import { CallManager } from 'im.v2.lib.call';
import { showCloseWithActiveCallConfirm } from 'im.v2.lib.confirm';

import 'ui.notification';
import 'im.v2.lib.opener';

import type { Store } from 'ui.vue3.vuex';

const SLIDER_PREFIX = 'im:slider';
const BASE_STACK_INDEX = 1200;
const SLIDER_CONTAINER_CLASS = 'bx-im-messenger__slider';

export class MessengerSlider
{
	static instance = null;

	instances: Object = {};
	sidePanelManager: SliderManager = SidePanel.Instance;
	store: Store;

	static init()
	{
		if (this.instance)
		{
			return;
		}

		this.instance = new this();
	}

	static getInstance(): MessengerSlider
	{
		this.init();

		return this.instance;
	}

	constructor()
	{
		Logger.warn('Slider: class created');
		this.bindEvents();
		this.store = Core.getStore();
	}

	bindEvents(): boolean
	{
		EventEmitter.subscribe('SidePanel.Slider:onCloseByEsc', this.onCloseByEsc.bind(this));
		EventEmitter.subscribe('SidePanel.Slider:onClose', this.onClose.bind(this));
		EventEmitter.subscribe('SidePanel.Slider:onDestroy', this.onDestroy.bind(this));

		Event.ready(this.initZIndex.bind(this));

		return true;
	}

	openSlider(): Promise
	{
		if (this.isChatEmbeddedOnPage())
		{
			return Promise.resolve();
		}

		if (DesktopManager.isChatWindow())
		{
			this.sidePanelManager.closeAll(true);

			return Promise.resolve();
		}

		if (this.isOpened())
		{
			ZIndexManager.bringToFront(this.getCurrent().getOverlay());

			return Promise.resolve();
		}

		void this.launchMessengerApplication();

		return new Promise((resolve) => {
			if (this.isFocused())
			{
				resolve();

				return;
			}

			const nextId = this.getNextId();

			this.sidePanelManager.open(`${SLIDER_PREFIX}:${nextId}`, {
				data: { rightBoundary: 0 },
				cacheable: false,
				animationDuration: 100,
				hideControls: true,
				customLeftBoundary: 0,
				customRightBoundary: 0,
				loader: this.getLoaderPath(),
				contentCallback: () => {
					return `<div class="${SLIDER_CONTAINER_CLASS}"></div>`;
				},
				events: {
					onOpenComplete: async (event) => {
						event.slider.showLoader();
						await this.initMessengerComponent();
						event.slider.closeLoader();

						return resolve();
					},
				},
			});

			this.instances[nextId] = this.sidePanelManager.getSlider(`${SLIDER_PREFIX}:${nextId}`);
		});
	}

	launchMessengerApplication(): Promise
	{
		if (this.applicationPromise)
		{
			return this.applicationPromise;
		}

		this.applicationPromise = Runtime.loadExtension('im.v2.application.messenger').then(() => {
			return Launch('messenger');
		}).then((application) => {
			Logger.warn('Slider: Messenger application launched', application);

			return application;
		});

		return this.applicationPromise;
	}

	async initMessengerComponent(): Promise
	{
		const application = await this.applicationPromise;

		return application.initComponent(`.${SLIDER_CONTAINER_CLASS}`);
	}

	onDialogOpen(event)
	{
		Logger.warn('Slider: onDialogOpen', event.data.dialogId);
	}

	async onClose({ data: event })
	{
		[event] = event;
		const sliderId = event.getSlider().getUrl().toString();
		if (!sliderId.startsWith(SLIDER_PREFIX))
		{
			return;
		}

		if (!this.canClose())
		{
			event.denyAction();

			return;
		}

		const hasCall = CallManager.getInstance().hasCurrentCall();
		if (hasCall)
		{
			event.denyAction();

			const result = await showCloseWithActiveCallConfirm();
			if (result)
			{
				CallManager.getInstance().leaveCurrentCall();
				event.slider.close();
			}

			return;
		}

		// TODO: emit event to close all popups

		const id = this.getIdFromSliderId(sliderId);
		delete this.instances[id];

		EventEmitter.emit(EventType.slider.onClose);

		void LayoutManager.getInstance().setLayout({
			name: Layout.chat,
		});
	}

	onCloseByEsc({ data: event })
	{
		[event] = event;
		const sliderId = event.getSlider().getUrl().toString();
		if (!sliderId.startsWith(SLIDER_PREFIX))
		{
			return;
		}

		// we handle close by esc in the im.v2.lib.esc-manager
		event.denyAction();
	}

	onDestroy({ data: event })
	{
		[event] = event;
		const sliderId = event.getSlider().getUrl().toString();
		if (!sliderId.startsWith(SLIDER_PREFIX))
		{
			return;
		}

		const id = this.getIdFromSliderId(sliderId);
		delete this.instances[id];
	}

	initZIndex()
	{
		if (!ZIndexManager)
		{
			return;
		}

		const stack = ZIndexManager.getOrAddStack(document.body);
		stack.baseIndex = BASE_STACK_INDEX;
		stack.sort();
	}

	getZIndex(): number
	{
		return BASE_STACK_INDEX;
	}

	isOpened(): boolean
	{
		return Object.keys(this.instances).length > 0;
	}

	isFocused(): boolean
	{
		if (!this.isOpened())
		{
			return false;
		}

		const slider = this.sidePanelManager.getTopSlider();
		if (!slider)
		{
			return false;
		}

		return slider.getUrl().toString().startsWith(SLIDER_PREFIX);
	}

	canClose(): boolean
	{
		return true;
	}

	getCurrent(): Object
	{
		return this.instances[this.getCurrentId()];
	}

	getCurrentId(): number
	{
		return Object.keys(this.instances).length;
	}

	getNextId(): number
	{
		return this.getCurrentId() + 1;
	}

	getIdFromSliderId(sliderId: string): number
	{
		return Number.parseInt(sliderId.slice(SLIDER_PREFIX.length + 1), 10);
	}

	getLoaderPath(): string
	{
		return '/bitrix/js/im/v2/lib/slider/src/images/loader-chats-without-navigation.svg';
	}

	isChatEmbeddedOnPage(): boolean
	{
		return LayoutManager.getInstance().isQuickAccessHidden();
	}
}
