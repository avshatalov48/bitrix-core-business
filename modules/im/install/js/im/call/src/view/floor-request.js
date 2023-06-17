import {Dom} from 'main.core';
import {EventEmitter} from 'main.core.events';

export class FloorRequest extends EventEmitter
{
	constructor(config)
	{
		super();
		this.setEventNamespace("BX.Call.FloorRequest");

		this.hideTime = BX.prop.getInteger(config, "hideTime", 10);
		this.userModel = config.userModel;

		this.elements = {
			root: null,
			avatar: null
		};

		this._hideTimeout = null;
		this._onUserModelChangedHandler = this._onUserModelChanged.bind(this);
		this.userModel.subscribe("changed", this._onUserModelChangedHandler);
	};

	static create(config)
	{
		return new FloorRequest(config);
	};

	mount(container)
	{
		container.appendChild(this.render());
		this.scheduleDismount();
	};

	dismount()
	{
		BX.remove(this.elements.root);
		this.destroy();
	};

	dismountWithAnimation()
	{
		if (!this.elements.root)
		{
			return;
		}
		this.elements.root.classList.add("closing");

		this.elements.root.addEventListener("animationend", () => this.dismount());
	};

	render()
	{
		if (this.elements.root)
		{
			return this.elements.root;
		}

		this.elements.root = Dom.create("div", {
			props: {className: "bx-call-view-floor-request-notification"},
			children: [
				Dom.create("div", {
					props: {className: "bx-call-view-floor-request-notification-icon-container"},
					children: [
						this.elements.avatar = Dom.create("div", {
							props: {className: "bx-call-view-floor-request-notification-avatar"}
						}),
						Dom.create("div", {
							props: {className: "bx-call-view-floor-request-notification-icon bx-messenger-videocall-floor-request-icon"}
						}),
					]
				}),
				Dom.create("span", {
					props: {className: "bx-call-view-floor-request-notification-text-container"},
					html: BX.message("IM_CALL_WANTS_TO_SAY_" + (this.userModel.gender == "F" ? "F" : "M")).replace("#NAME#", '<span class ="bx-call-view-floor-request-notification-text-name">' + BX.util.htmlspecialchars(this.userModel.name) + '</span>')
				}),
				Dom.create("div", {
					props: {className: "bx-call-view-floor-request-notification-close"},
					events: {
						click: this.dismount.bind(this)
					}
				})
			]
		});

		if (this.userModel.avatar)
		{
			this.elements.avatar.style.setProperty("--avatar", "url('" + this.userModel.avatar + "')");
		}

		return this.elements.root
	};

	scheduleDismount()
	{
		return;
		this._hideTimeout = setTimeout(this.dismountWithAnimation.bind(this), this.hideTime * 1000);
	};

	_onUserModelChanged(event)
	{
		var eventData = event.data;

		if (eventData.fieldName == "floorRequestState" && !this.userModel.floorRequestState)
		{
			this.dismountWithAnimation();
		}
	};

	destroy()
	{
		clearTimeout(this._hideTimeout);
		this._hideTimeout = null;
		this.elements = null;
		if (this.userModel)
		{
			this.userModel.unsubscribe("changed", this._onUserModelChangedHandler);
			this.userModel = null;
		}
		this.emit("onDestroy", {});
	}
}