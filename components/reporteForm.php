<div class="overlay hidden closeFormBtn"></div>
<form id="reporteForm" class="hidden">
    <div>
        <button class="closeFormBtn xBtn" type="button">
            <p>X</p>
        </button>
    </div>
    <h1>Añadir un reporte</h1>
    <input type="file" name="file" id="file" accept=".pdf" size="<?php echo 50 * 1024 * 1024 ?>" required>
    <div>
        <label>
            <p>Título: </p>
            <input type="text" name="title" required>
        </label>
        <div>
            <h2>Autor(es)</h2>
            <ul id="authorList" class="authorList">
                <li class="authorItem">
                    <label>
                        <p>Nombre: </p>
                        <input type="text" name="author[0][name]" required>
                    </label>
                    <label>
                        <p>No. de control: </p>
                        <input type="text" name="author[0][noC]" required>
                    </label>
                </li>
            </ul>
            <button type="button" id="addAuthorBtn">Añadir Autor</button>
        </div>
        <label>
            <p>Fecha de publicación: </p>
            <input type="date" name="publishDate">            
        </label>
        <label>
            <p>Asesor Interno: </p>
            <input type="text" name="asesorInterno">
        </label>
        <label>
            <p>Asesor Externo: </p>
            <input type="text" name="asesorExterno">
        </label>    
    </div>
    <div class="bottomBtns">
        <button class="hidden" id="deleteBtn" type="button">
            <p>Borrar</p>
        </button>

        <div>  
            <button class="closeFormBtn closeBtn" type="button">
                <p>Cerrar</p>
            </button>
            <button class="saveFormBtn" id="addReporte"type="submit">
                <p>Añadir</p>
            </button>
        </div>
    </div>
</form>
