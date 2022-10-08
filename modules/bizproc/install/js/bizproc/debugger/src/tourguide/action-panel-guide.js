import {Guide} from 'ui.tour';
import {Text} from "main.core";

export class ActionPanelGuide
{
	#guide: Guide;

	constructor(options)
	{
		this.#guide = new Guide({
			steps: [
				{
					target: options.target,
					title: ActionPanelGuide.#getHtmlTitle(options.title) || '',
					text: options.text || '',
					//article: options.article,
					condition: {
						top: true,
						bottom: false,
						color: 'warning',
					},
				},
			],
			onEvents: true
		});
	}

	start()
	{
		this.#guide.getPopup().setWidth(370); //some magic ^_^
		this.#guide.showNextStep();
	}

	finish()
	{
		this.#guide.close();
	}

	static #getHtmlTitle(title): ?HTMLDivElement
	{
		if (title)
		{
			return `
				<div class="bizproc__action-panel-guide">
					<div class="bizproc__action-panel-guide--title --warning-icon">${Text.encode(title)}</div>
				</div>
			`;
		}

		return null;
	}
}