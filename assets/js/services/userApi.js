import axios from "axios";
import {API_URL} from "../config";

async function getCurrentPatient(userId) {
    return await axios
        .get(
            `${API_URL}current/patient?id=${userId}`
        )
        .then(response => {
            return response.data;
        })
        .catch(error => {
            console.log(error);
        });
}

export default {
    getCurrentPatient
}