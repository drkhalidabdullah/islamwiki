import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { useAuthStore } from '../store/authStore';
import Button from '../components/ui/Button';
import Input from '../components/ui/Input';
import Textarea from '../components/ui/Textarea';
import Card from '../components/ui/Card';

const UserProfilePage: React.FC = () => {
  const { username } = useParams<{ username: string }>();
  const navigate = useNavigate();
  const { user, isAuthenticated } = useAuthStore();
  const [isEditing, setIsEditing] = useState(false);
  const [profileUser, setProfileUser] = useState(user);
  const [isOwnProfile, setIsOwnProfile] = useState(false);
  const [formData, setFormData] = useState({
    first_name: user?.first_name || '',
    last_name: user?.last_name || '',
    bio: '', // Will be implemented when User interface is extended
    location: '', // Will be implemented when User interface is extended
    website: '' // Will be implemented when User interface is extended
  });
  const [errors, setErrors] = useState<Record<string, string>>({});
  const [isLoading, setIsLoading] = useState(false);

  // Determine if this is the current user's profile or another user's profile
  useEffect(() => {
    if (username) {
      // If username is provided in URL, check if it's the current user
      if (username === user?.username) {
        setProfileUser(user);
        setIsOwnProfile(true);
        // Update form data with current user's data
        setFormData({
          first_name: user?.first_name || '',
          last_name: user?.last_name || '',
          bio: '', // Will be implemented when User interface is extended
          location: '', // Will be implemented when User interface is extended
          website: '' // Will be implemented when User interface is extended
        });
      } else {
        // TODO: Fetch other user's profile data
        // For now, we'll redirect to the current user's profile
        // In a real implementation, you would fetch the other user's data
        navigate(`/user/${user?.username}`);
        return;
      }
    } else {
      // No username in URL, this is the current user's profile
      setProfileUser(user);
      setIsOwnProfile(true);
      // Update form data with current user's data
      setFormData({
        first_name: user?.first_name || '',
        last_name: user?.last_name || '',
        bio: '', // Will be implemented when User interface is extended
        location: '', // Will be implemented when User interface is extended
        website: '' // Will be implemented when User interface is extended
      });
    }
  }, [username, user, navigate]);

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
    const { name, value } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: value
    }));
    
    // Clear error when user starts typing
    if (errors[name]) {
      setErrors(prev => ({
        ...prev,
        [name]: ''
      }));
    }
  };

  const validateForm = () => {
    const newErrors: Record<string, string> = {};
    
    if (!formData.first_name.trim()) {
      newErrors.first_name = 'First name is required';
    }
    
    if (!formData.last_name.trim()) {
      newErrors.last_name = 'Last name is required';
    }
    
    if (formData.website && !isValidUrl(formData.website)) {
      newErrors.website = 'Please enter a valid URL';
    }
    
    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const isValidUrl = (string: string) => {
    try {
      new URL(string);
      return true;
    } catch (_) {
      return false;
    }
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    
    if (!validateForm()) {
      return;
    }
    
    setIsLoading(true);
    
    try {
      // TODO: Implement actual profile update API call
      console.log('Profile update data:', formData);
      
      // Simulate API call delay
      await new Promise(resolve => setTimeout(resolve, 1000));
      
      // TODO: Handle successful profile update
      console.log('Profile updated successfully!');
      setIsEditing(false);
      
    } catch (error) {
      if (error instanceof Error) {
        setErrors({ general: error.message });
      } else {
        setErrors({ general: 'An unexpected error occurred' });
      }
    } finally {
      setIsLoading(false);
    }
  };

  const handleCancel = () => {
    // Reset form data to original values
    setFormData({
      first_name: user?.first_name || '',
      last_name: user?.last_name || '',
      bio: '', // Will be implemented when User interface is extended
      location: '', // Will be implemented when User interface is extended
      website: '' // Will be implemented when User interface is extended
    });
    setErrors({});
    setIsEditing(false);
  };

  if (!isAuthenticated || !user) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="text-center">
          <h1 className="text-2xl font-bold text-gray-900 mb-4">Access Denied</h1>
          <p className="text-gray-600">Please log in to view your profile.</p>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-50 py-8">
      <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="mb-8">
          <h1 className="text-3xl font-bold text-gray-900">User Profile</h1>
          <p className="text-gray-600 mt-2">Manage your account information and preferences</p>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
          {/* Profile Summary */}
          <div className="lg:col-span-1">
            <Card className="p-6">
              <div className="text-center">
                <div className="w-24 h-24 mx-auto mb-4 bg-gray-200 rounded-full flex items-center justify-center">
                  <span className="text-2xl font-bold text-gray-600">
                    {profileUser?.first_name?.[0]}{profileUser?.last_name?.[0]}
                  </span>
                </div>
                <h2 className="text-xl font-semibold text-gray-900">
                  {profileUser?.first_name} {profileUser?.last_name}
                </h2>
                <p className="text-gray-600">@{profileUser?.username}</p>
                <p className="text-sm text-gray-500 mt-1">{profileUser?.email}</p>
                
                {!isEditing && isOwnProfile && (
                  <Button
                    variant="primary"
                    size="md"
                    onClick={() => setIsEditing(true)}
                    className="mt-4 w-full"
                  >
                    Edit Profile
                  </Button>
                )}
              </div>
            </Card>
          </div>

          {/* Profile Details */}
          <div className="lg:col-span-2">
            <Card className="p-6">
              <h3 className="text-lg font-semibold text-gray-900 mb-6">
                Profile Information
              </h3>

              {errors.general && (
                <div className="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded">
                  {errors.general}
                </div>
              )}

              {isEditing ? (
                <form onSubmit={handleSubmit} className="space-y-4">
                  <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <Input
                      label="First Name"
                      name="first_name"
                      value={formData.first_name}
                      onChange={handleChange}
                      error={errors.first_name}
                      required
                    />
                    
                    <Input
                      label="Last Name"
                      name="last_name"
                      value={formData.last_name}
                      onChange={handleChange}
                      error={errors.last_name}
                      required
                    />
                  </div>

                  <Textarea
                    label="Bio"
                    name="bio"
                    value={formData.bio}
                    onChange={handleChange}
                    placeholder="Tell us about yourself... (Coming soon)"
                    rows={4}
                    disabled
                  />

                  <Input
                    label="Location"
                    name="location"
                    value={formData.location}
                    onChange={handleChange}
                    placeholder="City, Country (Coming soon)"
                    disabled
                  />

                  <Input
                    label="Website"
                    name="website"
                    value={formData.website}
                    onChange={handleChange}
                    error={errors.website}
                    placeholder="https://example.com (Coming soon)"
                    disabled
                  />

                  <div className="flex space-x-4 pt-4">
                    <Button
                      type="submit"
                      variant="primary"
                      loading={isLoading}
                    >
                      {isLoading ? 'Saving...' : 'Save Changes'}
                    </Button>
                    
                    <Button
                      type="button"
                      variant="secondary"
                      onClick={handleCancel}
                    >
                      Cancel
                    </Button>
                  </div>
                </form>
              ) : (
                <div className="space-y-4">
                  <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <label className="block text-sm font-medium text-gray-700 mb-1">
                        First Name
                      </label>
                      <p className="text-gray-900">{profileUser?.first_name || 'Not specified'}</p>
                    </div>
                    
                    <div>
                      <label className="block text-sm font-medium text-gray-700 mb-1">
                        Last Name
                      </label>
                      <p className="text-gray-900">{profileUser?.last_name || 'Not specified'}</p>
                    </div>
                  </div>

                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">
                      Bio
                    </label>
                    <p className="text-gray-900 text-gray-500 italic">Coming soon</p>
                  </div>

                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">
                      Location
                    </label>
                    <p className="text-gray-900 text-gray-500 italic">Coming soon</p>
                  </div>

                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">
                      Website
                    </label>
                    <p className="text-gray-900 text-gray-500 italic">Coming soon</p>
                  </div>

                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">
                      Member Since
                    </label>
                    <p className="text-gray-900">
                      {profileUser?.created_at ? new Date(profileUser.created_at).toLocaleDateString() : 'Unknown'}
                    </p>
                  </div>
                </div>
              )}
            </Card>
          </div>
        </div>
      </div>
    </div>
  );
};

export default UserProfilePage; 