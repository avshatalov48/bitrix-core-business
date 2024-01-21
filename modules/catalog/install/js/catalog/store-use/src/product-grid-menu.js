import { StoreSlider } from './slider';

class ProductGridMenu
{
	static reloadGridAction()
	{
		document.location.reload();
	}

	static openWarehousePanel(url)
	{
		new StoreSlider().open(
			url,
			{
				data: {
					closeSliderOnDone: false,
				},
			},
		)
			.then(() => {
				ProductGridMenu.reloadGridAction();
			})
			.catch(() => {});
	}
}

export {
	ProductGridMenu,
};
