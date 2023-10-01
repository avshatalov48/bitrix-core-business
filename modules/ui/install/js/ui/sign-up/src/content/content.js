import {Cache} from 'main.core';
import {EventEmitter} from 'main.core.events';
import {CanvasWrapper} from '../canvas-wrapper/canvas-wrapper';

type ContentOption = {
	color?: string,
	events?: {[p: string]: Function}
};

export class Content extends EventEmitter
{
	cache = new Cache.MemoryCache();

	constructor(options: ContentOption = {})
	{
		super();
		this.setEventNamespace('BX.UI.SignUp.Content');
		this.subscribeFromOptions(options.events);
		this.setOptions(options);
	}

	getColor(): string | null
	{
		return this.getOptions().color ?? null;
	}

	setOptions(options)
	{
		this.cache.set('options', {...options});
	}

	getOptions()
	{
		return this.cache.get('options', {});
	}

	getLayout(): HTMLDivElement
	{
		throw new Error('Must be implemented in a child class');
	}

	getCanvas(): CanvasWrapper
	{
		throw new Error('Must be implemented in a child class');
	}
}