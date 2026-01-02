import { SidebarMainPanelBlock } from 'im.v2.const';

import { InfoPreview } from '../blocks/info';
import { FileListPreview } from '../blocks/file-list';
import { TaskListPreview } from '../blocks/task-list';
import { MarketAppListPreview } from '../blocks/market-app-list';
import { MeetingListPreview } from '../blocks/meeting-list';
import { CopilotInfoPreview } from '../blocks/copilot-info';
import { ChatPreview } from '../blocks/chat';
import { PostPreview } from '../blocks/post';
import { UserPreview } from '../blocks/user';
import { CopilotPreview } from '../blocks/copilot';
import { SupportPreview } from '../blocks/support';
import { FileUnsortedListPreview } from '../blocks/file-unsorted-list';
import { MultidialogPreview } from '../blocks/multidialog';
import { TariffLimitPreview } from '../blocks/tariff-limit';
import { CollabHelpdeskPreview } from '../blocks/collab-helpdesk';
import { NotesPreview } from '../blocks/notes';
import { TaskPreview } from '../blocks/task';

export const ComponentMap = {
	[SidebarMainPanelBlock.chat]: ChatPreview,
	[SidebarMainPanelBlock.notes]: NotesPreview,
	[SidebarMainPanelBlock.post]: PostPreview,
	[SidebarMainPanelBlock.user]: UserPreview,
	[SidebarMainPanelBlock.support]: SupportPreview,
	[SidebarMainPanelBlock.info]: InfoPreview,
	[SidebarMainPanelBlock.fileList]: FileListPreview,
	[SidebarMainPanelBlock.task]: TaskPreview,
	[SidebarMainPanelBlock.taskList]: TaskListPreview,
	[SidebarMainPanelBlock.meetingList]: MeetingListPreview,
	[SidebarMainPanelBlock.fileUnsortedList]: FileUnsortedListPreview,
	[SidebarMainPanelBlock.marketAppList]: MarketAppListPreview,
	[SidebarMainPanelBlock.multidialog]: MultidialogPreview,
	[SidebarMainPanelBlock.copilot]: CopilotPreview,
	[SidebarMainPanelBlock.copilotInfo]: CopilotInfoPreview,
	[SidebarMainPanelBlock.tariffLimit]: TariffLimitPreview,
	[SidebarMainPanelBlock.collabHelpdesk]: CollabHelpdeskPreview,
};
