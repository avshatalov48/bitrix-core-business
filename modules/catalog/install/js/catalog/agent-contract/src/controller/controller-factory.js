import {type BaseEvent, EventEmitter} from 'main.core.events';
import {AgentContractController} from "./controller";

export class ControllersFactory
{
	constructor(eventName)
	{
		EventEmitter.subscribe(eventName + ':onInitialize', (event: BaseEvent) => {
			const [, eventArgs] = event.getCompatData();
			eventArgs.methods['agent_contract'] = this.factory.bind(this);
		});
	}

	factory(type, controlId, settings)
	{
		if (type === 'agent_contract')
		{
			return new AgentContractController(controlId, settings);
		}

		return null;
	}
}
