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
		location.href = this.#pathToTrash;
	}

	redirectToVolume()
	{
		location.href = this.#pathToVolume;
	}
}