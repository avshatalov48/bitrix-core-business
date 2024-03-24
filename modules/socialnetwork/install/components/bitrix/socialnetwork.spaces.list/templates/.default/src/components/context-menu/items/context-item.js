import { EventEmitter } from 'main.core.events';

type Options = {
	message: string,
	spaceId: number,
}
export class ContextItem extends EventEmitter
{
	spaceId: number;
	message: string;
	emitter: EventEmitter;

	constructor(options: Options)
	{
		super();

		this.setEventNamespace('BX.Socialnetwork.Spaces.ContextItem');

		this.message = options.message;
		this.spaceId = options.spaceId;
		this.emitter = new EventEmitter();
	}
}
