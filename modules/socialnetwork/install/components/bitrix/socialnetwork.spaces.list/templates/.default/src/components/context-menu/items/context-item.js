import { EventEmitter } from 'main.core.events';

type Options = {
	message: string,
	spaceId: number,
}
export class ContextItem
{
	spaceId: number;
	message: string;
	emitter: EventEmitter;

	constructor(options: Options)
	{
		this.message = options.message;
		this.spaceId = options.spaceId;
		this.emitter = new EventEmitter();
	}

	getEmitter(): EventEmitter
	{
		return this.emitter;
	}
}