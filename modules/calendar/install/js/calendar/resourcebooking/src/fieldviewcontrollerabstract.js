import {Type, Loc, Dom, Event} from "./resourcebooking";
import {UserSelector} from "./controls/userselector";
import {ResourceSelector} from "./controls/resourceselector";
import {ServiceSelector} from "./controls/serviceselector";
import {DurationSelector} from "./controls/durationselector";
import {DateSelector} from "./controls/dateselector";
import {TimeSelector} from "./controls/timeselector";

export class FieldViewControllerAbstract extends Event.EventEmitter
{
	constructor(params)
	{
		super(params);
		this.settings = params.settings || {};
		this.showTitle = params.displayTitle !== false;
		this.title = params.title || '';
		this.DOM = {
			wrap: params.wrap // outer wrap of the form
		};
	}

	build()
	{
		this.controls = {};
		// inner wrap
		this.DOM.outerWrap = this.DOM.wrap.appendChild(Dom.create("div", {props : { className : 'calendar-resbook-webform-wrapper calendar-resbook-webform-wrapper-form'}}));
		this.DOM.innerWrap = this.DOM.outerWrap.appendChild(Dom.create("div", {props : { className : 'calendar-resbook-webform-inner'}}));

		if (this.settings.userfieldSettings.useUsers || this.settings.userfieldSettings.useResources)
		{
			this.displayTitle();
			this.displayUsersControl();
			this.displayResourcesControl();
			this.displayServicesControl();
			this.displayDurationControl();
			this.displayDateControl();
			this.displayTimeControl();
		}
		else
		{
			this.displayWarning(Loc.getMessage('WEBF_RES_BOOKING_WARNING'));
		}
	}

	destroy()
	{
		Dom.remove(this.DOM.outerWrap);
	}

	displayTitle()
	{
		if (this.showTitle)
		{
			this.DOM.titleWrap = this.DOM.innerWrap
				.appendChild(Dom.create("div", {props:{className:'calendar-resbook-webform-title'}}))
				.appendChild(Dom.create("div", {props:{className:'calendar-resbook-webform-title-text'}}));
			this.updateTitle(this.title);
		}
	}

	updateTitle(title)
	{
		if (this.showTitle)
		{
			this.title = title;
			Dom.adjust(this.DOM.titleWrap, {text: this.title});
		}
	}

	displayWarning(message)
	{
		this.DOM.warningWrap = this.DOM.innerWrap
			.appendChild(Dom.create("div", {
				props:{className:'ui-alert ui-alert-warning ui-alert-text-center ui-alert-icon-warning'},
				style: {marginBottom: 0},
				html: '<span class="ui-alert-message">' + message + '</span>'
			}));
	}

	displayUsersControl()
	{
		if (this.settings.userfieldSettings.useUsers)
		{
			if (this.settings.data.users.value === null
				&& Type.isArray(this.settings.userfieldSettings.users))
			{
				this.settings.data.users.value = this.settings.userfieldSettings.users;
			}

			this.controls.users = new UserSelector({
				outerWrap: this.DOM.innerWrap,
				data: this.settings.data.users,
				userIndex: this.settings.userfieldSettings.userIndex
			});
			this.controls.users.display();
		}
	}

	displayResourcesControl()
	{
		if (this.settings.userfieldSettings.useResources)
		{
			if (this.settings.data.resources.value === null
				&& Type.isArray(this.settings.userfieldSettings.resources))
			{
				this.settings.data.resources.value = [];
				this.settings.userfieldSettings.resources.forEach(function(res)
				{
					this.settings.data.resources.value.push(parseInt(res.id));
				}, this);
			}

			this.controls.resources = new ResourceSelector({
				outerWrap: this.DOM.innerWrap,
				data: this.settings.data.resources,
				resourceList: this.settings.userfieldSettings.resources
			});
			this.controls.resources.display();
		}
	}

	displayServicesControl()
	{
		if (this.settings.userfieldSettings.useServices)
		{
			if (this.settings.data.services.value === null
				&& Type.isArray(this.settings.userfieldSettings.services))
			{
				this.settings.data.services.value = [];
				this.settings.userfieldSettings.services.forEach(function(serv)
				{
					this.settings.data.services.value.push(serv.name);
				}, this);
			}

			this.controls.services = new ServiceSelector({
				outerWrap: this.DOM.innerWrap,
				data: this.settings.data.services,
				serviceList: this.settings.userfieldSettings.services
			});
			this.controls.services.display();
		}
	}

	displayDurationControl()
	{
		if (!this.settings.userfieldSettings.useServices)
		{
			this.controls.duration = new DurationSelector({
				outerWrap: this.DOM.innerWrap,
				data: this.settings.data.duration,
				fullDay: this.settings.userfieldSettings.fullDay
			});
			this.controls.duration.display();
		}
	}

	displayDateControl()
	{
		this.controls.date = new DateSelector({
			outerWrap: this.DOM.innerWrap,
			data: this.settings.data.date
		});
		this.controls.date.display();
	}

	displayTimeControl()
	{
		if (!this.settings.userfieldSettings.fullDay)
		{
			this.controls.time = new TimeSelector({
				outerWrap: this.DOM.innerWrap,
				data: this.settings.data.time
			});
			this.controls.time.display();
		}
	}

	refreshLayout(settingsData)
	{
		for (let k in this.controls)
		{
			if (this.controls.hasOwnProperty(k) && Type.isFunction(this.controls[k].refresh))
			{
				this.controls[k].refresh(settingsData[k] || this.settings.data[k]);
			}
		}
	}

	getInnerWrap()
	{
		return this.DOM.innerWrap;
	}

	getOuterWrap()
	{
		return this.DOM.outerWrap;
	}
}






