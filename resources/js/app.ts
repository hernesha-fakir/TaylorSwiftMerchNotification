import './bootstrap';
import { NotificationDropdown } from '@/components/NotificationDropdown';

// Initialize notification dropdown when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
  // Initialize main notification dropdown
  const notificationContainer = document.getElementById('notification-dropdown');
  if (notificationContainer) {
    try {
      new NotificationDropdown('notification-dropdown');
    } catch (error) {
      console.error('Error initializing notification dropdown:', error);
    }
  }

  // Initialize manual test dropdown (if exists on test page)
  const manualTestContainer = document.getElementById('manual-test-notification-dropdown');
  if (manualTestContainer) {
    try {
      new NotificationDropdown('manual-test-notification-dropdown');
    } catch (error) {
      console.error('Error initializing test dropdown:', error);
    }
  }
});
