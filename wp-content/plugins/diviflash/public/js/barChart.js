(function () {
    'use strict';

    // Auto palette used when datasets come from a URL data source (no children → no per-dataset color).
    const DF_BAR_PALETTE = [
        '#3366cc', '#dc3912', '#ff9900', '#109618', '#990099',
        '#0099c6', '#dd4477', '#66aa00', '#b82e2e', '#316395',
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
    };


    // ---------------------------------------------------------------------------
    // DFBarChart – one instance per module on the page
    // ---------------------------------------------------------------------------

    class DFBarChart {

        constructor(moduleEl) {
            this.moduleEl = moduleEl;
            this.wrapper = moduleEl.querySelector('.df_bar_chart_wrapper');
            if (!this.wrapper) return;

            this.canvas = this.wrapper.querySelector('.df_bar_chart_canvas');
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
            this.labels = this._parseLabels();
            this.datasets = this._parseDatasets();
            if (this.datasets.length === 0) return;

            this.chartConfig = this._buildChartConfig();
            this._applyTooltipCallbacks();
            this._applyYAxisLimits();
            this._applyFonts(this.chartConfig);
            this._createChart();
            this._bindResize();
        }

        // -- Labels & Datasets --------------------------------------------------

        _parseLabels() {
            const { chartLabels } = this.config;
            return Array.isArray(chartLabels) && chartLabels.length > 0
                ? chartLabels
                : ['Jan', 'Feb', 'Mar'];
        }

        _parseDatasets() {
            // Remote data source — server already parsed it into the config.
            if (Array.isArray(this.config.remoteDatasets) && this.config.remoteDatasets.length > 0) {
                return this.config.remoteDatasets.map((ds, i) => this._buildDataset({
                    datasetName: ds.name || `Dataset ${i + 1}`,
                    values: ds.values || [],
                    barColor: DF_BAR_PALETTE[i % DF_BAR_PALETTE.length],
                    barHoverColor: DF_BAR_PALETTE[i % DF_BAR_PALETTE.length],
                    borderColor: DF_BAR_PALETTE[i % DF_BAR_PALETTE.length],
                    borderWidth: 1,
                    useGradient: 'off',
                }));
            }

            const scriptEls = this.wrapper.querySelectorAll('script.df_bar_chart_dataset');
            const datasets = [];

            // Track index so _buildDataset can pull a palette color per
            // sibling position when the dataset hasn't set its own color.
            // Matches the VB parent's children-source palette fallback.
            scriptEls.forEach((el, i) => {
                try {
                    const ds = JSON.parse(el.textContent);
                    datasets.push(this._buildDataset(ds, i));
                } catch {
                    // Skip invalid dataset
                }
            });

            return datasets;
        }

        _buildDataset(ds, i = 0) {
            const c = this.config;
            const palette = DF_BAR_PALETTE[i % DF_BAR_PALETTE.length];

            // Parse and normalize values to match labels count
            let values = Array.isArray(ds.values)
                ? ds.values.map(v => parseFloat(v) || 0)
                : [];
            while (values.length < this.labels.length) values.push(0);
            values = values.slice(0, this.labels.length);

            // Gradient config
            const gradient = ds.useGradient === 'on'
                ? { top: ds.gradientTop || palette, bottom: ds.gradientBottom || palette }
                : null;

            return {
                label: ds.datasetName || 'Dataset 1',
                data: values,
                backgroundColor: ds.barColor || palette,
                hoverBackgroundColor: ds.barHoverColor || palette,
                borderColor: ds.borderColor || palette,
                borderWidth: ds.borderWidth !== undefined ? parseInt(ds.borderWidth) : 1,
                borderRadius: parseInt(c.barBorderRadius) || 0,
                barPercentage: (parseFloat(c.barPercentage) || 80) / 100,
                categoryPercentage: (parseFloat(c.categoryPercentage) || 80) / 100,
                _dfGradients: values.map(() => gradient),
            };
        }

        // -- Chart.js config ----------------------------------------------------

        _buildChartConfig() {
            const c = this.config;
            const isHorizontal = c.variant === 'horizontal' || c.variant === 'horizontal_stacked';
            const isStacked = c.variant === 'stacked' || c.variant === 'horizontal_stacked';
            const enableAnimation = c.enableAnimation === 'on';
            const animateOnScroll = c.animateOnScroll === 'on' && enableAnimation;

            this.isHorizontal = isHorizontal;
            this.animateOnScroll = animateOnScroll;

            return {
                type: 'bar',
                data: { labels: this.labels, datasets: this.datasets },
                options: {
                    indexAxis: isHorizontal ? 'y' : 'x',
                    responsive: true,
                    maintainAspectRatio: false,
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
                    scales: {
                        x: {
                            display: c.showXAxis !== 'off',
                            stacked: isStacked,
                            ticks: { font: { size: 14 } },
                            title: { display: !!c.xAxisTitle, text: c.xAxisTitle || '', font: { size: 14 } },
                            grid: {
                                display: c.showXGrid !== 'off',
                                color: c.gridColor || 'rgba(0,0,0,0.1)',
                                lineWidth: parseInt(c.gridWidth ?? 1),
                            },
                        },
                        y: {
                            display: c.showYAxis !== 'off',
                            stacked: isStacked,
                            beginAtZero: c.yBeginAtZero !== 'off',
                            ticks: { font: { size: 14 } },
                            title: { display: !!c.yAxisTitle, text: c.yAxisTitle || '', font: { size: 14 } },
                            grid: {
                                display: c.showYGrid !== 'off',
                                color: c.gridColor || 'rgba(0,0,0,0.1)',
                                lineWidth: parseInt(c.gridWidth ?? 1),
                            },
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
            const prefix = this.config.valuePrefix || '';
            const suffix = this.config.valueSuffix || '';
            if (!prefix && !suffix) return;

            const axis = this.isHorizontal ? 'x' : 'y';
            this.chartConfig.options.plugins.tooltip.callbacks = {
                label: (context) => {
                    const val = context.parsed[axis];
                    return `${context.dataset.label}: ${prefix}${val}${suffix}`;
                },
            };
        }

        // -- Y-axis limits ------------------------------------------------------

        _applyYAxisLimits() {
            const c = this.config;
            const yScale = this.chartConfig.options.scales.y;

            if (c.yMin !== '' && c.yMin !== undefined) {
                yScale.min = parseFloat(c.yMin);
            }
            if (c.yMax !== '' && c.yMax !== undefined) {
                yScale.max = parseFloat(c.yMax);
            }
            if (c.yStepSize !== '' && c.yStepSize !== undefined) {
                yScale.ticks = yScale.ticks || {};
                yScale.ticks.stepSize = parseFloat(c.yStepSize);
            }
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

            // Axis ticks & titles
            const axisTickFont = resolveFont(fonts.axis_tick_font);
            const axisTitleFont = resolveFont(fonts.axis_title_font);

            for (const axis of ['x', 'y']) {
                const scale = cfg.options.scales[axis];

                if (axisTickFont) {
                    scale.ticks = scale.ticks || {};
                    scale.ticks.font = scale.ticks.font || {};
                    applyFontObj(scale.ticks.font, axisTickFont);
                    if (axisTickFont.color) scale.ticks.color = axisTickFont.color;
                }

                if (axisTitleFont) {
                    scale.title.font = scale.title.font || {};
                    applyFontObj(scale.title.font, axisTitleFont);
                    if (axisTitleFont.color) scale.title.color = axisTitleFont.color;
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
            let anyChanged = false;

            for (const ds of this.chartInstance.data.datasets) {
                if (!ds._dfGradients) continue;

                const bgArray = Array.isArray(ds.backgroundColor)
                    ? ds.backgroundColor.slice()
                    : [];

                let dsChanged = false;
                ds._dfGradients.forEach((grad, i) => {
                    if (!grad) return;
                    const gradient = this.ctx.createLinearGradient(0, top, 0, bottom);
                    gradient.addColorStop(0, grad.top);
                    gradient.addColorStop(1, grad.bottom);
                    bgArray[i] = gradient;
                    dsChanged = true;
                });

                if (dsChanged) {
                    ds.backgroundColor = bgArray;
                    anyChanged = true;
                }
            }

            if (anyChanged) this.chartInstance.update('none');
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
        const modules = document.querySelectorAll('.difl_barchart');
        if (!modules.length) return;

        modules.forEach((moduleEl) => {
            if (moduleEl.dataset.dfChartInit) return;
            moduleEl.dataset.dfChartInit = '1';
            new DFBarChart(moduleEl);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
}());
