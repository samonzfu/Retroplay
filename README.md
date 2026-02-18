# Retroplay

Retroplay is a web application for renting retro video game consoles and games. Rediscover the classics with our collection of vintage hardware and software.

## Features

- **Catalog Browser**: Browse a wide selection of retro consoles (Game Boy, NES, PlayStation, etc.) and video games.
- **User Accounts**: Register and log in to manage your rentals.
- **Shopping Cart**: Add items to your cart for rental.
- **Reservations**: Place rental orders and view your reservation history.
- **Responsive Design**: Accessible on various devices.

## Project Structure

The project is organized as follows:

- **`back/`**: Contains backend logic.
  - `Conexion_BD/`: Database connection scripts.
  - `Procesar/`: Processing logic (e.g., form handling).
- **`front/`**: Contains frontend pages and assets.
  - `inicio/`: Main landing page and product catalog.
  - `login/`: User login page.
  - `registro/`: User registration page.
  - `carrito/`: Shopping cart page.
  - `mi_cuenta/`: User account management.
  - `mis_reservas/`: User's reservation history.
- **`BBDD/`**: Database initialization scripts.
  - `setup_final.sql`: SQL script to create the database and tables, and populate initial data.

## Technologies Used

- **Backend**: PHP
- **Database**: MySQL
- **Frontend**: HTML, CSS, JavaScript

## Setup Instructions

### Prerequisites

- A web server (e.g., Apache, Nginx) with PHP support.
- MySQL database server.

### Installation

1.  **Clone the repository**:
    ```bash
    git clone <repository_url>
    ```

2.  **Database Setup**:
    -   Create a MySQL database (or let the script do it).
    -   Import the `BBDD/setup_final.sql` file into your MySQL server. This will create the `retroplay` database, tables, and insert sample data.
    -   The script creates a user `retroplay` with password `Retroplay123$`. If you wish to use different credentials, update `back/Conexion_BD/conexion.php`.

3.  **Configuration**:
    -   Ensure your web server is configured to serve the project root.
    -   Check `back/Conexion_BD/conexion.php` to ensure the database credentials match your environment.

    ```php
    $conexion = mysqli_connect("localhost", "retroplay", "Retroplay123$", "retroplay");
    ```

4.  **Running the Application**:
    -   Start your web server.
    -   Navigate to `http://localhost/retroplay/front/inicio/inicio.php` (adjust the path based on your server configuration).

## Usage

1.  **Register**: Create a new account in the registration section.
2.  **Browse**: Explore the catalog of consoles and games on the home page.
3.  **Rent**: Add items to your cart and proceed to checkout to create a reservation.
4.  **Manage**: View your reservations in the "My Reservations" section.

## Authors

Proyecto Sara y Piero
