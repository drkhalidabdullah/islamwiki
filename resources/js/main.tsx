import React from 'react';
import ReactDOM from 'react-dom/client';
import App from './App';

// Import Tailwind CSS
import './styles/tailwind.css';

// Create root and render app
const root = ReactDOM.createRoot(
  document.getElementById('root') as HTMLElement
);

root.render(
  <React.StrictMode>
    <App />
  </React.StrictMode>
); 