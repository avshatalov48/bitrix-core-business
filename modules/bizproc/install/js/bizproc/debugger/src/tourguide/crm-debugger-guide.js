import {EventEmitter} from "main.core.events";
import {FilterGuide} from "./filter-guide";
import {StageGuide} from "./stage-guide";

export type CrmDebuggerGuideOptions = {
	grid: BX.CRM.Kanban.Grid,
	showFilterStep: boolean,
	showStageStep: boolean,
	reserveFilterIds: string,
};

export class CrmDebuggerGuide extends EventEmitter
{
	#grid: BX.CRM.Kanban.Grid = null;
	#guides: Array = [];
	#reserveFilterIds: Array<string> = [];

	constructor(options: CrmDebuggerGuideOptions)
	{
		super();
		this.setEventNamespace('BX.Bizproc.Debugger.CrmDebuggerGuide');

		this.#grid = options.grid;
		this.#reserveFilterIds = options.reserveFilterIds;

		// reverse order
		if (options.showStageStep)
		{
			const stageStep = this.#getStageGuide();
			if (stageStep)
			{
				this.#guides.push(stageStep);
			}
		}

		if (options.showFilterStep)
		{
			const filterGuide = this.#getFilterGuide();
			if (filterGuide)
			{
				this.#guides.push(filterGuide);
			}
		}
	}

	#getStageGuide(): ?StageGuide
	{
		if (!this.#grid)
		{
			return;
		}

		const firstColumn = this.#grid.getColumns()[0];
		if (!firstColumn)
		{
			return null;
		}

		const guideTarget = firstColumn.getTitleContainer();
		if (!guideTarget)
		{
			return null;
		}

		const nextGuide = this.#guides[this.#guides.length - 1];

		return new StageGuide({
			target: '.' + guideTarget.classList[0],
			events: {
				'onShow': function () {
					this.emit('onStageGuideStepShow');
				}.bind(this),
				'onClose': function () {
					if (nextGuide)
					{
						nextGuide.start();
					}
					this.emit('onStageGuideStepClose');
				}.bind(this),
			},
		});
	}

	#getFilterGuide(): ?FilterGuide
	{
		const filterId = this.#grid ? [this.#grid.getData().gridId] :  this.#reserveFilterIds;
		if (filterId.length <= 0)
		{
			return null;
		}

		let filter;
		for (const key in filterId)
		{
			filter = BX.Main.filterManager.getById(filterId[key]);
			if (filter)
			{
				break;
			}
		}
		if (!filter)
		{
			return null;
		}

		const filterApi = filter.getApi();
		const guideTarget = filterApi?.parent?.getPopupBindElement()?.firstElementChild;

		if (!guideTarget)
		{
			return null;
		}

		const nextGuide = this.#guides[this.#guides.length - 1];

		return new FilterGuide({
			target: guideTarget,
			events: {
				'onShow': function () {
					this.emit('onFilterGuideStepShow');
				}.bind(this),
				'onClose': function () {
					if (nextGuide)
					{
						nextGuide.start();
					}
					this.emit('onFilterGuideStepClose');
				}.bind(this),
			}
		});
	}

	start()
	{
		if (this.#guides.length <= 0)
		{
			return;
		}

		this.#guides[this.#guides.length - 1].start();
	}
}