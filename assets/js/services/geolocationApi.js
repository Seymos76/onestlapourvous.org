import axios from "axios";
import {API_URL} from "../config";

async function getDepartmentsByCountry(country) {
    return await axios
        .get(`${API_URL}departments-by-country?country=${country}`)
        .then(response => {
            return response.data;
        });
}

export default {
    getDepartmentsByCountry
}