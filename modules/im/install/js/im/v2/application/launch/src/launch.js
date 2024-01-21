import { Logger } from 'im.v2.lib.logger';

import type { JsonObject } from 'main.core';

const ApplicationLauncher = (app: string, params: JsonObject = {}) => {
	let application = app;
	const name = app.toString();

	application = application.slice(0, 1).toUpperCase() + application.slice(1);

	if (application === 'Launch' || application === 'Core' || application.endsWith('Application'))
	{
		Logger.error('BX.Messenger.Application.Launch: specified name is forbidden.');

		return Promise.reject();
	}

	const launch = (): Promise => {
		try
		{
			BX.Messenger.v2.Application[name] = new BX.Messenger.v2.Application[`${application}Application`](params);

			return BX.Messenger.v2.Application[name].ready();
		}
		catch (error)
		{
			const errorMessage = `BX.Messenger.Application.Launch: application "${application}" is not initialized.`;
			Logger.error(errorMessage, error);

			return Promise.reject(errorMessage);
		}
	};

	if (!BX.Messenger.v2.Application[`${application}Application`] && BX?.Runtime?.loadExtension)
	{
		const loadExtension = `im.v2.application.${application.toString().toLowerCase()}`;

		return BX.Runtime.loadExtension(loadExtension).then(() => launch());
	}

	return launch();
};

export { ApplicationLauncher as Launch };
