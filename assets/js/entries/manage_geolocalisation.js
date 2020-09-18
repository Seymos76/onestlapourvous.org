import $ from "jquery";

$(document).ready( function () {
    const $filterForm = $("#table_filter_form");
    const $countryFilter = $("#country_filter");
    $countryFilter.on('change', function (e) {
        $filterForm.submit();
    });
});