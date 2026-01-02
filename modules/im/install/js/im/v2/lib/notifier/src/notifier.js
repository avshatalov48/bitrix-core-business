import { Loc } from 'main.core';

import { ChatNotifier } from './functions/chat';
import { MessageNotifier } from './functions/message';
import { CollabNotifier } from './functions/collab';
import { FileNotifier } from './functions/file';
import { InviteNotifier } from './functions/invite';
import { ConferenceNotifier } from './functions/conference';
import { SupportNotifier } from './functions/support';
import { SpeechNotifier } from './functions/speech';
import { CallNotifier } from './functions/call';
import { RecentNotifier } from './functions/recent';

import { showNotification } from './utils/notification';

export const Notifier = {
	chat: ChatNotifier,
	message: MessageNotifier,
	collab: CollabNotifier,
	file: FileNotifier,
	invite: InviteNotifier,
	conference: ConferenceNotifier,
	support: SupportNotifier,
	speech: SpeechNotifier,
	call: CallNotifier,
	recent: RecentNotifier,

	onCopyTextComplete(): void
	{
		showNotification(Loc.getMessage('IM_NOTIFIER_TEXT_COPY_COMPLETE'));
	},

	onCopyLinkComplete(): void
	{
		showNotification(Loc.getMessage('IM_NOTIFIER_LINK_COPY_COMPLETE'));
	},

	onDefaultError(): void
	{
		showNotification(Loc.getMessage('IM_NOTIFIER_DEFAULT_ERROR'));
	},

	onCopyLinkError(): void
	{
		showNotification(Loc.getMessage('IM_NOTIFIER_LINK_COPY_ERROR'));
	},
};
