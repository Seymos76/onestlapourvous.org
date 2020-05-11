import React, { useState, useEffect } from "react";
import ReactDOM from "react-dom";
import Pagination from "../components/Pagination";
import BookingConfirmation from "../components/BookingConfirmation";
import BookingRow from "../components/BookingRow";
import bookingApi from "../services/bookingApi";
import bookingFilters from "../utils/bookingFilters";
import BookingSearchForm from "../components/BookingSearchForm";
import geolocationApi from "../services/geolocationApi";

function BookingSearch() {
    const [currentPage, setCurrentPage] = useState(1);
    const [loading, setLoading] = useState(true);
    const [isConfirmed, setIsConfirmed] = useState(false);
    const [user, setUser] = useState({
        id: undefined,
        country: undefined,
        department: undefined
    });
    const [appoints, setAppoints] = useState([]);
    const [filtered, setFiltered] = useState([]);
    const [booking, setBooking] = useState({});
    const [departments, setDepartments] = useState([]);
    const [search, setSearch] = useState({
        bookingDate: undefined,
        department: undefined,
    });

    const handlePageChange = page => {
        setCurrentPage(page);
    }

    const itemsPerPage = 10;

    const handleChange = ({currentTarget}) => {
        const { name, value } = currentTarget;
        setSearch({...search, [name]: value});
    };

    const handleSubmit = async event => {
        event.preventDefault();
        const { id: userId } = user;
        const currentBooking = JSON.parse(localStorage.getItem('booking'));
        const response = await bookingApi.createBooking(currentBooking.id, userId);
        if (response.status !== 200) {
            console.log('Une erreur est survenue');
        } else {
            setIsConfirmed(true);
            setTimeout(resetSearch, 3000);
        }
    }

    const resetSearch = async () => {
        setLoading(true);
        localStorage.getItem('booking') && localStorage.removeItem('booking');
        setIsConfirmed(false);
        setBooking({});
        await updateBookingsByApiFilters();
        setLoading(false);
    }

    const cancelBooking = () => {
        if (localStorage.getItem('booking')) {
            localStorage.removeItem('booking');
        }
        setBooking({});
    }

    const filterWithTherapistDelay = (appoints) => {
        return appoints.filter(appoint => bookingFilters.filterFutureAppointments(appoint));
    }

    const createPatientBooking = (appointId) => {
        const booking = bookingFilters.filterById(appointId, appoints)[0];
        if (booking === {}) {
            return;
        }
        setBooking(booking);
        bookingFilters.setBookingToLocalStorage(booking);
    }

    const updateAppointsByUserFilters = () => {
        const updatedAppoints = bookingFilters.updateAppointsByFilters(appoints, search);
        setFiltered(updatedAppoints);
    }

    const getCurrentUser = () => {
        const $targetElement = document.getElementById("booking_search");
        if ($targetElement !== null) {
            const $targetData =  $targetElement.dataset;
            const userId = $targetData.user;
            const country = $targetData.country;
            const department = $targetData.defaultDepartment;
            setUser({ id: userId, country, department });
        }
    }

    const updateBookingsByApiFilters = async () => {
        const bookings = await bookingApi.updateBookingsByFilters(user);
        if (bookings.length > 0) {
            console.log('bookings:',bookings);
            const appoints = filterWithTherapistDelay(bookings);
            setAppoints(appoints);
        } else {
            console.log('bookings:',bookings);
            setAppoints([]);
        }
    }

    useEffect(() => {
        console.log('init user');
        getCurrentUser();
        console.log('user ok');
        setLoading(false);
    }, []);

    useEffect(() => {
        setLoading(true);
        (async function initDepartment() {
            if (user.country !== undefined) {
                console.log('init departments');
                const departments = await geolocationApi.getDepartmentsByCountry(user.country);
                setDepartments(departments);
                console.log('departments ok');
            }
        }());
        setLoading(false);
    },[user.country]);

    useEffect(() => {
        if (user.country !== undefined) {
            setLoading(true);
            (async function firstBookingSearch() {
                await updateBookingsByApiFilters();
                console.log('first search update');
            })();
            setLoading(false);
        }

    },[departments]);

    useEffect(() => {
        if (search.department !== undefined) {
            setLoading(true);
            updateAppointsByUserFilters();
            console.log('booking department filtered');
            setLoading(false);
        }
    },[search.department]);

    useEffect(() => {
        console.log('bookingDate changed');
        if (search.bookingDate !== undefined) {
            setLoading(true);
            updateAppointsByUserFilters();
            setLoading(false);
            console.log('booking date filtered');
        }
    },[search.bookingDate]);

    const appointsToDisplay = filtered.length ? filtered : appoints;

    const paginatedAppoints = appointsToDisplay.length > itemsPerPage ? Pagination.getData(
        appointsToDisplay,
        currentPage,
        itemsPerPage
    ) : appointsToDisplay;

    return (
        <>
            <div className="container-fluid mb-3">
                {
                    (localStorage.getItem('booking') && booking !== {}) ?
                        (<div>
                            <BookingConfirmation
                                loading={loading}
                                isConfirmed={isConfirmed}
                                handleSubmit={handleSubmit}
                                booking={JSON.parse(localStorage.getItem('booking'))}
                            />
                            <br/>
                            {!isConfirmed && <button className={"btn btn-danger"} type="button" onClick={cancelBooking}>Annuler et prendre un autre rendez-vous</button>}
                        </div>) :
                        (<div>
                            <BookingSearchForm departments={departments} search={search} handleChange={handleChange} />
                            {!loading ?
                                (paginatedAppoints.length > 0 ?
                                    <div className="table-responsive js-rep-log-table">
                                        <table className="table table-striped table-sm">
                                            <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Thérapeute</th>
                                                <th>Date</th>
                                                <th>Début</th>
                                                <th>Fin</th>
                                                <th>Département</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            {paginatedAppoints.map(a => {
                                                return (
                                                    <tr key={a.id}>
                                                        <BookingRow
                                                            booking={a}
                                                            createPatientBooking={createPatientBooking}
                                                        />
                                                    </tr>
                                                )
                                            })}
                                            </tbody>
                                        </table>
                                    </div> :
                                    <p>Aucune disponibilité...</p>) : (
                                    <div className="table-responsive js-rep-log-table">
                                        <table className="table table-striped table-sm">
                                            <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Thérapeute</th>
                                                <th>Date</th>
                                                <th>Début</th>
                                                <th>Fin</th>
                                                <th>Département</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr>
                                                <td>Chargement en cours...</td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                )
                            }
                            {itemsPerPage < appointsToDisplay.length &&
                            <Pagination
                                currentPage={currentPage}
                                itemsPerPage={itemsPerPage}
                                onPageChanged={handlePageChange}
                                length={appointsToDisplay.length}
                            />
                            }
                        </div>)
                }
            </div>
        </>
    )
}

const rootElement = document.querySelector("#booking_search");
ReactDOM.render(<BookingSearch/>, rootElement);
