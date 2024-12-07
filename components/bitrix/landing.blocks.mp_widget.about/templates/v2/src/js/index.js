export class AboutV2 extends BX.Landing.Widget.Base
{
	constructor(element)
	{
		super(element);
		this.initialize(element);
	}

	initialize(element)
	{
		const mainContainer = element.querySelector('.landing-widget-view-main');
		const sidebarContainer = element.querySelector('.landing-widget-view-sidebar');
		const widgetOptions = {
			mainContainer,
			sidebarContainer,
		};
		this.deleteContextDependentContainer(widgetOptions);
	}
}
