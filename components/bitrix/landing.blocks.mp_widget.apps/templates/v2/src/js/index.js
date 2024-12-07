import { QrAuthorization } from 'ui.qrauthorization';
import { Event } from 'main.core';

export class AppsV2 extends BX.Landing.Widget.Base
{
	constructor(element, options)
	{
		super(element);
		this.initialize(element, options);
	}

	initialize(element, options = {})
	{
		const mainContainer = element.querySelector('.landing-widget-view-main');
		const sidebarContainer = element.querySelector('.landing-widget-view-sidebar');
		const widgetOptions = {
			mainContainer,
			sidebarContainer,
		};
		this.deleteContextDependentContainer(widgetOptions);

		const qrButton = element.querySelector('.landing-widget-qr-button');
		const qrAuth = new QrAuthorization(options);
		if (qrButton && qrAuth)
		{
			Event.bind(qrButton, 'click', () => {
				const popup = qrAuth.getPopup();
				if (popup)
				{
					popup.show();
				}
			});
		}
	}
}
