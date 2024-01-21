import { Type, Browser, Loc } from 'main.core';
import { Logger } from 'im.v2.lib.logger';

export const loggerFunctions = {
	writeToLogFile(filename: string, text: any)
	{
		if (!Type.isStringFilled(filename))
		{
			console.error('Desktop logger: filename is not defined');
			return;
		}

		let textPrepared = '';
		if (Type.isString(text))
		{
			textPrepared = text;
		}
		else if (Type.isNumber(text))
		{
			textPrepared = text.toString();
		}
		else
		{
			textPrepared = JSON.stringify(text);
		}

		BXDesktopSystem?.Log(filename, textPrepared);
	},
	printWelcomePrompt()
	{
		const version = BXDesktopSystem.GetProperty('versionParts').join('.');
		let osName = 'unknown';
		if (Browser.isMac())
		{
			osName = 'MacOS';
		}
		else if (Browser.isWin())
		{
			osName = 'Windows';
		}
		else if (Browser.isLinux())
		{
			osName = 'Linux';
		}

		const promptMessage = Loc.getMessage('IM_LIB_DESKTOP_API_WELCOME_PROMPT', { '#VERSION#': version, '#OS#': osName });
		Logger.desktop(promptMessage);
	},
	setLogInfo(logFunction: Function)
	{
		BXDesktopSystem.LogInfo = logFunction;
	},
};
