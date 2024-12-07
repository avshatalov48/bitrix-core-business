import { BitrixVue } from 'ui.vue3';
import { Application } from './components/application';

export class Public
{
	constructor(options = {})
	{
		this.owner = options.owner;
		this.sharingUser = options.sharingUser;
		this.link = options.link;
		this.calendarSettings = options.calendarSettings;
		this.userAccessibility = options.userAccessibility;
		this.timezoneList = options.timezoneList;
		this.welcomePageVisited = options.welcomePageVisited;
		this.rootNode = BX('calendar-sharing-main');

		this.buildViews();
	}

	buildViews()
	{
		this.application = BitrixVue.createApp(Application, {
			link: this.link,
			owner: this.owner,
			sharingUser: this.sharingUser,
			calendarSettings: this.calendarSettings,
			userAccessibility: this.userAccessibility,
			timezoneList: this.timezoneList,
			welcomePageVisited: this.welcomePageVisited,
		}).mount(this.rootNode);
	}
}