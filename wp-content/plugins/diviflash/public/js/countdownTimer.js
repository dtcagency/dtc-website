(function () {
    'use strict';

    // ---------------------------------------------------------------------------
    // Utility helpers
    // ---------------------------------------------------------------------------

    const MAX_VALUES = { days: 365, hours: 24, minutes: 60, seconds: 60 };

    const setCookie = (name, value, days) => {
        const d = new Date();
        d.setTime(d.getTime() + days * 86400000);
        document.cookie = `${name}=${encodeURIComponent(value)};expires=${d.toUTCString()};path=/`;
    };

    const getCookie = (name) => {
        const match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
        return match ? decodeURIComponent(match[2]) : null;
    };

    const deleteCookie = (name) => {
        document.cookie = `${name}=;expires=Thu, 01 Jan 1970 00:00:00 GMT;path=/`;
    };

    const padUnit = (key, value) => {
        const len = key === 'days' ? Math.max(String(value).length, 2) : 2;
        return String(value).padStart(len, '0');
    };

    const el = (tag, className, styles = {}) => {
        const node = document.createElement(tag);
        if (className) node.className = className;
        Object.assign(node.style, styles);
        return node;
    };

    const svgEl = (tag, attrs = {}) => {
        const node = document.createElementNS('http://www.w3.org/2000/svg', tag);
        for (const [k, v] of Object.entries(attrs)) node.setAttribute(k, v);
        return node;
    };


    // ---------------------------------------------------------------------------
    // DFCountdownTimer – one instance per module on the page
    // ---------------------------------------------------------------------------

    class DFCountdownTimer {

        constructor(moduleEl) {
            this.moduleEl = moduleEl;
            this.wrapper = moduleEl.querySelector('.df_countdown_wrapper');
            if (!this.wrapper) return;

            this.config = this._parseConfig();
            if (!this.config) return;

            this.displayEl = this.wrapper.querySelector('.df_countdown_display');
            this.messageEl = this.wrapper.querySelector('.df_countdown_message');
            if (!this.displayEl) return;

            // Stable module identifier — used to derive a unique cookie name
            // for evergreen timers so two modules on the same page don't
            // share the same cookie. Try the DOM id first, then the Divi
            // order class (e.g. difl_countdowntimer_0), then the module's
            // position among all countdown timers on the page. Random is
            // the last resort and would defeat evergreen persistence.
            this.moduleId = (() => {
                if (moduleEl.id) return moduleEl.id;
                const orderClass = (moduleEl.className || '').match(/difl_countdowntimer_(\d+)/);
                if (orderClass) return orderClass[0];
                const all = document.querySelectorAll('.difl_countdowntimer');
                const idx = Array.prototype.indexOf.call(all, moduleEl);
                if (idx >= 0) return 'df_ct_idx_' + idx;
                return 'df_ct_' + Math.random().toString(36).substr(2, 9);
            })();
            this.prevValues = {};
            this.isExpired = false;
            this.style = this.config.timerStyle || 'default';
            this.visibleUnits = this._getVisibleUnits();

            this._init();
        }

        // -- Bootstrap ----------------------------------------------------------

        _parseConfig() {
            try {
                return JSON.parse(this.wrapper.getAttribute('data-countdown-config') || '{}');
            } catch {
                return null;
            }
        }

        _init() {
            this.targetTime = this._computeTargetTime();
            // Count Up flips direction silently at zero — track current
            // mode so _getTimeRemaining can pick the right diff sign and
            // the Circle / Progress visuals can cycle-fill when "up".
            this._countingUp = false;
            this._buildDisplay();
            this._startTicking();
        }

        // -- Target time --------------------------------------------------------

        _computeTargetTime() {
            const { countdownType = 'fixed' } = this.config;

            if (countdownType === 'fixed') {
                if (this.config.targetTimezone === 'visitor' && this.config.targetDate) {
                    return new Date(this.config.targetDate).getTime();
                }
                return this.config.endTimestamp * 1000;
            }
            if (countdownType === 'evergreen') return this._computeEvergreenTarget();
            if (countdownType === 'recurring') return this._computeRecurringTarget();
            if (countdownType === 'count_up') {
                // PHP returns 0 when timezone is "visitor" (resolve_local_datetime
                // bails for that case — same as Fixed Date). Parse the wall-clock
                // locally so each visitor sees elapsed time anchored to the same
                // wall-clock interpreted in their own browser zone.
                if (this.config.countUpTimezone === 'visitor' && this.config.countUpAnchorDate) {
                    return new Date(this.config.countUpAnchorDate).getTime();
                }
                return (parseInt(this.config.countUpAnchor) || 0) * 1000;
            }

            return Date.now() + 3600000;
        }

        _computeEvergreenTarget() {
            const c = this.config;
            const duration = (parseInt(c.evergreenDuration) || 3600) * 1000;
            const cookieName = c.evergreenCookieId || `df_countdown_${this.moduleId}`;
            const stored = getCookie(cookieName);

            // Cookie format: "targetMs|durationMs". The duration is stored so
            // changing days/hours/minutes in the builder invalidates an old
            // target (otherwise the visitor's locked-in target would never
            // pick up the new value). Legacy cookies are bare numbers — those
            // fall through and get rewritten in the new format.
            if (stored) {
                const parts = stored.split('|');
                if (parts.length === 2) {
                    const target = parseInt(parts[0]);
                    const storedDuration = parseInt(parts[1]);
                    if (storedDuration === duration && target > Date.now()) return target;
                }
                deleteCookie(cookieName);
            }

            const newTarget = Date.now() + duration;
            setCookie(cookieName, `${newTarget}|${duration}`, 365);
            return newTarget;
        }

        _computeRecurringTarget() {
            const c = this.config;
            const now = new Date();
            const [rh = 0, rm = 0] = (c.recurringTime || '23:59').split(':').map(Number);
            const rType = c.recurringType || 'daily';

            if (rType === 'daily') {
                const t = new Date(now.getFullYear(), now.getMonth(), now.getDate(), rh, rm, 0);
                if (t <= now) t.setDate(t.getDate() + 1);
                return t.getTime();
            }

            if (rType === 'weekly') {
                const wd = parseInt(c.recurringWeekday) || 1;
                const t = new Date(now.getFullYear(), now.getMonth(), now.getDate(), rh, rm, 0);
                let daysUntil = (wd - now.getDay() + 7) % 7;
                if (daysUntil === 0 && t <= now) daysUntil = 7;
                t.setDate(t.getDate() + daysUntil);
                return t.getTime();
            }

            // yearly
            const mo = (parseInt(c.recurringMonth) || 1) - 1;
            const dy = parseInt(c.recurringDay) || 1;
            const t = new Date(now.getFullYear(), mo, dy, rh, rm, 0);
            if (t <= now) t.setFullYear(t.getFullYear() + 1);
            return t.getTime();
        }

        // -- Visible units ------------------------------------------------------

        _getVisibleUnits() {
            const c = this.config;
            const units = [];
            if (c.showDays !== 'off') units.push('days');
            if (c.showHours !== 'off') units.push('hours');
            if (c.showMinutes !== 'off') units.push('minutes');
            if (c.showSeconds !== 'off') units.push('seconds');
            return units;
        }

        // -- Build display DOM --------------------------------------------------

        _buildDisplay() {
            // Progress Bar stacks rows vertically with no inline separator
            // slot; Boxed / Flip Clock / Circular Progress all render units
            // inline so the separator fits cleanly between them. The PHP
            // RenderCallback already maps legacy hide_separator → enableSeparator,
            // so we only need to honor enableSeparator here.
            const showSep = this.style !== 'progress' && this.config.enableSeparator !== 'off';
            this.displayEl.innerHTML = '';

            this.visibleUnits.forEach((key, idx) => {
                const unitEl =
                    this.style === 'flip' ? this._createFlipUnit(key) :
                    this.style === 'split_flip' ? this._createSplitFlipUnit(key) :
                    this.style === 'circle' ? this._createCircleUnit(key) :
                    this.style === 'progress' ? this._createProgressUnit(key) :
                    this._createDefaultUnit(key);

                this.displayEl.appendChild(unitEl);

                if (showSep && idx < this.visibleUnits.length - 1) {
                    this.displayEl.appendChild(this._createSeparator());
                }
            });
        }

        // -- Label helper -------------------------------------------------------

        _getLabel(key) {
            const cfgKeys = { days: 'dayLabel', hours: 'hourLabel', minutes: 'minuteLabel', seconds: 'secondLabel' };
            const val = this.config[cfgKeys[key]];
            return val === undefined || val === null ? '' : val;
        }

        _createLabelEl(key) {
            const labelEl = el('div', 'df_countdown_label');
            labelEl.textContent = this._getLabel(key);
            return labelEl;
        }

        _appendWithLabel(unitEl, contentEl, key) {
            const labelText = this._getLabel(key);
            // No label saved → render the unit without a label element so the
            // layout collapses cleanly.
            if (!labelText) {
                unitEl.appendChild(contentEl);
                return;
            }
            const labelEl = this._createLabelEl(key);
            if (this.config.labelPosition === 'above') {
                unitEl.appendChild(labelEl);
                unitEl.appendChild(contentEl);
            } else {
                unitEl.appendChild(contentEl);
                unitEl.appendChild(labelEl);
            }
        }

        // -- Base unit element ---------------------------------------------------

        _createUnitEl(key) {
            return el('div', `df_countdown_unit df_countdown_${key}`);
        }

        // -- Create default unit ------------------------------------------------

        _createDefaultUnit(key) {
            const unitEl = this._createUnitEl(key);
            const card = el('div', 'df_countdown_card');
            const numberEl = el('div', 'df_countdown_number');
            numberEl.textContent = '00';
            card.appendChild(numberEl);
            this._appendWithLabel(unitEl, card, key);
            return unitEl;
        }

        // -- Create flip unit ---------------------------------------------------

        _createFlipUnit(key) {
            const unitEl = this._createUnitEl(key);
            const card = el('div', 'df_countdown_card');
            const flipCard = el('div', 'df_flip_card');

            const topHalf = this._createFlipHalf('df_flip_top', '00');
            const bottomHalf = this._createFlipHalf('df_flip_bottom', '00');

            flipCard.append(topHalf, bottomHalf);
            card.appendChild(flipCard);
            this._appendWithLabel(unitEl, card, key);
            return unitEl;
        }

        _createFlipHalf(className, text) {
            const half = el('div', className);
            const num = el('div', 'df_countdown_number');
            num.textContent = text;
            half.appendChild(num);
            return half;
        }

        // -- Create split-flip unit (one card per digit) ------------------------

        _createSplitFlipUnit(key) {
            const unitEl = this._createUnitEl(key);
            const card = el('div', 'df_countdown_card');
            const sfUnit = el('div', 'df_split_flip_unit');
            // Two cards by default; for Days the row is rebuilt during the
            // first tick if the value crosses 100 / 1000 (handled in
            // _updateSplitFlipUnit so prev/new digit counts always line up).
            for (let i = 0; i < 2; i++) {
                sfUnit.appendChild(this._createSplitFlipCard('0'));
            }
            card.appendChild(sfUnit);
            this._appendWithLabel(unitEl, card, key);
            return unitEl;
        }

        _createSplitFlipCard(digit) {
            const sfCard = el('div', 'df_split_flip_card');
            sfCard.append(
                this._createSplitFlipHalf('df_split_flip_top', digit),
                this._createSplitFlipHalf('df_split_flip_bottom', digit)
            );
            return sfCard;
        }

        _createSplitFlipHalf(className, text) {
            const half = el('div', className);
            const num = el('div', 'df_countdown_number');
            num.textContent = text;
            half.appendChild(num);
            return half;
        }

        // -- Create circle unit -------------------------------------------------

        _createCircleUnit(key) {
            const c = this.config;
            const size = parseInt(c.circleSize) || 120;
            const sw = parseInt(c.circleStrokeWidth) || 4;
            // Centerline at (size/2 - sw/2) so the ring's outer edge sits
            // flush with the container edge (minus 0.2px for sub-pixel
            // anti-aliasing). Keeps the visible gap between the ring and a
            // wrapper-decorated container (box-shadow, border) at zero.
            const r = (size / 2) - sw / 2 - 0.2;
            const circ = 2 * Math.PI * r;
            const cx = size / 2;

            let progressColor = c.circleProgressColor || '#4285F4';
            let fillColor = c.circleFillColor || '';
            if (c.enableIndividualColors === 'on') {
                const ringMap = { days: c.daysRingColor, hours: c.hoursRingColor, minutes: c.minutesRingColor, seconds: c.secondsRingColor };
                if (ringMap[key]) progressColor = ringMap[key];
                const fillMap = { days: c.daysCircleFillColor, hours: c.hoursCircleFillColor, minutes: c.minutesCircleFillColor, seconds: c.secondsCircleFillColor };
                if (fillMap[key]) fillColor = fillMap[key];
            }

            const unitEl = this._createUnitEl(key);

            const container = el('div', 'df_circle_container');

            // SVG
            const svg = svgEl('svg', { class: 'df_circle_ring', viewBox: `0 0 ${size} ${size}` });

            if (fillColor) {
                svg.appendChild(svgEl('circle', { cx, cy: cx, r, fill: fillColor, stroke: 'none' }));
            }

            // Track
            svg.appendChild(svgEl('circle', {
                cx, cy: cx, r, fill: 'none',
                stroke: c.circleTrackColor || '#e0e0e0', 'stroke-width': sw,
            }));

            // Progress
            const progress = svgEl('circle', {
                cx, cy: cx, r, fill: 'none',
                stroke: progressColor, 'stroke-width': sw,
                'stroke-linecap': c.circleCap || 'round',
                'stroke-dasharray': circ, 'stroke-dashoffset': circ,
                transform: `rotate(-90 ${cx} ${cx})`,
                'data-circumference': circ,
            });
            svg.appendChild(progress);

            // Number (and optionally label) inside circle. When the
            // inside-label is on AND labelPosition is "above", insert the
            // label before the number so the flex-column stacks it on top.
            const contentDiv = el('div', 'df_circle_content');
            const numberEl = el('div', 'df_countdown_number');
            numberEl.textContent = '00';

            const labelInside = c.circleLabelInside === 'on';
            const labelText = this._getLabel(key);
            const innerLabel = (labelInside && labelText)
                ? el('div', 'df_countdown_label df_circle_label_inside')
                : null;
            if (innerLabel) innerLabel.textContent = labelText;

            if (innerLabel && c.labelPosition === 'above') {
                contentDiv.appendChild(innerLabel);
                contentDiv.appendChild(numberEl);
            } else {
                contentDiv.appendChild(numberEl);
                if (innerLabel) contentDiv.appendChild(innerLabel);
            }

            container.append(svg, contentDiv);
            const card = el('div', 'df_countdown_card');
            card.appendChild(container);
            if (labelInside) {
                unitEl.appendChild(card);
            } else {
                this._appendWithLabel(unitEl, card, key);
            }
            return unitEl;
        }

        // -- Create progress unit -----------------------------------------------

        _createProgressUnit(key) {
            const unitEl = this._createUnitEl(key);
            const labelEl = this._createLabelEl(key);
            const track = el('div', 'df_progress_track');
            const fill = el('div', 'df_progress_fill');
            track.appendChild(fill);
            const numberEl = el('div', 'df_countdown_number');
            numberEl.textContent = '00';
            unitEl.append(labelEl, track, numberEl);
            return unitEl;
        }

        // -- Create separator ---------------------------------------------------

        _createSeparator() {
            const sep = el('div', 'df_countdown_separator');
            sep.textContent = this.config.separatorCharacter || ':';
            return sep;
        }

        // -- Tick / countdown ---------------------------------------------------

        _getTimeRemaining() {
            const c = this.config;
            const type = c.countdownType || 'fixed';
            let diff;
            let isUp = false;

            if (type === 'count_up') {
                // Future anchor → preview countdown to it; past anchor →
                // elapsed time, growing forever. Flip happens silently at
                // zero on the next tick after the anchor passes.
                const now = Date.now();
                const elapsed = now - this.targetTime;
                isUp = elapsed >= 0;
                diff = isUp ? elapsed : -elapsed;
            } else {
                diff = this.targetTime - Date.now();
                if (diff <= 0) return { total: 0, days: 0, hours: 0, minutes: 0, seconds: 0, _cycle: null, _isUp: false };
            }

            this._countingUp = isUp;

            // Roll hidden units down into the next visible smaller unit so
            // hiding Days, for example, lets the Hours field cover the full
            // remaining duration instead of dropping the days portion.
            let remaining = diff;
            const result = { total: diff, days: 0, hours: 0, minutes: 0, seconds: 0 };
            if (c.showDays !== 'off') {
                result.days = Math.floor(remaining / 86400000);
                remaining -= result.days * 86400000;
            }
            if (c.showHours !== 'off') {
                result.hours = Math.floor(remaining / 3600000);
                remaining -= result.hours * 3600000;
            }
            if (c.showMinutes !== 'off') {
                result.minutes = Math.floor(remaining / 60000);
                remaining -= result.minutes * 60000;
            }
            if (c.showSeconds !== 'off') {
                result.seconds = Math.floor(remaining / 1000);
            }

            // Cycle-fill values for Circle / Progress visuals when counting
            // up — Seconds ring fills 0→59 then resets, Hours 0→23, etc.
            // The text digits still display the raw elapsed value.
            result._isUp = isUp;
            result._cycle = isUp ? {
                days: result.days % MAX_VALUES.days,
                hours: result.hours % MAX_VALUES.hours,
                minutes: result.minutes % MAX_VALUES.minutes,
                seconds: result.seconds % MAX_VALUES.seconds,
            } : null;
            return result;
        }

        _startTicking() {
            this._tick();
            setInterval(() => this._tick(), 1000);
        }

        _tick() {
            if (this.isExpired) return;

            const time = this._getTimeRemaining();
            const type = this.config.countdownType || 'fixed';
            const isExpiringType = type === 'fixed' || type === 'evergreen' || type === 'recurring';

            // Only countdown types ever expire. Count Up keeps ticking
            // forever (flips direction silently at the anchor).
            if (isExpiringType && time.total <= 0) {
                this._handleZero();
                return;
            }

            for (const key of this.visibleUnits) {
                const numStr = padUnit(key, time[key]);
                const prev = this.prevValues[key] || numStr;
                const changed = prev !== numStr;

                // Pass the cycle-filled value to Circle / Progress when the
                // timer is in "up" direction so the ring/bar reads as
                // "elapsed within this period" instead of "fraction of
                // some target remaining".
                const ringValue = (time._cycle && time._cycle[key] !== undefined)
                    ? time._cycle[key]
                    : time[key];

                this._updateUnit(key, numStr, prev, ringValue);

                if (changed && this.config.tickAnimation && this.config.tickAnimation !== 'none') {
                    if (this.config.tickTarget === 'all' || this.config.tickTarget === key) {
                        this._applyTickAnimation(key);
                    }
                }

                this.prevValues[key] = numStr;
            }
        }

        _updateUnit(key, numStr, prevStr, value) {
            const unitEl = this.displayEl.querySelector(`.df_countdown_${key}`);
            if (!unitEl) return;

            if (this.style === 'flip') {
                this._updateFlipCard(unitEl, numStr, prevStr);
            } else if (this.style === 'split_flip') {
                this._updateSplitFlipUnit(unitEl, numStr, prevStr);
            } else if (this.style === 'circle') {
                const numEl = unitEl.querySelector('.df_countdown_number');
                if (numEl) numEl.textContent = numStr;
                this._updateCircleProgress(unitEl, value, MAX_VALUES[key]);
            } else if (this.style === 'progress') {
                const numEl = unitEl.querySelector('.df_countdown_number');
                if (numEl) numEl.textContent = numStr;
                const fill = unitEl.querySelector('.df_progress_fill');
                if (fill) fill.style.width = `${(value / MAX_VALUES[key]) * 100}%`;
            } else {
                const numEl = unitEl.querySelector('.df_countdown_number');
                if (numEl) numEl.textContent = numStr;
            }
        }

        // -- Flip card update ---------------------------------------------------

        _updateFlipCard(unitEl, newStr, prevStr) {
            const flipCard = unitEl.querySelector('.df_flip_card');
            if (!flipCard) return;

            const topNum = flipCard.querySelector('.df_flip_top .df_countdown_number');
            const bottomNum = flipCard.querySelector('.df_flip_bottom .df_countdown_number');

            // Remove old flip overlays
            flipCard.querySelectorAll('.df_flip_top_flip, .df_flip_bottom_flip').forEach(n => n.remove());

            if (prevStr === newStr) {
                if (topNum) topNum.textContent = newStr;
                if (bottomNum) bottomNum.textContent = newStr;
                return;
            }

            const dur = parseInt(this.config.flipDuration) || 600;
            const half = dur / 2;

            // Static top: show NEW value
            if (topNum) topNum.textContent = newStr;

            // Top flip: shows OLD value, folds down (dynamic animation duration)
            const topFlip = this._createFlipHalf('df_flip_top_flip', prevStr);
            topFlip.style.animation = `dfFlipTop ${half}ms ease-in forwards`;

            // Bottom flip: shows NEW value, unfolds up (dynamic animation duration + delay)
            const bottomFlip = this._createFlipHalf('df_flip_bottom_flip', newStr);
            bottomFlip.style.animation = `dfFlipBottom ${half}ms ease-out ${half}ms forwards`;

            flipCard.append(topFlip, bottomFlip);

            // Cleanup after animation
            setTimeout(() => {
                if (bottomNum) bottomNum.textContent = newStr;
                topFlip.remove();
                bottomFlip.remove();
            }, dur + 50);
        }

        // -- Split-flip unit update (per-digit) ---------------------------------

        _updateSplitFlipUnit(unitEl, newStr, prevStr) {
            const sfUnit = unitEl.querySelector('.df_split_flip_unit');
            if (!sfUnit) return;

            // If the digit count changed (e.g. Days 99 → 100), rebuild the
            // row to match — no animation on the resize tick because the
            // old positions don't map onto the new ones.
            let cards = sfUnit.querySelectorAll('.df_split_flip_card');
            if (cards.length !== newStr.length) {
                sfUnit.innerHTML = '';
                for (let i = 0; i < newStr.length; i++) {
                    sfUnit.appendChild(this._createSplitFlipCard(newStr[i]));
                }
                return;
            }

            // Pad prev to newStr's length so positions line up if prev was
            // shorter (only happens on a rebuild's *next* tick, which is
            // already prevented above; kept defensively).
            const padPrev = String(prevStr).padStart(newStr.length, '0');
            const dur = parseInt(this.config.splitFlipDuration) || 600;
            const half = dur / 2;

            cards.forEach((sfCard, idx) => {
                const newD = newStr[idx];
                const prevD = padPrev[idx];
                const topNum = sfCard.querySelector('.df_split_flip_top .df_countdown_number');
                const bottomNum = sfCard.querySelector('.df_split_flip_bottom .df_countdown_number');

                // Drop any in-flight overlays from the previous tick so
                // a fast cadence doesn't stack animations.
                sfCard.querySelectorAll('.df_split_flip_top_flip, .df_split_flip_bottom_flip').forEach(n => n.remove());

                if (prevD === newD) {
                    if (topNum) topNum.textContent = newD;
                    if (bottomNum) bottomNum.textContent = newD;
                    return;
                }

                if (topNum) topNum.textContent = newD;
                const topFlip = this._createSplitFlipHalf('df_split_flip_top_flip', prevD);
                topFlip.style.animation = `dfFlipTop ${half}ms ease-in forwards`;
                const bottomFlip = this._createSplitFlipHalf('df_split_flip_bottom_flip', newD);
                bottomFlip.style.animation = `dfFlipBottom ${half}ms ease-out ${half}ms forwards`;
                sfCard.append(topFlip, bottomFlip);

                setTimeout(() => {
                    if (bottomNum) bottomNum.textContent = newD;
                    topFlip.remove();
                    bottomFlip.remove();
                }, dur + 50);
            });
        }

        // -- Circle progress update ---------------------------------------------

        _updateCircleProgress(unitEl, value, maxVal) {
            const progress = unitEl.querySelector('.df_circle_ring circle:last-child');
            if (!progress) return;

            const circ = parseFloat(progress.getAttribute('data-circumference'));
            if (!circ) return;

            progress.setAttribute('stroke-dashoffset', circ * (1 - value / maxVal));
        }

        // -- Tick animation -----------------------------------------------------

        _applyTickAnimation(key) {
            const unitEl = this.displayEl.querySelector(`.df_countdown_${key}`);
            if (!unitEl) return;

            const cls = `df_tick_${this.config.tickAnimation}`;
            unitEl.classList.remove(cls);
            void unitEl.offsetWidth; // Force reflow
            unitEl.classList.add(cls);
            setTimeout(() => unitEl.classList.remove(cls), 600);
        }

        // -- Expiration handling -------------------------------------------------

        _handleZero() {
            // Set all units to 00
            for (const key of this.visibleUnits) {
                const numStr = padUnit(key, 0);
                this._updateUnit(key, numStr, this.prevValues[key] || numStr, 0);
            }

            const c = this.config;
            const action = c.expirationAction || 'show_message';

            if (action === 'hide') {
                this.wrapper.classList.add('df_countdown_expired_hidden');
                this.isExpired = true;
            } else if (action === 'show_message') {
                this.wrapper.classList.add('df_countdown_expired');
                this.messageEl.textContent = c.expirationMessage || 'Countdown finished!';
                this.isExpired = true;
            } else if (action === 'redirect') {
                const delay = parseInt(c.redirectDelay) || 0;
                if (c.redirectUrl) setTimeout(() => { window.location.href = c.redirectUrl; }, delay * 1000);
                this.isExpired = true;
            } else if (action === 'restart') {
                if (c.countdownType === 'evergreen') {
                    deleteCookie(c.evergreenCookieId || `df_countdown_${this.moduleId}`);
                }
                const restartDelay = parseInt(c.restartDelay) || 0;
                setTimeout(() => {
                    this.targetTime = this._computeTargetTime();
                    this.prevValues = {};
                }, restartDelay * 1000);
            } else {
                this.isExpired = true;
            }
        }

    }


    // ---------------------------------------------------------------------------
    // Bootstrap – create one instance per module
    // ---------------------------------------------------------------------------

    function init() {
        const modules = document.querySelectorAll('.difl_countdowntimer');
        if (!modules.length) return;

        modules.forEach((moduleEl) => {
            if (moduleEl.dataset.dfCountdownInit) return;
            moduleEl.dataset.dfCountdownInit = '1';
            new DFCountdownTimer(moduleEl);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
}());
