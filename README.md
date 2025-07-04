# edtech
edtech solution for students
# SchoolTry EdTech Project

An AI-powered learning assistant that helps students interact with lesson content through intelligent Q&A.

## Features

- Admin interface for teachers to upload and manage lesson content
- Student interface to read lessons and ask questions
- AI-powered question answering system using OpenAI's GPT model
- Related lesson recommendations based on student questions
- User authentication and role-based access control

## Tech Stack

- **Backend**: Laravel 12 with PHP 8.2
- **Frontend**: Vue.js 3 with Vite
- **AI Integration**: OpenAI GPT API
- **Authentication**: Laravel Sanctum
- **Database**: SQLite (for simplicity and portability)

## Setup Instructions

### Prerequisites

- PHP 8.2 or higher
- Node.js 16 or higher
- Composer
- npm
- OpenAI API key

### Backend Setup

1. Navigate to the backend directory:
```bash
cd edtech-backend
```

2. Install PHP dependencies:
```bash
composer install
```

3. Copy .env.example to .env and configure your environment:
```bash
cp .env.example .env
```

4. Generate application key:
```bash
php artisan key:generate
```

5. Run database migrations:
```bash
php artisan migrate --seed
```

6. Start the Laravel development server:
```bash
php artisan serve
```

### Frontend Setup

1. Navigate to the frontend directory:
```bash
cd edtech-frontend
```

2. Install Node dependencies:
```bash
npm install
```

3. Start the development server:
```bash
npm run dev
```

## AI Integration

The project uses OpenAI's GPT model to:
- Answer student questions about lesson content
- Generate relevant lesson recommendations
- Provide contextual explanations

The AI integration is implemented through a service layer in the Laravel backend, which processes the lesson content and student questions before sending them to the OpenAI API.

## Project Structure

- `edtech-backend/`: Laravel backend application
  - `app/Models/`: Database models (User, Lesson, Question)
  - `app/Http/Controllers/`: API controllers
  - `database/migrations/`: Database structure
  - `routes/api.php`: API endpoints

- `edtech-frontend/`: Vue.js frontend application
  - `src/components/`: Vue components
  - `src/views/`: Page components
  - `src/router.js`: Route definitions
  - `src/api.js`: API integration

## Usage

1. Register as an admin/teacher to access the admin dashboard
2. Create and upload lesson content
3. Students can register and access lessons
4. Students can ask questions about lessons and receive AI-generated responses
5. The system will recommend related lessons based on student interactions

## Testing

### Backend Tests
```bash
cd edtech-backend
php artisan test
```

### Frontend Tests
```bash
cd edtech-frontend
npm run test
```



