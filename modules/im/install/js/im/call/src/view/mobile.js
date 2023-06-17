import {Dom} from 'main.core';
import {createSVG} from './svg'
import {UserState} from '../engine/engine';

export class CallUserMobile
{
	constructor(config)
	{
		this.userModel = config.userModel;

		this.elements = {
			root: null,
			avatar: null,
			avatarOutline: null,
			userName: null,
			userStatus: null,
			menuArrow: null,
			floorRequest: null,
			mic: null,
			cam: null,
		};

		this._onUserFieldChangeHandler = this._onUserFieldChange.bind(this);
		this.userModel.subscribe("changed", this._onUserFieldChangeHandler);

		this.callbacks = {
			onClick: BX.prop.getFunction(config, "onClick", BX.DoNothing)
		}
	};

	render()
	{
		if (this.elements.root)
		{
			return this.elements.root;
		}

		this.elements.root = Dom.create("div", {
			props: {
				className: "bx-messenger-videocall-user-mobile"
			},
			children: [
				this.elements.avatar = Dom.create("div", {
					props: {
						className: "bx-messenger-videocall-user-mobile-avatar" + (this.userModel.talking ? " talking" : "")
					},
					children: [
						this.elements.floorRequest = Dom.create("div", {
							props: {
								className: "bx-messenger-videocall-user-mobile-floor-request bx-messenger-videocall-floor-request-icon"
							}
						})
					]
				}),
				Dom.create("div", {
					props: {
						className: "bx-messenger-videocall-user-mobile-body"
					},
					children: [
						Dom.create("div", {
							props: {
								className: "bx-messenger-videocall-user-mobile-text"
							},
							children: [
								this.elements.mic = Dom.create("div", {
									props: {
										className: "bx-messenger-videocall-user-mobile-icon" + (this.userModel.microphoneState ? "" : " bx-call-view-icon-red-microphone-off")
									}
								}),
								this.elements.cam = Dom.create("div", {
									props: {
										className: "bx-messenger-videocall-user-mobile-icon" + (this.userModel.cameraState ? "" : " bx-call-view-icon-red-camera-off")
									}
								}),
								this.elements.userName = Dom.create("div", {
									props: {
										className: "bx-messenger-videocall-user-mobile-username"
									},
									text: this.userModel.name
								}),
								Dom.create("div", {
									props: {
										className: "bx-messenger-videocall-user-mobile-menu-arrow"
									}
								})
							]
						}),
						this.elements.userStatus = Dom.create("div", {
							props: {
								className: "bx-messenger-videocall-user-mobile-user-status"
							},
							text: this.userModel.pinned ? BX.message("IM_M_CALL_PINNED_USER") : BX.message("IM_M_CALL_CURRENT_PRESENTER")
						})
					]
				}),

			],
			events: {
				click: this.callbacks.onClick
			}
		});

		return this.elements.root;
	};

	update()
	{
		if (!this.elements.root)
		{
			return;
		}

		this.elements.userName.innerText = this.userModel.name;

		if (this.userModel.avatar !== '')
		{
			this.elements.root.style.setProperty("--avatar", "url('" + this.userModel.avatar + "')");
		}
		else
		{
			this.elements.root.style.removeProperty("--avatar");
		}
		this.elements.avatar.classList.toggle("talking", this.userModel.talking);
		this.elements.floorRequest.classList.toggle("active", this.userModel.floorRequestState);
		this.elements.mic.classList.toggle("bx-call-view-icon-red-microphone-off", !this.userModel.microphoneState);
		this.elements.cam.classList.toggle("bx-call-view-icon-red-camera-off", !this.userModel.cameraState);

		this.elements.userStatus.innerText = this.userModel.pinned ? BX.message("IM_M_CALL_PINNED_USER") : BX.message("IM_M_CALL_CURRENT_PRESENTER");
	};

	mount(parentElement)
	{
		parentElement.appendChild(this.render());
	};

	dismount()
	{
		if (!this.elements.root)
		{
			return;
		}
		Dom.remove(this.elements.root);
	};

	setUserModel(userModel: UserModel)
	{
		this.userModel.unsubscribe("changed", this._onUserFieldChangeHandler);
		this.userModel = userModel;
		this.userModel.subscribe("changed", this._onUserFieldChangeHandler);
		this.update();
	};

	_onUserFieldChange(event)
	{
		this.update();
	};
}

export class UserSelectorMobile
{
	constructor(config)
	{
		this.userRegistry = config.userRegistry;
		this.userRegistry.subscribe("userAdded", this._onUserAdded.bind(this));
		this.userRegistry.subscribe("userChanged", this._onUserChanged.bind(this));

		this.elements = {
			root: null,
			users: {}
		}
	};

	render()
	{
		if (this.elements.root)
		{
			return this.elements.root;
		}

		this.elements.root = Dom.create("div", {
			props: {
				className: "bx-messenger-videocall-user-selector-mobile",
			},
		});

		this.updateUsers();

		return this.elements.root;
	};

	renderUser(userFields)
	{
		return createSVG("svg", {
			attrNS: {
				width: 14.5, height: 11.6
			},
			style: {
				order: userFields.order
			},
			children: [
				createSVG("circle", {
					attrNS: {
						class: "bx-messenger-videocall-user-selector-mobile-border" + (userFields.talking ? " talking" : ""),
						cx: 7.25, cy: 5.8, r: 4.6,
					},
				}),
				createSVG("circle", {
					attrNS: {
						class: "bx-messenger-videocall-user-selector-mobile-dot" + (userFields.centralUser ? " pinned" : ""),
						cx: 7.25, cy: 5.8, r: 3.3
					},
				})
			]
		});
	};

	updateUsers()
	{
		this.userRegistry.users.forEach(function (userFields)
		{
			if (userFields.localUser || userFields.state != UserState.Connected)
			{
				if (this.elements.users[userFields.id])
				{
					BX.remove(this.elements.users[userFields.id]);
					this.elements.users[userFields.id] = null;
				}
			}
			else
			{
				var newNode = this.renderUser(userFields);
				if (this.elements.users[userFields.id])
				{
					BX.replace(this.elements.users[userFields.id], newNode)
				}
				else
				{
					this.elements.root.appendChild(newNode)
				}
				this.elements.users[userFields.id] = newNode;
			}
		}, this)
	};

	_onUserAdded(event)
	{
		this.updateUsers();
	};

	_onUserChanged(event)
	{
		this.updateUsers();
	};

	mount(parentElement)
	{
		parentElement.appendChild(this.render());
	};

	dismount()
	{
		if (!this.elements.root)
		{
			return;
		}
		BX.remove(this.elements.root);
	};
}

export class MobileSlider
{
	constructor(config)
	{
		this.parent = config.parent || null;
		this.content = config.content || null;

		this.elements = {
			background: null,
			root: null,
			handle: null,
			body: null,
		};

		this.callbacks = {
			onClose: BX.prop.getFunction(config, "onClose", BX.DoNothing),
			onDestroy: BX.prop.getFunction(config, "onDestroy", BX.DoNothing),
		};

		this.touchStartY = 0;
		this.processedTouchId = 0;
	};

	render()
	{
		if (this.elements.root)
		{
			return this.elements.root;
		}

		this.elements.background = Dom.create("div", {
			props: {
				className: "bx-videocall-mobile-menu-background"
			},
			events: {
				click: this._onBackgroundClick.bind(this)
			}
		});
		this.elements.root = Dom.create("div", {
			props: {
				className: "bx-videocall-mobile-menu-container"
			},
			children: [
				this.elements.handle = Dom.create("div", {
					props: {
						className: "bx-videocall-mobile-menu-handle"
					},
				}),
				this.elements.body = Dom.create("div", {
					props: {
						className: "bx-videocall-mobile-menu"
					},
					children: [
						this.content
					]
				})
			],
			events: {
				touchstart: this._onTouchStart.bind(this),
				touchmove: this._onTouchMove.bind(this),
				touchend: this._onTouchEnd.bind(this),
			}
		});

		return this.elements.root;
	};

	show()
	{
		if (this.parent)
		{
			this.render();
			this.parent.appendChild(this.elements.root);
			this.parent.appendChild(this.elements.background);
		}
	};

	close()
	{
		BX.remove(this.elements.root);
		BX.remove(this.elements.background);
		this.callbacks.onClose();
	};

	closeWithAnimation()
	{
		if (!this.elements.root)
		{
			return;
		}

		this.elements.root.classList.add("closing");
		this.elements.background.classList.add("closing");
		this.elements.root.addEventListener("animationend", function ()
		{
			this.close();
		}.bind(this));
	};

	_onTouchStart(e)
	{
		this.touchStartY = e.pageY;
		if (this.processedTouchId || e.touches.length > 1)
		{
			return;
		}
		if (e.target == this.elements.header || e.target == this.elements.root || this.elements.body.scrollTop === 0)
		{
			this.processedTouchId = e.touches[0].identifier;
		}
	};

	_onTouchMove(e)
	{
		if (e.touches.length > 1)
		{
			return;
		}
		if (e.touches[0].identifier != this.processedTouchId)
		{
			return;
		}
		var delta = this.touchStartY - e.pageY;
		if (delta > 0)
		{
			delta = 0;
		}
		this.elements.root.style.bottom = delta + "px";
		if (delta)
		{
			e.preventDefault();
		}
	};

	_onTouchEnd(e)
	{
		var allowProcessing = false;
		for (var i = 0; i < e.changedTouches.length; i++)
		{
			if (e.changedTouches[i].identifier == this.processedTouchId)
			{
				allowProcessing = true;
				break;
			}
		}
		if (!allowProcessing)
		{
			return;
		}

		var delta = e.pageY - this.touchStartY;
		if (delta > 100)
		{
			this.closeWithAnimation();
			e.preventDefault();
		}
		else
		{
			this.elements.root.style.removeProperty("bottom");
		}

		this.processedTouchId = 0;
		this.touchStartY = 0;
	};

	destroy()
	{
		this.callbacks.onDestroy();
		this.elements = {};
		this.callbacks = {};
		this.parent = null;
	};

	_onBackgroundClick()
	{
		this.closeWithAnimation();
	};
}

export class MobileMenu
{
	constructor(config)
	{
		this.parent = config.parent || null;
		this.header = BX.prop.getString(config, "header", "");
		this.largeIcons = BX.prop.getBoolean(config, "largeIcons", false);

		this.slider = null;

		var items = BX.prop.getArray(config, "items", []);
		if (items.length === 0)
		{
			throw Error("Items array should not be empty");
		}

		this.items = items
			.filter(item => typeof (item) === "object" && !!item)
			.map(item => new MobileMenuItem(item))

		this.elements = {
			root: null,
			header: null,
			body: null
		};

		this.callbacks = {
			onClose: BX.prop.getFunction(config, "onClose", BX.DoNothing),
			onDestroy: BX.prop.getFunction(config, "onDestroy", BX.DoNothing),
		};
	};

	render()
	{
		this.elements.header = Dom.create("div", {
			props: {
				className: "bx-videocall-mobile-menu-header"
			},
			text: this.header
		});
		this.elements.body = Dom.create("div", {
			props: {
				className: "bx-videocall-mobile-menu-body" + (this.largeIcons ? " bx-videocall-mobile-menu-large" : "")
			}
		});

		this.items.forEach(item =>
		{
			if (item)
			{
				this.elements.body.appendChild(item.render());
			}
		});

		return BX.createFragment([
			this.elements.header,
			this.elements.body
		]);
	};

	setHeader(header)
	{
		this.header = header;
		if (this.elements.header)
		{
			this.elements.header.innerText = header;
		}
	};

	show()
	{
		if (!this.slider)
		{
			this.slider = new MobileSlider({
				parent: this.parent,
				content: this.render(),
				onClose: this.onSliderClose.bind(this),
				onDestroy: this.onSliderDestroy.bind(this),
			});
		}

		this.slider.show()
	};

	close()
	{
		if (this.slider)
		{
			this.slider.close()
		}
	};

	onSliderClose()
	{
		this.slider.destroy();
	};

	onSliderDestroy()
	{
		this.slider = null;
		this.destroy();
	};

	destroy()
	{
		if (this.slider)
		{
			this.slider.destroy();
		}
		this.slider = null;
		this.items.forEach(function (item)
		{
			item.destroy();
		});
		this.items = [];

		this.callbacks.onDestroy();
		this.elements = {};
		this.callbacks = {};
		this.parent = null;
	};
}

class MobileMenuItem
{
	constructor(config)
	{
		this.id = BX.prop.getString(config, "id", Util.getUuidv4());
		this.icon = BX.prop.getString(config, "icon", "");
		this.iconClass = BX.prop.getString(config, "iconClass", "");
		this.text = BX.prop.getString(config, "text", "");
		this.showSubMenu = BX.prop.getBoolean(config, "showSubMenu", false);
		this.separator = BX.prop.getBoolean(config, "separator", false);
		this.enabled = BX.prop.getBoolean(config, "enabled", true);
		this.userModel = BX.prop.get(config, "userModel", null);

		if (this.userModel)
		{
			this._userChangeHandler = this._onUserChange.bind(this);
			this.subscribeUserEvents();
			this.text = this.userModel.name;
			this.icon = this.userModel.avatar;
			this.iconClass = "user-avatar";
		}

		this.elements = {
			root: null,
			icon: null,
			content: null,
			submenu: null,
			separator: null,
			mic: null,
			cam: null,
		};

		this.callbacks = {
			click: BX.prop.getFunction(config, "onClick", BX.DoNothing),
			clickSubMenu: BX.prop.getFunction(config, "onClickSubMenu", BX.DoNothing),
		};
	};

	render()
	{
		if (this.elements.root)
		{
			return this.elements.root;
		}

		if (this.separator)
		{
			this.elements.root = Dom.create("hr", {
				props: {
					className: "bx-videocall-mobile-menu-item-separator",
				},
			})
		}
		else
		{
			this.elements.root = Dom.create("div", {
				props: {
					className: "bx-videocall-mobile-menu-item" + (this.enabled ? "" : " disabled"),
				},
				children: [
					this.elements.icon = Dom.create("div", {
						props: {
							className: "bx-videocall-mobile-menu-item-icon " + this.iconClass
						}
					}),
					this.elements.content = Dom.create("div", {
						props: {
							className: "bx-videocall-mobile-menu-item-content"
						},
						children: [
							Dom.create("span", {
								text: this.text
							})
						]
					}),
				],
				events: {
					click: this.callbacks.click
				}
			});

			if (this.icon != "")
			{
				this.elements.icon.style.backgroundImage = "url(\"" + this.icon + "\")";
			}

			if (this.showSubMenu)
			{
				this.elements.submenu = Dom.create("div", {
					props: {
						className: "bx-videocall-mobile-menu-item-submenu-icon"
					}
				});
				this.elements.root.appendChild(this.elements.submenu);
			}

			if (this.userModel)
			{
				this.elements.mic = Dom.create("div", {
					props: {
						className: "bx-videocall-mobile-menu-icon-user bx-call-view-icon-red-microphone-off"
					}
				});
				this.elements.cam = Dom.create("div", {
					props: {
						className: "bx-videocall-mobile-menu-icon-user bx-call-view-icon-red-camera-off"
					}
				});
				if (!this.userModel.cameraState)
				{
					this.elements.content.prepend(this.elements.cam);
				}
				if (!this.userModel.microphoneState)
				{
					this.elements.content.prepend(this.elements.mic);
				}
			}
		}

		return this.elements.root;
	};

	updateUserIcons()
	{
		if (!this.userModel)
		{
			return;
		}

		if (this.userModel.microphoneState)
		{
			BX.remove(this.elements.mic);
		}
		else
		{
			this.elements.content.prepend(this.elements.mic);
		}
		if (this.userModel.cameraState)
		{
			BX.remove(this.elements.cam);
		}
		else
		{
			this.elements.content.prepend(this.elements.cam);
		}
	};

	subscribeUserEvents()
	{
		this.userModel.subscribe("changed", this._userChangeHandler);
	};

	_onUserChange(event)
	{
		this.updateUserIcons();
	};

	destroy()
	{
		if (this.userModel)
		{
			this.userModel.unsubscribe("changed", this._userChangeHandler);
			this.userModel = null;
		}
		this.callbacks = null;
		this.elements = null;
	};
}