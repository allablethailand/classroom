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
    buildPage('payment');
}
function buildPage(page) {
    switch(page) {
        case 'payment':
            buildPaymentPage();
            break;
        default:
            console.warn('Unknown page type:', page);
    }
}