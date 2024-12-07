import { Dom, Tag, Type } from 'main.core';
import 'ui.icon-set.main';

import './style.css';

type CountdownOptions = {
	seconds: number;
	showIcon: boolean;
	iconClass: string;
	showMinutes: boolean;
	cssClass: string;
	interval: number;
	node: HTMLElement;
	needStartImmediately: boolean;
	hideAfterEnd: boolean;
	onTimerEnd: Function;
	onTimerUpdate: Function;
}

export class Countdown
{
	seconds: number;
	showIcon: boolean;
	iconClass: ?string;
	showMinutes: boolean;
	cssClass: ?string;
	interval: number;
	node: HTMLElement;
	needStartImmediately: boolean;
	hideAfterEnd: boolean;
	onTimerEnd: ?function;
	onTimerUpdate: ?function;
	timerElement: HTMLElement;
	intervalId: number;

	constructor(props: CountdownOptions)
	{
		this.seconds = props.seconds ?? 0;
		this.showIcon = props.showIcon ?? true;
		this.iconClass = props.iconClass ?? 'ui-icon-set --clock-2';
		this.showMinutes = props.showMinutes ?? true;
		this.cssClass = props.cssClass ?? null;
		this.interval = props.interval ?? 1000;
		this.node = props.node ?? null;
		this.needStartImmediately = props.needStartImmediately ?? true;
		this.hideAfterEnd = props.hideAfterEnd ?? false;

		this.onTimerEnd = null;
		if (Type.isFunction(props.onTimerEnd))
		{
			this.onTimerEnd = props.onTimerEnd;
		}

		this.onTimerUpdate = null;
		if (Type.isFunction(props.onTimerUpdate))
		{
			this.onTimerUpdate = props.onTimerUpdate;
		}

		this.timerElement = null;
		this.intervalId = null;

		this.init();
	}

	init(): HTMLElement
	{
		this.timerElement = Tag.render`
			<div class="ui-countdown ${this.cssClass}">
				${this.showIcon ? Tag.render`<div class="ui-countdown__icon ${this.iconClass}"></div>` : ''}
				<span class="ui-countdown__time">${this.formatTime(this.seconds)}</span>
			</div>
		`;

		if (this.needStartImmediately)
		{
			this.start();
		}
		Dom.clean(this.node);
		Dom.append(this.timerElement, this.node);

		return this.node;
	}

	setSeconds(seconds: number)
	{
		this.seconds = seconds;
	}

	formatTime(seconds): string
	{
		if (this.showMinutes)
		{
			const minutes = Math.floor(seconds / 60);
			const remainingSeconds = seconds % 60;

			return `${minutes < 10 ? '0' : ''}${minutes}:${remainingSeconds < 10 ? '0' : ''}${remainingSeconds}`;
		}

		return seconds;
	}

	start()
	{
		this.lastTimestamp = Date.now();
		this.update();
	}

	update()
	{
		const now = Date.now();
		const elapsed = now - this.lastTimestamp;

		if (elapsed >= this.interval)
		{
			this.seconds -= Math.floor(elapsed / this.interval);
			this.lastTimestamp = now - (elapsed % this.interval);

			if (this.seconds <= 0)
			{
				this.seconds = 0;
			}
			const timeElement = this.timerElement.querySelector('.ui-countdown__time');
			const formattedTime = this.formatTime(this.seconds);
			timeElement.textContent = formattedTime;
			if (Type.isFunction(this.onTimerUpdate))
			{
				this.onTimerUpdate({
					seconds: this.seconds,
					formatted: formattedTime,
				});
			}

			if (this.seconds <= 0)
			{
				this.stop();

				return;
			}
		}

		this.intervalId = requestAnimationFrame(this.update.bind(this));
	}

	stop(): void
	{
		if (Type.isFunction(this.onTimerEnd))
		{
			this.onTimerEnd();
		}

		if (this.hideAfterEnd)
		{
			Dom.clean(this.node);
		}
		cancelAnimationFrame(this.intervalId);
	}

	getElement(): HTMLElement
	{
		return this.timerElement;
	}
}
