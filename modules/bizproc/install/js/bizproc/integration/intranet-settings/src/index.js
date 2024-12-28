import { EventEmitter } from 'main.core.events';
import { AutomationPage } from './automation-page';

EventEmitter.subscribe(
	EventEmitter.GLOBAL_TARGET,
	'BX.Intranet.Settings:onExternalPageLoaded:automation',
	() => new AutomationPage(),
);
