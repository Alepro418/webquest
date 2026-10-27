# 🧪 Plan de Pruebas – WebQuest Ciencias Naturales

**Proyecto:** WebQuest – Plataforma Educativa para Ciencias Naturales  
**Versión del documento:** 2.0  
**Fecha de ejecución:** 25 de octubre de 2026  
**Responsable de pruebas:** Equipo de Desarrollo  
**Entorno:** Local (XAMPP / PHP 8.x / MySQL 8.x / Chrome 100%)  
**Modelo de datos:** Versión 2.0 – incluye Momentos Pedagógicos y Proyectos de Aula (PA)  
**Objetivo:** Validar el flujo completo del sistema (autenticación, configuración de Momentos y PA, creación de asignaturas y talleres, entrega de talleres, evaluación, reportes por momento y estatus motivacional) simulando un escenario académico real con 1 docente y 5 estudiantes durante un año escolar.

---

## 1. 👥 Credenciales de Prueba

### 1.1 Docente

| Campo | Valor |
|-------|-------|
| Nombre completo | Manuel Gónzales |
| Nombre de usuario | `Manuel` |
| Correo electrónico | manuelzales03@gmail.com |
| Contraseña | `'\EWAaF/otNEyn2` |
| Rol | Docente |

### 1.2 Estudiantes

| # | Nombre completo | Usuario | Contraseña |
|---|-----------------|---------|------------|
| 1 | Daniel Moreno | `Daniel` | `dqQ6(rLe!` |
| 2 | Barbara Araujo | `Rawr💜` | `T4s69)1QVt5` |
| 3 | Sandra Carrasquero | `Sandra` | `7meRVd4iX)` |
| 4 | Miguel Suarez | `Miguel` | `KA27KjEo)N` |
| 5 | Pedro Castillo | `Pedrito` | `6mV6WblU)Ye` |

> ⚠️ **Nota:** Estas credenciales son únicamente para el entorno de pruebas y no deben utilizarse en producción.

---

## 2. 🔐 Preguntas de Seguridad por Estudiante

> **Propósito:** Validar el flujo de recuperación de contraseña en `recover.php`.  
> **Recordatorio:** En la base de datos las respuestas se almacenan como **hash** (con `password_hash`), pero aquí se listan en texto plano para poder probar el sistema manualmente.

### 2.1 Daniel Moreno (`Daniel`)

| # | Pregunta | Respuesta |
|---|----------|-----------|
| 1 | ¿Cómo se llama tu mascota? | Firulais |
| 2 | ¿Cuál es tu comida favorita? | Pizza |
| 3 | ¿Cuál es tu juego favorito? | Minecraft |
| 4 | ¿Quién es tu superhéroe favorito? | Spider-Man |
| 5 | ¿Cuál es tu color favorito? | Azul |

### 2.2 Barbara Araujo (`Rawr💜`)

| # | Pregunta | Respuesta |
|---|----------|-----------|
| 1 | ¿Cómo se llama tu mascota? | Dylan |
| 2 | ¿Cuál es tu comida favorita? | Sushi |
| 3 | ¿Cuál es tu juego favorito? | Roblox |
| 4 | ¿Quién es tu superhéroe favorito? | Wonder Woman |
| 5 | ¿Cuál es tu libro favorito? | Harry Potter |

### 2.3 Sandra Carrasquero (`Sandra`)

| # | Pregunta | Respuesta |
|---|----------|-----------|
| 1 | ¿Cómo se llama tu mascota? | Michi |
| 2 | ¿Cuál es tu comida favorita? | Arepas |
| 3 | ¿Cuál es tu juego favorito? | Ajedrez |
| 4 | ¿Cuál es tu color favorito? | Rosado |
| 5 | ¿Cuál es tu libro favorito? | El Principito |

### 2.4 Miguel Suarez (`Miguel`)

| # | Pregunta | Respuesta |
|---|----------|-----------|
| 1 | ¿Cómo se llama tu mascota? | Rocky |
| 2 | ¿Cuál es tu comida favorita? | Hamburguesa |
| 3 | ¿Cuál es tu juego favorito? | Free Fire |
| 4 | ¿Quién es tu superhéroe favorito? | Batman |
| 5 | ¿Cuál es tu color favorito? | Verde |

### 2.5 Pedro Castillo (`Pedrito`)

| # | Pregunta | Respuesta |
|---|----------|-----------|
| 1 | ¿Cómo se llama tu mascota? | Toby |
| 2 | ¿Cuál es tu comida favorita? | Lasagna |
| 3 | ¿Cuál es tu juego favorito? | Minecraft |
| 4 | ¿Cuál es tu libro favorito? | El Quijote |
| 5 | ¿Cuál es tu color favorito? | Rojo |

---

## 3. 🗓️ Configuración de Momentos y Proyectos de Aula

> **Contexto:** El sistema educativo venezolano divide el año escolar en **tres momentos pedagógicos**. Cada momento tiene un **Proyecto de Aula (PA)** que agrupa las asignaturas, talleres y notas del periodo.  
> **Regla del sistema:** Sin un PA configurado y publicado para el momento activo, el docente **no puede crear asignaturas ni talleres**.

### 3.1 Tabla Resumen de Momentos

| Momento | Proyecto de Aula | Fecha inicio | Fecha fin | Estado | Observación |
|:-------:|------------------|:------------:|:---------:|--------|-------------|
| **1er momento** | Ecosistemas Terrestres | 16/09/2025 | 15/12/2025 | 🟢 Publicada | Configurado y con datos completos |
| **2do momento** | El Cuerpo Humano | 07/01/2026 | 31/03/2026 | 🟡 Borrador | En preparación por el docente |
| **3er momento** | *(sin configurar)* | — | — | ⚪ Sin PA | Sirve para probar la validación del sistema |

### 3.2 Detalle del PA – 1er Momento

| Campo | Valor |
|-------|-------|
| Título | Ecosistemas Terrestres |
| Descripción | Proyecto de Aula orientado al estudio de los ecosistemas terrestres, sus componentes bióticos y abióticos, y su importancia para la vida en el planeta. |
| Momento | 1 (1er momento) |
| Año escolar | 2025–2026 |
| Grado y sección | 5° "A" |
| Docente responsable | Manuel Gónzales |
| Fecha de inicio | 16/09/2025 |
| Fecha de fin | 15/12/2025 |
| Estado | 🟢 Publicada |
| Asignaturas asociadas | 4 (ver sección 4) |
| Talleres asociados | 4 (ver sección 5) |

### 3.3 Detalle del PA – 2do Momento

| Campo | Valor |
|-------|-------|
| Título | El Cuerpo Humano |
| Descripción | Proyecto de Aula orientado al estudio de los sistemas del cuerpo humano y su cuidado. |
| Momento | 2 (2do momento) |
| Año escolar | 2025–2026 |
| Grado y sección | 5° "A" |
| Docente responsable | Manuel Gónzales |
| Fecha de inicio | 07/01/2026 |
| Fecha de fin | 31/03/2026 |
| Estado | 🟡 Borrador |
| Asignaturas asociadas | *(aún no creadas)* |
| Talleres asociados | *(aún no creados)* |

### 3.4 Validaciones del Sistema

| # | Escenario | Resultado esperado |
|---|-----------|-------------------|
| V-01 | Docente intenta crear una asignatura sin PA configurado en el momento activo | ⛔ Muestra advertencia: "Para crear asignaturas, primero debe configurar un Proyecto de Aula para el momento pedagógico actual." y redirige a Configuración. |
| V-02 | Docente intenta crear un taller sin PA configurado | ⛔ Muestra advertencia: "No puede crear talleres sin un Proyecto de Aula configurado." |
| V-03 | Docente intenta crear un segundo PA activo en el mismo momento | ⛔ Rechaza la acción (restricción `UNIQUE`). |
| V-04 | Docente intenta configurar `fecha_fin` anterior a `fecha_inicio` | ⛔ Rechaza la acción con mensaje de validación. |
| V-05 | Docente intenta crear contenido en un PA con estado `borrador` | ⛔ Bloquea hasta que el PA pase a estado `publicada`. |
| V-06 | Docente intenta crear contenido en un PA con estado `cerrada` | ⛔ Bloquea (el momento ya terminó). |
| V-07 | Docente publica el PA del 1er momento | ✅ Se habilita la creación de asignaturas y talleres. |

---

## 4. 📚 Asignaturas del 1er Momento

> Todas las asignaturas listadas a continuación pertenecen al PA **"Ecosistemas Terrestres"** del **1er momento**.

| ID | Título | Descripción breve | Recursos | Talleres |
|----|--------|-------------------|----------|:--------:|
| ASG-01 | Componentes del Ecosistema | Estudio de los componentes bióticos y abióticos de un ecosistema terrestre. | 1 video, 2 enlaces, 1 PDF | 1 |
| ASG-02 | El Ciclo del Agua | Análisis de las fases del ciclo hidrológico y su importancia para la vida. | 1 video, 1 enlace | 1 |
| ASG-03 | Cadenas Alimenticias | Identificación de productores, consumidores y descomponedores en un ecosistema. | 1 video, 1 PDF | 1 |
| ASG-04 | La Fotosíntesis | Proceso mediante el cual las plantas transforman la energía solar en energía química. | 2 videos, 1 enlace | 1 |

---

## 5. 📝 Talleres del 1er Momento

### 5.1 ASG-01 · Componentes del Ecosistema — *Taller 1*

- **Descripción:** Elaborar en la libreta un mapa conceptual con los componentes bióticos y abióticos de un ecosistema terrestre (bosque, desierto o selva).
- **Aspectos a cumplir:**
  1. Identificar al menos 3 factores bióticos.
  2. Identificar al menos 3 factores abióticos.
  3. Incluir un ejemplo de un ecosistema específico.
- **Pautas:**
  - Escritura manuscrita.
  - Usar colores para diferenciar componentes.
  - Extensión mínima: 1 página.
- **Fecha límite:** 30/09/2025
- **Recursos:**
  - 📹 Video: "Ecosistemas terrestres explicados"
  - 🔗 Enlace: "Factores bióticos y abióticos – National Geographic"
  - 📄 PDF: "Guía de ecosistemas de Venezuela"

### 5.2 ASG-02 · El Ciclo del Agua — *Taller 2*

- **Descripción:** Realizar un dibujo del ciclo del agua etiquetando cada fase (evaporación, condensación, precipitación, infiltración).
- **Aspectos a cumplir:**
  1. Etiquetar las 4 fases principales.
  2. Explicar brevemente cada fase (1-2 líneas).
  3. Indicar la importancia del ciclo para los seres vivos.
- **Pautas:**
  - Dibujo a mano.
  - Uso de flechas para indicar el flujo.
  - Extensión mínima: media página.
- **Fecha límite:** 31/10/2025
- **Recursos:**
  - 📹 Video: "El ciclo del agua en 3 minutos"
  - 🔗 Enlace: "USGS – Water Cycle"

### 5.3 ASG-03 · Cadenas Alimenticias — *Taller 3*

- **Descripción:** Construir 2 cadenas alimenticias (una terrestre y una acuática) e indicar el rol de cada organismo.
- **Aspectos a cumplir:**
  1. Cada cadena debe tener al menos 4 eslabones.
  2. Identificar productores, consumidores y descomponedores.
  3. Señalar qué ocurre si se elimina un eslabón.
- **Pautas:**
  - Escritura manuscrita.
  - Usar flechas direccionales.
  - Extensión mínima: 1 página.
- **Fecha límite:** 15/11/2025
- **Recursos:**
  - 📹 Video: "Cadenas y redes tróficas"
  - 📄 PDF: "Ejercicios de cadenas alimenticias"

### 5.4 ASG-04 · La Fotosíntesis — *Taller 4*

- **Descripción:** Explicar el proceso de la fotosíntesis mediante un esquema y una breve redacción.
- **Aspectos a cumplir:**
  1. Incluir fórmula química de la fotosíntesis.
  2. Mencionar los factores necesarios (luz, agua, CO₂, clorofila).
  3. Explicar el producto final (glucosa y oxígeno).
- **Pautas:**
  - Escritura manuscrita.
  - Esquema con colores.
  - Extensión mínima: 1 página.
- **Fecha límite:** 10/12/2025
- **Recursos:**
  - 📹 Video: "Fotosíntesis paso a paso"
  - 📹 Video: "Experimento de fotosíntesis"
  - 🔗 Enlace: "Khan Academy – Fotosíntesis"

---

## 6. 📤 Matriz de Entregas y Calificaciones – 1er Momento

> **Escala de evaluación:**
> - **A** (18–20): Sobresaliente
> - **B** (15–17): Distinguido
> - **C** (12–14): Aprobado
> - **D** (0–11): Por mejorar
>
> **Estatus motivacional:**
> - 🟢 Sobresaliente
> - 🔵 Buen estudiante
> - 🟠 Vamos a mejorar juntos
> - ⚪ Sin entrega
>
> **Cálculo del promedio:**  
> El promedio se calcula **sobre las entregas realizadas**. Si un estudiante no entrega un taller, ese taller **no cuenta** en el promedio, pero **sí afecta el estatus motivacional** (se refleja como falta de entrega).  
> Por ejemplo: si un estudiante entregó 3 de 4 talleres con notas 10, 8 y 11, su promedio es `(10 + 8 + 11) / 3 = 9.67`, y su estatus se ve afectado por la entrega faltante.

### 6.1 Resumen del 1er Momento – PA "Ecosistemas Terrestres"

| Pos. | Estudiante | Taller 1 | Taller 2 | Taller 3 | Taller 4 | Promedio |
|:----:|------------|:--------:|:--------:|:--------:|:--------:|:--------:|
| 🥇 1 | Barbara Araujo | 20 (A) | 19 (A) | 19 (A) | 20 (A) | **19.50** |
| 🥈 2 | Daniel Moreno | 19 (A) | 18 (A) | 19 (A) | 19 (A) | **18.75** |
| 🥉 3 | Pedro Castillo | 17 (B) | 16 (B) | 17 (B) | 18 (A) | **17.00** |
| 4 | Sandra Carrasquero | 14 (C) | 12 (C) | 15 (B) | 13 (C) | **13.50** |
| 5 | Miguel Suarez | 10 (D) | 8 (D) | 11 (D) | — | **9.67** |

### 6.2 Reporte Grupal del 1er Momento

```
================================================================================
     REPORTE DE NOTAS - 1er MOMENTO
================================================================================
 Proyecto de Aula: Ecosistemas Terrestres
 Periodo:          16/09/2025 - 15/12/2025
 Docente:          Manuel Gonzales
 Grado y Seccion:  5to "A"
 Anio Escolar:     2025-2026
================================================================================

  #  Estudiante            Asignaturas  Talleres    Notas   Promedio
 --- --------------------- ------------ ----------- ------- ----------
  1  Barbara Araujo               4       4 / 4       100%     19.50
  2  Daniel Moreno                4       4 / 4       100%     18.75
  3  Pedro Castillo               4       4 / 4       100%     17.00
  4  Sandra Carrasquero           4       4 / 4       100%     13.50
  5  Miguel Suarez                4       3 / 4        75%      9.67
================================================================================
 TOTAL                          20      19 / 20       95%     15.68
================================================================================
```

### 6.3 Detalle de Evaluaciones

#### 🟢 Barbara Araujo — *Sobresaliente*

| Taller | Puntaje | Escala | Estatus | Observaciones |
|--------|:-------:|:------:|---------|---------------|
| Taller 1 | 20 | A | Sobresaliente | Mapa conceptual impecable, con colores y excelente ortografía. |
| Taller 2 | 19 | A | Sobresaliente | Dibujo detallado, etiquetas claras y explicación completa de cada fase. |
| Taller 3 | 19 | A | Sobresaliente | Ambas cadenas correctas con 4 eslabones y análisis del impacto. |
| Taller 4 | 20 | A | Sobresaliente | Esquema impecable, fórmula química correcta y excelente redacción. |

#### 🟢 Daniel Moreno — *Sobresaliente*

| Taller | Puntaje | Escala | Estatus | Observaciones |
|--------|:-------:|:------:|---------|---------------|
| Taller 1 | 19 | A | Sobresaliente | Mapa conceptual completo, con colores y buena ortografía. |
| Taller 2 | 18 | A | Sobresaliente | Dibujo detallado, etiquetas claras y buena redacción. |
| Taller 3 | 19 | A | Sobresaliente | Ambas cadenas correctas, análisis profundo del impacto. |
| Taller 4 | 19 | A | Sobresaliente | Esquema muy completo, fórmula química correcta y explicación clara. |

#### 🔵 Pedro Castillo — *Buen estudiante*

| Taller | Puntaje | Escala | Estatus | Observaciones |
|--------|:-------:|:------:|---------|---------------|
| Taller 1 | 17 | B | Buen estudiante | Buen mapa, aunque faltó un factor abiótico y uso de colores. |
| Taller 2 | 16 | B | Buen estudiante | Dibujo correcto, pero faltó explicar la infiltración con detalle. |
| Taller 3 | 17 | B | Buen estudiante | Cadenas completas y bien explicadas, sin análisis del impacto. |
| Taller 4 | 18 | A | Sobresaliente | Esquema muy completo con fórmula química y buena redacción. |

#### 🟠 Sandra Carrasquero — *Aprobado*

| Taller | Puntaje | Escala | Estatus | Observaciones |
|--------|:-------:|:------:|---------|---------------|
| Taller 1 | 14 | C | Buen estudiante | Faltaron colores y solo incluyó 2 factores bióticos. |
| Taller 2 | 12 | C | Buen estudiante | Dibujo básico, faltaron etiquetas en 2 fases. |
| Taller 3 | 15 | B | Buen estudiante | Cadenas correctas, pero sin análisis del impacto. |
| Taller 4 | 13 | C | Buen estudiante | Esquema incompleto, faltó la fórmula química. |

#### ⚪ Miguel Suarez — *Vamos a mejorar juntos*

| Taller | Puntaje | Escala | Estatus | Observaciones |
|--------|:-------:|:------:|---------|---------------|
| Taller 1 | 10 | D | Vamos a mejorar juntos | Mapa incompleto, sin colores ni ejemplos. |
| Taller 2 | 8 | D | Vamos a mejorar juntos | Dibujo muy básico y sin explicaciones. |
| Taller 3 | 11 | D | Vamos a mejorar juntos | Solo una cadena y sin roles definidos. |
| Taller 4 | — | — | Sin entrega | No se recibió evidencia. |

---

## 7. 📊 Distribución de Resultados – 1er Momento

### 7.1 Por Estatus Motivacional

| Estatus | Cantidad de estudiantes | Porcentaje |
|---------|:-----------------------:|:----------:|
| 🟢 Sobresaliente | 2 | 40% |
| 🔵 Buen estudiante | 1 | 20% |
| 🟠 Vamos a mejorar juntos | 1 | 20% |
| ⚪ Sin entregas suficientes | 1 | 20% |

### 7.2 Porcentaje de Entregas Realizadas

| Estudiante | Entregas | Total | % |
|------------|:--------:|:-----:|:-:|
| Barbara Araujo | 4 | 4 | 100% |
| Daniel Moreno | 4 | 4 | 100% |
| Sandra Carrasquero | 4 | 4 | 100% |
| Pedro Castillo | 4 | 4 | 100% |
| Miguel Suarez | 3 | 4 | 75% |
| **Total** | **19** | **20** | **95%** |

---

## 8. 📈 Estado de los Otros Momentos

### 8.1 2do Momento – PA "El Cuerpo Humano"

| Elemento | Estado |
|----------|--------|
| PA configurado | 🟡 Borrador |
| Fechas configuradas | ✅ 07/01/2026 – 31/03/2026 |
| Asignaturas creadas | ❌ Pendiente (el PA no está publicado) |
| Talleres creados | ❌ Pendiente |
| Entregas recibidas | ❌ Pendiente |
| Reportes disponibles | ❌ Pendiente |

### 8.2 3er Momento – Sin PA configurado

| Elemento | Estado |
|----------|--------|
| PA configurado | ⚪ No existe |
| Fechas configuradas | ⚪ No existen |
| Asignaturas creadas | ❌ Bloqueado por el sistema (V-01) |
| Talleres creados | ❌ Bloqueado por el sistema (V-02) |
| Entregas recibidas | ❌ Bloqueado |
| Reportes disponibles | ❌ No aplica |

---

## 9. ✅ Criterios de Aceptación de las Pruebas

### 9.1 Autenticación y Seguridad

| # | Criterio | Estado |
|---|----------|:------:|
| 1 | Registro de docente con email | ✅ |
| 2 | Registro de estudiante con 5 preguntas de seguridad | ✅ |
| 3 | Login diferenciado por rol | ✅ |
| 4 | Recuperación de contraseña mediante 5 preguntas de seguridad | ✅ |

### 9.2 Configuración de Momentos y Proyectos de Aula

| # | Criterio | Estado |
|---|----------|:------:|
| 5 | Creación de Proyecto de Aula por momento | ✅ |
| 6 | Configuración de fechas de inicio y fin del momento | ✅ |
| 7 | Validación de fechas (fin > inicio) | ✅ |
| 8 | Publicación de PA para habilitar creación de contenido | ✅ |
| 9 | Restricción: no se puede crear asignatura sin PA (V-01) | ✅ |
| 10 | Restricción: no se puede crear taller sin PA (V-02) | ✅ |
| 11 | Restricción: un solo PA activo por momento (V-03) | ✅ |

### 9.3 Contenido Académico

| # | Criterio | Estado |
|---|----------|:------:|
| 12 | Creación de asignaturas dentro de un PA publicado | ✅ |
| 13 | Creación de talleres dentro de una asignatura | ✅ |
| 14 | Asociación de recursos a talleres | ✅ |

### 9.4 Entregas y Evaluaciones

| # | Criterio | Estado |
|---|----------|:------:|
| 15 | Entrega de talleres por parte del estudiante (foto de libreta) | ✅ |
| 16 | Evaluación cuantitativa (0–20) y cualitativa (A/B/C/D) | ✅ |
| 17 | Asignación automática de estatus motivacional | ✅ |

### 9.5 Reportes

| # | Criterio | Estado |
|---|----------|:------:|
| 18 | Reporte grupal incluye momento y PA | ✅ |
| 19 | Reporte individual incluye momento y PA | ✅ |
| 20 | Reporte filtrable por momento | ✅ |
| 21 | Reporte incluye fechas del momento | ✅ |
| 22 | Visualización del panel docente con cohorte y analítica | ✅ |
| 23 | Visualización del estatus del estudiante | ✅ |

---

## 10. 📌 Observaciones Finales

- El flujo **registro → login → configuración del PA → creación de contenido → entrega → evaluación → reportes** se completó sin errores críticos.
- El escenario simula un **año escolar en curso**: el 1er momento está cerrado con datos completos, el 2do momento está en preparación (borrador) y el 3er momento aún no ha sido configurado.
- Las **validaciones del sistema** (V-01 a V-07) demostraron que el docente **no puede crear contenido sin un PA publicado**, lo cual garantiza que toda nota quede trazada a un momento y a un PA específicos.
- Los **reportes por momento** ahora muestran claramente a qué PA y a qué periodo de fechas corresponden las notas, eliminando la ambigüedad del sistema anterior.
- El escenario simula **variabilidad real de desempeño**: dos alumnos destacados (Barbara en 1.er lugar con 19.50 y Daniel en 2.º con 18.75), un alumno constante en nivel medio-alto (Pedro), una alumna en nivel medio (Sandra) y un alumno con bajo rendimiento y entrega parcial (Miguel).
- Las **5 preguntas de seguridad** por estudiante permiten probar el flujo de recuperación de contraseña. Se recomienda probar cada combinación al menos una vez.
- Se recomienda añadir **pruebas automatizadas** para los cálculos de promedio y estatus motivacional.
- Próxima fase: **pruebas de carga** con múltiples docentes y asignaturas simultáneas, y pruebas del flujo completo de los 3 momentos.

---

> 🌱 *Documento generado para la fase de pruebas de la WebQuest – Ciencias Naturales (versión 2.0 con Momentos y Proyectos de Aula).*