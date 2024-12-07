import { Dom, Tag } from 'main.core';
import { MemoryCache } from 'main.core.cache';
import type { BaseCache } from 'main.core.cache';
import { EventEmitter } from 'main.core.events';
import type { DatePicker } from './date-picker';

export class BasePicker extends EventEmitter
{
	#datePicker: DatePicker = null;
	#refs: BaseCache<HTMLElement> = new MemoryCache();
	#rendered: boolean = false;

	constructor(datePicker)
	{
		super();
		this.setEventNamespace('BX.UI.DatePicker.BasePicker');

		this.#datePicker = datePicker;
	}

	getContainer(): HTMLElement
	{
		throw new Error('You must implement getContainer method');
	}

	getHeaderContainer(...children: HTMLElement): HTMLElement
	{
		return this.#refs.remember('header', () => {
			return Tag.render`<div class="ui-date-picker-header">${children}</div>`;
		});
	}

	getContentContainer(...children: HTMLElement): HTMLElement
	{
		return this.#refs.remember('content', () => {
			return Tag.render`<div class="ui-date-picker-content">${children}</div>`;
		});
	}

	getPrevBtn(): HTMLButtonElement
	{
		return this.#refs.remember('prev-button', () => {
			return Tag.render`
				<button type="button" class="ui-date-picker-button --left-arrow" onclick="${this.handlePrevBtnClick.bind(this)}">
					<span class="ui-icon-set --chevron-left" style="--ui-icon-set__icon-size: 20px"></span>
				</button>
			`;
		});
	}

	getNextBtn(): HTMLButtonElement
	{
		return this.#refs.remember('next-button', () => {
			return Tag.render`
				<button type="button" class="ui-date-picker-button --right-arrow" onclick="${this.handleNextBtnClick.bind(this)}">
					<span class="ui-icon-set --chevron-right" style="--ui-icon-set__icon-size: 20px"></span>
				</button>
			`;
		});
	}

	handlePrevBtnClick()
	{
		this.emit('onPrevBtnClick');
	}

	handleNextBtnClick()
	{
		this.emit('onNextBtnClick');
	}

	render(): void
	{
		throw new Error('You must implement render method');
	}

	onShow(): void
	{
		// you can override this method
	}

	onHide(): void
	{
		// you can override this method
	}

	getDatePicker(): DatePicker
	{
		return this.#datePicker;
	}

	isRendered(): boolean
	{
		return this.#rendered;
	}

	renderTo(container: HTMLElement): void
	{
		Dom.append(this.getContainer(), container);

		this.#rendered = true;
	}
}
