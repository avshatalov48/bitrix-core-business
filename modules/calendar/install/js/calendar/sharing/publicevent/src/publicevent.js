import {BitrixVue} from 'ui.vue3';
import {Application} from './components/application';
import { EventInfo } from './components/eventinfo';

class PublicEvent
{
	constructor(options)
	{
		this.link = options.link;
		this.event = options.event;
		this.owner = options.owner;
		this.ownerMeetingStatus = options.ownerMeetingStatus;
		this.action = options.action;
		this.rootNode = BX('calendar-sharing-event-main');
		this.buildView();
	}

	buildView()
	{
		this.application = BitrixVue.createApp(Application, {
			link: this.link,
			event: this.event,
			owner: this.owner,
			ownerMeetingStatus: this.ownerMeetingStatus,
			action: this.action,
		}).mount(this.rootNode);
	}
}

export {
	PublicEvent,
	EventInfo,
};