import { Type } from 'main.core';

export default class TouchDragListener
{
	constructor({
		element,
		touchStartCallback,
		touchEndCallback,
		touchMoveCallback
	})
	{
		this.element = Type.isDomNode(element) ? element : null;
		this.touchStartCallback = touchStartCallback;
		this.touchEndCallback = touchEndCallback;
		this.touchMoveCallback = touchMoveCallback;

		this.active = false;
		this.currentY = null;
		this.initialY = null;
		this.yOffset = 0;

		this.#bindEvents();
	}

	#bindEvents()
	{
		if (this.element)
		{
			this.element.addEventListener('touchstart', this.#dragStart.bind(this));
			this.element.addEventListener('touchend', this.#dragEnd.bind(this));
			this.element.addEventListener('touchmove', this.#dragMove.bind(this));
		}
	}

	#dragStart(ev)
	{
		this.active = true;
		this.element.classList.add('--ondrag');

		if (ev.type === 'touchstart')
		{
			this.initialY = ev.touches[0].clientY - this.yOffset;
		}
		else
		{
			this.initialY = ev.clientY - this.yOffset;
		}

		if (!this.touchStartCallback)
		{
			return;
		}

		this.touchStartCallback({
			element: this.element,
			active: this.active,
			currentY: this.currentY,
			initialY: this.initialY,
			yOffset: this.offSetY
		});
	}

	#dragEnd(ev)
	{
		this.active = true;
		this.element.classList.remove('--ondrag');

		this.yOffset = 0;

		this.initialY = this.currentY;

		if (!this.touchEndCallback) return;

		this.touchEndCallback({
			element: this.element,
			active: this.active,
			currentY: this.currentY,
			initialY: this.initialY,
			yOffset: this.offSetY
		});
	}
	
	#dragMove(ev)
	{
		if (!this.active)
		{
			return;
		}

		ev.preventDefault();

		if (ev.type === 'touchmove')
		{
			this.currentY = ev.touches[0].clientY - this.initialY;
		}
		else
		{
			this.currentY = ev.clientY - this.initialY;
		}

		this.yOffset = this.currentX;

		if (!this.touchMoveCallback)
		{
			return;
		}

		this.touchMoveCallback({
			element: this.element,
			active: this.active,
			currentY: this.currentY,
			initialY: this.initialY,
			yOffset: this.offSetY
		});
	}
}