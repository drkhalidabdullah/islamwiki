import React from 'react';
import { Link } from 'react-router-dom';

const HomePage: React.FC = () => {
  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      {/* Hero Section */}
      <div className="text-center mb-12">
        <h1 className="text-4xl font-bold text-gray-900 mb-4">
          Welcome to IslamWiki
        </h1>
        <p className="text-xl text-gray-600 mb-8 max-w-3xl mx-auto">
          Discover authentic Islamic knowledge, connect with scholars, and deepen your understanding of Islam through our comprehensive platform.
        </p>
        <div className="flex flex-col sm:flex-row gap-4 justify-center">
          <Link
            to="/categories"
            className="bg-green-600 text-white px-6 py-3 rounded-lg text-lg font-medium hover:bg-green-700 transition-colors"
          >
            Browse Articles
          </Link>
          <Link
            to="/register"
            className="border-2 border-green-600 text-green-600 px-6 py-3 rounded-lg text-lg font-medium hover:bg-green-50 transition-colors"
          >
            Join Community
          </Link>
        </div>
      </div>

      {/* Features Grid */}
      <div className="grid md:grid-cols-3 gap-8 mb-12">
        <div className="text-center p-6 bg-white rounded-lg shadow-sm border border-gray-200">
          <div className="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg className="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 5.477 5.754 5 7.5 5s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.523 18.246 19 16.5 19c-1.746 0-3.332-.477-4.5-1.253" />
            </svg>
          </div>
          <h3 className="text-xl font-semibold text-gray-900 mb-2">Islamic Knowledge</h3>
          <p className="text-gray-600">
            Access authentic Islamic articles, hadith, and scholarly works from verified sources.
          </p>
        </div>

        <div className="text-center p-6 bg-white rounded-lg shadow-sm border border-gray-200">
          <div className="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg className="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
          </div>
          <h3 className="text-xl font-semibold text-gray-900 mb-2">Community</h3>
          <p className="text-gray-600">
            Connect with fellow Muslims, ask questions, and share knowledge in a supportive environment.
          </p>
        </div>

        <div className="text-center p-6 bg-white rounded-lg shadow-sm border border-gray-200">
          <div className="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg className="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
            </svg>
          </div>
          <h3 className="text-xl font-semibold text-gray-900 mb-2">Learning</h3>
          <p className="text-gray-600">
            Structured courses, tutorials, and resources to help you learn and grow in your Islamic journey.
          </p>
        </div>
      </div>

      {/* Recent Articles Section */}
      <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h2 className="text-2xl font-bold text-gray-900 mb-6">Recent Articles</h2>
        <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
          {/* Placeholder articles - these would be populated from API */}
          <div className="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
            <h3 className="text-lg font-semibold text-gray-900 mb-2">
              Understanding the Five Pillars of Islam
            </h3>
            <p className="text-gray-600 text-sm mb-3">
              A comprehensive guide to the fundamental principles that form the foundation of Islamic faith and practice.
            </p>
            <div className="flex items-center justify-between text-sm text-gray-500">
              <span>By Scholar Ahmed</span>
              <span>2 days ago</span>
            </div>
          </div>

          <div className="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
            <h3 className="text-lg font-semibold text-gray-900 mb-2">
              The Importance of Prayer in Daily Life
            </h3>
            <p className="text-gray-600 text-sm mb-3">
              Exploring how regular prayer strengthens our connection with Allah and brings peace to our daily routine.
            </p>
            <div className="flex items-center justify-between text-sm text-gray-500">
              <span>By Scholar Fatima</span>
              <span>1 week ago</span>
            </div>
          </div>

          <div className="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
            <h3 className="text-lg font-semibold text-gray-900 mb-2">
              Islamic Ethics in Modern Business
            </h3>
            <p className="text-gray-600 text-sm mb-3">
              How Islamic principles guide ethical business practices and financial transactions in today's world.
            </p>
            <div className="flex items-center justify-between text-sm text-gray-500">
              <span>By Scholar Omar</span>
              <span>2 weeks ago</span>
            </div>
          </div>
        </div>
        
        <div className="text-center mt-6">
          <Link
            to="/articles"
            className="text-green-600 hover:text-green-700 font-medium"
          >
            View All Articles →
          </Link>
        </div>
      </div>

      {/* Call to Action */}
      <div className="text-center mt-12 p-8 bg-gradient-to-r from-green-600 to-green-700 rounded-lg text-white">
        <h2 className="text-2xl font-bold mb-4">Ready to Start Learning?</h2>
        <p className="text-lg mb-6 opacity-90">
          Join thousands of Muslims who are already benefiting from our platform.
        </p>
        <Link
          to="/register"
          className="bg-white text-green-600 px-8 py-3 rounded-lg text-lg font-medium hover:bg-gray-100 transition-colors"
        >
          Get Started Today
        </Link>
      </div>
    </div>
  );
};

export default HomePage; 