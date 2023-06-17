;(function(window){
	window.ViewEventManager = function(config)
	{
		this.id = config.id;
		this.config = config;
		this.userId = BX.message('sonetLCurrentUserID');

		this.viewEventUrl = this.config.viewEventUrlTemplate;
		this.viewEventUrl = this.viewEventUrl.replace(/#user_id#/ig, this.userId);
		this.viewEventUrl = this.viewEventUrl.replace(/#event_id#/ig, this.config.eventId);
		this.bx = window.top.BX || window.BX;

		if (this.config.EVENT.DATE_FROM && this.config.EVENT.RRULE)
		{
			this.viewEventUrl += '&EVENT_DATE=' + BX.formatDate(BX.parseDate(this.config.EVENT.DATE_FROM), BX.message('FORMAT_DATE'));
		}

		this.getCalendarUtils().then(function(){
			this.bx.ready(this.Init.bind(this));
		}.bind(this));
	};

	window.ViewEventManager.prototype = {
		Init: function()
		{
			this.pViewIconLink = BX('feed-event-view-icon-link-' + this.id);
			this.pViewLink = BX('feed-event-view-link-' + this.id);
			this.pViewLink.href = this.pViewIconLink.href = this.viewEventUrl;
			this.pFrom = BX('feed-event-view-from-' + this.id);

			var event = this.config.EVENT;
			if (event.DATE_FROM && event.DATE_TO)
			{
				event.dateFrom = BX.parseDate(event.DATE_FROM);
				event.dateTo = BX.parseDate(event.DATE_TO);
				event.DT_FROM_TS = event.dateFrom.getTime();
				event.DT_TO_TS = event.dateTo.getTime();
				if (event.DT_SKIP_TIME !== "Y")
				{
					event.DT_FROM_TS -= event['~USER_OFFSET_FROM'] * 1000;
					event.DT_TO_TS -= event['~USER_OFFSET_TO'] * 1000;
				}
				this.pFrom.innerHTML = this.GetFromHtml(event.DT_FROM_TS, event.DT_SKIP_TIME);
			}
			else // Copatibility with old records
			{
				this.pFrom.innerHTML = this.GetFromHtml(BX.date.getBrowserTimestamp(event.DT_FROM_TS), event.DT_SKIP_TIME);
			}

			var pViewTzHint = BX('feed-event-tz-hint-' + this.id);
			if (pViewTzHint)
			{
				new BX.CHint({parent: pViewTzHint, hint: pViewTzHint.getAttribute('data-bx-hint')});
			}

			this.InitPopups();

			// Invite controls
			this.ShowUserStatus(event.IS_MEETING && this.config.attendees[this.userId] ? this.config.attendees[this.userId].STATUS : false);

			if (BX('bx-feed-cal-view-files-' + this.id))
			{
				BX.viewElementBind(
					'bx-feed-cal-view-files-' + this.id,
					{showTitle: true},
					function(node){
						return BX.type.isElementNode(node) && (node.getAttribute('data-bx-viewer') || node.getAttribute('data-bx-image'));
					}
				);
			}
		},

		InitPopups: function()
		{
			var _this = this;
			var rand = Math.round(Math.random() * 100000);

			this.pMoreAttLinkY = BX('feed-event-more-att-link-y-' + this.id);
			this.pMoreAttLinkN = BX('feed-event-more-att-link-n-' + this.id);
			this.pMoreAttPopupContY = BX('feed-event-more-attendees-y-' + this.id);
			this.pMoreAttPopupContN = BX('feed-event-more-attendees-n-' + this.id);

			if (this.pMoreAttLinkY && this.pMoreAttPopupContY)
			{
				this.pMoreAttLinkY.onclick = function()
				{
					if (_this.popupNotifyMoreY)
					{
						_this.popupNotifyMoreY.destroy();
					}
					_this.popupNotifyMoreY = new BX.PopupWindow('bx_event_attendees_window_y_' + _this.id + '_' + rand, _this.pMoreAttLinkY,
						{
							zIndex: 100,
							lightShadow : true,
							offsetTop: -2,
							offsetLeft: 3,
							autoHide: true,
							closeByEsc: true,
							bindOptions: {position: "top"},
							content : _this.pMoreAttPopupContY
						}
					);
					_this.popupNotifyMoreY.setAngle({});
					_this.popupNotifyMoreY.show();
					_this.pMoreAttPopupContY.style.display = "block";
				}
			}

			if (this.pMoreAttLinkN && this.pMoreAttPopupContN)
			{
				this.pMoreAttLinkN.onclick = function()
				{
					if (_this.popupNotifyMoreN)
					{
						_this.popupNotifyMoreN.destroy();
					}

					_this.popupNotifyMoreN = new BX.PopupWindow('bx_event_attendees_window_n_' + _this.id + '_' + rand, _this.pMoreAttLinkN,
						{
							zIndex: 100,
							lightShadow : true,
							offsetTop: -2,
							offsetLeft: 3,
							autoHide: true,
							closeByEsc: true,
							bindOptions: {position: "top"},
							content : _this.pMoreAttPopupContN
						}
					);
					_this.popupNotifyMoreN.setAngle({});
					_this.popupNotifyMoreN.show();
					_this.pMoreAttPopupContN.style.display = "block";
				}
			}
		},

		ShowAttendees: function(attendees, pRow, params)
		{
			pRow.style.display = attendees.length > 0 ? "" : "none";

			if (!pRow || attendees.length <= 0)
				return;

			var
				contentCell = pRow.cells[1],
				i,
				cnt = 0,
				att,
				avatarSize = this.config.AVATAR_SIZE,
				bShowAll = attendees.length <= this.config.ATTENDEES_SHOWN_COUNT_MAX,
				popupContent = '',
				attCellContent = '';

			for(i = 0; i < attendees.length; i++)
			{
				att = attendees[i];
				cnt++;
				if (!bShowAll && cnt > this.config.ATTENDEES_SHOWN_COUNT)
				{
					// Put to popup
					popupContent += '<a href="' + att.URL + '" target="_blank" class="bxcal-att-popup-img bxcal-att-popup-att-full">' +
						'<span class="bxcal-att-popup-avatar">' +
							(att.AVATAR ? '<img src="' + encodeURI(att.AVATAR) + '" width="' + avatarSize + '" height="' + avatarSize + '" class="bxcal-att-popup-img-not-empty" />' : '') +
						'</span>' +
						'<span class="bxcal-att-popup-name">' + BX.util.htmlspecialchars(att.DISPLAY_NAME) + '</span>' +
					'</a>';
				}
				else // Display avatar
				{
					attCellContent += '<a title="' + BX.util.htmlspecialchars(att.DISPLAY_NAME) + '" href="' + att.URL + '" target="_blank" class="bxcal-att-popup-img">' +
						'<span class="bxcal-att-popup-avatar">' +
							(att.AVATAR ? '<img src="' + encodeURI(att.AVATAR) + '" width="' + avatarSize + '" height="' + avatarSize + '" class="bxcal-att-popup-img-not-empty" />' : '') +
						'</span>' +
					'</a>';
				}
			}

			contentCell.innerHTML = attCellContent;

			if (!bShowAll && params.MORE_MESSAGE)
			{
				var prefix = params.prefix;
				contentCell.appendChild(BX.create("SPAN", {props: {id: "feed-event-more-att-link-" + prefix + "-" + this.id, className: "bxcal-more-attendees"}, text: params.MORE_MESSAGE}));
				contentCell.appendChild(BX.create("DIV", {props: {id: "feed-event-more-attendees-" + prefix + "-" + this.id, className: "bxcal-more-attendees-popup"}, style: {display: "none"}, html: popupContent}));
			}
		},

		ShowUserStatus: function(status)
		{
			var
				_this = this,
				inviteCont = BX('feed-event-invite-controls-' + this.id);

			if (!inviteCont)
			{
				return;
			}

			if (status && status !== 'H')
			{
				var rand = Math.round(Math.random() * 100000);
				inviteCont.className = 'feed-cal-view-inv-controls' + ' feed-cal-view-inv-controls-' + status.toLowerCase();

				if (status === 'Y')
				{
					var linkY = BX('feed-event-stat-link-y-' + this.id);
					linkY.onclick = function()
					{
						if (!_this.popupAccepted)
						{
							_this.popupAccepted = new BX.PopupWindow('bx_event_change_win_y_' + _this.id + '_' + rand, linkY,
							{
								zIndex: 200,
								lightShadow : true,
								offsetTop: -5,
								offsetLeft: (linkY.offsetWidth || 100) + 10,
								autoHide: true,
								closeByEsc: true,
								bindOptions: {position: "top"},
								content : BX('feed-event-stat-link-popup-y-' + _this.id)
							});
							_this.popupAccepted.setAngle({});
						}
						_this.popupAccepted.show();
					};

					if (_this.config.EVENT.RRULE || _this.config.EVENT.RECURRENCE_ID)
					{
						BX('feed-rec-decline-' + this.id).style.display = 'block';
						BX('feed-event-decline-2-' + this.id).style.display = 'none';

						BX('feed-rec-decline-this-' + this.id).onclick = function(){_this.Decline('this');};
						BX('feed-rec-decline-next-' + this.id).onclick = function(){_this.Decline('next');};
						BX('feed-rec-decline-all-' + this.id).onclick = function(){_this.Decline('all');};
					}
					else
					{
						BX('feed-event-decline-2-' + this.id).style.display = '';
						BX('feed-event-decline-2-' + this.id).onclick = BX.proxy(this.Decline, this);
						BX('feed-rec-decline-' + this.id).style.display = 'none';
					}
				}
				else if (status === 'N')
				{
					var linkN = BX('feed-event-stat-link-n-' + this.id);
					linkN.onclick = function(){
						if(!_this.popupDeclined)
						{
							_this.popupDeclined = new BX.PopupWindow('bx_event_change_win_n_' + _this.id + '_' + rand, linkN,
							{
								zIndex: 200,
								lightShadow : true,
								offsetTop: -5,
								offsetLeft: (linkN.offsetWidth || 100) + 10,
								autoHide: true,
								closeByEsc: true,
								bindOptions: {position: "top"},
								content : BX('feed-event-stat-link-popup-n-' + _this.id)
							});
							_this.popupDeclined.setAngle({});
						}
						_this.popupDeclined.show();
					};
					BX('feed-event-accept-2-' + this.id).onclick = BX.proxy(this.Accept, this);
				}
				else
				{
					BX('feed-event-accept-' + this.id).onclick = BX.proxy(this.Accept, this);
					BX('feed-event-decline-' + this.id).onclick = BX.proxy(this.Decline, this);
				}
			}
			else
			{
				inviteCont.style.display = 'none';
			}
		},

		SetStatus: function(status, recMode)
		{
			var _this = this;

			if (this.popupDeclined)
				this.popupDeclined.close();

			if (this.popupAccepted)
				this.popupAccepted.close();

			BX.ajax.get(
				this.config.actionUrl,
				{
					event_feed_action: status,
					sessid: BX.bitrix_sessid(),
					event_id: this.config.eventId,
					parent_id: this.config.EVENT.PARENT_ID || false,
					ajax_params: this.config.AJAX_PARAMS,
					reccurent_mode: recMode || false,
					current_date_from: this.config.EVENT.DATE_FROM
				},
				function(result)
				{
					setTimeout(function()
					{
						if (result.indexOf('#EVENT_FEED_RESULT_OK#') !== -1 && _this.config.EVENT.IS_MEETING)
						{
							_this.ShowUserStatus(status == 'accept' ? "Y" : "N");

							if (window.ViewEventManager.requestResult)
							{
								// Show or hide accepted row + show users
								_this.ShowAttendees(
									window.ViewEventManager.requestResult['ACCEPTED_ATTENDEES'],
									BX('feed-event-accepted-row-' + _this.id),
									window.ViewEventManager.requestResult['ACCEPTED_PARAMS']
								);

								// Show or hide declined row + show users
								_this.ShowAttendees(
									window.ViewEventManager.requestResult['DECLINED_ATTENDEES'],
									BX('feed-event-declined-row-' + _this.id),
									window.ViewEventManager.requestResult['DECLIINED_PARAMS']
								);
							}

							_this.InitPopups();
						}
					}, 150);
				}
			);
		},

		Accept: function()
		{
			return this.SetStatus('accept');
		},

		Decline: function(recMode)
		{
			return this.SetStatus('decline', recMode);
		},

		DeleteEvent: function()
		{
			if (!this.config.eventId || !confirm(this.config.EC_JS_DEL_EVENT_CONFIRM))
				return false;

			BX.ajax.get(
				this.config.actionUrl,
				{
					event_feed_action: 'delete_event',
					sessid: BX.bitrix_sessid(),
					event_id: this.config.eventId
				},
				function(result)
				{
					if (result.indexOf('#EVENT_FEED_RESULT_OK#') !== -1)
						BX.reload();
				}
			);
		},

		GetFromHtml: function(dateFromTimestamp, isFullDay)
		{
			var dateFormat = this.config.culture
				? this.config.culture.date_format
				: this.bx.date.convertBitrixFormat(BX.message('FORMAT_DATE'))
			;

			var fromDate = new Date(dateFromTimestamp);
			var html = this.bx.date.format([
				["today", "today"],
				["tommorow", "tommorow"],
				["yesterday", "yesterday"],
				["" , dateFormat]
			], fromDate);

			if (isFullDay !== 'Y')
			{
				html += ', ' + this.bx.date.format(this.bx.Calendar.Util.getTimeFormatShort(), fromDate);
			}

			return html;
		},

		getCalendarUtils: function()
		{
			return new Promise(function(reslve){
				if (this.bx.Calendar && this.bx.Calendar.Util)
				{
					reslve(this.bx.Calendar.Util);
				}
				else
				{
					var extensionName = 'calendar.util';
					this.bx.Runtime.loadExtension(extensionName)
						.then(function()
							{
								if (this.bx.Calendar.Util)
								{
									reslve(this.bx.Calendar.Util);
								}
								else
								{
									console.error('Extension ' + extensionName + ' not found');
								}
							}.bind(this)
						);
				}
			}.bind(this));
		},
	};

})(window);
