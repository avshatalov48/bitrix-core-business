import {Loc,Event} from 'main.core';
import Stream from './stream';
import Package from './package';
import {EventEmitter} from 'main.core.events';

export default class Streams extends EventEmitter
{
	static maxInstances = 3;
	static #instance = new Map();
	static #packages = new Map();
	static #hiddenTag = Symbol('streams descriptor');

	static addPackage(pack: Package)
	{
		console.log('3. Add to a stream queue.');
		if (this.maxInstances > 0 && this.#instance.size > this.maxInstances)
		{
			this.#packages.set(pack);
		}
		else
		{
			this.#packages.delete(pack);
			this.#runPackage(pack);
		}
		if (!window[this.#hiddenTag])
		{
			window[this.#hiddenTag] = this.#catchWindow.bind(this);
			Event.bind(window, 'beforeunload', window[this.#hiddenTag]);
		}
	}

	static #catchWindow(event)
	{
		if (this.#packages.size > 0 || this.#instance.size > 0)
		{
			const confirmationMessage = Loc.getMessage('UPLOADER_UPLOADING_ONBEFOREUNLOAD');
			(event || window.event).returnValue = confirmationMessage;
			return confirmationMessage;
		}
	}

	static #runPackage(pack: Package)
	{
		const stream = new Stream();
		this.#instance.set(stream);
		console.log('3.1. Run package in a stream.');
		pack.subscribeOnce('done', () => {
			console.log('6. Package is done so release the stream.');
			this.#instance.delete(stream);
			stream.destroy();
			if (this.#packages.size > 0)
			{
				const [newPack] = this.#packages.entries().next().value;
				this.addPackage(newPack);
			}
			else if (this.#instance.size <= 0)
			{
				Event.unbind(window, 'beforeunload', window[this.#hiddenTag]);
				delete window[this.#hiddenTag];
			}
		});

		pack.run(stream);
	}
}