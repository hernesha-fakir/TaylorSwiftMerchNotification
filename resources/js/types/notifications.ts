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

// Stock available notification data
export interface StockAvailableNotificationData {
  product_id: number;
  product_name: string;
  product_price: string;
  product_url: string;
  message: string;
}

// Price changed notification data
export interface PriceChangedNotificationData {
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
