import { API_BASE_URL } from "../../environment.js";

const logoutBtn = document.querySelector('#logoutBtn');


logoutBtn.addEventListener('click', () => logoutUser());
document.addEventListener("DOMContentLoaded", () => onInit());

let currentUser = "";
const onInit = async () => {
    await renderUsers();
    const user = await getUser();
    currentUser = user['Username'];    
}

const getUser = async () => {
    try {

        const endpoint = API_BASE_URL + "/api/session.php";
        const response = await fetch(endpoint);
        if (!response.ok) {
            throw new Error("Error al recibir el rol del usuario.")
        }
        const data = await response.json();

        return data['ok'];
    } catch (e) {
        console.error(e);
    }

}

const logoutUser = async () => {
    const endpoint = API_BASE_URL  + '/api/auth/logout.php';
    const response = await fetch(endpoint);

    if (!response.ok) {
        throw new Error("Error al cerrar sesión.");
    }
    
    const data = await response.json();

    if (!data.ok) {
        throw new Error(`Error al cerrar sesión: ${data.message}`)
    }
    window.location.replace("../../index.php");
}

const getUsers = async () => {

    try {

        const endpoint = `${API_BASE_URL}/api/usuario.php`;
        const response = await fetch(endpoint);
    
        if (!response.ok) {
            throw new Error("Error al completar GET para usuario.php")
        }

        const results = await response.json();
        const data = results.data;

        if (!data) {
            throw new Error("No se cargó ningún usuario.");            
        }

        return data;
    } catch (e) {
        console.error(e);
    }
}

const renderUsers = async () => {
    const users = await getUsers();    
    const tableBody = document.querySelector(".table-body");
    tableBody.innerHTML = "";

    users.forEach((user) => {
        const row = document.createElement("tr");
        row.classList.add("user-row");
        row.dataset.id = user.Username;
        
        row.innerHTML = `
            <td class="cell"><p>${user.Username || "N/A"}</p></td>            
            <td class="cell"><p>${user.Role || "N/A"}</p></td>                        
            <td class="cell"><p>${user.CreatedAt || "N/A"}</p></td>            
            <td class="cell controles-cell"></td>            
        `;

        const controlesCell = row.querySelector(".controles-cell");

        const passwordBtn = document.createElement("button");
        passwordBtn.textContent = "Cambiar contraseña";
        passwordBtn.classList.add("control-btn", "password-btn");
        passwordBtn.addEventListener("click", (e) => onChangePassword(e));

        const deleteBtn = document.createElement("button");
        deleteBtn.textContent = "Borrar";
        deleteBtn.classList.add("control-btn", "delete-btn");        
        deleteBtn.addEventListener("click", (e) => onDelete(e));

        controlesCell.appendChild(passwordBtn);        
        controlesCell.appendChild(deleteBtn);
        
        
        tableBody.appendChild(row);
    });
}

const onDelete = async (e) => {        
    await deleteUser(e);    
};

const deleteUser = async (e) => {
    const row = e.target.closest("tr");    
    if (!row || !row.dataset.id) return;
    const uid = row.dataset.id;

    const endpoint = `${API_BASE_URL}/api/usuario.php`;
    const response = await fetch(endpoint, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            'user': uid
        })
    });

    if (!response.ok) {
        console.error("Error en la solicitud DELETE de usuario.php")        
    }
    
    await renderUsers();
}

const updateUserPassword = async () => {
    const endpoint = `${API_BASE_URL}/`
    const response = await fetch(endpoint, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json'
        },
        body: body
    });
}