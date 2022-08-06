import { Slider } from './slider';

class ProductGridMenu
{
	static reloadGridAction()
	{
		document.location.reload();
	}

	static openWarehousePanel(url)
	{
		new Slider().open(
			url,
			{
				data: {
					closeSliderOnDone: false
				}
			}
		)
			.then(() => {
				ProductGridMenu.reloadGridAction();
			})
		;
	}
}

export
{
	ProductGridMenu
}