import {Loc, Text, Tag} from 'main.core';
import {Popup} from 'main.popup';
import {Button} from 'ui.buttons';

export class DialogError
{
	text;
	helpArticleId;

	constructor(options = {})
	{
		this.text = options.text || '';
		this.helpArticleId = options.helpArticleId || '';
	}

	popup()
	{
		const popup = new Popup(null, null, {
			events: {
				onPopupClose: () => popup.destroy()
			},
			content: this.getContent(),
			maxWidth: 500,
			overlay: true,
			buttons: [
				new Button({
					text : Loc.getMessage('CAT_WAREHOUSE_MASTER_CLEAR_CLOSE'),
					color: Button.Color.PRIMARY,
					onclick: () => popup.close()
				})
			]
		});
		popup.show();
	}
	showHelp(event)
	{
		if (top.BX.Helper)
		{
			top.BX.Helper.show('redirect=detail&code=' + this.helpArticleId);
			event.preventDefault();
		}
	}
	getHelpLink()
	{
		const result = Tag.render`
			<a href="#" class="ui-link ui-link-dashed documents-grid-link">
				${Text.encode(Loc.getMessage('CAT_WAREHOUSE_MASTER_STORE_DETAILS'))}
			</a>
		`;
		result.addEventListener('click', this.showHelp.bind(this));

		return result;
	}
	getContent()
	{
		return Tag.render`
			<div class="catalog-warehouse-master-clear-popup-content">
				<h3>
					${Loc.getMessage('CAT_WAREHOUSE_MASTER_STORE_USE_10')}
				</h3>	
				<div class="catalog-warehouse-master-clear-popup-text">
					${Text.encode(this.text)} 
					${this.helpArticleId ? this.getHelpLink() : ''}
				</div>
			</div>
		`;
	}
}
