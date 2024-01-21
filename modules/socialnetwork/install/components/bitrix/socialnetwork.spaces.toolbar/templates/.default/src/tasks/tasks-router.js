import { Uri } from 'main.core';

type Params = {
	pathToGroupTasks?: string,
	pathToGroupTasksTask?: string,
}

export class TasksRouter
{
	#sidePanelManager: BX.SidePanel.Manager;

	#pathToGroupTasks: string;
	#pathToGroupTasksTask: string;

	constructor(params: Params)
	{
		this.#pathToGroupTasks = params.pathToGroupTasks;
		this.#pathToGroupTasksTask = params.pathToGroupTasksTask;

		this.#sidePanelManager = BX.SidePanel.Instance;
	}

	redirectTo(url)
	{
		location.href = url;
	}

	redirectToScrumTasks(urlParam: string, urlValue: string)
	{
		const viewUri = new Uri(this.#pathToGroupTasks);
		viewUri.setQueryParam(urlParam, urlValue);

		location.href = viewUri.toString();
	}

	redirectToScrumView(view: string)
	{
		const viewUri = new Uri(this.#pathToGroupTasks);

		viewUri.setQueryParam('tab', view);

		location.href = viewUri.toString();
	}

	showTask(taskId: number)
	{
		this.#sidePanelManager.open(
			this.#pathToGroupTasksTask
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
