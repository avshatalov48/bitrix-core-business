import { DesktopApi } from 'im.v2.lib.desktop-api';
import { Utils } from 'im.v2.lib.utils';
import { Loc, Reflection } from 'main.core';

let conferenceList = [];
let conferenceIndex = 0;

export const Conference = {
	openConference(code: string): boolean
	{
		if (!Utils.conference.isValidCode(code))
		{
			return false;
		}

		if (!DesktopApi.isDesktop())
		{
			return false;
		}

		let windowSize = null;

		const sizes = [
			{ width: 2560, height: 1440 },
			{ width: 2048, height: 1152 },
			{ width: 1920, height: 1080 },
			{ width: 1600, height: 900 },
			{ width: 1366, height: 768 },
			{ width: 1024, height: 576 },
		];

		for (const size of sizes)
		{
			windowSize = size;
			if (screen.width > size.width && screen.height > size.height)
			{
				break;
			}
		}

		conferenceList = conferenceList.filter((name) => {
			return Boolean(DesktopApi.findWindow(name));
		});

		conferenceList.push(Utils.conference.getWindowNameByCode(code));

		DesktopApi.createWindow(Utils.conference.getWindowNameByCode(code), (controller) => {
			controller.SetProperty('title', Loc.getMessage('IM_LIB_DESKTOP_CONFERENCE_TITLE'));
			controller.SetProperty('clientSize', { Width: windowSize.width, Height: windowSize.height });

			// we need the first 'center' command to prevent the window from jumping after we show it
			controller.ExecuteCommand('center');
			controller.SetProperty('minClientSize', { Width: 940, Height: 400 });
			controller.SetProperty('backgroundColor', '#2B3038');
			controller.ExecuteCommand('html.load', `<script>location.href="/desktop_app/router.php?alias=${code}&videoconf";</script>`);
			controller.ExecuteCommand('show');

			// we need the second 'center' command because we know the exact size of the window after we show it
			controller.ExecuteCommand('center');
		});

		return true;
	},
	toggleConference(): boolean
	{
		if (conferenceIndex > conferenceList.length - 1)
		{
			conferenceIndex = 0;

			DesktopApi.showWindow();

			return true;
		}

		conferenceList = conferenceList.filter((name) => {
			return Boolean(DesktopApi.findWindow(name));
		});

		for (let index = conferenceIndex; index < conferenceList.length; index++)
		{
			conferenceIndex++;

			const target = DesktopApi.findWindow(conferenceList[index]);
			if (target)
			{
				DesktopApi.activateWindow(target);
				break;
			}
		}

		return true;
	},
};
