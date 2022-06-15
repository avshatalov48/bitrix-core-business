import {Layout} from 'ui.sidepanel.layout';
import PreviewContent from './view/previewcontent.js';

export class ConsentPreview
{

	constructor()
	{
	}

	static open(consentId)
	{
		if (!consentId)
		{
			return;
		}

		const view = (new PreviewContent());
		BX.SidePanel.Instance.open("sender:consent-preview", {
			width: 800,
			cacheable: false,
			contentCallback: () => {
				return Layout.createContent({
					extensions:[
						'ui.buttons',
						'ui.buttons.icons',
						'ui.notification',
						'ui.sidepanel-content',
						'ui.sidepanel.layout',
						'sender.consent.preview',
					],
					content ()
					{
						BX.ajax.runAction('sender.consentPreview.loadData', {
							json: {
								id:consentId
							},
						}).then((response) => {
								view.setText(response.data.consentBody) || "";
								view.setApproveBtn(response.data.approveBtnText);
								view.setRejectBtn(response.data.rejectBtnText);
							},
							(response) => {
								// view.setText(response.data.consent);
						});

						return view.getTemplate();
					},
					buttons ({cancelButton})
					{
						return [
							cancelButton,
						];
					},
				});
			},
		});
	}

}