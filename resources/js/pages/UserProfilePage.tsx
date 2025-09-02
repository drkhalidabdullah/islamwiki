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
    bio: user?.bio || '',
    location: user?.location || '',
    website: user?.website || '',
    date_of_birth: user?.date_of_birth || '',
    gender: user?.gender || '',
    phone: user?.phone || '',
    social_links: {
      twitter: user?.social_links?.twitter || '',
      facebook: user?.social_links?.facebook || '',
      instagram: user?.social_links?.instagram || '',
      linkedin: user?.social_links?.linkedin || ''
    },
    preferences: {
      email_notifications: user?.preferences?.email_notifications !== false,
      push_notifications: user?.preferences?.push_notifications || false,
      profile_public: user?.preferences?.profile_public !== false,
      show_email: user?.preferences?.show_email || false,
      show_last_seen: user?.preferences?.show_last_seen !== false
    }
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
          bio: user?.bio || '',
          location: user?.location || '',
          website: user?.website || '',
          date_of_birth: user?.date_of_birth || '',
          gender: user?.gender || '',
          phone: user?.phone || '',
          social_links: {
            twitter: user?.social_links?.twitter || '',
            facebook: user?.social_links?.facebook || '',
            instagram: user?.social_links?.instagram || '',
            linkedin: user?.social_links?.linkedin || ''
          },
          preferences: {
            email_notifications: user?.preferences?.email_notifications !== false,
            push_notifications: user?.preferences?.push_notifications || false,
            profile_public: user?.preferences?.profile_public !== false,
            show_email: user?.preferences?.show_email || false,
            show_last_seen: user?.preferences?.show_last_seen !== false
          }
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
          bio: user?.bio || '',
          location: user?.location || '',
          website: user?.website || '',
          date_of_birth: user?.date_of_birth || '',
          gender: user?.gender || '',
          phone: user?.phone || '',
          social_links: {
            twitter: user?.social_links?.twitter || '',
            facebook: user?.social_links?.facebook || '',
            instagram: user?.social_links?.instagram || '',
            linkedin: user?.social_links?.linkedin || ''
          },
          preferences: {
            email_notifications: user?.preferences?.email_notifications !== false,
            push_notifications: user?.preferences?.push_notifications || false,
            profile_public: user?.preferences?.profile_public !== false,
            show_email: user?.preferences?.show_email || false,
            show_last_seen: user?.preferences?.show_last_seen !== false
          }
      });
    }
  }, [username, user, navigate]);

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>) => {
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
      bio: user?.bio || '',
      location: user?.location || '',
      website: user?.website || '',
      date_of_birth: user?.date_of_birth || '',
      gender: user?.gender || '',
      phone: user?.phone || '',
      social_links: {
        twitter: user?.social_links?.twitter || '',
        facebook: user?.social_links?.facebook || '',
        instagram: user?.social_links?.instagram || '',
        linkedin: user?.social_links?.linkedin || ''
      },
      preferences: {
        email_notifications: user?.preferences?.email_notifications !== false,
        push_notifications: user?.preferences?.push_notifications || false,
        profile_public: user?.preferences?.profile_public !== false,
        show_email: user?.preferences?.show_email || false,
        show_last_seen: user?.preferences?.show_last_seen !== false
      }
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
                    placeholder="Tell us about yourself..."
                    rows={4}
                  />

                  <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <Input
                    label="Location"
                    name="location"
                    value={formData.location}
                    onChange={handleChange}
                      placeholder="City, Country"
                  />

                  <Input
                    label="Website"
                    name="website"
                    value={formData.website}
                    onChange={handleChange}
                    error={errors.website}
                      placeholder="https://example.com"
                    />
                  </div>

                  <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <Input
                      label="Date of Birth"
                      name="date_of_birth"
                      type="date"
                      value={formData.date_of_birth}
                      onChange={handleChange}
                    />
                    
                    <div>
                      <label className="block text-sm font-medium text-gray-700 mb-2">Gender</label>
                      <select
                        name="gender"
                        value={formData.gender}
                        onChange={handleChange}
                        className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                      >
                        <option value="">Select gender</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                        <option value="prefer_not_to_say">Prefer not to say</option>
                      </select>
                    </div>
                  </div>

                  <Input
                    label="Phone"
                    name="phone"
                    value={formData.phone}
                    onChange={handleChange}
                    placeholder="+1 (555) 123-4567"
                  />

                  <div>
                    <h4 className="text-md font-medium text-gray-900 mb-3">Social Links</h4>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                      <Input
                        label="Twitter"
                        name="social_links.twitter"
                        value={formData.social_links.twitter}
                        onChange={(e) => setFormData(prev => ({
                          ...prev,
                          social_links: { ...prev.social_links, twitter: e.target.value }
                        }))}
                        placeholder="@username"
                      />
                      <Input
                        label="Facebook"
                        name="social_links.facebook"
                        value={formData.social_links.facebook}
                        onChange={(e) => setFormData(prev => ({
                          ...prev,
                          social_links: { ...prev.social_links, facebook: e.target.value }
                        }))}
                        placeholder="facebook.com/username"
                      />
                      <Input
                        label="Instagram"
                        name="social_links.instagram"
                        value={formData.social_links.instagram}
                        onChange={(e) => setFormData(prev => ({
                          ...prev,
                          social_links: { ...prev.social_links, instagram: e.target.value }
                        }))}
                        placeholder="@username"
                      />
                      <Input
                        label="LinkedIn"
                        name="social_links.linkedin"
                        value={formData.social_links.linkedin}
                        placeholder="linkedin.com/in/username"
                        onChange={(e) => setFormData(prev => ({
                          ...prev,
                          social_links: { ...prev.social_links, linkedin: e.target.value }
                        }))}
                      />
                    </div>
                  </div>

                  <div>
                    <h4 className="text-md font-medium text-gray-900 mb-3">Privacy & Preferences</h4>
                    <div className="space-y-4">
                      <div className="flex items-center justify-between">
                        <div>
                          <h5 className="text-sm font-medium text-gray-900">Email Notifications</h5>
                          <p className="text-sm text-gray-500">Receive email notifications about your account</p>
                        </div>
                        <label className="relative inline-flex items-center cursor-pointer">
                          <input
                            type="checkbox"
                            checked={formData.preferences.email_notifications}
                            onChange={(e) => setFormData(prev => ({
                              ...prev,
                              preferences: { ...prev.preferences, email_notifications: e.target.checked }
                            }))}
                            className="sr-only peer"
                          />
                          <div className="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                        </label>
                      </div>
                      
                      <div className="flex items-center justify-between">
                        <div>
                          <h5 className="text-sm font-medium text-gray-900">Push Notifications</h5>
                          <p className="text-sm text-gray-500">Receive push notifications in your browser</p>
                        </div>
                        <label className="relative inline-flex items-center cursor-pointer">
                          <input
                            type="checkbox"
                            checked={formData.preferences.push_notifications}
                            onChange={(e) => setFormData(prev => ({
                              ...prev,
                              preferences: { ...prev.preferences, push_notifications: e.target.checked }
                            }))}
                            className="sr-only peer"
                          />
                          <div className="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                        </label>
                      </div>
                      
                      <div className="flex items-center justify-between">
                        <div>
                          <h5 className="text-sm font-medium text-gray-900">Public Profile</h5>
                          <p className="text-sm text-gray-500">Allow others to view your profile</p>
                        </div>
                        <label className="relative inline-flex items-center cursor-pointer">
                          <input
                            type="checkbox"
                            checked={formData.preferences.profile_public}
                            onChange={(e) => setFormData(prev => ({
                              ...prev,
                              preferences: { ...prev.preferences, profile_public: e.target.checked }
                            }))}
                            className="sr-only peer"
                          />
                          <div className="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                        </label>
                      </div>
                      
                      <div className="flex items-center justify-between">
                        <div>
                          <h5 className="text-sm font-medium text-gray-900">Show Email</h5>
                          <p className="text-sm text-gray-500">Display your email address on your profile</p>
                        </div>
                        <label className="relative inline-flex items-center cursor-pointer">
                          <input
                            type="checkbox"
                            checked={formData.preferences.show_email}
                            onChange={(e) => setFormData(prev => ({
                              ...prev,
                              preferences: { ...prev.preferences, show_email: e.target.checked }
                            }))}
                            className="sr-only peer"
                          />
                          <div className="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                        </label>
                      </div>
                      
                      <div className="flex items-center justify-between">
                        <div>
                          <h5 className="text-sm font-medium text-gray-900">Show Last Seen</h5>
                          <p className="text-sm text-gray-500">Display when you were last active</p>
                        </div>
                        <label className="relative inline-flex items-center cursor-pointer">
                          <input
                            type="checkbox"
                            checked={formData.preferences.show_last_seen}
                            onChange={(e) => setFormData(prev => ({
                              ...prev,
                              preferences: { ...prev.preferences, show_last_seen: e.target.checked }
                            }))}
                            className="sr-only peer"
                          />
                          <div className="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                        </label>
                      </div>
                    </div>
                  </div>

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