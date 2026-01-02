import { FileMessage } from 'im.v2.component.message.file';
import { DefaultMessage } from 'im.v2.component.message.default';
import { ErrorMessage } from 'im.v2.component.message.error';
import { CallInviteMessage } from 'im.v2.component.message.call-invite';
import { DeletedMessage } from 'im.v2.component.message.deleted';
import { UnsupportedMessage } from 'im.v2.component.message.unsupported';
import { SmileMessage } from 'im.v2.component.message.smile';
import { SystemMessage } from 'im.v2.component.message.system';
import { ChatCreationMessage } from 'im.v2.component.message.creation.chat';
import { ChatCopilotCreationMessage } from 'im.v2.component.message.copilot.creation';
import { CopilotMessage } from 'im.v2.component.message.copilot.answer';
import { ChatCopilotAddedUsersMessage } from 'im.v2.component.message.copilot.added-users';
import { SupportVoteMessage } from 'im.v2.component.message.support.vote';
import { SupportSessionNumberMessage } from 'im.v2.component.message.support.session-number';
import { SupportChatCreationMessage } from 'im.v2.component.message.support.chat-creation';
import { ConferenceCreationMessage } from 'im.v2.component.message.creation.conference';
import { SupervisorUpdateFeatureMessage } from 'im.v2.component.message.supervisor.update-feature';
import { SupervisorEnableFeatureMessage } from 'im.v2.component.message.supervisor.enable-feature';
import { SignMessage } from 'im.v2.component.message.sign';
import { CheckInMessage } from 'im.v2.component.message.check-in';
import { OwnChatCreationMessage } from 'im.v2.component.message.creation.own-chat';
import { ZoomInviteMessage } from 'im.v2.component.message.zoom-invite';
import { GeneralChatCreationMessage } from 'im.v2.component.message.creation.general-chat';
import { GeneralChannelCreationMessage } from 'im.v2.component.message.creation.general-channel';
import { ChannelCreationMessage } from 'im.v2.component.message.creation.channel';
import { StartDialogMessage } from 'imopenlines.v2.component.message.start-dialog';
import { HiddenMessage } from 'imopenlines.v2.component.message.hidden';
import { FeedbackFormMessage } from 'imopenlines.v2.component.message.feedback-form';
import { CallMessage } from 'im.v2.component.message.call';
import { VoteMessage } from 'im.v2.component.message.vote';
import { TaskChatCreationMessage } from 'im.v2.component.message.creation.task-chat';
import { ConvertToCollabMessage } from 'im.v2.component.message.collab.convert';
import { AiAssistantMessage } from 'im.v2.component.message.ai-assistant.answer';

export const MessageComponents = {
	DefaultMessage,
	FileMessage,
	SmileMessage,
	ErrorMessage,
	CallInviteMessage,
	DeletedMessage,
	SystemMessage,
	UnsupportedMessage,
	ChatCreationMessage,
	OwnChatCreationMessage,
	ChatCopilotCreationMessage,
	CopilotMessage,
	AiAssistantMessage,
	SupportVoteMessage,
	SupportSessionNumberMessage,
	SupportChatCreationMessage,
	ConferenceCreationMessage,
	ZoomInviteMessage,
	CheckInMessage,
	SupervisorUpdateFeatureMessage,
	SupervisorEnableFeatureMessage,
	ChatCopilotAddedUsersMessage,
	SignMessage,
	GeneralChatCreationMessage,
	GeneralChannelCreationMessage,
	ChannelCreationMessage,
	CallMessage,
	StartDialogMessage,
	FeedbackFormMessage,
	HiddenMessage,
	VoteMessage,
	TaskChatCreationMessage,
	ConvertToCollabMessage,
};
