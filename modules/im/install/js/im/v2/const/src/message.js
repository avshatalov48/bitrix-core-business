import { OpenLinesMessageComponent } from 'imopenlines.v2.const';

export const MessageType = Object.freeze({
	self: 'self',
	opponent: 'opponent',
	system: 'system',
});

export const MessageComponent = Object.freeze({
	default: 'DefaultMessage',
	file: 'FileMessage',
	smile: 'SmileMessage',
	unsupported: 'UnsupportedMessage',
	deleted: 'DeletedMessage',
	callInvite: 'CallInviteMessage',
	zoomInvite: 'ZoomInviteMessage',
	chatCreation: 'ChatCreationMessage',
	ownChatCreation: 'OwnChatCreationMessage',
	copilotCreation: 'ChatCopilotCreationMessage',
	copilotMessage: 'CopilotMessage',
	copilotAddedUsers: 'ChatCopilotAddedUsersMessage',
	conferenceCreation: 'ConferenceCreationMessage',
	supervisorUpdateFeature: 'SupervisorUpdateFeatureMessage',
	supervisorEnableFeature: 'SupervisorEnableFeatureMessage',
	sign: 'SignMessage',
	checkIn: 'CheckInMessage',
	supportVote: 'SupportVoteMessage',
	supportSessionNumber: 'SupportSessionNumberMessage',
	supportChatCreation: 'SupportChatCreationMessage',
	system: 'SystemMessage',
	channelPost: 'ChannelPost',
	generalChatCreationMessage: 'GeneralChatCreationMessage',
	generalChannelCreationMessage: 'GeneralChannelCreationMessage',
	channelCreationMessage: 'ChannelCreationMessage',
	callMessage: 'CallMessage',
	voteMessage: 'VoteMessage',
	...OpenLinesMessageComponent,
});

export const MessageMentionType = Object.freeze({
	user: 'USER',
	chat: 'CHAT',
	lines: 'LINES',
	context: 'CONTEXT',
	call: 'CALL',
});

export const MessageStatus = {
	received: 'received',
	delivered: 'delivered',
	error: 'error',
};

export const OwnMessageStatus = Object.freeze({
	sending: 'sending',
	sent: 'sent',
	viewed: 'viewed',
	error: 'error',
});

export const FakeMessagePrefix = 'temp';
export const FakeDraftMessagePrefix = 'temp-draft';
