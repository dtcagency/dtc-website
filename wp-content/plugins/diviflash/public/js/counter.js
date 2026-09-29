(function () {
    'use strict';

    // ---------------------------------------------------------------------------
    // Utility helpers
    // ---------------------------------------------------------------------------

    const THOUSAND_MAP = { comma: ',', dot: '.', space: ' ', none: '' };
    const DECIMAL_MAP  = { dot: '.', comma: ',' };

    // Easing curves exposed by the panel's Easing select. Inputs progress
    // 0..1 and returns the eased fraction 0..1. Falls back to ease-out
    // when the config value is missing or unrecognized.
    const EASINGS = {
        'linear':      function (t) { return t; },
        'ease-in':     function (t) { return t * t * t; },
        'ease-out':    function (t) { return 1 - Math.pow(1 - t, 3); },
        'ease-in-out': function (t) {
            return t < 0.5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2;
        },
    };
    const getEasingFn = function (name) { return EASINGS[name] || EASINGS['ease-out']; };


    // ---------------------------------------------------------------------------
    // DFCounter -- one instance per module on the page
    // ---------------------------------------------------------------------------

    class DFCounter {

        constructor(moduleEl) {
            this.moduleEl = moduleEl;
            this.wrapper = moduleEl.querySelector('.df_counter_wrapper');
            if (!this.wrapper) return;

            this.config = this._parseConfig();
            if (!this.config) return;

            this.contentEl = this.wrapper.querySelector('.df_counter_content');
            if (!this.contentEl) return;

            // State
            this.animated = false;
            this.digitEls = [];
            this.numberEl = null;
            this.observer = null;

            this._init();
        }

        // -- Bootstrap ----------------------------------------------------------

        _parseConfig() {
            try {
                return JSON.parse(this.wrapper.getAttribute('data-counter-config') || '{}');
            } catch {
                return null;
            }
        }

        _init() {
            // End value's int width drives the digit/char layout so positions
            // stay aligned during the start→end tween.
            const endNum = parseFloat(this.config.endValue) || 0;
            this.endIntLen = Math.abs(endNum).toFixed(0).length;

            this.formatted = this._formatNumber(endNum);
            this.chars = this._getChars(this.formatted);
            this._buildDisplay();
            this._observeScroll();
        }

        // -- Number formatting --------------------------------------------------

        // Format with int-part padded to end value's width, so character layout
        // (digit count + separator positions) matches across start/current/end.
        _formatPadded(num) {
            return this._formatNumber(num, this.endIntLen);
        }

        _formatNumber(num, padIntTo) {
            const c = this.config;
            const places = parseInt(c.decimalPlaces) || 0;
            const thouKey = c.thousandSeparator || 'comma';
            const decKey  = c.decimalSeparator || 'dot';
            const thouChar = THOUSAND_MAP.hasOwnProperty(thouKey) ? THOUSAND_MAP[thouKey] : thouKey;
            const decChar  = DECIMAL_MAP.hasOwnProperty(decKey) ? DECIMAL_MAP[decKey] : decKey;

            const abs = Math.abs(num);
            const fixed = abs.toFixed(places);
            let [intPart, fracPart] = fixed.split('.');

            if (padIntTo && intPart.length < padIntTo) {
                intPart = intPart.padStart(padIntTo, '0');
            }

            // Insert thousand separators
            let formatted = '';
            if (thouChar) {
                const digits = intPart.split('');
                for (let i = 0; i < digits.length; i++) {
                    const pos = digits.length - i;
                    if (i > 0 && pos % 3 === 0) {
                        formatted += thouChar;
                    }
                    formatted += digits[i];
                }
            } else {
                formatted = intPart;
            }

            // Append decimal part
            if (places > 0 && fracPart !== undefined) {
                formatted += decChar + fracPart;
            }

            // Handle negative
            if (num < 0) {
                formatted = '-' + formatted;
            }

            return formatted;
        }

        _getChars(formatted) {
            const chars = [];
            for (const ch of formatted) {
                if (/\d/.test(ch)) {
                    chars.push({ type: 'digit', value: ch });
                } else {
                    chars.push({ type: 'separator', value: ch });
                }
            }
            return chars;
        }

        // -- DOM builders -------------------------------------------------------

        _buildDisplay() {
            const c = this.config;
            const style = c.animationStyle || 'rolling';
            this.contentEl.innerHTML = '';

            // Prefix
            if (c.prefix) {
                const pre = document.createElement('span');
                pre.className = 'df_counter_prefix';
                pre.textContent = c.prefix;
                this.contentEl.appendChild(pre);
            }

            if (style === 'odometer') {
                // Odometer's nested cell/viewport/strip layout is its own
                // structure; skip the shared .df_counter_digits flex wrapper
                // (which is sized for Rolling/Typewriter cells, not for the
                // Odometer's per-digit viewport math).
                this._buildOdometer();
            } else {
                this.digitsEl = document.createElement('div');
                this.digitsEl.className = 'df_counter_digits';
                this.contentEl.appendChild(this.digitsEl);

                if (style === 'rolling') {
                    this._buildRolling();
                } else if (style === 'typewriter') {
                    this._buildPerChar();
                } else {
                    this._buildSimple();
                }
            }

            // Suffix
            if (c.suffix) {
                const suf = document.createElement('span');
                suf.className = 'df_counter_suffix';
                suf.textContent = c.suffix;
                this.contentEl.appendChild(suf);
            }

        }

        _buildRolling() {
            this.digitEls = [];

            // Initialise columns to start value's digit at each position,
            // padded to end-value width so layout matches at all times.
            const startNum = parseFloat(this.config.startValue) || 0;
            const startFormatted = this._formatPadded(startNum);

            for (let i = 0; i < this.chars.length; i++) {
                const char = this.chars[i];
                const startChar = startFormatted[i];

                if (char.type === 'separator') {
                    const sep = document.createElement('span');
                    sep.className = 'df_counter_sep';
                    sep.textContent = char.value;
                    this.digitsEl.appendChild(sep);
                } else {
                    const digit = document.createElement('div');
                    digit.className = 'df_counter_digit';
                    digit.dataset.target = char.value;

                    const viewport = document.createElement('div');
                    viewport.className = 'df_counter_digit_viewport';

                    const col = document.createElement('div');
                    col.className = 'df_counter_digit_col';

                    for (let d = 0; d <= 9; d++) {
                        const span = document.createElement('span');
                        span.textContent = String(d);
                        col.appendChild(span);
                    }

                    // Show start digit immediately
                    const startDigit = /\d/.test(startChar) ? parseInt(startChar) : 0;
                    col.style.transform = 'translateY(' + (-startDigit * 10) + '%)';

                    viewport.appendChild(col);
                    digit.appendChild(viewport);
                    this.digitsEl.appendChild(digit);
                    this.digitEls.push(digit);
                }
            }
        }

        _buildPerChar() {
            this.charEls = [];

            // Initial chars show start value padded to end-value width.
            const startNum = parseFloat(this.config.startValue) || 0;
            const startFormatted = this._formatPadded(startNum);

            for (let i = 0; i < this.chars.length; i++) {
                const char = this.chars[i];
                const startCh = startFormatted[i] !== undefined ? startFormatted[i] : char.value;

                const span = document.createElement('span');
                span.style.opacity = '0';
                span.textContent = startCh;
                span.className = char.type === 'separator' ? 'df_counter_sep' : 'df_counter_char';

                this.digitsEl.appendChild(span);
                this.charEls.push(span);
            }
        }

        // Odometer style: per-digit cell + 1em viewport + flex-column strip
        // (mirrors the Rolling style's viewport > col layout). The strip
        // holds a fixed 11 entries — digits 0..9 plus a duplicate 0 below 9
        // — so the modulo wrap at translateY -10em ↔ 0em is visually
        // identical (both show "0"). DOM size is constant regardless of
        // count magnitude; rAF animates each strip's translateY in em.
        _buildOdometer() {
            const c = this.config;
            const startVal = parseFloat(c.startValue) || 0;
            const endVal = parseFloat(c.endValue) || 0;
            const places = parseInt(c.decimalPlaces) || 0;

            this.odometerEl = document.createElement('span');
            this.odometerEl.className = 'df_counter_odo';
            this.contentEl.appendChild(this.odometerEl);

            // Walk the formatted end value to lay out digit cells and
            // separator (thousand/decimal) characters in the same order.
            const endFormatted = this._formatNumber(endVal);
            const chars = this._getChars(endFormatted);

            let totalDigits = 0;
            for (const ch of chars) if (ch.type === 'digit') totalDigits++;
            const intCount = totalDigits - places;

            this.odoCols = [];
            let dCount = 0;

            for (const ch of chars) {
                if (ch.type === 'separator') {
                    const sep = document.createElement('span');
                    sep.className = 'df_counter_odo_sep';
                    sep.textContent = ch.value;
                    this.odometerEl.appendChild(sep);
                    continue;
                }

                const idx = dCount++;
                const place = idx < intCount
                    ? Math.pow(10, intCount - 1 - idx)
                    : Math.pow(10, -(idx - intCount + 1));

                // Floor (with a tiny epsilon for FP-precision edge cases like
                // 1234.5/0.1 ≈ 12344.999) so each column traverses integer
                // digit-units. Final state then lands on integer wheel
                // positions, which mod 10 gives the correct visible digit.
                const startWheel = Math.floor(startVal / place + 1e-9);
                const endWheel = Math.floor(endVal / place + 1e-9);

                const cell = document.createElement('span');
                cell.className = 'df_counter_odo_digit';

                const viewport = document.createElement('span');
                viewport.className = 'df_counter_odo_viewport';

                const strip = document.createElement('span');
                strip.className = 'df_counter_odo_strip';
                for (let d = 0; d <= 10; d++) {
                    const span = document.createElement('span');
                    span.textContent = String(d % 10);
                    strip.appendChild(span);
                }
                viewport.appendChild(strip);
                cell.appendChild(viewport);
                this.odometerEl.appendChild(cell);

                // Snap to the start digit before animation kicks in.
                const startCyclePos = ((startWheel % 10) + 10) % 10;
                strip.style.transform = 'translateY(-' + startCyclePos.toFixed(4) + 'em)';

                this.odoCols.push({ strip: strip, startWheel: startWheel, endWheel: endWheel });
            }
        }

        _buildSimple() {
            this.numberEl = document.createElement('span');
            this.numberEl.className = 'df_counter_number';

            const style = this.config.animationStyle || 'rolling';

            if (style === 'none') {
                this.numberEl.textContent = this.formatted;
            } else {
                this.numberEl.textContent = this._formatNumber(parseFloat(this.config.startValue) || 0);
            }

            this.digitsEl.appendChild(this.numberEl);
        }

        // Run a count tween from startValue to endValue and call onTick(currentNumber)
        // with the eased number on each frame.
        _runCountTween(onTick, onDone) {
            const c = this.config;
            const start = parseFloat(c.startValue) || 0;
            const end = parseFloat(c.endValue) || 0;
            const duration = parseInt(c.duration) || 2000;
            const easingFn = getEasingFn(c.easing);
            const startTime = performance.now();

            const tick = (now) => {
                const elapsed = now - startTime;
                const progress = Math.min(elapsed / duration, 1);
                const eased = easingFn(progress);
                const current = start + (end - start) * eased;

                onTick(current, progress);

                if (progress < 1) {
                    requestAnimationFrame(tick);
                } else if (typeof onDone === 'function') {
                    onDone();
                }
            };

            requestAnimationFrame(tick);
        }

        // -- Animation ----------------------------------------------------------

        _startAnimation() {
            if (this.animated && this.config.reTrigger !== 'on') return;
            this.animated = true;

            const style = this.config.animationStyle || 'rolling';

            if (style === 'rolling') {
                this._animateRolling();
            } else if (style === 'odometer') {
                this._animateOdometer();
            } else if (style === 'count_up') {
                this._animateCountUp();
            } else if (style === 'typewriter') {
                this._animatePerChar();
            }
            // "none" does not animate
        }

        _animateRolling() {
            const self = this;
            const staggerDelay = parseInt(this.config.staggerDelay);
            const sd = isNaN(staggerDelay) ? 50 : staggerDelay;
            const totalDigits = this.digitEls ? this.digitEls.length : 0;

            this._runCountTween(function (current) {
                const formatted = self._formatPadded(current);
                let digitIdx = 0;

                for (let i = 0; i < formatted.length; i++) {
                    const ch = formatted[i];
                    if (!/\d/.test(ch)) continue;

                    const digit = parseInt(ch);
                    const myDigitIdx = digitIdx++;
                    const digitEl = self.digitEls[myDigitIdx];
                    if (!digitEl) continue;

                    const col = digitEl.querySelector('.df_counter_digit_col');
                    if (col) {
                        // Right-to-left cascade — rightmost (ones place)
                        // starts at delay 0, leftmost finishes last.
                        const delay = (totalDigits - 1 - myDigitIdx) * sd;
                        col.style.transition = 'transform 100ms linear ' + delay + 'ms';
                        col.style.transform = 'translateY(' + (-digit * 10) + '%)';
                    }
                }
            });
        }

        // Odometer style: rAF tween over a fixed 11-entry strip. Per frame,
        // each column computes its current wheel position and translates to
        // -(wheelPos mod 10)em. Lower place values (ones, tens) tween across
        // many cycles in the same duration → fast scroll; higher places →
        // slow scroll → natural cascade.
        _animateOdometer() {
            if (!this.odoCols) return;
            const cols = this.odoCols;
            const duration = parseInt(this.config.duration) || 2000;
            const easingFn = getEasingFn(this.config.easing);
            const startTime = performance.now();

            const tick = function (now) {
                const elapsed = now - startTime;
                const progress = Math.min(elapsed / duration, 1);
                const eased = easingFn(progress);

                for (let i = 0; i < cols.length; i++) {
                    const col = cols[i];
                    const wheelPos = col.startWheel + (col.endWheel - col.startWheel) * eased;
                    const cyclePos = ((wheelPos % 10) + 10) % 10;
                    col.strip.style.transform = 'translateY(-' + cyclePos.toFixed(4) + 'em)';
                }

                if (progress < 1) {
                    requestAnimationFrame(tick);
                }
            };

            requestAnimationFrame(tick);
        }

        _animateCountUp() {
            const self = this;
            this._runCountTween(function (current) {
                self.numberEl.textContent = self._formatNumber(current);
            });
        }

        _animatePerChar() {
            const self = this;
            const charEls = this.charEls || [];
            const staggerDelay = parseInt(this.config.staggerDelay);
            const sd = isNaN(staggerDelay) ? 50 : staggerDelay;

            // Typewriter reveal: each char fades in at a staggered delay. The
            // count tween below runs concurrently and updates each digit's
            // textContent so the value tweens from start to end.
            for (let i = 0; i < charEls.length; i++) {
                (function (el, idx) {
                    setTimeout(function () {
                        el.style.opacity = '1';
                    }, idx * sd);
                })(charEls[i], i);
            }

            // Count tween updates digit textContent each frame (separators stay).
            this._runCountTween(function (current) {
                const formatted = self._formatPadded(current);
                for (let i = 0; i < charEls.length; i++) {
                    const el = charEls[i];
                    const ch = formatted[i];
                    if (ch === undefined) continue;
                    // Only update digit chars; leave separators as-is
                    if (/\d/.test(ch) && el.classList.contains('df_counter_char')) {
                        el.textContent = ch;
                    }
                }
            });
        }

        _resetAnimation() {
            const style = this.config.animationStyle || 'rolling';
            const startNum = parseFloat(this.config.startValue) || 0;
            const startFormatted = this._formatPadded(startNum);

            if (style === 'rolling') {
                let digitIdx = 0;
                for (let i = 0; i < startFormatted.length; i++) {
                    const ch = startFormatted[i];
                    if (!/\d/.test(ch)) continue;
                    const digitEl = this.digitEls[digitIdx++];
                    if (!digitEl) continue;
                    const col = digitEl.querySelector('.df_counter_digit_col');
                    if (col) {
                        col.style.transition = 'none';
                        col.style.transform = 'translateY(' + (-parseInt(ch) * 10) + '%)';
                    }
                }
            } else if (style === 'odometer') {
                if (this.odoCols) {
                    for (const col of this.odoCols) {
                        const startCyclePos = ((col.startWheel % 10) + 10) % 10;
                        col.strip.style.transform = 'translateY(-' + startCyclePos.toFixed(4) + 'em)';
                    }
                }
            } else if (style === 'count_up') {
                this.numberEl.textContent = this._formatNumber(startNum);
            } else if (style === 'typewriter') {
                const charEls = this.charEls || [];
                for (let i = 0; i < charEls.length; i++) {
                    const el = charEls[i];
                    const startCh = startFormatted[i];
                    el.style.transition = 'none';
                    el.style.opacity = '0';
                    if (startCh !== undefined && /\d/.test(startCh) && el.classList.contains('df_counter_char')) {
                        el.textContent = startCh;
                    }
                }
            }

            this.animated = false;
        }

        // -- Scroll observation -------------------------------------------------

        _observeScroll() {
            if (this.config.scrollTrigger !== 'on') {
                this._startAnimation();
                return;
            }

            const threshold = parseFloat(this.config.scrollThreshold) || 0.2;
            const reTrigger = this.config.reTrigger === 'on';

            this.observer = new IntersectionObserver((entries) => {
                for (const entry of entries) {
                    if (entry.isIntersecting) {
                        this._startAnimation();
                        if (!reTrigger) {
                            this.observer.unobserve(entry.target);
                        }
                    } else if (reTrigger && this.animated) {
                        this._resetAnimation();
                    }
                }
            }, { threshold });

            this.observer.observe(this.wrapper);
        }

    }


    // ---------------------------------------------------------------------------
    // Bootstrap -- create one instance per module
    // ---------------------------------------------------------------------------

    function init() {
        const modules = document.querySelectorAll('.difl_counter');
        if (!modules.length) return;

        modules.forEach((moduleEl) => {
            if (moduleEl.dataset.dfCounterInit) return;
            moduleEl.dataset.dfCounterInit = '1';
            new DFCounter(moduleEl);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
}());
