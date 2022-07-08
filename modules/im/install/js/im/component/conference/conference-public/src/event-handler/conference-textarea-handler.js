import { TextareaHandler } from "im.event-handler";

export class ConferenceTextareaHandler extends TextareaHandler
{
	application: Object = null;

	constructor($Bitrix)
	{
		super($Bitrix);
		this.application = $Bitrix.Application.get();
	}

	onAppButtonClick({data: event})
	{
		if (event.appId === 'smile')
		{
			this.application.toggleSmiles();
		}
	}
}