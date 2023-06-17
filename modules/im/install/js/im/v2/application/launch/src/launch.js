import {Logger} from 'im.v2.lib.logger';

const ApplicationLauncher = function (app, params = {})
{
	let application = '';
	let name = '';

	if (typeof app === 'object')
	{
		name = app.name.toString();
		application = app.application.toString();
	}
	else
	{
		name = app.toString();
		application = app;
	}

	application = application.slice(0, 1).toUpperCase() + application.slice(1);

	if (application === 'Launch' || application === 'Core' || application.endsWith('Application'))
	{
		Logger.error('BX.Messenger.Application.Launch: specified name is forbidden.');

		return Promise.reject();
	}

	const launch = function()
	{
		try {
			BX.Messenger.v2.Application[name] = new BX.Messenger.v2.Application[`${application}Application`](params);

			return BX.Messenger.v2.Application[name].ready();
		}
		catch (error)
		{
			Logger.error(`BX.Messenger.Application.Launch: application "${application}" is not initialized.`, error);

			return false;
		}
	};

	if (!BX.Messenger.v2.Application[`${application}Application`] && BX?.Runtime?.loadExtension)
	{
		const loadExtension = `im.v2.application.${application.toString().toLowerCase()}`;

		return BX.Runtime.loadExtension(loadExtension).then(() => launch());
	}

	return launch();
};

export {ApplicationLauncher as Launch};