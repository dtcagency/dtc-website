(function ($) {

    function initDfImageGallery() {
        const df_image_gallery = document.querySelectorAll('.difl_imagegallery');
        [].forEach.call(df_image_gallery, function (ele, index) {
            const container = ele.querySelector('.df_ig_container');
            if (!container) return;

            const settings = JSON.parse(container.dataset.settings);
            let image_obj = settings.image_ids.split(",");
            image_obj = image_obj.filter((v, i) => image_obj.indexOf(v) === i);

            const image_count = parseInt(settings.image_count);
            let grid = ele.querySelector('.grid');
            if (!grid) return;

            let target = settings.url_target;
            let ig_lightbox_options = {
                ig_lightbox: settings.use_lightbox,
                filter: false,
                filterValue: '',
                download: settings.use_lightbox_download === 'on'
            };

            if (typeof imagesLoaded === "function" && typeof Isotope === "function") {
                let iso = new Isotope(grid, {
                    layoutMode: settings.layout_mode,
                    percentPosition: false,
                    itemSelector: '.grid-item',
                    transitionDuration: '0.6s',
                    stagger: 30
                });

                // Fix lazy load layout issue
                let entries = grid.querySelectorAll('.grid-item');
                let observer = new IntersectionObserver(() => iso.layout());
                [].forEach.call(entries, (v) => observer.observe(v));

                df_ig_isotop(grid, iso);

                // ✅ Re-apply Isotope when images finish loading
                imagesLoaded(grid, function () {
                    iso.layout();
                });

                // ✅ Add load more, filters, pagination

                // load more functionality
                if (ele.querySelector('.ig-load-more-btn')) {
                    ele.querySelector('.ig-load-more-btn').addEventListener('click', function(event) {
                        event.preventDefault();
                        ele.querySelector('.ig-load-more-btn').classList.add('loading')

                        const ajaxurl = window.et_pb_custom.ajaxurl;
                        let load_more = container.querySelector('.ig-load-more-btn');
                        let loaded = parseInt(event.target.dataset.loaded);

                        fetch(ajaxurl, {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                                'Cache-Control': 'no-cache',
                            },
                            body: new URLSearchParams({
                                et_frontend_nonce: window.et_pb_custom.et_frontend_nonce,
                                action: 'df_image_gallery_fetch',
                                images: settings.image_ids,
                                page: load_more.dataset.page,
                                loaded: loaded,
                                image_count: parseInt(settings.image_count),
                                options: JSON.stringify(settings)
                            })
                        })
                        .then(function(response) { return response.json()})
                        .then(function(response) {
                            let parser = new DOMParser();
                            let parsedHtml = parser.parseFromString(response.data, 'text/html');
                            var items = parsedHtml.querySelectorAll('.df_ig_image');

                            if ( loaded >= image_obj.length) {
                                event.target.style.display = "none";
                            } else {
                                items.forEach(function(item) {
                                    grid.appendChild(item)
                                })
                                loaded = loaded + image_count;
                                event.target.setAttribute("data-loaded", loaded);
                                if(loaded >= image_obj.length){event.target.style.display = "none";}
                            }
                            iso.appended( items )
                            df_ig_isotop(grid, iso);
                            event.target.classList.remove('loading')
                        })
                        .then(function() {
                            df_ig_url_open(target, ele);

                            df_ig_use_wrap_lightbox(
                                ele,
                                ig_lightbox_options
                            );
                        })
                    })
                }

                // filter
                const filtersElem = ele.querySelector('.df_filter_buttons');

                if (filtersElem !== null) {
                    const buttons = filtersElem.querySelectorAll('.button');

                    filtersElem.addEventListener( 'click', function( event ) {
                        // Ditect mouse click outside button
                        if( !event.target.classList.contains('button')){
                            return;
                        }
                        for ( var i=0, len = buttons.length; i < len; i++ ) {
                            var button = buttons[i];
                            button.classList.remove('is-checked');
                        }

                        // only work with buttons
                        if ( !matchesSelector( event.target, 'button' ) ) {
                          return;
                        }

                        const filterValue = event.target.getAttribute('data-filter');
                        event.target.classList.add('is-checked');

                        // use matching filter function
                        iso.arrange({ filter: filterValue });
                        ig_lightbox_options.filter = true;
                        ig_lightbox_options.filterValue = filterValue;

                        df_ig_url_open(target, ele);

                        df_ig_use_wrap_lightbox(
                            ele,
                            ig_lightbox_options
                        );

                    });

                    const filterValue = filtersElem.querySelector('.is-checked').getAttribute('data-filter');
                    iso.arrange({ filter: filterValue });
                }

                // pagination functionality start
                const pageElem = ele.querySelector('.df-ig-pagination');
                if (pageElem !== null) {
                    const pageTags = pageElem.querySelectorAll('a');

                    pageElem.addEventListener( 'click', function( event ) {
                        event.preventDefault();
                        window.scrollTo({ top: $(ele).offset().top-150, behavior: 'smooth' });
                        // Ditect mouse click outside button
                        if( !event.target.classList.contains('page-numbers')){
                            return;
                        }
                        let firstPage,lastPage;
                        for ( var i=0, len = pageTags.length; i < len; i++ ) {
                            var pageTag = pageTags[i];
                            pageTag.classList.remove('current');
                        }

                        // only work with buttons
                        if ( !matchesSelector( event.target, 'a' ) ) {
                            return;
                        }

                        let pageNumber = '';
                        const imgCount = event.target.getAttribute('data-count');
                        if('on' === settings.use_number_pagination){
                            if(event.target.classList.contains('prev')){
                                const currentPage = event.target.getAttribute('data-current');
                                if(currentPage === pageTags[1].getAttribute('data-page')){
                                    pageTags[1].classList.add('current');
                                    return;
                                }
                                pageNumber = parseInt(currentPage)-1;
                                pageTags[pageNumber].classList.add('current');
                                pageTags[0].setAttribute('data-current', pageNumber);
                                pageTags[pageTags.length-1].setAttribute('data-current', pageNumber);
                            }
                            else if(event.target.classList.contains('next')){
                                const currentPage = event.target.getAttribute('data-current');
                                if(currentPage === pageTags[(parseInt(pageTags.length)-2)].getAttribute('data-page')){
                                    pageTags[pageTags.length-2].classList.add('current');
                                    return;
                                }
                                pageNumber = parseInt(currentPage)+1;
                                pageTags[pageNumber].classList.add('current');
                                pageTags[0].setAttribute('data-current', pageNumber);
                                pageTags[pageTags.length-1].setAttribute('data-current', pageNumber);
                            }
                            else{
                                pageNumber = event.target.getAttribute('data-page');
                                event.target.classList.add('current');
                                pageTags[0].setAttribute('data-current', pageNumber);
                                pageTags[pageTags.length-1].setAttribute('data-current', pageNumber);
                            }
                            (parseInt(pageNumber) > 1)?pageTags[0].style.display = 'block':pageTags[0].style.display = 'none';
                            (parseInt(pageNumber) === pageTags.length-2)?pageTags[pageTags.length-1].style.display = 'none':pageTags[pageTags.length-1].style.display = 'block';
                        }
                        else{
                            if(event.target.classList.contains('prev')){
                                const currentPage = event.target.getAttribute('data-current');
                                pageNumber = parseInt(currentPage)-1;
                                pageTags[0].setAttribute('data-current', pageNumber);
                                pageTags[pageTags.length-1].setAttribute('data-current', pageNumber);
                            }
                            else if(event.target.classList.contains('next')){
                                const currentPage = event.target.getAttribute('data-current');
                                pageNumber = parseInt(currentPage)+1;
                                pageTags[0].setAttribute('data-current', pageNumber);
                                pageTags[pageTags.length-1].setAttribute('data-current', pageNumber);
                            }
                            (parseInt(pageNumber) > 1)?pageTags[0].style.display = 'block':pageTags[0].style.display = 'none';
                            (parseInt(pageNumber) === Math.ceil(settings.image_ids.split(",").length/parseInt(imgCount)))?pageTags[pageTags.length-1].style.display = 'none':pageTags[pageTags.length-1].style.display = 'block';

                        }

                        var ajaxurl = window.et_pb_custom.ajaxurl;
                        fetch(ajaxurl, {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                                'Cache-Control': 'no-cache',
                            },
                            body: new URLSearchParams({
                                et_frontend_nonce: window.et_pb_custom.et_frontend_nonce,
                                action: 'df_image_gallery_fetch_page_data',
                                images: settings.image_ids,
                                page: parseInt(pageNumber),
                                image_count: parseInt(imgCount),
                                options: JSON.stringify(settings)
                            })
                        })
                            .then(function(response) { return response.json()})
                            .then(function(response) {
                                let parser = new DOMParser();
                                let parsedHtml = parser.parseFromString(response.data, 'text/html');
                                var items = parsedHtml.querySelectorAll('.df_ig_image');
                                grid.innerHTML = ''
                                if ( parseInt(imgCount) >= image_obj.length) {
                                    event.target.style.display = "none";
                                } else {
                                    items.forEach(function(item) {
                                        grid.appendChild(item);
                                    })
                                    if(parseInt(imgCount) >= image_obj.length){event.target.style.display = "none";}
                                }
                                 iso = new Isotope( grid, {
                                     layoutMode: settings.layout_mode,
                                     percentPosition: true,
                                     itemSelector: '.grid-item',
                                     transitionDuration: '0.6s',
                                     stagger: 30
                                 });
                                df_ig_isotop(grid, iso);
                            })
                            .then(function() {
                                df_ig_url_open(target, ele);

                                df_ig_use_wrap_lightbox(
                                    ele,
                                    ig_lightbox_options
                                );
                            })
                    });
                }
            }

            // ✅ Initialize lightbox after DOM ready
            df_ig_use_lightbox(ele.querySelector('.grid'), ig_lightbox_options);
        });
    }

    // ✅ Run initially on frontend
    $(document).ready(initDfImageGallery);

})(jQuery);

/**
 * Initialize Isotope layout after images load
 */
function df_ig_isotop(selector, iso) {
    if (!selector || !iso) return;
    imagesLoaded(selector)
        .on('progress', () => iso.layout())
        .on('done', () => {
            selector.style.opacity = 1;
        });
}

/**
 * Initialize lightGallery on a container
 */
function df_ig_use_lightbox(selector, options) {
    if (!selector || options.ig_lightbox !== 'on') return;

    // Destroy previous instance if exists
    if (selector.getAttribute('lg-uid') && window.lgData[selector.getAttribute('lg-uid')]) {
        window.lgData[selector.getAttribute('lg-uid')].destroy(true);
    }

    const settings = {
        subHtmlSelectorRelative: true,
        addClass: 'df_ig_lightbox',
        counter: false,
        download: options.download
    };

    if (options.filter) {
        settings.selector = options.filterValue.replace('*', '');
    }

    lightGallery(selector, settings);
}

/**
 * Attach click URLs to grid items
 */
function df_ig_url_open(target, ele) {
    if (!ele) return;
    const elements = ele.querySelectorAll('.item-content');
    elements.forEach((image) => {
        const url = image.dataset.url;
        if (url) {
            image.addEventListener('click', (event) => {
                if (target === 'same_window') {
                    window.location = url;
                } else {
                    window.open(url);
                }
            });
        }
    });
}

/**
 * Initialize lightbox for individual grid items inside wrapper
 */
function df_ig_use_wrap_lightbox(selector, options) {
    if (!selector || options.ig_lightbox !== 'on') return;

    const settings = {
        subHtmlSelectorRelative: true,
        addClass: 'df_ig_lightbox',
        counter: false,
        download: options.download
    };

    selector.querySelectorAll(".df_ig_image").forEach((item) => {
        item.addEventListener("click", function () {
            const parentDiv = this.parentElement;
            if (options.filter) {
                settings.parentDiv = options.filterValue.replace('*', '');
            }

            // Destroy any existing gallery on parentDiv
            if (parentDiv.getAttribute('lg-uid') && window.lgData[parentDiv.getAttribute('lg-uid')]) {
                window.lgData[parentDiv.getAttribute('lg-uid')].destroy(true);
            }

            lightGallery(parentDiv, settings);
        });
    });
}

/**
 * Handle Filter Clicks without full DOM reset
 */
function igHandleClick(event) {
    if (!event.target.dataset.filter) return;

    const parentContainer = event.target.closest('.df_ig_container');
    if (!parentContainer) return;

    const settings = JSON.parse(parentContainer.dataset.settings);
    const listWrap = parentContainer.querySelector(".df_ig_gallery");
    const filter = event.target.dataset.filter;
    const ig_lightbox_options = {
        ig_lightbox: settings.use_lightbox,
        filter: filter !== '*',
        filterValue: filter,
        download: settings.use_lightbox_download === 'on'
    };

    // Remove active class from all filter buttons
    const buttons = parentContainer.querySelectorAll('[data-filter]');
    buttons.forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');

    // Show all or filter
    const allItems = listWrap.querySelectorAll('.df_ig_image');
    allItems.forEach(item => {
        if (filter === '*' || item.matches(filter)) {
            item.style.display = '';
        } else {
            item.style.display = 'none';
        }
    });

    // Re-init lightbox on the filtered view
    df_ig_use_lightbox(listWrap, ig_lightbox_options);
}
