import { Loc, Tag, Event } from 'main.core';
import { MessageBox } from 'ui.dialogs.messagebox';

export class DialogError
{
	text: String;
	helpArticleId: String;

	constructor(options = {})
	{
		this.text = options.text || '';
		this.helpArticleId = options.helpArticleId || '';
	}

	popup()
	{
		MessageBox.alert(
			this.getContent(),
			(messageBox) => messageBox.close(),
			Loc.getMessage('CAT_WAREHOUSE_MASTER_CLEAR_CLOSE'),
		);
	}

	getContent(): String
	{
		const result = Tag.render`
			<div class="catalog-warehouse-master-clear-popup-text">
				${this.text}
			</div>
		`;

		const helpLinkContainer = result.querySelector('a');

		if (helpLinkContainer)
		{
			Event.bind(helpLinkContainer, 'click', ((event) => {
				event.preventDefault();
				if (top.BX.Helper)
				{
					top.BX.Helper.show(`redirect=detail&code=${this.helpArticleId}`);
				}
			}));
		}

		return result;
	}
}
