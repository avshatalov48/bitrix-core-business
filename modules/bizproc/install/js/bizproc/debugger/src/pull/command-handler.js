import {Type} from 'main.core';
import {PULL} from "pull.client";
import {EventEmitter} from "main.core.events";

export class CommandHandler extends EventEmitter
{
	#unsubscribe: CallableFunction;
	#commands = [
		'documentStatus',
		'documentValues',
		'documentDelete',
		//workflow
		'workflowStatus',
		'workflowEventAdd',
		'workflowEventRemove',
		//track
		'trackRow',
		//session
		'sessionFinish'
	];

	constructor()
	{
		super();
		this.setEventNamespace('BX.Bizproc.Debugger.Pull');

		this.#unsubscribe = PULL.subscribe(this);
	}

	destroy()
	{
		if (Type.isFunction(this.#unsubscribe))
		{
			this.#unsubscribe();
		}

		this.#unsubscribe = null;
	}

	getModuleId(): string
	{
		return 'bizproc';
	}

	getSubscriptionType()
	{
		return BX.PullClient.SubscriptionType.Server;
	}

	getMap()
	{
		const map = {};
		this.#commands.forEach(command => {
			map[command] = this.#handleCommand.bind(this);
		});

		return map;
	}

	#handleCommand(params, extra, command)
	{
		this.emit(command, params);
	}
}