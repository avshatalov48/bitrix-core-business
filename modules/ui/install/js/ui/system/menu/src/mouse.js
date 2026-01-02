import { Event } from 'main.core';

type MousePosition = {
	top: number,
	left: number,
};

class Mouse
{
	#delta: MousePosition = {
		top: 0,
		left: 0,
	};

	#position: MousePosition = {
		top: 0,
		left: 0,
	};

	constructor()
	{
		Event.bind(window, 'mousemove', this.#update);
	}

	getPosition(): MousePosition
	{
		return this.#position;
	}

	getDelta(): MousePosition
	{
		return this.#delta;
	}

	#update = (event: MouseEvent): void => {
		const position = {
			top: event.clientY + window.scrollY,
			left: event.clientX + window.scrollX,
		};

		this.#delta = {
			top: position.top - this.#position.top,
			left: position.left - this.#position.left,
		};

		this.#position = position;
	};
}

export const mouse = new Mouse();
