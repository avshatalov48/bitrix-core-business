import { Reflection } from 'main.core';
import { GridActions } from 'catalog.agent-contract';

const namespace = Reflection.namespace('BX.Catalog.Component');

class AgentContractList
{
	grid = null;
	gridActions = null;
	createUrl = null;

	constructor(options = {})
	{
		this.gridId = options.gridId;
		this.createUrl = options.createUrl;

		if (BX.Main.gridManager)
		{
			this.grid = BX.Main.gridManager.getInstanceById(this.gridId);
		}

		this.gridActions = new GridActions({
			grid: this.grid,
		});

		this.sliderOptions = {
			allowChangeHistory: false,
			cacheable: false,
			width: 650,
		};
	}

	open(url)
	{
		BX.SidePanel.Instance.open(url, this.sliderOptions);
	}

	create()
	{
		BX.SidePanel.Instance.open(this.createUrl, this.sliderOptions);
	}

	delete(id)
	{
		this.gridActions.delete(id);
	}

	deleteList()
	{
		const ids = this.grid.getRows().getSelectedIds();
		if (ids && ids.length > 0)
		{
			this.gridActions.deleteList(ids);
		}
	}

	static openHelpDesk()
	{
		if (top.BX.Helper)
		{
			top.BX.Helper.show('redirect=detail&code=17917894');
			event.preventDefault();
		}
	}
}

namespace.AgentContractList = AgentContractList;
