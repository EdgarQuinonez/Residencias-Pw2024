import { API_BASE_URL } from "../../environment.js";
const endpoint = API_BASE_URL  + '/api/auth/register.php';

const form = document.querySelector("#registerForm");

form.addEventListener('submit', e => onSubmit(e))

const onSubmit = async (e) => {
    e.preventDefault();
    try {
        const formData = new FormData(e.target);
        // Limpiar el user input
        for (const pair of formData.entries()) {     
            const [key, value] = pair;                  
            formData.set(key, value);
        }        
            
        const response = await fetch(endpoint, {
            method: 'POST',
            body: formData            
        });
            
        if (!response.ok) {        
            throw new Error("Hubo un error al registrar al usuario.")
        }                
        
        const data = await response.json();
        
        if (!data.ok) {
            throw new Error(`No se registró al usuario: ${data.message}`)
        }
        
        window.location.replace("../login/index.php");
    } catch (e) {
        console.error(e);
    }
}