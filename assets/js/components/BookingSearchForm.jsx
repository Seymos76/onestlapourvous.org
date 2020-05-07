import React from "react";

function BookingSearchForm({ handleChange, search, departments }) {
    return (
        <form>
            <div className="row">
                <div className="col-sm-12 col-md-6 col-lg-3">
                    <fieldset className="form-group">
                        <label htmlFor="bookingDate">Date</label>
                        <input onChange={handleChange} value={search.bookingDate} type="date" name={"bookingDate"} id={"bookingDate"} className={"form-control"}/>
                    </fieldset>
                </div>
                {
                    departments &&
                    <div className="col-sm-12 col-md-6 col-lg-6">
                        <fieldset className="form-group">
                            <label htmlFor="department">Sélectionnez un département :</label>
                            <select defaultValue={search.department} onChange={handleChange} name="department" id="department" className={"form-control"}>
                                <option value="">Dans tout le pays</option>
                                {departments.map((d, k) => {
                                    return (
                                        <option
                                            key={k}
                                            value={d.id}
                                        >{d.name}</option>
                                    )
                                })}
                            </select>
                        </fieldset>
                    </div>
                }
            </div>
        </form>
    )
}

export default BookingSearchForm