import React from 'react';
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import HomePage from './pages/HomePage';
import AdminPage from './pages/AdminPage';
import TestsPage from './pages/TestsPage';

function App() {
  console.log('App component is rendering!');
  
  return (
    <Router>
      <div className="min-h-screen bg-gray-50">
        <Routes>
          <Route path="/" element={<HomePage />} />
          <Route path="/admin" element={<AdminPage />} />
          <Route path="/tests" element={<TestsPage />} />
        </Routes>
      </div>
    </Router>
  );
}

export default App; 