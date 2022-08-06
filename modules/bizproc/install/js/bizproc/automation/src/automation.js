import TemplatesScheme from './templates-scheme';
export { TemplateScope } from './template-scope';
export { TriggerManager } from './trigger-manager';
export { Trigger } from './trigger';
export { Template } from './template';
export { Robot } from './robot';
export { UserOptions } from './user-options';
export { Document } from './document/document';
export { ViewMode } from './view-mode';
export { ConditionGroup } from './condition/condition-group';
export { ConditionGroupSelector } from './condition/condition-group-selector';
export { Condition } from './condition/condition';
export { Designer } from './designer';
export * from './tracker/tracker';
export { DelayInterval } from './delay-interval';
export { DelayIntervalSelector } from './delay-interval-selector';
export { HelpHint } from './help-hint';
import { AutomationContext } from './context/automation-context';

import 'ui.fonts.opensans';

export { Helper } from './helper';

import { Reflection } from "main.core";
import './css/style.css'

export {
	TemplatesScheme,
	AutomationContext,
}

export function getGlobalContext(): AutomationContext
{
	Reflection.namespace('BX.Bizproc.Automation');

	const context = BX.Bizproc.Automation.Context;
	if (context instanceof AutomationContext)
	{
		return context;
	}

	throw new Error('Context is not initialized yet');
}

export function tryGetGlobalContext(): ?AutomationContext
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

export function setGlobalContext(context: AutomationContext): AutomationContext
{
	Reflection.namespace('BX.Bizproc.Automation');

	BX.Bizproc.Automation.Context = context;

	return context;
}