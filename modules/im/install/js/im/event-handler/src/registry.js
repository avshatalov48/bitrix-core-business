import {SendMessageHandler} from "./send-message-handler";
import {ReadingHandler} from "./reading-handler";
import {ReactionHandler} from "./reaction-handler";
import {QuoteHandler} from "./quote-handler";
import {TextareaHandler} from "./textarea-handler";
import {TextareaUploadHandler} from "./textarea-upload-handler";
import {TextareaDragHandler} from "./textarea-drag-handler";
import {DialogActionHandler} from "./dialog-action-handler";
import {Reflection} from 'main.core';

export {
	TextareaHandler, TextareaDragHandler, TextareaUploadHandler,
	SendMessageHandler, ReadingHandler, ReactionHandler, QuoteHandler,
	DialogActionHandler
};

// fix for compatible with mobile, bug #169468
const namespace = Reflection.getClass('BX.Messenger');
if (namespace)
{
	namespace.ReadingHandler = ReadingHandler;
	namespace.ReactionHandler = ReactionHandler;
	namespace.QuoteHandler = QuoteHandler;
}