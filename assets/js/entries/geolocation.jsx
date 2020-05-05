import React, { useState, useEffect } from "react";
import ReactDOM from "react-dom";
import axios from "axios";
import {API_URL} from "../config";
import {CITY_FILE} from "../utils/cityFiles";
import geolocationApi from "../services/geolocationApi";

export function Geolocation() {
    const [selection, setSelection] = useState({
        country: "fr",
        department: "",
        city: "",
        citySearch: ""
    });

    const [departments, setDepartments] = useState([]);
    const [cities, setCities] = useState([]);
    const [filteredCities, setFilteredCities] = useState([]);

    const handleChange = ({currentTarget}) => {
        const { name, value } = currentTarget;
        if (name === 'department') {
            setSelection({...selection, [name]: value, city: ""})
        } else {
            setSelection({...selection, [name]: value});
        }
    };

    const handleCitySelect = ({currentTarget}) => {
        const { value } = currentTarget;
        const searchingFile = CITY_FILE[selection.country];
        const filter = getKeyForTown();
        const city = searchingFile.filter(
            c => c[filter] === value
        );
        setSelection({...selection, city: JSON.stringify(city[0]) })
    };

    const getDepartmentsByCountry = async () => {
        const departs = await geolocationApi.getDepartmentsByCountry(selection.country);
        setDepartments([]);
        setDepartments(departs.length > 0 && departs);
    }

    const getCitiesByDepartment = async () => {
        const cities = await axios
            .get(`${API_URL}towns-by-department?department=${selection.department}`)
            .then(response => {
                return response.data;
            });
        setCities([]);
        setCities(cities.length > 0 && cities);
    }

    const getCityFromJson = async () => {
        setCities([]);
        setSelection({...selection, city: "", citySearch: ""});
        const searchingFile = CITY_FILE[selection.country];
        const filters = getCityFiltersFromCountry();
        const cities = searchingFile.filter(
            c => c[filters[0]] === selection.department
        );

        setCities(cities.length > 0 && cities);
    }

    const getFilteredCities = () => {
        setFilteredCities([]);
        const arrKey = getKeyForTown();
        const filtered = cities.filter(c =>
            c[arrKey].toLowerCase().includes(selection.citySearch.toLowerCase())
        );
        setFilteredCities(filtered);
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

    const getKeyForTown = () => {
        if (selection.country === 'fr') {
            return "nom";
        } else if (selection.country === 'be') {
            return "localite";
        } else if (selection.country === 'ch') {
            return "city";
        }  else if (selection.country === 'lu') {
            return "COMMUNE";
        } else {
            return "nom";
        }
    }

    const getCityFiltersFromCountry = () => {
        if (selection.country === 'fr') {
            return ["codeDepartement"];
        } else if (selection.country === 'be') {
            return ["province"];
        } else if (selection.country === 'lu') {
            return ["CANTON"];
        } else if (selection.country === 'ch') {
            return ["admin"];
        } else {
            return ["codeDepartement"];
        }
    }

    useEffect(() => {
        (async function updateDepartments() {
            await getDepartmentsByCountry();
        })();
    }, [selection.country]);

    return (
        <>
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
        </>
    )
}


const rootElement = document.querySelector("#geolocation");
ReactDOM.render(<Geolocation/>, rootElement);
