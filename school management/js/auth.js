/**
 * Sky Hawk School Management System
 * Authentication & API Helper
 */

const API_BASE_URL = 'http://127.0.0.1:8000/api';

// Authentication Storage Helpers
function getToken() {
  return localStorage.getItem('skyhawk_token');
}

function getUser() {
  const data = localStorage.getItem('skyhawk_user');
  try {
    return data ? JSON.parse(data) : null;
  } catch (e) {
    return null;
  }
}

function setAuth(token, user) {
  localStorage.setItem('skyhawk_token', token);
  localStorage.setItem('skyhawk_user', JSON.stringify(user));
}

function clearAuth() {
  localStorage.removeItem('skyhawk_token');
  localStorage.removeItem('skyhawk_user');
}

function isAuthenticated() {
  return !!getToken();
}

/**
 * Redirect user if they are not authorized for this page
 */
function protectPage(requiredRole = null) {
  if (!isAuthenticated()) {
    window.location.href = 'login.html';
    return false;
  }

  const user = getUser();
  if (requiredRole && user && user.role !== requiredRole) {
    // Redirect to their own dashboard
    if (user.role === 'admin') {
      window.location.href = 'admin-dashboard.html';
    } else {
      window.location.href = 'student-dashboard.html';
    }
    return false;
  }

  return true;
}

/**
 * If user is already logged in, redirect them from login/register to dashboard
 */
function redirectIfLoggedIn() {
  if (isAuthenticated()) {
    const user = getUser();
    if (user && user.role === 'admin') {
      window.location.href = 'admin-dashboard.html';
    } else {
      window.location.href = 'student-dashboard.html';
    }
  }
}

/**
 * Logout user from backend and local storage
 */
async function logoutUser() {
  const token = getToken();
  if (token) {
    try {
      await fetch(`${API_BASE_URL}/logout`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'Authorization': `Bearer ${token}`
        }
      });
    } catch (err) {
      console.warn('Logout API request error:', err);
    }
  }

  clearAuth();
  window.location.href = 'login.html';
}

/**
 * Standard API request wrapper with auth header
 */
async function apiRequest(endpoint, method = 'GET', body = null) {
  const token = getToken();
  const headers = {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  };

  if (token) {
    headers['Authorization'] = `Bearer ${token}`;
  }

  const config = {
    method,
    headers,
  };

  if (body) {
    config.body = JSON.stringify(body);
  }

  try {
    const response = await fetch(`${API_BASE_URL}${endpoint}`, config);
    const data = await response.json();
    return { ok: response.ok, status: response.status, data };
  } catch (error) {
    console.error('API Error:', error);
    return {
      ok: false,
      status: 0,
      data: {
        status: false,
        message: 'Could not connect to the backend server. Please make sure the Laravel API server is running at ' + API_BASE_URL
      }
    };
  }
}
