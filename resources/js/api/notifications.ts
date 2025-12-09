import type {
  NotificationsResponse,
  MarkNotificationReadResponse,
} from '@/types/notifications';

/**
 * Fetch paginated notifications for the authenticated user
 */
export async function fetchNotifications(
  page: number = 1
): Promise<NotificationsResponse> {
  const response = await window.axios.get<NotificationsResponse>(
    `/api/notifications?page=${page}`
  );
  return response.data;
}

/**
 * Mark a notification as read
 */
export async function markNotificationAsRead(
  notificationId: string
): Promise<MarkNotificationReadResponse> {
  const response = await window.axios.post<MarkNotificationReadResponse>(
    `/api/notifications/${notificationId}/read`
  );
  return response.data;
}
