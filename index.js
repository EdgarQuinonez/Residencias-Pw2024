import { API_BASE_URL } from "./environment.js";

const logoutBtn = document.querySelector('#logoutBtn');
const addReporteBtn = document.querySelector('#addReporte');
const reporteForm = document.querySelector('#reporteForm');
const overlay = document.querySelector('.overlay');
const closeBtns = document.querySelectorAll('.closeFormBtn');
const addAuthorBtn = document.querySelector('#addAuthorBtn');
const authorList = document.querySelector('#authorList');

let authorCount = 1;

logoutBtn.addEventListener('click', () => logoutUser());
addReporteBtn.addEventListener('click', () => showReporteForm());
reporteForm.addEventListener('submit', (e) => onSubmit(e));
overlay.addEventListener('click', (e) => hideReporteForm(e));
closeBtns.forEach(el => el.addEventListener('click', () => hideReporteForm()));
addAuthorBtn.addEventListener('click', () => addAuthorFields());


const onInit = async () => {
    await getReportes();
}

const getReportes = async () => {
    const endpoint = API_BASE_URL + "/api/reporte.php";

    try {
        const response = await fetch(endpoint);
        if (!response.ok) {
            throw new Error(`Error al cargar los reportes: ${response.statusText}`);
        }

        const data = await response.json();        
        if (!data) {
            throw new Error("No se cargaron los reportes: " + data.message);
        }

        const reportes = data.data;
        const tableBody = document.querySelector("tbody");        
        tableBody.innerHTML = "";        
        
        reportes.forEach((reporte) => {
            const row = document.createElement("tr");
            row.classList.add("reporteRow");
            row.dataset.id = reporte.Id;

            let authorString = "";  
            reporte.Autores.forEach((a, i, arr) => {
                authorString += a["Nombre"];
                if (i != arr.length - 1) {
                    authorString += ", ";
                }
            });
                        
            row.innerHTML = `
                <td><p>${reporte.Title || "N/A"}</p></td>
                <td><p>${authorString || "N/A"}</p></td>
                <td><p>${reporte.AsesorInterno || "N/A"}</p></td>
                <td><p>${reporte.AsesorExterno || "N/A"}</p></td>
                <td><p>${reporte.FechaPublicacion || "N/A"}</p></td>
                <td><p>${reporte.CreatedAt || "N/A"}</p></td>
            `;
            
            tableBody.appendChild(row);
        });
        tableBody.addEventListener("click", (e) => onRowClick(e));
    } catch (e) {
        console.error("Error al cargar los reportes: ", e);
    }
};

const onRowClick = async (event) => {
    
    const row = event.target.closest("tr");

    if (!row || !row.dataset.id) return;

    const reporteID = row.dataset.id;
    const reporte = await getReporteById(reporteID);

    openReporteFormForEditing(reporte);
};


const getReporteById = async (reporteID) => {
    try {
        const response = await fetch(`${API_BASE_URL}/api/reporte.php?id=${reporteID}`);
        if (!response.ok) {
            throw new Error(`Error al cargar el reporte: ${response.statusText}`);
        }
        
        const reporte = await response.json();
        
        if (!reporte.data) {
            throw new Error("No se encontró el reporte.");
        }            

        return reporte.data;
    } catch (e) {
        console.error("Error al cargar el reporte: ", e);
    }
};
document.addEventListener("DOMContentLoaded", () => onInit());

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
    window.location.replace("./index.php");
}

const showReporteForm = () => {
    openReporteFormForAdding();
    if (reporteForm.classList.contains('hidden')) {
        reporteForm.classList.remove('hidden')
        overlay.classList.remove('hidden')
    }
}

const hideReporteForm = () => {    
    // const target = e.currentTarget;
    
    if (!reporteForm.classList.contains('hidden')) {
        reporteForm.classList.add('hidden')
        overlay.classList.add('hidden')
    }

    reporteForm.reset();
}

const onSubmit = async (e) => {
    try {
        e.preventDefault();
        const formData = new FormData(e.target);

        
        const authors = [];
        for (let i = 0; i < authorCount; i++) {
            const name = formData.get(`author[${i}][name]`).trim();
            const noControl = formData.get(`author[${i}][noC]`).trim();

            const authorObj = {
                name: name,
                noControl: noControl
            };

            authors.push(authorObj);
            formData.delete(`author[${i}][name]`);
            formData.delete(`author[${i}][noC]`);
        }
        formData.set("authors", JSON.stringify(authors));
        
        let method = 'POST';
        let endpoint = API_BASE_URL + "/api/reporte.php";
        
        if (e.target.dataset.isEditing === "true") {
            method = 'PUT';
            const reporteId = e.target.dataset.reporteId; // Retrieve the ID of the existing reporte
            endpoint += `?id=${reporteId}`; // Append ID for PUT request
        }

        
        const response = await fetch(endpoint, {
            method: method,
            body: formData
        });

        if (!response.ok) {
            throw new Error(`Error al hacer la ${method} request a reporte.php`);
        }
        const data = await response.json();

        if (!data.ok) {
            throw new Error(`Error al guardar el reporte: ${data.message}`);
        }
        
        hideReporteForm();
        e.target.reset();
        await getReportes();
    } catch (error) {
        console.error(error);
    }
};


const addAuthorFields = () => {
    const authorItem = document.createElement('li');
    authorItem.classList.add('authorItem');

    authorItem.innerHTML = `
        <label>
            <p>Nombre: </p>
            <input type="text" name="author[${authorCount}][name]" required>
        </label>
        <label>
            <p>No. de control: </p>
            <input type="text" name="author[${authorCount}][noC]" required>
        </label>
    `;

    authorList.appendChild(authorItem);
    authorCount++;
}

const populateReporteForm = (reporte) => {    
    reporteForm.classList.remove("hidden");
    overlay.classList.remove("hidden");

    reporteForm.querySelector("input[name='title']").value = reporte.Title || "";
    reporteForm.querySelector("input[name='publishDate']").value = reporte.FechaPublicacion || "";
    reporteForm.querySelector("input[name='asesorInterno']").value = reporte.AsesorInterno || "";
    reporteForm.querySelector("input[name='asesorExterno']").value = reporte.AsesorExterno || "";
    
    authorList.innerHTML = "";
    (reporte.Autores || []).forEach((author, index) => {
        const authorItem = document.createElement("li");
        authorItem.classList.add("authorItem");
        authorItem.innerHTML = `
            <label>
                <p>Nombre: </p>
                <input type="text" name="author[${index}][name]" value="${author.Nombre}" required>
            </label>
            <label>
                <p>No. de control: </p>
                <input type="text" name="author[${index}][noC]" value="${author.NoControl}" required>
            </label>
        `;
        authorList.appendChild(authorItem);
    });

    authorCount = reporte.Autores ? reporte.Autores.length : 0;
};


const openReporteFormForEditing = (reporte) => {
    reporteForm.dataset.isEditing = "true";
    reporteForm.dataset.reporteId = reporte.Id;
    populateReporteForm(reporte);
};

const openReporteFormForAdding = () => {
    reporteForm.dataset.isEditing = "false";
    reporteForm.removeAttribute("data-reporteId");
    populateReporteForm({});
};
