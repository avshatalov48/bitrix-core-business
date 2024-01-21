export const debugFunctions = {
	openDeveloperTools(): boolean
	{
		BXDesktopWindow?.OpenDeveloperTools();
	},
	openLogsFolder()
	{
		BXDesktopSystem?.OpenLogsFolder();
	},
};
