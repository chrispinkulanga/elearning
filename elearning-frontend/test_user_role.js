// Test script to check user authentication and role
console.log('Testing user authentication...');

// Check if user is logged in
const token = localStorage.getItem('auth_token');
const user = localStorage.getItem('user');

console.log('Auth Token:', token ? 'Present' : 'Missing');
console.log('User Data:', user ? 'Present' : 'Missing');

if (user) {
  try {
    const userData = JSON.parse(user);
    console.log('User ID:', userData.id);
    console.log('User Name:', userData.name);
    console.log('User Email:', userData.email);
    console.log('User Roles:', userData.roles);
  } catch (e) {
    console.log('Error parsing user data:', e);
  }
}

// Test API call to get user info
if (token) {
  fetch('/api/auth/user', {
    headers: {
      'Authorization': `Bearer ${token}`,
      'Accept': 'application/json'
    }
  })
  .then(response => response.json())
  .then(data => {
    console.log('API User Response:', data);
  })
  .catch(error => {
    console.error('API Error:', error);
  });
}
