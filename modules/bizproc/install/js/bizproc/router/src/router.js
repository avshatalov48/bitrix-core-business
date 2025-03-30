import 'sidepanel';

export class Router
{
	static init()
	{
		if (top !== window)
		{
			top.BX.Runtime.loadExtension('bizproc.router').then(({ Router }) => {
				Router.init();
			}).catch(e => console.error(e));

			return;
		}

		this.#bind();
	}

	static #bind()
	{
		top.BX.SidePanel.Instance.bindAnchors({
			rules:
				[
					{
						condition: [
							'/rpa/task/',
						],
						options: {
							width: 580,
							cacheable: false,
							allowChangeHistory: false,
						},
					},
					{
						condition: [
							'/company/personal/bizproc/([a-zA-Z0-9\\.]+)/',
						],
						options: {
							cacheable: false,
							loader: 'bizproc:workflow-info',
							width: this.#detectSliderWidth(),
						},
					},
				],
		});
	}

	static #detectSliderWidth(): number
	{
		if (window.innerWidth < 1500)
		{
			return null; // default slider width
		}

		return 1500 + Math.floor((window.innerWidth - 1500) / 3);
	}

	static #openSlider(url: string, options: Object): void
	{
		top.BX.Runtime
			.loadExtension('sidepanel')
			.then(() => {
				BX.SidePanel.Instance.open(url, options);
			})
			.catch((response) => console.error(response.errors));
	}

	static openWorkflowLog(workflowId: string): void
	{
		const url = `/bitrix/components/bitrix/bizproc.log/slider.php?WORKFLOW_ID=${workflowId}`;
		const options = {
			width: this.#detectSliderWidth(),
			cacheable: false,
		};
		this.#openSlider(url, options);
	}

	static openWorkflow(workflowId: string): void
	{
		const url = `/company/personal/bizproc/${workflowId}/`;
		const options = {
			width: this.#detectSliderWidth(),
			cacheable: false,
			loader: 'bizproc:workflow-info',
		};
		this.#openSlider(url, options);
	}

	static openWorkflowTask(taskId: number, userId: number): void
	{
		let url = `/company/personal/bizproc/${taskId}/`;
		if (userId > 0)
		{
			url += `?USER_ID=${userId}`;
		}
		const options = {
			width: this.#detectSliderWidth(),
			cacheable: false,
			loader: 'bizproc:workflow-info',
		};
		this.#openSlider(url, options);
	}
}
