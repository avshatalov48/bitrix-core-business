import { DesktopApi } from 'im.v2.lib.desktop-api';

export const SliderBindings = {
	init()
	{
		const sliderBindingStatus = DesktopApi.getSliderBindingsStatus();
		if (sliderBindingStatus)
		{
			BX.SidePanel.Instance.enableAnchorBinding();

			return;
		}

		BX.SidePanel.Instance.disableAnchorBinding();
	},
};
