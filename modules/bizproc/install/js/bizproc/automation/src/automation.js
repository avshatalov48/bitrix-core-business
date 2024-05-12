import TemplatesScheme from './templates-scheme';
import { Context } from './context/context';

export { TemplateScope } from './template-scope';
export { TriggerManager } from './trigger-manager';
export { Trigger } from './trigger';
export { Template } from './template';
export { Robot } from './robot';
export { UserOptions } from './user-options';
export { Document } from './document/document';
export { ViewMode } from './view-mode';
export { ConditionGroup } from './condition/condition-group';
export { ConditionGroupSelector } from './selectors/condition-group-selector';
export { Condition } from './condition/condition';
export { Designer } from './designer';
export * from './tracker/tracker';
export * from './workflow/types';
export { Manager as SelectorManager } from './selectors/manager';
export { InlineSelector } from './selectors/inline-selector';
export { InlineSelectorCondition } from './selectors/inline-selector-condition';
export { InlineSelectorHtml } from './selectors/inline-selector-html';
export { SaveStateCheckbox } from './selectors/save-state-checkbox';
export { UserSelector } from './selectors/user-selector';
export { FileSelector } from './selectors/file-selector';
export { TimeSelector } from './selectors/time-selector';
export { DelayInterval } from './delay-interval';
export { DelayIntervalSelector } from './delay-interval-selector';
export { HelpHint } from './help-hint';
export { SelectorContext } from './context/selector-context';
export { AutomationGlobals } from './automation-globals';
export { Statuses } from './statuses';
export { SelectorItemsManager } from './selectors/group/manager';

import enrichFieldsWithModifiers from './selectors/enrich-fields-with-modifiers';

export { Helper } from './helper';

import 'ui.design-tokens';
import 'ui.fonts.opensans';
import './css/style.css';

export {
	TemplatesScheme,
	Context,
	enrichFieldsWithModifiers,
};

export { BeginningGuide } from './tourguide/beginning-guide';
export { AutomationGuide } from './tourguide/automation-guide';

let contextInstance: ?Context;

export function getGlobalContext(): Context
{
	if (contextInstance instanceof Context)
	{
		return contextInstance;
	}

	throw new Error('Context is not initialized yet');
}

export function tryGetGlobalContext(): ?Context
{
	try
	{
		return getGlobalContext();
	}
	catch (error)
	{
		return null;
	}
}

export function setGlobalContext(context: Context): Context
{
	if (context instanceof Context)
	{
		contextInstance = context;
	}
	else
	{
		throw new Error('Unsupported Context');
	}

	return context;
}
