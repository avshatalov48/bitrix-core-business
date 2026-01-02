import 'sidepanel';
import { Loc, Text, Uri } from 'main.core';
import { EditConstantParams } from './types/edit-constant-params';

export class Router
{
	static #startSliderWidth: Number = 970;

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

	static openUserProcessesStart(options: Object): void
	{
		const sliderOptions = {
			width: this.#startSliderWidth,
			cacheable: false,
			loader: 'bizproc:start-process-page',
			...options,
		};

		let url = '/bizproc/start/';
		if (options && options.requestMethod === 'get' && options.requestParams)
		{
			url = BX.Uri.addParam(url, options.requestParams);
		}

		this.#openSlider(url, sliderOptions);
	}

	static openWorkflowStartList(options: Object): void
	{
		const sliderOptions = {
			width: this.#startSliderWidth,
			cacheable: false,
			loader: 'bizproc:start-process-page',
			...options,
		};

		let url = '/bitrix/components/bitrix/bizproc.workflow.start.list/';
		if (options && options.requestMethod === 'get' && options.requestParams)
		{
			url = BX.Uri.addParam(url, options.requestParams);
		}

		this.#openSlider(url, sliderOptions);
	}

	static openWorkflowChangeConstants(params: EditConstantParams): void
	{
		const url = Router.#createEditConstantSlider(params);
		const sliderOptions = {
			width: 900,
			cacheable: false,
			allowChangeHistory: false,
		};

		this.#openSlider(url, sliderOptions);
	}

	static #createEditConstantSlider(params: EditConstantParams): string
	{
		let url = Uri.addParam(
			'/bitrix/components/bitrix/bizproc.workflow.start/',
			{ sessid: Loc.getMessage('bitrix_sessid'), action: 'CHANGE_CONSTANTS' },
		);

		const templateId = Text.toInteger(params.templateId);
		if (templateId > 0)
		{
			url = Uri.addParam(url, { templateId });
		}

		if (params.signedDocumentType)
		{
			url = Uri.addParam(url, { signedDocumentType: params.signedDocumentType });
		}

		return url;
	}
}
