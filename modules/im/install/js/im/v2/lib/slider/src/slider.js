import { DesktopApi } from 'im.v2.lib.desktop-api';
import { Event, ZIndexManager, Runtime, Extension } from 'main.core';
import { EventEmitter } from 'main.core.events';

import { Core } from 'im.v2.application.core';
import { EventType, Layout, GetParameter } from 'im.v2.const';
import { Logger } from 'im.v2.lib.logger';
import { Launch } from 'im.v2.application.launch';
import { CallManager } from 'im.v2.lib.call';
import { PhoneManager } from 'im.v2.lib.phone';
import { Utils } from 'im.v2.lib.utils';
import { DesktopManager } from 'im.v2.lib.desktop';
import { LinesService } from 'im.v2.provider.service';

import 'ui.notification';

import type { Store } from 'ui.vue3.vuex';

const SLIDER_PREFIX = 'im:slider';
const BASE_STACK_INDEX = 1200;
const SLIDER_CONTAINER_CLASS = 'bx-im-messenger__slider';
const LOADER_CHATS_PATH = '/bitrix/js/im/v2/lib/slider/src/images/loader-chats.svg?v2';

export class MessengerSlider
{
	static instance = null;

	instances: Object = {};
	sidePanelManager: Object = BX.SidePanel.Instance;
	v2enabled: boolean = false;
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
		this.initSettings();
		this.bindEvents();
		this.store = Core.getStore();
	}

	async openChat(dialogId: string | number = ''): Promise
	{
		const preparedDialogId = dialogId.toString();
		if (Utils.dialog.isLinesExternalId(preparedDialogId))
		{
			return this.openLines(preparedDialogId);
		}

		await this.openSlider();

		await this.store.dispatch('application/setLayout', {
			layoutName: Layout.chat.name,
			entityId: preparedDialogId,
		});
		EventEmitter.emit(EventType.layout.onOpenChat, { dialogId: preparedDialogId });

		return Promise.resolve();
	}

	async openLines(dialogId: string = ''): Promise
	{
		let preparedDialogId = dialogId.toString();
		if (Utils.dialog.isLinesExternalId(preparedDialogId))
		{
			const linesService = new LinesService();
			preparedDialogId = await linesService.getDialogIdByUserCode(preparedDialogId);
		}

		await this.openSlider();

		return this.store.dispatch('application/setLayout', {
			layoutName: Layout.openlines.name,
			entityId: preparedDialogId,
		});
	}

	openHistory(dialogId: string | number = ''): Promise
	{
		if (!this.#checkHistoryDialogId(dialogId))
		{
			return Promise.reject();
		}

		const sliderLink = this.#prepareHistorySliderLink(dialogId);
		BX.SidePanel.Instance.open(sliderLink, {
			width: Utils.dialog.isLinesExternalId(dialogId) ? 700 : 1000,
			allowChangeHistory: false,
			allowChangeTitle: false,
			cacheable: false,
		});

		return Promise.resolve();
	}

	async openNotifications(): Promise
	{
		await this.openSlider();
		await this.store.dispatch('application/setLayout', {
			layoutName: Layout.notification.name,
		});

		EventEmitter.emit(EventType.layout.onOpenNotifications);

		return Promise.resolve();
	}

	async openRecentSearch(): Promise
	{
		await this.openSlider();
		await this.store.dispatch('application/setLayout', {
			layoutName: Layout.chat.name,
		});

		EventEmitter.emit(EventType.recent.openSearch);

		return Promise.resolve();
	}

	async openSettings(options: ?Object = {}): Promise
	{
		Logger.warn('Slider: openSettings', options);
		await this.openSlider();

		await this.store.dispatch('application/setLayout', {
			layoutName: Layout.settings.name,
		});

		return Promise.resolve();
	}

	openConference(code: string = ''): Promise
	{
		Logger.warn('Slider: openConference', code);

		if (!Utils.conference.isValidCode(code))
		{
			return new Promise((resolve, reject) => {
				reject();
			});
		}

		const url = Utils.conference.getUrlByCode(code);
		Utils.browser.openLink(url, Utils.conference.getWindowNameByCode(code));

		return new Promise((resolve, reject) => {
			resolve();
		});
	}

	startVideoCall(dialogId: string = '', withVideo: boolean = true): Promise
	{
		Logger.warn('Slider: onStartVideoCall', dialogId, withVideo);
		if (!Utils.dialog.isDialogId(dialogId))
		{
			Logger.error('Slider: onStartVideoCall - dialogId is not correct', dialogId);

			return false;
		}

		CallManager.getInstance().startCall(dialogId, withVideo);

		return Promise.resolve();
	}

	startPhoneCall(number: string, params: Object<any, string>): Promise
	{
		Logger.warn('Slider: startPhoneCall', number, params);
		PhoneManager.getInstance().startCall(number, params);

		return Promise.resolve();
	}

	startCallList(callListId: number, params: Object<string, any>): Promise
	{
		Logger.warn('Slider: startCallList', callListId, params);
		PhoneManager.getInstance().startCallList(callListId, params);

		return Promise.resolve();
	}

	openNewTab(path)
	{
		if (DesktopApi.getApiVersion() >= 75 && DesktopApi.isChatTab())
		{
			DesktopApi.createImTab(`${path}&${GetParameter.desktopChatTabMode}=Y`);
		}
		else
		{
			Utils.browser.openLink(path);
		}
	}

	bindEvents(): boolean
	{
		if (!this.v2enabled)
		{
			Logger.warn('Slider: v2 is not enabled');

			return false;
		}

		EventEmitter.subscribe('SidePanel.Slider:onCloseByEsc', this.onCloseByEsc.bind(this));
		EventEmitter.subscribe('SidePanel.Slider:onClose', this.onClose.bind(this));
		EventEmitter.subscribe('SidePanel.Slider:onDestroy', this.onDestroy.bind(this));

		Event.ready(this.initZIndex.bind(this));

		return true;
	}

	initSettings()
	{
		const settings = Extension.getSettings('im.v2.lib.slider');
		this.v2enabled = settings.get('v2enabled', false);
	}

	openSlider(): Promise
	{
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

		this.launchMessengerApplication();

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
				loader: LOADER_CHATS_PATH,
				contentCallback: () => {
					return `<div class="${SLIDER_CONTAINER_CLASS}"></div>`;
				},
				events: {
					onLoad: (event) => {
						event.slider.showLoader();
					},
					onOpenComplete: (event) => {
						this.initMessengerComponent().then(() => {
							event.slider.closeLoader();

							return resolve();
						});
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

	initMessengerComponent(): Promise
	{
		return this.applicationPromise.then((application) => {
			this.store.dispatch('application/setLayout', { layoutName: Layout.chat.name, entityId: '' });

			return application.initComponent(`.${SLIDER_CONTAINER_CLASS}`);
		});
	}

	onDialogOpen(event)
	{
		Logger.warn('Slider: onDialogOpen', event.data.dialogId);
	}

	onClose({ data: event })
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

		// TODO: emit event to close all popups

		const id = this.getIdFromSliderId(sliderId);
		delete this.instances[id];

		EventEmitter.emit(EventType.slider.onClose);

		this.store.dispatch('application/setLayout', {
			layoutName: Layout.chat.name,
			entityId: '',
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

		if (!this.canCloseByEsc())
		{
			event.denyAction();
		}
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

	canCloseByEsc(): boolean
	{
		return false;
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

	#checkHistoryDialogId(dialogId: string): boolean
	{
		return Utils.dialog.isDialogId(dialogId)
			|| Utils.dialog.isLinesHistoryId(dialogId)
			|| Utils.dialog.isLinesExternalId(dialogId);
	}

	#prepareHistorySliderLink(dialogId: string): string
	{
		const getParams = new URLSearchParams({
			[GetParameter.openHistory]: dialogId,
			[GetParameter.backgroundType]: 'light',
		});

		return `/desktop_app/history.php?${getParams.toString()}`;
	}
}
