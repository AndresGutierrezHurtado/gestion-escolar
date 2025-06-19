# 🏫 MATRYERSE – Plataforma SaaS de Gestión Escolar Integral

[English Version](./README.md)

**MATRYERSE** es una innovadora plataforma web basada en el modelo **Software as a Service (SaaS)**, diseñada para optimizar la gestión académica, administrativa y financiera de instituciones educativas privadas en Bogotá y otras ciudades. Su enfoque multi-institucional permite que varios colegios o academias utilicen el mismo sistema de manera independiente, garantizando total privacidad y aislamiento de los datos de cada institución gracias a una arquitectura de multi-tenencia lógica.

La plataforma busca centralizar procesos esenciales como:

-   Matrículas de estudiantes por periodo académico.
-   Asignación de docentes a grupos y materias.
-   Gestión de calificaciones y control de asistencia.
-   Administración de pagos y conceptos financieros.
-   Generación de reportes y trazabilidad de acciones.

**MATRYERSE** es ideal para instituciones que deseen modernizar su operación diaria, reducir procesos manuales, mejorar la trazabilidad de datos y ofrecer una experiencia digital sólida a sus administrativos, docentes, estudiantes y tutores.

![Laravel](https://img.shields.io/badge/Laravel-12-red)
![MySQL](https://img.shields.io/badge/MySQL-10.4-blue)
![TailwindCSS](https://img.shields.io/badge/TailwindCSS-v4-blue)
![License](https://img.shields.io/badge/license-MIT-green)

![Imagen del index](./docs/images/index.png)

---

## 📑 Tabla de Contenidos

1. [Características Principales](#-características-principales)
2. [Tecnologías Utilizadas](#️-tecnologías-utilizadas)
3. [Arquitectura](#️-arquitectura)
4. [Flujo de Uso](#-flujos-de-uso)
5. [Estructura de Carpetas](#-estructura-de-carpetas)
6. [Instalación](#️-instalación)
7. [Contacto](#-contacto)

---

## 🚀 Características Principales

### 📌 Gestión de Instituciones

Permite a los administradores gestionar múltiples instituciones educativas de manera independiente gracias a la arquitectura multi-tenant, garantizando privacidad de datos entre ellas.

-   Multi-tenencia lógica.
-   Creación, edición y eliminación de instituciones.
-   Aislamiento total de datos entre instituciones.

![Gestión de Instituciones](./docs/images/institutions.png)

### 🔐 Autenticación y Control de Acceso

Sistema de autenticación robusto que permite el acceso seguro de usuarios según su rol (Guest, Administrador, Administrativo, Docente, Estudiante, Tutor) con permisos y vistas personalizadas.

-   Inicio de sesión con validación segura.
-   Recuperación de contraseña.
-   Control de accesos y permisos por rol.

![Autenticación](./docs/images/authentication.png)

### 👥 Gestión de Usuarios

Funcionalidad para crear, editar, eliminar y asignar roles a los usuarios de la plataforma, permitiendo una administración eficiente de administrativos, docentes, estudiantes y tutores.

-   Registro y edición de usuarios.
-   Asignación de roles y permisos.
-   Gestión de estado activo/inactivo de cuentas.

![Gestión de Usuarios](./docs/images/users.png)

### 📝 Gestión de Matrículas

Permite a los administrativos registrar y gestionar las matrículas de los estudiantes por periodo académico. Incluye formulario de inscripción y envío de confirmación vía correo electrónico.

-   Formulario de inscripción para aspirantes.
-   Validación y aprobación de matrículas.
-   Notificación por correo electrónico.

![Gestión de Matrículas](./docs/images/enrollments.png)

### 📧 Solicitud de Información

Los visitantes (Guest) pueden llenar un formulario de contacto o inscripción. La información es enviada automáticamente a los administradores de la institución seleccionada.

-   Formulario de contacto personalizable.
-   Envío automático de datos a los administrativos.
-   Registro de solicitudes para seguimiento posterior.

![Solicitud de Información](./docs/images/forms.png)

### 📊 Estadísticas Generales e Institucionales

Visualización de datos relevantes como número de estudiantes matriculados, asistencia, pagos realizados, entre otros indicadores para una toma de decisiones informada.

-   Panel con indicadores clave.
-   Filtros por institución y periodo académico.
-   Exportación de reportes.

![Estadísticas](./docs/images/statistics.png)

### 🏫 Gestión de Estructura Escolar

Módulo para definir y gestionar la estructura académica de la institución: periodos, cursos, materias, asignación de docentes, horarios de clases y carga académica.

-   Creación de cursos, materias y periodos.
-   Asignación de docentes a grupos y materias.
-   Configuración de horarios de clases.

![Estructura Escolar](./docs/images/academicStructure.png)

### 💳 Registro y Gestión de Pagos

Permite registrar pagos de matrículas, pensiones y otros conceptos financieros. Incluye trazabilidad de pagos por estudiante y generación de reportes.

-   Registro manual o automático de pagos.
-   Historial de pagos por estudiante.
-   Generación de facturas y comprobantes.

![Gestión de Pagos](./docs/images/payments.png)

### 🗒️ Gestión de Observaciones y Asistencias

Los docentes pueden registrar ausencias y observaciones por estudiante, las cuales son visibles para los tutores y administrativos, garantizando un seguimiento integral del alumno.

-   Registro de ausencias diarias.
-   Observaciones académicas o disciplinarias.
-   Visualización de reportes por tutores.

![Observaciones y Asistencias](./docs/images/observations.png)

### 🕒 Vista de Horarios

Docentes, estudiantes y tutores pueden consultar de manera sencilla el horario de clases de los diferentes grupos, permitiendo una mejor organización de las actividades diarias.

-   Visualización de horario personalizado por rol.
-   Horarios semanales de cursos y materias.
-   Detalle de asignaturas, salones y docentes.

![Vista de Horarios](./docs/images/schedules.png)

---

## 🛠️ Tecnologías Utilizadas

### **Frontend**

-   **Laravel Blade** - Motor de plantillas de Laravel
-   **Vite** - Bundler y servidor de desarrollo
-   **Tailwind CSS v4** - Framework CSS utilitario
-   **DaisyUI** - Componentes UI para Tailwind
-   **Axios** - Cliente HTTP para peticiones API
-   **SweetAlert2** - Librería para alertas y notificaciones

### **Backend**

-   **Laravel 10** - Framework PHP
-   **Laravel API** - Endpoints RESTful
-   **Eloquent ORM** - ORM de Laravel
-   **MySQL** - Base de datos relacional
-   **Migrations** - Control de versiones de base de datos
-   **Seeders** - Población de datos iniciales
-   **Factories** - Generación de datos de prueba

---

## 🏗️ Arquitectura

### **Modelo Cliente-Servidor**

La aplicación sigue una arquitectura cliente-servidor moderna donde:

-   **Cliente (Frontend)**

    -   Interfaz de usuario construida con Laravel Blade
    -   Consumo de APIs RESTful mediante Axios
    -   Gestión de estado y UI con Tailwind y DaisyUI
    -   Validaciones del lado del cliente

-   **Servidor (Backend)**
    -   API RESTful construida con Laravel
    -   Endpoints para operaciones CRUD
    -   Autenticación y autorización
    -   Procesamiento de datos y lógica de negocio

### **Patrón MVC**

Para ciertos módulos como la autenticación y gestión de usuarios, se implementa el patrón MVC:

-   **Modelo (M)**

    -   Eloquent ORM para interacción con la base de datos
    -   Lógica de negocio y validaciones
    -   Relaciones entre entidades

-   **Vista (V)**

    -   Plantillas Blade para renderizado
    -   Componentes reutilizables
    -   Integración con Tailwind y DaisyUI

-   **Controlador (C)**
    -   Gestión de peticiones HTTP
    -   Coordinación entre modelos y vistas
    -   Lógica de aplicación

Esta arquitectura híbrida permite:

-   Separación clara de responsabilidades
-   Escalabilidad y mantenibilidad
-   Reutilización de código
-   Seguridad robusta
-   Experiencia de usuario fluida

---

## 👥 Flujos de Uso

### 👑 Administrador

El usuario con rol de **Administrador** tiene acceso completo a la plataforma y puede realizar las siguientes acciones:

-   Gestionar las instituciones académicas (crear, editar, eliminar).
-   Visualizar estadísticas generales de uso, rendimiento académico y estado financiero de cada institución.
-   Crear, modificar o eliminar usuarios de tipo administrativo, docente, estudiante y tutor.
-   Asignar permisos específicos a los usuarios administrativos.
-   Configurar parámetros generales de la plataforma.

---

### 🏢 Administrativo

El usuario con rol de **Administrativo** tiene acceso a funciones específicas de gestión interna de su institución, dependiendo de los permisos otorgados por el Administrador:

-   Gestionar periodos académicos (crear, modificar, eliminar).
-   Gestionar materias y asignaturas de la institución.
-   Crear y organizar grupos de estudiantes.
-   Asignar materias y docentes a los grupos.
-   Gestionar la matrícula de estudiantes (aprobación, rechazo de solicitudes).
-   Administrar pagos y conceptos financieros (registrar pagos, consultar estados de pago).
-   Registrar y gestionar observaciones académicas o disciplinarias por estudiante.
-   Registrar y gestionar inasistencias de estudiantes.
-   Visualizar estadísticas e informes relacionados con el desempeño académico, asistencia y estado financiero de la institución.

---

### 👨‍🏫 Docente

El usuario con rol de **Docente** tiene acceso limitado a funciones relacionadas con la enseñanza y la gestión de sus asignaturas:

-   Visualizar su propio horario académico.
-   Registrar y modificar calificaciones de los estudiantes en las materias asignadas.
-   Registrar y gestionar asistencias (presente, ausente, retardo) de los grupos asignados.
-   Visualizar y gestionar únicamente los grupos, materias y periodos que le han sido asignados.

---

### 🎓 Estudiante

El usuario con rol de **Estudiante** tiene acceso para consultar su información académica y personal:

-   Visualizar su horario académico.
-   Consultar las materias inscritas.
-   Consultar sus calificaciones por materia y periodo académico.
-   Consultar observaciones registradas por los docentes o administrativos.
-   Consultar el historial de inasistencias.

---

### 👨‍👩‍👧 Tutor/Padre

El usuario con rol de **Tutor/Padre** tiene acceso a la información académica y de comportamiento de su acudido:

-   Consultar el perfil del estudiante (hijo/acudido).
-   Visualizar las calificaciones por materia y periodo académico.
-   Consultar observaciones académicas o disciplinarias del estudiante.
-   Consultar el historial de inasistencias.

---

## 📁 Estructura de Carpetas

```
gestion-escolar/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/                    # Controladores para endpoints REST
│   │   │   ├── Auth/                   # Controladores de autenticación
│   │   │   ├── Web/                    # Controladores para vistas web
│   │   │   └── ViewsController.php
│   │   └── Middleware/
│   │       ├── PermisoMiddleware.php
│   │       └── RolMiddleware.php
│   ├── Models/                         # Modelos Eloquent
│   └── Services/                       # Servicios de negocio
├── config/                             # Archivos de configuración
├── database/
│   ├── migrations/                     # Migraciones de base de datos
│   ├── seeders/                        # Seeders para datos iniciales
│   └── gestion-escolar.sql
├── public/
│   ├── images/                         # Imágenes públicas
│   ├── index.php                       # Punto de entrada
│   └── .htaccess                       # Configuración Apache
├── resources/
│   ├── views/                          # Vistas Blade
│   ├── js/                             # Archivos JavaScript
│   ├── css/                            # Estilos CSS
│   └── fonts/                          # Fuentes tipográficas
├── routes/
│   ├── api.php                         # Rutas de API
│   └── web.php                         # Rutas web
└── storage/                            # Archivos subidos y logs
```

### Descripción de Directorios Principales

-   **app/Http/Controllers**: Contiene todos los controladores organizados por tipo (API, Web, Auth)
-   **app/Http/Middleware**: Middleware personalizado para permisos y roles
-   **database**: Migraciones, seeders y esquema de base de datos
-   **public**: Archivos accesibles públicamente y punto de entrada
-   **resources**: Recursos frontend (vistas, assets, etc.)
-   **routes**: Definición de rutas de la aplicación
-   **storage**: Almacenamiento de archivos y logs
-   **tests**: Pruebas unitarias y de integración

## ⚙️ Instalación

1. **Clonar el repositorio**

    ```bash
    git clone https://github.com/AndresGutierrezHurtado/gestion-escolar.git
    cd gestion-escolar
    ```

2. **Instalar dependencias**
   Ejecuta los siguientes comandos para instalar las dependencias de PHP y JavaScript:

    ```bash
    composer install
    npm install
    ```

3. **Configurar el archivo .env**
   Crea un archivo `.env` en la raíz del proyecto basado en el archivo `.env.example`. Configura las variables de entorno según tu entorno local, especialmente:

    - Conexión a base de datos
    - Claves de aplicación
    - Configuración de correo
    - Configuración Google OAuth

4. **Migrar la base de datos**
   Ejecuta el comando de migraciones:

    ```bash
    php artisan migrate
    php artisan db:seed
    ```

5. **Ejecutar la aplicación**
   Inicia los procesos de desarrollo:
    - Para compilar los assets frontend:
        ```bash
        npm run dev
        ```
    - Para levantar el servidor:
        ```bash
        php artisan serve
        ```

### Requisitos del Sistema

-   PHP >= 8.1
-   Composer
-   Node.js >= 16
-   MySQL >= 8.0
-   Apache/Nginx

## 📞 Contacto

Para preguntas, soporte o colaboración, por favor contacta:

-   Andrés Gutiérrez Hurtado
-   Correo: [andres52885241@gmail.com](mailto:andres52885241@gmail.com)
-   LinkedIn: [Andrés Gutiérrez](https://www.linkedin.com/in/andr%C3%A9s-guti%C3%A9rrez-hurtado-25946728b/)
-   GitHub: [@AndresGutierrezHurtado](https://github.com/AndresGutierrezHurtado)
-   Portafolio: [Link portafolio](https://andres-portfolio-b4dv.onrender.com)

## ⚠️ Licencia y Uso

Este proyecto se encuentra protegido bajo derechos de autor. Su código fuente se proporciona únicamente con fines de revisión y consulta.

🚫 No está permitido:

-   Usar este código en proyectos personales, académicos o comerciales.
-   Modificar, redistribuir o copiar total o parcialmente este repositorio.

Para obtener permisos especiales de uso, contacta a: [andres52885241@gmail.com](mailto:andres52885241@gmail.com)
