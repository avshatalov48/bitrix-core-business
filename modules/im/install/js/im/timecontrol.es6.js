BX.namespace('BX.ImTimeControl');

(function() {

	var ImTimeControl = function()
	{
		this.stack = [];

		this.init();
	};
	ImTimeControl.prototype =
	{
		init: function()
		{
			if (typeof BX.TimeControl == 'undefined')
				return false;

			if (!BX.desktop)
			{
				setTimeout(() => {
					this.init();
				}, 1000);
				return false;
			}

			this.init = true;

			this.pullEvent();

			clearInterval(this.workerTimeout);
			this.workerTimeout = setInterval(this.worker.bind(this), 5000);

			BX.desktop.addCustomEvent('imTimeControlCloseWindow', () => { this.blockExecute = false; });

			return true;
		},

		addToStack: function(params)
		{
			this.stack.push(params);
		},

		execute: function(params)
		{
			this.blockExecute = true;

			var absenceId = parseInt(params.absenceId);
			var dateStart = params.dateStart;
			var dateFinish = params.dateFinish;
			var duration = parseInt(params.duration);
			var calendarCheckboxDefault = false;

			console.info('ImTimeControl.execute: ', [absenceId, duration, dateStart, dateFinish]);

			BX.desktop.openTopmostWindow('<div id="timecontrol"></div>', `
				BX.desktop.setWindowName('timecontrol');
				BX.desktop.setWindowSize({ Width: 740, Height: 335 });
				BX.desktop.setWindowSize({ Width: 740, Height: 335 });
				BX.desktop.setWindowMinSize({ Width: 740, Height: 335 });
				BX.desktop.setWindowPosition({X: STP_CENTER, Y: STP_VCENTER, Width: 740, Height: 335});
				BX.desktop.windowCommand('focus');
				
				var timecontrol = new BX.TimeControl({
					placeholder: BX('timecontrol'),
					reportId: ${absenceId},
					absenceTime: ${duration},
					absenceStart: '${dateStart}',
					absenceEnd: '${dateFinish}',
					userAvatarUrl: '${BXIM.messenger.users[BXIM.userId].avatar}',
					userGender: '${BXIM.messenger.users[BXIM.userId].gender}',
					calendarCheckboxDefault: ${calendarCheckboxDefault},
					showConfirmModal: true
				});
				timecontrol.success(() => {
					BX.desktop.onCustomEvent('imTimeControlCloseWindow', []);
					BX.desktop.windowCommand('close');
				});
				timecontrol.fail(() => {
					BX.desktop.login(() => {});
				});
				
				BX.bind(window, "beforeunload", () => {
					BX.desktop.onCustomEvent('imTimeControlCloseWindow', []);
				});
				
				// Workaround for topmost window bug
				if (BX.browser.IsMac())
				{
					setTimeout(() => BX.style(BX('timecontrol'), 'padding-top', '3px'), 50);
				}
				else if (/Brick/.test(navigator.userAgent))
				{
					setTimeout(() => BX.style(BX('timecontrol'), 'padding-top', '4px'), 50);
				}
				else
				{
					setTimeout(() => BX.style(BX('timecontrol'), 'padding-top', '4px'), 50);
				}
			`);

			return true;
		},

		worker: function(params)
		{
			if (this.blockExecute || this.stack.length <= 0)
			{
				return true;
			}

			for (var i = 0; i < this.stack.length; i++)
			{
				if (!this.stack[i])
				{
					continue;
				}

				if (BX.browser.IsMac() || BX.desktop.enableInVersion(41))
				{
					this.execute(this.stack[i]);
				}
				else
				{
					var elementOfStack = BX.clone(this.stack[i]);
					setTimeout(() => this.execute(elementOfStack), 10000);
				}

				this.stack = this.stack.filter(element => element.absenceId != this.stack[i].absenceId);
				break;
			}

			return true;
		},

		pullEvent: function()
		{
			BX.addCustomEvent("onPullEvent-timeman", (command, params) =>
			{
				if (command == 'timeControlCommitAbsence')
				{
					this.addToStack(params)
				}
			});

			return true;
		}
	};

	BX.ImTimeControl = new ImTimeControl();
})();