/**
 * Array.prototype.fill polyfill
 */
;(function() {
	'use strict';

	if (typeof Array.prototype.fill !== 'function')
	{
		Object.defineProperty(Array.prototype, 'fill', {
			enumerable: false,
			value: function(value, start, end)
			{
				if (this === null)
				{
					throw new TypeError('Cannot read property \'fill\' of null');
				}

				var object = Object(this);
				if (typeof end !== 'number' && typeof end !== 'undefined')
				{
					return object;
				}

				var length = object.length;

				var startLength = typeof start === 'number'? start: 0;
				var startPosition = startLength < 0? Math.max(length + startLength, 0): Math.min(startLength, length);


				var endLength = typeof end === 'number'? end: length;
				var endPosition = endLength < 0? Math.max(length + endLength, 0): Math.min(endLength, length);

				while (startPosition < endPosition)
				{
					object[startPosition] = value;
					startPosition++;
				}

				return object;
			},
		});
	}

})();