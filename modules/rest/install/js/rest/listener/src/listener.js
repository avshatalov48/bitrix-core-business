import { EventEmitter } from 'main.core.events';

export class Listener extends EventEmitter
{
	#command: string;
	#handlerCommand: function;
	#listeningState: boolean = false;

	constructor(command: string, handlerCommand: function)
	{
		super();
		this.setEventNamespace('BX.Rest.Listener');
		this.#command = command;
		this.#handlerCommand = handlerCommand;
	}

	listen(): void
	{
		if (this.#listeningState)
		{
			return;
		}

		BX.PULL.subscribe({
			type: BX.PullClient.SubscriptionType.Server,
			moduleId: 'rest',
			callback: (data) => {
				this.#handleCommand(data);
			}
		});
		this.#listeningState = true;
	}

	#handleCommand(data): void
	{
		if (data.command === this.#command)
		{
			this.emit('pull');
			this.#handlerCommand(data);
		}
	}
}