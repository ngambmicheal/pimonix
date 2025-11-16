# Pimonix - Mini Wallet Application

A high-performance digital wallet application built with Laravel and Vue.js, featuring real-time transaction updates via Pusher.

## 🚀 Features

- **User Management**: Registration, login, and authentication with Laravel Sanctum
- **Money Transfers**: P2P transfers with 1.5% commission
- **Real-time Updates**: Instant transaction notifications via Pusher
- **High Concurrency**: Pessimistic locking to handle hundreds of transfers per second
- **Scalability**: Optimized for millions of transaction records
- **Modern Stack**: Laravel 12 + Vue 3 (Composition API) + Bootstrap 5

## 📋 Requirements

- PHP >= 8.2
- Composer
- Node.js >= 20.x
- MySQL or PostgreSQL
- Pusher account (free tier available)

## 🛠️ Installation

### Option 1: Local Development

#### Backend Setup

```bash
# Navigate to backend directory
cd backend

# Install PHP dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure database in .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pimonix
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Run migrations and seeders
php artisan migrate --seed

# Start Laravel development server
php artisan serve
```

#### Frontend Setup

```bash
# Navigate to frontend directory
cd frontend

# Install dependencies
npm install

# Copy environment file
cp .env.example .env

# Configure Pusher credentials in .env
VITE_PUSHER_APP_KEY=your-pusher-key
VITE_PUSHER_APP_CLUSTER=your-cluster

# Start development server
npm run dev
```

### Option 2: Docker

```bash
# Build and start containers
docker-compose up -d

# Run migrations and seeders
docker-compose exec backend php artisan migrate --seed
```

- Backend: http://localhost:8000
- Frontend: http://localhost:5173
- MySQL: localhost:3306

## 🔑 Pusher Setup

1. Create a free account at [pusher.com](https://pusher.com)
2. Create a new Channels app
3. Copy credentials to backend `.env`:
   ```env
   BROADCAST_DRIVER=pusher
   PUSHER_APP_ID=your-app-id
   PUSHER_APP_KEY=your-app-key
   PUSHER_APP_SECRET=your-app-secret
   PUSHER_APP_CLUSTER=your-cluster
   ```
4. Copy key to frontend `.env`:
   ```env
   VITE_PUSHER_APP_KEY=your-app-key
   VITE_PUSHER_APP_CLUSTER=your-cluster
   ```

## 👥 Test Users

After running seeders, you'll have these test accounts:

| Email | Password | Role | Balance |
|-------|----------|------|---------|
| admin@pimonix.com | password | Admin | $0.00 |
| john@example.com | password | User | $1,000.00 |
| jane@example.com | password | User | $1,000.00 |
| alice@example.com | password | User | $1,000.00 |
| bob@example.com | password | User | $1,000.00 |
| charlie@example.com | password | User | $1,000.00 |

## 📡 API Endpoints

### Authentication
- `POST /api/register` - Register new user
- `POST /api/login` - Login user
- `POST /api/logout` - Logout user (auth required)
- `GET /api/user` - Get authenticated user (auth required)

### Transactions
- `GET /api/transactions` - Get transaction history + balance (auth required)
- `POST /api/transactions` - Execute money transfer (auth required)
  ```json
  {
    "receiver_id": 2,
    "amount": 100.00
  }
  ```

## 🏗️ Architecture

### Backend (Laravel)

**Database Schema:**
- `users`: id, uid, name, email, password, balance, is_admin
- `transactions`: id, tuuid, sender_id, receiver_id, amount, commission_fee, type, status

**Key Components:**
- `TransferService`: Core business logic with pessimistic locking
- `TransactionController`: API endpoints
- `UserResource` & `TransactionResource`: API response formatting
- `TransactionCreated` event: Pusher broadcasting

**Concurrency Strategy:**
- Pessimistic locking (`SELECT ... FOR UPDATE`)
- Ordered locking (lock lower ID first) prevents deadlocks
- Atomic transactions with automatic rollback

### Frontend (Vue.js)

**Structure:**
- `stores/auth.js`: Authentication state management
- `stores/wallet.js`: Transaction and balance management
- `services/api.js`: Axios HTTP client
- `services/echo.js`: Laravel Echo/Pusher integration

**Views:**
- `/login` - User login
- `/register` - User registration
- `/wallet` - Main wallet dashboard

**Components:**
- `BalanceCard.vue` - Display current balance
- `TransferForm.vue` - Send money form
- `TransactionList.vue` - Transaction history table

## 💡 Usage

1. **Register/Login**: Create an account or login with test credentials
2. **Check Balance**: View your current balance on the wallet page
3. **Send Money**:
   - Enter receiver's user ID (2, 3, 4, 5, or 6 for test users)
   - Enter amount
   - Commission (1.5%) will be calculated automatically
   - Click "Send Money"
4. **View History**: All transactions appear in the table below
5. **Real-time Updates**: Open two browser windows with different users logged in and watch transactions appear instantly!

## 🔐 Security Features

- Laravel Sanctum token-based authentication
- Input validation on all endpoints
- SQL injection prevention via Eloquent ORM
- CSRF protection
- Authorization policies
- Rate limiting
- Password hashing with bcrypt

## 🚀 Performance Optimizations

1. **Database Indexes**: Composite indexes on (sender_id, created_at) and (receiver_id, created_at)
2. **Balance Storage**: Stored directly in users table (no need to calculate from millions of transactions)
3. **Pessimistic Locking**: Prevents race conditions in concurrent transfers
4. **Ordered Locking**: Prevents deadlocks
5. **Pagination**: Transaction history paginated (20 per page)
6. **API Resources**: Efficient JSON transformation

## 📊 Commission Calculation

For every transfer:
- **Sender**: Debited `amount + (amount × 1.5%)`
- **Receiver**: Credited `amount`
- **Admin**: Credited `amount × 1.5%`

**Example:**
- User A sends $100 to User B
- User A debited: $101.50
- User B credited: $100.00
- Admin receives: $1.50

## 🧪 Testing Tips

```bash
# Backend testing (if you create tests later)
php artisan test

# Check database state
php artisan tinker
>>> App\Models\User::all(['uid', 'name', 'balance']);
>>> App\Models\Transaction::count();

# Monitor logs
tail -f storage/logs/laravel.log

# Test concurrent transfers (requires Apache Bench)
ab -n 100 -c 10 -p transfer.json -T application/json -H "Authorization: Bearer YOUR_TOKEN" http://localhost:8000/api/transactions
```

## 🐛 Troubleshooting

**"SQLSTATE[HY000] [2002] Connection refused"**
- Ensure MySQL is running
- Check DB credentials in `.env`

**"Class 'Pusher' not found"**
- Run `composer install` in backend directory

**Real-time not working**
- Verify Pusher credentials in both backend and frontend `.env`
- Check browser console for WebSocket errors
- Enable Pusher debug console

**CORS errors**
- Verify `FRONTEND_URL` in backend `.env`
- Check Laravel CORS configuration in `config/cors.php`

## 📂 Project Structure

```
pimonix/
├── backend/                 # Laravel API
│   ├── app/
│   │   ├── Events/         # Pusher events
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   ├── Requests/
│   │   │   └── Resources/
│   │   ├── Models/
│   │   └── Services/       # Business logic
│   ├── database/
│   │   ├── migrations/
│   │   └── seeders/
│   └── routes/api.php
├── frontend/               # Vue.js SPA
│   ├── src/
│   │   ├── components/
│   │   ├── services/
│   │   ├── stores/         # Pinia stores
│   │   └── views/
│   └── package.json
├── docker/                 # Docker configuration
│   ├── backend/
│   └── frontend/
├── docker-compose.yml
├── PROGRESS.md             # Development progress tracker
└── README.md
```

## 🛣️ Roadmap / Future Improvements

- [ ] Email notifications for transactions
- [ ] Two-factor authentication
- [ ] Transaction limits and rate limiting
- [ ] Wallet to wallet transfers by email
- [ ] Transaction search and filtering
- [ ] Export transaction history (CSV/PDF)
- [ ] Admin dashboard
- [ ] Mobile app (React Native)
- [ ] Multi-currency support
- [ ] Automated testing suite
- [ ] CI/CD pipeline

## 📝 License

This project was created as a technical assignment for XXX

## 👨‍💻 Author

Built with Laravel & Vue.js following best practices for high-performance financial applications.

---