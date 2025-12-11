import { z } from 'zod';

// Base notification structure from Laravel
export interface LaravelNotification<T = unknown> {
  id: string;
  type: string;
  notifiable_type: string;
  notifiable_id: number;
  data: T;
  read_at: string | null;
  created_at: string;
  updated_at: string;
}

// Stock available notification data (discriminated union with 'type' field)
export interface StockAvailableNotificationData {
  type: 'stock_available'; // Discriminant field
  product_id: number;
  product_name: string;
  product_price: number; // Changed from string to number for consistency
  product_url: string;
  message: string;
}

// Price changed notification data (discriminated union with 'type' field)
export interface PriceChangedNotificationData {
  type: 'price_changed'; // Discriminant field
  product_id: number;
  product_name: string;
  product_url: string;
  old_price: number;
  new_price: number;
  price_difference: number;
  price_change_type: 'increase' | 'decrease';
  message: string;
}

// Union type for all notification data types
export type NotificationData =
  | StockAvailableNotificationData
  | PriceChangedNotificationData;

// Typed notification types
export type StockAvailableNotification = LaravelNotification<StockAvailableNotificationData>;
export type PriceChangedNotification = LaravelNotification<PriceChangedNotificationData>;
export type AppNotification = LaravelNotification<NotificationData>;

// API response types (Laravel's default paginator format)
export interface NotificationsResponse {
  current_page: number;
  data: AppNotification[];
  first_page_url: string;
  from: number | null;
  last_page: number;
  last_page_url: string;
  links: Array<{
    url: string | null;
    label: string;
    active: boolean;
  }>;
  next_page_url: string | null;
  path: string;
  per_page: number;
  prev_page_url: string | null;
  to: number | null;
  total: number;
}

export interface MarkNotificationReadResponse {
  success: boolean;
  message?: string;
}

// ============================================================================
// Zod Schemas for Runtime Validation
// ============================================================================

// Stock available notification schema (with discriminant)
export const StockAvailableNotificationDataSchema = z.object({
  type: z.literal('stock_available'), // Discriminant
  product_id: z.number().positive(),
  product_name: z.string().min(1),
  product_price: z.number().nonnegative(),
  product_url: z.string().url(),
  message: z.string().min(1),
});

// Price changed notification schema (with discriminant)
export const PriceChangedNotificationDataSchema = z.object({
  type: z.literal('price_changed'), // Discriminant
  product_id: z.number().positive(),
  product_name: z.string().min(1),
  product_url: z.string().url(),
  old_price: z.number().nonnegative(),
  new_price: z.number().nonnegative(),
  price_difference: z.number(),
  price_change_type: z.enum(['increase', 'decrease']),
  message: z.string().min(1),
});

// Discriminated union schema for all notification data types
// Using discriminatedUnion provides better error messages and performance
export const NotificationDataSchema = z.discriminatedUnion('type', [
  StockAvailableNotificationDataSchema,
  PriceChangedNotificationDataSchema,
]);

// Base Laravel notification schema
export const LaravelNotificationSchema = z.object({
  id: z.string(),
  type: z.string(),
  notifiable_type: z.string(),
  notifiable_id: z.number(),
  data: NotificationDataSchema,
  read_at: z.string().nullable(),
  created_at: z.string(),
  updated_at: z.string(),
});

// API response schema (Laravel paginator)
export const NotificationsResponseSchema = z.object({
  current_page: z.number(),
  data: z.array(LaravelNotificationSchema),
  first_page_url: z.string(),
  from: z.number().nullable(),
  last_page: z.number(),
  last_page_url: z.string(),
  links: z.array(
    z.object({
      url: z.string().nullable(),
      label: z.string(),
      active: z.boolean(),
    })
  ),
  next_page_url: z.string().nullable(),
  path: z.string(),
  per_page: z.number(),
  prev_page_url: z.string().nullable(),
  to: z.number().nullable(),
  total: z.number(),
});

// Mark as read response schema
export const MarkNotificationReadResponseSchema = z.object({
  success: z.boolean(),
  message: z.string().optional(),
});
