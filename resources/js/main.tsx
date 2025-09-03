import React from 'react';
import ReactDOM from 'react-dom/client';
import App from './App';

// Import Tailwind CSS
import './styles/tailwind.css';
// Import App CSS for accessibility features
import './styles/App.css';

console.log('🚀 React app is starting...');

// Create root and render app
const root = ReactDOM.createRoot(
  document.getElementById('root') as HTMLElement
);

console.log('🎯 Root element found:', root);

root.render(
  <React.StrictMode>
    <App />
  </React.StrictMode>
);

console.log('✅ React app rendered!'); 