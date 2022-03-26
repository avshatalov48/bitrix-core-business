import {Popup} from 'main.popup';
import {Tag, Text} from 'main.core';
import {Editor} from './product.list.editor';

export default class HintPopup
{
	editor: Editor;
	hintPopup: ?Popup;

	constructor(editor: Editor)
	{
		this.editor = editor;
	}

	load(node: HTMLElement, text: String): Popup
	{
		if (!this.hintPopup)
		{
			this.hintPopup = new Popup(
				'ui-hint-popup-' + this.editor.getId(),
				null,
				{
					darkMode: true,
					closeIcon: true,
					animation: 'fading-slide'
				}
			);
		}

		this.hintPopup.setBindElement(node);
		this.hintPopup.adjustPosition();
		this.hintPopup.setContent(Tag.render`
			<div class='ui-hint-content'>${Text.encode(text)}</div>
		`);

		return this.hintPopup;
	}

	show()
	{
		if (this.hintPopup)
		{
			this.hintPopup.show();
		}
	}

	close()
	{
		if (this.hintPopup)
		{
			this.hintPopup.close();
		}
	}
}