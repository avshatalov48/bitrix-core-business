import { Dom, Type, Text } from 'main.core';
import { EventEmitter } from 'main.core.events';
import type { CollapserParams } from './types/collapser';

export class Collapser
{
	#id: string;
	#isOpen: boolean;
	#outerContainer: HTMLElement;
	#innerContainer: HTMLElement;
	#duration: number;
	#calcProgress: function;

	constructor(params: CollapserParams)
	{
		this.#id = Type.isNil(params.id) ? 'collapser_' + Text.getRandom(8) : params.id;
		this.#isOpen = Type.isBoolean(params.isOpen) ? params.isOpen : true;
		this.#outerContainer = params.outerContainer;
		this.#innerContainer = params.innerContainer;
		this.#duration = Type.isNumber(params.duration) ? params.duration : 500;
		this.#calcProgress = Type.isFunction(params.calcProgress) ? params.calcProgress : this.#linear;
		this.init(params);
	}

	init(params: CollapserParams): void
	{
		Dom.style(this.getChildrenContainer(), 'overflow', 'hidden');

		if (!this.#isOpen)
		{
			Dom.style(this.getChildrenContainer(), 'height', '0px');
		}

		if (Type.isElementNode(params.buttons))
		{
			params.buttons = [params.buttons];
		}

		if (Type.isArray(params.buttons) || params.buttons instanceof NodeList)
		{
			for (const index in params.buttons)
			{
				const button = params.buttons[index];
				if (Type.isElementNode(button))
				{
					button.addEventListener('click', this.toggle.bind(this))
				}
			}
		}
		else
		{
			this.getOuterContainer().addEventListener('click', this.toggle.bind(this))
		}
	}

	expand(): void
	{
		if (this.isOpen())
		{
			return;
		}

		this.showAnimate(true);
	}

	collapse(): void
	{
		if (!this.isOpen())
		{
			return;
		}

		this.showAnimate(false);
	}

	showAnimate(isOpen: boolean): void
	{
		let start = performance.now();
		const draw = this.makeDraw(this.isOpen());

		const animate = (time) => {
			let partTime = (time - start) / this.#duration;

			if (partTime > 1)
			{
				partTime = 1;
			}

			let process = this.#calcProgress(partTime);
			draw(process);

			if (partTime < 1)
			{
				requestAnimationFrame(animate);
			}
			else
			{
				this.setOpen(isOpen);
				EventEmitter.emit(
					EventEmitter.GLOBAL_TARGET,
					'BX.UI.Collapse:onToggle', {
						isOpen: this.isOpen(),
						source: this,
					},
				);
				if (isOpen)
				{
					Dom.style(this.getChildrenContainer(), 'height', null);
				}
			}
		};
		requestAnimationFrame(animate);
	}

	makeDraw(isOpen: boolean): function
	{
		if (isOpen)
		{
			return (partTime) => {
				const process = this.getChildrenContainer().offsetHeight - this.getChildrenContainer().offsetHeight * partTime;
				Dom.style(this.getChildrenContainer(), 'height', process + 'px');
			}
		}
		else
		{
			return (partTime) => {
				const process = this.getChildrenContainer().scrollHeight * partTime;
				Dom.style(this.getChildrenContainer(), 'height', process + 'px');
			}
		}
	}

	#linear(partTime): number
	{
		return partTime;
	}

	getChildrenContainer(): HTMLElement
	{
		return this.#innerContainer;
	}

	getOuterContainer(): HTMLElement
	{
		return this.#outerContainer;
	}

	setOpen(state: boolean): void
	{
		this.#isOpen = state;
	}

	isOpen(): boolean
	{
		return this.#isOpen;
	}

	toggle(): void
	{
		this.isOpen() ? this.collapse() : this.expand();
	}

	getId(): string
	{
		return this.#id;
	}
}