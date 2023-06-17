import {Dom} from 'main.core';
import {getGlobalContext, TrackingStatus, Template} from "bizproc.automation";

export class Statuses
{
	#nodes: Array<HTMLElement> = [];
	#lastColorStatusIndex: number = -1;
	#defaultStatusColor: string = '#d4d6da';

	constructor(stagesContainerNode: HTMLElement)
	{
		const stagesContainer = stagesContainerNode.querySelector('.bizproc-automation-status-list');
		if (stagesContainer)
		{
			this.#nodes = stagesContainer.querySelectorAll('[data-role="automation-status-title"]');
		}
	}

	init(templates: Array<Template>): void
	{
		const context = getGlobalContext();
		if (context.document.getId() <= 0)
		{
			this.#lastColorStatusIndex = this.#nodes.length - 1;
		}
		else
		{
			this.#lastColorStatusIndex = templates.findIndex((template) => (
				template.getStatusId() === context.document.getCurrentStatusId()
			));
		}
	}

	fixColors(): void
	{
		this.#fixBackgroundColors();
		this.#fixTitleColors();
	}

	#fixBackgroundColors(): void
	{
		this.#nodes.forEach((statusNode, index) => {
			const backgroundNode = statusNode.querySelector('.bizproc-automation__status--bg');

			if (backgroundNode)
			{
				const color = (
					this.#isColorStatus(index) && statusNode.dataset.bgcolor
						? statusNode.dataset.bgcolor
						: this.#defaultStatusColor
				);

				Dom.style(backgroundNode, {
					backgroundColor: color,
					borderColor: color,
				});
			}
		});
	}

	#fixTitleColors(): void
	{
		this.#nodes.forEach((statusNode, index) => {
			if (!this.#isColorStatus(index))
			{
				return;
			}

			const backgroundColor = statusNode.dataset.bgcolor;
			if (backgroundColor)
			{
				const bigint = parseInt(backgroundColor, 16);
				const red = (bigint >> 16) & 255;
				const green = (bigint >> 8) & 255;
				const blue = bigint & 255;

				const isDarkColor = 0.21 * red + 0.72 * green + 0.07 * blue < 145;
				if (isDarkColor)
				{
					Dom.style(statusNode, 'color', 'white');
				}
			}
		});
	}

	#isColorStatus(index: number): boolean
	{
		return index <= this.#lastColorStatusIndex;
	}
}