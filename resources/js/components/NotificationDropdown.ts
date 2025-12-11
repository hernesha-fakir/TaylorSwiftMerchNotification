import { fetchNotifications, markNotificationAsRead } from '@/api/notifications';
import type {
  AppNotification,
  LaravelNotification,
  StockAvailableNotificationData,
  PriceChangedNotificationData,
} from '@/types/notifications';

/**
 * Notification Dropdown Component
 * A vanilla TypeScript component for displaying user notifications
 */
export class NotificationDropdown {
  private container: HTMLElement;
  private notifications: AppNotification[] = [];
  private isOpen: boolean = false;
  private isLoading: boolean = false;
  private error: Error | null = null;
  private outsideClickHandler: (event: MouseEvent) => void;
  private containerClickHandler: (event: MouseEvent) => void;

  constructor(containerId: string) {
    const element = document.getElementById(containerId);
    if (!element) {
      throw new Error(`Container element with id "${containerId}" not found`);
    }
    this.container = element;

    // Bind event handlers to maintain 'this' context
    this.outsideClickHandler = this.handleOutsideClick.bind(this);
    this.containerClickHandler = this.handleContainerClick.bind(this);

    this.init();
  }

  /**
   * Initialize the component
   */
  private async init(): Promise<void> {
    this.render();
    await this.loadNotifications();
  }

  /**
   * Load notifications from the API
   */
  private async loadNotifications(): Promise<void> {
    this.isLoading = true;
    this.error = null;
    this.render(); // Show loading state

    try {
      const response = await fetchNotifications(1);
      this.notifications = response.data;
      this.error = null;
    } catch (error) {
      console.error('Failed to load notifications:', error);
      this.error = error as Error;
    } finally {
      this.isLoading = false;
      this.render(); // Show final state (success or error)
    }
  }

  /**
   * Mark a notification as read (using immutable update pattern)
   */
  private async markAsRead(notificationId: string): Promise<void> {
    try {
      await markNotificationAsRead(notificationId);

      // Immutable update: create new array with updated notification
      this.notifications = this.notifications.map((notification) =>
        notification.id === notificationId
          ? { ...notification, read_at: new Date().toISOString() }
          : notification
      );

      this.render();
    } catch (error) {
      console.error('Failed to mark notification as read:', error);
    }
  }

  /**
   * Toggle dropdown visibility
   */
  private toggleDropdown(): void {
    this.isOpen = !this.isOpen;
    this.render();
  }

  /**
   * Format notification data based on type (using discriminated union)
   */
  private formatNotification(notification: AppNotification): string {
    const isRead = notification.read_at !== null;
    const readClass = isRead ? 'opacity-60' : '';

    // Use discriminated union for automatic type narrowing
    switch (notification.data.type) {
      case 'stock_available':
        return this.formatStockAvailableNotification(
          notification as LaravelNotification<StockAvailableNotificationData>,
          isRead,
          readClass
        );
      case 'price_changed':
        return this.formatPriceChangedNotification(
          notification as LaravelNotification<PriceChangedNotificationData>,
          isRead,
          readClass
        );
      default:
        return this.formatUnknownNotification(readClass);
    }
  }

  /**
   * Format stock available notification
   */
  private formatStockAvailableNotification(
    notification: LaravelNotification<StockAvailableNotificationData>,
    isRead: boolean,
    readClass: string
  ): string {
    return `
        <div class="fi-dropdown-list-item flex cursor-pointer gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-white/5 ${readClass}" data-notification-id="${notification.id}" style="overflow: hidden;">
          <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-success-50 dark:bg-success-400/10">
            <svg class="h-5 w-5 text-success-600 dark:text-success-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div class="flex-1 min-w-0 space-y-1" style="max-width: 100%;">
            <p class="text-sm font-medium text-gray-950 dark:text-white" style="overflow-wrap: anywhere; word-break: break-word; white-space: normal;">${notification.data.product_name}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400" style="overflow-wrap: anywhere; word-break: break-word; white-space: normal; max-width: 100%;">${notification.data.message}</p>
            <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
              <span>Price: $${notification.data.product_price.toFixed(2)}</span>
              <span>•</span>
              <span>${this.formatDate(notification.created_at)}</span>
            </div>
          </div>
          ${!isRead ? '<div class="h-2 w-2 flex-shrink-0 rounded-full bg-primary-600 dark:bg-primary-400"></div>' : ''}
        </div>
      `;
  }

  /**
   * Format price changed notification
   */
  private formatPriceChangedNotification(
    notification: LaravelNotification<PriceChangedNotificationData>,
    isRead: boolean,
    readClass: string
  ): string {
    const isIncrease = notification.data.price_change_type === 'increase';
    const iconColor = isIncrease ? 'text-warning-600 dark:text-warning-400' : 'text-success-600 dark:text-success-400';
    const bgColor = isIncrease ? 'bg-warning-50 dark:bg-warning-400/10' : 'bg-success-50 dark:bg-success-400/10';

    return `
      <div class="fi-dropdown-list-item flex cursor-pointer gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-white/5 ${readClass}" data-notification-id="${notification.id}" style="overflow: hidden;">
        <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full ${bgColor}">
          <svg class="h-5 w-5 ${iconColor}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            ${isIncrease
              ? '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941" />'
              : '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6L9 12.75l4.286-4.286a11.948 11.948 0 014.306 6.43l.776 2.898m0 0l3.182-5.511m-3.182 5.51l-5.511-3.181" />'
            }
          </svg>
        </div>
        <div class="flex-1 min-w-0 space-y-1" style="max-width: 100%;">
          <p class="text-sm font-medium text-gray-950 dark:text-white" style="overflow-wrap: anywhere; word-break: break-word; white-space: normal;">${notification.data.product_name}</p>
          <p class="text-xs text-gray-500 dark:text-gray-400" style="overflow-wrap: anywhere; word-break: break-word; white-space: normal; max-width: 100%;">${notification.data.message}</p>
          <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
            <span>${this.formatDate(notification.created_at)}</span>
          </div>
        </div>
        ${!isRead ? '<div class="h-2 w-2 flex-shrink-0 rounded-full bg-primary-600 dark:bg-primary-400"></div>' : ''}
      </div>
    `;
  }

  /**
   * Format unknown notification type
   */
  private formatUnknownNotification(readClass: string): string {
    return `
      <div class="fi-dropdown-list-item px-4 py-3 ${readClass}">
        <p class="text-sm text-gray-500 dark:text-gray-400">Unknown notification type</p>
      </div>
    `;
  }

  // Note: Type guards removed - discriminated unions handle type narrowing automatically

  /**
   * Format date to relative time
   */
  private formatDate(dateString: string): string {
    const date = new Date(dateString);
    const now = new Date();
    const diffInSeconds = Math.floor((now.getTime() - date.getTime()) / 1000);

    if (diffInSeconds < 60) return 'just now';
    if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)}m ago`;
    if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)}h ago`;
    if (diffInSeconds < 604800) return `${Math.floor(diffInSeconds / 86400)}d ago`;

    return date.toLocaleDateString();
  }

  /**
   * Render the component
   */
  private render(): void {
    const unreadCount = this.notifications.filter((n) => n.read_at === null).length;

    this.container.innerHTML = `
      <div class="relative fi-dropdown">
        <button
          id="notification-toggle"
          class="relative flex items-center justify-center w-10 h-10 text-gray-400 transition hover:text-gray-500 dark:text-gray-400 dark:hover:text-gray-300 focus:outline-none"
          aria-label="Notifications"
          type="button"
        >
          <svg class="fi-dropdown-trigger-button-icon h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
          </svg>
          ${unreadCount > 0 ? `
            <span class="absolute" style="top: -4px; right: -4px; display: flex; height: 20px; min-width: 20px; align-items: center; justify-content: center; border-radius: 9999px; background-color: rgb(147 51 234); padding: 0 4px; font-size: 11px; font-weight: 500; color: white;">
              ${unreadCount}
            </span>
          ` : ''}
        </button>

        ${this.isOpen ? `
          <div class="fi-dropdown-panel absolute right-0 top-full z-10 mt-2 w-[400px] overflow-hidden rounded-lg bg-white shadow-lg ring-1 ring-gray-950/5 transition dark:bg-gray-900 dark:ring-white/10">
            <div class="fi-dropdown-header flex items-center gap-x-3 border-b border-gray-200 px-4 py-3 dark:border-white/10">
              <h3 class="fi-dropdown-header-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">
                Notifications
              </h3>
            </div>

            <div class="fi-dropdown-list max-h-96 overflow-y-auto divide-y divide-gray-200 dark:divide-white/5">
              ${this.isLoading
                ? this.renderLoadingSkeleton()
                : this.error
                ? this.renderErrorMessage()
                : this.notifications.length > 0
                ? this.notifications.map((n) => this.formatNotification(n)).join('')
                : '<div class="p-4 text-center text-sm text-gray-500 dark:text-gray-400">No notifications</div>'
              }
            </div>
          </div>
        ` : ''}
      </div>
    `;

    this.attachEventListeners();
  }

  /**
   * Render loading skeleton
   */
  private renderLoadingSkeleton(): string {
    return `
      <div class="p-4 space-y-4">
        ${[1, 2, 3].map(() => `
          <div class="flex gap-3 animate-pulse">
            <div class="h-8 w-8 rounded-full bg-gray-200 dark:bg-gray-700"></div>
            <div class="flex-1 space-y-2">
              <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-3/4"></div>
              <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-1/2"></div>
            </div>
          </div>
        `).join('')}
      </div>
    `;
  }

  /**
   * Render error message with retry button
   */
  private renderErrorMessage(): string {
    return `
      <div class="p-6 text-center space-y-3">
        <div class="flex justify-center">
          <svg class="h-12 w-12 text-red-500 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <div>
          <p class="text-sm font-medium text-gray-900 dark:text-white">Failed to load notifications</p>
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">${this.error?.message || 'Unknown error'}</p>
        </div>
        <button
          id="retry-notifications"
          class="fi-link group/link relative inline-flex items-center justify-center gap-1 text-sm font-semibold outline-none transition duration-75 hover:underline focus-visible:underline text-primary-600 dark:text-primary-400"
        >
          Try again
        </button>
      </div>
    `;
  }

  /**
   * Handle clicks within the container (event delegation)
   */
  private handleContainerClick(event: MouseEvent): void {
    const target = event.target as HTMLElement;

    // Check if clicked on toggle button or its children
    const toggleButton = target.closest('#notification-toggle');
    if (toggleButton) {
      event.preventDefault();
      event.stopPropagation();
      this.toggleDropdown();
      return;
    }

    // Check if clicked on a notification item
    const notificationItem = target.closest('[data-notification-id]');
    if (notificationItem) {
      event.preventDefault();
      event.stopPropagation();
      const notificationId = notificationItem.getAttribute('data-notification-id');
      if (notificationId) {
        this.markAsRead(notificationId);
      }
      return;
    }

    // Check if clicked on retry button
    if (target.id === 'retry-notifications' || target.closest('#retry-notifications')) {
      event.preventDefault();
      this.loadNotifications();
      return;
    }
  }

  /**
   * Handle clicks outside the dropdown to close it
   */
  private handleOutsideClick(event: MouseEvent): void {
    if (!this.container.contains(event.target as Node) && this.isOpen) {
      this.isOpen = false;
      this.render();
    }
  }

  /**
   * Attach event listeners
   */
  private attachEventListeners(): void {
    // Remove old listeners before attaching new ones
    this.container.removeEventListener('click', this.containerClickHandler);
    document.removeEventListener('click', this.outsideClickHandler);

    // Attach listeners
    this.container.addEventListener('click', this.containerClickHandler);
    document.addEventListener('click', this.outsideClickHandler);
  }

  /**
   * Clean up resources and remove event listeners
   * Call this when destroying the component
   */
  public destroy(): void {
    // Remove event listeners
    this.container.removeEventListener('click', this.containerClickHandler);
    document.removeEventListener('click', this.outsideClickHandler);

    // Clear container
    this.container.innerHTML = '';

    // Clear notifications array
    this.notifications = [];
  }

  /**
   * Public method to refresh notifications
   */
  public refresh(): void {
    this.loadNotifications();
  }
}
