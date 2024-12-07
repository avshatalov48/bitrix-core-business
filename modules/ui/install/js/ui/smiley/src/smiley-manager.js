import { Extension } from 'main.core';
import { Smiley } from './smiley';

export class SmileyManager
{
	static #smileys: Map<string, Smiley> = new Map();
	static {
		const settings = Extension.getSettings('ui.smiley');
		const smileys = settings.get('smileys', []);
		for (const smiley of smileys)
		{
			this.#smileys.set(smiley.typing, new Smiley(smiley));
		}
	}

	static getSize(): number
	{
		return this.#smileys.size;
	}

	static get(typing: string): Smiley | null
	{
		return this.#smileys.get(typing) || null;
	}

	static getAll(): Smiley[]
	{
		return [...this.#smileys.values()];
	}
}
