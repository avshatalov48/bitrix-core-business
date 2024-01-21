type Params = {
	isUserSpace: boolean,
	isScrumSpace: boolean,
	viewMode: string,
}

export class TasksView
{
	#isUserSpace: boolean;
	#isScrumSpace: boolean;
	#viewMode: string;

	constructor(params: Params)
	{
		this.#isUserSpace = params.isUserSpace;
		this.#isScrumSpace = params.isScrumSpace;

		this.#setViewMode(params.viewMode);
	}

	getCurrentViewMode(): string
	{
		return this.#viewMode;
	}

	#setViewMode(viewMode: string)
	{
		const availableModes = this.#getAvailableModes().get(this.#getCurrentSpace());

		this.#viewMode = availableModes.has(viewMode) ? viewMode : this.#getDefaultMode();
	}

	#getAvailableModes(): Map
	{
		return new Map([
			['user', new Set(['list', 'plan', 'timeline', 'calendar', 'gantt'])],
			['group', new Set(['list', 'kanban', 'plan', 'timeline', 'calendar', 'gantt'])],
			['scrum', new Set(['plan', 'active', 'complete'])],
		]);
	}

	#getDefaultMode(): string
	{
		const defaultModes = new Map([
			['user', 'list'],
			['group', 'list'],
			['scrum', 'plan'],
		]);

		return defaultModes.get(this.#getCurrentSpace());
	}

	#getCurrentSpace(): string
	{
		if (this.#isUserSpace)
		{
			return 'user';
		}

		if (this.#isScrumSpace)
		{
			return 'scrum';
		}
		else
		{
			return 'group';
		}
	}
}
