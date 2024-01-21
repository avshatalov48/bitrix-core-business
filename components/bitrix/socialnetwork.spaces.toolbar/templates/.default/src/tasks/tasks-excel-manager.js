type Params = {
	pathToTasks: string,
}

export class TasksExcelManager
{
	#pathToTasks: number;

	constructor(params: Params)
	{
		this.#pathToTasks = params?.pathToTasks;
	}

	/**
	 * @param options {{isAll: boolean}}
	 */
	getExportHref(options = {}): string
	{
		let href = `${this.#pathToTasks}?F_STATE=sV80&EXPORT_AS=EXCEL&ncc=1`;
		if (options.isAll)
		{
			href += '&COLUMNS=ALL';
		}

		return href;
	}

	getImportHref(): string
	{
		return `${this.#pathToTasks}import/`;
	}
}
