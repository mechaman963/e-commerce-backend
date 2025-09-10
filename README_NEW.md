# Project2 Backend API

This is the backend API for the Project2 application, built with Laravel. It provides the necessary endpoints for the frontend application to interact with the database and handle business logic.

## 🚀 Deployment

### Prerequisites

- PHP 8.1 or higher
- Composer
- Node.js & NPM
- MySQL/PostgreSQL database
- Git

### Local Development Setup

1. Clone the repository:
   ```bash
   git clone https://github.com/yourusername/Project2-backend.git
   cd Project2-backend
   ```

2. Install PHP dependencies:
   ```bash
   composer install
   ```

3. Install NPM dependencies:
   ```bash
   npm install
   ```

4. Copy the environment file:
   ```bash
   cp .env.example .env
   ```

5. Generate application key:
   ```bash
   php artisan key:generate
   ```

6. Configure your `.env` file with your database credentials and other settings.

7. Run database migrations:
   ```bash
   php artisan migrate --seed
   ```

8. Start the development server:
   ```bash
   php artisan serve
   ```

## 🚀 Deployment to Railway

### Prerequisites
- A Railway account
- A GitHub account with the repository connected

### Steps

1. **Connect your GitHub repository** to Railway:
   - Go to [Railway](https://railway.app/) and sign in with GitHub
   - Click "New Project" and select "Deploy from GitHub repo"
   - Select your repository and branch

2. **Configure Environment Variables**
   - In your Railway project, go to the "Variables" tab
   - Add all the variables from your `.env` file
   - Make sure to set `APP_ENV=production`
   - Generate a new `APP_KEY` using `php artisan key:generate --show` and add it

3. **Set Up Database**
   - In Railway dashboard, click "New" and select "Database"
   - Choose MySQL or PostgreSQL
   - The connection URL will be automatically added to your environment variables as `DATABASE_URL`

4. **Configure Build Command**
   - In your Railway project settings, set the following:
     - Build Command: `npm install && npm run build && composer install --optimize-autoloader --no-dev`
     - Start Command: `php artisan serve --host=0.0.0.0 --port=${PORT:-8000}`

5. **Deploy**
   - Railway will automatically deploy your application
   - You can view the deployment logs in the "Deployments" tab

## Environment Variables

Required environment variables:

```
APP_NAME=Project2
APP_ENV=production
APP_KEY=base64:...
APP_DEBUG=false
APP_URL=your-app-url.railway.app

DB_CONNECTION=mysql
DB_HOST=your-db-host
DB_PORT=3306
DB_DATABASE=your-db-name
DB_USERNAME=your-db-user
DB_PASSWORD=your-db-password

CACHE_DRIVER=file
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120
```

## API Documentation

API documentation is available at `/api/documentation` after setting up the application.

## License

This project is open-source and available under the [MIT License](LICENSE).
