import { Uri } from 'main.core';

type Params = {
	pathToTasks?: string,
	pathToTasksTask?: string,
}

export class TasksRouter
{
	#sidePanelManager: BX.SidePanel.Manager;

	#pathToTasks: string;
	#pathToTasksTask: string;

	constructor(params: Params)
	{
		this.#pathToTasks = params.pathToTasks;
		this.#pathToTasksTask = params.pathToTasksTask;

		this.#sidePanelManager = BX.SidePanel.Instance;
	}

	redirectTo(url)
	{
		top.BX.Socialnetwork.Spaces.space.reloadPageContent(url);
	}

	redirectToTasks(urlParam: string, urlValue: string)
	{
		const viewUri = new Uri(this.#pathToTasks);
		viewUri.setQueryParam(urlParam, urlValue);

		top.BX.Socialnetwork.Spaces.space.reloadPageContent(viewUri.toString());
	}

	redirectToScrumView(view: string)
	{
		const viewUri = new Uri(this.#pathToTasks);

		viewUri.setQueryParam('tab', view);

		top.BX.Socialnetwork.Spaces.space.reloadPageContent(viewUri.toString());
	}

	showTask(taskId: number)
	{
		this.#sidePanelManager.open(
			this.#pathToTasksTask
				.replace('#action#', 'view')
				.replace('#task_id#', taskId),
		);
	}

	showSidePanel(url)
	{
		this.#sidePanelManager.open(url);
	}

	showByExtension(fullName: string, shortName: string, params: Object): Promise
	{
		return top.BX.Runtime.loadExtension(fullName)
			.then((exports) => {
				const className = shortName.replaceAll('-', '');

				if (exports && exports[className])
				{
					const extension = new exports[className](params);

					extension.show();

					return extension;
				}

				return null;
			})
		;
	}
}
