# Seguimientos PHP

Diez ejercicios resueltos con la arquitectura **MVC** usando Laravel.

Es **un solo proyecto**: cada ejercicio es un módulo independiente con su propio
controlador, su propio archivo de rutas y su propia vista, y se entra a cada uno
por su propio endpoint.

---

## Ejercicios y endpoints

| # | Ejercicio | Endpoint | Controlador |
|---|-----------|----------|-------------|
| — | Menú principal | `GET /` | `HomeController` |
| 1 | Lista de Tareas | `GET /tasks` | `TasksController` |
| 2 | Calculadora de Propinas | `GET /tips` | `TipsController` |
| 3 | Generador de Contraseñas | `GET /passwords` | `PasswordsController` |
| 4 | Gestor de Gastos | `GET /expenses` | `ExpensesController` |
| 5 | Sistema de Reservas | `GET /reservations` | `ReservationsController` |
| 6 | Gestor de Notas | `GET /notes` | `NotesController` |
| 7 | Calendario de Eventos | `GET /events` | `EventsController` |
| 8 | Plataforma de Recetas | `GET /recipes` | `RecipesController` |
| 9 | Juego de Memoria | `GET /memory` | `MemoryController` |
| 10 | Plataforma de Encuestas | `GET /surveys` | `SurveysController` |

Cada módulo expone además las acciones que necesita:

```
POST   /tasks/store            PUT    /tasks/toggle/{id}       DELETE /tasks/delete/{id}
POST   /tips/calculate
POST   /passwords/generate
POST   /expenses/store                                          DELETE /expenses/delete/{id}
POST   /reservations/store     PUT    /reservations/confirm/{id}
                                                                DELETE /reservations/cancel/{id}
POST   /notes/store            PUT    /notes/update/{id}        DELETE /notes/delete/{id}
POST   /events/store           PUT    /events/update/{id}       DELETE /events/delete/{id}
GET    /recipes/{id}
POST   /recipes/store          PUT    /recipes/favorite/{id}    DELETE /recipes/delete/{id}
POST   /memory/store
GET    /surveys/{id}
POST   /surveys/store          POST   /surveys/vote/{id}
                               PUT    /surveys/toggle/{id}      DELETE /surveys/delete/{id}
```

---

## Qué hace cada ejercicio

**1 · Lista de Tareas.** Agregar tareas, marcarlas como completadas o
devolverlas a pendientes, y eliminarlas. Las pendientes se muestran primero.

**2 · Calculadora de Propinas.** Calcula la propina a partir del total de la
cuenta y el porcentaje elegido, y reparte el total entre el número de personas.

**3 · Generador de Contraseñas.** Genera contraseñas aleatorias con
`random_int()` (generador criptográficamente seguro), permitiendo ajustar la
longitud y qué tipos de caracteres incluir. Excluye caracteres ambiguos
(`l`, `1`, `O`, `0`) y muestra una barra de entropía.

**4 · Gestor de Gastos.** Registra gastos diarios con descripción, valor,
categoría y fecha. Muestra el total del mes, el peso de cada categoría sobre ese
total y la comparación con los últimos seis meses. El mes viaja por la URL, así
que la vista es recargable y compartible.

**5 · Sistema de Reservas.** Se elige un servicio del catálogo y un día, y la
aplicación pinta las franjas horarias marcando cuáles están libres, cuáles están
ocupadas y cuáles ya pasaron. La reserva se crea en estado *pendiente* y hay que
**confirmarla**; al cancelarla la franja vuelve a quedar libre. Atención de lunes
a sábado, de 8:00 a 17:00, en franjas de una hora.

**6 · Gestor de Notas.** Notas con título, cuerpo y categoría. El buscador mira
tanto el título como el contenido, y el filtro por categoría muestra cuántas
notas tiene cada una. El mismo formulario sirve para crear y para editar.

**7 · Calendario de Eventos.** Rejilla mensual de lunes a domingo con los eventos
pintados en su día; al hacer clic en un día el formulario se abre con esa fecha, y
al hacer clic en un evento se abre para editarlo. Cada evento puede llevar un
recordatorio (15 minutos, 1 hora, 1 día o 1 semana antes); el panel de *próximos
eventos* resalta en amarillo y con campana los que ya entraron en esa ventana.

**8 · Plataforma de Recetas.** Se publica una receta con su tipo de comida,
tiempo, ingredientes (uno por línea) y preparación. Se puede buscar por nombre o
por ingrediente, filtrar por tipo, y guardar recetas en favoritas. Cada receta
tiene su propia URL, que es la que se comparte.

**9 · Juego de Memoria.** Tres niveles (6, 8 y 12 parejas). El mazo se baraja en
el servidor, así que cada partida reparte distinto. El navegador lleva las
jugadas y el cronómetro; al ganar se guarda el puntaje, que **calcula el
servidor** con la fórmula `parejas × 100 − 5 por jugada de más − 2 por segundo`.
Cada nivel tiene su propio ranking.

**10 · Plataforma de Encuestas.** Se crea una encuesta con hasta 6 opciones y se
vota. Los resultados se presentan como barras con sus porcentajes. Cada visitante
vota una sola vez por encuesta, lo que se respalda con un `UNIQUE` en la base de
datos. Las encuestas se pueden cerrar y reabrir; cerradas siguen mostrando sus
resultados pero no admiten votos.

---

## Requisitos

- PHP 8.2 o superior
- Composer
- MySQL encendido (por ejemplo el que trae WAMP o XAMPP)

No hace falta Node ni npm: los estilos y los scripts se cargan por CDN.

## Puesta en marcha

Son dos comandos.

```bash
git clone <url-del-repo>
cd seguimientos_php
```

```bash
composer run setup
```

Ese comando instala las dependencias, crea el `.env`, genera la clave de la
aplicación y corre las migraciones con los datos iniciales. Si la base de datos
`seguimientosdb` todavía no existe, Artisan pregunta si quiere crearla: responder
**yes**.

```bash
php artisan serve
```

La aplicación queda en <http://localhost:8000>.

### Si las credenciales de MySQL son distintas

Editar `.env` antes de correr `composer run setup`, o corregirlo y repetir
`php artisan migrate --seed`:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=seguimientosdb
DB_USERNAME=root
DB_PASSWORD=
```

---

## Estructura

```
app/
├── Http/Controllers/        # un controlador por ejercicio
│   ├── HomeController.php
│   ├── TasksController.php          TipsController.php
│   ├── PasswordsController.php      ExpensesController.php
│   ├── ReservationsController.php   NotesController.php
│   ├── EventsController.php         RecipesController.php
│   └── MemoryController.php         SurveysController.php
└── Models/                  # modelos Eloquent
    ├── Task.php       Expense.php     Service.php    Reservation.php
    ├── Note.php       Event.php       Recipe.php     MemoryScore.php
    └── Survey.php     SurveyOption.php               SurveyVote.php

database/
├── migrations/              # una por tabla
└── seeders/
    └── ServiceSeeder.php    # catálogo del ejercicio 5

resources/views/
├── layouts/app.blade.php    # plantilla base (Bootstrap + SweetAlert2)
├── home/index.blade.php     # menú de los diez ejercicios
├── tasks/     tips/      passwords/  expenses/  reservations/
└── notes/     events/    recipes/    memory/    surveys/

routes/
├── web.php                  # ruta raíz + include de cada ejercicio
└── web/
    ├── tasks.php   tips.php     passwords.php  expenses.php  reservations.php
    └── notes.php   events.php   recipes.php    memory.php    surveys.php
```

### Cómo se aplica el MVC

- **Modelo** — las clases de `app/Models` son el acceso a datos. Cada una
  corresponde a una tabla y encapsula sus relaciones (`Reservation` pertenece a
  un `Service`; una `Survey` tiene muchas `SurveyOption` y muchos `SurveyVote`)
  y sus conversiones de tipo (`spent_on` como fecha, `reserved_at` y `starts_at`
  como fecha y hora).
- **Vista** — las plantillas Blade de `resources/views` solo presentan. Todas
  heredan de `layouts/app.blade.php`, que aporta la barra de navegación, los
  estilos y el manejo de mensajes flash y errores de validación.
- **Controlador** — las clases de `app/Http/Controllers` reciben la petición,
  validan la entrada, aplican las reglas de negocio, consultan el modelo y
  deciden qué vista devolver. Ninguna regla de negocio vive en la vista.

Después de cada operación que escribe en la base de datos se redirige en lugar de
devolver la vista directamente (patrón *POST → Redirect → GET*), para que al
recargar el navegador no se repita la operación.

Los filtros, búsquedas y meses viajan por la URL con `GET`, de modo que cualquier
pantalla se puede recargar, marcar o compartir tal como se está viendo.

La aplicación corre en la zona horaria `America/Bogota` (`APP_TIMEZONE` en el
`.env`). Importa en los ejercicios con fechas: sin ella, PHP usaría UTC y «hoy»
cambiaría cinco horas antes que el reloj del usuario.
