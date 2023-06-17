import {Type} from 'main.core'
import {Utils as MessengerUtils} from 'im.lib.utils';

export class BackgroundDialog
{
	static isAvailable()
	{
		return MessengerUtils.platform.getDesktopVersion() >= 52;
	}

	static isMaskAvailable()
	{
		return MessengerUtils.platform.isDesktopFeatureEnabled('mask');
	}

	static open(options)
	{
		options = Type.isPlainObject(options) ? options : {};
		const tab = Type.isStringFilled(options.tab) ? options.tab : 'background'; // mask, background

		if (!this.isAvailable())
		{
			if (window.BX.Helper)
			{
				window.BX.Helper.show("redirect=detail&code=12398124");
			}

			return false;
		}

		const html =
			`<div id="bx-desktop-loader" class="bx-desktop-loader-wrap">
						<div class="bx-desktop-loader">
							<svg class="bx-desktop-loader-circular" viewBox="25 25 50 50">
								<circle class="bx-desktop-loader-path" cx="50" cy="50" r="20" fill="none" stroke-miterlimit="10"/>
							</svg>
						</div>
					</div>
					<div id="placeholder"></div>`
		;

		const js = `BX.Runtime.loadExtension("im.v2.component.call-background").then(function(exports) {
				BX.Vue3.BitrixVue.createApp({
					components: {CallBackground: exports.CallBackground},
					template: '<CallBackground tab="${tab}"/>',
				}).mount("#placeholder");
			});`;

		(opener || top).BX.desktop.createWindow("callBackground", (controller) =>
		{
			const title = this.isMaskAvailable() ? BX.message('BXD_CALL_BG_MASK_TITLE') : BX.message('BXD_CALL_BG_TITLE');
			controller.SetProperty("title", title);
			controller.SetProperty("clientSize", {Width: 943, Height: 670});
			controller.SetProperty("minClientSize", {Width: 943, Height: 670});
			controller.SetProperty("backgroundColor", "#2B3038");
			controller.ExecuteCommand("center");
			controller.ExecuteCommand("html.load", (opener || top).BXIM.desktop.getHtmlPage(html, js, false));
		});

		return true;
	}
}
