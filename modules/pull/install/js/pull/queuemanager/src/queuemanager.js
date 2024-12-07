import { Event, Loc, Text, Type } from 'main.core';
import { BaseEvent, EventEmitter } from 'main.core.events';
import { UI } from 'ui.notification';
import Queue from './queue';
import { Options, PullData } from './queuemanagertype';

export default class QueueManager
{
	#options: Options;
	#queue: Queue;
	#notifier: BX.UI.Notification.Balloon;
	#openedSlidersCount: Number;

	static eventIds: Set<string> = new Set();

	static registerRandomEventId(prefix: string = null): string
	{
		let eventId = Text.getRandom(12);
		if (Type.isStringFilled(prefix))
		{
			eventId = `${prefix}-${eventId}`;
		}

		this.registerEventId(eventId);

		return eventId;
	}

	static registerEventId(eventId: string): void
	{
		this.eventIds.add(eventId);
	}

	constructor(options: Options)
	{
		this.#options = options;

		const { config, callbacks } = options;

		this.#queue = new Queue({
			loadItemsDelay: config?.loadItemsDelay,
			maxPendingItems: config?.maxPendingItems,
			callbacks: {
				onBeforeExecute: callbacks.onBeforeQueueExecute,
				onExecute: callbacks.onQueueExecute,
			},
		});
		this.#openedSlidersCount = 0;

		this.initEventEmitter();

		const { moduleId, userId } = options;
		if (Type.isStringFilled(moduleId) && userId > 0)
		{
			Event.ready(() => this.init());
		}
	}

	initEventEmitter(): void
	{
		this.eventEmitter = new EventEmitter();
		this.eventEmitter.setEventNamespace('BX.Pull.QueueManager');
	}

	init(): void
	{
		if (!BX.PULL)
		{
			console.error('BX.PULL is not initialized');

			return;
		}

		this.subscribe();
		this.bindEvents();
	}

	subscribe(): void
	{
		const { moduleId, pullTag } = this.#options;

		BX.PULL.subscribe({
			moduleId,
			callback: (data) => this.onPullSubscribeCallback(data),
		});

		if (Type.isStringFilled(pullTag))
		{
			BX.PULL.extendWatch(pullTag);
		}
	}

	bindEvents(): void
	{
		if (Type.isPlainObject(this.#options.events))
		{
			for (const [eventName, callback] of Object.entries(this.#options.events))
			{
				if (Type.isFunction(callback))
				{
					this.eventEmitter.subscribe(eventName, (event) => callback(event));
				}
			}
		}

		Event.bind(document, 'visibilitychange', () => this.onDocumentVisibilityChange());

		EventEmitter.subscribe('SidePanel.Slider:onOpen', () => {
			this.#openedSlidersCount++;
			this.#queue.freeze();
		});

		EventEmitter.subscribe('SidePanel.Slider:onClose', () => {
			this.#openedSlidersCount--;
			if (this.#openedSlidersCount <= 0)
			{
				this.#openedSlidersCount = 0;
				this.#queue.unfreeze();
				this.onTabActivated();
			}
		});
	}

	onDocumentVisibilityChange(): void
	{
		if (!document.hidden)
		{
			this.onTabActivated();
		}
	}

	onPullSubscribeCallback(pullData: PullData): void
	{
		const { pullTag } = this.#options;

		if (Type.isStringFilled(pullTag) && pullData.command !== pullTag)
		{
			return;
		}

		const event = new BaseEvent({
			data: {
				pullData,
				queueItems: this.#queue.getAllAsArray(),
				options: this.#options,
				promises: [],
			},
		});
		this.eventEmitter.emit('onBeforePull', event);
		if (event.isDefaultPrevented())
		{
			return;
		}

		const { params } = pullData;

		if (!Type.isStringFilled(params.eventName))
		{
			return;
		}

		if (QueueManager.eventIds.has(params.eventId))
		{
			return;
		}

		if (this.#queue.isOverflow())
		{
			return;
		}

		this.eventEmitter.emit('onPull', event);
		if (event.isDefaultPrevented())
		{
			return;
		}

		void Promise
			.all(event.data.promises)
			.then((values) => {
				if (!Type.isArrayFilled(values))
				{
					return;
				}

				values.forEach((item) => {
					const { data } = item;
					this.#queue.push(`${data.id}_${params.eventName}`, data);
				});

				this.#queue.loadItem(false, params.ignoreDelay || false);
			})
		;
	}

	showOutdatedDataDialog(): void
	{
		if (this.#hasManyOpenSliders())
		{
			return;
		}

		const sliderInstance = this.#getSliderInstance();
		if (sliderInstance)
		{
			EventEmitter.subscribe(
				sliderInstance,
				'SidePanel.Slider:onClose',
				this.#createAndShowNotify.bind(this),
			);
		}
		else
		{
			this.#createAndShowNotify();
		}
	}

	#hasManyOpenSliders(): boolean
	{
		return (top.BX && top.BX.SidePanel && top.BX.SidePanel.Instance.getOpenSlidersCount() > 1);
	}

	#getSliderInstance(): BX.SidePanel.Slider | null
	{
		if (top.BX && top.BX.SidePanel)
		{
			const slider = top.BX.SidePanel.Instance.getTopSlider();
			if (slider && slider.isOpen())
			{
				return slider;
			}
		}

		return null;
	}

	#createAndShowNotify(): void
	{
		const showOutdatedDataDialog = this.#options.config?.showOutdatedDataDialog;
		const { onReload } = this.#options.callbacks;
		if (
			(Type.isBoolean(showOutdatedDataDialog) && showOutdatedDataDialog === false)
			|| !Type.isFunction(onReload)
		)
		{
			return;
		}

		if (this.#notifier)
		{
			if (
				this.#notifier.getState() === BX.UI.Notification.State.OPENING
				|| this.#notifier.getState() === BX.UI.Notification.State.OPEN
			)
			{
				return;
			}

			this.#notifier.show();

			return;
		}

		this.#notifier = UI.Notification.Center.notify({
			content: Loc.getMessage('PULL_QUEUEMANAGER_NOTIFY_OUTDATED_DATA'),
			closeButton: false,
			autoHide: false,
			actions: [{
				title: Loc.getMessage('PULL_QUEUEMANAGER_RELOAD'),
				events: {
					click: (event, balloon) => {
						balloon.close();
						onReload();
						this.#queue.clear();
					},
				},
			}],
		});
	}

	onTabActivated(): void
	{
		if (this.#queue.isOverflow())
		{
			this.showOutdatedDataDialog();

			return;
		}

		if (!this.#queue.isEmpty())
		{
			this.#queue.loadItem();
		}
	}

	hasInQueue(id: number): boolean
	{
		return this.#queue.has(id);
	}

	deleteFromQueue(id: number): void
	{
		this.#queue.delete(id);
	}

	getLoadItemsDelay(): number
	{
		return this.#queue.getLoadItemsDelay();
	}
}
