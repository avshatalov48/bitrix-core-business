type Params = {
	gridId: string,
}

type TaskSort = {
	field: string,
	direction: string,
}

export class TasksSortManager
{
	#gridId: string;

	constructor(params: Params)
	{
		this.#gridId = params.gridId;
	}

	setSort(taskSort: TaskSort)
	{
		const field = taskSort.field;
		const dir = taskSort.direction || 'asc';

		if (BX.Main.gridManager === undefined)
		{
			BX.ajax.post(
				BX.util.add_url_param('/bitrix/components/bitrix/main.ui.grid/settings.ajax.php', {
					GRID_ID: this.#gridId,
					action: 'setSort',
				}),
				{
					by: field,
					order: dir,
				},
				(res) => {
					try
					{
						res = JSON.parse(res);

						if (!res.error)
						{
							window.location.reload();
						}
					}
					catch (err)
					{
						console.log(err);
					}
				},
			);
		}
		else
		{
			const grid = BX.Main.gridManager.getById(this.#gridId).instance;
			grid.sortByColumn({ sort_by: field, sort_order: dir });

			if (field === 'SORTING')
			{
				grid.getRows().enableDragAndDrop();
			}
			else
			{
				grid.getRows().disableDragAndDrop();
			}
		}
	}
}
