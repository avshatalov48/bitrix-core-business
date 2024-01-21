import { Type, Reflection, Event, Runtime } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { OrderedArray } from 'main.core.collections';
import { ZIndexManager } from 'main.core.z-index-manager';

import LaunchItem from './launch-item';
import type { LaunchItemCallback, LaunchItemOptions } from './launch-item-options';

export const LauncherState = {
	IDLE: 'idle',
	WAITING_READY: 'waiting_ready',
	READY: 'ready',
};

export default class Launcher
{
	#enabled: boolean = true;
	#queue: OrderedArray<LaunchItem> = null;
	#currentItem: LaunchItem | null = null;
	#state: LauncherState = LauncherState.IDLE;
	#documentReady: boolean = false;
	#launchCount: number = 0;
	#startDebounced: Function = null;

	constructor()
	{
		this.#queue = new OrderedArray((itemA: LaunchItem, itemB: LaunchItem) => {
			const result = itemB.getPriority() - itemA.getPriority();

			return result === 0 ? -1 : result;
		});

		this.#startDebounced = Runtime.debounce(this.#start, 1000, this);
	}

	static canShowOnTop(): boolean
	{
		const popupManager = Reflection.getClass('BX.Main.PopupManager');
		if (popupManager)
		{
			const popups = popupManager.getPopups();
			for (const popup of popups)
			{
				if (!popup.isShown())
				{
					continue;
				}

				if (
					popup.getId().startsWith('timeman_weekly_report_popup_')
					|| popup.getId().startsWith('timeman_daily_report_popup_')
					|| BX.Dom.hasClass(popup.getPopupContainer(), 'b24-whatsnew__popup')
				)
				{
					return false;
				}
			}
		}

		const viewer = Reflection.getClass('BX.UI.Viewer.Instance');
		if (viewer && viewer.isOpen())
		{
			return false;
		}

		const sidePanel = Reflection.getClass('BX.SidePanel.Instance');
		if (sidePanel && sidePanel.getOpenSlidersCount() > 0)
		{
			return false;
		}

		const stack = ZIndexManager.getStack(document.body);
		const components = stack === null ? [] : stack.getComponents();
		for (const component of components)
		{
			if (component.getOverlay() !== null && component.getOverlay().offsetWidth > 0)
			{
				return false;
			}
		}

		return true;
	}

	register(callback: LaunchItemCallback, options: LaunchItemOptions = {})
	{
		const launchItem = new LaunchItem({
			callback,
			...options,
		});

		this.#queue.add(launchItem);

		this.#startDebounced();
	}

	unregister(id: string)
	{
		for (const launchItem of this.#queue)
		{
			if (launchItem.getId() === id)
			{
				this.#queue.delete(launchItem);
				break;
			}
		}

		if (this.#currentItem !== null && this.#currentItem.getId() === id)
		{
			this.#tryDequeue();
		}
	}

	isEnabled(): boolean
	{
		return this.#enabled;
	}

	enable(): void
	{
		this.#enabled = true;
		this.#startDebounced();
	}

	disable(): void
	{
		this.#enabled = false;
		this.#state = LauncherState.IDLE;
	}

	#start(): void
	{
		if (!this.isEnabled() || this.#state !== LauncherState.IDLE)
		{
			return;
		}

		const onReady = () => {
			this.#documentReady = true;
			this.#state = LauncherState.READY;

			setTimeout(() => {
				this.#tryDequeue();
			}, 1000);
		};

		if (this.#documentReady)
		{
			onReady();
		}
		else
		{
			this.#state = LauncherState.WAITING_READY;
			if (Type.isUndefined(window.frameCacheVars))
			{
				Event.ready(onReady);
			}
			else
			{
				const compositeReady = (
					BX?.frameCache?.frameDataInserted === true || !Type.isUndefined(window.frameRequestFail)
				);

				if (compositeReady)
				{
					onReady();
				}
				else
				{
					EventEmitter.subscribe('onFrameDataProcessed', onReady);
					EventEmitter.subscribe('onFrameDataRequestFail', onReady);
				}
			}
		}
	}

	#tryDequeue(): void
	{
		this.#currentItem = this.#queue.getFirst();
		if (this.#currentItem === null)
		{
			return;
		}

		this.#queue.delete(this.#currentItem);

		if (!this.#currentItem.canLaunchAfterOthers() && this.#launchCount > 0)
		{
			this.#tryDequeue();
		}
		else if (this.constructor.canShowOnTop() || this.#currentItem.canShowOnTop())
		{
			setTimeout(() => {
				if (this.constructor.canShowOnTop() || this.#currentItem.canShowOnTop())
				{
					this.#launchCount++;
					this.#currentItem.launch(() => {
						this.#tryDequeue();
					});
				}
				else
				{
					this.#tryDequeue();
				}
			}, this.#currentItem.getDelay());
		}
		else
		{
			this.#tryDequeue();
		}
	}
}
