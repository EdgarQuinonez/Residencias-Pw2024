import { API_BASE_URL } from "./environment.js";

const logoutBtn = document.querySelector('#logoutBtn');
const addReporteBtn = document.querySelector('#addReporte');
const reporteForm = document.querySelector('#reporteForm');
const overlay = document.querySelector('.overlay');
const closeBtns = document.querySelectorAll('.closeFormBtn');
const addAuthorBtn = document.querySelector('#addAuthorBtn');
const authorList = document.querySelector('#authorList');
const deleteBtn = document.getElementById("deleteBtn");

let authorCount = 1;

logoutBtn.addEventListener('click', () => logoutUser());
addReporteBtn.addEventListener('click', () => showReporteForm());
reporteForm.addEventListener('submit', (e) => onSubmit(e));
overlay.addEventListener('click', (e) => hideReporteForm(e));
closeBtns.forEach(el => el.addEventListener('click', () => hideReporteForm()));
addAuthorBtn.addEventListener('click', () => addAuthorFields());
if (deleteBtn) {
    deleteBtn.addEventListener("click", () => onDeleteReporteClick());
}

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

const onDeleteReporteClick = async (e) => {        
       await deleteReporteById();    
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

            formData.delete(`author[${i}][name]`);
            formData.delete(`author[${i}][noC]`);

            authors.push({ name, noControl });
        }

        const title = formData.get("title").trim();
        const publishDate = formData.get("publishDate").trim();
        const asesorInterno = formData.get("asesorInterno").trim();
        const asesorExterno = formData.get("asesorExterno").trim();

        let method = "POST";
        let endpoint = API_BASE_URL + "/api/reporte.php";
        let body;
        
        if (e.target.dataset.isEditing === "true") {
            method = "PUT";
            const reporteId = e.target.dataset.reporteId;
            
            body = JSON.stringify({
                reporteID: reporteId,
                title,
                publishDate,
                asesorInterno,
                asesorExterno,
                authors,
            });
        } else {            
            formData.set('authors', JSON.stringify(authors))

            body = formData;
        }

        console.log(body);
        
        const fetchOptions = {
            method: method,
            headers: method === "PUT" ? { "Content-Type": "application/json" } : undefined,
            body: body,
        };
        
        const response = await fetch(endpoint, fetchOptions);

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

const populateReporteForm = (reporte, isEditing = false) => {
    reporteForm.classList.remove("hidden");
    overlay.classList.remove("hidden");
    
    if (isEditing) {
        
        const deleteBtn = document.getElementById("deleteBtn");
        const addBtn = document.getElementById("addReporte");
        
        deleteBtn.classList.remove("hidden");
        addBtn.querySelector("p").textContent = "Actualizar";
        
        
        const fileInput = reporteForm.querySelector("input[name='file']");
        if (fileInput) {
            fileInput.classList.add("hidden");
            fileInput.required = false;
        }
    } else {        
        const addBtn = document.getElementById("addReporte");
        addBtn.querySelector("p").textContent = "Nuevo";
        const deleteBtn = document.getElementById("deleteBtn");
        deleteBtn.classList.add("hidden");
                
        const fileInput = reporteForm.querySelector("input[name='file']");
        if (fileInput) {
            fileInput.classList.remove("hidden");
            fileInput.required = true;
        }
    }
    
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
    populateReporteForm(reporte, true);
};

const openReporteFormForAdding = () => {
    reporteForm.dataset.isEditing = "false";
    reporteForm.removeAttribute("data-reporteId");
    populateReporteForm({});
};

const deleteReporteById = async () => {
    const reporteID = reporteForm.dataset.reporteId; // Get reporteID from the form's dataset
    const reporte = await getReporteById(reporteID);
    if (!reporteID) {
        console.error('Reporte ID is missing');
        return;
    }    
    const endpoint = `${API_BASE_URL}/api/reporte.php`;

    try {
        const response = await fetch(endpoint, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                filename: reporte.URI,
                id: reporteID
            }),
        });

        if (!response.ok) {
            throw new Error(`Error al eliminar el reporte con ID ${reporteID}`);
        }

        const data = await response.json();
        if (!data.ok) {
            throw new Error(`Error al eliminar el reporte: ${data.message}`);
        }
        
        hideReporteForm();
        await getReportes();
    } catch (error) {
        console.error(error);
    }
};
