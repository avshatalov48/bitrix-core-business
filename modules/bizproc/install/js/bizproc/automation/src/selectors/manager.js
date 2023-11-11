import {
	Designer,
	FileSelector,
	InlineSelector,
	InlineSelectorHtml,
	SaveStateCheckbox,
	TimeSelector,
	UserSelector,
} from 'bizproc.automation';
import { Type } from 'main.core';
import { InlineTimeSelector } from './inline-time-selector';

export class Manager
{
	static SELECTOR_ROLE_USER = 'user-selector';
	static SELECTOR_ROLE_FILE = 'file-selector';
	static SELECTOR_ROLE_INLINE = 'inline-selector-target';
	static SELECTOR_ROLE_INLINE_HTML = 'inline-selector-html';
	static SELECTOR_ROLE_TIME = 'time-selector';
	static SELECTOR_ROLE_SAVE_STATE = 'save-state-checkbox';
	static SELECTOR_ROLE_INLINE_TIME = 'inline-selector-time';

	static getSelectorByTarget(targetInput: HTMLElement): ?InlineSelector
	{
		// TODO - save created selectors with Manager
		const template = Designer.getInstance().getRobotSettingsDialog()?.template;

		if (template && Type.isArray(template.robotSettingsControls))
		{
			return template.robotSettingsControls.find(selector => selector.targetInput === targetInput);
		}

		return undefined;
	}

	static createSelectorByRole(role: string, selectorProps: object): InlineSelector | SaveStateCheckbox
	{
		if (role === this.SELECTOR_ROLE_USER)
		{
			return new UserSelector(selectorProps);
		}
		else if (role === this.SELECTOR_ROLE_FILE)
		{
			return new FileSelector(selectorProps);
		}
		else if (role === this.SELECTOR_ROLE_INLINE)
		{
			return new InlineSelector(selectorProps);
		}
		else if (role === this.SELECTOR_ROLE_INLINE_HTML)
		{
			return new InlineSelectorHtml(selectorProps);
		}
		else if (role === this.SELECTOR_ROLE_INLINE_TIME)
		{
			return new InlineTimeSelector(selectorProps);
		}
		else if (role === this.SELECTOR_ROLE_TIME)
		{
			return new TimeSelector(selectorProps);
		}
		else if (role === this.SELECTOR_ROLE_SAVE_STATE)
		{
			return new SaveStateCheckbox(selectorProps);
		}
		else
		{
			return undefined;
		}
	}
}
