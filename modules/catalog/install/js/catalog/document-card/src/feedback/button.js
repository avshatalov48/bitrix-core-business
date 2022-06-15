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
			BX.UI.Feedback.Form.open({
				id: 'catalog-store-document-card-feedback',
				forms: [
					{'id': 384, 'lang': 'ru', 'sec': '0pskpd', 'zones': ['ru', 'by', 'kz']},
					{'id': 392, 'lang': 'en', 'sec': 'siqjqa', 'zones': ['en', 'ua']},
					{'id': 388, 'lang': 'es', 'sec': '53t2bu', 'zones': ['es']},
					{'id': 390, 'lang': 'de', 'sec': 'mhglfc', 'zones': ['de']},
					{'id': 386, 'lang': 'com.br', 'sec': 't6tdpy', 'zones': ['com.br']},
				],
			});
		});

		parentNode.appendChild(button);

		return button;
	}
}
