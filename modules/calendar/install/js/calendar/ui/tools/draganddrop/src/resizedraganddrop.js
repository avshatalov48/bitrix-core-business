import DragAndDropInterface from './draganddropinterface';

/**
 * @implements DragAndDropInterface
 */
export class ResizeDragAndDrop implements DragAndDropInterface
{

	constructor(getDateByPos, getPosByDate)
	{
		this.getDateByPos = getDateByPos;
		this.getPosByDate = getPosByDate;
	}

	getFinalFrom()
	{
		return this.from;
	}

	getFinalTo()
	{
		return this.to;
	}

	onDragStart(timeInterval, minSize = 0, isStartResizer = false)
	{
		this.from = timeInterval.from;
		this.to = timeInterval.to;
		this.isStartResizer = isStartResizer;
		this.direction = isStartResizer ? -1 : 1;

		this.positionStart = this.getPosByDate(this.from);
		this.positionEnd = this.getPosByDate(this.to);
		this.size = this.positionEnd - this.positionStart;
		this.minSize = minSize;
	}

	getDragBoundary(dy)
	{
		const size = Math.max(this.size + dy * this.direction, this.minSize);

		if (this.isStartResizer)
		{
			this.positionStart = this.positionEnd - size;
			const timeFrom = this.getDateByPos(this.positionStart);
			this.from.setHours(timeFrom.getHours(), timeFrom.getMinutes(), 0, 0);
		}
		else
		{
			this.positionEnd = this.positionStart + size;
			const timeTo = this.getDateByPos(this.positionEnd);
			this.to.setHours(timeTo.getHours(), timeTo.getMinutes(), 0, 0);
		}

		return { from: this.from, to: this.to, position: this.positionStart, size };
	}

}
