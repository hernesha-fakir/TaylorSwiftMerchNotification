import type {
  NotificationsResponse,
  MarkNotificationReadResponse,
} from '@/types/notifications';
import {
  NotificationsResponseSchema,
  MarkNotificationReadResponseSchema,
} from '@/types/notifications';
import { ZodError } from 'zod';

/**
 * Custom error class for API validation failures
 */
export class ApiValidationError extends Error {
  constructor(
    message: string,
    public readonly validationErrors: ZodError
  ) {
    super(message);
    this.name = 'ApiValidationError';
  }
}

/**
 * Fetch paginated notifications for the authenticated user
 * @throws {ApiValidationError} If the API response doesn't match expected schema
 */
export async function fetchNotifications(
  page: number = 1
): Promise<NotificationsResponse> {
  const response = await window.axios.get(
    `/api/notifications?page=${page}`
  );

  // Validate response at runtime
  const validation = NotificationsResponseSchema.safeParse(response.data);

  if (!validation.success) {
    console.error('API validation failed:', validation.error);
    throw new ApiValidationError(
      'Invalid notifications response from server',
      validation.error
    );
  }

  return validation.data;
}

/**
 * Mark a notification as read
 * @throws {ApiValidationError} If the API response doesn't match expected schema
 */
export async function markNotificationAsRead(
  notificationId: string
): Promise<MarkNotificationReadResponse> {
  const response = await window.axios.post(
    `/api/notifications/${notificationId}/read`
  );

  // Validate response at runtime
  const validation = MarkNotificationReadResponseSchema.safeParse(response.data);

  if (!validation.success) {
    console.error('API validation failed:', validation.error);
    throw new ApiValidationError(
      'Invalid mark-as-read response from server',
      validation.error
    );
  }

  return validation.data;
}
