import moment from "moment";
import {formatDate, formatDateReverse, getArrayTime} from "./DateUtils";

function filterWithTherapistDelay(a) {
    const nowDate = moment().format('DD/MM/YYYY');
    console.log('nowDate:',nowDate);
    console.log('format date:',formatDate(a.bookingDate));
    if (nowDate === formatDate(a.bookingDate)) {
        const arrayTime = getArrayTime(a.bookingStart);
        const nowTime = moment();
        const targetTime = moment().hours(arrayTime[0]).minutes(arrayTime[1]);
        const delay = targetTime.diff(nowTime, 'hours');
        if ((targetTime > nowTime) && delay >= 12) {
            return a;
        }
    } else if (nowDate < formatDate(a.bookingDate)) {
        return a;
    } else {
        console.log("créneau passé");
    }
}

function filterFutureAppointments(appointment) {
    const nowDate = moment().format('DD/MM/YYYY');
    if (nowDate < formatDate(appointment.bookingDate)) {
        return appointment;
    }
}

function filterById(id, appoints) {
    return appoints.filter(function (appoint, i) {
        return appoint.id === id;
    });
}

function setBookingToLocalStorage(booking) {
    try {
        if (localStorage.getItem('booking')) {
            localStorage.removeItem('booking');
            localStorage.setItem('booking', JSON.stringify(booking));
        } else {
            localStorage.setItem('booking', JSON.stringify(booking));
        }
        return true;
    } catch (e) {
        console.log(e);
        return false;
    }
}

function updateAppointsByFilters(appoints, search) {
    if (search.bookingDate !== undefined) {
        return appoints.filter(function (a) {
            return formatDateReverse(a.bookingDate) === search.bookingDate;
        });
    } else if (search.department !== undefined) {
        return appoints.filter(function (a) {
            return parseInt(a.therapist?.department?.id) === parseInt(search.department);
        });
    } else if (search.department !== undefined && search.bookingDate !== undefined) {
        return appoints.filter(function (a) {
            return parseInt(a.therapist?.department?.id) === parseInt(search.department) && formatDateReverse(a.bookingDate) === search.bookingDate;
        });
    } else {
        return appoints;
    }
}

function mergeBookings(array1, array2) {
    var result_array = [];
    var arr = array1.concat(array2);
    var len = arr.length;
    var assoc = {};

    while(len--) {
        var item = arr[len];

        if(!assoc[item])
        {
            result_array.unshift(item);
            assoc[item] = true;
        }
    }

    return result_array;
}

export default {
    filterWithTherapistDelay,
    filterFutureAppointments,
    filterById,
    setBookingToLocalStorage,
    updateAppointsByFilters,
    mergeBookings
}