import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import { useEffect } from 'react';
import { useAuthStore } from './store/authStore';
import HomePage from './pages/HomePage';
import LoginPage from './pages/LoginPage';
import RegisterPage from './pages/RegisterPage';
import AdminPage from './pages/AdminPage';
import ErrorPage from './pages/ErrorPage';
import sessionService from './services/sessionService';

// Protected Route Component
const ProtectedRoute = ({ children, requiredRole = 'admin' }: { children: React.ReactNode; requiredRole?: string }) => {
  const { isAuthenticated, user } = useAuthStore();
  
  if (!isAuthenticated) {
    // Redirect to login with a message about accessing admin area
    return <Navigate to="/login?redirect=/admin&message=Please log in to access the admin area" replace />;
  }
  
  if (user?.role_name !== requiredRole) {
    // Redirect to home with a message about insufficient permissions
    return <Navigate to="/?message=Insufficient permissions to access admin area" replace />;
  }
  
  return <>{children}</>;
};

const App: React.FC = () => {
  const { isAuthenticated } = useAuthStore();

  // Session management
  useEffect(() => {
    if (isAuthenticated) {
      // Start session monitoring when authenticated
      sessionService.startSessionMonitoring();
      
      return () => {
        // Stop session monitoring when component unmounts
        sessionService.stopSessionMonitoring();
      };
    } else {
      // Stop session monitoring when not authenticated
      sessionService.stopSessionMonitoring();
    }
  }, [isAuthenticated]);

  // Handle beforeunload to cleanup session
  useEffect(() => {
    const handleBeforeUnload = () => {
      if (isAuthenticated) {
        sessionService.stopSessionMonitoring();
      }
    };

    window.addEventListener('beforeunload', handleBeforeUnload);
    return () => window.removeEventListener('beforeunload', handleBeforeUnload);
  }, [isAuthenticated]);

  return (
    <Router>
      <Routes>
        <Route path="/" element={<HomePage />} />
        <Route path="/login" element={<LoginPage />} />
        <Route path="/register" element={<RegisterPage />} />
        <Route 
          path="/admin" 
          element={
            <ProtectedRoute requiredRole="admin">
              <AdminPage />
            </ProtectedRoute>
          } 
        />
        <Route path="*" element={<ErrorPage />} />
      </Routes>
    </Router>
  );
};

export default App; 