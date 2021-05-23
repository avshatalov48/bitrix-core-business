import IblockSectionField from './iblock-section/field';
import NameCodeField from './name-code/field';
import {type BaseEvent, EventEmitter} from 'main.core.events'

export default class FieldsFactory
{
	constructor()
	{
		EventEmitter.subscribe('BX.UI.EntityEditorControlFactory:onInitialize', (event: BaseEvent) => {
			const [, eventArgs] = event.getCompatData();
			eventArgs.methods['entityCard'] = this.factory.bind(this);
		});
	}

	factory(type, controlId, settings)
	{
		if (type === 'iblock_section')
		{
			return new IblockSectionField(controlId, settings);
		}
		else if (type === 'name-code')
		{
			return new NameCodeField(controlId, settings);
		}

		return null;
	}
}