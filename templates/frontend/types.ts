// FILE: resources/js/Types/[MODULE_NAME]/[model_name].ts

// Single [model_name] record
export interface [MODEL_NAME] {
    id: number;
    // [FIELDS - camelCase]
    // Example:
    // name: string;
    // code: string;
    // email?: string | null;
    // phone?: string | null;
    // address?: string | null;
    // status: string;
    // statusLabel?: string;
    createdAt: string;
    updatedAt: string;
}

// Create DTO - required fields + optional
export interface Create[MODEL_NAME]Dto {
    // [FIELDS]
    // Example:
    // name: string;
    // code: string;
    // email?: string;
    // phone?: string;
    // address?: string;
    // status?: string;
}

// Update DTO - all fields optional
export interface Update[MODEL_NAME]Dto {
    // [FIELDS - all optional]
    name?: string;
    code?: string;
    email?: string;
    phone?: string;
    address?: string;
    status?: string;
}

// Filters for index page
export interface [MODEL_NAME]Filters {
    search?: string;
    perPage?: number;
    // [ADDITIONAL_FILTERS if enum exists]
    // Example:
    // status?: string;
}

// Pagination metadata
export interface [MODEL_NAME]Pagination {
    currentPage: number;
    perPage: number;
    total: number;
    lastPage: number;
}
