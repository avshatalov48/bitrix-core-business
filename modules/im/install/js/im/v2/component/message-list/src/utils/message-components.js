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
import { ChatCopilotAddedUsersMessage } from 'im.v2.component.message.copilot.added-users';
import { SupportVoteMessage } from 'im.v2.component.message.support.vote';
import { SupportSessionNumberMessage } from 'im.v2.component.message.support.session-number';
import { SupportChatCreationMessage } from 'im.v2.component.message.support.chat-creation';
import { ConferenceCreationMessage } from 'im.v2.component.message.conference-creation';
import { SupervisorUpdateFeatureMessage } from 'im.v2.component.message.supervisor.update-feature';
import { SupervisorEnableFeatureMessage } from 'im.v2.component.message.supervisor.enable-feature';
import { SignMessage } from 'im.v2.component.message.sign';
import { CheckInMessage } from 'im.v2.component.message.check-in';
import { OwnChatCreationMessage } from 'im.v2.component.message.own-chat-creation';
import { ZoomInviteMessage } from 'im.v2.component.message.zoom-invite';
import { GeneralChatCreationMessage } from 'im.v2.component.message.general-chat-creation';
import { GeneralChannelCreationMessage } from 'im.v2.component.message.general-channel-creation';
import { ChannelCreationMessage } from 'im.v2.component.message.channel-creation';
import { StartDialogMessage } from 'imopenlines.v2.component.message.start-dialog';
import { HiddenMessage } from 'imopenlines.v2.component.message.hidden';
import { FeedbackFormMessage } from 'imopenlines.v2.component.message.feedback-form';
import { CallMessage } from 'im.v2.component.message.call';
import { VoteMessage } from 'im.v2.component.message.vote';

export const MessageComponents = {
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
};
