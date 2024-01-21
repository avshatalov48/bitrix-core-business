import {EventEmitter} from 'main.core.events';

export class BaseSettingsVisitor extends EventEmitter
{
	static #instances = {};

	constructor(params)
	{
		super(params);
		this.setEventNamespace('BX.UI.FormElement.Field');
	}

	visitSettingsElement(settingsElement)
	{

	}

	static getInstance()
	{
		const id = this.name;

		if (!BaseSettingsVisitor.#instances[id])
		{
			BaseSettingsVisitor.#instances[id] = new this();
		}

		return BaseSettingsVisitor.#instances[id]
	}
}
