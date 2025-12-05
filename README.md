# EPT - Educational Psychology Testing Platform

This project is a web-based platform for conducting educational psychology tests. It allows teachers or administrators to create and manage exams, assign them to participants, and monitor their progress in real-time. Participants can take the assigned tests through a user-friendly interface.

## Technologies Used

- **Backend**: Laravel (PHP)
- **Frontend**: Vue.js with TypeScript, Inertia.js, and Tailwind CSS
- **Database**: SQLite (by default, but configurable)

## Getting Started

These instructions will get you a copy of the project up and running on your local machine for development and testing purposes.

### Prerequisites

- PHP >= 8.1
- Composer
- Node.js & npm
- A web server like Nginx or Apache (optional, for production)

### Installation

1.  **Clone the repository:**

    ```bash
    git clone https://github.com/your-username/ept.git
    cd ept
    ```

2.  **Install backend dependencies:**

    ```bash
    composer install
    ```

3.  **Install frontend dependencies:**

    ```bash
    npm install
    ```

4.  **Set up the environment file:**

    Copy the example environment file and generate an application key.

    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

5.  **Configure the database:**

    By default, the application is configured to use a local SQLite database. Create the database file:

    ```bash
    touch database/database.sqlite
    ```

    Then, run the database migrations and seed the database with initial data:

    ```bash
    php artisan migrate --seed
    ```

6.  **Run the development servers:**

    You'll need to run both the Vite development server for the frontend and the Laravel development server for the backend.

    ```bash
    # In one terminal, run the Vite server:
    npm run dev

    # In another terminal, run the Laravel server:
    php artisan serve
    ```

    Your application should now be available at `http://localhost:8000`.

### Running Tests

To run the PHPUnit test suite, use the following command:

```bash
php artisan test
```

## Usage

### User Roles

-   **Admin/Teacher**: Can create exams, assign participants, and monitor progress.
-   **Participant**: Can take assigned exams.

### Default Login Credentials

-   **Admin**:
    -   **Email**: `admin@email.com`
    -   **Password**: `123qwe!"§`
-   **Participant**:
    -   **Email**: `john@email.com`
    -   **Password**: `123qwe!"§`
