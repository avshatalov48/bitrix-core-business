// screensharing workaround
export function applyHacks() {
	if (window["BXDesktopSystem"])
	{
		navigator['getDisplayMedia'] = function ()
		{
			var mediaParams = {
				audio: false,
				video: {
					mandatory: {
						chromeMediaSource: 'desktop',
						maxWidth: screen.width > 1920 ? screen.width : 1920,
						maxHeight: screen.height > 1080 ? screen.height : 1080,
					},
					optional: [{googTemporalLayeredScreencast: true}],
				},
			};
			return navigator.mediaDevices.getUserMedia(mediaParams);
		};
	}
}
