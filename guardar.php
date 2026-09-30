<?php

function mostrarError($mensaje, $codigo = 400)
{
    http_response_code($codigo);
    echo '<!DOCTYPE html><html lang="es"><meta charset="UTF-8"><title>Error</title>';
    echo '<body><p style="color:#b00020;background:#fde7e9;padding:12px;border-radius:6px;">';
    echo htmlspecialchars($mensaje, ENT_QUOTES, "UTF-8");
    echo '</p><a href="index.html">Volver e intentar de nuevo</a></body></html>';
    exit;
}

// Validar también en el servidor: el atributo "required" del HTML
// se puede omitir si alguien envía la petición por otro medio.
if (!isset($_FILES["archivo"]) || $_FILES["archivo"]["error"] === UPLOAD_ERR_NO_FILE) {
    mostrarError("Selecciona un archivo antes de guardarlo.");
}

if ($_FILES["archivo"]["error"] !== UPLOAD_ERR_OK) {
    mostrarError("No se pudo subir el archivo. Inténtalo de nuevo.");
}

// ----------------------------------------------------
// OBTENER EL ARCHIVO
// ----------------------------------------------------

// $_FILES contiene la información de los archivos
// enviados mediante un formulario.

// "archivo" es el nombre que colocamos en:
// <input type="file" name="archivo">

$archivo = $_FILES["archivo"];

// Extensiones y tipos MIME permitidos. La validación del HTML (accept)
// solo ayuda al usuario; esta comprobación en el servidor es la importante.
$tiposPermitidos = [
    "pdf"  => "application/pdf",
    "jpg"  => "image/jpeg",
    "jpeg" => "image/jpeg",
    "png"  => "image/png",
    "gif"  => "image/gif",
    "webp" => "image/webp",
];

$extension = strtolower(pathinfo($archivo["name"], PATHINFO_EXTENSION));
$finfo = new finfo(FILEINFO_MIME_TYPE);
$tipoMime = $finfo->file($archivo["tmp_name"]);

if (!isset($tiposPermitidos[$extension]) || $tiposPermitidos[$extension] !== $tipoMime) {
    mostrarError("Tipo de archivo no permitido. Usa PDF, JPG, PNG, GIF o WEBP.");
}


// ----------------------------------------------------
// OBTENER EL NOMBRE DEL ARCHIVO
// ----------------------------------------------------

// Obtenemos el nombre original del archivo.

$nombre = basename($archivo["name"]);


// ----------------------------------------------------
// OBTENER LA UBICACIÓN TEMPORAL
// ----------------------------------------------------

// Cuando PHP recibe un archivo,
// primero lo guarda temporalmente en el servidor.

// tmp_name contiene esa ubicación temporal.

$temporal = $archivo["tmp_name"];
$directorio = __DIR__ . DIRECTORY_SEPARATOR . "archivos";
$rutaDestino = $directorio . DIRECTORY_SEPARATOR . $nombre;

// Evitar sobrescribir un archivo que ya tenga el mismo nombre.
if (file_exists($rutaDestino)) {
    mostrarError("Ya existe un archivo con ese nombre en la carpeta.");
}

// También evitar guardar el mismo contenido con un nombre diferente.
$hashSubido = hash_file("sha256", $temporal);
foreach (scandir($directorio) as $elemento) {
    $rutaExistente = $directorio . DIRECTORY_SEPARATOR . $elemento;
    if (is_file($rutaExistente) && hash_file("sha256", $rutaExistente) === $hashSubido) {
        mostrarError("Este archivo ya existe en la carpeta, aunque tenga otro nombre.");
    }
}


// ----------------------------------------------------
// GUARDAR EL ARCHIVO
// ----------------------------------------------------

// move_uploaded_file() mueve el archivo desde
// la ubicación temporal hasta nuestra carpeta.

// "archivos/" es la carpeta donde queremos guardarlo.
//
// $nombre es el nombre original del archivo.

if (!move_uploaded_file(
    $temporal,
    $rutaDestino
)) {
    mostrarError("No se pudo guardar el archivo.", 500);
}

echo "Archivo subido correctamente: " . htmlspecialchars($nombre, ENT_QUOTES, "UTF-8");


// ----------------------------------------------------
// MOSTRAR UN MENSAJE
// ----------------------------------------------------



?>