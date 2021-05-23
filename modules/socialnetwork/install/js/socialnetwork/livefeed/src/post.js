import {Popup} from 'main.popup';
import {Dom, Loc, Tag} from "main.core";
import {Button} from "ui.buttons";

class Post
{
	constructor()
	{
	}

	showBackgroundWarning({
		urlToEdit,
		menuPopupWindow
	})
	{
		const content = Tag.render`<div>${Loc.getMessage('SONET_EXT_LIVEFEED_POST_BACKGROUND_EDIT_WARNING_DESCRIPTION')}</div>`;

		const dialog = new Popup('create id here', null, {
			autoHide: true,
			closeByEsc: true,
			offsetLeft: 0,
			offsetTop: 0,
			draggable: true,
			bindOnResize: false,
			titleBar: Loc.getMessage('SONET_EXT_LIVEFEED_POST_BACKGROUND_EDIT_WARNING_TITLE'),
			closeIcon: true,
			className: 'sonet-livefeed-popup-warning',
			content: content,
			events: {},
			cacheable: false,
			buttons: [
				new Button({
					text: Loc.getMessage('SONET_EXT_LIVEFEED_POST_BACKGROUND_EDIT_WARNING_BUTTON_SUBMIT'),
					className: 'ui-btn ui-btn-primary',
					events: {
						click: () => {
							window.location = urlToEdit;
							dialog.close();
							if (menuPopupWindow)
							{
								menuPopupWindow.close();
							}
						}
					}
				}),
				new Button({
					text: Loc.getMessage('SONET_EXT_LIVEFEED_POST_BACKGROUND_EDIT_WARNING_BUTTON_CANCEL'),
					className: 'ui-btn ui-btn-light',
					events : {click : () => {
						dialog.close();
						if (menuPopupWindow)
						{
							menuPopupWindow.close();
						}
					}}
				})
			]
		});

		dialog.show();

		return false;
	}
}

export {
	Post
};