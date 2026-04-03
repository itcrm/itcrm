import { createMachine } from "xstate";

// ── Public machine ───────────────────────────────────────────────────────────

const publicStates = {
  // ── Auth ──────────────────────────────────────────────────────────
  login: {
    on: {
      SUBMIT_INVALID_CREDENTIALS: "login_failed",
    },
  },

  /** Login form visible after a failed attempt */
  login_failed: {
    on: {
      SUBMIT_INVALID_CREDENTIALS: "login_failed",
    },
  },
};

export type PublicState = keyof typeof publicStates;

/**
 * State machine modelling the ITCRM public (unauthenticated) routes.
 *
 * States = distinct screens / UI conditions the user can be in.
 * Events = actions discoverable from each state.
 */
export const publicMachine = createMachine({
  id: "itcrm-public",
  initial: "login",
  states: publicStates,
});

// ── Authenticated machine ────────────────────────────────────────────────────

const authenticatedStates = {
  /** Terminal state after logout — user is returned to the login page */
  logged_out: {},

  // ── Data screen ────────────────────────────────────────────────────

  /** Default data page — SHOW_PERIOD=-2 means no rows visible until a date filter is applied */
  data: {
    on: {
      /** Submit data form with missing required fields — triggers validation */
      SUBMIT_EMPTY_DATA_ROW: "data_validation_error",
      /** Save a new data row */
      SUBMIT_DATA_ROW: "data_row_saved",
      /** Apply "Today" date filter — seeded rows become visible, enabling row-level actions */
      APPLY_DATE_LAST_24H: "data_last_24h",
      /** Navigate to the reminder view — shows rows with RemindTo=user (seeded row) */
      NAVIGATE_REMINDER: "data_reminder_view",
      APPLY_DATA_SEARCH: "data_search_results",
      APPLY_DATA_SEARCH_DATE_SORTED: "data_search_date_sorted",
      APPLY_DATA_SEARCH_TODAY: "data_search_today",
      APPLY_DATA_SEARCH_WEEK: "data_search_week",
      APPLY_DATA_SEARCH_MONTH: "data_search_month",
      APPLY_DATA_SEARCH_YEAR: "data_search_year",
      NAVIGATE_TYPES: "types",
      NAVIGATE_USERS: "users",
      NAVIGATE_ORDERS: "orders",
      NAVIGATE_TASK: "task",
      NAVIGATE_WAREHOUSE: "warehouse",
      NAVIGATE_FILTERS: "filters",
    },
  },

  /** Data screen with "Today" filter applied — seeded rows are visible, row-level actions available */
  data_last_24h: {
    on: {
      EDIT_DATA_ROW: "data_row_editing",
      COPY_DATA_ROW: "data_row_copy",
      EXPAND_DATA_ROW: "data_row_expanded",
      DELETE_DATA_ROW: "data_row_deleted",
      TOGGLE_MULTI_EDIT: "data_multi_edit_with_rows",
      CHANGE_DATA_SORT: "data_sort_toggled",
      VIEW_DATA_CHANGES: "data_changes_page",
    },
  },

  /** Data screen after creating a new row via the form */
  data_row_saved: {},

  /** Data reminder view — /Data/Reminder/1 shows rows with RemindTo=Alice */
  data_reminder_view: {},

  /** Data screen after a failed save — required fields have the `error` class */
  data_validation_error: {
    on: {
      SUBMIT_DATA_ROW: "data_row_saved",
      NAVIGATE_TYPES: "types",
    },
  },

  /** Data screen sorted by document date — DateSort link shows "Dok.datums" */
  data_sort_toggled: {},

  /** Data search results with FindDeleted=1 — deleted rows appear (tr.deleted class) */
  data_search_deleted: {},

  /** Data screen after a menu-level Search — URL is /Data/Search, rows match the term */
  data_search_results: {},

  /** Data screen after searching with Period=Today (5) — Period dropdown shows Today selected */
  data_search_today: {},

  /** Data screen after a search with Sort=by-Date — Sort dropdown shows Date selected */
  data_search_date_sorted: {},

  /** Data screen after a search with Period=Week (7) — Period dropdown shows Week selected */
  data_search_week: {},

  /** Data screen after a search with Period=Month (1) — last 30 days */
  data_search_month: {},

  /** Data screen after a search with Period=Year (4) — last year */
  data_search_year: {},

  /** Data row is soft-deleted (has CSS class "deleted") — restore is available */
  data_row_deleted: {
    on: {
      RESTORE_DATA_ROW: "data_last_24h",
      /** Second delete permanently removes the row — returns with remaining seed rows */
      HARD_DELETE_DATA_ROW: "data_last_24h",
      /** Check FindDeleted and resubmit the filter to show deleted rows in the reload */
      FIND_DELETED_ROWS: "data_find_deleted",
      /** Search with FindDeleted=1 checked — deleted rows appear in search results */
      SEARCH_WITH_DELETED: "data_search_deleted",
    },
  },

  /** Data screen with FindDeleted checked — deleted rows are visible after page reload */
  data_find_deleted: {},

  /** Data screen with a row loaded into the edit form (ID field non-empty) */
  data_row_editing: {
    on: {
      /** Save the edits — triggers a confirmation dialog then updates the row */
      SUBMIT_EDIT_DATA_ROW: "data_last_24h",
      /** Submit the edit form with a missing required field — triggers validation */
      SUBMIT_INVALID_EDIT_DATA_ROW: "data_edit_validation_error",
      RESET_DATA_FORM: "data_last_24h",
    },
  },

  /** Edit form has validation errors — ID field is non-empty (edit mode) */
  data_edit_validation_error: {
    on: {
      /** Fix the required field and save — updates the row */
      SUBMIT_EDIT_DATA_ROW: "data_last_24h",
      RESET_DATA_FORM: "data_last_24h",
    },
  },

  /** Data screen after clicking clone — form has ID="0" and cloned field values */
  data_row_copy: {
    on: {
      /** Save the cloned row — creates a new row */
      SUBMIT_DATA_ROW: "data_row_saved",
      RESET_DATA_FORM: "data_last_24h",
    },
  },

  /** Data screen with the detail slider expanded on one row */
  data_row_expanded: {
    on: {
      COLLAPSE_DATA_ROW: "data_last_24h",
      /** Clicking edit on an expanded row loads it into the edit form */
      EDIT_DATA_ROW: "data_row_editing",
    },
  },

  /** Change-history page for a specific data row (/lv/Changes/{ID}) */
  data_changes_page: {},

  /** Multi-edit bar visible with seed data rows present — enables bulk apply */
  data_multi_edit_with_rows: {
    on: {
      /** Apply the bulk change — server updates rows and reloads the page */
      SUBMIT_MULTI_EDIT: "data_last_24h",
      /** Close the multi-edit bar */
      TOGGLE_MULTI_EDIT: "data_last_24h",
    },
  },

  // ── Filters ────────────────────────────────────────────────────────
  /** Saved-filter management screen */
  filters: {
    on: {
      /** Submitting without a filter name triggers validation */
      SUBMIT_EMPTY_FILTER: "filters_validation_error",
      SUBMIT_VALID_FILTER: "filters_saved",
    },
  },

  /** Filters screen after a failed save — Name field has the `error` class */
  filters_validation_error: {
    on: {
      SUBMIT_VALID_FILTER: "filters_saved",
    },
  },

  /** Filters screen with at least one saved filter row */
  filters_saved: {
    on: {
      EDIT_FILTER_ROW: "filters_row_edit",
      DELETE_FILTER_ROW: "filters_row_deleted",
    },
  },

  /** Filters screen with a row soft-deleted — restore is available */
  filters_row_deleted: {
    on: {
      RESTORE_FILTER_ROW: "filters_saved",
      /** Second delete permanently removes the filter (no password required) */
      HARD_DELETE_FILTER_ROW: "filters_hard_deleted",
    },
  },

  /** Filters screen after a hard-delete — deleted row is permanently gone */
  filters_hard_deleted: {},
  /** Filters screen with the add/edit form pre-filled for a specific filter */
  filters_row_edit: {
    on: {
      /** Save the edited filter — row updates, form resets */
      SUBMIT_FILTER_EDIT: "filters_saved",
      /** Clear Name and submit — Name field gets error class */
      SUBMIT_INVALID_FILTER_EDIT: "filters_edit_validation_error",
      /** The — (reset) button clears the form, returning to create-new mode */
      RESET_FILTER_FORM: "filters_saved",
    },
  },

  /** Filters edit form with validation error — Name field has error class, ID non-empty */
  filters_edit_validation_error: {
    on: {
      SUBMIT_FILTER_EDIT: "filters_saved",
      RESET_FILTER_FORM: "filters_saved",
    },
  },

  // ── Types ──────────────────────────────────────────────────────────
  types: {
    on: {
      /** Submitting the add-type form with an empty code triggers validation */
      SUBMIT_EMPTY_TYPE: "types_validation_error",
      /** Submitting with a valid code adds a row to the list */
      SUBMIT_VALID_TYPE: "types_saved",
    },
  },

  /** Types screen after a failed save — Code field has the `error` class */
  types_validation_error: {
    on: {
      /** Re-submit with a valid code recovers from the error */
      SUBMIT_VALID_TYPE: "types_saved",
    },
  },

  /** Types screen with at least one saved row, enabling row-level actions */
  types_saved: {
    on: {
      /** Clicking the edit icon pre-fills the form with that row's data */
      EDIT_TYPE_ROW: "types_row_edit",
      DELETE_TYPE_ROW: "types_row_deleted",
    },
  },

  /** Types screen with a row soft-deleted — restore is available */
  types_row_deleted: {
    on: {
      RESTORE_TYPE_ROW: "types_saved",
      /** Second delete permanently removes the type */
      HARD_DELETE_TYPE_ROW: "types_hard_deleted",
    },
  },

  /** Types screen after a hard-delete — deleted row is permanently gone */
  types_hard_deleted: {},
  /** Types screen with the add/edit form pre-filled for a specific type */
  types_row_edit: {
    on: {
      /** The — (reset) button clears the form, returning to create-new mode */
      RESET_TYPE_FORM: "types_saved",
      /** Save with a new code updates the row */
      SUBMIT_VALID_TYPE: "types_saved",
      /** Clear Code and submit — Code field gets error class, ID stays set */
      SUBMIT_INVALID_TYPE_EDIT: "types_edit_validation_error",
    },
  },

  /** Types edit form with validation error — Code field has error class, ID non-empty */
  types_edit_validation_error: {
    on: {
      SUBMIT_VALID_TYPE: "types_saved",
      RESET_TYPE_FORM: "types_saved",
    },
  },

  // ── Users ──────────────────────────────────────────────────────────
  users: {
    on: {
      /** Submitting with empty login triggers validation */
      SUBMIT_EMPTY_USER: "users_validation_error",
      /** Submitting with unique login/password adds a row to the list */
      SUBMIT_VALID_USER: "users_saved",
      /** Clicking edit on the seeded Alice row pre-fills the form */
      EDIT_USER_ROW: "users_edit_form",
    },
  },

  /** Users screen after a failed save — Login field has the `error` class */
  users_validation_error: {
    on: {
      SUBMIT_VALID_USER: "users_saved",
    },
  },

  /** Users screen with at least one test-added row */
  users_saved: {
    on: {
      DELETE_USER_ROW: "users_row_deleted",
      /** Edit the newly created test row */
      EDIT_USER_ROW: "users_edit_form",
    },
  },

  /** Users screen with a row soft-deleted (status=-1) — restore is available */
  users_row_deleted: {
    on: {
      RESTORE_USER_ROW: "users_saved",
      /** Second delete permanently removes the user */
      HARD_DELETE_USER_ROW: "users_hard_deleted",
    },
  },

  /** Users screen after a hard-delete — deleted row is permanently gone */
  users_hard_deleted: {},

  /** Users screen with the form pre-filled for editing an existing user */
  users_edit_form: {
    on: {
      /** Save the edits — row updates in the list, form resets */
      SUBMIT_USER_EDIT: "users",
      /** Clear Login and submit — Login field gets error class */
      SUBMIT_INVALID_USER_EDIT: "users_edit_validation_error",
      RESET_USER_FORM: "users",
    },
  },

  /** Users edit form with validation error — Login field has error class, ID non-empty */
  users_edit_validation_error: {
    on: {
      SUBMIT_USER_EDIT: "users",
      RESET_USER_FORM: "users",
    },
  },

  // ── Orders ─────────────────────────────────────────────────────────
  orders: {
    on: {
      /** Submitting with an empty code triggers validation */
      SUBMIT_EMPTY_ORDER: "orders_validation_error",
      /** Submitting with a valid code adds a row to the list */
      SUBMIT_VALID_ORDER: "orders_saved",
      /** Seeded SPRING-GALA is always present so edit is available immediately */
      EDIT_ORDER_ROW: "orders_row_edit",
      /** Filter the list by code — reloads /Orders with filtered results */
      APPLY_ORDERS_FILTER: "orders_filtered",
      /** Filter orders by description text — reloads with description-matched results */
      APPLY_ORDERS_DESCRIPTION_FILTER: "orders_description_filtered",
      /** SPRING-GALA is seeded with change history — changes panel always available */
      SHOW_ORDER_CHANGES: "orders_changes_panel",
      /** Toggle the orders sort between ID DESC (default) and Code ASC */
      SORT_ORDERS: "orders_sort_toggled",
    },
  },

  /** Orders screen after a failed save — Code field has the `error` class */
  orders_validation_error: {
    on: {
      SUBMIT_VALID_ORDER: "orders_saved",
    },
  },

  /** Orders screen with a code filter applied — list shows only matching rows */
  orders_filtered: {
    on: {
      /** Clear the filter — reload orders with all rows */
      CLEAR_ORDERS_FILTER: "orders",
    },
  },

  /** Orders screen with a description filter applied — matches "Spring Gala" seed text */
  orders_description_filtered: {
    on: {
      CLEAR_ORDERS_FILTER: "orders",
    },
  },

  /** Orders screen with at least one saved row, enabling row-level actions */
  orders_saved: {
    on: {
      EDIT_ORDER_ROW: "orders_row_edit",
      DELETE_ORDER_ROW: "orders_row_deleted",
      /** Open the inline changes panel for an order row (requires seeded change history) */
      SHOW_ORDER_CHANGES: "orders_changes_panel",
    },
  },

  /** Orders screen with the inline changes panel expanded for a specific row */
  orders_changes_panel: {},
  /** Orders screen with a row soft-deleted — restore is available */
  orders_row_deleted: {
    on: {
      RESTORE_ORDER_ROW: "orders_saved",
      /** Second delete permanently removes the order */
      HARD_DELETE_ORDER_ROW: "orders_hard_deleted",
    },
  },

  /** Orders list after toggling the sort to Code ASC (from the Code column header link) */
  orders_sort_toggled: {},

  /** Orders screen after a hard-delete — deleted row is gone, active orders remain */
  orders_hard_deleted: {
    on: {
      SUBMIT_VALID_ORDER: "orders_saved",
    },
  },

  /** Orders screen with the form pre-filled for editing a specific order */
  orders_row_edit: {
    on: {
      /** Save the edited order — row updates, form resets */
      SUBMIT_ORDER_EDIT: "orders_saved",
      /** Submit the edit form with an empty Code — Code field gets error class */
      SUBMIT_INVALID_ORDER_EDIT: "orders_edit_validation_error",
      RESET_ORDER_FORM: "orders_saved",
    },
  },

  /** Orders edit form with validation error — Code field has the error class */
  orders_edit_validation_error: {
    on: {
      /** Fix the code and save — row updates, form resets */
      SUBMIT_ORDER_EDIT: "orders_saved",
      RESET_ORDER_FORM: "orders_saved",
    },
  },

  // ── Other screens ──────────────────────────────────────────────────
  task: {
    on: {
      /** Switch the fullCalendar to the week (agendaWeek) view */
      SWITCH_TASK_WEEK_VIEW: "task_week_view",
      /** Switch directly to the day (agendaDay) view from month view */
      SWITCH_TASK_DAY_VIEW: "task_day_view",
    },
  },

  /** Task calendar in agendaWeek view — shows a weekly agenda grid */
  task_week_view: {
    on: {
      /** Switch to the day (agendaDay) view from week view */
      SWITCH_TASK_DAY_VIEW: "task_day_view",
      /** Switch back to month view from week view */
      SWITCH_TASK_MONTH_VIEW: "task",
    },
  },

  /** Task calendar in agendaDay view — shows a single-day hourly grid */
  task_day_view: {
    on: {
      /** Switch back to month view from day view */
      SWITCH_TASK_MONTH_VIEW: "task",
      /** Switch to week view from day view */
      SWITCH_TASK_WEEK_VIEW: "task_week_view",
    },
  },

  warehouse: {
    on: {
      /** Click the X inside the slider to hide the selection toolbar (slider open by default) */
      TOGGLE_WAREHOUSE_SLIDER: "warehouse_slider_closed",
      /** Click the Export button — navigates to /Warehouse/Export which shows an error when no data matches */
      EXPORT_WAREHOUSE: "warehouse_export_empty",
    },
  },

  /** Warehouse screen with the selection slider toolbar hidden (closed) */
  warehouse_slider_closed: {
    on: {
      /** Click the SLO button to re-open the selection slider toolbar */
      OPEN_WAREHOUSE_SLIDER: "warehouse",
    },
  },

  /** Warehouse export page — shows "Nav datu eksportēšanai!" when no warehouse data matches the filter */
  warehouse_export_empty: {},
};

export type AuthenticatedState = keyof typeof authenticatedStates;

/**
 * State machine modelling the ITCRM authenticated routes.
 *
 * States = distinct screens / UI conditions the user can be in.
 * Events = actions discoverable from each state.
 *
 * Used by the model-based test runner to derive test paths via
 * getShortestPaths, ensuring every state is exercised and screenshot.
 *
 * LOGOUT is a root-level event available from every state.
 */
export const authenticatedMachine = createMachine({
  id: "itcrm-authenticated",
  initial: "data",
  on: {
    LOGOUT: ".logged_out",
    NAVIGATE_DATA: ".data",
    NAVIGATE_TYPES: ".types",
    NAVIGATE_USERS: ".users",
    NAVIGATE_ORDERS: ".orders",
    NAVIGATE_TASK: ".task",
    NAVIGATE_WAREHOUSE: ".warehouse",
    NAVIGATE_FILTERS: ".filters",
  },
  states: authenticatedStates,
});

