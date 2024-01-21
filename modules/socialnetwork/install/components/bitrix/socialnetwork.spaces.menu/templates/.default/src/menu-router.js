import { Uri } from 'main.core';

type Params = {
	pathToFeatures?: string,
	pathToUsers?: string,
	pathToInvite?: string,
}

export class MenuRouter
{
	#pathToFeatures: string;
	#pathToUsers: string;
	#pathToInvite: string;

	#sidePanelManager: BX.SidePanel.Manager;

	constructor(params: Params)
	{
		this.#sidePanelManager = BX.SidePanel.Instance;

		this.#pathToFeatures = params.pathToFeatures;
		this.#pathToUsers = params.pathToUsers;
		this.#pathToInvite = params.pathToInvite;
	}

	openGroupFeatures()
	{
		this.#sidePanelManager.open(this.#pathToFeatures, {
			width: 800,
			loader: 'group-features-loader',
		});
	}

	openGroupUsers(mode: 'all' | 'in' | 'out')
	{
		const availableModes = {
			all: 'members',
			in: 'requests_in',
			out: 'requests_out',
		};

		const uri = new Uri(this.#pathToUsers);
		uri.setQueryParams({
			mode: availableModes[mode],
		});

		this.#sidePanelManager.open(uri.toString(), {
			width: 1200,
			cacheable: false,
			loader: 'group-users-loader',
		});
	}

	openGroupInvite()
	{
		this.#sidePanelManager.open(this.#pathToInvite, {
			width: 950,
			loader: 'group-invite-loader',
		});
	}
}
