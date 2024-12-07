(function() {
	BX.namespace('BX.MobileApp');

	BX.MobileApp.Gesture = {
		addLongTapListener(node, callback, customDuration)
		{
			var touchDuration = customDuration || 500;
			var timerInterval;
			var preventTouchEnd = false;

			function timer(interval, targetNode)
			{
				timerInterval = setTimeout(() => {
					tapHold(targetNode);
				}, interval);
			}

			var startPosition = { x: 0, y: 0 };

			function touchStart(e)
			{
				preventTouchEnd = false;
				if (e.target.tagName === 'A')
				{
					return;
				}

				startPosition = { x: e.changedTouches[0].clientX, y: e.changedTouches[0].clientY };
				timer(touchDuration, e.target);
			}

			function touchEnd(e)
			{
				startPosition = { x: 0, y: 0 };
				clearTimeout(timerInterval);
				if (preventTouchEnd)
				{
					e.preventDefault();
				}
			}

			function touchMove(e)
			{
				var x = e.changedTouches[0].clientX;
				var y = e.changedTouches[0].clientY;
				if (Math.abs(startPosition.x - x) > 5 || Math.abs(startPosition.y - y) > 5)
				{
					startPosition = { x: 0, y: 0 };
					clearTimeout(timerInterval);
				}
			}

			function tapHold(targetNode)
			{
				clearTimeout(timerInterval);
				preventTouchEnd = true;
				if (callback)
				{
					callback(targetNode);
				}
			}

			BX.addClass(node, 'long-tap-block');
			node.addEventListener('touchstart', touchStart);
			node.addEventListener('touchend', touchEnd);
			node.addEventListener('touchmove', touchMove);
			BX.addCustomEvent('onNativeTouchEnd', () => {
				clearTimeout(timerInterval);
			});
		},
	};
})();
