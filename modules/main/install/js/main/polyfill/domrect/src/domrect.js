if (
	!window.DOMRect
	|| typeof DOMRect.prototype.toJSON !== 'function'
	|| typeof DOMRect.fromRect !== 'function'
)
{
	window.DOMRect = class DOMRect
	{
		constructor(x, y, width, height)
		{
			this.x = x || 0;
			this.y = y || 0;
			this.width = width || 0;
			this.height = height || 0;
		}

		static fromRect(otherRect)
		{
			return new DOMRect(otherRect.x, otherRect.y, otherRect.width, otherRect.height);
		}

		get top()
		{
			return this.y;
		}

		get left()
		{
			return this.x;
		}

		get right()
		{
			return this.x + this.width;
		}

		get bottom()
		{
			return this.y + this.height;
		}

		toJSON()
		{
			return {
				top: this.top,
				left: this.left,
				right: this.right,
				bottom: this.bottom,
				width: this.width,
				height: this.height,
				x: this.x,
				y: this.y,
			};
		}
	};
}