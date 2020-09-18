import React, {useEffect, useState} from "react";
import {formatDateForTable, formatTime} from "../utils/DateUtils";

function BookingConfirmation({ loading, isConfirmed, booking, handleSubmit }) {
    const [isLoading, setIsLoading] = useState(loading);

    const initBookingConfirmation = () => {
        console.log('booking confirmation init');
        console.log('booking:',booking);
        const localBooking = JSON.parse(localStorage.getItem('booking'));
        console.log('local booking:',localBooking);
        setIsLoading(true);
        if (booking !== {}) {
            booking = JSON.parse(localStorage.getItem('booking'));
            setIsLoading(false);
        }
    }

    useEffect(() => {
        initBookingConfirmation();
    }, [isLoading]);

    return (
        <div>
            {isLoading && <div className="container"><p>Chargement en cours...</p></div>}
            {
                (!isLoading && booking !== []) &&
                <div className="container">
                    {
                        isConfirmed ?
                            <div className="alert alert-success alert-dismissible fade show" role="alert">
                                Rendez-vous confirmé !
                                <button type="button" className="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div> :
                            <div>
                                <h2>Demande de rendez-vous</h2>
                                Le {booking.bookingDate && formatDateForTable(booking.bookingDate)} à {booking.bookingStart && formatTime(booking.bookingStart)} avec {booking?.therapist?.firstName} {booking?.therapist?.lastName}
                                <div className="alert alert-warning">
                                    En cas d'annulation, merci de prévenir votre thérapeute au plus vite en cliquant sur le bouton d'annulation disponible dans vos rendez-vous.
                                </div>
                                <form onSubmit={handleSubmit}>
                                    <button className="btn btn-primary" type="submit">Confirmer mon rendez-vous</button>
                                </form>
                            </div>
                    }
                </div>
            }
        </div>
    )
}

export default BookingConfirmation