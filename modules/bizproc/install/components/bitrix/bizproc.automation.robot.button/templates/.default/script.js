;(function() {
	const namespace = BX.namespace('BX.Bizproc.Automation');

	if (namespace.RobotButton)
	{
		return;
	}

	class RobotButton
	{
		static buttonId;

		constructor(params)
		{
			RobotButton.buttonId = params.buttonId;
		}

		static getRobotButton()
		{
			if (!this.buttonId)
			{
				return;
			}

			const button = BX.UI.ButtonManager.createByUniqId(this.buttonId);
			if (!button)
			{
				return;
			}

			return button;
		}

		static setButtonCounter(unviewedCount = 0)
		{
			const button = this.getRobotButton();

			if (!button)
			{
				return;
			}

			if (!unviewedCount || unviewedCount <= 0)
			{
				button.setRightCounter(null);
				return;
			}

			let counter = button.getRightCounter();

			if (!counter)
			{
				const counterOptions = {
					style: BX.UI.CounterStyle.FILLED_SUCCESS,
					value: unviewedCount,
				};
				button.setRightCounter(counterOptions);
				return;
			}

			counter.setValue(unviewedCount);
		}

		static onEntityViewed(eventData)
		{
			const unviewedCount = eventData.unviewedEntitiesCount ?? 0;

			this.setButtonCounter(unviewedCount);
		}

		static updateButtonCounter(availableNewRobots)
		{
			if (!BX.Bizproc || !BX.Bizproc.LocalSettings.Settings || !Array.isArray(availableNewRobots))
			{
				return;
			}

			const settings = new BX.Bizproc.LocalSettings.Settings('robot-selector');
			const viewedRobots = settings.get('viewedNewRobotIds', []);

			const totalAvailable = availableNewRobots.length;
			let viewedInScopeCount = 0;

			if (Array.isArray(viewedRobots) && viewedRobots.length > 0)
			{
				const viewedBaseRobots = new Set(viewedRobots.map(id => String(id).toLowerCase()));
				availableNewRobots.forEach(id => {
					if (viewedBaseRobots.has(String(id).toLowerCase()))
					{
						viewedInScopeCount++;
					}
				});
			}
			const unviewedCount = totalAvailable - viewedInScopeCount;

			this.setButtonCounter(unviewedCount);
		}
	}

	namespace.RobotButton = RobotButton;
})();
