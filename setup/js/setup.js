$(document).ready(function() {
    initializeClassroomSetup();
});
function initializeClassroomSetup() {
    $(".get-setup").on("click", function() {
        $(".get-setup").removeClass("active");
        $(this).addClass("active");
        const page = $(this).attr("data-page");
        if (page) {
            buildPage(page);
        }
    });
    buildPage('position');
}
function buildPage(page) {
    switch(page) {
        case 'position':
            buildPositionPage();
            break;
        case 'payment':
            buildPaymentPage();
            break;
        default:
            console.warn('Unknown page type:', page);
    }
}