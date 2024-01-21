type Params = {
	pathToInvite: string,
}

export class UsersRouter
{
	#sidePanelManager: BX.SidePanel.Manager;

	#pathToInvite: string;

	constructor(params: Params)
	{
		this.#sidePanelManager = BX.SidePanel.Instance;

		this.#pathToInvite = params.pathToInvite;
	}

	openInvite()
	{
		this.#sidePanelManager.open(this.#pathToInvite, {
			width: 950,
			loader: 'group-invite-loader',
		});
	}
}