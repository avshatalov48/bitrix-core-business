import { Event, ZIndexManager, Runtime, Extension, Loc, Type } from 'main.core';
import { EventEmitter } from 'main.core.events';

import { Core } from 'im.v2.application.core';
import { EventType, Layout } from 'im.v2.const';
import { Logger } from 'im.v2.lib.logger';
import { Launch } from 'im.v2.application.launch';
import { CallManager } from 'im.v2.lib.call';
import { Utils } from 'im.v2.lib.utils';
import { DesktopManager } from 'im.v2.lib.desktop';

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

	openChat(dialogId: string | number = '', text: string = ''): Promise
	{
		let preparedDialogId = dialogId.toString();
		if (Utils.dialog.isLinesExternalId(preparedDialogId))
		{
			return this.openLines();
		}

		if (Type.isNumber(preparedDialogId))
		{
			preparedDialogId = preparedDialogId.toString();
		}

		return this.openSlider()
			.then(() => {
				return this.store.dispatch('application/setLayout', {
					layoutName: Layout.chat.name,
					entityId: preparedDialogId,
				});
			})
			.then(() => {
				EventEmitter.emit(EventType.layout.onOpenChat, { dialogId: preparedDialogId });
			});
	}

	openLines(): Promise
	{
		return new Promise((resolve, reject) => {
			BX.UI.Notification.Center.notify({
				content: Loc.getMessage('IM_LIB_SLIDER_LINES_NOT_IMPLEMENTED_2'),
				position: 'top-right',
				autoHideDelay: 10000,
			});
			reject(new Error('Messenger: lines are not implemented yet'));
		});
	}

	openNotifications(): Promise
	{
		return this.openSlider().then(() => {
			return this.store.dispatch('application/setLayout', {
				layoutName: Layout.notification.name
			});
		}).then(() => {
			EventEmitter.emit(EventType.layout.onOpenNotifications);
		});
	}

	openRecentSearch(): Promise
	{
		return this.openSlider().then(() => {
			this.store.dispatch('application/setLayout', {
				layoutName: Layout.chat.name,
			});
		}).then(() => {
			EventEmitter.emit(EventType.recent.openSearch);
		});
	}

	openSettings(selected: ?string = '', section: ?string = ''): Promise
	{
		Logger.warn('Slider: onOpenSettings', selected, section);

		return new Promise((resolve, reject) => {
			BX.UI.Notification.Center.notify({
				content: Loc.getMessage('IM_LIB_SLIDER_SETTINGS_NOT_IMPLEMENTED'),
				position: 'top-right',
				autoHideDelay: 10000,
			});
			reject(new Error('Messenger: settings are not implemented yet'));
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
		if (DesktopManager.isDesktop())
		{
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
			this.store.dispatch('application/setLayout', {layoutName: Layout.chat.name, entityId: ''});

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

		this.openChat();
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
}
