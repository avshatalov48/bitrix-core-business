import { Type, ajax, Loc, Event } from 'main.core';
import { BaseEvent, EventEmitter } from 'main.core.events';

import { Tooltip } from './tooltip';


export class TooltipBalloon
{
	constructor(params)
	{
		this.node = null;
		this.userId = null;
		this.loader = null;
		this.version = null;
		this.tracking = false;
		this.active = false;
		this.width = 364; // 393
		this.height = 215; // 302
		this.realAnchor = null;
		this.coordsLeft = 0;
		this.coordsTop = 0;
		this.anchorRight = 0;
		this.anchorTop = 0;
		this.hMirror = false;
		this.vMirror = false;
		this.rootClassName = '';
		this.INFO = null;
		this.DIV = null;
		this.ROOT_DIV = null;
		this.params = {};
		this.trackMouseHandle = this.trackMouse.bind(this);

		this.init(params);
		this.create();
		return this;
	}

	init(params)
	{
		this.node = params.node;
		this.userId = params.userId;
		this.loader = (Type.isStringFilled(params.loader) ? params.loader : '');

		this.version = (
			!Type.isUndefined(params.version)
			&& parseInt(params.version) > 0
				? parseInt(params.version)
				: (Type.isStringFilled(this.loader) ? 2 : 3)
		);

		this.rootClassName = this.node.getAttribute('bx-tooltip-classname');

		const paramsString = this.node.getAttribute('bx-tooltip-params');

		let anchorParams = {};
		if (Type.isStringFilled(paramsString))
		{
			anchorParams = JSON.parse(paramsString);
			if (!Type.isPlainObject(anchorParams))
			{
				anchorParams = {};
			}
		}

		this.params = anchorParams;

		EventEmitter.subscribe('SidePanel.Slider:onOpen', this.onSliderOpen.bind(this));
	}

	create()
	{
		if (!Tooltip.getDisabledStatus())
		{
			this.startTrackMouse();
		}

		Event.bind(this.node, 'mouseout', this.stopTrackMouse.bind(this));
	}

	onSliderOpen()
	{
		if (this.tracking)
		{
			this.stopTrackMouse();
		}
		else
		{
			this.hideTooltip();
		}
	}

	startTrackMouse()
	{
		if (this.tracking)
		{
			return;
		}

		const elCoords = BX.pos(this.node);
		this.realAnchor = this.node;

		this.coordsLeft = (
			elCoords.width < 40
				? (elCoords.left - 35)
				: (elCoords.left + 0)
		);
		this.coordsTop = elCoords.top - 245; // 325
		this.anchorRight = elCoords.right;
		this.anchorTop = elCoords.top;

		this.tracking = true;

		document.addEventListener('mousemove', this.trackMouseHandle);

		setTimeout(() => {
			this.tickTimer();
		}, 500);

		this.node.addEventListener('mouseout', this.stopTrackMouse.bind(this));
	}

	stopTrackMouse()
	{
		if (!this.tracking)
		{
			return;
		}
		document.removeEventListener('mousemove', this.trackMouseHandle);

		this.active = false;
		setTimeout(() => {
			this.hideTooltip()
		}, 500);
		this.tracking = false;
	}

	trackMouse(e)
	{
		if (!this.tracking)
		{
			return;
		}

		const current = (
			e && e.pageX
				? {
					x: e.pageX,
					y: e.pageY,
				}
				: {
					x: e.clientX + document.body.scrollLeft,
					y: e.clientY + document.body.scrollTop,
				}
		);

		if (current.x < 0)
		{
			current.x = 0;
		}

		if (current.y < 0)
		{
			current.y = 0;
		}

		current.time = this.tracking;

		if (!this.active)
		{
			this.active = current;
		}
		else
		{
			if (
				this.active.x >= (current.x - 1) && this.active.x <= (current.x + 1)
				&& this.active.y >= (current.y - 1) && this.active.y <= (current.y + 1)
			)
			{
				if ((this.active.time + 20/*2sec*/) <= current.time)
				{
					this.showTooltip();
				}
			}
			else
			{
				this.active = current;
			}
		}
	}

	tickTimer()
	{
		if (!this.tracking)
		{
			return;
		}

		this.tracking++;
		if (this.active)
		{
			if ((this.active.time + 5/*0.5sec*/)  <= this.tracking)
			{
				this.showTooltip();
			}
		}

		setTimeout(() => {
			this.tickTimer();
		}, 100);
	}

	hideTooltip()
	{
		if (this.tracking)
		{
			return;
		}

		this.showOpacityEffect(1);
	}

	showOpacityEffect(bFade)
	{
		const steps = 3;
		const period = 1;
		const delta = 1 / steps;
		let i = 0;

		const intId = setInterval(() => {
			i++;
			if (i > steps)
			{
				clearInterval(intId);
				return;
			}

			const op = (
				bFade
					? 1 - i * delta
					: i * delta
			);

			if (this.DIV != null)
			{
				try
				{
					this.DIV.style.opacity = op;
				}
				catch(e)
				{
				}
				finally
				{
					if (
						!bFade
						&& i == 1
					)
					{
						this.DIV.classList.add('ui-tooltip-info-shadow-show');
						this.DIV.style.display = 'block';
					}

					if (
						bFade
						&& i == steps
						&& this.DIV
					)
					{
						this.DIV.classList.remove('ui-tooltip-info-shadow-show');
						this.DIV.classList.add('ui-tooltip-info-shadow-hide');
						setTimeout(() => {
							this.DIV.style.display = 'none';
						}, 500);
					}

					if (bFade)
					{
						EventEmitter.emit('onTooltipHide', new BaseEvent({
							compatData: [ this ],
						}));
					}
				}
			}
		}, period);
	}

	showTooltip()
	{
		const old = document.getElementById(`${Tooltip.getIdPrefix()}${this.userId}`);

		if (
			Tooltip.getDisabledStatus()
			|| (
				old
				&& old.classList.contains('ui-tooltip-info-shadow-show')
			)
		)
		{
			return;
		}

		if (
			null == this.DIV
			&& null == this.ROOT_DIV
		)
		{
			this.ROOT_DIV = document.body.appendChild(document.createElement('DIV'));
			this.ROOT_DIV.style.position = 'absolute';

			BX.ZIndexManager.register(this.ROOT_DIV);

			this.DIV = this.ROOT_DIV.appendChild(document.createElement('DIV'));
			this.DIV.className = 'bx-ui-tooltip-info-shadow';

			this.DIV.style.width = `${this.width}px`;
		}

		let left = this.coordsLeft;
		const top = this.coordsTop + 30;
		const arScroll = BX.GetWindowScrollPos();
		const body = document.body;

		this.hMirror = false;
		this.vMirror = ((top - arScroll.scrollTop - 50) < 0);

		if ((body.clientWidth + arScroll.scrollLeft) < (left + this.width))
		{
			left = this.anchorRight - this.width;
			this.hMirror = true;
		}

		this.ROOT_DIV.style.left = `${parseInt(left)}px`;
		this.ROOT_DIV.style.top = `${parseInt(top)}px`;

		BX.ZIndexManager.bringToFront(this.ROOT_DIV);

		this.ROOT_DIV.addEventListener('click', (e) => { e.stopPropagation(); });

		if (Type.isStringFilled(this.rootClassName))
		{
			this.ROOT_DIV.className = this.rootClassName;
		}

		const loader = (
			Type.isStringFilled(this.loader)
				? this.loader
				: Tooltip.getLoader()
		);

		// create stub
		let stubCreated = false;

		if ('' == this.DIV.innerHTML)
		{
			stubCreated = true;

			if (this.version >= 3)
			{
				ajax.runComponentAction('bitrix:ui.tooltip', 'getData', {
					mode: 'ajax',
					data: {
						userId: this.userId,
						params: (!Type.isUndefined(this.params) ? this.params : {}),
					}
				}).then((response) => {

					const detailUrl = (Type.isStringFilled(response.data.user.detailUrl) ? response.data.user.detailUrl : '');
					let cardUserName = '';

					if (Type.isStringFilled(response.data.user.nameFormatted))
					{
						const {nameFormatted = ''} = response.data.user;

						if (Type.isStringFilled(detailUrl))
						{
							cardUserName = `
											<a
												class="bx-ui-tooltip-user-name"
												title="${nameFormatted}"
												href="${detailUrl}"
											>
												${response.data.user.nameFormatted}
											</a>`
							;
						}
						else
						{
							cardUserName = `
											<span 
												class="bx-ui-tooltip-user-name"
												title="${nameFormatted}"
											>
												response.data.user.nameFormatted
											</span>`
							;
						}
					}

					let cardFields = '<div class="bx-ui-tooltip-info-data-info">';
					Object.keys(response.data.user.cardFields).forEach((fieldCode) => {
						cardFields += `<span class="bx-ui-tooltip-field-row bx-ui-tooltip-field-row-${fieldCode.toLowerCase()}"><span class="bx-ui-tooltip-field-name">${response.data.user.cardFields[fieldCode].name}</span>: <span class="bx-ui-tooltip-field-value">${response.data.user.cardFields[fieldCode].value}</span></span>`;
					});
					cardFields += '</div>';

					const cardFieldsClassName = (
						parseInt(Loc.getMessage('USER_ID')) > 0
						&& response.data.currentUserPerms.operations.videocall
							? 'bx-ui-tooltip-info-data-cont-video'
							: 'bx-ui-tooltip-info-data-cont'
					);
					cardFields = `<div id="bx_user_info_data_cont_${response.data.user.id}" class="${cardFieldsClassName}">${cardFields}</div>`;

					let photo = '';
					let photoClassName = 'bx-ui-tooltip-info-data-photo no-photo';

					if (Type.isStringFilled(response.data.user.photo))
					{
						photo = response.data.user.photo;
						photoClassName = 'bx-ui-tooltip-info-data-photo';
					}

					photo = (
						Type.isStringFilled(detailUrl)
							? `<a href="${detailUrl}" class="${photoClassName}">${photo}</a>`
							: `<span class="${photoClassName}">${photo}</span>`
					);

					let toolbar = '';
					let toolbar2 = '';

					if (
						parseInt(Loc.getMessage('USER_ID')) > 0
						&& response.data.user.active
						&& response.data.user.id != Loc.getMessage('USER_ID')
						&& response.data.currentUserPerms.operations.message
					)
					{
						toolbar2 += `<li class="bx-icon bx-icon-message"><span onclick="return BX.Messenger.Public.openChat(${response.data.user.id});">${Loc.getMessage('MAIN_UL_TOOLBAR_MESSAGES_CHAT')}</span></li>`;
						toolbar2 += `<li id="im-video-call-button${response.data.user.id}" class="bx-icon bx-icon-video"><span onclick="return BX.tooltip.openCallTo(${response.data.user.id});">${Loc.getMessage('MAIN_UL_TOOLBAR_VIDEO_CALL')}</span></li>`;
						toolbar2 += `<script>Event.ready(() => { BX.tooltip.checkCallTo("im-video-call-button${response.data.user.id}"); };</script>`;
					}

					toolbar2 = (Type.isStringFilled(toolbar2) ? `<div class="bx-ui-tooltip-info-data-separator"></div><ul>${toolbar2}</ul>` : '');

					if (response.data.user.hasBirthday)
					{
						toolbar += `<li class="bx-icon bx-icon-birth">${Loc.getMessage('MAIN_UL_TOOLBAR_BIRTHDAY')}</li>`;
					}

					if (response.data.user.hasHonour)
					{
						toolbar += `<li class="bx-icon bx-icon-featured">${Loc.getMessage('MAIN_UL_TOOLBAR_HONORED')}</li>`;
					}

					if (response.data.user.hasAbsence)
					{
						toolbar += `<li class="bx-icon bx-icon-away">${Loc.getMessage('MAIN_UL_TOOLBAR_ABSENT')}</li>`;
					}

					toolbar = (Type.isStringFilled(toolbar) ? `<ul>${toolbar}</ul>` : '');

					this.insertData({
						RESULT: {
							Name: cardUserName,
							Position: (Type.isStringFilled(response.data.user.position) ? response.data.user.position : ''),
							Card: cardFields,
							Photo: photo,
							Toolbar: toolbar,
							Toolbar2: toolbar2,
						},
					});
					this.adjustPosition();

				}, () => {});
			}
			else
			{
				const url = loader +
					(loader.indexOf('?') >= 0 ? '&' : '?') +
					`MODE=UI&MUL_MODE=INFO&USER_ID=${this.userId}` +
					`&site=${(Loc.getMessage('SITE_ID') || '')}` +
					`&version=${this.version}` +
					(
						!Type.isUndefined(this.params)
						&& !Type.isUndefined(this.params.entityType)
						&& Type.isStringFilled(this.params.entityType)
							? `&entityType=${this.params.entityType}`
							: ''
					) +
					(
						!Type.isUndefined(this.params)
						&& !Type.isUndefined(this.params.entityId)
						&& parseInt(this.params.entityId) > 0
							? `&entityId=${parseInt(this.params.entityId)}`
							: ''
					);

				ajax.get(url, (data) => {
					this.insertData(data);
					this.adjustPosition();
				});
			}

			this.DIV.id = `${Tooltip.getIdPrefix()}${this.userId}`;

			this.DIV.innerHTML = '<div class="bx-ui-tooltip-info-wrap">'
				+ '<div class="bx-ui-tooltip-info-leftcolumn">'
				+ `<div class="bx-ui-tooltip-photo" id="${Tooltip.getIdPrefix()}photo-${this.userId}"><div class="bx-ui-tooltip-info-data-loading">${Loc.getMessage('JS_CORE_LOADING')}</div></div>`
				+ '</div>'
				+ '<div class="bx-ui-tooltip-info-data">'
				+ `<div id="${Tooltip.getIdPrefix()}data-card-${this.userId}"></div>`
				+ '<div class="bx-ui-tooltip-info-data-tools">'
				+ `<div class="bx-ui-tooltip-tb-control bx-ui-tooltip-tb-control-left" id="${Tooltip.getIdPrefix()}toolbar-${this.userId}"></div>`
				+ `<div class="bx-ui-tooltip-tb-control bx-ui-tooltip-tb-control-right" id="${Tooltip.getIdPrefix()}toolbar2-${this.userId}"></div>`
				+ '<div class="bx-ui-tooltip-info-data-clear"></div>'
				+ '</div>'
				+ '</div>'
				+ '</div><div class="bx-ui-tooltip-info-bottomarea"></div>';
		}

		this.DIV.className = 'bx-ui-tooltip-info-shadow';
		this.classNameAnim = 'bx-ui-tooltip-info-shadow-anim';
		this.classNameFixed = 'bx-ui-tooltip-info-shadow';

		if (this.hMirror && this.vMirror)
		{
			this.DIV.className = 'bx-ui-tooltip-info-shadow-hv';
			this.classNameAnim = 'bx-ui-tooltip-info-shadow-hv-anim';
			this.classNameFixed = 'bx-ui-tooltip-info-shadow-hv';
		}
		else
		{
			if (this.hMirror)
			{
				this.DIV.className = 'bx-ui-tooltip-info-shadow-h';
				this.classNameAnim = 'bx-ui-tooltip-info-shadow-h-anim';
				this.classNameFixed = 'bx-ui-tooltip-info-shadow-h';
			}

			if (this.vMirror)
			{
				this.DIV.className = 'bx-ui-tooltip-info-shadow-v';
				this.classNameAnim = 'bx-ui-tooltip-info-shadow-v-anim';
				this.classNameFixed = 'bx-ui-tooltip-info-shadow-v';
			}
		}

		this.DIV.style.display = 'block';

		if (!stubCreated)
		{
			this.adjustPosition();
		}

		this.showOpacityEffect(0);

		document.getElementById(`${Tooltip.getIdPrefix()}${this.userId}`).onmouseover = () => {
			this.startTrackMouse(this);
		};

		document.getElementById(`${Tooltip.getIdPrefix()}${this.userId}`).onmouseout = () => {
			this.stopTrackMouse(this);
		};

		EventEmitter.emit('onTooltipShow', new BaseEvent({
			compatData: [ this ],
		}));
	}

	adjustPosition()
	{
		const tooltipCoords = BX.pos(this.DIV);

		if (this.vMirror)
		{
			this.ROOT_DIV.style.top = `${parseInt(this.anchorTop + 13)}px`;
		}
		else
		{
			this.ROOT_DIV.style.top = `${parseInt(this.anchorTop - tooltipCoords.height - 13 + 12)}px`; // 12 - bottom block
		}
	}

	insertData(data)
	{
		if (
			null != data
			&& (
				this.version >= 3
				|| data.length > 0
			)
		)
		{
			if (this.version >= 3)
			{
				this.INFO = data;
			}
			else
			{
				eval(`this.INFO = ${data}`);
			}

			const cardEl = document.getElementById(`${Tooltip.getIdPrefix()}data-card-${this.userId}`);
			cardEl.innerHTML = '';
			if (Type.isStringFilled(this.INFO.RESULT.Name))
			{
				cardEl.innerHTML += `<div class="bx-ui-tooltip-user-name-block">${this.INFO.RESULT.Name}</div>`;
			}
			if (Type.isStringFilled(this.INFO.RESULT.Position))
			{
				cardEl.innerHTML += `<div class="bx-ui-tooltip-user-position">${this.INFO.RESULT.Position}</div>`;
			}
			cardEl.innerHTML += this.INFO.RESULT.Card;

			const photoEl = document.getElementById(`${Tooltip.getIdPrefix()}photo-${this.userId}`);
			photoEl.innerHTML = this.INFO.RESULT.Photo;

			const toolbarEl = document.getElementById(`${Tooltip.getIdPrefix()}toolbar-${this.userId}`);
			toolbarEl.innerHTML = this.INFO.RESULT.Toolbar;

			const toolbar2El = document.getElementById(`${Tooltip.getIdPrefix()}toolbar2-${this.userId}`);
			toolbar2El.innerHTML = this.INFO.RESULT.Toolbar2;

			if (Type.isArray(this.INFO.RESULT.Scripts))
			{
				this.INFO.RESULT.Scripts.forEach((script) => {
					eval(script);
				});
			}

			EventEmitter.emit('onTooltipInsertData', new BaseEvent({
				compatData: [ this ],
			}));
		}
	}
}