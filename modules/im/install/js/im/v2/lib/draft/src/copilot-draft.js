import { Layout } from 'im.v2.const';

import { DraftManager } from './draft';

const STORAGE_KEY = 'copilotDraft';

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

	getStorageKey(): string
	{
		return STORAGE_KEY;
	}

	getDraftMethodName(): string
	{
		return 'recent/setCopilotDraft';
	}
}
