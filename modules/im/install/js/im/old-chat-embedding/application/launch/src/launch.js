import {Logger} from 'im.old-chat-embedding.lib.logger';

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
			BX.Messenger.Embedding.Application[name] = new BX.Messenger.Embedding.Application[`${application}Application`](params);

			return BX.Messenger.Embedding.Application[name].ready();
		}
		catch (error)
		{
			Logger.error(`BX.Messenger.Application.Launch: application "${application}" is not initialized.`, error);

			return false;
		}
	};

	if (!BX.Messenger.Embedding.Application[`${application}Application`] && BX?.Runtime?.loadExtension)
	{
		const loadExtension = `im.old-chat-embedding.application.${application.toString().toLowerCase()}`;

		return BX.Runtime.loadExtension(loadExtension).then(() => launch());
	}

	return launch();
};

export {ApplicationLauncher as Launch};