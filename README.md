# SlotSwapper - Backend API

A RESTful API backend for the SlotSwapper peer-to-peer time-slot scheduling application, built with Laravel.

## 🚀 Tech Stack

- **Framework**: Laravel 11
- **Language**: PHP 8.2
- **Database**: MySQL 8.0
- **Authentication**: JWT (tymon/jwt-auth)
- **Architecture**: MVC Pattern
- **Containerization**: Docker
- **Web Server**: Nginx + PHP-FPM

## 📋 Features

### Core Features
- ✅ JWT-based authentication
- ✅ User registration and login
- ✅ Protected API routes
- ✅ Event/Slot CRUD operations
- ✅ Swap request system with complex transaction logic
- ✅ Status management (BUSY, SWAPPABLE, SWAP_PENDING)
- ✅ Database migrations and seeders
- ✅ CORS configuration for frontend integration
- ✅ RESTful API design

### Security Features
- JWT token authentication
- Password hashing with bcrypt
- Protected routes middleware
- CORS protection
- Input validation

## 🛠️ Prerequisites

Before you begin, ensure you have the following installed:
- **Docker** and **Docker Compose**
- **Git**

For local development without Docker:
- **PHP** 8.2 or higher
- **Composer**
- **MySQL** 8.0 or higher

## 📦 Installation

### Option 1: Using Docker (Recommended)

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd slotswapper-backend
   ```

2. **Copy environment file**
   ```bash
   cp .env.example .env
   ```

3. **Configure environment variables**
   
   Edit `.env` file:
   ```env
   APP_NAME=SlotSwapper
   APP_ENV=production
   APP_KEY=
   APP_DEBUG=false
   APP_URL=http://localhost

   DB_CONNECTION=mysql
   DB_HOST=mysql
   DB_PORT=3306
   DB_DATABASE=slotswapper
   DB_USERNAME=root
   DB_PASSWORD=your_password

   JWT_SECRET=your_jwt_secret_key
   ```

4. **Build and run Docker containers**
   ```bash
   docker-compose up -d
   ```

5. **Install dependencies**
   ```bash
   docker-compose exec app composer install
   ```

6. **Generate application key**
   ```bash
   docker-compose exec app php artisan key:generate
   ```

7. **Generate JWT secret**
   ```bash
   docker-compose exec app php artisan jwt:secret
   ```

8. **Run migrations**
   ```bash
   docker-compose exec app php artisan migrate
   ```

9. **Seed database (optional)**
   ```bash
   docker-compose exec app php artisan db:seed
   ```

The API will be available at `http://localhost:8000`

### Option 2: Local Development (Without Docker)

1. **Clone and navigate**
   ```bash
   git clone <repository-url>
   cd slotswapper-backend
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Setup environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   php artisan jwt:secret
   ```

4. **Configure database**
   
   Create MySQL database and update `.env`:
   ```env
   DB_DATABASE=slotswapper
   DB_USERNAME=root
   DB_PASSWORD=your_password
   ```

5. **Run migrations**
   ```bash
   php artisan migrate
   ```

6. **Start development server**
   ```bash
   php artisan serve
   ```

The API will be available at `http://localhost:8000`

## 📁 Project Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── UserController.php       # Authentication & profile
│   │   ├── EventController.php      # Event CRUD operations
│   │   └── SwapController.php       # Swap request logic
│   ├── Middleware/
│   │   └── HandleCors.php           # CORS handling
│   └── Requests/
│       ├── RegisterRequest.php      # Registration validation
│       └── LoginRequest.php         # Login validation
├── Models/
│   ├── User.php                     # User model
│   ├── Event.php                    # Event/Slot model
│   └── SwapRequest.php              # Swap request model
database/
├── migrations/
│   ├── create_users_table.php
│   ├── create_events_table.php
│   └── create_swap_requests_table.php
routes/
└── api.php                          # API routes definition
config/
├── cors.php                         # CORS configuration
└── jwt.php                          # JWT configuration
```

## 🗄️ Database Schema

### Users Table
```sql
- id (bigint, primary key)
- name (string)
- email (string, unique)
- password (string, hashed)
- created_at, updated_at
```

### Events Table
```sql
- id (bigint, primary key)
- user_id (foreign key -> users.id)
- title (string)
- start_time (datetime)
- end_time (datetime)
- status (enum: 'BUSY', 'SWAPPABLE', 'SWAP_PENDING')
- created_at, updated_at
```

### Swap Requests Table
```sql
- id (bigint, primary key)
- requester_id (foreign key -> users.id)
- requester_slot_id (foreign key -> events.id)
- target_slot_id (foreign key -> events.id)
- status (enum: 'PENDING', 'ACCEPTED', 'REJECTED')
- created_at, updated_at
```

## 🔌 API Endpoints

### Authentication Endpoints

#### Register User
```http
POST /api/register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}

Response: 201 Created
{
  "message": "User registered successfully",
  "user": { ... },
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
}
```

#### Login
```http
POST /api/login
Content-Type: application/json

{
  "email": "john@example.com",
  "password": "password123"
}

Response: 200 OK
{
  "message": "Login successful",
  "user": { ... },
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
}
```

#### Get Profile (Protected)
```http
GET /api/profile
Authorization: Bearer {token}

Response: 200 OK
{
  "id": 1,
  "name": "John Doe",
  "email": "john@example.com"
}
```

#### Logout (Protected)
```http
POST /api/logout
Authorization: Bearer {token}

Response: 200 OK
{
  "message": "Successfully logged out"
}
```

### Event Management Endpoints (All Protected)

#### Get User's Events
```http
GET /api/events
Authorization: Bearer {token}

Response: 200 OK
[
  {
    "id": 1,
    "title": "Team Meeting",
    "start_time": "2024-12-15 10:00:00",
    "end_time": "2024-12-15 11:00:00",
    "status": "SWAPPABLE",
    "user_id": 1
  }
]
```

#### Create Event
```http
POST /api/events
Authorization: Bearer {token}
Content-Type: application/json

{
  "title": "Team Meeting",
  "start_time": "2024-12-15 10:00:00",
  "end_time": "2024-12-15 11:00:00",
  "status": "BUSY"
}

Response: 201 Created
{
  "message": "Event created successfully",
  "event": { ... }
}
```

#### Update Event
```http
PUT /api/events/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
  "title": "Updated Meeting",
  "start_time": "2024-12-15 14:00:00",
  "end_time": "2024-12-15 15:00:00"
}

Response: 200 OK
{
  "message": "Event updated successfully",
  "event": { ... }
}
```

#### Update Event Status
```http
PATCH /api/events/{id}/status
Authorization: Bearer {token}
Content-Type: application/json

{
  "status": "SWAPPABLE"
}

Response: 200 OK
{
  "message": "Event status updated",
  "event": { ... }
}
```

#### Delete Event
```http
DELETE /api/events/{id}
Authorization: Bearer {token}

Response: 200 OK
{
  "message": "Event deleted successfully"
}
```

### Swap Endpoints (All Protected)

#### Get Swappable Slots
```http
GET /api/swappable-slots
Authorization: Bearer {token}

Response: 200 OK
[
  {
    "id": 5,
    "title": "Focus Block",
    "start_time": "2024-12-16 14:00:00",
    "end_time": "2024-12-16 15:00:00",
    "status": "SWAPPABLE",
    "user": {
      "id": 2,
      "name": "Jane Smith",
      "email": "jane@example.com"
    }
  }
]
```
*Note: Returns only slots from other users, not your own*

#### Request Swap
```http
POST /api/swap-request
Authorization: Bearer {token}
Content-Type: application/json

{
  "my_slot_id": 1,
  "their_slot_id": 5
}

Response: 201 Created
{
  "message": "Swap request created successfully",
  "swap_request": {
    "id": 1,
    "requester_id": 1,
    "requester_slot_id": 1,
    "target_slot_id": 5,
    "status": "PENDING"
  }
}
```

**Business Logic:**
- Validates both slots exist and are SWAPPABLE
- Sets both slots to SWAP_PENDING
- Creates swap request with PENDING status

#### Accept/Reject Swap
```http
POST /api/swap-response/{requestId}
Authorization: Bearer {token}
Content-Type: application/json

{
  "accepted": true
}

Response: 200 OK
{
  "message": "Swap request accepted",
  "swap_request": { ... }
}
```

**Business Logic:**
- **If Accepted:**
  - Swaps the ownership (user_id) of both events
  - Sets both events back to BUSY status
  - Marks swap request as ACCEPTED
  
- **If Rejected:**
  - Sets both events back to SWAPPABLE status
  - Marks swap request as REJECTED

#### Get Incoming Swap Requests
```http
GET /api/swap-requests/incoming
Authorization: Bearer {token}

Response: 200 OK
[
  {
    "id": 1,
    "status": "PENDING",
    "requester": {
      "id": 2,
      "name": "Jane Smith"
    },
    "requester_slot": {
      "id": 5,
      "title": "Focus Block",
      "start_time": "2024-12-16 14:00:00"
    },
    "target_slot": {
      "id": 1,
      "title": "Team Meeting",
      "start_time": "2024-12-15 10:00:00"
    }
  }
]
```

#### Get Outgoing Swap Requests
```http
GET /api/swap-requests/outgoing
Authorization: Bearer {token}

Response: 200 OK
[
  {
    "id": 2,
    "status": "PENDING",
    "target_user": {
      "id": 3,
      "name": "Bob Johnson"
    },
    "requester_slot": { ... },
    "target_slot": { ... }
  }
]
```

## 🔧 Configuration

### CORS Configuration

Edit `config/cors.php`:

```php
'paths' => ['api/*'],
'allowed_methods' => ['*'],
'allowed_origins' => [
    'https://swapslot-frontend.vercel.app',  // No trailing slash!
    'http://localhost:5173',
],
'allowed_headers' => ['*'],
'supports_credentials' => true,
```

### JWT Configuration

JWT settings in `config/jwt.php`:
- **TTL**: 60 minutes (configurable)
- **Algorithm**: HS256
- **Token refresh**: Enabled

## 🐋 Docker Configuration

### Dockerfile
```dockerfile
FROM php:8.2-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    git curl unzip libpq-dev libonig-dev libzip-dev nginx supervisor \
    && docker-php-ext-install pdo pdo_mysql mbstring zip

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Set permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80
CMD service nginx start && php-fpm
```

### docker-compose.yml
```yaml
version: '3.8'

services:
  app:
    build: .
    ports:
      - "8000:80"
    volumes:
      - .:/var/www/html
    depends_on:
      - mysql
    networks:
      - slotswapper

  mysql:
    image: mysql:8.0
    environment:
      MYSQL_ROOT_PASSWORD: root
      MYSQL_DATABASE: slotswapper
    ports:
      - "3306:3306"
    volumes:
      - mysql_data:/var/lib/mysql
    networks:
      - slotswapper

volumes:
  mysql_data:

networks:
  slotswapper:
```

## 🚀 Deployment

### Render Deployment

1. **Create `render.yaml`** (optional)
   ```yaml
   services:
     - type: web
       name: slotswapper-api
       env: docker
       plan: free
       envVars:
         - key: APP_KEY
           generateValue: true
         - key: JWT_SECRET
           generateValue: true
   ```

2. **Push to GitHub**
   ```bash
   git add .
   git commit -m "Deploy to Render"
   git push origin main
   ```

3. **Deploy on Render**
   - Go to [render.com](https://render.com)
   - New → Web Service
   - Connect GitHub repository
   - Select Docker as environment
   - Add environment variables from `.env`
   - Deploy

4. **Run Migrations**
   
   After deployment, access Render shell:
   ```bash
   php artisan migrate --force
   ```

## 🐛 Known Limitations

- ❌ No email verification for registration
- ❌ No password reset functionality
- ❌ No real-time WebSocket notifications
- ❌ No unit/integration tests
- ❌ No rate limiting on API endpoints
- ❌ Basic error messages (could be more descriptive)
- ❌ No API versioning

## 🎯 Design Decisions

1. **Laravel**: Chosen for its elegant syntax, built-in features, and MVC architecture
2. **JWT Authentication**: Stateless authentication suitable for API
3. **MySQL**: Relational database for data integrity with foreign keys
4. **MVC Pattern**: Clean separation of concerns
5. **Docker**: Consistent development and deployment environment
6. **Enum for Status**: Type safety and validation at database level
7. **Eloquent ORM**: Simplified database queries and relationships

## 🔐 Security Considerations

- Passwords hashed with bcrypt
- JWT tokens for stateless authentication
- CORS configured for specific origins
- SQL injection prevention via Eloquent ORM
- Input validation on all requests
- Protected routes with middleware

## 🧪 Testing

To run tests (when implemented):
```bash
php artisan test
```

## 🔮 Future Enhancements

- Add comprehensive unit and integration tests
- Implement rate limiting
- Add API versioning
- Implement WebSocket for real-time notifications
- Add email notifications
- Implement password reset flow
- Add API documentation (Swagger/OpenAPI)
- Add request logging and monitoring
- Implement soft deletes
- Add pagination for large datasets

## 📝 Common Issues & Solutions

### Issue: JWT Secret Not Found
```bash
php artisan jwt:secret
```

### Issue: Permission Denied on Storage
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Issue: CORS Error
- Remove trailing slash from `allowed_origins`
- Clear browser cache
- Check frontend is sending correct origin header

### Issue: Database Connection Failed
- Verify MySQL is running
- Check `.env` database credentials
- Ensure database exists

## 📧 Support

For issues or questions, please open an issue on GitHub.

## 📄 License

This project is part of the ServiceHive Full Stack Intern technical challenge.

---

**Live API**: [https://your-backend.onrender.com](https://your-backend.onrender.com)

**Frontend Repository**: [Link to frontend repo]