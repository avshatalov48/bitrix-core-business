import './vueDatePick.css';

const Format = {
    re: /[,.\- :\/\\]/,
    year: 'YYYY',
    month: 'MM',
    day: 'DD',
    hours: 'HH',
    hours12: 'H',
    hoursZeroFree: 'GG',
    hoursZeroFree12: 'G',
    minutes: 'MI',
    seconds: 'SS',
    ampm: 'TT',
    ampmLower: 'T',
    format (date, dateFormat)
    {
        let hours12 = date.getHours();
        if (hours12 === 0)
        {
            hours12 = 12;
        }
        else if (hours12 > 12)
        {
            hours12 -= 12;
        }
        let ampm = date.getHours() > 11 ? 'PM' : 'AM';
        return dateFormat.replace(this.year, () => date.getFullYear())
            .replace(this.month, match => paddNum(date.getMonth() + 1, match.length))
            .replace(this.day, match => paddNum(date.getDate(), match.length))

            .replace(this.hours, () => paddNum(date.getHours(), 2))
            .replace(this.hoursZeroFree, () => date.getHours())
            .replace(this.hours12, () => paddNum(hours12, 2))
            .replace(this.hoursZeroFree12, () => hours12)

            .replace(this.minutes, match => paddNum(date.getMinutes(), match.length))
            .replace(this.seconds, match => paddNum(date.getSeconds(), match.length))

            .replace(this.ampm, () => ampm)
            .replace(this.ampmLower, () => ampm.toLowerCase())
        ;
    },
    parse (dateString, dateFormat)
    {
        let r = {day: 1, month: 1, year: 1970, hours: 0, minutes: 0, seconds: 0};

        const dateParts = dateString.split(this.re);
        const formatParts = dateFormat.split(this.re);
        const partsSize = formatParts.length;

        let isPm = false;
        for (let i = 0; i < partsSize; i++)
        {
            let part = dateParts[i];
            switch (formatParts[i])
            {
                case this.ampm:
                case this.ampmLower:
                    isPm = part.toUpperCase() === 'PM';
                    break;
            }
        }

        for (let i = 0; i < partsSize; i++)
        {
            let part = dateParts[i];
            let partInt = parseInt(part);
            switch (formatParts[i])
            {
                case this.year:
                    r.year = partInt;
                    break;
                case this.month:
                    r.month = partInt;
                    break;
                case this.day:
                    r.day = partInt;
                    break;
                case this.hours:
                case this.hoursZeroFree:
                    r.hours = partInt;
                    break;
                case this.hours12:
                case this.hoursZeroFree12:
                    r.hours = isPm
                        ? (partInt > 11 ? 11 : partInt) + 12
                        : partInt > 11 ? 0 : partInt;
                    break;
                case this.minutes:
                    r.minutes = partInt;
                    break;
                case this.seconds:
                    r.seconds = partInt;
                    break;
            }
        }

        return r;
    },
    isAmPm(dateFormat)
    {
        return (
            dateFormat.indexOf(this.ampm) >= 0
            ||
            dateFormat.indexOf(this.ampmLower) >= 0
        );
    },
    convertHoursToAmPm(hours, isPm)
    {
        return isPm
            ? (hours > 11 ? 11 : hours) + 12
            : hours > 11 ? 0 : hours;
    }
};

const VueDatePick = {

    props: {
        show: {type: Boolean, default: true},
        value: {type: String, default: ''},
        format: {type: String, default: 'MM/DD/YYYY'},
        displayFormat: {type: String},
        editable: {type: Boolean, default: true},
        hasInputElement: {type: Boolean, default: true},
        inputAttributes: {type: Object},
        selectableYearRange: {type: Number, default: 40},
        parseDate: {type: Function},
        formatDate: {type: Function},
        pickTime: {type: Boolean, default: false},
        pickMinutes: {type: Boolean, default: true},
        pickSeconds: {type: Boolean, default: false},
        isDateDisabled: {type: Function, default: () => false},
        nextMonthCaption: {type: String, default: 'Next month'},
        prevMonthCaption: {type: String, default: 'Previous month'},
        setTimeCaption: {type: String, default: 'Set time:'},
        closeButtonCaption: {type: String, default: 'Close'},
        mobileBreakpointWidth: {type: Number, default: 530},
        weekdays: {
            type: Array,
            default: () => ([
                'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'
            ])
        },
        months: {
            type: Array,
            default: () => ([
                'January', 'February', 'March', 'April',
                'May', 'June', 'July', 'August',
                'September', 'October', 'November', 'December'
            ])
        },
        startWeekOnSunday: {type: Boolean, default: false}
    },

    data() {
        return {
            inputValue: this.valueToInputFormat(this.value),
            currentPeriod: this.getPeriodFromValue(this.value, this.format),
            direction: undefined,
            positionClass: undefined,
            opened: !this.hasInputElement && this.show
        };
    },

    computed: {

        valueDate() {

            const value = this.value;
            const format = this.format;

            return value
                ? this.parseDateString(value, format)
                : undefined
            ;

        },

        isReadOnly() {
            return !this.editable || (this.inputAttributes && this.inputAttributes.readonly);
        },

        isValidValue() {

            const valueDate = this.valueDate;

            return this.value ? Boolean(valueDate) : true;

        },

        currentPeriodDates() {

            const {year, month} = this.currentPeriod;
            const days = [];
            const date = new Date(year, month, 1);
            const today = new Date();
            const offset = this.startWeekOnSunday ? 1 : 0;

            // append prev month dates
            const startDay = date.getDay() || 7;

            if (startDay > (1 - offset)) {
                for (let i = startDay - (2 - offset); i >= 0; i--) {

                    const prevDate = new Date(date);
                    prevDate.setDate(-i);
                    days.push({outOfRange: true, date: prevDate});

                }
            }

            while (date.getMonth() === month) {
                days.push({date: new Date(date)});
                date.setDate(date.getDate() + 1);
            }

            // append next month dates
            const daysLeft = 7 - days.length % 7;

            for (let i = 1; i <= daysLeft; i++) {

                const nextDate = new Date(date);
                nextDate.setDate(i);
                days.push({outOfRange: true, date: nextDate});

            }

            // define day states
            days.forEach(day => {
                day.disabled = this.isDateDisabled(day.date);
                day.today = areSameDates(day.date, today);
                day.dateKey = [
                    day.date.getFullYear(), day.date.getMonth() + 1, day.date.getDate()
                ].join('-');
                day.selected = this.valueDate ? areSameDates(day.date, this.valueDate) : false;
            });

            return chunkArray(days, 7);

        },

        yearRange() {

            const years = [];
            const currentYear = this.currentPeriod.year;
            const startYear = currentYear - this.selectableYearRange;
            const endYear = currentYear + this.selectableYearRange;

            for (let i = startYear; i <= endYear; i++) {
                years.push(i);
            }

            return years;

        },

        hasCurrentTime() {
            return !!this.valueDate;
        },

        currentTime() {

            const currentDate = this.valueDate;
            let hours = currentDate ? currentDate.getHours() : 12;
            let minutes = currentDate ? currentDate.getMinutes() : 0;
            let seconds = currentDate ? currentDate.getSeconds() : 0;

            return {
                hours: hours,
                minutes: minutes,
                seconds: seconds,
                hoursPadded: paddNum(hours, 1),
                minutesPadded: paddNum(minutes, 2),
                secondsPadded: paddNum(seconds, 2)
            };

        },

        directionClass() {

            return this.direction ? `vdp${this.direction}Direction` : undefined;

        },

        weekdaysSorted() {

            if (this.startWeekOnSunday) {
                const weekdays = this.weekdays.slice();
                weekdays.unshift(weekdays.pop());
                return weekdays;
            } else {
                return this.weekdays;
            }

        }

    },

    watch: {

        show(value) {
            this.opened = value;
        },

        value(value) {

            if (this.isValidValue) {
                this.inputValue = this.valueToInputFormat(value);
                this.currentPeriod = this.getPeriodFromValue(value, this.format);
            }

        },

        currentPeriod(currentPeriod, oldPeriod) {

            const currentDate = new Date(currentPeriod.year, currentPeriod.month).getTime();
            const oldDate = new Date(oldPeriod.year, oldPeriod.month).getTime();

            this.direction = currentDate !== oldDate
                ? (currentDate > oldDate ? 'Next' : 'Prev')
                : undefined
            ;

        }

    },

    beforeDestroy() {

        this.removeCloseEvents();
        this.teardownPosition();

    },

    methods: {

        valueToInputFormat(value) {

            return !this.displayFormat ? value : this.formatDateToString(
                this.parseDateString(value, this.format), this.displayFormat
            ) || value;

        },

        getPeriodFromValue(dateString, format) {

            const date = this.parseDateString(dateString, format) || new Date();

            return {month: date.getMonth(), year: date.getFullYear()};

        },

        parseDateString(dateString, dateFormat) {

            return !dateString
                ? undefined
                : this.parseDate
                    ? this.parseDate(dateString, dateFormat)
                    : this.parseSimpleDateString(dateString, dateFormat)
            ;

        },

        formatDateToString(date, dateFormat) {

            return !date
                ? ''
                : this.formatDate
                    ? this.formatDate(date, dateFormat)
                    : this.formatSimpleDateToString(date, dateFormat)
            ;

        },

        parseSimpleDateString(dateString, dateFormat) {

            let r = Format.parse(dateString, dateFormat);
            let day = r.day, month = r.month, year = r.year,
                hours = r.hours, minutes = r.minutes, seconds = r.seconds;

            const resolvedDate = new Date(
                [paddNum(year, 4), paddNum(month, 2), paddNum(day, 2)].join('-')
            );

            if (isNaN(resolvedDate)) {
                return undefined;
            } else {

                const date = new Date(year, month - 1, day);

                [
                    [year, 'setFullYear'],
                    [hours, 'setHours'],
                    [minutes, 'setMinutes'],
                    [seconds, 'setSeconds']
                ].forEach(([value, method]) => {
                    typeof value !== 'undefined' && date[method](value);
                });

                return date;
            }

        },

        formatSimpleDateToString(date, dateFormat)
        {
            return Format.format(date, dateFormat);
        },

        getHourList()
        {
            let list = [];
            let isAmPm = Format.isAmPm(this.displayFormat || this.format);
            for (let hours = 0; hours < 24; hours++)
            {
                let hoursDisplay = hours > 12
                    ? (hours - 12)
                    : (hours === 0) ? 12 : hours;
                hoursDisplay += hours > 11 ? ' pm' : ' am';

                list.push({
                    value: hours,
                    name: isAmPm ? hoursDisplay : hours
                });
            }
            return list;
        },

        incrementMonth(increment = 1) {

            const refDate = new Date(this.currentPeriod.year, this.currentPeriod.month);
            const incrementDate = new Date(refDate.getFullYear(), refDate.getMonth() + increment);

            this.currentPeriod = {
                month: incrementDate.getMonth(),
                year: incrementDate.getFullYear()
            };

        },

        processUserInput(userText) {

            const userDate = this.parseDateString(
                userText, this.displayFormat || this.format
            );

            this.inputValue = userText;

            this.$emit('input', userDate
                ? this.formatDateToString(userDate, this.format)
                : userText
            );

        },

        open() {

            if (!this.opened) {
                this.opened = true;
                this.currentPeriod = this.getPeriodFromValue(this.value, this.format);
                this.addCloseEvents();
                this.setupPosition();
            }
            this.direction = undefined;

        },

        close() {

            if (this.opened) {
                this.opened = false;
                this.direction = undefined;
                this.removeCloseEvents();
                this.teardownPosition();
            }

            this.$emit('close');
        },

        closeViaOverlay(e) {

            if (this.hasInputElement && e.target === this.$refs.outerWrap) {
                this.close();
            }

        },

        addCloseEvents() {

            if (!this.closeEventListener) {

                this.closeEventListener = e => this.inspectCloseEvent(e);

                ['click', 'keyup', 'focusin'].forEach(
                    eventName => document.addEventListener(eventName, this.closeEventListener)
                );

            }

        },

        inspectCloseEvent(event) {

            if (event.keyCode) {
                event.keyCode === 27 && this.close();
            } else if (!(event.target === this.$el) && !this.$el.contains(event.target)) {
                this.close();
            }

        },

        removeCloseEvents() {

            if (this.closeEventListener) {

                ['click', 'keyup'].forEach(
                    eventName => document.removeEventListener(eventName, this.closeEventListener)
                );

                delete this.closeEventListener;

            }

        },

        setupPosition() {

            if (!this.positionEventListener) {
                this.positionEventListener = () => this.positionFloater();
                window.addEventListener('resize', this.positionEventListener);
            }

            this.positionFloater();

        },

        positionFloater() {

            const inputRect = this.$el.getBoundingClientRect();

            let verticalClass = 'vdpPositionTop';
            let horizontalClass = 'vdpPositionLeft';

            const calculate = () => {

                const rect = this.$refs.outerWrap.getBoundingClientRect();
                const floaterHeight = rect.height;
                const floaterWidth = rect.width;

                if (window.innerWidth > this.mobileBreakpointWidth) {

                    // vertical
                    if (
                        (inputRect.top + inputRect.height + floaterHeight > window.innerHeight) &&
                        (inputRect.top - floaterHeight > 0)
                    ) {
                        verticalClass = 'vdpPositionBottom';
                    }

                    // horizontal
                    if (inputRect.left + floaterWidth > window.innerWidth) {
                        horizontalClass = 'vdpPositionRight';
                    }

                    this.positionClass = ['vdpPositionReady', verticalClass, horizontalClass].join(' ');

                } else {

                    this.positionClass = 'vdpPositionFixed';

                }

            };

            this.$refs.outerWrap ? calculate() : this.$nextTick(calculate);

        },

        teardownPosition() {

            if (this.positionEventListener) {
                this.positionClass = undefined;
                window.removeEventListener('resize', this.positionEventListener);
                delete this.positionEventListener;
            }

        },

        clear() {

            this.$emit('input', '');

        },

        selectDateItem(item) {

            if (!item.disabled) {

                const newDate = new Date(item.date);

                if (this.hasCurrentTime) {
                    newDate.setHours(this.currentTime.hours);
                    newDate.setMinutes(this.currentTime.minutes);
                    newDate.setSeconds(this.currentTime.seconds);
                }

                this.$emit('input', this.formatDateToString(newDate, this.format));

                if (this.hasInputElement && !this.pickTime) {
                    this.close();
                }
            }

        },

        inputTime(method, event) {
            const currentDate = this.valueDate || new Date();
            const maxValues = {setHours: 23, setMinutes: 59, setSeconds: 59};

            let numValue = parseInt(event.target.value, 10) || 0;

            if (numValue > maxValues[method]) {
                numValue = maxValues[method];
            } else if (numValue < 0) {
                numValue = 0;
            }

            event.target.value = paddNum(numValue, method === 'setHours' ? 1 : 2);
            currentDate[method](numValue);

            this.$emit('input', this.formatDateToString(currentDate, this.format), true);

        }

    },

    template: `
    <div class="vdpComponent" v-bind:class="{vdpWithInput: hasInputElement}">
        <input
            v-if="hasInputElement"
            type="text"
            v-bind="inputAttributes"
            v-bind:readonly="isReadOnly"
            v-bind:value="inputValue"
            v-on:input="editable && processUserInput($event.target.value)"
            v-on:focus="editable && open()"
            v-on:click="editable && open()"
        >
        <button
            v-if="editable && hasInputElement && inputValue"
            class="vdpClearInput"
            type="button"
            v-on:click="clear"
        ></button>
            <div
                v-if="opened"
                class="vdpOuterWrap"
                ref="outerWrap"
                v-on:click="closeViaOverlay"
                v-bind:class="[positionClass, {vdpFloating: hasInputElement}]"
            >
                <div class="vdpInnerWrap">
                    <header class="vdpHeader">
                        <button
                            class="vdpArrow vdpArrowPrev"
                            v-bind:title="prevMonthCaption"
                            type="button"
                            v-on:click="incrementMonth(-1)"
                        >{{ prevMonthCaption }}</button>
                        <button
                            class="vdpArrow vdpArrowNext"
                            type="button"
                            v-bind:title="nextMonthCaption"
                            v-on:click="incrementMonth(1)"
                        >{{ nextMonthCaption }}</button>
                        <div class="vdpPeriodControls">
                            <div class="vdpPeriodControl">
                                <button v-bind:class="directionClass" v-bind:key="currentPeriod.month" type="button">
                                    {{ months[currentPeriod.month] }}
                                </button>
                                <select v-model="currentPeriod.month">
                                    <option v-for="(month, index) in months" v-bind:value="index" v-bind:key="month">
                                        {{ month }}
                                    </option>
                                </select>
                            </div>
                            <div class="vdpPeriodControl">
                                <button v-bind:class="directionClass" v-bind:key="currentPeriod.year" type="button">
                                    {{ currentPeriod.year }}
                                </button>
                                <select v-model="currentPeriod.year">
                                    <option v-for="year in yearRange" v-bind:value="year" v-bind:key="year">
                                        {{ year }}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </header>
                    <table class="vdpTable">
                        <thead>
                            <tr>
                                <th class="vdpHeadCell" v-for="weekday in weekdaysSorted" v-bind:key="weekday">
                                    <span class="vdpHeadCellContent">{{weekday}}</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody
                            v-bind:key="currentPeriod.year + '-' + currentPeriod.month"
                            v-bind:class="directionClass"
                        >
                            <tr class="vdpRow" v-for="(week, weekIndex) in currentPeriodDates" v-bind:key="weekIndex">
                                <td
                                    class="vdpCell"
                                    v-for="item in week"
                                    v-bind:class="{
                                        selectable: !item.disabled,
                                        selected: item.selected,
                                        disabled: item.disabled,
                                        today: item.today,
                                        outOfRange: item.outOfRange
                                    }"
                                    v-bind:data-id="item.dateKey"
                                    v-bind:key="item.dateKey"
                                    v-on:click="selectDateItem(item)"
                                >
                                    <div
                                        class="vdpCellContent"
                                    >{{ item.date.getDate() }}</div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div v-if="pickTime" class="vdpTimeControls">
                        <span class="vdpTimeCaption">{{ setTimeCaption }}</span>
                        <div class="vdpTimeUnit">
                            <select class="vdpHoursInput"
                                v-if="pickMinutes"
                                v-on:input="inputTime('setHours', $event)"
                                v-on:change="inputTime('setHours', $event)"
                                v-bind:value="currentTime.hours"
                            >
                                <option
                                    v-for="item in getHourList()"
                                    :value="item.value"
                                >{{ item.name }}</option>
                            </select>
                        </div>
                        <span v-if="pickMinutes" class="vdpTimeSeparator">:</span>
                        <div v-if="pickMinutes" class="vdpTimeUnit">
                            <pre><span>{{ currentTime.minutesPadded }}</span><br></pre>
                            <input
                                v-if="pickMinutes"
                                type="number" pattern="\\d*" class="vdpMinutesInput"
                                v-on:input="inputTime('setMinutes', $event)"
                                v-bind:value="currentTime.minutesPadded"
                            >
                        </div>
                        <span v-if="pickSeconds" class="vdpTimeSeparator">:</span>
                        <div v-if="pickSeconds" class="vdpTimeUnit">
                            <pre><span>{{ currentTime.secondsPadded }}</span><br></pre>
                            <input
                                v-if="pickSeconds"
                                type="number" pattern="\\d*" class="vdpSecondsInput"
                                v-on:input="inputTime('setSeconds', $event)"
                                v-bind:value="currentTime.secondsPadded"
                            >
                        </div>
                        <span class="vdpTimeCaption">
                            <button type="button" @click="$emit('close');">{{ closeButtonCaption }}</button>
                        </span>
                    </div>
                </div>
            </div>
    </div>
    `

};

function paddNum(num, padsize) {

    return typeof num !== 'undefined'
        ? num.toString().length > padsize
            ? num
            : new Array(padsize - num.toString().length + 1).join('0') + num
        : undefined
    ;

}

function chunkArray(inputArray, chunkSize) {

    const results = [];

    while (inputArray.length) {
        results.push(inputArray.splice(0, chunkSize));
    }

    return results;

}

function areSameDates(date1, date2) {

    return (date1.getDate() === date2.getDate()) &&
        (date1.getMonth() === date2.getMonth()) &&
        (date1.getFullYear() === date2.getFullYear())
    ;

}

export {VueDatePick}