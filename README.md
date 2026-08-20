# Caraway System

> [!IMPORTANT]
> This project is  under development. The code and documentation are subject to change.

## 📋 About

This project is a comprehensive system for managing restaurant operations, including menu, orders, delivery, and customer information.

## 🚀 Getting Started

### Prerequisites

- Node.js (v18 or higher recommended)
- npm (or yarn)
- Python (3.10+ for backend)
- PostgreSQL (15+ for database)

### Installation

1. Clone the repository:
   ```bash
   git clone <repository-url>
   cd caraway-system
   ```

2. Install dependencies:
   ```bash
   # Backend
   cd backend
   pip install -r requirements.txt
   
   # Frontend
   cd ../frontend
   npm install
   ```

3. Setup environment variables:
   - Create a `.env` file in the `backend` directory based on `.env.example`
   - Create a `.env` file in the `frontend` directory based on `.env.example`

4. Database setup:
   ```bash
   # Run migrations
   cd backend
   python manage.py migrate
   ```

### Running the Application

1. Start the backend:
   ```bash
   cd backend
   python manage.py runserver
   ```

2. Start the frontend:
   ```bash
   cd ../frontend
   npm run dev
   ```

3. Open [http://localhost:3000](http://localhost:3000) in your browser.

## 📂 Project Structure

```
caraway-system/
├── backend/                # Django backend
│   ├── main/               # Django project
│   ├── restaurant/         # Restaurant app
│   ├── orders/             # Orders app
│   ├── users/              # Users app
│   ├── menu/               # Menu app
│   └── ...
├── frontend/               # React frontend
│   ├── src/
│   │   ├── components/     # React components
│   │   ├── pages/          # Page components
│   │   ├── services/       # API services
│   │   └── ...
│   └── ...
├── docs/                   # Documentation
├── .gitignore
├── README.md
└── ...
```

## 🛠️ Development

### Branching Strategy

- `main`: Production-ready code
- `develop`: Development branch
- Feature branches: `feature/feature-name`
- Bugfix branches: `bugfix/bugfix-name`
- Hotfix branches: `hotfix/hotfix-name`

### Coding Standards

- **Backend**: PEP 8 compliance, Django best practices
- **Frontend**: React best practices, component-based architecture
- **Commit messages**: Conventional commits format

## 🧪 Testing

### Backend Tests

```bash
cd backend
python manage.py test
```

### Frontend Tests

```bash
cd frontend
npm test
```

## 📄 License

This project is proprietary software. All rights reserved.

## 🤝 Contributing

Contributions are welcome! Please follow these steps:

1. Create a feature branch (`git checkout -b feature/AmazingFeature`)
2. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
3. Push to the branch (`git push origin feature/AmazingFeature`)
4. Open a Pull Request