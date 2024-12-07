import { Dom, Event, Tag } from 'main.core';

type Params = {
	popupId: string,
	workpiece: HTMLElement,
	containerWithoutOverlay: HTMLElement,
}

export class Overlay
{
	#popupId: string;
	#workpiece: HTMLElement;
	#containerWithoutOverlay: HTMLElement;

	#overlay: HTMLElement;
	#leftOverlay: HTMLElement;
	#topOverlay: HTMLElement;
	#rightOverlay: HTMLElement;

	constructor(params: Params)
	{
		this.#popupId = params.popupId;
		this.#workpiece = params.workpiece;
		this.#containerWithoutOverlay = params.containerWithoutOverlay;

		this.#createOverlay();

		Event.bind(window, 'resize', this.#resizeWindow.bind(this));
	}

	show()
	{
		Dom.style(this.#overlay, 'display', 'block');
	}

	hide()
	{
		Dom.style(this.#overlay, 'display', 'none');
	}

	append()
	{
		Dom.append(this.#overlay, document.body);
	}

	remove()
	{
		Dom.remove(this.#overlay);
	}

	#createOverlay()
	{
		const params = this.#getOverlayParams();

		this.#leftOverlay = this.#createPartOfOverlay(params.left.width, params.left.height);
		this.#topOverlay = this.#createPartOfOverlay(
			params.top.width,
			params.top.height,
			params.top.left,
		);
		this.#rightOverlay = this.#createPartOfOverlay(
			params.right.width,
			params.right.height,
			params.right.left,
		);

		this.#overlay = Tag.render`
			<div>
				${this.#leftOverlay}
				${this.#topOverlay}
				${this.#rightOverlay}
			</div>
		`;
	}

	#createPartOfOverlay(width: number, height: number, left: number = 0): HTMLElement
	{
		const overlay = this.#workpiece.cloneNode(true);

		this.#resizeOverlay(overlay, width, height, left);

		return overlay;
	}

	#resizeWindow()
	{
		const params = this.#getOverlayParams();

		this.#resizeOverlay(this.#leftOverlay, params.left.width, params.left.height);
		this.#resizeOverlay(
			this.#topOverlay,
			params.top.width,
			params.top.height,
			params.top.left,
		);
		this.#resizeOverlay(
			this.#rightOverlay,
			params.right.width,
			params.right.height,
			params.right.left,
		);
	}

	#getSizes(): Array
	{
		const scrollWidth = document.documentElement.scrollWidth;
		const scrollHeight = Math.max(
			document.body.scrollHeight,
			document.documentElement.scrollHeight,
			document.body.offsetHeight,
			document.documentElement.offsetHeight,
			document.body.clientHeight,
			document.documentElement.clientHeight,
		);

		return [scrollWidth, scrollHeight];
	}

	#getOverlayParams(): Object
	{
		const [scrollWidth, scrollHeight] = this.#getSizes();

		const rect = Dom.getPosition(this.#containerWithoutOverlay);

		return {
			left: {
				width: rect.left,
				height: scrollHeight,
				left: 0,
			},
			top: {
				width: rect.width,
				height: rect.top,
				left: rect.left,
			},
			right: {
				width: scrollWidth - rect.right,
				height: scrollHeight,
				left: rect.right,
			},
		};
	}

	#resizeOverlay(overlay: HTMLElement, width: number, height: number, left: number = 0)
	{
		Dom.style(overlay, 'width', `${width}px`);
		Dom.style(overlay, 'height', `${height}px`);

		if (left)
		{
			Dom.style(overlay, 'left', `${left}px`);
		}
	}
}
