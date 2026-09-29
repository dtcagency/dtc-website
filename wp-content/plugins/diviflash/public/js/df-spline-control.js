const attachSplinePinchScrollLock = (rootEl) => {
    if (!rootEl) {
        return;
    }

    rootEl.style.touchAction = "none";

    const onWheel = (e) => {
        if (e.ctrlKey) {
            e.preventDefault();
        }
    };
    rootEl.addEventListener("wheel", onWheel, { passive: false, capture: true });

    const onGesture = (e) => {
        e.preventDefault();
    };
    rootEl.addEventListener("gesturestart", onGesture, { passive: false, capture: true });
    rootEl.addEventListener("gesturechange", onGesture, { passive: false, capture: true });
    rootEl.addEventListener("gestureend", onGesture, { passive: false, capture: true });
};

document.addEventListener("DOMContentLoaded", () => {
    const splines = document.querySelectorAll(".df-spline-viewer");

    // Helper to normalize boolean values
    const normalizeBoolean = (val) => val === "on" || val === "1" || val === true;

    splines.forEach((el) => {
        const options = el.dataset.options ? JSON.parse(el.dataset.options) : null;
        if (!options || !options.url) return;
        const isLoadingAnimEnabled = normalizeBoolean(options["loading-anim"]);

        el.style.width = "100%";
        el.style.height = "100%";
        el.style.minHeight = "0";
        el.style.flex = "1 1 auto";
        el.style.overflow = "hidden";
        el.style.boxSizing = "border-box";
        el.style.position = "relative";

        attachSplinePinchScrollLock(el);

        // Build optional attributes dynamically
        const attrString = Object.entries(options)
            .filter(([key, val]) => val !== undefined && val !== "" && key !== "width" && key !== "height")
            .map(([key, val]) => {
                if (key === "loading-anim-type" && !isLoadingAnimEnabled) {
                    return "";
                }
                // Boolean attributes (presence only)
                if (["hint", "unloadable", "loading-anim"].includes(key)) {
                    return normalizeBoolean(val) ? `${key}` : "";
                }
                return `${key}="${val}"`;
            })
            .filter(Boolean) // remove empty strings
            .join(" ");

        el.innerHTML = `
            <spline-viewer
                style="width:100%;height:100%;min-height:0;display:block;border:none;box-sizing:border-box;touch-action:none;"
                ${attrString}
            ></spline-viewer>
        `;
    });
});
