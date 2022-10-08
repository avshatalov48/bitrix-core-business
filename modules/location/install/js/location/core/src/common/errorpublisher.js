import {EventEmitter} from 'main.core.events';

export default class ErrorPublisher extends EventEmitter
{
	static #instance = null;
	static #onErrorEvent = 'onError';

	static getInstance()
	{
		if(ErrorPublisher.#instance === null)
		{
			ErrorPublisher.#instance = new ErrorPublisher();
		}

		return ErrorPublisher.#instance;
	}

	constructor()
	{
		super();
		this.setEventNamespace('BX.Location.Core.ErrorPublisher');
	}

	notify(errors: Error[])
	{
		this.emit(ErrorPublisher.#onErrorEvent, {errors});
	}

	subscribe(listener)
	{
		super.subscribe(ErrorPublisher.#onErrorEvent, listener);
	}
}