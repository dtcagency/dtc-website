(($) => {
	"use strict";
	$(window).on('load', function() {
		$('.difl_social_share_item').each(function() {
			// const animationClass = $(this).data('animation');
			// $(this).on("mouseenter", () => $(this).addClass(animationClass));
			// $(this).on("mouseleave", () => $(this).removeClass(animationClass));
			$(this).on("click", (event) => {
				// $(this).removeClass(animationClass);
				if ($(this).hasClass('difl_print')) {
					event.preventDefault();
					print();
				}
			});
		});

		$('.difl_social_share').each(function(index, social_share) {
			process_tooltip(index, social_share);
		});

		function process_tooltip(index, social_share) {
			const settings = $(social_share).find('.difl_social_share_container').data('settings');
			const social_share_items = $(social_share).find('.difl_social_share_item');
			const tooltipStatus = settings.tooltip_enable;
			const disableOnMobile = settings.disable_on_mobile && window.innerWidth < 768;
			const ele_class = social_share.classList.value.split(" ").filter(function(class_name){
				return class_name.indexOf('difl_social_share_') !== -1;
			});

			if(social_share_items.length > 0 && tooltipStatus && !disableOnMobile){
				const tooltipMaxWidth = parseInt(settings.maxWidth, 10) || 350;
				const options = {
					arrow: settings.arrow,
					animation: settings.animation,
					placement: settings.placement,
					trigger: settings.trigger,
					allowHTML: true,
					followCursor: 'mouseenter focus' === settings.trigger ? settings.followCursor: false,
					interactive: settings.interactive,
					interactiveBorder: parseInt(settings.interactiveBorder),
					interactiveDebounce: parseInt(settings.interactiveDebounce),
					maxWidth: tooltipMaxWidth,
					offset:[parseInt(settings.offsetSkidding) , parseInt(settings.offsetDistance)],
					theme :`.${ele_class[0]}` , // for each module initiat , make different theme
					appendTo: document.body,
					delay: [parseInt(settings.delay), parseInt(settings.interactiveDebounce)],
					onCreate(instance) {
						const box = instance.popper;
						if (box && tooltipMaxWidth) {
							box.style.setProperty('max-width', tooltipMaxWidth + 'px', 'important');
						}
					}
				};

				social_share_items.each(function(item_index, social_share_item) {
					const tooltipContent = $(social_share_item).find(`noscript`).text();
					if(tooltipContent === ''){
						tippy(social_share_item, options).disable();
					}else{
						options['content'] =  tooltipContent;
						tippy(social_share_item, options);
					}
				});
			}
		}
	});
})(jQuery);