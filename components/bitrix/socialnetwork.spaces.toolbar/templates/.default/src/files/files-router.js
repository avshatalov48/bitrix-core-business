type Params = {
	pathToTrash: string,
	pathToUserFilesVolume: string,
}

export class FilesRouter
{
	#pathToTrash: string;
	#pathToVolume: string;

	constructor(params: Params)
	{
		this.#pathToTrash = params.pathToTrash;
		this.#pathToVolume = params.pathToUserFilesVolume;
	}

	redirectToTrash()
	{
		top.BX.Socialnetwork.Spaces.space.reloadPageContent(this.#pathToTrash);
	}

	redirectToVolume()
	{
		top.location.href = this.#pathToVolume;
	}
}