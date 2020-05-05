import React, { useState, useEffect } from "react";
import ReactDOM from "react-dom";
import Pagination from "../components/Pagination";
import BookingConfirmation from "../components/BookingConfirmation";
import BookingRow from "../components/BookingRow";
import bookingApi from "../services/bookingApi";
import bookingFilters from "../utils/bookingFilters";
import userApi from "../services/userApi";
import BookingSearchForm from "../components/BookingSearchForm";
import geolocationApi from "../services/geolocationApi";
import {toast, ToastContainer} from "react-toastify";
//import 'react-toastify/dist/ReactToastify.css';

function PatientSearch() {
    const [currentPage, setCurrentPage] = useState(1);
    const [loading, setLoading] = useState(true);
    const [isConfirmed, setIsConfirmed] = useState(false);
    const [user, setUser] = useState({
        id: null,
        country: null,
        department: null
    });
    const [appoints, setAppoints] = useState([]);
    const [filtered, setFiltered] = useState([]);
    const [booking, setBooking] = useState({});
    const [departments, setDepartments] = useState([]);
    const [search, setSearch] = useState({
        bookingDate: null,
        department: null,
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

    const setCurrentUser = async () => {
        const $targetElement = document.getElementById("patient_search");
        if ($targetElement !== null) {
            const $targetData = $targetElement.dataset;
            console.log('dataset:',$targetData);
            const userId = $targetData.user;
            //const patient = await userApi.getCurrentPatient(userId);
            //console.log('user:',patient);
            const country = $targetData.country;
            const department = $targetData.defaultDepartment;
            setUser({ id: userId, country, department });
            return country;
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

    const getCountryDepartments = async (country) => {
        const departments = await geolocationApi.getDepartmentsByCountry(country);
        setDepartments(departments);
    }

    const filterWithTherapistDelay = (appoints) => {
        return appoints.filter(appoint => bookingFilters.filterWithTherapistDelay(appoint));
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

    const updateBookingsByApiFilters = async () => {
        const bookings = await bookingApi.updateBookingsByFilters(search, user);
        if (bookings.length > 0) {
            const appoints = filterWithTherapistDelay(bookings);
            setAppoints(appoints);
            toast.info("Disponibilités mises à jour");
        } else {
            setAppoints([]);
            toast.info("Pas de disponibilité dans ce département");
        }
    }

    const cancelBooking = () => {
        if (localStorage.getItem('booking')) {
            localStorage.removeItem('booking');
        }
        setBooking({});
    }

    const appointsToDisplay = filtered.length ? filtered : appoints;

    const paginatedAppoints = appointsToDisplay.length > itemsPerPage ? Pagination.getData(
        appointsToDisplay,
        currentPage,
        itemsPerPage
    ) : appointsToDisplay;

    useEffect(() => {
        (async function initUser() {
            const country = await setCurrentUser();
            console.log('init user');
            await getCountryDepartments(country);
            console.log('init departments');
        })();
    }, []);

    useEffect(() => {
        (async function initBookings() {
            await updateBookingsByApiFilters();
            console.log('init bookings');
        })();
        setLoading(false);
    }, [user.department]);

    useEffect(() => {
        setLoading(true);
        (async function departmentSearchUpdate() {
            await updateBookingsByApiFilters();
            console.log('department search update');
        })();
        setLoading(false);
    },[search.department]);

    useEffect(() => {
        updateAppointsByUserFilters();
        console.log('filters update');
    },[search.bookingDate]);

    return (
        <>
            {/*<div className="container-fluid">
                <ToastContainer position={toast.POSITION.TOP_CENTER}/>
            </div>*/}
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

const rootElement = document.querySelector("#patient_search");
ReactDOM.render(<PatientSearch/>, rootElement);
