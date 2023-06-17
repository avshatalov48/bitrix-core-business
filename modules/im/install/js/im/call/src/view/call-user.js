import {Dom, Type} from 'main.core';
import {Menu} from 'main.popup';
import {UserState} from '../engine/engine';
import {BackgroundDialog} from '../dialogs/background_dialog';
import {logPlaybackError} from './tools';
import type {UserModel} from './user-registry'

type CallUserElements = {
	root?: HTMLElement,
	container?: HTMLElement,
	videoContainer?: HTMLElement,
	video?: HTMLVideoElement,
	audio?: HTMLAudioElement,
	videoBorder?: HTMLElement,
	avatarContainer?: HTMLElement,
	avatar?: HTMLElement,
	nameContainer?: HTMLElement,
	name?: HTMLElement,
	changeNameIcon?: HTMLElement,
	changeNameContainer?: HTMLElement,
	changeNameCancel?: HTMLElement,
	changeNameInput?: HTMLInputElement,
	changeNameConfirm?: HTMLElement,
	changeNameLoader?: HTMLElement,
	introduceYourselfContainer?: HTMLElement,
	floorRequest?: HTMLElement,
	state?: HTMLElement,
	removeButton?: HTMLElement,
	micState?: HTMLElement,
	cameraState?: HTMLElement,
	panel?: HTMLElement,
	buttonMenu?: HTMLElement,
	buttonBackground?: HTMLElement,
	buttonPin?: HTMLElement,
	buttonUnPin?: HTMLElement,
	buttonMask?: HTMLElement,
}

type CallUserParams = {
	parentContainer: HTMLElement,
	userModel: UserModel,
	audioElement: ?HTMLAudioElement,
	allowBackgroundItem: ?boolean,
	allowMaskItem: ?boolean,
	allowPinButton: ?boolean,
	screenSharingUser: ?boolean,
	audioTrack: ?MediaStreamTrack,
	videoTrack: ?MediaStreamTrack,

	onClick: () => void,
	onPin: () => void,
	onUnPin: () => void,
	onUserRename: () => void,
	onUserRenameInputFocus: () => void,
	onUserRenameInputBlur: () => void,
}

export class CallUser
{
	userModel: UserModel
	elements: CallUserElements = {}
	menu: ?Menu

	constructor(config: CallUserParams = {})
	{
		this.userModel = config.userModel;
		this.userModel.subscribe("changed", this._onUserFieldChanged.bind(this));

		this.parentContainer = config.parentContainer;
		this.screenSharingUser = Type.isBoolean(config.screenSharingUser) ? config.screenSharingUser : false;
		this.allowBackgroundItem = Type.isBoolean(config.allowBackgroundItem) ? config.allowBackgroundItem : true;
		this.allowMaskItem = Type.isBoolean(config.allowMaskItem) ? config.allowMaskItem : true;
		this._allowPinButton = Type.isBoolean(config.allowPinButton) ? config.allowPinButton : true;
		this._visible = true;
		this._audioTrack = config.audioTrack;
		this._audioStream = this._audioTrack ? new MediaStream([this._audioTrack]) : null;
		this._videoTrack = config.videoTrack;
		this._stream = this._videoTrack ? new MediaStream([this._videoTrack]) : null;
		this._videoRenderer = null;
		this._flipVideo = false;

		this.hidden = false;
		this.videoBlurState = false;
		this.isChangingName = false;

		this.incomingVideoConstraints = {
			width: 0, height: 0
		}
		if (config.audioElement)
		{
			this.elements.audio = config.audioElement;
		}

		this.callBacks = {
			onClick: Type.isFunction(config.onClick) ? config.onClick : BX.DoNothing,
			onUserRename: Type.isFunction(config.onUserRename) ? config.onUserRename : BX.DoNothing,
			onUserRenameInputFocus: Type.isFunction(config.onUserRenameInputFocus) ? config.onUserRenameInputFocus : BX.DoNothing,
			onUserRenameInputBlur: Type.isFunction(config.onUserRenameInputBlur) ? config.onUserRenameInputBlur : BX.DoNothing,
			onPin: Type.isFunction(config.onPin) ? config.onPin : BX.DoNothing,
			onUnPin: Type.isFunction(config.onUnPin) ? config.onUnPin : BX.DoNothing,
		};
		this.checkAspectInterval = setInterval(this.checkVideoAspect.bind(this), 500);
	};

	get id()
	{
		return this.userModel.id
	}

	get allowPinButton()
	{
		return this._allowPinButton;
	}

	set allowPinButton(allowPinButton)
	{
		if (this._allowPinButton == allowPinButton)
		{
			return;
		}
		this._allowPinButton = allowPinButton;
		this.update()
	}

	get audioTrack()
	{
		return this._audioTrack;
	}

	set audioTrack(audioTrack: ?MediaStreamTrack)
	{
		if (this._audioTrack === audioTrack)
		{
			return;
		}
		this._audioTrack = audioTrack;
		this._audioStream = this._audioTrack ? new MediaStream([this._audioTrack]) : null;
		this.playAudio()
	}

	get audioStream()
	{
		return this._audioStream;
	}

	get flipVideo()
	{
		return this._flipVideo;
	}

	set flipVideo(flipVideo)
	{
		this._flipVideo = flipVideo;
		this.update()
	}

	get stream(): ?MediaStream
	{
		return this._stream;
	}

	get visible()
	{
		return this._visible;
	}

	set visible(visible)
	{
		if (this._visible !== visible)
		{
			this._visible = visible;
			this.update();
			this.updateRendererState();
		}
	}

	get videoRenderer()
	{
		return this._videoRenderer;
	}

	set videoRenderer(videoRenderer)
	{
		this._videoRenderer = videoRenderer;
		this.update();
		this.updateRendererState();
	}

	get videoTrack()
	{
		return this._videoTrack;
	}

	set videoTrack(videoTrack: MediaStreamTrack)
	{
		if (this._videoTrack === videoTrack)
		{
			return;
		}
		this._videoTrack = videoTrack;
		this._stream = this._videoTrack ? new MediaStream([this._videoTrack]) : null;
		this.update()
	}

	render()
	{
		if (this.elements.root)
		{
			return this.elements.root;
		}
		this.elements.root = Dom.create("div", {
			props: {className: "bx-messenger-videocall-user"},
			dataset: {userId: this.userModel.id, order: this.userModel.order},
			children: [
				this.elements.videoBorder = Dom.create("div", {
					props: {
						className: "bx-messenger-videocall-user-border",
					}, children: [
						Dom.create("div", {
							props: {className: "bx-messenger-videocall-user-talking-icon"},
						}),
					]
				}),
				this.elements.container = Dom.create("div", {
					props: {className: "bx-messenger-videocall-user-inner"},
					children: [
						this.elements.avatarBackground = Dom.create("div", {
							props: {className: "bx-messenger-videocall-user-avatar-background"},
						}),
						this.elements.avatarContainer = Dom.create("div", {
							props: {className: "bx-messenger-videocall-user-avatar-border"},
							children: [this.elements.avatar = Dom.create("div", {
								props: {className: "bx-messenger-videocall-user-avatar"},
							}), Dom.create("div", {
								props: {className: "bx-messenger-videocall-user-avatar-overlay-border"}
							})]
						}),
						this.elements.panel = Dom.create("div", {
							props: {className: "bx-messenger-videocall-user-panel"}
						}),
						this.elements.state = Dom.create("div", {
							props: {className: "bx-messenger-videocall-user-status-text"},
							text: this.getStateMessage(this.userModel.state)
						}),
						Dom.create("div", {
							props: {className: "bx-messenger-videocall-user-bottom"},
							children: [
								this.elements.nameContainer = Dom.create("div", {
									props: {className: "bx-messenger-videocall-user-name-container" + ((this.userModel.allowRename && !this.userModel.wasRenamed) ? " hidden" : "")},
									children: [
										this.elements.micState = Dom.create("div", {
											props: {className: "bx-messenger-videocall-user-device-state mic" + (this.userModel.microphoneState ? " hidden" : "")},
										}),
										this.elements.cameraState = Dom.create("div", {
											props: {className: "bx-messenger-videocall-user-device-state camera" + (this.userModel.cameraState ? " hidden" : "")},
										}),
										this.elements.name = Dom.create("span", {
											props: {className: "bx-messenger-videocall-user-name"},
											text: (this.screenSharingUser ? BX.message('IM_CALL_USERS_SCREEN').replace("#NAME#", this.userModel.name) : this.userModel.name)
										}),
										this.elements.changeNameIcon = Dom.create("div", {
											props: {className: "bx-messenger-videocall-user-change-name-icon hidden"},
										})],
									events: {
										click: this.toggleNameInput.bind(this)
									}
								}),
								this.elements.changeNameContainer = Dom.create("div", {
									props: {className: "bx-messenger-videocall-user-change-name-container hidden"},
									children: [
										this.elements.changeNameCancel = Dom.create("div", {
											props: {className: "bx-messenger-videocall-user-change-name-cancel"},
											events: {
												click: this.toggleNameInput.bind(this)
											}
										}),
										this.elements.changeNameInput = Dom.create("input", {
											props: {
												className: "bx-messenger-videocall-user-change-name-input"
											}, attrs: {
												type: 'text', value: this.userModel.name
											}, events: {
												keydown: this.onNameInputKeyDown.bind(this),
												focus: this.callBacks.onUserRenameInputFocus,
												blur: this.callBacks.onUserRenameInputBlur
											}
										}),
										this.elements.changeNameConfirm = Dom.create("div", {
											props: {className: "bx-messenger-videocall-user-change-name-confirm"},
											events: {
												click: this.changeName.bind(this)
											}
										}),
										this.elements.changeNameLoader = Dom.create("div", {
											props: {className: "bx-messenger-videocall-user-change-name-loader hidden"},
											children: [
												Dom.create("div", {
													props: {className: "bx-messenger-videocall-user-change-name-loader-icon"}
												})
											]
										})
									]
								}),
								this.elements.introduceYourselfContainer = Dom.create("div", {
									props: {className: "bx-messenger-videocall-user-introduce-yourself-container" + (!this.userModel.allowRename || this.userModel.wasRenamed ? " hidden" : "")},
									children: [
										Dom.create("div", {
											props: {className: "bx-messenger-videocall-user-introduce-yourself-text"},
											text: BX.message('IM_CALL_GUEST_INTRODUCE_YOURSELF'),
										})
									],
									events: {
										click: this.toggleNameInput.bind(this)
									}
								})]
						}),
						this.elements.floorRequest = Dom.create("div", {
							props: {className: "bx-messenger-videocall-user-floor-request bx-messenger-videocall-floor-request-icon"}
						})
					]
				}),
			],
			style: {
				order: this.userModel.order
			},
			events: {
				click: function (e)
				{
					e.stopPropagation();
					this.callBacks.onClick({
						userId: this.id
					});
				}.bind(this)
			}
		});

		if (this.userModel.talking)
		{
			this.elements.root.classList.add("bx-messenger-videocall-user-talking");
		}

		if (this.userModel.localUser)
		{
			this.elements.root.classList.add("bx-messenger-videocall-user-self");
		}

		if (this.userModel.avatar !== '')
		{
			this.elements.root.style.setProperty("--avatar", "url('" + this.userModel.avatar + "')");
		}
		else
		{
			this.elements.root.style.removeProperty("--avatar");
		}

		this.elements.videoContainer = Dom.create("div", {
			props: {
				className: "bx-messenger-videocall-video-container",
			}, children: [this.elements.video = Dom.create("video", {
				props: {
					className: "bx-messenger-videocall-video", volume: 0, autoplay: true
				}, attrs: {
					playsinline: true, muted: true
				}
			}),]
		});
		this.elements.container.appendChild(this.elements.videoContainer);

		if (this.stream && this.stream.active)
		{
			this.elements.video.srcObject = this.stream;
		}
		if (this.flipVideo)
		{
			this.elements.video.classList.add("bx-messenger-videocall-video-flipped");
		}
		if (this.userModel.screenState)
		{
			this.elements.video.classList.add("bx-messenger-videocall-video-contain");
		}

		if (this.userModel.cameraState && this.userModel.microphoneState)
		{
			this.elements.nameContainer.classList.add("extra-padding");
		}

		//this.elements.nameContainer.appendChild(this.elements.micState);

		// todo: show button only if user have the permission to remove user
		/*this.elements.removeButton = Dom.create("div", {
			props: {className: "bx-messenger-videocall-user-close"}
		});

		this.elements.container.appendChild(this.elements.removeButton);*/

		this.elements.buttonMask = Dom.create("div", {
			props: {
				className: "bx-messenger-videocall-user-panel-button mask"
			},
			children: [
				Dom.create("div", {
					props: {
						className: "bx-messenger-videocall-user-panel-button-icon mask"
					}
				}),
				Dom.create("div", {
					props: {
						className: "bx-messenger-videocall-user-panel-button-text"
					},
					text: BX.message("IM_CALL_CHANGE_MASK")
				})
			],
			events: {
				click: () => BackgroundDialog.open({'tab': 'mask'})
			}
		});
		this.elements.buttonBackground = Dom.create("div", {
			props: {
				className: "bx-messenger-videocall-user-panel-button"
			},
			children: [
				Dom.create("div", {
					props: {
						className: "bx-messenger-videocall-user-panel-button-icon background"
					}
				}),
				Dom.create("div", {
					props: {
						className: "bx-messenger-videocall-user-panel-button-text"
					},
					text: BX.message("IM_CALL_CHANGE_BACKGROUND")
				})
			],
			events: {
				click: () => BackgroundDialog.open()
			}
		});
		this.elements.buttonMenu = Dom.create("div", {
			props: {
				className: "bx-messenger-videocall-user-panel-button"
			},
			children: [
				Dom.create("div", {
					props: {
						className: "bx-messenger-videocall-user-panel-button-icon menu"
					}
				}),
			],
			events: {
				click: () => this.showMenu()
			}
		});
		this.elements.buttonPin = Dom.create("div", {
			props: {
				className: "bx-messenger-videocall-user-panel-button"
			},
			children: [
				Dom.create("div", {
					props: {
						className: "bx-messenger-videocall-user-panel-button-icon pin"
					}
				}),
				Dom.create("div", {
					props: {
						className: "bx-messenger-videocall-user-panel-button-text"
					}, text: BX.message("IM_CALL_PIN")
				})
			],
			events: {
				click: (e) =>
				{
					e.stopPropagation();
					this.callBacks.onPin({userId: this.userModel.id});
				}
			}
		});
		this.elements.buttonUnPin = Dom.create("div", {
			props: {
				className: "bx-messenger-videocall-user-panel-button"
			},
			children: [
				Dom.create("div", {
					props: {
						className: "bx-messenger-videocall-user-panel-button-icon unpin"
					}
				}),
				Dom.create("div", {
					props: {
						className: "bx-messenger-videocall-user-panel-button-text"
					},
					text: BX.message("IM_CALL_UNPIN")
				})
			],
			events: {
				click:  (e) =>
				{
					e.stopPropagation();
					this.callBacks.onUnPin();
				}
			}
		});

		this.updatePanelDeferred();
		return this.elements.root;
	};

	setIncomingVideoConstraints(width, height)
	{
		this.incomingVideoConstraints.width = typeof (width) === "undefined" ? this.incomingVideoConstraints.width : width;
		this.incomingVideoConstraints.height = typeof (height) === "undefined" ? this.incomingVideoConstraints.height : height;

		if (!this.videoRenderer)
		{
			return;
		}

		// vox low quality temporary workaround
		// (disabled to test quality)
		// if (this.incomingVideoConstraints.width >= 320 && this.incomingVideoConstraints.width <= 640)
		// {
		// 	this.incomingVideoConstraints.width = 640;
		// }
		// if (this.incomingVideoConstraints.height >= 180 && this.incomingVideoConstraints.height <= 360)
		// {
		// 	this.incomingVideoConstraints.height = 360;
		// }

		this.videoRenderer.requestVideoSize(this.incomingVideoConstraints.width, this.incomingVideoConstraints.height);
	};

	updateRendererState()
	{
		/*if (this.videoRenderer)
		{
			if (this.visible)
			{
				this.videoRenderer.enable();
			}
			else
			{
				this.videoRenderer.disable();
			}
		}*/

		/*if (this.elements.video && this.elements.video.srcObject)
		{
			if (this.visible)
			{
				this.elements.video.play();
			}
			else
			{
				this.elements.video.pause();
			}
		}*/
	};

	_onUserFieldChanged(event)
	{
		const eventData = event.data;

		switch (eventData.fieldName)
		{
			case "id":
				return this.updateId();
			case "name":
				return this.updateName();
			case "avatar":
				return this.updateAvatar();
			case "state":
				return this.updateState();
			case "talking":
				return this.updateTalking();
			case "microphoneState":
				return this.updateMicrophoneState();
			case "cameraState":
				return this.updateCameraState();
			case "videoPaused":
				return this.updateVideoPaused();
			case "floorRequestState":
				return this.updateFloorRequestState();
			case "screenState":
				return this.updateScreenState();
			case "pinned":
				return this.updatePanel();
			case "allowRename":
				return this.updateRenameAllowed();
			case "wasRenamed":
				return this.updateWasRenamed();
			case "renameRequested":
				return this.updateRenameRequested();
			case "order":
				return this.updateOrder();

		}
	};

	toggleRenameIcon()
	{
		if (!this.userModel.allowRename)
		{
			return;
		}

		this.elements.changeNameIcon.classList.toggle('hidden');
	};

	toggleNameInput(event)
	{
		if (!this.userModel.allowRename || !this.elements.root)
		{
			return;
		}

		event.stopPropagation();

		if (this.isChangingName)
		{
			this.isChangingName = false;
			if (!this.userModel.wasRenamed)
			{
				this.elements.introduceYourselfContainer.classList.remove('hidden');
				this.elements.changeNameContainer.classList.add('hidden');
			}
			else
			{
				this.elements.changeNameContainer.classList.add('hidden');
				this.elements.nameContainer.classList.remove('hidden');
			}
		}
		else
		{
			if (!this.userModel.wasRenamed)
			{
				this.elements.introduceYourselfContainer.classList.add('hidden');
			}
			this.isChangingName = true;
			this.elements.nameContainer.classList.add('hidden');
			this.elements.changeNameContainer.classList.remove('hidden');
			this.elements.changeNameInput.value = this.userModel.name;
			this.elements.changeNameInput.focus();
			this.elements.changeNameInput.select();
		}
	};

	onNameInputKeyDown(event)
	{
		if (!this.userModel.allowRename)
		{
			return;
		}

		//enter
		if (event.keyCode === 13)
		{
			this.changeName(event);
		}
		//escape
		else if (event.keyCode === 27)
		{
			this.toggleNameInput(event);
		}
	};

	onNameInputFocus(event)
	{

	};

	onNameInputBlur(event)
	{

	};

	changeName(event)
	{
		event.stopPropagation();

		const inputValue = this.elements.changeNameInput.value;
		const newName = inputValue.trim();
		let needToUpdate = true;
		if (newName === this.userModel.name || newName === '')
		{
			needToUpdate = false;
		}

		if (needToUpdate)
		{
			this.elements.changeNameConfirm.classList.toggle('hidden');
			this.elements.changeNameLoader.classList.toggle('hidden');
			this.callBacks.onUserRename(newName);
		}
		else
		{
			this.toggleNameInput(event);
		}
	};

	showMenu()
	{
		const menuItems = [];

		if (this.userModel.localUser && this.allowBackgroundItem)
		{
			menuItems.push({
				text: (this.allowMaskItem ? BX.message("IM_CALL_CHANGE_BG_MASK") : BX.message("IM_CALL_CHANGE_BACKGROUND")),
				onclick: () =>
				{
					this.menu.close();
					BackgroundDialog.open();
				}
			});
		}
		if (menuItems.length === 0)
		{
			return;
		}

		let rect = Dom.getRelativePosition(this.elements.buttonMenu, this.parentContainer)
		this.menu = new Menu({
			id: 'call-view-user-menu-' + this.userModel.id,
			bindElement: {
				left: rect.left,
				top: rect.top,
				bottom: rect.bottom
			},
			items: menuItems,
			targetContainer: this.parentContainer,
			autoHide: true,
			closeByEsc: true,
			offsetTop: 0,
			offsetLeft: 0,
			bindOptions: {
				position: 'bottom'
			},
			angle: true,
			overlay: {
				backgroundColor: 'white', opacity: 0
			},
			cacheable: false,
			events: {
				onPopupDestroy: () => this.menu = null
			}
		});
		this.menu.show();
	};

	updateAvatar()
	{
		if (this.elements.root)
		{
			if (this.userModel.avatar !== '')
			{
				this.elements.root.style.setProperty("--avatar", "url('" + this.userModel.avatar + "')");
			}
			else
			{
				this.elements.root.style.removeProperty("--avatar");
			}
		}
	};

	updateId()
	{
		if (this.elements.root)
		{
			this.elements.root.dataset.userId = this.userModel.id;
		}
	};

	updateName()
	{
		if (this.isChangingName)
		{
			this.isChangingName = false;
			this.elements.changeNameConfirm.classList.toggle('hidden');
			this.elements.changeNameLoader.classList.toggle('hidden');
			this.elements.changeNameContainer.classList.add('hidden');
			this.elements.nameContainer.classList.remove('hidden');
		}

		if (this.elements.name)
		{
			this.elements.name.innerText = this.screenSharingUser ? BX.message('IM_CALL_USERS_SCREEN').replace("#NAME#", this.userModel.name) : this.userModel.name;
		}
	};

	updateRenameAllowed()
	{
		if (this.userModel.allowRename && this.elements.nameContainer && this.elements.introduceYourselfContainer)
		{
			this.elements.nameContainer.classList.add('hidden');
			this.elements.introduceYourselfContainer.classList.remove('hidden');
		}
	};

	updateWasRenamed()
	{
		if (!this.elements.root)
		{
			return;
		}

		if (this.userModel.allowRename)
		{
			this.elements.introduceYourselfContainer.classList.add('hidden');
			this.elements.changeNameIcon.classList.remove('hidden');
			if (this.elements.changeNameContainer.classList.contains('hidden'))
			{
				this.elements.nameContainer.classList.remove('hidden');
			}
		}
	};

	updateRenameRequested()
	{
		if (this.userModel.allowRename)
		{
			this.elements.introduceYourselfContainer.classList.add('hidden');
		}
	};

	updateOrder()
	{
		if (this.elements.root)
		{
			this.elements.root.dataset.order = this.userModel.order;
			this.elements.root.style.order = this.userModel.order;
		}
	};

	updatePanelDeferred()
	{
		setTimeout(this.updatePanel.bind(this), 0);
	};

	updatePanel()
	{
		if (!this.isMounted())
		{
			return;
		}
		const width = this.elements.root.offsetWidth;

		Dom.clean(this.elements.panel);
		if (this.userModel.localUser && this.allowBackgroundItem)
		{
			if (width > 300)
			{
				if (this.allowMaskItem)
				{
					this.elements.panel.appendChild(this.elements.buttonMask);
				}
				this.elements.panel.appendChild(this.elements.buttonBackground);
			}
			else
			{
				this.elements.panel.appendChild(this.elements.buttonMenu);
			}
		}

		if (!this.userModel.localUser && this.allowPinButton)
		{
			if (this.userModel.pinned)
			{
				this.elements.panel.appendChild(this.elements.buttonUnPin);
			}
			else
			{
				this.elements.panel.appendChild(this.elements.buttonPin);
			}

			if (width > 250)
			{
				this.elements.buttonPin.classList.remove("no-text");
				this.elements.buttonUnPin.classList.remove("no-text");
			}
			else
			{
				this.elements.buttonPin.classList.add("no-text");
				this.elements.buttonUnPin.classList.add("no-text");
			}
		}
	};

	update()
	{
		if (!this.elements.root)
		{
			return;
		}
		if (this.hasVideo()/* && this.visible*/)
		{
			if (this.visible)
			{
				if (this.videoRenderer)
				{
					this.videoRenderer.render(this.elements.video);
				}
				else if (this.elements.video.srcObject != this.stream)
				{
					this.elements.video.srcObject = this.stream;
				}
			}

			Dom.remove(this.elements.avatarContainer);
			this.elements.video.classList.toggle("bx-messenger-videocall-video-flipped", this.flipVideo);
			this.elements.video.classList.toggle("bx-messenger-videocall-video-contain", this.userModel.screenState);
		}
		else
		{
			this.elements.video.srcObject = null;
			this.elements.container.insertBefore(this.elements.avatarContainer, this.elements.panel);
		}
		this.updatePanelDeferred();
	};

	playAudio()
	{
		if (!this.audioStream)
		{
			this.elements.audio.srcObject = null;
			return;
		}

		if (this.speakerId && Type.isFunction(this.elements.audio.setSinkId))
		{
			this.elements.audio.setSinkId(this.speakerId).then(function ()
			{
				this.elements.audio.srcObject = this.audioStream;
				this.elements.audio.play().catch(logPlaybackError);
			}.bind(this)).catch(console.error);
		}
		else
		{
			this.elements.audio.srcObject = this.audioStream;
			this.elements.audio.play().catch(logPlaybackError);
		}
	};

	playVideo()
	{
		if (this.elements.video)
		{
			this.elements.video.play().catch(logPlaybackError);
		}
	};

	blurVideo(blurState)
	{
		blurState = !!blurState;

		if (this.videoBlurState == blurState)
		{
			return;
		}
		this.videoBlurState = blurState;
		if (this.elements.video)
		{
			this.elements.video.classList.toggle('bx-messenger-videocall-video-blurred');
		}
	};

	getStateMessage(userState, videoPaused)
	{
		switch (userState)
		{
			case UserState.Idle:
				return "";
			case UserState.Calling:
				return BX.message("IM_M_CALL_STATUS_WAIT_ANSWER");
			case UserState.Declined:
				return BX.message("IM_M_CALL_STATUS_DECLINED");
			case UserState.Ready:
			case UserState.Connecting:
				return BX.message("IM_M_CALL_STATUS_WAIT_CONNECT");
			case UserState.Connected:
				return videoPaused ? BX.message("IM_M_CALL_STATUS_VIDEO_PAUSED") : "";
			case UserState.Failed:
				return BX.message("IM_M_CALL_STATUS_CONNECTION_ERROR");
			case UserState.Unavailable:
				return BX.message("IM_M_CALL_STATUS_UNAVAILABLE");
			default:
				return "";
		}
	};

	mount(parent, force)
	{
		force = force === true;
		if (!this.elements.root)
		{
			this.render();
		}

		if (this.isMounted() && this.elements.root.parentElement == parent && !force)
		{
			this.updatePanelDeferred();
			return false;
		}

		parent.appendChild(this.elements.root);
		this.update();
	};

	dismount()
	{
		// this.visible = false;
		if (!this.isMounted())
		{
			return false;
		}

		this.elements.video.srcObject = null;
		Dom.remove(this.elements.root);
	};

	isMounted()
	{
		return !!(this.elements.root && this.elements.root.parentElement);
	};

	updateState()
	{
		if (!this.elements.root)
		{
			return;
		}

		if (this.userModel.state == UserState.Calling || this.userModel.state == UserState.Connecting)
		{
			this.elements.avatar.classList.add("bx-messenger-videocall-user-avatar-pulse");
		}
		else
		{
			this.elements.avatar.classList.remove("bx-messenger-videocall-user-avatar-pulse");
		}

		this.elements.state.innerText = this.getStateMessage(this.userModel.state, this.userModel.videoPaused);
		this.update();
	};

	updateTalking()
	{
		if (!this.elements.root)
		{
			return;
		}
		if (this.userModel.talking)
		{
			this.elements.root.classList.add("bx-messenger-videocall-user-talking");
		}
		else
		{
			this.elements.root.classList.remove("bx-messenger-videocall-user-talking");
		}
	};

	updateMicrophoneState()
	{
		if (!this.elements.root)
		{
			return;
		}
		if (this.userModel.microphoneState)
		{
			this.elements.micState.classList.add("hidden");
		}
		else
		{
			this.elements.micState.classList.remove("hidden");
		}

		if (this.userModel.cameraState && this.userModel.microphoneState)
		{
			this.elements.nameContainer.classList.add("extra-padding");
		}
		else
		{
			this.elements.nameContainer.classList.remove("extra-padding");
		}
	};

	updateCameraState()
	{
		if (!this.elements.root)
		{
			return;
		}
		if (this.userModel.cameraState)
		{
			this.elements.cameraState.classList.add("hidden");
		}
		else
		{
			this.elements.cameraState.classList.remove("hidden");
		}

		if (this.userModel.cameraState && this.userModel.microphoneState)
		{
			this.elements.nameContainer.classList.add("extra-padding");
		}
		else
		{
			this.elements.nameContainer.classList.remove("extra-padding");
		}
	};

	updateVideoPaused()
	{
		if (!this.elements.root)
		{
			return;

		}
		if (this.stream && this.hasVideo())
		{
			this.blurVideo(this.userModel.videoPaused);
		}
		this.updateState();
	};

	updateFloorRequestState()
	{
		if (!this.elements.floorRequest)
		{
			return;
		}
		if (this.userModel.floorRequestState)
		{
			this.elements.floorRequest.classList.add("active");
		}
		else
		{
			this.elements.floorRequest.classList.remove("active");
		}
	};

	updateScreenState()
	{
		if (!this.elements.video)
		{
			return;
		}
		if (this.userModel.screenState)
		{
			this.elements.video.classList.add("bx-messenger-videocall-video-contain");
		}
		else
		{
			this.elements.video.classList.remove("bx-messenger-videocall-video-contain");
		}
	};

	hide()
	{
		if (!this.elements.root)
		{
			return;
		}

		this.elements.root.dataset.hidden = 1;
	};

	show()
	{
		if (!this.elements.root)
		{
			return;
		}

		delete this.elements.root.dataset.hidden;
	};

	hasVideo()
	{
		return this.userModel.state == UserState.Connected && (!!this._videoTrack || !!this._videoRenderer);
	};

	checkVideoAspect()
	{
		if (!this.elements.video)
		{
			return;
		}

		if (this.elements.video.videoHeight > this.elements.video.videoWidth)
		{
			this.elements.video.classList.add("bx-messenger-videocall-video-vertical");
		}
		else
		{
			this.elements.video.classList.remove("bx-messenger-videocall-video-vertical");
		}
	};

	releaseStream()
	{
		if (this.elements.video)
		{
			this.elements.video.srcObject = null;
		}
		this.videoTrack = null;
	};

	destroy()
	{
		this.releaseStream();
		clearInterval(this.checkAspectInterval);
	};
}