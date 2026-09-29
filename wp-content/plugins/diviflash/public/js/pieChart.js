(function () {
    'use strict';

    // Auto palette used when items don't set their own slice color. Matches
    // the VB parent's children-source palette so the FE renders distinct
    // slice colors on fresh inserts instead of stamping the same blue.
    const DF_PIE_PALETTE = [
        '#4285F4', '#34A853', '#EA4335', '#FBBC05',
        '#FF6D01', '#46BDC6', '#9C27B0', '#795548',
    ];

    // ---------------------------------------------------------------------------
    // Utility helpers
    // ---------------------------------------------------------------------------

    const getBreakpoint = () => {
        const w = window.innerWidth;
        if (w <= 767) return 'phone';
        if (w <= 980) return 'tablet';
        return 'desktop';
    };

    const resolveFont = (fontEntry) => {
        if (!fontEntry) return null;
        const bp = getBreakpoint();
        const desktop = fontEntry.desktop || {};
        const override = bp !== 'desktop' ? (fontEntry[bp] || {}) : {};
        const merged = { ...desktop, ...override };
        return Object.keys(merged).length ? merged : null;
    };

    const applyFontObj = (target, font) => {
        if (!font) return;
        if (font.family) target.family = font.family;
        if (font.size) target.size = font.size;
        if (font.weight) target.weight = font.weight;
        if (font.style) target.style = font.style;
    };


    // ---------------------------------------------------------------------------
    // DFPieChart – one instance per module on the page
    // ---------------------------------------------------------------------------

    class DFPieChart {

        constructor(moduleEl) {
            this.moduleEl = moduleEl;
            this.wrapper = moduleEl.querySelector('.df_pie_chart_wrapper');
            if (!this.wrapper) return;

            this.canvas = this.wrapper.querySelector('.df_pie_chart_canvas');
            if (!this.canvas) return;

            this.config = this._parseConfig();
            if (!this.config) return;

            this.ctx = this.canvas.getContext('2d');
            this.chartInstance = null;

            this._init();
        }

        // -- Bootstrap ----------------------------------------------------------

        _parseConfig() {
            try {
                return JSON.parse(this.wrapper.getAttribute('data-chart-config') || '{}');
            } catch {
                return null;
            }
        }

        _init() {
            this.slices = this._parseSlices();
            if (this.slices.data.length === 0) return;

            this.chartConfig = this._buildChartConfig();
            this._applyTooltipCallbacks();
            this._applyFonts(this.chartConfig);
            this._createChart();
            this._bindResize();
        }

        // -- Slices -------------------------------------------------------------

        _parseSlices() {
            const slices = { labels: [], data: [], bgColors: [], hoverColors: [], offsets: [], gradients: [] };

            // Remote data source — server already produced normalized slices.
            const remote = this.config.remoteSlices;
            if (Array.isArray(remote) && remote.length > 0) {
                remote.forEach((item, i) => {
                    const palette = DF_PIE_PALETTE[i % DF_PIE_PALETTE.length];
                    slices.labels.push(item.sliceLabel || 'Slice');
                    slices.data.push(parseFloat(item.sliceValue) || 0);
                    slices.bgColors.push(item.sliceColor || palette);
                    slices.hoverColors.push(item.sliceHoverColor || item.sliceColor || palette);
                    slices.offsets.push(parseInt(item.sliceOffset) || 0);
                    slices.gradients.push(null);
                });
                return slices;
            }

            const scriptEls = this.wrapper.querySelectorAll('script.df_pie_chart_item');
            scriptEls.forEach((el, i) => {
                try {
                    const item = JSON.parse(el.textContent);
                    const palette = DF_PIE_PALETTE[i % DF_PIE_PALETTE.length];
                    slices.labels.push(item.sliceLabel || 'Slice');
                    slices.data.push(parseFloat(item.sliceValue) || 0);
                    slices.bgColors.push(item.sliceColor || palette);
                    slices.hoverColors.push(item.sliceHoverColor || item.sliceColor || palette);
                    slices.offsets.push(parseInt(item.sliceOffset) || 0);
                    slices.gradients.push(
                        item.useGradient === 'on'
                            ? { from: item.gradientFrom, to: item.gradientTo }
                            : null
                    );
                } catch {
                    // Skip invalid item
                }
            });

            return slices;
        }

        // -- Chart.js config ----------------------------------------------------

        _buildChartConfig() {
            const c = this.config;
            const s = this.slices;
            const isPie = c.chartVariant !== 'doughnut';
            const cutout = isPie ? 0 : (parseInt(c.cutoutPercentage) || 50);
            const enableAnimation = c.enableAnimation === 'on';
            const animateOnScroll = c.animateOnScroll === 'on' && enableAnimation;

            this.animateOnScroll = animateOnScroll;

            return {
                type: isPie ? 'pie' : 'doughnut',
                data: {
                    labels: s.labels,
                    datasets: [{
                        data: s.data,
                        backgroundColor: s.bgColors,
                        hoverBackgroundColor: s.hoverColors,
                        borderWidth: (function () { var v = parseInt(c.borderWidth); return Number.isFinite(v) ? v : 2; })(),
                        borderColor: c.borderColor || '#ffffff',
                        offset: s.offsets,
                        _dfGradients: s.gradients,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: cutout + '%',
                    font: { family: 'Open Sans', size: 14 },
                    plugins: {
                        legend: {
                            display: c.showLegend !== 'off',
                            position: c.legendPosition || 'top',
                            align: c.legendAlign || 'center',
                            labels: { font: { size: 14 } },
                        },
                        tooltip: {
                            enabled: c.showTooltip !== 'off',
                            titleFont: { size: 14 },
                            bodyFont: { size: 14 },
                        },
                        title: {
                            display: !!c.chartTitle,
                            text: c.chartTitle || '',
                            position: c.titlePosition || 'top',
                            font: { size: 26 },
                        },
                    },
                    animation: {
                        duration: enableAnimation && !animateOnScroll
                            ? (parseInt(c.animationDuration) || 1000) : 0,
                        easing: c.animationEasing || 'easeOutQuart',
                    },
                },
            };
        }

        // -- Tooltip callbacks --------------------------------------------------

        _applyTooltipCallbacks() {
            const c = this.config;
            const prefix = c.valuePrefix || '';
            const suffix = c.valueSuffix || '';
            const showPct = c.showPercentage === 'on';
            if (!prefix && !suffix && !showPct) return;

            this.chartConfig.options.plugins.tooltip.callbacks = {
                label: (context) => {
                    const label = context.label || '';
                    const value = context.parsed;
                    let text = `${label}: ${prefix}${value}${suffix}`;

                    if (showPct) {
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const pct = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                        text += ` (${pct}%)`;
                    }

                    return text;
                },
            };
        }

        // -- Responsive fonts ---------------------------------------------------

        _applyFonts(cfg) {
            const fonts = this.config.fonts;
            if (!fonts) return;

            // Title
            const titleFont = resolveFont(fonts.title_font);
            if (titleFont) {
                cfg.options.plugins.title.font = cfg.options.plugins.title.font || {};
                applyFontObj(cfg.options.plugins.title.font, titleFont);
                if (titleFont.color) cfg.options.plugins.title.color = titleFont.color;
            }

            // Legend
            const legendFont = resolveFont(fonts.legend_font);
            if (legendFont) {
                const labels = cfg.options.plugins.legend.labels = cfg.options.plugins.legend.labels || {};
                labels.font = labels.font || {};
                applyFontObj(labels.font, legendFont);
                if (legendFont.color) labels.color = legendFont.color;
            }

            // Tooltip
            const tooltipFont = resolveFont(fonts.tooltip_font);
            if (tooltipFont) {
                const tt = cfg.options.plugins.tooltip;
                tt.titleFont = tt.titleFont || {};
                tt.bodyFont = tt.bodyFont || {};
                applyFontObj(tt.titleFont, tooltipFont);
                applyFontObj(tt.bodyFont, tooltipFont);
                if (tooltipFont.color) {
                    tt.titleColor = tooltipFont.color;
                    tt.bodyColor = tooltipFont.color;
                }
            }
        }

        // -- Chart creation & gradients -----------------------------------------

        _createChart() {
            if (this.animateOnScroll && typeof IntersectionObserver !== 'undefined') {
                const observer = new IntersectionObserver((entries) => {
                    for (const entry of entries) {
                        if (entry.isIntersecting) {
                            this._instantiateChart(true);
                            observer.unobserve(entry.target);
                        }
                    }
                }, { threshold: 0.1 });
                observer.observe(this.wrapper);
            } else {
                this._instantiateChart(false);
            }
        }

        _instantiateChart(animated) {
            if (this.chartInstance) return;

            if (animated) {
                this.chartConfig.options.animation.duration =
                    parseInt(this.config.animationDuration) || 1000;
            }

            this.chartInstance = new Chart(this.ctx, this.chartConfig);
            requestAnimationFrame(() => this._applyGradients());
        }

        _applyGradients() {
            if (!this.chartInstance?.chartArea) return;

            const { top, bottom } = this.chartInstance.chartArea;
            let changed = false;

            for (const ds of this.chartInstance.data.datasets) {
                if (!ds._dfGradients) continue;

                const bgArray = Array.isArray(ds.backgroundColor)
                    ? ds.backgroundColor.slice()
                    : [];

                ds._dfGradients.forEach((grad, i) => {
                    if (!grad) return;
                    const gradient = this.ctx.createLinearGradient(0, top, 0, bottom);
                    gradient.addColorStop(0, grad.from);
                    gradient.addColorStop(1, grad.to);
                    bgArray[i] = gradient;
                    changed = true;
                });

                if (changed) ds.backgroundColor = bgArray;
            }

            if (changed) this.chartInstance.update('none');
        }

        // -- Responsive resize --------------------------------------------------

        _bindResize() {
            if (!this.config.fonts) return;

            let lastBp = getBreakpoint();

            window.addEventListener('resize', () => {
                const newBp = getBreakpoint();
                if (newBp !== lastBp && this.chartInstance) {
                    lastBp = newBp;
                    this._applyFonts(this.chartInstance.config);
                    this.chartInstance.update('none');
                }
            });
        }
    }


    // ---------------------------------------------------------------------------
    // Bootstrap – create one instance per module
    // ---------------------------------------------------------------------------

    function init() {
        const modules = document.querySelectorAll('.difl_piechart');
        if (!modules.length) return;

        modules.forEach((moduleEl) => {
            if (moduleEl.dataset.dfChartInit) return;
            moduleEl.dataset.dfChartInit = '1';
            new DFPieChart(moduleEl);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
}());
