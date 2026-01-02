import { Text, Runtime } from 'main.core';

import { Logger } from 'im.v2.lib.logger';
import { Utils } from 'im.v2.lib.utils';

import type { JsonObject } from 'main.core';

export { ChatEmbeddedApplication } from './const';
export type { ChatEmbeddedApplicationType, ChatEmbeddedApplicationInstance } from './const';

const RESERVED_NAMES = new Set(['Launch', 'Core']);

type LaunchParams = JsonObject & { embedded: boolean };

export const Launch = async (applicationName: string, params: LaunchParams = {}): Promise => {
	const { embedded = false } = params;

	if (!validateApplicationName(applicationName))
	{
		Logger.error('BX.Messenger.Application.Launch: specified name is forbidden');

		return Promise.reject();
	}

	if (!isApplicationLoaded(applicationName))
	{
		await loadExtension(applicationName, embedded);
	}

	const capitalizedName = Text.capitalize(applicationName);
	const className = `${capitalizedName}Application`;

	const preparedApplicationName = prepareApplicationName(applicationName, embedded);
	try
	{
		BX.Messenger.v2.Application[preparedApplicationName] = new BX.Messenger.v2.Application[className](params);

		return BX.Messenger.v2.Application[preparedApplicationName].ready();
	}
	catch (error)
	{
		const errorMessage = `BX.Messenger.Application.Launch: application "${capitalizedName}" is not initialized.`;
		Logger.error(errorMessage, error);

		return Promise.reject(errorMessage);
	}
};

const validateApplicationName = (applicationName: string): boolean => {
	const capitalizedName = Text.capitalize(applicationName);

	return !RESERVED_NAMES.has(capitalizedName) && !capitalizedName.endsWith('Application');
};

const isApplicationLoaded = (applicationName: string): boolean => {
	const capitalizedName = Text.capitalize(applicationName);
	const className = `${capitalizedName}Application`;

	return Boolean(BX.Messenger.v2.Application[className]);
};

const loadExtension = async (applicationName: string, embedded: boolean) => {
	const extensionName = embedded ? `im.v2.application.integration.${applicationName}` : `im.v2.application.${applicationName}`;

	return Runtime?.loadExtension(extensionName);
};

const prepareApplicationName = (applicationName: string, embedded: boolean): string => {
	if (embedded)
	{
		return `${applicationName}_${Utils.text.getUuidV4()}`;
	}

	return applicationName;
};
