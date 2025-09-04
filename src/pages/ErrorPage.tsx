import React, { useState } from 'react';
import { useRouteError, Link } from 'react-router-dom';
import { Button, Card } from '../components/index';

interface ErrorInfo {
  message?: string;
  stack?: string;
  status?: number;
  statusText?: string;
  data?: any;
}

const ErrorPage: React.FC = () => {
  const error = useRouteError() as ErrorInfo;
  const [showDetails, setShowDetails] = useState(false);
  const [copied, setCopied] = useState(false);

  // Extract error information
  const errorMessage = error?.message || 'An unexpected error occurred';
  const errorStack = error?.stack || 'No stack trace available';
  const errorStatus = error?.status || 500;
  const errorStatusText = error?.statusText || 'Internal Server Error';
  const errorData = error?.data || null;

  // Generate error summary for copying
  const generateErrorSummary = () => {
    const timestamp = new Date().toISOString();
    const userAgent = navigator.userAgent;
    const url = window.location.href;
    
    return `Error Report - ${timestamp}

URL: ${url}
Status: ${errorStatus} ${errorStatusText}
Message: ${errorMessage}

User Agent: ${userAgent}

Stack Trace:
${errorStack}

Additional Data:
${JSON.stringify(errorData, null, 2)}

Environment:
- React Router Error
- Client-side rendering
- Browser: ${navigator.userAgent}
- Timestamp: ${timestamp}`;
  };

  // Copy error summary to clipboard
  const copyErrorSummary = async () => {
    try {
      const summary = generateErrorSummary();
      await navigator.clipboard.writeText(summary);
      setCopied(true);
      setTimeout(() => setCopied(false), 2000);
    } catch (err) {
      // Fallback for older browsers
      const textArea = document.createElement('textarea');
      textArea.value = generateErrorSummary();
      document.body.appendChild(textArea);
      textArea.select();
      document.execCommand('copy');
      document.body.removeChild(textArea);
      setCopied(true);
      setTimeout(() => setCopied(false), 2000);
    }
  };

  // Copy detailed report
  const copyDetailedReport = async () => {
    try {
      const detailedReport = `DETAILED ERROR REPORT

${generateErrorSummary()}

Full Error Object:
${JSON.stringify(error, null, 2)}

Page State:
- Current URL: ${window.location.href}
- Referrer: ${document.referrer}
- Viewport: ${window.innerWidth}x${window.innerHeight}
- Time Zone: ${Intl.DateTimeFormat().resolvedOptions().timeZone}
- Language: ${navigator.language}

Performance Info:
- Navigation Start: ${performance.timing?.navigationStart || 'N/A'}
- Load Event End: ${performance.timing?.loadEventEnd || 'N/A'}
- DOM Content Loaded: ${performance.timing?.domContentLoadedEventEnd || 'N/A'}

Console Errors: (Check browser console for additional details)

Recommendations:
1. Refresh the page and try again
2. Clear browser cache and cookies
3. Check if the issue persists in incognito mode
4. Report this error to support with the copied information above`;
      
      await navigator.clipboard.writeText(detailedReport);
      setCopied(true);
      setTimeout(() => setCopied(false), 2000);
    } catch (err) {
      alert('Failed to copy detailed report. Please manually copy the error information.');
    }
  };

  // Get error type and icon based on status
  const getErrorInfo = (status: number) => {
    switch (status) {
      case 400:
        return { type: 'Bad Request', icon: '⚠️', color: 'text-yellow-600', bg: 'bg-yellow-50' };
      case 401:
        return { type: 'Unauthorized', icon: '🔒', color: 'text-red-600', bg: 'bg-red-50' };
      case 403:
        return { type: 'Forbidden', icon: '🚫', color: 'text-red-600', bg: 'bg-red-50' };
      case 404:
        return { type: 'Not Found', icon: '🔍', color: 'text-blue-600', bg: 'bg-blue-50' };
      case 500:
        return { type: 'Server Error', icon: '💥', color: 'text-red-600', bg: 'bg-red-50' };
      case 502:
        return { type: 'Bad Gateway', icon: '🌐', color: 'text-orange-600', bg: 'bg-orange-50' };
      case 503:
        return { type: 'Service Unavailable', icon: '🔧', color: 'text-orange-600', bg: 'bg-orange-50' };
      default:
        return { type: 'Error', icon: '❌', color: 'text-gray-600', bg: 'bg-gray-50' };
    }
  };

  const errorInfo = getErrorInfo(errorStatus);

  return (
    <div className="min-h-screen bg-gradient-to-br from-red-50 to-orange-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
      <div className="max-w-4xl w-full space-y-8">
        {/* Error Header */}
        <div className="text-center">
          <div className={`mx-auto h-24 w-24 ${errorInfo.bg} rounded-full flex items-center justify-center text-4xl mb-6`}>
            {errorInfo.icon}
          </div>
          <h1 className="text-6xl font-bold text-gray-900 mb-4">
            {errorStatus}
          </h1>
          <h2 className="text-3xl font-semibold text-gray-800 mb-2">
            {errorInfo.type}
          </h2>
          <p className="text-xl text-gray-600 max-w-2xl mx-auto">
            {errorMessage}
          </p>
        </div>

        {/* Action Buttons */}
        <div className="flex justify-center space-x-4">
          <Button onClick={() => window.location.reload()} size="lg">
            🔄 Refresh Page
          </Button>
          <Link 
            to="/" 
            className="inline-flex items-center justify-center font-medium rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 border border-gray-300 text-gray-700 hover:bg-gray-50 focus:ring-green-500 bg-white px-6 py-3 text-base"
          >
            🏠 Go Home
          </Link>
          <Button variant="outline" onClick={() => window.history.back()} size="lg">
            ⬅️ Go Back
          </Button>
        </div>

        {/* Error Details Card */}
        <Card className="p-6">
          <div className="flex items-center justify-between mb-4">
            <h3 className="text-lg font-semibold text-gray-900">Error Details</h3>
            <div className="flex space-x-2">
              <Button
                variant="outline"
                size="sm"
                onClick={copyErrorSummary}
                className={copied ? 'bg-green-100 text-green-800' : ''}
              >
                {copied ? '✅ Copied!' : '📋 Copy Summary'}
              </Button>
              <Button
                variant="outline"
                size="sm"
                onClick={copyDetailedReport}
                className={copied ? 'bg-green-100 text-green-800' : ''}
              >
                {copied ? '✅ Copied!' : '📄 Copy Full Report'}
              </Button>
            </div>
          </div>

          {/* Basic Error Info */}
          <div className="grid md:grid-cols-2 gap-4 mb-6">
            <div className="bg-gray-50 p-4 rounded-lg">
              <h4 className="font-medium text-gray-900 mb-2">Status Code</h4>
              <p className="text-2xl font-bold text-red-600">{errorStatus}</p>
              <p className="text-gray-600">{errorStatusText}</p>
            </div>
            <div className="bg-gray-50 p-4 rounded-lg">
              <h4 className="font-medium text-gray-900 mb-2">Error Type</h4>
              <p className="text-lg font-semibold text-gray-800">{errorInfo.type}</p>
              <p className="text-gray-600">Client-side error</p>
            </div>
          </div>

          {/* Error Message */}
          <div className="mb-6">
            <h4 className="font-medium text-gray-900 mb-2">Error Message</h4>
            <div className="bg-red-50 border border-red-200 rounded-lg p-4">
              <p className="text-red-800 font-mono text-sm break-words">{errorMessage}</p>
            </div>
          </div>

          {/* Toggle Details */}
          <div className="mb-6">
            <Button
              variant="ghost"
              onClick={() => setShowDetails(!showDetails)}
              className="w-full"
            >
              {showDetails ? '🔽 Hide Details' : '🔼 Show Details'}
            </Button>
          </div>

          {/* Detailed Error Information */}
          {showDetails && (
            <div className="space-y-6">
              {/* Stack Trace */}
              <div>
                <h4 className="font-medium text-gray-900 mb-2">Stack Trace</h4>
                <div className="bg-gray-900 text-green-400 p-4 rounded-lg overflow-x-auto">
                  <pre className="text-sm whitespace-pre-wrap break-words">{errorStack}</pre>
                </div>
              </div>

              {/* Additional Data */}
              {errorData && (
                <div>
                  <h4 className="font-medium text-gray-900 mb-2">Additional Data</h4>
                  <div className="bg-gray-100 p-4 rounded-lg overflow-x-auto">
                    <pre className="text-sm whitespace-pre-wrap break-words">
                      {JSON.stringify(errorData, null, 2)}
                    </pre>
                  </div>
                </div>
              )}

              {/* Environment Info */}
              <div>
                <h4 className="font-medium text-gray-900 mb-2">Environment Information</h4>
                <div className="bg-gray-50 p-4 rounded-lg">
                  <div className="grid md:grid-cols-2 gap-4 text-sm">
                    <div>
                      <span className="font-medium">URL:</span>
                      <p className="text-gray-600 break-all">{window.location.href}</p>
                    </div>
                    <div>
                      <span className="font-medium">User Agent:</span>
                      <p className="text-gray-600 text-xs break-all">{navigator.userAgent}</p>
                    </div>
                    <div>
                      <span className="font-medium">Timestamp:</span>
                      <p className="text-gray-600">{new Date().toISOString()}</p>
                    </div>
                    <div>
                      <span className="font-medium">Language:</span>
                      <p className="text-gray-600">{navigator.language}</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          )}
        </Card>

        {/* Help Section */}
        <Card className="p-6 bg-blue-50 border-blue-200">
          <h3 className="text-lg font-semibold text-blue-900 mb-4">Need Help?</h3>
          <div className="grid md:grid-cols-3 gap-4 text-sm">
            <div>
              <h4 className="font-medium text-blue-800 mb-2">🔄 Try These Steps</h4>
              <ul className="text-blue-700 space-y-1">
                <li>• Refresh the page</li>
                <li>• Clear browser cache</li>
                <li>• Try incognito mode</li>
              </ul>
            </div>
            <div>
              <h4 className="font-medium text-blue-800 mb-2">📞 Contact Support</h4>
              <p className="text-blue-700">
                If the problem persists, contact our support team with the error details above.
              </p>
            </div>
            <div>
              <h4 className="font-medium text-blue-800 mb-2">🐛 Report Bug</h4>
              <p className="text-blue-700">
                Help us improve by reporting this issue with the copied error information.
              </p>
            </div>
          </div>
        </Card>
      </div>
    </div>
  );
};

export default ErrorPage; 