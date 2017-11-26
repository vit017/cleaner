(function(){

	BX.namespace("BX.MobileApp");

	BX.MobileApp.Gesture = {
		addLongTapListener:function (node, callback)
		{
			var touchDuration = 100;
			var timerInterval;

			function timer(interval)
			{
				interval--;

				if (interval >= 0)
				{
					timerInterval = setTimeout(function ()
					{
						timer(interval);
					});
				}
				else
				{
					tapHold();
				}
			}

			var startPostition = {x: 0, y: 0};

			function touchStart(e)
			{
				if (e.target.tagName === "A")
				{
					return;
				}

				startPostition = {x: e.changedTouches[0].clientX, y: e.changedTouches[0].clientY};
				timer(touchDuration);
			}

			function touchEnd()
			{
				startPostition = {x: 0, y: 0};
				clearTimeout(timerInterval);
			}

			function touchMove(e)
			{
				var x = e.changedTouches[0].clientX;
				var y = e.changedTouches[0].clientY;
				if (Math.abs(startPostition.x - x) > 5 || Math.abs(startPostition.y - y) > 5)
				{
					startPostition = {x: 0, y: 0};
					clearTimeout(timerInterval);
				}
			}

			function tapHold()
			{
				clearTimeout(timerInterval);
				if (callback)
				{
					callback(node);
				}

			}

			BX.addClass(node, "long-tap-block");
			node.addEventListener('touchstart', touchStart);
			node.addEventListener('touchend', touchEnd);
			node.addEventListener('touchmove', touchMove);
		}
	};

})();