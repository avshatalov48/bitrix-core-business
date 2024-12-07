import { Extension, Text } from 'main.core';

class ExtensionSettings {
	#config: {
		currentUserId: number,
		currentUserTimeOffset: number,
		openEventSection: any,
		pullEventUserFieldsKey: string,
	};

	constructor()
	{
		this.#config = Extension.getSettings('calendar.open-events.list');
	}

	get currentUserId(): number
	{
		return Text.toNumber(this.#config.currentUserId);
	}

	get openEventSection(): any
	{
		return this.#config.openEventSection;
	}

	get currentUserTimeOffset(): number
	{
		return Text.toNumber(this.#config.currentUserTimeOffset);
	}

	get pullEventUserFieldsKey(): string
	{
		return this.#config.pullEventUserFieldsKey.toString();
	}
}

export const AppSettings = new ExtensionSettings();
