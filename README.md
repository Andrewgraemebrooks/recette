# Recette

A Laravel-based backend for managing recipes with features such as recipe CRUD operations, ingredient management, and category organisation.

## Getting Started

### Prerequisites

- [Composer](https://getcomposer.org/)
- [Laravel](https://laravel.com/)

### Installation

1. **Clone the repository:**
   ```bash
   git clone https://github.com/Andrewgraemebrooks/recette.git
   ```

2. **Install dependencies:**
   ```bash
   composer install
   ```

3. **Copy environment file and configure database:**
   ```bash
   cp .env.example .env
   ```

4. **Generate application key:**
   ```bash
   php artisan key:generate
   ```

5. **Run migrations**
   ```bash
   php artisan migrate
   ```

6. **Start the development server:**
   ```bash
   php artisan serve
   ```

   Access the API at `http://localhost:8000`.

## Usage

### Authentication

Obtain an authentication token by making a `POST` request to `/api/login` with valid credentials.

### Endpoints

- **Recipes:** `/api/recipes`
- **Ingredients:** `/api/ingredients`
- **Categories:** `/api/categories`
- **Groceries:** `/api/grocery`

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
