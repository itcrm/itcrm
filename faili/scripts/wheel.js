function handleMouseWheel(delta) {
    var scroll = window.getScroll();
    var x = scroll.x;
    window.scrollTo(x - 100 * delta, 0);
}

function wheel(event) {
    var delta = 0;
    if (!event) event = window.event;
    if (event.wheelDelta) {
        delta = event.wheelDelta / 120;
        if (window.opera) delta = -delta;
    } else if (event.detail) {
        delta = -event.detail / 3;
    }
    if (delta) handleMouseWheel(delta);
    if (event.preventDefault) event.preventDefault();
    event.returnValue = false;
}

window.addEvent("domready", function () {
    if (window.addEventListener) {
        window.addEventListener("DOMMouseScroll", wheel, false);
    }
    window.onmousewheel = document.onmousewheel = wheel;
});
