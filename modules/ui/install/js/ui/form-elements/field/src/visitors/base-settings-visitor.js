import {EventEmitter} from 'main.core.events';

export class BaseSettingsVisitor extends EventEmitter
{
	static instances = [];

	constructor(params)
	{
		super(params);
		this.setEventNamespace('BX.UI.FormElement.Field');
	}

	visitSettingsElement(settingsElement)
	{

	}
}
