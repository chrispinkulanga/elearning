# eLearning Platform - Vue.js Frontend

A modern, responsive Vue.js frontend for the eLearning platform, designed to work seamlessly with the Laravel backend API. This project provides a comprehensive learning management system with real-time data integration, role-based access control, and modern UI/UX.

## 🚀 Features

### Core Functionality
- **User Authentication**: Login, registration, password reset, and email verification
- **Role-based Access**: Separate interfaces for students and instructors
- **Course Management**: Browse, search, filter, and enroll in courses
- **Dashboard**: Personalized dashboards for different user roles
- **Profile Management**: Update profile information and account settings

### Student Features
- **Course Enrollment**: Enroll in courses and track progress
- **Learning Progress**: View course progress and completed lessons
- **Certificates**: Download certificates upon course completion
- **Activity Tracking**: Monitor learning activity and achievements
- **My Courses**: Manage enrolled courses with progress tracking

### Instructor Features
- **Course Creation**: Create and manage courses with rich content
- **Student Management**: View enrolled students and their progress
- **Analytics**: Track course performance and revenue
- **Content Management**: Organize lessons, sections, and materials
- **Course Editing**: Update course information and content

### Technical Features
- **Real-time Data**: Live integration with Laravel backend API
- **Responsive Design**: Mobile-first approach with Tailwind CSS
- **State Management**: Pinia for reactive state management
- **Route Protection**: Authentication and role-based route guards
- **Error Handling**: Comprehensive error handling and user feedback
- **Payment Integration**: Secure payment processing for course enrollment
- **Forum System**: Community discussion platform
- **Notifications**: Real-time notification system

## 🛠️ Technology Stack

- **Frontend Framework**: Vue.js 3 with Composition API
- **Build Tool**: Vite for fast development and building
- **State Management**: Pinia for reactive state management
- **Routing**: Vue Router with navigation guards
- **Styling**: Tailwind CSS with custom design system
- **HTTP Client**: Axios with interceptors for API communication
- **Notifications**: Vue Toastification for user feedback
- **Development**: ESLint and Prettier for code quality

## 📁 Project Structure

```
elearning-frontend/
├── public/                 # Static assets
├── src/
│   ├── assets/            # Images, fonts, and global styles
│   ├── components/        # Reusable Vue components
│   │   └── NotificationDropdown.vue
│   ├── router/            # Vue Router configuration
│   │   └── index.js
│   ├── services/          # API services and utilities
│   │   └── api.js
│   ├── stores/            # Pinia state management
│   │   ├── auth.js
│   │   └── courses.js
│   ├── views/             # Page components
│   │   ├── auth/          # Authentication pages
│   │   │   ├── Login.vue
│   │   │   ├── Register.vue
│   │   │   ├── ForgotPassword.vue
│   │   │   └── ResetPassword.vue
│   │   ├── courses/       # Course-related pages
│   │   │   ├── Courses.vue
│   │   │   ├── CourseDetail.vue
│   │   │   ├── CourseCreate.vue
│   │   │   ├── CourseEdit.vue
│   │   │   └── MyCourses.vue
│   │   ├── dashboard/     # Dashboard pages
│   │   │   ├── Dashboard.vue
│   │   │   ├── InstructorDashboard.vue
│   │   │   └── StudentDashboard.vue
│   │   ├── payment/       # Payment pages
│   │   │   └── PaymentForm.vue
│   │   ├── forum/         # Forum pages
│   │   │   └── ForumTopics.vue
│   │   ├── profile/       # Profile management
│   │   │   └── Profile.vue
│   │   └── NotFound.vue
│   ├── App.vue            # Root component
│   └── main.js            # Application entry point
├── index.html             # HTML template
├── package.json           # Dependencies and scripts
├── vite.config.js         # Vite configuration
├── tailwind.config.js     # Tailwind CSS configuration
└── README.md             # Project documentation
```

## 🚀 Getting Started

### Prerequisites

- Node.js (v16 or higher)
- npm or yarn
- Laravel backend running on `http://localhost:8000`

### Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd elearning-frontend
   ```

2. **Install dependencies**
   ```bash
   npm install
   ```

3. **Configuration**
   The app uses JavaScript-based configuration in `src/config/app.config.js`. 
   No `.env` file is needed - configuration is auto-detected based on hostname:
   - `localhost` → Development mode (API: http://localhost:8000/api)
   - Any other domain → Production mode (auto-detects current domain)
   
   To customize settings, edit `src/config/app.config.js`

4. **Start Development Server**
   ```bash
   npm run dev
   ```

5. **Build for Production**
   ```bash
   npm run build
   ```

## 🔧 Configuration

### API Configuration
The frontend uses a JavaScript configuration file (`src/config/app.config.js`) instead of `.env` files. 
The API URL is automatically detected based on the hostname:

**Development (localhost):**
```javascript
apiUrl: 'http://localhost:8000/api'
```

**Production (any domain):**
```javascript
apiUrl: '${window.location.origin}/api'  // Auto-detects your domain
```

To customize settings, edit `src/config/app.config.js`:
```javascript
const API_CONFIG = {
  development: {
    apiUrl: 'http://localhost:8000/api',
    baseUrl: 'http://localhost:8000'
  },
  production: {
    apiUrl: 'https://your-domain.com/api',
    baseUrl: 'https://your-domain.com'
  }
}
```

### Vite Configuration
The Vite configuration includes:
- Vue.js plugin
- Path aliases (`@` for `src`)
- Development server proxy for API calls
- Build optimization settings

### Tailwind CSS Configuration
Custom design system with:
- Primary, secondary, success, warning, and danger color palettes
- Custom font family (Inter)
- Responsive breakpoints
- Custom animations and utilities

## 📱 Features Overview

### Authentication System
- **Login/Register**: User authentication with email/password
- **Password Reset**: Forgot password functionality with email verification
- **Email Verification**: Account verification via email
- **Role-based Access**: Different interfaces for students and instructors

### Course Management
- **Course Browsing**: Search, filter, and browse available courses
- **Course Details**: Comprehensive course information and enrollment
- **Course Creation**: Instructors can create new courses with rich content
- **Course Editing**: Update course information and content
- **Progress Tracking**: Monitor learning progress and completion

### Payment System
- **Secure Payments**: Multiple payment methods (card, PayPal, Stripe)
- **Order Summary**: Detailed breakdown of costs and fees
- **Payment Processing**: Real-time payment status updates
- **Receipt Generation**: Automatic receipt and confirmation emails

### Forum System
- **Topic Creation**: Create discussion topics with categories
- **Search & Filter**: Find relevant discussions quickly
- **Real-time Updates**: Live notification of new replies
- **Moderation**: Topic editing and deletion capabilities

### Notification System
- **Real-time Notifications**: Instant updates for course activities
- **Notification Types**: Course enrollment, completion, announcements
- **Mark as Read**: Manage notification status
- **Notification Badge**: Unread count display

### Dashboard Features
- **Student Dashboard**: Course progress, certificates, learning activity
- **Instructor Dashboard**: Course analytics, student management, revenue tracking
- **Progress Visualization**: Charts and graphs for learning analytics
- **Quick Actions**: Easy access to common tasks

## 🔒 Security Features

- **Authentication Guards**: Protected routes for authenticated users
- **Role-based Access**: Different permissions for students and instructors
- **CSRF Protection**: Cross-site request forgery protection
- **Input Validation**: Client-side and server-side validation
- **Secure API Communication**: HTTPS and token-based authentication

## 🎨 Design System

### Color Palette
- **Primary**: Blue shades for main actions and branding
- **Secondary**: Gray shades for secondary elements
- **Success**: Green for positive actions and states
- **Warning**: Yellow/Orange for cautionary elements
- **Danger**: Red for destructive actions and errors

### Typography
- **Font Family**: Inter (Google Fonts)
- **Font Weights**: 300, 400, 500, 600, 700
- **Responsive**: Scalable typography across devices

### Components
- **Buttons**: Primary, secondary, danger variants
- **Cards**: Consistent card layouts for content
- **Forms**: Styled form inputs with validation states
- **Modals**: Overlay dialogs for user interactions
- **Navigation**: Responsive navigation with mobile menu

## 📊 API Integration

The frontend integrates with the Laravel backend through comprehensive API services:

### Authentication API
- User registration and login
- Password reset and email verification
- Token-based authentication

### Course API
- Course CRUD operations
- Enrollment management
- Progress tracking
- Review and rating system

### Payment API
- Payment processing
- Order management
- Receipt generation

### Forum API
- Topic creation and management
- Reply system
- Search and filtering

### Notification API
- Real-time notifications
- Notification management
- Read/unread status

## 🧪 Testing

### Unit Testing
```bash
npm run test:unit
```

### E2E Testing
```bash
npm run test:e2e
```

### Code Quality
```bash
npm run lint
npm run format
```

## 🚀 Deployment

### Production Build
```bash
npm run build
```

### Configuration for Production
Before building for production, update the production API URL in `src/config/app.config.js`:

```javascript
production: {
  apiUrl: 'https://your-backend-api.com/api',
  baseUrl: 'https://your-backend-api.com'
}
```

The build will automatically use production settings when deployed to your domain.

### Deployment Platforms
- **Vercel**: Zero-config deployment
- **Netlify**: Static site hosting
- **AWS S3**: Static website hosting
- **Docker**: Containerized deployment

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🆘 Support

For support and questions:
- Create an issue in the repository
- Check the documentation
- Contact the development team

## 🔄 Version History

- **v1.0.0**: Initial release with core features
- **v1.1.0**: Added payment integration and forum system
- **v1.2.0**: Enhanced notification system and mobile responsiveness
- **v1.3.0**: Improved performance and added advanced features

---

Built with ❤️ using Vue.js 3 and Tailwind CSS 