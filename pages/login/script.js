import { API_BASE_URL } from "../../environment.js";
const endpoint = API_BASE_URL  + '/api/auth/login.php';

const form = document.querySelector("#loginForm");
form.addEventListener('submit', e => onSubmit(e))

const onSubmit = async (e) => {
    e.preventDefault();
    try {

        const formData = new FormData(e.target);
        
        // Limpiar el user input
        for (const pair of formData.entries()) {     
            const [key, value] = pair;                  
            formData.set(key, value.trim());            
        }        
            
        const response = await fetch(endpoint, {
            method: 'POST',
            body: formData            
        });
        
    
        if (!response.ok) {        
            throw new Error("Hubo un error al iniciar sesión.")
        }
                        
        const data = await response.json();
        
        if (!data.ok) {
            throw new Error(`No se inició sesión: ${data.message}`)
        }
        
        window.location.replace("../../index.php");
    } catch (e) {
        console.error(e);
    }
}