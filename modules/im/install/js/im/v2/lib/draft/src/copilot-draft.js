import { Layout, LocalStorageKey } from 'im.v2.const';
import { DraftManager } from './draft';

export class CopilotDraftManager extends DraftManager
{
	static instance: CopilotDraftManager = null;

	static getInstance(): CopilotDraftManager
	{
		if (!CopilotDraftManager.instance)
		{
			CopilotDraftManager.instance = new CopilotDraftManager();
		}

		return CopilotDraftManager.instance;
	}

	getLayoutName(): string
	{
		return Layout.copilot.name;
	}

	getLocalStorageKey(): string
	{
		return LocalStorageKey.copilotDraft;
	}

	getDraftMethodName(): string
	{
		return 'recent/setCopilotDraft';
	}
}
