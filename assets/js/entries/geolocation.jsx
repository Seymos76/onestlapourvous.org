import React, { useState, useEffect } from "react";
import ReactDOM from "react-dom";
import axios from "axios";
import {API_URL} from "../config";
import {CITY_FILE} from "../utils/cityFiles";
import geolocationApi from "../services/geolocationApi";

export function Geolocation() {
    const [loading, setLoading] = useState(true);
    const [selection, setSelection] = useState({
        country: "fr",
        department: ""
    });
    const [departments, setDepartments] = useState([]);

    const handleChange = ({currentTarget}) => {
        const { name, value } = currentTarget;
        setSelection({...selection, [name]: value});
    };

    const getDepartmentsByCountry = async () => {
        const departs = await geolocationApi.getDepartmentsByCountry(selection.country);
        setDepartments([]);
        setDepartments(departs.length > 0 && departs);
        setLoading(false);
    }

    const getKeyForDepartment = () => {
        if (selection.country === 'fr') {
            return "code";
        } else if (selection.country === 'be') {
            return "name";
        } else if (selection.country === 'ch') {
            return "name";
        }  else if (selection.country === 'lu') {
            return "name";
        } else {
            return "code";
        }
    }

    useEffect(() => {
        (async function updateDepartments() {
            await getDepartmentsByCountry();
        })();
    }, [selection.country]);

    return (
        <>
            {loading && <div className={"alert alert-warning"}>Veuillez patienter avant de vous inscrire.</div>}
            {!loading &&
            <div className="row">
                <div className="form-group col-md-6">
                    <select onChange={handleChange} name="country" id="country" className={"form-control"}>
                        <option value="">Sélectionnez votre pays</option>
                        <option value="fr">France</option>
                        <option value="be">Belgique</option>
                        <option value="lu">Luxembourg</option>
                        <option value="ch">Suisse</option>
                    </select>
                </div>
                <div className="form-group col-md-6">
                    <select onChange={handleChange} name="department" id="department" className={"form-control"}>
                        <option value="">Sélectionnez votre département</option>
                        {departments &&
                        departments.map((depart, key) => {
                            const arrKey = getKeyForDepartment();
                            return (
                                <option key={key} value={depart[arrKey]}>{depart.name}</option>
                            )
                        })
                        }
                    </select>
                </div>
            </div>
            }
        </>
    )
}

function CitySearchFields() {
    return (
        {/*
            <div className="row">
                <div className="form-group col-md-6">
                    <label htmlFor="citySearch">Saisissez le nom de votre commune</label>
                    <input onChange={handleChange} value={selection.citySearch} type="text" name={"citySearch"} id={"citySearch"} className={"form-control"}/>
                    <input type="hidden" name="city" value={selection.citySearch !== undefined && selection.city}/>
                </div>
                {
                    selection.citySearch.length >= 2 &&
                    <div className="form-group col-md-6">
                        <label htmlFor="town">Sélectionnez ensuite votre localisation</label>
                        <select onChange={handleCitySelect} name="town" id="town" className={"form-control"}>
                            <option value="">Sélectionnez votre localisation</option>
                            {
                                filteredCities.length && filteredCities.map((city, key) => {
                                    const arrKey = getKeyForTown();
                                    return (
                                        <option key={key} value={city[arrKey]}>{city[arrKey]}</option>
                                    )
                                })
                            }
                        </select>
                    </div>
                }
            </div>
            */}
    )
}

const rootElement = document.querySelector("#geolocation");
ReactDOM.render(<Geolocation/>, rootElement);
