import { Loc } from 'main.core';
import { DateTimeFormat } from './date-time-format';

/**
 * Available units: `Y` - years, `m` - months, `d` - days, `H` - hours, `i` - minutes, `s` - seconds.
 */
type DurationFormatOptions = {
	format: string,
	style: 'long' | 'short',
};

const defaultOptions = {
	format: 'Y m d H i s',
	style: 'long',
};

export class DurationFormat
{
	constructor(milliseconds: number)
	{
		this.milliseconds = Math.abs(milliseconds);
	}

	static createFromSeconds(seconds: number): DurationFormat
	{
		return new DurationFormat(seconds * DurationFormat.getUnitDurations().s);
	}

	static createFromMinutes(minutes: number): DurationFormat
	{
		return new DurationFormat(minutes * DurationFormat.getUnitDurations().i);
	}

	static getUnitDurations(): { s: number, i: number, H: number, d: number, m: number, Y: number }
	{
		return {
			s: 1000,
			i: 60000,
			H: 3_600_000,
			d: 86_400_000,
			m: 2_678_400_000,
			Y: 31_536_000_000,
		};
	}

	get seconds(): number
	{
		return Math.floor(this.milliseconds / DurationFormat.getUnitDurations().s);
	}

	get minutes(): number
	{
		return Math.floor(this.milliseconds / DurationFormat.getUnitDurations().i);
	}

	get hours(): number
	{
		return Math.floor(this.milliseconds / DurationFormat.getUnitDurations().H);
	}

	get days(): number
	{
		return Math.floor(this.milliseconds / DurationFormat.getUnitDurations().d);
	}

	/**
	 * Considering month is 31 days
	 */
	get months(): number
	{
		return Math.floor(this.milliseconds / DurationFormat.getUnitDurations().m);
	}

	/**
	 * Considering year is 365 days
	 */
	get years(): number
	{
		return Math.floor(this.milliseconds / DurationFormat.getUnitDurations().Y);
	}

	/**
	 * @example new DurationFormat(5070000).format() // 1 hour, 24 minutes, 30 seconds
	 * @example new DurationFormat(5070000).format({ style: 'short' }) // 1 h 24 m 30 s
	 * @example new DurationFormat(5070000).format({ format: 'd H i' }) // 1 hour, 24 minutes
	 * @example new DurationFormat(5070000).format({ format: 'i s' }) // 84 minutes, 30 seconds
	 */
	format(formatOptions: DurationFormatOptions = defaultOptions): string
	{
		const options = { ...defaultOptions, ...formatOptions };
		const orderedUnits = Loc.getMessage('FD_UNIT_ORDER').split(' ');
		const separator = this.#getSeparator(options.style);

		const formatUnits = new Set(options.format.split(' '));
		const maxUnit = this.#getMaxUnit(options.format);

		return orderedUnits
			.filter((unit) => formatUnits.has(unit))
			.map((unit) => this.#formatUnit(unit, unit !== maxUnit, options.style))
			.filter((unit) => unit !== '')
			.join(separator)
		;
	}

	/**
	 * @example new DurationFormat(5070000).formatClosest() // 1 hour
	 * @example new DurationFormat(5070000).formatClosest({ format: 'i s' }) // 84 minutes
	 */
	formatClosest(formatOptions: DurationFormatOptions = defaultOptions): string
	{
		const options = { ...defaultOptions, ...formatOptions };
		const maxUnit = this.#getMaxUnit(options.format);

		return this.#formatUnit(maxUnit, false, options.style);
	}

	#getSeparator(style: string): string
	{
		if (style === 'short')
		{
			return Loc.getMessage('FD_SEPARATOR_SHORT').replaceAll('&#32;', ' ');
		}

		return Loc.getMessage('FD_SEPARATOR').replaceAll('&#32;', ' ');
	}

	#getMaxUnit(format: string): string
	{
		const formatUnits = new Set(format.split(' '));
		const units = Object.entries(DurationFormat.getUnitDurations()).filter(([unit]) => formatUnits.has(unit));

		return units.reduce((closestDuration, unitDuration) => {
			const whole = Math.floor(this.milliseconds / unitDuration[1]) >= 1;
			const max = unitDuration[1] > closestDuration[1];

			return (whole && max) ? unitDuration : closestDuration;
		}, units[0])[0];
	}

	#formatUnit(unitStr: string, mod: boolean, style: string): string
	{
		const value = mod ? this.#getUnitPropertyModByFormat(unitStr) : this.#getUnitPropertyByFormat(unitStr);
		if (mod && value === 0)
		{
			return '';
		}

		const now = Date.now() / 1000;
		const unitDuration = value * this.#getUnitDuration(unitStr) / 1000;
		const format = style === 'short' ? `${unitStr}short` : `${unitStr}diff`;

		return DateTimeFormat.format(format, now - unitDuration, now);
	}

	#getUnitPropertyModByFormat(unitStr: string): number
	{
		const propsMod = {
			s: this.seconds % 60,
			i: this.minutes % 60,
			H: this.hours % 24,
			d: this.days % 31,
			m: this.months % 12,
			Y: this.years,
		};

		return propsMod[unitStr];
	}

	#getUnitPropertyByFormat(unitStr: string): number
	{
		const props = { s: this.seconds, i: this.minutes, H: this.hours, d: this.days, m: this.months, Y: this.years };

		return props[unitStr];
	}

	#getUnitDuration(unitStr: string): number
	{
		return DurationFormat.getUnitDurations()[unitStr];
	}
}
