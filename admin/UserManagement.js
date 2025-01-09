import React, { useState, useEffect } from 'react';
import { AlertCircle } from 'lucide-react';
import { Alert, AlertTitle, AlertDescription } from '@/components/ui/alert';

const UserManagement = () => {
  const [users, setUsers] = useState([]);
  const [error, setError] = useState(null);
  const [success, setSuccess] = useState(null);

  useEffect(() => {
    fetchUsers();
  }, []);

  const fetchUsers = async () => {
    try {
      const response = await fetch('get_users.php');
      const data = await response.json();
      if (data.success) {
        setUsers(data.users);
      } else {
        setError('Failed to fetch users');
      }
    } catch (err) {
      setError('Error connecting to server');
    }
  };

  const toggleUserStatus = async (userId, currentStatus) => {
    try {
      const response = await fetch('toggle_user_status.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ userId, status: !currentStatus }),
      });
      const data = await response.json();
      
      if (data.success) {
        setSuccess(`User status updated successfully`);
        fetchUsers(); // Refresh user list
        setTimeout(() => setSuccess(null), 3000);
      } else {
        setError(data.message || 'Failed to update user status');
        setTimeout(() => setError(null), 3000);
      }
    } catch (err) {
      setError('Error connecting to server');
      setTimeout(() => setError(null), 3000);
    }
  };

  return (
    <div className="bg-white shadow-md rounded-lg overflow-hidden mb-8">
      <h2 className="px-6 py-4 bg-gray-200 text-gray-700 text-lg font-semibold">User Management</h2>
      
      {error && (
        <Alert variant="destructive" className="mb-4">
          <AlertCircle className="h-4 w-4" />
          <AlertTitle>Error</AlertTitle>
          <AlertDescription>{error}</AlertDescription>
        </Alert>
      )}
      
      {success && (
        <Alert className="mb-4 bg-green-50 text-green-700 border-green-200">
          <AlertTitle>Success</AlertTitle>
          <AlertDescription>{success}</AlertDescription>
        </Alert>
      )}

      <div className="overflow-x-auto">
        <table className="w-full">
          <thead>
            <tr className="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
              <th className="py-3 px-6 text-left">Username</th>
              <th className="py-3 px-6 text-left">Nama Kapster</th>
              <th className="py-3 px-6 text-left">Role</th>
              <th className="py-3 px-6 text-center">Status</th>
              <th className="py-3 px-6 text-center">Actions</th>
            </tr>
          </thead>
          <tbody className="text-gray-600 text-sm">
            {users.map((user) => (
              <tr key={user.user_id} className="border-b border-gray-200 hover:bg-gray-50">
                <td className="py-4 px-6">{user.username}</td>
                <td className="py-4 px-6">{user.nama_kapster}</td>
                <td className="py-4 px-6">{user.role}</td>
                <td className="py-4 px-6 text-center">
                  <span className={`px-3 py-1 rounded-full text-sm font-semibold ${
                    user.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                  }`}>
                    {user.is_active ? 'Active' : 'Inactive'}
                  </span>
                </td>
                <td className="py-4 px-6 text-center">
                  <button
                    onClick={() => toggleUserStatus(user.user_id, user.is_active)}
                    className={`px-4 py-2 rounded-md text-white ${
                      user.is_active 
                        ? 'bg-red-500 hover:bg-red-600' 
                        : 'bg-green-500 hover:bg-green-600'
                    }`}
                    disabled={user.role === 'admin'}
                  >
                    {user.is_active ? 'Deactivate' : 'Activate'}
                  </button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
};

export default UserManagement;