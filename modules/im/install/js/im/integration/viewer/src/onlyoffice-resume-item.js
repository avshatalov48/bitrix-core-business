import {Reflection} from 'main.core';
import OnlyOfficeChatItem from "./onlyoffice-chat-item";

export default class OnlyOfficeResumeItem extends OnlyOfficeChatItem
{
	loadData ()
	{
		/** @see BXIM.callController.currentCall */
		if (!Reflection.getClass('BXIM.callController.currentCall'))
		{
			return super.loadData();
		}

		const messageId = BX.MessengerCommon.diskGetMessageId(this.chatId, this.objectId);
		if (!messageId)
		{
			return super.loadData();
		}

		const callId = BX.MessengerCommon.getMessageParam(messageId, 'CALL_ID');
		const callController = BXIM.callController;
		if (!callId)
		{
			return super.loadData();
		}

		if (callId != callController.currentCall.id)
		{
			return super.loadData();
		}
		else
		{
			callController.unfold();
			callController.showDocumentEditor({
				type: BX.Call.Controller.DocumentType.Resume,
				force: true,
			});
		}

		return new BX.Promise();
	}
}