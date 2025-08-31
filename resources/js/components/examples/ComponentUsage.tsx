import React, { useState } from 'react';
import { Button, Input, Card, Modal, LoginForm } from '../index';

/**
 * Component Usage Examples
 * 
 * This file demonstrates how to use the IslamWiki component library
 * for building user interfaces.
 */

const ComponentUsage: React.FC = () => {
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [formData, setFormData] = useState({ name: '', email: '' });

  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    setFormData({
      ...formData,
      [e.target.name]: e.target.value
    });
  };

  return (
    <div className="max-w-4xl mx-auto p-6 space-y-8">
      <h1 className="text-3xl font-bold text-gray-900">Component Library Examples</h1>
      
      {/* Button Examples */}
      <Card>
        <h2 className="text-xl font-semibold mb-4">Button Components</h2>
        <div className="flex flex-wrap gap-4">
          <Button variant="primary">Primary Button</Button>
          <Button variant="secondary">Secondary Button</Button>
          <Button variant="outline">Outline Button</Button>
          <Button variant="ghost">Ghost Button</Button>
          <Button variant="danger">Danger Button</Button>
          <Button loading>Loading Button</Button>
        </div>
      </Card>

      {/* Input Examples */}
      <Card>
        <h2 className="text-xl font-semibold mb-4">Input Components</h2>
        <div className="space-y-4">
          <Input
            label="Name"
            name="name"
            value={formData.name}
            onChange={handleInputChange}
            placeholder="Enter your name"
          />
          <Input
            label="Email"
            name="email"
            type="email"
            value={formData.email}
            onChange={handleInputChange}
            placeholder="Enter your email"
            error="Please enter a valid email address"
          />
          <Input
            label="Success Input"
            variant="success"
            placeholder="This is a success state"
          />
        </div>
      </Card>

      {/* Modal Example */}
      <Card>
        <h2 className="text-xl font-semibold mb-4">Modal Component</h2>
        <Button onClick={() => setIsModalOpen(true)}>
          Open Modal
        </Button>
        
        <Modal
          isOpen={isModalOpen}
          onClose={() => setIsModalOpen(false)}
          title="Example Modal"
        >
          <p className="text-gray-600">
            This is an example modal component. You can put any content here.
          </p>
          <div className="mt-4 flex justify-end space-x-2">
            <Button variant="outline" onClick={() => setIsModalOpen(false)}>
              Cancel
            </Button>
            <Button onClick={() => setIsModalOpen(false)}>
              Confirm
            </Button>
          </div>
        </Modal>
      </Card>

      {/* Login Form Example */}
      <Card>
        <h2 className="text-xl font-semibold mb-4">Login Form Component</h2>
        <LoginForm
          onSuccess={() => console.log('Login successful')}
          onSwitchToRegister={() => console.log('Switch to register')}
        />
      </Card>

      {/* Card Variations */}
      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        <Card shadow="sm" hover>
          <h3 className="text-lg font-semibold mb-2">Small Shadow Card</h3>
          <p className="text-gray-600">This card has a small shadow and hover effect.</p>
        </Card>
        
        <Card shadow="lg" padding="lg">
          <h3 className="text-lg font-semibold mb-2">Large Shadow Card</h3>
          <p className="text-gray-600">This card has a large shadow and extra padding.</p>
        </Card>
      </div>
    </div>
  );
};

export default ComponentUsage; 