export interface AdminData {
  user_statistics: {
    total_users: number;
    active_users: number;
    inactive_users: number;
    new_users_today: number;
  };
  role_statistics: Array<{
    role_name: string;
    user_count: number;
  }>;
  recent_activity: Array<{
    username: string;
    display_name: string;
    last_login_at: string;
    last_seen_at: string;
    roles: string;
  }>;
  system_info: {
    php_version: string;
    mysql_version: string;
    server_time: string;
    memory_usage: number;
    peak_memory: number;
  };
  version: string;
  status: string;
}

export const adminService = {
  async getAdminData(): Promise<AdminData> {
    const response = await fetch('/api/admin');
    
    if (!response.ok) {
      throw new Error('Failed to fetch admin data');
    }
    
    const data = await response.json();
    
    if (!data.success) {
      throw new Error(data.error || 'Failed to fetch admin data');
    }
    
    return data.data;
  }
}; 