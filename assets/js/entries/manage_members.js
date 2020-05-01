
// Need jQuery? Install it with "yarn add jquery", then uncomment to import it.
import $ from 'jquery';

$(document).ready( function () {
    const $filterForm = $("#table_filter_form");
    const $emailFilter = $("#email_filter");
    const $roleFilter = $("#role_filter");
    const $lastNameFilter = $("#lastname_filter");
    const $firstNameFilter = $("#firstname_filter");
    $emailFilter.on('change', function (e) {
        $filterForm.submit();
    });
    $roleFilter.on('change', function (e) {
        $filterForm.submit();
    });
    $lastNameFilter.on('change', function (e) {
        $filterForm.submit();
    });
    $firstNameFilter.on('change', function (e) {
        $filterForm.submit();
    });
});