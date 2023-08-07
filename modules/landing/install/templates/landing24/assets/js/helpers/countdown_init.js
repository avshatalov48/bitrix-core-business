;(function ($)
{
	"use strict";

	BX.addCustomEvent("BX.Landing.Block:init", function (event)
	{
		var selector = event.makeRelativeSelector(".js-countdown");
		var nodes = event.block.querySelectorAll(selector);
		if (nodes.length > 0)
		{
			countdownInit(selector);
		}
	});


	BX.addCustomEvent("BX.Landing.Block:Node:updateAttr", function (event)
	{

		var selector = event.makeRelativeSelector(".js-countdown");
		var nodes = event.block.querySelectorAll(selector);
		if (
			nodes.length > 0 &&
			'data' in event &&
			'data-end-date' in event.data
		)
		{
			countdownInit(selector);
		}
	});

	BX.addCustomEvent("BX.Landing.Block:Card:add", function (event)
	{
		var selector = event.makeRelativeSelector(".js-countdown");
		var nodes = event.block.querySelectorAll(selector);
		if (nodes.length > 0)
		{
			countdownInit(selector);
		}
	});

	function countdownInit(selector)
	{
		var countdowns = $.HSCore.components.HSCountdown.init(selector, {
			yearsElSelector: '.js-cd-years',
			monthElSelector: '.js-cd-month',
			daysElSelector: '.js-cd-days',
			hoursElSelector: '.js-cd-hours',
			minutesElSelector: '.js-cd-minutes',
			secondsElSelector: '.js-cd-seconds',
		});

		countdowns.on('update.countdown', countdownUpdateHandler);
	}

	function countdownUpdateHandler(e)
	{
		var $this = $(this);
		if ($this.data('days-expired-classes'))
		{
			if(e.offset.totalDays <= 0)
			{
				$this.addClass($this.data('days-expired-classes'));
				$this.off('update.countdown', countdownUpdateHandler);
			}
			else {
				$this.removeClass($this.data('days-expired-classes'));
			}
		}
	}

})(window.jQueryLanding || jQuery);