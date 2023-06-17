import {Dom, Text, Type} from 'main.core';
import {createSVG} from './svg';
import {View} from './view';

export class TitleButton
{
	constructor(config)
	{
		this.elements = {
			root: null
		};

		this.text = Type.isStringFilled(config.text) ? config.text : '';
		this.isGroupCall = config.isGroupCall;
	};

	render()
	{
		this.elements.root = Dom.create("div", {
			props: {className: "bx-messenger-videocall-panel-title"},
			html: this.getTitle()
		});

		return this.elements.root;
	};

	getTitle()
	{
		const prettyName = '<span class="bx-messenger-videocall-panel-title-name">' + Text.encode(this.text) + '</span>';

		if (this.isGroupCall)
		{
			return BX.message("IM_M_GROUP_CALL_WITH").replace("#CHAT_NAME#", prettyName);
		}
		else
		{
			return BX.message("IM_M_CALL_WITH").replace("#USER_NAME#", prettyName);
		}
	};
}

export class SimpleButton
{
	constructor(config)
	{
		this.class = config.class;
		this.backgroundClass = BX.prop.getString(config, "backgroundClass", "");
		this.backgroundClass = "bx-messenger-videocall-panel-icon-background" + (this.backgroundClass ? " " : "") + this.backgroundClass;
		this.blocked = config.blocked === true;

		this.text = BX.prop.getString(config, "text", "");
		this.isActive = false;
		this.counter = BX.prop.getInteger(config, "counter", 0);

		this.elements = {
			root: null,
			counter: null,
		};

		this.callbacks = {
			onClick: BX.prop.getFunction(config, "onClick", BX.DoNothing),
			onMouseOver: BX.prop.getFunction(config, "onMouseOver", BX.DoNothing),
			onMouseOut: BX.prop.getFunction(config, "onMouseOut", BX.DoNothing),
		}
	};

	render()
	{
		if (this.elements.root)
		{
			return this.elements.root;
		}

		let textNode;
		if (this.text !== '')
		{
			textNode = Dom.create("div", {props: {className: "bx-messenger-videocall-panel-text"}, text: this.text});
		}
		else
		{
			textNode = null;
		}

		this.elements.root = Dom.create("div", {
			props: {className: "bx-messenger-videocall-panel-item" + (this.blocked ? " blocked" : "")},
			children: [
				Dom.create("div", {
					props: {className: this.backgroundClass},
					children: [
						Dom.create("div", {
							props: {className: "bx-messenger-videocall-panel-icon bx-messenger-videocall-panel-icon-" + this.class},
							children: [
								this.elements.counter = Dom.create("span", {
									props: {className: "bx-messenger-videocall-panel-item-counter"},
									text: 0,
									dataset: {
										counter: 0,
										counterType: 'digits',
									}
								}),
							]
						}),
					]
				}),
				textNode,
				Dom.create("div", {
					props: {className: "bx-messenger-videocall-panel-item-bottom-spacer"}
				})
			],
			events: {
				click: this.callbacks.onClick,
				mouseover: this.callbacks.onMouseOver,
				mouseout: this.callbacks.onMouseOut
			}
		});

		if (this.isActive)
		{
			this.elements.root.classList.add("active");
		}

		if (this.counter)
		{
			this.setCounter(this.counter);
		}

		return this.elements.root;
	};

	setActive(isActive)
	{
		if (this.isActive == isActive)
		{
			return;
		}
		this.isActive = isActive;
		if (!this.elements.root)
		{
			return;
		}
		if (this.isActive)
		{
			this.elements.root.classList.add("active");
		}
		else
		{
			this.elements.root.classList.remove("active");
		}
	};

	setBlocked(isBlocked)
	{
		if (this.blocked == isBlocked)
		{
			return;
		}

		this.blocked = isBlocked;
		if (this.blocked)
		{
			this.elements.root.classList.add("blocked");
		}
		else
		{
			this.elements.root.classList.remove("blocked");
		}
	};

	setCounter(counter)
	{
		this.counter = parseInt(counter, 10);

		let counterLabel = this.counter;
		if (counterLabel > 999)
		{
			counterLabel = 999;
		}

		let counterType = 'digits';
		if (counterLabel.toString().length === 2)
		{
			counterType = 'dozens';
		}
		else if (counterLabel.toString().length > 2)
		{
			counterType = 'hundreds';
		}

		this.elements.counter.dataset.counter = counterLabel;
		this.elements.counter.dataset.counterType = counterType;
		this.elements.counter.innerText = counterLabel;
	};
}

export class DeviceButton
{
	constructor(config)
	{
		this.class = config.class;
		this.text = config.text;

		this.enabled = (config.enabled === true);
		this.arrowEnabled = (config.arrowEnabled === true);
		this.arrowHidden = (config.arrowHidden === true);
		this.blocked = (config.blocked === true);

		this.showLevel = (config.showLevel === true);
		this.level = config.level || 0;

		this.sideIcon = BX.prop.getString(config, "sideIcon", "");

		this.elements = {
			root: null,
			iconContainer: null,
			icon: null,
			arrow: null,
			levelMeter: null,
			pointer: null,
			ellipsis: null,
		};

		this.callbacks = {
			onClick: BX.prop.getFunction(config, "onClick", BX.DoNothing),
			onArrowClick: BX.prop.getFunction(config, "onArrowClick", BX.DoNothing),
			onSideIconClick: BX.prop.getFunction(config, "onSideIconClick", BX.DoNothing),
			onMouseOver: BX.prop.getFunction(config, "onMouseOver", BX.DoNothing),
			onMouseOut: BX.prop.getFunction(config, "onMouseOut", BX.DoNothing),
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
				id: "bx-messenger-videocall-panel-item-with-arrow-" + this.class,
				className: "bx-messenger-videocall-panel-item-with-arrow" + (this.blocked ? " blocked" : "")
			},
			children: [
				Dom.create("div", {
					props: {className: "bx-messenger-videocall-panel-item-with-arrow-left"},
					children: [
						this.elements.iconContainer = Dom.create("div", {
							props: {className: "bx-messenger-videocall-panel-item-with-arrow-icon-container"},
							children: [
								this.elements.icon = Dom.create("div", {
									props: {className: this.getIconClass()},
								}),
							]
						}),

						Dom.create("div", {
							props: {className: "bx-messenger-videocall-panel-text"},
							text: this.text
						})
					]
				})
			],
			events: {
				click: this.callbacks.onClick,
				mouseover: this.callbacks.onMouseOver,
				mouseout: this.callbacks.onMouseOut
			}
		});

		this.elements.arrow = Dom.create("div", {
			props: {className: "bx-messenger-videocall-panel-item-with-arrow-right"},
			children: [
				Dom.create("div", {
					props: {className: "bx-messenger-videocall-panel-item-with-arrow-right-icon"},
				})
			],
			events: {
				click: function (e)
				{
					this.callbacks.onArrowClick.apply(this, arguments);
					e.stopPropagation();
				}.bind(this)
			}
		});

		if (!this.arrowHidden)
		{
			this.elements.root.appendChild(this.elements.arrow);
		}

		if (this.showLevel)
		{
			this.elements.icon.appendChild(createSVG("svg", {
				attrNS: {
					class: "bx-messenger-videocall-panel-item-level-meter-container",
					width: 3, height: 20
				},
				children: [
					createSVG("g", {
						attrNS: {
							fill: "#30B1DC"
						},
						children: [
							createSVG("rect", {
								attrNS: {
									x: 0, y: 0, width: 3, height: 20, rx: 1.5, opacity: .1,
								}
							}),
							this.elements.levelMeter = createSVG("rect", {
								attrNS: {
									x: 0, y: 20, width: 3, height: 20, rx: 1.5,
								}
							}),
						]
					})
				]
			}));
		}

		this.elements.ellipsis = Dom.create("div", {
			props: {className: "bx-messenger-videocall-panel-icon-ellipsis"},
			events: {
				click: this.callbacks.onSideIconClick
			}
		})

		this.elements.pointer = Dom.create("div", {
			props: {className: "bx-messenger-videocall-panel-icon-pointer"},
			events: {
				click: this.callbacks.onSideIconClick
			}
		})

		if (this.sideIcon == "pointer")
		{
			BX.Dom.insertAfter(this.elements.pointer, this.elements.icon);
		}
		else if (this.sideIcon == "ellipsis")
		{
			BX.Dom.insertAfter(this.elements.ellipsis, this.elements.icon);
		}

		return this.elements.root;
	};

	getIconClass()
	{
		return "bx-messenger-videocall-panel-item-with-arrow-icon bx-messenger-videocall-panel-item-with-arrow-icon-" + this.class + (this.enabled ? "" : "-off");
	};

	enable()
	{
		if (this.enabled)
		{
			return;
		}
		this.enabled = true;
		this.elements.icon.className = this.getIconClass();
		if (this.elements.levelMeter)
		{
			this.elements.levelMeter.setAttribute('y', Math.round((1 - this.level) * 20));
		}
	};

	disable()
	{
		if (!this.enabled)
		{
			return;
		}
		this.enabled = false;
		this.elements.icon.className = this.getIconClass();
		if (this.elements.levelMeter)
		{
			this.elements.levelMeter.setAttribute('y', 20);
		}
	};

	setBlocked(blocked)
	{
		if (this.blocked == blocked)
		{
			return;
		}

		this.blocked = blocked;
		this.elements.icon.className = this.getIconClass();
		if (this.blocked)
		{
			this.elements.root.classList.add("blocked");
		}
		else
		{
			this.elements.root.classList.remove("blocked");
		}
	};

	setSideIcon(sideIcon)
	{
		if (this.sideIcon == sideIcon)
		{
			return;
		}
		this.sideIcon = sideIcon;

		BX.Dom.remove(this.elements.pointer);
		BX.Dom.remove(this.elements.ellipsis);

		if (this.sideIcon == "pointer")
		{
			BX.Dom.insertAfter(this.elements.pointer, this.elements.icon);
		}
		else if (this.sideIcon == "ellipsis")
		{
			BX.Dom.insertAfter(this.elements.ellipsis, this.elements.icon);
		}
	}

	showArrow()
	{
		if (!this.arrowHidden)
		{
			return;
		}
		this.arrowHidden = false;
		this.elements.root.appendChild(this.elements.arrow);
	};

	hideArrow()
	{
		if (this.arrowHidden)
		{
			return;
		}
		this.arrowHidden = false;
		this.elements.root.removeChild(this.elements.arrow);
	};

	setLevel(level)
	{
		this.level = Math.log(level * 100) / 4.6;
		if (this.showLevel && this.enabled)
		{
			this.elements.levelMeter.setAttribute('y', Math.round((1 - this.level) * 20));
		}
	}
}

export class WaterMarkButton
{
	constructor(config)
	{
		this.language = config.language;
	};

	render()
	{
		return Dom.create("div", {
			props: {className: "bx-messenger-videocall-watermark"},
			children: [
				Dom.create("img", {
					props: {
						className: "bx-messenger-videocall-watermark-img",
						src: this.getWatermarkUrl(this.language)
					},
				})
			]
		});
	};

	getWatermarkUrl(language)
	{
		switch (language)
		{
			case 'ua':
				return '/bitrix/js/im/images/watermark-white-ua.svg';
			case 'ru':
			case 'kz':
			case 'by':
				return '/bitrix/js/im/images/watermark-white-ru.svg';
			default:
				return '/bitrix/js/im/images/watermark-white-en.svg';
		}
	};
}

export class TopButton
{
	constructor(config)
	{
		this.iconClass = BX.prop.getString(config, "iconClass", "");
		this.text = BX.prop.getString(config, "text", "");

		this.callbacks = {
			onClick: BX.prop.getFunction(config, "onClick", BX.DoNothing),
			onMouseOver: BX.prop.getFunction(config, "onMouseOver", BX.DoNothing),
			onMouseOut: BX.prop.getFunction(config, "onMouseOut", BX.DoNothing),
		}
	};

	render()
	{
		return Dom.create("div", {
			props: {className: "bx-messenger-videocall-top-button"},
			children: [
				Dom.create("div", {
					props: {className: "bx-messenger-videocall-top-button-icon " + this.iconClass}
				}),
				Dom.create("div", {
					props: {className: "bx-messenger-videocall-top-button-text "},
					text: this.text
				})
			],
			events: {
				click: this.callbacks.onClick,
				mouseover: this.callbacks.onMouseOver,
				mouseout: this.callbacks.onMouseOut
			}
		})
	};
}

export class TopFramelessButton
{
	constructor(config)
	{
		this.iconClass = BX.prop.getString(config, "iconClass", "");
		this.textClass = BX.prop.getString(config, "textClass", "");
		this.text = BX.prop.getString(config, "text", "");

		this.callbacks = {
			onClick: BX.prop.getFunction(config, "onClick", BX.DoNothing),
			onMouseOver: BX.prop.getFunction(config, "onMouseOver", BX.DoNothing),
			onMouseOut: BX.prop.getFunction(config, "onMouseOut", BX.DoNothing),
		}
	};

	render()
	{
		return Dom.create("div", {
			props: {className: "bx-messenger-videocall-top-button-frameless"},
			children: [
				Dom.create("div", {
					props: {className: "bx-messenger-videocall-top-button-icon " + this.iconClass}
				}),
				(this.text != "" ?
						Dom.create("div", {
							props: {className: "bx-messenger-videocall-top-button-text " + this.textClass},
							text: this.text
						})
						:
						null
				)
			],
			events: {
				click: this.callbacks.onClick,
				mouseover: this.callbacks.onMouseOver,
				mouseout: this.callbacks.onMouseOut
			},
		});
	};
}

export class ParticipantsButton
{
	constructor(config)
	{
		this.count = BX.prop.getInteger(config, "count", 0);
		this.foldButtonState = BX.prop.getString(config, "foldButtonState", ParticipantsButton.FoldButtonState.Hidden);
		this.allowAdding = BX.prop.getBoolean(config, "allowAdding", false);

		this.elements = {
			root: null,
			leftContainer: null,
			rightContainer: null,
			foldIcon: null,
			count: null,
			separator: null
		};

		this.callbacks = {
			onListClick: BX.prop.getFunction(config, "onListClick", BX.DoNothing),
			onAddClick: BX.prop.getFunction(config, "onAddClick", BX.DoNothing)
		}
	};

	static FoldButtonState = {
		Active: "active",
		Fold: "fold",
		Unfold: "unfold",
		Hidden: "hidden"
	};

	render()
	{
		if (this.elements.root)
		{
			return this.elements.root;
		}
		this.elements.root = Dom.create("div", {
			props: {className: "bx-messenger-videocall-top-participants"},
			children: [
				this.elements.leftContainer = Dom.create("div", {
					props: {className: "bx-messenger-videocall-top-participants-inner left" + (this.foldButtonState != ParticipantsButton.FoldButtonState.Hidden ? " active" : "")},
					children: [
						Dom.create("div", {
							props: {className: "bx-messenger-videocall-top-button-icon participants"}
						}),
						this.elements.count = Dom.create("div", {
							props: {className: "bx-messenger-videocall-top-participants-text-count"},
							text: this.count
						}),
						this.elements.foldIcon = Dom.create("div", {
							props: {className: "bx-messenger-videocall-top-participants-fold-icon " + this.foldButtonState},
						})
					],
					events: {
						click: this.callbacks.onListClick
					}
				}),

			]
		});

		this.elements.separator = Dom.create("div", {
			props: {className: "bx-messenger-videocall-top-participants-separator"}
		});
		this.elements.rightContainer = Dom.create("div", {
			props: {className: "bx-messenger-videocall-top-participants-inner active"},
			children: [
				Dom.create("div", {
					props: {className: "bx-messenger-videocall-top-button-icon add"}
				}),
				Dom.create("div", {
					props: {className: "bx-messenger-videocall-top-participants-text"},
					text: BX.message("IM_M_CALL_BTN_ADD")
				})
			],
			events: {
				click: this.callbacks.onAddClick
			}
		});

		if (this.allowAdding)
		{
			this.elements.root.appendChild(this.elements.separator);
			this.elements.root.appendChild(this.elements.rightContainer);
		}
		return this.elements.root;
	};

	update(config)
	{
		this.count = BX.prop.getInteger(config, "count", this.count);
		this.foldButtonState = BX.prop.getString(config, "foldButtonState", this.foldButtonState);
		this.allowAdding = BX.prop.getBoolean(config, "allowAdding", this.allowAdding);

		this.elements.count.innerText = this.count;

		this.elements.foldIcon.className = "bx-messenger-videocall-top-participants-fold-icon " + this.foldButtonState;
		if (this.foldButtonState == ParticipantsButton.FoldButtonState.Hidden)
		{
			this.elements.leftContainer.classList.remove("active");
		}
		else
		{
			this.elements.leftContainer.classList.add("active");
		}

		if (this.allowAdding && !this.elements.separator.parentElement)
		{
			this.elements.root.appendChild(this.elements.separator);
			this.elements.root.appendChild(this.elements.rightContainer);
		}
		if (!this.allowAdding && this.elements.separator.parentElement)
		{
			BX.remove(this.elements.separator);
			BX.remove(this.elements.rightContainer);
		}
	};
}

export class ParticipantsButtonMobile
{
	constructor(config)
	{
		this.count = BX.prop.getInteger(config, "count", 0);
		this.elements = {
			root: null,
			icon: null,
			text: null,
			arrow: null
		};

		this.callbacks = {
			onClick: BX.prop.getFunction(config, "onClick", BX.DoNothing),
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
				className: "bx-messenger-videocall-top-participants-mobile"
			},
			children: [
				this.elements.icon = Dom.create("div", {
					props: {
						className: "bx-messenger-videocall-top-participants-mobile-icon"
					}
				}),
				this.elements.text = Dom.create("div", {
					props: {
						className: "bx-messenger-videocall-top-participants-mobile-text"
					},
					text: BX.message("IM_M_CALL_PARTICIPANTS").replace("#COUNT#", this.count)
				}),
				this.elements.arrow = Dom.create("div", {
					props: {
						className: "bx-messenger-videocall-top-participants-mobile-arrow"
					}
				}),
			],
			events: {
				click: this.callbacks.onClick
			}
		});

		return this.elements.root;
	};

	setCount(count)
	{
		if (this.count == count)
		{
			return;
		}
		this.count = count;
		this.elements.text.innerText = BX.message("IM_M_CALL_PARTICIPANTS").replace("#COUNT#", this.count);
	};
}

export class RecordStatusButton
{
	constructor(config)
	{
		this.userId = config.userId;
		this.recordState = config.recordState;

		this.updateViewInterval = null;

		this.elements = {
			root: null,
			timeText: null,
			stateText: null,
		};

		this.callbacks = {
			onPauseClick: BX.prop.getFunction(config, "onPauseClick", BX.DoNothing),
			onStopClick: BX.prop.getFunction(config, "onStopClick", BX.DoNothing),
			onMouseOver: BX.prop.getFunction(config, "onMouseOver", BX.DoNothing),
			onMouseOut: BX.prop.getFunction(config, "onMouseOut", BX.DoNothing),
		}
	};

	render()
	{
		if (this.elements.root)
		{
			return this.elements.root;
		}

		this.elements.root = Dom.create("div", {
			props: {className: "bx-messenger-videocall-top-recordstatus record-status-" + this.recordState.state + " " + (this.recordState.userId == this.userId ? '' : 'record-user-viewer')},
			children: [
				Dom.create("div", {
					props: {className: "bx-messenger-videocall-top-recordstatus-status"},
					children: [
						Dom.create("div", {
							props: {className: "bx-messenger-videocall-top-button-icon record-status"}
						}),
					]
				}),
				Dom.create("div", {
					props: {className: "bx-messenger-videocall-top-recordstatus-time"},
					children: [
						this.elements.timeText = Dom.create("span", {
							props: {className: "bx-messenger-videocall-top-recordstatus-time-text"},
							text: this.getTimeText()
						}),
						Dom.create("span", {
							props: {className: "bx-messenger-videocall-top-recordstatus-time-separator"},
							html: ' &ndash; '
						}),
						this.elements.stateText = Dom.create("span", {
							props: {className: "bx-messenger-videocall-top-recordstatus-time-state"},
							text: BX.message('IM_M_CALL_RECORD_TITLE')
						}),
					]
				}),
				Dom.create("div", {
					props: {className: "bx-messenger-videocall-top-recordstatus-buttons"},
					children: [
						Dom.create("div", {
							props: {className: "bx-messenger-videocall-top-recordstatus-separator"}
						}),
						Dom.create("div", {
							props: {className: "bx-messenger-videocall-top-recordstatus-button"},
							children: [
								Dom.create("div", {
									props: {className: "bx-messenger-videocall-top-button-icon record-pause"},
								}),
							],
							events: {
								click: this.callbacks.onPauseClick
							}
						}),
						Dom.create("div", {
							props: {className: "bx-messenger-videocall-top-recordstatus-separator"}
						}),
						Dom.create("div", {
							props: {className: "bx-messenger-videocall-top-recordstatus-button"},
							children: [
								Dom.create("div", {
									props: {className: "bx-messenger-videocall-top-button-icon record-stop"},
								}),
							],
							events: {
								click: this.callbacks.onStopClick
							}
						}),
					]
				}),
			],
			events: {
				mouseover: this.callbacks.onMouseOver,
				mouseout: this.callbacks.onMouseOut
			}
		});

		return this.elements.root;
	};

	getTimeText()
	{
		if (this.recordState.state === View.RecordState.Stopped)
		{
			return '';
		}

		const nowDate = new Date();
		let startDate = new Date(this.recordState.date.start);
		if (startDate.getTime() < nowDate.getDate())
		{
			startDate = nowDate;
		}

		const pauseTime = this.recordState.date.pause
			.map((element) =>
			{
				const finish = element.finish ? new Date(element.finish) : nowDate;
				return finish - new Date(element.start);
			})
			.reduce((sum, element) => sum + element, 0);

		let totalTime = nowDate - startDate - pauseTime;
		if (totalTime <= 0)
		{
			totalTime = 0;
		}

		let second = Math.floor(totalTime / 1000);

		let hour = Math.floor(second / 60 / 60);
		if (hour > 0)
		{
			second -= hour * 60 * 60;
		}

		const minute = Math.floor(second / 60);
		if (minute > 0)
		{
			second -= minute * 60;
		}

		return (hour > 0 ? hour + ':' : '')
			+ (hour > 0 ? minute.toString().padStart(2, "0") + ':' : minute + ':')
			+ second.toString().padStart(2, "0")
			;
	}

	update(recordState)
	{
		if (this.recordState.state !== recordState.state)
		{
			clearInterval(this.updateViewInterval);
			if (recordState.state === View.RecordState.Started)
			{
				this.updateViewInterval = setInterval(this.updateView.bind(this), 1000);
			}
		}

		this.recordState = recordState;
		this.updateView();
	}

	updateView()
	{
		var timeText = this.getTimeText();
		if (this.elements.timeText.innerText !== timeText)
		{
			this.elements.timeText.innerText = this.getTimeText();
		}

		if (!this.elements.root.classList.contains("record-status-" + this.recordState.state))
		{
			this.elements.root.className = "bx-messenger-videocall-top-recordstatus record-status-" + this.recordState.state + ' ' + (this.recordState.userId == this.userId ? '' : 'record-user-viewer');
		}
	};

	stopViewUpdate()
	{
		if (this.updateViewInterval)
		{
			clearInterval(this.updateViewInterval);
			this.updateViewInterval = null;
		}
	};
}