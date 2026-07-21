<?php 
// Establece la zona horaria (importante para que coincida con venezuela)
date_default_timezone_set('America/Caracas');

// Configuramos el idioma a español para los nombres de los meses
setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'spanish');

// Arreglo de meses
$meses = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");

// Obtener el indice del mes actual
$iniceMes = date('n') - 1;

// Imprimir formato: "4 de Marzo de 2026" o "4, Marzo de 2026"
echo date('j') . ", " . $meses[$iniceMes] . " de " . date('Y');