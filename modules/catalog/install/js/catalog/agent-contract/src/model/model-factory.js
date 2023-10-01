import {type BaseEvent, EventEmitter} from 'main.core.events'
import {AgentContractModel} from "./model";

export class ModelFactory
{
	constructor()
	{
		EventEmitter.subscribe('BX.UI.EntityEditorModelFactory:onInitialize', (event: BaseEvent) => {
			const [, eventArgs] = event.getCompatData();
			eventArgs.methods['agent_contract'] = this.factory.bind(this);
		});
	}

	factory(type, controlId, settings)
	{
		if (type === 'agent_contract')
		{
			return new AgentContractModel(controlId, settings);
		}

		return null;
	}
}
