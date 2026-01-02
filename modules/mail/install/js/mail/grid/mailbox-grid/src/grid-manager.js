import { ActionFactory } from './action/action-factory';

type runActionConfig = {
	actionId: string,
	options: Array<{ [key: string]: any }>,
	params: Array<{ [key: string]: any }>,
}

export class GridManager
{
	static instances: Array<GridManager> = [];
	#grid: BX.Main.grid;

	constructor(gridId: string)
	{
		this.#grid = BX.Main.gridManager.getById(gridId)?.instance;
	}

	static getInstance(gridId: string): GridManager
	{
		if (!this.instances[gridId])
		{
			this.instances[gridId] = new GridManager(gridId);
		}

		return this.instances[gridId];
	}

	getGrid(): BX.Main.grid
	{
		return this.#grid;
	}

	runAction(config: runActionConfig): void
	{
		const actionId = config.actionId;
		const options = config.options;
		options.grid = this.#grid;

		const action = ActionFactory.create(actionId, options);
		if (action)
		{
			const params = config.params;
			action.setActionParams(params);
			action.execute();
		}
	}
}
