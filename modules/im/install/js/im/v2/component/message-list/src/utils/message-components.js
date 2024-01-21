import { FileMessage } from 'im.v2.component.message.file';
import { DefaultMessage } from 'im.v2.component.message.default';
import { CallInviteMessage } from 'im.v2.component.message.call-invite';
import { DeletedMessage } from 'im.v2.component.message.deleted';
import { UnsupportedMessage } from 'im.v2.component.message.unsupported';
import { SmileMessage } from 'im.v2.component.message.smile';
import { SystemMessage } from 'im.v2.component.message.system';
import { ChatCreationMessage } from 'im.v2.component.message.chat-creation';
import { ChatCopilotCreationMessage } from 'im.v2.component.message.copilot.creation';
import { CopilotMessage } from 'im.v2.component.message.copilot.answer';
import { SupportVoteMessage } from 'im.v2.component.message.support.vote';
import { SupportSessionNumberMessage } from 'im.v2.component.message.support.session-number';
import { ConferenceCreationMessage } from 'im.v2.component.message.conference-creation';
import { OwnChatCreationMessage } from 'im.v2.component.message.own-chat-creation';

export const messageComponents = {
	DefaultMessage,
	FileMessage,
	SmileMessage,
	CallInviteMessage,
	DeletedMessage,
	SystemMessage,
	UnsupportedMessage,
	ChatCreationMessage,
	OwnChatCreationMessage,
	ChatCopilotCreationMessage,
	CopilotMessage,
	SupportVoteMessage,
	SupportSessionNumberMessage,
	ConferenceCreationMessage,
};
