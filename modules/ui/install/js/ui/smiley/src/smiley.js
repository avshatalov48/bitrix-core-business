import { Type } from 'main.core';

export type SmileyOptions = {
	name: string;
	image: string;
	typing: string;
	width: number;
	height: number;
};

export class Smiley
{
	#name: string;
	#image: string;
	#typing: string;
	#width: number;
	#height: number;

	constructor(smileyOptions: SmileyOptions)
	{
		const options = Type.isPlainObject(smileyOptions) ? smileyOptions : {};

		this.setName(options.name);
		this.setImage(options.image);
		this.setTyping(options.typing);
		this.setWidth(options.width);
		this.setHeight(options.height);
	}

	getName(): string
	{
		return this.#name;
	}

	setName(value: string)
	{
		this.#name = value;
	}

	getImage(): string
	{
		return this.#image;
	}

	setImage(value: string)
	{
		this.#image = value;
	}

	getTyping(): string
	{
		return this.#typing;
	}

	setTyping(value: string)
	{
		this.#typing = value;
	}

	getWidth(): number
	{
		return this.#width;
	}

	setWidth(value: number)
	{
		this.#width = value;
	}

	getHeight(): number
	{
		return this.#height;
	}

	setHeight(value: number)
	{
		this.#height = value;
	}
}
