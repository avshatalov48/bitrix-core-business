import { Tag, Uri } from 'main.core';

type PublicationOptions = {
	siteId: number,
	landingId: number,
	url: string
};

type AjaxResult = {
	type: string,
	result: any
};

/**
example:
var instance = new BX.Landing.Dialog.Publication.getInstance({
	landingId: this.id,
	siteId: this.siteId,
	url: this.url
});
instance.publication(
	(mode !== 'landing') ? 'site' : 'landing'
);
*/

export class Publication
{
	static instance: ?Publication = null;
	dialog: ?BX.PopupWindow = null;
	siteId: number;
	landingId: number;
	url: string;

	constructor(options: PublicationOptions)
	{
		this.siteId = options.siteId;
		this.landingId = options.landingId;
		this.url = options.url;
	}

	static getInstance(options: PublicationOptions): Publication
	{
		if (!Publication.instance)
		{
			Publication.instance = new Publication(options);
		}
		return Publication.instance;
	}

	publication(mode: string)
	{
		const action = (mode === 'site') ? 'Site::publication' : 'Landing::publication';
		const data = {
			data: (mode === 'site')
			? {
				id: this.siteId
			}
			: {
				lid: this.landingId
			},
			actionType: 'rest',
			sessid: BX.message('bitrix_sessid')
		};

		this.renderPopup();

		BX.ajax({
			url: Uri.addParam(window.location.href, {action}),
			data,
			dataType: 'json',
			method: 'POST',
			onsuccess: (result: AjaxResult) => {
				if (result.type === 'error')
				{
					console.log(result.result);
					this.renderErrorPopupContent(result.result[0].error_description);
				}
				else
				{
					this.renderSuccessPopupContent();
				}
			}
		});
	}

	renderPopup()
	{
		if (!this.dialog)
		{
			this.dialog = new BX.PopupWindow('landing-publication-confirm', null, {
				content: '',
				titleBar: {content: 'Publication'},
				offsetLeft: 0,
				offsetTop: 0,
				buttons: [
					new BX.PopupWindowButton({
						text: 'OK',
						events: {
							click: function()
							{
								this.popupWindow.close();
							}
						}
					})
				]
			});
		}

		this.renderWaitPopupContent();
		this.dialog.show();
	}

	renderContent(status: HTMLElement)
	{
		this.dialog.setContent(Tag.render`
			<div class="ui-publication">
				<div>Publication dialog</div>
				<div>URL: <a href="${this.url}" target="_blank">${this.url}</a></div>
				${status}
			</div>
		`);
	}

	renderWaitPopupContent()
	{
		this.renderContent(Tag.render`
			<span class="ui-publication-name">Please wait...</span>
		`);
	}

	renderSuccessPopupContent()
	{
		this.renderContent(Tag.render`
			<span class="ui-publication-name">SUCCESS!</span>
		`);
	}

	renderErrorPopupContent(error: string)
	{
		this.renderContent(Tag.render`
			<span class="ui-publication-name">ERROR! ${error}</span>
		`);
	}
}