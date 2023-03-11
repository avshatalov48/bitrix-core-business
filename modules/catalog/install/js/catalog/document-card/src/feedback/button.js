import {Loc, Tag} from 'main.core';
import 'ui.feedback.form';

export default class Button
{
	static render(parentNode, highlight)
	{
		const buttonTitle = Loc.getMessage('FEEDBACK_BUTTON_TITLE');

		const button = Tag.render`
			<button class="ui-btn ui-btn-light-border ui-btn-themes" title="${buttonTitle}">
				<span class="ui-btn-text">
					${buttonTitle}
				</span>
			</button>
		`;
		if (highlight)
		{
			button.style.zIndex = 140;
			button.style.backgroundColor = '#fff';
		}

		button.addEventListener('click', () => {
			BX.Catalog.DocumentCard.Slider.openFeedbackForm();
		});

		parentNode.appendChild(button);

		return button;
	}
}
